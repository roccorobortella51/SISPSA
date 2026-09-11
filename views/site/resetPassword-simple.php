<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ResetPasswordForm */

$this->title = 'Restablecer Contraseña';

// Clear any flash messages
Yii::$app->session->removeFlash('success');
Yii::$app->session->removeFlash('error');
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::encode($this->title) ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 50%, #90caf9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .card {
            max-width: 420px;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.08);
            background: rgba(255, 255, 255, 0.95);
        }

        .card-header {
            background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 30px 20px;
            text-align: center;
        }

        .card-header h2 {
            margin: 0;
            font-weight: 700;
        }

        .card-body {
            padding: 30px;
        }

        .form-control {
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #0078d4;
            box-shadow: 0 0 0 3px rgba(0, 120, 212, 0.15);
        }

        .btn-primary {
            background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%);
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            font-size: 16px;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #005a9e 0%, #003d7a 100%);
        }

        .alert {
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="card-header">
            <div class="rounded-circle bg-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                <i class="fas fa-heartbeat" style="font-size: 28px; color: #1a237e;"></i>
            </div>
            <h2>SISPSA</h2>
            <p style="opacity: 0.8; margin: 0;">Restablecer Contraseña</p>
        </div>
        <div class="card-body">
            <?php if (Yii::$app->session->hasFlash('success')): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= Yii::$app->session->getFlash('success') ?>
                </div>
            <?php endif; ?>

            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?= Yii::$app->session->getFlash('error') ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

                <div class="form-group">
                    <label>Nueva Contraseña</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        </div>
                        <input type="password" class="form-control" name="ResetPasswordForm[password]" placeholder="Nueva Contraseña" required minlength="8">
                    </div>
                </div>

                <div class="form-group">
                    <label>Confirmar Contraseña</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-check"></i></span>
                        </div>
                        <input type="password" class="form-control" name="ResetPasswordForm[password_repeat]" placeholder="Confirmar Contraseña" required>
                    </div>
                </div>

                <div class="alert alert-warning" style="font-size: 13px; padding: 10px 14px;">
                    <i class="fas fa-shield-alt mr-2"></i>
                    <strong>Requisitos:</strong> Mínimo 8 caracteres, incluyendo mayúscula, minúscula y número.
                </div>

                <a href="<?= Yii::$app->urlManager->createUrl(['site/login']) ?>" class="btn btn-outline-secondary btn-block mb-3">
                    <i class="fas fa-arrow-left mr-2"></i> Volver al inicio de sesión
                </a>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save mr-2"></i> Restablecer Contraseña
                </button>
            </form>
        </div>
    </div>
</body>

</html>