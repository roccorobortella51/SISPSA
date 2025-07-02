<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AgenteFuerza $model */ // Correct model for AgenteFuerza
/* @var app\models\Agente $agente */ // Make sure $agente is passed from the controller

// Set the page title for updating an AgenteFuerza
$this->title = 'CREAR AGENCIAS';
$this->params['breadcrumbs'][] = ['label' => 'AGENTE DE FUERZA', 'url' => ['index']]; // Link to the main index for AgenteFuerza
$this->params['breadcrumbs'][] = 'CREAR AGENCIA';
?>

<div class="agente-fuerza-update">

    <div class="col-md-12 text-end">
        <div class="float-right" style="margin-bottom:10px;">
            <?= Html::a('<i class="fas fa-undo"></i> Volver', ['index'], ['class' => 'btn btn-info btn-lg']) ?> 
        </div>
    </div>

    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header">
                <h1><?= Html::encode($this->title); ?></h1>
            </div>
            <div class="ms-panel-body">
                <?= $this->render('_form', [
                    'model' => $model,
                    
                ]) ?>
            </div>
        </div>
    </div>

</div>