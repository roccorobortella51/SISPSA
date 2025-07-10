<?php

use app\models\Pagos;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use yii\widgets\DetailView;
use kartik\grid\ExpandRowColumn;


/** @var yii\web\View $this */
/** @var app\models\PagosSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'PAGOS';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pagos-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Nuevo Pagos', ['create','user_id' => $user_id], ['class' => 'btn btn-primary']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'created_at',
            'recibo_id',
            'fecha_pago',
            'monto_pagado',
            //'metodo_pago:ntext',
            //'estatus:ntext',
            //'numero_referencia_pago:ntext',
            //'updated_at',
            //'imagen_prueba:ntext',
            //'user_id',
            //'nombre_conciliador:ntext',
            //'fecha_conciliacion',
            //'fecha_registro',
            //'deleted_at',
            //'conciliador_id',
            //'conciliado',
            //'monto_usd',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Pagos $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
