<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "notifications".
 *
 * @property int $id
 * @property string $created_at
 * @property string|null $message
 * @property string|null $status
 * @property string|null $razon
 * @property string|null $tipo_notificacion
 * @property int|null $user_datos_id
 *
 * @property UserDatos $userDatos
 */
class Notifications extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notifications';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['message', 'status', 'razon', 'tipo_notificacion', 'user_datos_id'], 'default', 'value' => null],
            [['created_at', 'razon'], 'safe'],
            [['message', 'status', 'tipo_notificacion'], 'string'],
            [['user_datos_id'], 'default', 'value' => null],
            [['user_datos_id'], 'integer'],
            [['user_datos_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserDatos::class, 'targetAttribute' => ['user_datos_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'message' => 'Message',
            'status' => 'Status',
            'razon' => 'Razon',
            'tipo_notificacion' => 'Tipo Notificacion',
            'user_datos_id' => 'User Datos ID',
        ];
    }

    /**
     * Gets query for [[UserDatos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserDatos()
    {
        return $this->hasOne(UserDatos::class, ['id' => 'user_datos_id']);
    }

}
