<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Preexistencias */
/* @var $afiliado app\models\UserDatos|null */

// ============================================================
// GUARDS: Ensure variables are defined
// ============================================================
$model = $model ?? null;
$afiliado = $afiliado ?? null;

if (!$model) {
    echo '<div class="alert alert-danger">Error: No se pudo cargar la pre-existencia.</div>';
    return;
}

$this->title = 'Actualizar Pre-existencia: ' . $model->nombre;

$this->params['breadcrumbs'][] = ['label' => 'Pre-existencias', 'url' => ['index', 'user_id' => $model->user_id]];
if ($afiliado && is_object($afiliado)) {
    $this->params['breadcrumbs'][] = [
        'label' => $afiliado->nombres . ' ' . $afiliado->apellidos,
        'url' => ['/user-datos/view', 'id' => $afiliado->id]
    ];
}
$this->params['breadcrumbs'][] = ['label' => $model->nombre, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Actualizar';

// Register CSS
$this->registerCss("
/* ============================================ */
/* PRE-EXISTENCIA UPDATE PAGE - CLEAN */
/* ============================================ */

.preexistencia-update-container {
    max-width: 100%;
}

/* ===== HEADER ===== */
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

.preexistencia-update-header h1 {
    color: white;
    margin: 0;
    font-size: 1.6rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.preexistencia-update-header h1 .badge-id {
    background: rgba(255, 255, 255, 0.15) !important;
    color: white !important;
    font-size: 0.75rem;
    padding: 4px 12px;
    border-radius: 20px;
    font-weight: 500;
}

/* ===== STATUS BADGE ===== */
.status-badge-large {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 16px;
    border-radius: 30px;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-badge-large i {
    font-size: 14px;
}

.status-badge-large.activo {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.status-badge-large.inactivo {
    background: rgba(255, 255, 255, 0.15);
    color: rgba(255, 255, 255, 0.8);
}

.status-badge-large.tratamiento {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.status-badge-large.remision {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

/* ===== HEADER ACTIONS ===== */
.preexistencia-update-header .header-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}

.preexistencia-update-header .header-actions .btn {
    border-radius: 8px;
    padding: 8px 18px;
    font-weight: 600;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
}

.btn-back {
    background: rgba(255, 255, 255, 0.15) !important;
    color: white !important;
    border: 1px solid rgba(255, 255, 255, 0.25) !important;
}

.btn-back:hover {
    background: rgba(255, 255, 255, 0.3) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-delete-header {
    background: rgba(220, 53, 69, 0.2) !important;
    color: white !important;
    border: 1px solid rgba(220, 53, 69, 0.3) !important;
}

.btn-delete-header:hover {
    background: rgba(220, 53, 69, 0.35) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);
}

/* ===== BODY ===== */
.preexistencia-update-body {
    background: white;
    border-radius: 0 0 12px 12px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

/* ============================================================ */
/* AFFILIATE CARD - IMPROVED VERSION */
/* ============================================================ */

.affiliate-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #e8e0f0;
    overflow: hidden;
    margin-bottom: 28px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.affiliate-card .affiliate-card-header {
    background: linear-gradient(135deg, #6c2b8a 0%, #8e44ad 100%);
    padding: 14px 24px;
    color: white;
    display: flex;
    align-items: center;
    gap: 12px;
}

.affiliate-card .affiliate-card-header i {
    font-size: 18px;
}

.affiliate-card .affiliate-card-header h3 {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
    color: white;
}

.affiliate-card .affiliate-card-body {
    padding: 20px 24px;
    display: flex;
    align-items: center;
    gap: 24px;
    flex-wrap: wrap;
}

.affiliate-card .affiliate-avatar {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f4f0f8 0%, #e8e0f0 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    color: #6c2b8a;
    flex-shrink: 0;
    border: 3px solid #6c2b8a;
}

.affiliate-card .affiliate-info {
    flex: 1;
    min-width: 200px;
}

.affiliate-card .affiliate-info .affiliate-name {
    font-size: 18px;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 6px;
}

.affiliate-card .affiliate-info .affiliate-details {
    display: flex;
    flex-wrap: wrap;
    gap: 16px 24px;
}

.affiliate-card .affiliate-info .affiliate-details .detail-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    color: #495057;
}

.affiliate-card .affiliate-info .affiliate-details .detail-item i {
    color: #6c2b8a;
    font-size: 14px;
    width: 16px;
}

.affiliate-card .affiliate-actions {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
}

.affiliate-card .affiliate-actions .btn {
    border-radius: 8px;
    padding: 8px 16px;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.2s ease;
}

.affiliate-card .affiliate-actions .btn:hover {
    transform: translateY(-2px);
}

.btn-profile {
    background: #6c2b8a;
    color: white;
    border: none;
}

.btn-profile:hover {
    background: #5a2373;
    color: white;
    box-shadow: 0 4px 12px rgba(108, 43, 138, 0.3);
}

/* ===== FORM STYLES - CLEAN ===== */
.preexistencia-update-body .form-group {
    margin-bottom: 20px;
}

.preexistencia-update-body .form-control {
    border-radius: 8px;
    border: 2px solid #e9ecef;
    padding: 10px 14px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.preexistencia-update-body .form-control:focus {
    border-color: #6c2b8a;
    box-shadow: 0 0 0 3px rgba(108, 43, 138, 0.1);
}

.preexistencia-update-body .form-control-lg {
    padding: 12px 16px;
    font-size: 16px;
}

.preexistencia-update-body .form-label {
    font-weight: 600;
    color: #1a1a2e;
    font-size: 14px;
}

.preexistencia-update-body .help-block {
    color: #dc3545;
    font-size: 13px;
    margin-top: 4px;
}

.preexistencia-update-body .has-error .form-control {
    border-color: #dc3545;
}

.preexistencia-update-body .has-error .form-control:focus {
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
}

/* Hide the default hint text - using tooltips instead */
.preexistencia-update-body .form-text {
    display: none !important;
}

/* ===== ESTATUS SELECT ===== */
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

/* ===== CONFIRMATION NOTICE ===== */
.confirmation-notice {
    background: #fff8e7;
    border-left: 4px solid #856404;
    border-radius: 8px;
    padding: 16px 20px;
    margin-top: 24px;
}

.confirmation-notice .notice-content {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.confirmation-notice .notice-content i {
    color: #856404;
    font-size: 20px;
    margin-top: 2px;
}

.confirmation-notice .notice-content .notice-text {
    color: #856404;
    font-size: 0.95rem;
}

.confirmation-notice .notice-content .notice-text strong {
    color: #6b4c00;
}

/* ===== FORM ACTIONS ===== */
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

/* ===== TOOLTIP STYLES ===== */
.field-tooltip {
    display: inline-block;
    margin-left: 6px;
    color: #6c757d;
    cursor: help;
    font-size: 14px;
}

.field-tooltip:hover {
    color: #6c2b8a;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .preexistencia-update-header {
        flex-direction: column;
        align-items: flex-start;
        padding: 20px;
    }
    
    .preexistencia-update-header h1 {
        font-size: 1.2rem;
    }
    
    .preexistencia-update-header .header-actions {
        width: 100%;
    }
    
    .preexistencia-update-header .header-actions .btn {
        flex: 1;
        justify-content: center;
        font-size: 12px;
        padding: 6px 12px;
    }
    
    .preexistencia-update-body {
        padding: 16px;
    }
    
    .affiliate-card .affiliate-card-body {
        flex-direction: column;
        text-align: center;
        padding: 16px;
    }
    
    .affiliate-card .affiliate-info .affiliate-details {
        justify-content: center;
    }
    
    .affiliate-card .affiliate-actions {
        width: 100%;
        justify-content: center;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
        justify-content: center;
    }
    
    .confirmation-notice .notice-content {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .preexistencia-update-header h1 {
        font-size: 1rem;
    }
    
    .status-badge-large {
        font-size: 0.75rem;
        padding: 4px 12px;
    }
    
    .affiliate-card .affiliate-avatar {
        width: 56px;
        height: 56px;
        font-size: 24px;
    }
    
    .affiliate-card .affiliate-info .affiliate-name {
        font-size: 16px;
    }
    
    .affiliate-card .affiliate-info .affiliate-details .detail-item {
        font-size: 13px;
    }
}
");
?>
<div class="preexistencia-update-container">

    <!-- Header -->
    <div class="preexistencia-update-header">
        <h1>
            <i class="fas fa-edit"></i>
            <?= Html::encode($this->title) ?>
            <span class="badge-id">
                <i class="fas fa-hashtag"></i> ID: <?= $model->id ?>
            </span>
            <span id="status-badge-header" class="status-badge-large <?= strtolower(str_replace(' ', '', $model->estatus)) ?>">
                <?php
                $statusIcons = [
                    'Activo' => 'fa-check-circle',
                    'Inactivo' => 'fa-ban',
                    'En Tratamiento' => 'fa-heartbeat',
                    'En Remisión' => 'fa-clock',
                ];
                $icon = $statusIcons[$model->estatus] ?? 'fa-circle';
                ?>
                <i class="fas <?= $icon ?>"></i>
                <span id="status-badge-text"><?= Html::encode($model->estatus ?: 'Activo') ?></span>
            </span>
        </h1>
        <div class="header-actions">
            <?= Html::a(
                '<i class="fas fa-arrow-left"></i> Volver',
                ['view', 'id' => $model->id],
                ['class' => 'btn btn-back']
            ) ?>
            <?= Html::a(
                '<i class="fas fa-trash"></i> Eliminar',
                ['delete', 'id' => $model->id],
                [
                    'class' => 'btn btn-delete-header',
                    'data-method' => 'post',
                    'data-confirm' => '¿Está seguro de eliminar esta pre-existencia? Esta acción no se puede deshacer.',
                ]
            ) ?>
        </div>
    </div>

    <!-- Body -->
    <div class="preexistencia-update-body">

        <!-- Affiliate Card -->
        <?php if ($afiliado && is_object($afiliado)): ?>
            <div class="affiliate-card">
                <div class="affiliate-card-header">
                    <i class="fas fa-user-circle"></i>
                    <h3>Información del Afiliado</h3>
                </div>
                <div class="affiliate-card-body">
                    <div class="affiliate-avatar">
                        <?php if ($afiliado->selfie): ?>
                            <?= Html::img($afiliado->selfie, [
                                'alt' => 'Foto de perfil',
                                'style' => 'width: 100%; height: 100%; border-radius: 50%; object-fit: cover;'
                            ]) ?>
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </div>
                    <div class="affiliate-info">
                        <div class="affiliate-name">
                            <?= Html::encode($afiliado->nombres . ' ' . $afiliado->apellidos) ?>
                        </div>
                        <div class="affiliate-details">
                            <span class="detail-item">
                                <i class="fas fa-id-card"></i>
                                <?= Html::encode($afiliado->tipo_cedula . '-' . $afiliado->cedula) ?>
                            </span>
                            <span class="detail-item">
                                <i class="fas fa-phone"></i>
                                <?= Html::encode($afiliado->telefono ?? 'N/A') ?>
                            </span>
                            <span class="detail-item">
                                <i class="fas fa-envelope"></i>
                                <?= Html::encode($afiliado->email ?? 'N/A') ?>
                            </span>
                            <?php if ($afiliado->plan): ?>
                                <span class="detail-item">
                                    <i class="fas fa-file-contract"></i>
                                    <?= Html::encode($afiliado->plan->nombre ?? 'N/A') ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($afiliado->clinica): ?>
                                <span class="detail-item">
                                    <i class="fas fa-hospital"></i>
                                    <?= Html::encode($afiliado->clinica->nombre ?? 'N/A') ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="affiliate-actions">
                        <?= Html::a(
                            '<i class="fas fa-external-link-alt"></i> Ver Perfil',
                            ['/user-datos/view', 'id' => $afiliado->id],
                            [
                                'class' => 'btn btn-profile',
                                'target' => '_blank',
                            ]
                        ) ?>
                    </div>
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
                    'placeholder' => 'Ej: Diabetes Tipo 2, Hipertensión Arterial...',
                    'maxlength' => true,
                    'autocomplete' => 'off',
                ])->label('Nombre de la Pre-existencia <span class="text-danger">*</span> <i class="fas fa-info-circle field-tooltip" data-toggle="tooltip" title="Ingrese el nombre clínico completo de la condición médica."></i>') ?>
            </div>

            <!-- Fecha de Diagnóstico -->
            <div class="col-md-6">
                <?= $form->field($model, 'fecha_diagnostico')->textInput([
                    'type' => 'date',
                    'class' => 'form-control form-control-lg',
                    'autocomplete' => 'off',
                ])->label('Fecha de Diagnóstico <i class="fas fa-info-circle field-tooltip" data-toggle="tooltip" title="Fecha en que fue diagnosticada esta condición."></i>') ?>
            </div>

            <!-- Descripción -->
            <div class="col-md-12">
                <?= $form->field($model, 'descripcion')->textarea([
                    'rows' => 4,
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'Describa los detalles de la pre-existencia...',
                ])->label('Descripción <i class="fas fa-info-circle field-tooltip" data-toggle="tooltip" title="Proporcione información detallada sobre la condición médica (síntomas, severidad, evolución, tratamientos previos, etc.)."></i>') ?>
            </div>

            <!-- Médico Tratante -->
            <div class="col-md-6">
                <?= $form->field($model, 'medico_tratante')->textInput([
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'Nombre del médico tratante',
                    'maxlength' => true,
                    'autocomplete' => 'off',
                ])->label('Médico Tratante <i class="fas fa-info-circle field-tooltip" data-toggle="tooltip" title="Médico responsable del diagnóstico o tratamiento de esta condición."></i>') ?>
            </div>

            <!-- Estatus -->
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
                )->label('Estatus <i class="fas fa-info-circle field-tooltip" data-toggle="tooltip" title="Estado actual de la condición médica."></i>') ?>
            </div>

            <!-- Medicamentos -->
            <div class="col-md-12">
                <?= $form->field($model, 'medicamentos')->textarea([
                    'rows' => 3,
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'Lista de medicamentos recetados...',
                ])->label('Medicamentos <i class="fas fa-info-circle field-tooltip" data-toggle="tooltip" title="Medicamentos que el paciente está tomando para esta condición."></i>') ?>
            </div>

            <!-- Observaciones -->
            <div class="col-md-12">
                <?= $form->field($model, 'observaciones')->textarea([
                    'rows' => 3,
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'Observaciones adicionales...',
                ])->label('Observaciones <i class="fas fa-info-circle field-tooltip" data-toggle="tooltip" title="Cualquier otra información relevante sobre la condición médica."></i>') ?>
            </div>
        </div>

        <!-- Confidentiality Notice -->
        <div class="confirmation-notice">
            <div class="notice-content">
                <i class="fas fa-shield-alt fa-2x"></i>
                <div class="notice-text">
                    <strong>Aviso de Confidencialidad</strong>
                    <p class="mb-0">
                        La información de pre-existencias es de carácter <strong>médico y confidencial</strong>.
                        Será utilizada exclusivamente para fines de seguimiento clínico, gestión de cobertura y
                        atención médica del afiliado.
                    </p>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <?= Html::submitButton(
                '<i class="fas fa-save"></i> Actualizar Pre-existencia',
                ['class' => 'btn btn-save']
            ) ?>
            <?= Html::a(
                '<i class="fas fa-times"></i> Cancelar',
                ['view', 'id' => $model->id],
                ['class' => 'btn btn-cancel']
            ) ?>
            <?= Html::a(
                '<i class="fas fa-trash"></i> Eliminar',
                ['delete', 'id' => $model->id],
                [
                    'class' => 'btn btn-delete-form',
                    'data-method' => 'post',
                    'data-confirm' => '¿Está seguro de eliminar esta pre-existencia? Esta acción no se puede deshacer.',
                ]
            ) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>

<?php
// Additional JS for status badge preview and tooltips
$this->registerJs("
    // Initialize tooltips
    jQuery('[data-toggle=\"tooltip\"]').tooltip({
        placement: 'top',
        trigger: 'hover',
        delay: { show: 300, hide: 100 }
    });
    
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
    
    // Handle form submission with confirmation for status change
    jQuery('#preexistencia-form').on('beforeSubmit', function(e) {
        var currentStatus = jQuery('#preexistencia-estatus').val();
        var originalStatus = '{$model->estatus}';
        
        if (currentStatus !== originalStatus) {
            return confirm('¿Está seguro de cambiar el estatus de esta pre-existencia de \"' + originalStatus + '\" a \"' + currentStatus + '\"?');
        }
        return true;
    });
");
?>