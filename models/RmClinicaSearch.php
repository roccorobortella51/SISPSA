<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\RmClinica;

/**
 * RmClinicaSearch represents the model behind the search form of `app\models\RmClinica`.
 */
class RmClinicaSearch extends RmClinica
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['created_at', 'rif', 'nombre', 'estado', 'direccion', 'telefono', 'correo', 'estatus', 'webpage', 'rs_instagram', 'QRCode', 'codigo_clinica', 'deleted_at', 'updated_at', 'private_key'], 'safe'],
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
        $query = RmClinica::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 5, // Establece el tamaño de página a 5
            ],
        ]);
        
        $this->load($params, $formName);

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
        ]);

        $query->andFilterWhere(['ilike', 'rif', $this->rif])
            ->andFilterWhere(['ilike', 'nombre', $this->nombre])
            ->andFilterWhere(['ilike', 'estado', $this->estado])
            ->andFilterWhere(['ilike', 'direccion', $this->direccion])
            ->andFilterWhere(['ilike', 'telefono', $this->telefono])
            ->andFilterWhere(['ilike', 'correo', $this->correo])
            ->andFilterWhere(['ilike', 'estatus', $this->estatus])
            ->andFilterWhere(['ilike', 'webpage', $this->webpage])
            ->andFilterWhere(['ilike', 'rs_instagram', $this->rs_instagram])
            ->andFilterWhere(['ilike', 'QRCode', $this->QRCode])
            ->andFilterWhere(['ilike', 'codigo_clinica', $this->codigo_clinica])
            ->andFilterWhere(['ilike', 'private_key', $this->private_key]);

        return $dataProvider;
    }
}
