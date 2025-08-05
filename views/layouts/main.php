<?php
/* @var $this \yii\web\View */
/* @var $content string */
use app\assets\MedboardAsset;
use app\assets\AppAsset;
use kartik\spinner\Spinner;
use yii\helpers\Html;
use yii\bootstrap4\BootstrapAsset;
use yii\bootstrap4\BootstrapPluginAsset;
use yii\bootstrap4\Alert;

BootstrapAsset::register($this);
BootstrapPluginAsset::register($this);
AppAsset::register($this);

$assetDir = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');
$publishedRes = Yii::$app->assetManager->publish('@vendor/hail812/yii2-adminlte3/src/web/js');
$this->registerJsFile($publishedRes[1].'/control_sidebar.js', ['depends' => '\hail812\adminlte3\assets\AdminLteAsset']);

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
    
    <link rel="icon" href=<?= $logo_pestana?> sizes="32x32" />
    <link rel="icon" href=<?= $logo_pestana?> sizes="192x192" />
    <link rel="apple-touch-icon" href=<?= $logo_pestana?> />
    <meta name="msapplication-TileImage" content=<?= $logo_pestana?> />

    <?php $this->head() ?>
    <style>
        body.sidebar-collapse .hide-on-sidebar-collapse {
            display: none!important;
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
    </style>
</head>
<body class="hold-transition sidebar-mini">

<?php $this->beginBody() ?>

<!-- Spinner global -->
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
    <!-- Navbar -->
    <?= $this->render('navbar', ['assetDir' => $assetDir]) ?>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <?= $this->render('sidebar', ['assetDir' => $assetDir]) ?>

    <!-- Content Wrapper. Contains page content -->
    <?= $this->render('content', ['content' => $content, 'assetDir' => $assetDir]) ?>
    <!-- /.content-wrapper -->

    <!-- Control Sidebar -->
    <?= $this->render('control-sidebar') ?>
    <!-- /.control-sidebar -->

    <!-- Main Footer -->
    <?= $this->render('footer') ?>
</div>

<?php $this->endBody() ?>

<script>
$(function () {
    // Inicialización del PushMenu de AdminLTE
    $('[data-widget="pushmenu"]').PushMenu();

    // Toggle de la clase 'collapsed-sidebar'
    $('[data-widget="pushmenu"]').on('click', function(e) {
        e.preventDefault();
        $('body').toggleClass('collapsed-sidebar');
    });

    // Objeto para manejar el spinner global
    var pageSpinner = {
        show: function() {
            // Se muestra el spinner con un pequeño retraso
            // para evitar parpadeos en cargas muy rápidas
            setTimeout(function() {
                $('#global-page-spinner').css('display', 'flex').hide().fadeIn(200);
            }, 100);
        },
        hide: function() {
            $('#global-page-spinner').fadeOut(200);
        }
    };

    // Función para determinar si un enlace debe mostrar el spinner
    function shouldShowSpinner(link) {
        var $link = $(link);
        var href = link.href.trim();
        return !(
            href === '#' ||
            href.startsWith('javascript:') ||
            $link.is('[data-toggle="dropdown"]') ||
            $link.is('.dropdown-toggle') ||
            $link.hasClass('no-spinner') ||
            $link.hasClass('btn') || // Excluir botones que podrían tener la clase de enlace
            link.target === '_blank' ||
            $link.data('method') ||
            link.href.startsWith('mailto:') ||
            link.href.startsWith('tel:') ||
            $link.attr('data-pjax') !== undefined // Excluir enlaces PJAX
        );
    }
    
    // 1. Mostrar el spinner al hacer clic en enlaces de navegación
    $(document).on('click', 'a', function(e) {
        // Solo si la URL es diferente a la actual para evitar recargas innecesarias
        if (this.href && this.href !== window.location.href) {
            if (shouldShowSpinner(this)) {
                pageSpinner.show();
            }
        }
    });

    // 2. Ocultar el spinner en el momento en que la página está lista
    // Esto se ejecuta en cada carga de página (incluyendo la inicial).
    // Si la página se carga muy rápido, el spinner podría no llegar a mostrarse,
    // lo cual es un comportamiento deseado.
    pageSpinner.hide();
    
    // 3. Ocultar el spinner si la página tarda demasiado (fallback de seguridad)
    // Esto previene que el spinner se quede si ocurre un error o un script se bloquea.
    setTimeout(function() {
        if ($('#global-page-spinner').is(':visible')) {
            console.warn('El spinner global ha sido ocultado por el fallback de 5 segundos.');
            pageSpinner.hide();
        }
    }, 5000);
});

// Ocultar el spinner también en el evento 'pageshow' para
// manejar el historial del navegador (botón de atrás/adelante)
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        // La página se carga desde la caché
        $('#global-page-spinner').fadeOut(200);
    }
});
</script>
</body>
</html>
<?php $this->endPage() ?>