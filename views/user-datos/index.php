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
$permisos = ($rol == 'superadmin' || $rol == 'DIRECTOR-COMERCIALIZACIÓN' || $rol == 'Asesor' || $rol == 'Agente' || $rol == "ADMISIÓN" || $rol == "COORDINADOR-CLINICA");

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
$rolesSinFiltroClinica = ['GERENTE-CLINICA', 'Administrador-clinica', 'CONTROL DE CITAS', 'ADMISIÓN', 'ATENCIÓN', 'COORDINADOR-CLINICA'];
$mostrarFiltroClinica = $isAdmin && !in_array($rol, $rolesSinFiltroClinica);

// Define roles that can access Atención Médica
$rolesAtencionMedica = ['superadmin', 'DIRECTOR-COMERCIALIZACIÓN', 'COORDINADOR-CLINICA', 'CONTROL DE CITAS', 'GERENTE-CLINICA', 'ADMISIÓN', 'ATENCIÓN'];

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
// Base URL with current clinic_id if present
$baseUrl = ['index'];
if ($clinica && $clinica->id !== null) {
    $baseUrl['clinica_id'] = $clinica->id;
}

// Total Afiliados - show all (no filter)
$urlTotalAfiliados = Url::to(array_merge($baseUrl, []));

// Contratos Activos
$urlActivos = Url::to(array_merge($baseUrl, [
    'UserDatosSearch' => ['contrato_estatus' => 'Activo']
]));

// Contratos Suspendidos
$urlSuspendidos = Url::to(array_merge($baseUrl, [
    'UserDatosSearch' => ['contrato_estatus' => 'Suspendido']
]));

// Contratos Registrados
$urlRegistrados = Url::to(array_merge($baseUrl, [
    'UserDatosSearch' => ['contrato_estatus' => 'Registrado']
]));

// Contratos Anulados
$urlAnulados = Url::to(array_merge($baseUrl, [
    'UserDatosSearch' => ['contrato_estatus' => 'Anulado']
]));

// Afiliados Críticos - Filter by 3+ expired cuotas
$urlCriticos = Url::to(array_merge($baseUrl, [
    'UserDatosSearch' => ['critical_filter' => 'true']
]));

// Get current filter for display
$currentFilter = Yii::$app->request->get('UserDatosSearch')['contrato_estatus'] ?? null;
$isCriticalFilter = Yii::$app->request->get('UserDatosSearch')['critical_filter'] ?? null;

// Define available status counts for conditional display
$hasActivos = ($summaryStats['total_contratos_activos'] ?? 0) > 0;
$hasSuspendidos = ($summaryStats['total_contratos_suspendidos'] ?? 0) > 0;
$hasRegistrados = ($summaryStats['total_contratos_registrados'] ?? 0) > 0;
$hasAnulados = ($summaryStats['total_contratos_anulados'] ?? 0) > 0;
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
    <!-- KPI SUMMARY CARDS - Clickable Microsoft Style (Single Row)    -->
    <!-- ============================================================ -->
    <div class="kpi-cards-row">
        <!-- Card 1: Total Afiliados (Always visible) -->
        <a href="<?= $urlTotalAfiliados ?>" class="kpi-card-link"
            data-toggle="tooltip"
            data-placement="top"
            data-html="true"
            title="<strong>Total de Afiliados</strong><br>Muestra todos los afiliados registrados en el sistema.<br><small class='text-muted'>Click para ver todos</small>">
            <div class="kpi-card primary">
                <div class="kpi-card-header">
                    <span class="kpi-icon"><i class="fas fa-users"></i></span>
                    <span class="kpi-title">Total Afiliados</span>
                    <span class="kpi-arrow"><i class="fas fa-chevron-right"></i></span>
                </div>
                <div class="kpi-card-body">
                    <span class="kpi-number"><?= number_format($summaryStats['total_afiliados'] ?? 0) ?></span>
                    <span class="kpi-label">
                        <i class="fas fa-building mr-1"></i> <?= $displayClinicName ?>
                    </span>
                </div>
            </div>
        </a>

        <!-- Card 2: Contratos Activos (Conditional) -->
        <?php if ($hasActivos): ?>
            <a href="<?= $urlActivos ?>" class="kpi-card-link"
                data-toggle="tooltip"
                data-placement="top"
                data-html="true"
                title="<strong>Contratos Activos</strong><br>Afiliados con contratos en vigencia.<br><small class='text-muted'>Click para filtrar</small>">
                <div class="kpi-card success">
                    <div class="kpi-card-header">
                        <span class="kpi-icon"><i class="fas fa-check-circle"></i></span>
                        <span class="kpi-title">Contratos Activos</span>
                        <span class="kpi-arrow"><i class="fas fa-chevron-right"></i></span>
                    </div>
                    <div class="kpi-card-body">
                        <span class="kpi-number"><?= number_format($summaryStats['total_contratos_activos'] ?? 0) ?></span>
                        <span class="kpi-label">
                            <i class="fas fa-calendar-check mr-1"></i> En vigencia
                        </span>
                    </div>
                </div>
            </a>
        <?php endif; ?>

        <!-- Card 3: Contratos Suspendidos (Conditional) -->
        <?php if ($hasSuspendidos): ?>
            <a href="<?= $urlSuspendidos ?>" class="kpi-card-link"
                data-toggle="tooltip"
                data-placement="top"
                data-html="true"
                title="<strong>Contratos Suspendidos</strong><br>Afiliados con contratos suspendidos por tener al menos una cuota vencida.<br><small class='text-muted'>Click para filtrar</small>">
                <div class="kpi-card danger">
                    <div class="kpi-card-header">
                        <span class="kpi-icon"><i class="fas fa-pause-circle"></i></span>
                        <span class="kpi-title">Contratos Suspendidos</span>
                        <span class="kpi-arrow"><i class="fas fa-chevron-right"></i></span>
                    </div>
                    <div class="kpi-card-body">
                        <span class="kpi-number"><?= number_format($summaryStats['total_contratos_suspendidos'] ?? 0) ?></span>
                        <span class="kpi-label">
                            <i class="fas fa-clock mr-1"></i> Con cuotas vencidas
                        </span>
                    </div>
                </div>
            </a>
        <?php endif; ?>

        <!-- Card 4: Contratos Registrados (Conditional) -->
        <?php if ($hasRegistrados): ?>
            <a href="<?= $urlRegistrados ?>" class="kpi-card-link"
                data-toggle="tooltip"
                data-placement="top"
                data-html="true"
                title="<strong>Contratos Registrados</strong><br>Afiliados con contratos registrados pendientes de activación.<br><small class='text-muted'>Click para filtrar</small>">
                <div class="kpi-card registrado">
                    <div class="kpi-card-header">
                        <span class="kpi-icon"><i class="fas fa-clipboard-list"></i></span>
                        <span class="kpi-title">Contratos Registrados</span>
                        <span class="kpi-arrow"><i class="fas fa-chevron-right"></i></span>
                    </div>
                    <div class="kpi-card-body">
                        <span class="kpi-number"><?= number_format($summaryStats['total_contratos_registrados'] ?? 0) ?></span>
                        <span class="kpi-label">
                            <i class="fas fa-clock mr-1"></i> Pendientes de activación
                        </span>
                    </div>
                </div>
            </a>
        <?php endif; ?>

        <!-- Card 5: Contratos Anulados (Conditional) -->
        <?php if ($hasAnulados): ?>
            <a href="<?= $urlAnulados ?>" class="kpi-card-link"
                data-toggle="tooltip"
                data-placement="top"
                data-html="true"
                title="<strong>Contratos Anulados</strong><br>Afiliados con contratos cancelados permanentemente.<br><small class='text-muted'>Click para filtrar</small>">
                <div class="kpi-card anulado">
                    <div class="kpi-card-header">
                        <span class="kpi-icon"><i class="fas fa-ban"></i></span>
                        <span class="kpi-title">Contratos Anulados</span>
                        <span class="kpi-arrow"><i class="fas fa-chevron-right"></i></span>
                    </div>
                    <div class="kpi-card-body">
                        <span class="kpi-number"><?= number_format($summaryStats['total_contratos_anulados'] ?? 0) ?></span>
                        <span class="kpi-label">
                            <i class="fas fa-times-circle mr-1"></i> Cancelados
                        </span>
                    </div>
                </div>
            </a>
        <?php endif; ?>

        <!-- Card 6: Afiliados Críticos (Conditional) -->
        <?php if ($hasCriticos): ?>
            <a href="<?= $urlCriticos ?>" class="kpi-card-link"
                data-toggle="tooltip"
                data-placement="top"
                data-html="true"
                title="<strong>Afiliados Críticos</strong><br>Afiliados con 3 o más cuotas vencidas. <span class='text-warning'>¡Requieren atención inmediata!</span><br><small class='text-muted'>Click para filtrar</small>">
                <div class="kpi-card critical">
                    <div class="kpi-card-header">
                        <span class="kpi-icon"><i class="fas fa-exclamation-triangle"></i></span>
                        <span class="kpi-title">Afiliados Críticos</span>
                        <span class="kpi-arrow"><i class="fas fa-chevron-right"></i></span>
                    </div>
                    <div class="kpi-card-body">
                        <span class="kpi-number">
                            <?= number_format($summaryStats['critical_delinquency']['count'] ?? 0) ?>
                            <?php if (($summaryStats['critical_delinquency']['count'] ?? 0) > 0): ?>
                                <small class="kpi-percent">
                                    (<?= round(($summaryStats['critical_delinquency']['count'] / max($summaryStats['total_contratos_suspendidos'] ?? 1, 1)) * 100) ?>%)
                                </small>
                            <?php endif; ?>
                        </span>
                        <span class="kpi-label">
                            <i class="fas fa-exclamation-circle mr-1"></i> 3+ cuotas vencidas
                            <?php if (($summaryStats['critical_delinquency']['total_vencidas_cuotas'] ?? 0) > 0): ?>
                                <span class="kpi-sub">
                                    <?= number_format($summaryStats['critical_delinquency']['total_vencidas_cuotas'] ?? 0) ?> cuotas
                                </span>
                            <?php endif; ?>
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
                // Show active filter indicator
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

            <div class="top-native-scrollbar" style="display: none;">
                <div class="top-native-dummy"></div>
            </div>

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
                            'attribute' => 'nombres',
                            'value' => function ($model) {
                                return $model->nombres . ' ' . $model->apellidos;
                            },
                            'format' => 'ntext',
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'filterInputOptions' => [
                                'placeholder' => 'Buscar nombre',
                                'class' => 'form-control text-center',
                            ],
                        ],
                        [
                            'label' => 'Cédula Identidad',
                            'attribute' => 'cedula',
                            'value' => function ($model) {
                                $tipoCedula = $model->tipo_cedula ?? '';
                                $numeroCedula = $model->cedula ?? '';
                                $consecutivo = '';

                                if (isset($model->consecutivo_menor) && $model->consecutivo_menor !== null && $model->consecutivo_menor !== '') {
                                    $consecutivo = str_pad((int)$model->consecutivo_menor, 2, '0', STR_PAD_LEFT);
                                }

                                if ($tipoCedula === 'Menor Sin Cédula') {
                                    if (!empty($consecutivo)) {
                                        return '<div class="cedula-grande-container menor-sin-cedula"><div class="cedula-grande-text">SIN CÉDULA-' . $numeroCedula . '-<span class="consecutivo-grande">' . $consecutivo . '</span></div></div>';
                                    } else {
                                        return '<div class="cedula-grande-container menor-sin-cedula"><div class="cedula-grande-text">SIN CÉDULA-' . $numeroCedula . '</div></div>';
                                    }
                                }

                                if (!empty($numeroCedula) && !empty($tipoCedula) && $tipoCedula !== 'Menor Sin Cédula') {
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

                                if (!empty($tipoCedula) && empty($numeroCedula) && $tipoCedula !== 'Menor Sin Cédula') {
                                    return '<div class="cedula-grande-container cedula-pendiente"><div class="cedula-grande-text">' . $tipoCedula . '</div><div class="estado-etiqueta">PENDIENTE DE NÚMERO</div></div>';
                                }

                                return '<div class="cedula-grande-container sin-cedula"><div class="cedula-grande-text">SIN REGISTRO</div></div>';
                            },
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'options' => ['style' => 'width: 120px; min-width:120px;'],
                            'contentOptions' => function ($model) {
                                $tipo = $model->tipo_cedula ?? '';
                                if ($tipo === 'Menor Sin Cédula') {
                                    return ['class' => 'text-center bg-menor-sin-cedula'];
                                }
                                if (!empty($model->cedula)) {
                                    return ['class' => 'text-center bg-cedula-normal'];
                                }
                                return ['class' => 'text-center'];
                            },
                            'filterInputOptions' => [
                                'placeholder' => 'Buscar cédula',
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
                            'template' => '<div class="d-flex justify-content-center gap-0">{view}{update}{atencion}{pagos}</div>',
                            'options' => ['style' => 'width:55px; min-width:55px;'],
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
                                            'class' => 'btn-action view'
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
                                                'class' => 'btn-action view'
                                            ]
                                        );
                                    } else {
                                        return "";
                                    }
                                },
                                'atencion' => function ($url, $model, $key) use ($rolesAtencionMedica, $clinica, $rol) {
                                    if (in_array($rol, $rolesAtencionMedica) && $model->clinica_id) {
                                        $urlSiniestro = Url::to(['/sis-siniestro/index', 'user_id' => $model->id, 'modo' => 'siniestro', 'clinica_id' => $clinica ? $clinica->id : null]);
                                        $urlCita = Url::to(['/sis-siniestro/index', 'user_id' => $model->id, 'modo' => 'cita', 'clinica_id' => $clinica ? $clinica->id : null]);

                                        return Html::a(
                                            '<i class="fas fa-heartbeat" style="color: red;"></i>',
                                            '#',
                                            [
                                                'title' => 'Gestionar Atención',
                                                'class' => 'btn-action view atencion-btn',
                                                'data' => [
                                                    'url-siniestro' => $urlSiniestro,
                                                    'url-cita' => $urlCita,
                                                ],
                                            ]
                                        );
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
                                                'class' => 'btn-action view'
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
                                                'class' => 'btn-action view'
                                            ]
                                        );
                                    }
                                },
                            ],
                        ],
                    ],
                ]); ?>
            </div>
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
        // Initialize tooltips for KPI cards
        $('.kpi-card-link').tooltip('dispose');
        $('[data-toggle="tooltip"]').tooltip('dispose');
        
        try {
            // Tooltips for KPI cards with custom styling
            $('.kpi-card-link').tooltip({
                trigger: 'hover',
                delay: { "show": 200, "hide": 100 },
                container: 'body',
                boundary: 'window',
                html: true,
                template: '<div class="tooltip kpi-tooltip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
            });
            
            // Tooltips for other elements
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
    
    // Initial tooltip setup
    initAllTooltips();
    
    // Re-initialize after grid updates
    $(document).on('pjax:complete yiiGridViewUpdated', function() {
        setTimeout(initAllTooltips, 100);
    });
    
    // Contract tooltip click handler
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

// 4. DUAL SCROLLBAR SYSTEM
$(document).ready(function() {
    var $gridWrapper = $('#grid-scroll-container');
    var $top = $('.top-native-scrollbar');
    var $dummy = $('.top-native-dummy');
    var $activeScroller = $();
    var syncingFrom = null;

    function getActiveGridScroller() {
        var $candidates = $gridWrapper.find('*').addBack();
        var best = null;
        var bestWidth = 0;
        $candidates.each(function() {
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
        var topEl = $top.get(0);
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
        $gridWrapper.find('table').each(function() {
            var width = this.scrollWidth;
            if (width > maxWidth) maxWidth = width;
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
        var scrollWidth = getMaxScrollWidth();
        var viewportWidth = gridEl.clientWidth;
        var hasHorizontalScroll = scrollWidth > (viewportWidth + 1);
        if (hasHorizontalScroll) {
            $dummy.css('width', (scrollWidth + 20) + 'px');
            $top.show();
            setTimeout(function() {
                var maxScroll = scrollWidth - $top[0].clientWidth;
                if ($top[0].scrollLeft > maxScroll) {
                    $top[0].scrollLeft = maxScroll;
                }
            }, 20);
            syncBars('grid');
        } else {
            $top.hide();
        }
    }

    $top.off('scroll.dual').on('scroll.dual', function() {
        syncBars('top');
    });

    function bindGridScroll() {
        $gridWrapper.find('*').off('scroll.dual-grid');
        $gridWrapper.off('scroll.dual-grid');
        $activeScroller = getActiveGridScroller();
        $activeScroller.off('scroll.dual-grid').on('scroll.dual-grid', function() {
            syncBars('grid');
        });
    }

    bindGridScroll();
    refreshTopBar();
    setTimeout(refreshTopBar, 120);
    setTimeout(refreshTopBar, 320);

    $(window).off('resize.dual').on('resize.dual', refreshTopBar);
    $(document).on('pjax:complete yiiGridViewUpdated ajaxComplete', function() {
        setTimeout(function() {
            bindGridScroll();
            refreshTopBar();
        }, 150);
    });
});

// Click tracking for debugging
$(document).on('click', '.kpi-card-link', function(e) {
    console.log('KPI Card clicked - URL:', $(this).attr('href'));
});
JS;

$this->registerJs($js, View::POS_READY);
?>

<style>
    /* ============================================
       TOOLTIP CUSTOM STYLES
       ============================================ */
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

    /* ============================================
       KPI CARDS - Clickable Microsoft Style (Single Row)
       ============================================ */
    .kpi-cards-row {
        display: flex;
        flex-wrap: nowrap;
        gap: 12px;
        margin-bottom: 24px;
        overflow-x: auto;
        padding: 2px 0 8px 0;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }

    .kpi-cards-row::-webkit-scrollbar {
        height: 4px;
    }

    .kpi-cards-row::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .kpi-cards-row::-webkit-scrollbar-thumb {
        background: #c1c7cd;
        border-radius: 4px;
    }

    .kpi-cards-row::-webkit-scrollbar-thumb:hover {
        background: #a0a8b0;
    }

    .kpi-card-link {
        flex: 1 1 0;
        min-width: 0;
        max-width: none;
        text-decoration: none !important;
        display: block;
        transition: transform 0.2s ease;
        cursor: pointer;
    }

    .kpi-card-link:hover {
        transform: translateY(-3px);
        text-decoration: none !important;
    }

    .kpi-card-link:hover .kpi-card {
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        border-color: rgba(255, 255, 255, 0.3);
    }

    .kpi-card {
        background: #ffffff;
        border-radius: 10px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
        overflow: hidden;
        transition: all 0.25s ease;
        border: 1px solid #e8eaed;
        position: relative;
        min-height: 90px;
        height: 100%;
        width: 100%;
    }

    .kpi-card:hover {
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        border-color: rgba(255, 255, 255, 0.3);
    }

    .kpi-card-header {
        padding: 10px 14px 4px 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        position: relative;
    }

    .kpi-card-header .kpi-icon {
        font-size: 1rem;
        opacity: 0.9;
        line-height: 1;
        display: flex;
        align-items: center;
        flex-shrink: 0;
    }

    .kpi-card-header .kpi-title {
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        opacity: 0.9;
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .kpi-card-header .kpi-arrow {
        font-size: 0.7rem;
        opacity: 0.5;
        transition: all 0.3s ease;
        margin-left: auto;
        flex-shrink: 0;
    }

    .kpi-card-link:hover .kpi-arrow {
        opacity: 1;
        transform: translateX(3px);
    }

    .kpi-card-body {
        padding: 6px 14px 12px 14px;
        display: flex;
        flex-direction: column;
    }

    .kpi-card-body .kpi-number {
        font-size: 1.8rem;
        font-weight: 700;
        line-height: 1.2;
        margin: 0;
        letter-spacing: -0.5px;
        white-space: nowrap;
    }

    .kpi-card-body .kpi-label {
        font-size: 0.65rem;
        opacity: 0.85;
        margin-top: 2px;
        display: flex;
        align-items: center;
        gap: 4px;
        flex-wrap: wrap;
        white-space: nowrap;
    }

    .kpi-card-body .kpi-percent {
        font-size: 0.85rem;
        font-weight: 500;
        opacity: 0.75;
        margin-left: 2px;
    }

    .kpi-card-body .kpi-sub {
        display: block;
        font-size: 0.6rem;
        opacity: 0.75;
        margin-top: 1px;
        white-space: nowrap;
    }

    /* ============================================
       CARD COLOR SCHEMES - MATCHING BADGE COLORS
       ============================================ */

    /* Total Afiliados - Primary Blue (like badge-primary) */
    .kpi-card.primary {
        background: linear-gradient(145deg, #007bff 0%, #0069d9 100%);
        color: white;
        border-color: #007bff;
    }

    .kpi-card.primary .kpi-number,
    .kpi-card.primary .kpi-label,
    .kpi-card.primary .kpi-title,
    .kpi-card.primary .kpi-icon,
    .kpi-card.primary .kpi-arrow {
        color: #ffffff !important;
    }

    /* Registrado - Primary Blue (same as badge-primary) */
    .kpi-card.registrado {
        background: linear-gradient(145deg, #007bff 0%, #0069d9 100%);
        color: white;
        border-color: #007bff;
    }

    .kpi-card.registrado .kpi-number,
    .kpi-card.registrado .kpi-label,
    .kpi-card.registrado .kpi-title,
    .kpi-card.registrado .kpi-icon,
    .kpi-card.registrado .kpi-arrow {
        color: #ffffff !important;
    }

    /* Activo - Success Green (same as badge-success) */
    .kpi-card.success {
        background: linear-gradient(145deg, #28a745 0%, #218838 100%);
        color: white;
        border-color: #28a745;
    }

    .kpi-card.success .kpi-number,
    .kpi-card.success .kpi-label,
    .kpi-card.success .kpi-title,
    .kpi-card.success .kpi-icon,
    .kpi-card.success .kpi-arrow {
        color: #ffffff !important;
    }

    /* Suspendido - Secondary Gray (same as badge-secondary) */
    .kpi-card.danger {
        background: linear-gradient(145deg, #6c757d 0%, #5a6268 100%);
        color: white;
        border-color: #6c757d;
    }

    .kpi-card.danger .kpi-number,
    .kpi-card.danger .kpi-label,
    .kpi-card.danger .kpi-title,
    .kpi-card.danger .kpi-icon,
    .kpi-card.danger .kpi-arrow {
        color: #ffffff !important;
    }

    /* Anulado - Danger Red (same as badge-danger) */
    .kpi-card.anulado {
        background: linear-gradient(145deg, #dc3545 0%, #c82333 100%);
        color: white;
        border-color: #dc3545;
    }

    .kpi-card.anulado .kpi-number,
    .kpi-card.anulado .kpi-label,
    .kpi-card.anulado .kpi-title,
    .kpi-card.anulado .kpi-icon,
    .kpi-card.anulado .kpi-arrow {
        color: #ffffff !important;
    }

    /* Críticos - Warning Orange (same as badge-warning) */
    .kpi-card.critical {
        background: linear-gradient(145deg, #ffc107 0%, #e0a800 100%);
        color: #212529;
        border-color: #ffc107;
    }

    .kpi-card.critical .kpi-number,
    .kpi-card.critical .kpi-label,
    .kpi-card.critical .kpi-title,
    .kpi-card.critical .kpi-icon,
    .kpi-card.critical .kpi-arrow {
        color: #212529 !important;
    }

    .kpi-card.critical .kpi-percent {
        color: #856404 !important;
    }

    /* Critical badge for filter indicator */
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

    /* Filter indicator badge in header */
    .section-title .badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.75rem;
        vertical-align: middle;
    }

    /* Responsive - Single Row with horizontal scroll on smaller screens */
    @media (max-width: 1200px) {
        .kpi-cards-row {
            gap: 10px;
        }

        .kpi-card {
            min-height: 80px;
        }

        .kpi-card-body .kpi-number {
            font-size: 1.6rem;
        }

        .kpi-card-header .kpi-title {
            font-size: 0.65rem;
        }

        .kpi-card-header .kpi-icon {
            font-size: 0.9rem;
        }
    }

    @media (max-width: 992px) {
        .kpi-cards-row {
            gap: 8px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            padding-bottom: 8px;
        }

        .kpi-card-link {
            flex: 0 0 160px;
            min-width: 140px;
        }

        .kpi-card {
            min-height: 75px;
        }

        .kpi-card-header {
            padding: 8px 10px 3px 10px;
        }

        .kpi-card-header .kpi-title {
            font-size: 0.6rem;
        }

        .kpi-card-header .kpi-icon {
            font-size: 0.85rem;
        }

        .kpi-card-body {
            padding: 4px 10px 10px 10px;
        }

        .kpi-card-body .kpi-number {
            font-size: 1.4rem;
        }

        .kpi-card-body .kpi-label {
            font-size: 0.6rem;
        }

        .kpi-card-body .kpi-percent {
            font-size: 0.7rem;
        }

        .kpi-card-body .kpi-sub {
            font-size: 0.55rem;
        }
    }

    @media (max-width: 576px) {
        .kpi-cards-row {
            gap: 6px;
        }

        .kpi-card-link {
            flex: 0 0 130px;
            min-width: 110px;
        }

        .kpi-card {
            min-height: 65px;
        }

        .kpi-card-header {
            padding: 6px 8px 2px 8px;
        }

        .kpi-card-header .kpi-title {
            font-size: 0.5rem;
            letter-spacing: 0.2px;
        }

        .kpi-card-header .kpi-icon {
            font-size: 0.75rem;
        }

        .kpi-card-header .kpi-arrow {
            font-size: 0.6rem;
        }

        .kpi-card-body {
            padding: 3px 8px 8px 8px;
        }

        .kpi-card-body .kpi-number {
            font-size: 1.2rem;
        }

        .kpi-card-body .kpi-label {
            font-size: 0.5rem;
            white-space: normal;
        }

        .kpi-card-body .kpi-percent {
            font-size: 0.65rem;
        }

        .kpi-card-body .kpi-sub {
            font-size: 0.5rem;
            white-space: normal;
        }
    }

    /* ============================================
       CONTRACT STATUS BADGE STYLES - STANDARDIZED
       ============================================ */
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

    /* Registrado / Primary */
    .badge-primary {
        background: #007bff;
        color: #fff;
        border-color: #007bff;
    }

    /* Activo / Success */
    .badge-success {
        background: #28a745;
        color: #fff;
        border-color: #28a745;
    }

    /* Anulado / Danger */
    .badge-danger {
        background: #dc3545;
        color: #fff;
        border-color: #dc3545;
    }

    /* Crítico / Warning */
    .badge-warning {
        background: #ffc107;
        color: #212529;
        border-color: #ffc107;
    }

    /* Suspendido / Secondary */
    .badge-secondary {
        background: #6c757d;
        color: #fff;
        border-color: #6c757d;
    }

    /* Pendiente / Info */
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

    /* ============================================
       CÉDULA DE IDENTIDAD STYLES
       ============================================ */
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

    /* ============================================
       CORPORATIVO BADGE STYLES
       ============================================ */
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

    /* ============================================
       DUAL SCROLLBAR SYSTEM
       ============================================ */
    .table-responsive-scroll {
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
    }

    .top-native-scrollbar {
        width: 100%;
        height: 12px;
        overflow-x: scroll;
        overflow-y: hidden;
        margin-bottom: 10px;
        display: none;
        cursor: pointer;
        box-sizing: border-box;
        -webkit-overflow-scrolling: touch;
    }

    .top-native-scrollbar .top-native-dummy {
        display: inline-block;
        width: 100%;
        min-height: 1px;
        height: 1px;
    }

    .top-native-scrollbar::-webkit-scrollbar,
    .table-responsive-scroll::-webkit-scrollbar {
        height: 12px;
    }

    .top-native-scrollbar::-webkit-scrollbar-track,
    .table-responsive-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 6px;
    }

    .top-native-scrollbar::-webkit-scrollbar-thumb,
    .table-responsive-scroll::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 6px;
    }

    .top-native-scrollbar::-webkit-scrollbar-thumb:hover,
    .table-responsive-scroll::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Ensure proper centering */
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

    /* Responsive */
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
</style>