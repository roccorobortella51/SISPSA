<?php
/* @var $content string */

use yii\bootstrap4\Breadcrumbs; // O yii\widgets\Breadcrumbs; si usas Bootstrap 3
use yii\helpers\Html; // Importar Html para su uso
use app\widgets\Alert;

?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12">
                    <?= Alert::widget() ?>
                    <?php
                    echo Breadcrumbs::widget([
                        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                        'options' => [
                            'class' => 'breadcrumb float-sm-left'
                        ],
                        'itemTemplate' => "<li class=\"breadcrumb-item\">{link}</li>\n",
                        'activeItemTemplate' => "<li class=\"breadcrumb-item active\">{link}</li>\n",
                        // --- ¡importante ! ---
                        'homeLink' => [
                            'label' => 'INICIO', // Esto cambia 'Home' a 'Inicio'
                            'url' => Yii::$app->homeUrl, // Esto asegura que apunte a la URL de inicio de tu aplicación (dashboard)
                            'encode' => false,
                            'class' => 'breadcrumb-item'
                        ],
                        // --- FIN  ---
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="content">
        <?= $content ?>
    </div>
</div>
