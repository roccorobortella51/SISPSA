<?php
// app/views/reporte-atenciones/uso-servicios.php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\RmClinica;
use app\components\UserHelper;

/** @var yii\web\View $this */
/** @var app\models\SisSiniestroUsoSearch $searchModel */
/** @var array $reportData */
/** @var array $timelineData */
/** @var array $categoryData */
/** @var array $inactiveUsers */
/** @var array $clinicas */
/** @var array $baremos */

$this->title = 'Reporte de Uso de Servicios Médicos';
$this->params['breadcrumbs'][] = $this->title;

// Check user role for clinic access
$hasClinicAccess = UserHelper::hasClinicAccess();

// URLs
$ajaxUrl = Url::to(['generate-uso-servicios']);
?>

<div class="uso-servicios-index">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="text-center">
                <h1 class="display-5 fw-bold text-primary mb-2">
                    <i class="fas fa-chart-pie me-2"></i> <?= Html::encode($this->title) ?>
                    <?php if ($hasClinicAccess): ?>
                        <span class="badge bg-info ms-2" style="font-size: 0.9rem;">
                            <i class="fas fa-lock me-1"></i> Acceso Restringido
                        </span>
                    <?php endif; ?>
                </h1>
                <p class="lead text-muted">
                    Análisis de utilización de servicios médicos por parte de los afiliados
                    <?php if ($hasClinicAccess): ?>
                        <br><small class="text-primary"><i class="fas fa-building me-1"></i>Datos limitados a sus clínicas asignadas</small>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-lg mb-5"
        style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-left: 4px solid #0078d4;">
        <div class="card-body p-4">
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
                                    Configure los parámetros para analizar el uso de servicios médicos
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
                <!-- Alcance del Reporte - NEW -->
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="filter-group-ms border-0 shadow-lg p-3 rounded-3"
                        style="background: white; border-left: 3px solid #6f42c1;">
                        <label class="form-label fw-bold mb-2 d-flex align-items-center"
                            style="font-size: 1.2rem !important; color: #2c3e50;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm"
                                style="width: 40px; height: 40px; background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);">
                                <i class="fas fa-globe-americas text-white" style="font-size: 1.4rem;"></i>
                            </div>
                            Alcance del Reporte
                            <span class="ms-2"
                                data-toggle="tooltip"
                                data-placement="top"
                                title="Seleccione 'Período' para filtrar por fechas o 'Cartera Completa' para ver todos los datos históricos sin límite de fechas">
                                <i class="fas fa-info-circle text-muted" style="font-size: 0.85rem;"></i>
                            </span>
                        </label>
                        <select id="scope-filter" class="form-select border-2 shadow-sm py-2"
                            style="font-size: 1.2rem !important; height: 50px; border-radius: 8px; border-color: #6f42c1;">
                            <option value="period">📅 Período Específico</option>
                            <option value="cartera" selected>📊 Cartera Completa (Todo el histórico)</option>
                        </select>
                        <div class="mt-1">
                            <small class="text-muted" style="font-size: 1.1rem !important;">
                                <i class="fas fa-info-circle me-1" style="color: #6f42c1;"></i>
                                <span id="scope-description">Analiza los datos en un rango de fechas específico</span>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Fecha Desde - Show/Hide based on scope -->
                <div class="col-xl-3 col-lg-4 col-md-6" id="date-from-container">
                    <div class="filter-group-ms border-0 shadow-lg p-3 rounded-3"
                        style="background: white; border-left: 3px solid #0078d4;">
                        <label class="form-label fw-bold mb-2 d-flex align-items-center"
                            style="font-size: 1.2rem !important; color: #2c3e50;">
                            <i class="fas fa-calendar-day me-2 ms-primary"></i>
                            Fecha Desde
                            <span class="ms-2"
                                data-toggle="tooltip"
                                data-placement="top"
                                title="Fecha de inicio del período a analizar">
                                <i class="fas fa-info-circle text-muted" style="font-size: 0.85rem;"></i>
                            </span>
                        </label>
                        <input type="date" id="date-from" class="form-control border-2 border-primary shadow-sm py-2"
                            style="font-size: 1.2rem !important; height: 50px; border-radius: 8px;"
                            value="<?= $searchModel->date_from ?? date('Y-m-d', strtotime('-1 month')) ?>">
                    </div>
                </div>

                <!-- Fecha Hasta - Show/Hide based on scope -->
                <div class="col-xl-3 col-lg-4 col-md-6" id="date-to-container">
                    <div class="filter-group-ms border-0 shadow-lg p-3 rounded-3"
                        style="background: white; border-left: 3px solid #107c10;">
                        <label class="form-label fw-bold mb-2 d-flex align-items-center"
                            style="font-size: 1.2rem !important; color: #2c3e50;">
                            <i class="fas fa-calendar-day me-2 ms-success"></i>
                            Fecha Hasta
                            <span class="ms-2"
                                data-toggle="tooltip"
                                data-placement="top"
                                title="Fecha de fin del período a analizar">
                                <i class="fas fa-info-circle text-muted" style="font-size: 0.85rem;"></i>
                            </span>
                        </label>
                        <input type="date" id="date-to" class="form-control border-2 border-success shadow-sm py-2"
                            style="font-size: 1.2rem !important; height: 50px; border-radius: 8px;"
                            value="<?= $searchModel->date_to ?? date('Y-m-d') ?>">
                    </div>
                </div>

                <!-- Tipo de Atención -->
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="filter-group-ms border-0 shadow-lg p-3 rounded-3"
                        style="background: white; border-left: 3px solid #ff8c00;">
                        <label class="form-label fw-bold mb-2 d-flex align-items-center"
                            style="font-size: 1.2rem !important; color: #2c3e50;">
                            <i class="fas fa-filter me-2 ms-warning"></i>
                            Tipo de Atención
                            <span class="ms-2"
                                data-toggle="tooltip"
                                data-placement="top"
                                title="Filtra por el tipo de atención médica: Citas programadas o Emergencias">
                                <i class="fas fa-info-circle text-muted" style="font-size: 0.85rem;"></i>
                            </span>
                        </label>
                        <select id="tipo-atencion" class="form-select border-2 border-warning shadow-sm py-2"
                            style="font-size: 1.2rem !important; height: 50px; border-radius: 8px;">
                            <option value="all" selected>📋 Todas</option>
                            <option value="citas">📅 Citas Médicas</option>
                            <option value="siniestros">🚑 Emergencias</option>
                        </select>
                    </div>
                </div>

                <!-- Clínicas -->
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="filter-group-ms border-0 shadow-lg p-3 rounded-3"
                        style="background: white; border-left: 3px solid #107c10;">
                        <label class="form-label fw-bold mb-2 d-flex align-items-center"
                            style="font-size: 1.2rem !important; color: #2c3e50;">
                            <i class="fas fa-hospital me-2 ms-success"></i>
                            Clínica
                            <span class="ms-2"
                                data-toggle="tooltip"
                                data-placement="top"
                                title="Seleccione una clínica específica o todas para ver el panorama general">
                                <i class="fas fa-info-circle text-muted" style="font-size: 0.85rem;"></i>
                            </span>
                        </label>
                        <select id="clinica-filter" class="form-select border-2 border-success shadow-sm py-2"
                            style="font-size: 1.2rem !important; height: 50px; border-radius: 8px;"
                            <?= $hasClinicAccess ? 'disabled' : '' ?>>
                            <option value="">🏥 Todas las Clínicas</option>
                            <?php foreach ($clinicas as $clinica): ?>
                                <option value="<?= $clinica->id ?>">
                                    <?= Html::encode($clinica->nombre) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Botón Generar Reporte -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="text-center border-top pt-4">
                        <div class="d-inline-block p-3 rounded-3 shadow-lg"
                            style="background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);">
                            <?= Html::button('<i class="fas fa-chart-bar me-3"></i> Generar Reporte de Uso', [
                                'id' => 'btn-generar-reporte',
                                'class' => 'btn btn-light btn-xl px-4 py-3 fw-bold shadow-lg',
                                'style' => 'font-size: 1.4rem !important; border-radius: 10px;',
                                'data-toggle' => 'tooltip',
                                'data-placement' => 'bottom',
                                'title' => 'Haga clic para generar el reporte con los filtros seleccionados'
                            ]) ?>
                            <div class="mt-2">
                                <small class="text-white" style="font-size: 1.1rem !important;">
                                    <i class="fas fa-bolt me-2"></i>Analiza qué porcentaje de afiliados está usando los servicios
                                    <?php if ($hasClinicAccess): ?>
                                        <br><i class="fas fa-lock me-1"></i>Los datos se limitarán a sus clínicas asignadas
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
    <div id="report-results" class="mt-4">
        <!-- Empty State -->
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center p-5">
                <div class="mb-4">
                    <i class="fas fa-chart-pie fa-5x text-muted mb-4 opacity-25"></i>
                </div>
                <h3 class="text-dark mb-3 display-6">Reporte de Uso de Servicios Médicos</h3>
                <p class="text-muted mb-4 fs-4">
                    Configure los filtros arriba y presione <span class="badge bg-primary px-4 py-3 fs-5">Generar Reporte de Uso</span><br>
                    para visualizar el análisis de utilización de servicios.
                    <?php if ($hasClinicAccess): ?>
                        <br><span class="text-info"><i class="fas fa-chart-simple me-1"></i>Los datos se limitarán a sus clínicas asignadas</span>
                    <?php endif; ?>
                </p>
                <div class="alert alert-light border" style="max-width: 600px; margin: 0 auto;">
                    <i class="fas fa-info-circle text-primary me-2"></i>
                    <strong>¿Qué mide este reporte?</strong>
                    <p class="mb-0 text-muted mt-2" style="font-size: 0.95rem;">
                        Este reporte muestra qué porcentaje de su cartera de afiliados está utilizando los servicios médicos,
                        desglosado por tipo de atención, clínica y servicio específico. Ideal para evaluar el modelo de negocio.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Pass user clinic IDs to JavaScript
$userClinicaIdsJs = $hasClinicAccess ? json_encode(UserHelper::getAccessibleClinicaIds()) : '[]';

$this->registerJs(
    <<<JS
$(document).ready(function() {
    // Enable tooltips
    $('[data-toggle="tooltip"]').tooltip({
        container: 'body',
        trigger: 'hover focus',
        animation: true,
        delay: { show: 200, hide: 100 }
    });
    
    var config = {
        ajaxUrl: '{$ajaxUrl}',
        hasClinicAccess: '{$hasClinicAccess}' === '1',
        userClinicaIds: {$userClinicaIdsJs}
    };
    
    // Function to format date as YYYY-MM-DD
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
    
    // ============================================================
    // SCOPE FILTER TOGGLE
    // ============================================================
    $('#scope-filter').on('change', function() {
        var scope = $(this).val();
        var description = $('#scope-description');
        var dateFromContainer = $('#date-from-container');
        var dateToContainer = $('#date-to-container');
        
        if (scope === 'cartera') {
            description.html('<i class="fas fa-database me-1" style="color: #6f42c1;"></i> Muestra todos los datos históricos sin límite de fechas');
            dateFromContainer.fadeOut(300);
            dateToContainer.fadeOut(300);
            // Clear date values when switching to cartera
            $('#date-from').val('');
            $('#date-to').val('');
        } else {
            description.html('<i class="fas fa-calendar-alt me-1" style="color: #6f42c1;"></i> Analiza los datos en un rango de fechas específico');
            dateFromContainer.fadeIn(300);
            dateToContainer.fadeIn(300);
            // Set default dates if empty
            var today = new Date();
            var lastMonth = new Date();
            lastMonth.setMonth(today.getMonth() - 1);
            if (!$('#date-from').val()) {
                $('#date-from').val(formatDate(lastMonth));
            }
            if (!$('#date-to').val()) {
                $('#date-to').val(formatDate(today));
            }
        }
    });
    
    // Generate report function
    function generateReport() {
        var dateFrom = $('#date-from').val();
        var dateTo = $('#date-to').val();
        var tipoAtencion = $('#tipo-atencion').val();
        var clinicaId = $('#clinica-filter').val();
        var scope = $('#scope-filter').val();
        
        // If scope is 'period', validate dates
        if (scope === 'period') {
            if (!dateFrom || !dateTo) {
                alert('Por favor, seleccione el rango de fechas.');
                return;
            }
        }
        
        var params = {
            date_from: dateFrom,
            date_to: dateTo,
            tipo_atencion: tipoAtencion,
            clinica_id: clinicaId,
            scope: scope
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
                    $('html, body').animate({
                        scrollTop: $('#report-results').offset().top - 100
                    }, 500);
                    
                    // Re-enable tooltips in dynamically loaded content
                    $('[data-toggle="tooltip"]').tooltip({
                        container: 'body',
                        trigger: 'hover focus',
                        animation: true,
                        delay: { show: 200, hide: 100 }
                    });
                } else {
                    showError(response.message || 'Error desconocido');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
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
    
    // Show loading
    function showLoading() {
        $('#report-results').html(
            '<div class="card border-0 shadow">' +
            '    <div class="card-body text-center py-5">' +
            '        <div class="mb-4">' +
            '            <i class="fas fa-chart-pie fa-5x text-primary"></i>' +
            '        </div>' +
            '        <h3 class="text-primary mb-3">Generando Reporte de Uso</h3>' +
            '        <p class="text-muted fs-5">Analizando la utilización de servicios...</p>' +
            '        <div class="mt-5">' +
            '            <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status">' +
            '                <span class="visually-hidden">Cargando...</span>' +
            '            </div>' +
            '        </div>' +
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
    
    // Initialize with default date range
    console.log('System initialized with Service Usage Report');
});
JS
);
?>

<style>
    .filter-group-ms {
        transition: all 0.2s ease;
    }

    .filter-group-ms:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08) !important;
    }

    /* Microsoft-style tooltip customization */
    .tooltip {
        font-family: 'Segoe UI', Arial, sans-serif;
        font-size: 0.85rem;
        font-weight: 400;
    }

    .tooltip-inner {
        background: #2c3e50;
        color: #ffffff;
        padding: 8px 14px;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        max-width: 280px;
        text-align: center;
        line-height: 1.5;
    }

    .bs-tooltip-top .arrow::before,
    .bs-tooltip-auto[x-placement^="top"] .arrow::before {
        border-top-color: #2c3e50;
    }

    .bs-tooltip-bottom .arrow::before,
    .bs-tooltip-auto[x-placement^="bottom"] .arrow::before {
        border-bottom-color: #2c3e50;
    }

    .bs-tooltip-left .arrow::before,
    .bs-tooltip-auto[x-placement^="left"] .arrow::before {
        border-left-color: #2c3e50;
    }

    .bs-tooltip-right .arrow::before,
    .bs-tooltip-auto[x-placement^="right"] .arrow::before {
        border-right-color: #2c3e50;
    }

    /* Info icon hover effect */
    .fa-info-circle {
        cursor: pointer;
        transition: color 0.2s ease, transform 0.2s ease;
    }

    .fa-info-circle:hover {
        color: #0078d4 !important;
        transform: scale(1.15);
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

    @media (max-width: 768px) {
        .filter-group-ms {
            margin-bottom: 0.5rem;
        }
    }
</style>