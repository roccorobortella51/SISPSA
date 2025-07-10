<?php

use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use app\components\UserHelper;

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
   
    <span class="col-md-10">
        <h1><?= $this->title = 'Gestión de Usuarios'; ?></h1>
    </span>
    
   
    <div class="col-md-2 text-end"> 
       <?= Html::a(
        '<i class="fas fa-plus"></i> CREAR NUEVO USUARIO',
        ['create'],
        ['class' => 'btn btn-outline-primary btn-lg w-100']
    ) ?>
    </div>
</div>
            <div class="ms-panel-body">
                <div class="table-responsive">
                            <?= GridView::widget([
                               'id' => 'usuarios-grid',
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
                                        'attribute' => 'id',
                                        'options' => ['style' => 'width: 100px;'],
                                        'headerOptions' => ['style' => 'color: white!important;'],
                                        // MODIFICACIÓN: Añadir placeholder y centrado para el input de búsqueda
                                        'filterInputOptions' => [
                                            'placeholder' => 'Búsqueda',
                                            'class' => 'form-control text-center', // Añadimos text-center de Bootstrap
                                        ],
                                    ],
                                    // [
                                    //     'attribute' => 'username',
                                    //     'label' => 'Nombre de Usuario',
                                    //     'format' => 'ntext',
                                    //     'headerOptions' => ['style' => 'color: white!important;'],
                                    //     'options' => ['style' => 'width: 500px;'],
                                    //     // MODIFICACIÓN: Añadir placeholder y centrado para el input de búsqueda
                                    //     'filterInputOptions' => [
                                    //         'placeholder' => 'Búsqueda',
                                    //         'class' => 'form-control text-center', // Añadimos text-center de Bootstrap
                                    //     ],
                                    // ],
                                    [
                                        'attribute' => 'nombrecompleto', // <-- USA EL NUEVO ATRIBUTO VIRTUAL AQUÍ
                                        'label' => 'Nombre Completo', // Etiqueta para el encabezado de la columna
                                        'format' => 'ntext',
                                        'headerOptions' => ['style' => 'color: white!important;'],
                                        'options' => ['style' => 'width: 500px;'], // Mantén el ancho si lo necesitas
                                        'filterInputOptions' => [
                                            'placeholder' => 'Buscar por Nombre/Apellido', // Placeholder del filtro
                                            'class' => 'form-control text-center', // Centra el texto del filtro
                                        ],
                                        'value' => function ($model) { // <-- DEFINE CÓMO SE MUESTRA EL VALOR EN CADA FILA
                                            // Accede al nombre y apellido a través de la relación userDatos
                                            return $model->userDatos ? Html::encode($model->userDatos->nombres . ' ' . $model->userDatos->apellidos) : 'N/A';
                                        },
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

                                    // --- NUEVA COLUMNA PARA EL ROL ---
                                    [
                                        'label' => 'Rol del Usuario', // El título de la columna
                                        'headerOptions' => ['style' => 'color: white!important;'],
                                        'format' => 'raw', 
                                        'value' => function ($model) {
                                            return UserHelper::getRolNameByUserId($model->id);
                                        },
                                        // Opcional: Centrar el texto en la celda
                                        'contentOptions' => ['class' => 'text-center'],
                                        // Opcional: Si quieres un filtro de texto para el rol, tendrías que modificar UserSearch
                                        // 'filterInputOptions' => [
                                        //     'placeholder' => 'Filtrar por rol',
                                        //     'class' => 'form-control text-center',
                                        // ],
                                    ],
                                    // --- FIN NUEVA COLUMNA ---
                                        // Columna de Acciones - Se mantiene sin cambios



                                    //'status',
                                    //'created_at',
                                    //'updated_at',
                                    //'id',
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