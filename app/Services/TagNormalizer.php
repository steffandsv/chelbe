<?php

namespace App\Services;

/**
 * Tag Normalizer Service
 * 
 * Intelligent normalization of tag values from Trello labels and description tables.
 */
class TagNormalizer
{
    /**
     * Universal value mappings → normalized value
     */
    private const VALUE_MAP = [
        // Alta / High
        'alta' => 'Alta', 'high' => 'Alta', 'alto' => 'Alta',
        'elevada' => 'Alta', 'excelente' => 'Alta',
        '🟢' => 'Alta', 'verde' => 'Alta', 'green' => 'Alta',
        
        // Média / Medium (includes hybrid values)
        'média' => 'Média', 'media' => 'Média', 'medio' => 'Média',
        'médio' => 'Média', 'medium' => 'Média',
        'moderada' => 'Média', 'moderado' => 'Média',
        'média-alta' => 'Média', 'media-alta' => 'Média',
        'média-baixa' => 'Média', 'media-baixa' => 'Média',
        'média alta' => 'Média', 'media alta' => 'Média',
        'média baixa' => 'Média', 'media baixa' => 'Média',
        '🟡' => 'Média', '🟠' => 'Média',
        'amarelo' => 'Média', 'laranja' => 'Média',
        'yellow' => 'Média', 'orange' => 'Média',
        
        // Baixa / Low
        'baixa' => 'Baixa', 'baixo' => 'Baixa', 'low' => 'Baixa',
        'pouca' => 'Baixa', 'pouco' => 'Baixa',
        'fraca' => 'Baixa', 'fraco' => 'Baixa',
        '🔴' => 'Baixa', 'vermelho' => 'Baixa', 'red' => 'Baixa',
        
        // Muito Alta / Very High
        'muito alta' => 'Muito Alta', 'muito alto' => 'Muito Alta',
        'altíssima' => 'Muito Alta', 'altissima' => 'Muito Alta',
        'very high' => 'Muito Alta', '🟣' => 'Muito Alta',
        
        // Muito Baixa / Very Low
        'muito baixa' => 'Muito Baixa', 'muito baixo' => 'Muito Baixa',
        'very low' => 'Muito Baixa', 'mínima' => 'Muito Baixa', 'minima' => 'Muito Baixa',
    ];

    /**
     * Known metric patterns
     */
    private const METRIC_PATTERNS = [
        'viabilidade' => 'viabilidade_tatica',
        'viabilidade tática' => 'viabilidade_tatica',
        'complexidade' => 'complexidade_operacional',
        'lucratividade' => 'lucratividade_potencial',
        'chance' => 'chance_vitoria',
        'chance de vitória' => 'chance_vitoria',
        'probabilidade' => 'chance_vitoria',
        'risco' => 'risco_operacional',
        'risco operacional' => 'risco_operacional',
        'ipm' => 'ipm_score',
        'índice' => 'ipm_score',
    ];

    /**
     * Normalize a single value
     */
    public function normalizeValue(?string $raw): ?string
    {
        if (empty($raw)) {
            return null;
        }

        $key = mb_strtolower(trim($raw));
        
        if (isset(self::VALUE_MAP[$key])) {
            return self::VALUE_MAP[$key];
        }

        // Try removing accents
        $keyNoAccent = $this->removeAccents($key);
        foreach (self::VALUE_MAP as $mapKey => $value) {
            if ($this->removeAccents($mapKey) === $keyNoAccent) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Extract metrics from description markdown tables
     */
    public function extractFromDescription(?string $description): array
    {
        if (empty($description)) {
            return [];
        }

        $metrics = [];
        $pattern = '/\|\s*([^|\n]+?)\s*\|\s*([^|\n]+?)\s*\|/u';
        
        if (preg_match_all($pattern, $description, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $metricName = mb_strtolower(trim($match[1]));
                $rawValue = trim($match[2]);
                
                // Skip headers
                if (preg_match('/^[-:]+$/', $rawValue) || 
                    str_contains($metricName, 'métrica') ||
                    str_contains($metricName, 'valor')) {
                    continue;
                }

                $column = $this->matchMetricToColumn($metricName);
                if ($column) {
                    $normalized = $this->normalizeValue($rawValue);
                    if ($normalized) {
                        $metrics[$column] = $normalized;
                    }
                }
            }
        }

        // Extract IPM score
        if (preg_match('/IPM[:\s]+(\d+(?:[.,]\d+)?)/ui', $description, $ipmMatch)) {
            $score = (float) str_replace(',', '.', $ipmMatch[1]);
            $metrics['ipm_score'] = $this->scoreToLevel($score);
        }

        return $metrics;
    }

    /**
     * Process a Trello label
     */
    public function processLabel(array $label): array
    {
        $title = $label['title'] ?? $label['name'] ?? '';
        $color = $label['color'] ?? '';

        $normalized = $this->normalizeValue($title);
        
        return [
            'category' => $normalized ? $this->guessCategoryFromColor($color) : null,
            'value' => $normalized ?? $title,
            'normalized' => $normalized,
            'raw' => $title,
            'color' => $color,
        ];
    }

    private function matchMetricToColumn(string $metricName): ?string
    {
        foreach (self::METRIC_PATTERNS as $pattern => $column) {
            if (str_contains($metricName, $pattern)) {
                return $column;
            }
        }
        return null;
    }

    private function scoreToLevel(float $score): string
    {
        if ($score >= 8) return 'Muito Alta';
        if ($score >= 6) return 'Alta';
        if ($score >= 4) return 'Média';
        if ($score >= 2) return 'Baixa';
        return 'Muito Baixa';
    }

    private function guessCategoryFromColor(?string $color): ?string
    {
        return match($color) {
            'green' => 'viabilidade_tatica',
            'yellow' => 'risco_operacional',
            'orange' => 'complexidade_operacional',
            'red' => 'risco_operacional',
            'purple' => 'prioridade',
            'blue' => 'portal',
            default => null
        };
    }

    private function removeAccents(string $str): string
    {
        $map = [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n',
        ];
        return strtr($str, $map);
    }
}
