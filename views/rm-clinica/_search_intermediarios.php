<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\IntermediariosByClinicaSearch */
/* @var $clinic app\models\RmClinica */

// Safely get the clinic ID
$clinicId = null;
if (isset($clinic) && $clinic) {
    $clinicId = $clinic->id;
} else {
    // Try to get from request
    $clinicId = Yii::$app->request->get('id');
}

// Ensure model exists
if (!isset($model) || $model === null) {
    $model = new \app\models\IntermediariosByClinicaSearch();
}

// If no clinic ID is available, show a warning and don't render the form
if (!$clinicId) {
    echo '<div class="alert alert-warning">No se pudo identificar la clínica para filtrar los intermediarios.</div>';
    return;
}
?>

<div class="intermediarios-search">
    <?php $form = ActiveForm::begin([
        'action' => ['intermediarios', 'id' => $clinicId],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1,
            'class' => 'mb-3',
        ],
    ]); ?>

    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-12">
            <?= $form->field($model, 'nombre_completo', [
                'inputOptions' => ['class' => 'form-control form-control-sm', 'placeholder' => 'Buscar por nombre...'],
            ])->textInput()->label(false) ?>
        </div>
        <div class="col-lg-2 col-md-6 col-sm-12">
            <?= $form->field($model, 'cedula', [
                'inputOptions' => ['class' => 'form-control form-control-sm', 'placeholder' => 'Cédula...'],
            ])->textInput()->label(false) ?>
        </div>
        <div class="col-lg-2 col-md-6 col-sm-12">
            <?= $form->field($model, 'email', [
                'inputOptions' => ['class' => 'form-control form-control-sm', 'placeholder' => 'Correo...'],
            ])->textInput()->label(false) ?>
        </div>
        <div class="col-lg-2 col-md-6 col-sm-12">
            <?= $form->field($model, 'codigo_sudeaseg', [
                'inputOptions' => ['class' => 'form-control form-control-sm', 'placeholder' => 'Código SUDEASEG...'],
            ])->textInput()->label(false) ?>
        </div>
        <div class="col-lg-3 col-md-12 col-sm-12 d-flex align-items-end">
            <div class="btn-group w-100">
                <?= Html::submitButton('<i class="fas fa-search"></i> Buscar', ['class' => 'btn btn-primary btn-sm']) ?>
                <?= Html::resetButton('<i class="fas fa-undo"></i> Limpiar', ['class' => 'btn btn-outline-secondary btn-sm']) ?>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-lg-12">
            <div class="d-flex flex-wrap align-items-center">
                <span class="text-muted small mr-3">
                    <i class="fas fa-filter"></i> Filtros rápidos:
                </span>
                <div class="btn-group btn-group-sm flex-wrap" role="group">
                    <?= Html::a(
                        'Todos',
                        ['intermediarios', 'id' => $clinicId],
                        ['class' => 'btn btn-outline-secondary btn-sm ' . (empty($model->puede_vender) && empty($model->puede_asesorar) && empty($model->puede_registrar) && empty($model->puede_cobrar) ? 'active' : '')]
                    ) ?>
                    <?= Html::a(
                        'Pueden Vender',
                        ['intermediarios', 'id' => $clinicId, 'IntermediariosByClinicaSearch[puede_vender]' => 1],
                        ['class' => 'btn btn-outline-success btn-sm ' . ($model->puede_vender == 1 ? 'active' : '')]
                    ) ?>
                    <?= Html::a(
                        'Pueden Registrar',
                        ['intermediarios', 'id' => $clinicId, 'IntermediariosByClinicaSearch[puede_registrar]' => 1],
                        ['class' => 'btn btn-outline-primary btn-sm ' . ($model->puede_registrar == 1 ? 'active' : '')]
                    ) ?>
                    <?= Html::a(
                        'Pueden Asesorar',
                        ['intermediarios', 'id' => $clinicId, 'IntermediariosByClinicaSearch[puede_asesorar]' => 1],
                        ['class' => 'btn btn-outline-info btn-sm ' . ($model->puede_asesorar == 1 ? 'active' : '')]
                    ) ?>
                    <?= Html::a(
                        'Pueden Cobrar',
                        ['intermediarios', 'id' => $clinicId, 'IntermediariosByClinicaSearch[puede_cobrar]' => 1],
                        ['class' => 'btn btn-outline-warning btn-sm ' . ($model->puede_cobrar == 1 ? 'active' : '')]
                    ) ?>
                </div>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$this->registerCss("
    .intermediarios-search .btn-group .btn.active {
        z-index: 2;
        color: #fff;
        background-color: #007bff;
        border-color: #007bff;
    }
    .intermediarios-search .btn-group .btn-outline-success.active {
        background-color: #28a745;
        border-color: #28a745;
        color: white;
    }
    .intermediarios-search .btn-group .btn-outline-primary.active {
        background-color: #007bff;
        border-color: #007bff;
        color: white;
    }
    .intermediarios-search .btn-group .btn-outline-info.active {
        background-color: #17a2b8;
        border-color: #17a2b8;
        color: white;
    }
    .intermediarios-search .btn-group .btn-outline-warning.active {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #212529;
    }
");
?>