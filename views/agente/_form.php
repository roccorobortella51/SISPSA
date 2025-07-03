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
            <div class="row row-cols-1 row-cols-md-2 g-3 mb-3">
                <div class="col">
                    <?= Html::a(
                        '<i class="fas fa-undo"></i> Volver',
                        ['index'], // Ruta para "Volver"
                        ['class' => 'btn btn-primary btn-lg w-100']
                    ) ?>
                </div>

                <div class="col">
                    <?= Html::a(
                        '<i class="fas fa-users"></i> FUERZA DE VENTA',
                        ['agente-fuerza/index-by-agente', 'agente_id' => $model->id],
                        ['class' => 'btn btn-primary btn-lg w-100']
                    ) ?>
                </div>
            </div>
        <?php } ?>
        <br>

        <div class="row mb-3">
            <div class="col-md-4">
                <?= $form->field($model, 'nom')->label('NOMBRE DE LA AGENCIA')->textInput([
                    'maxlength' => true,
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'Nombre completo del agente',
                    'autofocus' => true,
                ]) ?>
            </div>

            <div class="col-md-4">
                <?= $form->field($model, 'sudeaseg')->label('CÓDIGO SUDEASEG')->textInput([
                    'maxlength' => true, // Asegúrate que el 'maxlength' aquí coincida con el VARCHAR de tu BD y el 'max' en rules()
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'Ingrese el código SUDEASEG',
                ]) ?>
            </div>
            <div class="col-md-4">
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
        </div>

        <div class="row mt-4">
            <div class="col-12 d-flex justify-content-start gap-4"> <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success btn-lg']) ?>
                <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-lg btn-warning']) ?>
                <?= Html::button('<i class="fas fa-sync-alt"></i> Refrescar', ['class' => 'btn btn-info btn-lg', 'id' => 'btn-refrescar-form']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
// Añadir JavaScript para el botón Refrescar
$js = <<<JS
$('#btn-refrescar-form').on('click', function() {
    // Busca el formulario actual por su ID y lo resetea
    // Si tu ActiveForm tiene un ID específico (ej: 'agente-form'), puedes usarlo:
    // $('#agente-form')[0].reset();
    // Si no tiene ID específico, ActiveForm.begin() a menudo genera uno como 'active-form'
    // O puedes apuntar al formulario más cercano al botón
    $(this).closest('form')[0].reset(); 
    // Si necesitas recargar la página para limpiar realmente los datos del modelo:
    // window.location.reload(); 
});
JS;
$this->registerJs($js);
?>