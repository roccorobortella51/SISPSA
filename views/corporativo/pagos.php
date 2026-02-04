<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Pagos $model */
/** @var app\models\Corporativo $corporativo */
/** @var array $afiliados */

$this->title = 'Pago Corporativo: ' . Html::encode($corporativo->nombre);
$this->params['breadcrumbs'][] = ['label' => 'Corporativos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Html::encode($corporativo->nombre), 'url' => ['view', 'id' => $corporativo->id]];
$this->params['breadcrumbs'][] = 'Pago Corporativo';

?>
<div class="main-container">
    <div class="ms-panel-header">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="button-group-spacing">
            <?= Html::a('<i class="fas fa-undo"></i> Volver a Contratos', ['contracts', 'id' => $corporativo->id], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>
    <div class="ms-panel">
        <div class="ms-panel-body">
            <?= $this->render('_pagos_form', [
                'model' => $model,
                'corporativo' => $corporativo,
                'allCuotas' => $allCuotas,
                'grandTotal' => $grandTotal,
            ]) ?>
        </div>
    </div>
</div>