<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\RmClinica $model */

$this->title = 'Actualizar Clínica: ' . $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Clínicas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->nombre, 'url' => ['view', 'id' => $model->id]];
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
                <h1><?= $this->title = 'Actualizar Clínica'; ?></h1>
            </div>
            <div class="ms-panel-body">
                <?= $this->render('_form', [
                    'model' => $model,
                ]) ?>        
            </div>
        </div>
</div>