<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sis_consulta".
 *
 * @property int $id
 * @property int $idsiniestro
 * @property int $idmedico
 * @property float $presion_alta
 * @property float $presion_baja
 * @property float $temperatura
 * @property float $peso
 * @property float $altura
 * @property string|null $informe
 * @property string $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 *
 * @property SisUsuario $idmedico0
 * @property SisSiniestro $idsiniestro0
 */
class SisConsulta extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sis_consulta';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['informe', 'updated_at', 'deleted_at'], 'default', 'value' => null],
            [['idsiniestro', 'idmedico', 'presion_alta', 'presion_baja', 'temperatura', 'peso', 'altura'], 'required'],
            [['idsiniestro', 'idmedico'], 'default', 'value' => null],
            [['idsiniestro', 'idmedico'], 'integer'],
            [['presion_alta', 'presion_baja', 'temperatura', 'peso', 'altura'], 'number'],
            [['informe'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['idsiniestro'], 'exist', 'skipOnError' => true, 'targetClass' => SisSiniestro::class, 'targetAttribute' => ['idsiniestro' => 'id']],
            [['idmedico'], 'exist', 'skipOnError' => true, 'targetClass' => SisUsuario::class, 'targetAttribute' => ['idmedico' => 'idusuario']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idsiniestro' => 'Idsiniestro',
            'idmedico' => 'Idmedico',
            'presion_alta' => 'Presion Alta',
            'presion_baja' => 'Presion Baja',
            'temperatura' => 'Temperatura',
            'peso' => 'Peso',
            'altura' => 'Altura',
            'informe' => 'Informe',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    /**
     * Gets query for [[Idmedico0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdmedico0()
    {
        return $this->hasOne(SisUsuario::class, ['idusuario' => 'idmedico']);
    }

    /**
     * Gets query for [[Idsiniestro0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdsiniestro0()
    {
        return $this->hasOne(SisSiniestro::class, ['id' => 'idsiniestro']);
    }

}
