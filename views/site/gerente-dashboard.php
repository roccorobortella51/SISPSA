<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\View;

$this->title = 'Dashboard Gerencial - SISPSA';
$this->params['breadcrumbs'][] = $this->title;

// Register Chart.js
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js', ['position' => View::POS_HEAD]);
?>

<style>
    :root {
        --primary: #2c3e50;
        --secondary: #3498db;
        --success: #27ae60;
        --warning: #f39c12;
        --danger: #e74c3c;
        --info: #3498db;
        --light: #ecf0f1;
        --dark: #2c3e50;
        --purple: #9b59b6;
        --orange: #e67e22;
    }

    body {
        background-color: #f8f9fa;
    }

    .dashboard-container {
        padding: 20px;
    }

    /* Header Styles */
    .dashboard-header {
        margin-bottom: 30px;
        padding: 20px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        border-radius: 15px;
        color: white;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .header-title {
        font-size: 2rem;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .header-subtitle {
        font-size: 1rem;
        opacity: 0.9;
    }

    .header-date {
        font-size: 0.9rem;
        opacity: 0.8;
        text-align: right;
    }

    /* Stat Cards */
    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        border: 1px solid rgba(0, 0, 0, 0.05);
        position: relative;
        overflow: hidden;
        cursor: help;
        /* Indicates tooltip is available */
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
    }

    .stat-icon {
        font-size: 2.5rem;
        color: var(--secondary);
        margin-bottom: 10px;
        opacity: 0.8;
    }

    .stat-label {
        font-size: 0.9rem;
        color: #7f8c8d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }

    .stat-label i {
        margin-left: 5px;
        font-size: 0.8rem;
        color: var(--secondary);
        opacity: 0.6;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary);
        line-height: 1.2;
        margin-bottom: 5px;
    }

    .stat-trend {
        font-size: 0.8rem;
        color: #27ae60;
    }

    .stat-trend.negative {
        color: #e74c3c;
    }

    .stat-trend.warning {
        color: #f39c12;
    }

    /* Status Badges */
    .status-badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-align: center;
        min-width: 80px;
        cursor: help;
    }

    .status-activo {
        background: rgba(39, 174, 96, 0.1);
        color: #27ae60;
        border: 1px solid rgba(39, 174, 96, 0.2);
    }

    .status-suspendido {
        background: rgba(231, 76, 60, 0.1);
        color: #e74c3c;
        border: 1px solid rgba(231, 76, 60, 0.2);
    }

    .status-pendiente {
        background: rgba(243, 156, 18, 0.1);
        color: #f39c12;
        border: 1px solid rgba(243, 156, 18, 0.2);
    }

    .status-inactivo {
        background: rgba(149, 165, 166, 0.1);
        color: #7f8c8d;
        border: 1px solid rgba(149, 165, 166, 0.2);
    }

    .status-solvente {
        background: rgba(39, 174, 96, 0.1);
        color: #27ae60;
        border: 1px solid rgba(39, 174, 96, 0.2);
    }

    .status-insolvente {
        background: rgba(231, 76, 60, 0.1);
        color: #e74c3c;
        border: 1px solid rgba(231, 76, 60, 0.2);
    }

    /* Chart Cards */
    .chart-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .chart-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--light);
        cursor: help;
    }

    .chart-title i:last-child {
        margin-left: 5px;
        font-size: 0.9rem;
        color: var(--secondary);
        opacity: 0.6;
    }

    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }

    /* Table Styles */
    .table-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .table-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--light);
        cursor: help;
    }

    .table-title i:last-child {
        margin-left: 5px;
        font-size: 0.9rem;
        color: var(--secondary);
        opacity: 0.6;
    }

    .dashboard-table {
        width: 100%;
        border-collapse: collapse;
    }

    .dashboard-table th {
        background-color: var(--light);
        color: var(--primary);
        font-weight: 600;
        font-size: 0.9rem;
        padding: 12px;
        text-align: left;
        border-bottom: 2px solid #dee2e6;
        cursor: help;
    }

    .dashboard-table th i {
        margin-left: 5px;
        font-size: 0.8rem;
        color: var(--secondary);
        opacity: 0.6;
    }

    .dashboard-table td {
        padding: 12px;
        border-bottom: 1px solid #dee2e6;
        color: #34495e;
    }

    .dashboard-table tr:hover {
        background-color: rgba(52, 152, 219, 0.05);
    }

    /* Progress Bar */
    .progress {
        height: 8px;
        border-radius: 4px;
        background-color: var(--light);
        margin-top: 10px;
        cursor: help;
    }

    .progress-bar {
        background: linear-gradient(90deg, var(--secondary), var(--primary));
        border-radius: 4px;
    }

    /* Quick Actions */
    .quick-actions {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .action-btn {
        display: block;
        padding: 15px;
        margin-bottom: 10px;
        background: var(--light);
        border-radius: 10px;
        color: var(--primary);
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .action-btn:hover {
        background: var(--secondary);
        color: white;
        transform: translateX(5px);
        text-decoration: none;
    }

    .action-btn i {
        margin-right: 10px;
        width: 20px;
    }

    /* Loading State */
    .loading {
        position: relative;
        opacity: 0.6;
        pointer-events: none;
    }

    .loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 30px;
        height: 30px;
        margin: -15px 0 0 -15px;
        border: 3px solid var(--light);
        border-top-color: var(--secondary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .header-title {
            font-size: 1.5rem;
            text-align: center;
        }

        .header-date {
            text-align: center;
            margin-top: 10px;
        }

        .stat-value {
            font-size: 1.5rem;
        }

        .chart-container {
            height: 250px;
        }
    }

    /* Welcome Message */
    .welcome-message {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        animation: slideIn 0.5s ease;
    }

    @keyframes slideIn {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Custom Tooltip Style (fallback for browsers that don't support title properly) */
    [data-tooltip] {
        position: relative;
    }
</style>

<div class="dashboard-container">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="header-title">
                    <i class="fas fa-chart-line mr-3"></i>Dashboard Gerencial
                </h1>
                <p class="header-subtitle">Panel de control y monitoreo de afiliados</p>
            </div>
            <div class="col-md-4">
                <div class="header-date">
                    <i class="far fa-calendar-alt mr-2"></i><?= date('d/m/Y H:i') ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Welcome Message (will be populated by JS) -->
    <div class="welcome-message" id="welcome-message" style="display: none;">
        <i class="fas fa-hand-wave mr-2"></i>
        <span id="welcome-text"></span>
    </div>

    <!-- Quick Stats Row with Tooltips -->
    <div class="row" id="quick-stats">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card loading" id="stat-total" title="Número total de afiliados registrados en la clínica, independientemente de su estatus">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-label">
                    Total Afiliados
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="stat-value">-</div>
                <div class="stat-trend">
                    <i class="fas fa-chart-line"></i> Cargando...
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card loading" id="stat-solventes" title="Afiliados al día con sus pagos. Estatus solvente = 'Si'">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-label">
                    Afiliados Solventes
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="stat-value">-</div>
                <div class="progress" title="Porcentaje de afiliados solventes sobre el total">
                    <div class="progress-bar" style="width: 0%"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card loading" id="stat-contratos" title="Contratos que vencen en los próximos 30 días. Requieren atención próxima">
                <div class="stat-icon">
                    <i class="fas fa-file-contract"></i>
                </div>
                <div class="stat-label">
                    Contratos por Vencer
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="stat-value">-</div>
                <div class="stat-trend warning" title="Contratos que expiran en los próximos 30 días">
                    <i class="fas fa-exclamation-triangle"></i> Próximos 30 días
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card loading" id="stat-recientes" title="Nuevos afiliados registrados en los últimos 30 días">
                <div class="stat-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-label">
                    Nuevos Afiliados
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="stat-value">-</div>
                <div class="stat-trend" title="Registros de los últimos 30 días">
                    <i class="fas fa-calendar-alt"></i> Últimos 30 días
                </div>
            </div>
        </div>
    </div>

    <!-- Status Distribution Row with Tooltips -->
    <div class="row">
        <div class="col-lg-6">
            <div class="chart-card">
                <h3 class="chart-title" title="Distribución de contratos por su estatus actual: Activos (vigentes), Creados (nuevos), Anulados (cancelados), Vencidos (expirados)">
                    <i class="fas fa-chart-bar mr-2"></i>Distribución por Estatus de Contratos
                    <i class="fas fa-info-circle"></i>
                </h3>
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="chart-card">
                <h3 class="chart-title" title="Composición de afiliados por tipo: Individuales (personas naturales) vs Corporativos (empresas)">
                    <i class="fas fa-chart-pie mr-2"></i>Distribución por Tipo de Afiliado
                    <i class="fas fa-info-circle"></i>
                </h3>
                <div class="chart-container">
                    <canvas id="typeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Demographics and Growth Row with Tooltips -->
    <div class="row">
        <div class="col-lg-6">
            <div class="chart-card">
                <h3 class="chart-title" title="Distribución de afiliados por género: Masculino y Femenino">
                    <i class="fas fa-venus-mars mr-2"></i>Distribución por Género
                    <i class="fas fa-info-circle"></i>
                </h3>
                <div class="chart-container">
                    <canvas id="genderChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="chart-card">
                <h3 class="chart-title" title="Evolución de nuevos afiliados en los últimos 6 meses. Muestra la tendencia de crecimiento">
                    <i class="fas fa-chart-line mr-2"></i>Crecimiento Mensual
                    <i class="fas fa-info-circle"></i>
                </h3>
                <div class="chart-container">
                    <canvas id="growthChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Plans Distribution and Contracts with Tooltips -->
    <div class="row">
        <div class="col-lg-5">
            <div class="chart-card">
                <h3 class="chart-title" title="Los 5 planes más contratados por los afiliados de esta clínica">
                    <i class="fas fa-crown mr-2"></i>Planes Más Populares
                    <i class="fas fa-info-circle"></i>
                </h3>
                <div class="chart-container">
                    <canvas id="plansChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="table-card">
                <h3 class="table-title" title="Contratos que vencen en los próximos 30 días. Los contratos en estado 'Crítico' vencen en ≤7 días">
                    <i class="fas fa-exclamation-circle mr-2"></i>Próximos Vencimientos de Contratos
                    <i class="fas fa-info-circle"></i>
                </h3>
                <table class="dashboard-table" id="contracts-table">
                    <thead>
                        <tr>
                            <th title="Nombre completo del afiliado titular del contrato">Afiliado <i class="fas fa-info-circle"></i></th>
                            <th title="Plan contratado por el afiliado">Plan <i class="fas fa-info-circle"></i></th>
                            <th title="Fecha en que el contrato expira">Fecha Vencimiento <i class="fas fa-info-circle"></i></th>
                            <th title="Días que faltan para el vencimiento. Menos de 7 días es crítico">Días Restantes <i class="fas fa-info-circle"></i></th>
                            <th title="Estado de urgencia: Vigente (>15 días), Próximo (8-15 días), Crítico (≤7 días)">Estado <i class="fas fa-info-circle"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" class="text-center">Cargando datos...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Status Summary Cards with Tooltips (removed as they were duplicate info) -->

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="quick-actions">
                <h3 class="table-title" title="Accesos directos a las funciones más utilizadas">
                    <i class="fas fa-bolt mr-2"></i>Acciones Rápidas
                    <i class="fas fa-info-circle"></i>
                </h3>
                <div class="row">
                    <div class="col-md-3">
                        <a href="<?= Url::to(['/user-datos/create']) ?>" class="action-btn" title="Registrar un nuevo afiliado en el sistema">
                            <i class="fas fa-user-plus"></i> Nuevo Afiliado
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= Url::to(['/user-datos/index']) ?>" class="action-btn" title="Ver y gestionar todos los afiliados">
                            <i class="fas fa-list"></i> Ver Afiliados
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= Url::to(['/user-datos/reporte-afiliados']) ?>" class="action-btn" title="Generar reportes y exportar datos">
                            <i class="fas fa-file-pdf"></i> Generar Reportes
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= Url::to(['/cuotas/index']) ?>" class="action-btn" title="Administrar cuotas y pagos de afiliados">
                            <i class="fas fa-dollar-sign"></i> Gestionar Cuotas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$statsUrl = Url::to(['site/get-clinica-stats']);
$contractsUrl = Url::to(['/contratos/expiring-soon']);

$js = <<<JS
// Enable Bootstrap tooltips if using Bootstrap 4
if (typeof $.fn.tooltip !== 'undefined') {
    $('[title]').tooltip({
        placement: 'top',
        trigger: 'hover'
    });
}

// Global chart instances
let statusChart, typeChart, genderChart, growthChart, plansChart;

// Function to initialize charts - UPDATED for contract status as BAR chart
function initializeCharts(stats) {
    // Destroy existing charts if they exist
    if (statusChart) statusChart.destroy();
    if (typeChart) typeChart.destroy();
    if (genderChart) genderChart.destroy();
    if (growthChart) growthChart.destroy();
    if (plansChart) plansChart.destroy();

    // ===== CONTRACT STATUS DISTRIBUTION CHART (BAR CHART) =====
    // Get contract status counts from the stats
    const contractStatusData = stats.contract_status || {
        activos: 0,
        creados: 0,
        anulados: 0,
        vencidos: 0
    };
    
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    statusChart = new Chart(statusCtx, {
        type: 'bar',
        data: {
            labels: ['Activos', 'Creados', 'Anulados', 'Vencidos'],
            datasets: [{
                label: 'Cantidad de Contratos',
                data: [
                    contractStatusData.activos,
                    contractStatusData.creados,
                    contractStatusData.anulados,
                    contractStatusData.vencidos
                ],
                backgroundColor: [
                    '#27ae60', // Activos - Green
                    '#3498db', // Creados - Blue
                    '#e74c3c', // Anulados - Red
                    '#95a5a6'  // Vencidos - Gray
                ],
                borderColor: [
                    '#1e8449',
                    '#2875a7',
                    '#c0392b',
                    '#7f8c8d'
                ],
                borderWidth: 1,
                borderRadius: 5,
                barPercentage: 0.6
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
                    callbacks: {
                        label: function(context) {
                            const label = context.dataset.label || '';
                            const value = context.raw || 0;
                            return label + ': ' + value;
                        },
                        afterLabel: function(context) {
                            const labels = ['Activos', 'Creados', 'Anulados', 'Vencidos'];
                            const descriptions = [
                                'Contratos vigentes y activos',
                                'Contratos recién creados pendientes de activación',
                                'Contratos cancelados o anulados',
                                'Contratos que han superado su fecha de vencimiento'
                            ];
                            return descriptions[context.dataIndex];
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    },
                    ticks: {
                        stepSize: 1,
                        precision: 0
                    },
                    title: {
                        display: true,
                        text: 'Cantidad de Contratos'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Type Distribution Chart (Pie)
    const typeCtx = document.getElementById('typeChart').getContext('2d');
    typeChart = new Chart(typeCtx, {
        type: 'doughnut',
        data: {
            labels: ['Individuales', 'Corporativos'],
            datasets: [{
                data: [
                    stats.stats.individuales,
                    stats.stats.corporativos
                ],
                backgroundColor: [
                    '#3498db',
                    '#9b59b6'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return label + ': ' + value + ' (' + percentage + '%)';
                        },
                        afterLabel: function(context) {
                            return context.label === 'Individuales' 
                                ? 'Personas naturales afiliadas' 
                                : 'Empresas y organizaciones afiliadas';
                        }
                    }
                }
            },
            cutout: '70%'
        }
    });

    // Gender Distribution Chart (Pie)
    const genderCtx = document.getElementById('genderChart').getContext('2d');
    genderChart = new Chart(genderCtx, {
        type: 'doughnut',
        data: {
            labels: ['Masculino', 'Femenino'],
            datasets: [{
                data: [
                    stats.stats.masculinos,
                    stats.stats.femeninos
                ],
                backgroundColor: [
                    '#3498db',
                    '#e84393'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            cutout: '70%'
        }
    });

    // Monthly Growth Chart (Line)
    const growthCtx = document.getElementById('growthChart').getContext('2d');
    const months = stats.monthly_growth.map(item => item.month);
    const counts = stats.monthly_growth.map(item => item.count);
    
    growthChart = new Chart(growthCtx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Nuevos Afiliados',
                data: counts,
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#2c3e50',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
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
                    callbacks: {
                        label: function(context) {
                            return 'Nuevos afiliados: ' + context.raw;
                        },
                        afterLabel: function(context) {
                            return 'Mes: ' + context.label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    },
                    title: {
                        display: true,
                        text: 'Cantidad'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Plans Distribution Chart (Bar)
    if (stats.plan_distribution && stats.plan_distribution.length > 0) {
        const plansCtx = document.getElementById('plansChart').getContext('2d');
        const planLabels = stats.plan_distribution.map(item => item.name);
        const planCounts = stats.plan_distribution.map(item => item.count);
        
        plansChart = new Chart(plansCtx, {
            type: 'bar',
            data: {
                labels: planLabels,
                datasets: [{
                    label: 'Afiliados',
                    data: planCounts,
                    backgroundColor: [
                        '#3498db',
                        '#2ecc71',
                        '#e74c3c',
                        '#f39c12',
                        '#9b59b6'
                    ],
                    borderRadius: 5,
                    barPercentage: 0.6
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
                        callbacks: {
                            label: function(context) {
                                return 'Afiliados: ' + context.raw;
                            },
                            afterLabel: function(context) {
                                return 'Plan: ' + context.label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        },
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        },
                        title: {
                            display: true,
                            text: 'Cantidad de Afiliados'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
}

// Function to update numeric values
function updateStats(stats) {
    // Update main stat cards
    $('#stat-total .stat-value').text(stats.stats.total);
    $('#stat-total .stat-trend').html('<i class="fas fa-check-circle"></i> Total registrados');
    $('#stat-total').removeClass('loading');

    // Solventes card
    $('#stat-solventes .stat-value').text(stats.stats.solventes);
    const solventePercentage = stats.stats.total > 0 ? (stats.stats.solventes / stats.stats.total * 100).toFixed(1) : 0;
    $('#stat-solventes .progress-bar').css('width', solventePercentage + '%');
    $('#stat-solventes').removeClass('loading');

    // Contratos por vencer
    $('#stat-contratos .stat-value').text(stats.stats.contratos_por_vencer);
    $('#stat-contratos').removeClass('loading');

    // Nuevos afiliados
    $('#stat-recientes .stat-value').text(stats.stats.recientes);
    $('#stat-recientes').removeClass('loading');

    // Show welcome message
$('#welcome-message').fadeIn();

// Create the HTML with inline styles on every element
var welcomeHtml = '<span style="color: white !important;">' +
                  'Bienvenido, gestionando clínica ' +
                  '<strong style="color: white !important;">' + stats.clinica + '</strong>' +
                  '. Tienes ' + stats.stats.total + ' afiliados en total.' +
                  '</span>';

$('#welcome-text').html(welcomeHtml);
}

// Function to load expiring contracts
function loadExpiringContracts() {
    $.ajax({
        url: '$contractsUrl',
        method: 'GET',
        success: function(response) {
            if (response.success && response.data.length > 0) {
                let html = '';
                response.data.forEach(contract => {
                    const daysLeft = contract.dias_restantes;
                    let statusClass = 'status-activo';
                    let statusText = 'Vigente';
                    let statusTooltip = 'Contrato vigente con más de 15 días antes del vencimiento';
                    
                    if (daysLeft <= 7) {
                        statusClass = 'status-suspendido';
                        statusText = 'Crítico';
                        statusTooltip = 'Vence en 7 días o menos. Requiere acción inmediata';
                    } else if (daysLeft <= 15) {
                        statusClass = 'status-pendiente';
                        statusText = 'Próximo';
                        statusTooltip = 'Vence en 8-15 días. Programar renovación';
                    }
                    
                    html += '<tr>' +
                        '<td title="' + contract.afiliado + '">' + contract.afiliado + '</td>' +
                        '<td title="Plan: ' + contract['plan'] + '">' + contract['plan'] + '</td>' +
                        '<td title="Fecha de vencimiento: ' + contract.fecha_ven + '">' + contract.fecha_ven + '</td>' +
                        '<td title="' + daysLeft + ' días restantes para el vencimiento"><strong>' + daysLeft + ' días</strong></td>' +
                        '<td><span class="status-badge ' + statusClass + '" title="' + statusTooltip + '">' + statusText + '</span></td>' +
                    '</tr>';
                });
                $('#contracts-table tbody').html(html);
                
                // Re-initialize tooltips for new content
                if (typeof $.fn.tooltip !== 'undefined') {
                    $('[title]').tooltip({
                        placement: 'top',
                        trigger: 'hover'
                    });
                }
            } else {
                $('#contracts-table tbody').html('<tr><td colspan="5" class="text-center">No hay contratos próximos a vencer</td></tr>');
            }
        },
        error: function() {
            $('#contracts-table tbody').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar los contratos</td></tr>');
        }
    });
}

// Main data loading function
function loadDashboardData() {
    $.ajax({
        url: '$statsUrl',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Update all stats
                updateStats(response);
                
                // Initialize charts with the data
                initializeCharts(response);
                
                // Load expiring contracts
                loadExpiringContracts();
            } else {
                toastr.error('Error al cargar los datos del dashboard');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading dashboard data:', error);
            toastr.error('Error de conexión al cargar los datos');
            
            // Remove loading states on error
            $('.stat-card').removeClass('loading');
            $('.stat-value').text('Error');
        }
    });
}

// Initialize dashboard on page load
$(document).ready(function() {
    loadDashboardData();
    
    // Auto-refresh every 5 minutes
    setInterval(loadDashboardData, 300000);
});
JS;

$this->registerJs($js, View::POS_READY);
?>