@extends('layouts.app')

@section('title', 'Dashboard de Gestão de Riscos')

@section('content')
<div class="analytics-container">
    
    <!-- Upload Section (if no stats) -->
    @if(!isset($stats))
    <div class="panel upload-panel" style="max-width: 600px; margin: 40px auto;">
        <div class="panel-header">
            <h3>📑 Analisar Arquivo de Derrotas</h3>
        </div>
        <div class="panel-body">
            <form action="{{ route('analytics.analyze') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label>Selecione o log (ex: ❌Derrotas❌.json)</label>
                    <input type="file" name="file" class="form-control" accept=".json" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Gerar Relatório de Riscos</button>
            </form>
        </div>
    </div>
    @else

    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <h2>📊 Análise de Risco: {{ $filename }}</h2>
        <a href="{{ route('analytics.index') }}" class="btn btn-outline">Testar Outro Arquivo</a>
    </div>

    <!-- Alert / Insight -->
    @if($stats['recommend_pause'])
    <div class="alert alert-warning risk-alert">
        <div class="alert-icon">⚠️</div>
        <div class="alert-content">
            <h4>Pausa Estratégica Recomendada</h4>
            <p>O sistema detectou uma alta densidade de derrotas consecutivas (Max: {{ $stats['max_consecutive'] }}) ou concentração em curto período. Considere uma pausa de 15-30 minutos.</p>
        </div>
    </div>
    @endif

    <!-- KPI Cards -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-title">Total de Perdas</div>
            <div class="kpi-value text-danger">{{ $stats['total_losses'] }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">Prejuízo Estimado</div>
            <div class="kpi-value">R$ {{ number_format($stats['total_value'], 2, ',', '.') }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">Hora Crítica</div>
            <div class="kpi-value">{{ $stats['critical_hour'] }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">Taxa de Quebra (Seq.)</div>
            <div class="kpi-value">{{ $stats['max_consecutive'] }}</div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="charts-grid">
        <!-- Timeline -->
        <div class="panel chart-panel">
            <div class="panel-header">Histórico de Derrotas (Dia a Dia)</div>
            <div class="panel-body">
                <canvas id="timelineChart"></canvas>
            </div>
        </div>

        <!-- Radar / Hourly -->
        <div class="panel chart-panel">
            <div class="panel-header">Distribuição por Horário</div>
            <div class="panel-body">
                <canvas id="hourlyChart"></canvas>
            </div>
        </div>

        <!-- Weekly Comparison -->
        <div class="panel chart-panel">
            <div class="panel-header">Semana Atual vs. Anterior</div>
            <div class="panel-body">
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>
    </div>

    @endif
</div>

<!-- Styles -->
<style>
    .analytics-container { padding: 20px; color: #eaeaeb; }
    .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    
    .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .kpi-card { background: #1e1e1e; padding: 20px; border-radius: 8px; border: 1px solid #333; }
    .kpi-title { color: #888; font-size: 0.9em; margin-bottom: 5px; }
    .kpi-value { font-size: 1.8em; font-weight: bold; }
    .text-danger { color: #ff5f5f; }

    .risk-alert { background: rgba(255, 166, 0, 0.15); border: 1px solid orange; color: orange; padding: 15px; border-radius: 8px; display: flex; gap: 15px; margin-bottom: 30px; align-items: center; }
    .alert-icon { font-size: 2em; }
    
    .charts-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; }
    .chart-panel { background: #1e1e1e; border-radius: 8px; padding: 15px; border: 1px solid #333; }
    .panel-header { font-weight: bold; margin-bottom: 15px; color: #ccc; }
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@if(isset($stats))
<script>
    // Data passed from controller
    const timelineData = @json($stats['timeline']);
    const hourlyData = @json($stats['hourly']);
    const weeklyData = @json($stats['weekly_comparison']);

    // Common Config
    Chart.defaults.color = '#888';
    Chart.defaults.borderColor = '#333';

    // 1. Timeline Chart
    new Chart(document.getElementById('timelineChart'), {
        type: 'line',
        data: {
            labels: Object.keys(timelineData),
            datasets: [{
                label: 'Derrotas',
                data: Object.values(timelineData),
                borderColor: '#ff5f5f',
                backgroundColor: 'rgba(255, 95, 95, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });

    // 2. Hourly Chart (Radar or Bar)
    new Chart(document.getElementById('hourlyChart'), {
        type: 'bar',
        data: {
            labels: Array.from({length: 24}, (_, i) => i + 'h'),
            datasets: [{
                label: 'Derrotas por Hora',
                data: hourlyData,
                backgroundColor: '#ffaa00'
            }]
        },
        options: { 
            responsive: true,
            scales: { y: { beginAtZero: true } }
        }
    });

    // 3. Weekly Chart
    new Chart(document.getElementById('weeklyChart'), {
        type: 'bar',
        data: {
            labels: ['Semana Passada', 'Semana Atual'],
            datasets: [{
                label: 'Comparativo',
                data: [weeklyData.last, weeklyData.current],
                backgroundColor: ['#444', '#ff5f5f']
            }]
        },
        options: { responsive: true }
    });
</script>
@endif

@endsection