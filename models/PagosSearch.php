<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Pagos;

/**
 * PagosSearch represents the model behind the search form of `app\models\Pagos`.
 */
class PagosSearch extends Pagos
{
    public $nombreUsuario;
    public $cedulaUsuario;
    public $observacion;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'recibo_id', 'user_id', 'conciliador_id', 'conciliado'], 'integer'],
            [['fecha_pago', 'monto_pagado', 'monto_usd'], 'number'],
            [['metodo_pago', 'estatus', 'numero_referencia_pago', 'nombre_conciliador', 'fecha_conciliacion', 'fecha_registro', 'nombreUsuario', 'cedulaUsuario', 'observacion'], 'safe'],
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
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Pagos::find()->joinWith(['userDatos']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC], // ✅ Cambiado a 'id'
                'attributes' => [
                    'id',
                    'created_at',
                    'fecha_pago',
                    'monto_pagado',
                    'monto_usd',
                    'estatus',
                    'numero_referencia_pago',
                    'observacion',
                    'metodo_pago',
                    'fecha_conciliacion',
                    'nombre_conciliador',
                    'nombreUsuario' => [
                        'asc' => ['user_datos.nombres' => SORT_ASC, 'user_datos.apellidos' => SORT_ASC],
                        'desc' => ['user_datos.nombres' => SORT_DESC, 'user_datos.apellidos' => SORT_DESC],
                    ],
                    'cedulaUsuario' => [
                        'asc' => ['user_datos.cedula' => SORT_ASC],
                        'desc' => ['user_datos.cedula' => SORT_DESC],
                    ],
                ],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'pagos.id' => $this->id,
            'recibo_id' => $this->recibo_id,
            'fecha_pago' => $this->fecha_pago,
            'monto_pagado' => $this->monto_pagado,
            'user_id' => $this->user_id,
            'conciliador_id' => $this->conciliador_id,
            'conciliado' => $this->conciliado,
            'monto_usd' => $this->monto_usd,
        ]);

        $query->andFilterWhere(['ilike', 'metodo_pago', $this->metodo_pago])
            ->andFilterWhere(['ilike', 'pagos.estatus', $this->estatus])
            ->andFilterWhere(['ilike', 'numero_referencia_pago', $this->numero_referencia_pago])
            ->andFilterWhere(['ilike', 'nombre_conciliador', $this->nombre_conciliador])
            ->andFilterWhere(['ilike', 'fecha_conciliacion', $this->fecha_conciliacion])
            ->andFilterWhere(['ilike', 'fecha_registro', $this->fecha_registro])
            ->andFilterWhere(['ilike', 'pagos.observacion', $this->observacion])
            ->andFilterWhere(['or',
                ['ilike', 'user_datos.nombres', $this->nombreUsuario],
                ['ilike', 'user_datos.apellidos', $this->nombreUsuario]
            ])
            ->andFilterWhere(['ilike', 'user_datos.cedula', $this->cedulaUsuario]);

        return $dataProvider;
    }

    /**
     * Creates data provider instance with search query applied for specific clinica
     *
     * @param array $params
     * @param string|null $formName
     * @param int|null $clinica_id
     *
     * @return ActiveDataProvider
     */
    public function searchClinica($params, $formName = null, $clinica_id = null)
    {
        $query = Pagos::find()
            ->joinWith(['userDatos.contratos' => function($q) use ($clinica_id) {
                $q->andWhere(['contratos.clinica_id' => $clinica_id]);
            }]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['pagos.id' => SORT_DESC], // ✅ Cambiado a 'pagos.id'
                'attributes' => [
                    'id',
                    'created_at',
                    'fecha_pago',
                    'monto_pagado',
                    'monto_usd',
                    'estatus',
                    'numero_referencia_pago',
                    'observacion',
                    'nombreUsuario' => [
                        'asc' => ['user_datos.nombres' => SORT_ASC, 'user_datos.apellidos' => SORT_ASC],
                        'desc' => ['user_datos.nombres' => SORT_DESC, 'user_datos.apellidos' => SORT_DESC],
                    ],
                    'cedulaUsuario' => [
                        'asc' => ['user_datos.cedula' => SORT_ASC],
                        'desc' => ['user_datos.cedula' => SORT_DESC],
                    ],
                ],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'pagos.id' => $this->id,
            'recibo_id' => $this->recibo_id,
            'fecha_pago' => $this->fecha_pago,
            'monto_pagado' => $this->monto_pagado,
            'user_id' => $this->user_id,
            'conciliador_id' => $this->conciliador_id,
            'conciliado' => $this->conciliado,
            'monto_usd' => $this->monto_usd,
        ]);

        $query->andFilterWhere(['ilike', 'metodo_pago', $this->metodo_pago])
            ->andFilterWhere(['ilike', 'pagos.estatus', $this->estatus])
            ->andFilterWhere(['ilike', 'numero_referencia_pago', $this->numero_referencia_pago])
            ->andFilterWhere(['ilike', 'nombre_conciliador', $this->nombre_conciliador])
            ->andFilterWhere(['ilike', 'fecha_conciliacion', $this->fecha_conciliacion])
            ->andFilterWhere(['ilike', 'fecha_registro', $this->fecha_registro])
            ->andFilterWhere(['ilike', 'pagos.observacion', $this->observacion])
            ->andFilterWhere(['or',
                ['ilike', 'user_datos.nombres', $this->nombreUsuario],
                ['ilike', 'user_datos.apellidos', $this->nombreUsuario]
            ])
            ->andFilterWhere(['ilike', 'user_datos.cedula', $this->cedulaUsuario]);

        return $dataProvider;
    }

    /**
     * Creates data provider instance with search query applied for specific user
     *
     * @param array $params
     * @param int $user_id
     *
     * @return ActiveDataProvider
     */
    public function searchByUser($params, $user_id)
    {
        $query = Pagos::find()
            ->where(['user_id' => $user_id])
            ->joinWith(['userDatos']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC], // ✅ Cambiado a 'id'
                'attributes' => [
                    'id',
                    'created_at',
                    'fecha_pago',
                    'monto_pagado',
                    'monto_usd',
                    'estatus',
                    'numero_referencia_pago',
                    'observacion',
                    'metodo_pago',
                    'fecha_conciliacion',
                    'nombre_conciliador',
                ],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'recibo_id' => $this->recibo_id,
            'fecha_pago' => $this->fecha_pago,
            'monto_pagado' => $this->monto_pagado,
            'conciliador_id' => $this->conciliador_id,
            'conciliado' => $this->conciliado,
            'monto_usd' => $this->monto_usd,
        ]);

        $query->andFilterWhere(['ilike', 'metodo_pago', $this->metodo_pago])
            ->andFilterWhere(['ilike', 'estatus', $this->estatus])
            ->andFilterWhere(['ilike', 'numero_referencia_pago', $this->numero_referencia_pago])
            ->andFilterWhere(['ilike', 'nombre_conciliador', $this->nombre_conciliador])
            ->andFilterWhere(['ilike', 'fecha_conciliacion', $this->fecha_conciliacion])
            ->andFilterWhere(['ilike', 'fecha_registro', $this->fecha_registro])
            ->andFilterWhere(['ilike', 'observacion', $this->observacion]);

        return $dataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'nombreUsuario',
            'cedulaUsuario',
            'observacion'
        ]);
    }
}