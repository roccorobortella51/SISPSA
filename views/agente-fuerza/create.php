<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AgenteFuerza $model */

$this->title = 'Crear agente';
$this->params['breadcrumbs'][] = ['label' => 'Agente Fuerzas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="agente-fuerza-create">

    <div class="col-xl-12 col-md-12">
        
            <div class="ms-panel-header">
                <h1> CREAR ASESOR VENDEDOR </h1>
            </div>
        </div>

    </div>
        <?= $this->render('_form', [
            'model' => $model,
            'agente' => $agente,
        ]) ?>

</div>
