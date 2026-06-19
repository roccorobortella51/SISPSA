<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Pagos $model */
/** @var array $cuotas */
/** @var app\models\Contratos $selectedContrato */

$this->title = 'Registrar Pago';
$this->params['breadcrumbs'][] = ['label' => 'Pagos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// --- PREPARE USER DATA FOR DISPLAY ---
$nombres = $model->userDatos->nombres ?? 'N/A';
$apellidos = $model->userDatos->apellidos ?? 'N/A';
$cedula = $model->userDatos->cedula ?? 'N/A';
$tipoCedula = $model->userDatos->tipo_cedula ?? '';
$email = $model->userDatos->email ?? 'N/A';
$nombreCompleto = $nombres . ' ' . $apellidos;
$cedulaCompleta = $tipoCedula ? $tipoCedula . '-' . $cedula : $cedula;
?>

<div class="pagos-create">
    <!-- Command Bar - Microsoft Style (Smaller) -->
    <div class="command-bar mb-3">
        <?= Html::a(
            '<i class="fas fa-arrow-left mr-2"></i> Volver a Contratos',
            Url::to(['contratos/index', 'user_id' => $model->user_id]),
            ['class' => 'btn btn-command']
        ) ?>
    </div>

    <!-- SINGLE CLEAN FRAME - Microsoft Fluent Container -->
    <div class="fluent-container">

        <!-- Header - Microsoft 365 Style with Affiliate Info (Smaller) -->
        <div class="fluent-header">
            <div class="d-flex align-items-center">
                <div class="avatar-fluent">
                    <i class="fas fa-user"></i>
                </div>
                <div class="ml-3">
                    <div class="header-title"><?= Html::encode($nombreCompleto) ?></div>
                    <div class="header-subtitle">
                        <span class="mr-3"><i class="fas fa-id-card mr-1"></i><?= $cedulaCompleta ?></span>
                        <span><i class="fas fa-envelope mr-1"></i><?= $email ?></span>
                    </div>
                </div>
            </div>
            <div class="header-badge">
                <i class="fas fa-credit-card mr-2"></i>Nuevo Pago
            </div>
        </div>

        <!-- Content Area -->
        <div class="fluent-content">

            <!-- ===== CONTRACT INFORMATION ===== -->
            <?php if (isset($selectedContrato) && $selectedContrato): ?>

                <?php
                // Status configuration with Microsoft Enterprise colors
                $rawStatus = strtolower(trim($selectedContrato->estatus));

                $statusConfig = [
                    'activo' => [
                        'class' => 'status-active',
                        'icon' => 'fa-check-circle',
                        'text' => 'ACTIVO',
                        'description' => 'Contrato vigente y al día'
                    ],
                    'registrado' => [
                        'class' => 'status-registered',
                        'icon' => 'fa-clock',
                        'text' => 'REGISTRADO',
                        'description' => 'Contrato registrado, pendiente de inicio'
                    ],
                    'creado manual' => [
                        'class' => 'status-manual',
                        'icon' => 'fa-pencil-alt',
                        'text' => 'CREADO MANUAL',
                        'description' => 'Contrato creado manualmente'
                    ],
                    'suspendido' => [
                        'class' => 'status-suspended',
                        'icon' => 'fa-pause-circle',
                        'text' => 'SUSPENDIDO',
                        'description' => 'Contrato suspendido por falta de pago'
                    ],
                    'vencida' => [
                        'class' => 'status-expired',
                        'icon' => 'fa-exclamation-triangle',
                        'text' => 'VENCIDO',
                        'description' => 'Contrato vencido'
                    ],
                    'anulado' => [
                        'class' => 'status-cancelled',
                        'icon' => 'fa-ban',
                        'text' => 'ANULADO',
                        'description' => 'Contrato anulado'
                    ],
                ];

                // Status resolution
                if ($rawStatus === 'suspendido') {
                    $config = $statusConfig['suspendido'];
                } elseif ($rawStatus === 'vencida') {
                    $config = $statusConfig['vencida'];
                } elseif (isset($statusConfig[$rawStatus])) {
                    $config = $statusConfig[$rawStatus];
                } else {
                    $config = [
                        'class' => 'status-unknown',
                        'icon' => 'fa-question-circle',
                        'text' => strtoupper($selectedContrato->estatus),
                        'description' => 'Estado desconocido'
                    ];
                }
                ?>

                <!-- Status Banner - Microsoft 365 Style (Smaller) -->
                <div class="status-banner-fluent <?= $config['class'] ?>">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="status-icon-fluent">
                                <i class="fas <?= $config['icon'] ?>"></i>
                            </div>
                            <div class="ml-3">
                                <div class="status-text"><?= $config['text'] ?></div>
                                <div class="status-desc"><?= $config['description'] ?></div>
                            </div>
                        </div>
                        <div class="contract-number-fluent">
                            <div class="contract-label">CONTRATO #</div>
                            <div class="contract-value"><?= $selectedContrato->nrocontrato ?: $selectedContrato->id ?></div>
                        </div>
                    </div>
                </div>

                <!-- Contract Details Cards - SMALLER VERSION -->
                <div class="row mt-3">
                    <!-- BLUE CARD - Período del Contrato -->
                    <div class="col-md-3">
                        <div class="info-card card-blue">
                            <div class="info-card-content">
                                <div class="info-card-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="info-card-text">
                                    <span class="info-card-label">PERÍODO</span>
                                    <span class="info-card-value">
                                        <?= Yii::$app->formatter->asDate($selectedContrato->fecha_ini, 'php:d/m/Y') ?>
                                        <i class="fas fa-arrow-right mx-1"></i>
                                        <?= Yii::$app->formatter->asDate($selectedContrato->fecha_ven, 'php:d/m/Y') ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- GREEN CARD - Clínica Asignada -->
                    <div class="col-md-3">
                        <div class="info-card card-green">
                            <div class="info-card-content">
                                <div class="info-card-icon">
                                    <i class="fas fa-hospital"></i>
                                </div>
                                <div class="info-card-text">
                                    <span class="info-card-label">CLÍNICA</span>
                                    <span class="info-card-value">
                                        <?= $selectedContrato->clinica ? Html::encode($selectedContrato->clinica->nombre) : 'N/A' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TEAL CARD - Plan Contratado -->
                    <div class="col-md-3">
                        <div class="info-card card-teal">
                            <div class="info-card-content">
                                <div class="info-card-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="info-card-text">
                                    <span class="info-card-label">PLAN</span>
                                    <span class="info-card-value">
                                        <?= $selectedContrato->plan ? Html::encode($selectedContrato->plan->nombre) : 'N/A' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PURPLE CARD - Monto Mensual -->
                    <div class="col-md-3">
                        <div class="info-card card-purple">
                            <div class="info-card-content">
                                <div class="info-card-icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <div class="info-card-text">
                                    <span class="info-card-label">MONTO</span>
                                    <span class="info-card-value">
                                        $<?= number_format($selectedContrato->monto, 2) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- Error Message - Microsoft Style (Smaller) -->
                <div class="error-fluent">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <div class="error-title">Error: Contrato no seleccionado</div>
                        <div class="error-message">No se ha seleccionado un contrato válido para registrar el pago.</div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Payment Form -->
            <div class="mt-4">
                <?= $this->render('_form', [
                    'model' => $model,
                    'cuotas' => $cuotas,
                    'user_id' => $model->user_id,
                    'isEditable' => true,
                    'selectedContrato' => $selectedContrato,
                ]) ?>
            </div>
        </div>
    </div>
</div>

<style>
    /* ===== MICROSOFT ENTERPRISE STANDARD STYLES (COMPACT VERSION) ===== */
    /* Fluent UI Design System - Inspired by Microsoft 365 */

    /* Typography - Microsoft Segoe UI */
    body,
    .fluent-container {
        font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, system-ui, sans-serif;
    }

    /* Command Bar */
    .command-bar {
        padding: 0;
    }

    .btn-command {
        background: white;
        border: 1px solid #8a8886;
        color: #323130;
        padding: 6px 16px;
        font-size: 13px;
        font-weight: 500;
        border-radius: 4px;
        transition: all 0.1s ease;
    }

    .btn-command:hover {
        background: #f3f2f1;
        border-color: #323130;
    }

    .btn-command i {
        color: #605e5c;
    }

    /* ===== SINGLE CLEAN CONTAINER ===== */
    .fluent-container {
        background: white;
        border: 1px solid #edebe9;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    /* Header - Microsoft 365 Style (Compact) */
    .fluent-header {
        background: #0078d4;
        padding: 16px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #106ebe;
    }

    /* Force white text in header */
    .fluent-header,
    .fluent-header .header-title,
    .fluent-header .header-subtitle,
    .fluent-header .header-subtitle span,
    .fluent-header .header-subtitle i,
    .fluent-header .avatar-fluent i {
        color: white !important;
    }

    .fluent-header .header-subtitle i {
        color: rgba(255, 255, 255, 0.9) !important;
    }

    .avatar-fluent {
        width: 44px;
        height: 44px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .header-title {
        font-size: 18px;
        font-weight: 600;
        line-height: 1.3;
        margin-bottom: 3px;
    }

    .header-subtitle {
        font-size: 13px;
        font-weight: 400;
    }

    .header-badge {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        padding: 6px 16px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 500;
    }

    /* Content Area */
    .fluent-content {
        padding: 20px 24px;
    }

    /* Status Banner (Compact) */
    .status-banner-fluent {
        padding: 14px 20px;
        border-radius: 6px;
        margin-bottom: 18px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    }

    .status-active {
        background: #107c10;
    }

    .status-registered {
        background: #0078d4;
    }

    .status-manual {
        background: #ffb900;
    }

    .status-suspended {
        background: #605e5c;
    }

    .status-expired {
        background: #d83b01;
    }

    .status-cancelled {
        background: #8a8886;
    }

    .status-unknown {
        background: #8a8886;
    }

    .status-icon-fluent {
        width: 42px;
        height: 42px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: white;
        flex-shrink: 0;
    }

    .status-text {
        color: white;
        font-size: 17px;
        font-weight: 600;
        line-height: 1.2;
    }

    .status-desc {
        color: rgba(255, 255, 255, 0.9);
        font-size: 12px;
        font-weight: 400;
    }

    .contract-number-fluent {
        background: rgba(255, 255, 255, 0.2);
        padding: 6px 16px;
        border-radius: 4px;
        text-align: right;
        flex-shrink: 0;
    }

    .contract-label {
        color: rgba(255, 255, 255, 0.8);
        font-size: 10px;
        font-weight: 500;
        letter-spacing: 0.5px;
    }

    .contract-value {
        color: white;
        font-size: 17px;
        font-weight: 600;
    }

    /* ===== COMPACT INFO CARDS ===== */
    .info-card {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%;
    }

    .info-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.10);
    }

    .info-card-content {
        padding: 0.9rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }

    .info-card-icon {
        width: 44px;
        height: 44px;
        background: rgba(0, 0, 0, 0.06);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    .info-card-text {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .info-card-label {
        font-size: 1.6rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 0.1rem;
        font-weight: 700;
        opacity: 0.7;
    }

    .info-card-value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.3;
        word-break: break-word;
    }

    /* Subtle Blue Card */
    .card-blue {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdef5 100%);
        border: 1px solid #90caf9;
    }

    .card-blue .info-card-icon,
    .card-blue .info-card-label,
    .card-blue .info-card-value {
        color: #0d47a1;
    }

    /* Subtle Green Card */
    .card-green {
        background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
        border: 1px solid #a5d6a7;
    }

    .card-green .info-card-icon,
    .card-green .info-card-label,
    .card-green .info-card-value {
        color: #1b5e20;
    }

    /* Subtle Teal Card */
    .card-teal {
        background: linear-gradient(135deg, #e0f2f1 0%, #b2dfdb 100%);
        border: 1px solid #80cbc4;
    }

    .card-teal .info-card-icon,
    .card-teal .info-card-label,
    .card-teal .info-card-value {
        color: #004d40;
    }

    /* Subtle Purple Card */
    .card-purple {
        background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);
        border: 1px solid #ce93d8;
    }

    .card-purple .info-card-icon,
    .card-purple .info-card-label,
    .card-purple .info-card-value {
        color: #4a148c;
    }

    /* Error Message (Compact) */
    .error-fluent {
        background: #fef1f0;
        border: 1px solid #f3b9b4;
        border-left: 6px solid #d83b01;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        gap: 16px;
        border-radius: 6px;
    }

    .error-fluent i {
        color: #d83b01;
        font-size: 24px;
        flex-shrink: 0;
    }

    .error-title {
        color: #323130;
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 3px;
    }

    .error-message {
        color: #605e5c;
        font-size: 13px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .fluent-header {
            flex-direction: column;
            text-align: center;
            gap: 12px;
            padding: 14px 16px;
        }

        .fluent-content {
            padding: 14px 16px;
        }

        .info-card-content {
            flex-direction: column;
            text-align: center;
            padding: 0.7rem;
        }

        .status-banner-fluent .d-flex {
            flex-direction: column;
            text-align: center;
            gap: 12px;
        }

        .status-icon-fluent {
            margin: 0 auto;
        }

        .contract-number-fluent {
            text-align: center;
        }

        .header-title {
            font-size: 16px;
        }

        .header-subtitle {
            font-size: 12px;
        }

        .info-card-value {
            font-size: 0.75rem;
        }
    }

    /* Focus States */
    .btn-command:focus,
    .info-card:focus-within {
        outline: 2px solid #0078d4;
        outline-offset: 2px;
    }
</style>