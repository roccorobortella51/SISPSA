<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use kartik\widgets\FileInput;
use yii\helpers\Url;
use yii\web\JsExpression;

/** @var yii\web\View $this */
/** @var app\models\Pagos $model */
/** @var yii\widgets\ActiveForm $form */

$user_id = isset($user_id) ? $user_id : ($model->user_id ?? null);

// ===== CONTRACT INFO ALERT - Shows which contract is being paid =====
if (isset($contrato_id) && $contrato_id && isset($selectedContrato) && $selectedContrato): ?>
    <div class="alert alert-info mb-4" style="border-left: 5px solid #17a2b8;">
        <div class="d-flex align-items-center">
            <div class="mr-4">
                <i class="fas fa-file-contract fa-3x text-info"></i>
            </div>
            <div>
                <h4 class="text-info mb-2" style="font-size: 1.8rem;">
                    <i class="fas fa-chevron-right mr-2"></i>Registrando Pago para Contrato Específico
                </h4>
                <p class="mb-1" style="font-size: 1.4rem;">
                    <strong>Contrato #<?= $selectedContrato->id ?></strong>
                    <?php if ($selectedContrato->nrocontrato): ?>
                        (N°: <?= Html::encode($selectedContrato->nrocontrato) ?>)
                    <?php endif; ?>
                </p>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <strong>Período:</strong>
                        <?= Yii::$app->formatter->asDate($selectedContrato->fecha_ini, 'php:d/m/Y') ?>
                        al <?= Yii::$app->formatter->asDate($selectedContrato->fecha_ven, 'php:d/m/Y') ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Plan:</strong>
                        <?= $selectedContrato->plan ? $selectedContrato->plan->nombre : 'N/A' ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Clínica:</strong>
                        <?= $selectedContrato->clinica ? $selectedContrato->clinica->nombre : 'N/A' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
// Main JavaScript for form functionality
$this->registerJs(
    <<<'JS'
    $(function() {
        // Function to update Bs amount
        function updateMontoBs() {
            var montoUsd = parseFloat($('#pagos-monto_pagado').val()) || 0;
            var tasa = parseFloat($('#pagos-tasa').val()) || 0;
            var montoBs = montoUsd * tasa;
            $('#pagos-monto_usd').val(montoBs.toFixed(4));
        }

        // Function to update field visibility and show info message
        function updateFieldsVisibility() {
            var metodo = $('#pagos-metodo_pago').val();
            var isCashDollar = (metodo === 'Efectivo - Dólar ($)');
            
            if (isCashDollar) {
                $('.field-pagos-numero_referencia_pago').hide();
                $('#pagos-numero_referencia_pago').val('');
                $('#cash-info-message').show();
            } else {
                $('.field-pagos-numero_referencia_pago').show();
                $('#cash-info-message').hide();
            }
        }

        // Calculate selected cuotas total with row highlighting
        function updateMontoSelected() {
            var sum = 0;
            var selectedCount = 0;
            
            $('.cuota-checkbox:checked').each(function() {
                sum += parseFloat($(this).data('monto')) || 0;
                selectedCount++;
            });
            
            $('#pagos-monto_pagado').val(sum.toFixed(2));
            updateMontoBs();
            
            // Update total display
            const totalElement = $('#selected-total');
            totalElement.html('<i class="fas fa-dollar-sign mr-1"></i>' + sum.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
            
            // Update selected counter badge
            let counterBadge = $('.selected-counter');
            if (selectedCount > 0) {
                if (counterBadge.length === 0) {
                    $('.badge-warning').after('<span class="selected-counter"><i class="fas fa-check-circle mr-1"></i>' + selectedCount + ' seleccionada(s)</span>');
                } else {
                    counterBadge.html('<i class="fas fa-check-circle mr-1"></i>' + selectedCount + ' seleccionada(s)');
                }
            } else {
                counterBadge.remove();
            }
        }

        // IMMEDIATE ROW HIGHLIGHTING ON CHECKBOX CHANGE
        $(document).on('change', '.cuota-checkbox', function() {
            var $row = $(this).closest('tr');
            if ($(this).is(':checked')) {
                $row.addClass('selected-cuota-row');
            } else {
                $row.removeClass('selected-cuota-row');
            }
            updateMontoSelected();
        });

        // Event handlers
        $('#fecha-pago').on('change', function() {
            var fecha = $(this).val();
            if (fecha) {
                $.ajax({
                    url: '../site/tasacambio',
                    type: 'post',
                    data: { fecha: fecha },
                    success: function(response) {
                        if (response) {
                            $('#pagos-tasa').val(parseFloat(response).toFixed(2));
                            updateMontoBs();
                        }
                    }
                });
            }
        });

        $('#pagos-monto_pagado, #pagos-tasa').on('change keyup', updateMontoBs);
        $('#pagos-metodo_pago').on('change', updateFieldsVisibility);

        // Initialize any pre-checked checkboxes (for edit mode)
        $('.cuota-checkbox:checked').each(function() {
            $(this).closest('tr').addClass('selected-cuota-row');
        });
        
        updateMontoSelected();
        updateFieldsVisibility();
        updateMontoBs();

        // Disable submit button on form submit
        $('#pago-form').on('submit', function() {
            var submitBtn = $('#submit-btn');
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Guardando...');
        });
    });
JS
);
?>

<style>
    /* ===== PROFESSIONAL BOOTSTRAP 4 STYLES - OPTIMIZED FOR READABILITY ===== */

    /* Form Field Styles - Larger Fonts */
    .pagos-form .form-group {
        margin-bottom: 1.5rem;
    }

    .pagos-form .form-label {
        font-weight: 600;
        color: #0056b3;
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
    }

    .pagos-form .form-control {
        font-size: 1rem;
        padding: 0.75rem 1rem;
        height: auto;
        border-radius: 0.25rem;
        border: 1px solid #ced4da;
    }

    .pagos-form .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    /* Required Field Indicator */
    .required-field::after {
        content: " *";
        color: #dc3545;
        font-size: 1.2rem;
    }

    /* ===== CUOTAS TABLE - WHITE HEADERS (FORCED) ===== */
    .cuotas-table {
        font-size: 1.1rem;
    }

    .cuotas-table thead th {
        font-size: 1.2rem;
        font-weight: 600;
        padding: 1rem 0.75rem;
        background-color: #2c3e50 !important;
        border-bottom: 2px solid #1a252f;
        color: #ffffff !important;
        text-align: center;
    }

    .cuotas-table tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
    }

    /* Ensure MONTO column alignment */
    .cuotas-table tbody td:nth-child(6) {
        text-align: center;
    }

    /* ===== SELECTED CUOTA ROW HIGHLIGHT - IMMEDIATE EFFECT ===== */
    .cuotas-table tbody tr.selected-cuota-row {
        background: linear-gradient(135deg, #bbdef5 0%, #90caf9 100%) !important;
        border-left: 4px solid #1565c0 !important;
        border-right: 1px solid #64b5f6 !important;
        box-shadow: 0 2px 8px rgba(21, 101, 192, 0.2) !important;
        transition: all 0.1s ease !important;
    }

    .cuotas-table tbody tr.selected-cuota-row:hover {
        background: linear-gradient(135deg, #a5d6f7 0%, #7bb8e8 100%) !important;
        box-shadow: 0 4px 12px rgba(21, 101, 192, 0.3) !important;
    }

    /* Royal blue for the cuota number when selected - immediate */
    .cuotas-table tbody tr.selected-cuota-row .cuota-number {
        background: linear-gradient(135deg, #1565c0, #0d47a1) !important;
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(21, 101, 192, 0.45) !important;
        transition: all 0.1s ease !important;
    }

    /* Circular Number Indicator */
    .cuota-number {
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, #007bff, #0069d9);
        color: white;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.1s ease;
    }

    /* Month/Year Text */
    .cuota-month {
        font-size: 1.2rem;
        font-weight: 500;
    }

    /* Coverage Period */
    .cuota-coverage {
        font-size: 1.1rem;
        white-space: nowrap;
    }

    /* Due Date */
    .cuota-due-date {
        font-size: 1.2rem;
        font-weight: 500;
    }

    .cuota-due-date.overdue {
        color: #dc3545 !important;
        font-weight: 700;
    }

    .cuota-due-date.overdue:after {
        content: " ⚠️";
        font-size: 1rem;
    }

    /* Amount */
    .cuota-amount {
        font-size: 1.3rem;
        font-weight: 700;
        color: #28a745;
    }

    /* ===== PROFESSIONAL STATUS BADGES - LARGER FONTS ===== */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.6rem 1.2rem;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 2rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        white-space: nowrap;
    }

    .status-badge i {
        margin-right: 0.6rem;
        font-size: 1.1rem;
    }

    /* Paid */
    .status-paid {
        background: linear-gradient(135deg, #28a745, #34ce57);
        color: white;
        border: 1px solid #1e7e34;
    }

    /* Grace Period */
    .status-grace {
        background: linear-gradient(135deg, #ffc107, #ffdb6d);
        color: #856404;
        border: 1px solid #d39e00;
    }

    /* Grace Period - Urgent (last 2 days) */
    .status-urgent {
        background: linear-gradient(135deg, #fd7e14, #ff9f4b);
        color: white;
        border: 1px solid #dc6b12;
        animation: pulse-urgent 2s infinite;
    }

    /* Overdue */
    .status-overdue {
        background: linear-gradient(135deg, #dc3545, #e4606d);
        color: white;
        border: 1px solid #bd2130;
    }

    /* Approaching Due Date */
    .status-approaching {
        background: linear-gradient(135deg, #17a2b8, #4fd0e0);
        color: white;
        border: 1px solid #117a8b;
    }

    /* Pending */
    .status-pending {
        background: linear-gradient(135deg, #6c757d, #929ba3);
        color: white;
        border: 1px solid #545b62;
    }

    /* ===== ANIMATIONS ===== */
    @keyframes pulse-urgent {
        0% {
            box-shadow: 0 0 0 0 rgba(253, 126, 20, 0.7);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(253, 126, 20, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(253, 126, 20, 0);
        }
    }

    /* Tooltip */
    [data-tooltip] {
        position: relative;
        cursor: help;
    }

    [data-tooltip]:before {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        padding: 0.5rem 1rem;
        background: rgba(0, 0, 0, 0.85);
        color: white;
        border-radius: 0.25rem;
        font-size: 0.9rem;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s ease;
        z-index: 1000;
        pointer-events: none;
    }

    [data-tooltip]:hover:before {
        opacity: 1;
        visibility: visible;
        bottom: 120%;
    }

    /* Checkbox Styling */
    .custom-checkbox .custom-control-label {
        font-size: 1.2rem;
        padding-left: 0.5rem;
    }

    .custom-checkbox .custom-control-label:before {
        width: 1.5rem;
        height: 1.5rem;
    }

    .custom-checkbox .custom-control-label:after {
        width: 1.5rem;
        height: 1.5rem;
    }

    .cuota-checkbox:disabled+label {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Ensure checkbox change triggers immediately */
    .cuota-checkbox {
        cursor: pointer;
    }

    .cuota-checkbox:checked {
        accent-color: #28a745;
    }

    /* Enhanced Checkbox Styling when checked */
    .custom-checkbox .custom-control-input:checked~.custom-control-label::before {
        background-color: #28a745 !important;
        border-color: #1e7e34 !important;
        box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.3);
    }

    .custom-checkbox .custom-control-input:checked~.custom-control-label::after {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23fff' d='M6.564.75l-3.59 3.612-1.538-1.55L0 4.26 2.974 7.25 8 2.193z'/%3e%3c/svg%3e") !important;
    }

    /* Checkbox pulse animation when checked */
    .custom-checkbox .custom-control-input:checked~.custom-control-label::before {
        animation: checkboxPulse 0.3s ease;
    }

    @keyframes checkboxPulse {
        0% {
            transform: scale(0.9);
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.4);
        }

        70% {
            transform: scale(1.05);
            box-shadow: 0 0 0 6px rgba(40, 167, 69, 0);
        }

        100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
        }
    }

    /* Status Summary Container */
    .status-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }

    /* ===== TOTAL SELECCIONADO - EYE CATCHING ===== */
    .total-row {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-top: 2px solid #28a745;
        border-bottom: 2px solid #28a745;
    }

    .total-row td {
        padding: 1rem;
        vertical-align: middle;
    }

    .total-amount {
        font-size: 2rem;
        font-weight: 800;
        color: #28a745;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        letter-spacing: 1px;
        animation: pulse-green 1.5s ease-in-out infinite;
        display: inline-block;
        text-align: center;
        width: 100%;
    }

    .total-label {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1e7e34;
    }

    @keyframes pulse-green {
        0% {
            text-shadow: 0 0 0 rgba(40, 167, 69, 0);
            transform: scale(1);
        }

        50% {
            text-shadow: 0 0 8px rgba(40, 167, 69, 0.4);
            transform: scale(1.02);
        }

        100% {
            text-shadow: 0 0 0 rgba(40, 167, 69, 0);
            transform: scale(1);
        }
    }

    /* Selected counter badge */
    .selected-counter {
        background: #28a745;
        color: white;
        border-radius: 20px;
        padding: 0.25rem 0.75rem;
        font-size: 0.8rem;
        font-weight: 600;
        margin-left: 0.5rem;
        animation: fadeIn 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.8);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    /* Empty State Alert */
    .alert-empty {
        font-size: 1.2rem;
        padding: 1.5rem;
    }

    /* ===== FILE INPUT BUTTONS - COLORED STYLES ===== */
    /* Browse (Examinar) button */
    .file-input .btn-file {
        background: linear-gradient(135deg, #b5d1e6 0%, #657989 100%) !important;
        border: none !important;
        color: white !important;
        font-weight: 600 !important;
        padding: 0.5rem 1.5rem !important;
        transition: all 0.2s ease !important;
    }

    .file-input .btn-file:hover {
        background: linear-gradient(135deg, #005a9e 0%, #004578 100%) !important;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 120, 212, 0.3);
    }

    .file-input .btn-file:active {
        transform: translateY(0);
    }

    /* Remove (Quitar) button */
    .file-input .btn-remove {
        background: linear-gradient(135deg, #d83b01 0%, #b02a00 100%) !important;
        border: none !important;
        color: white !important;
        font-weight: 600 !important;
        padding: 0.5rem 1.5rem !important;
        transition: all 0.2s ease !important;
    }

    .file-input .btn-remove:hover {
        background: linear-gradient(135deg, #b02a00 0%, #8a1f00 100%) !important;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(216, 59, 1, 0.3);
    }

    .file-input .btn-remove:active {
        transform: translateY(0);
    }

    /* File Input container styling */
    .file-input {
        margin-top: 0.5rem;
    }

    .file-input .file-preview {
        border-radius: 8px;
        border: 1px dashed #ced4da;
        background: #f8f9fa;
    }

    /* Button Styling */
    .btn-lg {
        font-size: 1.2rem;
        padding: 0.75rem 2rem;
    }

    /* ===== SPACE BETWEEN FILE INPUT BUTTONS ===== */
    .file-input .btn-group {
        gap: 10px !important;
    }

    .file-input .btn-group .btn {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    /* Alternative: Add space directly to the buttons */
    .file-input .btn-file {
        margin-right: 10px !important;
    }

    .file-input .btn-remove {
        margin-left: 10px !important;
    }

    /* For the file input action buttons container */
    .file-input .file-actions {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    /* If using kv-plugin-init wrapper */
    .file-input .kv-fileinput-remove,
    .file-input .fileinput-remove-button,
    .file-input .btn-kv-remove {
        margin-left: 10px !important;
    }
</style>

<div class="pagos-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'enctype' => 'multipart/form-data',
            'id' => 'pago-form',
        ],
        'type' => ActiveForm::TYPE_VERTICAL,
        'fieldConfig' => [
            'errorOptions' => [
                'class' => 'text-danger small',
                'style' => 'margin-top: 0.25rem;',
            ],
        ],
        'enableClientValidation' => false,
        'enableAjaxValidation' => false,
        'validateOnSubmit' => false,
    ]); ?>

    <?php $disabled = isset($isEditable) && !$isEditable; ?>

    <h4 class="mb-4 text-info border-bottom pb-2" style="font-size: 1.6rem;">
        <i class="fas fa-credit-card mr-2"></i> Información del Pago
    </h4>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'metodo_pago')->widget(Select2::classname(), [
                'data' => [
                    'Efectivo - Dólar ($)' => 'Efectivo - Dólar ($)',
                    'Pago Móvil' => 'Pago Móvil',
                    'Punto de Venta' => 'Punto de Venta',
                    'Transferencia Bancaria' => 'Transferencia Bancaria',
                    'Zelle' => 'Zelle'
                ],
                'options' => [
                    'placeholder' => 'Seleccione el método de pago...',
                    'class' => 'form-control',
                    'disabled' => $disabled,
                ],
                'pluginOptions' => [
                    'allowClear' => false,
                    'templateResult' => new JsExpression('
                        function(data) {
                            if (!data.id) return data.text;
                            var span = $("<span></span>");
                            if (data.id === "Efectivo - Dólar ($)") {
                                span.append("<i class=\'fas fa-dollar-sign text-success mr-2\'></i>");
                            }
                            span.append(document.createTextNode(data.text));
                            return span;
                        }
                    '),
                    'templateSelection' => new JsExpression('
                        function(data) {
                            if (!data.id) return data.text;
                            var span = $("<span></span>");
                            if (data.id === "Efectivo - Dólar ($)") {
                                span.append("<i class=\'fas fa-dollar-sign text-success mr-2\'></i>");
                            }
                            span.append(document.createTextNode(data.text));
                            return span;
                        }
                    '),
                    'escapeMarkup' => new JsExpression('function(markup) { return markup; }'),
                ],
            ])->label('Método de Pago' . '<span class="required-field"></span>') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'fecha_pago')->textInput([
                'class' => 'form-control',
                'type' => 'date',
                'placeholder' => 'Seleccione la fecha del pago',
                'disabled' => $disabled,
                'id' => 'fecha-pago',
            ])->label('Fecha de Pago' . '<span class="required-field"></span>') ?>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="mb-0" style="font-size: 1.4rem; font-weight: 600; color: #2c3e50;">
                    <i class="fas fa-calendar-alt text-primary mr-2"></i>Cuotas Pendientes
                </h5>
                <?php if (!empty($cuotas)): ?>
                    <span class="badge badge-warning px-3 py-2" style="font-size: 1.2rem;">
                        <i class="fas fa-clock mr-1"></i> <?= count($cuotas) ?> cuotas por pagar
                    </span>
                <?php endif; ?>
            </div>

            <?php
            $total = 0;
            $monthNames = [
                '01' => 'Enero',
                '02' => 'Febrero',
                '03' => 'Marzo',
                '04' => 'Abril',
                '05' => 'Mayo',
                '06' => 'Junio',
                '07' => 'Julio',
                '08' => 'Agosto',
                '09' => 'Septiembre',
                '10' => 'Octubre',
                '11' => 'Noviembre',
                '12' => 'Diciembre'
            ];

            // Count cuotas by status for summary
            $graceCount = 0;
            $urgentGraceCount = 0;
            $vencidaCount = 0;
            $pendienteCount = 0;
            $approachingCount = 0;
            $pagadaCount = 0;

            if (!empty($cuotas)):
                foreach ($cuotas as $cuota) {
                    $dueDateObj = new DateTime($cuota->fecha_vencimiento);
                    $todayObj = new DateTime();
                    $daysUntilDue = $todayObj->diff($dueDateObj)->days;

                    switch ($cuota->estatus) {
                        case 'en_gracias':
                            $dueDateStr = $cuota->fecha_vencimiento;
                            $daysOverdue = $todayObj->diff(new DateTime($dueDateStr))->days;
                            $daysRemaining = 7 - $daysOverdue;
                            if ($daysRemaining <= 2) {
                                $urgentGraceCount++;
                            } else {
                                $graceCount++;
                            }
                            break;
                        case 'vencida':
                            $vencidaCount++;
                            break;
                        case 'pagada':
                            $pagadaCount++;
                            break;
                        default:
                            $dueDateStr = $cuota->fecha_vencimiento;
                            if ($dueDateStr >= $todayObj->format('Y-m-d') && $daysUntilDue <= 3) {
                                $approachingCount++;
                            } else {
                                $pendienteCount++;
                            }
                    }
                }
            ?>

                <!-- Status Summary Badges -->
                <div class="status-summary">
                    <?php if ($pagadaCount > 0): ?>
                        <span class="status-badge status-paid"><i class="fas fa-check-circle"></i> Pagadas: <?= $pagadaCount ?></span>
                    <?php endif; ?>
                    <?php if ($urgentGraceCount > 0): ?>
                        <span class="status-badge status-urgent"><i class="fas fa-exclamation-circle"></i> Urgentes: <?= $urgentGraceCount ?></span>
                    <?php endif; ?>
                    <?php if ($graceCount > 0): ?>
                        <span class="status-badge status-grace"><i class="fas fa-hourglass-half"></i> Período de Gracia: <?= $graceCount ?></span>
                    <?php endif; ?>
                    <?php if ($approachingCount > 0): ?>
                        <span class="status-badge status-approaching"><i class="fas fa-clock"></i> Próximos: <?= $approachingCount ?></span>
                    <?php endif; ?>
                    <?php if ($pendienteCount > 0): ?>
                        <span class="status-badge status-pending"><i class="fas fa-calendar"></i> Pendientes: <?= $pendienteCount ?></span>
                    <?php endif; ?>
                    <?php if ($vencidaCount > 0): ?>
                        <span class="status-badge status-overdue"><i class="fas fa-exclamation-triangle"></i> Vencidas: <?= $vencidaCount ?></span>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover cuotas-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">Mes / Año</th>
                                        <th class="text-center">Período de Cobertura</th>
                                        <th class="text-center">Vence</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Monto</th>
                                        <th class="text-center"><i class="fas fa-check-circle"></i> Sel.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cuotas as $cuota): ?>
                                        <?php
                                        $cuotaNumero = $cuota->numero_cuota ?? 0;
                                        $monto = !empty($cuota->monto_usd) ? $cuota->monto_usd : $cuota->monto;
                                        $total += (float)$monto;

                                        $dueDate = new DateTime($cuota->fecha_vencimiento);
                                        $monthNum = $dueDate->format('m');
                                        $year = $dueDate->format('Y');
                                        $monthName = $monthNames[$monthNum] ?? $dueDate->format('F');
                                        $formattedDueDate = Yii::$app->formatter->asDate($cuota->fecha_vencimiento, 'php:d/m/Y');

                                        $today = date('Y-m-d');
                                        $status = $cuota->estatus;
                                        $dueDateStr = $dueDate->format('Y-m-d');

                                        // Determine status display
                                        $statusBadge = '';
                                        $rowClass = '';

                                        if ($status == 'pagada') {
                                            $statusBadge = '<span class="status-badge status-paid"><i class="fas fa-check-circle"></i>PAGADA</span>';
                                        } elseif ($status == 'en_gracias') {
                                            $dueDateObj = new DateTime($dueDateStr);
                                            $todayObj = new DateTime($today);
                                            $interval = $todayObj->diff($dueDateObj);
                                            $daysOverdue = $interval->days;
                                            $daysRemaining = 7 - $daysOverdue;

                                            if ($daysRemaining <= 2) {
                                                $statusBadge = '<span class="status-badge status-urgent"><i class="fas fa-exclamation-circle"></i> PERÍODO DE GRACIA (' . $daysRemaining . ' días)</span>';
                                            } else {
                                                $statusBadge = '<span class="status-badge status-grace"><i class="fas fa-hourglass-half"></i> PERÍODO DE GRACIA (' . $daysRemaining . ' días)</span>';
                                            }
                                        } elseif ($status == 'vencida') {
                                            $statusBadge = '<span class="status-badge status-overdue"><i class="fas fa-exclamation-circle"></i>VENCIDA - PAGAR AHORA</span>';
                                        } else {
                                            $dueDateObj = new DateTime($dueDateStr);
                                            $todayObj = new DateTime($today);
                                            $daysUntilDue = $todayObj->diff($dueDateObj)->days;

                                            if ($dueDateStr >= $today && $daysUntilDue <= 3) {
                                                $statusBadge = '<span class="status-badge status-approaching"><i class="fas fa-clock"></i>PRÓXIMO (' . $daysUntilDue . ' días)</span>';
                                            } else {
                                                $statusBadge = '<span class="status-badge status-pending"><i class="fas fa-clock"></i>PENDIENTE</span>';
                                            }
                                        }

                                        $dueDateClass = ($dueDateStr < $today && $status != 'pagada') ? 'overdue' : '';
                                        ?>
                                        <tr>
                                            <td class="text-center">
                                                <span class="cuota-number"><?= str_pad($cuotaNumero, 2, '0', STR_PAD_LEFT) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="cuota-month"><?= $monthName ?> <?= $year ?></span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($cuota->coverage_start && $cuota->coverage_end):
                                                    $start = new DateTime($cuota->coverage_start);
                                                    $end = new DateTime($cuota->coverage_end);
                                                ?>
                                                    <span class="cuota-coverage"><?= $start->format('d/m/Y') ?> - <?= $end->format('d/m/Y') ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="cuota-due-date <?= $dueDateClass ?>"
                                                    <?= ($dueDateStr < $today && $status != 'pagada') ? 'data-tooltip="Fecha de vencimiento expirada"' : '' ?>>
                                                    <?= $formattedDueDate ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?= $statusBadge ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="cuota-amount">$<?= number_format($monto, 2) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <div class="custom-control custom-checkbox">
                                                    <?= Html::checkbox('selected_cuotas[]', false, [
                                                        'value' => $cuota->id,
                                                        'id' => 'cuota-' . $cuota->id,
                                                        'class' => 'custom-control-input cuota-checkbox',
                                                        'data-monto' => $monto,
                                                        'disabled' => ($status == 'pagada') ? true : false
                                                    ]) ?>
                                                    <label class="custom-control-label" for="cuota-<?= $cuota->id ?>"></label>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                    <!-- Total Row - Aligned with MONTO column -->
                                    <tr class="total-row">
                                        <td colspan="4" class="text-right">
                                            <span class="total-label">
                                                <i class="fas fa-chart-line mr-2"></i>TOTAL SELECCIONADO
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span style="opacity: 0.5;">—</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="total-amount" id="selected-total">
                                                <i class="fas fa-dollar-sign mr-1"></i>0.00
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <i class="fas fa-arrow-right text-success" style="font-size: 1.2rem;"></i>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="alert alert-success d-flex align-items-center alert-empty">
                    <i class="fas fa-check-circle fa-3x mr-4"></i>
                    <div>
                        <strong style="font-size: 1.4rem;">✅ No hay cuotas pendientes</strong>
                        <?php if ($user_id): ?>
                            <div class="text-muted mt-2" style="font-size: 1.2rem;">
                                <i class="fas fa-user mr-2"></i> Usuario ID: <?= $user_id ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-4">
            <?= $form->field($model, 'monto_pagado')->textInput([
                'class' => 'form-control',
                'placeholder' => 'Ingrese el monto pagado',
                'id' => 'pagos-monto_pagado',
                'readonly' => true,
            ])->label('Monto a Pagar en USD' . '<span class="required-field"></span>') ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'tasa')->textInput([
                'class' => 'form-control',
                'type' => 'number',
                'step' => '0.0001',
                'placeholder' => 'Ingrese la tasa de cambio',
                'id' => 'pagos-tasa',
            ])->label('Tasa de Cambio USD a Bs(BCV)' . '<span class="required-field"></span>') ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'monto_usd')->textInput([
                'class' => 'form-control',
                'readonly' => true,
                'placeholder' => 'Monto en Bs (calculado)',
                'id' => 'pagos-monto_usd',
            ])->label('Monto en Bs' . '<span class="required-field"></span>') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'numero_referencia_pago')->textInput([
                'class' => 'form-control',
                'type' => 'text',
                'placeholder' => 'Ingrese el número de referencia del pago',
                'disabled' => $disabled,
                'id' => 'pagos-numero_referencia_pago',
            ])->label('Número de Referencia' . '<span class="required-field"></span>') ?>
        </div>
    </div>

    <h4 class="mt-4 mb-4 text-info border-bottom pb-2" style="font-size: 1.4rem;">
        <i class="fas fa-file-upload mr-2"></i> Comprobante de Pago
    </h4>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'imagen_prueba_file')->widget(FileInput::classname(), [
                'options' => ['accept' => 'image/*', 'disabled' => $disabled, 'id' => 'pagos-imagen_prueba_file'],
                'pluginOptions' => [
                    'theme' => 'fa5',
                    'browseClass' => 'btn btn-primary',
                    'removeClass' => 'btn btn-danger',
                    'uploadClass' => 'btn btn-info',
                    'browseIcon' => '<i class="fas fa-folder-open"></i> ',
                    'removeIcon' => '<i class="fas fa-trash"></i> ',
                    'browseLabel' => ' Examinar',
                    'removeLabel' => ' Quitar',
                    'showUpload' => false,
                    'showCancel' => false,
                    'previewFileType' => 'image',
                    'maxFileSize' => 2800,
                    'msgSizeTooLarge' => 'El archivo "{name}" ({size} KB) excede el tamaño máximo permitido de {maxSize} KB.',
                    'layoutTemplates' => [
                        'main1' => '{preview}{browse}{remove}',
                        'main2' => '{preview}{browse}{remove}',
                        'actions' => '<div class="file-actions" style="display: flex; gap: 12px;">{browse}{remove}</div>',
                    ],
                    'previewSettings' => [
                        'image' => [
                            'width' => '100%',
                            'height' => 'auto',
                            'max-width' => '250px'
                        ]
                    ],
                ],
            ])->label('Adjuntar Comprobante (JPG, PNG)' . '<span class="required-field"></span>') ?>
        </div>
    </div>

    <?php if (!$disabled): ?>
        <div class="form-group mt-4 text-center">
            <?php if (!empty($cuotas)): ?>
                <?= Html::submitButton('<i class="fas fa-save mr-2"></i> Guardar Pago', [
                    'class' => 'btn btn-success btn-lg px-5',
                    'id' => 'submit-btn',
                ]) ?>
            <?php endif; ?>
            <?= Html::a('<i class="fas fa-undo mr-2"></i> Volver', ['contratos/index', 'user_id' => $model->user_id], [
                'class' => 'btn btn-secondary btn-lg px-5 ml-2'
            ]) ?>
        </div>
    <?php endif; ?>

    <?php ActiveForm::end(); ?>

</div>