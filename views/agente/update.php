<?php

use yii\helpers\Html;


/** @var yii\web\View $this */
/** @var app\models\Agente $model */ // ¡Cambio aquí!

$this->title = 'ACTUALIZAR AGENTE: ' . $model->nom;
$this->params['breadcrumbs'][] = ['label' => 'AGENCIAS', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'ACTUALIZAR';
?>


<div class="col-xl-12 col-md-12">
    <div class="ms-panel ms-panel-fh">
        <div class="ms-panel-header">
            <h3><?= $this->title = 'ACTUALIZAR AGENCIA'; ?></h3> </div>
        <div class="ms-panel-body">
            <?= $this->render('_form', [ // Renderiza el _form de agente
                'model' => $model,
            ]) ?>        
        </div>
    </div>
</div>

