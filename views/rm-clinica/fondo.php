<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/**
 * @var float $fondoAnualTotal El monto total del fondo anual.
 * @var float $fondoMensualTotal El monto total del fondo mensual.
 * @var float $consumoMensualActual El monto actual consumido del fondo mensual.
 * @var float $porcentajeConsumido El porcentaje del fondo mensual consumido.
 * @var string $colorClase La clase CSS para el color del medidor (ej. 'bg-success', 'bg-warning', 'bg-danger').
 * @var int $limiteVerde El límite superior para el color verde del medidor (ej. 50).
 * @var int $limiteAmarillo El límite superior para el color amarillo del medidor (ej. 80).
 */

// --- Variables de Ejemplo (Estas deberían venir de tu controlador) ---
$fondoAnualTotal = $fondoAnualTotal ?? 5000000.00;
$fondoMensualTotal = $fondoMensualTotal ?? 400000.00;
$consumoMensualActual = $consumoMensualActual ?? 220000.00;
$limiteVerde = $limiteVerde ?? 50; // Por ejemplo, hasta 50% es verde
$limiteAmarillo = $limiteAmarillo ?? 80; // Por ejemplo, de 50% a 80% es amarillo
// --- Fin Variables de Ejemplo ---

// Calcular el porcentaje y la clase de color (idealmente se hace en el controlador)
$porcentajeConsumido = ($fondoMensualTotal > 0) ? round(($consumoMensualActual / $fondoMensualTotal) * 100, 2) : 0;

// Definir la clase de color para la BARRA DE PROGRESO (será un color sólido y fuerte)
if ($porcentajeConsumido <= $limiteVerde) {
    $colorClase = 'bg-success'; // Utiliza las clases de Bootstrap directamente
} elseif ($porcentajeConsumido <= $limiteAmarillo) {
    $colorClase = 'bg-warning';
} else {
    $colorClase = 'bg-danger';
}

// Colores VIBRANTES para el fondo del medidor que muestra las ZONAS
$vibrantGreen = '#28a745'; // Bootstrap success
$vibrantYellow = '#ffc107'; // Bootstrap warning
$vibrantRed = '#dc3545';   // Bootstrap danger

// Generar el gradiente de fondo dinámicamente con colores VIBRANTES
$gaugeBackgroundGradient = "linear-gradient(to right,
    {$vibrantGreen} 0%, {$vibrantGreen} {$limiteVerde}%,
    {$vibrantYellow} {$limiteVerde}%, {$vibrantYellow} {$limiteAmarillo}%,
    {$vibrantRed} {$limiteAmarillo}%, {$vibrantRed} 100%
)";


$this->title = 'Monitor de Fondos de Clínica';
$this->params['breadcrumbs'][] = ['label' => 'Clínicas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

\yii\web\YiiAsset::register($this);
?>

<div class="monitor-fondos-clinica-content-wrapper">


    <!-- Título principal de la vista -->
    <h1 class="h3 mb-4 text-gray-800"><?= Html::encode($this->title) ?></h1>

    <!-- Tarjetas de Total de Fondos -->
    <div class="row">
        <!-- Tarjeta de Fondo Anual -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card fund-card fund-card-primary shadow py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Fondo Anual Total</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo Yii::$app->formatter->asCurrency($fondoAnualTotal, 'VEF'); ?>
                            </div>
                        </div>
                        <div class="col-auto mr-3"> <!-- Añadido mr-3 para el margen -->
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta de Fondo Mensual -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card fund-card fund-card-success shadow py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Fondo Mensual Total</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo Yii::$app->formatter->asCurrency($fondoMensualTotal, 'VEF'); ?>
                            </div>
                        </div>
                        <div class="col-auto mr-3"> <!-- Añadido mr-3 para el margen -->
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Medidor de Consumo Mensual (Barra de Gasolina) -->
    <div class="ms-panel border-indigo mb-4">
        <div class="ms-panel-header">
            <h3 class="section-title"><i class="fas fa-gas-pump"></i> Consumo del Fondo Mensual</h3>
        </div>
        <div class="ms-panel-body">
            <p class="mb-4">
                <strong>Consumo Actual:</strong> <?php echo Yii::$app->formatter->asCurrency($consumoMensualActual, 'VEF'); ?> de
                <?php echo Yii::$app->formatter->asCurrency($fondoMensualTotal, 'VEF'); ?>
            </p>

            <div class="gauge-container" style="background: <?= Html::encode($gaugeBackgroundGradient); ?>;">
                <div class="gauge-progress <?= Html::encode($colorClase); ?>" style="width: <?= Html::encode($porcentajeConsumido); ?>%;">
                    <span><?= Html::encode($porcentajeConsumido); ?>%</span>
                </div>
            </div>
            <div class="gauge-label">
                <span class="text-success"><i class="fas fa-circle"></i> 0% - <?= Html::encode($limiteVerde); ?>% (Ideal)</span>
                <span class="text-warning"><i class="fas fa-circle"></i> <?= Html::encode($limiteVerde); ?>% - <?= Html::encode($limiteAmarillo); ?>% (Advertencia)</span>
                <span class="text-danger"><i class="fas fa-circle"></i> <?= Html::encode($limiteAmarillo); ?>% - 100% (Peligro)</span>
            </div>

            <!-- Barra paralela que indica el porcentaje consumido del fondo mensual -->
            <div class="secondary-bar-container">
                <div class="secondary-bar-progress" style="width: <?= Html::encode($porcentajeConsumido); ?>%;">
                    Consumido: <?= Html::encode($porcentajeConsumido); ?>%
                </div>
            </div>

            <p class="mt-4 text-muted">
                Este indicador muestra visualmente el porcentaje del fondo mensual que ha sido consumido.
                Los límites de colores (Verde, Amarillo, Rojo) son actualmente valores fijos para demostración.
                En una futura mejora, estos límites podrán ser configurados por el administrador.
                La lógica de envío de correos de advertencia o peligro también se implementaría en el backend al alcanzar estos umbrales.
            </p>
        </div>
    </div>

</div> <!-- /monitor-fondos-clinica-content-wrapper -->
