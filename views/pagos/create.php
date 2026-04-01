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
    <!-- Command Bar - Microsoft Style -->
    <div class="command-bar mb-4">
        <?= Html::a(
            '<i class="fas fa-arrow-left mr-2"></i> Volver a Contratos',
            Url::to(['contratos/index', 'user_id' => $model->user_id]),
            ['class' => 'btn btn-command']
        ) ?>
    </div>

    <!-- SINGLE CLEAN FRAME - Microsoft Fluent Container -->
    <div class="fluent-container">

        <!-- Header - Microsoft 365 Style with Affiliate Info -->
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

                <!-- Status Banner - Microsoft 365 Style -->
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

                <!-- Contract Details Grid - Microsoft Fluent Design -->
                <div class="row mt-4">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <!-- Period Card -->
                        <div class="fluent-section">
                            <div class="section-header">
                                <i class="fas fa-calendar-alt section-icon"></i>
                                <h3 class="section-title">Período del Contrato</h3>
                            </div>
                            <div class="section-content">
                                <div class="date-range">
                                    <div class="date-box">
                                        <span class="date-label">INICIO</span>
                                        <span class="date-value"><?= Yii::$app->formatter->asDate($selectedContrato->fecha_ini, 'php:d/m/Y') ?></span>
                                    </div>
                                    <div class="date-arrow">
                                        <i class="fas fa-arrow-right"></i>
                                    </div>
                                    <div class="date-box">
                                        <span class="date-label">FIN</span>
                                        <span class="date-value"><?= $selectedContrato->fecha_ven ? Yii::$app->formatter->asDate($selectedContrato->fecha_ven, 'php:d/m/Y') : 'INDEFINIDO' ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Plan Card -->
                        <div class="fluent-section">
                            <div class="section-header">
                                <i class="fas fa-clipboard-list section-icon"></i>
                                <h3 class="section-title">Plan Contratado</h3>
                            </div>
                            <div class="section-content">
                                <div class="d-flex align-items-center">
                                    <span class="plan-name"><?= $selectedContrato->plan ? $selectedContrato->plan->nombre : 'N/A' ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">
                        <!-- Clinic Card -->
                        <div class="fluent-section">
                            <div class="section-header">
                                <i class="fas fa-hospital section-icon"></i>
                                <h3 class="section-title">Clínica Asignada</h3>
                            </div>
                            <div class="section-content">
                                <div class="d-flex align-items-center">
                                    <span class="clinic-name"><?= $selectedContrato->clinica ? $selectedContrato->clinica->nombre : 'N/A' ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Amount Card -->
                        <div class="fluent-section">
                            <div class="section-header">
                                <i class="fas fa-dollar-sign section-icon"></i>
                                <h3 class="section-title">Monto Mensual</h3>
                            </div>
                            <div class="section-content">
                                <div class="amount-fluent">
                                    <?= Yii::$app->formatter->asCurrency($selectedContrato->monto ?: 0, 'USD') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- First Payment Reminder - Microsoft Info Bar Style -->
                <?php
                $showReminder = false;
                if (!empty($cuotas)) {
                    foreach ($cuotas as $cuota) {
                        if ($cuota->numero_cuota == 1 && $cuota->estatus == 'pendiente') {
                            $showReminder = true;
                            break;
                        }
                    }
                }

                if ($showReminder):
                ?>
                    <div class="info-bar-warning">
                        <i class="fas fa-exclamation-circle"></i>
                        <div class="info-content">
                            <strong>Recordatorio:</strong> La fecha de pago debe ser igual a la fecha de inicio del contrato
                            <span class="highlight-date"><?= Yii::$app->formatter->asDate($selectedContrato->fecha_ini, 'php:d/m/Y') ?></span>
                        </div>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- Error Message - Microsoft Style -->
                <div class="error-fluent">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <div class="error-title">Error: Contrato no seleccionado</div>
                        <div class="error-message">No se ha seleccionado un contrato válido para registrar el pago.</div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Payment Form -->
            <div class="mt-5">
                <?= $this->render('_form', [
                    'model' => $model,
                    'cuotas' => $cuotas,
                    'user_id' => $model->user_id,
                    'isEditable' => true,
                ]) ?>
            </div>
        </div>
    </div>
</div>

<style>
    /* ===== MICROSOFT ENTERPRISE STANDARD STYLES ===== */
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
        font-weight: 400;
        border-radius: 2px;
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
        border-radius: 4px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    /* Header - Microsoft 365 Style */
    .fluent-header {
        background: #0078d4;
        padding: 20px 24px;
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
        width: 48px;
        height: 48px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .header-title {
        font-size: 20px;
        font-weight: 600;
        line-height: 1.3;
        margin-bottom: 4px;
    }

    .header-subtitle {
        font-size: 14px;
        font-weight: 400;
    }

    .header-badge {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
    }

    /* Content Area */
    .fluent-content {
        padding: 24px;
    }

    /* Status Banner */
    .status-banner-fluent {
        padding: 16px 24px;
        border-radius: 4px;
        margin-bottom: 24px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
        width: 48px;
        height: 48px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
    }

    .status-text {
        color: white;
        font-size: 20px;
        font-weight: 600;
        line-height: 1.2;
    }

    .status-desc {
        color: rgba(255, 255, 255, 0.9);
        font-size: 13px;
        font-weight: 400;
    }

    .contract-number-fluent {
        background: rgba(255, 255, 255, 0.2);
        padding: 8px 16px;
        border-radius: 4px;
        text-align: right;
    }

    .contract-label {
        color: rgba(255, 255, 255, 0.8);
        font-size: 11px;
        font-weight: 500;
        letter-spacing: 0.5px;
    }

    .contract-value {
        color: white;
        font-size: 18px;
        font-weight: 600;
    }

    /* Fluent Sections */
    .fluent-section {
        background: #faf9f8;
        border: 1px solid #edebe9;
        border-radius: 4px;
        margin-bottom: 16px;
        overflow: hidden;
    }

    .section-header {
        background: #f3f2f1;
        padding: 12px 16px;
        border-bottom: 1px solid #edebe9;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .section-icon {
        color: #0078d4;
        font-size: 16px;
        width: 20px;
    }

    .section-title {
        color: #323130;
        font-size: 15px;
        font-weight: 600;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .section-content {
        padding: 16px;
    }

    /* Date Range */
    .date-range {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .date-box {
        flex: 1;
        background: white;
        border: 1px solid #e1dfdd;
        border-radius: 4px;
        padding: 12px;
        text-align: center;
    }

    .date-label {
        display: block;
        color: #605e5c;
        font-size: 11px;
        font-weight: 500;
        letter-spacing: 0.3px;
        margin-bottom: 4px;
    }

    .date-value {
        display: block;
        color: #323130;
        font-size: 15px;
        font-weight: 600;
    }

    .date-arrow {
        color: #8a8886;
        font-size: 16px;
    }

    /* Plan and Clinic Names */
    .plan-name,
    .clinic-name {
        font-size: 16px;
        font-weight: 500;
        color: #323130;
    }

    /* Amount Display */
    .amount-fluent {
        font-size: 24px;
        font-weight: 600;
        color: #107c10;
        line-height: 1.2;
    }

    /* Info Bar Warning */
    .info-bar-warning {
        background: #fff4ce;
        border: 1px solid #ffd966;
        border-left: 4px solid #ffb900;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 16px;
        border-radius: 4px;
    }

    .info-bar-warning i {
        color: #ffb900;
        font-size: 20px;
    }

    .info-content {
        color: #323130;
        font-size: 13px;
    }

    .highlight-date {
        background: white;
        padding: 2px 8px;
        border-radius: 2px;
        font-weight: 600;
        margin-left: 8px;
        color: #323130;
    }

    /* Error Message */
    .error-fluent {
        background: #fef1f0;
        border: 1px solid #f3b9b4;
        border-left: 4px solid #d83b01;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 16px;
        border-radius: 4px;
    }

    .error-fluent i {
        color: #d83b01;
        font-size: 24px;
    }

    .error-title {
        color: #323130;
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 4px;
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
        }

        .date-range {
            flex-direction: column;
        }

        .date-arrow {
            transform: rotate(90deg);
        }

        .status-banner-fluent .d-flex {
            flex-direction: column;
            text-align: center;
            gap: 16px;
        }

        .status-icon-fluent {
            margin: 0 auto;
        }

        .contract-number-fluent {
            text-align: center;
        }
    }

    /* Microsoft Hover Effects */
    .fluent-section:hover {
        border-color: #0078d4;
        transition: border-color 0.2s ease;
    }

    .btn-command:active {
        background: #edebe9;
        transform: none;
    }

    /* Focus States */
    .btn-command:focus,
    .fluent-section:focus-within {
        outline: 2px solid #0078d4;
        outline-offset: 2px;
    }
</style>