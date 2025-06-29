<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\UserDatos */
/* @var $form yii\bootstrap4\ActiveForm */
?>

<div class="user-datos-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'user_id')->textInput() ?>

    <?= $form->field($model, 'nombres')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'fechanac')->textInput() ?>

    <?= $form->field($model, 'sexo')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'selfie')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'telefono')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'estado')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'role')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'estatus')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'imagen_identificacion')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'qr')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'paso')->textInput() ?>

    <?= $form->field($model, 'video')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'ciudad')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'municipio')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'parroquia')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'direccion')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'codigoValidacion')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'clinica_id')->textInput() ?>

    <?= $form->field($model, 'plan_id')->textInput() ?>

    <?= $form->field($model, 'apellidos')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'email')->textInput() ?>

    <?= $form->field($model, 'contrato_id')->textInput() ?>

    <?= $form->field($model, 'asesor_id')->textInput() ?>

    <?= $form->field($model, 'deleted_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'ver_cedula')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'ver_foto')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'session_id')->textInput() ?>

    <?= $form->field($model, 'cedula')->textInput() ?>

    <?= $form->field($model, 'tipo_cedula')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'tipo_sangre')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'estatus_solvente')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
