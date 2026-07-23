<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * IntermediariosByClinicaSearch represents the model behind the search form
 * for showing intermediarios (AgenteFuerza) that belong to a specific clinic.
 * 
 * An intermediario is associated with a clinic if they have sold plans to 
 * affiliates (UserDatos) that belong to that clinic (clinica_id).
 * 
 * The report shows ALL intermediarios, and the clinic filters which ones
 * have activity in that specific clinic.
 */
class IntermediariosByClinicaSearch extends Model
{
    public $id;
    public $nombre_completo;
    public $cedula;
    public $email;
    public $telefono;
    public $codigo_sudeaseg;
    public $agencia_nombre;
    public $por_venta;
    public $por_asesor;
    public $por_cobranza;
    public $por_post_venta;
    public $por_registrar;
    public $puede_vender;
    public $puede_asesorar;
    public $puede_cobrar;
    public $puede_post_venta;
    public $puede_registrar;
    public $created_at;
    public $clinica_id;

    public function rules()
    {
        return [
            [['id', 'clinica_id', 'puede_vender', 'puede_asesorar', 'puede_cobrar', 'puede_post_venta', 'puede_registrar'], 'integer'],
            [['nombre_completo', 'cedula', 'email', 'telefono', 'codigo_sudeaseg', 'agencia_nombre', 'created_at'], 'safe'],
            [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_registrar'], 'number'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $clinica_id = null)
    {
        // If clinica_id is provided directly, use it
        if ($clinica_id !== null) {
            $this->clinica_id = $clinica_id;
        }

        // Build the query for AgenteFuerza - get ALL intermediarios
        $query = AgenteFuerza::find()
            ->alias('af')
            ->distinct();

        // Select all relevant fields
        $query->select([
            'af.id',
            'af.idusuario',
            'af.agente_id',
            'af.por_venta',
            'af.por_asesor',
            'af.por_cobranza',
            'af.por_post_venta',
            'af.por_registrar',
            'af.puede_vender',
            'af.puede_asesorar',
            'af.puede_cobrar',
            'af.puede_post_venta',
            'af.puede_registrar',
            'af.registro_corredor_actividad_aseguradora',
            'af.created_at',
            'af.updated_at',
        ]);

        // Join with UserDatos to get user information
        $query->innerJoin(['ud' => 'user_datos'], 'ud.id = af.idusuario');

        // Join with User to get login information
        $query->leftJoin(['u' => 'user'], 'u.id = ud.user_login_id');

        // Join with Agente to get agency information
        $query->leftJoin(['a' => 'agente'], 'a.id = af.agente_id');

        // ============================================================
        // FILTER BY CLINIC (Optional)
        // 
        // If a clinic is selected, only show intermediarios that have
        // sold plans to affiliates (UserDatos) belonging to that clinic.
        // 
        // FIXED: Using 'EXISTS (SELECT 1 ...)' without quotes for PostgreSQL
        // ============================================================
        if ($this->clinica_id) {
            $subQuery = (new \yii\db\Query())
                ->select(new Expression('1'))  // Use Expression instead of string '1'
                ->from(['ud_afiliado' => 'user_datos'])
                ->where('ud_afiliado.asesor_id = af.id')
                ->andWhere(['ud_afiliado.clinica_id' => $this->clinica_id])
                ->andWhere(['ud_afiliado.role' => 'afiliado'])
                ->andWhere(['is', 'ud_afiliado.deleted_at', null]);

            $query->andWhere(['exists', $subQuery]);
        }

        // Add a filter to ensure we only get active records
        $query->andWhere(['is', 'af.deleted_at', null]);

        // ============================================================
        // DATA PROVIDER CONFIGURATION
        // ============================================================
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
                'pageSizeParam' => false,
                'defaultPageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
                'attributes' => [
                    'id' => [
                        'asc' => ['af.id' => SORT_ASC],
                        'desc' => ['af.id' => SORT_DESC],
                    ],
                    'nombre_completo' => [
                        'asc' => ['ud.nombres' => SORT_ASC, 'ud.apellidos' => SORT_ASC],
                        'desc' => ['ud.nombres' => SORT_DESC, 'ud.apellidos' => SORT_DESC],
                    ],
                    'cedula' => [
                        'asc' => ['ud.cedula' => SORT_ASC],
                        'desc' => ['ud.cedula' => SORT_DESC],
                    ],
                    'email' => [
                        'asc' => ['ud.email' => SORT_ASC],
                        'desc' => ['ud.email' => SORT_DESC],
                    ],
                    'telefono' => [
                        'asc' => ['ud.telefono' => SORT_ASC],
                        'desc' => ['ud.telefono' => SORT_DESC],
                    ],
                    'codigo_sudeaseg' => [
                        'asc' => ['af.registro_corredor_actividad_aseguradora' => SORT_ASC],
                        'desc' => ['af.registro_corredor_actividad_aseguradora' => SORT_DESC],
                    ],
                    'agencia_nombre' => [
                        'asc' => ['a.nom' => SORT_ASC],
                        'desc' => ['a.nom' => SORT_DESC],
                    ],
                    'por_venta' => [
                        'asc' => ['af.por_venta' => SORT_ASC],
                        'desc' => ['af.por_venta' => SORT_DESC],
                    ],
                    'por_asesor' => [
                        'asc' => ['af.por_asesor' => SORT_ASC],
                        'desc' => ['af.por_asesor' => SORT_DESC],
                    ],
                    'por_cobranza' => [
                        'asc' => ['af.por_cobranza' => SORT_ASC],
                        'desc' => ['af.por_cobranza' => SORT_DESC],
                    ],
                    'por_post_venta' => [
                        'asc' => ['af.por_post_venta' => SORT_ASC],
                        'desc' => ['af.por_post_venta' => SORT_DESC],
                    ],
                    'por_registrar' => [
                        'asc' => ['af.por_registrar' => SORT_ASC],
                        'desc' => ['af.por_registrar' => SORT_DESC],
                    ],
                    'created_at' => [
                        'asc' => ['af.created_at' => SORT_ASC],
                        'desc' => ['af.created_at' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Apply search filters (these are applied regardless of clinic selection)
        $query->andFilterWhere(['af.id' => $this->id]);

        if (!empty($this->nombre_completo)) {
            $search = '%' . strtolower($this->nombre_completo) . '%';
            $query->andWhere(new Expression(
                "LOWER(ud.nombres || ' ' || ud.apellidos) LIKE :search",
                [':search' => $search]
            ));
        }

        if (!empty($this->cedula)) {
            $query->andWhere(['ilike', 'CAST(ud.cedula AS TEXT)', $this->cedula]);
        }

        if (!empty($this->email)) {
            $query->andWhere(['ilike', 'ud.email', $this->email]);
        }

        if (!empty($this->telefono)) {
            $query->andWhere(['ilike', 'ud.telefono', $this->telefono]);
        }

        if (!empty($this->codigo_sudeaseg)) {
            $query->andWhere(['ilike', 'af.registro_corredor_actividad_aseguradora', $this->codigo_sudeaseg]);
        }

        if (!empty($this->agencia_nombre)) {
            $query->andWhere(['ilike', 'a.nom', $this->agencia_nombre]);
        }

        $query->andFilterWhere(['af.por_venta' => $this->por_venta]);
        $query->andFilterWhere(['af.por_asesor' => $this->por_asesor]);
        $query->andFilterWhere(['af.por_cobranza' => $this->por_cobranza]);
        $query->andFilterWhere(['af.por_post_venta' => $this->por_post_venta]);
        $query->andFilterWhere(['af.por_registrar' => $this->por_registrar]);

        $query->andFilterWhere(['af.puede_vender' => $this->puede_vender]);
        $query->andFilterWhere(['af.puede_asesorar' => $this->puede_asesorar]);
        $query->andFilterWhere(['af.puede_cobrar' => $this->puede_cobrar]);
        $query->andFilterWhere(['af.puede_post_venta' => $this->puede_post_venta]);
        $query->andFilterWhere(['af.puede_registrar' => $this->puede_registrar]);

        if (!empty($this->created_at)) {
            $dates = explode(' a ', $this->created_at);
            if (count($dates) == 2) {
                $d1 = \DateTime::createFromFormat('d/m/Y', trim($dates[0]));
                $d2 = \DateTime::createFromFormat('d/m/Y', trim($dates[1]));
                if ($d1 && $d2) {
                    $date1 = $d1->format('Y-m-d') . ' 00:00:00';
                    $date2 = $d2->format('Y-m-d') . ' 23:59:59';
                    $query->andFilterWhere(['between', 'af.created_at', $date1, $date2]);
                }
            }
        }

        $query->orderBy(['af.id' => SORT_DESC]);

        return $dataProvider;
    }

    /**
     * Get summary statistics for intermediarios
     * 
     * @param int|null $clinica_id Optional clinic filter
     * @return array
     */
    public function getSummary($clinica_id = null)
    {
        $query = AgenteFuerza::find()
            ->alias('af')
            ->innerJoin(['ud' => 'user_datos'], 'ud.id = af.idusuario')
            ->leftJoin(['a' => 'agente'], 'a.id = af.agente_id');

        // Apply clinic filter if provided
        if ($clinica_id) {
            $subQuery = (new \yii\db\Query())
                ->select(new Expression('1'))
                ->from(['ud_afiliado' => 'user_datos'])
                ->where('ud_afiliado.asesor_id = af.id')
                ->andWhere(['ud_afiliado.clinica_id' => $clinica_id])
                ->andWhere(['ud_afiliado.role' => 'afiliado'])
                ->andWhere(['is', 'ud_afiliado.deleted_at', null]);

            $query->andWhere(['exists', $subQuery]);
        }

        $query->andWhere(['is', 'af.deleted_at', null]);

        $total = $query->count();
        $canSell = (clone $query)->andWhere(['af.puede_vender' => 1])->count();
        $canRegister = (clone $query)->andWhere(['af.puede_registrar' => 1])->count();
        $canAsesorar = (clone $query)->andWhere(['af.puede_asesorar' => 1])->count();
        $canCobrar = (clone $query)->andWhere(['af.puede_cobrar' => 1])->count();

        $avgVenta = (clone $query)->average('af.por_venta') ?: 0;
        $avgAsesor = (clone $query)->average('af.por_asesor') ?: 0;
        $avgCobranza = (clone $query)->average('af.por_cobranza') ?: 0;
        $avgPostVenta = (clone $query)->average('af.por_post_venta') ?: 0;
        $avgRegistrar = (clone $query)->average('af.por_registrar') ?: 0;

        $agencies = (clone $query)->select('af.agente_id')->distinct()->count();

        return [
            'total_intermediarios' => $total,
            'can_sell' => $canSell,
            'can_register' => $canRegister,
            'can_asesorar' => $canAsesorar,
            'can_cobrar' => $canCobrar,
            'avg_por_venta' => round($avgVenta, 2),
            'avg_por_asesor' => round($avgAsesor, 2),
            'avg_por_cobranza' => round($avgCobranza, 2),
            'avg_por_post_venta' => round($avgPostVenta, 2),
            'avg_por_registrar' => round($avgRegistrar, 2),
            'total_agencias' => $agencies,
        ];
    }

    /**
     * Get intermediarios query for export
     * 
     * @param int $clinica_id Clinic filter
     * @param array $params Search params
     * @return \yii\db\ActiveQuery
     */
    public function getIntermediariosQuery($clinica_id, $params = [])
    {
        $query = AgenteFuerza::find()
            ->alias('af')
            ->distinct()
            ->select([
                'af.id',
                'af.idusuario',
                'af.agente_id',
                'af.por_venta',
                'af.por_asesor',
                'af.por_cobranza',
                'af.por_post_venta',
                'af.por_registrar',
                'af.puede_vender',
                'af.puede_asesorar',
                'af.puede_cobrar',
                'af.puede_post_venta',
                'af.puede_registrar',
                'af.registro_corredor_actividad_aseguradora',
                'af.created_at',
                'af.updated_at',
            ]);

        $query->innerJoin(['ud' => 'user_datos'], 'ud.id = af.idusuario');
        $query->leftJoin(['a' => 'agente'], 'a.id = af.agente_id');

        // Apply clinic filter
        if ($clinica_id) {
            $subQuery = (new \yii\db\Query())
                ->select(new Expression('1'))
                ->from(['ud_afiliado' => 'user_datos'])
                ->where('ud_afiliado.asesor_id = af.id')
                ->andWhere(['ud_afiliado.clinica_id' => $clinica_id])
                ->andWhere(['ud_afiliado.role' => 'afiliado'])
                ->andWhere(['is', 'ud_afiliado.deleted_at', null]);

            $query->andWhere(['exists', $subQuery]);
        }

        $query->andWhere(['is', 'af.deleted_at', null]);

        // Apply search filters
        if (!empty($params['IntermediariosByClinicaSearch'])) {
            $searchParams = $params['IntermediariosByClinicaSearch'];

            if (!empty($searchParams['nombre_completo'])) {
                $search = '%' . strtolower($searchParams['nombre_completo']) . '%';
                $query->andWhere(new Expression(
                    "LOWER(ud.nombres || ' ' || ud.apellidos) LIKE :search",
                    [':search' => $search]
                ));
            }

            if (!empty($searchParams['cedula'])) {
                $query->andWhere(['ilike', 'CAST(ud.cedula AS TEXT)', $searchParams['cedula']]);
            }

            if (!empty($searchParams['email'])) {
                $query->andWhere(['ilike', 'ud.email', $searchParams['email']]);
            }

            if (!empty($searchParams['codigo_sudeaseg'])) {
                $query->andWhere(['ilike', 'af.registro_corredor_actividad_aseguradora', $searchParams['codigo_sudeaseg']]);
            }

            if (isset($searchParams['puede_vender'])) {
                $query->andWhere(['af.puede_vender' => $searchParams['puede_vender']]);
            }

            if (isset($searchParams['puede_asesorar'])) {
                $query->andWhere(['af.puede_asesorar' => $searchParams['puede_asesorar']]);
            }

            if (isset($searchParams['puede_cobrar'])) {
                $query->andWhere(['af.puede_cobrar' => $searchParams['puede_cobrar']]);
            }

            if (isset($searchParams['puede_post_venta'])) {
                $query->andWhere(['af.puede_post_venta' => $searchParams['puede_post_venta']]);
            }

            if (isset($searchParams['puede_registrar'])) {
                $query->andWhere(['af.puede_registrar' => $searchParams['puede_registrar']]);
            }
        }

        $query->orderBy(['af.id' => SORT_DESC]);

        return $query;
    }

    /**
     * Get a count of affiliates by clinic for each intermediario
     * 
     * @param int $intermediarioId
     * @param int|null $clinica_id
     * @return int
     */
    public function getAffiliateCount($intermediarioId, $clinica_id = null)
    {
        $query = UserDatos::find()
            ->where(['asesor_id' => $intermediarioId])
            ->andWhere(['role' => 'afiliado'])
            ->andWhere(['is', 'deleted_at', null]);

        if ($clinica_id) {
            $query->andWhere(['clinica_id' => $clinica_id]);
        }

        return $query->count();
    }

    /**
     * Get the clinic names where an intermediario has sold plans
     * 
     * @param int $intermediarioId
     * @return array
     */
    public function getClinicasForIntermediario($intermediarioId)
    {
        $clinicas = UserDatos::find()
            ->alias('ud')
            ->select(['rm_clinica.id', 'rm_clinica.nombre', 'COUNT(ud.id) as total_afiliados'])
            ->innerJoin(['rm_clinica'], 'rm_clinica.id = ud.clinica_id')
            ->where(['ud.asesor_id' => $intermediarioId])
            ->andWhere(['ud.role' => 'afiliado'])
            ->andWhere(['is', 'ud.deleted_at', null])
            ->andWhere(['is', 'rm_clinica.deleted_at', null])
            ->groupBy(['rm_clinica.id', 'rm_clinica.nombre'])
            ->orderBy(['total_afiliados' => SORT_DESC])
            ->asArray()
            ->all();

        return $clinicas;
    }
}
