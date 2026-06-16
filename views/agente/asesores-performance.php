<?php
/* @var $this yii\web\View */
/* @var $agenciaName string */
/* @var $agenciaId int */
/* @var $asesoresData array */
/* @var $totalAsesores int */
/* @var $totalAfiliadosGeneral int */
/* @var $totalActivosGeneral int */
/*var $totalSuspendidosGeneral int */
/* @var $totalVencidosGeneral int */
/* @var $totalComisionMensual float */
/* @var $totalComisionAnual float */
/* @var $tasaActividadGeneral float */
/* @var $asesoresConAlertas int */
/* @var $topPerformers array */
/* @var $asesoresNecesitanAtencion array */
/* @var $asesoresInactivos array */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'CRM de Ventas - ' . Html::encode($agenciaName ?? 'Mi Agencia');
$this->params['breadcrumbs'][] = ['label' => 'Mi Agencia', 'url' => ['site/dashboard-agencia']];
$this->params['breadcrumbs'][] = 'CRM de Ventas';

// Set default values for empty data
$asesoresData = $asesoresData ?? [];
$topPerformers = $topPerformers ?? [];
$asesoresNecesitanAtencion = $asesoresNecesitanAtencion ?? [];
$asesoresInactivos = $asesoresInactivos ?? [];
$totalAfiliadosGeneral = $totalAfiliadosGeneral ?? 0;
$totalActivosGeneral = $totalActivosGeneral ?? 0;
$totalSuspendidosGeneral = $totalSuspendidosGeneral ?? 0;
$totalVencidosGeneral = $totalVencidosGeneral ?? 0;
$totalComisionMensual = $totalComisionMensual ?? 0;
$totalComisionAnual = $totalComisionAnual ?? 0;
$tasaActividadGeneral = $tasaActividadGeneral ?? 0;
$asesoresConAlertas = $asesoresConAlertas ?? 0;
$totalAsesores = $totalAsesores ?? 0;
$agenciaName = $agenciaName ?? 'Mi Agencia';
$agenciaId = $agenciaId ?? null;
?>

<div class="crm-ventas-container">

    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="crm-header">
                <div class="crm-header-content">
                    <h1 class="crm-header-title">
                        <i class="fas fa-chart-line crm-header-icon"></i>
                        CRM de Ventas
                    </h1>
                    <p class="crm-header-subtitle">
                        Monitoreo y gestión del rendimiento de su fuerza de ventas · <?= Html::encode($agenciaName) ?>
                    </p>
                </div>
                <div class="crm-header-actions">
                    <a href="<?= Url::to(['/user/create', 'role' => 'Asesor']) ?>" class="crm-btn crm-btn-primary">
                        <i class="fas fa-user-plus mr-2"></i>Nuevo Asesor
                    </a>
                    <a href="<?= Url::to(['/agente-fuerza/index-by-agente', 'agente_id' => $agenciaId]) ?>" class="crm-btn crm-btn-secondary">
                        <i class="fas fa-percent mr-2"></i>Gestionar Comisiones
                    </a>
                    <a href="<?= Url::to(['/user-datos/index-by-agente', 'agente_id' => $agenciaId]) ?>" class="crm-btn crm-btn-outline">
                        <i class="fas fa-users mr-2"></i>Ver Afiliados
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Executive KPI Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="crm-kpi-card">
                <div class="crm-kpi-icon bg-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="crm-kpi-content">
                    <div class="crm-kpi-value"><?= $asesoresConAlertas ?></div>
                    <div class="crm-kpi-label">Asesores con Alertas</div>
                    <div class="crm-kpi-trend">
                        <i class="fas fa-bell"></i>
                        <span>Requieren atención inmediata</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="crm-kpi-card">
                <div class="crm-kpi-icon bg-danger">
                    <i class="fas fa-user-slash"></i>
                </div>
                <div class="crm-kpi-content">
                    <div class="crm-kpi-value"><?= count($asesoresInactivos) ?></div>
                    <div class="crm-kpi-label">Asesores Inactivos</div>
                    <div class="crm-kpi-trend">
                        <i class="fas fa-chart-line"></i>
                        <span>Sin ventas registradas</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="crm-kpi-card">
                <div class="crm-kpi-icon bg-success">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="crm-kpi-content">
                    <div class="crm-kpi-value"><?= $tasaActividadGeneral ?>%</div>
                    <div class="crm-kpi-label">Tasa de Actividad</div>
                    <div class="crm-kpi-trend">
                        <i class="fas fa-users"></i>
                        <span><?= number_format($totalActivosGeneral) ?> de <?= number_format($totalAfiliadosGeneral) ?> activos</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="crm-kpi-card">
                <div class="crm-kpi-icon bg-teal">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="crm-kpi-content">
                    <div class="crm-kpi-value">$<?= number_format($totalComisionMensual, 2) ?></div>
                    <div class="crm-kpi-label">Comisión Estimada</div>
                    <div class="crm-kpi-trend">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Anual: $<?= number_format($totalComisionAnual, 2) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performers Section -->
    <?php if (!empty($topPerformers)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="crm-section">
                    <div class="crm-section-header">
                        <div class="crm-section-title">
                            <i class="fas fa-trophy text-gold"></i>
                            <span>Top Performers</span>
                        </div>
                        <div class="crm-section-subtitle">Los asesores con mejor rendimiento de la agencia</div>
                    </div>
                    <div class="row">
                        <?php foreach ($topPerformers as $index => $asesor): ?>
                            <?php
                            $medalIcon = '';
                            $medalClass = '';
                            $medalColor = '';
                            if ($index == 0) {
                                $medalIcon = '🥇';
                                $medalClass = 'gold';
                                $medalColor = 'linear-gradient(135deg, #ffd700, #daa520)';
                            } elseif ($index == 1) {
                                $medalIcon = '🥈';
                                $medalClass = 'silver';
                                $medalColor = 'linear-gradient(135deg, #c0c0c0, #a8a8a8)';
                            } else {
                                $medalIcon = '🥉';
                                $medalClass = 'bronze';
                                $medalColor = 'linear-gradient(135deg, #cd7f32, #a8652a)';
                            }
                            ?>
                            <div class="col-md-4 mb-3">
                                <div class="crm-performer-card <?= $medalClass ?>">
                                    <div class="crm-performer-rank" style="background: <?= $medalColor ?>;">
                                        <?= $medalIcon ?>
                                    </div>
                                    <div class="crm-performer-info">
                                        <h4><?= Html::encode($asesor['nombre_completo'] ?? 'N/A') ?></h4>
                                        <div class="crm-performer-stats">
                                            <div class="crm-performer-stat">
                                                <div class="stat-value"><?= number_format($asesor['total'] ?? 0) ?></div>
                                                <div class="stat-label">Total Afiliados</div>
                                            </div>
                                            <div class="crm-performer-stat">
                                                <div class="stat-value"><?= number_format($asesor['activos'] ?? 0) ?></div>
                                                <div class="stat-label">Activos</div>
                                            </div>
                                            <div class="crm-performer-stat">
                                                <div class="stat-value"><?= $asesor['tasa_actividad'] ?? 0 ?>%</div>
                                                <div class="stat-label">Actividad</div>
                                            </div>
                                        </div>
                                        <div class="crm-performer-progress">
                                            <div class="crm-progress-fill" style="width: <?= $asesor['tasa_actividad'] ?? 0 ?>%;"></div>
                                        </div>
                                        <div class="crm-performer-footer">
                                            <span class="crm-comision-badge">
                                                <i class="fas fa-dollar-sign"></i> <?= number_format($asesor['comision_estimada'] ?? 0, 2) ?>
                                            </span>
                                            <a href="<?= Url::to(['/user-datos/index-by-afiliado', 'asesor_id' => $asesor['id'] ?? 0]) ?>" class="crm-link-arrow">
                                                Ver afiliados <i class="fas fa-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Asesores que Requieren Atención Section -->
    <?php if (!empty($asesoresNecesitanAtencion)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="crm-section">
                    <div class="crm-section-header crm-section-alert">
                        <div class="crm-section-title">
                            <i class="fas fa-bell text-warning"></i>
                            <span>Asesores que Requieren Atención</span>
                            <span class="crm-badge crm-badge-warning"><?= count($asesoresNecesitanAtencion) ?> pendientes</span>
                        </div>
                        <div class="crm-section-subtitle">Contacte a estos asesores para mejorar su rendimiento</div>
                    </div>
                    <div class="crm-alerts-grid">
                        <?php foreach ($asesoresNecesitanAtencion as $asesor): ?>
                            <div class="crm-alert-card alert-level-<?= $asesor['alert_level'] ?? 'medium' ?>">
                                <div class="crm-alert-icon">
                                    <?php if (($asesor['alert_level'] ?? '') == 'high'): ?>
                                        <i class="fas fa-exclamation-circle"></i>
                                    <?php else: ?>
                                        <i class="fas fa-clock"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="crm-alert-content">
                                    <div class="crm-alert-header">
                                        <div class="crm-alert-title">
                                            <strong><?= Html::encode($asesor['nombre_completo'] ?? 'N/A') ?></strong>
                                            <span class="crm-alert-badge alert-<?= $asesor['alert_level'] ?? 'medium' ?>">
                                                <?= ($asesor['alert_level'] ?? '') == 'high' ? 'Urgente' : 'Atención Media' ?>
                                            </span>
                                        </div>
                                        <div class="crm-alert-message">
                                            <i class="fas fa-info-circle"></i> <?= Html::encode($asesor['alert_message'] ?? 'Sin información') ?>
                                        </div>
                                    </div>
                                    <div class="crm-alert-stats">
                                        <div class="alert-stat">
                                            <i class="fas fa-users text-blue"></i>
                                            <span>Total: <strong><?= number_format($asesor['total'] ?? 0) ?></strong></span>
                                        </div>
                                        <div class="alert-stat">
                                            <i class="fas fa-check-circle text-success"></i>
                                            <span>Activos: <strong><?= number_format($asesor['activos'] ?? 0) ?></strong></span>
                                        </div>
                                        <div class="alert-stat">
                                            <i class="fas fa-pause-circle text-warning"></i>
                                            <span>Suspendidos: <strong><?= number_format($asesor['suspendidos'] ?? 0) ?></strong></span>
                                        </div>
                                        <div class="alert-stat">
                                            <i class="fas fa-calendar-times text-danger"></i>
                                            <span>Vencidos: <strong><?= number_format($asesor['vencidos'] ?? 0) ?></strong></span>
                                        </div>
                                        <?php if (isset($asesor['days_since_last_activity']) && $asesor['days_since_last_activity']): ?>
                                            <div class="alert-stat">
                                                <i class="fas fa-clock text-warning"></i>
                                                <span>Inactivo: <strong><?= $asesor['days_since_last_activity'] ?> días</strong></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="crm-alert-actions">
                                        <a href="tel:<?= $asesor['telefono'] ?? '' ?>" class="crm-action-btn crm-action-call">
                                            <i class="fas fa-phone"></i> Llamar
                                        </a>
                                        <a href="mailto:<?= $asesor['email'] ?? '' ?>" class="crm-action-btn crm-action-email">
                                            <i class="fas fa-envelope"></i> Email
                                        </a>
                                        <a href="<?= Url::to(['/agente-fuerza/update', 'id' => $asesor['id'] ?? 0]) ?>" class="crm-action-btn crm-action-config">
                                            <i class="fas fa-cog"></i> Configurar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Performance Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="crm-section">
                <div class="crm-section-header">
                    <div class="crm-section-title">
                        <i class="fas fa-chart-simple"></i>
                        <span>Rendimiento Detallado por Asesor</span>
                    </div>
                    <div class="crm-section-subtitle">
                        <?= count($asesoresData) ?> asesores activos · <?= number_format($totalAfiliadosGeneral) ?> afiliados totales
                    </div>
                </div>
                <div class="crm-table-container">
                    <div class="crm-table-wrapper">
                        <table class="crm-data-table">
                            <thead>
                                <tr>
                                    <th>Asesor</th>
                                    <th>Contacto</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Activos</th>
                                    <th class="text-center">Suspendidos</th>
                                    <th class="text-center">Vencidos</th>
                                    <th class="text-center">Sin Contrato</th>
                                    <th class="text-center">Actividad</th>
                                    <th class="text-center">Nuevos</th>
                                    <th class="text-center">Crecimiento</th>
                                    <th class="text-center">Comisión</th>
                                    <th class="text-center">Alerta</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($asesoresData as $asesor): ?>
                                    <?php
                                    $rowClass = '';
                                    if (($asesor['status_color'] ?? '') == 'danger') $rowClass = 'status-critical';
                                    elseif (($asesor['status_color'] ?? '') == 'warning') $rowClass = 'status-warning';
                                    elseif (($asesor['status_color'] ?? '') == 'info') $rowClass = 'status-info';
                                    else $rowClass = 'status-success';
                                    ?>
                                    <tr class="<?= $rowClass ?>">
                                        <td class="font-medium">
                                            <div class="crm-asesor-cell">
                                                <div class="crm-avatar">
                                                    <i class="fas fa-user-circle"></i>
                                                </div>
                                                <div class="crm-asesor-info">
                                                    <strong><?= Html::encode($asesor['nombre_completo'] ?? 'N/A') ?></strong>
                                                    <div class="crm-asesor-code">ID: <?= Html::encode($asesor['codigo_asesor'] ?? 'N/A') ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="crm-contact-info">
                                                <div><i class="fas fa-phone"></i> <?= Html::encode($asesor['telefono'] ?? 'N/A') ?></div>
                                                <div><i class="fas fa-envelope"></i> <?= Html::encode($asesor['email'] ?? 'N/A') ?></div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="crm-stat-badge crm-stat-total"><?= number_format($asesor['total'] ?? 0) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="crm-stat-badge crm-stat-active"><?= number_format($asesor['activos'] ?? 0) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="crm-stat-badge crm-stat-suspended"><?= number_format($asesor['suspendidos'] ?? 0) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="crm-stat-badge crm-stat-expired"><?= number_format($asesor['vencidos'] ?? 0) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="crm-stat-badge crm-stat-no-contract"><?= number_format($asesor['sin_contrato'] ?? 0) ?></span>
                                            <div class="crm-stat-percent"><?= $asesor['porcentaje_sin_contrato'] ?? 0 ?>%</div>
                                        </td>
                                        <td class="text-center">
                                            <div class="crm-progress-indicator">
                                                <div class="crm-progress-track">
                                                    <div class="crm-progress-fill" style="width: <?= $asesor['tasa_actividad'] ?? 0 ?>%; background: <?= ($asesor['tasa_actividad'] ?? 0) < 50 ? '#d13438' : (($asesor['tasa_actividad'] ?? 0) < 70 ? '#ff8c00' : '#107c10') ?>;">
                                                        <span class="crm-progress-label"><?= $asesor['tasa_actividad'] ?? 0 ?>%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <?php if (($asesor['nuevos_este_mes'] ?? 0) > 0): ?>
                                                <span class="crm-new-badge">+<?= number_format($asesor['nuevos_este_mes'] ?? 0) ?></span>
                                            <?php else: ?>
                                                <span class="crm-no-new">0</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (($asesor['growth'] ?? 0) > 0): ?>
                                                <span class="crm-growth-positive"><i class="fas fa-arrow-up"></i> <?= $asesor['growth'] ?>%</span>
                                            <?php elseif (($asesor['growth'] ?? 0) < 0): ?>
                                                <span class="crm-growth-negative"><i class="fas fa-arrow-down"></i> <?= abs($asesor['growth'] ?? 0) ?>%</span>
                                            <?php else: ?>
                                                <span class="crm-growth-neutral">0%</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="crm-comission-value">$<?= number_format($asesor['comision_estimada'] ?? 0, 2) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if (($asesor['alert_level'] ?? 'none') != 'none'): ?>
                                                <div class="crm-alert-indicator alert-<?= $asesor['alert_level'] ?? 'medium' ?>" title="<?= Html::encode($asesor['alert_message'] ?? '') ?>">
                                                    <i class="fas fa-bell"></i>
                                                </div>
                                            <?php else: ?>
                                                <div class="crm-alert-indicator alert-ok">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="crm-action-group">
                                                <a href="<?= Url::to(['/user-datos/index-by-afiliado', 'asesor_id' => $asesor['id'] ?? 0]) ?>" class="crm-action-icon" title="Ver afiliados">
                                                    <i class="fas fa-users"></i>
                                                </a>
                                                <a href="<?= Url::to(['/agente-fuerza/update', 'id' => $asesor['id'] ?? 0]) ?>" class="crm-action-icon" title="Editar comisiones">
                                                    <i class="fas fa-percent"></i>
                                                </a>
                                                <a href="mailto:<?= $asesor['email'] ?? '' ?>" class="crm-action-icon" title="Enviar email">
                                                    <i class="fas fa-envelope"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php if (empty($asesoresData)): ?>
                                    <tr>
                                        <td colspan="13" class="text-center">
                                            <div class="crm-empty-state">
                                                <i class="fas fa-user-friends"></i>
                                                <h4>No hay asesores registrados</h4>
                                                <p>Comience agregando su primer asesor a la agencia</p>
                                                <a href="<?= Url::to(['/user/create', 'role' => 'Asesor']) ?>" class="crm-btn crm-btn-primary">
                                                    <i class="fas fa-user-plus mr-2"></i>Registrar Asesor
                                                </a>
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

    <!-- Comisiones Summary Section -->
    <div class="row">
        <div class="col-12">
            <div class="crm-section">
                <div class="crm-section-header">
                    <div class="crm-section-title">
                        <i class="fas fa-percent"></i>
                        <span>Estructura de Comisiones por Asesor</span>
                    </div>
                    <div class="crm-section-subtitle">Porcentajes asignados por tipo de servicio</div>
                </div>
                <div class="crm-table-container">
                    <div class="crm-table-wrapper">
                        <table class="crm-data-table crm-table-compact">
                            <thead>
                                <tr>
                                    <th>Asesor</th>
                                    <th class="text-center">Venta</th>
                                    <th class="text-center">Asesoría</th>
                                    <th class="text-center">Cobranza</th>
                                    <th class="text-center">Post Venta</th>
                                    <th class="text-center">Registro</th>
                                    <th class="text-center">Comisión Estimada</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($asesoresData as $asesor): ?>
                                    <tr>
                                        <td class="font-medium"><?= Html::encode($asesor['nombre_completo'] ?? 'N/A') ?></td>
                                        <td class="text-center">
                                            <span class="crm-percent-badge"><?= $asesor['porcentajes']['por_venta'] ?? 0 ?>%</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="crm-percent-badge"><?= $asesor['porcentajes']['por_asesor'] ?? 0 ?>%</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="crm-percent-badge"><?= $asesor['porcentajes']['por_cobranza'] ?? 0 ?>%</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="crm-percent-badge"><?= $asesor['porcentajes']['por_post_venta'] ?? 0 ?>%</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="crm-percent-badge"><?= $asesor['porcentajes']['por_registrar'] ?? 0 ?>%</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="crm-comission-total">$<?= number_format($asesor['comision_estimada'] ?? 0, 2) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="crm-section-footer">
                    <a href="<?= Url::to(['/agente-fuerza/index-by-agente', 'agente_id' => $agenciaId]) ?>" class="crm-link-with-icon">
                        <span>Gestionar todas las comisiones</span>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* ============================================
   CRM VENTAS - MICROSOFT FLUENT DESIGN SYSTEM
   ============================================ */

    :root {
        --crm-primary: #0078d4;
        --crm-primary-dark: #106ebe;
        --crm-success: #107c10;
        --crm-success-light: #e8f5e9;
        --crm-warning: #ff8c00;
        --crm-warning-light: #fff4e6;
        --crm-danger: #d13438;
        --crm-danger-light: #fde7e9;
        --crm-teal: #00a1ab;
        --crm-teal-light: #e6f7f8;
        --crm-gray-dark: #323130;
        --crm-gray: #605e5c;
        --crm-gray-light: #f3f2f1;
        --crm-border: #edebe9;
        --crm-shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.04);
        --crm-shadow-md: 0 4px 8px rgba(0, 0, 0, 0.08);
        --crm-shadow-lg: 0 8px 16px rgba(0, 0, 0, 0.12);
    }

    /* Header */
    .crm-header {
        background: linear-gradient(135deg, #1a3a6e 0%, #2a5298 100%);
        border-radius: 16px;
        padding: 28px 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        color: white;
        box-shadow: var(--crm-shadow-md);
    }

    .crm-header-icon {
        font-size: 32px;
        margin-right: 12px;
        opacity: 0.9;
    }

    .crm-header-title {
        font-size: 28px;
        font-weight: 600;
        margin: 0 0 8px 0;
        letter-spacing: -0.3px;
    }

    .crm-header-subtitle {
        font-size: 14px;
        opacity: 0.85;
        margin: 0;
        color: white !important;
    }

    .crm-header-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    /* Buttons */
    .crm-btn {
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        border: none;
    }

    .crm-btn-primary {
        background: white;
        color: #1a3a6e;
    }

    .crm-btn-primary:hover {
        background: #f0f0f0;
        transform: translateY(-2px);
        text-decoration: none;
        color: #1a3a6e;
        box-shadow: var(--crm-shadow-sm);
    }

    .crm-btn-secondary {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        backdrop-filter: blur(4px);
    }

    .crm-btn-secondary:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
        text-decoration: none;
        color: white;
    }

    .crm-btn-outline {
        background: transparent;
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .crm-btn-outline:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: translateY(-2px);
        text-decoration: none;
        color: white;
    }

    /* KPI Cards */
    .crm-kpi-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: var(--crm-shadow-sm);
        border: 1px solid var(--crm-border);
        transition: all 0.2s ease;
    }

    .crm-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--crm-shadow-md);
    }

    .crm-kpi-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
    }

    .bg-warning {
        background: linear-gradient(135deg, #ff8c00, #e67600);
    }

    .bg-danger {
        background: linear-gradient(135deg, #d13438, #b92c30);
    }

    .bg-success {
        background: linear-gradient(135deg, #107c10, #0e6b0e);
    }

    .bg-teal {
        background: linear-gradient(135deg, #00a1ab, #008a92);
    }

    .crm-kpi-content {
        flex: 1;
    }

    .crm-kpi-value {
        font-size: 34px;
        font-weight: 700;
        color: var(--crm-gray-dark);
        line-height: 1.2;
    }

    .crm-kpi-label {
        font-size: 14px;
        color: var(--crm-gray);
        margin-top: 4px;
    }

    .crm-kpi-trend {
        font-size: 12px;
        color: var(--crm-gray);
        margin-top: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Section */
    .crm-section {
        background: white;
        border-radius: 16px;
        box-shadow: var(--crm-shadow-sm);
        border: 1px solid var(--crm-border);
        overflow: hidden;
    }

    .crm-section-header {
        padding: 20px 24px 16px 24px;
        border-bottom: 1px solid var(--crm-border);
        background: var(--crm-gray-light);
    }

    .crm-section-alert {
        border-left: 4px solid var(--crm-warning);
        background: var(--crm-warning-light);
    }

    .crm-section-title {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 18px;
        font-weight: 600;
        color: var(--crm-gray-dark);
        margin-bottom: 4px;
    }

    .crm-section-title i {
        font-size: 20px;
    }

    .crm-section-subtitle {
        font-size: 13px;
        color: var(--crm-gray);
        margin-left: 32px;
    }

    /* Badges */
    .crm-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .crm-badge-warning {
        background: var(--crm-warning);
        color: white;
        margin-left: 12px;
    }

    /* Performer Cards */
    .crm-performer-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        display: flex;
        gap: 16px;
        box-shadow: var(--crm-shadow-sm);
        border: 1px solid var(--crm-border);
        transition: all 0.2s ease;
        height: 100%;
    }

    .crm-performer-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--crm-shadow-lg);
    }

    .crm-performer-card.gold {
        border-top: 4px solid #ffd700;
    }

    .crm-performer-card.silver {
        border-top: 4px solid #c0c0c0;
    }

    .crm-performer-card.bronze {
        border-top: 4px solid #cd7f32;
    }

    .crm-performer-rank {
        width: 64px;
        height: 64px;
        border-radius: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        flex-shrink: 0;
    }

    .crm-performer-info {
        flex: 1;
    }

    .crm-performer-info h4 {
        font-size: 16px;
        font-weight: 600;
        margin: 0 0 12px 0;
        color: var(--crm-gray-dark);
    }

    .crm-performer-stats {
        display: flex;
        gap: 20px;
        margin-bottom: 12px;
    }

    .crm-performer-stat {
        text-align: center;
    }

    .stat-value {
        font-size: 22px;
        font-weight: 700;
        display: block;
        color: var(--crm-gray-dark);
    }

    .stat-label {
        font-size: 11px;
        color: var(--crm-gray);
    }

    .crm-performer-progress {
        height: 8px;
        background: var(--crm-gray-light);
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 12px;
    }

    .crm-progress-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    .crm-performer-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .crm-comision-badge {
        background: var(--crm-teal-light);
        color: var(--crm-teal);
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .crm-link-arrow {
        font-size: 12px;
        color: var(--crm-primary);
        text-decoration: none;
    }

    .crm-link-arrow:hover {
        text-decoration: underline;
    }

    /* Alert Cards */
    .crm-alerts-grid {
        display: flex;
        flex-direction: column;
        gap: 12px;
        padding: 20px 24px;
    }

    .crm-alert-card {
        background: white;
        border-radius: 12px;
        padding: 16px 20px;
        display: flex;
        gap: 16px;
        box-shadow: var(--crm-shadow-sm);
        border: 1px solid var(--crm-border);
        transition: all 0.2s ease;
    }

    .crm-alert-card:hover {
        box-shadow: var(--crm-shadow-md);
    }

    .alert-level-high {
        border-left: 4px solid var(--crm-danger);
        background: var(--crm-danger-light);
    }

    .alert-level-medium {
        border-left: 4px solid var(--crm-warning);
        background: var(--crm-warning-light);
    }

    .crm-alert-icon {
        font-size: 32px;
        flex-shrink: 0;
    }

    .alert-level-high .crm-alert-icon {
        color: var(--crm-danger);
    }

    .alert-level-medium .crm-alert-icon {
        color: var(--crm-warning);
    }

    .crm-alert-content {
        flex: 1;
    }

    .crm-alert-header {
        margin-bottom: 12px;
    }

    .crm-alert-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 6px;
    }

    .crm-alert-title strong {
        font-size: 16px;
        color: var(--crm-gray-dark);
    }

    .crm-alert-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    .alert-high {
        background: var(--crm-danger);
        color: white;
    }

    .alert-medium {
        background: var(--crm-warning);
        color: white;
    }

    .crm-alert-message {
        font-size: 13px;
        color: var(--crm-gray);
    }

    .crm-alert-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 16px;
    }

    .alert-stat {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: var(--crm-gray);
    }

    .alert-stat i {
        width: 16px;
    }

    .crm-alert-actions {
        display: flex;
        gap: 12px;
    }

    .crm-action-btn {
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .crm-action-call {
        background: var(--crm-success);
        color: white;
    }

    .crm-action-call:hover {
        background: #0e6b0e;
        text-decoration: none;
        color: white;
    }

    .crm-action-email {
        background: var(--crm-primary);
        color: white;
    }

    .crm-action-email:hover {
        background: var(--crm-primary-dark);
        text-decoration: none;
        color: white;
    }

    .crm-action-config {
        background: var(--crm-gray);
        color: white;
    }

    .crm-action-config:hover {
        background: #4a4846;
        text-decoration: none;
        color: white;
    }

    /* Tables */
    .crm-table-container {
        overflow-x: auto;
    }

    .crm-table-wrapper {
        min-width: 100%;
    }

    .crm-data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .crm-data-table th {
        text-align: left;
        padding: 14px 16px;
        background: white;
        font-weight: 600;
        font-size: 12px;
        color: var(--crm-gray);
        border-bottom: 1px solid var(--crm-border);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .crm-data-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--crm-border);
        font-size: 13px;
        color: var(--crm-gray-dark);
    }

    .crm-data-table tbody tr:hover {
        background: var(--crm-gray-light);
    }

    .crm-table-compact td,
    .crm-table-compact th {
        padding: 10px 16px;
    }

    /* Status Row Colors */
    .status-critical td {
        background-color: var(--crm-danger-light);
    }

    .status-warning td {
        background-color: var(--crm-warning-light);
    }

    .status-info td {
        background-color: #f0f9ff;
    }

    .status-success td {
        background-color: var(--crm-success-light);
    }

    /* Asesor Cell */
    .crm-asesor-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .crm-avatar {
        width: 36px;
        height: 36px;
        border-radius: 18px;
        background: var(--crm-gray-light);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--crm-primary);
        font-size: 18px;
    }

    .crm-asesor-info {
        line-height: 1.4;
    }

    .crm-asesor-code {
        font-size: 11px;
        color: var(--crm-gray);
    }

    /* Contact Info */
    .crm-contact-info {
        font-size: 12px;
        line-height: 1.6;
    }

    .crm-contact-info i {
        width: 20px;
        color: var(--crm-gray);
    }

    /* Stat Badges */
    .crm-stat-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .crm-stat-total {
        background: var(--crm-gray-light);
        color: var(--crm-gray-dark);
    }

    .crm-stat-active {
        background: var(--crm-success-light);
        color: var(--crm-success);
    }

    .crm-stat-suspended {
        background: var(--crm-warning-light);
        color: var(--crm-warning);
    }

    .crm-stat-expired {
        background: var(--crm-danger-light);
        color: var(--crm-danger);
    }

    .crm-stat-no-contract {
        background: var(--crm-gray-light);
        color: var(--crm-gray);
    }

    .crm-stat-percent {
        font-size: 10px;
        color: var(--crm-gray);
        margin-top: 2px;
    }

    /* Progress Indicator */
    .crm-progress-indicator {
        width: 100px;
        margin: 0 auto;
    }

    .crm-progress-track {
        height: 24px;
        background: var(--crm-gray-light);
        border-radius: 12px;
        overflow: hidden;
    }

    .crm-progress-fill {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .crm-progress-label {
        font-size: 11px;
        font-weight: 600;
        color: white;
    }

    /* New Badge */
    .crm-new-badge {
        display: inline-block;
        padding: 2px 8px;
        background: var(--crm-primary-light, #e6f2fb);
        color: var(--crm-primary);
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .crm-no-new {
        color: var(--crm-gray);
        font-size: 12px;
    }

    /* Growth Indicators */
    .crm-growth-positive {
        color: var(--crm-success);
        font-weight: 500;
    }

    .crm-growth-negative {
        color: var(--crm-danger);
        font-weight: 500;
    }

    .crm-growth-neutral {
        color: var(--crm-gray);
    }

    /* Comission Value */
    .crm-comission-value {
        font-weight: 600;
        color: var(--crm-teal);
    }

    /* Alert Indicator */
    .crm-alert-indicator {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 14px;
        font-size: 14px;
    }

    .alert-high {
        background: var(--crm-danger);
        color: white;
    }

    .alert-medium {
        background: var(--crm-warning);
        color: white;
    }

    .alert-ok {
        background: var(--crm-success-light);
        color: var(--crm-success);
    }

    /* Action Group */
    .crm-action-group {
        display: flex;
        gap: 6px;
        justify-content: center;
    }

    .crm-action-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--crm-gray);
        background: transparent;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .crm-action-icon:hover {
        background: var(--crm-gray-light);
        color: var(--crm-primary);
        text-decoration: none;
    }

    /* Percent Badge */
    .crm-percent-badge {
        display: inline-block;
        padding: 4px 10px;
        background: var(--crm-gray-light);
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        color: var(--crm-gray-dark);
    }

    .crm-comission-total {
        font-weight: 700;
        color: var(--crm-teal);
        font-size: 14px;
    }

    /* Section Footer */
    .crm-section-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--crm-border);
        background: white;
        text-align: right;
    }

    .crm-link-with-icon {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--crm-primary);
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
    }

    .crm-link-with-icon:hover {
        text-decoration: underline;
    }

    /* Empty State */
    .crm-empty-state {
        text-align: center;
        padding: 60px 24px;
    }

    .crm-empty-state i {
        font-size: 64px;
        color: var(--crm-border);
        margin-bottom: 20px;
    }

    .crm-empty-state h4 {
        font-size: 18px;
        color: var(--crm-gray-dark);
        margin-bottom: 8px;
    }

    .crm-empty-state p {
        color: var(--crm-gray);
        margin-bottom: 24px;
    }

    /* Text Utilities */
    .text-center {
        text-align: center;
    }

    .text-gold {
        color: #daa520;
    }

    .font-medium {
        font-weight: 500;
    }

    .small {
        font-size: 11px;
    }

    .text-success {
        color: var(--crm-success);
    }

    .text-warning {
        color: var(--crm-warning);
    }

    .text-danger {
        color: var(--crm-danger);
    }

    .text-blue {
        color: var(--crm-primary);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .crm-header {
            flex-direction: column;
            text-align: center;
        }

        .crm-header-actions {
            width: 100%;
            justify-content: center;
        }

        .crm-performer-stats {
            flex-direction: column;
            gap: 8px;
            align-items: flex-start;
        }

        .crm-alert-stats {
            flex-direction: column;
            gap: 8px;
        }

        .crm-alert-actions {
            flex-wrap: wrap;
        }

        .crm-data-table th,
        .crm-data-table td {
            padding: 10px 12px;
        }
    }

    @media (max-width: 576px) {
        .crm-kpi-card {
            flex-direction: column;
            text-align: center;
        }

        .crm-kpi-icon {
            margin-right: 0;
        }

        .crm-kpi-trend {
            justify-content: center;
        }
    }
</style>