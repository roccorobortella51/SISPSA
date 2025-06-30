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

$this->params['breadcrumbs'][] = ['label' => 'Clínicas', 'url' => ['index']];
// --- FIN  --- 


$this->title = 'Gestión de Clínicas'; // Este sigue siendo el título para la página y breadcrumbs

?>

<div class=row style="margin:3px !important;">
<input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />
    <div class="col-md-12 text-end">
        <div class="float-right" style="margin-bottom:10px;">
            <?= Html::a('<i class="fas fa-plus"></i> CREAR NUEVA CLÍNICA', ['create'], ['class' => 'btn btn-outline-primary btn-lg']) ?> 
        </div>
    </div>
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header">
                <h1><?= $this->title = 'Gestión de Clínicas'; ?></h1>
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

                                // Teléfono
                                [
                                    'attribute' => 'telefono',
                                    'options' => ['style' => 'width: 120px;'],
                                    'headerOptions' => ['style' => 'color: white!important;'],
                                    // MODIFICACIÓN: Añadir placeholder y centrado para el input de búsqueda
                                    'filterInputOptions' => [
                                        'placeholder' => 'Búsqueda',
                                        'class' => 'form-control text-center', // Añadimos text-center de Bootstrap
                                    ],
                                ],
                                // Correo
                                [
                                    'attribute' => 'correo',
                                    'options' => ['style' => 'width: 250px;'],
                                    'headerOptions' => ['style' => 'color: white!important;'],
                                    // MODIFICACIÓN: Añadir placeholder y centrado para el input de búsqueda
                                    'filterInputOptions' => [
                                        'placeholder' => 'Búsqueda',
                                        'class' => 'form-control text-center', // Añadimos text-center de Bootstrap
                                    ],
                                ],
                                [
                                    'label' => 'Estado',
                                    'attribute' => 'estatus',
                                    'format' => 'raw',
                                    'headerOptions' => ['class' => 'text-left header-link'],
                                    'options' => ['style' => 'width: 100px;'],
                                    'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
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
                                                    'title' => 'Detalle de la Clínica',
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
            </div>
            <div class="clearfix"></div>
</div>
 


