<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sis_siniestro".
 *
 * @property int $id
 * @property int $idclinica
 * @property string $fecha
 * @property string $hora
 * @property int $idbaremo
 * @property int $atendido
 * @property string|null $fecha_atencion
 * @property string|null $hora_atencion
 * @property int $iduser
 * @property string|null $descripcion
 * @property string $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 *
 * @property Baremo $idbaremo0
 * @property RmClinica $idclinica0
 * @property SisConsulta[] $sisConsultas
 */
class SisSiniestro extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sis_siniestro';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fecha_atencion', 'hora_atencion', 'descripcion', 'updated_at', 'deleted_at'], 'default', 'value' => null],
            [['atendido'], 'default', 'value' => 0],
            [['idclinica', 'fecha', 'hora', 'idbaremo', 'iduser', 'descripcion'], 'required'],
            [['idclinica', 'idbaremo', 'atendido', 'iduser'], 'default', 'value' => null],
            [['idclinica', 'idbaremo', 'atendido', 'iduser'], 'integer'],
            [['fecha', 'fecha_atencion', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['descripcion'], 'string'],
            [['hora', 'hora_atencion'], 'string', 'max' => 10],
            [['idbaremo'], 'exist', 'skipOnError' => true, 'targetClass' => Baremo::class, 'targetAttribute' => ['idbaremo' => 'id']],
            [['idclinica'], 'exist', 'skipOnError' => true, 'targetClass' => RmClinica::class, 'targetAttribute' => ['idclinica' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idclinica' => 'Idclinica',
            'fecha' => 'Fecha',
            'hora' => 'Hora',
            'idbaremo' => 'Baremo',
            'atendido' => 'Atendido',
            'fecha_atencion' => 'Fecha Atencion',
            'hora_atencion' => 'Hora Atencion',
            'iduser' => 'Iduser',
            'descripcion' => 'Descripcion',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    /**
     * Gets query for [[Idbaremo0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBaremo()
    {
        return $this->hasOne(Baremo::class, ['id' => 'idbaremo']);
    }

    /**
     * Gets query for [[Idclinica0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClinica()
    {
        return $this->hasOne(RmClinica::class, ['id' => 'idclinica']);
    }

    /**
     * Gets query for [[SisConsultas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSisConsultas()
    {
        return $this->hasMany(SisConsulta::class, ['idsiniestro' => 'id']);
    }

    public function getAfiliado()
    {
        return $this->hasOne(UserDatos::class, ['id' => 'user_id']);
    }

}
