<?php

use app\models\Corporativo;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView; // Usar Kartik GridView
use kartik\grid\ActionColumn; // También de Kartik para consistencia
use kartik\select2\Select2; // Para filtros de Select2
use app\models\RmClinica; // Para el filtro de clínicas asociadas
use app\models\User; // Para el filtro de empleados asociados
use app\models\UserDatos; // Para obtener nombres de empleados en filtros
use yii\helpers\ArrayHelper; // Para mapear datos para Select2

/** @var yii\web\View $this */
/** @var app\models\CorporativoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */


// --- BREADCRUMBS ---
$this->params['breadcrumbs'][] = ['label' => 'Corporativos', 'url' => ['index']];
// --- FIN  ---

$this->title = 'GESTION DE AFILIADOS CORPORATIVOS';
?>

<div class="row" style="margin:3px !important;">
    <input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">

            <div class="ms-panel-header d-flex justify-content-between align-items-center">
                <h1><?= Html::encode($this->title); ?></h1>

                <div>
                    <?= Html::a(
                        '<i class="fas fa-file-excel"></i> CARGAR MASIVOS DE CORPORATIVOS',
                        ['#'], // Ajusta esta ruta si tienes una funcionalidad de carga masiva
                        ['class' => 'btn btn-outline-primary btn-lg me-3']
                    ) ?>
                    <?= Html::a(
                        '<i class="fas fa-plus"></i> CREAR NUEVO CORPORATIVO',
                        ['create'],
                        ['class' => 'btn btn-outline-primary btn-lg']
                    ) ?>
                </div>
            </div>


            <div class="ms-panel-body">
                <div class="table-responsive">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'resizableColumns' => false,
                        'bordered' => false,
                        'responsiveWrap' => false,
                        'persistResize' => false,
                        'columns' => [
                            [
                                'attribute' => 'rowNumber', // Usamos el atributo virtual
                                'label' => 'No.', // La etiqueta para el encabezado
                                'options' => ['style' => 'width: 50px; text-align: center;'],
                                'headerOptions' => [
                                    'class' => 'gridview-header-custom', // Usamos la clase personalizada para estilos (si la creaste)
                                    // O si las clases no funcionan, el estilo directo con !important
                                    // 'style' => 'background-color: #007bff !important; color: white !important; text-align: center; width: 50px;',
                                ],
                                'contentOptions' => ['class' => 'text-center'], // Para centrar los números
                                'filterInputOptions' => [
                                    'placeholder' => 'No.', // El placeholder para el campo de búsqueda
                                    'class' => 'form-control text-center', // Clases para el input de búsqueda
                                ],
                                'value' => function ($model, $key, $index, $column) use ($dataProvider) {
                                    // Calcula el número de fila basado en la paginación
                                    $pagination = $dataProvider->getPagination();
                                    return ($pagination->page * $pagination->pageSize) + $index + 1;
                                },
                                'width' => '50px',
                            ],

                            'nombre',
                            [
                                'attribute' => 'rif',
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'contentOptions' => ['class' => 'text-center'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar por RIF',
                                    'class' => 'form-control text-center',
                                ],
                            ],
                            'email:email',
                            'telefono',
                            [
                                'attribute' => 'estatus',
                                'label' => 'Estatus',
                                'filter' => Select2::widget([
                                    'model' => $searchModel,
                                    'attribute' => 'estatus',
                                    'data' => [
                                        'Activo' => 'Activo',
                                        'Inactivo' => 'Inactivo',
                                        'Pendiente' => 'Pendiente',
                                    ],
                                    'options' => ['placeholder' => 'Estatus...'],
                                    'pluginOptions' => [
                                        'allowClear' => true
                                    ],
                                ]),
                                'contentOptions' => ['style' => 'width: 150px;'],
                                'contentOptions' => ['style' => 'text-align:center;'],
                            ],
                            // Columna para las clínicas asociadas (conteo y filtro)
                            [
                                'attribute' => 'clinicas_ids', // Usamos el atributo virtual para el filtro
                                'label' => 'Clínicas Asoc.',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $count = count($model->clinicas);
                                    return Html::a($count . ' Clínica(s)', ['view', 'id' => $model->id], ['title' => 'Ver clínicas asociadas']);
                                },
                                'filterType' => GridView::FILTER_SELECT2,
                                'filter' => ArrayHelper::map(RmClinica::find()->orderBy('nombre')->all(), 'id', 'nombre'),
                                'filterWidgetOptions' => [
                                    'pluginOptions' => ['allowClear' => true],
                                ],
                                'filterInputOptions' => ['placeholder' => 'Filtrar por clínicas...'],
                                'headerOptions' => ['style' => 'color: white!important; width:120px; text-align:center;'], // Asegura que el color y alineación del header sean consistentes
                                'contentOptions' => ['style' => 'text-align:center;'],
                            ],
                            // Columna para los empleados asociados (conteo y filtro)
                            [
                                'attribute' => 'users_ids', // Usamos el atributo virtual para el filtro
                                'label' => 'Empleados Asoc.',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $count = count($model->users);
                                    return Html::a($count . ' Empleado(s)', ['view', 'id' => $model->id], ['title' => 'Ver empleados asociados']);
                                },
                                'headerOptions' => ['style' => 'color: white!important; width:120px; text-align:center;'], // Asegura que el color y alineación del header sean consistentes
                                'contentOptions' => ['style' => 'text-align:center;'],
                            ],

                            // COLUMNA DE ACCIONES - Ajustada
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => 'ACCIONES',
                                'template' => '<div class="d-flex justify-content-center gap-0">{view}{update}{pay}{contracts}</div>', // Added {pay} here
                                'options' => ['style' => 'width:100px; min-width:100px;'], // Increased width to accommodate the new button
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fa fa-eye"></i>',
                                            Url::to(['view', 'id' => $model->id]),
                                            [
                                                'title' => 'Detalle de Corporativo',
                                                'class' => 'btn-action view'
                                            ]
                                        );
                                    },
                                    'update' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fas fa-pencil-alt ms-text-primary"></i>',
                                            Url::to(['update', 'id' => $model->id]),
                                            [
                                                'title' => 'Editar Corporativo',
                                                'class' => 'btn-action view'
                                            ]
                                        );
                                    },
                                    'pay' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fas fa-credit-card"></i>',
                                            Url::to(['deuda', 'id' => $model->id]), // Ajusta esta ruta según tu controlador
                                            [
                                                'title' => 'Pagar Corporativo',
                                                'class' => 'btn-action pay'
                                            ]
                                        );
                                    },
                                    'contracts' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fas fa-file-contract"></i>',
                                            Url::to(['contracts', 'id' => $model->id]),
                                            [
                                                'title' => 'Ver Contratos',
                                                'class' => 'btn-action contracts'
                                            ]
                                        );
                                    },
                                ],
                            ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</div>