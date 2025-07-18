<?php
return [
    'adminEmail' => 'admin@example.com',
    'senderName' => 'Example.com mailer',
    'senderEmail' => 'noreply@example.com',
    'hail812/yii2-adminlte3' => [
        'pluginMap' => [
            'sweetalert2' => [
                'css' => 'sweetalert2-theme-bootstrap-4/bootstrap-4.min.css',
                'js' => 'sweetalert2/sweetalert2.min.js'
            ],
            'toastr' => [
                'css' => ['toastr/toastr.min.css'],
                'js' => ['toastr/toastr.min.js']
            ],
        ]
    ],
    /*'supabaseS3' => [
        'key' => 'd362dc45727d72679e9ac8aa8a654079', // Tu clave de acceso
        'secret' => '407fbce30827a5a3ad74c63c7188af70586bb2e00284d9f23796c25e969c3434', // Tu clave secreta
        'bucket' => 'usuarios', // El nombre de tu bucket
        // *** CAMBIO CLAVE AQUÍ: El endpoint ahora incluye el bucket ***
        'endpoint' => 'https://mzatwtlqduhcphhnvwvk.supabase.co/storage/v1/s3/usuarios',
        'region' => 'us-east-2', // Puedes usar cualquier región, Supabase no tiene regiones S3 reales.
    ],*/
    'supabase' => [
        'url' => 'https://mzatwtlqduhcphhnvwvk.supabase.co', // URL base de tu proyecto Supabase
        'anon_key' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im16YXR3dGxxZHVoY3BoaG52d3ZrIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTI1Mzc1ODgsImV4cCI6MjA2ODExMzU4OH0.kK5jQDqfeWz_x1WMvzO276B2ktr6frjDEI9WAn_9kmw', // Reemplaza con tu anon key REAL
        'bucket_name' => 'usuarios', // El nombre de tu bucket
    ],
];