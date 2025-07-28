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
}else{
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
                <?= $form->field($model, 'estado')->widget(Select2::class, [
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
                <?= $form->field($model, 'municipio')->widget(DepDrop::class, [
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
                <?= $form->field($model, 'parroquia')->widget(DepDrop::class, [
                    'type' => DepDrop::TYPE_SELECT2,
                    'options' => [
                        'id' => 'parroquia_id',
                        'placeholder' => 'Seleccione una parroquia...',
                        'class' => 'form-control  form-control-lg',
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
                        'id' => 'btn-refrescar-form'
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
$('#btn-refrescar-form').on('click', function() {
    $(this).closest('form')[0].reset(); 
});
JS;
$this->registerJs($js);
?>
