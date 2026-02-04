<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Corporativo;

/**
 * CorporativoSearch represents the model behind the search form of `app\models\Corporativo`.
 */
class CorporativoSearch extends Corporativo
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['nombre', 'email', 'telefono', 'rif', 'estado', 'municipio', 'parroquia', 'direccion', 'codigo_asesor', 'lugar_registro', 'fecha_registro_mercantil', 'tomo_registro', 'folio_registro', 'domicilio_fiscal', 'contacto_nombre', 'contacto_cedula', 'contacto_telefono', 'contacto_cargo', 'estatus', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
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
        $query = Corporativo::find()->alias('t')->with(['users', 'clinicas']);

        // INICIO DE LA OPTIMIZACIÓN (Proyección de Columnas)
        // el alias 't' en el select para referenciar las columnas
        $query->select([
            't.id',
            't.nombre',
            't.rif',
            't.email',
            't.telefono',
            't.estatus',
        ]);
        // 🛑 FIN DE LA OPTIMIZACIÓN

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
            'fecha_registro_mercantil' => $this->fecha_registro_mercantil,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ]);

        $query->andFilterWhere(['ilike', 'nombre', $this->nombre])
            ->andFilterWhere(['ilike', 'email', $this->email])
            ->andFilterWhere(['ilike', 'telefono', $this->telefono])
            ->andFilterWhere(['ilike', 'rif', $this->rif])
            ->andFilterWhere(['ilike', 'estado', $this->estado])
            ->andFilterWhere(['ilike', 'municipio', $this->municipio])
            ->andFilterWhere(['ilike', 'parroquia', $this->parroquia])
            ->andFilterWhere(['ilike', 'direccion', $this->direccion])
            ->andFilterWhere(['ilike', 'codigo_asesor', $this->codigo_asesor])
            ->andFilterWhere(['ilike', 'lugar_registro', $this->lugar_registro])
            ->andFilterWhere(['ilike', 'tomo_registro', $this->tomo_registro])
            ->andFilterWhere(['ilike', 'folio_registro', $this->folio_registro])
            ->andFilterWhere(['ilike', 'domicilio_fiscal', $this->domicilio_fiscal])
            ->andFilterWhere(['ilike', 'contacto_nombre', $this->contacto_nombre])
            ->andFilterWhere(['ilike', 'contacto_cedula', $this->contacto_cedula])
            ->andFilterWhere(['ilike', 'contacto_telefono', $this->contacto_telefono])
            ->andFilterWhere(['ilike', 'contacto_cargo', $this->contacto_cargo])
            ->andFilterWhere(['ilike', 'estatus', $this->estatus]);

        return $dataProvider;
    }
}
