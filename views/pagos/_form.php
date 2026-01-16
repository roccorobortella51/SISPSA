<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use app\components\UserHelper;
use kartik\widgets\SwitchInput;
use kartik\widgets\DatePicker;
use kartik\widgets\FileInput;
use yii\helpers\Url;
use yii\web\JsExpression;

/** @var yii\web\View $this */
/** @var app\models\Pagos $model */
/** @var yii\widgets\ActiveForm $form */

$user_id = isset($user_id) ? $user_id : ($model->user_id ?? null);

// Add debug info at the bottom of the form
echo '<div style="display: none; background: #f0f0f0; padding: 10px; margin-top: 20px;">';
echo '<h4>Debug Info:</h4>';
echo 'Model isNewRecord: ' . ($model->isNewRecord ? 'Yes' : 'No') . '<br>';
echo 'Model scenario: ' . $model->scenario . '<br>';
echo 'User ID: ' . $user_id . '<br>';
echo 'Cuotas count: ' . (is_array($cuotas) ? count($cuotas) : 0) . '<br>';
echo '</div>';

// Main JavaScript for form functionality - BULLETPROOF VERSION
$this->registerJs(
    <<<'JS'
$(function() {
    console.log('Pagos form JavaScript loaded');
    
    // Function to update Bs amount
    function updateMontoBs() {
        var montoUsd = parseFloat($('#pagos-monto_pagado').val()) || 0;
        var tasa = parseFloat($('#pagos-tasa').val()) || 0;
        var montoBs = montoUsd * tasa;
        $('#pagos-monto_usd').val(montoBs.toFixed(4));
    }
    
    // Function to update field visibility
    function updateFieldsVisibility() {
        var metodo = $('#pagos-metodo_pago').val();
        var isCashDollar = (metodo === 'Efectivo - Dólar ($)');
        
        if (isCashDollar) {
            // Hide fields for cash dollar
            $('.field-pagos-numero_referencia_pago').hide();
            $('.field-pagos-imagen_prueba_file').hide();
            $('#comprobante-title').hide();
            
            // Clear the values so they don't fail validation
            $('#pagos-numero_referencia_pago').val('');
            $('#pagos-imagen_prueba_file').val('');
        } else {
            // Show fields for other payment methods
            $('.field-pagos-numero_referencia_pago').show();
            $('.field-pagos-imagen_prueba_file').show();
            $('#comprobante-title').show();
        }
    }
    
    // Calculate selected cuotas total
    function updateMontoSelected() {
        var sum = 0;
        $('.cuota-checkbox:checked').each(function() {
            sum += parseFloat($(this).data('monto')) || 0;
        });
        $('#pagos-monto_pagado').val(sum.toFixed(2));
        updateMontoBs();
    }
    
    // Event handlers
    $('#fecha-pago').on('change', function() {
        var fecha = $(this).val();
        if (fecha) {
            $.ajax({
                url: '../site/tasacambio',
                type: 'post',
                data: { fecha: fecha },
                success: function(response) {
                    if (response) {
                        $('#pagos-tasa').val(parseFloat(response).toFixed(2));
                        updateMontoBs();
                    }
                }
            });
        }
    });
    
    $('#pagos-monto_pagado, #pagos-tasa').on('change keyup', updateMontoBs);
    $('#pagos-metodo_pago').on('change', updateFieldsVisibility);
    $(document).on('change', '.cuota-checkbox', updateMontoSelected);
    
    // Initialize
    updateMontoSelected();
    updateFieldsVisibility();
    updateMontoBs();
    
    // Disable form submit button while processing to prevent double submission
    $('#pago-form').on('submit', function() {
        var submitBtn = $('#submit-btn');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Guardando...');
        console.log('Form submitted');
    });
});
JS
);

?>


?>
<style>
    /* Estilo para centrar el caption del FileInput */
    .file-input .file-caption {
        width: 100% !important;
        /* Ajustar al 100% dentro de su columna */
        margin: 0 auto;
        box-sizing: border-box;
    }

    /* Estilo adicional para los campos de formulario */
    .pagos-form .form-group {
        margin-bottom: 1rem;
        /* Espacio entre campos */
    }

    .pagos-form .form-label {
        font-weight: bold;
        color: #007bff;
        /* Color de texto para las etiquetas */
    }

    .pagos-form .form-control.rounded-pill {
        border-radius: 50rem !important;
        /* Bordes más redondeados para inputs */
    }

    .file-preview {
        max-width: 200px;
        /* Tamaño máximo para la vista previa de la imagen */
        margin: 0 auto;
    }

    /* Regla CSS para forzar el botón "Examinar" a ser blanco */
    /* Apunta a la clase `btn-file` que Kartik FileInput usa para el botón "Examinar" */
    .file-input .btn-file {
        background-color: white !important;
        /* Fondo blanco forzado */
        color: #333 !important;
        /* Texto oscuro para contraste */
        border: 1px solid #ced4da !important;
        /* Borde sutil */
    }

    /* Asegurar que los íconos y texto dentro del botón "Examinar" también tengan el color oscuro */
    .file-input .btn-file i,
    .file-input .btn-file span {
        color: #333 !important;
    }

    /* ADDED: Custom CSS for larger error messages */
    .field-pagos-imagen_prueba_file .help-block {
        font-size: 1.8rem !important;
        /* Larger font size for error message */
        font-weight: bold !important;
        color: #dc3545 !important;
        /* Red color for error */
        margin-top: 10px !important;
        padding: 10px !important;
        background-color: #ffe6e6 !important;
        /* Light red background */
        border-radius: 8px !important;
        border-left: 4px solid #dc3545 !important;
    }

    .field-pagos-numero_referencia_pago .help-block {
        font-size: 1.8rem !important;
        /* Larger font size for error message */
        font-weight: bold !important;
        color: #dc3545 !important;
        /* Red color for error */
        margin-top: 10px !important;
        padding: 10px !important;
        background-color: #ffe6e6 !important;
        /* Light red background */
        border-radius: 8px !important;
        border-left: 4px solid #dc3545 !important;
    }

    /* Style for the required field indicator */
    .required-field::after {
        content: " *";
        color: #dc3545;
        font-size: 1.5rem;
    }

    /* BULLET-PROOF SELECT2 STYLES - SIMPLIFIED */
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #5897fb !important;
        color: white !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] * {
        color: white !important;
    }

    /* Force white on hover for all children */
    .select2-container--default .select2-results__option--highlighted[aria-selected],
    .select2-container--default .select2-results__option--highlighted[aria-selected] i,
    .select2-container--default .select2-results__option--highlighted[aria-selected] span,
    .select2-container--default .select2-results__option--highlighted[aria-selected] span i {
        color: white !important;
    }

    /* Normal state for dollar icon */
    .select2-results__option i.fas.fa-dollar-sign {
        color: #28a745;
        margin-right: 8px;
    }

    /* Style for the selected option with dollar icon (in the input field) */
    .select2-selection__rendered i.fas.fa-dollar-sign {
        color: #28a745;
        margin-right: 8px;
    }

    /* Style for disabled submit button */
    button[type="submit"]:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Loading spinner animation */
    .fa-spinner {
        animation: fa-spin 1s infinite linear;
    }

    @keyframes fa-spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
</style>

<div class="pagos-form p-4 rounded-3 shadow-sm bg-light">

    <?php $form = ActiveForm::begin([
        'options' => [
            'enctype' => 'multipart/form-data',
            'id' => 'pago-form',
        ],
        'type' => ActiveForm::TYPE_VERTICAL,
        'formConfig' => [
            'labelSpan' => 12,
            'deviceSize' => ActiveForm::SIZE_MEDIUM,
        ],
        'fieldConfig' => [
            'errorOptions' => [
                'class' => 'text-danger',
                'style' => 'font-size: 1.8rem; font-weight: bold; padding: 10px;',
            ],
        ],
        // BULLETPROOF: Disable client-side validation to prevent blocking submission
        'enableClientValidation' => false,
        'enableAjaxValidation' => false,
        'validateOnSubmit' => false,
    ]); ?>

    <?php
    // Determinar si el formulario debe estar deshabilitado
    $disabled = isset($isEditable) && !$isEditable;
    ?>

    <h4 class="mb-4 text-info border-bottom pb-2"><i class="fas fa-credit-card me-2"></i> Información del Pago</h4>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'metodo_pago')->widget(Select2::classname(), [
                'data' => [
                    'Efectivo - Dólar ($)' => 'Efectivo - Dólar ($)',
                    'Pago Móvil' => 'Pago Móvil',
                    'Punto de Venta' => 'Punto de Venta',
                    'Transferencia Bancaria' => 'Transferencia Bancaria',
                    'Zelle' => 'Zelle'
                ],
                'options' => [
                    'placeholder' => 'Seleccione el método de pago...',
                    'class' => 'form-control rounded-pill',
                    'disabled' => $disabled,
                ],
                'pluginOptions' => [
                    'allowClear' => false,
                    'templateResult' => new JsExpression('
                        function(data) {
                            if (!data.id) {
                                return data.text;
                            }
                            var span = $("<span></span>");
                            if (data.id === "Efectivo - Dólar ($)") {
                                span.append("<i class=\'fas fa-dollar-sign text-success mr-2\'></i>");
                            }
                            span.append(document.createTextNode(data.text));
                            return span;
                        }
                    '),
                    'templateSelection' => new JsExpression('
                        function(data) {
                            if (!data.id) {
                                return data.text;
                            }
                            var span = $("<span></span>");
                            if (data.id === "Efectivo - Dólar ($)") {
                                span.append("<i class=\'fas fa-dollar-sign text-success mr-2\'></i>");
                            }
                            span.append(document.createTextNode(data.text));
                            return span;
                        }
                    '),
                    'escapeMarkup' => new JsExpression('function(markup) { return markup; }'),
                ],
            ])->label('Método de Pago' . '<span class="required-field"></span>') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'fecha_pago')->textInput([
                'class' => 'form-control rounded-pill', // Aplicar estilo redondeado
                'type' => 'date',
                'placeholder' => 'Seleccione la fecha del pago',
                'disabled' => $disabled,
                'id' => 'fecha-pago', // ID único
            ])->label('Fecha de Pago' . '<span class="required-field"></span>') ?>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-12">
            <h5 class="form-label has-star" style="font-size: 1.5rem !important;">Cuotas Pendientes</h5>
            <?php
            // DEBUG: Check what we're receiving
            Yii::info("=== FORM DEBUG ===");
            Yii::info("Cuotas variable type: " . gettype($cuotas));
            Yii::info("Cuotas count: " . (is_array($cuotas) ? count($cuotas) : 'N/A'));
            Yii::info("Cuotas empty check: " . (empty($cuotas) ? 'true' : 'false'));
            if (is_array($cuotas) || is_object($cuotas)) {
                foreach ($cuotas as $index => $cuota) {
                    Yii::info("Cuota $index: " . print_r($cuota, true));
                }
            }
            Yii::info("=== END DEBUG ===");

            $total = 0;
            $i = 0;
            if (!empty($cuotas)):
            ?>
                <ul class="list-group">
                    <?php foreach ($cuotas as $cuota): ?>
                        <?php
                        $i++;
                        // Use monto_usd if available, otherwise fall back to monto
                        $monto = !empty($cuota->monto_usd) ? $cuota->monto_usd : $cuota->monto;
                        $total += (float)$monto;
                        ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <?= Html::checkbox('selected_cuotas[]', false, [
                                    'value' => $cuota->id,
                                    'id' => 'cuota-' . $cuota->id,
                                    'class' => 'cuota-checkbox mr-4',
                                    'data-monto' => $monto
                                ]) ?>
                                <label for="cuota-<?= $cuota->id ?>" style="margin:0;">
                                    <strong style="font-size: 1.5rem !important;">Cuota #<?= $i ?></strong>
                                    <div style="font-size: 1.4rem !important;">
                                        Vence: <?= Yii::$app->formatter->asDate($cuota->fecha_vencimiento) ?>
                                        <?php if ($cuota->contrato): ?>
                                            | Contrato: <?= $cuota->contrato->nrocontrato ?: $cuota->contrato_id ?>
                                        <?php endif; ?>
                                    </div>
                                </label>
                            </div>
                            <span class="badge bg-primary rounded-pill" style="font-size: 1.5rem !important;">
                                $<?= number_format($monto, 2) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                    <li class="list-group-item list-group-item-primary d-flex justify-content-between align-items-center">
                        <strong style="font-size: 1.6rem !important;">TOTAL PENDIENTE:</strong>
                        <strong style="font-size: 1.6rem !important;">$<?= number_format($total, 2) ?></strong>
                    </li>
                </ul>
            <?php else: ?>
                <div class="alert alert-success rounded-pill">
                    <strong style="font-size: 1.5rem !important;">✅ No hay cuotas pendientes</strong>
                    <?php if ($user_id): ?>
                        <div class="mt-2">
                            <small>Usuario ID: <?= $user_id ?></small>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'monto_pagado')->textInput([
                'class' => 'form-control rounded-pill', // Aplicar estilo redondeado
                'placeholder' => 'Ingrese el monto pagado',
                'id' => 'pagos-monto_pagado',
            ])->label('Monto a Pagar en USD' . '<span class="required-field"></span>') ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'tasa')->textInput([
                'class' => 'form-control rounded-pill', // Aplicar estilo redondeado
                'type' => 'number',
                'step' => '0.0001', // Para tasas con más decimales
                'placeholder' => 'Ingrese la tasa de cambio',
                'id' => 'pagos-tasa', // ID único
            ])->label('Tasa de Cambio USD a Bs(BCV)' . '<span class="required-field"></span>') ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'monto_usd')->textInput([
                'class' => 'form-control rounded-pill', // Aplicar estilo redondeado
                'readonly' => true, // Siempre solo lectura
                'placeholder' => 'Monto en Bs (calculado)',
                'id' => 'pagos-monto_usd',
            ])->label('Monto en Bs' . '<span class="required-field"></span>') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'numero_referencia_pago')->textInput([
                'class' => 'form-control rounded-pill', // Aplicar estilo redondeado
                'type' => 'text',
                'placeholder' => 'Ingrese el número de referencia del pago',
                'disabled' => $disabled,
                'id' => 'pagos-numero_referencia_pago',
            ])->label('Número de Referencia' . '<span class="required-field"></span>') ?>
        </div>
    </div>

    <!-- Added ID to the Comprobante de Pago title for easy hiding -->
    <h4 id="comprobante-title" class="mt-4 mb-4 text-info border-bottom pb-2"><i class="fas fa-file-upload me-2"></i> Comprobante de Pago</h4>

    <div class="row">
        <div class="col-md-12">
            <?php
            $initialPreview = [];
            $initialPreviewConfig = [];
            if (!$model->isNewRecord && $model->imagen_prueba) {
                // Asegúrate de que la URL generada sea accesible públicamente o maneje autenticación
                $initialPreview[] = Url::to($model->imagen_prueba, true); // Usar Url::to con true para URL absoluta
                $initialPreviewConfig[] = [
                    'caption' => basename($model->imagen_prueba),
                    'key' => 1,
                    // Si el archivo puede ser borrado via AJAX, puedes añadir 'url' => Url::to(['/pagos/delete-file', 'id' => $model->id])
                ];
            }
            ?>
            <?= $form->field($model, 'imagen_prueba_file')->widget(FileInput::classname(), [
                'name' => 'imagen_prueba_file', // Asegúrate de que el nombre coincide con el del modelo
                'options' => [
                    'accept' => 'image/*', // Solo acepta imágenes
                    'disabled' => $disabled,
                    'id' => 'pagos-imagen_prueba_file',
                ],
                'pluginOptions' => [
                    'theme' => 'fa5', // Utiliza el tema de Font Awesome 5
                    // El CSS global manejará el color blanco del botón de examinar
                    'browseClass' => 'btn btn-light rounded-pill px-3 shadow-sm text-dark',
                    'removeClass' => 'btn btn-outline-danger rounded-pill px-3 shadow-sm',
                    'uploadClass' => 'btn btn-info rounded-pill px-3 shadow-sm', // Si tuvieras un botón de subir separado
                    'removeIcon' => '<i class="fas fa-trash"></i> ',
                    'showUpload' => false, // No mostrar botón de subir si no manejas la subida por AJAX
                    'showCancel' => false,
                    'previewFileType' => 'image',
                    'maxFileSize' => 2800, // En KB (2.8 MB)
                    'msgSizeTooLarge' => 'El archivo "{name}" ({size} KB) excede el tamaño máximo permitido de {maxSize} KB. Por favor, suba una imagen más pequeña.',
                    'initialPreview' => $initialPreview,
                    'initialPreviewAsData' => true,
                    'initialPreviewConfig' => $initialPreviewConfig,
                    'overwriteInitial' => true, // Permite que un nuevo archivo sobrescriba el existente
                    'layoutTemplates' => [
                        'main1' => '{preview}{browse}{remove}', // Eliminar {upload} si no se usa
                        'main2' => '{preview}{browse}{remove}',
                        'footer' => '<div class="file-thumbnail-footer">\n{progress} {actions}\n</div>',
                    ],
                    'previewSettings' => [
                        'image' => ['width' => '100%', 'height' => 'auto', 'max-width' => '250px'], // Ajustar el tamaño de la previsualización
                    ],
                    'purifyHtml' => true, // Para mayor seguridad
                ],
            ])->label('Adjuntar Comprobante (JPG, PNG)' . '<span class="required-field"></span>') ?>
        </div>
    </div>

    <?php if (!$disabled): ?>
        <div class="form-group mt-4 d-flex justify-content-center gap-3"> <!-- Cambiado a justify-content-center y añadido gap-3 -->
            <!-- Botón "Guardar Pago" con el tamaño original deseado -->
            <?php if (!empty($cuotas)): ?>
                <?= Html::submitButton('<i class="fas fa-save me-2"></i> Guardar Pago', [
                    'class' => 'btn btn-success btn-lg rounded-pill px-7 shadow-sm',
                    'id' => 'submit-btn', // Added ID for JavaScript
                ]) ?>
            <?php endif; ?>
            <!-- Botón "Volver" con el tamaño original deseado -->
            <?= Html::a('<i class="fas fa-undo me-2"></i> Volver', ['contratos/index', 'user_id' => $model->user_id], [
                'class' => 'btn btn-secondary btn-lg rounded-pill px-7 shadow-sm' // Vuelve a btn-lg y px-7
            ]) ?>
        </div>
    <?php endif; ?>

    <?php ActiveForm::end(); ?>

</div>