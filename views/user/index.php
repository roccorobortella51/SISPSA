<?php

use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\UserSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */


// --- BREADCRUMBS ---

$this->params['breadcrumbs'][] = ['label' => 'Usuarios', 'url' => ['index']];
// --- FIN  --- 


$this->title = 'Gestión de Usuarios'; // Este sigue siendo el título para la página y breadcrumbs
?>

<div class=row style="margin:3px !important;">
    <input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header row">
                <span class="col-md-10"><h1><?= $this->title = 'Gestión de Usuarios'; ?></h1></span>
                <span class="col-md-2" style="padding-left: 9rem;"><?= Html::a('<i class="fas fa-plus"></i> CREAR NUEVO USUARIO', ['create'], ['class' => 'btn btn-outline-primary btn-lg']) ?> </span>
            </div>
            <div class="ms-panel-body">
                <div class="table-responsive">
                            <?= GridView::widget([
                               'id' => 'usuarios-grid',
                                'dataProvider' => $dataProvider,
                                'filterModel' => $searchModel,
                                'layout' => "{items}{pager}",

                                'tableOptions' => [
                                    'class' => 'table table-striped table-bordered table-hover table-sm'
                                ],
                                'options' => [
                                    'class' => 'grid-view-container table-responsive',
                                ],
                                'columns' => [
                                    [
                                        'attribute' => 'id',
                                        'options' => ['style' => 'width: 100px;'],
                                        'headerOptions' => ['style' => 'color: white!important;'],
                                        // MODIFICACIÓN: Añadir placeholder y centrado para el input de búsqueda
                                        'filterInputOptions' => [
                                            'placeholder' => 'Búsqueda',
                                            'class' => 'form-control text-center', // Añadimos text-center de Bootstrap
                                        ],
                                    ],
                                    [
                                        'attribute' => 'username',
                                        'format' => 'ntext',
                                        'headerOptions' => ['style' => 'color: white!important;'],
                                        'options' => ['style' => 'width: 500px;'],
                                        // MODIFICACIÓN: Añadir placeholder y centrado para el input de búsqueda
                                        'filterInputOptions' => [
                                            'placeholder' => 'Búsqueda',
                                            'class' => 'form-control text-center', // Añadimos text-center de Bootstrap
                                        ],
                                    ],
                                    [
                                        'attribute' => 'email',
                                        'format' => 'ntext',
                                        'headerOptions' => ['style' => 'color: white!important;'],
                                        'options' => ['style' => 'width: 500px;'],
                                        // MODIFICACIÓN: Añadir placeholder y centrado para el input de búsqueda
                                        'filterInputOptions' => [
                                            'placeholder' => 'Búsqueda',
                                            'class' => 'form-control text-center', // Añadimos text-center de Bootstrap
                                        ],
                                    ],
                                    //'status',
                                    //'created_at',
                                    //'updated_at',
                                    //'id',
                                        // Columna de Acciones - Se mantiene sin cambios para no afectar lo ya logrado
                                    [
                                        'class' => 'yii\grid\ActionColumn',
                                        'header' => 'ACCIONES',
                                        'template' => '<div class="d-flex justify-content-center gap-0">{view}{update}{delete}</div>',
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
                                            'update' => function ($url, $model, $key) {
                                                return Html::a(
                                                    '<i class="fas fa-pencil-alt ms-text-primary"></i>',
                                                    Url::to(['update', 'id' => $model->id]),
                                                    [
                                                        'title' => 'Editar Usuario',
                                                        'class' => 'btn btn-link btn-sm text-success',
                                                        'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;'
                                                    ]
                                                );
                                            },
                                            'delete' => function ($url, $model, $key) {
                                                return Html::a(
                                                    '<i class="far fa-trash-alt ms-text-danger"></i>',
                                                    Url::to(['delete', 'id' => $model->id]),
                                                    [
                                                        'title' => 'Eliminar Usuario',
                                                        'data-confirm' => '¿Estás seguro de que quieres eliminar esta clínica?',
                                                        'data-method' => 'post',
                                                        'class' => 'btn btn-link btn-sm text-danger',
                                                        'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;'
                                                    ]
                                                );
                                            },
                                            
                                        ],
                                    ],
                                ],
                                // Fin de columns
                            ]); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</div>