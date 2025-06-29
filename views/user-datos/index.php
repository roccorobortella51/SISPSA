<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\UserDatosSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'User Datos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-12">
                            <?= Html::a('Create User Datos', ['create'], ['class' => 'btn btn-success']) ?>
                        </div>
                    </div>


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

                            ['class' => 'hail812\adminlte3\yii\grid\ActionColumn'],
                        ],
                        'summaryOptions' => ['class' => 'summary mb-2'],
                        'pager' => [
                            'class' => 'yii\bootstrap4\LinkPager',
                        ]
                    ]); ?>


                </div>
                <!--.card-body-->
            </div>
            <!--.card-->
        </div>
        <!--.col-md-12-->
    </div>
    <!--.row-->
</div>
