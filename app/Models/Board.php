<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Board extends Model
{
    protected $fillable = [
        'deck_board_id',
        'title',
        'owner',
        'color',
        'archived',
    ];

    protected $casts = [
        'deck_board_id' => 'integer',
        'archived' => 'boolean',
    ];

    /**
     * Get all stacks (columns) for this board
     */
    public function stacks(): HasMany
    {
        return $this->hasMany(Stack::class)->orderBy('stack_order');
    }

    /**
     * Get all cards for this board (through stacks or direct)
     */
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class, 'deck_board_id', 'deck_board_id');
    }

    /**
     * Find or create a board by deck_board_id
     */
    public static function findOrCreateByDeckId(int $deckBoardId, array $attributes): self
    {
        return static::updateOrCreate(
            ['deck_board_id' => $deckBoardId],
            $attributes
        );
    }
}
