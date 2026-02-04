@extends('layouts.app')

@section('title', 'Analytics')

@section('content')
    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-header">
                <h3>Performance Mensal</h3>
            </div>
            <div class="chart-container">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3>Distribuição por Portal</h3>
            </div>
            <div class="chart-container">
                <canvas id="portalChart"></canvas>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h3>Distribuição das Métricas</h3>
        </div>
        <div class="panel-body">
            <div id="metricsAnalysis" class="metrics-analysis">
                <p class="text-muted">Carregando análise de métricas...</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/analytics.js') }}"></script>
@endpush