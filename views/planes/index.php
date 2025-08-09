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
 * @var app\models\PlanSearch $searchModel // Modelo de búsqueda para planes
 * @var yii\data\ActiveDataProvider $dataProvider // Proveedor de datos para planes
 * @var app\models\RmClinica $clinica // Se asume que el modelo de clínica se pasa a la vista
 * @var app\models\Plan $model // Modelo para el formulario de agregar plan
 */

if (!isset($clinica)) {
    $clinica_id = Yii::$app->request->get('clinica_id');
    if (!empty($clinica_id)) {
        $clinica = \app\models\RmClinica::findOne((int)$clinica_id);
        if (!$clinica) {
            $clinica = (object)['id' => (int)$clinica_id, 'nombre' => 'Clínica Desconocida'];
        }
    } else {
        $clinica = (object)['id' => null, 'nombre' => 'Clínica Desconocida'];
    }
}


$this->params['breadcrumbs'][] = ['label' => 'CLÍNICAS', 'url' => ['/rm-clinica/index']];

if ($clinica->id !== null) { 
    $this->params['breadcrumbs'][] = ['label' => Html::encode($clinica->nombre), 'url' => ['/rm-clinica/view', 'id' => $clinica->id]];
}
$this->params['breadcrumbs'][] = 'PLANES'; 

$this->title = 'Gestión de Planes de ' . Html::encode($clinica->nombre); 

$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin'); 
?>

<div class="main-container"> 
    <input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />
    
    <!-- Encabezado y Botones de Acción Principal -->
    <div class="header-section"> 
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="header-buttons-group">
            <?php if ($permisos) : ?>
                <?= Html::a(
                    '<i class="fas fa-plus mr-2"></i> AGREGAR PLAN', 
                    ['create', 'clinica_id' => $clinica->id], 
                    ['class' => 'btn-base btn-blue'] 
                ) ?>
            <?php endif; ?>
            <?php if ($clinica->id !== null) : ?>
                <?= Html::a(
                    '<i class="fas fa-undo mr-2"></i> Volver', 
                    ['/rm-clinica/view', 'id' => $clinica->id], 
                    [
                        'class' => 'btn-base btn-gray', 
                        'title' => 'Volver a los detalles de la clínica',
                    ]
                ) ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($permisos && isset($model)) : ?>
   
    <?php endif; ?>

    <div class="ms-panel ms-panel-fh border-indigo"> 
        <div class="ms-panel-header">
            <h3 class="section-title">
                <i class="fas fa-list-alt mr-3 text-indigo-600"></i> Listado de Planes de <?= Html::encode($clinica->nombre) ?>
            </h3>
        </div>
        <div class="ms-panel-body">
            <div class="table-responsive">
                <?= GridView::widget([
                    'id' => 'planes-grid', 
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
                        // ID (si lo necesitas, lo he comentado para seguir tu ejemplo)
                        // [
                        //     'attribute' => 'id',
                        //     'options' => ['style' => 'width: 50px;'],
                        //     'headerOptions' => ['style' => 'color: white!important;'],
                        //     'filterInputOptions' => [
                        //         'placeholder' => 'Búsqueda',
                        //         'class' => 'form-control text-center',
                        //     ],
                        // ],
                        // Columna para el nombre del plan
                        [
                            'attribute' => 'nombre',
                            'format' => 'ntext',
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'filterInputOptions' => [
                                'placeholder' => 'Búsqueda',
                                'class' => 'form-control text-center',
                            ],
                        ],
                        // Columna para la descripción
                        [
                            'attribute' => 'descripcion',
                            'format' => 'ntext',
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'filterInputOptions' => [
                                'placeholder' => 'Búsqueda',
                                'class' => 'form-control text-center',
                            ],
                        ],
                        // Cobertura
                        [
                            'attribute' => 'cobertura',
                            'format' => ['currency', ''],
                            'contentOptions' => ['style' => 'text-align: right;'],
                            'filter' => false
                        ],
                        // Precio
                        [
                            'attribute' => 'precio',
                            'format' => ['currency', ''],
                            'contentOptions' => ['style' => 'text-align: right;'],
                            'filter' => false
                        ],
                        // Comisión
                        [
                            'attribute' => 'comision',
                            'format' => ['currency', ''],
                            'contentOptions' => ['style' => 'text-align: right;'],
                            'filter' => false
                        ],
                        // Edades
                        [   
                            'attribute' => 'edad_minima', 
                            'contentOptions' => ['class' => 'text-center'],
                            'label' => Yii::t('app', 'Edades'),
                            'value' => function ($model) { // Se quitó $key, $index, $widget si no se usan
                                return $model->edad_minima . "-" . $model->edad_limite . " años";
                            },
                            'headerOptions' => ['class' => 'text-left header-link'],
                        ],
                        // Estatus
                        [
                            'label' => 'Estatus',
                            'attribute' => 'estatus',
                            'format' => 'raw',
                            'headerOptions' => ['class' => 'text-center header-link'],
                            'contentOptions' => ['class' => 'text-center'],
                            'value' => function ($model) {
                                $isActive = ($model->estatus === 'Activo' || $model->estatus === 1 || $model->estatus === true);
                                
                                return SwitchInput::widget([
                                    'name' => 'status_'.$model->id,
                                    'value' => $isActive,
                                    'pluginEvents' => [
                                        'switchChange.bootstrapSwitch' => "function(e){updatestatus('$model->id')}" // Asegúrate de tener esta función JS
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
                            },
                            'filterType' => GridView::FILTER_SELECT2,
                            'filter' => ['Activo' => 'Activo', 'Inactivo' => 'Inactivo'],
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => 'Estatus'],
                        ],
                        // Columna de Acciones - Mantenida exactamente como se solicitó
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'header' => 'ACCIONES',
                            'template' => '<div class="d-flex justify-content-center gap-0">{view}{update}</div>',
                            'options' => ['style' => 'width:55px; min-width:55px;'],
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                            'buttons' => [
                               'view' => function ($url, $model, $key) use ($clinica) { // Pasar $clinica
                                    return Html::a(
                                        '<i class="fa fa-eye"></i>',
                                        Url::to(['view', 'id' => $model->id, 'clinica_id' => $clinica->id]), // Asegurar clinica_id
                                        [
                                            'title' => 'Detalles de Plán',
                                            'class' => 'btn-action view'
                                        ]
                                    );
                                },
                                'update' => function ($url, $model, $key) use ($permisos, $clinica) { // Pasar $permisos y $clinica
                                    if($permisos == true){
                                        return Html::a(
                                            '<i class="fas fa-pencil-alt ms-text-primary"></i>',
                                            Url::to(['update', 'id' => $model->id, 'clinica_id' => $clinica->id]), // Asegurar clinica_id
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
