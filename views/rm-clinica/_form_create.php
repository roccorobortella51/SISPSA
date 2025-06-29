<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2; // Para los selectores de estado y estatus
use yii\widgets\MaskedInput; // Para campos con máscaras como RIF y teléfono

/** @var yii\web\View $this */
/** @var app\models\RmClinica $model */
/** @var yii\widgets\ActiveForm $form */
/** @var array $listaEstados */ // Espera la lista de estados para el Select2
/** @var array $listaEstatus */ // Espera la lista de estatus para el Select2
/** @var string $mode */ // Para saber si es 'create' o 'edit'
/** @var bool $isNewRecord */ // Para saber si es un nuevo registro

// Asegúrate de que estas variables siempre tengan un valor para evitar errores
// si el controlador no las pasa por alguna razón (aunque el controlador sí las pasa).
$listaEstados = $listaEstados ?? [];
$listaEstatus = $listaEstatus ?? [];
$mode = $mode ?? 'create'; // Por defecto es 'create' si no se especifica
$isNewRecord = $isNewRecord ?? true; // Por defecto es true para este formulario

?>

<div class="rm-clinica-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-clinica-create', // Un ID único para este formulario de creación
        'enableAjaxValidation' => true, // Habilita la validación AJAX si la configuras en el modelo
        'enableClientValidation' => true, // Habilita la validación del lado del cliente
    ]); ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'nombre')->textInput([
                'maxlength' => true,
                'autofocus' => true,
                'placeholder' => 'Ingrese el nombre de la clínica',
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'rif')->widget(MaskedInput::class, [
                'mask' => 'J-99999999-9',
                'options' => [
                    'placeholder' => 'J-XXXXXXXX-X',
                    'class' => 'form-control',
                    'maxlength' => true,
                ]
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'telefono')->widget(MaskedInput::class, [
                'mask' => '(9999) 999-9999',
                'options' => [
                    'placeholder' => '(XXXX) XXX-XXXX',
                    'class' => 'form-control',
                    'maxlength' => true,
                ]
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
           <?= $form->field($model, 'correo')->textInput([
            'maxlength' => true,
            'placeholder' => 'Ingrese el correo electrónico',
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'estado')->widget(Select2::classname(), [
                'data' => $listaEstados,
                'options' => [
                    'placeholder' => 'Seleccione un estado...',
                    'class' => 'form-control-lg',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'estatus')->widget(Select2::classname(), [
                'data' => $listaEstatus, // Asegúrate de que esta línea esté, faltaba en tu código
                'options' => [
                    'placeholder' => 'Seleccione un estatus...',
                    'class' => 'form-control-lg',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'direccion')->textarea([
                'rows' => 3, // Número de filas visibles para el textarea
                'maxlength' => true,
                'placeholder' => 'Ingrese la dirección completa',
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'webpage')->textInput(['maxlength' => true, 'placeholder' => 'Ej: www.ejemplo.com']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'rs_instagram')->textInput(['maxlength' => true, 'placeholder' => 'Ej: @tu_clinica']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'codigo_clinica')->textInput(['maxlength' => true, 'placeholder' => 'Código interno de clínica']) ?>
        </div>
    </div>
    
    <div class="form-group text-center mt-4">
        <?= Html::submitButton('<i class="fas fa-save"></i> Guardar Clínica', ['class' => 'btn btn-success btn-lg']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
// Bloque de CSS específico para este formulario si necesitas ajustar el tamaño de fuente, etc.
$cssFormCreate = <<<CSS
#form-clinica-create .form-group label,
#form-clinica-create .form-control,
#form-clinica-create .select2-container .select2-selection__rendered,
#form-clinica-create .select2-container .select2-results__option,
#form-clinica-create .form-text.text-muted {
    font-size: 1.1rem !important; /* Ejemplo: aumenta el tamaño de la fuente */
}
/* Puedes añadir más estilos específicos para los campos del formulario aquí */
CSS;
$this->registerCss($cssFormCreate);
?>