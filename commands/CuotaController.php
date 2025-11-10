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
        
        // Diagnóstico completo: Distribución de estatus en contratos
        $estatusCounts = Contratos::find()
            ->select(['estatus', 'COUNT(*) as total'])
            ->groupBy(['estatus'])
            ->asArray()
            ->all();
        $this->stdout("Distribución de estatus en contratos:\n");
        foreach ($estatusCounts as $row) {
            $estatus = $row['estatus'] ?: 'NULL';
            $total = $row['total'];
            $this->stdout("  - '{$estatus}': {$total}\n");
        }
        $this->stdout("\n");
        
        // Contar contratos con estatus válidos (sin filtro de fecha)
        $totalValidEstatus = Contratos::find()
            ->where(['in', 'estatus', ['activo', 'Creado', 'Registrado','suspendido']])
            ->count();
        $this->stdout("Total contratos con estatus válidos ('activo', 'Creado', etc.): {$totalValidEstatus}\n");
        
        // Obtener contratos que necesitan cuotas generadas (incluyendo diferentes estatus válidos)
        $fechaActual = date('Y-m-d');
        $contratos = Contratos::find()
            ->where(['or',
                ['in', 'LOWER(estatus)', ['activo', 'creado', 'registrado', 'suspendido']], // Case-insensitive para estatus válidos
                ['estatus' => null] // Incluir si estatus es null
            ]) // Incluir múltiples estatus válidos
            ->andWhere(['<=', 'fecha_ini', $fechaActual]) // Solo contratos que ya iniciaron
            ->all();
            
        $this->stdout("Encontrados " . count($contratos) . " contratos para procesar (ya filtrados por fecha_ini <= {$fechaActual}).\n");
            
        $cuotasGeneradas = 0;
        $cuotasAtrasadas = 0;
        
        $contratosSuspendidos = 0;
        
        foreach ($contratos as $contrato) {
            // Generar cuotas atrasadas primero
            $cuotasAtrasadasContrato = $this->generarCuotasAtrasadas($contrato);
            $cuotasAtrasadas += $cuotasAtrasadasContrato;
            
            // Generar cuota del mes actual si no existe
            $cuotaGenerada = $this->generarCuotaMesActual($contrato);
            if ($cuotaGenerada) {
                $cuotasGeneradas++;
            }
            
            // Verificar y suspender si hay cuotas vencidas
            $cuotasVencidas = Cuotas::find()
                ->where([
                    'contrato_id' => $contrato->id,
                    'estatus' => 'pendiente'
                ])
                ->andWhere(['<', 'fecha_vencimiento', date('Y-m-d')])
                ->count();
                
            if ($cuotasVencidas > 0 && $contrato->estatus !== 'suspendido') {
                $contrato->estatus = 'suspendido';
                if ($contrato->save()) {
                    $contratosSuspendidos++;
                    $this->stdout("  ⚠️  Contrato #{$contrato->id} suspendido por {$cuotasVencidas} cuotas vencidas.\n");
                } else {
                    $this->stderr("  ❌ Error al suspender contrato #{$contrato->id}\n");
                }
            }
        }
        
        $this->stdout("Proceso completado. Se generaron {$cuotasGeneradas} cuotas del mes actual, {$cuotasAtrasadas} cuotas atrasadas, y se suspendieron {$contratosSuspendidos} contratos con cuotas vencidas.\n");
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
            ->where(['contrato_id' => $contrato->id, 'estatus' => 'pagado'])
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
        $fechaActualObj = new \DateTime($fechaActual);
        $fechaInicioObj = new \DateTime($fechaInicio);
        $intervalo = $fechaInicioObj->diff($fechaActualObj);
        $mesesAtrasados = ($intervalo->y * 12) + $intervalo->m;
        
        if ($mesesAtrasados > 0) {
            $this->stdout("  Contrato #{$contrato->id}: Generando {$mesesAtrasados} cuotas atrasadas desde {$fechaInicio}...\n");
            
            // Fecha base para el primer vencimiento: Día 7 del mes de fechaInicio
            $fechaBase = new \DateTime($fechaInicioObj->format('Y-m-d'));
            $fechaBase->modify('first day of this month');
            $fechaBase->modify('+6 days'); // Día 7 del mes de fechaInicio
            
            for ($i = 0; $i < $mesesAtrasados; $i++) {
                // Para i=0: Usar fechaBase (mes inicial)
                // Para i>0: Avanzar i meses desde fechaBase
                $fechaVencimiento = clone $fechaBase;
                if ($i > 0) {
                    $fechaVencimiento->modify('first day of + ' . $i . ' months');
                    $fechaVencimiento->modify('+6 days');
                }
                
                $fechaVencStr = $fechaVencimiento->format('Y-m-d');
                $this->stdout("    Calculando cuota para: {$fechaVencStr}\n");
                
                // Verificar si ya existe esta cuota
                $existeCuota = Cuotas::find()
                    ->where(['contrato_id' => $contrato->id, 'fecha_vencimiento' => $fechaVencStr])
                    ->exists();
                    
                if (!$existeCuota) {
                    $cuota = new Cuotas([
                        'contrato_id' => $contrato->id,
                        'fecha_vencimiento' => $fechaVencStr,
                        'monto_usd' => $contrato->monto, // Monto completo para atrasadas
                        'estatus' => 'pendiente',
                        'rate_usd_bs' => $this->obtenerTasaCambioActual(),
                    ]);
                    
                    if ($cuota->save()) {
                        $cuotasGeneradas++;
                        $this->stdout("    ✓ Cuota atrasada generada: {$fechaVencStr} ({$contrato->monto} USD)\n");
                    } else {
                        $this->stderr("    ✗ Error al generar cuota atrasada para {$fechaVencStr}: " . print_r($cuota->errors, true) . "\n");
                    }
                } else {
                    $this->stdout("    ⚠️  Cuota ya existe para: {$fechaVencStr}\n");
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
        $cuotasExistentes = Cuotas::find()->where(['contrato_id' => $contrato->id])->count();
        $fechaIni = new \DateTime($contrato->fecha_ini);
        $diaMesIni = (int) $fechaIni->format('j'); // Día del mes de fecha_ini
        $mesAnoIni = $fechaIni->format('Y-m'); // Mes de fecha_ini para vencimiento inicial
        
        // Calcular fecha_vencimiento: Día 7 del mes de fecha_ini (para inicial) o mes actual (no inicial)
        $fechaVencimiento = new \DateTime($mesAnoIni . '-07');
        $hoy = new \DateTime();
        $fechaActualStr = date('Y-m-d');
        
        if ($cuotasExistentes > 0) {
            // Para cuotas no iniciales, usar mes actual
            $fechaVencimiento = new \DateTime($fechaActualStr);
            $fechaVencimiento->modify('first day of this month');
            $fechaVencimiento->modify('+6 days'); // Día 7 del mes actual
            $this->stdout("  Generando cuota mensual para contrato #{$contrato->id} (no inicial).\n");
        } else {
            $this->stdout("  Generando cuota inicial para contrato #{$contrato->id} con fecha_ini {$contrato->fecha_ini}...\n");
            
            // Ajustar vencimiento si es pasado (para inicial)
            if ($fechaVencimiento < $hoy) {
                $fechaVencimiento = new \DateTime($fechaActualStr);
                $fechaVencimiento->modify('first day of this month');
                $fechaVencimiento->modify('+6 days');
                $this->stdout("    Vencimiento ajustado a mes actual (era pasado).\n");
            } else {
                $this->stdout("    Vencimiento en futuro: {$fechaVencimiento->format('Y-m-d')}.\n");
            }
        }
        
        $fechaVencimientoStr = $fechaVencimiento->format('Y-m-d');
        
        // Verificar si ya existe una cuota para la fecha calculada
        $existeCuota = Cuotas::find()
            ->where([
                'contrato_id' => $contrato->id,
                'fecha_vencimiento' => $fechaVencimientoStr
            ])
            ->exists();
            
        if (!$existeCuota) {
            // Decidir monto: Prorrateo solo para inicial
            $montoCompleto = $contrato->monto;
            if ($cuotasExistentes == 0) { // Inicial
                if ($diaMesIni <= 7) {
                    $montoCuota = $montoCompleto;
                    $this->stdout("    Monto completo ({$montoCuota} USD) ya que fecha_ini en primeros 7 días.\n");
                } else {
                    $montoCuota = $montoCompleto / 2;
                    $this->stdout("    Monto prorrateado (mitad: {$montoCuota} USD) ya que fecha_ini después del día 7.\n");
                }
            } else {
                $montoCuota = $montoCompleto;
            }
            
            $cuota = new Cuotas([
                'contrato_id' => $contrato->id,
                'fecha_vencimiento' => $fechaVencimientoStr,
                'monto_usd' => $montoCuota,
                'estatus' => 'pendiente',
                'rate_usd_bs' => $this->obtenerTasaCambioActual(),
            ]);
            
            if ($cuota->save()) {
                $this->stdout("Cuota generada para el contrato #{$contrato->id} - Vencimiento: {$fechaVencimientoStr} - Monto: {$montoCuota} USD\n");
                return true;
            } else {
                $this->stderr("Error al generar cuota para el contrato #{$contrato->id}: " .
                    print_r($cuota->errors, true) . "\n");
                return false;
            }
        } else {
            $this->stdout("Cuota ya existe para vencimiento {$fechaVencimientoStr} en contrato #{$contrato->id}.\n");
        }
        
        return false;
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
        
        // Obtener contratos activos que ya iniciaron
        $fechaActual = date('Y-m-d');
        $contratos = Contratos::find()
            ->where(['in', 'estatus', ['activo', 'Creado', 'Registrado']])
            ->andWhere(['<=', 'fecha_ini', $fechaActual])
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
        
        // Obtener contratos que necesitan cuotas generadas y ya iniciaron
        $fechaActual = date('Y-m-d');
        $contratos = Contratos::find()
            ->where(['in', 'estatus', ['activo', 'Creado', 'Registrado']])
            ->andWhere(['<=', 'fecha_ini', $fechaActual])
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
     * Verifica cuotas vencidas y contratos vencidos por fecha para suspenderlos.
     * Uso: `yii cuota/verificar-vencidas`
     * 
     * @return int Código de salida
     */
    public function actionVerificarVencidas()
    {
        $this->stdout("Verificando cuotas vencidas y contratos a suspender...\n");
        
        $fechaActual = date('Y-m-d');
        $contratosSuspendidos = 0;
        
        // 1. VERIFICAR CONTRATOS VENCIDOS POR FECHA
        $this->stdout("1. Verificando contratos vencidos por fecha...\n");
        $contratosVencidos = Contratos::find()
            ->where(['in', 'estatus', ['activo', 'Creado', 'Registrado']])
            ->andWhere(['<', 'fecha_ven', $fechaActual])
            ->all();
            
        if (!empty($contratosVencidos)) {
            $this->stdout("Se encontraron " . count($contratosVencidos) . " contratos vencidos por fecha.\n");
            
            foreach ($contratosVencidos as $contrato) {
                if ($contrato->estatus !== 'suspendido') {
                    $contrato->estatus = 'suspendido';
                    if ($contrato->save()) {
                        $contratosSuspendidos++;
                        $this->stdout("⚠️  Contrato #{$contrato->id} suspendido por fecha de vencimiento: {$contrato->fecha_ven}\n");
                    } else {
                        $this->stderr("❌ Error al suspender contrato #{$contrato->id}\n");
                    }
                }
            }
        } else {
            $this->stdout("✅ No hay contratos vencidos por fecha.\n");
        }
        
        // 2. VERIFICAR CONTRATOS POR CUOTAS VENCIDAS (7 días después del vencimiento)
        $this->stdout("\n2. Verificando contratos por cuotas vencidas...\n");
        $fechaLimite = date('Y-m-d', strtotime('-7 days')); // 7 días después del vencimiento
        
        $cuotasVencidas = Cuotas::find()
            ->where(['estatus' => 'pendiente'])
            ->andWhere(['<', 'fecha_vencimiento', $fechaLimite])
            ->all();
            
        if (!empty($cuotasVencidas)) {
            $this->stdout("Se encontraron " . count($cuotasVencidas) . " cuotas vencidas sin pago.\n");
            
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
        } else {
            $this->stdout("✅ No hay cuotas vencidas que requieran suspensión.\n");
        }
        
        if ($contratosSuspendidos > 0) {
            $this->stdout("\n✅ Proceso completado. Se suspendieron {$contratosSuspendidos} contratos en total.\n");
        } else {
            $this->stdout("\n✅ Proceso completado. No se suspendió ningún contrato.\n");
        }
        
        return ExitCode::OK;
    }
    
    /**
     * Verifica solo contratos vencidos por fecha para suspenderlos.
     * Uso: `yii cuota/verificar-contratos-vencidos`
     * 
     * @return int Código de salida
     */
    public function actionVerificarContratosVencidos()
    {
        $this->stdout("Verificando contratos vencidos por fecha...\n");
        
        $fechaActual = date('Y-m-d');
        $contratosSuspendidos = 0;
        
        // Buscar contratos vencidos por fecha
        $contratosVencidos = Contratos::find()
            ->where(['in', 'estatus', ['activo', 'Creado', 'Registrado']])
            ->andWhere(['<', 'fecha_ven', $fechaActual])
            ->all();
            
        if (empty($contratosVencidos)) {
            $this->stdout("✅ No hay contratos vencidos por fecha.\n");
            return ExitCode::OK;
        }
        
        $this->stdout("Se encontraron " . count($contratosVencidos) . " contratos vencidos por fecha.\n");
        
        foreach ($contratosVencidos as $contrato) {
            if ($contrato->estatus !== 'suspendido') {
                $contrato->estatus = 'suspendido';
                if ($contrato->save()) {
                    $contratosSuspendidos++;
                    $this->stdout("⚠️  Contrato #{$contrato->id} suspendido por fecha de vencimiento: {$contrato->fecha_ven}\n");
                    $this->stdout("   - Cliente: " . ($contrato->user ? $contrato->user->nombre : 'N/A') . "\n");
                    $this->stdout("   - Plan: " . ($contrato->plan ? $contrato->plan->nombre : 'N/A') . "\n");
                    $this->stdout("   - Monto: {$contrato->monto} USD\n");
                } else {
                    $this->stderr("❌ Error al suspender contrato #{$contrato->id}\n");
                }
            }
        }
        
        $this->stdout("Proceso completado. Se suspendieron {$contratosSuspendidos} contratos vencidos por fecha.\n");
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
     * Muestra un resumen de contratos próximos a vencer.
     * Uso: `yii cuota/resumen-proximos-vencer`
     * 
     * @return int Código de salida
     */
    public function actionResumenProximosVencer()
    {
        $this->stdout("=== RESUMEN DE CONTRATOS PRÓXIMOS A VENCER ===\n\n");
        
        $fechaActual = date('Y-m-d');
        $proximaSemana = date('Y-m-d', strtotime('+7 days'));
        $proximoMes = date('Y-m-d', strtotime('+30 days'));
        
        // Contratos que vencen en la próxima semana
        $contratosProximaSemana = Contratos::find()
            ->where(['in', 'estatus', ['activo', 'Creado', 'Registrado']])
            ->andWhere(['between', 'fecha_ven', $fechaActual, $proximaSemana])
            ->orderBy(['fecha_ven' => SORT_ASC])
            ->all();
            
        // Contratos que vencen en el próximo mes
        $contratosProximoMes = Contratos::find()
            ->where(['in', 'estatus', ['activo', 'Creado', 'Registrado']])
            ->andWhere(['between', 'fecha_ven', $proximaSemana, $proximoMes])
            ->orderBy(['fecha_ven' => SORT_ASC])
            ->all();
            
        if (!empty($contratosProximaSemana)) {
            $this->stdout("🚨 CONTRATOS QUE VENCEN EN LA PRÓXIMA SEMANA:\n");
            foreach ($contratosProximaSemana as $contrato) {
                $this->stdout("  - Contrato #{$contrato->id}: Vence el {$contrato->fecha_ven}\n");
                $this->stdout("    Cliente: " . ($contrato->user ? $contrato->user->nombre : 'N/A') . "\n");
                $this->stdout("    Plan: " . ($contrato->plan ? $contrato->plan->nombre : 'N/A') . "\n");
                $this->stdout("    Monto: {$contrato->monto} USD\n\n");
            }
        } else {
            $this->stdout("✅ No hay contratos que venzan en la próxima semana.\n\n");
        }
        
        if (!empty($contratosProximoMes)) {
            $this->stdout("⚠️  CONTRATOS QUE VENCEN EN EL PRÓXIMO MES:\n");
            foreach ($contratosProximoMes as $contrato) {
                $this->stdout("  - Contrato #{$contrato->id}: Vence el {$contrato->fecha_ven}\n");
                $this->stdout("    Cliente: " . ($contrato->user ? $contrato->user->nombre : 'N/A') . "\n");
                $this->stdout("    Plan: " . ($contrato->plan ? $contrato->plan->nombre : 'N/A') . "\n");
                $this->stdout("    Monto: {$contrato->monto} USD\n\n");
            }
        } else {
            $this->stdout("✅ No hay contratos que venzan en el próximo mes.\n\n");
        }
        
        $totalProximos = count($contratosProximaSemana) + count($contratosProximoMes);
        $this->stdout("Total de contratos próximos a vencer: {$totalProximos}\n");
        
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
            ->where(['contrato_id' => $contrato->id, 'estatus' => 'pagado'])
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
            ->where(['estatus' => 'pendiente'])
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
