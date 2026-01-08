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
$plan = \app\models\Planes::findOne($afiliado->plan_id);
$precioPlan = $plan ? $plan->cobertura : 0;

// Obtener la sumatoria de siniestros del afiliado
$sumatoriaSiniestros = \app\models\SisSiniestro::find()
    ->where(['iduser' => $afiliado->id])
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
$contrato = \app\models\Contratos::find()
    ->where(['user_id' => $afiliado->id])
    ->andWhere(['estatus' => 'Activo'])
    ->orderBy(['created_at' => SORT_DESC])
    ->one();

// Get baremos data for historical section
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
        <!-- Historical Data Tables FIRST -->
        <?php if($esCita == 1 && !empty($baremosCitas)): ?>
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
        <?php elseif($esCita == 1 && empty($baremosCitas)): ?>
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

        <?php if($esCita == 0 && !empty($baremosSiniestros)): ?>
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
        <?php elseif($esCita == 0 && empty($baremosSiniestros)): ?>
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

        <!-- Stats Summary Card AFTER the tables -->
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
    <div class="ms-panel-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
        <h3 class="large-title section-title-white mb-0" id="titulo-datos-registro">
            <i class="fas fa-file-alt me-2"></i> <?= $tituloSeccion ?>
            <?php if($esCitaMode): ?>
                <span class="badge bg-warning ms-2">Modo Cita</span>
            <?php else: ?>
                <span class="badge bg-info ms-2">Modo Atención</span>
            <?php endif; ?>
        </h3>
    </div>
    <div class="ms-panel-body">
        <!-- Hidden field -->
        <div style="display: none;">
            <?= $form->field($model, 'idclinica')->textInput(['value' => $afiliado->clinica_id]) ?>
        </div>

        <!-- Basic Information Card -->
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
                        )->label('¿Fue atendido?') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attention Details Card -->
        <div class="card mb-4">
            <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <i class="fas fa-stethoscope me-2"></i> Detalles de la Atención
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 field-with-icon">
                        <i class="fas fa-calendar-check"></i>
                        <?= $form->field($model, 'fecha_atencion')->textInput([
                            'type' => 'date',
                            'class' => 'form-control form-control-lg',
                            'placeholder' => 'Seleccione la fecha',
                            'autocomplete' => 'off',
                            'value' => $model->isNewRecord ? date('Y-m-d') : Yii::$app->formatter->asDate($model->fecha, 'yyyy-MM-dd')
                        ])->label('Fecha de la ' . $terminoPrincipal) ?>
                    </div>
                    
                    <div class="col-md-6 field-with-icon">
                        <i class="fas fa-clock"></i>
                        <?= $form->field($model, 'hora_atencion')->textInput([
                            'type' => 'time', 
                            'class' => 'form-control form-control-lg'
                        ])->label('Hora de la ' . $terminoPrincipal) ?>
                    </div>
                    
                    <div class="col-md-12 field-with-icon">
                        <i class="fas fa-align-left"></i>
                        <?= $form->field($model, 'descripcion')->textarea([
                            'rows' => 3, 
                            'class' => 'form-control form-control-lg',
                            'placeholder' => 'Describa los detalles de la ' . strtolower($terminoPrincipal) . '...'
                        ])->label('Descripción de la '. $terminoPrincipal) ?>
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
                                'maxFileSize' => 10240,
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
                        ])->label('Récipe Médico'); ?>
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
                                'maxFileSize' => 10240,
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
                        ])->label('Informe Médico'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


    <!-- ===== SECTION 4: SELECCIÓN DE SERVICIOS MÉDICOS ===== -->
    <?php
    // This PHP code block is EXACTLY the same as before
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
        // Modo Siniestro: Mostrar solo servicios SIN restricciones

        $query->andWhere(['planes_items_cobertura.plazo_espera' => 0]);
        /*$query->andWhere([
            'and',
            ['or',
                ['planes_items_cobertura.plazo_espera' => null],
                ['planes_items_cobertura.plazo_espera' => 0]
            ],
            ['or',
                ['planes_items_cobertura.cantidad_limite' => null],
                ['planes_items_cobertura.cantidad_limite' => 0]
            ]
        ]);*/
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
                $vecesUsado = \app\models\SisSiniestroBaremo::find()
                    ->joinWith('siniestro')
                    ->where(['baremo_id' => $item->baremo_id])
                    ->andWhere(['iduser' => $afiliado->id]);
                
                // For Siniestro mode, only count actual siniestros (not citas)
                if (!$esCitaMode) {
                    $vecesUsado->andWhere(['sis_siniestro.es_cita' => 0]);
                }
                
                $vecesUsado = $vecesUsado->count();

                // Verificar si excede el límite (solo si tiene límite definido)
                $excedeLimite = false;
                if ($item->cantidad_limite !== null && $item->cantidad_limite > 0) {
                    // Si tiene un límite positivo, verificamos si se alcanzó
                    if ($vecesUsado >= $item->cantidad_limite) {
                        $excedeLimite = true;
                    }
                }

                // Verificar si este baremo está entre los seleccionados (solo para update)
                $esBaremoGuardado = !$model->isNewRecord && in_array($item->baremo_id, $selectedBaremos);
                
                // If item exceeds limit and is NOT a previously saved item (historico)
                if ($excedeLimite && !$esBaremoGuardado) {
                    // Add to agotados array for tracking
                    $baremosAgotados[$item->baremo_id] = $textoPlano;
                    $baremosInfo[$item->baremo_id] = [
                        'nombre' => $servicio,
                        'area' => $area,
                        'descripcion' => $descripcion,
                        'plazo_espera' => $item->plazo_espera,
                        'cantidad_limite' => $item->cantidad_limite,
                        'veces_usado' => $vecesUsado,
                        'precio' => $precioBaremo,
                        'is_restricted_by_plazo' => $isRestrictedByPlazo,
                        'has_plazo_ever' => $hasPlazoEver,
                        'excede_limite' => $excedeLimite,
                        'es_historico' => $esBaremoGuardado,
                        'es_agotado' => true,
                    ];
                    
                    // Skip further processing for agotados (they won't be selectable)
                    continue;
                }

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
                    if (($isRestrictedByPlazo || $excedeLimite)) {
                        if (!$esBaremoGuardado) {
                            $debeIncluirse = false;
                        }
                    }
                    
                } else {
                    // MODO SINIESTRO: Check BOTH plazo_espera AND cantidad_limite
                    if ($contrato) {
                        $fechaContratoIni = new \DateTime($contrato->fecha_ini);
                        
                        // Lógica de plazo de espera - APPLIES IN SINIESTRO TOO!
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
                    if (($isRestrictedByPlazo || $excedeLimite)) {
                        if (!$esBaremoGuardado) {
                            $debeIncluirse = false;
                        }
                    }
                    
                    // In Siniestro mode, include restricted items but mark them as not selectable
                    if ($isRestrictedByPlazo) {
                        // Track for the "Restringidos" badge count
                        $baremosPendientesPlazo[$item->baremo_id] = $textoPlano;
                                                          
                        // Calculate remaining months
                        $mesesRestantes = 0;
                        $diasRestantes = 0;
                        $tiempoRestanteTexto = '';

                        if ($contrato) {
                            $fechaContratoIni = new \DateTime($contrato->fecha_ini);
                            $plazoRequerido = (int)$item->plazo_espera;
                            
                            // Calculate target date (contract start + required waiting period)
                            $fechaTarget = clone $fechaContratoIni;
                            $fechaTarget->modify("+{$plazoRequerido} months");
                            
                            // Calculate difference between now and target date
                            $diff = $fechaActual->diff($fechaTarget);
                            
                            $mesesRestantes = ($diff->y * 12) + $diff->m;
                            $diasRestantes = $diff->d;
                            
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
                        }

                        // Create a SIMPLE HTML version for restricted items
                        $simpleHtml = "<div class='baremo-dropdown-option'>";
                        $simpleHtml .= "<div class='baremo-first-row'>";
                        $simpleHtml .= "<div class='baremo-content-main'>";
                        $simpleHtml .= "<div class='baremo-area'>";
                        $simpleHtml .= "<div class='baremo-area-label'>Área</div>";
                        $simpleHtml .= "<div class='baremo-area-value'>" . $area . "</div>";
                        $simpleHtml .= "</div>";
                        $simpleHtml .= "<div class='baremo-servicio'>";
                        $simpleHtml .= "<div class='baremo-servicio-label'>Servicio</div>";
                        $simpleHtml .= "<div class='baremo-servicio-value'>" . $servicio . "</div>";
                        $simpleHtml .= "</div>";
                        $simpleHtml .= "<div class='baremo-descripcion'>";
                        $simpleHtml .= "<div class='baremo-descripcion-label'>Descripción</div>";
                        $simpleHtml .= "<div class='baremo-descripcion-value'>" . ($descripcion ?: 'Sin descripción') . "</div>";
                        $simpleHtml .= "</div>";
                        $simpleHtml .= "</div>"; // Close content-main
                        
                        // Add status badge
                        $simpleHtml .= "<div class='baremo-status'>";
                        $simpleHtml .= "<span class='restringido'>Restringido</span>";
                        $simpleHtml .= "</div>";
                        $simpleHtml .= "</div>"; // Close first-row
                        
                        // ROW 2: Price and Waiting Period
                        $simpleHtml .= "<div class='baremo-second-row'>";
                        $simpleHtml .= "<div class='baremo-price-container'>";
                        $simpleHtml .= "<span class='baremo-price'>" . number_format($precioBaremo, 2) . "</span>";
                        $simpleHtml .= "</div>";
                        $simpleHtml .= "<div class='baremo-waiting-period'>";
                        $simpleHtml .= "<i class='fas fa-clock me-1'></i>";                               
                        $simpleHtml .= "<span>Disponible en " . $tiempoRestanteTexto . "</span>";
                        $simpleHtml .= "</div>";
                        $simpleHtml .= "</div>"; // Close second-row
                        $simpleHtml .= "</div>"; // Close baremo-dropdown-option
                        
                        // Add to HTML for display (but they won't be selectable)
                        $baremosHtml[$item->baremo_id] = $simpleHtml;
                        $baremosRestringidosIDs[] = $item->baremo_id;
                        
                        // Add to info array with remaining months
                        $baremosInfo[$item->baremo_id] = [
                            'nombre' => $servicio,
                            'area' => $area,
                            'descripcion' => $descripcion,
                            'plazo_espera' => $item->plazo_espera,
                            'cantidad_limite' => $item->cantidad_limite,
                            'veces_usado' => $vecesUsado,
                            'precio' => $precioBaremo,
                            'is_restricted_by_plazo' => $isRestrictedByPlazo,
                            'has_plazo_ever' => $hasPlazoEver,
                            'excede_limite' => $excedeLimite,
                            'es_historico' => $esBaremoGuardado,
                            'remaining_months' => $mesesRestantes,
                            'remaining_days' => $diasRestantes,
                            'remaining_text' => $tiempoRestanteTexto,
                        ];
                        
                        // Don't include in selectable items array
                        if (!$esBaremoGuardado) {
                            continue; // Skip adding to selectable items arrays
                        }
                    }
                }

                
                // TEXT FOR DROPDOWN VALUE
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
                } elseif ($isRestrictedByPlazo && $esCitaMode) {
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
                $htmlFormateado .= "<div class='baremo-availability " . $availabilityClass . "'>" . $availabilityText . "</div>";
                $htmlFormateado .= "</div>"; // Close second-row
                $htmlFormateado .= "</div>"; // Close baremo-dropdown-option

                // Si es un baremo guardado que no cumple los filtros
                if ($esBaremoGuardado && !$debeIncluirse) {
                    $baremosForzados[$item->baremo_id] = $textoPlano;
                    $baremosHtml[$item->baremo_id] = $htmlFormateado;
                    $debeIncluirse = true;
                }

                if (!$debeIncluirse) {
                    continue;
                }

                // Clasificación según disponibilidad
                if ($esBaremoGuardado && isset($baremosForzados[$item->baremo_id])) {
                    // Ya se asignó en la sección de forzados
                } elseif ($esCitaMode) {
                    if (!$hasPlazoEver) {
                        $baremosSinPlazo[$item->baremo_id] = $textoPlano;
                        $baremosHtml[$item->baremo_id] = $htmlFormateado;
                    } elseif ($isRestrictedByPlazo) {
                        $baremosPendientesPlazo[$item->baremo_id] = $textoPlano;
                        $baremosHtml[$item->baremo_id] = $htmlFormateado;
                        $baremosRestringidosIDs[] = $item->baremo_id;
                    } else {
                        $baremosConPlazoCumplido[$item->baremo_id] = $textoPlano;
                        $baremosHtml[$item->baremo_id] = $htmlFormateado;
                    }
                } else {
                    // Modo Siniestro - los baremos disponibles van a $baremosSinPlazo
                    $baremosSinPlazo[$item->baremo_id] = $textoPlano;
                    $baremosHtml[$item->baremo_id] = $htmlFormateado;
                }

                $baremosInfo[$item->baremo_id] = [
                    'nombre' => $servicio,
                    'area' => $area,
                    'descripcion' => $descripcion,
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

        $baremosTotales = $baremosForzados + $baremosSinPlazo + $baremosConPlazoCumplido;
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
                    <span class="badge badge-pill stat-badge disponible" 
                        data-toggle="tooltip" 
                        data-placement="top" 
                        title="<?= htmlspecialchars('Servicios que cumplen todos los criterios y pueden ser seleccionados ahora mismo.', ENT_QUOTES) ?>">
                        <i class="fas fa-check-circle me-3"></i>
                        <?php 
                            $totalDisponibles = 0;
                            if ($esCitaMode) {
                                $totalDisponibles = count($baremosSinPlazo) + count($baremosConPlazoCumplido);
                            } else {
                                $totalDisponibles = count($baremosSinPlazo);
                            }
                            echo $totalDisponibles;
                            ?> Disponibles
                    </span>

                    <?php if (count($baremosPendientesPlazo) > 0): ?>
                    <span class="badge badge-pill stat-badge restringido" 
                        data-toggle="tooltip" 
                        data-placement="top" 
                        title="<?= htmlspecialchars('Servicios con plazo de espera pendiente. ' . ($esCitaMode ? 'No disponibles en modo Cita.' : 'No disponibles en modo Siniestro.'), ENT_QUOTES) ?>">
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
                    <span class="badge badge-pill stat-badge agotado" 
                        data-toggle="tooltip" 
                        data-placement="top" 
                        title="<?= htmlspecialchars('Servicios que han alcanzado su límite máximo de usos. No disponibles para selección.', ENT_QUOTES) ?>">
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
                            $totalGeneral = count($baremosForzados) + $totalAgotados + count($baremosPendientesPlazo);
                            
                            if ($esCitaMode) {
                                $totalGeneral += count($baremosSinPlazo) + count($baremosConPlazoCumplido);
                            } else {
                                $totalGeneral += count($baremosSinPlazo);
                            }
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

<!-- ===== MODALS ===== -->
<?php
Modal::begin([
    'title' => '<h4>Detalles del Afiliado <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>',
    'id' => 'afiliado-modal', 
    'size' => Modal::SIZE_LARGE, 
    'options' => [
        'tabindex' => false, 
        'class' => 'fade',
        'role' => 'dialog', 
    ],
    'dialogOptions' => ['class' => 'modal-dialog-centered'], 
]);

echo $this->render('/user-datos/view', ['model' => $afiliado]); 

Modal::end();
?>

<?php
// Add JavaScript for collapsible historial section
$this->registerJs(<<<JS
// Toggle historial section
$('#historial-toggle-header').on('click', function() {
    const content = $('#historial-content');
    const chevron = $('#historial-chevron');
    
    if (content.is(':visible')) {
        content.slideUp(300);
        chevron.removeClass('fa-chevron-up').addClass('fa-chevron-down');
    } else {
        content.slideDown(300);
        chevron.removeClass('fa-chevron-down').addClass('fa-chevron-up');
    }
});

// Afiliado modal trigger
$('#btn-abrir-afiliado-modal').on('click', function(e) {
    e.preventDefault();
    
    setTimeout(function() {
        $('#afiliado-modal').modal('show');
    }, 50); 
});
JS
, View::POS_END);

// Include the original baremos JavaScript
if (isset($baremosTotales) && isset($baremosHtml) && isset($baremosInfo) && isset($baremosRestringidosIDs)) {
    $baremosTotalesJson = json_encode($baremosTotales);
    $baremosHtmlJson = json_encode($baremosHtml);
    $baremosInfoJson = json_encode($baremosInfo);
    $baremosRestringidosJson = json_encode($baremosRestringidosIDs);
    $baremosPendientesPlazoJson = json_encode($baremosPendientesPlazo ?? []);
    $baremosAgotadosJson = json_encode($baremosAgotados ?? []);
    $baremosPendientesInfoJson = json_encode(array_intersect_key($baremosInfo, $baremosPendientesPlazo));
    $baremosAgotadosInfoJson = json_encode(array_intersect_key($baremosInfo, $baremosAgotados));

$jsCode = <<<JS
(function() {
    'use strict';
    
    // Capture variables from PHP
    const baremosTotales = {$baremosTotalesJson};
    const baremosHtml = {$baremosHtmlJson};
    const baremosInfo = {$baremosInfoJson};
    const baremosRestringidosIDs = {$baremosRestringidosJson};
    const baremosPendientesPlazo = {$baremosPendientesPlazoJson};
    const baremosAgotados = {$baremosAgotadosJson};
    const baremosPendientesInfo = {$baremosPendientesInfoJson};
    const baremosAgotadosInfo = {$baremosAgotadosInfoJson};
    
    // DOM elements cache
    const baremosSelect = \$('#baremos-select');
    const form = baremosSelect.closest('form');
    const citaWarning = \$('#cita-warning');
    
    // Function to show detailed modal for restricted/agotados items
    function showRestrictedAgotadosModal(type) {
        const isRestringidos = (type === 'restringidos');
        const title = isRestringidos ? 'Servicios con Plazo de Espera Pendiente' : 'Servicios Agotados (Límite Alcanzado)';
        const icon = isRestringidos ? '<i class="fas fa-clock"></i>' : '<i class="fas fa-ban"></i>';
        const items = isRestringidos ? baremosPendientesInfo : baremosAgotadosInfo;
        const count = isRestringidos ? baremosRestringidosIDs.length : Object.keys(baremosAgotadosInfo).length;
        
        if (count === 0) {
            return;
        }
        
        // Create modal HTML
        let modalHtml = '<div class="restricted-agotados-modal-overlay">';
        modalHtml += '<div class="restricted-agotados-modal">';
        
        // Header
        modalHtml += '<div class="modal-header">';
        modalHtml += '<div class="modal-icon">' + icon + '</div>';
        modalHtml += '<h3 class="modal-title">' + title + ' (' + count + ')</h3>';
        modalHtml += '<button type="button" class="modal-close" id="close-details-modal">&times;</button>';
        modalHtml += '</div>';
        
        // Content
        modalHtml += '<div class="modal-content">';
        
        if (isRestringidos) {
            modalHtml += '<div class="modal-explanation">';
            modalHtml += '<p><strong>¿Qué significa "Plazo de Espera Pendiente"?</strong></p>';
            modalHtml += '<p>Estos servicios requieren que haya transcurrido un período mínimo desde el inicio del contrato antes de poder ser utilizados. El plazo varía según el servicio.</p>';
            modalHtml += '</div>';
        } else {
            modalHtml += '<div class="modal-explanation">';
            modalHtml += '<p><strong>¿Qué significa "Agotado"?</strong></p>';
            modalHtml += '<p>Estos servicios han alcanzado su límite máximo de usos permitidos por el plan. No pueden ser seleccionados nuevamente.</p>';
            modalHtml += '</div>';
        }
        
        // Items table
        modalHtml += '<div class="items-table-container">';
        modalHtml += '<table class="items-table">';
        modalHtml += '<thead>';
        modalHtml += '<tr>';
        modalHtml += '<th width="25%">Servicio</th>';
        modalHtml += '<th width="20%">Área</th>';
        modalHtml += '<th width="35%">Descripción</th>';
        modalHtml += '<th width="20%">' + (isRestringidos ? 'Tiempo Restante' : 'Uso Actual') + '</th>';
        modalHtml += '</tr>';
        modalHtml += '</thead>';
        modalHtml += '<tbody>';
        
        // Add each item
        Object.keys(items).forEach(function(baremoId) {
            const item = items[baremoId];
            if (!item) return;
            
            modalHtml += '<tr>';
            modalHtml += '<td><strong>' + (item.nombre || 'Sin nombre') + '</strong></td>';
            modalHtml += '<td>' + (item.area || 'Sin área') + '</td>';
            modalHtml += '<td>' + (item.descripcion || 'Sin descripción') + '</td>';
            
            if (isRestringidos) {
                let remainingText = '';
                if (item.remaining_text) {
                    remainingText = item.remaining_text;
                } else if (item.plazo_espera) {
                    remainingText = 'Plazo: ' + item.plazo_espera + ' meses';
                    if (item.remaining_months || item.remaining_days) {
                        const months = item.remaining_months || 0;
                        const days = item.remaining_days || 0;
                        if (months > 0 && days > 0) {
                            remainingText += '<br><small>Faltan: ' + months + ' mes' + (months > 1 ? 'es' : '') + ' y ' + days + ' día' + (days > 1 ? 's' : '') + '</small>';
                        } else if (months > 0) {
                            remainingText += '<br><small>Faltan: ' + months + ' mes' + (months > 1 ? 'es' : '') + '</small>';
                        }
                    }
                }
                modalHtml += '<td class="text-center"><span class="time-badge">' + (remainingText || 'No disponible') + '</span></td>';
            } else {
                const used = item.veces_usado || 0;
                const limit = item.cantidad_limite || 0;
                modalHtml += '<td class="text-center"><span class="usage-badge">' + used + ' / ' + limit + '</span></td>';
            }
            
            modalHtml += '</tr>';
        });
        
        modalHtml += '</tbody>';
        modalHtml += '</table>';
        modalHtml += '</div>';
        
        // Footer
        modalHtml += '<div class="modal-footer">';
        modalHtml += '<button type="button" class="btn btn-secondary" id="close-details-modal-btn">';
        modalHtml += '<i class="fas fa-times me-1"></i> Cerrar';
        modalHtml += '</button>';
        modalHtml += '</div>';
        
        modalHtml += '</div>';
        modalHtml += '</div>';
        modalHtml += '</div>';
        
        // Remove any existing modal
        \$('.restricted-agotados-modal-overlay').remove();
        
        // Add to DOM
        \$('body').append(modalHtml);
        
        // Add click handlers
        \$('#close-details-modal, #close-details-modal-btn').off('click').on('click', function() {
            \$('.restricted-agotados-modal-overlay').fadeOut(300, function() {
                \$(this).remove();
            });
        });
        
        // Close when clicking outside
        \$('.restricted-agotados-modal-overlay').off('click').on('click', function(e) {
            if (\$(e.target).hasClass('restricted-agotados-modal-overlay')) {
                \$(this).fadeOut(300, function() {
                    \$(this).remove();
                });
            }
        });
        
        // Show modal
        \$('.restricted-agotados-modal-overlay').fadeIn(300);
    }
    
    // Initialize Select2 with enhanced templates
    function initializeSelect2() {
        // Destroy existing instance if any
        if (\$('#baremos-select').data('select2')) {
            \$('#baremos-select').select2('destroy');
        }
        
        // Initialize Select2 with custom templates
        \$('#baremos-select').select2({
            multiple: true,
            placeholder: 'Busca o selecciona los servicios del Baremo...',
            allowClear: true,
            closeOnSelect: false,
            tags: false,
            tokenSeparators: [',', ' '],
            minimumInputLength: 0,
            templateResult: function(data) {
                if (data.id && baremosHtml[data.id]) {
                    const html = \$(baremosHtml[data.id]);
                    html.data('baremo-id', data.id);
                    return html;
                }
                return data.text;
            },
            templateSelection: function(data) {
                if (data.id && baremosInfo[data.id]) {
                    const item = baremosInfo[data.id];
                    return item.nombre + ' (' + item.area + ')';
                }
                return data.text;
            },
            escapeMarkup: function(markup) {
                return markup;
            }
        });
        
        // Calculate initial total if there are selections
        calcularTotalYTabla();
    }
    
    // Function to calculate total and update table
    function calcularTotalYTabla() {
        const baremosSeleccionados = baremosSelect.val() || [];
        let total = 0.00;
        let tablaHtml = '';
        
        if (baremosSeleccionados.length === 0) {
            \$('#costo-total-container').hide();
            \$('#baremos-tabla-container').hide();
            \$('#costo-total-input').val('0.00');
            \$('#cobertura-warning').hide();
            return;
        }

        baremosSeleccionados.forEach(function(baremoId) {
            const item = baremosInfo[baremoId];
            
            if (item && item.precio !== undefined) {
                const precio = parseFloat(item.precio);
                total += precio;
                
                let restricciones = [];
                if (item.plazo_espera) {
                    restricciones.push('Plazo: ' + item.plazo_espera + ' meses');
                }
                if (item.cantidad_limite > 0) {
                    restricciones.push('Límite: ' + item.veces_usado + '/' + item.cantidad_limite + ' usos');
                }
                const restriccionesHtml = restricciones.join('<br>');
                
                tablaHtml += '<tr>';
                tablaHtml += '<td>' + item.nombre + '</td>';
                tablaHtml += '<td>' + item.area + '</td>';
                tablaHtml += '<td>' + (item.descripcion || 'Sin descripción') + '</td>';
                tablaHtml += '<td class="text-center">' + (restriccionesHtml || 'Ninguna') + '</td>';
                tablaHtml += '<td class="cost-col">\$' + precio.toFixed(2) + '</td>';
                tablaHtml += '</tr>';
            }
        });

        \$('#baremos-tabla-body').html(tablaHtml);
        \$('#baremos-tabla-container').show();
        
        \$('#costo-total-value').html('\$' + total.toFixed(2));
        \$('#summary-total-amount').html('\$' + total.toFixed(2));
        \$('#costo-total-input').val(total.toFixed(2));
        \$('#costo-total-container').show();
        
        const totalDisponible = $totalDisponible;
        const warningContainer = \$('#cobertura-warning');
        
        warningContainer.hide();
        
        if (total > totalDisponible) {
            const difference = total - totalDisponible;
            \$('#cobertura-difference-text').text('Excede por \$' + difference.toFixed(2));
            warningContainer.show();
        }
    }
    
    // Form validation
    function validateFormBeforeSubmit() {
        const selectedValues = baremosSelect.val() || [];
        
        if (selectedValues.length === 0) {
            if (!\$('#empty-selection-error').length) {
                const errorHtml = '<div id="empty-selection-error" class="alert alert-danger alert-dismissible fade show" style="margin-top: 1rem;">' +
                    '<div class="d-flex">' +
                        '<div class="alert-icon">' +
                            '<i class="fas fa-exclamation-circle"></i>' +
                        '</div>' +
                        '<div class="alert-content">' +
                            '<h5 class="alert-heading">¡Atención!</h5>' +
                            '<p class="mb-0"><strong>Debe seleccionar al menos un servicio médico</strong> para registrar la atención.</p>' +
                        '</div>' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                    '</div>' +
                '</div>';
                
                \$('.baremo-select-container').after(errorHtml);
            }
            
            \$('html, body').animate({
                scrollTop: \$('#empty-selection-error').offset().top - 100
            }, 500);
            
            return false;
        }
        
        // Check coverage warning
        const totalDisponible = $totalDisponible;
        const selectedTotal = calculateTotal();
        
        if (selectedTotal > totalDisponible) {
            \$('#cobertura-warning').show();
            \$('html, body').animate({
                scrollTop: \$('#cobertura-warning').offset().top - 100
            }, 500);
            return false;
        }
        
        // Remove any errors
        \$('#empty-selection-error').remove();
        
        return true;
    }
    
    function calculateTotal() {
        const selectedValues = baremosSelect.val() || [];
        let total = 0;
        
        selectedValues.forEach(function(baremoId) {
            const item = baremosInfo[baremoId];
            if (item && item.precio !== undefined) {
                total += parseFloat(item.precio);
            }
        });
        
        return total;
    }
    
    // Event handlers setup
    function setupEventHandlers() {
        // Make restricted/agotados badges clickable
        \$('.stat-badge.restringido').off('click').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            showRestrictedAgotadosModal('restringidos');
        });
        
        \$('.stat-badge.agotado').off('click').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            showRestrictedAgotadosModal('agotados');
        });
        
        // Select2 change handler
        baremosSelect.off('change').on('change', function() {
            calcularTotalYTabla();
        });
        
        // Recalculate total button
        \$('#recalculate-total').off('click').on('click', function() {
            calcularTotalYTabla();
            \$(this).html('<i class="fas fa-check me-1"></i> ¡Recalculado!');
            setTimeout(() => {
                \$(this).html('<i class="fas fa-redo me-1"></i> Recalcular');
            }, 2000);
        });
        
        // Form submission validation
        form.off('beforeSubmit').on('beforeSubmit', function(e) {
            if (!validateFormBeforeSubmit()) {
                e.preventDefault();
                return false;
            }
            
            return true;
        });
    }
    
    // Initialize everything when DOM is ready
    \$(document).ready(function() {
        // Initialize Select2
        setTimeout(function() {
            initializeSelect2();
            
            // Show/hide cita warning based on mode
            if ({$esCita} == 1) {
                citaWarning.show();
            } else {
                citaWarning.hide();
            }
            
            // Setup event handlers
            setupEventHandlers();
            
            // Initialize Bootstrap tooltips
            \$('[data-toggle="tooltip"]').tooltip({
                trigger: 'hover',
                placement: 'top',
                html: true,
                container: 'body'
            });
        }, 300);
    });
    
})();
JS;

    $this->registerJs($jsCode, \yii\web\View::POS_END);
}
?>
<?php
$this->registerJs(<<<JS
    \$('#btn-abrir-afiliado-modal').on('click', function(e) {
        e.preventDefault();
        
        setTimeout(function() {
            \$('#afiliado-modal').modal('show');
        }, 50); 
    });
JS
, View::POS_END);
?>