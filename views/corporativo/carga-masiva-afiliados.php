<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $model app\models\MasivoAfiliadosForm */
/* @var $corporativos array Lista de corporativos activos */

$this->title = 'Gestión: Carga Masiva de Afiliados Corporativos';
$this->params['breadcrumbs'][] = $this->title;

// Asumimos que el tema de Yii o Bootstrap ya maneja iconos (fas) y clases de diseño.
// Registramos el script de JavaScript para manejar la funcionalidad de las clínicas.
$this->registerJs(
    "
    // URL para obtener las clínicas, debes crear esta acción en tu controlador.
    const clinicasUrl = " . json_encode(Yii::$app->urlManager->createUrl(['corporativo/obtener-clinicas-por-corporativo'])) . ";

    // Función que carga las clínicas vía AJAX
    function cargarClinicas(corporativoId) {
        const infoDiv = $('#clinicas-asociadas-info');
        
        if (!corporativoId) {
            infoDiv.html('<p class=\"alert alert-info\">Seleccione un corporativo arriba para ver las clínicas asociadas.</p>');
            return;
        }

        infoDiv.html('<div class=\"text-center py-3\"><i class=\"fas fa-spinner fa-spin me-2\"></i> Cargando clínicas asociadas...</div>');

        $.ajax({
            url: clinicasUrl,
            type: 'GET',
            data: { id: corporativoId },
            dataType: 'json',
            success: function(data) {
                if (data.length > 0) {
                    let html = '<h3 class=\"text-success mb-3\"><i class=\"fas fa-notes-medical me-2\"></i> Clínicas Asociadas (ID Válidos para CSV):</h5>';
                    html += '<table class=\"table table-striped fs-5\">';
                    html += '<thead><tr><th>ID</th><th>Nombre de la Clínica</th></tr></thead>';
                    html += '<tbody>';
                    
                    data.forEach(function(clinica) {
                        // Asumimos que la respuesta JSON tiene 'id' y 'nombre'
                        html += '<tr><td class=\"fw-bold text-success align-middle text-center\">' + clinica.id + '</td><td>' + clinica.nombre + '</td></tr>';
                    });

                    html += '</tbody></table>';
                    infoDiv.html(html);
                } else {
                    infoDiv.html('<p class=\"alert alert-warning\">No se encontraron clínicas asociadas para el corporativo seleccionado.</p>');
                }
            },
            error: function() {
                infoDiv.html('<p class=\"alert alert-danger\">Error al cargar las clínicas. Por favor, intente de nuevo.</p>');
            }
        });
    }

    // Escucha el cambio en el selector del corporativo
    $('#masivoafiliadosform-corporativo_id').on('change', function() {
        cargarClinicas($(this).val());
    });

    // Carga inicial (si ya hay un valor seleccionado al cargar la página)
    cargarClinicas($('#masivoafiliadosform-corporativo_id').val());

    ",
    View::POS_END // Coloca el script al final del cuerpo (body)
);
?>

<div class="corporativo-carga-masiva-form p-4">

    <!-- Encabezado y Acción de Descarga -->
    <div class="d-flex justify-content-between align-items-center mb-5 border-bottom pb-3">
        <h1 class="text-primary font-weight-bold"><i class="fas fa-layer-group me-2"></i> <?= Html::encode($this->title) ?></h1>
        <?= Html::a(
            '<i class="fas fa-file-download me-2"></i> Descargar Plantilla CSV de Ejmplo',
            ['/corporativo/descargar-plantilla'], 
            [
                'class' => 'btn btn-success fw-bold py-3 px-5 shadow-xl', // Botón principal de descarga
                'title' => 'Descarga el formato CSV con todas las columnas'
            ]
        ) ?>
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
                    <tr><td>`sexo`</td><td>Texto</td><td>Género (M o F).</td></tr>
                    <tr><td>`telefono`</td><td>Texto</td><td>Teléfono de contacto (residencia o móvil).</td></tr>
                    <tr><td>`email`</td><td>Texto</td><td>Correo electrónico. (Debe ser único en el sistema)</td></tr>
                    <tr><td>`direccion`</td><td>Texto</td><td>Dirección de residencia o cobro.</td></tr>
                    <tr><td>`plan_id`</td><td>Número</td><td>ID del Plan al que se afiliará.</td></tr>
                    <tr class="table-danger">
                        <td>`clinica_id`</td>
                        <td>Número</td>
                        <td>ID de la Clínica a la que se vinculará el contrato. Observacion: Debe estar vinculada al Corporativo Destino.**</td>
                    </tr>
                    
                    <tr class="table-secondary fw-bold">
                        <td colspan="3">CAMPOS OPCIONALES (Para información de contrato y oficina)</td>
                    </tr>
                    <tr><td>`asesor_id`</td><td>Número</td><td>ID del asesor (si aplica).</td></tr>
                    <tr><td>`fecha_inicio_contrato`</td><td>Fecha</td><td>Fecha de inicio de vigencia del Contrato. Si está vacío, usa la fecha de hoy. Formato: YYYY-MM-DD.</td></tr>
                    <tr><td>`fecha_vencimiento_contrato`</td><td>Fecha</td><td>Fecha de vencimiento del Contrato. Si está vacío, puede quedar nulo en el sistema. Formato: YYYY-MM-DD.</td></tr>
                    <tr><td>`direccion_oficina`</td><td>Texto</td><td>Dirección de la oficina del afiliado.</td></tr>
                    <tr><td>`telefono_oficina`</td><td>Texto</td><td>Teléfono de la oficina del afiliado.</td></tr>
                    <tr><td>`tipo_sangre`</td><td>Texto</td><td>Tipo de sangre (ej: A+, O-).</td></tr>
                    <tr><td>`rol_en_corporativo`</td><td>Texto</td><td>Posición o rol del afiliado dentro de la empresa.</td></tr>
                    <tr><td>`nacionalidad`</td><td>Texto</td><td>Nacionalidad de origen  (ej: Venezolano o Extranjero).</td></tr>
                    <tr><td>`estado_civil`</td><td>Texto</td><td>Soltero, Casado, Divorciado o Viudo</td></tr>
                    <tr><td>`Lugar_nacimiento`</td><td>Texto</td><td>Escriba un estado por ejemplo</td></tr>
                    <tr><td>`profesion`</td><td>Texto</td><td>Profesión del afiliado.</td></tr>
                    <tr><td>`ocupacion`</td><td>Texto</td><td>Ocupación del afiliado.</td></tr>
                    <tr><td>`actividad_económica`</td><td>Texto</td><td>Actividad económica del afiliado.(ej: Industrial, Comercial, Gubernamental)</td></tr>
                    <tr><td>`ramo_comercial`</td><td>Texto</td><td>Descripción de la actividad económica del afiliado.(ej: Independiente, Dependiente,Societaria)</td></tr>
                    <tr><td>`ingreso_anual`</td><td>Texto</td><td>Ingreso anual del afiliado.(ej: De 1 a 5 Salarios mínimos, De 6 a 10 Salarios mínimos, De 11 a 20 Salarios mínimos, De 20 Salarios mínimos en adelante)</td></tr>
                    <tr><td>`dirección_cobro`</td><td>Texto</td><td>Dirección de cobro del afiliado.</td></tr>
                    <tr><td>`telefono_residencia`</td><td>Texto</td><td>Telefono de Residencia del afiliado.(ej: Independiente, Dependiente,Societaria)</td></tr>


                </tbody>
            </table>

            <p class="mt-4 alert alert-warning mb-0">
                VALIDACIÓN CRÍTICA (Sistema): El sistema verificará que el `clinica_id` proporcionado en el CSV esté correctamente vinculado al Corporativo Destino que usted seleccione en el formulario. Si no hay vínculo, la fila será rechazada.
            </p>
        </div>
    </div>

    <!-- Sección de Ayuda Dinámica de Clínicas (NUEVO) -->
    <div class="card shadow-sm border-info mt-4 mb-5">
        <div class="card-header bg-info text-white">
            <h4 class="mb-0"><i class="fas fa-hospital-alt me-2"></i> Guía de IDs de Clínica por Corporativo</h4>
        </div>
        <div class="card-body" id="clinicas-asociadas-info">
            <p class="alert alert-info">Seleccione un corporativo en el paso 1 del proceso de carga para ver los ID de clínica válidos que debe usar en su plantilla CSV.</p>
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
            
            <!-- 1. Campo de Selección de Corporativo -->
            <?= $form->field($model, 'corporativo_id')->dropDownList(
                $corporativos,
                ['prompt' => '--- Seleccione el Corporativo Destino para los afiliados ---', 'class' => 'form-control form-select-lg']
            )->label('<span class="fw-bold text-dark"><i class="fas fa-building me-2"></i> 1. Corporativo Destino</span>') ?>
            
            <!-- 2. Campo de Subida de Archivo -->
            <?= $form->field($model, 'masivoFile')->fileInput([
                'class' => 'form-control form-control-lg'
            ])->label('<span class="fw-bold text-dark"><i class="fas fa-file-csv me-2"></i> 2. Seleccionar Archivo CSV</span>')->hint('El archivo debe ser CSV y no debe superar los 5MB de tamaño.') ?>

            <div class="form-group pt-4 text-center">
                <?= Html::submitButton('<i class="fas fa-paper-plane me-2"></i> Procesar Carga', [
                    'class' => 'btn btn-primary btn-xl shadow-sm', // Botón de acción principal
                    'data-confirm' => 'ADVERTENCIA: ¿Está seguro de que desea iniciar la carga masiva? Esto creará nuevos usuarios, contratos y cuotas en el sistema.'
                ]) ?>
            </div>

        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>