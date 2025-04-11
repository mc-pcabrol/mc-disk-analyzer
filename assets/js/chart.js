function renderMCCharts() {
    const charts = document.querySelectorAll('canvas[data-chart]');
    charts.forEach(canvas => {
        const ctx = canvas.getContext('2d');
        if (!ctx || typeof Chart === 'undefined') return;

        const data = JSON.parse(canvas.dataset.chart);
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Taille',
                    data: data.sizes,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const mo = value / 1024 / 1024;
                                if (mo < 1024) {
                                    return `${context.label}: ${mo.toFixed(1)} Mo`;
                                } else {
                                    return `${context.label}: ${(mo / 1024).toFixed(2)} Go`;
                                }
                            }
                        }
                    }
                }
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', renderMCCharts);
