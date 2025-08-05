<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\RmClinica $model */

$this->title = 'ACTUALIZAR PLÁN: ' . $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'PLÁNES', 'url' => ['index', 'clinica_id' => $clinica->id]];
$this->params['breadcrumbs'][] = ['label' => $model->nombre, 'url' => ['view', 'id' => $model->id, 'clinica_id' => $clinica->id]];
$this->params['breadcrumbs'][] = 'ACTUALIZAR';
?>

<div class="col-xl-12 col-md-12">
    <div class="ms-panel ms-panel-fh">
        <div class="ms-panel-header">
            <h1><?= Html::encode($this->title); ?></h1>
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