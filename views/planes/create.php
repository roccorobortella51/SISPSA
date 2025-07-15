<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Planes $model */

$this->title = 'Create Planes';
$this->params['breadcrumbs'][] = ['label' => 'Planes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="planes-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
