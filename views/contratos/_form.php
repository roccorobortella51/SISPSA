<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Url; // Necesario para el botón de "Volver"

/* @var $this yii\web\View */
/* @var $model app\models\Contratos */
/* @var $form yii\bootstrap4\ActiveForm */
?>

<div class="contratos-form p-3">

    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'shadow p-4 rounded-3 bg-light'], // Estilo para el formulario
        'fieldConfig' => [
            'template' => "{label}\n{input}\n{hint}\n{error}",
            'options' => ['class' => 'form-group mb-3'], // Margen inferior para cada campo
            'labelOptions' => ['class' => 'form-label text-primary fw-bold'],
        ],
    ]); ?>

    <h4 class="mb-4 text-info border-bottom pb-2"><i class="fas fa-info-circle me-2"></i> Información General del Contrato</h4>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'plan_id')->textInput([
                'placeholder' => 'Ingrese el ID del plan',
                'class' => 'form-control rounded-pill'
            ])->hint('Sugiere usar un DropdownList para seleccionar el plan existente.') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'ente_id')->textInput([
                'placeholder' => 'Ingrese el ID del ente',
                'class' => 'form-control rounded-pill'
            ])->hint('Sugiere usar un DropdownList para seleccionar el ente existente.') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'clinica_id')->textInput([
                'placeholder' => 'Ingrese el ID de la clínica',
                'class' => 'form-control rounded-pill'
            ])->hint('Sugiere usar un DropdownList para seleccionar la clínica existente.') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'user_id')->textInput([
                'placeholder' => 'Ingrese el ID del usuario',
                'class' => 'form-control rounded-pill'
            ])->hint('Sugiere usar un DropdownList para seleccionar el usuario existente.') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'nrocontrato')->textInput([ // Cambiado a textInput
                'placeholder' => 'Ingrese el número de contrato',
                'class' => 'form-control rounded-pill'
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'sucursal')->textInput([ // Cambiado a textInput
                'placeholder' => 'Ingrese la sucursal',
                'class' => 'form-control rounded-pill'
            ]) ?>
        </div>
    </div>

    <h4 class="mt-4 mb-4 text-info border-bottom pb-2"><i class="fas fa-calendar-alt me-2"></i> Fechas del Contrato</h4>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'fecha_ini')->textInput([
                'type' => 'date', // Tipo de entrada para fecha
                'class' => 'form-control rounded-pill'
            ])->hint('Considera usar un widget de DatePicker para una mejor experiencia.') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'fecha_ven')->textInput([
                'type' => 'date', // Tipo de entrada para fecha
                'class' => 'form-control rounded-pill'
            ])->hint('Considera usar un widget de DatePicker para una mejor experiencia.') ?>
        </div>
    </div>

    <h4 class="mt-4 mb-4 text-info border-bottom pb-2"><i class="fas fa-dollar-sign me-2"></i> Detalles de Pago</h4>
    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'monto')->textInput([
                'type' => 'number', // Tipo de entrada para número
                'step' => '0.01', // Para permitir decimales
                'placeholder' => '0.00',
                'class' => 'form-control rounded-pill'
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'frecuencia_pago')->textInput([ // Cambiado a textInput
                'placeholder' => 'Ej: Mensual, Anual',
                'class' => 'form-control rounded-pill'
            ])->hint('Podría ser un DropdownList con opciones predefinidas.') ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'moneda')->textInput([ // Cambiado a textInput
                'placeholder' => 'Ej: USD, EUR, VEF',
                'class' => 'form-control rounded-pill'
            ])->hint('Podría ser un DropdownList con opciones predefinidas.') ?>
        </div>
    </div>

    <h4 class="mt-4 mb-4 text-info border-bottom pb-2"><i class="fas fa-clipboard-list me-2"></i> Estado y Documentación</h4>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'estatus')->textarea([ // Mantener como textarea si es texto descriptivo
                'rows' => 3, // Reducir filas si no es muy largo
                'placeholder' => 'Estado actual del contrato',
                'class' => 'form-control rounded-3'
            ])->hint('Podría ser un DropdownList con estatus predefinidos.') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'pdf')->textInput([ // Cambiado a textInput, podría ser fileInput
                'placeholder' => 'URL o ruta del pdf',
                'class' => 'form-control rounded-pill'
            ])->hint('Si es una subida de archivo, cambiar a fileInput y configurar la subida.') ?>
        </div>
    </div>

    <h4 class="mt-4 mb-4 text-info border-bottom pb-2"><i class="fas fa-history me-2"></i> Información de Auditoría y Anulación</h4>
    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'created_at')->textInput([
                'type' => 'datetime-local', // Tipo de entrada para fecha y hora
                'class' => 'form-control rounded-pill',
                'readonly' => true // Generalmente este campo se llena automáticamente
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'updated_at')->textInput([
                'type' => 'datetime-local', // Tipo de entrada para fecha y hora
                'class' => 'form-control rounded-pill',
                'readonly' => true // Generalmente este campo se llena automáticamente
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'deleted_at')->textInput([
                'type' => 'datetime-local', // Tipo de entrada para fecha y hora
                'class' => 'form-control rounded-pill',
                'readonly' => true // Generalmente este campo se llena automáticamente
            ])->hint('Campo para borrado lógico, podría estar oculto.') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'anulado_por')->textInput([
                'placeholder' => 'Usuario que anuló (si aplica)',
                'class' => 'form-control rounded-pill'
            ])->hint('Este campo y los siguientes podrían ser condicionales al estatus del contrato.') ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'anulado_fecha')->textInput([
                'type' => 'date', // Tipo de entrada para fecha
                'class' => 'form-control rounded-pill'
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'anulado_motivo')->textarea([
                'rows' => 3,
                'placeholder' => 'Motivo de anulación (si aplica)',
                'class' => 'form-control rounded-3'
            ]) ?>
        </div>
    </div>

    <div class="form-group mt-4 d-flex justify-content-between">
        <?= Html::a('<i class="fas fa-undo me-2"></i> Volver', ['index', 'user_id' => $model->user_id ?? null], [ // user_id podría ser null en create
            'class' => 'btn btn-secondary rounded-pill px-4 shadow-sm'
        ]) ?>
        <?= Html::submitButton('<i class="fas fa-save me-2"></i> Guardar Contrato', [
            'class' => 'btn btn-success rounded-pill px-4 shadow-sm'
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
