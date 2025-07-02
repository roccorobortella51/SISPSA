
<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2; // Para los selectores de estado y estatus
use yii\widgets\MaskedInput; // Para campos con máscaras como RIF y teléfono
use app\components\UserHelper;
use yii\helpers\Url;



// Asegúrate de que estas variables siempre tengan un valor para evitar errores
// si el controlador no las pasa por alguna razón (aunque el controlador sí las pasa).
$mode = $mode ?? 'create'; // Por defecto es 'create' si no se especifica
$isNewRecord = $isNewRecord ?? true; // Por defecto es true para este formulario

if ($model->isNewRecord) {
    $readOnly = false;
}else{
    $readOnly = true;
}
?>

<div class="rm-clinica-form">
    <div class="ms-panel-body">
    <?php $form = ActiveForm::begin(); ?>

     <?php if (!$model->isNewRecord) { ?>
        <div class="row mb-3"> 
            
            <div class="col-md-6">
                <div class="ms-panel ms-widget ms-identifier-widget bg-info panel-clickable" data-href="<?= Url::to(['update', 'id' => $model->id]) ?>">
                    <div class="ms-panel-header header-mini" style="padding-top: 35px; padding-bottom: 35px; text-align: center">
                        <h6 style="margin: 0;"> 
                            <?= Html::a(
                                'ACTUALIZACIÓN DE AGENCIA',
                                ['update', 'id' => $model->id],
                                ['class' => 'text-white']
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
                                ['class' => 'text-white']
                            ) ?>
                        </h6>
                    </div>
                </div>
            </div>
            
        </div>
    <?php } ?>


    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'nom')->label('NOMBRE DE LA AGENCIA')->textInput([
                'maxlength' => true,
                'class' => 'form-control form-control-lg',
                'placeholder' => 'Nombre completo del agente',
                'autofocus' => true,
            ]) ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'idusuariopropietario')->label('NOMBRE DEL PROPIETARIO')->widget(Select2::classname(), [
                            'data' => UserHelper::getAgentesList(),
                            'options' => [
                                'placeholder' => 'Seleccione',
                                'class' => 'form-control form-control-lg'
                            ],
                            'pluginOptions' => [
                                'allowClear' => false,
                            ],
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
                'class' => 'form-control form-control-lg'
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'por_asesor')->label('PORCENTAJE DE ASESORÍA')->textInput([
                'class' => 'form-control',
                'placeholder' => '% Asesoría',
                'type' => 'number',
                'step' => '0.01',
                'class' => 'form-control form-control-lg'
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'por_cobranza')->label('PORCENTAJE POR COBRANZA')->textInput([
                'class' => 'form-control',
                'placeholder' => '% Cobranza',
                'type' => 'number',
                'step' => '0.01',
                'class' => 'form-control form-control-lg'
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
                'class' => 'form-control form-control-lg'
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'por_agente')->label('PORCENTAJE DE AGENCIA')->textInput([
                'class' => 'form-control',
                'placeholder' => '% Agente',
                'type' => 'number',
                'step' => '0.01',
                'class' => 'form-control form-control-lg'
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'por_max')->label('PORCENTAJE MÁXIMO')->textInput([
                'class' => 'form-control',
                'placeholder' => '% Máximo',
                'type' => 'number',
                'step' => '0.01',
                'class' => 'form-control form-control-lg'
            ]) ?>
        </div>

        <div class="col-md-12">
        <div class="form-group text-rigth mt-4" style="margin-right:10px;">
            <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success btn-lg']) ?>
            <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-lg btn-warning']); ?>
        </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
    </div>
</div>

