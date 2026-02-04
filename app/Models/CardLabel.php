<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardLabel extends Model
{
    protected $fillable = [
        'card_id',
        'category',
        'value',
        'raw_label',
        'color',
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}
