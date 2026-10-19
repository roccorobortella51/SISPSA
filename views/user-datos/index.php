<?php

use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\select2\Select2;
use app\components\UserHelper;
use app\models\RmClinica;
use app\models\Contratos;
use app\components\ContractHelper;
use yii\web\View;
use yii\widgets\Pjax;

// Helper function for contract status classes
function getContractStatusClass($status)
{
    $status = strtolower($status);
    $classes = [
        'registrado' => 'badge badge-primary',
        'activo' => 'badge badge-success',
        'anulado' => 'badge badge-danger',
        'vencido' => 'badge badge-warning',
        'pendiente' => 'badge badge-info',
        'suspendido' => 'badge badge-secondary',
    ];

    return $classes[$status] ?? 'badge badge-light';
}

/** @var yii\web\View $this */
/** @var app\models\UserDatosSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string|null $clinica_id */

// --- Detección y Carga de Clínica (para contexto) ---
$clinica = null;
$clinica_id_param = Yii::$app->request->get('clinica_id');

if (!empty($clinica_id_param)) {
    $clinica = RmClinica::findOne((int)$clinica_id_param);
    if (!$clinica) {
        $clinica = (object)['id' => (int)$clinica_id_param, 'nombre' => 'Clínica Desconocida'];
    }
}

$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol == 'DIRECTOR-COMERCIALIZACIÓN' || $rol == 'Asesor' || $rol == 'Agente' || $rol == "ADMISIÓN" || $rol == "COORDINADOR-CLINICA" || $rol == "OPERACIONES-BOSQUE");

// --- BREADCRUMBS CONDICIONALES ---
if ($permisos == true) {
    $this->params['breadcrumbs'][] = ['label' => 'CLÍNICAS', 'url' => ['/rm-clinica/index']];
}
if ($clinica && $clinica->id !== null) {
    $this->params['breadcrumbs'][] = ['label' => Html::encode($clinica->nombre), 'url' => ['/rm-clinica/view', 'id' => $clinica->id]];
    $this->params['breadcrumbs'][] = 'AFILIADOS';
    $this->title = 'Gestión de Afiliados de ' . Html::encode($clinica->nombre);
} else {
    $this->params['breadcrumbs'][] = 'AFILIADOS';
    $this->title = 'Gestión de Afiliados';
}

// Define admin roles for clinic search filter
$isAdmin = ($rol == 'superadmin' || $rol == 'DIRECTOR-COMERCIALIZACIÓN');
$rolesSinFiltroClinica = ['GERENTE-CLINICA', 'Administrador-clinica', 'CONTROL DE CITAS', 'ADMISIÓN', 'ATENCIÓN', 'COORDINADOR-CLINICA', 'OPERACIONES-BOSQUE'];
$mostrarFiltroClinica = $isAdmin && !in_array($rol, $rolesSinFiltroClinica);

// Define roles that can access Atención Médica
$rolesAtencionMedica = ['superadmin', 'DIRECTOR-COMERCIALIZACIÓN', 'COORDINADOR-CLINICA', 'CONTROL DE CITAS', 'GERENTE-CLINICA', 'ADMISIÓN', 'ATENCIÓN', 'OPERACIONES-BOSQUE', 'GERENTE-OPERACIONES'];

// --- GET SUMMARY STATISTICS ---
$summaryStats = [];
if (isset($searchModel) && method_exists($searchModel, 'getSummaryStatistics')) {
    try {
        $params = Yii::$app->request->queryParams;
        $summaryStats = $searchModel->getSummaryStatistics($params);
    } catch (\Exception $e) {
        Yii::error("Error getting summary statistics: " . $e->getMessage(), 'affiliates-index');
        $summaryStats = [
            'total_afiliados' => 0,
            'total_contratos_activos' => 0,
            'total_contratos_suspendidos' => 0,
            'total_contratos_registrados' => 0,
            'total_contratos_anulados' => 0,
            'total_contratos_vencidos' => 0,
            'critical_delinquency' => [
                'count' => 0,
                'total_vencidas_cuotas' => 0,
                'average_vencidas' => 0,
            ],
        ];
    }
}

// --- Get clinic name for display ---
$displayClinicName = '';
try {
    if ($clinica && $clinica->id !== null) {
        $displayClinicName = Html::encode($clinica->nombre);
    } else {
        $clinicName = UserHelper::getMyClinicaName();
        if (!empty($clinicName)) {
            $displayClinicName = Html::encode($clinicName);
        } else {
            $displayClinicName = 'Todos los afiliados';
        }
    }
} catch (\Exception $e) {
    Yii::error("Error getting clinic name: " . $e->getMessage(), 'affiliates-index');
    $displayClinicName = 'Todos los afiliados';
}

// --- Build filter URLs for each card ---
$baseUrl = ['index'];
if ($clinica && $clinica->id !== null) {
    $baseUrl['clinica_id'] = $clinica->id;
}

$urlTotalAfiliados = Url::to(array_merge($baseUrl, []));

$urlActivos = Url::to(array_merge($baseUrl, [
    'UserDatosSearch' => ['contrato_estatus' => 'Activo']
]));

$urlSuspendidos = Url::to(array_merge($baseUrl, [
    'UserDatosSearch' => ['contrato_estatus' => 'Suspendido']
]));

$urlRegistrados = Url::to(array_merge($baseUrl, [
    'UserDatosSearch' => ['contrato_estatus' => 'Registrado']
]));

$urlAnulados = Url::to(array_merge($baseUrl, [
    'UserDatosSearch' => ['contrato_estatus' => 'Anulado']
]));
$urlVencidos = Url::to(array_merge($baseUrl, [
    'UserDatosSearch' => ['contrato_estatus' => 'Vencido']
]));

$urlCriticos = Url::to(array_merge($baseUrl, [
    'UserDatosSearch' => ['critical_filter' => 'true']
]));

// Get current filter for display
$currentFilter = Yii::$app->request->get('UserDatosSearch')['contrato_estatus'] ?? null;
$isCriticalFilter = Yii::$app->request->get('UserDatosSearch')['critical_filter'] ?? null;

$hasActivos = ($summaryStats['total_contratos_activos'] ?? 0) > 0;
$hasSuspendidos = ($summaryStats['total_contratos_suspendidos'] ?? 0) > 0;
$hasRegistrados = ($summaryStats['total_contratos_registrados'] ?? 0) > 0;
$hasAnulados = ($summaryStats['total_contratos_anulados'] ?? 0) > 0;
$hasVencidos = ($summaryStats['total_contratos_vencidos'] ?? 0) > 0;
$hasCriticos = ($summaryStats['critical_delinquency']['count'] ?? 0) > 0;

?>

<div class="main-container">
    <input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />

    <!-- HEADER SECTION -->
    <div class="header-section">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="header-buttons-group">
            <?php if ($permisos) : ?>
                <?= Html::a(
                    '<i class="fas fa-file-excel mr-2"></i> CARGAR MASIVOS DE AFILIADOS',
                    ['corporativo/index'],
                    ['class' => 'btn-base btn-blue']
                ) ?>
                <?= Html::a(
                    '<i class="fas fa-plus mr-2"></i> CREAR NUEVO AFILIADO DEL SÍSTEMA',
                    ['create'],
                    ['class' => 'btn-base btn-blue']
                ) ?>
            <?php endif; ?>
            <?php if ($clinica && $clinica->id !== null) : ?>
                <?= Html::a(
                    '<i class="fas fa-undo mr-2"></i> Volver a Clínica',
                    ['/rm-clinica/view', 'id' => $clinica->id],
                    [
                        'class' => 'btn-base btn-gray',
                        'title' => 'Volver a los detalles de la clínica',
                    ]
                ) ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- KPI SUMMARY CARDS — Microsoft Fluent (One Word + Hero Number) -->
    <!-- ============================================================ -->
    <div class="kpi-cards-row">

        <!-- Card 1: Total -->
        <a href="<?= $urlTotalAfiliados ?>" class="kpi-card-link" data-pjax="#affiliates-pjax"
            data-toggle="tooltip"
            data-placement="top"
            data-html="true"
            title="<strong>Total de Afiliados</strong><br>Todos los afiliados registrados en el sistema.<br><small class='text-muted'>Click para ver todos</small>">
            <div class="kpi-card primary">
                <span class="kpi-accent"></span>
                <div class="kpi-card-header">
                    <span class="kpi-icon"><i class="fas fa-users"></i></span>
                    <span class="kpi-title">Total</span>
                    <span class="kpi-arrow"><i class="fas fa-chevron-right"></i></span>
                </div>
                <div class="kpi-card-body">
                    <span class="kpi-number"><?= number_format($summaryStats['total_afiliados'] ?? 0) ?></span>
                    <span class="kpi-label">
                        <i class="fas fa-building mr-1"></i><?= $displayClinicName ?>
                    </span>
                </div>
            </div>
        </a>

        <!-- Card 2: Activos -->
        <?php if ($hasActivos): ?>
            <a href="<?= $urlActivos ?>" class="kpi-card-link" data-pjax="#affiliates-pjax"
                data-toggle="tooltip"
                data-placement="top"
                data-html="true"
                title="<strong>Afiliados Activos</strong><br>Afiliados con al menos un contrato en vigencia.<br><small class='text-muted'>Click para filtrar</small>">
                <div class="kpi-card success">
                    <span class="kpi-accent"></span>
                    <div class="kpi-card-header">
                        <span class="kpi-icon"><i class="fas fa-check-circle"></i></span>
                        <span class="kpi-title">Activos</span>
                        <span class="kpi-arrow"><i class="fas fa-chevron-right"></i></span>
                    </div>
                    <div class="kpi-card-body">
                        <span class="kpi-number"><?= number_format($summaryStats['total_contratos_activos'] ?? 0) ?></span>
                        <span class="kpi-label">
                            <i class="fas fa-calendar-check mr-1"></i>En vigencia
                        </span>
                    </div>
                </div>
            </a>
        <?php endif; ?>

        <!-- Card 3: Suspendidos -->
        <?php if ($hasSuspendidos): ?>
            <a href="<?= $urlSuspendidos ?>" class="kpi-card-link" data-pjax="#affiliates-pjax"
                data-toggle="tooltip"
                data-placement="top"
                data-html="true"
                title="<strong>Afiliados Suspendidos</strong><br>Afiliados con al menos un contrato suspendido por tener cuotas vencidas.<br><small class='text-muted'>Click para filtrar</small>">
                <div class="kpi-card danger">
                    <span class="kpi-accent"></span>
                    <div class="kpi-card-header">
                        <span class="kpi-icon"><i class="fas fa-pause-circle"></i></span>
                        <span class="kpi-title">Suspendidos</span>
                        <span class="kpi-arrow"><i class="fas fa-chevron-right"></i></span>
                    </div>
                    <div class="kpi-card-body">
                        <span class="kpi-number"><?= number_format($summaryStats['total_contratos_suspendidos'] ?? 0) ?></span>
                        <span class="kpi-label">
                            <i class="fas fa-clock mr-1"></i>Con cuotas vencidas
                        </span>
                    </div>
                </div>
            </a>
        <?php endif; ?>

        <!-- Card 4: Registrados -->
        <?php if ($hasRegistrados): ?>
            <a href="<?= $urlRegistrados ?>" class="kpi-card-link" data-pjax="#affiliates-pjax"
                data-toggle="tooltip"
                data-placement="top"
                data-html="true"
                title="<strong>Afiliados Registrados</strong><br>Afiliados con al menos un contrato registrado pendiente de activación.<br><small class='text-muted'>Click para filtrar</small>">
                <div class="kpi-card registrado">
                    <span class="kpi-accent"></span>
                    <div class="kpi-card-header">
                        <span class="kpi-icon"><i class="fas fa-clipboard-list"></i></span>
                        <span class="kpi-title">Registrados</span>
                        <span class="kpi-arrow"><i class="fas fa-chevron-right"></i></span>
                    </div>
                    <div class="kpi-card-body">
                        <span class="kpi-number"><?= number_format($summaryStats['total_contratos_registrados'] ?? 0) ?></span>
                        <span class="kpi-label">
                            <i class="fas fa-clock mr-1"></i>Pendientes
                        </span>
                    </div>
                </div>
            </a>
        <?php endif; ?>

        <!-- Card 5: Anulados -->
        <?php if ($hasAnulados): ?>
            <a href="<?= $urlAnulados ?>" class="kpi-card-link" data-pjax="#affiliates-pjax"
                data-toggle="tooltip"
                data-placement="top"
                data-html="true"
                title="<strong>Afiliados Anulados</strong><br>Afiliados sin ningún contrato vigente (todos sus contratos están anulados).<br><small class='text-muted'>Click para filtrar</small>">
                <div class="kpi-card anulado">
                    <span class="kpi-accent"></span>
                    <div class="kpi-card-header">
                        <span class="kpi-icon"><i class="fas fa-ban"></i></span>
                        <span class="kpi-title">Anulados</span>
                        <span class="kpi-arrow"><i class="fas fa-chevron-right"></i></span>
                    </div>
                    <div class="kpi-card-body">
                        <span class="kpi-number"><?= number_format($summaryStats['total_contratos_anulados'] ?? 0) ?></span>
                        <span class="kpi-label">
                            <i class="fas fa-times-circle mr-1"></i>Sin contrato
                        </span>
                    </div>
                </div>
            </a>
        <?php endif; ?>

        <!-- Card 5.1: Vencidos -->
        <?php if ($hasVencidos): ?>
            <a href="<?= $urlVencidos ?>" class="kpi-card-link" data-pjax="#affiliates-pjax"
                data-toggle="tooltip"
                data-placement="top"
                data-html="true"
                title="<strong>Afiliados Vencidos</strong><br>Afiliados cuyos contratos han superado su fecha de vigencia sin renovación.<br><small class='text-muted'>Click para filtrar</small>">
                <div class="kpi-card vencido">
                    <span class="kpi-accent"></span>
                    <div class="kpi-card-header">
                        <span class="kpi-icon"><i class="fas fa-calendar-times"></i></span>
                        <span class="kpi-title">Vencidos</span>
                        <span class="kpi-arrow"><i class="fas fa-chevron-right"></i></span>
                    </div>
                    <div class="kpi-card-body">
                        <span class="kpi-number"><?= number_format($summaryStats['total_contratos_vencidos'] ?? 0) ?></span>
                        <span class="kpi-label">
                            <i class="fas fa-hourglass-end mr-1"></i>Fuera de vigencia
                        </span>
                    </div>
                </div>
            </a>
        <?php endif; ?>

        <!-- Card 6: Críticos -->
        <?php if ($hasCriticos): ?>
            <a href="<?= $urlCriticos ?>" class="kpi-card-link" data-pjax="#affiliates-pjax"
                data-toggle="tooltip"
                data-placement="top"
                data-html="true"
                title="<strong>Afiliados Críticos</strong><br>Afiliados con 3 o más cuotas vencidas. <span class='text-warning'>¡Requieren atención inmediata!</span><br><small class='text-muted'>Click para filtrar</small>">
                <div class="kpi-card critical">
                    <span class="kpi-accent"></span>
                    <div class="kpi-card-header">
                        <span class="kpi-icon"><i class="fas fa-exclamation-triangle"></i></span>
                        <span class="kpi-title">Críticos</span>
                        <span class="kpi-arrow"><i class="fas fa-chevron-right"></i></span>
                    </div>
                    <div class="kpi-card-body">
                        <span class="kpi-number">
                            <?= number_format($summaryStats['critical_delinquency']['count'] ?? 0) ?>
                            <?php if (($summaryStats['critical_delinquency']['count'] ?? 0) > 0): ?>
                                <small class="kpi-percent">
                                    <?= round(($summaryStats['critical_delinquency']['count'] / max($summaryStats['total_contratos_suspendidos'] ?? 1, 1)) * 100) ?>%
                                </small>
                            <?php endif; ?>
                        </span>
                        <span class="kpi-label">
                            <i class="fas fa-exclamation-circle mr-1"></i>3+ cuotas vencidas
                        </span>
                    </div>
                </div>
            </a>
        <?php endif; ?>
    </div>

    <!-- ============================================================ -->
    <!-- GRID VIEW - Main Affiliates Table                            -->
    <!-- ============================================================ -->
    <div class="ms-panel ms-panel-fh border-indigo">
        <div class="ms-panel-header">
            <h3 class="section-title">
                <i class="fas fa-users mr-3 text-indigo-600"></i> Listado de Afiliados
                <?php
                if ($isCriticalFilter === 'true'):
                    echo '<span class="badge badge-critical ml-2">Filtrado: Críticos (3+ cuotas vencidas)</span>';
                elseif ($currentFilter):
                    $filterLabels = [
                        'Activo' => '<span class="badge badge-success ml-2">Filtrado: Activos</span>',
                        'Suspendido' => '<span class="badge badge-secondary ml-2">Filtrado: Suspendidos</span>',
                        'Registrado' => '<span class="badge badge-primary ml-2">Filtrado: Registrados</span>',
                        'Anulado' => '<span class="badge badge-danger ml-2">Filtrado: Anulados</span>',
                        'Vencido' => '<span class="badge badge-warning ml-2">Filtrado: Vencidos</span>',
                        'Pendiente' => '<span class="badge badge-info ml-2">Filtrado: Pendientes</span>',
                    ];
                    echo $filterLabels[$currentFilter] ?? '';
                endif;
                ?>
            </h3>
        </div>
        <div class="ms-panel-body">

            <!-- ============================================================ -->
            <!-- EYE-CATCHING TOP SCROLLBAR — same Fluent chip as pagos/index -->
            <!-- ============================================================ -->
            <div class="top-scrollbar-shell">
                <span class="top-scrollbar-hint">
                    <i class="fas fa-arrows-alt-h"></i> Deslizar
                </span>

                <button type="button"
                    class="top-scrollbar-arrow top-scrollbar-arrow-left"
                    aria-label="Desplazar a la izquierda"
                    title="Desplazar a la izquierda">
                    <i class="fas fa-chevron-left"></i>
                </button>

                <div class="top-native-scrollbar">
                    <div class="top-native-dummy"></div>
                </div>

                <button type="button"
                    class="top-scrollbar-arrow top-scrollbar-arrow-right"
                    aria-label="Desplazar a la derecha"
                    title="Desplazar a la derecha">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <!-- ============================================================ -->
            <!-- PJAX CONTAINER — the only part that refreshes on live search  -->
            <!-- ============================================================ -->
            <?php Pjax::begin([
                'id'                 => 'affiliates-pjax',
                'timeout'            => 5000,
                'enablePushState'    => false,
                'enableReplaceState' => false,
                'linkSelector'       => 'a[data-pjax="#affiliates-pjax"]',
                'clientOptions'      => [
                    'cache' => false,
                    'scrollTo' => false,
                ],
            ]); ?>

            <div class="table-responsive table-responsive-scroll" id="grid-scroll-container">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'resizableColumns' => false,
                    'bordered' => false,
                    'responsiveWrap' => false,
                    'persistResize' => false,
                    'filterModel' => $searchModel,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'attribute' => 'created_at',
                            'hAlign' => 'center',
                            'vAlign' => 'middle',
                            'label' => 'Fecha Alta',
                            'value' => function ($model, $key, $index, $widget) {
                                return !empty($model->created_at) ? Yii::$app->formatter->asDate($model->created_at, 'd/M/Y') : '';
                            },
                            'width' => '10%',
                            'filterType' => \kartik\grid\GridView::FILTER_DATE_RANGE,
                            'format' => 'raw',
                            'filterInputOptions' => ['placeholder' => 'Seleccione un rango de fechas', 'class' => 'form-control'],
                            'filterWidgetOptions' => [
                                'presetDropdown' => true,
                                'pluginOptions' => [
                                    'locale' => [
                                        'format' => 'DD/MM/YYYY',
                                        'separator' => ' a ',
                                    ],
                                    'placeholder' => 'Fecha de creación',
                                ],
                                'pluginEvents' => [
                                    "apply.daterangepicker" => "function() { $('.grid-view').yiiGridView('applyFilter') }",
                                ]
                            ],
                        ],
                        [
                            'attribute' => 'user_datos_type_id',
                            'hAlign' => 'center',
                            'label' => 'Tipo Afiliado',
                            'value' => function ($model) {
                                return $model->userDatosType ? $model->userDatosType->nombre : null;
                            },
                            'filter' => Select2::widget([
                                'model' => $searchModel,
                                'attribute' => 'user_datos_type_id',
                                'data' => app\models\UserDatosType::getList(),
                                'options' => ['placeholder' => 'Filtrar'],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ]),
                            'contentOptions' => ['style' => 'width: 150px;'],
                        ],
                        [
                            'attribute' => 'clinica_id',
                            'label' => 'Clínica',
                            'value' => function ($model) {
                                return $model->clinica ? $model->clinica->nombre : 'No asignada';
                            },
                            'filter' => $mostrarFiltroClinica ? Select2::widget([
                                'model' => $searchModel,
                                'attribute' => 'clinica_id',
                                'data' => \yii\helpers\ArrayHelper::map(
                                    \app\models\RmClinica::find()->orderBy('nombre')->all(),
                                    'id',
                                    'nombre'
                                ),
                                'options' => ['placeholder' => 'Filtrar Clínica'],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ]) : false,
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'contentOptions' => ['class' => 'text-center'],
                        ],
                        [
                            'attribute' => 'afiliado_corporativo_id',
                            'label' => 'Corporativo',
                            'value' => function ($model) {
                                if ($model->user_datos_type_id == 2 && $model->corporativo) {
                                    $name = Html::encode($model->corporativo->nombre);

                                    $colorPalettes = [
                                        ['#dbeafe', '#93c5fd', '#3b82f6', '#1e40af', '#2563eb'],
                                        ['#d1fae5', '#6ee7b7', '#10b981', '#065f46', '#059669'],
                                        ['#ede9fe', '#c4b5fd', '#8b5cf6', '#5b21b6', '#7c3aed'],
                                        ['#fef3c7', '#fcd34d', '#f59e0b', '#92400e', '#d97706'],
                                        ['#fce7f3', '#f9a8d4', '#ec4899', '#9d174d', '#db2777'],
                                        ['#ccfbf1', '#5eead4', '#14b8a6', '#115e59', '#0d9488'],
                                        ['#fecaca', '#f87171', '#ef4444', '#991b1b', '#dc2626'],
                                        ['#e0e7ff', '#818cf8', '#4f46e5', '#312e81', '#4338ca'],
                                        ['#cffafe', '#67e8f9', '#06b6d4', '#164e63', '#0891b2'],
                                        ['#fce4ec', '#f48fb1', '#e91e63', '#880e4f', '#c2185b'],
                                        ['#e8f5e9', '#81c784', '#4caf50', '#1b5e20', '#388e3c'],
                                        ['#fff3e0', '#ffb74d', '#ff9800', '#e65100', '#f57c00'],
                                        ['#f3e5f5', '#ce93d8', '#9c27b0', '#4a148c', '#7b1fa2'],
                                        ['#e0f7fa', '#80deea', '#00bcd4', '#006064', '#00838f'],
                                        ['#fff8e1', '#ffd54f', '#ffc107', '#f57f17', '#f9a825'],
                                        ['#efebe9', '#a1887f', '#795548', '#3e2723', '#5d4037'],
                                    ];

                                    $hash = abs(crc32($name));
                                    $colorIndex = $hash % count($colorPalettes);
                                    $palette = $colorPalettes[$colorIndex];
                                    list($bg1, $bg2, $border, $text, $icon) = $palette;

                                    $style = sprintf(
                                        'background: linear-gradient(135deg, %s 0%%, %s 100%%); border-color: %s; color: %s;',
                                        $bg1,
                                        $bg2,
                                        $border,
                                        $text
                                    );
                                    $iconStyle = sprintf('color: %s;', $icon);

                                    return sprintf(
                                        '<span class="corporativo-badge" style="%s"><i class="fas fa-building mr-2" style="%s"></i> %s</span>',
                                        $style,
                                        $iconStyle,
                                        $name
                                    );
                                }
                                return '<span class="no-corporativo-badge"><i class="fas fa-user mr-2"></i> No aplica</span>';
                            },
                            'filter' => \kartik\select2\Select2::widget([
                                'model' => $searchModel,
                                'attribute' => 'afiliado_corporativo_id',
                                'data' => \yii\helpers\ArrayHelper::map(
                                    \app\models\Corporativo::find()
                                        ->where(['estatus' => 'Activo'])
                                        ->orderBy('nombre')
                                        ->all(),
                                    'id',
                                    'nombre'
                                ),
                                'options' => [
                                    'placeholder' => 'Filtrar corporativo',
                                    'class' => 'form-control'
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ]),
                            'contentOptions' => function ($model) {
                                if ($model->user_datos_type_id == 2 && $model->corporativo) {
                                    return ['class' => 'text-center', 'style' => 'vertical-align: middle; padding: 8px 5px;'];
                                }
                                return ['class' => 'text-center', 'style' => 'vertical-align: middle; padding: 8px 5px;'];
                            },
                            'headerOptions' => ['style' => 'color: white!important; width: 150px; min-width: 150px;'],
                            'format' => 'raw',
                        ],
                        [
                            'label' => 'Nombre Completo',
                            'attribute' => 'nombre_completo',
                            'value' => function ($model) {
                                return $model->nombres . ' ' . $model->apellidos;
                            },
                            'format' => 'ntext',
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'filterInputOptions' => [
                                'placeholder'  => 'Buscar por nombre o apellido…',
                                'class'        => 'form-control text-center',
                                'autocomplete' => 'off',
                            ],
                        ],
                        [
                            'label' => 'Cédula / Pasaporte',
                            'attribute' => 'cedula',
                            'value' => function ($model) {
                                $tipoCedula = $model->tipo_cedula ?? '';
                                $numeroCedula = $model->cedula ?? '';
                                $consecutivo = '';

                                if (isset($model->consecutivo_menor) && $model->consecutivo_menor !== null && $model->consecutivo_menor !== '') {
                                    $consecutivo = str_pad((int)$model->consecutivo_menor, 2, '0', STR_PAD_LEFT);
                                }

                                if ($tipoCedula === 'P') {
                                    return '<div class="cedula-grande-container cedula-passport">
                    <div class="cedula-grande-text">
                        <i class="fas fa-passport" style="font-size: 1.1rem; margin-right: 8px; color: #0078d4;"></i>
                        PASAPORTE: ' . $tipoCedula . '-' . $numeroCedula . '
                    </div>
                   
                </div>';
                                }

                                if ($tipoCedula === 'Menor Sin Cédula') {
                                    if (!empty($consecutivo)) {
                                        return '<div class="cedula-grande-container menor-sin-cedula"><div class="cedula-grande-text">SIN CÉDULA-' . $numeroCedula . '-<span class="consecutivo-grande">' . $consecutivo . '</span></div></div>';
                                    } else {
                                        return '<div class="cedula-grande-container menor-sin-cedula"><div class="cedula-grande-text">SIN CÉDULA-' . $numeroCedula . '</div></div>';
                                    }
                                }

                                if (!empty($numeroCedula) && !empty($tipoCedula) && $tipoCedula !== 'Menor Sin Cédula' && $tipoCedula !== 'P') {
                                    $esMenorEdad = false;
                                    $edad = null;
                                    if (!empty($model->fechanac)) {
                                        try {
                                            $fechaNac = new DateTime($model->fechanac);
                                            $hoy = new DateTime();
                                            $edad = $fechaNac->diff($hoy)->y;
                                            $esMenorEdad = ($edad < 18);
                                        } catch (\Exception $e) {
                                        }
                                    }
                                    return '<div class="cedula-grande-container cedula-normal"><div class="cedula-grande-text">' . $tipoCedula . '-' . $numeroCedula . '</div>' . ($esMenorEdad ? '<div class="edad-etiqueta">MENOR (' . $edad . ' años)</div>' : '') . '</div>';
                                }

                                if (!empty($tipoCedula) && empty($numeroCedula) && $tipoCedula !== 'Menor Sin Cédula' && $tipoCedula !== 'P') {
                                    return '<div class="cedula-grande-container cedula-pendiente"><div class="cedula-grande-text">' . $tipoCedula . '</div><div class="estado-etiqueta">PENDIENTE DE NÚMERO</div></div>';
                                }

                                return '<div class="cedula-grande-container sin-cedula"><div class="cedula-grande-text">SIN REGISTRO</div></div>';
                            },
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'options' => ['style' => 'width: 140px; min-width:140px;'],
                            'contentOptions' => function ($model) {
                                $tipo = $model->tipo_cedula ?? '';
                                if ($tipo === 'Menor Sin Cédula') {
                                    return ['class' => 'text-center bg-menor-sin-cedula'];
                                }
                                if ($tipo === 'P') {
                                    return ['class' => 'text-center bg-passport'];
                                }
                                if (!empty($model->cedula)) {
                                    return ['class' => 'text-center bg-cedula-normal'];
                                }
                                return ['class' => 'text-center'];
                            },
                            'filterInputOptions' => [
                                'placeholder' => 'Buscar cédula/pasaporte',
                                'class' => 'form-control text-center',
                            ],
                        ],
                        'telefono',
                        [
                            'attribute' => 'email',
                            'label' => 'Correo Electrónico',
                            'format' => 'email',
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'options' => ['style' => 'width: 200px;'],
                            'filterInputOptions' => [
                                'placeholder' => 'Buscar por correo',
                                'class' => 'form-control text-center',
                            ],
                        ],
                        [
                            'label' => 'Asesor',
                            'hAlign' => 'center',
                            'format' => 'ntext',
                            'value' => function ($model) {
                                if ($model->asesor && $model->asesor->userDatos) {
                                    $ud = $model->asesor->userDatos;
                                    return trim(($ud->nombres ?? '') . ' ' . ($ud->apellidos ?? '')) ?: null;
                                }
                                return null;
                            },
                            'headerOptions' => ['style' => 'color: white!important;'],
                        ],
                        [
                            'label' => 'Estatus Contrato',
                            'format' => 'raw',
                            'value' => function ($model) {
                                return ContractHelper::generateContractStatusBadge($model);
                            },
                            'attribute' => 'contrato_estatus',
                            'filter' => \kartik\select2\Select2::widget([
                                'model' => $searchModel,
                                'attribute' => 'contrato_estatus',
                                'data' => array_merge(
                                    Contratos::getStatusOptions(),
                                    ['sin_contrato' => 'Sin Contrato']
                                ),
                                'options' => [
                                    'placeholder' => 'Filtrar por estatus',
                                    'class' => 'form-control'
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ]),
                            'contentOptions' => function ($model) {
                                return ContractHelper::getContractStatusCellClasses($model);
                            },
                            'headerOptions' => [
                                'style' => 'color: white!important;',
                                'title' => 'Estado actual del contrato del afiliado. Pase el cursor sobre cada estado para más detalles.',
                                'data-toggle' => 'tooltip',
                                'data-placement' => 'top'
                            ],
                            'filterInputOptions' => [
                                'class' => 'form-control',
                                'prompt' => 'Todos'
                            ],
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'header' => 'ACCIONES',
                            'template' => '<div class="d-flex justify-content-center">{view}{update}{contracts}{atencion}{pagos}</div>',
                            'options' => ['style' => 'width:105px; min-width:105px;'],
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                            'buttons' => [
                                'view' => function ($url, $model, $key) use ($clinica) {
                                    $params = ['view', 'id' => $model->id];
                                    if ($clinica && $clinica->id !== null) {
                                        $params['clinica_id'] = $clinica->id;
                                    }
                                    return Html::a(
                                        '<i class="fa fa-eye"></i>',
                                        Url::to($params),
                                        [
                                            'title' => 'Detalle de Usuario',
                                            'class' => 'btn-action view',
                                            'data-pjax' => '0',
                                        ]
                                    );
                                },
                                'update' => function ($url, $model, $key) use ($clinica, $rol) {
                                    if ($rol == 'superadmin' || $rol = 'DIRECTOR-COMERCIALIZACIÓN') {
                                        $params = ['update', 'id' => $model->id];
                                        if ($clinica && $clinica->id !== null) {
                                            $params['clinica_id'] = $clinica->id;
                                        }
                                        return Html::a(
                                            '<i class="fas fa-pencil-alt ms-text-primary"></i>',
                                            Url::to($params),
                                            [
                                                'title' => 'Editar Usuario',
                                                'class' => 'btn-action view',
                                                'data-pjax' => '0',
                                            ]
                                        );
                                    } else {
                                        return "";
                                    }
                                },
                                'contracts' => function ($url, $model) {
                                    return Html::a(
                                        '<i class="fas fa-file-contract"></i>',
                                        ['/contratos/index-list', 'user_id' => $model->id],
                                        [
                                            'title' => 'Contratos del afiliado',
                                            'aria-label' => 'Ver contratos del afiliado',
                                            'class' => 'btn-action view',
                                            'data-pjax' => '0',
                                        ]
                                    );
                                },
                                'atencion' => function ($url, $model, $key) use ($rolesAtencionMedica, $clinica, $rol) {
                                    if (in_array($rol, $rolesAtencionMedica) && $model->clinica_id) {
                                        $hasActiveContract = \app\models\Contratos::find()
                                            ->where(['user_id' => $model->id, 'estatus' => 'Activo'])
                                            ->exists();

                                        if ($hasActiveContract) {
                                            $urlSiniestro = Url::to(['/sis-siniestro/index', 'user_id' => $model->id, 'modo' => 'siniestro', 'clinica_id' => $clinica ? $clinica->id : null]);
                                            $urlCita = Url::to(['/sis-siniestro/index', 'user_id' => $model->id, 'modo' => 'cita', 'clinica_id' => $clinica ? $clinica->id : null]);

                                            return Html::a(
                                                '<i class="fas fa-heartbeat" style="color: red;"></i>',
                                                '#',
                                                [
                                                    'title' => 'Gestionar Atención',
                                                    'class' => 'btn-action view atencion-btn',
                                                    'data-pjax' => '0',
                                                    'data' => [
                                                        'url-siniestro' => $urlSiniestro,
                                                        'url-cita' => $urlCita,
                                                    ],
                                                ]
                                            );
                                        }
                                    }
                                    return "";
                                },
                                'pagos' => function ($url, $model, $key) {
                                    if ($model->user_datos_type_id == 1 || $model->user_datos_type_id == 2) {
                                        $params = ['/contratos/index', 'user_id' => $model->id];
                                        return Html::a(
                                            '<i class="fas fa-file-invoice-dollar ms-text-success"></i>',
                                            Url::to($params),
                                            [
                                                'title' => 'Pagos',
                                                'class' => 'btn-action view',
                                                'data-pjax' => '0',
                                            ]
                                        );
                                    }
                                    return null;
                                },
                                'delete' => function ($url, $model, $key) use ($permisos, $clinica) {
                                    if ($permisos) {
                                        $params = ['delete', 'id' => $model->id];
                                        if ($clinica && $clinica->id !== null) {
                                            $params['clinica_id'] = $clinica->id;
                                        }
                                        return Html::a(
                                            '<i class="far fa-trash-alt ms-text-danger"></i>',
                                            Url::to($params),
                                            [
                                                'title' => 'Eliminar Usuario',
                                                'data-confirm' => '¿Estás seguro de que quieres eliminar esta clínica?',
                                                'data-method' => 'post',
                                                'class' => 'btn-action view',
                                                'data-pjax' => '0',
                                            ]
                                        );
                                    }
                                },
                            ],
                        ],
                    ],
                ]); ?>
            </div>

            <?php Pjax::end(); ?>

        </div>

    </div>
</div>

<?php
$js = <<<'JS'
// 1. EVENTO PARA ABRIR EL MODAL
$(document).on('click', '.atencion-btn', function(e) {
    e.preventDefault(); 
    
    var urlSiniestro = $(this).data('url-siniestro');
    var urlCita = $(this).data('url-cita');

    var contentHtml = 
        '<p class="text-xl mt-4 mb-5 font-weight-bold">¿Desea registrar una Atención o programar una Cita?</p>' +
        '<div class="d-flex justify-content-center w-100 mt-5">' +
            '<a href="' + urlSiniestro + '" class="btn-base btn-danger btn-lg mx-4 px-5 py-4" style="color: white; text-decoration: none; font-size: 1.5rem; min-width: 250px;">' +
                '<i class="fas fa-hand-holding-medical me-3"></i> ATENCION' +
            '</a>' +
            '<a href="' + urlCita + '" class="btn-base btn-success btn-lg mx-4 px-5 py-4" style="color: white; text-decoration: none; font-size: 1.5rem; min-width: 250px;">' +
                '<i class="fas fa-calendar-check me-3"></i> CITA' +
            '</a>' +
        '</div>';

    var footerHtml = 
        '<button type="button" class="btn-base btn-gray btn-lg mt-4 btn-cerrar-swal">' +
            'CERRAR' +
        '</button>';

    Swal.fire({
        title: 'Selecciona una Opción',
        icon: 'question',
        showCloseButton: true,
        width: '50%',       
        padding: '2em',     
        showConfirmButton: false, 
        showDenyButton: false,     
        showCancelButton: false,    
        buttonsStyling: false, 
        html: contentHtml,
        footer: footerHtml 
    });
});

// 2. EVENTO GLOBAL PARA CERRAR EL MODAL
$(document).on('click', '.btn-cerrar-swal', function(e) {
    e.preventDefault();
    Swal.close();
});

// 3. ROBUST TOOLTIP MANAGEMENT SYSTEM
$(document).ready(function() {
    function initAllTooltips() {
        $('.kpi-card-link').tooltip('dispose');
        $('[data-toggle="tooltip"]').tooltip('dispose');
        
        try {
            $('.kpi-card-link').tooltip({
                trigger: 'hover',
                delay: { "show": 200, "hide": 100 },
                container: 'body',
                boundary: 'window',
                html: true,
                template: '<div class="tooltip kpi-tooltip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
            });
            
            $('[data-toggle="tooltip"]').tooltip({
                trigger: 'hover',
                delay: { "show": 100, "hide": 100 },
                container: 'body',
                boundary: 'window',
                html: true
            });
        } catch (error) {
            console.error('Error initializing tooltips:', error);
        }
    }
    
    initAllTooltips();
    
    $(document).on('pjax:complete yiiGridViewUpdated', function() {
        setTimeout(initAllTooltips, 100);
    });
    
    $(document).on('click', '.contract-tooltip strong', function(e) {
        e.stopPropagation();
        var text = $(this).text();
        if (text.includes('Contrato #') || text.includes('Número:')) {
            var contractNum = text.replace('Contrato #', '').replace('Número: ', '').trim();
            navigator.clipboard.writeText(contractNum).then(function() {
                var badgeElement = $(e.target).closest('[data-toggle="tooltip"]');
                var originalText = badgeElement.text();
                var originalTitle = badgeElement.attr('title');
                badgeElement.text('✓ Copiado!');
                badgeElement.attr('title', 'Número de contrato copiado: ' + contractNum);
                badgeElement.tooltip('update').tooltip('show');
                setTimeout(function() {
                    badgeElement.text(originalText);
                    badgeElement.attr('title', originalTitle);
                    badgeElement.tooltip('update');
                }, 1500);
            });
        }
    });
    
    function highlightImportantStatuses() {
        $('.badge-danger, .badge-warning, .badge-secondary').each(function() {
            if (!$(this).hasClass('highlighted')) {
                $(this).addClass('highlighted');
                $(this).css({
                    'animation': 'pulse 2s infinite',
                    'border': '2px solid rgba(255,255,255,0.3)'
                });
            }
        });
    }
    
    highlightImportantStatuses();
    $(document).on('pjax:complete yiiGridViewUpdated', function() {
        setTimeout(highlightImportantStatuses, 150);
    });
});

// 4. LIVE SEARCH — Debounced PJAX via event delegation
// ============================================================
// Uses delegated events on `document` so the handler survives
// every PJAX replacement of the grid and its filter input.
$(document).ready(function () {
    var debounceMs = 400;
    var timer      = null;
    var lastQuery  = '';

    var INPUT_NAME     = 'UserDatosSearch[nombre_completo]';
    var INPUT_SELECTOR = 'input[name="' + INPUT_NAME + '"]';
    var PJAX_CONTAINER = '#affiliates-pjax';

    function getInput() {
        return $(INPUT_SELECTOR);
    }

    // ------------------------------------------------------------
    // Build the target URL manually.
    // We take the current URL and REPLACE the `nombre_completo`
    // parameter with the current input value. This preserves every
    // other filter (clinica_id, contrato_estatus, critical_filter,
    // pagination, sorting, etc.) without relying on a <form>.
    // ------------------------------------------------------------
    function buildUrl() {
        var params = new URLSearchParams(window.location.search);

        var val = $.trim(getInput().val() || '');

        if (val === '') {
            params.delete(INPUT_NAME);
        } else {
            params.set(INPUT_NAME, val);
        }

        // Yii pagination uses `page` — reset to 1 on new search
        params.delete('page');

        var action = window.location.pathname || window.location.href.split('?')[0];
        var qs = params.toString();
        return action + (qs ? '?' + qs : '');
    }

    function triggerSearch(force) {
        var $inp  = getInput();
        var $pjax = $(PJAX_CONTAINER);
        if (!$inp.length || !$pjax.length) return;

        var currentQuery = $.trim($inp.val() || '');
        if (!force && currentQuery === lastQuery) return;
        lastQuery = currentQuery;

        var url = buildUrl();

        if (window.history && window.history.pushState) {
            window.history.pushState({ gridFilter: true }, '', url);
        }

        $pjax.addClass('pjax-loading');

        $.pjax({
            url: url,
            container: PJAX_CONTAINER,
            push: false,
            replace: false,
            timeout: 5000,
            scrollTo: false,
            cache: false,
            fragment: PJAX_CONTAINER,
        });
    }

    // ============================================================
    // DELEGATED EVENTS — survive any DOM replacement
    // ============================================================

    // Every keystroke → debounce
    $(document).off('input.liveSearch').on('input.liveSearch', INPUT_SELECTOR, function () {
        clearTimeout(timer);
        timer = setTimeout(function () { triggerSearch(false); }, debounceMs);
    });

    // Enter → immediate (only when the focus is on the nombre_completo input)
    $(document).off('keydown.liveSearch').on('keydown.liveSearch', INPUT_SELECTOR, function (e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            clearTimeout(timer);
            triggerSearch(true);
        }
    });

    // Blur → flush pending search ONLY if the input still exists
    // (i.e., the user actually tabbed/clicked away, not PJAX replacement)
    $(document).off('blur.liveSearch').on('blur.liveSearch', INPUT_SELECTOR, function () {
        var $self = $(this);
        // Defer the check: if PJAX is about to replace the grid,
        // this element will be detached from the DOM.
        setTimeout(function () {
            if (!$self.closest('body').length) {
                // Input was detached by PJAX — not a real blur. Do nothing.
                return;
            }
            if (timer) {
                clearTimeout(timer);
                timer = null;
                triggerSearch(false);
            }
        }, 0);
    });

    // Kill Yii/Kartik's native keydown handlers on the nombre_completo
    // input so we don't double-fire on blur/Enter. This does NOT affect
    // the other filter inputs (cedula, email, telefono, etc.).
    $(document).off('focus.yiiKill').on('focus.yiiKill', INPUT_SELECTOR, function () {
        $(this).off('change.yiiGridView keydown.yiiGridView');
    });

    // ============================================================
    // FORM SUBMIT INTERCEPTION — ONLY FOR THE nombre_completo INPUT
    // ============================================================
    // We must NOT hijack submits coming from other filter inputs
    // (cedula, email, telefono, contrato_estatus, etc.) because our
    // buildUrl() only knows how to encode `nombre_completo`.
    // Those filters should fall through to Yii's native grid filter
    // (full-page reload with all params) so they work correctly.
    // ============================================================
    $(document).off('submit.liveSearch').on('submit.liveSearch', 'form', function (e) {
        var $form = $(this);

        // Only handle forms that contain the nombre_completo input
        if (!$form.find(INPUT_SELECTOR).length) {
            return;
        }

        // Only intercept if the user pressed Enter / clicked submit
        // *from* the nombre_completo input. Otherwise let Yii handle it.
        var activeEl = document.activeElement;
        var fromNombreInput = activeEl && $(activeEl).is(INPUT_SELECTOR);

        // Also allow interception when the submit came from the
        // implicit search trigger (e.g., the user pressed Enter while
        // focus was on the nombre_completo input).
        if (!fromNombreInput) {
            // The user is submitting from another filter input (or the
            // submit was triggered by some other control). Let the
            // browser perform the normal form submit — Yii will reload
            // the grid with ALL filter parameters intact.
            return;
        }

        e.preventDefault();
        clearTimeout(timer);
        triggerSearch(true);
    });

    // Initialize lastQuery from the current URL (authoritative source)
    var initParams = new URLSearchParams(window.location.search);
    lastQuery = $.trim(initParams.get(INPUT_NAME) || '');

    // ============================================================
    // PJAX LIFECYCLE — bound on document so it survives replacement
    // ============================================================
    $(document)
        .off('pjax:end.liveSearch pjax:error.liveSearch pjax:timeout.liveSearch')
        .on('pjax:end.liveSearch pjax:error.liveSearch pjax:timeout.liveSearch', function (e) {
            if (e.target && e.target.id !== 'affiliates-pjax') return;
            $(PJAX_CONTAINER).removeClass('pjax-loading');
        });

    // ============================================================
    // BROWSER BACK / FORWARD
    // ============================================================
    $(window).off('popstate.liveSearch').on('popstate.liveSearch', function () {
        var url = window.location.href;
        var $pjax = $(PJAX_CONTAINER);

        $pjax.addClass('pjax-loading');
        $.pjax({
            url: url,
            container: PJAX_CONTAINER,
            push: false,
            replace: true,
            timeout: 5000,
            scrollTo: false,
            cache: false,
            fragment: PJAX_CONTAINER,
        });

        // Sync input from URL after swap
        setTimeout(function () {
            var params = new URLSearchParams(window.location.search);
            var newVal = params.get(INPUT_NAME) || '';
            var $fresh = getInput();
            if ($fresh.length && $fresh.val() !== newVal) {
                $fresh.val(newVal);
                lastQuery = $.trim(newVal);
            }
        }, 0);
    });
});

// 5. EYE-CATCHING DUAL SCROLLBAR SYSTEM
// ============================================================
// - Keeps a stylized top scrollbar in sync with the grid's native
//   bottom scrollbar (the actual overflow driver).
// - Provides left/right arrow buttons for one-click scrolling.
// - Auto-hides the whole shell when there's no horizontal overflow.
// - Re-binds on PJAX / grid refresh / window resize (idempotent).
// ============================================================
$(document).ready(function () {
    var $gridWrapper = $('#grid-scroll-container');
    var $shell       = $('.top-scrollbar-shell');
    var $top         = $('.top-native-scrollbar');
    var $dummy       = $('.top-native-dummy');
    var $arrowLeft   = $('.top-scrollbar-arrow-left');
    var $arrowRight  = $('.top-scrollbar-arrow-right');

    var $activeScroller = $();
    var syncingFrom     = null;

    if (!$gridWrapper.length || !$shell.length || !$top.length) {
        return;
    }

    // Find the innermost element that actually overflows horizontally.
    function getActiveGridScroller() {
        var $candidates = $gridWrapper.find('*').addBack();
        var best = null;
        var bestWidth = 0;

        $candidates.each(function () {
            var el = this;
            if (!el || !el.clientWidth) return;
            if (el.scrollWidth > el.clientWidth + 1 && el.scrollWidth > bestWidth) {
                best = el;
                bestWidth = el.scrollWidth;
            }
        });

        return best ? $(best) : $gridWrapper;
    }

    function syncBars(source) {
        var gridEl = $activeScroller.get(0);
        var topEl  = $top.get(0);
        if (!gridEl || !topEl) return;

        if (source === 'top') {
            if (syncingFrom === 'grid') return;
            syncingFrom = 'top';
            gridEl.scrollLeft = topEl.scrollLeft;
            syncingFrom = null;
            return;
        }

        if (source === 'grid') {
            if (syncingFrom === 'top') return;
            syncingFrom = 'grid';
            topEl.scrollLeft = gridEl.scrollLeft;
            syncingFrom = null;
        }
    }

    function getMaxScrollWidth() {
        var maxWidth = 0;

        $gridWrapper.find('table').each(function () {
            if (this.scrollWidth > maxWidth) maxWidth = this.scrollWidth;
        });

        if ($gridWrapper[0] && $gridWrapper[0].scrollWidth > maxWidth) {
            maxWidth = $gridWrapper[0].scrollWidth;
        }

        if ($activeScroller[0] && $activeScroller[0].scrollWidth > maxWidth) {
            maxWidth = $activeScroller[0].scrollWidth;
        }

        return maxWidth;
    }

    function refreshTopBar() {
        $activeScroller = getActiveGridScroller();

        var gridEl = $activeScroller.get(0);
        if (!gridEl || !$top.length || !$dummy.length) return;

        var scrollWidth   = getMaxScrollWidth();
        var viewportWidth = gridEl.clientWidth;
        var hasHorizontalScroll = scrollWidth > (viewportWidth + 1);

        if (hasHorizontalScroll) {
            $dummy.css('width', (scrollWidth + 20) + 'px');
            $shell.css('display', 'flex');

            setTimeout(function () {
                var maxScroll = scrollWidth - $top[0].clientWidth;
                if ($top[0].scrollLeft > maxScroll) {
                    $top[0].scrollLeft = maxScroll;
                }
            }, 20);

            syncBars('grid');
        } else {
            $shell.hide();
        }
    }

    // Top bar drives the grid.
    $top.off('scroll.dual').on('scroll.dual', function () {
        syncBars('top');
    });

    // Arrow buttons — smooth one-click scroll on the active scroller.
    function scrollByStep(direction) {
        var gridEl = $activeScroller.get(0);
        if (!gridEl) return;

        var step = Math.max(240, Math.floor(gridEl.clientWidth * 0.6));
        var target = gridEl.scrollLeft + (direction * step);

        var maxScroll = gridEl.scrollWidth - gridEl.clientWidth;
        if (target < 0) target = 0;
        if (target > maxScroll) target = maxScroll;

        if ('scrollBehavior' in document.documentElement.style) {
            gridEl.scrollTo({ left: target, behavior: 'smooth' });
        } else {
            gridEl.scrollLeft = target;
        }
    }

    $arrowLeft.off('click.dual').on('click.dual', function (e) {
        e.preventDefault();
        scrollByStep(-1);
    });

    $arrowRight.off('click.dual').on('click.dual', function (e) {
        e.preventDefault();
        scrollByStep(1);
    });

    function bindGridScroll() {
        $gridWrapper.find('*').off('scroll.dual-grid');
        $gridWrapper.off('scroll.dual-grid');

        $activeScroller = getActiveGridScroller();
        $activeScroller.off('scroll.dual-grid').on('scroll.dual-grid', function () {
            syncBars('grid');
        });
    }

    // Initial passes — a few runs to let Kartik GridView settle.
    bindGridScroll();
    refreshTopBar();
    setTimeout(refreshTopBar, 120);
    setTimeout(refreshTopBar, 320);

    // Recompute on window resize.
    $(window).off('resize.dual').on('resize.dual', refreshTopBar);

    // Recompute after PJAX / grid refresh events.
    $(document).on('pjax:complete yiiGridViewUpdated ajaxComplete', function () {
        setTimeout(function () {
            bindGridScroll();
            refreshTopBar();
        }, 150);
    });
});
JS;

$this->registerJs($js, View::POS_READY);
?>

<style>
    /* ============================================ */
    /* EYE-CATCHING DUAL SCROLLBAR                  */
    /* Microsoft Fluent "Content Scroller" pattern  */
    /* ============================================ */
    .table-responsive-scroll {
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
    }

    /* Wrapper that holds the hint label + arrows + native scroll strip */
    .top-scrollbar-shell {
        display: none;
        /* toggled by JS when overflow exists */
        align-items: center;
        gap: 10px;
        margin: 0 12px 12px 12px;
        padding: 8px 12px;
        border-radius: 10px;
        background: linear-gradient(135deg, #f8f9ff 0%, #eef2ff 100%);
        border: 1px solid rgba(99, 102, 241, 0.15);
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.9),
            0 1px 3px rgba(15, 23, 42, 0.04);
        position: relative;
        overflow: hidden;
        transition: box-shadow 0.25s ease, border-color 0.25s ease;
    }

    /* Animated gradient accent line at the top */
    .top-scrollbar-shell::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg,
                #0078d4 0%,
                #00b7c3 35%,
                #6b69d6 70%,
                #0078d4 100%);
        background-size: 200% 100%;
        animation: scrollbarAccentSlide 6s linear infinite;
        opacity: 0.85;
    }

    @keyframes scrollbarAccentSlide {
        0% {
            background-position: 0% 50%;
        }

        100% {
            background-position: 200% 50%;
        }
    }

    .top-scrollbar-shell:hover {
        border-color: rgba(99, 102, 241, 0.35);
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.9),
            0 4px 14px rgba(99, 102, 241, 0.15);
    }

    /* Hint label on the left */
    .top-scrollbar-hint {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        color: #4f46e5;
        white-space: nowrap;
        user-select: none;
        flex: 0 0 auto;
        padding-right: 6px;
        border-right: 1px solid rgba(99, 102, 241, 0.2);
    }

    .top-scrollbar-hint i {
        font-size: 12px;
        animation: scrollbarHintPulse 2.4s ease-in-out infinite;
    }

    @keyframes scrollbarHintPulse {

        0%,
        100% {
            transform: translateX(0);
            opacity: 1;
        }

        50% {
            transform: translateX(3px);
            opacity: 0.7;
        }
    }

    /* Arrow buttons */
    .top-scrollbar-arrow {
        flex: 0 0 auto;
        width: 28px;
        height: 28px;
        border: none;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.9);
        color: #4f46e5;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        padding: 0;
        box-shadow:
            inset 0 0 0 1px rgba(99, 102, 241, 0.15),
            0 1px 2px rgba(15, 23, 42, 0.05);
        transition: all 0.2s ease;
    }

    .top-scrollbar-arrow:hover {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.35);
    }

    .top-scrollbar-arrow:active {
        transform: translateY(0);
        box-shadow: 0 1px 2px rgba(79, 70, 229, 0.3);
    }

    .top-scrollbar-arrow i {
        font-size: 12px;
    }

    /* The actual native scroll strip */
    .top-native-scrollbar {
        flex: 1 1 auto;
        height: 14px;
        overflow-x: scroll;
        overflow-y: hidden;
        cursor: pointer;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #6366f1 #e0e7ff;
    }

    .top-native-scrollbar .top-native-dummy {
        display: inline-block;
        width: 100%;
        min-height: 1px;
        height: 1px;
    }

    /* WebKit — track */
    .top-native-scrollbar::-webkit-scrollbar,
    .table-responsive-scroll::-webkit-scrollbar {
        height: 12px;
    }

    .top-native-scrollbar::-webkit-scrollbar-track {
        background: linear-gradient(90deg, #e0e7ff 0%, #ede9fe 100%);
        border-radius: 6px;
        box-shadow: inset 0 1px 2px rgba(79, 70, 229, 0.08);
    }

    .table-responsive-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 6px;
    }

    /* WebKit — thumb: gradient + glow */
    .top-native-scrollbar::-webkit-scrollbar-thumb {
        background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 50%, #06b6d4 100%);
        background-size: 200% 100%;
        border-radius: 6px;
        border: 2px solid transparent;
        background-clip: padding-box;
        transition: all 0.2s ease;
    }

    .top-native-scrollbar:hover::-webkit-scrollbar-thumb {
        background: linear-gradient(90deg, #4f46e5 0%, #7c3aed 50%, #0891b2 100%);
        background-size: 200% 100%;
        box-shadow: 0 0 8px rgba(99, 102, 241, 0.55);
    }

    .top-native-scrollbar::-webkit-scrollbar-thumb:hover {
        box-shadow: 0 0 12px rgba(99, 102, 241, 0.75);
    }

    /* Native bottom thumb — subtle indigo tint */
    .table-responsive-scroll::-webkit-scrollbar-thumb {
        background: linear-gradient(90deg, #c7d2fe 0%, #ddd6fe 100%);
        border-radius: 6px;
        transition: background 0.2s ease;
    }

    .table-responsive-scroll::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(90deg, #a5b4fc 0%, #c4b5fd 100%);
    }

    /* Firefox native scrollbars */
    .top-native-scrollbar {
        scrollbar-color: #6366f1 #e0e7ff;
    }

    .table-responsive-scroll {
        scrollbar-color: #c7d2fe #f1f1f1;
        scrollbar-width: thin;
    }

    /* Reduce motion — respect user preference */
    @media (prefers-reduced-motion: reduce) {

        .top-scrollbar-shell::before,
        .top-scrollbar-hint i {
            animation: none !important;
        }
    }

    /* Responsive tweaks */
    @media (max-width: 768px) {
        .top-scrollbar-shell {
            margin: 0 8px 10px 8px;
            padding: 6px 8px;
            gap: 6px;
        }

        .top-scrollbar-hint {
            font-size: 10px;
            letter-spacing: 0.4px;
            padding-right: 4px;
        }

        .top-scrollbar-arrow {
            width: 24px;
            height: 24px;
        }
    }

    /* ============================================ */
    /* TOOLTIP CUSTOM STYLES                        */
    /* ============================================ */
    .kpi-tooltip .tooltip-inner {
        background: #2c3e50;
        color: #ffffff;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 0.85rem;
        max-width: 300px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .kpi-tooltip .tooltip-inner strong {
        color: #ffffff;
        font-size: 0.95rem;
    }

    .kpi-tooltip .tooltip-inner .text-muted {
        color: #aab8c5 !important;
        font-size: 0.75rem;
        display: block;
        margin-top: 4px;
    }

    .kpi-tooltip .tooltip-inner .text-warning {
        color: #ffc107 !important;
        font-weight: 600;
    }

    .kpi-tooltip .arrow::before {
        border-top-color: #2c3e50 !important;
    }

    .kpi-tooltip.bs-tooltip-top .arrow::before {
        border-top-color: #2c3e50 !important;
    }

    .kpi-tooltip.bs-tooltip-bottom .arrow::before {
        border-bottom-color: #2c3e50 !important;
    }

    .kpi-tooltip.bs-tooltip-left .arrow::before {
        border-left-color: #2c3e50 !important;
    }

    .kpi-tooltip.bs-tooltip-right .arrow::before {
        border-right-color: #2c3e50 !important;
    }

    /* PJAX busy state — subtle dim while refreshing the grid */
    #affiliates-pjax.pjax-loading {
        opacity: 0.55;
        transition: opacity 0.15s ease;
        pointer-events: none;
    }

    /* ============================================ */
    /* KPI CARDS — Microsoft Fluent Redesign        */
    /* ============================================ */
    .kpi-cards-row {
        display: flex;
        flex-wrap: nowrap;
        gap: 14px;
        margin-bottom: 24px;
        overflow-x: auto;
        padding: 6px 2px 14px 2px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }

    .kpi-cards-row::-webkit-scrollbar {
        height: 5px;
    }

    .kpi-cards-row::-webkit-scrollbar-track {
        background: transparent;
    }

    .kpi-cards-row::-webkit-scrollbar-thumb {
        background: #c7cbd1;
        border-radius: 3px;
    }

    .kpi-cards-row::-webkit-scrollbar-thumb:hover {
        background: #9aa0a6;
    }

    /* ── Card link wrapper ── */
    .kpi-card-link {
        flex: 1 1 0;
        min-width: 0;
        max-width: none;
        text-decoration: none !important;
        display: block;
        transition: transform 0.22s cubic-bezier(0.2, 0, 0, 1);
        cursor: pointer;
        outline: none;
    }

    .kpi-card-link:hover {
        transform: translateY(-4px);
        text-decoration: none !important;
    }

    .kpi-card-link:focus-visible .kpi-card {
        outline: 2px solid #0078d4;
        outline-offset: 2px;
    }

    /* ── The card itself ── */
    .kpi-card {
        position: relative;
        background: #ffffff;
        border-radius: 12px;
        box-shadow:
            0 1px 2px rgba(15, 23, 42, 0.06),
            0 2px 8px rgba(15, 23, 42, 0.04);
        overflow: hidden;
        transition:
            box-shadow 0.22s cubic-bezier(0.2, 0, 0, 1),
            transform 0.22s cubic-bezier(0.2, 0, 0, 1);
        border: 1px solid rgba(15, 23, 42, 0.06);
        min-height: 104px;
        height: 100%;
        width: 100%;
        display: flex;
        flex-direction: column;
    }

    .kpi-card-link:hover .kpi-card {
        box-shadow:
            0 4px 8px rgba(15, 23, 42, 0.08),
            0 12px 28px rgba(15, 23, 42, 0.14);
    }

    /* Fluent accent bar — brightens on hover */
    .kpi-card .kpi-accent {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: rgba(255, 255, 255, 0.35);
        transition: background 0.22s ease, height 0.22s ease;
        pointer-events: none;
    }

    .kpi-card-link:hover .kpi-accent {
        height: 4px;
        background: rgba(255, 255, 255, 0.75);
    }

    /* ── Header row: icon · title · chevron ── */
    .kpi-card-header {
        padding: 14px 16px 6px 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        position: relative;
        flex-shrink: 0;
    }

    .kpi-card-header .kpi-icon {
        font-size: 1.05rem;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.18);
        opacity: 0.95;
        transition: transform 0.3s ease;
    }

    .kpi-card-link:hover .kpi-icon {
        transform: scale(1.08) rotate(-3deg);
    }

    .kpi-card-header .kpi-title {
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        opacity: 0.95;
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .kpi-card-header .kpi-arrow {
        font-size: 0.7rem;
        opacity: 0.45;
        transition: transform 0.25s cubic-bezier(0.2, 0, 0, 1), opacity 0.25s ease;
        margin-left: auto;
        flex-shrink: 0;
    }

    .kpi-card-link:hover .kpi-arrow {
        opacity: 1;
        transform: translateX(4px);
    }

    /* ── Body: hero number + context label ── */
    .kpi-card-body {
        padding: 0 16px 16px 16px;
        display: flex;
        flex-direction: column;
        flex: 1;
        justify-content: flex-end;
    }

    .kpi-card-body .kpi-number {
        font-size: 2.1rem;
        font-weight: 700;
        line-height: 1;
        margin: 0;
        letter-spacing: -0.8px;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }

    .kpi-card-body .kpi-percent {
        font-size: 0.9rem;
        font-weight: 600;
        opacity: 0.7;
        margin-left: 4px;
        letter-spacing: 0;
    }

    .kpi-card-body .kpi-label {
        font-size: 0.7rem;
        opacity: 0.8;
        margin-top: 8px;
        display: flex;
        align-items: center;
        gap: 4px;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .kpi-card-body .kpi-label i {
        font-size: 0.65rem;
        opacity: 0.75;
        flex-shrink: 0;
    }

    .kpi-card-body .kpi-sub {
        display: block;
        font-size: 0.62rem;
        opacity: 0.7;
        margin-top: 2px;
        white-space: nowrap;
    }

    /* ──────────────────────────────────────────── */
    /* Card color themes                             */
    /* ──────────────────────────────────────────── */

    /* Total — Fluent blue */
    .kpi-card.primary {
        background: linear-gradient(135deg, #0f6cbd 0%, #0078d4 55%, #106ebe 100%);
        color: #ffffff;
        border-color: rgba(15, 108, 189, 0.2);
    }

    .kpi-card.primary .kpi-number,
    .kpi-card.primary .kpi-label,
    .kpi-card.primary .kpi-title,
    .kpi-card.primary .kpi-icon,
    .kpi-card.primary .kpi-arrow,
    .kpi-card.primary .kpi-percent {
        color: #ffffff !important;
    }

    /* Activos — Fluent green */
    .kpi-card.success {
        background: linear-gradient(135deg, #0e7a0d 0%, #107c10 55%, #0c6b0c 100%);
        color: #ffffff;
        border-color: rgba(16, 124, 16, 0.2);
    }

    .kpi-card.success .kpi-number,
    .kpi-card.success .kpi-label,
    .kpi-card.success .kpi-title,
    .kpi-card.success .kpi-icon,
    .kpi-card.success .kpi-arrow,
    .kpi-card.success .kpi-percent {
        color: #ffffff !important;
    }

    /* Registrados — Fluent royal blue */
    .kpi-card.registrado {
        background: linear-gradient(135deg, #4f46e5 0%, #5b5fc7 55%, #4338ca 100%);
        color: #ffffff;
        border-color: rgba(91, 95, 199, 0.2);
    }

    .kpi-card.registrado .kpi-number,
    .kpi-card.registrado .kpi-label,
    .kpi-card.registrado .kpi-title,
    .kpi-card.registrado .kpi-icon,
    .kpi-card.registrado .kpi-arrow,
    .kpi-card.registrado .kpi-percent {
        color: #ffffff !important;
    }

    /* Suspendidos — Fluent neutral gray */
    .kpi-card.danger {
        background: linear-gradient(135deg, #5c5c5c 0%, #6b6b6b 55%, #4f4f4f 100%);
        color: #ffffff;
        border-color: rgba(107, 107, 107, 0.2);
    }

    .kpi-card.danger .kpi-number,
    .kpi-card.danger .kpi-label,
    .kpi-card.danger .kpi-title,
    .kpi-card.danger .kpi-icon,
    .kpi-card.danger .kpi-arrow,
    .kpi-card.danger .kpi-percent {
        color: #ffffff !important;
    }

    /* Anulados — Fluent danger red */
    .kpi-card.anulado {
        background: linear-gradient(135deg, #c50f1f 0%, #d13438 55%, #a4262c 100%);
        color: #ffffff;
        border-color: rgba(209, 52, 56, 0.2);
    }

    .kpi-card.anulado .kpi-number,
    .kpi-card.anulado .kpi-label,
    .kpi-card.anulado .kpi-title,
    .kpi-card.anulado .kpi-icon,
    .kpi-card.anulado .kpi-arrow,
    .kpi-card.anulado .kpi-percent {
        color: #ffffff !important;
    }

    /* Vencidos — Fluent orange */
    .kpi-card.vencido {
        background: linear-gradient(135deg, #c85a00 0%, #da3b01 55%, #ad4e00 100%);
        color: #ffffff;
        border-color: rgba(218, 59, 1, 0.2);
    }

    .kpi-card.vencido .kpi-number,
    .kpi-card.vencido .kpi-label,
    .kpi-card.vencido .kpi-title,
    .kpi-card.vencido .kpi-icon,
    .kpi-card.vencido .kpi-arrow,
    .kpi-card.vencido .kpi-percent {
        color: #ffffff !important;
    }

    /* Críticos — Fluent warning yellow */
    .kpi-card.critical {
        background: linear-gradient(135deg, #c19c00 0%, #eaa300 55%, #b88f00 100%);
        color: #1a1a1a;
        border-color: rgba(234, 163, 0, 0.2);
    }

    .kpi-card.critical .kpi-number,
    .kpi-card.critical .kpi-label,
    .kpi-card.critical .kpi-title,
    .kpi-card.critical .kpi-icon,
    .kpi-card.critical .kpi-arrow {
        color: #1a1a1a !important;
    }

    .kpi-card.critical .kpi-percent {
        color: #5a4a00 !important;
        font-weight: 700;
    }

    /* ──────────────────────────────────────────── */
    /* Responsive                                    */
    /* ──────────────────────────────────────────── */
    @media (max-width: 1200px) {
        .kpi-cards-row {
            gap: 12px;
        }

        .kpi-card {
            min-height: 96px;
        }

        .kpi-card-body .kpi-number {
            font-size: 1.85rem;
        }
    }

    @media (max-width: 992px) {
        .kpi-cards-row {
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 12px;
        }

        .kpi-card-link {
            flex: 0 0 168px;
            min-width: 156px;
        }

        .kpi-card {
            min-height: 92px;
        }

        .kpi-card-header {
            padding: 12px 14px 4px 14px;
        }

        .kpi-card-header .kpi-icon {
            width: 24px;
            height: 24px;
            font-size: 0.95rem;
        }

        .kpi-card-header .kpi-title {
            font-size: 0.72rem;
        }

        .kpi-card-body {
            padding: 0 14px 14px 14px;
        }

        .kpi-card-body .kpi-number {
            font-size: 1.55rem;
        }

        .kpi-card-body .kpi-label {
            font-size: 0.65rem;
        }
    }

    @media (max-width: 576px) {
        .kpi-cards-row {
            gap: 8px;
        }

        .kpi-card-link {
            flex: 0 0 140px;
            min-width: 130px;
        }

        .kpi-card {
            min-height: 84px;
        }

        .kpi-card-header {
            padding: 10px 12px 2px 12px;
            gap: 8px;
        }

        .kpi-card-header .kpi-icon {
            width: 22px;
            height: 22px;
            font-size: 0.85rem;
        }

        .kpi-card-header .kpi-title {
            font-size: 0.65rem;
            letter-spacing: 0.4px;
        }

        .kpi-card-body {
            padding: 0 12px 12px 12px;
        }

        .kpi-card-body .kpi-number {
            font-size: 1.35rem;
        }

        .kpi-card-body .kpi-label {
            font-size: 0.6rem;
        }
    }

    /* Respect reduced motion */
    @media (prefers-reduced-motion: reduce) {

        .kpi-card-link,
        .kpi-card,
        .kpi-card .kpi-accent,
        .kpi-card-header .kpi-icon,
        .kpi-card-header .kpi-arrow {
            transition: none !important;
            animation: none !important;
        }

        .kpi-card-link:hover {
            transform: none;
        }

        .kpi-card-link:hover .kpi-icon {
            transform: none;
        }

        .kpi-card-link:hover .kpi-arrow {
            transform: none;
        }
    }

    .badge-critical {
        background: linear-gradient(135deg, #ffc107, #e0a800);
        color: #212529 !important;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .section-title .badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.75rem;
        vertical-align: middle;
    }

    /* ============================================ */
    /* CONTRACT STATUS BADGE STYLES                 */
    /* ============================================ */
    .badge {
        display: inline-block;
        padding: 0.35em 0.65em;
        font-size: 0.85em;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.375rem;
        transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        min-width: 80px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border: 1px solid transparent;
    }

    .badge-primary {
        background: #007bff;
        color: #fff;
        border-color: #007bff;
    }

    .badge-success {
        background: #28a745;
        color: #fff;
        border-color: #28a745;
    }

    .badge-danger {
        background: #dc3545;
        color: #fff;
        border-color: #dc3545;
    }

    .badge-warning {
        background: #ffc107;
        color: #212529;
        border-color: #ffc107;
    }

    .badge-secondary {
        background: #6c757d;
        color: #fff;
        border-color: #6c757d;
    }

    .badge-info {
        background: #17a2b8;
        color: #fff;
        border-color: #17a2b8;
    }

    .badge-light {
        color: #212529;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
    }

    .badge:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .contract-status-cell {
        font-weight: 600;
        text-align: center !important;
        vertical-align: middle !important;
    }

    .contract-status-cell.activo {
        background-color: rgba(40, 167, 69, 0.05) !important;
    }

    .contract-status-cell.vencido,
    .contract-status-cell.suspendido,
    .contract-status-cell.anulado {
        background-color: rgba(220, 53, 69, 0.05) !important;
    }

    .contract-status-cell.registrado {
        background-color: rgba(0, 123, 255, 0.05) !important;
    }

    .contract-status-cell.pendiente {
        background-color: rgba(23, 162, 184, 0.05) !important;
    }

    tbody tr:hover .contract-status-cell.activo {
        background-color: rgba(40, 167, 69, 0.1) !important;
    }

    /* ============================================ */
    /* CÉDULA DE IDENTIDAD STYLES                   */
    /* ============================================ */
    .cedula-grande-container {
        text-align: center;
        padding: 10px 5px;
        margin: 0;
    }

    .cedula-grande-text {
        font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        font-size: 1.2rem !important;
        font-weight: 600;
        line-height: 1.3;
        margin-bottom: 4px;
        letter-spacing: 0.3px;
    }

    .menor-sin-cedula .cedula-grande-text {
        font-size: 1.3rem !important;
        font-weight: 700;
        color: #e67e22;
        font-family: 'Courier New', Consolas, monospace;
        background-color: rgba(255, 152, 0, 0.1);
        border: 2px solid rgba(255, 152, 0, 0.2);
        border-radius: 8px;
        padding: 10px 15px;
        display: inline-block;
    }

    .menor-sin-cedula .consecutivo-grande {
        font-size: 1.4rem !important;
        font-weight: 900;
        color: #e74c3c;
        background-color: rgba(231, 76, 60, 0.15);
        padding: 0 6px;
        border-radius: 5px;
        margin: 0 3px;
    }

    .cedula-normal .cedula-grande-text {
        color: #2c3e50;
        background-color: rgba(52, 152, 219, 0.1);
        border: 2px solid rgba(52, 152, 219, 0.2);
        border-radius: 6px;
        padding: 8px 12px;
        display: inline-block;
    }

    .cedula-pendiente .cedula-grande-text {
        color: #7f8c8d;
        background-color: rgba(127, 140, 141, 0.1);
        border: 2px dashed rgba(127, 140, 141, 0.3);
        border-radius: 6px;
        padding: 8px 12px;
        display: inline-block;
    }

    .sin-cedula .cedula-grande-text {
        color: #e74c3c;
        background-color: rgba(231, 76, 60, 0.1);
        border: 2px solid rgba(231, 76, 60, 0.2);
        border-radius: 6px;
        padding: 8px 12px;
        display: inline-block;
    }

    .edad-etiqueta,
    .estado-etiqueta {
        font-size: 0.85rem;
        color: #7f8c8d;
        background-color: rgba(127, 140, 141, 0.1);
        padding: 4px 10px;
        border-radius: 12px;
        display: inline-block;
        margin-top: 5px;
        font-style: italic;
    }

    .bg-menor-sin-cedula {
        background-color: rgba(255, 193, 7, 0.08) !important;
    }

    .bg-cedula-normal {
        background-color: rgba(52, 152, 219, 0.03) !important;
    }

    tbody tr:hover .bg-menor-sin-cedula {
        background-color: rgba(255, 193, 7, 0.12) !important;
    }

    tbody tr:hover .bg-cedula-normal {
        background-color: rgba(52, 152, 219, 0.06) !important;
    }

    .cedula-grande-text {
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
    }

    @media (max-width: 768px) {
        .cedula-grande-text {
            font-size: 1.1rem !important;
        }

        .menor-sin-cedula .cedula-grande-text {
            font-size: 1.2rem !important;
            padding: 8px 10px;
        }

        .menor-sin-cedula .consecutivo-grande {
            font-size: 1.3rem !important;
        }
    }

    @media print {
        .cedula-grande-text {
            font-size: 11pt !important;
            color: #000 !important;
            background-color: transparent !important;
            border: 1px solid #ccc !important;
        }
    }

    /* ============================================ */
    /* CORPORATIVO BADGE STYLES                     */
    /* ============================================ */
    .corporativo-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.6rem 1.2rem;
        font-size: 1rem;
        font-weight: 600;
        border: 2px solid;
        border-radius: 25px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        letter-spacing: 0.4px;
        max-width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        min-height: 40px;
        background: linear-gradient(135deg, #dbeafe 0%, #93c5fd 100%);
        border-color: #3b82f6;
        color: #1e40af;
    }

    .corporativo-badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .corporativo-badge i {
        font-size: 1.1rem;
        margin-right: 10px;
    }

    .no-corporativo-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.6rem 1.2rem;
        font-size: 0.95rem;
        font-weight: 500;
        color: #6c757d;
        background: #f1f2f6;
        border: 2px dashed #ced4da;
        border-radius: 25px;
        transition: all 0.3s ease;
        letter-spacing: 0.3px;
        min-height: 40px;
    }

    .no-corporativo-badge i {
        font-size: 1rem;
        margin-right: 10px;
        color: #adb5bd;
    }

    .no-corporativo-badge:hover {
        background: #e9ecef;
        border-color: #adb5bd;
        transform: translateY(-1px);
    }

    .corporativo-badge {
        animation: fadeInBadge 0.5s ease-in-out;
    }

    @keyframes fadeInBadge {
        0% {
            opacity: 0;
            transform: scale(0.9);
        }

        100% {
            opacity: 1;
            transform: scale(1);
        }
    }

    .grid-view td:has(.badge) {
        text-align: center !important;
        vertical-align: middle !important;
    }

    .badge {
        display: inline-block !important;
        text-align: center !important;
    }

    .grid-view td {
        vertical-align: middle !important;
        padding: 8px 6px !important;
    }

    .grid-view th {
        vertical-align: middle !important;
    }

    @media (max-width: 768px) {

        .corporativo-badge,
        .no-corporativo-badge {
            font-size: 0.85rem;
            padding: 0.4rem 0.9rem;
            min-height: 34px;
            white-space: normal;
            word-break: break-word;
        }

        .corporativo-badge i,
        .no-corporativo-badge i {
            font-size: 0.9rem;
            margin-right: 6px;
        }

        .badge {
            font-size: 0.75em;
            padding: 0.25em 0.5em;
        }
    }

    @media (max-width: 576px) {

        .corporativo-badge,
        .no-corporativo-badge {
            font-size: 0.75rem;
            padding: 0.3rem 0.7rem;
            min-height: 30px;
            border-radius: 20px;
        }

        .corporativo-badge i,
        .no-corporativo-badge i {
            font-size: 0.8rem;
            margin-right: 4px;
        }
    }

    /* ============================================ */
    /* PASSPORT (P) CÉDULA STYLES                   */
    /* ============================================ */
    .cedula-grande-container.cedula-passport .cedula-grande-text {
        font-size: 1.2rem !important;
        font-weight: 700;
        color: #005a9e;
        background: linear-gradient(135deg, rgba(0, 120, 212, 0.08) 0%, rgba(0, 120, 212, 0.02) 100%);
        border: 2px solid rgba(0, 120, 212, 0.25);
        border-radius: 8px;
        padding: 10px 18px;
        display: inline-block;
        box-shadow: 0 2px 8px rgba(0, 120, 212, 0.08);
    }

    .cedula-grande-container.cedula-passport .cedula-grande-text i {
        color: #0078d4;
    }

    .bg-passport {
        background-color: rgba(0, 120, 212, 0.04) !important;
    }

    tbody tr:hover .bg-passport {
        background-color: rgba(120, 120, 212, 0.08) !important;
    }

    .filter-container .passport-badge {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        background: rgba(0, 120, 212, 0.1);
        border: 1px solid rgba(0, 120, 212, 0.2);
        border-radius: 12px;
        font-size: 0.7rem;
        color: #005a9e;
        font-weight: 600;
    }
</style>