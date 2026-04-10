<?php
// app/views/reporte-atenciones/index.php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\RmClinica;
use app\components\UserHelper;

/** @var yii\web\View $this */
/** @var app\models\SisSiniestroReporteSearch $searchModel */
/** @var array $clinicas */

$this->title = 'Reporte de Atenciones Médicas por Clínica';
$this->params['breadcrumbs'][] = $this->title;

// Check user role for clinic access
$hasClinicAccess = UserHelper::hasClinicAccess();
$isSuperAdmin = Yii::$app->user->can('superadmin') || Yii::$app->user->can('admin');

// Get clinics based on user access
if ($hasClinicAccess) {
    // Users with clinic roles only see their assigned clinics
    $clinicas = UserHelper::getAccessibleClinicas();
    $userClinicaIds = \yii\helpers\ArrayHelper::getColumn($clinicas, 'id');
    $hasMultipleClinicas = count($clinicas) > 1;
} else {
    // Superadmin and admin see all active clinics
    $clinicas = RmClinica::find()
        ->where(['estatus' => 'Activo'])
        ->andWhere(['IS', 'deleted_at', null])
        ->orderBy('nombre')
        ->all();
    $userClinicaIds = \yii\helpers\ArrayHelper::getColumn($clinicas, 'id');
    $hasMultipleClinicas = true;
}

// URLs
$ajaxUrl = Url::to(['generate-report']);
$excelUrl = Url::to(['export-excel']);
$pdfUrl = Url::to(['export-pdf']);
$clinicDetailUrl = Url::to(['clinic-detail']);
?>

<div class="reporte-atenciones-index">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="text-center">
                <h1 class="display-5 fw-bold text-primary mb-2">
                    <i class="fas fa-hospital me-2"></i> <?= Html::encode($this->title) ?>
                    <?php if ($hasClinicAccess): ?>
                        <span class="badge bg-info ms-2" style="font-size: 0.9rem;">
                            <i class="fas fa-lock me-1"></i> Acceso Restringido
                        </span>
                    <?php endif; ?>
                </h1>
                <p class="lead text-muted">
                    Análisis de atenciones médicas por centro de salud
                    <?php if ($hasClinicAccess && count($clinicas) === 1): ?>
                        <br><small class="text-primary"><i class="fas fa-building me-1"></i>Datos limitados a: <strong><?= Html::encode($clinicas[0]->nombre) ?></strong></small>
                    <?php elseif ($hasClinicAccess && count($clinicas) > 1): ?>
                        <br><small class="text-primary"><i class="fas fa-building me-1"></i>Datos limitados a sus <?= count($clinicas) ?> clínicas asignadas</small>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-lg mb-5"
        style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-left: 4px solid #0078d4;">
        <div class="card-body p-4">
            <!-- Instructions -->
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
                                    Configure los parámetros para analizar las atenciones médicas por clínica
                                    <?php if ($hasClinicAccess): ?>
                                        <br><small class="text-primary"><i class="fas fa-info-circle me-1"></i>Los datos están limitados a sus clínicas asignadas</small>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILTERS -->
            <div class="row g-3 align-items-end">
                <!-- Rango de Fechas -->
                <div class="col-xl-3 col-lg-4 col-md-6">
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

                        <?php if ($hasClinicAccess && count($clinicas) === 1): ?>
                            <!-- Single clinic - display as read-only -->
                            <div class="form-control border-2 border-success shadow-sm py-2"
                                style="font-size: 1.4rem !important; min-height: 55px; border-radius: 8px; background-color: #f8f9fa; display: flex; align-items: center;">
                                <i class="fas fa-hospital me-2 text-success"></i>
                                <?= Html::encode($clinicas[0]->nombre) ?>
                                <input type="hidden" id="clinica-filter" value="<?= $clinicas[0]->id ?>">
                            </div>
                            <div class="mt-1">
                                <small class="text-muted" style="font-size: 1.2rem !important;">
                                    <i class="fas fa-lock me-2 ms-success"></i>Acceso restringido a su clínica asignada
                                </small>
                            </div>
                        <?php else: ?>
                            <select id="clinica-filter" class="form-select select2-multiple border-2 border-success shadow-sm"
                                style="font-size: 1.4rem !important; min-height: 55px; border-radius: 8px;"
                                <?= $hasClinicAccess ? 'disabled' : '' ?>
                                <?= $hasClinicAccess ? '' : 'multiple="multiple"' ?>>
                                <?php if (!$hasClinicAccess): ?>
                                    <option value="todas" selected class="py-2">
                                        <span style="font-size: 1.4rem !important;">🏥 Todas las Clínicas</span>
                                    </option>
                                <?php endif; ?>
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
                                    <i class="fas fa-info-circle me-2 ms-success"></i>
                                    <?php if ($hasClinicAccess): ?>
                                        Datos limitados a sus <?= count($clinicas) ?> clínicas asignadas
                                    <?php else: ?>
                                        Seleccione una o múltiples clínicas
                                    <?php endif; ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Fechas Personalizadas -->
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
                                                        <i class="fas fa-calendar-check me-2"></i>Fecha de inicio
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
                                                        <i class="fas fa-calendar-times me-2"></i>Fecha de fin
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

            <!-- Botón Generar Reporte -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="text-center border-top pt-4">
                        <div class="d-inline-block p-3 rounded-3 shadow-lg"
                            style="background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);">
                            <?= Html::button('<i class="fas fa-chart-bar me-3"></i> Generar Reporte', [
                                'id' => 'btn-generar-reporte',
                                'class' => 'btn btn-light btn-xl px-4 py-3 fw-bold shadow-lg',
                                'style' => 'font-size: 1.6rem !important; border-radius: 10px;'
                            ]) ?>
                            <div class="mt-2">
                                <small class="text-white" style="font-size: 1.2rem !important;">
                                    <i class="fas fa-bolt me-2"></i>Presione para generar el reporte con los filtros seleccionados
                                    <?php if ($hasClinicAccess): ?>
                                        <br><i class="fas fa-lock me-1"></i>Los datos se limitarán automáticamente a sus clínicas asignadas
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de Resultados -->
    <div class="row" id="report-results">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="fas fa-chart-line fa-5x text-muted mb-4 opacity-25"></i>
                    </div>
                    <h3 class="text-dark mb-3 display-6">Reporte de Atenciones por Clínica</h3>
                    <p class="text-muted mb-4 fs-4">
                        Configure los filtros arriba y presione <span class="badge bg-primary px-4 py-3 fs-5">Generar Reporte</span><br>
                        para visualizar el análisis de atenciones médicas.
                        <?php if ($hasClinicAccess): ?>
                            <br><span class="text-info"><i class="fas fa-chart-simple me-1"></i>Los datos se limitarán automáticamente a sus clínicas asignadas</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Pass user clinic IDs to JavaScript
$userClinicaIdsJs = $hasClinicAccess ? json_encode($userClinicaIds) : '[]';

$this->registerJs(
    <<<JS
$(document).ready(function() {
    // Configuration with clinic access
    const config = {
        ajaxUrl: '{$ajaxUrl}',
        excelUrl: '{$excelUrl}',
        pdfUrl: '{$pdfUrl}',
        clinicDetailUrl: '{$clinicDetailUrl}',
        hasClinicAccess: '{$hasClinicAccess}' === '1',
        userClinicaIds: {$userClinicaIdsJs}
    };
    
    console.log('Reporte de Atenciones cargado con acceso restringido:', config.hasClinicAccess);
    console.log('Clínicas del usuario:', config.userClinicaIds);
    
    // Function to get selected clinics (respects access restrictions)
    function getSelectedClinicas() {
        if (config.hasClinicAccess) {
            // For clinic users, enforce assigned clinic IDs only
            if (Array.isArray(config.userClinicaIds) && config.userClinicaIds.length > 0) {
                return config.userClinicaIds;
            }
            return [];
        }
        
        // For superadmin/admin, get from selector
        const clinicas = $('#clinica-filter').val();
        if (!clinicas || clinicas.length === 0 || clinicas.includes('todas')) {
            return [];
        }
        return clinicas;
    }

    // Initialize Select2 only if not restricted or multiple clinics
    if ($.fn.select2 && $('#clinica-filter').length && !config.hasClinicAccess) {
        $('#clinica-filter').select2({
            placeholder: "Seleccione clínicas...",
            width: '100%',
            allowClear: true,
            theme: 'bootstrap-5',
            closeOnSelect: false
        });
    }

    // Function to format date as YYYY-MM-DD
    function formatDate(date) {
        const d = new Date(date);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return year + '-' + month + '-' + day;
    }

    // Function to calculate date range based on selection
    function calculateDateRange(range) {
        const today = new Date();
        let startDate = new Date(today);
        let endDate = new Date(today);

        switch (range) {
            case 'day':
                // Today - already set above
                break;
                
            case 'week':
                // Last 7 days (including today = 7 days total)
                startDate.setDate(today.getDate() - 6);
                break;
                
            case 'month':
                // Current month
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                break;
                
            case 'last-month':
                // Previous month
                const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                startDate = new Date(lastMonth.getFullYear(), lastMonth.getMonth(), 1);
                endDate = new Date(lastMonth.getFullYear(), lastMonth.getMonth() + 1, 0);
                break;
                
            case 'custom':
                // Use custom dates from inputs
                const dateFromVal = $('#date-from').val();
                const dateToVal = $('#date-to').val();
                
                if (dateFromVal) {
                    startDate = new Date(dateFromVal);
                }
                if (dateToVal) {
                    endDate = new Date(dateToVal);
                }
                break;
        }

        return {
            from: formatDate(startDate),
            to: formatDate(endDate)
        };
    }

    // Update date inputs when range changes
    function updateDateInputs(range) {
        const dates = calculateDateRange(range);
        $('#date-from').val(dates.from);
        $('#date-to').val(dates.to);
        console.log('Updated dates for range', range, ':', dates);
    }

    // Date range selector change handler
    $('#date-range-selector').on('change', function() {
        const range = $(this).val();
        
        if (range !== 'custom') {
            $('#custom-dates-container').slideUp(200);
            updateDateInputs(range);
        } else {
            $('#custom-dates-container').slideDown(200);
        }
    });

    // Update date inputs when custom range is selected
    $('#date-from, #date-to').on('change', function() {
        if ($('#date-range-selector').val() !== 'custom') {
            $('#date-range-selector').val('custom');
            $('#custom-dates-container').slideDown(200);
        }
    });

    // Generate report button
    $('#btn-generar-reporte').on('click', function() {
        generateReport();
    });

    // Enter key in date fields should also generate report
    $('#date-from, #date-to').on('keypress', function(e) {
        if (e.which === 13) {
            generateReport();
        }
    });

    // Generate report function
    function generateReport() {
        const clinicas = getSelectedClinicas();
        const dateRange = $('#date-range-selector').val();
        
        // Calculate date range
        const calculatedRange = calculateDateRange(dateRange);
        
        // Build parameters
        const params = {
            range: dateRange,
            clinicas: clinicas,
            date_from: calculatedRange.from,
            date_to: calculatedRange.to
        };
        
        console.log('Sending AJAX request with params:', params);
        
        showLoading();
        
        $.ajax({
            url: config.ajaxUrl,
            type: 'POST',
            data: params,
            dataType: 'json',
            success: function(response) {
                console.log('AJAX Response:', response);
                if (response.success) {
                    $('#report-results').html(response.html);
                    $('html, body').animate({
                        scrollTop: $('#report-results').offset().top - 100
                    }, 500);
                } else {
                    showError(response.message || 'Error desconocido');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                showError('Error del servidor: ' + (xhr.responseText || error));
            }
        });
    }

    // Show loading
    function showLoading() {
        $('#report-results').html(
            '<div class="col-12">' +
            '    <div class="card border-0 shadow">' +
            '        <div class="card-body text-center py-5">' +
            '            <div class="mb-4">' +
            '                <i class="fas fa-chart-bar fa-5x text-primary"></i>' +
            '            </div>' +
            '            <h3 class="text-primary mb-3">Generando Reporte</h3>' +
            '            <p class="text-muted fs-5">Procesando datos y generando análisis...</p>' +
            '            <div class="mt-5">' +
            '                <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status">' +
            '                    <span class="visually-hidden">Cargando...</span>' +
            '                </div>' +
            '            </div>' +
            '        </div>' +
            '    </div>' +
            '</div>'
        );
    }

    // Show error
    function showError(message) {
        $('#report-results').html(
            '<div class="col-12">' +
            '    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">' +
            '        <div class="d-flex align-items-center">' +
            '            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>' +
            '            <div>' +
            '                <h5 class="alert-heading mb-1 fw-bold">Error en la Generación</h5>' +
            '                <p class="mb-0 fs-5">' + message + '</p>' +
            '            </div>' +
            '        </div>' +
            '        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '    </div>' +
            '</div>'
        );
    }

    // Export handlers
    $(document).on('click', '#btn-export-excel', function(e) {
        e.preventDefault();
        exportReport('excel');
    });

    $(document).on('click', '#btn-export-pdf', function(e) {
        e.preventDefault();
        exportReport('pdf');
    });

    function exportReport(type) {
        const clinicas = getSelectedClinicas();
        const dateRange = $('#date-range-selector').val();
        const calculatedRange = calculateDateRange(dateRange);
        
        const url = type === 'excel' ? config.excelUrl : config.pdfUrl;
        
        let exportUrl = url + '?range=' + encodeURIComponent(dateRange) + 
                       '&clinicas=' + (clinicas.length ? clinicas.join(',') : 'todas') +
                       '&date_from=' + encodeURIComponent(calculatedRange.from) +
                       '&date_to=' + encodeURIComponent(calculatedRange.to);
        
        console.log('Export URL:', exportUrl);
        window.open(exportUrl, '_blank');
    }

    // View clinic detail - with clinic access support
    $(document).on('click', '.btn-view-detail', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const clinicId = $(this).data('id');
        const dateRange = $('#date-range-selector').val();
        const calculatedRange = calculateDateRange(dateRange);
        const clinicas = getSelectedClinicas();
        
        console.log('Loading clinic detail for ID:', clinicId);
        
        // Show loading in modal
        $('#clinic-detail-modal .modal-body').html(
            '<div class="text-center py-5">' +
            '    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">' +
            '        <span class="visually-hidden">Cargando...</span>' +
            '    </div>' +
            '    <p class="mt-3">Cargando detalles de la clínica...</p>' +
            '</div>'
        );
        
        // Show modal
        var modalElement = $('#clinic-detail-modal');
        modalElement.modal({
            backdrop: 'static',
            keyboard: false
        });
        modalElement.modal('show');
        
        // AJAX request for clinic detail
        $.ajax({
            url: config.clinicDetailUrl,
            type: 'GET',
            data: {
                id: clinicId,
                range: dateRange,
                date_from: calculatedRange.from,
                date_to: calculatedRange.to,
                clinicas: clinicas.length ? clinicas.join(',') : 'todas'
            },
            dataType: 'json',
            success: function(response) {
                console.log('Clinic detail response:', response);
                
                if (response.success && response.html) {
                    $('#clinic-detail-modal .modal-body').html(response.html);
                } else {
                    $('#clinic-detail-modal .modal-body').html(
                        '<div class="alert alert-danger">' +
                        '    <i class="fas fa-exclamation-triangle me-2"></i>' +
                        '    ' + (response.message || 'Error al cargar detalles') +
                        '</div>'
                    );
                }
            },
            error: function(xhr, status, error) {
                console.error('Clinic detail error:', error);
                $('#clinic-detail-modal .modal-body').html(
                    '<div class="alert alert-danger">' +
                    '    <i class="fas fa-exclamation-triangle me-2"></i>' +
                    '    Error del servidor: ' + error +
                    '</div>'
                );
            }
        });
    });

    // Initialize
    updateDateInputs('day');
    console.log('System initialized');
    console.log('Clinic access restricted:', config.hasClinicAccess);
});
JS
);
?>

<!-- Clinic Detail Modal - WIDE VERSION -->
<div class="modal fade" id="clinic-detail-modal" tabindex="-1" role="dialog" aria-labelledby="clinicDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable wide-modal-dialog" role="document">
        <div class="modal-content modal-content-fixed">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="clinicDetailModalLabel">
                    <i class="fas fa-hospital me-2"></i>Detalles de la Clínica
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Content will be loaded dynamically -->
                <div class="text-center py-5">
                    <i class="fas fa-clinic-medical fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Seleccione "Ver Detalles" en una clínica para ver información detallada.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* WIDE MODAL STYLES */
    .wide-modal-dialog {
        max-width: 1400px !important;
        width: 95% !important;
        margin: 20px auto !important;
        max-height: calc(100vh - 40px);
        display: flex;
        align-items: flex-start;
    }

    .wide-modal-dialog .modal-content {
        max-height: calc(100vh - 40px);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        width: 100%;
    }

    .wide-modal-dialog .modal-body {
        overflow-y: auto;
        flex: 1;
        min-height: 200px;
    }

    /* Responsive width adjustments */
    @media (min-width: 1920px) {
        .wide-modal-dialog {
            max-width: 1600px !important;
            width: 90% !important;
        }
    }

    @media (max-width: 1600px) {
        .wide-modal-dialog {
            max-width: 1300px !important;
            width: 95% !important;
        }
    }

    @media (max-width: 1400px) {
        .wide-modal-dialog {
            max-width: 1200px !important;
            width: 98% !important;
        }
    }

    @media (max-width: 1200px) {
        .wide-modal-dialog {
            max-width: 1100px !important;
            width: 98% !important;
        }
    }

    @media (max-width: 992px) {
        .wide-modal-dialog {
            max-width: 95% !important;
            width: 95% !important;
            margin: 10px auto !important;
        }
    }

    @media (max-width: 768px) {
        .wide-modal-dialog {
            max-width: 98% !important;
            width: 98% !important;
            margin: 5px auto !important;
        }

        .wide-modal-dialog .modal-content {
            max-height: calc(100vh - 10px) !important;
        }
    }

    @media (max-width: 576px) {
        .wide-modal-dialog {
            max-width: 100% !important;
            width: 100% !important;
            margin: 0 !important;
            max-height: 100vh;
        }

        .wide-modal-dialog .modal-content {
            max-height: 100vh !important;
            border-radius: 0 !important;
        }
    }

    /* Ensure modal is visible when shown */
    #clinic-detail-modal.modal.show .modal-dialog {
        transform: none !important;
    }

    /* Filter group styling */
    .filter-group-ms {
        transition: all 0.2s ease;
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
    .ms-primary {
        color: #0078d4 !important;
    }

    .ms-success {
        color: #107c10 !important;
    }

    .ms-warning {
        color: #ff8c00 !important;
    }

    .ms-danger {
        color: #d13438 !important;
    }

    /* Badge styling */
    .badge.bg-info {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
    }
</style>