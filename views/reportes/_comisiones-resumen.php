<?php
// app/views/reportes/_comisiones-resumen.php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var array $summaryPorClinica */
/** @var array $summary */
/** @var string $startDate */
/** @var string $endDate */
/** @var string $title */


if (!empty($summaryPorClinica)):
    // Calculate totals
    $totalMontoBs = 0;
    $totalMontoUsd = 0;
    $totalComisionAsesorBs = 0;
    $totalComisionAsesorUsd = 0;
    $totalComisionAgenciaBs = 0;
    $totalComisionAgenciaUsd = 0;
    $totalComisionClinicaBs = 0;
    $totalComisionClinicaUsd = 0;

    foreach ($summaryPorClinica as $clinica) {
        // We have commission amounts, need to calculate base amounts

        // Get commission amounts from the array
        $comisionAsesorBs = $clinica['total_comision_asesor_bs'] ?? 0;
        $comisionAsesorUsd = $clinica['total_comision_asesor_usd'] ?? 0;
        $comisionAgenciaBs = $clinica['total_comision_agencia_bs'] ?? 0;
        $comisionAgenciaUsd = $clinica['total_comision_agencia_usd'] ?? 0;

        // Calculate base Bs. amount from commissions
        // Asesor commission = 10% of base => base = commission / 0.10
        // Agencia commission = 4% of base => base = commission / 0.04
        // We can use either one, or average them
        if ($comisionAsesorBs > 0) {
            $montoBs = $comisionAsesorBs / 0.10;  // 5416.654 / 0.10 = 54166.54
        } elseif ($comisionAgenciaBs > 0) {
            $montoBs = $comisionAgenciaBs / 0.04;  // 2166.6616 / 0.04 = 54166.54
        } else {
            $montoBs = 0;
        }

        // Calculate base USD amount from commissions
        // We need to find exchange rate first
        // From grid: $comisionAsesorUsd = $comisionAsesorBs / $tasa
        // So: $tasa = $comisionAsesorBs / $comisionAsesorUsd
        if ($comisionAsesorUsd > 0 && $comisionAsesorBs > 0) {
            $tasa = $comisionAsesorBs / $comisionAsesorUsd;  // 5416.654 / 21.4 = ~253.11
        } elseif ($comisionAgenciaUsd > 0 && $comisionAgenciaBs > 0) {
            $tasa = $comisionAgenciaBs / $comisionAgenciaUsd;  // 2166.6616 / 8.56 = ~253.11
        } else {
            $tasa = 0;
        }

        // Now calculate USD base amount
        // From grid: $tasa = $montoBs / $montoUsd
        // So: $montoUsd = $montoBs / $tasa
        if ($tasa > 0) {
            $montoUsd = $montoBs / $tasa;  // 54166.54 / 253.11 = ~214.00
        } else {
            $montoUsd = 0;
        }

        // Now we have both base amounts, calculate remaining commissions
        $comisionClinicaBs = $montoBs * 0.70;  // 54166.54 * 0.70 = 37916.578
        $comisionClinicaUsd = $montoUsd * 0.70;  // 214 * 0.70 = 149.80

        // Accumulate totals
        $totalMontoBs += $montoBs;
        $totalMontoUsd += $montoUsd;
        $totalComisionAsesorBs += $comisionAsesorBs;
        $totalComisionAsesorUsd += $comisionAsesorUsd;
        $totalComisionAgenciaBs += $comisionAgenciaBs;
        $totalComisionAgenciaUsd += $comisionAgenciaUsd;
        $totalComisionClinicaBs += $comisionClinicaBs;
        $totalComisionClinicaUsd += $comisionClinicaUsd;

        // Debug
        // echo "Clínica: " . ($clinica['clinica_nombre'] ?? 'N/A') . "<br>";
        // echo "montoBs: $montoBs, montoUsd: $montoUsd, tasa: $tasa<br>";
        // echo "comisionAsesorBs: $comisionAsesorBs, comisionAsesorUsd: $comisionAsesorUsd<br>";
        // echo "comisionAgenciaBs: $comisionAgenciaBs, comisionAgenciaUsd: $comisionAgenciaUsd<br>";
        // echo "comisionClinicaBs: $comisionClinicaBs, comisionClinicaUsd: $comisionClinicaUsd<br><br>";
    }
?>
    <!-- Microsoft Professional Resumen de Comisiones Section -->
    <div class="col-12 resumen-section mb-4">
        <!-- Header Card -->
        <div class="ms-card border-0 shadow-lg mb-4 ms-fade-in">
            <div class="ms-card-body p-4">
                <div class="row align-items-center">
                    <div class="col-lg-8 mb-3 mb-lg-0">
                        <div class="d-flex align-items-start">
                            <div class="me-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center shadow"
                                    style="width: 70px; height: 70px; background: linear-gradient(135deg, #ff8c00 0%, #e67e00 100%);">
                                    <i class="fas fa-chart-pie text-white" style="font-size: 2.2rem;"></i>
                                </div>
                            </div>
                            <div>
                                <h1 class="display-5 fw-bold mb-1" style="font-size: 2rem !important; color: #ff8c00;">
                                    <?= Html::encode($title) ?>
                                </h1>
                                <div class="d-flex flex-column">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar-alt me-2" style="font-size: 1.6rem; color: #ff8c00;"></i>
                                        <span class="ms-body-lg fw-bold" style="font-size: 1.5rem !important;">
                                            Período: <?= $startDate ?> <i class="fas fa-arrow-right mx-2"></i> <?= $endDate ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="d-flex flex-column align-items-lg-end gap-2">
                            <div class="text-center text-lg-end w-100">
                                <div class="d-inline-block px-3 py-2 rounded-3 shadow-sm"
                                    style="background: white; border-left: 4px solid #ff8c00;">
                                    <div class="ms-body-sm text-muted mb-1" style="font-size: 1.1rem !important;">
                                        <i class="fas fa-hand-holding-usd me-2"></i>Total Monto (Bs.)
                                    </div>
                                    <div class="display-6 fw-bold text-warning" style="font-size: 2.2rem !important;">
                                        <?= Yii::$app->formatter->asCurrency($totalMontoBs, 'VES') ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Distribución por Clínicas -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="ms-summary-card border-0 shadow-lg h-100">
                    <div class="ms-card-body p-0">
                        <!-- Table Header -->
                        <div class="ms-card-header py-3" style="background: linear-gradient(135deg, #2c3e50 0%, #4a6491 100%);">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-chart-pie text-white me-3" style="font-size: 1.8rem;"></i>
                                <h4 class="mb-0 text-white fw-bold" style="font-size: 1.6rem !important;">
                                    DISTRIBUCIÓN POR CLÍNICAS
                                </h4>
                            </div>
                        </div>

                        <!-- Table Container -->
                        <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
                            <table class="table table-bordered table-hover mb-0" style="border-color: #dee2e6;">
                                <thead style="position: sticky; top: 0; z-index: 10;">
                                    <!-- Main Header Row -->
                                    <tr>
                                        <th rowspan="3" class="text-center align-middle" style="width: 60px; font-size: 1.4rem !important; background: linear-gradient(135deg, #2c3e50 0%, #4a6491 100%); color: white !important; border-right: 2px solid rgba(255,255,255,0.2);">
                                            #
                                        </th>
                                        <th rowspan="3" class="align-middle" style="min-width: 220px; font-size: 1.4rem !important; background: linear-gradient(135deg, #2c3e50 0%, #4a6491 100%); color: white !important; border-right: 2px solid rgba(255,255,255,0.2);">
                                            <i class="fas fa-hospital me-2"></i>CLÍNICA
                                        </th>
                                        <th rowspan="2" colspan="2" class="text-center align-middle" style="font-size: 1.4rem !important; background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%); color: white !important; border-right: 2px solid rgba(255,255,255,0.2);">
                                            <i class="fas fa-money-bill-wave me-2"></i>MONTOS PAGADOS
                                        </th>
                                        <th rowspan="2" colspan="9" class="text-center align-middle" style="font-size: 1.4rem !important; background: linear-gradient(135deg, #8b0000 0%, #a52a2a 100%); color: white !important; border-right: 2px solid rgba(255,255,255,0.2);">
                                            <i class="fas fa-hand-holding-usd me-2"></i>COMISIONES
                                        </th>

                                    </tr>

                                    <!-- Sub-header Row -->
                                    <tr>
                                        <!-- NOTE: MONTOS PAGADOS and COMISIONES cells removed from this row since they're now rowspan=2 above -->
                                        <!-- The percentage column is also rowspan=3, so it spans all rows -->
                                    </tr>

                                    <!-- Currency Header Row -->
                                    <tr>
                                        <!-- Montos Pagados currency headers - NOW UNDER THE MAIN HEADER -->
                                        <th class="text-center py-2" style="background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%); font-size: 1.2rem !important; border-right: 1px solid #dee2e6; color: white !important;">Bs.</th>
                                        <th class="text-center py-2" style="background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%); font-size: 1.2rem !important; border-right: 2px solid #dee2e6; color: white !important;">USD</th>

                                        <!-- Asesor currency headers -->
                                        <th class="text-center py-2" style="background: linear-gradient(135deg, #8b0000 0%, #a52a2a 100%); font-size: 1.2rem !important; color: white !important;">Bs.</th>
                                        <th class="text-center py-2" style="background: linear-gradient(135deg, #8b0000 0%, #a52a2a 100%); font-size: 1.2rem !important; color: white !important;">USD</th>

                                        <!-- Agencia currency headers -->
                                        <th class="text-center py-2" style="background: linear-gradient(135deg, #8b0000 0%, #a52a2a 100%); font-size: 1.2rem !important; color: white !important;">Bs.</th>
                                        <th class="text-center py-2" style="background: linear-gradient(135deg, #8b0000 0%, #a52a2a 100%); font-size: 1.2rem !important; color: white !important;">USD</th>

                                        <!-- Clínica currency headers -->
                                        <th class="text-center py-2" style="background: linear-gradient(135deg, #8b0000 0%, #a52a2a 100%); font-size: 1.2rem !important; color: white !important;">Bs.</th>
                                        <th class="text-center py-2" style="background: linear-gradient(135deg, #8b0000 0%, #a52a2a 100%); font-size: 1.2rem !important; color: white !important;">USD</th>
                                        <th class="text-center py-2" style="background: linear-gradient(135deg, #8b0000 0%, #a52a2a 100%); font-size: 2.3rem !important; color: white !important;">%</th>


                                        <!-- NOTE: Percentage column removed from this row since it's now rowspan=3 above -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $consecutivo = 1;
                                    foreach ($summaryPorClinica as $clinica):
                                        // Calculate from commission amounts

                                        // Get commission amounts from the array
                                        $comisionAsesorBs = $clinica['total_comision_asesor_bs'] ?? 0;
                                        $comisionAsesorUsd = $clinica['total_comision_asesor_usd'] ?? 0;
                                        $comisionAgenciaBs = $clinica['total_comision_agencia_bs'] ?? 0;
                                        $comisionAgenciaUsd = $clinica['total_comision_agencia_usd'] ?? 0;

                                        // Calculate base Bs. amount from commissions
                                        if ($comisionAsesorBs > 0) {
                                            $montoBs = $comisionAsesorBs / 0.10;
                                        } elseif ($comisionAgenciaBs > 0) {
                                            $montoBs = $comisionAgenciaBs / 0.04;
                                        } else {
                                            $montoBs = 0;
                                        }

                                        // Calculate exchange rate
                                        if ($comisionAsesorUsd > 0 && $comisionAsesorBs > 0) {
                                            $tasa = $comisionAsesorBs / $comisionAsesorUsd;
                                        } elseif ($comisionAgenciaUsd > 0 && $comisionAgenciaBs > 0) {
                                            $tasa = $comisionAgenciaBs / $comisionAgenciaUsd;
                                        } else {
                                            $tasa = 0;
                                        }

                                        // Calculate base USD amount
                                        if ($tasa > 0) {
                                            $montoUsd = $montoBs / $tasa;
                                        } else {
                                            $montoUsd = 0;
                                        }

                                        // Calculate remaining commissions
                                        $comisionClinicaBs = $montoBs * 0.70;
                                        $comisionClinicaUsd = $montoUsd * 0.70;

                                        $porcentajeClinica = $totalMontoBs > 0 ? ($montoBs / $totalMontoBs) * 100 : 0;

                                    ?>
                                        <tr class=" align-middle">
                                            <!-- # Column -->
                                            <td class="text-center py-3" style="background-color: #f8fafc; border-right: 2px solid #e9ecef;">
                                                <span class="badge bg-info shadow-sm" style="font-size: 1.3rem !important; padding: 0.5rem 0.75rem; min-width: 40px;">
                                                    <?= $consecutivo++ ?>
                                                </span>
                                            </td>

                                            <!-- Clínica Column -->
                                            <td class="py-3 ps-3" style="border-right: 2px solid #e9ecef;">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm"
                                                        style="width: 45px; height: 45px; background: linear-gradient(135deg, #c7e0f4 0%, #a5d2f4 100%);">
                                                        <i class="fas fa-hospital ms-primary" style="font-size: 1.4rem;"></i>
                                                    </div>
                                                    <div>
                                                        <div class="ms-body-lg fw-bold mb-1" style="font-size: 1.3rem !important;">
                                                            <?= Html::encode($clinica['clinica_nombre'] ?? 'Sin Clínica') ?>
                                                        </div>
                                                        <?php if (isset($clinica['clinica_rif']) && $clinica['clinica_rif'] !== 'N/A'): ?>
                                                            <div class="ms-body text-muted" style="font-size: 1.1rem !important;">
                                                                <i class="fas fa-id-card me-1"></i><?= $clinica['clinica_rif'] ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Montos Pagados Bs. -->
                                            <td class="text-center py-3" style="border-right: 1px solid #dee2e6;">
                                                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                                    <span class="display-6 fw-bold text-primary mb-1" style="font-size: 1.4rem !important;">
                                                        <?= Yii::$app->formatter->asCurrency($montoBs, 'VES') ?>
                                                    </span>
                                                </div>
                                            </td>

                                            <!-- Montos Pagados USD -->
                                            <td class="text-center py-3" style="border-right: 2px solid #dee2e6;">
                                                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                                    <span class="display-6 fw-bold text-success mb-1" style="font-size: 1.4rem !important;">
                                                        <?= Yii::$app->formatter->asCurrency($montoUsd, 'USD') ?>
                                                    </span>
                                                </div>
                                            </td>

                                            <!-- Comisión Asesor Bs. -->
                                            <td class="text-center py-3" style="background-color: #fff9e6; border-right: 1px solid #dee2e6;">
                                                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                                    <span class="fw-bold text-warning mb-1" style="font-size: 1.3rem !important;">
                                                        <?= Yii::$app->formatter->asCurrency($comisionAsesorBs, 'VES') ?>
                                                    </span>
                                                </div>
                                            </td>

                                            <!-- Comisión Asesor USD -->
                                            <td class="text-center py-3" style="background-color: #fff9e6; border-right: 1px solid #dee2e6;">
                                                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                                    <span class="fw-bold text-primary mb-1" style="font-size: 1.3rem !important;">
                                                        <?= Yii::$app->formatter->asCurrency($comisionAsesorUsd, 'USD') ?>
                                                    </span>
                                                </div>
                                            </td>

                                            <!-- Comisión Agencia Bs. -->
                                            <td class="text-center py-3" style="background-color: #ffe6e6; border-right: 1px solid #dee2e6;">
                                                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                                    <span class="fw-bold text-danger mb-1" style="font-size: 1.3rem !important;">
                                                        <?= Yii::$app->formatter->asCurrency($comisionAgenciaBs, 'VES') ?>
                                                    </span>
                                                </div>
                                            </td>

                                            <!-- Comisión Agencia USD -->
                                            <td class="text-center py-3" style="background-color: #ffe6e6; border-right: 1px solid #dee2e6;">
                                                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                                    <span class="fw-bold text-primary mb-1" style="font-size: 1.3rem !important;">
                                                        <?= Yii::$app->formatter->asCurrency($comisionAgenciaUsd, 'USD') ?>
                                                    </span>
                                                </div>
                                            </td>

                                            <!-- Comisión Clínica Bs. -->
                                            <td class="text-center py-3" style="background-color: #e6ffe6; border-right: 1px solid #dee2e6;">
                                                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                                    <span class="fw-bold text-success mb-1" style="font-size: 1.3rem !important;">
                                                        <?= Yii::$app->formatter->asCurrency($comisionClinicaBs, 'VES') ?>
                                                    </span>
                                                </div>
                                            </td>

                                            <!-- Comisión Clínica USD -->
                                            <td class="text-center py-3" style="background-color: #e6ffe6; border-right: 1px solid #dee2e6;">
                                                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                                    <span class="fw-bold text-success mb-1" style="font-size: 1.3rem !important;">
                                                        <?= Yii::$app->formatter->asCurrency($comisionClinicaUsd, 'USD') ?>
                                                    </span>
                                                </div>
                                            </td>

                                            <!-- Percentage Column -->
                                            <td class="text-center py-3" style="background-color: #f8fafc;">
                                                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                                    <div class="progress mb-2" style="height: 12px; width: 100px; border-radius: 6px;">
                                                        <div class="progress-bar"
                                                            style="width: <?= min(100, $porcentajeClinica) ?>%; background: linear-gradient(90deg, #010101ff 0%, #ffcc00 100%); border-radius: 6px;">
                                                        </div>
                                                    </div>
                                                    <span class="fw-bold" style="font-size: 1.3rem !important; color: #ff8c00;">
                                                        <?= number_format($porcentajeClinica, 1) ?>%
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>

                                <!-- Totals Row -->
                                <?php if ($totalMontoBs > 0): ?>
                                    <tfoot style="position: sticky; bottom: 0; z-index: 10;">
                                        <tr style="background: linear-gradient(135deg, #2c3e50 0%, #4a6491 100%);">
                                            <td colspan="2" class="ps-4 py-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-chart-bar me-3 text-white" style="font-size: 1.8rem;"></i>
                                                    <div>
                                                        <h3 class="mb-1 fw-bold text-white" style="font-size: 1.6rem !important;">
                                                            TOTAL GENERAL
                                                        </h3>
                                                        <p class="mb-0 text-white" style="opacity: 0.85; font-size: 1.2rem !important;">
                                                            <?= count($summaryPorClinica) ?> clínicas
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Total Montos Pagados Bs. -->
                                            <td class="text-center py-3">
                                                <div class="d-flex flex-column align-items-center">
                                                    <h3 class="mb-1 fw-bold text-white" style="font-size: 1.6rem !important;">
                                                        <?= Yii::$app->formatter->asCurrency($totalMontoBs, 'VES') ?>
                                                    </h3>
                                                    <p class="mb-0 text-white" style="opacity: 0.85; font-size: 1.1rem !important;">
                                                        Total Bs.
                                                    </p>
                                                </div>
                                            </td>

                                            <!-- Total Montos Pagados USD -->
                                            <td class="text-center py-3">
                                                <div class="d-flex flex-column align-items-center">
                                                    <h3 class="mb-1 fw-bold text-white" style="font-size: 1.6rem !important;">
                                                        <?= Yii::$app->formatter->asCurrency($totalMontoUsd, 'USD') ?>
                                                    </h3>
                                                    <p class="mb-0 text-white" style="opacity: 0.85; font-size: 1.1rem !important;">
                                                        Total USD
                                                    </p>
                                                </div>
                                            </td>

                                            <!-- Total Comisión Asesor Bs. -->
                                            <td class="text-center py-3" style="background-color: rgba(255, 249, 230, 0.2);">
                                                <div class="d-flex flex-column align-items-center">
                                                    <h3 class="mb-1 fw-bold text-white" style="font-size: 1.6rem !important;">
                                                        <?= Yii::$app->formatter->asCurrency($totalComisionAsesorBs, 'VES') ?>
                                                    </h3>
                                                    <p class="mb-0 text-white" style="opacity: 0.85; font-size: 1.1rem !important;">
                                                        Asesor Bs.
                                                    </p>
                                                </div>
                                            </td>

                                            <!-- Total Comisión Asesor USD -->
                                            <td class="text-center py-3" style="background-color: rgba(255, 249, 230, 0.2);">
                                                <div class="d-flex flex-column align-items-center">
                                                    <h3 class="mb-1 fw-bold text-white" style="font-size: 1.6rem !important;">
                                                        <?= Yii::$app->formatter->asCurrency($totalComisionAsesorUsd, 'USD') ?>
                                                    </h3>
                                                    <p class="mb-0 text-white" style="opacity: 0.85; font-size: 1.1rem !important;">
                                                        Asesor USD
                                                    </p>
                                                </div>
                                            </td>

                                            <!-- Total Comisión Agencia Bs. -->
                                            <td class="text-center py-3" style="background-color: rgba(255, 230, 230, 0.2);">
                                                <div class="d-flex flex-column align-items-center">
                                                    <h3 class="mb-1 fw-bold text-white" style="font-size: 1.6rem !important;">
                                                        <?= Yii::$app->formatter->asCurrency($totalComisionAgenciaBs, 'VES') ?>
                                                    </h3>
                                                    <p class="mb-0 text-white" style="opacity: 0.85; font-size: 1.1rem !important;">
                                                        Agencia Bs.
                                                    </p>
                                                </div>
                                            </td>

                                            <!-- Total Comisión Agencia USD -->
                                            <td class="text-center py-3" style="background-color: rgba(255, 230, 230, 0.2);">
                                                <div class="d-flex flex-column align-items-center">
                                                    <h3 class="mb-1 fw-bold text-white" style="font-size: 1.6rem !important;">
                                                        <?= Yii::$app->formatter->asCurrency($totalComisionAgenciaUsd, 'USD') ?>
                                                    </h3>
                                                    <p class="mb-0 text-white" style="opacity: 0.85; font-size: 1.1rem !important;">
                                                        Agencia USD
                                                    </p>
                                                </div>
                                            </td>

                                            <!-- Total Comisión Clínica Bs. -->
                                            <td class="text-center py-3" style="background-color: rgba(230, 255, 230, 0.2);">
                                                <div class="d-flex flex-column align-items-center">
                                                    <h3 class="mb-1 fw-bold text-white" style="font-size: 1.6rem !important;">
                                                        <?= Yii::$app->formatter->asCurrency($totalComisionClinicaBs, 'VES') ?>
                                                    </h3>
                                                    <p class="mb-0 text-white" style="opacity: 0.85; font-size: 1.1rem !important;">
                                                        Clínica Bs.
                                                    </p>
                                                </div>
                                            </td>

                                            <!-- Total Comisión Clínica USD -->
                                            <td class="text-center py-3" style="background-color: rgba(230, 255, 230, 0.2);">
                                                <div class="d-flex flex-column align-items-center">
                                                    <h3 class="mb-1 fw-bold text-white" style="font-size: 1.6rem !important;">
                                                        <?= Yii::$app->formatter->asCurrency($totalComisionClinicaUsd, 'USD') ?>
                                                    </h3>
                                                    <p class="mb-0 text-white" style="opacity: 0.85; font-size: 1.1rem !important;">
                                                        Clínica USD
                                                    </p>
                                                </div>
                                            </td>

                                            <!-- Total Percentage -->
                                            <td class="text-center py-3" style="background: linear-gradient(135deg, #2c3e50 0%, #4a6491 100%);">
                                                <div class="d-flex flex-column align-items-center">
                                                    <h3 class="mb-1 fw-bold text-white" style="font-size: 1.8rem !important;">
                                                        100%
                                                    </h3>
                                                    <p class="mb-0 text-white" style="opacity: 0.85; font-size: 1.1rem !important;">
                                                        Distribución
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>

                        <!-- Table Footer -->
                        <div class="ms-card-footer py-3" style="background: #faf9f8;">
                            <div class="row align-items-center">
                                <div class="col-lg-6">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm"
                                            style="width: 45px; height: 45px; background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);">
                                            <i class="fas fa-info-circle text-white" style="font-size: 1.3rem;"></i>
                                        </div>
                                        <div>
                                            <h4 class="ms-title-sm mb-1" style="font-size: 1.3rem !important;">
                                                Resumen de Distribución
                                            </h4>
                                            <div class="d-flex flex-wrap gap-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-hospital me-2 ms-primary" style="font-size: 1.1rem;"></i>
                                                    <span class="ms-body-lg fw-semibold" style="font-size: 1.2rem !important;">
                                                        <?= count($summaryPorClinica) ?> clínicas
                                                    </span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-hand-holding-usd me-2 ms-primary" style="font-size: 1.1rem;"></i>
                                                    <span class="ms-body-lg fw-semibold" style="font-size: 1.2rem !important;">
                                                        <?= Yii::$app->formatter->asCurrency($totalMontoBs, 'VES') ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="text-end">
                                        <small class="text-muted" style="font-size: 1.1rem !important;">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            Período: <?= $startDate ?> - <?= $endDate ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estilos Adicionales para Resumen de Comisiones -->
    <style>
        /* Animaciones suaves */
        .ms-fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Efecto hover para tarjetas */
        .ms-summary-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .ms-summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
        }

        /* Efecto hover para filas de la tabla */
        .table-hover tbody tr {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .table-hover tbody tr:hover {
            transform: translateX(3px);
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            background-color: rgba(0, 120, 212, 0.05) !important;
        }

        /* Scrollbar personalizado */
        .table-responsive::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 5px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);
            border-radius: 5px;
            border: 2px solid #f1f1f1;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #106ebe 0%, #005a9e 100%);
        }

        /* Progress bar animaciones */
        .progress-bar {
            transition: width 1s ease-in-out;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .ms-summary-card {
                margin-bottom: 1rem;
            }

            .display-6 {
                font-size: 1.8rem !important;
            }

            .ms-title-lg {
                font-size: 1.5rem !important;
            }

            .ms-title-sm {
                font-size: 1rem !important;
            }
        }

        /* fix: color */
        body {
            color: red;
        }
    </style>
<?php endif; ?>