<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Corporativo $model */

$this->title = 'ACTUALIZAR AFILIADO CORPORATIVO ';
$this->params['breadcrumbs'][] = ['label' => 'Corporativos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>

<div class="col-xl-12 col-md-12">
    <div class="ms-panel ms-panel-fh">
        <div class="ms-panel-header">
        <h1><?= Html::encode($this->title) ?></h1>
        </div>
        <div class="ms-panel-body">
        <?= $this->render('_form', [
        'model' => $model,
        ]) ?>        
        </div>
    </div>
</div>
