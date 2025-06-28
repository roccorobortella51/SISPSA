<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Baremo $model */

$this->title = 'Create Baremo';
$this->params['breadcrumbs'][] = ['label' => 'Baremos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="baremo-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
