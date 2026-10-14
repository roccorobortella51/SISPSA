<?php

/**
 * PDF Template - Afiliados Activos por Mes
 *
 * Metric: Tendencia Mensual (linear regression slope, normalized by mean)
 *
 * Single-page layout:
 *   - Header
 *   - Matrix table (clinic x month) with Tendencia Mensual column
 *   - Summary strip (KPIs) below the table
 *
 * @var yii\web\View $this
 * @var array $clinicMatrix
 * @var array $monthlyData
 * @var array $summaryStats
 * @var string $logo
 * @var string $generatedAt
 */

use yii\helpers\Html;

$monthLabels = $clinicMatrix['month_labels'] ?? [];
$monthKeys = $clinicMatrix['month_keys'] ?? [];
$clinics = $clinicMatrix['clinics'] ?? [];
$monthlyTotals = $clinicMatrix['monthly_totals'] ?? [];
$grandTotal = $clinicMatrix['grand_total'] ?? 0;

// Latest month label (used only for KPI cards at the bottom)
$latestMonthLabel = !empty($monthLabels) ? end($monthLabels) : '-';

// ============================================================
// Overall trend (footer) — single source of truth from summaryStats
// ============================================================
$overallGrowth = $summaryStats['growth_rate'] ?? null;
$overallStatus = $summaryStats['growth_status'] ?? 'flat';

// Growth color helpers (based on overall trend status)
$growthPositive = $overallStatus !== 'down';
$growthColor = $overallStatus === 'up' ? '#107c10' : ($overallStatus === 'down' ? '#d13438' : '#495057');
$growthSign = ($overallGrowth !== null && $overallGrowth > 0) ? '+' : '';
?>

<!-- ============================================================ -->
<!-- PAGE 1: MATRIX TABLE + SUMMARY                                -->
<!-- ============================================================ -->
<div>

    <!-- ============================================================ -->
    <!-- HEADER                                                        -->
    <!-- ============================================================ -->
    <table width="100%" cellpadding="0" cellspacing="0" style="border-bottom: 3px solid #0078d4; margin-bottom: 12px; padding-bottom: 8px;">
        <tr>
            <td width="160" valign="middle" style="padding: 0;">
                <?php if (file_exists($logo)): ?>
                    <img src="<?= $logo ?>" width="150px" alt="SISPSA" style="display: block;">
                <?php endif; ?>
            </td>
            <td valign="middle" align="center" style="padding: 0;">
                <div style="font-family: Arial, sans-serif; font-size: 14pt; font-weight: bold; color: #2c3e50; line-height: 1.2;">
                    Afiliados Activos por Mes
                </div>
                <div style="font-family: Arial, sans-serif; font-size: 10pt; color: #0078d4; margin-top: 3px;">
                    Últimos 12 meses - Desglose por Clínica
                </div>
            </td>
            <td width="160" valign="middle" align="right" style="padding: 0; font-family: Arial, sans-serif; font-size: 7pt; color: #605e5c;">
                <div>Generado</div>
                <div style="font-weight: bold;"><?= Html::encode($generatedAt) ?></div>
            </td>
        </tr>
    </table>

    <!-- ============================================================ -->
    <!-- MATRIX TABLE                                                  -->
    <!-- ============================================================ -->
    <table width="100%" cellpadding="3" cellspacing="0" style="font-family: Arial, sans-serif; font-size: 7pt; border-collapse: collapse;">
        <thead>
            <tr>
                <th align="left" style="border: 1px solid #1a2530; padding: 6px 6px; font-weight: bold; width: 160px; background-color: #2c3e50; color: #ffffff; font-size: 8pt;">
                    Clínica
                </th>
                <?php foreach ($monthLabels as $label): ?>
                    <th align="center" style="border: 1px solid #1a2530; padding: 6px 4px; font-weight: bold; background-color: #2c3e50; color: #ffffff; font-size: 8pt;">
                        <?= Html::encode($label) ?>
                    </th>
                <?php endforeach; ?>
                <th align="center" style="border: 1px solid #1a2530; padding: 6px 6px; font-weight: bold; background-color: #107c10; color: #ffffff; font-size: 8pt;">
                    Tendencia Mensual
                </th>
            </tr>
        </thead>
        <tbody>
            <?php $rowIndex = 0; ?>
            <?php foreach ($clinics as $clinic): ?>
                <?php
                $rowBg = ($rowIndex % 2 === 0) ? '#ffffff' : '#f8f9fa';
                $rowIndex++;

                // Trend per clinic
                $growthStatus = $clinic['growth_status'] ?? 'na';

                $growthText = '—';
                $growthTextColor = '#6c757d';
                $growthBg = '#f0f0f0';

                if ($growthStatus === 'up') {
                    $growthRate = (float)($clinic['growth_rate'] ?? 0);
                    $growthText = '+' . number_format($growthRate, 1) . '%/mes';
                    $growthTextColor = '#107c10';
                    $growthBg = '#d4f4d4';
                } elseif ($growthStatus === 'down') {
                    $growthRate = (float)($clinic['growth_rate'] ?? 0);
                    $growthText = number_format($growthRate, 1) . '%/mes';
                    $growthTextColor = '#d13438';
                    $growthBg = '#fde8e8';
                } elseif ($growthStatus === 'flat') {
                    $growthText = '≈ 0%/mes';
                    $growthTextColor = '#495057';
                    $growthBg = '#f0f0f0';
                }
                ?>
                <tr>
                    <td align="left" style="border: 1px solid #dee2e6; padding: 6px 6px; font-weight: bold; color: #2c3e50; background-color: <?= $rowBg ?>; font-size: 8.5pt;">
                        <?= Html::encode($clinic['clinica_nombre']) ?>
                    </td>
                    <?php foreach ($monthKeys as $key): ?>
                        <?php $count = $clinic['months'][$key] ?? 0; ?>
                        <td align="center" style="border: 1px solid #dee2e6; padding: 6px 4px; background-color: <?= $rowBg ?>; color: <?= $count > 0 ? '#2c3e50' : '#adb5bd' ?>; font-size: 10pt; font-weight: bold;">
                            <?= $count > 0 ? number_format($count) : '-' ?>
                        </td>
                    <?php endforeach; ?>
                    <td align="center" style="border: 1px solid #dee2e6; padding: 6px 6px; font-weight: bold; background-color: <?= $growthBg ?>; color: <?= $growthTextColor ?>; font-size: 10pt;">
                        <?= $growthText ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <!-- TOTAL ROW -->
            <tr>
                <td align="left" style="border: 1px solid #1a2530; padding: 6px 6px; font-weight: bold; background-color: #2c3e50; color: #ffffff; font-size: 8.5pt;">
                    TOTAL GENERAL
                </td>
                <?php foreach ($monthKeys as $key): ?>
                    <td align="center" style="border: 1px solid #1a2530; padding: 6px 4px; font-weight: bold; background-color: #2c3e50; color: #4cd964; font-size: 10pt;">
                        <?= number_format($monthlyTotals[$key] ?? 0) ?>
                    </td>
                <?php endforeach; ?>
                <td align="center" style="border: 1px solid #1a2530; padding: 6px 6px; font-weight: bold; background-color: #107c10; color: #ffffff; font-size: 11pt;">
                    <?php if ($overallGrowth === null): ?>
                        —
                    <?php elseif ($overallGrowth >= 1.0): ?>
                        +<?= number_format($overallGrowth, 1) ?>%/mes
                    <?php elseif ($overallGrowth <= -1.0): ?>
                        <?= number_format($overallGrowth, 1) ?>%/mes
                    <?php else: ?>
                        ≈ 0%/mes
                    <?php endif; ?>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- ============================================================ -->
    <!-- FOOTNOTE FOR TABLE                                            -->
    <!-- ============================================================ -->
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 8px; font-family: Arial, sans-serif; font-size: 7pt;">
        <tr>
            <td align="center" style="color: #6c757d;">
                <strong style="color: #2c3e50;">Definición:</strong>
                Afiliado con contrato vigente sin cuotas con estatus <em>vencida</em> al cierre del mes.
                Los afiliados en período de gracia (<em>en_gracias</em>) cuentan como activos.
                &nbsp;|&nbsp;
                <strong style="color: #107c10;">Tendencia Mensual</strong>
                = pendiente de regresión lineal sobre todos los meses activos, normalizada por el promedio.
            </td>
        </tr>
    </table>

    <!-- ============================================================ -->
    <!-- SEPARATOR                                                     -->
    <!-- ============================================================ -->
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 18px; margin-bottom: 12px;">
        <tr>
            <td style="border-top: 2px solid #0078d4;"></td>
        </tr>
    </table>

    <!-- ============================================================ -->
    <!-- RESUMEN ESTADÍSTICO (KPIs) - Horizontal Strip                 -->
    <!-- ============================================================ -->
    <table width="100%" cellpadding="0" cellspacing="0" style="font-family: Arial, sans-serif;">
        <tr>
            <td align="center" style="padding-bottom: 10px;">
                <div style="font-size: 12pt; font-weight: bold; color: #2c3e50; letter-spacing: 0.3px;">
                    Resumen Estadístico
                </div>
                <div style="font-size: 8pt; color: #0078d4;">
                    Afiliados Activos por Mes
                </div>
            </td>
        </tr>
    </table>

    <!-- KPI Cards Row -->
    <table width="100%" cellpadding="0" cellspacing="6" style="font-family: Arial, sans-serif; border-collapse: separate;">
        <tr>

            <!-- KPI 1: Activos (Latest) -->
            <td width="25%" valign="top" style="border: 1px solid #0078d4; background-color: #f0f6fd; padding: 10px; text-align: center;">
                <div style="font-size: 7pt; font-weight: bold; color: #0078d4; text-transform: uppercase; letter-spacing: 0.5px;">
                    Activos <?= Html::encode($latestMonthLabel) ?>
                </div>
                <div style="font-size: 20pt; font-weight: bold; color: #0078d4; line-height: 1.1; margin-top: 4px;">
                    <?= number_format($summaryStats['current'] ?? 0) ?>
                </div>
                <div style="font-size: 7pt; color: #605e5c; margin-top: 3px;">
                    Al cierre del mes
                </div>
            </td>

            <!-- KPI 2: Tendencia Mensual -->
            <td width="25%" valign="top" style="border: 1px solid <?= $growthColor ?>; background-color: <?= $growthPositive ? '#f0faf0' : '#fef6f6' ?>; padding: 10px; text-align: center;">
                <div style="font-size: 7pt; font-weight: bold; color: <?= $growthColor ?>; text-transform: uppercase; letter-spacing: 0.5px;">
                    Tendencia Mensual
                </div>
                <div style="font-size: 20pt; font-weight: bold; color: <?= $growthColor ?>; line-height: 1.1; margin-top: 4px;">
                    <?= $growthSign ?><?= number_format($summaryStats['growth_rate'] ?? 0, 1) ?>%
                </div>
                <div style="font-size: 7pt; color: #605e5c; margin-top: 3px;">
                    promedio por mes (regresión)
                </div>
            </td>

            <!-- KPI 3: Best Month -->
            <td width="25%" valign="top" style="border: 1px solid #107c10; background-color: #f0faf0; padding: 10px; text-align: center;">
                <div style="font-size: 7pt; font-weight: bold; color: #107c10; text-transform: uppercase; letter-spacing: 0.5px;">
                    Mejor Mes
                </div>
                <div style="font-size: 14pt; font-weight: bold; color: #107c10; line-height: 1.2; margin-top: 4px;">
                    <?= Html::encode($summaryStats['best_month_label'] ?? '-') ?>
                </div>
                <div style="font-size: 7pt; color: #605e5c; margin-top: 3px;">
                    <?= number_format($summaryStats['best_month_count'] ?? 0) ?> activos
                </div>
            </td>

            <!-- KPI 4: Worst Month -->
            <td width="25%" valign="top" style="border: 1px solid #6c757d; background-color: #f8f9fa; padding: 10px; text-align: center;">
                <div style="font-size: 7pt; font-weight: bold; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px;">
                    Menor Mes
                </div>
                <div style="font-size: 14pt; font-weight: bold; color: #495057; line-height: 1.2; margin-top: 4px;">
                    <?= Html::encode($summaryStats['worst_month_label'] ?? '-') ?>
                </div>
                <div style="font-size: 7pt; color: #605e5c; margin-top: 3px;">
                    <?= number_format($summaryStats['worst_month_count'] ?? 0) ?> activos
                </div>
            </td>

        </tr>
    </table>

    <!-- ============================================================ -->
    <!-- METADATA FOOTNOTE                                             -->
    <!-- ============================================================ -->
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 10px; font-family: Arial, sans-serif; font-size: 7pt;">
        <tr>
            <td align="center" style="color: #6c757d;">
                <strong style="color: #2c3e50;">Total de clínicas con actividad:</strong>
                <span style="color: #0078d4; font-weight: bold;"><?= count($clinics) ?></span>
                &nbsp;|&nbsp;
                <strong style="color: #2c3e50;">Meses analizados:</strong>
                <span style="color: #0078d4; font-weight: bold;"><?= count($monthKeys) ?></span>
            </td>
        </tr>
    </table>

</div>