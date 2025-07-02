<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2; // Para los selectores de estado y estatus
use yii\widgets\MaskedInput; // Para campos con máscaras como RIF y teléfono
use app\components\UserHelper;
use kartik\widgets\SwitchInput;

/** @var yii\web\View $this */
/** @var app\models\UserDatos $model */
/** @var yii\widgets\ActiveForm $form */
?>

<style>
.nav-tabs .nav-link.active {
    background-color: #007bff !important; /* Bootstrap primary blue */
    color: white !important;
    border-color: #007bff #007bff #fff !important;
}
</style>

<?php
$js = <<<JS
$('.nav-tabs .nav-link').click(function(e) {
    e.preventDefault();

    // Remove active class from all tab links and panes
    $('.nav-tabs .nav-link').removeClass('active');
    $('.tab-content .tab-pane').removeClass('active');

    // Add active class to clicked tab and corresponding pane
    $(this).addClass('active');
    var targetId = $(this).attr('href');
    $(targetId).addClass('active');
});
JS;
$this->registerJs($js);
?>

<div class="user-datos-form">
    <?php $form = ActiveForm::begin(); ?>
    <ul class="nav nav-tabs d-flex nav-justified mb-4" role="tablist">
        <li role="presentation"><a href="#tab13" aria-controls="tab13" class="nav-link active" role="tab" data-toggle="tab">Datos Personales</a></li>
        <li role="presentation"><a href="#tab14" aria-controls="tab14" class="nav-link" role="tab" data-toggle="tab">Fotos</a></li>
        <li role="presentation"><a href="#tab15" aria-controls="tab15" class="nav-link" role="tab" data-toggle="tab">Contactos</a></li>
        <li role="presentation"><a href="#tab16" aria-controls="tab16" class="nav-link" role="tab" data-toggle="tab">Declaracion de Salud</a></li>
        <li role="presentation"><a href="#tab17" aria-controls="tab17" class="nav-link" role="tab" data-toggle="tab">Activa tu Afiliacion</a></li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="tab13">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'codigoAsesor')->textInput() ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'asesor_id')->textInput() ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'email')->textInput() ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'telefono')->textInput() ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'nombres')->textInput() ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'apellidos')->textInput() ?>
                </div>
            </div>
            <div class="row ">
                <div class="col-md-3">
    <label style="font-weight: bold; font-size: 14px;">Cedula</label>
    <div class="form-inline d-flex"> <?php // Añadir d-flex para usar flexbox ?>
        <?= $form->field($model, 'tipo_cedula', ['options' => ['class' => 'form-group mr-2', 'style' => 'width: 25%;']])->textInput()->label(false) ?>
        <?= $form->field($model, 'cedula', ['options' => ['class' => 'form-group', 'style' => 'width: 75%;']])->textInput()->label(false) ?>
    </div>
</div>
                <div class="col-md-3">
                    <?= $form->field($model, 'fechanac')->textInput() ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'sexo')->textInput() ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'tipo_sangre')->textInput() ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'estado')->textInput() ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'municipio')->textInput() ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'parroquia')->textInput() ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'ciudad')->textInput() ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($model, 'direccion')->textarea() ?>
                </div>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="tab14">
            <p> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam urna nunc, congue nec laoreet sed, maximus non massa. Fusce vestibulum vel risus vitae tincidunt. </p>
            <p> Cras egestas nisi vel tempor dignissim. Ut condimentum iaculis ex nec ornare. Vivamus sit amet elementum ante. Fusce eget erat volutpat </p>
            <p> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam urna nunc, congue nec laoreet sed, maximus non massa. Fusce vestibulum vel risus vitae tincidunt. </p>
        </div>
        <div role="tabpanel" class="tab-pane" id="tab15">
            <p> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam urna nunc, congue nec laoreet sed, maximus non massa. Fusce vestibulum vel risus vitae tincidunt. </p>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
