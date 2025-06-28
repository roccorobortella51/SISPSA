<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "beneficiarios".
 *
 * @property int $id
 * @property string $created_at
 * @property string|null $nombres
 * @property string|null $correo
 * @property string|null $estatus
 * @property string|null $Apellidos
 * @property string|null $tipo_de_relacion
 * @property int|null $cedula
 * @property int|null $id_titular
 * @property int|null $id_beneficiario
 *
 * @property UserDatos $beneficiario
 * @property Recibos[] $recibos
 * @property UserDatos $titular
 */
class Beneficiarios extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'beneficiarios';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombres', 'correo', 'estatus', 'Apellidos', 'tipo_de_relacion', 'cedula', 'id_titular', 'id_beneficiario'], 'default', 'value' => null],
            [['created_at'], 'safe'],
            [['nombres', 'correo', 'estatus', 'Apellidos', 'tipo_de_relacion'], 'string'],
            [['cedula', 'id_titular', 'id_beneficiario'], 'default', 'value' => null],
            [['cedula', 'id_titular', 'id_beneficiario'], 'integer'],
            [['id_titular'], 'exist', 'skipOnError' => true, 'targetClass' => UserDatos::class, 'targetAttribute' => ['id_titular' => 'id']],
            [['id_beneficiario'], 'exist', 'skipOnError' => true, 'targetClass' => UserDatos::class, 'targetAttribute' => ['id_beneficiario' => 'id']],
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
            'correo' => 'Correo',
            'estatus' => 'Estatus',
            'Apellidos' => 'Apellidos',
            'tipo_de_relacion' => 'Tipo De Relacion',
            'cedula' => 'Cedula',
            'id_titular' => 'Id Titular',
            'id_beneficiario' => 'Id Beneficiario',
        ];
    }

    /**
     * Gets query for [[Beneficiario]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBeneficiario()
    {
        return $this->hasOne(UserDatos::class, ['id' => 'id_beneficiario']);
    }

    /**
     * Gets query for [[Recibos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecibos()
    {
        return $this->hasMany(Recibos::class, ['dependiente_id' => 'id']);
    }

    /**
     * Gets query for [[Titular]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTitular()
    {
        return $this->hasOne(UserDatos::class, ['id' => 'id_titular']);
    }

}
