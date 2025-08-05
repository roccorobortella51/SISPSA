<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Planes;
use Yii;

/**
 * PlanesSearch represents the model behind the search form of `app\models\Planes`.
 */
class PlanesSearch extends Planes
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'clinica_id', 'cobertura', 'edad_limite'], 'integer'],
            [['created_at', 'nombre', 'descripcion', 'estatus', 'nota', 'tipo', 'PDF', 'deleted_at', 'updated_at'], 'safe'],
            [['precio', 'comision', 'edad_minima'], 'number'],
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
        $query = Planes::find()->select('*');

        // add conditions that should always apply here

        if(Yii::$app->request->get('per_page') == ""){
            $paginas = 20;
        }else{
            $paginas = 20;
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
               'defaultOrder' => ['created_at' => SORT_DESC]
             ],
            'pagination' => ['pageSize' => $paginas ],
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
            'precio' => $this->precio,
            'clinica_id' => $this->clinica_id,
            'cobertura' => $this->cobertura,
            'comision' => $this->comision,
            'edad_minima' => $this->edad_minima,
            'edad_limite' => $this->edad_limite,
            'deleted_at' => $this->deleted_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['ilike', 'nombre', $this->nombre])
            ->andFilterWhere(['ilike', 'descripcion', $this->descripcion])
            ->andFilterWhere(['ilike', 'estatus', $this->estatus])
            ->andFilterWhere(['ilike', 'nota', $this->nota])
            ->andFilterWhere(['ilike', 'tipo', $this->tipo])
            ->andFilterWhere(['ilike', 'PDF', $this->PDF]);

        return $dataProvider;
    }
}
