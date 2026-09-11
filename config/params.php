<?php
return [
    'adminEmail' => 'notification@sispsatest.com',
    'senderName' => 'SISPSA - Sistema de Protección de Salud',
    'senderEmail' => 'notification@sispsatest.com',

    // ============================================
    // EMAIL NOTIFICATION CONFIGURATION
    // ============================================
    'appName' => 'SISPSA - Sistema de Protección de Salud',
    'supportEmail' => 'sispsa.notificaciones@gmail.com',
    'notificationEmail' => 'sispsa.notificaciones@gmail.com',
    'user.passwordResetTokenExpire' => 3600, // 1 hour token expiration

    // ============================================
    // ADMIN LTE PLUGIN CONFIGURATION
    // ============================================
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

    // ============================================
    // SUPABASE STORAGE CONFIGURATION
    // ============================================
    'supabase' => [
        'url' => 'https://mzatwtlqduhcphhnvwvk.supabase.co',
        'anon_key' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im16YXR3dGxxZHVoY3BoaG52d3ZrIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTI1Mzc1ODgsImV4cCI6MjA2ODExMzU4OH0.kK5jQDqfeWz_x1WMvzO276B2ktr6frjDEI9WAn_9kmw',
        'bucket_name' => 'usuarios',
    ],

    // ============================================
    // WHATSAPP CONFIGURATION (Optional - For future use)
    // ============================================
    'whatsapp' => [
        'phone_number_id' => '', // Will be filled when WhatsApp API is set up
        'access_token' => '',     // Will be filled when WhatsApp API is set up
        'business_id' => '',      // Will be filled when WhatsApp API is set up
        'template_name' => 'appointment_confirmation',
        'template_language' => 'es',
    ],
];
