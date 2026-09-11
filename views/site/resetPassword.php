<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ResetPasswordForm */
/* @var $form yii\bootstrap4\ActiveForm */
/* @var $success boolean */
/* @var $message string */

$this->title = 'Restablecer Contraseña';
$this->params['breadcrumbs'][] = $this->title;

// Ensure variables are set with default values
if (!isset($success)) {
    $success = false;
}
if (!isset($message)) {
    $message = '';
}

// If password was successfully reset, show success message and login link
if ($success) {
    // Clear any flash messages
    Yii::$app->session->removeFlash('success');
    Yii::$app->session->removeFlash('error');
?>
    <div class="login-container">
        <div class="login-box">
            <div class="login-logo text-center mb-4">
                <div class="logo-icon mb-3">
                    <div class="rounded-circle bg-success d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px; box-shadow: 0 4px 20px rgba(40,167,69,0.3);">
                        <i class="fas fa-check" style="font-size: 36px; color: white;"></i>
                    </div>
                </div>
                <h2 class="font-weight-bold" style="color: #1a237e;">SISPSA</h2>
                <p class="text-muted" style="font-size: 14px;">Contraseña Restablecida</p>
            </div>

            <div class="card shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
                <div class="card-body p-4 text-center">
                    <div class="alert alert-success" style="font-size: 16px; border-radius: 8px; padding: 20px;">
                        <i class="fas fa-check-circle fa-3x mb-3" style="color: #28a745;"></i>
                        <p style="font-size: 18px; font-weight: 500; margin: 0;">
                            <?= Html::encode($message) ?>
                        </p>
                    </div>

                    <div style="margin-top: 10px; color: #6c757d; font-size: 14px;">
                        <i class="fas fa-shield-alt mr-1" style="color: #28a745;"></i>
                        Conexión segura | SISPSA v2.0
                    </div>

                    <hr style="margin: 20px 0;">

                    <div class="d-flex justify-content-center">
                        <?= Html::a(
                            '<i class="fas fa-sign-in-alt mr-2"></i> Iniciar Sesión',
                            ['site/login'],
                            [
                                'class' => 'btn btn-primary btn-lg',
                                'style' => 'background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%); border: none; border-radius: 8px; font-weight: 600; font-size: 16px; padding: 12px 40px; box-shadow: 0 4px 12px rgba(0,120,212,0.3);'
                            ]
                        ) ?>
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

        .logo-icon {
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                box-shadow: 0 4px 20px rgba(40, 167, 69, 0.3);
            }

            50% {
                box-shadow: 0 4px 40px rgba(40, 167, 69, 0.5);
            }
        }

        .alert-success {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            padding: 12px 40px;
            box-shadow: 0 4px 12px rgba(0, 120, 212, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #005a9e 0%, #003d7a 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 120, 212, 0.4);
        }

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
<?php
    return;
}
?>

<!-- ============================================ -->
<!-- Reset Password Form (shown when not successful) -->
<!-- ============================================ -->
<div class="login-container">
    <div class="login-box">
        <div class="login-logo text-center mb-4">
            <div class="logo-icon mb-3">
                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px; box-shadow: 0 4px 20px rgba(0,120,212,0.3);">
                    <i class="fas fa-heartbeat" style="font-size: 36px; color: white;"></i>
                </div>
            </div>
            <h2 class="font-weight-bold" style="color: #1a237e;">SISPSA</h2>
            <p class="text-muted" style="font-size: 14px;">Restablecer Contraseña</p>
        </div>

        <div class="card shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-lock" style="color: #0078d4; font-size: 24px; margin-right: 12px;"></i>
                    <div>
                        <h4 class="card-title mb-0" style="color: #1a237e; font-weight: 600;">Ingrese su nueva contraseña</h4>
                        <small class="text-muted">La contraseña debe tener al menos 8 caracteres</small>
                    </div>
                </div>

                <hr>

                <?php if (Yii::$app->session->hasFlash('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?= Yii::$app->session->getFlash('success') ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if (Yii::$app->session->hasFlash('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?= Yii::$app->session->getFlash('error') ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php $form = ActiveForm::begin([
                    'id' => 'reset-password-form',
                    'method' => 'post',
                    'enableClientValidation' => true,
                    'enableAjaxValidation' => false,
                ]); ?>

                <?= $form->field($model, 'password')->passwordInput([
                    'autofocus' => true,
                    'placeholder' => 'Nueva Contraseña',
                    'class' => 'form-control form-control-lg',
                    'style' => 'background: #f0f4f8; font-size: 14px; border-radius: 8px;',
                    'id' => 'resetpasswordform-password',
                ]) ?>

                <?= $form->field($model, 'password_repeat')->passwordInput([
                    'placeholder' => 'Confirmar Contraseña',
                    'class' => 'form-control form-control-lg',
                    'style' => 'background: #f0f4f8; font-size: 14px; border-radius: 8px;',
                    'id' => 'resetpasswordform-password_repeat',
                ]) ?>

                <div class="info-box mb-3" style="background: #fff3cd; border-radius: 8px; padding: 12px 16px; border-left: 4px solid #ffc107;">
                    <p style="font-size: 14px; color: #856404; margin: 0;">
                        <i class="fas fa-shield-alt mr-2"></i>
                        <strong>Requisitos de seguridad:</strong> Mínimo 8 caracteres, incluyendo mayúscula, minúscula y número.
                    </p>
                </div>

                <div id="password-errors" style="margin-top: 10px;"></div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <?= Html::a(
                        '<i class="fas fa-arrow-left mr-2"></i> Volver al inicio de sesión',
                        ['site/login'],
                        ['class' => 'btn btn-outline-secondary', 'style' => 'border-radius: 8px; font-weight: 500;']
                    ) ?>
                </div>

                <?= Html::submitButton(
                    '<i class="fas fa-save mr-2"></i> Restablecer Contraseña',
                    [
                        'class' => 'btn btn-success btn-block btn-lg',
                        'style' => 'background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); border: none; border-radius: 8px; font-weight: 600; font-size: 16px; padding: 12px; box-shadow: 0 4px 12px rgba(40,167,69,0.3); opacity: 0.5; cursor: not-allowed;',
                        'id' => 'reset-password-button',
                        'disabled' => true,
                    ]
                ) ?>

                <?php ActiveForm::end(); ?>

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
    document.addEventListener('DOMContentLoaded', function() {
        var passwordField = document.getElementById('resetpasswordform-password');
        var repeatField = document.getElementById('resetpasswordform-password_repeat');
        var submitBtn = document.getElementById('reset-password-button');
        var errorContainer = document.getElementById('password-errors');

        function validatePasswords() {
            var password = passwordField ? passwordField.value : '';
            var repeat = repeatField ? repeatField.value : '';
            var errors = [];
            var isValid = true;

            // Clear previous errors
            if (errorContainer) {
                errorContainer.innerHTML = '';
            }
            if (passwordField) {
                passwordField.style.borderColor = '';
            }
            if (repeatField) {
                repeatField.style.borderColor = '';
            }

            // Check password requirements (only if password is not empty)
            if (password.length > 0) {
                if (password.length < 8) {
                    errors.push('❌ La contraseña debe tener al menos 8 caracteres');
                    isValid = false;
                }
                if (!/[A-Z]/.test(password)) {
                    errors.push('❌ Debe contener al menos una mayúscula');
                    isValid = false;
                }
                if (!/[a-z]/.test(password)) {
                    errors.push('❌ Debe contener al menos una minúscula');
                    isValid = false;
                }
                if (!/[0-9]/.test(password)) {
                    errors.push('❌ Debe contener al menos un número');
                    isValid = false;
                }

                // Check if passwords match
                if (repeat.length > 0 && password !== repeat) {
                    errors.push('❌ Las contraseñas no coinciden');
                    isValid = false;
                    if (repeatField) {
                        repeatField.style.borderColor = '#dc3545';
                    }
                } else if (repeat.length > 0 && password === repeat && isValid) {
                    if (repeatField) {
                        repeatField.style.borderColor = '#28a745';
                    }
                }

                // Check if all requirements are met
                if (isValid && password.length >= 8 && /[A-Z]/.test(password) && /[a-z]/.test(password) && /[0-9]/.test(password)) {
                    if (passwordField) {
                        passwordField.style.borderColor = '#28a745';
                    }
                    if (repeat.length > 0 && password === repeat) {
                        // All conditions met - enable submit button
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.style.opacity = '1';
                            submitBtn.style.cursor = 'pointer';
                        }
                    } else if (repeat.length === 0) {
                        // Password is valid but repeat is empty
                        if (submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.style.opacity = '0.5';
                            submitBtn.style.cursor = 'not-allowed';
                        }
                    }
                } else {
                    // Requirements not met - disable submit button
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.style.opacity = '0.5';
                        submitBtn.style.cursor = 'not-allowed';
                    }
                }
            } else {
                // Password is empty - disable submit button
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.5';
                    submitBtn.style.cursor = 'not-allowed';
                }
            }

            // Display errors
            if (errors.length > 0 && errorContainer) {
                var errorHtml = '<div class="alert alert-danger" style="border-radius: 8px; padding: 12px 16px; font-size: 14px;">';
                errorHtml += '<i class="fas fa-exclamation-circle mr-2"></i> <strong>Por favor corrija los siguientes errores:</strong><ul style="margin: 8px 0 0 20px; padding: 0;">';
                errors.forEach(function(err) {
                    errorHtml += '<li>' + err + '</li>';
                });
                errorHtml += '</ul></div>';
                errorContainer.innerHTML = errorHtml;
            }
        }

        // Add event listeners
        if (passwordField) {
            passwordField.addEventListener('keyup', validatePasswords);
            passwordField.addEventListener('change', validatePasswords);
            passwordField.addEventListener('blur', validatePasswords);
        }

        if (repeatField) {
            repeatField.addEventListener('keyup', validatePasswords);
            repeatField.addEventListener('change', validatePasswords);
            repeatField.addEventListener('blur', validatePasswords);
        }

        // Initial validation
        validatePasswords();
    });
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

    .alert {
        border-radius: 8px;
    }

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