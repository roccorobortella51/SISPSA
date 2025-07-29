<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\RmClinica $model */

$this->title = 'ACTUALIZAR VERIFICACIÓN: ' . $model->clinica->nombre;
$this->params['breadcrumbs'][] = ['label' => 'CLINICAS', 'url' => ['index', 'clinica_id' => $model->clinica_id]];
$this->params['breadcrumbs'][] = ['label' => "DETALLE DE LA VERIFICACIÓN", 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'ACTUALIZAR VERIFICACIÓN';
?>

<div class="col-xl-12 col-md-12">
    <div class="ms-panel ms-panel-fh">
        <div class="ms-panel-header">
            <h2><?= Html::encode($this->title); ?></h2>
            
                <div class="float-right" style="margin-bottom:10px;">
                    <?= Html::a('<i class="fas fa-undo"></i> Volver', ['index', 'clinica_id' => $model->clinica_id], ['class' => 'btn btn-secondary']) ?>
                </div>
            
        </div>
        <div class="ms-panel-body">
            <?= $this->render('_form', [
                'model' => $model,
                'clinica' => $clinica
            ]) ?>
        </div>
    </div>
</div>