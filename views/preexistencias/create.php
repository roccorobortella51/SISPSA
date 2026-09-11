<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Preexistencias */
/* @var $afiliado app\models\UserDatos|null */
/* @var $user_id int|null */

// ============================================================
// GUARDS: Ensure variables are defined
// ============================================================
$model = $model ?? null;
$afiliado = $afiliado ?? null;
$user_id = $user_id ?? null;

if (!$model) {
    echo '<div class="alert alert-danger">Error: No se pudo cargar la pre-existencia.</div>';
    return;
}

// Determine title
if ($afiliado && is_object($afiliado)) {
    $this->title = 'Registrar Pre-existencia para ' . Html::encode($afiliado->nombres . ' ' . $afiliado->apellidos);
} else {
    $this->title = 'Registrar Pre-existencia';
}

// Breadcrumbs
$this->params['breadcrumbs'][] = ['label' => 'Pre-existencias', 'url' => ['index', 'user_id' => $user_id]];

if ($afiliado && is_object($afiliado)) {
    $this->params['breadcrumbs'][] = [
        'label' => Html::encode($afiliado->nombres . ' ' . $afiliado->apellidos),
        'url' => ['/user-datos/view', 'id' => $afiliado->id]
    ];
}
$this->params['breadcrumbs'][] = 'Registrar';

// Register CSS
$this->registerCss("
/* ============================================ */
/* PRE-EXISTENCIA CREATE/UPDATE PAGE */
/* ============================================ */

.preexistencia-create-header,
.preexistencia-update-header {
    background: linear-gradient(135deg, #6c2b8a 0%, #8e44ad 100%);
    padding: 24px 30px;
    border-radius: 12px 12px 0 0;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.preexistencia-create-header h1,
.preexistencia-update-header h1 {
    color: white;
    margin: 0;
    font-size: 1.8rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.preexistencia-create-header h1 small,
.preexistencia-update-header h1 small {
    font-size: 0.6em;
    opacity: 0.85;
    font-weight: 400;
}

.preexistencia-create-header .header-actions,
.preexistencia-update-header .header-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.preexistencia-create-header .header-actions .btn,
.preexistencia-update-header .header-actions .btn {
    border-radius: 8px;
    padding: 10px 20px;
    font-weight: 600;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-back {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.btn-back:hover {
    background: rgba(255, 255, 255, 0.3);
    color: white;
    transform: translateY(-2px);
}

.btn-danger-outline {
    background: rgba(255, 82, 82, 0.2);
    color: white;
    border: 1px solid rgba(255, 82, 82, 0.3);
}

.btn-danger-outline:hover {
    background: rgba(255, 82, 82, 0.35);
    color: white;
    transform: translateY(-2px);
}

/* ===== FORM STYLES ===== */
.preexistencia-create-body,
.preexistencia-update-body {
    background: white;
    border-radius: 0 0 12px 12px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.preexistencia-create-body .form-group,
.preexistencia-update-body .form-group {
    margin-bottom: 20px;
}

.preexistencia-create-body .form-control,
.preexistencia-update-body .form-control {
    border-radius: 8px;
    border: 2px solid #e9ecef;
    padding: 10px 14px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.preexistencia-create-body .form-control:focus,
.preexistencia-update-body .form-control:focus {
    border-color: #6c2b8a;
    box-shadow: 0 0 0 3px rgba(108, 43, 138, 0.1);
}

.preexistencia-create-body .form-control-lg,
.preexistencia-update-body .form-control-lg {
    padding: 12px 16px;
    font-size: 16px;
}

.preexistencia-create-body .form-label,
.preexistencia-update-body .form-label {
    font-weight: 600;
    color: #1a1a2e;
    font-size: 14px;
}

.preexistencia-create-body .form-text,
.preexistencia-update-body .form-text {
    font-size: 12px;
    color: #6c757d;
}

.preexistencia-create-body .help-block,
.preexistencia-update-body .help-block {
    color: #dc3545;
    font-size: 13px;
    margin-top: 4px;
}

.preexistencia-create-body .has-error .form-control,
.preexistencia-update-body .has-error .form-control {
    border-color: #dc3545;
}

.preexistencia-create-body .has-error .form-control:focus,
.preexistencia-update-body .has-error .form-control:focus {
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
}

/* ===== AFFILIATE CARD ===== */
.affiliate-card {
    background: linear-gradient(135deg, #f4f0f8 0%, #e8e0f0 100%);
    border-radius: 12px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.affiliate-card .affiliate-icon {
    width: 48px;
    height: 48px;
    background: #6c2b8a;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    flex-shrink: 0;
}

.affiliate-card .affiliate-info {
    flex: 1;
}

.affiliate-card .affiliate-info .name {
    font-size: 16px;
    font-weight: 700;
    color: #1a1a2e;
}

.affiliate-card .affiliate-info .details {
    font-size: 13px;
    color: #6c757d;
}

.affiliate-card .affiliate-info .details i {
    margin-right: 4px;
}

/* ===== BUTTONS ===== */
.form-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 24px;
    padding-top: 24px;
    border-top: 2px solid #f0f0f0;
}

.form-actions .btn {
    border-radius: 8px;
    padding: 12px 28px;
    font-weight: 600;
    font-size: 15px;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.form-actions .btn:hover {
    transform: translateY(-2px);
}

.btn-save {
    background: linear-gradient(135deg, #6c2b8a 0%, #8e44ad 100%);
    color: white;
    border: none;
}

.btn-save:hover {
    box-shadow: 0 4px 16px rgba(108, 43, 138, 0.3);
    color: white;
}

.btn-cancel {
    background: #f8f9fa;
    color: #495057;
    border: 1px solid #dee2e6;
}

.btn-cancel:hover {
    background: #e9ecef;
}

.btn-delete-form {
    background: #dc3545;
    color: white;
    border: none;
}

.btn-delete-form:hover {
    background: #c82333;
    color: white;
    box-shadow: 0 4px 16px rgba(220, 53, 69, 0.3);
}

/* ===== STATUS BADGE ===== */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.activo {
    background: #e8f5e9;
    color: #2e7d32;
}

.status-badge.inactivo {
    background: #f5f5f5;
    color: #616161;
}

.status-badge.tratamiento {
    background: #e3f2fd;
    color: #1565c0;
}

.status-badge.remision {
    background: #fff3e0;
    color: #e65100;
}

/* ===== INFO BANNER ===== */
.info-banner {
    background: linear-gradient(135deg, #f4f0f8 0%, #e8e0f0 100%);
    border-radius: 8px;
    padding: 16px 20px;
    margin-bottom: 24px;
    border-left: 4px solid #6c2b8a;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.info-banner i {
    color: #6c2b8a;
    font-size: 20px;
    margin-top: 2px;
}

.info-banner .info-text {
    color: #4a1a6b;
    font-size: 14px;
    line-height: 1.6;
}

.info-banner .info-text strong {
    color: #2d0d4a;
}

/* ===== ESTATUS SELECT - BULLETPROOF FIX ===== */
#preexistencia-estatus {
    width: 100% !important;
    min-width: 280px !important;
    max-width: 100% !important;
    display: block !important;
    box-sizing: border-box !important;
    padding: 12px 16px !important;
    font-size: 16px !important;
    height: auto !important;
    min-height: 50px !important;
}

#preexistencia-estatus option {
    padding: 10px 16px !important;
    font-size: 15px !important;
    white-space: nowrap !important;
}

.field-preexistencia-estatus {
    width: 100% !important;
    min-width: 280px !important;
    max-width: 100% !important;
}

.form-control#preexistencia-estatus {
    width: 100% !important;
    min-width: 280px !important;
    max-width: 100% !important;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .preexistencia-create-header,
    .preexistencia-update-header {
        flex-direction: column;
        align-items: flex-start;
        padding: 20px;
    }
    
    .preexistencia-create-header h1,
    .preexistencia-update-header h1 {
        font-size: 1.4rem;
    }
    
    .preexistencia-create-header .header-actions,
    .preexistencia-update-header .header-actions {
        width: 100%;
    }
    
    .preexistencia-create-header .header-actions .btn,
    .preexistencia-update-header .header-actions .btn {
        flex: 1;
        justify-content: center;
        font-size: 13px;
        padding: 8px 14px;
    }
    
    .preexistencia-create-body,
    .preexistencia-update-body {
        padding: 16px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
        justify-content: center;
    }
    
    .affiliate-card {
        flex-direction: column;
        text-align: center;
    }
    
    .info-banner {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .preexistencia-create-header h1,
    .preexistencia-update-header h1 {
        font-size: 1.2rem;
    }
}
");
?>
<div class="preexistencia-create-container">

    <!-- Header -->
    <div class="preexistencia-create-header">
        <h1>
            <i class="fas fa-plus-circle"></i>
            <?= Html::encode($this->title) ?>
            <?php if (!$afiliado): ?>
                <small>
                    <i class="fas fa-id-card"></i>
                    ID: <?= $user_id ? '#' . $user_id : 'Nuevo' ?>
                </small>
            <?php endif; ?>
            <!-- Status Badge - Hidden on create since it's new -->
            <span id="status-badge-header" class="status-badge" style="display: none;">
                <i class="fas fa-circle"></i>
                <span id="status-badge-text"></span>
            </span>
        </h1>
        <div class="header-actions">
            <?php if ($user_id): ?>
                <?= Html::a(
                    '<i class="fas fa-arrow-left"></i> Volver',
                    ['index', 'user_id' => $user_id],
                    ['class' => 'btn btn-back']
                ) ?>
            <?php else: ?>
                <?= Html::a(
                    '<i class="fas fa-arrow-left"></i> Volver',
                    ['index'],
                    ['class' => 'btn btn-back']
                ) ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Body -->
    <div class="preexistencia-create-body">

        <!-- Info Banner -->
        <div class="info-banner">
            <i class="fas fa-info-circle"></i>
            <div class="info-text">
                <strong>¿Qué es una pre-existencia?</strong>
                Una pre-existencia es cualquier condición médica, enfermedad o lesión que el afiliado
                ya presentaba antes de la contratación del plan de salud. Registrar esta información permite
                un mejor seguimiento médico y una gestión más precisa de la cobertura.
            </div>
        </div>

        <!-- Affiliate Info -->
        <?php if ($afiliado && is_object($afiliado)): ?>
            <div class="affiliate-card">
                <div class="affiliate-icon">
                    <i class="fas fa-user"></i>
                </div>
                <div class="affiliate-info">
                    <div class="name">
                        <?= Html::encode($afiliado->nombres . ' ' . $afiliado->apellidos) ?>
                    </div>
                    <div class="details">
                        <i class="fas fa-id-card"></i> <?= Html::encode($afiliado->tipo_cedula . '-' . $afiliado->cedula) ?>
                        &nbsp;|&nbsp;
                        <i class="fas fa-phone"></i> <?= Html::encode($afiliado->telefono ?? 'N/A') ?>
                        &nbsp;|&nbsp;
                        <i class="fas fa-envelope"></i> <?= Html::encode($afiliado->email ?? 'N/A') ?>
                    </div>
                </div>
                <div>
                    <?= Html::a(
                        '<i class="fas fa-external-link-alt"></i> Ver Perfil',
                        ['/user-datos/view', 'id' => $afiliado->id],
                        [
                            'class' => 'btn btn-sm',
                            'target' => '_blank',
                            'style' => 'border-color: #6c2b8a; color: #6c2b8a; border-radius: 8px; padding: 6px 14px; font-weight: 600; border: 2px solid #6c2b8a; background: transparent;'
                        ]
                    ) ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <?php $form = ActiveForm::begin([
            'id' => 'preexistencia-form',
            'options' => [
                'class' => 'preexistencia-form',
                'autocomplete' => 'off',
            ],
        ]); ?>

        <div class="row">
            <!-- Nombre -->
            <div class="col-md-6">
                <?= $form->field($model, 'nombre')->textInput([
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'Ej: Diabetes Tipo 2, Hipertensión Arterial, Asma Bronquial...',
                    'maxlength' => true,
                    'autocomplete' => 'off',
                ])->label('Nombre de la Pre-existencia <span class="text-danger">*</span>') ?>
                <div class="form-text text-muted">
                    <i class="fas fa-notes-medical me-1"></i> Ingrese el nombre clínico completo de la condición médica.
                </div>
            </div>

            <!-- Fecha de Diagnóstico -->
            <div class="col-md-6">
                <?= $form->field($model, 'fecha_diagnostico')->textInput([
                    'type' => 'date',
                    'class' => 'form-control form-control-lg',
                    'autocomplete' => 'off',
                ])->label('Fecha de Diagnóstico') ?>
                <div class="form-text text-muted">
                    <i class="fas fa-calendar-alt me-1"></i> Fecha en que fue diagnosticada esta condición.
                </div>
            </div>

            <!-- Descripción -->
            <div class="col-md-12">
                <?= $form->field($model, 'descripcion')->textarea([
                    'rows' => 4,
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'Describa los detalles de la pre-existencia (síntomas, severidad, evolución, tratamientos previos, etc.)...',
                ])->label('Descripción de la Pre-existencia') ?>
                <div class="form-text text-muted">
                    <i class="fas fa-align-left me-1"></i> Proporcione información detallada sobre la condición médica.
                </div>
            </div>

            <!-- Médico Tratante -->
            <div class="col-md-6">
                <?= $form->field($model, 'medico_tratante')->textInput([
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'Nombre del médico que diagnosticó o trata la condición',
                    'maxlength' => true,
                    'autocomplete' => 'off',
                ])->label('Médico Tratante') ?>
                <div class="form-text text-muted">
                    <i class="fas fa-user-md me-1"></i> Médico responsable del diagnóstico o tratamiento de esta condición.
                </div>
            </div>

            <!-- ===== ESTATUS - BULLETPROOF ===== -->
            <div class="col-md-6">
                <?= $form->field($model, 'estatus', [
                    'options' => ['class' => 'form-group field-preexistencia-estatus', 'style' => 'width: 100%;']
                ])->dropDownList(
                    \app\models\Preexistencias::getStatusOptions(),
                    [
                        'class' => 'form-control form-control-lg',
                        'prompt' => 'Seleccione un estatus...',
                        'onchange' => 'updateStatusBadge(this.value)',
                        'id' => 'preexistencia-estatus',
                        'style' => 'width: 100% !important; min-width: 280px !important; max-width: 100% !important; padding: 12px 16px !important; font-size: 16px !important;'
                    ]
                )->label('Estatus') ?>
                <div class="form-text text-muted">
                    <i class="fas fa-info-circle me-1"></i> Estado actual de la condición médica.
                </div>
            </div>

            <!-- Medicamentos -->
            <div class="col-md-12">
                <?= $form->field($model, 'medicamentos')->textarea([
                    'rows' => 3,
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'Lista de medicamentos recetados para esta condición...',
                ])->label('Medicamentos') ?>
                <div class="form-text text-muted">
                    <i class="fas fa-pills me-1"></i> Medicamentos que el paciente está tomando para esta condición.
                </div>
            </div>

            <!-- Observaciones -->
            <div class="col-md-12">
                <?= $form->field($model, 'observaciones')->textarea([
                    'rows' => 3,
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'Observaciones adicionales sobre la pre-existencia...',
                ])->label('Observaciones') ?>
                <div class="form-text text-muted">
                    <i class="fas fa-comment me-1"></i> Cualquier otra información relevante sobre la condición médica.
                </div>
            </div>
        </div>

        <!-- Confidentiality Notice -->
        <div class="alert alert-warning mt-4" style="border-left: 4px solid #856404; background: #fff8e7; border-radius: 8px; padding: 16px 20px;">
            <div class="d-flex align-items-start">
                <div class="me-3">
                    <i class="fas fa-shield-alt fa-2x" style="color: #856404;"></i>
                </div>
                <div>
                    <strong style="color: #856404;">Aviso de Confidencialidad</strong>
                    <p class="mb-0" style="color: #856404; font-size: 0.95rem;">
                        La información de pre-existencias es de carácter <strong>médico y confidencial</strong>.
                        Será utilizada exclusivamente para fines de seguimiento clínico, gestión de cobertura y
                        atención médica del afiliado, conforme a las leyes de protección de datos aplicables.
                    </p>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <?= Html::submitButton(
                '<i class="fas fa-save"></i> Guardar Pre-existencia',
                ['class' => 'btn btn-save']
            ) ?>
            <?php if ($user_id): ?>
                <?= Html::a(
                    '<i class="fas fa-times"></i> Cancelar',
                    ['index', 'user_id' => $user_id],
                    ['class' => 'btn btn-cancel']
                ) ?>
            <?php else: ?>
                <?= Html::a(
                    '<i class="fas fa-times"></i> Cancelar',
                    ['index'],
                    ['class' => 'btn btn-cancel']
                ) ?>
            <?php endif; ?>
            <?php if ($model->isNewRecord): ?>
                <?= Html::a(
                    '<i class="fas fa-eraser"></i> Limpiar',
                    ['create', 'user_id' => $user_id],
                    ['class' => 'btn btn-outline-dark', 'style' => 'border-color: #6c757d; color: #495057;']
                ) ?>
            <?php endif; ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>

<?php
// Additional JS for status badge preview and form interaction
$this->registerJs("
    // Status badge mapping
    var statusMap = {
        'Activo': { class: 'activo', icon: 'fa-check-circle', label: 'Activo' },
        'Inactivo': { class: 'inactivo', icon: 'fa-ban', label: 'Inactivo' },
        'En Remisión': { class: 'remision', icon: 'fa-clock', label: 'En Remisión' },
        'En Tratamiento': { class: 'tratamiento', icon: 'fa-heartbeat', label: 'En Tratamiento' }
    };
    
    function updateStatusBadge(value) {
        var badge = jQuery('#status-badge-header');
        var text = jQuery('#status-badge-text');
        
        if (value && statusMap[value]) {
            var status = statusMap[value];
            badge.show();
            badge.removeClass('activo inactivo remision tratamiento');
            badge.addClass(status.class);
            text.html('<i class=\"fas ' + status.icon + '\"></i> ' + status.label);
        } else {
            badge.hide();
        }
    }
    
    // Initialize status badge on page load
    jQuery(document).ready(function() {
        var initialStatus = jQuery('#preexistencia-estatus').val();
        if (initialStatus) {
            updateStatusBadge(initialStatus);
        }
    });
    
    // Enable tooltips
    jQuery('[data-toggle=\"tooltip\"]').tooltip();
    
    // Confirm before leaving if form has changes
    var formChanged = false;
    jQuery('#preexistencia-form .form-control').on('change', function() {
        formChanged = true;
    });
    
    jQuery('#preexistencia-form textarea').on('input', function() {
        formChanged = true;
    });
    
    jQuery('a:not(.btn-save)').on('click', function(e) {
        if (formChanged) {
            if (!confirm('¿Está seguro de salir? Los cambios no guardados se perderán.')) {
                e.preventDefault();
                return false;
            }
        }
        return true;
    });
");
?>