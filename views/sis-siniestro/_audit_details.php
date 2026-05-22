<?php
// views/sis-siniestro/_audit_details.php

use yii\helpers\Html;

/**
 * @var app\models\SisSiniestroAuditLog $auditLog
 * @var array $deletedData
 */

$esCita = isset($deletedData['es_cita']) ? $deletedData['es_cita'] : 0;
$termino = $esCita == 1 ? 'Cita' : 'Atención';
?>

<div class="audit-details">
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-light">
                <div class="card-header">
                    <strong><i class="fas fa-user mr-2"></i>Información del Usuario</strong>
                </div>
                <div class="card-body">
                    <p><strong>Nombre:</strong> <?= Html::encode($auditLog->user_name) ?></p>
                    <p><strong>Rol:</strong> <?= Html::encode($auditLog->user_role) ?></p>
                    <p><strong>Fecha:</strong> <?= Yii::$app->formatter->asDatetime($auditLog->created_at) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-light">
                <div class="card-header">
                    <strong><i class="fas fa-trash-alt mr-2"></i>Detalles de Eliminación</strong>
                </div>
                <div class="card-body">
                    <p><strong>Motivo:</strong><br><?= nl2br(Html::encode($auditLog->reason)) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <strong><i class="fas fa-calendar-alt mr-2"></i><?= $termino ?> Eliminada</strong>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th style="width: 30%;">Clínica</th>
                    <td><?= Html::encode($deletedData['clinica_nombre'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <th>Fecha</th>
                    <td><?= $deletedData['fecha'] ?? 'N/A' ?></td>
                </tr>
                <tr>
                    <th>Hora</th>
                    <td><?= $deletedData['hora'] ?? 'N/A' ?></td>
                </tr>
                <tr>
                    <th>Atendido</th>
                    <td><?= ($deletedData['atendido'] ?? 0) == 1 ? 'Sí' : 'No' ?></td>
                </tr>
                <tr>
                    <th>Costo Total</th>
                    <td class="text-danger font-weight-bold">$<?= number_format($deletedData['costo_total'] ?? 0, 2) ?></td>
                </tr>
                <?php if (!empty($deletedData['descripcion'])): ?>
                    <tr>
                        <th>Descripción</th>
                        <td><?= nl2br(Html::encode($deletedData['descripcion'])) ?></td>
                    </tr>
                <?php endif; ?>
            </table>

            <?php if (!empty($deletedData['baremos'])): ?>
                <h6 class="mt-3"><strong>Servicios Médicos Eliminados:</strong></h6>
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Servicio</th>
                            <th class="text-right">Precio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deletedData['baremos'] as $baremo): ?>
                            <tr>
                                <td><?= Html::encode($baremo['nombre_servicio'] ?? 'N/A') ?></td>
                                <td class="text-right">$<?= number_format($baremo['precio'] ?? 0, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="table-info">
                            <th class="text-right">Total:</th>
                            <th class="text-right">$<?= number_format($deletedData['costo_total'] ?? 0, 2) ?></th>
                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>