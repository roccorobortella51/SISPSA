<?php

namespace app\models;

//namespace mdm\admin\models;

use mdm\admin\components\Configs;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 *
 * @property UserProfile $profile
 * @property UserDatos $userDatos
 */
class User extends ActiveRecord implements IdentityInterface
{
    public $password;
    public $roles;

    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10; 


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Configs::instance()->userTable;
    }

    public function rules()
    {
        return [
            // 'username' y 'email' son siempre obligatorios
            [['username', 'email'], 'required'], 
            
            // 'password' solo es obligatorio al crear un nuevo usuario
            ['password', 'required', 'on' => 'create'],

            // El resto de tus reglas...
            ['username', 'string', 'max' => 255],
            ['email', 'string', 'max' => 255],
            ['email', 'email'],
            ['password', 'string', 'min' => 5], // Longitud mínima para la contraseña
            
            // Reglas para roles y status
            ['roles', 'safe'], // 'safe' para que se pueda cargar desde el formulario
         

            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED, 0, 1],
            'message' => 'El estatus "{value}" no es válido. Los valores permitidos son: Activo (10), Inactivo (9), Eliminado (0).'],
            // Si quieres que el mensaje sea más claro sobre lo que se espera *internamente*

            ['status', 'default', 'value' => self::STATUS_ACTIVE, 'on' => 'create'],
        ];
    }


    public function afterFind()
{
    parent::afterFind();
    // Convertimos el valor de la DB (10 o 9) a 1 o 0 para el SwitchInput
    if ($this->status === self::STATUS_ACTIVE) { // Si el status de DB es 10
        $this->status = 1; // Lo mostramos como 1 en el formulario
    } elseif ($this->status === self::STATUS_INACTIVE || $this->status === self::STATUS_DELETED) { // Si el status de DB es 9 o 0
        $this->status = 0; // Lo mostramos como 0 en el formulario
    }
    // IMPORTANTE: Esta conversión solo afecta la presentación en el formulario.
    // beforeSave() se encarga de convertirlo de vuelta para guardar en la DB.
}

    
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

           
            if ($this->isNewRecord || !empty($this->password)) {
                if (!empty($this->password)) { 
                    $this->password_hash = Yii::$app->security->generatePasswordHash($this->password);
                } else {
            
                }
            }
          

            // Generar auth_key siempre que sea un nuevo registro.
            if ($this->isNewRecord) {
                $this->auth_key = Yii::$app->security->generateRandomString();
            }

            if ($this->status == 1) {
                $this->status = self::STATUS_ACTIVE; // Convierte 1 a 10
            } elseif ($this->status == 0) {
                $this->status = self::STATUS_INACTIVE; // Convierte 0 a 9 (o a STATUS_DELETED si ese es tu deseo para el "off")
            }

            return true; // Continúa con el proceso de guardado
        }
        return false; // Detiene el guardado si el método padre falla
    }

    /**
     * Gets query for [[UserDatos]].
     *
     * @return \yii\db\ActiveQuery
     */
    
    public function getUserDatos()
    {
        return $this->hasOne(UserDatos::class, ['user_login_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
                'password_reset_token' => $token,
                'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     * @return string
     */
    public static function setPassword($password)
    {
        return Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public static function generateAuthKey()
    {
        return Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public static function getDb()
    {
        return Configs::userDb();
    }

    public function getAuthAssignment()
    {
        return $this->hasOne(AuthAssignment::class, ['user_id' => 'id']);
    }

    

}
