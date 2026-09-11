<?php
// app/views/reporte-atenciones/_uso_servicios_results.php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var array $reportData */
/** @var array $timelineData */
/** @var array $categoryData */
/** @var array $inactiveUsers */
/** @var app\models\SisSiniestroUsoSearch $searchModel */
/** @var bool $isCartera */

// ============================================================
// CALCULAR CITAS Y EMERGENCIAS DESDE LOS DATOS
// ============================================================
$citasCount = 0;
$emergenciasCount = 0;
if (!empty($reportData['detail'])) {
    foreach ($reportData['detail'] as $attention) {
        if ($attention['es_cita']) {
            $citasCount++;
        } else {
            $emergenciasCount++;
        }
    }
}

// ============================================================
// CALCULAR ÍNDICE DE ACTIVIDAD MÉDICA (IAM)
// ============================================================
$iamValue = $reportData['summary']['total_users_with_attentions'] > 0
    ? $reportData['summary']['total_attentions'] / $reportData['summary']['total_users_with_attentions']
    : 0;
$iamDisplay = number_format($iamValue, 1);

if ($iamValue >= 3.0) {
    $iamBadgeColor = '#28a745';
    $iamBadgeText = '🔵 Alta Intensidad';
    $iamInterpretation = 'Los usuarios son muy activos. Mantener engagement.';
} elseif ($iamValue >= 1.5) {
    $iamBadgeColor = '#ff8c00';
    $iamBadgeText = '🟡 Media Intensidad';
    $iamInterpretation = 'Uso regular. Oportunidad de aumentar frecuencia.';
} else {
    $iamBadgeColor = '#d13438';
    $iamBadgeText = '🔴 Baja Intensidad';
    $iamInterpretation = 'Uso esporádico. Necesita campañas de engagement.';
}
?>

<!-- ============================================================ -->
<!-- SCOPE BADGE - Shows current scope selection -->
<!-- ============================================================ -->
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <span class="badge" style="font-size: 1.1rem; padding: 0.7rem 1.8rem; border-radius: 20px; background: <?= $isCartera ? '#6f42c1' : '#0078d4' ?>; color: #ffffff !important;">
                    <i class="fas fa-<?= $isCartera ? 'database' : 'calendar-alt' ?> me-2" style="color: #ffffff !important;"></i>
                    <?php if ($isCartera): ?>
                        📊 Cartera Completa (Todo el histórico)
                    <?php else: ?>
                        📅 Período: <?= Yii::$app->formatter->asDate($searchModel->date_from) ?> al <?= Yii::$app->formatter->asDate($searchModel->date_to) ?>
                    <?php endif; ?>
                </span>
            </div>
            <div>
                <span class="badge bg-secondary" style="font-size: 1rem; padding: 0.6rem 1.5rem; border-radius: 20px;">
                    <i class="fas fa-users me-1"></i>
                    <?= number_format($reportData['summary']['total_affiliates']) ?> afiliados
                </span>
                <?php if ($isCartera): ?>
                    <span class="badge bg-info ms-2" style="font-size: 1rem; padding: 0.6rem 1.5rem; border-radius: 20px;">
                        <i class="fas fa-infinity me-1"></i>
                        Sin límite de fechas
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- USAGE SUMMARY CARDS - 5 TARJETAS EN UNA FILA -->
<!-- ============================================================ -->
<div class="row mb-4">
    <!-- Total Afiliados Activos - AZUL -->
    <div class="col-xl-2-4 col-lg-4 col-md-6 col-sm-12 mb-3">
        <div class="card border-0 shadow-lg h-100 text-center summary-card azul"
            style="border-radius: 16px; background: linear-gradient(135deg, #e8f4fd 0%, #d0e8f7 100%) !important; border-left: 6px solid #0078d4 !important; transition: all 0.3s ease; cursor: default; overflow: hidden; position: relative;">
            <div class="decorative-circle" style="position: absolute; top: -30px; right: -30px; width: 80px; height: 80px; background: rgba(0, 120, 212, 0.08); border-radius: 50%;"></div>
            <div class="card-body p-4" style="position: relative; z-index: 1;">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 icon-circle"
                    style="width: 70px; height: 70px; background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%) !important; box-shadow: 0 4px 15px rgba(0, 120, 212, 0.3);">
                    <i class="fas fa-users" style="color: #ffffff !important; font-size: 2rem;"></i>
                </div>
                <h3 class="fw-bold number" style="color: #0078d4 !important; font-size: 2.8rem; margin-bottom: 0.25rem;">
                    <?= number_format($reportData['summary']['total_affiliates']) ?>
                </h3>
                <p class="mb-0" style="font-size: 1rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; color: #0078d4 !important; opacity: 0.8;">
                    Total Afiliados Activos
                    <span class="ms-1"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="<?= $isCartera ? 'Número total de afiliados con contrato en toda la historia del sistema' : 'Número total de afiliados con contrato activo en el período seleccionado' ?>">
                        <i class="fas fa-info-circle" style="font-size: 1rem; color: #0078d4 !important; cursor: pointer; opacity: 0.7;"></i>
                    </span>
                </p>
            </div>
        </div>
    </div>

    <!-- Usuarios con Servicios - VERDE -->
    <div class="col-xl-2-4 col-lg-4 col-md-6 col-sm-12 mb-3">
        <div class="card border-0 shadow-lg h-100 text-center summary-card verde"
            style="border-radius: 16px; background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%) !important; border-left: 6px solid #28a745 !important; transition: all 0.3s ease; cursor: default; overflow: hidden; position: relative;">
            <div class="decorative-circle" style="position: absolute; top: -30px; right: -30px; width: 80px; height: 80px; background: rgba(40, 167, 69, 0.08); border-radius: 50%;"></div>
            <div class="card-body p-4" style="position: relative; z-index: 1;">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 icon-circle"
                    style="width: 70px; height: 70px; background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);">
                    <i class="fas fa-user-md" style="color: #ffffff !important; font-size: 2rem;"></i>
                </div>
                <h3 class="fw-bold number" style="color: #28a745 !important; font-size: 2.8rem; margin-bottom: 0.25rem;">
                    <?= number_format($reportData['summary']['total_users_with_attentions']) ?>
                </h3>
                <p class="mb-0" style="font-size: 1rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; color: #28a745 !important; opacity: 0.8;">
                    Usuarios que Usaron Servicios
                    <span class="ms-1"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="<?= $isCartera ? 'Afiliados que han utilizado al menos un servicio médico en toda la historia' : 'Afiliados que han utilizado al menos un servicio médico en el período' ?>">
                        <i class="fas fa-info-circle" style="font-size: 1rem; color: #28a745 !important; cursor: pointer; opacity: 0.7;"></i>
                    </span>
                </p>
            </div>
        </div>
    </div>

    <!-- Porcentaje de Uso - NARANJA -->
    <div class="col-xl-2-4 col-lg-4 col-md-6 col-sm-12 mb-3">
        <div class="card border-0 shadow-lg h-100 text-center summary-card naranja"
            style="border-radius: 16px; background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%) !important; border-left: 6px solid #ff8c00 !important; transition: all 0.3s ease; cursor: default; overflow: hidden; position: relative;">
            <div class="decorative-circle" style="position: absolute; top: -30px; right: -30px; width: 80px; height: 80px; background: rgba(255, 140, 0, 0.08); border-radius: 50%;"></div>
            <div class="card-body p-4" style="position: relative; z-index: 1;">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 icon-circle"
                    style="width: 70px; height: 70px; background: linear-gradient(135deg, #ff8c00 0%, #e67e00 100%) !important; box-shadow: 0 4px 15px rgba(255, 140, 0, 0.3);">
                    <i class="fas fa-percent" style="color: #ffffff !important; font-size: 2rem;"></i>
                </div>
                <h3 class="fw-bold number" style="color: #ff8c00 !important; font-size: 2.8rem; margin-bottom: 0.25rem;">
                    <?= number_format($reportData['summary']['usage_percentage'], 1) ?>%
                </h3>
                <p class="mb-0" style="font-size: 1rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; color: #ff8c00 !important; opacity: 0.8;">
                    Porcentaje de Uso
                    <span class="ms-1"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="<?php
                                $totalAffiliates = $reportData['summary']['total_affiliates'];
                                $totalUsers = $reportData['summary']['total_users_with_attentions'];
                                $percentage = $reportData['summary']['usage_percentage'];

                                if ($isCartera) {
                                    echo 'Porcentaje de la cartera total que ha utilizado servicios médicos en TODA la historia del sistema.';
                                } else {
                                    echo 'Porcentaje de la cartera total que utiliza servicios médicos en el período seleccionado. Meta ideal: >70%';
                                }

                                echo '\n\n📊 FÓRMULA DE CÁLCULO:';
                                echo '\n(Usuarios con Servicios ÷ Total Afiliados Activos) × 100';
                                echo '\n\n📝 EJEMPLO CON ESTOS DATOS:';
                                echo '\n(' . number_format($totalUsers) . ' ÷ ' . number_format($totalAffiliates) . ') × 100 = ' . number_format($percentage, 1) . '%';

                                if ($isCartera) {
                                    echo '\n\n💡 Este porcentaje representa la penetración histórica de servicios en toda la cartera.';
                                } else {
                                    echo '\n\n💡 Este porcentaje representa la penetración de servicios en el período actual.';
                                }
                                ?>">
                        <i class="fas fa-info-circle" style="font-size: 1rem; color: #ff8c00 !important; cursor: pointer; opacity: 0.7;"></i>
                    </span>
                </p>
                <?php
                $usagePercent = $reportData['summary']['usage_percentage'];
                $progressColor = $usagePercent >= 70 ? 'success' : ($usagePercent >= 40 ? 'warning' : 'danger');
                ?>
                <div class="progress mt-2" style="height: 6px; border-radius: 4px; background: rgba(255, 140, 0, 0.15);">
                    <div class="progress-bar bg-<?= $progressColor ?>"
                        role="progressbar"
                        style="width: <?= min($usagePercent, 100) ?>%; border-radius: 4px;"
                        aria-valuenow="<?= $usagePercent ?>"
                        aria-valuemin="0"
                        aria-valuemax="100"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Índice de Actividad Médica (IAM) - MORADO -->
    <div class="col-xl-2-4 col-lg-4 col-md-6 col-sm-12 mb-3">
        <div class="card border-0 shadow-lg h-100 text-center summary-card iam"
            style="border-radius: 16px; background: linear-gradient(135deg, #f3e8ff 0%, #e0ccff 100%) !important; border-left: 6px solid #6f42c1 !important; transition: all 0.3s ease; cursor: default; overflow: hidden; position: relative;">
            <div class="decorative-circle" style="position: absolute; top: -30px; right: -30px; width: 80px; height: 80px; background: rgba(111, 66, 193, 0.08); border-radius: 50%;"></div>
            <div class="card-body p-4" style="position: relative; z-index: 1;">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 icon-circle"
                    style="width: 70px; height: 70px; background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%) !important; box-shadow: 0 4px 15px rgba(111, 66, 193, 0.3);">
                    <i class="fas fa-chart-line" style="color: #ffffff !important; font-size: 2rem;"></i>
                </div>
                <h3 class="fw-bold number" style="color: #6f42c1 !important; font-size: 2.8rem; margin-bottom: 0.25rem;">
                    <?php
                    $iam = $reportData['summary']['total_users_with_attentions'] > 0
                        ? number_format($reportData['summary']['total_attentions'] / $reportData['summary']['total_users_with_attentions'], 1)
                        : '0.0';
                    echo $iam;
                    ?>
                </h3>
                <p class="mb-2" style="font-size: 1rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; color: #6f42c1 !important; opacity: 0.8;">
                    Índice de Actividad Médica
                    <span class="ms-1"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="<?php
                                $totalAttentions = $reportData['summary']['total_attentions'];
                                $totalUsers = $reportData['summary']['total_users_with_attentions'];
                                $iamValue = $totalUsers > 0 ? round($totalAttentions / $totalUsers, 1) : 0;

                                echo '📊 ÍNDICE DE ACTIVIDAD MÉDICA (IAM)';
                                echo '\n\nMide la intensidad de uso de los servicios médicos.';
                                echo '\n\n📝 FÓRMULA DE CÁLCULO:';
                                echo '\nTotal Atenciones ÷ Usuarios con Servicios';
                                echo '\n\n📝 EJEMPLO CON ESTOS DATOS:';
                                echo '\n(' . number_format($totalAttentions) . ' ÷ ' . number_format($totalUsers) . ') = ' . number_format($iamValue, 1) . ' atenciones/usuario';
                                echo '\n\n📊 INTERPRETACIÓN:';
                                echo '\n• > 3.0 → Alta intensidad (usuarios muy activos)';
                                echo '\n• 1.5 - 3.0 → Media intensidad (uso regular)';
                                echo '\n• < 1.5 → Baja intensidad (uso esporádico)';
                                ?>">
                        <i class="fas fa-info-circle" style="font-size: 1rem; color: #6f42c1 !important; cursor: pointer; opacity: 0.7;"></i>
                    </span>
                </p>
                <!-- Interpretación visual -->
                <div style="display: flex; justify-content: center;">
                    <span class="badge" style="background: <?= $iamBadgeColor ?>; color: #ffffff; font-size: 0.8rem; padding: 0.35rem 1rem; border-radius: 20px;">
                        <?= $iamBadgeText ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Atenciones - ROJO CON DESGLOSE + SERVICIOS USADOS -->
    <div class="col-xl-2-4 col-lg-4 col-md-6 col-sm-12 mb-3">
        <div class="card border-0 shadow-lg h-100 text-center summary-card rojo"
            style="border-radius: 16px; background: linear-gradient(135deg, #fde8e8 0%, #f5d0d0 100%) !important; border-left: 6px solid #d13438 !important; transition: all 0.3s ease; cursor: default; overflow: hidden; position: relative;">
            <div class="decorative-circle" style="position: absolute; top: -30px; right: -30px; width: 80px; height: 80px; background: rgba(209, 52, 56, 0.08); border-radius: 50%;"></div>
            <div class="card-body p-4" style="position: relative; z-index: 1;">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 icon-circle"
                    style="width: 80px; height: 80px; background: linear-gradient(135deg, #d13438 0%, #b92b2f 100%) !important; box-shadow: 0 4px 15px rgba(209, 52, 56, 0.3);">
                    <i class="fas fa-stethoscope" style="color: #ffffff !important; font-size: 2.5rem;"></i>
                </div>
                <h3 class="fw-bold number" style="color: #d13438 !important; font-size: 3rem; margin-bottom: 0.25rem;">
                    <?= number_format($reportData['summary']['total_attentions']) ?>
                </h3>
                <p class="mb-2" style="font-size: 1rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; color: #d13438 !important; opacity: 0.8;">
                    Total Atenciones Realizadas
                    <span class="ms-1"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="<?= $isCartera ? 'Número total de atenciones médicas (citas + emergencias) en toda la historia' : 'Número total de atenciones médicas (citas + emergencias) en el período' ?>">
                        <i class="fas fa-info-circle" style="font-size: 1rem; color: #d13438 !important; cursor: pointer; opacity: 0.7;"></i>
                    </span>
                </p>
                <!-- Desglose de Citas y Emergencias -->
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <div class="p-2 rounded" style="background: rgba(209, 52, 56, 0.08); border-left: 3px solid #d13438;">
                            <div style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.3px; color: #d13438; opacity: 0.7;">📅 Citas</div>
                            <div style="font-size: 1.8rem; font-weight: 700; color: #d13438;">
                                <?php echo number_format($citasCount); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 rounded" style="background: rgba(209, 52, 56, 0.08); border-left: 3px solid #ff8c00;">
                            <div style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.3px; color: #2e251a; opacity: 0.7;">🚑 Emergencias</div>
                            <div style="font-size: 1.8rem; font-weight: 700; color: #ff8c00;">
                                <?php echo number_format($emergenciasCount); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <hr style="margin: 0.5rem 0; border-top: 1px solid rgba(209, 52, 56, 0.15);">
                <div class="row g-2 mt-2">
                    <div class="col-12">
                        <div style="display: flex; justify-content: center; align-items: center; gap: 20px; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.3px; color: #d13438; opacity: 0.7;">📊 Servicios Usados</span>
                                <span style="font-size: 1.8rem; font-weight: 700; color: #d13438;">
                                    <?= number_format($reportData['summary']['total_services_used']) ?>
                                </span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.3px; color: #d13438; opacity: 0.7;">📈 Promedio</span>
                                <span style="font-size: 1.8rem; font-weight: 700; color: #d13438;">
                                    <?php
                                    $avgServices = $reportData['summary']['total_attentions'] > 0
                                        ? number_format($reportData['summary']['total_services_used'] / $reportData['summary']['total_attentions'], 2)
                                        : '0.00';
                                    echo $avgServices;
                                    ?>
                                </span>
                                <span style="font-size: 0.85rem; color: #d13438; opacity: 0.7;">serv/atención</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Usuarios Sin Uso - CORAL/ROJO CLARO -->
    <div class="col-xl-2-4 col-lg-4 col-md-6 col-sm-12 mb-3">
        <div class="card border-0 shadow-lg h-100 text-center summary-card sinuso"
            style="border-radius: 16px; background: linear-gradient(135deg, #fde8e8 0%, #f5d0d0 100%) !important; border-left: 6px solid #d13438 !important; transition: all 0.3s ease; cursor: default; overflow: hidden; position: relative;">
            <div class="decorative-circle" style="position: absolute; top: -30px; right: -30px; width: 80px; height: 80px; background: rgba(209, 52, 56, 0.08); border-radius: 50%;"></div>
            <div class="card-body p-4" style="position: relative; z-index: 1;">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 icon-circle"
                    style="width: 70px; height: 70px; background: linear-gradient(135deg, #d13438 0%, #b92b2f 100%) !important; box-shadow: 0 4px 15px rgba(209, 52, 56, 0.3);">
                    <i class="fas fa-user-slash" style="color: #ffffff !important; font-size: 2rem;"></i>
                </div>
                <h3 class="fw-bold number" style="color: #d13438 !important; font-size: 2.8rem; margin-bottom: 0.25rem;">
                    <?php
                    $usuariosSinUso = $reportData['summary']['total_affiliates'] - $reportData['summary']['total_users_with_attentions'];
                    echo number_format($usuariosSinUso);
                    ?>
                </h3>
                <p class="mb-2" style="font-size: 1rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; color: #d13438 !important; opacity: 0.8;">
                    Usuarios Sin Uso
                    <span class="ms-1"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="<?php
                                $totalAffiliates = $reportData['summary']['total_affiliates'];
                                $totalUsers = $reportData['summary']['total_users_with_attentions'];
                                $sinUso = $totalAffiliates - $totalUsers;

                                echo '👤 USUARIOS SIN USO';
                                echo '\n\nAfiliados activos que NO han utilizado ningún servicio médico.';
                                echo '\n\n📝 FÓRMULA DE CÁLCULO:';
                                echo '\nTotal Afiliados Activos - Usuarios con Servicios';
                                echo '\n\n📝 EJEMPLO CON ESTOS DATOS:';
                                echo '\n' . number_format($totalAffiliates) . ' - ' . number_format($totalUsers) . ' = ' . number_format($sinUso) . ' usuarios';
                                echo '\n\n💡 OPORTUNIDADES:';
                                echo '\n• Campañas de engagement para reactivar';
                                echo '\n• Comunicación personalizada';
                                echo '\n• Promociones especiales';
                                ?>">
                        <i class="fas fa-info-circle" style="font-size: 1rem; color: #d13438 !important; cursor: pointer; opacity: 0.7;"></i>
                    </span>
                </p>
                <?php
                $totalAffiliates = $reportData['summary']['total_affiliates'];
                $totalUsers = $reportData['summary']['total_users_with_attentions'];
                $sinUso = $totalAffiliates - $totalUsers;
                $porcentajeSinUso = $totalAffiliates > 0 ? round(($sinUso / $totalAffiliates) * 100, 1) : 0;

                if ($porcentajeSinUso <= 30) {
                    $badgeColor = '#28a745';
                    $badgeText = '✅ Bajo % de inactivos';
                } elseif ($porcentajeSinUso <= 50) {
                    $badgeColor = '#ff8c00';
                    $badgeText = '⚠️ % Moderado de inactivos';
                } else {
                    $badgeColor = '#d13438';
                    $badgeText = '🔴 Alto % de inactivos';
                }
                ?>
                <div style="display: flex; justify-content: center; gap: 8px; flex-wrap: wrap;">
                    <span class="badge" style="background: <?= $badgeColor ?>; color: #ffffff; font-size: 0.8rem; padding: 0.35rem 1rem; border-radius: 20px;">
                        <?= $badgeText ?> (<?= $porcentajeSinUso ?>%)
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- COST SUMMARY CARDS -->
<!-- ============================================================ -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body p-4">
                <h5 class="mb-3 fw-bold" style="color: #2c3e50; font-size: 1.4rem;">
                    <i class="fas fa-dollar-sign me-2" style="color: #0078d4;"></i>Resumen de Costos y Márgenes
                    <span class="ms-2"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="Resumen de precios, costos reales y márgenes de los servicios prestados">
                        <i class="fas fa-info-circle" style="font-size: 1rem; color: #6c757d; cursor: pointer;"></i>
                    </span>
                </h5>
                <div class="row g-3">
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="p-3 rounded h-100" style="background: linear-gradient(135deg, #f0f7ff 0%, #e0efff 100%); border-left: 4px solid #0078d4; min-height: 80px;">
                            <div style="font-size: 0.85rem; text-transform: uppercase; color: #6c757d; letter-spacing: 0.5px; font-weight: 600;">
                                💰 Precio Total (Facturación)
                            </div>
                            <div style="font-size: 2.2rem; font-weight: 700; color: #0078d4;">
                                $<?= number_format($reportData['summary']['total_precio'] ?? 0, 2) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="p-3 rounded h-100" style="background: linear-gradient(135deg, #fdf0f0 0%, #fce0e0 100%); border-left: 4px solid #d13438; min-height: 80px;">
                            <div style="font-size: 0.85rem; text-transform: uppercase; color: #6c757d; letter-spacing: 0.5px; font-weight: 600;">
                                📊 Costo Real Total (Clínicas)
                            </div>
                            <?php
                            $totalCostoReal = $reportData['summary']['total_cost_real'] ?? 0;
                            ?>
                            <div style="font-size: 2.2rem; font-weight: 700; color: #d13438;">
                                $<?= number_format($totalCostoReal, 2) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="p-3 rounded h-100" style="background: linear-gradient(135deg, #f0faf0 0%, #e0f5e0 100%); border-left: 4px solid #28a745; min-height: 80px;">
                            <div style="font-size: 0.85rem; text-transform: uppercase; color: #6c757d; letter-spacing: 0.5px; font-weight: 600;">
                                📈 Margen Total (Utilidad)
                            </div>
                            <?php
                            $totalPrecio = $reportData['summary']['total_precio'] ?? 0;
                            $totalMargen = $totalPrecio - $totalCostoReal;
                            ?>
                            <div style="font-size: 2.2rem; font-weight: 700; color: #28a745;">
                                $<?= number_format($totalMargen, 2) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="p-3 rounded h-100" style="background: linear-gradient(135deg, #f5f0ff 0%, #ebe0ff 100%); border-left: 4px solid #6f42c1; min-height: 80px;">
                            <div style="font-size: 0.85rem; text-transform: uppercase; color: #6c757d; letter-spacing: 0.5px; font-weight: 600;">
                                📊 % Margen Promedio
                            </div>
                            <?php
                            $porcentajeMargen = $totalPrecio > 0 ? round(($totalMargen / $totalPrecio) * 100, 1) : 0;
                            $marginColor = $porcentajeMargen >= 50 ? '#28a745' : ($porcentajeMargen >= 30 ? '#ff8c00' : '#d13438');
                            ?>
                            <div style="font-size: 2.2rem; font-weight: 700; color: <?= $marginColor ?>;">
                                <?= number_format($porcentajeMargen, 1) ?>%
                            </div>
                            <div class="progress mt-2" style="height: 5px; border-radius: 4px; background: rgba(0,0,0,0.05);">
                                <div class="progress-bar" style="width: <?= min($porcentajeMargen, 100) ?>%; background: <?= $marginColor ?>; border-radius: 4px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- CHARTS SECTION -->
<!-- ============================================================ -->
<div class="row mb-4">
    <div class="col-xl-6 col-12 mb-3 mb-xl-0">
        <div class="card border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header py-3 border-0" style="background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);">
                <h5 class="mb-0 fw-bold" style="font-size: 1.3rem; color: #ffffff !important;">
                    <i class="fas fa-chart-line me-2" style="color: #ffffff !important;"></i>Evolución Mensual
                </h5>
            </div>
            <div class="card-body p-4">
                <canvas id="usageTimelineChart" height="150"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-6 col-12">
        <div class="card border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header py-3 border-0" style="background: linear-gradient(135deg, #107c10 0%, #0e6a0e 100%);">
                <h5 class="mb-0 fw-bold" style="font-size: 1.3rem; color: #ffffff !important;">
                    <i class="fas fa-chart-pie me-2" style="color: #ffffff !important;"></i>Servicios por Categoría
                </h5>
            </div>
            <div class="card-body p-4">
                <canvas id="usageCategoryChart" height="150"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- USAGE BY CLINIC TABLE - ENHANCED FONT SIZES -->
<!-- ============================================================ -->
<div class="card border-0 shadow-lg mb-4" style="border-radius: 12px; overflow: hidden;">
    <div class="card-header py-3 border-0" style="background: linear-gradient(135deg, #ff8c00 0%, #e67e00 100%);">
        <h5 class="mb-0 fw-bold" style="font-size: 1.4rem; color: #ffffff !important;">
            <i class="fas fa-hospital me-2" style="color: #ffffff !important;"></i>Uso por Clínica
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size: 1.1rem;">
                <thead style="background: #0078d4;">
                    <tr>
                        <th style="font-weight: 600; color: #ffffff !important; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem 1.2rem; min-width: 180px;">Clínica</th>
                        <th class="text-center" style="font-weight: 600; color: #ffffff !important; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem 1.2rem;">Usuarios</th>
                        <th class="text-center" style="font-weight: 600; color: #ffffff !important; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem 1.2rem;">Atenciones</th>
                        <th class="text-center" style="font-weight: 600; color: #ffffff !important; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem 1.2rem;">📅 Citas</th>
                        <th class="text-center" style="font-weight: 600; color: #ffffff !important; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem 1.2rem;">🚑 Emergencias</th>
                        <th class="text-center" style="font-weight: 600; color: #ffffff !important; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem 1.2rem; min-width: 110px;">💰 Precio</th>
                        <th class="text-center" style="font-weight: 600; color: #ffffff !important; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem 1.2rem; min-width: 110px;">📊 Costo Real</th>
                        <th class="text-center" style="font-weight: 600; color: #ffffff !important; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem 1.2rem; min-width: 110px;">📈 Margen</th>
                        <th class="text-center" style="font-weight: 600; color: #ffffff !important; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem 1.2rem; min-width: 90px;">% Margen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reportData['usage_by_clinic'])): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted" style="font-size: 1.2rem;">
                                <i class="fas fa-inbox fa-2x d-block mb-2 opacity-25"></i>
                                <span>No hay datos disponibles para el período seleccionado</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php
                        $grandTotalPrecio = 0;
                        $grandTotalCostoReal = 0;
                        $grandTotalMargen = 0;
                        ?>
                        <?php foreach ($reportData['usage_by_clinic'] as $clinic):
                            $totalPrecio = (float)($clinic['total_precio'] ?? 0);
                            $totalCostoReal = (float)($clinic['total_costo_real'] ?? 0);
                            $totalMargen = (float)($clinic['total_margen'] ?? 0);
                            $margenPorcentaje = (float)($clinic['margen_porcentaje'] ?? 0);

                            $grandTotalPrecio += $totalPrecio;
                            $grandTotalCostoReal += $totalCostoReal;
                            $grandTotalMargen += $totalMargen;

                            $marginColor = $margenPorcentaje >= 50 ? '#28a745' : ($margenPorcentaje >= 30 ? '#ff8c00' : '#d13438');
                        ?>
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td style="padding: 0.9rem 1.2rem; font-size: 1.1rem;">
                                    <strong><?= Html::encode($clinic['clinica_nombre'] ?? 'Sin Asignar') ?></strong>
                                </td>
                                <td class="text-center" style="padding: 0.9rem 1.2rem; font-size: 1.2rem;">
                                    <span class="fw-bold"><?= number_format($clinic['unique_users'] ?? 0) ?></span>
                                </td>
                                <td class="text-center" style="padding: 0.9rem 1.2rem; font-size: 1.2rem;">
                                    <?= number_format($clinic['total_attentions'] ?? 0) ?>
                                </td>
                                <td class="text-center" style="padding: 0.9rem 1.2rem;">
                                    <span class="badge bg-info" style="font-size: 0.9rem; padding: 0.35rem 0.9rem; border-radius: 20px;">
                                        <?= number_format($clinic['citas_count'] ?? 0) ?>
                                    </span>
                                </td>
                                <td class="text-center" style="padding: 0.9rem 1.2rem;">
                                    <span class="badge bg-warning emergency-click"
                                        style="font-size: 0.95rem; padding: 0.45rem 1.1rem; border-radius: 20px; cursor: pointer; transition: all 0.2s;"
                                        data-clinic-id="<?= $clinic['id'] ?? 0 ?>"
                                        data-clinic-name="<?= Html::encode($clinic['clinica_nombre'] ?? 'Clínica') ?>"
                                        data-total-emergencies="<?= $clinic['emergencias_count'] ?? 0 ?>"
                                        data-scope="<?= $searchModel->scope ?? 'cartera' ?>"
                                        data-date-from="<?= $searchModel->date_from ?? '' ?>"
                                        data-date-to="<?= $searchModel->date_to ?? '' ?>">
                                        <?= number_format($clinic['emergencias_count'] ?? 0) ?>
                                        <i class="fas fa-chevron-right ms-1" style="font-size: 0.8rem;"></i>
                                    </span>
                                </td>
                                <td class="text-center" style="padding: 0.9rem 1.2rem; font-weight: 600; color: #0078d4; font-size: 1.15rem;">
                                    $<?= number_format($totalPrecio, 2) ?>
                                </td>
                                <td class="text-center" style="padding: 0.9rem 1.2rem; font-weight: 600; color: #d13438; font-size: 1.15rem;">
                                    $<?= number_format($totalCostoReal, 2) ?>
                                </td>
                                <td class="text-center" style="padding: 0.9rem 1.2rem; font-weight: 700; color: <?= $marginColor ?>; font-size: 1.15rem;">
                                    $<?= number_format($totalMargen, 2) ?>
                                </td>
                                <td class="text-center" style="padding: 0.9rem 1.2rem;">
                                    <span class="badge" style="background: <?= $marginColor ?>; color: #ffffff; font-size: 0.85rem; padding: 0.35rem 0.9rem; border-radius: 20px;">
                                        <?= number_format($margenPorcentaje, 1) ?>%
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($reportData['usage_by_clinic'])): ?>
                    <tfoot>
                        <tr style="background: #e6f2ff; border-top: 2px solid #0078d4;">
                            <td style="padding: 0.9rem 1.2rem; font-weight: 700; font-size: 1.15rem;">TOTALES</td>
                            <td class="text-center" style="padding: 0.9rem 1.2rem; font-weight: 700;"></td>
                            <td class="text-center" style="padding: 0.9rem 1.2rem; font-weight: 700;"></td>
                            <td class="text-center" style="padding: 0.9rem 1.2rem; font-weight: 700;"></td>
                            <td class="text-center" style="padding: 0.9rem 1.2rem; font-weight: 700;"></td>
                            <td class="text-center" style="padding: 0.9rem 1.2rem; font-weight: 700; color: #0078d4; font-size: 1.15rem;">
                                $<?= number_format($grandTotalPrecio, 2) ?>
                            </td>
                            <td class="text-center" style="padding: 0.9rem 1.2rem; font-weight: 700; color: #d13438; font-size: 1.15rem;">
                                $<?= number_format($grandTotalCostoReal, 2) ?>
                            </td>
                            <td class="text-center" style="padding: 0.9rem 1.2rem; font-weight: 700; color: #28a745; font-size: 1.15rem;">
                                $<?= number_format($grandTotalMargen, 2) ?>
                            </td>
                            <td class="text-center" style="padding: 0.9rem 1.2rem;">
                                <?php
                                $grandTotalMargenPorcentaje = $grandTotalPrecio > 0
                                    ? round(($grandTotalMargen / $grandTotalPrecio) * 100, 1)
                                    : 0;
                                $totalMarginColor = $grandTotalMargenPorcentaje >= 50 ? '#28a745' : ($grandTotalMargenPorcentaje >= 30 ? '#ff8c00' : '#d13438');
                                ?>
                                <span class="badge" style="background: <?= $totalMarginColor ?>; color: #ffffff; font-size: 0.9rem; padding: 0.35rem 0.9rem; border-radius: 20px;">
                                    <?= number_format($grandTotalMargenPorcentaje, 1) ?>%
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- TOP USERS TABLE - ENHANCED FONT SIZES -->
<!-- ============================================================ -->
<div class="card border-0 shadow-lg mb-4" style="border-radius: 12px; overflow: hidden;">
    <div class="card-header py-3 border-0" style="background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);">
        <h5 class="mb-0 fw-bold" style="font-size: 1.4rem; color: #ffffff !important;">
            <i class="fas fa-trophy me-2" style="color: #ffffff !important;"></i>Top 10 Usuarios con Mayor Uso
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size: 1.1rem;">
                <thead style="background: #6f42c1;">
                    <tr>
                        <th style="font-weight: 600; color: #ffffff !important; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem 1.2rem;">#</th>
                        <th style="font-weight: 600; color: #ffffff !important; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem 1.2rem;">Afiliado</th>
                        <th class="text-center" style="font-weight: 600; color: #ffffff !important; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem 1.2rem;">Total Atenciones</th>
                        <th class="text-center" style="font-weight: 600; color: #ffffff !important; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem 1.2rem;">Citas</th>
                        <th class="text-center" style="font-weight: 600; color: #ffffff !important; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem 1.2rem;">Emergencias</th>
                        <th class="text-center" style="font-weight: 600; color: #ffffff !important; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem 1.2rem;">Costo Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reportData['top_users'])): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted" style="font-size: 1.2rem;">
                                <i class="fas fa-inbox fa-2x d-block mb-2 opacity-25"></i>
                                <span>No hay datos disponibles para el período seleccionado</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $rank = 1; ?>
                        <?php foreach ($reportData['top_users'] as $user): ?>
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td style="padding: 1rem 1.2rem;">
                                    <span class="badge bg-secondary" style="font-size: 1rem; padding: 0.4rem 1rem; border-radius: 50%;"><?= $rank ?></span>
                                </td>
                                <td style="padding: 1rem 1.2rem;">
                                    <strong style="font-size: 1.15rem;"><?= Html::encode($user['nombres'] . ' ' . $user['apellidos']) ?></strong><br>
                                    <small class="text-muted" style="font-size: 0.95rem;">
                                        <?= Html::encode(($user['tipo_cedula'] ? $user['tipo_cedula'] . '-' : '') . $user['cedula']) ?>
                                    </small>
                                </td>
                                <td class="text-center" style="padding: 1rem 1.2rem;">
                                    <span class="fw-bold" style="font-size: 1.3rem;"><?= number_format($user['total_attentions'] ?? 0) ?></span>
                                </td>
                                <td class="text-center" style="padding: 1rem 1.2rem;">
                                    <span class="badge bg-info" style="font-size: 0.95rem; padding: 0.4rem 1rem; border-radius: 20px;">
                                        <?= number_format($user['citas_count'] ?? 0) ?>
                                    </span>
                                </td>
                                <td class="text-center" style="padding: 1rem 1.2rem;">
                                    <span class="badge bg-warning" style="font-size: 0.95rem; padding: 0.4rem 1rem; border-radius: 20px;">
                                        <?= number_format($user['emergencias_count'] ?? 0) ?>
                                    </span>
                                </td>
                                <td class="text-center" style="padding: 1rem 1.2rem;">
                                    <strong style="color: #0078d4; font-size: 1.2rem;">$<?= number_format($user['total_cost'] ?? 0, 2) ?></strong>
                                </td>
                            </tr>
                            <?php $rank++; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- INACTIVE USERS - ENHANCED FONT SIZES -->
<!-- ============================================================ -->
<?php if (!empty($inactiveUsers)): ?>
    <div class="card border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header py-3 border-0" style="background: linear-gradient(135deg, #dc3545 0%, #b92b2f 100%);">
            <h5 class="mb-0 fw-bold" style="font-size: 1.4rem; color: #ffffff !important;">
                <i class="fas fa-user-slash me-2" style="color: #ffffff !important;"></i>Afiliados Sin Uso de Servicios
                <span class="badge bg-white text-danger ms-2" style="font-size: 0.95rem; padding: 0.4rem 1rem; border-radius: 20px; color: #d13438 !important;">
                    <?= count($inactiveUsers) ?> usuarios
                </span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size: 1.1rem;">
                    <thead style="background: #dc3545;">
                        <tr>
                            <th style="font-weight: 600; color: #ffffff !important; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem 1.2rem;">Afiliado</th>
                            <th style="font-weight: 600; color: #ffffff !important; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem 1.2rem;">Cédula</th>
                            <th style="font-weight: 600; color: #ffffff !important; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1rem 1.2rem;">Clínica</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($inactiveUsers, 0, 20) as $user): ?>
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td style="padding: 1rem 1.2rem;">
                                    <strong style="font-size: 1.15rem;"><?= Html::encode($user['nombres'] . ' ' . $user['apellidos']) ?></strong>
                                </td>
                                <td style="padding: 1rem 1.2rem; font-size: 1.1rem;">
                                    <?= Html::encode(($user['tipo_cedula'] ? $user['tipo_cedula'] . '-' : '') . $user['cedula']) ?>
                                </td>
                                <td style="padding: 1rem 1.2rem; font-size: 1.1rem;">
                                    <?= Html::encode($user['clinica_nombre'] ?? 'No asignada') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (count($inactiveUsers) > 20): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-2" style="font-size: 1.1rem;">
                                    Y <?= number_format(count($inactiveUsers) - 20) ?> usuarios más sin uso de servicios
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- ============================================================ -->
<!-- FOOTER -->
<!-- ============================================================ -->
<div class="mt-4 text-center">
    <p style="color: #6c757d; font-size: 1.1rem; margin-bottom: 0;">
        <i class="fas fa-info-circle me-1" style="color: #0078d4;"></i>
        Reporte de Uso de Servicios Médicos
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <?php if ($isCartera): ?>
            <strong>📊 Cartera Completa</strong>
        <?php else: ?>
            Período: <strong><?= Yii::$app->formatter->asDate($searchModel->date_from) ?></strong>
            al <strong><?= Yii::$app->formatter->asDate($searchModel->date_to) ?></strong>
        <?php endif; ?>
        &nbsp;&nbsp;|&nbsp;&nbsp; Generado: <strong><?= date('d/m/Y H:i:s') ?></strong>
    </p>
</div>

<!-- ============================================================ -->
<!-- EMERGENCY DETAIL PANEL (Slide-in / Off-canvas) -->
<!-- ============================================================ -->
<div id="emergencyPanel" style="display: none; position: fixed; top: 0; right: 0; width: 850px; max-width: 92vw; height: 100vh; background: white; box-shadow: -8px 0 40px rgba(0,0,0,0.15); z-index: 10000; overflow-y: auto; transform: translateX(100%); transition: transform 0.35s cubic-bezier(0.25, 0.8, 0.25, 1); border-radius: 24px 0 0 24px;">
</div>

<div id="emergencyPanelOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.3); z-index: 9999; backdrop-filter: blur(4px); opacity: 0; transition: opacity 0.35s ease;">
</div>

<!-- ============================================================ -->
<!-- CHARTS & PANEL JAVASCRIPT -->
<!-- ============================================================ -->
<?php
$timelineJson = json_encode($timelineData);
$categoryJson = json_encode($categoryData);
$ajaxUrl = Url::to(['emergency-detail']);

$this->registerJs(
    <<<JS
// ============================================================
// ENABLE TOOLTIPS
// ============================================================
$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip({
        container: 'body',
        trigger: 'hover focus',
        animation: true,
        delay: { show: 200, hide: 100 }
    });
});

// ============================================================
// CATEGORY TOGGLE - SINGLE CLEAN HANDLER (NO DUPLICATES)
// ============================================================
// Remove any existing handlers first
document.removeEventListener('click', handleCategoryToggle);

function handleCategoryToggle(e) {
    var toggle = e.target.closest('.category-toggle');
    if (!toggle) return;
    
    e.stopPropagation();
    e.preventDefault();
    
    var category = toggle.dataset.category;
    if (!category) return;
    
    var details = document.querySelector('.category-details[data-category="' + category + '"]');
    if (!details) return;
    
    var arrow = toggle.querySelector('.category-arrow');
    var isHidden = details.style.display === 'none' || details.style.display === '';
    
    if (isHidden) {
        details.style.display = 'block';
        if (arrow) {
            arrow.style.transform = 'rotate(180deg)';
        }
    } else {
        details.style.display = 'none';
        if (arrow) {
            arrow.style.transform = 'rotate(0deg)';
        }
    }
}

// Attach the single handler
document.addEventListener('click', handleCategoryToggle);

// ============================================================
// EMERGENCY PANEL - Slide-in / Off-canvas
// ============================================================
$(document).on('click', '.emergency-click', function() {
    var clinicId = $(this).data('clinic-id');
    var clinicName = $(this).data('clinic-name');
    var total = $(this).data('total-emergencies');
    var scope = $(this).data('scope') || 'cartera';
    var dateFrom = $(this).data('date-from') || '';
    var dateTo = $(this).data('date-to') || '';
    
    if (total == 0) {
        Swal.fire({
            icon: 'info',
            title: 'Sin Emergencias',
            text: 'Esta clínica no tiene emergencias registradas.',
            confirmButtonColor: '#0078d4'
        });
        return;
    }
    
    showEmergencyPanelLoading(clinicName);
    
    var requestData = {
        clinic_id: clinicId,
        date_from: dateFrom,
        date_to: dateTo,
        scope: scope
    };
    
    $.ajax({
        url: '{$ajaxUrl}',
        type: 'GET',
        data: requestData,
        dataType: 'json',
        timeout: 30000,
        success: function(response) {
            if (response.success) {
                showEmergencyPanel(response.html);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Error al cargar los detalles.',
                    confirmButtonColor: '#d13438'
                });
                closeEmergencyPanel();
            }
        },
        error: function(xhr, status, error) {
            var errorMsg = 'No se pudieron cargar los detalles de emergencias.';
            try {
                var response = JSON.parse(xhr.responseText);
                if (response.message) {
                    errorMsg = response.message;
                }
            } catch (e) {
                if (xhr.responseText && xhr.responseText.indexOf('<') !== -1) {
                    errorMsg = 'Error del servidor. Por favor, revise los logs.';
                }
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Error de Conexión',
                text: errorMsg,
                confirmButtonColor: '#d13438'
            });
            closeEmergencyPanel();
        }
    });
});

function showEmergencyPanelLoading(clinicName) {
    var panel = document.getElementById('emergencyPanel');
    var overlay = document.getElementById('emergencyPanelOverlay');
    
    panel.innerHTML = `
        <div style="display: flex; justify-content: center; align-items: center; height: 100vh; flex-direction: column; padding: 2rem; background: #f8f9fa;">
            <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p style="margin-top: 1.5rem; font-size: 1.2rem; color: #2c3e50; font-weight: 500;">
                Cargando detalles de <strong>` + clinicName + `</strong>
            </p>
            <p style="color: #6c757d; font-size: 0.95rem;">Analizando las emergencias...</p>
            <div style="margin-top: 1rem; width: 200px; height: 4px; background: #e9ecef; border-radius: 4px; overflow: hidden;">
                <div style="width: 60%; height: 100%; background: linear-gradient(90deg, #0078d4, #28a745); border-radius: 4px; animation: loadingProgress 1.5s ease-in-out infinite;"></div>
            </div>
        </div>
    `;
    
    panel.style.display = 'block';
    overlay.style.display = 'block';
    
    setTimeout(function() {
        panel.style.transform = 'translateX(0)';
        overlay.style.opacity = '1';
    }, 50);
}

// ============================================================
// SHOW EMERGENCY PANEL
// ============================================================
function showEmergencyPanel(html) {
    var panel = document.getElementById('emergencyPanel');
    var overlay = document.getElementById('emergencyPanelOverlay');
    
    // Remove ALL scripts from the HTML - we don't want to execute them
    var cleanHtml = html.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
    
    // Insert clean HTML (NO SCRIPTS)
    panel.innerHTML = cleanHtml;
    panel.style.display = 'block';
    overlay.style.display = 'block';
    
    // Attach close button handler
    var closeBtn = panel.querySelector('.emergency-panel-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            closeEmergencyPanel();
        });
    }
    
    setTimeout(function() {
        panel.style.transform = 'translateX(0)';
        overlay.style.opacity = '1';
    }, 50);
    
    overlay.onclick = function() {
        closeEmergencyPanel();
    };
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeEmergencyPanel();
        }
    });
}

// ============================================================
// CLOSE EMERGENCY PANEL
// ============================================================
function closeEmergencyPanel() {
    var panel = document.getElementById('emergencyPanel');
    var overlay = document.getElementById('emergencyPanelOverlay');
    
    panel.style.transform = 'translateX(100%)';
    overlay.style.opacity = '0';
    
    setTimeout(function() {
        panel.style.display = 'none';
        overlay.style.display = 'none';
        // Clear panel content to reset state
        panel.innerHTML = '';
    }, 350);
}

window.closeEmergencyPanel = closeEmergencyPanel;

// ============================================================
// CHARTS
// ============================================================
var timelineData = {$timelineJson};
var categoryData = {$categoryJson};

if (timelineData && timelineData.length > 0) {
    var labels = timelineData.map(function(item) { return item.month; });
    var users = timelineData.map(function(item) { return parseInt(item.unique_users) || 0; });
    var attentions = timelineData.map(function(item) { return parseInt(item.total_attentions) || 0; });
    
    var ctx1 = document.getElementById('usageTimelineChart').getContext('2d');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Usuarios Únicos',
                    data: users,
                    backgroundColor: 'rgba(0, 120, 212, 0.7)',
                    borderColor: '#0078d4',
                    borderWidth: 2,
                    borderRadius: 4
                },
                {
                    label: 'Total Atenciones',
                    data: attentions,
                    backgroundColor: 'rgba(16, 124, 16, 0.7)',
                    borderColor: '#107c10',
                    borderWidth: 2,
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            size: 11,
                            weight: '600'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: '#ffffff',
                    titleColor: '#1a1a1a',
                    bodyColor: '#3a3a3a',
                    borderColor: '#e9ecef',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 12
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: { size: 11 }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.06)'
                    }
                },
                x: {
                    ticks: {
                        font: { size: 10 },
                        maxRotation: 45,
                        minRotation: 0
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
} else {
    var el = document.getElementById('usageTimelineChart');
    if (el) {
        el.parentElement.innerHTML = 
            '<div class="text-center py-4 text-muted">' +
            '<i class="fas fa-chart-line fa-2x d-block mb-2 opacity-25"></i>' +
            '<span style="font-size: 1.1rem;">No hay datos suficientes para mostrar la evolución</span>' +
            '</div>';
    }
}

if (categoryData && categoryData.length > 0) {
    var categoryLabels = categoryData.map(function(item) { return item.area_nombre || 'Sin Categoría'; });
    var categoryUses = categoryData.map(function(item) { return parseInt(item.total_uses) || 0; });
    
    var colors = ['#0078d4', '#107c10', '#ff8c00', '#d13438', '#6f42c1', '#17a2b8', '#fd7e14', '#20c997'];
    
    var ctx2 = document.getElementById('usageCategoryChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryUses,
                backgroundColor: colors.slice(0, categoryLabels.length),
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'right',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: {
                            size: 11,
                            weight: '500'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: '#ffffff',
                    titleColor: '#1a1a1a',
                    bodyColor: '#3a3a3a',
                    borderColor: '#e9ecef',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            var total = context.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                            var percentage = total > 0 ? Math.round((context.parsed / total) * 100) : 0;
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
} else {
    var el = document.getElementById('usageCategoryChart');
    if (el) {
        el.parentElement.innerHTML = 
            '<div class="text-center py-4 text-muted">' +
            '<i class="fas fa-chart-pie fa-2x d-block mb-2 opacity-25"></i>' +
            '<span style="font-size: 1.1rem;">No hay datos de categorías disponibles</span>' +
            '</div>';
    }
}

var style = document.createElement('style');
style.innerHTML = `
    @keyframes loadingProgress {
        0% { width: 10%; margin-left: 0%; }
        50% { width: 80%; margin-left: 20%; }
        100% { width: 10%; margin-left: 90%; }
    }
`;
document.head.appendChild(style);
JS
);
?>

<!-- ============================================================ -->
<!-- STYLES -->
<!-- ============================================================ -->
<style>
    .tooltip {
        font-family: 'Segoe UI', 'Microsoft Sans Serif', -apple-system, BlinkMacSystemFont, sans-serif;
        font-size: 0.85rem;
        font-weight: 400;
        letter-spacing: 0.2px;
    }

    .tooltip-inner {
        background: #1a1a1a;
        color: #ffffff;
        padding: 10px 18px;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25), 0 2px 8px rgba(0, 0, 0, 0.1);
        max-width: 320px;
        text-align: left;
        line-height: 1.6;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .bs-tooltip-top .arrow::before,
    .bs-tooltip-auto[x-placement^="top"] .arrow::before {
        border-top-color: #1a1a1a;
    }

    .bs-tooltip-bottom .arrow::before,
    .bs-tooltip-auto[x-placement^="bottom"] .arrow::before {
        border-bottom-color: #1a1a1a;
    }

    .bs-tooltip-left .arrow::before,
    .bs-tooltip-auto[x-placement^="left"] .arrow::before {
        border-left-color: #1a1a1a;
    }

    .bs-tooltip-right .arrow::before,
    .bs-tooltip-auto[x-placement^="right"] .arrow::before {
        border-right-color: #1a1a1a;
    }

    .tooltip.show {
        opacity: 1;
        animation: tooltipFadeIn 0.25s ease-in-out;
    }

    @keyframes tooltipFadeIn {
        from {
            opacity: 0;
            transform: translateY(6px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card-header .fw-bold,
    .card-header .fw-bold.text-white,
    .card-header .fw-bold i,
    .card-header h5,
    .card-header h5 i,
    .card-header .fa-info-circle {
        color: #ffffff !important;
    }

    .card-header * {
        color: #ffffff !important;
    }

    .card-header .badge.bg-white {
        color: #d13438 !important;
    }

    .card-header .badge.bg-white * {
        color: #d13438 !important;
    }

    .card-header .fa-info-circle:hover {
        color: #ffffff !important;
        opacity: 1 !important;
    }

    .summary-card {
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
        position: relative;
        overflow: hidden;
    }

    .summary-card::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 100%);
        pointer-events: none;
        border-radius: 16px;
    }

    .summary-card:hover {
        transform: translateY(-8px) scale(1.01);
        box-shadow: 0 16px 48px rgba(0, 0, 0, 0.12) !important;
    }

    .summary-card .icon-circle {
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    .summary-card:hover .icon-circle {
        transform: scale(1.08) rotate(-4deg);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2) !important;
    }

    .summary-card .number {
        transition: all 0.3s ease;
    }

    .summary-card:hover .number {
        transform: scale(1.02);
    }

    .summary-card .decorative-circle {
        transition: all 0.5s ease;
    }

    .summary-card:hover .decorative-circle {
        transform: scale(1.5);
        opacity: 0.15;
    }

    .summary-card.azul:hover {
        box-shadow: 0 16px 48px rgba(0, 120, 212, 0.25) !important;
    }

    .summary-card.verde:hover {
        box-shadow: 0 16px 48px rgba(40, 167, 69, 0.25) !important;
    }

    .summary-card.naranja:hover {
        box-shadow: 0 16px 48px rgba(255, 140, 0, 0.25) !important;
    }

    .summary-card.iam:hover {
        box-shadow: 0 16px 48px rgba(111, 66, 193, 0.25) !important;
    }

    .summary-card.rojo:hover {
        box-shadow: 0 16px 48px rgba(209, 52, 56, 0.25) !important;
    }

    .fa-info-circle {
        cursor: pointer;
        transition: color 0.2s ease, transform 0.2s ease;
        vertical-align: middle;
    }

    .fa-info-circle:hover {
        color: #0078d4 !important;
        transform: scale(1.15);
    }

    .text-white .fa-info-circle:hover {
        color: #ffffff !important;
        opacity: 1 !important;
    }

    .emergency-click {
        cursor: pointer !important;
        transition: all 0.25s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
        position: relative;
    }

    .emergency-click:hover {
        transform: scale(1.08) !important;
        box-shadow: 0 4px 20px rgba(255, 193, 7, 0.5) !important;
        background: #ffca2c !important;
        color: #1a1a1a !important;
    }

    .emergency-click:active {
        transform: scale(0.95) !important;
    }

    .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08) !important;
    }

    .table tbody tr:hover {
        background: #f0f7ff !important;
        cursor: default;
    }

    .badge {
        font-weight: 600;
    }

    .badge.bg-success {
        background: #107c10 !important;
    }

    .badge.bg-secondary {
        background: #6c757d !important;
    }

    .progress {
        background: #e9ecef;
    }

    #emergencyPanel {
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }

    #emergencyPanel::-webkit-scrollbar {
        width: 6px;
    }

    #emergencyPanel::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    #emergencyPanel::-webkit-scrollbar-thumb {
        background: #c1c7cd;
        border-radius: 10px;
    }

    #emergencyPanel::-webkit-scrollbar-thumb:hover {
        background: #a8aeb4;
    }

    @media (max-width: 768px) {
        .table thead th {
            font-size: 0.75rem !important;
            padding: 0.5rem 0.4rem !important;
        }

        .table tbody td {
            padding: 0.5rem 0.4rem !important;
            font-size: 0.9rem !important;
        }

        .card-body {
            padding: 0.8rem !important;
        }

        .tooltip-inner {
            font-size: 0.75rem;
            padding: 8px 12px;
            max-width: 220px;
        }

        .fa-info-circle {
            font-size: 1rem !important;
        }

        .summary-card .number {
            font-size: 2rem !important;
        }

        .summary-card .icon-circle {
            width: 55px !important;
            height: 55px !important;
        }

        #emergencyPanel {
            width: 95vw;
            border-radius: 16px 0 0 16px;
        }
    }

    @media (max-width: 576px) {
        .tooltip-inner {
            font-size: 0.7rem;
            padding: 6px 10px;
            max-width: 180px;
        }

        .fa-info-circle {
            font-size: 0.9rem !important;
        }

        .summary-card .number {
            font-size: 1.6rem !important;
        }

        .summary-card .icon-circle {
            width: 45px !important;
            height: 45px !important;
        }

        #emergencyPanel {
            width: 100vw;
            border-radius: 0;
            max-width: 100vw;
        }
    }

    .col-xl-2-4 {
        flex: 0 0 auto;
        width: 20%;
    }

    @media (max-width: 1200px) {
        .col-xl-2-4 {
            width: 25%;
        }
    }

    @media (max-width: 992px) {
        .col-xl-2-4 {
            width: 33.333%;
        }
    }

    @media (max-width: 768px) {
        .col-xl-2-4 {
            width: 50%;
        }
    }

    @media (max-width: 576px) {
        .col-xl-2-4 {
            width: 100%;
        }
    }
</style>