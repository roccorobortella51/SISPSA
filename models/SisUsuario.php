<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sis_usuario".
 *
 * @property int $idusuario
 * @property string|null $nom
 * @property string|null $ape
 * @property string|null $correo
 * @property string|null $celular
 * @property int|null $vercorreo
 * @property int|null $vercelular
 * @property string|null $codigo
 * @property string|null $clave
 * @property int|null $activo
 * @property int|null $estatus
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 * @property string|null $foto
 * @property string|null $roles
 * @property int|null $user_id
 *
 * @property SisConsulta[] $sisConsultas
 * @property User $user
 */
class SisUsuario extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sis_usuario';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nom', 'ape', 'correo', 'celular', 'codigo', 'clave', 'updated_at', 'deleted_at', 'foto', 'roles', 'user_id'], 'default', 'value' => null],
            [['estatus'], 'default', 'value' => 0],
            [['activo'], 'default', 'value' => 1],
            [['vercorreo', 'vercelular', 'activo', 'estatus', 'user_id'], 'default', 'value' => null],
            [['vercorreo', 'vercelular', 'activo', 'estatus', 'user_id'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['nom', 'ape', 'correo', 'celular', 'codigo', 'clave'], 'string', 'max' => 45],
            [['foto', 'roles'], 'string', 'max' => 256],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'idusuario' => 'Idusuario',
            'nom' => 'Nom',
            'ape' => 'Ape',
            'correo' => 'Correo',
            'celular' => 'Celular',
            'vercorreo' => 'Vercorreo',
            'vercelular' => 'Vercelular',
            'codigo' => 'Codigo',
            'clave' => 'Clave',
            'activo' => 'Activo',
            'estatus' => 'Estatus',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'foto' => 'Foto',
            'roles' => 'Roles',
            'user_id' => 'User ID',
        ];
    }

    /**
     * Gets query for [[SisConsultas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSisConsultas()
    {
        return $this->hasMany(SisConsulta::class, ['idmedico' => 'idusuario']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

}
