<?php

// Importamos las clases necesarias de Yii para trabajar con HTML y URLs.
// Html::encode() es crucial para prevenir ataques XSS.
// Url::to() ayuda a generar URLs correctas dentro de la aplicación Yii.
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Bloque de PHPDoc para documentar las variables que esta vista espera recibir.
 * Idealmente, estas variables son pasadas desde el controlador (`RmClinicaController`).
 *
 * @var yii\web\View $this La instancia de la vista actual.
 * @var float $fondoAnualTotal El monto total del fondo anual de la clínica.
 * @var float $fondoMensualTotal El monto total del fondo mensual disponible.
 * @var float $consumoMensualActual El monto que ya ha sido consumido del fondo mensual.
 * @var float $porcentajeConsumido El porcentaje calculado del fondo mensual que ha sido consumido.
 * @var string $colorClase La clase CSS (ej. 'bg-success', 'bg-warning', 'bg-danger') que determina el color de la barra de progreso.
 * @var int $limiteVerde El umbral en porcentaje (ej. 50) hasta donde el consumo se considera "verde" (ideal).
 * @var int $limiteAmarillo El umbral en porcentaje (ej. 80) hasta donde el consumo se considera "amarillo" (advertencia).
 */

// --- SECCIÓN: DECLARACIÓN Y/O RECEPCIÓN DE VARIABLES PRINCIPALES ---
// Aquí es donde se inicializan las variables que la vista necesita.
// En un entorno de producción, estas variables NO DEBERÍAN tener valores por defecto (??).
// En su lugar, el controlador DEBE pasarlas después de obtener los datos de la base de datos
// o de tus modelos de negocio.

// EJEMPLO de cómo recibir variables del controlador:
// Si en tu controlador haces:
// return $this->render('fondo', [
//     'fondoAnualTotal' => $miModelo->fondoAnual,
//     'fondoMensualTotal' => $miModelo->fondoMensual,
//     'consumoMensualActual' => $miModelo->getConsumoActual(),
//     'limiteVerde' => $configuracion->limite_verde,
//     'limiteAmarillo' => $configuracion->limite_amarillo,
// ]);
//
// Entonces aquí NO usarías `??` para asignar valores por defecto.
// La sintaxis `??` (Null Coalescing Operator) se usa aquí solo para fines de DEMOSTRACIÓN
// y para evitar errores si las variables no son pasadas desde el controlador durante el desarrollo.

$fondoAnualTotal = $fondoAnualTotal ?? 5000000.00; // Total del fondo anual de la clínica.
$fondoMensualTotal = $fondoMensualTotal ?? 400000.00; // Total del fondo disponible para el mes actual.

// ===========================================================================================
// !!! PUNTO DE CONFIGURACIÓN MANUAL PARA EL PORCENTAJE DE CONSUMO MENSUAL (PARA PRUEBAS) !!!
// ===========================================================================================
// Para simular diferentes escenarios de consumo, puedes ajustar el valor de $consumoMensualActual aquí.
// Esto te permitirá ver cómo el medidor reacciona a distintos porcentajes.
//
// EJEMPLOS:
// $consumoMensualActual = 80000.00;   // Si el fondo mensual es 400,000, esto es el 20%.
// $consumoMensualActual = 260000.00;  // Si el fondo mensual es 400,000, esto es el 65%.
// $consumoMensualActual = 350000.00;  // Si el fondo mensual es 400,000, esto es el 87.5%.
//
// El valor actual se ha fijado en 80000.00 para representar el 20% del fondo mensual total (400,000).
$consumoMensualActual = $consumoMensualActual ?? 80000.00; // MONTO ACTUAL CONSUMIDO (20% para esta demo)
// ===========================================================================================

// Límites predeterminados para las zonas de color (también deberían venir del controlador/configuración).
$limiteVerde = $limiteVerde ?? 50;   // Hasta este porcentaje, el consumo es "verde".
$limiteAmarillo = $limiteAmarillo ?? 80; // Entre limiteVerde y este porcentaje, es "amarillo".

// --- SECCIÓN: CÁLCULOS DERIVADOS ---
// Cálculos basados en las variables iniciales. Idealmente, gran parte de esta lógica
// de negocio debería residir en el controlador o en un modelo de servicio.

// Calcula el porcentaje consumido del fondo mensual. Evita división por cero.
$porcentajeConsumido = ($fondoMensualTotal > 0) ? round(($consumoMensualActual / $fondoMensualTotal) * 100, 2) : 0;

// Colores VIBRANTES definidos para el fondo del medidor que visualiza las ZONAS (verde, amarillo, rojo).
// Estos colores se usan tanto en PHP para la carga inicial como en JavaScript para las actualizaciones dinámicas.
$vibrantGreen = '#28a745';   // Color verde brillante (Bootstrap 'success').
$vibrantYellow = '#ffc107'; // Color amarillo brillante (Bootstrap 'warning').
$vibrantRed = '#dc3545';     // Color rojo brillante (Bootstrap 'danger').

// Determina la clase CSS para el color de la BARRA DE PROGRESO (el relleno del medidor).
// Esta lógica se duplica en el bloque JavaScript para permitir actualizaciones dinámicas en el cliente.
if ($porcentajeConsumido <= $limiteVerde) {
    $colorClase = 'bg-success'; // Si el consumo está en zona verde.
} elseif ($porcentajeConsumido <= $limiteAmarillo) {
    $colorClase = 'bg-warning'; // Si el consumo está en zona amarilla.
} else {
    $colorClase = 'bg-danger';   // Si el consumo está en zona roja.
}

// Genera la cadena CSS para el gradiente de fondo del contenedor del medidor.
// Esto crea las bandas de color (verde, amarillo, rojo) que indican los umbrales.
// Se usa Html::encode() para asegurar que la cadena CSS sea segura.
$gaugeBackgroundGradient = "linear-gradient(to right,
    {$vibrantGreen} 0%, {$vibrantGreen} {$limiteVerde}%,
    {$vibrantYellow} {$limiteVerde}%, {$vibrantYellow} {$limiteAmarillo}%,
    {$vibrantRed} {$limiteAmarillo}%, {$vibrantRed} 100%
)";

// --- SECCIÓN: CONFIGURACIÓN DE LA VISTA Y ASSETS ---
// Configura el título de la página y las "migas de pan" (breadcrumbs) para la navegación.
$this->title = 'Monitor de Fondos de Clínica';
$this->params['breadcrumbs'][] = ['label' => 'Clínicas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Registra los assets (CSS/JS) necesarios para la vista.
\yii\web\YiiAsset::register($this);
?>

<!-- --- SECCIÓN: CONTENIDO HTML DE LA PÁGINA --- -->
<!-- Contenedor principal de todo el contenido de la vista. -->
<div class="monitor-fondos-clinica-content-wrapper">

    <!-- Título principal visible de la página. -->
    <h1 class="h3 mb-4 text-gray-800"><?= Html::encode($this->title) ?></h1>

    <!-- Bloque de Tarjetas de Resumen de Fondos (Anual y Mensual). -->
    <div class="row">
        <!-- Tarjeta del Fondo Anual Total. -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card fund-card fund-card-primary shadow py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Fondo Anual Total</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <!-- Muestra el monto del fondo anual formateado como moneda venezolana (VEF). -->
                                <?php echo Yii::$app->formatter->asCurrency($fondoAnualTotal, 'VEF'); ?>
                            </div>
                        </div>
                        <div class="col-auto mr-3">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta del Fondo Mensual Total. -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card fund-card fund-card-success shadow py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Fondo Mensual Total</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <!-- Muestra el monto del fondo mensual formateado como moneda venezolana (VEF). -->
                                <?php echo Yii::$app->formatter->asCurrency($fondoMensualTotal, 'VEF'); ?>
                            </div>
                        </div>
                        <div class="col-auto mr-3">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Ingresos Anuales vs Mensuales (colocado antes de la barra de fondo) -->
    <div class="ms-panel border-info mb-4">
        <div class="ms-panel-header">
            <h3 class="section-title mb-0"><i class="fas fa-chart-bar"></i> Ingresos Anuales vs Mensuales</h3>
        </div>
        <div class="ms-panel-body" style="height: 260px;">
            <canvas id="ingresosChart"></canvas>
        </div>
    </div>

    <!-- Sección del Medidor de Consumo Mensual (la "Barra de Gasolina"). -->
    <div class="ms-panel border-indigo mb-4">
        <div class="ms-panel-header">
            <h3 class="section-title"><i class="fas fa-gas-pump"></i> Consumo del Fondo Mensual</h3>
        </div>
        <div class="ms-panel-body">
            <!-- Muestra el consumo actual y el total mensual en formato de moneda. -->
            <p class="mb-4">
                <strong>Consumo Actual:</strong> <?php echo Yii::$app->formatter->asCurrency($consumoMensualActual, 'VEF'); ?> de
                <?php echo Yii::$app->formatter->asCurrency($fondoMensualTotal, 'VEF'); ?>
            </p>

            <!-- Contenedor del medidor principal. El estilo de fondo (gradiente de colores)
                 se establece aquí inicialmente con PHP y luego es actualizado por JavaScript. -->
            <div id="gauge-container" class="gauge-container" style="background: <?= Html::encode($gaugeBackgroundGradient); ?>;">
                <!-- La barra de progreso dentro del medidor. Su ancho y color de fondo
                     cambian dinámicamente según el porcentaje consumido y los límites. -->
                <div id="gauge-progress" class="gauge-progress <?= Html::encode($colorClase); ?>" style="width: <?= Html::encode($porcentajeConsumido); ?>%;">
                    <!-- Muestra el porcentaje consumido. El CSS lo pinta de blanco. -->
                    <span><?= Html::encode($porcentajeConsumido); ?>%</span>
                </div>
            </div>

            <!-- Etiquetas de texto que explican las zonas de color del medidor. -->
            <div class="gauge-label">
                <span class="text-success"><i class="fas fa-circle"></i> 0% - <span id="label-verde"><?= Html::encode($limiteVerde); ?></span>% (Ideal)</span>
                <span class="text-warning"><i class="fas fa-circle"></i> <span id="label-verde-min"><?= Html::encode($limiteVerde); ?></span>% - <span id="label-amarillo"><?= Html::encode($limiteAmarillo); ?></span>% (Advertencia)</span>
                <span class="text-danger"><i class="fas fa-circle"></i> <span id="label-amarillo-min"><?= Html::encode($limiteAmarillo); ?></span>% - 100% (Peligro)</span>
            </div>

            <!-- Controles Slíder para ajustar visualmente los límites de advertencia. -->
            <div class="limit-controls mt-5 p-4 bg-light rounded shadow-sm">
                <h4 class="text-primary mb-3"><i class="fas fa-sliders-h"></i> Ajustar Límites de Advertencia</h4>
                <p class="text-muted small">Arrastra los slíders para definir los porcentajes donde cambian las zonas de color.
                    **Nota:** Estos cambios son solo visuales en la página actual. Para guardarlos permanentemente y
                    afectar los correos de advertencia, se requeriría una integración con la base de datos.</p>

                <div class="form-group mb-4">
                    <label for="sliderLimiteVerde" class="font-weight-bold text-success">Límite Verde (hasta): <output for="sliderLimiteVerde" id="outputLimiteVerde"><?= Html::encode($limiteVerde); ?></output>%</label>
                    <input type="range" class="form-control-range" id="sliderLimiteVerde" min="0" max="99" value="<?= Html::encode($limiteVerde); ?>">
                </div>

                <div class="form-group mb-0">
                    <label for="sliderLimiteAmarillo" class="font-weight-bold text-warning">Límite Amarillo (hasta): <output for="sliderLimiteAmarillo" id="outputLimiteAmarillo"><?= Html::encode($limiteAmarillo); ?></output>%</label>
                    <input type="range" class="form-control-range" id="sliderLimiteAmarillo" min="1" max="100" value="<?= Html::encode($limiteAmarillo); ?>">
                </div>
            </div>
            <!-- Fin Controles para editar los límites -->

            <!-- Barra paralela que también indica el porcentaje consumido (visibilidad opcional). -->
            <div class="secondary-bar-container">
                <div class="secondary-bar-progress" style="width: <?= Html::encode($porcentajeConsumido); ?>%;">
                    Consumido: <?= Html::encode($porcentajeConsumido); ?>%
                </div>
            </div>

            <!-- Texto explicativo para el usuario. -->
            <p class="mt-4 text-muted">
                Este indicador muestra visualmente el porcentaje del fondo mensual que ha sido consumido.
                Los límites de colores (Verde, Amarillo, Rojo) son actualmente valores fijos para demostración.
                En una futura mejora, estos límites podrán ser configurados por el administrador.
                La lógica de envío de correos de advertencia o peligro también se implementaría en el backend al alcanzar estos umbrales.
            </p>
        </div>
    </div>

    <!-- EL CARD DE TRANSACCIONES RECIENTES FUE ELIMINADO SEGÚN LO SOLICITADO. -->

</div> <!-- Cierre del div .monitor-fondos-clinica-content-wrapper -->

<!-- (El gráfico fue movido arriba, antes de la barra de fondo) -->

<?php
// Incluir Chart.js desde CDN
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', ['position' => \yii\web\View::POS_END]);

// Datos desde PHP
$anual = (float)$fondoAnualTotal;
$mensual = (float)$fondoMensualTotal;

// --- SECCIÓN: SCRIPTS JAVASCRIPT ---
// Bloque existente + inicialización del gráfico
$this->registerJs(<<<JS
(function() {
    // Inicialización del gráfico de barras (dos conjuntos: Anual y Mensual)
    const ctx = document.getElementById('ingresosChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Ingresos'],
                datasets: [
                    {
                        label: 'Anual',
                        data: [{$anual}],
                        backgroundColor: '#4e79a7',
                        borderRadius: 6,
                        maxBarThickness: 60
                    },
                    {
                        label: 'Mensual',
                        data: [{$mensual}],
                        backgroundColor: '#f28e2b',
                        borderRadius: 6,
                        maxBarThickness: 60
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const val = ctx.parsed.y || 0;
                                try {
                                    return new Intl.NumberFormat('es-VE', { style: 'currency', currency: 'VES', maximumFractionDigits: 2 }).format(val);
                                } catch (e) {
                                    return val.toLocaleString('es-VE');
                                }
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                try { return value.toLocaleString('es-VE'); } catch (e) { return value; }
                            }
                        }
                    }
                }
            }
        });
    }

    // Código JS existente a continuación
    const sliderLimiteVerde = document.getElementById('sliderLimiteVerde');
    const outputLimiteVerde = document.getElementById('outputLimiteVerde');
    const sliderLimiteAmarillo = document.getElementById('sliderLimiteAmarillo');
    const outputLimiteAmarillo = document.getElementById('outputLimiteAmarillo');
    const gaugeContainer = document.getElementById('gauge-container');
    const gaugeProgress = document.getElementById('gauge-progress');

    // Referencias a las etiquetas de texto que muestran los límites de porcentaje.
    const labelVerde = document.getElementById('label-verde');
    const labelVerdeMin = document.getElementById('label-verde-min');
    const labelAmarillo = document.getElementById('label-amarillo');
    const labelAmarilloMin = document.getElementById('label-amarillo-min');

    // Obtenemos el porcentaje consumido actual que PHP calculó.
    // parseFloat() asegura que sea un número flotante.
    const porcentajeConsumido = parseFloat('$porcentajeConsumido');

    // Colores vibrantes: se inyectan desde las variables PHP para mantener la consistencia.
    const vibrantGreen = '{$vibrantGreen}';
    const vibrantYellow = '{$vibrantYellow}';
    const vibrantRed = '{$vibrantRed}';

    /**
     * Función principal para actualizar el medidor y las etiquetas cuando los slíders cambian.
     */
    function updateGaugeLimits() {
        // Obtenemos los valores actuales de los slíders y los convertimos a enteros.
        let currentLimiteVerde = parseInt(sliderLimiteVerde.value);
        let currentLimiteAmarillo = parseInt(sliderLimiteAmarillo.value);

        // Lógica para asegurar que el límite amarillo siempre sea mayor que el verde.
        if (currentLimiteAmarillo <= currentLimiteVerde) {
            currentLimiteAmarillo = currentLimiteVerde + 1; // El amarillo debe ser al menos 1% mayor que el verde.
            if (currentLimiteAmarillo > 100) currentLimiteAmarillo = 100; // No puede exceder el 100%.
            sliderLimiteAmarillo.value = currentLimiteAmarillo; // Actualiza el valor del slider amarillo.
        }

        // Actualiza los elementos <output> junto a los slíders.
        outputLimiteVerde.textContent = currentLimiteVerde;
        outputLimiteAmarillo.textContent = currentLimiteAmarillo;

        // Actualiza las etiquetas de texto que describen las zonas de color en el medidor.
        labelVerde.textContent = currentLimiteVerde;
        labelVerdeMin.textContent = currentLimiteVerde;
        labelAmarillo.textContent = currentLimiteAmarillo;
        labelAmarilloMin.textContent = currentLimiteAmarillo;

        // Genera la nueva cadena CSS para el gradiente de fondo del medidor.
        // NOTA IMPORTANTE: Observa el uso de `\${variableJS}`. El `\` escapa el `$`
        // para que PHP NO intente interpretar `\$currentLimiteVerde` como una variable PHP.
        // Esto permite que JavaScript lo interprete correctamente en el navegador.
        const newGradient = `linear-gradient(to right,
            ${vibrantGreen} 0%, ${vibrantGreen} \${currentLimiteVerde}%,
            ${vibrantYellow} \${currentLimiteVerde}%, ${vibrantYellow} \${currentLimiteAmarillo}%,
            ${vibrantRed} \${currentLimiteAmarillo}%, ${vibrantRed} 100%
        )`;
        gaugeContainer.style.background = newGradient; // Aplica el nuevo gradiente al fondo.

        // Actualiza la clase de color de la barra de progreso (relleno).
        // Primero, removemos todas las clases de color existentes.
        gaugeProgress.classList.remove('bg-success', 'bg-warning', 'bg-danger');
        // Luego, aplicamos la clase correcta según el porcentaje consumido y los límites actuales.
        if (porcentajeConsumido <= currentLimiteVerde) {
            gaugeProgress.classList.add('bg-success');
        } else if (porcentajeConsumido <= currentLimiteAmarillo) {
            gaugeProgress.classList.add('bg-warning');
        } else {
            gaugeProgress.classList.add('bg-danger');
        }
    }

    // --- INICIALIZACIÓN DE LOS SLÍDERS AL CARGAR LA PÁGINA ---
    // Inyectamos los valores iniciales de los límites (que vienen de PHP) en los slíders de JavaScript.
    sliderLimiteVerde.value = parseFloat('<?= $limiteVerde ?>');
    sliderLimiteAmarillo.value = parseFloat('<?= $limiteAmarillo ?>');
    outputLimiteVerde.textContent = sliderLimiteVerde.value;
    outputLimiteAmarillo.textContent = sliderLimiteAmarillo.value;

    // Ajusta el límite mínimo del slider amarillo al cargar, para que siempre sea mayor que el verde.
    sliderLimiteAmarillo.min = parseInt(sliderLimiteVerde.value) + 1;
    if (parseInt(sliderLimiteAmarillo.value) <= parseInt(sliderLimiteVerde.value)) {
        sliderLimiteAmarillo.value = parseInt(sliderLimiteVerde.value) + 1;
        outputLimiteAmarillo.textContent = sliderLimiteAmarillo.value;
    }

    // --- ESCUCHADORES DE EVENTOS PARA LOS SLÍDERS ---
    // Cuando el slider "Límite Verde" se mueve.
    sliderLimiteVerde.addEventListener('input', () => {
        // Actualiza el límite mínimo del slider amarillo para que siempre sea mayor que el verde.
        sliderLimiteAmarillo.min = parseInt(sliderLimiteVerde.value) + 1;
        // Si el valor actual del slider amarillo es menor o igual al nuevo valor del verde, lo ajusta.
        if (parseInt(sliderLimiteAmarillo.value) <= parseInt(sliderLimiteVerde.value)) {
            sliderLimiteAmarillo.value = parseInt(sliderLimiteVerde.value) + 1;
        }
        updateGaugeLimits(); // Llama a la función de actualización.
    });

    // Cuando el slider "Límite Amarillo" se mueve.
    sliderLimiteAmarillo.addEventListener('input', () => {
        // Asegura que el límite amarillo siempre sea mayor que el verde.
        if (parseInt(sliderLimiteAmarillo.value) <= parseInt(sliderLimiteVerde.value)) {
            sliderLimiteAmarillo.value = parseInt(sliderLimiteVerde.value) + 1;
            // Si al ajustarlo excede 100, lo fija en 100.
            if (parseInt(sliderLimiteAmarillo.value) > 100) {
                 sliderLimiteAmarillo.value = 100;
            }
        }
        updateGaugeLimits(); // Llama a la función de actualización.
    });

    // Llama a la función de actualización una vez al cargar la página para asegurar
    // que todo el medidor se muestre correctamente con los valores iniciales.
    updateGaugeLimits();

})(); // Se usa una IIFE (Immediately Invoked Function Expression) para encapsular el código JS.
JS
);
?>
