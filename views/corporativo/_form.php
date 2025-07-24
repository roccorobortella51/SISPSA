<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput; // Para RIF y teléfonos
use kartik\depdrop\DepDrop; // Para Estado, Municipio, Parroquia, Ciudad
use yii\helpers\ArrayHelper;
use yii\helpers\Url; // Para generar URLs en DepDrop

use app\models\RmClinica;
use app\models\User;
use app\models\UserDatos;
use app\components\UserHelper;
use app\models\RmEstado;
use app\models\RmMunicipio;
use app\models\RmParroquia;
use app\models\RmCiudad;


/** @var yii\web\View $this */
/** @var app\models\Corporativo $model */
/** @var yii\widgets\ActiveForm $form */

// Variables de control similares a rm_clinica
$readOnly = false;
if (!$model->isNewRecord) {
    $readOnly = true; // Si es un registro existente, ciertos campos pueden ser de solo lectura
}

?>

<div class="corporativo-form">

    <?php $form = ActiveForm::begin([]); ?>

    <?php /*
    <?php if (!$model->isNewRecord) { ?>
    <div class="row row-cols-1 row-cols-md-4 g-3 mb-3">
        <div class="col">
            <?= Html::a('<i class="fas fa-chart-line"></i> Reportes', ['corporativo/reports', 'id' => $model->id], ['class' => 'btn btn-info btn-lg w-100']) ?>
        </div>
        <div class="col">
            <?= Html::a('<i class="fas fa-history"></i> Historial', ['corporativo/history', 'id' => $model->id], ['class' => 'btn btn-secondary btn-lg w-100']) ?>
        </div>
        <div class="col">
            // Puedes añadir otros botones específicos para Corporativos aquí
        </div>
        <div class="col">
            // Y otro más
        </div>
    </div>
    <?php } ?>
    */ ?>

    <h3 class="box-title">Datos Generales del Corporativo</h3>
    <hr>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <?= $form->field($model, 'nombre')->textInput([
                'maxlength' => true,
                'class' => 'form-control form-control-lg',
                'placeholder' => 'Nombre completo del Corporativo',
                'autofocus' => true,
                'readonly' => $readOnly // Ejemplo de campo de solo lectura en edición
            ]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'rif')->widget(MaskedInput::class, [
                'mask' => 'J-99999999-9',
                'options' => [
                    'placeholder' => 'J-XXXXXXXX-X',
                    'class' => 'form-control form-control-lg',
                    'maxlength' => true,
                    'readonly' => $readOnly
                ]
            ]) ?>
        </div>
        <div class="col-md-3">
    <?= $form->field($model, 'telefono')->textInput([
        'maxlength' => 11, // Asegura que solo se puedan ingresar 11 dígitos, ya que el patrón lo espera así.
        'placeholder' => 'Ej: 04121234567', // Cambia el placeholder a un formato sin máscara
        'class' => 'form-control form-control-lg',
        // Puedes añadir 'type' => 'tel' para móviles, aunque no es estrictamente necesario
        'type' => 'tel', 
    ]) ?>
</div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <?= $form->field($model, 'email')->textInput([
                'maxlength' => true,
                'placeholder' => 'Ingrese el correo electrónico',
                'class' => 'form-control form-control-lg',
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'estatus')->widget(Select2::classname(), [
                'data' => [
                    'Activo' => 'Activo',
                    'Inactivo' => 'Inactivo',
                    'Pendiente' => 'Pendiente',
                ],
                'options' => [
                    'placeholder' => 'Seleccione el estatus...',
                    'class' => 'form-control form-control-lg',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ]) ?>
        </div>
    </div>

    <h3 class="box-title mt-4">Ubicación Geográfica</h3>
<hr>

<div class="row">
        
 
        <div class="col-md-3">
            <?= $form->field($model, 'estado')->widget(Select2::classname(), [
                'data' => UserHelper::getEstadosList(),
                'options' => [
                    'placeholder' => 'Seleccione un estado...',
                    'class' => 'form-control  form-control-lg',
                    'id' => 'estado_id'
                ],
                'pluginOptions' => [
                    'allowClear' => false,
                ],
            ]);
            ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'municipio')->widget(DepDrop::classname(), [
                'type' => DepDrop::TYPE_SELECT2,
                'options' => [
                    'id' => 'municipio_id',
                    'placeholder' => 'Seleccione un municipio...',
                    'class' => 'form-control  form-control-lg',
                ],
                'pluginOptions' => [
                    'depends' => ['estado_id'],
                    'url' => Url::to(['/site/municipio']), 
                    'initialize' => true,
                ]
            ]);
            ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'parroquia')->widget(DepDrop::classname(), [
                'type' => DepDrop::TYPE_SELECT2,
                'options' => [
                    'id' => 'parroquia_id',
                    'placeholder' => 'Seleccione una parroquia...',
                    'class' => 'form-control  form-control-lg',
                ],
                'pluginOptions' => [
                    'depends' => ['municipio_id'],
                    'url' => Url::to(['/site/parroquia']), 
                    // 'initValueText' => isset($parroquiaName) ? $parroquiaName : '',
                ]
            ]);
            ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'ciudad')->widget(DepDrop::classname(), [
                'type' => DepDrop::TYPE_SELECT2,
                'options' => [
                    'id' => 'ciudad_id',
                    'placeholder' => 'Seleccione una ciudad...',
                    'class' => 'form-control  form-control-lg',
                ],
                'pluginOptions' => [
                    'depends' => ['estado_id'], 
                    'url' => Url::to(['/site/ciudad']), 
                    'initialize' => true,
                ]
            ]);  ?>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-12">
            <?= $form->field($model, 'direccion')->textarea([
                'rows' => 3,
                'placeholder' => 'Ingrese la dirección física completa',
                'class' => 'form-control form-control-lg',
            ]) ?>
        </div>
    </div>

    <?= $form->field($model, 'domicilio_fiscal')->textarea([
        'rows' => 3,
        'placeholder' => 'Ingrese la dirección de domicilio fiscal (si es diferente a la dirección física)',
        'class' => 'form-control form-control-lg',
    ]) ?>

    <h3 class="box-title mt-4">Datos de Registro Mercantil</h3>
    <hr>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <?= $form->field($model, 'lugar_registro')->textInput([
                'maxlength' => true,
                'placeholder' => 'Ej: Registro Mercantil Primero de Caracas',
                'class' => 'form-control form-control-lg',
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'fecha_registro_mercantil')->input('date', [ // HTML5 Date Input
                'class' => 'form-control form-control-lg',
            ]) ?>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <?= $form->field($model, 'tomo_registro')->textInput([
                'maxlength' => true,
                'placeholder' => 'Ej: 123A',
                'class' => 'form-control form-control-lg',
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'folio_registro')->textInput([
                'maxlength' => true,
                'placeholder' => 'Ej: 456B',
                'class' => 'form-control form-control-lg',
            ]) ?>
        </div>
    </div>

    <h3 class="box-title mt-4">Persona de Contacto</h3>
    <hr>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <?= $form->field($model, 'contacto_nombre')->textInput([
                'maxlength' => true,
                'placeholder' => 'Nombre completo de la persona de contacto',
                'class' => 'form-control form-control-lg',
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'contacto_cedula')->textInput([
                'maxlength' => true,
                'placeholder' => 'Ej: V-12345678', // Podrías usar MaskedInput aquí también si es necesario
                'class' => 'form-control form-control-lg',
            ]) ?>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <?= $form->field($model, 'contacto_telefono')->textInput([
                'maxlength' => true,
                'placeholder' => 'Ej: (0414) 123-4567', // Podrías usar MaskedInput aquí también
                'class' => 'form-control form-control-lg',
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'contacto_cargo')->textInput([
                'maxlength' => true,
                'placeholder' => 'Ej: Gerente de Recursos Humanos',
                'class' => 'form-control form-control-lg',
            ]) ?>
        </div>
    </div>

    <h3 class="box-title mt-4">Asociación con Clínicas y Empleados</h3>
    <hr>

    <div class="row g-3 mb-3">
        <div class="col-md-12">
            <?php
            $clinicasList = ArrayHelper::map(RmClinica::find()->orderBy('nombre')->all(), 'id', 'nombre');
            echo $form->field($model, 'clinicas_ids')->widget(Select2::class, [
                'data' => $clinicasList,
                'options' => [
                    'placeholder' => 'Seleccione una o varias clínicas...',
                    'multiple' => true,
                    'class' => 'form-control form-control-lg', // Clase de tamaño consistente
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ])->label('Clínicas Asociadas');
            ?>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-12">
            <?php
            // Ahora usamos el método de UserHelper para obtener solo los afiliados
            $afiliadosList = UserHelper::getAfiliadosList(); // <-- ¡Aquí está el cambio!

            echo $form->field($model, 'users_ids')->widget(Select2::class, [
                'data' => $afiliadosList, // <-- Usamos la lista filtrada
                'options' => [
                    'placeholder' => 'Seleccione uno o varios afiliados...', // Cambia el placeholder para reflejar el filtro
                    'multiple' => true,
                    'class' => 'form-control form-control-lg',
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ])->label('Afiliados Asociados'); // Puedes cambiar también la etiqueta del campo
            ?>
        </div>
    </div>


    <div class="row mt-4">
            <div class="col-12 d-flex justify-content-start">
                <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success btn-lg mr-5']) ?>
                
                <?php
                // Condición para mostrar el botón "Refrescar"
                // Solo muestra el botón "Refrescar" si el modelo es un NUEVO REGISTRO
                if ($model->isNewRecord) {
                    echo Html::button('<i class="fas fa-sync-alt"></i> Refrescar', [
                        'class' => 'btn btn-info btn-lg',
                        'id' => 'btn-refrescar-form'
                    ]);
                }
                ?>
            </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>