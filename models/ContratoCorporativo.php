<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Pure ActiveRecord for `contratos_corporativos`.
 *
 * SEMANTICS (updated):
 *   A corporation now has ONE contract, but the DB stores one row PER PLAN
 *   the corporation uses. All rows for the same corporation share the SAME
 *   `nrocontrato` (e.g. "CO-J158118181-2026-4") and the SAME vigencia.
 *
 *   - One row per (corporativo_id, plan_id, year(fecha_ini)).
 *   - The "contract" as a business concept = the set of rows sharing a
 *     `nrocontrato`. Use ContractHelper::findCorporateContractGroup() to
 *     load them together.
 *
 * Presentation, persistence, and lifecycle logic lives in
 * `app\components\ContractHelper`.
 *
 * @property int $id
 * @property int $corporativo_id
 * @property int $plan_id
 * @property int|null $clinica_id
 * @property string $nrocontrato
 * @property float $monto_por_afiliado
 * @property int $cantidad_afiliados
 * @property float $monto_total
 * @property string|null $fecha_ini
 * @property string|null $fecha_ven
 * @property string $estatus
 * @property string|null $pdf
 * @property string $created_at
 * @property string|null $updated_at
 * @property int|null $created_by
 * @property int|null $anulado_por
 * @property string|null $anulado_fecha
 * @property string|null $anulado_motivo
 *
 * @property Corporativo $corporativo
 * @property Planes $plan
 * @property RmClinica $clinica
 * @property User $creator
 * @property ContratoCorporativoAfiliado[] $contratoAfiliados
 * @property UserDatos[] $afiliados
 */
class ContratoCorporativo extends ActiveRecord
{
    const STATUS_REGISTRADO = 'Registrado';
    const STATUS_ACTIVO     = 'Activo';
    const STATUS_SUSPENDIDO = 'Suspendido';
    const STATUS_VENCIDO    = 'Vencido';
    const STATUS_ANULADO    = 'Anulado';

    public static function tableName()
    {
        return 'contratos_corporativos';
    }

    public function rules()
    {
        return [
            [['corporativo_id', 'plan_id', 'nrocontrato', 'monto_por_afiliado', 'cantidad_afiliados'], 'required'],
            [['corporativo_id', 'plan_id', 'clinica_id', 'cantidad_afiliados', 'created_by', 'anulado_por'], 'integer'],
            [['monto_por_afiliado', 'monto_total'], 'number'],
            [['fecha_ini', 'fecha_ven', 'created_at', 'updated_at', 'anulado_fecha'], 'safe'],
            [['estatus', 'nrocontrato', 'pdf', 'anulado_motivo'], 'string'],
            [['corporativo_id'], 'exist', 'targetClass' => Corporativo::class, 'targetAttribute' => ['corporativo_id' => 'id']],
            [['plan_id'],        'exist', 'targetClass' => Planes::class,      'targetAttribute' => ['plan_id' => 'id']],
            [['clinica_id'],     'exist', 'skipOnError' => true, 'targetClass' => RmClinica::class, 'targetAttribute' => ['clinica_id' => 'id']],

            // Composite uniqueness: one contract per (corporation, plan, year).
            // Mirrors the DB expression index `uq_contrato_corp_plan_year`.
            [['corporativo_id', 'plan_id', 'fecha_ini'], 'validateUniquePlanPerYear'],
        ];
    }

    /**
     * Validates that no other non-anulled row exists for the same
     * (corporativo_id, plan_id, year(fecha_ini)).
     */
    public function validateUniquePlanPerYear($attribute, $params)
    {
        if (empty($this->fecha_ini) || empty($this->corporativo_id) || empty($this->plan_id)) {
            return;
        }

        $year = (int) date('Y', strtotime($this->fecha_ini));

        $query = self::find()
            ->where([
                'corporativo_id' => $this->corporativo_id,
                'plan_id'        => $this->plan_id,
            ])
            ->andWhere(['!=', 'estatus', self::STATUS_ANULADO])
            ->andWhere(['>=', 'fecha_ini', $year . '-01-01'])
            ->andWhere(['<=', 'fecha_ini', $year . '-12-31']);

        if (!$this->isNewRecord) {
            $query->andWhere(['!=', 'id', $this->id]);
        }

        if ($query->exists()) {
            $this->addError(
                $attribute,
                "Ya existe un contrato para este corporativo, plan y año ({$year})."
            );
        }
    }

    public function attributeLabels()
    {
        return [
            'id'                  => 'ID',
            'corporativo_id'      => 'Corporativo',
            'plan_id'             => 'Plan',
            'clinica_id'          => 'Clínica',
            'nrocontrato'         => 'N° Contrato',
            'monto_por_afiliado'  => 'Monto por Afiliado',
            'cantidad_afiliados'  => 'Cantidad de Afiliados',
            'monto_total'         => 'Monto Total',
            'fecha_ini'           => 'Vigencia Desde',
            'fecha_ven'           => 'Vigencia Hasta',
            'estatus'             => 'Estatus',
            'created_at'          => 'Creado El',
            'updated_at'          => 'Actualizado El',
            'created_by'          => 'Creado Por',
        ];
    }

    public function getCorporativo()
    {
        return $this->hasOne(Corporativo::class, ['id' => 'corporativo_id']);
    }

    public function getPlan()
    {
        return $this->hasOne(Planes::class, ['id' => 'plan_id']);
    }

    public function getClinica()
    {
        return $this->hasOne(RmClinica::class, ['id' => 'clinica_id']);
    }

    public function getCreator()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getContratoAfiliados()
    {
        return $this->hasMany(ContratoCorporativoAfiliado::class, ['contrato_corporativo_id' => 'id']);
    }

    public function getAfiliados()
    {
        return $this->hasMany(UserDatos::class, ['id' => 'user_datos_id'])
            ->via('contratoAfiliados');
    }

    // ============================================================
    // GROUP HELPERS — the "sibling rows" that make up one contract
    // ============================================================

    /**
     * Returns all ContratoCorporativo rows that share this row's
     * `nrocontrato` (i.e., the same corporation-wide contract).
     *
     * @return ContratoCorporativo[]
     */
    public function getSiblings(): array
    {
        if (empty($this->nrocontrato)) {
            return [$this];
        }

        return self::find()
            ->with(['plan', 'clinica'])
            ->where(['nrocontrato' => $this->nrocontrato])
            ->orderBy(['plan_id' => SORT_ASC])
            ->all();
    }

    /**
     * The "primary" row of the group is the one with the lowest plan_id.
     * Used for display when only one representative row is needed.
     */
    public function getIsPrimary(): bool
    {
        $siblings = $this->getSiblings();
        if (empty($siblings)) {
            return true;
        }
        return (int) $siblings[0]->id === (int) $this->id;
    }

    /**
     * Returns the plans covered by this corporation-wide contract.
     *
     * @return Planes[]
     */
    public function getGroupPlans(): array
    {
        return array_map(
            fn($row) => $row->plan,
            array_filter($this->getSiblings(), fn($row) => $row->plan !== null)
        );
    }
}
