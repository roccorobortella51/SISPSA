<?php
// config/web.php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$modules = require __DIR__ . '/modules.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'admin'], // 'admin' y 'as access' deben estar en bootstrap
    'language' => 'es',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@kvgrid' => '@vendor/kartik-v/yii2-grid', // el GridView
    ],
    'modules' => $modules,
    'components' => [
        
         'assetManager' => [
            'bundles' => [
                'dmstr\\web\\AdminLteAsset' => [ // O el AssetBundle correcto de AdminLTE
                    //'css' => [], // Comentado para no vaciar la lista de CSS originales de AdminLTE
                    'depends' => [ // Mantener dependencias
                        'yii\\web\\YiiAsset',
                        'yii\\bootstrap4\\BootstrapAsset', // Cambiado a Bootstrap 4
                        //'rmrevin\\yii\\fontawesome\\AssetBundle', // Comentado FontAwesome para evitar error
                    ],
                ],
            ],
        ],
        'formatter' => [
            'defaultTimeZone' => 'America/Caracas', // ¡Zona horaria correcta!
            'dateFormat' => 'php:d-m-Y',
            'datetimeFormat' => 'php:d-m-Y H:i:s',
            'timeFormat' => 'php:H:i:s',
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'SECRET_KEY_HERE',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
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
        
        // ⭐ SOLUCIÓN CLAVE: Falta el componente authManager para RBAC
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            // Opcional: Define los roles predeterminados si los usa
            // 'defaultRoles' => ['guest'], 
        ],
        
        // ⭐ SOLUCIÓN PARA EL ERROR kvgrid: Agregar configuración i18n
        'i18n' => [
            'translations' => [
                'kvbase' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@vendor/kartik-v/yii2-grid/messages',
                    'forceTranslation' => true,
                ],
                'kvgrid' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@vendor/kartik-v/yii2-grid/messages',
                    'forceTranslation' => true,
                ],
                'kvdetail' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@vendor/kartik-v/yii2-grid/messages',
                    'forceTranslation' => true,
                ],
                'kvexport' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@vendor/kartik-v/yii2-grid/messages',
                    'forceTranslation' => true,
                ],
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                    'fileMap' => [
                        'app' => 'app.php',
                        'app/error' => 'error.php',
                    ],
                ],
            ],
        ],
        
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false, // Requiere .htaccess correcto
            'rules' => [
                // Agregue sus reglas personalizadas aquí
            ],
        ],
        
    ],
    
    // BLOQUE RBAC DE mdm\admin (Exclusión de cuota-web/* para evitar el error anterior)
    'as access' => [
        'class' => 'mdm\admin\components\AccessControl',
        'allowActions' => [
            // Permite el acceso a todas las acciones en el controlador cuota-web
            'cuota-web/*', 
            
            // Otras rutas que son siempre permitidas (ejemplos)
            'site/*',
        ]
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // 'allowedIPs' => ['127.0.0.1', '::1'], 
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'generators' => [ // here...
        ]
    ];
}

return $config;