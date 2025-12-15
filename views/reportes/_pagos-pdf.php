<?php
// app/views/reportes/_pagos-pdf.php

use yii\helpers\Html;

// Simple PDF-friendly styling
$this->registerCss('
    body { 
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 11px;
        margin: 0;
        padding: 10px;
    }
    .pdf-header {
        text-align: center;
        margin-bottom: 20px;
        border-bottom: 2px solid #000;
        padding-bottom: 10px;
    }
    .pdf-title {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .pdf-subtitle {
        font-size: 12px;
        color: #666;
    }
    .pdf-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    .pdf-table th {
        background-color: #f2f2f2;
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
        font-weight: bold;
        font-size: 10px;
    }
    .pdf-table td {
        border: 1px solid #ddd;
        padding: 6px;
        font-size: 9px;
    }
    .pdf-total {
        font-weight: bold;
        background-color: #f9f9f9;
        text-align: right;
        padding: 8px;
        margin-top: 10px;
    }
    .page-break {
        page-break-after: always;
    }
    .no-break {
        page-break-inside: avoid;
    }
');
?>

<div class="pdf-header">
    <div class="pdf-title"><?= Html::encode($title) ?></div>
    <div class="pdf-subtitle">
        Periodo: <?= Yii::$app->formatter->asDate($startDate, 'long') ?> al <?= Yii::$app->formatter->asDate($endDate, 'long') ?>
    </div>
    <div class="pdf-subtitle">
        Generado: <?= date('d/m/Y H:i:s') ?>
    </div>
</div>

<?php if (!empty($summaryPorClinica)): ?>
    <div class="no-break">
        <h4 style="font-size: 12px; margin-top: 15px;">Resumen por Clínica</h4>
        <table class="pdf-table">
            <thead>
                <tr>
                    <th>Clínica</th>
                    <th>RIF</th>
                    <th>Total Pagos</th>
                    <th>Conciliados</th>
                    <th>Pendientes</th>
                    <th>Total (Bs.)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($summaryPorClinica as $resumen): ?>
                    <tr>
                        <td><?= Html::encode($resumen['clinica_nombre'] ?? 'N/A') ?></td>
                        <td><?= Html::encode($resumen['clinica_rif'] ?? 'N/A') ?></td>
                        <td style="text-align: center;"><?= number_format($resumen['total_pagos'] ?? 0) ?></td>
                        <td style="text-align: center;"><?= number_format($resumen['conciliados'] ?? 0) ?></td>
                        <td style="text-align: center;"><?= number_format($resumen['pendientes'] ?? 0) ?></td>
                        <td style="text-align: right;"><?= Yii::$app->formatter->asCurrency($resumen['total_monto'] ?? 0, 'VES') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<div class="no-break" style="margin-top: 20px;">
    <h4 style="font-size: 12px;">Detalle de Pagos</h4>
    <table class="pdf-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Afiliado</th>
                <th>Cédula</th>
                <th>Monto (Bs.)</th>
                <th>Fecha</th>
                <th>Método</th>
                <th>Estado</th>
                <th>Clínica</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dataProvider->getModels() as $model): ?>
                <tr>
                    <td><?= $model->id ?></td>
                    <td>
                        <?php
                        $nombre = $model->userDatos ?
                            $model->userDatos->nombres . ' ' . $model->userDatos->apellidos :
                            'N/A';
                        echo Html::encode($nombre);
                        ?>
                    </td>
                    <td><?= Html::encode($model->userDatos ? $model->userDatos->cedula : 'N/A') ?></td>
                    <td style="text-align: right;"><?= Yii::$app->formatter->asCurrency($model->monto_usd, 'VES') ?></td>
                    <td><?= Yii::$app->formatter->asDate($model->fecha_pago, 'php:d/m/Y') ?></td>
                    <td><?= Html::encode($model->metodo_pago ?: 'N/A') ?></td>
                    <td><?= Html::encode($model->estatus) ?></td>
                    <td>
                        <?php
                        $clinicaNombre = 'Sin Clínica';
                        if ($model->contratos && count($model->contratos) > 0) {
                            foreach ($model->contratos as $contrato) {
                                if ($contrato->clinica) {
                                    $clinicaNombre = Html::encode($contrato->clinica->nombre);
                                    break;
                                }
                            }
                        }
                        echo $clinicaNombre;
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="pdf-total">
    Total General: <?= Yii::$app->formatter->asCurrency($summary['total_monto'], 'VES') ?>
    (<?= number_format($summary['total_count']) ?> pagos)
</div>