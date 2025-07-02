<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\PagosSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="pagos-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'created_at') ?>

    <?= $form->field($model, 'recibo_id') ?>

    <?= $form->field($model, 'fecha_pago') ?>

    <?= $form->field($model, 'monto_pagado') ?>

    <?php // echo $form->field($model, 'metodo_pago') ?>

    <?php // echo $form->field($model, 'estatus') ?>

    <?php // echo $form->field($model, 'numero_referencia_pago') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'imagen_prueba') ?>

    <?php // echo $form->field($model, 'user_id') ?>

    <?php // echo $form->field($model, 'nombre_conciliador') ?>

    <?php // echo $form->field($model, 'fecha_conciliacion') ?>

    <?php // echo $form->field($model, 'fecha_registro') ?>

    <?php // echo $form->field($model, 'deleted_at') ?>

    <?php // echo $form->field($model, 'conciliador_id') ?>

    <?php // echo $form->field($model, 'conciliado') ?>

    <?php // echo $form->field($model, 'monto_usd') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
