
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

    <?php $form = ActiveForm::begin([]); ?>
    <?php if (!$model->isNewRecord) { ?>
        <div class="row">
            <div class="col-md-4">
                <div class="ms-panel ms-widget ms-identifier-widget bg-info">
                    <div class="ms-panel-header header-mini">
                        <h6>
                            <?php
                            // Enlace para Baremo
                            echo Html::a('Baremo', ['baremo/index'], ['class' => 'text-white']); // Ajusta la clase si el texto se ve mal
                            ?>
                        </h6>
                    </div>
                    <div class="ms-panel-body">
                        <div class="text-center">
                            <i class="flaticon-information"></i>
                            <p>Gestión de los baremos para servicios médicos y honorarios profesionales.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="ms-panel ms-widget ms-identifier-widget bg-info">
                    <div class="ms-panel-header header-mini">
                        <h6>
                            <?php
                            // Enlace para Planes
                            echo Html::a('Planes', ['planes/index'], ['class' => 'text-white']); // Ajusta la clase si el texto se ve mal
                            ?>
                        </h6>
                    </div>
                    <div class="ms-panel-body">
                        <div class="text-center">
                            <i class="flaticon-information"></i>
                            <p>Administración y configuración de los diferentes planes de seguros y beneficios.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="ms-panel ms-widget ms-identifier-widget bg-info">
                    <div class="ms-panel-header header-mini">
                        <h6>
                            <?php
                            // Enlace para Afiliados
                            echo Html::a('Afiliados', ['afiliados/index'], ['class' => 'text-white']); // Ajusta la clase si el texto se ve mal
                            ?>
                        </h6>
                    </div>
                    <div class="ms-panel-body">
                        <div class="text-center">
                            <i class="flaticon-information"></i>
                            <p>Registro y gestión de todos los miembros y beneficiarios afiliados al sistema.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

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
    
    <div class="form-group text-rigth mt-4">
        <?= Html::submitButton('<i class="fas fa-save"></i> Guardar Clínica', ['class' => 'btn btn-success btn-lg']) ?>
        <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-lg btn-warning']); ?>

        <?php if ($model->isNewRecord) { echo Html::a('Limpiar', ['create'], ['class' => 'btn btn-lg btn-outline-warning']); } ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
