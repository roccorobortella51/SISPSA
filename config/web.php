<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$modules = require __DIR__ . '/modules.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'app\\controllers',  // Tells Yii where to find controllers
    'bootstrap' => ['log', 'admin'],
    'language' => 'es',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@kvgrid' => '@vendor/kartik-v/yii2-grid',
    ],
    'modules' => $modules,
    'components' => [

        'assetManager' => [
            'bundles' => [
                'dmstr\web\AdminLteAsset' => [
                    'depends' => [
                        'yii\web\YiiAsset',
                        'yii\bootstrap4\BootstrapAsset',
                    ],
                ],
            ],
        ],

        'formatter' => [
            'defaultTimeZone' => 'America/Caracas',
            'locale' => 'es-VE',
        ],

        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],

        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'authTimeout' => 3600 * 24 * 30,
        ],

        'request' => [
            'cookieValidationKey' => 'oqoctAFA1HZuDUMmYC4NcfCiL_X_NFph',
        ],

        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],

        'errorHandler' => [
            'errorAction' => 'site/error',
        ],

        'mailer' => [
            'class' => 'yii\symfonymailer\Mailer',
            'useFileTransport' => false,
            'transport' => [
                'scheme' => 'smtp',
                'host' => 'smtp.gmail.com',
                'username' => 'sispsa.notificaciones@gmail.com',
                'password' => 'wdeqdspikycwtjqf',
                'port' => 465,
                'encryption' => 'ssl',
            ],
        ],

        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logFile' => '@runtime/logs/app.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 5,
                ],
            ],
        ],

        'db' => $db,

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // RTON Report Routes
                'rton-report' => 'rton-report/index',
                'rton-report/export-excel' => 'rton-report/export-excel',
                'rton-report/export-csv' => 'rton-report/export-csv',

                // Cuota Web Controller Routes
                'v2/cuota/generar' => 'cuota-web/generar',
                'v2/cuota/generar-mensual' => 'cuota-web/generar-mensual',
                'v2/cuota/verificar-diario' => 'cuota-web/verificar-diario',
                'v2/cuota/verificar-vencidas' => 'cuota-web/verificar-vencidas',
                'v2/cuota/resumen-proximos-vencer' => 'cuota-web/resumen-proximos-vencer',
                'v2/cuota/resumen-atrasadas' => 'cuota-web/resumen-atrasadas',
                'v2/cuota/verificar-contratos-vencidos' => 'cuota-web/verificar-contratos-vencidos',
                'v2/cuota/verificar-espera' => 'cuota-web/verificar-espera',
                'reportes/comisiones' => 'reportes/comisiones',
                'reportes/get-comisiones-detail' => 'reportes/get-comisiones-detail',
            ],
        ],

        // BLOQUE DE CONFIGURACIÓN DE I18N PARA KARTIK
        'i18n' => [
            'translations' => [
                'kvgrid' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@kvgrid/messages',
                    'forceTranslation' => true,
                ],
            ],
        ],

        'mpdf' => [
            'class' => 'kartik\mpdf\Pdf',
            'format' => \kartik\mpdf\Pdf::FORMAT_A4,
            'orientation' => \kartik\mpdf\Pdf::ORIENT_PORTRAIT,
            'destination' => \kartik\mpdf\Pdf::DEST_BROWSER,
        ],

    ],

    // CONTROLLER MAP - This goes OUTSIDE of components (at the same level as 'components')
    'controllerMap' => [
        'rton-report' => [
            'class' => 'app\controllers\RTONReportController',
        ],
    ],

    'as access' => [
        'class' => 'mdm\admin\components\AccessControl',
        'allowActions' => [
            'site/login',
            'site/logout',
            'site/error',
            'site/tabs-data',
            'debug/*',
            'reportes/*',
            'rton-report/*',  // Allows RTON report access
            'cuota-web/generar',
            'cuota-web/generar-mensual',
            'cuota-web/verificar-diario',
            'cuota-web/verificar-vencidas',
            'cuota-web/resumen-proximos-vencer',
            'cuota-web/resumen-atrasadas',
            'cuota-web/verificar-contratos-vencidos',
            'cuota-web/verificar-espera',
            'site/test-email',
            'site/test-notification',
        ]
    ],

    'params' => $params,
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'generators' => [
            'crud' => [
                'class' => 'yii\gii\generators\crud\Generator',
                'templates' => [
                    'yii2-adminlte3' => '@vendor/hail812/yii2-adminlte3/src/gii/generators/crud/default'
                ]
            ]
        ]
    ];
}

return $config;
