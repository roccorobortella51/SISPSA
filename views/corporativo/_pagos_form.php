<?php

use yii\helpers\Html;
use yii\helpers\Json;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use app\components\UserHelper;
use kartik\widgets\SwitchInput;
use kartik\widgets\DatePicker;
use kartik\widgets\FileInput;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Pagos $model */
/** @var app\models\Corporativo $corporativo */
/** @var yii\widgets\ActiveForm $form */
/** @var array $allCuotas Array of all pending Cuotas across affiliates >0 */
/** @var float $grandTotal Total sum of pending cuotas >0 */
$grandTotal = $grandTotal ?? 0;
/** Assume $model is Pagos model, $afiliados loaded in controller via CorporativoUser */

// Registrar variable global en HEAD para evitar problemas con heredoc
$this->registerJs('var grandTotal = ' . Json::encode($grandTotal) . ';', \yii\web\View::POS_HEAD);

$js = <<<'JS'
$(document).ready(function() {
    // Función para buscar la tasa de cambio
    $('#fecha-pago').on('change', function() {
        var fechaSeleccionada = $(this).val();
        
        if (fechaSeleccionada) {
            $.ajax({
                url: '../site/tasacambio',
                type: 'post',
                data: { fecha: fechaSeleccionada },
                success: function(response) {
                    if (response) {
                        $('#pagos-tasa').val(response);
                        updateMontoUsd();
                    } else {
                        $('#pagos-tasa').val('');
                        alert('No se encontró tasa para esta fecha');
                    }
                },
                error: function() {
                    $('#pagos-tasa').val('');
                    alert('Error al buscar la tasa');
                }
            });
        }
    });

    // Función para actualizar el monto en USD (Bs)
    function updateMontoUsd() {
        var monto_pagado = parseFloat($('#pagos-monto_pagado').val()) || 0;
        var tasa = parseFloat($('#pagos-tasa').val()) || 0;
        var metodo_pago = $('#pagos-metodo_pago').val();
        
        var monto_usd_calculated = 0;
        if (metodo_pago === 'Zelle') {
            monto_usd_calculated = monto_pagado;
        } else {
            monto_usd_calculated = monto_pagado * tasa;
        }
        $('#pagos-monto_usd').val(monto_usd_calculated.toFixed(2));
    }

    // Función para el checkbox total (simple toggle)
    function updateMontoSelected() {
        if ($('#pagar-total').is(':checked')) {
            $('#pagos-monto_pagado').val((grandTotal > 0 ? grandTotal.toFixed(2) : '0.00'));
        } else {
            $('#pagos-monto_pagado').val('0.00');
        }
        updateMontoUsd();
    }

    // Listener para cambio en monto pagado o tasa
    $('#pagos-monto_pagado, #pagos-tasa').on('change keyup', function(){
        updateMontoUsd();
    });

    // Listener para el checkbox total
    $(document).on('change', '#pagar-total', function() {
        updateMontoSelected();
    });

    // Al cargar la página, inicializar el monto según checkboxes
    updateMontoSelected();

    // Listener para cambio en método de pago
    $('#pagos-metodo_pago').on('change', function(){
        if ($(this).val() == 'Zelle'){
            $('.field-pagos-tasa').hide();
        } else {
            $('.field-pagos-tasa').show();
        }
        updateMontoUsd();
    });

    // Asegurarse de que el estado inicial sea correcto
    $('#pagos-metodo_pago').trigger('change');
});
JS;
$this->registerJs($js);

?>
<style>
    .file-input .file-caption { width: 100% !important; margin: 0 auto; box-sizing: border-box; }
    .pagos-form .form-group { margin-bottom: 1rem; }
    .pagos-form .form-label { font-weight: bold; color: #007bff; }
    .pagos-form .form-control.rounded-pill { border-radius: 50rem !important; }
    .file-preview { max-width: 200px; margin: 0 auto; }
    .file-input .btn-file { background-color: white !important; color: #333 !important; border: 1px solid #ced4da !important; }
    .file-input .btn-file i, .file-input .btn-file span { color: #333 !important; }
    .affiliate-section { margin-bottom: 2rem; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 1rem; }
    .affiliate-header { background-color: #f8f9fa; padding: 0.5rem; border-radius: 0.375rem; margin-bottom: 1rem; }
</style>

<div class="pagos-form p-4 rounded-3 shadow-sm bg-light">

    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data'],
        'type' => ActiveForm::TYPE_VERTICAL,
        'formConfig' => [
            'labelSpan' => 12,
            'deviceSize' => ActiveForm::SIZE_MEDIUM,
        ],
    ]); ?>

    <h4 class="mb-4 text-info border-bottom pb-2"><i class="fas fa-credit-card me-2"></i> Información del Pago Corporativo</h4>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'metodo_pago')->widget(Select2::classname(), [
                'data' => [
                    'Pago Movil' => 'Pago Movil',
                    'Transferencia Bancaria' => 'Transferencia Bancaria',
                    'Zelle' => 'Zelle'
                ],
                'options' => [
                    'placeholder' => 'Seleccione el método de pago...',
                    'class' => 'form-control rounded-pill',
                ],
                'pluginOptions' => [
                    'allowClear' => false,
                ],
            ])->label('Método de Pago') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'fecha_pago')->textInput([
                'class' => 'form-control rounded-pill',
                'type' => 'date',
                'placeholder' => 'Seleccione la fecha del pago',
                'id' => 'fecha-pago',
            ])->label('Fecha de Pago') ?>
        </div>
    </div>

    <h5 class="mb-3">Cuotas Pendientes Totales</h5>
    <?php if ($grandTotal > 0): ?>
        <ul class="list-group">
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <?= Html::checkbox('pagar_total', true, [
                        'id' => 'pagar-total',
                        'class' => 'form-check-input',
                        'checked' => true,
                        'data-monto' => $grandTotal
                    ]) ?>
                    <label for="pagar-total" style="margin:0;">
                        <strong style="font-size: 1.5rem !important;">Cuota Total Pendiente</strong>
                        <div style="font-size: 1.1rem !important;">Total de todas las cuotas pendientes (<?= count($allCuotas) ?> cuotas)</div>
                    </label>
                </div>
                <span class="badge bg-primary rounded-pill" style="font-size: 1.5rem !important;">$<?= number_format($grandTotal, 2) ?></span>
            </li>
        </ul>
        <?php // Ocultar lista detallada o colapsar si quieres mostrarla opcionalmente ?>
        <div class="alert alert-info mt-2">
            <strong>Nota:</strong> Se pagará el total pendiente de todas las cuotas. Si deseas pagar parcial, contacta al administrador.
        </div>
    <?php else: ?>
        <div class="alert alert-warning rounded-pill">
            <strong style="font-size: 1.5rem !important;">No hay cuotas pendientes mayores a cero para este corporativo.</strong>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'monto_pagado')->textInput([
                'class' => 'form-control rounded-pill',
                'value' => $grandTotal ? number_format($grandTotal, 2) : '0.00',
                'placeholder' => 'Monto total a pagar (calculado de selecciones)',
                'readonly' => true, // Auto-calculated from selections
            ])->label('Monto Total a Pagar en USD') ?>
        </div>
        <div class="col-md-4 field-pagos-tasa">
            <?= $form->field($model, 'tasa')->textInput([
                'class' => 'form-control rounded-pill',
                'type' => 'number',
                'step' => '0.0001',
                'placeholder' => 'Ingrese la tasa de cambio',
                'id' => 'pagos-tasa',
            ])->label('Tasa de Cambio USD a Bs(BCV)') ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'monto_usd')->textInput([
                'class' => 'form-control rounded-pill',
                'readonly' => true,
                'placeholder' => 'Monto en Bs (calculado)',
            ])->label('Monto Total en Bs') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'numero_referencia_pago')->textInput([
                'class' => 'form-control rounded-pill',
                'type' => 'text',
                'placeholder' => 'Ingrese el número de referencia del pago',
            ])->label('Número de Referencia') ?>
        </div>
    </div>

    <h4 class="mt-4 mb-4 text-info border-bottom pb-2"><i class="fas fa-file-upload me-2"></i> Comprobante de Pago</h4>

    <div class="row">
        <div class="col-md-12">
            <?php
            $initialPreview = [];
            $initialPreviewConfig = [];
            if (!$model->isNewRecord && $model->imagen_prueba) {
                $initialPreview[] = Url::to($model->imagen_prueba, true);
                $initialPreviewConfig[] = [
                    'caption' => basename($model->imagen_prueba),
                    'key' => 1,
                ];
            }
            ?>
            <?= $form->field($model, 'imagen_prueba_file')->widget(FileInput::classname(), [
                'name' => 'imagen_prueba_file',
                'options' => ['accept' => 'image/*'],
                'pluginOptions' => [
                    'theme' => 'fa5',
                    'browseClass' => 'btn btn-light rounded-pill px-3 shadow-sm text-dark',
                    'removeClass' => 'btn btn-outline-danger rounded-pill px-3 shadow-sm',
                    'uploadClass' => 'btn btn-info rounded-pill px-3 shadow-sm',
                    'removeIcon' => '<i class="fas fa-trash"></i> ',
                    'showUpload' => false,
                    'showCancel' => false,
                    'previewFileType' => 'image',
                    'maxFileSize' => 5600,
                    'msgSizeTooLarge' => 'El archivo "{name}" ({size} KB) excede el tamaño máximo permitido de {maxSize} KB. Por favor, suba una imagen más pequeña.',
                    'initialPreview' => $initialPreview,
                    'initialPreviewAsData' => true,
                    'initialPreviewConfig' => $initialPreviewConfig,
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
            ])->label('Adjuntar Comprobante (JPG, PNG)') ?>
        </div>
    </div>

    <div class="form-group mt-4 d-flex justify-content-center gap-3">
        <?php if (!empty($allCuotas) && $grandTotal > 0): ?>
            <?= Html::submitButton('<i class="fas fa-save me-2"></i> Guardar Pago Corporativo', [
                'class' => 'btn btn-success btn-lg rounded-pill px-7 shadow-sm'
            ]) ?>
        <?php endif; ?>
        <?= Html::a('<i class="fas fa-undo me-2"></i> Volver', ['contracts', 'id' => $corporativo->id], [
            'class' => 'btn btn-secondary btn-lg rounded-pill px-7 shadow-sm'
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>