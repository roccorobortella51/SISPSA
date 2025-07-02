<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\models\User;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use app\models\Agente; // Asegúrate de importar el modelo Agente
use app\components\UserHelper;
use kartik\widgets\SwitchInput;

/** @var yii\web\View $this */
/** @var app\models\AgenteFuerza $model */
/** @var yii\widgets\ActiveForm $form */
/** @var array $userList La lista de usuarios en formato [id => username], pasado desde el controlador */
/** @var string|null $agenciaNombre El nombre de la agencia para el campo de solo lectura */


if (!isset($userList) || !is_array($userList)) {
    $users = User::find()->all();
    $userList = ArrayHelper::map($users, 'id', 'username');
}

$readOnly = !$model->isNewRecord;

// --- Corrección del error Undefined variable: agenciaNombre (NO MODIFICAR) ---
if (!isset($agenciaNombre) || $agenciaNombre === null) {
    if (!$model->isNewRecord && $model->agente) {
        $agenciaNombre = $model->agente->nom;
    } else {
        $agenciaNombre = 'N/A';
    }
}
// --- Fin de la corrección de errores ---

?>

<div class="agente-fuerza-form">

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0"><?= Html::encode($model->isNewRecord ? 'REGISTRAR NUEVO ASESOR DE VENTAS' : 'Actualizar Agente de Fuerza') ?></h3>
        </div>
        <div class="card-body">
            <?php $form = ActiveForm::begin([]); ?>

            <?php if (!$model->isNewRecord) { ?>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="ms-panel ms-widget ms-identifier-widget bg-info">
                            <div class="ms-panel-header header-mini">
                                <h6 style="margin: 0;">
                                    <?= Html::a(
                                        'ACTUALIZAR AGENTE',
                                        ['update', 'id' => $model->id],
                                        ['class' => 'text-white']
                                    ) ?>
                                </h6>
                            </div>
                            <div class="ms-panel-body">
                                <div class="text-center">
                                    <i class="flaticon-information"></i>
                                    <p class="mb-0">Modifica los datos de este agente de fuerza.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="ms-panel ms-widget ms-identifier-widget bg-info">
                            <div class="ms-panel-header header-mini">
                                <h6 style="margin: 0;">
                                    <?= Html::a(
                                        'VER DETALLES',
                                        ['view', 'id' => $model->id],
                                        ['class' => 'text-white']
                                    ) ?>
                                </h4>
                            </div>
                            <div class="ms-panel-body">
                                <div class="text-center">
                                    <i class="flaticon-information"></i>
                                    <p class="mb-0">Visualiza la información completa de este agente.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="ms-panel ms-widget ms-identifier-widget bg-info">
                            <div class="ms-panel-header header-mini">
                                <h6 style="margin: 0;">
                                    <?= Html::a(
                                        'USUARIO ASOCIADO',
                                        ['user/view', 'id' => $model->idusuario],
                                        ['class' => 'text-white']
                                    ) ?>
                                </h6>
                            </div>
                            <div class="ms-panel-body">
                                <div class="text-center">
                                    <i class="flaticon-information"></i>
                                    <p class="mb-0">Gestiona el usuario ligado a este agente.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <div class="row">
             
                <div class="col-md-4">
                    <?= $form->field($model, 'nombre_agente')->textInput([
                        'readonly' => true,
                        'class' => 'form-control form-control-lg',
                        'value' => $agente->nom,
                        'placeholder' => 'Nombre de la Agencia Asociada',
                    ])->label('AGENCIA ASOCIADA')
                    ?>
                </div>
                <div class="col-md-4">
                    

                    <?= $form->field($model, 'idusuario')->widget(Select2::classname(), [
                            'data' => UserHelper::getAgenteFuerzaList(),
                            'options' => [
                                'placeholder' => 'Seleccione',
                                'class' => 'form-control form-control-lg',
                            ],
                            'pluginOptions' => [
                                'allowClear' => false,
                            ],
                    ])->label('NOMBRE DEL ASESOR')
                    
                    ?> 


                </div>

                <div class="col-md-4">
                    <?= $form->field($model, 'por_venta')->label('PORCENTAJE POR VENTA')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => '% Venta',
                        'type' => 'number',
                        'step' => '0.01',
                    ]) ?>
                </div>

            </div>

            <div class="row">
                
                <div class="col-md-4">
                    <?= $form->field($model, 'por_asesor')->label('PORCENTAJE DE ASESORÍA')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => '% Asesoría',
                        'type' => 'number',
                        'step' => '0.01',
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'por_cobranza')->label('PORCENTAJE POR COBRANZA')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => '% Cobranza',
                        'type' => 'number',
                        'step' => '0.01',
                    ]) ?>
                </div>


                 <div class="col-md-4">
                    <?= $form->field($model, 'por_post_venta')->label('PORCENTAJE POST VENTA')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => '% Post-Venta',
                        'type' => 'number',
                        'step' => '0.01',
                    ]) ?>
                </div>
            </div>

            <div class="row mb-3">
               
                <div class="col-md-4">
                    <?= $form->field($model, 'por_registrar')->label('PORCENTAJE POR REGISTRO')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => '% Registro',
                        'type' => 'number',
                        'step' => '0.01',
                    ]) ?>
                </div>
                <div class="col-md-4">
                    </div>
            </div>


            
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-light">
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
                                'options' => [
                                    'id' => Html::getInputId($model, 'puede_vender'), // Mantener el ID explícito si es necesario
                                ],
                            ])->label('Puede Vender'); ?>

                            ---

                            <?= $form->field($model, 'puede_asesorar')->widget(SwitchInput::class, [
                                'type' => SwitchInput::CHECKBOX,
                                'pluginOptions' => [
                                    'onText' => 'Si',
                                    'offText' => 'No',
                                    'onColor' => 'success',
                                    'offColor' => 'danger',
                                ],
                                'options' => [
                                    'id' => Html::getInputId($model, 'puede_asesorar'),
                                ],
                            ])->label('Puede Asesorar'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0" style="color: white; font-size: 20px;">Permisos de Gestión y Cobranza</h6>
                        </div>
                        <div class="card-body">
                            <?= $form->field($model, 'puede_cobrar')->widget(SwitchInput::class, [
                                'type' => SwitchInput::CHECKBOX, // Opcional, ya que es el tipo por defecto para booleans
                                'pluginOptions' => [
                                    'onText' => 'Si',
                                    'offText' => 'No',
                                    'onColor' => 'success', // Color para 'Si' (verde)
                                    'offColor' => 'danger',  // Color para 'No' (rojo)
                                ],
                                'options' => [
                                    'id' => Html::getInputId($model, 'puede_cobrar'), // Mantener el ID explícito si es necesario
                                ],
                            ])->label('Puede Cobrar'); ?>

                            ---

                            <?= $form->field($model, 'puede_post_venta')->widget(SwitchInput::class, [
                                'type' => SwitchInput::CHECKBOX,
                                'pluginOptions' => [
                                    'onText' => 'Si',
                                    'offText' => 'No',
                                    'onColor' => 'success',
                                    'offColor' => 'danger',
                                ],
                                'options' => [
                                    'id' => Html::getInputId($model, 'puede_post_venta'),
                                ],
                            ])->label('Puede Post Venta'); ?>

                            ---

                            <?= $form->field($model, 'puede_registrar')->widget(SwitchInput::class, [
                                'type' => SwitchInput::CHECKBOX,
                                'pluginOptions' => [
                                    'onText' => 'Si',
                                    'offText' => 'No',
                                    'onColor' => 'success',
                                    'offColor' => 'danger',
                                ],
                                'options' => [
                                    'id' => Html::getInputId($model, 'puede_registrar'),
                                ],
                            ])->label('Puede Registrar'); ?>
                        </div>
                    </div>
                </div>
            </div>


            <div class="form-group text-end mt-4">
                <?= Html::submitButton('<i class="fas fa-save"></i> GUARDAR', ['class' => 'btn btn-success btn-lg']) ?>
                <!-- Html::a('<i class="fas fa-arrow-left"></i> VOLVER AL LISTADO', ['index'], ['class' => 'btn btn-outline-secondary btn-lg ms-2']) -->
            </div>
            

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>