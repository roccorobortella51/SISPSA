<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\models\EventoReportSearch;

$this->title = 'Matriz PSNP - SUDEASEG';
$this->params['breadcrumbs'][] = $this->title;

$searchModel = $searchModel ?? new EventoReportSearch();
$data = $data ?? [];
$summary = $summary ?? ['total_rows' => 0];

$dateRangeOptions = EventoReportSearch::getDateRangeOptions();

if (empty($searchModel->date_range_type)) {
    $searchModel->date_range_type = 'this_month';
}

$exportExcelUrl = Url::to(['/evento-report/export-excel']);
?>

<div class="psnp-container">
    <div class="row mb-4">
        <div class="col-12">
            <div class="text-center">
                <h1 class="display-5 fw-bold text-primary mb-2">
                    <i class="fas fa-file-excel me-2"></i> <?= Html::encode($this->title) ?>
                </h1>
                <p class="lead text-muted">
                    Matriz de Prestaciones y Siniestros Notificados de Personas (PSNP)
                </p>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-lg mb-5" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-left: 4px solid #107c10;">
        <div class="card-body p-4">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-light border-0 shadow-lg p-3" style="background: white; border-left: 4px solid #107c10;">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3 shadow"
                                style="width: 60px; height: 60px; background: linear-gradient(135deg, #107c10 0%, #0b5930 100%);">
                                <i class="fas fa-sliders-h text-white" style="font-size: 1.8rem;"></i>
                            </div>
                            <div>
                                <h3 class="mb-1 fw-bold" style="font-size: 1.6rem !important; color: #2c3e50;">
                                    <i class="fas fa-filter me-2"></i>Configuración del Reporte
                                </h3>
                                <p class="mb-0 text-muted" style="font-size: 1.4rem !important;">
                                    Configure los parámetros para generar la Matriz PSNP
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php $form = ActiveForm::begin([
                'method' => 'get',
                'id' => 'psnp-form',
                'enableClientValidation' => false,
            ]); ?>

            <div class="row g-3 align-items-end">
                <div class="col-12">
                    <div class="filter-group border-0 shadow-lg p-3 rounded-3" style="background: white; border-left: 3px solid #107c10;">
                        <label class="form-label fw-bold mb-2 d-flex align-items-center" style="font-size: 1.4rem !important; color: #2c3e50;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm"
                                style="width: 40px; height: 40px; background: linear-gradient(135deg, #107c10 0%, #0b5930 100%);">
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
                                        'class' => 'form-select border-2 border-success shadow-sm py-2',
                                        'style' => 'font-size: 1.4rem !important; height: 55px; border-radius: 8px;',
                                        'id' => 'date-range-selector'
                                    ]
                                )->label(false) ?>
                            </div>
                            <div class="col-md-6">
                                <div class="mt-1">
                                    <small class="text-muted" style="font-size: 1.2rem !important;">
                                        <i class="fas fa-info-circle me-2 text-success"></i>
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
                        style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border: 2px solid #107c10;">
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
                                        <div class="card border-0 shadow-sm h-100" style="border-left: 3px solid #107c10;">
                                            <div class="card-body p-3">
                                                <label class="form-label fw-bold mb-2" style="font-size: 1.4rem !important;">
                                                    <i class="fas fa-calendar-day me-2 text-success"></i>
                                                    Fecha Inicial
                                                </label>
                                                <input type="date" id="date-from-picker" name="EventoReportSearch[date_from]"
                                                    class="form-control border-2 border-success py-2"
                                                    value="<?= Html::encode($searchModel->date_from) ?>"
                                                    style="font-size: 1.4rem !important; height: auto;">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-0 shadow-sm h-100" style="border-left: 3px solid #0078d4;">
                                            <div class="card-body p-3">
                                                <label class="form-label fw-bold mb-2" style="font-size: 1.4rem !important;">
                                                    <i class="fas fa-calendar-day me-2 text-primary"></i>
                                                    Fecha Final
                                                </label>
                                                <input type="date" id="date-to-picker" name="EventoReportSearch[date_to]"
                                                    class="form-control border-2 border-primary py-2"
                                                    value="<?= Html::encode($searchModel->date_to) ?>"
                                                    style="font-size: 1.4rem !important; height: auto;">
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
                        <div class="d-inline-block p-3 rounded-3 shadow-lg" style="background: linear-gradient(135deg, #107c10 0%, #0b5930 100%);">
                            <?= Html::submitButton('<i class="fas fa-chart-bar me-3"></i> Generar Matriz PSNP', [
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

    <?php if (Yii::$app->request->get() && $summary['total_rows'] > 0): ?>
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="summary-card">
                    <div class="summary-icon"><i class="fas fa-file-excel fa-2x" style="color: #107c10;"></i></div>
                    <div class="summary-value"><?= number_format($summary['total_rows']) ?></div>
                    <div class="summary-label">Registros Generados</div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3 px-1">
            <div>
                <h3 class="text-secondary fw-bold m-0" style="font-size: 1.4rem; color: #201f1e !important;">
                    <i class="fas fa-list me-2" style="color: #605e5c;"></i> Datos Transformados
                    <span class="badge badge-soft-muted ms-2" style="font-size: 0.85rem; font-weight: 500; background: #f3f2f1; color: #323130; padding: 4px 8px; border-radius: 4px;">
                        <?= $summary['total_rows'] ?> registros
                    </span>
                </h3>
            </div>

            <div class="btn-toolbar-fluent">
                <button type="button" class="btn btn-fluent-excel me-2" id="btn-export-excel">
                    <i class="fas fa-file-excel me-2"></i> Exportar Matriz PSNP
                </button>
            </div>
        </div>

        <div class="card-filter">
            <div class="card-filter-body p-0">
                <div class="table-responsive">
                    <table class="table table-psnp mb-0" id="psnp-results-table">
                        <thead>
                            <tr>
                                <th>N° Póliza</th>
                                <th>N° Evento</th>
                                <th>Afiliado</th>
                                <th>Cédula</th>
                                <th>Inicio Vig.</th>
                                <th>Fin Vig.</th>
                                <th>Suma Aseg.</th>
                                <th>Ubicación</th>
                                <th>Moneda</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $row): ?>
                                <tr>
                                    <td class="text-center"><?= Html::encode($row['n_poliza']) ?></td>
                                    <td class="text-center"><strong><?= Html::encode($row['n_evento_salud']) ?></strong></td>
                                    <td class="text-center"><?= Html::encode(mb_substr($row['nombre_afiliado'], 0, 40)) ?></td>
                                    <td class="text-center"><?= Html::encode($row['cedula_afiliado']) ?></td>
                                    <td class="text-center"><?= Html::encode($row['fecha_ini_vigencia']) ?></td>
                                    <td class="text-center"><?= Html::encode($row['fecha_fin_vigencia']) ?></td>
                                    <td class="text-center"><?= Html::encode($row['suma_asegurada']) ?></td>
                                    <td class="text-center"><?= Html::encode($row['ubicacion_geografica']) ?></td>
                                    <td class="text-center"><?= Html::encode($row['moneda_original']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php elseif (Yii::$app->request->get()): ?>
        <div class="alert alert-info text-center mt-3">
            <i class="fas fa-info-circle"></i> No se encontraron registros para los criterios seleccionados.
        </div>
    <?php else: ?>
        <div class="alert alert-secondary text-center mt-3">
            <i class="fas fa-search"></i> Seleccione el rango de fechas y presione "Generar Matriz PSNP" para generar el reporte.
        </div>
    <?php endif; ?>
</div>

<?php
$this->registerCss("
    .psnp-container { background-color: #f5f5f5; min-height: calc(100vh - 120px); padding: 20px; }
    .card-filter { background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
    .card-filter-body { padding: 20px; }
    .summary-card { background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 15px; text-align: center; margin-bottom: 20px; transition: all 0.2s; }
    .summary-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
    .summary-value { font-size: 28px; font-weight: bold; color: #2c3e50; }
    .summary-label { font-size: 13px; color: #7f8c8d; margin-top: 5px; }
    .summary-icon { font-size: 32px; margin-bottom: 10px; }
    .table-psnp { width: 100%; font-size: 13px; }
    table.table-psnp thead th { 
        background: #34495e !important; 
        color: #ffffff !important; 
        font-weight: 600; 
        white-space: nowrap; 
        padding: 10px 12px; 
        border: none; 
    }
    .table-psnp tbody td { padding: 8px 12px; border-bottom: 1px solid #ecf0f1; vertical-align: middle; }
    .table-psnp tbody tr:hover { background-color: #f8f9fa; }
    .filter-group { transition: all 0.2s ease; }
    .btn-xl { padding: 1rem 2rem; font-size: 1.5rem; border-radius: 0.5rem; }
    .btn-fluent-excel {
        background: #e3d7d7;
        border: 1px solid #107c41;
        color: #107c41;
        font-size: 13px;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 4px;
        transition: all 0.15s ease-in-out;
    }
    .btn-fluent-excel:hover {
        background-color: #107c41;
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(16, 124, 65, 0.2);
    }
    .btn-fluent-excel:active {
        background-color: #0b5930;
        border-color: #0b5930;
    }
    .card-filter { border: 1px solid #edebe9; box-shadow: 0 2px 8px rgba(0,0,0,0.05) !important; border-radius: 6px; overflow: hidden; }
");

$js = <<<JS
$(document).ready(function() {
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
    
    $('#btn-export-excel').on('click', function() {
        var form = document.getElementById('psnp-form');
        if (!form) return;
        
        var formData = new FormData(form);
        var url = '$exportExcelUrl';
        
        var submitForm = document.createElement('form');
        submitForm.method = 'POST';
        submitForm.action = url;
        submitForm.target = '_blank';
        
        var csrfToken = document.querySelector('meta[name=\"csrf-token\"]');
        if (csrfToken) {
            var csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_csrf';
            csrfInput.value = csrfToken.content;
            submitForm.appendChild(csrfInput);
        }
        
        for (var pair of formData.entries()) {
            var name = pair[0];
            var value = pair[1];
            if (value === '' || value === null || value === undefined) continue;
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            submitForm.appendChild(input);
        }
        
        document.body.appendChild(submitForm);
        submitForm.submit();
        
        setTimeout(function() {
            if (document.body.contains(submitForm)) {
                document.body.removeChild(submitForm);
            }
        }, 3000);
    });
});
JS;

$this->registerJs($js);
?>