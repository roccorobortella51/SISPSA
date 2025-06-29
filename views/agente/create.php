<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Agente $model */

$this->title = 'Create Agente';
$this->params['breadcrumbs'][] = ['label' => 'Agentes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="agente-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
