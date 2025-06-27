<?php

use app\models\RmClinica;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\RmClinicaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Rm Clinicas';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="rm-clinica-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Rm Clinica', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'created_at',
            'rif:ntext',
            'nombre:ntext',
            'estado:ntext',
            //'direccion:ntext',
            //'telefono:ntext',
            //'correo:ntext',
            //'estatus:ntext',
            //'webpage:ntext',
            //'rs_instagram:ntext',
            //'QR Code:ntext',
            //'codigo_clinica:ntext',
            //'deleted_at',
            //'updated_at',
            //'private_key',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, RmClinica $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
