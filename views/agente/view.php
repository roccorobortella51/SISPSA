<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Agente $model */

$this->title = $model->nom; // Changed title for better context
$this->params['breadcrumbs'][] = ['label' => 'AGENCIAS', 'url' => ['index']];
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
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh text-center">
                <h3 class="mb-0"><?= Html::encode($this->title) ?></h3>
            <div class="ms-panel-header">
            </div>
        </div>
    </div> 
       
<div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
    <div class="col">
        <?= Html::a(
            '<i class="fas fa-edit"></i> ACTUALIZAR AGENCIA', // Icono para editar
            ['update', 'id' => $model->id],
            ['class' => 'btn btn-primary w-100'] // Eliminada la clase btn-lg
        ) ?>
    </div>

    <div class="col">
        <?= Html::a(
            '<i class="fas fa-list"></i> VOLVER A LA LISTA', // Icono para lista
            ['index'],
            ['class' => 'btn btn-primary w-100'] // Eliminada la clase btn-lg
        ) ?>
    </div>

    <div class="col">
        <?= Html::a(
            '<i class="fas fa-trash-alt"></i> ELIMINAR AGENCIA', // Icono para eliminar
            ['delete', 'id' => $model->id],
            [
                'class' => 'btn btn-danger w-100', // Eliminada la clase btn-lg
                'data' => [
                    'confirm' => '¿Estás seguro de que quieres eliminar este agente? Esta acción no se puede deshacer.',
                    'method' => 'post',
                ],
            ]
        ) ?>
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
                <div class="col-md-6">
                    <div class="card card-body bg-light text-center">
                        <h6 class="text-muted">Fecha de Creación</h6>
                        <p class="h5 text-dark"><?= formatDateTime($model->created_at) ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-body bg-light text-center">
                        <h6 class="text-muted">Última Actualización</h6>
                        <p class="h5 text-dark"><?= formatDateTime($model->updated_at) ?></p>
                    </div>
                </div>
                <!-- <div class="col-md-4">
                    <div class="card card-body bg-light text-center">
                        <h6 class="text-muted">Fecha de Eliminación</h6>
                        <p class="h5 text-dark"><?= formatDateTime($model->deleted_at) ?></p>
                    </div>
           
</div> -->
