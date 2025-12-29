<?php

use yii\helpers\Html;
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style type="text/css">
        body {
            font-family: helvetica, sans-serif;
            font-size: 10pt;
            color: #000000;
            margin: 0;
            padding: 0;
        }

        .main-title {
            font-size: 16pt;
            font-weight: bold;
            color: #2c3e50;
            text-align: center;
            margin: 0 0 10px 0;
            padding: 0;
        }

        .subtitle {
            font-size: 12pt;
            color: #0078d4;
            text-align: center;
            margin: 0 0 15px 0;
            padding: 0;
            font-weight: bold;
        }

        .report-info {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 8px;
            margin: 0 0 15px 0;
            border-radius: 3px;
        }

        .info-label {
            font-weight: bold;
            color: #2c3e50;
        }

        .pdf-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 8pt;
        }

        .pdf-table th {
            background-color: #2c3e50;
            color: white;
            font-weight: bold;
            padding: 6px;
            text-align: center;
            border: 1px solid #1a2530;
        }

        .pdf-table td {
            padding: 5px;
            border: 1px solid #dddddd;
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .row-highlight {
            background-color: #f8f9fa;
        }

        .col-montos {
            background-color: #e8f4fd;
        }

        .col-asesor {
            background-color: #fff9e6;
        }

        .col-agencia {
            background-color: #ffe6e6;
        }

        .col-clinica {
            background-color: #e8f7e8;
        }

        .grand-total {
            background-color: #2c3e50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin: 20px 0;
        }

        .grand-total-value {
            font-size: 18pt;
            font-weight: bold;
            color: #4cd964;
        }
    </style>
</head>

<body>

    <h1 class="main-title"><?= Html::encode($title) ?></h1>
    <div class="subtitle"><?= Html::encode($subtitle) ?></div>

    <div class="report-info">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td width="100" class="info-label">Período:</td>
                <td><?= date('d/m/Y', strtotime($startDate)) ?> al <?= date('d/m/Y', strtotime($endDate)) ?></td>
            </tr>
            <tr>
                <td class="info-label">Generado:</td>
                <td><?= $generatedAt ?></td>
            </tr>
            <?php if (!empty($clinicasSeleccionadas) && !in_array('todas', $clinicasSeleccionadas)): ?>
                <tr>
                    <td class="info-label">Clínicas:</td>
                    <td>
                        <?php
                        $clinicaCount = count($clinicasSeleccionadas);
                        if ($clinicaCount > 3) {
                            echo $clinicaCount . ' clínicas seleccionadas';
                        } else {
                            $nombres = [];
                            foreach ($clinicasSeleccionadas as $clinicaId) {
                                $clinica = \app\models\RmClinica::findOne($clinicaId);
                                if ($clinica) $nombres[] = $clinica->nombre;
                            }
                            echo implode(', ', $nombres);
                        }
                        ?>
                    </td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- SUMMARY CARDS -->
    <table width="100%" cellpadding="0" cellspacing="10">
        <tr>
            <td width="33%" valign="top">
                <div style="background-color: #fff9e6; border: 1px solid #ffc107; border-radius: 5px; padding: 10px;">
                    <div style="font-weight: bold; font-size: 10pt; color: #2c3e50;">Comisión Asesor</div>
                    <div style="font-size: 16pt; font-weight: bold; text-align: center; padding: 5px 0;">
                        Bs. <?= number_format($totalComisionAsesorBs, 2, ',', '.') ?>
                    </div>
                    <div style="text-align: center; font-size: 8pt; color: #666;">
                        USD <?= number_format($totalComisionAsesorUsd, 2, ',', '.') ?>
                    </div>
                </div>
            </td>
            <td width="33%" valign="top">
                <div style="background-color: #ffe6e6; border: 1px solid #dc3545; border-radius: 5px; padding: 10px;">
                    <div style="font-weight: bold; font-size: 10pt; color: #2c3e50;">Comisión Agencia</div>
                    <div style="font-size: 16pt; font-weight: bold; text-align: center; padding: 5px 0;">
                        Bs. <?= number_format($totalComisionAgenciaBs, 2, ',', '.') ?>
                    </div>
                    <div style="text-align: center; font-size: 8pt; color: #666;">
                        USD <?= number_format($totalComisionAgenciaUsd, 2, ',', '.') ?>
                    </div>
                </div>
            </td>
            <td width="33%" valign="top">
                <div style="background-color: #e6ffe6; border: 1px solid #28a745; border-radius: 5px; padding: 10px;">
                    <div style="font-weight: bold; font-size: 10pt; color: #2c3e50;">Pago a Clínicas</div>
                    <div style="font-size: 16pt; font-weight: bold; text-align: center; padding: 5px 0;">
                        Bs. <?= number_format($totalComisionClinicaBs, 2, ',', '.') ?>
                    </div>
                    <div style="text-align: center; font-size: 8pt; color: #666;">
                        USD <?= number_format($totalComisionClinicaUsd, 2, ',', '.') ?>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- DETAILED TABLE -->
    <div style="margin: 15px 0;">
        <div style="background-color: #2c3e50; color: white; padding: 8px; font-weight: bold; text-align: center; border-radius: 3px 3px 0 0;">
            DETALLE DE COMISIONES
        </div>

        <?php if (empty($models)): ?>
            <div style="text-align: center; padding: 20px; font-style: italic; color: #666;">
                No hay datos para el período seleccionado
            </div>
        <?php else: ?>
            <table class="pdf-table">
                <thead>
                    <tr>
                        <th width="4%">#</th>
                        <th width="12%" class="text-left">Afiliado</th>
                        <th width="8%">Cédula</th>
                        <th width="7%" class="col-montos">USD</th>
                        <th width="7%">Tasa</th>
                        <th width="7%" class="col-montos">Bs.</th>
                        <th width="9%" class="col-asesor">Com. Asesor Bs.</th>
                        <th width="9%" class="col-asesor">Com. Asesor USD</th>
                        <th width="9%" class="col-agencia">Com. Agencia Bs.</th>
                        <th width="9%" class="col-agencia">Com. Agencia USD</th>
                        <th width="9%" class="col-clinica">Pago Clínica Bs.</th>
                        <th width="9%" class="col-clinica">Pago Clínica USD</th>
                        <th width="6%">Clínica</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $consecutivo = 1;
                    $totalMontoBs = 0;
                    $totalMontoUsd = 0;
                    $totalComisionAsesorBs = 0;
                    $totalComisionAsesorUsd = 0;
                    $totalComisionAgenciaBs = 0;
                    $totalComisionAgenciaUsd = 0;
                    $totalPagoClinicaBs = 0;
                    $totalPagoClinicaUsd = 0;
                    ?>

                    <?php foreach ($models as $index => $model): ?>
                        <?php
                        // Get data from model
                        $montoBs = $model->monto_usd;        // Actually Bs.
                        $montoUsd = $model->monto_pagado;    // Actually USD

                        // Calculate exchange rate
                        $tasaDia = 0;
                        if ($montoUsd > 0 && $montoBs > 0) {
                            $tasaDia = $montoBs / $montoUsd;  // Bs. per USD
                        }

                        // Calculate commissions
                        $comisionAsesorBs = $montoBs * 0.10;
                        $comisionAsesorUsd = $tasaDia > 0 ? $comisionAsesorBs / $tasaDia : 0;
                        $comisionAgenciaBs = $montoBs * 0.04;
                        $comisionAgenciaUsd = $tasaDia > 0 ? $comisionAgenciaBs / $tasaDia : 0;
                        $pagoClinicaBs = $montoBs * 0.70;      // 70% of Bs.
                        $pagoClinicaUsd = $montoUsd * 0.70;    // 70% of USD

                        // Accumulate totals
                        $totalMontoBs += $montoBs;
                        $totalMontoUsd += $montoUsd;
                        $totalComisionAsesorBs += $comisionAsesorBs;
                        $totalComisionAsesorUsd += $comisionAsesorUsd;
                        $totalComisionAgenciaBs += $comisionAgenciaBs;
                        $totalComisionAgenciaUsd += $comisionAgenciaUsd;
                        $totalPagoClinicaBs += $pagoClinicaBs;
                        $totalPagoClinicaUsd += $pagoClinicaUsd;

                        // Get clinic name
                        $clinicaNombre = 'Sin Clínica';
                        if ($model->contratos && count($model->contratos) > 0) {
                            foreach ($model->contratos as $contrato) {
                                if ($contrato->clinica) {
                                    $clinicaNombre = $contrato->clinica->nombre;
                                    break;
                                }
                            }
                        }

                        // Get affiliate name
                        $afiliado = 'N/A';
                        if ($model->userDatos) {
                            $afiliado = $model->userDatos->nombres . ' ' . $model->userDatos->apellidos;
                        }

                        // Get cedula
                        $cedula = $model->userDatos ? $model->userDatos->cedula : 'N/A';
                        ?>
                        <tr class="<?= $index % 2 == 0 ? 'row-highlight' : '' ?>">
                            <td class="text-center"><?= $consecutivo++ ?></td>
                            <td class="text-left"><?= Html::encode($afiliado) ?></td>
                            <td class="text-center"><?= Html::encode($cedula) ?></td>
                            <td class="text-right col-montos"><?= number_format($montoUsd, 2, ',', '.') ?></td>
                            <td class="text-center"><?= $tasaDia > 0 ? number_format($tasaDia, 2, ',', '.') : 'N/A' ?></td>
                            <td class="text-right col-montos"><?= number_format($montoBs, 2, ',', '.') ?></td>
                            <td class="text-right col-asesor"><?= number_format($comisionAsesorBs, 2, ',', '.') ?></td>
                            <td class="text-right col-asesor"><?= number_format($comisionAsesorUsd, 2, ',', '.') ?></td>
                            <td class="text-right col-agencia"><?= number_format($comisionAgenciaBs, 2, ',', '.') ?></td>
                            <td class="text-right col-agencia"><?= number_format($comisionAgenciaUsd, 2, ',', '.') ?></td>
                            <td class="text-right col-clinica"><?= number_format($pagoClinicaBs, 2, ',', '.') ?></td>
                            <td class="text-right col-clinica"><?= number_format($pagoClinicaUsd, 2, ',', '.') ?></td>
                            <td class="text-center"><?= Html::encode($clinicaNombre) ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- TOTALS ROW -->
                    <tr style="background-color: #2c3e50; color: white; font-weight: bold;">
                        <td colspan="3" class="text-right">TOTALES:</td>
                        <td class="text-right" style="color: white;"><?= number_format($totalMontoUsd, 2, ',', '.') ?></td>
                        <td></td>
                        <td class="text-right" style="color: white;"><?= number_format($totalMontoBs, 2, ',', '.') ?></td>
                        <td class="text-right" style="color: white;"><?= number_format($totalComisionAsesorBs, 2, ',', '.') ?></td>
                        <td class="text-right" style="color: white;"><?= number_format($totalComisionAsesorUsd, 2, ',', '.') ?></td>
                        <td class="text-right" style="color: white;"><?= number_format($totalComisionAgenciaBs, 2, ',', '.') ?></td>
                        <td class="text-right" style="color: white;"><?= number_format($totalComisionAgenciaUsd, 2, ',', '.') ?></td>
                        <td class="text-right" style="color: white;"><?= number_format($totalPagoClinicaBs, 2, ',', '.') ?></td>
                        <td class="text-right" style="color: white;"><?= number_format($totalPagoClinicaUsd, 2, ',', '.') ?></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- GRAND TOTAL -->
    <div class="grand-total">
        <div style="font-size: 12pt; font-weight: bold; margin-bottom: 5px;">TOTAL GENERAL DE COMISIONES</div>
        <div class="grand-total-value">
            Bs. <?= number_format($totalComisionAsesorBs + $totalComisionAgenciaBs, 2, ',', '.') ?>
        </div>
        <div style="font-size: 9pt; margin-top: 5px;">
            USD <?= number_format($totalComisionAsesorUsd + $totalComisionAgenciaUsd, 2, ',', '.') ?>
        </div>
    </div>

    <!-- FOOTER -->
    <div style="margin-top: 20px; padding-top: 10px; border-top: 1px solid #dddddd; text-align: center; font-size: 7pt; color: #666666;">
        <div>Reporte generado automáticamente por el Sistema SISPSA</div>
        <div>Total de registros procesados: <?= count($models) ?></div>
        <div>Documento confidencial - Uso interno</div>
    </div>

</body>

</html>