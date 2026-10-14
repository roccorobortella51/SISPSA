<?php

use yii\helpers\Html;
use yii\helpers\Json;
use kartik\form\ActiveForm;
use yii\widgets\MaskedInput;
use kartik\widgets\FileInput;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Pagos $model */
/** @var app\models\Corporativo $corporativo */
/** @var yii\widgets\ActiveForm $form */
/** @var array $allCuotas Array of all pending Cuotas across affiliates >0 */
/** @var float $grandTotal Total sum of pending cuotas >0 */
$grandTotal = $grandTotal ?? 0;

// Calculate cuota count
$cuotaCount = count($allCuotas);
$isParcialPayment = isset($isParcial) && $isParcial === true;

// ============================================================
// Group cuotas by affiliate
// ============================================================
$affiliateGroups = [];
foreach ($allCuotas as $cuota) {
    $contrato = $cuota->contrato ?? null;
    $userDatos = $contrato->user ?? null;

    if ($userDatos) {
        $userId = $userDatos->id;
        $affiliateName = $userDatos->nombres . ' ' . $userDatos->apellidos;

        if (!isset($affiliateGroups[$userId])) {
            $affiliateGroups[$userId] = [
                'name' => $affiliateName,
                'cedula' => $userDatos->tipo_cedula . '-' . $userDatos->cedula,
                'cuotas' => [],
                'total' => 0,
                'contrato_id' => $contrato->id,
                'nrocontrato' => $contrato->nrocontrato,
            ];
        }

        $affiliateGroups[$userId]['cuotas'][] = $cuota;
        $affiliateGroups[$userId]['total'] += floatval($cuota->monto);
    }
}

// Sort affiliates alphabetically by name
uasort($affiliateGroups, function ($a, $b) {
    return strcmp($a['name'], $b['name']);
});

// Generar la URL absoluta para la acción del controlador
$urlTasaCambio = Url::to(['tasacambio-referencial']);

$disabled = isset($isEditable) && !$isEditable;

// Carga explícita del asset del DatePicker
\kartik\date\DatePickerAsset::register($this);

// Registrar variable global en HEAD
$this->registerJs('var grandTotal = ' . Json::encode($grandTotal) . ';', \yii\web\View::POS_HEAD);

// ============================================================
// CSS — Microsoft Fluent Design
// ============================================================
$this->registerCss(
    <<<CSS
/* ============================================================
   PAYMENT FORM — MICROSOFT FLUENT DESIGN
   ============================================================ */

/* ============================================================
   COMPACT SUMMARY BAR (GREEN) — ALL TEXT IN WHITE
   ============================================================ */
.payment-summary-bar {
    display: flex !important;
    align-items: stretch !important;
    background: linear-gradient(135deg, #107c10 0%, #0e700e 100%) !important;
    border-radius: 10px !important;
    padding: 0 !important;
    margin-bottom: 20px !important;
    color: #ffffff !important;
    box-shadow: 0 4px 14px rgba(16, 124, 16, 0.22) !important;
    overflow: hidden !important;
}

.payment-summary-bar,
.payment-summary-bar *,
.payment-summary-tile,
.payment-summary-tile * {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
}

.payment-summary-tile {
    display: flex !important;
    align-items: center !important;
    gap: 14px !important;
    padding: 16px 22px !important;
    flex: 1 1 0 !important;
    min-width: 0 !important;
    border-right: 1px solid rgba(255, 255, 255, 0.15) !important;
}

.payment-summary-tile:last-child {
    border-right: none !important;
}

.payment-summary-tile .tile-icon {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 40px !important;
    height: 40px !important;
    border-radius: 10px !important;
    background: rgba(255, 255, 255, 0.15) !important;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    font-size: 16px !important;
    flex-shrink: 0 !important;
}

.payment-summary-tile .tile-icon i {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
}

.payment-summary-tile .tile-body {
    display: flex !important;
    flex-direction: column !important;
    line-height: 1.15 !important;
    min-width: 0 !important;
}

.payment-summary-tile .tile-label {
    font-size: 11px !important;
    font-weight: 600 !important;
    opacity: 0.9 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
}

.payment-summary-tile .tile-value {
    font-size: 20px !important;
    font-weight: 700 !important;
    line-height: 1.1 !important;
    font-variant-numeric: tabular-nums !important;
    margin-top: 2px !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
}

@media (max-width: 992px) {
    .payment-summary-bar {
        flex-direction: column !important;
    }
    .payment-summary-tile {
        flex: 1 1 auto !important;
        border-right: none !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.15) !important;
    }
    .payment-summary-tile:last-child {
        border-bottom: none !important;
    }
}

@media (max-width: 576px) {
    .payment-summary-tile .tile-value {
        font-size: 18px !important;
    }
}

/* ===== SECTION CARDS ===== */
.section-card {
    background: #ffffff !important;
    border-radius: 12px !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
    margin-bottom: 24px !important;
    border: 1px solid #edebe9 !important;
    overflow: hidden !important;
}

.section-card-header {
    padding: 14px 20px !important;
    border-bottom: 1px solid #edebe9 !important;
    border-top: 3px solid #0078d4 !important;
    background-color: #faf9f8 !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: 12px !important;
}

.section-card-header h3 {
    margin: 0 !important;
    font-size: 16px !important;
    font-weight: 700 !important;
    color: #201f1e !important;
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
}

.section-card-header h3 i {
    color: #0078d4 !important;
}

.section-card-body {
    padding: 20px !important;
}

/* ============================================================
   FLOATING ACTION BUTTON — "Registrar Pago"
   ============================================================ */
.fl-fab {
    position: fixed !important;
    right: 28px !important;
    bottom: 28px !important;
    z-index: 1030 !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 12px !important;
    background-color: #a4262c !important;
    border: 1px solid #a4262c !important;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    font-weight: 700 !important;
    font-size: 15px !important;
    padding: 14px 26px !important;
    border-radius: 999px !important;
    min-height: 52px !important;
    box-shadow: 0 8px 24px rgba(164, 38, 44, 0.4) !important;
    transition: all 0.18s ease !important;
    text-decoration: none !important;
    white-space: nowrap !important;
    cursor: pointer !important;
}

.fl-fab:hover,
.fl-fab:focus,
.fl-fab:active {
    background-color: #8e1f24 !important;
    border-color: #8e1f24 !important;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    box-shadow: 0 12px 32px rgba(164, 38, 44, 0.5) !important;
    transform: translateY(-2px);
    text-decoration: none !important;
}

.fl-fab:active {
    transform: translateY(0);
    box-shadow: 0 6px 16px rgba(164, 38, 44, 0.4) !important;
}

.fl-fab i {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    font-size: 16px !important;
}

@media (max-width: 768px) {
    .fl-fab {
        right: 16px !important;
        bottom: 16px !important;
        padding: 12px 20px !important;
        font-size: 14px !important;
        min-height: 48px !important;
    }
}

/* ============================================================
   AFFILIATE TABLE — FLUENT STYLE
   ============================================================ */
.affiliate-table {
    width: 100% !important;
    font-size: 13px !important;
    border-collapse: separate !important;
    border-spacing: 0 !important;
}

.affiliate-table thead th {
    background-color: #faf9f8 !important;
    color: #605e5c !important;
    font-weight: 600 !important;
    padding: 12px 16px !important;
    font-size: 11px !important;
    letter-spacing: 0.05em !important;
    text-transform: uppercase !important;
    border-bottom: 1px solid #edebe9 !important;
    white-space: nowrap !important;
}

.affiliate-table tbody td {
    padding: 14px 16px !important;
    border-bottom: 1px solid #f3f2f1 !important;
    vertical-align: middle !important;
    background-color: #ffffff !important;
    color: #323130 !important;
}

.affiliate-group-header {
    background-color: #f3f2f1 !important;
    border-top: 2px solid #0078d4 !important;
    cursor: pointer !important;
    transition: background-color 0.15s ease !important;
    user-select: none !important;
}

.affiliate-group-header:hover {
    background-color: #edebe9 !important;
}

.affiliate-group-header td {
    font-weight: 600 !important;
    padding: 14px 16px !important;
    background-color: transparent !important;
}

.affiliate-group-header .toggle-icon {
    font-size: 14px !important;
    color: #0078d4 !important;
    transition: transform 0.2s ease !important;
    display: inline-block !important;
}

.affiliate-group-header .toggle-icon.expanded {
    transform: rotate(90deg) !important;
}

.affiliate-group-header .affiliate-name {
    font-weight: 700 !important;
    color: #201f1e !important;
    font-size: 14px !important;
}

.affiliate-group-header .affiliate-total {
    color: #a4262c !important;
    font-weight: 700 !important;
    font-variant-numeric: tabular-nums !important;
}

.affiliate-cuotas-detail {
    background-color: #ffffff !important;
}

.affiliate-cuotas-detail > td {
    padding: 0 !important;
    background-color: #ffffff !important;
}

.cuotas-detail-table {
    width: 100% !important;
    margin: 0 !important;
    background-color: #faf9f8 !important;
}

.cuotas-detail-table thead th {
    background-color: #f3f2f1 !important;
    color: #605e5c !important;
    font-weight: 600 !important;
    padding: 10px 16px !important;
    font-size: 11px !important;
    letter-spacing: 0.04em !important;
    text-transform: uppercase !important;
    border-bottom: 1px solid #edebe9 !important;
}

.cuotas-detail-table tbody td {
    padding: 12px 16px !important;
    border-bottom: 1px solid #f3f2f1 !important;
    background-color: #faf9f8 !important;
    color: #323130 !important;
    vertical-align: middle !important;
}

.cuotas-detail-table tbody tr:last-child td {
    border-bottom: none !important;
}

.cuotas-detail-table .sub-label {
    padding-left: 40px !important;
    color: #a19f9d !important;
}

/* ===== BADGES & PILLS ===== */
.month-badge {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    background-color: #e8f4fd !important;
    color: #0078d4 !important;
    padding: 4px 12px !important;
    border-radius: 999px !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    border: 1px solid #cfe7f9 !important;
    white-space: nowrap !important;
}

.coverage-badge {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    background-color: #e7f6e7 !important;
    color: #107c10 !important;
    padding: 3px 10px !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 500 !important;
    border: 1px solid #cfe9cf !important;
    white-space: nowrap !important;
    margin-top: 4px !important;
}

.cuota-number-pill {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-width: 34px !important;
    height: 26px !important;
    padding: 0 8px !important;
    border-radius: 6px !important;
    background-color: #e8f4fd !important;
    color: #0078d4 !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    font-variant-numeric: tabular-nums !important;
    border: 1px solid #cfe7f9 !important;
}

.status-pill {
    display: inline-flex !important;
    align-items: center !important;
    gap: 5px !important;
    padding: 3px 10px !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    letter-spacing: 0.02em !important;
    border: 1px solid transparent !important;
    white-space: nowrap !important;
}

.status-pill.status-pending {
    background-color: #e8f4fd !important;
    color: #0078d4 !important;
    border-color: #cfe7f9 !important;
}

.status-pill.status-grace {
    background-color: #fff4e5 !important;
    color: #ca5010 !important;
    border-color: #ffe0b3 !important;
}

.status-pill.status-overdue {
    background-color: #fde7e9 !important;
    color: #a4262c !important;
    border-color: #f5c2c7 !important;
}

.affiliate-subtotal {
    background-color: #e8f4fd !important;
}

.affiliate-subtotal td {
    background-color: #e8f4fd !important;
    font-weight: 700 !important;
    color: #0078d4 !important;
    border-top: 2px solid #cfe7f9 !important;
}

/* ============================================================
   FORM CONTROLS — FLUENT STYLE
   ============================================================ */
.form-group {
    margin-bottom: 20px !important;
}

.form-group label {
    font-size: 13px !important;
    font-weight: 600 !important;
    color: #323130 !important;
    margin-bottom: 6px !important;
    display: block !important;
}

.form-control {
    border-radius: 4px !important;
    border: 1px solid #8a8886 !important;
    padding: 8px 12px !important;
    font-size: 14px !important;
    min-height: 36px !important;
    transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
    background-color: #ffffff !important;
    color: #323130 !important;
}

.form-control:focus {
    border-color: #0078d4 !important;
    box-shadow: 0 0 0 1px #0078d4 !important;
    outline: none !important;
}

.form-control:disabled,
.form-control[readonly] {
    background-color: #f3f2f1 !important;
    color: #605e5c !important;
    cursor: not-allowed !important;
}

.form-control.is-invalid {
    border-color: #a4262c !important;
    box-shadow: 0 0 0 1px #a4262c !important;
}

.form-control.is-warning {
    border-color: #ffc107 !important;
    box-shadow: 0 0 0 1px #ffc107 !important;
    background-color: #fff9e6 !important;
}

.invalid-feedback {
    color: #a4262c !important;
    font-size: 12px !important;
    margin-top: 4px !important;
}

/* ============================================================
   SLIDE-IN PANEL / OFF-CANVAS DRAWER — NOW FROM THE LEFT
   ------------------------------------------------------------
   Anchored to the left edge of the viewport and translated
   off-screen to the left when inactive. The active state
   animates it into view.
   ============================================================ */
.slide-panel-overlay {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background-color: rgba(0, 0, 0, 0.35) !important;
    backdrop-filter: blur(2px) !important;
    -webkit-backdrop-filter: blur(2px) !important;
    z-index: 1040 !important;
    opacity: 0 !important;
    visibility: hidden !important;
    transition: opacity 0.3s ease, visibility 0.3s ease !important;
}

.slide-panel-overlay.active {
    opacity: 1 !important;
    visibility: visible !important;
}

.slide-panel {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;            /* ← anchored to the LEFT edge */
    right: auto !important;        /* ← undo any right anchoring */
    width: 920px !important;
    max-width: 92vw !important;
    height: 100% !important;
    background-color: #ffffff !important;
    z-index: 1050 !important;
    box-shadow: 4px 0 32px rgba(0, 0, 0, 0.2) !important;  /* ← cast shadow to the RIGHT */
    transform: translateX(-100%) !important;               /* ← starts off-screen on the LEFT */
    transition: transform 0.32s cubic-bezier(0.16, 1, 0.3, 1) !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
}

.slide-panel.active {
    transform: translateX(0) !important;  /* ← slides in from the LEFT */
}

.slide-panel-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    padding: 20px 32px !important;
    background: linear-gradient(135deg, #a4262c 0%, #8e1f24 100%) !important;
    color: #ffffff !important;
    flex-shrink: 0 !important;
}

.slide-panel-header h3 {
    margin: 0 !important;
    font-size: 18px !important;
    font-weight: 600 !important;
    color: #ffffff !important;
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
}

.slide-panel-close {
    background: rgba(255, 255, 255, 0.15) !important;
    border: none !important;
    color: #ffffff !important;
    width: 32px !important;
    height: 32px !important;
    border-radius: 4px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    cursor: pointer !important;
    transition: background-color 0.15s ease !important;
    font-size: 16px !important;
}

.slide-panel-close:hover {
    background: rgba(255, 255, 255, 0.25) !important;
}

.slide-panel-body {
    flex: 1 !important;
    overflow-y: auto !important;
    padding: 32px !important;
}

.slide-panel-footer {
    display: flex !important;
    justify-content: flex-end !important;
    gap: 12px !important;
    padding: 16px 32px !important;
    border-top: 1px solid #edebe9 !important;
    background-color: #faf9f8 !important;
    flex-shrink: 0 !important;
}

.slide-panel-confirm-hint {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    margin-right: auto !important;
    color: #605e5c !important;
    font-size: 12px !important;
}

.slide-panel-confirm-hint i {
    color: #a4262c !important;
}

/* ============================================================
   DRAWER FORM GRID — tight 3-column layout
   ============================================================ */
.slide-panel-body .drawer-section {
    margin-bottom: 24px !important;
}

.slide-panel-body .drawer-section:last-child {
    margin-bottom: 0 !important;
}

.slide-panel-body .drawer-section-title {
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #605e5c !important;
    text-transform: uppercase !important;
    letter-spacing: 0.06em !important;
    margin: 0 0 12px 0 !important;
    padding-bottom: 6px !important;
    border-bottom: 1px solid #edebe9 !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}

.slide-panel-body .drawer-section-title i {
    color: #0078d4 !important;
    font-size: 12px !important;
}

.slide-panel-body .form-group {
    margin-bottom: 16px !important;
}

.slide-panel-body .form-group label {
    font-size: 12px !important;
    font-weight: 600 !important;
    color: #323130 !important;
    margin-bottom: 6px !important;
    display: block !important;
}

/* ============================================================
   ATTACHMENT STRIP — compact horizontal dropzone
   ============================================================ */
.slide-panel-body .attach-label {
    font-size: 12px !important;
    font-weight: 600 !important;
    color: #323130 !important;
    margin-bottom: 6px !important;
    display: flex !important;
    align-items: center !important;
    gap: 4px !important;
}

.slide-panel-body .attach-required {
    color: #a4262c !important;
    font-weight: 700 !important;
    font-size: 14px !important;
    line-height: 1 !important;
}

.slide-panel-body .attach-strip {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 12px !important;
    padding: 10px 16px !important;
    background-color: #faf9f8 !important;
    border: 1.5px dashed #c8c6c4 !important;
    border-radius: 8px !important;
    min-height: 52px !important;
    transition: border-color 0.15s ease, background-color 0.15s ease !important;
}

.slide-panel-body .attach-strip:hover {
    border-color: #0078d4 !important;
    background-color: #f3f9fd !important;
}

.slide-panel-body .attach-strip.dragover {
    border-color: #107c10 !important;
    background-color: #e7f6e7 !important;
}

.slide-panel-body .attach-strip.has-error {
    border-color: #a4262c !important;
    background-color: #fdf3f4 !important;
}

.slide-panel-body .attach-strip-left {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    min-width: 0 !important;
    flex: 1 1 auto !important;
}

.slide-panel-body .attach-strip-icon {
    color: #0078d4 !important;
    font-size: 15px !important;
    flex-shrink: 0 !important;
}

.slide-panel-body .attach-strip-text {
    font-size: 13px !important;
    color: #605e5c !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
    min-width: 0 !important;
}

.slide-panel-body .attach-strip-text.has-file {
    color: #107c10 !important;
    font-weight: 600 !important;
}

.slide-panel-body .attach-strip-right {
    flex-shrink: 0 !important;
}

.slide-panel-body .attach-strip .field-pagos-imagen_prueba_file,
.slide-panel-body .attach-strip .field-pagos-imagen_prueba_file .form-group,
.slide-panel-body .attach-strip .file-input {
    margin: 0 !important;
}

.slide-panel-body .attach-error {
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    margin-top: 6px !important;
    padding: 6px 10px !important;
    background-color: #fde7e9 !important;
    border: 1px solid #f5c2c7 !important;
    border-radius: 4px !important;
    color: #a4262c !important;
    font-size: 12px !important;
    font-weight: 600 !important;
}

.slide-panel-body .attach-error i {
    color: #a4262c !important;
    font-size: 13px !important;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .slide-panel {
        width: 100% !important;
        max-width: 100% !important;
    }

    .slide-panel-body {
        padding: 20px !important;
    }

    .slide-panel-header,
    .slide-panel-footer {
        padding-left: 20px !important;
        padding-right: 20px !important;
    }

    .affiliate-table thead th,
    .affiliate-table tbody td {
        padding: 10px 12px !important;
        font-size: 12px !important;
    }

    .cuotas-detail-table thead th,
    .cuotas-detail-table tbody td {
        padding: 8px 12px !important;
        font-size: 11px !important;
    }

    .slide-panel-footer {
        flex-direction: column-reverse !important;
    }

    .slide-panel-footer .btn {
        width: 100% !important;
    }

    .slide-panel-confirm-hint {
        margin-right: 0 !important;
        justify-content: center !important;
    }

    .slide-panel-body .attach-strip {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 10px !important;
    }

    .slide-panel-body .attach-strip-right {
        display: flex !important;
        justify-content: flex-end !important;
    }
}
CSS
);

// ============================================================
// JAVASCRIPT
// ============================================================
$js = <<<JS
// ============================================================
// FETCH TASA DE CAMBIO
// ============================================================
function fetchTasaCambio(fecha) {
    if (!fecha) return;

    $.ajax({
        url: 'tasacambio-referencial',
        type: 'post',
        data: { fecha: fecha },
        success: function(response) {
            if (response && response !== '0') {
                var tasa = parseFloat(response);
                $('#pagos-tasa').val(tasa.toFixed(2));
                updateMontoBsReferencial();
            } else {
                console.warn('No se encontró tasa para la fecha: ' + fecha);
            }
        },
        error: function() {
            console.error('Error al buscar la tasa de cambio');
        }
    });
}

// ============================================================
// CALCULATE MONTO BS
// ============================================================
function updateMontoBsReferencial() {
    var montoUsd = parseFloat($('#pagos-monto_pagado').val().replace(/[^0-9.]/g, '')) || 0;
    var tasa = parseFloat($('#pagos-tasa').val()) || 0;
    var montoBs = 0;

    if (tasa > 0 && montoUsd > 0) {
        montoBs = montoUsd * tasa;
    }

    $('#monto-bs-display').val(montoBs.toFixed(2));
    $('#pagos-monto_bs').val(montoBs.toFixed(2));
}

// ============================================================
// VALIDATE MONTO USD
// ============================================================
function validateMontoUsd() {
    var montoUsd = parseFloat($('#pagos-monto_pagado').val().replace(/[^0-9.]/g, '')) || 0;

    if (montoUsd > grandTotal) {
        $('#pagos-monto_pagado').addClass('is-invalid');
        $('#monto-usd-error').text('El monto a pagar no puede superar el total seleccionado (' + grandTotal.toFixed(2) + ' USD).');
    } else if (Math.abs(montoUsd - grandTotal) > 0.01 && montoUsd > 0) {
        $('#pagos-monto_pagado').addClass('is-warning');
        $('#monto-usd-error').text('Advertencia: El monto ingresado (\$' + montoUsd.toFixed(2) + ') es diferente al total seleccionado (\$' + grandTotal.toFixed(2) + ').');
    } else {
        $('#pagos-monto_pagado').removeClass('is-invalid is-warning');
        $('#monto-usd-error').text('');
    }
}

// ============================================================
// SLIDE-IN PANEL CONTROLS
// ============================================================
function openSlidePanel() {
    $('#payment-slide-panel').addClass('active');
    $('#slide-panel-overlay').addClass('active');
    $('body').css('overflow', 'hidden');
}

function closeSlidePanel() {
    $('#payment-slide-panel').removeClass('active');
    $('#slide-panel-overlay').removeClass('active');
    $('body').css('overflow', '');
}

// ============================================================
// DOCUMENT READY
// ============================================================
$(document).ready(function() {

    // ── Toggle affiliate cuotas visibility ──
    $('.affiliate-group-header').on('click', function(e) {
        if ($(e.target).is(':checkbox') || $(e.target).closest('.checkbox-cell').length) {
            return;
        }

        var targetRow = $(this).next('.affiliate-cuotas-detail');
        var icon = $(this).find('.toggle-icon');

        targetRow.toggle();

        if (targetRow.is(':visible')) {
            icon.addClass('expanded');
        } else {
            icon.removeClass('expanded');
        }
    });

    // ── Open slide panel ──
    $('#btn-registrar-pago').on('click', function(e) {
        e.preventDefault();
        openSlidePanel();
    });

    // ── Close slide panel ──
    $('#slide-panel-close, #slide-panel-cancel').on('click', function(e) {
        e.preventDefault();
        closeSlidePanel();
    });

    // ── Close on overlay click ──
    $('#slide-panel-overlay').on('click', function() {
        closeSlidePanel();
    });

    // ── Close on Escape key ──
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#payment-slide-panel').hasClass('active')) {
            closeSlidePanel();
        }
    });

    // ── Tasa de Cambio fetch al cambiar la Fecha de Pago ──
    $('#fecha-pago').on('change', function() {
        var fechaSeleccionada = $(this).val();
        fetchTasaCambio(fechaSeleccionada);
    });

    // ── Auto-fetch tasa when page loads ──
    if ($('#fecha-pago').val()) {
        $('#fecha-pago').trigger('change');
    } else {
        var today = new Date().toISOString().split('T')[0];
        $('#fecha-pago').val(today).trigger('change');
    }

    // ── Recalcular si la Tasa de Cambio es modificada manualmente ──
    $('#pagos-tasa').on('input', updateMontoBsReferencial);

    // ── Recalcular Monto Bs y Validar Monto USD cuando Monto Pagado cambia ──
    $('#pagos-monto_pagado').on('input', function() {
        validateMontoUsd();
        updateMontoBsReferencial();
    });

    // ── Disparar cálculos iniciales ──
    if ($('#fecha-pago').val()) {
        $('#fecha-pago').trigger('change');
    } else if ($('#pagos-monto_pagado').val() || $('#pagos-tasa').val()) {
        updateMontoBsReferencial();
        validateMontoUsd();
    }

    // ── Dropzone drag-over visual feedback ──
    var dropzone = $('#attach-dropzone');
    if (dropzone.length) {
        dropzone.on('dragover dragenter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('dragover');
        });
        dropzone.on('dragleave dragend drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
        });
    }

    // ── Update filename label when a file is selected ──
    $(document).on('change', '#pagos-imagen_prueba_file', function() {
        var fileName = this.files && this.files[0] ? this.files[0].name : '';
        var \$text = $('#attach-filename');

        if (fileName) {
            \$text.text(fileName).addClass('has-file');
            $('.attach-strip-icon').removeClass('fa-paperclip').addClass('fa-check-circle').css('color', '#107c10');

            $('#attach-error').hide();
            $('#attach-dropzone').removeClass('has-error');
        } else {
            \$text.text('Adjuntar imagen · JPG o PNG · Máx. 2 MB').removeClass('has-file');
            $('.attach-strip-icon').removeClass('fa-check-circle').addClass('fa-paperclip').css('color', '#0078d4');
        }
    });

    // ── Block form submit if no comprobante is attached ──
    $('#pago-corporativo-form').on('submit', function(e) {
        var fileInput = document.getElementById('pagos-imagen_prueba_file');
        var hasFile   = fileInput && fileInput.files && fileInput.files.length > 0;

        var hasExistingUrl = $('input[name="Pagos[imagen_prueba]"]').val();

        if (!hasFile && !hasExistingUrl) {
            e.preventDefault();
            e.stopPropagation();

            $('#attach-error').show();
            $('#attach-dropzone').addClass('has-error');

            var dz = document.getElementById('attach-dropzone');
            if (dz) {
                dz.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            return false;
        }
    });

    // NOTE: All affiliate groups start COLLAPSED by default.
});
JS;
$this->registerJs($js);

// ============================================================
// FORM START
// ============================================================
$form = ActiveForm::begin([
    'id' => 'pago-corporativo-form',
    'options' => ['enctype' => 'multipart/form-data'],
    'type' => ActiveForm::TYPE_HORIZONTAL,
    'formConfig' => ['labelSpan' => 4, 'deviceSize' => ActiveForm::SIZE_SMALL],
]);
?>

<!-- ============================================================
     SECTION 1: COMPACT SUMMARY BAR
     ============================================================ -->
<div class="payment-summary-bar">
    <div class="payment-summary-tile">
        <div class="tile-icon"><i class="fas fa-dollar-sign"></i></div>
        <div class="tile-body">
            <span class="tile-label">Total a Pagar</span>
            <span class="tile-value"><?= Yii::$app->formatter->asCurrency($grandTotal, 'USD') ?></span>
        </div>
    </div>
    <div class="payment-summary-tile">
        <div class="tile-icon"><i class="fas fa-file-invoice"></i></div>
        <div class="tile-body">
            <span class="tile-label">Cuotas</span>
            <span class="tile-value"><?= $cuotaCount ?></span>
        </div>
    </div>
    <div class="payment-summary-tile">
        <div class="tile-icon"><i class="fas fa-users"></i></div>
        <div class="tile-body">
            <span class="tile-label">Afiliados</span>
            <span class="tile-value"><?= count($affiliateGroups) ?></span>
        </div>
    </div>
</div>

<!-- ============================================================
     SECTION 2: DETAIL TABLE (Grouped by Affiliate)
     ============================================================ -->
<div class="section-card">
    <div class="section-card-header">
        <h3>
            <i class="fas fa-list-ul"></i>
            Detalle de Cuotas a Pagar
        </h3>
    </div>
    <div class="section-card-body p-0">
        <div class="table-responsive">
            <table class="affiliate-table">
                <thead>
                    <tr>
                        <th style="width: 40px;"></th>
                        <th style="width: 50px;">#</th>
                        <th>Afiliado</th>
                        <th>Cédula</th>
                        <th>Contrato</th>
                        <th class="text-end">Total a Pagar</th>
                        <th class="text-center" style="width: 100px;">Cuotas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $affiliateNumber = 1;
                    foreach ($affiliateGroups as $affiliateId => $group):
                        $cuotasList = $group['cuotas'];
                        usort($cuotasList, function ($a, $b) {
                            $coverageA = $a->coverage_start ?: $a->fecha_vencimiento;
                            $coverageB = $b->coverage_start ?: $b->fecha_vencimiento;
                            return strtotime($coverageA) - strtotime($coverageB);
                        });
                    ?>
                        <!-- Affiliate Summary Row -->
                        <tr class="affiliate-group-header">
                            <td class="text-center">
                                <i class="fas fa-chevron-right toggle-icon"></i>
                            </td>
                            <td class="text-center">
                                <strong><?= $affiliateNumber++ ?></strong>
                            </td>
                            <td>
                                <span class="affiliate-name"><?= Html::encode($group['name']) ?></span>
                            </td>
                            <td style="color: #605e5c;"><?= Html::encode($group['cedula']) ?></td>
                            <td style="color: #605e5c;"><?= Html::encode($group['nrocontrato']) ?></td>
                            <td class="text-end">
                                <span class="affiliate-total"><?= Yii::$app->formatter->asCurrency($group['total'], 'USD') ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-primary" style="background-color: #0078d4 !important;">
                                    <?= count($cuotasList) ?> cuota(s)
                                </span>
                            </td>
                        </tr>

                        <!-- Affiliate Cuotas Detail Row (collapsed by default) -->
                        <tr class="affiliate-cuotas-detail" style="display: none;">
                            <td colspan="7" class="p-0">
                                <table class="cuotas-detail-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 70px;"></th>
                                            <th style="width: 70px; text-align: center;">Cuota #</th>
                                            <th>Mes / Período de Cobertura</th>
                                            <th>ID Cuota</th>
                                            <th class="text-end">Monto USD</th>
                                            <th>Vencimiento</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $cuotaNumber = 1;
                                        foreach ($cuotasList as $cuota):
                                            $coverageStart = $cuota->coverage_start ?: $cuota->fecha_vencimiento;
                                            $coverageDate = new \DateTime($coverageStart);
                                            $monthName = $coverageDate->format('F Y');

                                            $coverageText = '';
                                            if ($cuota->coverage_start && $cuota->coverage_end) {
                                                $start = new \DateTime($cuota->coverage_start);
                                                $end = new \DateTime($cuota->coverage_end);
                                                $coverageText = $start->format('d/m') . ' - ' . $end->format('d/m/Y');
                                            }

                                            $statusClass = 'status-pending';
                                            $statusIcon = 'fa-clock';
                                            $statusText = 'Pendiente';
                                            if ($cuota->estatus === 'vencida') {
                                                $statusClass = 'status-overdue';
                                                $statusIcon = 'fa-exclamation-circle';
                                                $statusText = 'Vencida';
                                            } elseif ($cuota->estatus === 'en_gracias') {
                                                $statusClass = 'status-grace';
                                                $statusIcon = 'fa-hourglass-half';
                                                $statusText = 'En Gracia';
                                            }
                                        ?>
                                            <tr>
                                                <td class="sub-label">
                                                    <i class="fas fa-receipt" style="color: #a19f9d;"></i>
                                                </td>
                                                <td class="text-center">
                                                    <span class="cuota-number-pill">#<?= $cuotaNumber++ ?></span>
                                                </td>
                                                <td>
                                                    <span class="month-badge">
                                                        <i class="far fa-calendar-alt"></i>
                                                        <?= $monthName ?>
                                                    </span>
                                                    <?php if ($coverageText): ?>
                                                        <br>
                                                        <span class="coverage-badge">
                                                            <i class="fas fa-calendar-check"></i>
                                                            Cobertura: <?= $coverageText ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small style="color: #605e5c;">#<?= $cuota->id ?></small>
                                                </td>
                                                <td class="text-end">
                                                    <strong style="font-variant-numeric: tabular-nums;">
                                                        <?= Yii::$app->formatter->asCurrency($cuota->monto, 'USD') ?>
                                                    </strong>
                                                </td>
                                                <td>
                                                    <?= Yii::$app->formatter->asDate($cuota->fecha_vencimiento, 'php:d/m/Y') ?>
                                                    <?php
                                                    $today = new \DateTime();
                                                    $dueDate = new \DateTime($cuota->fecha_vencimiento);
                                                    if ($dueDate < $today && $cuota->estatus !== 'pagada') {
                                                        $daysOverdue = $today->diff($dueDate)->days;
                                                        echo '<br><small style="color: #a4262c; font-weight: 600;">(' . $daysOverdue . ' días vencida)</small>';
                                                    } elseif ($dueDate >= $today) {
                                                        $daysLeft = $today->diff($dueDate)->days;
                                                        echo '<br><small style="color: #605e5c;">(Faltan ' . $daysLeft . ' días)</small>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <span class="status-pill <?= $statusClass ?>">
                                                        <i class="fas <?= $statusIcon ?>"></i>
                                                        <?= $statusText ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr class="affiliate-subtotal">
                                            <td colspan="4" class="text-end">
                                                <strong>Subtotal <?= Html::encode($group['name']) ?>:</strong>
                                            </td>
                                            <td class="text-end">
                                                <strong><?= Yii::$app->formatter->asCurrency($group['total'], 'USD') ?></strong>
                                            </td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background-color: #003e6c; color: #ffffff;">
                        <td colspan="5" class="text-end" style="padding: 14px 16px;">
                            <strong style="font-size: 14px;">TOTAL GENERAL:</strong>
                        </td>
                        <td class="text-end" style="padding: 14px 16px;">
                            <strong style="font-size: 16px; font-variant-numeric: tabular-nums;">
                                <?= Yii::$app->formatter->asCurrency($grandTotal, 'USD') ?>
                            </strong>
                        </td>
                        <td class="text-center" style="padding: 14px 16px;">
                            <strong><?= $cuotaCount ?> cuotas</strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- ============================================================
     SLIDE-IN PANEL OVERLAY
     ============================================================ -->
<div class="slide-panel-overlay" id="slide-panel-overlay"></div>

<!-- ============================================================
     SLIDE-IN PANEL: REGISTRO DE PAGO (now from the LEFT)
     ============================================================ -->
<div class="slide-panel" id="payment-slide-panel">
    <div class="slide-panel-header">
        <button type="button" class="slide-panel-close" id="slide-panel-close" aria-label="Cerrar">
            <i class="fas fa-times"></i>
        </button>
        <h3>
            <i class="fas fa-credit-card"></i>
            Registro del Pago Corporativo
        </h3>
    </div>

    <div class="slide-panel-body">

        <!-- ============================================================
             SECTION: DATOS DEL PAGO
             ============================================================ -->
        <div class="drawer-section">
            <div class="drawer-section-title">
                <i class="fas fa-info-circle"></i>
                Datos del Pago
            </div>
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'metodo_pago')->dropDownList([
                        'deposito' => 'Depósito Bancario',
                        'efectivo' => 'Efectivo',
                        'otro' => 'Otro Método',
                        'pago-movil' => 'Pago Móvil',
                        'punto-venta' => 'Punto de Venta',
                        'transferencia' => 'Transferencia Bancaria',
                        'zelle' => 'Zelle / Transferencia Internacional',
                    ], ['prompt' => 'Seleccione...'])->label('Método Pago') ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'fecha_pago')->textInput([
                        'class' => 'form-control',
                        'type' => 'date',
                        'placeholder' => 'Fecha del pago',
                        'disabled' => $disabled,
                        'id' => 'fecha-pago',
                    ])->label('Fecha Pago') ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'numero_referencia_pago')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'Nro. de Referencia',
                    ])->label('# Referencia') ?>
                </div>
            </div>
        </div>

        <!-- ============================================================
             SECTION: MONTOS
             ============================================================ -->
        <div class="drawer-section">
            <div class="drawer-section-title">
                <i class="fas fa-dollar-sign"></i>
                Montos
            </div>
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'monto_pagado')->widget(MaskedInput::class, [
                        'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => ',',
                            'autoGroup' => true,
                            'removeMaskOnSubmit' => true,
                            'digits' => 2,
                        ],
                        'options' => [
                            'id' => 'pagos-monto_pagado',
                            'placeholder' => 'Monto en USD',
                            'value' => number_format($grandTotal, 2, '.', ''),
                        ],
                    ])->textInput(['maxlength' => true])->label('Monto Pagado (USD)') ?>
                    <div id="monto-usd-error" class="invalid-feedback d-block"></div>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'tasa')->textInput([
                        'id' => 'pagos-tasa',
                        'placeholder' => 'Tasa BCV',
                    ])->label('Tasa BCV') ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'monto_usd')->textInput([
                        'id' => 'monto-bs-display',
                        'class' => 'form-control',
                        'readonly' => true,
                        'placeholder' => 'Monto en Bs',
                    ])->label('Monto Bs (Referencial)') ?>
                    <?= $form->field($model, 'monto_usd')->hiddenInput(['id' => 'pagos-monto_bs'])->label(false) ?>
                </div>
            </div>
            <small style="color: #605e5c; font-size: 12px; display: block; margin-top: -4px;">
                <i class="fas fa-calculator me-1"></i>
                Total seleccionado: <strong><?= Yii::$app->formatter->asCurrency($grandTotal, 'USD') ?></strong>
            </small>
        </div>

        <!-- ============================================================
             SECTION: DETALLES
             ============================================================ -->
        <div class="drawer-section">
            <div class="drawer-section-title">
                <i class="fas fa-paperclip"></i>
                Detalles Adicionales
            </div>

            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($model, 'observacion')->textarea([
                        'rows' => 3,
                        'readonly' => $disabled,
                        'placeholder' => 'Observaciones adicionales...',
                    ])->label('Observaciones') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <label class="attach-label">
                        Comprobante de Pago
                        <span class="attach-required">*</span>
                    </label>
                    <div class="attach-strip" id="attach-dropzone">
                        <div class="attach-strip-left">
                            <i class="fas fa-paperclip attach-strip-icon"></i>
                            <span class="attach-strip-text" id="attach-filename">Adjuntar imagen · JPG o PNG · Máx. 2 MB</span>
                        </div>
                        <div class="attach-strip-right">
                            <?= $form->field($model, 'imagen_prueba_file')->widget(FileInput::class, [
                                'options' => ['accept' => 'image/*', 'id' => 'pagos-imagen_prueba_file'],
                                'pluginOptions' => [
                                    'showUpload' => false,
                                    'showRemove' => false,
                                    'showCaption' => false,
                                    'showPreview' => false,
                                    'browseLabel' => ' Seleccionar',
                                    'browseIcon' => '<i class="fas fa-folder-open"></i>',
                                    'browseClass' => 'btn btn-outline-primary btn-sm',
                                    'maxFileSize' => 2048,
                                    'msgSizeTooLarge' => 'El archivo "{name}" ({sizeText}) excede el tamaño máximo permitido de {maxSize}.',
                                    'initialPreview' => [],
                                    'initialPreviewAsData' => false,
                                    'overwriteInitial' => true,
                                    'purifyHtml' => true,
                                ],
                            ])->label(false) ?>
                        </div>
                    </div>
                    <div class="attach-error" id="attach-error" style="display: none;">
                        <i class="fas fa-exclamation-circle"></i>
                        Debe adjuntar el comprobante de pago antes de continuar.
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="slide-panel-footer">
        <div class="slide-panel-confirm-hint">
            <i class="fas fa-info-circle"></i>
            <span>Revise los datos antes de confirmar</span>
        </div>
        <button type="button" class="btn btn-secondary" id="slide-panel-cancel">
            <i class="fas fa-times me-2"></i> Cancelar
        </button>
        <?php if (!empty($allCuotas) && $grandTotal > 0): ?>
            <?= Html::submitButton(
                '<i class="fas fa-check-circle me-2"></i> Confirmar Pago',
                [
                    'class' => 'btn btn-danger',
                ]
            ) ?>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================================
     FLOATING ACTION BUTTON — Registrar Pago
     ============================================================ -->
<button type="button" class="fl-fab" id="btn-registrar-pago" aria-label="Registrar Pago">
    <i class="fas fa-credit-card"></i>
    <span>Registrar Pago</span>
    <i class="fas fa-arrow-right" style="opacity: 0.85; font-size: 13px;"></i>
</button>

<?php ActiveForm::end(); ?>