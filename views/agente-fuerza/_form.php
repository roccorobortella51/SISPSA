<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\AgenteFuerza $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="agente-fuerza-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id')->textInput() ?>

    <?= $form->field($model, 'idusuario')->textInput() ?>

    <?= $form->field($model, 'agente_id')->textInput() ?>

    <?= $form->field($model, 'por_venta')->textInput() ?>

    <?= $form->field($model, 'por_asesor')->textInput() ?>

    <?= $form->field($model, 'por_cobranza')->textInput() ?>

    <?= $form->field($model, 'por_post_venta')->textInput() ?>

    <?= $form->field($model, 'puede_vender')->textInput() ?>

    <?= $form->field($model, 'puede_asesorar')->textInput() ?>

    <?= $form->field($model, 'puede_cobrar')->textInput() ?>

    <?= $form->field($model, 'puede_post_venta')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'deleted_at')->textInput() ?>

    <?= $form->field($model, 'puede_registrar')->textInput() ?>

    <?= $form->field($model, 'por_registrar')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
