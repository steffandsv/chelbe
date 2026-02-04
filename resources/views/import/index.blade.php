@extends('layouts.app')

@section('title', 'Importar JSON')

@section('content')
    <div class="import-container">
        <!-- Upload Panel -->
        <div class="panel upload-panel">
            <div class="panel-header">
                <h3>Importar Dados do Deck</h3>
            </div>
            <div class="panel-body">
                <!-- Import Type Selector -->
                <div class="import-type-selector">
                    <label class="import-type-label">Tipo de Dados:</label>
                    <div class="import-type-buttons">
                        <button type="button" class="import-type-btn" data-type="tracking">
                            <span class="type-icon">⚖️</span>
                            <span class="type-name">Em Andamento</span>
                            <span class="type-desc">Painel de Licitações</span>
                        </button>
                        <button type="button" class="import-type-btn" data-type="won">
                            <span class="type-icon">🏆</span>
                            <span class="type-name">Vitórias</span>
                            <span class="type-desc">Processos Vencidos</span>
                        </button>
                        <button type="button" class="import-type-btn" data-type="lost">
                            <span class="type-icon">❌</span>
                            <span class="type-name">Derrotas</span>
                            <span class="type-desc">Licitações Perdidas</span>
                        </button>
                    </div>
                    <input type="hidden" id="importType" value="">
                </div>

                <div class="upload-zone" id="uploadZone">
                    <div class="upload-icon">📁</div>
                    <div class="upload-text">Arraste e solte seu arquivo JSON aqui</div>
                    <div class="upload-subtext">ou clique para selecionar (máx. 50MB)</div>
                    <input type="file" id="fileInput" class="upload-input" accept=".json">
                </div>

                <div id="fileInfo" class="file-info hidden">
                    <div class="file-details">
                        <span class="file-icon">📄</span>
                        <span class="file-name" id="fileName"></span>
                        <span class="file-size" id="fileSize"></span>
                        <button type="button" class="btn-remove" id="removeFile">✕</button>
                    </div>
                </div>

                <!-- Preview Section -->
                <div id="previewSection" class="preview-section hidden">
                    <div class="preview-header">
                        <span class="preview-icon">📊</span>
                        <span>Prévia da Importação</span>
                    </div>
                    <div class="preview-stats">
                        <div class="preview-stat">
                            <span class="stat-value" id="previewTotal">0</span>
                            <span class="stat-label">Total de Cards</span>
                        </div>
                        <div class="preview-stat stat-new">
                            <span class="stat-value" id="previewNew">0</span>
                            <span class="stat-label">Novos</span>
                        </div>
                        <div class="preview-stat stat-update">
                            <span class="stat-value" id="previewExisting">0</span>
                            <span class="stat-label">Atualizações</span>
                        </div>
                    </div>
                    <div class="preview-boards" id="previewBoards"></div>
                </div>

                <div id="importProgress" class="import-progress hidden">
                    <div class="progress-bar">
                        <div id="progressFill" class="progress-fill"></div>
                    </div>
                    <div id="progressText" class="progress-text">Processando...</div>
                </div>

                <div id="importResults" class="import-results"></div>

                <div class="upload-actions">
                    <button type="button" id="importBtn" class="btn btn-primary btn-lg hidden" disabled>
                        Importar Dados
                    </button>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="panel panel-info">
            <div class="panel-header">
                <h3>Como exportar do Nextcloud Deck</h3>
            </div>
            <div class="panel-body">
                <ol class="instructions-list">
                    <li>Abra seu board no Nextcloud Deck</li>
                    <li>Clique em <strong>Menu</strong> (⋯) no canto superior direito</li>
                    <li>Selecione <strong>Download board as JSON</strong></li>
                    <li>Escolha o <strong>tipo de dados</strong> acima</li>
                    <li>Faça upload do arquivo aqui</li>
                </ol>
                <div class="info-note">
                    <strong>💡 Nota:</strong> Cards já existentes serão atualizados automaticamente.
                    Não há risco de duplicatas.
                </div>
            </div>
        </div>

        <!-- Import History -->
        @if($logs->count() > 0)
            <div class="panel">
                <div class="panel-header">
                    <h3>Histórico de Importações</h3>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Data/Hora</th>
                                <th>Arquivo</th>
                                <th>Board</th>
                                <th>Tipo</th>
                                <th>Criados</th>
                                <th>Atualizados</th>
                                <th>Ignorados</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $log->filename }}</td>
                                    <td>{{ $log->board_name ?? '-' }}</td>
                                    <td>
                                        @if($log->import_type === 'won')
                                            <span class="badge badge-success">🏆 Vitória</span>
                                        @elseif($log->import_type === 'lost')
                                            <span class="badge badge-danger">❌ Derrota</span>
                                        @elseif($log->import_type === 'tracking')
                                            <span class="badge badge-info">⚖️ Andamento</span>
                                        @else
                                            <span class="badge">-</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($log->cards_imported) }}</td>
                                    <td>{{ number_format($log->cards_updated) }}</td>
                                    <td>{{ number_format($log->cards_skipped ?? 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <style>
        .import-type-selector {
            margin-bottom: 1.5rem;
        }

        .import-type-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--text-primary);
        }

        .import-type-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .import-type-btn {
            flex: 1;
            min-width: 150px;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: 0.75rem;
            background: var(--bg-secondary);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .import-type-btn:hover {
            border-color: var(--primary);
            background: rgba(59, 130, 246, 0.1);
        }

        .import-type-btn.active {
            border-color: var(--primary);
            background: rgba(59, 130, 246, 0.15);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .import-type-btn[data-type="won"].active {
            border-color: var(--success);
            background: rgba(16, 185, 129, 0.15);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        }

        .import-type-btn[data-type="lost"].active {
            border-color: var(--danger);
            background: rgba(239, 68, 68, 0.15);
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
        }

        .type-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .type-name {
            font-weight: 600;
            color: var(--text-primary);
        }

        .type-desc {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }

        .file-info {
            background: var(--bg-secondary);
            border-radius: 0.5rem;
            padding: 1rem;
            margin: 1rem 0;
        }

        .file-details {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .file-name {
            flex: 1;
            font-weight: 500;
        }

        .file-size {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .btn-remove {
            background: none;
            border: none;
            color: var(--danger);
            cursor: pointer;
            font-size: 1.25rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }

        .btn-remove:hover {
            background: rgba(239, 68, 68, 0.1);
        }

        .preview-section {
            background: var(--bg-secondary);
            border-radius: 0.75rem;
            padding: 1.25rem;
            margin: 1rem 0;
        }

        .preview-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }

        .preview-stats {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1rem;
        }

        .preview-stat {
            text-align: center;
        }

        .preview-stat .stat-value {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .preview-stat .stat-label {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .preview-stat.stat-new .stat-value {
            color: var(--success);
        }

        .preview-stat.stat-update .stat-value {
            color: var(--warning);
        }

        .preview-boards {
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .info-note {
            background: rgba(59, 130, 246, 0.1);
            border-left: 3px solid var(--primary);
            padding: 0.75rem 1rem;
            margin-top: 1rem;
            border-radius: 0 0.5rem 0.5rem 0;
            font-size: 0.875rem;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.2);
            color: var(--success);
        }

        .badge-danger {
            background: rgba(239, 68, 68, 0.2);
            color: var(--danger);
        }

        .badge-info {
            background: rgba(59, 130, 246, 0.2);
            color: var(--primary);
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const zone = document.getElementById('uploadZone');
            const input = document.getElementById('fileInput');
            const fileInfo = document.getElementById('fileInfo');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            const removeBtn = document.getElementById('removeFile');
            const btn = document.getElementById('importBtn');
            const progress = document.getElementById('importProgress');
            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            const results = document.getElementById('importResults');
            const importTypeInput = document.getElementById('importType');
            const previewSection = document.getElementById('previewSection');
            const typeButtons = document.querySelectorAll('.import-type-btn');

            let currentFile = null;
            let selectedType = null;

            // Type selection
            typeButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    typeButtons.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    selectedType = btn.dataset.type;
                    importTypeInput.value = selectedType;
                    updateImportButton();
                });
            });

            // Drag events
            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                zone.classList.add('dragover');
            });

            zone.addEventListener('dragleave', () => {
                zone.classList.remove('dragover');
            });

            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                zone.classList.remove('dragover');
                if (e.dataTransfer.files.length > 0) {
                    handleFile(e.dataTransfer.files[0]);
                }
            });

            zone.addEventListener('click', () => input.click());

            input.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    handleFile(e.target.files[0]);
                }
            });

            removeBtn.addEventListener('click', () => {
                clearFile();
            });

            const MAX_SIZE = 50 * 1024 * 1024; // 50MB

            function handleFile(file) {
                if (!file.name.endsWith('.json')) {
                    showError('Apenas arquivos JSON são aceitos.');
                    return;
                }

                if (file.size > MAX_SIZE) {
                    showError('Arquivo muito grande (máximo: 50MB).');
                    return;
                }

                currentFile = file;
                fileName.textContent = file.name;
                fileSize.textContent = `${(file.size / 1024 / 1024).toFixed(2)} MB`;

                zone.classList.add('hidden');
                fileInfo.classList.remove('hidden');

                // Get preview
                getPreview(file);
                updateImportButton();
            }

            function clearFile() {
                currentFile = null;
                input.value = '';
                zone.classList.remove('hidden');
                fileInfo.classList.add('hidden');
                previewSection.classList.add('hidden');
                updateImportButton();
            }

            function updateImportButton() {
                const canImport = currentFile && selectedType;
                btn.classList.toggle('hidden', !canImport);
                btn.disabled = !canImport;
            }

            function showError(message) {
                results.innerHTML = `
                        <div class="result-item result-error">
                            ❌ ${message}
                        </div>
                    `;
            }

            async function getPreview(file) {
                try {
                    const formData = new FormData();
                    formData.append('file', file);

                    const res = await fetch('{{ route("import.preview") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await res.json();

                    if (data.success) {
                        document.getElementById('previewTotal').textContent = data.preview.total_cards;
                        document.getElementById('previewNew').textContent = data.preview.new_cards;
                        document.getElementById('previewExisting').textContent = data.preview.existing_cards;

                        const boardsHtml = data.preview.boards.length > 0
                            ? `Boards: ${data.preview.boards.join(', ')}`
                            : '';
                        document.getElementById('previewBoards').textContent = boardsHtml;

                        previewSection.classList.remove('hidden');
                    }
                } catch (err) {
                    console.error('Preview failed:', err);
                }
            }

            btn.addEventListener('click', async () => {
                if (!currentFile || !selectedType) return;

                btn.classList.add('hidden');
                progress.classList.remove('hidden');
                results.innerHTML = '';
                progressFill.style.width = '0%';
                progressText.textContent = 'Importando...';

                try {
                    const formData = new FormData();
                    formData.append('file', currentFile);
                    formData.append('import_type', selectedType);

                    progressFill.style.width = '30%';

                    const res = await fetch('{{ route("import.store") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    progressFill.style.width = '70%';

                    const contentType = res.headers.get("content-type");
                    if (!contentType || !contentType.includes("application/json")) {
                        throw new Error("Resposta inválida do servidor (não é JSON)");
                    }

                    const data = await res.json();

                    if (!res.ok) {
                        if (res.status === 419) {
                            window.location.reload();
                            return;
                        }
                        throw new Error(data.errors?.join(', ') || 'Erro desconhecido');
                    }

                    progressFill.style.width = '100%';

                    if (data.success) {
                        results.innerHTML = `
                                <div class="result-item result-success">
                                    ✅ <strong>${data.board_name}</strong>: 
                                    ${data.cards_created} criados, 
                                    ${data.cards_updated} atualizados, 
                                    ${data.cards_skipped} ignorados
                                </div>
                            `;

                        // Refresh page after 2 seconds to show updated history
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        throw new Error(data.errors?.join(', ') || 'Erro desconhecido');
                    }
                } catch (err) {
                    results.innerHTML = `
                            <div class="result-item result-error">
                                ❌ ${err.message}
                            </div>
                        `;
                    btn.classList.remove('hidden');
                }

                progressText.textContent = 'Concluído!';

                setTimeout(() => {
                    progress.classList.add('hidden');
                }, 2000);
            });
        });
    </script>
@endpush