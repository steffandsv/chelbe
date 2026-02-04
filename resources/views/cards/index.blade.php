@extends('layouts.app')

@section('title', 'Cards')

@section('content')
    <!-- Filters -->
    <div class="filters-bar">
        <form method="GET" action="{{ route('cards.index') }}" class="filters-form">
            <div class="filter-group">
                <input type="text" name="q" class="filter-input" placeholder="Buscar por título..."
                    value="{{ $filters['q'] ?? '' }}">
            </div>

            <div class="filter-group">
                <select name="status" class="filter-select">
                    <option value="all">Todos os status</option>
                    <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pendente</option>
                    <option value="tracking" {{ ($filters['status'] ?? '') === 'tracking' ? 'selected' : '' }}>Acompanhando
                    </option>
                    <option value="won" {{ ($filters['status'] ?? '') === 'won' ? 'selected' : '' }}>Vencido</option>
                    <option value="lost" {{ ($filters['status'] ?? '') === 'lost' ? 'selected' : '' }}>Perdido</option>
                </select>
            </div>

            <div class="filter-group">
                <select name="analyst" class="filter-select">
                    <option value="">Todos os analistas</option>
                    @foreach($analysts as $analyst)
                        <option value="{{ $analyst }}" {{ ($filters['analyst'] ?? '') === $analyst ? 'selected' : '' }}>
                            {{ $analyst }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <select name="board" class="filter-select">
                    <option value="">Todos os boards</option>
                    @foreach($boards as $board)
                        <option value="{{ $board }}" {{ ($filters['board'] ?? '') === $board ? 'selected' : '' }}>
                            {{ $board }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="{{ route('cards.index') }}" class="btn btn-ghost">Limpar</a>
        </form>
    </div>

    <!-- Results info -->
    <div class="results-info">
        <span>Mostrando {{ $cards->firstItem() ?? 0 }} - {{ $cards->lastItem() ?? 0 }} de {{ $cards->total() }} cards</span>
    </div>

    <!-- Cards Table -->
    <div class="panel">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="25%">Título</th>
                        <th>Analista</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Motivo Derrota</th>
                        <th>Tags</th>
                        <th width="50">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cards as $card)
                        <tr class="card-row" data-id="{{ $card->id }}">
                            <td class="cell-title">
                                <span title="{{ $card->title }}">{{ Str::limit($card->title, 40) }}</span>
                                @if($card->viabilidade_tatica)
                                    <span class="metric-badge metric-{{ strtolower($card->viabilidade_tatica) }}">
                                        VT: {{ $card->viabilidade_tatica }}
                                    </span>
                                @endif
                            </td>
                            <td>{{ $card->analyst ?? '-' }}</td>
                            <td>{{ $card->extracted_date?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                <select class="inline-edit status-select" data-id="{{ $card->id }}" data-field="user_status"
                                    data-url="{{ url('/api/cards/' . $card->id) }}">
                                    <option value="pending" {{ $card->user_status === 'pending' ? 'selected' : '' }}>Pendente
                                    </option>
                                    <option value="tracking" {{ $card->user_status === 'tracking' ? 'selected' : '' }}>
                                        Acompanhando</option>
                                    <option value="won" {{ $card->user_status === 'won' ? 'selected' : '' }}>Vencido</option>
                                    <option value="lost" {{ $card->user_status === 'lost' ? 'selected' : '' }}>Perdido</option>
                                </select>
                            </td>
                            <td>
                                <input type="text"
                                    class="inline-edit defeat-input {{ $card->user_status !== 'lost' ? 'hidden' : '' }}"
                                    data-id="{{ $card->id }}" data-field="defeat_reason"
                                    data-url="{{ url('/api/cards/' . $card->id) }}" value="{{ $card->defeat_reason }}"
                                    placeholder="Motivo...">
                            </td>
                            <td>
                                <div class="tags-container" data-card-id="{{ $card->id }}">
                                    @foreach($card->customTags as $tag)
                                        <span class="tag">
                                            {{ $tag->tag_name }}{{ $tag->tag_value ? ': ' . $tag->tag_value : '' }}
                                            <span class="remove-tag" data-id="{{ $tag->id }}" title="Remover">×</span>
                                        </span>
                                    @endforeach
                                    <button type="button" class="btn-add-tag" data-card-id="{{ $card->id }}">+ Tag</button>
                                </div>
                            </td>
                            <td>
                                <button type="button" class="btn-icon btn-expand" data-id="{{ $card->id }}"
                                    title="Ver detalhes">
                                    👁️
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">
                                Nenhum card encontrado. <a href="{{ route('import.index') }}">Importar JSON</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($cards->hasPages())
            <div class="pagination">
                @if($cards->onFirstPage())
                    <span class="pagination-btn disabled">← Anterior</span>
                @else
                    <a href="{{ $cards->previousPageUrl() }}" class="pagination-btn">← Anterior</a>
                @endif

                <span class="pagination-info">
                    Página {{ $cards->currentPage() }} de {{ $cards->lastPage() }}
                </span>

                @if($cards->hasMorePages())
                    <a href="{{ $cards->nextPageUrl() }}" class="pagination-btn">Próximo →</a>
                @else
                    <span class="pagination-btn disabled">Próximo →</span>
                @endif
            </div>
        @endif
    </div>

    <!-- Card Detail Modal -->
    <div id="cardModal" class="modal hidden">
        <div class="modal-backdrop"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Detalhes do Card</h3>
                <button type="button" class="modal-close" onclick="closeModal()">×</button>
            </div>
            <div class="modal-body" id="cardModalBody">
                <!-- Loaded via JS -->
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/inline-edit.js') }}"></script>
@endpush