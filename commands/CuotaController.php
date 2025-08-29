<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use app\models\Cuotas;
use app\models\Contrato; // Asegúrate de que este modelo exista
use yii\helpers\Console;

/**
 * Controlador de comandos para la gestión de cuotas de suscripción.
 */
class CuotaController extends Controller
{
    /**
     * Genera las cuotas pendientes basadas en los contratos activos.
     * Uso: `yii cuota/generar`
     * 
     * @return int Código de salida
     */
    public function actionGenerar()
    {
        $this->stdout("Iniciando generación de cuotas...\n");
        
        // Obtener contratos activos que necesitan cuotas generadas
        $contratos = Contrato::find()
            ->where(['estado' => 'activo']) // Ajusta según tu modelo Contrato
            ->all();
            
        $cuotasGeneradas = 0;
        
        foreach ($contratos as $contrato) {
            // Verificar si ya existe una cuota pendiente para el próximo período
            $existeCuota = Cuotas::find()
                ->where(['contrato_id' => $contrato->id, 'Estatus' => 'pendiente'])
                ->exists();
                
            if (!$existeCuota) {
                $proximoVencimiento = date('Y-m-d', strtotime('+1 month')); // Ajusta según la periodicidad
                
                $cuota = new Cuotas([
                    'contrato_id' => $contrato->id,
                    'fecha_vencimiento' => $proximoVencimiento,
                    'monto' => $contrato->monto_cuota, // Asegúrate de que este campo exista en el modelo Contrato
                    'Estatus' => 'pendiente',
                    'rate_usd_bs' => $this->obtenerTasaCambioActual(), // Implementa este método según tu lógica
                ]);
                
                if ($cuota->save()) {
                    $cuotasGeneradas++;
                    $this->stdout("Cuota generada para el contrato #{$contrato->id} - Vencimiento: {$proximoVencimiento}\n");
                } else {
                    $this->stderr("Error al generar cuota para el contrato #{$contrato->id}: " . 
                        print_r($cuota->errors, true) . "\n");
                }
            }
        }
        
        $this->stdout("Proceso completado. Se generaron {$cuotasGeneradas} cuotas.\n");
        return ExitCode::OK;
    }
    
    /**
     * Notifica a los usuarios sobre cuotas próximas a vencer.
     * Uso: `yii cuota/notificar`
     * 
     * @return int Código de salida
     */
    public function actionNotificar()
    {
        $this->stdout("Iniciando notificación de cuotas próximas a vencer...\n");
        
        $hoy = date('Y-m-d');
        $proximaSemana = date('Y-m-d', strtotime('+7 days'));
        
        $cuotas = Cuotas::find()
            ->where(['Estatus' => 'pendiente'])
            ->andWhere(['between', 'fecha_vencimiento', $hoy, $proximaSemana])
            ->all();
            
        $notificacionesEnviadas = 0;
        
        foreach ($cuotas as $cuota) {
            // Implementa la lógica de envío de notificaciones
            // Por ejemplo, enviar correo electrónico o notificación push
            
            $this->stdout("Notificación enviada para cuota #{$cuota->id} del contrato #{$cuota->contrato_id} que vence el {$cuota->fecha_vencimiento}\n");
            $notificacionesEnviadas++;
        }
        
        $this->stdout("Proceso completado. Se enviaron {$notificacionesEnviadas} notificaciones.\n");
        return ExitCode::OK;
    }
    
    /**
     * Obtiene la tasa de cambio actual.
     * Implementa esta función según tu lógica de obtención de tasas.
     * 
     * @return int Tasa de cambio actual
     */
    private function obtenerTasaCambioActual()
    {
        // Implementa la lógica para obtener la tasa de cambio actual
        // Por ejemplo, desde una API o base de datos
        return 1; // Valor por defecto, reemplazar con implementación real
    }
}
