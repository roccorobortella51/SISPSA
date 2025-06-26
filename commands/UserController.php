<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\User; // Asegúrate de que esta sea la ruta correcta a tu modelo User

/**
 * Creates a new user in the database.
 * Crea un nuevo usuario en la base de datos.
 */
class UserController extends Controller
{
    /**
     * This command creates a new user.
     * Este comando crea un nuevo usuario.
     *
     * @param string $username The username for the new user.
     * El nombre de usuario para el nuevo usuario.
     * @param string $email The email for the new user.
     * El email para el nuevo usuario.
     * @param string $password The password for el nuevo user.
     * La contraseña para el nuevo usuario.
     * @return int Exit code. Código de salida.
     */
    public function actionCreate($username, $email, $password)
    {
        $user = new User();
        $user->username = $username;
        $user->email = $email;
        // Establece la contraseña hasheada
        $user->setPassword($password);
        // Genera la clave de autenticación
        $user->generateAuthKey();
        // Genera un token de acceso (útil para 'recordarme' o APIs)
        $user->generatePasswordResetToken();

        if ($user->save()) {
            $this->stdout("Usuario '{$username}' creado exitosamente.\n");
            return self::EXIT_CODE_NORMAL;
        } else {
            $this->stderr("Error al crear el usuario:\n");
            foreach ($user->errors as $attribute => $errors) {
                foreach ($errors as $error) {
                    $this->stderr(" - {$attribute}: {$error}\n");
                }
            }
            return self::EXIT_CODE_ERROR;
        }
    }
}
