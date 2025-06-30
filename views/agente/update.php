<?php

use yii\helpers\Html;


/** @var yii\web\View $this */
/** @var app\models\Agente $model */ // ¡Cambio aquí!

$this->title = 'Actualizar Agente: ' . $model->nom;
$this->params['breadcrumbs'][] = ['label' => 'Agentes', 'url' => ['index']]; 
$this->params['breadcrumbs'][] = ['label' => $model->nom, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Actualizar';
?>

<div class="col-md-12 text-end">
    <div class="float-right" style="margin-bottom:10px;">
        <?= Html::a('<i class="fas fa-undo"></i> Volver', ['index'], ['class' => 'btn btn-warning btn-lg']) ?> 
    </div>
</div>

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

