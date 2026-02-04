<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Contratos;

/**
 * ContratosSearch represents the model behind the search form of `app\models\Contratos`.
 */
class ContratosSearch extends Contratos
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'plan_id', 'ente_id', 'clinica_id', 'anulado_por', 'user_id'], 'integer'],
            [['created_at', 'fecha_ini', 'fecha_ven', 'estatus', 'nrocontrato', 'frecuencia_pago', 'sucursal', 'moneda', 'updated_at', 'deleted_at', 'anulado_fecha', 'anulado_motivo', 'pdf'], 'safe'],
            [['monto'], 'number'],
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
        $query = Contratos::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
            'created_at' => $this->created_at,
            'plan_id' => $this->plan_id,
            'ente_id' => $this->ente_id,
            'clinica_id' => $this->clinica_id,
            'fecha_ini' => $this->fecha_ini,
            'fecha_ven' => $this->fecha_ven,
            'monto' => $this->monto,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'anulado_por' => $this->anulado_por,
            'anulado_fecha' => $this->anulado_fecha,
            'user_id' => $this->user_id,
        ]);

        $query->andFilterWhere(['ilike', 'estatus', $this->estatus])
            ->andFilterWhere(['ilike', 'nrocontrato', $this->nrocontrato])
            ->andFilterWhere(['ilike', 'frecuencia_pago', $this->frecuencia_pago])
            ->andFilterWhere(['ilike', 'sucursal', $this->sucursal])
            ->andFilterWhere(['ilike', 'moneda', $this->moneda])
            ->andFilterWhere(['ilike', 'anulado_motivo', $this->anulado_motivo])
            ->andFilterWhere(['ilike', 'pdf', $this->pdf]);

        return $dataProvider;
    }
}
