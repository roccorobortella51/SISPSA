<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Cancelar Cita Médica';
$this->params['breadcrumbs'][] = ['label' => 'Citas', 'url' => ['index', 'user_id' => $model->iduser, 'modo' => 'cita']];
$this->params['breadcrumbs'][] = $this->title;

// ----------------------------------------------------------------------
// CÁLCULOS DE TIEMPO
// ----------------------------------------------------------------------
$appointmentDateTime = $model->fecha_atencion . ' ' . ($model->hora_atencion ? substr($model->hora_atencion, 0, 5) : '00:00');
$appointmentTimestamp = strtotime($appointmentDateTime);
$currentTimestamp = time();
$hoursUntil = round(($appointmentTimestamp - $currentTimestamp) / 3600, 1);
$isPast = ($appointmentTimestamp < $currentTimestamp);

// Determinar tipo de cancelación
if ($isPast) {
    $cancellationType = 'past';
    $canCancelWithoutPenalty = false;
} else {
    $canCancelWithoutPenalty = ($hoursUntil >= 48);
    $cancellationType = $canCancelWithoutPenalty ? 'early' : 'late';
}

// Configuración de colores y mensajes según el tipo
$alertConfig = [
    'past' => [
        'class' => 'alert-danger',
        'icon' => 'fa-calendar-times',
        'title' => 'Cita Vencida',
        'message' => 'Esta cita ya ocurrió y no puede ser cancelada sin penalidad.'
    ],
    'late' => [
        'class' => 'alert-warning',
        'icon' => 'fa-exclamation-triangle',
        'title' => 'Cancelación con Penalidad',
        'message' => 'Ya no es posible cancelar sin penalidad (faltan menos de 48 horas).'
    ],
    'early' => [
        'class' => 'alert-success',
        'icon' => 'fa-check-circle',
        'title' => 'Cancelación sin Penalidad',
        'message' => 'Aún puede cancelar esta cita sin que se descuente de su cobertura.'
    ]
];

$currentAlert = $alertConfig[$cancellationType];

// Status badges
$statusBadges = [
    'scheduled' => '<span class="badge badge-warning"><i class="fas fa-calendar"></i> Agendada</span>',
    'confirmed' => '<span class="badge badge-info"><i class="fas fa-check-circle"></i> Confirmada</span>',
    'cancelled' => '<span class="badge badge-secondary"><i class="fas fa-ban"></i> Cancelada</span>',
    'completed' => '<span class="badge badge-success"><i class="fas fa-check-double"></i> Completada</span>',
    'no_show' => '<span class="badge badge-danger"><i class="fas fa-user-slash"></i> No Asistió</span>',
];
?>

<div class="sis-siniestro-cancel">
    <div class="ms-panel">
        <!-- HEADER -->
        <div class="ms-panel-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <i class="fas fa-calendar-times fa-2x mr-3" style="color: white;"></i>
                    <h3 class="mb-0" style="color: white; font-weight: 600;"><?= Html::encode($this->title) ?></h3>
                </div>
                <span class="badge badge-light" style="font-size: 0.9rem; padding: 8px 16px;">
                    <i class="fas fa-clock mr-1"></i>
                    <?= $isPast ? 'Cita vencida' : (($canCancelWithoutPenalty) ? 'Dentro del período' : 'Fuera del período') ?>
                </span>
            </div>
        </div>

        <div class="ms-panel-body">
            <!-- ALERTA PRINCIPAL -->
            <div class="alert <?= $currentAlert['class'] ?> mb-4" style="border-radius: 12px; border-left: 5px solid; padding: 20px;">
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <i class="fas <?= $currentAlert['icon'] ?> fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="mb-1 font-weight-bold"><?= $currentAlert['title'] ?></h5>
                        <p class="mb-0"><?= $currentAlert['message'] ?></p>
                        <?php if (!$isPast): ?>
                            <small class="mt-1 d-block">
                                <i class="fas fa-hourglass-half mr-1"></i>
                                <strong><?= number_format(abs($hoursUntil), 1) ?></strong> horas restantes para la cita
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- POLÍTICA DE CANCELACIÓN - SIMPLIFICADA -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-light border-0" style="background: #f8f9fa !important;">
                    <i class="fas fa-gavel mr-2" style="color: #2a5298;"></i>
                    <strong>Política de Cancelación</strong>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-6">
                            <div class="p-4 border rounded-lg" style="background: <?= ($cancellationType == 'early') ? '#d4edda' : '#f8f9fa' ?>; border-color: #28a745 !important;">
                                <i class="fas fa-calendar-check fa-3x mb-3" style="color: #28a745;"></i>
                                <h5 class="mb-2">≥ 48 horas de anticipación</h5>
                                <div class="badge badge-success px-3 py-2">SIN PENALIDAD</div>
                                <p class="mt-3 mb-0 small">✅ La cobertura es <strong>restaurada</strong></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-4 border rounded-lg" style="background: <?= ($cancellationType != 'early') ? '#f8d7da' : '#f8f9fa' ?>; border-color: #dc3545 !important;">
                                <i class="fas fa-hourglass-end fa-3x mb-3" style="color: #dc3545;"></i>
                                <h5 class="mb-2">Menos de 48 horas o después de la cita</h5>
                                <div class="badge badge-danger px-3 py-2">CON PENALIDAD</div>
                                <p class="mt-3 mb-0 small">❌ La cobertura <strong>no es restaurada</strong></p>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-warning mt-3 mb-0" style="background: #fff3cd; border-radius: 10px;">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Importante:</strong> En ambos casos de penalidad (menos de 48 horas o cita ya vencida),
                        el <strong>$<?= number_format($model->costo_total, 2) ?></strong> será descontado de la cobertura del afiliado sin posibilidad de reembolso.
                    </div>
                </div>
            </div>

            <!-- INFORMACIÓN DE LA CITA -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-light border-0" style="background: #f8f9fa !important;">
                    <i class="fas fa-info-circle mr-2" style="color: #2a5298;"></i>
                    <strong>Información de la Cita</strong>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Columna Izquierda -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small mb-1">PACIENTE</label>
                                <div class="font-weight-bold" style="font-size: 1.1rem;">
                                    <i class="fas fa-user mr-2 text-primary"></i>
                                    <?= Html::encode($model->afiliado->nombres . ' ' . $model->afiliado->apellidos) ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small mb-1">FECHA Y HORA</label>
                                <div>
                                    <i class="fas fa-calendar-alt mr-2 text-primary"></i>
                                    <?= Yii::$app->formatter->asDate($model->fecha_atencion) ?>
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-clock mr-2 text-primary"></i>
                                    <?= $model->hora_atencion ? substr($model->hora_atencion, 0, 5) : 'No especificada' ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small mb-1">CLÍNICA</label>
                                <div>
                                    <i class="fas fa-building mr-2 text-primary"></i>
                                    <?= $model->clinica ? $model->clinica->nombre : 'N/A' ?>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small mb-1">ESTADO ACTUAL</label>
                                <div>
                                    <?= $statusBadges[$model->appointment_status] ?? '<span class="badge badge-secondary">' . $model->appointment_status . '</span>' ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small mb-1">COSTO TOTAL</label>
                                <div>
                                    <span style="font-size: 1.4rem; font-weight: 700; color: <?= ($cancellationType == 'early') ? '#28a745' : '#dc3545' ?>;">
                                        $<?= number_format($model->costo_total, 2) ?>
                                    </span>
                                    <?php if ($cancellationType == 'early'): ?>
                                        <span class="badge badge-success ml-2">
                                            <i class="fas fa-undo-alt"></i> Se reembolsará
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-danger ml-2">
                                            <i class="fas fa-exclamation-triangle"></i> No se reembolsará
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Servicios -->
                    <?php if (!empty($model->baremos)): ?>
                        <div class="mt-3 pt-3 border-top">
                            <label class="text-muted small mb-2">
                                <i class="fas fa-stethoscope mr-1"></i> SERVICIOS AGENDADOS
                            </label>
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless mb-0">
                                    <tbody>
                                        <?php foreach ($model->baremos as $baremo): ?>
                                            <tr>
                                                <td style="width: 70%;"><?= Html::encode($baremo->nombre_servicio) ?></td>
                                                <td class="text-right font-weight-bold">$<?= number_format($baremo->precio, 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ADVERTENCIA DE PENALIZACIÓN (si aplica) -->
            <?php if (!$canCancelWithoutPenalty): ?>
                <div class="alert alert-danger mb-4" style="background: linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%); border-left: 4px solid #dc3545;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle fa-2x mr-3" style="color: #dc3545;"></i>
                        <div>
                            <strong class="d-block" style="font-size: 1rem;">¡Atención!</strong>
                            <span>
                                Esta acción es <strong>irreversible</strong>.
                                <?php if ($isPast): ?>
                                    La cita ya expiró. Al cancelar, <strong>$<?= number_format($model->costo_total, 2) ?></strong> será descontado de la cobertura.
                                <?php else: ?>
                                    Al cancelar, <strong>$<?= number_format($model->costo_total, 2) ?></strong> será descontado de la cobertura del afiliado.
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-success mb-4" style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); border-left: 4px solid #28a745;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle fa-2x mr-3" style="color: #28a745;"></i>
                        <div>
                            <strong class="d-block" style="font-size: 1rem;">Cancelación sin penalidad</strong>
                            <span>El costo total será <strong>totalmente reembolsado</strong> a la cobertura del afiliado.</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- FORMULARIO DE CANCELACIÓN -->
            <?php $form = ActiveForm::begin(); ?>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-0" style="background: #f8f9fa !important;">
                    <i class="fas fa-edit mr-2" style="color: #2a5298;"></i>
                    <strong>Motivo de Cancelación</strong>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label font-weight-bold">
                            Motivo <span class="text-danger">*</span>
                        </label>
                        <?= Html::textarea('reason', '', [
                            'class' => 'form-control',
                            'rows' => 4,
                            'placeholder' => 'Describa detalladamente el motivo de la cancelación...',
                            'required' => true,
                            'style' => 'border-radius: 10px; resize: vertical;'
                        ]) ?>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Este motivo quedará registrado para futuras referencias.
                        </small>
                    </div>

                    <div class="form-group mt-4 text-center">
                        <?= Html::submitButton('<i class="fas fa-check-circle mr-2"></i> Confirmar Cancelación', [
                            'class' => 'btn btn-danger btn-lg px-5',
                            'style' => 'border-radius: 50px; font-weight: 600;',
                            'onclick' => 'return confirm("¿Está completamente seguro de cancelar esta cita?\n\nEsta acción es irreversible y puede tener consecuencias financieras para el afiliado.");'
                        ]) ?>

                        <?= Html::a('<i class="fas fa-arrow-left mr-2"></i> Volver', ['view', 'id' => $model->id], [
                            'class' => 'btn btn-secondary btn-lg ml-3 px-4',
                            'style' => 'border-radius: 50px;'
                        ]) ?>
                    </div>
                </div>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>

<?php
// CSS adicional para mejorar la apariencia
$this->registerCss("
    .rounded-lg {
        border-radius: 12px !important;
    }
    
    .badge {
        font-weight: 500;
        padding: 6px 12px;
    }
    
    .ms-panel-header {
        border-radius: 16px 16px 0 0;
    }
    
    .card {
        border-radius: 16px;
        overflow: hidden;
    }
    
    .table-sm td {
        padding: 8px 0;
    }
");
?>