<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use kartik\widgets\SwitchInput;

/**
 * @var yii\web\View $this
 * @var app\models\SisSiniestroSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\UserDatos $afiliado
 * @var int $user_id
 */

$this->params['breadcrumbs'][] = ['label' => 'AFILIADOS', 'url' => ['/user-datos/index-clinicas', 'clinica_id' => $afiliado->clinica_id]];
$this->title = 'Atención ' . Html::encode($afiliado->nombres . " " . $afiliado->apellidos . " " . $afiliado->tipo_cedula . "-" . $afiliado->cedula);

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
                <?= Html::a('<i class="fas fa-plus"></i> CREAR NUEVA ATENCIÓN', ['create', 'user_id' => $user_id], ['class' => 'btn btn-outline-primary btn-lg']) ?>
                <?= Html::a(
                    '<i class="fas fa-undo mr-2"></i> Volver',
                    '#',
                    [
                        'class' => 'btn btn-outline-secondary btn-lg', // Estilo ajustado para coincidir
                        'onclick' => 'window.history.back(); return false;',
                        'title' => 'Volver a la página anterior',
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
                            'class' => 'table table-striped table-bordered table-hover table-sm'
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
                            'fecha',
                            'hora',
                            [
                                'attribute' => 'idbaremo',
                                'value' => 'baremo.nombre_servicio', // Corregido para usar la relación 'baremo' y el campo 'nombre_servicio'
                                'label' => 'Baremo',
                            ],
                            [
                                'attribute' => 'atendido',
                                'value' => function($model) {
                                    return $model->atendido ? 'Sí' : 'No';
                                },
                                'filter' => [0 => 'No', 1 => 'Sí'],
                            ],
                            'fecha_atencion',
                            'hora_atencion',
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
                                    'update' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fas fa-pencil-alt"></i>', // Icono sin ms-text-primary, ya que btn-action maneja el color
                                            Url::to(['update', 'id' => $model->id, 'user_id' => $model->iduser]), // Asegura que user_id se pase para la navegación
                                            [
                                                'title' => 'Editar',
                                                'class' => 'btn-action edit'
                                            ]
                                        );
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
