<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'pgsql:host=localhost;dbname=prueba',
    'username' => 'postgres',
    'password' => '123456',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',Sipsa123456*
];

/*return [
    'class' => 'yii\db\Connection',
    'dsn' => 'pgsql:host=aws-0-us-east-1.pooler.supabase.com;port=5432;dbname=postgres', // Reemplaza xxxxxxxxxxxxx con el valor de tu host
    'username' => 'postgres.rhpkljtjyblihoajhswg', // Reemplaza si tu usuario es diferente
    'password' => 'GxNPIOxKXbX2gIjw', // ¡MUY IMPORTANTE: Reemplaza con tu contraseña real!
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
    'password' => 'Exp9800654*', // ¡MUY IMPORTANTE: Reemplaza con tu contraseña real!
    'charset' => 'utf8',
    'enableSchemaCache' => !YII_DEBUG, // Cache de esquema para producción, deshabilitado en desarrollo
    'schemaCacheDuration' => 60 * 60, // Duración del cache en segundos (1 hora)
    'schemaCache' => 'cache', // Componente de cache a usar
    'attributes' => [
        PDO::ATTR_PERSISTENT => true // Opcional: Para conexiones persistentes (útil en algunos entornos)
    ],
];*/

