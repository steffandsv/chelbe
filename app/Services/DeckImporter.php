<?php

namespace App\Services;

use App\Models\Board;
use App\Models\Stack;
use App\Models\Card;
use App\Models\CardLabel;
use App\Models\AssignedUser;
use App\Models\ImportLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * DeckImporter - Complete JSON importer for Nextcloud Deck data
 * 
 * Handles import of boards, stacks, cards, labels and assigned users
 * with full deduplication support via deck_card_id
 */
class DeckImporter
{
    protected DateExtractor $dateExtractor;
    
    protected int $cardsCreated = 0;
    protected int $cardsUpdated = 0;
    protected int $cardsSkipped = 0;
    protected int $labelsProcessed = 0;
    protected int $usersProcessed = 0;
    
    protected ?string $boardName = null;
    protected ?int $boardId = null;
    
    public function __construct()
    {
        $this->dateExtractor = new DateExtractor();
    }
    
    /**
     * Import data from JSON
     * 
     * @param array $data The decoded JSON data
     * @param string $importType 'lost', 'won', or 'tracking'
     * @param string $filename Original filename for logging
     * @return array
     */
    public function import(array $data, string $importType, string $filename): array
    {
        $this->resetCounters();
        
        // Map import type to user_status
        $userStatus = match ($importType) {
            'lost' => 'lost',
            'won' => 'won',
            'tracking' => 'tracking',
            default => 'pending',
        };
        
        DB::beginTransaction();
        
        try {
            // Handle both array format and {"boards": [...]} format
            $boards = $data['boards'] ?? $data;
            
            // If boards is not a list, wrap it
            if (!isset($boards[0]) && !empty($boards)) {
                $boards = [$boards];
            }
            
            // Process each board in the JSON
            foreach ($boards as $boardData) {
                $this->processBoard($boardData, $importType, $userStatus);
            }
            
            // Log the import
            ImportLog::create([
                'filename' => $filename,
                'board_name' => $this->boardName,
                'import_type' => $importType,
                'cards_imported' => $this->cardsCreated,
                'cards_updated' => $this->cardsUpdated,
                'cards_skipped' => $this->cardsSkipped,
                'labels_imported' => $this->labelsProcessed,
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'board_name' => $this->boardName,
                'cards_created' => $this->cardsCreated,
                'cards_updated' => $this->cardsUpdated,
                'cards_skipped' => $this->cardsSkipped,
                'labels_processed' => $this->labelsProcessed,
                'users_processed' => $this->usersProcessed,
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('DeckImporter error: ' . $e->getMessage(), [
                'file' => $filename,
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Process a single board
     */
    protected function processBoard(array $boardData, string $importType, string $userStatus): void
    {
        $deckBoardId = $boardData['id'] ?? null;
        $this->boardName = $boardData['title'] ?? 'Unknown Board';
        
        // Create or update board
        if ($deckBoardId) {
            $board = Board::findOrCreateByDeckId($deckBoardId, [
                'title' => $this->boardName,
                'owner' => $boardData['owner'] ?? null,
                'color' => $boardData['color'] ?? null,
                'archived' => $boardData['archived'] ?? false,
            ]);
            $this->boardId = $board->id;
        }
        
        // Process stacks (columns)
        $stacks = $boardData['stacks'] ?? [];
        foreach ($stacks as $stackData) {
            $this->processStack($stackData, $deckBoardId, $importType, $userStatus);
        }
    }
    
    /**
     * Process a single stack (column/list)
     */
    protected function processStack(array $stackData, ?int $deckBoardId, string $importType, string $userStatus): void
    {
        $deckStackId = $stackData['id'] ?? null;
        $stackTitle = $stackData['title'] ?? 'Unknown Stack';
        
        // Create or update stack
        if ($this->boardId && $deckStackId) {
            Stack::findOrCreateByDeckId($this->boardId, $deckStackId, [
                'title' => $stackTitle,
                'stack_order' => $stackData['order'] ?? 0,
            ]);
        }
        
        // Process cards in this stack
        $cards = $stackData['cards'] ?? [];
        foreach ($cards as $cardData) {
            $this->processCard($cardData, $deckBoardId, $deckStackId, $stackTitle, $importType, $userStatus);
        }
    }
    
    /**
     * Process a single card
     */
    protected function processCard(
        array $cardData,
        ?int $deckBoardId,
        ?int $deckStackId,
        string $stackTitle,
        string $importType,
        string $userStatus
    ): void {
        $deckCardId = $cardData['id'] ?? null;
        
        if (!$deckCardId) {
            $this->cardsSkipped++;
            return;
        }
        
        // Check if card already exists
        $existingCard = Card::where('deck_card_id', $deckCardId)->first();
        $isUpdate = $existingCard !== null;
        
        // Extract date from title/description
        $extractedDate = $this->dateExtractor->extract($cardData);
        
        // Parse duedate
        $dueDate = null;
        if (!empty($cardData['duedate'])) {
            try {
                $dueDate = Carbon::parse($cardData['duedate'])->toDateString();
            } catch (\Exception $e) {
                // Invalid date, ignore
            }
        }
        
        // Parse timestamps
        $deckCreatedAt = null;
        $deckModifiedAt = null;
        
        if (!empty($cardData['createdAt'])) {
            try {
                $deckCreatedAt = Carbon::createFromTimestamp($cardData['createdAt']);
            } catch (\Exception $e) {
                // Invalid timestamp
            }
        }
        
        if (!empty($cardData['lastModified'])) {
            try {
                $deckModifiedAt = Carbon::createFromTimestamp($cardData['lastModified']);
            } catch (\Exception $e) {
                // Invalid timestamp
            }
        }
        
        // Get owner info
        $owner = $cardData['owner'] ?? [];
        $ownerUid = $owner['uid'] ?? null;
        $ownerDisplayname = $owner['displayname'] ?? null;
        
        // Build attributes
        $attributes = [
            'deck_board_id' => $deckBoardId,
            'deck_stack_id' => $deckStackId,
            'title' => $cardData['title'] ?? '',
            'description' => $cardData['description'] ?? '',
            'board_name' => $this->boardName,
            'list_name' => $stackTitle,
            'stack_name' => $stackTitle,
            'owner_uid' => $ownerUid,
            'owner_displayname' => $ownerDisplayname,
            'card_order' => $cardData['order'] ?? 0,
            'archived' => $cardData['archived'] ?? false,
            'done' => $cardData['done'] ?? null,
            'notified' => $cardData['notified'] ?? false,
            'comments_count' => $cardData['commentsCount'] ?? 0,
            'deck_created_at' => $deckCreatedAt,
            'deck_modified_at' => $deckModifiedAt,
            'etag' => $cardData['ETag'] ?? null,
            'import_type' => $importType,
            'user_status' => $userStatus,
            'extracted_date' => $extractedDate,
            'due_date' => $dueDate,
        ];
        
        // Create or update card
        $card = Card::findOrCreateByDeckId($deckCardId, $attributes);
        
        if ($isUpdate) {
            $this->cardsUpdated++;
        } else {
            $this->cardsCreated++;
        }
        
        // Process labels
        $this->processLabels($card, $cardData['labels'] ?? []);
        
        // Process assigned users
        $this->processAssignedUsers($card, $cardData['assignedUsers'] ?? []);
    }
    
    /**
     * Process labels for a card
     */
    protected function processLabels(Card $card, array $labels): void
    {
        // Get existing label raw values
        $existingRaws = $card->labels()->pluck('raw_label')->toArray();
        $newRaws = [];
        
        foreach ($labels as $labelData) {
            $rawLabel = $labelData['title'] ?? '';
            if (empty($rawLabel)) {
                continue;
            }
            
            $newRaws[] = $rawLabel;
            
            // Parse label (format: "CATEGORY: VALUE" or just "VALUE")
            $parts = explode(':', $rawLabel, 2);
            $category = count($parts) > 1 ? trim($parts[0]) : null;
            $value = count($parts) > 1 ? trim($parts[1]) : trim($parts[0]);
            
            CardLabel::updateOrCreate(
                [
                    'card_id' => $card->id,
                    'raw_label' => $rawLabel,
                ],
                [
                    'category' => $category,
                    'value' => $value,
                    'color' => $labelData['color'] ?? null,
                ]
            );
            
            $this->labelsProcessed++;
        }
        
        // Remove labels no longer present
        $toRemove = array_diff($existingRaws, $newRaws);
        if (!empty($toRemove)) {
            $card->labels()->whereIn('raw_label', $toRemove)->delete();
        }
    }
    
    /**
     * Process assigned users for a card
     */
    protected function processAssignedUsers(Card $card, array $assignedUsers): void
    {
        AssignedUser::syncForCard($card->id, $assignedUsers);
        $this->usersProcessed += count($assignedUsers);
    }
    
    /**
     * Reset counters for a new import
     */
    protected function resetCounters(): void
    {
        $this->cardsCreated = 0;
        $this->cardsUpdated = 0;
        $this->cardsSkipped = 0;
        $this->labelsProcessed = 0;
        $this->usersProcessed = 0;
        $this->boardName = null;
        $this->boardId = null;
    }
    
    /**
     * Preview import without making changes
     */
    public function preview(array $data): array
    {
        $totalCards = 0;
        $existingCards = 0;
        $newCards = 0;
        $boards = [];
        
        // Handle both array format and {"boards": [...]} format
        $boardsList = $data['boards'] ?? $data;
        
        // If boards is not a list, wrap it
        if (!isset($boardsList[0]) && !empty($boardsList)) {
            $boardsList = [$boardsList];
        }
        
        foreach ($boardsList as $boardData) {
            $boardName = $boardData['title'] ?? 'Unknown';
            $boards[] = $boardName;
            
            // Stacks can be an object (associative array) or array
            $stacks = $boardData['stacks'] ?? [];
            foreach ($stacks as $stackData) {
                // Ensure stackData is an array (in case of object format)
                if (!is_array($stackData)) {
                    continue;
                }
                $cards = $stackData['cards'] ?? [];
                foreach ($cards as $cardData) {
                    $deckCardId = $cardData['id'] ?? null;
                    $totalCards++;
                    
                    if ($deckCardId && Card::where('deck_card_id', $deckCardId)->exists()) {
                        $existingCards++;
                    } else {
                        $newCards++;
                    }
                }
            }
        }
        
        return [
            'boards' => $boards,
            'total_cards' => $totalCards,
            'existing_cards' => $existingCards,
            'new_cards' => $newCards,
        ];
    }
}
