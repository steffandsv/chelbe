@extends('layouts.app')

@section('title', 'Editar Card')

@section('content')
    <div class="form-container">
        <div class="panel">
            <div class="panel-header">
                <h2>Editar Card</h2>
                <a href="{{ route('cards.index') }}" class="btn btn-ghost">← Voltar</a>
            </div>
            <div class="panel-body">
                @if($errors->any())
                    <div class="alert alert-error">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('cards.update', $card) }}" class="card-form">
                    @csrf
                    @method('PUT')
                    
                    <!-- Identification Section -->
                    <fieldset class="form-section">
                        <legend>📋 Identificação</legend>
                        
                        <div class="form-group">
                            <label for="title">Título *</label>
                            <input type="text" id="title" name="title" class="form-input" 
                                value="{{ old('title', $card->title) }}" required maxlength="500">
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Descrição</label>
                            <textarea id="description" name="description" class="form-textarea" rows="4">{{ old('description', $card->description) }}</textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="portal">Portal</label>
                                <input type="text" id="portal" name="portal" class="form-input" 
                                    value="{{ old('portal', $card->portal) }}" list="portals-list">
                                <datalist id="portals-list">
                                    @foreach($portals as $p)
                                        <option value="{{ $p }}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="form-group">
                                <label for="pregao_number">Nº Pregão</label>
                                <input type="text" id="pregao_number" name="pregao_number" class="form-input" 
                                    value="{{ old('pregao_number', $card->pregao_number) }}">
                            </div>
                            <div class="form-group">
                                <label for="orgao">Órgão</label>
                                <input type="text" id="orgao" name="orgao" class="form-input" 
                                    value="{{ old('orgao', $card->orgao) }}">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="valor_estimado">Valor Estimado (R$)</label>
                                <input type="number" id="valor_estimado" name="valor_estimado" class="form-input" 
                                    value="{{ old('valor_estimado', $card->valor_estimado) }}" step="0.01" min="0">
                            </div>
                        </div>
                    </fieldset>

                    <!-- Status Section -->
                    <fieldset class="form-section">
                        <legend>📊 Status</legend>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="user_status">Status</label>
                                <select id="user_status" name="user_status" class="form-select">
                                    <option value="pending" {{ old('user_status', $card->user_status) === 'pending' ? 'selected' : '' }}>Pendente</option>
                                    <option value="tracking" {{ old('user_status', $card->user_status) === 'tracking' ? 'selected' : '' }}>Acompanhando</option>
                                    <option value="won" {{ old('user_status', $card->user_status) === 'won' ? 'selected' : '' }}>Vencido</option>
                                    <option value="lost" {{ old('user_status', $card->user_status) === 'lost' ? 'selected' : '' }}>Perdido</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="import_type">Tipo de Import</label>
                                <select id="import_type" name="import_type" class="form-select">
                                    <option value="">-</option>
                                    <option value="tracking" {{ old('import_type', $card->import_type) === 'tracking' ? 'selected' : '' }}>Tracking</option>
                                    <option value="won" {{ old('import_type', $card->import_type) === 'won' ? 'selected' : '' }}>Won</option>
                                    <option value="lost" {{ old('import_type', $card->import_type) === 'lost' ? 'selected' : '' }}>Lost</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="defeat_reason">Motivo da Derrota</label>
                            <input type="text" id="defeat_reason" name="defeat_reason" class="form-input" 
                                value="{{ old('defeat_reason', $card->defeat_reason) }}">
                        </div>
                        
                        <div class="form-group">
                            <label for="user_notes">Notas</label>
                            <textarea id="user_notes" name="user_notes" class="form-textarea" rows="3">{{ old('user_notes', $card->user_notes) }}</textarea>
                        </div>
                    </fieldset>

                    <!-- IPM Metrics Section -->
                    <fieldset class="form-section">
                        <legend>📈 Métricas IPM</legend>
                        
                        <div class="form-row metrics-row">
                            <div class="form-group">
                                <label for="viabilidade_tatica">Viabilidade Tática</label>
                                <select id="viabilidade_tatica" name="viabilidade_tatica" class="form-select">
                                    <option value="">-</option>
                                    <option value="Alta" {{ old('viabilidade_tatica', $card->viabilidade_tatica) === 'Alta' ? 'selected' : '' }}>Alta</option>
                                    <option value="Média" {{ old('viabilidade_tatica', $card->viabilidade_tatica) === 'Média' ? 'selected' : '' }}>Média</option>
                                    <option value="Baixa" {{ old('viabilidade_tatica', $card->viabilidade_tatica) === 'Baixa' ? 'selected' : '' }}>Baixa</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="complexidade_operacional">Complexidade Op.</label>
                                <select id="complexidade_operacional" name="complexidade_operacional" class="form-select">
                                    <option value="">-</option>
                                    <option value="Alta" {{ old('complexidade_operacional', $card->complexidade_operacional) === 'Alta' ? 'selected' : '' }}>Alta</option>
                                    <option value="Média" {{ old('complexidade_operacional', $card->complexidade_operacional) === 'Média' ? 'selected' : '' }}>Média</option>
                                    <option value="Baixa" {{ old('complexidade_operacional', $card->complexidade_operacional) === 'Baixa' ? 'selected' : '' }}>Baixa</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="lucratividade_potencial">Lucratividade Pot.</label>
                                <select id="lucratividade_potencial" name="lucratividade_potencial" class="form-select">
                                    <option value="">-</option>
                                    <option value="Alta" {{ old('lucratividade_potencial', $card->lucratividade_potencial) === 'Alta' ? 'selected' : '' }}>Alta</option>
                                    <option value="Média" {{ old('lucratividade_potencial', $card->lucratividade_potencial) === 'Média' ? 'selected' : '' }}>Média</option>
                                    <option value="Baixa" {{ old('lucratividade_potencial', $card->lucratividade_potencial) === 'Baixa' ? 'selected' : '' }}>Baixa</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row metrics-row">
                            <div class="form-group">
                                <label for="chance_vitoria">Chance de Vitória</label>
                                <select id="chance_vitoria" name="chance_vitoria" class="form-select">
                                    <option value="">-</option>
                                    <option value="Alta" {{ old('chance_vitoria', $card->chance_vitoria) === 'Alta' ? 'selected' : '' }}>Alta</option>
                                    <option value="Média" {{ old('chance_vitoria', $card->chance_vitoria) === 'Média' ? 'selected' : '' }}>Média</option>
                                    <option value="Baixa" {{ old('chance_vitoria', $card->chance_vitoria) === 'Baixa' ? 'selected' : '' }}>Baixa</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="risco_operacional">Risco Operacional</label>
                                <select id="risco_operacional" name="risco_operacional" class="form-select">
                                    <option value="">-</option>
                                    <option value="Alta" {{ old('risco_operacional', $card->risco_operacional) === 'Alta' ? 'selected' : '' }}>Alta</option>
                                    <option value="Média" {{ old('risco_operacional', $card->risco_operacional) === 'Média' ? 'selected' : '' }}>Média</option>
                                    <option value="Baixa" {{ old('risco_operacional', $card->risco_operacional) === 'Baixa' ? 'selected' : '' }}>Baixa</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="ipm_score">Score IPM</label>
                                <input type="text" id="ipm_score" name="ipm_score" class="form-input" 
                                    value="{{ old('ipm_score', $card->ipm_score) }}">
                            </div>
                        </div>
                    </fieldset>

                    <!-- Metadata Section -->
                    <fieldset class="form-section">
                        <legend>🏷️ Metadados</legend>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="analyst">Analista</label>
                                <input type="text" id="analyst" name="analyst" class="form-input" 
                                    value="{{ old('analyst', $card->analyst) }}" list="analysts-list">
                                <datalist id="analysts-list">
                                    @foreach($analysts as $a)
                                        <option value="{{ $a }}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="form-group">
                                <label for="board_name">Board</label>
                                <input type="text" id="board_name" name="board_name" class="form-input" 
                                    value="{{ old('board_name', $card->board_name) }}" list="boards-list">
                                <datalist id="boards-list">
                                    @foreach($boards as $b)
                                        <option value="{{ $b }}">
                                    @endforeach
                                </datalist>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="list_name">Lista</label>
                                <input type="text" id="list_name" name="list_name" class="form-input" 
                                    value="{{ old('list_name', $card->list_name) }}">
                            </div>
                            <div class="form-group">
                                <label for="stack_name">Stack</label>
                                <input type="text" id="stack_name" name="stack_name" class="form-input" 
                                    value="{{ old('stack_name', $card->stack_name) }}">
                            </div>
                        </div>
                    </fieldset>

                    <!-- Dates Section -->
                    <fieldset class="form-section">
                        <legend>📅 Datas</legend>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="extracted_date">Data de Extração</label>
                                <input type="date" id="extracted_date" name="extracted_date" class="form-input" 
                                    value="{{ old('extracted_date', $card->extracted_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="form-group">
                                <label for="due_date">Prazo Limite</label>
                                <input type="date" id="due_date" name="due_date" class="form-input" 
                                    value="{{ old('due_date', $card->due_date?->format('Y-m-d')) }}">
                            </div>
                        </div>
                    </fieldset>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                        <a href="{{ route('cards.index') }}" class="btn btn-ghost">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .form-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .card-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-section {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            background: var(--bg-tertiary);
        }

        .form-section legend {
            font-weight: 600;
            font-size: 1rem;
            padding: 0 0.5rem;
            color: var(--text-primary);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.375rem;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-secondary);
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 0.625rem 0.875rem;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 0.9rem;
            transition: border-color 0.2s;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .metrics-row {
            grid-template-columns: repeat(3, 1fr);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }

        .alert ul {
            margin: 0;
            padding-left: 1.25rem;
        }
    </style>
@endsection
