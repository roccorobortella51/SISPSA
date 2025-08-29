<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use kartik\widgets\SwitchInput;
use app\components\UserHelper;

/**
 * @var yii\web\View $this
 * @var app\models\SisSiniestroSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\UserDatos $afiliado
 * @var int $user_id
 */

$this->params['breadcrumbs'][] = ['label' => 'AFILIADOS', 'url' => ['/user-datos/index-clinicas', 'clinica_id' => $afiliado->clinica_id]];
$this->title = 'Atención ' . Html::encode($afiliado->nombres . " " . $afiliado->apellidos . " " . $afiliado->tipo_cedula . "-" . $afiliado->cedula);
$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol == 'GERENTE-COMERCIALIZACION' || $rol == 'Asesor' || $rol == 'Agente' || $rol == "ADMISIÓN" || $rol == "CONTROL DE CITAS" || $rol == "COORDINADOR-CLINICA");

                                   

?>

<div class="row" style="margin:3px !important;">
<input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />
    <div class="col-md-12 text-end">
       
    </div>
    <div class="col-md-12">
        <div class="ms-panel ms-panel-fh">
        <div class="ms-panel-header d-flex justify-content-between align-items-center">
            <h1><?= $this->title ?></h1>
            <div class="d-flex gap-3"> <!-- Contenedor flex para los botones con espacio -->

                <?php if($permisos){ echo  Html::a('<i class="fas fa-plus"></i> CREAR NUEVA ATENCIÓN', ['create', 'user_id' => $user_id], ['class' => 'btn btn-outline-primary btn-lg']); } ?>
                <?= Html::a(
                    '<i class="fas fa-undo mr-2"></i> Volver',
                    ['/user-datos/index-clinicas', 'clinica_id' => $afiliado->clinica_id],
                    [
                        'class' => 'btn btn-outline-secondary btn-lg',
                        'title' => 'Volver a la lista de afiliados',
                        'data' => ['pjax' => 0],
                    ]
                ) ?>
            </div>
        </div>
            <div class="ms-panel-body">
                <div class="table-responsive">
                    <?= GridView::widget([
                        'id' => 'clinica-grid',
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'layout' => "{items}{pager}",
                        'resizableColumns' => false,
                        'bordered' => false,
                        'responsiveWrap' => false,
                        'persistResize' => false,
                        'tableOptions' => [
                            'class' => 'table table-striped table-bordered table-hover '
                        ],
                        'options' => [
                            'class' => 'grid-view-container table-responsive',
                        ],
                        'columns' => [
                            [
                                'attribute' => 'idclinica',
                                'value' => 'clinica.nombre', // Corregido para usar la relación 'clinica'
                                'label' => 'Clínica',
                            ],
                            [

                            'attribute' => 'fecha',
                                'format' => 'Html',
                                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                                'value' => function($model) {

                                    return Yii::$app->formatter->asDate($model->fecha);
                                     
                                },
                            ],
                            [
                                'attribute' => 'hora',
                                'format' => 'Html',
                                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                                'value' => function($model) {

                                    return Yii::$app->formatter->asTime($model->hora);
                                     
                                },
                            ],

                            [
                                'attribute' => 'baremos',
                                'format' => 'raw',
                                'contentOptions' => ['style' => 'max-width: 250px; white-space: normal;'],
                                'value' => function($model) {
                                    $baremos = $model->baremos;
                                    if (empty($baremos)) {
                                        return '<span class="text-muted">No hay baremos</span>';
                                    }
                                    
                                    $items = [];
                                    foreach ($baremos as $baremo) {
                                        if (is_array($baremo) && isset($baremo['nombre_servicio'])) {
                                            $items[] = Html::tag('div', 
                                                Html::encode($baremo['nombre_servicio']),
                                                ['class' => 'mb-1']
                                            );
                                        } elseif (is_object($baremo) && property_exists($baremo, 'nombre_servicio')) {
                                            $items[] = Html::tag('div', 
                                                Html::encode($baremo->nombre_servicio),
                                                ['class' => 'mb-1']
                                            );
                                        }
                                    }
                                    
                                    return !empty($items) ? implode('', $items) : '<span class="text-muted">No hay baremos</span>';
                                },
                                'label' => 'Baremos',
                            ],
                            [

                            'attribute' => 'fecha_atencion',
                                'format' => 'Html',
                                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                                'value' => function($model) {

                                    return Yii::$app->formatter->asDate($model->fecha_atencion);
                                     
                                },
                            ],
                            [
                                'attribute' => 'hora_atencion',
                                'format' => 'Html',
                                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                                'value' => function($model) {

                                    return Yii::$app->formatter->asTime($model->hora_atencion);
                                     
                                },
                            ],

                            [
                                'attribute' => 'costo_total',
                                'format' => ['currency', 'USD'],
                                'contentOptions' => ['style' => 'text-align: right;'],
                                'filter' => false
                            ],
                            
                            [
                                'attribute' => 'atendido',
                                'format' => 'Html',
                                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                                'value' => function($model) {
                                     $isTrue = $model->atendido;
                                     return $isTrue == 1 ? '<p class="status-badge active">Sí</p>' : '<p class="status-badge inactive">No</p>';
                                },
                                'filter' => [0 => 'No', 1 => 'Sí'],
                            ],


                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => 'ACCIONES',
                                'template' => '<div class="d-flex justify-content-center gap-0">{view}{update}</div>',
                                'options' => ['class' => 'action-buttons'],
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fa fa-eye"></i>',
                                            Url::to(['view', 'id' => $model->id, 'user_id' => $model->iduser]), // Asegura que user_id se pase para la navegación
                                            [
                                                'title' => 'Detalle de la atención',
                                                'class' => 'btn-action view'
                                            ]
                                        );
                                    },
                                    'update' => function ($url, $model, $key)use($permisos) {

                                        if($permisos){
                                        return Html::a(
                                            '<i class="fas fa-pencil-alt"></i>', // Icono sin ms-text-primary, ya que btn-action maneja el color
                                            Url::to(['update', 'id' => $model->id, 'user_id' => $model->iduser]), // Asegura que user_id se pase para la navegación
                                            [
                                                'title' => 'Editar',
                                                'class' => 'btn-action edit'
                                            ]
                                        );}
                                    },
                                    // El botón de eliminar está comentado en tu código original, lo mantengo así.
                                    /*'delete' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="far fa-trash-alt"></i>',
                                            Url::to(['delete', 'id' => $model->id]),
                                            [
                                                'title' => 'Eliminar',
                                                'data-confirm' => '¿Estás seguro de que quieres eliminar esta clínica?',
                                                'data-method' => 'post',
                                                'class' => 'btn-action delete'
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
    <div class="clearfix"></div>
</div>
