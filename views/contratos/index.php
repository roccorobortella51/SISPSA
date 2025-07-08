<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use yii\widgets\DetailView;
use kartik\grid\ExpandRowColumn;
use kartik\grid\GridViewAsset;
use kartik\grid\ExpandRowColumnAsset;



/* @var $this yii\web\View */
/* @var $searchModel app\models\ContratosSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Contratos';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-12">
                            <?= Html::a('Create Contratos', ['create'], ['class' => 'btn btn-success']) ?>
                        </div>
                    </div>


                    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [
                            [
                                'class' => ExpandRowColumn::class,
                                'value' => function ($model, $key, $index, $column) {
                                    return GridView::ROW_EXPANDED;
                                },
                                // Carga AJAX:
                                'detailUrl' => \yii\helpers\Url::to(['detalle-pagos-ajax']),
                                'expandOneOnly' => true,
                                'headerOptions' => ['style' => 'width:50px'],
                            ],
                            [
                                'label' => 'Nro de contrato',
                                'attribute' => 'id',
                                'headerOptions' => ['style' => 'width:50px'],
                            ],
                            [
                                'label' => 'clinica',
                                'value' => function($model){
                                    return $model->clinica->nombre;
                                }
                            ],
                            [
                                'label' => 'plan',
                                'value' => function($model){
                                    return $model->plan->nombre;
                                }
                            ],
                            [
                                'label' => 'cobertura (BS)',
                                'value' => function($model){
                                    return $model->plan->cobertura;
                                }
                            ],
                            //'fecha_ini',
                            //'fecha_ven',
                            //'monto',
                            //'estatus:ntext',
                            //'nrocontrato:ntext',
                            //'frecuencia_pago:ntext',
                            //'sucursal:ntext',
                            //'moneda:ntext',
                            //'updated_at',
                            //'deleted_at',
                            //'anulado_por',
                            //'anulado_fecha',
                            //'anulado_motivo:ntext',
                            //'user_id',
                            //'PDF:ntext',

                            //['class' => 'hail812\adminlte3\yii\grid\ActionColumn'],
                            [
                                        'class' => 'yii\grid\ActionColumn',
                                        'header' => 'ACCIONES',
                                        'template' => '<div class="d-flex justify-content-center gap-0">{view}{payment}</div>',
                                        'options' => ['style' => 'width:55px; min-width:55px;'],
                                        'headerOptions' => ['style' => 'color: white!important;'],
                                        'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                                        'buttons' => [
                                            'view' => function ($url, $model, $key) {
                                                return Html::a(
                                                    '<i class="fa fa-eye"></i>',
                                                    Url::to(['view', 'id' => $model->id]),
                                                    [
                                                        'title' => 'Detalle de Usuario',
                                                        'class' => 'btn btn-link btn-sm text-success',
                                                        'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;'
                                                    ]
                                                );
                                            },

                                            'payment' => function ($url, $model, $key) {
                                                return Html::a(
                                                    '<i class="fa fa-file-invoice-dollar ms-text-primary"></i>',
                                                    Url::to(['../pagos/create', 'user_id' => $model->user_id]),
                                                    [
                                                        'title' => 'Realizar pago',
                                                        'class' => 'btn btn-link btn-sm text-danger',
                                                        'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;'
                                                    ]
                                                );
                                            },
                                            
                                        ],
                            ],
                        ],
                        'summaryOptions' => ['class' => 'summary mb-2'],
                        'pager' => [
                            'class' => 'yii\bootstrap4\LinkPager',
                        ]
                    ]); ?>


                </div>
                <!--.card-body-->
            </div>
            <!--.card-->
        </div>
        <!--.col-md-12-->
    </div>
    <!--.row-->
</div>
