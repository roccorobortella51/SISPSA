<?php

use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\select2\Select2;
use app\models\AuthItem;
use app\components\UserHelper;

// Register Microsoft-style CSS for this view only
$this->registerCssFile('@web/css/microsoft-grid.css', [
    'depends' => [\yii\bootstrap\BootstrapAsset::class],
    'position' => \yii\web\View::POS_HEAD
]);

/** @var yii\web\View $this */
/** @var app\models\UserSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */


// --- BREADCRUMBS ---

$this->params['breadcrumbs'][] = ['label' => 'Usuarios', 'url' => ['index']];
// --- FIN  --- 


$this->title = 'Gestión de Usuarios'; // Este sigue siendo el título para la página y breadcrumbs

$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol == 'DIRECTOR-COMERCIALIZACIÓN');
?>

<div class=row style="margin:3px !important;">
    <input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />

    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header row">

                <span class="col-md-10">
                    <h1><?= $this->title = 'Gestión de Usuarios'; ?></h1>
                </span>


                <div class="col-md-2 text-end">
                    <?php
                    if ($permisos)
                        echo Html::a(
                            '<i class="fas fa-plus"></i>&nbsp;CREAR NUEVO USUARIO',
                            ['create'],
                            ['class' => 'btn btn-outline-primary btn-lg w-100']
                        ); ?>
                </div>
            </div>
            <div class="ms-panel-body">
                <div class="table-responsive">
                    <?= GridView::widget([
                        'id' => 'usuarios-grid',
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
                            // CONSECUTIVE COUNT COLUMN (NEW FIRST COLUMN)
                            [
                                'class' => 'yii\grid\SerialColumn',
                                'header' => '#',
                                'headerOptions' => [
                                    'style' => 'color: white!important; background-color: #2b5797; width: 50px;',
                                    'class' => 'text-center'
                                ],
                                'contentOptions' => [
                                    'class' => 'text-center',
                                    'style' => 'font-weight: 500; color: #333;'
                                ],
                            ],

                            [
                                'attribute' => 'nombrecompleto',
                                'label' => 'Nombre Completo',
                                'format' => 'ntext',
                                'headerOptions' => [
                                    'style' => 'color: white!important; background-color: #2b5797;',
                                    'class' => 'text-center'
                                ],
                                'options' => ['style' => 'width: 500px;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar por Nombre/Apellido',
                                    'class' => 'form-control text-center',
                                ],
                                'value' => function ($model) {
                                    return $model->userDatos ? Html::encode($model->userDatos->nombres . ' ' . $model->userDatos->apellidos) : 'N/A';
                                },
                            ],

                            [
                                'attribute' => 'email',
                                'format' => 'ntext',
                                'label' => 'Correo Electrónico',
                                'headerOptions' => [
                                    'style' => 'color: white!important; background-color: #2b5797;',
                                    'class' => 'text-center'
                                ],
                                'options' => ['style' => 'width: 500px;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar por email',
                                    'class' => 'form-control text-center',
                                ],
                            ],

                            [
                                'attribute' => 'cedula',
                                'label' => 'Cédula',
                                'value' => function ($model) {
                                    return $model->userDatos ?
                                        ($model->userDatos->tipo_cedula . '-' . $model->userDatos->cedula) :
                                        'No registrado';
                                },
                                'format' => 'ntext',
                                'headerOptions' => [
                                    'style' => 'color: white!important; background-color: #2b5797;',
                                    'class' => 'text-center'
                                ],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar por cédula',
                                    'class' => 'form-control text-center',
                                ],
                            ],

                            [
                                'attribute' => 'telefono',  // Changed from 'userDatos.telefono'
                                'format' => 'ntext',
                                'label' => 'Teléfono',
                                'headerOptions' => [
                                    'style' => 'color: white!important; background-color: #2b5797;',
                                    'class' => 'text-center'
                                ],
                                'value' => function ($model) {
                                    return $model->userDatos->telefono ?? 'No registrado';
                                },
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar por teléfono',
                                    'class' => 'form-control text-center',
                                ],
                            ],

                            [
                                'label' => 'Rol del Usuario',
                                'headerOptions' => [
                                    'style' => 'color: white!important; background-color: #2b5797;',
                                    'class' => 'text-center'
                                ],
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $role = $model->userDatos->role ?? 'No asignado';
                                    $badgeColor = '#0078d4'; // Microsoft blue default

                                    // Different colors for different roles (Microsoft palette)
                                    switch ($role) {
                                        case 'superadmin':
                                        case 'admin':
                                            $badgeColor = '#107c41'; // Microsoft green
                                            break;
                                        case 'DIRECTOR-COMERCIALIZACIÓN':
                                            $badgeColor = '#d83b01'; // Microsoft orange
                                            break;
                                        case 'Agente':
                                            $badgeColor = '#0078d4'; // Microsoft blue
                                            break;
                                        case 'Asesor':
                                            $badgeColor = '#5c2d91'; // Microsoft purple
                                            break;
                                        default:
                                            $badgeColor = '#666666'; // Gray
                                    }

                                    return '<span style="background-color: ' . $badgeColor . '; color: white; padding: 4px 12px; border-radius: 16px; font-size: 12px; font-weight: 500; display: inline-block;">' . Html::encode($role) . '</span>';
                                },
                                'contentOptions' => ['class' => 'text-center'],
                                'filter' => Select2::widget([
                                    'model' => $searchModel,
                                    'attribute' => 'roleName',
                                    'data' => UserHelper::getRolesAllRoles(),
                                    'options' => ['placeholder' => 'Filtrar por rol...'],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                    ],
                                ]),
                            ],

                            [
                                'attribute' => 'status',
                                'label' => 'Estado',
                                'format' => 'raw',
                                'headerOptions' => [
                                    'style' => 'color: white!important; background-color: #2b5797;',
                                    'class' => 'text-center'
                                ],
                                'value' => function ($model) {
                                    // Your database uses 1 for active, 0 for inactive
                                    if ($model->status == 1) {
                                        return '<span class="status-badge status-active">Activo</span>';
                                    } else {
                                        return '<span class="status-badge status-inactive">Inactivo</span>';
                                    }
                                },
                                'filter' => [
                                    1 => 'Activo',
                                    0 => 'Inactivo',
                                ],
                                'contentOptions' => ['class' => 'text-center'],
                            ],

                            [
                                'attribute' => 'created_at',
                                'label' => 'Fecha Creación',
                                'format' => ['date', 'php:d/m/Y H:i'],
                                'headerOptions' => [
                                    'style' => 'color: white!important; background-color: #2b5797;',
                                    'class' => 'text-center'
                                ],
                                'contentOptions' => ['class' => 'text-center', 'style' => 'font-size: 13px;'],
                                'filter' => false,
                            ],

                            // ACTION COLUMN WITH IMPROVED MICROSOFT-STYLE BUTTONS
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => 'ACCIONES',
                                'headerOptions' => [
                                    'style' => 'color: white!important; background-color: #2b5797;',
                                    'class' => 'text-center'
                                ],
                                'template' => '<div class="action-button-container">{view}{update}</div>',
                                'options' => ['style' => 'width: 120px; min-width: 120px;'],
                                'contentOptions' => [
                                    'style' => 'text-align: center; padding: 8px 4px !important;',
                                    'class' => 'action-column'
                                ],
                                'buttons' => [
                                    'view' => function ($url, $model, $key) use ($rol) {
                                        // Base button classes for consistent styling
                                        $baseClass = 'microsoft-action-btn view-btn';

                                        if ($rol == "DIRECTOR-COMERCIALIZACIÓN" || $rol == "Agente") {
                                            $asesor_id = $model->userDatos->asesor->id ?? '0';
                                            if ($asesor_id > 0) {
                                                return Html::a(
                                                    '<i class="fa fa-eye"></i>',
                                                    Url::to(['/agente-fuerza/view', 'id' => $asesor_id]),
                                                    [
                                                        'title' => 'Ver detalles del usuario',
                                                        'class' => $baseClass,
                                                        'data-toggle' => 'tooltip',
                                                        'data-placement' => 'top',
                                                    ]
                                                );
                                            }
                                            return '';
                                        } else {
                                            return Html::a(
                                                '<i class="fa fa-eye"></i>',
                                                Url::to(['view', 'id' => $model->id]),
                                                [
                                                    'title' => 'Ver detalles del usuario',
                                                    'class' => $baseClass,
                                                    'data-toggle' => 'tooltip',
                                                    'data-placement' => 'top',
                                                ]
                                            );
                                        }
                                    },
                                    'update' => function ($url, $model, $key) use ($rol) {
                                        // Base button classes for consistent styling
                                        $baseClass = 'microsoft-action-btn edit-btn';

                                        if ($rol == "DIRECTOR-COMERCIALIZACIÓN" || $rol == "Agente") {
                                            $asesor_id = $model->userDatos->asesor->id ?? '0';
                                            if ($asesor_id > 0) {
                                                return Html::a(
                                                    '<i class="fas fa-pencil-alt"></i>',
                                                    Url::to(['/agente-fuerza/update', 'id' => $asesor_id]),
                                                    [
                                                        'title' => 'Editar usuario',
                                                        'class' => $baseClass,
                                                        'data-toggle' => 'tooltip',
                                                        'data-placement' => 'top',
                                                    ]
                                                );
                                            }
                                            return '';
                                        } else {
                                            return Html::a(
                                                '<i class="fas fa-pencil-alt"></i>',
                                                Url::to(['update', 'id' => $model->id]),
                                                [
                                                    'title' => 'Editar usuario',
                                                    'class' => $baseClass,
                                                    'data-toggle' => 'tooltip',
                                                    'data-placement' => 'top',
                                                ]
                                            );
                                        }
                                    },
                                ],
                            ],
                        ],
                        // Fin de columns
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</div>