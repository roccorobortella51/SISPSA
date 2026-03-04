<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Pagos;
use yii\db\Expression;

/**
 * PagosSearch represents the model behind the search form of `app\models\Pagos`.
 */
class PagosSearch extends Pagos
{
    public $nombreUsuario;
    public $cedulaUsuario;
    public $observacion;
    public $clinica_nombre;
    public $tipo_filter;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'recibo_id', 'user_id', 'conciliador_id', 'conciliado'], 'integer'],
            [['fecha_pago', 'monto_pagado', 'monto_usd'], 'number'],
            [['metodo_pago', 'estatus', 'numero_referencia_pago', 'nombre_conciliador', 'fecha_conciliacion', 'fecha_registro', 'nombreUsuario', 'cedulaUsuario', 'observacion', 'clinica_nombre', 'tipo_filter'], 'safe'],
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
        // 1. Asignamos alias 't' a la tabla principal 'pagos'
        $query = Pagos::find()->alias('t')
            ->joinWith(['userDatos'])
            ->joinWith(['userDatos.clinica as userClinica'])
            ->joinWith(['userDatos.contratos.clinica as contratoClinica'])
            ->joinWith(['corporativo.clinicas as corpClinicas']);

        // 2. Proyección de columnas
        $query->select([
            't.id',
            't.fecha_pago',
            't.monto_pagado',
            't.monto_usd',
            't.estatus',
            't.numero_referencia_pago',
            't.metodo_pago',
            't.fecha_conciliacion',
            't.nombre_conciliador',
            't.observacion',
            'user_datos.nombres',
            'user_datos.apellidos',
            'user_datos.cedula',
            'user_datos.estatus_solvente',
            't.user_id',
            't.conciliador_id',
            't.recibo_id',
            't.conciliado',
            't.created_at',
            // Campos críticos para ordenamiento
            't.tipo_pago',
            't.corporativo_id',
            't.pago_corporativo_id',
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'id',
                    'fecha_pago',
                    'monto_pagado',
                    'monto_usd',
                    'estatus',
                    'numero_referencia_pago',
                    'metodo_pago',
                    'fecha_conciliacion',
                    'nombre_conciliador',
                    'observacion',
                    'created_at',
                    'tipo_pago',
                    'nombreUsuario' => [
                        'asc' => ['user_datos.nombres' => SORT_ASC, 'user_datos.apellidos' => SORT_ASC],
                        'desc' => ['user_datos.nombres' => SORT_DESC, 'user_datos.apellidos' => SORT_DESC],
                    ],
                    'cedulaUsuario' => [
                        'asc' => ['user_datos.cedula' => SORT_ASC],
                        'desc' => ['user_datos.cedula' => SORT_DESC],
                    ],
                    'clinica_nombre' => [
                        'asc' => ['userClinica.nombre' => SORT_ASC],
                        'desc' => ['userClinica.nombre' => SORT_DESC],
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

        // Filtros existentes...
        $query->andFilterWhere([
            't.id' => $this->id,
            'recibo_id' => $this->recibo_id,
            'fecha_pago' => $this->fecha_pago,
            'monto_pagado' => $this->monto_pagado,
            'user_id' => $this->user_id,
            'conciliador_id' => $this->conciliador_id,
            'conciliado' => $this->conciliado,
            'monto_usd' => $this->monto_usd,
        ]);

        $query->andFilterWhere(['ilike', 'metodo_pago', $this->metodo_pago])
            ->andFilterWhere(['ilike', 't.estatus', $this->estatus])
            ->andFilterWhere(['ilike', 'numero_referencia_pago', $this->numero_referencia_pago])
            ->andFilterWhere(['ilike', 'nombre_conciliador', $this->nombre_conciliador])
            ->andFilterWhere(['ilike', 'fecha_conciliacion', $this->fecha_conciliacion])
            ->andFilterWhere(['ilike', 'fecha_registro', $this->fecha_registro])
            ->andFilterWhere(['ilike', 't.observacion', $this->observacion])
            ->andFilterWhere([
                'or',
                ['ilike', 'user_datos.nombres', $this->nombreUsuario],
                ['ilike', 'user_datos.apellidos', $this->nombreUsuario]
            ])
            ->andFilterWhere(['ilike', 'CAST(user_datos.cedula AS TEXT)', $this->cedulaUsuario]);

        // Filter by clinic name
        if (!empty($this->clinica_nombre)) {
            $query->andWhere([
                'or',
                ['ilike', 'userClinica.nombre', $this->clinica_nombre],
                ['ilike', 'contratoClinica.nombre', $this->clinica_nombre],
                ['ilike', 'corpClinicas.nombre', $this->clinica_nombre],
            ]);
        }

        // Filter by tipo
        if (!empty($this->tipo_filter)) {
            switch ($this->tipo_filter) {
                case 'corporativo':
                    $query->andWhere(['t.tipo_pago' => 'corporativo'])
                        ->andWhere(['not', ['t.corporativo_id' => null]]);
                    break;
                case 'afiliado':
                    $query->andWhere(['not', ['t.pago_corporativo_id' => null]]);
                    break;
                case 'individual':
                    $query->andWhere([
                        'or',
                        ['t.tipo_pago' => null],
                        ['t.tipo_pago' => ''],
                        [
                            'and',
                            ['t.tipo_pago' => 'individual'],
                            ['t.pago_corporativo_id' => null]
                        ]
                    ]);
                    break;
            }
        }

        // ===========================================================================
        // ORDENAMIENTO JERÁRQUICO QUE MANTIENE EL ORDEN CRONOLÓGICO
        // ===========================================================================

        $query->orderBy(new \yii\db\Expression("
        -- NIVEL 1: Ordenar por la FECHA/HORA del GRUPO PADRE (más importante)
        -- Para pagos corporativos: usar su propio created_at
        -- Para afiliados: usar el created_at de su pago corporativo padre
        (
            SELECT COALESCE(padre.created_at, t.created_at)
            FROM pagos AS padre
            WHERE padre.id = COALESCE(t.pago_corporativo_id, t.id)
        ) DESC,
        
        -- NIVEL 2: Agrupar por 'padre' para mantener juntos los afiliados con su corporación
        COALESCE(t.pago_corporativo_id, t.id) ASC,
        
        -- NIVEL 3: Dentro de cada grupo, el pago corporativo primero
        CASE 
            WHEN t.tipo_pago = 'corporativo' AND t.corporativo_id IS NOT NULL THEN 1
            ELSE 2
        END ASC,
        
        -- NIVEL 4: Dentro de cada subgrupo, ordenar por fecha de creación (más reciente primero)
        t.created_at DESC,
        
        -- NIVEL 5: Por ID como último desempate
        t.id DESC
        "));

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
        // 1. Asignamos alias 't' a la tabla principal 'pagos'
        $query = Pagos::find()->alias('t');

        // Unir Pagos -> UserDatos -> Contratos (con filtro de clinica_id)
        $query->joinWith(['userDatos' => function ($q) use ($clinica_id) {
            $q->joinWith(['contratos' => function ($q_c) use ($clinica_id) {
                $q_c->andWhere(['contratos.clinica_id' => $clinica_id]);
            }], true, 'INNER JOIN');
        }], true, 'INNER JOIN');

        // Also join with corporate clinics for completeness
        $query->joinWith(['corporativo.clinicas']);

        // 2. Proyección de columnas: Seleccionamos solo las necesarias para el GridView
        $query->select([
            't.id',
            't.fecha_pago',
            't.monto_pagado',
            't.monto_usd',
            't.estatus',
            't.numero_referencia_pago',
            't.metodo_pago',
            't.fecha_conciliacion',
            't.nombre_conciliador',
            't.observacion',
            'user_datos.nombres',
            'user_datos.apellidos',
            'user_datos.cedula',
            'user_datos.estatus_solvente',
            't.user_id',
            't.conciliador_id',
            't.recibo_id',
            't.conciliado',
            't.created_at',
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['t.id' => SORT_DESC],
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
            't.id' => $this->id,
            'recibo_id' => $this->recibo_id,
            'fecha_pago' => $this->fecha_pago,
            'monto_pagado' => $this->monto_pagado,
            'user_id' => $this->user_id,
            'conciliador_id' => $this->conciliador_id,
            'conciliado' => $this->conciliado,
            'monto_usd' => $this->monto_usd,
        ]);

        $query->andFilterWhere(['ilike', 'metodo_pago', $this->metodo_pago])
            ->andFilterWhere(['ilike', 't.estatus', $this->estatus])
            ->andFilterWhere(['ilike', 'numero_referencia_pago', $this->numero_referencia_pago])
            ->andFilterWhere(['ilike', 'nombre_conciliador', $this->nombre_conciliador])
            ->andFilterWhere(['ilike', 'fecha_conciliacion', $this->fecha_conciliacion])
            ->andFilterWhere(['ilike', 'fecha_registro', $this->fecha_registro])
            ->andFilterWhere(['ilike', 't.observacion', $this->observacion])
            ->andFilterWhere([
                'or',
                ['ilike', 'user_datos.nombres', $this->nombreUsuario],
                ['ilike', 'user_datos.apellidos', $this->nombreUsuario]
            ])
            ->andFilterWhere(['ilike', 'CAST(user_datos.cedula AS TEXT)', $this->cedulaUsuario]);

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
        // 1. Asignamos alias 't' a la tabla principal 'pagos'
        $query = Pagos::find()->alias('t');

        $query->where(['t.user_id' => $user_id]);

        // 2. Proyección de columnas: Solo necesitamos las de Pagos
        $query->select([
            't.id',
            't.fecha_pago',
            't.monto_pagado',
            't.monto_usd',
            't.estatus',
            't.numero_referencia_pago',
            't.metodo_pago',
            't.fecha_conciliacion',
            't.nombre_conciliador',
            't.observacion',
            't.created_at',
            't.user_id',
            't.conciliador_id',
            't.recibo_id',
            't.conciliado',
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
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

        // grid filtering conditions (usamos el alias 't')
        $query->andFilterWhere([
            't.id' => $this->id,
            'recibo_id' => $this->recibo_id,
            'fecha_pago' => $this->fecha_pago,
            'monto_pagado' => $this->monto_pagado,
            'conciliador_id' => $this->conciliador_id,
            'conciliado' => $this->conciliado,
            'monto_usd' => $this->monto_usd,
        ]);

        $query->andFilterWhere(['ilike', 'metodo_pago', $this->metodo_pago])
            ->andFilterWhere(['ilike', 't.estatus', $this->estatus])
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
            'observacion',
            'clinica_nombre',
            'tipo_filter',
        ]);
    }
}
