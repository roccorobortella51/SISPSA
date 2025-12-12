<?php
    use yii\helpers\Html;
    use yii\grid\GridView;
    use yii\widgets\Pjax;
    use yii\bootstrap\BootstrapPluginAsset;

    /* @var $this yii\web\View */
    /* @var $dataProvider yii\data\ArrayDataProvider */
    /* @var $agencyName string */
    /* @var $agencyId int */
    /* @var $siniestroNames array */
    /* @var $siniestroCounts array */

    $this->title = 'Ventas por Asesor';
    $this->params['breadcrumbs'][] = ['label' => 'Ventas por Agencia', 'url' => ['agency-earnings']];
    $this->params['breadcrumbs'][] = $agencyName;

    // 1. REGISTER BOOTSTRAP PLUGIN ASSET: This ensures the .tooltip() function is available.
    $this->registerAssetBundle(BootstrapPluginAsset::class);

    // Calculate totals
    $totalSales = array_sum(array_column($dataProvider->models, 'total_ventas'));
    $totalEarnings = array_sum(array_column($dataProvider->models, 'comision_asesor'));
    $totalSiniestros = array_sum(array_column($dataProvider->models, 'total_siniestros'));

    // Prepare data for charts - INCLUDE ALL SALESMEN
    $salesmanNames = [];
    $salesmanSales = [];

    foreach ($dataProvider->models as $model) {
        if (!empty($model['nombre'])) {
            $salesmanNames[] = $model['nombre'];
            $salesmanSales[] = (float)($model['total_ventas'] ?? 0);
        }
    }

    $salesmanEarnings = $salesmanSales;

    // Custom currency formatter function
    $formatCurrency = function($value) {
        return '$' . number_format($value, 2);
    };

    // Get pagination information manually
    $pagination = $dataProvider->getPagination();
    $totalCount = $dataProvider->getTotalCount();
    $begin = $pagination ? ($pagination->page * $pagination->pageSize) + 1 : 1;
    $end = $pagination ? min(($pagination->page + 1) * $pagination->pageSize, $totalCount) : $totalCount;

    // 2. PJAX-AWARE TOOLTIP INITIALIZATION
    $minimalJs = <<<JS
    // Let Bootstrap handle tooltips automatically without our interference
    $(document).ready(function() {
        // Just enable tooltips globally - Bootstrap should handle the rest
        $('body').tooltip({
            selector: '[data-toggle="tooltip"]',
            container: 'body',
            trigger: 'hover'
        });
    });
    
    // No PJAX handlers - let Bootstrap's native delegation handle dynamic content
JS;
    $this->registerJs($minimalJs);
?>

<div class="salesman-earnings">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        .table th {
            color: white !important;
            font-size: 14px !important;
            font-weight: bold !important;
        }
        .table td {
            font-size: 13px !important;
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
        /* --- NEW STYLES FOR HIGH CONTRAST TOOLTIP ICONS --- */
        /* Target the info icon specifically within the table header (th) and make it white */
        .table th .info-icon {
            color: white !important; /* Change icon color to white for contrast */
            /* Optional: Add a slight yellow glow to make it pop further */
            text-shadow: 0 0 2px rgba(255, 255, 0, 0.5); 
            background-color: transparent !important; /* Ensure no white background on icon */
            border-radius: 0 !important; /* Remove border radius if it was affecting the circle */
        }
        .chart-container {
            position: relative;
            height: 250px;
        }
    </style>
    
    <div class="row" style="margin-bottom: 15px;">
        <div class="col-md-12">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1 style="margin: 0; font-size: 22px; font-weight: bold; color: #2c3e50;">
                    <?= Html::encode($this->title) ?>
                </h1>
                <?= Html::a(
                    '<span class="fa fa-arrow-left"></span> Volver a Agencias', 
                    ['agency-earnings'], 
                    ['class' => 'btn btn-default btn-sm', 'style' => 'color: #337ab7; border-color: #337ab7;']
                ) ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default" style="border-color: #337ab7;">
                <div class="panel-heading" style="background-color: #337ab7; padding: 10px 15px;">
                    <h3 class="panel-title" style="margin: 0;">
                        <span class="fa fa-users" style="color: white; margin-right: 8px;"></span>
                        <span style="color: white; font-weight: bold; font-size: 18px;">
                            Asesores: <?= Html::encode($agencyName) ?>
                        </span>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body" style="padding: 15px;">
                    <?php Pjax::begin(); ?>
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'tableOptions' => [
                            'class' => 'table table-striped table-bordered', 
                            'style' => 'margin-bottom: 5px; border: 1px solid #ddd; font-size: 13px;'
                        ],
                        'layout' => "{items}",
                        'columns' => [
                            [
                                'class' => 'yii\grid\SerialColumn',
                                'contentOptions' => [
                                    'style' => 'width: 40px; text-align: center; background-color: #f9f9f9; font-weight: bold; font-size: 13px;'
                                ],
                                'headerOptions' => [
                                    'style' => 'background-color: #337ab7; color: white; font-size: 14px; font-weight: bold;'
                                ],
                            ],
                            [
                                'attribute' => 'nombre',
                                'label' => 'NOMBRE',
                                'contentOptions' => [
                                    'style' => 'font-weight: 600; color: #2c3e50; font-size: 13px;'
                                ],
                                'headerOptions' => [
                                    'style' => 'background-color: #337ab7; color: white; font-size: 14px; font-weight: bold;'
                                ]
                            ],
                            [
                                'attribute' => 'total_clients',
                                'header' => Html::tag('div', 
                                    Html::tag('span', 'TOTAL DE CLIENTES', [
                                        'style' => 'color: white;',
                                    ]) . 
                                    // Removed 'text-info' class and custom inline style for background/border
                                    Html::tag('i', '', [
                                        'class' => 'fa fa-info-circle info-icon', 
                                        'data-toggle' => 'tooltip',
                                        'title' => 'Número total de clientes asociados al asesor en el período.',
                                    ]), 
                                    ['class' => 'kpi-header']
                                ),
                                'format' => 'raw',
                                'contentOptions' => [
                                    'class' => 'text-center',
                                    'style' => 'font-weight: 600; color: #4267B2; font-size: 13px;'
                                ],
                                'headerOptions' => [
                                    'style' => 'background-color: #337ab7; font-size: 14px; font-weight: bold;'
                                ]
                            ],
                            [
                                'attribute' => 'total_ventas',
                                'header' => Html::tag('div', 
                                    Html::tag('span', 'TOTAL DE VENTAS', [
                                        'style' => 'color: white;',
                                    ]) . 
                                    // Removed 'text-info' class and custom inline style for background/border
                                    Html::tag('i', '', [
                                        'class' => 'fa fa-info-circle info-icon',
                                        'data-toggle' => 'tooltip',
                                        'title' => 'Monto total de las ventas generadas por el asesor (sin deducir comisiones).',
                                    ]), 
                                    ['class' => 'kpi-header']
                                ),
                                'format' => 'raw',
                                'value' => function ($model) use ($formatCurrency) {
                                    return $formatCurrency($model['total_ventas']);
                                },
                                'contentOptions' => [
                                    'class' => 'text-center', 
                                    'style' => 'font-weight: 600; color: #2c3e50; font-size: 13px;'
                                ],
                                'headerOptions' => [
                                    'style' => 'background-color: #337ab7; font-size: 14px; font-weight: bold;'
                                ]
                            ],
                            [
                                'attribute' => 'porcentaje_comision_display',
                                'header' => Html::tag('div', 
                                    Html::tag('span', 'COMISIÓN VENTA (%)', ['style' => 'color: white;',]) . 
                                    // Removed 'text-info' class and custom inline style for background/border
                                    Html::tag('i', '', [
                                        'class' => 'fa fa-info-circle info-icon',
                                        'data-toggle' => 'tooltip',
                                        'title' => 'Porcentaje de comisión que el asesor recibe sobre el monto de sus ventas.',
                                    ]), 
                                    ['class' => 'kpi-header']
                                ),
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return Html::tag('span', Html::encode($model['porcentaje_comision_display']) . '%', 
                                        ['class' => 'label label-primary', 'style' => 'border-radius: 10px; padding: 5px 10px; font-size: 13px;']);
                                },
                                'contentOptions' => [
                                    'class' => 'text-center',
                                    'style' => 'font-size: 13px;'
                                ],
                                'headerOptions' => [
                                    'style' => 'background-color: #337ab7; font-size: 14px; font-weight: bold;'
                                ]
                            ],
                            [
                                'attribute' => 'comision_asesor',
                                'header' => Html::tag('div', 
                                    Html::tag('span', 'INGRESOS DEL ASESOR', ['style' => 'color: white;',]) . 
                                    // Removed 'text-info' class and custom inline style for background/border
                                    Html::tag('i', '', [
                                        'class' => 'fa fa-info-circle info-icon',
                                        'data-toggle' => 'tooltip',
                                        'title' => 'Monto total de comisiones (ingresos) ganadas por el asesor en el período.',
                                    ]), 
                                    ['class' => 'kpi-header']
                                ),
                                'format' => 'raw',
                                'value' => function ($model) use ($formatCurrency) {
                                    return $formatCurrency($model['comision_asesor']);
                                },
                                'contentOptions' => [
                                    'class' => 'text-center', 
                                    'style' => 'font-weight: bold; color: #28a745; background-color: #f9fff9; font-size: 13px;'
                                ],
                                'headerOptions' => [
                                    'style' => 'background-color: #337ab7; font-size: 14px; font-weight: bold;'
                                ]
                            ],
                            [
                                'attribute' => 'commission_efficiency',
                                'header' => Html::tag('div', 
                                    Html::tag('span', 'EFICIENCIA DE COMISIÓN', ['style' => 'color: white;',]) . 
                                    // Removed 'text-info' class and custom inline style for background/border
                                    Html::tag('i', '', [
                                        'class' => 'fa fa-info-circle info-icon',
                                        'data-toggle' => 'tooltip',
                                        'title' => 'Ventas Totales / Comisión del Asesor. Mide cuánta venta genera el asesor por cada dólar de comisión que recibe.',
                                    ]), 
                                    ['class' => 'kpi-header']
                                ),
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $efficiency = $model['commission_efficiency'];
                                    $color = $efficiency >= 15 ? 'success' : ($efficiency >= 10 ? 'warning' : 'danger');
                                    $text = $efficiency > 0 ? $efficiency . 'x' : 'N/A';
                                    
                                    $title_content = '$' . number_format($model['total_ventas'], 2) . ' ventas / $' . number_format($model['comision_asesor'], 2) . ' comisión';

                                    return Html::tag('span', $text, [
                                        'class' => "label label-{$color}", 
                                        'style' => 'border-radius: 10px; padding: 5px 10px; font-size: 13px; cursor: help;',
                                        'title' => $title_content,
                                        'data-toggle' => 'tooltip'
                                    ]);
                                },
                                'contentOptions' => [
                                    'class' => 'text-center',
                                    'style' => 'font-size: 13px;'
                                ],
                                'headerOptions' => [
                                    'style' => 'background-color: #337ab7; font-size: 14px; font-weight: bold;'
                                ]
                            ],
                            // START NEW COLUMN: TOTAL DE SINIESTROS
                            [
                                'attribute' => 'total_siniestros',
                                'header' => Html::tag('div', 
                                    Html::tag('span', 'TOTAL DE SINIESTROS', [
                                        'style' => 'color: white;',
                                    ]) . 
                                    Html::tag('i', '', [
                                        'class' => 'fa fa-info-circle info-icon', 
                                        'data-toggle' => 'tooltip',
                                        'title' => 'Número total de siniestros creados por los clientes del asesor en el período.',
                                    ]), 
                                    ['class' => 'kpi-header']
                                ),
                                'format' => 'raw',
                                'contentOptions' => [
                                    'class' => 'text-center',
                                    'style' => 'font-weight: 600; color: #DC3545; font-size: 13px;' // Red color for claims
                                ],
                                'headerOptions' => [
                                    'style' => 'background-color: #337ab7; font-size: 14px; font-weight: bold;'
                                ]
                            ],
                            // END NEW COLUMN: TOTAL DE SINIESTROS
                            [
                                'attribute' => 'quarterly_growth',
                                'header' => Html::tag('div', 
                                    Html::tag('span', 'CRECIMIENTO TRIMESTRAL', ['style' => 'color: white;',]) . 
                                    // Removed 'text-info' class and custom inline style for background/border
                                    Html::tag('i', '', [
                                        'class' => 'fa fa-info-circle info-icon',
                                        'data-toggle' => 'tooltip',
                                        'title' => 'Crecimiento o decrecimiento de las ventas del asesor comparando el trimestre actual con el trimestre anterior.',
                                    ]), 
                                    ['class' => 'kpi-header']
                                ),
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $growth = $model['quarterly_growth'];
                                    $currentQuarter = $model['current_quarter'];
                                    $previousQuarter = $model['previous_quarter'];
                                    
                                    if ($growth === 0) {
                                        $color = 'default';
                                        $icon = '➡️';
                                        $text = '0%';
                                    } elseif ($growth > 0) {
                                        $color = 'success';
                                        $icon = '📈';
                                        $text = '+' . $growth . '%';
                                    } else {
                                        $color = 'danger';
                                        $icon = '📉';
                                        $text = $growth . '%';
                                    }
                                    
                                    $tooltip = "{$previousQuarter}: $" . number_format($model['previous_quarter_sales'], 2) . " → {$currentQuarter}: $" . number_format($model['current_quarter_sales'], 2);
                                    
                                    return Html::tag('span', $icon . ' ' . $text, [
                                        'class' => "label label-{$color}", 
                                        'style' => 'border-radius: 10px; padding: 5px 10px; font-size: 13px; cursor: help;',
                                        'title' => $tooltip,
                                        'data-toggle' => 'tooltip'
                                    ]);
                                },
                                'contentOptions' => [
                                    'class' => 'text-center',
                                    'style' => 'font-size: 13px;'
                                ],
                                'headerOptions' => [
                                    'style' => 'background-color: #337ab7; font-size: 14px; font-weight: bold;'
                                ]
                            ],
                        ],
                        'emptyText' => '<div style="padding: 15px; text-align: center; color: #777; font-size: 14px;">No se encontraron asesores</div>',
                    ]) ?>
                    
                    <div class="summary" style="padding: 8px 0; color: #555; font-size: 13px;">
                        Mostrando <b><?= $begin ?>-<?= $end ?></b> de <b><?= $totalCount ?></b> asesores
                    </div>
                    
                    <?php Pjax::end(); ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading" style="background-color: #337ab7; padding: 12px 15px;">
                    <h3 class="panel-title text-center" style="margin: 0;">
                        <span class="fa fa-bar-chart" style="color: white; margin-right: 8px;"></span>
                        <span style="color: white; font-weight: bold; font-size: 18px;">
                            Resumen General de Ventas
                        </span>
                    </h3>
                </div>
                <div class="panel-body text-center" style="padding: 20px 15px; background-color: #f8f9fa;">
                    <div class="row">
                        <div class="col-xs-4">
                            <div style="border-right: 1px solid #ddd; padding: 10px;">
                                <h3 style="margin: 5px 0; color: #2c3e50; font-weight: bold; font-size: 20px;">
                                    <?= $formatCurrency($totalSales) ?>
                                </h3>
                                <p style="margin: 0; font-weight: 600; color: #555; font-size: 14px;">Total en Ventas</p>
                            </div>
                        </div>
                        <div class="col-xs-4">
                            <div style="border-right: 1px solid #ddd; padding: 10px;">
                                <h3 style="margin: 5px 0; color: #28a745; font-weight: bold; font-size: 20px;">
                                    <?= $formatCurrency($totalEarnings) ?>
                                </h3>
                                <p style="margin: 0; font-weight: 600; color: #555; font-size: 14px;">Total en Ganancias</p>
                            </div>
                        </div>
                        <div class="col-xs-4">
                            <div style="padding: 10px;">
                                <h3 style="margin: 5px 0; color: #DC3545; font-weight: bold; font-size: 20px;">
                                    <?= number_format($totalSiniestros) ?>
                                </h3>
                                <p style="margin: 0; font-weight: 600; color: #555; font-size: 14px;">Total de Siniestros</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading" style="background-color: #337ab7; padding: 12px 15px;">
                    <h3 class="panel-title text-center" style="margin: 0;">
                        <span class="fa fa-signal" style="color: white; margin-right: 8px;"></span>
                        <span style="color: white; font-weight: bold;">
                            Comparación de Ventas
                        </span>
                    </h3>
                </div>
                <div class="panel-body" style="padding: 15px; position: relative;">
                    <div class="chart-container">
                        <canvas id="salesman-earnings-bar-chart" height="250"></canvas>
                        <div id="bar-chart-message"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading" style="background-color: #337ab7; padding: 12px 15px;">
                    <h3 class="panel-title text-center" style="margin: 0;">
                        <span class="fa fa-pie-chart" style="color: white; margin-right: 8px;"></span>
                        <span style="color: white; font-weight: bold;">
                            Distribución de Ventas
                        </span>
                    </h3>
                </div>
                <div class="panel-body" style="padding: 15px; position: relative;">
                    <div class="chart-container">
                        <canvas id="salesman-earnings-pie-chart" height="250"></canvas>
                        <div id="pie-chart-message"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading" style="background-color: #DC3545; padding: 12px 15px;">
                    <h3 class="panel-title text-center" style="margin: 0;">
                        <span class="fa fa-exclamation-triangle" style="color: white; margin-right: 8px;"></span>
                        <span style="color: white; font-weight: bold;">
                            Distribución de Siniestros
                        </span>
                    </h3>
                </div>
                <div class="panel-body" style="padding: 15px; position: relative;">
                    <div class="chart-container">
                        <canvas id="siniestros-pie-chart" height="250"></canvas>
                        <div id="siniestros-chart-message"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing charts...');
    
    const labels = <?= json_encode($salesmanNames); ?>;
    const earningsData = <?= json_encode($salesmanEarnings); ?>;
    const siniestroNames = <?= json_encode($siniestroNames); ?>;
    const siniestroCounts = <?= json_encode($siniestroCounts); ?>;
    
    console.log('Chart data loaded:', {
        labels: labels,
        earningsData: earningsData,
        siniestroNames: siniestroNames,
        siniestroCounts: siniestroCounts
    });

    const chartColors = [
        '#4267B2', '#F65058', '#00B06B', '#F9A11A', '#5D5D5D', '#A16E83',
        '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD',
        '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E9', '#F8C471', '#82E0AA'
    ];

    // Check if we have valid data
    function hasValidData(dataArray) {
        const hasData = dataArray && 
                        dataArray.length > 0 && 
                        dataArray.some(val => val > 0);
        
        console.log('Data validation result:', hasData);
        return hasData;
    }

    function showNoDataMessage(chartId, message = 'No hay datos para mostrar') {
        const messageDiv = document.getElementById(chartId + '-message');
        if (messageDiv) {
            messageDiv.innerHTML = `
                <div class="text-center" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #6c757d;">
                    <i class="fa fa-chart-bar" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i><br>
                    <h4 style="color: #6c757d;">${message}</h4>
                    <p>No se encontraron datos registrados.</p>
                </div>
            `;
        }
    }

    function createCharts() {
        console.log('Creating charts...');
        
        // Bar Chart for Sales
        if (hasValidData(earningsData)) {
            try {
                const barCtx = document.getElementById('salesman-earnings-bar-chart').getContext('2d');
                console.log('Bar chart canvas context:', barCtx);
                
                const barChart = new Chart(barCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Ventas Totales ($)',
                            data: earningsData,
                            backgroundColor: chartColors.slice(0, labels.length),
                            borderWidth: 0
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString();
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Ventas: $' + context.parsed.x.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
                console.log('Bar chart created successfully');
            } catch (error) {
                console.error('Error creating bar chart:', error);
                showNoDataMessage('bar-chart', 'Error al crear gráfico de ventas');
            }
        } else {
            showNoDataMessage('bar-chart', 'No hay ventas para mostrar');
        }

        // Pie Chart for Sales Distribution
        if (hasValidData(earningsData)) {
            try {
                const pieCtx = document.getElementById('salesman-earnings-pie-chart').getContext('2d');
                console.log('Pie chart canvas context:', pieCtx);
                
                const pieChart = new Chart(pieCtx, {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Ventas Totales ($)',
                            data: earningsData,
                            backgroundColor: chartColors.slice(0, labels.length),
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { 
                                position: 'right'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const value = context.raw;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${context.label}: $${value.toLocaleString()} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
                console.log('Pie chart created successfully');
            } catch (error) {
                console.error('Error creating pie chart:', error);
                showNoDataMessage('pie-chart', 'Error al crear gráfico de distribución');
            }
        } else {
            showNoDataMessage('pie-chart', 'No hay ventas para mostrar');
        }

        // NEW: Pie Chart for Siniestros Distribution
        if (hasValidData(siniestroCounts)) {
            try {
                const siniestrosCtx = document.getElementById('siniestros-pie-chart').getContext('2d');
                console.log('Siniestros pie chart canvas context:', siniestrosCtx);
                
                const siniestrosChart = new Chart(siniestrosCtx, {
                    type: 'pie',
                    data: {
                        labels: siniestroNames,
                        datasets: [{
                            label: 'Total de Siniestros',
                            data: siniestroCounts,
                            backgroundColor: chartColors.slice(0, siniestroNames.length),
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { 
                                position: 'right'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const value = context.raw;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${context.label}: ${value} siniestros (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
                console.log('Siniestros pie chart created successfully');
            } catch (error) {
                console.error('Error creating siniestros pie chart:', error);
                showNoDataMessage('siniestros-chart', 'Error al crear gráfico de siniestros');
            }
        } else {
            showNoDataMessage('siniestros-chart', 'No hay siniestros para mostrar');
        }
    }

    // Wait a bit to ensure all elements are rendered before chart generation
    setTimeout(createCharts, 100);
});
</script>