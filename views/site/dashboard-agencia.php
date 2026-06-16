<?php
/* @var $this yii\web\View */
/* @var $agenciaName string */
/* @var $agenciaStats array */
/* @var $asesoresStats array */
/* @var $statsByAsesor array */
/* @var $monthlyData array */
/* @var $topAsesores array */
/* @var $recientes array */
/* @var $agencias array */
/* @var $debugInfo array */

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\UserHelper;

$this->title = 'Dashboard Agencia - SISPSA';
$this->params['breadcrumbs'][] = 'Dashboard Agencia';

// Register Chart.js
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

// Set default values for empty data
$agenciaStats = $agenciaStats ?? [
    'total_afiliados' => 0,
    'activos' => 0,
    'suspendidos' => 0,
    'vencidos' => 0,
    'registrados' => 0,
    'nuevos_este_mes' => 0,
    'tasa_actividad' => 0,
    'total_asesores' => 0,
    'asesores_activos' => 0,
    'comision_estimada_mensual' => 0,
    'comision_total_acumulada' => 0,
];

$asesoresStats = $asesoresStats ?? ['total_asesores' => 0, 'asesores_activos' => 0];
$statsByAsesor = $statsByAsesor ?? [];
$recientes = $recientes ?? [];
$monthlyData = $monthlyData ?? [];
$topAsesores = $topAsesores ?? [];
$agencias = $agencias ?? [];
?>

<div class="dashboard-agencia">

    <!-- Welcome Banner -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="ms-welcome-banner">
                <div class="ms-welcome-content">
                    <h1 class="ms-welcome-title">Buenos días, <?= Html::encode($agenciaName ?? 'Agencia') ?></h1>
                    <p class="ms-welcome-subtitle">Panel de control de gestión de fuerza de ventas y afiliados</p>
                </div>
                <div class="ms-welcome-date">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    <?= Yii::$app->formatter->asDate(date('Y-m-d'), 'EEEE, d MMMM Y') ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main KPI Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="ms-card ms-card-kpi">
                <div class="ms-card-kpi-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="ms-card-kpi-content">
                    <div class="ms-card-kpi-value"><?= number_format($agenciaStats['total_afiliados'] ?? 0) ?></div>
                    <div class="ms-card-kpi-label">Total Afiliados</div>
                    <div class="ms-card-kpi-trend">
                        <i class="fas fa-chart-line"></i>
                        <span>+<?= number_format($agenciaStats['nuevos_este_mes'] ?? 0) ?> este mes</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="ms-card ms-card-kpi">
                <div class="ms-card-kpi-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="ms-card-kpi-content">
                    <div class="ms-card-kpi-value"><?= number_format($agenciaStats['activos'] ?? 0) ?></div>
                    <div class="ms-card-kpi-label">Contratos Activos</div>
                    <div class="ms-card-kpi-trend">
                        <i class="fas fa-percent"></i>
                        <span><?= ($agenciaStats['tasa_actividad'] ?? 0) ?>% del total</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="ms-card ms-card-kpi">
                <div class="ms-card-kpi-icon orange">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="ms-card-kpi-content">
                    <div class="ms-card-kpi-value"><?= number_format($agenciaStats['total_asesores'] ?? 0) ?></div>
                    <div class="ms-card-kpi-label">Asesores Activos</div>
                    <div class="ms-card-kpi-trend">
                        <i class="fas fa-bullhorn"></i>
                        <span>Fuerza de ventas</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="ms-card ms-card-kpi">
                <div class="ms-card-kpi-icon teal">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="ms-card-kpi-content">
                    <div class="ms-card-kpi-value">$<?= number_format($agenciaStats['comision_estimada_mensual'] ?? 0, 2) ?></div>
                    <div class="ms-card-kpi-label">Comisión Mensual</div>
                    <div class="ms-card-kpi-trend">
                        <i class="fas fa-chart-bar"></i>
                        <span>Total: $<?= number_format($agenciaStats['comision_total_acumulada'] ?? 0, 2) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Metrics Row -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="ms-metric-card">
                <div class="ms-metric-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="ms-metric-info">
                    <div class="ms-metric-value"><?= ($agenciaStats['tasa_actividad'] ?? 0) ?>%</div>
                    <div class="ms-metric-label">Tasa de Actividad</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="ms-metric-card">
                <div class="ms-metric-icon">
                    <i class="fas fa-pause-circle"></i>
                </div>
                <div class="ms-metric-info">
                    <div class="ms-metric-value"><?= number_format($agenciaStats['suspendidos'] ?? 0) ?></div>
                    <div class="ms-metric-label">Contratos Suspendidos</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="ms-metric-card">
                <div class="ms-metric-icon">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <div class="ms-metric-info">
                    <div class="ms-metric-value"><?= number_format($agenciaStats['vencidos'] ?? 0) ?></div>
                    <div class="ms-metric-label">Contratos Vencidos</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="ms-metric-card">
                <div class="ms-metric-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="ms-metric-info">
                    <div class="ms-metric-value"><?= number_format($agenciaStats['nuevos_este_mes'] ?? 0) ?></div>
                    <div class="ms-metric-label">Nuevos Afiliados</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="ms-card">
                <div class="ms-card-header">
                    <div class="ms-card-header-title">Afiliados por Asesor</div>
                    <div class="ms-card-header-subtitle">Distribución de afiliados por intermediario</div>
                </div>
                <div class="ms-card-body">
                    <canvas id="asesoresChart" style="height: 320px; width: 100%;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="ms-card">
                <div class="ms-card-header">
                    <div class="ms-card-header-title">Crecimiento Mensual</div>
                    <div class="ms-card-header-subtitle">Afiliados nuevos por mes</div>
                </div>
                <div class="ms-card-body">
                    <canvas id="monthlyChart" style="height: 320px; width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Asesores Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="ms-card">
                <div class="ms-card-header">
                    <div class="ms-card-header-title">Top Asesores por Rendimiento</div>
                    <div class="ms-card-header-subtitle">Los 5 intermediarios con mejor desempeño</div>
                </div>
                <div class="ms-card-body p-0">
                    <div class="ms-table-responsive">
                        <table class="ms-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px">#</th>
                                    <th>Asesor / Intermediario</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Activos</th>
                                    <th class="text-center">Tasa Actividad</th>
                                    <th class="text-center">Comisión Estimada</th>
                                    <th class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($topAsesores)): ?>
                                    <?php $counter = 1; ?>
                                    <?php foreach ($topAsesores as $asesor): ?>
                                        <tr class="ranking-<?= $counter ?>">
                                            <td class="text-center">
                                                <?php if ($counter == 1): ?>
                                                    <span class="ranking-badge gold"><i class="fas fa-trophy"></i></span>
                                                <?php elseif ($counter == 2): ?>
                                                    <span class="ranking-badge silver"><i class="fas fa-medal"></i></span>
                                                <?php elseif ($counter == 3): ?>
                                                    <span class="ranking-badge bronze"><i class="fas fa-medal"></i></span>
                                                <?php else: ?>
                                                    <span class="ranking-number"><?= $counter ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="font-medium">
                                                <i class="fas fa-user-circle text-blue mr-2"></i>
                                                <?= Html::encode($asesor['nombre_completo'] ?? 'N/A') ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="ms-badge ms-badge-gray"><?= number_format($asesor['total'] ?? 0) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="ms-badge ms-badge-green"><?= number_format($asesor['activos'] ?? 0) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <?php $porcentaje = ($asesor['total'] ?? 0) > 0 ? round((($asesor['activos'] ?? 0) / ($asesor['total'] ?? 1)) * 100, 1) : 0; ?>
                                                <div class="ms-progress" style="width: 120px; margin: 0 auto;">
                                                    <div class="ms-progress-bar" style="width: <?= $porcentaje ?>%;">
                                                        <span class="ms-progress-label"><?= $porcentaje ?>%</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="ms-badge ms-badge-teal">$<?= number_format($asesor['comision_estimada'] ?? 0, 2) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= Url::to(['/user-datos/index-by-afiliado', 'asesor_id' => $asesor['id'] ?? 0]) ?>"
                                                    class="ms-icon-btn"
                                                    target="_blank"
                                                    title="Ver afiliados de este asesor">
                                                    <i class="fas fa-arrow-right"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php $counter++; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr class="ms-table-empty">
                                        <td colspan="7">
                                            <div class="ms-empty-state">
                                                <i class="fas fa-chart-line"></i>
                                                <p>No hay datos de asesores disponibles</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Progress Cards -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="ms-card">
                <div class="ms-card-header">
                    <div class="ms-card-header-title">Meta de Afiliados 2026</div>
                    <div class="ms-card-header-subtitle">Objetivo anual: <?= number_format($agenciaStats['meta_anual'] ?? 500) ?> afiliados</div>
                </div>
                <div class="ms-card-body">
                    <?php $metaPorcentaje = min(100, round((($agenciaStats['total_afiliados'] ?? 0) / ($agenciaStats['meta_anual'] ?? 500)) * 100, 1)); ?>
                    <div class="ms-progress-large mb-2">
                        <div class="ms-progress-large-bar" style="width: <?= $metaPorcentaje ?>%;">
                            <span class="ms-progress-large-label"><?= $metaPorcentaje ?>%</span>
                        </div>
                    </div>
                    <div class="ms-meta-info">
                        <span><i class="fas fa-bullseye text-blue"></i> Actual: <?= number_format($agenciaStats['total_afiliados'] ?? 0) ?></span>
                        <span><i class="fas fa-flag-checkered text-green"></i> Meta: <?= number_format($agenciaStats['meta_anual'] ?? 500) ?></span>
                        <span><i class="fas fa-chart-line text-teal"></i> Restan: <?= number_format(max(0, ($agenciaStats['meta_anual'] ?? 500) - ($agenciaStats['total_afiliados'] ?? 0))) ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="ms-card">
                <div class="ms-card-header">
                    <div class="ms-card-header-title">Tasa de Actividad General</div>
                    <div class="ms-card-header-subtitle">Afiliados con contratos activos</div>
                </div>
                <div class="ms-card-body">
                    <div class="ms-progress-large mb-2">
                        <div class="ms-progress-large-bar teal" style="width: <?= ($agenciaStats['tasa_actividad'] ?? 0) ?>%;">
                            <span class="ms-progress-large-label"><?= ($agenciaStats['tasa_actividad'] ?? 0) ?>%</span>
                        </div>
                    </div>
                    <div class="ms-meta-info">
                        <span><i class="fas fa-check-circle text-green"></i> Activos: <?= number_format($agenciaStats['activos'] ?? 0) ?></span>
                        <span><i class="fas fa-pause-circle text-orange"></i> Suspendidos: <?= number_format($agenciaStats['suspendidos'] ?? 0) ?></span>
                        <span><i class="fas fa-calendar-times text-red"></i> Vencidos: <?= number_format($agenciaStats['vencidos'] ?? 0) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Asesores Performance Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="ms-card">
                <div class="ms-card-header">
                    <div class="ms-card-header-title">Rendimiento Detallado por Asesor</div>
                    <div class="ms-card-header-subtitle">Listado completo de intermediarios y sus métricas</div>
                </div>
                <div class="ms-card-body p-0">
                    <div class="ms-table-responsive">
                        <table class="ms-table">
                            <thead>
                                <tr>
                                    <th>Asesor / Intermediario</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Activos</th>
                                    <th class="text-center">Suspendidos</th>
                                    <th class="text-center">Actividad</th>
                                    <th class="text-center">Comisión</th>
                                    <th class="text-center">Último Registro</th>
                                    <th class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($statsByAsesor)): ?>
                                    <?php foreach ($statsByAsesor as $asesor): ?>
                                        <tr>
                                            <td class="font-medium">
                                                <i class="fas fa-user-circle text-blue mr-2"></i>
                                                <?= Html::encode($asesor['nombre_completo'] ?? 'N/A') ?>
                                                <div class="text-muted small">
                                                    <i class="fas fa-id-card"></i> Código: <?= Html::encode($asesor['codigo_asesor'] ?? 'N/A') ?>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="ms-badge ms-badge-gray"><?= number_format($asesor['total'] ?? 0) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="ms-badge ms-badge-green"><?= number_format($asesor['activos'] ?? 0) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="ms-badge ms-badge-orange"><?= number_format($asesor['suspendidos'] ?? 0) ?></span>
                                            </td>
                                            <td class="text-center" style="width: 180px;">
                                                <?php $porcentaje = ($asesor['total'] ?? 0) > 0 ? round((($asesor['activos'] ?? 0) / ($asesor['total'] ?? 1)) * 100, 1) : 0; ?>
                                                <div class="ms-progress">
                                                    <div class="ms-progress-bar" style="width: <?= $porcentaje ?>%;">
                                                        <span class="ms-progress-label"><?= $porcentaje ?>%</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="ms-badge ms-badge-teal">$<?= number_format($asesor['comision_estimada'] ?? 0, 2) ?></span>
                                            </td>
                                            <td class="text-muted small text-center">
                                                <?= Yii::$app->formatter->asDate($asesor['ultimo_registro'] ?? null, 'dd/MM/yyyy') ?? 'N/A' ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="ms-action-buttons">
                                                    <a href="<?= Url::to(['/user-datos/index-by-afiliado', 'asesor_id' => $asesor['id'] ?? 0]) ?>"
                                                        class="ms-icon-btn sm"
                                                        target="_blank"
                                                        title="Ver afiliados">
                                                        <i class="fas fa-users"></i>
                                                    </a>
                                                    <a href="<?= Url::to(['/user/index', 'idasesor' => $asesor['user_id'] ?? 0]) ?>"
                                                        class="ms-icon-btn sm"
                                                        title="Ver perfil">
                                                        <i class="fas fa-user"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr class="ms-table-empty">
                                        <td colspan="8">
                                            <div class="ms-empty-state">
                                                <i class="fas fa-user-friends"></i>
                                                <p>No hay asesores registrados en esta agencia</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Affiliates Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="ms-card">
                <div class="ms-card-header">
                    <div class="ms-card-header-title">Afiliados Recientes</div>
                    <div class="ms-card-header-subtitle">Últimos 10 afiliados registrados en la agencia</div>
                </div>
                <div class="ms-card-body p-0">
                    <div class="ms-table-responsive">
                        <table class="ms-table">
                            <thead>
                                <tr>
                                    <th style="width: 40px">#</th>
                                    <th>Nombre</th>
                                    <th>Documento</th>
                                    <th>Asesor</th>
                                    <th>Clínica</th>
                                    <th>Estatus Contrato</th>
                                    <th>Registro</th>
                                    <th style="width: 80px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recientes)): ?>
                                    <?php $counter = 1; ?>
                                    <?php foreach ($recientes as $afiliado): ?>
                                        <tr>
                                            <td class="text-center text-muted"><?= $counter++ ?></td>
                                            <td class="font-medium">
                                                <?= Html::encode(($afiliado['nombres'] ?? '') . ' ' . ($afiliado['apellidos'] ?? '')) ?>
                                            </td>
                                            <td>
                                                <?php
                                                $cedulaCompleta = ($afiliado['tipo_cedula'] ?? '') . '-' . ($afiliado['cedula'] ?? '');
                                                echo Html::encode($cedulaCompleta);
                                                ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($afiliado['asesor_nombre'])): ?>
                                                    <span class="ms-badge ms-badge-light">
                                                        <i class="fas fa-user-tie"></i> <?= Html::encode($afiliado['asesor_nombre']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">
                                                        <i class="fas fa-user"></i> Sin asignar
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="ms-badge ms-badge-light"><?= Html::encode($afiliado['clinica_nombre'] ?? 'No asignada') ?></span>
                                            </td>
                                            <td>
                                                <?php
                                                $estatusContrato = $afiliado['contrato_estatus'] ?? 'Sin Contrato';
                                                $badgeClass = '';
                                                if ($estatusContrato == 'Activo') $badgeClass = 'ms-badge-green';
                                                elseif ($estatusContrato == 'Registrado') $badgeClass = 'ms-badge-blue';
                                                elseif ($estatusContrato == 'Suspendido') $badgeClass = 'ms-badge-orange';
                                                elseif ($estatusContrato == 'Vencido') $badgeClass = 'ms-badge-red';
                                                else $badgeClass = 'ms-badge-gray';
                                                ?>
                                                <span class="ms-badge <?= $badgeClass ?>"><?= Html::encode($estatusContrato) ?></span>
                                            </td>
                                            <td class="text-muted small"><?= Yii::$app->formatter->asDate($afiliado['created_at'] ?? null, 'dd/MM/yyyy') ?></td>
                                            <td>
                                                <div class="ms-action-buttons">
                                                    <a href="<?= Url::to(['/user-datos/view', 'id' => $afiliado['id'] ?? 0]) ?>" class="ms-icon-btn sm" title="Ver">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?= Url::to(['/user-datos/update', 'id' => $afiliado['id'] ?? 0]) ?>" class="ms-icon-btn sm" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr class="ms-table-empty">
                                        <td colspan="8">
                                            <div class="ms-empty-state">
                                                <i class="fas fa-user-slash"></i>
                                                <p>No hay afiliados registrados en esta agencia</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="ms-card-footer">
                    <a href="<?= Url::to(['/user-datos/index-by-agente', 'agente_id' => $agenciaStats['agencia_id'] ?? null]) ?>" class="ms-link">
                        Ver todos los afiliados de la agencia <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Section -->
    <div class="row">
        <div class="col-12">
            <div class="ms-card">
                <div class="ms-card-header">
                    <div class="ms-card-header-title">Acciones Rápidas</div>
                    <div class="ms-card-header-subtitle">Gestione su fuerza de ventas</div>
                </div>
                <div class="ms-card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="<?= Url::to(['/user/create', 'role' => 'Asesor']) ?>" class="ms-action-card">
                                <div class="ms-action-icon blue">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div class="ms-action-text">Registrar Nuevo Asesor</div>
                                <div class="ms-action-desc">Añadir un intermediario a su red</div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="<?= Url::to(['/agente-fuerza/index-by-agente', 'agente_id' => $agenciaStats['agencia_id'] ?? null]) ?>" class="ms-action-card">
                                <div class="ms-action-icon green">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="ms-action-text">Ver Fuerza de Ventas</div>
                                <div class="ms-action-desc">Gestionar porcentajes y permisos</div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="<?= Url::to(['/user-datos/create']) ?>" class="ms-action-card">
                                <div class="ms-action-icon orange">
                                    <i class="fas fa-file-medical"></i>
                                </div>
                                <div class="ms-action-text">Registrar Afiliado</div>
                                <div class="ms-action-desc">Crear un nuevo afiliado en el sistema</div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="<?= Url::to(['/user-datos/index-by-agente', 'agente_id' => $agenciaStats['agencia_id'] ?? null]) ?>" class="ms-action-card">
                                <div class="ms-action-icon teal">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="ms-action-text">Ver Todos los Afiliados</div>
                                <div class="ms-action-desc">Listado completo de afiliados de la agencia</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* ============================================
       MICROSOFT FLUENT DESIGN SYSTEM - AGENCIA DASHBOARD
       ============================================ */

    :root {
        --ms-blue: #0078d4;
        --ms-blue-light: #e6f2fb;
        --ms-green: #107c10;
        --ms-green-light: #e8f5e9;
        --ms-orange: #ff8c00;
        --ms-orange-light: #fff4e6;
        --ms-red: #d13438;
        --ms-red-light: #fde7e9;
        --ms-teal: #00a1ab;
        --ms-teal-light: #e6f7f8;
        --ms-gray: #605e5c;
        --ms-gray-light: #f3f2f1;
        --ms-gray-border: #edebe9;
        --ms-white: #ffffff;
        --ms-shadow: 0 2px 4px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.03);
        --ms-shadow-hover: 0 8px 16px rgba(0, 0, 0, 0.08);
    }

    /* Welcome Banner */
    .ms-welcome-banner {
        background: linear-gradient(135deg, #1a3a6e 0%, #2a5298 100%);
        border-radius: 8px;
        padding: 24px 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        color: white;
    }

    .ms-welcome-title {
        font-size: 24px;
        font-weight: 600;
        margin: 0 0 4px 0;
    }

    .ms-welcome-subtitle {
        font-size: 14px;
        opacity: 0.8;
        margin: 0;
        color: white;
    }

    .ms-welcome-date {
        font-size: 14px;
        opacity: 0.85;
        background: rgba(255, 255, 255, 0.15);
        padding: 8px 16px;
        border-radius: 20px;
    }

    /* KPI Cards */
    .ms-card {
        background: var(--ms-white);
        border-radius: 8px;
        box-shadow: var(--ms-shadow);
        border: 1px solid var(--ms-gray-border);
        overflow: hidden;
        transition: box-shadow 0.2s;
    }

    .ms-card:hover {
        box-shadow: var(--ms-shadow-hover);
    }

    .ms-card-kpi {
        display: flex;
        align-items: center;
        padding: 20px;
    }

    .ms-card-kpi-icon {
        width: 52px;
        height: 52px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 16px;
        font-size: 24px;
        color: white;
    }

    .ms-card-kpi-icon.blue {
        background: var(--ms-blue);
    }

    .ms-card-kpi-icon.green {
        background: var(--ms-green);
    }

    .ms-card-kpi-icon.orange {
        background: var(--ms-orange);
    }

    .ms-card-kpi-icon.red {
        background: var(--ms-red);
    }

    .ms-card-kpi-icon.teal {
        background: var(--ms-teal);
    }

    .ms-card-kpi-content {
        flex: 1;
    }

    .ms-card-kpi-value {
        font-size: 32px;
        font-weight: 600;
        color: #323130;
        line-height: 1.2;
    }

    .ms-card-kpi-label {
        font-size: 14px;
        color: var(--ms-gray);
        margin-top: 4px;
    }

    .ms-card-kpi-trend {
        font-size: 12px;
        color: var(--ms-gray);
        margin-top: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Card Header */
    .ms-card-header {
        padding: 20px 24px 12px 24px;
        border-bottom: 1px solid var(--ms-gray-border);
        background-color: #00E3E2;
    }

    .ms-card-header-title {
        font-size: 18px;
        font-weight: 600;
        color: #323130;
    }

    .ms-card-header-subtitle {
        font-size: 13px;
        color: var(--ms-gray);
        margin-top: 4px;
    }

    .ms-card-body {
        padding: 20px 24px;
    }

    .ms-card-footer {
        padding: 12px 24px;
        border-top: 1px solid var(--ms-gray-border);
        background: var(--ms-gray-light);
    }

    /* Metric Cards */
    .ms-metric-card {
        background: var(--ms-gray-light);
        border-radius: 8px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .ms-metric-icon {
        width: 48px;
        height: 48px;
        background: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: var(--ms-blue);
    }

    .ms-metric-info {
        flex: 1;
    }

    .ms-metric-value {
        font-size: 24px;
        font-weight: 600;
        color: #323130;
    }

    .ms-metric-label {
        font-size: 13px;
        color: var(--ms-gray);
    }

    /* Tables */
    .ms-table-responsive {
        overflow-x: auto;
    }

    .ms-table {
        width: 100%;
        border-collapse: collapse;
    }

    .ms-table th {
        text-align: left;
        padding: 12px 16px;
        background: var(--ms-gray-light);
        font-weight: 600;
        font-size: 13px;
        color: var(--ms-gray);
        border-bottom: 1px solid var(--ms-gray-border);
        background-color: #007BFF;
        color: white;
    }

    .ms-table td {
        padding: 12px 16px;
        border-bottom: 1px solid var(--ms-gray-border);
        font-size: 14px;
    }

    .ms-table tbody tr:hover {
        background: var(--ms-gray-light);
    }

    .ms-table-empty td {
        text-align: center;
        padding: 48px;
    }

    /* Badges */
    .ms-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 16px;
        font-size: 12px;
        font-weight: 500;
    }

    .ms-badge-blue {
        background: var(--ms-blue-light);
        color: var(--ms-blue);
    }

    .ms-badge-green {
        background: var(--ms-green-light);
        color: var(--ms-green);
    }

    .ms-badge-orange {
        background: var(--ms-orange-light);
        color: var(--ms-orange);
    }

    .ms-badge-red {
        background: var(--ms-red-light);
        color: var(--ms-red);
    }

    .ms-badge-gray {
        background: var(--ms-gray-light);
        color: var(--ms-gray);
    }

    .ms-badge-light {
        background: #f8f9fa;
        color: #6c757d;
    }

    .ms-badge-teal {
        background: var(--ms-teal-light);
        color: var(--ms-teal);
    }

    /* Progress Bars */
    .ms-progress {
        height: 28px;
        background: var(--ms-gray-border);
        border-radius: 4px;
        overflow: hidden;
        position: relative;
    }

    .ms-progress-bar {
        height: 100%;
        background: var(--ms-blue);
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: width 0.3s;
    }

    .ms-progress-label {
        font-size: 12px;
        font-weight: 500;
        color: white;
    }

    .ms-progress-large {
        height: 40px;
        background: var(--ms-gray-border);
        border-radius: 8px;
        overflow: hidden;
    }

    .ms-progress-large-bar {
        height: 100%;
        background: var(--ms-blue);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: width 0.3s;
    }

    .ms-progress-large-bar.teal {
        background: var(--ms-teal);
    }

    .ms-progress-large-label {
        font-size: 14px;
        font-weight: 600;
        color: white;
    }

    /* Action Buttons */
    .ms-icon-btn {
        width: 32px;
        height: 32px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--ms-gray);
        background: transparent;
        transition: all 0.2s;
        text-decoration: none;
    }

    .ms-icon-btn:hover {
        background: var(--ms-gray-light);
        color: var(--ms-blue);
        text-decoration: none;
    }

    .ms-icon-btn.sm {
        width: 28px;
        height: 28px;
        font-size: 12px;
    }

    .ms-action-buttons {
        display: flex;
        gap: 4px;
    }

    /* Links */
    .ms-link {
        color: var(--ms-blue);
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
    }

    .ms-link:hover {
        text-decoration: underline;
    }

    /* Empty State */
    .ms-empty-state {
        text-align: center;
        padding: 48px 24px;
    }

    .ms-empty-state i {
        font-size: 48px;
        color: var(--ms-gray-border);
        margin-bottom: 16px;
    }

    .ms-empty-state p {
        color: var(--ms-gray);
        margin: 0;
    }

    /* Meta Info */
    .ms-meta-info {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        color: var(--ms-gray);
        margin-top: 12px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .ms-meta-info i {
        margin-right: 6px;
    }

    /* Ranking Badges */
    .ranking-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        font-size: 16px;
    }

    .ranking-badge.gold {
        background: linear-gradient(135deg, #ffd700, #daa520);
        color: white;
    }

    .ranking-badge.silver {
        background: linear-gradient(135deg, #c0c0c0, #a8a8a8);
        color: white;
    }

    .ranking-badge.bronze {
        background: linear-gradient(135deg, #cd7f32, #a8652a);
        color: white;
    }

    .ranking-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--ms-gray-light);
        color: var(--ms-gray);
        font-weight: 600;
        font-size: 14px;
    }

    /* Action Cards */
    .ms-action-card {
        display: block;
        text-align: center;
        padding: 24px 16px;
        background: var(--ms-gray-light);
        border-radius: 8px;
        transition: all 0.2s ease;
        text-decoration: none;
        border: 1px solid transparent;
    }

    .ms-action-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--ms-shadow-hover);
        text-decoration: none;
        border-color: var(--ms-blue);
    }

    .ms-action-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        margin-bottom: 12px;
    }

    .ms-action-icon.blue {
        background: var(--ms-blue);
    }

    .ms-action-icon.green {
        background: var(--ms-green);
    }

    .ms-action-icon.orange {
        background: var(--ms-orange);
    }

    .ms-action-icon.teal {
        background: var(--ms-teal);
    }

    .ms-action-text {
        font-size: 14px;
        font-weight: 600;
        color: #323130;
        margin-bottom: 4px;
    }

    .ms-action-desc {
        font-size: 12px;
        color: var(--ms-gray);
    }

    .text-blue {
        color: var(--ms-blue);
    }

    .text-green {
        color: var(--ms-green);
    }

    .text-orange {
        color: var(--ms-orange);
    }

    .text-teal {
        color: var(--ms-teal);
    }

    .text-red {
        color: var(--ms-red);
    }

    .font-medium {
        font-weight: 500;
    }

    .text-center {
        text-align: center;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .ms-welcome-banner {
            flex-direction: column;
            text-align: center;
            gap: 16px;
        }

        .ms-card-kpi {
            flex-direction: column;
            text-align: center;
        }

        .ms-card-kpi-icon {
            margin-right: 0;
            margin-bottom: 12px;
        }

        .ms-meta-info {
            flex-direction: column;
            align-items: center;
        }
    }
</style>

<script>
    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {

        // Chart 1: Afiliados por Asesor
        <?php if (!empty($statsByAsesor)): ?>
            const asesoresCanvas = document.getElementById('asesoresChart');
            if (asesoresCanvas) {
                try {
                    const asesoresLabels = <?= json_encode(array_column($statsByAsesor, 'nombre_completo')) ?>;
                    const asesoresTotal = <?= json_encode(array_column($statsByAsesor, 'total')) ?>;
                    const asesoresActivos = <?= json_encode(array_column($statsByAsesor, 'activos')) ?>;

                    new Chart(asesoresCanvas.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: asesoresLabels,
                            datasets: [{
                                label: 'Total Afiliados',
                                data: asesoresTotal,
                                backgroundColor: '#0078d4',
                                borderRadius: 6,
                                barPercentage: 0.65
                            }, {
                                label: 'Activos',
                                data: asesoresActivos,
                                backgroundColor: '#107c10',
                                borderRadius: 6,
                                barPercentage: 0.65
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        boxWidth: 12,
                                        font: {
                                            size: 11
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Cantidad de Afiliados'
                                    },
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                } catch (e) {
                    console.error('Asesores chart error:', e);
                }
            }
        <?php endif; ?>

        // Chart 2: Crecimiento Mensual
        <?php if (!empty($monthlyData)): ?>
            const monthlyCanvas = document.getElementById('monthlyChart');
            if (monthlyCanvas) {
                try {
                    const monthlyLabels = <?= json_encode(array_column($monthlyData, 'month')) ?>;
                    const monthlyCounts = <?= json_encode(array_column($monthlyData, 'count')) ?>;

                    new Chart(monthlyCanvas.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: monthlyLabels,
                            datasets: [{
                                label: 'Nuevos Afiliados',
                                data: monthlyCounts,
                                borderColor: '#00a1ab',
                                backgroundColor: 'rgba(0, 161, 171, 0.05)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.3,
                                pointBackgroundColor: '#00a1ab',
                                pointBorderColor: '#fff',
                                pointRadius: 4,
                                pointHoverRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'top'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Cantidad de Afiliados'
                                    },
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                } catch (e) {
                    console.error('Monthly chart error:', e);
                }
            }
        <?php endif; ?>
    });
</script>