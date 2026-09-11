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
/* @var $planes array Lista de planes (inicialmente vacío) */
/* @var $asesores array Lista de asesores */

if (!isset($corporativos) || !is_array($corporativos)) {
    $corporativos = [];
}

if (!isset($planes) || !is_array($planes)) {
    $planes = [];
}

if (!isset($asesores) || !is_array($asesores)) {
    $asesores = [];
}

$this->title = 'Carga Masiva de Afiliados Corporativos';
$this->params['breadcrumbs'][] = $this->title;

// Get the base URL for JavaScript
$getPlanesUrl = Yii::$app->urlManager->createUrl(['corporativo/obtener-planes-por-corporativo']);
?>

<!-- ============================================ -->
<!-- PROGRESS OVERLAY (Simple & Effective)        -->
<!-- ============================================ -->
<div id="progress-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 99999; align-items: center; justify-content: center;">
    <div class="card shadow-lg" style="max-width: 500px; width: 90%; border-radius: 16px; background: white; animation: fadeInUp 0.3s ease;">
        <div class="card-body text-center p-4">
            <!-- Spinner -->
            <div class="spinner-border text-primary" style="width: 60px; height: 60px; border-width: 6px;" role="status">
                <span class="sr-only">Cargando...</span>
            </div>

            <h5 class="mt-3 font-weight-bold" id="progress-title">Procesando Carga Masiva</h5>

            <!-- Progress Bar -->
            <div class="progress mt-3" style="height: 20px; border-radius: 10px; overflow: hidden;">
                <div id="upload-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                    role="progressbar" style="width: 0%; border-radius: 10px; transition: width 0.3s ease;">
                    <span id="upload-progress-text" class="font-weight-bold">0%</span>
                </div>
            </div>

            <!-- Status Messages -->
            <div id="progress-status" class="mt-2 text-muted small">Preparando archivo...</div>

            <!-- Cancel Button (hidden, kept for future use) -->
            <button type="button" class="btn btn-outline-danger btn-sm mt-3" id="cancel-upload-btn" style="display: none;">
                <i class="fas fa-times mr-1"></i> Cancelar
            </button>
        </div>
    </div>
</div>

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
    <!-- 1. GUÍA RÁPIDA: FLUJO DE TRABAJO (PROFESSIONAL) -->
    <!-- ============================================ -->
    <div class="card shadow-lg mb-5 border-0 overflow-hidden" style="border-radius: 16px; background: linear-gradient(135deg, #f8f9fc 0%, #e9ecef 100%);">
        <div class="card-header bg-gradient-primary text-white py-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0 font-weight-bold">
                        <i class="fas fa-map-signs mr-3"></i>Guía Rápida: Flujo de Trabajo
                    </h4>
                    <small class="text-white-50 ml-4">
                        <i class="fas fa-mouse-pointer mr-1"></i> Haga clic en cualquier paso para ir directamente
                    </small>
                </div>
                <div>
                    <span class="badge badge-light text-primary px-3 py-2">
                        <i class="fas fa-clock mr-1"></i> Aprox. 5 minutos
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            <!-- Desktop Version -->
            <div class="d-none d-md-block">
                <!-- Step 1-4 Row -->
                <div class="row no-gutters align-items-stretch">
                    <!-- Step 1 -->
                    <div class="col-md-2">
                        <div class="flow-step step-1 text-center p-3 flow-step-link h-100" data-step="1" style="cursor: pointer; display: flex; flex-direction: column; justify-content: center;">
                            <div class="step-header">
                                <div class="step-number mx-auto animated-step">1</div>
                                <div class="step-icon-corner">
                                    <i class="fas fa-download fa-2x text-primary"></i>
                                </div>
                            </div>
                            <h6 class="font-weight-bold mb-1 mt-3 text-primary">Descargar Plantillas</h6>
                            <p class="small text-muted mb-0">Obtenga los recursos necesarios</p>
                            <div class="step-badge-status mt-2">
                                <span class="badge badge-pill badge-primary px-3 py-1">
                                    <i class="fas fa-file-download mr-1"></i> Paso 1
                                </span>
                            </div>
                        </div>
                    </div>
                    <!-- Arrow 1 -->
                    <div class="col-md-1 d-flex align-items-center justify-content-center">
                        <div class="arrow-container">
                            <i class="fas fa-arrow-right fa-2x text-primary arrow-pulse"></i>
                        </div>
                    </div>
                    <!-- Step 2 (Highlighted - Most Important) -->
                    <div class="col-md-2">
                        <div class="flow-step step-2 text-center p-3 flow-step-link h-100" data-step="2" style="cursor: pointer; display: flex; flex-direction: column; justify-content: center; border: 3px solid #1cc88a; background: linear-gradient(135deg, #f0fff4 0%, #d4edda 100%); box-shadow: 0 0 30px rgba(28, 200, 138, 0.2);">
                            <div class="step-header">
                                <div class="step-number mx-auto animated-step">2</div>
                                <div class="step-icon-corner">
                                    <i class="fas fa-edit fa-2x text-success"></i>
                                </div>
                                <div class="pulse-dot">
                                    <span class="badge badge-danger badge-pill px-2 py-1" style="font-size: 0.6rem; animation: pulse-badge 1.5s infinite;">
                                        <i class="fas fa-exclamation-circle mr-1"></i> CRÍTICO
                                    </span>
                                </div>
                            </div>
                            <h6 class="font-weight-bold mb-1 mt-3 text-success">Preparar CSV</h6>
                            <p class="small text-muted mb-0 font-weight-bold">Complete con datos de afiliados</p>
                            <div class="step-badge-status mt-2">
                                <span class="badge badge-pill badge-success px-3 py-1">
                                    <i class="fas fa-star mr-1"></i> Paso Más Importante
                                </span>
                            </div>
                            <div class="mt-1">
                                <small class="text-muted">
                                    <i class="fas fa-check-circle text-success mr-1"></i> 10 campos requeridos
                                </small>
                            </div>
                        </div>
                    </div>
                    <!-- Arrow 2 -->
                    <div class="col-md-1 d-flex align-items-center justify-content-center">
                        <div class="arrow-container">
                            <i class="fas fa-arrow-right fa-2x text-primary arrow-pulse"></i>
                        </div>
                    </div>
                    <!-- Step 3 -->
                    <div class="col-md-2">
                        <div class="flow-step step-3 text-center p-3 flow-step-link h-100" data-step="3" style="cursor: pointer; display: flex; flex-direction: column; justify-content: center;">
                            <div class="step-header">
                                <div class="step-number mx-auto animated-step">3</div>
                                <div class="step-icon-corner">
                                    <i class="fas fa-building fa-2x text-info"></i>
                                </div>
                            </div>
                            <h6 class="font-weight-bold mb-1 mt-3 text-info">Seleccionar</h6>
                            <p class="small text-muted mb-0">Elija Corporativo, Plan y Asesor</p>
                            <div class="step-badge-status mt-2">
                                <span class="badge badge-pill badge-info px-3 py-1">
                                    <i class="fas fa-check mr-1"></i> Paso 3
                                </span>
                            </div>
                        </div>
                    </div>
                    <!-- Arrow 3 -->
                    <div class="col-md-1 d-flex align-items-center justify-content-center">
                        <div class="arrow-container">
                            <i class="fas fa-arrow-right fa-2x text-primary arrow-pulse"></i>
                        </div>
                    </div>
                    <!-- Step 4 -->
                    <div class="col-md-2">
                        <div class="flow-step step-4 text-center p-3 flow-step-link h-100" data-step="4" style="cursor: pointer; display: flex; flex-direction: column; justify-content: center;">
                            <div class="step-header">
                                <div class="step-number mx-auto animated-step">4</div>
                                <div class="step-icon-corner">
                                    <i class="fas fa-calendar-alt fa-2x text-warning"></i>
                                </div>
                            </div>
                            <h6 class="font-weight-bold mb-1 mt-3 text-warning">Completar Formulario</h6>
                            <p class="small text-muted mb-0">Ingrese fechas y archivo</p>
                            <div class="step-badge-status mt-2">
                                <span class="badge badge-pill badge-warning px-3 py-1">
                                    <i class="fas fa-check mr-1"></i> Paso 4
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 5 & Result Row -->
                <div class="row no-gutters mt-4 justify-content-center align-items-stretch">
                    <!-- Arrow 4 -->
                    <div class="col-md-2 d-flex align-items-center justify-content-center">
                        <div class="arrow-container">
                            <i class="fas fa-arrow-down fa-2x text-primary arrow-pulse"></i>
                        </div>
                    </div>
                    <!-- Step 5 -->
                    <div class="col-md-2">
                        <div class="flow-step step-5 text-center p-3 flow-step-link h-100" data-step="5" style="cursor: pointer; display: flex; flex-direction: column; justify-content: center;">
                            <div class="step-header">
                                <div class="step-number mx-auto animated-step">5</div>
                                <div class="step-icon-corner">
                                    <i class="fas fa-play fa-2x text-danger"></i>
                                </div>
                            </div>
                            <h6 class="font-weight-bold mb-1 mt-3 text-danger">Procesar Carga</h6>
                            <p class="small text-muted mb-0">Ejecute la carga masiva</p>
                            <div class="step-badge-status mt-2">
                                <span class="badge badge-pill badge-danger px-3 py-1">
                                    <i class="fas fa-rocket mr-1"></i> Paso 5
                                </span>
                            </div>
                        </div>
                    </div>
                    <!-- Arrow 5 -->
                    <div class="col-md-1 d-flex align-items-center justify-content-center">
                        <div class="arrow-container">
                            <i class="fas fa-arrow-right fa-2x text-success arrow-pulse"></i>
                        </div>
                    </div>
                    <!-- Result Card -->
                    <div class="col-md-3">
                        <div class="result-card-final text-center p-4 rounded h-100" style="display: flex; flex-direction: column; justify-content: center; background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%); box-shadow: 0 10px 40px rgba(28, 200, 138, 0.3); border-radius: 16px; min-height: 180px;">
                            <div class="mb-2">
                                <div class="result-icon-wrapper">
                                    <i class="fas fa-check-circle fa-3x text-white"></i>
                                </div>
                            </div>
                            <h6 class="font-weight-bold text-white mb-0" style="font-size: 1.1rem;">¡Proceso Completado!</h6>
                            <small class="text-white-50">Resumen detallado del proceso</small>
                            <div class="mt-2">
                                <span class="badge badge-light text-success px-3 py-1">
                                    <i class="fas fa-chart-line mr-1"></i> Ver Resultados
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Progress Indicator -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted">
                                <i class="fas fa-flag-checkered mr-1"></i> Inicio
                            </span>
                            <div class="flex-grow-1 mx-3">
                                <div class="progress" style="height: 4px; background: #e9ecef;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 0%;" id="flow-progress"></div>
                                </div>
                            </div>
                            <span class="small text-muted">
                                <i class="fas fa-flag-checkered mr-1"></i> Fin
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-flex justify-content-center flex-wrap gap-3">
                            <span class="badge badge-light px-3 py-2">
                                <i class="fas fa-file-csv text-success mr-1"></i> 10 campos requeridos
                            </span>
                            <span class="badge badge-light px-3 py-2">
                                <i class="fas fa-info-circle text-info mr-1"></i> 14 campos opcionales
                            </span>
                            <span class="badge badge-light px-3 py-2">
                                <i class="fas fa-users text-primary mr-1"></i> Carga ilimitada
                            </span>
                            <span class="badge badge-light px-3 py-2">
                                <i class="fas fa-clock text-warning mr-1"></i> Procesamiento rápido
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Version -->
            <div class="d-block d-md-none">
                <div class="text-center">
                    <!-- Step 1 -->
                    <div class="flow-step step-1 text-center p-3 mb-2" style="cursor: pointer;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="step-number mx-auto d-inline-block">1</span>
                                <span class="font-weight-bold ml-2">Descargar Plantillas</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted"></i>
                        </div>
                    </div>
                    <i class="fas fa-arrow-down text-primary my-1"></i>

                    <!-- Step 2 (Highlighted) -->
                    <div class="flow-step step-2 text-center p-3 mb-2" style="cursor: pointer; border: 3px solid #1cc88a; background: #f0fff4;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="step-number mx-auto d-inline-block" style="background: #1cc88a; color: white;">2</span>
                                <span class="font-weight-bold ml-2 text-success">Preparar CSV</span>
                                <span class="badge badge-danger ml-2">CRÍTICO</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted"></i>
                        </div>
                        <div class="text-left mt-1">
                            <small class="text-muted">Paso más importante - Complete con datos de afiliados</small>
                        </div>
                    </div>
                    <i class="fas fa-arrow-down text-primary my-1"></i>

                    <!-- Step 3 -->
                    <div class="flow-step step-3 text-center p-3 mb-2" style="cursor: pointer;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="step-number mx-auto d-inline-block">3</span>
                                <span class="font-weight-bold ml-2">Seleccionar Configuración</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted"></i>
                        </div>
                    </div>
                    <i class="fas fa-arrow-down text-primary my-1"></i>

                    <!-- Step 4 -->
                    <div class="flow-step step-4 text-center p-3 mb-2" style="cursor: pointer;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="step-number mx-auto d-inline-block">4</span>
                                <span class="font-weight-bold ml-2">Completar Formulario</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted"></i>
                        </div>
                    </div>
                    <i class="fas fa-arrow-down text-primary my-1"></i>

                    <!-- Step 5 -->
                    <div class="flow-step step-5 text-center p-3 mb-2" style="cursor: pointer;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="step-number mx-auto d-inline-block">5</span>
                                <span class="font-weight-bold ml-2">Procesar Carga</span>
                            </div>
                            <i class="fas fa-chevron-right text-muted"></i>
                        </div>
                    </div>
                    <i class="fas fa-arrow-down text-primary my-1"></i>

                    <!-- Result -->
                    <div class="result-card-final text-center p-3 rounded" style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%); border-radius: 12px;">
                        <i class="fas fa-check-circle fa-2x text-white"></i>
                        <h6 class="font-weight-bold text-white mb-0 mt-1">¡Proceso Completado!</h6>
                        <small class="text-white-50">Resumen detallado</small>
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
            </div>
            <div class="alert alert-info mt-3 mb-0">
                <i class="fas fa-lightbulb mr-2"></i>
                <strong>Consejo:</strong> La plantilla ya no requiere <code>plan_id</code>, <code>clinica_id</code> ni <code>asesor_id</code>.
                Estos se seleccionan en el Paso 3.
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- PASO 2: PREPARAR ARCHIVO CSV - ENHANCED     -->
    <!-- ============================================ -->
    <div id="step2-prepare" class="card shadow-lg mb-5 border-0" style="border-top: 6px solid #1cc88a !important; position: relative; overflow: hidden;">
        <!-- Eye-catching ribbon -->
        <div style="position: absolute; top: 20px; right: -35px; transform: rotate(45deg); background: #1cc88a; color: white; padding: 5px 40px; font-size: 0.7rem; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; z-index: 10; box-shadow: 0 2px 10px rgba(28, 200, 138, 0.3);">
            <i class="fas fa-star mr-1"></i> MUY IMPORTANTE
        </div>

        <div class="card-header bg-gradient-step2 text-white py-3">
            <h5 class="mb-0 font-weight-bold">
                <span class="step-badge mr-2">2</span>
                <i class="fas fa-edit mr-2"></i>Preparar Archivo CSV
                <span class="badge badge-light text-success ml-3 px-3 py-2">
                    <i class="fas fa-exclamation-circle mr-1"></i> PASO CRÍTICO
                </span>
            </h5>
        </div>
        <div class="card-body">
            <!-- Alert Banner -->
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border-left: 6px solid #1cc88a; border-radius: 10px;">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <i class="fas fa-lightbulb text-success" style="font-size: 2.5rem;"></i>
                    </div>
                    <div>
                        <h6 class="font-weight-bold text-success mb-1">
                            <i class="fas fa-check-circle mr-1"></i> ¡La preparación del CSV es el paso más importante!
                        </h6>
                        <p class="mb-0 text-dark">
                            Un archivo bien preparado garantiza una carga exitosa. Revise cuidadosamente cada campo
                            y asegúrese de que los datos cumplan con el formato requerido.
                        </p>
                    </div>
                </div>
            </div>

            <!-- 3-Column Quick Guide -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 text-center" style="background: #f8f9fc; border-radius: 12px;">
                        <div class="card-body">
                            <div class="bg-success rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 60px; height: 60px;">
                                <i class="fas fa-check-double text-white" style="font-size: 1.8rem;"></i>
                            </div>
                            <h6 class="font-weight-bold mt-3 text-success">📋 Datos Correctos</h6>
                            <p class="small text-muted mb-0">Verifique que todos los campos requeridos estén completos y en el formato correcto.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 text-center" style="background: #f8f9fc; border-radius: 12px;">
                        <div class="card-body">
                            <div class="bg-info rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 60px; height: 60px;">
                                <i class="fas fa-file-alt text-white" style="font-size: 1.8rem;"></i>
                            </div>
                            <h6 class="font-weight-bold mt-3 text-info">📄 Formato CSV</h6>
                            <p class="small text-muted mb-0">Use la plantilla descargada. Respete el orden y los nombres de las columnas.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 text-center" style="background: #f8f9fc; border-radius: 12px;">
                        <div class="card-body">
                            <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 60px; height: 60px;">
                                <i class="fas fa-eye text-white" style="font-size: 1.8rem;"></i>
                            </div>
                            <h6 class="font-weight-bold mt-3 text-warning">🔍 Verificar Datos</h6>
                            <p class="small text-muted mb-0">Revise que no haya datos duplicados (cédula y email) antes de cargar.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table with Enhanced Styling -->
            <div class="table-responsive" style="border-radius: 12px; overflow: hidden; box-shadow: 0 2px 15px rgba(0,0,0,0.08);">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-primary bg-primary text-white">
                        <tr>
                            <th style="width: 15%;">Campo</th>
                            <th style="width: 10%;">Tipo</th>
                            <th style="width: 50%;">Descripción</th>
                            <th style="width: 25%;">Ejemplo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Required Fields Header -->
                        <tr class="table-success" style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);">
                            <td colspan="4" class="font-weight-bold text-success">
                                <i class="fas fa-exclamation-circle mr-2"></i> CAMPOS REQUERIDOS
                                <span class="badge badge-success ml-2">Deben estar completos</span>
                            </td>
                        </tr>
                        <tr>
                            <td><code class="font-weight-bold text-danger">tipo_cedula</code></td>
                            <td><span class="badge badge-secondary">Texto</span></td>
                            <td>Tipo de documento de identidad</td>
                            <td><kbd>V</kbd>, <kbd>E</kbd>, <kbd>P</kbd>, <kbd>J</kbd></td>
                        </tr>
                        <tr>
                            <td><code class="font-weight-bold text-danger">cedula</code></td>
                            <td><span class="badge badge-secondary">Numérico</span></td>
                            <td>Solo números (sin prefijos ni guiones)</td>
                            <td><kbd>19088456</kbd></td>
                        </tr>
                        <tr>
                            <td><code class="font-weight-bold text-danger">nombres</code></td>
                            <td><span class="badge badge-secondary">Texto</span></td>
                            <td>Nombres completos (mayúsculas recomendadas)</td>
                            <td><kbd>JUAN PABLO</kbd></td>
                        </tr>
                        <tr>
                            <td><code class="font-weight-bold text-danger">apellidos</code></td>
                            <td><span class="badge badge-secondary">Texto</span></td>
                            <td>Apellidos completos (mayúsculas recomendadas)</td>
                            <td><kbd>ROJAS PEREZ</kbd></td>
                        </tr>
                        <tr>
                            <td><code class="font-weight-bold text-danger">fechanac</code></td>
                            <td><span class="badge badge-secondary">Fecha</span></td>
                            <td>Formato: <strong>DD/MM/AAAA</strong> o <strong>YYYY-MM-DD</strong></td>
                            <td><kbd>15/05/1990</kbd> o <kbd>1990-05-15</kbd></td>
                        </tr>
                        <tr>
                            <td><code class="font-weight-bold text-danger">sexo</code></td>
                            <td><span class="badge badge-secondary">Texto</span></td>
                            <td><strong>M</strong>, <strong>F</strong>, <strong>Masculino</strong>, <strong>Femenino</strong></td>
                            <td><kbd>Masculino</kbd> o <kbd>M</kbd></td>
                        </tr>
                        <tr>
                            <td><code class="font-weight-bold text-danger">telefono</code></td>
                            <td><span class="badge badge-secondary">Texto</span></td>
                            <td>11 dígitos (formato venezolano: 0412-1234567)</td>
                            <td><kbd>04121234567</kbd></td>
                        </tr>
                        <tr>
                            <td><code class="font-weight-bold text-danger">email</code></td>
                            <td><span class="badge badge-secondary">Email</span></td>
                            <td>Debe ser <strong>único</strong> en el sistema</td>
                            <td><kbd>juan@ejemplo.com</kbd></td>
                        </tr>
                        <tr>
                            <td><code class="font-weight-bold text-danger">direccion</code></td>
                            <td><span class="badge badge-secondary">Texto</span></td>
                            <td>Dirección de residencia</td>
                            <td><kbd>Calle Principal #123</kbd></td>
                        </tr>
                        <tr>
                            <td><code class="font-weight-bold text-danger">estado</code></td>
                            <td><span class="badge badge-secondary">Texto</span></td>
                            <td>Estado (ver catálogo descargable)</td>
                            <td><kbd>MIRANDA</kbd></td>
                        </tr>

                        <!-- Optional Fields Header -->
                        <tr class="table-info" style="background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);">
                            <td colspan="4" class="font-weight-bold text-info">
                                <i class="fas fa-info-circle mr-2"></i> CAMPOS OPCIONALES
                                <span class="badge badge-info ml-2">Pueden estar vacíos</span>
                            </td>
                        </tr>
                        <tr>
                            <td><code>nacionalidad</code></td>
                            <td><span class="badge badge-secondary">Texto</span></td>
                            <td>Nacionalidad de la persona</td>
                            <td><kbd>VENEZOLANA</kbd></td>
                        </tr>
                        <tr>
                            <td><code>estado_civil</code></td>
                            <td><span class="badge badge-secondary">Texto</span></td>
                            <td><strong>Soltero</strong>, <strong>Casado</strong>, <strong>Divorciado</strong>, <strong>Viudo</strong></td>
                            <td><kbd>Casado</kbd></td>
                        </tr>
                        <tr>
                            <td><code>lugar_nacimiento</code></td>
                            <td><span class="badge badge-secondary">Texto</span></td>
                            <td>Lugar de nacimiento</td>
                            <td><kbd>CARACAS</kbd></td>
                        </tr>
                        <tr>
                            <td><code>profesion</code></td>
                            <td><span class="badge badge-secondary">Texto</span></td>
                            <td>Profesión u oficio</td>
                            <td><kbd>INGENIERO</kbd></td>
                        </tr>
                        <tr>
                            <td><code>ocupacion</code></td>
                            <td><span class="badge badge-secondary">Texto</span></td>
                            <td>Ocupación actual</td>
                            <td><kbd>EMPLEADO</kbd></td>
                        </tr>
                        <tr>
                            <td><code>actividad_economica</code></td>
                            <td><span class="badge badge-secondary">Texto</span></td>
                            <td><strong>Industrial</strong>, <strong>Comercial</strong>, <strong>Profesional</strong>, <strong>Gubernamental</strong></td>
                            <td><kbd>Gubernamental</kbd></td>
                        </tr>
                        <tr>
                            <td><code>ramo_comercial</code></td>
                            <td><span class="badge badge-secondary">Texto</span></td>
                            <td>Ramo o sector comercial</td>
                            <td><kbd>SERVICIOS</kbd></td>
                        </tr>
                        <tr>
                            <td><code>descripcion_actividad</code></td>
                            <td><span class="badge badge-secondary">Texto</span></td>
                            <td><strong>Independiente</strong>, <strong>Dependiente</strong>, <strong>Societaria</strong></td>
                            <td><kbd>Dependiente</kbd></td>
                        </tr>
                        <tr>
                            <td><code>ingreso_anual</code></td>
                            <td><span class="badge badge-secondary">Texto</span></td>
                            <td>Rango de ingreso anual</td>
                            <td><kbd>De 6 a 10 Salarios mínimos</kbd></td>
                        </tr>
                        <tr>
                            <td><code>direccion_cobro</code></td>
                            <td><span class="badge badge-secondary">Texto</span></td>
                            <td>Dirección para envío de facturación</td>
                            <td><kbd>Av. Principal #456</kbd></td>
                        </tr>
                        <tr>
                            <td><code>telefono_residencia</code></td>
                            <td><span class="badge badge-secondary">Texto</span></td>
                            <td>Teléfono fijo de residencia</td>
                            <td><kbd>02125551234</kbd></td>
                        </tr>
                        <tr>
                            <td><code>direccion_oficina</code></td>
                            <td><span class="badge badge-secondary">Texto</span></td>
                            <td>Dirección de la oficina</td>
                            <td><kbd>Av. Principal, Edif. Azul</kbd></td>
                        </tr>
                        <tr>
                            <td><code>telefono_oficina</code></td>
                            <td><span class="badge badge-secondary">Texto</span></td>
                            <td>Teléfono de la oficina</td>
                            <td><kbd>2125871425</kbd></td>
                        </tr>
                        <tr>
                            <td><code>tipo_sangre</code></td>
                            <td><span class="badge badge-secondary">Texto</span></td>
                            <td><strong>A+, A-, B+, B-, AB+, AB-, O+, O-</strong></td>
                            <td><kbd>A+</kbd></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Tips Section -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card border-warning shadow-sm h-100" style="border-radius: 12px; border-left: 6px solid #ffc107;">
                        <div class="card-body">
                            <h6 class="font-weight-bold text-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i> Errores Comunes
                            </h6>
                            <ul class="small text-muted mb-0 pl-3">
                                <li class="mb-1">✗ Teléfonos sin el <strong>0</strong> inicial (Ej: 4243283480 → debe ser 04243283480)</li>
                                <li class="mb-1">✗ Fechas en formato incorrecto (Ej: 17/10/89 → debe ser 17/10/1989)</li>
                                <li class="mb-1">✗ Cédula con prefijos o guiones (Ej: V-19088456 → debe ser 19088456)</li>
                                <li class="mb-1">✗ Emails duplicados en el archivo o en el sistema</li>
                                <li>✗ Estado escrito incorrectamente (use los nombres exactos del catálogo)</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-success shadow-sm h-100" style="border-radius: 12px; border-left: 6px solid #28a745;">
                        <div class="card-body">
                            <h6 class="font-weight-bold text-success">
                                <i class="fas fa-check-circle mr-2"></i> Consejos para el Éxito
                            </h6>
                            <ul class="small text-muted mb-0 pl-3">
                                <li class="mb-1">✓ Descargue y use la <strong>plantilla de ejemplo</strong></li>
                                <li class="mb-1">✓ Revise que no haya <strong>cédulas duplicadas</strong> en el archivo</li>
                                <li class="mb-1">✓ Verifique que los <strong>emails</strong> sean únicos y válidos</li>
                                <li class="mb-1">✓ Use <strong>mayúsculas</strong> para nombres y apellidos</li>
                                <li>✓ Guarde el archivo en formato <strong>CSV UTF-8</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Validation Checklist -->
            <div class="mt-4 p-3 bg-light rounded" style="border: 2px dashed #6c757d; border-radius: 12px;">
                <h6 class="font-weight-bold text-secondary mb-2">
                    <i class="fas fa-clipboard-list mr-2"></i> Lista de Verificación Rápida
                </h6>
                <div class="row">
                    <div class="col-md-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="check1">
                            <label class="custom-control-label small" for="check1">✓ Usé la plantilla descargada</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="check2">
                            <label class="custom-control-label small" for="check2">✓ Todos los campos requeridos están completos</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="check3">
                            <label class="custom-control-label small" for="check3">✓ No hay cédulas duplicadas</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="check4">
                            <label class="custom-control-label small" for="check4">✓ Los emails son únicos y válidos</label>
                        </div>
                    </div>
                    <div class="col-md-3 mt-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="check5">
                            <label class="custom-control-label small" for="check5">✓ Teléfonos tienen el 0 inicial (11 dígitos)</label>
                        </div>
                    </div>
                    <div class="col-md-3 mt-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="check6">
                            <label class="custom-control-label small" for="check6">✓ Fechas en formato correcto (DD/MM/AAAA)</label>
                        </div>
                    </div>
                    <div class="col-md-3 mt-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="check7">
                            <label class="custom-control-label small" for="check7">✓ Cédula sin prefijos ni guiones</label>
                        </div>
                    </div>
                    <div class="col-md-3 mt-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="check8">
                            <label class="custom-control-label small" for="check8">✓ Archivo guardado como CSV</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- PASO 3: SELECCIONAR CORPORATIVO, PLAN Y ASESOR -->
    <!-- ============================================ -->
    <div id="step3-validate" class="card shadow-lg border-primary mb-5">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0 font-weight-bold">
                <span class="step-badge bg-white text-primary mr-2">3</span>
                <i class="fas fa-building mr-2"></i>Seleccionar Configuración
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Importante:</strong> Seleccione el corporativo, plan y asesor que se asignará a todos los afiliados
                del archivo CSV. El sistema determinará automáticamente la clínica asociada al plan seleccionado.
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group p-3 bg-light rounded">
                        <label class="font-weight-bold text-primary mb-2">
                            <i class="fas fa-building mr-2"></i>
                            Corporativo <span class="text-danger">*</span>
                        </label>
                        <?= Html::dropDownList(
                            'corporativo_selector',
                            null,
                            $corporativos,
                            [
                                'id' => 'corporativo-selector',
                                'prompt' => '--- Seleccione el Corporativo ---',
                                'class' => 'form-control form-control-lg',
                            ]
                        ) ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group p-3 bg-light rounded">
                        <label class="font-weight-bold text-primary mb-2">
                            <i class="fas fa-file-contract mr-2"></i>
                            Plan de Afiliación <span class="text-danger">*</span>
                        </label>
                        <?= Html::dropDownList(
                            'plan_selector',
                            null,
                            [],
                            [
                                'id' => 'plan-selector',
                                'prompt' => '--- Primero seleccione un corporativo ---',
                                'class' => 'form-control form-control-lg',
                                'disabled' => true,
                            ]
                        ) ?>
                        <div id="plan-info" class="mt-2" style="display: none;">
                            <div class="alert alert-success small">
                                <i class="fas fa-check-circle mr-1"></i>
                                <strong>Clínica:</strong> <span id="clinica-nombre">-</span>
                                <span class="badge badge-info ml-2">ID: <span id="clinica-id">-</span></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group p-3 bg-light rounded">
                        <label class="font-weight-bold text-primary mb-2">
                            <i class="fas fa-user-tie mr-2"></i>
                            Asesor / Intermediario
                        </label>
                        <?= Html::dropDownList(
                            'asesor_selector',
                            null,
                            $asesores,
                            [
                                'id' => 'asesor-selector',
                                'prompt' => '--- Sin Asesor (Opcional) ---',
                                'class' => 'form-control form-control-lg',
                            ]
                        ) ?>
                        <small class="text-muted">Opcional: Seleccione un asesor si desea asociarlo a los afiliados.</small>
                    </div>
                </div>
            </div>

            <!-- Resultados dinámicos de la selección -->
            <div id="selected-info" class="mt-3">
                <div class="alert alert-secondary mb-0 text-center py-4">
                    <i class="fas fa-hand-pointer fa-2x mb-2 d-block text-muted"></i>
                    <strong>Seleccione un corporativo y un plan arriba</strong><br>
                    para confirmar la configuración antes de continuar.
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- PASO 4 Y 5: FORMULARIO                       -->
    <!-- ============================================ -->
    <?php $form = ActiveForm::begin([
        'options' => [
            'enctype' => 'multipart/form-data',
            'id' => 'carga-masiva-form',
        ],
    ]); ?>

    <div id="step4-form" class="card shadow-lg border-warning mb-5">
        <div class="card-header bg-gradient-step4 text-white py-3">
            <h5 class="mb-0 font-weight-bold">
                <span class="step-badge mr-2">4</span>
                <i class="fas fa-calendar-alt mr-2"></i>Completar Formulario de Carga
            </h5>
        </div>
        <div class="card-body p-4">
            <!-- Hidden fields for corporativo_id, plan_id, and asesor_id -->
            <?= $form->field($model, 'corporativo_id')->hiddenInput(['id' => 'masivoafiliadosform-corporativo_id'])->label(false) ?>
            <?= $form->field($model, 'plan_id')->hiddenInput(['id' => 'masivoafiliadosform-plan_id'])->label(false) ?>
            <?= $form->field($model, 'asesor_id')->hiddenInput(['id' => 'masivoafiliadosform-asesor_id'])->label(false) ?>

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
                    'required' => true,
                    'id' => 'csv-file-input',
                ])->label(false) ?>
                <small class="text-muted">Formatos permitidos: CSV | Tamaño máximo: 5MB</small>
                <div id="file-info" class="mt-2" style="display: none;">
                    <div class="alert alert-success small">
                        <i class="fas fa-check-circle mr-1"></i>
                        Archivo seleccionado: <strong id="file-name">-</strong>
                        <span class="badge badge-info ml-2" id="file-size">-</span>
                    </div>
                </div>
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
                    'id' => 'submit-button',
                ]
            ) ?>
            <div id="validation-alert" class="mt-3" style="display: none;">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span id="validation-message"></span>
                </div>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<!-- ============================================ -->
<!-- ESTILOS CSS - COMPLETO Y CORREGIDO           -->
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

    /* ============================================ */
    /* FLOW STEP STYLES - WITH GRADIENT BACKGROUNDS */
    /* ============================================ */
    .flow-step {
        border-radius: 16px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid #e3e6f0;
        position: relative;
        overflow: hidden;
        min-height: 160px;
    }

    .flow-step::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.2) 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }

    .flow-step:hover::before {
        opacity: 1;
    }

    .flow-step:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12) !important;
    }

    /* Step 1 - Blue Gradient */
    .step-1 {
        background: linear-gradient(135deg, #f0f4ff 0%, #dce3f5 100%) !important;
        border-top: 4px solid #4e73df !important;
    }

    .step-1 .step-number {
        color: #4e73df;
        border: 3px solid #4e73df;
        background: white;
    }

    .step-1:hover .step-number {
        background: #4e73df;
        color: white;
    }

    .step-1 h6 {
        color: #4e73df !important;
    }

    /* Step 2 - Green Gradient (Highlighted - Most Important) */
    .step-2 {
        background: linear-gradient(135deg, #e8f8f0 0%, #c3e6cb 100%) !important;
        border: 3px solid #1cc88a !important;
        box-shadow: 0 0 30px rgba(28, 200, 138, 0.2) !important;
    }

    .step-2 .step-number {
        color: #1cc88a;
        border: 3px solid #1cc88a;
        background: #1cc88a;
        color: white;
    }

    .step-2:hover .step-number {
        transform: scale(1.1);
        box-shadow: 0 0 30px rgba(28, 200, 138, 0.4);
    }

    .step-2 h6 {
        color: #1cc88a !important;
    }

    .step-2:hover {
        box-shadow: 0 15px 40px rgba(28, 200, 138, 0.3) !important;
    }

    /* Step 3 - Teal/Cyan Gradient */
    .step-3 {
        background: linear-gradient(135deg, #e3f7fa 0%, #bee5eb 100%) !important;
        border-top: 4px solid #36b9cc !important;
    }

    .step-3 .step-number {
        color: #36b9cc;
        border: 3px solid #36b9cc;
        background: white;
    }

    .step-3:hover .step-number {
        background: #36b9cc;
        color: white;
    }

    .step-3 h6 {
        color: #36b9cc !important;
    }

    /* Step 4 - Yellow Gradient */
    .step-4 {
        background: linear-gradient(135deg, #fef9e7 0%, #fce8b2 100%) !important;
        border-top: 4px solid #f6c23e !important;
    }

    .step-4 .step-number {
        color: #f6c23e;
        border: 3px solid #f6c23e;
        background: white;
    }

    .step-4:hover .step-number {
        background: #f6c23e;
        color: white;
    }

    .step-4 h6 {
        color: #f6c23e !important;
    }

    /* Step 5 - Red Gradient */
    .step-5 {
        background: linear-gradient(135deg, #fde8e8 0%, #f5c6c6 100%) !important;
        border-top: 4px solid #e74a3b !important;
    }

    .step-5 .step-number {
        color: #e74a3b;
        border: 3px solid #e74a3b;
        background: white;
    }

    .step-5:hover .step-number {
        background: #e74a3b;
        color: white;
    }

    .step-5 h6 {
        color: #e74a3b !important;
    }

    /* Step Number Base */
    .step-number {
        width: 40px;
        height: 40px;
        background: white;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.1rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        position: relative;
        z-index: 2;
    }

    /* Step Badge */
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

    /* Result Card */
    .result-card-small {
        background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        box-shadow: 0 5px 15px rgba(28, 200, 138, 0.3);
    }

    /* Result Card Final */
    .result-card-final {
        transition: all 0.3s ease;
        min-height: 160px;
    }

    .result-card-final:hover {
        transform: scale(1.02);
        box-shadow: 0 15px 50px rgba(28, 200, 138, 0.4) !important;
    }

    .result-icon-wrapper {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        width: 70px;
        height: 70px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        animation: pulse-number 2s infinite;
    }

    /* Thead Primary */
    .thead-primary th {
        background-color: #4e73df !important;
        color: white !important;
    }

    /* Code styling */
    code {
        background: #f4f4f4;
        padding: 2px 6px;
        border-radius: 4px;
        color: #e83e8c;
    }

    /* ============================================ */
    /* ANIMATIONS                                  */
    /* ============================================ */

    /* Step Number Pulse Animation */
    @keyframes pulse-number {
        0% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(78, 115, 223, 0.7);
        }

        70% {
            transform: scale(1.1);
            box-shadow: 0 0 0 15px rgba(78, 115, 223, 0);
        }

        100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(78, 115, 223, 0);
        }
    }

    .animated-step {
        animation: pulse-number 3s infinite;
    }

    /* Arrow Pulse Animation */
    @keyframes arrow-pulse {
        0% {
            transform: translateX(0);
            opacity: 1;
        }

        50% {
            transform: translateX(8px);
            opacity: 0.7;
        }

        100% {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .arrow-pulse {
        animation: arrow-pulse 1.5s infinite;
    }

    /* Badge Pulse */
    @keyframes pulse-badge {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    /* Highlight Pulse */
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

    /* Required Missing Pulse */
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

    /* ============================================ */
    /* PROGRESS OVERLAY                            */
    /* ============================================ */

    #progress-overlay {
        display: none;
    }

    #progress-overlay .spinner-border {
        border-width: 6px;
    }

    #upload-progress-bar {
        transition: width 0.3s ease;
    }

    #progress-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        z-index: 99999;
        display: none;
        align-items: center;
        justify-content: center;
    }

    #progress-overlay .card {
        animation: fadeInUp 0.3s ease;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Arrow Container */
    .arrow-container {
        padding: 10px 0;
    }

    /* Progress Bar */
    #flow-progress {
        transition: width 1s ease;
    }

    /* Gap utility for flex wrap */
    .gap-3 {
        gap: 0.75rem;
    }

    /* ============================================ */
    /* RESPONSIVE                                  */
    /* ============================================ */

    @media (max-width: 768px) {
        .display-4 {
            font-size: 1.8rem;
        }

        #progress-overlay .card {
            max-width: 95%;
            width: 95%;
        }

        .flow-step {
            min-height: auto;
            padding: 12px !important;
        }

        .step-number {
            width: 32px;
            height: 32px;
            font-size: 0.9rem;
        }

        .result-card-final {
            min-height: auto;
            padding: 16px !important;
        }

        .result-icon-wrapper {
            width: 50px;
            height: 50px;
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
        const getPlanesUrl = "<?= $getPlanesUrl ?>";

        // File input change handler
        $('#csv-file-input').on('change', function() {
            const file = this.files[0];
            if (file) {
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                $('#file-name').text(file.name);
                $('#file-size').text(fileSize + ' MB');
                $('#file-info').show();
            } else {
                $('#file-info').hide();
            }
        });

        // Function to load plans for a corporate
        function cargarPlanesPorCorporativo(corporativoId) {
            const planSelector = $('#plan-selector');
            const planInfo = $('#plan-info');
            const infoDiv = $('#selected-info');

            if (!corporativoId) {
                planSelector.prop('disabled', true)
                    .html('<option value="">--- Primero seleccione un corporativo ---</option>')
                    .val('');
                planInfo.hide();
                infoDiv.html(
                    '<div class="alert alert-secondary mb-0 text-center py-4"><i class="fas fa-hand-pointer fa-2x mb-2 d-block text-muted"></i><strong>Seleccione un corporativo arriba</strong><br>para ver los planes disponibles.</div>'
                );
                return;
            }

            // Show loading state
            planSelector.prop('disabled', true)
                .html('<option value="">Cargando planes...</option>')
                .val('');
            infoDiv.html(
                '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><br><span class="text-muted">Cargando planes disponibles...</span></div>'
            );

            $.ajax({
                url: getPlanesUrl,
                type: 'GET',
                data: {
                    id: corporativoId
                },
                dataType: 'json',
                success: function(response) {
                    console.log('AJAX Response:', response);

                    if (response.success && response.data && response.data.length > 0) {
                        let options = '<option value="">--- Seleccione un Plan ---</option>';
                        let planInfoHtml = '<div class="row">';

                        $.each(response.data, function(index, plan) {
                            options += '<option value="' + plan.id + '" data-clinica="' + plan
                                .clinica_nombre + '" data-clinica-id="' + plan.clinica_id +
                                '">' + plan.nombre + '</option>';

                            planInfoHtml += '<div class="col-md-6 mb-3">';
                            planInfoHtml += '<div class="card border-info h-100 shadow-sm">';
                            planInfoHtml += '<div class="card-header bg-info text-white font-weight-bold">';
                            planInfoHtml += '<i class="fas fa-file-contract mr-2"></i> ' + plan
                                .nombre;
                            planInfoHtml += '</div>';
                            planInfoHtml += '<div class="card-body">';
                            planInfoHtml += '<p class="mb-1"><strong>Clínica:</strong> ' + plan
                                .clinica_nombre + '</p>';
                            planInfoHtml += '<p class="mb-1"><strong>Precio:</strong> $' + parseFloat(
                                plan.precio).toFixed(2) + '</p>';
                            planInfoHtml += '<p class="mb-0"><strong>ID Plan:</strong> <span class="badge badge-primary">' +
                                plan.id + '</span></p>';
                            planInfoHtml += '</div></div></div>';
                        });

                        planInfoHtml += '</div>';

                        planSelector.html(options);
                        planSelector.prop('disabled', false);
                        infoDiv.html(planInfoHtml);
                        planInfo.hide();

                    } else if (response.success && (!response.data || response.data.length === 0)) {
                        planSelector.prop('disabled', true)
                            .html('<option value="">--- No hay planes disponibles ---</option>')
                            .val('');
                        infoDiv.html(
                            '<div class="alert alert-warning mb-0 text-center py-4"><i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i><strong>No se encontraron planes</strong><br>Este corporativo no tiene planes asociados. Contacte al administrador.</div>'
                        );
                        planInfo.hide();
                    } else {
                        planSelector.prop('disabled', true)
                            .html('<option value="">--- Error al cargar planes ---</option>')
                            .val('');
                        infoDiv.html(
                            '<div class="alert alert-danger mb-0"><i class="fas fa-bug mr-2"></i>Error: ' +
                            (response.error || 'No se pudieron cargar los datos') + '</div>'
                        );
                        planInfo.hide();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.error('Response Text:', xhr.responseText);
                    planSelector.prop('disabled', true)
                        .html('<option value="">--- Error de conexión ---</option>')
                        .val('');
                    infoDiv.html(
                        '<div class="alert alert-danger mb-0"><i class="fas fa-plug mr-2"></i>Error de conexión. Verifique que el servidor esté funcionando correctamente.<br><small>Detalles: ' +
                        error + '</small></div>'
                    );
                    planInfo.hide();
                }
            });
        }

        // When corporate selector changes
        $('#corporativo-selector').on('change', function() {
            var selectedValue = $(this).val();
            $('#masivoafiliadosform-corporativo_id').val(selectedValue);
            cargarPlanesPorCorporativo(selectedValue);
            $('#masivoafiliadosform-plan_id').val('');
        });

        // When plan selector changes
        $('#plan-selector').on('change', function() {
            var planId = $(this).val();
            var selectedOption = $(this).find('option:selected');

            $('#masivoafiliadosform-plan_id').val(planId);

            if (planId) {
                var clinicaNombre = selectedOption.data('clinica') || '-';
                var clinicaId = selectedOption.data('clinica-id') || '-';

                $('#clinica-nombre').text(clinicaNombre);
                $('#clinica-id').text(clinicaId);
                $('#plan-info').show();

                var asesorName = $('#asesor-selector option:selected').text();
                if (asesorName === '--- Sin Asesor (Opcional) ---') {
                    asesorName = 'Ninguno';
                }

                $('#selected-info .alert-secondary').html(
                    '<i class="fas fa-check-circle fa-2x mb-2 d-block text-success"></i>' +
                    '<strong>Configuración confirmada</strong><br>' +
                    'Corporativo: <span class="font-weight-bold">' + $('#corporativo-selector option:selected')
                    .text() + '</span><br>' +
                    'Plan: <span class="font-weight-bold">' + selectedOption.text() + '</span><br>' +
                    'Clínica: <span class="font-weight-bold text-info">' + clinicaNombre + '</span><br>' +
                    'Asesor: <span class="font-weight-bold">' + asesorName + '</span>'
                );
            } else {
                $('#plan-info').hide();
                $('#selected-info .alert-secondary').html(
                    '<i class="fas fa-hand-pointer fa-2x mb-2 d-block text-muted"></i>' +
                    '<strong>Seleccione un corporativo y un plan arriba</strong><br>' +
                    'para confirmar la configuración antes de continuar.'
                );
            }
        });

        // When asesor selector changes
        $('#asesor-selector').on('change', function() {
            $('#masivoafiliadosform-asesor_id').val($(this).val());

            if ($('#plan-selector').val()) {
                var asesorName = $(this).find('option:selected').text();
                if (asesorName === '--- Sin Asesor (Opcional) ---') {
                    asesorName = 'Ninguno';
                }
                $('#plan-selector').trigger('change');
            }
        });

        // Initial load if there's a preselected value
        if ($('#corporativo-selector').val()) {
            $('#masivoafiliadosform-corporativo_id').val($('#corporativo-selector').val());
            cargarPlanesPorCorporativo($('#corporativo-selector').val());

            var preselectedPlan = '<?= $model->plan_id ?? '' ?>';
            if (preselectedPlan) {
                $('#plan-selector').val(preselectedPlan).trigger('change');
            }

            var preselectedAsesor = '<?= $model->asesor_id ?? '' ?>';
            if (preselectedAsesor) {
                $('#asesor-selector').val(preselectedAsesor);
                $('#masivoafiliadosform-asesor_id').val(preselectedAsesor);
            }
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

        // ============================================ //
        // FORM SUBMISSION WITH PROGRESS OVERLAY        //
        // ============================================ //

        // Form validation before submit
        $('#carga-masiva-form').on('submit', function(e) {
            var corporativoId = $('#masivoafiliadosform-corporativo_id').val();
            var planId = $('#masivoafiliadosform-plan_id').val();
            var fechaIni = $('.fecha-ini-field').val();
            var fileInput = $('input[type="file"]').val();

            // Reset validation alert
            $('#validation-alert').hide();

            // Validate corporativo
            if (!corporativoId || corporativoId === '') {
                e.preventDefault();
                $('#validation-message').text('ERROR: Debe seleccionar un corporativo en el Paso 3.');
                $('#validation-alert').show();
                smoothScrollTo('#step3-validate', 500);
                $('#step3-validate').addClass('required-missing');
                setTimeout(function() {
                    $('#step3-validate').removeClass('required-missing');
                }, 2000);
                return false;
            }

            // Validate plan
            if (!planId || planId === '') {
                e.preventDefault();
                $('#validation-message').text('ERROR: Debe seleccionar un plan en el Paso 3.');
                $('#validation-alert').show();
                smoothScrollTo('#step3-validate', 500);
                $('#step3-validate').addClass('required-missing');
                setTimeout(function() {
                    $('#step3-validate').removeClass('required-missing');
                }, 2000);
                return false;
            }

            // Validate date
            if (!fechaIni) {
                e.preventDefault();
                $('#validation-message').text('ERROR: Debe seleccionar una fecha de inicio.');
                $('#validation-alert').show();
                smoothScrollTo('#step4-form', 500);
                return false;
            }

            // Validate file
            if (!fileInput) {
                e.preventDefault();
                $('#validation-message').text('ERROR: Debe seleccionar un archivo CSV para cargar.');
                $('#validation-alert').show();
                smoothScrollTo('#step4-form', 500);
                return false;
            }

            // If all validations pass, show progress overlay
            showProgressOverlay();
            return true;
        });

        // ============================================ //
        // PROGRESS OVERLAY FUNCTIONS                   //
        // ============================================ //

        function showProgressOverlay() {
            // Show the overlay
            $('#progress-overlay').css('display', 'flex');

            // Reset progress bar
            $('#upload-progress-bar').css('width', '0%').removeClass('bg-danger').addClass('bg-success');
            $('#upload-progress-text').text('0%');
            $('#progress-title').text('Procesando Carga Masiva');
            $('#progress-status').text('Preparando archivo...');

            // Start simulating progress while file uploads
            var progress = 0;
            var interval = setInterval(function() {
                progress += 3;
                if (progress > 90) {
                    clearInterval(interval);
                    $('#progress-status').text('Procesando archivo en el servidor...');
                    return;
                }
                $('#upload-progress-bar').css('width', progress + '%');
                $('#upload-progress-text').text(progress + '%');

                // Update status messages based on progress
                if (progress < 30) {
                    $('#progress-status').text('Validando archivo...');
                } else if (progress < 60) {
                    $('#progress-status').text('Subiendo archivo al servidor...');
                } else if (progress < 90) {
                    $('#progress-status').text('Procesando datos...');
                }
            }, 300);

            // Store interval to clear it later
            window.progressInterval = interval;
        }

        function hideProgressOverlay() {
            $('#progress-overlay').fadeOut(500);
            if (window.progressInterval) {
                clearInterval(window.progressInterval);
                window.progressInterval = null;
            }
        }

        // ============================================ //
        // HANDLE PAGE LOAD WITH FLASH MESSAGES         //
        // ============================================ //
        <?php if (Yii::$app->session->hasFlash('success') || Yii::$app->session->hasFlash('warning') || Yii::$app->session->hasFlash('error')): ?>
            $(document).ready(function() {
                // Show progress overlay with result
                $('#progress-overlay').css('display', 'flex');

                // Remove spinner, show result icon
                $('.spinner-border').remove();
                <?php if (Yii::$app->session->hasFlash('success')): ?>
                    $('#progress-overlay .card-body').prepend('<i class="fas fa-check-circle text-success" style="font-size: 60px;"></i>');
                    $('#upload-progress-bar').css('width', '100%').removeClass('bg-success').addClass('bg-success');
                    $('#upload-progress-text').text('100%');
                    $('#progress-title').text('✅ ¡Proceso Completado!');
                    $('#progress-status').text('<?= addslashes(Yii::$app->session->getFlash('success')) ?>');
                <?php else: ?>
                    $('#progress-overlay .card-body').prepend('<i class="fas fa-exclamation-circle text-danger" style="font-size: 60px;"></i>');
                    $('#upload-progress-bar').css('width', '100%').removeClass('bg-success').addClass('bg-danger');
                    $('#upload-progress-text').text('100%');
                    $('#progress-title').text('⚠️ Proceso Completado con Errores');
                    <?php
                    $message = Yii::$app->session->getFlash('warning') ?: Yii::$app->session->getFlash('error');
                    $message = addslashes($message);
                    ?>
                    $('#progress-status').text('<?= $message ?>');
                <?php endif; ?>

                // Hide after 4 seconds
                setTimeout(function() {
                    $('#progress-overlay').fadeOut(500);
                }, 4000);
            });
        <?php endif; ?>

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