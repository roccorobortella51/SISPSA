<?php

/**
 * PDF Template - Two Pages
 * Page 1: Monthly breakdown table (clinic x month)
 * Page 2: Timeline chart
 *
 * mPDF NOTE: Colors do NOT cascade from <tr> to <td>/<th>.
 * Every cell that needs a color MUST have it set explicitly inline.
 *
 * @var string $chartImagePath
 * @var string $chartTitle
 * @var string $chartSubtitle
 * @var string $logo
 * @var string $generatedAt
 * @var array $monthlyBreakdown
 */

use yii\helpers\Html;

// Extract monthly breakdown data
$monthLabels = $monthlyBreakdown['month_labels'] ?? [];
$clinics = $monthlyBreakdown['clinics'] ?? [];
$monthlyTotals = $monthlyBreakdown['monthly_totals'] ?? [];
$grandTotal = $monthlyBreakdown['grand_total'] ?? 0;
$monthKeys = $monthlyBreakdown['month_keys'] ?? [];
?>

<!-- ============================================================ -->
<!-- PAGE 1: MONTHLY BREAKDOWN TABLE                               -->
<!-- ============================================================ -->
<div style="page-break-after: always;">

    <!-- HEADER -->
    <table width="100%" cellpadding="0" cellspacing="0" style="border-bottom: 3px solid #0078d4; margin-bottom: 14px; padding-bottom: 8px;">
        <tr>
            <td width="160" valign="middle" style="padding: 0;">
                <?php if (file_exists($logo)): ?>
                    <img src="<?= $logo ?>" width="150px" alt="SISPSA" style="display: block;">
                <?php endif; ?>
            </td>
            <td valign="middle" align="center" style="padding: 0;">
                <div style="font-family: Arial, sans-serif; font-size: 14pt; font-weight: bold; color: #2c3e50; line-height: 1.2;">
                    Reporte de Afiliados por Clínica
                </div>
                <div style="font-family: Arial, sans-serif; font-size: 10pt; color: #0078d4; margin-top: 3px;">
                    Desglose Mensual - Últimos 12 Meses
                </div>
            </td>
            <td width="160" valign="middle" align="right" style="padding: 0; font-family: Arial, sans-serif; font-size: 7pt; color: #605e5c;">
                <div>Generado</div>
                <div style="font-weight: bold;"><?= Html::encode($generatedAt) ?></div>
            </td>
        </tr>
    </table>

    <!-- MONTHLY BREAKDOWN TABLE -->
    <?php if (!empty($clinics) && !empty($monthLabels)): ?>
        <table width="100%" cellpadding="3" cellspacing="0" style="font-family: Arial, sans-serif; font-size: 7pt; border-collapse: collapse;">
            <thead>
                <tr>
                    <th align="left" style="border: 1px solid #1a2530; padding: 5px 6px; font-weight: bold; width: 160px; background-color: #2c3e50; color: #ffffff;">
                        Clínica
                    </th>
                    <?php foreach ($monthLabels as $label): ?>
                        <th align="center" style="border: 1px solid #1a2530; padding: 5px 3px; font-weight: bold; background-color: #2c3e50; color: #ffffff;">
                            <?= Html::encode($label) ?>
                        </th>
                    <?php endforeach; ?>
                    <th align="center" style="border: 1px solid #1a2530; padding: 5px 6px; font-weight: bold; background-color: #0078d4; color: #ffffff;">
                        Total
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php $rowIndex = 0; ?>
                <?php foreach ($clinics as $clinic): ?>
                    <?php
                    $rowBg = ($rowIndex % 2 === 0) ? '#ffffff' : '#f8f9fa';
                    $rowIndex++;
                    ?>
                    <tr>
                        <td align="left" style="border: 1px solid #dee2e6; padding: 4px 6px; font-weight: bold; color: #2c3e50; background-color: <?= $rowBg ?>;">
                            <?= Html::encode($clinic['clinica_nombre']) ?>
                        </td>
                        <?php foreach ($monthKeys as $key): ?>
                            <?php $count = $clinic['months'][$key] ?? 0; ?>
                            <td align="center" style="border: 1px solid #dee2e6; padding: 4px 3px; background-color: <?= $rowBg ?>; color: <?= $count > 0 ? '#323130' : '#adb5bd' ?>;">
                                <?= $count > 0 ? number_format($count) : '-' ?>
                            </td>
                        <?php endforeach; ?>
                        <td align="center" style="border: 1px solid #dee2e6; padding: 4px 6px; font-weight: bold; background-color: #e8f4fd; color: #0078d4;">
                            <?= number_format($clinic['total']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <!-- TOTALS ROW -->
                <tr>
                    <td align="left" style="border: 1px solid #1a2530; padding: 5px 6px; font-weight: bold; background-color: #2c3e50; color: #ffffff;">
                        TOTAL GENERAL
                    </td>
                    <?php foreach ($monthKeys as $key): ?>
                        <?php $monthTotal = $monthlyTotals[$key] ?? 0; ?>
                        <td align="center" style="border: 1px solid #1a2530; padding: 5px 3px; font-weight: bold; background-color: #2c3e50; color: #4cd964;">
                            <?= number_format($monthTotal) ?>
                        </td>
                    <?php endforeach; ?>
                    <td align="center" style="border: 1px solid #1a2530; padding: 5px 6px; font-weight: bold; background-color: #0078d4; color: #ffffff; font-size: 8pt;">
                        <?= number_format($grandTotal) ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- SUMMARY FOOTNOTE -->
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 14px; font-family: Arial, sans-serif; font-size: 8pt; color: #6c757d;">
            <tr>
                <td align="center">
                    <strong style="color: #2c3e50;">Total de afiliados registrados en el período:</strong>
                    <span style="color: #0078d4; font-weight: bold; font-size: 10pt;"><?= number_format($grandTotal) ?></span>
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    <strong style="color: #2c3e50;">Clínicas con actividad:</strong>
                    <span style="color: #0078d4; font-weight: bold;"><?= count($clinics) ?></span>
                </td>
            </tr>
        </table>
    <?php else: ?>
        <p style="font-family: Arial, sans-serif; font-size: 11pt; color: #6c757d; text-align: center; padding: 40px;">
            No hay datos de afiliados para mostrar en el período seleccionado.
        </p>
    <?php endif; ?>

</div>

<!-- ============================================================ -->
<!-- PAGE 2: TIMELINE CHART                                        -->
<!-- ============================================================ -->
<div>

    <!-- HEADER -->
    <table width="100%" cellpadding="0" cellspacing="0" style="border-bottom: 2px solid #0078d4; margin-bottom: 8px; padding-bottom: 6px;">
        <tr>
            <td width="110" valign="middle" style="padding: 0;">
                <?php if (file_exists($logo)): ?>
                    <img src="<?= $logo ?>" width="100px" alt="SISPSA" style="display: block;">
                <?php endif; ?>
            </td>
            <td valign="middle" align="center" style="padding: 0;">
                <div style="font-family: Arial, sans-serif; font-size: 14pt; font-weight: bold; color: #2c3e50; line-height: 1.2;">
                    <?= Html::encode($chartTitle) ?>
                </div>
                <?php if (!empty($chartSubtitle)): ?>
                    <div style="font-family: Arial, sans-serif; font-size: 9pt; color: #0078d4; margin-top: 3px;">
                        <?= Html::encode($chartSubtitle) ?>
                    </div>
                <?php endif; ?>
            </td>
            <td width="110" valign="middle" align="right" style="padding: 0; font-family: Arial, sans-serif; font-size: 7pt; color: #605e5c;">
                <?= Html::encode($generatedAt) ?>
            </td>
        </tr>
    </table>

    <!-- CHART IMAGE -->
    <div style="text-align: center; padding: 6px 0;">
        <?php if (file_exists($chartImagePath)): ?>
            <img src="<?= $chartImagePath ?>"
                alt="Gráfico"
                width="260mm"
                height="130mm"
                style="display: block; margin: 0 auto;">
        <?php else: ?>
            <p style="color: #dc3545; font-weight: bold; font-family: Arial, sans-serif;">
                Error: No se pudo cargar la imagen del gráfico.
            </p>
        <?php endif; ?>
    </div>

    <!-- LEGEND -->
    <table width="100%" cellpadding="0" cellspacing="0" style="font-family: Arial, sans-serif; font-size: 9pt; color: #323130; border-top: 1px solid #dee2e6; padding-top: 6px;">
        <tr>
            <td align="center" style="color: #323130;">
                <span style="display: inline-block; width: 11px; height: 11px; background-color: #2ecc71; border: 1px solid #cccccc; vertical-align: middle; margin-right: 4px;"></span>
                <strong style="color: #323130;">Total Afiliados</strong>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <span style="display: inline-block; width: 11px; height: 11px; background-color: #3498db; border: 1px solid #cccccc; vertical-align: middle; margin-right: 4px;"></span>
                <strong style="color: #323130;">Individual</strong>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <span style="display: inline-block; width: 11px; height: 11px; background-color: #e74c3c; border: 1px solid #cccccc; vertical-align: middle; margin-right: 4px;"></span>
                <strong style="color: #323130;">Corporativo</strong>
            </td>
        </tr>
    </table>

</div>