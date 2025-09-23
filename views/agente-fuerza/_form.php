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
$permisos = ($rol == 'superadmin' || $rol == 'DIRECTOR-COMERCIALIZACIÓN'); 
?>

<div class="agente-fuerza-form">
    <div class="ms-panel-body">
        <?php $form = ActiveForm::begin(['id' => 'agente-fuerza-form']); // ¡ID esencial para JavaScript! ?>

        <?php if (!$model->isNewRecord) { ?>
            <div class="row row-cols-1 row-cols-md-2 g-3 mb-3">
                <div class="col">
                    <?= Html::a(
                        '<i class="fas fa-undo"></i> Volver',
                        ['index-by-agente', 'agente_id' => $agente->id],
                        ['class' => 'btn btn-primary btn-lg w-100']
                    ) ?>
                </div>

                <div class="col">
                    <?= Html::a(
                        '<i class="fas fa-users-cog"></i> VER AFILIADOS DEL VENDEDOR/ASESOR',
                        ['user-datos/index-by-afiliado', 'asesor_id' => $model->id],
                        ['class' => 'btn btn-success btn-lg w-100']
                    ) ?>
                </div>
            </div>
            <br> 
        <?php } ?>
        
        <!-- Sección de Información General -->
        <div class="card mb-3">
            <div class="card-header bg-info text-center">
                <h6 class="mb-0 fw-bold" style="color: white; font-size: 20px;">INFORMACIÓN GENERAL</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6" style="display:none;">
                        <?= $form->field($model, 'agente_id')->textInput([
                            'readonly' => true,
                            'class' => 'form-control form-control-lg',
                            'value' => $agente->id,
                            'placeholder' => 'Nombre de la Agencia Asociada',
                        ]) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'nombre_agente')->textInput([
                            'readonly' => true,
                            'class' => 'form-control form-control-lg',
                            'value' => $agente->nom ?? 'N/A',
                            'placeholder' => 'Nombre de la Agencia Asociada',
                        ])->label('AGENCIA ASOCIADA') ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'idusuario')->widget(Select2::classname(), [
                                'data' => UserHelper::getAsesor(),
                                'options' => [
                                    'placeholder' => 'Seleccione el asesor',
                                    'class' => 'form-control form-control-lg',
                                ],
                                'pluginOptions' => [
                                    'allowClear' => false,
                                ],
                        ])->label('NOMBRE DEL AGENTE/ASESOR') ?> 
                    </div>
                    <div class="col-md-12">
                        <?= $form->field($model, 'registro_corredor_actividad_aseguradora')->textInput([
                            'class' => 'form-control form-control-lg',
                            'placeholder' => 'Registro corredor aseguradoras',
                        ])->label('REGISTRO SUDEASEG') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de Porcentajes -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-center"> 
                <h6 class="mb-0 fw-bold" style="color: white; font-size: 20px;">PORCENTAJES (%)</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, 'por_venta')->label('PORCENTAJE POR VENTA')->textInput([
                            'class' => 'form-control form-control-lg',
                            'placeholder' => '% Venta',
                            'type' => 'number',
                            'step' => '0.01',
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'por_asesor')->label('PORCENTAJE DE ASESORÍA')->textInput([
                            'class' => 'form-control form-control-lg',
                            'placeholder' => '% Asesoría',
                            'type' => 'number',
                            'step' => '0.01',
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'por_cobranza')->label('PORCENTAJE POR COBRANZA')->textInput([
                            'class' => 'form-control form-control-lg',
                            'placeholder' => '% Cobranza',
                            'type' => 'number',
                            'step' => '0.01',
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'por_post_venta')->label('PORCENTAJE POST VENTA')->textInput([
                            'class' => 'form-control form-control-lg',
                            'placeholder' => '% Post-Venta',
                            'type' => 'number',
                            'step' => '0.01',
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'por_registrar')->label('PORCENTAJE POR REGISTRO')->textInput([
                            'class' => 'form-control form-control-lg', 
                            'placeholder' => '% Registro',
                            'type' => 'number',
                            'step' => '0.01',
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sección de Permisos -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-center"> 
                <h6 class="mb-0 fw-bold" style="color: white; font-size: 20px;">PERMISOS DEL AGENTE/ASESOR</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
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
                    </div>
                    <div class="col-md-4">
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
                    <div class="col-md-4">
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
                    </div>
                    <div class="col-md-4">
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
                    </div>
                    <div class="col-md-4">
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
        
        <!-- Sección de Botones de Acción -->
        <div class="row">
            <div class="col-md-12">
                <div class="form-group text-end mt-4" style="margin-right:10px;">
                    <?php if($permisos){ echo Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success btn-lg']); } ?>
                    
                    <?php if (isset($agente) && $agente !== null): ?>
                        <?= Html::a('VOLVER', ['agente-fuerza/index-by-agente', 'agente_id' => $agente->id], ['class' => 'btn btn-info btn-lg ms-2']); ?>
                    <?php endif; ?>

                    <?php if ($model->isNewRecord) { ?>
                        <button type="button" class="btn btn-secondary  btn-lg ms-2" id="btn-limpiar-formulario">
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
