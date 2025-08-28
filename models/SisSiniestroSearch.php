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
    public $afiliado_nombre;
    public $afiliado_cedula;
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idclinica', 'idbaremo', 'atendido', 'iduser'], 'integer'],
            [['fecha', 'hora', 'fecha_atencion', 'hora_atencion', 'descripcion', 'created_at', 'updated_at', 'deleted_at', 'afiliado_nombre', 'afiliado_cedula'], 'safe'],
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
        // Primero obtenemos la consulta base con join a la tabla intermedia
        $query = SisSiniestro::find()
            ->select(['sis_siniestro.*'])
            ->joinWith(['sisSiniestroBaremos sb'])
            ->with(['baremos'])
            ->groupBy('sis_siniestro.id');
        
        // Aplicamos las condiciones de búsqueda
        $this->load($params);
        
        // Configuramos el data provider
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'fecha' => SORT_DESC,
                    'hora' => SORT_DESC,
                ]
            ],
        ]);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Agregamos condiciones de búsqueda
        $query->andFilterWhere([
            'sis_siniestro.id' => $this->id,
            'sis_siniestro.idclinica' => $this->idclinica,
            'sis_siniestro.atendido' => $this->atendido,
            'sis_siniestro.iduser' => $this->iduser,
        ]);
        
        // Condiciones de fecha
        if ($this->fecha) {
            $query->andFilterWhere(['>=', 'sis_siniestro.fecha', $this->fecha]);
        }
        
        // Filtro por baremo si se especifica
        if ($this->idbaremo) {
            $query->andFilterWhere(['sb.baremo_id' => $this->idbaremo]);
        }

        // Filtros de texto
        $query->andFilterWhere(['ilike', 'sis_siniestro.hora', $this->hora])
            ->andFilterWhere(['ilike', 'sis_siniestro.hora_atencion', $this->hora_atencion])
            ->andFilterWhere(['ilike', 'sis_siniestro.descripcion', $this->descripcion]);
            
        // Filtros de fecha adicionales
        $query->andFilterWhere(['>=', 'sis_siniestro.fecha_atencion', $this->fecha_atencion])
            ->andFilterWhere(['>=', 'sis_siniestro.created_at', $this->created_at])
            ->andFilterWhere(['>=', 'sis_siniestro.updated_at', $this->updated_at])
            ->andFilterWhere(['>=', 'sis_siniestro.deleted_at', $this->deleted_at]);

        return $dataProvider;
    }

    public function searchClinica($params)
    {
        $query = SisSiniestro::find()->joinWith(['clinica', 'afiliado']);

        $this->load($params);
        
        // Configuramos el data provider
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'fecha' => SORT_DESC,
                    'hora' => SORT_DESC,
                ]
            ],
        ]);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Agregamos condiciones de búsqueda
        $query->andFilterWhere([
            'sis_siniestro.id' => $this->id,
            'sis_siniestro.idclinica' => $this->idclinica,
            'sis_siniestro.atendido' => $this->atendido,
            'sis_siniestro.iduser' => $this->iduser,
        ]);
        
        // Condiciones de fecha
        if ($this->fecha) {
            $query->andFilterWhere(['>=', 'sis_siniestro.fecha', $this->fecha]);
        }
        
        // Filtro por baremo si se especifica
        if ($this->idbaremo) {
            $query->andFilterWhere(['sb.baremo_id' => $this->idbaremo]);
        }

        if (!empty($this->afiliado_nombre)) {
            $query->andFilterWhere(['or',
                ['like', 'user_datos.nombres', $this->afiliado_nombre],
                ['like', 'user_datos.apellidos', $this->afiliado_nombre]
            ]);
        }

        if (!empty($this->afiliado_cedula)) {
            $query->andFilterWhere(['or',
                ['like', 'user_datos.cedula', $this->afiliado_cedula],
                ['like', 'user_datos.tipo_cedula', $this->afiliado_cedula]
            ]);
        }

        // Filtros de texto
        $query->andFilterWhere(['ilike', 'sis_siniestro.hora', $this->hora])
            ->andFilterWhere(['ilike', 'sis_siniestro.hora_atencion', $this->hora_atencion])
            ->andFilterWhere(['ilike', 'sis_siniestro.descripcion', $this->descripcion]);
            
        // Filtros de fecha adicionales
        $query->andFilterWhere(['>=', 'sis_siniestro.fecha_atencion', $this->fecha_atencion])
            ->andFilterWhere(['>=', 'sis_siniestro.created_at', $this->created_at])
            ->andFilterWhere(['>=', 'sis_siniestro.updated_at', $this->updated_at])
            ->andFilterWhere(['>=', 'sis_siniestro.deleted_at', $this->deleted_at]);

        return $dataProvider;

    }

    public function getBaremo()
    {
        return $this->hasOne(Baremo::class, ['id' => 'idbaremo']);
    }
}