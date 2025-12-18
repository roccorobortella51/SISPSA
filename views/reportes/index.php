<?php
// app/views/reportes/index.php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\RmClinica;

/** @var yii\web\View $this */
$this->title = 'Reporte de Pagos de Afiliados';
$this->params['breadcrumbs'][] = $this->title;


// URLs
$ajaxUrl = Url::to(['get-pagos-detail']);
$pdfUrl = Url::to(['generate-pdf']);

// Obtener todas las clínicas activas
$clinicas = RmClinica::find()->where(['estatus' => 'Activo'])->orderBy('nombre')->all();
?>

<div class="reportes-pagos-index">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="text-center">
                <h1 class="display-5 fw-bold text-primary mb-2">
                    <i class="fas fa-chart-pie me-2"></i> <?= Html::encode($this->title) ?>
                </h1>
                <p class="lead text-muted">Análisis financiero y gestión de pagos</p>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-lg mb-5"
        style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-left: 4px solid #0078d4;">
        <div class="card-body p-4">
            <!-- Instrucciones Mejoradas -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-light border-0 shadow-lg p-3"
                        style="background: white; border-left: 4px solid #107c10;">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3 shadow"
                                style="width: 60px; height: 60px; background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);">
                                <i class="fas fa-sliders-h text-white" style="font-size: 1.8rem;"></i>
                            </div>
                            <div>
                                <h3 class="mb-1 fw-bold" style="font-size: 1.6rem !important; color: #2c3e50;">
                                    <i class="fas fa-filter me-2 ms-primary"></i>Configuración del Reporte
                                </h3>
                                <p class="mb-0 ms-body-lg text-muted" style="font-size: 1.4rem !important;">
                                    Configure los parámetros para generar el análisis de pagos
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILTROS EN UNA SOLA FILA - AJUSTADA -->
            <div class="row g-3 align-items-end">
                <!-- Estado -->
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="filter-group-ms border-0 shadow-lg p-3 rounded-3"
                        style="background: white; border-left: 3px solid #0078d4;">
                        <label class="form-label fw-bold mb-2 d-flex align-items-center"
                            style="font-size: 1.4rem !important; color: #2c3e50;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm"
                                style="width: 40px; height: 40px; background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);">
                                <i class="fas fa-check-circle text-white" style="font-size: 1.4rem;"></i>
                            </div>
                            Estado de Pagos
                        </label>
                        <select id="pago-status-selector" class="form-select border-2 border-primary shadow-sm py-2"
                            style="font-size: 1.4rem !important; height: 55px; border-radius: 8px;">
                            <option value="todos" class="py-2">
                                <span style="font-size: 1.4rem !important;">📊 Todos los Estados</span>
                            </option>
                            <option value="Por Conciliar" class="py-2">
                                <span style="font-size: 1.4rem !important;">⏳ Pendientes por Conciliar</span>
                            </option>
                            <option value="Conciliado" class="py-2">
                                <span style="font-size: 1.4rem !important;">✅ Pagos Conciliados</span>
                            </option>
                        </select>
                        <div class="mt-1">
                            <small class="text-muted" style="font-size: 1.2rem !important;">
                                <i class="fas fa-info-circle me-2 ms-primary"></i>Filtre por estado de conciliación
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Clínicas -->
                <div class="col-xl-4 col-lg-5 col-md-6">
                    <div class="filter-group-ms border-0 shadow-lg p-3 rounded-3"
                        style="background: white; border-left: 3px solid #107c10;">
                        <label class="form-label fw-bold mb-2 d-flex align-items-center"
                            style="font-size: 1.4rem !important; color: #2c3e50;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm"
                                style="width: 40px; height: 40px; background: linear-gradient(135deg, #107c10 0%, #0e6a0e 100%);">
                                <i class="fas fa-hospital text-white" style="font-size: 1.4rem;"></i>
                            </div>
                            Selección de Clínicas
                        </label>
                        <select id="clinica-filter" class="form-select select2-multiple border-2 border-success shadow-sm"
                            style="font-size: 1.4rem !important; min-height: 55px; border-radius: 8px;" multiple="multiple">
                            <option value="todas" selected class="py-2">
                                <span style="font-size: 1.4rem !important;">🏥 Todas las Clínicas</span>
                            </option>
                            <?php foreach ($clinicas as $clinica): ?>
                                <option value="<?= $clinica->id ?>" class="py-2">
                                    <span style="font-size: 1.4rem !important;">
                                        <i class="fas fa-clinic-medical me-2"></i><?= Html::encode($clinica->nombre) ?>
                                    </span>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="mt-1">
                            <small class="text-muted" style="font-size: 1.2rem !important;">
                                <i class="fas fa-info-circle me-2 ms-success"></i>Seleccione una o múltiples clínicas
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Rango de Fechas -->
                <div class="col-xl-3 col-lg-3 col-md-6">
                    <div class="filter-group-ms border-0 shadow-lg p-3 rounded-3"
                        style="background: white; border-left: 3px solid #ff8c00;">
                        <label class="form-label fw-bold mb-2 d-flex align-items-center"
                            style="font-size: 1.4rem !important; color: #2c3e50;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm"
                                style="width: 40px; height: 40px; background: linear-gradient(135deg, #ff8c00 0%, #e67e00 100%);">
                                <i class="fas fa-calendar-alt text-white" style="font-size: 1.4rem;"></i>
                            </div>
                            Período de Análisis
                        </label>
                        <select id="date-range-selector" class="form-select border-2 border-warning shadow-sm py-2"
                            style="font-size: 1.4rem !important; height: 55px; border-radius: 8px;">
                            <option value="day" class="py-2">
                                <span style="font-size: 1.4rem !important;">📅 Hoy</span>
                            </option>
                            <option value="week" class="py-2">
                                <span style="font-size: 1.4rem !important;">📆 Última Semana</span>
                            </option>
                            <option value="month" class="py-2">
                                <span style="font-size: 1.4rem !important;">🗓️ Mes Actual</span>
                            </option>
                            <option value="last-month" class="py-2">
                                <span style="font-size: 1.4rem !important;">📊 Mes Anterior</span>
                            </option>
                            <option value="custom" class="py-2">
                                <span style="font-size: 1.4rem !important;">🎯 Rango Personalizado</span>
                            </option>
                        </select>
                        <div class="mt-1">
                            <small class="text-muted" style="font-size: 1.2rem !important;">
                                <i class="fas fa-info-circle me-2 ms-warning"></i>Defina el período del reporte
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Fechas Personalizadas - Ajustada -->
                <div class="col-12" id="custom-dates-container" style="display: none;">
                    <div class="filter-group-ms border-0 shadow-lg p-4 rounded-3 ms-fade-in mt-3"
                        style="background: linear-gradient(135deg, #fff4ce 0%, #ffe8a3 100%); border: 2px solid #ff8c00;">
                        <div class="row align-items-center">
                            <div class="col-lg-3 mb-3 mb-lg-0">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3 shadow"
                                        style="width: 50px; height: 50px; background: linear-gradient(135deg, #d13438 0%, #a4262c 100%);">
                                        <i class="fas fa-calendar-range text-white" style="font-size: 1.6rem;"></i>
                                    </div>
                                    <div>
                                        <h4 class="fw-bold mb-1" style="font-size: 1.4rem !important; color: #d13438;">
                                            Rango Personalizado
                                        </h4>
                                        <p class="mb-0 text-muted" style="font-size: 1.2rem !important;">
                                            Defina fechas específicas
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-9">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="card border-0 shadow-sm h-100"
                                            style="border-left: 3px solid #0078d4;">
                                            <div class="card-body p-3">
                                                <label class="form-label fw-bold mb-2 d-flex align-items-center"
                                                    style="font-size: 1.4rem !important; color: #2c3e50;">
                                                    <i class="fas fa-calendar-day me-2 ms-primary"></i>
                                                    Fecha Inicial
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white border-2 border-primary border-end-0 py-2"
                                                        style="font-size: 1.4rem !important;">
                                                        <i class="fas fa-play-circle ms-primary"></i>
                                                    </span>
                                                    <input type="date" id="date-from" class="form-control border-2 border-primary border-start-0 py-2"
                                                        style="font-size: 1.4rem !important; height: auto;">
                                                </div>
                                                <div class="mt-1">
                                                    <small class="text-muted" style="font-size: 1.1rem !important;">
                                                        <i class="fas fa-calendar-check me-2"></i>Fecha de inicio del período
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-0 shadow-sm h-100"
                                            style="border-left: 3px solid #107c10;">
                                            <div class="card-body p-3">
                                                <label class="form-label fw-bold mb-2 d-flex align-items-center"
                                                    style="font-size: 1.4rem !important; color: #2c3e50;">
                                                    <i class="fas fa-calendar-day me-2 ms-success"></i>
                                                    Fecha Final
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white border-2 border-success border-end-0 py-2"
                                                        style="font-size: 1.4rem !important;">
                                                        <i class="fas fa-flag-checkered ms-success"></i>
                                                    </span>
                                                    <input type="date" id="date-to" class="form-control border-2 border-success border-start-0 py-2"
                                                        style="font-size: 1.4rem !important; height: auto;">
                                                </div>
                                                <div class="mt-1">
                                                    <small class="text-muted" style="font-size: 1.1rem !important;">
                                                        <i class="fas fa-calendar-times me-2"></i>Fecha de fin del período
                                                    </small>
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

            <!-- Botón Generar Reporte - AJUSTADO -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="text-center border-top pt-4">
                        <div class="d-inline-block p-3 rounded-3 shadow-lg"
                            style="background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);">
                            <?= Html::button('<i class="fas fa-chart-bar me-3"></i> Generar Reporte', [
                                'id' => 'btn-aplicar-filtros',
                                'class' => 'btn btn-light btn-xl px-4 py-3 fw-bold shadow-lg',
                                'style' => 'font-size: 1.6rem !important; border-radius: 10px;'
                            ]) ?>
                            <div class="mt-2">
                                <small class="text-white" style="font-size: 1.2rem !important;">
                                    <i class="fas fa-bolt me-2"></i>Presione para generar el reporte con los filtros seleccionados
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                
                <!-- Columna 2: Rango de Fechas Personalizado -->
                <div class="col-lg-6">
                    <div class="filter-card">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-calendar-range text-danger me-2 fs-5"></i>
                            <h5 class="mb-0 fw-bold">Rango de Fechas Personalizado</h5>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-calendar-day text-primary"></i>
                                    </span>
                                    <input type="date" id="date-from" class="form-control border-primary" 
                                           placeholder="Desde">
                                </div>
                                <small class="text-muted mt-1 d-block">Fecha inicial</small>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-calendar-day text-primary"></i>
                                    </span>
                                    <input type="date" id="date-to" class="form-control border-primary" 
                                           placeholder="Hasta">
                                </div>
                                <small class="text-muted mt-1 d-block">Fecha final</small>
                            </div>
                        </div>
                        <div class="mt-3">
                            <?= Html::button('<i class="fas fa-search me-2"></i> Consultar Rango', [
                                'id' => 'btn-custom-range',
                                'class' => 'btn btn-primary w-100'
                            ]) ?>
                        </div>
                        <small class="text-muted mt-2 d-block">Seleccione un rango personalizado para el reporte</small>
                    </div>
                </div>
            </div>
            
            <!-- Botón de Acción Principal -->
            <div class="text-center mt-5 pt-3 border-top">
                <?= Html::button('<i class="fas fa-rocket me-2"></i> Generar Reporte', [
                    'id' => 'btn-aplicar-filtros',
                    'class' => 'btn btn-primary btn-lg px-5 py-3 fw-bold shadow-sm'
                ]) ?>
                <p class="text-muted mt-3 mb-0">
                    <i class="fas fa-info-circle me-1"></i> 
                    Los filtros se aplican automáticamente al cambiar cualquier opción
                </p>
            </div>
        </div>
        
        <!-- Footer del Card -->
        <div class="card-footer bg-light py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-database text-primary me-2"></i>
                        <small class="text-muted">Datos en tiempo real • Actualización automática</small>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">
                        <i class="fas fa-file-pdf me-1 text-danger"></i>
                        Exportable a PDF con un solo clic
                    </small>
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
                        <i class="fas fa-chart-bar fa-5x text-muted mb-4 opacity-25"></i>
                    </div>
                    <h3 class="text-dark mb-3 display-6">Panel de Reportes de Pagos</h3>
                    <p class="text-muted mb-4 fs-4">
                        Configure los filtros arriba y presione <span class="badge bg-primary px-4 py-3 fs-5">Generar Reporte</span><br>
                        para visualizar el análisis de datos.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

// Complete CSS Section with Microsoft Professional Design and 3x larger fonts
$this->registerCss(
    <<<CSS
    /* =============================================
       MICROSOFT PROFESSIONAL DESIGN SYSTEM - 3X LARGER
       ============================================= */
    
    /* Microsoft Font System */
    .reportes-pagos-index,
    .ms-card,
    .ms-table,
    .ms-btn,
    .ms-badge,
    .ms-title-xl,
    .ms-title-lg,
    .ms-title-md,
    .ms-title-sm,
    .ms-body-lg,
    .ms-body,
    .ms-body-sm {
        font-family: 'Segoe UI', 'Segoe UI Web (West European)', 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, 'Helvetica Neue', sans-serif !important;
    }
    
    /* Microsoft Color Palette */
    .ms-primary { color: #0078d4 !important; }
    .ms-primary-bg { background-color: #0078d4 !important; }
    .ms-primary-light { color: #50e6ff !important; }
    .ms-primary-gradient { background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%) !important; }
    
    .ms-success { color: #107c10 !important; }
    .ms-success-bg { background-color: #107c10 !important; }
    .ms-success-light { color: #9bf00b !important; }
    
    .ms-warning { color: #ff8c00 !important; }
    .ms-warning-bg { background-color: #ff8c00 !important; }
    
    .ms-danger { color: #d13438 !important; }
    .ms-danger-bg { background-color: #d13438 !important; }
    
    .ms-neutral-10 { color: #faf9f8 !important; }
    .ms-neutral-20 { color: #f3f2f1 !important; }
    .ms-neutral-30 { color: #edebe9 !important; }
    .ms-neutral-90 { color: #201f1e !important; }
    
    /* Microsoft Card Design */
    .ms-card {
        background: white !important;
        border: 1px solid #edebe9 !important;
        border-radius: 8px !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        margin-bottom: 2rem !important;
    }
    
    .ms-card:hover {
        box-shadow: 0 8px 32px rgba(0,0,0,0.12) !important;
        border-color: #c8c6c4 !important;
    }
    
    .ms-card-header {
        background: #faf9f8 !important;
        border-bottom: 1px solid #edebe9 !important;
        padding: 2rem 2.5rem !important;
        border-radius: 8px 8px 0 0 !important;
    }
    
    .ms-card-body {
        padding: 2.5rem !important;
    }
    
    .ms-card-footer {
        background: #faf9f8 !important;
        border-top: 1px solid #edebe9 !important;
        padding: 1.5rem 2.5rem !important;
        border-radius: 0 0 8px 8px !important;
    }
    
    /* =============================================
       TYPOGRAPHY - 3X LARGER FONTS
       ============================================= */
    
    /* Microsoft Typography Scale - 3x larger */
    .ms-title-xl {
        font-size: 3.75rem !important; /* Was 1.25rem, now 3x larger */
        font-weight: 700 !important;
        line-height: 1.1 !important;
        color: #201f1e !important;
        letter-spacing: -0.5px !important;
    }
    
    .ms-title-lg {
        font-size: 3rem !important; /* Was 1rem, now 3x larger */
        font-weight: 700 !important;
        line-height: 1.2 !important;
        color: #201f1e !important;
        letter-spacing: -0.25px !important;
    }
    
    .ms-title-md {
        font-size: 2.5rem !important; /* Was 0.875rem, now 3x larger */
        font-weight: 600 !important;
        line-height: 1.3 !important;
        color: #201f1e !important;
    }
    
    .ms-title-sm {
        font-size: 2rem !important; /* Was 0.75rem, now 3x larger */
        font-weight: 600 !important;
        line-height: 1.4 !important;
        color: #201f1e !important;
    }
    
    .ms-body-lg {
        font-size: 2rem !important; /* Was 0.75rem, now 3x larger */
        font-weight: 400 !important;
        line-height: 1.5 !important;
        color: #323130 !important;
    }
    
    .ms-body {
        font-size: 1.75rem !important; /* Was 0.625rem, now 3x larger */
        font-weight: 400 !important;
        line-height: 1.5 !important;
        color: #323130 !important;
    }
    
    .ms-body-sm {
        font-size: 1.5rem !important; /* Was 0.5rem, now 3x larger */
        font-weight: 400 !important;
        line-height: 1.5 !important;
        color: #605e5c !important;
    }
    
    /* =============================================
       BUTTONS - SCALED FOR LARGER TEXT
       ============================================= */
    
    .ms-btn {
        font-family: 'Segoe UI', sans-serif !important;
        font-weight: 600 !important;
        padding: 1rem 2rem !important; /* Larger padding for bigger text */
        border: 2px solid transparent !important;
        border-radius: 6px !important;
        font-size: 1.75rem !important; /* 3x larger */
        line-height: 1.5 !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        cursor: pointer !important;
        height: auto !important;
        min-height: 60px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    
    .ms-btn-primary {
        background-color: #0078d4 !important;
        color: white !important;
        border-color: #0078d4 !important;
    }
    
    .ms-btn-primary:hover {
        background-color: #106ebe !important;
        border-color: #106ebe !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(0, 120, 212, 0.3) !important;
    }
    
    .ms-btn-danger {
        background-color: #d13438 !important;
        color: white !important;
        border-color: #d13438 !important;
    }
    
    .ms-btn-danger:hover {
        background-color: #c12a2e !important;
        border-color: #c12a2e !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(209, 52, 56, 0.3) !important;
    }
    
    .ms-btn-success {
        background-color: #107c10 !important;
        color: white !important;
        border-color: #107c10 !important;
    }
    
    .ms-btn-success:hover {
        background-color: #0e6a0e !important;
        border-color: #0e6a0e !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(16, 124, 16, 0.3) !important;
    }
    
    /* =============================================
       TABLES - SCALED FOR LARGER TEXT
       ============================================= */
    
    .ms-table {
        width: 100% !important;
        border-collapse: collapse !important;
        font-family: 'Segoe UI', sans-serif !important;
    }
    
    .ms-table thead {
        background-color: #faf9f8 !important;
        border-bottom: 3px solid #edebe9 !important;
    }
    
    .ms-table th {
        font-weight: 600 !important;
        font-size: 2rem !important; /* 3x larger */
        color: #323130 !important;
        padding: 1.5rem 1.5rem !important; /* Larger padding */
        text-align: left !important;
        text-transform: uppercase !important;
        letter-spacing: 1px !important;
        vertical-align: middle !important;
    }
    
    .ms-table td {
        padding: 1.25rem 1.5rem !important; /* Larger padding */
        font-size: 1.75rem !important; /* 3x larger */
        color: #323130 !important;
        border-bottom: 2px solid #edebe9 !important;
        vertical-align: middle !important;
    }
    
    .ms-table tr:hover {
        background-color: #f3f2f1 !important;
    }
    
    .ms-table-striped tbody tr:nth-child(odd) {
        background-color: #faf9f8 !important;
    }
    
    /* =============================================
       BADGES - SCALED FOR LARGER TEXT
       ============================================= */
    
    .ms-badge {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0.75rem 1.5rem !important; /* Larger padding */
        font-size: 1.5rem !important; /* 3x larger */
        font-weight: 600 !important;
        line-height: 1.2 !important;
        text-align: center !important;
        white-space: nowrap !important;
        vertical-align: middle !important;
        border-radius: 6px !important;
        min-height: 45px !important;
        min-width: 45px !important;
    }
    
    .ms-badge-success {
        background-color: #dff6dd !important;
        color: #107c10 !important;
        border: 1px solid #b2d8b1 !important;
    }
    
    .ms-badge-warning {
        background-color: #fff4ce !important;
        color: #7a5c00 !important;
        border: 1px solid #e6d8a4 !important;
    }
    
    .ms-badge-info {
        background-color: #c7e0f4 !important;
        color: #005a9e !important;
        border: 1px solid #a3c9e8 !important;
    }
    
    /* =============================================
       ICONS - SCALED FOR LARGER TEXT
       ============================================= */
    
    .ms-icon {
        font-size: 2.5rem !important; /* Larger icons */
        vertical-align: middle !important;
    }
    
    .ms-icon-lg {
        font-size: 3.5rem !important; /* Larger icons */
    }
    
    /* =============================================
       SUMMARY CARDS
       ============================================= */
    
    .ms-summary-card {
        background: white !important;
        border: 1px solid #edebe9 !important;
        border-radius: 8px !important;
        padding: 2.5rem !important;
        transition: all 0.2s ease !important;
    }
    
    .ms-summary-card:hover {
        border-color: #c8c6c4 !important;
        box-shadow: 0 4px 16px rgba(0,0,0,0.1) !important;
    }
    
    .ms-summary-card-primary {
        border-left: 6px solid #0078d4 !important;
    }
    
    .ms-summary-card-success {
        border-left: 6px solid #107c10 !important;
    }
    
    .ms-summary-card-warning {
        border-left: 6px solid #ff8c00 !important;
    }
    
    /* =============================================
       PROGRESS BARS
       ============================================= */
    
    .ms-progress {
        height: 8px !important; /* Thicker for larger design */
        background-color: #edebe9 !important;
        border-radius: 4px !important;
        overflow: hidden !important;
    }
    
    .ms-progress-bar {
        height: 100% !important;
        background-color: #0078d4 !important;
        transition: width 0.3s ease !important;
    }
    
    /* =============================================
       FOCUS STATES
       ============================================= */
    
    .ms-focus:focus {
        outline: 3px solid #0078d4 !important;
        outline-offset: 3px !important;
    }
    
    /* =============================================
       ANIMATIONS
       ============================================= */
    
    .ms-fade-in {
        animation: msFadeIn 0.4s ease-in-out !important;
    }
    
    @keyframes msFadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .ms-slide-in {
        animation: msSlideIn 0.5s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    
    @keyframes msSlideIn {
        from { transform: translateX(-30px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    /* =============================================
       RESPONSIVE DESIGN
       ============================================= */
    
    @media (max-width: 1200px) {
        .ms-title-xl { font-size: 3rem !important; }
        .ms-title-lg { font-size: 2.5rem !important; }
        .ms-title-md { font-size: 2rem !important; }
        .ms-body-lg { font-size: 1.75rem !important; }
        .ms-body { font-size: 1.5rem !important; }
        .ms-body-sm { font-size: 1.25rem !important; }
        .ms-table th { font-size: 1.75rem !important; padding: 1.25rem 1rem !important; }
        .ms-table td { font-size: 1.5rem !important; padding: 1rem !important; }
        .ms-btn { font-size: 1.5rem !important; padding: 0.875rem 1.5rem !important; }
    }
    
    @media (max-width: 768px) {
        .ms-title-xl { font-size: 2.5rem !important; }
        .ms-title-lg { font-size: 2rem !important; }
        .ms-title-md { font-size: 1.75rem !important; }
        .ms-body-lg { font-size: 1.5rem !important; }
        .ms-body { font-size: 1.25rem !important; }
        .ms-body-sm { font-size: 1rem !important; }
        .ms-table th { font-size: 1.5rem !important; padding: 1rem 0.75rem !important; }
        .ms-table td { font-size: 1.25rem !important; padding: 0.875rem 0.75rem !important; }
        .ms-btn { font-size: 1.25rem !important; padding: 0.75rem 1.25rem !important; min-height: 50px !important; }
        .ms-card-body { padding: 1.5rem !important; }
        .ms-card-header { padding: 1.5rem !important; }
    }
    
    @media (max-width: 576px) {
        .ms-title-xl { font-size: 2rem !important; }
        .ms-title-lg { font-size: 1.75rem !important; }
        .ms-title-md { font-size: 1.5rem !important; }
        .ms-body-lg { font-size: 1.25rem !important; }
        .ms-body { font-size: 1.1rem !important; }
        .ms-body-sm { font-size: 1rem !important; }
    }
    
    /* =============================================
       CUSTOM UTILITIES FOR RESUMEN AND DETALLE SECTIONS
       ============================================= */
    
    /* Make Resumen and Detalle sections have same font size */
    .resumen-section *,
    .detalle-section * {
        font-size: inherit !important;
    }
    
    /* Grid View specific overrides for 3x larger text */
    .grid-view table {
        font-size: 1.75rem !important;
    }
    
    .grid-view table th {
        font-size: 2rem !important;
        padding: 1.5rem !important;
    }
    
    .grid-view table td {
        font-size: 1.75rem !important;
        padding: 1.25rem !important;
    }
    
    /* Pagination larger */
    .pagination .page-link {
        font-size: 1.5rem !important;
        padding: 0.75rem 1.25rem !important;
    }
    
    /* Form controls larger */
    .form-control,
    .form-select,
    .select2-container .select2-selection--multiple {
        font-size: 1.75rem !important;
        padding: 1rem 1.25rem !important;
        height: 60px !important;
        min-height: 60px !important;
    }
    
    /* Select2 larger */
    .select2-container--default .select2-selection--multiple {
        min-height: 60px !important;
        padding: 0.5rem !important;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        font-size: 1.5rem !important;
        padding: 0.5rem 1rem !important;
        margin: 0.25rem !important;
    }
    
    /* Ensure all text in report sections is large */
    .report-results * {
        font-size: 1.75rem !important;
    }
    
    .report-results .ms-body {
        font-size: 1.75rem !important;
    }
    
    .report-results .ms-body-sm {
        font-size: 1.5rem !important;
    }
        /* Enhanced Filter Styles */
    .filter-group-ms 
    
    /* Animation for custom dates container */
    #custom-dates-container {
        animation: slideDown 0.5s cubic-bezier(0.4, 0, 0.2, 1);
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
    
    /* Adjusted Progress Bars in Resumen */
    .progress {
        height: 10px !important;
        border-radius: 5px !important;
        overflow: hidden;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .progress-bar {
        border-radius: 5px !important;
        transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }
CSS
);

// JavaScript corregido - con URLs correctas
$this->registerJs(
    <<<JS
    // =============================================
    // CONFIGURACIÓN INICIAL
    // =============================================
    const config = {
        ajaxUrl: '{$ajaxUrl}',
        pdfUrl: '{$pdfUrl}',
        selectors: {
            results: '#report-results',
            status: '#pago-status-selector',
            clinica: '#clinica-filter',
            dateRange: '#date-range-selector',
            dateFrom: '#date-from',
            dateTo: '#date-to',
            customDatesContainer: '#custom-dates-container',
            aplicarBtn: '#btn-aplicar-filtros'
        }
    };
    
    // Debug: Verificar las URLs
    console.log('AJAX URL:', config.ajaxUrl);
    console.log('PDF URL:', config.pdfUrl);
    
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
        
        // Set default dates for range inputs (today and yesterday)
        const today = new Date().toISOString().split('T')[0];
        const yesterday = new Date(Date.now() - 86400000).toISOString().split('T')[0];
        
        \$(config.selectors.dateFrom).val(yesterday);
        \$(config.selectors.dateTo).val(today);
        
        // Marcar botón inicial como activo
        \$('.btn-range[data-range="day"]').addClass('active');
    
    // =============================================
    // FUNCIONES UTILITARIAS
    // =============================================
    function showLoading() {

        \$(config.selectors.results).html(
            '<div class="col-12">' +
                '<div class="card border-0 shadow">' +
                    '<div class="card-body text-center py-5">' +
                        '<div class="loading-pulse">' +
                            '<div class="mb-4">' +
                                '<i class="fas fa-chart-bar fa-5x text-primary"></i>' +
                            '</div>' +
                            '<h3 class="text-primary mb-3">Generando Reporte</h3>' +
                            '<p class="text-muted fs-5">Procesando datos y generando análisis...</p>' +
                            '<div class="mt-5">' +
                                '<div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status">' +
                                    '<span class="visually-hidden">Cargando...</span>' +
                                '</div>' +
                            '</div>' +
                            '<div class="progress mt-5" style="height: 8px;">' +
                                '<div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 75%"></div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>'
        );
    }
    // =============================================
// FUNCIÓN PARA EXPORTAR RESUMEN PDF
// =============================================
function generarResumenPDF() {
    // Obtener parámetros actuales
    const status = $(config.selectors.status).val();
    const clinicasSeleccionadas = $(config.selectors.clinica).val();
    const dateRange = $(config.selectors.dateRange).val();
    const dateFrom = $(config.selectors.dateFrom).val();
    const dateTo = $(config.selectors.dateTo).val();
    
    // Construir URL para resumen PDF
    let pdfParams = {
        range: dateRange,
        status: status,
        clinicas: clinicasSeleccionadas ? clinicasSeleccionadas.join(',') : 'todas',
        resumen_only: 'true' // Parámetro especial para solo el resumen
    };
    
    // Agregar fechas personalizadas si aplica
    if (dateRange === 'custom' && dateFrom && dateTo) {
        pdfParams.date_from = dateFrom;
        pdfParams.date_to = dateTo;
        pdfParams.custom_range = 'true';
    }
    
    const pdfUrl = config.pdfUrl + '?' + new URLSearchParams(pdfParams).toString();
    
    // Abrir en nueva pestaña
    window.open(pdfUrl, '_blank');
}

// =============================================
// MANEJADOR PARA BOTÓN RESUMEN PDF
// =============================================
$(document).on('click', '#btn-resumen-pdf', function() {
    generarResumenPDF();
});

    function showError(message) {
        \$(config.selectors.results).html(
            '<div class="col-12">' +
                '<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">' +
                    '<div class="d-flex align-items-center">' +
                        '<i class="fas fa-exclamation-triangle fa-2x me-3"></i>' +
                        '<div>' +
                            '<h5 class="alert-heading mb-1 fw-bold">Error en la Generación</h5>' +
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
>>>>>>> 99fe86e83a048865e60b4e1dced25854568a58b2
        }
        
        if (dateFrom > dateTo) {
            return { valid: false, message: 'La fecha inicial no puede ser mayor a la fecha final.' };
        }
        
        return { valid: true };
    }
    
    // =============================================
    // FUNCIÓN PRINCIPAL - CARGAR REPORTE (VERSIÓN SIMPLIFICADA)
    // =============================================
    async function cargarReporte(params = {}) {
        try {
            console.log('Iniciando carga de reporte con params:', params);
            showLoading();
        
            const csrfToken = \$('meta[name="csrf-token"]').attr('content') || '';
        
            const requestData = {
                ...params,
                status: params.status || \$(config.selectors.status).val(),
                clinicas: params.clinicas || \$(config.selectors.clinica).val() || [],
                _csrf: csrfToken
            };
        
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
                
                // Intentar actualizar el botón PDF si existe
                const pdfBtn = \$('#btn-print-pdf');
                if (pdfBtn.length) {
                    const pdfParams = {
                        range: params.range || 'day',
                        status: params.status,
                        clinicas: params.clinicas ? params.clinicas.join(',') : 'todas',
                        ...(params.custom_range && { custom_range: 'true' }),
                        ...(params.date_from && { date_from: params.date_from }),
                        ...(params.date_to && { date_to: params.date_to })
                    };
                    
                    const pdfUrl = config.pdfUrl + '?' + new URLSearchParams(pdfParams).toString();
                    pdfBtn.attr('href', pdfUrl);
                }
                
                // Scroll suave a resultados
                \$('html, body').animate({
                    scrollTop: \$(config.selectors.results).offset().top - 100
                }, 500);
                
            } else {
                console.error('Error en respuesta:', response.message);
                showError(response.message || 'Error al procesar el reporte.');
            }
            
        } catch (error) {
            console.error('Error en carga de reporte:', error);
            console.error('Error details:', error.responseText);
            
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
        let startDate = today;
        
        switch (range) {
            case 'day':
                startDate = new Date(today);
                break;
            case 'week':
                startDate = new Date(today);
                startDate.setDate(today.getDate() - 7);
                break;
            case 'month':
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                break;
            case 'last-month':
                startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                const endDate = new Date(today.getFullYear(), today.getMonth(), 0);
                \$(config.selectors.dateFrom).val(formatDate(startDate));
                \$(config.selectors.dateTo).val(formatDate(endDate));
                return;
            case 'custom':
                \$(config.selectors.customDatesContainer)
                    .slideDown(200);
                return;
        }
        
        \$(config.selectors.dateFrom).val(formatDate(startDate));
        \$(config.selectors.dateTo).val(formatDate(today));
    }
    
    function formatDate(date) {
        return date.toISOString().split('T')[0];
    }
    
    function exportToExcel() {
        const status = \$(config.selectors.status).val();
        const clinicasSeleccionadas = \$(config.selectors.clinica).val();
        const dateRange = \$(config.selectors.dateRange).val();
        const dateFrom = \$(config.selectors.dateFrom).val();
        const dateTo = \$(config.selectors.dateTo).val();
        
        let exportUrl = config.ajaxUrl.replace('get-pagos-detail', 'export-excel') + '?';
        exportUrl += 'range=' + encodeURIComponent(dateRange);
        exportUrl += '&status=' + encodeURIComponent(status);
        exportUrl += '&clinicas=' + (clinicasSeleccionadas ? clinicasSeleccionadas.join(',') : 'todas');
        
        if (dateRange === 'custom' && dateFrom && dateTo) {
            exportUrl += '&date_from=' + encodeURIComponent(dateFrom);
            exportUrl += '&date_to=' + encodeURIComponent(dateTo);
            exportUrl += '&custom_range=true';
        }
        
        console.log('Export URL:', exportUrl);
        window.open(exportUrl, '_blank');
    }
    
    // =============================================
    // FUNCIÓN PRINCIPAL PARA GENERAR REPORTE
    // =============================================
    function generateReport() {
        const status = \$(config.selectors.status).val();
        const clinicas = \$(config.selectors.clinica).val() || [];
        const dateRange = \$(config.selectors.dateRange).val();
        
        let params = {
            status: status,
            clinicas: clinicas
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
        
        console.log('Generando reporte con parámetros:', params);
        cargarReporte(params);
    }
    
    // =============================================
    // MANEJADORES DE EVENTOS
    // =============================================
    function setupEventHandlers() {
        // Cambio en selector de rango de fechas
        \$(config.selectors.dateRange).on('change', function() {
            const range = \$(this).val();
            
            if (range !== 'custom') {
                \$(config.selectors.customDatesContainer).slideUp(200);
                updateDateInputsByRange(range);
            } else {
                updateDateInputsByRange(range);
            }
        });
        
        // Botón principal de generar reporte
        \$(config.selectors.aplicarBtn).on('click', function() {
            generateReport();
        });
        
        // Permitir Enter en campos de fecha
        \$(config.selectors.dateFrom).add(config.selectors.dateTo).on('keypress', function(e) {
            if (e.which === 13) generateReport();
        });
    }
    
    function mostrarAnalisisGrafico() {
    // Obtener parámetros actuales
    const params = {
        range: $(config.selectors.dateRange).val(),
        date_from: $(config.selectors.dateFrom).val(),
        date_to: $(config.selectors.dateTo).val(),
        custom_range: $(config.selectors.dateRange).val() === 'custom',
        status: $(config.selectors.status).val(),
        clinicas: $(config.selectors.clinica).val() || [],
        _csrf: $('meta[name="csrf-token"]').attr('content')
    };
    
    // Mostrar loading
    $(config.selectors.results).html(`
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-body text-center py-5">
                    <div class="loading-pulse">
                        <div class="mb-4">
                            <i class="fas fa-chart-bar fa-5x text-primary"></i>
                        </div>
                        <h3 class="text-primary mb-3">Cargando Análisis Gráfico</h3>
                        <p class="text-muted fs-5">Preparando visualizaciones y estadísticas...</p>
                        <div class="mt-5">
                            <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `);
    
    // Cargar vista de análisis
    $.ajax({
        url: '/reportes/get-analytics-view',
        type: 'POST',
        data: params,
        success: function(response) {
            $(config.selectors.results).html(response);
            
            // Load chart data after view is rendered
            setTimeout(() => {
                if (typeof loadChartData === 'function') {
                    loadChartData();
                }
            }, 100);
            
            // Scroll suave a la sección de análisis
            $('html, body').animate({
                scrollTop: $('#analytics-section').offset().top - 100
            }, 500);
        },
        error: function(xhr, status, error) {
            console.error('Error loading analytics:', error);
            showError('Error al cargar el análisis gráfico: ' + error);
        }
    });
}

// Add this event listener for the analytics button
$(document).on('click', '.btn-analytics', function(e) {
    e.preventDefault();
    mostrarAnalisisGrafico();
});

    // =============================================
    // INICIALIZACIÓN DE LA PÁGINA
    // =============================================
    \$(document).ready(function() {
        console.log('Reportes cargado correctamente');
        console.log('AJAX URL configurada:', config.ajaxUrl);
        
        initComponents();
        setupEventHandlers();
        updateDateInputsByRange('day');
    });
JS
);
