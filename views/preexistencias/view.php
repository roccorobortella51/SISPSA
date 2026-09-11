<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\UserHelper;

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

// Determine title
$this->title = 'Pre-existencia: ' . $model->nombre;

// Breadcrumbs
$this->params['breadcrumbs'][] = ['label' => 'Pre-existencias', 'url' => ['index', 'user_id' => $model->user_id]];
if ($afiliado && is_object($afiliado)) {
    $this->params['breadcrumbs'][] = [
        'label' => $afiliado->nombres . ' ' . $afiliado->apellidos,
        'url' => ['/user-datos/view', 'id' => $afiliado->id]
    ];
}
$this->params['breadcrumbs'][] = $model->nombre;

// Register CSS
$this->registerCss("
/* ============================================ */
/* PRE-EXISTENCIA VIEW PAGE - CLEAN */
/* ============================================ */

.preexistencia-view-container {
    max-width: 100%;
}

/* ===== HEADER ===== */
.preexistencia-view-header {
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

.preexistencia-view-header h1 {
    color: white;
    margin: 0;
    font-size: 1.6rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.preexistencia-view-header h1 .badge-id {
    background: rgba(255, 255, 255, 0.15) !important;
    color: white !important;
    font-size: 0.75rem;
    padding: 4px 12px;
    border-radius: 20px;
    font-weight: 500;
}

/* ===== STATUS BADGE LARGE ===== */
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
.preexistencia-view-header .header-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}

.preexistencia-view-header .header-actions .btn {
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

.btn-edit-header {
    background: rgba(255, 255, 255, 0.2) !important;
    color: white !important;
    border: 1px solid rgba(255, 255, 255, 0.3) !important;
}

.btn-edit-header:hover {
    background: rgba(255, 255, 255, 0.35) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-toggle-header {
    background: rgba(255, 193, 7, 0.2) !important;
    color: white !important;
    border: 1px solid rgba(255, 193, 7, 0.3) !important;
}

.btn-toggle-header:hover {
    background: rgba(255, 193, 7, 0.35) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.2);
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
.preexistencia-view-body {
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

.btn-profile-outline {
    background: transparent;
    color: #6c2b8a;
    border: 2px solid #6c2b8a;
}

.btn-profile-outline:hover {
    background: #6c2b8a;
    color: white;
    box-shadow: 0 4px 12px rgba(108, 43, 138, 0.2);
}

/* ===== DETAIL SECTIONS ===== */
.detail-section {
    margin-bottom: 28px;
}

.detail-section:last-child {
    margin-bottom: 0;
}

.detail-section-title {
    font-size: 15px;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 14px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.detail-section-title i {
    color: #6c2b8a;
    font-size: 16px;
}

/* ===== DETAIL GRID ===== */
.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 14px;
}

.detail-item {
    padding: 12px 16px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 3px solid #6c2b8a;
    transition: all 0.2s ease;
}

.detail-item:hover {
    background: #f5f0f8;
}

.detail-item .detail-label {
    font-size: 11px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: block;
    margin-bottom: 4px;
}

.detail-item .detail-label i {
    margin-right: 4px;
}

.detail-item .detail-value {
    font-size: 15px;
    font-weight: 500;
    color: #1a1a2e;
    word-break: break-word;
}

.detail-item .detail-value.text-muted {
    color: #6c757d;
    font-weight: 400;
}

/* ===== CONTENT BLOCKS ===== */
.content-block {
    padding: 16px 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 3px solid #6c2b8a;
    line-height: 1.6;
    color: #1a1a2e;
}

.content-block.medications {
    border-left-color: #ffc107;
}

.content-block.observations {
    border-left-color: #17a2b8;
}

/* ===== TIMESTAMP INFO ===== */
.timestamp-info {
    display: flex;
    gap: 24px;
    flex-wrap: wrap;
    padding: 12px 16px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-top: 4px;
}

.timestamp-info .timestamp-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #6c757d;
}

.timestamp-info .timestamp-item i {
    font-size: 14px;
    color: #6c2b8a;
}

.timestamp-info .timestamp-item a {
    color: #6c2b8a;
    text-decoration: none;
    font-weight: 500;
}

.timestamp-info .timestamp-item a:hover {
    text-decoration: underline;
}

/* ===== TOOLTIP STYLES ===== */
.detail-tooltip {
    display: inline-block;
    margin-left: 4px;
    color: #6c757d;
    cursor: help;
    font-size: 12px;
    opacity: 0.7;
}

.detail-tooltip:hover {
    color: #6c2b8a;
    opacity: 1;
}

/* ===== ACTION FOOTER ===== */
.action-footer {
    margin-top: 24px;
    padding-top: 24px;
    border-top: 2px solid #f0f0f0;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.action-footer .btn {
    border-radius: 8px;
    padding: 10px 24px;
    font-weight: 600;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.action-footer .btn:hover {
    transform: translateY(-2px);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .preexistencia-view-header {
        flex-direction: column;
        align-items: flex-start;
        padding: 20px;
    }
    
    .preexistencia-view-header h1 {
        font-size: 1.2rem;
    }
    
    .preexistencia-view-header .header-actions {
        width: 100%;
    }
    
    .preexistencia-view-header .header-actions .btn {
        flex: 1;
        justify-content: center;
        font-size: 12px;
        padding: 6px 12px;
    }
    
    .preexistencia-view-body {
        padding: 16px;
    }
    
    .detail-grid {
        grid-template-columns: 1fr;
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
    
    .timestamp-info {
        flex-direction: column;
        gap: 8px;
    }
    
    .action-footer {
        flex-direction: column;
    }
    
    .action-footer .btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .preexistencia-view-header h1 {
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
    
    .detail-item {
        padding: 10px 14px;
    }
    
    .detail-item .detail-value {
        font-size: 14px;
    }
}
");
?>
<div class="preexistencia-view-container">

    <!-- Header -->
    <div class="preexistencia-view-header">
        <h1>
            <i class="fas fa-heartbeat"></i>
            <?= Html::encode($this->title) ?>
            <span class="badge-id">
                <i class="fas fa-hashtag"></i> ID: <?= $model->id ?>
            </span>
            <span class="status-badge-large <?= strtolower(str_replace(' ', '', $model->estatus)) ?>">
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
                <?= Html::encode($model->estatus ?: 'Activo') ?>
            </span>
        </h1>
        <div class="header-actions">
            <?= Html::a(
                '<i class="fas fa-arrow-left"></i> Volver',
                ['index', 'user_id' => $model->user_id],
                ['class' => 'btn btn-back']
            ) ?>
            <?= Html::a(
                '<i class="fas fa-edit"></i> Actualizar',
                ['update', 'id' => $model->id],
                ['class' => 'btn btn-edit-header']
            ) ?>
            <?php if ($model->estatus === \app\models\Preexistencias::ESTATUS_ACTIVO): ?>
                <?= Html::a(
                    '<i class="fas fa-pause"></i> Desactivar',
                    ['toggle-status', 'id' => $model->id],
                    [
                        'class' => 'btn btn-toggle-header',
                        'data-method' => 'post',
                        'data-confirm' => '¿Está seguro de desactivar esta pre-existencia?',
                    ]
                ) ?>
            <?php else: ?>
                <?= Html::a(
                    '<i class="fas fa-play"></i> Activar',
                    ['toggle-status', 'id' => $model->id],
                    [
                        'class' => 'btn btn-toggle-header',
                        'data-method' => 'post',
                        'data-confirm' => '¿Está seguro de activar esta pre-existencia?',
                    ]
                ) ?>
            <?php endif; ?>
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
    <div class="preexistencia-view-body">

        <!-- ============================================================ -->
        <!-- AFFILIATE CARD - IMPROVED VERSION -->
        <!-- ============================================================ -->
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

        <!-- Main Details -->
        <div class="detail-section">
            <div class="detail-section-title">
                <i class="fas fa-info-circle"></i>
                Información de la Pre-existencia
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">
                        <i class="fas fa-notes-medical"></i> Nombre
                    </span>
                    <span class="detail-value"><?= Html::encode($model->nombre) ?></span>
                </div>
                <div class="detail-item" style="border-left-color: #1565c0;">
                    <span class="detail-label">
                        <i class="fas fa-calendar-alt"></i> Fecha de Diagnóstico
                        <i class="fas fa-info-circle detail-tooltip" data-toggle="tooltip" title="Fecha en que fue diagnosticada esta condición médica."></i>
                    </span>
                    <span class="detail-value">
                        <?= $model->fecha_diagnostico ? Yii::$app->formatter->asDate($model->fecha_diagnostico, 'dd/MM/yyyy') : '<span class="text-muted">No especificada</span>' ?>
                    </span>
                </div>
                <div class="detail-item" style="border-left-color: #2e7d32;">
                    <span class="detail-label">
                        <i class="fas fa-user-md"></i> Médico Tratante
                        <i class="fas fa-info-circle detail-tooltip" data-toggle="tooltip" title="Médico responsable del diagnóstico o tratamiento de esta condición."></i>
                    </span>
                    <span class="detail-value">
                        <?= $model->medico_tratante ? Html::encode($model->medico_tratante) : '<span class="text-muted">No especificado</span>' ?>
                    </span>
                </div>
                <div class="detail-item" style="border-left-color: #e65100;">
                    <span class="detail-label">
                        <i class="fas fa-tag"></i> Estatus
                        <i class="fas fa-info-circle detail-tooltip" data-toggle="tooltip" title="Estado actual de la condición médica."></i>
                    </span>
                    <span class="detail-value">
                        <?php
                        $badgeClasses = [
                            'Activo' => 'badge-success',
                            'Inactivo' => 'badge-secondary',
                            'En Tratamiento' => 'badge-info',
                            'En Remisión' => 'badge-warning',
                        ];
                        $class = $badgeClasses[$model->estatus] ?? 'badge-secondary';
                        ?>
                        <span class="badge <?= $class ?>" style="font-size: 14px; padding: 6px 14px;">
                            <?= Html::encode($model->estatus ?: 'Activo') ?>
                        </span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Description -->
        <?php if ($model->descripcion): ?>
            <div class="detail-section">
                <div class="detail-section-title">
                    <i class="fas fa-align-left"></i>
                    Descripción
                    <i class="fas fa-info-circle detail-tooltip" data-toggle="tooltip" title="Información detallada sobre la condición médica (síntomas, severidad, evolución, tratamientos previos, etc.)."></i>
                </div>
                <div class="content-block">
                    <?= nl2br(Html::encode($model->descripcion)) ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Medications -->
        <?php if ($model->medicamentos): ?>
            <div class="detail-section">
                <div class="detail-section-title">
                    <i class="fas fa-pills"></i>
                    Medicamentos
                    <i class="fas fa-info-circle detail-tooltip" data-toggle="tooltip" title="Medicamentos que el paciente está tomando para esta condición."></i>
                </div>
                <div class="content-block medications">
                    <?= nl2br(Html::encode($model->medicamentos)) ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Observations -->
        <?php if ($model->observaciones): ?>
            <div class="detail-section">
                <div class="detail-section-title">
                    <i class="fas fa-comment"></i>
                    Observaciones
                    <i class="fas fa-info-circle detail-tooltip" data-toggle="tooltip" title="Cualquier otra información relevante sobre la condición médica."></i>
                </div>
                <div class="content-block observations">
                    <?= nl2br(Html::encode($model->observaciones)) ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Timestamps -->
        <div class="detail-section">
            <div class="detail-section-title">
                <i class="fas fa-clock"></i>
                Información de Registro
            </div>
            <div class="timestamp-info">
                <div class="timestamp-item">
                    <i class="fas fa-calendar-plus"></i>
                    <strong>Creado:</strong> <?= Yii::$app->formatter->asDatetime($model->created_at, 'dd/MM/yyyy HH:mm') ?>
                </div>
                <?php if ($model->updated_at): ?>
                    <div class="timestamp-item">
                        <i class="fas fa-calendar-edit"></i>
                        <strong>Actualizado:</strong> <?= Yii::$app->formatter->asDatetime($model->updated_at, 'dd/MM/yyyy HH:mm') ?>
                    </div>
                <?php endif; ?>
                <?php if ($model->sis_siniestro_id): ?>
                    <div class="timestamp-item">
                        <i class="fas fa-file-medical"></i>
                        <strong>Registrada en:</strong>
                        <?= Html::a(
                            'Atención/Cita #' . $model->sis_siniestro_id,
                            ['/sis-siniestro/view', 'id' => $model->sis_siniestro_id],
                            ['target' => '_blank']
                        ) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Footer -->
        <div class="action-footer">
            <?= Html::a(
                '<i class="fas fa-arrow-left"></i> Volver a la lista',
                ['index', 'user_id' => $model->user_id],
                ['class' => 'btn btn-outline-secondary']
            ) ?>
            <?= Html::a(
                '<i class="fas fa-edit"></i> Actualizar',
                ['update', 'id' => $model->id],
                ['class' => 'btn btn-primary']
            ) ?>
            <?php if ($model->estatus === \app\models\Preexistencias::ESTATUS_ACTIVO): ?>
                <?= Html::a(
                    '<i class="fas fa-pause"></i> Desactivar',
                    ['toggle-status', 'id' => $model->id],
                    [
                        'class' => 'btn btn-warning',
                        'data-method' => 'post',
                        'data-confirm' => '¿Está seguro de desactivar esta pre-existencia?',
                    ]
                ) ?>
            <?php else: ?>
                <?= Html::a(
                    '<i class="fas fa-play"></i> Activar',
                    ['toggle-status', 'id' => $model->id],
                    [
                        'class' => 'btn btn-success',
                        'data-method' => 'post',
                        'data-confirm' => '¿Está seguro de activar esta pre-existencia?',
                    ]
                ) ?>
            <?php endif; ?>
            <?= Html::a(
                '<i class="fas fa-trash"></i> Eliminar',
                ['delete', 'id' => $model->id],
                [
                    'class' => 'btn btn-danger',
                    'data-method' => 'post',
                    'data-confirm' => '¿Está seguro de eliminar esta pre-existencia? Esta acción no se puede deshacer.',
                ]
            ) ?>
        </div>

    </div>
</div>

<?php
// Additional JS for tooltips
$this->registerJs("
    // Initialize tooltips
    jQuery('[data-toggle=\"tooltip\"]').tooltip({
        placement: 'top',
        trigger: 'hover',
        delay: { show: 300, hide: 100 }
    });
");
?>