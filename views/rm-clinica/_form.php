
<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2; // Para los selectores de estado y estatus
use yii\widgets\MaskedInput; // Para campos con máscaras como RIF y teléfono
use app\components\UserHelper;
use yii\helpers\Url;
use kartik\depdrop\DepDrop;



// Asegúrate de que estas variables siempre tengan un valor para evitar errores
// si el controlador no las pasa por alguna razón (aunque el controlador sí las pasa).
$listaEstatus = $listaEstatus ?? [];
$mode = $mode ?? 'create'; // Por defecto es 'create' si no se especifica
$isNewRecord = $isNewRecord ?? true; // Por defecto es true para este formulario

if ($model->isNewRecord) {
    $readOnly = false;
}else{
    $readOnly = true;
}
?>


<div class="rm-clinica-form">

    <?php $form = ActiveForm::begin([]); ?>


    <?php if (!$model->isNewRecord) { ?>
    <div class="row row-cols-1 row-cols-md-4 g-3 mb-3">
        <div class="col">
            <?= Html::a(
                '<i class="fas fa-file-invoice-dollar"></i> Baremo', // Icono y texto en la misma línea
                ['baremo/index', 'clinica_id' => $model->id],
                ['class' => 'btn btn-primary btn-lg w-100'] // Quitamos 'py-4'
            ) ?>
        </div>

        <div class="col">
            <?= Html::a(
                '<i class="fas fa-clipboard-list"></i> Planes', // Icono y texto en la misma línea
                ['planes/index', 'clinica_id' => $model->id],
                ['class' => 'btn btn-primary btn-lg w-100'] // Quitamos 'py-4'
            ) ?>
        </div>

        <div class="col">
            <?= Html::a(
                '<i class="fas fa-users"></i> Afiliados', // Icono y texto en la misma línea
                ['user-datos/index-clinicas', 'clinica_id' => $model->id],
                ['class' => 'btn btn-primary btn-lg w-100'] // Quitamos 'py-4'
            ) ?>
        </div>

        <div class="col">
            <?= Html::a(
                '<i class="fas fa-tasks"></i> Check List', // Icono y texto en la misma línea
                ['check-list-clinicas/index', 'clinica_id' => $model->id],
                ['class' => 'btn btn-primary btn-lg w-100'] // Quitamos 'py-4'
            ) ?>
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
                'readonly' => $readOnly
            ]) ?>
        </div>
        <div class="col-md-2">
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
        <div class="col-md-2">
            <?= $form->field($model, 'telefono')->widget(MaskedInput::class, [
                'mask' => '(9999) 999-9999',
                'options' => [
                    'placeholder' => '(XXXX) XXX-XXXX',
                    'class' => 'form-control form-control-lg',
                    'maxlength' => true,
                ]
            ]) ?>
        </div>

        <div class="col-md-4">
           <?= $form->field($model, 'correo')->textInput([
            'maxlength' => true,
            'placeholder' => 'Ingrese el correo electrónico',
            'class' => 'form-control form-control-lg',
            ]) ?>
        </div>
    </div>

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

    
    
   <div class="row mt-4">
            <div class="col-12 d-flex justify-content-start">
                <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success btn-lg mr-5']) ?> <!-- Clase mr-5 para mayor margen a la derecha -->
                
                <?php
                // Condición para mostrar el botón "Refrescar"
                if (isset($isNewRecord) && $isNewRecord) { 
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
