<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\View;
use yii\bootstrap4\ActiveForm;

$this->title = 'Panel de Control Gerencial - SISPSA';
$this->params['breadcrumbs'][] = $this->title;

// Register Chart.js
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js', ['position' => View::POS_HEAD]);

// Register Font Awesome
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
?>

<style>
    /* ===== GLOBAL DASHBOARD STYLES ===== */
    .dashboard-sispsa {
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
        --teal: #1abc9c;
        --gray-100: #f8f9fa;
        --gray-200: #e9ecef;
        --gray-300: #dee2e6;
        --gray-400: #ced4da;
        --gray-500: #adb5bd;
        --gray-600: #6c757d;
        --gray-700: #495057;
        --gray-800: #343a40;
        --gray-900: #212529;

        font-family: 'Open Sans', sans-serif;
        background-color: #f8f9fa;
        padding: 20px;
        min-height: 100vh;
    }

    /* ===== PREMIUM TAB NAVIGATION ===== */
    .dashboard-sispsa .tab-container {
        margin-bottom: 30px;
    }

    .dashboard-sispsa .premium-tabs {
        display: flex;
        background: white;
        border-radius: 60px;
        padding: 6px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05), inset 0 1px 2px rgba(255, 255, 255, 0.8);
        border: 1px solid var(--gray-200);
        max-width: 600px;
        margin: 0 auto 20px;
    }

    .dashboard-sispsa .premium-tab-btn {
        flex: 1;
        border: none;
        background: transparent;
        padding: 14px 20px;
        font-weight: 600;
        font-size: 1rem;
        color: var(--gray-600);
        cursor: pointer;
        transition: all 0.3s ease;
        border-radius: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        position: relative;
        overflow: hidden;
    }

    .dashboard-sispsa .premium-tab-btn i {
        font-size: 1.2rem;
        transition: all 0.3s ease;
        color: var(--gray-500);
    }

    .dashboard-sispsa .premium-tab-btn:hover {
        color: var(--secondary);
        background: rgba(52, 152, 219, 0.05);
    }

    .dashboard-sispsa .premium-tab-btn:hover i {
        color: var(--secondary);
    }

    .dashboard-sispsa .premium-tab-btn.active {
        background: linear-gradient(135deg, var(--secondary), #2980b9);
        color: white !important;
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
    }

    .dashboard-sispsa .premium-tab-btn.active i {
        color: white !important;
        transform: scale(1.1);
    }

    .dashboard-sispsa .premium-tab-btn:first-child.active {
        background: linear-gradient(135deg, var(--secondary), #2980b9);
    }

    .dashboard-sispsa .premium-tab-btn:last-child.active {
        background: linear-gradient(135deg, var(--teal), #16a085);
    }

    .dashboard-sispsa .tab-badge {
        background: rgba(255, 255, 255, 0.2);
        padding: 2px 8px;
        border-radius: 30px;
        font-size: 0.7rem;
        margin-left: 5px;
        color: var(--gray-700);
    }

    .dashboard-sispsa .premium-tab-btn.active .tab-badge {
        background: rgba(255, 255, 255, 0.3);
        color: white;
    }

    /* ===== HEADER STYLES ===== */
    .dashboard-sispsa .dashboard-header {
        margin-bottom: 30px;
        padding: 25px 30px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        border-radius: 15px;
        color: white;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .dashboard-sispsa .header-title {
        font-size: 2rem;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .dashboard-sispsa .header-subtitle {
        font-size: 1rem;
        opacity: 0.9;
        color: white;
    }

    .dashboard-sispsa .header-date {
        font-size: 0.95rem;
        opacity: 0.8;
        text-align: right;
        padding-top: 10px;
    }

    /* ===== FILTER CARD ===== */
    .dashboard-sispsa .filter-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .dashboard-sispsa .filter-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 15px;
    }

    /* ===== STAT CARDS ===== */
    .dashboard-sispsa .stat-card {
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
    }

    .dashboard-sispsa .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .dashboard-sispsa .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
    }

    .dashboard-sispsa .stat-card.siniestro::before {
        background: linear-gradient(90deg, var(--danger), var(--orange));
    }

    .dashboard-sispsa .stat-card.cita::before {
        background: linear-gradient(90deg, var(--success), var(--teal));
    }

    .dashboard-sispsa .stat-icon {
        font-size: 2.5rem;
        color: var(--secondary);
        margin-bottom: 10px;
        opacity: 0.8;
    }

    .dashboard-sispsa .stat-icon.siniestro {
        color: var(--danger);
    }

    .dashboard-sispsa .stat-icon.cita {
        color: var(--success);
    }

    .dashboard-sispsa .stat-label {
        font-size: 0.9rem;
        color: #7f8c8d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }

    .dashboard-sispsa .stat-label i {
        margin-left: 5px;
        font-size: 0.8rem;
        color: var(--secondary);
        opacity: 0.6;
    }

    .dashboard-sispsa .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary);
        line-height: 1.2;
        margin-bottom: 5px;
    }

    .dashboard-sispsa .stat-value.small {
        font-size: 1.5rem;
    }

    .dashboard-sispsa .stat-trend {
        font-size: 0.9rem;
        color: #7f8c8d;
    }

    .dashboard-sispsa .stat-trend .badge {
        font-size: 0.8rem;
        padding: 5px 10px;
        font-weight: 500;
    }

    /* ===== CHART CARDS ===== */
    .dashboard-sispsa .chart-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .dashboard-sispsa .chart-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--light);
        cursor: help;
    }

    .dashboard-sispsa .chart-title i:last-child {
        margin-left: 5px;
        font-size: 0.9rem;
        color: var(--secondary);
        opacity: 0.6;
    }

    .dashboard-sispsa .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }

    /* ===== TABLE STYLES ===== */
    .dashboard-sispsa .table-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .dashboard-sispsa .table-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--light);
        cursor: help;
    }

    .dashboard-sispsa .table-title i:last-child {
        margin-left: 5px;
        font-size: 0.9rem;
        color: var(--secondary);
        opacity: 0.6;
    }

    .dashboard-sispsa .kpi-table {
        width: 100%;
        border-collapse: collapse;
    }

    .dashboard-sispsa .kpi-table th {
        background-color: var(--light);
        color: #000000 !important;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 12px;
        text-align: left;
        border-bottom: 2px solid #dee2e6;
    }

    .dashboard-sispsa .kpi-table th i {
        margin-left: 5px;
        font-size: 0.8rem;
        color: var(--secondary);
        opacity: 0.6;
        cursor: help;
    }

    .dashboard-sispsa .kpi-table td {
        padding: 12px;
        border-bottom: 1px solid #dee2e6;
        color: #34495e;
    }

    .dashboard-sispsa .kpi-table tr:hover {
        background-color: rgba(52, 152, 219, 0.05);
    }

    /* ===== PROGRESS BARS ===== */
    .dashboard-sispsa .progress {
        height: 10px;
        border-radius: 5px;
        background-color: var(--light);
        margin-top: 10px;
        cursor: help;
    }

    .dashboard-sispsa .progress-bar {
        background: linear-gradient(90deg, var(--secondary), var(--primary));
        border-radius: 5px;
    }

    .dashboard-sispsa .progress-bar.success {
        background: linear-gradient(90deg, var(--success), var(--teal));
    }

    .dashboard-sispsa .progress-bar.warning {
        background: linear-gradient(90deg, var(--warning), var(--orange));
    }

    .dashboard-sispsa .progress-bar.danger {
        background: linear-gradient(90deg, var(--danger), #c0392b);
    }

    /* ===== STATUS BADGES ===== */
    .dashboard-sispsa .status-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-align: center;
        min-width: 90px;
        cursor: help;
    }

    .dashboard-sispsa .status-activo {
        background: rgba(39, 174, 96, 0.1);
        color: #27ae60;
        border: 1px solid rgba(39, 174, 96, 0.2);
    }

    .dashboard-sispsa .status-suspendido {
        background: rgba(231, 76, 60, 0.1);
        color: #e74c3c;
        border: 1px solid rgba(231, 76, 60, 0.2);
    }

    .dashboard-sispsa .status-pendiente {
        background: rgba(243, 156, 18, 0.1);
        color: #f39c12;
        border: 1px solid rgba(243, 156, 18, 0.2);
    }

    .dashboard-sispsa .status-inactivo {
        background: rgba(149, 165, 166, 0.1);
        color: #7f8c8d;
        border: 1px solid rgba(149, 165, 166, 0.2);
    }

    .dashboard-sispsa .status-solvente {
        background: rgba(39, 174, 96, 0.1);
        color: #27ae60;
        border: 1px solid rgba(39, 174, 96, 0.2);
    }

    .dashboard-sispsa .status-atendida {
        background: rgba(39, 174, 96, 0.1);
        color: #27ae60;
        border: 1px solid rgba(39, 174, 96, 0.2);
    }

    .dashboard-sispsa .status-pendiente-atencion {
        background: rgba(243, 156, 18, 0.1);
        color: #f39c12;
        border: 1px solid rgba(243, 156, 18, 0.2);
    }

    /* ===== LOADING STATE ===== */
    .dashboard-sispsa .loading {
        position: relative;
        opacity: 0.6;
        pointer-events: none;
    }

    .dashboard-sispsa .loading::after {
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

    /* ===== WELCOME MESSAGE ===== */
    .dashboard-sispsa .welcome-message {
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

    /* ===== QUICK ACTIONS ===== */
    .dashboard-sispsa .quick-actions {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .dashboard-sispsa .action-btn {
        display: block;
        padding: 15px;
        background: var(--light);
        border-radius: 10px;
        color: var(--primary);
        text-decoration: none;
        transition: all 0.3s ease;
        text-align: center;
        cursor: help;
    }

    .dashboard-sispsa .action-btn:hover {
        background: var(--secondary);
        color: white;
        transform: translateX(5px);
        text-decoration: none;
    }

    .dashboard-sispsa .action-btn i {
        margin-right: 10px;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 992px) {
        .dashboard-sispsa .header-title {
            font-size: 1.5rem;
            text-align: center;
        }

        .dashboard-sispsa .header-date {
            text-align: center;
            margin-top: 10px;
        }
    }

    @media (max-width: 768px) {
        .dashboard-sispsa {
            padding: 10px;
        }

        .dashboard-sispsa .stat-value {
            font-size: 1.5rem;
        }

        .dashboard-sispsa .chart-container {
            height: 250px;
        }

        .dashboard-sispsa .premium-tabs {
            flex-direction: column;
            border-radius: 30px;
            max-width: 100%;
        }

        .dashboard-sispsa .premium-tab-btn {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 576px) {
        .dashboard-sispsa .premium-tab-btn {
            padding: 12px;
            font-size: 0.9rem;
        }
    }
</style>

<div class="dashboard-sispsa">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="header-title">
                    <i class="fas fa-chart-line mr-3"></i>Panel de Control Gerencial
                </h1>
                <p class="header-subtitle">
                    <i class="fas fa-hospital mr-2"></i><?= Html::encode($clinicaNombre) ?>
                </p>
            </div>
            <div class="col-md-4">
                <div class="header-date">
                    <i class="far fa-calendar-alt mr-2"></i><?= date('d/m/Y H:i') ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Premium Tab Navigation -->
    <div class="tab-container">
        <div class="premium-tabs">
            <button class="premium-tab-btn <?= $activeTab === 'general' ? 'active' : '' ?>"
                id="tab-general-btn"
                title="Ver indicadores generales de afiliados, contratos y planes">
                <i class="fas fa-chart-pie"></i>
                <span>Panel General</span>
                <span class="tab-badge" id="general-badge">Afiliados</span>
            </button>
            <button class="premium-tab-btn <?= $activeTab === 'atenciones' ? 'active' : '' ?>"
                id="tab-atenciones-btn"
                title="Ver estadísticas de siniestros, citas médicas y baremos">
                <i class="fas fa-heartbeat"></i>
                <span>Atenciones Médicas</span>
                <span class="tab-badge" id="atenciones-badge">Siniestros</span>
            </button>
        </div>
    </div>

    <!-- Manual Tab Content -->
    <div id="general-tab-content" style="display: <?= $activeTab === 'general' ? 'block' : 'none' ?>;">
        <!-- Welcome Message -->
        <div class="welcome-message" id="general-welcome" style="display: none;">
            <i class="fas fa-hand-wave mr-2"></i>
            <span id="general-welcome-text"></span>
        </div>

        <!-- Quick Stats Row -->
        <div class="row" id="general-quick-stats">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card loading" id="stat-total"
                    title="Número total de afiliados registrados en la clínica, independientemente de su estatus actual">
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
                <div class="stat-card loading" id="stat-solventes"
                    title="Afiliados al día con sus pagos. Estatus solvente = 'Si'. Incluye tanto individuales como corporativos">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-label">
                        Afiliados Solventes
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="stat-value">-</div>
                    <div class="progress" title="Porcentaje de afiliados solventes sobre el total de afiliados">
                        <div class="progress-bar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card loading" id="stat-contratos"
                    title="Contratos que vencen en los próximos 30 días. Requieren atención para renovación">
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
                <div class="stat-card loading" id="stat-recientes"
                    title="Nuevos afiliados registrados en los últimos 30 días. Mide el crecimiento reciente">
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

        <!-- Charts Row 1 -->
        <div class="row">
            <div class="col-lg-6">
                <div class="chart-card">
                    <h3 class="chart-title"
                        title="Distribución de contratos por estatus actual: Activos (vigentes), Creados (nuevos), Suspendidos (temporalmente), Anulados (cancelados), Vencidos (expirados)">
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
                    <h3 class="chart-title"
                        title="Composición de afiliados por tipo: Individuales (personas naturales) vs Corporativos (empresas u organizaciones)">
                        <i class="fas fa-chart-pie mr-2"></i>Distribución por Tipo de Afiliado
                        <i class="fas fa-info-circle"></i>
                    </h3>
                    <div class="chart-container">
                        <canvas id="typeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="row">
            <div class="col-lg-6">
                <div class="chart-card">
                    <h3 class="chart-title"
                        title="Distribución de afiliados por género: Masculino y Femenino. Útil para análisis demográfico">
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
                    <h3 class="chart-title"
                        title="Evolución de nuevos afiliados en los últimos 6 meses. Muestra la tendencia de crecimiento y permite identificar picos estacionales">
                        <i class="fas fa-chart-line mr-2"></i>Crecimiento Mensual
                        <i class="fas fa-info-circle"></i>
                    </h3>
                    <div class="chart-container">
                        <canvas id="growthChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Plans and Contracts -->
        <div class="row">
            <div class="col-lg-5">
                <div class="chart-card">
                    <h3 class="chart-title"
                        title="Los 5 planes más contratados por los afiliados de esta clínica. Ayuda a identificar preferencias y tendencias">
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
                    <h3 class="table-title"
                        title="Contratos que vencen en los próximos 30 días. Los contratos en estado 'Crítico' vencen en ≤7 días y requieren acción inmediata">
                        <i class="fas fa-exclamation-circle mr-2"></i>Próximos Vencimientos de Contratos
                        <i class="fas fa-info-circle"></i>
                    </h3>
                    <table class="kpi-table" id="contracts-table">
                        <thead>
                            <tr>
                                <th title="Nombre completo del afiliado titular del contrato">Afiliado <i class="fas fa-info-circle"></i></th>
                                <th title="Plan contratado por el afiliado">Plan <i class="fas fa-info-circle"></i></th>
                                <th title="Fecha en que el contrato expira. Formato DD/MM/AAAA">Vencimiento <i class="fas fa-info-circle"></i></th>
                                <th title="Días que faltan para el vencimiento. Menos de 7 días es crítico, 8-15 días es próximo">Días <i class="fas fa-info-circle"></i></th>
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

        <!-- Status Summary Cards -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="stat-card" title="Afiliados con estatus 'Activo' - actualmente habilitados en el sistema">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">Activos</div>
                            <div class="stat-value small" id="activos-count">-</div>
                        </div>
                        <div class="status-badge status-activo" title="Estatus actual del afiliado en el sistema">
                            <i class="fas fa-check-circle"></i> Activo
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" title="Afiliados con estatus 'Suspendido' - temporalmente inhabilitados por falta de pago u otras razones">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">Suspendidos</div>
                            <div class="stat-value small" id="suspendidos-count">-</div>
                        </div>
                        <div class="status-badge status-suspendido" title="Estatus actual del afiliado en el sistema">
                            <i class="fas fa-ban"></i> Suspendido
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" title="Afiliados con estatus 'Pendiente' - en proceso de activación o esperando documentación">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">Pendientes</div>
                            <div class="stat-value small" id="pendientes-count">-</div>
                        </div>
                        <div class="status-badge status-pendiente" title="Estatus actual del afiliado en el sistema">
                            <i class="fas fa-clock"></i> Pendiente
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" title="Afiliados con estatus 'Inactivo' - ya no están en el sistema (dados de baja)">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-label">Inactivos</div>
                            <div class="stat-value small" id="inactivos-count">-</div>
                        </div>
                        <div class="status-badge status-inactivo" title="Estatus actual del afiliado en el sistema">
                            <i class="fas fa-user-slash"></i> Inactivo
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="atenciones-tab-content" style="display: <?= $activeTab === 'atenciones' ? 'block' : 'none' ?>;">
        <!-- Filter Form -->
        <div class="filter-card">
            <h3 class="filter-title" title="Seleccione un rango de fechas para filtrar las estadísticas de atenciones médicas">
                <i class="fas fa-filter mr-2"></i>Filtrar por período
                <i class="fas fa-info-circle"></i>
            </h3>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="mr-2" title="Fecha inicial del período a analizar">Desde:</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" value="<?= date('Y-m-01') ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="mr-2" title="Fecha final del período a analizar">Hasta:</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" value="<?= date('Y-m-t') ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="button" id="apply-filter" class="btn btn-primary" title="Aplicar el filtro de fechas seleccionado">
                        <i class="fas fa-sync-alt mr-2"></i>Aplicar filtro
                    </button>
                    <button type="button" id="reset-filter" class="btn btn-secondary ml-2" title="Restablecer al mes actual">
                        <i class="fas fa-undo mr-2"></i>Reiniciar
                    </button>
                </div>
            </div>
        </div>

        <!-- Atenciones Stats Cards -->
        <div class="row" id="atenciones-stats">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card loading" id="stat-total-atenciones"
                    title="Total de atenciones (siniestros + citas) en el período seleccionado">
                    <div class="stat-icon">
                        <i class="fas fa-notes-medical"></i>
                    </div>
                    <div class="stat-label">
                        Total Atenciones
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="stat-value">-</div>
                    <div class="stat-trend">
                        <span class="badge badge-info" id="date-range-badge" title="Período de análisis actual">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card siniestro loading" id="stat-siniestros"
                    title="Siniestros (emergencias médicas) atendidos en el período">
                    <div class="stat-icon siniestro">
                        <i class="fas fa-ambulance"></i>
                    </div>
                    <div class="stat-label">
                        Siniestros
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="stat-value">-</div>
                    <div class="stat-trend">
                        <span class="badge badge-danger" id="siniestros-pct" title="Porcentaje de siniestros sobre el total">0%</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card cita loading" id="stat-citas"
                    title="Citas médicas programadas en el período">
                    <div class="stat-icon cita">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-label">
                        Citas
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="stat-value">-</div>
                    <div class="stat-trend">
                        <span class="badge badge-success" id="citas-pct" title="Porcentaje de citas sobre el total">0%</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card loading" id="stat-pacientes-unicos"
                    title="Número de pacientes únicos atendidos en el período (sin duplicados)">
                    <div class="stat-icon">
                        <i class="fas fa-user-injured"></i>
                    </div>
                    <div class="stat-label">
                        Pacientes Únicos
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="stat-value">-</div>
                    <div class="stat-trend">
                        <span class="badge badge-info" id="promedio-paciente" title="Promedio de atenciones por paciente">0 atenciones/paciente</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Second Row - Status and Costs -->
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card loading" id="stat-atendidas"
                    title="Atenciones que ya fueron realizadas (completadas)">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle" style="color: var(--success);"></i>
                    </div>
                    <div class="stat-label">
                        Atendidas
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="stat-value">-</div>
                    <div class="progress" id="tasa-atencion-progress" title="Tasa de atención - porcentaje completado">
                        <div class="progress-bar success" style="width: 0%"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card loading" id="stat-pendientes"
                    title="Atenciones pendientes por realizar (no completadas)">
                    <div class="stat-icon">
                        <i class="fas fa-clock" style="color: var(--warning);"></i>
                    </div>
                    <div class="stat-label">
                        Pendientes
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="stat-value">-</div>
                    <div class="stat-trend">
                        <span class="badge badge-warning" id="pendientes-pct" title="Porcentaje de atenciones pendientes">0%</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card loading" id="stat-costo-total"
                    title="Costo total acumulado de todas las atenciones en el período">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-label">
                        Costo Total
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="stat-value">$ -</div>
                    <div class="stat-trend">
                        <span class="badge badge-secondary" id="costo-promedio" title="Costo promedio por atención">Promedio: $0</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card loading" id="stat-tasa-atencion"
                    title="Tasa de atención = (Atendidas / Total) * 100. Indica el porcentaje de atenciones completadas">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line" style="color: var(--primary);"></i>
                    </div>
                    <div class="stat-label">
                        Tasa de Atención
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="stat-value">-</div>
                    <div class="stat-trend">
                        <span class="badge" id="tasa-atencion-badge" title="Excelente: ≥80%, Regular: 60-80%, Bajo: <60%">-</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Atenciones Charts Row 1 -->
        <div class="row">
            <div class="col-lg-6">
                <div class="chart-card">
                    <h3 class="chart-title"
                        title="Tendencia diaria de atenciones: compara siniestros (rojo) vs citas (verde) día por día">
                        <i class="fas fa-chart-line mr-2"></i>Tendencia Diaria
                        <i class="fas fa-info-circle"></i>
                    </h3>
                    <div class="chart-container">
                        <canvas id="dailyTrendChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-card">
                    <h3 class="chart-title"
                        title="Distribución porcentual entre Siniestros (emergencias) y Citas programadas">
                        <i class="fas fa-chart-pie mr-2"></i>Distribución por Tipo
                        <i class="fas fa-info-circle"></i>
                    </h3>
                    <div class="chart-container">
                        <canvas id="typeDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Atenciones Charts Row 2 -->
        <div class="row">
            <div class="col-lg-6">
                <div class="chart-card">
                    <h3 class="chart-title"
                        title="Atenciones por día de la semana: identifica los días de mayor demanda">
                        <i class="fas fa-calendar-week mr-2"></i>Atenciones por Día
                        <i class="fas fa-info-circle"></i>
                    </h3>
                    <div class="chart-container">
                        <canvas id="dayOfWeekChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-card">
                    <h3 class="chart-title"
                        title="Costo diario de atenciones: permite identificar días con mayor impacto financiero">
                        <i class="fas fa-chart-bar mr-2"></i>Costo Diario
                        <i class="fas fa-info-circle"></i>
                    </h3>
                    <div class="chart-container">
                        <canvas id="dailyCostChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Baremos Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="table-card">
                    <h3 class="table-title"
                        title="Los 10 servicios/baremos más utilizados en las atenciones del período. Incluye frecuencia de uso y costos asociados">
                        <i class="fas fa-crown mr-2"></i>Top 10 Baremos Más Utilizados
                        <i class="fas fa-info-circle"></i>
                    </h3>
                    <table class="kpi-table" id="top-baremos-table">
                        <thead>
                            <tr>
                                <th title="Posición en el ranking"># <i class="fas fa-info-circle"></i></th>
                                <th title="Nombre del servicio o procedimiento médico">Baremo / Servicio <i class="fas fa-info-circle"></i></th>
                                <th title="Número de veces que se utilizó este servicio">Veces Utilizado <i class="fas fa-info-circle"></i></th>
                                <th title="Costo total acumulado del servicio en el período">Costo Total <i class="fas fa-info-circle"></i></th>
                                <th title="Costo promedio por cada uso del servicio">Costo Promedio <i class="fas fa-info-circle"></i></th>
                                <th title="Porcentaje que representa este servicio sobre el total de atenciones">% del Total <i class="fas fa-info-circle"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center">Cargando datos...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions (Visible in both tabs) -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="quick-actions">
                <h3 class="table-title" title="Accesos directos a las funciones más utilizadas del sistema">
                    <i class="fas fa-bolt mr-2"></i>Acciones Rápidas
                    <i class="fas fa-info-circle"></i>
                </h3>
                <div class="row">
                    <div class="col-md-3">
                        <a href="<?= Url::to(['/user-datos/create']) ?>" class="action-btn"
                            title="Registrar un nuevo afiliado en el sistema">
                            <i class="fas fa-user-plus"></i> Nuevo Afiliado
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= Url::to(['/user-datos/index']) ?>" class="action-btn"
                            title="Ver y gestionar todos los afiliados de la clínica">
                            <i class="fas fa-list"></i> Ver Afiliados
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= Url::to(['/user-datos/reporte-afiliados']) ?>" class="action-btn"
                            title="Generar reportes y exportar datos de afiliados">
                            <i class="fas fa-file-pdf"></i> Generar Reportes
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= Url::to(['/sis-siniestro/por-clinica', 'clinica_id' => $clinicaId]) ?>" class="action-btn"
                            title="Ver todas las atenciones médicas de la clínica">
                            <i class="fas fa-heartbeat"></i> Ver Atenciones
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// URLs for AJAX calls
$generalDataUrl = Url::to(['site/get-dashboard-data']);
$atencionesDataUrl = Url::to(['site/get-atenciones-data']);
$contractsUrl = Url::to(['/contratos/expiring-soon']);

// Pass the active tab to JavaScript
$activeTabJs = $activeTab;

$js = <<<JS
// Global chart instances
let statusChart, typeChart, genderChart, growthChart, plansChart;
let dailyTrendChart, typeDistChart, dayOfWeekChart, dailyCostChart;

// ===== GENERAL DASHBOARD FUNCTIONS =====
function initializeGeneralCharts(stats) {
    // Destroy existing charts
    if (statusChart) statusChart.destroy();
    if (typeChart) typeChart.destroy();
    if (genderChart) genderChart.destroy();
    if (growthChart) growthChart.destroy();
    if (plansChart) plansChart.destroy();

    // Contract Status Chart (Bar)
    const contractData = stats.contract_status || { 
        activos: 0, 
        creados: 0, 
        suspendidos: 0,
        anulados: 0, 
        vencidos: 0 
    };

    const statusCtx = document.getElementById('statusChart').getContext('2d');
    statusChart = new Chart(statusCtx, {
        type: 'bar',
        data: {
            labels: ['Activos', 'Creados', 'Suspendidos', 'Anulados', 'Vencidos'],
            datasets: [{
                label: 'Cantidad',
                data: [
                    contractData.activos, 
                    contractData.creados, 
                    contractData.suspendidos,
                    contractData.anulados, 
                    contractData.vencidos
                ],
                backgroundColor: [
                    '#27ae60', // Activos - Green
                    '#3498db', // Creados - Blue
                    '#f39c12', // Suspendidos - Orange/Yellow
                    '#e74c3c', // Anulados - Red
                    '#95a5a6'  // Vencidos - Gray
                ],
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.dataset.label || '';
                            const value = context.raw || 0;
                            return label + ': ' + value;
                        },
                        afterLabel: function(context) {
                            const descriptions = [
                                'Contratos vigentes y activos',
                                'Contratos recién creados pendientes de activación',
                                'Contratos suspendidos temporalmente',
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

    // Type Distribution Chart
    const typeCtx = document.getElementById('typeChart').getContext('2d');
    typeChart = new Chart(typeCtx, {
        type: 'doughnut',
        data: {
            labels: ['Individuales', 'Corporativos'],
            datasets: [{
                data: [stats.stats.individuales, stats.stats.corporativos],
                backgroundColor: ['#3498db', '#9b59b6'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { position: 'bottom' },
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

    // Gender Distribution Chart
    const genderCtx = document.getElementById('genderChart').getContext('2d');
    genderChart = new Chart(genderCtx, {
        type: 'doughnut',
        data: {
            labels: ['Masculino', 'Femenino'],
            datasets: [{
                data: [stats.stats.masculinos, stats.stats.femeninos],
                backgroundColor: ['#3498db', '#e84393'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { position: 'bottom' },
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

    // Growth Chart
    const growthCtx = document.getElementById('growthChart').getContext('2d');
    const months = stats.monthly_growth.map(i => i.month);
    const counts = stats.monthly_growth.map(i => i.count);
    growthChart = new Chart(growthCtx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Nuevos Afiliados',
                data: counts,
                borderColor: '#3498db',
                backgroundColor: 'rgba(52,152,219,0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
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
                    title: {
                        display: true,
                        text: 'Cantidad'
                    }
                }
            }
        }
    });

    // Plans Chart
    if (stats.plan_distribution && stats.plan_distribution.length > 0) {
        const plansCtx = document.getElementById('plansChart').getContext('2d');
        const planLabels = stats.plan_distribution.map(i => i.name);
        const planCounts = stats.plan_distribution.map(i => i.count);
        plansChart = new Chart(plansCtx, {
            type: 'bar',
            data: {
                labels: planLabels,
                datasets: [{
                    label: 'Afiliados',
                    data: planCounts,
                    backgroundColor: ['#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6'],
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
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
                        ticks: { stepSize: 1, precision: 0 },
                        title: {
                            display: true,
                            text: 'Cantidad de Afiliados'
                        }
                    }
                }
            }
        });
    }
}

function updateGeneralStats(stats) {
    $('#stat-total .stat-value').text(stats.stats.total);
    $('#stat-total .stat-trend').html('<i class="fas fa-check-circle"></i> Total registrados');
    $('#stat-total').removeClass('loading');

    $('#stat-solventes .stat-value').text(stats.stats.solventes);
    const pct = stats.stats.total > 0 ? ((stats.stats.solventes / stats.stats.total) * 100).toFixed(1) : 0;
    $('#stat-solventes .progress-bar').css('width', pct + '%');
    $('#stat-solventes').removeClass('loading');

    $('#stat-contratos .stat-value').text(stats.stats.contratos_por_vencer);
    $('#stat-contratos').removeClass('loading');

    $('#stat-recientes .stat-value').text(stats.stats.recientes);
    $('#stat-recientes').removeClass('loading');

    $('#activos-count').text(stats.stats.activos);
    $('#suspendidos-count').text(stats.stats.suspendidos);
    $('#pendientes-count').text(stats.stats.pendientes);
    $('#inactivos-count').text(stats.stats.inactivos);

    // Update badge count
    $('#general-badge').text(stats.stats.total + ' afiliados');

    $('#general-welcome').fadeIn();
    $('#general-welcome-text').html('<span style="color: white;">Bienvenido, gestionando clínica <strong>' + stats.clinica + '</strong>. Tienes ' + stats.stats.total + ' afiliados.</span>');
}

function loadExpiringContracts() {
    $.ajax({
        url: '$contractsUrl',
        method: 'GET',
        success: function(res) {
            if (res.success && res.data.length > 0) {
                let html = '';
                res.data.forEach(c => {
                    let cls = 'status-activo', txt = 'Vigente';
                    let tooltip = 'Contrato vigente con más de 15 días antes del vencimiento';
                    if (c.dias_restantes <= 7) { 
                        cls = 'status-suspendido'; 
                        txt = 'Crítico';
                        tooltip = 'Vence en 7 días o menos. Requiere acción inmediata';
                    } else if (c.dias_restantes <= 15) { 
                        cls = 'status-pendiente'; 
                        txt = 'Próximo';
                        tooltip = 'Vence en 8-15 días. Programar renovación';
                    }
                    html += '<tr>' +
                        '<td title="' + c.afiliado + '">' + c.afiliado + '</td>' +
                        '<td title="Plan: ' + c.plan + '">' + c.plan + '</td>' +
                        '<td title="Fecha de vencimiento: ' + c.fecha_ven + '">' + c.fecha_ven + '</td>' +
                        '<td title="' + c.dias_restantes + ' días restantes para el vencimiento"><strong>' + c.dias_restantes + ' días</strong></td>' +
                        '<td><span class="status-badge ' + cls + '" title="' + tooltip + '">' + txt + '</span></td>' +
                    '</tr>';
                });
                $('#contracts-table tbody').html(html);
            } else {
                $('#contracts-table tbody').html('<tr><td colspan="5" class="text-center">No hay contratos próximos a vencer</td></tr>');
            }
        },
        error: function() {
            $('#contracts-table tbody').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar</td></tr>');
        }
    });
}

function loadGeneralDashboard() {
    $.ajax({
        url: '$generalDataUrl',
        method: 'GET',
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                updateGeneralStats(res);
                initializeGeneralCharts(res);
                loadExpiringContracts();
            } else {
                toastr.error('Error al cargar datos generales');
            }
        },
        error: function() {
            toastr.error('Error de conexión');
            $('.stat-card').removeClass('loading');
        }
    });
}

// ===== ATENCIONES DASHBOARD FUNCTIONS =====
function initializeAtencionesCharts(data) {
    if (dailyTrendChart) dailyTrendChart.destroy();
    if (typeDistChart) typeDistChart.destroy();
    if (dayOfWeekChart) dayOfWeekChart.destroy();
    if (dailyCostChart) dailyCostChart.destroy();

    // Daily Trend
    const dates = data.daily_data.map(d => d.fecha);
    const siniestros = data.daily_data.map(d => d.siniestros);
    const citas = data.daily_data.map(d => d.citas);
    
    const trendCtx = document.getElementById('dailyTrendChart').getContext('2d');
    dailyTrendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [
                { 
                    label: 'Siniestros', 
                    data: siniestros, 
                    borderColor: '#e74c3c', 
                    backgroundColor: 'rgba(231,76,60,0.1)', 
                    tension: 0.4, 
                    fill: true 
                },
                { 
                    label: 'Citas', 
                    data: citas, 
                    borderColor: '#27ae60', 
                    backgroundColor: 'rgba(39,174,96,0.1)', 
                    tension: 0.4, 
                    fill: true 
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.raw;
                        },
                        afterLabel: function(context) {
                            return 'Fecha: ' + context.label;
                        }
                    }
                }
            },
            scales: { 
                y: { 
                    beginAtZero: true, 
                    ticks: { stepSize: 1, precision: 0 },
                    title: {
                        display: true,
                        text: 'Cantidad de atenciones'
                    }
                }
            }
        }
    });

    // Type Distribution
    const typeCtx = document.getElementById('typeDistributionChart').getContext('2d');
    typeDistChart = new Chart(typeCtx, {
        type: 'doughnut',
        data: {
            labels: ['Siniestros', 'Citas'],
            datasets: [{
                data: [data.siniestros, data.citas],
                backgroundColor: ['#e74c3c', '#27ae60'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { position: 'bottom' },
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
                            return context.label === 'Siniestros' 
                                ? 'Emergencias médicas' 
                                : 'Citas programadas';
                        }
                    }
                }
            },
            cutout: '70%'
        }
    });

    // Day of Week
    const days = data.day_of_week_data.map(d => d.day);
    const dayCounts = data.day_of_week_data.map(d => d.total);
    const dayCtx = document.getElementById('dayOfWeekChart').getContext('2d');
    dayOfWeekChart = new Chart(dayCtx, {
        type: 'bar',
        data: {
            labels: days,
            datasets: [{
                label: 'Atenciones',
                data: dayCounts,
                backgroundColor: '#3498db',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Atenciones: ' + context.raw;
                        },
                        afterLabel: function(context) {
                            return 'Día: ' + context.label;
                        }
                    }
                }
            },
            scales: { 
                y: { 
                    beginAtZero: true, 
                    ticks: { stepSize: 1, precision: 0 },
                    title: {
                        display: true,
                        text: 'Cantidad de atenciones'
                    }
                }
            }
        }
    });

    // Daily Cost
    const costCtx = document.getElementById('dailyCostChart').getContext('2d');
    dailyCostChart = new Chart(costCtx, {
        type: 'bar',
        data: {
            labels: dates,
            datasets: [{
                label: 'Costo ($)',
                data: data.daily_data.map(d => d.costo),
                backgroundColor: '#f39c12',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Costo: $' + context.raw.toFixed(2);
                        },
                        afterLabel: function(context) {
                            return 'Fecha: ' + context.label;
                        }
                    }
                }
            },
            scales: { 
                y: { 
                    beginAtZero: true, 
                    ticks: { callback: v => '$' + v.toFixed(2) },
                    title: {
                        display: true,
                        text: 'Costo ($)'
                    }
                }
            }
        }
    });

    // Update badge
    $('#atenciones-badge').text(data.total_atenciones + ' atenciones');
}

function updateAtencionesStats(data) {
    console.log('===== ATENCIONES DATA RECEIVED =====');
    console.log('Full data object:', data);
    console.log('Total atenciones:', data.total_atenciones);
    console.log('Siniestros:', data.siniestros);
    console.log('Citas:', data.citas);
    console.log('Atendidas:', data.atendidas);
    console.log('Pendientes:', data.pendientes);
    console.log('Costo total:', data.costo_total);
    console.log('Pacientes únicos:', data.pacientes_unicos);
    console.log('Top baremos:', data.top_baremos);
    console.log('Top baremos length:', data.top_baremos ? data.top_baremos.length : 0);
    
    if (data.top_baremos && data.top_baremos.length > 0) {
        console.log('First baremo sample:', data.top_baremos[0]);
        console.log('First baremo costo_total type:', typeof data.top_baremos[0].costo_total);
        console.log('First baremo costo_total value:', data.top_baremos[0].costo_total);
    }
    
    // Update Total Atenciones
    $('#stat-total-atenciones .stat-value').text(data.total_atenciones || 0);
    $('#stat-total-atenciones').removeClass('loading');
    $('#date-range-badge').text((data.date_range?.from || 'N/A') + ' al ' + (data.date_range?.to || 'N/A'));

    // Update Siniestros
    $('#stat-siniestros .stat-value').text(data.siniestros || 0);
    $('#stat-siniestros').removeClass('loading');
    const sPct = data.total_atenciones > 0 ? ((data.siniestros / data.total_atenciones) * 100).toFixed(1) : 0;
    $('#siniestros-pct').text(sPct + '%');

    // Update Citas
    $('#stat-citas .stat-value').text(data.citas || 0);
    $('#stat-citas').removeClass('loading');
    const cPct = data.total_atenciones > 0 ? ((data.citas / data.total_atenciones) * 100).toFixed(1) : 0;
    $('#citas-pct').text(cPct + '%');

    // Update Pacientes Únicos
    $('#stat-pacientes-unicos .stat-value').text(data.pacientes_unicos || 0);
    $('#stat-pacientes-unicos').removeClass('loading');
    $('#promedio-paciente').text((data.promedio_por_paciente || 0) + ' atenciones/paciente');

    // Update Atendidas
    $('#stat-atendidas .stat-value').text(data.atendidas || 0);
    $('#stat-atendidas').removeClass('loading');
    
    // Update Pendientes
    $('#stat-pendientes .stat-value').text(data.pendientes || 0);
    $('#stat-pendientes').removeClass('loading');
    const pendPct = data.total_atenciones > 0 ? ((data.pendientes / data.total_atenciones) * 100).toFixed(1) : 0;
    $('#pendientes-pct').text(pendPct + '%');

    // Update Costo Total
    const costoTotal = parseFloat(data.costo_total) || 0;
    $('#stat-costo-total .stat-value').text('$ ' + costoTotal.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
    $('#stat-costo-total').removeClass('loading');
    
    const costoPromedio = parseFloat(data.costo_promedio) || 0;
    $('#costo-promedio').text('Promedio: $ ' + costoPromedio.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));

    // Update Tasa de Atención
    const tasaAtencion = parseFloat(data.tasa_atencion) || 0;
    $('#stat-tasa-atencion .stat-value').text(tasaAtencion + '%');
    $('#stat-tasa-atencion').removeClass('loading');
    $('#tasa-atencion-progress .progress-bar').css('width', tasaAtencion + '%');
    
    // Set badge based on performance
    let badgeClass = 'badge-success';
    let badgeText = 'Excelente';
    let badgeTooltip = 'Tasa de atención superior al 80% - Excelente desempeño';
    if (tasaAtencion < 60) { 
        badgeClass = 'badge-danger'; 
        badgeText = 'Bajo';
        badgeTooltip = 'Tasa de atención inferior al 60% - Requiere atención urgente';
    } else if (tasaAtencion < 80) { 
        badgeClass = 'badge-warning'; 
        badgeText = 'Regular';
        badgeTooltip = 'Tasa de atención entre 60% y 80% - Puede mejorar';
    }
    $('#tasa-atencion-badge').attr('class', 'badge ' + badgeClass).text(badgeText).attr('title', badgeTooltip);

    // Update badge in tab
    $('#atenciones-badge').text((data.total_atenciones || 0) + ' atenciones');

    // ===== TOP BAREMOS TABLE =====
    console.log('Rendering top baremos table...');
    const tableBody = $('#top-baremos-table tbody');
    
    if (!tableBody.length) {
        console.error('Table body #top-baremos-table tbody not found!');
        return;
    }
    
    // Clear existing content
    tableBody.empty();
    
    // Check if we have baremos data
    if (data.top_baremos && Array.isArray(data.top_baremos) && data.top_baremos.length > 0) {
        console.log('Rendering ' + (data.top_baremos ? data.top_baremos.length : 0) + ' baremos');
        
        let html = '';
        data.top_baremos.forEach((b, idx) => {
            // ===== FIX: Convert string values to numbers with parseFloat =====
            const baremoNombre = b.baremo_nombre || b.nombre_servicio || 'Sin nombre';
            const usoCount = parseInt(b.uso_count) || parseInt(b.count) || 0;
            
            // CRITICAL FIX: Convert costo_total to number before using toFixed()
            let costoTotalValue = 0;
            if (b.costo_total !== undefined && b.costo_total !== null) {
                costoTotalValue = parseFloat(b.costo_total);
                if (isNaN(costoTotalValue)) costoTotalValue = 0;
            }
            
            console.log('Baremo ' + (idx+1) + ': ' + baremoNombre + ', usos: ' + usoCount + ', costo: ' + costoTotalValue + ' (type: ' + (typeof costoTotalValue) + ')');
            
            // Calculate percentage
            const pct = data.total_atenciones > 0 
                ? ((usoCount / data.total_atenciones) * 100).toFixed(1) 
                : 0;
            
            // Calculate average cost per use
            const avgCost = usoCount > 0 
                ? (costoTotalValue / usoCount).toFixed(2) 
                : '0.00';
            
            // Format currency with thousand separators
            const formattedCostoTotal = '$ ' + costoTotalValue.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            
            html += '<tr>' +
                '<td title="Posición #' + (idx + 1) + ' en el ranking de uso"><strong>' + (idx + 1) + '</strong></td>' +
                '<td title="Servicio: ' + baremoNombre + '">' + baremoNombre + '</td>' +
                '<td title="Utilizado ' + usoCount + ' veces en el período"><span class="badge badge-info">' + usoCount + ' veces</span></td>' +
                '<td title="Costo total acumulado: ' + formattedCostoTotal + '">' + formattedCostoTotal + '</td>' +
                '<td title="Costo promedio por uso: $' + avgCost + '">$ ' + avgCost + '</td>' +
                '<td title="Representa el ' + pct + '% del total de atenciones">' +
                    '<div style="display: flex; align-items: center;">' +
                        '<div class="progress" style="height: 5px; width: 80px; margin-right: 8px;" title="' + pct + '% del total">' +
                            '<div class="progress-bar" style="width: ' + pct + '%; background: linear-gradient(90deg, #3498db, #2980b9);"></div>' +
                        '</div>' +
                        '<span>' + pct + '%</span>' +
                    '</div>' +
                '</td>' +
            '</tr>';
        });
        
        tableBody.html(html);
        console.log('Table rendered successfully');
    } else {
        console.log('No baremos data available, showing empty state');
        
        // Show appropriate message based on whether there's any data at all
        if (data.total_atenciones > 0) {
            // There are attentions but no baremos data - this might indicate an issue
            console.warn('Total atenciones > 0 but no baremos data. This might indicate missing relationships.');
            tableBody.html('<tr><td colspan="6" class="text-center text-warning">Hay atenciones registradas pero no se encontraron baremos asociados. Verifique la configuración.</td></tr>');
        } else {
            // No attentions at all
            tableBody.html('<tr><td colspan="6" class="text-center">No hay datos de baremos para el período seleccionado</td></tr>');
        }
    }
    
    // Log final state
    console.log('Table rows after render:', tableBody.find('tr').length);
    console.log('===== ATENCIONES UPDATE COMPLETE =====');
}

function loadAtencionesDashboard() {
    const from = $('#date_from').val();
    const to = $('#date_to').val();
    
    $('#atenciones-tab-content .stat-card').addClass('loading');
    
    $.ajax({
        url: '$atencionesDataUrl',
        method: 'GET',
        data: { date_from: from, date_to: to },
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                updateAtencionesStats(res);
                initializeAtencionesCharts(res);
            } else {
                toastr.error('Error al cargar datos de atenciones');
            }
        },
        error: function() {
            toastr.error('Error de conexión');
            $('#atenciones-tab-content .stat-card').removeClass('loading');
        }
    });
}

// ===== MANUAL TAB HANDLING =====
$(document).ready(function() {

    // ===== INICIALIZAR TOOLTIPS DE BOOTSTRAP =====
function initTooltips() {
    // Inicializar todos los elementos con atributo title
    if (typeof $.fn.tooltip === 'function') {
        $('[title]').tooltip({
            placement: 'top',
            trigger: 'hover',
            delay: { show: 500, hide: 100 }
        });
        console.log('Tooltips initialized');
    } else {
        console.warn('Bootstrap tooltip function not available');
    }
}

// Reinicializar tooltips después de cargar contenido dinámico
function refreshTooltips() {
    if (typeof $.fn.tooltip === 'function') {
        $('[title]').tooltip('dispose'); // Eliminar tooltips existentes
        $('[title]').tooltip({           // Recrear tooltips
            placement: 'top',
            trigger: 'hover',
            delay: { show: 500, hide: 100 }
        });
    }
}


    // Set default dates for atenciones tab
    if (!$('#date_from').val()) {
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        $('#date_from').val(firstDay.toISOString().split('T')[0]);
        $('#date_to').val(lastDay.toISOString().split('T')[0]);
    }

    // Tab click handlers
    $('#tab-general-btn').click(function() {
        // Update button styles
        $('#tab-general-btn').addClass('active');
        $('#tab-atenciones-btn').removeClass('active');
        
        // Show/hide content
        $('#general-tab-content').show();
        $('#atenciones-tab-content').hide();
        
        // Load general dashboard data
        loadGeneralDashboard();
    });

    $('#tab-atenciones-btn').click(function() {
        // Update button styles
        $('#tab-atenciones-btn').addClass('active');
        $('#tab-general-btn').removeClass('active');
        
        // Show/hide content
        $('#atenciones-tab-content').show();
        $('#general-tab-content').hide();
        
        // Load atenciones dashboard data
        loadAtencionesDashboard();
    });

    // Filter buttons
    $('#apply-filter').click(function() {
        loadAtencionesDashboard();
    });

    $('#reset-filter').click(function() {
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        $('#date_from').val(firstDay.toISOString().split('T')[0]);
        $('#date_to').val(lastDay.toISOString().split('T')[0]);
        loadAtencionesDashboard();
    });

    // Initial load based on active tab
    if ('$activeTabJs' === 'general') {
        $('#tab-general-btn').addClass('active');
        $('#general-tab-content').show();
        $('#atenciones-tab-content').hide();
        loadGeneralDashboard();
    } else {
        $('#tab-atenciones-btn').addClass('active');
        $('#atenciones-tab-content').show();
        $('#general-tab-content').hide();
        loadAtencionesDashboard();
    }

    // Auto-refresh every 5 minutes for general tab
    setInterval(function() {
        if ($('#tab-general-btn').hasClass('active')) {
            loadGeneralDashboard();
        }
    }, 300000);
});
JS;

$this->registerJs($js, View::POS_READY);
?>