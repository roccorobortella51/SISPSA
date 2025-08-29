<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\widgets\ActiveForm;
use app\components\UserHelper;


/**
 * @var yii\web\View $this
 * @var app\models\SisSiniestroSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var array $estadisticas
 * @var array $clinicas
 * @var int|null $clinicaSeleccionada
 */

$this->title = 'Siniestros por Clínica';
$this->params['breadcrumbs'][] = ['label' => 'CLÍNICAS', 'url' => ['/rm-clinica/index']];
$this->params['breadcrumbs'][] = $this->title;

$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol == 'GERENTE-COMERCIALIZACION' || $rol == 'Asesor' || $rol == 'Agente' || $rol == "ADMISIÓN" || $rol == "CONTROL DE CITAS" || $rol == "COORDINADOR-CLINICA");

// Preparar datos para gráficos
$clinicaNames = [];
$atendidosData = [];
$noAtendidosData = [];

if (!empty($estadisticas['por_clinica'])) {
    foreach ($estadisticas['por_clinica'] as $clinica) {
        $clinicaNames[] = $clinica['nombre'];
        $atendidosData[] = $clinica['atendidos'];
        $noAtendidosData[] = $clinica['no_atendidos'];
    }
}

$clinicaNamesJson = json_encode($clinicaNames);
$atendidosDataJson = json_encode($atendidosData);
$noAtendidosDataJson = json_encode($noAtendidosData);

// Datos para el gráfico circular
$pieLabels = ['Atendidos', 'No Atendidos'];
$pieData = [$estadisticas['atendidos'], $estadisticas['no_atendidos']];
$pieLabelsJson = json_encode($pieLabels);
$pieDataJson = json_encode($pieData);

?>

<div class="row" style="margin:3px !important;">
    <div class="col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header d-flex justify-content-between align-items-center">
                <h1><?= $this->title ?></h1>
                <div class="d-flex gap-3">
                    <?= Html::a(
                        '<i class="fas fa-undo mr-2"></i> Volver a Clínicas',
                        ['/rm-clinica/index'],
                        [
                            'class' => 'btn btn-outline-secondary btn-lg',
                            'title' => 'Volver a la lista de clínicas',
                        ]
                    ) ?>
                </div>
            </div>
            
            <div class="ms-panel-body">
                <!-- Filtro por clínica -->
                <div class="row mb-4">
                     <?php if($permisos){?>
                    <div class="col-md-6">
                        <?php $form = ActiveForm::begin([
                            'method' => 'get',
                            'action' => ['/sis-siniestro/por-clinica'],
                        ]); ?>

                       
                        
                            <div class="input-group">
                                <?= Html::dropDownList(
                                    'clinica_id',
                                    $clinicaSeleccionada,
                                    ['' => 'Todas las Clínicas'] + \yii\helpers\ArrayHelper::map($clinicas, 'id', 'nombre'),
                                    ['class' => 'form-control form-control-lg']
                                ) ?>
                                <div class="input-group-append">
                                    <?= Html::submitButton('<i class="fas fa-filter"></i> Filtrar', ['class' => 'btn btn-primary btn-lg']) ?>
                                </div>
                            </div>
                        
                        
                        <?php ActiveForm::end(); ?>
                    </div>
                <?php }else{ ?>
                     <div class="col-md-6">
                        <h4>Clínica</h4>
                    </div>

            <?php } ?>
                    
                    <div class="col-md-6 text-right">
                        <div class="alert alert-info">
                            <h3><strong>Resumen:</strong>
                            Total: <?= $estadisticas['total'] ?> | 
                            Atendidos: <span class="text-success"><?= $estadisticas['atendidos'] ?></span> | 
                            No Atendidos: <span class="text-danger"><?= $estadisticas['no_atendidos'] ?></span></h3>
                        </div>
                    </div>
                </div>
                
                <!-- Gráficos - Tamaño reducido 50% -->
                <div class="row mb-4">
                    <?php if (empty($clinicaSeleccionada) && !empty($estadisticas['por_clinica'])): ?>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Siniestros por Clínica (Comparativo)</h5>
                            </div>
                            <div class="card-body">
                                <div style="height: 200px; width: 100%;"> <!-- Reducido de 400px a 200px (50%) -->
                                    <canvas id="clinicaChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Estado de Siniestros</h5>
                            </div>
                            <div class="card-body">
                                <div style="height: 200px; width: 100%;"> <!-- Reducido de 400px a 200px (50%) -->
                                    <canvas id="estadoChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tabla de siniestros -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Listado de Siniestros</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <?= GridView::widget([
                                'id' => 'siniestros-grid',
                                'dataProvider' => $dataProvider,
                                'filterModel' => $searchModel,
                                'layout' => "{items}{pager}",
                                
                                'columns' => [
                                    [
                                        'attribute' => 'id',
                                        'options' => ['style' => 'width: 50px;'],
                                    ],
                                    [
                                        'attribute' => 'idclinica',
                                        'value' => 'clinica.nombre',
                                        'label' => 'Clínica',
                                        'filter' => \yii\helpers\ArrayHelper::map($clinicas, 'id', 'nombre'),
                                    ],
                                    [
                                        'attribute' => 'afiliado_nombre',
                                        'value' => function($model) {
                                            return $model->afiliado ? 
                                                Html::encode($model->afiliado->nombres . ' ' . $model->afiliado->apellidos) : 
                                                'N/A';
                                        },
                                        'label' => 'Nombre del Afiliado',
                                        'filterInputOptions' => [
                                            'placeholder' => 'Buscar por nombre...',
                                            'class' => 'form-control',
                                        ],
                                    ],
                                    [
                                        'attribute' => 'afiliado_cedula',
                                        'value' => function($model) {
                                            return $model->afiliado ? 
                                                Html::encode(($model->afiliado->tipo_cedula ? $model->afiliado->tipo_cedula . '-' : '') . $model->afiliado->cedula) : 
                                                'N/A';
                                        },
                                        'label' => 'Cédula del Afiliado',
                                        'filterInputOptions' => [
                                            'placeholder' => 'Buscar por cédula...',
                                            'class' => 'form-control',
                                        ],
                                    ],
                                    [
                                        'attribute' => 'fecha',
                                        'filter' => false,
                                        'format' => 'date',
                                        'contentOptions' => ['style' => 'text-align: center;'],
                                    ],
                                    [
                                        'attribute' => 'hora',
                                        'filter' => false,
                                        'format' => 'time',
                                        'contentOptions' => ['style' => 'text-align: center;'],
                                    ],
                                    [
                                        'attribute' => 'costo_total',
                                        'format' => ['currency', 'USD'],
                                        'contentOptions' => ['style' => 'text-align: right;'],
                                    ],
                                    [
                                        'attribute' => 'atendido',
                                        'format' => 'html',
                                        'value' => function($model) {
                                            return $model->atendido == 1 ? 
                                                '<span class="badge badge-success">Atendido</span>' : 
                                                '<span class="badge badge-danger">No Atendido</span>';
                                        },
                                        'filter' => [1 => 'Atendido', 0 => 'No Atendido'],
                                        'contentOptions' => ['style' => 'text-align: center;'],
                                    ],
                                    [
                                        'attribute' => 'fecha_atencion',
                                        'filter' => false,
                                        'format' => 'date',
                                        'contentOptions' => ['style' => 'text-align: center;'],
                                    ],
                                    [
                                        'class' => 'yii\grid\ActionColumn',
                                        'header' => 'Acciones',
                                        'template' => '{view}',
                                        'buttons' => [
                                            'view' => function ($url, $model) {
                                                return Html::a(
                                                    '<i class="fa fa-eye"></i>',
                                                    ['view', 'id' => $model->id, 'user_id' => $model->iduser],
                                                    [
                                                        'title' => 'Ver detalles',
                                                        'class' => 'btn btn-sm btn-info'
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
        </div>
    </div>
</div>

<?php
// Registrar scripts de Chart.js
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js');

$js = <<<JS
// Gráfico de estado (circular)
const estadoCtx = document.getElementById('estadoChart').getContext('2d');
const estadoChart = new Chart(estadoCtx, {
    type: 'pie',
    data: {
        labels: $pieLabelsJson,
        datasets: [{
            data: $pieDataJson,
            backgroundColor: ['#28a745', '#dc3545'],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false, // Importante para que respete el tamaño del contenedor
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += context.raw + ' (' + Math.round(context.parsed * 100 / context.dataset.data.reduce((a, b) => a + b, 0)) + '%)';
                        return label;
                    }
                }
            }
        }
    }
});

// Gráfico comparativo por clínica (solo si hay datos)
if (typeof document.getElementById('clinicaChart') !== 'undefined' && document.getElementById('clinicaChart') !== null) {
    const clinicaCtx = document.getElementById('clinicaChart').getContext('2d');
    const clinicaChart = new Chart(clinicaCtx, {
        type: 'bar',
        data: {
            labels: $clinicaNamesJson,
            datasets: [
                {
                    label: 'Atendidos',
                    data: $atendidosDataJson,
                    backgroundColor: '#28a745',
                },
                {
                    label: 'No Atendidos',
                    data: $noAtendidosDataJson,
                    backgroundColor: '#dc3545',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Importante para que respete el tamaño del contenedor
            scales: {
                x: {
                    stacked: true,
                },
                y: {
                    stacked: true,
                    beginAtZero: true
                }
            }
        }
    });
}
JS;

$this->registerJs($js);