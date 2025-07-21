<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\CorporativoSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="corporativo-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'nombre') ?>

    <?= $form->field($model, 'email') ?>

    <?= $form->field($model, 'telefono') ?>

    <?= $form->field($model, 'rif') ?>

    <?php // echo $form->field($model, 'estado') ?>

    <?php // echo $form->field($model, 'municipio') ?>

    <?php // echo $form->field($model, 'parroquia') ?>

    <?php // echo $form->field($model, 'direccion') ?>

    <?php // echo $form->field($model, 'codigo_asesor') ?>

    <?php // echo $form->field($model, 'lugar_registro') ?>

    <?php // echo $form->field($model, 'fecha_registro_mercantil') ?>

    <?php // echo $form->field($model, 'tomo_registro') ?>

    <?php // echo $form->field($model, 'folio_registro') ?>

    <?php // echo $form->field($model, 'domicilio_fiscal') ?>

    <?php // echo $form->field($model, 'contacto_nombre') ?>

    <?php // echo $form->field($model, 'contacto_cedula') ?>

    <?php // echo $form->field($model, 'contacto_telefono') ?>

    <?php // echo $form->field($model, 'contacto_cargo') ?>

    <?php // echo $form->field($model, 'estatus') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'deleted_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
