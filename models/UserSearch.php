<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\User;
use app\models\UserDatos; 
use app\components\UserHelper; 


/**
 * UserSearch represents the model behind the search form of `app\models\User`.
 */
class UserSearch extends User
{

    public $roleName;
    public $idasesor;


    public $nombrecompleto;
    public $agencia;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'auth_key', 'password_hash', 'password_reset_token', 'email', 'nombrecompleto','roleName', 'agencia'], 'safe'],
            [['status', 'created_at', 'updated_at', 'id', 'idasesor'], 'integer'],
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
        $rol = UserHelper::getMyRol();

        $query = User::find();
        $query->innerJoinWith(['userDatos']);


        if($rol == "GERENTE-COMERCIALIZACION" || $rol == "Agente"){
            $query->leftJoin('auth_assignment', '"user"."id" = CAST("auth_assignment"."user_id" AS INTEGER)')
            ->andFilterWhere(['auth_assignment.item_name' => "Asesor"]);
        }

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ],
                // Permite ordenar por roleName, apuntando a la columna real en la tabla unida
                'attributes' => array_merge(parent::attributes(), [
                    'roleName' => [
                         'asc' => ['user_datos.role' => SORT_ASC],
                        'desc' => ['user_datos.role' => SORT_DESC],
                    ],
                ]),
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
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user.id' => $this->id,
        ]);

        $query->andFilterWhere(['ilike', 'username', $this->username])
            ->andFilterWhere(['ilike', 'auth_key', $this->auth_key])
            ->andFilterWhere(['ilike', 'password_hash', $this->password_hash])
            ->andFilterWhere(['ilike', 'password_reset_token', $this->password_reset_token])
            ->andFilterWhere(['ilike', 'email', $this->email])
            ->andFilterWhere(['like', 'user_datos.role', $this->roleName]);

                // Filtro para el nombre completo
        if (!empty($this->nombrecompleto)) { // <-- AÑADE ESTO
            $query->andFilterWhere(['or',
                ['ilike', 'user_datos.nombres', $this->nombrecompleto],
                ['ilike', 'user_datos.apellidos', $this->nombrecompleto]
            ]);
        }

        return $dataProvider;
    }

    public function searchAgentes($params, $formName = null)
    {
        $rol = UserHelper::getMyRol();

        $query = User::find();
        $query->innerJoinWith(['userDatos']);
        $query->joinWith(['userDatos.asesor']);
        $query->joinWith(['userDatos.asesor.agente']);


        if($rol == "DIRECTOR-COMERCIALIZACIÓN" || $rol == "Agente" || $rol == "admin" || $rol == "superadmin"){
            $query->leftJoin('auth_assignment', '"user"."id" = CAST("auth_assignment"."user_id" AS INTEGER)')
            ->andFilterWhere(['auth_assignment.item_name' => "Asesor"]);
        }

        if($rol == "Agente"){
            
            $query->andFilterWhere(['agente_fuerza.agente_id' => UserHelper::getAgenteId()]);
        }

        
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ],
                // Permite ordenar por roleName, apuntando a la columna real en la tabla unida
                'attributes' => array_merge(parent::attributes(), [
                    'roleName' => [
                         'asc' => ['user_datos.role' => SORT_ASC],
                        'desc' => ['user_datos.role' => SORT_DESC],
                    ],
                ]),
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
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user.id' => $this->id,
            'agente_fuerza.id' => $this->idasesor
        ]);

        $query->andFilterWhere(['ilike', 'username', $this->username])
            ->andFilterWhere(['ilike', 'auth_key', $this->auth_key])
            ->andFilterWhere(['ilike', 'password_hash', $this->password_hash])
            ->andFilterWhere(['ilike', 'password_reset_token', $this->password_reset_token])
            ->andFilterWhere(['ilike', 'email', $this->email])
            ->andFilterWhere(['like', 'user_datos.role', $this->roleName])
            ->andFilterWhere(['like', 'agente.nom', $this->agencia]);

                // Filtro para el nombre completo
        if (!empty($this->nombrecompleto)) { // <-- AÑADE ESTO
            $query->andFilterWhere(['or',
                ['ilike', 'user_datos.nombres', $this->nombrecompleto],
                ['ilike', 'user_datos.apellidos', $this->nombrecompleto]
            ]);
        }

        return $dataProvider;
    }
}
