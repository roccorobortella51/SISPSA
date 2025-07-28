<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\SisSiniestro;

/**
 * SisSiniestroSearch represents the model behind the search form of `app\models\SisSiniestro`.
 */
class SisSiniestroSearch extends SisSiniestro
{
    public $iduser;
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idclinica', 'idbaremo', 'atendido', 'iduser'], 'integer'],
            [['fecha', 'hora', 'fecha_atencion', 'hora_atencion', 'descripcion', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
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
        $query = SisSiniestro::find();

        // add conditions that should always apply here
        if ($this->iduser) {
            $query->andWhere(['iduser' => $this->iduser]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'fecha' => SORT_DESC,
                    'hora' => SORT_DESC,
                ]
            ],
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
            'idclinica' => $this->idclinica,
            'fecha' => $this->fecha,
            'idbaremo' => $this->idbaremo,
            'atendido' => $this->atendido,
            'fecha_atencion' => $this->fecha_atencion,
            'iduser' => $this->iduser,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ]);

        $query->andFilterWhere(['ilike', 'hora', $this->hora])
            ->andFilterWhere(['ilike', 'hora_atencion', $this->hora_atencion])
            ->andFilterWhere(['ilike', 'descripcion', $this->descripcion]);

        return $dataProvider;
    }

    public function getBaremo()
    {
        return $this->hasOne(Baremo::class, ['id' => 'idbaremo']);
    }
}