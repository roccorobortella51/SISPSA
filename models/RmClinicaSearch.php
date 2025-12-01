<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\RmClinica;
use Yii;
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
            // Mantener 'safe' para todos los campos que se usan en el filtro
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
        // 1. Asignamos alias 't' a la tabla principal 'rm_clinica'
        $query = RmClinica::find()->alias('t');

        // 2. Proyección de columnas: Seleccionamos solo las columnas necesarias para el GridView
        // ID, Nombre, RIF, Teléfono, Correo, Estado, Estatus (y las de control de tiempo si son necesarias)
        $query->select([
            't.id',
            't.nombre',
            't.rif',
            't.telefono',
            't.correo',
            't.estado',
            't.estatus',
            't.created_at', 
        ]);


        // add conditions that should always apply here

        if(Yii::$app->request->get('per_page') == ""){
            $paginas = 20;
        }else{
            // Nota: Es mejor usar $paginas = Yii::$app->request->get('per_page') si quieres que el parámetro funcione, 
            // pero si siempre quieres 20, tu lógica actual está bien.
            $paginas = 20;
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
               // Usamos alias 't.created_at' para la ordenación por defecto
               'defaultOrder' => ['created_at' => SORT_DESC],
               // Definir el resto de atributos para ordenar si es necesario.
               // Si no se definen aquí, Yii2 asume que son atributos de 't'.
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
        // Usamos 't.id' en las condiciones
        $query->andFilterWhere([
            't.id' => $this->id,
            't.created_at' => $this->created_at,
            't.deleted_at' => $this->deleted_at,
            't.updated_at' => $this->updated_at,
        ]);

        // Los siguientes filtros ilike usan nombres de columna simples (ej. 'rif'),
        // por lo que Yii2 los califica automáticamente con el alias 't' debido a la configuración previa.
        $query->andFilterWhere(['ilike', 'rif', $this->rif])
            ->andFilterWhere(['ilike', 'nombre', $this->nombre])
            ->andFilterWhere(['ilike', 'estado', $this->estado])
            // Solo incluimos las columnas que son necesarias para la búsqueda o la visualización.
            // La columna 'direccion' no se muestra pero se puede buscar.
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