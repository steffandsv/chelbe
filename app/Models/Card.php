<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    protected $fillable = [
        'trello_id',
        'title',
        'description',
        'board_name',
        'list_name',
        'analyst',
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
        'valor_estimado' => 'decimal:2',
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
