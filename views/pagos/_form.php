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
/** @var array $cuotas */
/** @var app\models\Contratos|null $selectedContrato */

$user_id = isset($user_id) ? $user_id : ($model->user_id ?? null);

// ===== ENSURE $cuotas IS ALWAYS DEFINED =====
if (!isset($cuotas)) {
    $cuotas = [];
}

// ===== ENSURE $selectedContrato IS ALWAYS DEFINED =====
if (!isset($selectedContrato)) {
    $selectedContrato = null;
}

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
        
// ============================================================
// VALIDATION: Check if all mandatory fields are filled
// ============================================================
$('#pago-form').on('submit', function(e) {
    // Prevent default submission first
    e.preventDefault();
    e.stopPropagation();
    
    var errors = [];
    var isValid = true;
    var errorFields = [];
    
    // 1. Check if at least one cuota is selected
    var selectedCount = $('.cuota-checkbox:checked').length;
    if (selectedCount === 0) {
        errors.push('⚠️ Debe seleccionar al menos una cuota para realizar el pago.');
        isValid = false;
    }
    
    // 2. Check if Método de Pago is selected
    var metodoPago = $('#pagos-metodo_pago').val();
    if (!metodoPago || metodoPago === '') {
        errors.push('⚠️ Debe seleccionar un método de pago.');
        isValid = false;
        errorFields.push('pagos-metodo_pago');
    }
    
    // 3. Check if Fecha de Pago is filled
    var fechaPago = $('#fecha-pago').val();
    if (!fechaPago || fechaPago === '') {
        errors.push('⚠️ Debe seleccionar la fecha de pago.');
        isValid = false;
        errorFields.push('fecha-pago');
    }
    
    // 4. Check if Tasa de Cambio is filled and greater than 0
    var tasa = parseFloat($('#pagos-tasa').val()) || 0;
    if (tasa <= 0) {
        errors.push('⚠️ Debe ingresar la tasa de cambio (mayor a 0).');
        isValid = false;
        errorFields.push('pagos-tasa');
    }
    
    // 5. Check if payment amount is greater than 0
    var montoPagado = parseFloat($('#pagos-monto_pagado').val()) || 0;
    if (montoPagado <= 0) {
        errors.push('⚠️ El monto a pagar debe ser mayor a 0.');
        isValid = false;
    }
    
    // 6. Check Número de Referencia (unless Cash in Dollars)
    var isCashDollar = (metodoPago === 'Efectivo - Dólar ($)');
    
    if (!isCashDollar) {
        var referencia = $('#pagos-numero_referencia_pago').val();
        if (!referencia || referencia.trim() === '') {
            errors.push('⚠️ Debe ingresar el número de referencia del pago.');
            isValid = false;
            errorFields.push('pagos-numero_referencia_pago');
        }
    }
    
    // 7. Check Comprobante de Pago (unless Cash in Dollars)
    if (!isCashDollar) {
        var hasFile = $('#pagos-imagen_prueba_file').val() !== '';
        // Check if there's an existing file (for edit mode)
        var existingFile = $('#pagos-imagen_prueba_file').data('existing-file') || '';
        if (!hasFile && !existingFile) {
            errors.push('⚠️ Debe adjuntar el comprobante de pago (JPG, PNG).');
            isValid = false;
            errorFields.push('pagos-imagen_prueba_file');
        }
    }
    
    // 8. Check if Monto en Bs is calculated
    var montoBs = parseFloat($('#pagos-monto_usd').val()) || 0;
    if (montoBs <= 0) {
        errors.push('⚠️ El monto en Bs no se ha calculado correctamente.');
        isValid = false;
    }
    
    // If there are errors, show them and prevent submission
    if (!isValid) {
        // Build error message
        var errorMessage = '❌ Por favor corrija los siguientes errores:\n\n';
        errors.forEach(function(error) {
            errorMessage += '• ' + error + '\n';
        });
        errorMessage += '\n\nPor favor complete todos los campos obligatorios antes de continuar.';
        
        alert(errorMessage);
        
        // Highlight error fields
        errorFields.forEach(function(fieldId) {
            $('#' + fieldId).addClass('is-invalid').css('border-color', '#d13438');
            $('#' + fieldId).closest('.form-group').addClass('has-error');
        });
        
        // Scroll to first error
        if (errorFields.length > 0) {
            $('#' + errorFields[0]).focus();
        }
        
        return false;
    }
    
    // If all validations pass, show loading overlay
    showLoading();
    
    // Disable submit button
    var submitBtn = $('#submit-btn');
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Guardando...');
    
    // Now submit the form programmatically
    // Use the native form submit to bypass jQuery validation
    this.submit();
    
    return true;
});

        // ============================================================
        // Function to update Bs amount with 4 decimals
        // ============================================================
        function updateMontoBs() {
            var montoUsd = parseFloat($('#pagos-monto_pagado').val()) || 0;
            var tasa = parseFloat($('#pagos-tasa').val()) || 0;
            var montoBs = montoUsd * tasa;
            $('#pagos-monto_usd').val(montoBs.toFixed(4));
        }

        // ============================================================
        // Function to update field visibility
        // ============================================================
        function updateFieldsVisibility() {
            var metodo = $('#pagos-metodo_pago').val();
            var isCashDollar = (metodo === 'Efectivo - Dólar ($)');
            
            if (isCashDollar) {
                $('.field-pagos-numero_referencia_pago').hide();
                $('#pagos-numero_referencia_pago').val('');
                $('.field-pagos-imagen_prueba_file').hide();
                $('#cash-info-message').show();
            } else {
                $('.field-pagos-numero_referencia_pago').show();
                $('.field-pagos-imagen_prueba_file').show();
                $('#cash-info-message').hide();
            }
        }

        // ============================================================
        // Calculate selected cuotas total with row highlighting
        // ============================================================
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
                    $('.cuotas-selected-counter').html('<span class="selected-counter"><i class="fas fa-check-circle mr-1"></i>' + selectedCount + ' seleccionada(s)</span>');
                } else {
                    counterBadge.html('<i class="fas fa-check-circle mr-1"></i>' + selectedCount + ' seleccionada(s)');
                }
            } else {
                counterBadge.remove();
                $('.cuotas-selected-counter').html('');
            }
        }

        // ============================================================
        // IMMEDIATE ROW HIGHLIGHTING ON CHECKBOX CHANGE
        // ============================================================
        $(document).on('change', '.cuota-checkbox', function() {
            var $row = $(this).closest('tr');
            if ($(this).is(':checked')) {
                $row.addClass('selected-cuota-row');
            } else {
                $row.removeClass('selected-cuota-row');
            }
            updateMontoSelected();
        });

        // ============================================================
        // Event handlers with 4 decimal support
        // ============================================================
        $('#fecha-pago').on('change', function() {
            var fecha = $(this).val();
            if (fecha) {
                $.ajax({
                    url: '../site/tasacambio',
                    type: 'post',
                    data: { fecha: fecha },
                    success: function(response) {
                        if (response) {
                            var tasaValue = parseFloat(response);
                            if (!isNaN(tasaValue) && tasaValue > 0) {
                                $('#pagos-tasa').val(tasaValue.toFixed(4));
                                updateMontoBs();
                            } else {
                                $('#pagos-tasa').val('');
                            }
                        }
                    }
                });
            }
        });

        // ============================================================
        // Update on manual change with 4 decimal support
        // ============================================================
        $('#pagos-tasa').on('change keyup', function() {
            var val = parseFloat($(this).val());
            if (!isNaN(val) && val > 0) {
                $(this).val(val.toFixed(4));
            }
            updateMontoBs();
        });

        $('#pagos-monto_pagado').on('change keyup', updateMontoBs);
        $('#pagos-metodo_pago').on('change', updateFieldsVisibility);

        // Initialize any pre-checked checkboxes (for edit mode)
        $('.cuota-checkbox:checked').each(function() {
            $(this).closest('tr').addClass('selected-cuota-row');
        });
        
        updateMontoSelected();
        updateFieldsVisibility();
        updateMontoBs();

        // ============================================================
        // ===== REAL PROGRESS TRACKING WITH ICONS =====
        // ============================================================
        var progressInterval = null;
        var currentStep = 1;
        var isRedirecting = false;
        var stepProgress = {
            step1: { progress: 0, max: 100, active: true },
            step2: { progress: 0, max: 100, active: false },
            step3: { progress: 0, max: 100, active: false }
        };

        var icons = {
            step1: {
                pending: 'fa-hourglass-start',
                active: 'fa-spinner fa-spin',
                done: 'fa-check-circle',
                subIcons: {
                    validating: 'fa-shield-alt',
                    saving: 'fa-save',
                    confirming: 'fa-check-double'
                }
            },
            step2: {
                pending: 'fa-hourglass-start',
                active: 'fa-spinner fa-spin',
                done: 'fa-check-circle',
                subIcons: {
                    generating: 'fa-cogs',
                    assembling: 'fa-users',
                    finalizing: 'fa-file-invoice'
                }
            },
            step3: {
                pending: 'fa-hourglass-start',
                active: 'fa-spinner fa-spin',
                done: 'fa-check-circle',
                subIcons: {
                    preparing: 'fa-envelope-open-text',
                    attaching: 'fa-paperclip',
                    sending: 'fa-paper-plane',
                    confirming: 'fa-check-double'
                }
            }
        };

        function showLoading() {
            $('#loadingOverlay').addClass('active');
            resetProgress();
            updateStepDisplay(1);
            $('#progressFill').css('width', '0%');
            $('#progressText').text('0%');
            $('.loading-title').html('<i class="fas fa-credit-card mr-2"></i> Procesando Pago');
            $('.loading-subtitle').html('<i class="fas fa-info-circle mr-2"></i> Por favor espere, estamos registrando su pago...');
            $('.loading-wait-message').hide();
            startRealProgress();
        }

        function resetProgress() {
            currentStep = 1;
            isRedirecting = false;
            stepProgress = {
                step1: { progress: 0, max: 100, active: true },
                step2: { progress: 0, max: 100, active: false },
                step3: { progress: 0, max: 100, active: false }
            };
            
            $('.loading-step').removeClass('active done pending');
            $('#step1').addClass('active');
            $('#step2, #step3').addClass('pending');
            
            updateStepIcon(1, 'pending');
            updateStepIcon(2, 'pending');
            updateStepIcon(3, 'pending');
            
            $('.loading-wait-message').hide();
            $('.loading-spinner-small').hide();
            
            if (progressInterval) {
                clearInterval(progressInterval);
                progressInterval = null;
            }
        }

        function updateStepIcon(step, state, subState) {
            var $icon = $('#step' + step + ' .loading-step-icon i');
            var iconMap = {
                1: icons.step1,
                2: icons.step2,
                3: icons.step3
            };
            
            var iconSet = iconMap[step];
            var iconClass = '';
            
            if (state === 'pending') {
                iconClass = iconSet.pending;
            } else if (state === 'active') {
                iconClass = iconSet.active;
            } else if (state === 'done') {
                iconClass = iconSet.done;
            } else if (state === 'sub' && subState && iconSet.subIcons[subState]) {
                iconClass = iconSet.subIcons[subState];
            }
            
            $icon.removeClass().addClass('fas ' + iconClass);
            
            if (state === 'active') {
                $icon.addClass('fa-spin');
            }
        }

        function startRealProgress() {
            updateRealProgress(1, 5, 'Guardando datos del pago...', 'validating');
            
            var step1Interval = setInterval(function() {
                if (currentStep === 1 && !isRedirecting) {
                    var currentProgress = stepProgress.step1.progress;
                    if (currentProgress < 33) {
                        var increment = Math.random() * 3 + 1;
                        var newProgress = Math.min(currentProgress + increment, 33);
                        stepProgress.step1.progress = newProgress;
                        updateTotalProgress();
                        
                        if (newProgress < 15) {
                            updateRealProgress(1, newProgress, 'Validando datos del pago...', 'validating');
                        } else if (newProgress < 25) {
                            updateRealProgress(1, newProgress, 'Guardando información del pago...', 'saving');
                        } else {
                            updateRealProgress(1, newProgress, 'Confirmando transacción...', 'confirming');
                        }
                    } else {
                        clearInterval(step1Interval);
                        stepProgress.step1.progress = 33;
                        updateTotalProgress();
                        updateStepDisplay(2);
                        stepProgress.step2.progress = 33;
                        updateTotalProgress();
                        
                        var step2Interval = setInterval(function() {
                            if (currentStep === 2 && !isRedirecting) {
                                var currentProgress = stepProgress.step2.progress;
                                if (currentProgress < 66) {
                                    var increment = Math.random() * 2 + 0.5;
                                    var newProgress = Math.min(currentProgress + increment, 66);
                                    stepProgress.step2.progress = newProgress;
                                    updateTotalProgress();
                                    
                                    if (newProgress < 45) {
                                        updateRealProgress(2, newProgress, 'Generando estructura del recibo...', 'generating');
                                    } else if (newProgress < 55) {
                                        updateRealProgress(2, newProgress, 'Armando datos del afiliado...', 'assembling');
                                    } else {
                                        updateRealProgress(2, newProgress, 'Finalizando generación de recibos...', 'finalizing');
                                    }
                                } else {
                                    clearInterval(step2Interval);
                                    stepProgress.step2.progress = 66;
                                    updateTotalProgress();
                                    updateStepDisplay(3);
                                    stepProgress.step3.progress = 66;
                                    updateTotalProgress();
                                    
                                    var step3Interval = setInterval(function() {
                                        if (currentStep === 3 && !isRedirecting) {
                                            var currentProgress = stepProgress.step3.progress;
                                            if (currentProgress < 100) {
                                                var increment = Math.random() * 1.5 + 0.5;
                                                var newProgress = Math.min(currentProgress + increment, 100);
                                                stepProgress.step3.progress = newProgress;
                                                updateTotalProgress();
                                                
                                                if (newProgress < 75) {
                                                    updateRealProgress(3, newProgress, 'Preparando correo electrónico...', 'preparing');
                                                } else if (newProgress < 85) {
                                                    updateRealProgress(3, newProgress, 'Adjuntando recibos al correo...', 'attaching');
                                                } else if (newProgress < 95) {
                                                    updateRealProgress(3, newProgress, 'Enviando correo electrónico...', 'sending');
                                                } else {
                                                    updateRealProgress(3, newProgress, 'Confirmando entrega del correo...', 'confirming');
                                                }
                                            } else {
                                                clearInterval(step3Interval);
                                                stepProgress.step3.progress = 100;
                                                updateTotalProgress();
                                                completeProgress();
                                            }
                                        }
                                    }, 300);
                                }
                            }
                        }, 250);
                    }
                }
            }, 200);
        }

        function updateStepDisplay(step) {
            currentStep = step;
            
            $('.loading-step').removeClass('active done pending');
            
            for (var i = 1; i <= 3; i++) {
                var $step = $('#step' + i);
                if (i < step) {
                    $step.addClass('done');
                    updateStepIcon(i, 'done');
                } else if (i === step) {
                    $step.addClass('active');
                    updateStepIcon(i, 'active');
                } else {
                    $step.addClass('pending');
                    updateStepIcon(i, 'pending');
                }
            }
        }

        function updateRealProgress(step, progress, message, subIcon) {
            updateTotalProgress();
            
            var iconMap = {
                validating: 'fa-shield-alt',
                saving: 'fa-save',
                confirming: 'fa-check-double',
                generating: 'fa-cogs',
                assembling: 'fa-users',
                finalizing: 'fa-file-invoice',
                preparing: 'fa-envelope-open-text',
                attaching: 'fa-paperclip',
                sending: 'fa-paper-plane'
            };
            
            var iconClass = iconMap[subIcon] || 'fa-info-circle';
            $('.loading-subtitle').html('<i class="fas ' + iconClass + ' mr-2"></i> ' + message);
            
            if (subIcon) {
                updateStepIcon(step, 'sub', subIcon);
            }
        }

        function updateTotalProgress() {
            var totalProgress = 0;
            var totalMax = 0;
            
            for (var key in stepProgress) {
                if (stepProgress.hasOwnProperty(key)) {
                    totalProgress += stepProgress[key].progress;
                    totalMax += stepProgress[key].max;
                }
            }
            
            var percentage = Math.round((totalProgress / totalMax) * 100);
            $('#progressFill').css('width', percentage + '%');
            $('#progressText').text(percentage + '%');
        }

        function completeProgress() {
            isRedirecting = true;
            
            $('#progressFill').css('width', '100%');
            $('#progressText').text('100%');
            $('.loading-title').html('✅ ¡Pago Completado!');
            $('.loading-subtitle').html('<i class="fas fa-arrow-right mr-2"></i> Redirigiendo a la vista de recibos...');
            
            $('.loading-wait-message').show();
            $('.loading-spinner-small').show();
            
            $('.loading-step').removeClass('active pending').addClass('done');
            updateStepIcon(1, 'done');
            updateStepIcon(2, 'done');
            updateStepIcon(3, 'done');
            
            startWaitAnimation();
        }

        function startWaitAnimation() {
            var dots = 0;
            var waitInterval = setInterval(function() {
                if (isRedirecting) {
                    dots = (dots % 3) + 1;
                    var dotsText = '.'.repeat(dots);
                    $('.loading-wait-text').html('<i class="fas fa-hourglass-half mr-2"></i> Espere un momento, estamos redirigiendo' + dotsText);
                    $('.loading-wait-subtext').html('<i class="fas fa-exclamation-circle mr-2"></i> Por favor no cierre esta ventana mientras completamos el proceso...');
                } else {
                    clearInterval(waitInterval);
                }
            }, 500);
            
            window.waitInterval = waitInterval;
        }

        // ============================================================
        // HANDLE FLASH MESSAGES
        // ============================================================
        $(document).ready(function() {
            var flashMessage = $('.alert-success, .alert-danger, .alert-warning');
            if (flashMessage.length > 0) {
                $('#loadingOverlay').removeClass('active');
                resetProgress();
                if (window.waitInterval) {
                    clearInterval(window.waitInterval);
                }
            }
            
            if (window.location.href.indexOf('payment_id') === -1 && window.location.href.indexOf('pagos/create') === -1) {
                $('#loadingOverlay').removeClass('active');
                resetProgress();
                if (window.waitInterval) {
                    clearInterval(window.waitInterval);
                }
            }
            
            if (window.location.href.indexOf('view-receipts') !== -1) {
                $('#loadingOverlay').removeClass('active');
                resetProgress();
                if (window.waitInterval) {
                    clearInterval(window.waitInterval);
                }
            }
        });
    });
JS
);
?>

<style>
    /* ===== MICROSOFT FLUENT DESIGN SYSTEM - BULLETPROOF ===== */

    /* Typography */
    body,
    .pagos-form {
        font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, system-ui, sans-serif !important;
    }

    /* ===== FORM CONTAINER ===== */
    .fluent-container {
        background: white !important;
        border-radius: 12px !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06) !important;
        overflow: hidden !important;
        border: 1px solid #edebe9 !important;
    }

    .fluent-header {
        background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%) !important;
        padding: 20px 28px !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        border-bottom: 1px solid #106ebe !important;
    }

    .fluent-header .header-title {
        color: white !important;
        font-size: 20px !important;
        font-weight: 600 !important;
        margin: 0 !important;
    }

    .fluent-header .header-title i {
        margin-right: 10px !important;
    }

    .fluent-header .header-badge {
        background: rgba(255, 255, 255, 0.2) !important;
        color: white !important;
        padding: 6px 16px !important;
        border-radius: 20px !important;
        font-size: 12px !important;
        font-weight: 500 !important;
    }

    .fluent-body {
        padding: 28px 30px !important;
    }

    /* ===== SECTIONS - 100% BULLETPROOF ===== */
    .section-title {
        font-size: 22px !important;
        font-weight: 700 !important;
        color: #1a3a6e !important;
        margin: 0 0 20px 0 !important;
        padding: 16px 24px !important;
        background: linear-gradient(135deg, #e2e8f0 0%, #f1f5f9 100%) !important;
        border-radius: 10px !important;
        border-left: 6px solid #0078d4 !important;
        display: flex !important;
        align-items: center !important;
        gap: 14px !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06) !important;
        min-height: 68px !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .section-title i:first-child {
        color: #0078d4 !important;
        font-size: 24px !important;
        width: 42px !important;
        height: 42px !important;
        background: white !important;
        border-radius: 50% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        box-shadow: 0 2px 8px rgba(0, 120, 212, 0.15) !important;
        flex-shrink: 0 !important;
    }

    .section-title .section-title-text {
        font-size: 20px !important;
        font-weight: 700 !important;
        color: #1a3a6e !important;
        flex: 1 !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        min-width: 0 !important;
    }

    .section-title .section-badge {
        margin-left: auto !important;
        background: linear-gradient(135deg, #0078d4, #005a9e) !important;
        color: white !important;
        padding: 6px 18px !important;
        border-radius: 20px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        white-space: nowrap !important;
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        flex-shrink: 0 !important;
        border: none !important;
    }

    .section-title .section-badge i {
        background: transparent !important;
        width: auto !important;
        height: auto !important;
        box-shadow: none !important;
        font-size: 13px !important;
        color: white !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .section-title .cuotas-selected-counter {
        display: inline-flex !important;
        align-items: center !important;
        margin-left: 4px !important;
        flex-shrink: 0 !important;
    }

    /* ===== SECTION CARDS ===== */
    .section-card {
        border-radius: 12px !important;
        border: 1px solid #edebe9 !important;
        overflow: hidden !important;
        margin-bottom: 28px !important;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04) !important;
        transition: box-shadow 0.2s ease !important;
    }

    .section-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06) !important;
    }

    /* ===== CUOTAS TABLE - PROFESSIONAL & BEAUTIFUL ===== */
    .cuotas-table {
        font-size: 13px !important;
        margin-bottom: 0 !important;
        width: 100% !important;
    }

    .cuotas-table thead th {
        font-size: 11px !important;
        font-weight: 600 !important;
        padding: 14px 12px !important;
        background: #1e293b !important;
        color: #ffffff !important;
        border-bottom: 2px solid #e1dfdd !important;
        text-align: center !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        white-space: nowrap !important;
    }

    .cuotas-table tbody td {
        padding: 12px 10px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #edebe9 !important;
        text-align: center !important;
        background: #ffffff !important;
        /* This is the white layer masking everything */
    }

    .cuotas-table tbody tr {
        transition: background 0.15s ease !important;
    }

    /* FIX 1: Make cells transparent on hover so the row color shows */
    .cuotas-table tbody tr:hover td {
        background-color: transparent !important;
    }

    .cuotas-table tbody tr:hover {
        background: #dfeef8 !important;
    }

    /* FIX 2: Make cells transparent when selected so the blue color shows */
    .cuotas-table tbody tr.selected-cuota-row td {
        background-color: transparent !important;
    }

    .cuotas-table tbody tr.selected-cuota-row {
        background: #e0f2fe !important;
        /* Using the clean soft blue we talked about */
        border-left: 3px solid #0078d4 !important;
    }

    .cuotas-table tbody tr.selected-cuota-row .cuota-number {
        background: linear-gradient(135deg, #004578, #002b4a) !important;
        box-shadow: 0 2px 8px rgba(0, 120, 212, 0.3) !important;
        transform: scale(1.05) !important;
    }


    /* Cuota Number Badge */
    .cuota-number {
        width: 38px !important;
        height: 38px !important;
        background: linear-gradient(135deg, #0078d4, #005a9e) !important;
        color: white !important;
        border-radius: 50% !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 14px !important;
        font-weight: 700 !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 2px 4px rgba(0, 120, 212, 0.15) !important;
    }

    /* Month/Year */
    .cuota-month {
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #323130 !important;
    }

    /* Coverage Period */
    .cuota-coverage {
        font-size: 12px !important;
        color: #605e5c !important;
        white-space: nowrap !important;
    }

    .cuota-coverage i {
        color: #0078d4 !important;
        font-size: 11px !important;
    }

    /* Due Date */
    .cuota-due-date {
        font-size: 13px !important;
        font-weight: 500 !important;
        color: #323130 !important;
    }

    .cuota-due-date.overdue {
        color: #d13438 !important;
        font-weight: 700 !important;
    }

    .cuota-due-date.overdue::before {
        content: "⚠️ ";
        font-size: 12px !important;
    }

    /* Amount */
    .cuota-amount {
        font-size: 15px !important;
        font-weight: 700 !important;
        color: #107c10 !important;
    }

    /* ===== STATUS BADGES ===== */
    .status-badge {
        display: inline-flex !important;
        align-items: center !important;
        padding: 5px 14px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        border-radius: 20px !important;
        white-space: nowrap !important;
        gap: 6px !important;
        letter-spacing: 0.3px !important;
    }

    .status-paid {
        background: linear-gradient(135deg, #dff6dd, #b7e6b3) !important;
        color: #107c10 !important;
        border: 1px solid #92d48e !important;
    }

    .status-grace {
        background: linear-gradient(135deg, #fff4ce, #ffe7a0) !important;
        color: #8b6b00 !important;
        border: 1px solid #f2c94c !important;
    }

    .status-urgent {
        background: linear-gradient(135deg, #fce4d6, #f8c9b5) !important;
        color: #d13438 !important;
        border: 1px solid #f28b82 !important;
        animation: pulse-urgent 2s infinite !important;
    }

    .status-overdue {
        background: linear-gradient(135deg, #fce4d6, #f5b8a0) !important;
        color: #d13438 !important;
        border: 1px solid #e4606d !important;
    }

    .status-approaching {
        background: linear-gradient(135deg, #d6eff8, #a8d8ed) !important;
        color: #005a9e !important;
        border: 1px solid #7bc0d8 !important;
    }

    .status-pending {
        background: linear-gradient(135deg, #e8e8e8, #d4d4d4) !important;
        color: #605e5c !important;
        border: 1px solid #c8c6c4 !important;
    }

    @keyframes pulse-urgent {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(209, 52, 56, 0.3);
        }

        50% {
            box-shadow: 0 0 0 6px rgba(209, 52, 56, 0);
        }
    }

    /* ===== TOTAL ROW (FIJO Y ESTABLE) ===== */
    /* 1. Aplicamos el color base a la fila por estructura */
    .cuotas-table tbody tr.total-row {
        background: linear-gradient(135deg, #9df09d 0%, #e6f5e6 100%) !important;
        border-top: 2px solid #107c10 !important;
        border-bottom: 2px solid #107c10 !important;
    }

    /* 2. FIX DEFINITIVO: Obligamos a los TDs de esta fila específica a ser transparentes SIEMPRE, no solo en hover */
    .cuotas-table tbody tr.total-row td {
        background-color: transparent !important;
        /* <--- Esto elimina el fondo blanco permanente */
        padding: 14px 10px !important;
        vertical-align: middle !important;
    }

    /* (Opcional) Un pequeño toque extra cuando pasan el mouse encima del total */
    .cuotas-table tbody tr.total-row:hover td {
        background-color: rgba(16, 124, 16, 0.05) !important;
        /* Un destello verde interactivo al hacer hover */
    }

    .total-amount {
        font-size: 24px !important;
        font-weight: 800 !important;
        color: #107c10 !important;
        display: inline-block !important;
        width: 100% !important;
        text-align: center !important;
        text-shadow: 0 1px 2px rgba(16, 124, 16, 0.1) !important;
        letter-spacing: 0.5px !important;
    }

    .total-label {
        font-size: 15px !important;
        font-weight: 700 !important;
        color: #1e7e34 !important;
    }

    .selected-counter {
        background: #0078d4 !important;
        color: #ffffff !important;
        border-radius: 20px !important;
        padding: 4px 14px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        box-shadow: 0 2px 6px rgba(16, 124, 16, 0.2) !important;
    }

    .cuotas-selected-counter {
        display: inline-block !important;
        margin-left: 10px !important;
    }

    /* ===== CHECKBOX STYLING ===== */
    /* ===== MEJORA DE CHECKBOXES DE CUOTAS (FLUENT DESIGN) ===== */
    .cuotas-table .custom-checkbox {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        position: relative !important;
        min-height: auto !important;
        padding-left: 0 !important;
        margin: 0 !important;
    }

    /* Ocultar el input nativo pero mantenerlo accesible */
    .cuotas-table .custom-control-input {
        position: absolute !important;
        opacity: 0 !important;
        z-index: -1 !important;
    }

    /* El nuevo contenedor visual del checkbox (Aumentado a 24px) */
    .cuotas-table .custom-control-label {
        position: relative !important;
        cursor: pointer !important;
        padding-left: 0 !important;
        margin-bottom: 0 !important;
        display: inline-block !important;
        width: 19px !important;
        height: 19px !important;
    }

    /* El recuadro desmarcado: borde más grueso y oscuro para que resalte en la fila blanca */
    .cuotas-table .custom-control-label::before {
        content: '' !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 19px !important;
        height: 19px !important;
        background-color: #ffffff !important;
        border: 2px solid #323130 !important;
        /* Gris oscuro Fluent de alto contraste */
        border-radius: 4px !important;
        transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    /* Estado: Marcado (Checked) - Cambia a verde éxito con sombra suave */
    .cuotas-table .custom-control-input:checked~.custom-control-label::before {
        background-color: #107c10 !important;
        /* Verde corporativo de tu fila de totales */
        border-color: #107c10 !important;
        box-shadow: 0 2px 6px rgba(16, 124, 16, 0.3) !important;
    }

    /* El check interno (Palomita / Tick) hecho con CSS puro */
    .cuotas-table .custom-control-label::after {
        content: '' !important;
        position: absolute !important;
        top: 4px !important;
        left: 9px !important;
        width: 6px !important;
        height: 12px !important;
        border: solid white !important;
        border-width: 0 3px 3px 0 !important;
        transform: rotate(45deg) scale(0) !important;
        transition: transform 0.15s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
    }

    /* Mostrar la palomita con una pequeña animación elástica al marcar */
    .cuotas-table .custom-control-input:checked~.custom-control-label::after {
        transform: rotate(45deg) scale(1) !important;
    }

    /* Enfoque por teclado o clic (Focus) */
    .cuotas-table .custom-control-input:focus~.custom-control-label::before {
        box-shadow: 0 0 0 3px rgba(16, 124, 16, 0.25) !important;
        border-color: #107c10 !important;
    }

    /* Efecto Hover sobre la fila o el control: se oscurece el borde para invitar al clic */
    .cuotas-table tbody tr:hover .custom-control-label::before {
        border-color: #0078d4 !important;
        /* Cambia a azul Fluent al pasar el mouse por la fila */
    }

    /* Estado Deshabilitado (Cuotas ya pagadas) */
    .cuotas-table .custom-control-input:disabled~.custom-control-label {
        cursor: not-allowed !important;
    }

    .cuotas-table .custom-control-input:disabled~.custom-control-label::before {
        background-color: #f3f2f1 !important;
        border-color: #a19f9d !important;
    }


    /* ===== REMINDER BANNER - EYE-CATCHING ===== */
    .reminder-banner {
        background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%) !important;
        border-left: 15px solid #f50000 !important;
        border-right: 15px solid #f50000 !important;
        border-top: 8px solid #f50000 !important;
        border-bottom: 10px solid #f50000 !important;
        border-radius: 12px !important;
        padding: 16px 28px !important;
        margin: 0 0 24px 0 !important;
        display: flex !important;
        align-items: center !important;
        gap: 20px !important;
        box-shadow: 0 4px 20px rgba(245, 124, 0, 0.25) !important;
        position: relative !important;
        overflow: hidden !important;
        animation: reminderPulse 2s ease-in-out infinite !important;
    }

    /* Animated background pattern */
    .reminder-banner::before {
        content: '' !important;
        position: absolute !important;
        top: -50% !important;
        left: -50% !important;
        width: 200% !important;
        height: 200% !important;
        background: repeating-linear-gradient(45deg,
                transparent,
                transparent 20px,
                rgba(245, 124, 0, 0.05) 20px,
                rgba(245, 124, 0, 0.05) 40px) !important;
        animation: patternMove 8s linear infinite !important;
        pointer-events: none !important;
    }

    @keyframes patternMove {
        0% {
            transform: translateX(0) translateY(0);
        }

        100% {
            transform: translateX(40px) translateY(40px);
        }
    }

    @keyframes reminderPulse {

        0%,
        100% {
            box-shadow: 0 4px 20px rgba(245, 124, 0, 0.25);
            transform: scale(1);
        }

        50% {
            box-shadow: 0 4px 40px rgba(245, 124, 0, 0.4);
            transform: scale(1.005);
        }
    }

    .reminder-banner .reminder-icon {
        background: linear-gradient(135deg, #f57c00, #e65100) !important;
        width: 52px !important;
        height: 52px !important;
        border-radius: 50% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        box-shadow: 0 4px 16px rgba(245, 124, 0, 0.4) !important;
        position: relative !important;
        z-index: 1 !important;
        animation: iconPulse 1.5s ease-in-out infinite !important;
    }

    @keyframes iconPulse {

        0%,
        100% {
            transform: scale(1);
            box-shadow: 0 4px 16px rgba(245, 124, 0, 0.4);
        }

        50% {
            transform: scale(1.08);
            box-shadow: 0 4px 24px rgba(245, 124, 0, 0.6);
        }
    }

    .reminder-banner .reminder-icon i {
        color: white !important;
        font-size: 24px !important;
        animation: iconShake 2s ease-in-out infinite !important;
    }

    @keyframes iconShake {

        0%,
        100% {
            transform: rotate(0deg);
        }

        5% {
            transform: rotate(15deg);
        }

        10% {
            transform: rotate(-15deg);
        }

        15% {
            transform: rotate(10deg);
        }

        20% {
            transform: rotate(-10deg);
        }

        25% {
            transform: rotate(5deg);
        }

        30% {
            transform: rotate(-5deg);
        }

        35% {
            transform: rotate(0deg);
        }
    }

    .reminder-banner .reminder-text {
        flex: 1 !important;
        font-size: 15px !important;
        font-weight: 500 !important;
        color: #4a3000 !important;
        position: relative !important;
        z-index: 1 !important;
    }

    .reminder-banner .reminder-text strong {
        color: #e65100 !important;
        font-weight: 700 !important;
        font-size: 16px !important;
        text-shadow: 0 0 20px rgba(230, 81, 0, 0.15) !important;
    }

    .reminder-banner .reminder-date {
        background: linear-gradient(135deg, #e65100, #bf360c) !important;
        color: white !important;
        padding: 8px 24px !important;
        border-radius: 30px !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        white-space: nowrap !important;
        box-shadow: 0 4px 16px rgba(230, 81, 0, 0.3) !important;
        position: relative !important;
        z-index: 1 !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        animation: dateGlow 2s ease-in-out infinite !important;
    }

    @keyframes dateGlow {

        0%,
        100% {
            box-shadow: 0 4px 16px rgba(230, 81, 0, 0.3);
        }

        50% {
            box-shadow: 0 4px 28px rgba(230, 81, 0, 0.5);
        }
    }

    .reminder-banner .reminder-date i {
        font-size: 16px !important;
        animation: calendarBounce 2s ease-in-out infinite !important;
    }

    @keyframes calendarBounce {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-3px);
        }
    }

    /* ===== Glow ring around the banner ===== */
    .reminder-banner::after {
        content: '' !important;
        position: absolute !important;
        inset: -2px !important;
        border-radius: 14px !important;
        background: linear-gradient(135deg, #f57c00, #ff9800, #f57c00, #e65100) !important;
        background-size: 300% 300% !important;
        animation: glowRing 3s ease-in-out infinite !important;
        z-index: -1 !important;
        opacity: 0.3 !important;
    }

    @keyframes glowRing {

        0%,
        100% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }
    }

    /* ===== FORM FIELDS - FLUENT STYLE ===== */
    .fluent-field {
        margin-bottom: 20px !important;
    }

    .fluent-field .control-label {
        font-weight: 500 !important;
        color: #323130 !important;
        font-size: 14px !important;
        margin-bottom: 6px !important;
        display: block !important;
    }

    .fluent-field .control-label .required {
        color: #d13438 !important;
        margin-left: 4px !important;
    }

    .fluent-field .form-control {
        border: 1px solid #d4d9e2 !important;
        border-radius: 6px !important;
        padding: 10px 14px !important;
        font-size: 14px !important;
        transition: all 0.2s ease !important;
        background: #fafbfc !important;
        height: auto !important;
    }

    .fluent-field .form-control:focus {
        border-color: #0078d4 !important;
        box-shadow: 0 0 0 3px rgba(0, 120, 212, 0.15) !important;
        background: white !important;
    }

    .fluent-field .form-control[readonly] {
        background: #f3f2f1 !important;
        cursor: not-allowed !important;
    }

    .fluent-field .help-block {
        font-size: 12px !important;
        color: #6c757d !important;
        margin-top: 4px !important;
    }

    /* ===== SELECT2 OVERRIDES ===== */
    .select2-container--krajee .select2-selection--single {
        border: 1px solid #d4d9e2 !important;
        border-radius: 6px !important;
        padding: 10px 14px !important;
        height: auto !important;
        background: #fafbfc !important;
        transition: all 0.2s ease !important;
    }

    .select2-container--krajee .select2-selection--single:hover {
        border-color: #a8c4f0 !important;
        background: #f0f6ff !important;
    }

    .select2-container--krajee .select2-selection--single:focus {
        border-color: #0078d4 !important;
        box-shadow: 0 0 0 3px rgba(0, 120, 212, 0.15) !important;
    }

    .select2-container--krajee .select2-selection--single .select2-selection__rendered {
        color: #2c3e50 !important;
        font-size: 14px !important;
        line-height: 1.5 !important;
        padding: 0 !important;
    }

    .select2-dropdown {
        border-color: #d4d9e2 !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
        overflow: hidden !important;
    }

    .select2-container--krajee .select2-results__option {
        padding: 10px 16px !important;
        font-size: 14px !important;
        transition: all 0.15s ease !important;
    }

    .select2-container--krajee .select2-results__option--highlighted {
        background: linear-gradient(135deg, #e8f4f8 0%, #d5ecf5 100%) !important;
        color: #1a5276 !important;
    }

    .select2-container--krajee .select2-results__option[aria-selected="true"] {
        background: linear-gradient(135deg, #e0f2f1 0%, #b2dfdb 100%) !important;
        color: #004d40 !important;
        font-weight: 500 !important;
    }

    /* ===== FILE INPUT ===== */
    .file-input .btn-file {
        background: linear-gradient(135deg, #e8f0fe 0%, #d2e3fc 100%) !important;
        border: 1px solid #8ab4f8 !important;
        color: #1a73e8 !important;
        font-weight: 600 !important;
        padding: 8px 20px !important;
        border-radius: 6px !important;
        transition: all 0.2s ease !important;
    }

    .file-input .btn-file:hover {
        background: linear-gradient(135deg, #d2e3fc 0%, #b8d4fa 100%) !important;
        border-color: #1a73e8 !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 2px 8px rgba(26, 115, 232, 0.25) !important;
    }

    .file-input .btn-remove {
        background: linear-gradient(135deg, #fce8e6 0%, #fad2cf 100%) !important;
        border: 1px solid #f28b82 !important;
        color: #d93025 !important;
        font-weight: 600 !important;
        padding: 8px 20px !important;
        border-radius: 6px !important;
        transition: all 0.2s ease !important;
    }

    .file-input .btn-remove:hover {
        background: linear-gradient(135deg, #fad2cf 0%, #f5b8b4 100%) !important;
        border-color: #d93025 !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 2px 8px rgba(217, 48, 37, 0.25) !important;
    }

    /* ===== BUTTONS ===== */
    .btn-fluent-primary {
        background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%) !important;
        border: none !important;
        color: white !important;
        padding: 12px 36px !important;
        font-size: 16px !important;
        font-weight: 600 !important;
        border-radius: 6px !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 2px 8px rgba(0, 120, 212, 0.2) !important;
    }

    .btn-fluent-primary:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 16px rgba(0, 120, 212, 0.35) !important;
        color: white !important;
    }

    .btn-fluent-primary:disabled {
        opacity: 0.6 !important;
        transform: none !important;
    }

    .btn-fluent-secondary {
        background: #f3f2f1 !important;
        border: 1px solid #d4d9e2 !important;
        color: #323130 !important;
        padding: 12px 36px !important;
        font-size: 16px !important;
        font-weight: 600 !important;
        border-radius: 6px !important;
        transition: all 0.2s ease !important;
    }

    .btn-fluent-secondary:hover {
        background: #e1dfdd !important;
        border-color: #b8c0cc !important;
        color: #323130 !important;
    }

    /* ===== LOADING OVERLAY ===== */
    .loading-overlay {
        display: none !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        background: rgba(0, 0, 0, 0.6) !important;
        z-index: 99999 !important;
        justify-content: center !important;
        align-items: center !important;
        flex-direction: column !important;
        backdrop-filter: blur(4px) !important;
    }

    .loading-overlay.active {
        display: flex !important;
    }

    .loading-box {
        background: white !important;
        border-radius: 16px !important;
        padding: 40px 50px !important;
        max-width: 480px !important;
        width: 90% !important;
        text-align: center !important;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25) !important;
        animation: slideUp 0.3s ease !important;
    }

    @keyframes slideUp {
        from {
            transform: translateY(30px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .loading-spinner {
        width: 72px !important;
        height: 72px !important;
        margin: 0 auto 20px !important;
        border-radius: 50% !important;
        border: 5px solid #e9ecef !important;
        border-top: 5px solid #0078d4 !important;
        animation: spin 1s linear infinite !important;
        position: relative !important;
    }

    .loading-spinner i {
        font-size: 28px !important;
        color: #0078d4 !important;
        position: absolute !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .loading-title {
        font-size: 20px !important;
        font-weight: 700 !important;
        color: #1a3a6e !important;
        margin-bottom: 8px !important;
    }

    .loading-subtitle {
        font-size: 14px !important;
        color: #6c757d !important;
        margin-bottom: 20px !important;
        min-height: 20px !important;
    }

    .loading-steps {
        text-align: left !important;
        margin: 16px 0 !important;
    }

    .loading-step {
        display: flex !important;
        align-items: center !important;
        padding: 8px 0 !important;
        border-bottom: 1px solid #f0f0f0 !important;
        opacity: 0.4 !important;
        transition: all 0.3s ease !important;
    }

    .loading-step.active {
        opacity: 1 !important;
    }

    .loading-step.done {
        opacity: 1 !important;
    }

    .loading-step-icon {
        width: 30px !important;
        height: 30px !important;
        border-radius: 50% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin-right: 12px !important;
        font-size: 13px !important;
        flex-shrink: 0 !important;
        transition: all 0.3s ease !important;
    }

    .loading-step.pending .loading-step-icon {
        background: #e9ecef !important;
        color: #adb5bd !important;
    }

    .loading-step.active .loading-step-icon {
        background: #0078d4 !important;
        color: white !important;
        animation: pulse-icon 1.5s ease infinite !important;
    }

    .loading-step.done .loading-step-icon {
        background: #28a745 !important;
        color: white !important;
    }

    @keyframes pulse-icon {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.08);
        }
    }

    .loading-step-text {
        font-size: 13px !important;
        font-weight: 500 !important;
        color: #212529 !important;
    }

    .loading-step-text small {
        display: block !important;
        font-weight: 400 !important;
        color: #6c757d !important;
        font-size: 11px !important;
    }

    .loading-step.active .loading-step-text small {
        color: #0078d4 !important;
    }

    .loading-step.done .loading-step-text small {
        color: #28a745 !important;
    }

    .loading-progress-bar {
        width: 100% !important;
        height: 5px !important;
        background: #e9ecef !important;
        border-radius: 3px !important;
        margin-top: 16px !important;
        overflow: hidden !important;
    }

    .loading-progress-fill {
        height: 100% !important;
        background: linear-gradient(90deg, #0078d4, #28a745) !important;
        border-radius: 3px !important;
        width: 0% !important;
        transition: width 0.5s ease !important;
    }

    .loading-percentage {
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #0078d4 !important;
        margin-top: 8px !important;
    }

    /* ===== WAIT MESSAGE ===== */
    .loading-wait-message {
        display: none !important;
        margin-top: 12px !important;
        padding: 12px 16px !important;
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
        border-radius: 10px !important;
        border-left: 4px solid #0078d4 !important;
        text-align: left !important;
    }

    .loading-wait-message .wait-content {
        display: flex !important;
        align-items: flex-start !important;
        gap: 10px !important;
    }

    .loading-wait-message .loading-spinner-small {
        display: none !important;
        width: 18px !important;
        height: 18px !important;
        border: 3px solid #e9ecef !important;
        border-top: 3px solid #0078d4 !important;
        border-radius: 50% !important;
        animation: spin 0.8s linear infinite !important;
        margin-right: 10px !important;
        flex-shrink: 0 !important;
    }

    .loading-wait-text {
        font-size: 14px !important;
        font-weight: 600 !important;
        color: #0d47a1 !important;
    }

    .loading-wait-subtext {
        font-size: 12px !important;
        color: #1565c0 !important;
        font-weight: 400 !important;
    }

    .loading-wait-icon {
        font-size: 18px !important;
        color: #0078d4 !important;
        animation: bounce 1s ease infinite !important;
        flex-shrink: 0 !important;
        margin-top: 2px !important;
    }

    @keyframes bounce {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-5px);
        }
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .fluent-body {
            padding: 16px !important;
        }

        .fluent-header {
            flex-direction: column !important;
            text-align: center !important;
            gap: 10px !important;
        }

        .fluent-header .header-title {
            font-size: 18px !important;
        }

        .fluent-header .header-badge {
            font-size: 11px !important;
        }

        .section-title {
            font-size: 18px !important;
            padding: 12px 16px !important;
            flex-wrap: wrap !important;
            gap: 10px !important;
            min-height: auto !important;
        }

        .section-title i:first-child {
            font-size: 20px !important;
            width: 36px !important;
            height: 36px !important;
        }

        .section-title .section-title-text {
            font-size: 17px !important;
            flex: 1 1 auto !important;
        }

        .section-title .section-badge {
            font-size: 11px !important;
            padding: 4px 12px !important;
            margin-left: 0 !important;
        }

        .cuotas-table {
            font-size: 12px !important;
        }

        .cuotas-table thead th {
            font-size: 10px !important;
            padding: 10px 6px !important;
        }

        .cuotas-table tbody td {
            padding: 8px 6px !important;
        }

        .cuota-number {
            width: 30px !important;
            height: 30px !important;
            font-size: 12px !important;
        }

        .total-amount {
            font-size: 18px !important;
        }

        .reminder-banner {
            flex-direction: column !important;
            text-align: center !important;
            padding: 16px !important;
        }

        .reminder-banner .reminder-date {
            white-space: normal !important;
        }

        .btn-fluent-primary,
        .btn-fluent-secondary {
            width: 100% !important;
            margin: 5px 0 !important;
        }

        .loading-box {
            padding: 30px 20px !important;
        }
    }

    /* ===== SCROLLBAR ===== */
    .table-responsive {
        background-color: #bfe4e9;
    }

    .table-responsive::-webkit-scrollbar {
        height: 8px !important;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f3f5 !important;
        border-radius: 4px !important;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #d4d9e2 !important;
        border-radius: 4px !important;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #b8c0cc !important;
    }

    /* ===== VALIDATION FEEDBACK ===== */
    .is-invalid {
        border-color: #d13438 !important;
        box-shadow: 0 0 0 3px rgba(209, 52, 56, 0.15) !important;
        background: #fff5f5 !important;
    }

    .has-error .help-block {
        color: #d13438 !important;
        font-weight: 600 !important;
    }

    /* Fix for Select2 validation */
    .is-invalid+.select2-container--krajee .select2-selection--single {
        border-color: #d13438 !important;
        box-shadow: 0 0 0 3px rgba(209, 52, 56, 0.15) !important;
    }

    /* Fix for file input validation */
    .is-invalid+.file-input .file-preview {
        border-color: #d13438 !important;
        box-shadow: 0 0 0 3px rgba(209, 52, 56, 0.15) !important;
    }

    /* Styles the outer container of the file input field */
    .file-upload-card {
        background-color: #e4eef7 !important;
        /* Soft light gray-blue background */
        padding: 20px !important;
        border-radius: 8px !important;
        border: 1px solid #e7bbb2 !important;
        margin-bottom: 20px !important;
    }

    /* Optional: Ensures the label inside this styled card stands out */
    .file-upload-card label.control-label {
        font-weight: 600 !important;
        color: #323130 !important;
        margin-bottom: 10px !important;
        display: inline-block !important;
    }

    /* Target the selection hover inside your custom panel */
    .custom-dropdown-panel .select2-results__option--highlighted[aria-selected] {
        background-color: #e0f2fe !important;
        /* Elegant Soft Blue hover to match your selected cuotas */
        color: #0369a1 !important;
        /* Clean dark blue text for high contrast */
    }

    /* Target the option that is active/already clicked inside your custom panel */
    .custom-dropdown-panel .select2-results__option[aria-selected=true] {
        background-color: #bae6fd !important;
        /* Slightly deeper blue for selected status */
        color: #0c4a6e !important;
    }

    /* Optional: Smooth out drop-down item padding for modern spacing */
    .custom-dropdown-panel .select2-results__option {
        padding: 8px 12px !important;
        font-size: 13px !important;
    }

    /* ===== UNIFIED HEIGHT FOR ALL FIELDS (40px) ===== */
    .fluent-field .form-control,
    .select2-container--krajee .select2-selection--single {
        height: 40px !important;
        padding: 8px 14px !important;
        display: flex !important;
        align-items: center !important;
    }

    .select2-container--krajee .select2-selection--single .select2-selection__rendered {
        line-height: 1.5 !important;
        padding: 0 !important;
    }

    .select2-container--krajee .select2-selection--single .select2-selection__arrow {
        height: 38px !important;
    }

    /* Date input specific fix */
    input[type="date"].form-control {
        height: 40px !important;
        padding: 8px 14px !important;
    }
</style>

<div class="pagos-form">
    <!-- ===== LOADING OVERLAY ===== -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-box">
            <div class="loading-spinner">
                <i class="fas fa-credit-card"></i>
            </div>
            <div class="loading-title"><i class="fas fa-credit-card mr-2"></i> Procesando Pago</div>
            <div class="loading-subtitle"><i class="fas fa-info-circle mr-2"></i> Por favor espere, estamos registrando su pago...</div>

            <div class="loading-steps">
                <div class="loading-step active" id="step1">
                    <div class="loading-step-icon"><i class="fas fa-spinner fa-spin"></i></div>
                    <div class="loading-step-text">
                        Guardando pago
                        <small>Registrando la transacción en el sistema</small>
                    </div>
                </div>
                <div class="loading-step pending" id="step2">
                    <div class="loading-step-icon"><i class="fas fa-hourglass-start"></i></div>
                    <div class="loading-step-text">
                        Generando recibo(s)
                        <small>Creando los recibos de afiliación</small>
                    </div>
                </div>
                <div class="loading-step pending" id="step3">
                    <div class="loading-step-icon"><i class="fas fa-hourglass-start"></i></div>
                    <div class="loading-step-text">
                        Enviando notificación por email
                        <small>Enviando los recibos al correo del afiliado</small>
                    </div>
                </div>
            </div>

            <div class="loading-progress-bar">
                <div class="loading-progress-fill" id="progressFill"></div>
            </div>
            <div class="loading-percentage" id="progressText">0%</div>

            <div class="loading-wait-message" id="loadingWaitMessage">
                <div class="wait-content">
                    <div class="loading-spinner-small" id="loadingSpinnerSmall"></div>
                    <div class="wait-text">
                        <div class="loading-wait-text"><i class="fas fa-hourglass-half mr-2"></i> Espere un momento, estamos redirigiendo.</div>
                        <div class="loading-wait-subtext"><i class="fas fa-exclamation-circle mr-2"></i> Por favor no cierre esta ventana mientras completamos el proceso...</div>
                    </div>
                    <div class="loading-wait-icon">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
        // DISABLE CLIENT VALIDATION - we handle it manually
        'enableClientValidation' => false,
        'enableAjaxValidation' => false,
        'validateOnSubmit' => false,
        'validateOnBlur' => false,
        'validateOnChange' => false,
        'validateOnType' => false,
    ]); ?>

    <?php $disabled = isset($isEditable) && !$isEditable; ?>

    <!-- ===== FLUENT CONTAINER ===== -->
    <div class="fluent-container">
        <!-- ===== HEADER ===== -->
        <div class="fluent-header">
            <div class="header-title">
                <i class="fas fa-credit-card"></i> Registro de Pago
            </div>
            <div class="header-badge">
                <i class="fas fa-clock mr-1"></i> Nuevo Pago
            </div>
        </div>

        <!-- ===== BODY ===== -->
        <div class="fluent-body">



            <!-- ===== SECTION 2: CUOTAS PENDIENTES ===== -->
            <div class="section-title">
                <i class="fas fa-calendar-alt"></i> Cuotas Pendientes
                <span class="cuotas-selected-counter"></span>
                <span class="section-badge"><i class="fas fa-list"></i> <?= count($cuotas) ?> cuotas</span>
            </div>

            <?php
            // Initialize variables before the loop
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
            ?>

            <?php if (!empty($cuotas)): ?>
                <div class="section-card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover cuotas-table mb-0">
                                <thead class="thead-dark">
                                    <tr>
                                        <th class="text-center" style="width: 50px;">#</th>
                                        <th class="text-center" style="width: 120px;">Mes / Año</th>
                                        <th class="text-center" style="width: 180px;">Período de Cobertura</th>
                                        <th class="text-center" style="width: 110px;">Vence</th>
                                        <th class="text-center" style="width: 160px;">Estado</th>
                                        <th class="text-center" style="width: 100px;">Monto</th>
                                        <th class="text-center" style="width: 60px;">
                                            <i class="fas fa-check-circle" style="color: #ffffff;"></i>
                                        </th>
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

                                        if ($status == 'pagada') {
                                            $statusBadge = '<span class="status-badge status-paid"><i class="fas fa-check-circle"></i> PAGADA</span>';
                                        } elseif ($status == 'en_gracias') {
                                            $dueDateObj = new DateTime($dueDateStr);
                                            $todayObj = new DateTime($today);
                                            $interval = $todayObj->diff($dueDateObj);
                                            $daysOverdue = $interval->days;
                                            $daysRemaining = 7 - $daysOverdue;
                                            $statusBadge = $daysRemaining <= 2
                                                ? '<span class="status-badge status-urgent"><i class="fas fa-exclamation-circle"></i> URGENTE (' . $daysRemaining . 'd)</span>'
                                                : '<span class="status-badge status-grace"><i class="fas fa-hourglass-half"></i> GRACIA (' . $daysRemaining . 'd)</span>';
                                        } elseif ($status == 'vencida') {
                                            $statusBadge = '<span class="status-badge status-overdue"><i class="fas fa-exclamation-circle"></i> VENCIDA</span>';
                                        } else {
                                            $dueDateObj = new DateTime($dueDateStr);
                                            $todayObj = new DateTime($today);
                                            $daysUntilDue = $todayObj->diff($dueDateObj)->days;
                                            $statusBadge = ($dueDateStr >= $today && $daysUntilDue <= 3)
                                                ? '<span class="status-badge status-approaching"><i class="fas fa-clock"></i> PRÓXIMO (' . $daysUntilDue . 'd)</span>'
                                                : '<span class="status-badge status-pending"><i class="fas fa-clock"></i> PENDIENTE</span>';
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
                                                    <span class="cuota-coverage"><i class="fas fa-calendar-alt mr-1"></i> <?= $start->format('d/m/Y') ?> <i class="fas fa-arrow-right mx-1" style="font-size: 10px;"></i> <?= $end->format('d/m/Y') ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="cuota-due-date <?= $dueDateClass ?>"><?= $formattedDueDate ?></span>
                                            </td>
                                            <td class="text-center"><?= $statusBadge ?></td>
                                            <td class="text-center">
                                                <span class="cuota-amount">$<?= number_format($monto, 2) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <div class="custom-control custom-checkbox" style="margin: 0;">
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

                                    <!-- Total Row -->
                                    <tr class="total-row">
                                        <td colspan="5" class="text-right">
                                            <span class="total-label">
                                                <i class="fas fa-chart-line mr-2"></i>TOTAL SELECCIONADO
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="total-amount" id="selected-total">
                                                <i class="fas fa-dollar-sign mr-1"></i>0.00
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <i class="fas fa-arrow-right text-success" style="font-size: 18px;"></i>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-success d-flex align-items-center" style="border-radius: 10px; border-left: 4px solid #107c10;">
                    <i class="fas fa-check-circle fa-2x mr-4" style="color: #107c10;"></i>
                    <div>
                        <strong style="font-size: 16px;">✅ No hay cuotas pendientes</strong>
                        <?php if ($user_id): ?>
                            <div class="text-muted" style="font-size: 13px;">
                                <i class="fas fa-user mr-2"></i> Usuario ID: <?= $user_id ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ===== SECTION 3: INFORMACIÓN DEL PAGO ===== -->
            <div class="section-title" style="margin-top: 0;">
                <i class="fas fa-info-circle"></i> 💳 Información del Pago
                <span class="section-badge"><i class="fas fa-edit"></i> Completar datos</span>
            </div>

            <!-- ===== REMINDER BANNER (inside Información del Pago section) - EYE-CATCHING ===== -->
            <?php
            // Only show banner if the FIRST cuota (numero_cuota = 1) is still pending (not paid)
            $showReminderBanner = false;
            if (isset($selectedContrato) && $selectedContrato && $selectedContrato->fecha_ini && !empty($cuotas)) {
                // Find the first cuota (numero_cuota = 1)
                $firstCuota = null;
                foreach ($cuotas as $cuota) {
                    if ($cuota->numero_cuota == 1) {
                        $firstCuota = $cuota;
                        break;
                    }
                }

                // Check if first cuota exists and is NOT paid
                if ($firstCuota && $firstCuota->estatus !== 'pagada') {
                    $showReminderBanner = true;
                }
            }
            ?>
            <?php if ($showReminderBanner): ?>
                <div class="reminder-banner">
                    <div class="reminder-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="reminder-text">
                        <strong>⚠️ ¡ATENCIÓN!</strong> La fecha de pago debe ser igual a la fecha de inicio del contrato
                    </div>
                    <div class="reminder-date">
                        <i class="fas fa-calendar-check"></i>
                        <?= Yii::$app->formatter->asDate($selectedContrato->fecha_ini, 'php:l, d \d\e F \d\e Y') ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ===== Payment Fields Row 1: Método de Pago, Fecha de Pago, Número de Referencia ===== -->
            <div class="row">
                <div class="col-md-4">
                    <div class="fluent-field">
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
                                'dropdownCssClass' => 'custom-dropdown-panel',
                                'templateResult' => new JsExpression('
            function(data) {
                if (!data.id) return data.text;
                var span = $("<span></span>");
                
                if (data.id === "Efectivo - Dólar ($)") {
                    span.append("<i class=\'fas fa-dollar-sign text-success mr-2\'></i>");
                } else if (data.id === "Pago Móvil") {
                    // Added Zelle verification rule with signature purple tone
                    span.append("<i class=\'fas fa-mobile-alt mr-2\' style=\'color: #7414CA;\'></i>");
                } else if (data.id === "Punto de Venta") {
                    // Added Punto de Venta with a modern teal look
                    span.append("<i class=\'fas fa-wifi mr-2\' style=\'color: #0d9488;\'></i>");
                } else if (data.id === "Transferencia Bancaria") {
                    // Added Punto de Venta with a modern teal look
                    span.append("<i class=\'fas fa-university mr-2\' style=\'color: #0d9488;\'></i>");
                } else if (data.id === "Zelle") {
                    // Added Zelle verification rule with signature purple tone
                    span.append("<i class=\'fas fa-hand-holding-usd mr-2\' style=\'color: #7414CA;\'></i>");
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
                } else if (data.id === "Pago Móvil") {
                    // Added Zelle verification rule with signature purple tone
                    span.append("<i class=\'fas fa-mobile-alt mr-2\' style=\'color: #7414CA;\'></i>");
                } else if (data.id === "Punto de Venta") {
                    // Added Punto de Venta with a modern teal look
                    span.append("<i class=\'fas fa-mobile-alt mr-2\' style=\'color: #0d9488;\'></i>");
                } else if (data.id === "Punto de Venta") {
                    // Added Punto de Venta with a modern teal look
                    span.append("<i class=\'fas fa-wifi mr-2\' style=\'color: #0d9488;\'></i>");
                } else if (data.id === "Transferencia Bancaria") {
                    // Added Punto de Venta with a modern teal look
                    span.append("<i class=\'fas fa-university mr-2\' style=\'color: #0d9488;\'></i>");
                } else if (data.id === "Zelle") {
                    // Added Zelle verification rule with signature purple tone
                    span.append("<i class=\'fas fa-hand-holding-usd mr-2\' style=\'color: #7414CA;\'></i>");
                }
                
                span.append(document.createTextNode(data.text));
                return span;
            }
        '),
                                'escapeMarkup' => new JsExpression('function(markup) { return markup; }'),
                            ],
                        ])->label('Método de Pago' . '<span class="required-field" style="color:#d13438;"></span>') ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="fluent-field">
                        <?= $form->field($model, 'fecha_pago')->textInput([
                            'class' => 'form-control',
                            'type' => 'date',
                            'placeholder' => 'Seleccione la fecha del pago',
                            'disabled' => $disabled,
                            'id' => 'fecha-pago',
                        ])->label('Fecha de Pago' . '<span class="required-field" style="color:#d13438;"></span>') ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="fluent-field">
                        <?= $form->field($model, 'numero_referencia_pago')->textInput([
                            'class' => 'form-control',
                            'type' => 'text',
                            'placeholder' => 'Ingrese el número de referencia del pago',
                            'disabled' => $disabled,
                            'id' => 'pagos-numero_referencia_pago',
                        ])->label('Número de Referencia' . '<span class="required-field" style="color:#d13438;"> *</span>') ?>
                    </div>
                </div>
            </div>

            <!-- ===== Payment Fields Row 2: Monto a Pagar, Tasa, Monto en Bs ===== -->
            <div class="row">
                <div class="col-md-4">
                    <div class="fluent-field">
                        <?= $form->field($model, 'monto_pagado')->textInput([
                            'class' => 'form-control',
                            'placeholder' => 'Monto en USD',
                            'id' => 'pagos-monto_pagado',
                            'readonly' => true,
                        ])->label('Monto a Pagar (USD)' . '<span class="required-field" style="color:#d13438;"></span>') ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="fluent-field">
                        <?= $form->field($model, 'tasa')->textInput([
                            'class' => 'form-control',
                            'type' => 'number',
                            'step' => '0.0001',  // 4 decimal places
                            'min' => '0.0001',
                            'placeholder' => 'Ingrese la tasa de cambio',
                            'id' => 'pagos-tasa',
                            'value' => $model->tasa ? number_format($model->tasa, 4, '.', '') : '',
                        ])->label('Tasa de Cambio USD a Bs (BCV)' . '<span class="required-field" style="color:#d13438;"></span>') ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="fluent-field">
                        <?= $form->field($model, 'monto_usd')->textInput([
                            'class' => 'form-control',
                            'readonly' => true,
                            'placeholder' => 'Monto en Bs (calculado)',
                            'id' => 'pagos-monto_usd',
                        ])->label('Monto en Bs' . '<span class="required-field" style="color:#d13438;"></span>') ?>
                    </div>
                </div>
            </div>

            <!-- ===== Payment Fields Row 3: Comprobante de Pago ===== -->
            <div class="row">
                <div class="col-md-12">
                    <div class="fluent-field">
                        <?= $form->field($model, 'imagen_prueba_file', [
                            // Adds a custom class to the outer wrapper container
                            'options' => ['class' => 'form-group file-upload-card']
                        ])->widget(FileInput::classname(), [
                            'options' => ['accept' => 'image/*', 'disabled' => $disabled, 'id' => 'pagos-imagen_prueba_file'],
                            'pluginOptions' => [
                                'theme' => 'fa5',
                                'browseClass' => 'btn btn-primary mr-3',
                                'removeClass' => 'btn btn-danger ml-3',
                                'uploadClass' => 'btn btn-info',
                                'browseIcon' => '<i class="fas fa-folder-open"></i> ',
                                'removeIcon' => '<i class="fas fa-trash"></i> ',
                                'browseLabel' => ' Buscar',
                                'removeLabel' => ' Quitar',
                                'showUpload' => false,
                                'showCancel' => false,
                                'previewFileType' => 'image',
                                'maxFileSize' => 2800,
                                'msgSizeTooLarge' => 'El archivo "{name}" ({size} KB) excede el tamaño máximo permitido de {maxSize} KB.',
                                'layoutTemplates' => [
                                    'main1' => '{preview}{browse}{remove}',
                                    'main2' => '{preview}{browse}{remove}',
                                    'actions' => '<div class="file-actions" style="display: flex; gap: 12px;"></div>',
                                ],
                                'previewSettings' => [
                                    'image' => [
                                        'width' => '100%',
                                        'height' => 'auto',
                                        'max-width' => '250px'
                                    ]
                                ],
                            ],
                        ])->label('Comprobante de Pago (JPG, PNG)' . '<span class="required-field" style="color:#d13438;">*</span>') ?>
                    </div>
                </div>
            </div>

            <!-- ===== SECTION 4: ACTION BUTTONS ===== -->
            <div class="form-group mt-4 text-center" style="border-top: 1px solid #edebe9; padding-top: 24px;">
                <?php if (!$disabled): ?>
                    <?php if (!empty($cuotas)): ?>
                        <?= Html::submitButton('<i class="fas fa-save mr-2"></i> Guardar Pago', [
                            'class' => 'btn-fluent-primary',
                            'id' => 'submit-btn',
                        ]) ?>
                    <?php else: ?>
                        <button class="btn-fluent-primary" disabled style="opacity: 0.6; cursor: not-allowed;">
                            <i class="fas fa-save mr-2"></i> No hay cuotas pendientes
                        </button>
                    <?php endif; ?>
                    <?= Html::a('<i class="fas fa-undo mr-2"></i> Volver', ['contratos/index', 'user_id' => $model->user_id], [
                        'class' => 'btn-fluent-secondary ml-2'
                    ]) ?>
                <?php endif; ?>
            </div>

        </div><!-- /fluent-body -->
    </div><!-- /fluent-container -->

    <?php ActiveForm::end(); ?>
</div>