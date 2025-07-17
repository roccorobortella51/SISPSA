<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Corporativo $model */

$this->title = 'Create Corporativo';
$this->params['breadcrumbs'][] = ['label' => 'Corporativos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="corporativo-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
