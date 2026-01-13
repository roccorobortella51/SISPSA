<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use yii\helpers\Url;

// Define $periodoInfo BEFORE the if statement so it's always available
$periodoInfo = 'Periodo del contrato: ' . Yii::$app->formatter->asDate($model->fecha_ini);
if ($model->fecha_ven) {
    $periodoInfo .= ' al ' . Yii::$app->formatter->asDate($model->fecha_ven);
} else {
    $periodoInfo .= ' en adelante';
}

if ($model->estatus === 'Anulado' && $model->anulado_fecha) {
    $periodoInfo .= ' (Anulado el ' . Yii::$app->formatter->asDate($model->anulado_fecha) . ')';
}

// Use the filtered payments method
$pagosDelContrato = $model->getPagosDelContrato()->all();


if (!empty($pagosDelContrato)) {
    echo "<h4>Pagos Realizados para este Contrato</h4>";

    echo "<p class='text-muted'><small>{$periodoInfo}</small></p>";

    echo GridView::widget([
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
        'summary' => 'Mostrando <b>{begin}-{end}</b> de <b>{totalCount}</b> pagos',
        'striped' => true,
        'hover' => true,
        'columns' => [
            [
                'attribute' => 'fecha_pago',
                'format' => ['date', 'php:d/m/Y'],
                'label' => 'Fecha de Pago',
                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle'],
            ],
            [
                'attribute' => 'monto_pagado',
                'value' => function ($model) {
                    return Yii::$app->formatter->asDecimal($model->monto_pagado, 2) . ' USD';
                },
                'label' => 'Monto Pagado (USD)',
                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle'],
            ],
            [
                'attribute' => 'monto_usd',
                'value' => function ($model) {
                    return Yii::$app->formatter->asDecimal($model->monto_usd, 2) . ' Bs';
                },
                'label' => 'Monto en Bs',
                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle'],
            ],
            [
                'attribute' => 'metodo_pago',
                'label' => 'Método',
                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle'],
            ],
            [
                'attribute' => 'numero_referencia_pago',
                'label' => 'Referencia',
                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle'],
            ],
            [
                'attribute' => 'tasa',
                'value' => function ($model) {
                    return $model->tasa ? Yii::$app->formatter->asDecimal($model->tasa, 2) : 'N/A';
                },
                'label' => 'Tasa',
                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle'],
            ],
            [
                'attribute' => 'imagen_prueba',
                'format' => 'raw',
                'label' => 'Comprobante',
                'value' => function ($model) {
                    if ($model->imagen_prueba) {
                        $imageUrl = Url::to(['/pagos/view-image', 'id' => $model->id]);

                        return Html::a(
                            '<i class="fas fa-file-image text-primary"></i> Ver',
                            $model->imagen_prueba,
                            [
                                'target' => '_blank',
                                'title' => 'Ver comprobante',
                                'class' => 'btn btn-sm btn-outline-primary'
                            ]
                        );
                    }
                    return '<span class="text-muted">Sin comprobante</span>';
                },
                'contentOptions' => ['style' => 'text-align: center;'],
            ],
            [
                'attribute' => 'estatus',
                'format' => 'raw',
                'value' => function ($pago) {
                    $badges = [
                        'Conciliado' => 'badge-success',
                        'Por Conciliar' => 'badge-warning',
                        'pendiente' => 'badge-info',
                        'cancelado' => 'badge-danger',
                    ];
                    $estatus = $pago->estatus ?? 'Por Conciliar';
                    $class = $badges[$estatus] ?? 'badge-secondary';
                    return Html::tag('span', $estatus, ['class' => "badge $class"]);
                },
                'contentOptions' => ['style' => 'text-align: center;'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'ACCIONES',
                'template' => '<div class="d-flex justify-content-center gap-1">{view}{update}</div>',
                'options' => ['style' => 'width:80px; min-width:80px;'],
                'headerOptions' => ['style' => 'color: white!important;'],
                'contentOptions' => ['style' => 'text-align: center;'],
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a(
                            '<i class="fa fa-eye"></i>',
                            Url::to(['pagos/view', 'id' => $model->id]),
                            [
                                'title' => 'Ver detalle',
                                'class' => 'btn btn-sm btn-outline-info',
                                'style' => 'padding: 0.25rem 0.5rem;'
                            ]
                        );
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a(
                            '<i class="fas fa-edit"></i>',
                            Url::to(['pagos/update', 'id' => $model->id]),
                            [
                                'title' => 'Editar',
                                'class' => 'btn btn-sm btn-outline-warning',
                                'style' => 'padding: 0.25rem 0.5rem;'
                            ]
                        );
                    },
                ],
            ]
        ],
    ]);
} else {
    echo '<div class="alert alert-info">No hay pagos registrados para este contrato.</div>';
    echo "<p class='text-muted'><small>{$periodoInfo}</small></p>";
    echo '<p><small>Solo se muestran pagos realizados durante el periodo activo del contrato.</small></p>';
}
