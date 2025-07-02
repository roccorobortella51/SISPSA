<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\models\User;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use app\models\Agente; // Asegúrate de importar el modelo Agente

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
            <h3 class="mb-0"><?= Html::encode($model->isNewRecord ? 'Registrar Nuevo Agente de Fuerza' : 'Actualizar Agente de Fuerza') ?></h3>
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
                    <?= $form->field($model, 'id')->textInput([
                        'readonly' => true,
                        'class' => 'form-control form-control-lg',
                        'placeholder' => 'ID Agente Fuerza',
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'agente_id')->textInput([
                        'readonly' => true,
                        'class' => 'form-control form-control-lg',
                        'value' => $agenciaNombre,
                        'placeholder' => 'Nombre de la Agencia Asociada',
                    ])->label('AGENCIA ASOCIADA')
                    ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'idusuario')->label('USUARIO ASOCIADO')->widget(Select2::classname(), [
                        'data' => $userList,
                        'options' => [
                            'placeholder' => 'Selecciona un usuario',
                            'class' => 'form-control form-control-lg',
                            'disabled' => $readOnly
                        ],
                        'pluginOptions' => [
                            'allowClear' => false,
                        ],
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'por_venta')->label('PORCENTAJE POR VENTA')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => '% Venta',
                        'type' => 'number',
                        'step' => '0.01',
                    ]) ?>
                </div>
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
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <?= $form->field($model, 'por_post_venta')->label('PORCENTAJE POST VENTA')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => '% Post-Venta',
                        'type' => 'number',
                        'step' => '0.01',
                    ]) ?>
                </div>
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

            ---

            <h5 class="mt-4 mb-3">Permisos de Acceso</h5>
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Permisos de Venta y Asesoría</h6>
                        </div>
                        <div class="card-body">
                            <?= $form->field($model, 'puede_vender', [
                                // Aquí es donde controlamos el template para el switch
                                'template' => '<div class="form-check form-switch">{input}{label}{error}{hint}</div>',
                                // No necesitamos 'options' para la envoltura div.form-group del field,
                                // porque el template ahora lo maneja directamente
                            ])->checkbox([
                                'class' => 'form-check-input',
                                'role' => 'switch',
                                // Es fundamental que el ID sea correcto para que el label funcione
                                'id' => Html::getInputId($model, 'puede_vender')
                            ])->label('Puede Vender', [
                                // La clase para el label del switch
                                'class' => 'form-check-label'
                            ]); ?>

                            <?= $form->field($model, 'puede_asesorar', [
                                'template' => '<div class="form-check form-switch">{input}{label}{error}{hint}</div>',
                            ])->checkbox([
                                'class' => 'form-check-input',
                                'role' => 'switch',
                                'id' => Html::getInputId($model, 'puede_asesorar')
                            ])->label('Puede Asesorar', [
                                'class' => 'form-check-label'
                            ]); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Permisos de Gestión y Cobranza</h6>
                        </div>
                        <div class="card-body">
                            <?= $form->field($model, 'puede_cobrar', [
                                'template' => '<div class="form-check form-switch">{input}{label}{error}{hint}</div>',
                            ])->checkbox([
                                'class' => 'form-check-input',
                                'role' => 'switch',
                                'id' => Html::getInputId($model, 'puede_cobrar')
                            ])->label('Puede Cobrar', [
                                'class' => 'form-check-label'
                            ]); ?>

                            <?= $form->field($model, 'puede_post_venta', [
                                'template' => '<div class="form-check form-switch">{input}{label}{error}{hint}</div>',
                            ])->checkbox([
                                'class' => 'form-check-input',
                                'role' => 'switch',
                                'id' => Html::getInputId($model, 'puede_post_venta')
                            ])->label('Puede Post Venta', [
                                'class' => 'form-check-label'
                            ]); ?>

                            <?= $form->field($model, 'puede_registrar', [
                                'template' => '<div class="form-check form-switch">{input}{label}{error}{hint}</div>',
                            ])->checkbox([
                                'class' => 'form-check-input',
                                'role' => 'switch',
                                'id' => Html::getInputId($model, 'puede_registrar')
                            ])->label('Puede Registrar', [
                                'class' => 'form-check-label'
                            ]); ?>
                        </div>
                    </div>
                </div>
            </div>

            ---

            <div class="form-group text-end mt-4">
                <?= Html::submitButton('<i class="fas fa-save"></i> GUARDAR', ['class' => 'btn btn-success btn-lg']) ?>
                <!-- Html::a('<i class="fas fa-arrow-left"></i> VOLVER AL LISTADO', ['index'], ['class' => 'btn btn-outline-secondary btn-lg ms-2']) -->
            </div>
            

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>