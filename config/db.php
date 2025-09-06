<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'pgsql:host=aws-0-us-east-1.pooler.supabase.com;dbname=postgres',
    'username' => 'postgres.rhpkljtjyblihoajhswg',
    'password' => 'GxNPIOxKXbX2gIjw',
    'charset' => 'utf8',
];
/*return [
    'class' => 'yii\db\Connection',
    'dsn' => 'pgsql:host=localhost;dbname=digitalw_sispsamarcos',
    'username' => 'digitalw_sispsaUser',
    //'username' => 'digitalw',
    'password' => 'Exp9800654*',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];

/*return [
    'class' => 'yii\db\Connection',
    'dsn' => 'pgsql:host=localhost;dbname=sispsa',
    'username' => 'postgres',
    'password' => 'postgres',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',*/

/*return [
    'class' => 'yii\db\Connection',
            'dsn' => 'pgsql:host=aws-0-us-east-1.pooler.supabase.com;port=5432;dbname=postgres',
            'username' => 'postgres.rhpkljtjyblihoajhswg',
            'password' => 'TU_CONTRASEÑA_AQUI', // ¡CAMBIA ESTO POR TU CONTRASEÑA REAL!
            'charset' => 'utf8',
            'schemaMap' => [
                'pgsql'=> [
                    'class'=>'yii\db\pgsql\Schema',
                    'defaultSchema' => 'public' // O el esquema que estés utilizando
                ]
            ]
];*/

/*return [
    'class' => 'yii\db\Connection',
    'dsn' => 'pgsql:host=aws-0-us-east-2.pooler.supabase.com;port=5432;dbname=postgres;sslmode=require', // Reemplaza xxxxxxxxxxxxx con el valor de tu host
    'username' => 'postgres.mzatwtlqduhcphhnvwvk', // Reemplaza si tu usuario es diferente
    'password' => 'Exp9800654*', // ¡MUY IMPORTANTE: Reemplaza con tu contraseña real!
    'charset' => 'utf8',
    'enableSchemaCache' => !YII_DEBUG, // Cache de esquema para producción, deshabilitado en desarrollo
    'schemaCacheDuration' => 60 * 60, // Duración del cache en segundos (1 hora)
    'schemaCache' => 'cache', // Componente de cache a usar
    'attributes' => [
        PDO::ATTR_PERSISTENT => true // Opcional: Para conexiones persistentes (útil en algunos entornos)
    ],
];*/

/*return [
    'class' => 'yii\db\Connection',
    'dsn' => 'pgsql:host=aws-0-us-east-2.pooler.supabase.com;port=6543;dbname=postgres', // Reemplaza xxxxxxxxxxxxx con el valor de tu host
    'username' => 'postgres.mzatwtlqduhcphhnvwvk', // Reemplaza si tu usuario es diferente
    'password' => 'Exp9800654*',
    'charset' => 'utf8',
    'schemaMap' => [
        'pgsql' => [
            'class' => 'yii\db\pgsql\Schema',
            'defaultSchema' => 'public'
        ]
    ],
];*/







