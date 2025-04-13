import Chart from 'chart.js/auto';

export function initCompanyAnalytics() {
    initDailyUploadsChart();
    initGoalChartSeparate();
    initUsageChart();
    initTopUsersChart();
    initCalendar();

    function initDailyUploadsChart() {
        const ctx = document.getElementById('revenueChart').getContext('2d');

        // Создаем градиент для заливки
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(99, 102, 241, 0.4)');
        gradient.addColorStop(1, 'rgba(99, 102, 241, 0)');

        // Получаем данные из скрытого input
        const rawValue = document.getElementById('uploadedTransfers')?.value || '[]';
        let dailyUploads = [];

        try {
            dailyUploads = JSON.parse(rawValue);
        } catch (e) {
            console.error('Ошибка при парсинге uploadedTransfers:', e);
            dailyUploads = [];
        }

        // Определяем выбранный месяц для построения графика
        const calendarWidget = document.querySelector('.calendar-widget');
        let currentMonthDate;
        if (calendarWidget && calendarWidget.getAttribute('data-current-month')) {
            const currentMonthStr = calendarWidget.getAttribute('data-current-month'); // формат: "YYYY-MM"
            const [year, month] = currentMonthStr.split('-');
            currentMonthDate = new Date(parseInt(year, 10), parseInt(month, 10) - 1, 1);
        } else {
            // Если data-атрибут отсутствует, используем текущий месяц
            currentMonthDate = new Date();
            currentMonthDate.setDate(1);
        }

        // Вычисляем количество дней в выбранном месяце
        const daysInMonth = new Date(currentMonthDate.getFullYear(), currentMonthDate.getMonth() + 1, 0).getDate();
        const labels = [];
        const fullDates = [];
        const uploadCounts = [];

        // Генерируем метки и значения для каждого дня
        for (let d = 1; d <= daysInMonth; d++) {
            const date = new Date(currentMonthDate.getFullYear(), currentMonthDate.getMonth(), d);
            // Для оси X оставляем только номер дня
            labels.push(date.getDate().toString());
            // Полная дата для tooltip
            const fullDate = `${date.getDate()} ${date.toLocaleString('en-US', { month: 'long' })}`;
            fullDates.push(fullDate);

            const dateStr = date.toISOString().split('T')[0]; // Формат "YYYY-MM-DD"
            const upload = dailyUploads.find(item => item.day === dateStr);
            uploadCounts.push(upload ? upload.count : 0);
        }

        const maxValue = Math.max(...uploadCounts);
        const chartMax = maxValue < 5 ? 5 : maxValue + 1;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Files sent',
                    data: uploadCounts,
                    borderColor: '#b88cff',
                    backgroundColor: gradient,
                    tension: 0.3,
                    borderWidth: 1,
                    pointRadius: 3,
                    pointBackgroundColor: '#b88cff',
                    pointBorderColor: '#ffffff',
                    fill: true,
                    clip: false  // Отключаем обрезание элементов
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
                            // Заменяем заголовок tooltip на полную дату
                            title: function(context) {
                                // Используем индекс точки для получения полного значения из массива fullDates
                                const index = context[0].dataIndex;
                                return fullDates[index];
                            },
                            // Оставляем отображение только количества файлов
                            label: context => `Files: ${context.parsed.y}`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: {
                            color: '#babcd2',
                            autoSkip: false,       // Показываем все метки
                            maxRotation: 45,
                            minRotation: 45
                        }
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
                    legend: {display: false},
                    tooltip: {enabled: false}
                }
            }
        });
    }

    function initGoalChartSeparate() {
        const total = parseInt(document.getElementById('analytics-total').value, 10);
        const downloaded = parseInt(document.getElementById('analytics-downloaded').value, 10);
        const uploaded = parseInt(document.getElementById('analytics-uploaded').value, 10);
        const expired = parseInt(document.getElementById('analytics-expired').value, 10);
        const deleted = parseInt(document.getElementById('analytics-deleted').value, 10);

        // const total = 100
        // const downloaded = 100
        // const uploaded = 100
        // const expired = 100
        // const deleted = 100

        //
        const downloadedRatio = downloaded / total || 0;
        const uploadedRatio = uploaded / total || 0;
        const expiredRatio = expired / total || 0;
        const deletedRatio = deleted / total || 0;


        drawSingleRing('chart-sent', 1, '#b88cff', 'Sent');
        drawSingleRing('chart-waiting', uploadedRatio, '#63e', 'Waiting');
        drawSingleRing('chart-downloaded', downloadedRatio, '#63eebb', 'Downloaded');
        drawSingleRing('chart-expired', expiredRatio, '#e02367', 'Expired');
        drawSingleRing('chart-deleted', deletedRatio, '#e02323', 'Deleted');

        const percent = total > 0 ? ((downloaded / total) * 100).toFixed(1) : '0.0';
        const center = document.getElementById('goalPercentage');
        if (center) center.textContent = `${percent}%`;
    }

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
                        '#63e',
                        'rgba(99, 102, 241, 0.2)'
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
                    legend: {display: false},
                    tooltip: {enabled: false}
                }
            }
        });
    }

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

    function initCalendar() {
        const monthNames = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];

        const calendarWidget = document.querySelector('.calendar-widget');
        const calendarHeader = calendarWidget.querySelector('.calendar-header h3');
        const prevButton = calendarWidget.querySelector('.calendar-nav .prev');
        const nextButton = calendarWidget.querySelector('.calendar-nav .next');

        let currentMonthStr = calendarWidget.getAttribute('data-current-month');
        let [year, month] = currentMonthStr ? currentMonthStr.split('-') : [];
        let currentDate = new Date();

        if (year && month) {
            currentDate = new Date(parseInt(year), parseInt(month) - 1, 1);
        } else {
            currentDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
        }

        function updateCalendarHeader() {
            const displayMonth = monthNames[currentDate.getMonth()];
            const displayYear = currentDate.getFullYear();
            calendarHeader.textContent = displayMonth + ' ' + displayYear;
        }

        function formatYearMonth(date) {
            let y = date.getFullYear();
            let m = (date.getMonth() + 1).toString().padStart(2, '0');
            return `${y}-${m}`;
        }

        function updateUrlParameter(url, param, paramVal) {
            let [baseUrl, queryString] = url.split('?');
            let params = new URLSearchParams(queryString || '');
            params.set(param, paramVal);
            return `${baseUrl}?${params.toString()}`;
        }

        updateCalendarHeader();

        prevButton.addEventListener('click', function () {
            currentDate.setMonth(currentDate.getMonth() - 1);
            updateCalendarHeader();
            const newMonth = formatYearMonth(currentDate);
            window.location.href = updateUrlParameter(window.location.href, 'month', newMonth);
        });

        nextButton.addEventListener('click', function () {
            currentDate.setMonth(currentDate.getMonth() + 1);
            updateCalendarHeader();
            const newMonth = formatYearMonth(currentDate);
            window.location.href = updateUrlParameter(window.location.href, 'month', newMonth);
        });
    }
}
