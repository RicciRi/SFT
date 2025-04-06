import Chart from 'chart.js/auto';

export function initAnalytics() {
    const container = document.getElementById('analyticsChartContainer');
    if (!container) return;

    const chartId = container.getAttribute('data-chart-id');
    const labels = JSON.parse(container.getAttribute('data-labels'));
    const dataValues = JSON.parse(container.getAttribute('data-data'));

    const ctx = document.getElementById(chartId).getContext('2d');

    const chartData = {
        labels: labels,
        datasets: [{
            label: 'Active Users',
            data: dataValues,
            borderColor: '#7f56d9',
            backgroundColor: 'rgba(127, 86, 217, 0.15)',
            borderWidth: 0.8,
            tension: 0.35,
            pointRadius: 3,
            pointHoverRadius: 4,
            pointBackgroundColor: '#7f56d9',
            pointBorderColor: '#fff',
            fill: true
        }]
    };

    const chartOptions = {
        responsive: false,
        layout: {
            padding: {
                top: 5,
                bottom: 5
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)',
                    drawTicks: false
                },
                ticks: {
                    color: '#dfe1f4',
                    font: { size: 10, family: 'monospace' },
                    padding: 14,

                }
            },
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    color: '#dfe1f4',
                    font: { size: 10, family: 'monospace' },
                    maxRotation: 0,
                    minRotation: 0
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: '#fff',
                titleColor: '#000',
                bodyColor: '#000',
                borderColor: '#ccc',
                borderWidth: 1,
                padding: 8,
                titleFont: { size: 11, weight: 'bold' },
                bodyFont: { size: 10 }
            }
        }
    };

    new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: chartOptions
    });
}
