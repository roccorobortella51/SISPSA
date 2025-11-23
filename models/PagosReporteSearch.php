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
            [['nombres', 'apellidos', 'cedula'], 'safe'],
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
     * Crea una instancia del proveedor de datos con la consulta de búsqueda aplicada.
     *
     * @param array $params Parámetros de búsqueda.
     * @param string $startDate Fecha de inicio del rango 'Y-m-d'.
     * @param string $endDate Fecha de fin del rango 'Y-m-d'.
     * @param string $status Estatus del pago ('Conciliado', 'Pendiente', etc.)
     * @return ActiveDataProvider
     */
    public function search($params, $startDate, $endDate, $status = 'Conciliado')
    {
        // 1. Inicializar la consulta y el JOIN
        $query = Pagos::find()->joinWith(['userDatos']);
        
        // 2. Configurar el proveedor de datos
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50, // O el tamaño de página que prefiera
            ],
            // 3. Configuración de Ordenación (Sort)
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_ASC, // **CAMBIO CLAVE: Ordenar por ID descendente (más reciente primero)**
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
        
        // Filtro de Estatus: Usar el parámetro $status
        $query->andWhere(['pagos.estatus' => $status]); 
        
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
            ->andFilterWhere(['ilike', 'pagos.numero_referencia_pago', $this->numero_referencia_pago]);

        // Filtros de texto (LIKE) en la tabla UserDatos (para Nombres, Apellidos, Cédula)
        $query->andFilterWhere(['ilike', 'userDatos.nombres', $this->nombres])
            ->andFilterWhere(['ilike', 'userDatos.apellidos', $this->apellidos])
            ->andFilterWhere(['ilike', 'userDatos.cedula', $this->cedula]);

        return $dataProvider;
    }
}