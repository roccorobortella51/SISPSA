<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Pagos $model */

$this->title = 'Crear Pagos';
$this->params['breadcrumbs'][] = ['label' => 'Pagos', 'url' => ['contratos/index', 'user_id' => $model->user_id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pagos-create">
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="text-primary"><?= Html::encode("Crear Pago") ?></h4>
                <div class="float-right" style="margin-bottom:10px;">
                    <?= Html::a('<i class="fas fa-undo-alt"></i> Volver', ['contratos/index', 'user_id' => $model->user_id], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                </div>
            </div>
        </div>
            <div class="ms-panel-body">
                <?= $this->render('_form', [
                    'model' => $model,
                    
                ]) ?>
            </div>
        </div>
    </div>

</div>
