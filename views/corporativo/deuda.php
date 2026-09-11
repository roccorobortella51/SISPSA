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
// CHANGED: Group cuotas by coverage_start instead of fecha_vencimiento
// ============================================================
$monthGroups = [];

foreach ($allCuotas as $cuota) {
    // Use coverage_start for grouping
    $coverageStart = $cuota->coverage_start;

    // Fallback to fecha_vencimiento if coverage_start is null
    if (empty($coverageStart)) {
        $coverageStart = $cuota->fecha_vencimiento;
        \Yii::warning("Cuota #{$cuota->id} has no coverage_start, using fecha_vencimiento: {$coverageStart}", 'deuda');
    }

    $coverageDate = new \DateTime($coverageStart);
    $monthKey = $coverageDate->format('Y-m');

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

    $englishMonth = $coverageDate->format('F');
    $monthName = $months[$englishMonth] . ' ' . $coverageDate->format('Y');
    $monthNumber = $coverageDate->format('m');
    $year = $coverageDate->format('Y');

    $contrato = $cuota->contrato ?? null;
    $userDatos = $contrato->user ?? null;

    if (!isset($monthGroups[$monthKey])) {
        $monthGroups[$monthKey] = [
            'key' => $monthKey,
            'name' => $monthName,
            'year' => $year,
            'month' => $monthNumber,
            'cuotas' => [],
            'total' => 0,
            'cuota_count' => 0,
            'affiliates' => [],
            'coverage_start' => $coverageStart, // Added for reference
        ];
    }

    // Store cuota with affiliate info
    $cuotaData = [
        'cuota' => $cuota,
        'affiliate_id' => $userDatos ? $userDatos->id : null,
        'affiliate_name' => $userDatos ? $userDatos->nombres . ' ' . $userDatos->apellidos : 'N/A',
        'affiliate_cedula' => $userDatos ? $userDatos->tipo_cedula . '-' . $userDatos->cedula : 'N/A',
        'contrato_nro' => $contrato ? $contrato->nrocontrato : 'N/A',
        'coverage_start' => $coverageStart,
        'coverage_end' => $cuota->coverage_end,
    ];

    $monthGroups[$monthKey]['cuotas'][] = $cuotaData;
    $monthGroups[$monthKey]['total'] += floatval($cuota->monto);
    $monthGroups[$monthKey]['cuota_count']++;

    // Group by affiliate for detail view
    if ($userDatos) {
        $affiliateId = $userDatos->id;
        $affiliateName = $userDatos->nombres . ' ' . $userDatos->apellidos;

        if (!isset($monthGroups[$monthKey]['affiliates'][$affiliateId])) {
            $monthGroups[$monthKey]['affiliates'][$affiliateId] = [
                'id' => $affiliateId,
                'name' => $affiliateName,
                'cedula' => $userDatos->tipo_cedula . '-' . $userDatos->cedula,
                'contrato_nro' => $contrato ? $contrato->nrocontrato : 'N/A',
                'cuotas' => [],
                'total' => 0,
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

// Calculate totals
$totalMonths = count($monthGroups);
$totalCuotas = count($allCuotas);
$totalAffiliates = count(array_unique(array_reduce($monthGroups, function ($carry, $month) {
    return array_merge($carry, array_keys($month['affiliates']));
}, [])));

// Calculate paid cuotas count per payment (store in temporary array)
$paymentCuotasCount = [];
foreach ($paymentHistory as $payment) {
    $paymentCuotasCount[$payment->id] = \app\models\Cuotas::find()
        ->where(['id_pago' => $payment->id])
        ->count();
}

// ============================================================
// ENHANCED: Add coverage period badge to month header
// ============================================================
// Register CSS
$this->registerCss(
    <<<CSS
/* ===== MICROSOFT FLUENT DESIGN SYSTEM ===== */
body {
    font-family: "Segoe UI", SegoeUI, "Helvetica Neue", Helvetica, Arial, sans-serif !important;
    font-size: 14px !important;
    color: #323130 !important;
    background-color: #faf9f8 !important;
}

/* ===== TYPOGRAPHY ===== */
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

/* ===== MONTH ACCORDION ===== */
.month-accordion {
    margin-bottom: 16px;
    border: 1px solid #edebe9;
    border-radius: 12px;
    overflow: hidden;
    background: #ffffff;
    transition: all 0.2s ease;
}

.month-accordion.selected {
    border-color: #0078d4;
    border-width: 2px;
    box-shadow: 0 2px 8px rgba(0,120,212,0.2);
}

.month-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    background-color: #faf9f8;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.month-header:hover {
    background-color: #f3f2f1;
}

.month-header-left {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.month-header-left .toggle-icon {
    font-size: 16px;
    transition: transform 0.2s ease;
    color: #0078d4;
}

.month-header-left .toggle-icon.expanded {
    transform: rotate(90deg);
}

.month-name {
    font-size: 18px;
    font-weight: 600;
    color: #323130;
}

.month-badge {
    background-color: #e8f4fd;
    color: #0078d4;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
}

.month-badge-coverage {
    background-color: #e8f4fd;
    color: #107c10;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.month-header-right {
    display: flex;
    align-items: center;
    gap: 24px;
    flex-wrap: wrap;
}

.month-stats {
    display: flex;
    align-items: baseline;
    gap: 16px;
}

.month-stats .cuota-count {
    font-size: 13px;
    color: #605e5c;
}

.month-stats .month-total {
    font-size: 20px;
    font-weight: 700;
    color: #107c10;
}

.month-checkbox {
    width: 20px;
    height: 20px;
    cursor: pointer;
    margin: 0;
}

.month-content {
    display: none;
    border-top: 1px solid #edebe9;
    background-color: #ffffff;
}

/* ===== AFFILIATE TABLE INSIDE MONTH ===== */
.affiliate-table {
    width: 100%;
    font-size: 13px;
}

.affiliate-table th {
    background-color: #e8f4fd !important;
    color: #323130 !important;
    font-weight: 600 !important;
    padding: 12px 12px !important;
    font-size: 13px !important;
    border-bottom: 1px solid #c8e6f5 !important;
}

.affiliate-table td {
    padding: 10px 12px !important;
    border-bottom: 1px solid #edebe9 !important;
    vertical-align: middle !important;
    background-color: #ffffff !important;
    color: #323130 !important;
}

.affiliate-table tr:last-child td {
    border-bottom: none !important;
}

.affiliate-group-row {
    background-color: #faf9f8 !important;
    cursor: pointer;
}

.affiliate-group-row td {
    font-weight: 600 !important;
    background-color: #faf9f8 !important;
    color: #323130 !important;
}

.affiliate-group-row:hover td {
    background-color: #f3f2f1 !important;
}

.affiliate-group-row .sub-toggle-icon {
    font-size: 14px;
    transition: transform 0.2s ease;
    display: inline-block;
    color: #0078d4 !important;
}

.affiliate-group-row .sub-toggle-icon.expanded {
    transform: rotate(90deg);
}

.affiliate-cuotas-row {
    background-color: #ffffff !important;
}

.affiliate-cuotas-row td {
    padding: 0 !important;
    background-color: #ffffff !important;
}

.affiliate-cuotas-table {
    width: 100%;
    background-color: #faf9f8 !important;
}

.affiliate-cuotas-table td {
    padding: 10px 12px 10px 48px !important;
    border-bottom: 1px solid #edebe9 !important;
    font-size: 13px !important;
    background-color: #faf9f8 !important;
    color: #323130 !important;
}

.affiliate-cuotas-table tr:last-child td {
    border-bottom: none !important;
}

.affiliate-cuotas-table .text-muted {
    color: #605e5c !important;
}

/* ===== COVERAGE PERIOD DISPLAY ===== */
.coverage-period {
    font-size: 12px;
    color: #605e5c;
    background-color: #f3f2f1;
    padding: 2px 10px;
    border-radius: 12px;
    display: inline-block;
}

/* ===== STATUS STYLES ===== */
.status-overdue { color: #d13438 !important; font-weight: 600 !important; }
.status-grace { color: #ff8c00 !important; font-weight: 600 !important; }
.status-pending { color: #107c10 !important; font-weight: 600 !important; }
.status-paid { color: #107c10 !important; font-weight: 600 !important; }

/* ===== SUMMARY CARDS ===== */
.summary-card {
    background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%) !important;
    border-radius: 12px !important;
    padding: 20px !important;
    margin-bottom: 20px !important;
    color: white !important;
    text-align: center;
}

.summary-number {
    font-size: 32px !important;
    font-weight: 700 !important;
    line-height: 1.2 !important;
}

.summary-label {
    font-size: 13px !important;
    opacity: 0.9 !important;
    margin-top: 6px !important;
}

.summary-card-success {
    background: linear-gradient(135deg, #107c10 0%, #B9D750 100%) !important;
    border-radius: 12px !important;
    padding: 20px !important;
    margin-bottom: 20px !important;
    color: white !important;
    text-align: center;
}

/* ===== PAYMENT HISTORY TABLE ===== */
.payment-history-table {
    font-size: 13px !important;
}

.payment-history-table th {
    background-color: #e8f4fd !important;
    color: #323130 !important;
    font-weight: 600 !important;
    padding: 12px 12px !important;
    font-size: 13px !important;
}

.payment-history-table td {
    padding: 10px 12px !important;
    vertical-align: middle !important;
    background-color: #ffffff !important;
    color: #323130 !important;
    border-bottom: 1px solid #edebe9 !important;
}

.payment-badge-paid {
    background-color: #107c10 !important;
    color: white !important;
    padding: 4px 12px !important;
    border-radius: 20px !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    display: inline-block !important;
}

.payment-badge-pending {
    background-color: #ffc107 !important;
    color: #212529 !important;
    padding: 4px 12px !important;
    border-radius: 20px !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    display: inline-block !important;
}

/* ===== SELECTION INFO PANEL ===== */
.selection-info-panel {
    background-color: #f3f2f1 !important;
    border: 1px solid #edebe9 !important;
    border-radius: 8px !important;
    padding: 16px 20px !important;
    margin-bottom: 20px !important;
    display: none !important;
}

.selection-info-panel.active {
    display: block !important;
}

.selection-info-content {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: 12px !important;
}

.selection-info-text {
    font-size: 14px !important;
    color: #323130 !important;
}

.selection-info-text strong {
    font-size: 16px !important;
    color: #107c10 !important;
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

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .month-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .month-header-right {
        width: 100%;
        justify-content: space-between;
    }
    
    .summary-number { font-size: 28px !important; }
    .selection-info-content { flex-direction: column !important; align-items: flex-start !important; }
    .affiliate-table th, .affiliate-table td { font-size: 12px !important; padding: 8px !important; }
}

/* ===== PANEL STYLES ===== */
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
CSS
);

// JavaScript for month accordion functionality
$this->registerJs(
    <<<JS
$(document).ready(function() {
    // Store cuota amounts for each checkbox
    $('.checkbox-cuota').each(function() {
        var currentCheckbox = $(this);
        var rowElement = currentCheckbox.closest('tr');
        var amountText = rowElement.find('.cuota-amount').text();
        
        // Parse currency properly
        var amount = 0;
        if (amountText) {
            // Remove currency symbol and convert
            var cleaned = amountText.replace('$', '').replace('USD', '').trim();
            cleaned = cleaned.replace(/[^0-9.,-]/g, '');
            cleaned = cleaned.replace(',', '');
            amount = parseFloat(cleaned);
            if (isNaN(amount)) amount = 0;
        }
        currentCheckbox.data('amount', amount);
        // Also store amount as a data attribute on the checkbox
        currentCheckbox.attr('data-amount', amount);
    });
    
    // Toggle month content (expand/collapse)
    $('.month-header').on('click', function(e) {
        if ($(e.target).is('.month-checkbox') || $(e.target).closest('.month-checkbox').length) {
            return;
        }
        var targetContent = $(this).next('.month-content');
        var icon = $(this).find('.toggle-icon');
        targetContent.slideToggle(200);
        if (targetContent.is(':visible')) {
            icon.addClass('expanded');
        } else {
            icon.removeClass('expanded');
        }
    });
    
    // Toggle affiliate cuotas within month
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
    
    // Month checkbox selection
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
    
    // Individual cuota checkbox change event
    $(document).on('change', '.checkbox-cuota', function() {
        var monthKey = $(this).data('month-key');
        var monthCheckbox = $('.month-checkbox[data-month-key="' + monthKey + '"]');
        var allCuotasInMonth = $('.checkbox-cuota[data-month-key="' + monthKey + '"]');
        var checkedCuotasInMonth = $('.checkbox-cuota[data-month-key="' + monthKey + '"]:checked');
        
        // Update month checkbox state
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
    
    // Global select all months
    $('#select-all-months').on('change', function() {
        var isChecked = $(this).prop('checked');
        $('.month-checkbox').prop('checked', isChecked);
        $('.checkbox-cuota').prop('checked', isChecked);
        
        if (isChecked) {
            $('.month-accordion').addClass('selected');
        } else {
            $('.month-accordion').removeClass('selected');
        }
        
        updateSelectionInfo();
    });
    
    // Clear selection button
    $('#clear-selection').on('click', function() {
        $('.month-checkbox, #select-all-months').prop('checked', false);
        $('.checkbox-cuota').prop('checked', false);
        $('.month-accordion').removeClass('selected');
        updateSelectionInfo();
    });
    
    // Update selection info panel
    function updateSelectionInfo() {
        var selectedCuotas = $('.checkbox-cuota:checked');
        var selectedMonths = $('.month-checkbox:checked');
        var selectedCount = selectedCuotas.length;
        var selectedTotal = 0;
        
        selectedCuotas.each(function() {
            var currentCheckbox = $(this);
            // Try to get amount from data attribute first
            var amount = currentCheckbox.data('amount');
            
            // If not found, try to get from the cuota-amount cell
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
            paymentBtn.html('<i class="fas fa-credit-card me-2"></i> Pagar ' + selectedCount + ' cuota(s) de ' + selectedMonths.length + ' mes(es) por $' + selectedTotal.toFixed(2) + ' USD');
            
            // Collect selected cuota IDs
            var selectedIds = [];
            selectedCuotas.each(function() {
                var cuotaId = $(this).val();
                if (cuotaId && cuotaId !== '') {
                    selectedIds.push(cuotaId);
                }
            });
            
            // Build URL with cuota IDs
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
    
    function parseCurrency(currencyString) {
        var cleaned = currencyString.replace(/[^0-9.,-]+/g, '');
        cleaned = cleaned.replace(',', '.');
        var result = parseFloat(cleaned);
        return isNaN(result) ? 0 : result;
    }
    
    // Initialize selection info
    updateSelectionInfo();
    
    // Expand first month by default for better UX
    $('.month-header').first().trigger('click');
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
                    <!-- Corporate Info -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="ms-panel border-info mb-4">
                                <div class="ms-panel-header bg-gradient-blue-2">
                                    <h3 class="mb-0"><i class="fas fa-building me-2"></i>Información del Corporativo</h3>
                                </div>
                                <div class="ms-panel-body informacion-corporativo">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Nombre:</strong> <?= Html::encode($corporativo->nombre) ?></p>
                                            <p><strong>RIF:</strong> <?= Html::encode($corporativo->rif) ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Email:</strong> <?= Html::encode($corporativo->email) ?></p>
                                            <p><strong>Teléfono:</strong> <?= Html::encode($corporativo->telefono) ?></p>
                                            <?php if ($lastPaymentDate): ?>
                                                <p><strong>Último pago:</strong> <?= Yii::$app->formatter->asDate($lastPaymentDate, 'php:d/m/Y') ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Cards - Enhanced with Payment Stats -->
                    <div class="row mb-4">
                        <div class="col-md-3 col-sm-6">
                            <div class="summary-card">
                                <div class="summary-number"><?= $totalAffiliates ?></div>
                                <div class="summary-label">Afiliados con Deuda</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="summary-card">
                                <div class="summary-number"><?= $totalMonths ?></div>
                                <div class="summary-label">Meses con Deuda</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="summary-card">
                                <div class="summary-number">$<?= number_format($grandTotal, 2, '.', ',') ?> USD</div>
                                <div class="summary-label">Deuda Pendiente</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="summary-card-success">
                                <div class="summary-number">$<?= number_format($totalPaid, 2, '.', ',') ?> USD</div>
                                <div class="summary-label">Total Pagado</div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment History Section -->
                    <?php if (!empty($paymentHistory)): ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="ms-panel border-success mb-4">
                                    <div class="ms-panel-header bg-gradient-green">
                                        <h3 class="mb-0">
                                            <i class="fas fa-history me-2"></i>Historial de Pagos
                                            <span class="badge badge-light ms-2"><?= $totalPayments ?> pagos</span>
                                        </h3>
                                    </div>
                                    <div class="ms-panel-body p-0">
                                        <div class="table-responsive">
                                            <table class="table payment-history-table mb-0">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 50px;">#</th>
                                                        <th>Fecha de Pago</th>
                                                        <th>Mes Pagado</th>
                                                        <th>Método de Pago</th>
                                                        <th class="text-end">Monto Pagado (USD)</th>
                                                        <th class="text-end">Monto Bs</th>
                                                        <th class="text-end">Tasa Cambio</th>
                                                        <th class="text-center">Cuotas Pagadas</th>
                                                        <th>Estado</th>
                                                        <th>Referencia</th>
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
                                                        <tr>
                                                            <td class="text-center"><strong><?= $paymentCounter++ ?></strong></td>
                                                            <td>
                                                                <?= Yii::$app->formatter->asDate($payment->fecha_pago, 'php:d/m/Y') ?>
                                                                <br>
                                                                <small class="text-muted"><?= Yii::$app->formatter->asTime($payment->created_at, 'php:H:i') ?></small>
                                                            </td>
                                                            <td>
                                                                <span class="month-badge">
                                                                    <i class="far fa-calendar-alt me-1"></i>
                                                                    <?= $paidMonth ?>
                                                                </span>
                                                            </td>
                                                            <td><?= Html::encode($payment->metodo_pago) ?></td>
                                                            <td class="text-end">
                                                                <strong>$<?= number_format($montoPagadoUSD, 2, '.', ',') ?> USD</strong>
                                                            </td>
                                                            <td class="text-end">
                                                                <strong><?= number_format($montoBs, 2, '.', ',') ?> Bs</strong>
                                                            </td>
                                                            <td class="text-end">
                                                                <?= number_format($tasaCalculada, 2, '.', ',') ?> Bs/USD
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="badge badge-primary">
                                                                    <i class="fas fa-file-invoice me-1"></i> <?= $paidCuotas ?> cuota(s)
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <?php if ($payment->estatus === 'Conciliado'): ?>
                                                                    <span class="payment-badge-paid">
                                                                        <i class="fas fa-check-circle me-1"></i> Conciliado
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="payment-badge-pending">
                                                                        <i class="fas fa-clock me-1"></i> Por Conciliar
                                                                    </span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?= Html::encode($payment->numero_referencia_pago) ?>
                                                                <?php if ($payment->imagen_prueba): ?>
                                                                    <br>
                                                                    <a href="<?= $payment->imagen_prueba ?>" target="_blank" class="text-primary">
                                                                        <i class="fas fa-file-image me-1"></i> Ver comprobante
                                                                    </a>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Selection Controls -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="alert alert-light border" style="background-color: #EE931F; border-radius: 8px;">
                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <div>
                                        <div class="checkbox-container" style="display: inline-flex; align-items: center;">
                                            <input type="checkbox" id="select-all-months" style="margin-right: 10px; width: 18px; height: 18px;">
                                            <label for="select-all-months" style="margin: 0; font-weight: 600;">Seleccionar Todos los Meses</label>
                                        </div>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="clear-selection">
                                            <i class="fas fa-times me-2"></i> Limpiar Selección
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Month Accordion -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="ms-panel border-warning mb-4">
                                <div class="ms-panel-header bg-gradient-blue-3">
                                    <h3 class="mb-0">
                                        <i class="fas fa-calendar-alt me-2"></i>Cuotas Pendientes por Mes de Cobertura
                                        <span class="badge badge-light ms-2"><?= $totalMonths ?> meses</span>
                                    </h3>
                                </div>
                                <div class="ms-panel-body">

                                    <?php if (empty($monthGroups)): ?>
                                        <div class="alert alert-success text-center">
                                            <i class="fas fa-check-circle me-2"></i>
                                            No hay cuotas pendientes. El corporativo está al día con sus pagos.
                                        </div>
                                    <?php else: ?>

                                        <?php foreach ($monthGroups as $monthKey => $month): ?>
                                            <div class="month-accordion" data-month-key="<?= $monthKey ?>">
                                                <div class="month-header">
                                                    <div class="month-header-left">
                                                        <i class="fas fa-chevron-right toggle-icon"></i>
                                                        <span class="month-name"><?= Html::encode($month['name']) ?></span>
                                                        <span class="month-badge">
                                                            <i class="fas fa-file-invoice me-1"></i> <?= $month['cuota_count'] ?> cuota(s)
                                                        </span>
                                                        <?php if (isset($month['coverage_start'])): ?>
                                                            <span class="coverage-period">
                                                                <i class="fas fa-calendar-check me-1"></i>
                                                                Inicio cobertura: <?= Yii::$app->formatter->asDate($month['coverage_start'], 'php:d/m/Y') ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="month-header-right">
                                                        <div class="month-stats">
                                                            <span class="month-total">
                                                                $<?= number_format($month['total'], 2, '.', ',') ?> USD
                                                            </span>
                                                        </div>
                                                        <input type="checkbox" class="month-checkbox" data-month-key="<?= $monthKey ?>">
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
                                                                    <th style="width: 100px;"></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                $affiliateCounter = 1;
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
                                                                            <strong><?= Html::encode($affiliate['name']) ?></strong>
                                                                        </td>
                                                                        <td><?= Html::encode($affiliate['cedula']) ?></td>
                                                                        <td><?= Html::encode($affiliate['contrato_nro']) ?></td>
                                                                        <td class="text-end">
                                                                            <strong class="text-danger">$<?= number_format($affiliate['total'], 2, '.', ',') ?> USD</strong>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <span class="badge badge-primary"><?= count($cuotasList) ?> cuota(s)</span>
                                                                        </td>
                                                                    </tr>
                                                                    <tr class="affiliate-cuotas-row" style="display: none;">
                                                                        <td colspan="6" class="p-0">
                                                                            <table class="affiliate-cuotas-table">
                                                                                <tbody>
                                                                                    <?php foreach ($cuotasList as $cuota):
                                                                                        $statusClass = 'status-pending';
                                                                                        $statusText = 'Pendiente';
                                                                                        if ($cuota->estatus === 'vencida') {
                                                                                            $statusClass = 'status-overdue';
                                                                                            $statusText = 'Vencida';
                                                                                        } elseif ($cuota->estatus === 'en_gracias') {
                                                                                            $statusClass = 'status-grace';
                                                                                            $statusText = 'En Gracia';
                                                                                        }
                                                                                    ?>
                                                                                        <tr>
                                                                                            <td style="width: 40px; text-align: center;">
                                                                                                <input type="checkbox" class="checkbox-cuota"
                                                                                                    value="<?= $cuota->id ?>"
                                                                                                    data-month-key="<?= $monthKey ?>"
                                                                                                    data-cuota-id="<?= $cuota->id ?>">
                                                                                            </td>
                                                                                            <td colspan="2">
                                                                                                <small class="text-muted">Cuota #<?= $cuota->numero_cuota ?></small>
                                                                                                <?php if (!empty($cuota->coverage_start) && !empty($cuota->coverage_end)): ?>
                                                                                                    <br>
                                                                                                    <span class="coverage-period" style="font-size: 11px;">
                                                                                                        <i class="fas fa-calendar-alt me-1"></i>
                                                                                                        Cobertura: <?= Yii::$app->formatter->asDate($cuota->coverage_start, 'php:d/m/Y') ?> -
                                                                                                        <?= Yii::$app->formatter->asDate($cuota->coverage_end, 'php:d/m/Y') ?>
                                                                                                    </span>
                                                                                                <?php endif; ?>
                                                                                            </td>
                                                                                            <td class="text-end cuota-amount" data-amount="<?= $cuota->monto ?>">
                                                                                                <strong>$<?= number_format($cuota->monto, 2, '.', ',') ?> USD</strong>
                                                                                            </td>
                                                                                            <td>
                                                                                                <span class="coverage-period">
                                                                                                    Vence: <?= Yii::$app->formatter->asDate($cuota->fecha_vencimiento, 'php:d/m/Y') ?>
                                                                                                </span>
                                                                                            </td>
                                                                                            <td>
                                                                                                <span class="<?= $statusClass ?>"><?= $statusText ?></span>
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

                    <!-- Selection Info Panel -->
                    <div class="selection-info-panel" id="selection-info-panel">
                        <div class="selection-info-content">
                            <div class="selection-info-text">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong><span id="selected-months">0</span> mes(es)</strong> seleccionado(s) -
                                <strong><span id="selected-count">0</span> cuota(s)</strong> -
                                Total: <strong>$<span id="selected-total">0.00</span> USD</strong>
                            </div>
                            <div>
                                <?= Html::a(
                                    '<i class="fas fa-credit-card me-2"></i> Pagar Seleccionadas',
                                    '#',
                                    [
                                        'class' => 'btn btn-success btn-lg',
                                        'id' => 'pago-parcial-btn'
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
                        <?= Html::a(
                            '<i class="fas fa-chart-line me-2"></i> Ver Reporte de Pagos',
                            ['pagos', 'id' => $corporativo->id],
                            ['class' => 'btn btn-outline-primary btn-lg']
                        ) ?>
                    </div>
                </div>
            </div>

        <?php endif; ?>

    </div>
</div>