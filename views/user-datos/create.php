<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\UserDatos $model */

$this->title = 'Crear Afiliado';
$this->params['breadcrumbs'][] = ['label' => 'Afiliados', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="col-xl-12 col-md-12">
    <div class="ms-panel ms-panel-fh">
    <div class="ms-panel-header d-flex justify-content-between align-items-center">
    <h1><?= Html::encode($this->title); ?></h1>
    
    <div>
        <?= Html::a(
            '<i class="fas fa-undo"></i> Volver', 
            '#', // Or a specific URL like ['index'] if you don't want history.back()
            [
                'class' => 'btn btn-primary btn-lg', 
                'onclick' => 'window.history.back(); return false;', 
                'title' => 'Volver a la página anterior', 
            ]
        ) ?> 
    </div>
</div>
        <div class="ms-panel-body">
            <?= $this->render('_form', [
                'model' => $model,
                'modelContrato' => $modelContrato,
            ]) ?>        
        </div>
    </div>
</div>

