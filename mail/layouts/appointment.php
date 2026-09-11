<?php
// app/mail/layouts/appointment.php

/* @var $this \yii\web\View */
/* @var $content string */
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->title ?? 'SISPSA - Notificación' ?></title>
    <style type="text/css">
        body,
        table,
        td,
        p,
        a,
        div,
        span {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #f0f4f8;
        }

        table {
            border-collapse: collapse;
        }

        td {
            border-collapse: collapse;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.08);
        }

        /* ===== HEADER WITH SISPSA COLORS ===== */
        .header {
            background: linear-gradient(135deg, #011F3E 0%, #011F3E 60%, #003366 100%);
            padding: 35px 40px 30px;
            text-align: center;
            border-bottom: 4px solid #009EFB;
        }

        .header h1 {
            color: #ffffff;
            font-size: 32px;
            font-weight: 700;
            margin: 0;
            letter-spacing: 1px;
        }

        .header .subtitle {
            color: rgba(255, 255, 255, 0.85);
            font-size: 15px;
            margin: 6px 0 0;
            font-weight: 300;
        }

        .header .divider {
            width: 50px;
            height: 3px;
            background: #009EFB;
            margin: 12px auto 0;
            border-radius: 3px;
        }

        /* ===== BODY ===== */
        .body-content {
            padding: 30px 35px 25px;
            background: #ffffff;
        }

        /* ===== SISPSA BLUE ACCENTS ===== */
        .sispsa-blue {
            color: #009EFB;
        }

        .sispsa-dark {
            color: #011F3E;
        }

        .sispsa-border {
            border-left: 4px solid #009EFB;
        }

        .sispsa-badge {
            background: #009EFB;
            color: #ffffff;
            padding: 2px 14px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        /* ===== CARDS ===== */
        .card {
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e8ecf1;
            margin-bottom: 24px;
            overflow: hidden;
        }

        .card-header {
            background: #eef3f7;
            padding: 14px 22px;
            border-bottom: 1px solid #e8ecf1;
            border-left: 4px solid #009EFB;
        }

        .card-header span {
            font-size: 15px;
            font-weight: 600;
            color: #011F3E;
        }

        .card-body {
            padding: 18px 22px;
        }

        /* ===== INFO ROWS ===== */
        .info-row {
            padding: 5px 0;
            display: table;
            width: 100%;
        }

        .info-label {
            font-size: 13px;
            color: #718096;
            font-weight: 500;
            letter-spacing: 0.3px;
            display: table-cell;
            width: 30%;
            padding-right: 10px;
        }

        .info-value {
            font-size: 15px;
            color: #2c3e50;
            font-weight: 500;
            display: table-cell;
            width: 70%;
        }

        .info-value .badge {
            display: inline-block;
            background: #e8f4fd;
            color: #009EFB;
            padding: 2px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .info-value .badge-dark {
            display: inline-block;
            background: #011F3E;
            color: #ffffff;
            padding: 2px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        /* ===== SERVICES TABLE ===== */
        .services-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin-top: 4px;
        }

        .services-table th {
            background: #011F3E;
            padding: 10px 14px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border-bottom: 2px solid #009EFB;
        }

        .services-table td {
            padding: 10px 14px;
            border-bottom: 1px solid #f0f0f0;
            color: #2c3e50;
            font-size: 14px;
        }

        .services-table tr:last-child td {
            border-bottom: none;
        }

        .services-table .total-row {
            background: #f8fafc;
            font-weight: 600;
        }

        .services-table .total-row td {
            padding: 12px 14px;
            border-top: 2px solid #009EFB;
            font-size: 15px;
        }

        .services-table .total-row .total-label {
            color: #011F3E;
        }

        .services-table .total-row .total-amount {
            color: #009EFB;
            font-size: 17px;
        }

        .service-name {
            font-weight: 500;
            color: #2c3e50;
        }

        .service-area {
            font-size: 12px;
            color: #718096;
        }

        .service-price {
            color: #009EFB;
            font-weight: 600;
        }

        /* ===== WELCOME NOTE ===== */
        .welcome-note {
            background: linear-gradient(135deg, #f0f8ff 0%, #e6f4ff 100%);
            border-left: 4px solid #009EFB;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 24px;
        }

        .welcome-note .note-title {
            font-size: 14px;
            font-weight: 600;
            color: #011F3E;
            margin: 0 0 4px 0;
        }

        .welcome-note .note-text {
            font-size: 13px;
            color: #4a5568;
            line-height: 1.7;
            margin: 0;
        }

        /* ===== IMPORTANT NOTES ===== */
        .note-box {
            background: #fef9e7;
            border-left: 4px solid #f39c12;
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 24px;
        }

        .note-box .note-title {
            font-size: 14px;
            font-weight: 600;
            color: #856404;
            margin: 0 0 6px 0;
        }

        .note-box ul {
            margin: 0;
            padding-left: 18px;
            font-size: 13px;
            color: #4a5568;
            line-height: 1.8;
        }

        /* ===== FOOTER ===== */
        .footer {
            background: #011F3E;
            padding: 22px 35px;
            text-align: center;
            border-top: 4px solid #009EFB;
        }

        .footer .company-name {
            font-size: 14px;
            font-weight: 600;
            color: #ffffff;
            margin: 0 0 4px 0;
        }

        .footer .legal {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.7);
            margin: 0 0 4px 0;
            line-height: 1.5;
        }

        .footer .disclaimer {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.5);
            margin: 0;
            line-height: 1.5;
        }

        .footer .footer-divider {
            width: 40px;
            height: 2px;
            background: #009EFB;
            margin: 10px auto 0;
            border-radius: 2px;
        }

        /* ===== RESPONSIVE ===== */
        @media only screen and (max-width: 480px) {
            .header {
                padding: 25px 20px;
            }

            .header h1 {
                font-size: 26px;
            }

            .body-content {
                padding: 20px;
            }

            .card-body {
                padding: 14px 16px;
            }

            .info-label {
                width: 35%;
                font-size: 12px;
            }

            .info-value {
                font-size: 14px;
            }

            .services-table th,
            .services-table td {
                padding: 8px 10px;
                font-size: 12px;
            }

            .footer {
                padding: 16px 20px;
            }
        }
    </style>
</head>

<body style="margin: 0; padding: 20px 10px; background-color: #f0f4f8;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f0f4f8; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0" class="container" style="max-width: 600px; width: 100%; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 30px rgba(0,0,0,0.08);">

                    <!-- ===== HEADER WITH SISPSA COLORS ===== -->
                    <tr>
                        <td class="header" style="background: linear-gradient(135deg, #011F3E 0%, #011F3E 60%, #003366 100%); padding: 35px 40px 30px; text-align: center; border-bottom: 4px solid #009EFB;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center">
                                        <div style="display: inline-block; background: rgba(0,158,251,0.2); padding: 4px 16px; border-radius: 20px; margin-bottom: 8px; border: 1px solid rgba(0,158,251,0.3);">
                                            <span style="color: #009EFB; font-size: 11px; font-weight: 600; letter-spacing: 1.2px; text-transform: uppercase;">Sistema de Salud</span>
                                        </div>
                                        <h1 style="color: #ffffff; font-size: 32px; font-weight: 700; margin: 0; letter-spacing: 1px;">SISPSA</h1>
                                        <p style="color: rgba(255,255,255,0.85); font-size: 15px; margin: 6px 0 0; font-weight: 300;">Confirmación de Cita Médica</p>
                                        <div style="width: 50px; height: 3px; background: #009EFB; margin: 12px auto 0; border-radius: 3px;"></div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- ===== BODY ===== -->
                    <tr>
                        <td class="body-content" style="padding: 30px 35px 25px; background: #ffffff;">
                            <?= $content ?? '' ?>
                        </td>
                    </tr>

                    <!-- ===== DOCTOR SECTION ===== -->
                    <tr>
                        <td style="padding: 0 35px 20px; text-align: center; background: #ffffff;">
                            <div style="font-size: 48px; margin-bottom: 4px;">👨‍⚕️</div>
                            <p style="font-size: 13px; color: #009EFB; margin: 0; font-style: italic; font-weight: 500;">Siempre a tu servicio</p>
                        </td>
                    </tr>

                    <!-- ===== FOOTER WITH SISPSA COLORS ===== -->
                    <tr>
                        <td class="footer" style="background: #011F3E; padding: 22px 35px; text-align: center; border-top: 4px solid #009EFB;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center">
                                        <p style="font-size: 14px; font-weight: 600; color: #ffffff; margin: 0 0 4px 0;">SISPSA - Sistema de Salud Integral</p>
                                        <p style="font-size: 11px; color: rgba(255,255,255,0.7); margin: 0 0 4px 0; line-height: 1.5;">
                                            Inscrita en la Superintendencia de la Actividad Aseguradora bajo el No. MP000013
                                        </p>
                                        <p style="font-size: 11px; color: rgba(255,255,255,0.5); margin: 0; line-height: 1.5;">
                                            Este es un mensaje automático. Por favor, no responder a este correo.
                                            <br>
                                            © <?= date('Y') ?> SISPSA. Todos los derechos reservados.
                                        </p>
                                        <div style="width: 40px; height: 2px; background: #009EFB; margin: 10px auto 0; border-radius: 2px;"></div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>

</html>