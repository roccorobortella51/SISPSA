<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Contratos */
/* @var $form yii\bootstrap4\ActiveForm */
?>

<div class="contratos-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'plan_id')->textInput() ?>

    <?= $form->field($model, 'ente_id')->textInput() ?>

    <?= $form->field($model, 'clinica_id')->textInput() ?>

    <?= $form->field($model, 'fecha_ini')->textInput() ?>

    <?= $form->field($model, 'fecha_ven')->textInput() ?>

    <?= $form->field($model, 'monto')->textInput() ?>

    <?= $form->field($model, 'estatus')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'nrocontrato')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'frecuencia_pago')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'sucursal')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'moneda')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'deleted_at')->textInput() ?>

    <?= $form->field($model, 'anulado_por')->textInput() ?>

    <?= $form->field($model, 'anulado_fecha')->textInput() ?>

    <?= $form->field($model, 'anulado_motivo')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'user_id')->textInput() ?>

    <?= $form->field($model, 'PDF')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
