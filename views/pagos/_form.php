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
            var monto_total = monto_pagado * tasa;
            $('#pagos-monto_usd').val(monto_total);
            console.log(monto_total);
        })
    })
JS;
$this->registerJs($js);

?>
<div class="pagos-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class ="row">
        <div class="col-md-2 text-end">
            <?= $form->field($model, 'metodo_pago')->widget(Select2::classname(), [
                            'data' => ['Pago Movil' => 'Pago Movil','Transferencia Bancaria' => 'Transferencia Bancaria','Zelle' => 'Zelle'],
                            'options' => [
                                'placeholder' => 'Seleccione el asesor', // Placeholder adaptado
                                'class' => 'form-control form-control-lg',
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
                'placeholder' => 'Seleccione su fecha de nacimiento'
                ])->label('Fecha de Pago') 
                ?>
        </div>
        <div class="col-md-2 text-end">    
            <?= $form->field($model, 'monto_pagado')->textInput([
                'class' => 'form-control form-control-lg',
                'type' => 'number',
                'placeholder' => 'Ingrese el monto pagado'
                ])->label('Monto Pagado') 
            ?>
        </div>
        <div class="col-md-2 text-end">
            <?= $form->field($model, 'tasa')->textInput([
                'class' => 'form-control form-control-lg',
                'type' => 'number',
                'readonly' => 'readonly',
                'placeholder' => 'Ingrese la tasa'
                ])->label('Tasa de Cambio') 
            ?>
        </div>
        <div class="col-md-2 text-end">
            <?= $form->field($model, 'monto_usd')->textInput([
                'class' => 'form-control form-control-lg',
                'readonly' => 'readonly',
                'placeholder' => 'Ingrese el monto en USD'
                ])->label('Monto en USD') 
            ?>
        </div>
        <div class="col-md-2 text-end">
            <?= $form->field($model, 'numero_referencia_pago')->textInput([
                'class' => 'form-control form-control-lg',
                'type' => 'text',
                'placeholder' => 'Ingrese el numero de referencia'
                ])->label('Numero de Referencia') 
            ?>
        </div>
    </div>






    <?= $form->field($model, 'imagen_prueba')->widget(FileInput::classname(),[
        'name' => 'attachments', 
        ])->label('Imagen de Prueba');
    ?>    




    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
