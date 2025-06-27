<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ente".
 *
 * @property int $id
 * @property string $created_at
 * @property string|null $nombre
 * @property string|null $direccion
 * @property string|null $telefono
 * @property string|null $correo
 * @property string|null $contacto
 * @property string|null $nota
 * @property string|null $estatus
 * @property string|null $estado
 */
class Ente extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ente';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'direccion', 'telefono', 'correo', 'contacto', 'nota', 'estatus', 'estado'], 'default', 'value' => null],
            [['created_at'], 'safe'],
            [['nombre', 'direccion', 'telefono', 'correo', 'contacto', 'nota', 'estatus', 'estado'], 'string'],
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
            'direccion' => 'Direccion',
            'telefono' => 'Telefono',
            'correo' => 'Correo',
            'contacto' => 'Contacto',
            'nota' => 'Nota',
            'estatus' => 'Estatus',
            'estado' => 'Estado',
        ];
    }

}
