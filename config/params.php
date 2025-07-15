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
    'supabaseS3' => [
        'key' => 'd362dc45727d72679e9ac8aa8a654079', // Reemplaza esto
        'secret' => '407fbce30827a5a3ad74c63c7188af70586bb2e00284d9f23796c25e969c3434', // Reemplaza esto
        'bucket' => 'usuarios', // Reemplaza esto con el bucket que usarás
        'endpoint' => 'https://mzatwtlqduhcphhnvwvk.supabase.co/storage/v1/s3', // Reemplaza <project-ref>
        'region' => 'us-east-2', // Supabase no tiene regiones S3 reales, pero el SDK requiere una. Puedes usar cualquiera, como 'us-east-1'.
    ],
];