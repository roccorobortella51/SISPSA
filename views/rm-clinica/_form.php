<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\RmClinica */
/* @var $form yii\widgets\ActiveForm */

// Asegura que las variables existan, proporcionando valores por defecto si no están establecidas
$listaEstados = $listaEstados ?? [];
$listaEstatus = $listaEstatus ?? [];

// Determina el modo (creación o edición) para el autofocus dinámico y otros elementos
$mode = $mode ?? 'create';
$isNewRecord = $model->isNewRecord ?? true;

?>

<?php
// ActiveForm sin la clase 'form-horizontal' para labels arriba
$form = ActiveForm::begin([
    'id' => 'form-clinica', // ID para el JavaScript
]);
?>

<?php if (!$isNewRecord): ?>
    <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
<?php endif; ?>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <?php
            echo $form->field($model, 'rif')->begin();
            ?>

            <?= Html::activeLabel($model, 'rif') ?> <span class="text-danger">*</span>

            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">J-</span>
                </div>
                <?= Html::activeTextInput($model, 'rif', [
                    'maxlength' => 12,
                    'placeholder' => 'Ingrese solo los números (Ej: 1234567890)',
                    'class' => 'form-control text-center',
                    'pattern' => '[0-9]{10,12}',
                    'title' => 'Ingrese entre 10 y 12 dígitos numéricos (sin guiones ni letras)',
                    'required' => true,
                    'autofocus' => ($mode == 'create'),
                    'id' => 'rmclinica-rif-input',
                ]) ?>
            </div>

            <small class="form-text text-muted">Ingrese de 10 a 12 dígitos numéricos (la J- se muestra automáticamente).</small>

            <?php
            echo Html::error($model, 'rif', ['class' => 'invalid-feedback d-block']);
            echo $form->field($model, 'rif')->end();
            ?>
        </div>
    </div>

    <div class="col-md-4">
        <?= $form->field($model, 'nombre')->textInput([
            'maxlength' => true,
            'placeholder' => 'Nombre comercial de la clínica',
            'class' => 'form-control text-center',
            'required' => true,
        ])->label('Nombre <span class="text-danger">*</span>') ?>
    </div>

    <div class="col-md-4">
        <?= $form->field($model, 'estado')->widget(Select2::classname(), [
            'data' => $listaEstados,
            'options' => ['placeholder' => 'Seleccione el estado donde se ubica'],
            'pluginOptions' => [
                'allowClear' => true
            ],
            'theme' => Select2::THEME_BOOTSTRAP,
            'options' => [
                'class' => 'text-center',
                'required' => true,
            ]
        ])->label('Estado <span class="text-danger">*</span>') ?>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <?= $form->field($model, 'telefono')->textInput([
            'maxlength' => true,
            'placeholder' => 'Número de teléfono (Ej: 0212-XXX-XXXX)',
            'class' => 'form-control text-center',
            'required' => true,
        ])->label('Teléfono <span class="text-danger">*</span>') ?>
    </div>

    <div class="col-md-4">
        <?= $form->field($model, 'correo')->textInput([
            'maxlength' => true,
            'placeholder' => 'Correo electrónico de contacto (Ej: info@clinica.com)',
            'class' => 'form-control text-center',
            'type' => 'email',
            'required' => true,
        ])->label('Correo <span class="text-danger">*</span>') ?>
    </div>

    <div class="col-md-4">
        <?= $form->field($model, 'estatus')->widget(Select2::classname(), [
            'data' => $listaEstatus,
            'options' => ['placeholder' => 'Seleccione el estatus de la clínica'],
            'pluginOptions' => [
                'allowClear' => true
            ],
            'theme' => Select2::THEME_BOOTSTRAP,
            'options' => [
                'class' => 'text-center',
                'required' => true,
            ]
        ])->label('Estatus <span class="text-danger">*</span>') ?>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <?= $form->field($model, 'webpage')->textInput([
            'maxlength' => true,
            'placeholder' => 'Dirección del sitio web (URL completa)',
            'class' => 'form-control text-center',
            'type' => 'url',
        ])->label('Página Web') ?>
        <small class="form-text text-muted">Opcional</small>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <?php
            echo $form->field($model, 'rs_instagram')->begin();
            ?>
            <?= Html::activeLabel($model, 'rs_instagram') ?>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">@</span>
                </div>
                <?= Html::activeTextInput($model, 'rs_instagram', [
                    'maxlength' => true,
                    'placeholder' => 'usuario',
                    'class' => 'form-control text-center',
                ]) ?>
            </div>
            <small class="form-text text-muted">Opcional</small>
            <?= Html::error($model, 'rs_instagram', ['class' => 'invalid-feedback d-block']) ?>
            <?php echo $form->field($model, 'rs_instagram')->end(); ?>
        </div>
    </div>

    <div class="col-md-4">
        <?= $form->field($model, 'codigo_clinica')->textInput([
            'maxlength' => true,
            'placeholder' => 'Código interno asignado a la clínica',
            'class' => 'form-control text-center',
            'required' => true,
        ])->label('Código de la Clínica <span class="text-danger">*</span>') ?>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <?= $form->field($model, 'direccion')->textArea([
            'rows' => 3,
            'placeholder' => 'Dirección completa de la clínica, incluyendo municipio y parroquia',
            'class' => 'form-control text-center',
            'required' => true,
        ])->label('Dirección <span class="text-danger">*</span>') ?>
    </div>
</div>

<?php // Eliminado: el div de cierre huérfano que estaba aquí ?>
<?php if (!$isNewRecord): // Mostrar solo en modo edición ?>
    <?php // Si en el futuro necesitas un div aquí, asegúrate de abrirlo antes. ?>
<?php endif; ?>

<div class="form-group text-right mt-4 text-center">
    <?= Html::a('<i class="fa fa-times"></i> Cancelar', ['index'], ['class' => 'btn btn-secondary ml-3']) ?>

    <?= Html::submitButton(
        '<i class="fas ' . (($mode == 'create') ? 'fa-plus-circle' : 'fa-save') . '"></i> ' .
        (($mode == 'create') ? 'Crear Clínica' : 'Guardar Cambios'),
        ['class' => 'btn btn-success ml-3', 'id' => 'submitFormBtn']
    ) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
// Script JavaScript para manejar la "J-" fija del RIF
// Este bloque estaba duplicado, he dejado solo uno.
$js = <<<JS
$(document).ready(function() {
    var rifCompletoDelModelo = $('#rmclinica-rif').val();
    if (rifCompletoDelModelo && rifCompletoDelModelo.startsWith('J-')) {
        $('#rmclinica-rif-input').val(rifCompletoDelModelo.substring(2)); // Mostrar solo números
    }

    $('#form-clinica').on('beforeSubmit', function(e) {
        var valorNumericoIngresado = $('#rmclinica-rif-input').val();
        if (valorNumericoIngresado && !valorNumericoIngresado.startsWith('J-')) {
            $('#rmclinica-rif').val('J-' + valorNumericoIngresado); // Añadir J- al valor final
        }
        return true;
    });
});
JS;

?>