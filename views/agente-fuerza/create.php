<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AgenteFuerza $model */

$this->title = 'Crear agente';
$this->params['breadcrumbs'][] = ['label' => 'Agente Fuerzas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="agente-fuerza-create">

    <?= $this->render('_form', [
        'model' => $model,
        'agente' => $agente,
    ]) ?>

</div>
