<?php
// app/mail/appointment-notification.php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $afiliado app\models\UserDatos */
/* @var $siniestro app\models\SisSiniestro */
/* @var $services array */
/* @var $totalCost float */
/* @var $appointmentDate string */
/* @var $appointmentTime string */
/* @var $clinicaName string */
/* @var $doctorName string */
/* @var $analystName string */
/* @var $appointmentId int */

// Ensure optional variables are defined
$doctorName = $doctorName ?? 'No especificado';
$appointmentDate = $appointmentDate ?? 'No especificada';
$appointmentTime = $appointmentTime ?? 'No especificada';
$analystName = $analystName ?? 'No especificado';
$appointmentId = $appointmentId ?? ($siniestro->id ?? null);

// Get full name
$fullName = '';
if (is_object($afiliado)) {
    $fullName = ($afiliado->nombres ?? '') . ' ' . ($afiliado->apellidos ?? '');
} else {
    $fullName = (string)$afiliado;
}
$fullName = trim($fullName);
?>

<!-- ===== GREETING ===== -->
<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td>
            <p style="font-size: 15px; color: #2c3e50; line-height: 1.8; margin: 0 0 2px 0; font-weight: 300;">
                Estimado(a)
            </p>
            <h2 style="font-size: 24px; color: #011F3E; margin: 0 0 12px 0; font-weight: 700;">
                <?= Html::encode($fullName) ?> 👋
            </h2>

            <p style="font-size: 15px; color: #4a5568; line-height: 1.8; margin: 0 0 6px 0;">
                <span style="color: #009EFB; font-weight: 600;">🌟 ¡Nos alegra tenerte con nosotros!</span>
            </p>
            <p style="font-size: 15px; color: #4a5568; line-height: 1.8; margin: 0 0 20px 0;">
                Su cita médica ha sido registrada exitosamente en nuestro sistema.
                En <strong style="color: #011F3E;">SISPSA</strong>, nos comprometemos a brindarle la mejor atención y cuidado para su salud.
            </p>
        </td>
    </tr>
</table>

<!-- ===== WELCOME NOTE ===== -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: linear-gradient(135deg, #f0f8ff 0%, #e6f4ff 100%); border-left: 4px solid #009EFB; border-radius: 8px; margin-bottom: 24px;">
    <tr>
        <td style="padding: 16px 20px;">
            <p style="font-size: 14px; font-weight: 600; color: #011F3E; margin: 0 0 4px 0;">🏥 Un entorno de confianza y calidez</p>
            <p style="font-size: 13px; color: #4a5568; line-height: 1.7; margin: 0;">
                En SISPSA, su bienestar es nuestra prioridad. Nuestro equipo de profesionales está listo para atenderle con
                la calidez y excelencia que usted merece. ❤️
            </p>
        </td>
    </tr>
</table>

<!-- ===== APPOINTMENT DETAILS ===== -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #f8fafc; border-radius: 12px; border: 1px solid #e8ecf1; margin-bottom: 24px;">
    <tr>
        <td style="padding: 0;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="background: #eef3f7; padding: 14px 22px; border-bottom: 1px solid #e8ecf1; border-left: 4px solid #009EFB; border-radius: 12px 12px 0 0;">
                        <span style="font-size: 15px; font-weight: 600; color: #011F3E;">📋 Detalles de su Cita</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 18px 22px;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td style="padding: 5px 0; width: 30%;">
                                    <span style="font-size: 13px; color: #718096; font-weight: 500;">📅 Fecha</span>
                                </td>
                                <td style="padding: 5px 0; width: 70%;">
                                    <span style="font-size: 15px; color: #2c3e50; font-weight: 500;"><?= Html::encode($appointmentDate) ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 0;">
                                    <span style="font-size: 13px; color: #718096; font-weight: 500;">🕐 Hora</span>
                                </td>
                                <td style="padding: 5px 0;">
                                    <span style="font-size: 15px; color: #2c3e50; font-weight: 500;"><?= Html::encode($appointmentTime) ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 0;">
                                    <span style="font-size: 13px; color: #718096; font-weight: 500;">🏛️ Clínica</span>
                                </td>
                                <td style="padding: 5px 0;">
                                    <span style="font-size: 15px; color: #2c3e50; font-weight: 500;"><?= Html::encode($clinicaName) ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 0;">
                                    <span style="font-size: 13px; color: #718096; font-weight: 500;">👨‍⚕️ Médico</span>
                                </td>
                                <td style="padding: 5px 0;">
                                    <span style="font-size: 15px; color: #2c3e50; font-weight: 500;">
                                        <?php if (!empty($doctorName) && $doctorName !== 'No especificado'): ?>
                                            <?= Html::encode($doctorName) ?>
                                        <?php else: ?>
                                            <span style="color: #a0aec0; font-weight: 400;">No especificado</span>
                                        <?php endif; ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 0;">
                                    <span style="font-size: 13px; color: #718096; font-weight: 500;">💼 Analista</span>
                                </td>
                                <td style="padding: 5px 0;">
                                    <span style="font-size: 15px; color: #2c3e50; font-weight: 500;">
                                        <?php if (!empty($analystName) && $analystName !== 'No especificado'): ?>
                                            <?= Html::encode($analystName) ?>
                                        <?php else: ?>
                                            <span style="color: #a0aec0; font-weight: 400;">No especificado</span>
                                        <?php endif; ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 0;">
                                    <span style="font-size: 13px; color: #718096; font-weight: 500;">📋 ID de Cita</span>
                                </td>
                                <td style="padding: 5px 0;">
                                    <span style="font-size: 15px; color: #2c3e50; font-weight: 600;">#<?= $appointmentId ?></span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- ===== SERVICES ===== -->
<?php if (!empty($services)): ?>
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 24px;">
        <tr>
            <td>
                <div style="display: flex; align-items: center; margin-bottom: 12px;">
                    <span style="font-size: 16px; font-weight: 600; color: #011F3E;">📋 Servicios Médicos</span>
                    <span style="margin-left: 12px; background: #011F3E; padding: 2px 14px; border-radius: 12px; font-size: 12px; color: #ffffff; font-weight: 500;"><?= count($services) ?> servicio<?= count($services) > 1 ? 's' : '' ?></span>
                </div>
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border: 1px solid #e8ecf1; border-radius: 8px; overflow: hidden;">
                    <thead>
                        <tr style="background: #011F3E;">
                            <th style="padding: 10px 14px; text-align: left; font-size: 12px; font-weight: 600; color: #ffffff; text-transform: uppercase; letter-spacing: 0.4px; border-bottom: 2px solid #009EFB; width: 40%;">Servicio</th>
                            <th style="padding: 10px 14px; text-align: left; font-size: 12px; font-weight: 600; color: #ffffff; text-transform: uppercase; letter-spacing: 0.4px; border-bottom: 2px solid #009EFB; width: 25%;">Área</th>
                            <th style="padding: 10px 14px; text-align: right; font-size: 12px; font-weight: 600; color: #ffffff; text-transform: uppercase; letter-spacing: 0.4px; border-bottom: 2px solid #009EFB; width: 35%;">Costo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $service): ?>
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td style="padding: 10px 14px; font-size: 14px; color: #2c3e50;">
                                    <span style="font-weight: 500;"><?= Html::encode($service['name']) ?></span>
                                    <?php if (!empty($service['description'])): ?>
                                        <br><span style="font-size: 12px; color: #718096;"><?= Html::encode($service['description']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 10px 14px; font-size: 13px; color: #4a5568;">
                                    <span style="background: #e8f4fd; padding: 3px 12px; border-radius: 12px; font-size: 12px; color: #009EFB; display: inline-block;"><?= Html::encode($service['area']) ?></span>
                                </td>
                                <td style="padding: 10px 14px; text-align: right; font-size: 15px; color: #009EFB; font-weight: 600;">
                                    $<?= number_format($service['price'], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background: #f8fafc; border-top: 2px solid #009EFB;">
                            <td colspan="2" style="padding: 12px 14px; text-align: right; font-size: 15px; font-weight: 600; color: #011F3E;">
                                <span style="font-size: 16px;">Total</span>
                            </td>
                            <td style="padding: 12px 14px; text-align: right; font-size: 17px; color: #009EFB; font-weight: 700;">
                                $<?= number_format($totalCost, 2) ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </td>
        </tr>
    </table>
<?php else: ?>
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #fef9e7; border-left: 4px solid #f39c12; border-radius: 8px; margin-bottom: 24px;">
        <tr>
            <td style="padding: 14px 18px;">
                <span style="font-size: 14px; color: #856404;">ℹ️ No se han especificado servicios médicos para esta cita.</span>
            </td>
        </tr>
    </table>
<?php endif; ?>

<!-- ===== IMPORTANT NOTES ===== -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #fef9e7; border-left: 4px solid #f39c12; border-radius: 8px; margin-bottom: 24px;">
    <tr>
        <td style="padding: 14px 18px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="vertical-align: top; width: 32px; padding-right: 12px;">
                        <span style="font-size: 18px;">📌</span>
                    </td>
                    <td>
                        <p style="font-size: 14px; font-weight: 600; color: #856404; margin: 0 0 6px 0;">Instrucciones importantes:</p>
                        <ul style="margin: 0; padding-left: 18px; font-size: 13px; color: #4a5568; line-height: 1.8;">
                            <li>Por favor, llegar con <strong>15 minutos de anticipación</strong> a su cita.</li>
                            <li>Llevar su cédula de identidad y cualquier documentación médica relevante.</li>
                            <li>Si necesita cancelar o reprogramar, comuníquese con la clínica con al menos 24 horas de anticipación.</li>
                        </ul>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- ===== CLOSING ===== -->
<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td style="padding-top: 16px; text-align: center;">
            <p style="font-size: 14px; color: #4a5568; line-height: 1.8; margin: 0;">
                <span style="color: #011F3E; font-weight: 600;">✨ ¡Gracias por confiar en SISPSA!</span>
                <br>
                <span style="font-size: 13px; color: #009EFB;">
                    Estamos comprometidos con su bienestar y salud. ❤️
                </span>
            </p>
            <p style="font-size: 13px; color: #a0aec0; margin: 6px 0 0 0;">
                🙏 Siempre a su servicio
            </p>
        </td>
    </tr>
</table>