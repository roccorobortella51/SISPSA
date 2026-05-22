<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Pagos */

$this->title = 'Detalle del Pago #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Contratos', 'url' => ['/contratos/index', 'user_id' => $model->user_id]];
$this->params['breadcrumbs'][] = $this->title;

// Get the UserDatos model with explicit type hint for VS Code
/** @var \app\models\UserDatos|null $userDatos */
$userDatos = null;

// Get UserDatos via relation (defined in Pagos.php)
$userDatos = $model->userDatos;

// Fallback: manual lookup if relation didn't work
if (!$userDatos && !empty($model->user_id)) {
    $userDatos = \app\models\UserDatos::findOne(['user_id' => $model->user_id]);
}

// Second fallback: try by ID
if (!$userDatos && !empty($model->user_id)) {
    $userDatos = \app\models\UserDatos::findOne($model->user_id);
}

// Final fallback: create empty model to avoid null errors
if (!$userDatos) {
    $userDatos = new \app\models\UserDatos();
}

// Check if receipts already exist for this payment
$existingReceipts = \app\components\ReceiptGenerator::getReceiptsForPayment($model->id);
$hasInstallments = \app\models\Cuotas::find()->where(['id_pago' => $model->id])->exists();

// Calculate TASA DE CAMBIO if values exist
$tasaCalculada = null;
if ($model->monto_pagado > 0 && $model->monto_usd > 0) {
    $tasaCalculada = $model->monto_usd / $model->monto_pagado;
}

// Get affiliate full name and ID (the insured person)
$affiliateName = trim(($userDatos->nombres ?? '') . ' ' . ($userDatos->apellidos ?? ''));
$affiliateId = ($userDatos->tipo_cedula ?? '') . '-' . ($userDatos->cedula ?? '');

// If affiliate name is empty, show a fallback
if (empty($affiliateName)) {
    $affiliateName = 'Afiliado #' . ($model->user_id ?? 'Desconocido');
}
if (empty($affiliateId) || $affiliateId == '-') {
    $affiliateId = 'N/A';
}

// ============================================================
// PAYER/CONTRACTOR INFORMATION - Check if different payer exists
// ============================================================
$hasDifferentPayer = !empty($userDatos->tiene_contratante_diferente) && !empty($userDatos->nombre_contratante);

if ($hasDifferentPayer):
    // Use Contractor/Payer data from UserDatos (Datos del Contratante section)
    $payerName = trim(($userDatos->nombre_contratante ?? '') . ' ' . ($userDatos->apellido_contratante ?? ''));
    $payerId = ($userDatos->tipo_cedula_contratante ? $userDatos->tipo_cedula_contratante . '-' : '') . ($userDatos->cedula_contratante ?? '');
    $payerPhone = $userDatos->telefono_celular_contratante
        ?: ($userDatos->telefono_residencia_contratante
            ?: ($userDatos->telefono_oficina_contratante ?: 'N/A'));
    $payerEmail = $userDatos->email_contratante ?: 'N/A';
    $payerAddress = $userDatos->direccion_cobro_contratante
        ?: ($userDatos->direccion_residencia_contratante ?: 'N/A');
    $payerNote = '<small class="text-muted" style="font-size: 11px;"><i class="fas fa-info-circle"></i> Persona diferente al afiliado (Contratante)</small>';
else:
    // Use Affiliate data (same as insured person)
    $payerName = $affiliateName;
    $payerId = $affiliateId;
    $payerPhone = $userDatos->telefono ?? 'N/A';
    $payerEmail = $userDatos->email ?? 'N/A';
    $payerAddress = $userDatos->direccion_cobro ?? ($userDatos->direccion ?? 'N/A');
    $payerNote = '<small class="text-muted" style="font-size: 11px;"><i class="fas fa-info-circle"></i> Mismo afiliado</small>';
endif;

// Clean up any empty payer data
if (empty($payerName) || $payerName == ' ') {
    $payerName = $affiliateName;
}
if (empty($payerId) || $payerId == '-') {
    $payerId = $affiliateId;
}
?>

<div class="container-fluid px-4 py-4">
    <!-- Microsoft Fluent Design - Main Card -->
    <div class="card shadow-sm mb-4" style="border-radius: 8px; border: 1px solid #e1e1e1;">

        <!-- Header - Microsoft Blue with White Text -->
        <div class="card-header" style="background-color: #0078d4; border-bottom: none; padding: 20px 24px; border-radius: 8px 8px 0 0;">
            <!-- Row 1: Title and Action Buttons -->
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h1 style="color: white; font-size: 24px; font-weight: 600; margin: 0; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;">
                        <i class="fas fa-credit-card me-2"></i> Detalle del Pago #<?= Html::encode($model->id) ?>
                    </h1>
                </div>
                <div class="mt-2 mt-md-0">
                    <!-- RECEIPT BUTTONS - Generate or Print -->
                    <?php if (!empty($existingReceipts)): ?>
                        <?php if (count($existingReceipts) == 1): ?>
                            <?= Html::a(
                                '<i class="fas fa-receipt me-2"></i> Imprimir Recibo',
                                ['/receipts/print', 'id' => $existingReceipts[0]->id, 'auto_print' => 1],
                                [
                                    'class' => 'btn btn-light me-2',
                                    'style' => 'font-size: 13px; font-weight: 500; border-radius: 4px; padding: 8px 16px; background-color: white; color: #0078d4; border: none;',
                                    'target' => '_blank'
                                ]
                            ) ?>
                        <?php else: ?>
                            <div class="btn-group me-2">
                                <?= Html::a(
                                    '<i class="fas fa-receipt me-2"></i> Imprimir Recibos (' . count($existingReceipts) . ')',
                                    '#',
                                    [
                                        'class' => 'btn btn-light dropdown-toggle',
                                        'data-toggle' => 'dropdown',
                                        'style' => 'font-size: 13px; font-weight: 500; border-radius: 4px; padding: 8px 16px; background-color: white; color: #0078d4; border: none;'
                                    ]
                                ) ?>
                                <div class="dropdown-menu">
                                    <?= Html::a(
                                        '<i class="fas fa-print me-2"></i> Imprimir todos (' . count($existingReceipts) . ')',
                                        ['/receipts/print-all', 'paymentId' => $model->id, 'auto_print' => 1],
                                        ['class' => 'dropdown-item', 'target' => '_blank']
                                    ) ?>
                                    <div class="dropdown-divider"></div>
                                    <?php foreach ($existingReceipts as $receipt): ?>
                                        <?= Html::a(
                                            '<i class="fas fa-receipt me-2"></i> ' . $receipt->receipt_number,
                                            ['/receipts/print', 'id' => $receipt->id, 'auto_print' => 1],
                                            ['class' => 'dropdown-item', 'target' => '_blank']
                                        ) ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php elseif ($hasInstallments): ?>
                        <?= Html::a(
                            '<i class="fas fa-plus-circle me-2"></i> Generar Recibo(s)',
                            ['/pagos/generate-receipts', 'id' => $model->id],
                            [
                                'class' => 'btn btn-warning me-2',
                                'style' => 'font-size: 13px; font-weight: 500; border-radius: 4px; padding: 8px 16px;',
                                'data-confirm' => '¿Está seguro de generar los recibos para este pago? Se generará un recibo por cada cuota pagada.',
                            ]
                        ) ?>
                    <?php else: ?>
                        <?= Html::a(
                            '<i class="fas fa-receipt me-2"></i> Sin cuotas asociadas',
                            '#',
                            [
                                'class' => 'btn btn-secondary me-2 disabled',
                                'style' => 'font-size: 13px; font-weight: 500; border-radius: 4px; padding: 8px 16px; opacity: 0.5; cursor: not-allowed;',
                                'title' => 'Este pago no tiene cuotas asociadas'
                            ]
                        ) ?>
                    <?php endif; ?>

                    <?= Html::a(
                        '<i class="fas fa-arrow-left me-2"></i> Volver',
                        ['/contratos/index', 'user_id' => $model->user_id],
                        ['class' => 'btn btn-light', 'style' => 'font-size: 13px; font-weight: 500; border-radius: 4px; padding: 8px 16px; background-color: white; color: #0078d4; border: none;']
                    ) ?>
                </div>
            </div>

            <!-- Row 2: Separator -->
            <div style="border-top: 1px solid rgba(255,255,255,0.2); margin: 16px 0 12px 0;"></div>

            <!-- Row 3: Affiliate Information (Insured Person) -->
            <div class="d-flex align-items-center flex-wrap">
                <div style="background-color: rgba(255,255,255,0.15); border-radius: 6px; padding: 8px 16px;">
                    <i class="fas fa-user-circle me-2" style="color: white; font-size: 16px;"></i>
                    <span style="color: white; font-size: 14px; font-weight: 500;">
                        Afiliado (Asegurado): <?= Html::encode($affiliateName) ?>
                    </span>
                    <span class="mx-2" style="color: rgba(255,255,255,0.5);">|</span>
                    <i class="fas fa-id-card me-1" style="color: white; font-size: 14px;"></i>
                    <span style="color: white; font-size: 14px;">
                        <?= Html::encode($affiliateId) ?>
                    </span>
                </div>

                <?php if ($hasDifferentPayer): ?>
                    <div class="mt-2 mt-sm-0 ms-0 ms-sm-3" style="background-color: rgba(255,215,0,0.2); border-radius: 6px; padding: 8px 16px;">
                        <i class="fas fa-arrow-right me-2" style="color: #ffc107; font-size: 14px;"></i>
                        <span style="color: #ffc107; font-size: 13px;">
                            Pago realizado por contratante diferente
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Body -->
        <div class="card-body p-0">
            <!-- Status Banner with Registration Info -->
            <div class="px-4 py-3" style="background-color: <?= $model->estatus == 'Conciliado' ? '#dff6dd' : '#fff4e5'; ?>; border-bottom: 1px solid #e1e1e1;">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="d-flex align-items-center">
                        <i class="fas <?= $model->estatus == 'Conciliado' ? 'fa-check-circle' : 'fa-clock'; ?> me-3" style="font-size: 20px; color: <?= $model->estatus == 'Conciliado' ? '#107c10' : '#ff8c00'; ?>;"></i>
                        <div>
                            <span style="font-size: 14px; font-weight: 600; color: <?= $model->estatus == 'Conciliado' ? '#107c10' : '#ff8c00'; ?>;">
                                <?= $model->estatus == 'Conciliado' ? 'PAGO CONCILIADO' : 'PAGO PENDIENTE DE CONCILIACIÓN'; ?>
                            </span>
                        </div>
                    </div>
                    <div class="mt-2 mt-sm-0">
                        <span style="font-size: 13px; color: <?= $model->estatus == 'Conciliado' ? '#107c10' : '#ff8c00'; ?>; opacity: 0.8;">
                            <i class="fas fa-calendar-alt me-1"></i> Registrado: <?= Yii::$app->formatter->asDate($model->created_at, 'php:d/m/Y H:i:s') ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- SECTION 1: CUOTAS PAGADAS -->
            <?php
            $installments = \app\models\Cuotas::find()
                ->where(['id_pago' => $model->id])
                ->orderBy(['numero_cuota' => SORT_ASC])
                ->all();
            ?>

            <?php if (!empty($installments)): ?>
                <div class="p-4" style="border-bottom: 1px solid #e1e1e1;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 style="color: #0078d4; font-size: 16px; font-weight: 700; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-list-ol me-2"></i> CUOTAS PAGADAS
                        </h3>
                        <span class="badge" style="background-color: #107c10; font-size: 14px; padding: 8px 16px; border-radius: 6px;">
                            <i class="fas fa-chart-line me-1"></i> Total: $ <?= number_format(array_sum(array_map(function ($i) {
                                                                                return $i->monto_usd ?: $i->monto;
                                                                            }, $installments)), 2) ?>
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover" style="font-size: 14px;">
                            <thead style="background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%);">
                                <tr>
                                    <th class="text-center" style="padding: 14px; font-weight: 700; color: #ffffff !important;"># Cuota</th>
                                    <th class="text-center" style="padding: 14px; font-weight: 700; color: #ffffff !important;">Fecha Vencimiento</th>
                                    <th class="text-center" style="padding: 14px; font-weight: 700; color: #ffffff !important;">Monto</th>
                                    <th class="text-center" style="padding: 14px; font-weight: 700; color: #ffffff !important;">Período de Cobertura</th>
                                    <th class="text-center" style="padding: 14px; font-weight: 700; color: #ffffff !important;">Recibo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($installments as $installment): ?>
                                    <?php $receiptForInstallment = \app\components\ReceiptGenerator::getReceiptForInstallment($installment->id); ?>
                                    <tr style="border-bottom: 1px solid #f0f0f0;">
                                        <td class="text-center" style="padding: 12px; vertical-align: middle;">
                                            <span style="font-size: 18px; font-weight: 700; color: #0078d4;"><?= $installment->numero_cuota ?></span>
                                        </td>
                                        <td class="text-center" style="padding: 12px; vertical-align: middle; font-weight: 500;">
                                            <?= Yii::$app->formatter->asDate($installment->fecha_vencimiento, 'php:d/m/Y') ?>
                                        </td>
                                        <td class="text-center" style="padding: 12px; vertical-align: middle;">
                                            <span style="font-size: 16px; font-weight: 700; color: #107c10;">
                                                $ <?= number_format($installment->monto_usd ?: $installment->monto, 2) ?>
                                            </span>
                                        </td>
                                        <td class="text-center" style="padding: 12px; vertical-align: middle;">
                                            <?php if ($installment->coverage_start && $installment->coverage_end): ?>
                                                <span class="badge" style="background-color: #e9ecef; color: #495057; font-size: 12px; padding: 6px 12px;">
                                                    <i class="fas fa-calendar-week me-1"></i>
                                                    <?= Yii::$app->formatter->asDate($installment->coverage_start, 'php:d/m/Y') ?> - <?= Yii::$app->formatter->asDate($installment->coverage_end, 'php:d/m/Y') ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">No especificado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center" style="padding: 12px; vertical-align: middle;">
                                            <?php if ($receiptForInstallment): ?>
                                                <?= Html::a(
                                                    '<i class="fas fa-receipt me-2"></i> ' . $receiptForInstallment->receipt_number,
                                                    ['/receipts/print', 'id' => $receiptForInstallment->id, 'auto_print' => 1],
                                                    [
                                                        'target' => '_blank',
                                                        'class' => 'btn btn-primary btn-sm',
                                                        'style' => 'font-size: 12px; font-weight: 600; border-radius: 6px; padding: 8px 16px; background-color: #0078d4; border: none; white-space: nowrap;'
                                                    ]
                                                ) ?>
                                            <?php else: ?>
                                                <span class="text-muted">No generado</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <!-- GROUP 1: Payment Information + Payer Information -->
            <div class="border-bottom" style="border-color: #e1e1e1 !important;">
                <div class="row g-0">
                    <!-- LEFT: Payment Information -->
                    <div class="col-lg-6 border-end" style="border-color: #e1e1e1 !important;">
                        <div class="p-4">
                            <h3 style="color: #0078d4; font-size: 16px; font-weight: 700; margin: 0 0 16px 0; text-transform: uppercase; letter-spacing: 0.5px;">
                                <i class="fas fa-dollar-sign me-2"></i> INFORMACIÓN DEL PAGO
                            </h3>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label style="display: block; font-size: 12px; color: #6c757d; margin-bottom: 4px; font-weight: 600;">MONTO PAGADO (USD)</label>
                                    <span style="font-size: 28px; font-weight: 700; color: #107c10;">$ <?= number_format($model->monto_pagado, 2) ?></span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label style="display: block; font-size: 12px; color: #6c757d; margin-bottom: 4px; font-weight: 600;">MONTO EN BOLÍVARES</label>
                                    <span style="font-size: 20px; font-weight: 700;">Bs. <?= number_format($model->monto_usd, 2) ?></span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label style="display: block; font-size: 12px; color: #6c757d; margin-bottom: 4px; font-weight: 600;">MÉTODO DE PAGO</label>
                                    <span style="font-size: 15px; font-weight: 500;"><?= Html::encode($model->metodo_pago ?: 'N/A') ?></span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label style="display: block; font-size: 12px; color: #6c757d; margin-bottom: 4px; font-weight: 600;">NÚMERO DE REFERENCIA</label>
                                    <code style="font-size: 14px; background: #f5f5f5; padding: 4px 8px;"><?= Html::encode($model->numero_referencia_pago ?: 'N/A') ?></code>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label style="display: block; font-size: 12px; color: #6c757d; margin-bottom: 4px; font-weight: 600;">TASA DE CAMBIO</label>
                                    <span style="font-size: 15px; font-weight: 500;">
                                        <?php if ($tasaCalculada): ?>
                                            <?= number_format($tasaCalculada, 2) ?> Bs/USD
                                            <small style="font-size: 11px; color: #6c757d; display: block;">(Monto Bs ÷ Monto USD)</small>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label style="display: block; font-size: 12px; color: #6c757d; margin-bottom: 4px; font-weight: 600;">TIPO DE PAGO</label>
                                    <?php if ($model->tipo_pago === 'corporativo' && $model->corporativo_id): ?>
                                        <span class="badge" style="background-color: #6f42c1; font-size: 12px; padding: 4px 12px;" title="Pago realizado por una empresa/corporación para cubrir múltiples afiliados">CORPORATIVO</span>
                                    <?php elseif ($model->pago_corporativo_id): ?>
                                        <span class="badge" style="background-color: #ffc107; font-size: 12px; padding: 4px 12px; color: #212529;" title="Pago realizado por un afiliado individual que pertenece a una corporación">AFILIADO CORPORATIVO</span>
                                    <?php else: ?>
                                        <span class="badge" style="background-color: #17a2b8; font-size: 12px; padding: 4px 12px;" title="Pago realizado por una persona natural independiente (no corporativa)">INDIVIDUAL</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT: Payer Information (Contractor) -->
                    <div class="col-lg-6">
                        <div class="p-4">
                            <h3 style="color: #0078d4; font-size: 16px; font-weight: 700; margin: 0 0 16px 0; text-transform: uppercase; letter-spacing: 0.5px;">
                                <i class="fas fa-user-circle me-2"></i> INFORMACIÓN DEL PAGADOR
                            </h3>

                            <div class="d-flex align-items-start mb-4">
                                <div class="me-3">
                                    <div style="width: 48px; height: 48px; background-color: #e9ecef; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-user" style="font-size: 24px; color: #0078d4;"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 style="font-size: 18px; font-weight: 700; margin: 0 0 4px 0; color: #333;"><?= Html::encode($payerName) ?></h4>
                                    <p style="font-size: 13px; color: #6c757d; margin: 0;">
                                        <i class="fas fa-id-card me-1"></i> <?= Html::encode($payerId) ?>
                                    </p>
                                    <?= $payerNote ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label style="display: block; font-size: 12px; color: #6c757d; margin-bottom: 4px; font-weight: 600;">TELÉFONO</label>
                                    <span style="font-size: 14px;"><?= Html::encode($payerPhone) ?></span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label style="display: block; font-size: 12px; color: #6c757d; margin-bottom: 4px; font-weight: 600;">CORREO ELECTRÓNICO</label>
                                    <span style="font-size: 13px;"><?= Html::encode($payerEmail) ?></span>
                                </div>
                                <div class="col-12 mb-3">
                                    <label style="display: block; font-size: 12px; color: #6c757d; margin-bottom: 4px; font-weight: 600;">DIRECCIÓN</label>
                                    <span style="font-size: 14px;"><?= Html::encode($payerAddress) ?></span>
                                </div>
                            </div>

                            <?php if ($hasDifferentPayer && (!empty($userDatos->profesion_contratante) || !empty($userDatos->ocupacion_contratante))): ?>
                                <div class="mt-3 pt-2 border-top" style="border-color: #e1e1e1 !important;">
                                    <div class="row">
                                        <?php if (!empty($userDatos->profesion_contratante)): ?>
                                            <div class="col-md-6 mb-2">
                                                <label style="display: block; font-size: 11px; color: #6c757d; margin-bottom: 2px;">PROFESIÓN</label>
                                                <span style="font-size: 13px;"><?= Html::encode($userDatos->profesion_contratante) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($userDatos->ocupacion_contratante)): ?>
                                            <div class="col-md-6 mb-2">
                                                <label style="display: block; font-size: 11px; color: #6c757d; margin-bottom: 2px;">OCUPACIÓN</label>
                                                <span style="font-size: 13px;"><?= Html::encode($userDatos->ocupacion_contratante) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GROUP 2: Important Dates + Conciliation Information -->
            <div class="border-bottom" style="border-color: #e1e1e1 !important;">
                <div class="row g-0">
                    <!-- LEFT: Important Dates -->
                    <div class="col-lg-6 border-end" style="border-color: #e1e1e1 !important;">
                        <div class="p-4">
                            <h3 style="color: #0078d4; font-size: 16px; font-weight: 700; margin: 0 0 16px 0; text-transform: uppercase; letter-spacing: 0.5px;">
                                <i class="fas fa-calendar-alt me-2"></i> FECHAS IMPORTANTES
                            </h3>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label style="display: block; font-size: 12px; color: #6c757d; margin-bottom: 4px; font-weight: 600;">FECHA DE PAGO</label>
                                    <span style="font-size: 14px; font-weight: 500;"><?= $model->fecha_pago ? Yii::$app->formatter->asDate($model->fecha_pago, 'php:d/m/Y H:i:s') : 'Por definir' ?></span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label style="display: block; font-size: 12px; color: #6c757d; margin-bottom: 4px; font-weight: 600;">FECHA DE REGISTRO</label>
                                    <span style="font-size: 14px; font-weight: 500;"><?= $model->fecha_registro ? Yii::$app->formatter->asDate($model->fecha_registro, 'php:d/m/Y H:i:s') : 'Por definir' ?></span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label style="display: block; font-size: 12px; color: #6c757d; margin-bottom: 4px; font-weight: 600;">FECHA DE CONCILIACIÓN</label>
                                    <span style="font-size: 14px; font-weight: 500;"><?= $model->fecha_conciliacion ? Yii::$app->formatter->asDate($model->fecha_conciliacion, 'php:d/m/Y H:i:s') : 'Pendiente' ?></span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label style="display: block; font-size: 12px; color: #6c757d; margin-bottom: 4px; font-weight: 600;">ÚLTIMA ACTUALIZACIÓN</label>
                                    <span style="font-size: 14px; font-weight: 500;"><?= $model->updated_at ? Yii::$app->formatter->asDate($model->updated_at, 'php:d/m/Y H:i:s') : 'N/A' ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT: Conciliation Information -->
                    <div class="col-lg-6">
                        <div class="p-4">
                            <h3 style="color: #0078d4; font-size: 16px; font-weight: 700; margin: 0 0 16px 0; text-transform: uppercase; letter-spacing: 0.5px;">
                                <i class="fas fa-clipboard-list me-2"></i> INFORMACIÓN DE CONCILIACIÓN
                            </h3>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label style="display: block; font-size: 12px; color: #6c757d; margin-bottom: 4px; font-weight: 600;">ESTADO</label>
                                    <span style="font-size: 14px; font-weight: 500;"><?= $model->conciliado ? 'Conciliado' : 'No conciliado' ?></span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label style="display: block; font-size: 12px; color: #6c757d; margin-bottom: 4px; font-weight: 600;">ID CONCILIADOR</label>
                                    <span style="font-size: 14px;"><?= Html::encode($model->conciliador_id ?: 'N/A') ?></span>
                                </div>
                                <div class="col-12 mb-3">
                                    <label style="display: block; font-size: 12px; color: #6c757d; margin-bottom: 4px; font-weight: 600;">NOMBRE DEL CONCILIADOR</label>
                                    <span style="font-size: 14px;"><?= Html::encode($model->nombre_conciliador ?: 'No asignado') ?></span>
                                </div>
                                <?php if ($model->observacion): ?>
                                    <div class="col-12">
                                        <label style="display: block; font-size: 12px; color: #6c757d; margin-bottom: 4px; font-weight: 600;">OBSERVACIONES</label>
                                        <div style="background-color: #f5f5f5; padding: 12px; border-radius: 6px; font-size: 13px;">
                                            <?= nl2br(Html::encode($model->observacion)) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Proof Section -->
            <?php if ($model->imagen_prueba): ?>
                <div class="p-4">
                    <h3 style="color: #0078d4; font-size: 16px; font-weight: 700; margin: 0 0 16px 0; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="fas fa-image me-2"></i> COMPROBANTE DE PAGO
                    </h3>
                    <div class="text-center">
                        <?= Html::a(
                            '<i class="fas fa-external-link-alt me-2"></i> Ver Comprobante',
                            $model->imagen_prueba,
                            [
                                'target' => '_blank',
                                'class' => 'btn btn-primary',
                                'style' => 'background-color: #0078d4; border: none; border-radius: 6px; padding: 10px 24px; font-size: 13px; font-weight: 600;'
                            ]
                        ) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    /* Microsoft Fluent Design System */
    * {
        font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Roboto', sans-serif;
    }

    .card {
        box-shadow: 0 1px 4px 0 rgba(0, 0, 0, 0.05);
        transition: box-shadow 0.2s ease;
    }

    .card:hover {
        box-shadow: 0 4px 12px 0 rgba(0, 0, 0, 0.08);
    }

    /* Microsoft Blue theme */
    .btn-primary {
        background-color: #0078d4;
        border-color: #0078d4;
    }

    .btn-primary:hover {
        background-color: #106ebe;
        border-color: #106ebe;
    }

    /* Warning button */
    .btn-warning {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #212529;
    }

    .btn-warning:hover {
        background-color: #e0a800;
        border-color: #e0a800;
        color: #212529;
    }

    /* Table styling */
    .table th {
        border-top: none;
    }

    .table td {
        vertical-align: middle;
    }

    .table tbody tr:hover {
        background-color: rgba(0, 120, 212, 0.04);
    }

    /* Print styles */
    @media print {

        .btn,
        .btn-group {
            display: none !important;
        }

        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }

        .card-header {
            background-color: #0078d4 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            padding: 0;
            margin: 0;
        }

        .container-fluid {
            padding: 0;
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .container-fluid {
            padding: 12px !important;
        }

        .border-end {
            border-right: none !important;
        }

        .btn-sm {
            font-size: 11px !important;
            padding: 6px 10px !important;
        }

        .table {
            font-size: 12px !important;
        }

        .ms-0 {
            margin-left: 0 !important;
        }

        .ms-sm-3 {
            margin-left: 0 !important;
        }
    }

    @media (min-width: 576px) {
        .ms-sm-3 {
            margin-left: 1rem !important;
        }
    }
</style>