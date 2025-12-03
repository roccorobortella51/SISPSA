<?php

use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Pagos $model */
/** @var app\models\Corporativo $corporativo */
/** @var array $allCuotas Array of all pending Cuotas across affiliates >0 */
/** @var float $grandTotal Total sum of pending cuotas >0 */
$grandTotal = $grandTotal ?? 0;

// Registrar variable global en HEAD para evitar problemas con heredoc
$this->registerJs('var grandTotal = ' . Json::encode($grandTotal) . ';', \yii\web\View::POS_HEAD);

// Register Microsoft Fluent Design CSS
$this->registerCss(<<<CSS
/* ===== MICROSOFT FLUENT DESIGN SYSTEM ===== */
body {
    font-family: "Segoe UI", SegoeUI, "Helvetica Neue", Helvetica, Arial, sans-serif !important;
    font-size: 16px !important;
    color: #323130 !important;
    background-color: #faf9f8 !important;
}

/* ===== TYPOGRAPHY - Microsoft Fluent Scale - ADJUSTED SIZES ===== */
h1, h2, h3, h4, h5, h6 {
    font-family: "Segoe UI", SegoeUI, "Helvetica Neue", Helvetica, Arial, sans-serif !important;
    font-weight: 600 !important;
    color: #000000 !important;
    margin-bottom: 16px !important;
}

/* FIX: Main header title should be black, not white */
.ms-panel-header h1:not(.no-deuda-title) {
    color: #000000 !important;
}

/* ADJUSTED: Title sizes following Microsoft Fluent standards */
h1 { font-size: 28px !important; }
h2 { font-size: 24px !important; }
h3 { font-size: 20px !important; }
h4 { font-size: 18px !important; }

/* ===== BUTTONS - Microsoft Fluent Buttons - ADJUSTED SIZES (MADE SMALLER) ===== */
.btn {
    border-radius: 2px !important;
    padding: 4px 10px !important; /* Made smaller */
    font-weight: 600 !important;
    font-size: 12px !important; /* Made smaller */
    font-family: "Segoe UI", SegoeUI, "Helvetica Neue", Helvetica, Arial, sans-serif !important;
    border: 1px solid transparent !important;
    line-height: 1.33 !important;
    min-height: 28px !important; /* Made smaller */
    transition: all 0.1s ease !important;
    text-decoration: none !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.btn-lg {
    padding: 6px 12px !important; /* Made smaller */
    font-size: 14px !important; /* Made smaller */
    min-height: 32px !important; /* Made smaller */
}

.btn-success {
    background-color: #107c10 !important;
    border-color: #107c10 !important;
    color: #ffffff !important;
}

.btn-success:hover {
    background-color: #0e700e !important;
    border-color: #0e700e !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
}

.btn-secondary {
    background-color: #f3f2f1 !important;
    border-color: #8a8886 !important;
    color: #323130 !important;
}

.btn-secondary:hover {
    background-color: #edebe9 !important;
    border-color: #8a8886 !important;
    color: #201f1e !important;
}

.btn-outline-light {
    background-color: transparent !important;
    border-color: #ffffff !important;
    color: #ffffff !important;
}

.btn-outline-light:hover {
    background-color: rgba(255,255,255,0.1) !important;
    border-color: #ffffff !important;
    color: #ffffff !important;
}

.btn-light {
    background-color: #ffffff !important;
    border-color: #8a8886 !important;
    color: #323130 !important;
}

.btn-light:hover {
    background-color: #f3f2f1 !important;
    border-color: #8a8886 !important;
    color: #201f1e !important;
}

/* ===== SECTION TEXT SIZES - CONSISTENT ACROSS ALL SECTIONS ===== */
.ms-panel-body {
    padding: 20px !important;
    font-size: 18px !important;
}

/* Ensure all text in sections is the same size */
.ms-panel-body p,
.ms-panel-body div:not(.btn),
.ms-panel-body span:not(.btn),
.ms-panel-body strong,
.ms-panel-body .text-muted {
    font-size: 18px !important;
    line-height: 1.5 !important;
}

/* Specific styling for informational sections */
.informacion-corporativo p,
.resumen-deuda p {
    font-size: 18px !important;
    margin-bottom: 12px !important;
}

/* ===== TABLES - Microsoft Fluent Tables - LARGER ===== */
.table {
    font-size: 18px !important;
    color: #323130 !important;
    border-collapse: collapse !important;
    width: 100% !important;
}

.table th {
    background-color: #0078d4 !important;
    color: #ffffff !important;
    font-weight: 600 !important;
    font-size: 18px !important;
    padding: 12px 16px !important;
    border: none !important;
    border-bottom: 2px solid #0078d4 !important;
}

.table td {
    font-size: 18px !important;
    padding: 12px 16px !important;
    border-bottom: 1px solid #edebe9 !important;
    vertical-align: middle !important;
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: #faf9f8 !important;
}

.table-striped tbody tr:hover {
    background-color: #f3f2f1 !important;
}

.table-bordered {
    border: 1px solid #edebe9 !important;
}

.table-bordered th,
.table-bordered td {
    border: 1px solid #edebe9 !important;
}

/* FIX: Total a Pagar row - make it readable */
.table-dark {
    background-color: #0078d4 !important;
    color: #ffffff !important;
}

.table-dark td {
    background-color: #0078d4 !important;
    color: #ffffff !important;
    border-color: #106ebe !important;
    font-size: 20px !important;
    font-weight: 700 !important;
}

.table-dark td strong {
    color: #ffffff !important;
}

/* ===== ALERTS - Microsoft Fluent Alerts - LARGER ===== */
.alert {
    font-size: 18px !important;
    padding: 16px 20px !important;
    border-radius: 2px !important;
    border: 1px solid !important;
    line-height: 1.33 !important;
    margin-bottom: 20px !important;
}

.alert-info {
    background-color: #f8f9fc !important;
    border-color: #0078d4 !important;
    color: #004578 !important;
}

/* CHANGED: Orange to Burgundy */
.alert-warning {
    background-color: #fdf4f7 !important;
    border-color: #800020 !important;
    color: #800020 !important;
}

.alert-danger {
    background-color: #fdf6f6 !important;
    border-color: #d13438 !important;
    color: #d13438 !important;
}

/* ===== NO DEUDA MESSAGE - Microsoft Style - LARGER ===== */
.no-deuda-message {
    background: linear-gradient(135deg, #107c10 0%, #0e700e 100%) !important;
    border: none !important;
    border-radius: 2px !important;
    box-shadow: 0 8px 16px rgba(16, 124, 16, 0.3) !important;
    color: #ffffff !important;
    padding: 60px 40px !important;
    text-align: center !important;
    margin: 30px 0 !important;
}

.no-deuda-icon {
    font-size: 80px !important;
    margin-bottom: 30px !important;
    opacity: 0.9 !important;
}

.no-deuda-title {
    font-size: 36px !important;
    font-weight: 300 !important;
    margin-bottom: 20px !important;
    letter-spacing: 0.5px !important;
    color: #ffffff !important; /* Pure White for high contrast */
}

.no-deuda-subtitle {
    font-size: 24px !important;
    opacity: 1 !important;
    margin-bottom: 40px !important;
    color: #ffffff !important; /* Blanco puro para el texto circundante */
    line-height: 1.4 !important;
}

/* NUEVO: Asegura que la etiqueta strong dentro del subtítulo también sea blanca */
.no-deuda-subtitle strong {
    color: #ffffff !important;
    font-size: 32px !important;
}

/* ===== FORM CONTROLS - LARGER ===== */
.form-control {
    font-size: 18px !important;
    padding: 10px 12px !important;
    border-radius: 2px !important;
    border: 1px solid #605e5c !important;
    font-family: "Segoe UI", SegoeUI, "Helvetica Neue", Helvetica, Arial, sans-serif !important;
    min-height: 44px !important;
    background-color: #ffffff !important;
}

.form-control:focus {
    border-color: #0078d4 !important;
    outline: 2px solid #0078d4 !important;
    outline-offset: -2px !important;
    box-shadow: none !important;
}

.control-label {
    font-size: 18px !important;
    font-weight: 600 !important;
    color: #323130 !important;
    margin-bottom: 8px !important;
}

/* ===== LAYOUT AND SPACING - LARGER ===== */
.row {
    margin: 0 !important;
}

.col-xl-12, .col-md-12 {
    padding: 12px !important;
}

/* NEW: GRADIENT CLASSES (Light to Dark Blue Variations) */
.bg-gradient-blue-1 { /* Lightest: Resumen Financiero */
    background: linear-gradient(135deg, #1E90FF 0%, #0078D4 100%) !important;
    color: #ffffff !important; 
    border-color: #0078D4 !important;
    box-shadow: 0 4px 8px rgba(30, 144, 255, 0.2) !important;
}

.bg-gradient-blue-2 { /* Light-Medium: Información del Corporativo */
    background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%) !important;
    color: #ffffff !important; 
    border-color: #005a9e !important;
    box-shadow: 0 4px 8px rgba(0, 120, 212, 0.2) !important;
}

.bg-gradient-blue-3 { /* Medium-Dark: Detalle de Cuotas Pendientes */
    background: linear-gradient(135deg, #005A9E 0%, #003E6C 100%) !important;
    color: #ffffff !important; 
    border-color: #003E6C !important;
    box-shadow: 0 4px 8px rgba(0, 90, 158, 0.2) !important;
}

.bg-gradient-blue-4 { /* Darkest: Resumen de Deuda */
    background: linear-gradient(135deg, #003E6C 0%, #002D4F 100%) !important;
    color: #ffffff !important; 
    border-color: #002D4F !important;
    box-shadow: 0 4px 8px rgba(0, 62, 108, 0.2) !important;
}

/* FIX: Ensure titles inside the gradient header are white */
.bg-gradient-blue-1 h2, .bg-gradient-blue-1 h3,
.bg-gradient-blue-2 h2, .bg-gradient-blue-2 h3,
.bg-gradient-blue-3 h2, .bg-gradient-blue-3 h3,
.bg-gradient-blue-4 h2, .bg-gradient-blue-4 h3 {
    color: #ffffff !important;
}

.text-primary { color: #0078d4 !important; }
.text-success { color: #107c10 !important; }
.text-danger { color: #d13438 !important; }
/* CHANGED: Orange to Burgundy */
.text-warning { color: #800020 !important; }
.text-info { color: #004578 !important; }

.bg-primary { background-color: #0078d4 !important; }
.bg-success { background-color: #107c10 !important; }
/* CHANGED: Orange to Burgundy */
.bg-warning { background-color: #800020 !important; }
.bg-info { background-color: #004578 !important; }
.bg-dark { background-color: #323130 !important; }

.border-primary { border-color: #0078d4 !important; }
.border-success { border-color: #107c10 !important; }
/* CHANGED: Orange to Burgundy */
.border-warning { border-color: #800020 !important; }
.border-info { border-color: #004578 !important; }

.shadow-sm {
    box-shadow: 0 1.6px 3.6px 0 rgba(0,0,0,.132), 0 0.3px 0.9px 0 rgba(0,0,0,.108) !important;
}

/* ===== ICONS - LARGER ===== */
.fas, .fa {
    font-size: 20px !important;
    margin-right: 10px !important;
}

/* ===== CHECKBOX STYLING ===== */
.checkbox-cell {
    width: 50px !important;
    text-align: center !important;
}

.checkbox-header {
    width: 50px !important;
    text-align: center !important;
}

.checkbox-select-all {
    margin: 0 !important;
    transform: scale(1.2) !important;
}

.checkbox-cuota {
    margin: 0 !important;
    transform: scale(1.2) !important;
}

/* Checkbox container styling */
.checkbox-container {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    height: 100% !important;
}

/* ===== SELECTION INFO PANEL ===== */
.selection-info-panel {
    background-color: #f3f2f1 !important;
    border: 1px solid #edebe9 !important;
    border-radius: 2px !important;
    padding: 16px 20px !important;
    margin-bottom: 20px !important;
    display: none !important;
}

.selection-info-panel.active {
    display: block !important;
    animation: fadeIn 0.3s ease-in !important;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.selection-info-content {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
}

.selection-info-text {
    font-size: 18px !important;
    color: #323130 !important;
}

.selection-info-text strong {
    color: #107c10 !important;
}

/* ===== RESPONSIVE DESIGN ===== */
@media (max-width: 768px) {
    body {
        font-size: 14px !important;
    }
    
    .ms-panel-body {
        padding: 16px !important;
        font-size: 16px !important;
    }
    
    /* ADJUSTED: Responsive title sizes */
    h1 { font-size: 24px !important; }
    h2 { font-size: 20px !important; }
    h3 { font-size: 18px !important; }
    h4 { font-size: 16px !important; }
    
    .btn-lg {
        width: 100% !important;
        margin-bottom: 12px !important;
    }
    
    .no-deuda-message {
        padding: 40px 20px !important;
        margin: 20px 0 !important;
    }
    
    .no-deuda-icon {
        font-size: 60px !important;
    }
    
    .no-deuda-title {
        font-size: 28px !important;
    }
    
    .no-deuda-subtitle {
        font-size: 20px !important;
    }
    
    .table {
        font-size: 16px !important;
    }
    
    .table th,
    .table td {
        font-size: 16px !important;
        padding: 10px 12px !important;
    }
    
    .selection-info-content {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 10px !important;
    }
}

/* ===== ACCESSIBILITY ===== */
.btn:focus,
.form-control:focus {
    outline: 2px solid #0078d4 !important;
    outline-offset: 2px !important;
}

.table-responsive {
    border: 1px solid #edebe9 !important;
    border-radius: 2px !important;
}

/* Additional Microsoft Fluent Panel Styles */
.ms-panel {
    background: #ffffff !important;
    border-radius: 2px !important;
    box-shadow: 0 1.6px 3.6px 0 rgba(0,0,0,.132), 0 0.3px 0.9px 0 rgba(0,0,0,.108) !important;
    margin-bottom: 20px !important;
    border: 1px solid #edebe9 !important;
}

.ms-panel-header {
    padding: 16px 20px !important;
    border-bottom: 1px solid #edebe9 !important;
    color: #ffffff !important;
    font-weight: 600 !important;
}

.ms-panel-header h1,
.ms-panel-header h2,
.ms-panel-header h3 {
    color: #ffffff !important;
    margin-bottom: 0 !important;
}

.ms-panel-fh {
    min-height: 400px !important;
}
CSS
);

// JavaScript for checkbox functionality
$this->registerJs(<<<JS
$(document).ready(function() {
    // Store cuota amounts in a data attribute for easier access
    $('.checkbox-cuota').each(function() {
        var cuotaRow = $(this).closest('tr');
        var amountText = cuotaRow.find('td:eq(5)').text().trim(); // Changed from eq(4) to eq(5) because we added checkbox column
        
        // Parse currency value
        var amount = parseCurrency(amountText);
        $(this).data('amount', amount);
    });
    
    // Select all checkbox functionality
    $('#select-all-cuotas').on('change', function() {
        var isChecked = $(this).prop('checked');
        $('.checkbox-cuota').prop('checked', isChecked);
        updateSelectionInfo();
    });
    
    // Individual checkbox functionality
    $('.checkbox-cuota').on('change', function() {
        updateSelectionInfo();
        
        // Update select all checkbox state
        var totalCheckboxes = $('.checkbox-cuota').length;
        var checkedCheckboxes = $('.checkbox-cuota:checked').length;
        $('#select-all-cuotas').prop('checked', totalCheckboxes === checkedCheckboxes);
    });
    
    // Update selection info panel
    function updateSelectionInfo() {
        var selectedCuotas = $('.checkbox-cuota:checked');
        var selectedCount = selectedCuotas.length;
        var selectedTotal = 0;
        
        // Calculate total amount of selected cuotas
        selectedCuotas.each(function() {
            var amount = $(this).data('amount') || 0;
            if (!isNaN(amount)) {
                selectedTotal += amount;
            }
        });
        
        if (selectedCount > 0) {
            $('#selection-info-panel').addClass('active');
            $('#selected-count').text(selectedCount);
            $('#selected-total').text(selectedTotal.toFixed(2));
            
            // Update payment button
            var paymentBtn = $('#pago-parcial-btn');
            paymentBtn.text('Realizar Pago Corporativo por ' + selectedTotal.toFixed(2) + ' USD');
            
            // Get selected cuota IDs
            var selectedIds = [];
            selectedCuotas.each(function() {
                selectedIds.push($(this).val());
            });
            
            // Update button URL
            var baseUrl = $('#pago-parcial-base-url').val();
            paymentBtn.attr('href', baseUrl + '&cuotas=' + selectedIds.join(','));
        } else {
            $('#selection-info-panel').removeClass('active');
        }
    }
    
    // Helper function to parse currency strings
    function parseCurrency(currencyString) {
        // Remove all non-numeric characters except decimal point and minus sign
        var cleaned = currencyString.replace(/[^0-9.,-]+/g, '');
        
        // Replace comma with dot if comma is used as decimal separator
        cleaned = cleaned.replace(',', '.');
        
        // Parse as float
        var result = parseFloat(cleaned);
        
        // Return 0 if parsing fails
        return isNaN(result) ? 0 : result;
    }
    
    // Initialize selection info
    updateSelectionInfo();
    
    // Clear selection button
    $('#clear-selection').on('click', function() {
        $('.checkbox-cuota, #select-all-cuotas').prop('checked', false);
        updateSelectionInfo();
    });
});
JS
);
?>

<div class="row">
    <div class="col-xl-12 col-md-12">
        
        <?php if ($grandTotal == 0): ?>
        
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
        
        <!-- Hidden fields for URLs and IDs -->
        <input type="hidden" id="corporativo-id" value="<?= $corporativo->id ?>">
        <input type="hidden" id="pago-parcial-base-url" value="<?= \yii\helpers\Url::to(['pagos-parcial', 'id' => $corporativo->id]) ?>">
        
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header d-flex justify-content-between align-items-center">
                <h1 style="font-size: 26px !important; font-weight: 700 !important;">
                    <i class="fas fa-building me-2"></i>Gestión de Deuda - <?= Html::encode($corporativo->nombre) ?>
                </h1>
                <div style="margin-left: 40px !important;"> 
                    <?= Html::a(
                        '<i class="fas fa-credit-card me-2"></i> Realizar Pago Corporativo Completo',
                        ['pagos', 'id' => $corporativo->id],
                        ['class' => 'btn btn-success btn-lg']
                    ) ?>
                </div>
            </div>

            <div class="ms-panel-body">
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="ms-panel border-primary mb-4">
                            <div class="ms-panel-header bg-gradient-blue-1">
                                <h2 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Resumen Financiero</h2>
                            </div>
                            <div class="ms-panel-body">
                                <div class="alert alert-info" role="alert">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Corporativo:</strong> <?= Html::encode($corporativo->nombre) ?><br>
                                            <strong>Estado:</strong> <span class="text-danger">Pendiente de pago</span>
                                        </div>
                                        <div class="text-end">
                                            <div class="text-muted">Total Pendiente:</div>
                                            <h3 class="text-danger mb-0">
                                                <strong><?= Yii::$app->formatter->asCurrency($grandTotal) ?></strong>
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="ms-panel border-warning mb-4">
                            <div class="ms-panel-header bg-gradient-blue-3">
                                <h3 class="mb-0"><i class="fas fa-list-ul me-2"></i>Detalle de Cuotas Pendientes</h3>
                            </div>
                            <div class="ms-panel-body p-0">
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th class="checkbox-header">
                                                    <div class="checkbox-container">
                                                        <input type="checkbox" id="select-all-cuotas" class="checkbox-select-all">
                                                    </div>
                                                </th>
                                                <th>ID Cuota</th>
                                                <th>ID Afiliado</th>
                                                <th>Afiliado</th>
                                                <th>Contrato</th>
                                                <th class="text-end">Monto USD</th>
                                                <th>Vencimiento</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($allCuotas as $cuota): 
                                                $contrato = $cuota->contrato ?? null;
                                                $userDatos = $contrato->user ?? null;
                                                
                                                $userId = $contrato->user_id ?? 'N/A';
                                                $nombreCompleto = $userDatos ? Html::encode($userDatos->nombres . ' ' . $userDatos->apellidos) : 'Afiliado no encontrado';
                                            ?>
                                            <tr>
                                                <td class="checkbox-cell">
                                                    <div class="checkbox-container">
                                                        <input type="checkbox" class="checkbox-cuota" value="<?= $cuota->id ?>" data-cuota-id="<?= $cuota->id ?>">
                                                    </div>
                                                </td>
                                                <td><?= Html::encode($cuota->id) ?></td>
                                                <td><?= $userId ?></td>
                                                <td title="<?= $nombreCompleto ?>"><?= \yii\helpers\StringHelper::truncateWords($nombreCompleto, 3, '...') ?></td>
                                                <td><?= Html::encode($contrato->nrocontrato ?? 'N/A') ?></td>
                                                <td class="text-end"><?= Yii::$app->formatter->asCurrency($cuota->monto) ?></td>
                                                <td><?= Yii::$app->formatter->asDate($cuota->fecha_vencimiento, 'php:d/m/Y') ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($allCuotas)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center">No hay cuotas pendientes para los afiliados de este corporativo.</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-dark">
                                                <td colspan="5" class="text-end"><strong>TOTAL PENDIENTE:</strong></td>
                                                <td class="text-end"><strong><?= Yii::$app->formatter->asCurrency($grandTotal) ?></strong></td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                
                                <!-- Selection Info Panel (moved here, right after the table) -->
                                <div class="selection-info-panel mt-4" id="selection-info-panel">
                                    <div class="selection-info-content">
                                        <div class="selection-info-text">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            <span id="selected-count">0</span> cuota(s) seleccionada(s) - 
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
                                            <button type="button" class="btn btn-secondary btn-lg ms-2" id="clear-selection">
                                                <i class="fas fa-times me-2"></i> Limpiar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="ms-panel border-success mb-4">
                            <div class="ms-panel-header bg-gradient-blue-4">
                                <h3 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Resumen de Deuda</h3>
                            </div>
                            <div class="ms-panel-body resumen-deuda">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Total de Cuotas Pendientes:</strong> <?= count($allCuotas) ?></p>
                                        <p><strong>Corporativo:</strong> <?= Html::encode($corporativo->nombre) ?></p>
                                        <p><strong>Calculado el:</strong> <?= date('d/m/Y H:i:s') ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-end">
                                            <p class="text-muted mb-1">Total Deuda Pendiente</p>
                                            <h3 class="text-primary mb-0"><?= number_format($grandTotal, 2, ',', '.') ?> USD</h3>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-4">
                                    <?= Html::a(
                                        '<i class="fas fa-credit-card me-2"></i> Realizar Pago Corporativo Completo por ' . number_format($grandTotal, 2, ',', '.') . ' USD',
                                        ['pagos', 'id' => $corporativo->id],
                                        ['class' => 'btn btn-success btn-lg']
                                    ) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-4 d-flex justify-content-center">
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