<?php
// app/views/receipts/view.php

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = 'Recibo ' . $model->receipt_number;
$this->params['breadcrumbs'][] = ['label' => 'Recibos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="receipt-view">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title mb-0">
                <i class="fas fa-receipt mr-2"></i>
                <?= Html::encode($this->title) ?>
            </h3>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12 text-right">
                    <?= Html::a(
                        '<i class="fas fa-print mr-1"></i> Imprimir',
                        ['print', 'id' => $model->id, 'auto_print' => 1],
                        ['class' => 'btn btn-primary', 'target' => '_blank']
                    ) ?>
                    <?= Html::a(
                        '<i class="fas fa-arrow-left mr-1"></i> Volver',
                        Yii::$app->request->referrer ?: ['/contratos/index'],
                        ['class' => 'btn btn-secondary']
                    ) ?>
                </div>
            </div>

            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'receipt_number',
                    'issue_date:date',
                    [
                        'attribute' => 'amount',
                        'value' => $model->getFormattedAmountWithCurrency(),
                    ],
                    'currency',
                    'payment_method',
                    'reference_number',
                    'coverage_start_date:date',
                    'coverage_end_date:date',
                    [
                        'attribute' => 'status',
                        'value' => $model->getStatusBadge(),
                        'format' => 'raw',
                    ],
                ],
            ]) ?>

            <div class="mt-4">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Vista previa del recibo:</strong>
                </div>
                <div class="receipt-preview border p-3" style="max-height: 600px; overflow-y: auto;">
                    <?= $model->html_content ?>
                </div>
            </div>
        </div>
    </div>
</div>