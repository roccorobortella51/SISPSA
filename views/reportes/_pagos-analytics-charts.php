<?php
// app/views/reportes/_pagos-analytics-charts.php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var array $chartData */
/** @var string $startDate */
/** @var string $endDate */
/** @var array $summary */

$analyticsDataUrl = Url::to(['reportes/get-analytics-data']);
?>

<div class="row" id="analytics-section">
    <!-- Your HTML content remains exactly the same as before -->
    <!-- ... all the HTML from the previous version ... -->
</div>

<!-- Chart.js and Custom Scripts -->
<script>
    // Initialize charts when document is ready
    $(document).ready(function() {
        loadChartData();
    });

    const analyticsDataUrl = '<?= $analyticsDataUrl ?>';

    function loadChartData() {
        // Obtener parámetros actuales de los filtros
        const params = getCurrentFilterParams();

        // Mostrar loading state
        showChartLoading();

        console.log('Loading chart data from:', analyticsDataUrl);
        console.log('Params:', params);

        // Obtener datos del servidor
        $.ajax({
            url: analyticsDataUrl,
            type: 'POST',
            data: params,
            dataType: 'json',
            success: function(response) {
                console.log('Chart data response:', response);
                if (response.success && response.data) {
                    renderAllCharts(response.data);
                } else {
                    showChartError(response.message || 'No se pudieron cargar los datos para los gráficos');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading chart data:', error, xhr);
                showChartError('Error al cargar datos: ' + error);
            }
        });
    }

    function getCurrentFilterParams() {
        return {
            range: $('#date-range-selector').val(),
            date_from: $('#date-from').val(),
            date_to: $('#date-to').val(),
            custom_range: $('#date-range-selector').val() === 'custom',
            status: $('#pago-status-selector').val(),
            clinicas: $('#clinica-filter').val() || [],
            _csrf: $('meta[name="csrf-token"]').attr('content')
        };
    }

    function showChartLoading() {
        const charts = ['#revenueChart', '#statusPieChart', '#topClinicsChart', '#paymentMethodsChart'];

        charts.forEach(chartId => {
            $(chartId).html(`
            <div class="text-center p-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2" style="font-size: 1.2rem !important;">Cargando datos...</p>
            </div>
        `);
        });
    }

    function showChartError(message) {
        const charts = ['#revenueChart', '#statusPieChart', '#topClinicsChart', '#paymentMethodsChart'];

        charts.forEach(chartId => {
            $(chartId).html(`
            <div class="text-center p-4">
                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-3"></i>
                <p class="mb-0" style="font-size: 1.2rem !important;">${message}</p>
                <button class="btn btn-sm btn-primary mt-2" onclick="loadChartData()">
                    <i class="fas fa-redo me-1"></i>Reintentar
                </button>
            </div>
        `);
        });
    }

    function renderAllCharts(chartData) {
        // 1. Render Revenue Over Time Chart
        renderRevenueChart(chartData.revenue_over_time);

        // 2. Render Status Distribution Chart
        renderStatusChart(chartData.status_distribution);

        // 3. Render Top Clinics Chart
        renderTopClinicsChart(chartData.top_clinics);

        // 4. Render Payment Methods Chart
        renderPaymentMethodsChart(chartData.payment_methods);

        // 5. Update KPI statistics
        updateKPIStatistics(chartData.statistics);

        // 6. Update metadata
        updateChartMetadata(chartData.metadata);
    }

    function renderRevenueChart(data) {
        const ctx = document.createElement('canvas');
        $('#revenueChart').html(ctx);

        if (!data.labels || !data.labels.length) {
            $('#revenueChart').html(`
            <div class="text-center p-4">
                <i class="fas fa-chart-line fa-2x text-muted mb-3"></i>
                <p class="mb-0" style="font-size: 1.2rem !important;">No hay datos para el período seleccionado</p>
            </div>
        `);
            return;
        }

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Ingreso (Bs.)',
                    data: data.revenue_data,
                    borderColor: '#0078d4',
                    backgroundColor: 'rgba(0, 120, 212, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Ingreso: ' + formatCurrency(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value);
                            },
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    }
                }
            }
        });
    }

    function renderStatusChart(data) {
        const ctx = document.createElement('canvas');
        $('#statusPieChart').html(ctx);

        if (!data.labels || !data.labels.length) {
            $('#statusPieChart').html(`
            <div class="text-center p-4">
                <i class="fas fa-chart-pie fa-2x text-muted mb-3"></i>
                <p class="mb-0" style="font-size: 1.2rem !important;">No hay datos para mostrar</p>
            </div>
        `);
            return;
        }

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.counts_data,
                    backgroundColor: ['#107c10', '#ff8c00'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 12
                            },
                            padding: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = data.counts_data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.raw / total) * 100).toFixed(1);
                                return context.label + ': ' + context.raw + ' pagos (' + percentage + '%)';
                            }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    }

    function renderTopClinicsChart(data) {
        const ctx = document.createElement('canvas');
        $('#topClinicsChart').html(ctx);

        if (!data.labels || !data.labels.length) {
            $('#topClinicsChart').html(`
            <div class="text-center p-4">
                <i class="fas fa-chart-bar fa-2x text-muted mb-3"></i>
                <p class="mb-0" style="font-size: 1.2rem !important;">No hay datos de clínicas</p>
            </div>
        `);
            return;
        }

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Ingreso (Bs.)',
                    data: data.revenue_data,
                    backgroundColor: data.revenue_data.map(function(_, index) {
                        return index % 2 === 0 ? '#0078d4' : '#106ebe';
                    }),
                    borderColor: '#ffffff',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Ingreso: ' + formatCurrency(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value);
                            },
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    y: {
                        ticks: {
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    }
                }
            }
        });
    }

    function renderPaymentMethodsChart(data) {
        const ctx = document.createElement('canvas');
        $('#paymentMethodsChart').html(ctx);

        if (!data.labels || !data.labels.length) {
            $('#paymentMethodsChart').html(`
            <div class="text-center p-4">
                <i class="fas fa-credit-card fa-2x text-muted mb-3"></i>
                <p class="mb-0" style="font-size: 1.2rem !important;">No hay datos de métodos de pago</p>
            </div>
        `);
            return;
        }

        const backgroundColors = [
            '#0078d4', '#107c10', '#ff8c00', '#d13438',
            '#2c3e50', '#4a6491', '#605e5c', '#1e7e34'
        ];

        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.count_data,
                    backgroundColor: backgroundColors.slice(0, data.labels.length),
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: {
                                size: 11
                            },
                            padding: 15,
                            boxWidth: 12
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = data.count_data.reduce(function(a, b) {
                                    return a + b;
                                }, 0);
                                const percentage = ((context.raw / total) * 100).toFixed(1);
                                return context.label + ': ' + context.raw + ' pagos (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    function updateKPIStatistics(stats) {
        // Update Total Revenue KPI
        $('.kpi-total-revenue').text(formatCurrency(stats.total_revenue));
        $('.kpi-total-transactions').text(stats.total_transactions.toLocaleString());
        $('.kpi-avg-transaction').text(formatCurrency(stats.avg_transaction_value));
        $('.kpi-reconciliation-rate').text(stats.reconciliation_rate.toFixed(1) + '%');
    }

    function updateChartMetadata(metadata) {
        // Update period information
        $('.chart-period-info').text(metadata.period_label);
        $('.chart-clinics-count').text(metadata.clinicas_count);
        $('.chart-status-filter').text(metadata.status === 'todos' ? 'Todos' : metadata.status);

        // Update last updated time
        $('.last-updated-time').text('Última actualización: ' + new Date().toLocaleTimeString());
    }

    function formatCurrency(value) {
        return new Intl.NumberFormat('es-VE', {
            style: 'currency',
            currency: 'VES',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(value);
    }

    function changeChartPeriod(period) {
        // Update the main date range selector
        $('#date-range-selector').val(period).trigger('change');

        // Reload chart data
        loadChartData();

        // Show notification
        showNotification('Período cambiado a: ' + period, 'info');
    }

    function exportAnalytics() {
        const params = getCurrentFilterParams();
        const queryString = $.param(params);
        const url = '/reportes/export-analytics?' + queryString;

        window.open(url, '_blank');
    }

    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = '<div class="alert alert-' + type + ' alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; max-width: 300px;">' +
            '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'info-circle') + ' me-2"></i>' +
            message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>';

        $('body').append(notification);

        // Auto-remove after 3 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 3000);
    }

    // Add refresh button functionality
    $(document).on('click', '.refresh-charts', function() {
        loadChartData();
        showNotification('Gráficos actualizados', 'success');
    });

    // Listen for filter changes to refresh charts
    $(document).on('change', '#pago-status-selector, #clinica-filter', function() {
        // Debounce to avoid too many requests
        clearTimeout(window.chartRefreshTimeout);
        window.chartRefreshTimeout = setTimeout(loadChartData, 500);
    });
</script>

<!-- Styles for Analytics Section -->
<style>
    /* Analytics specific styles */
    #analytics-section .ms-card {
        border-radius: 10px;
        overflow: hidden;
    }

    /* Chart container styling */
    .chart-container {
        position: relative;
        height: 100%;
        min-height: 250px;
    }

    /* Smooth scroll to analytics */
    html {
        scroll-behavior: smooth;
    }

    /* Animation for charts loading */
    @keyframes chartFadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    #revenueChart,
    #statusPieChart,
    #topClinicsChart,
    #paymentMethodsChart {
        animation: chartFadeIn 0.6s ease-out;
    }

    /* Tooltip styling for charts */
    .chart-tooltip {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 0.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        font-size: 1.1rem !important;
    }

    /* Responsive charts */
    @media (max-width: 768px) {
        .chart-container {
            min-height: 200px;
        }

        #analytics-section .ms-card-body {
            padding: 1rem !important;
        }
    }
</style>