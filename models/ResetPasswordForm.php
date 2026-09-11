<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\base\InvalidParamException;
use app\models\User;

class ResetPasswordForm extends Model
{
    public $password;
    public $password_repeat;

    private $_user;

    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidParamException('El token de restablecimiento de contraseña no puede estar vacío.');
        }
        $this->_user = User::findByPasswordResetToken($token);
        if (!$this->_user) {
            throw new InvalidParamException('El token de restablecimiento de contraseña no es válido o ha expirado.');
        }
        parent::__construct($config);
    }

    public function rules()
    {
        return [
            ['password', 'required', 'message' => 'Por favor ingrese su nueva contraseña.'],
            ['password', 'string', 'min' => 8, 'tooShort' => 'La contraseña debe tener al menos 8 caracteres.'],
            [
                'password',
                'match',
                'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                'message' => 'La contraseña debe contener al menos una mayúscula, una minúscula y un número.'
            ],
            ['password_repeat', 'required', 'message' => 'Por favor confirme su nueva contraseña.'],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Las contraseñas no coinciden.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'password' => 'Nueva Contraseña',
            'password_repeat' => 'Confirmar Contraseña',
        ];
    }

    public function resetPassword()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = $this->_user;

        // Use the static method from User model
        $user->password_hash = User::setPassword($this->password);
        $user->password_reset_token = null;
        $user->password = $this->password;

        // Save the user
        if ($user->save(false)) {
            Yii::info('Password reset saved successfully for user ID: ' . $user->id, 'password-reset');
            return true;
        } else {
            Yii::error('Failed to save password reset: ' . json_encode($user->errors), 'password-reset');
            return false;
        }
    }
}
