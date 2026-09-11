<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

// Clear any flash messages that might be showing incorrectly
Yii::$app->session->removeFlash('success');
Yii::$app->session->removeFlash('error');
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
                <?php $form = ActiveForm::begin([
                    'id' => 'login-form',
                    'class' => 'needs-validation',
                    'enableClientValidation' => true,
                    'enableAjaxValidation' => false,
                ]); ?>

                <!-- Centered Welcome Section -->
                <div class="welcome-section text-center mb-4">
                    <h4 class="card-title" style="color: #1a237e; font-weight: 600; display: block; margin-bottom: 2px;">Bienvenido</h4>
                    <p class="text-muted" style="font-size: 14px; display: block; margin: 0;">Ingrese sus credenciales para continuar</p>
                </div>

                <div class="form-body">
                    <?= $form->field($model, 'username', [
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
                            'style' => 'border-radius: 0 8px 8px 0; border-left: none; background: #f0f4f8; font-size: 14px;'
                        ]) ?>

                    <?= $form->field($model, 'password', [
                        'options' => ['class' => 'form-group'],
                        'inputTemplate' => '
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" style="background: #f0f4f8; border-right: none; border-radius: 8px 0 0 8px;">
                                        <i class="fas fa-lock" style="color: #0078d4;"></i>
                                    </span>
                                </div>
                                {input}
                                <div class="input-group-append">
                                    <span class="input-group-text" style="background: #f0f4f8; border-left: none; border-radius: 0 8px 8px 0; cursor: pointer;" onclick="togglePassword()">
                                        <i class="fas fa-eye" id="togglePasswordIcon" style="color: #6c757d;"></i>
                                    </span>
                                </div>
                            </div>',
                        'template' => '{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'mb-3']
                    ])
                        ->label(false)
                        ->passwordInput([
                            'placeholder' => 'Contraseña',
                            'class' => 'form-control form-control-lg',
                            'style' => 'border-radius: 0; border-left: none; border-right: none; background: #f0f4f8; font-size: 14px;',
                            'id' => 'password-field'
                        ]) ?>

                    <!-- ============================================ -->
                    <!-- ONE CHECKBOX ONLY - PURE HTML (NO Yii2 FIELD) -->
                    <!-- ============================================ -->
                    <div class="form-options">
                        <div class="checkbox-container">
                            <label class="checkbox-label">
                                <!-- REMOVED: <?= $model->rememberMe ? 'checked' : '' ?> -->
                                <!-- NOW: unchecked by default -->
                                <input type="checkbox"
                                    id="rememberMe"
                                    name="rememberMe"
                                    value="1"
                                    class="checkbox-input">
                                <span class="checkmark"></span>
                                <span class="checkbox-text"> Recordarme</span>
                            </label>
                        </div>
                        <a href="<?= Yii::$app->urlManager->createUrl(['site/request-password-reset']) ?>" class="forgot-link">
                            <i class="fas fa-key"></i> ¿Olvidó su contraseña?
                        </a>
                    </div>

                    <!-- ============================================ -->
                    <!-- ANIMATED PASSWORD GIF -->
                    <!-- ============================================ -->
                    <div class="password-animation-container text-center mb-3">
                        <div class="password-gif-wrapper">
                            <img src="<?= Yii::$app->request->baseUrl ?>/img/password.gif"
                                alt="Recuperación de Contraseña"
                                class="password-gif">
                        </div>
                    </div>

                    <?= Html::submitButton(
                        '<i class="fas fa-sign-in-alt mr-2"></i> Ingresar',
                        [
                            'class' => 'btn btn-primary btn-block btn-lg',
                            'style' => 'background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%); border: none; border-radius: 8px; font-weight: 600; font-size: 16px; padding: 12px; box-shadow: 0 4px 12px rgba(0,120,212,0.3); transition: all 0.3s ease;',
                            'onmouseover' => 'this.style.transform="translateY(-2px)"; this.style.boxShadow="0 6px 20px rgba(0,120,212,0.4)";',
                            'onmouseout' => 'this.style.transform="translateY(0)"; this.style.boxShadow="0 4px 12px rgba(0,120,212,0.3)";',
                        ]
                    ) ?>

                    <?php ActiveForm::end(); ?>
                </div>

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
    function togglePassword() {
        const passwordField = document.getElementById('password-field');
        const icon = document.getElementById('togglePasswordIcon');
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
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
    /* ONE CHECKBOX - 100% WORKING */
    /* ============================================ */
    .checkbox-container {
        display: flex;
        align-items: center;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #495057;
        cursor: pointer;
        user-select: none;
        position: relative;
        padding-left: 26px;
        margin-bottom: 0;
        line-height: 1.5;
    }

    .checkbox-input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
        margin: 0;
        padding: 0;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
    }

    .checkmark {
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        background-color: #ffffff;
        border: 2px solid #d4d9e2;
        border-radius: 4px;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .checkbox-label:hover .checkmark {
        border-color: #0078d4;
    }

    .checkbox-input:checked~.checkmark {
        background-color: #0078d4;
        border-color: #0078d4;
    }

    .checkbox-input:checked~.checkmark::after {
        content: '';
        position: absolute;
        left: 5px;
        top: 2px;
        width: 6px;
        height: 10px;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }

    .checkbox-text {
        display: flex;
        align-items: center;
        gap: 2px;
    }

    /* ============================================ */
    /* HIDE ANY YII2 REMEMBERME FIELDS */
    /* ============================================ */
    .field-loginform-rememberme,
    input[name="LoginForm[rememberMe]"] {
        display: none !important;
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
    /* PASSWORD GIF */
    /* ============================================ */
    .password-animation-container {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 8px 0;
        border-top: 1px solid rgba(0, 0, 0, 0.06);
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        margin: 0 0 16px 0;
    }

    .password-gif-wrapper {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 4px 10px;
        border-radius: 20px;
        transition: all 0.3s ease;
    }

    .password-gif-wrapper:hover {
        background: rgba(0, 120, 212, 0.05);
    }

    .password-gif {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        box-shadow: 0 2px 12px rgba(0, 120, 212, 0.15);
        animation: floatGif 3s ease-in-out infinite;
        transition: transform 0.3s ease;
        border: 2px solid rgba(0, 120, 212, 0.1);
        flex-shrink: 0;
    }

    .password-gif:hover {
        transform: scale(1.15) rotate(-5deg);
        border-color: rgba(0, 120, 212, 0.3);
    }

    .password-gif-label {
        font-size: 12px;
        color: #6c757d;
        font-weight: 500;
        white-space: nowrap;
        transition: color 0.3s ease;
    }

    .password-gif-wrapper:hover .password-gif-label {
        color: #0078d4;
    }

    @keyframes floatGif {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-4px);
        }
    }

    /* Force the welcome section to stack vertically and center */
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

    /* Form options - flex layout */
    .form-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        flex-wrap: wrap;
        gap: 8px;
    }

    .forgot-link {
        font-size: 13px;
        color: #0078d4;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .forgot-link:hover {
        color: #005a9e;
        text-decoration: underline;
    }

    /* Responsive */
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

        .password-gif {
            width: 32px;
            height: 32px;
        }

        .password-gif-label {
            font-size: 10px;
        }

        .password-gif-wrapper {
            gap: 8px;
            padding: 4px 8px;
        }

        .form-options {
            flex-direction: column;
            gap: 8px;
            align-items: flex-start;
        }

        .checkbox-label {
            font-size: 13px;
            padding-left: 24px;
        }

        .checkmark {
            width: 16px;
            height: 16px;
        }
    }

    @media (max-width: 360px) {
        .password-gif-label {
            display: none;
        }
    }
</style>