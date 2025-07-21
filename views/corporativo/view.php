<?php

use yii\helpers\Html;
use yii\helpers\Url; // Para usar Url::to()

/** @var yii\web\View $this */
/** @var app\models\Corporativo $model */

$this->title = 'DETALLES DEL AFILIADO CORPORATIVO: ' . Html::encode($model->nombre);
$this->params['breadcrumbs'][] = ['label' => 'Corporativos', 'url' => ['index']];
$this->params['breadcrumbs'][] = Html::encode($model->nombre); // Solo el nombre para el breadcrumb
\yii\web\YiiAsset::register($this);
?>

<div class="corporativo-view container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><?= Html::encode($this->title) ?></h3>
        <div class="btn-group" role="group" aria-label="Acciones de Corporativo">
            <?= Html::a(
                '<i class="fas fa-edit"></i> Actualizar',
                ['update', 'id' => $model->id],
                ['class' => 'btn btn-primary btn-xs text-white me-2'] // 'btn-xs' para extra pequeño, 'text-white' y 'me-2' para margen derecho
            ) ?>
            <?= Html::a(
                '<i class="fas fa-trash-alt"></i> Eliminar',
                ['delete', 'id' => $model->id],
                [
                    'class' => 'btn btn-danger btn-xs text-white me-2', // 'btn-xs', 'text-white' y 'me-2'
                    'data' => [
                        'confirm' => '¿Está seguro de que desea eliminar este corporativo?',
                        'method' => 'post',
                    ],
                ]
            ) ?>
            <?= Html::a(
                '<i class="fas fa-undo"></i> Volver',
                Url::to(['index']),
                [
                    'class' => 'btn btn-outline-secondary btn-xs', // 'btn-xs' para extra pequeño
                    'title' => 'Volver a la lista de corporativos',
                ]
            ) ?>
        </div>
    </div>

    <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-header bg-primary"> 
            <h3 class="card-title text-bold text-white">Información General del Corporativo</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Nombre:</strong> <?= Html::encode($model->nombre) ?></p>
                    <p><strong>Email:</strong> <?= Html::a(Html::encode($model->email), 'mailto:' . Html::encode($model->email)) ?></p>
                    <p><strong>Teléfono:</strong> <?= Html::encode($model->telefono) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>RIF:</strong> <?= Html::encode($model->rif) ?></p>
                    <p><strong>Estatus:</strong> <?= Html::encode($model->estatus) ?></p>
                    <p><strong>Código Asesor:</strong> <?= Html::encode($model->codigo_asesor) ?></p>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <p><strong>Fecha de Creación:</strong> <?= Html::encode(Yii::$app->formatter->asDatetime($model->created_at, 'medium')) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Última Actualización:</strong> <?= Html::encode(Yii::$app->formatter->asDatetime($model->updated_at, 'medium')) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-info shadow-sm mb-4">
        <div class="card-header bg-info"> 
            <h3 class="card-title text-bold text-white">Ubicación Geográfica</h3> 
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Estado:</strong> <?= Html::encode($model->rmEstado ? $model->rmEstado->nombre : $model->estado) ?></p>
                    <p><strong>Municipio:</strong> <?= Html::encode($model->rmMunicipio ? $model->rmMunicipio->nombre : $model->municipio) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Parroquia:</strong> <?= Html::encode($model->rmParroquia ? $model->rmParroquia->nombre : $model->parroquia) ?></p>
                    <p><strong>Ciudad:</strong> <?= Html::encode($model->rmCiudad ? $model->rmCiudad->nombre : $model->ciudad) ?></p>
                </div>
            </div>
            <p class="mt-3"><strong>Dirección:</strong> <?= nl2br(Html::encode($model->direccion)) ?></p>
        </div>
    </div>

    <div class="card card-outline card-secondary shadow-sm mb-4">
        <div class="card-header bg-secondary"> 
            <h3 class="card-title text-bold text-white">Información Registral</h3> 
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Lugar de Registro:</strong> <?= Html::encode($model->lugar_registro) ?></p>
                    <p><strong>Fecha Registro Mercantil:</strong> <?= Html::encode(Yii::$app->formatter->asDate($model->fecha_registro_mercantil, 'long')) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Tomo de Registro:</strong> <?= Html::encode($model->tomo_registro) ?></p>
                    <p><strong>Folio de Registro:</strong> <?= Html::encode($model->folio_registro) ?></p>
                </div>
            </div>
            <p class="mt-3"><strong>Domicilio Fiscal:</strong> <?= nl2br(Html::encode($model->domicilio_fiscal)) ?></p>
        </div>
    </div>

    <div class="card card-outline card-warning shadow-sm mb-4">
        <div class="card-header bg-warning"> 
            <h3 class="card-title text-bold text-white">Contacto Principal</h3> 
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Nombre Contacto:</strong> <?= Html::encode($model->contacto_nombre) ?></p>
                    <p><strong>Cédula Contacto:</strong> <?= Html::encode($model->contacto_cedula) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Teléfono Contacto:</strong> <?= Html::encode($model->contacto_telefono) ?></p>
                    <p><strong>Cargo Contacto:</strong> <?= Html::encode($model->contacto_cargo) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-success shadow-sm mb-4">
                <div class="card-header bg-success"> 
                    <h3 class="card-title text-bold text-white">Clínicas Asociadas</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($model->clinicas)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($model->clinicas as $clinica): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        <?= Html::a(Html::encode($clinica->nombre), ['rm-clinica/view', 'id' => $clinica->id], ['class' => 'text-primary text-bold']) ?>
                                        <small class="text-muted">(RIF: <?= Html::encode($clinica->rif) ?>)</small>
                                    </span>
                                    <i class="fas fa-hospital text-muted"></i>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">No hay clínicas asociadas a este corporativo.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-outline card-dark shadow-sm mb-4">
                <div class="card-header bg-dark"> 
                    <h3 class="card-title text-bold text-white">Empleados Asociados</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($model->users)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($model->users as $user): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        <?php
                                            $nombreCompleto = '';
                                            if ($user->userDatos) {
                                                $nombreCompleto = $user->userDatos->nombres . ' ' . $user->userDatos->apellidos;
                                            } else {
                                                $nombreCompleto = $user->username; // Fallback
                                            }
                                        ?>
                                        <?= Html::a(Html::encode($nombreCompleto), ['user/view', 'id' => $user->id], ['class' => 'text-primary text-bold']) ?>
                                        <small class="text-muted">(Usuario: <?= Html::encode($user->username) ?>)</small>
                                    </span>
                                    <i class="fas fa-user-tie text-muted"></i>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">No hay empleados asociados a este corporativo.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>