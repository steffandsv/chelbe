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

        <!-- Section 1: System Context -->
        <div class="panel doc-section" id="section-context">
            <div class="panel-header">
                <h2>🌐 Contexto do Sistema</h2>
                <button class="btn btn-small btn-ghost"
                    onclick="downloadSection('section-context', 'contexto_sistema.md')">📥 Download MD</button>
            </div>
            <div class="panel-body">
                <div class="context-info">
                    <p><strong>MABUS Analytics</strong> é um sistema de inteligência para licitações públicas que integra
                        dados do Nextcloud Deck para análise estratégica de oportunidades B2G (Business-to-Government).</p>

                    <h4>📊 Fluxo de Dados</h4>
                    <pre class="diagram-block">
    ┌──────────────────┐     ┌──────────────────┐     ┌──────────────────┐
    │  Nextcloud Deck  │────▶│  JSON Export     │────▶│   MABUS Import   │
    │  (Kanban Board)  │     │  (Manual/API)    │     │   (DeckImporter) │
    └──────────────────┘     └──────────────────┘     └────────┬─────────┘
                                                               │
                        ┌──────────────────────────────────────┼──────────────────────────────────────┐
                        │                                      ▼                                      │
                        │  ┌─────────┐   ┌─────────┐   ┌─────────┐   ┌─────────────┐   ┌──────────┐  │
                        │  │ Boards  │──▶│ Stacks  │──▶│ Cards   │──▶│ Card Labels │   │Custom Tags│ │
                        │  └─────────┘   └─────────┘   └─────────┘   └─────────────┘   └──────────┘  │
                        │                                      │                                      │
                        │               POSTGRESQL DATABASE    │         ASSIGNED USERS               │
                        └──────────────────────────────────────┼──────────────────────────────────────┘
                                                               │
                  ┌────────────────────────────────────────────┼────────────────────────────────────────────┐
                  │                                            ▼                                            │
                  │   ┌────────────────┐    ┌────────────────┐    ┌────────────────┐    ┌────────────────┐  │
                  │   │   Dashboard    │    │   Cards CRUD   │    │   Analytics    │    │    GOD-MOD     │  │
                  │   │   (Stats/KPI)  │    │  (List/Edit)   │    │   (Graphs)     │    │  (You Are Here)│  │
                  │   └────────────────┘    └────────────────┘    └────────────────┘    └────────────────┘  │
                  │                              LARAVEL APPLICATION LAYER                                  │
                  └─────────────────────────────────────────────────────────────────────────────────────────┘</pre>

                    <h4>🎯 Objetivos do Sistema</h4>
                    <ul>
                        <li><strong>Sincronizar:</strong> Importar cards do Nextcloud Deck preservando hierarquia e
                            metadados</li>
                        <li><strong>Analisar:</strong> Calcular métricas IPM (Viabilidade, Complexidade, Lucratividade,
                            Chance, Risco)</li>
                        <li><strong>Rastrear:</strong> Acompanhar status de licitações (Pendente → Acompanhando →
                            Ganho/Perdido)</li>
                        <li><strong>Reportar:</strong> Gerar análises de win rate, volume por portal, tendências</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Section 2: ER Diagram -->
        <div class="panel doc-section" id="section-er">
            <div class="panel-header">
                <h2>📊 Diagrama de Relacionamento (ER)</h2>
                <button class="btn btn-small btn-ghost" onclick="downloadSection('section-er', 'er_diagram.md')">📥 Download
                    MD</button>
            </div>
            <div class="panel-body">
                <div class="er-diagram">
                    <pre class="diagram-block">
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

                <h4>📐 Cardinalidades</h4>
                <ul class="relationship-list">
                    <li><code>boards</code> → <code>stacks</code>: 1:N (Um board tem várias colunas)</li>
                    <li><code>stacks</code> → <code>cards</code>: 1:N (Uma coluna tem vários cards)</li>
                    <li><code>cards</code> → <code>assigned_users</code>: 1:N (Card pode ter múltiplos responsáveis)</li>
                    <li><code>cards</code> → <code>card_labels</code>: 1:N (Card pode ter múltiplas labels)</li>
                    <li><code>cards</code> → <code>custom_tags</code>: 1:N (Tags personalizadas)</li>
                </ul>
            </div>
        </div>

        <!-- Section 3: Card Lifecycle -->
        <div class="panel doc-section" id="section-lifecycle">
            <div class="panel-header">
                <h2>🔄 Ciclo de Vida do Card</h2>
                <button class="btn btn-small btn-ghost"
                    onclick="downloadSection('section-lifecycle', 'ciclo_vida_card.md')">📥 Download MD</button>
            </div>
            <div class="panel-body">
                <pre class="diagram-block">
                                        ┌─────────────┐
                                        │   IMPORT    │
                                        │  (via JSON) │
                                        └──────┬──────┘
                                               │
                                               ▼
                                        ┌─────────────┐
                               ┌───────▶│   PENDING   │◀──────── Status inicial
                               │        └──────┬──────┘
                               │               │
                               │   Análise     │  Analista avalia
                               │   necessária  │  viabilidade
                               │               ▼
                               │        ┌─────────────┐
                               │        │  TRACKING   │◀──────── Acompanhando licitação
                               │        └──────┬──────┘
                               │               │
                               │               │ Resultado do pregão
                               │               │
                        ┌──────┴──────┐   ┌────▼────┐
                        │             │   │         │
                        ▼             ▼   ▼         ▼
                 ┌─────────────┐  ┌─────────────┐
                 │     WON     │  │    LOST     │
                 │   ✅ Ganho   │  │   ❌ Perdido │
                 └─────────────┘  └──────┬──────┘
                                         │
                                         ▼
                                  ┌─────────────┐
                                  │defeat_reason│ ◀──── Motivo registrado
                                  │  (obrigatório)│
                                  └─────────────┘</pre>

                <h4>📊 Métricas IPM (Índice de Priorização MABUS)</h4>
                <div class="ipm-grid">
                    <div class="ipm-item">
                        <span class="ipm-name">Viabilidade Tática</span>
                        <span class="ipm-desc">Capacidade de atender os requisitos técnicos</span>
                    </div>
                    <div class="ipm-item">
                        <span class="ipm-name">Complexidade Operacional</span>
                        <span class="ipm-desc">Dificuldade de execução/entrega</span>
                    </div>
                    <div class="ipm-item">
                        <span class="ipm-name">Lucratividade Potencial</span>
                        <span class="ipm-desc">Margem esperada do contrato</span>
                    </div>
                    <div class="ipm-item">
                        <span class="ipm-name">Chance de Vitória</span>
                        <span class="ipm-desc">Probabilidade de ganhar a licitação</span>
                    </div>
                    <div class="ipm-item">
                        <span class="ipm-name">Risco Operacional</span>
                        <span class="ipm-desc">Riscos associados à execução</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 4: Import Flow -->
        <div class="panel doc-section" id="section-import">
            <div class="panel-header">
                <h2>📥 Fluxo de Importação</h2>
                <button class="btn btn-small btn-ghost"
                    onclick="downloadSection('section-import', 'fluxo_importacao.md')">📥 Download MD</button>
            </div>
            <div class="panel-body">
                <pre class="diagram-block">
    ┌─────────────────────────────────────────────────────────────────────────┐
    │                        DECK IMPORTER SERVICE                            │
    └────────────────────────────────────┬────────────────────────────────────┘
                                         │
             ┌───────────────────────────┼───────────────────────────┐
             ▼                           ▼                           ▼
    ┌─────────────────┐         ┌─────────────────┐         ┌─────────────────┐
    │  Parse JSON     │         │  Upsert Board   │         │  Process Stacks │
    │  - Validate     │────────▶│  - deck_board_id│────────▶│  - Per board    │
    │  - Extract data │         │  - Title/Owner  │         │  - Order        │
    └─────────────────┘         └─────────────────┘         └────────┬────────┘
                                                                      │
             ┌────────────────────────────────────────────────────────┘
             ▼
    ┌─────────────────┐         ┌─────────────────┐         ┌─────────────────┐
    │  Process Cards  │         │  Parse Labels   │         │  Extract IPM    │
    │  - deck_card_id │────────▶│  - Normalize    │────────▶│  - 5 métricas   │
    │  - All fields   │         │  - Category     │         │  - Score final  │
    └─────────────────┘         └─────────────────┘         └────────┬────────┘
                                                                      │
                                         ┌────────────────────────────┘
                                         ▼
                                ┌─────────────────┐
                                │  Assign Users   │
                                │  - Multi-assign │
                                │  - Displayname  │
                                └─────────────────┘</pre>

                <h4>🔑 Estratégia de Deduplicação</h4>
                <div class="info-box">
                    <p>O sistema usa <code>deck_card_id</code> como chave única para evitar duplicatas:</p>
                    <pre class="code-inline">Card::updateOrCreate(
        ['deck_card_id' => $cardData['deck_card_id']],
        [...$allCardFields]
    );</pre>
                </div>
            </div>
        </div>

        <!-- Section 5: Data Dictionary -->
        <div class="panel doc-section" id="section-dictionary">
            <div class="panel-header">
                <h2>📖 Dicionário de Dados</h2>
                <button class="btn btn-small btn-ghost"
                    onclick="downloadSection('section-dictionary', 'dicionario_dados.md')">📥 Download MD</button>
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
                                    <td><code>board_name</code></td>
                                    <td>varchar(255)</td>
                                    <td>Nome do board original</td>
                                    <td>Segmentação por board</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </details>

                <!-- Other Tables -->
                <details class="table-doc">
                    <summary class="table-name"><span class="icon">🏢</span> boards</summary>
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
                                    <td><code>archived</code></td>
                                    <td>boolean</td>
                                    <td>Status de arquivamento</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </details>

                <details class="table-doc">
                    <summary class="table-name"><span class="icon">📚</span> stacks</summary>
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
                    <summary class="table-name"><span class="icon">👥</span> assigned_users</summary>
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
                            </tbody>
                        </table>
                    </div>
                </details>

                <details class="table-doc">
                    <summary class="table-name"><span class="icon">🏷️</span> card_labels</summary>
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
                            </tbody>
                        </table>
                    </div>
                </details>

                <details class="table-doc">
                    <summary class="table-name"><span class="icon">🔖</span> custom_tags</summary>
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

        <!-- Section 6: Query Recipes -->
        <div class="panel doc-section" id="section-queries">
            <div class="panel-header">
                <h2>🎯 Receitas de Cruzamento</h2>
                <button class="btn btn-small btn-ghost"
                    onclick="downloadSection('section-queries', 'receitas_queries.md')">📥 Download MD</button>
            </div>
            <div class="panel-body">
                <p class="section-desc">Copie e use para gerar gráficos! Clique no código para copiar.</p>
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

        <!-- Section 7: Eloquent Cheatsheet -->
        <div class="panel doc-section" id="section-eloquent">
            <div class="panel-header">
                <h2>⚡ Eloquent Quick Reference</h2>
                <button class="btn btn-small btn-ghost"
                    onclick="downloadSection('section-eloquent', 'eloquent_cheatsheet.md')">📥 Download MD</button>
            </div>
            <div class="panel-body">
                <div class="cheatsheet">
                    <div class="cheat-item">
                        <code onclick="copyToClipboard(this)">Card::with(['labels', 'customTags'])->get()</code>
                        <span>Carregar com relacionamentos</span>
                    </div>
                    <div class="cheat-item">
                        <code onclick="copyToClipboard(this)">Card::where('user_status', 'won')->count()</code>
                        <span>Contar ganhos</span>
                    </div>
                    <div class="cheat-item">
                        <code
                            onclick="copyToClipboard(this)">Card::whereNotNull('analyst')->distinct('analyst')->pluck('analyst')</code>
                        <span>Lista de analistas únicos</span>
                    </div>
                    <div class="cheat-item">
                        <code
                            onclick="copyToClipboard(this)">Card::whereBetween('extracted_date', [$start, $end])->get()</code>
                        <span>Filtrar por período</span>
                    </div>
                    <div class="cheat-item">
                        <code
                            onclick="copyToClipboard(this)">Card::where('valor_estimado', '>', 100000)->sum('valor_estimado')</code>
                        <span>Soma de valores altos</span>
                    </div>
                    <div class="cheat-item">
                        <code onclick="copyToClipboard(this)">$card->labels()->where('category', 'status')->first()</code>
                        <span>Label específica de um card</span>
                    </div>
                    <div class="cheat-item">
                        <code
                            onclick="copyToClipboard(this)">Card::selectRaw('analyst, COUNT(*) as total')->groupBy('analyst')->orderByDesc('total')->get()</code>
                        <span>Top analistas por volume</span>
                    </div>
                    <div class="cheat-item">
                        <code onclick="copyToClipboard(this)">Board::with(['stacks.cards'])->get()</code>
                        <span>Hierarquia completa</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 8: API Endpoints -->
        <div class="panel doc-section" id="section-api">
            <div class="panel-header">
                <h2>🔗 Endpoints da API</h2>
                <button class="btn btn-small btn-ghost" onclick="downloadSection('section-api', 'api_endpoints.md')">📥
                    Download MD</button>
            </div>
            <div class="panel-body">
                <table class="data-table compact">
                    <thead>
                        <tr>
                            <th>Método</th>
                            <th>Endpoint</th>
                            <th>Descrição</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="method-get">GET</span></td>
                            <td><code>/cards</code></td>
                            <td>Listar cards com filtros</td>
                        </tr>
                        <tr>
                            <td><span class="method-get">GET</span></td>
                            <td><code>/cards/create</code></td>
                            <td>Form de criação</td>
                        </tr>
                        <tr>
                            <td><span class="method-post">POST</span></td>
                            <td><code>/cards</code></td>
                            <td>Criar novo card</td>
                        </tr>
                        <tr>
                            <td><span class="method-get">GET</span></td>
                            <td><code>/cards/{id}/edit</code></td>
                            <td>Form de edição</td>
                        </tr>
                        <tr>
                            <td><span class="method-put">PUT</span></td>
                            <td><code>/cards/{id}</code></td>
                            <td>Atualizar card</td>
                        </tr>
                        <tr>
                            <td><span class="method-delete">DELETE</span></td>
                            <td><code>/cards/{id}</code></td>
                            <td>Excluir card</td>
                        </tr>
                        <tr>
                            <td><span class="method-get">GET</span></td>
                            <td><code>/api/cards/{id}</code></td>
                            <td>Detalhes JSON</td>
                        </tr>
                        <tr>
                            <td><span class="method-patch">PATCH</span></td>
                            <td><code>/api/cards/{id}</code></td>
                            <td>Update parcial (inline edit)</td>
                        </tr>
                        <tr>
                            <td><span class="method-get">GET</span></td>
                            <td><code>/api/cards/{id}/tags</code></td>
                            <td>Listar tags</td>
                        </tr>
                        <tr>
                            <td><span class="method-post">POST</span></td>
                            <td><code>/api/cards/{id}/tags</code></td>
                            <td>Adicionar tag</td>
                        </tr>
                        <tr>
                            <td><span class="method-delete">DELETE</span></td>
                            <td><code>/api/tags/{id}</code></td>
                            <td>Remover tag</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Download All Button -->
        <div class="download-all-section">
            <button class="btn btn-primary btn-lg" onclick="downloadAllSections()">
                📥 Baixar TODA Documentação (Markdown)
            </button>
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
            background: linear-gradient(135deg, var(--bg-glass), var(--bg-secondary));
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
            color: var(--accent-primary);
            line-height: 1;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .doc-section {
            margin-bottom: 2rem;
        }

        .doc-section .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .diagram-block {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
            font-size: 0.75rem;
            line-height: 1.4;
            color: var(--text-secondary);
            overflow-x: auto;
            font-family: 'Fira Code', 'Monaco', monospace;
            white-space: pre;
            margin: 1rem 0;
        }

        .context-info ul {
            margin: 1rem 0;
            padding-left: 1.5rem;
        }

        .context-info li {
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
        }

        .context-info li strong {
            color: var(--text-primary);
        }

        .er-diagram {
            overflow-x: auto;
        }

        .relationship-list {
            margin: 1rem 0;
            padding-left: 1.5rem;
        }

        .relationship-list li {
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
        }

        .relationship-list code {
            background: var(--bg-primary);
            padding: 0.125rem 0.375rem;
            border-radius: 4px;
            font-size: 0.85rem;
        }

        .ipm-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .ipm-item {
            background: var(--bg-glass);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
        }

        .ipm-name {
            display: block;
            font-weight: 600;
            color: var(--accent-primary);
            margin-bottom: 0.25rem;
        }

        .ipm-desc {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .info-box {
            background: rgba(129, 140, 248, 0.1);
            border: 1px solid rgba(129, 140, 248, 0.3);
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .info-box p {
            margin: 0 0 0.5rem 0;
            color: var(--text-secondary);
        }

        .code-inline {
            background: var(--bg-primary);
            padding: 0.75rem 1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            display: block;
            overflow-x: auto;
            color: var(--accent-primary);
            font-family: 'Fira Code', monospace;
        }

        .table-doc {
            margin-bottom: 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
        }

        .table-name {
            padding: 1rem;
            background: var(--bg-glass);
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
            color: var(--accent-primary);
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
            background: var(--accent-primary);
            color: white;
        }

        .section-desc {
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
        }

        .query-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
        }

        .query-card {
            background: var(--bg-glass);
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
            border-color: var(--accent-primary);
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
            color: var(--accent-primary);
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
            background: var(--bg-glass);
            border-radius: 8px;
            flex-wrap: wrap;
        }

        .cheat-item code {
            background: var(--bg-primary);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            color: var(--accent-primary);
            flex-shrink: 0;
            cursor: pointer;
        }

        .cheat-item code:hover {
            background: var(--bg-secondary);
        }

        .cheat-item span {
            color: var(--text-secondary);
            font-size: 0.85rem;
        }

        .method-get {
            color: #10b981;
            font-weight: 600;
        }

        .method-post {
            color: #f59e0b;
            font-weight: 600;
        }

        .method-put,
        .method-patch {
            color: #3b82f6;
            font-weight: 600;
        }

        .method-delete {
            color: #ef4444;
            font-weight: 600;
        }

        .download-all-section {
            text-align: center;
            padding: 2rem 0;
            border-top: 1px solid var(--border-color);
            margin-top: 2rem;
        }

        .btn-lg {
            padding: 1rem 2rem;
            font-size: 1.1rem;
        }
    </style>

    <script>
            function copyToClipboard(element) {
                const text = element.innerText || element.textContent;
                navigator.clipboard.writeText(text).then(() => {
                    const original = element.style.borderColor;
                    element.style.borderColor = '#10b981';
                    setTimeout(() => {
                        element.style.borderColor = original;
                    }, 1000);
                });
            }

            function downloadSection(sectionId, filename) {
                const section = document.getElementById(sectionId);
                const markdown = convertToMarkdown(section);
                downloadMarkdown(markdown, filename);
            }

            function convertToMarkdown(element) {
                const title = element.querySelector('.panel-header h2')?.textContent || 'Section';
                let md = `# ${title}\n\n`;

                // Get all text content, diagrams, and tables
                const body = element.querySelector('.panel-body');
                if (!body) return md;

                // Process diagrams
                body.querySelectorAll('.diagram-block').forEach(diagram => {
                    md += '```\n' + diagram.textContent.trim() + '\n```\n\n';
                });

                // Process paragraphs
                body.querySelectorAll('p').forEach(p => {
                    if (!p.closest('.query-card') && !p.closest('.cheat-item')) {
                        md += p.textContent.trim() + '\n\n';
                    }
                });

                // Process headers
                body.querySelectorAll('h4').forEach(h => {
                    md += `## ${h.textContent.trim()}\n\n`;
                });

                // Process lists
                body.querySelectorAll('ul').forEach(ul => {
                    ul.querySelectorAll('li').forEach(li => {
                        md += `- ${li.textContent.trim()}\n`;
                    });
                    md += '\n';
                });

                // Process tables
                body.querySelectorAll('table.data-table').forEach(table => {
                    const headers = [...table.querySelectorAll('th')].map(th => th.textContent.trim());
                    if (headers.length > 0) {
                        md += '| ' + headers.join(' | ') + ' |\n';
                        md += '| ' + headers.map(() => '---').join(' | ') + ' |\n';

                        table.querySelectorAll('tbody tr').forEach(row => {
                            const cells = [...row.querySelectorAll('td')].map(td => td.textContent.trim());
                            md += '| ' + cells.join(' | ') + ' |\n';
                        });
                        md += '\n';
                    }
                });

                // Process code blocks
                body.querySelectorAll('.code-block').forEach(code => {
                    md += '```sql\n' + code.textContent.trim() + '\n```\n\n';
                });

                // Process cheatsheet items
                body.querySelectorAll('.cheat-item').forEach(item => {
                    const code = item.querySelector('code')?.textContent || '';
                    const desc = item.querySelector('span')?.textContent || '';
                    md += `- \`${code}\` - ${desc}\n`;
                });

                return md;
            }

            function downloadMarkdown(content, filename) {
                const blob = new Blob([content], { type: 'text/markdown' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            }

            function downloadAllSections() {
                const sections = document.querySelectorAll('.doc-section');
                let allMarkdown = '# MABUS Analytics - Documentação Completa\n\n';
                allMarkdown += `Gerado em: ${new Date().toLocaleString('pt-BR')}\n\n---\n\n`;

                sections.forEach(section => {
                    allMarkdown += convertToMarkdown(section) + '\n---\n\n';
                });

                downloadMarkdown(allMarkdown, 'mabus_documentacao_completa.md');
            }
    </script>
@endsection