<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\web\View;

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

// CSS personalizado (Mantengo el CSS intacto)
$css = <<<CSS
/*.sis-siniestro-form {
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}*/

.section-title {
    font-size: 18px;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
}

.section-title i {
    margin-right: 10px;
    font-size: 20px;
}

.text-blue-600 {
    color: white !important;
}

.select2-container--krajee .select2-selection--multiple,
.select2-container--krajee .select2-selection--single {
    border-radius: 8px;
    border: 1px solid #ced4da;
    padding: 6px 15px;
    min-height: 48px;
    display: flex;
    align-items: center;
}

.select2-container--krajee .select2-selection--multiple .select2-selection__choice {
    border-radius: 6px;
    background-color: #e9f2ff;
    border: 1px solid #c5d9f8;
    color: #2c3e50;
    padding: 3px 8px;
}

.btn {
    border-radius: 8px;
    padding: 12px 24px;
    font-weight: 600;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn i {
    margin-right: 8px;
}

.btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

.btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.btn-warning {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #212529;
}

.btn-warning:hover {
    background-color: #e0a800;
    border-color: #d39e00;
    color: #212529;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.btn-outline-dark {
    border-color: #343a40;
    color: #343a40;
}

.btn-outline-dark:hover {
    background-color: #343a40;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.afiliado-container {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border-left: 4px solid #4a90e2;
    max-height: 600px;
    overflow-y: auto;
}

/* Mejoras para la vista del afiliado */
.afiliado-container .card {
    box-shadow: none;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
}

.afiliado-container .card-header {
    background: linear-gradient(135deg, #4a90e2 0%, #2c3e50 100%);
    color: white;
    border-radius: 8px 8px 0 0 !important;
}

.afiliado-container .nav-tabs .nav-link {
    color: #6c757d;
    font-weight: 600;
    border: none;
    border-bottom: 3px solid transparent;
}

.afiliado-container .nav-tabs .nav-link.active {
    color: #4a90e2;
    background-color: transparent;
    border-color: #4a90e2;
}

.afiliado-container .table th {
    background-color: #f8f9fa;
    color: #2c3e50;
    font-weight: 600;
    border-top: none;
}

.afiliado-container .badge {
    font-weight: 500;
    padding: 6px 10px;
    border-radius: 4px;
}

/* Estilo adicional para mostrar el cálculo en tiempo real */
.costo-total-container {
    background-color: #e8f5e9;
    border-radius: 8px;
    padding: 15px;
    margin-top: 10px;
    border-left: 4px solid #4caf50;
}

.costo-total-label {
    font-weight: 600;
    color: #2e7d32;
}

.costo-total-value {
    font-size: 24px;
    font-weight: 700;
    color: #1b5e20;
}

/* Estilo para información del plan */
.plan-info-container {
    background-color: #e3f2fd;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
    border-left: 4px solid #2196f3;
}

.plan-info-title {
    font-weight: 600;
    color: #0d47a1;
    margin-bottom: 10px;
}

.plan-info-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    padding-bottom: 8px;
    border-bottom: 1px dashed #90caf9;
}

.plan-info-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.plan-info-label {
    font-weight: 500;
    color: #1565c0;
}

.plan-info-value {
    font-weight: 600;
    color: #0d47a1;
}

.plan-info-total {
    background-color: #bbdefb;
    padding: 10px;
    border-radius: 6px;
    margin-top: 10px;
}

/* Estilos para mensajes de restricciones */
.hint-block {
    color: #6c757d;
    font-size: 0.875rem;
    margin-top: 5px;
}

.hint-block i {
    margin-right: 5px;
}

/* Estilos de la tabla de baremos */
#baremos-tabla-container {
    margin-top: 20px;
    margin-bottom: 20px;
}

#baremos-tabla-container table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 5px; /* Espacio entre filas */
}

#baremos-tabla-container th, #baremos-tabla-container td {
    padding: 10px;
    text-align: left;
}

#baremos-tabla-container th {
    background-color: #f1f1f1;
    font-weight: 600;
}

#baremos-tabla-container tr:nth-child(even) {
    background-color: #f9f9f9;
}

#baremos-tabla-container tr {
    border-bottom: 1px solid #eee;
    border-radius: 6px;
}

#baremos-tabla-container .cost-col {
    font-weight: 700;
    text-align: right;
    width: 120px;
}

@media (max-width: 768px) {
    .sis-siniestro-form {
        padding: 15px;
    }
    
    .ms-panel-body {
        padding: 15px;
    }
    
    .btn {
        width: 100%;
        margin-bottom: 10px;
    }
    
    .text-end {
        text-align: left !important;
    }
    
    .afiliado-container {
        max-height: none;
        overflow-y: visible;
    }
}

.field-with-icon {
    position: relative;
}

.field-with-icon .form-control {
    padding-left: 40px;
}

.field-with-icon i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    z-index: 5;
}
CSS;

$this->registerCss($css);
?>

<div class="sis-siniestro-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="ms-panel">
        <div class="ms-panel-header">
            <h3 class="section-title">
                <i class="fas fa-file-alt text-blue-600"></i> Datos del Siniestro
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
                    <span class="plan-info-label">Total de Siniestros Registrados:</span>
                    <span class="plan-info-value"><?= number_format($sumatoriaSiniestros ?? 0, 2) ?></span>
                </div>
                <div class="plan-info-item plan-info-total">
                    <span class="plan-info-label">Total Disponible:</span>
                    <span class="plan-info-value"><?= number_format($totalDisponible, 2) ?></span>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <?php
                    // Consulta para listar los baremos de ese plan y clínica
                    $planesItemsCobertura = \app\models\PlanesItemsCobertura::find()
                        ->joinWith('baremo')
                        ->joinWith('plan')
                        ->joinWith('baremo.area')
                        ->where(['planes.clinica_id' => $afiliado->clinica_id])
                        ->andWhere(['baremo.estatus' => 'Activo'])
                        ->andWhere(['planes.id' => $afiliado->plan_id])
                        ->all();
                    
                    // Crear array de baremos con información adicional
                    $baremosDisponibles = [];
                    $baremosInfo = [];
                    
                    // --- OBTENER CONTRATO ACTIVO DEL AFILIADO (Necesario para ambas validaciones) ---
                    $contrato = \app\models\Contratos::find()
                        ->where(['user_id' => $afiliado->id])
                        ->andWhere(['estatus' => 'Activo'])
                        ->orderBy(['created_at' => SORT_DESC])
                        ->one();

                    $fechaActual = new \DateTime();

                    foreach ($planesItemsCobertura as $item) {
                        if ($item->baremo) {
                            $restricciones = [];
                            $costoBaremo = $item->baremo->costo ?? 0; 
                            $vecesUsado = 0; // Inicializar uso para este baremo

                            if ($contrato) {
                                $fechaContratoIni = new \DateTime($contrato->fecha_ini);

                                // --- 1. LÓGICA DE PLAZO DE ESPERA ---
                                if (!empty($item->plazo_espera) && $item->plazo_espera > 0) {
                                    $fechaPlazoFin = clone $fechaContratoIni;
                                    // Sumar los meses del plazo de espera
                                    $fechaPlazoFin->modify("+{$item->plazo_espera} months"); 
                                    
                                    // Si la fecha actual es ANTES de que termine el plazo, se EXCLUYE (continue).
                                    if ($fechaActual < $fechaPlazoFin) {
                                        $diasRestantes = $fechaActual->diff($fechaPlazoFin)->days;
                                        $restricciones[] = "Plazo pendiente: {$item->plazo_espera} meses (Faltan {$diasRestantes} días)";
                                        continue; 
                                    } else {
                                        // Si el plazo ya se cumplió, solo se informa.
                                        $restricciones[] = "Plazo cumplido: {$item->plazo_espera} meses";
                                    }
                                }
                                
                                // --- 2. LÓGICA DE LÍMITE DE USO (Anual) ---
                                if ($item->cantidad_limite !== null && $item->cantidad_limite > 0) {
                                    // Calcular año de vigencia (período anual desde el inicio del contrato)
                                    $anioVigencia = $fechaContratoIni->diff($fechaActual)->y;
                                    
                                    // Definir el inicio del período anual actual
                                    $inicioAnio = clone $fechaContratoIni;
                                    $inicioAnio->modify("+{$anioVigencia} years");
                                    
                                    // Definir el fin del período anual actual
                                    $finAnio = clone $inicioAnio;
                                    $finAnio->modify("+1 year -1 day");
                                    
                                    // Contar cuántas veces se ha usado en el año actual
                                    $vecesUsado = \app\models\SisSiniestroBaremo::find()
                                        ->joinWith('siniestro')
                                        ->where(['sis_siniestro_baremo.baremo_id' => $item->baremo_id])
                                        ->andWhere(['sis_siniestro.iduser' => $afiliado->id])
                                        ->andWhere(['IS', 'sis_siniestro.deleted_at', null])
                                        ->andWhere(['>=', 'sis_siniestro.fecha', $inicioAnio->format('Y-m-d')])
                                        ->andWhere(['<=', 'sis_siniestro.fecha', $finAnio->format('Y-m-d')])
                                        ->count();
                                        
                                    // Si el límite ya se alcanzó o se superó, EXCLUIR este baremo.
                                    if ($vecesUsado >= $item->cantidad_limite) {
                                        $restricciones[] = "Límite anual alcanzado: {$vecesUsado}/{$item->cantidad_limite} usos";
                                        continue; 
                                    }
                                    
                                    $restricciones[] = "Límite anual: {$vecesUsado}/{$item->cantidad_limite} usos";
                                }
                            } else {
                                // Si no hay contrato, se muestra advertencia pero se permite el uso por defecto.
                                $restricciones[] = "Advertencia: Contrato activo no encontrado. No se validaron Plazo/Límites.";
                            }
                            
                            // Si el código llega aquí, el baremo está DISPONIBLE
                            $area = $item->baremo->area ? $item->baremo->area->nombre : 'Sin área';
                            $descripcion = $item->baremo->descripcion ? "  {$item->baremo->descripcion}" : '';
                            $nombreCompleto = "ÁREA: {$area} - SERVICIO: {$item->baremo->nombre_servicio} - DESCRIPCIÓN: {$descripcion}";
                            
                            if (!empty($restricciones)) {
                                $nombreCompleto .= " [" . implode(", ", $restricciones) . "]";
                            }
                            
                            $baremosDisponibles[$item->baremo_id] = $nombreCompleto;
                            $baremosInfo[$item->baremo_id] = [
                                'nombre' => $item->baremo->nombre_servicio,
                                'area' => $area,
                                'plazo_espera' => $item->plazo_espera,
                                'cantidad_limite' => $item->cantidad_limite,
                                'veces_usado' => $vecesUsado,
                                'costo' => $costoBaremo, // El costo para ser usado en JS
                            ];
                        }
                    }

                    // Obtener baremos seleccionados (código sin cambios)
                    $selectedBaremos = [];
                    if (method_exists($model, 'getBaremos')) {
                        $baremosRelacion = $model->getBaremos()->all();
                        
                        if (empty($baremosRelacion) && !$model->isNewRecord) {
                            $baremosDirectos = (new \yii\db\Query())
                                ->select(['baremo_id'])
                                ->from('sis_siniestro_baremo')
                                ->where(['siniestro_id' => $model->id])
                                ->column();
                            
                            if (!empty($baremosDirectos)) {
                                $selectedBaremos = $baremosDirectos;
                            }
                        } else {
                            $selectedBaremos = \yii\helpers\ArrayHelper::getColumn($baremosRelacion, 'id');
                        }
                    }
                    ?>
                    
                    <div class="field-with-icon">
                        <?= $form->field($model, 'idbaremo[]')->widget(Select2::class, [
                            'data' => $baremosDisponibles,
                            'options' => [
                                'multiple' => true,
                                'value' => $selectedBaremos,
                                'placeholder' => 'Seleccione uno o más Baremos',
                                'class' => 'form-control form-control-lg',
                                'id' => 'baremos-select' // Agregamos un ID para facilitar la selección con JS
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'closeOnSelect' => true,
                                'tags' => false,
                                'tokenSeparators' => [',', ' '],
                            ],
                        ])->label('Baremos')->hint('Los baremos muestran sus restricciones: <strong>Plazo de espera</strong> y <strong>Límite de uso</strong>. El sistema validará automáticamente si puede usar cada baremo.') ?>
                    </div>

                    <div id="baremos-tabla-container" style="display: none;">
                        <h4 class="section-title mb-3">
                            <i class="fas fa-list-alt text-blue-600"></i> Resumen de Servicios
                        </h4>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <th>Área</th>
                                    <th class="text-center">Restricciones</th>
                                    <th class="cost-col">Costo</th>
                                </tr>
                            </thead>
                            <tbody id="baremos-tabla-body">
                                </tbody>
                        </table>
                    </div>
                    
                    <div class="costo-total-container" id="costo-total-container" style="display: none;">
                        <div class="costo-total-label">Total calculado:</div>
                        <div class="costo-total-value" id="costo-total-value">$0.00</div>
                        <!-- Elemento para mostrar advertencia de límite de cobertura -->
                        <div id="cobertura-warning" class="mt-2 p-2 rounded-3 text-danger" style="display: none; background-color: #ffe0b2; border: 1px solid #ff9800;"></div>
                    </div>
                </div>
                
                <div class="col-md-12">
                    <?= $form->field($model, 'costo_total')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => '0.00',
                        'autocomplete' => 'off',
                        'id' => 'costo-total-input',
                        'readonly' => true,
                    ])->label('Total') ?>
                </div>
                
                <div class="col-md-6">
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
                            ])->label('Fecha del Siniestro') ?>
                        </div>
                        
                        <div class="col-md-6 field-with-icon">
                            <?= $form->field($model, 'hora')->textInput([
                                'type' => 'time', 
                                'class' => 'form-control form-control-lg'
                            ])->label('Hora del Siniestro') ?>
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
                            ])->label('Fecha de Atención') ?>
                        </div>
                        
                        <div class="col-md-6 field-with-icon">
                            <?= $form->field($model, 'hora_atencion')->textInput([
                                'type' => 'time', 
                                'class' => 'form-control form-control-lg'
                            ])->label('Hora de Atención') ?>
                        </div>
                        
                        <div class="col-md-12 field-with-icon">
                            <i class="fas fa-align-left"></i>
                            <?= $form->field($model, 'descripcion')->textarea([
                                'rows' => 3, 
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Describa los detalles del siniestro...'
                            ])->label('Descripción del Siniestro') ?>
                        </div>

                         <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="section-title">
                                        <i class="fas fa-camera"></i> Archivos de la Atencion
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
                                                    'maxFileSize' => 5120, // 5MB
                                                    'dropZoneEnabled' => false,
                                                    'showClose' => false,
                                                    'browseLabel' => 'Subir Recibo/Factura',
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
                                            ])->label('Recipe');
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
                                                    'maxFileSize' => 5120, // 5MB
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
                            <div class="afiliado-container">
                                <?= $this->render('/user-datos/view', ['model' => $afiliado]) ?>
                            </div>
                        </div>
                    </div>
                </div>
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
// Información de los baremos, incluyendo el costo
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
        $('#costo-total-input').val('0.00');
        // También ocultar la advertencia
        $('#cobertura-warning').hide().empty(); 
        return;
    }

    // 2. Procesar y construir la tabla
    baremosSeleccionados.forEach(function(baremoId) {
        var item = baremosInfo[baremoId];
        
        if (item && item.costo) {
            var costo = parseFloat(item.costo);
            total += costo;
            
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
            tablaHtml += '<td class="cost-col">$' + costo.toFixed(2) + '</td>';
            tablaHtml += '</tr>';
        }
    });

    // 3. Renderizar la tabla y el total
    $('#baremos-tabla-body').html(tablaHtml);
    $('#baremos-tabla-container').show();
    
    $('#costo-total-value').html('$' + total.toFixed(2));
    $('#costo-total-input').val(total.toFixed(2));
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
