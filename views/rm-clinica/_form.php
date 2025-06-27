<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\RmClinica $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="rm-clinica-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'rif')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'nombre')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'estado')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'direccion')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'telefono')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'correo')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'estatus')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'webpage')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'rs_instagram')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'QRCode')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'codigo_clinica')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'deleted_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'private_key')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
