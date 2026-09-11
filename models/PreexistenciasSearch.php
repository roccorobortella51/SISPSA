<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Preexistencias;
use app\components\UserHelper;
use Yii;

/**
 * PreexistenciasSearch represents the model behind the search form of `app\models\Preexistencias`.
 */
class PreexistenciasSearch extends Preexistencias
{
    public $afiliado_nombre;
    public $afiliado_cedula;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'sis_siniestro_id'], 'integer'],
            [['nombre', 'descripcion', 'fecha_diagnostico', 'medico_tratante', 'medicamentos', 'observaciones', 'estatus', 'created_at', 'updated_at', 'deleted_at', 'afiliado_nombre', 'afiliado_cedula'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param int|null $user_id Filter by specific user
     *
     * @return ActiveDataProvider
     */
    public function search($params, $user_id = null)
    {
        $query = Preexistencias::find()
            ->alias('preexistencias')
            ->joinWith(['afiliado' => function ($q) {
                $q->alias('user_datos');
            }])
            ->where(['IS', 'preexistencias.deleted_at', null]);

        // Clinic access filtering
        if (UserHelper::hasClinicAccess()) {
            $clinicId = UserHelper::getMyClinicaId();
            if ($clinicId) {
                $query->andWhere(['user_datos.clinica_id' => $clinicId]);
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC, // Usar el nombre del atributo, no la columna
                ],
                'attributes' => [
                    'id' => [
                        'asc' => ['preexistencias.id' => SORT_ASC],
                        'desc' => ['preexistencias.id' => SORT_DESC],
                    ],
                    'nombre' => [
                        'asc' => ['preexistencias.nombre' => SORT_ASC],
                        'desc' => ['preexistencias.nombre' => SORT_DESC],
                    ],
                    'estatus' => [
                        'asc' => ['preexistencias.estatus' => SORT_ASC],
                        'desc' => ['preexistencias.estatus' => SORT_DESC],
                    ],
                    'fecha_diagnostico' => [
                        'asc' => ['preexistencias.fecha_diagnostico' => SORT_ASC],
                        'desc' => ['preexistencias.fecha_diagnostico' => SORT_DESC],
                    ],
                    'created_at' => [
                        'asc' => ['preexistencias.created_at' => SORT_ASC],
                        'desc' => ['preexistencias.created_at' => SORT_DESC],
                    ],
                    'afiliado_nombre' => [
                        'asc' => ['user_datos.nombres' => SORT_ASC],
                        'desc' => ['user_datos.nombres' => SORT_DESC],
                    ],
                    'afiliado_cedula' => [
                        'asc' => ['user_datos.cedula' => SORT_ASC],
                        'desc' => ['user_datos.cedula' => SORT_DESC],
                    ],
                ]
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Filter by specific user if provided
        if ($user_id) {
            $query->andWhere(['preexistencias.user_id' => $user_id]);
        }

        // Grid filtering conditions
        $query->andFilterWhere([
            'preexistencias.id' => $this->id,
            'preexistencias.user_id' => $this->user_id,
            'preexistencias.sis_siniestro_id' => $this->sis_siniestro_id,
            'preexistencias.estatus' => $this->estatus,
            'preexistencias.fecha_diagnostico' => $this->fecha_diagnostico,
        ]);

        // Text filters with ILIKE
        $query->andFilterWhere(['ilike', 'preexistencias.nombre', $this->nombre])
            ->andFilterWhere(['ilike', 'preexistencias.descripcion', $this->descripcion])
            ->andFilterWhere(['ilike', 'preexistencias.medico_tratante', $this->medico_tratante])
            ->andFilterWhere(['ilike', 'preexistencias.medicamentos', $this->medicamentos])
            ->andFilterWhere(['ilike', 'preexistencias.observaciones', $this->observaciones])
            ->andFilterWhere(['ilike', 'user_datos.nombres', $this->afiliado_nombre])
            ->andFilterWhere(['ilike', 'CAST(user_datos.cedula AS TEXT)', $this->afiliado_cedula]);

        return $dataProvider;
    }

    /**
     * Get summary statistics for pre-existences
     */
    public function getSummaryStatistics($user_id = null)
    {
        // Usar consulta directa para evitar ambigüedad
        $query = Preexistencias::find()
            ->alias('preexistencias')
            ->where(['IS', 'preexistencias.deleted_at', null]);

        if ($user_id) {
            $query->andWhere(['preexistencias.user_id' => $user_id]);
        }

        if (UserHelper::hasClinicAccess()) {
            $clinicId = UserHelper::getMyClinicaId();
            if ($clinicId) {
                $query->joinWith(['afiliado' => function ($q) {
                    $q->alias('user_datos');
                }])
                    ->andWhere(['user_datos.clinica_id' => $clinicId]);
            }
        }

        return [
            'total' => $query->count(),
            'activos' => (clone $query)->andWhere(['preexistencias.estatus' => Preexistencias::ESTATUS_ACTIVO])->count(),
            'tratamiento' => (clone $query)->andWhere(['preexistencias.estatus' => Preexistencias::ESTATUS_TRATAMIENTO])->count(),
            'remision' => (clone $query)->andWhere(['preexistencias.estatus' => Preexistencias::ESTATUS_EN_REMISION])->count(),
        ];
    }
}
