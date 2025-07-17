<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "corporativos".
 *
 * @property int $id
 * @property string $nombre
 * @property string|null $email
 * @property string|null $telefono
 * @property string|null $rif
 * @property string|null $estado
 * @property string|null $municipio
 * @property string|null $parroquia
 * @property string|null $direccion
 * @property string|null $codigo_asesor
 * @property string|null $lugar_registro
 * @property string|null $fecha_registro_mercantil
 * @property string|null $tomo_registro
 * @property string|null $folio_registro
 * @property string|null $domicilio_fiscal
 * @property string|null $contacto_nombre
 * @property string|null $contacto_cedula
 * @property string|null $contacto_telefono
 * @property string|null $contacto_cargo
 * @property string $estatus
 * @property string $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 *
 * @property RmClinica[] $clinicas
 * @property CorporativoClinica[] $corporativoClinicas
 * @property CorporativoUser[] $corporativoUsers
 * @property User[] $users
 */
class Corporativo extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'corporativos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['email', 'telefono', 'rif', 'estado', 'municipio', 'parroquia', 'direccion', 'codigo_asesor', 'lugar_registro', 'fecha_registro_mercantil', 'tomo_registro', 'folio_registro', 'domicilio_fiscal', 'contacto_nombre', 'contacto_cedula', 'contacto_telefono', 'contacto_cargo', 'updated_at', 'deleted_at'], 'default', 'value' => null],
            [['estatus'], 'default', 'value' => 'Activo'],
            [['nombre'], 'required'],
            [['direccion', 'domicilio_fiscal'], 'string'],
            [['fecha_registro_mercantil', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['nombre', 'email', 'lugar_registro', 'contacto_nombre'], 'string', 'max' => 255],
            [['telefono', 'rif', 'contacto_cedula', 'contacto_telefono'], 'string', 'max' => 20],
            [['estado', 'municipio', 'parroquia', 'contacto_cargo'], 'string', 'max' => 100],
            [['codigo_asesor', 'tomo_registro', 'folio_registro', 'estatus'], 'string', 'max' => 50],
            [['email'], 'unique'],
            [['nombre'], 'unique'],
            [['rif'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'email' => 'Email',
            'telefono' => 'Telefono',
            'rif' => 'Rif',
            'estado' => 'Estado',
            'municipio' => 'Municipio',
            'parroquia' => 'Parroquia',
            'direccion' => 'Direccion',
            'codigo_asesor' => 'Codigo Asesor',
            'lugar_registro' => 'Lugar Registro',
            'fecha_registro_mercantil' => 'Fecha Registro Mercantil',
            'tomo_registro' => 'Tomo Registro',
            'folio_registro' => 'Folio Registro',
            'domicilio_fiscal' => 'Domicilio Fiscal',
            'contacto_nombre' => 'Contacto Nombre',
            'contacto_cedula' => 'Contacto Cedula',
            'contacto_telefono' => 'Contacto Telefono',
            'contacto_cargo' => 'Contacto Cargo',
            'estatus' => 'Estatus',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    /**
     * Gets query for [[Clinicas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClinicas()
    {
        return $this->hasMany(RmClinica::class, ['id' => 'clinica_id'])->viaTable('corporativo_clinica', ['corporativo_id' => 'id']);
    }

    /**
     * Gets query for [[CorporativoClinicas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCorporativoClinicas()
    {
        return $this->hasMany(CorporativoClinica::class, ['corporativo_id' => 'id']);
    }

    /**
     * Gets query for [[CorporativoUsers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCorporativoUsers()
    {
        return $this->hasMany(CorporativoUser::class, ['corporativo_id' => 'id']);
    }

    /**
     * Gets query for [[Users]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])->viaTable('corporativo_user', ['corporativo_id' => 'id']);
    }

}
