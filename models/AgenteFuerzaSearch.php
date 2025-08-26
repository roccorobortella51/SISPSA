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
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idusuario', 'agente_id', 'puede_vender', 'puede_asesorar', 'puede_cobrar', 'puede_post_venta', 'puede_registrar'], 'integer'],
            [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_registrar'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
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

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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

        return $dataProvider;
    }
}
