<?php

use yii\helpers\Html;
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 15px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
        }

        .title {
            font-size: 18pt;
            font-weight: bold;
            color: #2c3e50;
        }

        .subtitle {
            font-size: 10pt;
            color: #7f8c8d;
            margin-top: 5px;
        }

        .info {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background-color: #2c3e50;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }

        td {
            padding: 6px 8px;
            border-bottom: 1px solid #dee2e6;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8pt;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8pt;
        }

        .badge-primary {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">REPORTE DE AFILIADOS</div>
        <div class="subtitle">Sistema SISPSA</div>
        <div class="subtitle">Generado: <?= $generatedAt ?></div>
    </div>

    <div class="info">
        <strong>Resumen:</strong> Total de afiliados: <?= number_format($total) ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre Completo</th>
                <th>Cédula</th>
                <th>Clínica</th>
                <th>Tipo</th>
                <th>Plan</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php $counter = 1;
            foreach ($affiliates as $affiliate): ?>
                <tr>
                    <td><?= $counter++ ?></td>
                    <td><?= Html::encode($affiliate->nombres . ' ' . $affiliate->apellidos) ?></td>
                    <td><?= Html::encode($affiliate->tipo_cedula . '-' . $affiliate->cedula) ?></td>
                    <td><?= Html::encode($affiliate->clinica ? $affiliate->clinica->nombre : '') ?></td>
                    <td><?= Html::encode($affiliate->userDatosType ? $affiliate->userDatosType->nombre : '') ?></td>
                    <td><?= Html::encode($affiliate->plan ? $affiliate->plan->nombre : '') ?></td>
                    <td><?= Html::encode($affiliate->estatus) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        Documento generado automáticamente por el Sistema SISPSA<br>
        Total de registros: <?= number_format($total) ?>
    </div>
</body>

</html>