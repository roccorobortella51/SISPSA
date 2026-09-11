<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PasswordResetRequestForm */
/* @var $form yii\bootstrap4\ActiveForm */
/* @var $emailSent boolean */
/* @var $errorMessage string */

$this->title = 'Recuperar Contraseña';
$this->params['breadcrumbs'][] = $this->title;

// Ensure variables are set
if (!isset($emailSent)) {
    $emailSent = false;
}
if (!isset($errorMessage)) {
    $errorMessage = '';
}
?>

<div class="login-container">
    <div class="login-box">
        <div class="login-logo text-center mb-4">
            <div class="logo-icon mb-3">
                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px; box-shadow: 0 4px 20px rgba(0,120,212,0.3);">
                    <!-- Animated Red Heart -->
                    <i class="fas fa-heart" style="font-size: 36px; color: #ff0000; animation: heartBeat 1.2s ease-in-out infinite;"></i>
                </div>
            </div>
            <h2 class="font-weight-bold" style="color: #1a237e;">SISPSA</h2>
            <p class="text-muted" style="font-size: 14px;">Sistema Integral de Salud Programado</p>
        </div>

        <div class="card shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-body p-4">

                <!-- ============================================ -->
                <!-- SUCCESS MESSAGE (shown when email is sent) -->
                <!-- ============================================ -->
                <?php if ($emailSent): ?>
                    <div id="success-container" style="text-align: center; padding: 20px 0;">
                        <div class="alert alert-success text-center" role="alert" style="border-radius: 8px; padding: 25px 20px;">
                            <i class="fas fa-check-circle fa-4x mb-3" style="color: #28a745;"></i>
                            <h4 style="color: #155724; font-weight: 600; margin-bottom: 10px;">¡Enlace enviado exitosamente!</h4>
                            <p style="font-size: 16px; margin-bottom: 5px;">
                                Se ha enviado un enlace de recuperación a su correo electrónico.
                            </p>
                            <!-- FIXED: Changed username to email -->
                            <p style="font-size: 15px; color: #155724; font-weight: 500;">
                                <i class="fas fa-envelope mr-2"></i>
                                <strong><?= Html::encode($model->email) ?></strong>
                            </p>
                            <div class="alert alert-info mt-3" style="border-radius: 8px; background: #d1ecf1; border-color: #bee5eb; color: #0c5460; font-size: 14px; text-align: left;">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Por favor revise:</strong>
                                <ul style="margin: 8px 0 0 20px; padding: 0;">
                                    <li>Su bandeja de entrada principal</li>
                                    <li>La carpeta de <strong>correo no deseado (SPAM)</strong></li>
                                    <li>La carpeta de <strong>promociones</strong> (si usa Gmail)</li>
                                </ul>
                            </div>
                            <div class="mt-4">
                                <?= Html::a(
                                    '<i class="fas fa-sign-in-alt mr-2"></i> Volver al inicio de sesión',
                                    ['site/login'],
                                    ['class' => 'btn btn-primary btn-lg', 'style' => 'border-radius: 8px; font-weight: 600; font-size: 16px; padding: 12px 40px;']
                                ) ?>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- ERROR MESSAGE (shown when email fails) -->
                    <!-- ============================================ -->
                <?php elseif (!empty($errorMessage)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 8px;">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?= Html::encode($errorMessage) ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <!-- ============================================ -->
                    <!-- FORM (shown when no email has been sent) -->
                    <!-- ============================================ -->
                <?php else: ?>

                    <!-- Welcome Section -->
                    <div class="welcome-section text-center mb-4">
                        <h4 class="card-title" style="color: #1a237e; font-weight: 600; display: block; margin-bottom: 2px;">¿Olvidó su contraseña?</h4>
                        <p class="text-muted" style="font-size: 14px; display: block; margin: 0;">Ingrese su correo para recibir el enlace de recuperación</p>
                    </div>

                    <hr>

                    <!-- Form -->
                    <div id="reset-form">
                        <?php $form = ActiveForm::begin([
                            'id' => 'request-password-reset-form',
                            'class' => 'needs-validation',
                            'options' => [
                                'onsubmit' => 'return showLoading();',
                            ],
                        ]); ?>

                        <div class="info-box mb-3" style="background: #e3f2fd; border-radius: 8px; padding: 12px 16px; border-left: 4px solid #0078d4;">
                            <p style="font-size: 14px; color: #1a237e; margin: 0;">
                                <i class="fas fa-info-circle mr-2"></i>
                                Le enviaremos un enlace seguro para restablecer su contraseña.
                            </p>
                        </div>

                        <?= $form->field($model, 'email', [
                            'options' => ['class' => 'form-group'],
                            'inputTemplate' => '
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="background: #f0f4f8; border-right: none; border-radius: 8px 0 0 8px;">
                                            <i class="fas fa-envelope" style="color: #0078d4;"></i>
                                        </span>
                                    </div>
                                    {input}
                                </div>',
                            'template' => '{beginWrapper}{input}{error}{endWrapper}',
                            'wrapperOptions' => ['class' => 'mb-3']
                        ])
                            ->label(false)
                            ->textInput([
                                'placeholder' => 'Correo electrónico',
                                'class' => 'form-control form-control-lg',
                                'style' => 'border-radius: 0 8px 8px 0; border-left: none; background: #f0f4f8; font-size: 14px;',
                                'autofocus' => true,
                                'id' => 'email-field',
                            ]) ?>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <?= Html::a(
                                '<i class="fas fa-arrow-left mr-2"></i> Volver al inicio de sesión',
                                ['site/login'],
                                ['class' => 'btn btn-outline-secondary', 'style' => 'border-radius: 8px; font-weight: 500;']
                            ) ?>
                        </div>

                        <?= Html::submitButton(
                            '<i class="fas fa-paper-plane mr-2"></i> Enviar enlace de recuperación',
                            [
                                'class' => 'btn btn-primary btn-block btn-lg',
                                'style' => 'background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%); border: none; border-radius: 8px; font-weight: 600; font-size: 16px; padding: 12px; box-shadow: 0 4px 12px rgba(0,120,212,0.3);',
                                'id' => 'submit-btn',
                            ]
                        ) ?>

                        <?php ActiveForm::end(); ?>
                    </div>

                    <!-- Loading/Progress Indicator -->
                    <div id="loading-container" style="display: none; text-align: center; padding: 20px 0;">
                        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                            <span class="sr-only">Enviando...</span>
                        </div>
                        <div class="progress mt-3" style="height: 8px;">
                            <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated"
                                role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <p class="mt-2" style="color: #1a237e; font-weight: 500;">
                            <i class="fas fa-paper-plane mr-2"></i> Enviando enlace de recuperación...
                        </p>
                        <small class="text-muted">Por favor espere, esto puede tomar unos segundos</small>
                    </div>

                <?php endif; ?>

                <div class="mt-3 text-center">
                    <small class="text-muted" style="font-size: 12px;">
                        <i class="fas fa-shield-alt mr-1" style="color: #28a745;"></i>
                        Conexión segura | SISPSA v2.0
                    </small>
                </div>
            </div>
        </div>

        <div class="text-center mt-3">
            <small class="text-muted" style="font-size: 11px;">
                &copy; <?= date('Y') ?> SISPSA - Sistema Integral de Salud Programado
            </small>
        </div>
    </div>
</div>

<script>
    function showLoading() {
        // Hide the form
        document.getElementById('reset-form').style.display = 'none';
        // Show loading container
        document.getElementById('loading-container').style.display = 'block';

        // Animate progress bar
        var progress = 0;
        var interval = setInterval(function() {
            progress += Math.floor(Math.random() * 10) + 5;
            if (progress > 95) {
                progress = 95;
                clearInterval(interval);
            }
            var progressBar = document.getElementById('progress-bar');
            if (progressBar) {
                progressBar.style.width = progress + '%';
                progressBar.setAttribute('aria-valuenow', progress);
            }
        }, 200);

        return true;
    }
</script>

<style>
    .login-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 50%, #90caf9 100%);
        padding: 20px;
    }

    .login-box {
        width: 100%;
        max-width: 420px;
    }

    .card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 8px 40px rgba(0, 0, 0, 0.08);
        border: none;
    }

    .form-control:focus {
        border-color: #0078d4;
        box-shadow: 0 0 0 3px rgba(0, 120, 212, 0.15);
    }

    .input-group-text {
        background: #f0f4f8;
        border: 1px solid #d4d9e2;
    }

    /* ============================================ */
    /* ANIMATED RED HEART - Heartbeat Effect */
    /* ============================================ */
    @keyframes heartBeat {

        0%,
        100% {
            transform: scale(1);
        }

        14% {
            transform: scale(1.3);
        }

        28% {
            transform: scale(1);
        }

        42% {
            transform: scale(1.3);
        }

        70% {
            transform: scale(1);
        }
    }

    /* ============================================ */
    /* LOGO PULSE ANIMATION */
    /* ============================================ */
    .logo-icon {
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            box-shadow: 0 4px 20px rgba(0, 120, 212, 0.3);
        }

        50% {
            box-shadow: 0 4px 40px rgba(0, 120, 212, 0.5);
        }
    }

    /* ============================================ */
    /* WELCOME SECTION - Stack vertically and center */
    /* ============================================ */
    .welcome-section {
        text-align: center !important;
    }

    .welcome-section h4,
    .welcome-section p {
        display: block !important;
        width: 100% !important;
        clear: both !important;
        text-align: center !important;
    }

    /* ============================================ */
    /* PROGRESS BAR */
    /* ============================================ */
    .progress {
        background-color: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-bar {
        background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%);
        transition: width 0.3s ease;
    }

    /* ============================================ */
    /* ALERTS */
    /* ============================================ */
    .alert {
        border-radius: 8px;
    }

    .alert-info ul {
        margin-bottom: 0;
    }

    .alert-info ul li {
        margin-bottom: 2px;
    }

    /* ============================================ */
    /* RESPONSIVE */
    /* ============================================ */
    @media (max-width: 480px) {
        .login-container {
            padding: 10px;
        }

        .login-box {
            max-width: 100%;
        }

        .card-body {
            padding: 20px !important;
        }
    }
</style>