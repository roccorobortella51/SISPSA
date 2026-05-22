<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use yii\helpers\Url;
use kartik\depdrop\DepDrop;

$listaEstatus = $listaEstatus ?? [];
$mode = $mode ?? 'create';
$isNewRecord = $isNewRecord ?? true;

if ($model->isNewRecord) {
    $readOnly = false;
} else {
    $readOnly = true;
}
?>

<div class="rm-clinica-form">
    <div class="ms-panel-body">
        <?php $form = ActiveForm::begin([]); ?>

        <?php if (!$model->isNewRecord) { ?>
            <div class="nav-buttons-grid mb-6">
                <div>
                    <?= Html::a(
                        '<i class="fas fa-file-invoice-dollar mr-2"></i> Baremo',
                        ['baremo/index', 'clinica_id' => $model->id],
                        ['class' => 'nav-btn-base nav-btn-blue', 'style' => 'padding: 1.2rem 1.8rem; font-size: 1.2rem;']
                    ) ?>
                </div>
                <div>
                    <?= Html::a(
                        '<i class="fas fa-clipboard-list mr-2"></i> Planes',
                        ['planes/index', 'clinica_id' => $model->id],
                        ['class' => 'nav-btn-base nav-btn-indigo', 'style' => 'padding: 1.2rem 1.8rem; font-size: 1.2rem;']
                    ) ?>
                </div>
                <div>
                    <?= Html::a(
                        '<i class="fas fa-users mr-2"></i> Afiliados',
                        ['user-datos/index-clinicas', 'clinica_id' => $model->id],
                        ['class' => 'nav-btn-base nav-btn-teal', 'style' => 'padding: 1.2rem 1.8rem; font-size: 1.2rem;']
                    ) ?>
                </div>
                <div>
                    <?= Html::a(
                        '<i class="fas fa-tasks mr-2"></i> Check List',
                        ['check-list-clinicas/index', 'clinica_id' => $model->id],
                        ['class' => 'nav-btn-base nav-btn-cyan', 'style' => 'padding: 1.2rem 1.8rem; font-size: 1.2rem;']
                    ) ?>
                </div>
            </div>
        <?php } ?>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'nombre')->label('NOMBRE DE LA CLÍNICA')->textInput([
                    'maxlength' => true,
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'Nombre completo de la Clínica',
                    'autofocus' => true,
                    'readonly' => $readOnly,
                    'title' => 'Ingrese el nombre legal o comercial completo de la clínica. Este nombre se mostrará en reportes y facturación.',
                    'data-toggle' => 'tooltip',
                    'data-placement' => 'top',
                ]) ?>
            </div>
            <div class="col-md-2">
                <?= $form->field($model, 'rif')->widget(MaskedInput::class, [
                    'mask' => 'J-99999999-9',
                    'options' => [
                        'placeholder' => 'J-XXXXXXXX-X',
                        'class' => 'form-control form-control-lg',
                        'maxlength' => true,
                        'readonly' => $readOnly,
                        'title' => 'Registro de Información Fiscal (RIF) de la clínica. Formato: J-XXXXXXXX-X',
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'top',
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
                        'title' => 'Número de teléfono principal de contacto. Formato: (XXXX) XXX-XXXX',
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'top',
                    ]
                ]) ?>
            </div>

            <div class="col-md-4">
                <?= $form->field($model, 'correo')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'Ingrese el correo electrónico',
                    'class' => 'form-control form-control-lg',
                    'title' => 'Correo electrónico oficial de la clínica para comunicaciones y notificaciones del sistema.',
                    'data-toggle' => 'tooltip',
                    'data-placement' => 'top',
                ]) ?>
            </div>
        </div>

        <div class="row">

            <div class="col-md-3">
                <?= $form->field($model, 'estado')->widget(Select2::class, [
                    'data' => UserHelper::getEstadosList(),
                    'options' => [
                        'placeholder' => 'Seleccione un estado...',
                        'class' => 'form-control  form-control-lg',
                        'id' => 'estado_id',
                        'title' => 'Seleccione el estado donde se encuentra ubicada la clínica.',
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'top',
                    ],
                    'pluginOptions' => [
                        'allowClear' => false,
                    ],
                ]);
                ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'municipio')->widget(DepDrop::class, [
                    'type' => DepDrop::TYPE_SELECT2,
                    'options' => [
                        'id' => 'municipio_id',
                        'placeholder' => 'Seleccione un municipio...',
                        'class' => 'form-control  form-control-lg',
                        'title' => 'Seleccione el municipio correspondiente al estado seleccionado.',
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'top',
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
                <?= $form->field($model, 'parroquia')->widget(DepDrop::class, [
                    'type' => DepDrop::TYPE_SELECT2,
                    'options' => [
                        'id' => 'parroquia_id',
                        'placeholder' => 'Seleccione una parroquia...',
                        'class' => 'form-control  form-control-lg',
                        'title' => 'Seleccione la parroquia correspondiente al municipio seleccionado.',
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'top',
                    ],
                    'pluginOptions' => [
                        'depends' => ['municipio_id'],
                        'url' => Url::to(['/site/parroquia']),
                    ]
                ]);
                ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'ciudad')->widget(DepDrop::class, [
                    'type' => DepDrop::TYPE_SELECT2,
                    'options' => [
                        'id' => 'ciudad_id',
                        'placeholder' => 'Seleccione una ciudad...',
                        'class' => 'form-control  form-control-lg',
                        'title' => 'Seleccione la ciudad donde opera la clínica.',
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'top',
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
                    'title' => 'Dirección fiscal y física completa de la clínica. Incluya calle, número, edificio, referencia, etc.',
                    'data-toggle' => 'tooltip',
                    'data-placement' => 'top',
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'webpage')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'Ej: www.ejemplo.com',
                    'class' => 'form-control form-control-lg',
                    'title' => 'Sitio web oficial de la clínica. Ejemplo: www.miclinica.com',
                    'data-toggle' => 'tooltip',
                    'data-placement' => 'top',
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'rs_instagram')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'Ej: @tu_clinica',
                    'class' => 'form-control form-control-lg',
                    'title' => 'Usuario oficial de Instagram de la clínica. Ejemplo: @clinicacentral',
                    'data-toggle' => 'tooltip',
                    'data-placement' => 'top',
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'codigo_clinica')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'Código interno de clínica',
                    'class' => 'form-control form-control-lg',
                    'title' => 'Código interno único para identificación rápida de la clínica en el sistema.',
                    'data-toggle' => 'tooltip',
                    'data-placement' => 'top',
                ]) ?>
            </div>
        </div>

        <!-- New Meta Field Section -->
        <div class="row mt-3">
            <div class="col-md-4">
                <?= $form->field($model, 'meta')->textInput([
                    'type' => 'number',
                    'step' => '1',
                    'min' => '0',
                    'max' => '999999',
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'Ej: 150',
                    'title' => 'Meta mensual de afiliados que la clínica debe captar. Este valor representa el número objetivo de nuevos afiliados por mes. Útil para seguimiento de cumplimiento de objetivos comerciales.',
                    'data-toggle' => 'tooltip',
                    'data-placement' => 'top',
                ])->label('META MENSUAL <small class="text-muted">(N° de Afiliados)</small>') ?>
                <small class="form-text text-muted">
                    <i class="fas fa-chart-line mr-1"></i>
                    Objetivo mensual de captación de afiliados para esta clínica
                </small>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12 d-flex justify-content-start">
                <?= Html::submitButton('<i class="fas fa-save mr-2"></i> Guardar', ['class' => 'btn btn-success btn-lg mr-4']) ?>

                <?= Html::a(
                    '<i class="fas fa-undo mr-2"></i> Volver',
                    ['index'],
                    [
                        'class' => 'btn btn-secondary btn-lg mr-4',
                        'onclick' => 'window.history.back(); return false;',
                        'title' => 'Volver a la página anterior',
                    ]
                ) ?>

                <?php
                if (isset($isNewRecord) && $isNewRecord) {
                    echo Html::button('<i class="fas fa-sync-alt mr-2"></i> Refrescar', [
                        'class' => 'btn btn-info btn-lg',
                        'id' => 'btn-refrescar-form',
                        'title' => 'Limpiar todos los campos del formulario',
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'top',
                    ]);
                }
                ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$js = <<<JS
// Initialize all tooltips
$(function () {
    $('[data-toggle="tooltip"]').tooltip({
        trigger: 'hover',
        animation: true,
        delay: { show: 300, hide: 100 }
    });
});

// Refresh form button functionality
$('#btn-refrescar-form').on('click', function() {
    $(this).closest('form')[0].reset();
    // Clear any select2 values
    $('select').val(null).trigger('change');
    // Show success notification
    toastr.success('Formulario reiniciado correctamente', 'Éxito');
});
JS;
$this->registerJs($js);
?>