<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Pagos;

/**
 * PagosSearch represents the model behind the search form of `app\models\Pagos`.
 */
class PagosSearch extends Pagos
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'recibo_id', 'user_id', 'conciliador_id', 'conciliado'], 'integer'],
            [['created_at', 'fecha_pago', 'metodo_pago', 'estatus', 'numero_referencia_pago', 'updated_at', 'imagen_prueba', 'nombre_conciliador', 'fecha_conciliacion', 'fecha_registro', 'deleted_at'], 'safe'],
            [['monto_pagado', 'monto_usd'], 'number'],
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
        $query = Pagos::find();

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
            'recibo_id' => $this->recibo_id,
            'fecha_pago' => $this->fecha_pago,
            'monto_pagado' => $this->monto_pagado,
            'updated_at' => $this->updated_at,
            'user_id' => $this->user_id,
            'fecha_conciliacion' => $this->fecha_conciliacion,
            'fecha_registro' => $this->fecha_registro,
            'deleted_at' => $this->deleted_at,
            'conciliador_id' => $this->conciliador_id,
            'conciliado' => $this->conciliado,
            'monto_usd' => $this->monto_usd,
        ]);

        $query->andFilterWhere(['ilike', 'metodo_pago', $this->metodo_pago])
            ->andFilterWhere(['ilike', 'estatus', $this->estatus])
            ->andFilterWhere(['ilike', 'numero_referencia_pago', $this->numero_referencia_pago])
            ->andFilterWhere(['ilike', 'imagen_prueba', $this->imagen_prueba])
            ->andFilterWhere(['ilike', 'nombre_conciliador', $this->nombre_conciliador]);

        return $dataProvider;
    }
}
