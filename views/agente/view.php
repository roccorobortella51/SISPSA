<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Agente $model */

$this->title = 'Detalles de Agencia ' . $model->nom; // Changed title for better context
$this->params['breadcrumbs'][] = ['label' => 'Agentes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->nom; // Changed breadcrumb to agent's name
\yii\web\YiiAsset::register($this);

// Function to format percentages (assuming they are stored as integers representing percentage points)
function formatPercentage($value) {
    return Yii::$app->formatter->asPercent($value / 100);
}

// Function to format dates
function formatDateTime($value) {
    return $value ? Yii::$app->formatter->asDatetime($value) : 'N/A';
}

?>

<div class="agente-view">

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0"><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="card-body">

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="ms-panel ms-widget ms-identifier-widget bg-info">
                        <div class="ms-panel-header header-mini">
                            <h6 style="margin: 0;">
                                <?= Html::a(
                                    'ACTUALIZAR AGENCIA',
                                    ['update', 'id' => $model->id],
                                    ['class' => 'text-white']
                                ) ?>
                            </h6>
                        </div>
                        <div class="ms-panel-body">
                            <div class="text-center">
                                <i class="flaticon-information"></i>
                                <p class="mb-0">Modifica los datos de este agente.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="ms-panel ms-widget ms-identifier-widget bg-info">
                        <div class="ms-panel-header header-mini">
                            <h6 style="margin: 0;">
                                <?= Html::a(
                                    'VOLVER A LA LISTA',
                                    ['index'],
                                    ['class' => 'text-white']
                                ) ?>
                            </h6>
                        </div>
                        <div class="ms-panel-body">
                            <div class="text-center">
                                <i class="flaticon-information"></i>
                                <p class="mb-0">Regresa a la lista completa de agentes.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="ms-panel ms-widget ms-identifier-widget bg-danger">
                        <div class="ms-panel-header header-mini">
                            <h6 style="margin: 0;">
                                <?= Html::a(
                                    'ELIMINAR AGENCIA',
                                    ['delete', 'id' => $model->id],
                                    [
                                        'class' => 'text-white',
                                        'data' => [
                                            'confirm' => '¿Estás seguro de que quieres eliminar este agente? Esta acción no se puede deshacer.',
                                            'method' => 'post',
                                        ],
                                    ]
                                ) ?>
                            </h6>
                        </div>
                        <div class="ms-panel-body">
                            <div class="text-center">
                                <i class="flaticon-information"></i>
                                <p class="mb-0">Elimina este agente de forma permanente.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

         

            <h5 class="mt-4 mb-3">Porcentajes de Comisión</h5>
            <div class="row mb-4 g-3">
                <div class="col-md-4">
                    <div class="card card-body bg-light text-center">
                        <h6 class="text-muted">Porcentaje Venta</h6>
                        <p class="h4 text-info"><?= formatPercentage($model->por_venta) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-body bg-light text-center">
                        <h6 class="text-muted">Porcentaje Asesoría</h6>
                        <p class="h4 text-info"><?= formatPercentage($model->por_asesor) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-body bg-light text-center">
                        <h6 class="text-muted">Porcentaje Cobranza</h6>
                        <p class="h4 text-info"><?= formatPercentage($model->por_cobranza) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-body bg-light text-center">
                        <h6 class="text-muted">Porcentaje Post Venta</h6>
                        <p class="h4 text-info"><?= formatPercentage($model->por_post_venta) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-body bg-light text-center">
                        <h6 class="text-muted">Porcentaje Agente</h6>
                        <p class="h4 text-info"><?= formatPercentage($model->por_agente) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-body bg-light text-center">
                        <h6 class="text-muted">Porcentaje Máximo</h6>
                        <p class="h4 text-info"><?= formatPercentage($model->por_max) ?></p>
                    </div>
                </div>
            </div>

     

            <h5 class="mt-4 mb-3">Fechas de Gestión</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card card-body bg-light text-center">
                        <h6 class="text-muted">Fecha de Creación</h6>
                        <p class="h5 text-dark"><?= formatDateTime($model->created_at) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-body bg-light text-center">
                        <h6 class="text-muted">Última Actualización</h6>
                        <p class="h5 text-dark"><?= formatDateTime($model->updated_at) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-body bg-light text-center">
                        <h6 class="text-muted">Fecha de Eliminación</h6>
                        <p class="h5 text-dark"><?= formatDateTime($model->deleted_at) ?></p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
