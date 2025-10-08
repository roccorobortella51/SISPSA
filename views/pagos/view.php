<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Pagos */

$this->title = 'DETALLE DEL PAGO: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'CONTRATOS', 'url' => ['/contratos/index', 'user_id' => $model->user_id]];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="container-fluid py-4">
    <div class="card shadow-lg mb-4 rounded-3">
        <div class="card-header bg-success text-white text-center py-3 rounded-top-3">
            <h3 class="card-title mb-0 display-3" style="font-size: 1.5em;">
                <i class="fas fa-money-bill-wave me-2"></i> DETALLE DEL PAGO #<?= Html::encode($model->id) ?>
            </h3>
        </div>
        <div class="card-body p-4">
            <div class="d-flex justify-content-between mb-4">
                <?= Html::a('<i class="fas fa-undo me-2"></i> Volver', ['/contratos/index', 'user_id' => $model->user_id], ['class' => 'btn btn-secondary btn-sm rounded-pill px-3 shadow-sm']) ?>
                <?= Html::a('<i class="fas fa-trash me-2"></i> Borrar', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger btn-sm rounded-pill px-3 shadow-sm',
                    'data' => [
                        'confirm' => '¿Estás seguro de que quieres eliminar este pago?',
                        'method' => 'post',
                    ],
                ]) ?>
            </div>

            <h4 class="mt-4 mb-3 text-info border-bottom pb-2"><i class="fas fa-info-circle me-2"></i> Información Principal</h4>
            <div class="row g-3">
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">ID Pago:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode($model->id) ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">ID Recibo:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode($model->recibo_id ? $model->recibo_id : 'N/A') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Afiliado:</strong>
                        <p class="mb-0 fs-5"><?= $model->userDatos->nombres . ' ' . $model->userDatos->apellidos ." ". $model->userDatos->tipo_cedula  . ' ' . $model->userDatos->cedula ?> </p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Solvente:</strong>
                        <p class="mb-0 fs-5"><?= $model->userDatos->estatus_solvente?> </p>
                    </div>
                </div>
            </div>

            <h4 class="mt-5 mb-3 text-info border-bottom pb-2"><i class="fas fa-dollar-sign me-2"></i> Detalles de Pago</h4>
            <div class="row g-3">
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Monto USD:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode(Yii::$app->formatter->asDecimal($model->monto_pagado, 2)) ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Monto Pagado:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode(Yii::$app->formatter->asDecimal($model->monto_usd, 2)) ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Método de Pago:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode($model->metodo_pago ? $model->metodo_pago : 'N/A') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Número de Referencia:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode($model->numero_referencia_pago ? $model->numero_referencia_pago : 'N/A') ?></p>
                    </div>
                </div>
            </div>

            <h4 class="mt-5 mb-3 text-info border-bottom pb-2"><i class="fas fa-calendar-alt me-2"></i> Fechas Relevantes</h4>
           <div class="row g-3">
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Fecha de Pago:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode($model->fecha_pago ? Yii::$app->formatter->asDate($model->fecha_pago, 'php:d-m-Y') : 'Por actualizar') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Fecha de Registro:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode($model->fecha_registro ? Yii::$app->formatter->asDate($model->fecha_registro, 'php:d-m-Y') : 'Por actualizar') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Fecha de Conciliación:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode($model->fecha_conciliacion ? Yii::$app->formatter->asDate($model->fecha_conciliacion, 'php:d-m-Y') : 'Por actualizar') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Creado el:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode($model->created_at ? Yii::$app->formatter->asDate($model->created_at, 'php:d-m-Y H:i') : 'Por actualizar') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Actualizado el:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode($model->updated_at ? Yii::$app->formatter->asDate($model->updated_at, 'php:d-m-Y H:i') : 'Por actualizar') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Eliminado el:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode($model->deleted_at ? Yii::$app->formatter->asDate($model->deleted_at, 'php:d-m-Y H:i') : 'N/A') ?></p>
                    </div>
                </div>
            </div>

            <h4 class="mt-5 mb-3 text-info border-bottom pb-2"><i class="fas fa-clipboard-check me-2"></i> Estado y Conciliación</h4>
            <div class="row g-3">
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Estatus:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode($model->estatus ? $model->estatus : 'N/A') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Conciliado:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode($model->conciliado ? 'Sí' : 'No') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Conciliador ID:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode($model->conciliador_id ? $model->conciliador_id : 'N/A') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Nombre Conciliador:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode($model->nombre_conciliador ? $model->nombre_conciliador : 'N/A') ?></p>
                    </div>
                </div>
            </div>

            <?php if ($model->imagen_prueba): ?>
            <h4 class="mt-5 mb-3 text-info border-bottom pb-2"><i class="fas fa-image me-2"></i> Comprobante de Pago</h4>
            <div class="row g-3">
                <div class="col-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm">
                        <strong class="text-primary d-block mb-3">Imagen de Prueba:</strong>
                        <?= Html::a('Ver comprobante', $model->imagen_prueba, ['target' => '_blank', 'class' => 'btn btn-primary rounded-pill']) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>