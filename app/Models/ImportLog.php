<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    protected $fillable = [
        'filename',
        'board_name',
        'cards_imported',
        'cards_updated',
        'labels_imported',
    ];
}
