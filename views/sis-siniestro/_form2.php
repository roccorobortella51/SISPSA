<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\web\View;
use yii\bootstrap4\Modal;

$this->registerCssFile(Yii::getAlias('@web') . "/css/_formsiniestros.css", ['position' => View::POS_HEAD]);

/* @var $this yii\web\View */
/* @var $model app\models\SisSiniestro */
/* @var $form yii\widgets\ActiveForm */
/* @var $afiliado app\models\UserDatos */

// Obtener información del plan del afiliado
if (is_object($afiliado)) {
    $planId = $afiliado->plan_id;
    $afiliadoObj = $afiliado;
} elseif (is_array($afiliado) && isset($afiliado['plan_id'])) {
    $planId = $afiliado['plan_id'];
    $afiliadoObj = (object)$afiliado;
} else {
    $planId = (int)$afiliado;
    $afiliadoObj = \app\models\UserDatos::findOne($planId);
}
$plan = \app\models\Planes::findOne($planId);
$afiliado = $afiliadoObj;
$precioPlan = $plan ? $plan->cobertura : 0;

// Obtener la sumatoria de siniestros del afiliado
$afiliadoId = is_object($afiliado) ? $afiliado->id : (is_array($afiliado) && isset($afiliado['id']) ? $afiliado['id'] : (int)$afiliado);
$sumatoriaSiniestros = \app\models\SisSiniestro::find()
    ->where(['iduser' => (int)$afiliadoId])
    ->andWhere(['not', ['costo_total' => null]])
    ->sum('costo_total');

$totalDisponible = $precioPlan - $sumatoriaSiniestros;

// OBTENER el parámetro 'es_cita'
$esCita = (int)Yii::$app->request->get('es_cita', 0);

// Definir los modos y términos
$esCitaMode = ($esCita === 1);
$terminoPrincipal = $esCitaMode ? 'Cita' : 'Atención';
$tituloSeccion = 'Datos de la ' . $terminoPrincipal;

// Obtener el contrato activo del afiliado
$afiliadoIdContrato = is_object($afiliado) ? $afiliado->id : (is_array($afiliado) && isset($afiliado['id']) ? $afiliado['id'] : (int)$afiliado);
$contrato = \app\models\Contratos::find()
    ->where(['user_id' => $afiliadoIdContrato])
    ->andWhere(['estatus' => 'Activo'])
    ->orderBy(['created_at' => SORT_DESC])
    ->one();

// Get baremos data for historical section
$afiliadoIdHist = is_object($afiliado) ? $afiliado->id : (is_array($afiliado) && isset($afiliado['id']) ? $afiliado['id'] : (int)$afiliado);
$baremosUtilizados = \app\models\SisSiniestroBaremo::find()
    ->joinWith(['siniestro', 'baremo'])
    ->where(['sis_siniestro.iduser' => $afiliadoIdHist])
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
<div class="sis-siniestro-form">
    <?php $form = ActiveForm::begin(); ?>

    <!-- ===== SECTION 1: AFILIADO & PLAN CONTEXT ===== -->
    <div class="ms-panel mb-4">
        <div class="ms-panel-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white !important;">
            <h3 class="large-title section-title-white mb-0" style="color: white !important;">
                <i class="fas fa-user-circle me-2" style="color: white !important;"></i> Información del Afiliado y Plan
            </h3>
        </div>
        <div class="ms-panel-body">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                            <i class="fas fa-user me-2"></i> Datos del Afiliado
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <p><strong>Nombre:</strong> <?= Html::encode(trim($afiliado->nombres . ' ' . $afiliado->apellidos)) ?></p>
                                    <p><strong>Código:</strong> <?= Html::encode($afiliado->codigo ?? 'N/A') ?></p>
                                    <p><strong>Cédula:</strong> <?php echo Html::encode(($afiliado->tipo_cedula ?? 'V') . '-' . str_pad($afiliado->cedula, 8, '0', STR_PAD_LEFT)); ?></p>
                                    <p><strong>Teléfono:</strong> <?= Html::encode($afiliado->telefono) ?></p>
                                    <p><strong>Email:</strong> <?= Html::encode($afiliado->email) ?></p>
                                </div>
                                <div class="col-md-4 d-flex align-items-center justify-content-center">
                                    <?= Html::button('<i class="fas fa-eye mr-2"></i> Ver Detalles', ['class' => 'btn btn-success', 'id' => 'btn-abrir-afiliado-modal', 'type' => 'button']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                            <i class="fas fa-file-contract me-2"></i> Información del Plan y Límites
                        </div>
                        <div class="card-body">
                            <div class="plan-info-summary">
                                <div class="plan-info-item"><span class="plan-info-label">Plan:</span><span class="plan-info-value"><?= $afiliado->plan->nombre ?? 'Sin plan' ?></span></div>
                                <div class="plan-info-item"><span class="plan-info-label">Cobertura del Plan:</span><span class="plan-info-value">$<?= number_format($precioPlan, 2) ?></span></div>
                                <div class="plan-info-item"><span class="plan-info-label">Total Utilizado:</span><span class="plan-info-value">$<?= number_format($sumatoriaSiniestros ?? 0, 2) ?></span></div>
                                <div class="plan-info-item plan-info-total"><span class="plan-info-label">Total Disponible:</span><span class="plan-info-value">$<?= number_format($totalDisponible, 2) ?></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== SECTION 2: HISTORIAL (COLLAPSIBLE) ===== -->
    <div class="ms-panel mb-4">
        <div class="ms-panel-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); cursor: pointer; color: white !important;" id="historial-toggle-header">
            <h3 class="large-title section-title-white mb-0" style="color: white !important;">
                <i class="fas fa-history me-2" style="color: white !important;"></i> Historial de Servicios
                <small class="ms-2" style="color: rgba(255, 255, 255, 0.8) !important; font-size: 16px !important;">(<?= count($baremosCitas) + count($baremosSiniestros) ?> registros)</small>
                <span class="float-right"><i class="fas fa-chevron-down" id="historial-chevron" style="color: white !important;"></i></span>
            </h3>
        </div>
        <div class="ms-panel-body" id="historial-content" style="display: none;">
            <?php if ($esCita == 1 && !empty($baremosCitas)): ?>
                <div class="card mb-4">
                    <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;"><i class="fas fa-calendar-check me-2"></i> Citas Realizadas <span class="badge badge-success float-right"><?= count($baremosCitas) ?></span></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Área</th>
                                        <th>Servicio</th>
                                        <th>Descripción</th>
                                        <th>Precio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($baremosCitas as $cita): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($cita['fecha'])) ?></td>
                                            <td><?= $cita['area'] ?></td>
                                            <td><?= $cita['nombre_servicio'] ?></td>
                                            <td><?= $cita['descripcion'] ?: 'Sin descripción' ?></td>
                                            <td>$<?= number_format($cita['precio'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($esCita == 0 && !empty($baremosSiniestros)): ?>
                <div class="card mb-4">
                    <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;"><i class="fas fa-file-medical me-2"></i> Atenciones Médicas Registradas <span class="badge badge-info float-right"><?= count($baremosSiniestros) ?></span></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Área</th>
                                        <th>Servicio</th>
                                        <th>Descripción</th>
                                        <th>Precio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($baremosSiniestros as $siniestro): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($siniestro['fecha'])) ?></td>
                                            <td><?= $siniestro['area'] ?></td>
                                            <td><?= $siniestro['nombre_servicio'] ?></td>
                                            <td><?= $siniestro['descripcion'] ?: 'Sin descripción' ?></td>
                                            <td>$<?= number_format($siniestro['precio'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;"><i class="fas fa-chart-bar me-2"></i> Resumen Estadístico</div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="stats-number"><?= count($baremosCitas) + count($baremosSiniestros) ?></div>
                            <div class="stats-label">Total Baremos</div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-number"><?= count($baremosCitas) ?></div>
                            <div class="stats-label">Citas</div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-number"><?= count($baremosSiniestros) ?></div>
                            <div class="stats-label">Atenciones</div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-number">$<?= number_format(array_sum(array_column(array_merge($baremosCitas, $baremosSiniestros), 'precio')), 2) ?></div>
                            <div class="stats-label">Total Utilizado</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== SECTION 3: DATOS DE LA ATENCIÓN/CITA ===== -->
    <div class="ms-panel mb-4">
        <div class="ms-panel-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
            <h3 class="large-title section-title-white mb-0"><i class="fas fa-file-alt me-2"></i> <?= $tituloSeccion ?> <?php if ($esCitaMode): ?><span class="badge bg-warning ms-2">Modo Cita</span><?php else: ?><span class="badge bg-info ms-2">Modo Atención</span><?php endif; ?></h3>
        </div>
        <div class="ms-panel-body">
            <div style="display: none;"><?= $form->field($model, 'idclinica')->textInput(['value' => $afiliado->clinica_id]) ?></div>

            <div class="card mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;"><i class="fas fa-info-circle me-2"></i> Información Básica</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6"><?= $form->field($model, 'fecha')->textInput(['type' => 'date', 'class' => 'form-control form-control-lg', 'value' => $model->isNewRecord ? date('Y-m-d') : Yii::$app->formatter->asDate($model->fecha, 'yyyy-MM-dd')])->label('Fecha del Evento de Salud') ?></div>
                        <div class="col-md-6"><?= $form->field($model, 'hora')->textInput(['type' => 'time', 'class' => 'form-control form-control-lg'])->label('Hora del Evento de Salud') ?></div>
                        <div class="col-md-12"><?= $form->field($model, 'atendido')->dropDownList([0 => 'No', 1 => 'Sí'], ['prompt' => 'Seleccione estado', 'class' => 'form-control form-control-lg'])->label('¿Fue atendido?') ?></div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;"><i class="fas fa-stethoscope me-2"></i> Detalles de la <?= $terminoPrincipal ?></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6"><?= $form->field($model, 'fecha_atencion')->textInput(['type' => 'date', 'class' => 'form-control form-control-lg', 'value' => $model->isNewRecord ? date('Y-m-d') : Yii::$app->formatter->asDate($model->fecha_atencion, 'yyyy-MM-dd')])->label('Fecha de la ' . $terminoPrincipal) ?></div>
                        <div class="col-md-6"><?= $form->field($model, 'hora_atencion')->textInput(['type' => 'time', 'class' => 'form-control form-control-lg'])->label('Hora de la ' . $terminoPrincipal) ?></div>
                        <div class="col-md-12"><?= $form->field($model, 'descripcion')->textarea(['rows' => 3, 'class' => 'form-control form-control-lg', 'placeholder' => 'Describa los detalles de la ' . strtolower($terminoPrincipal) . '...'])->label('Descripción de la ' . $terminoPrincipal) ?></div>
                        <div class="col-md-6"><?= $form->field($model, 'admission_analyst')->textInput(['class' => 'form-control form-control-lg', 'placeholder' => 'Nombre del analista de admisión'])->label('Analista de Admisión') ?></div>
                    </div>
                </div>
            </div>

            <!-- ===== DOCUMENTACIÓN ADJUNTA ===== -->
            <div class="card mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;"><i class="fas fa-paperclip me-2"></i> Documentación Adjunta</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'imagenRecipeFile')->widget(\kartik\file\FileInput::classname(), [
                                'options' => ['accept' => 'image/*, application/pdf'],
                                'pluginOptions' => [
                                    'theme' => 'fa5',
                                    'browseClass' => 'btn btn-primary',
                                    'removeClass' => 'btn btn-secondary',
                                    'removeIcon' => '<i class="fas fa-trash"></i> ',
                                    'showUpload' => false,
                                    'showCancel' => false,
                                    'showCaption' => true,
                                    'previewFileType' => 'image',
                                    'allowedFileExtensions' => ['jpg', 'jpeg', 'png', 'pdf'],
                                    'maxFileSize' => 10240,
                                    'browseLabel' => 'Subir Recipe',
                                    'removeLabel' => 'Quitar',
                                    'layoutTemplates' => ['main1' => "{preview}\n{remove}\n{upload}\n{browse}\n{caption}", 'main2' => "{preview}\n{remove}\n{upload}\n{browse}\n{caption}"],
                                ],
                            ])->label('Récipe Médico'); ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'imagenInformeFile')->widget(\kartik\file\FileInput::classname(), [
                                'options' => ['accept' => 'image/*, application/pdf'],
                                'pluginOptions' => [
                                    'theme' => 'fa5',
                                    'browseClass' => 'btn btn-primary',
                                    'removeClass' => 'btn btn-secondary',
                                    'removeIcon' => '<i class="fas fa-trash"></i> ',
                                    'showUpload' => false,
                                    'showCancel' => false,
                                    'showCaption' => true,
                                    'previewFileType' => 'image',
                                    'allowedFileExtensions' => ['jpg', 'jpeg', 'png', 'pdf'],
                                    'maxFileSize' => 10240,
                                    'browseLabel' => 'Subir Informe Médico',
                                    'removeLabel' => 'Quitar',
                                    'layoutTemplates' => ['main1' => "{preview}\n{remove}\n{upload}\n{browse}\n{caption}", 'main2' => "{preview}\n{remove}\n{upload}\n{browse}\n{caption}"],
                                ],
                            ])->label('Informe Médico'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== INCLUDE DOCUMENTOS ADICIONALES ===== -->
    <?= $this->render('_form_documentos_adicionales', ['model' => $model, 'form' => $form]) ?>

    <!-- ===== SECTION 4: SELECCIÓN DE SERVICIOS MÉDICOS ===== -->
    <?php
    // Initialize variables
    $baremosTotales = [];
    $baremosHtml = [];
    $baremosInfo = [];
    $baremosRestringidosIDs = [];

    $baremosForzados = [];
    $baremosSinPlazo = [];
    $baremosConPlazoCumplido = [];
    $baremosPendientesPlazo = [];
    $baremosAgotados = [];

    // NEW: Array for available services info
    $baremosDisponiblesInfo = [];

    // Calculate baremos data
    if ($contrato && $contrato->estatus === 'Activo') {
        $query = \app\models\PlanesItemsCobertura::find()
            ->joinWith('baremo')
            ->joinWith('plan')
            ->joinWith('baremo.area')
            ->where(['planes.clinica_id' => $afiliado->clinica_id])
            ->andWhere(['baremo.estatus' => 'Activo'])
            ->andWhere(['planes.id' => $afiliado->plan_id]);

        if ($esCitaMode) {
            // Modo Cita: Mostrar solo servicios con restricciones
            $query->andWhere([
                'or',
                ['>', 'planes_items_cobertura.plazo_espera', 0],
                ['>', 'planes_items_cobertura.cantidad_limite', 0]
            ]);
        } else {
            // MODIFICACIÓN: Modo Siniestro - INCLUIR TODOS los servicios sin filtrar
        }

        $planesItemsCobertura = $query->all();

        $selectedBaremos = [];
        if (!$model->isNewRecord) {
            $baremosDirectos = (new \yii\db\Query())
                ->select(['baremo_id'])
                ->from('sis_siniestro_baremo')
                ->where(['siniestro_id' => $model->id])
                ->column();

            if (!empty($baremosDirectos)) {
                $selectedBaremos = $baremosDirectos;
            }
        }

        $fechaActual = new \DateTime();

        foreach ($planesItemsCobertura as $item) {
            // Initialize variables at the start of each iteration
            $precioBaremo = 0;
            $area = 'Sin área';
            $servicio = '';
            $descripcion = '';
            $textoPlano = '';
            $isRestrictedByPlazo = false;

            if ($item->baremo) {
                $hasPlazoEver = (!empty($item->plazo_espera) && $item->plazo_espera > 0);
                $precioBaremo = $item->baremo->precio ?? 0;
                $area = $item->baremo->area ? $item->baremo->area->nombre : 'Sin área';
                $servicio = $item->baremo->nombre_servicio;
                $descripcion = $item->baremo->descripcion ?? '';

                // TEXT FOR DROPDOWN VALUE
                $textoPlano = $servicio . " (" . $area . ")";
                if (!empty($descripcion)) {
                    $textoPlano .= " - " . $descripcion;
                }

                $hasPlazoEver = (!empty($item->plazo_espera) && $item->plazo_espera > 0);

                // Count usage differently based on mode
                $queryCount = \app\models\SisSiniestroBaremo::find()
                    ->joinWith('siniestro')
                    ->where(['baremo_id' => $item->baremo_id])
                    ->andWhere(['iduser' => $afiliado->id]);

                // For Siniestro mode, only count actual siniestros (not citas)
                if (!$esCitaMode) {
                    $queryCount->andWhere(['sis_siniestro.es_cita' => 0]);
                }

                $vecesUsado = $queryCount->count();

                // Verificar si excede el límite (siempre verificar, incluso si cantidad_limite = 0)
                $excedeLimite = false;
                if ($item->cantidad_limite !== null && $item->cantidad_limite > 0) {
                    // Si tiene un límite positivo, verificamos si se alcanzó
                    if ($vecesUsado >= $item->cantidad_limite) {
                        $excedeLimite = true;
                    }
                }

                // Verificar si este baremo está entre los seleccionados (solo para update)
                $esBaremoGuardado = !$model->isNewRecord && in_array($item->baremo_id, $selectedBaremos);

                // Lógica de plazo de espera
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

                // MODO SINIESTRO MODIFICADO: Clasificar servicios según su estado real

                // Determinar si el servicio debe incluirse en el dropdown (para selección)
                $debeIncluirse = true;

                // CLASIFICACIÓN POR ESTADO
                if ($excedeLimite) {
                    // Servicio AGOTADO - ha alcanzado su límite de uso
                    $baremosAgotados[$item->baremo_id] = $textoPlano;
                    $baremosInfo[$item->baremo_id]['es_agotado'] = true;

                    // Si NO es un servicio guardado históricamente, no debe ser seleccionable
                    if (!$esBaremoGuardado) {
                        $debeIncluirse = false;
                    }
                } elseif ($isRestrictedByPlazo) {
                    // Servicio RESTRINGIDO - aún en período de espera
                    $baremosPendientesPlazo[$item->baremo_id] = $textoPlano;
                    $baremosRestringidosIDs[] = $item->baremo_id;
                    $baremosInfo[$item->baremo_id]['is_restricted_by_plazo'] = true;

                    // Calcular tiempo restante para mostrar
                    if ($contrato) {
                        $fechaContratoIni = new \DateTime($contrato->fecha_ini);
                        $plazoRequerido = (int)$item->plazo_espera;
                        $fechaTarget = clone $fechaContratoIni;
                        $fechaTarget->modify("+{$plazoRequerido} months");
                        $diff = $fechaActual->diff($fechaTarget);

                        $mesesRestantes = ($diff->y * 12) + $diff->m;
                        $diasRestantes = $diff->d;

                        // Guardar información de tiempo restante
                        $baremosInfo[$item->baremo_id]['remaining_months'] = $mesesRestantes;
                        $baremosInfo[$item->baremo_id]['remaining_days'] = $diasRestantes;

                        // Build text representation
                        if ($mesesRestantes > 0 && $diasRestantes > 0) {
                            $tiempoRestanteTexto = $mesesRestantes . " mes" . ($mesesRestantes > 1 ? "es" : "") .
                                " y " . $diasRestantes . " día" . ($diasRestantes > 1 ? "s" : "");
                        } elseif ($mesesRestantes > 0) {
                            $tiempoRestanteTexto = $mesesRestantes . " mes" . ($mesesRestantes > 1 ? "es" : "");
                        } elseif ($diasRestantes > 0) {
                            $tiempoRestanteTexto = $diasRestantes . " día" . ($diasRestantes > 1 ? "s" : "");
                        } else {
                            $tiempoRestanteTexto = "Próximamente";
                        }
                        $baremosInfo[$item->baremo_id]['remaining_text'] = $tiempoRestanteTexto;
                    }

                    // Si NO es un servicio guardado históricamente, no debe ser seleccionable
                    if (!$esBaremoGuardado) {
                        $debeIncluirse = false;
                    }
                } else {
                    // Servicio DISPONIBLE - cumple todos los criterios
                    $baremosSinPlazo[$item->baremo_id] = $textoPlano;

                    // NEW: Add to disponibles info array
                    $baremosDisponiblesInfo[$item->baremo_id] = [
                        'nombre' => $servicio,
                        'area' => $area,
                        'descripcion' => $descripcion,
                        'precio' => $precioBaremo,
                        'cantidad_limite' => (int)$item->cantidad_limite,
                        'veces_usado' => (int)$vecesUsado,
                        'disponibles' => $item->cantidad_limite > 0 ? $item->cantidad_limite - $vecesUsado : 'Ilimitado',
                    ];
                }

                // Guardar información del servicio para el template HTML
                $baremosInfo[$item->baremo_id] = array_merge($baremosInfo[$item->baremo_id] ?? [], [
                    'nombre' => $servicio,
                    'area' => $area,
                    'descripcion' => $descripcion,
                    'plazo_espera' => $item->plazo_espera,
                    'cantidad_limite' => (int)$item->cantidad_limite,
                    'veces_usado' => (int)$vecesUsado,
                    'precio' => $precioBaremo,
                    'has_plazo_ever' => $hasPlazoEver,
                    'excede_limite' => $excedeLimite,
                    'es_historico' => $esBaremoGuardado,
                ]);

                // TEXT FOR DROPDOWN VALUE (recalculate in case it changed)
                $textoPlano = $servicio . " (" . $area . ")";
                if (!empty($descripcion)) {
                    $textoPlano .= " - " . $descripcion;
                }

                // AVAILABILITY CALCULATION
                $disponibles = 0;
                $availabilityClass = '';
                $availabilityText = '';

                if ($item->cantidad_limite !== null && $item->cantidad_limite > 0) {
                    $disponibles = $item->cantidad_limite - $vecesUsado;

                    if ($disponibles <= 0) {
                        $availabilityClass = 'none';
                        $availabilityText = 'Agotado';
                    } elseif ($disponibles == 1) {
                        $availabilityClass = 'low';
                        $availabilityText = '1 de ' . $item->cantidad_limite . ' disponible';
                    } else {
                        $availabilityClass = '';
                        $availabilityText = $disponibles . ' de ' . $item->cantidad_limite . ' disponibles';
                    }
                } else {
                    $availabilityClass = '';
                    $availabilityText = 'Sin límite';
                }

                // HTML para el template del dropdown
                $htmlFormateado = "<div class='baremo-dropdown-option'>";
                $htmlFormateado .= "<div class='baremo-first-row'>";
                $htmlFormateado .= "<div class='baremo-content-main'>";
                $htmlFormateado .= "<div class='baremo-area'>";
                $htmlFormateado .= "<div class='baremo-area-label'>Área</div>";
                $htmlFormateado .= "<div class='baremo-area-value'>" . $area . "</div>";
                $htmlFormateado .= "</div>";
                $htmlFormateado .= "<div class='baremo-servicio'>";
                $htmlFormateado .= "<div class='baremo-servicio-label'>Servicio</div>";
                $htmlFormateado .= "<div class='baremo-servicio-value'>" . $servicio . "</div>";
                $htmlFormateado .= "</div>";
                $htmlFormateado .= "<div class='baremo-descripcion'>";
                $htmlFormateado .= "<div class='baremo-descripcion-label'>Descripción</div>";
                $htmlFormateado .= "<div class='baremo-descripcion-value' title='" . htmlspecialchars($descripcion ?: 'Sin descripción', ENT_QUOTES) . "'>" . ($descripcion ?: 'Sin descripción') . "</div>";
                $htmlFormateado .= "</div>";
                $htmlFormateado .= "</div>"; // Close content-main

                // Status badge
                $htmlFormateado .= "<div class='baremo-status'>";
                if ($esBaremoGuardado && !$debeIncluirse) {
                    $htmlFormateado .= "<span class='historico'>Histórico</span>";
                } elseif ($isRestrictedByPlazo) {
                    $htmlFormateado .= "<span class='restringido'>Restringido</span>";
                } elseif ($excedeLimite) {
                    $htmlFormateado .= "<span class='agotado'>Agotado</span>";
                } else {
                    $htmlFormateado .= "<span class='disponible'>Disponible</span>";
                }
                $htmlFormateado .= "</div>"; // Close status
                $htmlFormateado .= "</div>"; // Close first-row

                // ROW 2: Price and Availability
                $htmlFormateado .= "<div class='baremo-second-row'>";
                $htmlFormateado .= "<div class='baremo-price-container'>";
                $htmlFormateado .= "<span class='baremo-price'>" . number_format($precioBaremo, 2) . "</span>";
                $htmlFormateado .= "</div>";

                // For restricted items, show waiting period instead of availability
                if ($isRestrictedByPlazo && isset($baremosInfo[$item->baremo_id]['remaining_text'])) {
                    $htmlFormateado .= "<div class='baremo-waiting-period'>";
                    $htmlFormateado .= "<i class='fas fa-clock me-1'></i>";
                    $htmlFormateado .= "<span>Disponible en " . $baremosInfo[$item->baremo_id]['remaining_text'] . "</span>";
                    $htmlFormateado .= "</div>";
                } else {
                    $htmlFormateado .= "<div class='baremo-availability " . $availabilityClass . "'>" . $availabilityText . "</div>";
                }
                $htmlFormateado .= "</div>"; // Close second-row
                $htmlFormateado .= "</div>"; // Close baremo-dropdown-option

                // Si el servicio debe incluirse (es seleccionable O es histórico), agregar al HTML
                if ($debeIncluirse || $esBaremoGuardado) {
                    $baremosHtml[$item->baremo_id] = $htmlFormateado;

                    // Add to appropriate totals array for selection
                    if (!$excedeLimite && !$isRestrictedByPlazo) {
                        $baremosTotales[$item->baremo_id] = $textoPlano;
                    } elseif ($esBaremoGuardado) {
                        // For historical items that are no longer available, add to forzados
                        $baremosForzados[$item->baremo_id] = $textoPlano;
                        $baremosTotales[$item->baremo_id] = $textoPlano;
                    }
                }
            }
        }

        // Merge all selectable items
        $baremosTotales = $baremosForzados + $baremosSinPlazo;
    }
    ?>

    <div class="ms-panel mb-4">
        <div class="combined-section-card">
            <div class="d-flex align-items-center justify-content-between mb-0">
                <div class="d-flex align-items-center">
                    <div class="section-icon-white me-3">
                        <i class="fas fa-stethoscope fa-2x" style="color: white;"></i>
                    </div>
                    <div>
                        <h3 class="section-title-white mb-0">Selección de Servicios Médicos</h3>
                        <p class="text-white-50 mb-0 mt-1" style="color: white !important; opacity: 0.9 !important;">Seleccione los servicios médicos aplicados en esta <?= strtolower($terminoPrincipal) ?></p>
                    </div>
                </div>
                <div class="section-badge-white d-flex flex-wrap gap-2 align-items-center">
                    <!-- MODIFIED: Disponibles badge is now clickable -->
                    <span class="badge badge-pill stat-badge disponible clickable-badge"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="<?= htmlspecialchars('Servicios que cumplen todos los criterios y pueden ser seleccionados ahora mismo. Click para ver detalles.', ENT_QUOTES) ?>">
                        <i class="fas fa-check-circle me-3"></i>
                        <?php
                        $totalDisponibles = count($baremosSinPlazo);
                        echo $totalDisponibles;
                        ?> Disponibles
                    </span>

                    <?php if (count($baremosPendientesPlazo) > 0): ?>
                        <span class="badge badge-pill stat-badge restringido clickable-badge"
                            data-toggle="tooltip"
                            data-placement="top"
                            title="<?= htmlspecialchars('Servicios con plazo de espera pendiente. No disponibles para selección. Click para ver detalles.', ENT_QUOTES) ?>">
                            <i class="fas fa-clock me-3"></i>
                            <span>
                                <?= count($baremosPendientesPlazo) ?> Restringidos
                            </span>
                        </span>
                    <?php endif; ?>

                    <?php
                    $totalAgotados = count($baremosAgotados);
                    if ($totalAgotados > 0):
                    ?>
                        <span class="badge badge-pill stat-badge agotado clickable-badge"
                            data-toggle="tooltip"
                            data-placement="top"
                            title="<?= htmlspecialchars('Servicios que han alcanzado su límite máximo de usos. No disponibles para selección. Click para ver detalles.', ENT_QUOTES) ?>">
                            <i class="fas fa-ban me-3"></i>
                            <span>
                                <?= $totalAgotados ?> Agotados
                            </span>
                        </span>
                    <?php endif; ?>

                    <?php if (!empty($baremosForzados)): ?>
                        <span class="badge badge-pill stat-badge historico"
                            data-toggle="tooltip"
                            data-placement="top"
                            title="<?= htmlspecialchars('Servicios previamente guardados que ya no cumplen criterios actuales, mostrados solo para referencia histórica.', ENT_QUOTES) ?>">
                            <i class="fas fa-history me-3"></i>
                            <span>
                                <?= count($baremosForzados) ?> Históricos
                            </span>
                        </span>
                    <?php endif; ?>

                    <span class="badge badge-pill stat-badge total"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="<?= htmlspecialchars('Cantidad total de servicios médicos incluidos en el plan, independientemente de su disponibilidad.', ENT_QUOTES) ?>">
                        <i class="fas fa-layer-group me-3"></i>
                        <span>
                            <?php
                            $totalGeneral = count($baremosForzados) + $totalAgotados + count($baremosPendientesPlazo) + count($baremosSinPlazo);
                            echo $totalGeneral;
                            ?> Total
                        </span>
                    </span>
                </div>
            </div>

            <?php if ($contrato && $contrato->estatus === 'Activo'): ?>
                <div class="baremo-combined-container">
                    <div class="field-with-icon baremo-select-container">
                        <?php
                        echo $form->field($model, 'idbaremo[]', [
                            'options' => ['class' => 'm-0'],
                            'template' => "{input}"
                        ])->widget(Select2::class, [
                            'data' => $baremosTotales,
                            'options' => [
                                'multiple' => true,
                                'value' => $selectedBaremos,
                                'placeholder' => 'Busca o selecciona los servicios del Baremo...',
                                'class' => 'form-control form-control-lg baremo-master-select',
                                'id' => 'baremos-select',
                                'style' => 'padding-left: 0px;'
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'closeOnSelect' => false,
                                'tags' => false,
                                'tokenSeparators' => [',', ' '],
                                'minimumInputLength' => 0,
                            ],
                        ])->label(false)->hint(false);
                        ?>
                    </div>

                    <div id="baremos-tabla-container" class="summary-container mt-4" style="display: none;">
                        <div class="summary-header">
                            <div class="d-flex align-items-center">
                                <div class="summary-icon">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                                <div>
                                    <h4 class="mb-1 text-dark">Resumen de servicios seleccionados</h4>
                                    <p class="text-muted mb-0">Detalle de costos y restricciones</p>
                                </div>
                            </div>
                            <div class="summary-total">
                                <span class="total-label">Total:</span>
                                <span class="total-amount" id="summary-total-amount">$0.00</span>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-summary">
                                <thead class="table-light">
                                    <tr>
                                        <th width="25%" class="text-start">Servicio</th>
                                        <th width="20%" class="text-start">Área</th>
                                        <th width="30%" class="text-start">Descripción</th>
                                        <th width="15%" class="text-center">Restricciones</th>
                                        <th width="10%" class="text-end">Costo</th>
                                    </tr>
                                </thead>
                                <tbody id="baremos-tabla-body">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="costo-total-container mt-4" id="costo-total-container" style="display: none;">
                        <div class="total-card">
                            <div class="total-content">
                                <div class="total-icon">
                                    <i class="fas fa-calculator"></i>
                                </div>
                                <div class="total-details">
                                    <div class="total-label">Total estimado</div>
                                    <div class="total-value" id="costo-total-value">$0.00</div>
                                    <div class="total-hint">Esta cantidad será descontada de la cobertura disponible</div>
                                </div>
                            </div>
                            <div class="total-actions">
                                <?= $form->field($model, 'costo_total')->hiddenInput(['id' => 'costo-total-input'])->label(false) ?>
                                <button type="button" class="btn btn-primary" id="recalculate-total">
                                    <i class="fas fa-redo me-1"></i> Recalcular
                                </button>
                            </div>
                        </div>

                        <div id="cobertura-warning" class="coverage-warning mt-3" style="display: none;">
                            <div class="warning-content">
                                <div class="warning-icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="warning-text">
                                    <strong>¡Advertencia!</strong> El costo total estimado supera la cobertura disponible del afiliado.
                                    <span id="cobertura-difference-text"></span>
                                </div>
                            </div>
                        </div>

                        <?php if ($esCitaMode): ?>
                            <div id="cita-warning" class="info-message mt-3">
                                <div class="info-content">
                                    <div class="info-icon">
                                        <i class="fas fa-info-circle"></i>
                                    </div>
                                    <div class="info-text">
                                        <strong>Registro como Cita:</strong> Los servicios con <strong>Plazo de Espera Pendiente</strong> no estarán disponibles para selección.
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            <?php else: ?>
                <div id="contrato-error-message" class="alert alert-warning alert-dismissible fade show" style="margin-top: 1.5rem;">
                    <div class="d-flex">
                        <div class="alert-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="alert-content">
                            <h5 class="alert-heading">Contrato no activo</h5>
                            <p class="mb-0">El contrato del afiliado no está activado. Por favor, active el contrato para poder seleccionar servicios médicos.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===== SECTION 5: ACCIONES FINALES ===== -->
    <div class="ms-panel">
        <div class="ms-panel-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white !important;">
            <h3 class="large-title section-title-white mb-0"><i class="fas fa-check-circle me-2"></i> Confirmación y Acciones Finales</h3>
        </div>
        <div class="ms-panel-body">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white !important;"><i class="fas fa-exclamation-triangle me-2"></i> Verificación Final</div>
                <div class="card-body">
                    <div class="alert alert-warning text-center"><i class="fas fa-info-circle fa-3x mb-3 d-block"></i>
                        <h5>Antes de guardar, verifique que:</h5>
                        <ul class="text-start d-inline-block">
                            <li>Todos los datos del afiliado sean correctos</li>
                            <li>La fecha y hora del evento sean precisas</li>
                            <li>Los servicios médicos seleccionados sean los adecuados</li>
                            <li>Los documentos adjuntos sean legibles y correspondan a esta atención</li>
                        </ul>
                    </div>
                    <div class="form-group text-center mt-4">
                        <?= Html::submitButton('<i class="fas fa-save"></i> Guardar ' . $terminoPrincipal, ['class' => 'btn btn-success btn-lg me-3 px-5']) ?>
                        <?= Html::a('<i class="fas fa-times"></i> Cancelar', ['index', 'user_id' => $afiliado->id], ['class' => 'btn btn-warning btn-lg me-3 px-5']) ?>
                        <?php if ($model->isNewRecord): ?><?= Html::a('<i class="fas fa-eraser"></i> Limpiar', ['create', 'user_id' => $afiliado->id], ['class' => 'btn btn-outline-dark btn-lg px-5']) ?><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<!-- ===== MODALS ===== -->
<?php
Modal::begin(['title' => '<h4>Detalles del Afiliado <button type="button" class="close" data-dismiss="modal">&times;</button></h4>', 'id' => 'afiliado-modal', 'size' => Modal::SIZE_LARGE, 'options' => ['tabindex' => false, 'class' => 'fade'], 'dialogOptions' => ['class' => 'modal-dialog-centered']]);
echo $this->render('/user-datos/view', ['model' => $afiliado]);
Modal::end();
?>

<?php
$this->registerJs(
    <<<JS
$('#historial-toggle-header').on('click', function() {
    $('#historial-content').slideToggle(300);
    $('#historial-chevron').toggleClass('fa-chevron-down fa-chevron-up');
});
$('#btn-abrir-afiliado-modal').on('click', function(e) { e.preventDefault(); setTimeout(function() { $('#afiliado-modal').modal('show'); }, 50); });
JS
);
?>

<?php
if (isset($baremosTotales) && isset($baremosHtml) && isset($baremosInfo) && isset($baremosRestringidosIDs)) {
    $baremosTotalesJson = json_encode($baremosTotales);
    $baremosHtmlJson = json_encode($baremosHtml);
    $baremosInfoJson = json_encode($baremosInfo);
    $baremosRestringidosJson = json_encode($baremosRestringidosIDs);
    $baremosPendientesPlazoJson = json_encode($baremosPendientesPlazo ?? []);
    $baremosAgotadosJson = json_encode($baremosAgotados ?? []);
    $baremosDisponiblesInfoJson = json_encode($baremosDisponiblesInfo ?? []);
    $baremosPendientesInfoJson = json_encode(array_intersect_key($baremosInfo, $baremosPendientesPlazo));
    $baremosAgotadosInfoJson = json_encode(array_intersect_key($baremosInfo, $baremosAgotados));

    $jsCode = <<<JS
(function() {
    'use strict';
    
    const baremosTotales = {$baremosTotalesJson};
    const baremosHtml = {$baremosHtmlJson};
    const baremosInfo = {$baremosInfoJson};
    const baremosRestringidosIDs = {$baremosRestringidosJson};
    const baremosDisponiblesInfo = {$baremosDisponiblesInfoJson};
    const baremosPendientesInfo = {$baremosPendientesInfoJson};
    const baremosAgotadosInfo = {$baremosAgotadosInfoJson};
    
    const baremosSelect = $('#baremos-select');
    const form = baremosSelect.closest('form');
    const citaWarning = $('#cita-warning');
    
    function showDisponiblesModal() {
        const items = baremosDisponiblesInfo;
        const count = Object.keys(items).length;
        if (count === 0) return;
        let modalHtml = '<div class="restricted-agotados-modal-overlay"><div class="restricted-agotados-modal"><div class="modal-header"><div class="modal-icon"><i class="fas fa-check-circle" style="color:#28a745;"></i></div><h3 class="modal-title">Servicios Disponibles (' + count + ')</h3><button type="button" class="modal-close" id="close-details-modal">&times;</button></div><div class="modal-content"><div class="modal-explanation"><p><strong>¿Qué significa "Disponible"?</strong></p><p>Estos servicios cumplen todos los criterios y pueden ser seleccionados ahora mismo.</p></div><div class="items-table-container"><table class="items-table"><thead><tr><th>Servicio</th><th>Área</th><th>Descripción</th><th>Disponibles</th><th>Precio</th></tr></thead><tbody>';
        Object.keys(items).forEach(function(baremoId) {
            const item = items[baremoId];
            if (!item) return;
            modalHtml += '<tr><td><strong>' + (item.nombre || 'Sin nombre') + '</strong></td><td>' + (item.area || 'Sin área') + '</td><td>' + (item.descripcion || 'Sin descripción') + '</td><td class="text-center"><span class="usage-badge" style="background-color:#28a745;">' + (item.disponibles || 'Ilimitado') + '</span></td><td class="text-end"><strong>$' + parseFloat(item.precio).toFixed(2) + '</strong></td></tr>';
        });
        modalHtml += '</tbody></table></div><div class="modal-footer"><button type="button" class="btn btn-secondary" id="close-details-modal-btn"><i class="fas fa-times me-1"></i> Cerrar</button></div></div></div></div>';
        $('.restricted-agotados-modal-overlay').remove();
        $('body').append(modalHtml);
        $('#close-details-modal, #close-details-modal-btn').off('click').on('click', function() { $('.restricted-agotados-modal-overlay').fadeOut(300, function() { $(this).remove(); }); });
        $('.restricted-agotados-modal-overlay').fadeIn(300);
    }
    
    function showRestrictedAgotadosModal(type) {
        const isRestringidos = (type === 'restringidos');
        const title = isRestringidos ? 'Servicios con Plazo de Espera Pendiente' : 'Servicios Agotados';
        const icon = isRestringidos ? '<i class="fas fa-clock"></i>' : '<i class="fas fa-ban"></i>';
        const items = isRestringidos ? baremosPendientesInfo : baremosAgotadosInfo;
        const count = Object.keys(items).length;
        if (count === 0) return;
        let modalHtml = '<div class="restricted-agotados-modal-overlay"><div class="restricted-agotados-modal"><div class="modal-header"><div class="modal-icon">' + icon + '</div><h3 class="modal-title">' + title + ' (' + count + ')</h3><button type="button" class="modal-close" id="close-details-modal">&times;</button></div><div class="modal-content"><div class="modal-explanation"><p><strong>' + (isRestringidos ? '¿Qué significa "Plazo de Espera Pendiente"?' : '¿Qué significa "Agotado"?') + '</strong></p><p>' + (isRestringidos ? 'Estos servicios requieren un período mínimo desde el inicio del contrato.' : 'Estos servicios han alcanzado su límite máximo de usos.') + '</p></div><div class="items-table-container"><table class="items-table"><thead><tr><th>Servicio</th><th>Área</th><th>Descripción</th><th>' + (isRestringidos ? 'Tiempo Restante' : 'Uso Actual') + '</th></tr></thead><tbody>';
        Object.keys(items).forEach(function(baremoId) {
            const item = items[baremoId];
            if (!item) return;
            modalHtml += '<tr>。<strong>' + (item.nombre || 'Sin nombre') + '</strong><td>' + (item.area || 'Sin área') + '<td>' + (item.descripcion || 'Sin descripción') + '<td><td class="text-center"><span class="time-badge">' + (item.remaining_text || (isRestringidos ? 'En espera' : (item.veces_usado + ' / ' + item.cantidad_limite))) + '</span></td></tr>';
        });
        modalHtml += '</tbody></table></div><div class="modal-footer"><button type="button" class="btn btn-secondary" id="close-details-modal-btn"><i class="fas fa-times me-1"></i> Cerrar</button></div></div></div></div>';
        $('.restricted-agotados-modal-overlay').remove();
        $('body').append(modalHtml);
        $('#close-details-modal, #close-details-modal-btn').off('click').on('click', function() { $('.restricted-agotados-modal-overlay').fadeOut(300, function() { $(this).remove(); }); });
        $('.restricted-agotados-modal-overlay').fadeIn(300);
    }
    
    function initializeSelect2() {
        if ($('#baremos-select').data('select2')) $('#baremos-select').select2('destroy');
        $('#baremos-select').select2({ multiple: true, placeholder: 'Busca o selecciona los servicios del Baremo...', allowClear: true, closeOnSelect: false, minimumInputLength: 0, templateResult: function(data) { if (data.id && baremosHtml[data.id]) return $(baremosHtml[data.id]); return data.text; }, templateSelection: function(data) { if (data.id && baremosInfo[data.id]) return baremosInfo[data.id].nombre + ' (' + baremosInfo[data.id].area + ')'; return data.text; }, escapeMarkup: function(m) { return m; } });
        calcularTotalYTabla();
    }
    
    function calcularTotalYTabla() {
        const selected = baremosSelect.val() || [];
        let total = 0, html = '';
        if (selected.length === 0) { $('#costo-total-container, #baremos-tabla-container').hide(); $('#costo-total-input').val('0.00'); $('#cobertura-warning').hide(); return; }
        selected.forEach(function(id) {
            const item = baremosInfo[id];
            if (item && item.precio !== undefined) {
                total += parseFloat(item.precio);
                let restricciones = [];
                if (item.plazo_espera) restricciones.push('Plazo: ' + item.plazo_espera + ' meses');
                if (item.cantidad_limite > 0) restricciones.push('Límite: ' + item.veces_usado + '/' + item.cantidad_limite);
                html += '<tr>。<strong>' + item.nombre + '</strong><td>' + item.area + '<td>' + (item.descripcion || 'Sin descripción') + '<td><td class="text-center">' + (restricciones.join('<br>') || 'Ninguna') + '</td><td class="cost-col">$' + item.precio.toFixed(2) + '</td></tr>';
            }
        });
        $('#baremos-tabla-body').html(html);
        $('#baremos-tabla-container').show();
        $('#costo-total-value, #summary-total-amount').html('$' + total.toFixed(2));
        $('#costo-total-input').val(total.toFixed(2));
        $('#costo-total-container').show();
        const totalDisponible = <?= json_encode($totalDisponible) ?>;
        if (total > totalDisponible) { $('#cobertura-difference-text').text('Excede por $' + (total - totalDisponible).toFixed(2)); $('#cobertura-warning').show(); } else { $('#cobertura-warning').hide(); }
    }
    
    function validateFormBeforeSubmit() {
        const selected = baremosSelect.val() || [];
        if (selected.length === 0) { alert('Debe seleccionar al menos un servicio médico.'); return false; }
        return true;
    }
    
    $('.stat-badge.disponible').on('click', function(e) { e.preventDefault(); showDisponiblesModal(); });
    $('.stat-badge.restringido').on('click', function(e) { e.preventDefault(); showRestrictedAgotadosModal('restringidos'); });
    $('.stat-badge.agotado').on('click', function(e) { e.preventDefault(); showRestrictedAgotadosModal('agotados'); });
    baremosSelect.on('change', function() { calcularTotalYTabla(); });
    $('#recalculate-total').on('click', function() { calcularTotalYTabla(); });
    form.on('beforeSubmit', function() { return validateFormBeforeSubmit(); });
    
    $(document).ready(function() { setTimeout(function() { initializeSelect2(); $('[data-toggle="tooltip"]').tooltip({ trigger: 'hover', placement: 'top', html: true }); }, 300); });
})();
JS;
    $this->registerJs($jsCode, \yii\web\View::POS_END);
}
?>