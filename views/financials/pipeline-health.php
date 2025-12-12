<?php
use yii\helpers\Html;

$this->title = 'Salud del Pipeline de Ventas - Últimos 90 Días';
$this->params['breadcrumbs'][] = ['label' => 'Finanzas', 'url' => ['/financials/agency-earnings']];
$this->params['breadcrumbs'][] = $this->title;

$currentDate = date('d/m/Y');
$startDate90 = date('d/m/Y', strtotime('-90 days'));
$startDate30 = date('d/m/Y', strtotime('-30 days'));
$startDate60_90 = date('d/m/Y', strtotime('-90 days'));
$endDate60_90 = date('d/m/Y', strtotime('-31 days'));
?>

<div class="financials-pipeline-health">
    <div class="page-header">
        <h1 style="font-size: 2.5rem; font-weight: 700;">
            <?= Html::encode($this->title) ?>
            <small class="float-right" style="font-size: 1.2rem;">
                <?= Html::a('<i class="fas fa-arrow-left"></i> Volver a Dashboard', ['kpi-dashboard'], ['class' => 'btn btn-default btn-lg']) ?>
            </small>
        </h1>
        <p class="text-muted" style="font-size: 1.2rem; font-weight: 600;">
            Período de análisis: <?= $startDate90 ?> - <?= $currentDate ?>
        </p>
    </div>

    <!-- Base de Clientes Activos and Análisis de Tendencia First -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title mb-0" style="font-size: 1.6rem; font-weight: 700;">
                        <i class="fas fa-users"></i> BASE DE CLIENTES ACTIVOS (90 DÍAS)
                    </h3>
                </div>
                <div class="card-body text-center">
                    <h1 style="font-size: 5rem; font-weight: 900; color: #17a2b8; margin: 20px 0;"><?= $pipeline['total_active_clients'] ?></h1>
                    <p class="text-muted" style="font-size: 1.3rem; font-weight: 600;">CLIENTES CON PAGOS EN LOS ÚLTIMOS 90 DÍAS</p>
                    <small class="text-info" style="font-size: 1rem;">Período: <?= $startDate90 ?> - <?= $currentDate ?></small>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-<?= $pipeline['revenue_trend'] >= 0 ? 'success' : 'danger' ?> text-white">
                    <h3 class="card-title mb-0" style="font-size: 1.6rem; font-weight: 700;">
                        <i class="fas fa-chart-line"></i> ANÁLISIS DE TENDENCIA (30 DÍAS)
                    </h3>
                </div>
                <div class="card-body">
                    <?php if ($pipeline['revenue_trend'] >= 0): ?>
                        <div class="alert alert-success" style="font-size: 1.2rem;">
                            <h4 style="font-size: 1.5rem; font-weight: 700;"><i class="fas fa-arrow-up"></i> TENDENCIA POSITIVA</h4>
                            <p style="font-size: 1.3rem;">Los ingresos han aumentado un <strong style="font-size: 1.4rem;"><?= $pipeline['revenue_trend'] ?>%</strong> 
                            comparado con el período anterior (<?= $startDate60_90 ?> - <?= $endDate60_90 ?>).</p>
                            <p style="font-size: 1.1rem; margin-bottom: 0;">
                                <strong>Ingresos actuales:</strong> $<?= number_format($pipeline['current_period_revenue'], 2) ?> | 
                                <strong>Ingresos anteriores:</strong> $<?= number_format($pipeline['previous_period_revenue'], 2) ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger" style="font-size: 1.2rem;">
                            <h4 style="font-size: 1.5rem; font-weight: 700;"><i class="fas fa-arrow-down"></i> TENDENCIA NEGATIVA</h4>
                            <p style="font-size: 1.3rem;">Los ingresos han disminuido un <strong style="font-size: 1.4rem;"><?= abs($pipeline['revenue_trend']) ?>%</strong> 
                            comparado con el período anterior (<?= $startDate60_90 ?> - <?= $endDate60_90 ?>).</p>
                            <p style="font-size: 1.1rem; margin-bottom: 0;">
                                <strong>Ingresos actuales:</strong> $<?= number_format($pipeline['current_period_revenue'], 2) ?> | 
                                <strong>Ingresos anteriores:</strong> $<?= number_format($pipeline['previous_period_revenue'], 2) ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="progress" style="height: 30px; margin: 20px 0;">
                        <div class="progress-bar bg-<?= $pipeline['renewal_rate'] >= 70 ? 'success' : ($pipeline['renewal_rate'] >= 50 ? 'warning' : 'danger') ?>" 
                             style="width: <?= min($pipeline['renewal_rate'], 100) ?>%; font-size: 1.2rem; font-weight: 700; line-height: 30px;">
                            <?= $pipeline['renewal_rate'] ?>% TASA DE RENOVACIÓN
                        </div>
                    </div>
                    <small class="text-muted" style="font-size: 1.1rem; font-weight: 600;">META RECOMENDADA: >70%</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <!-- Centered Info Icon at Top -->
                    <div class="kpi-tooltip-top">
                        <i class="fas fa-info-circle tooltip-icon" data-toggle="tooltip" 
                           title="Clientes que realizaron su primer pago en los últimos 30 días y no tenían pagos anteriores en los 90 días previos."></i>
                    </div>
                    <h3 style="font-size: 3.5rem; font-weight: 800; margin-bottom: 10px;"><?= $pipeline['new_acquisitions'] ?></h3>
                    <p style="font-size: 1.4rem; font-weight: 600;" class="text-white">NUEVAS ADQUISICIONES</p>
                    <small class="text-white" style="font-size: 1rem;">Últimos 30 días</small>
                </div>
                <div class="icon"><i class="fas fa-user-plus text-white"></i></div>
                <a href="javascript:void(0)" class="small-box-footer toggle-details" data-target="newAcquisitionsDetails" style="font-size: 1.1rem;">
                    VER DETALLES <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <!-- Centered Info Icon at Top -->
                    <div class="kpi-tooltip-top">
                        <i class="fas fa-info-circle tooltip-icon" data-toggle="tooltip" 
                           title="Clientes existentes que han renovado sus servicios en los últimos 30 días y ya tenían pagos anteriores en los 90 días previos."></i>
                    </div>
                    <h3 style="font-size: 3.5rem; font-weight: 800; margin-bottom: 10px;"><?= $pipeline['renewals'] ?></h3>
                    <p style="font-size: 1.4rem; font-weight: 600;" class="text-white">RENOVACIONES</p>
                    <small class="text-white" style="font-size: 1rem;">Últimos 30 días</small>
                </div>
                <div class="icon"><i class="fas fa-sync-alt text-white"></i></div>
                <a href="javascript:void(0)" class="small-box-footer toggle-details" data-target="renewalsDetails" style="font-size: 1.1rem;">
                    VER DETALLES <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <!-- Centered Info Icon at Top -->
                    <div class="kpi-tooltip-top">
                        <i class="fas fa-info-circle tooltip-icon" data-toggle="tooltip" 
                           title="Porcentaje de clientes existentes que renuevan sus servicios comparado con el total de transacciones recientes. Fórmula: (Renovaciones ÷ (Nuevas Adquisiciones + Renovaciones)) × 100"></i>
                    </div>
                    <h3 style="font-size: 3.5rem; font-weight: 800; margin-bottom: 10px;"><?= $pipeline['renewal_rate'] ?>%</h3>
                    <p style="font-size: 1.4rem; font-weight: 600;">TASA DE RENOVACIÓN</p>
                    <small class="text-muted" style="font-size: 1rem;">Últimos 30 días</small>
                </div>
                <div class="icon"><i class="fas fa-chart-pie text-white"></i></div>
                <a href="javascript:void(0)" class="small-box-footer toggle-details" data-target="renewalRateDetails" style="font-size: 1.1rem;">
                    VER DETALLES <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-<?= $pipeline['revenue_trend'] >= 0 ? 'info' : 'danger' ?>">
                <div class="inner">
                    <!-- Centered Info Icon at Top -->
                    <div class="kpi-tooltip-top">
                        <i class="fas fa-info-circle tooltip-icon" data-toggle="tooltip" 
                           title="Mide el cambio porcentual en los ingresos comparando el período actual (últimos 30 días) con el período anterior (30-60 días atrás)."></i>
                    </div>
                    <h3 style="font-size: 3.5rem; font-weight: 800; margin-bottom: 10px;"><?= $pipeline['revenue_trend'] ?>%</h3>
                    <p style="font-size: 1.4rem; font-weight: 600;">TENDENCIA DE INGRESOS</p>
                    <small class="text-white" style="font-size: 1rem;">Comparación 30 días</small>
                </div>
                <div class="icon"><i class="fas fa-chart-line text-white"></i></div>
                <a href="javascript:void(0)" class="small-box-footer toggle-details" data-target="revenueTrendDetails" style="font-size: 1.1rem;">
                    VER DETALLES <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Additional Metrics Row -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="info-box bg-gradient-info">
                <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text" style="font-size: 1.2rem; font-weight: 600;">INGRESO PROMEDIO POR CLIENTE</span>
                    <span class="info-box-number" style="font-size: 2rem; font-weight: 700;">$<?= number_format($pipeline['avg_revenue_per_client'], 2) ?></span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <span class="progress-description" style="font-size: 1rem;">Últimos 30 días</span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="info-box bg-gradient-success">
                <span class="info-box-icon"><i class="fas fa-chart-bar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text" style="font-size: 1.2rem; font-weight: 600;">CLIENTES DE ALTA ACTIVIDAD</span>
                    <span class="info-box-number" style="font-size: 2rem; font-weight: 700;"><?= $pipeline['client_activity_distribution']['high'] ?></span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <span class="progress-description" style="font-size: 1rem;">3+ pagos en 90 días</span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="info-box bg-gradient-warning">
                <span class="info-box-icon"><i class="fas fa-chart-pie"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text" style="font-size: 1.2rem; font-weight: 600;">DISTRIBUCIÓN DE ACTIVIDAD</span>
                    <span class="info-box-number" style="font-size: 1.8rem; font-weight: 700;">
                        Alta: <?= $pipeline['client_activity_distribution']['high'] ?> | 
                        Media: <?= $pipeline['client_activity_distribution']['medium'] ?> | 
                        Baja: <?= $pipeline['client_activity_distribution']['low'] ?>
                    </span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <span class="progress-description" style="font-size: 1rem;">Últimos 90 días</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Sections -->
    <div class="details-section mt-4">
        <!-- New Acquisitions Details -->
        <div class="card details-card" id="newAcquisitionsDetails" style="display: none;">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0" style="font-size: 1.8rem; font-weight: 700;">
                    <i class="fas fa-user-plus"></i> DETALLES - NUEVAS ADQUISICIONES
                </h4>
                <button type="button" class="btn btn-warning btn-close-details" data-target="newAcquisitionsDetails" style="font-weight: 700;">
                    CERRAR
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fas fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text" style="font-size: 1.3rem; font-weight: 600;">TOTAL NUEVAS ADQUISICIONES</span>
                                <span class="info-box-number" style="font-size: 2.5rem; font-weight: 800;"><?= $pipeline['new_acquisitions'] ?></span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <span class="progress-description" style="font-size: 1.1rem;">ÚLTIMOS 30 DÍAS</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="fas fa-calendar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text" style="font-size: 1.3rem; font-weight: 600;">PERÍODO</span>
                                <span class="info-box-number" style="font-size: 2.5rem; font-weight: 800;">30 DÍAS</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <span class="progress-description" style="font-size: 1.1rem;">DESDE <?= $startDate30 ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info mt-3" style="font-size: 1.1rem;">
                    <strong>Definición:</strong> Clientes que realizaron su primer pago en los últimos 30 días y no tenían pagos anteriores en los 90 días previos.
                </div>
            </div>
        </div>

        <!-- Renewals Details -->
        <div class="card details-card" id="renewalsDetails" style="display: none;">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0" style="font-size: 1.8rem; font-weight: 700;">
                    <i class="fas fa-sync-alt"></i> DETALLES - RENOVACIONES
                </h4>
                <button type="button" class="btn btn-warning btn-close-details" data-target="renewalsDetails" style="font-weight: 700;">
                    CERRAR
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box bg-primary">
                            <span class="info-box-icon"><i class="fas fa-sync"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text" style="font-size: 1.3rem; font-weight: 600;">TOTAL RENOVACIONES</span>
                                <span class="info-box-number" style="font-size: 2.5rem; font-weight: 800;"><?= $pipeline['renewals'] ?></span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <span class="progress-description" style="font-size: 1.1rem;">ÚLTIMOS 30 DÍAS</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="fas fa-calendar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text" style="font-size: 1.3rem; font-weight: 600;">PERÍODO</span>
                                <span class="info-box-number" style="font-size: 2.5rem; font-weight: 800;">30 DÍAS</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <span class="progress-description" style="font-size: 1.1rem;">DESDE <?= $startDate30 ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info mt-3" style="font-size: 1.1rem;">
                    <strong>Definición:</strong> Clientes existentes que han renovado sus servicios en los últimos 30 días y ya tenían pagos anteriores en los 90 días previos.
                </div>
            </div>
        </div>

        <!-- Renewal Rate Details -->
        <div class="card details-card" id="renewalRateDetails" style="display: none;">
            <div class="card-header bg-warning d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0" style="font-size: 1.8rem; font-weight: 700;">
                    <i class="fas fa-chart-pie"></i> DETALLES - TASA DE RENOVACIÓN
                </h4>
                <button type="button" class="btn btn-primary btn-close-details" data-target="renewalRateDetails" style="font-weight: 700;">
                    CERRAR
                </button>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <h1 style="font-size: 5rem; font-weight: 900; color: #ffc107; margin-bottom: 20px;"><?= $pipeline['renewal_rate'] ?>%</h1>
                    <p class="lead" style="font-size: 1.8rem; font-weight: 700;">TASA DE RENOVACIÓN</p>
                </div>
                
                <div class="progress" style="height: 40px; margin: 30px 0;">
                    <div class="progress-bar bg-<?= $pipeline['renewal_rate'] >= 70 ? 'success' : ($pipeline['renewal_rate'] >= 50 ? 'warning' : 'danger') ?>" 
                         style="width: <?= min($pipeline['renewal_rate'], 100) ?>%; font-size: 1.3rem; font-weight: 700; line-height: 40px;">
                        <?= $pipeline['renewal_rate'] ?>%
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="alert alert-success" style="font-size: 1.2rem;">
                            <strong style="font-size: 1.3rem;">META RECOMENDADA:</strong> >70%
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-info" style="font-size: 1.2rem;">
                            <strong style="font-size: 1.3rem;">ESTADO ACTUAL:</strong> 
                            <?= $pipeline['renewal_rate'] >= 70 ? 'EXCELENTE' : ($pipeline['renewal_rate'] >= 50 ? 'ACEPTABLE' : 'NECESITA MEJORA') ?>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info mt-3" style="font-size: 1.1rem;">
                    <strong>Fórmula:</strong> (Renovaciones ÷ (Nuevas Adquisiciones + Renovaciones)) × 100<br>
                    <strong>Cálculo:</strong> (<?= $pipeline['renewals'] ?> ÷ (<?= $pipeline['new_acquisitions'] ?> + <?= $pipeline['renewals'] ?>)) × 100 = <?= $pipeline['renewal_rate'] ?>%
                </div>
            </div>
        </div>

        <!-- Revenue Trend Details -->
        <div class="card details-card" id="revenueTrendDetails" style="display: none;">
            <div class="card-header bg-<?= $pipeline['revenue_trend'] >= 0 ? 'info' : 'danger' ?> text-white d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0" style="font-size: 1.8rem; font-weight: 700;">
                    <i class="fas fa-chart-line"></i> DETALLES - TENDENCIA DE INGRESOS
                </h4>
                <button type="button" class="btn btn-warning btn-close-details" data-target="revenueTrendDetails" style="font-weight: 700;">
                    CERRAR
                </button>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <h1 style="font-size: 5rem; font-weight: 900; color: <?= $pipeline['revenue_trend'] >= 0 ? '#17a2b8' : '#dc3545' ?>; margin-bottom: 20px;">
                        <?= $pipeline['revenue_trend'] ?>%
                    </h1>
                    <p class="lead" style="font-size: 1.8rem; font-weight: 700;">TENDENCIA DE INGRESOS</p>
                </div>
                
                <?php if ($pipeline['revenue_trend'] >= 0): ?>
                    <div class="alert alert-success" style="font-size: 1.2rem;">
                        <h4 style="font-size: 1.6rem; font-weight: 700;"><i class="fas fa-arrow-up"></i> TENDENCIA POSITIVA</h4>
                        <p style="font-size: 1.3rem;">Los ingresos han aumentado un <strong style="font-size: 1.4rem;"><?= $pipeline['revenue_trend'] ?>%</strong> 
                        comparado con el período anterior.</p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger" style="font-size: 1.2rem;">
                        <h4 style="font-size: 1.6rem; font-weight: 700;"><i class="fas fa-arrow-down"></i> TENDENCIA NEGATIVA</h4>
                        <p style="font-size: 1.3rem;">Los ingresos han disminuido un <strong style="font-size: 1.4rem;"><?= abs($pipeline['revenue_trend']) ?>%</strong> 
                        comparado con el período anterior.</p>
                    </div>
                <?php endif; ?>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text" style="font-size: 1.2rem; font-weight: 600;">PERÍODO ACTUAL</span>
                                <span class="info-box-number" style="font-size: 2rem; font-weight: 700;">$<?= number_format($pipeline['current_period_revenue'], 2) ?></span>
                                <span class="progress-description" style="font-size: 1rem;"><?= $startDate30 ?> - <?= $currentDate ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-secondary">
                            <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text" style="font-size: 1.2rem; font-weight: 600;">PERÍODO ANTERIOR</span>
                                <span class="info-box-number" style="font-size: 2rem; font-weight: 700;">$<?= number_format($pipeline['previous_period_revenue'], 2) ?></span>
                                <span class="progress-description" style="font-size: 1rem;"><?= $startDate60_90 ?> - <?= $endDate60_90 ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info mt-3" style="font-size: 1.1rem;">
                    <strong>Fórmula:</strong> ((Ingresos Actuales - Ingresos Anteriores) ÷ Ingresos Anteriores) × 100<br>
                    <strong>Cálculo:</strong> (($<?= number_format($pipeline['current_period_revenue'], 2) ?> - $<?= number_format($pipeline['previous_period_revenue'], 2) ?>) ÷ $<?= number_format($pipeline['previous_period_revenue'], 2) ?>) × 100 = <?= $pipeline['revenue_trend'] ?>%
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.details-card {
    border: 3px solid #007bff;
    margin-bottom: 20px;
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    border-radius: 10px;
}

.toggle-details {
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
}

.toggle-details:hover {
    opacity: 0.8;
    transform: translateY(-2px);
}

.small-box-footer {
    cursor: pointer !important;
    font-weight: 600;
}

.small-box .inner h3 {
    font-size: 3.5rem !important;
    font-weight: 800 !important;
}

.small-box .inner p {
    font-size: 1.4rem !important;
    font-weight: 600 !important;
}

.info-box-text {
    font-size: 1.3rem !important;
    font-weight: 600 !important;
}

.info-box-number {
    font-size: 2.5rem !important;
    font-weight: 800 !important;
}

.progress-description {
    font-size: 1.1rem !important;
    font-weight: 600 !important;
}

.alert h4, .alert h5 {
    font-weight: 700 !important;
}

.alert p {
    font-size: 1.2rem !important;
}

/* Centered Top Tooltip Styles */
.kpi-tooltip-top {
    position: absolute;
    top: 10px;
    left: 0;
    right: 0;
    text-align: center;
    z-index: 10;
}

.tooltip-icon {
    font-size: 1.3rem;
    color: rgba(255, 255, 255, 0.8);
    cursor: help;
    transition: all 0.3s ease;
    background: rgba(0, 0, 0, 0.2);
    border-radius: 50%;
    padding: 5px;
    width: 30px;
    height: 30px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.tooltip-icon:hover {
    color: #ffffff;
    background: rgba(0, 0, 0, 0.4);
    transform: scale(1.1);
}

/* Professional Close Button Styles */
.btn-close-details {
    font-size: 1rem;
    font-weight: 700;
    padding: 10px 20px;
    border-radius: 8px;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.btn-close-details:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}

/* Custom tooltip styling */
.tooltip-inner {
    background-color: #333;
    color: #fff;
    font-size: 1rem;
    padding: 10px 15px;
    border-radius: 6px;
    max-width: 300px;
}

.tooltip.bs-tooltip-top .arrow::before {
    border-top-color: #333;
}

.tooltip.bs-tooltip-bottom .arrow::before {
    border-bottom-color: #333;
}

.tooltip.bs-tooltip-left .arrow::before {
    border-left-color: #333;
}

.tooltip.bs-tooltip-right .arrow::before {
    border-right-color: #333;
}

/* Card header alignment */
.card-header {
    padding: 15px 20px;
}

/* Adjust small-box inner padding for centered icon */
.small-box .inner {
    position: relative;
    padding-top: 20px;
}

/* Gradient backgrounds for info boxes */
.bg-gradient-info {
    background: linear-gradient(45deg, #17a2b8, #6f42c1) !important;
    color: white;
}

.bg-gradient-success {
    background: linear-gradient(45deg, #28a745, #20c997) !important;
    color: white;
}

.bg-gradient-warning {
    background: linear-gradient(45deg, #ffc107, #fd7e14) !important;
    color: white;
}
</style>

<script>
// Simple JavaScript for toggling details - no jQuery dependency
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Details toggler initialized');
    
    // Add click event to all toggle buttons
    document.querySelectorAll('.toggle-details, .btn-close-details').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var targetId = this.getAttribute('data-target');
            var targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                // Hide all other detail cards
                document.querySelectorAll('.details-card').forEach(function(card) {
                    if (card.id !== targetId) {
                        card.style.display = 'none';
                    }
                });
                
                // Toggle the clicked card
                if (targetElement.style.display === 'none' || targetElement.style.display === '') {
                    targetElement.style.display = 'block';
                    // Scroll to the card
                    targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    targetElement.style.display = 'none';
                }
                
                console.log('Toggled:', targetId);
            }
        });
    });
    
    // Close details when clicking outside (optional)
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.details-card') && !e.target.closest('.toggle-details') && !e.target.closest('.btn-close-details')) {
            document.querySelectorAll('.details-card').forEach(function(card) {
                card.style.display = 'none';
            });
        }
    });

    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            placement: 'top',
            trigger: 'hover'
        });
    });
});
</script>