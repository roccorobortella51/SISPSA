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
            infoDiv.html('<p class=\"alert alert-info\">Seleccione un corporativo abajo para ver las clínicas asociadas.</p>');
            return;
        }

        infoDiv.html('<div class=\"text-center py-3\"><i class=\"fas fa-spinner fa-spin me-2\"></i> Cargando clínicas y planes asociados...</div>');

        $.ajax({
            url: clinicasPlanesUrl,
            type: 'GET',
            data: { id: corporativoId },
            dataType: 'json',
            success: function(response) {
                // Manejamos la respuesta con la estructura { success: true, data: [...] }
                if (response.success && response.data.length > 0) {
                    
                    let html = '<h3 class=\"text-primary mb-4\"><i class=\"fas fa-check-double me-2\"></i> IDs Válidos para su Plantilla CSV:</h5>';
                    
                    response.data.forEach(function(clinica) {
                        // --- INICIO DE MEJORA: Tarjeta para cada Clínica ---
                        html += '<div class=\"card border-info mb-4 shadow-lg\">'; // Usamos border-info
                        
                        // Encabezado mejorado para resaltar el ID de la Clínica
                        html += '<div class=\"card-header bg-info text-white fw-bold d-flex justify-content-between align-items-center\">';
                        html += '<div><i class=\"fas fa-hospital-alt me-2\"></i> Clínica: ' + clinica.nombre + '</div>';
                        // ID de la Clínica con un badge grande y amarillo para máxima visibilidad
                        html += '<span class=\"badge bg-warning text-dark fs-5 py-2 px-3 fw-bolder\">ID CLÍNICA: ' + clinica.id + '</span>'; 
                        html += '</div>';
                        
                        // Cuerpo de la tarjeta para los planes
                        if (clinica.planes && clinica.planes.length > 0) {
                            html += '<div class=\"card-body p-3\">';
                            html += '<h5 class=\"text-dark ms-2 mt-2\">Planes de esta Clínica (IDs para `plan_id` en CSV):</h5>';
                            html += '<table class=\"table table-bordered table-sm fs-6\">';
                            html += '<thead class=\"bg-light\"><tr><th style=\"width: 30%;\">ID del Plan</th><th>Nombre del Plan</th></tr></thead>';
                            html += '<tbody>';

                            clinica.planes.forEach(function(plan) {
                                html += '<tr>';
                                // ID del Plan resaltado en negrita y color rojo de peligro para diferenciarlo
                                html += '<td class=\"fw-bolder text-danger align-middle\">' + plan.id + '</td>'; 
                                html += '<td>' + plan.nombre + '</td>';
                                html += '</tr>';
                            });

                            html += '</tbody></table>';
                            html += '</div>'; // card-body
                        } else {
                            html += '<div class=\"card-body\"><p class=\"alert alert-warning mb-0\">No hay planes activos asociados a esta clínica (' + clinica.nombre + ').</p></div>';
                        }
                        
                        html += '</div>'; // card
                        // --- FIN DE MEJORA ---
                    });
                    
                    infoDiv.html(html);

                } else if (response.success && response.data.length === 0) {
                    infoDiv.html('<p class=\"alert alert-warning\">No se encontraron clínicas asociadas para el corporativo seleccionado.</p>');
                } else if (response.error) {
                     // Manejo de errores detallado si el controlador falla
                     infoDiv.html('<p class=\"alert alert-danger\">Error del Servidor: ' + response.error + '</p>');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                infoDiv.html('<p class=\"alert alert-danger\">Error de Conexión. Asegúrese de que el ID del corporativo es válido.</p>');
            }
        });
    }

    // Escucha el cambio en el selector del corporativo
    $('#masivoafiliadosform-corporativo_id').on('change', function() {
        cargarClinicasPlanes($(this).val());
    });

    // Carga inicial (si ya hay un valor seleccionado al cargar la página)
    cargarClinicasPlanes($('#masivoafiliadosform-corporativo_id').val());

    ",
    View::POS_END 
);
?>



    <!-- Encabezado y Acción de Descarga -->
<div class="d-flex flex-column mb-5 border-bottom pb-4" style="margin-left: 60px;">
    
    <h1 class="display-4 font-weight-bold mb-5">
        <i class="fas fa-layer-group me-2"></i> <?= Html::encode($this->title) ?>
    </h1>


    <div class="d-flex flex-wrap">
        <?= Html::a(
            '<i class="fas fa-file-download me-2"></i> Descargar Plantilla CSV de Ejemplo',
            ['/corporativo/descargar-plantilla'], 
            [
                'class' => 'btn btn-success btn-lg fw-bold shadow-lg btn-fixed-success',
                'style' => 'font-size: 1.2rem !important; padding: 12px 20px !important; margin-right: 30px; margin-bottom: 20px;', 
                'title' => 'Descarga el formato CSV con todas las columnas'
            ]
        ) ?>
        <?= Html::a(
            '<i class="fas fa-list-ul me-2"></i> Descargar Catálogo de Estados',
            ['/corporativo/descargar-catalogo-estados'],
            [
                'class' => 'btn btn-outline-primary btn-lg fw-bold shadow-sm btn-fixed-primary',
                'style' => 'font-size: 1.2rem !important; padding: 12px 20px !important; margin-right: 30px; margin-bottom: 20px;',
                'title' => 'Descarga un CSV con la lista de estados válidos'
            ]
        ) ?>
        <?= Html::a(
            '<i class="fas fa-user-tie me-2"></i> Descargar Catálogo de Asesores',
            ['/corporativo/descargar-catalogo-asesores'],
            [
                'class' => 'btn btn-outline-secondary btn-lg fw-bold shadow-sm btn-fixed-secondary',
                'style' => 'font-size: 1.2rem !important; padding: 12px 20px !important; margin-right: 30px; margin-bottom: 20px;',
                'title' => 'Descarga un CSV con la lista de asesores'
            ]
        ) ?>
    </div>
</div>

    <!-- Guía de Instrucciones (Tarjeta Azul) -->
    <div class="card bg-light border-primary mb-5 shadow-sm">
        <div class="card-body">
            <h3 class="card-title text-primary mb-3"><i class="fas fa-info-circle me-2"></i> Instrucciones y Formato Requerido</h3>
            <hr>
            <p>
                Al procesar la carga, el sistema automáticamente creará el Contrato y la Cuota Inicial para cada afiliado. Utilice la plantilla oficial (botón verde) para asegurar el orden y la cabecera correcta de las columnas.
            </p>
            
            <!-- Tabla de Campos -->
            <table class="table table-bordered table-sm mt-3">
                <thead class="bg-info text-white">
                    <tr>
                        <th style="width: 25%;">Campo</th>
                        <th style="width: 25%;">Tipo</th>
                        <th>Descripción y Formato</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="table-primary fw-bold">
                        <td colspan="3">CAMPOS REQUERIDOS (Obligatorios para el registro)</td>
                    </tr>
                    <tr><td>`tipo_cedula`</td><td>Texto</td><td>Tipo de documento de identidad (ej: V, E, P).</td></tr>
                    <tr><td>`cedula`</td><td>Numeral</td><td>Número de cédula o documento.</td></tr>
                    <tr><td>`nombres`</td><td>Texto</td><td>Nombres completos del afiliado.</td></tr>
                    <tr><td>`apellidos`</td><td>Texto</td><td>Apellidos completos del afiliado.</td></tr>
                    <tr><td>`fechanac`</td><td>Fecha</td><td>Fecha de nacimiento. Formato estricto: YYYY-MM-DD (ej: 1990-05-15 / año/mes/dia).</td></tr>
                    <tr><td>`sexo`</td><td>Texto</td><td>Género (Masculino o Femenino).</td></tr>
                    <tr><td>`telefono`</td><td>Texto</td><td>Teléfono de contacto (residencia o móvil).</td></tr>
                    <tr><td>`email`</td><td>Texto</td><td>Correo electrónico. (Debe ser único en el sistema)</td></tr>
                    <tr><td>`direccion`</td><td>Texto</td><td>Dirección de residencia o cobro.</td></tr>
                    <tr class="table-danger">
                        <td>`plan_id`</td>
                        <td>Número</td>
                        <td>ID del Plan al que se afiliará. (Debe ser un plan listado en la sección de guía)</td>
                    </tr>
                    <tr class="table-danger">
                        <td>`clinica_id`</td>
                        <td>Número</td>
                        <td>ID de la Clínica a la que se vinculará el contrato.(Debe ser una clínica listada en la sección de guía)</td>
                    </tr>
                    
                    <tr class="table-secondary fw-bold">
                        <td colspan="3">CAMPOS OPCIONALES (Para información de contrato y oficina)</td>
                    </tr>
                    <tr><td>`asesor_id`</td><td>Número</td><td>ID del asesor (si aplica y podra descargarse en el boton de descarga).</td></tr>
                     <tr><td>`Estado`</td><td>Texto</td><td>Estado de afiliacion (podra descargarse en el boton de descarga).</td></tr>
                    <tr><td>`direccion_oficina`</td><td>Texto</td><td>Dirección de la oficina del afiliado.</td></tr>
                    <tr><td>`telefono_oficina`</td><td>Texto</td><td>Teléfono de la oficina del afiliado.</td></tr>
                    <tr><td>`tipo_sangre`</td><td>Texto</td><td>Tipo de sangre (ej: A+, O-).</td></tr>
                    <tr><td>`nacionalidad`</td><td>Texto</td><td>Nacionalidad de origen  (ej: Venezolano o Extranjero).</td></tr>
                    <tr><td>`estado_civil`</td><td>Texto</td><td>Soltero, Casado, Divorciado o Viudo</td></tr>
                    <tr><td>`Lugar_nacimiento`</td><td>Texto</td><td>Escriba un estado por ejemplo</td></tr>
                    <tr><td>`profesion`</td><td>Texto</td><td>Profesión del afiliado.</td></tr>
                    <tr><td>`ocupacion`</td><td>Texto</td><td>Ocupación del afiliado.</td></tr>
                    <tr><td>`actividad_económica`</td><td>Texto</td><td>Actividad económica del afiliado.(ej: Industrial, Comercial, Gubernamental)</td></tr>
                    <tr><td>`ramo_comercial`</td><td>Texto</td><td>Descripción del ramo comercial donde trabaja el afiliado.</td></tr>
                    <tr><td>`Descripcion_actividad`</td><td>Texto</td><td>Descripción de la actividad económica del afiliado.(ej: Independiente, Dependiente,Societaria)</td></tr>
                    <tr><td>`ingreso_anual`</td><td>Texto</td><td>Ingreso anual del afiliado.(ej: De 1 a 5 Salarios mínimos, De 6 a 10 Salarios mínimos, De 11 a 20 Salarios mínimos, De 20 Salarios mínimos en adelante)</td></tr>
                    <tr><td>`dirección_cobro`</td><td>Texto</td><td>Dirección de cobro del afiliado.</td></tr>
                    <tr><td>`telefono_residencia`</td><td>Texto</td><td>Telefono de Residencia del afiliado.(ej: Independiente, Dependiente,Societaria)</td></tr>


                </tbody>
            </table>

            <p class="mt-4 alert alert-warning mb-0">
                VALIDACIÓN CRÍTICA (Sistema): El sistema verificará que el `clinica_id` proporcionado en el CSV esté correctamente vinculado al Corporativo Destino que usted seleccione en el formulario.
            </p>
        </div>
    </div>

    <!-- Sección de Ayuda Dinámica de Clínicas y Planes -->
    <div class="card shadow-sm border-info mt-4 mb-5">
        <div class="card-header bg-info text-white">
            <h4 class="mb-0"><i class="fas fa-hospital-alt me-2"></i> Guía de IDs de Clínica y Plan por Corporativo</h4>
        </div>
        <div class="card-body" id="clinicas-asociadas-info">
            <p class="alert alert-info">Seleccione un corporativo en el paso 1 del proceso de carga para ver los ID de clínica y plan válidos que debe usar en su plantilla CSV.</p>
        </div>
    </div>
    <!-- Fin de Sección de Ayuda Dinámica de Clínicas -->


    <!-- Formulario de Carga (Tarjeta de Éxito) -->
    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data'],
        'id' => 'carga-masiva-form'
    ]); ?>

   <div class="card shadow-lg border-success">
    <div class="card-header bg-success text-white">
        <h4 class="mb-0"><i class="fas fa-cloud-upload-alt me-2"></i> Proceso de Carga</h4>
    </div>
    
    <div class="card-body">
        
        <?= $form->field($model, 'corporativo_id')->dropDownList(
            $corporativos,
            ['prompt' => '--- Seleccione el Corporativo Destino para los afiliados ---', 'class' => 'form-control form-select-lg']
        )->label('<i class="fas fa-building me-2"></i> 1. Corporativo Destino', [
            'class' => 'fw-bold text-dark mb-2',
            'style' => 'padding-left: 8px;', // Alineación manual solicitada
            'encode' => false
        ]) ?>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'fecha_ini')->textInput([
                    'class' => 'form-control form-control-lg fecha-ini-field',
                    'type' => 'date',
                    'required' => true,
                ])->label('<i class="fas fa-calendar-alt me-2"></i> 2. Fecha de Inicio de contratos', [
                    'class' => 'fw-bold text-dark mb-2',
                    'style' => 'padding-left: 0px!important;', // Alineación manual solicitada
                    'encode' => false
                ]) ?>
            </div>
            <div class="col-md-6 fecha-ven-container" style="display: none;">
                <?= $form->field($model, 'fecha_ven')->textInput([
                    'class' => 'form-control form-control-lg fecha-ven-field',
                    'type' => 'date',
                    'required' => true,
                ])->label('<i class="fas fa-calendar-check me-2"></i> 2.1. Fecha de Vencimiento de contratos', [
                    'class' => 'fw-bold text-dark mb-2',
                    'style' => 'padding-left: 8px;', // Alineación manual solicitada
                    'encode' => false
                ]) ?>
            </div>
        </div>
        
        <?= $form->field($model, 'masivoFile')->fileInput([
            'class' => 'form-control form-control-lg'
        ])->label('<i class="fas fa-file-csv me-2"></i> 3. Seleccionar Archivo CSV', [
            'class' => 'fw-bold text-dark mb-2',
            'style' => 'padding-left: 8px;', // Alineación manual solicitada
            'encode' => false
        ])->hint('El archivo debe ser CSV y no debe superar los 5MB de tamaño.') ?>

        <div class="form-group pt-4 text-center">
            <?= Html::submitButton('<i class="fas fa-paper-plane me-2"></i> Procesar Carga', [
                'class' => 'btn btn-success btn-xl shadow-sm',
                'style' => 'font-size: 1.2rem !important; padding: 12px 20px !important; margin-right: 30px; margin-bottom: 20px;',
                'data-confirm' => 'ADVERTENCIA: ¿Está seguro de que desea iniciar la carga masiva? Esto creará nuevos usuarios, contratos y cuotas en el sistema.'
            ]) ?>
        </div>
    </div>
</div>

    <?php ActiveForm::end(); ?>


<?php
$this->registerJs(<<<JS
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
JS
, View::POS_END);
?>