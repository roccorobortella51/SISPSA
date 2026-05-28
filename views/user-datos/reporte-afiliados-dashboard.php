<?php

/**
 * @var yii\web\View $this
 * @var app\models\AfiliadosReportSearch $searchModel
 * @var array $summaryByClinic
 * @var array $summaryByPlan
 * @var array $timelineData
 * @var array $topClinics
 * @var array $totals
 * @var array $chartData
 * @var array $clinicaList
 * @var array $clinicas
 * @var array $planList
 * @var array $tipoAfiliadoList
 */

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
// MODIFIED: By default, no clinics are selected unless explicitly chosen by the user
$defaultClinicaIds = [];

// Only pre-select clinics if user has submitted filters
if (!empty($searchModel->clinica_ids)) {
    // If user submitted filters, respect them but ensure they only include accessible clinics
    $submittedClinicaIds = is_array($searchModel->clinica_ids) ? $searchModel->clinica_ids : [$searchModel->clinica_ids];
    $defaultClinicaIds = array_intersect($submittedClinicaIds, $userClinicaIds);
} else {
    // On initial page load, no clinics are selected
    $defaultClinicaIds = [];
}

// Register Chart.js CDN
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

// Define plan colors for pie chart
$planColors = [
    'Oro' => 'rgba(255, 215, 0, 0.85)',
    'Plata' => 'rgba(128, 128, 128, 0.85)',
    'Bronce' => 'rgba(205, 127, 50, 0.85)',
    'Esmeralda' => 'rgba(128, 0, 128, 0.85)',
    'Diamante' => 'rgba(0, 191, 255, 0.85)',
    'Básico' => 'rgba(46, 204, 113, 0.85)',
    'Premium' => 'rgba(231, 76, 60, 0.85)',
    'Default' => 'rgba(52, 152, 219, 0.85)',
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

// Get meta achievement data
$metaSummary = $searchModel->getMetaAchievementSummary(Yii::$app->request->queryParams);

// Sort meta summary by actual affiliates (Afiliados Actuales) descending
if (!empty($metaSummary['detalle_por_clinica'])) {
    usort($metaSummary['detalle_por_clinica'], function ($a, $b) {
        return $b['total_afiliados'] - $a['total_afiliados'];
    });
}

// Get selected clinic IDs for checkboxes
// MODIFIED: Only use submitted values, not defaults on initial load
$selectedClinicaIds = [];

// Check if there are submitted clinic IDs from the form
if (!empty($searchModel->clinica_ids)) {
    $selectedClinicaIds = is_array($searchModel->clinica_ids) ? $searchModel->clinica_ids : [$searchModel->clinica_ids];
    $selectedClinicaIds = array_map('intval', $selectedClinicaIds);
} else {
    // On initial load, no clinics are selected
    $selectedClinicaIds = [];
}

// Determine which clinics to show for checkboxes
if ($hasClinicAccess) {
    $displayClinicas = UserHelper::getAccessibleClinicas();
    $isRestricted = true;
} elseif ($isSuperAdmin) {
    $displayClinicas = $clinicas;
    $isRestricted = false;
} else {
    $displayClinicas = $clinicas;
    $isRestricted = true;
}

// Convert to array if it's an ActiveQuery result
if (!is_array($displayClinicas)) {
    $displayClinicas = $displayClinicas->all();
}

$this->registerCss("
    /* Microsoft-style layout */
    body {
        background-color: #f5f5f5;
        font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', sans-serif;
    }
    
    /* Three Cards Row - Microsoft Style */
    .kpi-cards-row {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .kpi-card {
        flex: 1;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        transition: all 0.2s ease;
        border: 1px solid #e0e0e0;
        position: relative;
    }
    
    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }
    
    .kpi-card-header {
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .kpi-card-header h6 {
        margin: 0;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .kpi-card-header i {
        font-size: 1.5rem;
        opacity: 0.8;
    }
    
    .kpi-card-body {
        padding: 0 20px 20px 20px;
    }
    
    .kpi-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.2;
    }
    
    .kpi-label {
        font-size: 0.7rem;
        opacity: 0.8;
        margin-top: 8px;
    }
    
    .kpi-card.primary {
        background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%);
        color: white;
    }
    
    .kpi-card.success {
        background: linear-gradient(135deg, #107c10 0%, #0a5e0a 100%);
        color: white;
    }
    
    .kpi-card.danger {
        background: linear-gradient(135deg, #d13438 0%, #a80000 100%);
        color: white;
    }
    
    /* Filter Section Styles */
    .filter-section {
        background-color: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        margin-bottom: 24px;
    }
    
    .filter-section .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e0e0e0;
        padding: 12px 20px;
    }
    
    .filter-section .card-header h5 {
        margin: 0;
        font-size: 0.9rem;
        font-weight: 600;
        color: #2c3e50;
    }
    
    .filter-row {
        padding: 16px 20px;
    }
    
    .filter-row:first-child {
        border-bottom: 1px solid #e0e0e0;
    }
    
    .filter-label {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        margin-bottom: 8px;
    }
    
    .filter-label i {
        margin-right: 6px;
        color: #0078d4;
    }
    
    /* Clinic Checkbox Group Styles */
    .clinics-checkbox-group {
        max-height: 220px;
        overflow-y: auto;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 12px;
        background: #ffffff;
    }
    
    .clinics-checkbox-group .checkbox-item {
        margin-bottom: 10px;
        padding: 6px 10px;
        border-radius: 6px;
        transition: background-color 0.2s ease;
    }
    
    .clinics-checkbox-group .checkbox-item:hover {
        background-color: #f0f7ff;
    }
    
    .clinics-checkbox-group .checkbox-item:last-child {
        margin-bottom: 0;
    }
    
    .clinics-checkbox-group .custom-control-label {
        font-size: 0.85rem;
        cursor: pointer;
        user-select: none;
        font-weight: 500;
    }
    
    .clinics-checkbox-group .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #0078d4;
        border-color: #0078d4;
    }
    
    .checkbox-actions {
        margin-bottom: 12px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .checkbox-actions .btn-link {
        padding: 0;
        font-size: 0.75rem;
        color: #0078d4;
        font-weight: 500;
    }
    
    .checkbox-actions .btn-link:hover {
        color: #005a9e;
        text-decoration: none;
    }
    
    .selected-count-badge {
        background: #0078d4;
        color: white;
        border-radius: 20px;
        padding: 2px 10px;
        font-size: 0.7rem;
        font-weight: 500;
        margin-left: 8px;
    }
    
    .clinics-info {
        margin-top: 8px;
        font-size: 0.7rem;
    }
    
    .clinics-info i {
        margin-right: 4px;
    }
    
    /* Professional Totals Section */
    .totals-section {
        background: #ffffff;
        border-radius: 12px;
        margin-top: 30px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e0e0e0;
        overflow: hidden;
    }
    
    .totals-section-header {
        background: #2c3e50;
        padding: 16px 24px;
        border-bottom: 1px solid #34495e;
    }
    
    .totals-section-header h4 {
        margin: 0;
        color: #ecf0f1;
        font-weight: 600;
        font-size: 1rem;
        letter-spacing: 0.5px;
    }
    
    .totals-section-header h4 i {
        margin-right: 10px;
        color: #3498db;
    }
    
    .totals-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 0;
        padding: 0;
    }
    
    .total-card {
        padding: 20px;
        text-align: center;
        transition: all 0.2s ease;
        position: relative;
        background: #ffffff;
    }
    
    .total-card:hover {
        background: #f8f9fa;
    }
    
    .total-card:not(:last-child) {
        border-right: 1px solid #e0e0e0;
    }
    
    .total-card-icon {
        font-size: 2rem;
        margin-bottom: 12px;
    }
    
    .total-card-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 8px;
    }
    
    .total-card-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: #2c3e50;
        line-height: 1.2;
    }
    
    .total-card-sub {
        font-size: 0.65rem;
        color: #6c757d;
        margin-top: 6px;
    }
    
    .total-card.active .total-card-value {
        color: #28a745;
    }
    
    .total-card.suspended .total-card-value {
        color: #dc3545;
    }
    
    .total-card.individual .total-card-icon {
        color: #0078d4;
    }
    
    .total-card.corporate .total-card-icon {
        color: #ff8c00;
    }
    
    @media (max-width: 768px) {
        .kpi-cards-row {
            flex-direction: column;
            gap: 15px;
        }
        
        .totals-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .total-card {
            border-right: none;
        }
        
        .total-card:nth-child(odd) {
            border-right: 1px solid #e0e0e0;
        }
        
        .filter-row .row {
            flex-direction: column;
        }
        
        .filter-row .col-md-4 {
            margin-bottom: 15px;
        }
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
    
    .table td {
        vertical-align: middle;
        padding: 10px 8px;
        font-size: 0.9rem;
    }
    .clinic-name {
        font-weight: 600;
        font-size: 0.95rem;
        color: #2c3e50;
    }
    
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
    
    .btn-primary {
        background-color: #0078d4;
        border-color: #0078d4;
    }
    
    .btn-primary:hover {
        background-color: #005a9e;
        border-color: #005a9e;
    }
    
    .btn-outline-primary {
        color: #0078d4;
        border-color: #0078d4;
    }
    
    .btn-outline-primary:hover {
        background-color: #0078d4;
        border-color: #0078d4;
    }
    
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
    
    .small-stat-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        transition: all 0.2s ease;
        border: 1px solid #e0e0e0;
        height: 100%;
    }
    
    .small-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .small-stat-icon {
        font-size: 2rem;
        margin-bottom: 8px;
    }
    
    .small-stat-value {
        font-size: 1.8rem;
        font-weight: bold;
        color: #333;
    }
    
    .small-stat-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
    }
    
    .counter-badge {
        display: inline-block;
        width: 32px;
        height: 32px;
        line-height: 32px;
        text-align: center;
        background: #0078d4;
        color: white;
        border-radius: 50%;
        font-weight: 700;
        font-size: 0.85rem;
    }
    
    .counter-number {
        font-weight: 700;
        color: #0078d4;
        font-size: 1rem;
    }
    
    .form-control-sm {
        border-radius: 6px;
        border: 1px solid #e0e0e0;
    }
    
    .form-control-sm:focus {
        border-color: #0078d4;
        box-shadow: 0 0 0 0.2rem rgba(0, 120, 212, 0.25);
    }
    
    .text-success { color: #28a745 !important; }
    .text-warning { color: #ffc107 !important; }
    .text-danger { color: #dc3545 !important; }
");

// JavaScript for checkbox handling
$this->registerJs("
    function updateSelectedCount() {
        var checked = $('.clinic-checkbox:checked').length;
        $('#selected-count').text(checked);
        if (checked === 0) {
            $('#selected-count-badge').hide();
        } else {
            $('#selected-count-badge').show();
        }
    }
    
    function selectAllClinicas() {
        $('.clinic-checkbox').prop('checked', true);
        updateSelectedCount();
    }
    
    function clearAllClinicas() {
        $('.clinic-checkbox').prop('checked', false);
        updateSelectedCount();
    }
    
    // Initialize on document ready
    $(document).ready(function() {
        updateSelectedCount();
        
        // Debug: Log when checkboxes change
        $('.clinic-checkbox').on('change', function() {
            console.log('Checkbox changed. Selected count: ' + $('.clinic-checkbox:checked').length);
        });
    });
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
                '<i class="fas fa-file-pdf mr-2"></i>Exportar Resumen PDF',
                ['exportar-resumen-pdf', 'AfiliadosReportSearch' => Yii::$app->request->get('AfiliadosReportSearch', [])],
                ['class' => 'btn btn-danger', 'target' => '_blank']
            ) ?>
        </div>
    </div>

    <!-- Filter Section - Reorganized -->
    <div class="filter-section">
        <div class="card-header">
            <h5><i class="fas fa-sliders-h mr-2" style="color: #0078d4;"></i>Filtros de Búsqueda</h5>
        </div>

        <?php $form = ActiveForm::begin([
            'method' => 'get',
            'action' => ['reporte-afiliados-dashboard'],
            'options' => ['id' => 'filter-form']
        ]); ?>

        <!-- First Row: Tipo de Afiliado, Plan, Estado -->
        <div class="filter-row">
            <div class="row">
                <div class="col-md-4">
                    <div class="filter-label">
                        <i class="fas fa-user-tag"></i> Tipo de Afiliado
                    </div>
                    <?= $form->field($searchModel, 'user_datos_type_id', ['options' => ['class' => 'mb-0']])
                        ->dropDownList(
                            ['' => 'Todos los tipos'] + $tipoAfiliadoList,
                            ['class' => 'form-control form-control-sm']
                        )->label(false) ?>
                </div>

                <div class="col-md-4">
                    <div class="filter-label">
                        <i class="fas fa-file-invoice-dollar"></i> Plan Médico
                    </div>
                    <?= $form->field($searchModel, 'plan_id', ['options' => ['class' => 'mb-0']])
                        ->dropDownList(
                            ['' => 'Todos los planes'] + $planList,
                            ['class' => 'form-control form-control-sm']
                        )->label(false) ?>
                </div>

                <div class="col-md-4">
                    <div class="filter-label">
                        <i class="fas fa-circle"></i> Estado del Afiliado
                    </div>
                    <?= $form->field($searchModel, 'estatus', ['options' => ['class' => 'mb-0']])
                        ->dropDownList(
                            ['' => 'Todos los estados', 'Registrado' => 'Activos', 'Inactivo' => 'Inactivos'],
                            ['class' => 'form-control form-control-sm']
                        )->label(false) ?>
                </div>
            </div>
        </div>

        <!-- Second Row: Clínicas with Checkboxes -->
        <div class="filter-row">
            <div class="filter-label" style="font-size: 0.85rem;">
                <i class="fas fa-hospital"></i> Gestión de Clínicas
                <?php if ((!$hasClinicAccess || count($displayClinicas) > 1) && count($displayClinicas) > 1): ?>
                    <span id="selected-count-badge" class="selected-count-badge" style="display: none;">
                        <span id="selected-count">0</span> seleccionadas
                    </span>
                <?php endif; ?>
            </div>

            <?php if ($isRestricted && count($displayClinicas) === 1): ?>
                <!-- Single clinic - show as static text with icon -->
                <?php $singleClinic = is_object($displayClinicas[0]) ? $displayClinicas[0] : (object)$displayClinicas[0]; ?>
                <div class="alert alert-info mb-0 py-2">
                    <i class="fas fa-lock mr-2"></i>
                    <strong><?= Html::encode($singleClinic->nombre ?? $displayClinicas[0]['nombre'] ?? 'Mi Clínica') ?></strong>
                    <small class="text-muted ml-2">(Acceso restringido a su clínica)</small>
                </div>
                <!-- For single clinic, create a hidden input with the clinic ID -->
                <input type="hidden" name="AfiliadosReportSearch[clinica_ids][]" value="<?= $singleClinic->id ?? $displayClinicas[0]['id'] ?? 0 ?>">
            <?php else: ?>
                <!-- Multiple clinics - show as checkbox group -->
                <div class="clinics-checkbox-group">
                    <div class="checkbox-actions d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-check-double mr-1"></i> Seleccione las clínicas a filtrar
                        </small>
                        <div>
                            <a href="javascript:void(0)" onclick="selectAllClinicas()" class="btn-link mr-3" style="font-size: 0.8rem;">
                                <i class="fas fa-check-circle mr-1"></i> Seleccionar todas
                            </a>
                            <a href="javascript:void(0)" onclick="clearAllClinicas()" class="btn-link" style="font-size: 0.8rem;">
                                <i class="fas fa-times-circle mr-1"></i> Limpiar selección
                            </a>
                        </div>
                    </div>

                    <div class="row">
                        <?php
                        foreach ($displayClinicas as $clinica):
                            // Handle both object and array formats
                            if (is_object($clinica)) {
                                $clinicaId = $clinica->id;
                                $clinicaNombre = $clinica->nombre;
                                $clinicaMeta = $clinica->meta ?? 0;
                            } else {
                                $clinicaId = $clinica['id'];
                                $clinicaNombre = $clinica['nombre'];
                                $clinicaMeta = $clinica['meta'] ?? 0;
                            }
                            $isChecked = in_array((int)$clinicaId, $selectedClinicaIds);
                        ?>
                            <div class="col-md-6">
                                <div class="checkbox-item custom-control custom-checkbox">
                                    <input type="checkbox"
                                        class="custom-control-input clinic-checkbox"
                                        name="AfiliadosReportSearch[clinica_ids][]"
                                        id="clinica_<?= $clinicaId ?>"
                                        value="<?= $clinicaId ?>"
                                        <?= $isChecked ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="clinica_<?= $clinicaId ?>" style="font-size: 0.9rem;">
                                        <?= Html::encode($clinicaNombre) ?>
                                        <?php if ($clinicaMeta && $clinicaMeta > 0): ?>
                                            <small class="text-muted">(Meta: <?= number_format($clinicaMeta) ?>)</small>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="clinics-info">
                    <i class="fas fa-info-circle text-info"></i>
                    <small>Seleccione una o más clínicas para filtrar los datos. Puede usar "Seleccionar todas" para incluir todas las clínicas.</small>
                </div>
            <?php endif; ?>

            <div class="text-right mt-3">
                <?= Html::submitButton('<i class="fas fa-filter mr-2"></i>Aplicar Filtros', ['class' => 'btn btn-primary px-4']) ?>
                <?= Html::a('<i class="fas fa-sync-alt mr-2"></i>Limpiar Filtros', ['reporte-afiliados-dashboard'], ['class' => 'btn btn-outline-secondary ml-2']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

    <!-- KPI CARDS - Single Row with 3 cards -->
    <div class="kpi-cards-row">
        <div class="kpi-card primary">
            <div class="kpi-card-header">
                <h6><i class="fas fa-users mr-2"></i> <?= $hasClinicAccess ? 'Afiliados en su Clínica' : 'Total Afiliados' ?></h6>
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="kpi-card-body">
                <div class="kpi-number"><?= number_format($totals['total_afiliados'] ?? 0) ?></div>
                <div class="kpi-label">
                    <i class="fas fa-building mr-1"></i>
                    <?php if ($hasClinicAccess): ?>
                        <?= count($userClinicas) ?> <?= count($userClinicas) == 1 ? 'clínica' : 'clínicas' ?>
                    <?php else: ?>
                        <?= $totals['total_clinicas'] ?? 0 ?> clínicas
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="kpi-card success">
            <div class="kpi-card-header">
                <h6><i class="fas fa-check-circle mr-2"></i> Contratos Activos</h6>
                <i class="fas fa-chart-simple"></i>
            </div>
            <div class="kpi-card-body">
                <div class="kpi-number"><?= number_format($totals['total_contratos_activos'] ?? 0) ?></div>
                <div class="kpi-label">
                    <i class="fas fa-calendar-check mr-1"></i> En vigencia
                </div>
            </div>
        </div>

        <div class="kpi-card danger">
            <div class="kpi-card-header">
                <h6><i class="fas fa-pause-circle mr-2"></i> Contratos Suspendidos</h6>
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="kpi-card-body">
                <div class="kpi-number"><?= number_format($totals['total_contratos_suspendidos'] ?? 0) ?></div>
                <div class="kpi-label">
                    <i class="fas fa-clock mr-1"></i> Con cuotas vencidas
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- META GOALS CARD - WITH COUNTER -->
    <!-- ============================================ -->
    <div class="card mb-4">
        <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <i class="fas fa-bullseye mr-2"></i> Cumplimiento de Metas Mensuales por Clínica
            <small class="ml-2">(Ordenado por Afiliados Actuales)</small>
        </div>
        <div class="card-body">
            <!-- Summary Stats -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="small-stat-card text-center">
                        <div class="small-stat-icon">
                            <i class="fas fa-chart-line" style="color: #667eea;"></i>
                        </div>
                        <div class="small-stat-value"><?= number_format($metaSummary['porcentaje_global_cumplimiento']) ?>%</div>
                        <div class="small-stat-label">Cumplimiento Global</div>
                        <small class="text-muted"><?= number_format($metaSummary['total_afiliados_actual']) ?> / <?= number_format($metaSummary['total_meta_objetivo']) ?> afiliados</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="small-stat-card text-center">
                        <div class="small-stat-icon text-success">
                            <i class="fas fa-check-circle" style="color: #28a745;"></i>
                        </div>
                        <div class="small-stat-value"><?= $metaSummary['clinicas_que_alcanzaron_meta'] ?></div>
                        <div class="small-stat-label">Clínicas que Alcanzaron Meta</div>
                        <small class="text-muted">(≥100%)</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="small-stat-card text-center">
                        <div class="small-stat-icon text-warning">
                            <i class="fas fa-chart-simple" style="color: #ffc107;"></i>
                        </div>
                        <div class="small-stat-value"><?= $metaSummary['clinicas_cerca_meta'] ?></div>
                        <div class="small-stat-label">Clínicas Cerca de Meta</div>
                        <small class="text-muted">(75-99%)</small>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="small-stat-card text-center">
                        <div class="small-stat-icon text-danger">
                            <i class="fas fa-hourglass-half" style="color: #dc3545;"></i>
                        </div>
                        <div class="small-stat-value"><?= $metaSummary['clinicas_lejos_meta'] ?></div>
                        <div class="small-stat-label">Clínicas Lejos de Meta</div>
                        <small class="text-muted">(&lt;75%)</small>
                    </div>
                </div>
            </div>

            <!-- Meta Achievement Table - With Counter -->
            <?php if (!empty($metaSummary['detalle_por_clinica'])): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="text-white text-center" style="width: 50px;">#</th>
                                <th class="text-white">Clínica</th>
                                <th class="text-white text-center">Meta Mensual</th>
                                <th class="text-white text-center">Afiliados Actuales</th>
                                <th class="text-white text-center">Cumplimiento</th>
                                <th class="text-white text-center">Faltante/Excedente</th>
                                <th class="text-white text-center">Estado</th>
                                <th class="text-white text-center">Barra de Progreso</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $counter = 1; ?>
                            <?php foreach ($metaSummary['detalle_por_clinica'] as $clinica): ?>
                                <?php
                                $progressClass = 'bg-success';
                                if ($clinica['porcentaje'] < 50) {
                                    $progressClass = 'bg-danger';
                                } elseif ($clinica['porcentaje'] < 75) {
                                    $progressClass = 'bg-warning';
                                } elseif ($clinica['porcentaje'] < 100) {
                                    $progressClass = 'bg-info';
                                }

                                $statusBadge = '';
                                if ($clinica['estado'] == 'alcanzada') {
                                    $statusBadge = '<span class="badge badge-success"><i class="fas fa-trophy mr-1"></i> Meta Alcanzada</span>';
                                } elseif ($clinica['estado'] == 'cerca') {
                                    $statusBadge = '<span class="badge badge-warning"><i class="fas fa-chart-line mr-1"></i> Cerca de Meta</span>';
                                } else {
                                    $statusBadge = '<span class="badge badge-danger"><i class="fas fa-exclamation-triangle mr-1"></i> Meta Pendiente</span>';
                                }
                                ?>
                                <tr>
                                    <td class="text-center">
                                        <span class="counter-badge"><?= $counter ?></span>
                                    </td>
                                    <td class="font-weight-bold"><?= Html::encode($clinica['nombre']) ?></td>
                                    <td class="text-center">
                                        <span class="badge badge-primary"><?= number_format($clinica['meta']) ?> afiliados</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info"><?= number_format($clinica['total_afiliados']) ?> afiliados</span>
                                    </td>
                                    <td class="text-center">
                                        <strong><?= $clinica['porcentaje'] ?>%</strong>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($clinica['faltante'] > 0): ?>
                                            <span class="text-danger">Faltan - <?= number_format($clinica['faltante']) ?></span>
                                        <?php else: ?>
                                            <span class="text-success">Excedente + <?= number_format($clinica['excedente']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?= $statusBadge ?></td>
                                    <td style="min-width: 150px;">
                                        <div class="progress" style="height: 25px;">
                                            <div class="progress-bar <?= $progressClass ?> progress-bar-striped"
                                                role="progressbar"
                                                style="width: <?= min(100, $clinica['porcentaje']) ?>%;"
                                                aria-valuenow="<?= $clinica['porcentaje'] ?>"
                                                aria-valuemin="0"
                                                aria-valuemax="100">
                                                <?= $clinica['porcentaje'] ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php $counter++; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    No hay datos de metas disponibles. Configure las metas mensuales en la sección de Clínicas.
                </div>
            <?php endif; ?>

            <!-- Legend -->
            <div class="row mt-3">
                <div class="col-12">
                    <small class="text-muted">
                        <i class="fas fa-chart-line text-success"></i> Meta Alcanzada (≥100%) &nbsp;&nbsp;
                        <i class="fas fa-chart-simple text-info"></i> Cerca de Meta (75-99%) &nbsp;&nbsp;
                        <i class="fas fa-exclamation-triangle text-warning"></i> En Progreso (50-74%) &nbsp;&nbsp;
                        <i class="fas fa-hourglass-start text-danger"></i> Lejos de Meta (&lt;50%)
                    </small>
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
                    <?= $hasClinicAccess ? 'Top Afiliados por Categoría' : 'Top Clínicas por Afiliados Activos' ?>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-warning">
                                <tr class="text-white">
                                    <th class="text-white">#</th>
                                    <th class="text-white"><?= $hasClinicAccess ? 'Categoría' : 'Clínica' ?></th>
                                    <th class="text-white text-right">Activos</th>
                                    <th class="text-white text-center">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $rank = 1;
                                $total = $totals['total_contratos_activos'] ?? 0;
                                $sortedTopClinics = $topClinics;
                                usort($sortedTopClinics, function ($a, $b) {
                                    return ($b['contratos_activos'] ?? 0) - ($a['contratos_activos'] ?? 0);
                                });
                                foreach ($sortedTopClinics as $clinic):
                                    $activos = $clinic['contratos_activos'] ?? 0;
                                    $percentage = $total > 0 ? round(($activos / $total) * 100) : 0;
                                ?>
                                    <tr>
                                        <td class="text-center">
                                            <?php if ($rank == 1): ?>
                                                <i class="fas fa-medal text-warning" style="font-size: 1.2rem;"></i>
                                            <?php elseif ($rank == 2): ?>
                                                <i class="fas fa-medal text-secondary" style="font-size: 1.2rem;"></i>
                                            <?php elseif ($rank == 3): ?>
                                                <i class="fas fa-medal text-danger" style="font-size: 1.2rem;"></i>
                                            <?php else: ?>
                                                <span class="counter-number"><?= $rank ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= Html::encode($clinic['clinica_nombre']) ?></strong></td>
                                        <td class="text-right"><?= number_format($activos) ?></td>
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
                                $category = $planCategory['plan_category'];
                                $color = isset($planColors[$category]) ? $planColors[$category] : $planColors['Default'];
                            ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>
                                        <i class="fas fa-circle" style="color: <?= $color ?>; font-size: 0.7rem;"></i>
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
                            <i class="fas fa-info-circle"></i> Colores por categoría:
                            <span style="color:#FFD700;">Oro</span>,
                            <span style="color:#808080;">Plata</span>,
                            <span style="color:#CD7F32;">Bronce</span>,
                            <span style="color:#800080;">Esmeralda</span>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">No hay datos de planes disponibles</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Clinic Summary Table - WITH COUNTER -->
    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-table mr-2"></i> Resumen Detallado por Clínica
            <small class="ml-2">(Ordenado por Afiliados Activos)</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-primary">
                        <tr class="text-white">
                            <th class="text-white text-center" style="width: 50px;">#</th>
                            <th class="text-white">Clínica</th>
                            <th class="text-white text-center">Meta</th>
                            <th class="text-white text-center">Total Afiliados</th>
                            <th class="text-white text-center">Individual</th>
                            <th class="text-white text-center">Corporativo</th>
                            <th class="text-white text-center">Activos</th>
                            <th class="text-white text-center">Suspendidos</th>
                            <th class="text-white text-center">Anulados</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Sort summaryByClinic by contratos_activos descending
                        $sortedSummary = $summaryByClinic;
                        usort($sortedSummary, function ($a, $b) {
                            return ($b['contratos_activos'] ?? 0) - ($a['contratos_activos'] ?? 0);
                        });
                        $counter = 1;
                        foreach ($sortedSummary as $clinic):
                        ?>
                            <?php
                            // Only make row clickable if user is superadmin
                            $rowClass = $isSuperAdmin ? 'clickable-row' : 'non-clickable-row';
                            $onClick = $isSuperAdmin ? "window.location='" . Url::to(['user-datos/index-clinicas', 'clinica_id' => $clinic['clinica_id']]) . "'" : '';

                            // Calculate meta compliance
                            $meta = (int)($clinic['clinica_meta'] ?? 0);
                            $totalAfiliadosClinica = $clinic['total_afiliados'];
                            $metaPorcentaje = $meta > 0 ? round(($totalAfiliadosClinica / $meta) * 100) : 0;
                            $metaBadgeClass = 'secondary';
                            if ($meta > 0) {
                                if ($metaPorcentaje >= 100) {
                                    $metaBadgeClass = 'success';
                                } elseif ($metaPorcentaje >= 75) {
                                    $metaBadgeClass = 'warning';
                                } elseif ($metaPorcentaje >= 50) {
                                    $metaBadgeClass = 'info';
                                } else {
                                    $metaBadgeClass = 'danger';
                                }
                            }
                            ?>
                            <tr class="<?= $rowClass ?>" <?= $onClick ? "onclick=\"{$onClick}\"" : '' ?>>
                                <td class="text-center">
                                    <span class="counter-badge"><?= $counter ?></span>
                                </td>
                                <td class="clinic-name"><strong><?= Html::encode($clinic['clinica_nombre']) ?></strong></td>
                                <td class="text-center">
                                    <?php if ($meta > 0): ?>
                                        <span class="badge badge-<?= $metaBadgeClass ?>" title="<?= $metaPorcentaje ?>% de cumplimiento">
                                            <?= number_format($meta) ?>
                                            <small>(<?= $metaPorcentaje ?>%)</small>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">No definida</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><span class="badge badge-primary"><?= number_format($clinic['total_afiliados']) ?></span></td>
                                <td class="text-center"><span class="badge badge-info"><?= number_format($clinic['tipo_individual']) ?></span></td>
                                <td class="text-center"><span class="badge badge-warning"><?= number_format($clinic['tipo_corporativo']) ?></span></td>
                                <td class="text-center"><span class="badge badge-success"><?= number_format($clinic['contratos_activos'] ?? 0) ?></span></td>
                                <td class="text-center"><span class="badge badge-danger"><?= number_format($clinic['contratos_suspendidos'] ?? 0) ?></span></td>
                                <td class="text-center"><span class="badge badge-secondary"><?= number_format($clinic['contratos_anulados'] ?? 0) ?></span></td>
                            </tr>
                            <?php $counter++; ?>
                        <?php endforeach; ?>

                        <?php if (empty($sortedSummary)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No hay datos disponibles para los filtros seleccionados</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PROFESSIONAL TOTALS SECTION -->
    <div class="totals-section">
        <div class="totals-section-header">
            <h4>
                <i class="fas fa-chart-pie"></i> Resumen General de Todas las Clínicas
            </h4>
        </div>
        <div class="totals-grid">
            <div class="total-card">
                <div class="total-card-icon">
                    <i class="fas fa-users" style="color: #0078d4;"></i>
                </div>
                <div class="total-card-label">TOTAL AFILIADOS</div>
                <div class="total-card-value"><?= number_format($totalAfiliados) ?></div>
                <div class="total-card-sub">en todas las clínicas</div>
            </div>

            <div class="total-card individual">
                <div class="total-card-icon">
                    <i class="fas fa-user" style="color: #0078d4;"></i>
                </div>
                <div class="total-card-label">INDIVIDUALES</div>
                <div class="total-card-value"><?= number_format($totalIndividual) ?></div>
                <div class="total-card-sub"><?= round(($totalIndividual / max($totalAfiliados, 1)) * 100) ?>% del total</div>
            </div>

            <div class="total-card corporate">
                <div class="total-card-icon">
                    <i class="fas fa-building" style="color: #ff8c00;"></i>
                </div>
                <div class="total-card-label">CORPORATIVOS</div>
                <div class="total-card-value"><?= number_format($totalCorporativo) ?></div>
                <div class="total-card-sub"><?= round(($totalCorporativo / max($totalAfiliados, 1)) * 100) ?>% del total</div>
            </div>

            <div class="total-card active">
                <div class="total-card-icon">
                    <i class="fas fa-check-circle" style="color: #28a745;"></i>
                </div>
                <div class="total-card-label">ACTIVOS</div>
                <div class="total-card-value"><?= number_format($totalActivos) ?></div>
                <div class="total-card-sub">contratos vigentes</div>
            </div>

            <div class="total-card suspended">
                <div class="total-card-icon">
                    <i class="fas fa-pause-circle" style="color: #dc3545;"></i>
                </div>
                <div class="total-card-label">SUSPENDIDOS</div>
                <div class="total-card-value"><?= number_format($totalSuspendidos) ?></div>
                <div class="total-card-sub">con cuotas vencidas</div>
            </div>

            <div class="total-card">
                <div class="total-card-icon">
                    <i class="fas fa-ban" style="color: #6c757d;"></i>
                </div>
                <div class="total-card-label">ANULADOS</div>
                <div class="total-card-value"><?= number_format($totalAnulados) ?></div>
                <div class="total-card-sub">contratos cancelados</div>
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

// Prepare plan chart data with custom colors
$planLabels = array_column($summaryByPlan, 'plan_category');
$planTotals = array_column($summaryByPlan, 'total_afiliados');
$planColorArray = [];
foreach ($planLabels as $category) {
    $planColorArray[] = isset($planColors[$category]) ? $planColors[$category] : $planColors['Default'];
}
$planChartData = json_encode([
    'labels' => $planLabels,
    'totals' => $planTotals,
    'colors' => $planColorArray,
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
    
    // Plan Pie Chart with custom colors
    const planCtx = document.getElementById('planChart').getContext('2d');
    const planData = $planChartData;
    
    if (planData.labels && planData.labels.length > 0) {
        new Chart(planCtx, {
            type: 'pie',
            data: {
                labels: planData.labels,
                datasets: [{
                    data: planData.totals,
                    backgroundColor: planData.colors,
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