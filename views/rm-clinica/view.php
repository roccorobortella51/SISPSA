<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2; // Para los selectores de estado y estatus
use yii\widgets\MaskedInput; // Para campos con máscaras como RIF y teléfono
use yii\helpers\Url; // Necesario para Url::to
use app\components\UserHelper; // Asumo que esto se usa en getAgenteFuerzaList() y getClinicasList()

/** @var yii\web\View $this */
/** @var app\models\RmClinica $model */
/** @var yii\widgets\ActiveForm $form */
/** @var array $listaEstados */ // Espera la lista de estados para el Select2
/** @var array $listaEstatus */ // Espera la lista de estatus para el Select2
/** @var string $mode */ // Para saber si es 'create' o 'edit'
/** @var bool $isNewRecord */ // Para saber si es un nuevo registro

// Asegúrate de que estas variables siempre tengan un valor para evitar errores
// si el controlador no las pasa por alguna razón (aunque el controlador sí las pasa).
$listaEstados = $listaEstados ?? [];
$listaEstatus = $listaEstatus ?? [];
$mode = $mode ?? 'create'; // Por defecto es 'create' si no se especifica
$isNewRecord = $isNewRecord ?? true; // Por defecto es true para este formulario

$this->title = 'DETALLES DE LA CLINICA: ' . $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'CLINICAS', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->nombre, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'ACTUALIZAR';

?>

<style>
.nav-tabs .nav-link.active {
    background-color: #007bff !important; /* Bootstrap primary blue */
    color: white !important;
    border-color: #007bff #007bff #fff !important;
}
</style>

<div class="col-md-12 text-end">
    <div class="float-right" style="margin-bottom:10px;">
        <?= Html::a('<i class="fas fa-undo"></i> Volver', ['index'], ['class' => 'btn btn-info btn-lg']) ?>
    </div>
</div>

<div class="col-xl-12 col-md-12">
    <div class="ms-panel ms-panel-fh">
        <div class="ms-panel-header">
            <h1><?= Html::encode($this->title = 'Detalle de la Clínica'); ?></h1>
        </div>
        <div class="ms-panel-body">

            <div class="row row-cols-1 row-cols-md-4 g-3 mb-4">
                <div class="col">
                    <?= Html::a(
                        '<i class="fas fa-file-invoice-dollar"></i> Baremo',
                        ['baremo/index', 'clinica_id' => $model->id],
                        ['class' => 'btn btn-primary btn-lg w-100']
                    ) ?>
                </div>
                <div class="col">
                    <?= Html::a(
                        '<i class="fas fa-clipboard-list"></i> Planes',
                        ['planes/index', 'clinica_id' => $model->id],
                        ['class' => 'btn btn-primary btn-lg w-100']
                    ) ?>
                </div>
                <div class="col">
                    <?= Html::a(
                        '<i class="fas fa-users"></i> Afiliados',
                        ['user-datos/index', 'clinica_id' => $model->id],
                        ['class' => 'btn btn-primary btn-lg w-100']
                    ) ?>
                </div>
                <div class="col">
                    <?= Html::a(
                        '<i class="fas fa-tasks"></i> Check List',
                        ['user-datos/index', 'clinica_id' => $model->id],
                        ['class' => 'btn btn-primary btn-lg w-100']
                    ) ?>
                </div>
            </div>
            <?php $form = ActiveForm::begin([]); ?>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'nombre')->textInput([
                        'readonly' => true,
                        'autofocus' => true,
                        'placeholder' => 'Ingrese el nombre de la clínica',
                        'class' => 'form-control form-control-lg',
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'rif')->widget(MaskedInput::class, [
                        'mask' => 'J-99999999-9',
                        'options' => [
                            'placeholder' => 'J-XXXXXXXX-X',
                            'class' => 'form-control form-control-lg',
                            'readonly' => true,
                        ]
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'telefono')->widget(MaskedInput::class, [
                        'mask' => '(9999) 999-9999',
                        'options' => [
                            'placeholder' => '(XXXX) XXX-XXXX',
                            'class' => 'form-control form-control-lg',
                            'readonly' => true,
                        ]
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                   <?= $form->field($model, 'correo')->textInput([
                    'readonly' => true,
                    'placeholder' => 'Ingrese el correo electrónico',
                    'class' => 'form-control form-control-lg',
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'estado')->textInput([
                    'readonly' => true,
                    'placeholder' => 'Ingrese el correo electrónico',
                    'class' => 'form-control form-control-lg',
                    'value' => $model->estado
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'estatus')->widget(Select2::classname(), [
                        'data' => $listaEstatus,
                        'options' => [
                            'placeholder' => 'Seleccione un estatus...',
                            'class' => 'form-control form-control-lg',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($model, 'direccion')->textarea([
                        'rows' => 3,
                        'readonly' => true,
                        'placeholder' => 'Ingrese la dirección completa',
                        'class' => 'form-control form-control-lg',
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'webpage')->textInput(['readonly' => true, 'placeholder' => 'Ej: www.ejemplo.com', 'class' => 'form-control form-control-lg',]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'rs_instagram')->textInput(['readonly' => true, 'placeholder' => 'Ej: @tu_clinica', 'class' => 'form-control form-control-lg',]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'codigo_clinica')->textInput(['readonly' => true, 'placeholder' => 'Código interno de clínica', 'class' => 'form-control form-control-lg',]) ?>
                </div>
            </div>

            <div class="row mt-4 mb-3"> <div class="col-12 d-flex justify-content-center gap-3"> <?= Html::a('Actualizar', ['update', 'id' => $model->id], ['class' => 'btn btn-lg btn-success']) ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>