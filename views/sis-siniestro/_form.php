<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\web\View;
use yii\bootstrap4\Modal;
use kartik\file\FileInput;  // Add this if not already there
use kartik\file\FileInputAsset;  // Add this line

/* @var $this yii\web\View */
/* @var $model app\models\SisSiniestro */
/* @var $form yii\widgets\ActiveForm */
/* @var $afiliado app\models\UserDatos */
/* @var $es_cita int */

$this->registerCssFile(Yii::getAlias('@web') . "/css/_formsiniestros.css", ['position' => View::POS_HEAD]);

FileInputAsset::register($this);


// ============================================================
// GUARDS: Ensure variables are defined
// ============================================================
if (!isset($model) || $model === null) {
    $model = new \app\models\SisSiniestro();
}

if (!isset($afiliado) || $afiliado === null) {
    if (isset($model) && $model->iduser) {
        $afiliado = \app\models\UserDatos::findOne($model->iduser);
    } else {
        $afiliado = null;
    }
}

if (!isset($es_cita)) {
    $es_cita = 0;
}

// Obtener información del plan del afiliado
if ($afiliado && is_object($afiliado)) {
    $planId = $afiliado->plan_id;
    $afiliadoObj = $afiliado;
} elseif (is_array($afiliado) && isset($afiliado['plan_id'])) {
    $planId = $afiliado['plan_id'];
    $afiliadoObj = (object)$afiliado;
} elseif ($afiliado && is_numeric($afiliado)) {
    $planId = (int)$afiliado;
    $afiliadoObj = \app\models\UserDatos::findOne($planId);
} else {
    $planId = null;
    $afiliadoObj = null;
}

if (!$afiliadoObj) {
    echo '<div class="alert alert-danger">Error: No se pudo cargar la información del afiliado.</div>';
    return;
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
$esCita = (int)$es_cita;

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
                <!-- Afiliado Card -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                            <i class="fas fa-user me-2"></i> Datos del Afiliado
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <p><strong>Nombre:</strong>
                                        <?= Html::encode(trim($afiliado->nombres . ' ' . $afiliado->apellidos)) ?>
                                    </p>
                                    <p><strong>Código:</strong> <?= Html::encode($afiliado->codigo ?? 'N/A') ?></p>
                                    <p><strong>Cédula:</strong>
                                        <?php
                                        $cedula = $afiliado->cedula;
                                        $tipoCedula = $afiliado->tipo_cedula ?? 'V';
                                        echo Html::encode($tipoCedula . '-' . str_pad($cedula, 8, '0', STR_PAD_LEFT));
                                        ?>
                                    </p>
                                    <p><strong>Teléfono:</strong> <?= Html::encode($afiliado->telefono) ?></p>
                                    <p><strong>Email:</strong> <?= Html::encode($afiliado->email) ?></p>
                                </div>
                                <div class="col-md-4 d-flex align-items-center justify-content-center">
                                    <?= Html::button(
                                        '<i class="fas fa-eye mr-2"></i> Ver Detalles',
                                        [
                                            'class' => 'btn btn-success',
                                            'id' => 'btn-abrir-afiliado-modal',
                                            'type' => 'button'
                                        ]
                                    ) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Plan Info Card -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                            <i class="fas fa-file-contract me-2"></i> Información del Plan y Límites
                        </div>
                        <div class="card-body">
                            <div class="plan-info-summary">
                                <div class="plan-info-item">
                                    <span class="plan-info-label">Plan:</span>
                                    <span class="plan-info-value"><?= $afiliado->plan->nombre ?? 'Sin plan' ?></span>
                                </div>
                                <div class="plan-info-item">
                                    <span class="plan-info-label">Cobertura del Plan:</span>
                                    <span class="plan-info-value">$<?= number_format($precioPlan, 2) ?></span>
                                </div>
                                <div class="plan-info-item">
                                    <span class="plan-info-label">Total Utilizado:</span>
                                    <span class="plan-info-value">$<?= number_format($sumatoriaSiniestros ?? 0, 2) ?></span>
                                </div>
                                <div class="plan-info-item plan-info-total">
                                    <span class="plan-info-label">Total Disponible:</span>
                                    <span class="plan-info-value">$<?= number_format($totalDisponible, 2) ?></span>
                                </div>
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
                <small class="ms-2" style="color: rgba(255, 255, 255, 0.8) !important; font-size: 16px !important;">
                    (<?= count($baremosCitas) + count($baremosSiniestros) ?> registros)
                </small>
                <span class="float-right">
                    <i class="fas fa-chevron-down" id="historial-chevron" style="color: white !important; text-decoration: none !important;"></i>
                </span>
            </h3>
        </div>
        <div class="ms-panel-body" id="historial-content" style="display: none;">
            <?php if ($esCita == 1 && !empty($baremosCitas)): ?>
                <div class="card mb-4">
                    <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                        <i class="fas fa-calendar-check me-2"></i> Citas Realizadas
                        <span class="badge badge-success float-right"><?= count($baremosCitas) ?></span>
                    </div>
                    <div class="card-body">
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
                                            <td><?= date('d/m/Y', strtotime($cita['fecha'])) ?></td>
                                            <td><strong><?= $cita['area'] ?></strong></td>
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
                    </div>
                </div>
            <?php elseif ($esCita == 1 && empty($baremosCitas)): ?>
                <div class="card mb-4">
                    <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                        <i class="fas fa-calendar-check me-2"></i> Citas Realizadas
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info text-center mb-0">
                            <i class="fas fa-info-circle"></i> No se han realizado citas aún.
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($esCita == 0 && !empty($baremosSiniestros)): ?>
                <div class="card mb-4">
                    <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                        <i class="fas fa-file-medical me-2"></i> Atenciones Médicas Registradas
                        <span class="badge badge-info float-right"><?= count($baremosSiniestros) ?></span>
                    </div>
                    <div class="card-body">
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
                                            <td><?= date('d/m/Y', strtotime($siniestro['fecha'])) ?></td>
                                            <td><strong><?= $siniestro['area'] ?></strong></td>
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
                                                <span class="badge badge-siniestro">Atención</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php elseif ($esCita == 0 && empty($baremosSiniestros)): ?>
                <div class="card mb-4">
                    <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                        <i class="fas fa-file-medical me-2"></i> Atenciones Médicas Registradas
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info text-center mb-0">
                            <i class="fas fa-info-circle"></i> No se han registrado atenciones médicas aún.
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                    <i class="fas fa-chart-bar me-2"></i> Resumen Estadístico
                </div>
                <div class="card-body">
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
                                <div class="stats-label">Atenciones Registradas</div>
                            </div>
                            <div class="col-md-3">
                                <?php
                                $totalUtilizado = array_sum(array_column(array_merge($baremosCitas, $baremosSiniestros), 'precio'));
                                ?>
                                <div class="stats-number">$<?= number_format($totalUtilizado, 2) ?></div>
                                <div class="stats-label">Total Utilizado</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== SECTION 3: DATOS DE LA ATENCIÓN/CITA ===== -->
    <div class="ms-panel mb-4">
        <div class="ms-panel-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); padding: 16px 25px;">
            <h3 class="large-title section-title-white mb-0" id="titulo-datos-registro" style="margin: 0; display: flex; align-items: center; gap: 12px;">
                <?php if ($esCitaMode): ?>
                    <i class="fas fa-calendar-alt" style="color: #a8e6cf; font-size: 1.6rem; vertical-align: middle;"></i>
                <?php else: ?>
                    <i class="fas fa-heartbeat" style="color: #ff6b6b; font-size: 1.6rem; vertical-align: middle;"></i>
                <?php endif; ?>
                <span style="line-height: 1.4;"><?= $tituloSeccion ?></span>
                <?php if ($esCitaMode): ?>
                    <span class="badge bg-warning ms-2" style="font-size: 0.75rem; padding: 5px 12px;">Modo Cita</span>
                <?php else: ?>
                    <span class="badge bg-info ms-2" style="font-size: 0.75rem; padding: 5px 12px;">Modo Atención</span>
                <?php endif; ?>
            </h3>
        </div>
        <div class="ms-panel-body">
            <div style="display: none;">
                <?= $form->field($model, 'idclinica')->textInput(['value' => $afiliado->clinica_id]) ?>
            </div>

            <?php if (false): ?>
                <div class="card mb-4">
                    <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                        <i class="fas fa-info-circle me-2"></i> Información Básica
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
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
                                <i class="fas fa-clock"></i>
                                <?= $form->field($model, 'hora')->textInput([
                                    'type' => 'text',
                                    'class' => 'form-control form-control-lg time-input',
                                    'placeholder' => 'HH:MM (ejemplo: 14:30)',
                                    'maxlength' => 5,
                                    'autocomplete' => 'off'
                                ])->label('Hora del Evento de Salud') ?>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Use formato 24 horas (HH:MM). Ejemplo: 14:30 o 09:15
                                </small>
                            </div>

                            <?php if (!$esCitaMode): ?>
                                <div class="col-md-12">
                                    <?= $form->field($model, 'atendido')->dropDownList(
                                        [0 => 'No', 1 => 'Sí'],
                                        [
                                            'prompt' => 'Seleccione estado',
                                            'class' => 'form-control form-control-lg'
                                        ]
                                    )->label('¿Fue atendido?') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card mb-4">
                    <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                        <i class="fas fa-stethoscope me-2"></i> <?= $esCitaMode ? 'Detalles de la Cita' : 'Detalles de la Atención' ?>
                    </div>
                    <div class="card-body">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6 field-with-icon">
                                    <i class="fas fa-calendar-check"></i>
                                    <?= $form->field($model, 'fecha_atencion')->textInput([
                                        'type' => 'date',
                                        'class' => 'form-control form-control-lg',
                                        'placeholder' => 'Seleccione la fecha',
                                        'autocomplete' => 'off',
                                        'value' => $model->isNewRecord ? date('Y-m-d') : Yii::$app->formatter->asDate($model->fecha_atencion, 'yyyy-MM-dd')
                                    ])->label('Fecha de la ' . $terminoPrincipal) ?>
                                </div>

                                <div class="col-md-6 field-with-icon">
                                    <i class="fas fa-clock"></i>
                                    <?= $form->field($model, 'hora_atencion')->textInput([
                                        'type' => 'text',
                                        'class' => 'form-control form-control-lg time-input',
                                        'placeholder' => 'HH:MM (ejemplo: 14:30)',
                                        'maxlength' => 5,
                                        'autocomplete' => 'off'
                                    ])->label('Hora de la ' . $terminoPrincipal) ?>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-clock"></i> Use formato 24 horas (HH:MM). Ejemplo: 14:30 o 09:15
                                    </small>
                                </div>

                                <div class="col-md-12 field-with-icon">
                                    <i class="fas fa-align-left"></i>
                                    <?= $form->field($model, 'descripcion')->textarea([
                                        'rows' => 3,
                                        'class' => 'form-control form-control-lg',
                                        'placeholder' => 'Describa los detalles de la ' . strtolower($terminoPrincipal) . '...'
                                    ])->label('Descripción de la ' . $terminoPrincipal) ?>
                                </div>

                                <!-- ===== DOCTOR AND ADMISSION ANALYST FIELDS - SAME ROW ===== -->
                                <div class="row">
                                    <div class="col-md-6 field-with-icon">
                                        <i class="fas fa-user-md"></i>
                                        <?= $form->field($model, 'nombre_doctor')->textInput([
                                            'class' => 'form-control form-control-lg',
                                            'placeholder' => 'Médico Tratante',
                                            'maxlength' => true,
                                            'autocomplete' => 'off'
                                        ])->label('Nombre del Doctor') ?>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-user-md"></i> Médico que atendió al paciente
                                        </small>
                                    </div>

                                    <div class="col-md-6 field-with-icon">
                                        <i class="fas fa-user-tie"></i>
                                        <?= $form->field($model, 'admission_analyst')->textInput([
                                            'class' => 'form-control form-control-lg',
                                            'placeholder' => $esCitaMode ? 'Nombre del analista de citas' : 'Nombre del analista de admisión',
                                            'maxlength' => true,
                                            'autocomplete' => 'off'
                                        ])->label($esCitaMode ? 'Analista de Citas' : 'Analista de Admisión') ?>
                                        <small class="form-text text-muted" style="white-space: nowrap; overflow: visible;">
                                            <i class="fas fa-user-tie"></i> <?= $esCitaMode ? 'Persona responsable del registro de esta cita' : 'Persona responsable del registro de esta ' . strtolower($terminoPrincipal) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== SUBSECTION: DOCUMENTACIÓN ADJUNTA ===== -->
                    <div class="card mb-4">
                        <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                            <i class="fas fa-paperclip me-2"></i> Documentación Adjunta
                        </div>
                        <div class="card-body">
                            <p class="hint-block mb-4">
                                <i class="fas fa-info-circle"></i> Adjunte los documentos relacionados con esta <?= strtolower($terminoPrincipal) ?>
                            </p>

                            <div class="row">
                                <!-- Récipe Médico -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label font-weight-bold">
                                            <i class="fas fa-file-prescription text-primary"></i> Récipe Médico
                                        </label>
                                        <div class="custom-file">
                                            <?= Html::fileInput('SisSiniestro[imagenRecipeFile]', null, [
                                                'class' => 'custom-file-input',
                                                'accept' => 'image/*,application/pdf',
                                                'id' => 'recipe-file-input'
                                            ]) ?>
                                            <label class="custom-file-label" for="recipe-file-input" id="recipe-file-label">
                                                <i class="fas fa-upload"></i> Seleccionar archivo...
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i> Formatos permitidos: JPG, JPEG, PNG, PDF (máx. 10MB)
                                        </small>
                                        <?php if ($model->imagen_recipe && !$model->isNewRecord): ?>
                                            <div class="mt-2">
                                                <a href="<?= $model->imagen_recipe ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-eye"></i> Ver archivo actual
                                                </a>
                                                <span class="text-muted ml-2">
                                                    <i class="fas fa-check-circle text-success"></i> Archivo guardado
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Informe Médico -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label font-weight-bold">
                                            <i class="fas fa-file-medical text-info"></i> Informe Médico
                                        </label>
                                        <div class="custom-file">
                                            <?= Html::fileInput('SisSiniestro[imagenInformeFile]', null, [
                                                'class' => 'custom-file-input',
                                                'accept' => 'image/*,application/pdf',
                                                'id' => 'informe-file-input'
                                            ]) ?>
                                            <label class="custom-file-label" for="informe-file-input" id="informe-file-label">
                                                <i class="fas fa-upload"></i> Seleccionar archivo...
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i> Formatos permitidos: JPG, JPEG, PNG, PDF (máx. 10MB)
                                        </small>
                                        <?php if ($model->imagen_informe && !$model->isNewRecord): ?>
                                            <div class="mt-2">
                                                <a href="<?= $model->imagen_informe ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-eye"></i> Ver archivo actual
                                                </a>
                                                <span class="text-muted ml-2">
                                                    <i class="fas fa-check-circle text-success"></i> Archivo guardado
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional info about existing files -->
                            <?php if (($model->imagen_recipe || $model->imagen_informe) && !$model->isNewRecord): ?>
                                <div class="alert alert-info mt-3 mb-0">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Nota:</strong> Si selecciona nuevos archivos, reemplazarán los existentes.
                                    Deje el campo vacío para mantener los archivos actuales.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                /* Change file input browse button to Spanish */
                .custom-file-label::after {
                    content: "Buscar" !important;
                }

                /* Optional: Style the file input when a file is selected */
                .custom-file-label.selected {
                    background-color: #e8f5e9;
                    border-color: #4caf50;
                    color: #2e7d32;
                }

                .custom-file-label.selected i {
                    color: #2e7d32;
                }
            </style>

            <?= $this->render('_form_documentos_adicionales', ['model' => $model, 'form' => $form]) ?>

            <!-- ===== SECTION 4: SELECCIÓN DE SERVICIOS MÉDICOS ===== -->
            <?php
            // Initialize ALL variables
            $baremosTotales = [];
            $baremosHtml = [];
            $baremosInfo = [];
            $baremosRestringidosIDs = [];
            $baremosForzados = [];
            $baremosSinPlazo = [];
            $baremosConPlazoCumplido = [];
            $baremosPendientesPlazo = [];
            $baremosAgotados = [];
            $baremosDisponiblesInfo = [];
            $selectedBaremos = [];

            if ($contrato && $contrato->estatus === 'Activo') {
                $query = \app\models\PlanesItemsCobertura::find()
                    ->joinWith('baremo')
                    ->joinWith('plan')
                    ->joinWith('baremo.area')
                    ->where(['planes.clinica_id' => $afiliado->clinica_id])
                    ->andWhere(['baremo.estatus' => 'Activo'])
                    ->andWhere(['planes.id' => $afiliado->plan_id]);

                if ($esCitaMode) {
                    $query->andWhere([
                        'or',
                        ['>', 'planes_items_cobertura.plazo_espera', 0],
                        ['>', 'planes_items_cobertura.cantidad_limite', 0]
                    ]);
                }

                $planesItemsCobertura = $query->all();

                if (isset($model) && $model !== null && !$model->isNewRecord && $model->id) {
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
                    if ($item->baremo) {
                        $hasPlazoEver = (!empty($item->plazo_espera) && $item->plazo_espera > 0);
                        $precioBaremo = $item->baremo->precio ?? 0;
                        $area = $item->baremo->area ? $item->baremo->area->nombre : 'Sin área';
                        $servicio = $item->baremo->nombre_servicio;
                        $descripcion = $item->baremo->descripcion ?? '';

                        $textoPlano = $servicio . " (" . $area . ")";
                        if (!empty($descripcion)) {
                            $textoPlano .= " - " . $descripcion;
                        }

                        $queryCount = \app\models\SisSiniestroBaremo::find()
                            ->joinWith('siniestro')
                            ->where(['baremo_id' => $item->baremo_id])
                            ->andWhere(['iduser' => $afiliado->id]);

                        if (!$esCitaMode) {
                            $queryCount->andWhere(['sis_siniestro.es_cita' => 0]);
                        }

                        $vecesUsado = $queryCount->count();

                        $remainingUses = 0;
                        $excedeLimite = false;

                        if ($item->cantidad_limite !== null && $item->cantidad_limite > 0) {
                            $remainingUses = $item->cantidad_limite - $vecesUsado;
                            if ($remainingUses <= 0) {
                                $excedeLimite = true;
                            }
                        } else {
                            $remainingUses = 999;
                        }

                        $esBaremoGuardado = !$model->isNewRecord && in_array($item->baremo_id, $selectedBaremos);
                        $isRestrictedByPlazo = false;

                        if ($contrato && $hasPlazoEver) {
                            $fechaContratoIni = new \DateTime($contrato->fecha_ini);
                            $diff = $fechaContratoIni->diff($fechaActual);
                            $mesesTranscurridos = $diff->y * 12 + $diff->m;
                            $plazoRequerido = (int)$item->plazo_espera;

                            if ($mesesTranscurridos < $plazoRequerido) {
                                $isRestrictedByPlazo = true;
                            }
                        }

                        $hasValidLimit = ($item->cantidad_limite !== null && $item->cantidad_limite > 0);
                        $hasValidPlazo = ($item->plazo_espera !== null && $item->plazo_espera > 0);

                        if (!$hasValidLimit && !$hasValidPlazo) {
                            continue;
                        }

                        $debeIncluirse = true;

                        if ($excedeLimite) {
                            $baremosAgotados[$item->baremo_id] = $textoPlano;
                            $baremosInfo[$item->baremo_id]['es_agotado'] = true;
                            if (!$esBaremoGuardado) {
                                $debeIncluirse = false;
                            }
                        } elseif ($isRestrictedByPlazo) {
                            $baremosPendientesPlazo[$item->baremo_id] = $textoPlano;
                            $baremosRestringidosIDs[] = $item->baremo_id;
                            $baremosInfo[$item->baremo_id]['is_restricted_by_plazo'] = true;

                            if ($contrato) {
                                $fechaContratoIni = new \DateTime($contrato->fecha_ini);
                                $plazoRequerido = (int)$item->plazo_espera;
                                $fechaTarget = clone $fechaContratoIni;
                                $fechaTarget->modify("+{$plazoRequerido} months");
                                $diff = $fechaActual->diff($fechaTarget);

                                $mesesRestantes = ($diff->y * 12) + $diff->m;
                                $diasRestantes = $diff->d;

                                $baremosInfo[$item->baremo_id]['remaining_months'] = $mesesRestantes;
                                $baremosInfo[$item->baremo_id]['remaining_days'] = $diasRestantes;

                                if ($mesesRestantes > 0 && $diasRestantes > 0) {
                                    $tiempoRestanteTexto = $mesesRestantes . " mes" . ($mesesRestantes > 1 ? "es" : "") . " y " . $diasRestantes . " día" . ($diasRestantes > 1 ? "s" : "");
                                } elseif ($mesesRestantes > 0) {
                                    $tiempoRestanteTexto = $mesesRestantes . " mes" . ($mesesRestantes > 1 ? "es" : "");
                                } elseif ($diasRestantes > 0) {
                                    $tiempoRestanteTexto = $diasRestantes . " día" . ($diasRestantes > 1 ? "s" : "");
                                } else {
                                    $tiempoRestanteTexto = "Próximamente";
                                }
                                $baremosInfo[$item->baremo_id]['remaining_text'] = $tiempoRestanteTexto;
                            }

                            if (!$esBaremoGuardado) {
                                $debeIncluirse = false;
                            }
                        } else {
                            $hasRemainingUses = ($remainingUses > 0) || ($item->cantidad_limite === null || $item->cantidad_limite == 0);
                            if ($hasRemainingUses) {
                                $baremosSinPlazo[$item->baremo_id] = $textoPlano;
                                $baremosDisponiblesInfo[$item->baremo_id] = [
                                    'nombre' => $servicio,
                                    'area' => $area,
                                    'descripcion' => $descripcion,
                                    'precio' => $precioBaremo,
                                    'cantidad_limite' => (int)$item->cantidad_limite,
                                    'veces_usado' => (int)$vecesUsado,
                                    'disponibles' => ($remainingUses > 0 && $remainingUses < 999) ? $remainingUses : ($item->cantidad_limite > 0 ? $remainingUses : 'Ilimitado'),
                                    'remaining' => $remainingUses,
                                ];
                            }
                        }

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
                            'remaining_uses' => $remainingUses,
                        ]);

                        $disponibles = $remainingUses;
                        $availabilityClass = '';
                        $availabilityText = '';

                        if ($item->cantidad_limite !== null && $item->cantidad_limite > 0) {
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
                            if ($vecesUsado > 0) {
                                $availabilityText = 'Usado ' . $vecesUsado . ' vez/veces (sin límite)';
                            } else {
                                $availabilityText = 'Sin límite';
                            }
                        }

                        $htmlFormateado = "<div class='baremo-dropdown-option'>";
                        $htmlFormateado .= "<div class='baremo-first-row'>";
                        $htmlFormateado .= "<div class='baremo-content-main'>";
                        $htmlFormateado .= "<div class='baremo-area'><div class='baremo-area-label'>Área</div><div class='baremo-area-value'>" . Html::encode($area) . "</div></div>";
                        $htmlFormateado .= "<div class='baremo-servicio'><div class='baremo-servicio-label'>Servicio</div><div class='baremo-servicio-value'>" . Html::encode($servicio) . "</div></div>";
                        $htmlFormateado .= "<div class='baremo-descripcion'><div class='baremo-descripcion-label'>Descripción</div><div class='baremo-descripcion-value' title='" . Html::encode($descripcion ?: 'Sin descripción') . "'>" . Html::encode($descripcion ?: 'Sin descripción') . "</div></div>";
                        $htmlFormateado .= "</div>";
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
                        $htmlFormateado .= "</div></div>";
                        $htmlFormateado .= "<div class='baremo-second-row'>";
                        $htmlFormateado .= "<div class='baremo-price-container'><span class='baremo-price'>$" . number_format($precioBaremo, 2) . "</span></div>";
                        if ($isRestrictedByPlazo && isset($baremosInfo[$item->baremo_id]['remaining_text'])) {
                            $htmlFormateado .= "<div class='baremo-waiting-period'><i class='fas fa-clock me-1'></i><span>Disponible en " . $baremosInfo[$item->baremo_id]['remaining_text'] . "</span></div>";
                        } else {
                            $htmlFormateado .= "<div class='baremo-availability " . $availabilityClass . "'>" . $availabilityText . "</div>";
                        }
                        $htmlFormateado .= "</div></div>";

                        if ($debeIncluirse || $esBaremoGuardado) {
                            $baremosHtml[$item->baremo_id] = $htmlFormateado;
                            if (!$excedeLimite && !$isRestrictedByPlazo) {
                                $baremosTotales[$item->baremo_id] = $textoPlano;
                            } elseif ($esBaremoGuardado) {
                                $baremosForzados[$item->baremo_id] = $textoPlano;
                                $baremosTotales[$item->baremo_id] = $textoPlano;
                            }
                        }
                    }
                }

                $baremosTotales = $baremosForzados + $baremosSinPlazo;
            }
            ?>

            <div class="ms-panel mb-4">
                <div class="combined-section-card">
                    <div class="d-flex align-items-center justify-content-between mb-0">
                        <div class="d-flex align-items-center" style="gap: 16px;">
                            <div class="services-icon-wrapper" style="display: inline-flex; align-items: center; justify-content: center; width: 52px; height: 52px; background: rgba(255,255,255,0.15); border-radius: 12px;">
                                <?php if ($esCitaMode): ?>
                                    <i class="fas fa-calendar-alt services-icon-calendar"></i>
                                <?php else: ?>
                                    <i class="fas fa-heartbeat services-icon-heartbeat"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h3 class="services-section-title" style="margin: 0; line-height: 1.3; font-weight: 600; font-size: 1.3rem; color: white !important;">
                                    Selección de Servicios Médicos
                                </h3>
                                <p class="services-section-subtitle" style="margin: 4px 0 0 0; font-size: 0.9rem; color: rgba(255,255,255,0.85) !important;">
                                    Seleccione los servicios médicos aplicados en esta <?= strtolower($terminoPrincipal) ?>
                                </p>
                            </div>
                        </div>
                        <div class="section-badge-white d-flex flex-wrap gap-2 align-items-center">
                            <span class="badge badge-pill stat-badge disponible clickable-badge"
                                data-toggle="tooltip"
                                data-placement="top"
                                title="Servicios que cumplen todos los criterios y pueden ser seleccionados ahora mismo. Click para ver detalles.">
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
                                    title="Servicios con plazo de espera pendiente. No disponibles para selección. Click para ver detalles.">
                                    <i class="fas fa-clock me-3"></i>
                                    <span><?= count($baremosPendientesPlazo) ?> Restringidos</span>
                                </span>
                            <?php endif; ?>

                            <?php
                            $totalAgotados = count($baremosAgotados);
                            if ($totalAgotados > 0):
                            ?>
                                <span class="badge badge-pill stat-badge agotado clickable-badge"
                                    data-toggle="tooltip"
                                    data-placement="top"
                                    title="Servicios que han alcanzado su límite máximo de usos. No disponibles para selección. Click para ver detalles.">
                                    <i class="fas fa-ban me-3"></i>
                                    <span><?= $totalAgotados ?> Agotados</span>
                                </span>
                            <?php endif; ?>

                            <?php if (!empty($baremosForzados)): ?>
                                <span class="badge badge-pill stat-badge historico"
                                    data-toggle="tooltip"
                                    data-placement="top"
                                    title="Servicios previamente guardados que ya no cumplen criterios actuales, mostrados solo para referencia histórica.">
                                    <i class="fas fa-history me-3"></i>
                                    <span><?= count($baremosForzados) ?> Históricos</span>
                                </span>
                            <?php endif; ?>

                            <span class="badge badge-pill stat-badge total"
                                data-toggle="tooltip"
                                data-placement="top"
                                title="Cantidad total de servicios médicos incluidos en el plan, independientemente de su disponibilidad.">
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
                                        <tbody id="baremos-tabla-body"></tbody>
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
                    <h3 class="large-title section-title-white mb-0" style="color: white !important;">
                        <i class="fas fa-check-circle me-2" style="color: white !important;"></i> Confirmación y Acciones Finales
                    </h3>
                </div>
                <div class="ms-panel-body">
                    <div class="card">
                        <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white !important;">
                            <i class="fas fa-exclamation-triangle me-2" style="color: white !important;"></i> Verificación Final
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning text-center">
                                <div class="d-flex flex-column align-items-center">
                                    <div class="alert-icon mb-3">
                                        <i class="fas fa-info-circle fa-3x"></i>
                                    </div>
                                    <div class="alert-content">
                                        <h5 class="alert-heading mb-3">Antes de guardar, verifique que:</h5>
                                        <ul class="mb-0 text-start" style="display: inline-block;">
                                            <li>Todos los datos del afiliado sean correctos</li>
                                            <li>La fecha y hora del evento sean precisas</li>
                                            <li>Los servicios médicos seleccionados sean los adecuados</li>
                                            <li>Los documentos adjuntos sean legibles y correspondan a esta atención</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group text-center mt-4">
                                <?= Html::submitButton('<i class="fas fa-save"></i> Guardar ' . $terminoPrincipal, [
                                    'class' => 'btn btn-success btn-lg me-3 px-5'
                                ]) ?>
                                <?= Html::a('<i class="fas fa-times"></i> Cancelar', ['index', 'user_id' => $afiliado->id], [
                                    'class' => 'btn btn-warning btn-lg me-3 px-5'
                                ]); ?>
                                <?php if ($model->isNewRecord): ?>
                                    <?= Html::a('<i class="fas fa-eraser"></i> Limpiar', ['create', 'user_id' => $afiliado->id], [
                                        'class' => 'btn btn-outline-dark btn-lg px-5'
                                    ]); ?>
                                <?php endif; ?>
                            </div>
                            <div class="text-center mt-3">
                                <p class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Revise que todos los datos estén correctos antes de guardar la <?= strtolower($terminoPrincipal) ?>.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
        </div>

        <?php
        Modal::begin([
            'title' => '<h4>Detalles del Afiliado <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>',
            'id' => 'afiliado-modal',
            'size' => Modal::SIZE_LARGE,
            'options' => ['tabindex' => false, 'class' => 'fade', 'role' => 'dialog'],
            'dialogOptions' => ['class' => 'modal-dialog-centered'],
        ]);
        echo $this->render('/user-datos/view', ['model' => $afiliado]);
        Modal::end();
        ?>

        <?php
        $this->registerJs(<<<'JS'
// ============================================
// TOGGLE HISTORIAL SECTION
// ============================================
$('#historial-toggle-header').on('click', function() {
    var content = $('#historial-content');
    var chevron = $('#historial-chevron');
    
    if (content.is(':visible')) {
        content.slideUp(300);
        chevron.removeClass('fa-chevron-up').addClass('fa-chevron-down');
    } else {
        content.slideDown(300);
        chevron.removeClass('fa-chevron-down').addClass('fa-chevron-up');
    }
});

// ============================================
// AFILIADO MODAL TRIGGER
// ============================================
$('#btn-abrir-afiliado-modal').on('click', function(e) {
    e.preventDefault();
    setTimeout(function() {
        $('#afiliado-modal').modal('show');
    }, 50);
});

// ============================================
// TIME INPUT FORMATTING AND VALIDATION
// ============================================

// Validate time format (HH:MM with 00-23:00-59)
function validateTimeFormat(timeStr) {
    if (!timeStr || timeStr === '') return true;
    return /^([0-1][0-9]|2[0-3]):[0-5][0-9]$/.test(timeStr);
}

// Convert any time format to 24-hour HH:MM
function convertTo24Hour(timeStr) {
    if (!timeStr) return '';
    
    // Already in 24-hour format (HH:MM)
    if (/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/.test(timeStr)) {
        return timeStr;
    }
    
    // Handle 12-hour format with AM/PM
    var match = timeStr.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
    if (match) {
        var hour = parseInt(match[1]);
        var minute = match[2];
        var ampm = match[3].toUpperCase();
        
        if (ampm === 'PM' && hour !== 12) {
            hour += 12;
        } else if (ampm === 'AM' && hour === 12) {
            hour = 0;
        }
        
        return hour.toString().padStart(2, '0') + ':' + minute;
    }
    
    // Handle format with seconds (HH:MM:SS)
    match = timeStr.match(/^(\d{1,2}):(\d{2}):\d{2}$/);
    if (match) {
        return match[1].padStart(2, '0') + ':' + match[2];
    }
    
    return timeStr;
}

// Auto-format time when user leaves the field (on blur)
function autoFormatTime(input) {
    var value = input.value;
    if (!value) return;
    
    // Remove any non-digit characters for processing
    var digits = value.replace(/[^0-9]/g, '');
    
    if (digits.length >= 3) {
        var hour = digits.slice(0, 2);
        var minute = digits.slice(2, 4);
        
        // Validate ranges
        var hourInt = parseInt(hour);
        var minuteInt = parseInt(minute);
        
        if (hourInt > 23) hour = '23';
        if (hourInt < 0 || isNaN(hourInt)) hour = '00';
        if (minuteInt > 59) minute = '59';
        if (minuteInt < 0 || isNaN(minuteInt)) minute = '00';
        
        input.value = hour + ':' + minute;
    } else if (digits.length === 2) {
        input.value = digits;
    }
}

// Initialize time inputs when document is ready
$(document).ready(function() {
    // Convert existing values to 24-hour format on page load
    $('.time-input').each(function() {
        var converted = convertTo24Hour($(this).val());
        if (converted !== $(this).val()) {
            $(this).val(converted);
        }
    });
    
    // Auto-format when user leaves the field (NOT while typing)
    $('.time-input').on('blur', function() {
        autoFormatTime(this);
        
        var $this = $(this);
        var isValid = validateTimeFormat($this.val());
        
        if (!isValid && $this.val() !== '') {
            $this.addClass('is-invalid');
            if ($this.next('.invalid-feedback').length === 0) {
                $this.after('<div class="invalid-feedback">Formato inválido. Use HH:MM en formato 24 horas (ejemplo: 14:30)</div>');
            }
        } else {
            $this.removeClass('is-invalid');
            $this.next('.invalid-feedback').remove();
        }
    });
    
    // Allow normal typing - only validate, don't reformat
    $('.time-input').on('input', function() {
        // Remove the invalid class while user is typing
        $(this).removeClass('is-invalid');
        $(this).next('.invalid-feedback').remove();
    });
    
    // Convert to 24-hour format before form submission
    $('form').on('beforeSubmit', function() {
        var isValid = true;
        
        $('.time-input').each(function() {
            var $thisField = $(this);
            
            // Auto-format before validation
            autoFormatTime(this);
            
            var converted = convertTo24Hour($thisField.val());
            if (converted !== $thisField.val()) {
                $thisField.val(converted);
            }
            
            if (!validateTimeFormat($thisField.val()) && $thisField.val() !== '') {
                $thisField.addClass('is-invalid');
                isValid = false;
            }
        });
        
        if (!isValid) {
            if ($('#time-validation-error').length === 0) {
                $('.sis-siniestro-form').prepend('<div id="time-validation-error" class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-exclamation-circle"></i> <strong>Error de validación:</strong> Por favor, corrija los errores en los campos de hora antes de guardar.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
            }
            return false;
        }
        
        $('#time-validation-error').remove();
        return true;
    });
});

// ============================================
// END TIME INPUT FORMATTING AND VALIDATION
// ============================================

// ============================================
// CUSTOM FILE INPUT - Show selected file name
// ============================================
$('#recipe-file-input, #informe-file-input').on('change', function() {
    var fileName = $(this).val().split('\\').pop();
    var labelId = $(this).attr('id') === 'recipe-file-input' ? '#recipe-file-label' : '#informe-file-label';
    
    if (fileName) {
        $(labelId).html('<i class="fas fa-file"></i> ' + fileName);
        $(labelId).addClass('selected');
    } else {
        $(labelId).html('<i class="fas fa-upload"></i> Seleccionar archivo...');
        $(labelId).removeClass('selected');
    }
});

JS, View::POS_END);

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
    const baremosPendientesPlazo = {$baremosPendientesPlazoJson};
    const baremosAgotados = {$baremosAgotadosJson};
    const baremosDisponiblesInfo = {$baremosDisponiblesInfoJson};
    const baremosPendientesInfo = {$baremosPendientesInfoJson};
    const baremosAgotadosInfo = {$baremosAgotadosInfoJson};
    
    const baremosSelect = $('#baremos-select');
    const form = baremosSelect.closest('form');
    const citaWarning = $('#cita-warning');
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Add CSS styles for modals (only once)
    function addModalStyles() {
        if ($('#servicios-modal-styles').length) return;
        var styles = '<style id="servicios-modal-styles">' +
            '.servicios-modal-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);backdrop-filter:blur(3px);display:flex;justify-content:center;align-items:center;z-index:10000;animation:fadeIn 0.2s ease;}' +
            '.servicios-modal{background:white;border-radius:12px;width:90%;max-width:1100px;max-height:85vh;display:flex;flex-direction:column;box-shadow:0 20px 40px rgba(0,0,0,0.3);animation:slideIn 0.3s ease;}' +
            '.servicios-modal-header{display:flex;align-items:center;padding:20px 24px;background:linear-gradient(135deg,#1e3c72 0%,#2a5298 100%);color:white;border-radius:12px 12px 0 0;gap:16px;}' +
            '.servicios-modal-header.header-warning{background:linear-gradient(135deg,#f0ad4e 0%,#ec971f 100%);}' +
            '.servicios-modal-header.header-danger{background:linear-gradient(135deg,#d9534f 0%,#c9302c 100%);}' +
            '.servicios-modal-header-icon{width:48px;height:48px;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px;}' +
            '.servicios-modal-header-title h3{margin:0;font-size:20px;font-weight:600;}' +
            '.servicios-modal-header-title p{margin:4px 0 0;font-size:13px;opacity:0.85;}' +
            '.servicios-modal-close{margin-left:auto;background:rgba(255,255,255,0.2);border:none;color:white;width:32px;height:32px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s;}' +
            '.servicios-modal-close:hover{background:rgba(255,255,255,0.3);transform:scale(1.05);}' +
            '.servicios-modal-body{flex:1;overflow-y:auto;padding:20px 24px;}' +
            '.servicios-info-banner{background:#e8f4fd;border-left:4px solid #1e3c72;padding:12px 16px;border-radius:8px;margin-bottom:16px;display:flex;align-items:flex-start;gap:12px;}' +
            '.servicios-info-banner i{color:#1e3c72;font-size:18px;margin-top:2px;}' +
            '.servicios-info-banner .info-text{font-size:13px;color:#2c3e50;line-height:1.4;}' +
            '.servicios-info-banner.info-warning{background:#fff8e7;border-left-color:#f0ad4e;}' +
            '.servicios-info-banner.info-warning i{color:#f0ad4e;}' +
            '.servicios-info-banner.info-danger{background:#fdf0ef;border-left-color:#d9534f;}' +
            '.servicios-info-banner.info-danger i{color:#d9534f;}' +
            '.servicios-selection-info{background:#e8f5e9;border-radius:8px;padding:10px 16px;margin-bottom:16px;display:flex;align-items:center;gap:10px;font-size:13px;color:#2e7d32;}' +
            '.servicios-toolbar{display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #e9ecef;margin-bottom:16px;}' +
            '.toolbar-actions{display:flex;gap:8px;}' +
            '.btn-toolbar{padding:6px 12px;background:#f8f9fa;border:1px solid #dee2e6;border-radius:6px;font-size:12px;cursor:pointer;transition:all 0.2s;}' +
            '.btn-toolbar:hover{background:#e9ecef;}' +
            '.toolbar-stats{font-size:13px;color:#6c757d;font-weight:500;}' +
            '.servicios-table-container{overflow-x:auto;border-radius:8px;border:1px solid #e9ecef;}' +
            '.servicios-table{width:100%;border-collapse:collapse;font-size:13px;}' +
            '.servicios-table thead th{background:#f8f9fa;padding:12px;text-align:left;font-weight:600;color:#495057;border-bottom:2px solid #dee2e6;}' +
            '.servicios-table tbody tr{border-bottom:1px solid #f0f0f0;transition:background 0.2s;cursor:pointer;}' +
            '.servicios-table tbody tr:hover{background:#f8f9fa;}' +
            '.servicios-table tbody td{padding:12px;vertical-align:middle;}' +
            '.checkbox-wrapper{position:relative;display:inline-block;}' +
            '.checkbox-wrapper input[type="checkbox"]{position:absolute;opacity:0;cursor:pointer;}' +
            '.checkbox-wrapper label{display:inline-block;width:18px;height:18px;border:2px solid #cbd5e0;border-radius:4px;background:white;cursor:pointer;transition:all 0.2s;}' +
            '.checkbox-wrapper input[type="checkbox"]:checked + label{background:#1e3c72;border-color:#1e3c72;}' +
            '.checkbox-wrapper input[type="checkbox"]:checked + label::after{content:"✓";display:block;color:white;font-size:12px;line-height:14px;text-align:center;}' +
            '.area-badge{background:#e8f4fd;color:#1e3c72;padding:4px 8px;border-radius:4px;font-size:11px;font-weight:500;display:inline-block;}' +
            '.disponibilidad-badge{display:inline-block;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;}' +
            '.disponibilidad-badge.disponible{background:#e8f5e9;color:#2e7d32;}' +
            '.servicio-nombre{font-weight:500;color:#2c3e50;}' +
            '.servicio-precio{font-weight:600;color:#28a745;text-align:right;}' +
            '.detail-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:20px;font-size:12px;}' +
            '.detail-badge.detail-warning{background:#fff8e7;color:#856404;}' +
            '.usage-progress{width:100%;}' +
            '.usage-stats{text-align:center;margin-bottom:4px;font-size:12px;}' +
            '.usage-used{font-weight:700;color:#dc3545;}' +
            '.progress-bar-container{background:#e9ecef;border-radius:4px;height:4px;overflow:hidden;}' +
            '.progress-bar-fill{height:100%;border-radius:4px;transition:width 0.3s;}' +
            '.servicios-modal-footer{padding:16px 24px;border-top:1px solid #e9ecef;display:flex;justify-content:space-between;align-items:center;background:#fafbfc;border-radius:0 0 12px 12px;}' +
            '.footer-summary{display:flex;gap:24px;}' +
            '.summary-item{display:flex;align-items:baseline;gap:6px;}' +
            '.summary-label{font-size:12px;color:#6c757d;}' +
            '.summary-value{font-size:18px;font-weight:700;color:#2c3e50;}' +
            '.summary-price{color:#28a745;}' +
            '.footer-actions{display:flex;gap:12px;}' +
            '.btn-modal{padding:8px 20px;border-radius:6px;font-size:13px;font-weight:500;cursor:pointer;transition:all 0.2s;border:none;}' +
            '.btn-modal-primary{background:linear-gradient(135deg,#1e3c72 0%,#2a5298 100%);color:white;}' +
            '.btn-modal-primary:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(30,60,114,0.3);}' +
            '.btn-modal-secondary{background:#f8f9fa;border:1px solid #dee2e6;color:#495057;}' +
            '.btn-modal-secondary:hover{background:#e9ecef;}' +
            '@keyframes fadeIn{from{opacity:0;}to{opacity:1;}}' +
            '@keyframes slideIn{from{opacity:0;transform:translateY(-20px);}to{opacity:1;transform:translateY(0);}}' +
            '@media (max-width:768px){.servicios-modal{width:95%;max-height:90vh;}.servicios-modal-header{padding:16px;}.servicios-modal-body{padding:16px;}.footer-summary{flex-direction:column;gap:8px;}.summary-value{font-size:14px;}.servicios-table thead th{font-size:11px;padding:8px;}.servicios-table tbody td{padding:8px;font-size:11px;}}' +
        '</style>';
        $('head').append(styles);
    }
    
    // Professional Modal for Available Services
    function showDisponiblesModal() {
        var currentlySelected = baremosSelect.val() || [];
        var items = {};
        Object.keys(baremosDisponiblesInfo).forEach(function(baremoId) {
            if (!currentlySelected.includes(baremoId.toString())) {
                items[baremoId] = baremosDisponiblesInfo[baremoId];
            }
        });
        var count = Object.keys(items).length;
        
        if (count === 0) {
            if (typeof toastr !== 'undefined') {
                toastr.info('No hay servicios disponibles adicionales. Todos los servicios ya han sido seleccionados.');
            } else {
                alert('No hay servicios disponibles adicionales. Todos los servicios ya han sido seleccionados.');
            }
            return;
        }
        
        var totalAvailablePrice = 0;
        Object.keys(items).forEach(function(baremoId) {
            totalAvailablePrice += parseFloat(items[baremoId].precio || 0);
        });
        
        var modalHtml = '<div class="servicios-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(3px); display: flex; justify-content: center; align-items: center; z-index: 10000;">' +
    '<div class="servicios-modal servicios-disponibles-modal" style="background: white; border-radius: 12px; width: 90%; max-width: 1100px; max-height: 85vh; display: flex; flex-direction: column; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);">' +
        '<div class="servicios-modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); padding: 20px 24px; border-radius: 12px 12px 0 0; display: flex; align-items: center; gap: 16px; color: white;">' +
            '<div class="servicios-modal-header-icon" style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;"><i class="fas fa-check-circle" style="color: white; font-size: 24px;"></i></div>' +
            '<div class="servicios-modal-header-title" style="flex: 1;">' +
                '<h3 style="color: white; margin: 0; font-size: 20px; font-weight: 600;">Servicios Médicos Disponibles</h3>' +
                '<p style="color: rgba(255, 255, 255, 0.9); margin: 4px 0 0; font-size: 13px;">' + count + ' servicio(s) disponible(s) para agregar</p>' +
            '</div>' +
            '<button type="button" class="servicios-modal-close" id="close-available-modal" style="background: rgba(255, 255, 255, 0.2); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;"><i class="fas fa-times" style="color: white;"></i></button>' +
        '</div>' +
        '<div class="servicios-modal-body" style="flex: 1; overflow-y: auto; padding: 20px 24px; background: white;">' +
            '<div class="servicios-info-banner" style="background: #e8f4fd; border-left: 4px solid #1e3c72; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; display: flex; align-items: flex-start; gap: 12px;">' +
                '<i class="fas fa-info-circle" style="color: #1e3c72; font-size: 18px;"></i>' +
                '<div class="info-text" style="font-size: 13px; color: #2c3e50; line-height: 1.4;">' +
                    '<strong style="color: #2c3e50;">¿Qué significa "Disponible"?</strong>' +
                    ' Estos servicios cumplen con los criterios (sin plazo de espera pendiente y con usos disponibles) ' +
                '</div>' +
            '</div>';
        
        if (currentlySelected.length > 0) {
            modalHtml += '<div class="servicios-selection-info">' +
                '<i class="fas fa-check-square"></i>' +
                '<span>Actualmente tienes <strong>' + currentlySelected.length + '</strong> servicio(s) seleccionado(s). Los servicios que ya están agregados no se muestran en esta lista.</span>' +
            '</div>';
        }
        
        modalHtml += '<div class="servicios-toolbar">' +
    
'<div class="servicios-table-container">' +
    '<table class="servicios-table">' +
        '<thead>' +
            '<tr style="background: #e9ecef !important;">' +
                '<th width="5%" style="background: #e9ecef !important; color: #1a1a1a !important; font-weight: 700 !important; padding: 12px !important;"><div class="checkbox-wrapper"><input type="checkbox" id="available-select-all-checkbox"><label for="available-select-all-checkbox"></label></div></th>' +
                '<th width="25%" style="background: #e9ecef !important; color: #1a1a1a !important; font-weight: 700 !important; padding: 12px !important;">Servicio</th>' +
                '<th width="18%" style="background: #e9ecef !important; color: #1a1a1a !important; font-weight: 700 !important; padding: 12px !important;">Área</th>' +
                '<th width="30%" style="background: #e9ecef !important; color: #1a1a1a !important; font-weight: 700 !important; padding: 12px !important;">Descripción</th>' +
                '<th width="12%" style="background: #e9ecef !important; color: #1a1a1a !important; font-weight: 700 !important; padding: 12px !important;">Disponibles</th>' +
                '<th width="10%" style="background: #e9ecef !important; color: #1a1a1a !important; font-weight: 700 !important; padding: 12px !important;">Precio</th>' +
            '</tr>' +
        '</thead>' +
        '<tbody>';
        
        Object.keys(items).forEach(function(baremoId) {
            var item = items[baremoId];
            if (!item) return;
            var disponibles = item.disponibles === 'Ilimitado' ? '∞' : item.disponibles;
            var precio = parseFloat(item.precio || 0).toFixed(2);
            var nombre = escapeHtml(item.nombre || 'Sin nombre');
            var area = escapeHtml(item.area || 'Sin área');
            var descripcion = escapeHtml(item.descripcion || 'Sin descripción');
            
            modalHtml += '<tr>' +
                '<td class="text-center"><div class="checkbox-wrapper"><input type="checkbox" class="service-checkbox-available" id="chk_' + baremoId + '" data-baremo-id="' + baremoId + '" data-price="' + precio + '"><label for="chk_' + baremoId + '"></label></div></td>' +
                '<td class="servicio-nombre"><strong>' + nombre + '</strong></td>' +
                '<td class="servicio-area"><span class="area-badge">' + area + '</span></td>' +
                '<td class="servicio-descripcion">' + descripcion + '</td>' +
                '<td class="text-center"><span class="disponibilidad-badge disponible">' + disponibles + '</span></td>' +
                '<td class="servicio-precio">$' + precio + '</td>' +
            '</tr>';
        });
        
        modalHtml += '</tbody>' +
                        '</table>' +
                    '</div>' +
                '</div>' +
                '<div class="servicios-modal-footer">' +
                    '<div class="footer-summary">' +
                        '<div class="summary-item"><span class="summary-label">Total de servicios:</span><span class="summary-value" id="available-total-services">' + count + '</span></div>' +
                        '<div class="summary-item"><span class="summary-label">Valor total:</span><span class="summary-value summary-price">$' + totalAvailablePrice.toFixed(2) + '</span></div>' +
                        '<div class="summary-item"><span class="summary-label">Seleccionados:</span><span class="summary-value" id="available-selected-summary">0</span></div>' +
                    '</div>' +
                    '<div class="footer-actions">' +
                        '<button type="button" class="btn-modal btn-modal-secondary" id="cancel-available-modal"><i class="fas fa-times"></i> Cancelar</button>' +
                        '<button type="button" class="btn-modal btn-modal-primary" id="add-selected-available"><i class="fas fa-plus-circle"></i> Agregar Seleccionados</button>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';
        
        $('.servicios-modal-overlay').remove();
        $('body').append(modalHtml);
        addModalStyles();
        
        function updateAvailableSelectedCount() {
            var selectedCount = $('.service-checkbox-available:checked').length;
            $('#available-selected-count').text(selectedCount + ' seleccionado' + (selectedCount !== 1 ? 's' : ''));
            $('#available-selected-summary').text(selectedCount);
            $('#available-select-all-checkbox').prop('checked', selectedCount === count && count > 0);
        }
        
        $('#available-select-all-checkbox').off('change').on('change', function() {
            $('.service-checkbox-available').prop('checked', $(this).prop('checked'));
            updateAvailableSelectedCount();
        });
        $(document).off('change', '.service-checkbox-available').on('change', '.service-checkbox-available', function() { updateAvailableSelectedCount(); });
        $('#select-all-available').off('click').on('click', function() { $('.service-checkbox-available').prop('checked', true); updateAvailableSelectedCount(); });
        $('#deselect-all-available').off('click').on('click', function() { $('.service-checkbox-available').prop('checked', false); updateAvailableSelectedCount(); });
        
        $('#add-selected-available').off('click').on('click', function() {
            var selectedServices = [];
            $('.service-checkbox-available:checked').each(function() {
                var baremoId = $(this).data('baremo-id');
                if (baremoId && baremosTotales[baremoId]) selectedServices.push(baremoId);
            });
            if (selectedServices.length === 0) {
                if (typeof toastr !== 'undefined') toastr.warning('Por favor, seleccione al menos un servicio.');
                else alert('Por favor, seleccione al menos un servicio.');
                return;
            }
            var currentValues = baremosSelect.val() || [];
            var addedCount = 0;
            selectedServices.forEach(function(baremoId) {
                if (!currentValues.includes(baremoId.toString())) {
                    currentValues.push(baremoId.toString());
                    addedCount++;
                }
            });
            if (addedCount > 0) {
                baremosSelect.val(currentValues).trigger('change');
                if (typeof toastr !== 'undefined') toastr.success(addedCount + ' servicio(s) agregado(s) correctamente.');
            } else {
                if (typeof toastr !== 'undefined') toastr.info('Los servicios seleccionados ya están agregados.');
            }
            $('.servicios-modal-overlay').fadeOut(300, function() { $(this).remove(); });
        });
        
        $('#close-available-modal, #cancel-available-modal').off('click').on('click', function() {
            $('.servicios-modal-overlay').fadeOut(300, function() { $(this).remove(); });
        });
        $('.servicios-modal-overlay').off('click').on('click', function(e) {
            if ($(e.target).hasClass('servicios-modal-overlay')) {
                $(this).fadeOut(300, function() { $(this).remove(); });
            }
        });
        $('.servicios-modal-overlay').fadeIn(300);
    }
    
    // Professional Modal for Restricted/Agotados Services
    function showRestrictedAgotadosModal(type) {
        var isRestringidos = (type === 'restringidos');
        var title = isRestringidos ? 'Servicios con Plazo de Espera Pendiente' : 'Servicios Agotados';
        var icon = isRestringidos ? 'fa-clock' : 'fa-ban';
        var items = isRestringidos ? baremosPendientesInfo : baremosAgotadosInfo;
        var count = Object.keys(items).length;
        
        if (count === 0) return;
        
        var headerClass = isRestringidos ? 'header-warning' : 'header-danger';
        var bannerClass = isRestringidos ? 'info-warning' : 'info-danger';
        var iconClass2 = isRestringidos ? 'fa-hourglass-half' : 'fa-exclamation-triangle';
        var statusText = isRestringidos ? 'en período de espera' : 'con límite de uso alcanzado';
        var infoText = isRestringidos ? '<strong>¿Qué significa "Plazo de Espera Pendiente"?</strong> Estos servicios requieren que haya transcurrido un período mínimo desde el inicio del contrato antes de poder ser utilizados. No están disponibles para selección hasta que se cumpla el plazo.' : '<strong>¿Qué significa "Agotado"?</strong> Estos servicios han alcanzado su límite máximo de usos permitidos por el plan. No pueden ser seleccionados nuevamente.';
        
        var modalHtml = '<div class="servicios-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(3px); display: flex; justify-content: center; align-items: center; z-index: 10000;">' +
    '<div class="servicios-modal servicios-' + (isRestringidos ? 'restringidos' : 'agotados') + '-modal" style="background: white; border-radius: 12px; width: 90%; max-width: 1100px; max-height: 85vh; display: flex; flex-direction: column; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);">' +
        '<div class="servicios-modal-header ' + headerClass + '" style="padding: 20px 24px; border-radius: 12px 12px 0 0; display: flex; align-items: center; gap: 16px; color: white;">' +
            '<div class="servicios-modal-header-icon" style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;"><i class="fas ' + icon + '" style="color: white; font-size: 24px;"></i></div>' +
            '<div class="servicios-modal-header-title" style="flex: 1;">' +
                '<h3 style="color: white; margin: 0; font-size: 20px; font-weight: 600;">' + title + '</h3>' +
                '<p style="color: rgba(255, 255, 255, 0.9); margin: 4px 0 0; font-size: 13px;">' + count + ' servicio(s) ' + statusText + '</p>' +
            '</div>' +
            '<button type="button" class="servicios-modal-close" id="close-restricted-modal" style="background: rgba(255, 255, 255, 0.2); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;"><i class="fas fa-times" style="color: white;"></i></button>' +
        '</div>' +
        '<div class="servicios-modal-body" style="flex: 1; overflow-y: auto; padding: 20px 24px; background: white;">' +
            '<div class="servicios-info-banner ' + bannerClass + '" style="padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; display: flex; align-items: flex-start; gap: 12px;">' +
                '<i class="fas ' + iconClass2 + '" style="font-size: 18px;"></i>' +
                '<div class="info-text" style="font-size: 13px; line-height: 1.4; color: #2c3e50;">' + infoText + '</div>' +
            '</div>' +
            '<div class="servicios-table-container" style="overflow-x: auto; border-radius: 8px; border: 1px solid #e9ecef;">' +
                '<table class="servicios-table" style="width: 100%; border-collapse: collapse; font-size: 13px;">' +
                    '<thead>' +
                        '<tr style="background: #e9ecef;">' +
                            '<th width="25%" style="background: #58bef5; color: #1a1a1a; font-weight: 700; padding: 12px; text-align: left;">Servicio</th>' +
                            '<th width="18%" style="background: #58bef5; color: #1a1a1a; font-weight: 700; padding: 12px; text-align: left;">Área</th>' +
                            '<th width="37%" style="background: #58bef5; color: #1a1a1a; font-weight: 700; padding: 12px; text-align: left;">Descripción</th>' +
                            '<th width="20%" style="background: #58bef5; color: #1a1a1a; font-weight: 700; padding: 12px; text-align: center;">' + (isRestringidos ? 'Tiempo Restante' : 'Uso Actual / Límite') + '</th>' +
                        '</tr>' +
                    '</thead>' +
                    '<tbody>';
        
        Object.keys(items).forEach(function(baremoId) {
            var item = items[baremoId];
            if (!item) return;
            var nombre = escapeHtml(item.nombre || 'Sin nombre');
            var area = escapeHtml(item.area || 'Sin área');
            var descripcion = escapeHtml(item.descripcion || 'Sin descripción');
            var detailHtml = '';
            
            if (isRestringidos) {
                detailHtml = '<span class="detail-badge detail-warning"><i class="fas fa-hourglass-half"></i> ' + (item.remaining_text || 'No disponible') + '</span>';
            } else {
                var used = parseInt(item.veces_usado) || 0;
                var limit = parseInt(item.cantidad_limite) || 0;
                var percentage = limit > 0 ? (used / limit) * 100 : 0;
                detailHtml = '<div class="usage-progress"><div class="usage-stats"><span class="usage-used">' + used + '</span> / ' + limit + '</div><div class="progress-bar-container"><div class="progress-bar-fill" style="width: ' + percentage + '%; background-color: #dc3545;"></div></div></div>';
            }
            
            modalHtml += '<tr>' +
                '<td class="servicio-nombre"><strong>' + nombre + '</strong></td>' +
                '<td class="servicio-area"><span class="area-badge">' + area + '</span></td>' +
                '<td class="servicio-descripcion">' + descripcion + '</td>' +
                '<td class="text-center">' + detailHtml + '</td>' +
            '</tr>';
        });
        
        modalHtml += '</tbody>' +
                        '</table>' +
                    '</div>' +
                '</div>' +
                '<div class="servicios-modal-footer">' +
                    '<div class="footer-summary"><div class="summary-item"><span class="summary-label">Total de servicios:</span><span class="summary-value">' + count + '</span></div></div>' +
                    '<div class="footer-actions"><button type="button" class="btn-modal btn-modal-secondary" id="close-restricted-modal-btn"><i class="fas fa-check"></i> Entendido</button></div>' +
                '</div>' +
            '</div>' +
        '</div>';
        
        $('.servicios-modal-overlay').remove();
        $('body').append(modalHtml);
        addModalStyles();
        
        $('#close-restricted-modal, #close-restricted-modal-btn').off('click').on('click', function() {
            $('.servicios-modal-overlay').fadeOut(300, function() { $(this).remove(); });
        });
        $('.servicios-modal-overlay').off('click').on('click', function(e) {
            if ($(e.target).hasClass('servicios-modal-overlay')) {
                $(this).fadeOut(300, function() { $(this).remove(); });
            }
        });
        $('.servicios-modal-overlay').fadeIn(300);
    }
    
    function initializeSelect2() {
        if ($('#baremos-select').data('select2')) $('#baremos-select').select2('destroy');
        $('#baremos-select').select2({
            multiple: true,
            placeholder: 'Busca o selecciona los servicios del Baremo...',
            allowClear: true,
            closeOnSelect: false,
            tags: false,
            tokenSeparators: [',', ' '],
            minimumInputLength: 0,
            templateResult: function(data) {
                if (data.id && baremosHtml[data.id]) return $(baremosHtml[data.id]);
                return data.text;
            },
            templateSelection: function(data) {
                if (data.id && baremosInfo[data.id]) return baremosInfo[data.id].nombre + ' (' + baremosInfo[data.id].area + ')';
                return data.text;
            },
            escapeMarkup: function(markup) { return markup; }
        });
        calcularTotalYTabla();
    }
    
    function calcularTotalYTabla() {
        var baremosSeleccionados = baremosSelect.val() || [];
        var total = 0;
        var tablaHtml = '';
        if (baremosSeleccionados.length === 0) {
            $('#costo-total-container, #baremos-tabla-container').hide();
            $('#costo-total-input').val('0.00');
            $('#cobertura-warning').hide();
            return;
        }
        baremosSeleccionados.forEach(function(baremoId) {
            var item = baremosInfo[baremoId];
            if (item && item.precio !== undefined) {
                var precio = parseFloat(item.precio);
                total += precio;
                var restricciones = [];
                if (item.plazo_espera) restricciones.push('Plazo: ' + item.plazo_espera + ' meses');
                if (item.cantidad_limite > 0) {
                    var remaining = item.remaining_uses !== undefined ? item.remaining_uses : (item.cantidad_limite - item.veces_usado);
                    restricciones.push('Límite: ' + item.veces_usado + '/' + item.cantidad_limite + ' usos' + (remaining > 0 ? ' (' + remaining + ' restantes)' : ''));
                }
                tablaHtml += '<tr><td>' + item.nombre + '</td><td>' + item.area + '</td><td>' + (item.descripcion || 'Sin descripción') + '</td><td class="text-center">' + (restricciones.join('<br>') || 'Ninguna') + '</td><td class="text-end">$' + precio.toFixed(2) + '</td></tr>';
            }
        });
        $('#baremos-tabla-body').html(tablaHtml);
        $('#baremos-tabla-container').show();
        $('#costo-total-value, #summary-total-amount').html('$' + total.toFixed(2));
        $('#costo-total-input').val(total.toFixed(2));
        $('#costo-total-container').show();
        var totalDisponible = parseFloat({$totalDisponible});
        if (total > totalDisponible) {
            $('#cobertura-difference-text').text('Excede por $' + (total - totalDisponible).toFixed(2));
            $('#cobertura-warning').show();
        } else {
            $('#cobertura-warning').hide();
        }
    }
    
    function calculateTotal() {
        var selectedValues = baremosSelect.val() || [];
        var total = 0;
        selectedValues.forEach(function(baremoId) {
            var item = baremosInfo[baremoId];
            if (item && item.precio !== undefined) total += parseFloat(item.precio);
        });
        return total;
    }
    
    function validateFormBeforeSubmit() {
        var selectedValues = baremosSelect.val() || [];
        if (selectedValues.length === 0) {
            if (!$('#empty-selection-error').length) {
                $('.baremo-select-container').after('<div id="empty-selection-error" class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <strong>¡Atención!</strong> Debe seleccionar al menos un servicio médico para registrar la atención.</div>');
            }
            $('html, body').animate({ scrollTop: $('#empty-selection-error').offset().top - 100 }, 500);
            return false;
        }
        var totalDisponible = parseFloat({$totalDisponible});
        if (calculateTotal() > totalDisponible) {
            $('#cobertura-warning').show();
            $('html, body').animate({ scrollTop: $('#cobertura-warning').offset().top - 100 }, 500);
            return false;
        }
        $('#empty-selection-error').remove();
        return true;
    }
    
    function setupEventHandlers() {
        $('.stat-badge.disponible').off('click').on('click', function(e) { e.preventDefault(); e.stopPropagation(); showDisponiblesModal(); });
        $('.stat-badge.restringido').off('click').on('click', function(e) { e.preventDefault(); e.stopPropagation(); showRestrictedAgotadosModal('restringidos'); });
        $('.stat-badge.agotado').off('click').on('click', function(e) { e.preventDefault(); e.stopPropagation(); showRestrictedAgotadosModal('agotados'); });
        baremosSelect.off('change').on('change', function() { calcularTotalYTabla(); });
        $('#recalculate-total').off('click').on('click', function() { calcularTotalYTabla(); $(this).html('<i class="fas fa-check me-1"></i> ¡Recalculado!'); setTimeout(function() { $('#recalculate-total').html('<i class="fas fa-redo me-1"></i> Recalcular'); }, 2000); });
        form.off('beforeSubmit').on('beforeSubmit', function(e) { if (!validateFormBeforeSubmit()) { e.preventDefault(); return false; } return true; });
    }
    
    $(document).ready(function() {
        setTimeout(function() {
            initializeSelect2();
            if ({$esCita} == 1) citaWarning.show(); else citaWarning.hide();
            setupEventHandlers();
            $('[data-toggle="tooltip"]').tooltip({ trigger: 'hover', placement: 'top', html: true });
        }, 300);
    });
})();
JS;
            $this->registerJs($jsCode, \yii\web\View::POS_END);
        }
        ?>