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
        <div class="col-md-2">
            <?= $form->field($model, 'nombre_servicio')->textInput([ 'class' => 'form-control form-control-lg',]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'descripcion')->textInput([ 'class' => 'form-control form-control-lg',]) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'costo')->textInput([ 'class' => 'form-control form-control-lg',]) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'precio')->textInput([ 'class' => 'form-control form-control-lg',]) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'area_id')->widget(Select2::classname(), [
                            'data' => UserHelper::getAreaList(),
                            'options' => [
                                'placeholder' => 'Seleccione un estado...',
                                'class' => 'form-control form-control-lg',
                            ],
                            'pluginOptions' => [
                                'allowClear' => false,
                            ],
            ]) ?>
        </div>
        <div class="col-md-12">
        <div class="form-group text-rigth mt-4" style="margin-right:10px;">
            <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success btn-lg']) ?>
           
        </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
    </div>
</div>
