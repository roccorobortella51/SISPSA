<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Url;
use app\models\Planes;
use app\models\RmClinica;
use app\models\UserDatos;

/* @var $this yii\web\View */
/* @var $model app\models\Contratos */
/* @var $form yii\bootstrap4\ActiveForm */

// Helper function to format field values
$formatValue = function ($value) {
    return $value ? Html::encode($value) : '<span class="text-muted fst-italic">No especificado</span>';
};

// Get related model names for display
$planNombre = $model->plan ? $model->plan->nombre : null;
$clinicaNombre = $model->clinica ? $model->clinica->nombre : null;
$userNombre = $model->user ? $model->user->nombres . ' ' . $model->user->apellidos : null;
$userCedula = $model->user ? ($model->user->tipo_cedula . '-' . $model->user->cedula) : null;
?>

<div class="contratos-form p-3">

    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'shadow p-4 rounded-3 bg-light'],
        'fieldConfig' => [
            'template' => "{label}\n{input}\n{hint}\n{error}",
            'options' => ['class' => 'form-group mb-3'],
            'labelOptions' => ['class' => 'form-label text-primary fw-bold'],
        ],
    ]); ?>

    <!-- INFORMACIÓN DEL AFILIADO (READ-ONLY) -->
    <div class="card mb-4 border-info">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-user me-2"></i> Información del Afiliado</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small text-uppercase">Nombre Completo</label>
                        <div class="h6"><?= $formatValue($userNombre) ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small text-uppercase">Cédula</label>
                        <div class="h6"><?= $formatValue($userCedula) ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small text-uppercase">Email</label>
                        <div class="h6"><?= $formatValue($model->user ? $model->user->email : null) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h4 class="mb-4 text-info border-bottom pb-2"><i class="fas fa-info-circle me-2"></i> Información General del Contrato</h4>
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label text-primary fw-bold">Plan</label>
                <div class="form-control bg-light" style="min-height: 38px; padding: 0.375rem 0.75rem;">
                    <?= $formatValue($planNombre) ?>
                    <small class="text-muted">(ID: <?= $model->plan_id ?: 'N/A' ?>)</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label text-primary fw-bold">Clínica</label>
                <div class="form-control bg-light" style="min-height: 38px; padding: 0.375rem 0.75rem;">
                    <?= $formatValue($clinicaNombre) ?>
                    <small class="text-muted">(ID: <?= $model->clinica_id ?: 'N/A' ?>)</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label text-primary fw-bold">Número de Contrato</label>
                <div class="form-control bg-light"><?= $formatValue($model->nrocontrato) ?></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label text-primary fw-bold">Sucursal</label>
                <div class="form-control bg-light"><?= $formatValue($model->sucursal) ?></div>
            </div>
        </div>
    </div>

    <h4 class="mt-4 mb-4 text-info border-bottom pb-2"><i class="fas fa-calendar-alt me-2"></i> Fechas del Contrato</h4>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'fecha_ini', [
                'options' => ['class' => 'form-group mb-3'],
                'inputOptions' => [
                    'class' => 'form-control rounded-pill',
                    'type' => 'date',
                    'id' => 'fecha-ini-field'
                ]
            ])->label('Fecha de Inicio <span class="text-danger">*</span>') ?>
            <small class="text-muted d-block mt-1">
                <i class="fas fa-info-circle"></i> Modifique esta fecha para actualizar el vencimiento automáticamente
            </small>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label text-primary fw-bold">Fecha de Vencimiento</label>
                <div class="form-control bg-light" id="fecha-ven-display">
                    <?= $model->fecha_ven ? Yii::$app->formatter->asDate($model->fecha_ven) : '<span class="text-muted fst-italic">Se calculará al guardar</span>' ?>
                </div>
                <?= Html::hiddenInput('Contratos[fecha_ven]', $model->fecha_ven, ['id' => 'fecha-ven-hidden']) ?>
                <small class="text-muted d-block mt-1">
                    <i class="fas fa-clock"></i> Se calcula automáticamente: Fecha de Inicio + 1 año
                </small>
            </div>
        </div>
    </div>

    <h4 class="mt-4 mb-4 text-info border-bottom pb-2"><i class="fas fa-dollar-sign me-2"></i> Detalles de Pago</h4>
    <div class="row">
        <div class="col-md-4">
            <div class="mb-3">
                <label class="form-label text-primary fw-bold">Monto</label>
                <div class="form-control bg-light">$ <?= number_format($model->monto, 2) ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label class="form-label text-primary fw-bold">Frecuencia de Pago</label>
                <div class="form-control bg-light"><?= $formatValue($model->frecuencia_pago) ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label class="form-label text-primary fw-bold">Moneda</label>
                <div class="form-control bg-light"><?= $formatValue($model->moneda) ?></div>
            </div>
        </div>
    </div>

    <h4 class="mt-4 mb-4 text-info border-bottom pb-2"><i class="fas fa-clipboard-list me-2"></i> Estado y Documentación</h4>
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label text-primary fw-bold">Estatus</label>
                <div class="form-control bg-light">
                    <?php if ($model->estatus): ?>
                        <?php if ($model->estatus === 'Activo'): ?>
                            <span class="badge badge-success p-2"><?= $model->estatus ?></span>
                        <?php elseif ($model->estatus === 'Anulado'): ?>
                            <span class="badge badge-danger p-2"><?= $model->estatus ?></span>
                        <?php elseif ($model->estatus === 'Creado manual'): ?>
                            <span class="badge badge-dark p-2"><?= $model->estatus ?></span>
                        <?php elseif ($model->estatus === 'suspendido'): ?>
                            <span class="badge badge-warning p-2">Suspendido</span>
                        <?php elseif ($model->estatus === 'Vencido'): ?>
                            <span class="badge badge-secondary p-2"><?= $model->estatus ?></span>
                        <?php else: ?>
                            <span class="badge badge-info p-2"><?= $model->estatus ?></span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="text-muted fst-italic">No especificado</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label text-primary fw-bold">PDF del Contrato</label>
                <div class="form-control bg-light">
                    <?php if ($model->pdf): ?>
                        <a href="<?= $model->pdf ?>" target="_blank" class="text-primary">
                            <i class="fas fa-file-pdf"></i> Ver documento
                        </a>
                    <?php else: ?>
                        <span class="text-muted fst-italic">No disponible</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <h4 class="mt-4 mb-4 text-info border-bottom pb-2"><i class="fas fa-history me-2"></i> Información de Auditoría</h4>
    <div class="row">
        <div class="col-md-4">
            <div class="mb-3">
                <label class="form-label text-primary fw-bold">Fecha de Creación</label>
                <div class="form-control bg-light"><?= $model->created_at ? Yii::$app->formatter->asDatetime($model->created_at) : '<span class="text-muted fst-italic">No disponible</span>' ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label class="form-label text-primary fw-bold">Última Actualización</label>
                <div class="form-control bg-light"><?= $model->updated_at ? Yii::$app->formatter->asDatetime($model->updated_at) : '<span class="text-muted fst-italic">No disponible</span>' ?></div>
            </div>
        </div>
    </div>

    <!-- Información de Anulación (solo visible si está anulado) -->
    <?php if ($model->estatus === 'Anulado'): ?>
        <div class="card mt-4 border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-ban me-2"></i> Información de Anulación</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label text-muted small text-uppercase">Anulado Por</label>
                            <div class="h6"><?= $formatValue($model->anulado_por) ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label text-muted small text-uppercase">Fecha de Anulación</label>
                            <div class="h6"><?= $model->anulado_fecha ? Yii::$app->formatter->asDatetime($model->anulado_fecha) : '<span class="text-muted fst-italic">No especificada</span>' ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label text-muted small text-uppercase">Motivo</label>
                            <div class="h6"><?= $formatValue($model->anulado_motivo) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="form-group mt-4 d-flex justify-content-between">
        <?= Html::a(
            '<i class="fas fa-undo me-2"></i> Volver',
            $model->user_id ? ['index', 'user_id' => $model->user_id] : ['index'],
            ['class' => 'btn btn-secondary rounded-pill px-4 shadow-sm']
        ) ?>
        <?= Html::submitButton('<i class="fas fa-save me-2"></i> Actualizar Contrato', [
            'class' => 'btn btn-success rounded-pill px-4 shadow-sm'
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
// JavaScript to auto-calculate fecha_ven when fecha_ini changes
$js = <<<JS
$(document).ready(function() {
    $('#fecha-ini-field').on('change', function() {
        var fechaIni = $(this).val();
        
        if (fechaIni) {
            // Parse the date
            var parts = fechaIni.split('-');
            var year = parseInt(parts[0]);
            var month = parseInt(parts[1]) - 1; // JavaScript months are 0-based
            var day = parseInt(parts[2]);
            
            // Create date object and add 1 year
            var fecha = new Date(year, month, day);
            fecha.setFullYear(fecha.getFullYear() + 1);
            
            // Format back to YYYY-MM-DD
            var newYear = fecha.getFullYear();
            var newMonth = String(fecha.getMonth() + 1).padStart(2, '0');
            var newDay = String(fecha.getDate()).padStart(2, '0');
            
            var fechaVen = newYear + '-' + newMonth + '-' + newDay;
            
            // Update display and hidden input
            var formattedDate = new Date(fechaVen + 'T12:00:00').toLocaleDateString('es-VE', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            $('#fecha-ven-display').text(formattedDate);
            $('#fecha-ven-hidden').val(fechaVen);
            
            // Show a success message
            var message = 'Fecha de vencimiento actualizada: ' + formattedDate;
            if (typeof toastr !== 'undefined') {
                toastr.success(message);
            } else {
                console.log(message);
            }
        } else {
            $('#fecha-ven-display').html('<span class="text-muted fst-italic">Se calculará al guardar</span>');
            $('#fecha-ven-hidden').val('');
        }
    });
    
    // Trigger change on page load to set initial formatted display
    if ($('#fecha-ini-field').val()) {
        $('#fecha-ini-field').trigger('change');
    }
});
JS;

$this->registerJs($js);
?>

<style>
    /* Additional styling for read-only fields */
    .form-control.bg-light {
        background-color: #f8f9fa !important;
        border: 1px solid #e9ecef;
        color: #495057;
        cursor: default;
    }

    .form-control.bg-light:hover {
        background-color: #f8f9fa !important;
    }

    .badge {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }

    .badge-success {
        background-color: #28a745;
    }

    .badge-danger {
        background-color: #dc3545;
    }

    .badge-warning {
        background-color: #ffc107;
        color: #212529;
    }

    .badge-info {
        background-color: #17a2b8;
    }

    .badge-dark {
        background-color: #343a40;
    }

    .badge-secondary {
        background-color: #6c757d;
    }

    /* Card styling */
    .card {
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .card-header {
        border-radius: 10px 10px 0 0 !important;
        font-weight: 600;
    }

    /* Small text styling */
    small.text-muted {
        font-size: 0.8rem;
    }

    /* Label styling */
    .form-label {
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
    }

    /* Field value styling */
    .h6 {
        font-size: 1rem;
        font-weight: 500;
        margin-bottom: 0;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .form-group {
            margin-bottom: 1rem;
        }

        .card-body {
            padding: 1rem;
        }
    }
</style>