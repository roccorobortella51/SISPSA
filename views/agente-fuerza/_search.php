<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\AgenteFuerzaSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="agente-fuerza-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'idusuario') ?>

    <?= $form->field($model, 'agente_id') ?>

    <?= $form->field($model, 'por_venta') ?>

    <?= $form->field($model, 'por_asesor') ?>

    <?php // echo $form->field($model, 'por_cobranza') ?>

    <?php // echo $form->field($model, 'por_post_venta') ?>

    <?php // echo $form->field($model, 'puede_vender') ?>

    <?php // echo $form->field($model, 'puede_asesorar') ?>

    <?php // echo $form->field($model, 'puede_cobrar') ?>

    <?php // echo $form->field($model, 'puede_post_venta') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'deleted_at') ?>

    <?php // echo $form->field($model, 'puede_registrar') ?>

    <?php // echo $form->field($model, 'por_registrar') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
