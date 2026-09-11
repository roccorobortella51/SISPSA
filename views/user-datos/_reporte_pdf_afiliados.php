<?php
// views/user-datos/_reporte_pdf_afiliados.php

use yii\helpers\Html;

/* @var $affiliates array */
/* @var $filtros array */
/* @var $total integer */
/* @var $numeroClinicas integer */

$affiliates = isset($affiliates) && is_array($affiliates) ? $affiliates : [];
$total = isset($total) ? $total : count($affiliates);
$numeroClinicas = isset($numeroClinicas) ? $numeroClinicas : count(array_unique(array_filter(array_map(function ($affiliate) {
    return isset($affiliate->clinica) && $affiliate->clinica ? $affiliate->clinica->nombre : null;
}, $affiliates))));
$logo = isset($logo) ? $logo : '';
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Reporte de Afiliados - SISPSA</title>
</head>

<body style="font-family: Arial, sans-serif; font-size: 10pt; margin: 0; padding: 15px; color: #333;">

    <!-- Logo Section -->
    <table style="width: 100%;">
        <tr>
            <td style="vertical-align: top; width: 100%; text-align: center;">
                <?php if (!empty($logo) && file_exists($logo)): ?>
                    <img src="<?= $logo ?>" alt="Logo SISPSA" style="width: 100px; display: block; margin: 0 auto;">
                <?php endif; ?>
                <div style="font-size: 10px; margin-top: 5px;">
                    <div>Inscrita en la Superintendencia de la Actividad Aseguradora bajo el No. MP000013</div>
                    <div>R.I.F.: J-50654922</div>
                </div>
            </td>
        </tr>
    </table>
    <br>

    <!-- Header -->
    <div style="text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #2c3e50;">
        <div style="font-size: 16pt; font-weight: bold; margin-bottom: 5px; color: #2c3e50;">REPORTE DE AFILIADOS</div>
        <div style="font-size: 12pt; margin-bottom: 5px; color: #7f8c8d;">Listado de Afiliados por Clínica</div>
        <div style="font-size: 10pt; color: #95a5a6; margin-bottom: 10px;">Generado: <?= date('d/m/Y H:i:s') ?></div>
    </div>

    <!-- Filters -->
    <?php if (!empty($filtros)): ?>
        <div style="margin-bottom: 15px; padding: 10px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; font-size: 9pt;">
            <strong style="color: #2c3e50;">Filtros aplicados:</strong><br>
            <?= implode(' | ', $filtros) ?>
        </div>
    <?php endif; ?>

    <!-- Table -->
    <table style="width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 8pt;">
        <thead>
            <tr>
                <th style="width: 3%; text-align: center; background-color: #2c3e50; color: white; border: 1px solid #ddd; padding: 6px; font-weight: bold;">#</th>
                <th style="width: 16%; background-color: #2c3e50; color: white; border: 1px solid #ddd; padding: 6px; font-weight: bold;">Nombre Completo</th>
                <th style="width: 10%; text-align: center; background-color: #2c3e50; color: white; border: 1px solid #ddd; padding: 6px; font-weight: bold;">Cédula</th>
                <!-- ============================================ -->
                <!-- NEW COLUMN: Fecha de Nacimiento              -->
                <!-- ============================================ -->
                <th style="width: 10%; text-align: center; background-color: #2c3e50; color: white; border: 1px solid #ddd; padding: 6px; font-weight: bold;">F. Nacimiento</th>
                <th style="width: 10%; text-align: center; background-color: #2c3e50; color: white; border: 1px solid #ddd; padding: 6px; font-weight: bold;">F. Afiliación</th>
                <th style="width: 11%; text-align: center; background-color: #2c3e50; color: white; border: 1px solid #ddd; padding: 6px; font-weight: bold;">Plan</th>
                <th style="width: 7%; text-align: center; background-color: #2c3e50; color: white; border: 1px solid #ddd; padding: 6px; font-weight: bold;">Cuotas</th>
                <th style="width: 33%; background-color: #2c3e50; color: white; border: 1px solid #ddd; padding: 6px; font-weight: bold;">Clínica</th>
            </tr>
        </thead>
        <tbody>
            <?php $counter = 1; ?>
            <?php foreach ($affiliates as $affiliate): ?>
                <?php
                $contrato = $affiliate->getContratos()
                    ->where(['!=', 'estatus', 'Anulado'])
                    ->orderBy(['fecha_ini' => SORT_DESC])
                    ->one();

                $fechaAfiliacion = '';
                if ($contrato && !empty($contrato->fecha_ini)) {
                    $fechaAfiliacion = date('d/m/Y', strtotime($contrato->fecha_ini));
                } elseif ($contrato && !empty($contrato->created_at)) {
                    $fechaAfiliacion = date('d/m/Y', strtotime($contrato->created_at));
                }

                // ============================================
                // NEW: Format birth date for PDF
                // ============================================
                $fechaNacimiento = '';
                if (!empty($affiliate->fechanac)) {
                    $fechaNacimiento = date('d/m/Y', strtotime($affiliate->fechanac));
                }

                $planNombre = $affiliate->plan ? $affiliate->plan->nombre : '';

                $totalPaid = \app\models\Cuotas::find()
                    ->innerJoin('contratos', 'contratos.id = cuotas.contrato_id')
                    ->where(['contratos.user_id' => $affiliate->id])
                    ->andWhere(['!=', 'contratos.estatus', 'Anulado'])
                    ->andWhere(['cuotas.estatus' => 'pagada'])
                    ->count();

                $rowColor = ($counter % 2 == 0) ? '#f9f9f9' : '#ffffff';
                ?>
                <tr style="background-color: <?= $rowColor ?>;">
                    <td style="text-align: center; border: 1px solid #ddd; padding: 5px;"><?= $counter++ ?></td>
                    <td style="border: 1px solid #ddd; padding: 5px;"><?= Html::encode($affiliate->nombres . ' ' . $affiliate->apellidos) ?></td>
                    <td style="text-align: center; border: 1px solid #ddd; padding: 5px;"><?= Html::encode($affiliate->tipo_cedula . '-' . $affiliate->cedula) ?></td>
                    <!-- ============================================ -->
                    <!-- NEW: Birth date cell                          -->
                    <!-- ============================================ -->
                    <td style="text-align: center; border: 1px solid #ddd; padding: 5px;">
                        <?php if (!empty($fechaNacimiento)): ?>
                            <?= Html::encode($fechaNacimiento) ?>
                        <?php else: ?>
                            <span style="color: #6c757d; font-style: italic;">N/D</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center; border: 1px solid #ddd; padding: 5px;">
                        <?php if (!empty($fechaAfiliacion)): ?>
                            <?= Html::encode($fechaAfiliacion) ?>
                        <?php else: ?>
                            <span style="color: #6c757d; font-style: italic;">N/D</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center; border: 1px solid #ddd; padding: 5px;"><?= Html::encode($planNombre) ?></td>
                    <td style="text-align: center; border: 1px solid #ddd; padding: 5px; font-weight: bold;"><?= number_format($totalPaid) ?></td>
                    <td style="border: 1px solid #ddd; padding: 5px;"><?= Html::encode($affiliate->clinica ? $affiliate->clinica->nombre : '') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Summary -->
    <div style="margin-top: 20px; padding: 10px; background-color: #e8f4fd; border: 1px solid #b6d4fe; border-radius: 4px; font-size: 10pt;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
            <div style="flex: 1;">
                <strong style="color: #0d6efd;">Total de Afiliados:</strong> <?= number_format($total) ?>
            </div>
            <div style="flex: 1;">
                <strong style="color: #0d6efd;">Número de Clínicas:</strong> <?= number_format($numeroClinicas) ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div style="margin-top: 20px; text-align: center; font-size: 9pt; color: #6c757d; border-top: 1px solid #dee2e6; padding-top: 10px;">
        Documento confidencial - Sistema SISPSA - Página {PAGENO} de {nbpg}
    </div>
</body>

</html>