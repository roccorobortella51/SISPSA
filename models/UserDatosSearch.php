<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\UserDatos;

/**
 * UserDatosSearch represents the model behind the search form of `app\models\UserDatos`.
 */
class UserDatosSearch extends UserDatos
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'clinica_id', 'plan_id', 'contrato_id', 'asesor_id', 'cedula', 'user_login_id'], 'integer'],
            [['created_at', 'user_id', 'nombres', 'fechanac', 'sexo', 'selfie', 'telefono', 'estado', 'role', 'estatus', 'imagen_identificacion', 'qr', 'video', 'ciudad', 'municipio', 'parroquia', 'direccion', 'codigoValidacion', 'apellidos', 'email', 'deleted_at', 'updated_at', 'ver_cedula', 'ver_foto', 'session_id', 'tipo_cedula', 'tipo_sangre', 'estatus_solvente'], 'safe'],
            [['paso'], 'number'],
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
        $query = UserDatos::find();

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
            'fechanac' => $this->fechanac,
            'paso' => $this->paso,
            'clinica_id' => $this->clinica_id,
            'plan_id' => $this->plan_id,
            'contrato_id' => $this->contrato_id,
            'asesor_id' => $this->asesor_id,
            'deleted_at' => $this->deleted_at,
            'updated_at' => $this->updated_at,
            'cedula' => $this->cedula,
            'user_login_id' => $this->user_login_id,
        ]);

        $query->andFilterWhere(['ilike', 'user_id', $this->user_id])
            ->andFilterWhere(['ilike', 'nombres', $this->nombres])
            ->andFilterWhere(['ilike', 'sexo', $this->sexo])
            ->andFilterWhere(['ilike', 'selfie', $this->selfie])
            ->andFilterWhere(['ilike', 'telefono', $this->telefono])
            ->andFilterWhere(['ilike', 'estado', $this->estado])
            ->andFilterWhere(['ilike', 'role', $this->role])
            ->andFilterWhere(['ilike', 'estatus', $this->estatus])
            ->andFilterWhere(['ilike', 'imagen_identificacion', $this->imagen_identificacion])
            ->andFilterWhere(['ilike', 'qr', $this->qr])
            ->andFilterWhere(['ilike', 'video', $this->video])
            ->andFilterWhere(['ilike', 'ciudad', $this->ciudad])
            ->andFilterWhere(['ilike', 'municipio', $this->municipio])
            ->andFilterWhere(['ilike', 'parroquia', $this->parroquia])
            ->andFilterWhere(['ilike', 'direccion', $this->direccion])
            ->andFilterWhere(['ilike', 'codigoValidacion', $this->codigoValidacion])
            ->andFilterWhere(['ilike', 'apellidos', $this->apellidos])
            ->andFilterWhere(['ilike', 'email', $this->email])
            ->andFilterWhere(['ilike', 'ver_cedula', $this->ver_cedula])
            ->andFilterWhere(['ilike', 'ver_foto', $this->ver_foto])
            ->andFilterWhere(['ilike', 'session_id', $this->session_id])
            ->andFilterWhere(['ilike', 'tipo_cedula', $this->tipo_cedula])
            ->andFilterWhere(['ilike', 'tipo_sangre', $this->tipo_sangre])
            ->andFilterWhere(['ilike', 'estatus_solvente', $this->estatus_solvente]);

        return $dataProvider;
    }
}
