<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\web\View;
use yii\helpers\ArrayHelper;
use yii\bootstrap4\Modal;

$this->registerCssFile(Yii::getAlias('@web') . "/css/_formsiniestros.css", ['position' => View::POS_HEAD]);



/* @var $this yii\web\View */
/* @var $model app\models\SisSiniestro */
/* @var $form yii\widgets\ActiveForm */
/* @var $afiliado app\models\UserDatos */

// Obtener información del plan del afiliado
$plan = \app\models\Planes::findOne($afiliado->plan_id);
$precioPlan = $plan ? $plan->cobertura : 0;

// Obtener la sumatoria de siniestros del afiliado
$sumatoriaSiniestros = \app\models\SisSiniestro::find()
    ->where(['iduser' => $afiliado->id])
    ->andWhere(['not', ['costo_total' => null]])
    ->sum('costo_total');

$totalDisponible = $precioPlan - $sumatoriaSiniestros;
 
//OBTENER el parámetro 'es_cita' directamente del Request, con un valor por defecto de 0.
$esCita = (int)Yii::$app->request->get('es_cita', 0);

// Definir los modos y términos
$esCitaMode = ($esCita === 1);
$terminoPrincipal = $esCitaMode ? 'Cita' : 'Atención';
$tituloSeccion = 'Datos de la ' . $terminoPrincipal;

// Obtener el contrato activo del afiliado
$contrato = \app\models\Contratos::find()
    ->where(['user_id' => $afiliado->id])
    ->andWhere(['estatus' => 'Activo'])
    ->orderBy(['created_at' => SORT_DESC])
    ->one();



?>

<div class="sis-siniestro-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="ms-panel">
        <div class="ms-panel-header">
    <h3 class="section-title" id="titulo-datos-registro">
        <i class="fas fa-file-alt text-blue-600"></i> <?= $tituloSeccion ?>
    </h3>
</div>
        <div class="ms-panel-body">
            <div class="plan-info-container">
                <div class="plan-info-title">Información del Plan y Límites</div>
                <div class="plan-info-item">
                    <span class="plan-info-label">Plan:</span>
                    <span class="plan-info-value"><?= $afiliado->plan->nombre ?></span>
                </div>
                <div class="plan-info-item">
                    <span class="plan-info-label">Cobertura del Plan:</span>
                    <span class="plan-info-value"><?= number_format($precioPlan, 2) ?></span>
                </div>
                <div class="plan-info-item">
                    <span class="plan-info-label">Total de Atenciones Médicas Registradas:</span>
                    <span class="plan-info-value"><?= number_format($sumatoriaSiniestros ?? 0, 2) ?></span>
                </div>
                <div class="plan-info-item plan-info-total">
                    <span class="plan-info-label">Total Disponible:</span>
                    <span class="plan-info-value"><?= number_format($totalDisponible, 2) ?></span>
                </div>
        </div>

    <?php
// Consulta para obtener los baremos utilizados por este afiliado
$baremosUtilizados = \app\models\SisSiniestroBaremo::find()
    ->joinWith(['siniestro', 'baremo'])
    ->where(['sis_siniestro.iduser' => $afiliado->id])
    ->andWhere(['baremo.estatus' => 'Activo'])
    ->orderBy(['sis_siniestro.fecha' => SORT_DESC])
    ->all();

// Separar en citas y siniestros
$baremosCitas = [];
$baremosSiniestros = [];

foreach ($baremosUtilizados as $siniestroBaremo) {
    if ($siniestroBaremo->siniestro) {
        $item = [
            'fecha' => $siniestroBaremo->siniestro->fecha,
            'nombre_servicio' => $siniestroBaremo->baremo->nombre_servicio,
            'area' => $siniestroBaremo->baremo->area ? $siniestroBaremo->baremo->area->nombre : 'Sin área',
            'descripcion' => $siniestroBaremo->baremo->descripcion,
            'precio' => $siniestroBaremo->baremo->precio ?? 0,
            'tipo' => $siniestroBaremo->siniestro->es_cita ? 'Cita' : 'Siniestro',
            'estado' => $siniestroBaremo->siniestro->estatus ?? 'Desconocido'
        ];

        if ($siniestroBaremo->siniestro->es_cita) {
            $baremosCitas[] = $item;
        } else {
            $baremosSiniestros[] = $item;
        }
    }
}
?>

<!-- Estilos para la tabla -->


<div class="row">
    <!-- Estadísticas rápidas -->
    <div class="col-md-12">
        <div class="stats-card">
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="stats-number"><?= count($baremosCitas) + count($baremosSiniestros) ?></div>
                    <div class="stats-label">Total Baremos Usados</div>
                </div>
                <div class="col-md-3">
                    <div class="stats-number"><?= count($baremosCitas) ?></div>
                    <div class="stats-label">Citas Realizadas</div>
                </div>
                <div class="col-md-3">
                    <div class="stats-number"><?= count($baremosSiniestros) ?></div>
                    <div class="stats-label">Atenciones Realizadas</div>
                </div>
                <div class="col-md-3">
                    <div class="stats-number">$<?= number_format(array_sum(array_column(array_merge($baremosCitas, $baremosSiniestros), 'precio')), 2) ?></div>
                    <div class="stats-label">Total Invertido</div>
                </div>
            </div>
        </div>
    </div>

    <?php if($esCita == 1){?>
    <!-- Tabla de Citas -->
    <div class="col-md-12">
        <h5 class="section-title">
            <i class="fas fa-calendar-check"></i> Citas Realizadas (<?= count($baremosCitas) ?>)
        </h5>
        <?php if (!empty($baremosCitas)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-baremos">
                    <thead>
                        <tr>
                            <th width="120">Fecha</th>
                            <th>Área</th>
                            <th>Servicio</th>
                            <th>Descripción</th>
                            <th width="100">Precio</th>
                            <th width="100">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($baremosCitas as $cita): ?>
                        <tr>
                            <td>
                               <?= date('d/m/Y', strtotime($cita['fecha'])) ?>
                            </td>
                            <td>
                                <strong><?= $cita['area'] ?></strong>
                            </td>
                            <td><?= $cita['nombre_servicio'] ?></td>
                            <td>
                                <?php if (!empty($cita['descripcion'])): ?>
                                    <?= $cita['descripcion'] ?>
                                <?php else: ?>
                                    <span class="text-muted">Sin descripción</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-success">
                                <strong>$<?= number_format($cita['precio'], 2) ?></strong>
                            </td>
                            <td>
                                <span class="badge badge-cita">Cita</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle"></i> No se han realizado citas aún.
            </div>
        <?php endif; ?>

<?php } ?>

    <!-- Tabla de Siniestros -->
    <?php if($esCita == 0){?>
    <div class="col-md-12">
        <h5 class="section-title">
            <i class="fas fa-file-medical"></i> Siniestros Atendidos (<?= count($baremosSiniestros) ?>)
        </h5>
        <?php if (!empty($baremosSiniestros)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-baremos">
                    <thead>
                        <tr>
                            <th width="120">Fecha</th>
                            <th>Área</th>
                            <th>Servicio</th>
                            <th>Descripción</th>
                            <th width="100">Precio</th>
                            <th width="100">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($baremosSiniestros as $siniestro): ?>
                        <tr>
                            <td>
                                <?= date('d/m/Y', strtotime($siniestro['fecha'])) ?>
                            </td>
                            <td>
                                <strong><?= $siniestro['area'] ?></strong>
                            </td>
                            <td><?= $siniestro['nombre_servicio'] ?></td>
                            <td>
                                <?php if (!empty($siniestro['descripcion'])): ?>
                                    <?= $siniestro['descripcion'] ?>
                                <?php else: ?>
                                    <span class="text-muted">Sin descripción</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-success">
                                <strong>$<?= number_format($siniestro['precio'], 2) ?></strong>
                            </td>
                            <td>
                                <span class="badge badge-siniestro">Siniestro</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle"></i> No se han realizado atenciones aún.
            </div>
        <?php endif; ?>
    <?php }?>
    </div>
</div>

<!-- Script para mejoras visuales -->
<script>
$(document).ready(function() {
    // Agregar tooltips si es necesario
    $('[data-toggle="tooltip"]').tooltip();
    
    // Resaltar filas al pasar el mouse
    $('.table-baremos tbody tr').hover(
        function() {
            $(this).addClass('table-active');
        },
        function() {
            $(this).removeClass('table-active');
        }
    );
});
</script>



    <br>
    <div class="row">


    <div class="row mb-4 d-none" id="tipo-registro-control">
    <div class="col-md-12">
        <!-- Inicio: Control de Tipo de Registro con estilo de Tarjeta -->
        <div class="card shadow-sm border-2 border-primary-subtle rounded-3">
            <div class="card-body py-3 px-4">
                <div class="form-group mb-0">
                    <!-- Título más destacado -->
                    <label class="fw-bold mb-2 text-primary" for="es-cita-switch">
                        <i class="fas fa-clipboard-list me-1"></i> Tipo de Registro
                    </label>
                    
                    <div class="d-flex align-items-center justify-content-between">
                        
                        <!-- Etiqueta Dinámica: Más grande y con estilo de píldora -->
                        <span class="badge fs-6 p-2 rounded-pill shadow-sm" id="tipo-registro-label" style="min-width: 120px; text-align: center;"></span>
                        
                        <!-- Switch de control (Eliminamos el span wrapper y ajustamos el div para usar Bootstrap estándar) -->
                        <div class="form-check form-switch ms-auto">
                            <!-- Campo oculto que guarda el valor real de 0 o 1 -->
                            <?= Html::activeHiddenInput($model, 'es_cita', ['id' => 'es-cita-hidden']) ?>
                            
                            <input class="form-check-input" type="checkbox" id="es-cita-switch" 
                                <?= $model->es_cita == 1 ? 'checked' : '' ?>
                                title="Activar para Cita (reserva de servicio con plazo pendiente)">
                            <label class="form-check-label fw-semibold" for="es-cita-switch">Es Cita</label>
                        </div>
                    </div>
                    
                    <!-- Información contextual -->
                    <h5 class="form-text text-muted mt-2 d-block">
                        <strong>Siniestro:</strong> Uso inmediato. <strong>Cita:</strong> Reserva (permite Plazo Pendiente).
                    </h5>
                </div>
            </div>
        </div>
        <!-- Fin: Control de Tipo de Registro con estilo de Tarjeta -->
    </div>
</div>


<!-------------------------------------- contenido de los baremos  --------------------------------------------->

<div class="col-md-12"> <!-- UNICA COLUMNA: Envuelve toda la sección de baremos para evitar que rompa el layout -->

<?php
// Inicializar las variables para evitar errores si el contrato no está activo
$baremosTotales = [];
$baremosInfo = [];
$baremosRestringidosIDs = [];
?>

<?php if ($contrato && $contrato->estatus === 'Activo'): ?>
    
    <div id="plazo-error-message" class="alert alert-danger" style="display: none;">
        <i class="fas fa-exclamation-triangle"></i> **ADVERTENCIA:** No se puede guardar la CITA. El baremo seleccionado requiere un **Plazo de Espera Pendiente**. Por favor, deseleccione el baremo para continuar.
    </div>

<?php
// Las sentencias 'use' (ArrayHelper, Select2) se asumen existentes.

// Consulta para listar los baremos de ese plan y clínica
$planesItemsCobertura = \app\models\PlanesItemsCobertura::find()
    ->joinWith('baremo')
    ->joinWith('plan')
    ->joinWith('baremo.area')
    ->where(['planes.clinica_id' => $afiliado->clinica_id])
    ->andWhere(['baremo.estatus' => 'Activo'])
    ->andWhere(['planes.id' => $afiliado->plan_id])
    ->all();

// PRIMERO: Obtener los baremos seleccionados ANTES de filtrar
$selectedBaremos = [];
if (!$model->isNewRecord) {
    if (method_exists($model, 'getBaremos')) {
        $baremosRelacion = $model->getBaremos()->all();
        
        if (empty($baremosRelacion)) {
            $baremosDirectos = (new \yii\db\Query())
                ->select(['baremo_id'])
                ->from('sis_siniestro_baremo')
                ->where(['siniestro_id' => $model->id])
                ->column();
            
            if (!empty($baremosDirectos)) {
                $selectedBaremos = $baremosDirectos;
            }
        } else {
            $selectedBaremos = ArrayHelper::getColumn($baremosRelacion, 'id');
        }
    } else {
        // Fallback: buscar directamente en la tabla sis_siniestro_baremo
        $baremosDirectos = (new \yii\db\Query())
            ->select(['baremo_id'])
            ->from('sis_siniestro_baremo')
            ->where(['siniestro_id' => $model->id])
            ->column();
        
        if (!empty($baremosDirectos)) {
            $selectedBaremos = $baremosDirectos;
        }
    }
}

// Crear arrays para las categorías
$baremosSinPlazo = [];          // NO tiene plazo definido
$baremosConPlazoCumplido = [];  // Tiene plazo, pero ya se cumplió
$baremosPendientesPlazo = [];   // Tiene plazo, y está pendiente
$baremosForzados = [];          // Baremos ya guardados que no cumplen filtros actuales
$baremosInfo = [];              // Información auxiliar para JavaScript
$baremosRestringidosIDs = [];   // IDs de baremos con Plazo PENDIENTE

$fechaActual = new \DateTime();

foreach ($planesItemsCobertura as $item) {
    if ($item->baremo) {
        $restricciones = [];
        $isRestrictedByPlazo = false;
        $hasPlazoEver = (!empty($item->plazo_espera) && $item->plazo_espera > 0);

        // Definir la cantidad de veces usado
        $vecesUsado = \app\models\SisSiniestroBaremo::find()
            ->joinWith('siniestro')
            ->where(['baremo_id' => $item->baremo_id])
            ->andWhere(['iduser' => $afiliado->id])
            ->count();

        // Verificar si excede el límite (solo si tiene límite definido)
        $excedeLimite = ($item->cantidad_limite !== null && $item->cantidad_limite > 0 && $vecesUsado >= $item->cantidad_limite);

        // Verificar si este baremo está entre los seleccionados (solo para update)
        $esBaremoGuardado = !$model->isNewRecord && in_array($item->baremo_id, $selectedBaremos);

        // --- LÓGICA DE FILTRADO SEGÚN MODO ---
        $debeIncluirse = true;
        
        if ($esCitaMode) {
            // MODO CITA: Aplicar todas las restricciones
            
            if ($contrato) {
                $fechaContratoIni = new \DateTime($contrato->fecha_ini);

                // Lógica de plazo de espera
                if ($hasPlazoEver) {
                    $diff = $fechaContratoIni->diff($fechaActual);
                    $mesesTranscurridos = $diff->y * 12 + $diff->m;
                    $plazoRequerido = (int)$item->plazo_espera;

                    if ($mesesTranscurridos < $plazoRequerido) {
                        $isRestrictedByPlazo = true; // Plazo PENDIENTE
                    }
                }
            }

            // Excluir baremos con plazo pendiente O que exceden el límite
            // PERO incluir si es un baremo ya guardado (solo en update)
            if (($isRestrictedByPlazo || $excedeLimite)) {
                if (!$esBaremoGuardado) {
                    $debeIncluirse = false;
                }
            }
            
        } else {
            // MODO SINIESTRO: Solo incluir baremos sin límite (cantidad_limite IS NULL)
            // PERO incluir si es un baremo ya guardado (solo en update)
            if ($item->cantidad_limite !== null) {
                if (!$esBaremoGuardado) {
                    $debeIncluirse = false;
                }
            }
        }

        // Definir el precio del baremo
        $precioBaremo = $item->baremo->precio ?? 0;

        // Clasificación de baremos
        $area = $item->baremo->area ? $item->baremo->area->nombre : 'Sin área';
        $nombreCompleto = "ÁREA: {$area} - SERVICIO: {$item->baremo->nombre_servicio}";

        if (!empty($item->baremo->descripcion)) {
            $nombreCompleto .= " | DESCRIPCIÓN: {$item->baremo->descripcion}";
        }

        if (!empty($restricciones)) {
            $nombreCompleto .= " [" . implode(", ", $restricciones) . "]";
        }

        // Si es un baremo guardado que no cumple los filtros, marcarlo como HISTÓRICO
        if ($esBaremoGuardado && !$debeIncluirse) {
            $baremosForzados[$item->baremo_id] = "(HISTÓRICO) " . $nombreCompleto;
            $debeIncluirse = true; // Forzar inclusión
        }

        /*if (!$debeIncluirse) {
            continue;
        }*/

        // Clasificación normal según disponibilidad
        if ($esBaremoGuardado && isset($baremosForzados[$item->baremo_id])) {
            // Ya se asignó en la sección de forzados
        } elseif ($esCitaMode) {
            if (!$hasPlazoEver) {
                $baremosSinPlazo[$item->baremo_id] = $nombreCompleto;
            } elseif ($isRestrictedByPlazo) {
                $baremosPendientesPlazo[$item->baremo_id] = "(NO DISPONIBLE NO CUMPLE CON EL PLAZO) " . $nombreCompleto;
                $baremosRestringidosIDs[] = $item->baremo_id;
            } else {
                $baremosConPlazoCumplido[$item->baremo_id] = "(DISPONIBLE) " . $nombreCompleto;
            }
        } else {
            // Modo Siniestro - todos los baremos disponibles se muestran sin prefijo
            $baremosSinPlazo[$item->baremo_id] = $nombreCompleto;
        }

        $baremosInfo[$item->baremo_id] = [
            'nombre' => $item->baremo->nombre_servicio,
            'area' => $area,
            'plazo_espera' => $item->plazo_espera,
            'cantidad_limite' => $item->cantidad_limite,
            'veces_usado' => $vecesUsado,
            'precio' => $precioBaremo,
            'is_restricted_by_plazo' => $isRestrictedByPlazo,
            'has_plazo_ever' => $hasPlazoEver,
            'excede_limite' => $excedeLimite,
            'es_historico' => $esBaremoGuardado,
        ];
    }
}

// COMBINAR todos los arrays: forzados + normales
$baremosTotales = $baremosForzados + $baremosSinPlazo + $baremosConPlazoCumplido + $baremosPendientesPlazo;
?>

<div class="col-md-12" align="center"><h3>Formulario</h3></div>
<div class="field-with-icon">
    <?= $form->field($model, 'idbaremo[]')->widget(Select2::class, [ 
        'data' => $baremosTotales, 
        'options' => [
            'multiple' => true,
            'value' => $selectedBaremos,
            'placeholder' => 'Seleccione uno o más Baremos',
            'class' => 'form-control form-lg',
            'id' => 'baremos-select' 
        ],
        'pluginOptions' => [
            'allowClear' => true,
            'closeOnSelect' => true,
            'tags' => false,
            'tokenSeparators' => [',', ' '],
        ],
    ])->label('Baremos')->hint('Seleccione el baremo') ?>
</div>


<?php else: ?>
    <!-- Mostrar mensaje si el contrato no está activo -->
    <div id="contrato-error-message" class="alert alert-warning" style="margin-top: 20px;">
        <i class="fas fa-exclamation-triangle"></i> **ADVERTENCIA:** El contrato del afiliado no está activado. Por favor, active el contrato para continuar.
    </div>
<?php endif; ?>

    <div id="baremos-tabla-container" style="display: none;">
        <h4 class="section-title mb-3 fs-3">
            <i class="fas fa-list-alt text-blue-600"></i> Resumen de Servicios
        </h4>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Servicio</th>
                    <th>Área</th>
                    <th class="text-center">Restricciones</th>
                    <th class="cost-col">Precio</th>
                </tr>
            </thead>
            <tbody id="baremos-tabla-body">
                </tbody>
        </table>
    </div>
    
    <div class="costo-total-container" id="costo-total-container" style="display: none;">
        <div class="costo-total-label">Total calculado:</div>
        <div class="costo-total-value" id="costo-total-value">$0.00</div>
        <!-- ADD THE HIDDEN INPUT FIELD RIGHT HERE -->
        <?= $form->field($model, 'costo_total')->hiddenInput(['id' => 'costo-total-input'])->label(false) ?>

        <div id="cobertura-warning" class="mt-2 p-2 rounded-3 text-danger" style="display: none; background-color: #ffe0b2; border: 1px solid #ff9800;"></div>
        <div id="cita-warning" class="mt-2 p-2 rounded-3 text-warning" style="display: none; background-color: #fff3cd; border: 1px solid #ffc107;">
            <i class="fas fa-exclamation-triangle me-1"></i> Este registro es una **Cita**. Los baremos con Plazo Pendiente no estaran disponibles.
        </div>
    </div>
</div> <!-- FIN de la columna col-md-12 que envuelve todo el contenido de baremos -->

<?php 
// -----------------------------------------------------------------------------------------------------
// CÓDIGO JAVASCRIPT con BLOQUEO DE GUARDADO
// -----------------------------------------------------------------------------------------------------
$baremosTotalesJson = json_encode($baremosTotales);
$baremosInfoJson = json_encode($baremosInfo);
$baremosRestringidosJson = json_encode($baremosRestringidosIDs); 

// Se registra el script al final de la vista
$this->registerJs(<<<JS
    // Información de baremos y lista total de opciones
    const baremosTotales = {$baremosTotalesJson};
    const baremosInfo = {$baremosInfoJson};
    const baremosRestringidosIDs = {$baremosRestringidosJson}; // IDs con plazo pendiente
    
    const esCitaHidden = $('#es-cita-hidden');
    const esCitaSwitch = $('#es-cita-switch');
    const tipoRegistroLabel = $('#tipo-registro-label');
    const citaWarning = $('#cita-warning');
    const baremosSelect = $('#baremos-select');
    const form = baremosSelect.closest('form');
    
    // Referencia al div del mensaje de error
    const plazoErrorMessage = $('#plazo-error-message'); 
    
    // --- LÓGICA DE BLOQUEO CRÍTICA ---
    function validateAndBlockSave() {
        const isCitaMode = esCitaSwitch.is(':checked'); 
        plazoErrorMessage.hide(); // Ocultar por defecto antes de validar

        if (!isCitaMode) {
            return true;
        }

        const selectedValues = baremosSelect.val() || [];
        const hasRestricted = selectedValues.some(id => baremosRestringidosIDs.includes(id));

        if (hasRestricted) {
            // ** BLOQUEA EL GUARDADO y MUESTRA el mensaje **
            plazoErrorMessage.show();
            return false; 
        } else {
            return true;
        }
    }
    // ----------------------------------

    // Función para manejar el estado del formulario (Siniestro/Cita)
    function updateTipoRegistro() {
        const isCitaMode = esCitaSwitch.is(':checked');
        
        if (isCitaMode) {
            esCitaHidden.val(1);
            tipoRegistroLabel.removeClass('bg-danger').addClass('bg-warning').text('CITA (Reserva)');
            citaWarning.show();
        } else {
            esCitaHidden.val(0);
            tipoRegistroLabel.removeClass('bg-warning').addClass('bg-danger').text('SINIESTRO (Uso Inmediato)');
            citaWarning.hide();
        }
        
        filterBaremosForTipoRegistro(isCitaMode);
        // valida al cambiar el modo
        
        validateAndBlockSave(); 
    }
    
    // Función para filtrar dinámicamente las opciones del Select2
    function filterBaremosForTipoRegistro(isCitaMode) {
        const selectedValues = baremosSelect.val() || [];
        const newValidValues = [];
        
        baremosSelect.find('option').remove();
        
        for (const baremoId in baremosTotales) {
            const info = baremosInfo[baremoId];
            
            let shouldInclude = false; 

            if (isCitaMode) {
                // In Cita mode: show baremos that HAVE waiting periods (both fulfilled and pending)
                if (info.has_plazo_ever === true) {
                    shouldInclude = true;
                }
            } else {
                // In Siniestro mode: show baremos that either DON'T have waiting periods OR have fulfilled waiting periods
                if (info.has_plazo_ever === false || (info.has_plazo_ever === true && !info.is_restricted_by_plazo)) {
                    shouldInclude = true;
                }
            }

            if (shouldInclude) {
                const text = baremosTotales[baremoId];
                const option = new Option(text, baremoId, false, selectedValues.includes(baremoId)); 
                baremosSelect.append(option);
            }
            
            if (shouldInclude && selectedValues.includes(baremoId)) {
                 newValidValues.push(baremoId);
            }
        }
        
        // Comprobamos si Select2 fue inicializado antes de destruirlo.
        if (baremosSelect.data('select2')) { 
            baremosSelect.select2('destroy');
        }
        baremosSelect.select2({
            multiple: true,
            placeholder: 'Seleccione uno o más Baremos',
            allowClear: true,
            closeOnSelect: true,
            tags: false,
            tokenSeparators: [',', ' '],
            templateResult: function (data) {
                const isPlazoBaremo = data.text.includes('[CITA'); 
                if (isPlazoBaremo) {
                    return $('<span><i class="fas fa-clock text-warning me-2"></i>' + data.text + '</span>');
                }
                return data.text;
            }
        });
        
        // El 'change' debe disparar el listener de validación
        baremosSelect.val(newValidValues).trigger('change');
    }
    
    // Listeners y Ejecución Inicial
    esCitaSwitch.on('change', updateTipoRegistro);
    
    // Listener para la validación al cambiar la selección
    baremosSelect.on('change', function() {
        validateAndBlockSave(); 
    });
    
    // ** LISTENER CLAVE: Bloquea el guardado en el submit del formulario **
    form.on('beforeSubmit', function(e) {
        if (!validateAndBlockSave()) {
            e.preventDefault();
            baremosSelect.select2('close'); 
            // Desplazar la vista hacia el mensaje de error
            $('html, body').animate({
                scrollTop: plazoErrorMessage.offset().top - 100
            }, 500);
            return false; 
        }
        return true;
    });

    $(document).ready(function() {
        // Inicializar el estado y el Select2 al cargar
        updateTipoRegistro(); 
    });
JS
, \yii\web\View::POS_END); 
?>  
                
                                
                <div class="col-md-6 form-fields-section">
                    <div class="row g-3">
                        <div class="col-md-12" style="display: none;">
                             <?= $form->field($model, 'idclinica')->textInput(['value' => $afiliado->clinica_id]) ?>
                        </div>

                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-calendar-day"></i>
                            <?= $form->field($model, 'fecha')->textInput([
                                'type' => 'date',
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Seleccione la fecha',
                                'autocomplete' => 'off',
                                'value' => $model->isNewRecord ? date('Y-m-d') : Yii::$app->formatter->asDate($model->fecha, 'yyyy-MM-dd')
                            ])->label('Fecha del Evento de Salud') ?>
                        </div>
                        
                        <div class="col-md-6 field-with-icon">
                            <?= $form->field($model, 'hora')->textInput([
                                'type' => 'time', 
                                'class' => 'form-control form-control-lg'
                            ])->label('Hora del Evento de Salud') ?>
                        </div>
                        
                        <div class="col-md-12">
                            <?= $form->field($model, 'atendido')->dropDownList(
                                [0 => 'No', 1 => 'Sí'],
                                [
                                    'prompt' => 'Seleccione estado', 
                                    'class' => 'form-control form-control-lg'
                                ]
                            )->label('Atendido') ?>
                        </div>
                        
                        <div class="col-md-6 field-with-icon">
                            <?= $form->field($model, 'fecha_atencion')->textInput([
                                'type' => 'date',
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Seleccione la fecha',
                                'autocomplete' => 'off',
                                'value' => $model->isNewRecord ? date('Y-m-d') : Yii::$app->formatter->asDate($model->fecha, 'yyyy-MM-dd')
                            ])->label('Fecha de la ' . $terminoPrincipal)?>
                        </div>
                        
                        <div class="col-md-6 field-with-icon">
                            <?= $form->field($model, 'hora_atencion')->textInput([
                                'type' => 'time', 
                                'class' => 'form-control form-control-lg'
                            ])->label('Hora de la ' . $terminoPrincipal)?>
                        </div>
                        
                        <div class="col-md-12 field-with-icon">
                            <i class="fas fa-align-left"></i>
                            <?= $form->field($model, 'descripcion')->textarea([
                                'rows' => 3, 
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Describa los detalles...'
                            ])->label('Descripción de la '. $terminoPrincipal) ?>
                        </div>

                         <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="section-title">
                                        <i class="fas fa-camera"></i> Archivos de la Atención
                                    </div>
                                    
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <?= $form->field($model, 'imagenRecipeFile')->widget(\kartik\file\FileInput::classname(),[
                                                'options' => [
                                                    'accept' => 'image/*, application/pdf',
                                                ],
                                                'pluginOptions' => [
                                                    'theme' => 'fa5',
                                                    'browseClass' => 'btn btn-primary',
                                                    'removeClass' => 'btn btn-secondary',
                                                    'removeIcon' => '<i class="fas fa-trash"></i> ',
                                                    'showUpload' => false,
                                                    'showCancel' => false,
                                                    'showCaption' => true,
                                                    'previewFileType' => 'image',
                                                    'allowedFileExtensions' => ['jpg','jpeg','png','pdf'], 
                                                    'maxFileSize' => 10240, // 10MB
                                                    'dropZoneEnabled' => false,
                                                    'showClose' => false,
                                                    'browseLabel' => 'Subir Recipe',
                                                    'removeLabel' => 'Quitar',
                                                    'fileActionSettings' => [
                                                        'showZoom' => false,
                                                        'showDrag' => false,
                                                    ],
                                                    'previewSettings' => [
                                                        'image' => ['width' => '150px', 'height' => 'auto'],
                                                    ],
                                                    'layoutTemplates' => [
                                                        'main1' => "{preview}\n{remove}\n{upload}\n{browse}\n{caption}",
                                                        'main2' => "{preview}\n{remove}\n{upload}\n{browse}\n{caption}",
                                                    ],
                                                ],
                                            ])->label('Récipe');
                                            ?>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <?= $form->field($model, 'imagenInformeFile')->widget(\kartik\file\FileInput::classname(),[
                                                'options' => [
                                                    'accept' => 'image/*, application/pdf',
                                                ],
                                                'pluginOptions' => [
                                                    'theme' => 'fa5',
                                                    'browseClass' => 'btn btn-primary',
                                                    'removeClass' => 'btn btn-secondary',
                                                    'removeIcon' => '<i class="fas fa-trash"></i> ',
                                                    'previewFileType' => 'image',
                                                    'showUpload' => false,
                                                    'showCancel' => false,
                                                    'showCaption' => true,
                                                    'allowedFileExtensions' => ['jpg','jpeg','png','pdf'], 
                                                    'maxFileSize' => 10240, // 10MB
                                                    'dropZoneEnabled' => false,
                                                    'showClose' => false,
                                                    'browseLabel' => 'Subir Informe Médico',
                                                    'removeLabel' => 'Quitar',
                                                    'fileActionSettings' => [
                                                        'showZoom' => false,
                                                        'showDrag' => false,
                                                    ],
                                                    'previewSettings' => [
                                                        'image' => ['width' => '150px', 'height' => 'auto'],
                                                    ],
                                                    'layoutTemplates' => [
                                                        'main1' => "{preview}\n{remove}\n{upload}\n{browse}\n{caption}",
                                                        'main2' => "{preview}\n{remove}\n{upload}\n{browse}\n{caption}",
                                                    ],
                                                ],
                                            ])->label('Informe Médico');
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group text-end mt-4">
                            <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success btn-lg me-2']) ?>
                            <?= Html::a('<i class="fas fa-times"></i> Cancelar', ['index', 'user_id' => $afiliado->id], ['class' => 'btn btn-warning btn-lg me-2']); ?>
                            <?php if ($model->isNewRecord): ?>
                                <?= Html::a('<i class="fas fa-eraser"></i> Limpiar', ['create', 'user_id' => $afiliado->id], ['class' => 'btn btn-outline-dark btn-lg']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
               <div class="col-md-6">
                    <div class="ms-panel">
                        <div class="ms-panel-header">
                            <h3 class="section-title">
                                <i class="fas fa-user text-blue-600"></i> Datos del Afiliado
                            </h3>
                        </div>
                        <div class="ms-panel-body">
                            <?= Html::button(
                                '<i class="fas fa-eye mr-2"></i> Ver Detalles del Afiliado',
                                [
                                    'class' => 'btn btn-success btn-lg w-100',
                                    'id' => 'btn-abrir-afiliado-modal', // <-- ID para que el JS lo encuentre
                                    'type' => 'button' 
                                    
                                ]
                            ) ?>
                        </div>
                    </div>
                </div>

                <?php
               
                Modal::begin([
                    'title' => '<h4>Detalles del Afiliado <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>',
                    'id' => 'afiliado-modal', 
                    'size' => Modal::SIZE_LARGE, 
                    'options' => [
                        'tabindex' => false, 
                        'class' => 'fade', // Necesario para la animación
                        'role' => 'dialog', 
                    ],
                  
                    'dialogOptions' => ['class' => 'modal-dialog-centered'], 
                ]);

                // ESTE ES EL CONTENIDO QUE SE MOSTRARÁ DENTRO DE LA VENTANA MODAL
                echo $this->render('/user-datos/view', ['model' => $afiliado]); 

                Modal::end();
                ?>


            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php
// Codificar la información de los baremos para usarla en JavaScript
$baremosInfoJson = json_encode($baremosInfo);

// JavaScript para calcular la suma de los baremos seleccionados y generar la tabla
$js = <<<JS
// Información de los baremos, incluyendo el precio
var baremosInfo = $baremosInfoJson; 

// Función para calcular el total y renderizar la tabla
function calcularTotalYTabla() {
    var baremosSeleccionados = $('#baremos-select').val();
    var total = 0.00;
    var tablaHtml = '';
    
    // 1. Ocultar contenedores si no hay selección
    if (!baremosSeleccionados || baremosSeleccionados.length === 0) {
        $('#costo-total-container').hide();
        $('#baremos-tabla-container').hide();
        $('#costo-total-input').val('0.00'); // FIXED: Use the correct ID
        // También ocultar la advertencia
        $('#cobertura-warning').hide().empty(); 
        return;
    }

    // 2. Procesar y construir la tabla
    baremosSeleccionados.forEach(function(baremoId) {
        var item = baremosInfo[baremoId];
        
        if (item && item.precio !== undefined) {
            var precio = parseFloat(item.precio);
            total += precio;
            
            // Construir la cadena de restricciones
            var restricciones = [];
            // Si el baremo fue excluido por plazo o límite, no estará en baremosInfo, 
            // pero el siguiente código es para mostrar la info de los seleccionados que sí están disponibles.
            if (item.plazo_espera) {
                // NOTA: La lógica PHP ya determinó que el plazo fue cumplido si aparece aquí.
                restricciones.push('Plazo: ' + item.plazo_espera + ' meses');
            }
            if (item.cantidad_limite > 0) {
                restricciones.push('Límite: ' + item.veces_usado + '/' + item.cantidad_limite + ' usos');
            }
            var restriccionesHtml = restricciones.join('<br>');
            
            // Construir la fila de la tabla
            tablaHtml += '<tr>';
            tablaHtml += '<td>' + item.nombre + '</td>';
            tablaHtml += '<td>' + item.area + '</td>';
            // Se muestra el estado de las restricciones para referencia
            tablaHtml += '<td class="text-center">' + (restriccionesHtml || 'Ninguna') + '</td>'; 
            tablaHtml += '<td class="cost-col">$' + precio.toFixed(2) + '</td>';
            tablaHtml += '</tr>';
        }
    });

    // 3. Renderizar la tabla y el total
    $('#baremos-tabla-body').html(tablaHtml);
    $('#baremos-tabla-container').show();
    
    $('#costo-total-value').html('$' + total.toFixed(2));
    $('#costo-total-input').val(total.toFixed(2)); // FIXED: Use the correct ID
    $('#costo-total-container').show();
    
    // 4. Verificar límite disponible (REEMPLAZO DE ALERT)
    var totalDisponible = $totalDisponible; // PHP variable
    var warningContainer = $('#cobertura-warning');
    
    warningContainer.hide().empty(); // Resetear advertencia
    
    if (total > totalDisponible) {
        var warningMessage = '¡Advertencia! El costo total ($' + total.toFixed(2) + ') supera el total disponible ($' + totalDisponible.toFixed(2) + ') del afiliado. La suma no será cubierta en su totalidad.';
        console.warn(warningMessage);
        // Mostrar el mensaje de advertencia en el contenedor
        warningContainer.html('<i class="fas fa-exclamation-triangle me-2"></i>' + warningMessage).show();
    }
}

// Evento de cambio para la selección de baremos
$('#baremos-select').on('change', function() {
    calcularTotalYTabla();
});

// Calcular y mostrar al cargar la página si hay baremos seleccionados
$(document).ready(function() {
    if ($('#baremos-select').val() && $('#baremos-select').val().length > 0) {
        calcularTotalYTabla();
    }
});
JS;

$this->registerJs($js, View::POS_READY);
?>

<?php
// Usamos View::POS_END para asegurar que este script se ejecuta lo último de lo último, 
// después de que todos los assets y scripts del DOM estén completamente cargados.

$this->registerJs(<<<JS
    $('#btn-abrir-afiliado-modal').on('click', function(e) {
        e.preventDefault();
        
        // Retraso de 50ms para evitar scripts de cierre
        setTimeout(function() {
            $('#afiliado-modal').modal('show');
        }, 50); 
    });
JS
, View::POS_END);
?>
