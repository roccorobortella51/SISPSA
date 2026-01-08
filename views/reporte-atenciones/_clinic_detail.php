<?php
// app/views/reporte-atenciones/_clinic_detail.php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var array $detailData */
?>

<div class="clinic-detail-container">
    <!-- Clinic Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="border-left: 4px solid #0078d4;">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2 fw-bold text-primary">
                                <i class="fas fa-hospital me-2"></i><?= Html::encode($detailData['clinic']->nombre) ?>
                            </h2>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                        <?= Html::encode($detailData['clinic']->direccion ?? 'No especificada') ?>
                                    </p>
                                    <p class="mb-1">
                                        <i class="fas fa-phone text-muted me-2"></i>
                                        <?= Html::encode($detailData['clinic']->telefono ?? 'No especificado') ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <i class="fas fa-calendar-alt text-muted me-2"></i>
                                        Período: <?= Yii::$app->formatter->asDate($detailData['date_range']['from']) ?>
                                        al <?= Yii::$app->formatter->asDate($detailData['date_range']['to']) ?>
                                    </p>
                                    <p class="mb-1">
                                        <i class="fas fa-info-circle text-muted me-2"></i>
                                        Estado:
                                        <span class="badge bg-<?= $detailData['clinic']->estatus == 'Activo' ? 'success' : 'secondary' ?>">
                                            <?= $detailData['clinic']->estatus ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <a href="<?= Url::to(['sis-siniestro/por-clinica', 'clinica_id' => $detailData['clinic']->id]) ?>"
                                class="btn btn-primary">
                                <i class="fas fa-list me-2"></i>Ver Todas las Atenciones
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Summary -->
    <div class="row mb-4">
        <?php
        $stats = [
            'total_attentions' => 0,
            'attended_count' => 0,
            'pending_count' => 0,
            'total_cost' => 0,
            'unique_patients' => 0,
        ];

        foreach ($detailData['attentions'] as $attention) {
            $stats['total_attentions']++;
            $stats['attended_count'] += $attention->atendido ? 1 : 0;
            $stats['pending_count'] += $attention->atendido ? 0 : 1;
            $stats['total_cost'] += $attention->costo_total ?? 0;
        }

        // Count unique patients
        $patientIds = [];
        foreach ($detailData['attentions'] as $attention) {
            $patientIds[$attention->iduser] = true;
        }
        $stats['unique_patients'] = count($patientIds);

        $attendance_rate = $stats['total_attentions'] > 0 ?
            round(($stats['attended_count'] / $stats['total_attentions']) * 100, 1) : 0;
        ?>

        <div class="col-md-2 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body">
                    <h3 class="fw-bold text-primary"><?= $stats['total_attentions'] ?></h3>
                    <p class="text-muted mb-0">Atenciones</p>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body">
                    <h3 class="fw-bold text-success"><?= $stats['attended_count'] ?></h3>
                    <p class="text-muted mb-0">Atendidas</p>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body">
                    <h3 class="fw-bold text-warning"><?= $stats['pending_count'] ?></h3>
                    <p class="text-muted mb-0">No Atendidos</p>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body">
                    <h3 class="fw-bold text-info"><?= $stats['unique_patients'] ?></h3>
                    <p class="text-muted mb-0">Pacientes</p>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body">
                    <h3 class="fw-bold text-purple"><?= $attendance_rate ?>%</h3>
                    <p class="text-muted mb-0">Tasa Atención</p>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body">
                    <h3 class="fw-bold text-danger">$<?= number_format($stats['total_cost'], 2) ?></h3>
                    <p class="text-muted mb-0">Costo Total</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Attentions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-history me-2"></i>Últimas Atenciones
                    </h5>
                    <span class="badge bg-primary"><?= count($detailData['attentions']) ?> registros</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Paciente</th>
                                    <th>Tipo</th>
                                    <th>Baremos</th>
                                    <th>Estado</th>
                                    <th>Costo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($detailData['attentions'], 0, 10) as $attention): ?>
                                    <tr>
                                        <td>
                                            <?= Yii::$app->formatter->asDate($attention->fecha) ?><br>
                                            <small class="text-muted"><?= $attention->hora ?></small>
                                        </td>
                                        <td>
                                            <?php if ($attention->afiliado): ?>
                                                <?= Html::encode($attention->afiliado->nombres . ' ' . $attention->afiliado->apellidos) ?><br>
                                                <small class="text-muted">CI: <?= Html::encode($attention->afiliado->cedula) ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">No disponible</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $attention->es_cita ? 'info' : 'warning' ?>">
                                                <?= $attention->es_cita ? 'Cita' : 'Emergencia' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($attention->baremos): ?>
                                                <?php
                                                $baremoNames = [];
                                                foreach ($attention->baremos as $baremo) {
                                                    $baremoNames[] = Html::encode($baremo->nombre_servicio);
                                                }
                                                echo implode(', ', array_slice($baremoNames, 0, 2));
                                                if (count($baremoNames) > 2) {
                                                    echo '... (+' . (count($baremoNames) - 2) . ')';
                                                }
                                                ?>
                                            <?php else: ?>
                                                <span class="text-muted">No especificado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($attention->atendido): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>Atendido
                                                </span>
                                                <?php if ($attention->fecha_atencion): ?>
                                                    <br><small class="text-muted"><?= Yii::$app->formatter->asDate($attention->fecha_atencion) ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock me-1"></i>Pendiente
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <strong>$<?= number_format($attention->costo_total ?? 0, 2) ?></strong>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($detailData['attentions']) > 10): ?>
                        <div class="card-footer text-center">
                            <a href="<?= Url::to(['sis-siniestro/por-clinica', 'clinica_id' => $detailData['clinic']->id]) ?>"
                                class="btn btn-outline-primary">
                                Ver todas las atenciones (<?= count($detailData['attentions']) ?>)
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Statistics Chart -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-chart-line me-2"></i>Evolución Diaria
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="dailyStatsChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// JavaScript for daily stats chart
$dailyStatsJson = json_encode($detailData['daily_stats']);
$this->registerJs(<<<JS
$(document).ready(function() {
    const dailyStats = {$dailyStatsJson};
    
    const dates = dailyStats.map(stat => stat.date);
    const attentionsCount = dailyStats.map(stat => parseInt(stat.attentions_count));
    const attendedCount = dailyStats.map(stat => parseInt(stat.attended_count));
    
    const ctx = document.getElementById('dailyStatsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [
                {
                    label: 'Total Atenciones',
                    data: attentionsCount,
                    borderColor: '#0078d4',
                    backgroundColor: 'rgba(0, 120, 212, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Atendidas',
                    data: attendedCount,
                    borderColor: '#107c10',
                    backgroundColor: 'rgba(16, 124, 16, 0.1)',
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Atenciones por Día'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Número de Atenciones'
                    }
                }
            }
        }
    });
});
JS);
?>