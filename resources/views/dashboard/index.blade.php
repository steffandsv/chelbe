@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <!-- KPI Cards -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon">📊</div>
            <div class="kpi-content">
                <span class="kpi-value">{{ number_format($stats['total']) }}</span>
                <span class="kpi-label">Total Cards</span>
            </div>
        </div>

        <div class="kpi-card kpi-won">
            <div class="kpi-icon">🏆</div>
            <div class="kpi-content">
                <span class="kpi-value">{{ number_format($stats['won']) }}</span>
                <span class="kpi-label">Vencidos</span>
            </div>
        </div>

        <div class="kpi-card kpi-lost">
            <div class="kpi-icon">❌</div>
            <div class="kpi-content">
                <span class="kpi-value">{{ number_format($stats['lost']) }}</span>
                <span class="kpi-label">Perdidos</span>
            </div>
        </div>

        <div class="kpi-card kpi-tracking">
            <div class="kpi-icon">👁️</div>
            <div class="kpi-content">
                <span class="kpi-value">{{ number_format($stats['tracking']) }}</span>
                <span class="kpi-label">Acompanhando</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon">⏳</div>
            <div class="kpi-content">
                <span class="kpi-value">{{ number_format($stats['pending']) }}</span>
                <span class="kpi-label">Pendentes</span>
            </div>
        </div>

        <div class="kpi-card kpi-rate">
            <div class="kpi-icon">📈</div>
            <div class="kpi-content">
                <span class="kpi-value">{{ $stats['win_rate'] }}%</span>
                <span class="kpi-label">Win Rate</span>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="charts-grid">
        <div class="chart-card chart-wide">
            <div class="chart-header">
                <h3>Performance Semanal</h3>
                <select id="weeklyChartScale" class="chart-control">
                    <option value="linear">Linear</option>
                    <option value="logarithmic">Log</option>
                </select>
            </div>
            <div class="chart-container">
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3>Status Atual</h3>
            </div>
            <div class="chart-container chart-doughnut">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3>Ranking Analistas</h3>
            </div>
            <div class="chart-container">
                <canvas id="analystChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Cards -->
    <div class="panel">
        <div class="panel-header">
            <h3>Cards Recentes</h3>
            <a href="{{ route('cards.index') }}" class="btn btn-ghost btn-small">Ver todos →</a>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Board</th>
                        <th>Analista</th>
                        <th>Status</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentCards as $card)
                        <tr>
                            <td class="cell-title">
                                <span title="{{ $card->title }}">{{ Str::limit($card->title, 50) }}</span>
                            </td>
                            <td>{{ $card->board_name ?? '-' }}</td>
                            <td>{{ $card->analyst ?? '-' }}</td>
                            <td>
                                <span class="status-badge status-{{ $card->user_status }}">
                                    {{ ucfirst($card->user_status) }}
                                </span>
                            </td>
                            <td>{{ $card->extracted_date?->format('d/m/Y') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">
                                Nenhum card importado. <a href="{{ route('import.index') }}">Importar JSON</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.dashboardData = {
            stats: @json($stats)
        };
    </script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
@endpush