<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\CustomTag;
use Illuminate\Http\Request;

class CardController extends Controller
{
    /**
     * Display cards listing
     */
    public function index(Request $request)
    {
        $query = Card::with(['labels', 'customTags']);

        // Apply filters
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('user_status', $request->status);
        }

        if ($request->filled('analyst')) {
            $query->where('analyst', $request->analyst);
        }

        if ($request->filled('board')) {
            $query->where('board_name', $request->board);
        }

        if ($request->filled('q')) {
            $query->where('title', 'like', "%{$request->q}%");
        }

        if ($request->filled('date_from')) {
            $query->where('extracted_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('extracted_date', '<=', $request->date_to);
        }

        $cards = $query->orderByDesc('updated_at')
            ->paginate(25)
            ->withQueryString();

        $analysts = Card::whereNotNull('analyst')
            ->distinct()
            ->pluck('analyst')
            ->sort();

        $boards = Card::whereNotNull('board_name')
            ->distinct()
            ->pluck('board_name')
            ->sort();

        return view('cards.index', [
            'cards' => $cards,
            'analysts' => $analysts,
            'boards' => $boards,
            'filters' => $request->only(['status', 'analyst', 'board', 'q', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Get single card (API)
     */
    public function show(Card $card)
    {
        $card->load(['labels', 'customTags']);
        return response()->json($card);
    }

    /**
     * Update card (API)
     */
    public function update(Request $request, Card $card)
    {
        $validated = $request->validate([
            'user_status' => 'sometimes|in:pending,tracking,won,lost',
            'defeat_reason' => 'nullable|string|max:1000',
            'user_notes' => 'nullable|string',
        ]);

        $card->update($validated);

        return response()->json([
            'success' => true,
            'id' => $card->id,
        ]);
    }

    /**
     * Get card tags (API)
     */
    public function getTags(Card $card)
    {
        return response()->json($card->customTags);
    }

    /**
     * Add custom tag (API)
     */
    public function addTag(Request $request, Card $card)
    {
        $validated = $request->validate([
            'tag_name' => 'required|string|max:100',
            'tag_value' => 'nullable|string|max:255',
        ]);

        $tag = $card->customTags()->create($validated);

        return response()->json([
            'success' => true,
            'id' => $tag->id,
            'tag_name' => $tag->tag_name,
            'tag_value' => $tag->tag_value,
        ]);
    }

    /**
     * Delete custom tag (API)
     */
    public function deleteTag(CustomTag $tag)
    {
        $tag->delete();
        return response()->json(['success' => true]);
    }
}
