<?php

use app\models\Baremo;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\BaremoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Baremos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="baremo-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Baremo', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'created_at',
            'nombre_servicio:ntext',
            'descripcion:ntext',
            'estatus:ntext',
            //'deleted_at',
            //'updated_at',
            //'precio',
            //'clinica_id',
            //'costo',
            //'area_id',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Baremo $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
