<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AgenteFuerza $model */

$this->title = 'CREAR INTERMEDIARIO';
$this->params['breadcrumbs'][] = ['label' => 'INTERMEDIARIO'];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="agente-fuerza-create">

    <div class="col-xl-12 col-md-12">

        <div class="ms-panel-header">
            <h1> CREAR INTERMEDIARIO </h1>
        </div>
    </div>

</div>

<?php if (!isset($agente)) {
    $agente = null;
} ?>

<?= $this->render('_form', [
    'model' => $model,
    'agente' => $agente,
]) ?>

</div>