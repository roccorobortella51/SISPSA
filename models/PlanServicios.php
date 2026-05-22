<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "plan_servicios".
 *
 * @property int $id
 * @property int $plan_id
 * @property int|null $clinica_id
 * @property string $servicio_nombre
 * @property string|null $plazo_espera
 * @property string|null $descripcion
 * @property int $orden
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 *
 * @property Planes $plan
 * @property RmClinica $clinica
 */
class PlanServicios extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'plan_servicios';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['plan_id', 'servicio_nombre'], 'required'],
            [['plan_id', 'clinica_id', 'orden'], 'integer'],
            [['descripcion'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['servicio_nombre', 'plazo_espera'], 'string', 'max' => 255],
            [['plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => Planes::class, 'targetAttribute' => ['plan_id' => 'id']],
            [['clinica_id'], 'exist', 'skipOnError' => true, 'targetClass' => RmClinica::class, 'targetAttribute' => ['clinica_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'plan_id' => 'Plan',
            'clinica_id' => 'Clínica',
            'servicio_nombre' => 'Nombre del Servicio',
            'plazo_espera' => 'Plazo de Espera (P/E)',
            'descripcion' => 'Descripción',
            'orden' => 'Orden',
            'created_at' => 'Creado',
            'updated_at' => 'Actualizado',
            'deleted_at' => 'Eliminado',
        ];
    }

    /**
     * Gets query for [[Plan]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlan()
    {
        return $this->hasOne(Planes::class, ['id' => 'plan_id']);
    }

    /**
     * Gets query for [[Clinica]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClinica()
    {
        return $this->hasOne(RmClinica::class, ['id' => 'clinica_id']);
    }

    /**
     * Get services for a specific plan and clinic
     *
     * @param int $planId
     * @param int|null $clinicaId
     * @return array|ActiveRecord[]
     */
    public static function getServicesForPlan($planId, $clinicaId = null)
    {
        $query = self::find()
            ->where(['plan_id' => $planId])
            ->andWhere(['deleted_at' => null])
            ->orderBy(['orden' => SORT_ASC]);

        if ($clinicaId) {
            $query->andWhere(['clinica_id' => $clinicaId]);
        }

        $services = $query->all();

        // If no clinic-specific services found, get default services (clinica_id IS NULL)
        if (empty($services) && $clinicaId) {
            $services = self::find()
                ->where(['plan_id' => $planId])
                ->andWhere(['clinica_id' => null])
                ->andWhere(['deleted_at' => null])
                ->orderBy(['orden' => SORT_ASC])
                ->all();
        }

        return $services;
    }
}
