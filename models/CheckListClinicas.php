<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "check_list_clinicas".
 *
 * @property int $id
 * @property int|null $clinica_id
 * @property bool|null $planes
 * @property bool|null $programa_de_servicio
 * @property bool|null $equipamiento
 * @property bool|null $servicios_de_tecnologia
 * @property bool|null $soepsa_rm_009013_reglamento_soepsa_mes
 * @property bool|null $visita_clinica_registro_escrito
 * @property bool|null $otro_paso1
 * @property bool|null $ubicacion_de_la_clinica_facil_acceso_usuario
 * @property bool|null $instalaciones_adecuacion_aire_atencion_medica
 * @property bool|null $instalaciones_optimas_equipos_nuevos
 * @property bool|null $generador_energia_emergencia
 * @property bool|null $capacidad_atencion_afiliados_emergencia_hosp
 * @property bool|null $especialistas_diferentes_especialidades
 * @property bool|null $disponibilidad_ambulancia
 * @property bool|null $personal_medico_registrado_licencia
 * @property bool|null $personal_enfermeria_licencia
 * @property bool|null $complemento_horarios_atencion
 * @property bool|null $servicio_farmacia
 * @property bool|null $acuerdo_servicios_medicos_emergencia_urgencia
 * @property bool|null $formatos_servicios
 * @property bool|null $certificacion_cumplimientos_normativas_sanitarias
 * @property bool|null $certificacion_cumplimiento_normativas_riesgos
 * @property bool|null $certificado_control_calidad
 * @property bool|null $documentos_superintendencia_seguros
 * @property bool|null $certificado_registro_salud_minsalud_ci_nii
 * @property bool|null $documentos_legales_firma_poderes_autorizar
 * @property bool|null $firma_permisos
 * @property bool|null $contratos
 * @property bool|null $marco_legal_operar
 * @property bool|null $documentacion_responsables_firmantes_contrato
 * @property bool|null $personal_soepsa
 * @property bool|null $asignar_personal_adecuado_soepsa
 * @property bool|null $capacitacion_personal_pdv_servicio_local
 * @property bool|null $verif_sistema_pdv_local_clinica
 * @property bool|null $otro_paso5
 * @property bool|null $descripcion_servicios_soepsa
 * @property bool|null $explicacion_beneficios_afiliados_soepsa
 * @property bool|null $espacio_presentacion_ilum_sonido_internet
 * @property bool|null $material_apoyo_presentacion_triptico_laminas
 * @property bool|null $plan_contingencia_fallas_procedencia
 * @property bool|null $comunicacion_fluida_gerencias_apoyo
 * @property bool|null $tiempo_ejecucion_actividades_acumuladas
 * @property bool|null $plazos_ejecucion_actividades_super
 * @property bool|null $verificar_servicios_cuentan_clinica
 * @property bool|null $equipo_implementacion_coordinador_soepsa
 * @property bool|null $equipo_implementacion_gerencia
 * @property bool|null $equipo_implementacion_directivos_operaciones
 * @property bool|null $equipo_implementacion_directivo_marketing_ventas
 * @property bool|null $equipo_implementacion_directivo_finanzas
 * @property bool|null $equipo_implementacion_contabilidad
 * @property bool|null $equipo_implementacion_coordinador_ventas
 * @property bool|null $equipo_implementacion_soporte
 * @property bool|null $equipo_implementacion_cursos_capacitacion
 * @property bool|null $equipo_implementacion_material_apoyo_sistema
 * @property bool|null $sistema_soepsa
 * @property bool|null $instalacion_personal_medico
 * @property bool|null $verificacion_area_emergencia
 */
class CheckListClinicas extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'check_list_clinicas';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // El campo clinica_id es requerido
            ['clinica_id', 'required'],
            // Asegura que clinica_id sea un entero
            ['clinica_id', 'integer'],

            // Todos los campos booleanos se declaran como 'boolean'
            // y luego se marcan como 'required'.
            // Un checkbox no marcado enviaría null o false,
            // pero si es requerido, deberías manejar que siempre haya un valor.
            // Para checkboxes, esto significa que el usuario debe marcarlo.
            [['planes', 'programa_de_servicio', 'equipamiento', 'servicios_de_tecnologia',
              'soepsa_rm_009013_reglamento_soepsa_mes', 'visita_clinica_registro_escrito', 'otro_paso1',
              'ubicacion_de_la_clinica_facil_acceso_usuario', 'instalaciones_adecuacion_aire_atencion_medica',
              'instalaciones_optimas_equipos_nuevos', 'generador_energia_emergencia',
              'capacidad_atencion_afiliados_emergencia_hosp', 'especialistas_diferentes_especialidades',
              'disponibilidad_ambulancia', 'personal_medico_registrado_licencia', 'personal_enfermeria_licencia',
              'complemento_horarios_atencion', 'servicio_farmacia',
              'acuerdo_servicios_medicos_emergencia_urgencia', 'formatos_servicios',
              'certificacion_cumplimientos_normativas_sanitarias', 'certificacion_cumplimiento_normativas_riesgos',
              'certificado_control_calidad', 'documentos_superintendencia_seguros',
              'certificado_registro_salud_minsalud_ci_nii', 'documentos_legales_firma_poderes_autorizar',
              'firma_permisos', 'contratos', 'marco_legal_operar', 'documentacion_responsables_firmantes_contrato',
              'personal_soepsa', 'asignar_personal_adecuado_soepsa', 'capacitacion_personal_pdv_servicio_local',
              'verif_sistema_pdv_local_clinica', 'otro_paso5', 'descripcion_servicios_soepsa',
              'explicacion_beneficios_afiliados_soepsa', 'espacio_presentacion_ilum_sonido_internet',
              'material_apoyo_presentacion_triptico_laminas', 'plan_contingencia_fallas_procedencia',
              'comunicacion_fluida_gerencias_apoyo', 'tiempo_ejecucion_actividades_acumuladas',
              'plazos_ejecucion_actividades_super', 'verificar_servicios_cuentan_clinica',
              'equipo_implementacion_coordinador_soepsa', 'equipo_implementacion_gerencia',
              'equipo_implementacion_directivos_operaciones', 'equipo_implementacion_directivo_marketing_ventas',
              'equipo_implementacion_directivo_finanzas', 'equipo_implementacion_contabilidad',
              'equipo_implementacion_coordinador_ventas', 'equipo_implementacion_soporte',
              'equipo_implementacion_cursos_capacitacion', 'equipo_implementacion_material_apoyo_sistema',
              'sistema_soepsa', 'instalacion_personal_medico', 'verificacion_area_emergencia'], 'boolean'],
            
            /*[['planes', 'programa_de_servicio', 'equipamiento', 'servicios_de_tecnologia',
              'soepsa_rm_009013_reglamento_soepsa_mes', 'visita_clinica_registro_escrito', 'otro_paso1',
              'ubicacion_de_la_clinica_facil_acceso_usuario', 'instalaciones_adecuacion_aire_atencion_medica',
              'instalaciones_optimas_equipos_nuevos', 'generador_energia_emergencia',
              'capacidad_atencion_afiliados_emergencia_hosp', 'especialistas_diferentes_especialidades',
              'disponibilidad_ambulancia', 'personal_medico_registrado_licencia', 'personal_enfermeria_licencia',
              'complemento_horarios_atencion', 'servicio_farmacia',
              'acuerdo_servicios_medicos_emergencia_urgencia', 'formatos_servicios',
              'certificacion_cumplimientos_normativas_sanitarias', 'certificacion_cumplimiento_normativas_riesgos',
              'certificado_control_calidad', 'documentos_superintendencia_seguros',
              'certificado_registro_salud_minsalud_ci_nii', 'documentos_legales_firma_poderes_autorizar',
              'firma_permisos', 'contratos', 'marco_legal_operar', 'documentacion_responsables_firmantes_contrato',
              'personal_soepsa', 'asignar_personal_adecuado_soepsa', 'capacitacion_personal_pdv_servicio_local',
              'verif_sistema_pdv_local_clinica', 'otro_paso5', 'descripcion_servicios_soepsa',
              'explicacion_beneficios_afiliados_soepsa', 'espacio_presentacion_ilum_sonido_internet',
              'material_apoyo_presentacion_triptico_laminas', 'plan_contingencia_fallas_procedencia',
              'comunicacion_fluida_gerencias_apoyo', 'tiempo_ejecucion_actividades_acumuladas',
              'plazos_ejecucion_actividades_super', 'verificar_servicios_cuentan_clinica',
              'equipo_implementacion_coordinador_soepsa', 'equipo_implementacion_gerencia',
              'equipo_implementacion_directivos_operaciones', 'equipo_implementacion_directivo_marketing_ventas',
              'equipo_implementacion_directivo_finanzas', 'equipo_implementacion_contabilidad',
              'equipo_implementacion_coordinador_ventas', 'equipo_implementacion_soporte',
              'equipo_implementacion_cursos_capacitacion', 'equipo_implementacion_material_apoyo_sistema',
              'sistema_soepsa', 'instalacion_personal_medico', 'verificacion_area_emergencia'], 'required'],*/
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'clinica_id' => 'Clínica ID',
            'planes' => 'Planes',
            'programa_de_servicio' => 'Programa de servicio',
            'equipamiento' => 'Equipamiento',
            'servicios_de_tecnologia' => 'Servicios de tecnología',
            'soepsa_rm_009013_reglamento_soepsa_mes' => 'SOEPSA, RM 009013, (Reglamento SOEPSA/MES)',
            'visita_clinica_registro_escrito' => 'Visita clínica, (Registro escrito)',
            'otro_paso1' => 'Otro (Paso 1)',
            'ubicacion_de_la_clinica_facil_acceso_usuario' => 'Ubicación de la clínica, fácil acceso para el usuario',
            'instalaciones_adecuacion_aire_atencion_medica' => 'Instalaciones y adecuación de aire atención médica',
            'instalaciones_optimas_equipos_nuevos' => 'Instalaciones óptimas, equipos nuevos',
            'generador_energia_emergencia' => 'Generador de energía, para casos de falla de servicio y emergencia',
            'capacidad_atencion_afiliados_emergencia_hosp' => 'Capacidad para atender a los afiliados (emergencia y hospitalización)',
            'especialistas_diferentes_especialidades' => 'Especialistas de las diferentes especialidades',
            'disponibilidad_ambulancia' => 'Disponibilidad de ambulancia',
            'personal_medico_registrado_licencia' => 'Personal médico registrado y con licencia',
            'personal_enfermeria_licencia' => 'Personal de enfermería con licencia',
            'complemento_horarios_atencion' => 'Complemento de horarios de atención',
            'servicio_farmacia' => 'Servicio de farmacia',
            'acuerdo_servicios_medicos_emergencia_urgencia' => 'Acuerdo de servicios médicos de emergencia y urgencia',
            'formatos_servicios' => 'Formatos de servicios',
            'certificacion_cumplimientos_normativas_sanitarias' => 'Certificación de cumplimientos de normativas sanitarias',
            'certificacion_cumplimiento_normativas_riesgos' => 'Certificación de cumplimiento de normativas de riesgos',
            'certificado_control_calidad' => 'Certificado de control de calidad',
            'documentos_superintendencia_seguros' => 'Documentos de la Superintendencia de Seguros',
            'certificado_registro_salud_minsalud_ci_nii' => 'Certificado de registro de salud (Ministerio de Salud, C.I. y NII)',
            'documentos_legales_firma_poderes_autorizar' => 'Documentos legales necesarios para la firma, poderes para autorizar',
            'firma_permisos' => 'Firma y permisos',
            'contratos' => 'Contratos',
            'marco_legal_operar' => 'Marco legal para operar',
            'documentacion_responsables_firmantes_contrato' => 'Documentación de los responsables y firmantes del contrato',
            'personal_soepsa' => 'Personal SOEPSA',
            'asignar_personal_adecuado_soepsa' => 'Asignar personal adecuado para el personal de SOEPSA',
            'capacitacion_personal_pdv_servicio_local' => 'Capacitación de personal, punto de venta, servicio, en el local',
            'verif_sistema_pdv_local_clinica' => 'Verificación del sistema, punto de venta, servicio, en el local (Clínica)',
            'otro_paso5' => 'Otro (Paso 5)',
            'descripcion_servicios_soepsa' => 'Descripción de los servicios que ofrece SOEPSA',
            'explicacion_beneficios_afiliados_soepsa' => 'Explicación de los beneficios de los afiliados de SOEPSA',
            'espacio_presentacion_ilum_sonido_internet' => 'Espacio adecuado para la presentación: tamaño, iluminación, sonido, internet',
            'material_apoyo_presentacion_triptico_laminas' => 'Material de apoyo para la presentación (Tríptico o láminas)',
            'plan_contingencia_fallas_procedencia' => 'Plan de contingencia, en caso de fallas de alguna procedencia',
            'comunicacion_fluida_gerencias_apoyo' => 'Comunicación fluida con las gerencias de apoyo',
            'tiempo_ejecucion_actividades_acumuladas' => 'Tiempo estimado de ejecución de actividades acumuladas entre las partes',
            'plazos_ejecucion_actividades_super' => 'Definición de plazos de ejecución de las actividades por la Superintendencia',
            'verificar_servicios_cuentan_clinica' => 'Verificar los servicios con lo que cuentan (Clínica)',
            'equipo_implementacion_coordinador_soepsa' => 'Coordinador de SOEPSA',
            'equipo_implementacion_gerencia' => 'Gerencia',
            'equipo_implementacion_directivos_operaciones' => 'Directivos (Operaciones)',
            'equipo_implementacion_directivo_marketing_ventas' => 'Directivo (Marketing y Ventas)',
            'equipo_implementacion_directivo_finanzas' => 'Directivo (Finanzas)',
            'equipo_implementacion_contabilidad' => 'Contabilidad',
            'equipo_implementacion_coordinador_ventas' => 'Coordinador de Ventas',
            'equipo_implementacion_soporte' => 'Soporte',
            'equipo_implementacion_cursos_capacitacion' => 'Cursos - Capacitación',
            'equipo_implementacion_material_apoyo_sistema' => 'Material de apoyo al Sistema',
            'sistema_soepsa' => 'Sistema SOEPSA',
            'instalacion_personal_medico' => 'Instalación de personal médico',
            'verificacion_area_emergencia' => 'Verificación del área de emergencia',
        ];
    }

    public function getClinica()
    {
        // Relaciona idusuariopropietario de Agente con id de User
        return $this->hasOne(RmClinica::class, ['id' => 'clinica_id']);
    }
}