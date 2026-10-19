<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Corporativo;
use app\components\UserHelper;
use yii\db\Expression;

/**
 * CorporativoSearch represents the model behind the search form of `app\models\Corporativo`.
 */
class CorporativoSearch extends Corporativo
{
    /**
     * Virtual attribute: count of affiliates whose best contract status is 'Activo'.
     * Computed via a correlated subquery, exposed so the GridView can sort by it.
     */
    public $active_affiliates_count;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [[
                'nombre',
                'email',
                'telefono',
                'rif',
                'estado',
                'municipio',
                'parroquia',
                'direccion',
                'codigo_asesor',
                'lugar_registro',
                'fecha_registro_mercantil',
                'tomo_registro',
                'folio_registro',
                'domicilio_fiscal',
                'contacto_nombre',
                'contacto_cedula',
                'contacto_telefono',
                'contacto_cargo',
                'estatus',
                'created_at',
                'updated_at',
                'deleted_at',
                'active_affiliates_count',   // ← virtual sortable attribute
            ], 'safe'],
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
     * Creates data provider instance with search query applied.
     *
     * @param array       $params
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = Corporativo::find()->alias('t')->with(['users', 'clinicas']);

        // ─────────────────────────────────────────────────────────────────
        // ACTIVE AFFILIATES COUNT — computed as a correlated subquery.
        //
        // Mirrors the KPI definition used on corporate/index.php:
        //   ACTIVO = affiliate has at least one non-annulled contract
        //            whose estatus = 'Activo'
        //
        // Computed in SQL (not PHP) so ORDER BY works across the full
        // result set, not just the current page.
        // ─────────────────────────────────────────────────────────────────
        $activeAffiliatesSubquery = new Expression("(
            SELECT COUNT(DISTINCT ud.id)
            FROM user_datos ud
            INNER JOIN contratos c ON c.user_id = ud.id
            WHERE ud.afiliado_corporativo_id = t.id
              AND ud.user_datos_type_id = 2
              AND ud.deleted_at IS NULL
              AND c.estatus = 'Activo'
        )");

        // Projection — keep this list minimal for performance.
        $query->select([
            't.id',
            't.nombre',
            't.rif',
            't.email',
            't.telefono',
            't.estatus',
            'active_affiliates_count' => $activeAffiliatesSubquery,
        ]);

        // ────────── Clinic-scope filter for clinic-bound roles ──────────
        $userRole = UserHelper::getMyRol();
        $allowedRoles = [
            "Administrador-clinica",
            "CONTROL DE CITAS",
            "ADMISIÓN",
            "ATENCIÓN",
            "COORDINADOR-CLINICA",
            "GERENTE-CLINICA",
        ];

        if (in_array($userRole, $allowedRoles)) {
            $clinicaId = UserHelper::getMyClinicaId();

            if (!empty($clinicaId)) {
                $query->innerJoin('corporativo_clinica cc', 'cc.corporativo_id = t.id')
                    ->andWhere(['cc.clinica_id' => $clinicaId]);
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'defaultOrder' => [
                    'active_affiliates_count' => SORT_DESC,
                    'nombre'                  => SORT_ASC,
                ],
                'attributes' => [
                    'id'                      => ['asc' => ['t.id' => SORT_ASC],         'desc' => ['t.id' => SORT_DESC]],
                    'nombre'                  => ['asc' => ['t.nombre' => SORT_ASC],     'desc' => ['t.nombre' => SORT_DESC]],
                    'rif'                     => ['asc' => ['t.rif' => SORT_ASC],        'desc' => ['t.rif' => SORT_DESC]],
                    'email'                   => ['asc' => ['t.email' => SORT_ASC],      'desc' => ['t.email' => SORT_DESC]],
                    'telefono'                => ['asc' => ['t.telefono' => SORT_ASC],   'desc' => ['t.telefono' => SORT_DESC]],
                    'estatus'                 => ['asc' => ['t.estatus' => SORT_ASC],    'desc' => ['t.estatus' => SORT_DESC]],
                    'active_affiliates_count' => [
                        'asc'  => ['active_affiliates_count' => SORT_ASC],
                        'desc' => ['active_affiliates_count' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // If validation fails, return an unfiltered data provider
            return $dataProvider;
        }

        // ────────── Grid filtering conditions ──────────
        $query->andFilterWhere([
            'id'                       => $this->id,
            'fecha_registro_mercantil' => $this->fecha_registro_mercantil,
            'created_at'               => $this->created_at,
            'updated_at'               => $this->updated_at,
            'deleted_at'               => $this->deleted_at,
        ]);

        $query->andFilterWhere(['ilike', 't.nombre',             $this->nombre])
            ->andFilterWhere(['ilike', 't.email',                $this->email])
            ->andFilterWhere(['ilike', 't.telefono',             $this->telefono])
            ->andFilterWhere(['ilike', 't.rif',                  $this->rif])
            ->andFilterWhere(['ilike', 't.estado',               $this->estado])
            ->andFilterWhere(['ilike', 't.municipio',            $this->municipio])
            ->andFilterWhere(['ilike', 't.parroquia',            $this->parroquia])
            ->andFilterWhere(['ilike', 't.direccion',            $this->direccion])
            ->andFilterWhere(['ilike', 't.codigo_asesor',        $this->codigo_asesor])
            ->andFilterWhere(['ilike', 't.lugar_registro',       $this->lugar_registro])
            ->andFilterWhere(['ilike', 't.tomo_registro',        $this->tomo_registro])
            ->andFilterWhere(['ilike', 't.folio_registro',       $this->folio_registro])
            ->andFilterWhere(['ilike', 't.domicilio_fiscal',     $this->domicilio_fiscal])
            ->andFilterWhere(['ilike', 't.contacto_nombre',      $this->contacto_nombre])
            ->andFilterWhere(['ilike', 't.contacto_cedula',      $this->contacto_cedula])
            ->andFilterWhere(['ilike', 't.contacto_telefono',    $this->contacto_telefono])
            ->andFilterWhere(['ilike', 't.contacto_cargo',       $this->contacto_cargo])
            ->andFilterWhere(['ilike', 't.estatus',              $this->estatus]);

        return $dataProvider;
    }
}
