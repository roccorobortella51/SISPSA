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

// Registramos el script de JavaScript para manejar la funcionalidad de las clínicas y planes.
$this->registerJs(
    "
    // URL para obtener las clínicas Y planes
    const clinicasPlanesUrl = " . json_encode(Yii::$app->urlManager->createUrl(['corporativo/obtener-clinicas-con-planes-por-corporativo'])) . ";

    // Función que carga las clínicas y planes vía AJAX
    function cargarClinicasPlanes(corporativoId) {
        const infoDiv = $('#clinicas-asociadas-info');
        
        if (!corporativoId) {
            infoDiv.html('<div class=\"alert alert-info mb-0\"><i class=\"fas fa-info-circle mr-2\"></i>Seleccione un corporativo arriba para ver las clínicas asociadas y sus planes.</div>');
            return;
        }

        infoDiv.html('<div class=\"text-center py-5\"><i class=\"fas fa-spinner fa-spin fa-2x mr-2\"></i><br><span class=\"text-muted mt-2\">Cargando clínicas y planes asociados...</span></div>');

        $.ajax({
            url: clinicasPlanesUrl,
            type: 'GET',
            data: { id: corporativoId },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    
                    let html = '<div class=\"alert alert-success mb-3\">';
                    html += '<i class=\"fas fa-check-circle mr-2\"></i>';
                    html += '<strong>' + response.data.length + ' clínicas encontradas</strong> para este corporativo. Los IDs marcados en <span class=\"text-danger font-weight-bold\">ROJO</span> son los que debe usar en su CSV.';
                    html += '</div>';
                    html += '<div class=\"row\">';
                    
                    response.data.forEach(function(clinica) {
                        html += '<div class=\"col-md-6 mb-4\">';
                        html += '<div class=\"card border-info h-100 shadow-sm\">';
                        html += '<div class=\"card-header bg-info text-white font-weight-bold d-flex justify-content-between align-items-center\">';
                        html += '<div><i class=\"fas fa-hospital-alt mr-2\"></i> ' + clinica.nombre + '</div>';
                        html += '<span class=\"badge badge-warning text-dark p-2 px-3\">ID CLÍNICA: ' + clinica.id + '</span>'; 
                        html += '</div>';
                        
                        if (clinica.planes && clinica.planes.length > 0) {
                            html += '<div class=\"card-body p-3\">';
                            html += '<h6 class=\"text-dark ml-2 mt-2 mb-3\"><i class=\"fas fa-file-contract mr-2 text-primary\"></i> Planes disponibles (' + clinica.planes.length + '):</h6>';
                            html += '<div class=\"table-responsive\">';
                            html += '<table class=\"table table-bordered table-sm\">';
                            html += '<thead class=\"bg-light\">';
                            html += '<tr><th class=\"w-25\">ID del Plan</th><th>Nombre del Plan</th></tr>';
                            html += '</thead><tbody>';

                            clinica.planes.forEach(function(plan) {
                                html += '<tr>';
                                html += '<td class=\"font-weight-bold text-danger align-middle text-center\"><span class=\"badge badge-danger badge-pill px-3 py-2\">' + plan.id + '</span></td>'; 
                                html += '<td><i class=\"fas fa-file-signature mr-1\"></i> ' + plan.nombre + '</td>';
                                html += '</tr>';
                            });

                            html += '</tbody></table></div>';
                            
                            html += '<div class=\"alert alert-secondary mt-3 mb-0 small\">';
                            html += '<i class=\"fas fa-code mr-1\"></i> <strong>Ejemplo para su CSV:</strong> use <code class=\"text-danger\">plan_id: ' + clinica.planes[0].id + '</code> y <code class=\"text-danger\">clinica_id: ' + clinica.id + '</code>';
                            html += '</div>';
                            html += '</div>';
                        } else {
                            html += '<div class=\"card-body\">';
                            html += '<div class=\"alert alert-warning mb-0\">';
                            html += '<i class=\"fas fa-exclamation-triangle mr-2\"></i>';
                            html += 'No hay planes activos asociados a esta clínica. Contacte al administrador.';
                            html += '</div></div>';
                        }
                        
                        html += '</div></div>';
                    });
                    
                    html += '</div>';
                    infoDiv.html(html);

                } else if (response.success && (!response.data || response.data.length === 0)) {
                    infoDiv.html('<div class=\"alert alert-warning mb-0\"><i class=\"fas fa-exclamation-triangle mr-2\"></i>No se encontraron clínicas asociadas para el corporativo seleccionado. Verifique que el corporativo tenga clínicas asignadas.</div>');
                } else if (response.error) {
                     infoDiv.html('<div class=\"alert alert-danger mb-0\"><i class=\"fas fa-bug mr-2\"></i>Error del Servidor: ' + response.error + '</div>');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                infoDiv.html('<div class=\"alert alert-danger mb-0\"><i class=\"fas fa-plug mr-2\"></i>Error de Conexión. Verifique que el ID del corporativo es válido.</div>');
            }
        });
    }

    // Escucha el cambio en el selector del corporativo
    $('#corporativo-selector').on('change', function() {
        var selectedValue = $(this).val();
        cargarClinicasPlanes(selectedValue);
    });

    // Carga inicial
    var initialValue = $('#corporativo-selector').val();
    if (initialValue) {
        cargarClinicasPlanes(initialValue);
    }
    
    // SMOOTH SCROLL FUNCTION
    function smoothScrollTo(target, duration) {
        var targetElement = $(target);
        if (targetElement.length) {
            $('html, body').animate({
                scrollTop: targetElement.offset().top - 80
            }, duration);
        }
    }
    
    // CLICK HANDLERS FOR FLOWCHART STEPS
    $(document).ready(function() {
        // Paso 1 click
        $('.flow-step-link[data-step=\"1\"]').on('click', function(e) {
            e.preventDefault();
            smoothScrollTo('#step1-download', 500);
            // Highlight effect
            $('#step1-download').addClass('highlight-pulse');
            setTimeout(function() {
                $('#step1-download').removeClass('highlight-pulse');
            }, 1000);
        });
        
        // Paso 2 click
        $('.flow-step-link[data-step=\"2\"]').on('click', function(e) {
            e.preventDefault();
            smoothScrollTo('#step2-prepare', 500);
            $('#step2-prepare').addClass('highlight-pulse');
            setTimeout(function() {
                $('#step2-prepare').removeClass('highlight-pulse');
            }, 1000);
        });
        
        // Paso 3 click
        $('.flow-step-link[data-step=\"3\"]').on('click', function(e) {
            e.preventDefault();
            smoothScrollTo('#step3-validate', 500);
            $('#step3-validate').addClass('highlight-pulse');
            setTimeout(function() {
                $('#step3-validate').removeClass('highlight-pulse');
            }, 1000);
        });
        
        // Paso 4 click
        $('.flow-step-link[data-step=\"4\"]').on('click', function(e) {
            e.preventDefault();
            smoothScrollTo('#step4-form', 500);
            $('#step4-form').addClass('highlight-pulse');
            setTimeout(function() {
                $('#step4-form').removeClass('highlight-pulse');
            }, 1000);
        });
        
        // Paso 5 click
        $('.flow-step-link[data-step=\"5\"]').on('click', function(e) {
            e.preventDefault();
            smoothScrollTo('#step5-process', 500);
            $('#step5-process').addClass('highlight-pulse');
            setTimeout(function() {
                $('#step5-process').removeClass('highlight-pulse');
            }, 1000);
        });
    });
    ",
    View::POS_END
);
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

            <!-- FLOWCHART - Desktop View -->
            <div class="d-none d-md-block">
                <div class="row no-gutters mb-4">
                    <!-- Paso 1 - Clickable -->
                    <div class="col-md-2">
                        <div class="flow-step step-1 text-center p-3 flow-step-link" data-step="1" style="cursor: pointer;">
                            <div class="step-header">
                                <div class="step-number mx-auto">1</div>
                                <div class="step-icon-corner">
                                    <i class="fas fa-download"></i>
                                </div>
                            </div>
                            <h6 class="font-weight-bold mb-1 mt-3">Descargar Plantillas</h6>
                            <p class="small text-muted mb-0">Obtenga los recursos necesarios</p>
                            <div class="click-hint mt-2">
                                <small class="text-primary"><i class="fas fa-hand-pointer"></i> Click aquí</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 d-flex align-items-center justify-content-center">
                        <i class="fas fa-arrow-right fa-2x text-primary"></i>
                    </div>
                    <!-- Paso 2 - Clickable -->
                    <div class="col-md-2">
                        <div class="flow-step step-2 text-center p-3 flow-step-link" data-step="2" style="cursor: pointer;">
                            <div class="step-header">
                                <div class="step-number mx-auto">2</div>
                                <div class="step-icon-corner">
                                    <i class="fas fa-edit"></i>
                                </div>
                            </div>
                            <h6 class="font-weight-bold mb-1 mt-3">Preparar CSV</h6>
                            <p class="small text-muted mb-0">Complete con datos de afiliados</p>
                            <div class="click-hint mt-2">
                                <small class="text-primary"><i class="fas fa-hand-pointer"></i> Click aquí</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 d-flex align-items-center justify-content-center">
                        <i class="fas fa-arrow-right fa-2x text-primary"></i>
                    </div>
                    <!-- Paso 3 - Clickable -->
                    <div class="col-md-2">
                        <div class="flow-step step-3 text-center p-3 flow-step-link" data-step="3" style="cursor: pointer;">
                            <div class="step-header">
                                <div class="step-number mx-auto">3</div>
                                <div class="step-icon-corner">
                                    <i class="fas fa-search"></i>
                                </div>
                            </div>
                            <h6 class="font-weight-bold mb-1 mt-3">Validar IDs</h6>
                            <p class="small text-muted mb-0">Seleccione corporativo y consulte</p>
                            <div class="click-hint mt-2">
                                <small class="text-primary"><i class="fas fa-hand-pointer"></i> Click aquí</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 d-flex align-items-center justify-content-center">
                        <i class="fas fa-arrow-right fa-2x text-primary"></i>
                    </div>
                    <!-- Paso 4 - Clickable -->
                    <div class="col-md-2">
                        <div class="flow-step step-4 text-center p-3 flow-step-link" data-step="4" style="cursor: pointer;">
                            <div class="step-header">
                                <div class="step-number mx-auto">4</div>
                                <div class="step-icon-corner">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                            </div>
                            <h6 class="font-weight-bold mb-1 mt-3">Completar Formulario</h6>
                            <p class="small text-muted mb-0">Ingrese fechas y archivo</p>
                            <div class="click-hint mt-2">
                                <small class="text-primary"><i class="fas fa-hand-pointer"></i> Click aquí</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row no-gutters mt-2 justify-content-center">
                    <!-- Paso 5 - Clickable -->
                    <div class="col-md-2">
                        <div class="flow-step step-5 text-center p-3 flow-step-link" data-step="5" style="cursor: pointer;">
                            <div class="step-header">
                                <div class="step-number mx-auto">5</div>
                                <div class="step-icon-corner">
                                    <i class="fas fa-play"></i>
                                </div>
                            </div>
                            <h6 class="font-weight-bold mb-1 mt-3">Procesar Carga</h6>
                            <p class="small text-muted mb-0">Ejecute la carga masiva</p>
                            <div class="click-hint mt-2">
                                <small class="text-primary"><i class="fas fa-hand-pointer"></i> Click aquí</small>
                            </div>
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

            <!-- FLOWCHART - Mobile View -->
            <div class="d-block d-md-none">
                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="flow-step step-1 text-center p-3 flow-step-link" data-step="1" style="cursor: pointer;">
                            <div class="step-header">
                                <div class="step-number mx-auto">1</div>
                                <div class="step-icon-corner"><i class="fas fa-download"></i></div>
                            </div>
                            <h6 class="font-weight-bold mt-2 mb-0">Descargar Plantillas</h6>
                            <small class="text-primary"><i class="fas fa-hand-pointer"></i> Tap aquí</small>
                        </div>
                    </div>
                    <div class="col-12 text-center my-1"><i class="fas fa-arrow-down text-primary"></i></div>
                    <div class="col-12 mb-3">
                        <div class="flow-step step-2 text-center p-3 flow-step-link" data-step="2" style="cursor: pointer;">
                            <div class="step-header">
                                <div class="step-number mx-auto">2</div>
                                <div class="step-icon-corner"><i class="fas fa-edit"></i></div>
                            </div>
                            <h6 class="font-weight-bold mt-2 mb-0">Preparar CSV</h6>
                            <small class="text-primary"><i class="fas fa-hand-pointer"></i> Tap aquí</small>
                        </div>
                    </div>
                    <div class="col-12 text-center my-1"><i class="fas fa-arrow-down text-primary"></i></div>
                    <div class="col-12 mb-3">
                        <div class="flow-step step-3 text-center p-3 flow-step-link" data-step="3" style="cursor: pointer;">
                            <div class="step-header">
                                <div class="step-number mx-auto">3</div>
                                <div class="step-icon-corner"><i class="fas fa-search"></i></div>
                            </div>
                            <h6 class="font-weight-bold mt-2 mb-0">Validar IDs</h6>
                            <small class="text-primary"><i class="fas fa-hand-pointer"></i> Tap aquí</small>
                        </div>
                    </div>
                    <div class="col-12 text-center my-1"><i class="fas fa-arrow-down text-primary"></i></div>
                    <div class="col-12 mb-3">
                        <div class="flow-step step-4 text-center p-3 flow-step-link" data-step="4" style="cursor: pointer;">
                            <div class="step-header">
                                <div class="step-number mx-auto">4</div>
                                <div class="step-icon-corner"><i class="fas fa-calendar-alt"></i></div>
                            </div>
                            <h6 class="font-weight-bold mt-2 mb-0">Completar Formulario</h6>
                            <small class="text-primary"><i class="fas fa-hand-pointer"></i> Tap aquí</small>
                        </div>
                    </div>
                    <div class="col-12 text-center my-1"><i class="fas fa-arrow-down text-primary"></i></div>
                    <div class="col-12 mb-3">
                        <div class="flow-step step-5 text-center p-3 flow-step-link" data-step="5" style="cursor: pointer;">
                            <div class="step-header">
                                <div class="step-number mx-auto">5</div>
                                <div class="step-icon-corner"><i class="fas fa-play"></i></div>
                            </div>
                            <h6 class="font-weight-bold mt-2 mb-0">Procesar Carga</h6>
                            <small class="text-primary"><i class="fas fa-hand-pointer"></i> Tap aquí</small>
                        </div>
                    </div>
                    <div class="col-12 text-center my-2"><i class="fas fa-arrow-down text-success"></i></div>
                    <div class="col-12">
                        <div class="result-card-small text-center p-3 rounded">
                            <i class="fas fa-chart-line fa-2x text-white mb-1"></i>
                            <h6 class="font-weight-bold text-white mb-0">¡Completado!</h6>
                            <small class="text-white-50">Ver resumen</small>
                        </div>
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
                    [
                        'class' => 'btn btn-success mr-3 mb-2 px-4 py-2 shadow-sm',
                        'style' => 'font-size: 0.95rem;',
                        'title' => 'Descarga el formato CSV con todas las columnas'
                    ]
                ) ?>
                <?= Html::a(
                    '<i class="fas fa-map-marker-alt mr-2"></i> Catálogo de Estados',
                    ['/corporativo/descargar-catalogo-estados'],
                    [
                        'class' => 'btn btn-outline-primary mr-3 mb-2 px-4 py-2 shadow-sm',
                        'style' => 'font-size: 0.95rem;',
                        'title' => 'Descarga un CSV con la lista de estados válidos'
                    ]
                ) ?>
                <?= Html::a(
                    '<i class="fas fa-user-tie mr-2"></i> Catálogo de Asesores',
                    ['/corporativo/descargar-catalogo-asesores'],
                    [
                        'class' => 'btn btn-outline-secondary mb-2 px-4 py-2 shadow-sm',
                        'style' => 'font-size: 0.95rem;',
                        'title' => 'Descarga un CSV con la lista de asesores'
                    ]
                ) ?>
            </div>
            <div class="alert alert-info mt-3 mb-0">
                <i class="fas fa-lightbulb mr-2"></i>
                <strong>Consejo:</strong> Descargue la plantilla y los catálogos antes de comenzar. Esto le ayudará a preparar su archivo con los datos correctos.
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
                            <th class="align-middle" style="width: 18%;">Campo</th>
                            <th class="align-middle" style="width: 12%;">Tipo</th>
                            <th class="align-middle">Descripción y Formato</th>
                            <th class="align-middle" style="width: 22%;">Ejemplo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="table-secondary">
                            <td colspan="4" class="font-weight-bold">📌 CAMPOS REQUERIDOS (11 columnas obligatorias)
                        <tr>
                        </tr>
                        <tr>
                            <td><code>tipo_cedula</code></td>
                            <td>Texto</td>
                            <td>Tipo de documento de identidad</td>
                            <td>V, E, P, J</td>
                        </tr>
                        <tr>
                            <td><code>cedula</code></td>
                            <td>Numérico</td>
                            <td>Solo números, sin letras ni guiones</td>
                            <td>19088456</td>
                        </tr>
                        <tr>
                            <td><code>nombres</code></td>
                            <td>Texto</td>
                            <td>Nombres completos del afiliado</td>
                            <td>JUAN PABLO</td>
                        </tr>
                        <tr>
                            <td><code>apellidos</code></td>
                            <td>Texto</td>
                            <td>Apellidos completos del afiliado</td>
                            <td>ROJAS PEREZ</td>
                        </tr>
                        <tr>
                            <td><code>fechanac</code></td>
                            <td>Fecha</td>
                            <td>Formato estricto: AAAA-MM-DD</td>
                            <td>1990-05-15</td>
                        </tr>
                        <tr>
                            <td><code>sexo</code></td>
                            <td>Texto</td>
                            <td>Género del afiliado</td>
                            <td>Masculino / Femenino</td>
                        </tr>
                        <tr>
                            <td><code>telefono</code></td>
                            <td>Texto</td>
                            <td>11 dígitos (código de área + número)</td>
                            <td>04121234567</td>
                        </tr>
                        <tr>
                            <td><code>email</code></td>
                            <td>Email</td>
                            <td>Debe ser único en el sistema</td>
                            <td>juan@ejemplo.com</td>
                        </tr>
                        <tr>
                            <td><code>direccion</code></td>
                            <td>Texto</td>
                            <td>Dirección de residencia</td>
                            <td>Calle Principal #123</td>
                        </tr>
                        <tr class="table-danger">
                            <td><code>plan_id</code></td>
                            <td>Número</td>
                            <td>ID del Plan (ver Paso 3)</td>
                            <td class="font-weight-bold text-danger">2</td>
                        </tr>
                        <tr class="table-danger">
                            <td><code>clinica_id</code></td>
                            <td>Número</td>
                            <td>ID de la Clínica (ver Paso 3)</td>
                            <td class="font-weight-bold text-danger">2</td>
                        </tr>
                        <tr>
                            <td><code>estado</code></td>
                            <td>Texto</td>
                            <td>Estado (ver catálogo descargable)</td>
                            <td>MIRANDA</td>
                        </tr>

                        <tr class="table-secondary">
                            <td colspan="4" class="font-weight-bold">📋 CAMPOS OPCIONALES (Para enriquecer la información)</td>
                        </tr>
                        <tr>
                            <td><code>asesor_id</code></td>
                            <td>Número</td>
                            <td>ID del asesor que gestiona el afiliado</td>
                            <td>5</td>
                        </tr>
                        <tr>
                            <td><code>direccion_oficina</code></td>
                            <td>Texto</td>
                            <td>Dirección laboral del afiliado</td>
                            <td>Av. Principal, Torre B</td>
                        </tr>
                        <tr>
                            <td><code>telefono_oficina</code></td>
                            <td>Texto</td>
                            <td>Teléfono laboral (11 dígitos)</td>
                            <td>02125551234</td>
                        </tr>
                        <tr>
                            <td><code>tipo_sangre</code></td>
                            <td>Texto</td>
                            <td>Tipo sanguíneo</td>
                            <td>A+, O-</td>
                        </tr>
                        <tr>
                            <td><code>nacionalidad</code></td>
                            <td>Texto</td>
                            <td>País de origen</td>
                            <td>Venezolano</td>
                        </tr>
                        <tr>
                            <td><code>estado_civil</code></td>
                            <td>Texto</td>
                            <td>Estado civil</td>
                            <td>Casado</td>
                        </tr>
                        <tr>
                            <td><code>profesion</code></td>
                            <td>Texto</td>
                            <td>Profesión del afiliado</td>
                            <td>Ingeniero</td>
                        </tr>
                        <tr>
                            <td><code>ingreso_anual</code></td>
                            <td>Texto</td>
                            <td>Rango de ingreso anual</td>
                            <td>De 6 a 10 Salarios mínimos</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-3 bg-light border-top">
                <i class="fas fa-check-circle text-success mr-2"></i>
                <strong>Validaciones importantes:</strong>
                <span class="text-muted ml-2">• Fechas en formato YYYY-MM-DD • Teléfonos de 11 dígitos • Cédula sin letras ni guiones • Email único</span>
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
            <!-- Selector de Corporativo dentro del Paso 3 -->
            <div class="form-group mb-4 p-3 bg-light rounded">
                <label class="font-weight-bold text-primary mb-2">
                    <i class="fas fa-building mr-2"></i>Seleccione el Corporativo para ver sus clínicas y planes:
                </label>
                <?= Html::dropDownList(
                    'corporativo_selector',
                    isset($model->corporativo_id) ? $model->corporativo_id : null,
                    $corporativos,
                    [
                        'id' => 'corporativo-selector',
                        'prompt' => '--- Seleccione el Corporativo Destino ---',
                        'class' => 'form-control form-control-lg',
                        'style' => 'max-width: 500px;'
                    ]
                ) ?>
                <small class="text-muted mt-2 d-block">
                    <i class="fas fa-info-circle mr-1"></i>
                    Al seleccionar un corporativo, se mostrarán las clínicas asociadas y sus planes. <strong>Los IDs que aparecen son los que debe usar en su archivo CSV.</strong>
                </small>
            </div>

            <!-- Resultados dinámicos de clínicas y planes -->
            <div id="clinicas-asociadas-info">
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle mr-2"></i>
                    Seleccione un corporativo arriba para ver las clínicas asociadas y los IDs de planes disponibles.
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- PASO 4: COMPLETAR FORMULARIO                  -->
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

            <!-- Campo oculto para enviar el corporativo_id al formulario -->
            <?= $form->field($model, 'corporativo_id')->hiddenInput(['id' => 'corporativo-id-hidden'])->label(false) ?>

            <!-- Fechas -->
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
                        ])->label('Fecha de Vencimiento (auto-calculada)') ?>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="alert alert-info p-2 mb-3 w-100 text-center">
                            <small><i class="fas fa-sync-alt mr-1"></i> +1 año</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Archivo CSV -->
            <div class="form-group mb-4">
                <label class="font-weight-bold text-warning">
                    <i class="fas fa-file-csv mr-2"></i>Archivo CSV
                </label>
                <?= $form->field($model, 'masivoFile')->fileInput([
                    'class' => 'form-control form-control-lg',
                    'accept' => '.csv'
                ])->label(false) ?>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="fas fa-file-csv mr-1"></i> Formatos permitidos: CSV
                        </small>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="fas fa-database mr-1"></i> Tamaño máximo: 5MB
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- PASO 5: PROCESAR CARGA                        -->
    <!-- ============================================ -->
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
                    'data-confirm' => 'ADVERTENCIA: ¿Está seguro de que desea iniciar la carga masiva? Esto creará nuevos usuarios, contratos y 12 cuotas por cada afiliado en el sistema.'
                ]
            ) ?>

            <hr class="my-4">

            <!-- ¿Qué esperar? -->
            <div class="row text-left mt-3">
                <div class="col-md-12">
                    <h6 class="font-weight-bold text-success mb-3">
                        <i class="fas fa-chart-line mr-2"></i>¿Qué esperar después del procesamiento?
                    </h6>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <i class="fas fa-file-csv fa-2x mb-2 text-info"></i>
                        <p class="small mb-0"><strong>Validación</strong><br>Cada registro es verificado</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <i class="fas fa-user-check fa-2x mb-2 text-success"></i>
                        <p class="small mb-0"><strong>Creación</strong><br>Usuario + Afiliado + Contrato</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <i class="fas fa-coins fa-2x mb-2 text-warning"></i>
                        <p class="small mb-0"><strong>12 Cuotas</strong><br>Cobertura por 1 año completo</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <i class="fas fa-chart-bar fa-2x mb-2 text-primary"></i>
                        <p class="small mb-0"><strong>Reporte</strong><br>Éxitos y errores detallados</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<!-- ============================================ -->
<!-- ESTILOS CSS PERSONALIZADOS - BOOTSTRAP 4     -->
<!-- ============================================ -->
<style>
    /* Gradientes para cada paso */
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

    /* Flow Step Cards */
    .flow-step {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: 1px solid #e3e6f0;
        height: 100%;
        position: relative;
    }

    .flow-step:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        background: #f8f9fc;
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

    .step-header {
        position: relative;
    }

    .step-number {
        width: 32px;
        height: 32px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1rem;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin: 0 auto;
    }

    .step-icon-corner {
        position: absolute;
        top: -5px;
        right: 5px;
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, 0.05);
    }

    .step-icon-corner i {
        font-size: 0.9rem;
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

    .step-1 .step-icon-corner i {
        color: #4e73df;
    }

    .step-2 .step-icon-corner i {
        color: #1cc88a;
    }

    .step-3 .step-icon-corner i {
        color: #36b9cc;
    }

    .step-4 .step-icon-corner i {
        color: #f6c23e;
    }

    .step-5 .step-icon-corner i {
        color: #e74a3b;
    }

    .click-hint {
        opacity: 0.6;
        transition: opacity 0.2s ease;
    }

    .flow-step:hover .click-hint {
        opacity: 1;
    }

    /* Step badges en encabezados */
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

    /* Result card small */
    .result-card-small {
        background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        box-shadow: 0 5px 15px rgba(28, 200, 138, 0.3);
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
        font-size: 0.85em;
        color: #e83e8c;
    }

    /* Badge danger pill */
    .badge-danger {
        background-color: #e74a3b;
        font-size: 0.9rem;
        padding: 5px 12px;
    }

    /* Highlight Pulse Animation */
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

    /* Responsive */
    @media (max-width: 768px) {
        .display-4 {
            font-size: 1.8rem;
        }

        .step-icon-corner {
            top: -2px;
            right: 2px;
        }

        .click-hint {
            font-size: 0.7rem;
        }
    }
</style>

<script>
    // Sincronizar el selector del Paso 3 con el campo oculto del formulario
    $(document).ready(function() {
        $('#corporativo-selector').on('change', function() {
            $('#corporativo-id-hidden').val($(this).val());
        });

        // Si hay un valor inicial, sincronizarlo
        if ($('#corporativo-selector').val()) {
            $('#corporativo-id-hidden').val($('#corporativo-selector').val());
        }

        // Add cursor pointer to all flow-step-link elements
        $('.flow-step-link').css('cursor', 'pointer');
    });
</script>

<?php
$this->registerJs(
    <<<JS
function calcularFechaVencimiento(fechaIni) {
  if (fechaIni) {
    var parts = fechaIni.split('-');
    var year = parseInt(parts[0]);
    var month = parseInt(parts[1]) - 1;
    var day = parseInt(parts[2]);
    var fecha = new Date(year, month, day);
    fecha.setFullYear(fecha.getFullYear() + 1);
    var newYear = fecha.getFullYear();
    var newMonth = String(fecha.getMonth() + 1).padStart(2, '0');
    var newDay = String(fecha.getDate()).padStart(2, '0');
    return newYear + '-' + newMonth + '-' + newDay;
  }
  return '';
}
function toggleFechaVen() {
  var fechaIni = $('.fecha-ini-field').val();
  var fechaVenContainer = $('.fecha-ven-container');
  if (fechaIni) {
    fechaVenContainer.show();
    var fechaVen = calcularFechaVencimiento(fechaIni);
    $('.fecha-ven-field').val(fechaVen);
  } else {
    fechaVenContainer.hide();
    $('.fecha-ven-field').val('');
  }
}
$(function() {
  toggleFechaVen();
  $('.fecha-ini-field').on('change', function() {
    toggleFechaVen();
  });
});
JS,
    View::POS_END
);
?>