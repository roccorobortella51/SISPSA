<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "rm_clinica".
 *
 * @property int $id
 * @property string $created_at
 * @property string|null $rif
 * @property string|null $nombre
 * @property string|null $estado
 * @property string|null $direccion
 * @property string|null $telefono
 * @property string|null $correo
 * @property string $estatus
 * @property string|null $webpage
 * @property string|null $rs_instagram
 * @property string|null $QRCode
 * @property string|null $codigo_clinica
 * @property string|null $deleted_at
 * @property string|null $updated_at
 * @property string|null $private_key
 *
 * @property ClinicaContactos[] $clinicaContactos
 * @property Contratos[] $contratos
 * @property Planes[] $planes
 * @property Qr[] $qrs
 * @property SisSiniestro[] $sisSiniestros
 */
class RmClinica extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'rm_clinica';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['QRCode', 'codigo_clinica', 'deleted_at', 'updated_at', 'private_key'], 'default', 'value' => null],
            [['rs_instagram'], 'default', 'value' => ''],
            [['created_at', 'deleted_at', 'updated_at'], 'safe'],
            [['rif', 'nombre', 'estado', 'direccion', 'telefono', 'correo', 'estatus', 'webpage', 'rs_instagram', 'QRCode', 'codigo_clinica'], 'string'],
            [['private_key'], 'string', 'max' => 64],
            [['rif', 'nombre', 'estado', 'direccion', 'telefono', 'correo', 'codigo_clinica'], 'required'],

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
            'rif' => 'Rif',
            'nombre' => 'Nombre',
            'estado' => 'Estado',
            'direccion' => 'Direccion',
            'telefono' => 'Telefono',
            'correo' => 'Correo',
            'estatus' => 'Estatus',
            'webpage' => 'Webpage',
            'rs_instagram' => 'Instagram',
            'QRCode' => 'Qr Code',
            'codigo_clinica' => 'Codigo Clinica',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'private_key' => 'Private Key',
        ];
    }

    /**
     * Gets query for [[ClinicaContactos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClinicaContactos()
    {
        return $this->hasMany(ClinicaContactos::class, ['clinica_id' => 'id']);
    }

    /**
     * Gets query for [[Contratos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContratos()
    {
        return $this->hasMany(Contratos::class, ['clinica_id' => 'id']);
    }

    /**
     * Gets query for [[Planes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlanes()
    {
        return $this->hasMany(Planes::class, ['clinica_id' => 'id']);
    }

    /**
     * Gets query for [[Qrs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQrs()
    {
        return $this->hasMany(Qr::class, ['id_clinica' => 'id']);
    }

    /**
     * Gets query for [[SisSiniestros]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSisSiniestros()
    {
        return $this->hasMany(SisSiniestro::class, ['idclinica' => 'id']);
    }

}
