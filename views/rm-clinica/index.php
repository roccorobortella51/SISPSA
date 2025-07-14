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

$this->params['breadcrumbs'][] = ['label' => 'CLINICAS', 'url' => ['index']];
// --- FIN  --- 


$this->title = 'Gestión de Clínicas'; // Este sigue siendo el título para la página y breadcrumbs

$clinicNames = json_encode(array_column($chartData, 'clinicName'));
$percentages = json_encode(array_column($chartData, 'percentage'));
$clinicIds = json_encode(array_column($chartData, 'clinicId')); // <-- ¡NUEVO! Pasar los IDs de las clínicas
$currentDate = Yii::$app->formatter->asDate(time(), 'php:d/m/y');
?>

<div class=row style="margin:3px !important;">
<input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />
    <div class="col-md-12 text-end">
       
    </div>
    <div class="col-md-8">
        <div class="ms-panel ms-panel-fh">
<div class="ms-panel-header d-flex justify-content-between align-items-center">
                <h1><?= $this->title = 'Gestión de Clínicas'; ?></h1>
                <div>
                    <?= Html::a('<i class="fas fa-plus"></i> CREAR NUEVA CLÍNICA', ['create'], ['class' => 'btn btn-outline-primary btn-lg']) ?>
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

                                // RIF
                                [
                                    'attribute' => 'rif',
                                    'headerOptions' => ['style' => 'color: white!important;'],
                                    'options' => ['style' => 'width: 120px;'], 
                                    'filterInputOptions' => [
                                        'placeholder' => 'Búsqueda',
                                        'class' => 'form-control text-center',
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
                                    'attribute' => 'estado',
                                    'options' => ['style' => 'width: 250px;'],
                                    'headerOptions' => ['style' => 'color: white!important;'],
                                    // MODIFICACIÓN: Añadir placeholder y centrado para el input de búsqueda
                                    'filterInputOptions' => [
                                        'placeholder' => 'Búsqueda',
                                        'class' => 'form-control text-center', // Añadimos text-center de Bootstrap
                                    ],

                                    /*filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                                     'filter' => \yii\helpers\ArrayHelper::map(\app\models\RmEstado::find()->orderBy('nombre')->asArray()->all(), 'estado.nombre', 'customer.estado.nombre'),
                                    'filterWidgetOptions' => [
                                        'pluginOptions' => ['allowClear' => true],
                                    ],
                                    'filterInputOptions' => ['placeholder' => 'Estado'],*/
                                ],
                                [
                                    'label' => 'Estatus',
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
                                    'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                                    'filter' => ['Activo' => 'Activo', 'Inactivo' => 'Inactivo'],
                                    'filterWidgetOptions' => [
                                        'pluginOptions' => ['allowClear' => true],
                                    ],
                                    'filterInputOptions' => ['placeholder' => 'Estatus'],
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
               <div class="col-md-4">
                    <div class="ms-panel ms-panel-fh">
                        <div class="ms-panel-header">
                            <h1>% Avance de los Check list por Clínica <?= $currentDate ?></h1>
                        </div>
                        <div class="card-body">
                            <div style="height: 450px; width: 100%;"> <canvas id="progressChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
</div>
<?php
// Script para Chart.js
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js');

$js = <<<JS
// Datos pasados desde PHP
const clinicNames = {$clinicNames};
const percentages = {$percentages};
const clinicIds = {$clinicIds}; // <-- ¡NUEVO! IDs de las clínicas

const ctx = document.getElementById('progressChart').getContext('2d');

const progressChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: clinicNames,
        datasets: [{
            label: '% Avance',
            data: percentages,
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                beginAtZero: true,
                max: 100,
                title: {
                    display: true,
                    text: 'Porcentaje (%)'
                }
            },
            y: {
                ticks: {
                    autoSkip: false,
                    maxRotation: 0,
                    minRotation: 0,
                    font: {
                        size: 10
                    }
                },
                title: {
                    display: false
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.x !== null) {
                            label += context.parsed.x + '%';
                        }
                        return label;
                    }
                }
            }
        },
        // *** AQUI AGREGAMOS LA LOGICA DE CLICK ***
        onClick: (e, elements) => {
            if (elements.length > 0) {
                const firstElement = elements[0];
                const index = firstElement.index; // Obtener el índice de la barra clicada

                const clickedClinicId = clinicIds[index]; // Obtener el ID de la clínica usando el índice
                if (clickedClinicId) {
                    // Construir la URL de redirección
                    const url = '/web/check-list-clinicas/index?clinica_id=' + clickedClinicId;
                    window.location.href = url; // Redirigir a la nueva URL
                }
            }
        },
        // Opcional: Cambiar el cursor a 'pointer' cuando se pasa sobre una barra
        hover: {
            mode: 'nearest',
            intersect: true,
            onHover: function(e, elements) {
                e.native.target.style.cursor = elements[0] ? 'pointer' : 'default';
            }
        }
    }
});

// Ajustar el tamaño del canvas cuando la ventana cambie
$(window).on('resize', function() {
    progressChart.resize();
});

JS;
$this->registerJs($js);
?>

