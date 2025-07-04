<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\CheckListClinicas */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="check-list-clinicas-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Información General</h4>
        </div>
        <div class="card-body">
            <div class="form-group row">
                <div class="col-md-12" style="display:none;">
                    <?= $form->field($model, 'clinica_id')->textInput(['placeholder' => 'ID de la Clínica', 'value' => $clinica->id]) ?>
                </div>
                <div class="col-md-12">
                    <h5><?= $clinica->nombre." RIF:".$clinica->rif ?></h5>
                </div>

            </div>
        </div>
    </div>

    <div class="card mb-4">
         <div class="card-header bg-gradient-info text-white text-center" style="height:50px;">
            <h4 class="mb-0">Paso 1: Visita de Clínica de SISPSA. Responsable: Presidencia. Gestión de Operaciones (Personas contacto) 
                    </h4>
        </div>
        <div class="card-body">
            <div class="form-group row">
                <div class="col-md-12">
                    <?= $form->field($model, 'planes')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'otro_paso1')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'programa_de_servicio')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'equipamiento')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'servicios_de_tecnologia')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'soepsa_rm_009013_reglamento_soepsa_mes')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'visita_clinica_registro_escrito')->checkbox() ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-gradient-info text-white text-center" style="height:50px;">
            <h4 class="mb-0">Paso 2: Evaluación de la Clínica. Responsable: Gestión de Operaciones/Informática/Comercialización</h4>
        </div>
        <div class="card-body">
            <div class="form-group row">
                <div class="col-md-12">
                    <?= $form->field($model, 'ubicacion_de_la_clinica_facil_acceso_usuario')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'instalaciones_adecuacion_aire_atencion_medica')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'instalaciones_optimas_equipos_nuevos')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'generador_energia_emergencia')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'capacidad_atencion_afiliados_emergencia_hosp')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'especialistas_diferentes_especialidades')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'disponibilidad_ambulancia')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'personal_medico_registrado_licencia')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'personal_enfermeria_licencia')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'complemento_horarios_atencion')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'servicio_farmacia')->checkbox() ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-gradient-info text-white text-center" style="height:50px;">                    
            <h4 class="mb-0">Paso 3: Presentación del CONVENIO SISPSA-CLÍNICA. Responsable: Gestión de Operaciones, Consultoría Jurídica, cualquier otra Dirección que sirva de apoyo</h4>
        </div>
        <div class="card-body">
            <div class="form-group row">
                <div class="col-md-12">
                    <?= $form->field($model, 'acuerdo_servicios_medicos_emergencia_urgencia')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'formatos_servicios')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'certificacion_cumplimientos_normativas_sanitarias')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'certificacion_cumplimiento_normativas_riesgos')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'certificado_control_calidad')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'documentos_superintendencia_seguros')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'certificado_registro_salud_minsalud_ci_nii')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'documentos_legales_firma_poderes_autorizar')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'firma_permisos')->checkbox() ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
         <div class="card-header bg-gradient-info text-white text-center" style="height:50px;">                    
            <h4 class="mb-0">Paso 4: Firma de contrato Responsable: Gestión de Operaciones, Consultoría Jurídica, cualquier otra Director que sirva de apoyo</h4>
                </div>
        <div class="card-body">
            <div class="form-group row">
                <div class="col-md-12">
                    <?= $form->field($model, 'contratos')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'marco_legal_operar')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'documentacion_responsables_firmantes_contrato')->checkbox() ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-gradient-info text-white text-center" style="height:50px;">                    
            <h4 class="mb-0">Paso 5: Puesta en marcha del convenio</h4>
                </div>
        <div class="card-body">
            <div class="form-group row">
                <div class="col-md-12">
                    <?= $form->field($model, 'personal_soepsa')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'asignar_personal_adecuado_soepsa')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'capacitacion_personal_pdv_servicio_local')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'verif_sistema_pdv_local_clinica')->checkbox() ?>
                </div>
                
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-gradient-info text-white text-center" style="height:50px;">                    
            <h4 class="mb-0">Paso 6: Ejecución detallada del sistema</h4>
                </div>
        <div class="card-body">
            <div class="form-group row">
                <div class="col-md-12">
                    <?= $form->field($model, 'otro_paso5')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'descripcion_servicios_soepsa')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'explicacion_beneficios_afiliados_soepsa')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'espacio_presentacion_ilum_sonido_internet')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'material_apoyo_presentacion_triptico_laminas')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'plan_contingencia_fallas_procedencia')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'comunicacion_fluida_gerencias_apoyo')->checkbox() ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-gradient-info text-white text-center" style="height:50px;">                    
            <h4 class="mb-0">Paso 7: Análisis y estudio de los plazos con el equipo de trabajo</h4>
                </div>
        <div class="card-body">
            <div class="form-group row">
                <div class="col-md-12">
                    <?= $form->field($model, 'tiempo_ejecucion_actividades_acumuladas')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'plazos_ejecucion_actividades_super')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'verificar_servicios_cuentan_clinica')->checkbox() ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
       <div class="card-header bg-gradient-info text-white text-center" style="height:50px;">                    
        <h4 class="mb-0">Paso 8: Especificar, analizar y evaluar el Plan</h4>
                </div>
        <div class="card-body">
            <div class="form-group row">
                <div class="col-md-12">
                    <?= $form->field($model, 'equipo_implementacion_coordinador_soepsa')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'equipo_implementacion_gerencia')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'equipo_implementacion_directivos_operaciones')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'equipo_implementacion_directivo_marketing_ventas')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'equipo_implementacion_directivo_finanzas')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'equipo_implementacion_contabilidad')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'equipo_implementacion_coordinador_ventas')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'equipo_implementacion_soporte')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'equipo_implementacion_cursos_capacitacion')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'equipo_implementacion_material_apoyo_sistema')->checkbox() ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
       <div class="card-header bg-gradient-info text-white text-center" style="height:50px;">
                    <h4 class="mb-0">Paso 9: Ventas de los Planes</h4>
                </div>
        <div class="card-body">
            <div class="form-group row">
                <div class="col-md-12">
                    <?= $form->field($model, 'sistema_soepsa')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'instalacion_personal_medico')->checkbox() ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($model, 'verificacion_area_emergencia')->checkbox() ?>
                </div>
            </div>
        </div>
    </div>

     <div class="form-group text-rigth mt-4">
        <?= Html::submitButton('<i class="fas fa-save"></i> Guardar Verificación', ['class' => 'btn btn-success btn-lg']) ?>
        <?= Html::a('Cancelar', ['index'], ['class' => 'tn btn btn-lg btn-warning']); ?>

        <?php if ($model->isNewRecord) { echo Html::a('Limpiar', ['create'], ['class' => 'btn btn-lg btn-outline-dark']); } ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<style>
    card-header {
        height: 100px !important; /* O el valor que prefieras */
        display: flex; /* Asegura que el contenido (h4) se centre verticalmente */
        align-items: center; /* Centra verticalmente */
        justify-content: center; /* Centra horizontalmente el texto */
    }
</style>