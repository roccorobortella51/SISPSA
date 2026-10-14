<?php

/**
 * Afiliados Activos por Mes - Main view
 *
 * Metric: Tendencia Mensual (linear regression slope, normalized by mean)
 *
 * @var yii\web\View $this
 * @var app\models\AfiliadosActivosReportSearch $searchModel
 * @var array $monthlyData
 * @var array $clinicMatrix
 * @var array $summaryStats
 * @var array $clinicas
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;

$this->title = 'Afiliados Activos por Mes';
$this->params['breadcrumbs'][] = ['label' => 'Reportes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Register Chart.js
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

// Role-aware behavior
$hasClinicAccess = UserHelper::hasClinicAccess();
$isRestricted = $searchModel->hasClinicRestriction();
$displayClinicas = $searchModel->getAvailableClinicas();

// Selected clinic IDs
$selectedClinicaIds = is_array($searchModel->clinica_ids)
    ? array_map('intval', $searchModel->clinica_ids)
    : [];

// Ensure displayClinicas is an array
if (!is_array($displayClinicas)) {
    $displayClinicas = $displayClinicas->all();
}

// ============================================================
// Overall trend (footer) — from summary stats (single source of truth)
// ============================================================
$overallGrowth = $summaryStats['growth_rate'] ?? null;

// ============================================================
// CSS - Microsoft Fluent Design
// Using nowdoc (<<<'CSS') so VS Code treats it as pure CSS
// ============================================================
$css = <<<'CSS'
    /* ============================================================ */
    /* PAGE LAYOUT                                                   */
    /* ============================================================ */
    .afiliados-activos-mensual {
        font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', sans-serif;
    }

    .afiliados-activos-mensual .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 24px;
    }

    .afiliados-activos-mensual h1 {
        font-size: 2.5rem;
        font-weight: 600;
        color: #2c3e50;
        margin: 0 0 6px 0;
    }

    .afiliados-activos-mensual h1 i {
        color: #0078d4;
    }

    .afiliados-activos-mensual .page-subtitle {
        font-size: 1.2rem;
        color: #6c757d;
        margin: 0;
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

    /* ============================================================ */
    /* KPI CARDS                                                     */
    /* ============================================================ */
    .kpi-cards-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .kpi-card {
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.2s ease;
        border: 1px solid #e0e0e0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }

    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
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

    .kpi-card.gray {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
    }

    .kpi-card-header {
        padding: 14px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid rgba(255, 255, 255, 0.15);
    }

    .kpi-card-header h6 {
        margin: 0;
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #ffffff !important;
    }

    .kpi-card-header i {
        font-size: 1.3rem;
        opacity: 0.85;
    }

    .kpi-card-body {
        padding: 16px 20px 20px 20px;
    }

    .kpi-number {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1.15;
        color: #ffffff !important;
        margin: 0;
    }

    .kpi-label {
        font-size: 1.2rem;
        opacity: 0.9;
        margin-top: 6px;
        color: #ffffff !important;
    }

    .kpi-label i {
        opacity: 0.85;
    }

    /* ============================================================ */
    /* FILTER SECTION                                                */
    /* ============================================================ */
    .filter-section {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        margin-bottom: 24px;
        overflow: hidden;
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

    .filter-label {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        margin-bottom: 6px;
        display: block;
    }

    .filter-label i {
        color: #0078d4;
        margin-right: 4px;
    }

    /* ============================================================ */
    /* CLINIC CHECKBOX GROUP                                         */
    /* ============================================================ */
    .clinics-checkbox-group {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 12px;
        background: #ffffff;
    }

    .clinics-checkbox-group .checkbox-item {
        margin-bottom: 8px;
        padding: 5px 8px;
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
        margin-bottom: 10px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e0e0e0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.78rem;
    }

    .checkbox-actions .btn-link {
        padding: 0;
        font-size: 0.75rem;
        color: #0078d4;
        font-weight: 500;
        text-decoration: none;
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
        margin-top: 6px;
        font-size: 0.72rem;
        color: #6c757d;
    }

    .clinics-info i {
        margin-right: 4px;
    }

    .access-note {
        display: inline-block;
        padding: 2px 8px;
        background: #e8f4fd;
        color: #0078d4;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
        margin-left: 6px;
    }

    /* ============================================================ */
    /* MATRIX TABLE - PROFESSIONAL STYLE                             */
    /* ============================================================ */
    .matrix-table {
        font-size: 0.9rem;
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }

    /* ---------- HEADER ---------- */
    .matrix-table thead th {
        background-color: #2c3e50;
        color: #ffffff !important;
        font-weight: 600;
        font-size: 0.82rem;
        padding: 14px 10px;
        border: none;
        border-bottom: 3px solid #1a2530;
        border-right: 1px solid rgba(255, 255, 255, 0.08);
        vertical-align: middle;
        white-space: nowrap;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .matrix-table thead th:first-child {
        border-top-left-radius: 8px;
        text-align: left;
        padding-left: 18px;
    }

    .matrix-table thead th:last-child {
        border-top-right-radius: 8px;
        border-right: none;
    }

    .matrix-table thead th.growth-col {
        background-color: #107c10 !important;
        border-left: 3px solid #ffffff;
    }

    /* ---------- BODY ---------- */
    .matrix-table tbody td {
        padding: 14px 10px;
        border: none;
        border-bottom: 1px solid #e8ecef;
        vertical-align: middle;
        transition: background-color 0.15s ease;
    }

    /* ZEBRA STRIPING - alternating row colors */
    .matrix-table tbody tr:nth-child(even) td {
        background-color: #f8fafc;
    }

    .matrix-table tbody tr:nth-child(odd) td {
        background-color: #ffffff;
    }

    /* HOVER - highlights the entire row */
    .matrix-table tbody tr:hover td {
        background-color: #e8f4fd !important;
    }

    /* Clinic name cell */
    .matrix-table tbody td.clinic-name-cell {
        font-weight: 600;
        color: #2c3e50;
        font-size: 1.2rem;
        white-space: nowrap;
        padding-left: 18px;
        border-left: 3px solid transparent;
        transition: border-color 0.15s ease;
    }

    .matrix-table tbody tr:hover td.clinic-name-cell {
        border-left-color: #0078d4;
    }

    /* Month cells */
    .matrix-table tbody td.month-cell {
        text-align: center;
    }

    /* Growth cell */
    .matrix-table tbody td.growth-cell {
        text-align: center;
        background-color: #fafcfa;
        border-left: 1px solid #e8ecef;
    }

    .matrix-table tbody tr:nth-child(even) td.growth-cell {
        background-color: #f2f6f3;
    }

    .matrix-table tbody tr:hover td.growth-cell {
        background-color: #e5f2e8 !important;
    }

    /* ---------- FOOTER (Totals Row) ---------- */
    .matrix-table tfoot td {
        background-color: #2c3e50;
        color: #ffffff !important;
        font-weight: 700;
        font-size: 1rem;
        padding: 16px 10px;
        border: none;
        border-top: 3px solid #1a2530;
        vertical-align: middle;
    }

    .matrix-table tfoot td:first-child {
        border-bottom-left-radius: 8px;
        padding-left: 18px;
        text-align: left;
        font-size: 0.9rem;
        letter-spacing: 0.5px;
    }

    .matrix-table tfoot td:last-child {
        border-bottom-right-radius: 8px;
    }

    .matrix-table tfoot td.month-total {
        color: #4cd964 !important;
        text-align: center;
    }

    .matrix-table tfoot td.growth-total {
        background-color: #107c10 !important;
        text-align: center;
        font-size: 1.1rem;
        border-left: 3px solid #ffffff;
    }

    /* ============================================================ */
    /* COUNT BADGES - BIGGER NUMBERS                                 */
    /* ============================================================ */
    .count-badge {
        display: inline-block;
        min-width: 48px;
        padding: 5px 10px;
        background: #e8f4fd;
        color: #0078d4;
        border-radius: 6px;
        font-weight: 700;
        font-size: 1rem;
    }

    .count-badge.zero {
        background: transparent;
        color: #c0c7ce;
        font-weight: 400;
        font-size: 1rem;
    }

    /* ============================================================ */
    /* GROWTH / TREND BADGE                                          */
    /* ============================================================ */
    .growth-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        min-width: 90px;
        padding: 5px 10px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 1.3rem;
        justify-content: center;
    }

    .growth-badge.up {
        background: #d4f4d4;
        color: #107c10;
    }

    .growth-badge.down {
        background: #fde8e8;
        color: #d13438;
    }

    .growth-badge.flat {
        background: #f0f0f0;
        color: #495057;
        font-weight: 600;
    }

    .growth-badge.zero,
    .growth-badge.na {
        background: #f0f0f0;
        color: #6c757d;
        font-weight: 500;
    }

    .growth-badge i {
        font-size: 0.85rem;
        opacity: 0.85;
    }

    /* ============================================================ */
    /* INFO ICON                                                     */
    /* ============================================================ */
    .info-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
        font-size: 0.65rem;
        margin-left: 6px;
        cursor: help;
        transition: all 0.15s ease;
        vertical-align: middle;
        border: 1px solid rgba(255, 255, 255, 0.35);
        text-transform: none;
        letter-spacing: 0;
        font-weight: 700;
        font-family: 'Segoe UI', sans-serif;
        font-style: italic;
    }

    .info-icon:hover {
        background: rgba(255, 255, 255, 0.4);
        transform: scale(1.1);
    }

    /* ============================================================ */
    /* CUSTOM TOOLTIP — high-contrast, Microsoft-inspired            */
    /* ============================================================ */
    .tooltip {
        font-family: 'Segoe UI', 'Roboto', sans-serif;
        z-index: 9999;
    }

    .tooltip-inner {
        max-width: 360px;
        padding: 14px 18px;
        background-color: #1a2530;
        color: #ffffff !important;
        font-size: 0.85rem;
        line-height: 1.55;
        text-align: left;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .tooltip-inner,
    .tooltip-inner * {
        color: #240f0f;
    }

    .tooltip-inner .tooltip-title {
        display: block;
        font-weight: 700;
        font-size: 0.95rem;
        color: #220c0c !important;
        margin-bottom: 8px;
        padding-bottom: 6px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.15);
    }

    .tooltip-inner .tooltip-list {
        margin: 8px 0 0 0;
        padding-left: 18px;
        list-style-type: disc;
    }

    .tooltip-inner .tooltip-list li {
        margin-bottom: 4px;
        color: #01080f !important;
        line-height: 1.5;
    }

    .tooltip-inner .tooltip-list li::marker {
        color: #050d13;
    }

    /* High-contrast color helpers */
    .tooltip-inner .text-up {
        color: #6ee7a5 !important;
        font-weight: 700;
    }

    .tooltip-inner .text-down {
        color: #ff8a8a !important;
        font-weight: 700;
    }

    .tooltip-inner .text-new {
        color: #74c0fc !important;
        font-weight: 700;
    }

    .tooltip-inner .text-zero {
        color: #b8c4d0 !important;
        font-weight: 700;
    }

    .tooltip-inner strong {
        color: #100101 !important;
        font-weight: 700;
    }

    .tooltip-inner .tooltip-note {
        display: inline-block;
        margin-top: 6px;
        padding: 6px 10px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 6px;
        color: #cfd8e3 !important;
        font-size: 0.78rem;
        font-style: italic;
    }

    .tooltip.bs-tooltip-top .arrow::before,
    .tooltip.bs-tooltip-auto[x-placement^='top'] .arrow::before {
        border-top-color: #1a2530;
    }

    .tooltip.bs-tooltip-bottom .arrow::before,
    .tooltip.bs-tooltip-auto[x-placement^='bottom'] .arrow::before {
        border-bottom-color: #1a2530;
    }

    .tooltip.bs-tooltip-left .arrow::before,
    .tooltip.bs-tooltip-auto[x-placement^='left'] .arrow::before {
        border-left-color: #1a2530;
    }

    .tooltip.bs-tooltip-right .arrow::before,
    .tooltip.bs-tooltip-auto[x-placement^='right'] .arrow::before {
        border-right-color: #1a2530;
    }

    /* ============================================================ */
    /* INFO PANEL                                                    */
    /* ============================================================ */
    .info-panel {
        background: #f0f6fd;
        border-left: 5px solid #0078d4;
        border-radius: 8px;
        padding: 22px 26px;
        margin-top: 24px;
    }

    .info-panel h6 {
        font-size: 1.15rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0 0 12px 0;
    }

    .info-panel h6 i {
        color: #0078d4;
        margin-right: 8px;
        font-size: 1.1rem;
    }

    .info-panel p,
    .info-panel li {
        font-size: 1rem;
        color: #495057;
        line-height: 1.7;
    }

    .info-panel p strong,
    .info-panel li strong {
        color: #2c3e50;
    }

    .info-panel ol {
        padding-left: 24px;
        margin-bottom: 10px;
    }

    .info-panel ol li {
        margin-bottom: 6px;
    }

    .info-panel code {
        background: #ffffff;
        color: #d13438;
        padding: 2px 7px;
        border-radius: 4px;
        font-size: 0.95rem;
        font-family: 'Consolas', 'Monaco', monospace;
        font-weight: 600;
    }

    .info-panel .note {
        font-size: 0.95rem;
        color: #6c757d;
        margin-top: 12px;
        margin-bottom: 0;
        line-height: 1.7;
    }

    .info-panel .note i {
        font-size: 1rem;
        margin-right: 4px;
    }

    /* ============================================================ */
    /* RESPONSIVE                                                    */
    /* ============================================================ */
    @media (max-width: 1200px) {
        .kpi-cards-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .kpi-cards-row {
            grid-template-columns: 1fr;
        }

        .afiliados-activos-mensual .page-header {
            flex-direction: column;
            gap: 16px;
        }
    }
CSS;

$this->registerCss($css);
?>

<div class="afiliados-activos-mensual">

    <!-- ============================================================ -->
    <!-- PAGE HEADER                                                   -->
    <!-- ============================================================ -->
    <div class="page-header">
        <div>
            <h1>
                <i class="fas fa-user-check mr-2"></i>Afiliados Activos por Mes
                <?php if ($hasClinicAccess): ?>
                    <span class="access-badge">
                        <i class="fas fa-lock mr-1"></i> Acceso Restringido
                    </span>
                <?php endif; ?>
            </h1>
            <p class="page-subtitle">
                <i class="fas fa-info-circle mr-1"></i>
                Afiliados sin cuotas <strong>vencidas</strong> al cierre de cada mes (últimos 12 meses)
            </p>
        </div>
        <div>
            <?= Html::a(
                '<i class="fas fa-file-excel mr-2"></i>Exportar Excel',
                array_merge(['export-afiliados-activos-excel'], Yii::$app->request->queryParams),
                ['class' => 'btn btn-outline-success mr-2']
            ) ?>
            <?= Html::a(
                '<i class="fas fa-file-pdf mr-2"></i>Exportar PDF',
                array_merge(['export-afiliados-activos-pdf'], Yii::$app->request->queryParams),
                ['class' => 'btn btn-danger', 'target' => '_blank']
            ) ?>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- FILTERS - ONLY CLINICS                                        -->
    <!-- ============================================================ -->
    <div class="filter-section">
        <div class="card-header">
            <h5><i class="fas fa-sliders-h mr-2"></i>Filtros</h5>
        </div>
        <div class="card-body" style="padding: 16px 20px;">
            <?php $form = ActiveForm::begin([
                'method' => 'get',
                'action' => ['afiliados-activos-mensual'],
                'options' => ['id' => 'afiliados-activos-filter-form'],
            ]); ?>

            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="filter-label">
                        <i class="fas fa-hospital"></i> Clínicas
                        <?php if ($isRestricted): ?>
                            <span class="access-note">
                                <i class="fas fa-lock mr-1"></i>Acceso restringido
                            </span>
                        <?php endif; ?>
                        <?php if (!$isRestricted && count($displayClinicas) > 1): ?>
                            <span id="selected-count-badge" class="selected-count-badge" style="display:none;">
                                <span id="selected-count">0</span> seleccionadas
                            </span>
                        <?php endif; ?>
                    </label>

                    <?php if ($isRestricted && count($displayClinicas) === 1): ?>
                        <?php
                        $singleClinic = is_object($displayClinicas[0]) ? $displayClinicas[0] : (object)$displayClinicas[0];
                        ?>
                        <div class="alert alert-info mb-0 py-2">
                            <i class="fas fa-lock mr-2"></i>
                            <strong><?= Html::encode($singleClinic->nombre ?? 'Mi Clínica') ?></strong>
                            <small class="text-muted ml-2">(Solo puede ver los datos de su clínica asignada)</small>
                        </div>
                        <input type="hidden"
                            name="AfiliadosActivosReportSearch[clinica_ids][]"
                            value="<?= $singleClinic->id ?? 0 ?>">
                    <?php elseif (empty($displayClinicas)): ?>
                        <div class="alert alert-warning mb-0 py-2">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            No tiene clínicas asignadas. Contacte al administrador.
                        </div>
                    <?php else: ?>
                        <div class="clinics-checkbox-group">
                            <div class="checkbox-actions">
                                <small class="text-muted">
                                    <i class="fas fa-check-double mr-1"></i>
                                    Seleccione las clínicas a filtrar
                                </small>
                                <div>
                                    <a href="javascript:void(0)" onclick="selectAllClinicas()" class="btn-link mr-3">
                                        <i class="fas fa-check-circle mr-1"></i> Todas
                                    </a>
                                    <a href="javascript:void(0)" onclick="clearAllClinicas()" class="btn-link">
                                        <i class="fas fa-times-circle mr-1"></i> Limpiar
                                    </a>
                                </div>
                            </div>

                            <div class="row">
                                <?php foreach ($displayClinicas as $clinica): ?>
                                    <?php
                                    if (is_object($clinica)) {
                                        $clinicaId = $clinica->id;
                                        $clinicaNombre = $clinica->nombre;
                                    } else {
                                        $clinicaId = $clinica['id'];
                                        $clinicaNombre = $clinica['nombre'];
                                    }
                                    $isChecked = in_array((int)$clinicaId, $selectedClinicaIds);
                                    ?>
                                    <div class="col-md-4">
                                        <div class="checkbox-item custom-control custom-checkbox">
                                            <input type="checkbox"
                                                class="custom-control-input clinic-checkbox"
                                                name="AfiliadosActivosReportSearch[clinica_ids][]"
                                                id="clinica_<?= $clinicaId ?>"
                                                value="<?= $clinicaId ?>"
                                                <?= $isChecked ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="clinica_<?= $clinicaId ?>">
                                                <?= Html::encode($clinicaNombre) ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="clinics-info">
                            <i class="fas fa-info-circle"></i>
                            Seleccione una o más clínicas. Si no selecciona ninguna, se mostrarán todas.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 text-right">
                    <?= Html::submitButton(
                        '<i class="fas fa-filter mr-2"></i>Aplicar Filtros',
                        ['class' => 'btn btn-primary']
                    ) ?>
                    <?= Html::a(
                        '<i class="fas fa-sync-alt mr-2"></i>Limpiar Filtros',
                        ['afiliados-activos-mensual'],
                        ['class' => 'btn btn-outline-secondary ml-2']
                    ) ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- KPI CARDS                                                     -->
    <!-- ============================================================ -->
    <div class="kpi-cards-row">

        <!-- Card 1: Current Active -->
        <div class="kpi-card primary">
            <div class="kpi-card-header">
                <h6><i class="fas fa-users mr-2"></i>Activos Actuales</h6>
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="kpi-card-body">
                <div class="kpi-number"><?= number_format($summaryStats['current'] ?? 0) ?></div>
                <div class="kpi-label">
                    <i class="fas fa-calendar-alt mr-1"></i> Al cierre del mes
                </div>
            </div>
        </div>

        <!-- Card 2: Tendencia Mensual -->
        <?php $growthPositive = ($summaryStats['growth_rate'] ?? 0) >= 0; ?>
        <div class="kpi-card <?= $growthPositive ? 'success' : 'danger' ?>">
            <div class="kpi-card-header">
                <h6>
                    <i class="fas fa-<?= $growthPositive ? 'arrow-up' : 'arrow-down' ?> mr-2"></i>
                    Tendencia Mensual
                </h6>
                <i class="fas fa-percentage"></i>
            </div>
            <div class="kpi-card-body">
                <div class="kpi-number">
                    <?= $growthPositive ? '+' : '' ?><?= number_format($summaryStats['growth_rate'] ?? 0, 1) ?>%
                </div>
                <div class="kpi-label">
                    promedio por mes (regresión)
                </div>
            </div>
        </div>

        <!-- Card 3: Best Month -->
        <div class="kpi-card success">
            <div class="kpi-card-header">
                <h6><i class="fas fa-trophy mr-2"></i>Mejor Mes</h6>
                <i class="fas fa-chart-simple"></i>
            </div>
            <div class="kpi-card-body">
                <div class="kpi-number" style="font-size: 1.6rem;">
                    <?= Html::encode($summaryStats['best_month_label'] ?? '-') ?>
                </div>
                <div class="kpi-label">
                    <i class="fas fa-users mr-1"></i>
                    <?= number_format($summaryStats['best_month_count'] ?? 0) ?> activos
                </div>
            </div>
        </div>

        <!-- Card 4: Worst Month -->
        <div class="kpi-card gray">
            <div class="kpi-card-header">
                <h6><i class="fas fa-chart-line mr-2"></i>Menor Mes</h6>
                <i class="fas fa-chart-bar"></i>
            </div>
            <div class="kpi-card-body">
                <div class="kpi-number" style="font-size: 1.6rem;">
                    <?= Html::encode($summaryStats['worst_month_label'] ?? '-') ?>
                </div>
                <div class="kpi-label">
                    <i class="fas fa-users mr-1"></i>
                    <?= number_format($summaryStats['worst_month_count'] ?? 0) ?> activos
                </div>
            </div>
        </div>

    </div>

    <!-- ============================================================ -->
    <!-- TIMELINE CHART                                                -->
    <!-- ============================================================ -->
    <div class="card mb-4">
        <div class="card-header" style="background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%); color: white; border-bottom: none;">
            <span style="color: #ffffff; font-weight: 600;">
                <i class="fas fa-chart-line mr-2" style="color: #ffffff;"></i>
                Evolución de Afiliados Activos (Últimos 12 meses)
            </span>
        </div>
        <div class="card-body">
            <?php if (!empty($monthlyData['labels'])): ?>
                <canvas id="activeAffiliatesChart" height="100"></canvas>
            <?php else: ?>
                <div class="alert alert-info text-center mb-0">
                    <i class="fas fa-info-circle mr-2"></i>No hay datos para mostrar en el período seleccionado.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- MATRIX TABLE                                                  -->
    <!-- ============================================================ -->
    <div class="card mb-4">
        <div class="card-header" style="background: #2c3e50; color: white; border-bottom: none;">
            <span style="color: #ffffff; font-weight: 600;">
                <i class="fas fa-table mr-2" style="color: #ffffff;"></i>
                Matriz de Afiliados Activos por Clínica y Mes
                <small class="ml-2" style="color: #b8c4d0; font-weight: 400;">
                    (ordenado por tendencia mensual, mayor a menor)
                </small>
            </span>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($clinicMatrix['clinics'])): ?>
                <div class="table-responsive">
                    <table class="table matrix-table mb-0">
                        <thead>
                            <tr>
                                <th style="min-width: 180px;">Clínica</th>
                                <?php foreach ($clinicMatrix['month_labels'] as $label): ?>
                                    <th class="text-center"><?= Html::encode($label) ?></th>
                                <?php endforeach; ?>
                                <!-- Column header renamed + tooltip sourced from hidden div -->
                                <th class="text-center growth-col">
                                    <i class="fas fa-chart-line mr-1"></i>Tendencia Mensual
                                    <span class="info-icon"
                                        data-toggle="tooltip"
                                        data-placement="bottom"
                                        data-html="true"
                                        data-container="body"
                                        data-tooltip-source="#tooltip-tendencia-mensual"
                                        title="">
                                        i
                                    </span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clinicMatrix['clinics'] as $clinic): ?>
                                <?php
                                $growthStatus = $clinic['growth_status'] ?? 'na';
                                $growthRate = $clinic['growth_rate'] ?? null;
                                ?>
                                <tr>
                                    <td class="clinic-name-cell">
                                        <?= Html::encode($clinic['clinica_nombre']) ?>
                                    </td>
                                    <?php foreach ($clinicMatrix['month_keys'] as $key): ?>
                                        <?php $count = $clinic['months'][$key] ?? 0; ?>
                                        <td class="month-cell">
                                            <?php if ($count > 0): ?>
                                                <span class="count-badge"><?= number_format($count) ?></span>
                                            <?php else: ?>
                                                <span class="count-badge zero">-</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="growth-cell">
                                        <?php if ($growthStatus === 'up'): ?>
                                            <span class="growth-badge up">
                                                <i class="fas fa-arrow-up"></i>
                                                +<?= number_format($growthRate, 1) ?>%/mes
                                            </span>
                                        <?php elseif ($growthStatus === 'down'): ?>
                                            <span class="growth-badge down">
                                                <i class="fas fa-arrow-down"></i>
                                                <?= number_format($growthRate, 1) ?>%/mes
                                            </span>
                                        <?php elseif ($growthStatus === 'flat'): ?>
                                            <span class="growth-badge flat">
                                                <i class="fas fa-arrow-right"></i>
                                                ≈ 0%/mes
                                            </span>
                                        <?php else: ?>
                                            <span class="growth-badge zero">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>TOTAL GENERAL</td>
                                <?php foreach ($clinicMatrix['month_keys'] as $key): ?>
                                    <td class="month-total">
                                        <?= number_format($clinicMatrix['monthly_totals'][$key] ?? 0) ?>
                                    </td>
                                <?php endforeach; ?>
                                <td class="growth-total">
                                    <?php if ($overallGrowth === null): ?>
                                        —
                                    <?php elseif ($overallGrowth >= 1.0): ?>
                                        <i class="fas fa-arrow-up mr-1"></i>+<?= number_format($overallGrowth, 1) ?>%/mes
                                    <?php elseif ($overallGrowth <= -1.0): ?>
                                        <i class="fas fa-arrow-down mr-1"></i><?= number_format($overallGrowth, 1) ?>%/mes
                                    <?php else: ?>
                                        <i class="fas fa-arrow-right mr-1"></i>≈ 0%/mes
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center mb-0 m-3">
                    <i class="fas fa-info-circle mr-2"></i>
                    No hay datos de afiliados activos para los filtros seleccionados.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- INFO PANEL                                                    -->
    <!-- ============================================================ -->
    <div class="info-panel">
        <h6>
            <i class="fas fa-info-circle"></i>
            ¿Cómo se calcula un afiliado "Activo"?
        </h6>
        <p class="mb-2">
            <strong>Un afiliado se considera activo al cierre del mes M si cumple TODAS estas condiciones:</strong>
        </p>
        <ol>
            <li>Tenía un contrato vigente durante ese mes (inicio ≤ fin del mes, y el contrato no estaba anulado).</li>
            <li><strong>Ninguna</strong> de sus cuotas tenía el estatus <code>vencida</code> al cierre del mes.</li>
        </ol>
        <p class="note mb-0">
            <i class="fas fa-check-circle text-success"></i>
            Los afiliados en período de gracia (<code>en_gracias</code>) <strong>sí</strong> cuentan como activos.
            <br>
            <i class="fas fa-check-circle text-success"></i>
            Cada afiliado se cuenta una sola vez, aunque tenga varios contratos.
            <br>
            <i class="fas fa-chart-line text-success"></i>
            La columna <strong>Tendencia Mensual</strong> se calcula por <strong>regresión lineal</strong> sobre todos
            los meses activos de cada clínica (desde su primer mes con actividad), normalizada por el promedio.
            Verde si sube ≥ 1%/mes, rojo si baja ≤ −1%/mes, gris si está estable.
        </p>
    </div>

    <!-- ============================================================ -->
    <!-- HIDDEN TOOLTIP CONTENT (Bootstrap 4-safe)                     -->
    <!-- ============================================================ -->
    <div id="tooltip-tendencia-mensual" style="display:none;">
        <span class="tooltip-title">
            <i class="fas fa-chart-line mr-1"></i> ¿Qué es la Tendencia Mensual?
        </span>
        <strong>Pendiente de regresión lineal sobre todos los meses activos de la clínica
            (comenzando desde su primer mes con actividad), normalizada por el promedio.</strong>
        <br><br>
        <strong>Fórmula:
            pendiente ÷ promedio × 100 → %/mes</strong>
        <br><br>
        <strong>Umbrales:</strong>
        <ul class="tooltip-list">
            <li><strong class="text-up">Verde ↗</strong> — sube ≥ +1 %/mes</li>
            <li><strong class="text-down">Rojo ↘</strong> — baja ≤ −1 %/mes</li>
            <li><strong class="text-zero">Gris →</strong> — estable (|tasa| &lt; 1 %/mes)</li>
            <li><strong class="text-zero">Gris —</strong> — menos de 3 meses activos</li>
        </ul>
        <br>
        <em class="tooltip-note">Regresión lineal · usa todos los meses activos</em>
    </div>

</div>

<?php
// ============================================================
// Chart.js initialization
// ============================================================
$chartLabels = json_encode($monthlyData['labels'] ?? []);
$chartTotals = json_encode($monthlyData['totals'] ?? []);

$this->registerJs("
    (function () {
        // ============================================================
        // CLINIC CHECKBOX HANDLING (plain JS, no jQuery dependency)
        // ============================================================
        function updateSelectedCount() {
            var checked = document.querySelectorAll('.clinic-checkbox:checked').length;
            var counterEl = document.getElementById('selected-count');
            var badgeEl = document.getElementById('selected-count-badge');
            if (counterEl) counterEl.textContent = checked;
            if (badgeEl) badgeEl.style.display = checked > 0 ? 'inline-block' : 'none';
        }

        window.selectAllClinicas = function () {
            document.querySelectorAll('.clinic-checkbox').forEach(function (cb) {
                cb.checked = true;
            });
            updateSelectedCount();
        };

        window.clearAllClinicas = function () {
            document.querySelectorAll('.clinic-checkbox').forEach(function (cb) {
                cb.checked = false;
            });
            updateSelectedCount();
        };

        // ============================================================
        // TOOLTIP + CHART INIT
        // jQuery-ready guarantees execution even if DOMContentLoaded
        // already fired before this script runs.
        // ============================================================
        var initTooltipsAndChart = function () {
            // --- Clinic checkboxes ---
            updateSelectedCount();
            document.querySelectorAll('.clinic-checkbox').forEach(function (cb) {
                cb.addEventListener('change', updateSelectedCount);
            });

            // --- Bootstrap tooltips ---
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.tooltip === 'function') {
                jQuery('[data-toggle=\"tooltip\"]').each(function () {
                    var \$el = jQuery(this);
                    var source = \$el.attr('data-tooltip-source');

                    if (source) {
                        var \$source = jQuery(source);
                        if (\$source.length) {
                            \$el.attr('title', \$source.html());
                        }
                    }

                    \$el.tooltip({
                        trigger: 'hover focus',
                        delay: { show: 150, hide: 100 },
                        animation: true,
                        html: true,
                        sanitize: false,
                        container: 'body'
                    });
                });
            }

            // --- Chart.js ---
            var ctx = document.getElementById('activeAffiliatesChart');
            if (!ctx) {
                return;
            }

            var labels = $chartLabels;
            var data = $chartTotals;

            if (!labels || labels.length === 0) {
                return;
            }

            new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Afiliados Activos',
                        data: data,
                        borderColor: 'rgba(0, 120, 212, 1)',
                        backgroundColor: 'rgba(0, 120, 212, 0.15)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        pointBackgroundColor: 'rgba(0, 120, 212, 1)',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#2c3e50',
                            padding: 12,
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 },
                            callbacks: {
                                label: function (context) {
                                    return 'Activos: ' + context.raw.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Número de Afiliados Activos'
                            },
                            ticks: {
                                precision: 0
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        };

        // Run after jQuery is guaranteed to be ready
        if (typeof jQuery !== 'undefined') {
            jQuery(initTooltipsAndChart);
        } else {
            document.addEventListener('DOMContentLoaded', initTooltipsAndChart);
        }
    })();
");
?>