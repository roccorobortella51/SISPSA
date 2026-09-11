<?php
// app/views/reporte-atenciones/_report_results.php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var array $reportData */
/** @var app\models\SisSiniestroReporteSearch $searchModel */
?>

<!-- ============================================================ -->
<!-- REPORT HEADER - Microsoft Style -->
<!-- ============================================================ -->
<div class="card border-0 shadow-lg mb-4" style="border-radius: 12px;">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="fw-bold mb-1" style="color: #1a1a1a; font-size: 2.4rem; letter-spacing: -0.5px;">
                    <i class="fas fa-chart-bar me-2" style="color: #0078d4;"></i>Reporte de Atenciones por Clínica
                </h2>
                <p class="mb-0" style="color: #5a5a5a; font-size: 1.15rem;">
                    <i class="fas fa-calendar-alt me-1" style="color: #0078d4;"></i>
                    <strong>Período:</strong> <?= Yii::$app->formatter->asDate($reportData['summary']['date_range']['from']) ?>
                    <strong>al</strong> <?= Yii::$app->formatter->asDate($reportData['summary']['date_range']['to']) ?>
                    &nbsp;&nbsp;|&nbsp;&nbsp; <i class="fas fa-hospital me-1" style="color: #0078d4;"></i>
                    <strong>Clínicas:</strong> <?= $reportData['summary']['total_clinics'] ?>
                    <?php if (isset($reportData['filters']['type'])): ?>
                        &nbsp;&nbsp;|&nbsp;&nbsp; <i class="fas fa-filter me-1" style="color: #0078d4;"></i>
                        <strong>Tipo:</strong>
                        <?php
                        $typeLabels = [
                            'all' => 'Todas las Atenciones',
                            'citas' => 'Citas Médicas',
                            'siniestros' => 'Emergencias / Siniestros'
                        ];
                        echo $typeLabels[$reportData['filters']['type']] ?? 'Todas las Atenciones';
                        ?>
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="d-flex flex-wrap gap-3 justify-content-md-end">
                    <a href="#" id="btn-export-excel" class="btn btn-success" style="font-size: 1.05rem; font-weight: 600; padding: 0.7rem 2rem; border-radius: 8px; margin: 0 4px;">
                        <i class="fas fa-file-excel me-2"></i>Excel
                    </a>
                    <a href="#" id="btn-export-pdf" class="btn btn-danger" style="font-size: 1.05rem; font-weight: 600; padding: 0.7rem 2rem; border-radius: 8px; margin: 0 4px;">
                        <i class="fas fa-file-pdf me-2"></i>PDF
                    </a>
                    <button class="btn btn-secondary" onclick="window.print()" style="font-size: 1.05rem; font-weight: 600; padding: 0.7rem 2rem; border-radius: 8px; margin: 0 4px;">
                        <i class="fas fa-print me-2"></i>Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- ============================================================ -->
<!-- SUMMARY CARDS - Microsoft Style with LARGER VALUES -->
<!-- ============================================================ -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card h-100" style="border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border-left: 5px solid #0078d4;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                        style="width: 60px; height: 60px; background: rgba(0, 120, 212, 0.12);">
                        <i class="fas fa-hospital" style="color: #0078d4; font-size: 1.8rem;"></i>
                    </div>
                    <div>
                        <div style="color: #6c757d; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
                            Clínicas Activas
                            <i class="fas fa-info-circle ms-1"
                                style="color: #0078d4; cursor: help; font-size: 0.85rem;"
                                data-toggle="tooltip"
                                data-placement="top"
                                title="Clínicas con estatus Activo que tuvieron al menos una atención en el período seleccionado."></i>
                        </div>
                        <div style="color: #1a1a1a; font-size: 2.8rem; font-weight: 700; line-height: 1.2;"><?= $reportData['summary']['total_clinics'] ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card h-100" style="border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border-left: 5px solid #28a745;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                        style="width: 60px; height: 60px; background: rgba(40, 167, 69, 0.12);">
                        <i class="fas fa-stethoscope" style="color: #28a745; font-size: 1.8rem;"></i>
                    </div>
                    <div>
                        <div style="color: #6c757d; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
                            Total Atenciones
                            <i class="fas fa-info-circle ms-1"
                                style="color: #28a745; cursor: help; font-size: 0.85rem;"
                                data-toggle="tooltip"
                                data-placement="top"
                                title="Suma de todas las visitas (citas y emergencias) registradas en el período. Un paciente que visitó 3 veces cuenta como 3."></i>
                        </div>
                        <div style="color: #1a1a1a; font-size: 2.8rem; font-weight: 700; line-height: 1.2;"><?= number_format($reportData['summary']['total_attentions']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card h-100" style="border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border-left: 5px solid #ff8c00;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                        style="width: 60px; height: 60px; background: rgba(255, 140, 0, 0.12);">
                        <i class="fas fa-users" style="color: #ff8c00; font-size: 1.8rem;"></i>
                    </div>
                    <div>
                        <div style="color: #6c757d; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
                            Pacientes Únicos
                            <i class="fas fa-info-circle ms-1"
                                style="color: #ff8c00; cursor: help; font-size: 0.85rem;"
                                data-toggle="tooltip"
                                data-placement="top"
                                title="Número de pacientes distintos que recibieron al menos una atención médica (cita o emergencia) durante el período seleccionado. Cada paciente se contabiliza una sola vez, independientemente del número de visitas realizadas."></i>
                        </div>
                        <div style="color: #1a1a1a; font-size: 2.8rem; font-weight: 700; line-height: 1.2;"><?= number_format($reportData['summary']['total_patients']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card h-100" style="border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border-left: 5px solid #d13438;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                        style="width: 60px; height: 60px; background: rgba(209, 52, 56, 0.12);">
                        <i class="fas fa-dollar-sign" style="color: #d13438; font-size: 1.8rem;"></i>
                    </div>
                    <div>
                        <div style="color: #6c757d; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
                            Monto Total
                            <i class="fas fa-info-circle ms-1"
                                style="color: #d13438; cursor: help; font-size: 0.85rem;"
                                data-toggle="tooltip"
                                data-placement="top"
                                title="Suma de los costos de todas las atenciones del período. El promedio se calcula dividiendo el monto total entre el total de atenciones."></i>
                        </div>
                        <div style="color: #1a1a1a; font-size: 2.4rem; font-weight: 700; line-height: 1.2;">$<?= number_format($reportData['summary']['total_cost'], 2) ?></div>
                        <div style="color: #6c757d; font-size: 0.9rem;">Prom: $<?= number_format($reportData['summary']['avg_cost_per_attention'], 2) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- DATA TABLE - Microsoft Style -->
<!-- ============================================================ -->
<div class="card border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
    <div class="card-header py-3 border-0" style="background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);">
        <h5 class="mb-0 fw-bold text-white" style="font-size: 1.4rem;">
            <i class="fas fa-table me-2 text-white"></i>Detalle por Clínica
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0" style="font-size: 1.05rem;">
                <thead>
                    <tr style="background: #0078d4;">
                        <th style="min-width: 250px; color: #ffffff; font-weight: 600; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1.1rem 1.2rem;">Clínica</th>
                        <th class="text-center" style="color: #ffffff; font-weight: 600; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1.1rem 1.2rem;">
                            Total Atenciones
                            <i class="fas fa-info-circle ms-1"
                                style="color: #ffffff; cursor: help; font-size: 0.85rem; opacity: 0.9;"
                                data-toggle="tooltip"
                                data-placement="top"
                                title="Suma de todas las visitas (citas y emergencias) registradas en el período."></i>
                        </th>
                        <th class="text-center" style="color: #ffffff; font-weight: 600; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1.1rem 1.2rem;">
                            Pacientes Únicos
                            <i class="fas fa-info-circle ms-1"
                                style="color: #ffffff; cursor: help; font-size: 0.85rem; opacity: 0.9;"
                                data-toggle="tooltip"
                                data-placement="top"
                                title="Número de pacientes distintos que recibieron al menos una atención médica (cita o emergencia) durante el período seleccionado. Cada paciente se contabiliza una sola vez, independientemente del número de visitas realizadas."></i>
                        </th>
                        <th class="text-center" style="color: #ffffff; font-weight: 600; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1.1rem 1.2rem;">
                            Monto Total
                            <i class="fas fa-info-circle ms-1"
                                style="color: #ffffff; cursor: help; font-size: 0.85rem; opacity: 0.9;"
                                data-toggle="tooltip"
                                data-placement="top"
                                title="Suma de los costos de todas las atenciones. El promedio por atención aparece debajo del monto."></i>
                        </th>
                        <th class="text-center" style="width: 120px; color: #ffffff; font-weight: 600; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 1.1rem 1.2rem;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reportData['data'])): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5" style="color: #6c757d; font-size: 1.15rem;">
                                <i class="fas fa-inbox fa-3x d-block mb-3 opacity-25"></i>
                                <strong>No hay datos disponibles</strong><br>
                                <span style="font-size: 1rem;">Intente ajustar los filtros o seleccionar otro período</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reportData['data'] as $index => $item): ?>
                            <tr style="<?= $index % 2 == 0 ? 'background: #ffffff;' : 'background: #f8f9fa;' ?> border-bottom: 1px solid #f0f0f0;">
                                <td style="padding: 1.1rem 1.2rem;">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                                            style="width: 52px; height: 52px; background: rgba(0, 120, 212, 0.10);">
                                            <i class="fas fa-clinic-medical" style="color: #0078d4; font-size: 1.5rem;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold clinic-name" style="color: #1a1a1a; font-size: 1.15rem;"><?= Html::encode($item['clinic_name']) ?></div>
                                            <div style="font-size: 0.9rem; color: #6c757d;">
                                                <span class="badge bg-<?= $item['clinic_status'] == 'Activo' ? 'success' : 'secondary' ?>" style="font-size: 0.8rem; padding: 0.35rem 0.9rem; border-radius: 20px;">
                                                    <?= $item['clinic_status'] ?>
                                                </span>
                                                <span class="ms-2">
                                                    <i class="fas fa-calendar-check text-primary me-1"></i>
                                                    <?= $item['appointments_count'] ?> Citas
                                                </span>
                                                <span class="ms-2">
                                                    <i class="fas fa-ambulance text-danger me-1"></i>
                                                    <?= $item['emergencies_count'] ?> Emergencias
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center" style="padding: 1.1rem 1.2rem;">
                                    <div style="font-size: 1.7rem; font-weight: 700; color: #1a1a1a;"><?= number_format($item['total_attentions']) ?></div>
                                    <div style="font-size: 0.85rem; color: #6c757d;"><?= $item['avg_patient_attentions'] ?> por paciente</div>
                                </td>
                                <td class="text-center" style="padding: 1.1rem 1.2rem;">
                                    <div style="font-size: 1.4rem; font-weight: 600; color: #1a1a1a;"><?= number_format($item['unique_patients']) ?></div>
                                </td>
                                <td class="text-center" style="padding: 1.1rem 1.2rem;">
                                    <div style="font-size: 1.4rem; font-weight: 700; color: #0078d4;">$<?= number_format($item['total_cost'], 2) ?></div>
                                    <div style="font-size: 0.85rem; color: #6c757d;">Prom: $<?= number_format($item['avg_cost'], 2) ?></div>
                                </td>
                                <td class="text-center" style="padding: 1.1rem 1.2rem;">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-outline-primary btn-view-detail"
                                            data-id="<?= $item['id'] ?>"
                                            title="Ver detalles de la clínica"
                                            style="width: 42px; height: 42px; padding: 0; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; border-width: 2px;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="<?= Url::to(['sis-siniestro/por-clinica', 'clinica_id' => $item['id']]) ?>"
                                            class="btn btn-outline-info"
                                            title="Ver todas las atenciones"
                                            style="width: 42px; height: 42px; padding: 0; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; border-width: 2px;">
                                            <i class="fas fa-list"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($reportData['data'])): ?>
                    <tfoot>
                        <tr style="background: #e6f2ff; border-top: 3px solid #0078d4;">
                            <td style="padding: 1.1rem 1.2rem; font-size: 1.15rem;"><strong>TOTALES</strong></td>
                            <td class="text-center" style="padding: 1.1rem 1.2rem; font-size: 1.4rem; font-weight: 700; color: #1a1a1a;">
                                <?= number_format($reportData['summary']['total_attentions']) ?>
                            </td>
                            <td class="text-center" style="padding: 1.1rem 1.2rem; font-size: 1.4rem; font-weight: 700; color: #1a1a1a;">
                                <?= number_format($reportData['summary']['total_patients']) ?>
                            </td>
                            <td class="text-center" style="padding: 1.1rem 1.2rem; font-size: 1.4rem; font-weight: 700; color: #0078d4;">
                                $<?= number_format($reportData['summary']['total_cost'], 2) ?>
                            </td>
                            <td style="padding: 1.1rem 1.2rem;"></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- FOOTER - Microsoft Style -->
<!-- ============================================================ -->
<div class="mt-4 text-center">
    <p style="color: #6c757d; font-size: 0.95rem; margin-bottom: 0;">
        <i class="fas fa-info-circle me-1" style="color: #0078d4;"></i>
        Mostrando <strong><?= count($reportData['data']) ?></strong> clínica(s)
        &nbsp;&nbsp;|&nbsp;&nbsp; Período: <strong><?= Yii::$app->formatter->asDate($reportData['summary']['date_range']['from']) ?></strong>
        al <strong><?= Yii::$app->formatter->asDate($reportData['summary']['date_range']['to']) ?></strong>
        &nbsp;&nbsp;|&nbsp;&nbsp; Generado: <strong><?= date('d/m/Y H:i:s') ?></strong>
    </p>
</div>

<style>
    /* ============================================================
       MICROSOFT STYLES - Clean, Professional, Readable
    ============================================================ */

    /* OVERRIDE BOOTSTRAP TABLE HEADER STYLES - Force white text */
    .table thead th {
        color: #ffffff !important;
        background: #0078d4 !important;
    }

    .table thead th i {
        color: #ffffff !important;
    }

    /* Card hover effects */
    .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08) !important;
    }

    /* Table row hover */
    .table tbody tr:hover {
        background: #f0f7ff !important;
        cursor: default;
    }

    /* Button styles */
    .btn-outline-primary {
        border-color: #0078d4;
        color: #0078d4;
        transition: all 0.2s ease;
    }

    .btn-outline-primary:hover {
        background: #0078d4;
        color: #ffffff;
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(0, 120, 212, 0.3);
    }

    .btn-outline-info {
        border-color: #17a2b8;
        color: #17a2b8;
        transition: all 0.2s ease;
    }

    .btn-outline-info:hover {
        background: #17a2b8;
        color: #ffffff;
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3);
    }

    /* Export buttons */
    .btn-success {
        background: #107c10;
        border-color: #107c10;
        transition: all 0.2s ease;
    }

    .btn-success:hover {
        background: #0e6a0e;
        border-color: #0e6a0e;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 124, 16, 0.3);
    }

    .btn-danger {
        background: #d13438;
        border-color: #d13438;
        transition: all 0.2s ease;
    }

    .btn-danger:hover {
        background: #b92b2f;
        border-color: #b92b2f;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(209, 52, 56, 0.3);
    }

    .btn-secondary {
        transition: all 0.2s ease;
    }

    .btn-secondary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
    }

    /* Badge styles */
    .badge {
        font-weight: 600;
    }

    .badge.bg-success {
        background: #107c10 !important;
    }

    .badge.bg-secondary {
        background: #6c757d !important;
    }

    /* ============================================================
       TOOLTIPS — Microsoft Fluent Style
    ============================================================ */
    .tooltip {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: 0.95rem;
    }

    .tooltip .tooltip-inner {
        background-color: #1a1a1a;
        color: #ffffff;
        padding: 10px 14px;
        border-radius: 6px;
        max-width: 380px;
        text-align: left;
        line-height: 1.5;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
        font-weight: 400;
    }

    .tooltip.bs-tooltip-top .arrow::before,
    .tooltip.bs-tooltip-auto[x-placement^="top"] .arrow::before {
        border-top-color: #1a1a1a;
    }

    .tooltip.bs-tooltip-bottom .arrow::before,
    .tooltip.bs-tooltip-auto[x-placement^="bottom"] .arrow::before {
        border-bottom-color: #1a1a1a;
    }

    .tooltip.bs-tooltip-left .arrow::before,
    .tooltip.bs-tooltip-auto[x-placement^="left"] .arrow::before {
        border-left-color: #1a1a1a;
    }

    .tooltip.bs-tooltip-right .arrow::before,
    .tooltip.bs-tooltip-auto[x-placement^="right"] .arrow::before {
        border-right-color: #1a1a1a;
    }

    /* Responsive adjustments */
    @media (max-width: 992px) {
        .col-xl-3 {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }

    @media (max-width: 768px) {
        .col-xl-3 {
            flex: 0 0 100%;
            max-width: 100%;
        }

        .table thead th {
            font-size: 0.75rem !important;
            padding: 0.6rem 0.5rem !important;
        }

        .table tbody td {
            padding: 0.6rem 0.5rem !important;
            font-size: 0.9rem !important;
        }

        .clinic-name {
            font-size: 1rem !important;
        }

        .btn-view-detail,
        .btn-outline-info {
            width: 36px !important;
            height: 36px !important;
            font-size: 0.9rem !important;
        }

        .card-body {
            padding: 1rem !important;
        }
    }

    @media (max-width: 576px) {
        .number {
            font-size: 1.8rem !important;
        }

        .display-5 {
            font-size: 1.6rem !important;
        }

        .btn-export {
            font-size: 0.85rem !important;
            padding: 0.4rem 1rem !important;
        }
    }
</style>