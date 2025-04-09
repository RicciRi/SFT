import Chart from 'chart.js/auto';

export function initCompanyAnalytics() {
    // Initialize all charts
    initDailyUploadsChart();
    initGoalChartSeparate();
    initUsageChart();
    initTopUsersChart();
    // initCalendar();

// Revenue Chart
    function initDailyUploadsChart() {
        const ctx = document.getElementById('revenueChart').getContext('2d');

        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(99, 102, 241, 0.4)');
        gradient.addColorStop(1, 'rgba(99, 102, 241, 0)');

        const rawValue = document.getElementById('uploadedTransfers')?.value || '[]';
        let dailyUploads = [];

        try {
            dailyUploads = JSON.parse(rawValue);
        } catch (e) {
            console.error('Ошибка при парсинге uploadedTransfers:', e);
            dailyUploads = [];
        }

        const now = new Date();
        const labels = [];
        const uploadCounts = [];

        // Генерируем последние 7 дней
        for (let i = 6; i >= 0; i--) {
            const date = new Date(now);
            date.setDate(now.getDate() - i);

            const label = date.toLocaleDateString('en-US', { weekday: 'short' }); // Mon, Tue, ...
            labels.push(label);

            const dateStr = date.toISOString().split('T')[0]; // YYYY-MM-DD

            const upload = dailyUploads.find(item => item.day === dateStr);
            uploadCounts.push(upload ? upload.count : 0);
        }
        const maxValue = Math.max(...uploadCounts);
        const chartMax = maxValue < 10 ? 10 : maxValue + 1;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Files sent',
                    data: uploadCounts,
                    borderColor: '#b88cff',
                    backgroundColor: gradient,
                    tension: 0.4,
                    borderWidth: 1,
                    pointRadius: 5,
                    pointBackgroundColor: '#b88cff',
                    pointBorderColor: '#ffffff',
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1a1a1a',
                        titleColor: '#fff',
                        bodyColor: '#aaaaaa',
                        borderColor: '#383a4d',
                        borderWidth: 1,
                        displayColors: false,
                        callbacks: {
                            label: context => `Files: ${context.parsed.y}`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: { color: '#babcd2' }
                    },
                    y: {
                        beginAtZero: true,
                        min: 0,
                        max: chartMax,
                        grid: {
                            color: 'rgba(56, 58, 77, 0.5)',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#babcd2',
                            stepSize: 1
                        }
                    }
                }

            }
        });
    }

// Goal Chart (Progress Circle)
    function drawSingleRing(canvasId, value, color, label = '') {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: [label],
                datasets: [{
                    data: [value, 1 - value],
                    backgroundColor: [color, 'transparent'],
                    borderWidth: 0,
                    cutout: '97%',
                    circumference: 180,
                    rotation: -90
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            }
        });
    }

    function initGoalChartSeparate() {
        const total = parseInt(document.getElementById('analytics-total').value, 10);
        const downloaded = parseInt(document.getElementById('analytics-downloaded').value, 10);
        const uploaded = parseInt(document.getElementById('analytics-uploaded').value, 10);
        const expired = parseInt(document.getElementById('analytics-expired').value, 10);

        // const total = 100
        // const downloaded = 100
        // const uploaded = 100
        // const expired = 154
        //
        const downloadedRatio = downloaded / total || 0;
        const uploadedRatio = uploaded / total || 0;
        const expiredRatio = expired / total || 0;

        // Base ring: always 100%
        drawSingleRing('chart-sent', 1, '#b88cff', 'Sent');
        drawSingleRing('chart-waiting', uploadedRatio, '#63e', 'Waiting');
        drawSingleRing('chart-downloaded', downloadedRatio, '#63eebb', 'Downloaded');
        drawSingleRing('chart-expired', expiredRatio, '#e02367', 'Expired');

        // center text update (optional)
        const percent = total > 0 ? ((downloaded / total) * 100).toFixed(1) : '0.0';
        const center = document.getElementById('goalPercentage');
        if (center) center.textContent = `${percent}%`;
    }
// Sales Growth Chart (Circle)
    function initUsageChart() {
        const ctx = document.getElementById('salesGrowthChart').getContext('2d');

        const limitGb = parseFloat(document.getElementById('dataLimit')?.value || 1024);
        const bytesUsed = parseFloat(document.getElementById('dataUsed')?.value || 0);
        const usedGb = bytesUsed / (1024 ** 3);
        // const usedGb = 800;

        const percentUsed = Math.min((usedGb / limitGb) * 100, 100);
        const percentDisplay = percentUsed.toFixed(1);
        const rotation = -90;
        const circumference = (percentUsed / 100) * 360;

        const percentEl = document.querySelector('.growth-percentage .percentage');
        if (percentEl) {
            percentEl.textContent = `${percentDisplay}%`;
        }

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [percentUsed, 100 - percentUsed],
                    backgroundColor: [
                        '#63e',                             // Прогресс
                        'rgba(99, 102, 241, 0.2)'           // Фон
                    ],
                    borderWidth: 0,
                    cutout: '94%',
                    rotation: -90,
                    circumference: 360
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    animateRotate: true,
                    duration: 1000,
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            }
        });
    }

// Profit Chart (Bar and Line)
    function initTopUsersChart() {
        const ctx = document.getElementById('topUsersChart').getContext('2d');
        const topUsersLabels = JSON.parse(document.getElementById('topUsersLabels').value);
        const topUsersData = JSON.parse(document.getElementById('topUsersData').value);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: topUsersLabels,
                datasets: [{
                    label: 'Transfers',
                    data: topUsersData,
                    backgroundColor: ['#b88cff', '#63e', '#4213c1'],
                    barPercentage: 0.6,
                    categoryPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1a1a1a',
                        titleColor: '#fff',
                        bodyColor: '#aaaaaa',
                        borderColor: '#383a4d',
                        borderWidth: 1
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            color: '#babcd2'
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(56, 58, 77, 0.5)',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#babcd2',
                            stepSize: 1,
                            beginAtZero: true
                        }
                    }
                }
            }
        });
    }

// Initialize Calendar
//     function initCalendar() {
//         const calendarDays = document.querySelector('.calendar-days');
//         const currentDate = new Date();
//         const currentMonth = 3; // April (0-indexed)
//         const currentYear = 2025;
//         const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
//         const firstDayOfMonth = new Date(currentYear, currentMonth, 1).getDay();
//
//         // Adjust for Monday as first day (0 = Monday, 6 = Sunday)
//         const firstDayAdjusted = firstDayOfMonth === 0 ? 6 : firstDayOfMonth - 1;
//
//         // Create empty slots for days before first day of month
//         for (let i = 0; i < firstDayAdjusted; i++) {
//             const emptyDay = document.createElement('div');
//             emptyDay.classList.add('calendar-day', 'empty');
//             calendarDays.appendChild(emptyDay);
//         }
//
//         // Create days of the month
//         for (let day = 1; day <= daysInMonth; day++) {
//             const dayElement = document.createElement('div');
//             dayElement.classList.add('calendar-day');
//             dayElement.textContent = day;
//
//             // Highlight current day (April 8, 2025)
//             if (day === 8) {
//                 dayElement.classList.add('current');
//             }
//
//             calendarDays.appendChild(dayElement);
//         }
//
//         // Add event listeners for calendar navigation
//         document.querySelector('.calendar-nav-btn.prev').addEventListener('click', function () {
//             // Navigation logic would go here
//         });
//
//         document.querySelector('.calendar-nav-btn.next').addEventListener('click', function () {
//             // Navigation logic would go here
//         });
//     }
}
