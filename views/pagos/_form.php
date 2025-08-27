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

/** @var yii\web\View $this */
/** @var app\models\Pagos $model */
/** @var yii\widgets\ActiveForm $form */

$js = <<<JS
    $(document).ready(function() {
        // Función para actualizar el monto en USD
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
            $('#pagos-monto_usd').val(monto_usd_calculated.toFixed(2)); // Formatear a 2 decimales
        }

        // Listener para cambio en monto pagado o tasa
        $('#pagos-monto_pagado, #pagos-tasa').on('change keyup', function(){
            updateMontoUsd();
        });

        // Listener para cambio en método de pago
        $('#pagos-metodo_pago').on('change', function(){
            // Resetear los campos al cambiar el método de pago
            $('#pagos-monto_usd').val(0);
            $('#pagos-monto_pagado').val(0);
            
            if ($(this).val() == 'Zelle'){
                $('.field-pagos-tasa').hide(); // Ocultar campo de tasa para Zelle
            } else {
                $('.field-pagos-tasa').show(); // Mostrar campo de tasa para otros métodos
            }
            updateMontoUsd(); // Recalcular al cambiar el método de pago
        });

        // Asegurarse de que el estado inicial sea correcto al cargar la página
        // Simular un cambio inicial para ajustar la visibilidad de la tasa
        $('#pagos-metodo_pago').trigger('change');
    });
JS;
$this->registerJs($js);

?>
<style>
    /* Estilo para centrar el caption del FileInput */
    .file-input .file-caption {
        width: 100% !important; /* Ajustar al 100% dentro de su columna */
        margin: 0 auto;
        box-sizing: border-box;
    }
    /* Estilo adicional para los campos de formulario */
    .pagos-form .form-group {
        margin-bottom: 1rem; /* Espacio entre campos */
    }
    .pagos-form .form-label {
        font-weight: bold;
        color: #007bff; /* Color de texto para las etiquetas */
    }
    .pagos-form .form-control.rounded-pill {
        border-radius: 50rem !important; /* Bordes más redondeados para inputs */
    }
    .file-preview {
        max-width: 200px; /* Tamaño máximo para la vista previa de la imagen */
        margin: 0 auto;
    }

    /* Regla CSS para forzar el botón "Examinar" a ser blanco */
    /* Apunta a la clase `btn-file` que Kartik FileInput usa para el botón "Examinar" */
    .file-input .btn-file {
        background-color: white !important; /* Fondo blanco forzado */
        color: #333 !important; /* Texto oscuro para contraste */
        border: 1px solid #ced4da !important; /* Borde sutil */
    }
    /* Asegurar que los íconos y texto dentro del botón "Examinar" también tengan el color oscuro */
    .file-input .btn-file i, .file-input .btn-file span {
        color: #333 !important;
    }

</style>

<div class="pagos-form p-4 rounded-3 shadow-sm bg-light">

    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data'],
        'type' => ActiveForm::TYPE_VERTICAL, // Formulario vertical
        'formConfig' => [
            'labelSpan' => 12, // Etiqueta ocupa todo el ancho de la columna
            'deviceSize' => ActiveForm::SIZE_MEDIUM,
        ],
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
                    'Pago Movil' => 'Pago Movil',
                    'Transferencia Bancaria' => 'Transferencia Bancaria',
                    'Zelle' => 'Zelle'
                ],
                'options' => [
                    'placeholder' => 'Seleccione el método de pago...',
                    'class' => 'form-control rounded-pill', // Aplicar estilo redondeado
                    'disabled' => $disabled,
                ],
                'pluginOptions' => [
                    'allowClear' => false,
                ],
            ])->label('Método de Pago') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'fecha_pago')->textInput([
                'class' => 'form-control rounded-pill', // Aplicar estilo redondeado
                'type' => 'date',
                'placeholder' => 'Seleccione la fecha del pago',
                'disabled' => $disabled,
            ])->label('Fecha de Pago') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'monto_pagado')->textInput([
                'class' => 'form-control rounded-pill', // Aplicar estilo redondeado
                'type' => 'number',
                'step' => '0.01', // Para permitir decimales
                'placeholder' => 'Ingrese el monto pagado',
                'disabled' => $disabled,
            ])->label('Monto a Pagar en USD') ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'tasa')->textInput([
                'class' => 'form-control rounded-pill', // Aplicar estilo redondeado
                'type' => 'number',
                'step' => '0.0001', // Para tasas con más decimales
                'readonly' => true, // Mantener como solo lectura si se actualiza por JS
                'placeholder' => 'Ingrese la tasa de cambio',
                'disabled' => $disabled,
            ])->label('Tasa de Cambio USD a Bs(BCV)') ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'monto_usd')->textInput([
                'class' => 'form-control rounded-pill', // Aplicar estilo redondeado
                'readonly' => true, // Siempre solo lectura
                'placeholder' => 'Monto en Bs (calculado)',
                'disabled' => $disabled,
            ])->label('Monto en Bs') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'numero_referencia_pago')->textInput([
                'class' => 'form-control rounded-pill', // Aplicar estilo redondeado
                'type' => 'text',
                'placeholder' => 'Ingrese el número de referencia del pago',
                'disabled' => $disabled,
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
            ])->label('Adjuntar Comprobante (JPG, PNG)') ?>
        </div>
    </div>

    <?php if (!$disabled): ?>
    <div class="form-group mt-4 d-flex justify-content-center gap-3"> <!-- Cambiado a justify-content-center y añadido gap-3 -->
        <!-- Botón "Guardar Pago" con el tamaño original deseado -->
        <?= Html::submitButton('<i class="fas fa-save me-2"></i> Guardar Pago', [
            'class' => 'btn btn-success btn-lg rounded-pill px-7 shadow-sm' // Vuelve a btn-lg y px-7
        ]) ?>
        <!-- Botón "Volver" con el tamaño original deseado -->
        <?= Html::a('<i class="fas fa-undo me-2"></i> Volver', ['contratos/index', 'user_id' => $model->user_id], [
            'class' => 'btn btn-secondary btn-lg rounded-pill px-7 shadow-sm' // Vuelve a btn-lg y px-7
        ]) ?>
    </div>
    <?php endif; ?>

    <?php ActiveForm::end(); ?>

</div>
