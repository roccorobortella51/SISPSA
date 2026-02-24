<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\RmClinica $model */

$this->title = 'Actualizar Contrato: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Contracto', 'url' => ['index', 'user_id' => $model->user_id]];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Actualizar';
?>
<div class="col-xl-12 col-md-12">
    <div class="ms-panel ms-panel-fh">
        <div class="ms-panel-header">
            <h1><?= $this->title = 'Actualizar Contracto'; ?></h1>
        </div>
        <div class="ms-panel-body">
            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>
        </div>
    </div>
</div>