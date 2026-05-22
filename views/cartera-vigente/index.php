<?php
// app/views/cartera-vigente/index.php

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this yii\web\View
 * @var $months array
 * @var $years array
 * @var $selectedMonth string
 * @var $selectedYear string
 */

$this->title = 'Cartera Vigente - SUDEASEG';
$this->params['breadcrumbs'][] = 'Cartera Vigente';

// Initialize variables with default values if not set
if (!isset($months) || empty($months)) {
    $months = [
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
        '12' => 'Diciembre',
    ];
}

if (!isset($years) || empty($years)) {
    $currentYear = date('Y');
    $years = [];
    for ($y = $currentYear - 2; $y <= $currentYear + 2; $y++) {
        $years[$y] = $y;
    }
}

if (!isset($selectedMonth) || empty($selectedMonth)) {
    $selectedMonth = date('m');
}

if (!isset($selectedYear) || empty($selectedYear)) {
    $selectedYear = date('Y');
}
?>

<div class="cartera-vigente-index">
    <div class="container-fluid px-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #1a2a6c, #2c3e50, #1a252f); border-radius: 15px; overflow: hidden;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-white p-3 me-4" style="box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
                                <i class="fas fa-chart-pie fa-2x" style="color: #2c3e50;"></i>
                            </div>
                            <div>
                                <h2 class="mb-0 fw-bold text-white" style="letter-spacing: -0.5px;">
                                    Cartera Vigente
                                </h2>
                                <p class="mb-0 text-white-50 mt-2">
                                    <i class="fas fa-file-alt me-2"></i>Reporte conforme a la Circular SAA-09-0135-2026 de SUDEASEG
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h5 class="mb-0 text-primary">
                            <i class="fas fa-filter me-2"></i>Filtros del Reporte
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info border-0 shadow-sm" role="alert" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-radius: 12px;">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle fa-2x text-primary me-3"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <strong class="text-dark">Instrucciones:</strong>
                                    <p class="mb-0 text-dark">Seleccione el mes y año para generar el reporte de cartera vigente.
                                        El reporte incluye los afiliados con contratos <strong class="text-success">ACTIVOS</strong> al último día del mes seleccionado.</p>
                                </div>
                            </div>
                        </div>

                        <form id="cartera-vigente-form" method="get" action="<?= Url::to(['cartera-vigente/export-excel']) ?>" class="mt-4">
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label text-secondary mb-1" style="font-size: 0.75rem; font-weight: 500;">Mes</label>
                                    <?= Html::dropDownList('month', $selectedMonth, $months, [
                                        'class' => 'form-control form-control-sm',
                                        'id' => 'month-select',
                                        'style' => 'font-size: 0.85rem; height: 34px;'
                                    ]) ?>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-secondary mb-1" style="font-size: 0.75rem; font-weight: 500;">Año</label>
                                    <?= Html::dropDownList('year', $selectedYear, $years, [
                                        'class' => 'form-control form-control-sm',
                                        'id' => 'year-select',
                                        'style' => 'font-size: 0.85rem; height: 34px;'
                                    ]) ?>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-sm w-100" id="export-btn" style="background: linear-gradient(135deg, #1e7e34 0%, #107c10 100%); border: none; color: white; font-size: 0.8rem; font-weight: 500; height: 34px; border-radius: 6px;">
                                        <i class="fas fa-file-excel me-1"></i>Exportar a Excel
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Information Card -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header" style="background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%); border-radius: 12px 12px 0 0;">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-clipboard-list me-2"></i>
                            Estructura del Reporte
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 10%; background: linear-gradient(135deg, #1a2a6c 0%, #2c3e50 100%); color: #ffffff !important; text-align: center !important; vertical-align: middle !important; font-weight: 600 !important; border-bottom: none;">
                                            <i class="fas fa-hashtag me-2"></i>Línea
                                        </th>
                                        <th style="width: 20%; background: linear-gradient(135deg, #1a2a6c 0%, #2c3e50 100%); color: #ffffff !important; text-align: center !important; vertical-align: middle !important; font-weight: 600 !important; border-bottom: none;">
                                            <i class="fas fa-tag me-2"></i>Tipo Tomador
                                        </th>
                                        <th style="width: 30%; background: linear-gradient(135deg, #1a2a6c 0%, #2c3e50 100%); color: #ffffff !important; text-align: center !important; vertical-align: middle !important; font-weight: 600 !important; border-bottom: none;">
                                            <i class="fas fa-info-circle me-2"></i>Descripción
                                        </th>
                                        <th style="width: 15%; background: linear-gradient(135deg, #1a2a6c 0%, #2c3e50 100%); color: #ffffff !important; text-align: center !important; vertical-align: middle !important; font-weight: 600 !important; border-bottom: none;">
                                            <i class="fas fa-shield-alt me-2"></i>Tipo Seguro
                                        </th>
                                        <th style="width: 25%; background: linear-gradient(135deg, #1a2a6c 0%, #2c3e50 100%); color: #ffffff !important; text-align: center !important; vertical-align: middle !important; font-weight: 600 !important; border-bottom: none;">
                                            <i class="fas fa-chart-line me-2"></i>Campos a Reportar
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="background-color: #f8f9fa;">
                                        <td class="text-center align-middle">
                                            <span class="badge bg-success px-3 py-2 rounded-pill fs-6 shadow-sm">1</span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <strong class="text-primary"><i class="fas fa-user me-1"></i> NATURAL</strong>
                                        </td>
                                        <td class="align-middle text-center text-muted">
                                            <i class="fas fa-users me-1 text-secondary"></i>Personas naturales (afiliados individuales)
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="badge bg-info px-3 py-2 rounded-pill shadow-sm">PERSONAS</span>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex flex-column">
                                                <small class="mb-1"><i class="fas fa-user-check text-success me-2"></i><strong class="text-dark">Titulares Vigentes:</strong> <span class="text-muted">Afiliados con contrato ACTIVO</span></small>
                                                <small><i class="fas fa-user-friends text-info me-2"></i><strong class="text-dark">Dependientes Vigentes:</strong> <span class="text-muted">Beneficiarios con contrato ACTIVO</span></small>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr style="background-color: #ffffff;">
                                        <td class="text-center align-middle">
                                            <span class="badge bg-success px-3 py-2 rounded-pill fs-6 shadow-sm">2</span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <strong class="text-primary"><i class="fas fa-building me-1"></i> JURÍDICO</strong>
                                        </td>
                                        <td class="align-middle text-center text-muted">
                                            <i class="fas fa-briefcase me-1 text-secondary"></i>Personas jurídicas (afiliados corporativos)
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="badge bg-info px-3 py-2 rounded-pill shadow-sm">PERSONAS</span>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex flex-column">
                                                <small class="mb-1"><i class="fas fa-building text-success me-2"></i><strong class="text-dark">Titulares Vigentes:</strong> <span class="text-muted">Empresas con al menos un Afiliados con contrato ACTIVO</span></small>
                                                <small><i class="fas fa-users text-info me-2"></i><strong class="text-dark">Dependientes Vigentes:</strong> <span class="text-muted">Empleados/beneficiarios con contrato ACTIVO de un Jurídico</span></small>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Information Cards Row - PROFESSIONAL SIZES -->
        <div class="row mt-4 g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); border-radius: 12px; transition: all 0.3s ease;">
                    <div class="card-body text-white p-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-check-circle fa-2x me-3 text-white"></i>
                            <h5 class="mb-0 fw-bold" style="font-size: 1.7rem;">Contratos Activos</h5>
                        </div>
                        <p class="mb-0 text-white-80" style="font-size: 1.3rem; line-height: 1.4;">
                            Se consideran solo contratos con estatus <strong class="text-warning">'Activo'</strong> y vigentes en la fecha de corte.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #17a2b8 0%, #0f7a8a 100%); border-radius: 12px; transition: all 0.3s ease;">
                    <div class="card-body text-white p-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-users fa-2x me-3 text-white"></i>
                            <h5 class="mb-0 fw-bold" style="font-size: 1.7rem;">Dependientes</h5>
                        </div>
                        <p class="mb-0 text-white-80" style="font-size: 1.3rem; line-height: 1.4;">
                            Afiliados con la opción <strong class="text-warning">"tiene_contratante_diferente = true"</strong>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); border-radius: 12px; transition: all 0.3s ease;">
                    <div class="card-body text-white p-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-calendar-alt fa-2x me-3 text-white"></i>
                            <h5 class="mb-0 fw-bold" style="font-size: 1.7rem;">Fecha de Corte</h5>
                        </div>
                        <p class="mb-0 text-white-80" style="font-size: 1.3rem; line-height: 1.4;">
                            Último día del mes seleccionado como período de corte.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Base Legal Card -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 12px;">
                    <div class="card-body p-4">
                        <h6 class="mb-2 fw-bold text-dark" style="font-size: 1.5rem;">
                            <i class="fas fa-balance-scale me-2 text-warning"></i>Base Legal
                        </h6>
                        <p class="mb-0 text-muted" style="font-size: 1.2rem;">
                            Circular SAA-09-0135-2026 de fecha abril 2026, emitida por la Superintendencia de la Actividad Aseguradora (SUDEASEG). RIF Fijo: <strong class="text-primary">J-506549220</strong> según lo establecido en la circular.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('cartera-vigente-form');
    var exportBtn = document.getElementById('export-btn');
    
    if (form && exportBtn) {
        var originalText = exportBtn.innerHTML;
        
        form.addEventListener('submit', function() {
            exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generando...';
            exportBtn.disabled = true;
            
            setTimeout(function() {
                exportBtn.innerHTML = originalText;
                exportBtn.disabled = false;
            }, 10000);
        });
    }
});
JS;

$this->registerJs($js);
?>

<style>
    /* FORCE white text on table headers */
    .table thead th {
        color: #ffffff !important;
        background: linear-gradient(135deg, #1a2a6c 0%, #2c3e50 100%) !important;
        text-align: center !important;
        vertical-align: middle !important;
        font-weight: 600 !important;
        border-bottom: none !important;
        padding: 15px 10px !important;
    }

    /* Ensure icons in headers are white */
    .table thead th i {
        color: #ffffff !important;
    }

    /* Table cell styling */
    .table td {
        vertical-align: middle !important;
        padding: 12px 10px !important;
    }

    /* Custom styles for better visual appeal */
    .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    .form-select:focus,
    .form-control:focus {
        border-color: #0078d4;
        box-shadow: 0 0 0 0.2rem rgba(0, 120, 212, 0.25);
    }

    /* Text color utilities */
    .text-white-80 {
        color: rgba(255, 255, 255, 0.8);
    }

    /* Badge styling */
    .badge.bg-success,
    .badge.bg-info {
        font-weight: 500;
        letter-spacing: 0.3px;
    }

    /* Table header icon spacing */
    .table thead th i {
        margin-right: 8px;
        font-size: 0.9rem;
    }

    /* Center text in specific columns */
    .table td.text-center {
        text-align: center !important;
    }

    /* Alternating row colors */
    .table tbody tr:first-child {
        background-color: #f8f9fa;
    }

    .table tbody tr:last-child {
        background-color: #ffffff;
    }
</style>