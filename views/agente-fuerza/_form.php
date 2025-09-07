<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\models\User;
use kartik\select2\Select2;
use yii\widgets\MaskedInput; 
use app\models\Agente; 
use app\components\UserHelper;
use kartik\widgets\SwitchInput;
use yii\web\View; 

/** @var yii\web\View $this */
/** @var app\models\AgenteFuerza $model */
/** @var yii\widgets\ActiveForm $form */
/** @var array $userList La lista de usuarios en formato [id => username], pasado desde el controlador */
/** @var string|null $agenciaNombre El nombre de la agencia para el campo de solo lectura */
/* @var $agente app\models\Agente */ // Se espera que $agente esté disponible

if (!isset($userList) || !is_array($userList)) {
    $users = User::find()->all();
    $userList = ArrayHelper::map($users, 'id', 'username');
}

// La variable $readOnly se mantiene como la original en este formulario
// para que el campo de agencia siempre sea de solo lectura.
$readOnly = !$model->isNewRecord; // Si es un nuevo registro, false. Si es existente, true.

// --- Corrección del error Undefined variable: agenciaNombre (NO MODIFICAR) ---
// Se mantiene esta lógica para asegurar que $agenciaNombre siempre tenga un valor.
if (!isset($agenciaNombre) || $agenciaNombre === null) {
    if (!$model->isNewRecord && $model->agente) {
        $agenciaNombre = $model->agente->nom;
    } else {
        $agenciaNombre = 'N/A';
    }
}
// --- Fin de la corrección de errores ---

$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol == 'GERENTE-COMERCIALIZACION'); 
?>

<div class="agente-fuerza-form">
    <div class="ms-panel-body">
        <?php $form = ActiveForm::begin(['id' => 'agente-fuerza-form']); // ¡ID esencial para JavaScript! ?>

        <?php if (!$model->isNewRecord) { ?>
        <div class="row row-cols-1 row-cols-md-2 g-3 mb-3">
            <div class="col">
                <?= Html::a(
                    '<i class="fas fa-undo"></i> Volver',
                    // CAMBIO AQUÍ: La ruta ahora apunta a 'index-by-agente' y pasa el 'agente_id'
                    ['index-by-agente', 'agente_id' => $agente->id],
                    ['class' => 'btn btn-primary btn-lg w-100']
                ) ?>
            </div>

            <div class="col">
                <?= Html::a(
                    '<i class="fas fa-users-cog"></i> VER AFILIADOS DEL VENDEDOR', // Icono para afiliados
                    ['user-datos/index-by-afiliado', 'asesor_id' => $model->id],
                    ['class' => 'btn btn-success btn-lg w-100'] // Estilo de botón
                ) ?>
            </div>
        </div>
        <br> 
        <?php } ?>

        <div class="row">
            <div class="col-md-6" style="display:none;">
                <?= $form->field($model, 'agente_id')->textInput([
                    'readonly' => true, // Este campo siempre es de solo lectura
                    'class' => 'form-control form-control-lg',
                    'value' => $agente->id, // Usar null coalescing para seguridad
                    'placeholder' => 'Nombre de la Agencia Asociada',
                ]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'nombre_agente')->textInput([
                    'readonly' => true, // Este campo siempre es de solo lectura
                    'class' => 'form-control form-control-lg',
                    'value' => $agente->nom ?? 'N/A', // Usar null coalescing para seguridad
                    'placeholder' => 'Nombre de la Agencia Asociada',
                ])->label('AGENCIA ASOCIADA') ?>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'idusuario')->widget(Select2::classname(), [
                    'data' => UserHelper::getAsesor(),
                    'options' => [
                        'placeholder' => 'Seleccione el Agente o Asesor', // Placeholder adaptado
                        'class' => 'form-control form-control-lg',
                    ],
                    'pluginOptions' => [
                        'allowClear' => false,
                    ],
                ])->label('NOMBRE DEL AGENTE / ASESOR') // Etiqueta adaptada
                ?> 
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'registro_corredor_actividad_aseguradora')->textInput([
                    'class' => 'form-control form-control-lg',
                    //'value' => $agente->nom ?? 'N/A', // Usar null coalescing para seguridad
                    'placeholder' => 'Registro corredor aseguradoras ',
                ])->label('REGISTRO SUDEASEG') ?>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-3"> <!-- FIXED: Changed card-mb-3 to card mb-3 -->
                    <div class="card-header bg-primary"> 
                        <h6 class="mb-0" style="color: white; font-size: 20px;">Porcentajes de Comisiones por (%)</h6>
                    </div>
                    <div class="card-body">
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
                                <?= $form->field($model, 'por_asesor')->label('Asesoría <i class="fas fa-info-circle text-info" data-toggle="tooltip" data-placement="top" title="Comisión por brindar asesoramiento и consultoría especializada a los clientes. Incluye recomendaciones de cobertura y análisis de riesgos."></i>')->textInput([
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
                                <?= $form->field($model, 'por_registrar')->label('Registro <i class="fas fa-info-circle text-info" data-toggle="tooltip" data-placement="top" title="Comisión general de la agencia por registrar un afiliado(a)."></i>')->textInput([
                                    'class' => 'form-control form-control-lg',
                                    'placeholder' => 'Ej: 10.0',
                                    'type' => 'number',
                                    'step' => '0.1',
                                    'min' => '0',
                                    'max' => '100'
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- FIXED: Added missing closing div for this row -->
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-primary"> 
                        <h6 class="mb-0" style="color: white; font-size: 20px;">Permisos de Venta y Asesoría</h6>
                    </div>
                    <div class="card-body">
                        <?= $form->field($model, 'puede_vender')->widget(SwitchInput::class, [
                            'type' => SwitchInput::CHECKBOX,
                            'pluginOptions' => [
                                'onText' => 'Si',
                                'offText' => 'No',
                                'onColor' => 'success',
                                'offColor' => 'danger',
                            ],
                            'options' => ['id' => Html::getInputId($model, 'puede_vender')],
                        ])->label('Puede Vender'); ?>

                        <hr> 

                        <?= $form->field($model, 'puede_asesorar')->widget(SwitchInput::class, [
                            'type' => SwitchInput::CHECKBOX,
                            'pluginOptions' => [
                                'onText' => 'Si',
                                'offText' => 'No',
                                'onColor' => 'success',
                                'offColor' => 'danger',
                            ],
                            'options' => ['id' => Html::getInputId($model, 'puede_asesorar')],
                        ])->label('Puede Asesorar'); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-primary"> 
                        <h6 class="mb-0" style="color: white; font-size: 20px;">Permisos de Gestión y Cobranza</h6>
                    </div>
                    <div class="card-body">
                        <?= $form->field($model, 'puede_cobrar')->widget(SwitchInput::class, [
                            'type' => SwitchInput::CHECKBOX,
                            'pluginOptions' => [
                                'onText' => 'Si',
                                'offText' => 'No',
                                'onColor' => 'success',
                                'offColor' => 'danger',
                            ],
                            'options' => ['id' => Html::getInputId($model, 'puede_cobrar')],
                        ])->label('Puede Cobrar'); ?>

                        <hr> 

                        <?= $form->field($model, 'puede_post_venta')->widget(SwitchInput::class, [
                            'type' => SwitchInput::CHECKBOX,
                            'pluginOptions' => [
                                'onText' => 'Si',
                                'offText' => 'No',
                                'onColor' => 'success',
                                'offColor' => 'danger',
                            ],
                            'options' => ['id' => Html::getInputId($model, 'puede_post_venta')],
                        ])->label('Puede Post Venta'); ?>

                        <hr> 

                        <?= $form->field($model, 'puede_registrar')->widget(SwitchInput::class, [
                            'type' => SwitchInput::CHECKBOX,
                            'pluginOptions' => [
                                'onText' => 'Si',
                                'offText' => 'No',
                                'onColor' => 'success',
                                'offColor' => 'danger',
                            ],
                            'options' => ['id' => Html::getInputId($model, 'puede_registrar')],
                        ])->label('Puede Registrar'); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group text-end mt-4" style="margin-right:10px;">
                    <?php if($permisos){ echo Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success btn-lg']); } ?>
                    
                    <?php if (isset($agente) && $agente !== null): ?>
                        <?= Html::a('CANCELAR', ['agente-fuerza/index-by-agente', 'agente_id' => $agente->id], ['class' => 'btn btn-warning btn-lg ms-2']); ?>
                    <?php endif; ?>

                    <?php if ($model->isNewRecord) { ?>
                        <button type="button" class="btn btn-default btn-lg ms-2" id="btn-limpiar-formulario">
                            <i class="fas fa-eraser"></i> LIMPIAR FORMULARIO
                        </button>
                    <?php } ?>
                </div>
            </div>
        </div>
        
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
// Script JavaScript para limpiar el formulario
$script = <<<JS
    $('#btn-limpiar-formulario').on('click', function() {
        // Resetea todos los campos del formulario con el ID 'agente-fuerza-form'
        $('#agente-fuerza-form')[0].reset();

        // Para Select2: Si el campo 'idusuario' no se resetea visualmente,
        // necesitas forzar el cambio. El ID por defecto de un Select2 de Yii con ActiveForm es
        // 'nombremodelo-nombreatributo' -> '#agentefuerza-idusuario'
        $('#agentefuerza-idusuario').val('').trigger('change');
    });
JS;
$this->registerJs($script, View::POS_END);
?>