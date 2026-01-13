<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use yii\widgets\DetailView;
use kartik\grid\ExpandRowColumn;
use kartik\grid\GridViewAsset;
use kartik\grid\ExpandRowColumnAsset;


$this->title = 'Contratos';

?>
<?php $afiliado_datos = (isset($afiliado) && is_object($afiliado)) ? $afiliado->nombres . ' ' . $afiliado->apellidos . " " . $afiliado->tipo_cedula . ' ' . $afiliado->cedula : ''; ?>


<div class="view-main-container">
    <div class="ms-panel-header">
        <h1 class="main-page-title display-4" style="text-align: center;"><?= Html::encode($this->title) . ": " . $afiliado_datos ?> </h1>
        <div class="button-group-spacing">
            <?php
            if ($searchModel->estatus == 'Anulado') {
                echo Html::a('<i class="fas fa-plus mr-2"></i> Crear Contratos', ['create'], ['class' => 'btn btn-primary']);
            }
            ?>
            <?= Html::a('<i class="fas fa-undo-alt mr-2"></i> Volver', Url::to(['user-datos/update', 'id' => (is_object($afiliado) ? $afiliado->id : '')]), ['class' => 'btn btn-secondary']); ?>
        </div>
    </div>




    <div class="ms-panel">
        <div class="ms-panel-body">

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'filterRowOptions' => ['style' => 'display: none'], // ADD THIS LINE
                'columns' => [
                    [
                        'class' => ExpandRowColumn::class,
                        'value' => function ($model, $key, $index, $column) {
                            return GridView::ROW_EXPANDED;
                        },
                        'detailUrl' => \yii\helpers\Url::to(['detalle-pagos-ajax']),
                        'expandOneOnly' => true,
                        'headerOptions' => [
                            'style' => 'width:20px; text-align: center; vertical-align: middle; color: white !important;',
                            'class' => 'kv-expand-header-cell',
                        ],
                    ],
                    [
                        'label' => '#. Contrato',
                        'attribute' => 'id',
                        'headerOptions' => ['style' => 'width:20px; text-align: center; vertical-align: middle;'],
                        // Centers the actual ID values
                        'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                        'filter' => false, // REMOVED FILTER
                    ],
                    [
                        'attribute' => 'estatus',
                        'label' => 'Estatus',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->getStatusBadge();
                        },
                        'contentOptions' => function ($model) {
                            $status = $model->estatus ?: 'Registrado';
                            $statusLower = strtolower($status);

                            $classes = [
                                'registrado' => 'text-center font-weight-bold text-primary',
                                'activo' => 'text-center font-weight-bold text-success',
                                'anulado' => 'text-center font-weight-bold text-danger',
                                'vencido' => 'text-center font-weight-bold text-warning',
                                'pendiente' => 'text-center font-weight-bold text-info',
                                'suspendido' => 'text-center font-weight-bold text-secondary',
                            ];

                            return ['class' => $classes[$statusLower] ?? 'text-center'];
                        },
                        'filter' => false, // REMOVED FILTER
                    ],
                    [
                        'label' => 'clínica',
                        'value' => function ($model) {
                            if ($model->clinica) {
                                return $model->clinica->nombre;
                            }
                            return '';
                        },
                        'headerOptions' => ['style' => 'width:20px; text-align: center; vertical-align: middle;'],
                        // Centers the actual ID values
                        'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                        'filter' => false, // REMOVED FILTER (if you don't want filtering on clinic either)
                    ],
                    [
                        'label' => 'plan',
                        'value' => function ($model) {
                            if ($model->plan) {
                                return $model->plan->nombre;
                            }
                            return '';
                        },
                        'headerOptions' => ['style' => 'width:160px; text-align: center; vertical-align: middle;'],
                        // Centers the actual ID values
                        'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                        'filter' => false, // REMOVED FILTER (if you don't want filtering on plan either)
                    ],
                    [
                        'attribute' => 'cobertura del plan USD',
                        'format' => 'html',
                        'contentOptions' => ['style' => 'text-align: center; vertical-align: middle'],
                        'value' => function ($model) {
                            if ($model->plan) {
                                return  Yii::$app->formatter->asDecimal($model->plan->cobertura, 2);
                            }
                            return '';
                        },
                        'filter' => false
                    ],

                    [
                        'attribute' => 'precio USD',
                        'format' => 'html',
                        'contentOptions' => ['style' => 'text-align: center; vertical-align: middle'],
                        'value' => function ($model) {
                            if ($model->plan) {
                                return  Yii::$app->formatter->asDecimal($model->plan->precio, 2);
                            }
                            return '';
                        },
                        'filter' => false
                    ],

                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => 'ACCIONES',
                        'template' => '<div class="d-flex justify-content-center gap-0">{view}{payment}</div>',
                        'options' => ['style' => 'width:70px; min-width:70px;', 'class' => 'action-buttons'],
                        'headerOptions' => ['style' => 'color: white!important;'],
                        'contentOptions' => function ($model) {
                            // For Anulado contracts, hide the entire action column
                            if ($model->estatus === 'Anulado') {
                                return [
                                    'style' => 'display: none;',
                                    'class' => 'hidden-action-column'
                                ];
                            }
                            return ['style' => 'text-align: center; padding: 10 !important;'];
                        },
                        'visibleButtons' => [
                            'view' => function ($model, $key, $index) {
                                // Hide view button for Anulado contracts
                                return $model->estatus !== 'Anulado';
                            },
                            'payment' => function ($model, $key, $index) {
                                // Hide payment button for Anulado contracts
                                return $model->estatus !== 'Anulado';
                            },
                        ],
                        'buttons' => [
                            'view' => function ($url, $model, $key) {
                                return Html::a(
                                    '<i class="fa fa-eye"></i>',
                                    Url::to(['view', 'id' => $model->id]),
                                    [
                                        'title' => 'Detalle de Contrato',
                                        'class' => 'btn-action view'
                                    ]
                                );
                            },
                            'payment' => function ($url, $model, $key) {
                                return Html::a(
                                    '<i class="fa fa-file-invoice-dollar text-blue-600"></i>',
                                    Url::to(['pagos/create', 'user_id' => $model->user_id]),
                                    [
                                        'title' => 'Realizar pago',
                                        'class' => 'btn-action view'
                                    ]
                                );
                            },
                        ],
                    ],
                ],
                'summaryOptions' => ['class' => 'summary mb-2 text-muted'],
                'pager' => [
                    'class' => 'yii\bootstrap4\LinkPager',
                ]
            ]); ?>

        </div>
    </div>
</div>
<style>
    /* Add to your main CSS file */
    .badge {
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
    }

    .badge-primary {
        background-color: #007bff;
        color: #fff;
    }

    .badge-success {
        background-color: #28a745;
        color: #fff;
    }

    .badge-danger {
        background-color: #dc3545;
        color: #fff;
    }

    .badge-warning {
        background-color: #ffc107;
        color: #212529;
    }

    .badge-info {
        background-color: #17a2b8;
        color: #fff;
    }

    .badge-secondary {
        background-color: #6c757d;
        color: #fff;
    }

    .badge-light {
        background-color: #f8f9fa;
        color: #212529;
    }
</style>