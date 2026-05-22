<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $resultados array */
/* @var $corporativo app\models\Corporativo */
/* @var $model app\models\MasivoAfiliadosForm */

$this->title = 'Resumen de Carga Masiva';
$this->params['breadcrumbs'][] = ['label' => 'Carga Masiva', 'url' => ['carga-masiva-afiliados']];
$this->params['breadcrumbs'][] = $this->title;

$resultados = isset($resultados) && is_array($resultados) ? $resultados : [];
if (!isset($model)) {
    $model = null;
}
$errorCount = isset($resultados['errors']) && is_array($resultados['errors']) ? count($resultados['errors']) : 0;
$successCount = isset($resultados['successCount']) ? (int) $resultados['successCount'] : 0;
$totalProcessed = $successCount + $errorCount;
$successPercentage = $totalProcessed > 0 ? round(($successCount / $totalProcessed) * 100, 2) : 0;
$errorPercentage = $totalProcessed > 0 ? round(($errorCount / $totalProcessed) * 100, 2) : 0;

// Ensure corporativo is defined when not passed explicitly
if (!isset($corporativo)) {
    $corporativo = isset($model) && isset($model->corporativo) ? $model->corporativo : null;
}

// Get success details if they exist
$successDetails = isset($resultados['successDetails']) && is_array($resultados['successDetails']) ? $resultados['successDetails'] : [];

?>

<div class="carga-masiva-resumen">
    <div class="container-fluid px-4">

        <!-- Header Section with Title -->
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <div>
                <h1 class="display-5 fw-bold text-primary mb-2">
                    <i class="fas fa-chart-line me-3"></i><?= Html::encode($this->title) ?>
                </h1>
                <p class="text-muted lead">Análisis detallado del proceso de carga masiva de afiliados</p>
            </div>
            <div class="text-end">
                <span class="badge bg-secondary fs-6 p-3">
                    <i class="fas fa-calendar-alt me-2"></i>
                    <?= date('d/m/Y H:i:s') ?>
                </span>
            </div>
        </div>

        <!-- Summary Cards Section -->
        <div class="row mb-5">
            <!-- Total Processed Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                    <i class="fas fa-database me-1"></i> Total Procesados
                                </div>
                                <div class="h5 mb-0 fw-bold text-gray-800"><?= number_format($totalProcessed) ?></div>
                                <div class="small text-muted mt-2">Registros analizados</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-file-csv fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Successful Uploads Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                    <i class="fas fa-check-circle me-1"></i> Cargados Exitosamente
                                </div>
                                <div class="h5 mb-0 fw-bold text-gray-800"><?= number_format($successCount) ?></div>
                                <div class="small text-success mt-2">
                                    <i class="fas fa-arrow-up me-1"></i> <?= $successPercentage ?>% del total
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Errors Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-danger text-uppercase mb-1">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Errores Encontrados
                                </div>
                                <div class="h5 mb-0 fw-bold text-gray-800"><?= number_format($errorCount) ?></div>
                                <div class="small text-danger mt-2">
                                    <i class="fas fa-arrow-down me-1"></i> <?= $errorPercentage ?>% del total
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success Rate Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-info text-uppercase mb-1">
                                    <i class="fas fa-chart-pie me-1"></i> Tasa de Éxito
                                </div>
                                <div class="h5 mb-0 fw-bold text-gray-800"><?= $successPercentage ?>%</div>
                                <div class="progress mt-2" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: <?= $successPercentage ?>%"></div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-percent fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Corporate Information Section -->
        <div class="card shadow mb-4">
            <div class="card-header bg-gradient-primary text-white py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-building me-2"></i> Información del Proceso
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 180px;"><i class="fas fa-hospital me-2 text-primary"></i> Corporativo Destino:</th>
                                <td class="fw-bold"><?= Html::encode($corporativo ? $corporativo->nombre : 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-calendar-alt me-2 text-primary"></i> Fecha de Inicio:</th>
                                <td><?= $model && $model->fecha_ini ? Yii::$app->formatter->asDate($model->fecha_ini, 'dd/MM/yyyy') : 'N/A' ?></td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-calendar-check me-2 text-primary"></i> Fecha de Vencimiento:</th>
                                <td><?= $model && $model->fecha_ven ? Yii::$app->formatter->asDate($model->fecha_ven, 'dd/MM/yyyy') : 'N/A' ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 180px;"><i class="fas fa-file-csv me-2 text-primary"></i> Archivo Procesado:</th>
                                <td class="text-truncate"><?= $model && $model->masivoFile ? Html::encode($model->masivoFile->name) : 'N/A' ?></td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-clock me-2 text-primary"></i> Hora de Procesamiento:</th>
                                <td><?= date('H:i:s') ?></td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-chart-bar me-2 text-primary"></i> Registros por Segundo:</th>
                                <td><?= $totalProcessed > 0 ? number_format($totalProcessed / max(1, (time() - strtotime(date('Y-m-d H:i:s')) + 3600)), 2) : 'N/A' ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Successfully Uploaded Affiliates Section -->
        <?php if (!empty($successDetails)): ?>
            <div class="card shadow mb-4">
                <div class="card-header bg-gradient-success text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-users me-2"></i> Afiliados Cargados Exitosamente
                        </h5>
                        <span class="badge bg-light text-success fs-6">
                            <i class="fas fa-user-plus me-1"></i> <?= count($successDetails) ?> nuevos afiliados
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle" id="successTable">
                            <thead class="table-success">
                                <tr>
                                    <th class="text-center" style="width: 50px;">#</th>
                                    <th><i class="fas fa-id-card me-1"></i> ID Afiliado</th>
                                    <th><i class="fas fa-id-card me-1"></i> Tipo/Cédula</th>
                                    <th><i class="fas fa-user me-1"></i> Nombre Completo</th>
                                    <th><i class="fas fa-envelope me-1"></i> Email</th>
                                    <th><i class="fas fa-file-contract me-1"></i> Contrato</th>
                                    <th><i class="fas fa-calendar-week me-1"></i> Fecha Registro</th>
                                    <th><i class="fas fa-chart-line me-1"></i> Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($successDetails as $index => $detail): ?>
                                    <tr>
                                        <td class="text-center fw-bold"><?= $index + 1 ?></td>
                                        <td>
                                            <span class="badge bg-primary rounded-pill fs-6">
                                                <i class="fas fa-hashtag me-1"></i> <?= Html::encode($detail['user_datos_id'] ?? 'N/A') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold"><?= Html::encode($detail['tipo_cedula'] ?? '') ?>-</span>
                                            <span class="font-monospace"><?= Html::encode($detail['cedula'] ?? 'N/A') ?></span>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?= Html::encode($detail['nombres'] ?? '') ?> <?= Html::encode($detail['apellidos'] ?? '') ?></div>
                                            <small class="text-muted">ID User: <?= Html::encode($detail['user_login_id'] ?? 'N/A') ?></small>
                                        </td>
                                        <td>
                                            <a href="mailto:<?= Html::encode($detail['email'] ?? '') ?>" class="text-decoration-none">
                                                <i class="fas fa-envelope me-1 text-muted"></i>
                                                <?= Html::encode($detail['email'] ?? 'N/A') ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-dark">
                                                <i class="fas fa-file-signature me-1"></i>
                                                <?= Html::encode($detail['nrocontrato'] ?? 'N/A') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <i class="fas fa-calendar-alt me-1 text-muted"></i>
                                            <?= isset($detail['created_at']) ? Yii::$app->formatter->asDate($detail['created_at'], 'dd/MM/yyyy HH:mm') : 'N/A' ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i> Activo
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary Stats for Success Section -->
                    <div class="row mt-4 pt-3 border-top">
                        <div class="col-md-4">
                            <div class="alert alert-success mb-0">
                                <i class="fas fa-chart-simple me-2"></i>
                                <strong>Total Afiliados:</strong> <?= count($successDetails) ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-file-contract me-2"></i>
                                <strong>Contratos Generados:</strong> <?= count($successDetails) ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-coins me-2"></i>
                                <strong>Cuotas Generadas:</strong> <?= count($successDetails) * 12 ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php if ($successCount > 0): ?>
                <div class="card shadow mb-4">
                    <div class="card-header bg-gradient-success text-white py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-check-circle me-2"></i> Carga Exitosa
                        </h5>
                    </div>
                    <div class="card-body text-center py-5">
                        <i class="fas fa-check-circle text-success" style="font-size: 64px;"></i>
                        <h4 class="mt-3"><?= $successCount ?> afiliados procesados exitosamente</h4>
                        <p class="text-muted">Todos los registros fueron validados y creados correctamente en el sistema.</p>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Detailed Errors Section -->
        <?php if ($errorCount > 0): ?>
            <div class="card shadow mb-4">
                <div class="card-header bg-gradient-danger text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-bug me-2"></i> Detalle de Errores
                        </h5>
                        <span class="badge bg-light text-danger fs-6">
                            <i class="fas fa-exclamation-circle me-1"></i> <?= $errorCount ?> errores encontrados
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Los siguientes registros no pudieron ser procesados. Revise los errores y corrija el archivo CSV para reintentar la carga.
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="errorTable">
                            <thead class="table-danger">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th style="width: 80px;">Línea</th>
                                    <th>Cédula / Identificador</th>
                                    <th>Descripción del Error</th>
                                    <th>Acción Recomendada</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resultados['errors'] as $index => $error): ?>
                                    <?php
                                    // Extract error details for better display
                                    $errorMessage = $error;
                                    $lineNumber = 'N/A';
                                    $cedulaInfo = 'N/A';

                                    if (preg_match('/Línea (\d+)/', $error, $matches)) {
                                        $lineNumber = $matches[1];
                                    }
                                    if (preg_match('/Cédula: ([^\s]+)/', $error, $matches)) {
                                        $cedulaInfo = $matches[1];
                                    }
                                    ?>
                                    <tr>
                                        <td class="text-center fw-bold"><?= $index + 1 ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-code-branch me-1"></i> <?= $lineNumber ?>
                                            </span>
                                        </td>
                                        <td>
                                            <i class="fas fa-id-card me-1 text-muted"></i>
                                            <?= Html::encode($cedulaInfo) ?>
                                        </td>
                                        <td>
                                            <i class="fas fa-times-circle text-danger me-2"></i>
                                            <?= Html::encode($errorMessage) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-edit me-1"></i> Corregir y reintentar
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Error Summary Stats -->
                    <div class="row mt-4 pt-3 border-top">
                        <div class="col-md-6">
                            <div class="alert alert-danger mb-0">
                                <i class="fas fa-chart-pie me-2"></i>
                                <strong>Tasa de Error:</strong> <?= $errorPercentage ?>%
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-secondary mb-0">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>Sugerencia:</strong> Descargue la plantilla de ejemplo y valide los datos antes de cargar.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Action Buttons Section -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <?php if ($successCount > 0): ?>
                        <div class="btn-group">
                            <button onclick="window.print()" class="btn btn-secondary">
                                <i class="fas fa-print me-2"></i> Imprimir Reporte
                            </button>
                            <button onclick="exportToExcel()" class="btn btn-success">
                                <i class="fas fa-file-excel me-2"></i> Exportar a Excel
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="btn-group ms-auto">
                        <?= Html::a(
                            '<i class="fas fa-upload me-2"></i> Realizar Otra Carga',
                            ['carga-masiva-afiliados'],
                            ['class' => 'btn btn-primary btn-lg px-4']
                        ) ?>
                        <?= Html::a(
                            '<i class="fas fa-home me-2"></i> Volver al Inicio',
                            Yii::$app->homeUrl,
                            ['class' => 'btn btn-outline-secondary btn-lg px-4']
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS Styles -->
<style>
    .border-left-primary {
        border-left: 4px solid #4e73df !important;
    }

    .border-left-success {
        border-left: 4px solid #1cc88a !important;
    }

    .border-left-danger {
        border-left: 4px solid #e74a3b !important;
    }

    .border-left-info {
        border-left: 4px solid #36b9cc !important;
    }

    .bg-gradient-primary {
        background: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
    }

    .bg-gradient-success {
        background: linear-gradient(180deg, #1cc88a 10%, #13855c 100%);
    }

    .bg-gradient-danger {
        background: linear-gradient(180deg, #e74a3b 10%, #be2617 100%);
    }

    .table-responsive {
        border-radius: 10px;
        overflow-x: auto;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(78, 115, 223, 0.05);
    }

    @media print {

        .btn-group,
        .ms-auto,
        .progress,
        .btn {
            display: none !important;
        }

        .card {
            border: 1px solid #ddd !important;
            break-inside: avoid;
        }

        body {
            padding: 20px;
        }
    }
</style>

<!-- JavaScript for Export Functionality -->
<script>
    function exportToExcel() {
        // Check if there's data to export
        <?php if (!empty($successDetails)): ?>
            const successTable = document.getElementById('successTable');
            if (successTable) {
                let html = '<html><head><meta charset="UTF-8"><title>Resumen Carga Masiva</title></head><body>';

                // Add title
                html += '<h1>Resumen de Carga Masiva de Afiliados</h1>';
                html += '<h3>Fecha: <?= date('d/m/Y H:i:s') ?></h3>';
                html += '<h3>Corporativo: <?= Html::encode($corporativo ? $corporativo->nombre : 'N/A') ?></h3>';
                html += '<hr>';

                // Copy the table
                html += successTable.outerHTML;

                // Add summary
                html += '<br><hr>';
                html += '<h4>Resumen Estadístico</h4>';
                html += '<table border="1" cellpadding="5">';
                html += '<tr><th>Total Procesados</th><td><?= $totalProcessed ?></td></tr>';
                html += '<tr><th>Afiliados Cargados</th><td><?= $successCount ?></td></tr>';
                html += '<tr><th>Errores Encontrados</th><td><?= $errorCount ?></td></tr>';
                html += '<tr><th>Cuotas Generadas</th><td><?= $successCount * 12 ?></td></tr>';
                html += '</table>';

                html += '</body></html>';

                const blob = new Blob([html], {
                    type: 'application/vnd.ms-excel'
                });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'resumen_carga_masiva_<?= date('Ymd_His') ?>.xls';
                link.click();
                URL.revokeObjectURL(link.href);
            }
        <?php else: ?>
            alert('No hay datos para exportar.');
        <?php endif; ?>
    }

    // Add data table enhancements (search, sort) - optional
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (!empty($successDetails)): ?>
            // Add search functionality to success table
            const searchInput = document.createElement('div');
            searchInput.className = 'mb-3';
            searchInput.innerHTML = `
        <div class="input-group" style="max-width: 300px;">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" id="tableSearch" class="form-control" placeholder="Buscar afiliado...">
        </div>
    `;
            const successCard = document.querySelector('#successTable').closest('.card-body');
            if (successCard && successCard.firstChild) {
                successCard.insertBefore(searchInput, successCard.firstChild.nextSibling);
            }

            const searchField = document.getElementById('tableSearch');
            if (searchField) {
                searchField.addEventListener('keyup', function() {
                    const searchTerm = this.value.toLowerCase();
                    const table = document.getElementById('successTable');
                    const rows = table.getElementsByTagName('tr');

                    for (let i = 1; i < rows.length; i++) {
                        const row = rows[i];
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchTerm) ? '' : 'none';
                    }
                });
            }
        <?php endif; ?>
    });
</script>

<!-- Optional: Add Font Awesome if not already loaded -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">