<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Agente $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="agente-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'idusuariopropietario')->textInput() ?>

    <?= $form->field($model, 'nom')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'por_venta')->textInput() ?>

    <?= $form->field($model, 'por_asesor')->textInput() ?>

    <?= $form->field($model, 'por_cobranza')->textInput() ?>

    <?= $form->field($model, 'por_post_venta')->textInput() ?>

    <?= $form->field($model, 'por_agente')->textInput() ?>

    <?= $form->field($model, 'por_max')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'deleted_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
