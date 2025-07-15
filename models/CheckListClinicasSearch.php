<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\CheckListClinicas;

/**
 * CheckListClinicasSearch represents the model behind the search form of `app\models\CheckListClinicas`.
 */
class CheckListClinicasSearch extends CheckListClinicas
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'clinica_id'], 'integer'],
            // Para los campos booleanos, no necesitamos que sean 'required' en el modelo de búsqueda.
            // Los tratamos como 'safe' para permitir que se reciban en el filtro,
            // pero su validación principal ocurre en el modelo 'CheckListClinicas'.
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
              'sistema_soepsa', 'instalacion_personal_medico', 'verificacion_area_emergencia'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = CheckListClinicas::find();

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
               'defaultOrder' => ['created_at' => SORT_DESC]
             ],
            'pagination' => ['pageSize' => 20 ],
        ]);
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'clinica_id' => $this->clinica_id,
            // Aplicamos condiciones de filtrado para cada campo booleano
            'planes' => $this->planes,
            'programa_de_servicio' => $this->programa_de_servicio,
            'equipamiento' => $this->equipamiento,
            'servicios_de_tecnologia' => $this->servicios_de_tecnologia,
            'soepsa_rm_009013_reglamento_soepsa_mes' => $this->soepsa_rm_009013_reglamento_soepsa_mes,
            'visita_clinica_registro_escrito' => $this->visita_clinica_registro_escrito,
            'otro_paso1' => $this->otro_paso1,
            'ubicacion_de_la_clinica_facil_acceso_usuario' => $this->ubicacion_de_la_clinica_facil_acceso_usuario,
            'instalaciones_adecuacion_aire_atencion_medica' => $this->instalaciones_adecuacion_aire_atencion_medica,
            'instalaciones_optimas_equipos_nuevos' => $this->instalaciones_optimas_equipos_nuevos,
            'generador_energia_emergencia' => $this->generador_energia_emergencia,
            'capacidad_atencion_afiliados_emergencia_hosp' => $this->capacidad_atencion_afiliados_emergencia_hosp,
            'especialistas_diferentes_especialidades' => $this->especialistas_diferentes_especialidades,
            'disponibilidad_ambulancia' => $this->disponibilidad_ambulancia,
            'personal_medico_registrado_licencia' => $this->personal_medico_registrado_licencia,
            'personal_enfermeria_licencia' => $this->personal_enfermeria_licencia,
            'complemento_horarios_atencion' => $this->complemento_horarios_atencion,
            'servicio_farmacia' => $this->servicio_farmacia,
            'acuerdo_servicios_medicos_emergencia_urgencia' => $this->acuerdo_servicios_medicos_emergencia_urgencia,
            'formatos_servicios' => $this->formatos_servicios,
            'certificacion_cumplimientos_normativas_sanitarias' => $this->certificacion_cumplimientos_normativas_sanitarias,
            'certificacion_cumplimiento_normativas_riesgos' => $this->certificacion_cumplimiento_normativas_riesgos,
            'certificado_control_calidad' => $this->certificado_control_calidad,
            'documentos_superintendencia_seguros' => $this->documentos_superintendencia_seguros,
            'certificado_registro_salud_minsalud_ci_nii' => $this->certificado_registro_salud_minsalud_ci_nii,
            'documentos_legales_firma_poderes_autorizar' => $this->documentos_legales_firma_poderes_autorizar,
            'firma_permisos' => $this->firma_permisos,
            'contratos' => $this->contratos,
            'marco_legal_operar' => $this->marco_legal_operar,
            'documentacion_responsables_firmantes_contrato' => $this->documentacion_responsables_firmantes_contrato,
            'personal_soepsa' => $this->personal_soepsa,
            'asignar_personal_adecuado_soepsa' => $this->asignar_personal_adecuado_soepsa,
            'capacitacion_personal_pdv_servicio_local' => $this->capacitacion_personal_pdv_servicio_local,
            'verif_sistema_pdv_local_clinica' => $this->verif_sistema_pdv_local_clinica,
            'otro_paso5' => $this->otro_paso5,
            'descripcion_servicios_soepsa' => $this->descripcion_servicios_soepsa,
            'explicacion_beneficios_afiliados_soepsa' => $this->explicacion_beneficios_afiliados_soepsa,
            'espacio_presentacion_ilum_sonido_internet' => $this->espacio_presentacion_ilum_sonido_internet,
            'material_apoyo_presentacion_triptico_laminas' => $this->material_apoyo_presentacion_triptico_laminas,
            'plan_contingencia_fallas_procedencia' => $this->plan_contingencia_fallas_procedencia,
            'comunicacion_fluida_gerencias_apoyo' => $this->comunicacion_fluida_gerencias_apoyo,
            'tiempo_ejecucion_actividades_acumuladas' => $this->tiempo_ejecucion_actividades_acumuladas,
            'plazos_ejecucion_actividades_super' => $this->plazos_ejecucion_actividades_super,
            'verificar_servicios_cuentan_clinica' => $this->verificar_servicios_cuentan_clinica,
            'equipo_implementacion_coordinador_soepsa' => $this->equipo_implementacion_coordinador_soepsa,
            'equipo_implementacion_gerencia' => $this->equipo_implementacion_gerencia,
            'equipo_implementacion_directivos_operaciones' => $this->equipo_implementacion_directivos_operaciones,
            'equipo_implementacion_directivo_marketing_ventas' => $this->equipo_implementacion_directivo_marketing_ventas,
            'equipo_implementacion_directivo_finanzas' => $this->equipo_implementacion_directivo_finanzas,
            'equipo_implementacion_contabilidad' => $this->equipo_implementacion_contabilidad,
            'equipo_implementacion_coordinador_ventas' => $this->equipo_implementacion_coordinador_ventas,
            'equipo_implementacion_soporte' => $this->equipo_implementacion_soporte,
            'equipo_implementacion_cursos_capacitacion' => $this->equipo_implementacion_cursos_capacitacion,
            'equipo_implementacion_material_apoyo_sistema' => $this->equipo_implementacion_material_apoyo_sistema,
            'sistema_soepsa' => $this->sistema_soepsa,
            'instalacion_personal_medico' => $this->instalacion_personal_medico,
            'verificacion_area_emergencia' => $this->verificacion_area_emergencia,
        ]);

        return $dataProvider;
    }
}