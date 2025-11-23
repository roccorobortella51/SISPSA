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

// Generar la URL absoluta para la acción del controlador
$urlTasaCambio = Url::to(['tasacambio-referencial']);

$disabled = isset($isEditable) && !$isEditable;


// Carga explícita del asset del DatePicker (Ayuda a solucionar problemas de visualización)
\kartik\date\DatePickerAsset::register($this); 

// Registrar variable global en HEAD para evitar problemas con heredoc
$this->registerJs('var grandTotal = ' . Json::encode($grandTotal) . ';', \yii\web\View::POS_HEAD);

// --- BLOQUE DE JAVASCRIPT (Funcionalidad inalterada) ---
$js = <<<JS
// Función para calcular Monto en Bs (Referencial)
function updateMontoBsReferencial() {
    // Usar el ID correcto: #pagos-monto_pagado
    var montoUsd = parseFloat($('#pagos-monto_pagado').val().replace(/[^0-9.]/g, '')) || 0; 
    // Usar el ID correcto: #pagos-tasa
    var tasa = parseFloat($('#pagos-tasa').val()) || 0;
    var montoBs = 0;

    // Fórmula: Monto en Bs (Referencial) = Monto en USD * Tasa
    if (tasa > 0 && montoUsd > 0) {
        montoBs = montoUsd * tasa;
    }

    // Actualizar el campo de visualización (Ahora usa el ID del textInput dentro del ActiveField)
    $('#monto-bs-display').val(montoBs.toFixed(4));
    // Actualizar el campo oculto del modelo
    $('#pagos-monto_bs').val(montoBs.toFixed(4));
}

// Función para validar Monto USD
function validateMontoUsd() {
    // Usar el ID correcto: #pagos-monto_pagado
    var montoUsd = parseFloat($('#pagos-monto_pagado').val().replace(/[^0-9.]/g, '')) || 0;
    
    // Validación: El monto a pagar no debe superar el grandTotal
    if (montoUsd > grandTotal) {
        $('#pagos-monto_pagado').addClass('is-invalid');
        $('#monto-usd-error').text('El monto a pagar no puede superar el total pendiente (' + grandTotal.toFixed(2) + ' USD).');
    } else {
        $('#pagos-monto_pagado').removeClass('is-invalid');
        $('#monto-usd-error').text('');
    }
}


$(document).ready(function() {
    
    // 1. Tasa de Cambio fetch al cambiar la Fecha de Pago (ID: fecha-pago)
    $('#fecha-pago').on('change', function() {
        var fechaSeleccionada = $(this).val();

        if (fechaSeleccionada) {
            var ajaxUrl = '{$urlTasaCambio}'; 

            // AJAX Call al actionTasacambioReferencial en CorporativoController
            $.ajax({
                url: ajaxUrl, 
                type: 'post', // Usamos POST como convención
                data: { fecha: fechaSeleccionada },
                success: function(response) {
                    if (response) {
                        tasa = parseFloat(response);
                        // Usar el ID correcto: #pagos-tasa
                        $('#pagos-tasa').val(tasa.toFixed(2));
                    } else {
                        $('#pagos-tasa').val(''); // Limpiar la tasa si no se encuentra
                    }
                    updateMontoBsReferencial(); // Recalcular con la nueva tasa
                },
                error: function() {
                    console.log('Error fetching referential exchange rate.');
                    $('#pagos-tasa').val('');
                    updateMontoBsReferencial();
                }
            });
        }
    });

    // 2. Recalcular si la Tasa de Cambio es modificada manualmente
    $('#pagos-tasa').on('input', updateMontoBsReferencial);
    
    // 3. Recalcular Monto Bs y Validar Monto USD cuando Monto Pagado cambia
    // Usamos el ID correcto: #pagos-monto_pagado
    $('#pagos-monto_pagado').on('input', function() {
        validateMontoUsd();
        updateMontoBsReferencial();
    });

    // 4. Disparar cálculos iniciales al cargar
    if ($('#fecha-pago').val()) {
        // Disparar el evento change para que busque la tasa inicial
        $('#fecha-pago').trigger('change');
    } else if ($('#pagos-monto_pagado').val() || $('#pagos-tasa').val()) {
        updateMontoBsReferencial();
        validateMontoUsd();
    }
});
JS;
$this->registerJs($js);

// Add global CSS for larger fonts throughout the entire view
$this->registerCss('
    /* Global font size increase */
    body {
        font-size: 16px !important;
    }
    
    /* Card headers and titles */
    .card-header h1,
    .card-header h2,
    .card-header h3,
    .card-header h4 {
        font-size: 1.5rem !important;
        font-weight: 600 !important;
    }
    
    /* Form labels */
    .control-label {
        font-size: 1.1rem !important;
        font-weight: 500 !important;
    }
    
    /* Form inputs */
    .form-control {
        font-size: 1.1rem !important;
        padding: 0.75rem 1rem !important;
    }
    
    /* Table text */
    .table {
        font-size: 1.1rem !important;
    }
    
    .table th {
        font-size: 1.2rem !important;
        font-weight: 600 !important;
        padding: 1rem 0.75rem !important;
    }
    
    .table td {
        font-size: 1.1rem !important;
        padding: 0.875rem 0.75rem !important;
    }
    
    /* Alert text */
    .alert {
        font-size: 1.2rem !important;
    }
    
    .alert h3 {
        font-size: 2rem !important;
        font-weight: 700 !important;
    }
    
    /* Buttons */
    .btn {
        font-size: 1.2rem !important;
        font-weight: 500 !important;
        padding: 0.75rem 2rem !important;
    }
    
    /* File input text */
    .file-caption-name {
        font-size: 1.1rem !important;
    }
    
    /* Help blocks and errors */
    .help-block,
    .invalid-feedback {
        font-size: 1rem !important;
    }
    
    /* Select dropdowns */
    select.form-control {
        font-size: 1.1rem !important;
        height: auto !important;
        padding: 0.75rem 1rem !important;
    }
    
    /* Textareas */
    textarea.form-control {
        font-size: 1.1rem !important;
    }
');

$form = ActiveForm::begin([
    'id' => 'pago-corporativo-form',
    'options' => ['enctype' => 'multipart/form-data'], // Necesario para FileInput
    'type' => ActiveForm::TYPE_HORIZONTAL,
    // Configuramos labelSpan a 4 (4/12 del ancho del wrapper) para acortar el campo de texto
    'formConfig' => ['labelSpan' => 4, 'deviceSize' => ActiveForm::SIZE_SMALL], 
]);
?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm border-primary mb-4">
            <div class="card-header bg-primary text-white">
                <h1 class="card-title mb-0" style="font-size: 1.8rem !important;"><i class="fas fa-credit-card me-2"></i> Registro del Pago Corporativo</h1>
            </div>
            <div class="card-body">
                <div class="alert alert-info text-center" role="alert">
                    <div style="font-size: 1.3rem !important;">Total Pendiente para Corporativo <strong><?= Html::encode($corporativo->nombre) ?></strong>:</div>
                    <br>
                    <h3 class="text-danger" style="font-size: 2.5rem !important; font-weight: 700 !important;">
                        <strong><?= Yii::$app->formatter->asCurrency($grandTotal) ?></strong>
                    </h3>
                </div>

                <div class="row mb-3 pb-2 border-bottom"> <div class="col-md-4">
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
                            'id' => 'fecha-pago', // ID único
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
                                'removeMaskOnSubmit' => true
                            ],
                            'options' => [
                                'id' => 'pagos-monto_pagado', // ID explícito para JS
                                'placeholder' => 'Monto total en USD'
                            ],
                        ])->textInput(['maxlength' => true])->label('Monto Pagado (USD)') ?>
                        <div id="monto-usd-error" class="invalid-feedback d-block"></div>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'tasa')->textInput([
                            'id' => 'pagos-tasa', // ID explícito para JS
                            'placeholder' => 'Tasa de Referencia', 
                        ])->label('Tasa BCV') ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'monto_usd', [
                            // Aplicar la misma estructura horizontal del formConfig
                            'horizontalCssClasses' => [
                                'label' => 'col-sm-4 control-label', 
                                'wrapper' => 'col-sm-8', 
                            ],
                            // Template modificado para evitar que se renderice doblemente el input (solo necesitamos el contenedor)
                            'template' => "{label}\n<div class='col-sm-8'>{input}\n{hint}\n{error}</div>",
                        ])->textInput([
                            'id' => 'monto-bs-display', // ID para que el JS actualice este campo
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

            </div>
        </div>
    </div>
    
    <div class="col-md-12">
        <div class="card shadow-sm border-info mb-4">
            <div class="card-header bg-info text-white">
                <h3 class="card-title mb-0" style="font-size: 1.6rem !important;"><i class="fas fa-list-ul me-2"></i> Detalle de Cuotas Pendientes</h3>
            </div>
            <div class="card-body p-0">
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm mb-0">
                        <thead class="bg-dark"> 
                            <tr class="text-white">
                                <th style="color: white !important; font-size: 1.2rem !important;">ID Cuota</th>
                                <th style="color: white !important; font-size: 1.2rem !important;">ID Afiliado</th>
                                <th style="color: white !important; font-size: 1.2rem !important;">Afiliado</th>
                                <th style="color: white !important; font-size: 1.2rem !important;">Contrato</th>
                                <th style="color: white !important; font-size: 1.2rem !important;">Monto USD</th>
                                <th style="color: white !important; font-size: 1.2rem !important;">Vencimiento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allCuotas as $cuota): 
                                // Se asume: Cuotas->contrato->user
                                $contrato = $cuota->contrato ?? null;
                                $userDatos = $contrato->user ?? null;
                                
                                $userId = $contrato->user_id ?? 'N/A';
                                $nombreCompleto = $userDatos ? Html::encode($userDatos->nombres . ' ' . $userDatos->apellidos) : 'Afiliado no encontrado';
                            ?>
                            <tr>
                                <td><?= Html::encode($cuota->id) ?></td>
                                <td><?= $userId ?></td>
                                <td title="<?= $nombreCompleto ?>"><?= \yii\helpers\StringHelper::truncateWords($nombreCompleto, 3, '...') ?></td>
                                <td><?= Html::encode($contrato->nrocontrato ?? 'N/A') ?></td>
                                <td class="text-right"><?= Yii::$app->formatter->asCurrency($cuota->monto_usd) ?></td>
                                <td><?= Yii::$app->formatter->asDate($cuota->fecha_vencimiento, 'php:d/m/Y') ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($allCuotas)): ?>
                            <tr>
                                <td colspan="6" class="text-center" style="font-size: 1.2rem !important;">No hay cuotas pendientes para los afiliados de este corporativo.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-dark">
                                <td colspan="4" class="text-right" style="font-size: 1.2rem !important;"><strong>TOTAL PENDIENTE:</strong></td>
                                <td class="text-right" style="font-size: 1.2rem !important;"><strong><?= Yii::$app->formatter->asCurrency($grandTotal) ?></strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-12">
        <div class="card shadow-sm border-secondary mb-4 mx-auto" style="max-width: 600px;">
            <div class="card-header bg-secondary text-white text-center">
                <h4 class="card-title mb-0" style="font-size: 1.5rem !important;"><i class="fas fa-paperclip me-2"></i> Adjuntar Comprobante (JPG, PNG)</h4>
            </div>
            <div class="card-body">
                <?php 
                $initialPreview = [];
                $initialPreviewConfig = [];
                
                // NUCLEAR OPTION CSS - 100% guaranteed to work
                $this->registerCss('
                    /* Nuclear option - target every possible element */
                    .btn-file *,
                    .file-caption *,
                    .file-input *,
                    .kv-fileinput-caption *,
                    [class*="file"] {
                        color: white !important;
                    }
                    .btn-file,
                    .file-caption-name,
                    .file-caption-icon,
                    .fileinput-upload-button,
                    .btn-default,
                    .btn-kv {
                        color: white !important;
                        background-color: #007bff !important;
                        border-color: #007bff !important;
                    }
                    /* Force white text on all children */
                    .btn-file span,
                    .btn-file div,
                    .btn-file p {
                        color: white !important;
                    }
                    /* Target specific Kartik FileInput elements */
                    .file-caption,
                    .file-caption .file-caption-name,
                    .kv-fileinput-caption,
                    .file-input .btn,
                    .file-input .btn-default {
                        color: white !important;
                    }
                    /* Ensure hover states also maintain white text */
                    .btn-file:hover,
                    .btn-file:hover * {
                        color: white !important;
                        opacity: 0.9;
                    }
                ');
                ?>
                <?= $form->field($model, 'imagen_prueba_file')->widget(FileInput::class, [
                    'options' => ['accept' => 'image/*'],
                    'pluginOptions' => [
                        'showUpload' => false,
                        'showRemove' => false,
                        'maxFileSize' => 2048, // 2MB
                        'msgSizeTooLarge' => 'El archivo "{name}" ({sizeText}) excede el tamaño máximo permitido de {maxSize}. Pruebe una imagen más pequeña.',
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
                ])->label(false) ?>
            </div>
        </div>
    </div>
</div>

<div class="form-group mt-4 d-flex justify-content-center gap-3">
    <?php if (!empty($allCuotas) && $grandTotal > 0): ?>
        <?= Html::submitButton('<i class="fas fa-save me-2"></i> Guardar Pago Corporativo', [
            'class' => 'btn btn-success btn-lg rounded-pill px-7 shadow-sm text-white',
            'style' => 'color: white !important; font-size: 1.3rem !important; padding: 0.875rem 2.5rem !important;'
        ]) ?>
    <?php endif; ?>
    <?= Html::a('<i class="fas fa-undo me-2"></i> Volver', ['contracts', 'id' => $corporativo->id], [
        'class' => 'btn btn-secondary btn-lg rounded-pill px-7 shadow-sm text-white',
        'style' => 'color: white !important; font-size: 1.3rem !important; padding: 0.875rem 2.5rem !important;'
    ]) ?>
</div>

<?php ActiveForm::end(); ?>