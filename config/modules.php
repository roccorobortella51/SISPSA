<?php
return 
[
    'admin' => [
            'class' => 'mdm\admin\Module',
            // Configuración clave para el módulo 'admin' de mdmsoft
            'controllerMap' => [
                'assignment' => [
                    'class' => 'mdm\admin\controllers\AssignmentController',
                    // ¡MUY IMPORTANTE! Asegúrate de que esta sea la ruta correcta a tu modelo User
                    'userClassName' => 'app\models\User',
                    'idField' => 'id',
                    'usernameField' => 'username',
                ],
                'menu' => [
                    'class' => 'app\controllers\AdminMenuController',
                ],
            ],
            // Opcional: Define el layout para el módulo de administración.
            // Esto es útil si quieres que la interfaz de admin tenga un diseño específico.
            // 'layout' => 'left-menu', // Por ejemplo, 'left-menu' o 'top-menu'
            // 'mainLayout' => '@app/views/layouts/main.php', // Si quieres un layout diferente
        ],
        'gridview' => [
            'class' => '\kartik\grid\Module',
        ],
];