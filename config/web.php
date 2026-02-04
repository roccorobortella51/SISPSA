<?php

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
                'dmstr\web\AdminLteAsset' => [ // O el AssetBundle correcto de AdminLTE
                    //'css' => [], // Comentado para no vaciar la lista de CSS originales de AdminLTE
                    'depends' => [ // Mantener dependencias
                        'yii\web\YiiAsset',
                        'yii\bootstrap4\BootstrapAsset', // Cambiado a Bootstrap 4
                        //'rmrevin\yii\fontawesome\AssetBundle', // Comentado FontAwesome para evitar error
                    ],
                ],
            ],
        ],
        'formatter' => [
            'defaultTimeZone' => 'America/Caracas', // ¡Esta es la línea clave!
            // Opcional: Puedes también configurar el locale si lo necesitas
            'locale' => 'es-VE',
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
                    'logFile' => '@runtime/logs/app.log', // Ruta donde se guardará el "reporte"
                    'maxFileSize' => 1024 * 2, // Tamaño máximo del archivo en KB
                    'maxLogFiles' => 5, // Número de archivos de log a mantener
                ],
            ],
        ],
        'db' => $db,

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
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
            //'gii/*',
            'site/login',
            'site/logout',
            'site/error',
            'site/tabs-data', // Permite acceso público a todas las acciones de SiteController (login, error, etc.)
            'debug/*',             // Permite acceso público a Debug Toolbar (solo para desarrollo)

            'reportes/*',
            // TEMPORARY: Add cuota web actions for testing (remove in production for security)
            'cuota-web/generar',
            'cuota-web/generar-mensual',
            'cuota-web/verificar-diario',
            'cuota-web/verificar-vencidas',
            'cuota-web/resumen-proximos-vencer',
            'cuota-web/resumen-atrasadas',
            'cuota-web/verificar-contratos-vencidos',
            'cuota-web/verificar-espera',
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
