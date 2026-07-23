<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $clinic app\models\RmClinica */
/* @var $intermediariosWithCounts array */
/* @var $summary array */
/* @var $logo string */
/* @var $fecha_generacion string */

// Safely get the clinic
$clinic = isset($clinic) ? $clinic : (isset($model) ? $model : null);

// Ensure intermediariosWithCounts is an array
$intermediariosWithCounts = isset($intermediariosWithCounts) ? $intermediariosWithCounts : [];

// Default summary if not provided
$summary = isset($summary) ? $summary : [
    'total_intermediarios' => 0,
    'total_agencias' => 0,
];

// Default fecha
$fecha_generacion = isset($fecha_generacion) ? $fecha_generacion : date('d/m/Y H:i');

// If no clinic, show error
if (!$clinic) {
    echo '<h1>Error: No se encontró la clínica</h1>';
    return;
}
?>

<div class="header">
    <?php if (isset($logo) && file_exists($logo)): ?>
        <img src="<?= $logo ?>" alt="SISPSA Logo" style="height: 60px; margin-bottom: 5px;">
    <?php endif; ?>
    <div class="title">REPORTE DE INTERMEDIARIOS POR CLÍNICA</div>
    <div class="subtitle"><?= Html::encode($clinic->nombre) ?></div>
    <?php if (!empty($clinic->codigo_clinica)): ?>
        <div class="subtitle" style="font-size: 10pt;">Código: <?= Html::encode($clinic->codigo_clinica) ?></div>
    <?php endif; ?>
    <div class="date">Generado: <?= $fecha_generacion ?></div>
</div>

<div class="summary-cards">
    <div class="summary-item">
        <span class="number"><?= number_format($summary['total_intermediarios'] ?? 0) ?></span>
        <span class="label">Total Intermediarios</span>
    </div>
    <div class="summary-item">
        <span class="number"><?= number_format($summary['total_agencias'] ?? 0) ?></span>
        <span class="label">Agencias Asociadas</span>
    </div>
</div>

<table class="table">
    <thead>
        <tr>
            <th style="width: 25px;">#</th>
            <th style="width: 130px;">Nombre</th>
            <th style="width: 70px;">Cédula</th>
            <th style="width: 110px;">Email</th>
            <th style="width: 70px;">Teléfono</th>
            <th style="width: 90px;">Agencia</th>
            <th style="width: 80px;">Código SUDEASEG</th>
            <th style="width: 45px;">Afiliados</th>
            <th style="width: 45px;">% Venta</th>
            <th style="width: 70px;">Fecha Creación</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($intermediariosWithCounts)): ?>
            <tr>
                <td colspan="10" class="text-center" style="padding: 20px; color: #6c757d;">
                    No se encontraron intermediarios para esta clínica.
                </td>
            </tr>
        <?php else: ?>
            <?php $counter = 1; ?>
            <?php foreach ($intermediariosWithCounts as $item): ?>
                <?php
                $intermediario = $item['model'];
                $affiliateCount = $item['affiliateCount'];
                $userDatos = $intermediario->userDatos;
                ?>
                <tr>
                    <td class="text-center"><?= $counter ?></td>
                    <td><?= $userDatos ? Html::encode($userDatos->nombres . ' ' . $userDatos->apellidos) : 'N/A' ?></td>
                    <td class="text-center"><?= $userDatos ? Html::encode($userDatos->tipo_cedula . '-' . $userDatos->cedula) : 'N/A' ?></td>
                    <td><?= $userDatos ? Html::encode($userDatos->email) : 'N/A' ?></td>
                    <td class="text-center"><?= $userDatos ? Html::encode($userDatos->telefono) : 'N/A' ?></td>
                    <td><?= $intermediario->agente ? Html::encode($intermediario->agente->nom) : 'N/A' ?></td>
                    <td class="text-center"><?= $intermediario->registro_corredor_actividad_aseguradora ?: 'N/A' ?></td>
                    <td class="text-center"><strong><?= number_format($affiliateCount) ?></strong></td>
                    <td class="text-center"><?= number_format($intermediario->por_venta ?? 0, 1) ?>%</td>
                    <td class="text-center"><?= Yii::$app->formatter->asDate($intermediario->created_at, 'php:d/m/Y') ?></td>
                </tr>
                <?php $counter++; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<div class="footer">
    <p>Este reporte muestra todos los intermediarios asociados a la clínica.</p>
    <p>SISPSA - Sistema de Gestión de Seguros y Salud &bull; Total de intermediarios: <?= number_format($summary['total_intermediarios'] ?? 0) ?></p>
</div>