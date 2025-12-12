<?php
// app/views/reportes/_pagos-resumen-clinicas.php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var array $summaryPorClinica */
/** @var array $summary */
/** @var string $startDate */
/** @var string $endDate */
/** @var string $title */

// Ensure summary has the expected keys with default values
$summary = array_merge([
    'total_monto' => 0,
    'total_count' => 0,
    'conciliados' => 0,
    'pendientes' => 0
], $summary ?? []);

if (!empty($summaryPorClinica)):
?>
    <!-- Microsoft Professional Resumen Section - Adjusted -->
    <div class="col-12 resumen-section mb-4">
        <!-- Panel de Resumen por Clínica - Adjusted Header -->
        <div class="ms-card border-0 shadow-lg mb-4 ms-fade-in"
            style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-left: 4px solid #0078d4;">
            <div class="ms-card-body p-4">
                <div class="row align-items-center">
                    <div class="col-lg-8 mb-3 mb-lg-0">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center shadow"
                                    style="width: 70px; height: 70px; background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);">
                                    <i class="fas fa-hospital text-white" style="font-size: 2.2rem;"></i>
                                </div>
                            </div>
                            <div>
                                <h2 class="display-5 fw-bold mb-1 ms-primary" style="font-size: 1.8rem !important;">
                                    <i class="fas fa-chart-pie me-2"></i>Resumen por Clínica
                                </h2>
                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-chart-bar me-2 ms-primary" style="font-size: 1.3rem;"></i>
                                        <span class="ms-body-lg fw-semibold" style="font-size: 1.3rem !important;">
                                            Distribución de pagos por institución médica
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="far fa-calendar-alt me-2 ms-primary" style="font-size: 1.3rem;"></i>
                                        <span class="ms-body-lg fw-bold" style="font-size: 1.3rem !important;">
                                            <?= $startDate ?> al <?= $endDate ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="d-flex flex-column align-items-lg-end gap-2">
                            <button class="ms-btn ms-btn-danger px-3 py-2 shadow" id="btn-resumen-pdf"
                                style="font-size: 1.3rem !important;">
                                <i class="fas fa-file-pdf me-2" style="font-size: 1.3rem;"></i> Resumen PDF
                            </button>
                            <div class="text-center text-lg-end">
                                <div class="d-inline-block px-3 py-2 rounded-3 shadow-sm"
                                    style="background: white; border-left: 4px solid #107c10;">
                                    <div class="ms-body-sm text-muted mb-1" style="font-size: 1.1rem !important;">
                                        Clínicas Analizadas
                                    </div>
                                    <div class="display-6 fw-bold text-success" style="font-size: 2.2rem !important;">
                                        <?= count($summaryPorClinica) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Resumen Ajustada -->
        <div class="ms-card border-0 shadow-lg p-0 ms-fade-in" style="animation-delay: 0.1s;">
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="ms-table ms-table-striped mb-0" style="min-width: 900px;">
                    <thead style="position: sticky; top: 0; z-index: 10;">
                        <tr style="background: linear-gradient(135deg, #2c3e50 0%, #4a6491 100%) !important;">
                            <th class="ps-4 py-3" style="width: 35%; border-right: 2px solid rgba(255,255,255,0.2);">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-hospital-alt me-2 text-white" style="font-size: 1.3rem;"></i>
                                    <span class="text-white fw-bold" style="font-size: 1.3rem !important;">Clínica</span>
                                </div>
                            </th>
                            <th class="text-center py-3" style="width: 15%; border-right: 2px solid rgba(255,255,255,0.2);">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fas fa-id-card me-2 text-white" style="font-size: 1.3rem;"></i>
                                    <span class="text-white fw-bold" style="font-size: 1.3rem !important;">RIF</span>
                                </div>
                            </th>
                            <th class="text-center py-3" style="width: 15%; border-right: 2px solid rgba(255,255,255,0.2);">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fas fa-receipt me-2 text-white" style="font-size: 1.3rem;"></i>
                                    <span class="text-white fw-bold" style="font-size: 1.3rem !important;">Total Pagos</span>
                                </div>
                            </th>
                            <th class="text-center py-3" style="width: 20%; border-right: 2px solid rgba(255,255,255,0.2);">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fas fa-chart-pie me-2 text-white" style="font-size: 1.3rem;"></i>
                                    <span class="text-white fw-bold" style="font-size: 1.3rem !important;">Estado</span>
                                </div>
                            </th>
                            <th class="text-center py-3 pe-4" style="width: 15%;">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fas fa-money-bill-wave me-2 text-white" style="font-size: 1.3rem;"></i>
                                    <span class="text-white fw-bold" style="font-size: 1.3rem !important;">Total (Bs.)</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $granTotal = 0;
                        $granTotalPagos = 0;
                        $consecutivo = 1;

                        foreach ($summaryPorClinica as $resumen):
                            $resumen = array_merge([
                                'clinica_nombre' => 'Desconocida',
                                'clinica_rif' => 'N/A',
                                'total_monto' => 0,
                                'total_pagos' => 0,
                                'conciliados' => 0,
                                'pendientes' => 0
                            ], $resumen);

                            $granTotal += (float)$resumen['total_monto'];
                            $granTotalPagos += (int)$resumen['total_pagos'];

                            // Calculate percentages for visual indicators
                            $totalPagosClinica = $resumen['total_pagos'];
                            $conciliadosPercent = $totalPagosClinica > 0 ? ($resumen['conciliados'] / $totalPagosClinica) * 100 : 0;
                            $pendientesPercent = $totalPagosClinica > 0 ? ($resumen['pendientes'] / $totalPagosClinica) * 100 : 0;

                            // Calculate percentage of total
                            $porcentajeTotal = $granTotal > 0 ? ($resumen['total_monto'] / $granTotal) * 100 : 0;
                        ?>
                            <tr class="ms-slide-in" style="animation-delay: <?= $consecutivo * 0.05 ?>s; border-bottom: 1px solid #f8f9fa;">
                                <!-- Clínica -->
                                <td class="ps-4 py-2" style="border-right: 2px solid #e9ecef;">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3 text-center">
                                            <span class="ms-badge ms-badge-info shadow-sm"
                                                style="min-width: 45px; padding: 0.5rem; font-size: 1.3rem !important;">
                                                <?= $consecutivo++ ?>
                                            </span>
                                        </div>
                                        <div>
                                            <div class="ms-body-lg fw-bold mb-1" style="font-size: 1.3rem !important;">
                                                <?= Html::encode($resumen['clinica_nombre']) ?>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2" style="width: 150px; max-width: 100%;">
                                                    <div class="progress" style="height: 10px; border-radius: 5px;">
                                                        <div class="progress-bar"
                                                            style="width: <?= min(100, $porcentajeTotal) ?>%; background: linear-gradient(90deg, #0078d4 0%, #50e6ff 100%); border-radius: 5px;">
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="ms-body fw-semibold" style="font-size: 1.2rem !important; color: #0078d4;">
                                                    <?= number_format($porcentajeTotal, 1) ?>%
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- RIF -->
                                <td class="text-center py-2" style="border-right: 2px solid #e9ecef;">
                                    <div class="d-flex align-items-center justify-content-center h-100">
                                        <span class="ms-badge ms-badge-info shadow-sm"
                                            style="font-size: 1.3rem !important; padding: 0.5rem 1rem;">
                                            <?= Html::encode($resumen['clinica_rif']) ?>
                                        </span>
                                    </div>
                                </td>

                                <!-- Total Pagos -->
                                <td class="text-center py-2" style="border-right: 2px solid #e9ecef;">
                                    <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                        <span class="display-6 fw-bold mb-1" style="font-size: 1.4rem !important;">
                                            <?= number_format($resumen['total_pagos']) ?>
                                        </span>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-exchange-alt me-1" style="font-size: 1.1rem; color: #605e5c;"></i>
                                            <small class="ms-body-sm text-muted" style="font-size: 1.1rem !important;">
                                                transacciones
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <!-- Estado -->
                                <td class="text-center py-2" style="border-right: 2px solid #e9ecef;">
                                    <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                        <div class="row g-2 w-100">
                                            <div class="col-6">
                                                <div class="d-flex flex-column align-items-center p-2 rounded-3"
                                                    style="background: rgba(223, 246, 221, 0.5); border: 1px solid #107c10;">
                                                    <span class="display-6 fw-bold text-success mb-1"
                                                        style="font-size: 1.4rem !important;">
                                                        <?= $resumen['conciliados'] ?>
                                                    </span>
                                                    <div class="progress mb-1" style="width: 60px; height: 6px;">
                                                        <div class="progress-bar bg-success"
                                                            style="width: <?= $conciliadosPercent ?>%; border-radius: 3px;">
                                                        </div>
                                                    </div>
                                                    <small class="ms-body-sm text-muted" style="font-size: 1.1rem !important;">
                                                        Conciliados
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="d-flex flex-column align-items-center p-2 rounded-3"
                                                    style="background: rgba(255, 244, 206, 0.5); border: 1px solid #ff8c00;">
                                                    <span class="display-6 fw-bold text-warning mb-1"
                                                        style="font-size: 1.4rem !important;">
                                                        <?= $resumen['pendientes'] ?>
                                                    </span>
                                                    <div class="progress mb-1" style="width: 60px; height: 6px;">
                                                        <div class="progress-bar bg-warning"
                                                            style="width: <?= $pendientesPercent ?>%; border-radius: 3px;">
                                                        </div>
                                                    </div>
                                                    <small class="ms-body-sm text-muted" style="font-size: 1.1rem !important;">
                                                        Pendientes
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Total Monto -->
                                <td class="text-center py-2 pe-4">
                                    <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                        <span class="display-6 fw-bold text-success mb-1" style="font-size: 1.4rem !important;">
                                            <?= Yii::$app->formatter->asCurrency($resumen['total_monto'], 'VES') ?>
                                        </span>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-percentage me-1" style="font-size: 1.1rem; color: #605e5c;"></i>
                                            <small class="ms-body-sm text-muted" style="font-size: 1.1rem !important;">
                                                <?= number_format($porcentajeTotal, 1) ?>% del total
                                            </small>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                    <!-- TOTAL SECTION ADJUSTED -->
                    <tfoot style="position: sticky; bottom: 0; z-index: 10;">
                        <tr style="background: linear-gradient(135deg, #1a252f 0%, #2c3e50 100%);">
                            <td colspan="2" class="ps-4 py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-chart-bar me-3" style="font-size: 1.8rem; color: #ffffff !important;"></i>
                                    <div>
                                        <h3 class="mb-1 fw-bold" style="color: #ffffff !important; font-size: 1.6rem !important;">
                                            <i class="fas fa-calculator me-2"></i>TOTAL GENERAL
                                        </h3>
                                        <p class="mb-0" style="color: rgba(255, 255, 255, 0.85) !important; font-size: 1.3rem !important;">
                                            Resumen consolidado de todas las clínicas
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center py-3">
                                <div class="d-flex flex-column align-items-center">
                                    <h3 class="mb-1 fw-bold" style="color: #ffffff !important; font-size: 1.6rem !important;">
                                        <?= number_format($granTotalPagos) ?>
                                    </h3>
                                    <p class="mb-0" style="color: rgba(255, 255, 255, 0.85) !important; font-size: 1.3rem !important;">
                                        pagos totales
                                    </p>
                                </div>
                            </td>
                            <td class="text-center py-3">
                                <div class="d-flex justify-content-center gap-4">
                                    <div class="text-center">
                                        <h2 class="mb-1 fw-bold" style="color: #ffffff !important; font-size: 1.6rem !important;">
                                            <?= $summary['conciliados'] ?? 0 ?>
                                        </h2>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <div class="me-1" style="width: 12px; height: 12px; border-radius: 50%; background-color: #28a745;"></div>
                                            <p class="mb-0" style="color: rgba(255, 255, 255, 0.85) !important; font-size: 1.3rem !important;">
                                                conciliados
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <h2 class="mb-1 fw-bold" style="color: #ffffff !important; font-size: 1.6rem !important;">
                                            <?= $summary['pendientes'] ?? 0 ?>
                                        </h2>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <div class="me-1" style="width: 12px; height: 12px; border-radius: 50%; background-color: #ffc107;"></div>
                                            <p class="mb-0" style="color: rgba(255, 255, 255, 0.85) !important; font-size: 1.3rem !important;">
                                                pendientes
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center pe-4 py-3">
                                <div class="d-flex flex-column align-items-center">
                                    <h1 class="mb-1 fw-bold" style="color: #ffffff !important; font-size: 1.8rem !important;">
                                        <?= Yii::$app->formatter->asCurrency($granTotal, 'VES') ?>
                                    </h1>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-chart-line me-1" style="color: rgba(255, 255, 255, 0.85); font-size: 1.3rem;"></i>
                                        <p class="mb-0" style="color: rgba(255, 255, 255, 0.85) !important; font-size: 1.3rem !important;">
                                            monto total consolidado
                                        </p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Update the footer section in _pagos-resumen-clinicas.php -->
            <div class="ms-card-footer py-3" style="background: #faf9f8; border-top: 2px solid #0078d4;">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-3 mb-lg-0">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm"
                                style="width: 45px; height: 45px; background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);">
                                <i class="fas fa-chart-line text-white" style="font-size: 1.3rem;"></i>
                            </div>
                            <div>
                                <h4 class="ms-title-sm mb-1" style="font-size: 1.3rem !important;">
                                    <i class="fas fa-chart-pie me-2 ms-primary"></i>Análisis del Reporte
                                </h4>
                                <div class="d-flex flex-wrap gap-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-hospital me-2 ms-primary" style="font-size: 1.1rem;"></i>
                                        <span class="ms-body-lg fw-semibold" style="font-size: 1.2rem !important;">
                                            <?= count($summaryPorClinica) ?> clínica(s) reportadas
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-percentage me-2 ms-primary" style="font-size: 1.1rem;"></i>
                                        <span class="ms-body-lg fw-semibold" style="font-size: 1.2rem !important;">
                                            Distribución porcentual basada en montos
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="d-flex align-items-center justify-content-lg-end flex-wrap gap-3">
                            <!-- Analytics Button Added Here -->
                            <a href="#analytics-section" class="ms-btn ms-btn-primary px-3 py-2 shadow btn-analytics"
                                style="font-size: 1.3rem !important;">
                                <i class="fas fa-chart-bar me-2"></i>Ver Análisis Gráfico
                            </a>

                            <div class="d-flex align-items-center">
                                <div class="rounded-circle me-2"
                                    style="width: 12px; height: 12px; background-color: #107c10; border-radius: 50%;"></div>
                                <span class="ms-body-lg fw-semibold" style="font-size: 1.2rem !important;">
                                    Conciliados
                                </span>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle me-2"
                                    style="width: 12px; height: 12px; background-color: #ff8c00; border-radius: 50%;"></div>
                                <span class="ms-body-lg fw-semibold" style="font-size: 1.2rem !important;">
                                    Pendientes
                                </span>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle me-2"
                                    style="width: 12px; height: 12px; background: linear-gradient(135deg, #0078d4 0%, #50e6ff 100%); border-radius: 50%;"></div>
                                <span class="ms-body-lg fw-semibold" style="font-size: 1.2rem !important;">
                                    Porcentaje del Total
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>

<!-- Estilos Adicionales para Resumen Ajustados -->
<style>
    /* Efecto hover para las tarjetas de estado - Ajustado */
    .resumen-section .col-6 .rounded-3 {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .resumen-section .col-6 .rounded-3:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15) !important;
    }

    /* Scrollbar personalizado - Ajustado */
    .table-responsive::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);
        border-radius: 4px;
        border: 2px solid #f1f1f1;
    }

    /* Resaltado al pasar el mouse - Ajustado */
    .ms-table tbody tr:hover {
        background-color: rgba(0, 120, 212, 0.05) !important;
        transform: translateX(3px);
        transition: all 0.2s ease;
    }
</style>