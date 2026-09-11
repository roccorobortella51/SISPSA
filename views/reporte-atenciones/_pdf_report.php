<?php
// app/views/reporte-atenciones/_pdf_report.php

use yii\helpers\Html;
use app\components\UserHelper;

/**
 * @var array $reportData
 * @var app\models\SisSiniestroReporteSearch $searchModel
 * @var string $userRole
 * @var bool $hasClinicAccess
 * @var array|null $accessibleClinicas
 * @var array $detailedClinics
 */
?>

<style>
    /* ========== MICROSOFT STYLES ========== */
    .pdf-report {
        font-family: 'Segoe UI', Arial, sans-serif;
        color: #333333;
        padding: 20px;
        background: #ffffff;
    }

    /* HEADER */
    .pdf-header {
        border-bottom: 4px solid #0078d4;
        padding-bottom: 20px;
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .pdf-header-left {
        flex: 1;
    }

    .pdf-title {
        color: #0078d4;
        font-size: 26px;
        font-weight: 700;
        margin: 0;
        letter-spacing: -0.5px;
    }

    .pdf-subtitle {
        color: #6c757d;
        font-size: 14px;
        margin: 5px 0 0 0;
    }

    .pdf-header-right {
        text-align: right;
        color: #6c757d;
        font-size: 12px;
        flex-shrink: 0;
        padding-left: 20px;
    }

    .badge-restricted {
        display: inline-block;
        background: #107c10;
        color: white;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        margin-top: 5px;
    }

    .badge-all-access {
        display: inline-block;
        background: #0078d4;
        color: white;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        margin-top: 5px;
    }

    /* ACCESS INDICATOR */
    .access-indicator {
        background: #e6f3e6;
        border: 1px solid #107c10;
        border-radius: 4px;
        padding: 10px 18px;
        margin-bottom: 20px;
        color: #107c10;
        font-size: 13px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .access-indicator .clinic-list {
        font-weight: 600;
        font-size: 12px;
        color: #2c3e50;
    }

    .access-indicator-all {
        background: #e6f2ff;
        border: 1px solid #0078d4;
        border-radius: 4px;
        padding: 10px 18px;
        margin-bottom: 20px;
        color: #0078d4;
        font-size: 13px;
    }

    /* SUMMARY CARDS */
    .pdf-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        background: #f8f9fa;
        padding: 20px 25px;
        border-radius: 8px;
        margin-bottom: 25px;
        border: 1px solid #e9ecef;
    }

    .pdf-summary .summary-item {
        flex: 1;
        min-width: 120px;
        text-align: center;
        padding: 5px 10px;
    }

    .pdf-summary .summary-item .number {
        font-size: 22px;
        font-weight: 700;
        color: #0078d4;
        display: block;
    }

    .pdf-summary .summary-item .label {
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .pdf-summary .summary-divider {
        width: 1px;
        background: #dee2e6;
        flex-shrink: 0;
    }

    /* TABLES */
    .pdf-table-wrapper {
        margin-bottom: 25px;
        overflow-x: auto;
    }

    .pdf-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .pdf-table thead th {
        background: #0078d4;
        color: white;
        padding: 10px 12px;
        text-align: center;
        font-weight: 600;
        border: 1px solid #005a9e;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .pdf-table tbody td {
        padding: 8px 12px;
        border: 1px solid #dee2e6;
        text-align: center;
        vertical-align: middle;
    }

    .pdf-table tbody td:first-child {
        text-align: left;
        font-weight: 600;
        color: #2c3e50;
    }

    .pdf-table tbody tr:nth-child(even) {
        background: #f8f9fa;
    }

    .pdf-table tbody tr:hover {
        background: #e9ecef;
    }

    .pdf-table tfoot td {
        background: #e6f2ff;
        font-weight: 700;
        border-top: 2px solid #0078d4;
        padding: 10px 12px;
        text-align: center;
        border: 1px solid #dee2e6;
    }

    .pdf-table tfoot td:first-child {
        text-align: left;
    }

    /* SECTION TITLES */
    .pdf-section-title {
        color: #2c3e50;
        font-size: 18px;
        font-weight: 600;
        margin: 25px 0 15px 0;
        padding-bottom: 8px;
        border-bottom: 2px solid #e9ecef;
        display: flex;
        align-items: center;
    }

    .pdf-section-title .icon {
        margin-right: 10px;
        color: #0078d4;
    }

    /* TYPE FILTER BADGE */
    .type-badge {
        display: inline-block;
        background: #e6f2ff;
        color: #0078d4;
        padding: 2px 12px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 12px;
    }

    /* DETAILED CLINIC SECTIONS */
    .clinic-detail-section {
        margin-top: 30px;
        page-break-inside: avoid;
    }

    .clinic-detail-header {
        background: #0078d4;
        color: white;
        padding: 12px 16px;
        border-radius: 4px;
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 15px;
    }

    .clinic-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px 20px;
        background: #f8f9fa;
        padding: 12px 16px;
        border-radius: 4px;
        margin-bottom: 15px;
        border: 1px solid #e9ecef;
    }

    .clinic-info-grid .info-item {
        display: flex;
        gap: 8px;
    }

    .clinic-info-grid .info-item .label {
        font-weight: 600;
        color: #495057;
        min-width: 80px;
    }

    .clinic-info-grid .info-item .value {
        color: #212529;
    }

    .detail-stats-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 10px;
        margin-bottom: 15px;
    }

    .detail-stats-grid .stat-card {
        background: #f8f9fa;
        padding: 10px 14px;
        border-radius: 4px;
        text-align: center;
        border: 1px solid #e9ecef;
    }

    .detail-stats-grid .stat-card .stat-number {
        font-size: 20px;
        font-weight: 700;
        color: #0078d4;
        display: block;
    }

    .detail-stats-grid .stat-card .stat-label {
        font-size: 10px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .sub-section-title {
        font-size: 14px;
        font-weight: 600;
        color: #2c3e50;
        margin: 15px 0 10px 0;
        padding: 6px 12px;
        background: #e9ecef;
        border-radius: 3px;
    }

    .detail-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
        margin-bottom: 10px;
    }

    .detail-table thead th {
        background: #e9ecef;
        color: #2c3e50;
        padding: 6px 10px;
        text-align: left;
        font-weight: 600;
        border: 1px solid #dee2e6;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .detail-table tbody td {
        padding: 5px 10px;
        border: 1px solid #dee2e6;
    }

    .detail-table tbody tr:nth-child(even) {
        background: #f8f9fa;
    }

    .clinic-spacer {
        height: 20px;
        border-bottom: 2px dashed #dee2e6;
        margin: 30px 0 20px 0;
    }

    /* FOOTER */
    .pdf-footer {
        margin-top: 40px;
        padding-top: 20px;
        border-top: 2px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        color: #6c757d;
    }

    .pdf-footer .footer-left {
        text-align: left;
    }

    .pdf-footer .footer-right {
        text-align: right;
    }

    /* Page break utilities */
    .page-break {
        page-break-before: always;
    }

    .status-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 600;
    }

    .status-badge.active {
        background: #d4edda;
        color: #155724;
    }

    .status-badge.inactive {
        background: #f8d7da;
        color: #721c24;
    }

    .status-badge.appointment {
        background: #cce5ff;
        color: #004085;
    }

    .status-badge.emergency {
        background: #f8d7da;
        color: #721c24;
    }
</style>

<div class="pdf-report">

    <!-- ========== HEADER ========== -->
    <div class="pdf-header">
        <div class="pdf-header-left">
            <div class="pdf-title">
                <span style="color: #0078d4;">🏥</span> Reporte de Atenciones por Clínica
            </div>
            <div class="pdf-subtitle">
                <strong>Período:</strong> <?= Yii::$app->formatter->asDate($reportData['summary']['date_range']['from']) ?>
                al <?= Yii::$app->formatter->asDate($reportData['summary']['date_range']['to']) ?>
                &nbsp;|&nbsp; <strong>Tipo:</strong>
                <span class="type-badge">
                    <?php
                    $typeLabels = [
                        'all' => 'Todas las Atenciones',
                        'citas' => 'Citas Médicas',
                        'siniestros' => 'Emergencias / Siniestros'
                    ];
                    $currentType = $reportData['filters']['type'] ?? 'all';
                    echo $typeLabels[$currentType] ?? 'Todas las Atenciones';
                    ?>
                </span>
                &nbsp;|&nbsp; <strong>Rol:</strong> <?= Html::encode($userRole ?? 'N/A') ?>
                &nbsp;|&nbsp; <strong>Generado:</strong> <?= date('d/m/Y H:i:s') ?>
            </div>
        </div>
        <div class="pdf-header-right">
            <?php if ($hasClinicAccess): ?>
                <div class="badge-restricted">🔒 Acceso Restringido</div>
                <?php if ($accessibleClinicas): ?>
                    <div style="font-size: 10px; color: #107c10; margin-top: 3px;">
                        <?= count($accessibleClinicas) ?> clínica(s) asignada(s)
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="badge-all-access">🌐 Acceso Total</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ========== ACCESS INDICATOR ========== -->
    <?php if ($hasClinicAccess && $accessibleClinicas): ?>
        <div class="access-indicator">
            <span>
                <strong>🔒 Acceso Restringido</strong>
                &nbsp;—&nbsp; Datos limitados a sus clínicas asignadas
            </span>
            <span class="clinic-list">
                <?= implode(', ', array_column($accessibleClinicas, 'nombre')) ?>
            </span>
        </div>
    <?php elseif (!$hasClinicAccess): ?>
        <div class="access-indicator-all">
            🌐 <strong>Acceso Total</strong> &nbsp;—&nbsp; Visualizando todas las clínicas activas
        </div>
    <?php endif; ?>

    <!-- ========== SUMMARY ========== -->
    <div class="pdf-summary">
        <div class="summary-item">
            <span class="number"><?= number_format($reportData['summary']['total_clinics']) ?></span>
            <span class="label">Clínicas Activas</span>
        </div>
        <div class="summary-divider"></div>
        <div class="summary-item">
            <span class="number"><?= number_format($reportData['summary']['total_attentions']) ?></span>
            <span class="label">Total Atenciones</span>
        </div>
        <div class="summary-divider"></div>
        <div class="summary-item">
            <span class="number"><?= number_format($reportData['summary']['total_patients']) ?></span>
            <span class="label">Pacientes Únicos</span>
        </div>
        <div class="summary-divider"></div>
        <div class="summary-item">
            <span class="number">$<?= number_format($reportData['summary']['total_cost'], 2) ?></span>
            <span class="label">Monto Total</span>
        </div>
        <div class="summary-divider"></div>
        <div class="summary-item">
            <span class="number">$<?= number_format($reportData['summary']['avg_cost_per_attention'], 2) ?></span>
            <span class="label">Monto Promedio</span>
        </div>
    </div>

    <!-- ========== MAIN TABLE ========== -->
    <div class="pdf-section-title">
        <span class="icon">📊</span> Detalle por Clínica
    </div>

    <div class="pdf-table-wrapper">
        <table class="pdf-table">
            <thead>
                <tr>
                    <th style="text-align: left; min-width: 140px;">Clínica</th>
                    <th>Estado</th>
                    <th>Total</th>
                    <th>Pacientes</th>
                    <th>Monto Total</th>
                    <th>Citas</th>
                    <th>Emergencias</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalAttentions = 0;
                $totalCost = 0;
                $totalPatients = 0;
                $totalAppointments = 0;
                $totalEmergencies = 0;
                ?>

                <?php if (empty($reportData['data'])): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px 20px; color: #6c757d;">
                            <strong>No hay datos disponibles para el período seleccionado</strong><br>
                            <span style="font-size: 11px;">Intente ajustar los filtros o seleccionar otro período</span>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reportData['data'] as $item):
                        $totalAttentions += $item['total_attentions'];
                        $totalCost += $item['total_cost'];
                        $totalPatients += $item['unique_patients'];
                        $totalAppointments += $item['appointments_count'];
                        $totalEmergencies += $item['emergencies_count'];
                    ?>
                        <tr>
                            <td style="text-align: left;"><?= Html::encode($item['clinic_name']) ?></td>
                            <td><?= Html::encode($item['clinic_status']) ?></td>
                            <td><strong><?= number_format($item['total_attentions']) ?></strong></td>
                            <td><?= number_format($item['unique_patients']) ?></td>
                            <td>$<?= number_format($item['total_cost'], 2) ?></td>
                            <td><?= number_format($item['appointments_count']) ?></td>
                            <td><?= number_format($item['emergencies_count']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($reportData['data'])): ?>
                <tfoot>
                    <tr>
                        <td colspan="2"><strong>TOTALES</strong></td>
                        <td><strong><?= number_format($totalAttentions) ?></strong></td>
                        <td><strong><?= number_format($totalPatients) ?></strong></td>
                        <td><strong>$<?= number_format($totalCost, 2) ?></strong></td>
                        <td><strong><?= number_format($totalAppointments) ?></strong></td>
                        <td><strong><?= number_format($totalEmergencies) ?></strong></td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>

    <!-- ========== DETAILED CLINIC REPORTS ========== -->
    <?php if (!empty($detailedClinics)): ?>
        <div class="page-break"></div>

        <?php foreach ($detailedClinics as $index => $detailData): ?>
            <?php if ($index > 0): ?>
                <div class="clinic-spacer"></div>
            <?php endif; ?>

            <div class="clinic-detail-section">
                <!-- Clinic Header -->
                <div class="clinic-detail-header">
                    🏥 <?= Html::encode($detailData['clinic']->nombre) ?>
                </div>

                <!-- Clinic Info -->
                <div class="clinic-info-grid">
                    <div class="info-item">
                        <span class="label">Estado:</span>
                        <span class="value">
                            <span class="status-badge <?= strtolower($detailData['clinic']->estatus) ?>">
                                <?= Html::encode($detailData['clinic']->estatus) ?>
                            </span>
                        </span>
                    </div>
                    <?php if (!empty($detailData['clinic']->direccion)): ?>
                        <div class="info-item">
                            <span class="label">Dirección:</span>
                            <span class="value"><?= Html::encode($detailData['clinic']->direccion) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($detailData['clinic']->telefono)): ?>
                        <div class="info-item">
                            <span class="label">Teléfono:</span>
                            <span class="value"><?= Html::encode($detailData['clinic']->telefono) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="info-item">
                        <span class="label">Período:</span>
                        <span class="value">
                            <?= Yii::$app->formatter->asDate($detailData['date_range']['from']) ?>
                            al <?= Yii::$app->formatter->asDate($detailData['date_range']['to']) ?>
                        </span>
                    </div>
                </div>

                <?php
                // Calculate stats from attentions
                $attentions = $detailData['attentions'];
                $totalAtt = count($attentions);
                $totalCost = 0;
                $uniquePatients = [];
                $appointmentsCount = 0;
                $emergenciesCount = 0;

                foreach ($attentions as $attention) {
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
                ?>

                <!-- Stats Grid -->
                <div class="detail-stats-grid">
                    <div class="stat-card">
                        <span class="stat-number"><?= $totalAtt ?></span>
                        <span class="stat-label">Total Atenciones</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?= count($uniquePatients) ?></span>
                        <span class="stat-label">Pacientes Únicos</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number">$<?= number_format($totalCost, 2) ?></span>
                        <span class="stat-label">Monto Total</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number">$<?= number_format($totalAtt > 0 ? $totalCost / $totalAtt : 0, 2) ?></span>
                        <span class="stat-label">Monto Promedio</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?= $appointmentsCount ?></span>
                        <span class="stat-label">Citas</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?= $emergenciesCount ?></span>
                        <span class="stat-label">Emergencias</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?= count($attentions) ?></span>
                        <span class="stat-label">Registros</span>
                    </div>
                </div>

                <!-- Most Common Services -->
                <?php if (!empty($detailData['common_baremos'])): ?>
                    <div class="sub-section-title">📋 Servicios Más Utilizados</div>
                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th style="text-align: left;">Servicio</th>
                                <th style="text-align: left;">Descripción</th>
                                <th style="text-align: center;">Veces Usado</th>
                                <th style="text-align: right;">Precio Promedio</th>
                                <th style="text-align: right;">Monto Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detailData['common_baremos'] as $service): ?>
                                <?php
                                // Get the full baremo record to get description
                                $baremo = app\models\Baremo::findOne($service['baremo_id'] ?? null);
                                $descripcion = $baremo ? $baremo->descripcion : '';
                                ?>
                                <tr>
                                    <td style="text-align: left;"><?= Html::encode($service['service_name'] ?? 'N/A') ?></td>
                                    <td style="text-align: left;"><?= Html::encode($descripcion) ?></td>
                                    <td style="text-align: center;"><?= $service['usage_count'] ?? 0 ?></td>
                                    <td style="text-align: right;">$<?= number_format($service['avg_price'] ?? 0, 2) ?></td>
                                    <td style="text-align: right;">$<?= number_format($service['total_cost'] ?? 0, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <!-- Daily Statistics -->
                <?php if (!empty($detailData['daily_stats'])): ?>
                    <div class="sub-section-title">📊 Estadísticas Diarias</div>
                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th style="text-align: left;">Fecha</th>
                                <th style="text-align: center;">Atenciones</th>
                                <th style="text-align: right;">Monto Diario</th>
                                <th style="text-align: center;">Pacientes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detailData['daily_stats'] as $daily): ?>
                                <tr>
                                    <td style="text-align: left;"><?= Yii::$app->formatter->asDate($daily['date']) ?></td>
                                    <td style="text-align: center;"><?= $daily['attentions_count'] ?? 0 ?></td>
                                    <td style="text-align: right;">$<?= number_format($daily['daily_cost'] ?? 0, 2) ?></td>
                                    <td style="text-align: center;"><?= $daily['daily_patients'] ?? 0 ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <!-- Recent Attentions -->
                <?php if (!empty($detailData['attentions'])): ?>
                    <div class="sub-section-title">📋 Atenciones Recientes</div>
                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th style="text-align: left;">Fecha</th>
                                <th style="text-align: left;">Paciente</th>
                                <th style="text-align: left;">Servicios</th>
                                <th style="text-align: left;">Descripción</th>
                                <th style="text-align: right;">Monto</th>
                                <th style="text-align: center;">Tipo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $displayedAttentions = array_slice($detailData['attentions'], 0, 30);
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

                                $typeClass = $attention->es_cita ? 'appointment' : 'emergency';
                                $typeText = $attention->es_cita ? 'Cita' : 'Emergencia';
                            ?>
                                <tr>
                                    <td style="text-align: left;"><?= Yii::$app->formatter->asDate($attention->fecha) ?></td>
                                    <td style="text-align: left;"><?= Html::encode($patientName) ?></td>
                                    <td style="text-align: left;"><?= Html::encode($serviceList) ?></td>
                                    <td style="text-align: left;"><?= Html::encode($descriptionList) ?></td>
                                    <td style="text-align: right;">$<?= number_format($attention->costo_total ?? 0, 2) ?></td>
                                    <td style="text-align: center;">
                                        <span class="status-badge <?= $typeClass ?>"><?= $typeText ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (count($detailData['attentions']) > 30): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: #6c757d; font-style: italic;">
                                        ... y <?= count($detailData['attentions']) - 30 ?> atenciones adicionales
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- ========== FOOTER ========== -->
    <div class="pdf-footer">
        <div class="footer-left">
            <strong>Sistema de Gestión Médica</strong><br>
            Reporte generado automáticamente
        </div>
        <div class="footer-right">
            Página 1 de 1<br>
            <?= date('d/m/Y H:i:s') ?>
        </div>
    </div>

</div>