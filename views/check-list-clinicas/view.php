<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\CheckListClinicas */

$this->title = 'DETALLES DE LA VERIFICACIÓN DE LA CLÍNICA: ' . $model->clinica->nombre;
$this->params['breadcrumbs'][] = ['label' => 'LISTA DE VERIFICACIÓN DE CLÍNICAS', 'url' => ['index', 'clinica_id' => $model->clinica_id]];
$this->params['breadcrumbs'][] = $this->title;

// Helper para generar el HTML del valor booleano como un badge
// Ahora solo necesita el atributo, el label lo toma del modelo
$renderBooleanField = function ($attribute, $model) {
    // Obtenemos el label directamente del modelo
    $label = $model->getAttributeLabel($attribute);

    $isChecked = ($model->$attribute === true || $model->$attribute === 1 || $model->$attribute === 't');
    $statusText = $isChecked ? 'Sí' : 'No';
    $badgeClass = $isChecked ? 'badge-success' : 'badge-danger';

    return Html::tag('div',
        Html::tag('label', $label, ['class' => 'form-label field-label']) .
        Html::tag('div',
            Html::tag('span', $statusText, ['class' => 'badge ' . $badgeClass . ' boolean-badge']),
            ['class' => 'd-flex align-items-center justify-content-center value-container']
        ),
        ['class' => 'd-flex align-items-center mb-3 p-2 border-bottom field-row']
    );
};

// Helper para los campos que no son booleanos (solo texto)
// Ahora solo necesita el atributo, el label lo toma del modelo
$renderTextField = function ($attribute, $model) {
    // Obtenemos el label directamente del modelo
    $label = $model->getAttributeLabel($attribute);

    return Html::tag('div',
        Html::tag('label', $label, ['class' => 'form-label field-label']) .
        Html::tag('div', Html::encode($model->$attribute), ['class' => 'text-center value-container text-value']),
        ['class' => 'd-flex align-items-center mb-3 p-2 border-bottom field-row']
    );
};

?>

<div class="check-list-clinicas-view container mt-4">

    <h1 class="text-center mb-4"><?= Html::encode($this->title) ?></h1>

    <div class="d-flex justify-content-center mb-4">
        <?= Html::a(
            '<i class="fas fa-undo"></i> Volver', 
            '#',
            [
                'class' => 'btn btn-primary btn-lg mr-3', 
                'onclick' => 'window.history.back(); return false;', 
                'title' => 'Volver a la página anterior', 
            ]
        ) ?> 
        <?= Html::a('<i class="fas fa-sync-alt"></i> Renovar', ['update', 'id' => $model->id], ['class' => 'btn btn-info btn-lg mr-3']) ?>
        <?= Html::a('<i class="fas fa-trash-alt"></i> Eliminar', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger btn-lg',
            'data' => [
                'confirm' => '¿Está seguro de que desea eliminar este elemento?',
                'method' => 'post',
            ],
        ]) ?>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-12">

            <div class="card shadow mb-4">
                <div class="card-header bg-gradient-primary text-white text-center">
                    <h5 class="mb-0">Información General</h5>
                </div>
                <div class="card-body" style="display:none;">
                    <?= $renderTextField('id', $model) ?>
                    <?= $renderTextField('clinica_id', $model) ?>
                </div>
                <div class="card-body" style="display:block;">
                   RIF: <h3><?= $clinica->rif ?></h3>
                   NOMBRE: <h3><?= $clinica->nombre ?></h3>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header bg-gradient-info text-white text-center">
                    <h5 class="mb-0">Paso 1: Visita de Clínica de SISPSA. Responsable: Presidencia. Gestión de Operaciones (Personas contacto) 
                    </h5>
                </div>
                <div class="card-body">
                    <?= $renderBooleanField('planes', $model) ?>
                    <?= $renderBooleanField('otro_paso1', $model) ?>
                    <?= $renderBooleanField('programa_de_servicio', $model) ?>
                    <?= $renderBooleanField('equipamiento', $model) ?>
                    <?= $renderBooleanField('servicios_de_tecnologia', $model) ?>
                    <?= $renderBooleanField('soepsa_rm_009013_reglamento_soepsa_mes', $model) ?>
                    <?= $renderBooleanField('visita_clinica_registro_escrito', $model) ?>
                   
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header bg-gradient-info text-white text-center">
                     <h5 class="mb-0">Paso 2: Evaluación de la Clínica. Responsable: Gestión de Operaciones/Informática/Comercialización</h5>
                </div>
                <div class="card-body">
                    <?= $renderBooleanField('ubicacion_de_la_clinica_facil_acceso_usuario', $model) ?>
                    <?= $renderBooleanField('instalaciones_adecuacion_aire_atencion_medica', $model) ?>
                    <?= $renderBooleanField('instalaciones_optimas_equipos_nuevos', $model) ?>
                    <?= $renderBooleanField('generador_energia_emergencia', $model) ?>
                    <?= $renderBooleanField('capacidad_atencion_afiliados_emergencia_hosp', $model) ?>
                    <?= $renderBooleanField('especialistas_diferentes_especialidades', $model) ?>
                    <?= $renderBooleanField('disponibilidad_ambulancia', $model) ?>
                    <?= $renderBooleanField('personal_medico_registrado_licencia', $model) ?>
                    <?= $renderBooleanField('personal_enfermeria_licencia', $model) ?>
                    <?= $renderBooleanField('complemento_horarios_atencion', $model) ?>
                    <?= $renderBooleanField('servicio_farmacia', $model) ?>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-header bg-gradient-info text-white text-center">
                   <h5 class="mb-0">Paso 3: Presentación del CONVENIO SISPSA-CLÍNICA. Responsable: Gestión de Operaciones, Consultoría Jurídica, cualquier otra Dirección que sirva de apoyo</h5>
                </div>
                <div class="card-body">
                    <?= $renderBooleanField('acuerdo_servicios_medicos_emergencia_urgencia', $model) ?>
                    <?= $renderBooleanField('formatos_servicios', $model) ?>
                    <?= $renderBooleanField('certificacion_cumplimientos_normativas_sanitarias', $model) ?>
                    <?= $renderBooleanField('certificacion_cumplimiento_normativas_riesgos', $model) ?>
                    <?= $renderBooleanField('certificado_control_calidad', $model) ?>
                    <?= $renderBooleanField('documentos_superintendencia_seguros', $model) ?>
                    <?= $renderBooleanField('certificado_registro_salud_minsalud_ci_nii', $model) ?>
                    <?= $renderBooleanField('documentos_legales_firma_poderes_autorizar', $model) ?>
                    <?= $renderBooleanField('firma_permisos', $model) ?>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-header bg-gradient-info text-white text-center">
                    <h5 class="mb-0">Paso 4: Firma de contrato Responsable: Gestión de Operaciones, Consultoría Jurídica, cualquier otra Director que sirva de apoyo</h5>
                </div>
                <div class="card-body">
                    <?= $renderBooleanField('contratos', $model) ?>
                    <?= $renderBooleanField('marco_legal_operar', $model) ?>
                    <?= $renderBooleanField('documentacion_responsables_firmantes_contrato', $model) ?>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-header bg-gradient-info text-white text-center">
                    <h5 class="mb-0">Paso 5: Puesta en marcha del convenio</h5>
                </div>
                <div class="card-body">
                    <?= $renderBooleanField('personal_soepsa', $model) ?>
                    <?= $renderBooleanField('asignar_personal_adecuado_soepsa', $model) ?>
                    <?= $renderBooleanField('capacitacion_personal_pdv_servicio_local', $model) ?>
                    <?= $renderBooleanField('verif_sistema_pdv_local_clinica', $model) ?>
                    <?= $renderBooleanField('otro_paso5', $model) ?>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-header bg-gradient-info text-white text-center">
                    <h5 class="mb-0">Paso 6: Ejecución detallada del sistema</h5>
                </div>
                <div class="card-body">
                    <?= $renderBooleanField('descripcion_servicios_soepsa', $model) ?>
                    <?= $renderBooleanField('explicacion_beneficios_afiliados_soepsa', $model) ?>
                    <?= $renderBooleanField('espacio_presentacion_ilum_sonido_internet', $model) ?>
                    <?= $renderBooleanField('material_apoyo_presentacion_triptico_laminas', $model) ?>
                    <?= $renderBooleanField('plan_contingencia_fallas_procedencia', $model) ?>
                    <?= $renderBooleanField('comunicacion_fluida_gerencias_apoyo', $model) ?>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-header bg-gradient-info text-white text-center">
                    <h5 class="mb-0">Paso 7: Análisis y estudio de los plazos con el equipo de trabajo</h5>
                </div>
                <div class="card-body">
                    <?= $renderBooleanField('tiempo_ejecucion_actividades_acumuladas', $model) ?>
                    <?= $renderBooleanField('plazos_ejecucion_actividades_super', $model) ?>
                    <?= $renderBooleanField('verificar_servicios_cuentan_clinica', $model) ?>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-header bg-gradient-info text-white text-center">
                    <h5 class="mb-0">Paso 8: Especificar, analizar y evaluar el Plan</h5>
                </div>
                <div class="card-body">
                    <?= $renderBooleanField('equipo_implementacion_coordinador_soepsa', $model) ?>
                    <?= $renderBooleanField('equipo_implementacion_gerencia', $model) ?>
                    <?= $renderBooleanField('equipo_implementacion_directivos_operaciones', $model) ?>
                    <?= $renderBooleanField('equipo_implementacion_directivo_marketing_ventas', $model) ?>
                    <?= $renderBooleanField('equipo_implementacion_directivo_finanzas', $model) ?>
                    <?= $renderBooleanField('equipo_implementacion_contabilidad', $model) ?>
                    <?= $renderBooleanField('equipo_implementacion_coordinador_ventas', $model) ?>
                    <?= $renderBooleanField('equipo_implementacion_soporte', $model) ?>
                    <?= $renderBooleanField('equipo_implementacion_cursos_capacitacion', $model) ?>
                    <?= $renderBooleanField('equipo_implementacion_material_apoyo_sistema', $model) ?>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-header bg-gradient-info text-white text-center">
                    <h5 class="mb-0">Paso 9: Ventas de los Planes</h5>
                </div>
                <div class="card-body">
                    <?= $renderBooleanField('sistema_soepsa', $model) ?>
                    <?= $renderBooleanField('instalacion_personal_medico', $model) ?>
                    <?= $renderBooleanField('verificacion_area_emergencia', $model) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos personalizados para los gradientes en los headers de las cards */
    .bg-gradient-primary {
        background: linear-gradient(45deg, #007bff, #0056b3) !important;
    }
    .bg-gradient-info {
        background: linear-gradient(45deg, #17a2b8, #0f6674) !important;
    }

    /* Estilo para los card-header (aumentado para que sea más largo) */
    .card-header {
        height: 70px; /* Ajusta este valor para la altura deseada */
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .card-header h4 {
        margin-bottom: 0; /* Asegura que no haya margen inferior extra en el título */
    }

    /* Contenedor de cada fila de campo */
    .field-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 15px;
    }

    /* Estilo para la etiqueta del campo (parte "Importante") */
    .field-label {
        flex-shrink: 0;
        flex-basis: 50%;
        max-width: 50%;
        font-size: 1.1em;
        font-weight: bold;
        color: #343a40;
        text-align: left;
        padding-right: 20px;
        word-wrap: break-word;
    }

    /* Contenedor del valor (solo badge o texto) */
    .value-container {
        flex-grow: 1;
        flex-basis: 50%;
        max-width: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Ajuste para el badge */
    .boolean-badge {
        min-width: 60px;
        text-align: center;
        padding: .5em .8em;
        font-size: 1em;
        border-radius: .40rem;
        font-weight: bold;
    }

    /* Para campos de texto plano (no booleanos) */
    .text-value.value-container {
        text-align: center;
        color: #007bff;
        font-size: 1.1em;
        font-weight: bold;
        padding: 0;
        line-height: normal;
    }

    /* Estilos para los botones de acción */
    .btn-lg {
        font-size: 1.1em;
        padding: .8em 1.5em;
    }
    .btn-lg i {
        margin-right: 8px;
    }
</style>

