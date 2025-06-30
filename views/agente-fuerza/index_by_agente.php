<?php

use yii\helpers\Html;
use kartik\grid\GridView; // Usaremos Kartick GridView para consistencia si lo tienes instalado
use yii\helpers\Url;
use yii\grid\ActionColumn; // Para la columna de acciones
use yii\web\JqueryAsset; // Asegúrate de tener este 'use' si tu JS de paneles lo requiere
use app\models\AgenteFuerza; // Tu modelo AgenteFuerza
use app\models\Agente; // Tu modelo Agente

/** @var yii\web\View $this */
/** @var app\models\search\AgenteFuerzaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var int $id_agente */
/** @var app\models\Agente $agente */ // ¡Este es el objeto Agente que necesitas!

$this->title = 'FUERZA DE VENTA PARA AGENTE: ' . $agente->nom; // Título adaptado
$this->params['breadcrumbs'][] = ['label' => 'Agentes', 'url' => ['agente/index']];
$this->params['breadcrumbs'][] = ['label' => 'Agente: ' . $agente->nom, 'url' => ['agente/update', 'id' => $agente->id]];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="row" style="margin:3px !important;">
    <div class="col-md-12 text-end">
        <div class="float-right" style="margin-bottom:10px;">
            <?= Html::a('<i class="fas fa-plus"></i> CREAR MIEMBRO DE FUERZA DE VENTA', ['agente-fuerza/create', 'agente_id' => $id_agente], ['class' => 'btn btn-outline-primary btn-lg']) ?>
            <?= Html::a('Volver al Agente', ['agente/update', 'id' => $agente->id], ['class' => 'btn btn-outline-secondary btn-lg']) ?>
        </div>
    </div>
    

    <?php if (!$agente->isNewRecord) { ?>
        <div class="col-xl-12 col-md-12 mb-3"> <div class="row">
                <div class="col-md-6">
                    <div class="ms-panel ms-widget ms-identifier-widget bg-info panel-clickable" 
                         data-href="<?= Url::to(['agente/update', 'id' => $agente->id]) ?>">
                        <div class="ms-panel-header header-mini" style="padding-top: 35px; padding-bottom: 35px; text-align: center">
                            <h6 style="margin: 0;"> 
                                <?= Html::a(
                                    'ACTUALIZACIÓN DE AGENCIA',
                                    ['agente/update', 'id' => $agente->id],
                                    ['class' => 'text-white panel-link', 'style' => 'font-size: 1.40em;']
                                ) ?>
                            </h6>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="ms-panel ms-widget ms-identifier-widget bg-info" 
                         style="cursor: default;"> 
                        <div class="ms-panel-header header-mini" style="padding-top: 35px; padding-bottom: 35px; text-align: center">
                            <h6 style="margin: 0;">
                                <span class="text-white" style="font-size: 1.40em;">
                                    FUERZA DE VENTA
                                </span>
                            </h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

  
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header">
                <h1><?= Html::encode($this->title) ?></h1> </div>
            <div class="ms-panel-body">
                <div class="table-responsive">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'layout' => "{items}{pager}", // Puedes mantener o ajustar según necesites {summary}{items}{pager}

                        'tableOptions' => [
                            'class' => 'table table-striped table-bordered table-hover table-sm'
                        ],
                        'options' => [
                            'class' => 'grid-view-container table-responsive',
                        ],

                        'columns' => [
                           // ['class' => 'kartik\grid\SerialColumn'], // Usamos SerialColumn de Kartik para consistencia
                            
                            // Asegúrate de que estos atributos existan en tu modelo AgenteFuerza
                            // y sean relevantes para mostrar en la tabla
                            [
                                'attribute' => 'idusuario', // ID del usuario de la fuerza de venta
                                'label' => 'ID',
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar',
                                    'class' => 'form-control form-control-lg text-center',
                                ],
                                'options' => ['style' => 'width: 100px;'], // <--- para ancho de la celda
                            ],
                            // Puedes añadir una columna para mostrar el nombre del usuario si tienes una relación
                           
                            [
                                'attribute' => 'usuario.nombre_completo', // Asumiendo una relación 'usuario' en AgenteFuerza
                                'label' => 'Nombre',
                                'value' => function($model) {
                                    return $model->usuario->nombre_completo ?? 'N/A';
                                },
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar nombre',
                                    'class' => 'form-control form-control-lg text-center',
                                ],
                                'options' => ['style' => 'width: 30%;'], // <--- para ancho de la celda
                            ],
                           
                            [
                                'attribute' => 'por_venta',
                                'label' => '% Venta',
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'filterInputOptions' => [
                                    'placeholder' => '%',
                                    'class' => 'form-control form-control-lg text-center',
                                ],
                            ],
                            [
                                'attribute' => 'por_asesor',
                                'label' => '% Asesor',
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'filterInputOptions' => [
                                    'placeholder' => '%',
                                    'class' => 'form-control form-control-lg text-center',
                                ],
                            ],
                            // Puedes añadir más columnas de porcentajes o booleanas aquí
                            'puede_vender:boolean',
                            'puede_cobrar:boolean',
                            'puede_post_venta:boolean',

                            // Columna de Acciones (Ver, Editar, Eliminar)
                            [
                                'class' => ActionColumn::class,
                                'header' => 'ACCIONES',
                                'template' => '<div class="d-flex justify-content-center gap-0">{update}{delete}</div>', // No incluimos 'view' por ahora
                                'options' => ['style' => 'width:80px; min-width:80px;'],
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'contentOptions' => ['style' => 'text-align: center; padding: 10px !important;'],
                                'buttons' => [
                                    'update' => function ($url, $model, $key) use ($id_agente) {
                                        return Html::a(
                                            '<i class="fas fa-pencil-alt ms-text-primary"></i>',
                                            Url::to(['agente-fuerza/update', 'id' => $model->id, 'id_agente' => $id_agente]), // Pasamos id_agente
                                            [
                                                'title' => 'Editar',
                                                'class' => 'btn btn-link btn-sm text-success',
                                                'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;'
                                            ]
                                        );
                                    },
                                    'delete' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fas fa-trash-alt"></i>',
                                            Url::to(['agente-fuerza/delete', 'id' => $model->id]),
                                            [
                                                'title' => 'Eliminar',
                                                'class' => 'btn btn-link btn-sm text-danger',
                                                'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;',
                                                'data-confirm' => '¿Estás seguro de que quieres eliminar este miembro de la fuerza de venta?',
                                                'data-method' => 'post'
                                            ]
                                        );
                                    },
                                ],
                            ],
                        ], // Fin de columns
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>

