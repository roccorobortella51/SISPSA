<?php
/* @var $this yii\web\View */
/* @var $asesorName string */
/* @var $clinicaNombre string */
/* @var $clinicas array */
/* @var $statsByClinic array */
/* @var $clinicaStats array */
/* @var $asesorStats array */
/* @var $monthlyData array */
/* @var $planesData array */
/* @var $recientes array */
/* @var $debugInfo array */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Dashboard Asesor - SISPSA';
$this->params['breadcrumbs'][] = 'Dashboard Asesor';

// Register Chart.js
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

// Set default values for empty data
$clinicaStats = $clinicaStats ?? ['total_afiliados' => 0, 'activos' => 0, 'suspendidos' => 0, 'con_contratos_vencidos' => 0, 'nuevos_este_mes' => 0, 'tasa_actividad' => 0];
$asesorStats = $asesorStats ?? ['total_afiliados' => 0, 'activos' => 0, 'suspendidos' => 0, 'con_contratos_vencidos' => 0, 'nuevos_este_mes' => 0, 'tasa_actividad' => 0];
$statsByClinic = $statsByClinic ?? [];
$recientes = $recientes ?? [];
$monthlyData = $monthlyData ?? [];
$clinicas = $clinicas ?? [];
$planesData = $planesData ?? ['individual' => [], 'senior' => [], 'benefits_summary' => []];
?>

<div class="dashboard-asesor">

    <!-- Welcome Banner -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="ms-welcome-banner">
                <div class="ms-welcome-content">
                    <h1 class="ms-welcome-title">Buenos días, <?= Html::encode($asesorName ?? 'Asesor') ?></h1>
                    <p class="ms-welcome-subtitle">Panel de control de ventas y afiliados</p>
                </div>
                <div class="ms-welcome-date">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    <?= Yii::$app->formatter->asDate(date('Y-m-d'), 'EEEE, d MMMM Y') ?>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="ms-card ms-card-kpi">
                <div class="ms-card-kpi-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="ms-card-kpi-content">
                    <div class="ms-card-kpi-value"><?= number_format($clinicaStats['total_afiliados'] ?? 0) ?></div>
                    <div class="ms-card-kpi-label">Total Afiliados</div>
                    <div class="ms-card-kpi-trend">
                        <i class="fas fa-chart-line"></i>
                        <span>+<?= number_format($clinicaStats['nuevos_este_mes'] ?? 0) ?> este mes</span>
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
                    <div class="ms-card-kpi-value"><?= number_format($clinicaStats['activos'] ?? 0) ?></div>
                    <div class="ms-card-kpi-label">Activos</div>
                    <div class="ms-card-kpi-trend">
                        <i class="fas fa-percent"></i>
                        <span><?= ($clinicaStats['tasa_actividad'] ?? 0) ?>% del total</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="ms-card ms-card-kpi">
                <div class="ms-card-kpi-icon orange">
                    <i class="fas fa-pause-circle"></i>
                </div>
                <div class="ms-card-kpi-content">
                    <div class="ms-card-kpi-value"><?= number_format($clinicaStats['suspendidos'] ?? 0) ?></div>
                    <div class="ms-card-kpi-label">Suspendidos</div>
                    <div class="ms-card-kpi-trend text-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Requieren atención</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="ms-card ms-card-kpi">
                <div class="ms-card-kpi-icon red">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <div class="ms-card-kpi-content">
                    <div class="ms-card-kpi-value"><?= number_format($clinicaStats['con_contratos_vencidos'] ?? 0) ?></div>
                    <div class="ms-card-kpi-label">Contratos Vencidos</div>
                    <div class="ms-card-kpi-trend">
                        <i class="fas fa-clock"></i>
                        <span>Pendientes renovación</span>
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
                    <i class="fas fa-building"></i>
                </div>
                <div class="ms-metric-info">
                    <div class="ms-metric-value"><?= count($clinicas) ?></div>
                    <div class="ms-metric-label">Clínicas Activas</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="ms-metric-card">
                <div class="ms-metric-icon">
                    <i class="fas fa-percent"></i>
                </div>
                <div class="ms-metric-info">
                    <div class="ms-metric-value"><?= ($clinicaStats['tasa_actividad'] ?? 0) ?>%</div>
                    <div class="ms-metric-label">Tasa de Actividad</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="ms-metric-card">
                <div class="ms-metric-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="ms-metric-info">
                    <div class="ms-metric-value"><?= number_format($clinicaStats['nuevos_este_mes'] ?? 0) ?></div>
                    <div class="ms-metric-label">Nuevos Este Mes</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="ms-metric-card">
                <div class="ms-metric-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="ms-metric-info">
                    <div class="ms-metric-value">$<?= number_format(($clinicaStats['activos'] ?? 0) * 5, 2) ?></div>
                    <div class="ms-metric-label">Comisión Estimada</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="ms-card">
                <div class="ms-card-header">
                    <div class="ms-card-header-title">Afiliados por Clínica</div>
                    <div class="ms-card-header-subtitle">Distribución por institución</div>
                </div>
                <div class="ms-card-body">
                    <canvas id="clinicasChart" style="height: 320px; width: 100%;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="ms-card">
                <div class="ms-card-header">
                    <div class="ms-card-header-title">Crecimiento Mensual</div>
                    <div class="ms-card-header-subtitle">Últimos 6 meses</div>
                </div>
                <div class="ms-card-body">
                    <canvas id="monthlyChart" style="height: 320px; width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Clinics Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="ms-card">
                <div class="ms-card-header">
                    <div class="ms-card-header-title">Detalle por Clínica</div>
                    <div class="ms-card-header-subtitle">Listado completo de clínicas con afiliados asignados</div>
                </div>
                <div class="ms-card-body p-0">
                    <div class="ms-table-responsive">
                        <table class="ms-table">
                            <thead>
                                <tr>
                                    <th>Clínica</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Activos</th>
                                    <th class="text-center">Suspendidos</th>
                                    <th class="text-center">Actividad</th>
                                    <th class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($statsByClinic)): ?>
                                    <?php foreach ($statsByClinic as $clinic): ?>
                                        <tr>
                                            <td class="font-medium">
                                                <i class="fas fa-hospital text-blue mr-2"></i>
                                                <?= Html::encode($clinic['clinica_nombre'] ?? 'N/A') ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="ms-badge ms-badge-gray"><?= number_format($clinic['total'] ?? 0) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="ms-badge ms-badge-green"><?= number_format($clinic['activos'] ?? 0) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="ms-badge ms-badge-orange"><?= number_format($clinic['suspendidos'] ?? 0) ?></span>
                                            </td>
                                            <td class="text-center" style="width: 180px;">
                                                <?php $porcentaje = ($clinic['total'] ?? 0) > 0 ? round((($clinic['activos'] ?? 0) / ($clinic['total'] ?? 1)) * 100, 1) : 0; ?>
                                                <div class="ms-progress">
                                                    <div class="ms-progress-bar" style="width: <?= $porcentaje ?>%;">
                                                        <span class="ms-progress-label"><?= $porcentaje ?>%</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <?php
                                                $clinicaId = null;
                                                foreach ($clinicas as $c) {
                                                    if ($c->nombre == ($clinic['clinica_nombre'] ?? '')) {
                                                        $clinicaId = $c->id;
                                                        break;
                                                    }
                                                }
                                                if ($clinicaId):
                                                ?>
                                                    <a href="<?= Url::to(['/user-datos/index-clinicas', 'clinica_id' => $clinicaId]) ?>"
                                                        class="ms-icon-btn"
                                                        target="_blank"
                                                        title="Ver afiliados">
                                                        <i class="fas fa-arrow-right"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr class="ms-table-empty">
                                        <td colspan="6">
                                            <div class="ms-empty-state">
                                                <i class="fas fa-inbox"></i>
                                                <p>No hay datos disponibles</p>
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

    <!-- Personal Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="ms-personal-card">
                <div class="ms-personal-card-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="ms-personal-card-info">
                    <div class="ms-personal-card-value"><?= number_format($asesorStats['total_afiliados'] ?? 0) ?></div>
                    <div class="ms-personal-card-label">Total Afiliados</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="ms-personal-card">
                <div class="ms-personal-card-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="ms-personal-card-info">
                    <div class="ms-personal-card-value"><?= number_format($asesorStats['activos'] ?? 0) ?></div>
                    <div class="ms-personal-card-label">Activos</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="ms-personal-card">
                <div class="ms-personal-card-icon teal">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <div class="ms-personal-card-info">
                    <div class="ms-personal-card-value"><?= number_format($asesorStats['nuevos_este_mes'] ?? 0) ?></div>
                    <div class="ms-personal-card-label">Nuevos Este Mes</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Cards -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="ms-card">
                <div class="ms-card-header">
                    <div class="ms-card-header-title">Meta de Ventas 2026</div>
                    <div class="ms-card-header-subtitle">Objetivo: 50 afiliados activos</div>
                </div>
                <div class="ms-card-body">
                    <?php $metaPorcentaje = min(100, round((($asesorStats['activos'] ?? 0) / 50) * 100, 1)); ?>
                    <div class="ms-progress-large mb-2">
                        <div class="ms-progress-large-bar" style="width: <?= $metaPorcentaje ?>%;">
                            <span class="ms-progress-large-label"><?= $metaPorcentaje ?>%</span>
                        </div>
                    </div>
                    <div class="ms-meta-info">
                        <span><i class="fas fa-bullseye text-blue"></i> Actual: <?= $asesorStats['activos'] ?? 0 ?></span>
                        <span><i class="fas fa-flag-checkered text-green"></i> Meta: 50</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="ms-card">
                <div class="ms-card-header">
                    <div class="ms-card-header-title">Tasa de Actividad</div>
                    <div class="ms-card-header-subtitle">Afiliados con contratos activos</div>
                </div>
                <div class="ms-card-body">
                    <div class="ms-progress-large mb-2">
                        <div class="ms-progress-large-bar teal" style="width: <?= ($asesorStats['tasa_actividad'] ?? 0) ?>%;">
                            <span class="ms-progress-large-label"><?= ($asesorStats['tasa_actividad'] ?? 0) ?>%</span>
                        </div>
                    </div>
                    <div class="ms-meta-info">
                        <span><i class="fas fa-chart-line text-teal"></i> <?= ($asesorStats['tasa_actividad'] ?? 0) ?>% de tus afiliados están activos</span>
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
                    <div class="ms-card-header-subtitle">Últimos 10 afiliados registrados</div>
                </div>
                <div class="ms-card-body p-0">
                    <div class="ms-table-responsive">
                        <table class="ms-table">
                            <thead>
                                <tr>
                                    <th style="width: 40px">#</th>
                                    <th>Nombre</th>
                                    <th>Documento</th>
                                    <th>Contacto</th>
                                    <th>Clínica</th>
                                    <th>Estatus</th>
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
                                                <div class="ms-contact-info">
                                                    <div><i class="fas fa-envelope text-gray"></i> <?= Html::encode($afiliado['email'] ?? '') ?></div>
                                                    <div><i class="fas fa-phone text-gray"></i> <?= Html::encode($afiliado['telefono'] ?? '') ?></div>
                                                </div>
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
                                                <p>No hay afiliados registrados</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="ms-card-footer">
                    <?php
                    $asesorUserDatos = \app\models\UserDatos::findOne(['user_login_id' => Yii::$app->user->id]);
                    if ($asesorUserDatos):
                        $agenteFuerza = \app\models\AgenteFuerza::findOne(['idusuario' => $asesorUserDatos->id]);
                        $asesorIdParam = $agenteFuerza ? $agenteFuerza->id : null;
                    ?>
                        <a href="<?= Url::to(['/user-datos/index-by-afiliado', 'asesor_id' => $asesorIdParam]) ?>" class="ms-link">
                            Ver todos los afiliados <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Plans Section - Individual Plans with CORRECT COLORS -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="ms-card">
                <div class="ms-card-header">
                    <div class="ms-card-header-title">Planes Individuales</div>
                    <div class="ms-card-header-subtitle">Cobertura médica para personas de 0 a 59 años</div>
                </div>
                <div class="ms-card-body">
                    <div class="row">
                        <!-- Plan Bronce - Bronze Color -->
                        <div class="col-md-4 mb-3">
                            <div class="ms-plan-card plan-bronce">
                                <div class="ms-plan-card-header">
                                    <i class="fas fa-medal plan-icon"></i>
                                    <h3 class="ms-plan-card-title">Plan Bronce</h3>
                                    <div class="ms-plan-card-price">
                                        $16 <span class="ms-plan-card-period">/mes</span>
                                        <div class="ms-plan-card-optional">+ $11 Maternidad (Opcional)</div>
                                    </div>
                                </div>
                                <div class="ms-plan-card-body">
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-shield-alt"></i>
                                        <span>Cobertura: <strong>$10.000</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Edad: <strong>0 a 59 años</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-ambulance"></i>
                                        <span>Emergencias: <strong>Inmediato</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-stethoscope"></i>
                                        <span>Consultas Básicas: <strong>Sin espera</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-heartbeat"></i>
                                        <span>Consultas Especializadas: <strong>4 meses</strong></span>
                                    </div>
                                </div>
                                <div class="ms-plan-card-footer">
                                    <button class="ms-btn ms-btn-bronce w-100" onclick="showPlanDetails('Plan Bronce')">
                                        <i class="fas fa-info-circle mr-2"></i>Más información
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Plan Plata - Silver Color -->
                        <div class="col-md-4 mb-3">
                            <div class="ms-plan-card plan-plata">
                                <div class="ms-plan-card-header">
                                    <i class="fas fa-gem plan-icon"></i>
                                    <h3 class="ms-plan-card-title">Plan Plata</h3>
                                    <div class="ms-plan-card-price">
                                        $20 <span class="ms-plan-card-period">/mes</span>
                                        <div class="ms-plan-card-optional">+ $11 Maternidad (Opcional)</div>
                                    </div>
                                </div>
                                <div class="ms-plan-card-body">
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-shield-alt"></i>
                                        <span>Cobertura: <strong>$15.000</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Edad: <strong>0 a 59 años</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-ambulance"></i>
                                        <span>Emergencias: <strong>Inmediato</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-stethoscope"></i>
                                        <span>Consultas Básicas: <strong>Sin espera</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-heartbeat"></i>
                                        <span>Consultas Especializadas: <strong>3 meses (2 anuales)</strong></span>
                                    </div>
                                </div>
                                <div class="ms-plan-card-footer">
                                    <button class="ms-btn ms-btn-plata w-100" onclick="showPlanDetails('Plan Plata')">
                                        <i class="fas fa-info-circle mr-2"></i>Más información
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Plan Oro - Gold Color -->
                        <div class="col-md-4 mb-3">
                            <div class="ms-plan-card plan-oro">
                                <div class="ms-plan-card-header">
                                    <i class="fas fa-crown plan-icon"></i>
                                    <h3 class="ms-plan-card-title">Plan Oro</h3>
                                    <div class="ms-plan-card-price">
                                        $24 <span class="ms-plan-card-period">/mes</span>
                                        <div class="ms-plan-card-optional">+ $11 Maternidad (Opcional)</div>
                                    </div>
                                </div>
                                <div class="ms-plan-card-body">
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-shield-alt"></i>
                                        <span>Cobertura: <strong>$20.000</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Edad: <strong>0 a 59 años</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-ambulance"></i>
                                        <span>Emergencias: <strong>Inmediato</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-stethoscope"></i>
                                        <span>Consultas Básicas: <strong>Sin espera</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-heartbeat"></i>
                                        <span>Consultas Especializadas: <strong>2 meses (3 anuales)</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-procedures"></i>
                                        <span>Cirugías Electivas: <strong>12 meses</strong></span>
                                    </div>
                                </div>
                                <div class="ms-plan-card-footer">
                                    <button class="ms-btn ms-btn-oro w-100" onclick="showPlanDetails('Plan Oro')">
                                        <i class="fas fa-info-circle mr-2"></i>Más información
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Senior Plans Section with CORRECT COLORS -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="ms-card">
                <div class="ms-card-header">
                    <div class="ms-card-header-title">Planes Senior</div>
                    <div class="ms-card-header-subtitle">Cobertura especializada para adultos de 60 a 80 años</div>
                </div>
                <div class="ms-card-body">
                    <div class="row">
                        <!-- Plan Esmeralda Básico - Emerald Green -->
                        <div class="col-md-4 mb-3">
                            <div class="ms-plan-card plan-esmeralda">
                                <div class="ms-plan-card-header">
                                    <i class="fas fa-leaf plan-icon"></i>
                                    <h3 class="ms-plan-card-title">Plan Esmeralda Básico</h3>
                                    <div class="ms-plan-card-price">
                                        $13 <span class="ms-plan-card-period">/mes</span>
                                    </div>
                                </div>
                                <div class="ms-plan-card-body">
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-shield-alt"></i>
                                        <span>Cobertura: <strong>$14.000</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Edad: <strong>60 a 80 años</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-ambulance"></i>
                                        <span>Emergencias: <strong>Inmediato</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-stethoscope"></i>
                                        <span>Consultas: <strong>Medicina General sin espera</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-heartbeat"></i>
                                        <span>Consultas Especializadas: <strong>4 meses (1 anual)</strong></span>
                                    </div>
                                </div>
                                <div class="ms-plan-card-footer">
                                    <button class="ms-btn ms-btn-esmeralda w-100" onclick="showPlanDetails('Plan Esmeralda Básico')">
                                        <i class="fas fa-info-circle mr-2"></i>Más información
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Plan Esmeralda Plus - Brighter Emerald -->
                        <div class="col-md-4 mb-3">
                            <div class="ms-plan-card plan-esmeralda-plus">
                                <div class="ms-plan-card-header">
                                    <i class="fas fa-gem plan-icon"></i>
                                    <h3 class="ms-plan-card-title">Plan Esmeralda Plus</h3>
                                    <div class="ms-plan-card-price">
                                        $29 <span class="ms-plan-card-period">/mes</span>
                                    </div>
                                </div>
                                <div class="ms-plan-card-body">
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-shield-alt"></i>
                                        <span>Cobertura: <strong>$16.000</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Edad: <strong>60 a 80 años</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-ambulance"></i>
                                        <span>Emergencias: <strong>Inmediato</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-stethoscope"></i>
                                        <span>Consultas Básicas: <strong>Sin espera</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-heartbeat"></i>
                                        <span>Consultas Especializadas: <strong>3 meses (2 anuales)</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-procedures"></i>
                                        <span>Cirugías Electivas: <strong>12 meses</strong></span>
                                    </div>
                                </div>
                                <div class="ms-plan-card-footer">
                                    <button class="ms-btn ms-btn-esmeralda-plus w-100" onclick="showPlanDetails('Plan Esmeralda Plus')">
                                        <i class="fas fa-info-circle mr-2"></i>Más información
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Plan Diamante - Diamond Blue -->
                        <div class="col-md-4 mb-3">
                            <div class="ms-plan-card plan-diamante">
                                <div class="ms-plan-card-header">
                                    <i class="fas fa-diamond plan-icon"></i>
                                    <h3 class="ms-plan-card-title">Plan Diamante</h3>
                                    <div class="ms-plan-card-price">
                                        $34 <span class="ms-plan-card-period">/mes</span>
                                    </div>
                                </div>
                                <div class="ms-plan-card-body">
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-shield-alt"></i>
                                        <span>Cobertura: <strong>$50.000</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Edad: <strong>60 a 80 años</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-ambulance"></i>
                                        <span>Emergencias: <strong>Inmediato</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-stethoscope"></i>
                                        <span>Consultas Básicas: <strong>Sin espera</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-heartbeat"></i>
                                        <span>Consultas Especializadas: <strong>2 meses (3 anuales)</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-procedures"></i>
                                        <span>Cirugías Electivas: <strong>12 meses</strong></span>
                                    </div>
                                    <div class="ms-plan-feature">
                                        <i class="fas fa-heart"></i>
                                        <span>UCI/UCIN: <strong>Cubierto</strong></span>
                                    </div>
                                </div>
                                <div class="ms-plan-card-footer">
                                    <button class="ms-btn ms-btn-diamante w-100" onclick="showPlanDetails('Plan Diamante')">
                                        <i class="fas fa-info-circle mr-2"></i>Más información
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Benefits Summary -->
    <div class="row">
        <div class="col-12">
            <div class="ms-card">
                <div class="ms-card-header">
                    <div class="ms-card-header-title">Beneficios Incluidos</div>
                    <div class="ms-card-header-subtitle">Cobertura estándar en todos los planes</div>
                </div>
                <div class="ms-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="ms-benefit-item">
                                <i class="fas fa-bolt"></i>
                                <div class="ms-benefit-content">
                                    <strong>Atención Inmediata</strong>
                                    <p>Desde el primer día tendrá garantizada la atención médica cubierta por su plan en casos de emergencias en su clínica asignada sólo presentando la cédula, sin trámites, ni claves.</p>
                                </div>
                            </div>
                            <div class="ms-benefit-item">
                                <i class="fas fa-ambulance"></i>
                                <div class="ms-benefit-content">
                                    <strong>Emergencias</strong>
                                    <p>Sin plazos de espera. Atención Primaria de Emergencia con hematología, glicemia, rayos X, medicación analgésica, ecograma según criterio médico, sala de cura menor y terapia respiratoria.</p>
                                </div>
                            </div>
                            <div class="ms-benefit-item">
                                <i class="fas fa-stethoscope"></i>
                                <div class="ms-benefit-content">
                                    <strong>Consultas Básicas</strong>
                                    <p>Sin plazos de espera. Medicina General y Pediatría. Las que requiera en el año contratado.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ms-benefit-item">
                                <i class="fas fa-heartbeat"></i>
                                <div class="ms-benefit-content">
                                    <strong>Consultas Especializadas</strong>
                                    <p>Medicina Interna, Cirugía General, Ginecología, Cardiología, Traumatología, Urología y más. Plazos de espera desde 2 a 4 meses según el plan.</p>
                                </div>
                            </div>
                            <div class="ms-benefit-item">
                                <i class="fas fa-microscope"></i>
                                <div class="ms-benefit-content">
                                    <strong>Exámenes de Laboratorio</strong>
                                    <p>Hematología Completa, Glicemia, Creatinina, Colesterol, Perfil Lipídico, HIV, VDRL y más. Entre 1 y 2 rutinas anuales según el plan.</p>
                                </div>
                            </div>
                            <div class="ms-benefit-item">
                                <i class="fas fa-baby-carriage"></i>
                                <div class="ms-benefit-content">
                                    <strong>Anexo de Maternidad</strong>
                                    <p>Opcional en planes individuales (+$11). 12 meses de espera. Cubre consultas, ecografías, atención de parto/cesárea y atención del recién nacido.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* ============================================
   MICROSOFT FLUENT DESIGN SYSTEM
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
        --ms-gray: #605e5c;
        --ms-gray-light: #f3f2f1;
        --ms-gray-border: #edebe9;
        --ms-white: #ffffff;
        --ms-shadow: 0 2px 4px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.03);
        --ms-shadow-hover: 0 8px 16px rgba(0, 0, 0, 0.08);

        /* Plan Colors */
        --bronze: #cd7f32;
        --bronze-dark: #a8652a;
        --bronze-light: #e8a95b;

        --silver: #c0c0c0;
        --silver-dark: #a8a8a8;
        --silver-light: #e0e0e0;

        --gold: #ffd700;
        --gold-dark: #daa520;
        --gold-light: #ffe44d;

        --emerald: #2ecc71;
        --emerald-dark: #27ae60;
        --emerald-light: #58d68d;

        --emerald-plus: #1abc9c;
        --emerald-plus-dark: #16a085;

        --diamond: #00bfff;
        --diamond-dark: #009acd;
        --diamond-light: #4dc9ff;
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

    /* Personal Cards */
    .ms-personal-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        border: 1px solid var(--ms-gray-border);
        transition: box-shadow 0.2s;
    }

    .ms-personal-card:hover {
        box-shadow: var(--ms-shadow-hover);
    }

    .ms-personal-card-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
    }

    .ms-personal-card-icon.blue {
        background: linear-gradient(135deg, #0078d4, #106ebe);
    }

    .ms-personal-card-icon.green {
        background: linear-gradient(135deg, #107c10, #0e6b0e);
    }

    .ms-personal-card-icon.teal {
        background: linear-gradient(135deg, #00a1ab, #008a92);
    }

    .ms-personal-card-info {
        flex: 1;
    }

    .ms-personal-card-value {
        font-size: 32px;
        font-weight: 600;
        color: #323130;
    }

    .ms-personal-card-label {
        font-size: 14px;
        color: var(--ms-gray);
    }

    /* Buttons */
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

    .ms-link {
        color: var(--ms-blue);
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
    }

    .ms-link:hover {
        text-decoration: underline;
    }

    /* ============================================
   PLAN CARDS WITH SPECIFIC COLORS
   ============================================ */

    /* Plan Cards Base */
    .ms-plan-card {
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        height: 100%;
        box-shadow: var(--ms-shadow);
    }

    .ms-plan-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--ms-shadow-hover);
    }

    .ms-plan-card .plan-icon {
        font-size: 2rem;
        margin-bottom: 10px;
        display: block;
    }

    .ms-plan-card-header {
        padding: 24px;
        text-align: center;
        color: white;
    }

    .ms-plan-card-title {
        font-size: 1.3rem;
        font-weight: 700;
        margin: 10px 0;
    }

    .ms-plan-card-price {
        font-size: 2rem;
        font-weight: 800;
    }

    .ms-plan-card-period {
        font-size: 0.8rem;
        font-weight: 400;
        opacity: 0.85;
    }

    .ms-plan-card-optional {
        font-size: 0.7rem;
        font-weight: 400;
        margin-top: 5px;
        opacity: 0.9;
    }

    .ms-plan-card-body {
        padding: 20px;
        background: white;
    }

    .ms-plan-feature {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #edebe9;
        font-size: 0.85rem;
    }

    .ms-plan-feature:last-child {
        border-bottom: none;
    }

    .ms-plan-feature i {
        width: 24px;
        font-size: 1rem;
        color: #666;
    }

    .ms-plan-card-footer {
        padding: 16px 20px 20px;
        background: white;
        border-top: 1px solid #edebe9;
    }

    /* Plan Bronce - Bronze Theme */
    .plan-bronce .ms-plan-card-header {
        background: linear-gradient(135deg, var(--bronze), var(--bronze-dark));
    }

    .ms-btn-bronce {
        background: linear-gradient(135deg, var(--bronze), var(--bronze-dark));
        border: none;
        color: white;
    }

    .ms-btn-bronce:hover {
        background: linear-gradient(135deg, var(--bronze-dark), var(--bronze));
        transform: translateY(-2px);
    }

    /* Plan Plata - Silver Theme */
    .plan-plata .ms-plan-card-header {
        background: linear-gradient(135deg, var(--silver), var(--silver-dark));
        color: #333;
    }

    .plan-plata .ms-plan-card-header .plan-icon,
    .plan-plata .ms-plan-card-title,
    .plan-plata .ms-plan-card-price {
        color: #333;
    }

    .ms-btn-plata {
        background: linear-gradient(135deg, var(--silver), var(--silver-dark));
        border: none;
        color: #333;
    }

    .ms-btn-plata:hover {
        background: linear-gradient(135deg, var(--silver-dark), var(--silver));
        transform: translateY(-2px);
        color: #333;
    }

    /* Plan Oro - Gold Theme */
    .plan-oro .ms-plan-card-header {
        background: linear-gradient(135deg, var(--gold), var(--gold-dark));
        color: #333;
    }

    .plan-oro .ms-plan-card-header .plan-icon,
    .plan-oro .ms-plan-card-title,
    .plan-oro .ms-plan-card-price {
        color: #333;
    }

    .ms-btn-oro {
        background: linear-gradient(135deg, var(--gold), var(--gold-dark));
        border: none;
        color: #333;
    }

    .ms-btn-oro:hover {
        background: linear-gradient(135deg, var(--gold-dark), var(--gold));
        transform: translateY(-2px);
        color: #333;
    }

    /* Plan Esmeralda - Emerald Theme */
    .plan-esmeralda .ms-plan-card-header {
        background: linear-gradient(135deg, var(--emerald), var(--emerald-dark));
    }

    .ms-btn-esmeralda {
        background: linear-gradient(135deg, var(--emerald), var(--emerald-dark));
        border: none;
        color: white;
    }

    .ms-btn-esmeralda:hover {
        background: linear-gradient(135deg, var(--emerald-dark), var(--emerald));
        transform: translateY(-2px);
    }

    /* Plan Esmeralda Plus - Brighter Emerald Theme */
    .plan-esmeralda-plus .ms-plan-card-header {
        background: linear-gradient(135deg, var(--emerald-plus), var(--emerald-plus-dark));
    }

    .ms-btn-esmeralda-plus {
        background: linear-gradient(135deg, var(--emerald-plus), var(--emerald-plus-dark));
        border: none;
        color: white;
    }

    .ms-btn-esmeralda-plus:hover {
        background: linear-gradient(135deg, var(--emerald-plus-dark), var(--emerald-plus));
        transform: translateY(-2px);
    }

    /* Plan Diamante - Diamond Blue Theme */
    .plan-diamante .ms-plan-card-header {
        background: linear-gradient(135deg, var(--diamond), var(--diamond-dark));
    }

    .ms-btn-diamante {
        background: linear-gradient(135deg, var(--diamond), var(--diamond-dark));
        border: none;
        color: white;
    }

    .ms-btn-diamante:hover {
        background: linear-gradient(135deg, var(--diamond-dark), var(--diamond));
        transform: translateY(-2px);
    }

    /* Benefit Items */
    .ms-benefit-item {
        display: flex;
        gap: 16px;
        margin-bottom: 20px;
    }

    .ms-benefit-item i {
        font-size: 28px;
        color: var(--ms-blue);
        flex-shrink: 0;
    }

    .ms-benefit-content strong {
        display: block;
        font-size: 14px;
        margin-bottom: 4px;
        color: #323130;
    }

    .ms-benefit-content p {
        font-size: 13px;
        color: var(--ms-gray);
        margin: 0;
        line-height: 1.5;
    }

    /* Buttons */
    .ms-btn {
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        border: none;
    }

    .ms-btn:hover {
        transform: translateY(-2px);
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
    }

    .ms-meta-info i {
        margin-right: 6px;
    }

    /* Contact Info */
    .ms-contact-info {
        font-size: 12px;
    }

    .ms-contact-info div {
        margin-bottom: 2px;
    }

    .ms-contact-info i {
        width: 14px;
        margin-right: 6px;
        color: var(--ms-gray);
    }

    /* Utility Classes */
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

        .ms-plan-card-price {
            font-size: 1.5rem;
        }
    }
</style>

<script>
    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {

        // Chart 1: Afiliados por Clínica
        <?php if (!empty($statsByClinic)): ?>
            const clinicasCanvas = document.getElementById('clinicasChart');
            if (clinicasCanvas) {
                try {
                    const clinicasLabels = <?= json_encode(array_column($statsByClinic, 'clinica_nombre')) ?>;
                    const clinicasTotal = <?= json_encode(array_column($statsByClinic, 'total')) ?>;
                    const clinicasActivos = <?= json_encode(array_column($statsByClinic, 'activos')) ?>;

                    new Chart(clinicasCanvas.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: clinicasLabels,
                            datasets: [{
                                label: 'Total Afiliados',
                                data: clinicasTotal,
                                backgroundColor: '#0078d4',
                                borderRadius: 6,
                                barPercentage: 0.65
                            }, {
                                label: 'Activos',
                                data: clinicasActivos,
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
                    console.error('Clinicas chart error:', e);
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

    function showPlanDetails(planName) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Información del Plan',
                html: `Para más información sobre <strong>${planName}</strong>, contacte al departamento de ventas.`,
                icon: 'info',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#0078d4'
            });
        } else {
            alert('Para más información sobre el plan ' + planName + ', contacte al departamento de ventas.');
        }
    }
</script>