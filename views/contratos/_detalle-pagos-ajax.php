<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use yii\helpers\Url;

// Define $periodoInfo BEFORE the if statement so it's always available
$periodoInfo = Yii::$app->formatter->asDate($model->fecha_ini);
if ($model->fecha_ven) {
    $periodoInfo .= ' - ' . Yii::$app->formatter->asDate($model->fecha_ven);
} else {
    $periodoInfo .= ' - Sin fecha de vencimiento';
}

// Contract information summary
$contractInfo = [
    'Contrato' => '#' . $model->id,
    'Plan' => $model->plan ? $model->plan->nombre : 'N/A',
    'Cobertura' => $model->plan  ? Yii::$app->formatter->asCurrency($model->plan->cobertura, 'USD') : 'N/A',
    'Clínica' => $model->clinica ? $model->clinica->nombre : 'N/A',
    'Monto Contrato' => $model->monto ? Yii::$app->formatter->asCurrency($model->monto, 'USD') : 'N/A',
    'Periodo' => $periodoInfo,
    'Estatus' => $model->getStatusBadge(),
];

if ($model->estatus === 'Anulado' && $model->anulado_fecha) {
    $contractInfo['Fecha Anulación'] = Yii::$app->formatter->asDate($model->anulado_fecha);
    $contractInfo['Motivo Anulación'] = $model->anulado_motivo ?: 'No especificado';
}

// Use the filtered payments method
$pagosDelContrato = $model->getPagosDelContrato()->all();

// Calculate payment statistics
$totalPagado = 0;
$totalPagos = count($pagosDelContrato);
$lastPaymentDate = null;

foreach ($pagosDelContrato as $pago) {
    $totalPagado += floatval($pago->monto_pagado);
    if (!$lastPaymentDate || strtotime($pago->fecha_pago) > strtotime($lastPaymentDate)) {
        $lastPaymentDate = $pago->fecha_pago;
    }
}
?>

<!-- Contract Information Card -->
<div class="card mb-4 border-primary">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-file-contract mr-2"></i>Información del Contrato
        </h5>
        <span class="badge bg-light text-dark"><?= $model->estatus ?></span>
    </div>
    <div class="card-body">
        <div class="row">
            <?php foreach ($contractInfo as $label => $value): ?>
                <div class="col-md-4 mb-3">
                    <label class="text-muted small d-block"><?= $label ?></label>
                    <div class="font-weight-medium">
                        <?php if ($label === 'Estatus'): ?>
                            <?= $value ?>
                        <?php else: ?>
                            <?= $value ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Payment Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="card-title text-muted mb-1">Total Pagos</h6>
                <h3 class="text-primary"><?= $totalPagos ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="card-title text-muted mb-1">Monto Total</h6>
                <h3 class="text-success"><?= Yii::$app->formatter->asCurrency($totalPagado, 'USD') ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="card-title text-muted mb-1">Último Pago</h6>
                <h5 class="text-info">
                    <?= $lastPaymentDate ? Yii::$app->formatter->asDate($lastPaymentDate) : 'N/A' ?>
                </h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="card-title text-muted mb-1">Promedio por Pago</h6>
                <h5 class="text-warning">
                    <?= $totalPagos > 0 ? Yii::$app->formatter->asCurrency($totalPagado / $totalPagos, 'USD') : 'N/A' ?>
                </h5>
            </div>
        </div>
    </div>
</div>

<!-- Payments Table -->
<?php if (!empty($pagosDelContrato)): ?>
    <div class="card">
        <div class="card-header bg-gradient-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-credit-card mr-2"></i>Historial de Pagos
                <small class="ml-2 text-white">(<?= $totalPagos ?> registros)</small>
            </h5>
            <?php if ($model->estatus !== 'Anulado'): ?>
                <?= Html::a(
                    '<i class="fas fa-plus-circle mr-1"></i> Registrar Pago',
                    Url::to(['pagos/create', 'user_id' => $model->user_id]),
                    [
                        'class' => 'btn btn-light btn-sm',
                        'title' => 'Registrar nuevo pago',
                        'data-pjax' => '0'
                    ]
                ) ?>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <?= GridView::widget([
                    'dataProvider' => new \yii\data\ArrayDataProvider([
                        'allModels' => $pagosDelContrato,
                        'pagination' => [
                            'pageSize' => 10,
                        ],
                        'sort' => [
                            'attributes' => ['fecha_pago', 'monto_pagado', 'monto_usd'],
                            'defaultOrder' => ['fecha_pago' => SORT_DESC],
                        ],
                    ]),
                    'summary' => '<div class="px-3 py-2 text-muted small">
                    Mostrando <b>{begin}-{end}</b> de <b>{totalCount}</b> pagos
                    <span class="float-right">Ordenar por: {sorter}</span>
                </div>',
                    'showPageSummary' => false,
                    'striped' => true,
                    'hover' => true,
                    'responsiveWrap' => false,
                    'options' => ['class' => 'table-striped mb-0'],
                    'headerRowOptions' => ['class' => 'thead-dark'],
                    'tableOptions' => ['class' => 'table mb-0'],
                    'columns' => [
                        [
                            'attribute' => 'fecha_pago',
                            'format' => ['date', 'php:d/m/Y'],
                            'label' => 'Fecha',
                            'headerOptions' => ['style' => 'width: 100px;'],
                            'contentOptions' => ['class' => 'align-middle text-center'],
                        ],
                        [
                            'attribute' => 'monto_pagado',
                            'value' => function ($model) {
                                return '<span class="font-weight-bold text-success">' .
                                    Yii::$app->formatter->asDecimal($model->monto_pagado, 2) .
                                    ' USD</span>';
                            },
                            'format' => 'raw',
                            'label' => 'Monto USD',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'contentOptions' => ['class' => 'align-middle text-center'],
                        ],
                        [
                            'attribute' => 'monto_usd',
                            'value' => function ($model) {
                                return '<span class="text-primary">' .
                                    Yii::$app->formatter->asDecimal($model->monto_usd, 2) .
                                    ' Bs</span>';
                            },
                            'format' => 'raw',
                            'label' => 'Monto Bs',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'contentOptions' => ['class' => 'align-middle text-center'],
                        ],
                        [
                            'attribute' => 'metodo_pago',
                            'label' => 'Método',
                            'contentOptions' => ['class' => 'align-middle text-center'],
                            'value' => function ($model) {
                                $methods = [
                                    'transferencia' => '<i class="fas fa-university mr-1"></i>Transferencia',
                                    'efectivo' => '<i class="fas fa-money-bill-wave mr-1"></i>Efectivo',
                                    'pago_movil' => '<i class="fas fa-mobile-alt mr-1"></i>Pago Móvil',
                                    'zelle' => '<i class="fas fa-exchange-alt mr-1"></i>Zelle',
                                    'paypal' => '<i class="fab fa-paypal mr-1"></i>PayPal',
                                ];
                                return isset($methods[$model->metodo_pago]) ?
                                    $methods[$model->metodo_pago] :
                                    '<span class="text-muted">' . ($model->metodo_pago ?: 'N/A') . '</span>';
                            },
                            'format' => 'raw',
                        ],
                        [
                            'attribute' => 'numero_referencia_pago',
                            'label' => 'Referencia',
                            'contentOptions' => ['class' => 'align-middle text-center'],
                            'value' => function ($model) {
                                return $model->numero_referencia_pago ?
                                    '<span class="font-monospace small">' . $model->numero_referencia_pago . '</span>' :
                                    '<span class="text-muted">Sin ref.</span>';
                            },
                            'format' => 'raw',
                        ],
                        [
                            'attribute' => 'tasa',
                            'value' => function ($model) {
                                return $model->tasa ?
                                    '<span class="badge badge-light">' . Yii::$app->formatter->asDecimal($model->tasa, 2) . '</span>' :
                                    '<span class="text-muted">N/A</span>';
                            },
                            'format' => 'raw',
                            'label' => 'Tasa',
                            'headerOptions' => ['style' => 'width: 80px;'],
                            'contentOptions' => ['class' => 'align-middle text-center'],
                        ],
                        [
                            'attribute' => 'estatus',
                            'format' => 'raw',
                            'label' => 'Estatus',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'contentOptions' => ['class' => 'align-middle text-center'],
                            'value' => function ($pago) {
                                $badges = [
                                    'Conciliado' => 'badge-success',
                                    'Por Conciliar' => 'badge-warning',
                                    'pendiente' => 'badge-info',
                                    'cancelado' => 'badge-danger',
                                    'verificado' => 'badge-primary',
                                ];
                                $estatus = $pago->estatus ?? 'Por Conciliar';
                                $class = $badges[$estatus] ?? 'badge-secondary';
                                $icons = [
                                    'Conciliado' => 'fas fa-check-circle',
                                    'Por Conciliar' => 'fas fa-clock',
                                    'pendiente' => 'fas fa-hourglass-half',
                                    'cancelado' => 'fas fa-times-circle',
                                    'verificado' => 'fas fa-check-double',
                                ];
                                $icon = isset($icons[$estatus]) ? '<i class="' . $icons[$estatus] . ' mr-1"></i>' : '';
                                return Html::tag('span', $icon . $estatus, [
                                    'class' => "badge $class py-1 px-2",
                                    'style' => 'font-size: 0.85em;'
                                ]);
                            },
                        ],
                        [
                            'attribute' => 'imagen_prueba',
                            'format' => 'raw',
                            'label' => 'Comprobante',
                            'headerOptions' => ['style' => 'width: 100px;'],
                            'contentOptions' => ['class' => 'align-middle text-center'],
                            'value' => function ($model) {
                                if ($model->imagen_prueba) {
                                    return Html::a(
                                        '<i class="fas fa-file-invoice-dollar mr-1"></i>Ver',
                                        $model->imagen_prueba,
                                        [
                                            'target' => '_blank',
                                            'title' => 'Ver comprobante de pago',
                                            'class' => 'btn btn-sm btn-outline-primary',
                                            'style' => 'padding: 0.2rem 0.5rem; font-size: 0.85em;',
                                            'data-toggle' => 'tooltip',
                                            'data-placement' => 'top'
                                        ]
                                    );
                                }
                                return '<span class="text-muted small"><i class="fas fa-ban mr-1"></i>Sin comp.</span>';
                            },
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'header' => 'Acciones',
                            'template' => '<div class="btn-group btn-group-sm">{view}{update}</div>',
                            'options' => ['style' => 'width: 90px;'],
                            'headerOptions' => ['class' => 'text-center'],
                            'contentOptions' => ['class' => 'text-center align-middle'],
                            'buttons' => [
                                'view' => function ($url, $model, $key) {
                                    return Html::a(
                                        '<i class="fas fa-eye"></i>',
                                        Url::to(['pagos/view', 'id' => $model->id]),
                                        [
                                            'title' => 'Ver detalles del pago',
                                            'class' => 'btn btn-outline-info',
                                            'style' => 'border-radius: 4px 0 0 4px;',
                                            'data-toggle' => 'tooltip',
                                            'data-placement' => 'top'
                                        ]
                                    );
                                },
                                'update' => function ($url, $model, $key) {
                                    return Html::a(
                                        '<i class="fas fa-edit"></i>',
                                        Url::to(['pagos/update', 'id' => $model->id]),
                                        [
                                            'title' => 'Editar pago',
                                            'class' => 'btn btn-outline-warning',
                                            'style' => 'border-radius: 0 4px 4px 0;',
                                            'data-toggle' => 'tooltip',
                                            'data-placement' => 'top'
                                        ]
                                    );
                                },
                            ],
                        ]
                    ],
                ]); ?>
            </div>
        </div>
        <div class="card-footer bg-light">
            <div class="row">
                <div class="col-md-8">
                    <small class="text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        <?php if ($model->estatus === 'Anulado'): ?>
                            Se muestran solo los pagos realizados antes de la anulación (<?= $model->anulado_fecha ? Yii::$app->formatter->asDate($model->anulado_fecha) : 'N/A' ?>).
                        <?php else: ?>
                            Se muestran los pagos realizados durante la vigencia del contrato.
                        <?php endif; ?>
                    </small>
                </div>
                <div class="col-md-4 text-right">
                    <small class="text-muted">
                        <i class="fas fa-sync-alt mr-1"></i>
                        Última actualización: <?= date('d/m/Y H:i') ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- No Payments Message -->
    <div class="card border-warning">
        <div class="card-header bg-warning text-white">
            <h5 class="mb-0">
                <i class="fas fa-exclamation-circle mr-2"></i>Sin Pagos Registrados
            </h5>
        </div>
        <div class="card-body text-center py-5">
            <div class="mb-4">
                <i class="fas fa-credit-card fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No hay pagos registrados para este contrato</h4>
                <p class="text-muted">No se han registrado pagos durante el periodo activo del contrato.</p>
            </div>

            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="card bg-light">
                        <div class="card-body text-left">
                            <h6 class="card-title text-primary">
                                <i class="fas fa-info-circle mr-2"></i>Información del Periodo:
                            </h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-calendar-alt mr-2 text-info"></i>
                                    <strong>Periodo:</strong> <?= $periodoInfo ?>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-calendar-check mr-2 text-success"></i>
                                    <strong>Inicio:</strong> <?= Yii::$app->formatter->asDate($model->fecha_ini) ?>
                                </li>
                                <?php if ($model->fecha_ven): ?>
                                    <li class="mb-2">
                                        <i class="fas fa-calendar-times mr-2 text-danger"></i>
                                        <strong>Vencimiento:</strong> <?= Yii::$app->formatter->asDate($model->fecha_ven) ?>
                                    </li>
                                <?php endif; ?>
                                <li class="mb-2">
                                    <i class="fas fa-dollar-sign mr-2 text-warning"></i>
                                    <strong>Monto del Contrato:</strong>
                                    <?= $model->monto ? Yii::$app->formatter->asCurrency($model->monto, 'USD') : 'N/A' ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($model->estatus !== 'Anulado'): ?>
                <div class="mt-4">
                    <?= Html::a(
                        '<i class="fas fa-plus-circle mr-2"></i> Registrar Primer Pago',
                        Url::to(['pagos/create', 'user_id' => $model->user_id]),
                        [
                            'class' => 'btn btn-success btn-lg',
                            'title' => 'Registrar el primer pago para este contrato',
                            'data-pjax' => '0'
                        ]
                    ) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Add some CSS for better presentation -->
<style>
    .card {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .card-header {
        border-radius: 8px 8px 0 0 !important;
        font-weight: 600;
    }

    .table th {
        font-weight: 600;
        font-size: 0.9em;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table td {
        font-size: 0.9em;
        vertical-align: middle !important;
    }

    .badge {
        font-size: 0.85em;
        font-weight: 500;
        padding: 0.35em 0.65em;
    }

    .btn-group-sm>.btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        line-height: 1.5;
    }

    .font-monospace {
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    }
</style>

<!-- Initialize tooltips -->
<script>
    $(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>