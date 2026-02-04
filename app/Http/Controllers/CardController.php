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
     * Show create form
     */
    public function create()
    {
        $analysts = Card::whereNotNull('analyst')->distinct()->pluck('analyst')->sort();
        $boards = Card::whereNotNull('board_name')->distinct()->pluck('board_name')->sort();
        $portals = Card::whereNotNull('portal')->distinct()->pluck('portal')->sort();
        
        return view('cards.create', compact('analysts', 'boards', 'portals'));
    }

    /**
     * Store new card
     */
    public function store(Request $request)
    {
        $validated = $this->validateCard($request);
        
        // Generate a unique trello_id if not provided
        if (empty($validated['trello_id'])) {
            $validated['trello_id'] = 'manual_' . uniqid();
        }
        
        Card::create($validated);
        
        return redirect()->route('cards.index')
            ->with('success', 'Card criado com sucesso!');
    }

    /**
     * Show edit form
     */
    public function edit(Card $card)
    {
        $analysts = Card::whereNotNull('analyst')->distinct()->pluck('analyst')->sort();
        $boards = Card::whereNotNull('board_name')->distinct()->pluck('board_name')->sort();
        $portals = Card::whereNotNull('portal')->distinct()->pluck('portal')->sort();
        
        return view('cards.edit', compact('card', 'analysts', 'boards', 'portals'));
    }

    /**
     * Full update (web form)
     */
    public function updateFull(Request $request, Card $card)
    {
        $validated = $this->validateCard($request);
        $card->update($validated);
        
        return redirect()->route('cards.index')
            ->with('success', 'Card atualizado com sucesso!');
    }

    /**
     * Delete card
     */
    public function destroy(Card $card)
    {
        $card->delete();
        
        return redirect()->route('cards.index')
            ->with('success', 'Card excluído com sucesso!');
    }

    /**
     * Validate card data
     */
    protected function validateCard(Request $request): array
    {
        return $request->validate([
            // Identification
            'title' => 'required|string|max:500',
            'description' => 'nullable|string',
            'trello_id' => 'nullable|string|max:64',
            
            // Status
            'user_status' => 'sometimes|in:pending,tracking,won,lost',
            'defeat_reason' => 'nullable|string|max:1000',
            'user_notes' => 'nullable|string',
            
            // IPM Metrics
            'viabilidade_tatica' => 'nullable|in:Alta,Média,Baixa',
            'complexidade_operacional' => 'nullable|in:Alta,Média,Baixa',
            'lucratividade_potencial' => 'nullable|in:Alta,Média,Baixa',
            'chance_vitoria' => 'nullable|in:Alta,Média,Baixa',
            'risco_operacional' => 'nullable|in:Alta,Média,Baixa',
            'ipm_score' => 'nullable|string|max:20',
            
            // Metadata
            'analyst' => 'nullable|string|max:100',
            'board_name' => 'nullable|string|max:255',
            'list_name' => 'nullable|string|max:255',
            'stack_name' => 'nullable|string|max:255',
            
            // Tender data
            'portal' => 'nullable|string|max:100',
            'pregao_number' => 'nullable|string|max:100',
            'valor_estimado' => 'nullable|numeric|min:0',
            'orgao' => 'nullable|string|max:255',
            
            // Dates
            'extracted_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            
            // Import
            'import_type' => 'nullable|in:lost,won,tracking',
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
     * Update card (API - partial)
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

