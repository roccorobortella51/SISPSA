<?php
// app/views/reportes/_pagos-resumen-clinicas.php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var array $summaryPorClinica */
/** @var array $summary */
/** @var string $startDate */
/** @var string $endDate */
/** @var string $title */

// Ensure summary has the expected keys with default values
$summary = array_merge([
    'total_monto' => 0,
    'total_count' => 0,
    'conciliados' => 0,
    'pendientes' => 0
], $summary ?? []);

if (!empty($summaryPorClinica)):
?>
<div class="col-12 mb-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="fas fa-hospital me-2"></i> Resumen por Clínica: 
                <small class="float-end fs-6 text-white"><?= Html::encode($startDate) ?> al <?= Html::encode($endDate) ?></small>
            </h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light" style="background-color: #007bff !important;">
                        <tr>
                            <th width="40%" class="text-center" style="color: white !important;">Clínica</th>
                            <th width="15%" class="text-center" style="color: white !important;">RIF</th>
                            <th width="15%" class="text-center" style="color: white !important;">Total Pagos</th>
                            <th width="15%" class="text-center" style="color: white !important;">Estado Pago</th>
                            <th width="15%" class="text-center" style="color: white !important;">Total (Bs.)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $granTotal = 0;
                        $granTotalPagos = 0;
                        foreach ($summaryPorClinica as $resumen):
                            // Ensure all keys exist with defaults
                            $resumen = array_merge([
                                'clinica_nombre' => 'Desconocida',
                                'clinica_rif' => 'N/A',
                                'total_monto' => 0,
                                'total_pagos' => 0,
                                'conciliados' => 0,
                                'pendientes' => 0
                            ], $resumen);
                            
                            $granTotal += (float)$resumen['total_monto'];
                            $granTotalPagos += (int)$resumen['total_pagos'];
                        ?>
                        <tr class="hospital-summary-card">
                            <td>
                                <strong><?= Html::encode($resumen['clinica_nombre']) ?></strong>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary"><?= Html::encode($resumen['clinica_rif']) ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info fs-6"><?= number_format($resumen['total_pagos']) ?></span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center">
                                    <span class="badge bg-success me-1"><?= $resumen['conciliados'] ?></span>
                                    <span class="badge bg-warning"><?= $resumen['pendientes'] ?></span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold text-success"><?= Yii::$app->formatter->asCurrency($resumen['total_monto'], 'VES') ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="summary-totals">
                        <tr>
                            <th colspan="3" class="text-end">
                                <h5 class="mb-0 text-white">TOTAL GENERAL:</h5>
                            </th>
                            <th class="text-center">
                                <h5 class="mb-0 text-white"><?= number_format($granTotalPagos) ?> pagos</h5>
                            </th>
                            <th class="text-center">
                                <h4 class="mb-0 text-white"><?= Yii::$app->formatter->asCurrency($granTotal, 'VES') ?></h4>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light">
            <div class="row">
                <div class="col-md-6">
                    <span class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Mostrando <?= count($summaryPorClinica) ?> clínica(s)
                    </span>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-sm btn-outline-primary" id="btn-exportar-resumen">
                        <i class="fas fa-file-excel me-1"></i> Exportar Resumen
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>