<?php
// File: C:\xampp\htdocs\sipsa\views\rton-report\index.php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;
use kartik\date\DatePicker;
use app\components\UserHelper;
use app\models\RTONReportSearch;

/* @var $this yii\web\View */
/* @var $searchModel RTONReportSearch */
/* @var $dataProvider yii\data\ArrayDataProvider|null */
/* @var $clinicas array */
/* @var $summary array */

$this->title = 'RTON - Reporte de Transacciones y Operaciones de Negocios';
$this->params['breadcrumbs'][] = $this->title;

// Initialize all variables with defaults
$searchModel = $searchModel ?? new RTONReportSearch();
$dataProvider = $dataProvider ?? null;
$summary = $summary ?? ['total_payments' => 0, 'total_amount' => 0, 'total_usd_amount' => 0, 'total_coverage' => 0, 'unique_clinics' => 0];

$dateRangeOptions = RTONReportSearch::getDateRangeOptions();

// Check user role for clinic access
$hasClinicAccess = UserHelper::hasClinicAccess();

// Set default date range type if not set
if (empty($searchModel->date_range_type)) {
    $searchModel->date_range_type = 'this_month';
}

// Get the URLs for exports
$exportExcelUrl = Url::to(['/rton-report/export-excel']);
$exportCsvUrl = Url::to(['/rton-report/export-csv']);
?>

<div class="rton-container">
    <div class="row mb-4">
        <div class="col-12">
            <div class="text-center">
                <h1 class="display-5 fw-bold text-primary mb-2">
                    <i class="fas fa-file-alt me-2"></i> <?= Html::encode($this->title) ?>
                </h1>
                <p class="lead text-muted">
                    Reporte de Transacciones y Operaciones de Negocios (RTON)
                    <?php if ($hasClinicAccess): ?>
                        <span class="badge bg-info ms-2" style="font-size: 1rem;">
                            <i class="fas fa-lock me-1"></i> Acceso Restringido
                        </span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-lg mb-5" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-left: 4px solid #0078d4;">
        <div class="card-body p-4">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-light border-0 shadow-lg p-3" style="background: white; border-left: 4px solid #107c10;">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3 shadow"
                                style="width: 60px; height: 60px; background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);">
                                <i class="fas fa-sliders-h text-white" style="font-size: 1.8rem;"></i>
                            </div>
                            <div>
                                <h3 class="mb-1 fw-bold" style="font-size: 1.6rem !important; color: #2c3e50;">
                                    <i class="fas fa-filter me-2"></i>Configuración del Reporte RTON
                                </h3>
                                <p class="mb-0 text-muted" style="font-size: 1.4rem !important;">
                                    Configure los parámetros para generar el reporte de transacciones
                                    <?php if ($hasClinicAccess): ?>
                                        <br><small class="text-primary"><i class="fas fa-info-circle me-1"></i>Los datos están limitados a sus clínicas asignadas</small>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php $form = ActiveForm::begin([
                'method' => 'get',
                'id' => 'rton-form',
                'enableClientValidation' => false,
            ]); ?>

            <div class="row g-3 align-items-end">
                <div class="col-12">
                    <div class="filter-group border-0 shadow-lg p-3 rounded-3" style="background: white; border-left: 3px solid #ff8c00;">
                        <label class="form-label fw-bold mb-2 d-flex align-items-center" style="font-size: 1.4rem !important; color: #2c3e50;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm"
                                style="width: 40px; height: 40px; background: linear-gradient(135deg, #ff8c00 0%, #e67e00 100%);">
                                <i class="fas fa-calendar-alt text-white" style="font-size: 1.4rem;"></i>
                            </div>
                            Período de Análisis
                        </label>
                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($searchModel, 'date_range_type')->dropDownList(
                                    $dateRangeOptions,
                                    [
                                        'prompt' => 'Seleccione un rango...',
                                        'class' => 'form-select border-2 border-warning shadow-sm py-2',
                                        'style' => 'font-size: 1.4rem !important; height: 55px; border-radius: 8px;',
                                        'id' => 'date-range-selector'
                                    ]
                                )->label(false) ?>
                            </div>
                            <div class="col-md-6">
                                <div class="mt-1">
                                    <small class="text-muted" style="font-size: 1.2rem !important;">
                                        <i class="fas fa-info-circle me-2 text-warning"></i>
                                        Defina el período del reporte
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3" id="custom-dates-container" style="display: none;">
                <div class="col-12">
                    <div class="filter-group border-0 shadow-lg p-4 rounded-3 mt-2"
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
                                        <div class="card border-0 shadow-sm h-100" style="border-left: 3px solid #0078d4;">
                                            <div class="card-body p-3">
                                                <label class="form-label fw-bold mb-2" style="font-size: 1.4rem !important;">
                                                    <i class="fas fa-calendar-day me-2 text-primary"></i>
                                                    Fecha Inicial
                                                </label>
                                                <?= DatePicker::widget([
                                                    'name' => 'RTONReportSearch[date_from]',
                                                    'value' => $searchModel->date_from,
                                                    'type' => DatePicker::TYPE_INPUT,
                                                    'options' => [
                                                        'placeholder' => 'Seleccione fecha desde',
                                                        'class' => 'form-control',
                                                        'style' => 'font-size: 1.4rem !important;',
                                                        'id' => 'date-from-picker',
                                                    ],
                                                    'pluginOptions' => [
                                                        'autoclose' => true,
                                                        'format' => 'yyyy-mm-dd',
                                                        'todayHighlight' => true,
                                                        'todayBtn' => true,
                                                        'clearBtn' => true,
                                                    ],
                                                ]) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-0 shadow-sm h-100" style="border-left: 3px solid #107c10;">
                                            <div class="card-body p-3">
                                                <label class="form-label fw-bold mb-2" style="font-size: 1.4rem !important;">
                                                    <i class="fas fa-calendar-day me-2 text-success"></i>
                                                    Fecha Final
                                                </label>
                                                <?= DatePicker::widget([
                                                    'name' => 'RTONReportSearch[date_to]',
                                                    'value' => $searchModel->date_to,
                                                    'type' => DatePicker::TYPE_INPUT,
                                                    'options' => [
                                                        'placeholder' => 'Seleccione fecha hasta',
                                                        'class' => 'form-control',
                                                        'style' => 'font-size: 1.4rem !important;',
                                                        'id' => 'date-to-picker',
                                                    ],
                                                    'pluginOptions' => [
                                                        'autoclose' => true,
                                                        'format' => 'yyyy-mm-dd',
                                                        'todayHighlight' => true,
                                                        'todayBtn' => true,
                                                        'clearBtn' => true,
                                                    ],
                                                ]) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="text-center border-top pt-4">
                        <div class="d-inline-block p-3 rounded-3 shadow-lg" style="background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);">
                            <?= Html::submitButton('<i class="fas fa-chart-bar me-3"></i> Generar Reporte', [
                                'class' => 'btn btn-light btn-xl px-4 py-3 fw-bold shadow-lg',
                                'style' => 'font-size: 1.6rem !important; border-radius: 10px;'
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <!-- Summary Cards -->
    <?php if (Yii::$app->request->get() && $summary['total_payments'] > 0): ?>
        <div class="row">
            <div class="col-md-3">
                <div class="summary-card">
                    <div class="summary-icon"><i class="fas fa-exchange-alt fa-2x" style="color: #3498db;"></i></div>
                    <div class="summary-value"><?= number_format($summary['total_payments']) ?></div>
                    <div class="summary-label">Total Transacciones</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card">
                    <div class="summary-icon"><i class="fas fa-money-bill-wave fa-2x" style="color: #27ae60;"></i></div>
                    <div class="summary-value">Bs. <?= number_format($summary['total_amount'], 2) ?></div>
                    <div class="summary-label">Monto Total (Bs.)</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card">
                    <div class="summary-icon"><i class="fas fa-dollar-sign fa-2x" style="color: #f39c12;"></i></div>
                    <div class="summary-value">$ <?= number_format($summary['total_usd_amount'] ?? 0, 2) ?></div>
                    <div class="summary-label">Monto Total (USD)</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card">
                    <div class="summary-icon"><i class="fas fa-building fa-2x" style="color: #8e44ad;"></i></div>
                    <div class="summary-value"><?= number_format($summary['unique_clinics']) ?></div>
                    <div class="summary-label">Clínicas Involucradas</div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Results Table -->
    <?php if ($dataProvider && $dataProvider->getTotalCount() > 0): ?>
        <div class="card-filter mt-3">
            <div class="card-filter-header">
                <i class="fas fa-list"></i> Resultados por Clínica
                <span class="float-right">
                    <span class="badge badge-light"><?= $dataProvider->getTotalCount() ?> clínicas</span>
                    <div class="btn-group ms-3">
                        <button type="button" class="btn btn-success btn-sm" id="btn-export-excel">
                            <i class="fas fa-file-excel"></i> Exportar Excel
                        </button>
                        <button type="button" class="btn btn-info btn-sm" id="btn-export-csv">
                            <i class="fas fa-file-csv"></i> Exportar CSV
                        </button>
                    </div>
                </span>
            </div>
            <div class="card-filter-body p-0">
                <div class="table-responsive">
                    <table class="table table-rton" id="rton-results-table">
                        <thead>
                            <tr>
                                <th>CÓD. SUCURSAL</th>
                                <th>CLÍNICA</th>
                                <th>TOTAL TRANSACCIONES</th>
                                <th>MONTO TOTAL (Bs.)</th>
                                <th>MONTO TOTAL (USD)</th>
                                <th>COBERTURA TOTAL</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dataProvider->getModels() as $clinic): ?>
                                <tr class="clinic-row" data-clinic-id="<?= $clinic['clinica_id'] ?>">
                                    <td><?= Html::encode($clinic['clinica_codigo'] ?: $clinic['clinica_id']) ?></td>
                                    <td><strong><?= Html::encode($clinic['clinica_nombre']) ?></strong></td>
                                    <td class="text-center"><?= number_format($clinic['summary']['total_transactions']) ?></td>
                                    <td class="text-right">Bs. <?= number_format($clinic['summary']['total_amount'], 2) ?></td>
                                    <td class="text-right">$ <?= number_format($clinic['summary']['total_usd_amount'] ?? 0, 2) ?></td>
                                    <td class="text-right">Bs. <?= number_format($clinic['summary']['total_coverage'], 2) ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary toggle-details" data-clinic-id="<?= $clinic['clinica_id'] ?>">
                                            <i class="fas fa-chevron-down"></i> Ver Detalles
                                        </button>
                                    </td>
                                </tr>
                                <tr class="details-row-<?= $clinic['clinica_id'] ?>" style="display: none;">
                                    <td colspan="7" class="p-0">
                                        <div class="p-3 bg-light">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="table-secondary">
                                                    <tr>
                                                        <th>FECHA EMISIÓN</th>
                                                        <th>N° PÓLIZA</th>
                                                        <th>MONTO (Bs.)</th>
                                                        <th>MONTO (USD)</th>
                                                        <th>FORMA PAGO</th>
                                                        <th>CONTRATANTE</th>
                                                        <th>INTERMEDIARIO</th>
                                                        <th>ESTADO</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($clinic['transactions'] as $transaction): ?>
                                                        <tr>
                                                            <td><?= Html::encode($transaction['fecha_emision']) ?></td>
                                                            <td><?= Html::encode($transaction['numero_poliza'] ?: 'N/A') ?></td>
                                                            <td class="text-right">Bs. <?= number_format((float)$transaction['monto_prima'], 2) ?></td>
                                                            <td class="text-right">$ <?= number_format((float)($transaction['monto_pagado'] ?? 0), 2) ?></td>
                                                            <td><?= Html::encode($transaction['forma_pago']) ?></td>
                                                            <td><?= Html::encode(mb_substr($transaction['nombre_contratante'], 0, 35)) ?></td>
                                                            <td><?= Html::encode(mb_substr($transaction['primer_intermediario'], 0, 25) ?: 'N/A') ?></td>
                                                            <td>
                                                                <?php
                                                                $badgeClass = $transaction['estado_pago'] === 'Conciliado' ? 'badge-conciliado' : 'badge-por-conciliar';
                                                                ?>
                                                                <span class="<?= $badgeClass ?>"><?= Html::encode($transaction['estado_pago']) ?></span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="p-3 bg-light">
                    <?= LinkPager::widget([
                        'pagination' => $dataProvider->getPagination(),
                        'options' => ['class' => 'pagination justify-content-center mb-0'],
                        'linkOptions' => ['class' => 'page-link'],
                        'pageCssClass' => 'page-item',
                    ]) ?>
                </div>
            </div>
        </div>
    <?php elseif (Yii::$app->request->get()): ?>
        <div class="alert alert-info text-center mt-3">
            <i class="fas fa-info-circle"></i> No se encontraron transacciones para los criterios seleccionados.
        </div>
    <?php else: ?>
        <div class="alert alert-secondary text-center mt-3">
            <i class="fas fa-search"></i> Seleccione el rango de fechas y presione "Generar Reporte" para generar el reporte.
        </div>
    <?php endif; ?>
</div>

<?php
$this->registerCss("
    .rton-container { background-color: #f5f5f5; min-height: calc(100vh - 120px); padding: 20px; }
    .card-filter { background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
    .card-filter-header { background: #2c3e50; color: white; padding: 12px 20px; font-weight: 600; border-radius: 8px 8px 0 0; }
    .card-filter-body { padding: 20px; }
    .summary-card { background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 15px; text-align: center; margin-bottom: 20px; transition: all 0.2s; }
    .summary-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
    .summary-value { font-size: 28px; font-weight: bold; color: #2c3e50; }
    .summary-label { font-size: 13px; color: #7f8c8d; margin-top: 5px; }
    .summary-icon { font-size: 32px; margin-bottom: 10px; }
    .table-rton { width: 100%; font-size: 13px; }
    .table-rton thead th { background: #34495e; color: white; font-weight: 600; white-space: nowrap; padding: 10px 12px; border: none; }
    .table-rton tbody td { padding: 8px 12px; border-bottom: 1px solid #ecf0f1; vertical-align: middle; }
    .table-rton tbody tr:hover { background-color: #f8f9fa; }
    .badge-conciliado { background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 4px; font-size: 11px; }
    .badge-por-conciliar { background: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 4px; font-size: 11px; }
    .filter-group { transition: all 0.2s ease; }
    .btn-xl { padding: 1rem 2rem; font-size: 1.5rem; border-radius: 0.5rem; }
");

// REGISTER JAVASCRIPT - BULLETPROOF VERSION
$js = <<<JS
    $(document).ready(function() {
        // Date range selector handler
        var dateRangeSelect = $('#date-range-selector');
        var customDatesContainer = $('#custom-dates-container');
        
        function updateCustomDatesVisibility() {
            var selectedValue = dateRangeSelect.val();
            if (selectedValue === 'custom') {
                customDatesContainer.slideDown(300);
            } else {
                customDatesContainer.slideUp(300);
            }
        }
        
        updateCustomDatesVisibility();
        dateRangeSelect.on('change', updateCustomDatesVisibility);
        
        // Toggle details rows for clinics
        $(document).on('click', '.toggle-details', function(e) {
            e.preventDefault();
            
            var clinicId = $(this).data('clinic-id');
            var detailsRow = $('.details-row-' + clinicId);
            var button = $(this);
            var icon = button.find('i');
            
            if (detailsRow.is(':visible')) {
                detailsRow.slideUp(300);
                icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                button.html('<i class="fas fa-chevron-down"></i> Ver Detalles');
            } else {
                $('.details-row').slideUp(300);
                $('.toggle-details i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                $('.toggle-details').html('<i class="fas fa-chevron-down"></i> Ver Detalles');
                
                detailsRow.slideDown(300);
                icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                button.html('<i class="fas fa-chevron-up"></i> Ocultar Detalles');
            }
        });
        
        // Export Excel
        $('#btn-export-excel').on('click', function() {
            exportReport('excel');
        });
        
        // Export CSV
        $('#btn-export-csv').on('click', function() {
            exportReport('csv');
        });
    });
    
    // Export function - works with both Excel and CSV
    function exportReport(type) {
        console.log('Exporting:', type);
        
        // Get the form
        var form = document.getElementById('rton-form');
        if (!form) {
            console.error('Form not found!');
            return;
        }
        
        // Get all form data
        var formData = new FormData(form);
        var url = type === 'excel' ? '$exportExcelUrl' : '$exportCsvUrl';
        
        console.log('URL:', url);
        
        // Create a new form for submission
        var submitForm = document.createElement('form');
        submitForm.method = 'POST';
        submitForm.action = url;
        submitForm.target = '_blank';
        
        // Add CSRF token
        var csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            var csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_csrf';
            csrfInput.value = csrfToken.content;
            submitForm.appendChild(csrfInput);
        }
        
        // Add all form data
        for (var pair of formData.entries()) {
            var name = pair[0];
            var value = pair[1];
            // Skip empty values
            if (value === '' || value === null || value === undefined) {
                continue;
            }
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            submitForm.appendChild(input);
        }
        
        // Log what we're submitting
        console.log('Submitting to:', url);
        console.log('Form data:', submitForm.elements);
        
        // Submit the form
        document.body.appendChild(submitForm);
        submitForm.submit();
        
        // Clean up after submission
        setTimeout(function() {
            if (document.body.contains(submitForm)) {
                document.body.removeChild(submitForm);
            }
        }, 3000);
    }
JS;

$this->registerJs($js);
?>