/**
 * Dashboard Charts - JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts if on dashboard
    if (document.getElementById('weeklyChart')) {
        initDashboardCharts();
    }
});

async function initDashboardCharts() {
    Chart.defaults.color = '#9ca3af';
    Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.1)';
    
    await Promise.all([
        createStatusChart(),
        createWeeklyChart(),
        createAnalystChart()
    ]);
    
    setupChartControls();
}

async function createStatusChart() {
    const ctx = document.getElementById('statusChart');
    if (!ctx || !window.dashboardData) return;
    
    const stats = window.dashboardData.stats;
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Vencidos', 'Perdidos', 'Acompanhando', 'Pendentes'],
            datasets: [{
                data: [stats.won, stats.lost, stats.tracking, stats.pending],
                backgroundColor: [
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(107, 114, 128, 0.8)'
                ],
                borderColor: ['#10b981', '#ef4444', '#f59e0b', '#6b7280'],
                borderWidth: 2,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { padding: 16, usePointStyle: true } }
            },
            cutout: '65%'
        }
    });
}

async function createWeeklyChart() {
    const ctx = document.getElementById('weeklyChart');
    if (!ctx) return;
    
    try {
        const response = await fetch('/api/stats/weekly');
        const data = await response.json();
        
        window.weeklyChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(d => d.label),
                datasets: [
                    {
                        label: 'Vencidos',
                        data: data.map(d => d.won),
                        backgroundColor: 'rgba(16, 185, 129, 0.8)',
                        borderColor: '#10b981',
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        label: 'Perdidos',
                        data: data.map(d => d.lost),
                        backgroundColor: 'rgba(239, 68, 68, 0.8)',
                        borderColor: '#ef4444',
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        label: 'Total',
                        data: data.map(d => d.total),
                        type: 'line',
                        borderColor: '#818cf8',
                        backgroundColor: 'rgba(129, 140, 248, 0.1)',
                        borderWidth: 2,
                        pointBackgroundColor: '#818cf8',
                        pointRadius: 4,
                        fill: true,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { usePointStyle: true, padding: 16 } }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { maxRotation: 45, minRotation: 45, font: { size: 10 } } },
                    y: { beginAtZero: true, grid: { color: 'rgba(255, 255, 255, 0.05)' } }
                }
            }
        });
    } catch (error) {
        console.error('Error loading weekly data:', error);
    }
}

async function createAnalystChart() {
    const ctx = document.getElementById('analystChart');
    if (!ctx) return;
    
    try {
        const response = await fetch('/api/stats/analysts');
        const data = await response.json();
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(d => d.analyst || 'Sem analista'),
                datasets: [{
                    label: 'Win Rate (%)',
                    data: data.map(d => parseFloat(d.win_rate) || 0),
                    backgroundColor: 'rgba(129, 140, 248, 0.8)',
                    borderColor: '#818cf8',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { position: 'top', labels: { usePointStyle: true, padding: 16 } }
                },
                scales: {
                    x: { max: 100, grid: { color: 'rgba(255, 255, 255, 0.05)' } }
                }
            }
        });
    } catch (error) {
        console.error('Error loading analyst data:', error);
    }
}

function setupChartControls() {
    const scaleSelect = document.getElementById('weeklyChartScale');
    if (scaleSelect && window.weeklyChartInstance) {
        scaleSelect.addEventListener('change', function() {
            window.weeklyChartInstance.options.scales.y.type = this.value;
            window.weeklyChartInstance.update();
        });
    }
}

// Modal functions
window.openModal = function(modalId) {
    document.getElementById(modalId)?.classList.remove('hidden');
};

window.closeModal = function() {
    document.querySelectorAll('.modal').forEach(m => m.classList.add('hidden'));
};

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-backdrop')) {
        closeModal();
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
