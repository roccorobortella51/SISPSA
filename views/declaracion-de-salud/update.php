<?php

use yii\helpers\Html;


/** @var yii\web\View $this */
/** @var app\models\Agente $model */ // ¡Cambio aquí!

$this->title = 'DECLARACIÓN DE SALUD: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'DECLARACIÓN DE SALUD', 'url' => ['index', 'user_id' => $model->user_id]];
$this->params['breadcrumbs'][] = 'ACTUALIZAR';
?>

<div class="col-md-12 text-end">
    <div class="float-right" style="margin-bottom:10px;">
        <?= Html::a('<i class="fas fa-undo"></i> Volver', ['index', 'user_id' => $model->user_id], ['class' => 'btn btn-info btn-lg']) ?> 
    </div>
</div>

<div class="col-xl-12 col-md-12">
    <div class="ms-panel ms-panel-fh">
        <div class="ms-panel-header">
            <h3><?= $this->title = 'ACTUALIZAR DECLARACIÓN DE SALUD'; ?></h3> </div>
        <div class="ms-panel-body">
            <?= $this->render('_form', [ // Renderiza el _form de agente
                'model' => $model,
                'afiliado' => $afiliado
            ]) ?>        
        </div>
    </div>
</div>

