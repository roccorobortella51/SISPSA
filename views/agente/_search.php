<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\AgenteSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="agente-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'idusuariopropietario') ?>

    <?= $form->field($model, 'nom') ?>

    <?= $form->field($model, 'por_venta') ?>

    <?= $form->field($model, 'por_asesor') ?>

    <?php // echo $form->field($model, 'por_cobranza') ?>

    <?php // echo $form->field($model, 'por_post_venta') ?>

    <?php // echo $form->field($model, 'por_agente') ?>

    <?php // echo $form->field($model, 'por_max') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'deleted_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
