<?php

// Importaciones necesarias
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use yii\widgets\ActiveForm;
use app\components\UserHelper;
use kartik\widgets\SwitchInput;
use kartik\select2\Select2;
/**
 * @var yii\web\View $this
 * @var app\models\RmClinicaSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 */

// --- BREADCRUMBS ---

$this->params['breadcrumbs'][] = ['label' => 'AFILIADO', 'url' => ['/user-datos/update', 'id' => $afiliado->id]];
// --- FIN  --- 


$this->title = 'Gestión de Declaración de Salud del Afiliado'; // Este sigue siendo el título para la página y breadcrumbs

?>

<div class=row style="margin:3px !important;">
    <!-- Nuevo contenedor para los botones "Volver" y "Crear Declaración de Salud" -->
    <div class="col-md-12 d-flex justify-content-center gap-3" style="margin-bottom:10px;">
        <?= Html::a('<i class="fas fa-undo"></i> Volver', ['/user-datos/update', 'id' => $afiliado->id], ['class' => 'btn btn-secondary btn-lg rounded-pill px-7 shadow-sm']) ?> 
        <?= Html::a('<i class="fas fa-plus"></i> CREAR DECLARACIÓN DE SALUD', ['create', 'user_id' => $afiliado->id], ['class' => 'btn btn-outline-primary btn-lg']) ?> 
    </div>


    </div>
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header">
                <div class="ms-panel-header">
                <h1>Gestión de Declaración de Salud del Afiliado: <?= $this->title = 'Nombre completo: '.$afiliado->nombres." ".$afiliado->apellidos.', Cédula: ' . $afiliado->cedula; ?></h1>
            </div>

            </div>
            <div class="ms-panel-body">
                        <div class="table-responsive">
                   
                            <?= GridView::widget([
                                'dataProvider' => $dataProvider,
                                'filterModel' => $searchModel,
                                'layout' => "{items}{pager}",
                                'columns' => [
                                    ['class' => 'yii\grid\SerialColumn'],

                                    //'id',
                                    //'created_at',
                                    //'p1_sino:ntext',
                                    //'p1_especifica:ntext',
                                    //'p2_sino:ntext',
                                    //'p2_especifica:ntext',
                                    //'p3_sino:ntext',
                                    //'p3_especifica:ntext',
                                    //'p4_sino:ntext',
                                    //'p4_especifica:ntext',
                                    //'p5_sino:ntext',
                                    //'p5_especifica:ntext',
                                    //'p6_sino:ntext',
                                    //'p6_especifica:ntext',
                                    //'p7_sino:ntext',
                                    //'p7_especifica:ntext',
                                    //'p8_sino:ntext',
                                    //'p8_especifica:ntext',
                                    //'p9_sino:ntext',
                                    //'p9_especifica:ntext',
                                    //'p10_sino:ntext',
                                    //'p10_especifica:ntext',
                                    //'p11_sino:ntext',
                                    //'p11_especifica:ntext',
                                    //'p12_sino:ntext',
                                    //'p12_especifica:ntext',
                                    //'p13_sino:ntext',
                                    //'p13_especifica:ntext',
                                    //'p14_sino:ntext',
                                    //'p14_especifica:ntext',
                                    //'p15_sino:ntext',
                                    //'p15_especifica:ntext',
                                    //'p16_sino:ntext',
                                    //'p16_especifica:ntext',
                                    //'deleted_at',
                                    //'updated_at',
                                    //'ver_usuario_id',
                                    'ver_observacion:ntext',
                                    //'ver_si_no:ntext',
                                    //'ver_fecha',
                                    //'url_video_declaracion:ntext',
                                    //'estatus:ntext',
                                    //'user_id',
                                    'estatura:ntext',
                                    'peso:ntext',
                                    [
                                    'class' => 'yii\grid\ActionColumn',
                                    'header' => 'ACCIONES',
                                    'template' => '<div class="d-flex justify-content-center gap-0">{view}{salud}{update}</div>',
                                    'options' => ['style' => 'width:55px; min-width:55px;'],
                                    'headerOptions' => ['style' => 'color: white!important;'],
                                    'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                                    'buttons' => [
                                        'view' => function ($url, $model, $key) {
                                            return Html::a(
                                                '<i class="fa fa-eye"></i>',
                                                Url::to(['view', 'id' => $model->id]),
                                                [
                                                    'title' => 'Detalle de la Declaración de Salud',
                                                    'class' => 'btn-action view'
                                                ]
                                            );
                                        },    
                                        'salud' => function ($url, $model, $key) {
                                            return Html::a(
                                                '<i class="fas fa-file-pdf ms-text-danger"></i>',
                                                Url::to(['generar-pdf', 'id' => $model->id]),
                                                [
                                                    'title' => 'Declaración de salud',
                                                    'class' => 'btn-action view',
                                                    'target' => '_blank'
                                                ]
                                            );
                                        },
                                        'update' => function ($url, $model, $key) {
                                            return Html::a(
                                                '<i class="fas fa-pencil-alt ms-text-primary"></i>',
                                                Url::to(['update', 'id' => $model->id]),
                                                [
                                                    'title' => 'Editar',
                                                    'class' => 'btn-action view'
                                                ]
                                            );
                                        },
                                        /*'delete' => function ($url, $model, $key) {
                                            return Html::a(
                                                '<i class="far fa-trash-alt ms-text-danger"></i>',
                                                Url::to(['delete', 'id' => $model->id]),
                                                [
                                                    'title' => 'Eliminar',
                                                    'data-confirm' => '¿Estás seguro de que quieres eliminar esta clínica?',
                                                    'data-method' => 'post',
                                                    'class' => 'btn-action view'
                                                ]
                                            );
                                        },*/
                                        
                                    ],
                                ],
                                ],
                            ]); ?>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="clearfix"></div>
        </div>
