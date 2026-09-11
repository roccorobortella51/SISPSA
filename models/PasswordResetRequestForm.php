<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;

class PasswordResetRequestForm extends Model
{
    public $email;

    public function rules()
    {
        return [
            ['email', 'trim'],
            ['email', 'required', 'message' => 'Por favor ingrese su correo electrónico.'],
            ['email', 'email', 'message' => 'El formato del correo electrónico no es válido.'],
            [
                'email',
                'exist',
                'targetClass' => '\app\models\User',
                'filter' => ['status' => User::STATUS_ACTIVE],
                'message' => 'No existe un usuario activo con este correo electrónico.'
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'email' => 'Correo Electrónico',
        ];
    }

    public function sendEmail()
    {
        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'email' => $this->email,
        ]);

        if (!$user) {
            Yii::error('User not found with email: ' . $this->email, 'password-reset');
            return false;
        }

        Yii::info('User found: ID ' . $user->id . ', Username: ' . $user->username, 'password-reset');

        // Check if token exists and is still valid
        if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
            Yii::info('Generating new password reset token for user: ' . $user->id, 'password-reset');
            $user->generatePasswordResetToken();
            if (!$user->save(false)) {
                Yii::error('Failed to save user with new token: ' . json_encode($user->errors), 'password-reset');
                return false;
            }
            Yii::info('Token generated and saved: ' . $user->password_reset_token, 'password-reset');
        } else {
            Yii::info('Using existing valid token: ' . $user->password_reset_token, 'password-reset');
        }

        // CRITICAL: Check if token exists after generation
        if (empty($user->password_reset_token)) {
            Yii::error('Password reset token is empty after generation!', 'password-reset');
            return false;
        }

        $resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
        Yii::info('Reset link generated: ' . $resetLink, 'password-reset');

        // DEBUG: Verify the link is not empty
        if (empty($resetLink)) {
            Yii::error('Reset link is empty!', 'password-reset');
            return false;
        }

        try {
            // IMPORTANT FIX: Pass variables as array to renderFile
            $htmlBody = Yii::$app->view->renderFile(
                '@app/mail/passwordResetToken-html.php',
                [
                    'user' => $user,
                    'resetLink' => $resetLink,
                ]
            );

            $textBody = Yii::$app->view->renderFile(
                '@app/mail/passwordResetToken-text.php',
                [
                    'user' => $user,
                    'resetLink' => $resetLink,
                ]
            );

            // DEBUG: Check if the link is in the rendered HTML
            if (strpos($htmlBody, $resetLink) === false) {
                Yii::error('Reset link NOT found in rendered HTML!', 'password-reset');
                Yii::error('HTML Body: ' . substr($htmlBody, 0, 500), 'password-reset');
            } else {
                Yii::info('Reset link found in rendered HTML', 'password-reset');
            }

            $result = Yii::$app->mailer->compose()
                ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' - Sistema de Soporte'])
                ->setTo($this->email)
                ->setSubject('Recuperación de Contraseña - ' . Yii::$app->name)
                ->setHtmlBody($htmlBody)
                ->setTextBody($textBody)
                ->send();

            if ($result) {
                Yii::info('Email sent successfully to: ' . $this->email, 'password-reset');
            } else {
                Yii::error('Email send returned false for: ' . $this->email, 'password-reset');
            }

            return $result;
        } catch (\Exception $e) {
            Yii::error('Exception sending password reset email: ' . $e->getMessage() . "\n" . $e->getTraceAsString(), 'password-reset');
            return false;
        }
    }
}
