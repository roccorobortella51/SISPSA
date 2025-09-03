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
    <?php $afiliado_datos =  $afiliado->nombres . ' ' . $afiliado->apellidos ." ". $afiliado->tipo_cedula  . ' ' . $afiliado->cedula; ?>


<div class="view-main-container">
    <div class="ms-panel-header">
        <h1 class="main-page-title display-4" style="text-align: center;"><?= Html::encode($this->title)." ".$afiliado_datos ?> </h1>
        <div class="button-group-spacing">
            <?php
                if($searchModel->estatus == 'Anulado'){
                    echo Html::a('<i class="fas fa-plus mr-2"></i> Crear Contratos', ['create'], ['class' => 'btn btn-primary']);
                }
            ?>
            <?= Html::a('<i class="fas fa-undo-alt mr-2"></i> Volver', Url::to(['user-datos/update','id' => $afiliado->id]), ['class' => 'btn btn-secondary']); ?>
        </div>
    </div>




    <div class="ms-panel">
        <div class="ms-panel-body">

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    [
                        'class' => ExpandRowColumn::class,
                        'value' => function ($model, $key, $index, $column) {
                            return GridView::ROW_EXPANDED;
                        },
                        'detailUrl' => \yii\helpers\Url::to(['detalle-pagos-ajax']),
                        'expandOneOnly' => true,
                        'headerOptions' => [
                            'style' => 'width:50px; text-align: center; vertical-align: middle; color: white !important;',
                            'class' => 'kv-expand-header-cell',
                        ],
                    ],
                    [
                        'label' => 'Nro de contrato',
                        'attribute' => 'id',
                        'headerOptions' => ['style' => 'width:50px'],
                    ],
                    [
                        'label' => 'clinica',
                        'value' => function($model){
                            if($model->clinica){
                                return $model->clinica->nombre;
                            }
                            return '';
                        }
                    ],
                    [
                        'label' => 'plan',
                        'value' => function($model){
                            if($model->plan){
                                return $model->plan->nombre;
                            }
                            return '';
                        }
                    ],
                    /*[
                        'label' => 'cobertura (USD)',
                        'value' => function($model){
                            if($model->plan){
                                return  Yii::$app->formatter->asDecimal($model->plan->cobertura, 2);
                            }
                            return '';
                        }
                    ],*/
                    [
                         'attribute' => 'cobertura del plan USD',
                        'format' => ['currency', 'USD'],
                        'contentOptions' => ['style' => 'text-align: right;'],
                        'value' => function($model){
                            if($model->plan){
                                return  Yii::$app->formatter->asDecimal($model->plan->cobertura, 2);
                            }
                            return '';
                        },
                        'filter' => false
                    ],
                    
                    [
                        'attribute' => 'precio USD',
                        'format' => ['currency', 'USD'],
                        'contentOptions' => ['style' => 'text-align: right;'],
                        'value' => function($model){
                            if($model->plan){
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
                        'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
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
