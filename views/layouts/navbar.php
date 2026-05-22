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
    "OPERACIONES-BOSQUE",      // Added
    "GERENTE-CLINICA",          // Added
    "INFORMACIÓN",              // Added
    "ADMINISTRACION-CLINICA"    // Added
];

if (in_array(UserHelper::getMyRol(), $rolesWithClinica)) {
    $clinicaName = UserHelper::getMyClinicaName();
}

?>
<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light" style="background: linear-gradient(135deg, #009efb 0%, #0078d4 100%); box-shadow: 0 2px 10px rgba(0,0,0,0.1); width:auto; min-height: 56px;">
    <!-- Left navbar links -->
    <ul class="navbar-nav align-items-center">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button" style="color: white; transition: all 0.3s ease; padding: 0.5rem 1rem;">
                <i class="fas fa-bars fa-lg"></i>
            </a>
        </li>
    </ul>

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

    /* Responsive adjustments */
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
    }
</style>

<script>
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