<?php
// app/views/reportes/comisiones.php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\RmClinica;

/** @var yii\web\View $this */
$this->title = 'Reporte de Comisiones';
$this->params['breadcrumbs'][] = $this->title;

// URLs
$ajaxUrl = Url::to(['reportes/get-comisiones-detail']);
$pdfUrl = Url::to(['reportes/generate-comisiones-pdf']);

// Obtener todas las clínicas activas
$clinicas = RmClinica::find()->where(['estatus' => 'Activo'])->orderBy('nombre')->all();

// Get default dates
$today = date('Y-m-d');
$firstDayOfMonth = date('Y-m-01');
?>

<div class="reportes-comisiones-index">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center shadow-lg"
                        style="width: 80px; height: 80px; background: linear-gradient(135deg, #ff8c00 0%, #e67e00 100%);">
                        <i class="fas fa-hand-holding-usd text-white" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
                <div>
                    <h1 class="display-6 fw-bold text-dark mb-1">
                        <?= Html::encode($this->title) ?>
                    </h1>
                    <p class="text-muted mb-0 fs-5">
                        <i class="fas fa-chart-pie me-2"></i>Análisis y distribución de comisiones por pagos
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros Section - Professional Design -->
    <div class="card border-0 shadow-lg mb-5 overflow-hidden">
        <div class="card-header py-4" style="background: linear-gradient(135deg, #2c3e50 0%, #4a6491 100%);">
            <div class="d-flex align-items-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center me-3 shadow"
                    style="width: 50px; height: 50px; background: rgba(255, 255, 255, 0.2);">
                    <i class="fas fa-sliders-h text-white" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                    <h3 class="mb-0 text-white fw-bold">
                        <i class="fas fa-filter me-2"></i>Filtros del Reporte
                    </h3>
                    <p class="mb-0 text-white-50 fs-6">Configure los parámetros para generar el análisis de comisiones</p>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
            <!-- Row 1: Main Filters -->
            <div class="row g-4 mb-4">
                <!-- Clínicas -->
                <div class="col-xl-4 col-lg-4">
                    <div class="filter-card h-100 border-0 shadow-sm rounded-3 p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                                style="width: 40px; height: 40px; background: linear-gradient(135deg, #107c10 0%, #0e6a0e 100%);">
                                <i class="fas fa-hospital text-white" style="font-size: 1.2rem;"></i>
                            </div>
                            <div>
                                <label class="form-label fw-bold mb-0" style="font-size: 1.2rem !important; color: #2c3e50;">
                                    Selección de Clínicas
                                </label>
                                <small class="text-muted d-block" style="font-size: 1rem !important;">
                                    Seleccione una o múltiples clínicas
                                </small>
                            </div>
                        </div>
                        <select id="clinica-filter" class="form-select select2-multiple border-2 border-success shadow-none"
                            style="font-size: 1.2rem !important; min-height: 50px; border-radius: 8px;" multiple="multiple">
                            <option value="todas" selected>🏥 Todas las Clínicas</option>
                            <?php foreach ($clinicas as $clinica): ?>
                                <option value="<?= $clinica->id ?>">
                                    <i class="fas fa-clinic-medical me-2"></i><?= Html::encode($clinica->nombre) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Estado de Pagos -->
                <div class="col-xl-4 col-lg-4">
                    <div class="filter-card h-100 border-0 shadow-sm rounded-3 p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                                style="width: 40px; height: 40px; background: linear-gradient(135deg, #ff8c00 0%, #e67e00 100%);">
                                <i class="fas fa-check-circle text-white" style="font-size: 1.2rem;"></i>
                            </div>
                            <div>
                                <label class="form-label fw-bold mb-0" style="font-size: 1.2rem !important; color: #2c3e50;">
                                    Estado de Pagos
                                </label>
                                <small class="text-muted d-block" style="font-size: 1rem !important;">
                                    Filtrar por estado de conciliación
                                </small>
                            </div>
                        </div>
                        <select id="status-filter" class="form-select border-2 border-warning shadow-none py-2"
                            style="font-size: 1.2rem !important; height: 50px; border-radius: 8px;">
                            <option value="todos" selected>✅ Todos los Estados</option>
                            <option value="Conciliado">🟢 Conciliados</option>
                            <option value="Por Conciliar">🟡 Por Conciliar</option>
                        </select>
                    </div>
                </div>

                <!-- Período de Análisis -->
                <div class="col-xl-4 col-lg-4">
                    <div class="filter-card h-100 border-0 shadow-sm rounded-3 p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                                style="width: 40px; height: 40px; background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);">
                                <i class="fas fa-calendar-alt text-white" style="font-size: 1.2rem;"></i>
                            </div>
                            <div>
                                <label class="form-label fw-bold mb-0" style="font-size: 1.2rem !important; color: #2c3e50;">
                                    Período de Análisis
                                </label>
                                <small class="text-muted d-block" style="font-size: 1rem !important;">
                                    Seleccione el período del reporte
                                </small>
                            </div>
                        </div>
                        <select id="date-range-selector" class="form-select border-2 border-primary shadow-none py-2"
                            style="font-size: 1.2rem !important; height: 50px; border-radius: 8px;">
                            <option value="day">📅 Hoy</option>
                            <option value="week">📆 Última Semana</option>
                            <option value="month" selected>🗓️ Mes Actual</option>
                            <option value="last-month">📊 Mes Anterior</option>
                            <option value="custom">🎯 Rango Personalizado</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Row 2: Custom Date Range (Initially hidden) -->
            <div class="row" id="custom-dates-container" style="display: none !important;">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                        <div class="card-header py-3" style="background: linear-gradient(135deg, #ff8c00 0%, #e67e00 100%);">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar-range text-white me-2" style="font-size: 1.3rem;"></i>
                                <h4 class="mb-0 text-white fw-bold" style="font-size: 1.3rem !important;">
                                    Rango Personalizado de Fechas
                                </h4>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="date-input-card">
                                        <label class="form-label fw-bold mb-3 d-flex align-items-center"
                                            style="font-size: 1.2rem !important; color: #2c3e50;">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                                                style="width: 45px; height: 45px; background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);">
                                                <i class="fas fa-calendar-day text-white" style="font-size: 1.3rem;"></i>
                                            </div>
                                            <div>
                                                Fecha Inicial
                                                <small class="text-muted d-block" style="font-size: 1rem !important;">
                                                    Fecha de inicio del período
                                                </small>
                                            </div>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-2 border-primary border-end-0 py-3"
                                                style="font-size: 1.2rem !important;">
                                                <i class="fas fa-play-circle ms-primary"></i>
                                            </span>
                                            <input type="date" id="date-from" class="form-control border-2 border-primary border-start-0 py-3"
                                                style="font-size: 1.2rem !important;" value="<?= $firstDayOfMonth ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="date-input-card">
                                        <label class="form-label fw-bold mb-3 d-flex align-items-center"
                                            style="font-size: 1.2rem !important; color: #2c3e50;">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                                                style="width: 45px; height: 45px; background: linear-gradient(135deg, #107c10 0%, #0e6a0e 100%);">
                                                <i class="fas fa-calendar-day text-white" style="font-size: 1.3rem;"></i>
                                            </div>
                                            <div>
                                                Fecha Final
                                                <small class="text-muted d-block" style="font-size: 1rem !important;">
                                                    Fecha de fin del período
                                                </small>
                                            </div>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-2 border-success border-end-0 py-3"
                                                style="font-size: 1.2rem !important;">
                                                <i class="fas fa-flag-checkered ms-success"></i>
                                            </span>
                                            <input type="date" id="date-to" class="form-control border-2 border-success border-start-0 py-3"
                                                style="font-size: 1.2rem !important;" value="<?= $today ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Generate Button (Centered, not in a card) -->
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <button id="btn-aplicar-filtros" class="btn btn-warning py-3 px-5 fw-bold shadow-lg"
                        style="font-size: 1.3rem !important; border-radius: 10px; border: none; min-width: 300px;">
                        <i class="fas fa-play-circle me-2"></i>Generar Reporte
                    </button>
                    <div class="mt-2">
                        <small class="text-muted" style="font-size: 1.1rem !important;">
                            <i class="fas fa-bolt me-1"></i>Presione para generar el reporte con los filtros seleccionados
                        </small>
                    </div>
                </div>
            </div>

            <!-- Commission Information -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert alert-light border-0 rounded-3 p-3" style="background: #f8f9fa;">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                                        style="width: 50px; height: 50px; background: linear-gradient(135deg, #ff8c00 0%, #e67e00 100%);">
                                        <i class="fas fa-percentage text-white" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1 fw-bold" style="font-size: 1.3rem !important; color: #2c3e50;">
                                            <i class="fas fa-info-circle me-2 ms-warning"></i>Información de Comisiones
                                        </h5>
                                        <p class="mb-0 text-muted" style="font-size: 1.1rem !important;">
                                            Las comisiones se calculan sobre el monto total de cada pago
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="text-center p-2 rounded-3" style="background: #fff9e6; border: 2px solid #ff8c00;">
                                            <span class="fw-bold d-block" style="font-size: 1.1rem !important; color: #ff8c00;">
                                                <i class="fas fa-user-tie me-1"></i>Asesor
                                            </span>
                                            <span class="fw-bold" style="font-size: 1.3rem !important; color: #2c3e50;">
                                                10%
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-2 rounded-3" style="background: #ffe6e6; border: 2px solid #d13438;">
                                            <span class="fw-bold d-block" style="font-size: 1.1rem !important; color: #d13438;">
                                                <i class="fas fa-building me-1"></i>Agencia
                                            </span>
                                            <span class="fw-bold" style="font-size: 1.3rem !important; color: #2c3e50;">
                                                4%
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de Resultados -->
    <div class="row" id="report-results">
        <!-- Contenido dinámico cargado aquí -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="fas fa-hand-holding-usd fa-5x text-warning mb-4 opacity-25"></i>
                    </div>
                    <h3 class="text-dark mb-3 display-6">Panel de Reportes de Comisiones</h3>
                    <p class="text-muted mb-4 fs-5">
                        Configure los filtros arriba y presione <span class="badge bg-warning px-4 py-3 fs-5">Generar Reporte</span><br>
                        para visualizar el análisis de comisiones.
                    </p>
                    <div class="row mt-5">
                        <div class="col-md-6 mb-3">
                            <div class="alert alert-warning border-0 shadow-sm p-4 h-100">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                                        style="width: 60px; height: 60px; background: linear-gradient(135deg, #ff8c00 0%, #e67e00 100%);">
                                        <i class="fas fa-user-tie text-white" style="font-size: 1.8rem;"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-1">Comisión Asesor</h5>
                                        <p class="mb-0 text-muted">Sobre el monto total de cada pago</p>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <span class="display-6 fw-bold text-warning">10%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="alert alert-danger border-0 shadow-sm p-4 h-100">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                                        style="width: 60px; height: 60px; background: linear-gradient(135deg, #d13438 0%, #a4262c 100%);">
                                        <i class="fas fa-building text-white" style="font-size: 1.8rem;"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-1">Comisión Agencia</h5>
                                        <p class="mb-0 text-muted">Sobre el monto total de cada pago</p>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <span class="display-6 fw-bold text-danger">4%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// CSS Styles for the improved design
$this->registerCss(
    <<<CSS
    /* Filter Card Styles */
    .filter-card {
        background: white;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid #e9ecef !important;
    }
    
    .filter-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1) !important;
        border-color: #c8c6c4 !important;
    }
    
    /* Date Input Card */
    .date-input-card {
        background: white;
        padding: 1.5rem;
        border-radius: 10px;
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .date-input-card:hover {
        border-color: #0078d4;
        box-shadow: 0 4px 12px rgba(0, 120, 212, 0.1);
    }
    
    /* Form Controls */
    .form-select, .form-control {
        font-size: 1.2rem !important;
        padding: 0.75rem 1rem !important;
        border-radius: 8px !important;
        border: 2px solid #dee2e6 !important;
        transition: all 0.2s ease !important;
    }
    
    .form-select:focus, .form-control:focus {
        border-color: #0078d4 !important;
        box-shadow: 0 0 0 3px rgba(0, 120, 212, 0.1) !important;
    }
    
    /* Button Styles */
    #btn-aplicar-filtros {
        background: linear-gradient(135deg, #ff8c00 0%, #e67e00 100%);
        color: white;
        border: none;
        padding: 0.875rem 1.5rem;
        font-weight: 600;
        font-size: 1.3rem;
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    
    #btn-aplicar-filtros:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(255, 140, 0, 0.4) !important;
        background: linear-gradient(135deg, #e67e00 0%, #cc6f00 100%);
    }
    
    #btn-aplicar-filtros:active {
        transform: translateY(-1px);
    }
    
    /* Select2 Customization */
    .select2-container--default .select2-selection--multiple {
        border: 2px solid #dee2e6 !important;
        border-radius: 8px !important;
        min-height: 50px !important;
        padding: 0.375rem !important;
        font-size: 1.2rem !important;
    }
    
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #107c10 !important;
        box-shadow: 0 0 0 3px rgba(16, 124, 16, 0.1) !important;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background: #dff6dd !important;
        border: 1px solid #b2d8b1 !important;
        border-radius: 6px !important;
        padding: 0.375rem 0.75rem !important;
        font-size: 1.1rem !important;
    }
    
    /* Card Header Gradient */
    .card-header {
        border-bottom: none !important;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .filter-card {
            margin-bottom: 1rem;
        }
        
        #btn-aplicar-filtros {
            width: 100%;
            padding: 1rem;
            min-width: auto !important;
        }
        
        .display-6 {
            font-size: 2rem !important;
        }
        
        .fs-5 {
            font-size: 1.1rem !important;
        }
    }
    
    /* Animation for custom dates */
    #custom-dates-container {
        animation: slideDown 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Icon colors */
    .ms-primary { color: #0078d4 !important; }
    .ms-success { color: #107c10 !important; }
    .ms-warning { color: #ff8c00 !important; }
    .ms-danger { color: #d13438 !important; }
CSS
);

// Fixed JavaScript section
$this->registerJs(
    <<<JS
    // =============================================
    // CONFIGURACIÓN INICIAL - COMISIONES
    // =============================================
    const config = {
        ajaxUrl: '{$ajaxUrl}',
        selectors: {
            results: '#report-results',
            clinica: '#clinica-filter',
            status: '#status-filter',
            dateRange: '#date-range-selector',
            dateFrom: '#date-from',
            dateTo: '#date-to',
            customDatesContainer: '#custom-dates-container',
            aplicarBtn: '#btn-aplicar-filtros'
        }
    };
    
    console.log('AJAX URL:', config.ajaxUrl);
    
    // =============================================
    // INICIALIZACIÓN DE COMPONENTES
    // =============================================
    function initComponents() {
        // Inicializar Select2 para selección múltiple
        if (\$.fn.select2 && \$(config.selectors.clinica).length) {
            \$(config.selectors.clinica).select2({
                placeholder: "Seleccione clínicas...",
                width: '100%',
                allowClear: true,
                theme: 'bootstrap-5',
                closeOnSelect: false
            });
        }
        
        // Set default dates for range inputs (Month range)
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        
        \$(config.selectors.dateFrom).val(formatDate(firstDay));
        \$(config.selectors.dateTo).val(formatDate(today));
        
        // Set default range selector to "Mes Actual"
        \$(config.selectors.dateRange).val('month');
        
        // Set default status to "todos"
        \$(config.selectors.status).val('todos');
        
        // Ensure custom dates container is hidden on load
        \$(config.selectors.customDatesContainer).hide();
        
        // Update dates for current month
        updateDateInputsByRange('month');
    }
    
    // =============================================
    // FUNCIONES UTILITARIAS
    // =============================================
    function showLoading() {
        \$(config.selectors.results).html(
            '<div class="col-12">' +
                '<div class="card border-0 shadow-lg">' +
                    '<div class="card-body text-center py-5">' +
                        '<div class="loading-pulse">' +
                            '<div class="mb-4">' +
                                '<div class="spinner-grow text-warning" style="width: 4rem; height: 4rem;" role="status">' +
                                    '<span class="visually-hidden">Cargando...</span>' +
                                '</div>' +
                            '</div>' +
                            '<h3 class="text-warning mb-3">Generando Reporte de Comisiones</h3>' +
                            '<p class="text-muted fs-5">Calculando comisiones y generando análisis...</p>' +
                            '<div class="mt-4">' +
                                '<div class="progress" style="height: 8px; max-width: 300px; margin: 0 auto;">' +
                                    '<div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" style="width: 75%"></div>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>'
        );
    }
    
    function showError(message) {
        \$(config.selectors.results).html(
            '<div class="col-12">' +
                '<div class="alert alert-danger alert-dismissible fade show shadow-lg" role="alert">' +
                    '<div class="d-flex align-items-center">' +
                        '<div class="rounded-circle d-flex align-items-center justify-content-center me-3" ' +
                            'style="width: 60px; height: 60px; background: linear-gradient(135deg, #d13438 0%, #a4262c 100%);">' +
                            '<i class="fas fa-exclamation-triangle text-white" style="font-size: 1.8rem;"></i>' +
                        '</div>' +
                        '<div>' +
                            '<h5 class="alert-heading mb-1 fw-bold" style="font-size: 1.4rem !important;">Error en la Generación</h5>' +
                            '<p class="mb-0 fs-5">' + message + '</p>' +
                        '</div>' +
                    '</div>' +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                '</div>' +
            '</div>'
        );
    }
    
    function validateDateRange(dateFrom, dateTo) {
        if (!dateFrom || !dateTo) {
            return { valid: false, message: 'Por favor seleccione ambas fechas.' };
        }
        
        if (dateFrom > dateTo) {
            return { valid: false, message: 'La fecha inicial no puede ser mayor a la fecha final.' };
        }
        
        return { valid: true };
    }
    
    // =============================================
    // FUNCIÓN PRINCIPAL - CARGAR REPORTE DE COMISIONES
    // =============================================
    async function cargarReporteComisiones(params = {}) {
        try {
            console.log('Iniciando carga de reporte de comisiones con params:', params);
            showLoading();
        
            const csrfToken = \$('meta[name="csrf-token"]').attr('content') || '';
        
            const requestData = {
                range: params.range || \$(config.selectors.dateRange).val(),
                clinicas: params.clinicas || \$(config.selectors.clinica).val() || [],
                status: params.status || \$(config.selectors.status).val() || 'todos',
                _csrf: csrfToken
            };
            
            // Add custom date parameters if needed
            if (requestData.range === 'custom') {
                requestData.custom_range = true;
                requestData.date_from = \$(config.selectors.dateFrom).val();
                requestData.date_to = \$(config.selectors.dateTo).val();
            }
        
            console.log('Enviando request data:', requestData);
            console.log('URL:', config.ajaxUrl);
            
            const response = await \$.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: requestData,
                dataType: 'json',
                headers: {
                    'X-CSRF-Token': csrfToken
                }
            });
            
            console.log('Respuesta recibida:', response);
            
            if (response.success) {
                // Mostrar directamente el HTML recibido del servidor
                \$(config.selectors.results).html(response.html);
                
                // Scroll suave a resultados
                \$('html, body').animate({
                    scrollTop: \$(config.selectors.results).offset().top - 100
                }, 500);
                
            } else {
                console.error('Error en respuesta:', response.message);
                showError(response.message || 'Error al procesar el reporte de comisiones.');
            }
            
        } catch (error) {
            console.error('Error en carga de reporte de comisiones:', error);
            console.error('Error status:', error.status);
            console.error('Error response:', error.responseText);
            
            let errorMessage = 'Error de conexión con el servidor.';
            if (error.status === 403) {
                errorMessage = 'Acceso denegado. Verifique sus permisos.';
            } else if (error.status === 500) {
                try {
                    const errorData = JSON.parse(error.responseText);
                    errorMessage = errorData.message || 'Error interno del servidor.';
                } catch (e) {
                    errorMessage = 'Error del servidor (500). Consulte los logs del sistema.';
                }
            } else if (error.status === 404) {
                errorMessage = 'URL no encontrada. Verifique la configuración del sistema.';
            }
            
            showError(errorMessage);
        }
    }
    
    // =============================================
    // FUNCIONES PARA MANEJAR EL RANGO DE FECHAS
    // =============================================
    function updateDateInputsByRange(range) {
        const today = new Date();
        
        switch (range) {
            case 'day':
                \$(config.selectors.dateFrom).val(formatDate(today));
                \$(config.selectors.dateTo).val(formatDate(today));
                \$(config.selectors.customDatesContainer).slideUp(200);
                break;
            case 'week':
                const weekAgo = new Date(today);
                weekAgo.setDate(today.getDate() - 7);
                \$(config.selectors.dateFrom).val(formatDate(weekAgo));
                \$(config.selectors.dateTo).val(formatDate(today));
                \$(config.selectors.customDatesContainer).slideUp(200);
                break;
            case 'month':
                const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                \$(config.selectors.dateFrom).val(formatDate(firstDay));
                \$(config.selectors.dateTo).val(formatDate(today));
                \$(config.selectors.customDatesContainer).slideUp(200);
                break;
            case 'last-month':
                const lastMonthStart = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                const lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 0);
                \$(config.selectors.dateFrom).val(formatDate(lastMonthStart));
                \$(config.selectors.dateTo).val(formatDate(lastMonthEnd));
                \$(config.selectors.customDatesContainer).slideUp(200);
                break;
            case 'custom':
                \$(config.selectors.customDatesContainer).slideDown(200);
                break;
        }
    }
    
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return year + '-' + month + '-' + day;
    }
    
    // =============================================
    // MANEJADORES DE EVENTOS
    // =============================================
    function setupEventHandlers() {
        // Cambio en selector de rango de fechas
        \$(config.selectors.dateRange).on('change', function() {
            const range = \$(this).val();
            updateDateInputsByRange(range);
        });
        
        // Botón principal de generar reporte
        \$(config.selectors.aplicarBtn).on('click', function() {
            // Add click animation
            \$(this).css('transform', 'translateY(0)');
            setTimeout(() => {
                \$(this).css('transform', 'translateY(-2px)');
            }, 100);
            
            generateComisionesReport();
        });
        
        // Permitir Enter en campos de fecha
        \$(config.selectors.dateFrom).add(config.selectors.dateTo).on('keypress', function(e) {
            if (e.which === 13) generateComisionesReport();
        });
    }
    
    // =============================================
    // FUNCIÓN PRINCIPAL PARA GENERAR REPORTE DE COMISIONES
    // =============================================
    function generateComisionesReport() {
        const clinicas = \$(config.selectors.clinica).val() || [];
        const dateRange = \$(config.selectors.dateRange).val();
        const status = \$(config.selectors.status).val();
        
        let params = {
            clinicas: clinicas,
            status: status || 'todos'
        };
        
        if (dateRange === 'custom') {
            const dateFrom = \$(config.selectors.dateFrom).val();
            const dateTo = \$(config.selectors.dateTo).val();
            
            if (!dateFrom || !dateTo) {
                alert('Por favor seleccione ambas fechas para el rango personalizado.');
                return;
            }
            
            const validation = validateDateRange(dateFrom, dateTo);
            if (!validation.valid) {
                alert(validation.message);
                return;
            }
            
            params.custom_range = true;
            params.date_from = dateFrom;
            params.date_to = dateTo;
        } else {
            params.range = dateRange;
        }
        
        console.log('Generando reporte de comisiones con parámetros:', params);
        cargarReporteComisiones(params);
    }
    
    // =============================================
    // INICIALIZACIÓN DE LA PÁGINA
    // =============================================
    \$(document).ready(function() {
        console.log('Reporte de Comisiones cargado correctamente');
        console.log('AJAX URL configurada:', config.ajaxUrl);
        
        initComponents();
        setupEventHandlers();
        
        console.log('Custom dates container hidden:', \$(config.selectors.customDatesContainer).is(':hidden'));
        
        console.log('All handlers initialized');
    });
JS
);
