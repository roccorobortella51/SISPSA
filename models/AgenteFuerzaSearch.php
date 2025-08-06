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
        // Atributos virtuales para los datos del USUARIO (idusuario) del AgenteFuerza
        public $agenteFuerzaUserEmail;
        public $agenteFuerzaUserCedula;
        public $agenteFuerzaUserNombres;
        public $agenteFuerzaUserTelefono;

        // Atributos virtuales para los datos del PROPIETARIO del AGENTE
        public $agentePropietarioNombreCompleto; // Renombrado para mayor claridad
        public $agentePropietarioEmail; // Nuevo atributo virtual para el email del propietario del agente
        public $agentePropietarioCedula; // Nuevo atributo virtual para la cédula del propietario del agente


        /**
         * {@inheritdoc}
         */
        public function rules()
        {
            return [
                [['id', 'idusuario', 'agente_id', 'puede_vender', 'puede_asesorar', 'puede_cobrar', 'puede_post_venta', 'puede_registrar'], 'integer'],
                [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_registrar'], 'number'],
                [['created_at', 'updated_at', 'deleted_at'], 'safe'],
                // Asegúrate que todos los atributos virtuales estén en 'safe'
                [['agenteFuerzaUserEmail', 'agenteFuerzaUserCedula', 'agenteFuerzaUserNombres', 'agenteFuerzaUserTelefono',
                'agentePropietarioNombreCompleto', 'agentePropietarioEmail', 'agentePropietarioCedula'], 'safe'],
                // La regla 'integer' para 'propietarioNombreCompleto' era incorrecta, debe ser 'safe'
                // Ya la hemos movido a la regla 'safe' de arriba.
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

            // 1. Join con Agente
            $query->joinWith(['agente']);

            // 2. Join con el Propietario del Agente (alias 'p')
            $query->leftJoin('user p', 'p.id = agente.idusuariopropietario');
            // 3. Join con los UserDatos del Propietario del Agente (alias 'pd')
            $query->leftJoin('user_datos pd', 'pd.user_login_id = p.id');

            // 4. Join con el Usuario de AgenteFuerza (alias 'user') y sus UserDatos (alias 'userDatos')
            // Este innerJoinWith ya crea los alias 'user' y 'userDatos'
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

                        // Atributos relacionados: Propietario del Agente (usando alias 'p' y 'pd')
                        'agentePropietarioNombreCompleto' => [
                            'asc' => ['pd.nombres' => SORT_ASC, 'pd.apellidos' => SORT_ASC], // Usa 'pd'
                            'desc' => ['pd.nombres' => SORT_DESC, 'pd.apellidos' => SORT_DESC],
                            'label' => 'Propietario (Agente)',
                        ],
                        'agentePropietarioEmail' => [ // Nuevo para email del propietario del agente
                            'asc' => ['p.email' => SORT_ASC], // Usa 'p'
                            'desc' => ['p.email' => SORT_DESC],
                            'label' => 'Email Propietario (Agente)',
                        ],
                        'agentePropietarioCedula' => [ // Nuevo para cédula del propietario del agente
                            'asc' => ['pd.cedula' => SORT_ASC], // Usa 'pd'
                            'desc' => ['pd.cedula' => SORT_DESC],
                            'label' => 'Cédula Propietario (Agente)',
                        ],

                        // Atributos relacionados: Usuario del AgenteFuerza (usando alias 'user' y 'userDatos')
                        'agenteFuerzaUserNombres' => [ 
                            'asc' => ['user_datos.nombres' => SORT_ASC], 
                            'desc' => ['user_datos.nombres' => SORT_DESC],
                            'label' => 'Nombres (Agente Fuerza)',
                        ],
                        'agenteFuerzaUserEmail' => [ // Email del User del AgenteFuerza
                            'asc' => ['user.email' => SORT_ASC], // Usa 'user'
                            'desc' => ['user.email' => SORT_DESC],
                            'label' => 'Correo (Agente Fuerza)',
                        ],
                        'agenteFuerzaUserTelefono' => [ 
                            'asc' => ['user_datos.telefono' => SORT_ASC], // <--- CAMBIADO AQUÍ
                            'desc' => ['user_datos.telefono' => SORT_DESC],
                            'label' => 'Teléfono (Agente Fuerza)',
                        ],
                        'agenteFuerzaUserCedula' => [ // Cédula del UserDatos del AgenteFuerza
                            'asc' => ['user_datos.cedula' => SORT_ASC], // <--- CAMBIADO AQUÍ
                            'desc' => ['user_datos.cedula' => SORT_DESC],
                            'label' => 'Cédula (Agente Fuerza)',
                        ],
                    ],
                    // Opcional: Establecer el atributo de ordenamiento por defecto
                    // 'defaultOrder' => ['agenteFuerzaUserNombres' => SORT_ASC],
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

            // Filtros para el Usuario (idusuario) del AgenteFuerza (usando alias 'user' y 'userDatos')
            $query->andFilterWhere(['ilike', 'user_datos.nombres', $this->agenteFuerzaUserNombres]); 
            $query->andFilterWhere(['ilike', 'user.email', $this->agenteFuerzaUserEmail]);
            $query->andFilterWhere(['ilike', 'user_datos.telefono', $this->agenteFuerzaUserTelefono]); // <--- CAMBIADO AQUÍ
            $query->andFilterWhere(['ilike', 'user_datos.cedula', $this->agenteFuerzaUserCedula]); // <--- CAMBIADO AQUÍ
            // Filtro para el Propietario del Agente (usando alias 'p' y 'pd')
            if (!empty($this->agentePropietarioNombreCompleto)) {
                $query->andFilterWhere(['or',
                    ['ilike', 'pd.nombres', $this->agentePropietarioNombreCompleto],
                    ['ilike', 'pd.apellidos', $this->agentePropietarioNombreCompleto]
                ]);
            }
            // Filtro para el email del Propietario del Agente
            $query->andFilterWhere(['ilike', 'p.email', $this->agentePropietarioEmail]);
            // Filtro para la cédula del Propietario del Agente
            $query->andFilterWhere(['ilike', 'pd.cedula', $this->agentePropietarioCedula]);


            // Filtro para el nombre del AGENTE de la FUERZA DE VENTA (si usas agenteFuerzaNombreCompleto)
            // Este filtro parece ser para el AgenteFuerza, pero usa 'user_datos'.
            // Si 'agenteFuerzaNombreCompleto' se refiere al nombre del AgenteFuerza (el UserDatos asociado),
            // entonces debería usar 'userDatos.nombres' y 'userDatos.apellidos'.
            // Si no necesitas este filtro, puedes eliminarlo.
            /*
            if (!empty($this->agenteFuerzaNombreCompleto)) {
                $query->andFilterWhere(['or',
                    ['ilike', 'userDatos.nombres', $this->agenteFuerzaNombreCompleto],
                    ['ilike', 'userDatos.apellidos', $this->agenteFuerzaNombreCompleto]
                ]);
            }
            */

            return $dataProvider;
        }
    }

?>  