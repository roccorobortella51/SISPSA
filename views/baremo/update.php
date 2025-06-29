<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Baremo $model */

$this->title = 'Update Baremo: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Baremos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->nombre]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="baremo-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
