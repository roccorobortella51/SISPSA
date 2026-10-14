<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\User;
use app\models\UserDatos;
use app\components\UserHelper;

/**
 * UserSearch represents the model behind the search form of `app\models\User`.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * KEY DESIGN NOTES (v2.1):
 *
 *   1. STATUS FILTER
 *      The grid form sends 1 (Activo) or 0 (Inactivo), but the DB stores
 *      10 (Activo), 9 (Inactivo), or 0 (Deleted). We translate before
 *      filtering so the two representations never diverge.
 *
 *   2. LEFT JOIN INSTEAD OF INNER JOIN
 *      We use leftJoinWith(['userDatos']) so that User rows that are
 *      missing a linked UserDatos row (orphans) still appear in the grid
 *      with a "Sin datos" badge, instead of silently disappearing.
 *
 *   3. ALIASED COLUMNS
 *      All user_datos columns selected by the query are aliased with a
 *      `ud_` prefix (ud_nombres, ud_cedula, …). The grid can then fall
 *      back to those aliases when the relational accessor
 *      ($model->userDatos) returns null for orphan users.
 *
 *   4. POSTGRES RESERVED WORD
 *      `user` is a reserved word in PostgreSQL. It MUST be double-quoted
 *      in any raw SQL fragment (join condition, select list, order by).
 *      Yii's query builder handles this automatically when you pass
 *      `'user.status'` in an array condition — but NOT when you pass a
 *      raw string like `'user_datos.user_login_id = user.id'`.
 *      Every raw fragment below uses `"user"` explicitly.
 * ─────────────────────────────────────────────────────────────────────────────
 */
class UserSearch extends User
{
    public $roleName;
    public $idasesor;
    public $nombrecompleto;
    public $agencia;
    public $cedula;        // String (accepts formatted cédula like "V-12345678")
    public $telefono;      // String

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[
                'username',
                'auth_key',
                'password_hash',
                'password_reset_token',
                'email',
                'nombrecompleto',
                'roleName',
                'agencia',
                'telefono',
            ], 'safe'],
            [['status', 'created_at', 'updated_at', 'id', 'idasesor'], 'integer'],
            [['cedula'], 'safe'],   // String to allow partial + formatted search
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    // ============================================================
    // SHARED: JOIN + SELECT + STATUS FILTER
    // ============================================================

    /**
     * Attach the base join + aliased select to a User query.
     *
     * Uses LEFT JOIN so orphan users (no linked UserDatos) still
     * appear in the grid.
     *
     * IMPORTANT: PostgreSQL requires `"user"` to be double-quoted in
     * raw SQL because `user` is a reserved keyword.
     *
     * @param \yii\db\ActiveQuery $query
     * @return \yii\db\ActiveQuery
     */
    private function attachUserDatosJoin($query)
    {
        $query->leftJoin(
            'user_datos',
            'user_datos.user_login_id = "user".id'
        );

        $query->addSelect([
            '"user".*',
            'user_datos.id            AS ud_id',
            'user_datos.nombres       AS ud_nombres',
            'user_datos.apellidos     AS ud_apellidos',
            'user_datos.cedula        AS ud_cedula',
            'user_datos.tipo_cedula   AS ud_tipo_cedula',
            'user_datos.telefono      AS ud_telefono',
            'user_datos.role          AS ud_role',
            'user_datos.estatus       AS ud_estatus',
            'user_datos.clinica_id    AS ud_clinica_id',
        ]);

        return $query;
    }

    /**
     * Apply the STATUS filter, translating the form value (1/0)
     * to the DB value (10 for active, 9/0 for inactive/deleted).
     *
     * @param \yii\db\ActiveQuery $query
     * @param mixed               $status
     */
    private function applyStatusFilter($query, $status)
    {
        if ($status === null || $status === '') {
            return;
        }

        if ((int)$status === 1) {
            $query->andWhere(['user.status' => User::STATUS_ACTIVE]);   // 10
        } elseif ((int)$status === 0) {
            $query->andWhere(['in', 'user.status', [
                User::STATUS_INACTIVE,   // 9
                User::STATUS_DELETED,    // 0
            ]]);
        }
    }

    // ============================================================
    // MAIN SEARCH
    // ============================================================

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
        $rol   = UserHelper::getMyRol();
        $query = User::find();

        // Attach the LEFT JOIN + aliased select
        $this->attachUserDatosJoin($query);

        // ── Role-based scoping ──
        if ($rol == "DIRECTOR-COMERCIALIZACIÓN" || $rol == "Agente") {
            $query->leftJoin(
                'auth_assignment',
                '"user"."id" = CAST("auth_assignment"."user_id" AS INTEGER)'
            )->andFilterWhere(['auth_assignment.item_name' => "Asesor"]);
        }

        // ── Data provider ──
        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ],
                'attributes' => array_merge(parent::attributes(), [
                    'roleName' => [
                        'asc'  => ['user_datos.role' => SORT_ASC],
                        'desc' => ['user_datos.role' => SORT_DESC],
                    ],
                    'cedula' => [
                        'asc'  => ['user_datos.cedula' => SORT_ASC],
                        'desc' => ['user_datos.cedula' => SORT_DESC],
                    ],
                    'telefono' => [
                        'asc'  => ['user_datos.telefono' => SORT_ASC],
                        'desc' => ['user_datos.telefono' => SORT_DESC],
                    ],
                ]),
            ],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // ============================================================
        // FILTERS
        // ============================================================

        // ── Exact / numeric filters ──
        $query->andFilterWhere([
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user.id'    => $this->id,
        ]);

        // ── STATUS FILTER (translated) ──
        $this->applyStatusFilter($query, $this->status);

        // ── Text filters ──
        // Note: `user.username` and `user.email` are safe here because
        // Yii's andFilterWhere() understands array conditions and
        // quotes identifiers automatically.
        $query
            ->andFilterWhere(['ilike', 'user.username', $this->username])
            ->andFilterWhere(['ilike', 'auth_key', $this->auth_key])
            ->andFilterWhere(['ilike', 'password_hash', $this->password_hash])
            ->andFilterWhere(['ilike', 'password_reset_token', $this->password_reset_token])
            ->andFilterWhere(['ilike', 'user.email', $this->email])
            ->andFilterWhere(['like', 'user_datos.role', $this->roleName]);

        // ── Full-name filter ──
        if (!empty($this->nombrecompleto)) {
            $query->andFilterWhere([
                'or',
                ['ilike', 'user_datos.nombres',   $this->nombrecompleto],
                ['ilike', 'user_datos.apellidos', $this->nombrecompleto],
            ]);
        }

        // ── Cédula filter (cast integer → text for ILIKE) ──
        if (!empty($this->cedula)) {
            $query->andWhere(['ilike', 'CAST(user_datos.cedula AS TEXT)', $this->cedula]);
        }

        // ── Teléfono filter ──
        if (!empty($this->telefono)) {
            $query->andFilterWhere(['ilike', 'user_datos.telefono', $this->telefono]);
        }

        return $dataProvider;
    }

    // ============================================================
    // SEARCH: AGENTES / ASESORES
    // ============================================================

    /**
     * Search variant for the "Usuarios por Agente" grid.
     *
     * @param array       $params
     * @param string|null $formName
     * @return ActiveDataProvider
     */
    public function searchAgentes($params, $formName = null)
    {
        $rol   = UserHelper::getMyRol();
        $query = User::find();

        // Attach the LEFT JOIN + aliased select
        $this->attachUserDatosJoin($query);

        // Additional joins for asesor / agente
        $query->joinWith(['userDatos.asesor']);
        $query->joinWith(['userDatos.asesor.agente']);

        // ── Role-based scoping ──
        if (
            $rol == "DIRECTOR-COMERCIALIZACIÓN"
            || $rol == "Agente"
            || $rol == "admin"
            || $rol == "superadmin"
        ) {
            $query->leftJoin(
                'auth_assignment',
                '"user"."id" = CAST("auth_assignment"."user_id" AS INTEGER)'
            )->andFilterWhere(['auth_assignment.item_name' => "Asesor"]);
        }

        if ($rol == "Agente") {
            $query->andFilterWhere(['agente_fuerza.agente_id' => UserHelper::getAgenteId()]);
        }

        // ── Data provider ──
        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ],
                'attributes' => array_merge(parent::attributes(), [
                    'roleName' => [
                        'asc'  => ['user_datos.role' => SORT_ASC],
                        'desc' => ['user_datos.role' => SORT_DESC],
                    ],
                    'cedula' => [
                        'asc'  => ['user_datos.cedula' => SORT_ASC],
                        'desc' => ['user_datos.cedula' => SORT_DESC],
                    ],
                    'telefono' => [
                        'asc'  => ['user_datos.telefono' => SORT_ASC],
                        'desc' => ['user_datos.telefono' => SORT_DESC],
                    ],
                ]),
            ],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // ============================================================
        // FILTERS
        // ============================================================

        // ── Exact / numeric filters ──
        $query->andFilterWhere([
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
            'user.id'          => $this->id,
            'agente_fuerza.id' => $this->idasesor,
        ]);

        // ── STATUS FILTER (translated) ──
        $this->applyStatusFilter($query, $this->status);

        // ── Text filters ──
        // NOTE: the previous version had a duplicated 'username' filter
        // with an invalid fragment `'username', 'user.username'` — that
        // second one was nonsense (column 'user.username' with alias
        // 'username'). Removed here.
        $query
            ->andFilterWhere(['ilike', 'user.username', $this->username])
            ->andFilterWhere(['ilike', 'auth_key', $this->auth_key])
            ->andFilterWhere(['ilike', 'password_hash', $this->password_hash])
            ->andFilterWhere(['ilike', 'password_reset_token', $this->password_reset_token])
            ->andFilterWhere(['ilike', 'user.email', $this->email])
            ->andFilterWhere(['like', 'user_datos.role', $this->roleName])
            ->andFilterWhere(['like', 'agente.nom', $this->agencia]);

        // ── Full-name filter ──
        if (!empty($this->nombrecompleto)) {
            $query->andFilterWhere([
                'or',
                ['ilike', 'user_datos.nombres',   $this->nombrecompleto],
                ['ilike', 'user_datos.apellidos', $this->nombrecompleto],
            ]);
        }

        // ── Cédula filter ──
        if (!empty($this->cedula)) {
            $query->andWhere(['ilike', 'CAST(user_datos.cedula AS TEXT)', $this->cedula]);
        }

        // ── Teléfono filter ──
        if (!empty($this->telefono)) {
            $query->andFilterWhere(['ilike', 'user_datos.telefono', $this->telefono]);
        }

        return $dataProvider;
    }
}
