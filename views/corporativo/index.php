<?php

use app\models\Corporativo;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\CorporativoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Corporativos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="corporativo-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Corporativo', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'nombre',
            'email:email',
            'telefono',
            'rif',
            //'estado',
            //'municipio',
            //'parroquia',
            //'direccion:ntext',
            //'codigo_asesor',
            //'lugar_registro',
            //'fecha_registro_mercantil',
            //'tomo_registro',
            //'folio_registro',
            //'domicilio_fiscal:ntext',
            //'contacto_nombre',
            //'contacto_cedula',
            //'contacto_telefono',
            //'contacto_cargo',
            //'estatus',
            //'created_at',
            //'updated_at',
            //'deleted_at',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Corporativo $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
