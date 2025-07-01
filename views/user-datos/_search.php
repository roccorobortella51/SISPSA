<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\UserDatosSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="user-datos-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'created_at') ?>

    <?= $form->field($model, 'user_id') ?>

    <?= $form->field($model, 'nombres') ?>

    <?= $form->field($model, 'fechanac') ?>

    <?php // echo $form->field($model, 'sexo') ?>

    <?php // echo $form->field($model, 'selfie') ?>

    <?php // echo $form->field($model, 'telefono') ?>

    <?php // echo $form->field($model, 'estado') ?>

    <?php // echo $form->field($model, 'role') ?>

    <?php // echo $form->field($model, 'estatus') ?>

    <?php // echo $form->field($model, 'imagen_identificacion') ?>

    <?php // echo $form->field($model, 'qr') ?>

    <?php // echo $form->field($model, 'paso') ?>

    <?php // echo $form->field($model, 'video') ?>

    <?php // echo $form->field($model, 'ciudad') ?>

    <?php // echo $form->field($model, 'municipio') ?>

    <?php // echo $form->field($model, 'parroquia') ?>

    <?php // echo $form->field($model, 'direccion') ?>

    <?php // echo $form->field($model, 'codigoValidacion') ?>

    <?php // echo $form->field($model, 'clinica_id') ?>

    <?php // echo $form->field($model, 'plan_id') ?>

    <?php // echo $form->field($model, 'apellidos') ?>

    <?php // echo $form->field($model, 'email') ?>

    <?php // echo $form->field($model, 'contrato_id') ?>

    <?php // echo $form->field($model, 'asesor_id') ?>

    <?php // echo $form->field($model, 'deleted_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'ver_cedula') ?>

    <?php // echo $form->field($model, 'ver_foto') ?>

    <?php // echo $form->field($model, 'session_id') ?>

    <?php // echo $form->field($model, 'cedula') ?>

    <?php // echo $form->field($model, 'tipo_cedula') ?>

    <?php // echo $form->field($model, 'tipo_sangre') ?>

    <?php // echo $form->field($model, 'estatus_solvente') ?>

    <?php // echo $form->field($model, 'user_login_id') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
