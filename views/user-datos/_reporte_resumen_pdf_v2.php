<?php
// views/user-datos/_reporte_resumen_pdf_v2.php

use yii\helpers\Html;

/* @var $summary array */
/* @var $totals array */
/* @var $filtros array */
/* @var $logo string */
/* @var $metaSummary array */

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Resumen de Afiliados</title>
</head>

<body>

    <!-- Centered Large Logo at Top -->
    <table width="100%" style="border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td style="text-align: center; padding: 20px 0 10px 0;">
                <?php
                // Get the correct logo path
                $logoPath = null;
                if (!empty($logo) && file_exists(str_replace('@webroot', Yii::getAlias('@webroot'), $logo))) {
                    $logoPath = str_replace('@webroot', Yii::getAlias('@webroot'), $logo);
                } else {
                    $fallbackPath = Yii::getAlias('@webroot/img/sispsalogo.jpg');
                    if (file_exists($fallbackPath)) {
                        $logoPath = $fallbackPath;
                    }
                }

                if ($logoPath):
                ?>
                    <img src="<?= $logoPath ?>"
                        alt="Logo SISPSA"
                        style="width: 250px; height: auto; display: block; margin: 0 auto;">
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <!-- Title Section -->
    <table width="100%" style="border-collapse: collapse; margin-bottom: 5px;">
        <tr>
            <td style="text-align: center;">
                <h1 style="margin: 0; font-size: 22pt; color: #2c3e50;">RESUMEN DE AFILIADOS POR CLÍNICA</h1>
                <p style="margin: 8px 0 0 0; font-size: 11pt; color: #555;">Inscrita en la Superintendencia de la Actividad Aseguradora bajo el No. MP000013</p>
                <p style="margin: 3px 0 0 0; font-size: 10pt; color: #666;">R.I.F.: J-50654922</p>
                <p style="margin: 12px 0 0 0; font-size: 9pt; color: #888;">Generado: <?= date('d/m/Y H:i:s') ?></p>
            </td>
        </tr>
    </table>

    <hr style="border: 1px solid #2c3e50; margin: 15px 0 20px 0;">

    <?php if (!empty($filtros)): ?>
        <div style="background-color: #f8f9fa; padding: 8px; border: 1px solid #dee2e6; border-radius: 4px; margin-bottom: 20px;">
            <strong>Filtros aplicados:</strong> <?= implode(' | ', $filtros) ?>
        </div>
    <?php endif; ?>

    <!-- META SUMMARY SECTION - NEW -->
    <?php if (isset($metaSummary) && !empty($metaSummary)): ?>
        <div style="background-color: #e8f4fd; padding: 15px; border: 1px solid #b6d4fe; border-radius: 8px; margin-bottom: 20px;">
            <h3 style="margin: 0 0 12px 0; color: #0d6efd; font-size: 14pt; text-align: center;">📊 CUMPLIMIENTO DE METAS MENSUALES</h3>
            <table width="100%" style="border-collapse: collapse;">
                <tr>
                    <td align="center" style="padding: 8px;">
                        <div style="font-size: 20pt; font-weight: bold; color: #0d6efd;"><?= number_format($metaSummary['porcentaje_global_cumplimiento'] ?? 0) ?>%</div>
                        <div style="font-size: 9pt; color: #6c757d;">Cumplimiento Global</div>
                        <small><?= number_format($metaSummary['total_afiliados_actual'] ?? 0) ?> / <?= number_format($metaSummary['total_meta_objetivo'] ?? 0) ?> afiliados</small>
                    </td>
                    <td align="center" style="padding: 8px;">
                        <div style="font-size: 20pt; font-weight: bold; color: #28a745;"><?= $metaSummary['clinicas_que_alcanzaron_meta'] ?? 0 ?></div>
                        <div style="font-size: 9pt; color: #6c757d;">Clínicas que Alcanzaron Meta</div>
                        <small>≥100%</small>
                    </td>
                    <td align="center" style="padding: 8px;">
                        <div style="font-size: 20pt; font-weight: bold; color: #ffc107;"><?= $metaSummary['clinicas_cerca_meta'] ?? 0 ?></div>
                        <div style="font-size: 9pt; color: #6c757d;">Clínicas Cerca de Meta</div>
                        <small>75-99%</small>
                    </td>
                    <td align="center" style="padding: 8px;">
                        <div style="font-size: 20pt; font-weight: bold; color: #dc3545;"><?= $metaSummary['clinicas_lejos_meta'] ?? 0 ?></div>
                        <div style="font-size: 9pt; color: #6c757d;">Clínicas Lejos de Meta</div>
                        <small>&lt;75%</small>
                    </td>
                </tr>
            </table>
        </div>
    <?php endif; ?>

    <!-- Main Table with White Header Text - ADDED META COLUMN -->
    <table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-bottom: 25px;">
        <thead>
            <tr style="background-color: #2c3e50;">
                <th style="color: white; padding: 10px; text-align: center;">Clínica</th>
                <th style="color: white; padding: 10px; text-align: center;">Meta Mensual</th>
                <th style="color: white; padding: 10px; text-align: center;">Total</th>
                <th style="color: white; padding: 10px; text-align: center;">Individual</th>
                <th style="color: white; padding: 10px; text-align: center;">Corporativo</th>
                <th style="color: white; padding: 10px; text-align: center;">Activos</th>
                <th style="color: white; padding: 10px; text-align: center;">Suspendidos</th>
                <th style="color: white; padding: 10px; text-align: center;">Anulados</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($summary as $clinic): ?>
                <?php
                $meta = (int)($clinic['clinica_meta'] ?? 0);
                $totalAfiliadosClinica = $clinic['total_afiliados'];
                $metaPorcentaje = $meta > 0 ? round(($totalAfiliadosClinica / $meta) * 100) : 0;

                $metaDisplay = $meta > 0 ? number_format($meta) . " ({$metaPorcentaje}%)" : 'No definida';
                $metaColor = $meta > 0 ? ($metaPorcentaje >= 100 ? '#28a745' : ($metaPorcentaje >= 75 ? '#ffc107' : '#dc3545')) : '#6c757d';
                ?>
                <tr>
                    <td style="padding: 6px;"><strong><?= Html::encode($clinic['clinica_nombre']) ?></strong></td>
                    <td align="center" style="padding: 6px; color: <?= $metaColor ?>; font-weight: bold;">
                        <?= $metaDisplay ?>
                    </td>
                    <td align="center" style="padding: 6px;"><?= number_format($clinic['total_afiliados']) ?></td>
                    <td align="center" style="padding: 6px;"><?= number_format($clinic['tipo_individual']) ?></td>
                    <td align="center" style="padding: 6px;"><?= number_format($clinic['tipo_corporativo']) ?></td>
                    <td align="center" style="padding: 6px;"><?= number_format($clinic['contratos_activos'] ?? 0) ?></td>
                    <td align="center" style="padding: 6px;"><?= number_format($clinic['contratos_suspendidos'] ?? 0) ?></td>
                    <td align="center" style="padding: 6px;"><?= number_format($clinic['contratos_anulados'] ?? 0) ?></td>
                </tr>
            <?php endforeach; ?>

            <!-- Total Row with Meta Summary -->
            <tr style="background-color: #e8f4fd;">
                <td style="padding: 8px;"><strong>TOTAL GENERAL</strong></td>
                <td align="center" style="padding: 8px;">
                    <strong><?= number_format($totals['meta']['total_meta_objetivo'] ?? 0) ?></strong>
                </td>
                <td align="center" style="padding: 8px;"><strong><?= number_format($totals['total_afiliados']) ?></strong></td>
                <td align="center" style="padding: 8px;"><strong><?= number_format($totals['total_individual']) ?></strong></td>
                <td align="center" style="padding: 8px;"><strong><?= number_format($totals['total_corporativo']) ?></strong></td>
                <td align="center" style="padding: 8px;"><strong><?= number_format($totals['total_contratos_activos']) ?></strong></td>
                <td align="center" style="padding: 8px;"><strong><?= number_format($totals['total_contratos_suspendidos']) ?></strong></td>
                <td align="center" style="padding: 8px;"><strong><?= number_format($totals['total_contratos_anulados'] ?? 0) ?></strong></td>
            </tr>
        </tbody>
    </table>

    <!-- Summary Cards - Updated with Meta Info -->
    <h3 style="color: #2c3e50; margin-bottom: 15px; text-align: center;">Resumen General</h3>

    <table width="100%" style="border-collapse: collapse; margin-bottom: 20px;">
        <!-- Row 1: Basic Stats -->
        <tr>
            <td align="center" style="border: 1px solid #ddd; padding: 15px; width: 33%; border-radius: 8px;">
                <strong style="font-size: 12pt;">Total Afiliados</strong><br>
                <span style="font-size: 24pt; font-weight: bold; color: #3498db;"><?= number_format($totals['total_afiliados']) ?></span>
            </td>
            <td align="center" style="border: 1px solid #ddd; padding: 15px; width: 33%; border-radius: 8px;">
                <strong style="font-size: 12pt;">Individuales</strong><br>
                <span style="font-size: 24pt; font-weight: bold; color: #3498db;"><?= number_format($totals['total_individual']) ?></span><br>
                <span style="font-size: 8pt; color: #7f8c8d;"><?= round(($totals['total_individual'] / max($totals['total_afiliados'], 1)) * 100) ?>% del total</span>
            </td>
            <td align="center" style="border: 1px solid #ddd; padding: 15px; width: 33%; border-radius: 8px;">
                <strong style="font-size: 12pt;">Corporativos</strong><br>
                <span style="font-size: 24pt; font-weight: bold; color: #3498db;"><?= number_format($totals['total_corporativo']) ?></span><br>
                <span style="font-size: 8pt; color: #7f8c8d;"><?= round(($totals['total_corporativo'] / max($totals['total_afiliados'], 1)) * 100) ?>% del total</span>
            </td>
        </tr>

        <!-- Row 2: Contract Status -->
        <tr>
            <td align="center" style="border: 1px solid #ddd; padding: 15px; width: 33%; border-radius: 8px;">
                <strong style="font-size: 12pt;">Activos</strong><br>
                <span style="font-size: 24pt; font-weight: bold; color: #28a745;"><?= number_format($totals['total_contratos_activos']) ?></span><br>
                <span style="font-size: 8pt; color: #7f8c8d;">usuarios con contrato activo</span>
            </td>
            <td align="center" style="border: 1px solid #ddd; padding: 15px; width: 33%; border-radius: 8px;">
                <strong style="font-size: 12pt;">Suspendidos</strong><br>
                <span style="font-size: 24pt; font-weight: bold; color: #dc3545;"><?= number_format($totals['total_contratos_suspendidos']) ?></span><br>
                <span style="font-size: 8pt; color: #7f8c8d;">usuarios con cuotas vencidas</span>
            </td>
            <td align="center" style="border: 1px solid #ddd; padding: 15px; width: 33%; border-radius: 8px;">
                <strong style="font-size: 12pt;">Anulados</strong><br>
                <span style="font-size: 24pt; font-weight: bold; color: #6c757d;"><?= number_format($totals['total_contratos_anulados'] ?? 0) ?></span><br>
                <span style="font-size: 8pt; color: #7f8c8d;">usuarios con contratos anulados</span>
            </td>
        </tr>

        <!-- Row 3: System Stats -->
        <tr>
            <td align="center" style="border: 1px solid #ddd; padding: 15px; width: 33%; border-radius: 8px;">
                <strong style="font-size: 12pt;">Total Clínicas</strong><br>
                <span style="font-size: 24pt; font-weight: bold; color: #3498db;"><?= number_format($totals['total_clinicas']) ?></span><br>
                <span style="font-size: 8pt; color: #7f8c8d;">con afiliados registrados</span>
            </td>
            <td align="center" style="border: 1px solid #ddd; padding: 15px; width: 33%; border-radius: 8px;">
                <strong style="font-size: 12pt;">Total Contratos</strong><br>
                <span style="font-size: 24pt; font-weight: bold; color: #3498db;"><?= number_format($totals['total_contratos'] ?? 0) ?></span><br>
                <span style="font-size: 8pt; color: #7f8c8d;">registrados en sistema</span>
            </td>
            <td align="center" style="border: 1px solid #ddd; padding: 15px; width: 33%; border-radius: 8px;">
                <strong style="font-size: 12pt;">Contratos Activos</strong><br>
                <span style="font-size: 24pt; font-weight: bold; color: #28a745;"><?= number_format($totals['total_contratos_activos_count'] ?? 0) ?></span><br>
                <span style="font-size: 8pt; color: #7f8c8d;">en vigencia</span>
            </td>
        </tr>

        <!-- Row 4: Meta Statistics (NEW) -->
        <?php if (isset($metaSummary) && !empty($metaSummary)): ?>
            <tr>
                <td align="center" style="border: 1px solid #ddd; padding: 15px; width: 33%; border-radius: 8px; background-color: #e8f4fd;">
                    <strong style="font-size: 12pt; color: #0d6efd;">Clínicas con Meta</strong><br>
                    <span style="font-size: 24pt; font-weight: bold; color: #0d6efd;"><?= number_format($metaSummary['clinicas_con_meta'] ?? 0) ?></span><br>
                    <span style="font-size: 8pt; color: #6c757d;">tienen objetivo definido</span>
                </td>
                <td align="center" style="border: 1px solid #ddd; padding: 15px; width: 33%; border-radius: 8px; background-color: #e8f4fd;">
                    <strong style="font-size: 12pt; color: #0d6efd;">Clínicas sin Meta</strong><br>
                    <span style="font-size: 24pt; font-weight: bold; color: #6c757d;"><?= number_format($metaSummary['clinicas_sin_meta'] ?? 0) ?></span><br>
                    <span style="font-size: 8pt; color: #6c757d;">sin objetivo definido</span>
                </td>
                <td align="center" style="border: 1px solid #ddd; padding: 15px; width: 33%; border-radius: 8px; background-color: #e8f4fd;">
                    <strong style="font-size: 12pt; color: #0d6efd;">Total Objetivo</strong><br>
                    <span style="font-size: 24pt; font-weight: bold; color: #0d6efd;"><?= number_format($metaSummary['total_meta_objetivo'] ?? 0) ?></span><br>
                    <span style="font-size: 8pt; color: #6c757d;">afiliados en meta</span>
                </td>
            </tr>
        <?php endif; ?>
    </table>

    <!-- Footer -->
    <div style="margin-top: 30px; text-align: center; font-size: 8pt; color: #6c757d; border-top: 1px solid #dee2e6; padding-top: 10px;">
        Documento confidencial - Sistema SISPSA - Página {PAGENO} de {nbpg}
    </div>

</body>

</html>