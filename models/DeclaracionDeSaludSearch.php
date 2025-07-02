<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\DeclaracionDeSalud;

/**
 * DeclaracionDeSaludSearch represents the model behind the search form of `app\models\DeclaracionDeSalud`.
 */
class DeclaracionDeSaludSearch extends DeclaracionDeSalud
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'ver_usuario_id', 'user_id'], 'integer'],
            [['created_at', 'p1_sino', 'p1_especifica', 'p2_sino', 'p2_especifica', 'p3_sino', 'p3_especifica', 'p4_sino', 'p4_especifica', 'p5_sino', 'p5_especifica', 'p6_sino', 'p6_especifica', 'p7_sino', 'p7_especifica', 'p8_sino', 'p8_especifica', 'p9_sino', 'p9_especifica', 'p10_sino', 'p10_especifica', 'p11_sino', 'p11_especifica', 'p12_sino', 'p12_especifica', 'p13_sino', 'p13_especifica', 'p14_sino', 'p14_especifica', 'p15_sino', 'p15_especifica', 'p16_sino', 'p16_especifica', 'deleted_at', 'updated_at', 'ver_observacion', 'ver_si_no', 'ver_fecha', 'url_video_declaracion', 'estatus', 'estatura', 'peso'], 'safe'],
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
        $query = DeclaracionDeSalud::find();

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
            'ver_usuario_id' => $this->ver_usuario_id,
            'ver_fecha' => $this->ver_fecha,
            'user_id' => $this->user_id,
        ]);

        $query->andFilterWhere(['ilike', 'p1_sino', $this->p1_sino])
            ->andFilterWhere(['ilike', 'p1_especifica', $this->p1_especifica])
            ->andFilterWhere(['ilike', 'p2_sino', $this->p2_sino])
            ->andFilterWhere(['ilike', 'p2_especifica', $this->p2_especifica])
            ->andFilterWhere(['ilike', 'p3_sino', $this->p3_sino])
            ->andFilterWhere(['ilike', 'p3_especifica', $this->p3_especifica])
            ->andFilterWhere(['ilike', 'p4_sino', $this->p4_sino])
            ->andFilterWhere(['ilike', 'p4_especifica', $this->p4_especifica])
            ->andFilterWhere(['ilike', 'p5_sino', $this->p5_sino])
            ->andFilterWhere(['ilike', 'p5_especifica', $this->p5_especifica])
            ->andFilterWhere(['ilike', 'p6_sino', $this->p6_sino])
            ->andFilterWhere(['ilike', 'p6_especifica', $this->p6_especifica])
            ->andFilterWhere(['ilike', 'p7_sino', $this->p7_sino])
            ->andFilterWhere(['ilike', 'p7_especifica', $this->p7_especifica])
            ->andFilterWhere(['ilike', 'p8_sino', $this->p8_sino])
            ->andFilterWhere(['ilike', 'p8_especifica', $this->p8_especifica])
            ->andFilterWhere(['ilike', 'p9_sino', $this->p9_sino])
            ->andFilterWhere(['ilike', 'p9_especifica', $this->p9_especifica])
            ->andFilterWhere(['ilike', 'p10_sino', $this->p10_sino])
            ->andFilterWhere(['ilike', 'p10_especifica', $this->p10_especifica])
            ->andFilterWhere(['ilike', 'p11_sino', $this->p11_sino])
            ->andFilterWhere(['ilike', 'p11_especifica', $this->p11_especifica])
            ->andFilterWhere(['ilike', 'p12_sino', $this->p12_sino])
            ->andFilterWhere(['ilike', 'p12_especifica', $this->p12_especifica])
            ->andFilterWhere(['ilike', 'p13_sino', $this->p13_sino])
            ->andFilterWhere(['ilike', 'p13_especifica', $this->p13_especifica])
            ->andFilterWhere(['ilike', 'p14_sino', $this->p14_sino])
            ->andFilterWhere(['ilike', 'p14_especifica', $this->p14_especifica])
            ->andFilterWhere(['ilike', 'p15_sino', $this->p15_sino])
            ->andFilterWhere(['ilike', 'p15_especifica', $this->p15_especifica])
            ->andFilterWhere(['ilike', 'p16_sino', $this->p16_sino])
            ->andFilterWhere(['ilike', 'p16_especifica', $this->p16_especifica])
            ->andFilterWhere(['ilike', 'ver_observacion', $this->ver_observacion])
            ->andFilterWhere(['ilike', 'ver_si_no', $this->ver_si_no])
            ->andFilterWhere(['ilike', 'url_video_declaracion', $this->url_video_declaracion])
            ->andFilterWhere(['ilike', 'estatus', $this->estatus])
            ->andFilterWhere(['ilike', 'estatura', $this->estatura])
            ->andFilterWhere(['ilike', 'peso', $this->peso]);

        return $dataProvider;
    }
}
