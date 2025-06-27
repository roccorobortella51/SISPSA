<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "asesores".
 *
 * @property int $id
 * @property string $created_at
 * @property string|null $nombres
 * @property string|null $apellidos
 * @property string|null $telefono
 * @property string|null $email
 * @property string|null $estatus
 * @property string|null $direccion
 * @property string|null $cedula
 * @property string|null $estado
 * @property string|null $imagen
 * @property int|null $id_user
 */
class Asesores extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'asesores';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombres', 'apellidos', 'telefono', 'email', 'estatus', 'direccion', 'cedula', 'estado', 'imagen', 'id_user'], 'default', 'value' => null],
            [['created_at'], 'safe'],
            [['nombres', 'apellidos', 'telefono', 'email', 'estatus', 'direccion', 'cedula', 'estado', 'imagen'], 'string'],
            [['id_user'], 'default', 'value' => null],
            [['id_user'], 'integer'],
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
            'nombres' => 'Nombres',
            'apellidos' => 'Apellidos',
            'telefono' => 'Telefono',
            'email' => 'Email',
            'estatus' => 'Estatus',
            'direccion' => 'Direccion',
            'cedula' => 'Cedula',
            'estado' => 'Estado',
            'imagen' => 'Imagen',
            'id_user' => 'Id User',
        ];
    }

}
