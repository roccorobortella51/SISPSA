<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\UserHelper;

/** @var yii\web\View $this */
/** @var app\models\Agente $model */

$this->title = 'DETALLES DE LA AGENCIA: ' . Html::encode($model->nom);
$this->params['breadcrumbs'][] = ['label' => 'AGENCIAS', 'url' => ['index']];
$this->params['breadcrumbs'][] = Html::encode($model->nom);
\yii\web\YiiAsset::register($this);

function formatPercentage($value) {
    return Yii::$app->formatter->asPercent((float)$value / 100);
}

function formatDateTime($value) {
    return $value ? Yii::$app->formatter->asDatetime($value) : 'N/A';
}

$ownerContactInfo = UserHelper::getAgenteOwnerContactInfo($model->id);
$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol =='DIRECTOR-COMERCIALIZACIÓN');

// --- DATOS DE EJEMPLO PARA LA GRÁFICA ---
$gananciasPorMes = [
    'Enero' => 12500,
    'Febrero' => 14300,
    'Marzo' => 13800,
    'Abril' => 16500,
    'Mayo' => 17200,
    'Junio' => 15300,
];

// Preparamos los datos y las etiquetas para el JavaScript
$meses = array_keys($gananciasPorMes);
$datos = array_values($gananciasPorMes);

// Codificamos los datos para que JavaScript pueda usarlos fácilmente
$mesesJs = json_encode($meses);
$datosJs = json_encode($datos);

?>

<div class="main-container agente-view"> 
   
    <div class="header-section"> 
        <div class="d-flex justify-content-between align-items-center w-100">
            <h1><?= Html::encode($this->title) ?></h1>
            <?= Html::a(
                '<i class="fas fa-users mr-2"></i> AGENTES/FUERZA DE ESTA AGENCIA',
                ['agente-fuerza/index-by-agente', 'agente_id' => $model->id],
                [
                    'class' => 'btn btn-primary',
                ]
            ) ?>
        </div>
       
        <div class="header-buttons-group">
            <?php 
            if($permisos){
                echo Html::a(
                    '<i class="fas fa-edit mr-2"></i> Actualizar',
                    ['update', 'id' => $model->id],
                    ['class' => 'btn-base btn-blue'] 
                );
            } ?>
            <?= Html::a(
                '<i class="fas fa-undo mr-2"></i> Volver',
                ['index'],
                [
                    'class' => 'btn-base btn-gray', 
                    'title' => 'Volver a la lista de agencias',
                ]
            ) ?>
        </div>
    </div>

    <div class="ms-panel">
    <div class="ms-panel-body">
        <h2 class="section-title">
            <i class="fas fa-building text-blue-600 mr-3"></i> Información General de la Agencia
        </h2>
        <div class="row">
            <div class="col-md-6">
                <p class="text-gray-700 mb-2 text-lg-18"><strong>Nombre del Propietario:</strong> <?= Html::encode(($model->propietario->nombres ?? 'N/A') . ' ' . ($model->propietario->apellidos ?? '')) ?></p>
                <p class="text-gray-700 mb-2 text-lg-18"><strong>RIF:</strong> <?= Html::encode($ownerContactInfo['rif']) ?></p>
            </div>
            <div class="col-md-6">
                <p class="text-gray-700 mb-2 text-lg-18"><strong>Email:</strong> <?= Html::a(Html::encode($ownerContactInfo['email']), 'mailto:' . Html::encode($ownerContactInfo['email']), ['class' => 'text-primary']) ?></p>
                <p class="text-gray-700 mb-2 text-lg-18"><strong>Teléfono:</strong> <?= Html::encode($ownerContactInfo['telefono']) ?></p>
            </div>
        </div>
        <p class="text-gray-700 mt-4 pt-4 border-top text-lg-18"><strong>Dirección:</strong> <?= nl2br(Html::encode($ownerContactInfo['direccion'])) ?></p>
    </div>
</div>

    <div class="ms-panel">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-percent text-purple-600 mr-3"></i> Porcentajes de Comisión
            </h3>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="info-card-body text-center">
                        <h4 class="text-muted">Porcentaje Venta</h4>
                        <p class="h4 text-info"><?= formatPercentage($model->por_venta) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-card-body text-center">
                        <h4 class="text-muted">Porcentaje Asesoría</h4>
                        <p class="h4 text-info"><?= formatPercentage($model->por_asesor) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-card-body text-center">
                        <h4 class="text-muted">Porcentaje Cobranza</h4>
                        <p class="h4 text-info"><?= formatPercentage($model->por_cobranza) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-card-body text-center">
                        <h4 class="text-muted">Porcentaje Post Venta</h4>
                        <p class="h4 text-info"><?= formatPercentage($model->por_post_venta) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-card-body text-center">
                        <h4 class="text-muted">Porcentaje Agente</h4>
                        <p class="h4 text-info"><?= formatPercentage($model->por_agente) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-card-body text-center">
                        <h4 class="text-muted">Porcentaje Máximo</h4>
                        <p class="h4 text-info"><?= formatPercentage($model->por_max) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="ms-panel">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-chart-line text-green-600 mr-3"></i> Ganancias de los Últimos 6 Meses
            </h3>
            <div class="chart-container" style="height: 250px;">
                <canvas id="gananciasChart"></canvas>
            </div>
        </div>
    </div>

    <div class="ms-panel">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-calendar-alt text-gray-600 mr-3"></i> Fechas de Gestión
            </h3>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h4 class="text-muted">Fecha de Creación</h4>
                        <p class="h5 text-dark"><?= formatDateTime($model->created_at) ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h4 class="text-muted">Última Actualización</h4>
                        <p class="h5 text-dark"><?= formatDateTime($model->updated_at) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const meses = <?= $mesesJs ?>;
        const datos = <?= $datosJs ?>;

        const ctx = document.getElementById('gananciasChart').getContext('2d');
        const gananciasChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: meses, // El eje X usa los nombres de los meses
                datasets: [{
                    label: 'Ganancias',
                    data: datos,
                    backgroundColor: 'rgba(40, 167, 69, 0.2)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        ticks: {
                            // Esta función de callback se encarga de mostrar el mes y el monto
                            callback: function(val, index) {
                                // Devolvemos un array con el mes y el valor formateado
                                const monto = datos[index].toLocaleString('en-US', {
                                    style: 'currency',
                                    currency: 'USD',
                                    minimumFractionDigits: 2
                                });
                                return [meses[index], monto];
                            },
                            font: {
                                size: 12 // Opcional: ajusta el tamaño de la fuente
                            },
                            padding: 10 // Opcional: agrega espacio entre la línea del eje y las etiquetas
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Ganancia: $' + context.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    });
</script>