<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Contactos_Emergencia".
 *
 * @property int $id
 * @property string $created_at
 * @property string|null $nombre
 * @property string|null $telefono
 * @property string|null $correo
 * @property int|null $user_id
 * @property string|null $relacion
 * @property string|null $deleted_at
 * @property string|null $updated_at
 *
 * @property UserDatos $user
 */
class ContactosEmergencia extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Contactos_Emergencia';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'telefono', 'correo', 'user_id', 'relacion', 'deleted_at', 'updated_at'], 'default', 'value' => null],
            [['created_at', 'deleted_at', 'updated_at'], 'safe'],
            [['nombre', 'telefono', 'correo', 'relacion'], 'string'],
            [['user_id'], 'default', 'value' => null],
            [['user_id'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserDatos::class, 'targetAttribute' => ['user_id' => 'id']],
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
            'nombre' => 'Nombre',
            'telefono' => 'Telefono',
            'correo' => 'Correo',
            'user_id' => 'User ID',
            'relacion' => 'Relacion',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(UserDatos::class, ['id' => 'user_id']);
    }

}
