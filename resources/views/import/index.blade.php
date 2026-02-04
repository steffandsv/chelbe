@extends('layouts.app')

@section('title', 'Importar JSON')

@section('content')
    <div class="import-container">
        <!-- Upload Panel -->
        <div class="panel upload-panel">
            <div class="panel-header">
                <h3>Upload de JSON do Trello</h3>
            </div>
            <div class="panel-body">
                <div class="upload-zone" id="uploadZone">
                    <div class="upload-icon">📁</div>
                    <div class="upload-text">Arraste e solte seus arquivos JSON aqui</div>
                    <div class="upload-subtext">ou clique para selecionar (máx. 50MB)</div>
                    <input type="file" id="fileInput" class="upload-input" accept=".json" multiple>
                </div>

                <div id="filesList" class="files-list"></div>

                <div id="importProgress" class="import-progress hidden">
                    <div class="progress-bar">
                        <div id="progressFill" class="progress-fill"></div>
                    </div>
                    <div id="progressText" class="progress-text">Processando...</div>
                </div>

                <div id="importResults" class="import-results"></div>

                <div class="upload-actions">
                    <button type="button" id="importBtn" class="btn btn-primary btn-lg hidden">
                        Importar Arquivos
                    </button>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="panel panel-info">
            <div class="panel-header">
                <h3>Como exportar do Trello</h3>
            </div>
            <div class="panel-body">
                <ol class="instructions-list">
                    <li>Abra seu board no Trello</li>
                    <li>Clique em <strong>Menu</strong> (⋯) no canto superior direito</li>
                    <li>Selecione <strong>More</strong> → <strong>Print and Export</strong></li>
                    <li>Escolha <strong>Export as JSON</strong></li>
                    <li>Faça upload do arquivo aqui</li>
                </ol>
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
                                <th>Importados</th>
                                <th>Atualizados</th>
                                <th>Labels</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $log->filename }}</td>
                                    <td>{{ $log->board_name ?? '-' }}</td>
                                    <td>{{ number_format($log->cards_imported) }}</td>
                                    <td>{{ number_format($log->cards_updated) }}</td>
                                    <td>{{ number_format($log->labels_imported) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const zone = document.getElementById('uploadZone');
            const input = document.getElementById('fileInput');
            const list = document.getElementById('filesList');
            const btn = document.getElementById('importBtn');
            const progress = document.getElementById('importProgress');
            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            const results = document.getElementById('importResults');

            let files = [];

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
                handleFiles(e.dataTransfer.files);
            });

            input.addEventListener('change', (e) => {
                handleFiles(e.target.files);
            });

            const MAX_SIZE = 50 * 1024 * 1024; // 50MB

            function handleFiles(newFiles) {
                const validFiles = [];
                [...newFiles].forEach(f => {
                    if (!f.name.endsWith('.json')) return;
                    
                    if (f.size > MAX_SIZE) {
                        results.innerHTML += `
                            <div class="result-item result-error">
                                ❌ ${f.name}: Arquivo muito grande (Max: 50MB).
                            </div>
                        `;
                        return;
                    }
                    validFiles.push(f);
                });

                files = [...files, ...validFiles];
                updateFilesList();
            }

            function updateFilesList() {
                list.innerHTML = files.map((f, i) => `
                <div class="file-item">
                    <span class="file-icon">📄</span>
                    <span class="file-name">${f.name}</span>
                    <span class="file-size">${(f.size / 1024 / 1024).toFixed(2)} MB</span>
                </div>
            `).join('');

                btn.classList.toggle('hidden', files.length === 0);
            }

            btn.addEventListener('click', async () => {
                if (files.length === 0) return;

                btn.classList.add('hidden');
                progress.classList.remove('hidden');
                results.innerHTML = '';

                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const pct = Math.round((i / files.length) * 100);
                    progressFill.style.width = pct + '%';
                    progressText.textContent = `Importando ${file.name}...`;

                    try {
                        const formData = new FormData();
                        formData.append('file', file);

                        const res = await fetch('{{ route("import.store") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        // Check content type
                        const contentType = res.headers.get("content-type");
                        if (!contentType || !contentType.includes("application/json")) {
                            throw new Error("Resposta inválida do servidor (não é JSON)");
                        }

                        const data = await res.json();

                        if (!res.ok) {
                            if (res.status === 419) {
                                // Session expired: Reload immediately and stop processing
                                window.location.reload();
                                return; 
                            }
                             throw new Error(data.message || data.errors?.join(', ') || 'Erro desconhecido');
                        }

                        if (data.success) {
                            results.innerHTML += `
                            <div class="result-item result-success">
                                ✅ ${file.name}: ${data.imported} importados, ${data.updated} atualizados
                            </div>
                        `;
                        } else {
                            // Backup for success:false inside 200 OK (if API changes)
                            throw new Error(data.errors?.join(', ') || 'Erro desconhecido');
                        }
                    } catch (err) {
                        results.innerHTML += `
                        <div class="result-item result-error">
                            ❌ ${file.name}: ${err.message}
                        </div>
                    `;
                    }
                }

                progressFill.style.width = '100%';
                progressText.textContent = 'Importação concluída!';

                files = [];
                list.innerHTML = '';

                setTimeout(() => {
                    progress.classList.add('hidden');
                }, 2000);
            });
        });
    </script>
@endpush