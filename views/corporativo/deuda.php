<?php

use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Pagos $model */
/** @var app\models\Corporativo $corporativo */
/** @var array $allCuotas Array of all pending Cuotas across affiliates >0 */
/** @var float $grandTotal Total sum of pending cuotas >0 */
/** @var array $paymentHistory Array of corporate payments */
/** @var array $affiliatePayments Array of affiliate payments */
/** @var float $totalPaid Total amount paid by corporation */
/** @var int $totalPayments Total number of payments */
/** @var string $lastPaymentDate Last payment date */

$grandTotal = $grandTotal ?? 0;
$paymentHistory = $paymentHistory ?? [];
$affiliatePayments = $affiliatePayments ?? [];
$totalPaid = $totalPaid ?? 0;
$totalPayments = $totalPayments ?? 0;
$lastPaymentDate = $lastPaymentDate ?? null;

// ============================================================
// Group cuotas by a STABLE, DETERMINISTIC month key
// ============================================================
$monthGroups = [];

// Manual Spanish month mapping (works without intl extension)
$months = [
    'January' => 'Enero',
    'February' => 'Febrero',
    'March' => 'Marzo',
    'April' => 'Abril',
    'May' => 'Mayo',
    'June' => 'Junio',
    'July' => 'Julio',
    'August' => 'Agosto',
    'September' => 'Septiembre',
    'October' => 'Octubre',
    'November' => 'Noviembre',
    'December' => 'Diciembre',
];

foreach ($allCuotas as $cuota) {
    $contrato = $cuota->contrato ?? null;
    $userDatos = $contrato->user ?? null;

    if ($contrato && !empty($contrato->fecha_ini)) {
        $baseDateStr = $contrato->fecha_ini;
    } elseif (!empty($cuota->coverage_start)) {
        $baseDateStr = $cuota->coverage_start;
    } elseif (!empty($cuota->fecha_vencimiento)) {
        $baseDateStr = $cuota->fecha_vencimiento;
    } else {
        \Yii::warning("Cuota #{$cuota->id} has no usable date for grouping; skipping.", 'deuda');
        continue;
    }

    try {
        $baseDate = new \DateTime($baseDateStr);
    } catch (\Exception $e) {
        \Yii::warning("Cuota #{$cuota->id} has invalid base date '{$baseDateStr}'; skipping.", 'deuda');
        continue;
    }

    $cuotaNumber  = max(1, (int)$cuota->numero_cuota);
    $offsetMonths = $cuotaNumber - 1;

    $baseDate->modify('first day of this month');

    if ($offsetMonths > 0) {
        $baseDate->modify("+{$offsetMonths} months");
    }

    $baseDate->modify('first day of this month');

    $monthKey     = $baseDate->format('Y-m');
    $englishMonth = $baseDate->format('F');
    $monthName    = $months[$englishMonth] . ' ' . $baseDate->format('Y');
    $monthNumber  = $baseDate->format('m');
    $year         = $baseDate->format('Y');

    $coverageStart = $cuota->coverage_start ?: $cuota->fecha_vencimiento;

    if (!isset($monthGroups[$monthKey])) {
        $monthGroups[$monthKey] = [
            'key'            => $monthKey,
            'name'           => $monthName,
            'year'           => $year,
            'month'          => $monthNumber,
            'cuotas'         => [],
            'total'          => 0,
            'cuota_count'    => 0,
            'affiliates'     => [],
            'coverage_start' => $coverageStart,
        ];
    }

    $cuotaData = [
        'cuota'            => $cuota,
        'affiliate_id'     => $userDatos ? $userDatos->id : null,
        'affiliate_name'   => $userDatos ? $userDatos->nombres . ' ' . $userDatos->apellidos : 'N/A',
        'affiliate_cedula' => $userDatos ? $userDatos->tipo_cedula . '-' . $userDatos->cedula : 'N/A',
        'contrato_nro'     => $contrato ? $contrato->nrocontrato : 'N/A',
        'coverage_start'   => $cuota->coverage_start,
        'coverage_end'     => $cuota->coverage_end,
    ];

    $monthGroups[$monthKey]['cuotas'][] = $cuotaData;
    $monthGroups[$monthKey]['total'] += floatval($cuota->monto);
    $monthGroups[$monthKey]['cuota_count']++;

    if ($userDatos) {
        $affiliateId   = $userDatos->id;
        $affiliateName = $userDatos->nombres . ' ' . $userDatos->apellidos;

        if (!isset($monthGroups[$monthKey]['affiliates'][$affiliateId])) {
            $monthGroups[$monthKey]['affiliates'][$affiliateId] = [
                'id'           => $affiliateId,
                'name'         => $affiliateName,
                'cedula'       => $userDatos->tipo_cedula . '-' . $userDatos->cedula,
                'contrato_nro' => $contrato ? $contrato->nrocontrato : 'N/A',
                'cuotas'       => [],
                'total'        => 0,
            ];
        }
        $monthGroups[$monthKey]['affiliates'][$affiliateId]['cuotas'][] = $cuota;
        $monthGroups[$monthKey]['affiliates'][$affiliateId]['total'] += floatval($cuota->monto);
    }
}

// Sort months chronologically
ksort($monthGroups);

// Sort affiliates alphabetically within each month
foreach ($monthGroups as $monthKey => &$month) {
    uasort($month['affiliates'], function ($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
}
unset($month);

$totalMonths     = count($monthGroups);
$totalCuotas     = count($allCuotas);
$totalAffiliates = count(array_unique(array_reduce($monthGroups, function ($carry, $month) {
    return array_merge($carry, array_keys($month['affiliates']));
}, [])));

$paymentCuotasCount = [];
foreach ($paymentHistory as $payment) {
    $paymentCuotasCount[$payment->id] = \app\models\Cuotas::find()
        ->where(['id_pago' => $payment->id])
        ->count();
}

$this->registerCss(
    <<<CSS
/* ===== MICROSOFT FLUENT DESIGN SYSTEM ===== */
body {
    font-family: "Segoe UI", SegoeUI, "Helvetica Neue", Helvetica, Arial, sans-serif !important;
    font-size: 14px !important;
    color: #323130 !important;
    background-color: #faf9f8 !important;
}

h1 { font-size: 28px !important; font-weight: 600 !important; }
h2 { font-size: 24px !important; font-weight: 600 !important; }
h3 { font-size: 20px !important; font-weight: 600 !important; }
h4 { font-size: 18px !important; font-weight: 600 !important; }
h5 { font-size: 16px !important; font-weight: 600 !important; }

/* ===== BUTTONS ===== */
.btn {
    border-radius: 4px !important;
    padding: 8px 20px !important;
    font-weight: 600 !important;
    font-size: 14px !important;
    min-height: 36px !important;
}

.btn-lg {
    padding: 10px 24px !important;
    font-size: 14px !important;
    min-height: 42px !important;
}

.btn-success {
    background-color: #107c10 !important;
    border-color: #107c10 !important;
    color: #ffffff !important;
}

.btn-success:hover {
    background-color: #0e700e !important;
    border-color: #0e700e !important;
}

.btn-secondary {
    background-color: #f3f2f1 !important;
    border-color: #8a8886 !important;
    color: #323130 !important;
}

.btn-outline-secondary {
    background-color: transparent !important;
    border-color: #8a8886 !important;
    color: #323130 !important;
}

.btn-outline-secondary:hover {
    background-color: #f3f2f1 !important;
    border-color: #8a8886 !important;
    color: #201f1e !important;
}

.btn-outline-primary {
    background-color: transparent !important;
    border-color: #0078d4 !important;
    color: #0078d4 !important;
}

.btn-outline-primary:hover {
    background-color: #e8f4fd !important;
    border-color: #0078d4 !important;
    color: #0078d4 !important;
}

/* ============================================================
   CORPORATE INFO PANEL
   ============================================================ */
.corp-info-card {
    background: #ffffff !important;
    border: 1px solid #edebe9 !important;
    border-radius: 10px !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06) !important;
    margin-bottom: 20px !important;
    overflow: hidden !important;
}

.corp-info-header {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    padding: 14px 20px !important;
    background: #faf9f8 !important;
    border-bottom: 1px solid #edebe9 !important;
    flex-wrap: wrap !important;
    gap: 12px !important;
}

.corp-info-header-left {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    min-width: 0 !important;
}

.corp-info-avatar {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 40px !important;
    height: 40px !important;
    border-radius: 10px !important;
    background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%) !important;
    color: #ffffff !important;
    font-size: 18px !important;
    flex-shrink: 0 !important;
}

.corp-info-title-block {
    display: flex !important;
    flex-direction: column !important;
    line-height: 1.2 !important;
    min-width: 0 !important;
}

.corp-info-title {
    font-size: 16px !important;
    font-weight: 700 !important;
    color: #201f1e !important;
    letter-spacing: -0.01em !important;
    margin: 0 !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
}

.corp-info-rif-pill {
    display: inline-flex !important;
    align-items: center !important;
    gap: 5px !important;
    margin-top: 3px !important;
    padding: 2px 10px !important;
    background: #e8f4fd !important;
    color: #0078d4 !important;
    border: 1px solid #cfe7f9 !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    letter-spacing: 0.03em !important;
    font-variant-numeric: tabular-nums !important;
    white-space: nowrap !important;
    align-self: flex-start !important;
}

.corp-info-rif-pill i { font-size: 10px !important; }

.corp-info-fields {
    display: flex !important;
    align-items: stretch !important;
    flex-wrap: wrap !important;
}

.corp-info-field {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    padding: 14px 20px !important;
    flex: 1 1 0 !important;
    min-width: 200px !important;
    border-right: 1px solid #edebe9 !important;
}

.corp-info-field:last-child { border-right: none !important; }

.corp-info-field-icon {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 34px !important;
    height: 34px !important;
    border-radius: 8px !important;
    background: #f3f2f1 !important;
    color: #605e5c !important;
    font-size: 13px !important;
    flex-shrink: 0 !important;
}

.corp-info-field-body {
    display: flex !important;
    flex-direction: column !important;
    line-height: 1.2 !important;
    min-width: 0 !important;
}

.corp-info-field-label {
    font-size: 10px !important;
    font-weight: 700 !important;
    color: #a19f9d !important;
    text-transform: uppercase !important;
    letter-spacing: 0.06em !important;
}

.corp-info-field-value {
    font-size: 14px !important;
    font-weight: 600 !important;
    color: #201f1e !important;
    margin-top: 2px !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
    font-variant-numeric: tabular-nums !important;
}

.corp-info-field-value.is-muted {
    color: #a19f9d !important;
    font-weight: 500 !important;
    font-style: italic !important;
}

@media (max-width: 992px) {
    .corp-info-field {
        flex: 1 1 50% !important;
        border-bottom: 1px solid #edebe9 !important;
    }
    .corp-info-field:nth-child(2n) { border-right: none !important; }
    .corp-info-field:nth-last-child(-n+2) { border-bottom: none !important; }
}

@media (max-width: 576px) {
    .corp-info-field {
        flex: 1 1 100% !important;
        border-right: none !important;
        border-bottom: 1px solid #edebe9 !important;
    }
    .corp-info-field:last-child { border-bottom: none !important; }
}

/* ============================================================
   MONTH ACCORDION — 2-column grid (no ordinal chip)
   ============================================================ */
.month-accordion {
    margin-bottom: 14px;
    border: 1px solid #edebe9;
    border-radius: 10px;
    overflow: hidden;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.month-accordion:hover { border-color: #d2d0ce; }

.month-accordion.selected {
    border-color: #107c10;
    box-shadow: 0 0 0 1px #107c10, 0 2px 8px rgba(16,124,16,0.15);
}

.month-accordion.expanded { border-color: #0078d4; }

.month-header {
    display: grid;
    grid-template-columns: 1fr auto;
    align-items: center;
    gap: 16px;
    padding: 14px 20px;
    cursor: pointer;
    transition: background-color 0.15s ease;
    user-select: none;
}

.month-content {
    display: none;
    border-top: 1px solid #edebe9;
    background-color: #ffffff;
}

.month-title-block {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    min-width: 0;
}

.month-name {
    font-size: 17px;
    font-weight: 600;
    color: #201f1e;
    letter-spacing: -0.01em;
}

.month-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 500;
    line-height: 1.4;
    white-space: nowrap;
}

.month-pill i { font-size: 11px; }

.month-pill-coverage {
    background-color: #ffffff;
    color: #605e5c;
    border: 1px solid #edebe9;
}

.month-pill-count {
    background-color: #e8f4fd;
    color: #0078d4;
    border: 1px solid #cfe7f9;
}

.month-pill-total {
    background-color: #e7f6e7;
    color: #107c10;
    border: 1px solid #cfe9cf;
    font-weight: 700;
    font-size: 13px;
}

.month-pill-total i { font-size: 12px; }

.month-header-right {
    display: flex;
    align-items: center;
    gap: 14px;
    padding-right: 4px;
}

.month-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
    margin: 0;
    accent-color: #107c10;
}

.month-chevron {
    font-size: 13px;
    color: #a19f9d;
    transition: transform 0.2s ease, color 0.2s ease;
    margin-left: 4px;
}

.month-accordion.expanded .month-chevron {
    transform: rotate(90deg);
    color: #0078d4;
}

/* ===== AFFILIATE TABLE ===== */
.affiliate-table { width: 100%; font-size: 13px; }

.affiliate-table th {
    background-color: #faf9f8 !important;
    color: #605e5c !important;
    font-weight: 600 !important;
    padding: 10px 14px !important;
    font-size: 11px !important;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    border-bottom: 1px solid #edebe9 !important;
}

.affiliate-table td {
    padding: 12px 14px !important;
    border-bottom: 1px solid #f3f2f1 !important;
    vertical-align: middle !important;
    background-color: #ffffff !important;
    color: #323130 !important;
}

.affiliate-table tr:last-child td { border-bottom: none !important; }

.affiliate-group-row { cursor: pointer; transition: background-color 0.15s ease; }
.affiliate-group-row td { font-weight: 600 !important; background-color: #ffffff !important; color: #323130 !important; }
.affiliate-group-row:hover td { background-color: #faf9f8 !important; }

.affiliate-group-row .sub-toggle-icon {
    font-size: 12px;
    transition: transform 0.2s ease;
    display: inline-block;
    color: #0078d4 !important;
}

.affiliate-group-row .sub-toggle-icon.expanded { transform: rotate(90deg); }

.affiliate-name { font-weight: 600; color: #201f1e; }
.affiliate-cedula, .affiliate-contrato { color: #605e5c; font-variant-numeric: tabular-nums; }
.affiliate-total { color: #a4262c; font-weight: 700; font-variant-numeric: tabular-nums; }

/* ===== CUOTA DETAILS ===== */
.affiliate-cuotas-row { background-color: #ffffff !important; }
.affiliate-cuotas-row td { padding: 0 !important; background-color: #ffffff !important; }

.affiliate-cuotas-table {
    width: 100%;
    background-color: #faf9f8 !important;
    border-top: 1px solid #f3f2f1;
}

.affiliate-cuotas-table tr { border-bottom: 1px solid #f3f2f1; }
.affiliate-cuotas-table tr:last-child { border-bottom: none; }

.affiliate-cuotas-table td {
    padding: 12px 14px 12px 56px !important;
    font-size: 13px !important;
    background-color: #faf9f8 !important;
    color: #323130 !important;
    vertical-align: middle;
    border-bottom: none !important;
}

.cuota-anchor-cell {
    padding-left: 20px !important;
    padding-right: 8px !important;
    display: flex;
    align-items: center;
    gap: 12px;
    white-space: nowrap;
}

.cuota-checkbox {
    width: 16px;
    height: 16px;
    cursor: pointer;
    margin: 0;
    accent-color: #107c10;
    flex-shrink: 0;
}

.cuota-number-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 34px;
    height: 26px;
    padding: 0 8px;
    border-radius: 6px;
    background-color: #e8f4fd;
    color: #0078d4;
    font-size: 12px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    border: 1px solid #cfe7f9;
    flex-shrink: 0;
}

.cuota-description { display: flex; flex-direction: column; gap: 4px; min-width: 0; }

.cuota-label {
    font-size: 12px;
    font-weight: 600;
    color: #605e5c;
    letter-spacing: 0.03em;
    text-transform: uppercase;
}

.cuota-coverage-range {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #605e5c;
    font-variant-numeric: tabular-nums;
}

.cuota-coverage-range .range-arrow { color: #a19f9d; font-size: 10px; }
.cuota-coverage-range .range-date { font-weight: 500; }

.cuota-amount {
    text-align: right;
    font-weight: 700;
    font-size: 14px;
    color: #201f1e;
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
}

.cuota-due { text-align: right; white-space: nowrap; }

.cuota-due-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 10px;
    border-radius: 999px;
    background-color: #f3f2f1;
    color: #605e5c;
    font-size: 11px;
    font-weight: 500;
    font-variant-numeric: tabular-nums;
    border: 1px solid #edebe9;
}

.cuota-due-pill i { font-size: 10px; }

.cuota-status { text-align: right; white-space: nowrap; }

.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.02em;
    border: 1px solid transparent;
}

.status-pill i { font-size: 10px; }
.status-pill.status-pending { background-color: #e8f4fd; color: #0078d4; border-color: #cfe7f9; }
.status-pill.status-grace { background-color: #fff4e5; color: #ca5010; border-color: #ffe0b3; }
.status-pill.status-overdue { background-color: #fde7e9; color: #a4262c; border-color: #f5c2c7; }

/* ============================================================
   COMPACT STAT BAR
   ============================================================ */
.payment-summary-bar {
    display: flex !important;
    align-items: stretch !important;
    background: #ffffff !important;
    border-radius: 10px !important;
    padding: 0 !important;
    margin-bottom: 20px !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06) !important;
    border: 1px solid #edebe9 !important;
    overflow: hidden !important;
}

.payment-summary-tile {
    display: flex !important;
    align-items: center !important;
    gap: 14px !important;
    padding: 16px 22px !important;
    flex: 1 1 0 !important;
    min-width: 0 !important;
    border-right: 1px solid #edebe9 !important;
}

.payment-summary-tile:last-child { border-right: none !important; }

.payment-summary-tile .tile-icon {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 40px !important;
    height: 40px !important;
    border-radius: 10px !important;
    background: #e8f4fd !important;
    color: #0078d4 !important;
    font-size: 16px !important;
    flex-shrink: 0 !important;
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
    color: #605e5c !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.payment-summary-tile .tile-value {
    font-size: 20px !important;
    font-weight: 700 !important;
    line-height: 1.1 !important;
    color: #201f1e !important;
    font-variant-numeric: tabular-nums !important;
    margin-top: 2px !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.payment-summary-tile-success .tile-icon { background: #e7f6e7 !important; color: #107c10 !important; }
.payment-summary-tile-success .tile-value { color: #107c10 !important; }

@media (max-width: 992px) {
    .payment-summary-bar { flex-wrap: wrap !important; }
    .payment-summary-tile {
        flex: 1 1 50% !important;
        border-bottom: 1px solid #edebe9 !important;
    }
    .payment-summary-tile:nth-child(2) { border-right: none !important; }
    .payment-summary-tile:nth-child(3),
    .payment-summary-tile:nth-child(4) { border-bottom: none !important; }
}

@media (max-width: 576px) {
    .payment-summary-tile {
        flex: 1 1 100% !important;
        border-right: none !important;
        border-bottom: 1px solid #edebe9 !important;
    }
    .payment-summary-tile:last-child { border-bottom: none !important; }
}

/* ============================================================
   HISTORIAL DE PAGOS
   ============================================================ */
.history-card {
    background: #ffffff !important;
    border-radius: 10px !important;
    border: 1px solid #edebe9 !important;
    border-top: 3px solid #107c10 !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06) !important;
    margin-bottom: 24px !important;
    overflow: hidden !important;
}

.history-card-header {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    padding: 12px 20px !important;
    background: #faf9f8 !important;
    border-bottom: 1px solid #edebe9 !important;
}

.history-header-left {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
}

.history-header-left i { color: #107c10 !important; font-size: 15px !important; }

.history-title {
    font-size: 15px !important;
    font-weight: 700 !important;
    color: #201f1e !important;
    letter-spacing: -0.01em !important;
}

.history-badge {
    display: inline-flex !important;
    align-items: center !important;
    padding: 2px 10px !important;
    background: #e7f6e7 !important;
    color: #107c10 !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    letter-spacing: 0.02em !important;
    border: 1px solid #cfe9cf !important;
}

.history-table {
    width: 100% !important;
    margin: 0 !important;
    border-collapse: collapse !important;
    font-size: 13px !important;
}

.history-table thead th {
    background: #f3f2f1 !important;
    color: #605e5c !important;
    font-weight: 600 !important;
    font-size: 11px !important;
    letter-spacing: 0.05em !important;
    text-transform: uppercase !important;
    padding: 10px 14px !important;
    border-bottom: 1px solid #edebe9 !important;
    text-align: left !important;
    white-space: nowrap !important;
}

.history-table thead th.text-end { text-align: right !important; }
.history-table thead th.text-center { text-align: center !important; }

.history-table tbody td {
    padding: 12px 14px !important;
    border-bottom: 1px solid #edebe9 !important;
    vertical-align: middle !important;
    color: #323130 !important;
}

.history-table tbody tr.history-row:nth-child(odd) { background: #ffffff !important; }
.history-table tbody tr.history-row:nth-child(even) { background: #faf9f8 !important; }
.history-table tbody tr.history-row:hover {
    background: #eef4fb !important;
    transition: background-color 0.15s ease !important;
}
.history-table tbody tr.history-row:last-child td { border-bottom: none !important; }

.history-index { width: 44px !important; }

.history-index-pill {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-width: 26px !important;
    height: 22px !important;
    padding: 0 6px !important;
    background: #e8f4fd !important;
    color: #0078d4 !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    border-radius: 6px !important;
    font-variant-numeric: tabular-nums !important;
}

.history-date { display: flex !important; flex-direction: column !important; line-height: 1.2 !important; }
.history-date-day { font-weight: 600 !important; color: #201f1e !important; font-variant-numeric: tabular-nums !important; }
.history-date-time { font-size: 11px !important; color: #a19f9d !important; font-variant-numeric: tabular-nums !important; }

.history-month-pill {
    display: inline-flex !important;
    align-items: center !important;
    gap: 5px !important;
    padding: 3px 10px !important;
    background: #e8f4fd !important;
    color: #0078d4 !important;
    border: 1px solid #cfe7f9 !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    white-space: nowrap !important;
}

.history-method { color: #605e5c !important; font-size: 12px !important; }
.history-amount {
    font-weight: 700 !important;
    color: #201f1e !important;
    font-variant-numeric: tabular-nums !important;
    white-space: nowrap !important;
}
.history-amount-secondary {
    color: #605e5c !important;
    font-size: 12px !important;
    font-variant-numeric: tabular-nums !important;
    white-space: nowrap !important;
}
.history-rate {
    color: #605e5c !important;
    font-size: 12px !important;
    font-variant-numeric: tabular-nums !important;
}

.history-cuotas-pill {
    display: inline-flex !important;
    align-items: center !important;
    gap: 5px !important;
    padding: 3px 10px !important;
    background: #e8f4fd !important;
    color: #0078d4 !important;
    border: 1px solid #cfe7f9 !important;
    border-radius: 999px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    font-variant-numeric: tabular-nums !important;
}

.status-pill-success { background: #e7f6e7 !important; color: #107c10 !important; border-color: #cfe9cf !important; }
.status-pill-warning { background: #fff4e5 !important; color: #ca5010 !important; border-color: #ffe0b3 !important; }

.history-reference {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}

.history-ref-number {
    color: #323130 !important;
    font-size: 12px !important;
    font-family: "Consolas", "Menlo", monospace !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
    max-width: 110px !important;
}

.history-ref-link {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 24px !important;
    height: 24px !important;
    background: #e8f4fd !important;
    color: #0078d4 !important;
    border-radius: 4px !important;
    text-decoration: none !important;
    font-size: 11px !important;
    transition: background-color 0.15s ease !important;
    flex-shrink: 0 !important;
}

.history-ref-link:hover {
    background: #cfe7f9 !important;
    color: #005a9e !important;
    text-decoration: none !important;
}

@media (max-width: 768px) {
    .history-table { font-size: 12px !important; }
    .history-table thead th, .history-table tbody td { padding: 8px 10px !important; }
    .history-ref-number { max-width: 70px !important; }
}

/* ============================================================
   SECTION HEADER — title + action on the same row
   ============================================================ */
.section-header-action {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 16px !important;
    flex-wrap: wrap !important;
    width: 100% !important;
}

.section-header-action,
.section-header-action *,
.section-header-title,
.section-header-title *,
.section-header-title span,
.section-header-badge,
.section-header-badge * {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
}

.section-header-title {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    min-width: 0 !important;
    margin: 0 !important;
    font-size: 18px !important;
    font-weight: 600 !important;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
}

.section-header-title i {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    font-size: 18px !important;
}

.section-header-title span {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
}

.section-header-badge {
    display: inline-flex !important;
    align-items: center !important;
    padding: 3px 10px !important;
    background: rgba(255, 255, 255, 0.2) !important;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    border-radius: 999px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    letter-spacing: 0.02em !important;
    border: 1px solid rgba(255, 255, 255, 0.25) !important;
}

.clear-selection-btn {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 6px 14px !important;
    background: rgba(255, 255, 255, 0.12) !important;
    border: 1px solid rgba(255, 255, 255, 0.4) !important;
    border-radius: 4px !important;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    transition: background-color 0.15s ease, border-color 0.15s ease, transform 0.15s ease !important;
    white-space: nowrap !important;
    line-height: 1 !important;
}

.clear-selection-btn i {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    font-size: 11px !important;
}

.clear-selection-btn:hover {
    background: rgba(255, 255, 255, 0.22) !important;
    border-color: rgba(255, 255, 255, 0.7) !important;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    transform: translateY(-1px) !important;
}

.clear-selection-btn:active {
    transform: translateY(0) !important;
}

/* ============================================================
   SELECTION INFO PANEL
   ============================================================ */
.selection-info-panel {
    display: none;
    background-color: #ffffff !important;
    border: 1px solid #cfe7f9 !important;
    border-left: 4px solid #107c10 !important;
    border-radius: 8px !important;
    padding: 0 !important;
    margin-bottom: 20px !important;
    box-shadow: 0 2px 8px rgba(16, 124, 16, 0.08) !important;
    overflow: hidden;
}

.selection-info-panel.active {
    display: block !important;
    animation: selectionSlideIn 0.2s ease-out;
}

@keyframes selectionSlideIn {
    from { opacity: 0; transform: translateY(-4px); }
    to   { opacity: 1; transform: translateY(0); }
}

.selection-info-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 20px;
    background-color: #f0f9f0;
    border-bottom: 1px solid #d6ecd6;
    font-size: 13px;
    font-weight: 600;
    color: #107c10;
    letter-spacing: 0.02em;
}

.selection-info-header i { font-size: 14px; margin: 0; }

.selection-info-content {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 20px !important;
    padding: 16px 20px !important;
    flex-wrap: wrap !important;
}

.selection-metrics {
    display: flex;
    align-items: stretch;
    gap: 12px;
    flex: 1 1 auto;
    flex-wrap: wrap;
}

.selection-metric {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 16px;
    background-color: #fafbfc;
    border: 1px solid #edebe9;
    border-radius: 6px;
    min-width: 150px;
    flex: 1 1 0;
    max-width: 260px;
}

.selection-metric-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background-color: #e8f4fd;
    color: #0078d4;
    flex-shrink: 0;
}

.selection-metric-icon i { font-size: 16px; margin: 0; }
.selection-metric-icon.icon-green { background-color: #e7f6e7; color: #107c10; }

.selection-metric-body { display: flex; flex-direction: column; line-height: 1.2; min-width: 0; }

.selection-metric-value {
    font-size: 18px;
    font-weight: 700;
    color: #201f1e;
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.01em;
}

.selection-metric-value.value-green { color: #107c10; }

.selection-metric-label {
    font-size: 11px;
    font-weight: 600;
    color: #605e5c;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-top: 2px;
}

.selection-action { flex: 0 0 auto; }

.selection-pay-btn {
    display: inline-flex !important;
    align-items: center !important;
    gap: 10px !important;
    background-color: #107c10 !important;
    border: 1px solid #107c10 !important;
    color: #ffffff !important;
    font-weight: 600 !important;
    font-size: 15px !important;
    padding: 12px 24px !important;
    border-radius: 4px !important;
    min-height: 48px !important;
    box-shadow: 0 2px 6px rgba(16, 124, 16, 0.25) !important;
    transition: all 0.15s ease !important;
    text-decoration: none !important;
    white-space: nowrap !important;
}

.selection-pay-btn:hover {
    background-color: #0e700e !important;
    border-color: #0e700e !important;
    color: #ffffff !important;
    box-shadow: 0 4px 10px rgba(16, 124, 16, 0.35) !important;
    transform: translateY(-1px);
    text-decoration: none !important;
}

.selection-pay-btn i { font-size: 14px; margin: 0; }

@media (max-width: 768px) {
    .selection-info-content { flex-direction: column !important; align-items: stretch !important; }
    .selection-metric { min-width: 0; max-width: none; flex: 1 1 100%; }
    .selection-action { width: 100%; }
    .selection-pay-btn { width: 100%; justify-content: center; }
}

/* ===== UTILITY ===== */
.text-end { text-align: right !important; }
.text-center { text-align: center !important; }
.text-left { text-align: left !important; }
.fw-bold { font-weight: 700 !important; }
.me-1 { margin-right: 4px !important; }
.me-2 { margin-right: 8px !important; }
.me-3 { margin-right: 12px !important; }
.ms-2 { margin-left: 8px !important; }
.mb-0 { margin-bottom: 0 !important; }
.mb-3 { margin-bottom: 12px !important; }
.mb-4 { margin-bottom: 20px !important; }
.mt-2 { margin-top: 8px !important; }
.mt-3 { margin-top: 12px !important; }
.mt-4 { margin-top: 20px !important; }
.p-0 { padding: 0 !important; }

@media (max-width: 768px) {
    .month-header {
        grid-template-columns: 1fr;
        grid-template-rows: auto auto;
        padding: 12px 14px;
        gap: 10px;
    }
    .month-header-right {
        grid-column: 1;
        justify-content: flex-start;
        flex-wrap: wrap;
        padding-right: 0;
    }
    .affiliate-table th, .affiliate-table td { font-size: 12px !important; padding: 8px !important; }
    .affiliate-cuotas-table td { padding-left: 20px !important; }
    .cuota-anchor-cell { padding-left: 14px !important; }

    .section-header-action { flex-direction: column !important; align-items: flex-start !important; }
    .clear-selection-btn { width: 100% !important; justify-content: center !important; }
}

.ms-panel {
    background: #ffffff !important;
    border-radius: 8px !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
    margin-bottom: 20px !important;
    border: 1px solid #edebe9 !important;
}

.ms-panel-header {
    padding: 16px 20px !important;
    border-bottom: 1px solid #edebe9 !important;
}

.bg-gradient-blue-1 { background: linear-gradient(135deg, #1E90FF 0%, #0078D4 100%) !important; color: #ffffff !important; }
.bg-gradient-blue-2 { background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%) !important; color: #ffffff !important; }
.bg-gradient-blue-3 { background: linear-gradient(135deg, #005A9E 0%, #003E6C 100%) !important; color: #ffffff !important; }
.bg-gradient-blue-4 { background: linear-gradient(135deg, #003E6C 0%, #002D4F 100%) !important; color: #ffffff !important; }
.bg-gradient-green { background: linear-gradient(135deg, #107c10 0%, #B1D34A 100%) !important; color: #ffffff !important; }

.bg-gradient-blue-1 h2, .bg-gradient-blue-1 h3,
.bg-gradient-blue-2 h2, .bg-gradient-blue-2 h3,
.bg-gradient-blue-3 h2, .bg-gradient-blue-3 h3,
.bg-gradient-blue-4 h2, .bg-gradient-blue-4 h3,
.bg-gradient-green h2, .bg-gradient-green h3 {
    color: #ffffff !important;
}

html body .ms-panel-body .month-accordion.month-row-even > .month-header {
    background-color: #eef4fb !important;
}
html body .ms-panel-body .month-accordion.month-row-odd > .month-header {
    background-color: #ffffff !important;
}
html body .ms-panel-body .month-accordion.month-row-even > .month-header:hover {
    background-color: #e4eefa !important;
}
html body .ms-panel-body .month-accordion.month-row-odd > .month-header:hover {
    background-color: #faf9f8 !important;
}

html body .ms-panel-body .month-accordion.selected > .month-header {
    background-color: #e7f6e7 !important;
    border-left: 4px solid #107c10 !important;
}
html body .ms-panel-body .month-accordion.selected > .month-header:hover {
    background-color: #dff0df !important;
}
html body .ms-panel-body .month-accordion.selected .month-name {
    color: #107c10 !important;
}

a.selection-pay-btn,
a.selection-pay-btn:link,
a.selection-pay-btn:visited,
a.selection-pay-btn:hover,
a.selection-pay-btn:focus,
a.selection-pay-btn:active,
.selection-action a.selection-pay-btn,
#pago-parcial-btn,
#pago-parcial-btn:link,
#pago-parcial-btn:visited,
#pago-parcial-btn:hover,
#pago-parcial-btn:focus,
#pago-parcial-btn:active {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    text-decoration: none !important;
}

a.selection-pay-btn span,
a.selection-pay-btn i,
#pago-parcial-btn span,
#pago-parcial-btn i,
#pago-parcial-btn-label {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
}

a.selection-pay-btn.disabled,
a.selection-pay-btn.disabled span,
a.selection-pay-btn.disabled i,
#pago-parcial-btn.disabled,
#pago-parcial-btn.disabled span,
#pago-parcial-btn.disabled i {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
}
CSS
);

$this->registerJs(
    <<<JS
$(document).ready(function() {
    $('.checkbox-cuota').each(function() {
        var currentCheckbox = $(this);
        var rowElement = currentCheckbox.closest('tr');
        var amountText = rowElement.find('.cuota-amount').text();

        var amount = 0;
        if (amountText) {
            var cleaned = amountText.replace('$', '').replace('USD', '').trim();
            cleaned = cleaned.replace(/[^0-9.,-]/g, '');
            cleaned = cleaned.replace(',', '');
            amount = parseFloat(cleaned);
            if (isNaN(amount)) amount = 0;
        }
        currentCheckbox.data('amount', amount);
        currentCheckbox.attr('data-amount', amount);
    });

    $('.month-header').on('click', function(e) {
        if ($(e.target).is('.month-checkbox') || $(e.target).closest('.month-checkbox').length) {
            return;
        }
        var accordion = $(this).closest('.month-accordion');
        var targetContent = $(this).next('.month-content');

        targetContent.slideToggle(200, function() {
            if ($(this).is(':visible')) {
                accordion.addClass('expanded');
            } else {
                accordion.removeClass('expanded');
            }
        });
    });

    $(document).on('click', '.affiliate-group-row', function(e) {
        if ($(e.target).is('.checkbox-cuota') || $(e.target).closest('.checkbox-cell').length) {
            return;
        }
        var targetRow = $(this).next('.affiliate-cuotas-row');
        var icon = $(this).find('.sub-toggle-icon');
        targetRow.toggle();
        if (targetRow.is(':visible')) {
            icon.addClass('expanded');
        } else {
            icon.removeClass('expanded');
        }
    });

    $('.month-checkbox').on('change', function() {
        var monthKey = $(this).data('month-key');
        var isChecked = $(this).prop('checked');
        var monthAccordion = $(this).closest('.month-accordion');

        if (isChecked) {
            monthAccordion.addClass('selected');
        } else {
            monthAccordion.removeClass('selected');
        }

        $('.checkbox-cuota[data-month-key="' + monthKey + '"]').prop('checked', isChecked).trigger('change');
        updateSelectionInfo();
    });

    $(document).on('change', '.checkbox-cuota', function() {
        var monthKey = $(this).data('month-key');
        var monthCheckbox = $('.month-checkbox[data-month-key="' + monthKey + '"]');
        var allCuotasInMonth = $('.checkbox-cuota[data-month-key="' + monthKey + '"]');
        var checkedCuotasInMonth = $('.checkbox-cuota[data-month-key="' + monthKey + '"]:checked');

        if (checkedCuotasInMonth.length === allCuotasInMonth.length && allCuotasInMonth.length > 0) {
            monthCheckbox.prop('checked', true);
            monthCheckbox.closest('.month-accordion').addClass('selected');
        } else if (checkedCuotasInMonth.length === 0) {
            monthCheckbox.prop('checked', false);
            monthCheckbox.closest('.month-accordion').removeClass('selected');
        } else {
            monthCheckbox.prop('checked', false);
            monthCheckbox.closest('.month-accordion').addClass('selected');
        }

        updateSelectionInfo();
    });

    $('#clear-selection').on('click', function() {
        $('.month-checkbox').prop('checked', false);
        $('.checkbox-cuota').prop('checked', false);
        $('.month-accordion').removeClass('selected');
        updateSelectionInfo();
    });

    function updateSelectionInfo() {
        var selectedCuotas = $('.checkbox-cuota:checked');
        var selectedMonths = $('.month-checkbox:checked');
        var selectedCount = selectedCuotas.length;
        var selectedTotal = 0;

        selectedCuotas.each(function() {
            var currentCheckbox = $(this);
            var amount = currentCheckbox.data('amount');

            if (amount === undefined || isNaN(amount)) {
                var rowElement = currentCheckbox.closest('tr');
                var amountText = rowElement.find('.cuota-amount').text();
                if (amountText) {
                    var cleaned = amountText.replace('$', '').replace('USD', '').trim();
                    cleaned = cleaned.replace(/[^0-9.,-]/g, '');
                    cleaned = cleaned.replace(',', '');
                    amount = parseFloat(cleaned);
                    if (isNaN(amount)) amount = 0;
                    currentCheckbox.data('amount', amount);
                } else {
                    amount = 0;
                }
            }

            if (!isNaN(amount)) {
                selectedTotal += amount;
            }
        });

        if (selectedCount > 0) {
            $('#selection-info-panel').addClass('active');
            $('#selected-months').text(selectedMonths.length);
            $('#selected-count').text(selectedCount);
            $('#selected-total').text(selectedTotal.toFixed(2));

            var paymentBtn = $('#pago-parcial-btn');
            paymentBtn.find('#pago-parcial-label').text(
                'Pagar ' + selectedCount + ' cuota(s) - $' + selectedTotal.toFixed(2)
            );

            var selectedIds = [];
            selectedCuotas.each(function() {
                var cuotaId = $(this).val();
                if (cuotaId && cuotaId !== '') {
                    selectedIds.push(cuotaId);
                }
            });

            if (selectedIds.length > 0) {
                var baseUrl = $('#pago-parcial-base-url').val();
                var newUrl = baseUrl + '&cuotas=' + selectedIds.join(',');
                paymentBtn.attr('href', newUrl);
                paymentBtn.removeClass('disabled');
                paymentBtn.prop('disabled', false);
            } else {
                paymentBtn.addClass('disabled');
                paymentBtn.prop('disabled', true);
                paymentBtn.attr('href', '#');
            }
        } else {
            $('#selection-info-panel').removeClass('active');
        }
    }

    updateSelectionInfo();
});
JS
);
?>

<div class="row">
    <div class="col-xl-12 col-md-12">

        <?php if ($grandTotal == 0 && empty($paymentHistory)): ?>

            <div class="ms-panel ms-panel-fh">
                <div class="ms-panel-body">
                    <div class="no-deuda-message">
                        <div class="no-deuda-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h1 class="no-deuda-title">Estado financiero actualizado</h1>
                        <p class="no-deuda-subtitle"><strong><?= Html::encode($corporativo->nombre) ?></strong></p>
                        <p><span class="text-white">Se encuentra al día con todos sus compromisos financieros.</span></p>
                        <div class="mt-4">
                            <?= Html::a(
                                '<i class="fas fa-arrow-left me-2"></i> Volver al Corporativo',
                                ['view', 'id' => $corporativo->id],
                                ['class' => 'btn btn-light btn-lg']
                            ) ?>
                            <?= Html::a(
                                '<i class="fas fa-list me-2"></i> Ver Todos los Corporativos',
                                ['index'],
                                ['class' => 'btn btn-outline-light btn-lg ms-2']
                            ) ?>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>

            <input type="hidden" id="corporativo-id" value="<?= $corporativo->id ?>">
            <input type="hidden" id="pago-parcial-base-url" value="<?= \yii\helpers\Url::to(['pagos-parcial', 'id' => $corporativo->id]) ?>">

            <div class="ms-panel ms-panel-fh">
                <div class="ms-panel-header d-flex justify-content-between align-items-center flex-wrap">
                    <h1 style="font-size: 24px !important; font-weight: 700 !important; margin-bottom: 0 !important;">
                        <i class="fas fa-building me-2"></i>Gestión de Pagos - <?= Html::encode($corporativo->nombre) ?>
                    </h1>
                </div>

                <div class="ms-panel-body">

                    <!-- ============================================================
                         INFORMACIÓN DEL CORPORATIVO — compact strip
                         ============================================================ -->
                    <div class="corp-info-card">
                        <div class="corp-info-header">
                            <div class="corp-info-header-left">
                                <div class="corp-info-avatar">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="corp-info-title-block">
                                    <span class="corp-info-title"><?= Html::encode($corporativo->nombre) ?></span>
                                    <?php if (!empty($corporativo->rif)): ?>
                                        <span class="corp-info-rif-pill">
                                            <i class="fas fa-id-card"></i>
                                            <?= Html::encode($corporativo->rif) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="corp-info-fields">
                            <div class="corp-info-field">
                                <div class="corp-info-field-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="corp-info-field-body">
                                    <span class="corp-info-field-label">Email</span>
                                    <span class="corp-info-field-value">
                                        <?= !empty($corporativo->email) ? Html::encode($corporativo->email) : '—' ?>
                                    </span>
                                </div>
                            </div>

                            <div class="corp-info-field">
                                <div class="corp-info-field-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="corp-info-field-body">
                                    <span class="corp-info-field-label">Teléfono</span>
                                    <span class="corp-info-field-value">
                                        <?= !empty($corporativo->telefono) ? Html::encode($corporativo->telefono) : '—' ?>
                                    </span>
                                </div>
                            </div>

                            <div class="corp-info-field">
                                <div class="corp-info-field-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="corp-info-field-body">
                                    <span class="corp-info-field-label">Dirección</span>
                                    <span class="corp-info-field-value"
                                        title="<?= Html::encode($corporativo->direccion ?? '') ?>">
                                        <?= !empty($corporativo->direccion) ? Html::encode($corporativo->direccion) : '—' ?>
                                    </span>
                                </div>
                            </div>

                            <div class="corp-info-field">
                                <div class="corp-info-field-icon">
                                    <i class="fas fa-history"></i>
                                </div>
                                <div class="corp-info-field-body">
                                    <span class="corp-info-field-label">Último Pago</span>
                                    <span class="corp-info-field-value <?= !$lastPaymentDate ? 'is-muted' : '' ?>">
                                        <?= $lastPaymentDate
                                            ? Yii::$app->formatter->asDate($lastPaymentDate, 'php:d/m/Y')
                                            : 'Sin pagos registrados' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================
                         COMPACT STAT BAR
                         ============================================================ -->
                    <div class="payment-summary-bar">
                        <div class="payment-summary-tile">
                            <div class="tile-icon"><i class="fas fa-users"></i></div>
                            <div class="tile-body">
                                <span class="tile-label">Afiliados con Deuda</span>
                                <span class="tile-value"><?= $totalAffiliates ?></span>
                            </div>
                        </div>
                        <div class="payment-summary-tile">
                            <div class="tile-icon"><i class="fas fa-calendar-alt"></i></div>
                            <div class="tile-body">
                                <span class="tile-label">Meses con Deuda</span>
                                <span class="tile-value"><?= $totalMonths ?></span>
                            </div>
                        </div>
                        <div class="payment-summary-tile">
                            <div class="tile-icon"><i class="fas fa-exclamation-circle"></i></div>
                            <div class="tile-body">
                                <span class="tile-label">Deuda Pendiente</span>
                                <span class="tile-value">$<?= number_format($grandTotal, 2, '.', ',') ?> USD</span>
                            </div>
                        </div>
                        <div class="payment-summary-tile payment-summary-tile-success">
                            <div class="tile-icon"><i class="fas fa-check-circle"></i></div>
                            <div class="tile-body">
                                <span class="tile-label">Total Pagado</span>
                                <span class="tile-value">$<?= number_format($totalPaid, 2, '.', ',') ?> USD</span>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================
                         HISTORIAL DE PAGOS
                         ============================================================ -->
                    <?php if (!empty($paymentHistory)): ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="history-card">
                                    <div class="history-card-header">
                                        <div class="history-header-left">
                                            <i class="fas fa-history"></i>
                                            <span class="history-title">Historial de Pagos</span>
                                            <span class="history-badge"><?= $totalPayments ?> pagos</span>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="history-table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 44px;">#</th>
                                                    <th style="width: 130px;">Fecha de Pago</th>
                                                    <th style="width: 130px;">Mes Pagado</th>
                                                    <th>Método de Pago</th>
                                                    <th class="text-end" style="width: 130px;">Monto Pagado</th>
                                                    <th class="text-end" style="width: 110px;">Monto Bs</th>
                                                    <th class="text-end" style="width: 110px;">Tasa</th>
                                                    <th class="text-center" style="width: 90px;">Cuotas</th>
                                                    <th style="width: 130px;">Estado</th>
                                                    <th style="width: 160px;">Referencia</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $paymentCounter = 1;
                                                foreach ($paymentHistory as $payment):
                                                    $paymentDate = new \DateTime($payment->fecha_pago);
                                                    $paidMonth = $paymentDate->format('F Y');
                                                    $paidCuotas = $paymentCuotasCount[$payment->id] ?? 0;
                                                    $montoPagadoUSD = floatval($payment->monto_pagado);
                                                    $montoBs = floatval($payment->monto_usd);
                                                    $tasaCalculada = ($montoPagadoUSD > 0) ? $montoBs / $montoPagadoUSD : 0;
                                                ?>
                                                    <tr class="history-row">
                                                        <td class="text-center history-index">
                                                            <span class="history-index-pill"><?= $paymentCounter++ ?></span>
                                                        </td>
                                                        <td>
                                                            <div class="history-date">
                                                                <span class="history-date-day"><?= Yii::$app->formatter->asDate($payment->fecha_pago, 'php:d/m/Y') ?></span>
                                                                <span class="history-date-time"><?= Yii::$app->formatter->asTime($payment->created_at, 'php:H:i') ?></span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="history-month-pill">
                                                                <i class="far fa-calendar-alt"></i>
                                                                <?= $paidMonth ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="history-method"><?= Html::encode($payment->metodo_pago) ?></span>
                                                        </td>
                                                        <td class="text-end">
                                                            <span class="history-amount">$<?= number_format($montoPagadoUSD, 2, '.', ',') ?></span>
                                                        </td>
                                                        <td class="text-end">
                                                            <span class="history-amount-secondary"><?= number_format($montoBs, 2, '.', ',') ?> Bs</span>
                                                        </td>
                                                        <td class="text-end">
                                                            <span class="history-rate"><?= number_format($tasaCalculada, 2, '.', ',') ?></span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="history-cuotas-pill">
                                                                <i class="fas fa-file-invoice"></i>
                                                                <?= $paidCuotas ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php if ($payment->estatus === 'Conciliado'): ?>
                                                                <span class="status-pill status-pill-success">
                                                                    <i class="fas fa-check-circle"></i>
                                                                    Conciliado
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="status-pill status-pill-warning">
                                                                    <i class="fas fa-clock"></i>
                                                                    Por Conciliar
                                                                </span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div class="history-reference">
                                                                <span class="history-ref-number"><?= Html::encode($payment->numero_referencia_pago) ?></span>
                                                                <?php if ($payment->imagen_prueba): ?>
                                                                    <a href="<?= $payment->imagen_prueba ?>" target="_blank"
                                                                        class="history-ref-link" title="Ver comprobante">
                                                                        <i class="fas fa-file-image"></i>
                                                                    </a>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- ============================================================
                         MONTH ACCORDION — no ordinal chip
                         ============================================================ -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="ms-panel border-warning mb-4">
                                <div class="ms-panel-header bg-gradient-blue-3">
                                    <div class="section-header-action">
                                        <div class="section-header-title">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span>Cuotas Pendientes por Mes de Cobertura</span>
                                            <span class="section-header-badge"><?= $totalMonths ?> meses</span>
                                        </div>
                                        <button type="button" class="clear-selection-btn" id="clear-selection">
                                            <i class="fas fa-times"></i>
                                            Limpiar Selección
                                        </button>
                                    </div>
                                </div>
                                <div class="ms-panel-body">

                                    <?php if (empty($monthGroups)): ?>
                                        <div class="alert alert-success text-center">
                                            <i class="fas fa-check-circle me-2"></i>
                                            No hay cuotas pendientes. El corporativo está al día con sus pagos.
                                        </div>
                                    <?php else: ?>

                                        <?php
                                        $rowIndex = 0;
                                        foreach ($monthGroups as $monthKey => $month):
                                            $rowIndex++;
                                            $rowParity = ($rowIndex % 2 === 0) ? 'even' : 'odd';

                                            $firstCuota = $month['cuotas'][0]['cuota'] ?? null;
                                            $rangeStart = $firstCuota && !empty($firstCuota->coverage_start)
                                                ? Yii::$app->formatter->asDate($firstCuota->coverage_start, 'php:d/m/Y')
                                                : null;
                                            $rangeEnd = $firstCuota && !empty($firstCuota->coverage_end)
                                                ? Yii::$app->formatter->asDate($firstCuota->coverage_end, 'php:d/m/Y')
                                                : null;
                                        ?>
                                            <div class="month-accordion month-row-<?= $rowParity ?>" data-month-key="<?= $monthKey ?>">
                                                <div class="month-header">
                                                    <div class="month-title-block">
                                                        <span class="month-name"><?= Html::encode($month['name']) ?></span>

                                                        <?php if ($rangeStart && $rangeEnd): ?>
                                                            <span class="month-pill month-pill-coverage">
                                                                <i class="fas fa-calendar-alt"></i>
                                                                <?= $rangeStart ?> <span class="range-arrow">→</span> <?= $rangeEnd ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="month-header-right">
                                                        <span class="month-pill month-pill-count">
                                                            <i class="fas fa-file-invoice"></i>
                                                            <?= $month['cuota_count'] ?> cuota(s)
                                                        </span>
                                                        <span class="month-pill month-pill-total">
                                                            <i class="fas fa-dollar-sign"></i>
                                                            <?= number_format($month['total'], 2, '.', ',') ?> USD
                                                        </span>
                                                        <input type="checkbox" class="month-checkbox" data-month-key="<?= $monthKey ?>">
                                                        <i class="fas fa-chevron-right month-chevron"></i>
                                                    </div>
                                                </div>
                                                <div class="month-content">
                                                    <div class="table-responsive">
                                                        <table class="affiliate-table">
                                                            <thead>
                                                                <tr>
                                                                    <th style="width: 40px;"></th>
                                                                    <th>Afiliado</th>
                                                                    <th>Cédula</th>
                                                                    <th>Contrato</th>
                                                                    <th class="text-end">Total</th>
                                                                    <th style="width: 100px;" class="text-center">Cuotas</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                foreach ($month['affiliates'] as $affiliateId => $affiliate):
                                                                    $cuotasList = $affiliate['cuotas'];
                                                                    usort($cuotasList, function ($a, $b) {
                                                                        return strtotime($a->fecha_vencimiento) - strtotime($b->fecha_vencimiento);
                                                                    });
                                                                ?>
                                                                    <tr class="affiliate-group-row">
                                                                        <td class="text-center">
                                                                            <i class="fas fa-chevron-right sub-toggle-icon"></i>
                                                                        </td>
                                                                        <td>
                                                                            <span class="affiliate-name"><?= Html::encode($affiliate['name']) ?></span>
                                                                        </td>
                                                                        <td class="affiliate-cedula"><?= Html::encode($affiliate['cedula']) ?></td>
                                                                        <td class="affiliate-contrato"><?= Html::encode($affiliate['contrato_nro']) ?></td>
                                                                        <td class="text-end">
                                                                            <span class="affiliate-total">$<?= number_format($affiliate['total'], 2, '.', ',') ?> USD</span>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <span class="badge badge-primary"><?= count($cuotasList) ?></span>
                                                                        </td>
                                                                    </tr>
                                                                    <tr class="affiliate-cuotas-row" style="display: none;">
                                                                        <td colspan="6" class="p-0">
                                                                            <table class="affiliate-cuotas-table">
                                                                                <tbody>
                                                                                    <?php foreach ($cuotasList as $cuota):
                                                                                        $statusClass = 'status-pending';
                                                                                        $statusIcon  = 'fa-clock';
                                                                                        $statusText  = 'Pendiente';

                                                                                        if ($cuota->estatus === 'vencida') {
                                                                                            $statusClass = 'status-overdue';
                                                                                            $statusIcon  = 'fa-exclamation-circle';
                                                                                            $statusText  = 'Vencida';
                                                                                        } elseif ($cuota->estatus === 'en_gracias') {
                                                                                            $statusClass = 'status-grace';
                                                                                            $statusIcon  = 'fa-hourglass-half';
                                                                                            $statusText  = 'En Gracia';
                                                                                        }

                                                                                        $cuotaNumber = (int)$cuota->numero_cuota;
                                                                                    ?>
                                                                                        <tr>
                                                                                            <td class="cuota-anchor-cell">
                                                                                                <input type="checkbox" class="checkbox-cuota"
                                                                                                    value="<?= $cuota->id ?>"
                                                                                                    data-month-key="<?= $monthKey ?>"
                                                                                                    data-cuota-id="<?= $cuota->id ?>">
                                                                                                <span class="cuota-number-pill">#<?= $cuotaNumber ?></span>
                                                                                            </td>

                                                                                            <td colspan="3">
                                                                                                <div class="cuota-description">
                                                                                                    <span class="cuota-label">Cuota <?= $cuotaNumber ?></span>
                                                                                                    <?php if (!empty($cuota->coverage_start) && !empty($cuota->coverage_end)): ?>
                                                                                                        <span class="cuota-coverage-range">
                                                                                                            <i class="fas fa-calendar-alt" style="color:#a19f9d;"></i>
                                                                                                            <span class="range-date"><?= Yii::$app->formatter->asDate($cuota->coverage_start, 'php:d/m/Y') ?></span>
                                                                                                            <span class="range-arrow">→</span>
                                                                                                            <span class="range-date"><?= Yii::$app->formatter->asDate($cuota->coverage_end, 'php:d/m/Y') ?></span>
                                                                                                        </span>
                                                                                                    <?php endif; ?>
                                                                                                </div>
                                                                                            </td>

                                                                                            <td class="cuota-amount cuota-amount-cell" data-amount="<?= $cuota->monto ?>">
                                                                                                $<?= number_format($cuota->monto, 2, '.', ',') ?> USD
                                                                                            </td>

                                                                                            <td class="cuota-due">
                                                                                                <span class="cuota-due-pill">
                                                                                                    <i class="fas fa-hourglass-end"></i>
                                                                                                    Vence <?= Yii::$app->formatter->asDate($cuota->fecha_vencimiento, 'php:d/m/Y') ?>
                                                                                                </span>
                                                                                            </td>

                                                                                            <td class="cuota-status">
                                                                                                <span class="status-pill <?= $statusClass ?>">
                                                                                                    <i class="fas <?= $statusIcon ?>"></i>
                                                                                                    <?= $statusText ?>
                                                                                                </span>
                                                                                            </td>
                                                                                        </tr>
                                                                                    <?php endforeach; ?>
                                                                                </tbody>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>

                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================
                         SELECTION INFO PANEL
                         ============================================================ -->
                    <div class="selection-info-panel" id="selection-info-panel">
                        <div class="selection-info-header">
                            <i class="fas fa-check-circle"></i>
                            <span>Selección activa</span>
                        </div>
                        <div class="selection-info-content">
                            <div class="selection-metrics">
                                <div class="selection-metric">
                                    <div class="selection-metric-icon">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                    <div class="selection-metric-body">
                                        <span class="selection-metric-value" id="selected-months">0</span>
                                        <span class="selection-metric-label">Mes(es)</span>
                                    </div>
                                </div>

                                <div class="selection-metric">
                                    <div class="selection-metric-icon">
                                        <i class="fas fa-file-invoice"></i>
                                    </div>
                                    <div class="selection-metric-body">
                                        <span class="selection-metric-value" id="selected-count">0</span>
                                        <span class="selection-metric-label">Cuota(s)</span>
                                    </div>
                                </div>

                                <div class="selection-metric">
                                    <div class="selection-metric-icon icon-green">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                    <div class="selection-metric-body">
                                        <span class="selection-metric-value value-green">
                                            $<span id="selected-total">0.00</span>
                                        </span>
                                        <span class="selection-metric-label">Total a pagar (USD)</span>
                                    </div>
                                </div>
                            </div>

                            <div class="selection-action">
                                <?= Html::a(
                                    '<span id="pago-parcial-label">Pagar Seleccionadas</span> <i class="fas fa-arrow-right"></i>',
                                    '#',
                                    [
                                        'class' => 'selection-pay-btn',
                                        'id' => 'pago-parcial-btn',
                                        'style' => 'color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;'
                                    ]
                                ) ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-4 d-flex justify-content-center gap-3">
                        <?= Html::a(
                            '<i class="fas fa-arrow-left me-2"></i> Volver al Corporativo',
                            ['view', 'id' => $corporativo->id],
                            ['class' => 'btn btn-secondary btn-lg']
                        ) ?>
                    </div>
                </div>
            </div>

        <?php endif; ?>

    </div>
</div>