<?php
// models/SisSiniestroAuditLog.php

namespace app\models;


use Yii;
use yii\db\ActiveRecord;
use app\components\UserHelper;

/**
 * Audit log model for tracking deletions of appointments/attentions
 *
 * @property int $id
 * @property int $siniestro_id
 * @property int $user_id
 * @property string $user_name
 * @property string $user_role
 * @property string $action
 * @property string $reason
 * @property string $deleted_data
 * @property string $created_at
 */
class SisSiniestroAuditLog extends ActiveRecord
{
    const ACTION_DELETE = 'delete';
    const ACTION_RESTORE = 'restore'; // For future restoration feature

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sis_siniestro_audit_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['siniestro_id', 'user_id', 'user_name', 'user_role', 'action', 'reason'], 'required'],
            [['siniestro_id', 'user_id'], 'integer'],
            [['deleted_data'], 'safe'],
            [['created_at'], 'safe'],
            [['user_name', 'user_role', 'action'], 'string', 'max' => 100],
            [['reason'], 'string', 'max' => 500],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'siniestro_id' => 'Appointment/Attention ID',
            'user_id' => 'User ID',
            'user_name' => 'User Name',
            'user_role' => 'User Role',
            'action' => 'Action',
            'reason' => 'Deletion Reason',
            'deleted_data' => 'Deleted Data',
            'created_at' => 'Date',
        ];
    }

    /**
     * Log a deletion
     * @param int $siniestroId
     * @param array $deletedData
     * @param string $reason
     * @return bool
     */
    public static function logDeletion($siniestroId, $deletedData, $reason)
    {
        $user = Yii::$app->user->identity;
        $userDatos = UserDatos::findOne($user->id);
        $userRole = UserHelper::getMyRol();

        $log = new self();
        $log->siniestro_id = $siniestroId;
        $log->user_id = $user->id;
        $log->user_name = $userDatos ? $userDatos->nombres . ' ' . $userDatos->apellidos : $user->username;
        $log->user_role = $userRole;
        $log->action = self::ACTION_DELETE;
        $log->reason = $reason;
        $log->deleted_data = json_encode($deletedData, JSON_UNESCAPED_UNICODE);
        $log->created_at = date('Y-m-d H:i:s');

        return $log->save();
    }
}
