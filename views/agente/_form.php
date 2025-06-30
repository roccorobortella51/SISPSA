<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Agente $model */
/** @var yii\widgets\ActiveForm $form */

?>

<div class="agente-form">

    <?php $form = ActiveForm::begin([]); ?>

    <div class="row mb-3">

        <div class="col-md-6">
            <?= $form->field($model, 'nom')->label('NOMBRE DE LA AGENCIA')->textInput([
                'maxlength' => true,
                'class' => 'form-control', // Usamos 'form-control' para tamaño estándar
                'placeholder' => 'Nombre completo del agente',
                'label' => 'Nombre de la agencia',
                'autofocus' => true,
            ]) ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'idusuariopropietario')->label('NOMBRE DEL PROPETARIO')->textInput([
                'class' => 'form-control', // Usamos 'form-control' para tamaño estándar
                'placeholder' => 'ID del usuario propietario',
            ]) ?>
        </div>
       
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <?= $form->field($model, 'por_venta')->label('PORCENTAJE POR VENTA')->textInput([
                'class' => 'form-control', // Usamos 'form-control' para tamaño estándar
                'placeholder' => '% Venta',
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'por_asesor')->label('PORCENTAJE DE ASESORÍA Y POST VENTA')->textInput([
                'class' => 'form-control', // Usamos 'form-control' para tamaño estándar
                'placeholder' => '% Asesoría',
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'por_cobranza')->label('PORCENTAJE POR COBRANZA')->textInput([
                'class' => 'form-control', // Usamos 'form-control' para tamaño estándar
                'placeholder' => '% Cobranza',
            ]) ?>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <?= $form->field($model, 'por_post_venta')->label('PORCENTAJE POR VENTA')->textInput([
                'class' => 'form-control', // Usamos 'form-control' para tamaño estándar
                'placeholder' => '% Post-Venta',
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'por_agente')->label('PORCENTAJE DE AGENCIA')->textInput([
                'class' => 'form-control', // Usamos 'form-control' para tamaño estándar
                'placeholder' => '% Agente',
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'por_max')->label('PORCENTAJE MAXIMO')->textInput([
                'class' => 'form-control', // Usamos 'form-control' para tamaño estándar
                'placeholder' => '% Máximo',
            ]) ?>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <?= $form->field($model, 'created_at')->textInput(['class' => 'form-control', 'placeholder' => 'Fecha Creación']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'updated_at')->textInput(['class' => 'form-control', 'placeholder' => 'Última Actualización']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'deleted_at')->textInput(['class' => 'form-control', 'placeholder' => 'Fecha Eliminación']) ?>
        </div>
    </div>

    <div class="form-group text-end mt-4">
        <?= Html::submitButton('<i class="fas fa-save"></i> GUARDAR AGENTE', ['class' => 'btn btn-outline-primary btn-md']) ?>

        <?= Html::a('CANCELAR', ['index'], ['class' => 'btn btn-md btn-outline-warning ms-2']); ?>

        <?php if ($model->isNewRecord) { ?>
            <?= Html::a('LIMPIAR', ['create'], ['class' => 'btn btn-md btn-outline-dark ms-2']); ?>
        <?php } ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>