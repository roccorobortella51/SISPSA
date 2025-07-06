<?php

// Importaciones necesarias
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use yii\widgets\ActiveForm;
use app\components\UserHelper;
use kartik\widgets\SwitchInput;
use kartik\select2\Select2;
/**
 * @var yii\web\View $this
 * @var app\models\RmClinicaSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 */

// --- BREADCRUMBS ---

$this->params['breadcrumbs'][] = ['label' => 'CLINICAS', 'url' => ['/rm-clinica/index']];
// --- FIN  --- 


$this->title = 'Gestión de Baremos'; // Este sigue siendo el título para la página y breadcrumbs

?>

<div class=row style="margin:3px !important;">
    <div class="col-md-12 text-end">
        <div class="float-right" style="margin-bottom:10px;">
            <?= Html::a('<i class="fas fa-undo"></i> Volver', ['/rm-clinica/update', 'id' => $clinica->id], ['class' => 'btn btn-info btn-lg']) ?> 
        </div>
    </div>
    <div class="col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header">
                <h1><?= $this->title = 'Agregar de Baremos a la Clínica '.$clinica->nombre; ?> </h1>
            </div>
            <div class="ms-panel-body">
                <?php $form = ActiveForm::begin(); ?>
                <div class="row">
                    <div class="col-md-2">
                        <?= $form->field($model, 'area_id')->widget(Select2::classname(), [
                            'data' => UserHelper::getAreaList(),
                            'options' => [
                                'placeholder' => 'Seleccione un estado...',
                                'class' => 'form-control form-control-lg',
                            ],
                            'pluginOptions' => [
                                'allowClear' => false,
                            ],
                        ]) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'nombre_servicio')->textInput([ 'class' => 'form-control', 'placeholder' => 'Escriba un nombre para el Baremo','class' => 'form-control form-control-lg',]) ?>
                    </div>
                    <div class="col-md-4">
                         <?= $form->field($model, 'descripcion')->textInput([ 'class' => 'form-control', 'placeholder' => 'Escriba una descripción para el Baremo','class' => 'form-control form-control-lg',]) ?>
                    </div>
                    <div class="col-md-2">
                         <?= $form->field($model, 'costo')->textInput(['type' => 'number','class' => 'form-control form-control-lg', 'placeholder' => '0.00' ]) ?>
                    </div>
                    <div class="col-md-2">
                         <?= $form->field($model, 'precio')->textInput(['type' => 'number', 'class' => 'form-control form-control-lg', 'placeholder' => '0.00']) ?>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group text-rigth mt-4" style="margin-right:10px;">
                            <div class="form-group text-rigth mt-4" style="margin-right:10px;">
                                <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success btn-lg']) ?>
                            </div>
                        </div>
                    </div>
                     <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
     </div>
    </div>
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header">
                <h1><?= $this->title = 'Gestión de Baremos '; ?> de <?= $clinica->nombre ?></h1>
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
                                // ID
                                [
                                    'attribute' => 'area_id',
                                    'value' => function ($model, $key, $index, $widget) {

                                        if($model->area){
                                            return $model->area->nombre;
                                        }else{
                                            return "";
                                        }

                                    },
                                    'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                                    'filter' => UserHelper::getAreaList(),
                                    'filterWidgetOptions' => [
                                        'pluginOptions' => ['allowClear' => true],
                                    ],
                                    'filterInputOptions' => ['placeholder' => Yii::t('app', 'Seleccione')],
                                    'format' => 'raw',
                                    'headerOptions' => ['class' => 'text-center header-link'], // Cambia el color del texto a negro
                                    'label' => 'Area',
                                ],
                                /*[
                                    'attribute' => 'id',
                                    'options' => ['style' => 'width: 50px;'],
                                    'headerOptions' => ['style' => 'color: white!important;'],
                                    // MODIFICACIÓN: Añadir placeholder y centrado para el input de búsqueda
                                    'filterInputOptions' => [
                                        'placeholder' => 'Búsqueda',
                                        'class' => 'form-control text-center', // Añadimos text-center de Bootstrap
                                    ],
                                ],*/

                                // Nombre
                                [
                                    'attribute' => 'nombre_servicio',
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
                                    'attribute' => 'costo',
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
                                // Estado
                                [
                                    'label' => 'Estado',
                                    'attribute' => 'estatus',
                                    'format' => 'raw',
                                    'headerOptions' => ['class' => 'text-left header-link'],
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
                                    'template' => '<div class="d-flex justify-content-center gap-0">{update}</div>',
                                    'options' => ['style' => 'width:55px; min-width:55px;'],
                                    'headerOptions' => ['style' => 'color: white!important;'],
                                    'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                                    'buttons' => [
                                        /*'view' => function ($url, $model, $key) {
                                            return Html::a(
                                                '<i class="fa fa-eye"></i>',
                                                Url::to(['view', 'id' => $model->id]),
                                                [
                                                    'title' => 'Detalle de la Clínica',
                                                    'class' => 'btn btn-link btn-sm text-success',
                                                    'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;'
                                                ]
                                            );
                                        },*/
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

 




















