<?php

use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\select2\Select2;
use app\models\AuthItem; 
use app\components\UserHelper;

/** @var yii\web\View $this */
/** @var app\models\UserSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */


// --- BREADCRUMBS ---

$this->params['breadcrumbs'][] = ['label' => 'Usuarios', 'url' => ['index']];
// --- FIN  --- 


$this->title = 'Gestión de Usuarios'; // Este sigue siendo el título para la página y breadcrumbs

$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol == 'GERENTE-COMERCIALIZACION'); 
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
                                        'value' => function ($model) {
                                            return $model->agenteFuerza->agente->id ?? 'No asignado';
                                        },
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

                                    [
                                        'attribute' => 'userDatos.telefono',
                                        'format' => 'ntext',
                                        'label' => 'Teléfono',
                                        'headerOptions' => ['style' => 'color: white!important;'],
                                        'options' => ['style' => 'width: 50px;'],
                                        // MODIFICACIÓN: Añadir placeholder y centrado para el input de búsqueda
                                        'filterInputOptions' => [
                                            'placeholder' => 'Búsqueda',
                                            'class' => 'form-control text-center', // Añadimos text-center de Bootstrap
                                        ],
                                    ],

                                    [  
                                        'label' => 'Agencia',
                                        'attribute' => 'id',
                                        'headerOptions' => ['style' => 'color: white!important;'],
                                        'format' => 'raw',
                                        'value' => function ($model) {
                                            return $model->agenteFuerza->agente->nom ?? 'No asignado';
                                        },
                                        'contentOptions' => ['class' => 'text-center'],
                                    ],


                                    // --- COLUMNA PARA EL ROL ---
                                    [  
                                        'label' => 'Rol del Usuario',
                                        'headerOptions' => ['style' => 'color: white!important;'],
                                        'format' => 'raw',
                                        'value' => function ($model) {
                                            return $model->userDatos->role ?? 'No asignado';
                                        },
                                        'contentOptions' => ['class' => 'text-center'],
                                        'filter' => Select2::widget([
                                            'model' => $searchModel,
                                            'attribute' => 'roleName',
                                           
                                            'data' => UserHelper::getRolesAllRoles(), 
                                            'options' => ['placeholder' => 'Filtrar por rol...'],
                                            'pluginOptions' => [
                                                'allowClear' => true,
                                            ],
                                        ]),
                                    ],
                                    // --- FIN COLUMNA ----
                                    
                        
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
                                                        'class' => 'btn-action view'
                                                    ]
                                                );
                                            },
                                            'update' => function ($url, $model, $key) {
                                                return Html::a(
                                                    '<i class="fas fa-pencil-alt ms-text-primary"></i>',
                                                    Url::to(['update', 'id' => $model->id]),
                                                    [
                                                        'title' => 'Editar Usuario',
                                                        'class' => 'btn-action view'
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