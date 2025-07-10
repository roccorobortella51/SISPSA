<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\AgenteFuerza;
use app\models\User;
use app\models\UserDatos; 

/**
 * AgenteFuerzaSearch represents the model behind the search form of `app\models\AgenteFuerza`.
 */
class AgenteFuerzaSearch extends AgenteFuerza
{

    public $user_nombres;
    public $user_email;
    public $user_telefono;
    public $propietarioNombreCompleto;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idusuario', 'agente_id', 'puede_vender', 'puede_asesorar', 'puede_cobrar', 'puede_post_venta', 'puede_registrar','propietarioNombreCompleto'], 'integer'],
            [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_registrar'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
             [['user_nombres', 'user_email', 'user_telefono', 'propietarioNombreCompleto'], 'safe'],
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
        
        $query->joinWith(['agente']); 
        $query->leftJoin('user p', 'p.id = agente.idusuariopropietario');
        $query->leftJoin('user_datos pd', 'pd.user_login_id = p.id');

        // add conditions that should always apply here
        $query->innerJoinWith([
            'user' => function ($query) {
                $query->innerJoinWith('userDatos');
            }
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],

             'sort' => [
                'attributes' => [
                    // Atributos de tu modelo AgenteFuerza (los que ya existen en la tabla agente_fuerza)
                    'id',
                    'idusuario',
                    'agente_id',
                    'por_venta',
                    'por_asesor',
                    'por_cobranza',
                    'por_post_venta',
                    'puede_vender',
                    'puede_asesorar',
                    'puede_cobrar',
                    'puede_post_venta',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                    'puede_registrar',
                    'por_registrar',

                    // Atributos relacionados (los que creamos para ordenar por user)
                    'propietarioNombreCompleto' => [
                        'asc' => ['p.nombres' => SORT_ASC, 'p.apellidos' => SORT_ASC],
                        'desc' => ['p.nombres' => SORT_DESC, 'p.apellidos' => SORT_DESC],
                        'label' => 'Propietario',
                    ],

                    // Atributos relacionados (los que creamos para ordenar por userDatos)
                    'user_nombres' => [
                        'asc' => ['user_datos.nombres' => SORT_ASC],
                        'desc' => ['user_datos.nombres' => SORT_DESC],
                        'label' => 'Nombre',
                    ],
                    'user_email' => [
                        'asc' => ['user_datos.email' => SORT_ASC],
                        'desc' => ['user_datos.email' => SORT_DESC],
                        'label' => 'Correo Electrónico',
                    ],
                    'user_telefono' => [
                        'asc' => ['user_datos.telefono' => SORT_ASC],
                        'desc' => ['user_datos.telefono' => SORT_DESC],
                        'label' => 'Teléfono',
                    ],
                ],
                // Opcional: Establecer el atributo de ordenamiento por defecto
                // 'defaultOrder' => ['user_nombres' => SORT_ASC],
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
            'id' => $this->id,
            //'agente.idusuariopropietario' => $this->idusuariopropietario,
            'idusuario' => $this->idusuario,
            'agente_id' => $this->agente_id,
            'por_venta' => $this->por_venta,
            'por_asesor' => $this->por_asesor,
            'por_cobranza' => $this->por_cobranza,
            'por_post_venta' => $this->por_post_venta,
            'puede_vender' => $this->puede_vender,
            'puede_asesorar' => $this->puede_asesorar,
            'puede_cobrar' => $this->puede_cobrar,
            'puede_post_venta' => $this->puede_post_venta,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'puede_registrar' => $this->puede_registrar,
            'por_registrar' => $this->por_registrar,
        ]);

        $query->andFilterWhere(['ilike', 'user_datos.nombres', $this->user_nombres]);
        $query->andFilterWhere(['ilike', 'user_datos.email', $this->user_email]);
        $query->andFilterWhere(['ilike', 'user_datos.telefono', $this->user_telefono]);

        if (!empty($this->propietarioNombreCompleto)) {
            $query->andFilterWhere(['or',
                ['ilike', 'pd.nombres', $this->propietarioNombreCompleto],
                ['ilike', 'pd.apellidos', $this->propietarioNombreCompleto]
            ]);
        }

        // Filtro para el nombre del AGENTE de la FUERZA DE VENTA (si usas agenteFuerzaNombreCompleto)
        if (!empty($this->agenteFuerzaNombreCompleto)) {
            $query->andFilterWhere(['or',
                ['ilike', 'user_datos.nombres', $this->agenteFuerzaNombreCompleto],
                ['ilike', 'user_datos.apellidos', $this->agenteFuerzaNombreCompleto]
            ]);
        }

        return $dataProvider;
    }
}
