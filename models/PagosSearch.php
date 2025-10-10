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
    // Atributos virtuales para la búsqueda por relación
    public $nombreUsuario;
    public $cedulaUsuario; // NUEVO: Atributo para la cédula

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id'], 'integer'],
            // Aseguramos que 'cedulaUsuario' sea 'safe' para el filtro
            [['numero_referencia_pago', 'fecha_pago', 'estatus', 'created_at', 'nombreUsuario', 'cedulaUsuario'], 'safe'],
            [['monto_pagado', 'monto_usd'], 'number'], 
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
        $query = Pagos::find();

        // IMPORTANTE: Para buscar por nombre y cédula, hacemos un LEFT JOIN con la tabla de datos de usuario.
        $query->joinWith(['userDatos']); 

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20, // Forzamos un tamaño de página mayor al default de 1
            ],
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'id',
                    'user_id',
                    'numero_referencia_pago',
                    'fecha_pago',
                    'monto_pagado',
                    'monto_usd',
                    'created_at',
                    // FIX AMBIGUITY: Definir 'estatus' explícitamente con el prefijo de tabla
                    'estatus' => [
                        'asc' => ['pagos.estatus' => SORT_ASC],
                        'desc' => ['pagos.estatus' => SORT_DESC],
                        'label' => 'Estatus',
                    ],
                    // El atributo virtual 'nombreUsuario' se mantiene
                    'nombreUsuario' => [
                        'asc' => ['public.user_datos.nombres' => SORT_ASC, 'public.user_datos.apellidos' => SORT_ASC],
                        'desc' => ['public.user_datos.nombres' => SORT_DESC, 'public.user_datos.apellidos' => SORT_DESC],
                        'label' => 'Usuario',
                    ],
                    // NUEVO: Atributo virtual para la cédula
                    'cedulaUsuario' => [
                        'asc' => ['public.user_datos.cedula' => SORT_ASC],
                        'desc' => ['public.user_datos.cedula' => SORT_DESC],
                        'label' => 'Cédula',
                    ],
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions (usando siempre 'pagos.columna' cuando sea necesario)
        $query->andFilterWhere([
            'pagos.id' => $this->id,
            'pagos.user_id' => $this->user_id,
            'pagos.monto_pagado' => $this->monto_pagado,
            'pagos.monto_usd' => $this->monto_usd,
            'pagos.fecha_pago' => $this->fecha_pago,
            'pagos.created_at' => $this->created_at,
        ]);

        // FIX DEFINITIVO PARA EL ERROR 'Ambiguous column: estatus'
        if (!empty($this->estatus)) {
            $query->andWhere(['ilike', 'pagos.estatus', $this->estatus]);
        }

        // Filtros string restantes (que no causan conflicto)
        $query->andFilterWhere(['ilike', 'pagos.numero_referencia_pago', $this->numero_referencia_pago]);

        // FILTRO POR NOMBRE DE USUARIO (atributo virtual)
        $query->andFilterWhere(['ilike', "CAST(public.user_datos.nombres AS TEXT) || ' ' || CAST(public.user_datos.apellidos AS TEXT)", $this->nombreUsuario]);
        
        // NUEVO FILTRO POR CÉDULA
        $query->andFilterWhere(['ilike', 'public.user_datos.cedula', $this->cedulaUsuario]);


        return $dataProvider;
    }
}
