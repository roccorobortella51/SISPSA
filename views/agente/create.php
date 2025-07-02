<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AgenteFuerza $model */ // Correct model for AgenteFuerza
/* @var app\models\Agente $agente */ // Make sure $agente is passed from the controller

// Set the page title for updating an AgenteFuerza
$this->title = 'ACTUALIZAR ASESOR DE VENTAS: ' . $model->idusuario; // Or $model->nombre_agente for context
$this->params['breadcrumbs'][] = ['label' => 'Agente de Fuerza', 'url' => ['index']]; // Link to the main index for AgenteFuerza
$this->params['breadcrumbs'][] = ['label' => $model->idusuario, 'url' => ['view', 'id' => $model->id]]; // Link to the view page of this specific AgenteFuerza
$this->params['breadcrumbs'][] = 'Actualizar';
?>

<div class="agente-fuerza-update">

    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header">
                <h1><?= Html::encode($this->title); ?></h1>
            </div>
            <div class="ms-panel-body">
                <?= $this->render('_form', [
                    'model' => $model,
                    'agente' => $agente, // Crucial to pass the $agente object
                ]) ?>
            </div>
        </div>
    </div>

</div>