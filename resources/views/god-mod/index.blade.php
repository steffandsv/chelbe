@extends('layouts.app')

@section('title', '🔮 GOD-MOD')

@section('content')
    <div class="god-mod-container">
        <!-- Stats Banner -->
        <div class="stats-banner">
            <div class="stat-item">
                <span class="stat-value">{{ $stats['cards'] }}</span>
                <span class="stat-label">Cards</span>
            </div>
            <div class="stat-item">
                <span class="stat-value">{{ $stats['boards'] }}</span>
                <span class="stat-label">Boards</span>
            </div>
            <div class="stat-item">
                <span class="stat-value">{{ $stats['stacks'] }}</span>
                <span class="stat-label">Stacks</span>
            </div>
            <div class="stat-item">
                <span class="stat-value">{{ $stats['analysts'] }}</span>
                <span class="stat-label">Analistas</span>
            </div>
            <div class="stat-item">
                <span class="stat-value">{{ $stats['portals'] }}</span>
                <span class="stat-label">Portais</span>
            </div>
        </div>

        <!-- ER Diagram -->
        <div class="panel">
            <div class="panel-header">
                <h2>📊 Diagrama de Relacionamento</h2>
            </div>
            <div class="panel-body">
                <div class="er-diagram">
                    <pre class="code-block">
    ┌─────────────────┐     ┌─────────────────┐     ┌─────────────────────────────────────────┐
    │     BOARDS      │────▶│     STACKS      │────▶│                 CARDS                   │
    ├─────────────────┤     ├─────────────────┤     ├─────────────────────────────────────────┤
    │ id              │     │ id              │     │ id (PK)                                 │
    │ deck_board_id   │     │ board_id (FK)   │     │ deck_card_id (UK)                       │
    │ title           │     │ deck_stack_id   │     │ title, description                      │
    │ owner           │     │ title           │     │ analyst, user_status, defeat_reason     │
    │ color           │     │ stack_order     │     │ viabilidade_tatica, complexidade...     │
    │ archived        │     └─────────────────┘     │ portal, orgao, valor_estimado           │
    └─────────────────┘                             └───────────┬───────────┬─────────────────┘
                                                                │           │
                                                   ┌────────────▼──┐  ┌─────▼─────────────┐
                                                   │ ASSIGNED_USERS│  │   CARD_LABELS     │
                                                   ├───────────────┤  ├───────────────────┤
                                                   │ card_id (FK)  │  │ card_id (FK)      │
                                                   │ uid           │  │ category, value   │
                                                   │ displayname   │  │ raw_label, color  │
                                                   └───────────────┘  └───────────────────┘
                                                                │
                                                   ┌────────────▼──┐
                                                   │  CUSTOM_TAGS  │
                                                   ├───────────────┤
                                                   │ card_id (FK)  │
                                                   │ tag_name      │
                                                   │ tag_value     │
                                                   └───────────────┘</pre>
                </div>
            </div>
        </div>

        <!-- Data Dictionary -->
        <div class="panel">
            <div class="panel-header">
                <h2>📖 Dicionário de Dados</h2>
            </div>
            <div class="panel-body">
                <!-- Cards Table -->
                <details class="table-doc" open>
                    <summary class="table-name">
                        <span class="icon">📋</span> cards
                        <span class="badge badge-primary">Principal</span>
                    </summary>
                    <div class="table-fields">
                        <table class="data-table compact">
                            <thead>
                                <tr>
                                    <th>Campo</th>
                                    <th>Tipo</th>
                                    <th>Descrição</th>
                                    <th>Uso em IA/Gráficos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>id</code></td>
                                    <td>bigint</td>
                                    <td>Chave primária</td>
                                    <td>Identificador único</td>
                                </tr>
                                <tr>
                                    <td><code>deck_card_id</code></td>
                                    <td>bigint</td>
                                    <td>ID original do Nextcloud Deck</td>
                                    <td>Deduplicação na sincronização</td>
                                </tr>
                                <tr>
                                    <td><code>title</code></td>
                                    <td>varchar(500)</td>
                                    <td>Título da licitação</td>
                                    <td>Busca textual, NLP</td>
                                </tr>
                                <tr>
                                    <td><code>description</code></td>
                                    <td>longText</td>
                                    <td>Descrição completa (markdown)</td>
                                    <td>Extração de contexto por IA</td>
                                </tr>
                                <tr class="highlight-row">
                                    <td><code>analyst</code></td>
                                    <td>varchar(100)</td>
                                    <td>Analista responsável</td>
                                    <td>📊 GROUP BY analyst</td>
                                </tr>
                                <tr class="highlight-row">
                                    <td><code>user_status</code></td>
                                    <td>enum</td>
                                    <td>pending / tracking / won / lost</td>
                                    <td>📊 GROUP BY user_status</td>
                                </tr>
                                <tr>
                                    <td><code>defeat_reason</code></td>
                                    <td>text</td>
                                    <td>Motivo da derrota</td>
                                    <td>Análise de padrões de perda</td>
                                </tr>
                                <tr>
                                    <td><code>user_notes</code></td>
                                    <td>text</td>
                                    <td>Notas do usuário</td>
                                    <td>Contexto adicional</td>
                                </tr>
                                <tr class="highlight-row">
                                    <td><code>valor_estimado</code></td>
                                    <td>decimal(15,2)</td>
                                    <td>Valor do pregão</td>
                                    <td>📊 SUM(), AVG() por período</td>
                                </tr>
                                <tr>
                                    <td><code>viabilidade_tatica</code></td>
                                    <td>varchar(20)</td>
                                    <td>Alta / Média / Baixa</td>
                                    <td>Score IPM estratégico</td>
                                </tr>
                                <tr>
                                    <td><code>complexidade_operacional</code></td>
                                    <td>varchar(20)</td>
                                    <td>Alta / Média / Baixa</td>
                                    <td>Score IPM operacional</td>
                                </tr>
                                <tr>
                                    <td><code>lucratividade_potencial</code></td>
                                    <td>varchar(20)</td>
                                    <td>Alta / Média / Baixa</td>
                                    <td>Score IPM financeiro</td>
                                </tr>
                                <tr>
                                    <td><code>chance_vitoria</code></td>
                                    <td>varchar(20)</td>
                                    <td>Alta / Média / Baixa</td>
                                    <td>Score IPM probabilidade</td>
                                </tr>
                                <tr>
                                    <td><code>risco_operacional</code></td>
                                    <td>varchar(20)</td>
                                    <td>Alta / Média / Baixa</td>
                                    <td>Score IPM risco</td>
                                </tr>
                                <tr>
                                    <td><code>ipm_score</code></td>
                                    <td>varchar(20)</td>
                                    <td>Score final IPM</td>
                                    <td>Decisão estratégica final</td>
                                </tr>
                                <tr class="highlight-row">
                                    <td><code>portal</code></td>
                                    <td>varchar(100)</td>
                                    <td>Portal de origem</td>
                                    <td>📊 GROUP BY portal</td>
                                </tr>
                                <tr>
                                    <td><code>orgao</code></td>
                                    <td>varchar(255)</td>
                                    <td>Órgão público</td>
                                    <td>Filtro geográfico/institucional</td>
                                </tr>
                                <tr class="highlight-row">
                                    <td><code>extracted_date</code></td>
                                    <td>date</td>
                                    <td>Data de extração</td>
                                    <td>📊 Timeline, tendências</td>
                                </tr>
                                <tr>
                                    <td><code>due_date</code></td>
                                    <td>date</td>
                                    <td>Prazo limite</td>
                                    <td>Alertas de vencimento</td>
                                </tr>
                                <tr>
                                    <td><code>import_type</code></td>
                                    <td>varchar(20)</td>
                                    <td>lost / won / tracking</td>
                                    <td>Contexto de origem do import</td>
                                </tr>
                                <tr>
                                    <td><code>board_name</code></td>
                                    <td>varchar(255)</td>
                                    <td>Nome do board original</td>
                                    <td>Segmentação por board</td>
                                </tr>
                                <tr>
                                    <td><code>list_name</code></td>
                                    <td>varchar(255)</td>
                                    <td>Nome da lista/coluna</td>
                                    <td>Posição no funil</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </details>

                <!-- Other Tables -->
                <details class="table-doc">
                    <summary class="table-name">
                        <span class="icon">🏢</span> boards
                    </summary>
                    <div class="table-fields">
                        <table class="data-table compact">
                            <thead>
                                <tr>
                                    <th>Campo</th>
                                    <th>Tipo</th>
                                    <th>Descrição</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>id</code></td>
                                    <td>bigint</td>
                                    <td>PK</td>
                                </tr>
                                <tr>
                                    <td><code>deck_board_id</code></td>
                                    <td>bigint</td>
                                    <td>ID único do Deck</td>
                                </tr>
                                <tr>
                                    <td><code>title</code></td>
                                    <td>varchar</td>
                                    <td>Nome do board</td>
                                </tr>
                                <tr>
                                    <td><code>owner</code></td>
                                    <td>varchar</td>
                                    <td>Proprietário</td>
                                </tr>
                                <tr>
                                    <td><code>color</code></td>
                                    <td>varchar</td>
                                    <td>Cor de destaque</td>
                                </tr>
                                <tr>
                                    <td><code>archived</code></td>
                                    <td>boolean</td>
                                    <td>Status de arquivamento</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </details>

                <details class="table-doc">
                    <summary class="table-name">
                        <span class="icon">📚</span> stacks
                    </summary>
                    <div class="table-fields">
                        <table class="data-table compact">
                            <thead>
                                <tr>
                                    <th>Campo</th>
                                    <th>Tipo</th>
                                    <th>Descrição</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>id</code></td>
                                    <td>bigint</td>
                                    <td>PK</td>
                                </tr>
                                <tr>
                                    <td><code>board_id</code></td>
                                    <td>bigint</td>
                                    <td>FK → boards</td>
                                </tr>
                                <tr>
                                    <td><code>deck_stack_id</code></td>
                                    <td>bigint</td>
                                    <td>ID único do Deck</td>
                                </tr>
                                <tr>
                                    <td><code>title</code></td>
                                    <td>varchar</td>
                                    <td>Nome da coluna</td>
                                </tr>
                                <tr>
                                    <td><code>stack_order</code></td>
                                    <td>int</td>
                                    <td>Ordem de exibição</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </details>

                <details class="table-doc">
                    <summary class="table-name">
                        <span class="icon">👥</span> assigned_users
                    </summary>
                    <div class="table-fields">
                        <table class="data-table compact">
                            <thead>
                                <tr>
                                    <th>Campo</th>
                                    <th>Tipo</th>
                                    <th>Descrição</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>id</code></td>
                                    <td>bigint</td>
                                    <td>PK</td>
                                </tr>
                                <tr>
                                    <td><code>card_id</code></td>
                                    <td>bigint</td>
                                    <td>FK → cards</td>
                                </tr>
                                <tr>
                                    <td><code>uid</code></td>
                                    <td>varchar</td>
                                    <td>ID do usuário</td>
                                </tr>
                                <tr>
                                    <td><code>displayname</code></td>
                                    <td>varchar</td>
                                    <td>Nome de exibição</td>
                                </tr>
                                <tr>
                                    <td><code>participant_type</code></td>
                                    <td>tinyint</td>
                                    <td>Tipo de participação</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </details>

                <details class="table-doc">
                    <summary class="table-name">
                        <span class="icon">🏷️</span> card_labels
                    </summary>
                    <div class="table-fields">
                        <table class="data-table compact">
                            <thead>
                                <tr>
                                    <th>Campo</th>
                                    <th>Tipo</th>
                                    <th>Descrição</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>id</code></td>
                                    <td>bigint</td>
                                    <td>PK</td>
                                </tr>
                                <tr>
                                    <td><code>card_id</code></td>
                                    <td>bigint</td>
                                    <td>FK → cards</td>
                                </tr>
                                <tr>
                                    <td><code>category</code></td>
                                    <td>varchar</td>
                                    <td>Categoria normalizada</td>
                                </tr>
                                <tr>
                                    <td><code>value</code></td>
                                    <td>varchar</td>
                                    <td>Valor normalizado</td>
                                </tr>
                                <tr>
                                    <td><code>raw_label</code></td>
                                    <td>varchar</td>
                                    <td>Label original</td>
                                </tr>
                                <tr>
                                    <td><code>color</code></td>
                                    <td>varchar</td>
                                    <td>Cor da label</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </details>

                <details class="table-doc">
                    <summary class="table-name">
                        <span class="icon">🔖</span> custom_tags
                    </summary>
                    <div class="table-fields">
                        <table class="data-table compact">
                            <thead>
                                <tr>
                                    <th>Campo</th>
                                    <th>Tipo</th>
                                    <th>Descrição</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>id</code></td>
                                    <td>bigint</td>
                                    <td>PK</td>
                                </tr>
                                <tr>
                                    <td><code>card_id</code></td>
                                    <td>bigint</td>
                                    <td>FK → cards</td>
                                </tr>
                                <tr>
                                    <td><code>tag_name</code></td>
                                    <td>varchar</td>
                                    <td>Nome da tag</td>
                                </tr>
                                <tr>
                                    <td><code>tag_value</code></td>
                                    <td>varchar</td>
                                    <td>Valor da tag</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </details>
            </div>
        </div>

        <!-- Query Recipes -->
        <div class="panel">
            <div class="panel-header">
                <h2>🎯 Receitas de Cruzamento</h2>
                <span class="subtitle">Copie e use para gerar gráficos!</span>
            </div>
            <div class="panel-body">
                <div class="query-grid">
                    <!-- Recipe 1 -->
                    <div class="query-card">
                        <h4>📊 Cards por Status</h4>
                        <p class="query-desc">Distribuição do funil de vendas</p>
                        <pre class="code-block" onclick="copyToClipboard(this)">SELECT user_status, COUNT(*) as total
    FROM cards
    GROUP BY user_status
    ORDER BY total DESC;</pre>
                        <div class="eloquent-version">
                            <span class="label">Eloquent:</span>
                            <code>Card::groupBy('user_status')->selectRaw('user_status, COUNT(*) as total')->get()</code>
                        </div>
                    </div>

                    <!-- Recipe 2 -->
                    <div class="query-card">
                        <h4>📊 Win Rate por Analista</h4>
                        <p class="query-desc">Performance individual</p>
                        <pre class="code-block" onclick="copyToClipboard(this)">SELECT 
        analyst,
        SUM(CASE WHEN user_status='won' THEN 1 ELSE 0 END) as won,
        SUM(CASE WHEN user_status='lost' THEN 1 ELSE 0 END) as lost,
        ROUND(SUM(CASE WHEN user_status='won' THEN 1 ELSE 0 END) * 100.0 / 
              NULLIF(SUM(CASE WHEN user_status IN ('won','lost') THEN 1 ELSE 0 END), 0), 2) as win_rate
    FROM cards
    WHERE analyst IS NOT NULL
    GROUP BY analyst;</pre>
                    </div>

                    <!-- Recipe 3 -->
                    <div class="query-card">
                        <h4>📊 Valor por Portal</h4>
                        <p class="query-desc">Volume financeiro por origem</p>
                        <pre class="code-block" onclick="copyToClipboard(this)">SELECT 
        portal,
        COUNT(*) as total_cards,
        SUM(valor_estimado) as valor_total,
        AVG(valor_estimado) as valor_medio
    FROM cards
    WHERE portal IS NOT NULL
    GROUP BY portal
    ORDER BY valor_total DESC;</pre>
                    </div>

                    <!-- Recipe 4 -->
                    <div class="query-card">
                        <h4>📊 Tendência Mensal</h4>
                        <p class="query-desc">Volume ao longo do tempo</p>
                        <pre class="code-block" onclick="copyToClipboard(this)">SELECT 
        DATE_FORMAT(extracted_date, '%Y-%m') as mes,
        COUNT(*) as total,
        SUM(CASE WHEN user_status='won' THEN 1 ELSE 0 END) as ganhos,
        SUM(CASE WHEN user_status='lost' THEN 1 ELSE 0 END) as perdidos
    FROM cards
    WHERE extracted_date IS NOT NULL
    GROUP BY mes
    ORDER BY mes DESC
    LIMIT 12;</pre>
                    </div>

                    <!-- Recipe 5 -->
                    <div class="query-card">
                        <h4>📊 Distribuição IPM</h4>
                        <p class="query-desc">Score estratégico</p>
                        <pre class="code-block" onclick="copyToClipboard(this)">SELECT 
        viabilidade_tatica,
        COUNT(*) as total,
        ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM cards WHERE viabilidade_tatica IS NOT NULL), 2) as percentual
    FROM cards
    WHERE viabilidade_tatica IS NOT NULL
    GROUP BY viabilidade_tatica;</pre>
                    </div>

                    <!-- Recipe 6 -->
                    <div class="query-card">
                        <h4>📊 Motivos de Derrota</h4>
                        <p class="query-desc">Análise de padrões de perda</p>
                        <pre class="code-block" onclick="copyToClipboard(this)">SELECT 
        defeat_reason,
        COUNT(*) as ocorrencias
    FROM cards
    WHERE user_status = 'lost' 
      AND defeat_reason IS NOT NULL 
      AND defeat_reason != ''
    GROUP BY defeat_reason
    ORDER BY ocorrencias DESC
    LIMIT 10;</pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- Eloquent Cheatsheet -->
        <div class="panel">
            <div class="panel-header">
                <h2>⚡ Eloquent Quick Reference</h2>
            </div>
            <div class="panel-body">
                <div class="cheatsheet">
                    <div class="cheat-item">
                        <code>Card::with(['labels', 'customTags'])->get()</code>
                        <span>Carregar com relacionamentos</span>
                    </div>
                    <div class="cheat-item">
                        <code>Card::where('user_status', 'won')->count()</code>
                        <span>Contar ganhos</span>
                    </div>
                    <div class="cheat-item">
                        <code>Card::whereNotNull('analyst')->distinct('analyst')->pluck('analyst')</code>
                        <span>Lista de analistas únicos</span>
                    </div>
                    <div class="cheat-item">
                        <code>Card::whereBetween('extracted_date', [$start, $end])->get()</code>
                        <span>Filtrar por período</span>
                    </div>
                    <div class="cheat-item">
                        <code>Card::where('valor_estimado', '>', 100000)->sum('valor_estimado')</code>
                        <span>Soma de valores altos</span>
                    </div>
                    <div class="cheat-item">
                        <code>$card->labels()->where('category', 'status')->first()</code>
                        <span>Label específica de um card</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .god-mod-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .stats-banner {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .stat-item {
            background: linear-gradient(135deg, var(--bg-tertiary), var(--bg-secondary));
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            text-align: center;
            flex: 1;
            min-width: 120px;
        }

        .stat-value {
            display: block;
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            line-height: 1;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .er-diagram {
            overflow-x: auto;
            padding: 1rem;
            background: var(--bg-primary);
            border-radius: 8px;
        }

        .er-diagram pre {
            margin: 0;
            font-size: 0.8rem;
            line-height: 1.4;
            color: var(--text-secondary);
        }

        .table-doc {
            margin-bottom: 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
        }

        .table-name {
            padding: 1rem;
            background: var(--bg-tertiary);
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table-name:hover {
            background: var(--bg-secondary);
        }

        .table-name .icon {
            font-size: 1.2rem;
        }

        .table-fields {
            padding: 1rem;
        }

        .data-table.compact {
            font-size: 0.85rem;
        }

        .data-table.compact td,
        .data-table.compact th {
            padding: 0.5rem 0.75rem;
        }

        .highlight-row {
            background: rgba(99, 102, 241, 0.1);
        }

        .highlight-row td:nth-child(4) {
            color: var(--primary);
            font-weight: 500;
        }

        .badge {
            font-size: 0.65rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .badge-primary {
            background: var(--primary);
            color: white;
        }

        .query-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
        }

        .query-card {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.25rem;
        }

        .query-card h4 {
            margin: 0 0 0.5rem 0;
            color: var(--text-primary);
        }

        .query-desc {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin: 0 0 1rem 0;
        }

        .code-block {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
            font-size: 0.8rem;
            overflow-x: auto;
            cursor: pointer;
            transition: border-color 0.2s;
            white-space: pre;
            font-family: 'Fira Code', 'Monaco', monospace;
        }

        .code-block:hover {
            border-color: var(--primary);
        }

        .eloquent-version {
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid var(--border-color);
            font-size: 0.75rem;
        }

        .eloquent-version .label {
            color: var(--text-secondary);
        }

        .eloquent-version code {
            display: block;
            margin-top: 0.25rem;
            color: var(--primary);
            word-break: break-all;
        }

        .cheatsheet {
            display: grid;
            gap: 0.75rem;
        }

        .cheat-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1rem;
            background: var(--bg-tertiary);
            border-radius: 8px;
            flex-wrap: wrap;
        }

        .cheat-item code {
            background: var(--bg-primary);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            color: var(--primary);
            flex-shrink: 0;
        }

        .cheat-item span {
            color: var(--text-secondary);
            font-size: 0.85rem;
        }

        .subtitle {
            font-size: 0.85rem;
            color: var(--text-secondary);
            font-weight: normal;
        }
    </style>
@endsection

@push('scripts')
    <script>
        function copyToClipboard(element) {
            const text = element.textContent;
            navigator.clipboard.writeText(text).then(() => {
                const original = element.style.borderColor;
                element.style.borderColor = '#22c55e';
                setTimeout(() => {
                    element.style.borderColor = original;
                }, 1000);
            });
        }
    </script>
@endpush