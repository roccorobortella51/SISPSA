<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Pagos $model */


\yii\web\YiiAsset::register($this);
?>
<div class="pagos-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('<i class="fas fa-undo-alt"></i> Volver', Url::to(['contratos/index', 'user_id' => $model->user_id]), ['class' => 'btn btn-info']) ?> 

        <?= Html::a('Borrar', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'created_at',
            'recibo_id',
            'fecha_pago',
            'monto_pagado',
            'metodo_pago:ntext',
            'estatus:ntext',
            'numero_referencia_pago:ntext',
            'updated_at',
            'imagen_prueba:url',
            'user_id',
            'nombre_conciliador:ntext',
            'fecha_conciliacion',
            'fecha_registro',
            'deleted_at',
            'conciliador_id',
            'conciliado',
            'monto_usd',
        ],
    ]) ?>

</div>
