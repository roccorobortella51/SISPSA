<?php

use yii\helpers\Html;
use yii\helpers\Json;
use kartik\form\ActiveForm;
use yii\widgets\MaskedInput;
use kartik\widgets\FileInput;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Pagos $model */
/** @var app\models\Corporativo $corporativo */
/** @var yii\widgets\ActiveForm $form */
/** @var array $allCuotas Array of all pending Cuotas across affiliates >0 */
/** @var float $grandTotal Total sum of pending cuotas >0 */
$grandTotal = $grandTotal ?? 0;

// Calculate cuota count
$cuotaCount = count($allCuotas);
$isParcialPayment = isset($isParcial) && $isParcial === true;

// ============================================================
// CHANGED: Group cuotas by coverage_start instead of fecha_vencimiento
// ============================================================
$affiliateGroups = [];
foreach ($allCuotas as $cuota) {
    $contrato = $cuota->contrato ?? null;
    $userDatos = $contrato->user ?? null;

    if ($userDatos) {
        $userId = $userDatos->id;
        $affiliateName = $userDatos->nombres . ' ' . $userDatos->apellidos;

        if (!isset($affiliateGroups[$userId])) {
            $affiliateGroups[$userId] = [
                'name' => $affiliateName,
                'cedula' => $userDatos->tipo_cedula . '-' . $userDatos->cedula,
                'cuotas' => [],
                'total' => 0,
                'contrato_id' => $contrato->id,
                'nrocontrato' => $contrato->nrocontrato,
            ];
        }

        $affiliateGroups[$userId]['cuotas'][] = $cuota;
        $affiliateGroups[$userId]['total'] += floatval($cuota->monto);
    }
}

// Sort affiliates alphabetically by name
uasort($affiliateGroups, function ($a, $b) {
    return strcmp($a['name'], $b['name']);
});

// Generar la URL absoluta para la acción del controlador
$urlTasaCambio = Url::to(['tasacambio-referencial']);

$disabled = isset($isEditable) && !$isEditable;

// Carga explícita del asset del DatePicker
\kartik\date\DatePickerAsset::register($this);

// Registrar variable global en HEAD
$this->registerJs('var grandTotal = ' . Json::encode($grandTotal) . ';', \yii\web\View::POS_HEAD);

// JavaScript
$js = <<<JS
// Función para buscar la tasa de cambio
function fetchTasaCambio(fecha) {
    if (!fecha) return;
    
    $.ajax({
        url: 'tasacambio-referencial',
        type: 'post',
        data: { fecha: fecha },
        success: function(response) {
            if (response && response !== '0') {
                var tasa = parseFloat(response);
                $('#pagos-tasa').val(tasa.toFixed(2));
                updateMontoBsReferencial();
            } else {
                console.warn('No se encontró tasa para la fecha: ' + fecha);
            }
        },
        error: function() {
            console.error('Error al buscar la tasa de cambio');
        }
    });
}

// Función para calcular Monto en Bs (Referencial)
function updateMontoBsReferencial() {
    var montoUsd = parseFloat($('#pagos-monto_pagado').val().replace(/[^0-9.]/g, '')) || 0; 
    var tasa = parseFloat($('#pagos-tasa').val()) || 0;
    var montoBs = 0;

    if (tasa > 0 && montoUsd > 0) {
        montoBs = montoUsd * tasa;
    }

    $('#monto-bs-display').val(montoBs.toFixed(4));
    $('#pagos-monto_bs').val(montoBs.toFixed(4));
}

// Función para validar Monto USD
function validateMontoUsd() {
    var montoUsd = parseFloat($('#pagos-monto_pagado').val().replace(/[^0-9.]/g, '')) || 0;
    
    if (montoUsd > grandTotal) {
        $('#pagos-monto_pagado').addClass('is-invalid');
        $('#monto-usd-error').text('El monto a pagar no puede superar el total seleccionado (' + grandTotal.toFixed(2) + ' USD).');
    } else if (Math.abs(montoUsd - grandTotal) > 0.01 && montoUsd > 0) {
        $('#pagos-monto_pagado').addClass('is-warning');
        $('#monto-usd-error').text('Advertencia: El monto ingresado ($' + montoUsd.toFixed(2) + ') es diferente al total seleccionado ($' + grandTotal.toFixed(2) + ').');
    } else {
        $('#pagos-monto_pagado').removeClass('is-invalid is-warning');
        $('#monto-usd-error').text('');
    }
}

$(document).ready(function() {
    // Toggle affiliate cuotas visibility
    $('.affiliate-group-header').on('click', function(e) {
        // Don't toggle if clicking on the checkbox area
        if ($(e.target).is(':checkbox') || $(e.target).closest('.checkbox-cell').length) {
            return;
        }
        
        // Find the detail row (the next tr with class 'affiliate-cuotas-detail')
        var targetRow = $(this).next('.affiliate-cuotas-detail');
        var icon = $(this).find('.toggle-icon');
        
        // Toggle the detail row visibility
        targetRow.toggle();
        
        // Rotate the icon
        if (targetRow.is(':visible')) {
            icon.addClass('expanded');
        } else {
            icon.removeClass('expanded');
        }
    });
    
    // Tasa de Cambio fetch al cambiar la Fecha de Pago
    $('#fecha-pago').on('change', function() {
        var fechaSeleccionada = $(this).val();
        fetchTasaCambio(fechaSeleccionada);
    });
    
    // Auto-fetch tasa when page loads
    if ($('#fecha-pago').val()) {
        $('#fecha-pago').trigger('change');
    } else {
        var today = new Date().toISOString().split('T')[0];
        $('#fecha-pago').val(today).trigger('change');
    }

    // Recalcular si la Tasa de Cambio es modificada manualmente
    $('#pagos-tasa').on('input', updateMontoBsReferencial);
    
    // Recalcular Monto Bs y Validar Monto USD cuando Monto Pagado cambia
    $('#pagos-monto_pagado').on('input', function() {
        validateMontoUsd();
        updateMontoBsReferencial();
    });

    // Disparar cálculos iniciales
    if ($('#fecha-pago').val()) {
        $('#fecha-pago').trigger('change');
    } else if ($('#pagos-monto_pagado').val() || $('#pagos-tasa').val()) {
        updateMontoBsReferencial();
        validateMontoUsd();
    }
    
    // Expand first affiliate by default for better UX
    $('.affiliate-group-header').first().trigger('click');
});
JS;
$this->registerJs($js);

// Add CSS for the improved table
$this->registerCss('
    .affiliate-group-header {
        background-color: #f3f2f1 !important;
        border-top: 2px solid #0078d4 !important;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    
    .affiliate-group-header:hover {
        background-color: #edebe9 !important;
    }
    
    .affiliate-group-header td {
        font-weight: 600 !important;
        padding: 12px 16px !important;
    }
    
    .affiliate-group-header .toggle-icon {
        font-size: 18px;
        margin-right: 10px;
        transition: transform 0.2s ease;
    }
    
    .affiliate-group-header .toggle-icon.expanded {
        transform: rotate(90deg);
    }
    
    .affiliate-cuotas-detail {
        background-color: #ffffff !important;
    }
    
    .affiliate-cuotas-detail td {
        padding: 0 !important;
    }
    
    .cuotas-detail-table {
        width: 100% !important;
        margin: 0 !important;
        background-color: #faf9f8 !important;
    }
    
    .cuotas-detail-table td {
        padding: 10px 16px !important;
        border-bottom: 1px solid #edebe9 !important;
    }
    
    .cuotas-detail-table tr:last-child td {
        border-bottom: none !important;
    }
    
    .cuotas-detail-table .sub-label {
        padding-left: 40px !important;
    }
    
    .month-badge {
        background-color: #e8f4fd !important;
        color: #0078d4 !important;
        padding: 4px 12px !important;
        border-radius: 16px !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        display: inline-block !important;
    }
    
    .coverage-badge {
        background-color: #e8f4fd !important;
        color: #107c10 !important;
        padding: 4px 12px !important;
        border-radius: 16px !important;
        font-size: 12px !important;
        font-weight: 500 !important;
        display: inline-block !important;
    }
    
    .affiliate-subtotal {
        background-color: #e8f4fd !important;
        font-weight: 700 !important;
    }
    
    .status-overdue {
        color: #d13438 !important;
        font-weight: 600 !important;
    }
    
    .status-grace {
        color: #ff8c00 !important;
        font-weight: 600 !important;
    }
    
    .status-pending {
        color: #107c10 !important;
        font-weight: 600 !important;
    }
    
    .is-warning {
        border-color: #ffc107 !important;
        background-color: #fff3cd !important;
    }
    
    .payment-summary-card {
        background: linear-gradient(135deg, #107c10 0%, #0e700e 100%) !important;
        border-radius: 8px !important;
        padding: 15px 20px !important;
        margin-bottom: 20px !important;
        color: white !important;
    }
    
    .payment-summary-card .stat-value {
        font-size: 28px !important;
        font-weight: 700 !important;
    }
    
    .payment-summary-card .stat-label {
        font-size: 13px !important;
        opacity: 0.9 !important;
    }
    
    .coverage-period-text {
        font-size: 11px;
        color: #605e5c;
        background-color: #f3f2f1;
        padding: 2px 8px;
        border-radius: 4px;
        display: inline-block;
        margin-top: 2px;
    }
');

$form = ActiveForm::begin([
    'id' => 'pago-corporativo-form',
    'options' => ['enctype' => 'multipart/form-data'],
    'type' => ActiveForm::TYPE_HORIZONTAL,
    'formConfig' => ['labelSpan' => 4, 'deviceSize' => ActiveForm::SIZE_SMALL],
]);
?>

<div class="row">
    <!-- SECCIÓN 1: Total a Pagar con Resumen -->
    <div class="col-md-12">
        <div class="payment-summary-card">
            <div class="row align-items-center">
                <div class="col-md-3 col-sm-6">
                    <div class="text-center">
                        <div class="stat-label">Total a Pagar</div>
                        <div class="stat-value"><?= Yii::$app->formatter->asCurrency($grandTotal, 'USD') ?></div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="text-center">
                        <div class="stat-label">Cuotas Seleccionadas</div>
                        <div class="stat-value"><?= $cuotaCount ?></div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="text-center">
                        <div class="stat-label">Afiliados Involucrados</div>
                        <div class="stat-value"><?= count($affiliateGroups) ?></div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="text-center">
                        <div class="stat-label">Meses de Cobertura</div>
                        <div class="stat-value">
                            <?php
                            // Count unique months from coverage_start
                            $uniqueMonths = [];
                            foreach ($allCuotas as $cuota) {
                                $coverageStart = $cuota->coverage_start ?: $cuota->fecha_vencimiento;
                                if ($coverageStart) {
                                    $date = new \DateTime($coverageStart);
                                    $uniqueMonths[$date->format('Y-m')] = true;
                                }
                            }
                            echo count($uniqueMonths);
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 2: Detalle de Cuotas a Pagar (Agrupado por Afiliado) -->
    <div class="col-md-12">
        <div class="card shadow-sm border-info mb-4">
            <div class="card-header bg-gradient-blue-2 text-white">
                <h3 class="card-title mb-0" style="font-size: 1.6rem !important;">
                    <i class="fas fa-list-ul me-2"></i> Detalle de Cuotas a Pagar
                    <span class="badge badge-light float-end" style="font-size: 1.2rem; color: #323130 !important;"><?= $cuotaCount ?> cuotas</span>
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="bg-dark">
                            <tr class="text-white">
                                <th style="width: 40px;"></th>
                                <th style="width: 60px;">#</th>
                                <th>Afiliado</th>
                                <th>Cédula</th>
                                <th>Contrato</th>
                                <th class="text-end">Total a Pagar</th>
                                <th class="text-center">Cuotas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $affiliateNumber = 1;
                            foreach ($affiliateGroups as $affiliateId => $group):
                                $cuotasList = $group['cuotas'];
                                // Sort cuotas by coverage_start (oldest first)
                                usort($cuotasList, function ($a, $b) {
                                    $coverageA = $a->coverage_start ?: $a->fecha_vencimiento;
                                    $coverageB = $b->coverage_start ?: $b->fecha_vencimiento;
                                    return strtotime($coverageA) - strtotime($coverageB);
                                });
                            ?>
                                <!-- Affiliate Summary Row -->
                                <tr class="affiliate-group-header">
                                    <td class="text-center">
                                        <i class="fas fa-chevron-right toggle-icon"></i>
                                    </td>
                                    <td class="text-center">
                                        <strong><?= $affiliateNumber++ ?></strong>
                                    </td>
                                    <td>
                                        <strong><?= Html::encode($group['name']) ?></strong>
                                    </td>
                                    <td><?= Html::encode($group['cedula']) ?></td>
                                    <td><?= Html::encode($group['nrocontrato']) ?></td>
                                    <td class="text-end">
                                        <strong class="text-danger"><?= Yii::$app->formatter->asCurrency($group['total'], 'USD') ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-primary"><?= count($cuotasList) ?> cuota(s)</span>
                                    </td>
                                </tr>

                                <!-- Affiliate Cuotas Detail Row (hidden by default) -->
                                <tr class="affiliate-cuotas-detail" style="display: none;">
                                    <td colspan="7" class="p-0">
                                        <table class="table table-sm cuotas-detail-table mb-0">
                                            <thead>
                                                <tr style="background-color: #f3f2f1;">
                                                    <th style="width: 90px;"></th>
                                                    <th style="width: 80px; text-align: center;">Cuota #</th>
                                                    <th>Mes / Período de Cobertura</th>
                                                    <th>ID Cuota</th>
                                                    <th class="text-end">Monto USD</th>
                                                    <th>Vencimiento</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $cuotaNumber = 1;
                                                foreach ($cuotasList as $cuota):
                                                    // ============================================================
                                                    // CHANGED: Use coverage_start for display
                                                    // ============================================================
                                                    $coverageStart = $cuota->coverage_start ?: $cuota->fecha_vencimiento;
                                                    $coverageDate = new \DateTime($coverageStart);
                                                    $monthName = $coverageDate->format('F Y');

                                                    $coverageText = '';
                                                    if ($cuota->coverage_start && $cuota->coverage_end) {
                                                        $start = new \DateTime($cuota->coverage_start);
                                                        $end = new \DateTime($cuota->coverage_end);
                                                        $coverageText = $start->format('d/m') . ' - ' . $end->format('d/m/Y');
                                                    }

                                                    // Determine status class
                                                    $statusClass = 'status-pending';
                                                    $statusText = 'Pendiente';
                                                    if ($cuota->estatus === 'vencida') {
                                                        $statusClass = 'status-overdue';
                                                        $statusText = 'Vencida';
                                                    } elseif ($cuota->estatus === 'en_gracias') {
                                                        $statusClass = 'status-grace';
                                                        $statusText = 'En Gracia';
                                                    }
                                                ?>
                                                    <tr>
                                                        <td class="sub-label">
                                                            <small class="text-muted">
                                                                <i class="fas fa-receipt"></i>
                                                            </small>
                                                        </td>
                                                        <td class="text-center">
                                                            <strong><?= $cuotaNumber++ ?></strong>
                                                        </td>
                                                        <td>
                                                            <span class="month-badge">
                                                                <i class="far fa-calendar-alt me-1"></i>
                                                                <?= $monthName ?>
                                                            </span>
                                                            <?php if ($coverageText): ?>
                                                                <br>
                                                                <span class="coverage-badge">
                                                                    <i class="fas fa-calendar-check me-1"></i>
                                                                    Cobertura: <?= $coverageText ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <small>#<?= $cuota->id ?></small>
                                                        </td>
                                                        <td class="text-end">
                                                            <strong><?= Yii::$app->formatter->asCurrency($cuota->monto, 'USD') ?></strong>
                                                        </td>
                                                        <td>
                                                            <?= Yii::$app->formatter->asDate($cuota->fecha_vencimiento, 'php:d/m/Y') ?>
                                                            <?php
                                                            $today = new \DateTime();
                                                            $dueDate = new \DateTime($cuota->fecha_vencimiento);
                                                            if ($dueDate < $today && $cuota->estatus !== 'pagada') {
                                                                $daysOverdue = $today->diff($dueDate)->days;
                                                                echo '<br><small class="status-overdue">(' . $daysOverdue . ' días vencida)</small>';
                                                            } elseif ($dueDate >= $today) {
                                                                $daysLeft = $today->diff($dueDate)->days;
                                                                echo '<br><small class="text-muted">(Faltan ' . $daysLeft . ' días)</small>';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <span class="<?= $statusClass ?>"><?= $statusText ?></span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr class="affiliate-subtotal">
                                                    <td colspan="4" class="text-end">
                                                        <strong>Subtotal <?= Html::encode($group['name']) ?>:</strong>
                                                    </td>
                                                    <td class="text-end">
                                                        <strong><?= Yii::$app->formatter->asCurrency($group['total'], 'USD') ?></strong>
                                                    </td>
                                                    <td colspan="2"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-dark">
                                <td colspan="5" class="text-end"><strong>TOTAL GENERAL:</strong></td>
                                <td class="text-end"><strong><?= Yii::$app->formatter->asCurrency($grandTotal, 'USD') ?></strong></td>
                                <td class="text-center"><strong><?= $cuotaCount ?> cuotas</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 3: Registro del Pago Corporativo -->
    <div class="col-md-12">
        <div class="card shadow-sm border-success mb-4">
            <div class="card-header bg-gradient-green text-white">
                <h1 class="card-title mb-0" style="font-size: 1.8rem !important;">
                    <i class="fas fa-credit-card me-2"></i> Registro del Pago Corporativo
                </h1>
            </div>
            <div class="card-body">
                <div class="row mb-3 pb-2 border-bottom">
                    <div class="col-md-4">
                        <?= $form->field($model, 'metodo_pago')->dropDownList([
                            'deposito' => 'Depósito Bancario',
                            'efectivo' => 'Efectivo',
                            'otro' => 'Otro Método',
                            'pago-movil' => 'Pago Móvil',
                            'punto-venta' => 'Punto de Venta',
                            'transferencia' => 'Transferencia Bancaria',
                            'zelle' => 'Zelle / Transferencia Internacional',
                        ], ['prompt' => 'Seleccione el Tipo de Pago'])->label('Método Pago') ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'fecha_pago')->textInput([
                            'class' => 'form-control',
                            'type' => 'date',
                            'placeholder' => 'Seleccione la fecha del pago',
                            'disabled' => $disabled,
                            'id' => 'fecha-pago',
                        ])->label('Fecha Pago') ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'numero_referencia_pago')->textInput(['maxlength' => true, 'placeholder' => 'Nro. de Referencia/Comprobante'])->label('# Referencia') ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <?= $form->field($model, 'monto_pagado')->widget(MaskedInput::class, [
                            'clientOptions' => [
                                'alias' => 'decimal',
                                'groupSeparator' => ',',
                                'autoGroup' => true,
                                'removeMaskOnSubmit' => true,
                                'digits' => 2,
                            ],
                            'options' => [
                                'id' => 'pagos-monto_pagado',
                                'placeholder' => 'Monto total en USD',
                                'value' => number_format($grandTotal, 2, '.', ''),
                            ],
                        ])->textInput(['maxlength' => true])->label('Monto Pagado (USD)') ?>
                        <div id="monto-usd-error" class="invalid-feedback d-block"></div>
                        <small class="text-muted">
                            <i class="fas fa-calculator"></i>
                            Total seleccionado: <?= Yii::$app->formatter->asCurrency($grandTotal, 'USD') ?>
                        </small>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'tasa')->textInput([
                            'id' => 'pagos-tasa',
                            'placeholder' => 'Tasa de Referencia',
                        ])->label('Tasa BCV') ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'monto_usd', [
                            'horizontalCssClasses' => [
                                'label' => 'col-sm-4 control-label',
                                'wrapper' => 'col-sm-8',
                            ],
                            'template' => "{label}\n<div class='col-sm-8'>{input}\n{hint}\n{error}</div>",
                        ])->textInput([
                            'id' => 'monto-bs-display',
                            'class' => 'form-control',
                            'readonly' => true,
                            'placeholder' => 'Monto calculado en Bs',
                        ])->label('Monto Bs (Referencial)') ?>

                        <?= $form->field($model, 'monto_usd')->hiddenInput(['id' => 'pagos-monto_bs'])->label(false) ?>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <?= $form->field($model, 'observacion')->textarea(['rows' => 3, 'readonly' => $disabled]) ?>
                    </div>
                </div>

                <!-- Sección de Adjuntar Comprobante -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card shadow-sm border-secondary">
                            <div class="card-header bg-secondary text-white text-center">
                                <h4 class="card-title mb-0" style="font-size: 1.5rem !important;">
                                    <i class="fas fa-paperclip me-2"></i> Adjuntar Comprobante (JPG, PNG)
                                </h4>
                            </div>
                            <div class="card-body">
                                <?= $form->field($model, 'imagen_prueba_file')->widget(FileInput::class, [
                                    'options' => ['accept' => 'image/*'],
                                    'pluginOptions' => [
                                        'showUpload' => false,
                                        'showRemove' => false,
                                        'maxFileSize' => 2048,
                                        'msgSizeTooLarge' => 'El archivo "{name}" ({sizeText}) excede el tamaño máximo permitido de {maxSize}.',
                                        'initialPreview' => [],
                                        'initialPreviewAsData' => true,
                                        'initialPreviewConfig' => [],
                                        'overwriteInitial' => true,
                                        'layoutTemplates' => [
                                            'main1' => '{preview}{browse}{remove}',
                                            'main2' => '{preview}{browse}{remove}',
                                            'footer' => '<div class="file-thumbnail-footer">\n{progress} {actions}\n</div>',
                                        ],
                                        'previewSettings' => [
                                            'image' => ['width' => '100%', 'height' => 'auto', 'max-width' => '250px'],
                                        ],
                                        'purifyHtml' => true,
                                    ],
                                ])->label(false) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="form-group mt-4 d-flex justify-content-center gap-3 flex-wrap">
    <?php if (!empty($allCuotas) && $grandTotal > 0): ?>
        <?= Html::submitButton('<i class="fas fa-save me-2"></i> Guardar Pago Corporativo', [
            'class' => 'btn btn-success btn-lg rounded-pill px-7 shadow-sm text-white',
            'style' => 'color: white !important; font-size: 1.3rem !important; padding: 0.875rem 2.5rem !important;'
        ]) ?>
    <?php endif; ?>
    <?= Html::a('<i class="fas fa-undo me-2"></i> Volver', ['deuda', 'id' => $corporativo->id], [
        'class' => 'btn btn-secondary btn-lg rounded-pill px-7 shadow-sm text-white',
        'style' => 'color: white !important; font-size: 1.3rem !important; padding: 0.875rem 2.5rem !important;'
    ]) ?>
</div>

<?php ActiveForm::end(); ?>