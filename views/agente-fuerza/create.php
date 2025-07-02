<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AgenteFuerza $model */

$this->title = 'Create Agente Fuerza';
$this->params['breadcrumbs'][] = ['label' => 'Agente Fuerzas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="agente-fuerza-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'agente' => $agente,
    ]) ?>

</div>
