<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\CheckListClinicas */

$this->title = 'DETALLES DE LA VERIFICACIÓN DE LA CLÍNICA: ' . $model->clinica->nombre;
$this->params['breadcrumbs'][] = ['label' => 'LISTA DE VERIFICACIÓN DE CLÍNICAS', 'url' => ['index', 'clinica_id' => $model->clinica_id]];
$this->params['breadcrumbs'][] = $this->title;

// Helper para generar el HTML del valor booleano como un badge
$renderBooleanField = function ($attribute, $label, $model) {
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
$renderTextField = function ($attribute, $label, $model) {
    return Html::tag('div',
        Html::tag('label', $label, ['class' => 'form-label field-label']) .
        Html::tag('div', Html::encode($model->$attribute), ['class' => 'text-center value-container text-value']), // text-value para estilos específicos
        ['class' => 'd-flex align-items-center mb-3 p-2 border-bottom field-row']
    );
};

?>

<div class="check-list-clinicas-view container mt-4">

    <h3 class="text-center mb-4"><?= Html::encode($this->title) ?></h3>

    <div class="d-flex justify-content-center mb-4">
        <?= Html::a('<i class="fas fa-edit"></i> Actualizar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary btn-lg mr-3']) ?>
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
                    <h4 class="mb-0">Información General de la Cínica</h4>
                </div>
                <div class="card-body" style="display: none;">
                    <?= $renderTextField('id', 'ID:', $model) ?>
                    <?= $renderTextField('clinica_id', 'Clínica ID:', $model) ?>
                </div>
                <div class="card-body" style="display: block;">
                    RIF: <?= $clinica->rif ?><br>
                    CLÍNICA: <?= $clinica->nombre ?>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header bg-gradient-info text-white text-center">
                    <h4 class="mb-0">Paso 1: Planes y Servicios</h4>
                </div>
                <div class="card-body">
                    <?= $renderBooleanField('planes', 'Planes:', $model) ?>
                    <?= $renderBooleanField('programa_de_servicio', 'Programa de Servicio:', $model) ?>
                    <?= $renderBooleanField('equipamiento', 'Equipamiento:', $model) ?>
                    <?= $renderBooleanField('servicios_de_tecnologia', 'Servicios de Tecnología:', $model) ?>
                    <?= $renderBooleanField('soepsa_rm_009013_reglamento_soepsa_mes', 'SOEPSA RM 009013 Reglamento SOEPSA MES:', $model) ?>
                    <?= $renderBooleanField('visita_clinica_registro_escrito', 'Visita Clínica Registro Escrito:', $model) ?>
                    <?= $renderBooleanField('otro_paso1', 'Otro Paso 1:', $model) ?>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header bg-gradient-info text-white text-center">
                    <h4 class="mb-0">Paso 2: Instalaciones y Personal</h4>
                </div>
                <div class="card-body">
                    <?= $renderBooleanField('ubicacion_de_la_clinica_facil_acceso_usuario', 'Ubicación de la Clínica Fácil Acceso Usuario:', $model) ?>
                    <?= $renderBooleanField('instalaciones_adecuacion_aire_atencion_medica', 'Instalaciones Adecuación Aire Atención Médica:', $model) ?>
                    <?= $renderBooleanField('instalaciones_optimas_equipos_nuevos', 'Instalaciones Óptimas Equipos Nuevos:', $model) ?>
                    <?= $renderBooleanField('generador_energia_emergencia', 'Generador Energía Emergencia:', $model) ?>
                    <?= $renderBooleanField('capacidad_atencion_afiliados_emergencia_hosp', 'Capacidad Atención Afiliados Emergencia Hosp:', $model) ?>
                    <?= $renderBooleanField('especialistas_diferentes_especialidades', 'Especialistas Diferentes Especialidades:', $model) ?>
                    <?= $renderBooleanField('disponibilidad_ambulancia', 'Disponibilidad Ambulancia:', $model) ?>
                    <?= $renderBooleanField('personal_medico_registrado_licencia', 'Personal Médico Registrado Licencia:', $model) ?>
                    <?= $renderBooleanField('personal_enfermeria_licencia', 'Personal Enfermería Licencia:', $model) ?>
                    <?= $renderBooleanField('complemento_horarios_atencion', 'Complemento Horarios Atención:', $model) ?>
                    <?= $renderBooleanField('servicio_farmacia', 'Servicio Farmacia:', $model) ?>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-header bg-gradient-info text-white text-center">
                    <h4 class="mb-0">Paso 3: Cumplimiento Normativo y Legal</h4>
                </div>
                <div class="card-body">
                    <?= $renderBooleanField('acuerdo_servicios_medicos_emergencia_urgencia', 'Acuerdo Servicios Médicos Emergencia Urgencia:', $model) ?>
                    <?= $renderBooleanField('formatos_servicios', 'Formatos Servicios:', $model) ?>
                    <?= $renderBooleanField('certificacion_cumplimientos_normativas_sanitarias', 'Certificación Cumplimientos Normativas Sanitarias:', $model) ?>
                    <?= $renderBooleanField('certificacion_cumplimiento_normativas_riesgos', 'Certificación Cumplimiento Normativas Riesgos:', $model) ?>
                    <?= $renderBooleanField('certificado_control_calidad', 'Certificado Control Calidad:', $model) ?>
                    <?= $renderBooleanField('documentos_superintendencia_seguros', 'Documentos Superintendencia Seguros:', $model) ?>
                    <?= $renderBooleanField('certificado_registro_salud_minsalud_ci_nii', 'Certificado Registro Salud MinSalud CI NII:', $model) ?>
                    <?= $renderBooleanField('documentos_legales_firma_poderes_autorizar', 'Documentos Legales Firma Poderes Autorizar:', $model) ?>
                    <?= $renderBooleanField('firma_permisos', 'Firma Permisos:', $model) ?>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-header bg-gradient-info text-white text-center">
                    <h4 class="mb-0">Paso 4: Contratos y Marco Legal</h4>
                </div>
                <div class="card-body">
                    <?= $renderBooleanField('contratos', 'Contratos:', $model) ?>
                    <?= $renderBooleanField('marco_legal_operar', 'Marco Legal Operar:', $model) ?>
                    <?= $renderBooleanField('documentacion_responsables_firmantes_contrato', 'Documentación Responsables Firmantes Contrato:', $model) ?>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-header bg-gradient-info text-white text-center">
                    <h4 class="mb-0">Paso 5: Gestión de Personal SOEPSA</h4>
                </div>
                <div class="card-body">
                    <?= $renderBooleanField('personal_soepsa', 'Personal SOEPSA:', $model) ?>
                    <?= $renderBooleanField('asignar_personal_adecuado_soepsa', 'Asignar Personal Adecuado SOEPSA:', $model) ?>
                    <?= $renderBooleanField('capacitacion_personal_pdv_servicio_local', 'Capacitación Personal PDV Servicio Local:', $model) ?>
                    <?= $renderBooleanField('verif_sistema_pdv_local_clinica', 'Verificación Sistema PDV Local Clínica:', $model) ?>
                    <?= $renderBooleanField('otro_paso5', 'Otro Paso 5:', $model) ?>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-header bg-gradient-info text-white text-center">
                    <h4 class="mb-0">Paso 6: Comunicación y Presentación</h4>
                </div>
                <div class="card-body">
                    <?= $renderBooleanField('descripcion_servicios_soepsa', 'Descripción Servicios SOEPSA:', $model) ?>
                    <?= $renderBooleanField('explicacion_beneficios_afiliados_soepsa', 'Explicación Beneficios Afiliados SOEPSA:', $model) ?>
                    <?= $renderBooleanField('espacio_presentacion_ilum_sonido_internet', 'Espacio Presentación Ilum. Sonido Internet:', $model) ?>
                    <?= $renderBooleanField('material_apoyo_presentacion_triptico_laminas', 'Material Apoyo Presentación Tríptico Láminas:', $model) ?>
                    <?= $renderBooleanField('plan_contingencia_fallas_procedencia', 'Plan Contingencia Fallas Procedencia:', $model) ?>
                    <?= $renderBooleanField('comunicacion_fluida_gerencias_apoyo', 'Comunicación Fluida Gerencias Apoyo:', $model) ?>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-header bg-gradient-info text-white text-center">
                    <h4 class="mb-0">Paso 7: Tiempos de Ejecución</h4>
                </div>
                <div class="card-body">
                    <?= $renderBooleanField('tiempo_ejecucion_actividades_acumuladas', 'Tiempo Ejecución Actividades Acumuladas:', $model) ?>
                    <?= $renderBooleanField('plazos_ejecucion_actividades_super', 'Plazos Ejecucion Actividades Super:', $model) ?>
                    <?= $renderBooleanField('verificar_servicios_cuentan_clinica', 'Verificar Servicios Cuentan Clínica:', $model) ?>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-header bg-gradient-info text-white text-center">
                    <h4 class="mb-0">Paso 8: Equipo de Implementación</h4>
                </div>
                <div class="card-body">
                    <?= $renderBooleanField('equipo_implementacion_coordinador_soepsa', 'Equipo Implementación Coordinador SOEPSA:', $model) ?>
                    <?= $renderBooleanField('equipo_implementacion_gerencia', 'Equipo Implementación Gerencia:', $model) ?>
                    <?= $renderBooleanField('equipo_implementacion_directivos_operaciones', 'Equipo Implementación Directivos Operaciones:', $model) ?>
                    <?= $renderBooleanField('equipo_implementacion_directivo_marketing_ventas', 'Equipo Implementación Directivo Marketing Ventas:', $model) ?>
                    <?= $renderBooleanField('equipo_implementacion_directivo_finanzas', 'Equipo Implementación Directivo Finanzas:', $model) ?>
                    <?= $renderBooleanField('equipo_implementacion_contabilidad', 'Equipo Implementación Contabilidad:', $model) ?>
                    <?= $renderBooleanField('equipo_implementacion_coordinador_ventas', 'Equipo Implementación Coordinador Ventas:', $model) ?>
                    <?= $renderBooleanField('equipo_implementacion_soporte', 'Equipo Implementación Soporte:', $model) ?>
                    <?= $renderBooleanField('equipo_implementacion_cursos_capacitacion', 'Equipo Implementación Cursos Capacitación:', $model) ?>
                    <?= $renderBooleanField('equipo_implementacion_material_apoyo_sistema', 'Equipo Implementación Material Apoyo Sistema:', $model) ?>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-header bg-gradient-info text-white text-center">
                    <h4 class="mb-0">Paso 9: Configuración del Sistema</h4>
                </div>
                <div class="card-body">
                    <?= $renderBooleanField('sistema_soepsa', 'Sistema SOEPSA:', $model) ?>
                    <?= $renderBooleanField('instalacion_personal_medico', 'Instalación Personal Médico:', $model) ?>
                    <?= $renderBooleanField('verificacion_area_emergencia', 'Verificación Área Emergencia:', $model) ?>
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

    /* Contenedor de cada fila de campo */
    .field-row {
        display: flex; /* Asegura el comportamiento flex */
        align-items: center; /* Alinea los ítems al centro verticalmente */
        justify-content: space-between; /* Espacio entre la etiqueta y el valor */
        padding: 10px 15px;
    }

    /* Estilo para la etiqueta del campo (parte "Importante") */
    .field-label {
        /* Define un ancho fijo o un porcentaje para la etiqueta */
        flex-shrink: 0; /* Previene que la etiqueta se encoja */
        flex-basis: 50%; /* La etiqueta ocupa el 50% del ancho disponible de la fila */
        max-width: 50%; /* Asegura que no exceda el 50% */
        font-size: 1.1em;
        font-weight: bold;
        color: #343a40;
        text-align: left;
        padding-right: 20px; /* Espacio entre etiqueta y valor */
        word-wrap: break-word; /* Permite que el texto de la etiqueta se envuelva */
    }

    /* Contenedor del valor (solo badge o texto) */
    .value-container {
        /* Define un ancho fijo o un porcentaje para el valor */
        flex-grow: 1; /* Permite que ocupe el espacio restante */
        flex-basis: 50%; /* El valor ocupa el 50% del ancho disponible de la fila */
        max-width: 50%; /* Asegura que no exceda el 50% */
        display: flex;
        align-items: center;
        justify-content: center; /* Centrar el badge/texto en su propia "columna" */
    }
    
    /* Ajuste para el badge */
    .boolean-badge {
        min-width: 60px; /* Ancho mínimo para el badge */
        text-align: center;
        padding: .5em .8em;
        font-size: 1em;
        border-radius: .40rem;
        font-weight: bold;
    }

    /* Para campos de texto plano (no booleanos) */
    .text-value.value-container { /* Combinamos la clase text-value con value-container */
        text-align: center; /* Alinea el texto al centro */
        color: #007bff;
        font-size: 1.1em;
        font-weight: bold;
        padding: 0; /* Elimina padding para evitar desplazamientos */
        line-height: normal; /* Asegura que la altura de línea no afecte la alineación */
    }
</style>