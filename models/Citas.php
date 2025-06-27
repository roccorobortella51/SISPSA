<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "citas".
 *
 * @property int $id
 * @property string $created_at
 * @property string|null $user_id
 * @property string|null $nombre
 * @property string|null $clinica
 * @property string|null $doctor
 * @property string|null $fecha
 * @property string|null $hora
 * @property string|null $nota
 * @property string|null $estatus
 * @property string|null $satusfaccion
 */
class Citas extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'citas';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'clinica', 'doctor', 'fecha', 'hora', 'nota', 'estatus', 'satusfaccion'], 'default', 'value' => null],
            [['user_id'], 'default', 'value' => 'gen_random_uuid()'],
            [['created_at', 'fecha', 'hora'], 'safe'],
            [['user_id', 'nombre', 'clinica', 'doctor', 'nota', 'estatus', 'satusfaccion'], 'string'],
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
            'user_id' => 'User ID',
            'nombre' => 'Nombre',
            'clinica' => 'Clinica',
            'doctor' => 'Doctor',
            'fecha' => 'Fecha',
            'hora' => 'Hora',
            'nota' => 'Nota',
            'estatus' => 'Estatus',
            'satusfaccion' => 'Satusfaccion',
        ];
    }

}
