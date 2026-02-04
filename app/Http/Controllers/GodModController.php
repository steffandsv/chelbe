<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Card;
use App\Models\Stack;

class GodModController extends Controller
{
    /**
     * Display the GOD-MOD documentation page
     */
    public function index()
    {
        // Get counts for context
        $stats = [
            'boards' => Board::count(),
            'stacks' => Stack::count(),
            'cards' => Card::count(),
            'analysts' => Card::whereNotNull('analyst')->distinct('analyst')->count('analyst'),
            'portals' => Card::whereNotNull('portal')->distinct('portal')->count('portal'),
        ];

        return view('god-mod.index', compact('stats'));
    }
}
