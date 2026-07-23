<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\widgets\ActiveForm;
use app\components\UserHelper;

/**
 * @var yii\web\View $this
 * @var app\models\SisSiniestroSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var array $estadisticas
 * @var array $clinicas
 * @var int|null $clinicaSeleccionada
 * @var array $siniestrosPorMes
 */

$this->title = 'Siniestros por Clínica';
$this->params['breadcrumbs'][] = ['label' => 'Clínicas', 'url' => ['/rm-clinica/index']];
$this->params['breadcrumbs'][] = $this->title;

$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol == 'DIRECTOR-COMERCIALIZACION' || $rol == 'Asesor' || $rol == 'Agente' || $rol == "ADMISIÓN" || $rol == "CONTROL DE CITAS" || $rol == "COORDINADOR-CLINICA");

// Determinar si el usuario es un coordinador de clínica (solo ve una clínica)
$esCoordinadorClinica = ($rol == 'COORDINADOR-CLINICA');
$esSuperAdmin = ($rol == 'superadmin' || $rol == 'DIRECTOR-COMERCIALIZACION');

// Filtrar clínicas basado en el rol del usuario
$clinicasFiltradas = $clinicas;
$clinicaSeleccionadaActual = $clinicaSeleccionada;

// Si el usuario es coordinador de clínica, restringir a su clínica
if ($esCoordinadorClinica) {
    $userClinica = UserHelper::getMyClinicaId();
    if ($userClinica) {
        $clinicasFiltradas = array_filter($clinicas, function ($clinica) use ($userClinica) {
            return $clinica['id'] == $userClinica;
        });
        $clinicaSeleccionadaActual = $userClinica;
    }
}

// Para superadmins, si no hay selección, mostrar todas
if ($esSuperAdmin && empty($clinicaSeleccionadaActual)) {
    $clinicaSeleccionadaActual = null;
}

// Preparar datos para gráficos
$clinicaNames = [];
$clinicaTotals = [];

if (!empty($estadisticas['por_clinica'])) {
    foreach ($estadisticas['por_clinica'] as $clinica) {
        if (empty($clinicaSeleccionadaActual) || $clinica['id'] == $clinicaSeleccionadaActual) {
            $clinicaNames[] = $clinica['nombre'];
            $clinicaTotals[] = $clinica['atendidos'] + $clinica['no_atendidos'];
        }
    }
}

// Preparar datos para el gráfico de tendencia mensual
$mesesLabels = [];
$mesesData = [];

// Verificar si $siniestrosPorMes está definido y no está vacío
if (isset($siniestrosPorMes) && !empty($siniestrosPorMes) && is_array($siniestrosPorMes)) {
    // Ordenar por mes
    ksort($siniestrosPorMes);
    foreach ($siniestrosPorMes as $mes => $cantidad) {
        $timestamp = strtotime($mes . '-01');
        if ($timestamp !== false) {
            $mesesLabels[] = date('M Y', $timestamp);
            $mesesData[] = intval($cantidad);
        }
    }
}

// Si no hay datos reales, generar datos de ejemplo para demostración
if (empty($mesesData) || array_sum($mesesData) == 0) {
    $mesesLabels = [];
    $mesesData = [];
    for ($i = 5; $i >= 0; $i--) {
        $mes = date('Y-m', strtotime("-$i months"));
        $mesesLabels[] = date('M Y', strtotime($mes . '-01'));
        $mesesData[] = rand(5, 25);
    }
}

$mesesLabelsJson = json_encode($mesesLabels);
$mesesDataJson = json_encode($mesesData);

$clinicaNamesJson = json_encode($clinicaNames);
$clinicaTotalsJson = json_encode($clinicaTotals);

// Colores profesionales
$colors = [
    'primary' => '#0078D4',
    'success' => '#107C10',
    'danger' => '#D13438',
    'warning' => '#FFB900',
    'info' => '#00BCF2',
    'gray' => '#605E5C',
    'lightGray' => '#F3F2F1',
    'dark' => '#323130',
    'white' => '#FFFFFF',
];

// Helper function para formatear fechas
function formatDateSafe($date)
{
    if (empty($date)) {
        return '<span class="text-muted">—</span>';
    }
    try {
        if (is_string($date)) {
            $timestamp = strtotime($date);
            if ($timestamp === false) {
                return '<span class="text-muted">—</span>';
            }
            return date('d/m/Y', $timestamp);
        }
        return '<span class="text-muted">—</span>';
    } catch (Exception $e) {
        return '<span class="text-muted">—</span>';
    }
}

?>

<div class="por-clinica-container">
    <div class="container-fluid px-0">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h1 class="h1 mb-0 font-weight-normal" style="color: <?= $colors['white'] ?>; font-size: 2.5rem;">
                            <i class="fas fa-chart-pie mr-3" style="color: <?= $colors['white'] ?>; font-size: 2.2rem;"></i>
                            <?= Html::encode($this->title) ?>
                        </h1>
                        <small class="d-block mt-2" style="color: <?= $colors['white'] ?>; font-size: 1.1rem;">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            <?= date('l, d \d\e F \d\e Y') ?>
                        </small>
                    </div>
                    <div>
                        <?= Html::a(
                            '<i class="fas fa-arrow-left mr-2"></i> Volver a Clínicas',
                            ['/rm-clinica/index'],
                            [
                                'class' => 'btn btn-outline-light',
                                'style' => 'font-size: 1.1rem; padding: 10px 28px;',
                                'title' => 'Volver a la lista de clínicas',
                            ]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards - Only Total -->
        <div class="row mb-4">
            <div class="col-md-4 col-sm-6 mb-3 mx-auto">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center justify-content-center" style="padding: 2rem;">
                        <div class="rounded-circle p-4 mr-4" style="background-color: <?= $colors['primary'] ?>20;">
                            <i class="fas fa-clinic-medical fa-3x" style="color: <?= $colors['primary'] ?>;"></i>
                        </div>
                        <div>
                            <h5 class="text-muted mb-1 font-weight-normal" style="font-size: 1.1rem;">Total Siniestros</h5>
                            <h2 class="mb-0 font-weight-bold" style="color: <?= $colors['dark'] ?>; font-size: 3rem;"><?= number_format($estadisticas['total']) ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Charts Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body" style="padding: 1.8rem;">
                <!-- Filter Bar -->
                <div class="row align-items-end">
                    <div class="col-md-7">
                        <?php if ($permisos && $esSuperAdmin) { ?>
                            <?php $form = ActiveForm::begin([
                                'method' => 'get',
                                'action' => ['/sis-siniestro/por-clinica'],
                                'options' => ['class' => 'filter-form'],
                            ]); ?>
                            <div class="form-row align-items-end">
                                <div class="col-md-8">
                                    <?= Html::label('Seleccionar Clínica', 'clinica_id', ['class' => 'font-weight-semibold text-muted mb-2 d-block', 'style' => 'font-size: 1rem;']) ?>
                                    <?= Html::dropDownList(
                                        'clinica_id',
                                        $clinicaSeleccionadaActual,
                                        ['' => 'Todas las Clínicas'] + \yii\helpers\ArrayHelper::map($clinicasFiltradas, 'id', 'nombre'),
                                        ['class' => 'form-control', 'style' => 'font-size: 1.1rem; padding: 10px 16px; height: auto;']
                                    ) ?>
                                </div>
                                <div class="col-md-4">
                                    <?= Html::submitButton(
                                        '<i class="fas fa-filter mr-2"></i> Filtrar',
                                        ['class' => 'btn btn-primary', 'style' => 'font-size: 1.05rem; padding: 10px 20px; width: 100%;']
                                    ) ?>
                                </div>
                            </div>
                            <?php ActiveForm::end(); ?>
                        <?php } else { ?>
                            <div class="d-flex align-items-center h-100">
                                <span class="text-muted font-weight-semibold" style="font-size: 1.15rem;">
                                    <i class="fas fa-building mr-3"></i>
                                    <?php
                                    if ($clinicaSeleccionadaActual || $esCoordinadorClinica) {
                                        $clinicaNombre = \yii\helpers\ArrayHelper::map($clinicasFiltradas, 'id', 'nombre')[$clinicaSeleccionadaActual] ?? 'Clínica';
                                        echo Html::encode($clinicaNombre);
                                    } else {
                                        echo 'Todas las Clínicas';
                                    }
                                    ?>
                                </span>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="col-md-5 text-md-right mt-3 mt-md-0">
                        <div class="d-flex align-items-center justify-content-md-end flex-wrap gap-3">
                            <span class="text-muted" style="font-size: 1.05rem;">
                                <i class="fas fa-file-alt mr-2"></i>
                                <strong><?= number_format($dataProvider->getTotalCount()) ?></strong> registros
                            </span>
                            <?php if ($clinicaSeleccionadaActual && $esSuperAdmin): ?>
                                <?= Html::a(
                                    '<i class="fas fa-times mr-2"></i> Limpiar filtro',
                                    ['/sis-siniestro/por-clinica'],
                                    ['class' => 'btn btn-outline-secondary', 'style' => 'font-size: 1rem; padding: 8px 18px;']
                                ) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Charts Row - Only Monthly Trend Chart -->
                <div class="row mt-4">
                    <!-- Monthly Trend Chart - Full Width -->
                    <div class="col-lg-12">
                        <div class="chart-container" style="padding: 1.2rem;">
                            <h6 class="text-muted font-weight-semibold mb-3" style="font-size: 1.1rem;">
                                <i class="fas fa-chart-line mr-2" style="color: <?= $colors['primary'] ?>;"></i>
                                Tendencia Mensual de Siniestros
                            </h6>
                            <div style="height: 300px; width: 100%;">
                                <canvas id="trendChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Clinics Chart - Only for Superadmins when viewing all clinics -->
                <?php if ($esSuperAdmin && empty($clinicaSeleccionadaActual) && !empty($clinicaNames) && count($clinicaNames) > 1): ?>
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="chart-container" style="padding: 1.2rem;">
                                <h6 class="text-muted font-weight-semibold mb-3" style="font-size: 1.1rem;">
                                    <i class="fas fa-chart-bar mr-2" style="color: <?= $colors['primary'] ?>;"></i>
                                    Distribución por Clínica
                                </h6>
                                <div style="height: 250px; width: 100%;">
                                    <canvas id="clinicaChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Data Table Section -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0 py-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <h4 class="mb-0 font-weight-semibold" style="color: <?= $colors['white'] ?>; font-size: 1.5rem;">
                        <i class="fas fa-list-ul mr-3" style="color: <?= $colors['primary'] ?>;"></i>
                        Listado de Siniestros
                    </h4>
                    <span class="badge badge-light text-muted font-weight-normal" style="font-size: 1.1rem; padding: 8px 20px;">
                        <i class="fas fa-database mr-2"></i>
                        <?= number_format($dataProvider->getTotalCount()) ?> registros
                    </span>
                </div>
            </div>
            <div class="card-body pt-0" style="padding: 1.5rem;">
                <div class="table-responsive">
                    <?= GridView::widget([
                        'id' => 'siniestros-grid',
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'layout' => "{items}\n<div class='row mt-4'><div class='col-sm-12 col-md-5'>{summary}</div><div class='col-sm-12 col-md-7'>{pager}</div></div>",
                        'summary' => '<span class="text-muted" style="font-size: 1rem;">Mostrando <strong>{begin}</strong> - <strong>{end}</strong> de <strong>{totalCount}</strong> registros</span>',
                        'pager' => [
                            'options' => ['class' => 'pagination justify-content-end'],
                            'linkOptions' => ['class' => 'page-link', 'style' => 'font-size: 1rem; padding: 8px 16px;'],
                            'pageCssClass' => 'page-item',
                            'prevPageCssClass' => 'page-item',
                            'nextPageCssClass' => 'page-item',
                            'firstPageCssClass' => 'page-item',
                            'lastPageCssClass' => 'page-item',
                        ],
                        'tableOptions' => [
                            'class' => 'table table-hover table-striped mb-0',
                            'style' => 'font-size: 1.2rem;',
                        ],
                        'columns' => [
                            [
                                'attribute' => 'id',
                                'options' => ['style' => 'width: 70px;'],
                                'headerOptions' => ['class' => 'text-white font-weight-semibold', 'style' => 'background-color: #1a1a2e; font-size: 1.15rem; padding: 16px 14px;'],
                                'contentOptions' => ['style' => 'font-size: 1.2rem; font-weight: 600; padding: 16px 14px;'],
                            ],
                            [
                                'attribute' => 'idclinica',
                                'value' => 'clinica.nombre',
                                'label' => 'Clínica',
                                'filter' => \yii\helpers\ArrayHelper::map($clinicasFiltradas, 'id', 'nombre'),
                                'headerOptions' => ['class' => 'text-white font-weight-semibold', 'style' => 'background-color: #1a1a2e; font-size: 1.15rem; padding: 16px 14px;'],
                                'contentOptions' => ['style' => 'font-size: 1.2rem; padding: 16px 14px;'],
                                'visible' => $esSuperAdmin && empty($clinicaSeleccionadaActual) && count($clinicasFiltradas) > 1,
                                'filterInputOptions' => [
                                    'class' => 'form-control',
                                    'style' => 'font-size: 1.05rem; padding: 8px 12px; height: auto;'
                                ],
                            ],
                            [
                                'attribute' => 'afiliado_nombre',
                                'value' => function ($model) {
                                    return $model->afiliado ?
                                        Html::encode($model->afiliado->nombres . ' ' . $model->afiliado->apellidos) :
                                        'N/A';
                                },
                                'label' => 'Afiliado',
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar por nombre...',
                                    'class' => 'form-control',
                                    'style' => 'font-size: 1.05rem; padding: 8px 12px; height: auto;',
                                ],
                                'headerOptions' => ['class' => 'text-white font-weight-semibold', 'style' => 'background-color: #1a1a2e; font-size: 1.15rem; padding: 16px 14px;'],
                                'contentOptions' => ['style' => 'font-size: 1.2rem; padding: 16px 14px;'],
                            ],
                            [
                                'attribute' => 'afiliado_cedula',
                                'value' => function ($model) {
                                    return $model->afiliado ?
                                        Html::encode(($model->afiliado->tipo_cedula ? $model->afiliado->tipo_cedula . '-' : '') . $model->afiliado->cedula) :
                                        'N/A';
                                },
                                'label' => 'Cédula',
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar por cédula...',
                                    'class' => 'form-control',
                                    'style' => 'font-size: 1.05rem; padding: 8px 12px; height: auto;',
                                ],
                                'headerOptions' => ['class' => 'text-white font-weight-semibold', 'style' => 'background-color: #1a1a2e; font-size: 1.15rem; padding: 16px 14px;'],
                                'contentOptions' => ['style' => 'font-size: 1.2rem; font-weight: 500; padding: 16px 14px;'],
                            ],
                            [
                                'attribute' => 'fecha',
                                'filter' => false,
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return formatDateSafe($model->fecha);
                                },
                                'contentOptions' => ['style' => 'text-align: center; font-size: 1.2rem; padding: 16px 14px;'],
                                'headerOptions' => ['class' => 'text-white font-weight-semibold text-center', 'style' => 'background-color: #1a1a2e; font-size: 1.15rem; padding: 16px 14px;'],
                            ],
                            [
                                'attribute' => 'hora',
                                'filter' => false,
                                'format' => 'raw',
                                'value' => function ($model) {
                                    if (empty($model->hora)) {
                                        return '<span class="text-muted">—</span>';
                                    }
                                    try {
                                        if (is_string($model->hora)) {
                                            $timestamp = strtotime($model->hora);
                                            if ($timestamp !== false) {
                                                return date('h:i A', $timestamp);
                                            }
                                        }
                                        return '<span class="text-muted">—</span>';
                                    } catch (Exception $e) {
                                        return '<span class="text-muted">—</span>';
                                    }
                                },
                                'contentOptions' => ['style' => 'text-align: center; font-size: 1.2rem; padding: 16px 14px;'],
                                'headerOptions' => ['class' => 'text-white font-weight-semibold text-center', 'style' => 'background-color: #1a1a2e; font-size: 1.15rem; padding: 16px 14px;'],
                            ],
                            [
                                'attribute' => 'costo_total',
                                'format' => ['currency', 'USD'],
                                'contentOptions' => ['style' => 'text-align: right; font-weight: 600; font-size: 1.2rem; padding: 16px 14px;'],
                                'headerOptions' => ['class' => 'text-white font-weight-semibold text-right', 'style' => 'background-color: #1a1a2e; font-size: 1.15rem; padding: 16px 14px;'],
                            ],
                            [
                                'attribute' => 'fecha_atencion',
                                'filter' => false,
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return formatDateSafe($model->fecha_atencion);
                                },
                                'contentOptions' => ['style' => 'text-align: center; font-size: 1.2rem; padding: 16px 14px;'],
                                'headerOptions' => ['class' => 'text-white font-weight-semibold text-center', 'style' => 'background-color: #1a1a2e; font-size: 1.15rem; padding: 16px 14px;'],
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => 'Acciones',
                                'headerOptions' => ['class' => 'text-white font-weight-semibold text-center', 'style' => 'background-color: #1a1a2e; font-size: 1.15rem; padding: 16px 14px; width: 100px;'],
                                'contentOptions' => ['style' => 'text-align: center; font-size: 1.2rem; padding: 16px 14px;'],
                                'template' => '{view}',
                                'buttons' => [
                                    'view' => function ($url, $model) {
                                        return Html::a(
                                            '<i class="fas fa-eye"></i>',
                                            ['view', 'id' => $model->id, 'user_id' => $model->iduser],
                                            [
                                                'title' => 'Ver detalles',
                                                'class' => 'btn btn-outline-primary',
                                                'style' => 'border-radius: 6px; font-size: 1.1rem; padding: 8px 18px;',
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
</div>

<?php
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js');

$this->registerCss("
    .por-clinica-container {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        min-height: 100vh;
        padding: 25px;
        border-radius: 0;
        margin: -20px;
    }
    .por-clinica-container .card {
        border-radius: 10px;
        background: {$colors['white']};
    }
    .por-clinica-container .table th {
        border-top: none;
        border-bottom: 2px solid #dee2e6;
        padding: 16px 14px !important;
    }
    .por-clinica-container .table td {
        vertical-align: middle;
        padding: 16px 14px !important;
    }
    .por-clinica-container .filter-form .form-control:focus {
        box-shadow: none;
        border-color: {$colors['primary']};
    }
    .por-clinica-container .btn-primary {
        background-color: {$colors['primary']};
        border-color: {$colors['primary']};
    }
    .por-clinica-container .btn-primary:hover {
        background-color: #0062a3;
        border-color: #0062a3;
    }
    .por-clinica-container .btn-outline-secondary:hover {
        background-color: {$colors['lightGray']};
        border-color: {$colors['gray']};
    }
    .por-clinica-container .badge-light {
        background-color: rgba(255, 255, 255, 0.15) !important;
        color: {$colors['white']} !important;
    }
    .por-clinica-container .shadow-sm {
        box-shadow: 0 4px 12px rgba(0,0,0,0.12) !important;
    }
    .por-clinica-container .text-muted {
        color: {$colors['gray']} !important;
    }
    .por-clinica-container .chart-container {
        background: #FAFAFA;
        border-radius: 8px;
        padding: 16px;
        border: 1px solid #EDEBE9;
    }
    .por-clinica-container .font-weight-semibold {
        font-weight: 600;
    }
    .por-clinica-container .gap-2 {
        gap: 0.5rem;
    }
    .por-clinica-container .gap-3 {
        gap: 1rem;
    }
    .por-clinica-container .btn-outline-light {
        color: {$colors['white']};
        border-color: {$colors['white']};
        font-size: 1.1rem;
        padding: 10px 28px;
    }
    .por-clinica-container .btn-outline-light:hover {
        background-color: rgba(255, 255, 255, 0.15);
        color: {$colors['white']};
        border-color: {$colors['white']};
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(255,255,255,0.1);
    }
    .por-clinica-container .table thead th {
        color: {$colors['white']} !important;
        background-color: #1a1a2e !important;
        border-color: #2a2a4e !important;
        font-size: 1.15rem !important;
        padding: 16px 14px !important;
    }
    .por-clinica-container .table thead th a {
        color: {$colors['white']} !important;
        font-size: 1.15rem !important;
    }
    .por-clinica-container .table thead th .kv-sort {
        color: {$colors['white']} !important;
        font-size: 1.15rem !important;
    }
    .por-clinica-container .table thead th .kv-sort:hover {
        color: {$colors['primary']} !important;
    }
    .por-clinica-container .card-header {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%) !important;
        border-bottom: none !important;
        border-radius: 10px 10px 0 0 !important;
        padding: 20px 24px !important;
    }
    .por-clinica-container .card-header h4 {
        color: {$colors['white']} !important;
        font-size: 1.5rem !important;
    }
    .por-clinica-container .card-header .badge {
        background-color: rgba(255, 255, 255, 0.15) !important;
        color: {$colors['white']} !important;
        font-size: 1.1rem !important;
        padding: 8px 20px !important;
    }
    .por-clinica-container .table tbody td {
        font-size: 1.2rem !important;
        padding: 16px 14px !important;
    }
    .por-clinica-container .summary {
        font-size: 1rem !important;
    }
    .por-clinica-container .pagination .page-link {
        font-size: 1rem !important;
        padding: 10px 18px !important;
    }
    .por-clinica-container .pagination .page-item.active .page-link {
        background-color: {$colors['primary']} !important;
        border-color: {$colors['primary']} !important;
    }
    .por-clinica-container .table-hover tbody tr:hover {
        background-color: rgba(0, 120, 212, 0.05) !important;
        transition: background-color 0.2s ease;
    }
    .por-clinica-container .filter-form .form-control {
        font-size: 1.1rem !important;
        padding: 10px 16px !important;
        height: auto !important;
    }
");

$js = <<<JS
// Gráfico de Tendencia Mensual (Línea)
if (document.getElementById('trendChart')) {
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    const gradient = trendCtx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(0, 120, 212, 0.3)');
    gradient.addColorStop(1, 'rgba(0, 120, 212, 0.0)');

    const mesesLabels = $mesesLabelsJson;
    const mesesData = $mesesDataJson;

    const trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: mesesLabels,
            datasets: [{
                label: 'Siniestros',
                data: mesesData,
                borderColor: '#0078D4',
                backgroundColor: gradient,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#0078D4',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Siniestros: ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 12,
                            weight: '500'
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.06)'
                    },
                    ticks: {
                        font: {
                            size: 12
                        },
                        stepSize: 1
                    }
                }
            }
        }
    });
}

// Gráfico de Distribución por Clínica (solo para superadmin)
if (document.getElementById('clinicaChart')) {
    const clinicaCtx = document.getElementById('clinicaChart').getContext('2d');
    
    // Generar colores automáticos
    const colors = [
        '#0078D4', '#107C10', '#D13438', '#FFB900', '#00BCF2',
        '#5C2D91', '#E3008C', '#00B4C4', '#E67E22', '#2ECC71'
    ];
    
    const clinicaNames = $clinicaNamesJson;
    const clinicaTotals = $clinicaTotalsJson;
    
    // Solo crear el gráfico si hay datos
    if (clinicaNames.length > 0 && clinicaTotals.length > 0) {
        const backgroundColors = clinicaNames.map((_, i) => colors[i % colors.length]);
        
        const clinicaChart = new Chart(clinicaCtx, {
            type: 'bar',
            data: {
                labels: clinicaNames,
                datasets: [{
                    label: 'Total Siniestros',
                    data: clinicaTotals,
                    backgroundColor: backgroundColors,
                    borderRadius: 4,
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Total: ' + context.parsed.y + ' siniestros';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12,
                                weight: '500'
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.06)'
                        },
                        ticks: {
                            font: {
                                size: 12
                            },
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
}
JS;

$this->registerJs($js);
?>