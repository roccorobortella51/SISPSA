<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cuotas".
 *
 * @property int $id
 * @property string $created_at
 * @property int|null $contrato_id
 * @property string|null $fecha_vencimiento
 * @property float|null $monto
 * @property string|null $estatus
 * @property string|null $fecha_pago
 * @property float|null $rate_usd_bs
 * @property float|null $monto_usd
 * @property int|null $id_pago
 */
class Cuotas extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cuotas';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['contrato_id', 'fecha_vencimiento', 'monto', 'estatus', 'fecha_pago', 'rate_usd_bs', 'id_pago'], 'default', 'value' => null],
            [['created_at', 'fecha_vencimiento', 'fecha_pago'], 'safe'],
            [['contrato_id', 'id_pago'], 'integer'],
            [['monto', 'rate_usd_bs', 'monto_usd'], 'number'],
            [['monto', 'monto_usd'], 'number', 'numberPattern' => '/^\d+(\.\d{1,2})?$/'], // 2 decimal validation
            [['estatus'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Ensure monto and monto_usd always have 2 decimal places
            if ($this->monto !== null) {
                $this->monto = round($this->monto, 2);
            }
            if ($this->monto_usd !== null) {
                $this->monto_usd = round($this->monto_usd, 2);
            }
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'contrato_id' => 'Contrato ID',
            'fecha_vencimiento' => 'Fecha Vencimiento',
            'monto' => 'Monto',
            'estatus' => 'Estatus',
            'fecha_pago' => 'Fecha Pago',
            'rate_usd_bs' => 'Rate Usd Bs',
            'monto_usd' => 'Monto USD',
            'id_pago' => 'ID Pago',
        ];
    }

    /**
     * Get the contract associated with this cuota
     */
    public function getContrato()
    {
        return $this->hasOne(Contratos::class, ['id' => 'contrato_id']);
    }

    /**
     * Get pending cuotas for a user
     */
    public static function getPendingCuotasForUser($user_id)
    {
        if (empty($user_id)) {
            return [];
        }

        // Find all contracts for this user (including suspended ones as they might have pending cuotas)
        $contratos = Contratos::find()
            ->where(['user_id' => $user_id])
            ->all();

        if (empty($contratos)) {
            Yii::info("No contracts found for user_id: " . $user_id);
            return [];
        }

        $contratoIds = [];
        foreach ($contratos as $contrato) {
            $contratoIds[] = $contrato->id;
            Yii::info("Found contract ID: " . $contrato->id . " for user_id: " . $user_id);
        }

        // Find pending cuotas for these contracts
        $cuotas = self::find()
            ->where(['IN', 'contrato_id', $contratoIds])
            ->andWhere(['estatus' => 'pendiente'])
            ->orderBy(['fecha_vencimiento' => SORT_ASC])
            ->all();

        Yii::info("Found " . count($cuotas) . " pending cuotas for user_id: " . $user_id . " with contract IDs: " . implode(', ', $contratoIds));

        foreach ($cuotas as $cuota) {
            // Use rounded values for logging
            $monto = $cuota->monto ? round($cuota->monto, 2) : 0;
            $monto_usd = $cuota->monto_usd ? round($cuota->monto_usd, 2) : 0;
            Yii::info("Cuota ID: " . $cuota->id . ", Contrato ID: " . $cuota->contrato_id . ", Monto USD: " . $monto_usd . ", Monto: " . $monto);
        }

        return $cuotas;
    }

    /**
     * Get the last cuota (paid or pending) for a contract
     */
    public static function getLastCuotaForContract($contrato_id)
    {
        return self::find()
            ->where(['contrato_id' => $contrato_id])
            ->orderBy(['fecha_vencimiento' => SORT_DESC])
            ->one();
    }

    /**
     * Get next due date based on last cuota
     */
    public static function calculateNextDueDate($lastCuotaDate = null)
    {
        if ($lastCuotaDate) {
            $date = new \DateTime($lastCuotaDate);
        } else {
            $date = new \DateTime();
        }

        // Add one month and set day to 7th (typical due day)
        $date->modify('+1 month');
        $date->setDate($date->format('Y'), $date->format('m'), 7);

        return $date->format('Y-m-d');
    }

    /**
     * Preview advance cuotas without saving - CORREGIDO: Cambiado de nombre a previewCuotasAdelantadas
     */
    public static function previewCuotasAdelantadas($contrato_id, $num_cuotas, $fecha_inicio = null, $meses = '', $modo = 'cantidad', $fecha_limite = null)
    {
        try {
            // Get contract details
            $contrato = Contratos::findOne($contrato_id);
            if (!$contrato) {
                throw new \Exception("Contrato no encontrado");
            }

            // Get last cuota
            $lastCuota = self::getLastCuotaForContract($contrato_id);
            $startDate = $fecha_inicio;

            if (!$startDate && $lastCuota) {
                $startDate = $lastCuota->fecha_vencimiento;
            } elseif (!$startDate) {
                // CORREGIDO: Cambiar fecha_inicio por fecha_ini
                $startDate = $contrato->fecha_ini ?: date('Y-m-d');
            }

            // Determine amount
            $montoCuota = $contrato->monto;
            if (!$montoCuota && $lastCuota) {
                $montoCuota = $lastCuota->monto_usd ?: $lastCuota->monto;
            }

            $previewCuotas = [];
            $currentDate = new \DateTime($startDate);

            for ($i = 0; $i < $num_cuotas; $i++) {
                $currentDate->modify('+1 month');
                $fechaVencimiento = $currentDate->format('Y-m-07');

                // Check if exists
                $exists = self::find()
                    ->where([
                        'contrato_id' => $contrato_id,
                        'fecha_vencimiento' => $fechaVencimiento
                    ])
                    ->exists();

                $previewCuotas[] = [
                    'numero' => $i + 1,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'monto' => round($montoCuota, 2),
                    'existe' => $exists,
                    'mes' => $currentDate->format('F Y')
                ];
            }

            return [
                'success' => true,
                'contrato' => [
                    'id' => $contrato->id,
                    'nrocontrato' => $contrato->nrocontrato,
                    'user_id' => $contrato->user_id,
                    'fecha_ini' => $contrato->fecha_ini // Añadir esto para referencia
                ],
                'last_cuota' => $lastCuota ? [
                    'fecha_vencimiento' => $lastCuota->fecha_vencimiento,
                    'estatus' => $lastCuota->estatus,
                    'monto' => $lastCuota->monto_usd ?: $lastCuota->monto
                ] : null,
                'preview' => $previewCuotas,
                'total' => round($montoCuota * $num_cuotas, 2)
            ];
        } catch (\Exception $e) {
            \Yii::error("Error in previewCuotasAdelantadas: " . $e->getMessage(), 'cuotas');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate advance cuotas for a contract with 1-year limit
     */
    public static function generarCuotasAdelantadas($contrato_id, $num_cuotas, $fecha_inicio = null, $meses = '', $fecha_limite = null)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Get contract details
            $contrato = Contratos::findOne($contrato_id);
            if (!$contrato) {
                throw new \Exception("Contrato no encontrado");
            }

            // Get last cuota to determine next date
            $lastCuota = self::getLastCuotaForContract($contrato_id);
            $startDate = $fecha_inicio;

            if (!$startDate && $lastCuota) {
                // Start from last cuota's due date
                $startDate = $lastCuota->fecha_vencimiento;
            } elseif (!$startDate) {
                // Start from contract start date or today
                $startDate = $contrato->fecha_ini ?: date('Y-m-d');
            }

            // Determine amount to use
            $montoCuota = $contrato->monto;
            if (!$montoCuota && $lastCuota) {
                $montoCuota = $lastCuota->monto_usd ?: $lastCuota->monto;
            }

            if (!$montoCuota) {
                throw new \Exception("No se puede determinar el monto de la cuota");
            }

            $generatedCuotas = [];
            $currentDate = new \DateTime($startDate);

            // Preparar array de cuotas a generar
            $cuotas_a_generar = [];

            // Si hay meses específicos
            if (!empty($meses)) {
                $mesesArray = explode(',', $meses);
                $contador = 1;
                foreach ($mesesArray as $mes) {
                    // Crear fecha del primer día del mes
                    $fechaVencimiento = new \DateTime($mes . '-01');
                    // Establecer como día 7 del mes
                    $fechaVencimiento->setDate(
                        $fechaVencimiento->format('Y'),
                        $fechaVencimiento->format('m'),
                        7
                    );

                    $cuotas_a_generar[] = [
                        'numero' => $contador++,
                        'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
                        'mes' => $fechaVencimiento->format('F Y')
                    ];
                }
            } else {
                // Modo: por cantidad de cuotas
                for ($i = 0; $i < $num_cuotas; $i++) {
                    $fechaVencimiento = clone $currentDate;
                    $fechaVencimiento->modify('+' . ($i + 1) . ' month');
                    $fechaVencimiento->setDate(
                        $fechaVencimiento->format('Y'),
                        $fechaVencimiento->format('m'),
                        7
                    );

                    $cuotas_a_generar[] = [
                        'numero' => $i + 1,
                        'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
                        'mes' => $fechaVencimiento->format('F Y')
                    ];
                }
            }

            // ==============================================
            // VALIDACIÓN DE LÍMITE DE 1 AÑO - INSERTA ESTO
            // ==============================================
            if ($fecha_limite) {
                $fechaLimiteObj = new \DateTime($fecha_limite);
                foreach ($cuotas_a_generar as $cuota) {
                    $fechaCuota = new \DateTime($cuota['fecha_vencimiento']);
                    if ($fechaCuota > $fechaLimiteObj) {
                        return [
                            'success' => false,
                            'error' => "No se pueden generar cuotas después de " . $fechaLimiteObj->format('Y-m-d') .
                                " (límite de 1 año desde inicio del contrato)",
                            'generated' => 0
                        ];
                    }
                }
            }
            // ==============================================
            // FIN DE LA VALIDACIÓN
            // ==============================================

            // Generar las cuotas válidas
            foreach ($cuotas_a_generar as $cuotaData) {
                $fechaVencimiento = $cuotaData['fecha_vencimiento'];

                // Check if cuota already exists for this date
                $existingCuota = self::find()
                    ->where([
                        'contrato_id' => $contrato_id,
                        'fecha_vencimiento' => $fechaVencimiento
                    ])
                    ->one();

                if ($existingCuota) {
                    Yii::info("Cuota ya existe para {$fechaVencimiento} en contrato #{$contrato_id}", 'cuotas');
                    continue;
                }

                // Create new cuota
                $cuota = new self([
                    'contrato_id' => $contrato_id,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'monto' => round($montoCuota, 2),
                    'monto_usd' => round($montoCuota, 2),
                    'estatus' => 'pendiente',
                    'rate_usd_bs' => 1.0, // Default rate
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                if ($cuota->save()) {
                    $generatedCuotas[] = $cuota;
                    Yii::info("Cuota adelantada generada: ID {$cuota->id} para {$fechaVencimiento}", 'cuotas');
                } else {
                    Yii::error("Error generando cuota: " . print_r($cuota->errors, true), 'cuotas');
                }
            }

            $transaction->commit();
            return [
                'success' => true,
                'generated' => count($generatedCuotas),
                'cuotas' => $generatedCuotas
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("Error generando cuotas adelantadas: " . $e->getMessage(), 'cuotas');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
