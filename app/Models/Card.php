<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    protected $fillable = [
        'trello_id',
        'deck_card_id',
        'deck_board_id',
        'deck_stack_id',
        'title',
        'description',
        'board_name',
        'list_name',
        'stack_name',
        'analyst',
        'owner_uid',
        'owner_displayname',
        'card_order',
        'archived',
        'done',
        'notified',
        'comments_count',
        'deck_created_at',
        'deck_modified_at',
        'etag',
        'import_type',
        'extracted_date',
        'due_date',
        'user_status',
        'defeat_reason',
        'user_notes',
        'viabilidade_tatica',
        'complexidade_operacional',
        'lucratividade_potencial',
        'chance_vitoria',
        'risco_operacional',
        'ipm_score',
        'portal',
        'pregao_number',
        'valor_estimado',
        'orgao',
    ];

    protected $casts = [
        'extracted_date' => 'date',
        'due_date' => 'date',
        'deck_created_at' => 'datetime',
        'deck_modified_at' => 'datetime',
        'valor_estimado' => 'decimal:2',
        'deck_card_id' => 'integer',
        'deck_board_id' => 'integer',
        'deck_stack_id' => 'integer',
        'card_order' => 'integer',
        'archived' => 'boolean',
        'done' => 'boolean',
        'notified' => 'boolean',
        'comments_count' => 'integer',
    ];

    /**
     * Labels from Trello
     */
    public function labels(): HasMany
    {
        return $this->hasMany(CardLabel::class);
    }

    /**
     * User-defined custom tags
     */
    public function customTags(): HasMany
    {
        return $this->hasMany(CustomTag::class);
    }

    /**
     * Users assigned to this card
     */
    public function assignedUsers(): HasMany
    {
        return $this->hasMany(AssignedUser::class);
    }

    /**
     * Board this card belongs to (by deck_board_id)
     */
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class, 'deck_board_id', 'deck_board_id');
    }

    /**
     * Stack (column) this card belongs to (by deck_stack_id)
     */
    public function stack(): BelongsTo
    {
        return $this->belongsTo(Stack::class, 'deck_stack_id', 'deck_stack_id');
    }

    /**
     * Find or create a card by deck_card_id
     */
    public static function findOrCreateByDeckId(int $deckCardId, array $attributes): self
    {
        return static::updateOrCreate(
            ['deck_card_id' => $deckCardId],
            $attributes
        );
    }

    /**
     * Scope for filtering by import type
     */
    public function scopeByImportType($query, string $importType)
    {
        return $query->where('import_type', $importType);
    }

    /**
     * Scope for filtering by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('user_status', $status);
    }

    /**
     * Scope for filtering by analyst
     */
    public function scopeByAnalyst($query, string $analyst)
    {
        return $query->where('analyst', $analyst);
    }

    /**
     * Scope for filtering by board
     */
    public function scopeByBoard($query, string $board)
    {
        return $query->where('board_name', $board);
    }

    /**
     * Calculate win rate for a collection of cards
     */
    public static function calculateWinRate($cards): float
    {
        $won = $cards->where('user_status', 'won')->count();
        $lost = $cards->where('user_status', 'lost')->count();
        $total = $won + $lost;
        
        return $total > 0 ? round($won / $total * 100, 2) : 0;
    }

    /**
     * Get dashboard statistics
     */
    public static function getStats(): array
    {
        $cards = self::all();
        
        return [
            'total' => $cards->count(),
            'won' => $cards->where('user_status', 'won')->count(),
            'lost' => $cards->where('user_status', 'lost')->count(),
            'tracking' => $cards->where('user_status', 'tracking')->count(),
            'pending' => $cards->where('user_status', 'pending')->count(),
            'win_rate' => self::calculateWinRate($cards),
        ];
    }
}
