<?php
// controllers/CuotaWebController.php   

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;

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
            'generar', 'generar-mensual', 'generar-atrasadas', 'verificar-vencidas',
            'verificar-diario', 'resumen-proximos-vencer', 'resumen-atrasadas',
            'verificar-todo', 'actualizar-montos', 'verificar-contratos-vencidos', 'verificar-espera', 
            'reparar-relacion-pagos', 'diagnostico-inconsistencias', 'generar-cuotas-faltantes' // ← AÑADIR ESTO
        ])) {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
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

    public function actionVerificarEspera()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        try {
            $result = $this->runConsoleCommand('verificar-espera');
            
            return [
                'success' => $result['success'],
                'output' => $result['output'],
                'message' => $result['success'] ? 'Verificación de contratos en espera completada' : 'Error en verificación',
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
                $cuotas = \app\models\Cuotas::find()
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
                        $updated = \app\models\Cuotas::updateAll(
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

    /**
     * Reactiva un contrato si está suspendido
     */
    private function reactivateContractIfNeeded($userId, &$output)
    {
        try {
            $contrato = \app\models\Contratos::find()
                ->where(['user_id' => $userId])
                ->andWhere(['estatus' => 'suspendido'])
                ->one();
                
            if ($contrato) {
                // Check if there are any pending cuotas
                $pendingCuotas = \app\models\Cuotas::find()
                    ->where(['contrato_id' => $contrato->id])
                    ->andWhere(['estatus' => 'pendiente'])
                    ->andWhere(['<', 'fecha_vencimiento', date('Y-m-d')])
                    ->count();
                    
                if ($pendingCuotas == 0) {
                    $contrato->estatus = 'Activo';
                    if ($contrato->save()) {
                        $output .= "  🔄 Contrato #{$contrato->id} reactivado automáticamente\n";
                        
                        // Also update user solvent status
                        $user = \app\models\UserDatos::findOne($userId);
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
                $cuota = \app\models\Cuotas::findOne($cuotaId);
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
                $existingCuota = \app\models\Cuotas::find()
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
                    $nuevaCuota = new \app\models\Cuotas([
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

}