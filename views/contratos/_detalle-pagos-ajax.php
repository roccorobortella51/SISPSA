<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use yii\helpers\Url;

if (!empty($model->pagos)) {
    echo "<h4>Pagos Realizados</h4>";

    echo GridView::widget([
        'dataProvider' => new \yii\data\ArrayDataProvider([
            'allModels' => $model->pagos,
        ]),
        'summary' => false,
        'striped' => true,
        'hover' => true,
        'columns' => [
            'fecha_pago',
            [
                'attribute' => 'monto_pagado',
                'value' => function ($model) {
                    return $model->monto_pagado . ' USD';
                },
                'label' => 'Monto Pagado en USD',
            ],
            [
                'attribute' => 'monto_usd',
                'value' => function ($model) {
                    return $model->monto_usd . ' Bs';
                },
                'label' => 'Monto Pagado en Bs',
            ],
            'metodo_pago',
            'numero_referencia_pago',
            [
                'attribute' => 'imagen_prueba',
                'format' => 'raw',
                'label' => 'Comprobante',
                'value' => function($model) {
                    if ($model->imagen_prueba) {
                        // Use the same view-image action as in the main view
                        $imageUrl = Url::to(['/pagos/view-image', 'id' => $model->id]);
                        $thumbnailUrl = Url::to(['/pagos/view-image', 'id' => $model->id]);
                        
                        return Html::a(
                            Html::img($thumbnailUrl, [
                                'width' => '50', 
                                'height' => '50',
                                'style' => 'object-fit: cover; border-radius: 4px;',
                                'alt' => 'Comprobante de pago'
                            ]),
                            $imageUrl,
                            [
                                'target' => '_blank',
                                'title' => 'Ver comprobante completo'
                            ]
                        );
                    }
                    return '<span class="text-muted">Sin comprobante</span>';
                },
            ],
            [
                'attribute' => 'estatus',
                'value' => function ($pago) {
                    return $pago->estatus;
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'ACCIONES',
                'options' => ['style' => 'width:55px; min-width:55px;'],
                'headerOptions' => ['style' => 'color: white!important;'],
                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                'template' => '<div class="d-flex justify-content-center gap-0">{view}{update}</div>',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a(
                            '<i class="fa fa-eye"></i>',
                            Url::to(['pagos/view', 'id' => $model->id]),
                            [
                                'title' => 'Detalle de Pago',
                                'class' => 'btn btn-link btn-sm text-success',
                                'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;'
                            ]
                        );
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a(
                            '<i class="fas fa-pencil-alt ms-text-primary"></i>',
                            Url::to(['pagos/update', 'id' => $model->id]),
                            [
                                'title' => 'Editar Pago',
                                'class' => 'btn btn-link btn-sm text-success',
                                'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;'
                            ]
                        );
                    },
                ],
            ]
        ],
    ]);
} else {
    echo '<div class="alert alert-info">No hay pagos registrados.</div>';
}
?>