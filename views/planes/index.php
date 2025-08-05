<?php

// Importaciones necesarias
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use yii\widgets\ActiveForm;
use app\components\UserHelper;
use kartik\widgets\SwitchInput;
/**
 * @var yii\web\View $this
 * @var app\models\RmClinicaSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 */

// --- BREADCRUMBS ---

$this->params['breadcrumbs'][] = ['label' => 'CLINICAS', 'url' => ['/rm-clinica/index']];
// --- FIN  --- 


$this->title = 'Gestión de Planes'; // Este sigue siendo el título para la página y breadcrumbs

$rol = UserHelper::getMyRol();

$permisos = false;

if ($rol == 'superadmin') 
{
    $permisos = true;
}


?>

<div class=row style="margin:3px !important;">
    

    <div class="col-md-12">
        <div class="ms-panel ms-panel-fh">
            
        <div class="ms-panel-header d-flex justify-content-between align-items-center">
            <h1><?= $this->title = 'Agregar de Plan a '.$clinica->nombre; ?> </h1>
                        
                        <div>
                            <?= Html::a(
                                '<i class="fas fa-undo"></i> Volver', 
                                '#',
                                [
                                    'class' => 'btn btn-primary btn-lg', 
                                    'onclick' => 'window.history.back(); return false;', 
                                    'title' => 'Volver a la página anterior', 
                                ]
                            ) ?> 
                        </div>
        </div>
        <?php if($permisos == true){?>
                    <div class="ms-panel-header d-flex justify-content-between align-items-center">
                    <?php if($permisos == true){?>
                        <div> 
                            
                            <?= Html::a(
                                '<i class="fas fa-plus"></i> CREAR NUEVO PLÁN', 
                                ['create', 'clinica_id' => $clinica->id], 
                                // Este es el último botón, no necesita margen a la derecha
                                ['class' => 'btn btn-outline-primary btn-lg'] 
                            ) ?> 
                        </div>
                    <?php }?>
                    </div>
        
        <?php }?>
        </div>
     </div>
    </div>
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header">
                <h1><?= $this->title = 'Gestión de Planes '; ?> de <?= $clinica->nombre ?></h1>
            </div>
            <div class="ms-panel-body">
                        <div class="table-responsive">
                            <?= GridView::widget([
                            'id' => 'clinica-grid',
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
                                // ID
                                [
                                    'attribute' => 'id',
                                    'options' => ['style' => 'width: 50px;'],
                                    'headerOptions' => ['style' => 'color: white!important;'],
                                    // MODIFICACIÓN: Añadir placeholder y centrado para el input de búsqueda
                                    'filterInputOptions' => [
                                        'placeholder' => 'Búsqueda',
                                        'class' => 'form-control text-center', // Añadimos text-center de Bootstrap
                                    ],
                                ],

                                // Nombre
                                [
                                    'attribute' => 'nombre',
                                    'format' => 'ntext',
                                    'headerOptions' => ['style' => 'color: white!important;'],
                                    'options' => ['style' => 'width: 250px;'],
                                    // MODIFICACIÓN: Añadir placeholder y centrado para el input de búsqueda
                                    'filterInputOptions' => [
                                        'placeholder' => 'Búsqueda',
                                        'class' => 'form-control text-center', // Añadimos text-center de Bootstrap
                                    ],
                                ],
                                [
                                    'attribute' => 'descripcion',
                                    'format' => 'ntext',
                                    'headerOptions' => ['style' => 'color: white!important;'],
                                    'options' => ['style' => 'width: 250px;'],
                                    // MODIFICACIÓN: Añadir placeholder y centrado para el input de búsqueda
                                    'filterInputOptions' => [
                                        'placeholder' => 'Búsqueda',
                                        'class' => 'form-control text-center', // Añadimos text-center de Bootstrap
                                    ],
                                ],
                                [
                                    'attribute' => 'cobertura',
                                    'format' => ['currency', ''],
                                    'contentOptions' => ['style' => 'text-align: right;'],
                                    'filter' => false
                                ],
                                [
                                    'attribute' => 'precio',
                                    'format' => ['currency', ''],
                                    'contentOptions' => ['style' => 'text-align: right;'],
                                    'filter' => false

                                ],
                                [
                                    'attribute' => 'comision',
                                    'format' => ['currency', ''],
                                    'contentOptions' => ['style' => 'text-align: right;'],
                                    'filter' => false

                                ],
                                [   
                                    'attribute' => 'edad_minima', 
                                    'contentOptions' => ['class' => 'text-center'],
                                    'label' => Yii::t('app', 'Edades'),
                                    'value' => function ($model, $key, $index, $widget) {
                                        return $model->edad_minima."-".$model->edad_limite." años";
                                    },
                                    'headerOptions' => ['class' => 'text-left header-link'],
                                ],
                               

                                // Estado
                                [
                                    'label' => 'Estado',
                                    'attribute' => 'estatus',
                                    'format' => 'raw',
                                    'headerOptions' => ['class' => 'text-center header-link'],
                                    'contentOptions' => ['class' => 'text-center'],
                                    'value' => function ($model) {
                                        // Asegurarse que el valor es booleano o compatible (1/0, 'true'/'false')
                                        $isActive = ($model->estatus === 'Activo' || $model->estatus === 1 || $model->estatus === true);
                                        
                                        return SwitchInput::widget([
                                            'name' => 'status_'.$model->id, // Mejor usar un nombre único por registro
                                            'value' => $isActive, // Valor booleano que determina el estado inicial
                                            'pluginEvents' => [
                                                'switchChange.bootstrapSwitch' => "function(e){updatestatus('$model->id')}"
                                            ],
                                            'pluginOptions' => [
                                                'onText' => 'Activo',
                                                'offText' => 'Inactivo',
                                                'onColor' => 'success',
                                                'offColor' => 'danger',
                                                'state' => $isActive // Estado inicial del switch
                                            ],
                                            'options' => [
                                                'id' => 'status-switch-'.$model->id // ID único para cada switch
                                            ],
                                            'labelOptions' => ['style' => 'font-size: 12px;'],
                                        ]);
                                    },
                                ],
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
                                                    'title' => 'Detalles de Plán',
                                                    'class' => 'btn-action view'
                                                ]
                                            );
                                        },
                                        'update' => function ($url, $model, $key)use($permisos) {
                                            if($permisos == true){
                                                return Html::a(
                                                    '<i class="fas fa-pencil-alt ms-text-primary"></i>',
                                                    Url::to(['update', 'id' => $model->id]),
                                                    [
                                                        'title' => 'Editar',
                                                        'class' => 'btn-action view'
                                                    ]
                                                );
                                            }
                                        },
                                        /*'delete' => function ($url, $model, $key) {
                                            return Html::a(
                                                '<i class="far fa-trash-alt ms-text-danger"></i>',
                                                Url::to(['delete', 'id' => $model->id]),
                                                [
                                                    'title' => 'Eliminar',
                                                    'data-confirm' => '¿Estás seguro de que quieres eliminar esta clínica?',
                                                    'data-method' => 'post',
                                                    'class' => 'btn-action view'
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
            </div>
            <div class="clearfix"></div>
        </div>

 
