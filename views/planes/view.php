<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\GridView;

/* @var $this yii\web\View */
/* @var $model app\models\Planes */
/* @var $itemsCobertura app\models\PlanesItemsCobertura[] */
/* @var $baremosFaltantes app\models\Baremo[] */

$this->title = "DETALLES DEL PLÁN: " . $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'PLANES', 'url' => ['index', 'clinica_id' => $model->clinica_id]];
$this->params['breadcrumbs'][] = $this->title;

// Formatear fechas
$createdAt = Yii::$app->formatter->asDatetime($model->created_at);
$updatedAt = Yii::$app->formatter->asDatetime($model->updated_at);
?>
<div class="planes-view">

    <!-- Header con título y botones -->
    <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0"><?= Html::encode($this->title) ?></h1> 

    </div>

    <div class="row">
        <!-- Columna izquierda - Datos del Plan -->
        <div class="col-lg-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Información General
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h5 class="text-muted mb-1">Nombre del Plan</h5>
                                <p class="lead"><?= Html::encode($model->nombre) ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <h5 class="text-muted mb-1">Precio</h5>
                                <p class="lead text-success font-weight-bold">
                                    <?= $model->precio ?>
                                </p>
                            </div>
                            
                            <div class="mb-3">
                                <h5 class="text-muted mb-1">Comisión</h5>
                                <p class="lead">
                                    <?= $model->comision ? $model->comision . '%' : 'N/A' ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h5 class="text-muted mb-1">Estatus</h5>
                                <span class="badge badge-<?= $model->estatus == 'activo' ? 'success' : 'secondary' ?> p-2">
                                    <?= strtoupper($model->estatus) ?>
                                </span>
                            </div>
                            
                            <div class="mb-3">
                                <h5 class="text-muted mb-1">Rango de Edad</h5>
                                <p class="lead">
                                    <?= $model->edad_minima ?> - 
                                    <?= $model->edad_limite ? $model->edad_limite : 'Sin límite' ?> años
                                </p>
                            </div>
                            
                            <div class="mb-3">
                                <h5 class="text-muted mb-1">Clínica</h5>
                                <p><?= $model->clinica ? $model->clinica->nombre : 'N/A' ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h5 class="text-muted mb-2">Descripción</h5>
                        <div class="border rounded p-3 bg-light">
                            <?= $model->descripcion ? nl2br(Html::encode($model->descripcion)) : 'Sin descripción' ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-muted small">
                    <div class="row">
                        <div class="col-md-6">
                            <i class="far fa-calendar-plus"></i> Creado: <?= $createdAt ?>
                        </div>
                        <div class="col-md-6 text-right">
                            <i class="far fa-calendar-check"></i> Actualizado: <?= $updatedAt ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna derecha - Coberturas -->
        <div class="col-lg-6">
            <!-- Tarjeta de coberturas incluidas -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-shield-alt"></i> Coberturas Incluidas
                        <span class="badge badge-light float-right">
                            <?= count($itemsCobertura) ?> servicios
                        </span>
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Servicio (Baremos)</th>
                                    <!-- <th class="text-center">Cobertura</th> -->
                                    <th class="text-center">Límite</th>
                                    <th class="text-center">Espera</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($itemsCobertura)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                                            No hay coberturas registradas
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($itemsCobertura as $item): ?>
                                        <tr>
                                            <td>
                                                <strong><?= Html::encode($item->baremo->nombre_servicio) ?></strong>
                                            </td>
                                            <?php /* ?>
                                            <td class="text-center">
                                                <span class="badge badge-pill badge-primary">
                                                    <?php $item->porcentaje_cobertura ?>%
                                                </span>
                                            </td>
                                            <?php */ ?>
                                            <td class="text-center">
                                                 <span class="badge badge-pill badge-warning">
                                                   <?= $item->cantidad_limite ?: 'N/A' ?>
                                                </span>
                                            </td>

                                            <td class="text-center">
                                                <span class="badge badge-pill badge-primary">
                                                   <?= $item->plazo_espera ?: 'N/A' ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de servicios disponibles para agregar -->
            <?php if (!empty($baremosFaltantes)): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-plus-circle"></i> Servicios Disponibles
                            <span class="badge badge-light float-right">
                                <?= count($baremosFaltantes) ?> disponibles
                            </span>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php foreach ($baremosFaltantes as $baremo): ?>
                                <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?= Html::encode($baremo->nombre_servicio) ?></h6>
                                        <small class="text-muted"><?= Html::encode($baremo->descripcion) ?></small>
                                    </div>
                                    <?= Html::a(
                                        '<i class="fas fa-plus"></i> Agregar', 
                                        ['add-cobertura', 'plan_id' => $model->id, 'baremo_id' => $baremo->id],
                                        ['class' => 'btn btn-sm btn-success']
                                    ) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Estilos personalizados -->
<style>
    .card {
        border: none;
        border-radius: 10px;
        overflow: hidden;
        transition: transform 0.2s;
    }
    .card:hover {
        transform: translateY(-2px);
    }
    .card-header {
        border-bottom: none;
        padding: 1.25rem 1.5rem;
    }
    .table th {
        border-top: none;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
    .badge-pill {
        padding: 0.5em 0.8em;
        min-width: 50px;
    }
    .list-group-item {
        border-left: none;
        border-right: none;
    }
    .list-group-item:first-child {
        border-top: none;
    }
    .list-group-item:last-child {
        border-bottom: none;
    }
</style>