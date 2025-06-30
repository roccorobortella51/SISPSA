
<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\components\UserHelper;
use kartik\select2\Select2;

/** @var yii\web\View $this */
/** @var app\models\Baremo $model */
/** @var yii\widgets\ActiveForm $form */
?>
<div class="baremo-form">
    <div class="ms-panel-body">
    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'nombre')->textInput(['class' => 'form-control form-control-lg', 'placeholder' => 'Escriba un nombre para el plan']) ?>
        </div>
        <div class="col-md-12">
            <?= $form->field($model, 'descripcion')->textInput(['class' => 'form-control form-control-lg', 'placeholder' => 'Escriba una descripción para el plan']) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'cobertura')->textInput(['type' => 'number', 'class' => 'form-control form-control-lg', 'placeholder' => '0.00']) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'precio')->textInput(['type' => 'number', 'class' => 'form-control form-control-lg', 'placeholder' => '0.00']) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'comision')->textInput(['type' => 'number', 'class' => 'form-control form-control-lg', 'placeholder' => '0%']) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'edad_minima')->textInput(['type' => 'number', 'class' => 'form-control form-control-lg', 'placeholder' => '0']) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'edad_limite')->textInput(['type' => 'number', 'class' => 'form-control form-control-lg', 'placeholder' => '0']) ?>
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
</div>


