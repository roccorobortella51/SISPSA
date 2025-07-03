<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\YourModelName */
/* @var $form yii\widgets\ActiveForm */

// Define las opciones para "Sí/No" con los strings 'Si' y 'No'
$sinoOptions = [
    'Si' => 'Sí', // La clave 'Si' se guardará en DB, el valor 'Sí' se mostrará al usuario
    'No' => 'No', // La clave 'No' se guardará en DB, el valor 'No' se mostrará al usuario
];

?>

<div class="your-form-container">

    <?php $form = ActiveForm::begin(); ?>

  
    <h3>Sección de Preguntas Sí/No</h3>
    <hr>

    <?php for ($i = 1; $i <= 16; $i++): ?>
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Pregunta <?= $i ?></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <?= $form->field($model, "p{$i}_sino")->radioList($sinoOptions, [
                            'itemOptions' => ['class' => 'me-3'],
                            'separator' => '<br>',
                            // O si quieres que estén en línea: 'class' => 'form-check-inline'
                        ])->label("¿Tuvo alguna afección para la pregunta {$i}?"); ?>
                    </div>
                    <div class="col-md-8">
                        <?= $form->field($model, "p{$i}_especifica")->textarea(['rows' => 3, 'placeholder' => 'Especifique la afección para la pregunta ' . $i]) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endfor; ?>

    <hr>
    <h3>Otros Datos</h3>


    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'ver_observacion')->textarea(['rows' => 3]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'ver_si_no')->radioList($sinoOptions, [
                'itemOptions' => ['class' => 'me-3'],
                'separator' => '<br>'
            ])->label('¿Verificado Sí/No?'); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'url_video_declaracion')->textarea(['rows' => 3]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'estatus')->textInput() ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'user_id')->textInput(['value' => $afiliado->id, 'readonly' => true]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'estatura')->textInput() ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'peso')->textInput(['type' => 'number']) ?>
        </div>
    </div>

     <div class="form-group text-rigth mt-4">
        <?= Html::submitButton('<i class="fas fa-save"></i> Guardar Clínica', ['class' => 'btn btn-success btn-lg']) ?>
        <?= Html::a('Cancelar', ['index', 'user_id' => $afiliado->id], ['class' => 'tn btn btn-lg btn-warning']); ?>

        <?php if ($model->isNewRecord) { echo Html::a('Limpiar', ['create'], ['class' => 'btn btn-lg btn-outline-dark']); } ?>
    </div>


    <?php ActiveForm::end(); ?>

</div>