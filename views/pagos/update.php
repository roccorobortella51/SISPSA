<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Pagos $model */

$this->title = 'Update Pagos: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Pagos', 'url' => ['contratos/index', 'user_id' => $model->user_id]];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="pagos-update">
    <div class="col-md-12 text-end">
        <div class="float-right" style="margin-bottom:10px;">
            <?= Html::a('<i class="fas fa-undo"></i> Volver', ['contratos/index', 'user_id' => $model->user_id], ['class' => 'btn btn-info btn-lg']) ?> 
        </div>
    </div>

    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
    <div class="ms-panel-body">
        <?= $this->render('_form', [
            'model' => $model,
            'isEditable' => $isEditable,
        ]) ?>
    </div>
            </div>
        </div>

</div>
