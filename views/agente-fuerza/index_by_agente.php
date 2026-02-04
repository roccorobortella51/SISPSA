<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\web\JqueryAsset;
use app\models\AgenteFuerza;
use app\models\Agente;
use app\components\UserHelper;


/** @var yii\web\View $this */
/** @var app\models\search\AgenteFuerzaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var int $id_agente */
/** @var app\models\Agente $agente */

$this->title = 'FUERZA DE VENTA';
$this->params['breadcrumbs'][] = ['label' => 'AGENCIAS', 'url' => ['agente/index']];
$this->params['breadcrumbs'][] = ['label' => $agente->nom, 'url' => ['agente/update', 'id' => $agente->id]];
$this->params['breadcrumbs'][] = $this->title;

$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol == 'DIRECTOR-COMERCIALIZACIÓN');

?>

<div class="row" style="margin:3px !important;">
   
   <?php if (!$agente->isNewRecord) { ?>
    <div class="col-xl-12 col-md-12 mb-3">
        <div class="row row-cols-1 row-cols-md-4 g-3">
            <div class="col">
                <?= Html::a(
                    '<i class="fas fa-building"></i> <span style="font-weight: bold;">AGENCIAS</span>',
                    ['agente/index'],
                    // Se ha ajustado el padding y el font-size para que los botones sean un poco más pequeños.
                    ['class' => 'btn btn-info w-100', 'style' => 'padding: 1rem 2rem !important; font-size: 1.25rem !important;']
                ) ?>
            </div>
            <?php if($permisos){ ?>
            <div class="col">
                <?= Html::a(
                    '<i class="fas fa-undo"></i> <span style="font-weight: bold; color:black;">VOLVER</span>',
                    ['agente/update', 'id' => $agente->id],
                    // Se ha ajustado el padding y el font-size para que los botones sean un poco más pequeños.
                    ['class' => 'btn btn-secondary w-100', 'style' => 'padding: 1rem 2rem !important; font-size: 1.25rem !important;']
                ) ?>
            </div>
            
            <div class="col">
                <?= Html::a(
                    '<i class="fas fa-plus"></i> <span style="font-weight: bold; color:white;">ASIGNAR AGENTE</span>',
                    ['agente-fuerza/create', 'agente_id' => $agente->id],
                    // Se ha ajustado el padding y el font-size para que los botones sean un poco más pequeños.
                    ['class' => 'btn btn-success w-100', 'style' => 'padding: 1rem 2rem !important; font-size: 1.25rem !important;']
                ) ?>
            </div>

            <div class="col">
                <?= Html::a(
                    '<i class="fas fa-upload"></i> <span style="font-weight: bold; color:white;">CARGA MASIVA</span>',
                    '#',
                    // Se ha ajustado el padding y el font-size para que los botones sean un poco más pequeños.
                    ['class' => 'btn btn-primary w-100 disabled', 'style' => 'padding: 1rem 2rem !important; font-size: 1.25rem !important;']
                ) ?>
            </div>
            <?php } ?>
        </div>
    </div>
<?php } ?>
    
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header">
                <h1><?= Html::encode($this->title).": ".$agente->nom ?> </h1>
            </div>
            <div class="ms-panel-body">
                <div class="table-responsive">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'layout' => "{items}{pager}",
                        'resizableColumns' => false,
                        'bordered' => false,
                        'responsiveWrap' => false,
                        'persistResize' => false,
                        'tableOptions' => [
                            'class' => 'table table-striped table-bordered table-hover'
                        ],
                        'options' => [
                            'class' => 'grid-view-container table-responsive',
                        ],
                        'columns' => [
                            [
                                'attribute' => 'id',
                                'label' => 'N° de Agente',
                                'contentOptions' => ['class' => 'text-center'],
                                'value' => function($model) {
                                    return $model->id;
                                },
                                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar N° de Agente',
                                    'class' => 'form-control form-control-lg text-center',
                                ],
                            ],
                            [
                                'attribute' => 'agenteFuerzaUserNombres',
                                'label' => 'Nombre',
                                'contentOptions' => ['class' => 'text-center'],
                                'value' => function($model) {
                                    if ($model->userDatos) {
                                        return $model->userDatos->nombres . ' ' . $model->userDatos->apellidos;
                                    }
                                    return 'N/A';
                                },
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar nombre',
                                    'class' => 'form-control form-control-lg text-center',
                                ],
                            ],
                            [
                                'label' => 'Cedula de identidad',
                                'contentOptions' => ['class' => 'text-center'],
                                'value' => function($model) {
                                    if ($model->userDatos) {
                                        return $model->userDatos->cedula;
                                    }
                                    return 'No disponible';
                                },
                                'attribute' => 'agenteFuerzaUserCedula',
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar cedula de identidad',
                                    'class' => 'form-control form-control-lg text-center',
                                ],
                            ],
                            [
                                'label' => 'Correo Electrónico',
                                'contentOptions' => ['class' => 'text-center'],
                                'value' => function($model) {
                                    if ($model->userDatos) {
                                        return $model->userDatos->email;
                                    }
                                    return 'No disponible';
                                },
                                'attribute' => 'agenteFuerzaUserEmail',
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar correo',
                                    'class' => 'form-control form-control-lg text-center',
                                ],
                            ],
                            [
                                'label' => 'Teléfono',
                                'contentOptions' => ['class' => 'text-center'],
                                'value' => function($model) {
                                    return $model->userDatos->telefono ?? 'No disponible';
                                },
                                'attribute' => 'agenteFuerzaUserTelefono',
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar teléfono',
                                    'class' => 'form-control form-control-lg text-center',
                                ],
                            ],
                            // **SOLUCIÓN: Copiar la ActionColumn de la otra vista**
                            [
                                'class' => ActionColumn::class,
                                'header' => 'ACCIONES',
                                'template' => '{view}&nbsp;{update}',
                                'options' => ['class' => 'action-buttons'],
                                'headerOptions' => ['style' => 'color: white!important;'], 
                                'contentOptions' => ['class' => 'text-center'],
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fas fa-eye"></i>',
                                            $url,
                                            [
                                                'title' => 'Ver Detalle',
                                                'class' => 'btn-action view'
                                            ]
                                        );
                                    },
                                    'update' => function ($url, $model, $key) use ($permisos) {
                                        if ($permisos)
                                            return Html::a(
                                                '<i class="fas fa-pen"></i>',
                                                $url,
                                                [
                                                    'title' => 'Editar',
                                                    'class' => 'btn-action edit'
                                                ]
                                            );
                                    },
                                    // Eliminamos el botón 'afiliados' para que coincida con el ejemplo
                                ],
                            ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
