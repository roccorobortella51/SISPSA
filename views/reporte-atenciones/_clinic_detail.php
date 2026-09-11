<?php
// app/views/reporte-atenciones/_clinic_detail.php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\models\Baremo;

/**
 * @var array $detailData
 */

$clinic = $detailData['clinic'];
$attentions = $detailData['attentions'];
$dailyStats = $detailData['daily_stats'];
$commonBaremos = $detailData['common_baremos'];
$dateRange = $detailData['date_range'];
$filters = $detailData['filters'] ?? [];

// Calculate statistics
$totalAttentions = count($attentions);
$attendedCount = 0;
$totalCost = 0;
$uniquePatients = [];
$appointmentsCount = 0;
$emergenciesCount = 0;

foreach ($attentions as $attention) {
    if ($attention->atendido) {
        $attendedCount++;
    }
    $totalCost += (float)$attention->costo_total;
    if ($attention->iduser) {
        $uniquePatients[$attention->iduser] = true;
    }
    if ($attention->es_cita) {
        $appointmentsCount++;
    } else {
        $emergenciesCount++;
    }
}

$attendanceRate = $totalAttentions > 0 ? round(($attendedCount / $totalAttentions) * 100, 1) : 0;
$avgCost = $totalAttentions > 0 ? $totalCost / $totalAttentions : 0;
?>

<div class="clinic-detail-container">
    <!-- ============================================================ -->
    <!-- CLINIC HEADER -->
    <!-- ============================================================ -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h3 class="fw-bold mb-1" style="color: #1a1a1a; font-size: 1.8rem;">
                <i class="fas fa-hospital me-2" style="color: #0078d4;"></i>
                <?= Html::encode($clinic->nombre) ?>
            </h3>
            <p class="text-muted mb-0" style="font-size: 0.95rem;">
                <span class="badge bg-<?= $clinic->estatus == 'Activo' ? 'success' : 'secondary' ?> me-2">
                    <?= Html::encode($clinic->estatus) ?>
                </span>
                <?php if (!empty($clinic->direccion)): ?>
                    <i class="fas fa-map-marker-alt me-1 ms-2" style="color: #0078d4;"></i>
                    <?= Html::encode($clinic->direccion) ?>
                <?php endif; ?>
                <?php if (!empty($clinic->telefono)): ?>
                    <i class="fas fa-phone me-1 ms-2" style="color: #0078d4;"></i>
                    <?= Html::encode($clinic->telefono) ?>
                <?php endif; ?>
            </p>
            <p class="text-muted small mb-0">
                <i class="fas fa-calendar-alt me-1" style="color: #0078d4;"></i>
                Período: <?= Yii::$app->formatter->asDate($dateRange['from']) ?> al <?= Yii::$app->formatter->asDate($dateRange['to']) ?>
                <?php if (isset($filters['type']) && $filters['type'] !== 'all'): ?>
                    <span class="ms-2 badge bg-info">Tipo: <?= $filters['type'] == 'citas' ? 'Citas' : 'Emergencias' ?></span>
                <?php endif; ?>
            </p>
        </div>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()" style="border-radius: 8px;">
            <i class="fas fa-print me-1"></i> Imprimir
        </button>
    </div>

    <!-- ============================================================ -->
    <!-- STATISTICS CARDS -->
    <!-- ============================================================ -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card border-0 h-100" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border-left: 4px solid #0078d4;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                            style="width: 48px; height: 48px; background: rgba(0, 120, 212, 0.10);">
                            <i class="fas fa-stethoscope" style="color: #0078d4; font-size: 1.4rem;"></i>
                        </div>
                        <div>
                            <div style="color: #6c757d; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 600;">Total Atenciones</div>
                            <div style="font-size: 1.8rem; font-weight: 700; color: #1a1a1a; line-height: 1.2;"><?= number_format($totalAttentions) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card border-0 h-100" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border-left: 4px solid #28a745;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                            style="width: 48px; height: 48px; background: rgba(40, 167, 69, 0.10);">
                            <i class="fas fa-user-md" style="color: #28a745; font-size: 1.4rem;"></i>
                        </div>
                        <div>
                            <div style="color: #6c757d; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 600;">Pacientes Únicos</div>
                            <div style="font-size: 1.8rem; font-weight: 700; color: #1a1a1a; line-height: 1.2;"><?= number_format(count($uniquePatients)) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card border-0 h-100" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border-left: 4px solid #ff8c00;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                            style="width: 48px; height: 48px; background: rgba(255, 140, 0, 0.10);">
                            <i class="fas fa-dollar-sign" style="color: #ff8c00; font-size: 1.4rem;"></i>
                        </div>
                        <div>
                            <div style="color: #6c757d; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 600;">Monto Total</div>
                            <div style="font-size: 1.6rem; font-weight: 700; color: #1a1a1a; line-height: 1.2;">$<?= number_format($totalCost, 2) ?></div>
                            <div style="font-size: 0.8rem; color: #6c757d;">Prom: $<?= number_format($avgCost, 2) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card border-0 h-100" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border-left: 4px solid #17a2b8;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                            style="width: 48px; height: 48px; background: rgba(23, 162, 184, 0.10);">
                            <i class="fas fa-percentage" style="color: #17a2b8; font-size: 1.4rem;"></i>
                        </div>
                        <div>
                            <div style="color: #6c757d; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 600;">Tasa Atención</div>
                            <div style="font-size: 1.8rem; font-weight: 700; color: #1a1a1a; line-height: 1.2;"><?= $attendanceRate ?>%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- TYPE BREAKDOWN -->
    <!-- ============================================================ -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 h-100" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div style="color: #6c757d; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 600;">Citas Médicas</div>
                            <div style="font-size: 1.6rem; font-weight: 700; color: #0078d4; line-height: 1.2;"><?= number_format($appointmentsCount) ?></div>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 48px; height: 48px; background: rgba(0, 120, 212, 0.10);">
                            <i class="fas fa-calendar-check" style="color: #0078d4; font-size: 1.4rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 h-100" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div style="color: #6c757d; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 600;">Emergencias / Siniestros</div>
                            <div style="font-size: 1.6rem; font-weight: 700; color: #d13438; line-height: 1.2;"><?= number_format($emergenciesCount) ?></div>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 48px; height: 48px; background: rgba(209, 52, 56, 0.10);">
                            <i class="fas fa-ambulance" style="color: #d13438; font-size: 1.4rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- MOST COMMON SERVICES -->
    <!-- ============================================================ -->
    <?php if (!empty($commonBaremos)): ?>
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header py-2 border-0" style="background: linear-gradient(135deg, #fff9e6 0%, #fff3cd 100%);">
                <h5 class="mb-0 fw-bold" style="color: #faf9f8;">
                    <i class="fas fa-clipboard-list me-2" style="color: #faf9f8;"></i>
                    Servicios Más Utilizados
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0" style="font-size: 0.9rem;">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th style="padding: 0.7rem 1rem;">Servicio</th>
                                <th style="padding: 0.7rem 1rem;">Descripción</th>
                                <th class="text-center" style="padding: 0.7rem 1rem;">Veces Usado</th>
                                <th class="text-end" style="padding: 0.7rem 1rem;">Precio Promedio</th>
                                <th class="text-end" style="padding: 0.7rem 1rem;">Monto Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($commonBaremos as $service):
                                // Get the full baremo record to get description
                                $baremo = Baremo::findOne($service['baremo_id'] ?? null);
                                $descripcion = $baremo ? $baremo->descripcion : '';
                            ?>
                                <tr>
                                    <td style="padding: 0.6rem 1rem; font-weight: 600;"><?= Html::encode($service['service_name'] ?? 'N/A') ?></td>
                                    <td style="padding: 0.6rem 1rem; color: #6c757d;"><?= Html::encode($descripcion) ?></td>
                                    <td class="text-center" style="padding: 0.6rem 1rem;"><?= $service['usage_count'] ?? 0 ?></td>
                                    <td class="text-end" style="padding: 0.6rem 1rem;">$<?= number_format($service['avg_price'] ?? 0, 2) ?></td>
                                    <td class="text-end" style="padding: 0.6rem 1rem; font-weight: 600; color: #0078d4;">
                                        $<?= number_format($service['total_cost'] ?? 0, 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- ============================================================ -->
    <!-- DAILY STATISTICS -->
    <!-- ============================================================ -->
    <?php if (!empty($dailyStats)): ?>
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header py-2 border-0" style="background: linear-gradient(135deg, #e6f3e6 0%, #d4edda 100%);">
                <h5 class="mb-0 fw-bold" style="color: #f2f5f2;">
                    <i class="fas fa-chart-line me-2" style="color: #f6f7f6;"></i>
                    Estadísticas Diarias
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0" style="font-size: 0.9rem;">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th style="padding: 0.7rem 1rem;">Fecha</th>
                                <th class="text-center" style="padding: 0.7rem 1rem;">Atenciones</th>
                                <th class="text-end" style="padding: 0.7rem 1rem;">Monto Diario</th>
                                <th class="text-center" style="padding: 0.7rem 1rem;">Pacientes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dailyStats as $daily): ?>
                                <tr>
                                    <td style="padding: 0.6rem 1rem; font-weight: 600;"><?= Yii::$app->formatter->asDate($daily['date']) ?></td>
                                    <td class="text-center" style="padding: 0.6rem 1rem;"><?= $daily['attentions_count'] ?? 0 ?></td>
                                    <td class="text-end" style="padding: 0.6rem 1rem; font-weight: 600; color: #0078d4;">
                                        $<?= number_format($daily['daily_cost'] ?? 0, 2) ?>
                                    </td>
                                    <td class="text-center" style="padding: 0.6rem 1rem;"><?= $daily['daily_patients'] ?? 0 ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- ============================================================ -->
    <!-- RECENT ATTENTIONS -->
    <!-- ============================================================ -->
    <?php if (!empty($attentions)): ?>
        <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header py-2 border-0" style="background: linear-gradient(135deg, #fde8e8 0%, #f8d7da 100%);">
                <h5 class="mb-0 fw-bold" style="color: #fbf9f9;">
                    <i class="fas fa-history me-2" style="color: #fafafa;"></i>
                    Atenciones Recientes
                    <span class="badge bg-secondary ms-2"><?= count($attentions) ?></span>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0" style="font-size: 0.9rem;">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th style="padding: 0.7rem 1rem;">Fecha</th>
                                <th style="padding: 0.7rem 1rem;">Paciente</th>
                                <th style="padding: 0.7rem 1rem;">Servicios</th>
                                <th style="padding: 0.7rem 1rem;">Descripción</th>
                                <th class="text-end" style="padding: 0.7rem 1rem;">Monto</th>
                                <th class="text-center" style="padding: 0.7rem 1rem;">Tipo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $displayedAttentions = array_slice($attentions, 0, 30);
                            foreach ($displayedAttentions as $attention):
                                // Get patient name
                                $patientName = 'N/A';
                                if ($attention->afiliado) {
                                    $patientName = trim($attention->afiliado->nombres . ' ' . $attention->afiliado->apellidos);
                                }

                                // Get service names and descriptions
                                $serviceNames = [];
                                $serviceDescriptions = [];
                                foreach ($attention->baremos as $baremo) {
                                    $serviceNames[] = $baremo->nombre_servicio;
                                    $serviceDescriptions[] = $baremo->descripcion;
                                }
                                $serviceList = !empty($serviceNames) ? implode(', ', $serviceNames) : 'N/A';
                                $descriptionList = !empty($serviceDescriptions) ? implode(', ', $serviceDescriptions) : 'N/A';

                                $typeClass = $attention->es_cita ? 'info' : 'danger';
                                $typeText = $attention->es_cita ? 'Cita' : 'Emergencia';
                            ?>
                                <tr>
                                    <td style="padding: 0.6rem 1rem; white-space: nowrap;">
                                        <?= Yii::$app->formatter->asDate($attention->fecha) ?>
                                        <br><small class="text-muted"><?= $attention->hora ?></small>
                                    </td>
                                    <td style="padding: 0.6rem 1rem;">
                                        <strong><?= Html::encode($patientName) ?></strong>
                                    </td>
                                    <td style="padding: 0.6rem 1rem; max-width: 150px;">
                                        <?= Html::encode($serviceList) ?>
                                    </td>
                                    <td style="padding: 0.6rem 1rem; max-width: 200px; color: #6c757d; font-size: 0.85rem;">
                                        <?= Html::encode($descriptionList) ?>
                                    </td>
                                    <td class="text-end" style="padding: 0.6rem 1rem; font-weight: 600; color: #0078d4;">
                                        $<?= number_format($attention->costo_total ?? 0, 2) ?>
                                    </td>
                                    <td class="text-center" style="padding: 0.6rem 1rem;">
                                        <span class="badge bg-<?= $typeClass ?>"><?= $typeText ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (count($attentions) > 30): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-3" style="color: #6c757d; font-style: italic;">
                                        <i class="fas fa-ellipsis-h me-2"></i>
                                        ... y <?= count($attentions) - 30 ?> atenciones adicionales
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    /* ============================================================
       CLINIC DETAIL PANEL STYLES
    ============================================================ */
    .clinic-detail-container {
        padding: 0;
    }

    .clinic-detail-container .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .clinic-detail-container .card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08) !important;
    }

    .clinic-detail-container .table th {
        border-top: none;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.3px;
        color: #495057;
    }

    .clinic-detail-container .table td {
        vertical-align: middle;
    }

    .clinic-detail-container .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 0, 0, 0.02);
    }

    .clinic-detail-container .table-striped tbody tr:hover {
        background-color: rgba(0, 120, 212, 0.05);
    }

    .clinic-detail-container .badge {
        font-weight: 600;
        padding: 0.35rem 0.75rem;
    }

    .clinic-detail-container .badge.bg-success {
        background: #107c10 !important;
    }

    .clinic-detail-container .badge.bg-warning {
        background: #ff8c00 !important;
        color: #ffffff;
    }

    .clinic-detail-container .badge.bg-danger {
        background: #d13438 !important;
    }

    .clinic-detail-container .badge.bg-info {
        background: #0078d4 !important;
    }

    .clinic-detail-container .badge.bg-secondary {
        background: #742121 !important;
    }

    @media (max-width: 768px) {
        .clinic-detail-container .table {
            font-size: 0.8rem;
        }

        .clinic-detail-container .table td,
        .clinic-detail-container .table th {
            padding: 0.4rem 0.5rem !important;
        }

        .clinic-detail-container .card-body {
            padding: 0.5rem !important;
        }
    }
</style>