
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('mc-disk-chart').getContext('2d');
    if (!ctx || typeof Chart === 'undefined') return;

    const data = JSON.parse(document.getElementById('mc-disk-chart').dataset.chart);
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Poids des dossiers',
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
