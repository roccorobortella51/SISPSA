<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\components\UserHelper;
/** @var yii\web\View $this */
/** @var app\models\Baremo $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="ms-panel-body">
<?php $form = ActiveForm::begin(); ?>
<div class="row">
    <div class="col-md-12">
        <?= $form->field($model, 'nombre')->textInput() ?>
    </div>
    <div class="col-md-12">
        <?= $form->field($model, 'descripcion')->textInput() ?>
    </div>
    <div class="col-md-2">
        <?= $form->field($model, 'cobertura')->textInput(['type' => 'number']) ?>
    </div>
    <div class="col-md-2">
        <?= $form->field($model, 'precio')->textInput(['type' => 'number']) ?>
    </div>
    <div class="col-md-2">
        <?= $form->field($model, 'comision')->textInput(['type' => 'number']) ?>
    </div>
    <div class="col-md-2">
        <?= $form->field($model, 'edad_minima')->textInput(['type' => 'number']) ?>
    </div>
    <div class="col-md-2">
        <?= $form->field($model, 'edad_limite')->textInput(['type' => 'number']) ?>
    </div>    
    <div class="col-md-12">
    <div class="form-group text-rigth mt-4" style="margin-right:10px;">
        <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success btn-lg']) ?>
        <?= Html::a('Cancelar', ['index', 'clinica_id' => $model->clinica_id], ['class' => 'btn btn-lg btn-warning']); ?>
    </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
</div>
