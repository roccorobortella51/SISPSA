<?php
// File: views/reportes/_comisiones-pdf.php
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
    <style type="text/css">
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.3;
            color: #000000;
            margin: 0;
            padding: 0;
        }

        .main-title {
            font-size: 18pt;
            font-weight: bold;
            color: #2c3e50;
            text-align: center;
            margin: 0 0 5px 0;
            padding: 0;
        }

        .subtitle {
            font-size: 11pt;
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

        .info-grid {
            display: table;
            width: 100%;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            font-weight: bold;
            color: #2c3e50;
            padding: 3px 8px 3px 0;
            width: 120px;
        }

        .info-value {
            display: table-cell;
            color: #333333;
            padding: 3px 0;
        }

        .summary-section {
            margin: 15px 0;
        }

        .summary-grid {
            width: 100%;
            margin-bottom: 15px;
        }

        .summary-card {
            border: 1px solid #dddddd;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .card-asesor {
            background-color: #fff9e6;
            border-color: #ffc107;
        }

        .card-agencia {
            background-color: #ffe6e6;
            border-color: #dc3545;
        }

        .card-clinica {
            background-color: #e6ffe6;
            border-color: #28a745;
        }

        .card-title {
            font-weight: bold;
            font-size: 10pt;
            color: #2c3e50;
        }

        .card-value {
            font-size: 16pt;
            font-weight: bold;
            text-align: center;
            padding: 5px 0;
        }

        .pdf-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
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
            vertical-align: middle;
        }

        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
            font-family: "Courier New", monospace;
            font-weight: bold;
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

        .totals-section {
            margin: 20px 0;
        }

        .totals-grid {
            width: 100%;
        }

        .total-card {
            border: 1px solid #dee2e6;
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .total-title {
            font-weight: bold;
            font-size: 10pt;
            color: #495057;
        }

        .total-value {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            color: #2c3e50;
        }

        .grand-total {
            background-color: #2c3e50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin: 20px 0;
            border: 2px solid #0078d4;
        }

        .grand-total-label {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .grand-total-value {
            font-size: 20pt;
            font-weight: bold;
            font-family: "Courier New", monospace;
            color: #4cd964;
        }

        .report-footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #dddddd;
            text-align: center;
            font-size: 7pt;
            color: #666666;
        }

        .mb-10 {
            margin-bottom: 10px;
        }

        .mb-15 {
            margin-bottom: 15px;
        }

        .mb-20 {
            margin-bottom: 20px;
        }

        .mt-10 {
            margin-top: 10px;
        }

        .mt-15 {
            margin-top: 15px;
        }

        .mt-20 {
            margin-top: 20px;
        }

        .page-break {
            page-break-before: always;
        }

        .page-break-avoid {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>
    <!-- HEADER -->
    <div class="header">
        <h1 class="main-title"><?= htmlspecialchars($title) ?></h1>
        <div class="subtitle"><?= htmlspecialchars($subtitle) ?></div>
    </div>

    <!-- REPORT INFORMATION -->
    <div class="report-info">
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Período:</div>
                <div class="info-value"><?= date('d/m/Y', strtotime($startDate)) ?> al <?= date('d/m/Y', strtotime($endDate)) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Generado:</div>
                <div class="info-value"><?= $generatedAt ?></div>
            </div>
            <?php if (!empty($clinicasSeleccionadas) && !in_array('todas', $clinicasSeleccionadas)): ?>
                <div class="info-row">
                    <div class="info-label">Clínicas:</div>
                    <div class="info-value">
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
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="summary-section page-break-avoid">
        <table class="summary-grid" cellpadding="0" cellspacing="0">
            <tr>
                <td width="33%" valign="top">
                    <div class="summary-card card-asesor">
                        <div class="card-title">Comisión Asesor</div>
                        <div class="card-value">Bs. <?= number_format($totalComisionAsesorBs, 2, ',', '.') ?></div>
                        <div style="text-align: center; font-size: 8pt; color: #666;">
                            USD <?= number_format($totalComisionAsesorUsd, 2, ',', '.') ?>
                        </div>
                    </div>
                </td>
                <td width="33%" valign="top">
                    <div class="summary-card card-agencia">
                        <div class="card-title">Comisión Agencia</div>
                        <div class="card-value">Bs. <?= number_format($totalComisionAgenciaBs, 2, ',', '.') ?></div>
                        <div style="text-align: center; font-size: 8pt; color: #666;">
                            USD <?= number_format($totalComisionAgenciaUsd, 2, ',', '.') ?>
                        </div>
                    </div>
                </td>
                <td width="33%" valign="top">
                    <div class="summary-card card-clinica">
                        <div class="card-title">Pago a Clínicas</div>
                        <div class="card-value">Bs. <?= number_format($totalComisionClinicaBs, 2, ',', '.') ?></div>
                        <div style="text-align: center; font-size: 8pt; color: #666;">
                            USD <?= number_format($totalComisionClinicaUsd, 2, ',', '.') ?>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- DETAILED TABLE -->
    <div class="table-section page-break-avoid">
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
                            <td class="text-left"><?= htmlspecialchars($afiliado) ?></td>
                            <td class="text-center"><?= htmlspecialchars($cedula) ?></td>
                            <td class="text-right col-montos"><?= number_format($montoUsd, 2, ',', '.') ?></td>
                            <td class="text-center"><?= $tasaDia > 0 ? number_format($tasaDia, 2, ',', '.') : 'N/A' ?></td>
                            <td class="text-right col-montos"><?= number_format($montoBs, 2, ',', '.') ?></td>
                            <td class="text-right col-asesor"><?= number_format($comisionAsesorBs, 2, ',', '.') ?></td>
                            <td class="text-right col-asesor"><?= number_format($comisionAsesorUsd, 2, ',', '.') ?></td>
                            <td class="text-right col-agencia"><?= number_format($comisionAgenciaBs, 2, ',', '.') ?></td>
                            <td class="text-right col-agencia"><?= number_format($comisionAgenciaUsd, 2, ',', '.') ?></td>
                            <td class="text-right col-clinica"><?= number_format($pagoClinicaBs, 2, ',', '.') ?></td>
                            <td class="text-right col-clinica"><?= number_format($pagoClinicaUsd, 2, ',', '.') ?></td>
                            <td class="text-center"><?= htmlspecialchars($clinicaNombre) ?></td>
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

    <!-- SUMMARY BY CLINIC -->
    <?php if (!empty($summaryPorClinica)): ?>
        <div class="table-section page-break-avoid">
            <div style="background-color: #2c3e50; color: white; padding: 8px; font-weight: bold; text-align: center; border-radius: 3px 3px 0 0;">
                RESUMEN POR CLÍNICA
            </div>

            <table class="pdf-table">
                <thead>
                    <tr>
                        <th width="25%" class="text-left">Clínica</th>
                        <th width="15%">RIF</th>
                        <th width="10%">Total Pagos</th>
                        <th width="10%">Conciliados</th>
                        <th width="10%">Pendientes</th>
                        <th width="15%" class="col-asesor">Com. Asesor Bs.</th>
                        <th width="15%" class="col-agencia">Com. Agencia Bs.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $clinicaTotalPagos = 0;
                    $clinicaTotalConciliados = 0;
                    $clinicaTotalPendientes = 0;
                    $clinicaTotalComisionAsesor = 0;
                    $clinicaTotalComisionAgencia = 0;
                    ?>

                    <?php foreach ($summaryPorClinica as $index => $resumen): ?>
                        <?php
                        $clinicaTotalPagos += $resumen['total_pagos'] ?? 0;
                        $clinicaTotalConciliados += $resumen['conciliados'] ?? 0;
                        $clinicaTotalPendientes += $resumen['pendientes'] ?? 0;
                        $clinicaTotalComisionAsesor += $resumen['total_comision_asesor_bs'] ?? 0;
                        $clinicaTotalComisionAgencia += $resumen['total_comision_agencia_bs'] ?? 0;
                        ?>
                        <tr class="<?= $index % 2 == 0 ? 'row-highlight' : '' ?>">
                            <td class="text-left"><?= htmlspecialchars($resumen['clinica_nombre'] ?? 'N/A') ?></td>
                            <td class="text-center"><?= htmlspecialchars($resumen['clinica_rif'] ?? 'N/A') ?></td>
                            <td class="text-center"><?= $resumen['total_pagos'] ?? 0 ?></td>
                            <td class="text-center"><?= $resumen['conciliados'] ?? 0 ?></td>
                            <td class="text-center"><?= $resumen['pendientes'] ?? 0 ?></td>
                            <td class="text-right col-asesor"><?= number_format($resumen['total_comision_asesor_bs'] ?? 0, 2, ',', '.') ?></td>
                            <td class="text-right col-agencia"><?= number_format($resumen['total_comision_agencia_bs'] ?? 0, 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- CLINICA TOTALS ROW -->
                    <tr style="background-color: #2c3e50; color: white; font-weight: bold;">
                        <td class="text-right" colspan="2" style="color: white;">TOTALES CLÍNICAS:</td>
                        <td class="text-center" style="color: white;"><?= $clinicaTotalPagos ?></td>
                        <td class="text-center" style="color: white;"><?= $clinicaTotalConciliados ?></td>
                        <td class="text-center" style="color: white;"><?= $clinicaTotalPendientes ?></td>
                        <td class="text-right" style="color: white;"><?= number_format($clinicaTotalComisionAsesor, 2, ',', '.') ?></td>
                        <td class="text-right" style="color: white;"><?= number_format($clinicaTotalComisionAgencia, 2, ',', '.') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- GRAND TOTAL -->
    <div class="grand-total page-break-avoid">
        <div class="grand-total-label">TOTAL GENERAL DE COMISIONES</div>
        <div class="grand-total-value">
            Bs. <?= number_format($totalComisionAsesorBs + $totalComisionAgenciaBs, 2, ',', '.') ?>
        </div>
        <div style="font-size: 9pt; margin-top: 5px;">
            USD <?= number_format($totalComisionAsesorUsd + $totalComisionAgenciaUsd, 2, ',', '.') ?>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="report-footer">
        <div>Reporte generado automáticamente por el Sistema SISPSA</div>
        <div>Total de registros procesados: <?= count($models) ?></div>
        <div>Documento confidencial - Uso interno</div>
    </div>
</body>

</html>