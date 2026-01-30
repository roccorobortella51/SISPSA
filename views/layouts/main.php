<?php
/* @var $this \yii\web\View */
/* @var $content string */

use app\assets\MedboardAsset;
use app\assets\AppAsset;
use kartik\spinner\Spinner;
use yii\helpers\Html;

// Force clear asset cache by appending timestamp
Yii::$app->assetManager->appendTimestamp = true;

// Clear any cached asset bundles
if (YII_ENV_PROD) {
    Yii::$app->assetManager->hashCallback = function ($path) {
        return hash('md5', $path . filemtime($path));
    };
}


AppAsset::register($this);

// --- START: CUSTOM CSS FOR NESTED MENU ---
$customCss = '
/* ===== GLOBAL MENU STYLES ===== */

/* Base styles for all menu items */
.nav-sidebar .nav-link {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    position: relative !important;
    overflow: hidden !important;
    display: flex !important;
    align-items: center !important;
}

/* Base styles for all menu icons and text */
.nav-sidebar .nav-link > .nav-icon,
.nav-sidebar .nav-link > .menu-text {
    transition: all 0.3s ease !important;
}

/* ===== PROFESSIONAL HOVER EFFECTS FOR ALL MENU ITEMS - ORIGINALS RESTORED ===== */

/* Level 1: Main menu items (Agencia, Dashboard, etc.) */
.nav-sidebar > .nav-item > .nav-link:hover {
    background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%) !important; /* RESTORED: Dark Gray/Slate */
    color: #ffffff !important;
    transform: translateX(8px) !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}

/* Level 2: First-level sub-menu items */
.nav-sidebar .nav-treeview .nav-link:hover {
    background: linear-gradient(135deg, #f1f8e9 0%, #81c784 100%) !important;
    color: #1b5e20 !important;
    transform: translateX(8px) !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}

/* Level 3: Second-level sub-menu items (Indicadores de Ventas children) */
.nav-sidebar .nav-treeview .nav-treeview .nav-link:hover {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%) !important; /* RESTORED: Blue */
    color: #ffffff !important;
    transform: translateX(8px) !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}

/* ===== HOVER EFFECTS FOR ICONS - NO CHANGE ===== */

/* Main menu icons */
.nav-sidebar > .nav-item > .nav-link:hover > .nav-icon {
    color: #e2e8f0 !important;
    transform: scale(1.1) !important;
}

/* First-level sub-menu icons */
.nav-sidebar .nav-treeview .nav-link:hover > .nav-icon {
    color: #333333 !important;  /* Cambiado a negro para que coincida con el texto */
    transform: scale(1.1) !important;
}

/* Second-level sub-menu icons */
.nav-sidebar .nav-treeview .nav-treeview .nav-link:hover > .nav-icon {
    color: #ffffff !important;
    transform: scale(1.1) !important;
}

/* ===== HOVER EFFECTS FOR TEXT - NO CHANGE ===== */

.nav-sidebar .nav-link:hover > .menu-text {
    color: #f7fafc !important;
    font-weight: 700 !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2) !important;
}

/* ===== ACTIVE STATE STYLING FOR ALL LEVELS - NO CHANGE ===== */

.nav-sidebar .nav-link.active {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2) !important;
}

/* Main menu active state */
.nav-sidebar > .nav-item > .nav-link.active {
    background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%) !important;
}

/* First-level sub-menu active state */
.nav-sidebar .nav-treeview .nav-link.active {
    background: #f1f8e9 !important;
    color: #1b5e20 !important;
    font-weight: 600 !important;
    border-left: none !important;
    transform: none !important;
    box-shadow: none !important;
}

/* Second-level sub-menu active state */
.nav-sidebar .nav-treeview .nav-treeview .nav-link.active {
    background: linear-gradient(135deg, #2980b9 0%, #3498db 100%) !important;
}

/* Active state text and icons */
.nav-sidebar .nav-link.active > .nav-icon,
.nav-sidebar .nav-link.active > .menu-text {
    color: #ffffff !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2) !important;
}

/* ===== PULSE ANIMATION FOR ACTIVE ITEMS - NO CHANGE ===== */

.nav-sidebar .nav-link.active::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    animation: pulse-submenu 2s infinite;
    z-index: -1;
}

/* Different pulse colors for different levels */
.nav-sidebar > .nav-item > .nav-link.active::before {
    background: rgba(99, 179, 237, 0.15); /* Blue pulse for main menu */
}

.nav-sidebar .nav-treeview .nav-link.active::before {
    background: rgba(52, 206, 87, 0.15); /* Green pulse for first-level */
}

.nav-sidebar .nav-treeview .nav-treeview .nav-link.active::before {
    background: rgba(93, 173, 226, 0.15); /* Light blue pulse for second-level */
}

@keyframes pulse-submenu {
    0% {
        opacity: 0.3;
    }
    50% {
        opacity: 0.6;
    }
    100% {
        opacity: 0.3;
    }
}

/* ===== REMOVED: RIPPLED EFFECT FOR ALL MENU ITEMS - NO CHANGE ===== */
/* This section intentionally left blank to remove the arrow/ripple effect */

/* ===== FOCUS STATES FOR ACCESSIBILITY - NO CHANGE ===== */

.nav-sidebar .nav-link:focus {
    outline: none;
    box-shadow: 0 0 0 2px #63b3ed, 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}

/* ===== MENU STRUCTURE FIXES - NO CHANGE ===== */

/* Ensure menus stay open and show sub-items */
.nav-sidebar > .nav-item.menu-open > .nav-treeview {
    display: block !important;
}

.nav-sidebar .nav-treeview .nav-item.menu-open > .nav-treeview {
    display: block !important;
}

/* Active/open parent link styling */
.nav-sidebar > .nav-item.menu-open > .nav-link, 
.nav-sidebar > .nav-item.menu-open > .nav-link:hover {
    color: #ffffff !important; 
    background-color: rgba(0, 0, 0, 0.2) !important; 
}

.nav-sidebar .nav-treeview .nav-item.menu-open > .nav-link, 
.nav-sidebar .nav-treeview .nav-item.menu-open > .nav-link:hover {
    color: #ffffff !important; 
    background-color: rgba(0, 0, 0, 0.35) !important;
    font-weight: 700 !important;
}

/* Menu container styling */
.nav-sidebar > .nav-item > .nav-treeview {
    border-left: 3px solid rgba(255, 255, 255, 0.2); 
    margin-left: 5px;
    padding-left: 0; 
}

.nav-sidebar .nav-treeview .nav-item > .nav-treeview {
    background-color: rgba(255, 255, 255, 0.05) !important;
    border-radius: 4px;
    margin-left: 10px; 
    border-left: 5px solid rgba(255, 255, 255, 0.3);
    margin-top: 5px; 
    margin-bottom: 5px; 
    padding: 5px 0; 
}

/* Critical display fixes */
.nav-sidebar .menu-open > .nav-treeview,
.nav-sidebar .nav-treeview .menu-open > .nav-treeview {
    display: block !important;
    height: auto !important;
    opacity: 1 !important;
    visibility: visible !important;
}

.nav-sidebar .nav-treeview .nav-treeview {
    display: none;
}

.nav-sidebar .nav-treeview .menu-open .nav-treeview {
    display: block !important;
}
';

$this->registerCss($customCss);
// --- END: CUSTOM CSS FOR NESTED MENU ---

$assetDir = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');
$publishedRes = Yii::$app->assetManager->publish('@vendor/hail812/yii2-adminlte3/src/web/js');
$this->registerJsFile($publishedRes[1] . '/control_sidebar.js', ['depends' => '\hail812\adminlte3\assets\AdminLteAsset']);

$logo_pestana = "https://sispsa.app/v2/web/img/sispsa.svg";
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>

    <link rel="icon" href=<?= $logo_pestana ?> sizes="32x32" />
    <link rel="icon" href=<?= $logo_pestana ?> sizes="192x192" />
    <link rel="apple-touch-icon" href=<?= $logo_pestana ?> />
    <meta name="msapplication-TileImage" content=<?= $logo_pestana ?> />
    <meta name="csrf-token" content="<?= Yii::$app->request->getCsrfToken() ?>">

    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
-->
    <!-- Load Chart.js for charts and graphs -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>

    <?php $this->head() ?>
    <style>
        body.sidebar-collapse .hide-on-sidebar-collapse {
            display: none !important;
        }

        /* Estilos para el spinner */
        #global-page-spinner {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.9);
            z-index: 9999;
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: opacity 0.3s ease;
        }

        .spinner-text {
            font-size: 1.2rem;
            color: #555;
            margin-top: 15px;
        }

        /* Ensure modals appear above everything */
        .modal {
            z-index: 1060;
        }

        .modal-backdrop {
            z-index: 1050;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini">

    <?php $this->beginBody() ?>

    <div id="global-page-spinner" role="status" aria-live="polite">
        <?= Spinner::widget([
            'id' => 'main-spinner',
            'preset' => 'large',
            'color' => '#3c8dbc',
            'pluginOptions' => [
                'lines' => 13,
                'length' => 20,
                'width' => 8,
                'radius' => 30,
                'speed' => 1.2,
                'trail' => 60,
                'shadow' => false
            ],
            'options' => [
                'style' => 'margin-bottom: 20px;'
            ]
        ]) ?>
        <div class="spinner-text">Cargando, por favor espere...</div>
    </div>

    <div class="wrapper">
        <?= $this->render('navbar', ['assetDir' => $assetDir]) ?>
        <?= $this->render('sidebar', ['assetDir' => $assetDir]) ?>
        <?= $this->render('content', ['content' => $content, 'assetDir' => $assetDir]) ?>
        <?= $this->render('control-sidebar') ?>
        <?= $this->render('footer') ?>
    </div>

    <?php $this->endBody() ?>

    <script>
        // Debug and ensure modals work
        console.log('Page loaded - testing Bootstrap functionality');
        console.log('jQuery version:', jQuery.fn.jquery);
        console.log('Bootstrap modal available:', jQuery.fn.modal ? 'YES' : 'NO');

        jQuery(document).ready(function($) {
            console.log('Document ready - modal functionality initialized');

            // Debug modal events
            $('[data-toggle="modal"]').on('click', function(e) {
                e.preventDefault();
                var target = $(this).data('target');
                console.log('Modal link clicked:', target);
                console.log('Target exists:', $(target).length > 0);
            });

            // Test modal events
            $('#testModal').on('show.bs.modal', function() {
                console.log('Test modal is about to show');
            });

            $('#testModal').on('shown.bs.modal', function() {
                console.log('Test modal is now visible');
            });

            // Force show test modal after 2 seconds for testing
            setTimeout(function() {
                console.log('Attempting to show test modal programmatically');
                $('#testModal').modal('show');
            }, 2000);
        });
    </script>

</body>

</html>
<?php $this->endPage() ?>