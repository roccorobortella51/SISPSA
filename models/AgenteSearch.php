<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Agente;

/**
 * AgenteSearch represents the model behind the search form of `app\models\Agente`.
 */
class AgenteSearch extends Agente
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idusuariopropietario'], 'integer'],
            [['nom', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_agente', 'por_max'], 'number'],
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
        $query = Agente::find();

        // add conditions that should always apply here
        $query->with(['propietario', 'agenteFuerzas']); 

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
            'idusuariopropietario' => $this->idusuariopropietario,
            'por_venta' => $this->por_venta,
            'por_asesor' => $this->por_asesor,
            'por_cobranza' => $this->por_cobranza,
            'por_post_venta' => $this->por_post_venta,
            'por_agente' => $this->por_agente,
            'por_max' => $this->por_max,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ]);

        $query->andFilterWhere(['ilike', 'nom', $this->nom]);

        return $dataProvider;
    }
}
