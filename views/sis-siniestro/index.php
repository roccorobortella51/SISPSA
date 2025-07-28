<?php

// Importaciones necesarias
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use kartik\widgets\SwitchInput;
/**
 * @var yii\web\View $this
 * @var app\models\RmClinicaSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 */

// --- BREADCRUMBS ---

$this->params['breadcrumbs'][] = ['label' => 'AFILIADOS', 'url' => ['/user-datos/index-clinicas', 'clinica_id' => $afiliado->clinica_id]];
// --- FIN  --- 


$this->title = 'Atención '.$afiliado->nombres." ".$afiliado->apellidos." ".$afiliado->tipo_cedula."-".$afiliado->cedula; // Este sigue siendo el título para la página y breadcrumbs

?>

<div class=row style="margin:3px !important;">
<input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />
    <div class="col-md-12 text-end">
       
    </div>
    <div class="col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header d-flex justify-content-between align-items-center">
                <h1><?= $this->title ?></h1>
                <div>
                    <?= Html::a('<i class="fas fa-plus"></i> CREAR NUEVA ATENCIÓN', ['create', 'user_id' => $user_id], ['class' => 'btn btn-outline-primary btn-lg']) ?>
                </div>
            </div>
            <div class="ms-panel-body">
                        <div class="table-responsive">


                <?= GridView::widget([
                    'id' => 'clinica-grid',
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'layout' => "{items}{pager}",
                    'resizableColumns' => false,
                    'bordered' => false,
                    'responsiveWrap' => false,
                    'persistResize' => false,
                            'tableOptions' => [
                                'class' => 'table table-striped table-bordered table-hover table-sm'
                            ],
                            'options' => [
                                'class' => 'grid-view-container table-responsive',
                            ],

                            'columns' => [
                                 [
                        'attribute' => 'idclinica',
                        'value' => 'idclinica0.nombre',
                        'label' => 'Clínica',
                    ],
                    'fecha',
                    'hora',
                    [
                        'attribute' => 'idbaremo',
                        'value' => 'idbaremo0.nombre',
                        'label' => 'Baremo',
                    ],
                    [
                        'attribute' => 'atendido',
                        'value' => function($model) {
                            return $model->atendido ? 'Sí' : 'No';
                        },
                        'filter' => [0 => 'No', 1 => 'Sí'],
                    ],
                    'fecha_atencion',
                    'hora_atencion',


                                // Columna de Acciones - Se mantiene sin cambios para no afectar lo ya logrado
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => 'ACCIONES',
                        'template' => '<div class="d-flex justify-content-center gap-0">{view}{update}</div>',
                        'options' => ['style' => 'width:55px; min-width:55px;'],
                        'headerOptions' => ['style' => 'color: white!important;'],
                        'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                        'buttons' => [
                                        'view' => function ($url, $model, $key) {
                                            return Html::a(
                                                '<i class="fa fa-eye"></i>',
                                                Url::to(['view', 'id' => $model->id]),
                                                [
                                                    'title' => 'Detalle de la atención',
                                                    'class' => 'btn btn-link btn-sm text-success',
                                                    'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;'
                                                ]
                                            );
                                        },
                                        'update' => function ($url, $model, $key) {
                                            return Html::a(
                                                '<i class="fas fa-pencil-alt ms-text-primary"></i>',
                                                Url::to(['update', 'id' => $model->id]),
                                                [
                                                    'title' => 'Editar',
                                                    'class' => 'btn btn-link btn-sm text-success',
                                                    'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;'
                                                ]
                                            );
                                        },
                                        /*'delete' => function ($url, $model, $key) {
                                            return Html::a(
                                                '<i class="far fa-trash-alt ms-text-danger"></i>',
                                                Url::to(['delete', 'id' => $model->id]),
                                                [
                                                    'title' => 'Eliminar',
                                                    'data-confirm' => '¿Estás seguro de que quieres eliminar esta clínica?',
                                                    'data-method' => 'post',
                                                    'class' => 'btn btn-link btn-sm text-danger',
                                                    'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;'
                                                ]
                                            );
                                        },*/
                                        
                                    ],
                                ],

                            ], // Fin de columns
                        ]); ?>
                        </div>
                     </div>
                  </div>
               </div>
               
            <div class="clearfix"></div>
        </div>
