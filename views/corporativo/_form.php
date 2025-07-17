<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Corporativo $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="corporativo-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'telefono')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'rif')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'estado')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'municipio')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'parroquia')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'direccion')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'codigo_asesor')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'lugar_registro')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fecha_registro_mercantil')->textInput() ?>

    <?= $form->field($model, 'tomo_registro')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'folio_registro')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'domicilio_fiscal')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'contacto_nombre')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'contacto_cedula')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'contacto_telefono')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'contacto_cargo')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'estatus')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'deleted_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
