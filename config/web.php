<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$modules = require __DIR__ . '/modules.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'admin'], // 'admin' y 'as access' deben estar en bootstrap
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@kvgrid' => '@vendor/kartik-v/yii2-grid', // el GridView
    ],
    'modules' => $modules,
    'components' => [
        'assetManager' => [ //SETTING FOR MATERIAL DASHBOARD THEME
		    'bundles' => [
			'genny3021\materialdashboard\web\MaterialDashboardAsset',
            ],
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager', // Correcto: Usando DbManager para RBAC en base de datos
            // Puedes configurar un valor de caché si lo necesitas para entornos de producción:
            // 'cache' => 'cache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true, // Habilitado para recordar al usuario
            'authTimeout' => 3600 * 24 * 30, // Tiempo de duración de la sesión (ej. 30 días si enableAutoLogin es true)
            // 'enableSession' => false, // Descomentar si usas token de autenticación sin sesión
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'oqoctAFA1HZuDUMmYC4NcfCiL_X_NFph',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // Puedes añadir tus reglas de URL aquí si necesitas URLs más amigables para tus propias rutas.
            ],
        ],

        // BLOQUE DE CONFIGURACIÓN DE I18N PARA KARTIK
        'i18n' => [
            'translations' => [
                'kvgrid' => [ // Categoría para los mensajes de Kartik GridView
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@kvgrid/messages', // Ubicación de los archivos de traducción de Kartik
                    'forceTranslation' => true, // Opcional, pero recomendado para asegurar que se traduzca
                ],
                // Si en el futuro tienes errores con 'kvdrange' o 'kvsfmsg',
                // también los añadirías aquí siguiendo el mismo patrón:
                // 'kvdrange' => [
                //     'class' => 'yii\i18n\PhpMessageSource',
                //     'basePath' => '@kvdrange/messages',
                //     'forceTranslation' => true,
                // ],
            ],
        ],
        'mpdf' => [
            'class' => 'kartik\mpdf\Pdf',
            'format' => \kartik\mpdf\Pdf::FORMAT_A4,
            'orientation' => \kartik\mpdf\Pdf::ORIENT_PORTRAIT,
            'destination' => \kartik\mpdf\Pdf::DEST_BROWSER,
        ],
        // FIN BLOQUE DE CONFIGURACIÓN DE I18N PARA KARTIK

    ],
    // 'as access' debe ir aquí, fuera de 'components'
    'as access' => [
        'class' => 'mdm\admin\components\AccessControl',
        'allowActions' => [
            'gii/*',
            'site/*',              // Permite acceso público a todas las acciones de SiteController (login, error, etc.)
            'debug/*',             // Permite acceso público a Debug Toolbar (solo para desarrollo)
            'admin/*',             // Temporalmente permitir acceso a todas las rutas de admin
        ]
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        // 'allowedIPs' => ['127.0.0.1', '::1'], // DESCOMENTA Y AJUSTA SI ES NECESARIO
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'generators' => [ // here
            'crud' => [ // generator name
                'class' => 'yii\gii\generators\crud\Generator', // generator class
                'templates' => [ // setting for our templates
                    'yii2-adminlte3' => '@vendor/hail812/yii2-adminlte3/src/gii/generators/crud/default' // template name => path to template
                ]
            ]
        ]
    ];

}

return $config;