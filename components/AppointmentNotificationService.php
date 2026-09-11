<?php
// app/components/AppointmentNotificationService.php

namespace app\components;

use Yii;
use yii\helpers\Html;
use app\models\SisSiniestro;
use app\models\UserDatos;

class AppointmentNotificationService
{
    /**
     * Send appointment notification via email
     * 
     * @param SisSiniestro $siniestro The appointment/cita
     * @return array ['success' => bool, 'message' => string]
     */
    public static function sendAppointmentEmail($siniestro)
    {
        // Only send for citas (appointments)
        if (!$siniestro || $siniestro->es_cita != 1) {
            return [
                'success' => false,
                'message' => 'Solo se pueden enviar notificaciones para citas médicas.'
            ];
        }

        $afiliado = UserDatos::findOne($siniestro->iduser);
        if (!$afiliado) {
            return [
                'success' => false,
                'message' => 'Afiliado no encontrado.'
            ];
        }

        if (empty($afiliado->email)) {
            return [
                'success' => false,
                'message' => 'El afiliado no tiene un correo electrónico registrado.'
            ];
        }

        try {
            $emailData = self::prepareEmailData($siniestro, $afiliado);

            $sent = Yii::$app->mailer->compose('appointment-notification', $emailData)
                ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->params['appName'] ?? 'SISPSA'])
                ->setTo($afiliado->email)
                ->setSubject($emailData['subject'])
                ->send();

            if ($sent) {
                Yii::info("Appointment email sent to {$afiliado->email} for appointment #{$siniestro->id}", 'appointment');

                return [
                    'success' => true,
                    'message' => 'Notificación enviada correctamente por correo electrónico a <strong>' . Html::encode($afiliado->email) . '</strong>.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al enviar el correo electrónico. Por favor, intente nuevamente.'
                ];
            }
        } catch (\Exception $e) {
            Yii::error("Exception sending appointment email: " . $e->getMessage(), 'appointment');

            return [
                'success' => false,
                'message' => 'Error al enviar el correo: ' . $e->getMessage()
            ];
        }
    }

    private static function prepareEmailData($siniestro, $afiliado)
    {
        $baremos = $siniestro->baremos;
        $services = [];
        $totalCost = 0;

        if (!empty($baremos)) {
            foreach ($baremos as $baremo) {
                $services[] = [
                    'name' => $baremo->nombre_servicio ?? 'Servicio médico',
                    'area' => $baremo->area ? $baremo->area->nombre : 'Sin área',
                    'description' => $baremo->descripcion ?? '',
                    'price' => (float)($baremo->precio ?? 0),
                ];
                $totalCost += (float)($baremo->precio ?? 0);
            }
        }

        $appointmentDate = isset($siniestro->fecha_atencion)
            ? Yii::$app->formatter->asDate($siniestro->fecha_atencion, 'dd/MM/yyyy')
            : 'No especificada';

        $appointmentTime = isset($siniestro->hora_atencion)
            ? date('H:i', strtotime($siniestro->hora_atencion))
            : 'No especificada';

        $clinicaName = $siniestro->clinica ? $siniestro->clinica->nombre : 'No especificada';
        $doctorName = $siniestro->nombre_doctor ?? 'No especificado';
        $analystName = $siniestro->admission_analyst ?? 'No especificado';

        return [
            'subject' => "📋 Confirmación de Cita Médica - SISPSA",
            'afiliado' => $afiliado,
            'siniestro' => $siniestro,
            'services' => $services,
            'totalCost' => $totalCost,
            'appointmentDate' => $appointmentDate,
            'appointmentTime' => $appointmentTime,
            'clinicaName' => $clinicaName,
            'doctorName' => $doctorName,
            'analystName' => $analystName,
            'appointmentId' => $siniestro->id,
            'year' => date('Y'),
        ];
    }

    /**
     * Generate WhatsApp link for staff (manual sending)
     * 
     * @param string $phone The affiliate's phone number
     * @param string $message The pre-filled message
     * @return string|null The WhatsApp link
     */
    public static function generateWhatsAppLink($phone, $message)
    {
        if (empty($phone)) {
            return null;
        }

        // Remove all non-numeric characters from phone
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If phone has 10 digits (Venezuela format without country code)
        if (strlen($phone) === 10) {
            $phone = '58' . $phone; // Add Venezuela country code
        }

        // If phone has 11 digits (Venezuela format with 0 at start)
        if (strlen($phone) === 11 && substr($phone, 0, 1) === '0') {
            $phone = '58' . substr($phone, 1); // Remove leading 0, add 58
        }

        // ============================================
        // USE https://wa.me/ - THE CORRECT URL FORMAT
        // This opens WhatsApp directly with the pre-filled message
        // ============================================
        $encodedMessage = urlencode($message);
        return "https://wa.me/{$phone}?text={$encodedMessage}";
    }
}
