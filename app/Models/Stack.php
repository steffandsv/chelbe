<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stack extends Model
{
    protected $fillable = [
        'board_id',
        'deck_stack_id',
        'title',
        'stack_order',
    ];

    protected $casts = [
        'deck_stack_id' => 'integer',
        'stack_order' => 'integer',
    ];

    /**
     * Get the board this stack belongs to
     */
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    /**
     * Get all cards in this stack
     */
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class, 'deck_stack_id', 'deck_stack_id');
    }

    /**
     * Find or create a stack by board_id and deck_stack_id
     */
    public static function findOrCreateByDeckId(int $boardId, int $deckStackId, array $attributes): self
    {
        return static::updateOrCreate(
            [
                'board_id' => $boardId,
                'deck_stack_id' => $deckStackId,
            ],
            $attributes
        );
    }
}
