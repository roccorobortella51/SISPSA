<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\web\JqueryAsset;

/** @var yii\web\View $this */
/** @var app\models\Agente $model */
/** @var yii\widgets\ActiveForm $form */
// ... otras variables ...

?>

<div class="agente-form">

    <?php $form = ActiveForm::begin([]); ?>

    <?php if (!$model->isNewRecord) { ?>
        <div class="row mb-3"> 
            
            <div class="col-md-6">
                <div class="ms-panel ms-widget ms-identifier-widget bg-info panel-clickable" data-href="<?= Url::to(['update', 'id' => $model->id]) ?>">
                    <div class="ms-panel-header header-mini" style="padding-top: 35px; padding-bottom: 35px; text-align: center">
                        <h6 style="margin: 0;"> 
                            <?= Html::a(
                                'ACTUALIZACIÓN DE AGENCIA',
                                ['update', 'id' => $model->id],
                                ['class' => 'text-white panel-link', 'style' => 'font-size: 1.em;']
                            ) ?>
                        </h6>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="ms-panel ms-widget ms-identifier-widget bg-info panel-clickable" data-href="<?= Url::to(['#']) ?>">
                    <div class="ms-panel-header header-mini" style="padding-top: 35px; padding-bottom: 35px; text-align: center">
                        <h6 style="margin: 0;">
                            <?= Html::a(
                                'FUERZA DE VENTA',
                                ['agente-fuerza/index-by-agente', 'agente_id' => $model->id], 
                                ['class' => 'text-white panel-link', 'style' => 'font-size: 1.em;']
                            ) ?>
                        </h6>
                    </div>
                </div>
            </div>
            
        </div>
    <?php } ?>

    <div class="row mb-3">
        <div class="col-md-6">
            <?= $form->field($model, 'nom')->label('NOMBRE DE LA AGENCIA')->textInput([
                'maxlength' => true,
                'class' => 'form-control',
                'placeholder' => 'Nombre completo del agente',
                'autofocus' => true,
            ]) ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'idusuariopropietario')->label('NOMBRE DEL PROPIETARIO')->textInput([
                'class' => 'form-control',
                'placeholder' => 'ID del usuario propietario',
            ]) ?>
        </div>
       
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <?= $form->field($model, 'por_venta')->label('PORCENTAJE POR VENTA')->textInput([
                'class' => 'form-control',
                'placeholder' => '% Venta',
                'type' => 'number',
                'step' => '0.01',
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'por_asesor')->label('PORCENTAJE DE ASESORÍA')->textInput([
                'class' => 'form-control',
                'placeholder' => '% Asesoría',
                'type' => 'number',
                'step' => '0.01',
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'por_cobranza')->label('PORCENTAJE POR COBRANZA')->textInput([
                'class' => 'form-control',
                'placeholder' => '% Cobranza',
                'type' => 'number',
                'step' => '0.01',
            ]) ?>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <?= $form->field($model, 'por_post_venta')->label('PORCENTAJE POST VENTA')->textInput([
                'class' => 'form-control',
                'placeholder' => '% Post-Venta',
                'type' => 'number',
                'step' => '0.01',
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'por_agente')->label('PORCENTAJE DE AGENCIA')->textInput([
                'class' => 'form-control',
                'placeholder' => '% Agente',
                'type' => 'number',
                'step' => '0.01',
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'por_max')->label('PORCENTAJE MÁXIMO')->textInput([
                'class' => 'form-control',
                'placeholder' => '% Máximo',
                'type' => 'number',
                'step' => '0.01',
            ]) ?>
        </div>
    </div>


    <div class="form-group text-end mt-4">
        <?= Html::submitButton('<i class="fas fa-save"></i> GUARDAR', ['class' => 'btn btn-outline-primary btn-md']) ?>

        <?= Html::a('CANCELAR', ['index'], ['class' => 'btn btn-md btn-outline-warning ms-2']); ?>

        <?php if ($model->isNewRecord) { ?>
            <?= Html::a('LIMPIAR', ['create'], ['class' => 'btn btn-md btn-outline-dark ms-2']); ?>
        <?php } ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>



