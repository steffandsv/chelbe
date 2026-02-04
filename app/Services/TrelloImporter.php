<?php

namespace App\Services;

use App\Models\Card;
use App\Models\CardLabel;
use App\Models\ImportLog;
use Illuminate\Support\Facades\DB;

/**
 * Trello JSON Importer Service
 */
class TrelloImporter
{
    private TagNormalizer $normalizer;
    private DateExtractor $dateExtractor;

    public function __construct()
    {
        $this->normalizer = new TagNormalizer();
        $this->dateExtractor = new DateExtractor();
    }

    /**
     * Import a Trello JSON file
     */
    public function import(string $jsonContent, string $filename): array
    {
        $stats = ['imported' => 0, 'updated' => 0, 'labels' => 0, 'errors' => []];

        $data = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $stats['errors'][] = 'Invalid JSON: ' . json_last_error_msg();
            return $stats;
        }

        $boardName = $data['title'] ?? pathinfo($filename, PATHINFO_FILENAME);
        $stacks = $data['stacks'] ?? $data['lists'] ?? [];

        DB::beginTransaction();
        
        try {
            foreach ($stacks as $stack) {
                $listName = $stack['title'] ?? $stack['name'] ?? 'Unknown';
                $cards = $stack['cards'] ?? [];

                foreach ($cards as $cardData) {
                    $result = $this->processCard($cardData, $listName, $boardName);
                    
                    if ($result['action'] === 'insert') {
                        $stats['imported']++;
                    } else {
                        $stats['updated']++;
                    }
                    
                    $stats['labels'] += $result['labels'];
                }
            }

            // Log import
            ImportLog::create([
                'filename' => $filename,
                'board_name' => $boardName,
                'cards_imported' => $stats['imported'],
                'cards_updated' => $stats['updated'],
                'labels_imported' => $stats['labels'],
            ]);
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $stats['errors'][] = 'Import failed: ' . $e->getMessage();
        }

        return $stats;
    }

    /**
     * Process a single card
     */
    private function processCard(array $cardData, string $listName, string $boardName): array
    {
        $trelloId = $cardData['id'] ?? uniqid('card_');
        $title = $cardData['title'] ?? $cardData['name'] ?? 'Untitled';
        $description = $cardData['description'] ?? $cardData['desc'] ?? '';

        // Check if exists
        $existing = Card::where('trello_id', $trelloId)->first();
        $action = $existing ? 'update' : 'insert';

        // Extract data
        $metrics = $this->normalizer->extractFromDescription($description);
        $extractedDate = $this->dateExtractor->extract($cardData);
        $analyst = $this->extractAnalyst($cardData);

        // Prepare data
        $data = [
            'trello_id' => $trelloId,
            'title' => $title,
            'description' => $description,
            'board_name' => $boardName,
            'list_name' => $listName,
            'analyst' => $analyst,
            'extracted_date' => $extractedDate,
            'due_date' => isset($cardData['duedate']) ? $this->dateExtractor->parseFromText($cardData['duedate']) : null,
            'viabilidade_tatica' => $metrics['viabilidade_tatica'] ?? null,
            'complexidade_operacional' => $metrics['complexidade_operacional'] ?? null,
            'lucratividade_potencial' => $metrics['lucratividade_potencial'] ?? null,
            'chance_vitoria' => $metrics['chance_vitoria'] ?? null,
            'risco_operacional' => $metrics['risco_operacional'] ?? null,
            'ipm_score' => $metrics['ipm_score'] ?? null,
            'portal' => $this->extractPortal($title, $description),
            'pregao_number' => $this->extractPregaoNumber($title),
            'valor_estimado' => $this->extractValor($description),
            'orgao' => $this->extractOrgao($description),
        ];

        // Upsert
        $card = Card::updateOrCreate(
            ['trello_id' => $trelloId],
            $data
        );

        // Process labels
        $labelsCount = 0;
        $labels = $cardData['labels'] ?? [];
        
        if (!empty($labels)) {
            $card->labels()->delete();
            
            foreach ($labels as $label) {
                $processed = $this->normalizer->processLabel($label);
                
                CardLabel::create([
                    'card_id' => $card->id,
                    'category' => $processed['category'],
                    'value' => $processed['normalized'] ?? $processed['value'],
                    'raw_label' => $processed['raw'],
                    'color' => $processed['color'],
                ]);
                
                $labelsCount++;
            }
        }

        return ['action' => $action, 'card_id' => $card->id, 'labels' => $labelsCount];
    }

    private function extractAnalyst(array $card): ?string
    {
        $members = $card['assignedUsers'] ?? $card['members'] ?? [];
        
        if (!empty($members)) {
            $member = is_array($members[0]) 
                ? ($members[0]['participant']['displayname'] ?? $members[0]['fullName'] ?? $members[0]['username'] ?? null)
                : $members[0];
            return $member;
        }

        return null;
    }

    private function extractPortal(string $title, string $description): ?string
    {
        $patterns = [
            'ComprasNet' => '/compras\.?net|comprasnet/i',
            'Compras.gov.br' => '/compras\.gov\.br/i',
            'BLL' => '/\bBLL\b/i',
            'BNC' => '/\bBNC\b/i',
            'Licitanet' => '/licitanet/i',
            'BB Licitações' => '/bb\s*licita[çc][oõ]es/i',
        ];

        $text = $title . ' ' . $description;
        
        foreach ($patterns as $portal => $pattern) {
            if (preg_match($pattern, $text)) {
                return $portal;
            }
        }

        return null;
    }

    private function extractPregaoNumber(string $title): ?string
    {
        if (preg_match('/(?:PE|Preg[aã]o|#)\s*(\d+(?:\/\d+)?)/iu', $title, $m)) {
            return $m[1];
        }
        return null;
    }

    private function extractValor(string $description): ?float
    {
        if (preg_match('/R\$\s*([\d.,]+)/u', $description, $m)) {
            $value = str_replace('.', '', $m[1]);
            $value = str_replace(',', '.', $value);
            return (float) $value;
        }
        return null;
    }

    private function extractOrgao(string $description): ?string
    {
        if (preg_match('/(?:Órgão|Orgao|Entidade)[:\s]+([^\n|]+)/iu', $description, $m)) {
            return trim($m[1]);
        }
        return null;
    }
}
