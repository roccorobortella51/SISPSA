<?php
// app/models/SisSiniestroNotification.php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "sis_siniestro_notification".
 *
 * @property int $id
 * @property int $siniestro_id
 * @property string $recipient_email
 * @property string $recipient_name
 * @property string $message
 * @property string $status
 * @property string|null $sent_at
 * @property string|null $error_message
 * @property int $retry_count
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property SisSiniestro $siniestro
 */
class SisSiniestroNotification extends ActiveRecord
{
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';
    const STATUS_RETRY = 'retry';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sis_siniestro_notification';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['siniestro_id', 'recipient_email', 'recipient_name', 'message'], 'required'],
            [['siniestro_id', 'retry_count'], 'integer'],
            [['message'], 'string'],
            [['sent_at', 'created_at', 'updated_at'], 'safe'],
            [['recipient_email'], 'email'],
            [['recipient_email'], 'string', 'max' => 255],
            [['recipient_name'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 20],
            [['status'], 'default', 'value' => self::STATUS_PENDING],
            [['error_message'], 'string', 'max' => 500],
            [['siniestro_id'], 'exist', 'skipOnError' => true, 'targetClass' => SisSiniestro::class, 'targetAttribute' => ['siniestro_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'siniestro_id' => 'Cita ID',
            'recipient_email' => 'Correo del Destinatario',
            'recipient_name' => 'Nombre del Destinatario',
            'message' => 'Mensaje',
            'status' => 'Estado',
            'sent_at' => 'Enviado El',
            'error_message' => 'Mensaje de Error',
            'retry_count' => 'Intentos de Reenvío',
            'created_at' => 'Creado El',
            'updated_at' => 'Actualizado El',
        ];
    }

    /**
     * Gets query for [[Siniestro]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSiniestro()
    {
        return $this->hasOne(SisSiniestro::class, ['id' => 'siniestro_id']);
    }

    /**
     * Get status label with badge HTML
     *
     * @return string
     */
    public function getStatusBadge()
    {
        $badges = [
            self::STATUS_PENDING => '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pendiente</span>',
            self::STATUS_SENT => '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Enviado</span>',
            self::STATUS_FAILED => '<span class="badge badge-danger"><i class="fas fa-exclamation-circle"></i> Fallido</span>',
            self::STATUS_RETRY => '<span class="badge badge-info"><i class="fas fa-sync"></i> Reintento</span>',
        ];

        return $badges[$this->status] ?? $badges[self::STATUS_PENDING];
    }

    /**
     * Get status options for dropdown
     *
     * @return array
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_SENT => 'Enviado',
            self::STATUS_FAILED => 'Fallido',
            self::STATUS_RETRY => 'Reintento',
        ];
    }

    /**
     * Mark notification as sent
     *
     * @return bool
     */
    public function markAsSent()
    {
        $this->status = self::STATUS_SENT;
        $this->sent_at = date('Y-m-d H:i:s');
        $this->error_message = null;
        return $this->save(false);
    }

    /**
     * Mark notification as failed
     *
     * @param string $errorMessage
     * @return bool
     */
    public function markAsFailed($errorMessage)
    {
        $this->status = self::STATUS_FAILED;
        $this->error_message = $errorMessage;
        $this->retry_count++;
        return $this->save(false);
    }

    /**
     * Queue for retry
     *
     * @return bool
     */
    public function queueForRetry()
    {
        $this->status = self::STATUS_RETRY;
        $this->retry_count++;
        return $this->save(false);
    }

    /**
     * Create notification for a siniestro/cita
     *
     * @param SisSiniestro $siniestro
     * @return SisSiniestroNotification|null
     */
    public static function createFromSiniestro($siniestro)
    {
        if (!$siniestro || $siniestro->es_cita != 1) {
            return null;
        }

        $afiliado = UserDatos::findOne($siniestro->iduser);
        if (!$afiliado) {
            Yii::error("Afiliado not found for siniestro ID: {$siniestro->id}", __METHOD__);
            return null;
        }

        // Check if email is valid
        $email = $afiliado->email;
        if (empty($email)) {
            Yii::warning("No email for affiliate ID: {$afiliado->id}", __METHOD__);
            return null;
        }

        // Build the message (this will be used as fallback, but the actual email uses the template)
        $message = self::buildEmailMessage($siniestro, $afiliado);

        $notification = new self();
        $notification->siniestro_id = $siniestro->id;
        $notification->recipient_email = $email;
        $notification->recipient_name = trim($afiliado->nombres . ' ' . $afiliado->apellidos);
        $notification->message = $message;
        $notification->status = self::STATUS_PENDING;
        $notification->retry_count = 0;

        return $notification;
    }

    /**
     * Build email message content (fallback if template fails)
     *
     * @param SisSiniestro $siniestro
     * @param UserDatos $afiliado
     * @return string
     */
    public static function buildEmailMessage($siniestro, $afiliado)
    {
        // Get baremos/services
        $baremos = $siniestro->baremos;
        $servicesList = '';
        $totalCost = 0;

        if (!empty($baremos)) {
            $servicesList = "Servicios Médicos:\n";
            $counter = 1;
            foreach ($baremos as $baremo) {
                $serviceName = $baremo->nombre_servicio ?? 'Sin nombre';
                $price = $baremo->precio ?? 0;
                $servicesList .= "   {$counter}. {$serviceName} - $" . number_format($price, 2) . "\n";
                $totalCost += $price;
                $counter++;
            }
        } else {
            $servicesList = "Servicio: No especificado\n";
        }

        // Format date and time
        $appointmentDate = isset($siniestro->fecha_atencion)
            ? Yii::$app->formatter->asDate($siniestro->fecha_atencion, 'dd/MM/yyyy')
            : 'No especificada';

        $appointmentTime = isset($siniestro->hora_atencion)
            ? date('H:i', strtotime($siniestro->hora_atencion))
            : 'No especificada';

        $doctorName = $siniestro->nombre_doctor ?? 'No especificado';
        $analystName = $siniestro->admission_analyst ?? 'No especificado';
        $clinicaName = $siniestro->clinica ? $siniestro->clinica->nombre : 'No especificada';

        $message = "SISPSA - Confirmación de Cita Médica\n\n";
        $message .= "Paciente: {$afiliado->nombres} {$afiliado->apellidos}\n";
        $message .= "Cédula: {$afiliado->tipo_cedula}-{$afiliado->cedula}\n";
        $message .= "Clínica: {$clinicaName}\n";
        $message .= "Fecha: {$appointmentDate}\n";
        $message .= "Hora: {$appointmentTime}\n";
        $message .= "Médico: {$doctorName}\n";
        $message .= "Analista: {$analystName}\n\n";
        $message .= $servicesList;

        if ($totalCost > 0) {
            $message .= "\nCosto Total: $" . number_format($totalCost, 2);
        }

        $message .= "\n\n---\n";
        $message .= "Por favor, llegar con 15 minutos de anticipación.\n";
        $message .= "Para cualquier inquietud, comuníquese con su clínica.";

        return $message;
    }

    /**
     * Check if notification exists for a siniestro
     *
     * @param int $siniestroId
     * @return bool
     */
    public static function existsForSiniestro($siniestroId)
    {
        return self::find()
            ->where(['siniestro_id' => $siniestroId])
            ->exists();
    }

    /**
     * Get notification for a siniestro
     *
     * @param int $siniestroId
     * @return SisSiniestroNotification|null
     */
    public static function getForSiniestro($siniestroId)
    {
        return self::find()
            ->where(['siniestro_id' => $siniestroId])
            ->one();
    }

    /**
     * Get recent notifications for a user/affiliate
     *
     * @param int $userId
     * @param int $limit
     * @return SisSiniestroNotification[]
     */
    public static function getRecentForUser($userId, $limit = 10)
    {
        return self::find()
            ->innerJoin('sis_siniestro', 'sis_siniestro.id = sis_siniestro_notification.siniestro_id')
            ->where(['sis_siniestro.iduser' => $userId])
            ->andWhere(['sis_siniestro.es_cita' => 1])
            ->orderBy(['sis_siniestro_notification.created_at' => SORT_DESC])
            ->limit($limit)
            ->all();
    }

    /**
     * Get pending notifications count
     *
     * @return int
     */
    public static function getPendingCount()
    {
        return self::find()
            ->where(['status' => [self::STATUS_PENDING, self::STATUS_RETRY]])
            ->count();
    }
}
