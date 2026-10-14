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
    'depends'  => [\yii\bootstrap\BootstrapAsset::class],
    'position' => \yii\web\View::POS_HEAD
]);

/** @var yii\web\View $this */
/** @var app\models\UserSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

// --- BREADCRUMBS ---
$this->params['breadcrumbs'][] = ['label' => 'Usuarios', 'url' => ['index']];
// --- FIN ---

$this->title = 'Gestión de Usuarios';

$rol      = UserHelper::getMyRol();
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
                        'id'               => 'usuarios-grid',
                        'dataProvider'     => $dataProvider,
                        'filterModel'      => $searchModel,
                        'layout'           => "{items}{pager}",
                        'resizableColumns' => false,
                        'bordered'         => false,
                        'responsiveWrap'   => false,
                        'persistResize'    => false,
                        'tableOptions' => [
                            'class' => 'table table-striped table-bordered table-hover '
                        ],
                        'options' => [
                            'class' => 'grid-view-container table-responsive',
                        ],
                        'columns' => [
                            // ============================================================
                            // CONSECUTIVE COUNT COLUMN
                            // ============================================================
                            [
                                'class'          => 'yii\grid\SerialColumn',
                                'header'         => '#',
                                'headerOptions'  => [
                                    'style' => 'color: white!important; background-color: #2b5797; width: 50px;',
                                    'class' => 'text-center'
                                ],
                                'contentOptions' => [
                                    'class' => 'text-center',
                                    'style' => 'font-weight: 500; color: #333;'
                                ],
                            ],

                            // ============================================================
                            // NOMBRE COMPLETO (uses aliased ud_* with fallback)
                            // ============================================================
                            [
                                'attribute' => 'nombrecompleto',
                                'label'     => 'Nombre Completo',
                                'format'    => 'raw',
                                'headerOptions' => [
                                    'style' => 'color: white!important; background-color: #2b5797;',
                                    'class' => 'text-center'
                                ],
                                'options' => ['style' => 'width: 500px;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar por Nombre/Apellido',
                                    'class'       => 'form-control text-center',
                                ],
                                'value' => function ($model) {
                                    // 1. Prefer the aliased columns (works for orphans)
                                    $nombres   = $model->ud_nombres   ?? null;
                                    $apellidos = $model->ud_apellidos ?? null;

                                    // 2. Fallback to the relational accessor
                                    if ($nombres === null && $model->userDatos) {
                                        $nombres   = $model->userDatos->nombres;
                                        $apellidos = $model->userDatos->apellidos;
                                    }

                                    $full = trim((string)$nombres . ' ' . (string)$apellidos);

                                    if ($full === '') {
                                        return '<span class="text-muted">'
                                            . '<i class="fas fa-exclamation-triangle text-warning mr-1"></i>'
                                            . 'Sin datos personales</span>';
                                    }

                                    return Html::encode($full);
                                },
                            ],

                            // ============================================================
                            // CORREO ELECTRÓNICO
                            // ============================================================
                            [
                                'attribute' => 'email',
                                'format'    => 'ntext',
                                'label'     => 'Correo Electrónico',
                                'headerOptions' => [
                                    'style' => 'color: white!important; background-color: #2b5797;',
                                    'class' => 'text-center'
                                ],
                                'options' => ['style' => 'width: 500px;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar por email',
                                    'class'       => 'form-control text-center',
                                ],
                            ],

                            // ============================================================
                            // CÉDULA (uses aliased ud_* with fallback)
                            // ============================================================
                            [
                                'attribute' => 'cedula',
                                'label'     => 'Cédula',
                                'format'    => 'raw',
                                'headerOptions' => [
                                    'style' => 'color: white!important; background-color: #2b5797;',
                                    'class' => 'text-center'
                                ],
                                'value' => function ($model) {
                                    // 1. Aliased columns
                                    $tipo   = $model->ud_tipo_cedula ?? null;
                                    $numero = $model->ud_cedula        ?? null;

                                    // 2. Fallback
                                    if ($numero === null && $model->userDatos) {
                                        $tipo   = $model->userDatos->tipo_cedula;
                                        $numero = $model->userDatos->cedula;
                                    }

                                    if (!$numero) {
                                        return '<span class="text-muted">No registrado</span>';
                                    }

                                    return Html::encode($tipo . '-' . $numero);
                                },
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar por cédula',
                                    'class'       => 'form-control text-center',
                                ],
                            ],

                            // ============================================================
                            // TELÉFONO (uses aliased ud_* with fallback)
                            // ============================================================
                            [
                                'attribute' => 'telefono',
                                'format'    => 'ntext',
                                'label'     => 'Teléfono',
                                'headerOptions' => [
                                    'style' => 'color: white!important; background-color: #2b5797;',
                                    'class' => 'text-center'
                                ],
                                'value' => function ($model) {
                                    $telefono = $model->ud_telefono
                                        ?? ($model->userDatos->telefono ?? 'No registrado');

                                    return $telefono ?: 'No registrado';
                                },
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar por teléfono',
                                    'class'       => 'form-control text-center',
                                ],
                            ],

                            [
                                'label'  => 'Rol del Usuario',
                                'format' => 'raw',
                                'headerOptions' => [
                                    'style' => 'color: white!important; background-color: #2b5797;',
                                    'class' => 'text-center'
                                ],
                                'value' => function ($model) {
                                    // ============================================================
                                    // PRIORITY (v3 — RBAC-first, deterministic):
                                    //   1. RBAC assignment, resolved via a priority table so
                                    //      multi-role users get their MOST authoritative role
                                    //   2. user_datos.role (fallback for orphans / edge cases)
                                    //   3. Neutral "Sin rol" badge
                                    //
                                    // This matches UserHelper::getMyRol() exactly, so what the
                                    // grid shows is what the app enforces.
                                    // ============================================================
                                    $role = null;

                                    // ── 1. RBAC assignment (highest weight wins) ──
                                    $auth     = Yii::$app->authManager;
                                    $assigned = $auth->getRolesByUser($model->id);

                                    if (!empty($assigned)) {
                                        $priority = [
                                            'afiliado'                  => 1,
                                            'Asesor'                    => 2,
                                            'Agente'                    => 3,
                                            'COORDINADOR-CLINICA'       => 4,
                                            'Administrador-clinica'     => 4,
                                            'GERENTE-CLINICA'           => 4,
                                            'DIRECTOR-COMERCIALIZACIÓN' => 5,
                                            'admin'                     => 6,
                                            'superadmin'                => 7,
                                        ];

                                        $best       = null;
                                        $bestWeight = -1;

                                        foreach ($assigned as $r) {
                                            $w = $priority[$r->name] ?? 0;
                                            if ($w > $bestWeight) {
                                                $bestWeight = $w;
                                                $best       = $r->name;
                                            }
                                        }

                                        $role = $best;
                                    }

                                    // ── 2. Fallback to user_datos.role ──
                                    if (empty($role)) {
                                        $role = $model->ud_role
                                            ?? ($model->userDatos->role ?? null);
                                    }

                                    // ── 3. No role at all ──
                                    if (empty($role)) {
                                        return '<span style="background-color:#8a8886;color:white;'
                                            . 'padding:4px 12px;border-radius:16px;font-size:12px;'
                                            . 'font-weight:500;display:inline-block;" '
                                            . 'title="Este usuario no tiene rol asignado.">'
                                            . '<i class="fas fa-question-circle mr-1"></i>Sin rol</span>';
                                    }

                                    // ── 4. Color map ──
                                    $badgeColor = '#666666';
                                    switch ($role) {
                                        case 'superadmin':
                                        case 'admin':
                                            $badgeColor = '#107c41';
                                            break;
                                        case 'DIRECTOR-COMERCIALIZACIÓN':
                                            $badgeColor = '#d83b01';
                                            break;
                                        case 'Agente':
                                            $badgeColor = '#0078d4';
                                            break;
                                        case 'Asesor':
                                            $badgeColor = '#5c2d91';
                                            break;
                                        case 'COORDINADOR-CLINICA':
                                            $badgeColor = '#8764b8';
                                            break;
                                        case 'Administrador-clinica':
                                        case 'GERENTE-CLINICA':
                                            $badgeColor = '#008272';
                                            break;
                                        case 'afiliado':
                                            $badgeColor = '#0078d4';
                                            break;
                                        default:
                                            $badgeColor = '#666666';
                                    }

                                    return '<span style="background-color: ' . $badgeColor . '; color: white; '
                                        . 'padding: 4px 12px; border-radius: 16px; font-size: 12px; '
                                        . 'font-weight: 500; display: inline-block;">'
                                        . Html::encode($role) . '</span>';
                                },
                                'contentOptions' => ['class' => 'text-center'],
                                'filter' => Select2::widget([
                                    'model'     => $searchModel,
                                    'attribute' => 'roleName',
                                    'data'      => UserHelper::getRolesAllRoles(),
                                    'options'   => ['placeholder' => 'Filtrar por rol...'],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                    ],
                                ]),
                            ],

                            // ============================================================
                            // ESTADO
                            // ============================================================
                            [
                                'attribute' => 'status',
                                'label'     => 'Estado',
                                'format'    => 'raw',
                                'headerOptions' => [
                                    'style' => 'color: white!important; background-color: #2b5797;',
                                    'class' => 'text-center'
                                ],
                                'value' => function ($model) {
                                    // The model's afterFind() converts 10 → 1 and 9/0 → 0,
                                    // so $model->status is already 1 or 0 here.
                                    if ($model->status == 1) {
                                        return '<span class="status-badge status-active">Activo</span>';
                                    }

                                    return '<span class="status-badge status-inactive">Inactivo</span>';
                                },
                                'filter' => [
                                    1 => 'Activo',
                                    0 => 'Inactivo',
                                ],
                                'contentOptions' => ['class' => 'text-center'],
                            ],

                            // ============================================================
                            // FECHA DE CREACIÓN
                            // ============================================================
                            [
                                'attribute' => 'created_at',
                                'label'     => 'Fecha Creación',
                                'format'    => ['date', 'php:d/m/Y H:i'],
                                'headerOptions' => [
                                    'style' => 'color: white!important; background-color: #2b5797;',
                                    'class' => 'text-center'
                                ],
                                'contentOptions' => [
                                    'class' => 'text-center',
                                    'style' => 'font-size: 13px;'
                                ],
                                'filter' => false,
                            ],

                            // ============================================================
                            // ACTION COLUMN
                            // ============================================================
                            [
                                'class'  => 'yii\grid\ActionColumn',
                                'header' => 'ACCIONES',
                                'headerOptions' => [
                                    'style' => 'color: white!important; background-color: #2b5797;',
                                    'class' => 'text-center'
                                ],
                                'template' => '<div class="action-button-container">{view}{update}</div>',
                                'options'  => ['style' => 'width: 120px; min-width: 120px;'],
                                'contentOptions' => [
                                    'style' => 'text-align: center; padding: 8px 4px !important;',
                                    'class' => 'action-column'
                                ],
                                'buttons' => [
                                    'view' => function ($url, $model, $key) use ($rol) {
                                        $baseClass = 'microsoft-action-btn view-btn';

                                        if ($rol == "DIRECTOR-COMERCIALIZACIÓN" || $rol == "Agente") {
                                            $asesor_id = $model->userDatos->asesor->id ?? '0';
                                            if ($asesor_id > 0) {
                                                return Html::a(
                                                    '<i class="fa fa-eye"></i>',
                                                    Url::to(['/agente-fuerza/view', 'id' => $asesor_id]),
                                                    [
                                                        'title'          => 'Ver detalles del usuario',
                                                        'class'          => $baseClass,
                                                        'data-toggle'    => 'tooltip',
                                                        'data-placement' => 'top',
                                                    ]
                                                );
                                            }
                                            return '';
                                        }

                                        return Html::a(
                                            '<i class="fa fa-eye"></i>',
                                            Url::to(['view', 'id' => $model->id]),
                                            [
                                                'title'          => 'Ver detalles del usuario',
                                                'class'          => $baseClass,
                                                'data-toggle'    => 'tooltip',
                                                'data-placement' => 'top',
                                            ]
                                        );
                                    },
                                    'update' => function ($url, $model, $key) use ($rol) {
                                        $baseClass = 'microsoft-action-btn edit-btn';

                                        if ($rol == "DIRECTOR-COMERCIALIZACIÓN" || $rol == "Agente") {
                                            $asesor_id = $model->userDatos->asesor->id ?? '0';
                                            if ($asesor_id > 0) {
                                                return Html::a(
                                                    '<i class="fas fa-pencil-alt"></i>',
                                                    Url::to(['/agente-fuerza/update', 'id' => $asesor_id]),
                                                    [
                                                        'title'          => 'Editar usuario',
                                                        'class'          => $baseClass,
                                                        'data-toggle'    => 'tooltip',
                                                        'data-placement' => 'top',
                                                    ]
                                                );
                                            }
                                            return '';
                                        }

                                        return Html::a(
                                            '<i class="fas fa-pencil-alt"></i>',
                                            Url::to(['update', 'id' => $model->id]),
                                            [
                                                'title'          => 'Editar usuario',
                                                'class'          => $baseClass,
                                                'data-toggle'    => 'tooltip',
                                                'data-placement' => 'top',
                                            ]
                                        );
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