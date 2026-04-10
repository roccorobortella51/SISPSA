<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use app\models\rmclinica;

$this->title = 'Dashboard de Afiliados';
$this->params['breadcrumbs'][] = $this->title;

// Check user role for link permissions
$isSuperAdmin = Yii::$app->user->can('superadmin') || Yii::$app->user->can('admin');
$hasClinicAccess = UserHelper::hasClinicAccess();
$currentUserId = Yii::$app->user->id;

// Get accessible clinics based on user role
if ($hasClinicAccess) {
    // Users with clinic roles get their assigned clinics
    $userClinicas = UserHelper::getAccessibleClinicas();
    $userClinicaIds = ArrayHelper::getColumn($userClinicas, 'id');
} elseif ($isSuperAdmin) {
    // Superadmin can see all clinics
    $userClinicas = $clinicas;
    $userClinicaIds = ArrayHelper::getColumn($clinicas, 'id');
} else {
    // Other users - try to get their assigned clinics
    $userClinicas = UserHelper::getAccessibleClinicas();
    $userClinicaIds = ArrayHelper::getColumn($userClinicas, 'id');
}

// If user has no clinics assigned, prevent access
if (empty($userClinicaIds) && !$isSuperAdmin) {
    Yii::$app->session->setFlash('error', 'No tiene clínicas asignadas. Contacte al administrador.');
    return;
}

// Filter the initial clinic list for dropdown
$filteredClinicas = $isSuperAdmin ? $clinicas : $userClinicas;

// Set default selected clinics based on user's access
$defaultClinicaIds = [];
if (!$isSuperAdmin && !empty($userClinicaIds)) {
    // For non-superadmins, automatically select their assigned clinics
    $defaultClinicaIds = $userClinicaIds;
} elseif (empty($searchModel->clinica_ids)) {
    $defaultClinicaIds = $userClinicaIds;
} else {
    // If user submitted filters, respect them but ensure they only include accessible clinics
    $submittedClinicaIds = is_array($searchModel->clinica_ids) ? $searchModel->clinica_ids : [$searchModel->clinica_ids];
    $defaultClinicaIds = array_intersect($submittedClinicaIds, $userClinicaIds);
}

// Register Chart.js CDN
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

// Define plan colors for pie chart
$planColors = [
    'rgba(205, 127, 50, 0.8)',   // Bronze
    'rgba(192, 192, 192, 0.8)',   // Silver
    'rgba(255, 215, 0, 0.8)',     // Gold
    'rgba(80, 200, 120, 0.8)',    // Emerald
    'rgba(155, 89, 182, 0.8)',    // Purple
    'rgba(52, 152, 219, 0.8)',    // Blue
    'rgba(46, 204, 113, 0.8)',    // Green
    'rgba(241, 196, 15, 0.8)',    // Yellow
    'rgba(230, 126, 34, 0.8)',    // Orange
    'rgba(231, 76, 60, 0.8)',     // Red
];

// Calculate totals for the summary table
$totalAfiliados = 0;
$totalIndividual = 0;
$totalCorporativo = 0;
$totalActivos = 0;
$totalSuspendidos = 0;
$totalAnulados = 0;

foreach ($summaryByClinic as $clinic) {
    $totalAfiliados += $clinic['total_afiliados'];
    $totalIndividual += $clinic['tipo_individual'];
    $totalCorporativo += $clinic['tipo_corporativo'];
    $totalActivos += $clinic['contratos_activos'] ?? 0;
    $totalSuspendidos += $clinic['contratos_suspendidos'] ?? 0;
    $totalAnulados += $clinic['contratos_anulados'] ?? 0;
}

// Custom CSS with Microsoft styles
$this->registerCss("
    /* Microsoft-style layout */
    body {
        background-color: #f5f5f5;
        font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', sans-serif;
    }
    
    .stat-card {
        border-radius: 8px;
        transition: all 0.2s ease;
        border: none;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
    }
    .stat-card.bg-primary, .stat-card.bg-success, .stat-card.bg-danger {
        color: white !important;
    }
    .stat-card.bg-primary .card-title,
    .stat-card.bg-success .card-title,
    .stat-card.bg-danger .card-title,
    .stat-card.bg-primary .card-body,
    .stat-card.bg-success .card-body,
    .stat-card.bg-danger .card-body {
        color: white !important;
    }
    .stat-card.bg-primary small,
    .stat-card.bg-success small,
    .stat-card.bg-danger small {
        color: rgba(255,255,255,0.8) !important;
    }
    .card-header.bg-primary, .card-header.bg-success, .card-header.bg-info, .card-header.bg-warning {
        color: white !important;
        border-bottom: none;
        font-weight: 600;
        font-size: 1rem;
    }
    .table thead.bg-primary th,
    .table thead.bg-success th,
    .table thead.bg-info th,
    .table thead.bg-warning th {
        color: white !important;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 12px 12px;
        border-bottom: none;
    }
    .progress {
        background-color: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
    }
    .progress-bar {
        background: linear-gradient(90deg, #0078d4, #005a9e);
    }
    .badge {
        font-size: 0.9rem;
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        font-weight: 500;
        display: inline-block;
        min-width: 70px;
    }
    .badge-primary {
        background: #0078d4;
        font-size: 1rem;
        padding: 0.5rem 1rem;
    }
    .badge-info {
        background: #5bc0de;
    }
    .badge-warning {
        background: #ffc107;
        color: #212529;
    }
    .badge-success {
        background: #28a745;
    }
    .badge-danger {
        background: #dc3545;
    }
    .badge-secondary {
        background: #6c757d;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    .clickable-row {
        cursor: pointer;
    }
    .non-clickable-row {
        cursor: default;
    }
    
    /* Microsoft-style Totals Section */
    .totals-container {
        background: #ffffff;
        border-radius: 8px;
        padding: 0;
        margin-top: 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e0e0e0;
        overflow: hidden;
    }
    
    .totals-header {
        background: #f8f9fa;
        padding: 12px 20px;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .totals-header h4 {
        margin: 0;
        color: #333;
        font-weight: 600;
        font-size: 1rem;
    }
    
    .totals-header h4 i {
        margin-right: 8px;
        color: #0078d4;
    }
    
    .totals-grid {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-around;
        padding: 20px;
    }
    
    .total-item {
        text-align: center;
        padding: 15px 20px;
        flex: 1;
        min-width: 130px;
        transition: all 0.2s ease;
        position: relative;
    }
    
    .total-item:hover {
        background-color: #f8f9fa;
    }
    
    .total-item:not(:last-child)::after {
        content: '';
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        height: 50px;
        width: 1px;
        background: #e0e0e0;
    }
    
    .total-icon {
        font-size: 1.5rem;
        margin-bottom: 8px;
        color: #0078d4;
    }
    
    .total-label {
        color: #6c757d;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 500;
        margin-bottom: 6px;
    }
    
    .total-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: #333;
        line-height: 1;
        margin-bottom: 4px;
    }
    
    .total-sub {
        font-size: 0.65rem;
        color: #6c757d;
        margin-top: 4px;
    }
    
    @media (max-width: 768px) {
        .total-item {
            flex-basis: 50%;
            padding: 12px;
        }
        .total-item:nth-child(even)::after {
            display: none;
        }
        .total-number {
            font-size: 1.2rem;
        }
    }
    
    /* Special styling for specific totals */
    .total-item.total-activos .total-number {
        color: #28a745;
    }
    .total-item.total-suspendidos .total-number {
        color: #dc3545;
    }
    
    .table td {
        vertical-align: middle;
        padding: 10px 8px;
        font-size: 0.9rem;
    }
    .table td:first-child {
        font-weight: 600;
        font-size: 0.95rem;
    }
    .clinic-name {
        font-weight: 600;
        font-size: 0.95rem;
        color: #2c3e50;
    }
    
    /* Microsoft-style cards */
    .card {
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .card-header {
        border-bottom: 1px solid #e0e0e0;
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    .btn {
        border-radius: 4px;
        font-weight: 500;
    }
    
    .btn-light {
        background-color: #ffffff;
        border-color: #d0d0d0;
    }
    
    .btn-light:hover {
        background-color: #f8f9fa;
        border-color: #b0b0b0;
    }
    
    /* Filter section */
    .filter-section {
        background-color: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
    }
    
    /* Access restriction badge */
    .access-badge {
        display: inline-block;
        padding: 3px 10px;
        font-size: 0.7rem;
        font-weight: 600;
        border-radius: 20px;
        background-color: #e8f4fd;
        color: #0078d4;
        margin-left: 10px;
    }
");
?>

<div class="reporte-afiliados-dashboard">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2 text-gray-800">
                <i class="fas fa-chart-line mr-2" style="color: #0078d4;"></i>Dashboard de Afiliados
                <?php if ($hasClinicAccess): ?>
                    <span class="access-badge">
                        <i class="fas fa-lock mr-1"></i> Acceso Restringido
                    </span>
                <?php endif; ?>
            </h1>
            <p class="text-muted">
                <?php if ($hasClinicAccess && count($userClinicas) === 1): ?>
                    <i class="fas fa-building mr-1"></i> Datos de: <strong><?= Html::encode($userClinicas[0]->nombre ?? 'Mi Clínica') ?></strong>
                <?php elseif ($hasClinicAccess && count($userClinicas) > 1): ?>
                    <i class="fas fa-building mr-1"></i> Datos de: <strong><?= count($userClinicas) ?> clínicas asignadas</strong>
                <?php else: ?>
                    <i class="fas fa-chart-bar mr-1"></i> Análisis completo de afiliados por clínica, tipo de afiliado y planes seleccionados
                <?php endif; ?>
            </p>
        </div>
        <div>
            <?= Html::a(
                '<i class="fas fa-chart-bar mr-2"></i>Exportar Resumen Excel',
                ['exportar-resumen-excel', 'AfiliadosReportSearch' => Yii::$app->request->get('AfiliadosReportSearch', [])],
                ['class' => 'btn btn-outline-primary mr-2']
            ) ?>

            <?= Html::a(
                '<i class="fas fa-chart-pie mr-2"></i>Exportar Resumen PDF',
                ['exportar-resumen-pdf', 'AfiliadosReportSearch' => Yii::$app->request->get('AfiliadosReportSearch', [])],
                ['class' => 'btn btn-outline-warning', 'target' => '_blank']
            ) ?>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4 filter-section">
        <div class="card-body">
            <?php $form = ActiveForm::begin([
                'method' => 'get',
                'action' => ['reporte-afiliados-dashboard'],
                'options' => ['class' => 'row align-items-end', 'id' => 'filter-form']
            ]); ?>

            <!-- Clinica selection - Limited by user's permissions -->
            <div class="col-lg-3 col-md-6 mb-3">
                <?php if ($hasClinicAccess): ?>
                    <?php
                    // User has clinic-level access - show their assigned clinics (read-only or disabled)
                    $accessibleClinicas = UserHelper::getAccessibleClinicas();
                    $accessibleClinicaIds = ArrayHelper::getColumn($accessibleClinicas, 'id');
                    ?>
                    <?= $form->field($searchModel, 'clinica_ids')->dropDownList(
                        ArrayHelper::map($accessibleClinicas, 'id', 'nombre'),
                        [
                            'multiple' => true,
                            'size' => count($accessibleClinicas) > 1 ? 4 : 1,
                            'class' => 'form-control form-control-sm',
                            'disabled' => count($accessibleClinicas) === 1,
                            'value' => $accessibleClinicaIds,
                        ]
                    )->label('<i class="fas fa-hospital mr-1"></i> Clínicas') ?>
                    <?php if (count($accessibleClinicas) === 1): ?>
                        <small class="text-muted">Acceso restringido a su clínica asignada</small>
                    <?php else: ?>
                        <small class="text-muted">Seleccione una o más clínicas (solo sus asignadas)</small>
                    <?php endif; ?>
                <?php elseif ($isSuperAdmin): ?>
                    <?= $form->field($searchModel, 'clinica_ids')->dropDownList(
                        ArrayHelper::map($clinicas, 'id', 'nombre'),
                        [
                            'multiple' => true,
                            'size' => 4,
                            'class' => 'form-control form-control-sm',
                            'prompt' => 'Todas las clínicas...',
                            'value' => $defaultClinicaIds
                        ]
                    )->label('<i class="fas fa-hospital mr-1"></i> Clínicas') ?>
                    <small class="text-muted">Ctrl + clic para seleccionar múltiples</small>
                <?php else: ?>
                    <?= $form->field($searchModel, 'clinica_ids')->dropDownList(
                        ArrayHelper::map($clinicas, 'id', 'nombre'),
                        [
                            'multiple' => true,
                            'size' => count($clinicas) > 1 ? 4 : 1,
                            'class' => 'form-control form-control-sm',
                            'disabled' => count($clinicas) === 1,
                            'value' => $defaultClinicaIds
                        ]
                    )->label('<i class="fas fa-hospital mr-1"></i> Clínicas') ?>
                    <?php if (count($clinicas) === 1): ?>
                        <small class="text-muted">Acceso restringido a su clínica asignada</small>
                    <?php else: ?>
                        <small class="text-muted">Seleccione una o más clínicas (solo sus asignadas)</small>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="col-lg-2 col-md-6 mb-3">
                <?= $form->field($searchModel, 'user_datos_type_id')->dropDownList(
                    ['' => 'Todos'] + $tipoAfiliadoList,
                    ['class' => 'form-control form-control-sm']
                )->label('<i class="fas fa-user-tag mr-1"></i> Tipo de Afiliado') ?>
            </div>

            <div class="col-lg-2 col-md-6 mb-3">
                <?= $form->field($searchModel, 'plan_id')->dropDownList(
                    ['' => 'Todos los planes'] + $planList,
                    ['class' => 'form-control form-control-sm']
                )->label('<i class="fas fa-file-invoice-dollar mr-1"></i> Plan') ?>
            </div>

            <div class="col-lg-2 col-md-6 mb-3">
                <?= $form->field($searchModel, 'estatus')->dropDownList(
                    ['' => 'Todos', 'Registrado' => 'Activos', 'Inactivo' => 'Inactivos'],
                    ['class' => 'form-control form-control-sm']
                )->label('<i class="fas fa-circle mr-1"></i> Estado') ?>
            </div>

            <div class="col-lg-3 col-md-12 mb-3">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div class="d-flex">
                        <?= Html::submitButton('<i class="fas fa-filter mr-2"></i>Aplicar Filtros', ['class' => 'btn btn-primary mr-2 flex-grow-1']) ?>
                        <?= Html::a('<i class="fas fa-sync-alt"></i>', ['reporte-afiliados-dashboard'], ['class' => 'btn btn-outline-secondary', 'title' => 'Limpiar filtros']) ?>
                    </div>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card stat-card bg-primary text-white h-100">
                <div class="card-body">
                    <h6 class="card-title text-white">
                        <i class="fas fa-users mr-1"></i>
                        <?= $hasClinicAccess ? 'Afiliados en su Clínica' : 'Total Afiliados en Clínicas' ?>
                    </h6>
                    <h2 class="mb-0 text-white" style="font-size: 2rem;"><?= number_format($totals['total_afiliados'] ?? 0) ?></h2>
                    <small class="text-white-50">
                        <?php if ($hasClinicAccess): ?>
                            <?php $clinicaCount = count($userClinicas); ?>
                            En <?= $clinicaCount ?> <?= $clinicaCount == 1 ? 'clínica' : 'clínicas' ?>
                        <?php else: ?>
                            En <?= $totals['total_clinicas'] ?? 0 ?> clínicas
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card stat-card bg-success text-white h-100">
                <div class="card-body">
                    <h6 class="card-title text-white">
                        <i class="fas fa-check-circle mr-1"></i> Activos
                    </h6>
                    <h2 class="mb-0 text-white" style="font-size: 2rem;"><?= number_format($totals['total_contratos_activos'] ?? 0) ?></h2>
                    <small class="text-white-50">Contratos en vigencia</small>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card stat-card bg-danger text-white h-100">
                <div class="card-body">
                    <h6 class="card-title text-white">
                        <i class="fas fa-pause-circle mr-1"></i> Suspendidos
                    </h6>
                    <h2 class="mb-0 text-white" style="font-size: 2rem;"><?= number_format($totals['total_contratos_suspendidos'] ?? 0) ?></h2>
                    <small class="text-white-50">Con cuotas vencidas</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Chart: Affiliates by Clinic -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-chart-bar mr-2"></i> Distribución de Afiliados por Clínica
                </div>
                <div class="card-body">
                    <?php if (!empty($chartData['clinicLabels'])): ?>
                        <canvas id="clinicChart" height="300"></canvas>
                    <?php else: ?>
                        <div class="alert alert-info text-center">No hay datos para mostrar</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Chart: Timeline -->
            <div class="card mt-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-chart-line mr-2"></i> Evolución de Registros (Últimos 12 meses)
                </div>
                <div class="card-body">
                    <?php if (!empty($chartData['timelineLabels'])): ?>
                        <canvas id="timelineChart" height="300"></canvas>
                    <?php else: ?>
                        <div class="alert alert-info text-center">No hay datos para mostrar</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Top Clinics -->
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <i class="fas fa-trophy mr-2"></i>
                    <?= $hasClinicAccess ? 'Top Afiliados por Categoría' : 'Top Clínicas por Afiliados' ?>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-warning">
                                <tr class="text-white">
                                    <th class="text-white">#</th>
                                    <th class="text-white"><?= $hasClinicAccess ? 'Categoría' : 'Clínica' ?></th>
                                    <th class="text-white text-right">Afiliados</th>
                                    <th class="text-white text-center">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $rank = 1;
                                $total = $totals['total_afiliados'] ?? 0;
                                foreach ($topClinics as $clinic):
                                    $percentage = $total > 0 ? round(($clinic['total_afiliados'] / $total) * 100) : 0;
                                ?>
                                    <tr>
                                        <td class="text-center">
                                            <?php if ($rank == 1): ?>
                                                <i class="fas fa-medal text-warning"></i>
                                            <?php elseif ($rank == 2): ?>
                                                <i class="fas fa-medal text-secondary"></i>
                                            <?php elseif ($rank == 3): ?>
                                                <i class="fas fa-medal text-danger"></i>
                                            <?php else: ?>
                                                <?= $rank ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= Html::encode($clinic['clinica_nombre']) ?></strong></td>
                                        <td class="text-right"><?= number_format($clinic['total_afiliados']) ?></td>
                                        <td class="text-center">
                                            <div class="progress" style="height: 5px;">
                                                <div class="progress-bar" style="width: <?= $percentage ?>%"></div>
                                            </div>
                                            <small><?= $percentage ?>%</small>
                                        </td>
                                    </tr>
                                <?php
                                    $rank++;
                                endforeach;
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Plans Distribution - Grouped by Category -->
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-chart-pie mr-2"></i> Distribución por Planes (Agrupado)
                </div>
                <div class="card-body">
                    <?php if (!empty($summaryByPlan)): ?>
                        <canvas id="planChart" height="200"></canvas>
                        <div class="mt-3">
                            <?php
                            $planIndex = 0;
                            foreach ($summaryByPlan as $planCategory):
                                $colorIndex = $planIndex % count($planColors);
                            ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>
                                        <i class="fas fa-circle" style="color: <?= $planColors[$colorIndex] ?>; font-size: 0.7rem;"></i>
                                        <strong><?= Html::encode($planCategory['plan_category']) ?></strong>
                                        <?php if (count($planCategory['plans']) > 1): ?>
                                            <small class="text-muted">(<?= count($planCategory['plans']) ?> variantes)</small>
                                        <?php endif; ?>
                                    </span>
                                    <span class="badge badge-primary"><?= number_format($planCategory['total_afiliados']) ?></span>
                                </div>
                            <?php
                                $planIndex++;
                            endforeach;
                            ?>
                        </div>
                        <div class="alert alert-info mt-3 small">
                            <i class="fas fa-info-circle"></i> Los planes han sido agrupados por categoría (Bronce, Plata, Oro, Esmeralda, etc.)
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">No hay datos de planes disponibles</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Clinic Summary Table -->
    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-table mr-2"></i> Resumen Detallado por Clínica
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-primary">
                        <tr class="text-white">
                            <th class="text-white">Clínica</th>
                            <th class="text-white text-center">Total Afiliados</th>
                            <th class="text-white text-center">Individual</th>
                            <th class="text-white text-center">Corporativo</th>
                            <th class="text-white text-center">Activos</th>
                            <th class="text-white text-center">Suspendidos</th>
                            <th class="text-white text-center">Anulados</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($summaryByClinic as $clinic): ?>
                            <?php
                            // Only make row clickable if user is superadmin
                            $rowClass = $isSuperAdmin ? 'clickable-row' : 'non-clickable-row';
                            $onClick = $isSuperAdmin ? "window.location='" . Url::to(['user-datos/index-clinicas', 'clinica_id' => $clinic['clinica_id']]) . "'" : '';
                            ?>
                            <tr class="<?= $rowClass ?>" <?= $onClick ? "onclick=\"{$onClick}\"" : '' ?>>
                                <td class="clinic-name"><strong><?= Html::encode($clinic['clinica_nombre']) ?></strong></td>
                                <td class="text-center"><span class="badge badge-primary"><?= number_format($clinic['total_afiliados']) ?></span></td>
                                <td class="text-center"><span class="badge badge-info"><?= number_format($clinic['tipo_individual']) ?></span></td>
                                <td class="text-center"><span class="badge badge-warning"><?= number_format($clinic['tipo_corporativo']) ?></span></td>
                                <td class="text-center"><span class="badge badge-success"><?= number_format($clinic['contratos_activos'] ?? 0) ?></span></td>
                                <td class="text-center"><span class="badge badge-danger"><?= number_format($clinic['contratos_suspendidos'] ?? 0) ?></span></td>
                                <td class="text-center"><span class="badge badge-secondary"><?= number_format($clinic['contratos_anulados'] ?? 0) ?></span></td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($summaryByClinic)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No hay datos disponibles para los filtros seleccionados</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- BEAUTIFUL TOTALS SECTION -->
    <div class="totals-container">
        <div class="totals-header">
            <h4>
                <i class="fas fa-chart-line"></i>
                Resumen General de Todas las Clínicas
            </h4>
        </div>
        <div class="totals-grid">
            <div class="total-item total-afiliados">
                <div class="total-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="total-label">TOTAL AFILIADOS</div>
                <div class="total-number"><?= number_format($totalAfiliados) ?></div>
                <div class="total-sub">en todas las clínicas</div>
            </div>

            <div class="total-item">
                <div class="total-icon">
                    <i class="fas fa-user"></i>
                </div>
                <div class="total-label">INDIVIDUALES</div>
                <div class="total-number"><?= number_format($totalIndividual) ?></div>
                <div class="total-sub"><?= round(($totalIndividual / max($totalAfiliados, 1)) * 100) ?>% del total</div>
            </div>

            <div class="total-item">
                <div class="total-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="total-label">CORPORATIVOS</div>
                <div class="total-number"><?= number_format($totalCorporativo) ?></div>
                <div class="total-sub"><?= round(($totalCorporativo / max($totalAfiliados, 1)) * 100) ?>% del total</div>
            </div>

            <div class="total-item total-activos">
                <div class="total-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="total-label">ACTIVOS</div>
                <div class="total-number"><?= number_format($totalActivos) ?></div>
                <div class="total-sub">contratos vigentes</div>
            </div>

            <div class="total-item total-suspendidos">
                <div class="total-icon">
                    <i class="fas fa-pause-circle"></i>
                </div>
                <div class="total-label">SUSPENDIDOS</div>
                <div class="total-number"><?= number_format($totalSuspendidos) ?></div>
                <div class="total-sub">con cuotas vencidas</div>
            </div>

            <div class="total-item">
                <div class="total-icon">
                    <i class="fas fa-ban"></i>
                </div>
                <div class="total-label">ANULADOS</div>
                <div class="total-number"><?= number_format($totalAnulados) ?></div>
                <div class="total-sub">contratos cancelados</div>
            </div>
        </div>
    </div>

    <!-- Footer Statistics -->
    <div class="row mt-3">
        <div class="col-12 text-center">
            <small class="text-muted">
                <i class="fas fa-calendar-alt"></i> Última actualización: <?= date('d/m/Y H:i:s') ?>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <i class="fas fa-chart-bar"></i> Datos basados en los filtros aplicados
                <?php if ($hasClinicAccess): ?>
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    <i class="fas fa-lock"></i> Datos limitados a sus clínicas asignadas
                <?php endif; ?>
            </small>
        </div>
    </div>
</div>

<?php
// Prepare chart data for JavaScript
$clinicChartData = json_encode([
    'labels' => $chartData['clinicLabels'] ?? [],
    'individual' => $chartData['clinicIndividual'] ?? [],
    'corporativo' => $chartData['clinicCorporativo'] ?? [],
]);

$timelineChartData = json_encode([
    'labels' => $chartData['timelineLabels'] ?? [],
    'totals' => $chartData['timelineTotals'] ?? [],
    'individual' => $chartData['timelineIndividual'] ?? [],
    'corporativo' => $chartData['timelineCorporativo'] ?? [],
]);

$planChartData = json_encode([
    'labels' => array_column($summaryByPlan, 'plan_category'),
    'totals' => array_column($summaryByPlan, 'total_afiliados'),
]);

$this->registerJs("
    // Clinic Bar Chart
    const clinicCtx = document.getElementById('clinicChart').getContext('2d');
    const clinicData = $clinicChartData;
    
    if (clinicData.labels && clinicData.labels.length > 0) {
        new Chart(clinicCtx, {
            type: 'bar',
            data: {
                labels: clinicData.labels,
                datasets: [
                    {
                        label: 'Individual',
                        data: clinicData.individual,
                        backgroundColor: 'rgba(52, 152, 219, 0.8)',
                        borderColor: 'rgba(52, 152, 219, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        label: 'Corporativo',
                        data: clinicData.corporativo,
                        backgroundColor: 'rgba(231, 76, 60, 0.8)',
                        borderColor: 'rgba(231, 76, 60, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw.toLocaleString() + ' afiliados';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Número de Afiliados' },
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }
    
    // Timeline Line Chart
    const timelineCtx = document.getElementById('timelineChart').getContext('2d');
    const timelineData = $timelineChartData;
    
    if (timelineData.labels && timelineData.labels.length > 0) {
        new Chart(timelineCtx, {
            type: 'line',
            data: {
                labels: timelineData.labels,
                datasets: [
                    {
                        label: 'Total Afiliados',
                        data: timelineData.totals,
                        borderColor: 'rgba(46, 204, 113, 1)',
                        backgroundColor: 'rgba(46, 204, 113, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: 'rgba(46, 204, 113, 1)'
                    },
                    {
                        label: 'Individual',
                        data: timelineData.individual,
                        borderColor: 'rgba(52, 152, 219, 1)',
                        backgroundColor: 'rgba(52, 152, 219, 0.05)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3
                    },
                    {
                        label: 'Corporativo',
                        data: timelineData.corporativo,
                        borderColor: 'rgba(231, 76, 60, 1)',
                        backgroundColor: 'rgba(231, 76, 60, 0.05)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Número de Afiliados' },
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }
    
    // Plan Pie Chart
    const planCtx = document.getElementById('planChart').getContext('2d');
    const planData = $planChartData;
    
    if (planData.labels && planData.labels.length > 0) {
        const planColors = " . json_encode($planColors) . ";
        
        new Chart(planCtx, {
            type: 'pie',
            data: {
                labels: planData.labels,
                datasets: [{
                    data: planData.totals,
                    backgroundColor: planColors.slice(0, planData.labels.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.raw / total) * 100).toFixed(1);
                                return context.label + ': ' + context.raw.toLocaleString() + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
");
?>