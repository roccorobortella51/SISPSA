<?php
use yii\helpers\Html;
use yii\bootstrap\BootstrapPluginAsset;

$this->title = 'Dashboard de KPIs de Ventas';
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

<div class="financials-kpi-dashboard">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <style>
        .page-header h1 {
            font-size: 28px !important;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 25px;
        }
        .card-title {
            font-size: 22px !important;
            font-weight: bold;
            margin: 0;
        }
        .card-header {
            padding: 15px 20px !important;
        }
        .card-body {
            padding: 20px !important;
        }
        .info-box-text {
            font-size: 16px !important;
            font-weight: 600;
        }
        .info-box-number {
            font-size: 28px !important;
            font-weight: bold;
        }
        .small-box h3 {
            font-size: 32px !important;
            font-weight: bold;
        }
        .small-box p {
            font-size: 16px !important;
            font-weight: 600;
        }
        .btn-lg {
            font-size: 18px !important;
            padding: 12px 24px;
            font-weight: 600;
        }
        .btn {
            font-size: 16px !important;
        }
        .badge {
            font-size: 14px !important;
            padding: 6px 10px;
        }
        strong {
            font-size: 16px !important;
        }
        small {
            font-size: 14px !important;
        }
        .info-box-icon {
            font-size: 40px !important;
        }
        .small-box .icon {
            font-size: 70px !important;
        }
    </style>
    
    <div class="page-header">
        <h1>
            <?= Html::encode($this->title) ?>
            <small class="pull-right">
                <?= Html::a('<i class="fa fa-refresh" style="font-size: 16px;"></i> Actualizar', ['kpi-dashboard'], [
                    'class' => 'btn btn-primary',
                    'style' => 'font-size: 16px; padding: 10px 20px;'
                ]) ?>
            </small>
        </h1>
    </div>

    <div class="row">
        <!-- Pipeline Health Cards -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info">
                    <h3 class="card-title"><i class="fa fa-heartbeat" style="font-size: 20px;"></i> Salud del Pipeline</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fa fa-user-plus"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Nuevos Clientes</span>
                                    <span class="info-box-number"><?= $pipelineData['new_acquisitions'] ?></span>
                                    <div class="progress" style="background: rgba(0,0,0,0.2);">
                                        <div class="progress-bar" style="width: 70%"></div>
                                    </div>
                                    <span class="progress-description" style="font-size: 13px;">
                                        Últimos 30 días
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box bg-primary">
                                <span class="info-box-icon"><i class="fa fa-refresh"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Renovaciones</span>
                                    <span class="info-box-number"><?= $pipelineData['renewals'] ?></span>
                                    <div class="progress" style="background: rgba(0,0,0,0.2);">
                                        <div class="progress-bar" style="width: 70%"></div>
                                    </div>
                                    <span class="progress-description" style="font-size: 13px;">
                                        Clientes existentes
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3><?= $pipelineData['renewal_rate'] ?>%</h3>
                                    <p style="font-size: 16px; font-weight: 600;">Tasa de Renovación</p>
                                </div>
                                <div class="icon"><i class="fa fa-pie-chart"></i></div>
                                <?= Html::a('Ver detalles <i class="fa fa-arrow-circle-right"></i>', ['pipeline-health'], [
                                    'class' => 'small-box-footer',
                                    'style' => 'font-size: 14px;'
                                ]) ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="small-box bg-<?= $pipelineData['revenue_trend'] >= 0 ? 'success' : 'danger' ?>">
                                <div class="inner">
                                    <h3><?= $pipelineData['revenue_trend'] ?>%</h3>
                                    <p style="font-size: 16px; font-weight: 600;">Tendencia de Ingresos</p>
                                </div>
                                <div class="icon"><i class="fa fa-line-chart"></i></div>
                                <?= Html::a('Ver análisis <i class="fa fa-arrow-circle-right"></i>', ['agency-earnings'], [
                                    'class' => 'small-box-footer',
                                    'style' => 'font-size: 14px;'
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Agent Performance -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success">
                    <h3 class="card-title"><i class="fa fa-trophy" style="font-size: 20px;"></i> Top Agentes (Este Mes)</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($performanceData)): ?>
                        <?php foreach (array_slice($performanceData, 0, 5) as $agent): ?>
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3" style="min-height: 60px;">
                                <div>
                                    <strong style="font-size: 16px; display: block;"><?= $agent['agent_name'] ?></strong>
                                    <small class="text-muted" style="font-size: 14px;"><?= $agent['agency_name'] ?></small>
                                </div>
                                <div class="text-right">
                                    <span class="badge badge-primary" style="font-size: 14px; padding: 6px 10px; margin-bottom: 3px;">
                                        $<?= number_format($agent['total_revenue'], 0) ?>
                                    </span>
                                    <br>
                                    <span class="badge badge-<?= $agent['performance_score'] >= 80 ? 'success' : ($agent['performance_score'] >= 60 ? 'warning' : 'danger') ?>" 
                                          style="font-size: 13px; padding: 5px 8px;"
                                          data-toggle="tooltip" 
                                          title="Puntuación de desempeño: <?= $agent['performance_score'] ?>/100">
                                        <?= $agent['performance_score'] ?> pts
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="text-center mt-4">
                            <?= Html::a('<i class="fa fa-list-alt"></i> Ver Reporte Completo', ['agent-performance'], [
                                'class' => 'btn btn-primary',
                                'style' => 'font-size: 16px; padding: 10px 20px;'
                            ]) ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted" style="font-size: 16px; text-align: center; padding: 20px;">
                            No hay datos de agentes disponibles.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title" style="color: white;"><i class="fa fa-rocket" style="font-size: 20px;"></i> Acciones Rápidas</h3>
                </div>
                <div class="card-body text-center">
                    <?= Html::a('<i class="fas fa-users" style="font-size: 20px;"></i> Desempeño de Agentes', ['agent-performance'], [
                        'class' => 'btn btn-info btn-lg m-3',
                        'style' => 'font-size: 18px; padding: 15px 30px; min-width: 250px;'
                    ]) ?>
                    <?= Html::a('<i class="fa fa-heartbeat" style="font-size: 20px;"></i> Salud del Pipeline', ['pipeline-health'], [
                        'class' => 'btn btn-warning btn-lg m-3',
                        'style' => 'font-size: 18px; padding: 15px 30px; min-width: 250px;'
                    ]) ?>
                    <?= Html::a('<i class="fa fa-chart-bar" style="font-size: 20px;"></i> Reportes Financieros', ['agency-earnings'], [
                        'class' => 'btn btn-success btn-lg m-3',
                        'style' => 'font-size: 18px; padding: 15px 30px; min-width: 250px;'
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>