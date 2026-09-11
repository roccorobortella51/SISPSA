<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Cuotas;
use app\models\Contratos;
use app\models\PlanServicios;

/**
 * @var yii\web\View $this
 * @var Contratos $model
 * @var app\models\Pagos[] $pagosDelContrato
 */

$this->title = 'CONTRATO #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Contratos', 'url' => ['index', 'user_id' => $model->user_id]];
$this->params['breadcrumbs'][] = $this->title;

// Calculate payment statistics
$pagosQuery = $model->getPagosDelContrato();
if (is_object($pagosQuery)) {
    $pagosDelContrato = $pagosQuery->orderBy(['fecha_pago' => SORT_DESC])->all();
} else {
    $pagosDelContrato = [];
}
$totalPagos = count($pagosDelContrato);
$totalPagado = 0;
$lastPaymentDate = null;

foreach ($pagosDelContrato as $pago) {
    $totalPagado += floatval($pago->monto_pagado);
    if (!$lastPaymentDate || strtotime($pago->fecha_pago) > strtotime($lastPaymentDate)) {
        $lastPaymentDate = $pago->fecha_pago;
    }
}

// Calculate cuota statistics
$cuotasDelContrato = Cuotas::find()->where(['contrato_id' => $model->id])->all();
$totalCuotas = count($cuotasDelContrato);
$pagadas = 0;
$pendientes = 0;
$vencidas = 0;
$enGracias = 0;
$anuladas = 0;
$montoTotal = 0;
$montoPagado = 0;

foreach ($cuotasDelContrato as $c) {
    $montoTotal += $c->monto ?: 0;
    switch ($c->estatus) {
        case 'pagada':
            $pagadas++;
            $montoPagado += $c->monto ?: 0;
            break;
        case 'pendiente':
            $pendientes++;
            break;
        case 'vencida':
            $vencidas++;
            break;
        case 'en_gracias':
            $enGracias++;
            break;
        case 'anulada':
            $anuladas++;
            break;
    }
}
$saldoPendiente = $montoTotal - $montoPagado;
$progressPercent = $montoTotal > 0 ? min(100, round(($montoPagado / $montoTotal) * 100)) : 0;

// Calculate remaining days
$remainingDays = null;
$remainingStatus = '';
$remainingColor = '';
if ($model->fecha_ven) {
    $today = new DateTime();
    $endDate = new DateTime($model->fecha_ven);
    if ($today < $endDate) {
        $remainingDays = $today->diff($endDate)->days;
        $remainingStatus = 'días restantes';
        $remainingColor = '#28a745';
    } elseif ($today > $endDate) {
        $remainingDays = $endDate->diff($today)->days;
        $remainingStatus = 'días vencido';
        $remainingColor = '#dc3545';
    } else {
        $remainingDays = 0;
        $remainingStatus = 'finaliza hoy';
        $remainingColor = '#ffc107';
    }
}

// Status colors
$statusColors = [
    'Activo' => 'success',
    'Anulado' => 'danger',
    'Vencido' => 'warning',
    'Registrado' => 'info',
    'Pendiente' => 'primary',
    'suspendido' => 'secondary',
];
$statusColor = $statusColors[$model->estatus] ?? 'secondary';

// ============================================================
// GET MEDICAL SERVICES FROM THE PLAN USING PlanServicios
// ============================================================
$serviciosMedicos = [];
if ($model->plan) {
    $serviciosMedicos = PlanServicios::getServicesForPlan(
        $model->plan_id,
        $model->clinica_id
    );
}
?>

<div class="container-fluid py-4">
    <!-- Header Card -->
    <div class="card shadow-lg mb-4 rounded-3 border-0" style="overflow: hidden;">
        <div class="card-header text-white py-2 px-4" style="background: linear-gradient(135deg, #1a237e 0%, #0d47a1 50%, #1565c0 100%); border: none;">
            <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 8px 16px;">
                <!-- Left: Icon + Contract # -->
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                        <i class="fas fa-file-contract" style="color: #1a237e; font-size: 18px;"></i>
                    </div>
                    <h3 class="card-title mb-0 font-weight-bold" style="font-size: 18px; letter-spacing: 0.3px; line-height: 1.2; color: #ffffff; white-space: nowrap;">
                        CONTRATO #<?= Html::encode($model->id) ?>
                    </h3>
                </div>

                <!-- Center: Affiliate Name -->
                <?php if ($model->user): ?>
                    <div class="d-flex align-items-center" style="flex: 1; min-width: 0;">
                        <span style="font-size: 14px; padding: 4px 16px; border-radius: 20px; display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.92); color: #1a237e; font-weight: 600; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid rgba(255,255,255,0.2); max-width: 100%;">
                            <i class="fas fa-user" style="font-size: 13px; color: #1a237e; opacity: 0.6; flex-shrink: 0;"></i>
                            <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= Html::encode($model->user->nombres . ' ' . $model->user->apellidos) ?></span>
                            <span style="font-weight: 400; font-size: 10px; color: #6c757d; background: #e9ecef; padding: 1px 8px; border-radius: 12px; flex-shrink: 0;">
                                <?= $model->user->tipo_cedula ?>-<?= $model->user->cedula ?>
                            </span>
                        </span>
                    </div>
                <?php endif; ?>

                <!-- Right: Action Buttons -->
                <div class="d-flex align-items-center flex-shrink-0" style="gap: 8px;">
                    <?= Html::a(
    '<i class="fas fa-file-pdf me-1"></i> Imprimir Contrato',
    ['user-datos/generar-contratov', 'id' => $model->user_id],
    [
        'class' => 'btn btn-danger btn-sm rounded-pill px-3 shadow-sm',
'style' => 'color: #ffffff; font-weight: 500; font-size: 13px; background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%); border: none; transition: all 0.2s ease;',
        'target' => '_blank',
        'data-pjax' => '0',
        'data-toggle' => 'tooltip',
        'title' => 'Imprimir el contrato en formato PDF'
    ]
) ?>
                    <?= Html::a(
                        '<i class="fas fa-arrow-left me-1"></i> Volver',
                        ['index', 'user_id' => $model->user_id],
                        [
                            'class' => 'btn btn-light btn-sm rounded-pill px-3 shadow-sm',
                            'style' => 'color: #1a237e; font-weight: 500; font-size: 13px;',
                            'data-toggle' => 'tooltip',
                            'title' => 'Volver al listado de contratos'
                        ]
                    ) ?>
                </div>
            </div>
        </div>

        <!-- Quick Stats Row -->
        <div class="card-body p-0">
            <div class="row g-0">
                <div class="col-md-3 border-end">
                    <div class="p-3 text-center" style="min-height: 75px; display: flex; flex-direction: column; justify-content: center;">
                        <div class="text-muted small text-uppercase" style="font-size: 9px; letter-spacing: 0.5px; margin-bottom: 4px;">Estatus</div>
                        <div>
                            <?php
                            $badgeClasses = [
                                'Registrado' => 'badge-info',
                                'Activo' => 'badge-success',
                                'Anulado' => 'badge-danger',
                                'Vencido' => 'badge-warning',
                                'Pendiente' => 'badge-primary',
                                'suspendido' => 'badge-secondary',
                            ];
                            $class = $badgeClasses[$model->estatus] ?? 'badge-light';
                            $statusIcons = [
                                'Registrado' => '📋',
                                'Activo' => '✅',
                                'Anulado' => '❌',
                                'Vencido' => '⏰',
                                'Pendiente' => '⏳',
                                'suspendido' => '⏸️',
                            ];
                            $icon = $statusIcons[$model->estatus] ?? '';
                            ?>
                            <span class="badge <?= $class ?> px-3 py-2" style="font-size: 13px; font-weight: 600;">
                                <?= $icon ?> <?= $model->estatus ?: 'Registrado' ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 border-end">
                    <div class="p-3 text-center" style="min-height: 75px; display: flex; flex-direction: column; justify-content: center;">
                        <div class="text-muted small text-uppercase" style="font-size: 9px; letter-spacing: 0.5px; margin-bottom: 4px;">Plan</div>
                        <div class="fw-bold" style="font-size: 15px; color: #1a237e;">
                            <?= $model->plan ? $model->plan->nombre : 'N/A' ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 border-end">
                    <div class="p-3 text-center" style="min-height: 75px; display: flex; flex-direction: column; justify-content: center;">
                        <div class="text-muted small text-uppercase" style="font-size: 9px; letter-spacing: 0.5px; margin-bottom: 4px;">Clínica</div>
                        <div class="fw-bold" style="font-size: 15px; color: #1a237e;">
                            <?= $model->clinica ? $model->clinica->nombre : 'N/A' ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 text-center" style="min-height: 75px; display: flex; flex-direction: column; justify-content: center;">
                        <div class="text-muted small text-uppercase" style="font-size: 9px; letter-spacing: 0.5px; margin-bottom: 4px;">N° Contrato</div>
                        <div class="fw-bold" style="font-size: 15px; color: #1a237e;">
                            <?= $model->nrocontrato ?: 'N/A' ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contract Summary Cards -->
    <div class="row mb-4">
        <!-- Card 1: Monto Total -->
        <div class="col-md-3 mb-3">
            <div class="card border-0 h-100 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="card-body text-center py-4">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 50px; height: 50px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                        <i class="fas fa-dollar-sign" style="color: #667eea; font-size: 22px;"></i>
                    </div>
                    <h6 class="text-uppercase opacity-75" style="font-size: 11px; letter-spacing: 0.5px;">Monto Total</h6>
                    <h4 class="fw-bold" style="font-size: 24px;"><?= Yii::$app->formatter->asCurrency($model->monto, 'USD') ?></h4>
                    <hr class="bg-white opacity-25">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="opacity-75">Cobertura</small>
                        <strong><?= $model->plan ? Yii::$app->formatter->asCurrency($model->plan->cobertura, 'USD') : 'N/A' ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Pagado -->
        <div class="col-md-3 mb-3">
            <div class="card border-0 h-100 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white;">
                <div class="card-body text-center py-4">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 50px; height: 50px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                        <i class="fas fa-check-circle" style="color: #28a745; font-size: 22px;"></i>
                    </div>
                    <h6 class="text-uppercase opacity-75" style="font-size: 11px; letter-spacing: 0.5px;">Total Pagado</h6>
                    <h4 class="fw-bold" style="font-size: 24px;"><?= Yii::$app->formatter->asCurrency($montoPagado, 'USD') ?></h4>
                    <hr class="bg-white opacity-25">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="opacity-75">Progreso</small>
                        <strong><?= $progressPercent ?>%</strong>
                    </div>
                    <div class="progress mt-1" style="height: 4px; background: rgba(255,255,255,0.25); border-radius: 2px;">
                        <div class="progress-bar" style="width: <?= $progressPercent ?>%; height: 100%; background: white; border-radius: 2px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Tiempo Restante (REMOVED "Tiempo Restante" title and "% del período completado") -->
        <div class="col-md-3 mb-3">
            <div class="card border-0 h-100 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, <?= $remainingColor ?> 0%, <?= $remainingColor ?>cc 100%); color: white;">
                <div class="card-body text-center py-4">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 50px; height: 50px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                        <i class="fas fa-calendar-alt" style="color: <?= $remainingColor ?>; font-size: 22px;"></i>
                    </div>
                    <h6 class="text-uppercase opacity-75" style="font-size: 11px; letter-spacing: 0.5px;">Período del Contrato</h6>

                    <!-- Start and End Dates -->
                    <div class="d-flex justify-content-center align-items-center gap-2 mt-2">
                        <div>
                            <small class="opacity-75" style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.3px;">Inicio</small>
                            <div class="fw-bold" style="font-size: 14px;"><?= Yii::$app->formatter->asDate($model->fecha_ini, 'php:d/m/Y') ?></div>
                        </div>
                        <span style="opacity: 0.4; font-size: 20px; font-weight: 300;">→</span>
                        <div>
                            <small class="opacity-75" style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.3px;">Vencimiento</small>
                            <div class="fw-bold" style="font-size: 14px;"><?= $model->fecha_ven ? Yii::$app->formatter->asDate($model->fecha_ven, 'php:d/m/Y') : 'Indefinido' ?></div>
                        </div>
                    </div>

                    <hr class="bg-white opacity-25" style="margin: 10px 0;">

                    <!-- Remaining Days -->
                    <div>
                        <?php if ($remainingDays !== null): ?>
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <span class="fw-bold" style="font-size: 28px;"><?= $remainingDays ?></span>
                                <span style="font-size: 12px; opacity: 0.8;"><?= $remainingStatus ?></span>
                            </div>
                        <?php else: ?>
                            <div class="fw-bold" style="font-size: 16px;">Contrato Indefinido</div>
                        <?php endif; ?>
                    </div>

                    <!-- Progress Bar (without the % text) -->
                    <div class="mt-2">
                        <div class="progress" style="height: 4px; background: rgba(255,255,255,0.25); border-radius: 2px;">
                            <?php
                            $totalDays = 365;
                            if ($model->fecha_ven && $model->fecha_ini) {
                                $start = new DateTime($model->fecha_ini);
                                $end = new DateTime($model->fecha_ven);
                                $totalDays = $start->diff($end)->days;
                            }
                            $daysPassed = $totalDays - $remainingDays;
                            $progressPercentDays = $totalDays > 0 && $remainingDays !== null ? min(100, round(($daysPassed / $totalDays) * 100)) : 0;
                            ?>
                            <div class="progress-bar" style="width: <?= $progressPercentDays ?>%; height: 100%; background: rgba(255,255,255,0.8); border-radius: 2px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Resumen de Pagos -->
        <div class="col-md-3 mb-3">
            <div class="card border-0 h-100 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                <div class="card-body text-center py-4">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 50px; height: 50px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                        <i class="fas fa-credit-card" style="color: #f5576c; font-size: 22px;"></i>
                    </div>
                    <h6 class="text-uppercase opacity-75" style="font-size: 11px; letter-spacing: 0.5px;">Resumen de Pagos</h6>
                    <div class="row g-1">
                        <div class="col-6">
                            <div class="bg-white bg-opacity-10 rounded p-1">
                                <small class="d-block opacity-75" style="font-size: 9px;">Pagadas</small>
                                <strong style="font-size: 18px;"><?= $pagadas ?></strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-white bg-opacity-10 rounded p-1">
                                <small class="d-block opacity-75" style="font-size: 9px;">Pendientes</small>
                                <strong style="font-size: 18px;"><?= $pendientes ?></strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-white bg-opacity-10 rounded p-1">
                                <small class="d-block opacity-75" style="font-size: 9px;">Vencidas</small>
                                <strong style="font-size: 18px; color: #ffc107;"><?= $vencidas ?></strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-white bg-opacity-10 rounded p-1">
                                <small class="d-block opacity-75" style="font-size: 9px;">En Gracias</small>
                                <strong style="font-size: 18px;"><?= $enGracias ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- DESCRIPCIÓN DE SERVICIOS SECTION -->
    <!-- ============================================================ -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-header bg-transparent border-bottom py-3" style="border-color: #e8edf2 !important; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold" style="color: #ffffff;">
                    <i class="fas fa-stethoscope me-4" style="color: #fafbfc;"></i>Descripción de Servicios
                    <span class="badge ms-2" style="background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%); padding: 4px 12px; border-radius: 12px; font-size: 11px; color: white;">
                        <?= count($serviciosMedicos) ?> servicios
                    </span>
                </h5>
                <?php if ($model->plan): ?>
                    <span style="font-size: 12px; color: #ffffff; background: linear-gradient(135deg, #1a237e 0%, #0d47a1 50%, #1565c0 100%); padding: 4px 14px; border-radius: 20px;">
                        <i class="fas fa-tag me-1" style="opacity: 0.8;"></i> Plan: <strong style="color: #ffffff;"><?= Html::encode($model->plan->nombre) ?></strong>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($serviciosMedicos)): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 13px;">
                        <thead style="background: linear-gradient(135deg, #1a237e 0%, #0d47a1 50%, #1565c0 100%); border-bottom: 2px solid #0078d4;">
                            <tr>
                                <th class="align-middle" style="padding: 14px 20px; width: 35%; font-weight: 700; color: #ffffff !important; font-size: 13px; text-transform: uppercase; letter-spacing: 0.3px;">
                                    <i class="fas fa-stethoscope me-2" style="color: #ffffff !important;"></i> DESCRIPCIÓN DE SERVICIOS
                                </th>
                                <th class="text-center align-middle" style="padding: 14px 20px; width: 20%; font-weight: 700; color: #ffffff !important; font-size: 13px; text-transform: uppercase; letter-spacing: 0.3px;">
                                    <i class="fas fa-clock me-2" style="color: #ffffff !important;"></i> PLAZO DE ESPERA (P/E)
                                </th>
                                <th class="align-middle" style="padding: 14px 20px; width: 45%; font-weight: 700; color: #ffffff !important; font-size: 13px; text-transform: uppercase; letter-spacing: 0.3px;">
                                    <i class="fas fa-info-circle me-2" style="color: #ffffff !important;"></i> DESCRIPCIÓN
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($serviciosMedicos as $index => $servicio): ?>
                                <?php
                                // Alternating colors matching Historial de Pagos
                                $rowBgColor = ($index % 2 == 0) ? '#b9cbde' : '#ffffff';
                                ?>
                                <tr style="background-color: <?= $rowBgColor ?>; border-bottom: 1px solid #e8edf2; transition: background 0.15s ease;">
                                    <td style="padding: 12px 20px; font-weight: 500; color: #1a237e; background-color: transparent;">
                                        <i class="fas fa-chevron-right me-2" style="color: #0078d4; font-size: 10px;"></i>
                                        <?= Html::encode($servicio->servicio_nombre) ?>
                                    </td>
                                    <td class="text-center" style="padding: 12px 20px; background-color: transparent;">
                                        <?php if ($servicio->plazo_espera): ?>
                                            <span class="badge" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: #212529; font-size: 12px; padding: 5px 16px; border-radius: 20px; font-weight: 600;">
                                                <i class="fas fa-hourglass-half me-1" style="font-size: 10px;"></i>
                                                <?= Html::encode($servicio->plazo_espera) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted" style="font-size: 12px;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 12px 20px; color: #495057; font-size: 13px; line-height: 1.5; background-color: transparent;">
                                        <?= Html::encode($servicio->descripcion ?: 'Sin descripción') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot style="background: #f8f9fa; border-top: 2px solid #0078d4;">
                            <tr>
                                <td colspan="3" class="text-muted" style="padding: 10px 20px; font-size: 12px;">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Servicios médicos incluidos en el plan <strong><?= $model->plan ? Html::encode($model->plan->nombre) : '' ?></strong>
                                    <?php if ($model->clinica): ?>
                                        · Clínica: <strong><?= Html::encode($model->clinica->nombre) ?></strong>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                        <i class="fas fa-ambulance fa-2x" style="color: #0078d4;"></i>
                    </div>
                    <h6 class="text-dark fw-bold" style="font-size: 16px;">No hay servicios médicos asociados</h6>
                    <p class="text-muted" style="font-size: 13px; max-width: 400px; margin: 0 auto;">
                        Este plan no tiene servicios médicos configurados.
                        <?php if ($model->plan): ?>
                            <br><small>Plan: <strong><?= Html::encode($model->plan->nombre) ?></strong></small>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Resumen Financiero - Simple Summary Row -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #f8f9fa;">
                <div class="card-body py-3">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="text-muted small text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">
                                <i class="fas fa-money-bill me-1" style="color: #1a237e;"></i> Monto Total
                            </div>
                            <div class="fw-bold" style="font-size: 18px; color: #1a237e;">
                                <?= Yii::$app->formatter->asCurrency($model->monto, 'USD') ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">
                                <i class="fas fa-check-circle me-1" style="color: #28a745;"></i> Total Pagado
                            </div>
                            <div class="fw-bold" style="font-size: 18px; color: #28a745;">
                                <?= Yii::$app->formatter->asCurrency($montoPagado, 'USD') ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">
                                <i class="fas fa-balance-scale me-1" style="color: <?= $saldoPendiente > 0 ? '#ffc107' : '#28a745' ?>;"></i> Saldo Pendiente
                            </div>
                            <div class="fw-bold" style="font-size: 18px; color: <?= $saldoPendiente > 0 ? '#ffc107' : '#28a745' ?>;">
                                <?= Yii::$app->formatter->asCurrency($saldoPendiente, 'USD') ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">
                                <i class="fas fa-receipt me-1" style="color: #0078d4;"></i> Total Cuotas
                            </div>
                            <div class="fw-bold" style="font-size: 18px; color: #1a237e;">
                                <?= $totalCuotas ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen del Contrato - Key Information -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: linear-gradient(135deg, #1a237e 0%, #0d47a1 50%, #1565c0 100%);">
                <div class="card-header bg-transparent border-bottom py-3" style="border-color: rgba(255,255,255,0.15) !important;">
                    <h5 class="mb-0 fw-bold" style="color: #ffffff; display: flex; align-items: center; gap: 14px;">
                        <i class="fas fa-info-circle" style="color: #ffffff; font-size: 18px;"></i>
                        <span style="color: #ffffff;">Resumen del Contrato</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-5 text-white-50" style="font-size: 13px; opacity: 0.8;">
                            <i class="fas fa-hashtag me-1" style="font-size: 11px; color: rgba(255,255,255,0.6);"></i> ID Contrato
                        </div>
                        <div class="col-7 fw-bold" style="font-size: 14px; color: #ffffff;">#<?= Html::encode($model->id) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-5 text-white-50" style="font-size: 13px; opacity: 0.8;">
                            <i class="fas fa-tag me-1" style="font-size: 11px; color: rgba(255,255,255,0.6);"></i> Plan
                        </div>
                        <div class="col-7 fw-bold" style="font-size: 14px; color: #ffffff;"><?= $model->plan ? $model->plan->nombre : 'N/A' ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-5 text-white-50" style="font-size: 13px; opacity: 0.8;">
                            <i class="fas fa-hospital me-1" style="font-size: 11px; color: rgba(255,255,255,0.6);"></i> Clínica
                        </div>
                        <div class="col-7 fw-bold" style="font-size: 14px; color: #ffffff;"><?= $model->clinica ? $model->clinica->nombre : 'N/A' ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-5 text-white-50" style="font-size: 13px; opacity: 0.8;">
                            <i class="fas fa-calendar-alt me-1" style="font-size: 11px; color: rgba(255,255,255,0.6);"></i> Fecha Inicio
                        </div>
                        <div class="col-7 fw-bold" style="font-size: 14px; color: #ffffff;">
                            <?= Yii::$app->formatter->asDate($model->fecha_ini, 'php:d/m/Y') ?>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-white-50" style="font-size: 13px; opacity: 0.8;">
                            <i class="fas fa-calendar-check me-1" style="font-size: 11px; color: rgba(255,255,255,0.6);"></i> Fecha Vencimiento
                        </div>
                        <div class="col-7 fw-bold" style="font-size: 14px; color: #ffffff;">
                            <?= $model->fecha_ven ? Yii::$app->formatter->asDate($model->fecha_ven, 'php:d/m/Y') : 'Indefinido' ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: linear-gradient(135deg, #1a237e 0%, #0d47a1 50%, #1565c0 100%);">
                <div class="card-header bg-transparent border-bottom py-3" style="border-color: rgba(255,255,255,0.15) !important;">
                    <h5 class="mb-0 fw-bold" style="color: #ffffff; display: flex; align-items: center; gap: 14px;">
                        <i class="fas fa-user" style="color: #ffffff; font-size: 18px;"></i>
                        <span style="color: #ffffff;">Información del Afiliado</span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($model->user): ?>
                        <div class="row mb-3">
                            <div class="col-5 text-white-50" style="font-size: 13px; opacity: 0.8;">
                                <i class="fas fa-user me-1" style="font-size: 11px; color: rgba(255,255,255,0.6);"></i> Nombre Completo
                            </div>
                            <div class="col-7 fw-bold" style="font-size: 14px; color: #ffffff;">
                                <?= Html::encode($model->user->nombres . ' ' . $model->user->apellidos) ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 text-white-50" style="font-size: 13px; opacity: 0.8;">
                                <i class="fas fa-id-card me-1" style="font-size: 11px; color: rgba(255,255,255,0.6);"></i> Cédula
                            </div>
                            <div class="col-7 fw-bold" style="font-size: 14px; color: #ffffff;">
                                <?= Html::encode($model->user->tipo_cedula . '-' . $model->user->cedula) ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 text-white-50" style="font-size: 13px; opacity: 0.8;">
                                <i class="fas fa-envelope me-1" style="font-size: 11px; color: rgba(255,255,255,0.6);"></i> Email
                            </div>
                            <div class="col-7 fw-bold" style="font-size: 14px; color: #ffffff;">
                                <?= Html::encode($model->user->email ?: 'N/A') ?>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 text-white-50" style="font-size: 13px; opacity: 0.8;">
                                <i class="fas fa-phone me-1" style="font-size: 11px; color: rgba(255,255,255,0.6);"></i> Teléfono
                            </div>
                            <div class="col-7 fw-bold" style="font-size: 14px; color: #ffffff;">
                                <?= Html::encode($model->user->telefono ?: 'N/A') ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-white-50 text-center py-3" style="opacity: 0.7;">No hay información del afiliado disponible</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Register tooltips
$this->registerJs("
    $(document).ready(function() {
        $('[data-toggle=\"tooltip\"]').tooltip({
            trigger: 'hover',
            placement: 'top',
            html: true,
            container: 'body',
            delay: { show: 300, hide: 100 }
        });
    });
");
?>