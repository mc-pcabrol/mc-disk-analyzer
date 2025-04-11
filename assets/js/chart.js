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
                    }
                }
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', renderMCCharts);
