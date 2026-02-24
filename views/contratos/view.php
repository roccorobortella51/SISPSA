<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Contratos */

$this->title = 'DETALLE DEL CONTRATO: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'CONTRATO', 'url' => ['index', 'user_id' => $model->user_id]];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="container-fluid py-4">
    <div class="card shadow-lg mb-4 rounded-3">
        <div class="card-header bg-primary text-white text-center py-3 rounded-top-3">
            <h3 class="card-title mb-0 display-3" style="font-size: 1.5em;">
                <i class="fas fa-file-contract me-2"></i> DETALLE DEL CONTRATO #<?= Html::encode($model->id) ?>
            </h3>
        </div>
        <div class="card-body p-4">
            <div class="d-flex justify-content-between mb-4">
                <?= Html::a('<i class="fas fa-undo me-2"></i> Volver', ['index', 'user_id' => $model->user_id], ['class' => 'btn btn-secondary btn-sm rounded-pill px-3 shadow-sm']) ?>
            </div>

            <h4 class="mt-4 mb-3 text-info border-bottom pb-2"><i class="fas fa-info-circle me-2"></i> Información General</h4>
            <div class="row g-3">
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">ID Contrato:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode($model->id) ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Plan:</strong>
                        <p class="mb-0 fs-5"><?php echo Html::encode($model->plan ? $model->plan->nombre : 'N/A'); ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Ente:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode($model->ente_id ? $model->ente_id : 'N/A') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Clínica:</strong>
                        <p class="mb-0 fs-5"><?php echo Html::encode($model->clinica ? $model->clinica->nombre : 'N/A'); ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Fecha Inicio:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode(Yii::$app->formatter->asDate($model->fecha_ini, 'php:d-m-Y')) ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Fecha Vencimiento:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode(Yii::$app->formatter->asDate($model->fecha_ven, 'php:d-m-Y')) ?></p>
                    </div>
                </div>
            </div>

            <h4 class="mt-5 mb-3 text-info border-bottom pb-2"><i class="fas fa-dollar-sign me-2"></i> Detalles de Pago</h4>
            <div class="row g-3">
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Monto:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode(Yii::$app->formatter->asDecimal($model->monto, 2)) ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Frecuencia de Pago:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode($model->frecuencia_pago ? $model->frecuencia_pago : 'N/A') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Moneda:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode($model->moneda ? $model->moneda : 'N/A') ?></p>
                    </div>
                </div>
            </div>

            <h4 class="mt-5 mb-3 text-info border-bottom pb-2"><i class="fas fa-clipboard-list me-2"></i> Estado y Otros</h4>
            <div class="row g-3">
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Estatus:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode($model->estatus ? $model->estatus : 'N/A') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Número de Contrato:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode($model->nrocontrato ? $model->nrocontrato : 'N/A') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Sucursal:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode($model->sucursal ? $model->sucursal : 'N/A') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Anulado Por:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode($model->anulado_por ? $model->anulado_por : 'N/A') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Fecha Anulación:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode($model->anulado_fecha ? Yii::$app->formatter->asDate($model->anulado_fecha, 'php:d-m-Y') : 'N/A') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Motivo Anulación:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode($model->anulado_motivo ? $model->anulado_motivo : 'N/A') ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">Usuario:</strong>
                        <p class="mb-0 fs-5"><?php echo Html::encode($model->user ? $model->user->nombres : 'N/A'); ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="detail-item-card p-4 border rounded-3 bg-light shadow-sm h-100">
                        <strong class="text-primary d-block mb-1">PDF:</strong>
                        <p class="mb-0 fs-5"><?= Html::encode($model->pdf ? $model->pdf : 'N/A') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>