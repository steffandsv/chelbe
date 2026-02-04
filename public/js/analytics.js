/**
 * Analytics Charts JavaScript
 */

document.addEventListener('DOMContentLoaded', async function() {
    await Promise.all([
        createMonthlyChart(),
        createPortalChart(),
        loadMetricsAnalysis()
    ]);
});

async function createMonthlyChart() {
    const ctx = document.getElementById('monthlyChart');
    if (!ctx) return;
    
    try {
        const response = await fetch('/api/stats/monthly');
        const data = await response.json();
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(d => d.label),
                datasets: [
                    {
                        label: 'Win Rate (%)',
                        data: data.map(d => d.win_rate),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y-rate'
                    },
                    {
                        label: 'Total Cards',
                        data: data.map(d => d.total),
                        borderColor: '#818cf8',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        tension: 0.4,
                        yAxisID: 'y-total'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    'y-rate': { type: 'linear', position: 'left', max: 100, title: { display: true, text: 'Win Rate (%)' } },
                    'y-total': { type: 'linear', position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Total' } }
                }
            }
        });
    } catch (error) {
        console.error('Error loading monthly data:', error);
    }
}

async function createPortalChart() {
    const ctx = document.getElementById('portalChart');
    if (!ctx) return;
    
    try {
        const response = await fetch('/api/stats/portals');
        const data = await response.json();
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(d => d.portal),
                datasets: [{
                    label: 'Cards',
                    data: data.map(d => d.total),
                    backgroundColor: [
                        'rgba(129, 140, 248, 0.8)',
                        'rgba(167, 139, 250, 0.8)',
                        'rgba(192, 132, 252, 0.8)',
                        'rgba(217, 125, 254, 0.8)',
                        'rgba(232, 121, 249, 0.8)'
                    ],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    } catch (error) {
        console.error('Error loading portal data:', error);
    }
}

async function loadMetricsAnalysis() {
    const container = document.getElementById('metricsAnalysis');
    if (!container) return;
    
    try {
        const response = await fetch('/api/stats/metrics');
        const data = await response.json();
        
        const labels = {
            viabilidade_tatica: 'Viabilidade Tática',
            complexidade_operacional: 'Complexidade Operacional',
            lucratividade_potencial: 'Lucratividade Potencial',
            chance_vitoria: 'Chance de Vitória',
            risco_operacional: 'Risco Operacional'
        };
        
        container.innerHTML = `
            <style>
                .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; }
                .metric-dist h4 { font-size: 0.875rem; margin-bottom: 0.75rem; color: var(--text-secondary); }
                .metric-bars { display: flex; flex-direction: column; gap: 0.5rem; }
                .metric-row { display: flex; justify-content: space-between; padding: 0.5rem 0.75rem; border-radius: 6px; font-size: 0.875rem; }
                .metric-row.alta { background: rgba(16, 185, 129, 0.15); color: #10b981; }
                .metric-row.média { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
                .metric-row.baixa { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
            </style>
            <div class="metrics-grid">
                ${Object.entries(data).map(([key, values]) => `
                    <div class="metric-dist">
                        <h4>${labels[key] || key}</h4>
                        <div class="metric-bars">
                            ${values.map(v => `
                                <div class="metric-row ${v.value.toLowerCase()}">
                                    <span>${v.value}</span>
                                    <strong>${v.count}</strong>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    } catch (error) {
        container.innerHTML = `<p style="color:var(--text-muted)">Erro: ${error.message}</p>`;
    }
}
