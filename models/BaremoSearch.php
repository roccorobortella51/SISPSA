<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Baremo;

/**
 * BaremoSearch represents the model behind the search form of `app\models\Baremo`.
 */
class BaremoSearch extends Baremo
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'clinica_id', 'area_id'], 'integer'],
            [['created_at', 'nombre_servicio', 'descripcion', 'estatus', 'deleted_at', 'updated_at'], 'safe'],
            [['precio', 'costo'], 'number'],
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
        $query = Baremo::find();

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
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
            'updated_at' => $this->updated_at,
            'precio' => $this->precio,
            'clinica_id' => $this->clinica_id,
            'costo' => $this->costo,
            'area_id' => $this->area_id,
        ]);

        $query->andFilterWhere(['ilike', 'nombre_servicio', $this->nombre_servicio])
            ->andFilterWhere(['ilike', 'descripcion', $this->descripcion])
            ->andFilterWhere(['ilike', 'estatus', $this->estatus]);

        return $dataProvider;
    }
}
