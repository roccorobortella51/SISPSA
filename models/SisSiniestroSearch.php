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
    public $admission_analyst;
    public $nombre_doctor;  // ← ADD THIS LINE


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idclinica', 'idbaremo', 'atendido', 'iduser'], 'integer'],
            [['fecha', 'hora', 'fecha_atencion', 'hora_atencion', 'descripcion', 'created_at', 'updated_at', 'deleted_at', 'afiliado_nombre', 'afiliado_cedula', 'admission_analyst', 'nombre_doctor'], 'safe'],  // ← ADD nombre_doctor
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
        // Simple query without GROUP BY to properly retrieve all fields including admission_analyst
        $query = SisSiniestro::find();

        // Aplicamos las condiciones de búsqueda
        $this->load($params);

        // Configuramos el data provider
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'fecha' => SORT_DESC,
                    'hora' => SORT_DESC,
                ],
                'attributes' => [
                    'id',
                    'idclinica',
                    'fecha',
                    'hora',
                    'atendido',
                    'fecha_atencion',
                    'hora_atencion',
                    'costo_total',
                    'admission_analyst',
                    'nombre_doctor',  // ← ADD THIS LINE

                    'created_at',
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

        // Filtro por baremo si se especifica - using EXISTS subquery instead of JOIN + GROUP BY
        if ($this->idbaremo) {
            $query->andWhere([
                'exists',
                (new \yii\db\Query())
                    ->select('1')
                    ->from('sis_siniestro_baremo sb')
                    ->where('sb.siniestro_id = sis_siniestro.id')
                    ->andWhere(['sb.baremo_id' => $this->idbaremo])
            ]);
        }

        // Filtros de texto
        $query->andFilterWhere(['ilike', 'sis_siniestro.hora', $this->hora])
            ->andFilterWhere(['ilike', 'sis_siniestro.hora_atencion', $this->hora_atencion])
            ->andFilterWhere(['ilike', 'sis_siniestro.descripcion', $this->descripcion])
            ->andFilterWhere(['ilike', 'sis_siniestro.admission_analyst', $this->admission_analyst]);

        // Filtros de fecha adicionales
        $query->andFilterWhere(['>=', 'sis_siniestro.fecha_atencion', $this->fecha_atencion])
            ->andFilterWhere(['>=', 'sis_siniestro.created_at', $this->created_at])
            ->andFilterWhere(['>=', 'sis_siniestro.updated_at', $this->updated_at])
            ->andFilterWhere(['>=', 'sis_siniestro.deleted_at', $this->deleted_at]);

        return $dataProvider;
    }

    public function searchClinica($params)
    {
        $query = SisSiniestro::find()
            ->joinWith(['clinica', 'afiliado']);

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
            $query->andWhere([
                'exists',
                (new \yii\db\Query())
                    ->select('1')
                    ->from('sis_siniestro_baremo sb')
                    ->where('sb.siniestro_id = sis_siniestro.id')
                    ->andWhere(['sb.baremo_id' => $this->idbaremo])
            ]);
        }

        if (!empty($this->afiliado_nombre)) {
            $query->andFilterWhere([
                'or',
                ['ilike', 'user_datos.nombres', $this->afiliado_nombre],
                ['ilike', 'user_datos.apellidos', $this->afiliado_nombre]
            ]);
        }

        if (!empty($this->afiliado_cedula)) {
            $query->andFilterWhere([
                'or',
                ['ilike', 'user_datos.cedula', $this->afiliado_cedula],
                ['ilike', 'user_datos.tipo_cedula', $this->afiliado_cedula]
            ]);
        }

        // Filtros de texto
        $query->andFilterWhere(['ilike', 'sis_siniestro.hora', $this->hora])
            ->andFilterWhere(['ilike', 'sis_siniestro.hora_atencion', $this->hora_atencion])
            ->andFilterWhere(['ilike', 'sis_siniestro.descripcion', $this->descripcion])
            ->andFilterWhere(['ilike', 'sis_siniestro.admission_analyst', $this->admission_analyst]);

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
