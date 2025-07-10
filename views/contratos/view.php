<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Contratos */

// Establece el título de la página
$this->title = 'DETALLE DEL CONTRATO: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'CONTRATO', 'url' => ['index', 'user_id' => $model->user_id]];
$this->params['breadcrumbs'][] = $this->title;

// Puedes mantener tus estilos CSS personalizados si aún los necesitas para algo específico,
// aunque gran parte de la responsividad la manejaremos con las clases de Bootstrap.
$css = <<<CSS
/* Si aún necesitas estilos específicos para ajustar el grid de los detalles */
.detail-item-card {
    margin-bottom: 15px; /* Espacio entre cada tarjeta de detalle */
}
CSS;
$this->registerCss($css);

?>

<div class="container-fluid">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="card-title mb-0">
                <i class="fas fa-file-contract mr-2"></i> DETALLE DEL CONTRATO #<?= Html::encode($model->id) ?>
            </h4>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-end">
                    <?php Html::a('<i class="fas fa-pencil-alt"></i> Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning btn-sm mr-2']) ?>
                    <?php Html::a('<i class="fas fa-trash-alt"></i> Eliminar', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger btn-sm',
                        'data' => [
                            'confirm' => '¿Estás seguro de que quieres eliminar este contrato?',
                            'method' => 'post',
                        ],
                    ]) ?>
                    <?= Html::a('<i class="fas fa-undo"></i> Volver', ['index', 'user_id' => $model->user_id], ['class' => 'btn btn-primary btn-md']) ?>
                </div>
            </div>

            <h5 class="mt-4 mb-3 text-info"><i class="fas fa-info-circle mr-2"></i> Información General</h5>
            <hr>
            <div class="row">
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="detail-item-card p-3 border rounded bg-light">
                        <strong>ID Contrato:</strong>
                        <p class="mb-0"><?= Html::encode($model->id) ?></p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="detail-item-card p-3 border rounded bg-light">
                        <strong>Plan:</strong>
                        <p class="mb-0"><?php if($model->plan){echo Html::encode($model->plan->nombre); } ?></p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="detail-item-card p-3 border rounded bg-light">
                        <strong>Ente:</strong>
                        <p class="mb-0"><?= Html::encode($model->ente_id) ?></p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="detail-item-card p-3 border rounded bg-light">
                        <strong>Clínica:</strong>
                        <p class="mb-0"><?php if($model->plan){ echo Html::encode($model->clinica->nombre); } ?></p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="detail-item-card p-3 border rounded bg-light">
                        <strong>Fecha Inicio:</strong>
                        <p class="mb-0"><?= Html::encode(Yii::$app->formatter->asDate($model->fecha_ini, 'php:d-m-Y')) ?></p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="detail-item-card p-3 border rounded bg-light">
                        <strong>Fecha Vencimiento:</strong>
                        <p class="mb-0"><?= Html::encode(Yii::$app->formatter->asDate($model->fecha_ven, 'php:d-m-Y')) ?></p>
                    </div>
                </div>
            </div>

            <h5 class="mt-4 mb-3 text-info"><i class="fas fa-dollar-sign mr-2"></i> Detalles de Pago</h5>
            <hr>
            <div class="row">
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="detail-item-card p-3 border rounded bg-light">
                        <strong>Monto:</strong>
                        <p class="mb-0"><?= Html::encode(Yii::$app->formatter->asDecimal($model->monto, 2)) ?></p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="detail-item-card p-3 border rounded bg-light">
                        <strong>Frecuencia de Pago:</strong>
                        <p class="mb-0"><?= Html::encode($model->frecuencia_pago) ?></p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="detail-item-card p-3 border rounded bg-light">
                        <strong>Moneda:</strong>
                        <p class="mb-0"><?= Html::encode($model->moneda) ?></p>
                    </div>
                </div>
            </div>

            <h5 class="mt-4 mb-3 text-info"><i class="fas fa-clipboard-list mr-2"></i> Estado y Otros</h5>
            <hr>
            <div class="row">
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="detail-item-card p-3 border rounded bg-light">
                        <strong>Estatus:</strong>
                        <p class="mb-0"><?= Html::encode($model->estatus) ?></p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="detail-item-card p-3 border rounded bg-light">
                        <strong>Número de Contrato:</strong>
                        <p class="mb-0"><?= Html::encode($model->nrocontrato) ?></p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="detail-item-card p-3 border rounded bg-light">
                        <strong>Sucursal:</strong>
                        <p class="mb-0"><?= Html::encode($model->sucursal) ?></p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="detail-item-card p-3 border rounded bg-light">
                        <strong>Anulado Por:</strong>
                        <p class="mb-0"><?= Html::encode($model->anulado_por) ?></p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="detail-item-card p-3 border rounded bg-light">
                        <strong>Fecha Anulación:</strong>
                        <p class="mb-0"><?= Html::encode($model->anulado_fecha) ?></p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="detail-item-card p-3 border rounded bg-light">
                        <strong>Motivo Anulación:</strong>
                        <p class="mb-0"><?= Html::encode($model->anulado_motivo) ?></p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="detail-item-card p-3 border rounded bg-light">
                        <strong>Usuario:</strong>
                        <p class="mb-0"><?php if($model->user){echo Html::encode($model->user->nombres); } ?></p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="detail-item-card p-3 border rounded bg-light">
                        <strong>PDF:</strong>
                        <p class="mb-0"><?= Html::encode($model->PDF) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>