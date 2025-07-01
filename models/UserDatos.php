<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_datos".
 *
 * @property int $id
 * @property string $created_at
 * @property string|null $user_id
 * @property string|null $nombres
 * @property string|null $fechanac
 * @property string|null $sexo
 * @property string|null $selfie
 * @property string|null $telefono
 * @property string|null $estado
 * @property string|null $role
 * @property string|null $estatus
 * @property string|null $imagen_identificacion
 * @property string|null $qr
 * @property float|null $paso
 * @property string|null $video
/**
 * This is the model class for table "user_datos".
 *
 * @property int $id
 * @property string $created_at
 * @property string|null $user_id
 * @property string|null $nombres
 * @property string|null $fechanac
 * @property string|null $sexo
 * @property string|null $selfie
 * @property string|null $telefono
 * @property string|null $estado
 * @property string|null $role
 * @property string|null $estatus
 * @property string|null $imagen_identificacion
 * @property string|null $qr
 * @property float|null $paso
 * @property string|null $video
 * @property string|null $ciudad
 * @property string|null $municipio
 * @property string|null $parroquia
 * @property string|null $direccion
 * @property string|null $codigoValidacion
 * @property int|null $clinica_id
 * @property int|null $plan_id
 * @property string|null $apellidos
 * @property string|null $email
 * @property int|null $contrato_id
 * @property int|null $asesor_id
 * @property string|null $deleted_at
 * @property string|null $updated_at
 * @property string|null $ver_cedula
 * @property string|null $ver_foto
 * @property string|null $session_id
 * @property int|null $cedula
 * @property string|null $tipo_cedula
 * @property string|null $tipo_sangre
 * @property string|null $estatus_solvente
 * @property int|null $user_login_id este campo relaciona los datos del usuario con los datos del login y el rbac
 *
 * @property Beneficiarios[] $beneficiarios
 * @property Beneficiarios[] $beneficiarios0
 * @property ContactosEmergencia[] $contactosEmergencias
 * @property Contratos[] $contratos
 * @property DeclaracionDeSalud[] $declaracionDeSaluds
 * @property Notifications[] $notifications
 * @property Recibos[] $recibos
 * @property TransactionHistory[] $transactionHistories
 * @property User $userLogin
 */
class UserDatos extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_datos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombres', 'fechanac', 'sexo', 'selfie', 'telefono', 'estado', 'role', 'estatus', 'imagen_identificacion', 'qr', 'paso', 'video', 'ciudad', 'municipio', 'parroquia', 'direccion', 'codigoValidacion', 'clinica_id', 'plan_id', 'apellidos', 'email', 'contrato_id', 'asesor_id', 'deleted_at', 'updated_at', 'ver_cedula', 'ver_foto', 'session_id', 'cedula', 'tipo_cedula', 'tipo_sangre', 'estatus_solvente', 'user_login_id'], 'default', 'value' => null],
            [['user_id'], 'default', 'value' => 'gen_random_uuid()'],
            [['created_at', 'fechanac', 'deleted_at', 'updated_at'], 'safe'],
            [['user_id', 'nombres', 'sexo', 'selfie', 'telefono', 'estado', 'role', 'estatus', 'imagen_identificacion', 'qr', 'video', 'ciudad', 'municipio', 'parroquia', 'direccion', 'codigoValidacion', 'apellidos', 'email', 'ver_cedula', 'ver_foto', 'session_id', 'tipo_cedula', 'tipo_sangre', 'estatus_solvente'], 'string'],
            [['paso'], 'number'],
            [['clinica_id', 'plan_id', 'contrato_id', 'asesor_id', 'cedula', 'user_login_id'], 'default', 'value' => null],
            [['clinica_id', 'plan_id', 'contrato_id', 'asesor_id', 'cedula', 'user_login_id'], 'integer'],
            [['user_login_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_login_id' => 'id']],
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
            'nombres' => 'Nombres',
            'fechanac' => 'Fechanac',
            'sexo' => 'Sexo',
            'selfie' => 'Selfie',
            'telefono' => 'Telefono',
            'estado' => 'Estado',
            'role' => 'Role',
            'estatus' => 'Estatus',
            'imagen_identificacion' => 'Imagen Identificacion',
            'qr' => 'Qr',
            'paso' => 'Paso',
            'video' => 'Video',
            'ciudad' => 'Ciudad',
            'municipio' => 'Municipio',
            'parroquia' => 'Parroquia',
            'direccion' => 'Direccion',
            'codigoValidacion' => 'Codigo Validacion',
            'clinica_id' => 'Clinica ID',
            'plan_id' => 'Plan ID',
            'apellidos' => 'Apellidos',
            'email' => 'Email',
            'contrato_id' => 'Contrato ID',
            'asesor_id' => 'Asesor ID',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'ver_cedula' => 'Ver Cedula',
            'ver_foto' => 'Ver Foto',
            'session_id' => 'Session ID',
            'cedula' => 'Cedula',
            'tipo_cedula' => 'Tipo Cedula',
            'tipo_sangre' => 'Tipo Sangre',
            'estatus_solvente' => 'Estatus Solvente',
            'user_login_id' => 'User Login ID',
            'user_login_id' => 'User Login ID',
        ];
    }

    /**
     * Gets query for [[Beneficiarios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBeneficiarios()
    {
        return $this->hasMany(Beneficiarios::class, ['id_titular' => 'id']);
    }

    /**
     * Gets query for [[Beneficiarios0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBeneficiarios0()
    {
        return $this->hasMany(Beneficiarios::class, ['id_beneficiario' => 'id']);
    }

    /**
     * Gets query for [[ContactosEmergencias]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContactosEmergencias()
    {
        return $this->hasMany(ContactosEmergencia::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Contratos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContratos()
    {
        return $this->hasMany(Contratos::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[DeclaracionDeSaluds]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeclaracionDeSaluds()
    {
        return $this->hasMany(DeclaracionDeSalud::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Notifications]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNotifications()
    {
        return $this->hasMany(Notifications::class, ['user_datos_id' => 'id']);
    }

    /**
     * Gets query for [[Recibos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecibos()
    {
        return $this->hasMany(Recibos::class, ['id_titular' => 'id']);
    }

    /**
     * Gets query for [[TransactionHistories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTransactionHistories()
    {
     return $this->hasMany(TransactionHistory::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserLogin]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserLogin()
    {
        return $this->hasOne(User::class, ['id' => 'user_login_id']);
    }

}
