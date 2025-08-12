<?php

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
 * @var app\models\BaremoSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\RmClinica $clinica 
 * @var app\models\Baremo 
 */

if (!isset($clinica)) {
    $clinica = (object)['id' => null, 'nombre' => 'Clínica Desconocida'];
}
$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin'); 

// --- BREADCRUMBS ---
if($permisos == true){
$this->params['breadcrumbs'][] = ['label' => 'CLÍNICAS', 'url' => ['/rm-clinica/index']];
}
$this->params['breadcrumbs'][] = ['label' => Html::encode($clinica->nombre), 'url' => ['/rm-clinica/view', 'id' => $clinica->id]];

$this->params['breadcrumbs'][] = 'BAREMOS'; 

$this->title = 'Gestión de Baremos de ' . Html::encode($clinica->nombre); 


?>

<div class="main-container"> <!-- Contenedor principal de la vista -->
    <input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />
    
    <!-- Encabezado y Botones de Acción Principal -->
    <div class="header-section"> 
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="header-buttons-group">
            
            <?= Html::a(
                '<i class="fas fa-undo mr-2"></i> Volver a Clínica', 
                ['/rm-clinica/view', 'id' => $clinica->id], 
                [
                    'class' => 'btn-base btn-gray', 
                    'title' => 'Volver a los detalles de la clínica',
                ]
            ) ?>
        </div>
    </div>

    <?php if ($permisos) : ?>
        <!-- Panel para Agregar Baremos -->
        <div class="ms-panel ms-panel-fh border-blue"> <!-- Usando ms-panel y borde azul -->
            <div class="ms-panel-header">
                <h3 class="section-title">
                    <i class="fas fa-plus-circle mr-3 text-blue-600"></i> Agregar Nuevo Baremo a la Clínica
                </h3>
            </div>
            <div class="ms-panel-body">
                <?php $form = ActiveForm::begin([
                    'action' => ['index', 'clinica_id' => $clinica->id], 
                ]); ?>
                <div class="row g-3"> 
                    <div class="col-md-2">
                        <?= $form->field($model, 'area_id')->widget(Select2::classname(), [
                            'data' => UserHelper::getAreaList(),
                            'options' => [
                                'placeholder' => 'Seleccione un área...',
                                'class' => 'form-control form-control-lg', 
                            ],
                            'pluginOptions' => [
                                'allowClear' => false,
                            ],
                        ])->label('Área') ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'nombre_servicio')->textInput([ 
                            'class' => 'form-control form-control-lg', 
                            'placeholder' => 'Nombre del Baremo'
                        ])->label('Nombre del Servicio') ?>
                    </div>
                    <div class="col-md-4">
                         <?= $form->field($model, 'descripcion')->textInput([ 
                            'class' => 'form-control form-control-lg', 
                            'placeholder' => 'Descripción del Baremo'
                        ])->label('Descripción') ?>
                    </div>
                    <div class="col-md-2">
                         <?= $form->field($model, 'costo')->textInput([
                            'type' => 'number',
                            'class' => 'form-control form-control-lg', 
                            'placeholder' => '0.00' 
                        ])->label('Costo') ?>
                    </div>
                    <div class="col-md-2">
                         <?= $form->field($model, 'precio')->textInput([
                            'type' => 'number', 
                            'class' => 'form-control form-control-lg', 
                            'placeholder' => '0.00'
                        ])->label('Precio') ?>
                    </div>
                    <div class="col-md-12 text-end mt-4">
                        <?= Html::submitButton('<i class="fas fa-save mr-2"></i> Guardar Baremo', ['class' => 'btn-base btn-blue']) ?>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Panel para la Gestión de Baremos (GridView) -->
    <div class="ms-panel ms-panel-fh border-indigo"> 
        <div class="ms-panel-header">
            <h3 class="section-title">
                <i class="fas fa-list-alt mr-3 text-indigo-600"></i> Listado de Baremos de <?= Html::encode($clinica->nombre) ?>
            </h3>
        </div>
        <div class="ms-panel-body">
            <div class="table-responsive">
                <?= GridView::widget([
                    'id' => 'baremo-grid', 
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
                            'attribute' => 'area_id',
                            'value' => function ($model) {
                                return $model->area ? $model->area->nombre : "";
                            },
                            'filterType' => GridView::FILTER_SELECT2,
                            'filter' => UserHelper::getAreaList(),
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => Yii::t('app', 'Seleccione')],
                            'format' => 'raw',
                            'headerOptions' => ['class' => 'text-center header-link'],
                            'label' => 'Área',
                        ],
                        [
                            'attribute' => 'nombre_servicio',
                            'format' => 'ntext',
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'filterInputOptions' => [
                                'placeholder' => 'Búsqueda',
                                'class' => 'form-control text-center',
                            ],
                        ],
                        [
                            'attribute' => 'descripcion',
                            'format' => 'ntext',
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'filterInputOptions' => [
                                'placeholder' => 'Búsqueda',
                                'class' => 'form-control text-center',
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
                        [
                            'label' => 'Estatus',
                            'attribute' => 'estatus',
                            'format' => 'raw',
                            'headerOptions' => ['class' => 'text-left header-link'],
                            'contentOptions' => ['style' => 'text-align: center; padding: 10px !important;'],
                            'value' => function ($model)use($permisos) {

                                if($permisos){
                                    $isActive = ($model->estatus === 'Activo' || $model->estatus === 1 || $model->estatus === true);
                                    
                                    return SwitchInput::widget([
                                        'name' => 'status_'.$model->id,
                                        'value' => $isActive,
                                        'pluginEvents' => [
                                            'switchChange.bootstrapSwitch' => "function(e){updatestatus('$model->id')}"
                                        ],
                                        'pluginOptions' => [
                                            'onText' => 'Activo',
                                            'offText' => 'Inactivo',
                                            'onColor' => 'success',
                                            'offColor' => 'danger',
                                            'state' => $isActive
                                        ],
                                        'options' => [
                                            'id' => 'status-switch-'.$model->id
                                        ],
                                        'labelOptions' => ['style' => 'font-size: 12px;'],
                                    ]);
                                }else{

                                    return '<span class="status-badge ' . ($model->estatus == 'Activo' ? 'active' : 'inactive') . '">' .
                                         ($model->estatus == 'Activo' ? 'Activo' : 'Inactivo') . '</span>';
                                }
                            },
                            'filterType' => GridView::FILTER_SELECT2,
                            'filter' => ['Activo' => 'Activo', 'Inactivo' => 'Inactivo'],
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => 'Estatus'],
                        ],
                        // Columna de Acciones - Restaurada al maquetado y diseño original
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'header' => 'ACCIONES',
                            'template' => '<div class="d-flex justify-content-center gap-0">{view}{update}</div>', // Template original
                            'options' => ['style' => 'width:55px; min-width:55px;'], // Ancho original
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'], // Padding original
                            'buttons' => [
                                'view' => function ($url, $model, $key) use ($clinica) { // Asegúrate de pasar $clinica aquí
                                    return Html::a(
                                        '<i class="fa fa-eye"></i>',
                                        Url::to(['view', 'id' => $model->id, 'clinica_id' => $clinica->id]), 
                                        [
                                            'title' => 'Detalle del baremo',
                                            'class' => 'btn-action view'
                                        ]
                                    );
                                },
                                'update' => function ($url, $model, $key) use ($permisos) {
                                    if ($permisos) {
                                        return Html::a(
                                            '<i class="fas fa-pencil-alt"></i>', // Icono sin ms-text-primary
                                            Url::to(['update', 'id' => $model->id]),
                                            [
                                                'title' => 'Editar',
                                                'class' => 'btn-action edit' // Clase de sipsa.css para el botón de editar
                                            ]
                                        );
                                    }
                                },
                                'delete' => function ($url, $model, $key) use ($permisos) {
                                    if ($permisos) {
                                        return Html::a(
                                            '<i class="far fa-trash-alt"></i>', // Icono sin ms-text-danger
                                            Url::to(['delete', 'id' => $model->id]),
                                            [
                                                'title' => 'Eliminar',
                                                'data-confirm' => '¿Estás seguro de que quieres eliminar este baremo?',
                                                'data-method' => 'post',
                                                'class' => 'btn-action delete' // Clase de sipsa.css para el botón de eliminar
                                            ]
                                        );
                                    }
                                },
                            ],
                        ],
                    ], // Fin de columns
                ]); ?>
            </div>
        </div>
    </div>
</div>
