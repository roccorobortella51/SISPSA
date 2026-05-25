<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap4\Nav;
use mdm\admin\components\MenuHelper;
use app\components\UserHelper;

$rol = UserHelper::getMyRol();
$clinica = "";

// Define which roles should show clinic name - easily customizable
$rolesWithClinica = [
    "Administrador-clinica",
    "CONTROL DE CITAS",
    "ADMISIÓN",
    "ATENCIÓN",
    "COORDINADOR-CLINICA",
    "GERENTE-CLINICA",
    "ADMINISTRACION-CLINICA",
    "OPERACIONES-BOSQUE",
    "INFORMACIÓN"
];

if (in_array($rol, $rolesWithClinica)) {
    $clinica = UserHelper::getMyClinicaName();
}

// Get user name from UserDatos
$userName = 'Usuario'; // Default fallback
$userIdentity = Yii::$app->user->identity;
$userPhoto = null; // Variable to store user photo path
$photoExists = false;
$photoUrl = null;

if ($userIdentity) {
    // Try to get the user's full name from UserDatos relation
    if ($userIdentity->userDatos) {
        $nombres = trim($userIdentity->userDatos->nombres ?? '');
        $apellidos = trim($userIdentity->userDatos->apellidos ?? '');

        if (!empty($nombres) && !empty($apellidos)) {
            $userName = $nombres . ' ' . $apellidos; // Full name
        } elseif (!empty($nombres)) {
            $userName = $nombres; // Just first name
        } elseif (!empty($apellidos)) {
            $userName = $apellidos; // Just last name
        } else {
            // Fallback to username if no names found
            $userName = $userIdentity->username ?? 'Usuario';
        }

        // Check for selfie photo
        if (!empty($userIdentity->userDatos->selfie)) {
            $userPhoto = $userIdentity->userDatos->selfie;

            // Clean up the path - remove leading slashes if any
            $cleanPhoto = ltrim($userPhoto, '/');

            // Define possible paths to check (in order of priority)
            $possiblePaths = [
                // Original path as stored
                $userPhoto,

                // Remove /v2/ prefix if present
                str_replace('/v2/', '/', $userPhoto),
                str_replace('/v2', '', $userPhoto),

                // Just the filename
                basename($userPhoto),

                // Various upload directories
                'uploads/selfie/' . basename($userPhoto),
                'uploads/' . basename($userPhoto),
                'img/payment/' . basename($userPhoto),
                'web/img/payment/' . basename($userPhoto),
                'img/selfie/' . basename($userPhoto),

                // Cleaned path without /v2/
                preg_replace('#^/v2/#', '/', $userPhoto),
                preg_replace('#^v2/#', '', $userPhoto),
            ];

            // Add web-accessible paths
            $webPaths = [];
            foreach ($possiblePaths as $path) {
                $webPaths[] = $path;
                $webPaths[] = '@web/' . ltrim($path, '/');
                $webPaths[] = '@webroot/' . ltrim($path, '/');
            }

            // Check each possible path
            foreach ($possiblePaths as $path) {
                // Try as web path
                $webPath = Yii::getAlias('@web/' . ltrim($path, '/'));
                $fullPath = Yii::getAlias('@webroot/' . ltrim($path, '/'));

                if (file_exists($fullPath)) {
                    $photoExists = true;
                    $photoUrl = $webPath;
                    break;
                }

                // Try direct path
                if (file_exists($path)) {
                    $photoExists = true;
                    $photoUrl = Yii::getAlias('@web/' . ltrim($path, '/'));
                    break;
                }
            }

            // If still not found, try to extract just the filename and search common locations
            if (!$photoExists) {
                $filename = basename($userPhoto);
                $commonLocations = [
                    '@webroot/uploads/selfie/' . $filename,
                    '@webroot/uploads/' . $filename,
                    '@webroot/img/payment/' . $filename,
                    '@webroot/img/selfie/' . $filename,
                    '@webroot/web/img/payment/' . $filename,
                ];

                foreach ($commonLocations as $location) {
                    $fullPath = Yii::getAlias($location);
                    if (file_exists($fullPath)) {
                        $photoExists = true;
                        $photoUrl = str_replace('@webroot', '@web', $location);
                        break;
                    }
                }
            }
        }
    } else {
        // Fallback to username if UserDatos doesn't exist
        $userName = $userIdentity->username ?? 'Usuario';
    }
}

// Get user's avatar/initials for profile image (fallback when no selfie)
$userInitials = '';
if (!empty($userName) && $userName != 'Usuario') {
    $nameParts = explode(' ', trim($userName));
    if (count($nameParts) >= 2) {
        $userInitials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
    } else {
        $userInitials = strtoupper(substr($userName, 0, 2));
    }
} else {
    $userInitials = 'U';
}

// Get user's email for tooltip
$userEmail = $userIdentity->email ?? '';

// Debug information (remove in production)
// Uncomment the line below to see debug info (only for troubleshooting)
// echo "<!-- Selfie path: " . Html::encode($userPhoto) . " - Exists: " . ($photoExists ? 'Yes' : 'No') . " - URL: " . Html::encode($photoUrl) . " -->";
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4" style="border-bottom: none; background-color: #009efb !important; color: white !important;">
    <!-- Brand Logo -->
    <a href="<?= Yii::$app->homeUrl ?>" class="brand-link d-flex justify-content-center" style="padding-top: 0; padding-bottom: 0; border-bottom: none;">
        <img src="<?= Yii::getAlias('@web/img/sispsa-12-62.png') ?>" alt="Logo" style="opacity: 1; margin: 15px auto; max-width: 250px; width: auto; height: auto; object-fit: contain;">
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel d-flex flex-column align-items-center">
            <div class="image mb-2 position-relative">
                <!-- CLICKABLE PHOTO - Links to Profile Page -->
                <a href="<?= Url::to(['/profile/index']) ?>" style="display: inline-block; text-decoration: none;">
                    <?php if ($photoExists && !empty($photoUrl)): ?>
                        <!-- Show selfie photo if exists -->
                        <img src="<?= $photoUrl ?>" class="img-circle elevation-2" alt="User Selfie" style="width: 90px; height: 90px; object-fit: cover; border: 3px solid rgba(255,255,255,0.3); cursor: pointer;">
                    <?php else: ?>
                        <!-- Show initials avatar if no selfie -->
                        <div class="img-circle elevation-2 d-flex align-items-center justify-content-center" style="
                            width: 90px; 
                            height: 90px; 
                            background: linear-gradient(135deg, #ffffff20, #ffffff30);
                            border: 3px solid rgba(255,255,255,0.3);
                            margin: 0 auto;
                            font-size: 32px;
                            font-weight: 600;
                            color: white;
                            cursor: pointer;
                        ">
                            <?= Html::encode($userInitials) ?>
                        </div>
                    <?php endif; ?>
                </a>
            </div>
            <div class="info text-center">
                <!-- Display clinic name if available -->
                <?php if (!empty($clinica)): ?>
                    <p class="mb-1" style="font-size: 14px; line-height: 1.2;">
                        <b><?= Html::encode($clinica) ?></b>
                    </p>
                <?php endif; ?>

                <!-- Display role -->
                <p class="mb-2" style="font-size: 13px; line-height: 1.2;">
                    <b><?= Html::encode($rol) ?></b>
                </p>

                <!-- Display user's full name (instead of username) -->
                <b>
                    <a href="#" class="d-block"
                        style="color: white !important; font-size: 14px; line-height: 1.2; text-decoration: none;"
                        <?php if (!empty($userEmail)): ?>
                        title="<?= Html::encode($userEmail) ?>"
                        <?php endif; ?>>
                        <?= Html::encode($userName) ?>
                    </a>
                </b>

                <!-- Optional: Show email on hover or as subtitle -->
                <?php if (!empty($userEmail)): ?>
                    <small style="font-size: 10px; color: rgba(255,255,255,0.7); display: block; margin-top: 4px;">
                        <?= Html::encode($userEmail) ?>
                    </small>
                <?php endif; ?>

                <!-- MI PERFIL BUTTON - EXTRA LARGE -->
                <div class="mt-3" style="width: 100%;">
                    <?= Html::a(
                        '<i class="fas fa-user-circle mr-3" style="font-size: 20px;"></i> MI PERFIL',
                        ['/profile/index'],
                        [
                            'class' => 'btn',
                            'style' => '
                                background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
                                color: #2c3e50;
                                border: none;
                                border-radius: 50px;
                                padding: 15px 20px;
                                font-size: 18px;
                                font-weight: 700;
                                width: 100%;
                                transition: all 0.3s ease;
                                box-shadow: 0 4px 12px rgba(0,0,0,0.25);
                                letter-spacing: 1px;
                                text-transform: uppercase;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                gap: 10px;
                            ',
                            'onmouseover' => 'this.style.transform="translateX(5px)"; this.style.boxShadow="0 6px 18px rgba(0,0,0,0.35)"; this.style.background="linear-gradient(135deg, #FFE44D 0%, #FFB347 100%)"',
                            'onmouseout' => 'this.style.transform="translateX(0)"; this.style.boxShadow="0 4px 12px rgba(0,0,0,0.25)"; this.style.background="linear-gradient(135deg, #FFD700 0%, #FFA500 100%)"'
                        ]
                    ) ?>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <?php
            $callback = function ($menu) {
                $data = [];
                $icon = 'fas fa-circle'; // Default icon

                if (!empty($menu['data'])) {
                    // Convert resource to string if needed
                    $menuData = is_resource($menu['data']) ? stream_get_contents($menu['data']) : $menu['data'];
                    // Try to decode as JSON
                    if ($menuData !== false) {
                        $decoded = @json_decode($menuData, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $data = $decoded;
                            // Extract icon if exists
                            if (isset($decoded['icon'])) {
                                $icon = $decoded['icon'];
                            }
                        }
                    }
                }

                $menuItem = [
                    'label' => '<span class="menu-text">' . $menu['name'] . '</span>',
                    'url' => [$menu['route']],
                    'options' => $data,
                ];

                // Add icon to menu
                if (!empty($menu['children'])) {
                    // Has children - dropdown menu
                    $menuItem['icon'] = $icon;
                    $menuItem['items'] = $menu['children'];
                    $menuItem['options']['class'] = 'nav-item has-treeview';
                    $menuItem['linkOptions'] = ['class' => 'nav-link'];
                } else {
                    // No children - simple link
                    $menuItem['icon'] = $icon;
                    $menuItem['options']['class'] = 'nav-item';
                    $menuItem['linkOptions'] = ['class' => 'nav-link'];
                }

                return $menuItem;
            };

            echo \hail812\adminlte\widgets\Menu::widget([
                'items' => MenuHelper::getAssignedMenu(Yii::$app->user->id, null, $callback),
                'options' => ['class' => 'nav nav-pills nav-sidebar flex-column', 'data-widget' => 'treeview'],
                'encodeLabels' => false,
                'activateParents' => true
            ]);
            ?>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>

<style>
    /* Sidebar custom styles */
    .main-sidebar .user-panel {
        padding: 20px 10px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 15px;
    }

    /* Hover effect for user name */
    .main-sidebar .user-panel .info a:hover {
        text-decoration: underline !important;
        opacity: 0.9;
    }

    /* Avatar pulse effect */
    @keyframes avatarPulse {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4);
        }

        50% {
            box-shadow: 0 0 0 5px rgba(255, 255, 255, 0.2);
        }
    }

    .main-sidebar .user-panel .image div,
    .main-sidebar .user-panel .image img {
        animation: avatarPulse 3s ease-in-out infinite;
    }

    /* Hover effect for photo */
    .main-sidebar .user-panel .image img:hover {
        transform: scale(1.05);
        transition: transform 0.3s ease;
        cursor: pointer;
    }

    /* Menu text truncation if needed */
    .menu-text {
        white-space: normal;
        word-break: break-word;
    }

    /* Sidebar menu hover effects */
    .nav-sidebar .nav-link:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: translateX(5px);
        transition: all 0.3s ease;
    }

    /* Active menu item */
    .nav-sidebar .nav-link.active {
        background: rgba(255, 255, 255, 0.2);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
</style>