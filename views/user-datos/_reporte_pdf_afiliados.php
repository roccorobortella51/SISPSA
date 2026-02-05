<?php
// views/user-datos/_reporte_pdf_afiliados.php

use yii\helpers\Html;

/* @var $affiliates array */
/* @var $filtros array */
/* @var $total integer */
/* @var $numeroClinicas integer */
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
</head>

<body>
<table style="width: 100%;">
    <tr>
        <td style="vertical-align: top; width: 100%; text-align: center;">
            <img src="<?= Html::encode($logo) ?>" alt="Logo SISPSA" class="logo-superior-izquierda" style="width: 100px; display: block; margin: 0 auto;">
            <div style="font-size: 10px; margin-top: 5px;">
                <div>Inscrita en la Superintendencia de la Actividad Aseguradora bajo el No. MP000013</div>
                <div>R.I.F.: J-50654922</div>
            </div>
        </td>
    </tr>
</table>
<br>


    <div class="header">
        <div class="title">REPORTE DE AFILIADOS - SISPSA</div>
        <div class="subtitle">Listado de Afiliados por Clínica</div>
        <div class="date">Generado: <?= date('d/m/Y H:i:s') ?></div>
    </div>

    <?php if (!empty($filtros)): ?>
        <div class="filters">
            <strong>Filtros aplicados:</strong><br>
            <?= implode(' | ', $filtros) ?>
        </div>
    <?php endif; ?>

    <table class="table">
        <thead>
            <tr>
                <th class="col-num">#</th>
                <th class="col-nombre">Nombre Completo</th>
                <th class="col-cedula">Cédula de Identidad</th>
                <th class="col-clinica">Clínica</th>
            </tr>
        </thead>
        <tbody>
            <?php $counter = 1; ?>
            <?php foreach ($affiliates as $affiliate): ?>
                <tr>
                    <td class="col-num"><?= $counter++ ?></td>
                    <td><?= Html::encode($affiliate->nombres . ' ' . $affiliate->apellidos) ?></td>
                    <td><?= Html::encode($affiliate->tipo_cedula . '-' . $affiliate->cedula) ?></td>
                    <td><?= Html::encode($affiliate->clinica ? $affiliate->clinica->nombre : '') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-row">
            <div class="summary-item">
                <strong>Total de Afiliados:</strong> <?= number_format($total) ?>
            </div>
            <div class="summary-item">
                <strong>Número de Clínicas:</strong> <?= number_format($numeroClinicas) ?>
            </div>
        </div>
    </div>

    <div class="footer">
        Documento confidencial - Sistema SISPSA - Página {PAGENO} de {nbpg}
    </div>
</body>

</html>