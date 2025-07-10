<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\RmClinica $model */

$this->title = 'Actualizar Plan: ' . $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Planes', 'url' => ['index', 'clinica_id' => $model->clinica_id]];
$this->params['breadcrumbs'][] = ['label' => $model->nombre, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Actualizar';
?>
<div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
        <div class="ms-panel-header d-flex justify-content-between align-items-center">
            <h1><?= $this->title = 'Actualizar Plan'; ?></h1>
    
            <div>
                <?= Html::a(
                    '<i class="fas fa-undo"></i> Volver', 
                    '#', 
                    [
                        'class' => 'btn btn-primary btn-lg', 
                        'onclick' => 'window.history.back(); return false;', 
                        'title' => 'Volver a la página anterior', 
                    ]
                ) ?> 
            </div>
        </div>
            <div class="ms-panel-body">
                <?= $this->render('_form', [
                    'model' => $model,
                ]) ?>        
            </div>
        </div>
</div>