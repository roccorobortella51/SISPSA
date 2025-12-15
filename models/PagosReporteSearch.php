<?php
// app/models/PagosReporteSearch.php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Pagos;
use yii\db\Expression;

/**
 * PagosReporteSearch representa el modelo detrás del formulario de búsqueda para Pagos.
 * Extiende de Pagos para usar los atributos de la tabla 'pagos'.
 */
class PagosReporteSearch extends Pagos
{
    // Atributos virtuales para buscar/ordenar por datos del afiliado
    public $nombres;
    public $apellidos;
    public $cedula;
    public $clinica_nombre; // Agregar este atributo virtual


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'recibo_id', 'user_id'], 'integer'],
            // Asegurarse de que 'estatus' esté en las reglas
            [['fecha_pago', 'metodo_pago', 'estatus', 'numero_referencia_pago'], 'safe'],
            [['monto_usd'], 'number'],

            // Reglas para los atributos virtuales de UserDatos (Nombres, Apellidos, Cédula)
            [['nombres', 'apellidos', 'cedula', 'clinica_nombre'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        // Agregar etiquetas para atributos virtuales
        $labels['nombres'] = 'Nombres';
        $labels['apellidos'] = 'Apellidos';
        $labels['cedula'] = 'Cédula';
        $labels['clinica_nombre'] = 'Clínica'; // Agregar esta etiqueta

        return $labels;
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
     * Crea una instancia del proveedor de datos con la consulta de búsqueda aplicada.
     *
     * @param array $params Parámetros de búsqueda.
     * @param string $startDate Fecha de inicio del rango 'Y-m-d'.
     * @param string $endDate Fecha de fin del rango 'Y-m-d'.
     * @param string $status Estatus del pago ('Conciliado', 'Pendiente', 'todos', etc.)
     * @param array $clinicas Array de IDs de clínicas a filtrar
     * @return ActiveDataProvider
     */
    public function search($params, $startDate, $endDate, $status = 'Conciliado', $clinicas = [])
    {
        // 1. Inicializar la consulta básica - SOLO con JOIN necesario para nombres, apellidos, cédula
        $query = Pagos::find()
            ->joinWith(['userDatos', 'contratos.clinica']); // Agregar JOIN a contratos y clínica

        // 2. Configurar el proveedor de datos
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50, // O el tamaño de página que prefiera
            ],
            // 3. Configuración de Ordenación (Sort)
            'sort' => [
                'defaultOrder' => [
                    'clinica_nombre' => SORT_ASC, // Ordenar por nombre de clínica ascendente por defecto

                    'id' => SORT_ASC, // Ordenar por ID ascendente
                ],
                'attributes' => [
                    'id', // Permitir ordenar por ID
                    'nombres' => [
                        'asc' => ['userDatos.nombres' => SORT_ASC],
                        'desc' => ['userDatos.nombres' => SORT_DESC],
                    ],
                    'apellidos' => [
                        'asc' => ['userDatos.apellidos' => SORT_ASC],
                        'desc' => ['userDatos.apellidos' => SORT_DESC],
                    ],
                    'cedula' => [
                        'asc' => ['userDatos.cedula' => SORT_ASC],
                        'desc' => ['userDatos.cedula' => SORT_DESC],
                    ],
                    'monto_usd',
                    'fecha_pago',
                    'metodo_pago',
                    'estatus',
                    // AGREGAR ATRIBUTO VIRTUAL PARA ORDENAR POR CLÍNICA
                    'clinica_nombre' => [
                        'asc' => ['rm_clinica.nombre' => SORT_ASC],
                        'desc' => ['rm_clinica.nombre' => SORT_DESC],
                        'label' => 'Clínica',
                    ],
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // Descomentar si no desea que se apliquen reglas cuando la validación falla
            // $query->where('0=1');
            return $dataProvider;
        }

        // 4. Aplicar Filtros Específicos del Reporte

        // Filtro de Estatus: Usar el parámetro $status (si no es 'todos')
        if ($status !== 'todos') {
            $query->andWhere(['pagos.estatus' => $status]);
        }

        // Filtro de Rango de Fecha (provee el controlador)
        if ($startDate && $endDate) {
            // Ajustar el end date para incluir el día completo (hasta el inicio del día siguiente)
            $adjustedEndDate = (new \DateTime($endDate))->modify('+1 day')->format('Y-m-d');

            // Usar COALESCE para intentar con fecha_pago o fecha_conciliacion si una es nula
            $query->andWhere(['between', new Expression('COALESCE(pagos.fecha_pago, pagos.fecha_conciliacion)'), $startDate, $adjustedEndDate]);
        }

        // 5. Aplicar Filtros del GridView

        // Filtros por igualdad (IDs y Monto)
        $query->andFilterWhere([
            'pagos.id' => $this->id,
            'pagos.recibo_id' => $this->recibo_id,
            'pagos.user_id' => $this->user_id,
            // 'pagos.monto_usd' => $this->monto_usd, // Descomentar si quiere filtrar por monto exacto
        ]);

        // Filtros de texto (LIKE) en la tabla Pagos
        $query->andFilterWhere(['ilike', 'pagos.metodo_pago', $this->metodo_pago])
            ->andFilterWhere(['ilike', 'pagos.numero_referencia_pago', $this->numero_referencia_pago])
            ->andFilterWhere(['ilike', 'pagos.estatus', $this->estatus]);

        // Filtros de texto (LIKE) en la tabla UserDatos (para Nombres, Apellidos, Cédula)
        $query->andFilterWhere(['ilike', 'userDatos.nombres', $this->nombres])
            ->andFilterWhere(['ilike', 'userDatos.apellidos', $this->apellidos])
            ->andFilterWhere(['ilike', 'userDatos.cedula', $this->cedula]);

        return $dataProvider;
    }

    /**
     * Versión alternativa del método search con filtrado por clínicas
     * Usa una subconsulta para evitar problemas de JOIN complejos
     * 
     * @param array $params Parámetros de búsqueda.
     * @param string $startDate Fecha de inicio del rango 'Y-m-d'.
     * @param string $endDate Fecha de fin del rango 'Y-m-d'.
     * @param string $status Estatus del pago ('Conciliado', 'Pendiente', 'todos', etc.)
     * @param array $clinicas Array de IDs de clínicas a filtrar
     * @return ActiveDataProvider
     */
    public function searchConClinicas($params, $startDate, $endDate, $status = 'Conciliado', $clinicas = [])
    {
        // 1. Inicializar la consulta básica
        $query = Pagos::find()->joinWith(['userDatos', 'contratos.clinica']); // Agregar JOIN a contratos y clínica


        // 2. Si hay filtro de clínicas, aplicar subconsulta
        if (!empty($clinicas) && !in_array('todas', $clinicas)) {
            // Subconsulta para obtener user_ids que tienen contratos con las clínicas seleccionadas
            $subQuery = Contratos::find()
                ->select(['user_id'])
                ->where(['clinica_id' => $clinicas])
                ->distinct();

            // Aplicar el filtro a la consulta principal
            $query->andWhere(['pagos.user_id' => $subQuery]);
        }

        // 3. Configurar el proveedor de datos (igual que en search())
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50,
            ],
            'sort' => [
                'defaultOrder' => ['id' => SORT_ASC],
                'attributes' => [
                    'clinica_nombre' => SORT_ASC, // Ordenar por clínica por defecto

                    'id',
                    'nombres' => [
                        'asc' => ['userDatos.nombres' => SORT_ASC],
                        'desc' => ['userDatos.nombres' => SORT_DESC],
                    ],
                    'apellidos' => [
                        'asc' => ['userDatos.apellidos' => SORT_ASC],
                        'desc' => ['userDatos.apellidos' => SORT_DESC],
                    ],
                    'cedula' => [
                        'asc' => ['userDatos.cedula' => SORT_ASC],
                        'desc' => ['userDatos.cedula' => SORT_DESC],
                    ],
                    'monto_usd',
                    'fecha_pago',
                    'metodo_pago',
                    'estatus',
                    // AGREGAR ATRIBUTO VIRTUAL PARA ORDENAR POR CLÍNICA
                    'clinica_nombre' => [
                        'asc' => ['rm_clinica.nombre' => SORT_ASC],
                        'desc' => ['rm_clinica.nombre' => SORT_DESC],
                        'label' => 'Clínica',
                    ],
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // 4. Aplicar Filtros Específicos del Reporte

        // Filtro de Estatus
        if ($status !== 'todos') {
            $query->andWhere(['pagos.estatus' => $status]);
        }

        // Filtro de Rango de Fecha
        if ($startDate && $endDate) {
            $adjustedEndDate = (new \DateTime($endDate))->modify('+1 day')->format('Y-m-d');
            $query->andWhere([
                'between',
                new Expression('COALESCE(pagos.fecha_pago, pagos.fecha_conciliacion)'),
                $startDate,
                $adjustedEndDate
            ]);
        }

        // 5. Aplicar Filtros del GridView
        $query->andFilterWhere([
            'pagos.id' => $this->id,
            'pagos.recibo_id' => $this->recibo_id,
            'pagos.user_id' => $this->user_id,
        ]);

        $query->andFilterWhere(['ilike', 'pagos.metodo_pago', $this->metodo_pago])
            ->andFilterWhere(['ilike', 'pagos.numero_referencia_pago', $this->numero_referencia_pago])
            ->andFilterWhere(['ilike', 'pagos.estatus', $this->estatus])
            ->andFilterWhere(['ilike', 'userDatos.nombres', $this->nombres])
            ->andFilterWhere(['ilike', 'userDatos.apellidos', $this->apellidos])
            ->andFilterWhere(['ilike', 'userDatos.cedula', $this->cedula]);

        return $dataProvider;
    }

    /**
     * Método para obtener resumen por clínica (para mostrar en el panel)
     * 
     * @param string $startDate
     * @param string $endDate
     * @param string $status
     * @param array $clinicas
     * @return array
     */
    public function obtenerResumenPorClinica($startDate, $endDate, $status = 'todos', $clinicas = [])
    {
        $adjustedEndDate = (new \DateTime($endDate))->modify('+1 day')->format('Y-m-d');

        // Construir consulta base para pagos
        $query = Pagos::find()
            ->joinWith(['userDatos.contratos.clinica'])
            ->where([
                'between',
                new Expression('COALESCE(pagos.fecha_pago, pagos.fecha_conciliacion)'),
                $startDate,
                $adjustedEndDate
            ]);

        // Filtrar por estado si no es "todos"
        if ($status !== 'todos') {
            $query->andWhere(['pagos.estatus' => $status]);
        }

        // Filtrar por clínicas si se especifican
        if (!empty($clinicas) && !in_array('todas', $clinicas)) {
            $query->andWhere(['rm_clinica.id' => $clinicas]);
        }

        // Agrupar por clínica y obtener resumen - CORREGIDO: usar comillas simples para strings
        $result = $query->select([
            'clinica_id' => 'rm_clinica.id',
            'clinica_nombre' => 'rm_clinica.nombre',
            'clinica_rif' => 'rm_clinica.rif',
            'total_monto' => 'COALESCE(SUM(pagos.monto_usd), 0)',
            'total_pagos' => 'COUNT(DISTINCT pagos.id)',
            // CORRECCIÓN: Usar comillas simples para valores de string
            'conciliados' => new Expression("SUM(CASE WHEN pagos.estatus = 'Conciliado' THEN 1 ELSE 0 END)"),
            'pendientes' => new Expression("SUM(CASE WHEN pagos.estatus = 'Por Conciliar' THEN 1 ELSE 0 END)")
        ])
            ->groupBy(['rm_clinica.id', 'rm_clinica.nombre', 'rm_clinica.rif'])
            ->orderBy(['total_monto' => SORT_DESC])
            ->asArray()
            ->all();

        return $result ?: [];
    }

    /**
     * Método adicional para obtener el total de pagos conciliados vs pendientes
     * Útil para estadísticas rápidas
     * 
     * @param string $startDate
     * @param string $endDate
     * @param array $clinicas
     * @return array
     */
    public function obtenerEstadisticasEstatus($startDate, $endDate, $clinicas = [])
    {
        $adjustedEndDate = (new \DateTime($endDate))->modify('+1 day')->format('Y-m-d');

        $query = Pagos::find()
            ->where([
                'between',
                new Expression('COALESCE(pagos.fecha_pago, pagos.fecha_conciliacion)'),
                $startDate,
                $adjustedEndDate
            ]);

        // Si hay filtro de clínicas, usar subconsulta
        if (!empty($clinicas) && !in_array('todas', $clinicas)) {
            $subQuery = Contratos::find()
                ->select(['user_id'])
                ->where(['clinica_id' => $clinicas])
                ->distinct();

            $query->andWhere(['pagos.user_id' => $subQuery]);
        }

        $result = $query->select([
            'conciliados' => 'SUM(CASE WHEN pagos.estatus = "Conciliado" THEN 1 ELSE 0 END)',
            'pendientes' => 'SUM(CASE WHEN pagos.estatus = "Por Conciliar" THEN 1 ELSE 0 END)',
            'total' => 'COUNT(*)'
        ])
            ->asArray()
            ->one();

        return [
            'conciliados' => $result['conciliados'] ?? 0,
            'pendientes' => $result['pendientes'] ?? 0,
            'total' => $result['total'] ?? 0
        ];
    }

    /**
     * Obtiene el resumen agrupado por método de pago
     * 
     * @param string $startDate
     * @param string $endDate
     * @param string $status
     * @param array $clinicas
     * @return array
     */
    public function obtenerResumenPorMetodoPago($startDate, $endDate, $status = 'todos', $clinicas = [])
    {
        $adjustedEndDate = (new \DateTime($endDate))->modify('+1 day')->format('Y-m-d');

        $query = Pagos::find()
            ->where([
                'between',
                new Expression('COALESCE(pagos.fecha_pago, pagos.fecha_conciliacion)'),
                $startDate,
                $adjustedEndDate
            ]);

        // Filtrar por estado si no es "todos"
        if ($status !== 'todos') {
            $query->andWhere(['pagos.estatus' => $status]);
        }

        // Si hay filtro de clínicas, usar subconsulta
        if (!empty($clinicas) && !in_array('todas', $clinicas)) {
            $subQuery = Contratos::find()
                ->select(['user_id'])
                ->where(['clinica_id' => $clinicas])
                ->distinct();

            $query->andWhere(['pagos.user_id' => $subQuery]);
        }

        $result = $query->select([
            'metodo_pago',
            'total_monto' => 'COALESCE(SUM(pagos.monto_usd), 0)',
            'total_pagos' => 'COUNT(*)'
        ])
            ->groupBy(['pagos.metodo_pago'])
            ->orderBy(['total_monto' => SORT_DESC])
            ->asArray()
            ->all();

        return $result ?: [];
    }

    public function obtenerResumenGeneral($startDate, $endDate, $status = 'todos', $clinicas = [])
    {
        $adjustedEndDate = (new \DateTime($endDate))->modify('+1 day')->format('Y-m-d');

        $query = Pagos::find()
            ->where([
                'between',
                new Expression('COALESCE(pagos.fecha_pago, pagos.fecha_conciliacion)'),
                $startDate,
                $adjustedEndDate
            ]);

        // Filtrar por estado si no es "todos"
        if ($status !== 'todos') {
            $query->andWhere(['pagos.estatus' => $status]);
        }

        // Si hay filtro de clínicas, usar subconsulta
        if (!empty($clinicas) && !in_array('todas', $clinicas)) {
            $subQuery = Contratos::find()
                ->select(['user_id'])
                ->where(['clinica_id' => $clinicas])
                ->distinct();

            $query->andWhere(['pagos.user_id' => $subQuery]);
        }

        // Obtener total monto y count
        $totalMonto = $query->sum('pagos.monto_usd');
        $totalCount = $query->count();

        // Obtener conteos por estado
        $conciliadosCount = 0;
        $pendientesCount = 0;

        if ($status === 'todos') {
            // Si estamos viendo todos los estados, contar separadamente
            $queryConciliados = clone $query;
            $queryPendientes = clone $query;

            $conciliadosCount = $queryConciliados->andWhere(['pagos.estatus' => 'Conciliado'])->count();
            $pendientesCount = $queryPendientes->andWhere(['pagos.estatus' => 'Por Conciliar'])->count();
        } else {
            // Si estamos filtrando por un estado específico
            if ($status === 'Conciliado') {
                $conciliadosCount = $totalCount;
                $pendientesCount = 0;
            } else if ($status === 'Por Conciliar') {
                $conciliadosCount = 0;
                $pendientesCount = $totalCount;
            }
        }

        // Always return valid array
        return [
            'total_monto' => $totalMonto ? (float)$totalMonto : 0,
            'total_count' => $totalCount ? (int)$totalCount : 0,
            'conciliados' => $conciliadosCount,
            'pendientes' => $pendientesCount
        ];
    }
}
