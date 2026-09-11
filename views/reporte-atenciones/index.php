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
    $clinicas = UserHelper::getAccessibleClinicas();
    $userClinicaIds = \yii\helpers\ArrayHelper::getColumn($clinicas, 'id');
    $hasMultipleClinicas = count($clinicas) > 1;
} else {
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
                            <option value="day" class="py-2">📅 Hoy</option>
                            <option value="week" class="py-2">📆 Última Semana</option>
                            <option value="month" class="py-2">🗓️ Mes Actual</option>
                            <option value="last-month" class="py-2">📊 Mes Anterior</option>
                            <option value="custom" class="py-2" selected>🎯 Rango Personalizado</option>
                        </select>
                        <div class="mt-1">
                            <small class="text-muted" style="font-size: 1.2rem !important;">
                                <i class="fas fa-info-circle me-2 ms-warning"></i>Defina el período del reporte
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Type Filter -->
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="filter-group-ms border-0 shadow-lg p-3 rounded-3"
                        style="background: white; border-left: 3px solid #0078d4;">
                        <label class="form-label fw-bold mb-2 d-flex align-items-center"
                            style="font-size: 1.4rem !important; color: #2c3e50;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm"
                                style="width: 40px; height: 40px; background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);">
                                <i class="fas fa-filter text-white" style="font-size: 1.4rem;"></i>
                            </div>
                            Tipo de Atención
                        </label>
                        <select id="type-filter" class="form-select border-2 border-primary shadow-sm py-2"
                            style="font-size: 1.4rem !important; height: 55px; border-radius: 8px;">
                            <option value="all" class="py-2" selected>📋 Todas las Atenciones</option>
                            <option value="citas" class="py-2">📅 Citas Médicas</option>
                            <option value="siniestros" class="py-2">🚑 Emergencias / Siniestros</option>
                        </select>
                        <div class="mt-1">
                            <small class="text-muted" style="font-size: 1.2rem !important;">
                                <i class="fas fa-info-circle me-2 ms-primary"></i>
                                Seleccione el tipo de atenciones a visualizar
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
                                    <option value="todas" selected class="py-2">🏥 Todas las Clínicas</option>
                                <?php endif; ?>
                                <?php foreach ($clinicas as $clinica): ?>
                                    <option value="<?= $clinica->id ?>" class="py-2">
                                        <i class="fas fa-clinic-medical me-2"></i><?= Html::encode($clinica->nombre) ?>
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
                <div class="col-12" id="custom-dates-container">
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
                                                        style="font-size: 1.4rem !important; height: auto;" value="2026-04-28">
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
                                                        style="font-size: 1.4rem !important; height: auto;" value="2026-08-21">
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
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de Resultados -->
    <div id="report-results" class="mt-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center p-5">
                <div class="mb-4">
                    <i class="fas fa-chart-line fa-5x text-muted mb-4 opacity-25"></i>
                </div>
                <h3 class="text-dark mb-3 display-6">Reporte de Atenciones por Clínica</h3>
                <p class="text-muted mb-4 fs-4">
                    Configure los filtros arriba y presione <span class="badge bg-primary px-4 py-3 fs-5">Generar Reporte</span><br>
                    para visualizar el análisis de atenciones médicas.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- SLIDE-IN PANEL / OFF-CANVAS DRAWER - Microsoft Style -->
<!-- ============================================================ -->
<div class="clinic-detail-overlay" id="clinicDetailOverlay"></div>
<div class="clinic-detail-panel" id="clinicDetailPanel">
    <div class="panel-header">
        <div class="panel-header-content">
            <div class="panel-title">
                <i class="fas fa-hospital me-2"></i>
                <span id="panelClinicName">Detalles de la Clínica</span>
            </div>
            <button type="button" class="panel-close-btn" id="panelCloseBtn" aria-label="Cerrar">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <div class="panel-body" id="panelBody">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
            <p class="mt-3 text-muted">Cargando detalles de la clínica...</p>
        </div>
    </div>
</div>

<?php
$userClinicaIdsJs = $hasClinicAccess ? json_encode($userClinicaIds) : '[]';

$this->registerJs(
    <<<JS
$(document).ready(function() {
    var config = {
        ajaxUrl: '{$ajaxUrl}',
        excelUrl: '{$excelUrl}',
        pdfUrl: '{$pdfUrl}',
        clinicDetailUrl: '{$clinicDetailUrl}',
        hasClinicAccess: '{$hasClinicAccess}' === '1',
        userClinicaIds: {$userClinicaIdsJs}
    };
    
    // ============================================================
    // TOOLTIPS — Inicialización idempotente (anti-parpadeo)
    // ============================================================
    // Helper: inicializa tooltips SOLO en elementos que no los tengan ya.
    // Esto evita el parpadeo causado por reinicializaciones repetidas.
    function initTooltips(root) {
        var \$scope = root ? $(root) : $(document);
        \$scope.find('[data-toggle="tooltip"]').each(function () {
            var \$el = $(this);
            // Si ya tiene una instancia de tooltip, no la recreamos
            if (\$el.data('bs.tooltip')) {
                return;
            }
            \$el.tooltip({
                trigger: 'hover focus',
                delay: { show: 400, hide: 200 },
                container: 'body',      // evita que el tooltip se recorte por overflow
                html: false,
                boundary: 'window'
            });
        });
    }

    // Escuchar eventos personalizados para re-inicializar tooltips
    // SOLO cuando insertamos HTML nuevo (no en cada AJAX global)
    $(document).on('report:rendered', function () {
        initTooltips('#report-results');
    });

    $(document).on('panel:rendered', function () {
        initTooltips('#panelBody');
    });
    
    function getSelectedClinicas() {
        if (config.hasClinicAccess) {
            if (Array.isArray(config.userClinicaIds) && config.userClinicaIds.length > 0) {
                return config.userClinicaIds;
            }
            return [];
        }
        var clinicas = $('#clinica-filter').val();
        if (!clinicas || clinicas.length === 0 || clinicas.includes('todas')) {
            return [];
        }
        return clinicas;
    }

    function formatDate(date) {
        if (!date) return '';
        if (typeof date === 'string' && date.match(/^\d{4}-\d{2}-\d{2}$/)) {
            return date;
        }
        var d = new Date(date);
        if (isNaN(d.getTime())) {
            return '';
        }
        var year = d.getUTCFullYear();
        var month = String(d.getUTCMonth() + 1).padStart(2, '0');
        var day = String(d.getUTCDate()).padStart(2, '0');
        return year + '-' + month + '-' + day;
    }

    function calculateDateRange(range) {
        var today = new Date();
        var startDate = new Date(today);
        var endDate = new Date(today);

        switch (range) {
            case 'day':
                break;
            case 'week':
                startDate.setDate(today.getDate() - 6);
                break;
            case 'month':
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                break;
            case 'last-month':
                var lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                startDate = new Date(lastMonth.getFullYear(), lastMonth.getMonth(), 1);
                endDate = new Date(lastMonth.getFullYear(), lastMonth.getMonth() + 1, 0);
                break;
            case 'custom':
                var dateFromVal = $('#date-from').val();
                var dateToVal = $('#date-to').val();
                if (dateFromVal && dateToVal) {
                    return { from: dateFromVal, to: dateToVal };
                }
                return { from: formatDate(today), to: formatDate(today) };
        }
        return { from: formatDate(startDate), to: formatDate(endDate) };
    }

    function updateDateInputs(range) {
        var dates = calculateDateRange(range);
        $('#date-from').val(dates.from);
        $('#date-to').val(dates.to);
    }

    $('#date-range-selector').on('change', function() {
        var range = $(this).val();
        if (range !== 'custom') {
            $('#custom-dates-container').slideUp(200);
            updateDateInputs(range);
        } else {
            $('#custom-dates-container').slideDown(200);
        }
    });

    function generateReport() {
        var clinicas = getSelectedClinicas();
        var dateRange = $('#date-range-selector').val();
        var type = $('#type-filter').val();
        var calculatedRange = calculateDateRange(dateRange);
        
        var params = {
            range: dateRange,
            clinicas: clinicas,
            date_from: calculatedRange.from,
            date_to: calculatedRange.to,
            type: type
        };
        
        showLoading();
        
        $.ajax({
            url: config.ajaxUrl,
            type: 'POST',
            data: params,
            dataType: 'json',
            timeout: 30000,
            success: function(response) {
                if (response.success) {
                    $('#report-results').html(response.html);
                    $(document).trigger('report:rendered');   // ← Inicializa tooltips del nuevo contenido
                    $('html, body').animate({
                        scrollTop: $('#report-results').offset().top - 100
                    }, 500);
                } else {
                    showError(response.message || 'Error desconocido');
                }
            },
            error: function(xhr, status, error) {
                var errorMsg = 'Error del servidor: ';
                try {
                    var response = JSON.parse(xhr.responseText);
                    errorMsg += response.message || error;
                } catch (e) {
                    errorMsg += error;
                }
                showError(errorMsg);
            }
        });
    }

    $('#btn-generar-reporte').on('click', function() {
        generateReport();
    });

    function showLoading() {
        $('#report-results').html(
            '<div class="card border-0 shadow">' +
            '    <div class="card-body text-center py-5">' +
            '        <div class="mb-4"><i class="fas fa-chart-bar fa-5x text-primary"></i></div>' +
            '        <h3 class="text-primary mb-3">Generando Reporte</h3>' +
            '        <p class="text-muted fs-5">Procesando datos y generando análisis...</p>' +
            '        <div class="mt-5"><div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status"><span class="sr-only">Cargando...</span></div></div>' +
            '    </div>' +
            '</div>'
        );
    }

    function showError(message) {
        $('#report-results').html(
            '<div class="col-12">' +
            '    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">' +
            '        <div class="d-flex align-items-center">' +
            '            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>' +
            '            <div><h5 class="alert-heading mb-1 fw-bold">Error en la Generación</h5><p class="mb-0 fs-5">' + message + '</p></div>' +
            '        </div>' +
            '        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
            '    </div>' +
            '</div>'
        );
    }

    $(document).on('click', '#btn-export-excel', function(e) {
        e.preventDefault();
        exportReport('excel');
    });

    $(document).on('click', '#btn-export-pdf', function(e) {
        e.preventDefault();
        exportReport('pdf');
    });

    function exportReport(type) {
        var clinicas = getSelectedClinicas();
        var dateRange = $('#date-range-selector').val();
        var calculatedRange = calculateDateRange(dateRange);
        var filterType = $('#type-filter').val();
        var url = type === 'excel' ? config.excelUrl : config.pdfUrl;
        var exportUrl = url + '?range=' + encodeURIComponent(dateRange) + 
                       '&clinicas=' + (clinicas.length ? clinicas.join(',') : 'todas') +
                       '&date_from=' + encodeURIComponent(calculatedRange.from) +
                       '&date_to=' + encodeURIComponent(calculatedRange.to) +
                       '&type=' + encodeURIComponent(filterType);
        window.open(exportUrl, '_blank');
    }

    // ============================================================
    // SLIDE-IN PANEL FUNCTIONS - Microsoft Style
    // ============================================================
    
    function openClinicPanel(clinicName) {
        var panel = $('#clinicDetailPanel');
        var overlay = $('#clinicDetailOverlay');
        $('#panelClinicName').text(clinicName || 'Detalles de la Clínica');
        overlay.fadeIn(300);
        panel.addClass('open');
        $('body').addClass('panel-open');
    }
    
    function closeClinicPanel() {
        var panel = $('#clinicDetailPanel');
        var overlay = $('#clinicDetailOverlay');
        panel.removeClass('open');
        overlay.fadeOut(300);
        $('body').removeClass('panel-open');
    }
    
    $('#panelCloseBtn').on('click', function() {
        closeClinicPanel();
    });
    
    $('#clinicDetailOverlay').on('click', function() {
        closeClinicPanel();
    });
    
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#clinicDetailPanel').hasClass('open')) {
            closeClinicPanel();
        }
    });

    // ============================================================
    // VIEW CLINIC DETAIL - FIXED
    // ============================================================
    $(document).on('click', '.btn-view-detail', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var clickedBtn = $(this);
        var clinicId = clickedBtn.data('id');
        var currentRow = clickedBtn.closest('tr');
        var clinicName = currentRow.find('.clinic-name').text().trim() || 'Clínica';
        
        console.log('🔍 Clinic name extracted:', clinicName);
        console.log('🔍 Clinic ID:', clinicId);
        
        var dateRange = $('#date-range-selector').val();
        var calculatedRange = calculateDateRange(dateRange);
        var clinicas = getSelectedClinicas();
        var filterType = $('#type-filter').val();
        var panelBody = $('#panelBody');
        
        panelBody.html(
            '<div class="text-center py-5">' +
            '    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">' +
            '        <span class="sr-only">Cargando...</span>' +
            '    </div>' +
            '    <p class="mt-3 text-muted">Cargando detalles de la clínica...</p>' +
            '</div>'
        );
        
        openClinicPanel(clinicName);
        
        $.ajax({
            url: config.clinicDetailUrl,
            type: 'GET',
            data: {
                id: clinicId,
                range: dateRange,
                date_from: calculatedRange.from,
                date_to: calculatedRange.to,
                clinicas: clinicas.length ? clinicas.join(',') : 'todas',
                type: filterType
            },
            dataType: 'json',
            timeout: 30000,
            success: function(response) {
                if (response.success && response.html) {
                    panelBody.html(response.html);
                    console.log('✅ Clinic detail loaded successfully');
                    
                    // Re-inicializar tooltips SOLO en el panel (idempotente)
                    $(document).trigger('panel:rendered');
                } else {
                    panelBody.html(
                        '<div class="alert alert-danger m-4">' +
                        '    <i class="fas fa-exclamation-triangle me-2"></i>' +
                        '    ' + (response.message || 'Error al cargar detalles') +
                        '</div>'
                    );
                }
            },
            error: function(xhr, status, error) {
                var errorMsg = 'Error del servidor: ' + error;
                panelBody.html(
                    '<div class="alert alert-danger m-4">' +
                    '    <i class="fas fa-exclamation-triangle me-2"></i>' +
                    '    ' + errorMsg +
                    '</div>'
                );
            }
        });
    });

    // Initialize
    initTooltips();              // ← Inicializa tooltips existentes al cargar la página
    updateDateInputs('custom');
    console.log('System initialized with slide-in panel');
});
JS
);
?>

<style>
    /* ============================================================
       SLIDE-IN PANEL STYLES - Microsoft Fluent Design
    ============================================================ */

    .clinic-detail-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1050;
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
    }

    .clinic-detail-panel {
        position: fixed;
        top: 0;
        right: -100%;
        width: 85%;
        max-width: 1200px;
        height: 100%;
        background: #ffffff;
        z-index: 1051;
        box-shadow: -8px 0 40px rgba(0, 0, 0, 0.2);
        transition: right 0.4s cubic-bezier(0.22, 1, 0.36, 1);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .clinic-detail-panel.open {
        right: 0;
    }

    .panel-header {
        background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);
        color: #ffffff;
        padding: 0;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(0, 120, 212, 0.3);
        position: relative;
        z-index: 2;
    }

    .panel-header-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 28px;
        min-height: 72px;
    }

    .panel-title {
        font-size: 1.4rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 12px;
        color: #ffffff;
    }

    .panel-title i {
        font-size: 1.6rem;
        color: #ffffff;
    }

    .panel-title span {
        color: #ffffff;
    }

    .panel-close-btn {
        background: rgba(255, 255, 255, 0.15);
        border: 2px solid rgba(255, 255, 255, 0.2);
        color: #ffffff;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        font-size: 1.4rem;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .panel-close-btn:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: scale(1.05);
        border-color: rgba(255, 255, 255, 0.4);
    }

    .panel-close-btn:active {
        transform: scale(0.95);
    }

    .panel-close-btn i {
        color: #ffffff;
        font-size: 1.4rem;
    }

    .panel-body {
        flex: 1;
        overflow-y: auto;
        padding: 0;
        background: #f8f9fa;
    }

    .panel-body .clinic-detail-container {
        padding: 24px 28px;
    }

    .panel-body::-webkit-scrollbar {
        width: 8px;
    }

    .panel-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .panel-body::-webkit-scrollbar-thumb {
        background: #c1c7cd;
        border-radius: 4px;
    }

    .panel-body::-webkit-scrollbar-thumb:hover {
        background: #a0a8b0;
    }

    body.panel-open {
        overflow: hidden;
    }

    @media (max-width: 992px) {
        .clinic-detail-panel {
            width: 92%;
            max-width: 100%;
        }
    }

    @media (max-width: 768px) {
        .clinic-detail-panel {
            width: 100%;
            max-width: 100%;
        }

        .panel-header-content {
            padding: 16px 20px;
            min-height: 60px;
        }

        .panel-title {
            font-size: 1.1rem;
        }

        .panel-close-btn {
            width: 38px;
            height: 38px;
            font-size: 1.2rem;
        }

        .panel-close-btn i {
            font-size: 1.2rem;
        }

        .panel-body .clinic-detail-container {
            padding: 16px 18px;
        }
    }

    @media (max-width: 576px) {
        .panel-header-content {
            padding: 12px 16px;
            min-height: 54px;
        }

        .panel-title {
            font-size: 1rem;
        }

        .panel-title i {
            font-size: 1.2rem;
        }

        .panel-close-btn {
            width: 34px;
            height: 34px;
            font-size: 1rem;
        }

        .panel-close-btn i {
            font-size: 1rem;
        }

        .panel-body .clinic-detail-container {
            padding: 12px 14px;
        }
    }

    .filter-group-ms {
        transition: all 0.2s ease;
    }

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

    #report-results .btn-view-detail {
        width: 34px;
        height: 34px;
        padding: 0;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        border: 1px solid #0078d4;
        color: #0078d4;
        background: transparent;
        cursor: pointer;
    }

    #report-results .btn-view-detail:hover {
        transform: scale(1.1);
        background: #0078d4;
        color: white;
        box-shadow: 0 2px 12px rgba(0, 120, 212, 0.4);
    }

    #report-results .table tfoot td {
        padding: 12px 8px;
        font-size: 0.95rem;
        border-top: 2px solid #0078d4;
    }

    .select2-container--bootstrap-5 .select2-selection {
        min-height: 55px !important;
        font-size: 1.4rem !important;
        border-radius: 8px !important;
        border-color: #107c10 !important;
    }

    /* ============================================================
       TOOLTIPS — Microsoft Fluent Style + Anti-flicker
    ============================================================ */
    .tooltip {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: 0.95rem;
        pointer-events: none;
        /* evita que el tooltip capture el mouse */
        z-index: 9999 !important;
        /* siempre por encima de todo */
    }

    .tooltip .tooltip-inner {
        background-color: #1a1a1a;
        color: #ffffff;
        padding: 10px 14px;
        border-radius: 6px;
        max-width: 320px;
        text-align: left;
        line-height: 1.5;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
        font-weight: 400;
    }

    /* Evita el parpadeo durante la transición de opacidad */
    .tooltip.show {
        opacity: 0.98;
        transition: opacity 0.15s ease-in-out;
    }

    /* El ícono ⓘ no debe cambiar el cursor ni disparar el hover del ancestro */
    [data-toggle="tooltip"] {
        cursor: help;
        display: inline-block;
        vertical-align: middle;
        line-height: 1;
    }

    [data-toggle="tooltip"]:focus {
        outline: none;
    }

    .tooltip.bs-tooltip-top .arrow::before,
    .tooltip.bs-tooltip-auto[x-placement^="top"] .arrow::before {
        border-top-color: #1a1a1a;
    }

    .tooltip.bs-tooltip-bottom .arrow::before,
    .tooltip.bs-tooltip-auto[x-placement^="bottom"] .arrow::before {
        border-bottom-color: #1a1a1a;
    }

    .tooltip.bs-tooltip-left .arrow::before,
    .tooltip.bs-tooltip-auto[x-placement^="left"] .arrow::before {
        border-left-color: #1a1a1a;
    }

    .tooltip.bs-tooltip-right .arrow::before,
    .tooltip.bs-tooltip-auto[x-placement^="right"] .arrow::before {
        border-right-color: #1a1a1a;
    }
</style>