<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use app\models\Cuotas;
use app\models\Contratos; // Modelo correcto
use app\models\TasaCambio;
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
        
        // Obtener contratos que necesitan cuotas generadas (incluyendo diferentes estatus válidos)
        $contratos = Contratos::find()
            ->where(['in', 'estatus', ['activo', 'Creado', 'Registrado']]) // Incluir múltiples estatus válidos
            ->all();
            
        $cuotasGeneradas = 0;
        $cuotasAtrasadas = 0;
        
        foreach ($contratos as $contrato) {
            // Generar cuotas atrasadas primero
            $cuotasAtrasadasContrato = $this->generarCuotasAtrasadas($contrato);
            $cuotasAtrasadas += $cuotasAtrasadasContrato;
            
            // Generar cuota del mes actual si no existe
            $cuotaGenerada = $this->generarCuotaMesActual($contrato);
            if ($cuotaGenerada) {
                $cuotasGeneradas++;
            }
        }
        
        $this->stdout("Proceso completado. Se generaron {$cuotasGeneradas} cuotas del mes actual y {$cuotasAtrasadas} cuotas atrasadas.\n");
        return ExitCode::OK;
    }
    
    /**
     * Genera cuotas atrasadas para un contrato.
     * 
     * @param Contratos $contrato
     * @return int Número de cuotas atrasadas generadas
     */
    private function generarCuotasAtrasadas($contrato)
    {
        $cuotasGeneradas = 0;
        
        // Obtener la última cuota pagada o la fecha de inicio del contrato
        $ultimaCuotaPagada = Cuotas::find()
            ->where(['contrato_id' => $contrato->id, 'Estatus' => 'pagado'])
            ->orderBy(['fecha_vencimiento' => SORT_DESC])
            ->one();
            
        $fechaInicio = $ultimaCuotaPagada ? 
            date('Y-m-d', strtotime($ultimaCuotaPagada->fecha_vencimiento . ' +1 month')) :
            $contrato->fecha_ini;
            
        $fechaActual = date('Y-m-d');
        
        // Si no hay cuotas atrasadas, salir
        if (strtotime($fechaInicio) >= strtotime($fechaActual)) {
            return 0;
        }
        
        // Calcular cuántas cuotas están atrasadas
        $fechaActual = new \DateTime($fechaActual);
        $fechaInicio = new \DateTime($fechaInicio);
        $intervalo = $fechaInicio->diff($fechaActual);
        $mesesAtrasados = ($intervalo->y * 12) + $intervalo->m;
        
        if ($mesesAtrasados > 0) {
            $this->stdout("  Contrato #{$contrato->id}: Generando {$mesesAtrasados} cuotas atrasadas...\n");
            
            for ($i = 0; $i < $mesesAtrasados; $i++) {
                $fechaVencimiento = clone $fechaInicio;
                $fechaVencimiento->add(new \DateInterval('P' . $i . 'M'));
                
                // Verificar si ya existe esta cuota
                $existeCuota = Cuotas::find()
                    ->where(['contrato_id' => $contrato->id, 'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d')])
                    ->exists();
                    
                if (!$existeCuota) {
                    $cuota = new Cuotas([
                        'contrato_id' => $contrato->id,
                        'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
                        'monto_usd' => $contrato->monto,
                        'Estatus' => 'pendiente',
                        'rate_usd_bs' => $this->obtenerTasaCambioActual(),
                    ]);
                    
                    if ($cuota->save()) {
                        $cuotasGeneradas++;
                        $this->stdout("    ✓ Cuota atrasada generada: {$fechaVencimiento->format('Y-m-d')}\n");
                    } else {
                        $this->stderr("    ✗ Error al generar cuota atrasada: " . print_r($cuota->errors, true) . "\n");
                    }
                }
            }
        }
        
        return $cuotasGeneradas;
    }
    
    /**
     * Genera la cuota del mes actual para un contrato.
     * 
     * @param Contratos $contrato
     * @return bool True si se generó la cuota
     */
    private function generarCuotaMesActual($contrato)
    {
        // Obtener el primer día del mes actual
        $primerDiaMes = date('Y-m-01');
        
        // Verificar si ya existe una cuota para este mes
        $existeCuota = Cuotas::find()
            ->where(['contrato_id' => $contrato->id, 'fecha_vencimiento' => $primerDiaMes])
            ->exists();
            
        if (!$existeCuota) {
            $cuota = new Cuotas([
                'contrato_id' => $contrato->id,
                'fecha_vencimiento' => $primerDiaMes,
                'monto_usd' => $contrato->monto,
                'Estatus' => 'pendiente',
                'rate_usd_bs' => $this->obtenerTasaCambioActual(),
            ]);
            
            if ($cuota->save()) {
                $this->stdout("Cuota del mes actual generada para el contrato #{$contrato->id} - Vencimiento: {$primerDiaMes}\n");
                return true;
            } else {
                $this->stderr("Error al generar cuota del mes actual para el contrato #{$contrato->id}: " . 
                    print_r($cuota->errors, true) . "\n");
                return false;
            }
        }
        
        return false;
    }
    
    /**
     * Genera la cuota inicial para un contrato recién creado.
     * Uso: `yii cuota/generar-inicial [contrato_id]`
     * 
     * @param int $contratoId ID del contrato
     * @return int Código de salida
     */
    public function actionGenerarInicial($contratoId)
    {
        $this->stdout("Generando cuota inicial para el contrato #{$contratoId}...\n");
        
        $contrato = Contratos::findOne($contratoId);
        if (!$contrato) {
            $this->stderr("❌ Contrato #{$contratoId} no encontrado.\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
        
        // Verificar si ya existe una cuota inicial
        $existeCuotaInicial = Cuotas::find()
            ->where(['contrato_id' => $contratoId])
            ->exists();
            
        if ($existeCuotaInicial) {
            $this->stdout("ℹ️  El contrato #{$contratoId} ya tiene cuotas generadas.\n");
            return ExitCode::OK;
        }
        
        // Generar cuota inicial para el mes de inicio del contrato
        $fechaInicio = new \DateTime($contrato->fecha_ini);
        $fechaVencimiento = $fechaInicio->format('Y-m-01');
        
        $cuota = new Cuotas([
            'contrato_id' => $contratoId,
            'fecha_vencimiento' => $fechaVencimiento,
            'monto_usd' => $contrato->monto,
            'Estatus' => 'pendiente',
            'rate_usd_bs' => $this->obtenerTasaCambioActual(),
        ]);
        
        if ($cuota->save()) {
            $this->stdout("✅ Cuota inicial generada para el contrato #{$contratoId} - Vencimiento: {$fechaVencimiento}\n");
            return ExitCode::OK;
        } else {
            $this->stderr("❌ Error al generar cuota inicial: " . print_r($cuota->errors, true) . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
    
    /**
     * Genera cuotas mensuales para todos los contratos activos (para ejecutar el día 1 de cada mes).
     * Uso: `yii cuota/generar-mensual`
     * 
     * @return int Código de salida
     */
    public function actionGenerarMensual()
    {
        $this->stdout("Iniciando generación de cuotas mensuales...\n");
        
        // Verificar que sea el día 1 del mes
        if (date('j') !== '1') {
            $this->stdout("⚠️  Este comando debe ejecutarse el día 1 de cada mes.\n");
            $this->stdout("Hoy es el día " . date('j') . " del mes.\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
        
        // Obtener contratos activos
        $contratos = Contratos::find()
            ->where(['in', 'estatus', ['activo', 'Creado', 'Registrado']])
            ->all();
            
        $cuotasGeneradas = 0;
        
        foreach ($contratos as $contrato) {
            // Generar cuota para el mes actual
            $cuotaGenerada = $this->generarCuotaMesActual($contrato);
            if ($cuotaGenerada) {
                $cuotasGeneradas++;
            }
        }
        
        $this->stdout("✅ Proceso completado. Se generaron {$cuotasGeneradas} cuotas mensuales.\n");
        return ExitCode::OK;
    }
    
    /**
     * Genera solo las cuotas atrasadas para contratos que no han pagado.
     * Uso: `yii cuota/generar-atrasadas`
     * 
     * @return int Código de salida
     */
    public function actionGenerarAtrasadas()
    {
        $this->stdout("Iniciando generación de cuotas atrasadas...\n");
        
        // Obtener contratos que necesitan cuotas generadas
        $contratos = Contratos::find()
            ->where(['in', 'estatus', ['activo', 'Creado', 'Registrado']])
            ->all();
            
        $totalCuotasAtrasadas = 0;
        $contratosConAtrasos = 0;
        
        foreach ($contratos as $contrato) {
            $cuotasAtrasadas = $this->generarCuotasAtrasadas($contrato);
            if ($cuotasAtrasadas > 0) {
                $contratosConAtrasos++;
                $totalCuotasAtrasadas += $cuotasAtrasadas;
            }
        }
        
        if ($totalCuotasAtrasadas > 0) {
            $this->stdout("✅ Proceso completado. Se generaron {$totalCuotasAtrasadas} cuotas atrasadas en {$contratosConAtrasos} contratos.\n");
        } else {
            $this->stdout("ℹ️  No se encontraron cuotas atrasadas para generar.\n");
        }
        
        return ExitCode::OK;
    }
    
    /**
     * Verifica cuotas vencidas y suspende contratos que no han pagado en los primeros 5 días.
     * Uso: `yii cuota/verificar-vencidas`
     * 
     * @return int Código de salida
     */
    public function actionVerificarVencidas()
    {
        $this->stdout("Verificando cuotas vencidas y contratos a suspender...\n");
        
        $fechaActual = date('Y-m-d');
        $fechaLimite = date('Y-m-d', strtotime('-5 days')); // 5 días después del vencimiento
        
        // Buscar cuotas vencidas que no se han pagado en los primeros 5 días
        $cuotasVencidas = Cuotas::find()
            ->where(['Estatus' => 'pendiente'])
            ->andWhere(['<', 'fecha_vencimiento', $fechaLimite])
            ->all();
            
        if (empty($cuotasVencidas)) {
            $this->stdout("✅ No hay cuotas vencidas que requieran suspensión.\n");
            return ExitCode::OK;
        }
        
        $this->stdout("Se encontraron " . count($cuotasVencidas) . " cuotas vencidas sin pago.\n");
        $contratosSuspendidos = 0;
        
        foreach ($cuotasVencidas as $cuota) {
            $contrato = Contratos::findOne($cuota->contrato_id);
            if ($contrato && $contrato->estatus !== 'suspendido') {
                $contrato->estatus = 'suspendido';
                if ($contrato->save()) {
                    $contratosSuspendidos++;
                    $this->stdout("⚠️  Contrato #{$contrato->id} suspendido por cuota vencida del {$cuota->fecha_vencimiento}\n");
                } else {
                    $this->stderr("❌ Error al suspender contrato #{$contrato->id}\n");
                }
            }
        }
        
        $this->stdout("Proceso completado. Se suspendieron {$contratosSuspendidos} contratos.\n");
        return ExitCode::OK;
    }
    
    /**
     * Verifica cuotas vencidas diariamente (para ejecutar con cron job).
     * Uso: `yii cuota/verificar-diario`
     * 
     * @return int Código de salida
     */
    public function actionVerificarDiario()
    {
        $this->stdout("=== VERIFICACIÓN DIARIA DE CUOTAS ===\n");
        $this->stdout("Fecha: " . date('Y-m-d H:i:s') . "\n\n");
        
        // 1. Verificar cuotas vencidas y suspender contratos
        $this->stdout("1. Verificando cuotas vencidas...\n");
        $this->runAction('verificar-vencidas');
        
        $this->stdout("\n2. Generando cuotas mensuales (si es día 1)...\n");
        if (date('j') === '1') {
            $this->runAction('generar-mensual');
        } else {
            $this->stdout("   No es día 1 del mes. Saltando generación mensual.\n");
        }
        
        $this->stdout("\n✅ Verificación diaria completada.\n");
        return ExitCode::OK;
    }
    
    /**
     * Muestra un resumen de cuotas atrasadas por contrato.
     * Uso: `yii cuota/resumen-atrasadas`
     * 
     * @return int Código de salida
     */
    public function actionResumenAtrasadas()
    {
        $this->stdout("=== RESUMEN DE CUOTAS ATRASADAS ===\n\n");
        
        $contratos = Contratos::find()
            ->where(['in', 'estatus', ['activo', 'Creado', 'Registrado']])
            ->all();
            
        $totalAtrasos = 0;
        
        foreach ($contratos as $contrato) {
            $cuotasAtrasadas = $this->calcularCuotasAtrasadas($contrato);
            if ($cuotasAtrasadas > 0) {
                $this->stdout("Contrato #{$contrato->id}:\n");
                $this->stdout("  - Monto por cuota: " . ($contrato->monto ?: 'SIN MONTO') . " USD\n");
                $this->stdout("  - Cuotas atrasadas: {$cuotasAtrasadas}\n");
                $this->stdout("  - Monto total atrasado: " . (($contrato->monto ?: 0) * $cuotasAtrasadas) . " USD\n");
                $this->stdout("  - Fecha inicio: {$contrato->fecha_ini}\n");
                
                // Mostrar cuotas existentes para este contrato
                $cuotasExistentes = Cuotas::find()
                    ->where(['contrato_id' => $contrato->id])
                    ->orderBy(['fecha_vencimiento' => SORT_ASC])
                    ->all();
                    
                if (!empty($cuotasExistentes)) {
                    $this->stdout("  - Cuotas existentes:\n");
                    foreach ($cuotasExistentes as $cuota) {
                        $this->stdout("    * {$cuota->fecha_vencimiento} - {$cuota->Estatus} - {$cuota->monto_usd} USD\n");
                    }
                }
                
                $this->stdout("\n");
                $totalAtrasos += $cuotasAtrasadas;
            }
        }
        
        if ($totalAtrasos > 0) {
            $this->stdout("Total de cuotas atrasadas en el sistema: {$totalAtrasos}\n");
        } else {
            $this->stdout("No hay cuotas atrasadas en el sistema.\n");
        }
        
        return ExitCode::OK;
    }
    
    /**
     * Elimina cuotas futuras incorrectas y regenera con la lógica corregida.
     * Uso: `yii cuota/corregir-cuotas-futuras`
     * 
     * @return int Código de salida
     */
    public function actionCorregirCuotasFuturas()
    {
        $this->stdout("Iniciando corrección de cuotas futuras...\n");
        
        // Buscar cuotas futuras (después de la fecha actual)
        $fechaActual = date('Y-m-d');
        $cuotasFuturas = Cuotas::find()
            ->where(['>', 'fecha_vencimiento', $fechaActual])
            ->all();
            
        if (empty($cuotasFuturas)) {
            $this->stdout("✅ No hay cuotas futuras para corregir.\n");
            return ExitCode::OK;
        }
        
        $this->stdout("Se encontraron " . count($cuotasFuturas) . " cuotas futuras.\n");
        $cuotasEliminadas = 0;
        
        foreach ($cuotasFuturas as $cuota) {
            $this->stdout("Eliminando cuota #{$cuota->id} con vencimiento {$cuota->fecha_vencimiento}...\n");
            if ($cuota->delete()) {
                $cuotasEliminadas++;
                $this->stdout("✅ Cuota eliminada.\n");
            } else {
                $this->stderr("❌ Error al eliminar cuota #{$cuota->id}\n");
            }
        }
        
        $this->stdout("Se eliminaron {$cuotasEliminadas} cuotas futuras.\n");
        $this->stdout("Ahora puedes ejecutar 'yii cuota/generar' para regenerar con la lógica corregida.\n");
        
        return ExitCode::OK;
    }
    
    /**
     * Calcula cuántas cuotas están atrasadas para un contrato (sin generarlas).
     * 
     * @param Contratos $contrato
     * @return int Número de cuotas atrasadas
     */
    private function calcularCuotasAtrasadas($contrato)
    {
        // Obtener la última cuota pagada o la fecha de inicio del contrato
        $ultimaCuotaPagada = Cuotas::find()
            ->where(['contrato_id' => $contrato->id, 'Estatus' => 'pagado'])
            ->orderBy(['fecha_vencimiento' => SORT_DESC])
            ->one();
            
        $fechaInicio = $ultimaCuotaPagada ? 
            date('Y-m-d', strtotime($ultimaCuotaPagada->fecha_vencimiento . ' +1 month')) :
            $contrato->fecha_ini;
            
        $fechaActual = date('Y-m-d');
        
        // Si no hay cuotas atrasadas, salir
        if (strtotime($fechaInicio) >= strtotime($fechaActual)) {
            return 0;
        }
        
        // Calcular cuántas cuotas están atrasadas
        $fechaActual = new \DateTime($fechaActual);
        $fechaInicio = new \DateTime($fechaInicio);
        $intervalo = $fechaInicio->diff($fechaActual);
        $mesesAtrasados = ($intervalo->y * 12) + $intervalo->m;
        
        return max(0, $mesesAtrasados);
    }
    
    /**
     * Actualiza los montos de cuotas existentes que no tienen monto.
     * Uso: `yii cuota/actualizar-montos`
     * 
     * @return int Código de salida
     */
    public function actionActualizarMontos()
    {
        $this->stdout("Iniciando actualización de montos de cuotas...\n");
        
        // Buscar cuotas sin monto
        $cuotasSinMonto = Cuotas::find()
            ->where(['or', ['monto_usd' => null], ['monto_usd' => 0]])
            ->all();
            
        if (empty($cuotasSinMonto)) {
            $this->stdout("✅ No hay cuotas sin monto para actualizar.\n");
            return ExitCode::OK;
        }
        
        $this->stdout("Se encontraron " . count($cuotasSinMonto) . " cuotas sin monto.\n");
        $cuotasActualizadas = 0;
        
        foreach ($cuotasSinMonto as $cuota) {
            // Obtener el contrato asociado
            $contrato = Contratos::findOne($cuota->contrato_id);
            if ($contrato && $contrato->monto) {
                $montoAnterior = $cuota->monto_usd;
                $cuota->monto_usd = $contrato->monto;
                
                if ($cuota->save()) {
                    $cuotasActualizadas++;
                    $this->stdout("✅ Cuota #{$cuota->id} actualizada: {$montoAnterior} → {$contrato->monto} USD\n");
                } else {
                    $this->stderr("❌ Error al actualizar cuota #{$cuota->id}: " . print_r($cuota->errors, true) . "\n");
                }
            } else {
                $this->stderr("⚠️  Contrato #{$cuota->contrato_id} no encontrado o sin monto para cuota #{$cuota->id}\n");
            }
        }
        
        $this->stdout("Proceso completado. Se actualizaron {$cuotasActualizadas} cuotas.\n");
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
     * Verifica la conexión a la base de datos.
     * Uso: `yii cuota/verificar-conexion`
     * 
     * @return int Código de salida
     */
    public function actionVerificarConexion()
    {
        $this->stdout("Verificando conexión a la base de datos...\n");
        
        try {
            $connection = Yii::$app->db;
            $connection->open();
            $this->stdout("✅ Conexión a la base de datos exitosa.\n");
            $connection->close();
            return ExitCode::OK;
        } catch (\Exception $e) {
            $this->stderr("❌ Error de conexión: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
    
    /**
     * Verifica que existan contratos activos.
     * Uso: `yii cuota/verificar-contratos`
     * 
     * @return int Código de salida
     */
    public function actionVerificarContratos()
    {
        $this->stdout("Verificando contratos activos...\n");
        
        try {
            $contratos = Contratos::find()
                ->where(['estatus' => 'activo'])
                ->all();
                
            $totalContratos = count($contratos);
            $this->stdout("✅ Se encontraron {$totalContratos} contratos activos.\n");
            
            if ($totalContratos > 0) {
                $this->stdout("Primeros 5 contratos:\n");
                foreach (array_slice($contratos, 0, 5) as $contrato) {
                    $this->stdout("  - Contrato #{$contrato->id}: Monto = {$contrato->monto}, Estatus = {$contrato->estatus}\n");
                }
            }
            
            return ExitCode::OK;
        } catch (\Exception $e) {
            $this->stderr("❌ Error al verificar contratos: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
    
    /**
     * Verifica el estado de todos los contratos en la base de datos.
     * Uso: `yii cuota/verificar-estado-contratos`
     * 
     * @return int Código de salida
     */
    public function actionVerificarEstadoContratos()
    {
        $this->stdout("Verificando estado de todos los contratos...\n");
        
        try {
            // Obtener todos los contratos agrupados por estatus
            $contratos = Contratos::find()
                ->select(['estatus', 'COUNT(*) as total'])
                ->groupBy(['estatus'])
                ->asArray()
                ->all();
                
            if (empty($contratos)) {
                $this->stderr("❌ No se encontraron contratos en la base de datos.\n");
                return ExitCode::UNSPECIFIED_ERROR;
            }
            
            $this->stdout("Distribución de contratos por estatus:\n");
            foreach ($contratos as $grupo) {
                $estatus = $grupo['estatus'] ?: 'sin estatus';
                $total = $grupo['total'];
                $this->stdout("  - {$estatus}: {$total} contratos\n");
            }
            
            // Mostrar algunos ejemplos de cada estatus
            $this->stdout("\nEjemplos de contratos por estatus:\n");
            foreach ($contratos as $grupo) {
                $estatus = $grupo['estatus'] ?: null;
                $ejemplos = Contratos::find()
                    ->where(['estatus' => $estatus])
                    ->limit(3)
                    ->all();
                    
                if (!empty($ejemplos)) {
                    $estatusLabel = $estatus ?: 'sin estatus';
                    $this->stdout("  {$estatusLabel}:\n");
                    foreach ($ejemplos as $contrato) {
                        $this->stdout("    - ID: {$contrato->id}, Monto: {$contrato->monto}, Fecha: {$contrato->fecha_ini}\n");
                    }
                }
            }
            
            return ExitCode::OK;
        } catch (\Exception $e) {
            $this->stderr("❌ Error al verificar estado de contratos: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
    
    /**
     * Verifica que existan tasas de cambio.
     * Uso: `yii cuota/verificar-tasa`
     * 
     * @return int Código de salida
     */
    public function actionVerificarTasa()
    {
        $this->stdout("Verificando tasas de cambio...\n");
        
        try {
            $tasaCambio = TasaCambio::find()
                ->orderBy(['fecha' => SORT_DESC, 'hora' => SORT_DESC])
                ->one();
                
            if ($tasaCambio) {
                $this->stdout("✅ Tasa de cambio encontrada:\n");
                $this->stdout("  - Fecha: {$tasaCambio->fecha}\n");
                $this->stdout("  - Hora: {$tasaCambio->hora}\n");
                $this->stdout("  - Tasa: {$tasaCambio->tasa_cambio}\n");
            } else {
                $this->stderr("⚠️  No se encontraron tasas de cambio registradas.\n");
            }
            
            return ExitCode::OK;
        } catch (\Exception $e) {
            $this->stderr("❌ Error al verificar tasas de cambio: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
    
    /**
     * Verifica la estructura de las tablas necesarias.
     * Uso: `yii cuota/verificar-tablas`
     * 
     * @return int Código de salida
     */
    public function actionVerificarTablas()
    {
        $this->stdout("Verificando estructura de tablas...\n");
        
        $tablas = ['contratos', 'cuotas', 'tasa_cambio'];
        $errores = [];
        
        foreach ($tablas as $tabla) {
            try {
                $schema = Yii::$app->db->getSchema()->getTableSchema($tabla);
                if ($schema) {
                    $this->stdout("✅ Tabla '{$tabla}' existe.\n");
                } else {
                    $errores[] = "Tabla '{$tabla}' no existe.";
                }
            } catch (\Exception $e) {
                $errores[] = "Error al verificar tabla '{$tabla}': " . $e->getMessage();
            }
        }
        
        if (empty($errores)) {
            $this->stdout("✅ Todas las tablas están disponibles.\n");
            return ExitCode::OK;
        } else {
            foreach ($errores as $error) {
                $this->stderr("❌ {$error}\n");
            }
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
    
    /**
     * Ejecuta todas las verificaciones previas.
     * Uso: `yii cuota/verificar-todo`
     * 
     * @return int Código de salida
     */
    public function actionVerificarTodo()
    {
        $this->stdout("=== VERIFICACIONES PREVIAS ===\n\n");
        
        $verificaciones = [
            'verificar-conexion' => 'Conexión a BD',
            'verificar-tablas' => 'Estructura de tablas',
            'verificar-contratos' => 'Contratos activos',
            'verificar-tasa' => 'Tasas de cambio'
        ];
        
        $errores = 0;
        
        foreach ($verificaciones as $action => $descripcion) {
            $this->stdout("🔍 Verificando {$descripcion}...\n");
            $resultado = $this->runAction($action);
            
            if ($resultado !== ExitCode::OK) {
                $errores++;
                $this->stderr("❌ Falló verificación de {$descripcion}\n");
            }
            
            $this->stdout("\n");
        }
        
        if ($errores === 0) {
            $this->stdout("✅ Todas las verificaciones pasaron. Puedes ejecutar 'yii cuota/generar' con confianza.\n");
            return ExitCode::OK;
        } else {
            $this->stderr("❌ Se encontraron {$errores} errores. Corrige los problemas antes de ejecutar 'yii cuota/generar'.\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
    
    /**
     * Obtiene la tasa de cambio actual.
     * Implementa esta función según tu lógica de obtención de tasas.
     * 
     * @return int Tasa de cambio actual
     */
    private function obtenerTasaCambioActual()
    {
        // Obtener la tasa de cambio más reciente de la base de datos
        $tasacambio = TasaCambio::find()->select(['tasa_cambio'])->where(['fecha' => date('Y-m-d')])->one();
        if ($tasacambio) {
            return $tasacambio->tasa_cambio;
        }
        
        return 1; // Valor por defecto si no hay tasa de cambio registrada
    }
}
