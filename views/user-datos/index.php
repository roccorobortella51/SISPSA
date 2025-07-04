<?php

use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\UserSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */


// --- BREADCRUMBS ---

$this->params['breadcrumbs'][] = ['label' => 'Afiliados', 'url' => ['index']];
// --- FIN  --- 


$this->title = 'Gestión de Afiliados'; // Este sigue siendo el título para la página y breadcrumbs
?>
<h1>AGREGAR BOTON DE CARGA MASIVA</h1>
<h1>AGREGAR BOTON DE GENERAR PDF DEL CONTRATO con los datos del afiliado</h1>
<div class=row style="margin:3px !important;">
<input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />
    <div class="col-md-12 text-end">
        <div class="float-right" style="margin-bottom:10px;">
            <?= Html::a('<i class="fas fa-plus"></i> CREAR NUEVO AFILIADO DEL SÍSTEMA', ['create'], ['class' => 'btn btn-outline-success btn-lg']) ?> 
        </div>
    </div>
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header row">
                <span class="col-md-10"><h1><?= $this->title = 'Gestión de Afiliados'; ?></h1></span>
            </div>
            <div class="ms-panel-body">
                <div class="table-responsive">
                            <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],

                            //'id',
                            //'created_at',
                            //'user_id',
                            'nombres:ntext',
                            'sexo:ntext',
                            'fechanac',
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
                                        'class' => 'yii\grid\ActionColumn',
                                        'header' => 'ACCIONES',
                                        'template' => '<div class="d-flex justify-content-center gap-0">{view}{update}{delete}</div>',
                                        'options' => ['style' => 'width:55px; min-width:55px;'],
                                        'headerOptions' => ['style' => 'color: white!important;'],
                                        'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                                        'buttons' => [
                                            'view' => function ($url, $model, $key) {
                                                return Html::a(
                                                    '<i class="fa fa-eye"></i>',
                                                    Url::to(['view', 'id' => $model->id]),
                                                    [
                                                        'title' => 'Detalle de Usuario',
                                                        'class' => 'btn btn-link btn-sm text-success',
                                                        'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;'
                                                    ]
                                                );
                                            },
                                            'update' => function ($url, $model, $key) {
                                                return Html::a(
                                                    '<i class="fas fa-pencil-alt ms-text-primary"></i>',
                                                    Url::to(['update', 'id' => $model->id]),
                                                    [
                                                        'title' => 'Editar Usuario',
                                                        'class' => 'btn btn-link btn-sm text-success',
                                                        'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;'
                                                    ]
                                                );
                                            },
                                            'delete' => function ($url, $model, $key) {
                                                return Html::a(
                                                    '<i class="far fa-trash-alt ms-text-danger"></i>',
                                                    Url::to(['delete', 'id' => $model->id]),
                                                    [
                                                        'title' => 'Eliminar Usuario',
                                                        'data-confirm' => '¿Estás seguro de que quieres eliminar esta clínica?',
                                                        'data-method' => 'post',
                                                        'class' => 'btn btn-link btn-sm text-danger',
                                                        'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;'
                                                    ]
                                                );
                                            },
                                            
                                        ],
                                    ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</div>

