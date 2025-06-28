<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "clinica_Contactos".
 *
 * @property int $id
 * @property string $created_at
 * @property int|null $clinica_id
 * @property int|null $cedula
 * @property string|null $nombre
 * @property string|null $cargo
 * @property string|null $telefono
 * @property string|null $correo
 * @property string|null $estatus
 *
 * @property RmClinica $clinica
 */
class ClinicaContactos extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'clinica_Contactos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['clinica_id', 'cedula', 'nombre', 'cargo', 'telefono', 'correo', 'estatus'], 'default', 'value' => null],
            [['created_at'], 'safe'],
            [['clinica_id', 'cedula'], 'default', 'value' => null],
            [['clinica_id', 'cedula'], 'integer'],
            [['nombre', 'cargo', 'telefono', 'correo', 'estatus'], 'string'],
            [['clinica_id'], 'exist', 'skipOnError' => true, 'targetClass' => RmClinica::class, 'targetAttribute' => ['clinica_id' => 'id']],
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
            'clinica_id' => 'Clinica ID',
            'cedula' => 'Cedula',
            'nombre' => 'Nombre',
            'cargo' => 'Cargo',
            'telefono' => 'Telefono',
            'correo' => 'Correo',
            'estatus' => 'Estatus',
        ];
    }

    /**
     * Gets query for [[Clinica]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClinica()
    {
        return $this->hasOne(RmClinica::class, ['id' => 'clinica_id']);
    }

}
