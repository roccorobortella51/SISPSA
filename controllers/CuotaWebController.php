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
            'verificar-todo', 'actualizar-montos', 'verificar-contratos-vencidos', 'verificar-espera'
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
     * Repara la relación entre pagos y cuotas desde la interfaz web.
     */
    public function actionRepararRelacionPagos()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        try {
            $result = $this->runConsoleCommand('reparar-relacion-pagos');
            
            return [
                'success' => $result['success'],
                'output' => $result['output'],
                'message' => $result['success'] ? 'Reparación de relaciones pago-cuota completada' : 'Error en reparación',
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

}