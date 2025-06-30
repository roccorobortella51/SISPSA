<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ContratosSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="row mt-2">
    <div class="col-md-12">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'created_at') ?>

    <?= $form->field($model, 'plan_id') ?>

    <?= $form->field($model, 'ente_id') ?>

    <?= $form->field($model, 'clinica_id') ?>

    <?php // echo $form->field($model, 'fecha_ini') ?>

    <?php // echo $form->field($model, 'fecha_ven') ?>

    <?php // echo $form->field($model, 'monto') ?>

    <?php // echo $form->field($model, 'estatus') ?>

    <?php // echo $form->field($model, 'nrocontrato') ?>

    <?php // echo $form->field($model, 'frecuencia_pago') ?>

    <?php // echo $form->field($model, 'sucursal') ?>

    <?php // echo $form->field($model, 'moneda') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'deleted_at') ?>

    <?php // echo $form->field($model, 'anulado_por') ?>

    <?php // echo $form->field($model, 'anulado_fecha') ?>

    <?php // echo $form->field($model, 'anulado_motivo') ?>

    <?php // echo $form->field($model, 'user_id') ?>

    <?php // echo $form->field($model, 'PDF') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

    </div>
    <!--.col-md-12-->
</div>
