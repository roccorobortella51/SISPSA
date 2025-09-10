<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use app\models\AgenteFuerza;
use app\models\Agente;
use app\components\UserHelper;

/** @var yii\web\View $this */
/** @var app\models\search\AgenteFuerzaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var int $id_agente */
/** @var app\models\Agente $agente */

$this->title = 'FUERZA DE VENTA: ' . $agente->nom;
$this->params['breadcrumbs'][] = ['label' => 'AGENCIAS', 'url' => ['agente/index']];
$this->params['breadcrumbs'][] = ['label' => $agente->nom, 'url' => ['agente/update', 'id' => $agente->id]];
$this->params['breadcrumbs'][] = 'Fuerza de Venta';

$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol == 'GERENTE-COMERCIALIZACION');

?>

<?php if (!$agente->isNewRecord) : ?>
<div class="col-xl-12 col-md-12 mb-3">
    <div class="row row-cols-1 row-cols-md-4 g-3">
        <div class="col">
            <?= Html::a(
                '<i class="fas fa-building"></i> AGENCIAS',
                ['agente/index'],
                [
                    'class' => 'btn btn-lg w-100',
                    'style' => 'background-color: #F3F4F6; border-color: #F3F4F6; color: #000;'
                ]
            ) ?>
        </div>
        <?php if($permisos): ?>
        <div class="col">
            <?= Html::a(
                '<i class="fas fa-undo"></i> VOLVER A AGENCIA',
                ['agente/update', 'id' => $agente->id],
                [
                    'class' => 'btn btn-lg w-100',
                    'style' => 'background-color: #13EAB1; border-color: #13EAB1; color: #000;'
                ]
            ) ?>
        </div>
        <div class="col">
            <?= Html::a(
                '<i class="fas fa-plus"></i> CREAR AGENTE/ASESOR',
                ['agente-fuerza/create', 'agente_id' => $agente->id],
                [
                    'class' => 'btn btn-lg w-100',
                    'style' => 'background-color: #00E3E2; border-color: #00E3E2; color: #000;'
                ]
            ) ?>
        </div>
        <div class="col">
            <?= Html::a(
                '<i class="fas fa-upload"></i> CARGA MASIVA',
                '#',
                [
                    'class' => 'btn btn-lg w-100',
                    'style' => 'background-color: #041E3F; border-color: #041E3F; color: #fff;',
                    'title' => 'Funcionalidad en construcción',
                    'onclick' => 'alert("Funcionalidad en construcción"); return false;'
                ]
            ) ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="col-xl-12 col-md-12">
    <div class="ms-panel ms-panel-fh">
        <div class="ms-panel-header">
            <h1><?= Html::encode($this->title) ?></h1>
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
                            'label' => 'Agente / Asesor',
                            'value' => function($model) {
                                return $model->id;
                            },
                            'contentOptions' => ['class' => 'text-center p-2'],
                            'filterInputOptions' => [
                                'placeholder' => 'Buscar N° de Agente /Asesor',
                                'class' => 'form-control form-control-lg text-center',
                            ],
                            'headerOptions' => ['style' => 'color: white;'],
                        ],
                        [
                            'attribute' => 'agenteFuerzaUserNombres',
                            'label' => 'Nombre',
                            'value' => function($model) {
                                if ($model->userDatos) {
                                    return $model->userDatos->nombres . ' ' . $model->userDatos->apellidos;
                                }
                                return 'N/A';
                            },
                            'filterInputOptions' => [
                                'placeholder' => 'Buscar nombre',
                                'class' => 'form-control form-control-lg text-center',
                            ],
                            'contentOptions' => ['class' => 'text-center'],
                            'headerOptions' => ['style' => 'color: white !important;'],
                        ],
                        [
                            'label' => 'Cedula de identidad',
                            'value' => function($model) {
                                if ($model->userDatos) {
                                    return $model->userDatos->cedula;
                                }
                                return 'No disponible';
                            },
                            'attribute' => 'agenteFuerzaUserCedula',
                            'headerOptions' => ['style' => 'color: white;'],
                            'filterInputOptions' => [
                                'placeholder' => 'Buscar cedula de identidad',
                                'class' => 'form-control form-control-lg text-center',
                            ],
                            'contentOptions' => ['class' => 'text-center'],
                            'headerOptions' => ['style' => 'color: white !important;'],
                        ],
                        [
                            'label' => 'Correo Electrónico',
                            'value' => function($model) {
                                if ($model->userDatos) {
                                    return $model->userDatos->email;
                                }
                                return 'No disponible';
                            },
                            'attribute' => 'agenteFuerzaUserEmail',
                            'headerOptions' => ['style' => 'color: white;'],
                            'filterInputOptions' => [
                                'placeholder' => 'Buscar correo',
                                'class' => 'form-control form-control-lg text-center',
                            ],
                            'contentOptions' => ['class' => 'text-center'],
                            'headerOptions' => ['style' => 'color: white !important;'],
                        ],
                        [
                            'label' => 'Teléfono',
                            'value' => function($model) {
                                return $model->userDatos->telefono ?? 'No disponible';
                            },
                            'attribute' => 'agenteFuerzaUserTelefono',
                            'headerOptions' => ['style' => 'color: white;'],
                            'filterInputOptions' => [
                                'placeholder' => 'Buscar teléfono',
                                'class' => 'form-control form-control-lg text-center',
                            ],
                            'contentOptions' => ['class' => 'text-center'],
                            'headerOptions' => ['style' => 'color: white !important;'],
                        ],
                        [
                            'class' => ActionColumn::class,
                            'header' => 'ACCIONES',
                            'template' => '<div class="d-flex justify-content-center gap-3">{view}{update}{afiliados}</div>',
                            'headerOptions' => ['style' => 'color: white;'],
                            'contentOptions' => ['class' => 'text-center p-2'],
                            'headerOptions' => ['style' => 'color: white !important;'],
                            'buttons' => [
                                'view' => function ($url, $model, $key) {
                                    return Html::a(
                                        '<i class="fa fa-eye"></i>',
                                        ['agente-fuerza/view', 'id' => $model->id],
                                        [
                                            'title' => 'Ver Detalles',
                                            'class' => 'btn btn-action btn-view',
                                            'style' => 'box-shadow: 0 4px 8px rgba(23, 162, 184, 0.3); transition: all 0.3s ease; transform: translateY(0);'
                                        ]
                                    );
                                },
                                'update' => function ($url, $model, $key) use ($permisos) {
                                    if($permisos) {
                                        return Html::a(
                                            '<i class="fas fa-pencil-alt"></i>',
                                            ['agente-fuerza/update', 'id' => $model->id],
                                            [
                                                'title' => 'Editar',
                                                'class' => 'btn btn-action btn-edit',
                                                'style' => 'box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3); transition: all 0.3s ease; transform: translateY(0);'
                                            ]
                                        );
                                    }
                                    return '';
                                },
                                'afiliados' => function ($url, $model, $key) {
                                    return Html::a(
                                        '<i class="fas fa-users"></i>',
                                        ['user-datos/index-by-afiliado', 'asesor_id' => $model->id],
                                        [
                                            'title' => 'Afiliados',
                                            'class' => 'btn btn-action btn-afiliados',
                                            'style' => 'box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3); transition: all 0.3s ease; transform: translateY(0);'
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

<style>
    /* Force all column headers to be white */
.table thead th {
    color: white !important;
    background-color: #007bff !important; /* Optional: add a background color to make white text stand out */
}

/* Specific targeting for gridview headers */
.grid-view thead th,
.table-striped thead th {
    color: white !important;
}

/* Ensure the white color is applied to all header cells */
th {
    color: white !important;
}
.btn-action {
    width: 45px;
    height: 45px;
    border-radius: 10px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    margin: 0 5px;
    transition: all 0.3s ease;
    position: relative;
}

.btn-action:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.25) !important;
}

.btn-action:active {
    transform: translateY(-1px) scale(1.02);
}

.btn-view {
    background: linear-gradient(145deg, #17a2b8, #138496);
    color: white !important;
}

.btn-edit {
    background: linear-gradient(145deg, #28a745, #20c997);
    color: white !important;
}

.btn-afiliados {
    background: linear-gradient(145deg, #ffc107, #fd7e14);
    color: white !important;
}

.btn-action i {
    margin: 0;
    font-size: 1.4rem;
    font-weight: bold;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.btn-action:hover i {
    transform: scale(1.1);
    text-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);
}

/* Add a subtle glow effect on hover */
.btn-view:hover {
    box-shadow: 0 8px 16px rgba(23, 162, 184, 0.4) !important;
}

.btn-edit:hover {
    box-shadow: 0 8px 16px rgba(40, 167, 69, 0.4) !important;
}

.btn-afiliados:hover {
    box-shadow: 0 8px 16px rgba(255, 193, 7, 0.4) !important;
}
</style>