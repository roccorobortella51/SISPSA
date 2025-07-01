<?php

use app\models\UserDatos;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\UserDatosSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'User Datos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-datos-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create User Datos', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'created_at',
            'user_id',
            'nombres:ntext',
            'fechanac',
            //'sexo:ntext',
            //'selfie:ntext',
            //'telefono:ntext',
            //'estado:ntext',
            //'role:ntext',
            //'estatus:ntext',
            //'imagen_identificacion:ntext',
            //'qr:ntext',
            //'paso',
            //'video:ntext',
            //'ciudad:ntext',
            //'municipio:ntext',
            //'parroquia:ntext',
            //'direccion:ntext',
            //'codigoValidacion:ntext',
            //'clinica_id',
            //'plan_id',
            //'apellidos:ntext',
            //'email:email',
            //'contrato_id',
            //'asesor_id',
            //'deleted_at',
            //'updated_at',
            //'ver_cedula:ntext',
            //'ver_foto:ntext',
            //'session_id',
            //'cedula',
            //'tipo_cedula:ntext',
            //'tipo_sangre:ntext',
            //'estatus_solvente:ntext',
            //'user_login_id',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, UserDatos $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
