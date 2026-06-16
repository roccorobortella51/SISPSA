<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\AgenteFuerza;

/**
 * AgenteFuerzaSearch represents the model behind the search form of `app\models\AgenteFuerza`.
 */
class AgenteFuerzaSearch extends AgenteFuerza
{
    public $agenteFuerzaUserNombres;
    public $agenteFuerzaUserCedula;
    public $agenteFuerzaUserEmail;
    public $agenteFuerzaUserTelefono;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idusuario', 'agente_id', 'puede_vender', 'puede_asesorar', 'puede_cobrar', 'puede_post_venta', 'puede_registrar'], 'integer'],
            [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_registrar'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['agenteFuerzaUserNombres', 'agenteFuerzaUserCedula', 'agenteFuerzaUserEmail', 'agenteFuerzaUserTelefono', 'registro_corredor_actividad_aseguradora'], 'safe'],
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
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = AgenteFuerza::find();
        $query->joinWith(['userDatos']);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
                'pageSizeParam' => false,
                'defaultPageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => ['id' => SORT_ASC], // Order by ID ASCENDING
                'attributes' => [
                    'id' => [
                        'asc' => ['agente_fuerza.id' => SORT_ASC],
                        'desc' => ['agente_fuerza.id' => SORT_DESC],
                        'label' => 'N° Intermediario',
                        'default' => SORT_ASC, // Set default to ascending
                    ],
                    'agenteFuerzaUserNombres' => [
                        'asc' => ['user_datos.nombres' => SORT_ASC, 'user_datos.apellidos' => SORT_ASC],
                        'desc' => ['user_datos.nombres' => SORT_DESC, 'user_datos.apellidos' => SORT_DESC],
                        'label' => 'Nombre',
                    ],
                    'agenteFuerzaUserCedula' => [
                        'asc' => ['user_datos.cedula' => SORT_ASC],
                        'desc' => ['user_datos.cedula' => SORT_DESC],
                        'label' => 'Cédula',
                    ],
                    'agenteFuerzaUserEmail' => [
                        'asc' => ['user_datos.email' => SORT_ASC],
                        'desc' => ['user_datos.email' => SORT_DESC],
                        'label' => 'Correo',
                    ],
                    'agenteFuerzaUserTelefono' => [
                        'asc' => ['user_datos.telefono' => SORT_ASC],
                        'desc' => ['user_datos.telefono' => SORT_DESC],
                        'label' => 'Teléfono',
                    ],
                    'registro_corredor_actividad_aseguradora' => [
                        'asc' => ['agente_fuerza.registro_corredor_actividad_aseguradora' => SORT_ASC],
                        'desc' => ['agente_fuerza.registro_corredor_actividad_aseguradora' => SORT_DESC],
                        'label' => 'Código SUDEASEG',
                    ],
                ],
            ],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'agente_fuerza.id' => $this->id,
            'agente_fuerza.idusuario' => $this->idusuario,
            'agente_fuerza.agente_id' => $this->agente_id,
            'agente_fuerza.por_venta' => $this->por_venta,
            'agente_fuerza.por_asesor' => $this->por_asesor,
            'agente_fuerza.por_cobranza' => $this->por_cobranza,
            'agente_fuerza.por_post_venta' => $this->por_post_venta,
            'agente_fuerza.puede_vender' => $this->puede_vender,
            'agente_fuerza.puede_asesorar' => $this->puede_asesorar,
            'agente_fuerza.puede_cobrar' => $this->puede_cobrar,
            'agente_fuerza.puede_post_venta' => $this->puede_post_venta,
            'agente_fuerza.created_at' => $this->created_at,
            'agente_fuerza.updated_at' => $this->updated_at,
            'agente_fuerza.deleted_at' => $this->deleted_at,
            'agente_fuerza.puede_registrar' => $this->puede_registrar,
            'agente_fuerza.por_registrar' => $this->por_registrar,
        ]);

        // Add search conditions for user data
        if (!empty($this->agenteFuerzaUserNombres)) {
            $query->andFilterWhere([
                'or',
                ['ilike', 'user_datos.nombres', $this->agenteFuerzaUserNombres],
                ['ilike', 'user_datos.apellidos', $this->agenteFuerzaUserNombres],
                ['ilike', 'concat(user_datos.nombres, \' \', user_datos.apellidos)', $this->agenteFuerzaUserNombres]
            ]);
        }

        if (!empty($this->agenteFuerzaUserCedula)) {
            $query->andFilterWhere(['ilike', 'user_datos.cedula', $this->agenteFuerzaUserCedula]);
        }

        if (!empty($this->agenteFuerzaUserEmail)) {
            $query->andFilterWhere(['ilike', 'user_datos.email', $this->agenteFuerzaUserEmail]);
        }

        if (!empty($this->agenteFuerzaUserTelefono)) {
            $query->andFilterWhere(['ilike', 'user_datos.telefono', $this->agenteFuerzaUserTelefono]);
        }

        // Add search condition for registro_corredor_actividad_aseguradora (SUDEASEG)
        if (!empty($this->registro_corredor_actividad_aseguradora)) {
            $query->andFilterWhere(['ilike', 'agente_fuerza.registro_corredor_actividad_aseguradora', $this->registro_corredor_actividad_aseguradora]);
        }

        // Force ORDER BY ID ASC at the query level as a fallback
        $query->orderBy(['agente_fuerza.id' => SORT_ASC]);

        return $dataProvider;
    }
}
