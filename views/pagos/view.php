<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Pagos $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Pagos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="pagos-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
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
            'imagen_prueba:ntext',
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
