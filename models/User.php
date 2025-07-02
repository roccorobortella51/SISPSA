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

    const STATUS_INACTIVE = 0;
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
            ['username', 'required'],
            ['username', 'string', 'max' => 255],
            ['email', 'required'],
            ['email', 'string', 'max' => 255],
            ['email', 'email'],
            ['password', 'string', 'min' => 5],
            ['roles', 'required'],
            ['roles', 'string'],
            ['status', 'in', 'range' => [1, self::STATUS_INACTIVE]],
        ];
    }

    public function beforeValidate()
    {
        if (isset($this->status)) {
            $this->status = (int) $this->status;
        }
        return parent::beforeValidate();
    }
     public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Si el valor recibido del formulario es 1 (del switch "encendido"),
            // lo cambiamos a 10 (STATUS_ACTIVE) para guardar en la base de datos.
            if ($this->status == 1) {
                $this->status = self::STATUS_ACTIVE; // O 10 directamente
            }
            // Si el valor es 0 (del switch "apagado"), ya es 0 (STATUS_INACTIVE),
            // no necesitamos cambiarlo, ya que coincide con el valor de la DB.

            return true;
        }
        return false;
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
