<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Planes $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="planes-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'nombre')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'descripcion')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'precio')->textInput() ?>

    <?= $form->field($model, 'estatus')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'nota')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'tipo')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'clinica_id')->textInput() ?>

    <?= $form->field($model, 'cobertura')->textInput() ?>

    <?= $form->field($model, 'PDF')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'comision')->textInput() ?>

    <?= $form->field($model, 'edad_minima')->textInput() ?>

    <?= $form->field($model, 'edad_limite')->textInput() ?>

    <?= $form->field($model, 'deleted_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
