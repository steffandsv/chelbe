<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomTag extends Model
{
    protected $fillable = [
        'card_id',
        'tag_name',
        'tag_value',
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}
