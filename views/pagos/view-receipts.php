<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Pagos $payment */
/** @var app\models\Receipt[] $receipts */
/** @var app\models\UserDatos $user */

$this->title = 'Recibos Generados - Pago #' . $payment->id;
$this->params['breadcrumbs'][] = ['label' => 'Pagos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Pago #' . $payment->id, 'url' => ['view', 'id' => $payment->id]];
$this->params['breadcrumbs'][] = 'Recibos';
?>

<div class="receipts-view">
    <div class="card">
        <div class="card-header" style="background: #0078d4; color: white;">
            <h3 class="card-title mb-0 display-4" style="font-size: 2.3rem;">
                <i class="fas fa-receipt mr-2"></i>
                Recibos Generados - Pago #<?= $payment->id ?>
            </h3>
        </div>
        <div class="card-body">
            <!-- Payment Summary -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-user"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Afiliado</span>
                            <span class="info-box-number"><?= Html::encode($user->nombres . ' ' . $user->apellidos) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Monto Pagado</span>
                            <span class="info-box-number">$<?= number_format($payment->monto_pagado, 2) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-calendar"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Fecha de Pago</span>
                            <span class="info-box-number"><?= Yii::$app->formatter->asDate($payment->fecha_pago, 'php:d/m/Y') ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary"><i class="fas fa-receipt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Recibos</span>
                            <span class="info-box-number"><?= count($receipts) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Receipts List -->
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-striped">
                    <thead style="background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%);">
                        <tr>
                            <th class="text-center align-middle text-white font-weight-bold" style="width: 50px; padding: 15px 8px; color: white !important;">#</th>
                            <th class="text-center align-middle text-white font-weight-bold" style="padding: 15px 8px; color: white !important;">Número de Recibo</th>
                            <th class="text-center align-middle text-white font-weight-bold" style="padding: 15px 8px; color: white !important;">Cuota</th>
                            <th class="text-center align-middle text-white font-weight-bold" style="padding: 15px 8px; color: white !important;">Período de Cobertura</th>
                            <th class="text-center align-middle text-white font-weight-bold" style="padding: 15px 8px; color: white !important;">Monto (Bs)</th>
                            <th class="text-center align-middle text-white font-weight-bold" style="padding: 15px 8px; color: white !important;">Fecha de Emisión</th>
                            <th class="text-center align-middle text-white font-weight-bold" style="width: 180px; padding: 15px 8px; color: white !important;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($receipts as $index => $receipt): ?>
                            <?php
                            $installment = \app\models\Cuotas::findOne($receipt->installment_id);
                            ?>
                            <tr style="transition: all 0.2s ease;">
                                <td class="text-center align-middle">
                                    <span class="badge" style="background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%); color: white; padding: 6px 12px; border-radius: 8px; font-size: 13px; min-width: 35px; display: inline-block;">
                                        <?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?>
                                    </span>
                                </td>
                                <td class="text-center align-middle">
                                    <strong style="color: #0078d4; font-size: 14px;">
                                        <i class="fas fa-receipt mr-1" style="color: #0078d4;"></i>
                                        <?= Html::encode($receipt->receipt_number) ?>
                                    </strong>
                                </td>
                                <td class="text-center align-middle">
                                    <?php if ($installment): ?>
                                        <span class="badge badge-info" style="font-size: 13px; padding: 6px 12px;">
                                            <i class="fas fa-hashtag mr-1"></i> <?= $installment->numero_cuota ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center align-middle">
                                    <span style="font-size: 13px;">
                                        <i class="fas fa-calendar-alt mr-1 text-muted" style="font-size: 11px;"></i>
                                        <?= Yii::$app->formatter->asDate($receipt->coverage_start_date, 'php:d/m/Y') ?>
                                        <i class="fas fa-arrow-right mx-1 text-muted" style="font-size: 11px;"></i>
                                        <?= Yii::$app->formatter->asDate($receipt->coverage_end_date, 'php:d/m/Y') ?>
                                    </span>
                                </td>
                                <td class="text-center align-middle">
                                    <strong style="color: #0078d4; font-size: 15px;">
                                        <i class="fas fa-chart-line mr-1" style="font-size: 12px;"></i>
                                        Bs. <?= number_format($receipt->amount, 2, ',', '.') ?>
                                    </strong>
                                </td>
                                <td class="text-center align-middle">
                                    <span style="font-size: 15px;">
                                        <i class="fas fa-calendar-check mr-1 text-muted" style="font-size: 11px;"></i>
                                        <?= Yii::$app->formatter->asDate($receipt->issue_date, 'php:d/m/Y') ?>
                                    </span>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="btn-group btn-group-sm" role="group" style="gap: 4px; flex-wrap: wrap; justify-content: center;">
                                        <?= Html::a(
                                            '<i class="fas fa-eye"></i>',
                                            ['pagos/view-receipt-html', 'id' => $receipt->id],
                                            [
                                                'class' => 'btn btn-outline-primary btn-sm',
                                                'target' => '_blank',
                                                'title' => 'Ver Recibo',
                                                'style' => 'border-radius: 6px; padding: 6px 10px; min-width: 34px; border-width: 1.5px;'
                                            ]
                                        ) ?>
                                        <?= Html::a(
                                            '<i class="fas fa-print"></i>',
                                            ['receipts/print', 'id' => $receipt->id, 'auto_print' => 1],
                                            [
                                                'class' => 'btn btn-outline-success btn-sm',
                                                'target' => '_blank',
                                                'title' => 'Imprimir Recibo',
                                                'style' => 'border-radius: 6px; padding: 6px 10px; min-width: 34px; border-width: 1.5px;'
                                            ]
                                        ) ?>
                                        <?= Html::a(
                                            '<i class="fas fa-envelope"></i>',
                                            ['pagos/resend-receipt-email', 'id' => $receipt->id],
                                            [
                                                'class' => 'btn btn-outline-secondary btn-sm',
                                                'title' => 'Reenviar por Email',
                                                'style' => 'border-radius: 6px; padding: 6px 10px; min-width: 34px; border-width: 1.5px;',
                                                'data' => [
                                                    'method' => 'post',
                                                    'confirm' => '¿Desea reenviar este recibo por email a ' . Html::encode($user->email) . '?'
                                                ]
                                            ]
                                        ) ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Actions -->
            <div class="mt-4 text-center">
                <?php if (count($receipts) > 1): ?>
                    <?= Html::a(
                        '<i class="fas fa-print mr-2"></i> Imprimir Todos',
                        ['receipts/print-all', 'paymentId' => $payment->id, 'auto_print' => 1],
                        [
                            'class' => 'btn btn-info btn-lg px-5',
                            'target' => '_blank',
                            'style' => 'border-radius: 8px; font-weight: 600; box-shadow: 0 2px 8px rgba(23, 162, 184, 0.3);'
                        ]
                    ) ?>

                    <?= Html::a(
                        '<i class="fas fa-envelope mr-2"></i> Reenviar Todos por Email',
                        ['pagos/resend-all-receipts-email', 'payment_id' => $payment->id],
                        [
                            'class' => 'btn btn-secondary btn-lg px-5 ml-2',
                            'style' => 'border-radius: 8px; font-weight: 600;',
                            'data' => [
                                'method' => 'post',
                                'confirm' => '¿Desea reenviar todos los recibos por email a ' . Html::encode($user->email) . '?'
                            ]
                        ]
                    ) ?>
                <?php endif; ?>

                <?= Html::a(
                    '<i class="fas fa-arrow-left mr-2"></i> Volver al Pago',
                    ['pagos/view', 'id' => $payment->id],
                    ['class' => 'btn btn-primary btn-lg px-5 ml-2', 'style' => 'border-radius: 8px; font-weight: 600;']
                ) ?>

                <?= Html::a(
                    '<i class="fas fa-arrow-left mr-2"></i> Volver a Contratos',
                    ['contratos/index', 'user_id' => $payment->user_id],
                    ['class' => 'btn btn-success btn-lg px-5 ml-2', 'style' => 'border-radius: 8px; font-weight: 600;']
                ) ?>
            </div>
        </div>
    </div>
</div>

<style>
    .info-box {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        background: #f8f9fa;
        border-radius: 12px;
        border: 1px solid #e9ecef;
        transition: all 0.2s ease;
        height: 100%;
    }

    .info-box:hover {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
        border-color: #d4d9e2;
    }

    .info-box-icon {
        font-size: 2rem;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: white;
        margin-right: 15px;
        flex-shrink: 0;
    }

    .info-box-content {
        flex: 1;
        min-width: 0;
    }

    .info-box-text {
        font-size: 1.1rem;
        color: #6c757d;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
    }

    .info-box-number {
        font-size: 1.6rem;
        font-weight: 600;
        color: #212529;
        word-break: break-word;
    }

    /* Table styling */
    .table {
        font-size: 13px;
        margin-bottom: 0;
    }

    .table thead th {
        border-bottom: 2px solid #005a9e;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
    }

    .table tbody tr:hover {
        background: #f0f7ff !important;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background: #fafbfc;
    }

    .table-bordered {
        border: 1px solid #dee2e6;
    }

    .table-bordered th,
    .table-bordered td {
        border: 1px solid #dee2e6;
        vertical-align: middle;
    }

    /* Button styling */
    .btn-group .btn {
        transition: all 0.2s ease;
    }

    .btn-group .btn:hover {
        transform: translateY(-1px);
    }

    .btn-group .btn-outline-primary:hover {
        background: #0078d4;
        color: white;
        box-shadow: 0 2px 8px rgba(0, 120, 212, 0.25);
    }

    .btn-group .btn-outline-success:hover {
        background: #28a745;
        color: white;
        box-shadow: 0 2px 8px rgba(40, 167, 69, 0.25);
    }

    .btn-group .btn-outline-secondary:hover {
        background: #6c757d;
        color: white;
        box-shadow: 0 2px 8px rgba(108, 117, 125, 0.25);
    }

    .btn-group .btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    /* Badge styling */
    .badge-info {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
        color: #0d47a1 !important;
        border: 1px solid #90caf9;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .info-box {
            padding: 12px 15px;
        }

        .info-box-icon {
            width: 40px;
            height: 40px;
            font-size: 1.5rem;
            margin-right: 10px;
        }

        .info-box-number {
            font-size: 1rem;
        }

        .table {
            font-size: 12px;
        }

        .btn-group .btn {
            padding: 4px 8px;
            font-size: 12px;
        }

        .btn-group {
            flex-wrap: wrap;
            gap: 3px;
            justify-content: center;
        }

        .btn-lg {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }
    }

    @media print {

        .btn-group,
        .btn,
        .no-print {
            display: none !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .table {
            font-size: 10px;
        }

        .table thead th {
            background: #2c3e50 !important;
            color: white !important;
        }

        .info-box {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
        }
    }

    /* Scrollbar styling */
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f3f5;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #d4d9e2;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #b8c0cc;
    }
</style>