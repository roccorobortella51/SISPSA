<?php

/* @var $this \yii\web\View */
/* @var $content string */
use app\assets\MedboardAsset; // Importa tu nuevo Asset Bundle
use app\assets\AppAsset;

use yii\helpers\Html;
//\hail812\adminlte3\assets\AdminLteAsset::register($this);
//MedboardAsset::register($this); // Registra el Asset Bundle de MedBoard
AppAsset::register($this);
// También asegúrate de que AppAsset se registre si lo necesitas para cosas generales de tu app
// app\assets\AppAsset::register($this);
$this->registerCssFile('https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback');

$assetDir = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');

$publishedRes = Yii::$app->assetManager->publish('@vendor/hail812/yii2-adminlte3/src/web/js');
$this->registerJsFile($publishedRes[1].'/control_sidebar.js', ['depends' => '\hail812\adminlte3\assets\AdminLteAsset']);

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
    <?php $this->head() ?>
    <style>
        body.sidebar-collapse .hide-on-sidebar-collapse {
            display: none!important;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
<?php $this->beginBody() ?>

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
        $('[data-widget="pushmenu"]').PushMenu();

        // Custom toggle for collapsed-sidebar class on body with debug logs
        $('[data-widget="pushmenu"]').on('click', function(e) {
            e.preventDefault();
            console.log('Pushmenu button clicked');
            $('body').toggleClass('collapsed-sidebar');
            console.log('collapsed-sidebar class toggled on body:', $('body').hasClass('collapsed-sidebar'));
        });
    });
</script>
</body>
</html>
<?php $this->endPage() ?>
