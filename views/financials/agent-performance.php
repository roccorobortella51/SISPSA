<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\BootstrapPluginAsset;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ArrayDataProvider */
/* @var $timeframe string */ 

$this->title = 'Desempeño de Agentes';
$this->params['breadcrumbs'][] = ['label' => 'Finanzas', 'url' => ['/financials/agency-earnings']];
$this->params['breadcrumbs'][] = $this->title;

// Register Bootstrap plugin for tooltips
$this->registerAssetBundle(BootstrapPluginAsset::class);

// Tooltip initialization
$tooltipJs = <<<JS
$(document).ready(function() {
    $('body').tooltip({
        selector: '[data-toggle="tooltip"]',
        container: 'body',
        trigger: 'hover'
    });
});
JS;
$this->registerJs($tooltipJs);
?>

<div class="financials-agent-performance">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <style>
        .table th {
            color: white !important;
            font-size: 14px !important; /* Standardized Header Size */
            font-weight: bold !important;
            background-color: #337ab7 !important; /* Dark Blue Header Background */
        }
        .table td {
            font-size: 13px; /* Standardized Content Size (Values and Names) */
        }
        .kpi-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        .info-icon {
            font-size: 16px !important;
            margin-left: 4px;
        }
        /* --- STYLES FOR HIGH CONTRAST TOOLTIP ICONS --- */
        .table th .info-icon {
            color: white !important; /* Change icon color to white for contrast */
            text-shadow: 0 0 2px rgba(255, 255, 0, 0.5); 
        }
        /* Style for summary: Standardized Descriptive/Summary Text Size to 13px */
        .grid-view .summary {
            margin-top: 10px;
            margin-bottom: 10px;
            font-size: 13px;
            color: #555;
        }
    </style>
    
    <div class="page-header">
        <h1>
            <?= Html::encode($this->title) ?>
            <small class="pull-right">
                <?= Html::a('<i class="fa fa-arrow-left"></i> Volver a Dashboard', ['kpi-dashboard'], ['class' => 'btn btn-default']) ?>
            </small>
        </h1>
        
        <div class="btn-group">
            <?= Html::a('Semanal', ['agent-performance', 'timeframe' => 'week'], ['class' => 'btn btn-default' . (isset($timeframe) && $timeframe == 'week' ? ' active' : '')]) ?>
            <?= Html::a('Mensual', ['agent-performance', 'timeframe' => 'month'], ['class' => 'btn btn-default' . (isset($timeframe) && $timeframe == 'month' ? ' active' : '')]) ?>
            <?= Html::a('Trimestral', ['agent-performance', 'timeframe' => 'quarter'], ['class' => 'btn btn-default' . (isset($timeframe) && $timeframe == 'quarter' ? ' active' : '')]) ?>
            <?= Html::a('Anual', ['agent-performance', 'timeframe' => 'year'], ['class' => 'btn btn-primary' . (isset($timeframe) && $timeframe == 'year' ? ' active' : '')]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <?php Pjax::begin(); ?>
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        // Layout change to place summary/pager below table, wrapped in a row/col structure
                        'layout' => "{items}\n<div class='row'><div class='col-md-6'>{summary}</div><div class='col-md-6'>{pager}</div></div>",
                        'tableOptions' => [
                            'class' => 'table table-striped table-bordered table-hover',
                            'style' => 'font-size: 13px;'
                        ],
                        // Updated Summary to show pagination details
                        'summary' => 'Mostrando desempeño de <b>{begin}-{end}</b> de <b>{totalCount}</b> agentes',
                        'columns' => [
                            [
                                'attribute' => 'agent_name',
                                // Using kpi-header wrapper for consistent centering of header content
                                'header' => Html::tag('div', 
                                    'AGENTE ' . Html::tag('i', '', [
                                        'class' => 'fa fa-info-circle info-icon', 
                                        'data-toggle' => 'tooltip', 
                                        'title' => 'Nombre del agente o asesor comercial.'
                                    ]),
                                    ['class' => 'kpi-header']
                                ),
                                'format' => 'raw',
                                'value' => function($model) {
                                    return Html::tag('strong', $model['agent_name']);
                                },
                                'contentOptions' => [
                                    'style' => 'font-weight: 600; color: #2c3e50;'
                                ]
                            ],
                            [
                                'attribute' => 'agency_name',
                                'header' => Html::tag('div', 
                                    'AGENCIA ' . Html::tag('i', '', [
                                        'class' => 'fa fa-info-circle info-icon', 
                                        'data-toggle' => 'tooltip', 
                                        'title' => 'Agencia a la que pertenece el agente.'
                                    ]),
                                    ['class' => 'kpi-header']
                                ),
                                'contentOptions' => [
                                    'style' => 'color: #4267B2;'
                                ]
                            ],
                            [
                                'attribute' => 'total_clients',
                                'header' => Html::tag('div', 
                                    'TOTAL CLIENTES ' . Html::tag('i', '', [
                                        'class' => 'fa fa-info-circle info-icon', 
                                        'data-toggle' => 'tooltip', 
                                        'title' => 'Número total de clientes asignados al agente.'
                                    ]),
                                    ['class' => 'kpi-header']
                                ),
                                'contentOptions' => [
                                    'class' => 'text-center',
                                    'style' => 'font-weight: 600; color: #4267B2;'
                                ]
                            ],
                            [
                                'attribute' => 'total_policies',
                                'header' => Html::tag('div', 
                                    'TOTAL PAGOS ' . Html::tag('i', '', [
                                        'class' => 'fa fa-info-circle info-icon', 
                                        'data-toggle' => 'tooltip', 
                                        'title' => 'Número total de pagos procesados por el agente.'
                                    ]),
                                    ['class' => 'kpi-header']
                                ),
                                'contentOptions' => [
                                    'class' => 'text-center',
                                    'style' => 'font-weight: 600; color: #2c3e50;'
                                ]
                            ],
                            [
                                'attribute' => 'total_revenue',
                                'header' => Html::tag('div', 
                                    'INGRESOS TOTALES ' . Html::tag('i', '', [
                                        'class' => 'fa fa-info-circle info-icon', 
                                        'data-toggle' => 'tooltip', 
                                        'title' => 'Monto total en ingresos generados por el agente.'
                                    ]),
                                    ['class' => 'kpi-header']
                                ),
                                'format' => 'raw',
                                'value' => function($model) {
                                    return Html::tag('span', '$ ' . number_format($model['total_revenue'], 2), [
                                        'style' => 'font-weight: bold;'
                                    ]);
                                },
                                'contentOptions' => [
                                    'class' => 'text-right',
                                    'style' => 'font-weight: bold; color: #28a745; background-color: #f9fff9;'
                                ]
                            ],
                            [
                                'attribute' => 'avg_policy_value',
                                'header' => Html::tag('div', 
                                    'VALOR PROMEDIO PAGO ' . Html::tag('i', '', [
                                        'class' => 'fa fa-info-circle info-icon', 
                                        'data-toggle' => 'tooltip', 
                                        'title' => 'Valor promedio de cada pago procesado por el agente.'
                                    ]),
                                    ['class' => 'kpi-header']
                                ),
                                'format' => 'raw',
                                'value' => function($model) {
                                    return '$ ' . number_format($model['avg_policy_value'], 2);
                                },
                                'contentOptions' => [
                                    'class' => 'text-right',
                                    'style' => 'color: #2c3e50;'
                                ]
                            ],
                            [
                                'attribute' => 'performance_score',
                                'header' => Html::tag('div', 
                                    'PUNTUACIÓN DESEMPEÑO ' . Html::tag('i', '', [
                                        'class' => 'fa fa-info-circle info-icon', 
                                        'data-toggle' => 'tooltip', 
                                        'title' => 'Puntuación general de desempeño del agente (0-100). Calculada basada en ingresos, número de clientes y actividad reciente.'
                                    ]),
                                    ['class' => 'kpi-header']
                                ),
                                'format' => 'raw',
                                'value' => function($model) {
                                    $color = $model['performance_score'] >= 80 ? 'success' : 
                                            ($model['performance_score'] >= 60 ? 'warning' : 'danger');
                                    return Html::tag('span', $model['performance_score'] . '/100', [
                                        'class' => "label label-{$color}",
                                        'style' => 'font-size: 13px; padding: 5px 10px; border-radius: 10px;',
                                        'data-toggle' => 'tooltip',
                                        'title' => 'Desempeño: ' . 
                                                    ($model['performance_score'] >= 80 ? 'Excelente' : 
                                                    ($model['performance_score'] >= 60 ? 'Bueno' : 'Necesita Mejora'))
                                    ]);
                                },
                                'contentOptions' => ['class' => 'text-center']
                            ],
                        ],
                        'emptyText' => 'No se encontraron datos de agentes para el período seleccionado.',
                    ]) ?>
                    <?php Pjax::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
