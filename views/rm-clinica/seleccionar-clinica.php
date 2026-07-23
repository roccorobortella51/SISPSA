<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $clinicas app\models\RmClinica[]|null */

// Initialize variables with defaults if not set
if (!isset($clinicas) || $clinicas === null) {
    $clinicas = [];
}

// Get the current clinic ID from the search model if available
$selectedClinicId = Yii::$app->request->get('id');

$this->title = 'Seleccionar Clínica - Intermediarios';
$this->params['breadcrumbs'][] = ['label' => 'Clínicas', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Seleccionar Clínica';
?>

<div class="rm-clinica-seleccionar">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h4 class="text-primary mb-0" style="font-weight: 600; font-size: 2rem;">
                <i class="fas fa-handshake mr-2"></i> Seleccionar Clínica
            </h4>
            <p class="text-muted mb-0" style="font-size: 1.1rem;">
                <i class="fas fa-info-circle mr-1"></i>
                Seleccione una clínica para visualizar sus intermediarios asociados
            </p>
        </div>
        <div>
            <span class="badge border px-3 py-2" style="border-radius: 4px; font-weight: 500; background-color: #ffffff; color: #212529; border-color: #ced4da; font-size: 1.1rem; padding: 0.6rem 1.2rem;">
                <i class="fas fa-building mr-1 text-primary"></i>
                <?= count($clinicas) ?> clínicas disponibles
            </span>
        </div>
    </div>

    <!-- Main Selection Card -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 8px;">
        <div class="card-body p-4">
            <?php $form = ActiveForm::begin([
                'action' => ['intermediarios'],
                'method' => 'get',
                'options' => [
                    'id' => 'seleccionar-clinica-form',
                    'class' => 'needs-validation',
                ],
            ]); ?>

            <div class="row align-items-end">
                <div class="col-lg-8 col-md-7 col-sm-12">
                    <div class="form-group mb-0">
                        <label for="clinica-select" class="font-weight-bold text-secondary small text-uppercase mb-2" style="letter-spacing: 0.5px; font-size: 0.9rem;">
                            <i class="fas fa-clinic-medical text-primary mr-1"></i> Clínica
                        </label>
                        <select id="clinica-select" name="id" class="form-control form-control-lg" required style="border-radius: 6px; font-size: 1.4rem; height: 70px; border-color: #ced4da; padding: 0 1.5rem;">
                            <option value="" style="font-size: 1.4rem;">-- Seleccionar Clínica --</option>
                            <?php if (!empty($clinicas)): ?>
                                <?php foreach ($clinicas as $clinica): ?>
                                    <option value="<?= $clinica->id ?>" <?= ($selectedClinicId == $clinica->id) ? 'selected' : '' ?> style="font-size: 1.4rem;">
                                        <?= Html::encode($clinica->nombre) ?>
                                        <?php if ($clinica->codigo_clinica): ?>
                                            (<?= Html::encode($clinica->codigo_clinica) ?>)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="col-lg-4 col-md-5 col-sm-12 mt-3 mt-md-0">
                    <div class="d-flex gap-2">
                        <?= Html::submitButton(
                            '<i class="fas fa-chevron-right mr-2"></i> Ver Intermediarios',
                            [
                                'class' => 'btn btn-primary btn-lg w-100',
                                'id' => 'btn-ver-intermediarios',
                                'disabled' => empty($clinicas),
                                'style' => 'border-radius: 6px; font-weight: 600; height: 70px; font-size: 1.4rem;',
                            ]
                        ) ?>
                    </div>
                </div>
            </div>

            <?php ActiveForm::end(); ?>

            <?php if (empty($clinicas)): ?>
                <div class="alert alert-warning mt-3 mb-0" style="border-radius: 6px; font-size: 1.2rem; padding: 1.25rem;">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    No hay clínicas disponibles en el sistema. Por favor, cree una clínica primero.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Access Section -->
    <?php if (!empty($clinicas)): ?>
        <div class="mb-4">
            <div class="d-flex align-items-center mb-3">
                <div class="border-bottom flex-grow-1 mr-3"></div>
                <span class="text-muted small font-weight-bold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.9rem;">
                    <i class="fas fa-clock mr-1"></i> Acceso Rápido
                </span>
                <div class="border-bottom flex-grow-1 ml-3"></div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <?php
                $quickClinics = array_slice($clinicas, 0, 6);
                foreach ($quickClinics as $clinica):
                ?>
                    <?= Html::a(
                        '<span class="d-flex align-items-center" style="font-size: 1.1rem;">
                            <span class="clinic-dot bg-primary mr-2" style="width: 12px; height: 12px; border-radius: 50%; display: inline-block;"></span>
                            <span>' . Html::encode($clinica->nombre) . '</span>
                            ' . ($clinica->codigo_clinica ? '<small class="text-muted ml-1" style="font-size: 0.9rem;">(' . Html::encode($clinica->codigo_clinica) . ')</small>' : '') . '
                        </span>',
                        ['intermediarios', 'id' => $clinica->id],
                        [
                            'class' => 'btn btn-outline-primary btn-sm px-3 py-2',
                            'style' => 'border-radius: 6px; font-weight: 500; font-size: 1.1rem; padding: 0.6rem 1.2rem;',
                            'title' => 'Ver intermediarios de ' . Html::encode($clinica->nombre),
                        ]
                    ) ?>
                <?php endforeach; ?>

                <?php if (count($clinicas) > 6): ?>
                    <span class="badge border px-3 py-2 align-self-center" style="border-radius: 6px; font-weight: 500; background-color: #ffffff; color: #212529; border-color: #ced4da; font-size: 1.1rem; padding: 0.6rem 1.2rem;">
                        <i class="fas fa-ellipsis-h mr-1"></i> +<?= count($clinicas) - 6 ?> más
                    </span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Clinics List -->
    <?php if (!empty($clinicas)): ?>
        <div class="card border-0 shadow-sm" style="border-radius: 8px;">
            <div class="card-header bg-primary text-white border-bottom px-4 py-3" style="border-radius: 8px 8px 0 0;">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 font-weight-bold text-white" style="font-size: 1.2rem;">
                        <i class="fas fa-list mr-2"></i> Listado de Clínicas
                    </h6>
                    <span class="badge bg-white text-primary px-3 py-2" style="border-radius: 4px; font-weight: 600; font-size: 1.1rem; padding: 0.5rem 1.2rem;">
                        <i class="fas fa-building mr-1"></i> <?= count($clinicas) ?>
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 1.3rem;">
                        <thead class="bg-primary">
                            <tr>
                                <th style="width: 50px; color: #ffffff !important; font-size: 1.1rem; padding: 1rem 1.2rem; text-align: center !important;">#</th>
                                <th style="color: #ffffff !important; font-size: 1.1rem; padding: 1rem 1.2rem; text-align: left !important;">Nombre de la Clínica</th>
                                <th style="color: #ffffff !important; font-size: 1.1rem; padding: 1rem 1.2rem; text-align: center !important;">Código</th>
                                <th style="color: #ffffff !important; font-size: 1.1rem; padding: 1rem 1.2rem; text-align: center !important;">Contacto</th>
                                <th style="width: 200px; color: #ffffff !important; font-size: 1.1rem; padding: 1rem 1.2rem; text-align: center !important;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $counter = 1; ?>
                            <?php foreach ($clinicas as $clinica): ?>
                                <tr>
                                    <td class="text-muted font-weight-bold text-center" style="font-size: 1.3rem; padding: 1rem 1.2rem; text-align: center !important;"><?= $counter++ ?></td>
                                    <td style="font-size: 1.3rem; padding: 1rem 1.2rem; text-align: left !important;">
                                        <div class="d-flex align-items-center">
                                            <span class="clinic-dot bg-primary mr-2" style="width: 14px; height: 14px; border-radius: 50%; display: inline-block; flex-shrink: 0;"></span>
                                            <span class="font-weight-bold" style="font-size: 1.3rem;"><?= Html::encode($clinica->nombre) ?></span>
                                        </div>
                                    </td>
                                    <td style="font-size: 1.3rem; padding: 1rem 1.2rem; text-align: center !important;">
                                        <?php if ($clinica->codigo_clinica): ?>
                                            <span class="badge border px-2 py-1" style="border-radius: 4px; font-weight: 500; background-color: #ffffff; color: #212529; border-color: #ced4da; font-size: 1.1rem; padding: 0.5rem 1rem;">
                                                <?= Html::encode($clinica->codigo_clinica) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted" style="font-size: 1.3rem;">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size: 1.3rem; padding: 1rem 1.2rem; text-align: center !important;">
                                        <?php if ($clinica->telefono): ?>
                                            <div style="font-size: 1.1rem;">
                                                <i class="fas fa-phone text-muted mr-1" style="width: 16px; font-size: 1.1rem;"></i>
                                                <?= Html::encode($clinica->telefono) ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($clinica->correo): ?>
                                            <div class="text-muted" style="font-size: 1.1rem;">
                                                <i class="fas fa-envelope text-muted mr-1" style="width: 16px; font-size: 1.1rem;"></i>
                                                <?= Html::encode($clinica->correo) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size: 1.3rem; padding: 1rem 1.2rem; text-align: center !important;">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <?= Html::a(
                                                '<i class="fas fa-users mr-1"></i> Ver',
                                                ['intermediarios', 'id' => $clinica->id],
                                                [
                                                    'class' => 'btn btn-primary btn-sm',
                                                    'style' => 'border-radius: 4px; font-weight: 500; font-size: 1.1rem; padding: 0.5rem 1.2rem;',
                                                    'title' => 'Ver intermediarios de ' . Html::encode($clinica->nombre),
                                                ]
                                            ) ?>
                                            <?= Html::a(
                                                '<i class="fas fa-file-excel"></i>',
                                                ['exportar-intermediarios-excel', 'id' => $clinica->id],
                                                [
                                                    'class' => 'btn btn-success btn-sm',
                                                    'style' => 'border-radius: 4px; font-size: 1.1rem; padding: 0.5rem 1rem;',
                                                    'title' => 'Exportar a Excel',
                                                    'target' => '_blank',
                                                ]
                                            ) ?>
                                            <?= Html::a(
                                                '<i class="fas fa-file-pdf"></i>',
                                                ['exportar-intermediarios-pdf', 'id' => $clinica->id],
                                                [
                                                    'class' => 'btn btn-danger btn-sm',
                                                    'style' => 'border-radius: 4px; font-size: 1.1rem; padding: 0.5rem 1rem;',
                                                    'title' => 'Exportar a PDF',
                                                    'target' => '_blank',
                                                ]
                                            ) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$this->registerJs("
    // Enable/Disable the submit button based on selection
    $('#clinica-select').on('change', function() {
        var selectedValue = $(this).val();
        if (selectedValue && selectedValue !== '') {
            $('#btn-ver-intermediarios').prop('disabled', false);
        } else {
            $('#btn-ver-intermediarios').prop('disabled', true);
        }
    });

    // If there's a preselected value, enable the button
    $(document).ready(function() {
        var initialValue = $('#clinica-select').val();
        if (initialValue && initialValue !== '') {
            $('#btn-ver-intermediarios').prop('disabled', false);
        }
    });

    // Allow Enter key to submit the form
    $('#clinica-select').on('keydown', function(e) {
        if (e.key === 'Enter' && $(this).val() && $(this).val() !== '') {
            $('#seleccionar-clinica-form').submit();
        }
    });

    // Make table rows clickable
    $('.table tbody tr').on('click', function(e) {
        // Don't trigger if clicking on a button or link
        if ($(e.target).closest('.btn, a').length) {
            return;
        }
        var url = $(this).find('.btn-primary').attr('href');
        if (url) {
            window.location.href = url;
        }
    });
");

$this->registerCss("
    .rm-clinica-seleccionar .card {
        border-radius: 8px;
        border: none;
    }
    .rm-clinica-seleccionar .form-control-lg {
        border-radius: 6px;
        font-size: 1.4rem;
        height: 70px;
        border-color: #ced4da;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .rm-clinica-seleccionar .form-control-lg:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    .rm-clinica-seleccionar .btn-lg {
        border-radius: 6px;
        font-weight: 600;
        height: 70px;
        font-size: 1.4rem;
        transition: all 0.15s ease-in-out;
    }
    .rm-clinica-seleccionar .btn-lg:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
    }
    .rm-clinica-seleccionar .btn-lg:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    .rm-clinica-seleccionar .gap-2 {
        gap: 0.5rem;
    }
    .rm-clinica-seleccionar .clinic-dot {
        flex-shrink: 0;
    }
    /* Force white text on table headers with !important */
    .rm-clinica-seleccionar .table thead.bg-primary th {
        color: #ffffff !important;
        font-size: 1.1rem !important;
        padding: 1rem 1.2rem !important;
    }
    .rm-clinica-seleccionar .table th {
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
        padding: 1rem 1.2rem;
        font-size: 1.1rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .rm-clinica-seleccionar .table td {
        padding: 1rem 1.2rem;
        vertical-align: middle;
        border-top: 1px solid #e9ecef;
        font-size: 1.3rem;
    }
    .rm-clinica-seleccionar .table-hover tbody tr {
        cursor: pointer;
        transition: background-color 0.15s ease-in-out;
    }
    .rm-clinica-seleccionar .table-hover tbody tr:hover {
        background-color: #f0f7ff !important;
    }
    .rm-clinica-seleccionar .btn-sm {
        border-radius: 4px;
        font-weight: 500;
        font-size: 1.1rem;
        padding: 0.5rem 1.2rem;
    }
    .rm-clinica-seleccionar .badge {
        font-weight: 500;
        border-radius: 4px;
    }
    .rm-clinica-seleccionar .border-bottom {
        border-bottom: 1px solid #e9ecef !important;
    }
    .rm-clinica-seleccionar .shadow-sm {
        box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04) !important;
    }
    .rm-clinica-seleccionar .text-uppercase {
        letter-spacing: 0.5px;
    }
    @media (max-width: 768px) {
        .rm-clinica-seleccionar .card-body {
            padding: 1.25rem !important;
        }
        .rm-clinica-seleccionar .table {
            font-size: 1.1rem;
        }
        .rm-clinica-seleccionar .table th,
        .rm-clinica-seleccionar .table td {
            padding: 0.75rem;
        }
        .rm-clinica-seleccionar .gap-2 {
            gap: 0.25rem;
        }
        .rm-clinica-seleccionar .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.95rem;
        }
        .rm-clinica-seleccionar .form-control-lg {
            font-size: 1.1rem;
            height: 60px;
        }
        .rm-clinica-seleccionar .btn-lg {
            font-size: 1.1rem;
            height: 60px;
        }
    }
");
?>