
<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use yii\helpers\Url;


/*echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'nombres',
        'apellidos',
        'cedulaFormatted',
        'email',
        'telefono',
        [
            'label' => 'Asesor',
            //'value' => $model->pagos ? $model->pagos->monto_pagado : 'Sin asignar',
        ],
        [
            'label' => 'Plan',
            //'value' => $model->pagos ? $model->pagos->fecha_pago : 'Sin asignar',
        ],
    ],
]);*/
        //var_dump($model->pagos);exit();

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
            'monto_pagado:currency',
            'metodo_pago',
            'numero_referencia_pago',
            [
                'attribute' => 'imagen_prueba',
                'format' => 'raw',
                'value' => function($model) {
                    $url = $model->imagenPruebaUrl;
                    if ($url) {
                        return Html::a(Html::img($url, ['width' => '350']), $url, ['target' => '_blank']);
                    }
                    return null;
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
                                'title' => 'Detalle de Usuario',
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
                                'title' => 'Editar Usuario',
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