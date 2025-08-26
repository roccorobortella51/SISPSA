<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\RmClinica $model */

$this->title = 'CREAR PLÁN'; // Título principal de la página
$this->params['breadcrumbs'][] = ['label' => 'PLANES', 'url' => ['index', 'clinica_id' => $clinica->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="col-xl-12 col-md-12">
    <div class="ms-panel ms-panel-fh">
        <div class="ms-panel-header d-flex justify-content-between align-items-center mb-3">
            <h1 class="m-0"><?= Html::encode($this->title); ?></h1>
        </div>
        <div class="ms-panel-body">
            <?= $this->render('_form', [
                'model' => $model,
                'itemsModels' => $itemsModels,
                'clinica' => $clinica
            ]) ?>        
        </div>
    </div>
</div>