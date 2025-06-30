
<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2; // Para los selectores de estado y estatus
use yii\widgets\MaskedInput; // Para campos con máscaras como RIF y teléfono
use app\components\UserHelper;



// Asegúrate de que estas variables siempre tengan un valor para evitar errores
// si el controlador no las pasa por alguna razón (aunque el controlador sí las pasa).
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
                                echo Html::a(
                                    'Baremo',
                                    ['baremo/index', 'clinica_id' => $model->id], // ¡CORRECCIÓN AQUÍ!
                                    ['class' => 'text-white'] // Ajusta la clase si el texto se ve mal
                                );
                            ?>
                        </h6>
                    </div>
                    <div class="ms-panel-body">
                        <div class="text-center">
                            <i class="flaticon-information"></i>
                            <p>Gestión de los baremos para servicios médicos.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="ms-panel ms-widget ms-identifier-widget bg-info">
                    <div class="ms-panel-header header-mini">
                        <h6>
                            <?php
                                // Enlace para Baremo
                                echo Html::a(
                                    'Planes',
                                    ['planes/index', 'clinica_id' => $model->id], // ¡CORRECCIÓN AQUÍ!
                                    ['class' => 'text-white'] // Ajusta la clase si el texto se ve mal
                                );
                            ?>
                        </h6>
                    </div>
                    <div class="ms-panel-body">
                        <div class="text-center">
                            <i class="flaticon-information"></i>
                            <p>Administración y configuración de los diferentes planes.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="ms-panel ms-widget ms-identifier-widget bg-info">
                    <div class="ms-panel-header header-mini">
                        <h6>
                            <?php
                                // Enlace para Baremo
                                echo Html::a(
                                    'Afiliados',
                                    ['user-datos/index', 'clinica_id' => $model->id], // ¡CORRECCIÓN AQUÍ!
                                    ['class' => 'text-white'] // Ajusta la clase si el texto se ve mal
                                );
                            ?>
                        </h6>
                    </div>
                    <div class="ms-panel-body">
                        <div class="text-center">
                            <i class="flaticon-information"></i>
                            <p>Registro y gestión de todos los miembros y beneficiarios afiliados.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'nombre')->label('NOMBRE DE LA CLÍNICA')->textInput([
                'maxlength' => true,
                'class' => 'form-control form-control-lg', // Usamos 'form-control' para tamaño estándar
                'placeholder' => 'Nombre completo de la Clínica',
                'label' => 'Nombre de la agencia',
                'autofocus' => true,
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'rif')->widget(MaskedInput::class, [
                'mask' => 'J-99999999-9',
                'options' => [
                    'placeholder' => 'J-XXXXXXXX-X',
                    'class' => 'form-control form-control-lg',
                    'maxlength' => true,
                ]
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'telefono')->widget(MaskedInput::class, [
                'mask' => '(9999) 999-9999',
                'options' => [
                    'placeholder' => '(XXXX) XXX-XXXX',
                    'class' => 'form-control form-control-lg',
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
            'class' => 'form-control form-control-lg',
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'estado')->widget(Select2::classname(), [
                'data' => UserHelper::getEstadosList(),
                'options' => [
                    'placeholder' => 'Seleccione',
                                'class' => 'form-control form-control-lg',
                            ],
                            'pluginOptions' => [
                                'allowClear' => false,
                            ],
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'direccion')->textInput([
                'maxlength' => true,
                'placeholder' => 'Ingrese la dirección completa',
                'class' => 'form-control form-control-lg',
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'webpage')->textInput(['maxlength' => true, 'placeholder' => 'Ej: www.ejemplo.com', 'class' => 'form-control form-control-lg',]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'rs_instagram')->textInput(['maxlength' => true, 'placeholder' => 'Ej: @tu_clinica', 'class' => 'form-control form-control-lg',]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'codigo_clinica')->textInput(['maxlength' => true, 'placeholder' => 'Código interno de clínica', 'class' => 'form-control form-control-lg',]) ?>
        </div>
    </div>
    
    <div class="form-group text-rigth mt-4">
        <?= Html::submitButton('<i class="fas fa-save"></i> Guardar Clínica', ['class' => 'btn btn-success btn-lg']) ?>
        <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-lg btn-warning']); ?>

        <?php if ($model->isNewRecord) { echo Html::a('Limpiar', ['create'], ['class' => 'btn btn-lg btn-outline-dark']); } ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
