<?php

/* @var $this yii\web\View */
/* @var $user app\models\User */
/* @var $resetLink string */

// Get user name safely
$userName = 'Usuario';
if (isset($user) && $user) {
    if (isset($user->userDatos) && $user->userDatos) {
        $userName = $user->userDatos->nombres;
    } elseif (isset($user->username)) {
        $userName = $user->username;
    }
}

$resetLink = isset($resetLink) ? $resetLink : '';
?>

===============================================
<?= Yii::$app->name ?> - Recuperación de Contraseña
Sistema Integral de Salud Programado
===============================================

Hola <?= $userName ?>,

Hemos recibido una solicitud para restablecer la contraseña de su cuenta en <?= Yii::$app->name ?>.

Si usted no realizó esta solicitud, puede ignorar este correo y su contraseña permanecerá sin cambios.

Para restablecer su contraseña, copie y pegue el siguiente enlace en su navegador:

<?= $resetLink ?>

⚠️ IMPORTANTE: Este enlace expirará en 1 hora por razones de seguridad.

Si tiene problemas para abrir el enlace, copie y pegue la URL completa en su navegador.

---
Este mensaje ha sido enviado automáticamente desde <?= Yii::$app->name ?>
Si necesita asistencia, contacte a nuestro equipo de soporte.

&copy; <?= date('Y') ?> <?= Yii::$app->name ?> - Sistema Integral de Salud Programado
Todos los derechos reservados