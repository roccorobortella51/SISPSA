<?php

use yii\helpers\Html;
use kartik\form\ActiveForm; // Asegúrate de que esto es 'kartik\form\ActiveForm'
use kartik\select2\Select2; // Para los selectores de estado y estatus
use yii\widgets\MaskedInput; // <--- ¡IMPORTANTE! Sigue siendo 'yii\widgets\MaskedInput' para el campo de cédula
use app\components\UserHelper;
use kartik\widgets\SwitchInput; // No usado en este fragmento, pero puede mantenerse.
use kartik\widgets\DatePicker;
use kartik\widgets\FileInput;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Pagos $model */
/** @var yii\widgets\ActiveForm $form */
$js = <<<JS
    $(document).ready(function() {
        $('#pagos-monto_pagado').on('change keyup', function(){
            var monto_pagado = $(this).val();
            var tasa = $('#pagos-tasa').val();
            if ($('#pagos-metodo_pago').val() != 'Zelle'){
                var monto_total = monto_pagado * tasa;
                $('#pagos-monto_usd').val(monto_total);
            }else{
                $('#pagos-monto_usd').val(monto_pagado);
            }
            console.log(monto_total);
        })
        $('#pagos-metodo_pago').on('change', function(){
            $('#pagos-monto_usd').val(0);
            $('#pagos-monto_pagado').val(0);
            if ($(this).val() == 'Zelle'){
                $('.field-pagos-tasa').hide();
            }else{
                $('.field-pagos-tasa').show();
            }            
        });
    })
JS;
$this->registerJs($js);

?>
<div class="pagos-form">

<?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data']
    ]); ?>

    <?php
    // Determine if the form should be disabled based on $isEditable
    $disabled = isset($isEditable) && !$isEditable;
    ?>

    <div class ="row">
        <div class="col-md-2 text-end">
            <?= $form->field($model, 'metodo_pago')->widget(Select2::classname(), [
                            'data' => ['Pago Movil' => 'Pago Movil','Transferencia Bancaria' => 'Transferencia Bancaria','Zelle' => 'Zelle'],
                            'options' => [
                                'placeholder' => 'Seleccione el asesor', // Placeholder adaptado
                                'class' => 'form-control form-control-lg',
                                'disabled' => $disabled,
                            ],
                            'pluginOptions' => [
                                'allowClear' => false,
                            ],
                    ])->label('Metodo de Pago') // Etiqueta adaptada
        
            ?>
        </div>
        <div class ="col-md-2 text-end">
            <?= $form->field($model, 'fecha_pago')->textInput([
                'class' => 'form-control form-control-lg',
                'type' => 'date',
                'placeholder' => 'Seleccione su fecha de nacimiento',
                'disabled' => $disabled,
                ])->label('Fecha de Pago') 
                ?>
        </div>
        <div class="col-md-2 text-end">    
            <?= $form->field($model, 'monto_pagado')->textInput([
                'class' => 'form-control form-control-lg',
                'type' => 'number',
                'placeholder' => 'Ingrese el monto pagado',
                'disabled' => $disabled,
                ])->label('Monto Pagado') 
            ?>
        </div>
        <div class="col-md-2 text-end">
            <?= $form->field($model, 'tasa')->textInput([
                'class' => 'form-control form-control-lg',
                'type' => 'number',
                'readonly' => 'readonly',
                'placeholder' => 'Ingrese la tasa',
                'disabled' => $disabled,
                ])->label('Tasa de Cambio') 
            ?>
        </div>
        <div class="col-md-2 text-end">
            <?= $form->field($model, 'monto_usd')->textInput([
                'class' => 'form-control form-control-lg',
                'readonly' => 'readonly',
                'placeholder' => 'Ingrese el monto en USD',
                'disabled' => $disabled,
                ])->label('Monto en USD') 
            ?>
        </div>
        <div class="col-md-2 text-end">
            <?= $form->field($model, 'numero_referencia_pago')->textInput([
                'class' => 'form-control form-control-lg',
                'type' => 'text',
                'placeholder' => 'Ingrese el numero de referencia',
                'disabled' => $disabled,
                ])->label('Numero de Referencia') 
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <?php
            $initialPreview = [];
            $initialPreviewConfig = [];
            if (!$model->isNewRecord && $model->imagen_prueba) {
                $initialPreview[] = \yii\helpers\Url::to('@web/' . $model->imagen_prueba);
                $initialPreviewConfig[] = ['caption' => basename($model->imagen_prueba), 'key' => 1];
            }
            ?>
            <?= $form->field($model, 'imagen_prueba_file')->widget(FileInput::classname(),[
                'name' => 'attachments',
                'pluginOptions' => [
                    'browseClass' => 'btn btn-success',
                    'uploadClass' => 'btn btn-info',
                    'removeClass' => 'btn btn-danger',
                    'removeIcon' => '<i class="fas fa-trash"></i> ',
                    'previewFileType' => 'image',
                    'showUpload' => true,
                    'maxFileSize' => 2800,
                    'previewSettings' => [
                        'image' => ['width' => '150px', 'height' => 'auto'],
                    ],
                    'initialPreview' => $initialPreview,
                    'initialPreviewAsData' => true,
                    'initialPreviewConfig' => $initialPreviewConfig,
                    'overwriteInitial' => true,
                    //'layoutTemplates' => [
                    //    'preview' => '<div class="file-preview {class}" style="width: 200px;"></div>',
                    //],
                ],
                'options' => [
                    'disabled' => $disabled,
                ],
                ])->label('Imagen de Prueba');
            ?>    
        </div>
    </div>
    <?php if (!$disabled): ?>
    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>
    <?php endif; ?>

    <?php ActiveForm::end(); ?>

</div>
