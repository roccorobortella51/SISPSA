<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\RmClinica $model */


$this->title = 'CREAR DECLARACIÓN DE SALUD';
$this->params['breadcrumbs'][] = ['label' => 'DECLARACIÓN DE SALUD', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="col-xl-12 col-md-12">
    <div class="ms-panel ms-panel-fh">
        <div class="ms-panel-header">
            <h1><?= $this->title = 'DECLARACIÓN DE SALUD'; ?></h1>
        </div>
        <div class="ms-panel-body">
            <?= $this->render('_form', [
                'model' => $model,
                'afiliado' => $afiliado
            ]) ?>        
        </div>
    </div>
</div>

