<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Pagos $model */
/** @var array $cuotas */

$this->title = 'Update Pagos: ' . $model->id;

?>
<div class="pagos-update">
    <div class="col-md-12 text-end">
        <div class="float-right" style="margin-bottom:10px;">
            <?= Html::a('<i class="fas fa-undo-alt"></i> Volver', Url::to(['contratos/index', 'user_id' => $model->user_id]), ['class' => 'btn btn-info btn-lg']) ?> 
        </div>
    </div>

    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-body">
                <?= $this->render('_form', [
                    'model' => $model,
                    'cuotas' => $cuotas, // ADD THIS LINE - pass the cuotas variable
                ]) ?>
            </div>
        </div>
    </div>
</div>