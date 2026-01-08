<?php
// app/views/reporte-atenciones/_pdf_report.php

use yii\helpers\Html;

/** @var array $reportData */
/** @var app\models\SisSiniestroReporteSearch $searchModel */
?>

<style>
    .pdf-report {
        font-family: Arial, sans-serif;
    }

    .pdf-header {
        border-bottom: 2px solid #0078d4;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }

    .pdf-title {
        color: #0078d4;
        font-size: 24px;
        font-weight: bold;
    }

    .pdf-subtitle {
        color: #666;
        font-size: 14px;
    }

    .pdf-card {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 15px;
        margin-bottom: 15px;
    }

    .pdf-card-title {
        font-weight: bold;
        color: #333;
        margin-bottom: 10px;
    }

    .pdf-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    .pdf-table th {
        background-color: #f5f5f5;
        border: 1px solid #ddd;
        padding: 8px;
        font-weight: bold;
        text-align: left;
    }

    .pdf-table td {
        border: 1px solid #ddd;
        padding: 8px;
    }

    .pdf-summary {
        background-color: #f9f9f9;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    .pdf-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: bold;
    }

    .badge-success {
        background-color: #d4edda;
        color: #155724;
    }

    .badge-warning {
        background-color: #fff3cd;
        color: #856404;
    }

    .badge-danger {
        background-color: #f8d7da;
        color: #721c24;
    }

    .badge-info {
        background-color: #d1ecf1;
        color: #0c5460;
    }
</style>

<div class="pdf-report">
    <!-- Header -->
    <div class="pdf-header">
        <div class="pdf-title">
            <i class="fas fa-hospital"></i> Reporte de Atenciones por Clínica
        </div>
        <div class="pdf-subtitle">
            Período: <?= Yii::$app->formatter->asDate($reportData['summary']['date_range']['from']) ?>
            al <?= Yii::$app->formatter->asDate($reportData['summary']['date_range']['to']) ?> |
            Generado: <?= date('d/m/Y H:i:s') ?>
        </div>
    </div>

    <!-- Summary -->
    <div class="pdf-summary">
        <h3>Resumen General</h3>
        <div class="row">
            <div class="col-3">
                <strong>Clínicas Activas:</strong> <?= $reportData['summary']['total_clinics'] ?>
            </div>
            <div class="col-3">
                <strong>Total Atenciones:</strong> <?= number_format($reportData['summary']['total_attentions']) ?>
            </div>
            <div class="col-3">
                <strong>Pacientes Únicos:</strong> <?= number_format($reportData['summary']['total_patients']) ?>
            </div>
            <div class="col-3">
                <strong>Costo Total:</strong> $<?= number_format($reportData['summary']['total_cost'], 2) ?>
            </div>
        </div>
    </div>

    <!-- Main Table -->
    <h3 style="color: white !important;">Detalle por Clínica</h3>
    <table class="pdf-table">
        <thead>
            <tr>
                <th>Clínica</th>
                <th>Total</th>
                <th>Atendidas</th>
                <th>No Atendidos</th>
                <th>Pacientes</th>
                <th>Tasa %</th>
                <th>Costo</th>
                <th>Desempeño</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reportData['data'] as $item): ?>
                <tr>
                    <td><?= Html::encode($item['clinic_name']) ?></td>
                    <td><?= number_format($item['total_attentions']) ?></td>
                    <td><?= number_format($item['attended_count']) ?></td>
                    <td><?= number_format($item['pending_count']) ?></td>
                    <td><?= number_format($item['unique_patients']) ?></td>
                    <td><?= number_format($item['attendance_rate'], 1) ?>%</td>
                    <td>$<?= number_format($item['total_cost'], 2) ?></td>
                    <td>
                        <?php
                        $badgeClass = 'badge-secondary';
                        $text = 'Bajo';

                        if ($item['attendance_rate'] >= 90) {
                            $badgeClass = 'badge-success';
                            $text = 'Excelente';
                        } elseif ($item['attendance_rate'] >= 75) {
                            $badgeClass = 'badge-info';
                            $text = 'Bueno';
                        } elseif ($item['attendance_rate'] >= 60) {
                            $badgeClass = 'badge-warning';
                            $text = 'Regular';
                        }
                        ?>
                        <span class="pdf-badge <?= $badgeClass ?>"><?= $text ?></span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td><strong>TOTALES</strong></td>
                <td><strong><?= number_format($reportData['summary']['total_attentions']) ?></strong></td>
                <td></td>
                <td></td>
                <td><strong><?= number_format($reportData['summary']['total_patients']) ?></strong></td>
                <td></td>
                <td><strong>$<?= number_format($reportData['summary']['total_cost'], 2) ?></strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <!-- Performance Analysis -->
    <h3>Análisis de Desempeño</h3>
    <?php
    $performanceLevels = [
        'excelente' => ['count' => 0, 'text' => 'Excelente (≥90%)'],
        'bueno' => ['count' => 0, 'text' => 'Bueno (75-89%)'],
        'regular' => ['count' => 0, 'text' => 'Regular (60-74%)'],
        'bajo' => ['count' => 0, 'text' => 'Bajo (<60%)'],
    ];

    foreach ($reportData['data'] as $item) {
        if (isset($performanceLevels[$item['performance_level']])) {
            $performanceLevels[$item['performance_level']]['count']++;
        }
    }
    ?>

    <table class="pdf-table">
        <thead>
            <tr>
                <th>Nivel de Desempeño</th>
                <th>Clínicas</th>
                <th>Porcentaje</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($performanceLevels as $level => $data): ?>
                <tr>
                    <td><?= $data['text'] ?></td>
                    <td><?= $data['count'] ?></td>
                    <td>
                        <?= $reportData['summary']['total_clinics'] > 0 ?
                            round(($data['count'] / $reportData['summary']['total_clinics']) * 100, 1) : 0 ?>%
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Footer -->
    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666;">
        <div class="row">
            <div class="col-6">
                Sistema de Gestión Médica<br>
                Reporte generado automáticamente
            </div>
            <div class="col-6 text-right">
                Página 1 de 1<br>
                <?= date('d/m/Y H:i:s') ?>
            </div>
        </div>
    </div>
</div>