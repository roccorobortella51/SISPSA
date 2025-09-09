<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use app\components\UserHelper;
use yii\helpers\Url;
use app\models\Agente;

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
        <?php $form = ActiveForm::begin(); ?>

        <?php if (!$model->isNewRecord) { ?>
            <!-- Section for the "Volver" button -->
            <div class="row mb-4">
                <div class="col-12">
                    <?= Html::a(
                        '<i class="fas fa-undo mr-2"></i> Volver',
                        ['index'],
                        [
                            'class' => 'btn btn-primary btn-lg',
                            'style' => 'font-size: 1.5rem;',
                            'title' => 'Volver a la lista de Agencias',
                        ]
                    ) ?>
                </div>
            </div>

            <!-- Start of the new "Fuerza de Venta" subsection -->
            <div class="card mb-4">
                <div class="card-header bg-primary">
                    <h5 class="mb-0" style="color: white; font-size: 1.75rem; font-weight:600">Fuerza de Venta y Datos de la Agencia</h5>
                </div>
                <div class="card-body">
                    <!-- "Fuerza de Venta" button styled as a tag -->
                    <div class="row mb-3 d-flex justify-content-end">
                        <div class="col-auto">
                            <?= Html::a(
                                '<i class="fas fa-users mr-2" style="font-size:1.5rem; vertical-align:middle;"></i> <span style="font-size:1.5rem; font-weight:600; color: #fff;letter-spacing:1px;">FUERZA DE VENTA</span>',
                                ['agente-fuerza/index-by-agente', 'agente_id' => $model->id],
                                [
                                    'class' => 'btn btn-primary shadow',
                                    'style' => 'padding: 0.75rem 1.5rem; font-size: 1.25rem; border-radius: 9999px; font-weight: 600; box-shadow: 0 5px 15px rgba(0,0,0,0.2);',
                                ]
                            ) ?>
                        </div>
                    </div>
                    <br>
        <?php } ?>

        <div class="row mb-3">
            <div class="col-md-4">
                <?= $form->field($model, 'nom')->label('NOMBRE DE LA AGENCIA')->textInput([
                    'maxlength' => true,
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'Nombre completo del agente',
                    'autofocus' => true,
                ]) ?>
            </div>

            <div class="col-md-4">
                <?= $form->field($model, 'sudeaseg')->label('CÓDIGO SUDEASEG')->textInput([
                    'maxlength' => true,
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'Ingrese el código SUDEASEG',
                ]) ?>
            </div>
            <div class="col-md-4">
                <?php
                $agentesList = UserHelper::getAgentesList();

                $hasRealAgents = (count($agentesList) > 1) || (count($agentesList) === 1 && !isset($agentesList['0']) && !isset($agentesList['']));

                $select2Options = [
                    'data' => $agentesList,
                    'options' => [
                        'placeholder' => 'Seleccione',
                        'class' => 'form-control form-control-lg'
                    ],
                    'pluginOptions' => [
                        'allowClear' => false,
                    ],
                ];

                if (!$hasRealAgents) {
                    $select2Options['options']['placeholder'] = 'No Disponible';
                    $select2Options['options']['disabled'] = true;
                }

                echo $form->field($model, 'idusuariopropietario')->label('NOMBRE DEL PROPIETARIO')->widget(Select2::classname(), $select2Options);
                ?>
            </div>
        </div>
        
        <div class="card mb-3">
            <div class="card-header bg-primary"> 
                <h6 class="mb-0" style="color: white; font-size: 1.55rem; font-weight:600">Porcentajes (%) de Comisiones por:</h6>
            </div>
            <div class="ms-panel-body">
                <div class="row g-3">
                    <div class="col-md-2">
                        <?= $form->field($model, 'por_venta')->label('Venta <i class="fas fa-info-circle text-info" data-toggle="tooltip" data-placement="top" title="Comisión por la venta inicial de pólizas de seguro. Se otorga cuando se cierra exitosamente una nueva póliza con el cliente."></i>')->textInput([
                            'class' => 'form-control form-control-lg',
                            'placeholder' => 'Ej: 15.0',
                            'type' => 'number',
                            'step' => '0.1',
                            'min' => '0',
                            'max' => '100'
                        ]) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'por_asesor')->label('Asesoría <i class="fas fa-info-circle text-info" data-toggle="tooltip" data-placement="top" title="Comisión por brindar asesoramiento y consultoría especializada a los clientes. Incluye recomendaciones de cobertura y análisis de riesgos."></i>')->textInput([
                            'class' => 'form-control form-control-lg',
                            'placeholder' => 'Ej: 12.5',
                            'type' => 'number',
                            'step' => '0.1',
                            'min' => '0',
                            'max' => '100'
                        ]) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'por_cobranza')->label('Cobranza <i class="fas fa-info-circle text-info" data-toggle="tooltip" data-placement="top" title="Comisión por gestionar y asegurar el cobro oportuno de las primas de las pólizas. Incluye seguimiento de pagos y gestión de morosidad."></i>')->textInput([
                            'class' => 'form-control form-control-lg',
                            'placeholder' => 'Ej: 8.0',
                            'type' => 'number',
                            'step' => '0.1',
                            'min' => '0',
                            'max' => '100'
                        ]) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'por_post_venta')->label('Post Venta <i class="fas fa-info-circle text-info" data-toggle="tooltip" data-placement="top" title="Comisión por servicios posteriores a la venta como renovaciones, modificaciones de pólizas, atención de reclamos y mantenimiento de la relación con el cliente."></i>')->textInput([
                            'class' => 'form-control form-control-lg',
                            'placeholder' => 'Ej: 5.0',
                            'type' => 'number',
                            'step' => '0.1',
                            'min' => '0',
                            'max' => '100'
                        ]) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'por_agente')->label('Agencia <i class="fas fa-info-circle text-info" data-toggle="tooltip" data-placement="top" title="Comisión general de la agencia por la gestión administrativa, supervisión del equipo de ventas y mantenimiento de la infraestructura operativa."></i>')->textInput([
                            'class' => 'form-control form-control-lg',
                            'placeholder' => 'Ej: 10.0',
                            'type' => 'number',
                            'step' => '0.1',
                            'min' => '0',
                            'max' => '100'
                        ]) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'por_max')->label('Máximo <i class="fas fa-info-circle text-info" data-toggle="tooltip" data-placement="top" title="Porcentaje máximo total de comisiones que puede recibir la agencia. Este límite asegura la sostenibilidad financiera y cumple con regulaciones del sector."></i>')->textInput([
                            'class' => 'form-control form-control-lg',
                            'placeholder' => 'Ej: 25.0',
                            'type' => 'number',
                            'step' => '0.1',
                            'min' => '0',
                            'max' => '100'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!$model->isNewRecord) { ?>
            <!-- End of the new "Fuerza de Venta" subsection -->
            </div>
            </div>
        <?php } ?>

        <div class="row mt-4">
            <div class="col-12 d-flex justify-content-start">
                <?= Html::submitButton('<i class="fas fa-save mr-2"></i> Guardar', ['class' => 'btn btn-success btn-lg mr-3']) ?>

                <?= Html::a(
                    '<i class="fas fa-undo mr-2"></i> Volver',
                    '#',
                    [
                        'class' => 'btn btn-secondary btn-lg mr-3',
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

// Initialize tooltips
$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip({
        html: true,
        trigger: 'hover focus',
        delay: { show: 300, hide: 100 }
    });
});
JS;
$this->registerJs($js);
?>
