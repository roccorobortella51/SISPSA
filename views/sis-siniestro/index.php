<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use app\components\UserHelper;


/**
 * @var yii\web\View $this
 * @var app\models\SisSiniestroSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\UserDatos $afiliado
 * @var int $user_id
 * @var string $modo 'siniestro' o 'cita' <-- ASUMIMOS QUE ESTO SE PASA DESDE EL CONTROLADOR
 */

// ----------------------------------------------------------------------
// 1. LÓGICA DE MODO Y BOTONES
// ----------------------------------------------------------------------
$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol == 'DIRECTOR-COMERCIALIZACION' || $rol == 'Asesor' || $rol == 'Agente' || $rol == "ADMISIÓN" || $rol == "CONTROL DE CITAS" || $rol == "Administrador-clinica");

// Definir variables basadas en el modo
$esCita = ($modo === 'cita') ? 1 : 0;
$tituloModo = ($modo === 'cita') ? 'Citas' : 'Siniestros';
$textoBoton = ($modo === 'cita') ? 'Crear Nueva Cita' : 'Crear Nuevo Siniestro';

$this->params['breadcrumbs'][] = ['label' => 'AFILIADOS', 'url' => ['/user-datos/index-clinicas', 'clinica_id' => $afiliado->clinica_id]];
// Título ahora refleja el modo
$this->title = $tituloModo . ' para ' . Html::encode($afiliado->nombres . " " . $afiliado->apellidos . " " . $afiliado->tipo_cedula . "-" . $afiliado->cedula);
// ----------------------------------------------------------------------
?>

<div class="row" style="margin:3px !important;">
<input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />
    <div class="col-md-12 text-end">
       
    </div>
    <div class="col-md-12">
        <div class="ms-panel ms-panel-fh">
        <div class="ms-panel-header d-flex justify-content-between align-items-center">
            <h1><?= $this->title ?></h1>
            <div class="d-flex gap-3"> <?php 
                // BOTÓN DE CREACIÓN DINÁMICO
                if($permisos){ 
                    echo Html::a(
                        '<i class="fas fa-plus"></i> ' . $textoBoton, 
                        // Enlace a actionCreate, pasando user_id y el valor binario es_cita (0 o 1)
                        ['create', 'user_id' => $user_id, 'es_cita' => $esCita], 
                        ['class' => 'btn btn-outline-primary btn-lg']
                    ); 
                } 
                ?>
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
                            // Columna para mostrar si es Cita o Siniestro (Opcional, pero útil)
                            [
                                'label' => 'Tipo',
                                'attribute' => 'es_cita',
                                'format' => 'Html',
                                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                                'value' => function($model) {
                                    return $model->es_cita == 1 ? '<span class="status-badge active bg-success">Cita</span>' : '<span class="status-badge inactive bg-primary">Siniestro</span>';
                                },
                                'filter' => [0 => 'Siniestro', 1 => 'Cita'],
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
                                        // Aseguramos que user_id se pase para mantener la navegación contextual.
                                        return Html::a(
                                            '<i class="fa fa-eye"></i>',
                                            Url::to(['view', 'id' => $model->id, 'user_id' => $model->iduser]), 
                                            [
                                                'title' => 'Detalle de la atención',
                                                'class' => 'btn-action view'
                                            ]
                                        );
                                    },
                                    'update' => function ($url, $model, $key)use($permisos) {
                                        if($permisos){
                                        // Aseguramos que user_id se pase para mantener la navegación contextual.
                                        return Html::a(
                                            '<i class="fas fa-pencil-alt"></i>',
                                            Url::to(['update', 'id' => $model->id, 'user_id' => $model->iduser]), 
                                            [
                                                'title' => 'Editar',
                                                'class' => 'btn-action edit'
                                            ]
                                        );}
                                    },
                                    // ... (Botón de delete comentado) ...
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