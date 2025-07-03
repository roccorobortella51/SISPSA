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
        <div class="col-md-3">
                    <?= $form->field($model, 'nombre')->textInput([ 'class' => 'form-control', 'placeholder' => 'Escriba un nombre del familiar','class' => 'form-control form-control-lg',]) ?>
                    </div>
                    <div class="col-md-3">
                         <?= $form->field($model, 'telefono')->textInput([ 'class' => 'form-control', 'placeholder' => 'Escriba un telefono del contacto','class' => 'form-control form-control-lg',]) ?>
                    </div>
                    <div class="col-md-3">
                         <?= $form->field($model, 'relacion')->textInput([ 'class' => 'form-control', 'placeholder' => 'Escriba la relación del contacto','class' => 'form-control form-control-lg',]) ?>
                    </div>
                    <div class="col-md-3">
                         <?= $form->field($model, 'correo')->textInput([ 'class' => 'form-control', 'placeholder' => 'Escriba un correo electronico del contacto','class' => 'form-control form-control-lg',]) ?>
                    </div>
        <div class="col-md-12">
        <div class="form-group text-rigth mt-4" style="margin-right:10px;">
            <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success btn-lg']) ?>
            <?= Html::a('Cancelar', ['index', 'user_id' => $model->user_id], ['class' => 'btn btn-lg btn-warning']); ?>
        </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
    </div>
</div>
