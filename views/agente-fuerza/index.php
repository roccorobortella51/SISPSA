<?php

use app\models\AgenteFuerza;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\AgenteFuerzaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Agente Fuerzas';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="agente-fuerza-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Agente Fuerza', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'idusuario',
            'agente_id',
            'por_venta',
            'por_asesor',
            //'por_cobranza',
            //'por_post_venta',
            //'puede_vender',
            //'puede_asesorar',
            //'puede_cobrar',
            //'puede_post_venta',
            //'created_at',
            //'updated_at',
            //'deleted_at',
            //'puede_registrar',
            //'por_registrar',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, AgenteFuerza $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
