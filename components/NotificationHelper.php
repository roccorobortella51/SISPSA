<?php

namespace app\components;

use Yii;
use yii\helpers\Url;

class NotificationHelper
{
    public static function sendReceiptNotification($receipt, $user, $payment)
    {
        // Skip if no email
        if (empty($user->email)) {
            Yii::info("No email for user {$user->id}", 'notification');
            return false;
        }

        try {
            // Get contract and plan info
            $contract = $receipt->contract;
            $planName = $contract && $contract->plan ? $contract->plan->nombre : 'N/A';
            $clinicaName = $contract && $contract->clinica ? $contract->clinica->nombre : 'N/A';

            // Generate download link - Fix for console mode
            $downloadLink = self::generateDownloadLink($receipt->id);

            // Format data
            $fullName = trim($user->nombres . ' ' . $user->apellidos);
            $receiptNumber = $receipt->receipt_number;

            // Convert to float
            $amountUSD = (float)$payment->monto_pagado;
            $amountBs = (float)$payment->monto_usd;

            $date = date('d/m/Y', strtotime($payment->fecha_pago));
            $coverageFrom = $receipt->coverage_start_date ? date('d/m/Y', strtotime($receipt->coverage_start_date)) : 'N/A';
            $coverageTo = $receipt->coverage_end_date ? date('d/m/Y', strtotime($receipt->coverage_end_date)) : 'N/A';
            $contractNumber = $contract ? $contract->nrocontrato : ($contract ? $contract->id : 'N/A');
            $installmentNumber = $receipt->installment ? $receipt->installment->numero_cuota : 'N/A';

            // Get logo URL (absolute URL works better in Gmail)
            $logoUrl = 'https://sispsatest.com/img/sispsalogo.jpg';

            // Fallback to base64 if URL doesn't work
            $logoBase64 = '';
            $logoPath = Yii::getAlias('@webroot') . '/img/sispsalogo.jpg';
            if (file_exists($logoPath) && !file_exists($logoUrl)) {
                $logoData = file_get_contents($logoPath);
                $logoBase64 = 'data:image/jpeg;base64,' . base64_encode($logoData);
            }

            $subject = '📄 Su recibo ha sido generado - SISPSA';

            // Format numbers for display
            $amountUSDFormatted = number_format($amountUSD, 2);
            $amountBsFormatted = number_format($amountBs, 2);

            // HTML message - Gmail optimized with tables
            $htmlMessage = self::getHtmlMessage(
                $logoUrl,
                $logoBase64,
                $fullName,
                $receiptNumber,
                $contractNumber,
                $installmentNumber,
                $amountUSDFormatted,
                $amountBsFormatted,
                $date,
                $coverageFrom,
                $coverageTo,
                $planName,
                $clinicaName,
                $downloadLink
            );

            // Plain text version
            $plainTextMessage = self::getPlainTextMessage(
                $fullName,
                $receiptNumber,
                $contractNumber,
                $installmentNumber,
                $amountUSDFormatted,
                $amountBsFormatted,
                $date,
                $coverageFrom,
                $coverageTo,
                $planName,
                $clinicaName,
                $downloadLink
            );

            // Use Yii2 mailer with Symfony Mailer
            $result = Yii::$app->mailer->compose()
                ->setFrom(['sispsa.notificaciones@gmail.com' => 'SISPSA Notificaciones'])
                ->setTo($user->email)
                ->setSubject($subject)
                ->setHtmlBody($htmlMessage)
                ->setTextBody($plainTextMessage)
                ->send();

            if ($result) {
                Yii::info("Email sent to {$user->email} for receipt {$receiptNumber}", 'notification');
            } else {
                Yii::error("Failed to send email to {$user->email}", 'notification');
            }

            return $result;
        } catch (\Exception $e) {
            Yii::error("Notification error: " . $e->getMessage(), 'notification');
            return false;
        }
    }

    private static function generateDownloadLink($receiptId)
    {
        // Check if running in console mode
        if (Yii::$app->request->isConsoleRequest || PHP_SAPI === 'cli') {
            // Manual URL construction for console mode
            $domain = 'https://sispsatest.com';
            return $domain . '/index.php?r=receipts/print&id=' . $receiptId;
        }

        // Web mode - use Yii URL manager
        return Url::to(['/receipts/print', 'id' => $receiptId], true);
    }

    private static function getHtmlMessage($logoUrl, $logoBase64, $fullName, $receiptNumber, $contractNumber, $installmentNumber, $amountUSDFormatted, $amountBsFormatted, $date, $coverageFrom, $coverageTo, $planName, $clinicaName, $downloadLink)
    {
        $logoImg = file_exists(Yii::getAlias('@webroot') . '/img/sispsalogo.jpg') ? $logoUrl : $logoBase64;

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo SISPSA</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #f4f7fc; line-height: 1.5;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" align="center" bgcolor="#f4f7fc" style="width: 100%;">
        <tr>
            <td align="center" style="padding: 20px 10px;">
                <!-- Main Container -->
                <table width="100%" cellpadding="0" cellspacing="0" border="0" align="center" style="max-width: 600px; width: 100%; background: #ffffff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    
                    <!-- Header with Logo -->
                    <tr>
                        <td align="center" bgcolor="#1a3a6e" style="padding: 30px 20px; border-radius: 12px 12px 0 0;">
                            <img src="{$logoImg}" alt="SISPSA Logo" width="150" style="display: block; border: 0; max-width: 150px; width: 100%; margin-bottom: 15px;">
                            <h1 style="color: #ffffff; font-size: 22px; margin: 0; font-weight: 600;">📄 Comprobante de Pago</h1>
                            <p style="color: #e0e0e0; font-size: 13px; margin: 5px 0 0 0;">Recibo de Afiliación</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px 25px;">
                            <!-- Greeting -->
                            <p style="font-size: 16px; color: #333333; margin: 0 0 20px 0;">
                                Estimado(a) <strong style="color: #1a3a6e;">{$fullName}</strong>,
                            </p>
                            <p style="font-size: 14px; color: #555555; margin: 0 0 20px 0;">
                                Nos complace informarle que su pago ha sido registrado exitosamente. A continuación los detalles de su recibo:
                            </p>
                            
                            <!-- Receipt Info Card -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #f8fafc; border-radius: 12px; margin: 20px 0; border-left: 4px solid #28a745;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="padding-bottom: 15px; border-bottom: 1px solid #e0e0e0;">
                                                    <strong style="color: #1a3a6e; font-size: 16px;">📋 INFORMACIÓN DEL RECIBO</strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top: 15px;">
                                                    <table width="100%" cellpadding="8" cellspacing="0" border="0">
                                                        <tr>
                                                            <td width="140" style="font-weight: 600; color: #555555;">Número de Recibo:</td>
                                                            <td style="color: #333333;"><strong style="color: #1a3a6e;">{$receiptNumber}</strong></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-weight: 600; color: #555555;">Número de Contrato:</td>
                                                            <td style="color: #333333;">{$contractNumber}</td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-weight: 600; color: #555555;">N° Cuota Pagada:</td>
                                                            <td style="color: #333333;">{$installmentNumber} de 12</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Payment Info Card -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #f8fafc; border-radius: 12px; margin: 20px 0; border-left: 4px solid #28a745;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="padding-bottom: 15px; border-bottom: 1px solid #e0e0e0;">
                                                    <strong style="color: #1a3a6e; font-size: 16px;">💰 INFORMACIÓN DEL PAGO</strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top: 15px;">
                                                    <table width="100%" cellpadding="8" cellspacing="0" border="0">
                                                        <tr>
                                                            <td width="140" style="font-weight: 600; color: #555555;">Monto Pagado (USD):</td>
                                                            <td style="color: #333333;"><strong style="color: #28a745;">$ {$amountUSDFormatted}</strong></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-weight: 600; color: #555555;">Monto Pagado (Bs):</td>
                                                            <td style="color: #333333;"><strong>Bs. {$amountBsFormatted}</strong></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-weight: 600; color: #555555;">Fecha de Pago:</td>
                                                            <td style="color: #333333;">{$date}</td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-weight: 600; color: #555555;">Período de Cobertura:</td>
                                                            <td style="color: #333333;">{$coverageFrom} al {$coverageTo}</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Contract Info Card -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #f8fafc; border-radius: 12px; margin: 20px 0; border-left: 4px solid #28a745;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="padding-bottom: 15px; border-bottom: 1px solid #e0e0e0;">
                                                    <strong style="color: #1a3a6e; font-size: 16px;">🏥 INFORMACIÓN DEL CONTRATO</strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top: 15px;">
                                                    <table width="100%" cellpadding="8" cellspacing="0" border="0">
                                                        <tr>
                                                            <td width="140" style="font-weight: 600; color: #555555;">Plan:</td>
                                                            <td style="color: #333333;">{$planName}</td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-weight: 600; color: #555555;">Clínica:</td>
                                                            <td style="color: #333333;">{$clinicaName}</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <a href="{$downloadLink}" style="display: inline-block; background: #28a745; color: #ffffff; text-decoration: none; padding: 12px 30px; border-radius: 8px; font-weight: 600; font-size: 14px;">📄 Ver y Descargar Recibo</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Highlight Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #e8f5e9; border-radius: 8px; margin: 15px 0;">
                                <tr>
                                    <td style="padding: 15px;">
                                        <p style="margin: 0; font-size: 13px; color: #2e7d32;">
                                            <strong>✅ Este comprobante es un documento oficial de pago.</strong><br>
                                            Puede descargarlo o imprimirlo para sus registros contables.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td bgcolor="#f8fafc" style="padding: 20px; border-top: 1px solid #e0e0e0;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="font-size: 11px; color: #888888;">
                                        <p style="margin: 5px 0;"><strong>SISPSA</strong> - Sistema Integral de Salud Programado</p>
                                        <p style="margin: 5px 0;">Inscrita en la Superintendencia de la Actividad Aseguradora bajo el No. MP000013</p>
                                        <p style="margin: 5px 0;">R.I.F.: J-506549220</p>
                                        <p style="margin: 10px 0 5px 0;">Este es un mensaje automático, por favor no responder a este correo.</p>
                                        <p style="margin: 5px 0;">© {$date} SISPSA. Todos los derechos reservados.</p>
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
HTML;
    }

    private static function getPlainTextMessage($fullName, $receiptNumber, $contractNumber, $installmentNumber, $amountUSDFormatted, $amountBsFormatted, $date, $coverageFrom, $coverageTo, $planName, $clinicaName, $downloadLink)
    {
        return <<<TEXT
Estimado(a) {$fullName},

Nos complace informarle que su pago ha sido registrado exitosamente. A continuación los detalles de su recibo:

📋 INFORMACIÓN DEL RECIBO
Número de Recibo: {$receiptNumber}
Número de Contrato: {$contractNumber}
N° Cuota Pagada: {$installmentNumber} de 12

💰 INFORMACIÓN DEL PAGO
Monto Pagado (USD): $ {$amountUSDFormatted}
Monto Pagado (Bs): Bs. {$amountBsFormatted}
Fecha de Pago: {$date}
Período de Cobertura: {$coverageFrom} al {$coverageTo}

🏥 INFORMACIÓN DEL CONTRATO
Plan: {$planName}
Clínica: {$clinicaName}

Ver y descargar su recibo: {$downloadLink}

✅ Este comprobante es un documento oficial de pago.
Puede descargarlo o imprimirlo para sus registros contables.

-- 
SISPSA - Sistema Integral de Salud Programado
Inscrita en la Superintendencia de la Actividad Aseguradora bajo el No. MP000013
R.I.F.: J-506549220
Este es un mensaje automático, por favor no responder a este correo.
TEXT;
    }
}
