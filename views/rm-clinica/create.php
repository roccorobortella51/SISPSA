<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\RmClinica $model */


$this->title = 'Crear Clínica';
$this->params['breadcrumbs'][] = ['label' => 'Crear Clínica', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="col-xl-12 col-md-12">
    <div class="ms-panel ms-panel-fh">
        <div class="ms-panel-header">
            <h1><?= $this->title = 'Crear Clínica'; ?></h1>
        </div>
        <div class="ms-panel-body">
            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>        
        </div>
    </div>
</div>

