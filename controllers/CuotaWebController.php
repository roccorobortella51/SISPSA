<?php
// controllers/CuotaWebController.php   

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use app\models\Cuotas;
use app\models\Contratos;
use app\models\UserDatos;

class CuotaWebController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Only logged in users
                    ],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (in_array($action->id, [
            'generar',
            'generar-mensual',
            'generar-atrasadas',
            'verificar-vencidas',
            'verificar-diario',
            'resumen-proximos-vencer',
            'resumen-atrasadas',
            'verificar-todo',
            'actualizar-montos',
            'verificar-contratos-vencidos',
            'verificar-espera',
            'reparar-relacion-pagos',
            'diagnostico-inconsistencias',
            'generar-cuotas-faltantes',
            'generar-adelantadas',
            'preview-adelantadas',
            'get-user-contracts',
            'search-user',
            'migration-check',
            'migration-preview',
            'migration-backup',
            'migration-show-backup',
            'migration-migrate-contrato',
            'migration-migrate-all',
            'migration-rollback',
            'migration-list-contracts',  // ← AÑADIR ESTO
            'daily-check',  // ← ADD THIS LINE
        ])) {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * Lista los contratos que necesitan migración
     */
    public function actionMigrationListContracts()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $sql = "
            SELECT 
                c.id,
                c.user_id,
                c.fecha_ini,
                c.fecha_ven,
                c.monto,
                c.estatus,
                ud.nombres,
                ud.apellidos,
                COUNT(cu.id) as total_cuotas,
                SUM(CASE WHEN cu.id_pago IS NOT NULL THEN 1 ELSE 0 END) as cuotas_pagadas,
                SUM(CASE WHEN cu.coverage_start IS NOT NULL THEN 1 ELSE 0 END) as cuotas_migradas
            FROM contratos c
            LEFT JOIN user_datos ud ON c.user_id = ud.id
            LEFT JOIN cuotas cu ON c.id = cu.contrato_id
            WHERE c.fecha_ini IS NOT NULL 
              AND c.monto IS NOT NULL
            GROUP BY c.id, c.user_id, c.fecha_ini, c.fecha_ven, c.monto, c.estatus, ud.nombres, ud.apellidos
            ORDER BY c.id DESC
        ";

            $contratos = Yii::$app->db->createCommand($sql)->queryAll();

            return [
                'success' => true,
                'contratos' => $contratos
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verificar estado de contratos
     */
    public function actionMigrationCheck($contratoId = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            // Construir el comando
            $command = "php " . Yii::getAlias('@app/yii') . " cuota-migration/check";
            if ($contratoId) {
                $command .= " " . $contratoId;
            }

            $output = [];
            $returnCode = 0;
            exec($command . " 2>&1", $output, $returnCode);

            return [
                'success' => $returnCode === 0,
                'output' => implode("\n", $output),
                'message' => $returnCode === 0 ? 'Verificación completada' : 'Error en verificación',
                'returnCode' => $returnCode
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => "Error: " . $e->getMessage(),
                'message' => 'Error ejecutando comando',
                'returnCode' => -1
            ];
        }
    }

    /**
     * Preview de migración
     */
    public function actionMigrationPreview($contratoId = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $command = "php " . Yii::getAlias('@app/yii') . " cuota-migration/preview";
            if ($contratoId) {
                $command .= " " . $contratoId;
            }

            $output = [];
            $returnCode = 0;
            exec($command . " 2>&1", $output, $returnCode);

            return [
                'success' => $returnCode === 0,
                'output' => implode("\n", $output),
                'message' => $returnCode === 0 ? 'Preview completado' : 'Error en preview',
                'returnCode' => $returnCode
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => "Error: " . $e->getMessage(),
                'message' => 'Error ejecutando comando',
                'returnCode' => -1
            ];
        }
    }

    /**
     * Crear backup de un contrato
     */
    public function actionMigrationBackup($contratoId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $command = "php " . Yii::getAlias('@app/yii') . " cuota-migration/backup " . $contratoId;

            $output = [];
            $returnCode = 0;
            exec($command . " 2>&1", $output, $returnCode);

            return [
                'success' => $returnCode === 0,
                'output' => implode("\n", $output),
                'message' => $returnCode === 0 ? 'Backup creado exitosamente' : 'Error creando backup',
                'returnCode' => $returnCode
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => "Error: " . $e->getMessage(),
                'message' => 'Error ejecutando comando',
                'returnCode' => -1
            ];
        }
    }

    /**
     * Mostrar backup de un contrato
     */
    public function actionMigrationShowBackup($contratoId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $command = "php " . Yii::getAlias('@app/yii') . " cuota-migration/show-backup " . $contratoId;

            $output = [];
            $returnCode = 0;
            exec($command . " 2>&1", $output, $returnCode);

            return [
                'success' => $returnCode === 0,
                'output' => implode("\n", $output),
                'message' => $returnCode === 0 ? 'Backup mostrado exitosamente' : 'Error mostrando backup',
                'returnCode' => $returnCode
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => "Error: " . $e->getMessage(),
                'message' => 'Error ejecutando comando',
                'returnCode' => -1
            ];
        }
    }

    /**
     * Migrar un contrato específico (MODO NO INTERACTIVO)
     */
    public function actionMigrationMigrateContrato($contratoId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            // Primero preguntar al usuario con un modal (esto ya se hace en JS)
            // El comando usa --interactive=0 para responder "yes" automáticamente
            $command = "php " . Yii::getAlias('@app/yii') . " cuota-migration/migrate-contrato " . $contratoId . " --interactive=0";

            $output = [];
            $returnCode = 0;
            exec($command . " 2>&1", $output, $returnCode);

            return [
                'success' => $returnCode === 0,
                'output' => implode("\n", $output),
                'message' => $returnCode === 0 ? 'Contrato migrado exitosamente' : 'Error migrando contrato',
                'returnCode' => $returnCode
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => "Error: " . $e->getMessage(),
                'message' => 'Error ejecutando comando',
                'returnCode' => -1
            ];
        }
    }

    /**
     * Migrar todos los contratos (MODO NO INTERACTIVO)
     */
    public function actionMigrationMigrateAll()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            // El comando usa --interactive=0 para responder "yes" automáticamente
            $command = "php " . Yii::getAlias('@app/yii') . " cuota-migration/migrate-all --interactive=0";

            $output = [];
            $returnCode = 0;
            exec($command . " 2>&1", $output, $returnCode);

            return [
                'success' => $returnCode === 0,
                'output' => implode("\n", $output),
                'message' => $returnCode === 0 ? 'Migración masiva completada' : 'Error en migración masiva',
                'returnCode' => $returnCode
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => "Error: " . $e->getMessage(),
                'message' => 'Error ejecutando comando',
                'returnCode' => -1
            ];
        }
    }

    /**
     * Rollback de un contrato (MODO NO INTERACTIVO)
     */
    public function actionMigrationRollback($contratoId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            // El comando usa --interactive=0 para responder "yes" automáticamente
            $command = "php " . Yii::getAlias('@app/yii') . " cuota-migration/rollback " . $contratoId . " --interactive=0";

            $output = [];
            $returnCode = 0;
            exec($command . " 2>&1", $output, $returnCode);

            return [
                'success' => $returnCode === 0,
                'output' => implode("\n", $output),
                'message' => $returnCode === 0 ? 'Rollback completado' : 'Error en rollback',
                'returnCode' => $returnCode
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => "Error: " . $e->getMessage(),
                'message' => 'Error ejecutando comando',
                'returnCode' => -1
            ];
        }
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Método mejorado para ejecutar comandos de consola sin RBAC
     */
    private function runConsoleCommand($action)
    {
        // Construir el comando completo
        $command = "php " . Yii::getAlias('@app/yii') . " cuota/{$action}";

        // Ejecutar el comando en segundo plano y capturar output
        $output = [];
        $returnCode = 0;

        exec($command . " 2>&1", $output, $returnCode);

        return [
            'success' => $returnCode === 0,
            'output' => implode("\n", $output),
            'returnCode' => $returnCode
        ];
    }

    // ===============================================================
    // MÉTODOS ACTUALIZADOS USANDO EXEC
    // ===============================================================

    public function actionGenerar()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $result = $this->runConsoleCommand('generar');

            return [
                'success' => $result['success'],
                'output' => $result['output'],
                'message' => $result['success'] ? 'Cuotas generadas exitosamente' : 'Error al generar cuotas',
                'returnCode' => $result['returnCode']
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => $e->getMessage(),
                'message' => 'Error ejecutando el comando',
                'returnCode' => -1
            ];
        }
    }

    public function actionGenerarMensual()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $result = $this->runConsoleCommand('generar-mensual');

            return [
                'success' => $result['success'],
                'output' => $result['output'],
                'message' => $result['success'] ? 'Cuotas mensuales generadas exitosamente' : 'Error al generar cuotas mensuales',
                'returnCode' => $result['returnCode']
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => $e->getMessage(),
                'message' => 'Error ejecutando el comando',
                'returnCode' => -1
            ];
        }
    }

    public function actionGenerarAtrasadas()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $result = $this->runConsoleCommand('generar-atrasadas');

            return [
                'success' => $result['success'],
                'output' => $result['output'],
                'message' => $result['success'] ? 'Cuotas atrasadas generadas exitosamente' : 'Error al generar cuotas atrasadas',
                'returnCode' => $result['returnCode']
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => $e->getMessage(),
                'message' => 'Error ejecutando el comando',
                'returnCode' => -1
            ];
        }
    }

    public function actionVerificarVencidas()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $result = $this->runConsoleCommand('verificar-vencidas');

            return [
                'success' => $result['success'],
                'output' => $result['output'],
                'message' => $result['success'] ? 'Verificación de vencidas completada' : 'Error en verificación',
                'returnCode' => $result['returnCode']
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => $e->getMessage(),
                'message' => 'Error ejecutando el comando',
                'returnCode' => -1
            ];
        }
    }

    public function actionVerificarDiario()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $result = $this->runConsoleCommand('verificar-diario');

            return [
                'success' => $result['success'],
                'output' => $result['output'],
                'message' => $result['success'] ? 'Verificación diaria completada' : 'Error en verificación diaria',
                'returnCode' => $result['returnCode']
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => $e->getMessage(),
                'message' => 'Error ejecutando el comando',
                'returnCode' => -1
            ];
        }
    }

    public function actionResumenProximosVencer()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $result = $this->runConsoleCommand('resumen-proximos-vencer');

            return [
                'success' => $result['success'],
                'output' => $result['output'],
                'message' => $result['success'] ? 'Resumen generado exitosamente' : 'Error al generar resumen',
                'returnCode' => $result['returnCode']
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => $e->getMessage(),
                'message' => 'Error ejecutando el comando',
                'returnCode' => -1
            ];
        }
    }

    public function actionResumenAtrasadas()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $result = $this->runConsoleCommand('resumen-atrasadas');

            return [
                'success' => $result['success'],
                'output' => $result['output'],
                'message' => $result['success'] ? 'Resumen de atrasadas generado' : 'Error al generar resumen',
                'returnCode' => $result['returnCode']
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => $e->getMessage(),
                'message' => 'Error ejecutando el comando',
                'returnCode' => -1
            ];
        }
    }

    public function actionVerificarTodo()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $result = $this->runConsoleCommand('verificar-todo');

            return [
                'success' => $result['success'],
                'output' => $result['output'],
                'message' => $result['success'] ? 'Todas las verificaciones completadas' : 'Errores en verificaciones',
                'returnCode' => $result['returnCode']
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => $e->getMessage(),
                'message' => 'Error ejecutando el comando',
                'returnCode' => -1
            ];
        }
    }

    public function actionVerificarContratosVencidos()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $result = $this->runConsoleCommand('verificar-contratos-vencidos');

            return [
                'success' => $result['success'],
                'output' => $result['output'],
                'message' => $result['success'] ? 'Verificación de contratos vencidos completada' : 'Error en verificación',
                'returnCode' => $result['returnCode']
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => $e->getMessage(),
                'message' => 'Error ejecutando el comando',
                'returnCode' => -1
            ];
        }
    }

    /**
     * Verifica cuotas duplicadas desde la interfaz web.
     */
    public function actionVerificarDuplicados()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $result = $this->runConsoleCommand('verificar-duplicados');

            return [
                'success' => $result['success'],
                'output' => $result['output'],
                'message' => $result['success'] ? 'Verificación de duplicados completada' : 'Error en verificación',
                'returnCode' => $result['returnCode']
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => $e->getMessage(),
                'message' => 'Error ejecutando el comando',
                'returnCode' => -1
            ];
        }
    }

    /**
     * Elimina cuotas duplicadas desde la interfaz web.
     */
    public function actionEliminarDuplicados()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $result = $this->runConsoleCommand('eliminar-duplicados');

            return [
                'success' => $result['success'],
                'output' => $result['output'],
                'message' => $result['success'] ? 'Eliminación de duplicados completada' : 'Error en eliminación',
                'returnCode' => $result['returnCode']
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => $e->getMessage(),
                'message' => 'Error ejecutando el comando',
                'returnCode' => -1
            ];
        }
    }

    public function actionActualizarMontos()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $result = $this->runConsoleCommand('actualizar-montos');

            return [
                'success' => $result['success'],
                'output' => $result['output'],
                'message' => $result['success'] ? 'Montos actualizados exitosamente' : 'Error al actualizar montos',
                'returnCode' => $result['returnCode']
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => $e->getMessage(),
                'message' => 'Error ejecutando el comando',
                'returnCode' => -1
            ];
        }
    }

    /**
     * Repara la relación entre pagos y cuotas - Versión Web PURA
     * NO usa comandos de consola - todo se ejecuta directamente en web
     */
    public function actionRepararRelacionPagos()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $output = "Iniciando reparación CORREGIDA de relaciones pago-cuota (Web Directa)...\n\n";

            // Find payments that don't have linked cuotas
            $paymentsWithoutCuotas = Yii::$app->db->createCommand("
                SELECT p.id as pago_id, p.user_id, p.fecha_pago, p.monto_pagado, p.monto_usd,
                       ud.nombres, ud.apellidos
                FROM pagos p
                LEFT JOIN cuotas c ON p.id = c.id_pago
                LEFT JOIN user_datos ud ON p.user_id = ud.id
                WHERE c.id IS NULL
                AND p.estatus IN ('Conciliado', 'Por Conciliar')
                ORDER BY p.fecha_pago DESC
            ")->queryAll();

            if (empty($paymentsWithoutCuotas)) {
                $output .= "✅ Todos los pagos tienen cuotas vinculadas correctamente.\n";
                return [
                    'success' => true,
                    'output' => $output,
                    'message' => 'No se encontraron problemas',
                    'returnCode' => 0
                ];
            }

            $output .= "Se encontraron " . count($paymentsWithoutCuotas) . " pagos sin cuotas vinculadas.\n\n";
            $repairedCount = 0;

            foreach ($paymentsWithoutCuotas as $payment) {
                $userName = $payment['nombres'] . ' ' . $payment['apellidos'];
                $output .= "Procesando pago #{$payment['pago_id']} para usuario {$payment['user_id']} ({$userName})...\n";

                // CORRECTED: Use monto_pagado (the actual payment amount in USD)
                $paymentAmount = $payment['monto_pagado'] ?? 0;
                $montoUsd = $payment['monto_usd'] ?? 'NULL';
                $output .= "  💰 Monto del pago - monto_pagado: {$paymentAmount} USD (monto_usd: {$montoUsd})\n";

                // Find pending cuotas for this user around payment date
                $cuotas = Cuotas::find()
                    ->joinWith(['contrato'])
                    ->where(['contratos.user_id' => $payment['user_id']])
                    ->andWhere(['cuotas.estatus' => 'pendiente'])
                    ->andWhere(['<=', 'cuotas.fecha_vencimiento', $payment['fecha_pago']])
                    ->orderBy(['cuotas.fecha_vencimiento' => SORT_ASC])
                    ->all();

                if (!empty($cuotas)) {
                    $output .= "  Encontradas " . count($cuotas) . " cuotas pendientes\n";

                    $totalCuotas = 0;
                    $cuotasToUpdate = [];

                    foreach ($cuotas as $cuota) {
                        // CORRECTED: Use monto field (the actual cuota amount in USD)
                        $monto = $cuota->monto ?? 0;
                        $montoUsdCuota = $cuota->monto_usd ?? 'NULL';
                        $output .= "    📊 Cuota #{$cuota->id}: monto = {$monto} USD (monto_usd: {$montoUsdCuota})\n";

                        if ($monto > 0) {
                            // Normalize amounts to 2 decimal places
                            $normalizedCuota = round($monto, 2);
                            $normalizedPayment = round($paymentAmount, 2);

                            // Check if this cuota matches the payment
                            if (abs($normalizedCuota - $normalizedPayment) <= 0.01) {
                                $totalCuotas += $monto;
                                $cuotasToUpdate[] = $cuota->id;
                                $output .= "    ✅ COINCIDE EXACTA: Cuota #{$cuota->id} = {$monto} USD\n";
                            } else {
                                $output .= "    ❌ NO COINCIDE: Cuota #{$cuota->id} = {$monto} USD vs Pago = {$paymentAmount} USD\n";
                            }
                        } else {
                            $output .= "    ⚠️ Cuota #{$cuota->id}: MONTO VACÍO o CERO\n";
                        }
                    }

                    if (!empty($cuotasToUpdate)) {
                        // Update the cuotas
                        $updated = Cuotas::updateAll(
                            [
                                'id_pago' => $payment['pago_id'],
                                'estatus' => 'pagada',
                                'fecha_pago' => $payment['fecha_pago']
                            ],
                            ['id' => $cuotasToUpdate]
                        );

                        if ($updated > 0) {
                            $repairedCount += $updated;
                            $output .= "  ✅ Vinculadas {$updated} cuotas al pago #{$payment['pago_id']}\n";

                            // Reactivate contract
                            $this->reactivateContractIfNeeded($payment['user_id'], $output);
                        }
                    } else {
                        $output .= "  ⚠️  No se encontraron cuotas que coincidan con el monto del pago\n";
                    }
                } else {
                    $output .= "  ℹ️  No hay cuotas pendientes para este usuario\n";
                }
                $output .= "\n";
            }

            $output .= "✅ Proceso completado. Se repararon {$repairedCount} relaciones pago-cuota.\n";

            return [
                'success' => true,
                'output' => $output,
                'message' => "Reparación CORREGIDA completada - {$repairedCount} relaciones reparadas",
                'returnCode' => 0
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => "❌ Error: " . $e->getMessage(),
                'message' => 'Error ejecutando la reparación corregida',
                'returnCode' => -1
            ];
        }
    }

    private function reactivateContractIfNeeded($userId, &$output)
    {
        try {
            $contrato = Contratos::find()
                ->where(['user_id' => $userId])
                ->andWhere(['estatus' => 'Suspendido'])  // Uppercase S
                ->one();

            if ($contrato) {
                // Check if there are any pending cuotas
                $pendingCuotas = Cuotas::find()
                    ->where(['contrato_id' => $contrato->id])
                    ->andWhere(['estatus' => 'pendiente'])
                    ->andWhere(['<', 'fecha_vencimiento', date('Y-m-d')])
                    ->count();

                if ($pendingCuotas == 0) {
                    $contrato->estatus = 'Activo';  // Uppercase A
                    if ($contrato->save()) {
                        $output .= "  🔄 Contrato #{$contrato->id} reactivado automáticamente\n";

                        // Also update user solvent status
                        $user = UserDatos::findOne($userId);
                        if ($user) {
                            $user->estatus_solvente = 'Si';
                            $user->save(false);
                            $output .= "  👤 Usuario marcado como solvente\n";
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $output .= "  ❌ Error reactivando contrato: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Elimina cuotas incorrectas específicas (IDs 149, 150, 147)
     */
    public function actionEliminarIncorrectas()  // ← SHORTER NAME
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            // Delete the specific cuotas that were incorrectly generated
            $cuotasToDelete = [149, 150, 147];
            $deletedCount = 0;
            $details = [];

            foreach ($cuotasToDelete as $cuotaId) {
                $cuota = Cuotas::findOne($cuotaId);
                if ($cuota) {
                    $contratoId = $cuota->contrato_id;
                    if ($cuota->delete()) {
                        $deletedCount++;
                        $details[] = "✅ Cuota #{$cuotaId} (Contrato #{$contratoId}) eliminada";
                        Yii::info("Deleted incorrect cuota #{$cuotaId} for contract #{$contratoId}", 'cuotas');
                    } else {
                        $details[] = "❌ Error eliminando cuota #{$cuotaId}";
                    }
                } else {
                    $details[] = "⚠️ Cuota #{$cuotaId} no encontrada";
                }
            }

            $output = implode("\n", $details);

            return [
                'success' => true,
                'output' => "Resultado: {$deletedCount}/3 cuotas eliminadas\n\n" . $output,
                'message' => 'Proceso de eliminación completado',
                'returnCode' => 0
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => "Error: " . $e->getMessage(),
                'message' => 'Error eliminando cuotas incorrectas',
                'returnCode' => -1
            ];
        }
    }

    /**
     * Genera cuotas faltantes para pagos existentes - VERSIÓN CORREGIDA
     */
    public function actionGenerarCuotasFaltantes()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $output = "🔍 Buscando pagos sin cuotas vinculadas...\n\n";

            // CONSULTA CORREGIDA - Usar la misma lógica que el diagnóstico
            $paymentsWithoutCuotas = Yii::$app->db->createCommand("
                SELECT p.id as pago_id, p.user_id, p.fecha_pago, p.monto_pagado, 
                    ud.nombres, ud.apellidos,
                    (SELECT id FROM contratos WHERE user_id = p.user_id ORDER BY id DESC LIMIT 1) as contrato_id,
                    (SELECT monto FROM contratos WHERE user_id = p.user_id ORDER BY id DESC LIMIT 1) as monto_contrato
                FROM pagos p
                INNER JOIN user_datos ud ON p.user_id = ud.id
                WHERE p.estatus IN ('Conciliado', 'Por Conciliar')
                AND NOT EXISTS (
                    SELECT 1 FROM cuotas c WHERE c.id_pago = p.id
                )
                ORDER BY p.fecha_pago ASC
            ")->queryAll();

            if (empty($paymentsWithoutCuotas)) {
                $output .= "✅ Todos los pagos tienen cuotas vinculadas.\n";
                return [
                    'success' => true,
                    'output' => $output,
                    'message' => 'No se encontraron pagos sin cuotas',
                    'returnCode' => 0
                ];
            }

            $output .= "📊 Se encontraron " . count($paymentsWithoutCuotas) . " pagos sin cuotas vinculadas.\n\n";
            $generatedCount = 0;
            $errorsCount = 0;

            foreach ($paymentsWithoutCuotas as $payment) {
                $userName = $payment['nombres'] . ' ' . $payment['apellidos'];
                $output .= "💳 Procesando pago #{$payment['pago_id']} - {$userName}\n";
                $output .= "   📅 Fecha pago: {$payment['fecha_pago']}\n";
                $output .= "   💰 Monto: {$payment['monto_pagado']} USD\n";
                $output .= "   👤 Contrato ID: " . ($payment['contrato_id'] ?? 'No encontrado') . "\n";

                if (empty($payment['contrato_id'])) {
                    $output .= "   ❌ ERROR: No se pudo encontrar contrato para este usuario\n";
                    $errorsCount++;
                    continue;
                }

                // Calculate due date (7th of the payment month)
                $paymentDate = new \DateTime($payment['fecha_pago']);
                $dueDate = $paymentDate->format('Y-m-07');

                // Use contract amount or payment amount
                $monto = $payment['monto_contrato'] ?: $payment['monto_pagado'];

                // Check if cuota already exists for this period (without payment link)
                $existingCuota = Cuotas::find()
                    ->where([
                        'contrato_id' => $payment['contrato_id'],
                        'fecha_vencimiento' => $dueDate,
                        'id_pago' => null // Not already linked
                    ])
                    ->one();

                if ($existingCuota) {
                    // Link existing cuota to payment
                    $existingCuota->id_pago = $payment['pago_id'];
                    $existingCuota->estatus = 'pagada';
                    $existingCuota->fecha_pago = $payment['fecha_pago'];
                    if ($existingCuota->save()) {
                        $output .= "   ✅ Cuota existente #{$existingCuota->id} vinculada al pago\n";
                        $generatedCount++;
                    } else {
                        $output .= "   ❌ Error vinculando cuota: " . print_r($existingCuota->errors, true) . "\n";
                        $errorsCount++;
                    }
                } else {
                    // Create new historical cuota
                    $nuevaCuota = new Cuotas([
                        'contrato_id' => $payment['contrato_id'],
                        'fecha_vencimiento' => $dueDate,
                        'monto' => round($monto, 2),
                        'monto_usd' => round($monto, 2),
                        'estatus' => 'pagada',
                        'fecha_pago' => $payment['fecha_pago'],
                        'id_pago' => $payment['pago_id'],
                        'rate_usd_bs' => 1.0,
                    ]);

                    if ($nuevaCuota->save()) {
                        $output .= "   ✅ Nueva cuota #{$nuevaCuota->id} creada y vinculada\n";
                        $generatedCount++;
                    } else {
                        $output .= "   ❌ Error creando cuota: " . print_r($nuevaCuota->errors, true) . "\n";
                        $errorsCount++;
                    }
                }
                $output .= "\n";
            }

            $output .= "🎯 RESUMEN FINAL:\n";
            $output .= "================\n";
            $output .= "• Pagos procesados: " . count($paymentsWithoutCuotas) . "\n";
            $output .= "• Cuotas creadas/vinculadas: {$generatedCount}\n";
            $output .= "• Errores: {$errorsCount}\n";

            if ($errorsCount > 0) {
                $output .= "⚠️  Algunos pagos no pudieron ser procesados. Revise los logs.\n";
            }

            return [
                'success' => true,
                'output' => $output,
                'message' => "Proceso completado - {$generatedCount} cuotas creadas/vinculadas de " . count($paymentsWithoutCuotas) . " pagos",
                'returnCode' => 0
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => "❌ Error: " . $e->getMessage(),
                'message' => 'Error generando cuotas faltantes',
                'returnCode' => -1
            ];
        }
    }

    /**
     * Diagnóstico completo de inconsistencias pago-cuota - VERSIÓN CORREGIDA
     */
    public function actionDiagnosticoInconsistencias()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $output = "=== DIAGNÓSTICO DE INCONSISTENCIAS PAGO-CUOTA ===\n\n";

            // 1. Pagos sin cuotas - CORREGIDO
            $pagosSinCuotas = Yii::$app->db->createCommand("
                SELECT COUNT(*) as total, 
                    SUM(CASE WHEN estatus = 'Conciliado' THEN 1 ELSE 0 END) as conciliados,
                    SUM(CASE WHEN estatus = 'Por Conciliar' THEN 1 ELSE 0 END) as por_conciliar
                FROM pagos p
                WHERE NOT EXISTS (SELECT 1 FROM cuotas c WHERE c.id_pago = p.id)
                AND p.estatus IN ('Conciliado', 'Por Conciliar')
            ")->queryOne();

            $output .= "1. PAGOS SIN CUOTAS VINCULADAS:\n";
            $output .= "   - Total: {$pagosSinCuotas['total']}\n";
            $output .= "   - Conciliados: {$pagosSinCuotas['conciliados']}\n";
            $output .= "   - Por conciliar: {$pagosSinCuotas['por_conciliar']}\n\n";

            // 2. Cuotas con montos inválidos - CORREGIDO (eliminada comparación con cadena vacía)
            $cuotasVacias = Yii::$app->db->createCommand("
                SELECT COUNT(*) as total
                FROM cuotas c
                WHERE c.monto IS NULL OR c.monto = 0 OR c.monto::text = ''
            ")->queryScalar();

            $output .= "2. CUOTAS CON MONTOS INVÁLIDOS: {$cuotasVacias}\n\n";

            // 3. Contratos suspendidos con pagos existentes
            $contratosSuspendidosConPagos = Yii::$app->db->createCommand("
                SELECT COUNT(DISTINCT c.id) as total
                FROM contratos c
                INNER JOIN pagos p ON c.user_id = p.user_id
                WHERE c.estatus = 'suspendido'
                AND p.estatus IN ('Conciliado', 'Por Conciliar')
                AND NOT EXISTS (
                    SELECT 1 FROM cuotas cu 
                    WHERE cu.contrato_id = c.id 
                    AND cu.id_pago = p.id
                )
            ")->queryScalar();

            $output .= "3. CONTRATOS SUSPENDIDOS CON PAGOS SIN VINCULAR: {$contratosSuspendidosConPagos}\n\n";

            // 4. Cuotas pagadas sin relación con pago
            $cuotasPagadasSinPago = Yii::$app->db->createCommand("
                SELECT COUNT(*) as total
                FROM cuotas c
                WHERE c.estatus = 'pagada' 
                AND c.id_pago IS NULL
            ")->queryScalar();

            $output .= "4. CUOTAS PAGADAS SIN PAGO ASOCIADO: {$cuotasPagadasSinPago}\n\n";

            // 5. Cuotas huérfanas (sin contrato)
            $cuotasHuerfanas = Yii::$app->db->createCommand("
                SELECT COUNT(*) as total
                FROM cuotas c
                LEFT JOIN contratos ct ON c.contrato_id = ct.id
                WHERE ct.id IS NULL
            ")->queryScalar();

            $output .= "5. CUOTAS HUÉRFANAS (SIN CONTRATO): {$cuotasHuerfanas}\n\n";

            // 6. Contratos activos sin cuotas
            $contratosSinCuotas = Yii::$app->db->createCommand("
                SELECT COUNT(DISTINCT ct.id) as total
                FROM contratos ct
                LEFT JOIN cuotas c ON ct.id = c.contrato_id
                WHERE ct.estatus = 'Activo' 
                AND c.id IS NULL
            ")->queryScalar();

            $output .= "6. CONTRATOS ACTIVOS SIN CUOTAS: {$contratosSinCuotas}\n\n";

            // 7. Detalle de pagos problemáticos
            $detallePagos = Yii::$app->db->createCommand("
                SELECT p.id, p.user_id, ud.nombres, ud.apellidos, 
                    p.fecha_pago, p.monto_pagado, p.estatus,
                    c.id as contrato_id, c.estatus as estatus_contrato
                FROM pagos p
                INNER JOIN user_datos ud ON p.user_id = ud.id
                INNER JOIN contratos c ON p.user_id = c.user_id
                WHERE NOT EXISTS (SELECT 1 FROM cuotas cu WHERE cu.id_pago = p.id)
                AND p.estatus IN ('Conciliado', 'Por Conciliar')
                ORDER BY p.fecha_pago DESC
                LIMIT 20
            ")->queryAll();

            $output .= "7. ÚLTIMOS 20 PAGOS PROBLEMÁTICOS:\n";
            if (!empty($detallePagos)) {
                foreach ($detallePagos as $pago) {
                    $output .= "   - Pago #{$pago['id']}: {$pago['nombres']} {$pago['apellidos']} ";
                    $output .= "({$pago['fecha_pago']}) - {$pago['monto_pagado']} USD ";
                    $output .= "- Contrato #{$pago['contrato_id']} ({$pago['estatus_contrato']})\n";
                }
            } else {
                $output .= "   ✅ No hay pagos problemáticos\n";
            }

            // Resumen final
            $totalProblemas = $pagosSinCuotas['total'] + $cuotasVacias + $contratosSuspendidosConPagos +
                $cuotasPagadasSinPago + $cuotasHuerfanas + $contratosSinCuotas;

            $output .= "\n📊 RESUMEN FINAL:\n";
            $output .= "================\n";
            $output .= "Total de inconsistencias encontradas: {$totalProblemas}\n\n";

            if ($totalProblemas == 0) {
                $output .= "🎉 ¡Excelente! El sistema está en buen estado.\n";
            } else {
                $output .= "⚠️  Se recomienda usar las herramientas de mantenimiento para resolver:\n";
                if ($pagosSinCuotas['total'] > 0) {
                    $output .= "   - Use 'Generar Cuotas Faltantes' para los pagos sin cuotas\n";
                }
                if ($cuotasVacias > 0) {
                    $output .= "   - Use 'Actualizar Montos de Cuotas' para cuotas con montos inválidos\n";
                }
                if ($contratosSuspendidosConPagos > 0) {
                    $output .= "   - Use 'Reparar Relación Pagos-Cuotas' para contratos suspendidos\n";
                }
            }

            return [
                'success' => true,
                'output' => $output,
                'message' => 'Diagnóstico completado - ' . $totalProblemas . ' inconsistencias encontradas',
                'returnCode' => 0
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => "❌ Error en diagnóstico: " . $e->getMessage(),
                'message' => 'Error ejecutando el diagnóstico',
                'returnCode' => -1
            ];
        }
    }

    public function actionVerificarEspera()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $output = "Iniciando verificación de contratos en espera...\n\n";

            // Find contracts with "espera" status
            $contratosEnEspera = Contratos::find()
                ->where(['estatus' => 'espera'])
                ->all();

            $output .= "📊 Se encontraron " . count($contratosEnEspera) . " contratos en espera.\n\n";

            if (empty($contratosEnEspera)) {
                $output .= "✅ No hay contratos en estado de espera.\n";
            } else {
                foreach ($contratosEnEspera as $contrato) {
                    $output .= "📋 Contrato #{$contrato->id}\n";
                    $output .= "   👤 User ID: {$contrato->user_id}\n";

                    // Get user info if available
                    $user = UserDatos::findOne($contrato->user_id);
                    if ($user) {
                        $output .= "   👥 Usuario: {$user->nombres} {$user->apellidos}\n";
                    }

                    $output .= "   📅 Fecha inicio: {$contrato->fecha_ini}\n";
                    $output .= "   💰 Monto: {$contrato->monto}\n";
                    $output .= "   🏷️  Servicio: {$contrato->servicio}\n\n";
                }
            }

            $output .= "✅ Verificación completada.\n";

            return [
                'success' => true,
                'output' => $output,
                'message' => 'Verificación de contratos en espera completada',
                'returnCode' => 0
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => "❌ Error: " . $e->getMessage(),
                'message' => 'Error en verificación de contratos en espera',
                'returnCode' => -1
            ];
        }
    }

    /**
     * Fallback method when console command doesn't exist
     */
    private function verificarEsperaWeb()
    {
        try {
            $output = "Iniciando verificación de contratos en espera (Web Fallback)...\n\n";

            $contratosEnEspera = Contratos::find()
                ->where(['estatus' => 'espera'])
                ->all();

            $output .= "Se encontraron " . count($contratosEnEspera) . " contratos en espera.\n\n";

            foreach ($contratosEnEspera as $contrato) {
                $output .= "📋 Contrato #{$contrato->id}\n";
                $output .= "   👤 User ID: {$contrato->user_id}\n";
                $output .= "   📅 Fecha inicio: {$contrato->fecha_ini}\n";
                $output .= "   💰 Monto: {$contrato->monto}\n\n";
            }

            $output .= "✅ Verificación completada.\n";

            return [
                'success' => true,
                'output' => $output,
                'message' => 'Verificación de contratos en espera completada',
                'returnCode' => 0
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => "❌ Error: " . $e->getMessage(),
                'message' => 'Error en verificación de contratos en espera',
                'returnCode' => -1
            ];
        }
    }

    // ===============================================================
    // MÉTODOS PARA CUOTAS ADELANTADAS
    // ===============================================================

    /**
     * Preview advance cuotas before generation with 1-year limit
     */
    public function actionPreviewAdelantadas()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $contrato_id = Yii::$app->request->post('contrato_id');
            $num_cuotas = Yii::$app->request->post('num_cuotas');
            $fecha_inicio = Yii::$app->request->post('fecha_inicio');
            $meses = Yii::$app->request->post('meses', '');

            \Yii::info("Preview parameters received: contrato_id={$contrato_id}, num_cuotas={$num_cuotas}, fecha_inicio={$fecha_inicio}, meses={$meses}", 'cuotas');

            if (empty($contrato_id) || empty($num_cuotas)) {
                throw new \Exception("Parámetros incompletos. contrato_id: {$contrato_id}, num_cuotas: {$num_cuotas}");
            }

            // Obtener información del contrato para validar límite de 1 año
            $contrato = Contratos::findOne($contrato_id);
            if (!$contrato) {
                throw new \Exception("Contrato no encontrado");
            }

            // Validar límite de 1 año
            $fechaInicioContrato = new \DateTime($contrato->fecha_ini);
            $fechaLimite = clone $fechaInicioContrato;
            $fechaLimite->modify('+1 year'); // Límite: 1 año desde fecha inicio del contrato

            \Yii::info("Límite de cuotas: Hasta " . $fechaLimite->format('Y-m-d'), 'cuotas');

            // Si se proporcionan meses, es modo "por meses específicos"
            $modo = !empty($meses) ? 'meses' : 'cantidad';

            $result = Cuotas::previewCuotasAdelantadas(
                $contrato_id,
                $num_cuotas,
                $fecha_inicio,
                $meses,
                $modo,
                $fechaLimite->format('Y-m-d') // Pasar fecha límite
            );

            return $result;
        } catch (\Exception $e) {
            \Yii::error("Error in previewAdelantadas: " . $e->getMessage(), 'cuotas');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate advance cuotas for a specific affiliate with 1-year limit
     */
    public function actionGenerarAdelantadas()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $user_id = Yii::$app->request->post('user_id');
            $contrato_id = Yii::$app->request->post('contrato_id');
            $num_cuotas = Yii::$app->request->post('num_cuotas');
            $modo = Yii::$app->request->post('modo', 'cantidad');
            $meses = Yii::$app->request->post('meses', '');
            $fecha_inicio = Yii::$app->request->post('fecha_inicio');

            // Validate inputs
            if (empty($contrato_id)) {
                throw new \Exception("Debe seleccionar un contrato");
            }

            // Obtener contrato para validar límite
            $contrato = Contratos::findOne($contrato_id);
            if (!$contrato) {
                throw new \Exception("Contrato no encontrado");
            }

            // Calcular fecha límite (1 año desde inicio del contrato)
            $fechaInicioContrato = new \DateTime($contrato->fecha_ini);
            $fechaLimite = clone $fechaInicioContrato;
            $fechaLimite->modify('+1 year');

            \Yii::info("Generación adelantada - Fecha límite: " . $fechaLimite->format('Y-m-d'), 'cuotas');

            if (empty($num_cuotas) && $modo === 'cantidad') {
                throw new \Exception("Debe especificar el número de cuotas");
            }

            if (empty($meses) && $modo === 'meses') {
                throw new \Exception("Debe especificar los meses");
            }

            // If mode is 'meses', calculate number of cuotas and validate dates
            if ($modo === 'meses') {
                $mesesArray = explode(',', $meses);
                $num_cuotas = count($mesesArray);

                // Validar que ningún mes exceda el límite de 1 año
                foreach ($mesesArray as $mes) {
                    $fechaMes = new \DateTime($mes . '-01'); // Primer día del mes
                    if ($fechaMes > $fechaLimite) {
                        throw new \Exception("No se pueden generar cuotas después de " . $fechaLimite->format('m/Y') .
                            " (límite de 1 año desde inicio del contrato)");
                    }
                }
            } else {
                // Modo cantidad: validar que no exceda 12 cuotas máximo
                if ($num_cuotas > 12) {
                    throw new \Exception("Máximo 12 cuotas (1 año) desde inicio del contrato");
                }
            }

            // Generate cuotas con fecha límite
            $result = Cuotas::generarCuotasAdelantadas(
                $contrato_id,
                $num_cuotas,
                $fecha_inicio,
                $meses,
                $fechaLimite->format('Y-m-d')
            );

            if ($result['success']) {
                // Get user info for response
                $contrato = Contratos::findOne($contrato_id);
                $user = $contrato ? $contrato->user : null;

                return [
                    'success' => true,
                    'output' => "✅ Se generaron {$result['generated']} cuotas adelantadas exitosamente.\n" .
                        "👤 Afiliado: " . ($user ? $user->nombres . ' ' . $user->apellidos : 'N/A') . "\n" .
                        "📋 Contrato: {$contrato->nrocontrato}\n" .
                        "📅 Fecha inicio contrato: {$contrato->fecha_ini}\n" .
                        "⏰ Límite: 1 año desde inicio (" . $fechaLimite->format('Y-m-d') . ")\n" .
                        "💰 Cuotas generadas: {$result['generated']} de {$num_cuotas} solicitadas",
                    'message' => "Cuotas adelantadas generadas exitosamente",
                    'returnCode' => 0,
                    'generated' => $result['generated']
                ];
            } else {
                throw new \Exception($result['error']);
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'output' => "❌ Error: " . $e->getMessage(),
                'message' => 'Error generando cuotas adelantadas',
                'returnCode' => -1
            ];
        }
    }

    /**
     * Get contracts for a user (AJAX endpoint)
     */
    public function actionGetUserContracts()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $user_id = Yii::$app->request->post('user_id');

        if (empty($user_id)) {
            return ['success' => false, 'error' => 'User ID requerido'];
        }

        $contratos = Contratos::find()
            ->where(['user_id' => $user_id])
            ->andWhere(['!=', 'estatus', 'suspendido']) // Exclude suspended contracts
            ->all();

        $data = [];
        foreach ($contratos as $contrato) {
            // Get last cuota for this contract
            $lastCuota = Cuotas::getLastCuotaForContract($contrato->id);

            $data[] = [
                'id' => $contrato->id,
                'nrocontrato' => $contrato->nrocontrato,
                'estatus' => $contrato->estatus,
                'monto' => $contrato->monto,
                'fecha_ini' => $contrato->fecha_ini, // AÑADIR ESTO
                'last_cuota' => $lastCuota ? [
                    'fecha' => $lastCuota->fecha_vencimiento,
                    'estatus' => $lastCuota->estatus
                ] : null
            ];
        }

        return [
            'success' => true,
            'contratos' => $data
        ];
    }

    public function actionSearchUser()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $search = trim(Yii::$app->request->post('search'));
        \Yii::info("Search term received: " . $search, 'cuotas');

        if (empty($search)) {
            return ['success' => false, 'error' => 'Término de búsqueda requerido'];
        }

        try {
            // Build search conditions for PostgreSQL
            $conditions = ['or'];

            // Check if numeric for ID/cedula (exact match)
            if (is_numeric($search)) {
                $conditions[] = ['id' => (int)$search];
                $conditions[] = ['cedula' => (int)$search];
            }

            // For text searches in PostgreSQL
            // PostgreSQL is case-sensitive by default, so we use ILIKE for case-insensitive
            $searchLike = '%' . $search . '%';

            // Option 1: Use ILIKE on individual fields (case-insensitive LIKE in PostgreSQL)
            $conditions[] = ['ilike', 'nombres', $searchLike];
            $conditions[] = ['ilike', 'apellidos', $searchLike];

            // Add email search too
            $conditions[] = ['ilike', 'email', $searchLike];

            // Create a query builder instance for complex conditions
            $query = UserDatos::find();

            // Add the simple conditions
            $query->where($conditions);

            // Add concatenated name search using addParams() and SQL expression
            // PostgreSQL uses || for string concatenation
            $concatCondition = "LOWER(nombres || ' ' || apellidos) LIKE LOWER(:searchTerm) OR 
                            LOWER(apellidos || ' ' || nombres) LIKE LOWER(:searchTerm)";

            // Add this as an OR condition using andWhere with OR operator
            $query->orWhere($concatCondition, [':searchTerm' => '%' . $search . '%']);

            \Yii::info("Search query: " . $query->createCommand()->rawSql, 'cuotas');

            $users = $query->limit(10)->all();

            \Yii::info("Users found: " . count($users), 'cuotas');

            $data = [];
            foreach ($users as $user) {
                \Yii::info("User found - ID: {$user->id}, Name: {$user->nombres} {$user->apellidos}, Cedula: {$user->cedula}", 'cuotas');

                // Get active contracts count
                $activeContracts = Contratos::find()
                    ->where(['user_id' => $user->id])
                    ->andWhere(['!=', 'estatus', 'suspendido'])
                    ->count();

                $data[] = [
                    'id' => $user->id,
                    'nombres' => $user->nombres,
                    'apellidos' => $user->apellidos,
                    'cedula' => $user->cedula,
                    'email' => $user->email,
                    'active_contracts' => $activeContracts
                ];
            }

            return [
                'success' => true,
                'users' => $data,
                'debug' => [
                    'search_term' => $search,
                    'is_numeric' => is_numeric($search),
                    'users_count' => count($users),
                    'database' => 'PostgreSQL'
                ]
            ];
        } catch (\Exception $e) {
            \Yii::error("Error in searchUser: " . $e->getMessage() . "\n" . $e->getTraceAsString(), 'cuotas');
            return [
                'success' => false,
                'error' => 'Error en búsqueda: ' . $e->getMessage(),
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    }

    /**
     * Execute daily check (grace period, suspensions, reactivations) - DIRECT DB IMPLEMENTATION
     * This bypasses the need for exec() function or console application
     */
    public function actionDailyCheck()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $output = [];
            $output[] = "═══════════════════════════════════════════════════════════";
            $output[] = "     VERIFICACIÓN DIARIA DE CUOTAS Y CONTRATOS";
            $output[] = "═══════════════════════════════════════════════════════════";
            $output[] = "";
            $output[] = "📅 Fecha de ejecución: " . date('Y-m-d H:i:s');
            $output[] = "";

            $today = date('Y-m-d');
            $gracePeriodDays = 7;
            $gracePeriodEnd = date('Y-m-d', strtotime('-' . $gracePeriodDays . ' days'));

            $output[] = "📊 Configuración:";
            $output[] = "   • Período de gracia: {$gracePeriodDays} días";
            $output[] = "   • Fecha límite para gracia: {$gracePeriodEnd}";
            $output[] = "";

            // ============================================================
            // PART 1: UPDATE CUOTA STATUSES
            // ============================================================
            $output[] = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";
            $output[] = "🔄 ACTUALIZANDO ESTADOS DE CUOTAS";
            $output[] = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";

            // 1. Move from PENDIENTE to EN_GRACIAS when past due date (within grace period)
            $toGrace = Yii::$app->db->createCommand("
            UPDATE cuotas 
            SET estatus = 'en_gracias'
            WHERE estatus = 'pendiente' 
            AND fecha_vencimiento < :today
            AND fecha_vencimiento >= :gracePeriodEnd
        ", [':today' => $today, ':gracePeriodEnd' => $gracePeriodEnd])->execute();

            $output[] = "✅ {$toGrace} cuotas en período de gracia (pendiente → en_gracias)";

            // 2. Move from EN_GRACIAS to VENCIDA after grace period ends
            $toVencidaFromGrace = Yii::$app->db->createCommand("
            UPDATE cuotas 
            SET estatus = 'vencida'
            WHERE estatus = 'en_gracias' 
            AND fecha_vencimiento < :gracePeriodEnd
        ", [':gracePeriodEnd' => $gracePeriodEnd])->execute();

            $output[] = "✅ {$toVencidaFromGrace} cuotas marcadas como VENCIDAS (en_gracias → vencida)";

            // 3. Directly from PENDIENTE to VENCIDA (if we missed grace period check)
            $toVencidaDirect = Yii::$app->db->createCommand("
            UPDATE cuotas 
            SET estatus = 'vencida'
            WHERE estatus = 'pendiente' 
            AND fecha_vencimiento < :gracePeriodEnd
        ", [':gracePeriodEnd' => $gracePeriodEnd])->execute();

            $output[] = "✅ {$toVencidaDirect} cuotas marcadas directamente como VENCIDAS";

            // ============================================================
            // PART 2: SUSPEND CONTRACTS WITH OVERDUE CUOTAS
            // ============================================================
            $output[] = "";
            $output[] = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";
            $output[] = "⏸️  SUSPENDIENDO CONTRATOS CON CUOTAS VENCIDAS";
            $output[] = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";

            // Get contracts with overdue cuotas (vencida status) - using uppercase 'Suspendido' for check
            $contractsToSuspend = Yii::$app->db->createCommand("
            SELECT DISTINCT c.id, c.user_id, c.nrocontrato, c.estatus
            FROM contratos c
            INNER JOIN cuotas cu ON c.id = cu.contrato_id
            WHERE cu.estatus = 'vencida'
            AND c.estatus != 'Suspendido'
            AND c.estatus != 'Anulado'
            AND c.estatus != 'anulado'
        ")->queryAll();

            $output[] = "🔍 Contratos con cuotas vencidas encontrados: " . count($contractsToSuspend);

            $suspendedCount = 0;
            foreach ($contractsToSuspend as $contract) {
                // Update to 'Suspendido' with uppercase S
                Yii::$app->db->createCommand("
                UPDATE contratos 
                SET estatus = 'Suspendido' 
                WHERE id = :id AND estatus != 'Suspendido'
            ", [':id' => $contract['id']])->execute();

                $suspendedCount++;
                $output[] = "   • Contrato #{$contract['id']} ({$contract['nrocontrato']}) → SUSPENDIDO";

                // Update user solvent status (using 'No' for PostgreSQL case sensitivity)
                Yii::$app->db->createCommand("
                UPDATE user_datos 
                SET estatus_solvente = 'No' 
                WHERE id = :user_id
            ", [':user_id' => $contract['user_id']])->execute();
            }

            $output[] = "";
            $output[] = "✅ {$suspendedCount} contratos suspendidos por cuotas vencidas";

            // ============================================================
            // PART 3: REACTIVATE CONTRACTS WITH NO OVERDUE CUOTAS
            // ============================================================
            $output[] = "";
            $output[] = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";
            $output[] = "🔄 REACTIVANDO CONTRATOS SIN CUOTAS VENCIDAS";
            $output[] = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";

            // Get suspended contracts with no vencida cuotas - checking for 'Suspendido' with uppercase S
            $contractsToReactivate = Yii::$app->db->createCommand("
            SELECT DISTINCT c.id, c.user_id, c.nrocontrato
            FROM contratos c
            WHERE c.estatus = 'Suspendido'
            AND NOT EXISTS (
                SELECT 1 FROM cuotas cu 
                WHERE cu.contrato_id = c.id 
                AND cu.estatus = 'vencida'
            )
            AND EXISTS (
                SELECT 1 FROM cuotas cu 
                WHERE cu.contrato_id = c.id 
                AND cu.estatus IN ('pendiente', 'en_gracias')
            )
        ")->queryAll();

            $output[] = "🔍 Contratos suspendidos sin cuotas vencidas: " . count($contractsToReactivate);

            $reactivatedCount = 0;
            foreach ($contractsToReactivate as $contract) {
                // Update to 'Activo' with uppercase A
                Yii::$app->db->createCommand("
                UPDATE contratos 
                SET estatus = 'Activo' 
                WHERE id = :id
            ", [':id' => $contract['id']])->execute();

                $reactivatedCount++;
                $output[] = "   • Contrato #{$contract['id']} ({$contract['nrocontrato']}) → ACTIVO";

                // Update user solvent status to 'Si' if no other contracts have vencida cuotas
                Yii::$app->db->createCommand("
                UPDATE user_datos 
                SET estatus_solvente = 'Si' 
                WHERE id = :user_id
                AND NOT EXISTS (
                    SELECT 1 FROM contratos c2 
                    INNER JOIN cuotas cu2 ON c2.id = cu2.contrato_id
                    WHERE c2.user_id = user_datos.id 
                    AND cu2.estatus = 'vencida'
                    AND c2.estatus != 'Anulado'
                    AND c2.estatus != 'anulado'
                )
            ", [':user_id' => $contract['user_id']])->execute();
            }

            $output[] = "";
            $output[] = "✅ {$reactivatedCount} contratos reactivados";

            // ============================================================
            // PART 4: SUMMARY
            // ============================================================
            $output[] = "";
            $output[] = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";
            $output[] = "📊 RESUMEN DE LA VERIFICACIÓN DIARIA";
            $output[] = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";

            // Get current statistics
            $stats = Yii::$app->db->createCommand("
            SELECT 
                COUNT(CASE WHEN estatus = 'pendiente' THEN 1 END) as pendientes,
                COUNT(CASE WHEN estatus = 'en_gracias' THEN 1 END) as en_gracias,
                COUNT(CASE WHEN estatus = 'vencida' THEN 1 END) as vencidas,
                COUNT(CASE WHEN estatus = 'pagada' THEN 1 END) as pagadas
            FROM cuotas
        ")->queryOne();

            $contractStats = Yii::$app->db->createCommand("
            SELECT 
                COUNT(CASE WHEN estatus = 'Activo' THEN 1 END) as activos,
                COUNT(CASE WHEN estatus = 'Suspendido' THEN 1 END) as suspendidos,
                COUNT(CASE WHEN estatus = 'espera' THEN 1 END) as en_espera,
                COUNT(CASE WHEN estatus = 'Anulado' THEN 1 END) as anulados
            FROM contratos
        ")->queryOne();

            $output[] = "";
            $output[] = "📈 CUOTAS:";
            $output[] = "   • Pendientes: {$stats['pendientes']}";
            $output[] = "   • En período de gracia: {$stats['en_gracias']}";
            $output[] = "   • Vencidas: {$stats['vencidas']}";
            $output[] = "   • Pagadas: {$stats['pagadas']}";
            $output[] = "";
            $output[] = "📋 CONTRATOS:";
            $output[] = "   • Activos: {$contractStats['activos']}";
            $output[] = "   • Suspendidos: {$contractStats['suspendidos']}";
            $output[] = "   • En espera: {$contractStats['en_espera']}";
            $output[] = "   • Anulados: {$contractStats['anulados']}";
            $output[] = "";
            $output[] = "═══════════════════════════════════════════════════════════";
            $output[] = "🎉 VERIFICACIÓN DIARIA COMPLETADA EXITOSAMENTE";
            $output[] = "═══════════════════════════════════════════════════════════";

            $formattedOutput = implode("\n", $output);

            return [
                'success' => true,
                'output' => $formattedOutput,
                'message' => '✅ Verificación diaria completada exitosamente',
                'returnCode' => 0,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            Yii::error("Error in actionDailyCheck: " . $e->getMessage() . "\n" . $e->getTraceAsString(), 'cuotas');

            return [
                'success' => false,
                'output' => "❌ Error: " . $e->getMessage(),
                'message' => '❌ Error ejecutando verificación diaria',
                'returnCode' => -1,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * Displays the Daily Check page with the form
     */
    public function actionDailyCheckPage()
    {
        return $this->render('daily-check');
    }
}
