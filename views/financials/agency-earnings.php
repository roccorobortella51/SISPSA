<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ArrayDataProvider */

$this->title = 'Ventas por Agencia';
$this->params['breadcrumbs'][] = $this->title;

// Calculate totals
$totalSales = array_sum(array_column($dataProvider->models, 'total_ventas'));
$totalEarnings = array_sum(array_column($dataProvider->models, 'comision_agencia'));

// Prepare data for charts
$agencyNames = array_column($dataProvider->models, 'nombre');
$agencyEarnings = array_column($dataProvider->models, 'comision_agencia');

// Custom currency formatter function
$formatCurrency = function($value) {
    return '$' . number_format($value, 2);
};

// Get pagination information manually
$pagination = $dataProvider->getPagination();
$totalCount = $dataProvider->getTotalCount();
$begin = $pagination ? ($pagination->page * $pagination->pageSize) + 1 : 1;
$end = $pagination ? min(($pagination->page + 1) * $pagination->pageSize, $totalCount) : $totalCount;
?>

<div class="agency-earnings">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <div class="row" style="margin-bottom: 15px;">
        <div class="col-md-12">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1 style="margin: 0; font-size: 22px; font-weight: bold; color: #2c3e50;">
                    <?= Html::encode($this->title) ?>
                </h1>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading" style="background-color: #337ab7; padding: 10px 15px;">
                    <h3 class="panel-title" style="margin: 0; color: white; font-weight: bold;">
                        <span class="fa fa-heartbeat" style="color: white; margin-right: 8px;"></span>
                        <span style="color: white; font-weight: bold; font-size: 18px;">
                            Resumen de las Ventas por Agencia
                        </span>
                    </h3>
                </div>
                <div class="panel-body" style="padding: 15px;">
                    <?php Pjax::begin(); ?>
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'tableOptions' => [
                            'class' => 'table table-striped table-bordered', 
                            'style' => 'margin-bottom: 5px; border: 1px solid #ddd;'
                        ],
                        'layout' => "{items}",
                        'columns' => [
                            [
                                'class' => 'yii\grid\SerialColumn',
                                'contentOptions' => [
                                    'style' => 'width: 40px; text-align: center; background-color: #f9f9f9; font-weight: bold;'
                                ],
                                'headerOptions' => ['style' => 'background-color: #f5f5f5; color: #333;']
                            ],
                            [
                                'attribute' => 'nombre',
                                'label' => 'NOMBRE DE AGENCIA',
                                'format' => 'raw',
                                'value' => function($model) {
                                    return Html::a($model['nombre'], ['salesman-earnings', 'agencyId' => $model['id']], 
                                        // MODIFIED: Changed color to primary blue and removed text-decoration: none
                                        ['data-pjax' => '0', 'style' => 'font-weight: 600; color: #337ab7;']); 
                                },
                                'contentOptions' => ['style' => 'font-weight: 600; color: #2c3e50;'],
                                'headerOptions' => ['style' => 'background-color: #f5f5f5; color: #333;']
                            ],
                            [
                                'attribute' => 'total_ventas',
                                'label' => 'TOTAL DE VENTAS',
                                'format' => 'raw',
                                'value' => function($model) use ($formatCurrency) {
                                    return $formatCurrency($model['total_ventas']);
                                },
                                'contentOptions' => [
                                    'class' => 'text-center', 
                                    'style' => 'font-weight: 600; color: #2c3e50;'
                                ],
                                'headerOptions' => ['style' => 'background-color: #f5f5f5; color: #333;']
                            ],
                            [
                                'attribute' => 'comision_agencia',
                                'label' => 'INGRESO DE LA AGENCIA',
                                'format' => 'raw',
                                'value' => function($model) use ($formatCurrency) {
                                    return $formatCurrency($model['comision_agencia']);
                                },
                                'contentOptions' => [
                                    'class' => 'text-center', 
                                    'style' => 'font-weight: bold; color: #28a745; background-color: #f9fff9;'
                                ],
                                'headerOptions' => ['style' => 'background-color: #f5f5f5; color: #333;']
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{view}',
                                'header' => 'DETALLES',
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<span class="fa fa-search-plus" style="font-size: 20px;"></span>', 
                                            ['salesman-earnings', 'agencyId' => $model['id']], 
                                            [
                                                'title' => 'Ver detalles', 
                                                'data-pjax' => '0',
                                                'style' => 'color: #337ab7; font-weight: bold;'
                                            ]
                                        );
                                    },
                                ],
                                'contentOptions' => ['class' => 'text-center', 'style' => 'font-weight: bold;'],
                                'headerOptions' => ['style' => 'background-color: #f5f5f5; color: #333;']
                            ],
                        ],
                        'emptyText' => '<div style="padding: 15px; text-align: center; color: #777;">No se encontraron agencias</div>',
                    ]); ?>
                    
                    <div class="summary" style="padding: 8px 0; color: #555;">
                        Mostrando <b><?= $begin ?>-<?= $end ?></b> de <b><?= $totalCount ?></b> agencias
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
                        <span class="fa fa-dollar" style="color: white; margin-right: 8px;"></span>
                        <span style="color: white; font-weight: bold; font-size: 18px;">
                            Resumen General de Ventas
                        </span>
                    </h3>
                </div>
                <div class="panel-body text-center" style="padding: 20px 15px; background-color: #f8f9fa;">
                    <div class="row">
                        <div class="col-xs-6">
                            <div style="border-right: 1px solid #ddd; padding: 10px;">
                                <h3 style="margin: 5px 0; color: #2c3e50; font-weight: bold;">
                                    <?= $formatCurrency($totalSales) ?>
                                </h3>
                                <p style="margin: 0; font-weight: 600; color: #555;">Total en Ventas</p>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div style="padding: 10px;">
                                <h3 style="margin: 5px 0; color: #28a745; font-weight: bold;">
                                    <?= $formatCurrency($totalEarnings) ?>
                                </h3>
                                <p style="margin: 0; font-weight: 600; color: #555;">Total en Ganancias</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading" style="background-color: #337ab7; padding: 12px 15px;">
                    <h3 class="panel-title text-center" style="margin: 0;">
                        <span class="fa fa-bar-chart" style="color: white; margin-right: 8px;"></span>
                        <span style="color: white; font-weight: bold;">
                            Comparación de Ventas
                        </span>
                    </h3>
                </div>
                <div class="panel-body" style="padding: 15px;">
                    <canvas id="agency-earnings-bar-chart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading" style="background-color: #337ab7; padding: 12px 15px;">
                    <h3 class="panel-title text-center" style="margin: 0;">
                        <span class="fa fa-pie-chart" style="color: white; margin-right: 8px;"></span>
                            <span style="color: white; font-weight: bold;">
                            Distribución de Ventas
                            </span>
                    </h3>
                </div>
                <div class="panel-body" style="padding: 15px;">
                    <canvas id="agency-earnings-pie-chart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* New modern dashboard card style */
    .dashboard-card {
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid #e0e0e0;
        text-align: center;
    }
    .dashboard-card-title {
        font-size: 18px;
        color: #666;
        margin-bottom: 5px;
        font-weight: 500;
    }
    .dashboard-card-value {
        font-size: 32px;
        font-weight: 700;
        color: #333;
    }
    /* Chart Panel Styling */
    .chart-panel {
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid #e0e0e0;
    }
    .chart-panel-heading {
        font-size: 18px;
        font-weight: 500;
        color: #333;
        padding-bottom: 10px;
        border-bottom: 1px solid #e0e0e0;
        margin-bottom: 15px;
    }
    /* GridView table styling */
    .grid-view table {
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border: 1px solid #e0e0e0;
    }
    .grid-view th {
        background-color: #f8f9fa;
        color: #555;
        font-weight: 600;
        border-bottom: 2px solid #ddd;
    }
    .grid-view td {
        vertical-align: middle;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = <?= json_encode($agencyNames); ?>;
    const earningsData = <?= json_encode($agencyEarnings); ?>;
    const chartColors = [
        '#4267B2', // Facebook Blue
        '#F65058', // Red
        '#00B06B', // Green
        '#F9A11A', // Orange
        '#5D5D5D', // Dark Gray
        '#A16E83', // Purple
    ];

    // Bar Chart
    const barData = {
        labels: labels,
        datasets: [{
            label: 'Ganancias ($)',
            backgroundColor: chartColors.slice(0, labels.length),
            data: earningsData,
            borderWidth: 0,
        }]
    };
    const barConfig = {
        type: 'bar',
        data: barData,
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    beginAtZero: true,
                    grid: {
                        display: false
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                },
                y: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: { display: false },
                title: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                    titleFont: { size: 14 },
                    bodyFont: { size: 12 },
                    padding: 10,
                    callbacks: {
                        label: function(context) {
                            return '$' + context.raw.toLocaleString(undefined, {minimumFractionDigits: 2});
                        }
                    }
                }
            }
        }
    };
    new Chart(document.getElementById('agency-earnings-bar-chart'), barConfig);

    // Pie Chart
    const pieData = {
        labels: labels,
        datasets: [{
            label: 'Ganancias ($)',
            data: earningsData,
            backgroundColor: chartColors.slice(0, labels.length),
            borderWidth: 1,
            borderColor: '#ffffff',
        }]
    };
    const pieConfig = {
        type: 'pie',
        data: pieData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        padding: 15,
                        font: { size: 12 }
                    }
                },
                title: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                    titleFont: { size: 14 },
                    bodyFont: { size: 12 },
                    padding: 10,
                    callbacks: {
                        label: function(context) {
                            return context.label + ': $' + context.raw.toLocaleString(undefined, {minimumFractionDigits: 2});
                        }
                    }
                }
            }
        },
    };
    new Chart(document.getElementById('agency-earnings-pie-chart'), pieConfig);
</script>