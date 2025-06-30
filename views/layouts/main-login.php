<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\assets\AppAsset;
use yii\helpers\Html;

// Registramos nuestro AppAsset, que ahora contendrá los estilos personalizados.
AppAsset::register($this);

\hail812\adminlte3\assets\AdminLteAsset::register($this);
$this->registerCssFile('https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700');
$this->registerCssFile('https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css');
\hail812\adminlte3\assets\PluginAsset::register($this)->add(['fontawesome', 'icheck-bootstrap']);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Sispsa</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <?php $this->head() ?>
</head>
<body >
<?php  $this->beginBody() ?>
    <main>
    <nav class="navbar ms-navbar" style="background-color: #009efb;">
        <div class="ms-aside-toggler ms-toggler pl-0">
        </div>
        <div class="logo-sn logo-sm">
            <a class="pl-0 ml-0 text-center navbar-brand mr-0" href="<?= Yii::$app->homeUrl ?>"><img src="<?= Yii::getAlias('@web/img/sispsa-12-62.png') ?>" alt="logo"> </a>
        </div>
        <div class="ms-aside-toggler ms-toggler pl-0">
        </div>
    </nav>
    <div class ="ms-auth-container">
        <div class="ms-auth-col">
            <div class="ms-auth-bg" bis_skin_checked="1"></div>
        </div>
        <div class="ms-auth-col">
            <?= $content ?>
        </div>
    </div>
<!-- /.login-box -->

<?php $this->endBody() ?>
    </main>
</body>
</html>
<?php $this->endPage() ?>