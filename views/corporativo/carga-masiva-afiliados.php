<?php
// Mostrar errores de validación de carga masiva si existen
if (Yii::$app->session->hasFlash('error')) {
    echo '<div class="mt-4 mb-4">';
    echo Yii::$app->session->getFlash('error');
    echo '</div>';
}

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $model app\models\MasivoAfiliadosForm */
/* @var $corporativos array Lista de corporativos activos */

if (!isset($corporativos) || !is_array($corporativos)) {
    $corporativos = [];
}

$this->title = 'Carga Masiva de Afiliados Corporativos';
$this->params['breadcrumbs'][] = $this->title;
?>

<!-- ============================================ -->
<!-- CONTENEDOR PRINCIPAL                          -->
<!-- ============================================ -->
<div class="container-fluid px-4">

    <!-- TÍTULO PRINCIPAL -->
    <div class="d-flex flex-column mb-4 pb-3 border-bottom">
        <h1 class="display-4 font-weight-bold text-primary mb-3">
            <i class="fas fa-users mr-3"></i><?= Html::encode($this->title) ?>
        </h1>
        <p class="lead text-muted">
            <i class="fas fa-rocket mr-2"></i>
            Registre múltiples afiliados corporativos de forma rápida y eficiente mediante un archivo CSV.
        </p>
    </div>

    <!-- ============================================ -->
    <!-- 1. GUÍA RÁPIDA: FLUJO DE TRABAJO (FLOWCHART) -->
    <!-- ============================================ -->
    <div class="card shadow-lg mb-5 border-0 overflow-hidden">
        <div class="card-header bg-gradient-primary text-white py-3">
            <h4 class="mb-0 font-weight-bold">
                <i class="fas fa-map-signs mr-2"></i>Guía Rápida: Flujo de Trabajo
                <small class="text-white-50 ml-2">(Haga clic en cualquier paso para ir directamente)</small>
            </h4>
        </div>
        <div class="card-body p-4">
            <div class="d-none d-md-block">
                <div class="row no-gutters mb-4">
                    <div class="col-md-2">
                        <div class="flow-step step-1 text-center p-3 flow-step-link" data-step="1" style="cursor: pointer;">
                            <div class="step-header">
                                <div class="step-number mx-auto">1</div>
                                <div class="step-icon-corner"><i class="fas fa-download"></i></div>
                            </div>
                            <h6 class="font-weight-bold mb-1 mt-3">Descargar Plantillas</h6>
                            <p class="small text-muted mb-0">Obtenga los recursos necesarios</p>
                        </div>
                    </div>
                    <div class="col-md-1 d-flex align-items-center justify-content-center">
                        <i class="fas fa-arrow-right fa-2x text-primary"></i>
                    </div>
                    <div class="col-md-2">
                        <div class="flow-step step-2 text-center p-3 flow-step-link" data-step="2" style="cursor: pointer;">
                            <div class="step-header">
                                <div class="step-number mx-auto">2</div>
                                <div class="step-icon-corner"><i class="fas fa-edit"></i></div>
                            </div>
                            <h6 class="font-weight-bold mb-1 mt-3">Preparar CSV</h6>
                            <p class="small text-muted mb-0">Complete con datos de afiliados</p>
                        </div>
                    </div>
                    <div class="col-md-1 d-flex align-items-center justify-content-center">
                        <i class="fas fa-arrow-right fa-2x text-primary"></i>
                    </div>
                    <div class="col-md-2">
                        <div class="flow-step step-3 text-center p-3 flow-step-link" data-step="3" style="cursor: pointer;">
                            <div class="step-header">
                                <div class="step-number mx-auto">3</div>
                                <div class="step-icon-corner"><i class="fas fa-search"></i></div>
                            </div>
                            <h6 class="font-weight-bold mb-1 mt-3">Validar IDs</h6>
                            <p class="small text-muted mb-0">Seleccione corporativo y consulte</p>
                        </div>
                    </div>
                    <div class="col-md-1 d-flex align-items-center justify-content-center">
                        <i class="fas fa-arrow-right fa-2x text-primary"></i>
                    </div>
                    <div class="col-md-2">
                        <div class="flow-step step-4 text-center p-3 flow-step-link" data-step="4" style="cursor: pointer;">
                            <div class="step-header">
                                <div class="step-number mx-auto">4</div>
                                <div class="step-icon-corner"><i class="fas fa-calendar-alt"></i></div>
                            </div>
                            <h6 class="font-weight-bold mb-1 mt-3">Completar Formulario</h6>
                            <p class="small text-muted mb-0">Ingrese fechas y archivo</p>
                        </div>
                    </div>
                </div>
                <div class="row no-gutters mt-2 justify-content-center">
                    <div class="col-md-2">
                        <div class="flow-step step-5 text-center p-3 flow-step-link" data-step="5" style="cursor: pointer;">
                            <div class="step-header">
                                <div class="step-number mx-auto">5</div>
                                <div class="step-icon-corner"><i class="fas fa-play"></i></div>
                            </div>
                            <h6 class="font-weight-bold mb-1 mt-3">Procesar Carga</h6>
                            <p class="small text-muted mb-0">Ejecute la carga masiva</p>
                        </div>
                    </div>
                    <div class="col-md-1 d-flex align-items-center justify-content-center">
                        <i class="fas fa-arrow-right fa-2x text-success"></i>
                    </div>
                    <div class="col-md-3">
                        <div class="result-card-small text-center p-3 rounded">
                            <i class="fas fa-chart-line fa-2x text-white mb-1"></i>
                            <h6 class="font-weight-bold text-white mb-0">¡Proceso Completado!</h6>
                            <small class="text-white-50">Resumen detallado</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-block d-md-none">
                <!-- Mobile flowchart - simplified -->
                <div class="text-center">
                    <div class="flow-step step-1 text-center p-2 mb-2">
                        <span class="step-number mx-auto d-inline-block">1</span>
                        <h6 class="mt-1 mb-0">Descargar Plantillas</h6>
                    </div>
                    <i class="fas fa-arrow-down text-primary my-1"></i>
                    <div class="flow-step step-2 text-center p-2 mb-2">
                        <span class="step-number mx-auto d-inline-block">2</span>
                        <h6 class="mt-1 mb-0">Preparar CSV</h6>
                    </div>
                    <i class="fas fa-arrow-down text-primary my-1"></i>
                    <div class="flow-step step-3 text-center p-2 mb-2">
                        <span class="step-number mx-auto d-inline-block">3</span>
                        <h6 class="mt-1 mb-0">Validar IDs</h6>
                    </div>
                    <i class="fas fa-arrow-down text-primary my-1"></i>
                    <div class="flow-step step-4 text-center p-2 mb-2">
                        <span class="step-number mx-auto d-inline-block">4</span>
                        <h6 class="mt-1 mb-0">Completar Formulario</h6>
                    </div>
                    <i class="fas fa-arrow-down text-primary my-1"></i>
                    <div class="flow-step step-5 text-center p-2 mb-2">
                        <span class="step-number mx-auto d-inline-block">5</span>
                        <h6 class="mt-1 mb-0">Procesar Carga</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- PASO 1: DESCARGAR PLANTILLAS                  -->
    <!-- ============================================ -->
    <div id="step1-download" class="card shadow-sm mb-5 border-0">
        <div class="card-header bg-gradient-step1 text-white py-3">
            <h5 class="mb-0 font-weight-bold">
                <span class="step-badge mr-2">1</span>
                <i class="fas fa-download mr-2"></i>Descargar Plantillas
            </h5>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">Comience descargando los recursos necesarios para preparar su carga:</p>
            <div class="d-flex flex-wrap">
                <?= Html::a(
                    '<i class="fas fa-file-csv mr-2"></i> Plantilla CSV de Ejemplo',
                    ['/corporativo/descargar-plantilla'],
                    ['class' => 'btn btn-success mr-3 mb-2 px-4 py-2 shadow-sm']
                ) ?>
                <?= Html::a(
                    '<i class="fas fa-map-marker-alt mr-2"></i> Catálogo de Estados',
                    ['/corporativo/descargar-catalogo-estados'],
                    ['class' => 'btn btn-outline-primary mr-3 mb-2 px-4 py-2 shadow-sm']
                ) ?>
                <?= Html::a(
                    '<i class="fas fa-user-tie mr-2"></i> Catálogo de Asesores',
                    ['/corporativo/descargar-catalogo-asesores'],
                    ['class' => 'btn btn-outline-secondary mb-2 px-4 py-2 shadow-sm']
                ) ?>
            </div>
            <div class="alert alert-info mt-3 mb-0">
                <i class="fas fa-lightbulb mr-2"></i>
                <strong>Consejo:</strong> Descargue la plantilla y los catálogos antes de comenzar.
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- PASO 2: PREPARAR ARCHIVO CSV                  -->
    <!-- ============================================ -->
    <div id="step2-prepare" class="card shadow-sm mb-5 border-0">
        <div class="card-header bg-gradient-step2 text-white py-3">
            <h5 class="mb-0 font-weight-bold">
                <span class="step-badge mr-2">2</span>
                <i class="fas fa-edit mr-2"></i>Preparar Archivo CSV
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-primary bg-primary text-white">
                        <tr>
                            <th>Campo</th>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            <th>Ejemplo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="table-secondary">
                            <td colspan="4" class="font-weight-bold">📌 CAMPOS REQUERIDOS</td>
                        </tr>
                        <tr>
                            <td><code>tipo_cedula</code></td>
                            <td>Texto</td>
                            <td>Tipo de documento</td>
                            <td>V, E, P, J</td>
                        </tr>
                        <tr>
                            <td><code>cedula</code></td>
                            <td>Numérico</td>
                            <td>Solo números</td>
                            <td>19088456</td>
                        </tr>
                        <tr>
                            <td><code>nombres</code></td>
                            <td>Texto</td>
                            <td>Nombres completos</td>
                            <td>JUAN PABLO</td>
                        </tr>
                        <tr>
                            <td><code>apellidos</code></td>
                            <td>Texto</td>
                            <td>Apellidos completos</td>
                            <td>ROJAS PEREZ</td>
                        </tr>
                        <tr>
                            <td><code>fechanac</code></td>
                            <td>Fecha</td>
                            <td>Formato: MM/DD/AAAA</td>
                            <td>05/15/1990</td>
                        </tr>
                        <tr>
                            <td><code>sexo</code></td>
                            <td>Texto</td>
                            <td>Género</td>
                            <td>Masculino / Femenino</td>
                        </tr>
                        <tr>
                            <td><code>telefono</code></td>
                            <td>Texto</td>
                            <td>11 dígitos</td>
                            <td>04121234567</td>
                        </tr>
                        <tr>
                            <td><code>email</code></td>
                            <td>Email</td>
                            <td>Único en el sistema</td>
                            <td>juan@ejemplo.com</td>
                        </tr>
                        <tr>
                            <td><code>direccion</code></td>
                            <td>Texto</td>
                            <td>Dirección</td>
                            <td>Calle Principal #123</td>
                        </tr>
                        <tr class="table-danger">
                            <td><code>plan_id</code></td>
                            <td>Número</td>
                            <td>ID del Plan (ver Paso 3)</td>
                            <td class="text-danger">2</td>
                        </tr>
                        <tr class="table-danger">
                            <td><code>clinica_id</code></td>
                            <td>Número</td>
                            <td>ID de la Clínica (ver Paso 3)</td>
                            <td class="text-danger">2</td>
                        </tr>
                        <tr>
                            <td><code>estado</code></td>
                            <td>Texto</td>
                            <td>Estado (ver catálogo)</td>
                            <td>MIRANDA</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- PASO 3: VALIDAR IDS (Guía Dinámica)          -->
    <!-- ============================================ -->
    <div id="step3-validate" class="card shadow-lg border-primary mb-5">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0 font-weight-bold">
                <span class="step-badge bg-white text-primary mr-2">3</span>
                <i class="fas fa-search mr-2"></i>Validar IDs - Guía de Clínicas y Planes
            </h5>
        </div>
        <div class="card-body">
            <div class="form-group mb-4 p-3 bg-light rounded">
                <label class="font-weight-bold text-primary mb-2">
                    <i class="fas fa-building mr-2"></i>
                    Seleccione el Corporativo <span class="text-danger">*</span>
                </label>
                <?= Html::dropDownList(
                    'corporativo_selector',
                    null,
                    $corporativos,
                    [
                        'id' => 'corporativo-selector',
                        'prompt' => '--- Seleccione el Corporativo Destino ---',
                        'class' => 'form-control form-control-lg',
                        'style' => 'max-width: 500px;'
                    ]
                ) ?>
                <div class="alert alert-warning mt-3 mb-0 small">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    <strong>Importante:</strong> Debe seleccionar un corporativo para ver los IDs de clínicas y planes que debe usar en su CSV.
                </div>
            </div>

            <!-- Resultados dinámicos de clínicas y planes -->
            <div id="clinicas-asociadas-info">
                <div class="alert alert-info mb-0 text-center py-4">
                    <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                    <strong>Seleccione un corporativo arriba</strong><br>
                    para ver las clínicas asociadas y los IDs de planes disponibles.
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- PASO 4 Y 5: FORMULARIO                       -->
    <!-- ============================================ -->
    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data', 'id' => 'carga-masiva-form'],
    ]); ?>

    <div id="step4-form" class="card shadow-lg border-warning mb-5">
        <div class="card-header bg-gradient-step4 text-white py-3">
            <h5 class="mb-0 font-weight-bold">
                <span class="step-badge mr-2">4</span>
                <i class="fas fa-calendar-alt mr-2"></i>Completar Formulario de Carga
            </h5>
        </div>
        <div class="card-body p-4">
            <!-- Hidden field for corporativo_id -->
            <?= $form->field($model, 'corporativo_id')->hiddenInput(['id' => 'masivoafiliadosform-corporativo_id'])->label(false) ?>

            <div class="form-group mb-4">
                <label class="font-weight-bold text-success">
                    <i class="fas fa-calendar-alt mr-2"></i>Fechas del Contrato
                </label>
                <div class="row">
                    <div class="col-md-5">
                        <?= $form->field($model, 'fecha_ini')->textInput([
                            'class' => 'form-control form-control-lg fecha-ini-field',
                            'type' => 'date',
                            'required' => true,
                        ])->label('Fecha de Inicio') ?>
                    </div>
                    <div class="col-md-5 fecha-ven-container" style="display: none;">
                        <?= $form->field($model, 'fecha_ven')->textInput([
                            'class' => 'form-control form-control-lg fecha-ven-field',
                            'type' => 'date',
                            'readonly' => true,
                        ])->label('Fecha de Vencimiento') ?>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="alert alert-info p-2 mb-3 w-100 text-center">
                            <small><i class="fas fa-sync-alt mr-1"></i> +1 año</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group mb-4">
                <label class="font-weight-bold text-warning">
                    <i class="fas fa-file-csv mr-2"></i>Archivo CSV <span class="text-danger">*</span>
                </label>
                <?= $form->field($model, 'masivoFile')->fileInput([
                    'class' => 'form-control form-control-lg',
                    'accept' => '.csv',
                    'required' => true
                ])->label(false) ?>
                <small class="text-muted">Formatos permitidos: CSV | Tamaño máximo: 5MB</small>
            </div>
        </div>
    </div>

    <div id="step5-process" class="card shadow-lg border-success mb-5">
        <div class="card-header bg-gradient-step5 text-white py-3">
            <h5 class="mb-0 font-weight-bold">
                <span class="step-badge mr-2">5</span>
                <i class="fas fa-play mr-2"></i>Procesar Carga Masiva
            </h5>
        </div>
        <div class="card-body p-4 text-center">
            <?= Html::submitButton(
                '<i class="fas fa-paper-plane mr-2"></i> Procesar Carga Masiva',
                [
                    'class' => 'btn btn-success btn-lg px-5 py-3 shadow',
                    'style' => 'font-size: 1.2rem;',
                    'id' => 'submit-button'
                ]
            ) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<!-- ============================================ -->
<!-- ESTILOS CSS                                  -->
<!-- ============================================ -->
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    }

    .bg-gradient-step1 {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    }

    .bg-gradient-step2 {
        background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
    }

    .bg-gradient-step4 {
        background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
    }

    .bg-gradient-step5 {
        background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);
    }

    .flow-step {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: 1px solid #e3e6f0;
    }

    .flow-step:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
    }

    .step-1 {
        border-top: 3px solid #4e73df;
    }

    .step-2 {
        border-top: 3px solid #1cc88a;
    }

    .step-3 {
        border-top: 3px solid #36b9cc;
    }

    .step-4 {
        border-top: 3px solid #f6c23e;
    }

    .step-5 {
        border-top: 3px solid #e74a3b;
    }

    .step-number {
        width: 32px;
        height: 32px;
        background: white;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1rem;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .step-1 .step-number {
        color: #4e73df;
        border: 2px solid #4e73df;
    }

    .step-2 .step-number {
        color: #1cc88a;
        border: 2px solid #1cc88a;
    }

    .step-3 .step-number {
        color: #36b9cc;
        border: 2px solid #36b9cc;
    }

    .step-4 .step-number {
        color: #f6c23e;
        border: 2px solid #f6c23e;
    }

    .step-5 .step-number {
        color: #e74a3b;
        border: 2px solid #e74a3b;
    }

    .step-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        background: white;
        color: #4e73df;
        border-radius: 50%;
        font-weight: bold;
        margin-right: 8px;
    }

    .result-card-small {
        background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        box-shadow: 0 5px 15px rgba(28, 200, 138, 0.3);
    }

    .thead-primary th {
        background-color: #4e73df !important;
        color: white !important;
    }

    code {
        background: #f4f4f4;
        padding: 2px 6px;
        border-radius: 4px;
        color: #e83e8c;
    }

    @keyframes highlightPulse {
        0% {
            box-shadow: 0 0 0 0 rgba(78, 115, 223, 0.7);
            border-color: #4e73df;
        }

        70% {
            box-shadow: 0 0 0 15px rgba(78, 115, 223, 0);
            border-color: #4e73df;
        }

        100% {
            box-shadow: 0 0 0 0 rgba(78, 115, 223, 0);
            border-color: transparent;
        }
    }

    .highlight-pulse {
        animation: highlightPulse 0.8s ease-out;
        border: 2px solid #4e73df !important;
        border-radius: 12px;
    }

    @keyframes pulseRed {
        0% {
            box-shadow: 0 0 0 0 rgba(231, 74, 59, 0.7);
            border-color: #e74a3b;
        }

        70% {
            box-shadow: 0 0 0 15px rgba(231, 74, 59, 0);
            border-color: #e74a3b;
        }

        100% {
            box-shadow: 0 0 0 0 rgba(231, 74, 59, 0);
            border-color: transparent;
        }
    }

    .required-missing {
        animation: pulseRed 0.8s ease-out;
        border: 2px solid #e74a3b !important;
        border-radius: 8px;
    }

    @media (max-width: 768px) {
        .display-4 {
            font-size: 1.8rem;
        }
    }
</style>

<!-- ============================================ -->
<!-- JAVASCRIPT                                   -->
<!-- ============================================ -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
    $(document).ready(function() {
        // URL for AJAX
        const clinicasPlanesUrl = "<?= Yii::$app->urlManager->createUrl(['corporativo/obtener-clinicas-con-planes-por-corporativo']) ?>";

        // Function to load clinics and plans
        function cargarClinicasPlanes(corporativoId) {
            const infoDiv = $('#clinicas-asociadas-info');

            if (!corporativoId) {
                infoDiv.html('<div class="alert alert-info mb-0 text-center py-4"><i class="fas fa-info-circle fa-2x mb-2 d-block"></i><strong>Seleccione un corporativo arriba</strong><br>para ver las clínicas asociadas y los IDs de planes disponibles.</div>');
                return;
            }

            infoDiv.html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><br><span class="text-muted">Cargando clínicas y planes asociados...</span></div>');

            $.ajax({
                url: clinicasPlanesUrl,
                type: 'GET',
                data: {
                    id: corporativoId
                },
                dataType: 'json',
                success: function(response) {
                    console.log('AJAX Response:', response);

                    if (response.success && response.data && response.data.length > 0) {
                        let html = '<div class="alert alert-success mb-3"><i class="fas fa-check-circle mr-2"></i><strong>' + response.data.length + ' clínicas encontradas</strong> para este corporativo. Los IDs marcados en <span class="text-danger font-weight-bold">ROJO</span> son los que debe usar en su CSV.</div>';
                        html += '<div class="row">';

                        $.each(response.data, function(index, clinica) {
                            html += '<div class="col-md-6 mb-4">';
                            html += '<div class="card border-info h-100 shadow-sm">';
                            html += '<div class="card-header bg-info text-white font-weight-bold">';
                            html += '<i class="fas fa-hospital-alt mr-2"></i> ' + clinica.nombre;
                            html += '<span class="badge badge-light float-right">ID: ' + clinica.id + '</span>';
                            html += '</div>';
                            html += '<div class="card-body">';

                            if (clinica.planes && clinica.planes.length > 0) {
                                html += '<h6 class="text-dark mb-3"><i class="fas fa-file-contract mr-2 text-primary"></i> Planes disponibles (' + clinica.planes.length + '):</h6>';
                                html += '<div class="table-responsive">';
                                html += '<table class="table table-bordered table-sm">';
                                html += '<thead class="bg-light"><tr><th>ID del Plan</th><th>Nombre del Plan</th></tr></thead><tbody>';

                                $.each(clinica.planes, function(idx, plan) {
                                    html += '<tr>';
                                    html += '<td class="text-center"><span class="badge badge-danger badge-pill px-3 py-2">' + plan.id + '</span></td>';
                                    html += '<td><i class="fas fa-file-signature mr-1"></i> ' + plan.nombre + '</td>';
                                    html += '</tr>';
                                });

                                html += '</tbody></table></div>';
                                html += '<div class="alert alert-secondary mt-3 mb-0 small">';
                                html += '<i class="fas fa-code mr-1"></i> <strong>Ejemplo para su CSV:</strong> use <code class="text-danger">plan_id: ' + clinica.planes[0].id + '</code> y <code class="text-danger">clinica_id: ' + clinica.id + '</code>';
                                html += '</div>';
                            } else {
                                html += '<div class="alert alert-warning mb-0">No hay planes activos asociados a esta clínica.</div>';
                            }

                            html += '</div></div></div>';
                        });

                        html += '</div>';
                        infoDiv.html(html);
                    } else if (response.success && (!response.data || response.data.length === 0)) {
                        infoDiv.html('<div class="alert alert-warning mb-0 text-center py-4"><i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i><strong>No se encontraron clínicas asociadas</strong><br>Verifique que el corporativo tenga clínicas asignadas.</div>');
                    } else {
                        infoDiv.html('<div class="alert alert-danger mb-0"><i class="fas fa-bug mr-2"></i>Error: ' + (response.error || 'No se pudieron cargar los datos') + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.error('Response Text:', xhr.responseText);
                    infoDiv.html('<div class="alert alert-danger mb-0"><i class="fas fa-plug mr-2"></i>Error de conexión. Verifique que el servidor esté funcionando correctamente.<br><small>Detalles: ' + error + '</small></div>');
                }
            });
        }

        // When corporate selector changes
        $('#corporativo-selector').on('change', function() {
            var selectedValue = $(this).val();
            // Update hidden field
            $('#masivoafiliadosform-corporativo_id').val(selectedValue);
            // Load clinics and plans
            cargarClinicasPlanes(selectedValue);
        });

        // Initial load if there's a preselected value
        if ($('#corporativo-selector').val()) {
            $('#masivoafiliadosform-corporativo_id').val($('#corporativo-selector').val());
            cargarClinicasPlanes($('#corporativo-selector').val());
        }

        // Smooth scroll function
        function smoothScrollTo(target, duration) {
            var targetElement = $(target);
            if (targetElement.length) {
                $('html, body').animate({
                    scrollTop: targetElement.offset().top - 80
                }, duration);
            }
        }

        // Flowchart click handlers
        $('.flow-step-link').on('click', function(e) {
            e.preventDefault();
            var step = $(this).data('step');
            if (step == 1) smoothScrollTo('#step1-download', 500);
            else if (step == 2) smoothScrollTo('#step2-prepare', 500);
            else if (step == 3) smoothScrollTo('#step3-validate', 500);
            else if (step == 4) smoothScrollTo('#step4-form', 500);
            else if (step == 5) smoothScrollTo('#step5-process', 500);

            $(this).closest('.flow-step').addClass('highlight-pulse');
            setTimeout(function() {
                $('.flow-step').removeClass('highlight-pulse');
            }, 1000);
        });

        // Form validation before submit
        $('#carga-masiva-form').on('submit', function(e) {
            var corporativoId = $('#masivoafiliadosform-corporativo_id').val();
            var fechaIni = $('.fecha-ini-field').val();
            var fileInput = $('input[type="file"]').val();

            if (!corporativoId || corporativoId === '') {
                e.preventDefault();
                alert('ERROR: Debe seleccionar un corporativo en el Paso 3 antes de procesar la carga.');
                smoothScrollTo('#step3-validate', 500);
                $('#step3-validate').addClass('required-missing');
                setTimeout(function() {
                    $('#step3-validate').removeClass('required-missing');
                }, 2000);
                return false;
            }

            if (!fechaIni) {
                e.preventDefault();
                alert('ERROR: Debe seleccionar una fecha de inicio.');
                smoothScrollTo('#step4-form', 500);
                return false;
            }

            if (!fileInput) {
                e.preventDefault();
                alert('ERROR: Debe seleccionar un archivo CSV para cargar.');
                smoothScrollTo('#step4-form', 500);
                return false;
            }

            return true;
        });

        // Date calculation for fecha_ven
        function calcularFechaVencimiento(fechaIni) {
            if (fechaIni) {
                var parts = fechaIni.split('-');
                var fecha = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
                fecha.setFullYear(fecha.getFullYear() + 1);
                var year = fecha.getFullYear();
                var month = String(fecha.getMonth() + 1).padStart(2, '0');
                var day = String(fecha.getDate()).padStart(2, '0');
                return year + '-' + month + '-' + day;
            }
            return '';
        }

        function toggleFechaVen() {
            var fechaIni = $('.fecha-ini-field').val();
            if (fechaIni) {
                $('.fecha-ven-container').show();
                $('.fecha-ven-field').val(calcularFechaVencimiento(fechaIni));
            } else {
                $('.fecha-ven-container').hide();
                $('.fecha-ven-field').val('');
            }
        }

        toggleFechaVen();
        $('.fecha-ini-field').on('change', toggleFechaVen);
    });
</script>