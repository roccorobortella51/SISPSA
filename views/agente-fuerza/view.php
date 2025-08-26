<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Agente;
use app\models\User;
use app\components\UserHelper;

/** @var yii\web\View $this */
/** @var app\models\AgenteFuerza $model */

$this->title = 'DETALLES DE ASESOR VENDEDOR: ';
$this->params['breadcrumbs'][] = ['label' => 'AGENTES DE FUERZA', 'url' => ['index-by-agente']];
$this->params['breadcrumbs'][] = 'DETALLES';

\yii\web\YiiAsset::register($this);

// Preparar el nombre de la agencia
$agenciaNombre = 'N/A';
if ($model->agente) {
    $agenciaNombre = $model->agente->nom;
}

// Preparar el nombre de usuario (asumiendo que userDatos tiene nombres y apellidos)
$nombreCompletoUsuario = 'N/A';
$telefonoUsuario = 'N/A';
$emailUsuario = 'N/A';

if ($model->userDatos) {
    $nombreCompletoUsuario = $model->userDatos->nombres . ' ' . $model->userDatos->apellidos;
    $telefonoUsuario = $model->userDatos->telefono ?? 'N/A';
    $emailUsuario = $model->userDatos->email ?? 'N/A';
} elseif ($model->user) {
    $nombreCompletoUsuario = $model->user->username;
}

// Función auxiliar para mostrar Sí/No con íconos y clases de CSS
function formatBooleanIcon($value) {
    if ($value) {
        return '<span class="text-green-600 mr-1"><i class="fas fa-check-circle"></i></span> Sí';
    } else {
        return '<span class="text-red-600 mr-1"><i class="fas fa-times-circle"></i></span> No';
    }
}


$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol == 'GERENTE-COMERCIALIZACION'); 


// --- DATOS DE EJEMPLO PARA LA GRÁFICA ---
// Reemplaza esto con los datos reales que pasarás desde el controlador
$gananciasPorMes = [
    'Enero' => 520,
    'Febrero' => 610,
    'Marzo' => 580,
    'Abril' => 750,
    'Mayo' => 840,
    'Junio' => 790,
];

// Codificamos los datos para que JavaScript pueda usarlos
$meses = array_keys($gananciasPorMes);
$datos = array_values($gananciasPorMes);
$mesesJs = json_encode($meses);
$datosJs = json_encode($datos);

?>

<div class="view-main-container agente-view">
   

    <div class="ms-panel-header">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="button-group-spacing">
            <?php if($permisos){ ?>
                <?= Html::a(
                    '<i class="fas fa-edit"></i> Actualizar',
                    ['update', 'id' => $model->id],
                    ['class' => 'btn btn-primary']
                ) ?>
            <?php } ?>
            <?= Html::a(
                '<i class="fas fa-arrow-left"></i> Volver',
                ['agente-fuerza/index-by-agente', 'agente_id' => $model->agente_id],
                [
                    'class' => 'btn btn-secondary',
                    'title' => 'Volver a la lista de agentes de fuerza',
                ]
            ) ?>
           
        </div>
    </div>

    <div class="ms-panel border-blue">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-user-tie text-blue-600"></i> Información General del Asesor
            </h3>
            <div class="info-grid">
                <div>
                    <p><strong>Usuario:</strong> <?= Html::encode($nombreCompletoUsuario) ?></p>
                    <p><strong>Teléfono:</strong> <?= Html::encode($telefonoUsuario) ?></p>
                </div>
                <div>
                    <p><strong>Agencia Asociada:</strong> <?= Html::encode($agenciaNombre) ?></p>
                    <p><strong>Correo Electrónico:</strong> <?= Html::a(Html::encode($emailUsuario), 'mailto:' . Html::encode($emailUsuario), ['class' => 'text-primary']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="ms-panel border-purple">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-percent text-purple-600"></i> Porcentajes de Comisión
            </h3>
            <div class="info-grid-percentages">
                <div class="info-card-body col-md-2" style="margin-right:10px; margin-bottom:10px;">
                    <h6>Venta</h6>
                    <p class="h4"><?= Yii::$app->formatter->asPercent($model->por_venta / 100) ?></p>
                </div>
                <div class="info-card-body col-md-2" style="margin-right:10px; margin-bottom:10px;">
                    <h6>Asesoría</h6>
                    <p class="h4"><?= Yii::$app->formatter->asPercent($model->por_asesor / 100) ?></p>
                </div>
                <div class="info-card-body col-md-2" style="margin-right:10px; margin-bottom:10px;">
                    <h6>Cobranza</h6>
                    <p class="h4"><?= Yii::$app->formatter->asPercent($model->por_cobranza / 100) ?></p>
                </div>
                <div class="info-card-body col-md-2" style="margin-right:10px; margin-bottom:10px;">
                    <h6>Post Venta</h6>
                    <p class="h4"><?= Yii::$app->formatter->asPercent($model->por_post_venta / 100) ?></p>
                </div>
                <div class="info-card-body col-md-2" style="margin-right:10px; margin-bottom:10px;">
                    <h6>Registro</h6>
                    <p class="h4"><?= Yii::$app->formatter->asPercent($model->por_registrar / 100) ?></p>
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

    <div class="ms-panel border-green">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-lock text-green-600"></i> Permisos de Acceso
            </h3>
            <div class="info-grid">
                <div class="info-card-body">
                    <h6 class="section-subtitle">Venta y Asesoría</h6>
                    <ul class="divide-y">
                        <li class="py-3 flex justify-between items-center text-gray-700">
                            Puede Vender
                            <span><?= formatBooleanIcon($model->puede_vender) ?></span>
                        </li>
                        <li class="py-3 flex justify-between items-center text-gray-700">
                            Puede Asesorar
                            <span><?= formatBooleanIcon($model->puede_asesorar) ?></span>
                        </li>
                    </ul>
                </div>
                <div class="info-card-body">
                    <h6 class="section-subtitle">Gestión y Cobranza</h6>
                    <ul class="divide-y">
                        <li class="py-3 flex justify-between items-center text-gray-700">
                            Puede Cobrar
                            <span><?= formatBooleanIcon($model->puede_cobrar) ?></span>
                        </li>
                        <li class="py-3 flex justify-between items-center text-gray-700">
                            Puede Post Venta
                            <span><?= formatBooleanIcon($model->puede_post_venta) ?></span>
                        </li>
                        <li class="py-3 flex justify-between items-center text-gray-700">
                            Puede Registrar
                            <span><?= formatBooleanIcon($model->puede_registrar) ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="ms-panel border-gray">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-calendar-alt text-gray-600"></i> Fechas de Gestión
            </h3>
            <div class="info-grid">
                <div class="info-card-body">
                    <h6>Fecha de Creación</h6>
                    <p class="h5"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></p>
                </div>
                <div class="info-card-body">
                    <h6>Última Actualización</h6>
                    <p class="h5"><?= Yii::$app->formatter->asDatetime($model->updated_at) ?></p>
                </div>
                <?php /*
                <div class="info-card-body">
                    <h6>Fecha de Eliminación</h6>
                    <p class="h5"><?= $model->deleted_at ? Yii::$app->formatter->asDatetime($model->deleted_at) : 'N/A' ?></p>
                </div>
                */ ?>
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
                labels: meses,
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
                            callback: function(val, index) {
                                const monto = datos[index].toLocaleString('en-US', {
                                    style: 'currency',
                                    currency: 'USD',
                                    minimumFractionDigits: 2
                                });
                                return [meses[index], monto];
                            },
                            font: {
                                size: 12
                            },
                            padding: 10
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