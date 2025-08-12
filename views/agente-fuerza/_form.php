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
                ['user-datos/index-by-afiliado', 'asesor_id' => $model->idusuario],
                ['class' => 'btn btn-success btn-lg w-100'] // Estilo de botón
            ) ?>
        </div>
    </div>
    <br> <?php } ?>

        <div class="row">
        <div class="col-md-6" style="display:none;">
                <?= $form->field($model, 'agente_id')->textInput([
                    'readonly' => true, // Este campo siempre es de solo lectura
                    'class' => 'form-control form-control-lg',
                    'value' => $agente->id, // Usar null coalescing para seguridad
                    'placeholder' => 'Nombre de la Agencia Asociada',
                ])
                ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'nombre_agente')->textInput([
                    'readonly' => true, // Este campo siempre es de solo lectura
                    'class' => 'form-control form-control-lg',
                    'value' => $agente->nom ?? 'N/A', // Usar null coalescing para seguridad
                    'placeholder' => 'Nombre de la Agencia Asociada',
                ])->label('AGENCIA ASOCIADA')
                ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'registro_corredor_actividad_aseguradora')->textInput([
                    'class' => 'form-control form-control-lg',
                    //'value' => $agente->nom ?? 'N/A', // Usar null coalescing para seguridad
                    'placeholder' => 'Registro corredor aseguradoras ',
                ])->label('REGISTRO CORREDOR ACTIVIDAD ASEGURADORA')
                ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'idusuario')->widget(Select2::classname(), [
                        'data' => UserHelper::getAsesor(),
                        'options' => [
                            'placeholder' => 'Seleccione el asesor', // Placeholder adaptado
                            'class' => 'form-control form-control-lg',
                        ],
                        'pluginOptions' => [
                            'allowClear' => false,
                        ],
                ])->label('NOMBRE DEL ASESOR') // Etiqueta adaptada
                ?> 
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <?= $form->field($model, 'por_venta')->label('PORCENTAJE POR VENTA')->textInput([
                    'class' => 'form-control form-control-lg', // Aseguramos 'form-control-lg'
                    'placeholder' => '% Venta',
                    'type' => 'number',
                    'step' => '0.01',
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'por_asesor')->label('PORCENTAJE DE ASESORÍA')->textInput([
                    'class' => 'form-control form-control-lg', // Aseguramos 'form-control-lg'
                    'placeholder' => '% Asesoría',
                    'type' => 'number',
                    'step' => '0.01',
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'por_cobranza')->label('PORCENTAJE POR COBRANZA')->textInput([
                    'class' => 'form-control form-control-lg', // Aseguramos 'form-control-lg'
                    'placeholder' => '% Cobranza',
                    'type' => 'number',
                    'step' => '0.01',
                ]) ?>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <?= $form->field($model, 'por_post_venta')->label('PORCENTAJE POST VENTA')->textInput([
                    'class' => 'form-control form-control-lg', // Aseguramos 'form-control-lg'
                    'placeholder' => '% Post-Venta',
                    'type' => 'number',
                    'step' => '0.01',
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'por_registrar')->label('PORCENTAJE POR REGISTRO')->textInput([ // Campo 'por_registrar' aquí
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

        <div class="col-md-12">
            <div class="form-group text-end mt-4" style="margin-right:10px;">
                <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success btn-lg']) ?>
                
               

                <?php if (isset($agente) && $agente !== null): ?>
                    <?= Html::a('CANCELAR', ['agente-fuerza/index-by-agente', 'agente_id' => $agente->id], ['class' => 'btn btn-warning btn-lg ms-2']); ?>
                <?php endif; ?>

                <?php if ($model->isNewRecord) { ?>
                    <button type="button" class="btn btn-default  btn-lg ms-2" id="btn-limpiar-formulario">
                        <i class="fas fa-eraser"></i> LIMPIAR FORMULARIO
                    </button>
                <?php } ?>
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