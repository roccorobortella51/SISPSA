<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user app\models\User */
/* @var $resetLink string */

// Get user name safely
$userName = 'Usuario';
if (isset($user) && $user) {
    if (isset($user->userDatos) && $user->userDatos) {
        $userName = $user->userDatos->nombres;
    } elseif (isset($user->username)) {
        $userName = $user->username;
    }
}

// Ensure $resetLink is defined to avoid undefined variable errors in case the caller
// did not provide it. Use a harmless placeholder link.
if (!isset($resetLink) || !$resetLink) {
    $resetLink = '#';
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= Yii::$app->name ?> - Recuperación de Contraseña</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .container {
            max-width: 580px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.08);
        }

        .header {
            background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);
            padding: 30px 30px 25px;
            text-align: center;
        }

        .header h1 {
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            letter-spacing: 1px;
        }

        .header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            margin: 4px 0 0;
        }

        .body-content {
            padding: 40px 35px 35px;
        }

        .greeting {
            font-size: 22px;
            font-weight: 600;
            color: #1a237e;
            margin-bottom: 8px;
        }

        .message {
            color: #444444;
            font-size: 16px;
            line-height: 1.6;
            margin: 16px 0 24px;
        }

        .button-container {
            text-align: center;
            margin: 30px 0 20px;
            padding: 20px 0;
            background: #f8f9ff;
            border-radius: 12px;
            border: 2px dashed #1a237e;
        }

        .reset-button {
            display: inline-block;
            background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);
            color: #ffffff !important;
            font-size: 20px;
            font-weight: 700;
            padding: 16px 50px;
            border-radius: 50px;
            text-decoration: none;
            box-shadow: 0 6px 24px rgba(13, 71, 161, 0.4);
            transition: all 0.3s ease;
            text-align: center;
            letter-spacing: 0.5px;
        }

        .reset-button:hover {
            box-shadow: 0 8px 32px rgba(13, 71, 161, 0.5);
            transform: translateY(-2px);
        }

        .link-fallback {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 16px 20px;
            margin: 20px 0 10px;
            word-break: break-all;
            font-size: 14px;
            color: #555555;
            border: 1px solid #e9ecef;
        }

        .link-fallback a {
            color: #1a237e;
            text-decoration: underline;
            font-weight: 500;
        }

        .link-fallback .label {
            font-weight: 600;
            color: #333333;
            display: block;
            margin-bottom: 6px;
            font-size: 15px;
        }

        .security-box {
            background: #fff3cd;
            border-radius: 8px;
            padding: 14px 18px;
            margin: 20px 0 10px;
            border-left: 4px solid #ffc107;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .security-box .icon {
            font-size: 22px;
            color: #856404;
            flex-shrink: 0;
        }

        .security-box .text {
            font-size: 14px;
            color: #856404;
            font-weight: 500;
        }

        .security-box .text strong {
            font-weight: 700;
        }

        .footer {
            padding: 20px 35px 30px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            background: #fafafa;
        }

        .footer p {
            color: #888888;
            font-size: 13px;
            margin: 4px 0;
            line-height: 1.5;
        }

        .footer .brand {
            color: #1a237e;
            font-weight: 600;
        }

        .divider {
            border: none;
            height: 1px;
            background: linear-gradient(to right, transparent, #e9ecef, transparent);
            margin: 20px 0;
        }

        @media (max-width: 600px) {
            .container {
                margin: 16px;
                border-radius: 8px;
            }

            .header {
                padding: 24px 20px 20px;
            }

            .header h1 {
                font-size: 24px;
            }

            .body-content {
                padding: 28px 20px 24px;
            }

            .greeting {
                font-size: 20px;
            }

            .reset-button {
                font-size: 17px;
                padding: 14px 30px;
                display: block;
            }

            .button-container {
                padding: 15px;
            }

            .footer {
                padding: 16px 20px 20px;
            }

            .security-box {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; min-height: 100vh;">
        <tr>
            <td align="center" valign="middle">
                <div class="container">
                    <!-- Header -->
                    <div class="header">
                        <h1><?= Html::encode(Yii::$app->name) ?></h1>
                        <p>Sistema Integral de Salud Programado</p>
                    </div>

                    <!-- Body -->
                    <div class="body-content">
                        <div class="greeting">
                            Hola, <strong><?= Html::encode($userName) ?></strong>
                        </div>

                        <p class="message">
                            Hemos recibido una solicitud para restablecer la contraseña de su cuenta en <?= Html::encode(Yii::$app->name) ?>.
                            Si usted no realizó esta solicitud, puede ignorar este correo y su contraseña permanecerá sin cambios.
                        </p>

                        <div class="button-container">
                            <a href="<?= Html::encode($resetLink) ?>" class="reset-button" target="_blank">
                                🔐 Restablecer Contraseña
                            </a>
                        </div>

                        <div class="link-fallback">
                            <span class="label">🔗 Si el botón no funciona, copie y pegue este enlace en su navegador:</span>
                            <a href="<?= Html::encode($resetLink) ?>"><?= Html::encode($resetLink) ?></a>
                        </div>

                        <hr class="divider">

                        <div class="security-box">
                            <span class="icon">🔒</span>
                            <span class="text">
                                <strong>Este enlace expirará en 1 hora</strong> por razones de seguridad.
                                Si no solicita el restablecimiento de su contraseña, ignore este mensaje.
                            </span>
                        </div>

                        <div style="text-align: center; margin: 15px 0 5px;">
                            <span style="background: #e8f5e9; color: #2e7d32; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 500;">
                                🔒 Conexión segura
                            </span>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="footer">
                        <p>Este mensaje ha sido enviado automáticamente desde <span class="brand"><?= Html::encode(Yii::$app->name) ?></span></p>
                        <p>Si necesita asistencia, por favor contacte a nuestro equipo de soporte.</p>
                        <p style="margin-top: 10px; font-size: 12px; color: #aaaaaa;">
                            &copy; <?= date('Y') ?> <?= Html::encode(Yii::$app->name) ?> - Todos los derechos reservados
                        </p>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>

</html>