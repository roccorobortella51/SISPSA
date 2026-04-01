<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use app\models\Contratos;
use app\models\Cuotas;
use app\models\Pagos;
use app\models\UserDatos;
use yii\helpers\Console;
use yii\db\Query;

/**
 * Migration script to convert existing contracts to simple cuotas
 * PRESERVES ALL PAYMENT HISTORY
 * 
 * Uso:
 *   php yii cuota-migration/check [contractId]           - Verificar estado de contratos
 *   php yii cuota-migration/preview [contractId]         - Vista previa sin cambios
 *   php yii cuota-migration/migrate-contrato 123         - Migrar un contrato específico
 *   php yii cuota-migration/migrate-all                  - Migrar todos los contratos
 *   php yii cuota-migration/backup 123                   - Crear backup de un contrato
 *   php yii cuota-migration/show-backup 123              - Mostrar backup de un contrato
 *   php yii cuota-migration/rollback 123                 - Rollback completo a estado original
 */
class CuotaMigrationController extends Controller
{
    private $backupTable = 'cuotas_backup';

    /**
     * Create a backup of a contract's cuotas before migration (POSTGRESQL VERSION)
     */
    private function createBackup($contratoId)
    {
        $this->stdout("\n💾 Creando backup del contrato #{$contratoId}...\n");

        // Drop backup table if it exists (to ensure clean structure)
        Yii::$app->db->createCommand("DROP TABLE IF EXISTS {$this->backupTable}")->execute();

        // Create backup table with exact same structure as cuotas
        Yii::$app->db->createCommand("
            CREATE TABLE {$this->backupTable} AS 
            SELECT * FROM cuotas WHERE 1=0
        ")->execute();

        $this->stdout("   📦 Tabla de backup creada con la misma estructura\n", Console::FG_YELLOW);

        // Get current columns to ensure we only backup what exists
        $columns = Yii::$app->db->createCommand("
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_name = 'cuotas' 
            ORDER BY ordinal_position
        ")->queryColumn();

        $columnList = '"' . implode('", "', $columns) . '"';

        // Copy current cuotas to backup with explicit column list
        $count = Yii::$app->db->createCommand("
            INSERT INTO {$this->backupTable} ({$columnList})
            SELECT {$columnList} FROM cuotas WHERE contrato_id = :contratoId
        ", [':contratoId' => $contratoId])->execute();

        $this->stdout("   ✅ Backup creado con {$count} cuotas\n", Console::FG_GREEN);

        // Also backup payment relationships for verification
        $paymentCount = Yii::$app->db->createCommand("
            SELECT COUNT(*) FROM cuotas 
            WHERE contrato_id = :contratoId AND id_pago IS NOT NULL
        ", [':contratoId' => $contratoId])->queryScalar();

        if ($paymentCount > 0) {
            $this->stdout("   💰 {$paymentCount} pagos respaldados\n", Console::FG_CYAN);
        }
    }

    /**
     * Migrate a single contract preserving payment history
     * Uso: `yii cuota-migration/migrate-contrato 123`
     */
    public function actionMigrateContrato($contratoId)
    {
        $this->stdout("\n============================================\n", Console::FG_YELLOW);
        $this->stdout("   MIGRANDO CONTRATO #{$contratoId} (CON PRESERVACIÓN DE PAGOS)\n", Console::FG_YELLOW);
        $this->stdout("============================================\n\n", Console::FG_YELLOW);

        $contrato = Contratos::findOne($contratoId);

        if (!$contrato) {
            $this->stderr("❌ Contrato #{$contratoId} no encontrado\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        // Get user info
        $user = UserDatos::findOne($contrato->user_id);
        $userName = $user ? $user->nombres . ' ' . $user->apellidos : 'N/A';

        $this->stdout("📄 Contrato encontrado:\n");
        $this->stdout("   - Cliente: {$userName}\n");
        $this->stdout("   - Fecha inicio: {$contrato->fecha_ini}\n");
        $this->stdout("   - Fecha fin actual: {$contrato->fecha_ven}\n");
        $this->stdout("   - Monto: \${$contrato->monto}\n\n");

        // Get existing cuotas with payment info
        $oldCuotas = Cuotas::find()
            ->where(['contrato_id' => $contrato->id])
            ->orderBy(['fecha_vencimiento' => SORT_ASC])
            ->all();

        $totalCuotas = count($oldCuotas);
        $paidCuotas = 0;
        $pendingCuotas = 0;
        $paymentMap = [];
        $paidCuotaIds = [];

        foreach ($oldCuotas as $cuota) {
            if ($cuota->id_pago || $cuota->estatus == 'pagada') {
                $paidCuotas++;
                $paidCuotaIds[] = $cuota->id;
                // Store payment info for this cuota - WITH MORE DETAILS
                $paymentMap[$cuota->id] = [
                    'id_pago' => $cuota->id_pago,
                    'fecha_pago' => $cuota->fecha_pago,
                    'estatus' => $cuota->estatus,
                    'monto' => $cuota->monto,
                    'fecha_vencimiento' => $cuota->fecha_vencimiento,
                    'numero_cuota' => $cuota->numero_cuota,
                    'orden' => $cuota->numero_cuota ?: 999, // Use numero_cuota if available, otherwise high number
                ];
                $this->stdout("   📝 Pago ID {$cuota->id_pago} (vence {$cuota->fecha_vencimiento}) será preservado con estatus '{$cuota->estatus}'\n", Console::FG_CYAN);
            } else {
                $pendingCuotas++;
            }
        }

        $this->stdout("📊 ESTADO ACTUAL:\n");
        $this->stdout("   - Total cuotas: {$totalCuotas}\n");
        $this->stdout("   - Cuotas pagadas: {$paidCuotas}\n", $paidCuotas > 0 ? Console::FG_GREEN : Console::FG_YELLOW);
        $this->stdout("   - Cuotas pendientes: {$pendingCuotas}\n", $pendingCuotas > 0 ? Console::FG_YELLOW : Console::FG_GREEN);

        if ($paidCuotas > 0) {
            $this->stdout("\n⚠️  IMPORTANTE: Se encontraron {$paidCuotas} cuotas con pagos registrados.\n", Console::FG_YELLOW);
            $this->stdout("   Estas relaciones SERÁN PRESERVADAS en las nuevas cuotas.\n", Console::FG_GREEN);
        }

        // Show current cuotas
        if (!empty($oldCuotas)) {
            $this->stdout("\n📋 Cuotas actuales:\n");
            $this->stdout(str_repeat("-", 80) . "\n");
            foreach ($oldCuotas as $cuota) {
                $cuotaNumero = $cuota->numero_cuota ?? 0;
                $statusIcon = $cuota->id_pago ? "✅ PAGADA" : "⏳ PENDIENTE";
                $pagoInfo = $cuota->id_pago ? " (Pago ID: {$cuota->id_pago})" : "";
                $this->stdout(sprintf(
                    "   #%02d | Vence: %s | %s%s\n",
                    $cuotaNumero,
                    $cuota->fecha_vencimiento,
                    $statusIcon,
                    $pagoInfo
                ), $cuota->id_pago ? Console::FG_GREEN : Console::FG_YELLOW);
            }
        }

        // Calculate new end date
        $newEndDate = clone new \DateTime($contrato->fecha_ini);
        $newEndDate->modify('+1 year');
        $newEndDate->modify('-1 day');

        $this->stdout("\n📅 Nueva fecha fin calculada: " . $newEndDate->format('Y-m-d') . "\n");

        // Confirm migration
        $confirm = $this->confirm("\n¿Migrar este contrato preservando los pagos existentes?");
        if (!$confirm) {
            $this->stdout("⏭️  Operación cancelada\n", Console::FG_YELLOW);
            return ExitCode::OK;
        }

        // Create backup before migration
        $this->createBackup($contratoId);

        // Begin transaction
        $transaction = Yii::$app->db->beginTransaction();

        try {
            // ============================================
            // STEP 1: Handle paid cuotas if they exist
            // ============================================
            if ($paidCuotas > 0) {
                $this->stdout("\n⚠️  Contrato con pagos detectado. Usando método especial...\n", Console::FG_YELLOW);

                // Temporarily detach payments (set id_pago to NULL but remember the mapping)
                foreach ($paymentMap as $oldCuotaId => $paymentInfo) {
                    Yii::$app->db->createCommand()
                        ->update('cuotas', ['id_pago' => null], ['id' => $oldCuotaId])
                        ->execute();
                    $this->stdout("   ⏸️  Pago ID {$paymentInfo['id_pago']} (vence {$paymentInfo['fecha_vencimiento']}) temporalmente desvinculado\n", Console::FG_YELLOW);
                }

                // Now delete ALL old cuotas (including the paid ones - but payments are detached)
                $deletedCount = Cuotas::deleteAll(['contrato_id' => $contrato->id]);
                $this->stdout("🗑️  Eliminadas {$deletedCount} cuotas antiguas (pagos temporalmente desvinculados)\n");
            } else {
                // No paid cuotas - simple case: just delete all
                $this->stdout("\n🔄 Eliminando cuotas existentes...\n");
                $deletedCount = Cuotas::deleteAll(['contrato_id' => $contrato->id]);
                $this->stdout("🗑️  Eliminadas {$deletedCount} cuotas antiguas\n");
            }

            // ============================================
            // STEP 2: Generate new cuotas
            // ============================================
            $this->stdout("🔄 Generando nuevas cuotas con método SIMPLE...\n");
            $result = Cuotas::generateCuotasSimple(
                $contrato->id,
                $contrato->fecha_ini,
                $contrato->monto
            );

            if (!$result['success']) {
                throw new \Exception("Error generando cuotas: " . ($result['error'] ?? 'Unknown error'));
            }

            // ============================================
            // STEP 3: Reattach payments if there were any (FIXED VERSION - WITH DATE-BASED MAPPING)
            // ============================================
            if ($paidCuotas > 0) {
                // Get the new cuotas, ordered by due date
                $newCuotas = Cuotas::find()
                    ->where(['contrato_id' => $contrato->id])
                    ->orderBy(['fecha_vencimiento' => SORT_ASC])
                    ->all();

                // Also sort old paid cuotas by due date
                $oldPaidCuotas = [];
                foreach ($paymentMap as $oldCuotaId => $paymentInfo) {
                    $oldPaidCuotas[] = $paymentInfo;
                }

                // Sort old paid cuotas by due date (oldest first)
                usort($oldPaidCuotas, function ($a, $b) {
                    return strtotime($a['fecha_vencimiento']) - strtotime($b['fecha_vencimiento']);
                });

                $this->stdout("\n🔄 Reasignando pagos a nuevas cuotas (basado en orden de fechas)...\n");
                $reattached = 0;

                // Map payments in order: oldest paid cuota -> oldest new cuota
                for ($i = 0; $i < count($oldPaidCuotas); $i++) {
                    $paymentInfo = $oldPaidCuotas[$i];
                    $targetCuota = isset($newCuotas[$i]) ? $newCuotas[$i] : null;

                    if ($targetCuota) {
                        // FIXED: Preserve BOTH id_pago AND estatus = 'pagada'
                        $updated = Yii::$app->db->createCommand()
                            ->update('cuotas', [
                                'id_pago' => $paymentInfo['id_pago'],
                                'fecha_pago' => $paymentInfo['fecha_pago'],
                                'estatus' => 'pagada'  // FORCE to 'pagada'
                            ], [
                                'id' => $targetCuota->id
                            ])
                            ->execute();

                        if ($updated > 0) {
                            $reattached++;
                            $this->stdout("   ✅ Pago ID {$paymentInfo['id_pago']} (originalmente vence {$paymentInfo['fecha_vencimiento']}) reasignado a cuota #{$targetCuota->numero_cuota} (vence {$targetCuota->fecha_vencimiento})\n", Console::FG_GREEN);

                            // Verify it worked
                            $check = Yii::$app->db->createCommand("
                                SELECT id, numero_cuota, id_pago, estatus, fecha_vencimiento 
                                FROM cuotas 
                                WHERE id = :id
                            ", [':id' => $targetCuota->id])->queryOne();

                            if ($check && $check['id_pago'] == $paymentInfo['id_pago'] && $check['estatus'] == 'pagada') {
                                $this->stdout("      ✓ Verificado: cuota #{$check['numero_cuota']} (vence {$check['fecha_vencimiento']}) ahora tiene pago ID {$check['id_pago']} y estatus 'pagada'\n", Console::FG_GREEN);
                            } else {
                                $this->stdout("      ⚠️  Verificación falló - estado actual: {$check['estatus']}\n", Console::FG_RED);
                            }
                        } else {
                            $this->stdout("   ❌ Error reasignando pago ID {$paymentInfo['id_pago']}\n", Console::FG_RED);
                        }
                    } else {
                        $this->stdout("   ⚠️  No hay suficiente cuotas nuevas para pago ID {$paymentInfo['id_pago']}\n", Console::FG_YELLOW);
                    }
                }

                if ($reattached != $paidCuotas) {
                    $this->stdout("   ⚠️  Se reasignaron {$reattached} de {$paidCuotas} pagos\n", Console::FG_YELLOW);
                }
            }

            // ============================================
            // STEP 4: Update contract end date
            // ============================================
            $contrato->fecha_ven = $newEndDate->format('Y-m-d');
            $contrato->save(false);

            $transaction->commit();

            // ============================================
            // STEP 5: Show results WITH STATUS
            // ============================================
            $this->stdout("\n============================================\n", Console::FG_GREEN);
            $this->stdout("   ✅ MIGRACIÓN COMPLETADA EXITOSAMENTE\n", Console::FG_GREEN);
            $this->stdout("============================================\n", Console::FG_GREEN);
            $this->stdout("   - Contrato #{$contrato->id}\n");
            $this->stdout("   - Cliente: {$userName}\n");
            $this->stdout("   - Nueva fecha fin: {$contrato->fecha_ven}\n");
            $this->stdout("   - Cuotas generadas: 12\n");
            if ($paidCuotas > 0) {
                $this->stdout("   - Pagos preservados: {$reattached} de {$paidCuotas}\n", $reattached == $paidCuotas ? Console::FG_GREEN : Console::FG_YELLOW);
            }

            // Get the new cuotas with fresh data
            $newCuotas = Cuotas::find()
                ->where(['contrato_id' => $contrato->id])
                ->orderBy(['numero_cuota' => SORT_ASC])
                ->all();

            $this->stdout("\n📅 Nuevo calendario de pagos:\n");
            $this->stdout(str_repeat("-", 110) . "\n");

            // Summary counters
            $totalPagadas = 0;
            $totalPendientes = 0;
            $totalVencidas = 0;

            foreach ($newCuotas as $cuota) {
                // Determine status icon and color
                $statusIcon = "⏳";
                $statusText = "pendiente";
                $statusColor = Console::FG_YELLOW;

                if ($cuota->id_pago || $cuota->estatus == 'pagada') {
                    $statusIcon = "✅";
                    $statusText = "pagada";
                    $statusColor = Console::FG_GREEN;
                    $totalPagadas++;
                } elseif ($cuota->fecha_vencimiento < date('Y-m-d')) {
                    $statusIcon = "❌";
                    $statusText = "vencida";
                    $statusColor = Console::FG_RED;
                    $totalVencidas++;
                } else {
                    $totalPendientes++;
                }

                $pagoInfo = $cuota->id_pago ? " (Pago ID: {$cuota->id_pago})" : "";
                $coverageText = "";
                if ($cuota->coverage_start && $cuota->coverage_end) {
                    $coverageText = sprintf(
                        " | Cubre: %s al %s",
                        Yii::$app->formatter->asDate($cuota->coverage_start, 'dd/MM/yy'),
                        Yii::$app->formatter->asDate($cuota->coverage_end, 'dd/MM/yy')
                    );
                }

                $this->stdout(sprintf(
                    "   %s #%02d | Vence: %s | Estado: %s%s%s\n",
                    $statusIcon,
                    $cuota->numero_cuota,
                    Yii::$app->formatter->asDate($cuota->fecha_vencimiento, 'dd/MM/yy'),
                    strtoupper($statusText),
                    $coverageText,
                    $pagoInfo
                ), $statusColor);
            }

            $this->stdout(str_repeat("-", 110) . "\n");
            $this->stdout(sprintf(
                "   📊 RESUMEN: ✅ %d pagada(s) | ⏳ %d pendiente(s) | ❌ %d vencida(s)\n",
                $totalPagadas,
                $totalPendientes,
                $totalVencidas
            ), Console::FG_CYAN);
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->stderr("\n❌ ERROR: " . $e->getMessage() . "\n", Console::FG_RED);
            $this->stderr("   La transacción ha sido revertida. No se perdieron datos.\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    /**
     * Migrate ALL contracts preserving payment history
     * Uso: `yii cuota-migration/migrate-all`
     */
    public function actionMigrateAll()
    {
        $this->stdout("\n============================================\n", Console::FG_YELLOW);
        $this->stdout("   MIGRACIÓN MASIVA PRESERVANDO PAGOS\n", Console::FG_YELLOW);
        $this->stdout("============================================\n\n", Console::FG_YELLOW);

        $this->stderr("⚠️  ADVERTENCIA: Esta operación migrará TODOS los contratos.\n", Console::FG_RED);
        $this->stderr("   Los pagos existentes SERÁN PRESERVADOS.\n", Console::FG_GREEN);
        $this->stderr("   Se recomienda hacer un backup de la base de datos primero.\n\n", Console::FG_YELLOW);

        $confirm = $this->confirm("¿Está ABSOLUTAMENTE SEGURO de continuar?");
        if (!$confirm) {
            $this->stdout("⏭️  Operación cancelada\n", Console::FG_YELLOW);
            return ExitCode::OK;
        }

        // Find all contracts that need migration
        $contratos = Contratos::find()
            ->where(['is not', 'fecha_ini', null])
            ->andWhere(['is not', 'monto', null])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        $total = count($contratos);
        $migrated = 0;
        $errors = 0;
        $skipped = 0;

        $this->stdout("\n📊 Procesando {$total} contratos...\n\n");

        foreach ($contratos as $index => $contrato) {
            $progress = ($index + 1) . '/' . $total;
            $this->stdout("[{$progress}] Procesando contrato #{$contrato->id}... ");

            // Check if already migrated (has coverage fields)
            $sampleCuota = Cuotas::find()
                ->where(['contrato_id' => $contrato->id])
                ->andWhere(['is not', 'coverage_start', null])
                ->one();

            if ($sampleCuota) {
                $this->stdout("⏭️ YA MIGRADO\n", Console::FG_YELLOW);
                $skipped++;
                continue;
            }

            // Call the single migration for this contract
            $result = $this->actionMigrateContrato($contrato->id);

            if ($result === ExitCode::OK) {
                $migrated++;
                $this->stdout("✅ COMPLETADO\n", Console::FG_GREEN);
            } else {
                $errors++;
                $this->stdout("❌ ERROR\n", Console::FG_RED);
            }
        }

        $this->stdout("\n============================================\n", Console::FG_YELLOW);
        $this->stdout("   RESUMEN DE MIGRACIÓN\n", Console::FG_YELLOW);
        $this->stdout("============================================\n", Console::FG_YELLOW);
        $this->stdout("📊 Total contratos procesados: {$total}\n");
        $this->stdout("✅ Migrados exitosamente: {$migrated}\n", Console::FG_GREEN);
        $this->stdout("⏭️  Ya migrados (skipped): {$skipped}\n", Console::FG_YELLOW);
        $this->stdout("❌ Errores: {$errors}\n", Console::FG_RED);
        $this->stdout("============================================\n\n", Console::FG_YELLOW);

        return ExitCode::OK;
    }

    /**
     * Check contracts that need migration - CAN FILTER BY SPECIFIC CONTRACT
     * Uso: 
     *   php yii cuota-migration/check                - Verificar todos los contratos
     *   php yii cuota-migration/check 129            - Verificar solo contrato 129
     */
    public function actionCheck($contratoId = null)
    {
        $this->stdout("\n============================================\n", Console::FG_YELLOW);
        if ($contratoId) {
            $this->stdout("   VERIFICACIÓN DEL CONTRATO #{$contratoId}\n", Console::FG_YELLOW);
        } else {
            $this->stdout("   VERIFICACIÓN DE TODOS LOS CONTRATOS\n", Console::FG_YELLOW);
        }
        $this->stdout("============================================\n\n", Console::FG_YELLOW);

        $sql = "
            SELECT 
                c.id,
                c.fecha_ini,
                c.fecha_ven,
                c.monto,
                COUNT(cu.id) as total_cuotas,
                SUM(CASE WHEN cu.id_pago IS NOT NULL THEN 1 ELSE 0 END) as cuotas_con_pago,
                SUM(CASE WHEN cu.estatus = 'pagada' THEN 1 ELSE 0 END) as cuotas_pagadas,
                SUM(CASE WHEN cu.coverage_start IS NOT NULL THEN 1 ELSE 0 END) as cuotas_con_cobertura
            FROM contratos c
            LEFT JOIN cuotas cu ON c.id = cu.contrato_id
            WHERE c.fecha_ini IS NOT NULL 
              AND c.monto IS NOT NULL
        ";

        $params = [];
        if ($contratoId) {
            $sql .= " AND c.id = :contratoId";
            $params[':contratoId'] = $contratoId;
        }

        $sql .= " GROUP BY c.id ORDER BY c.id";

        $results = Yii::$app->db->createCommand($sql, $params)->queryAll();

        if (empty($results)) {
            if ($contratoId) {
                $this->stdout("❌ No se encontró el contrato #{$contratoId}\n", Console::FG_RED);
            } else {
                $this->stdout("❌ No se encontraron contratos\n", Console::FG_RED);
            }
            return ExitCode::OK;
        }

        $needMigration = 0;
        $alreadyMigrated = 0;
        $noCuotas = 0;
        $totalPaidCuotas = 0;

        foreach ($results as $row) {
            $total = $row['total_cuotas'];
            $conCobertura = $row['cuotas_con_cobertura'];
            $conPago = $row['cuotas_con_pago'];

            $totalPaidCuotas += $conPago;

            $this->stdout("📄 Contrato #{$row['id']}\n");
            $this->stdout("   - Fecha inicio: {$row['fecha_ini']}\n");
            $this->stdout("   - Fecha fin: {$row['fecha_ven']}\n");
            $this->stdout("   - Monto: \${$row['monto']}\n");
            $this->stdout("   - Total cuotas: {$total}\n");
            $this->stdout("   - Cuotas pagadas: {$conPago}\n", $conPago > 0 ? Console::FG_GREEN : Console::FG_YELLOW);

            if ($total == 0) {
                $this->stdout("   ❌ SIN CUOTAS\n", Console::FG_RED);
                $noCuotas++;
            } elseif ($conCobertura == $total) {
                $this->stdout("   ✅ YA MIGRADO (tiene campos de cobertura)\n", Console::FG_GREEN);
                $alreadyMigrated++;
            } else {
                $this->stdout("   ⚠️  NECESITA MIGRACIÓN\n", Console::FG_YELLOW);
                $needMigration++;
            }
            $this->stdout("\n");
        }

        if (!$contratoId) {
            $this->stdout("============================================\n", Console::FG_YELLOW);
            $this->stdout("   RESUMEN GLOBAL\n", Console::FG_YELLOW);
            $this->stdout("============================================\n", Console::FG_YELLOW);
            $this->stdout("📊 Total contratos procesados: " . count($results) . "\n");
            $this->stdout("✅ Ya migrados: {$alreadyMigrated}\n", Console::FG_GREEN);
            $this->stdout("⚠️  Necesitan migración: {$needMigration}\n", Console::FG_YELLOW);
            $this->stdout("❌ Sin cuotas: {$noCuotas}\n", Console::FG_RED);
            $this->stdout("💰 Total de pagos a preservar: {$totalPaidCuotas}\n", Console::FG_CYAN);
            $this->stdout("============================================\n\n", Console::FG_YELLOW);
        }

        return ExitCode::OK;
    }

    /**
     * Preview what would happen without actually migrating - WITH STATUS
     * Uso: 
     *   php yii cuota-migration/preview                - Vista previa de todos los contratos
     *   php yii cuota-migration/preview 129            - Vista previa solo del contrato 129
     */
    public function actionPreview($contratoId = null)
    {
        $this->stdout("\n============================================\n", Console::FG_YELLOW);
        if ($contratoId) {
            $this->stdout("   PREVIEW DEL CONTRATO #{$contratoId} (MODO SIMULACIÓN)\n", Console::FG_YELLOW);
        } else {
            $this->stdout("   PREVIEW DE MIGRACIÓN (MODO SIMULACIÓN)\n", Console::FG_YELLOW);
        }
        $this->stdout("============================================\n\n", Console::FG_YELLOW);

        $query = Contratos::find()
            ->where(['is not', 'fecha_ini', null])
            ->andWhere(['is not', 'monto', null]);

        if ($contratoId) {
            $query->andWhere(['id' => $contratoId]);
        }

        $contratos = $query->limit($contratoId ? 1 : 5)->orderBy(['id' => SORT_ASC])->all();

        if (empty($contratos)) {
            if ($contratoId) {
                $this->stdout("❌ No se encontró el contrato #{$contratoId}\n", Console::FG_RED);
            } else {
                $this->stdout("❌ No se encontraron contratos\n", Console::FG_RED);
            }
            return ExitCode::OK;
        }

        foreach ($contratos as $contrato) {
            $user = UserDatos::findOne($contrato->user_id);
            $userName = $user ? $user->nombres . ' ' . $user->apellidos : 'N/A';

            $oldCuotas = Cuotas::find()
                ->where(['contrato_id' => $contrato->id])
                ->orderBy(['fecha_vencimiento' => SORT_ASC])
                ->all();

            $paidCount = 0;
            $overdueCount = 0;
            $pendingCount = 0;
            $paidDetails = [];

            foreach ($oldCuotas as $c) {
                if ($c->id_pago || $c->estatus == 'pagada') {
                    $paidCount++;
                    $paidDetails[] = [
                        'numero' => $c->numero_cuota ?? 0,
                        'fecha' => $c->fecha_vencimiento,
                        'pago_id' => $c->id_pago
                    ];
                } elseif ($c->fecha_vencimiento < date('Y-m-d')) {
                    $overdueCount++;
                } else {
                    $pendingCount++;
                }
            }

            $this->stdout("📄 Contrato #{$contrato->id} - {$userName}\n");
            $this->stdout("   - Fecha inicio: {$contrato->fecha_ini}\n");
            $this->stdout("   - Fecha fin actual: {$contrato->fecha_ven}\n");
            $this->stdout("   - Monto: \${$contrato->monto}\n");
            $this->stdout("   - Total cuotas: " . count($oldCuotas) . "\n");
            $this->stdout("   - ✅ Pagadas: {$paidCount}\n", $paidCount > 0 ? Console::FG_GREEN : Console::FG_YELLOW);
            $this->stdout("   - ⏳ Pendientes: {$pendingCount}\n", $pendingCount > 0 ? Console::FG_YELLOW : Console::FG_GREEN);
            $this->stdout("   - ❌ Vencidas: {$overdueCount}\n", $overdueCount > 0 ? Console::FG_RED : Console::FG_GREEN);

            // Show current cuotas with status
            if (!empty($oldCuotas)) {
                $this->stdout("\n   📋 CUOTAS ACTUALES:\n");
                $this->stdout("   " . str_repeat("-", 70) . "\n");
                foreach ($oldCuotas as $cuota) {
                    $statusIcon = "⏳";
                    $statusColor = Console::FG_YELLOW;

                    if ($cuota->id_pago || $cuota->estatus == 'pagada') {
                        $statusIcon = "✅";
                        $statusColor = Console::FG_GREEN;
                    } elseif ($cuota->fecha_vencimiento < date('Y-m-d')) {
                        $statusIcon = "❌";
                        $statusColor = Console::FG_RED;
                    }

                    $pagoInfo = $cuota->id_pago ? " (Pago ID: {$cuota->id_pago})" : "";
                    $this->stdout(sprintf(
                        "      %s #%02d | Vence: %s | %s%s\n",
                        $statusIcon,
                        $cuota->numero_cuota ?? 0,
                        $cuota->fecha_vencimiento,
                        $cuota->estatus ?? 'pendiente',
                        $pagoInfo
                    ), $statusColor);
                }
                $this->stdout("   " . str_repeat("-", 70) . "\n");
            }

            if ($paidCount > 0) {
                $this->stdout("\n   💰 DETALLE DE PAGOS:\n");
                foreach ($paidDetails as $p) {
                    $this->stdout("      * Cuota #{$p['numero']} del {$p['fecha']} - Pago ID: {$p['pago_id']}\n", Console::FG_GREEN);
                }
            }

            // Calculate what would happen after migration
            $newEndDate = clone new \DateTime($contrato->fecha_ini);
            $newEndDate->modify('+1 year');
            $newEndDate->modify('-1 day');

            $this->stdout("\n   🔮 DESPUÉS DE LA MIGRACIÓN:\n");
            $this->stdout("   - Nueva fecha fin: " . $newEndDate->format('Y-m-d') . "\n");
            $this->stdout("   - Se generarán 12 nuevas cuotas\n");
            $this->stdout("   - {$paidCount} pago(s) serán preservados\n");

            // Show sample of new cuotas with status prediction
            $startDate = new \DateTime($contrato->fecha_ini);
            $today = new \DateTime();

            $this->stdout("   - Ejemplo de nuevas cuotas:\n");
            for ($i = 1; $i <= min(3, 12); $i++) {
                $dueDate = clone $startDate;
                $dueDate->modify('+' . ($i - 1) . ' months');

                $coverageEnd = clone $dueDate;
                $coverageEnd->modify('+1 month');
                $coverageEnd->modify('-1 day');

                // Predict status
                $statusIcon = "⏳";
                $statusColor = Console::FG_YELLOW;
                $statusText = "pendiente";

                if ($i == 1 && $paidCount > 0) {
                    $statusIcon = "✅";
                    $statusColor = Console::FG_GREEN;
                    $statusText = "pagada (preserva pago)";
                } elseif ($dueDate < $today) {
                    $statusIcon = "❌";
                    $statusColor = Console::FG_RED;
                    $statusText = "vencida";
                }

                $this->stdout(sprintf(
                    "        %s #%02d | Vence: %s | Estado: %s | Cubre: %s al %s\n",
                    $statusIcon,
                    $i,
                    $dueDate->format('Y-m-d'),
                    $statusText,
                    $dueDate->format('Y-m-d'),
                    $coverageEnd->format('Y-m-d')
                ), $statusColor);
            }

            $this->stdout("\n" . str_repeat("=", 80) . "\n\n");
        }

        $this->stdout("✅ Preview completado. No se realizaron cambios.\n");

        return ExitCode::OK;
    }

    /**
     * Create a manual backup of a contract
     * Uso: `yii cuota-migration/backup 123`
     */
    public function actionBackup($contratoId)
    {
        $this->stdout("\n============================================\n", Console::FG_CYAN);
        $this->stdout("   CREANDO BACKUP MANUAL\n", Console::FG_CYAN);
        $this->stdout("============================================\n\n", Console::FG_CYAN);

        $this->createBackup($contratoId);

        $this->stdout("\n✅ Backup completado\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    /**
     * Show backup for a contract
     * Uso: `yii cuota-migration/show-backup 123`
     */
    public function actionShowBackup($contratoId)
    {
        $this->stdout("\n============================================\n", Console::FG_CYAN);
        $this->stdout("   BACKUP DEL CONTRATO #{$contratoId}\n", Console::FG_CYAN);
        $this->stdout("============================================\n\n", Console::FG_CYAN);

        // Check if backup table exists
        $tableExists = Yii::$app->db->createCommand("
            SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_name = '{$this->backupTable}'
            )
        ")->queryScalar();

        if (!$tableExists) {
            $this->stdout("❌ La tabla de backup no existe\n", Console::FG_RED);
            return ExitCode::OK;
        }

        $backupCuotas = Yii::$app->db->createCommand("
            SELECT * FROM {$this->backupTable} WHERE contrato_id = :contratoId ORDER BY fecha_vencimiento
        ", [':contratoId' => $contratoId])->queryAll();

        if (empty($backupCuotas)) {
            $this->stdout("❌ No hay backup para este contrato\n", Console::FG_RED);
            return ExitCode::OK;
        }

        $this->stdout("📋 Backup contiene " . count($backupCuotas) . " cuotas:\n");
        $this->stdout(str_repeat("-", 80) . "\n");
        foreach ($backupCuotas as $cuota) {
            $statusIcon = $cuota['id_pago'] ? "✅" : "⏳";
            $pagoInfo = $cuota['id_pago'] ? " (Pago ID: {$cuota['id_pago']})" : "";
            $estadoValor = $cuota['estado'] ?? $cuota['estatus'] ?? 'pendiente';
            $this->stdout(sprintf(
                "   %s #%02d | Vence: %s | %s%s\n",
                $statusIcon,
                $cuota['numero_cuota'] ?? 0,
                $cuota['fecha_vencimiento'],
                $estadoValor,
                $pagoInfo
            ), $cuota['id_pago'] ? Console::FG_GREEN : Console::FG_YELLOW);
        }

        return ExitCode::OK;
    }

    /**
     * ENHANCED ROLLBACK - Restore contract to original state from backup
     * Uso: `yii cuota-migration/rollback 123`
     */
    public function actionRollback($contratoId)
    {
        $this->stdout("\n============================================\n", Console::FG_RED);
        $this->stdout("   ⚠️  ROLLBACK COMPLETO ⚠️\n", Console::FG_RED);
        $this->stdout("============================================\n\n", Console::FG_RED);

        // Check if backup table exists
        $tableExists = Yii::$app->db->createCommand("
            SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_name = '{$this->backupTable}'
            )
        ")->queryScalar();

        if (!$tableExists) {
            $this->stderr("❌ La tabla de backup no existe\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        // Check if backup exists
        $backupExists = Yii::$app->db->createCommand("
            SELECT COUNT(*) FROM {$this->backupTable} WHERE contrato_id = :contratoId
        ", [':contratoId' => $contratoId])->queryScalar();

        if (!$backupExists) {
            $this->stderr("❌ No se encontró backup para el contrato #{$contratoId}\n", Console::FG_RED);
            $this->stderr("   No se puede realizar rollback sin backup.\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        // Get current table columns
        $currentColumns = Yii::$app->db->createCommand("
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_name = 'cuotas' 
            ORDER BY ordinal_position
        ")->queryColumn();

        // Get backup table columns
        $backupColumns = Yii::$app->db->createCommand("
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_name = '{$this->backupTable}' 
            ORDER BY ordinal_position
        ")->queryColumn();

        $this->stdout("📊 Estructura de tablas:\n");
        $this->stdout("   - Columnas en cuotas actual: " . implode(", ", $currentColumns) . "\n", Console::FG_YELLOW);
        $this->stdout("   - Columnas en backup: " . implode(", ", $backupColumns) . "\n", Console::FG_CYAN);

        // Find common columns (intersection)
        $commonColumns = array_intersect($currentColumns, $backupColumns);
        $this->stdout("   - Columnas comunes: " . implode(", ", $commonColumns) . "\n", Console::FG_GREEN);

        // Show backup info
        $backupCuotas = Yii::$app->db->createCommand("
            SELECT * FROM {$this->backupTable} WHERE contrato_id = :contratoId ORDER BY fecha_vencimiento
        ", [':contratoId' => $contratoId])->queryAll();

        $this->stdout("\n📋 Backup encontrado con " . count($backupCuotas) . " cuotas:\n");
        foreach ($backupCuotas as $cuota) {
            $statusIcon = $cuota['id_pago'] ? "✅" : "⏳";
            $pagoInfo = $cuota['id_pago'] ? " (Pago ID: {$cuota['id_pago']})" : "";
            $estadoValor = $cuota['estado'] ?? $cuota['estatus'] ?? 'pendiente';
            $this->stdout(sprintf(
                "   %s #%02d | Vence: %s | %s%s\n",
                $statusIcon,
                $cuota['numero_cuota'] ?? 0,
                $cuota['fecha_vencimiento'],
                $estadoValor,
                $pagoInfo
            ), $cuota['id_pago'] ? Console::FG_GREEN : Console::FG_YELLOW);
        }

        $this->stdout("\n⚠️  ADVERTENCIA: Esta operación RESTAURARÁ el contrato a su estado original.\n", Console::FG_RED);
        $this->stdout("   Todos los cambios realizados por la migración se perderán.\n", Console::FG_RED);

        $confirm = $this->confirm("¿Está ABSOLUTAMENTE SEGURO de continuar?");
        if (!$confirm) {
            $this->stdout("⏭️  Operación cancelada\n", Console::FG_YELLOW);
            return ExitCode::OK;
        }

        // Begin transaction
        $transaction = Yii::$app->db->beginTransaction();

        try {
            // Step 1: Detach all payments from current cuotas (to avoid FK constraints)
            Yii::$app->db->createCommand("
                UPDATE cuotas SET id_pago = NULL WHERE contrato_id = :contratoId
            ", [':contratoId' => $contratoId])->execute();

            // Step 2: Delete current cuotas
            $deleted = Cuotas::deleteAll(['contrato_id' => $contratoId]);
            $this->stdout("🗑️  Eliminadas {$deleted} cuotas actuales\n");

            // Step 3: Restore from backup using only common columns
            $restoredCount = 0;

            foreach ($backupCuotas as $cuota) {
                // Remove 'id' from the columns to let auto-increment generate new one
                $restoreColumns = array_diff($commonColumns, ['id']);

                // Build arrays for columns and values
                $columns = [];
                $params = [];
                $placeholders = [];
                $i = 0;

                foreach ($restoreColumns as $col) {
                    // Get the value from backup
                    if (isset($cuota[$col])) {
                        $value = $cuota[$col];
                    } elseif ($col == 'estatus' && isset($cuota['estado'])) {
                        // Handle case where backup has 'estado' but we need 'estatus'
                        $value = $cuota['estado'];
                    } else {
                        // Skip if value doesn't exist
                        continue;
                    }

                    $columns[] = $col;
                    $placeholders[] = ":p{$i}";
                    $params[":p{$i}"] = $value;
                    $i++;
                }

                // Make sure we have columns to insert
                if (empty($columns)) {
                    $this->stderr("   ⚠️ No hay columnas para insertar, saltando cuota\n", Console::FG_YELLOW);
                    continue;
                }

                // Build column list and VALUES
                $columnList = '"' . implode('", "', $columns) . '"';
                $valuesList = implode(", ", $placeholders);

                $insertSql = "
                    INSERT INTO cuotas ({$columnList}) 
                    VALUES ({$valuesList})
                ";

                try {
                    Yii::$app->db->createCommand($insertSql, $params)->execute();
                    $restoredCount++;

                    // Debug output for first cuota
                    if ($restoredCount == 1) {
                        $this->stdout("   📝 Primera cuota insertada con columnas: " . implode(", ", $columns) . "\n", Console::FG_CYAN);
                    }
                } catch (\Exception $e) {
                    $this->stderr("   ❌ Error insertando cuota: " . $e->getMessage() . "\n", Console::FG_RED);
                    $this->stderr("      SQL: {$insertSql}\n", Console::FG_RED);
                    $this->stderr("      Params: " . print_r($params, true) . "\n", Console::FG_RED);
                    throw $e;
                }
            }

            $this->stdout("✅ Restauradas {$restoredCount} cuotas desde backup\n", Console::FG_GREEN);

            $transaction->commit();

            $this->stdout("\n============================================\n", Console::FG_GREEN);
            $this->stdout("   ✅ ROLLBACK COMPLETADO EXITOSAMENTE\n", Console::FG_GREEN);
            $this->stdout("============================================\n", Console::FG_GREEN);
            $this->stdout("   - Contrato #{$contratoId} restaurado a estado original\n");
            $this->stdout("   - {$restoredCount} cuotas restauradas\n");

            // Show restored cuotas
            $restoredCuotas = Cuotas::find()
                ->where(['contrato_id' => $contratoId])
                ->orderBy(['numero_cuota' => SORT_ASC])
                ->all();

            $this->stdout("\n📋 Cuotas restauradas:\n");
            $this->stdout(str_repeat("-", 80) . "\n");
            foreach ($restoredCuotas as $cuota) {
                $statusIcon = $cuota->id_pago ? "✅" : "⏳";
                $pagoInfo = $cuota->id_pago ? " (Pago ID: {$cuota->id_pago})" : "";
                $this->stdout(sprintf(
                    "   %s #%02d | Vence: %s | %s%s\n",
                    $statusIcon,
                    $cuota->numero_cuota ?? 0,
                    $cuota->fecha_vencimiento,
                    $cuota->estatus,
                    $pagoInfo
                ), $cuota->id_pago ? Console::FG_GREEN : Console::FG_YELLOW);
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->stderr("\n❌ ERROR DURANTE ROLLBACK: " . $e->getMessage() . "\n", Console::FG_RED);
            $this->stderr("   La transacción ha sido revertida.\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
