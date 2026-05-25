<?php

use yii\helpers\Html;
use app\components\UserHelper;

// Get clinic name for the logged user
$clinicaName = '';
$rolesWithClinica = [
    "Administrador-clinica",
    "COORDINADOR-CLINICA",
    "CONTROL DE CITAS",
    "ADMISIÓN",
    "ATENCIÓN",
    "OPERACIONES-BOSQUE",
    "GERENTE-CLINICA",
    "INFORMACIÓN",
    "ADMINISTRACION-CLINICA"
];

if (in_array(UserHelper::getMyRol(), $rolesWithClinica)) {
    $clinicaName = UserHelper::getMyClinicaName();
}

// Get user name for welcome message - Get from UserDatos relation
$userName = 'Usuario'; // Default fallback
$userIdentity = Yii::$app->user->identity;

if ($userIdentity) {
    // Try to get the user's full name from UserDatos relation
    if ($userIdentity->userDatos) {
        $nombres = trim($userIdentity->userDatos->nombres ?? '');
        $apellidos = trim($userIdentity->userDatos->apellidos ?? '');

        if (!empty($nombres) && !empty($apellidos)) {
            $userName = $nombres . ' ' . $apellidos;
        } elseif (!empty($nombres)) {
            $userName = $nombres;
        } elseif (!empty($apellidos)) {
            $userName = $apellidos;
        } else {
            // Fallback to username if no names found
            $userName = $userIdentity->username ?? 'Usuario';
        }
    } else {
        // Fallback to username if UserDatos doesn't exist
        $userName = $userIdentity->username ?? 'Usuario';
    }
}

// Extract first name only for a more personal greeting (optional)
$firstName = explode(' ', trim($userName))[0];

// Determine time-based greeting
date_default_timezone_set('America/Caracas');
$hour = date('H');
$greeting = '';
$greetingIcon = '';

if ($hour >= 5 && $hour < 12) {
    $greeting = 'Buenos días';
    $greetingIcon = 'fas fa-sun';
} elseif ($hour >= 12 && $hour < 14) {
    $greeting = 'Buen mediodía';
    $greetingIcon = 'fas fa-cloud-sun';
} elseif ($hour >= 14 && $hour < 19) {
    $greeting = 'Buenas tardes';
    $greetingIcon = 'fas fa-cloud-sun';
} else {
    $greeting = 'Buenas noches';
    $greetingIcon = 'fas fa-moon';
}

// Comprehensive battery of positive messages
$positiveMessages = [
    // Motivational messages
    '🌟 ¡Tú puedes lograr todo lo que te propongas hoy!',
    '💪 Cada día es una nueva oportunidad para brillar',
    '🎯 Mantén el enfoque y alcanzarás tus metas',
    '🚀 Pequeños progresos llevan a grandes resultados',
    '⭐ Hoy es un gran día para hacer la diferencia',
    '🌈 La actitud positiva atrae resultados positivos',
    '🏆 La excelencia no es un acto, sino un hábito',
    '💫 Cree en ti y todo será posible',

    // Health & wellness messages
    '💙 Tu salud es tu mayor inversión, cuídala hoy',
    '🫀 Cada paciente merece tu mejor atención',
    '🤝 La empatía es el corazón de la medicina',
    '🏥 Brindar salud es el más noble de los oficios',
    '💚 Una sonrisa puede ser el mejor medicamento',
    '⭐ La prevención es la mejor cura',
    '🎯 Cada vida que tocas hoy, importa',
    '🌟 La calidad en el cuidado marca la diferencia',

    // Productivity messages
    '⚡ La organización es la clave del éxito',
    '📋 Prioriza, enfócate y ejecuta con excelencia',
    '🎯 Hoy puedes lograr más de lo que imaginas',
    '💼 Cada tarea completada es un paso adelante',
    '🚀 La eficiencia comienza con una buena actitud',
    '📊 El orden en tu trabajo refleja excelencia',
    '✨ Hazlo bien, hazlo con pasión',
    '🏆 La dedicación diaria construye grandes resultados',

    // Inspirational messages
    '🌅 Cada amanecer trae nuevas oportunidades',
    '💫 Hoy puedes inspirar a alguien con tu trabajo',
    '⭐ Pequeñas acciones generan grandes cambios',
    '🌈 La esperanza y la salud van de la mano',
    '🌟 Tu esfuerzo de hoy construye un mejor mañana',
    '💙 La paciencia y la perseverancia lo logran todo',
    '🎯 El éxito es la suma de pequeños esfuerzos',
    '🚀 Cree en el poder de tu trabajo diario',

    // Gratitude & positivity
    '🙏 Agradece por un nuevo día para servir',
    '💖 La gratitud transforma la perspectiva',
    '✨ Hoy es un regalo, por eso se llama presente',
    '⭐ Valoriza cada momento en tu labor diaria',
    '🌻 La felicidad se encuentra en servir a otros',
    '💫 Tu trabajo tiene un propósito noble',
    '🎉 Celebra cada pequeño logro del día',
    '🌟 La mejor forma de predecir el futuro es crearlo',

    // Patient care focused
    '💙 Cada paciente merece calidez y respeto',
    '🤝 La confianza se construye con cada interacción',
    '⭐ Tu dedicación cambia vidas',
    '🏥 La excelencia médica comienza contigo',
    '💚 Escuchar es el primer paso para sanar',
    '🎯 La precisión y el cuidado son tu sello',
    '🌟 Trata a cada paciente como a un familiar',
    '💫 La compasión es tan importante como la cura',

    // Teamwork messages
    '🤝 Juntos logramos más, el trabajo en equipo suma',
    '⭐ El respeto mutuo fortalece nuestro servicio',
    '🚀 La colaboración nos hace más fuertes',
    '💙 Un equipo unido brinda mejor atención',
    '🎯 Compartir conocimiento nos enriquece a todos',
    '🌟 Apoyarnos mutuamente nos hace brillar',
    '💫 La sinergia del equipo multiplica resultados',
    '🏆 El éxito compartido sabe mejor',

    // Daily motivation
    '⚡ Activa tu mejor versión hoy',
    '💪 Tú eres la diferencia en la vida de alguien',
    '🎯 Enfócate en soluciones, no en problemas',
    '🌟 Hazlo con amor o no lo hagas',
    '🚀 La excelencia no tiene techo',
    '💙 Cada día es una página en blanco',
    '✨ Escribe hoy una historia de éxito',
    '⭐ La constancia vence lo que la genialidad no alcanza',

    // Short & impactful
    '💪 ¡A darlo todo hoy!',
    '🌟 Brilla con luz propia',
    '🚀 Rumbo a la excelencia',
    '💙 Salud con calidad y calidez',
    '🎯 Metas claras, resultados extraordinarios',
    '⭐ Hoy será un gran día',
    '💫 Haz que cada minuto cuente',
    '🏆 La mejor versión de ti',

    // Professional pride
    '💼 Tu trabajo salva vidas, eso es invaluable',
    '🏥 La medicina es arte, ciencia y corazón',
    '⭐ Tu profesionalismo hace la diferencia',
    '🌟 Eres parte esencial del equipo de salud',
    '💙 La excelencia médica es tu sello personal',
    '🎯 Cada detalle cuenta en la atención médica',
    '🚀 Lidera con el ejemplo hoy',
    '💫 La calidad empieza contigo'
];

// Select random message
$randomMessage = $positiveMessages[array_rand($positiveMessages)];

?>
<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light" style="background: linear-gradient(135deg, #009efb 0%, #0078d4 100%); box-shadow: 0 2px 10px rgba(0,0,0,0.1); width:auto; min-height: 56px; position: relative;">
    <!-- Left navbar links -->
    <ul class="navbar-nav align-items-center">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button" style="color: white; transition: all 0.3s ease; padding: 0.5rem 1rem;">
                <i class="fas fa-bars fa-lg"></i>
            </a>
        </li>
    </ul>

    <!-- CENTER WELCOME MESSAGE -->
    <div style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); z-index: 10;">
        <div class="welcome-message" style="
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 6px 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid rgba(255,255,255,0.25);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            white-space: nowrap;
        ">
            <div style="
                width: 28px;
                height: 28px;
                background: rgba(255,215,0,0.2);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s ease;
            ">
                <i class="<?= $greetingIcon ?>" style="color: #FFD700; font-size: 14px;"></i>
            </div>
            <div style="display: flex; align-items: baseline; gap: 6px;">
                <span style="color: rgba(255,255,255,0.95); font-size: 12px; font-weight: 500;">
                    <?= $greeting ?>,
                </span>
                <span style="color: white; font-size: 13px; font-weight: 600;">
                    <?= Html::encode($firstName) ?>
                </span>
                <span style="color: #FFD700; font-size: 12px; font-weight: 500;" class="welcome-message-text">
                    • <?= $randomMessage ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto align-items-center">
        <!-- Clinic Name Display - Premium Glowing Badge with Red Medical Icon -->
        <?php if (!empty($clinicaName)): ?>
            <li class="nav-item" style="display: flex; align-items: center;">
                <div class="clinic-premium" style="
                background: linear-gradient(135deg, rgba(255,215,0,0.2), rgba(255,165,0,0.15));
                border-radius: 12px;
                padding: 5px 20px 5px 15px;
                display: flex;
                align-items: center;
                gap: 12px;
                border: 1px solid rgba(255,215,0,0.5);
                box-shadow: 0 0 15px rgba(255,215,0,0.3);
                transition: all 0.3s ease;
                cursor: default;
                position: relative;
                overflow: hidden;
            ">
                    <!-- Animated shine effect -->
                    <div style="
                    position: absolute;
                    top: 0;
                    left: -100%;
                    width: 100%;
                    height: 100%;
                    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
                    transition: left 0.5s ease;
                " class="shine"></div>

                    <!-- RED Icon Container with Modern Clinic Building -->
                    <div style="
                    width: 42px;
                    height: 42px;
                    background: linear-gradient(135deg, #e74c3c, #c0392b);
                    border-radius: 12px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
                    position: relative;
                    transition: all 0.3s ease;
                ">
                        <i class="fas fa-clinic-medical" style="color: white; font-size: 22px;"></i>
                    </div>

                    <div>
                        <div style="font-size: 9px; color: rgba(255,215,0,0.9); letter-spacing: 1px; font-weight: 700; text-transform: uppercase; line-height: 1.2;">
                            <i class="fas fa-map-marker-alt mr-1"></i>UBICACIÓN ACTUAL
                        </div>
                        <div style="font-size: 13px; color: #ffd700; font-weight: 700; text-shadow: 0 1px 2px rgba(0,0,0,0.2); line-height: 1.3;">
                            <i class="fas fa-building mr-1"></i><?= Html::encode($clinicaName) ?>
                        </div>
                    </div>
                </div>
            </li>
        <?php endif; ?>

        <!-- Modern Separator -->
        <?php if (!empty($clinicaName)): ?>
            <li class="nav-item" style="display: flex; align-items: center;">
                <div style="height: 40px; width: 2px; background: linear-gradient(to bottom, transparent, rgba(255,255,255,0.5), transparent);"></div>
            </li>
        <?php endif; ?>

        <!-- Enhanced Logout Button - Perfectly Centered -->
        <li class="nav-item" style="display: flex; align-items: center;">
            <?= Html::a(
                '<i class="fas fa-sign-out-alt" style="color: white; font-size: 18px;"></i> <span style="margin-left: 5px; display: none; @media (min-width: 768px) { display: inline; }">Salir</span>',
                ['/site/logout'],
                [
                    'data-method' => 'post',
                    'class' => 'nav-link logout-btn',
                    'style' => 'transition: all 0.3s ease; padding: 0.5rem 1rem; border-radius: 8px; display: flex; align-items: center; gap: 5px;',
                    'title' => 'Cerrar Sesión'
                ]
            ) ?>
        </li>
    </ul>
</nav>

<style>
    /* Fix navbar alignment */
    .main-header .navbar-nav {
        display: flex;
        align-items: center;
    }

    .main-header .nav-item {
        display: flex;
        align-items: center;
    }

    /* Premium badge hover effect */
    .clinic-premium {
        animation: glow 2s ease-in-out infinite;
    }

    .clinic-premium:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 0 25px rgba(255, 215, 0, 0.5) !important;
    }

    .clinic-premium:hover .shine {
        left: 100% !important;
    }

    /* Red icon hover effect */
    .clinic-premium:hover div:first-child {
        transform: scale(1.05);
        box-shadow: 0 6px 16px rgba(231, 76, 60, 0.6);
    }

    @keyframes glow {

        0%,
        100% {
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.3);
            border-color: rgba(255, 215, 0, 0.5);
        }

        50% {
            box-shadow: 0 0 25px rgba(255, 215, 0, 0.6);
            border-color: rgba(255, 215, 0, 0.8);
        }
    }

    /* Logout button hover effect */
    .logout-btn:hover {
        background-color: rgba(231, 76, 60, 0.8) !important;
        transform: translateX(3px);
    }

    /* Remove default nav-link padding for better centering */
    .main-header .navbar-nav .nav-link {
        line-height: 1;
    }

    /* Pulse animation for the red icon */
    @keyframes redPulse {

        0%,
        100% {
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
        }

        50% {
            box-shadow: 0 4px 20px rgba(231, 76, 60, 0.8);
        }
    }

    .clinic-premium div:first-child {
        animation: redPulse 2s ease-in-out infinite;
    }

    /* Welcome message hover effect */
    .welcome-message {
        transition: all 0.3s ease;
    }

    .welcome-message:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px);
    }

    /* Fade animation for message changes */
    @keyframes fadeInOut {
        0% {
            opacity: 0;
            transform: translateY(-5px);
        }

        10% {
            opacity: 1;
            transform: translateY(0);
        }

        90% {
            opacity: 1;
            transform: translateY(0);
        }

        100% {
            opacity: 0;
            transform: translateY(5px);
        }
    }

    .message-transition {
        animation: fadeInOut 1s ease-in-out;
    }

    /* Responsive adjustments */
    @media (max-width: 1200px) {
        .welcome-message-text {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
        }
    }

    @media (max-width: 992px) {
        .welcome-message-text {
            display: none !important;
        }
    }

    @media (max-width: 768px) {
        .clinic-premium {
            padding: 5px 12px !important;
        }

        .clinic-premium div:first-child {
            width: 36px;
            height: 36px;
        }

        .clinic-premium div:first-child i {
            font-size: 18px !important;
        }

        .clinic-premium div:last-child div:first-child {
            font-size: 8px;
        }

        .clinic-premium div:last-child div:last-child {
            font-size: 11px;
        }

        .logout-btn span {
            display: none;
        }

        .welcome-message {
            padding: 4px 12px !important;
        }

        .welcome-message div:first-child {
            width: 24px;
            height: 24px;
        }

        .welcome-message div:first-child i {
            font-size: 12px;
        }

        .welcome-message span {
            font-size: 11px !important;
        }
    }
</style>

<script>
    // Battery of positive messages (same as PHP array)
    const positiveMessages = [
        '🌟 ¡Tú puedes lograr todo lo que te propongas hoy!',
        '💪 Cada día es una nueva oportunidad para brillar',
        '🎯 Mantén el enfoque y alcanzarás tus metas',
        '🚀 Pequeños progresos llevan a grandes resultados',
        '⭐ Hoy es un gran día para hacer la diferencia',
        '🌈 La actitud positiva atrae resultados positivos',
        '🏆 La excelencia no es un acto, sino un hábito',
        '💫 Cree en ti y todo será posible',
        '💙 Tu salud es tu mayor inversión, cuídala hoy',
        '🫀 Cada paciente merece tu mejor atención',
        '🤝 La empatía es el corazón de la medicina',
        '🏥 Brindar salud es el más noble de los oficios',
        '💚 Una sonrisa puede ser el mejor medicamento',
        '⭐ La prevención es la mejor cura',
        '🎯 Cada vida que tocas hoy, importa',
        '🌟 La calidad en el cuidado marca la diferencia',
        '⚡ La organización es la clave del éxito',
        '📋 Prioriza, enfócate y ejecuta con excelencia',
        '🎯 Hoy puedes lograr más de lo que imaginas',
        '💼 Cada tarea completada es un paso adelante',
        '🚀 La eficiencia comienza con una buena actitud',
        '📊 El orden en tu trabajo refleja excelencia',
        '✨ Hazlo bien, hazlo con pasión',
        '🏆 La dedicación diaria construye grandes resultados'
    ];

    // Get the message element
    let messageElement = document.querySelector('.welcome-message-text');
    let currentIndex = Math.floor(Math.random() * positiveMessages.length);

    // Function to change message with animation
    function changeRandomMessage() {
        if (!messageElement) return;

        let newIndex;
        do {
            newIndex = Math.floor(Math.random() * positiveMessages.length);
        } while (newIndex === currentIndex && positiveMessages.length > 1);

        currentIndex = newIndex;
        const newMessage = positiveMessages[currentIndex];

        // Add fade animation
        messageElement.style.animation = 'fadeInOut 0.8s ease-in-out';

        // Change message after animation starts
        setTimeout(() => {
            messageElement.textContent = ' • ' + newMessage;
        }, 200);

        // Remove animation after completion
        setTimeout(() => {
            messageElement.style.animation = '';
        }, 1000);
    }

    // Random interval between 2 minutes (120000 ms) and 3 minutes (180000 ms)
    function scheduleNextChange() {
        const randomInterval = Math.floor(Math.random() * (180000 - 120000 + 1) + 120000);
        setTimeout(() => {
            changeRandomMessage();
            scheduleNextChange();
        }, randomInterval);
    }

    // Start the rotation when page loads
    if (messageElement && positiveMessages.length > 0) {
        document.addEventListener('DOMContentLoaded', () => {
            scheduleNextChange();
        });
    }

    // Add shine animation on hover
    document.querySelectorAll('.clinic-premium').forEach(badge => {
        badge.addEventListener('mouseenter', function() {
            const shine = this.querySelector('.shine');
            if (shine) {
                shine.style.left = '100%';
                setTimeout(() => {
                    shine.style.left = '-100%';
                }, 500);
            }
        });
    });
</script>