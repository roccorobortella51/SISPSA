<?php
// app/views/reporte-atenciones/_report_results.php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var array $reportData */
/** @var app\models\SisSiniestroReporteSearch $searchModel */

// Register CSS for tooltip styling (optional, can be removed if not using Bootstrap tooltips)
$this->registerCss(
    <<<CSS
/* Optional: Native tooltip styling cannot be customized much, 
   but we can keep this for if Bootstrap is loaded later */
.tooltip-inner {
    background-color: #2c3e50;
    color: white;
    font-size: 0.875rem;
    max-width: 300px;
    padding: 8px 12px;
    border-radius: 4px;
}
CSS
);
?>

<div class="report-results-container">
    <!-- Report Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="border-left: 4px solid #0078d4;">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h2 class="mb-2 fw-bold text-primary">
                                <i class="fas fa-chart-bar me-2"></i>Reporte de Atenciones por Clínica
                            </h2>
                            <p class="text-muted mb-0">
                                Período: <strong><?= Yii::$app->formatter->asDate($reportData['summary']['date_range']['from']) ?></strong>
                                al <strong><?= Yii::$app->formatter->asDate($reportData['summary']['date_range']['to']) ?></strong>
                                | Clínicas incluidas: <strong><?= $reportData['summary']['total_clinics'] ?></strong>
                            </p>
                        </div>
                        <div class="col-lg-4 text-lg-end">
                            <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                                <a href="#" id="btn-export-excel" class="btn btn-success">
                                    <i class="fas fa-file-excel me-2"></i>Excel
                                </a>
                                <a href="#" id="btn-export-pdf" class="btn btn-danger">
                                    <i class="fas fa-file-pdf me-2"></i>PDF
                                </a>
                                <button class="btn btn-primary" onclick="window.print()">
                                    <i class="fas fa-print me-2"></i>Imprimir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0078d4;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                            style="width: 50px; height: 50px; background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);">
                            <i class="fas fa-hospital text-white fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Clínicas Activas</h6>
                            <h3 class="fw-bold mb-0"><?= $reportData['summary']['total_clinics'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #107c10;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                            style="width: 50px; height: 50px; background: linear-gradient(135deg, #107c10 0%, #0e6a0e 100%);">
                            <i class="fas fa-stethoscope text-white fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Total Atenciones</h6>
                            <h3 class="fw-bold mb-0"><?= number_format($reportData['summary']['total_attentions']) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ff8c00;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                            style="width: 50px; height: 50px; background: linear-gradient(135deg, #ff8c00 0%, #e67e00 100%);">
                            <i class="fas fa-users text-white fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Pacientes Únicos</h6>
                            <h3 class="fw-bold mb-0"><?= number_format($reportData['summary']['total_patients']) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #d13438;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                            style="width: 50px; height: 50px; background: linear-gradient(135deg, #d13438 0%, #a4262c 100%);">
                            <i class="fas fa-dollar-sign text-white fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Costo Total</h6>
                            <h3 class="fw-bold mb-0">$<?= number_format($reportData['summary']['total_cost'], 2) ?></h3>
                            <small class="text-muted">Promedio: $<?= number_format($reportData['summary']['avg_cost_per_attention'], 2) ?> por atención</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Data Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary py-3" style="background-color: #0078d4 !important;">
                    <h5 class="mb-0 fw-bold" style="color: white !important;">
                        <i class="fas fa-table me-2"></i>Detalle por Clínica
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #0078d4 !important; color: white !important;">
                                <tr>
                                    <th class="py-3" style="color: white !important;"
                                        title="Nombre de la clínica o centro médico">
                                        Clínica
                                    </th>
                                    <th class="py-3 text-center" style="color: white !important;"
                                        title="Total de atenciones médicas registradas en el período">
                                        Total Atenciones
                                    </th>
                                    <th class="py-3 text-center" style="color: white !important;"
                                        title="Atenciones que han sido completadas y marcadas como atendidas">
                                        Atendidas
                                    </th>
                                    <th class="py-3 text-center" style="color: white !important;"
                                        title="Atenciones registradas que aún no han sido completadas">
                                        No Atendidos
                                    </th>
                                    <th class="py-3 text-center" style="color: white !important;"
                                        title="Número de pacientes diferentes atendidos (sin duplicados)">
                                        Pacientes Únicos
                                    </th>
                                    <th class="py-3 text-center" style="color: white !important;"
                                        title="Porcentaje de atenciones que han sido completadas (Atendidas ÷ Total)">
                                        Tasa de Atención
                                    </th>
                                    <th class="py-3 text-center" style="color: white !important;"
                                        title="Costo total acumulado de todas las atenciones en el período">
                                        Costo Total
                                    </th>
                                    <th class="py-3 text-center" style="color: white !important;"
                                        title="Acciones disponibles para esta clínica">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reportData['data'] as $item): ?>
                                    <tr>
                                        <td class="py-3"
                                            title="<?= Html::encode($item['clinic_name']) ?> - <?= $item['clinic_status'] ?>">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <i class="fas fa-clinic-medical fa-2x text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1 fw-bold"><?= Html::encode($item['clinic_name']) ?></h6>
                                                    <small class="text-muted">
                                                        <span class="badge bg-<?= $item['clinic_status'] == 'Activo' ? 'success' : 'secondary' ?>">
                                                            <?= $item['clinic_status'] ?>
                                                        </span>
                                                        | <?= $item['appointments_count'] ?> Citas | <?= $item['emergencies_count'] ?> Emergencias
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 text-center"
                                            title="Promedio: <?= $item['avg_patient_attentions'] ?> atenciones por paciente">
                                            <h4 class="fw-bold mb-0"><?= number_format($item['total_attentions']) ?></h4>
                                            <small class="text-muted"><?= $item['avg_patient_attentions'] ?> por paciente</small>
                                        </td>
                                        <td class="py-3 text-center"
                                            title="<?= number_format(($item['attended_count'] / max($item['total_attentions'], 1)) * 100, 1) ?>% del total">
                                            <span class="badge bg-success fs-6 px-3 py-2">
                                                <?= number_format($item['attended_count']) ?>
                                            </span>
                                        </td>
                                        <td class="py-3 text-center"
                                            title="<?= number_format(($item['pending_count'] / max($item['total_attentions'], 1)) * 100, 1) ?>% del total">
                                            <span class="badge bg-warning fs-6 px-3 py-2">
                                                <?= number_format($item['pending_count']) ?>
                                            </span>
                                        </td>
                                        <td class="py-3 text-center"
                                            title="Pacientes distintos atendidos en el período">
                                            <h5 class="fw-bold mb-0"><?= number_format($item['unique_patients']) ?></h5>
                                        </td>
                                        <td class="py-3 text-center">
                                            <?php
                                            $badgeClass = 'bg-secondary';
                                            if ($item['attendance_rate'] >= 90) $badgeClass = 'bg-success';
                                            elseif ($item['attendance_rate'] >= 75) $badgeClass = 'bg-info';
                                            elseif ($item['attendance_rate'] >= 60) $badgeClass = 'bg-warning';
                                            else $badgeClass = 'bg-danger';
                                            ?>
                                            <span class="badge <?= $badgeClass ?> fs-6 px-3 py-2"
                                                title="<?= $item['attendance_rate'] >= 90 ? 'Excelente' : ($item['attendance_rate'] >= 75 ? 'Bueno' : ($item['attendance_rate'] >= 60 ? 'Regular' : 'Bajo')) ?> desempeño">
                                                <?= number_format($item['attendance_rate'], 1) ?>%
                                            </span>
                                            <div class="progress mt-2" style="height: 5px;"
                                                title="Progreso de tasa de atención">
                                                <div class="progress-bar"
                                                    role="progressbar"
                                                    style="width: <?= $item['attendance_rate'] ?>%;"
                                                    aria-valuenow="<?= $item['attendance_rate'] ?>"
                                                    aria-valuemin="0"
                                                    aria-valuemax="100"></div>
                                            </div>
                                        </td>
                                        <td class="py-3 text-center"
                                            title="Promedio: $<?= number_format($item['avg_cost'], 2) ?> por atención">
                                            <h5 class="fw-bold mb-0 text-primary">
                                                $<?= number_format($item['total_cost'], 2) ?>
                                            </h5>
                                            <small class="text-muted">Prom: $<?= number_format($item['avg_cost'], 2) ?></small>
                                        </td>
                                        <td class="py-3 text-center">
                                            <button class="btn btn-outline-primary btn-sm btn-view-detail"
                                                data-id="<?= $item['id'] ?>"
                                                title="Ver detalles completos de esta clínica">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="<?= Url::to(['sis-siniestro/por-clinica', 'clinica_id' => $item['id']]) ?>"
                                                class="btn btn-outline-info btn-sm"
                                                title="Ver todas las atenciones de esta clínica">
                                                <i class="fas fa-list"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card border-0 shadow-lg h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-chart-pie me-2"></i>Distribución de Atenciones
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="attentionsDistributionChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card border-0 shadow-lg h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-chart-line me-2"></i>Desempeño por Clínica
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Analysis -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-chart-bar me-2"></i>Análisis de Desempeño
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php
                        $performanceLevels = [
                            'excelente' => ['count' => 0, 'color' => 'success', 'icon' => 'fa-trophy'],
                            'bueno' => ['count' => 0, 'color' => 'info', 'icon' => 'fa-thumbs-up'],
                            'regular' => ['count' => 0, 'color' => 'warning', 'icon' => 'fa-chart-line'],
                            'bajo' => ['count' => 0, 'color' => 'danger', 'icon' => 'fa-exclamation-triangle'],
                        ];

                        foreach ($reportData['data'] as $item) {
                            if (isset($performanceLevels[$item['performance_level']])) {
                                $performanceLevels[$item['performance_level']]['count']++;
                            }
                        }
                        ?>

                        <?php foreach ($performanceLevels as $level => $data): ?>
                            <div class="col-md-3 mb-3">
                                <div class="card border-0 shadow-sm h-100"
                                    style="border-left: 4px solid var(--bs-<?= $data['color'] ?>);">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                                                style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--bs-<?= $data['color'] ?>) 0%, var(--bs-<?= $data['color'] ?>-dark) 100%);">
                                                <i class="fas <?= $data['icon'] ?> text-white fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="text-muted mb-1"><?= ucfirst($level) ?></h6>
                                                <h3 class="fw-bold mb-0"><?= $data['count'] ?></h3>
                                                <small class="text-muted">
                                                    <?= $reportData['summary']['total_clinics'] > 0 ?
                                                        round(($data['count'] / $reportData['summary']['total_clinics']) * 100, 1) : 0 ?>% del total
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Register JavaScript for charts ONLY (no tooltip initialization)
$clinicNames = json_encode(array_column($reportData['data'], 'clinic_name'));
$attentionsData = json_encode(array_column($reportData['data'], 'total_attentions'));
$attendanceRates = json_encode(array_column($reportData['data'], 'attendance_rate'));
$totalCosts = json_encode(array_column($reportData['data'], 'total_cost'));

$this->registerJs(
    <<<JS
$(document).ready(function() {
    console.log('Initializing charts...');
    
    // Store chart instances so we can destroy them later
    window.chartInstances = window.chartInstances || {};
    
    // Prepare data for charts
    const clinicNames = {$clinicNames};
    const attentionsData = {$attentionsData};
    const attendanceRates = {$attendanceRates};
    const totalCosts = {$totalCosts};
    
    // Colors array
    const backgroundColors = [
        '#0078d4', '#107c10', '#ff8c00', '#d13438', '#8661c5',
        '#00b7c3', '#ff4343', '#9e008e', '#0078d4', '#107c10'
    ];
    
    // Attentions Distribution Chart (Pie)
    if (document.getElementById('attentionsDistributionChart')) {
        // Destroy existing chart if it exists
        if (window.chartInstances.attentionsDistributionChart) {
            window.chartInstances.attentionsDistributionChart.destroy();
        }
        
        const ctx1 = document.getElementById('attentionsDistributionChart').getContext('2d');
        window.chartInstances.attentionsDistributionChart = new Chart(ctx1, {
            type: 'pie',
            data: {
                labels: clinicNames,
                datasets: [{
                    data: attentionsData,
                    backgroundColor: backgroundColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.formattedValue + ' atenciones';
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Performance Chart (Bar)
    if (document.getElementById('performanceChart')) {
        // Destroy existing chart if it exists
        if (window.chartInstances.performanceChart) {
            window.chartInstances.performanceChart.destroy();
        }
        
        const ctx2 = document.getElementById('performanceChart').getContext('2d');
        window.chartInstances.performanceChart = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: clinicNames,
                datasets: [
                    {
                        label: 'Tasa de Atención %',
                        data: attendanceRates,
                        backgroundColor: '#0078d4',
                        borderColor: '#005a9e',
                        borderWidth: 1,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Total Atenciones',
                        data: attentionsData,
                        backgroundColor: '#107c10',
                        borderColor: '#0e6a0e',
                        borderWidth: 1,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Tasa de Atención %'
                        },
                        min: 0,
                        max: 100
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Total Atenciones'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
    }
});
JS
);
