<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AgenteFuerza $model */

$this->title = 'Update Agente Fuerza: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'AGENTES DE FUERZA'];
$this->params['breadcrumbs'][] = 'ACTUALIZAR';
?>
<div class="agente-fuerza-update">

    <?= $this->render('_form', [
        'model' => $model,
        'agente' => $agente,
        'agente_id' => $model->agente_id
    ]) ?>

</div>
