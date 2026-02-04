<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use app\models\Cuotas;
use app\models\Contratos; // Modelo correcto
use app\models\TasaCambio;
use yii\helpers\Console;
use app\models\Pagos;  // ← ADD THIS IMPORT
use app\models\UserDatos;  // Used in actionRepararRelacionPagos() method

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
            ->where(['in', 'estatus', ['activo', 'Creado', 'Registrado', 'suspendido']])
            ->count();
        $this->stdout("Total contratos con estatus válidos ('activo', 'Creado', etc.): {$totalValidEstatus}\n");

        // Obtener contratos que necesitan cuotas generadas (incluyendo diferentes estatus válidos)
        $fechaActual = date('Y-m-d');
        $contratos = Contratos::find()
            ->where([
                'or',
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

        // Obtener la última cuota (pagada O pendiente) o la fecha de inicio del contrato
        $ultimaCuota = Cuotas::find()
            ->where(['contrato_id' => $contrato->id])
            ->orderBy(['fecha_vencimiento' => SORT_DESC])
            ->one();

        $fechaInicio = $ultimaCuota ?
            date('Y-m-d', strtotime($ultimaCuota->fecha_vencimiento . ' +1 month')) :
            $contrato->fecha_ini;
        $fechaActual = date('Y-m-d');

        // Si no hay cuotas atrasadas, salir
        if (strtotime($fechaInicio) >= strtotime($fechaActual)) {
            return 0;
        }

        $fechaActualObj = new \DateTime($fechaActual);
        $fechaInicioObj = new \DateTime($fechaInicio);
        $intervalo = $fechaInicioObj->diff($fechaActualObj);
        $mesesAtrasados = ($intervalo->y * 12) + $intervalo->m;

        if ($mesesAtrasados > 0) {
            $this->stdout("  Contrato #{$contrato->id}: Generando {$mesesAtrasados} cuotas atrasadas desde {$fechaInicio}...\n");

            for ($i = 0; $i < $mesesAtrasados; $i++) {
                $fechaVencimiento = date('Y-m-07', strtotime($fechaInicio . " +{$i} months"));
                $mesVencimiento = date('Y-m', strtotime($fechaVencimiento));
                $primerDiaMes = date('Y-m-01', strtotime($fechaVencimiento));
                $ultimoDiaMes = date('Y-m-t', strtotime($fechaVencimiento));

                // CHECK IF PAYMENT ALREADY EXISTS FOR THIS MONTH - CRITICAL MISSING CHECK
                $pagoExistente = Pagos::find()
                    ->alias('p')
                    ->innerJoin(['c' => Cuotas::tableName()], 'p.id = c.id_pago')
                    ->where(['p.user_id' => $contrato->user_id])
                    ->andWhere(['p.estatus' => 'Conciliado'])
                    ->andWhere(['>=', 'c.fecha_vencimiento', $primerDiaMes])
                    ->andWhere(['<=', 'c.fecha_vencimiento', $ultimoDiaMes])
                    ->exists();

                if ($pagoExistente) {
                    $this->stdout("    ✅ Skipping {$mesVencimiento} - payment already exists\n");
                    continue; // Skip this month, payment already made
                }

                // Check if cuota already exists for this month
                $existeCuota = Cuotas::find()
                    ->where(['contrato_id' => $contrato->id])
                    ->andWhere(['>=', 'fecha_vencimiento', $primerDiaMes])
                    ->andWhere(['<=', 'fecha_vencimiento', $ultimoDiaMes])
                    ->exists();

                if (!$existeCuota) {
                    // Create the cuota

                    // Create the cuota with rounded amount
                    $montoCuota = round($contrato->monto, 2); // ← ADD ROUND TO 2 DECIMALS

                    $cuota = new Cuotas([
                        'contrato_id' => $contrato->id,
                        'fecha_vencimiento' => $fechaVencimiento,
                        'monto_usd' => $montoCuota,  // ← USE ROUNDED VALUE
                        'monto' => $montoCuota,
                        'estatus' => 'pendiente',
                        'rate_usd_bs' => $this->obtenerTasaCambioActual(),
                    ]);

                    if ($cuota->save()) {
                        $cuotasGeneradas++;
                        $this->stdout("    ✅ Cuota atrasada generada: {$fechaVencimiento} - {$contrato->monto} USD\n");
                    } else {
                        $this->stderr("    ❌ Error generando cuota atrasada: " . print_r($cuota->errors, true) . "\n");
                    }
                } else {
                    $this->stdout("    ℹ️  Cuota ya existe para {$mesVencimiento}\n");
                }
            }
        }

        return $cuotasGeneradas;
    }


    /**
     * Genera la cuota del mes actual para un contrato.
     * NOTA: La lógica de prorrateo está deshabilitada - siempre se usa el monto completo.
     * 
     * @param Contratos $contrato
     * @return bool True si se generó la cuota
     */
    private function generarCuotaMesActual($contrato)
    {
        $fechaActual = date('Y-m-d');
        $mesActual = date('Y-m');

        // Calcular primer y último día del mes actual correctamente
        $primerDiaMes = date('Y-m-01');
        $ultimoDiaMes = date('Y-m-t');

        $this->stdout("  Verificando mes: {$mesActual} ({$primerDiaMes} to {$ultimoDiaMes})\n");

        // Verificar si existe CUALQUIER cuota (pagada o pendiente) para este mes
        $cuotaExistente = Cuotas::find()
            ->where(['contrato_id' => $contrato->id])
            ->andWhere(['>=', 'fecha_vencimiento', $primerDiaMes])
            ->andWhere(['<=', 'fecha_vencimiento', $ultimoDiaMes])
            ->exists();

        if ($cuotaExistente) {
            $this->stdout("  ⚠️  Cuota del mes {$mesActual} ya existe para contrato #{$contrato->id}\n");
            return false;
        }

        // ============================================================
        // ADDED: CHECK IF PAYMENT ALREADY EXISTS FOR THIS MONTH
        // ============================================================
        $pagoExistente = Pagos::find()
            ->alias('p')
            ->innerJoin(['c' => Cuotas::tableName()], 'p.id = c.id_pago')
            ->where(['p.user_id' => $contrato->user_id])
            ->andWhere(['p.estatus' => 'Conciliado'])
            ->andWhere(['>=', 'c.fecha_vencimiento', $primerDiaMes])
            ->andWhere(['<=', 'c.fecha_vencimiento', $ultimoDiaMes])
            ->exists();

        if ($pagoExistente) {
            $this->stdout("  ✅ Payment already exists for month {$mesActual}, skipping cuota generation\n");
            return false;
        }
        // ============================================================

        // Calcular fecha de vencimiento (día 7 del mes actual)
        $fechaVencimiento = new \DateTime();
        $fechaVencimiento->modify('first day of this month');
        $fechaVencimiento->modify('+6 days'); // Día 7 del mes
        $fechaVencimientoStr = $fechaVencimiento->format('Y-m-d');

        // VERIFICACIÓN ADICIONAL: Por si acaso
        $existeCuotaFecha = Cuotas::find()
            ->where([
                'contrato_id' => $contrato->id,
                'fecha_vencimiento' => $fechaVencimientoStr
            ])
            ->exists();

        if ($existeCuotaFecha) {
            $this->stdout("  ⚠️  Cuota ya existe para fecha {$fechaVencimientoStr} en contrato #{$contrato->id}\n");
            return false;
        }

        // Determinar si es cuota inicial o mensual
        $cuotasExistentesCount = Cuotas::find()->where(['contrato_id' => $contrato->id])->count();
        $esCuotaInicial = ($cuotasExistentesCount == 0);

        // ============================================================
        // LÓGICA DE PRORRATEO COMENTADA - USAR MONTO COMPLETO SIEMPRE
        // ============================================================

        // Calcular monto (USAR MONTO COMPLETO - PRORRATEO DESHABILITADO)
        $montoCuota = round($contrato->monto, 2); // ← ADD ROUND TO 2 DECIMALS

        /*
        // LÓGICA DE PRORRATEO DESHABILITADA - MANTENER PARA REFERENCIA FUTURA
        if ($esCuotaInicial) {
            $fechaIni = new \DateTime($contrato->fecha_ini);
            $diaMesIni = (int) $fechaIni->format('j');
            
            if ($diaMesIni > 7) {
                $montoCuota = $contrato->monto / 2;
                $this->stdout("    Monto prorrateado (mitad: {$montoCuota} USD) - fecha_ini después del día 7\n");
            } else {
                $this->stdout("    Monto completo ({$montoCuota} USD) - fecha_ini en primeros 7 días\n");
            }
        }
        */

        // Siempre usar monto completo - registrar esta decisión
        if ($esCuotaInicial) {
            $this->stdout("    Cuota inicial - Monto completo: {$montoCuota} USD (prorrateo deshabilitado)\n");
        } else {
            $this->stdout("    Cuota mensual - Monto: {$montoCuota} USD\n");
        }

        // Crear la cuota
        $cuota = new Cuotas([
            'contrato_id' => $contrato->id,
            'fecha_vencimiento' => $fechaVencimientoStr,
            'monto_usd' => $montoCuota,
            'monto' => $montoCuota,         // ALSO SET MONTO FIELD!
            'estatus' => 'pendiente',
            'rate_usd_bs' => $this->obtenerTasaCambioActual(),
        ]);

        if ($cuota->save()) {
            $tipo = $esCuotaInicial ? 'inicial' : 'mensual';
            $this->stdout("✅ Cuota {$tipo} generada para contrato #{$contrato->id} - Vencimiento: {$fechaVencimientoStr} - Monto: {$montoCuota} USD\n");
            return true;
        } else {
            $this->stderr("❌ Error al generar cuota para contrato #{$contrato->id}: " . print_r($cuota->errors, true) . "\n");
            return false;
        }
    }

    /**
     * Verifica y elimina cuotas duplicadas para el mismo mes.
     * Uso: `yii cuota/eliminar-duplicados`
     * 
     * @return int Código de salida
     */
    public function actionEliminarDuplicados()
    {
        $this->stdout("Buscando y eliminando cuotas duplicadas...\n");

        // Encontrar meses con múltiples cuotas pendientes por contrato
        $query = "
        SELECT contrato_id, 
               DATE_TRUNC('month', fecha_vencimiento) as mes,
               COUNT(*) as total
        FROM cuotas 
        WHERE estatus = 'pendiente'
        GROUP BY contrato_id, DATE_TRUNC('month', fecha_vencimiento)
        HAVING COUNT(*) > 1
    ";

        $duplicados = Yii::$app->db->createCommand($query)->queryAll();

        if (empty($duplicados)) {
            $this->stdout("✅ No hay cuotas duplicadas en el sistema.\n");
            return ExitCode::OK;
        }

        $this->stdout("Se encontraron " . count($duplicados) . " grupos de cuotas duplicadas:\n");
        $cuotasEliminadas = 0;

        foreach ($duplicados as $duplicado) {
            $contratoId = $duplicado['contrato_id'];
            $mes = $duplicado['mes'];
            $total = $duplicado['total'];

            $this->stdout("Contrato #{$contratoId}: {$total} cuotas para el mes " . date('Y-m', strtotime($mes)) . "\n");

            // Obtener todas las cuotas duplicadas para este contrato y mes
            $cuotasDuplicadas = Cuotas::find()
                ->where(['contrato_id' => $contratoId])
                ->andWhere(['estatus' => 'pendiente'])
                ->andWhere(['>=', 'fecha_vencimiento', date('Y-m-01', strtotime($mes))])
                ->andWhere(['<=', 'fecha_vencimiento', date('Y-m-t', strtotime($mes))])
                ->orderBy(['fecha_vencimiento' => SORT_ASC, 'id' => SORT_ASC])
                ->all();

            // Mantener la primera cuota, eliminar las demás
            $mantener = true;
            foreach ($cuotasDuplicadas as $cuota) {
                if ($mantener) {
                    $this->stdout("  ✅ Manteniendo cuota #{$cuota->id} ({$cuota->fecha_vencimiento})\n");
                    $mantener = false;
                } else {
                    $this->stdout("  🗑️ Eliminando cuota duplicada #{$cuota->id} ({$cuota->fecha_vencimiento})\n");
                    if ($cuota->delete()) {
                        $cuotasEliminadas++;
                    } else {
                        $this->stderr("  ❌ Error eliminando cuota #{$cuota->id}\n");
                    }
                }
            }
            $this->stdout("\n");
        }

        $this->stdout("✅ Proceso completado. Se eliminaron {$cuotasEliminadas} cuotas duplicadas.\n");
        return ExitCode::OK;
    }

    /**
     * Verifica cuotas duplicadas sin eliminarlas.
     * Uso: `yii cuota/verificar-duplicados`
     * 
     * @return int Código de salida
     */
    public function actionVerificarDuplicados()
    {
        $this->stdout("Verificando cuotas duplicadas...\n");

        $query = "
        SELECT c.id as contrato_id, 
               ud.nombres || ' ' || ud.apellidos as afiliado,
               DATE_TRUNC('month', cu.fecha_vencimiento) as mes,
               COUNT(*) as total_cuotas,
               STRING_AGG(cu.id::text || ' (' || cu.fecha_vencimiento || ')', ', ') as cuotas_info
        FROM cuotas cu
        JOIN contratos c ON cu.contrato_id = c.id
        JOIN user_datos ud ON c.user_id = ud.id
        WHERE cu.estatus = 'pendiente'
        GROUP BY c.id, ud.nombres, ud.apellidos, DATE_TRUNC('month', cu.fecha_vencimiento)
        HAVING COUNT(*) > 1
        ORDER BY total_cuotas DESC
    ";

        $duplicados = Yii::$app->db->createCommand($query)->queryAll();

        if (empty($duplicados)) {
            $this->stdout("✅ No hay cuotas duplicadas en el sistema.\n");
            return ExitCode::OK;
        }

        $this->stdout("❌ SE ENCONTRARON CUOTAS DUPLICADAS:\n\n");

        foreach ($duplicados as $duplicado) {
            $this->stdout("🔸 Afiliado: {$duplicado['afiliado']}\n");
            $this->stdout("   Contrato: #{$duplicado['contrato_id']}\n");
            $this->stdout("   Mes: " . date('Y-m', strtotime($duplicado['mes'])) . "\n");
            $this->stdout("   Cuotas duplicadas: {$duplicado['total_cuotas']}\n");
            $this->stdout("   IDs y fechas: {$duplicado['cuotas_info']}\n\n");
        }

        $this->stdout("💡 Ejecuta 'yii cuota/eliminar-duplicados' para limpiar las duplicadas.\n");

        return ExitCode::OK;
    }

    /**
     * Genera cuotas mensuales para todos los contratos activos (para ejecutar el día 1 de cada mes).
     * Uso: `yii cuota/generar-mensual`
     * 
     * @return int Código de salida
     */
    public function actionGenerarMensual()
    {
        $this->stdout("╔══════════════════════════════════════════════════════════╗\n");
        $this->stdout("║        GENERACIÓN DE CUOTAS MENSUALES - REPORTE          ║\n");
        $this->stdout("╚══════════════════════════════════════════════════════════╝\n\n");

        $fechaActual = date('Y-m-d');
        $mesActual = date('F Y');

        $this->stdout("📅 Fecha de ejecución: " . date('Y-m-d H:i:s') . "\n");
        $this->stdout("📋 Mes objetivo: {$mesActual} (1 al " . date('t') . ")\n");

        // Verificar que sea el día 1 del mes
        if (date('j') !== '1') {
            $this->stdout("\n⚠️  ⚠️  ⚠️  ADVERTENCIA ⚠️  ⚠️  ⚠️\n");
            $this->stdout("Este comando debería ejecutarse el día 1 de cada mes.\n");
            $this->stdout("Hoy es el día " . date('j') . " del mes.\n");
            $this->stdout("Continuando con la generación...\n");
            // No retornar error, solo advertir
        }

        $this->stdout("\n📊 BUSCANDO CONTRATOS ELEGIBLES...\n");
        $this->stdout(str_repeat("─", 60) . "\n");

        // Obtener contratos activos que ya iniciaron - INCLUIR SUSPENDIDOS
        $contratos = Contratos::find()
            ->where(['in', 'estatus', ['activo', 'Creado', 'Registrado', 'suspendido']])
            ->andWhere(['<=', 'fecha_ini', $fechaActual])
            ->orderBy(['estatus' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        $this->stdout("✅ Encontrados " . count($contratos) . " contratos para procesar\n\n");

        // Contadores por estatus
        $estatusCounts = [
            'activo' => 0,
            'Creado' => 0,
            'Registrado' => 0,
            'suspendido' => 0,
            'otros' => 0
        ];

        foreach ($contratos as $contrato) {
            $estatus = strtolower($contrato->estatus);
            if (isset($estatusCounts[$estatus])) {
                $estatusCounts[$estatus]++;
            } else {
                $estatusCounts['otros']++;
            }
        }

        $this->stdout("📈 DISTRIBUCIÓN POR ESTATUS:\n");
        foreach ($estatusCounts as $estatus => $count) {
            if ($count > 0) {
                $icon = $estatus === 'suspendido' ? '⏸️' : ($estatus === 'activo' ? '✅' : '📝');
                $this->stdout("  {$icon} " . ucfirst($estatus) . ": {$count} contratos\n");
            }
        }

        $this->stdout("\n");
        $this->stdout("╔══════════════════════════════════════════════════════════╗\n");
        $this->stdout("║                    PROCESANDO CONTRATOS                  ║\n");
        $this->stdout("╚══════════════════════════════════════════════════════════╝\n\n");

        $cuotasGeneradas = 0;
        $cuotasExistentes = 0;
        $contratosConPagoExistente = 0;
        $contratosProcesados = 0;
        $errores = 0;

        // Tabla simplificada
        $this->stdout("┌──────┬─────────────────┬─────────┬────────────┐\n");
        $this->stdout("│  ID  │    Usuario      │ Estatus │  Resultado │\n");
        $this->stdout("├──────┼─────────────────┼─────────┼────────────┤\n");

        $resultados = [];

        foreach ($contratos as $contrato) {
            $contratosProcesados++;

            // Obtener nombre de usuario
            $nombreUsuario = "N/A";
            $userDatos = UserDatos::findOne($contrato->user_id);
            if ($userDatos) {
                $nombreUsuario = substr($userDatos->nombres . ' ' . $userDatos->apellidos, 0, 15);
                if (strlen($userDatos->nombres . ' ' . $userDatos->apellidos) > 15) {
                    $nombreUsuario .= "..";
                }
            }

            // Verificar si ya existe cuota para este mes
            $primerDiaMes = date('Y-m-01');
            $ultimoDiaMes = date('Y-m-t');

            $cuotaExistente = Cuotas::find()
                ->where(['contrato_id' => $contrato->id])
                ->andWhere(['>=', 'fecha_vencimiento', $primerDiaMes])
                ->andWhere(['<=', 'fecha_vencimiento', $ultimoDiaMes])
                ->exists();

            if ($cuotaExistente) {
                $cuotasExistentes++;
                $resultado = "⏭️  Ya existe";
                $icono = "⏭️";
            } else {
                // Verificar si ya hay pago para este mes
                $pagoExistente = Pagos::find()
                    ->alias('p')
                    ->innerJoin(['c' => Cuotas::tableName()], 'p.id = c.id_pago')
                    ->where(['p.user_id' => $contrato->user_id])
                    ->andWhere(['p.estatus' => 'Conciliado'])
                    ->andWhere(['>=', 'c.fecha_vencimiento', $primerDiaMes])
                    ->andWhere(['<=', 'c.fecha_vencimiento', $ultimoDiaMes])
                    ->exists();

                if ($pagoExistente) {
                    $contratosConPagoExistente++;
                    $resultado = "✅  Pagado";
                    $icono = "✅";
                } else {
                    // Generar cuota
                    $cuotaGenerada = $this->generarCuotaMesActual($contrato);
                    if ($cuotaGenerada) {
                        $cuotasGeneradas++;
                        $resultado = "🆕  Generada";
                        $icono = "🆕";
                    } else {
                        $errores++;
                        $resultado = "❌  Error";
                        $icono = "❌";
                    }
                }
            }

            // Mostrar línea simplificada
            $estatusCorto = substr($contrato->estatus, 0, 8);
            $this->stdout(sprintf(
                "│ #%-4s │ %-15s │ %-7s │ %-10s │\n",
                $contrato->id,
                $nombreUsuario,
                $estatusCorto,
                $resultado
            ));

            $resultados[] = [
                'id' => $contrato->id,
                'usuario' => $nombreUsuario,
                'estatus' => $contrato->estatus,
                'monto' => $contrato->monto ?: '0.00',
                'resultado' => $icono,
                'detalle' => $resultado
            ];
        }

        $this->stdout("└──────┴─────────────────┴─────────┴────────────┘\n");

        $this->stdout("\n📋 RESUMEN DETALLADO POR CONTRATO:\n");
        $this->stdout(str_repeat("─", 80) . "\n");
        $this->stdout(sprintf(
            " %-6s | %-25s | %-12s | %-8s | %-12s | %s\n",
            "ID",
            "Usuario",
            "Estatus",
            "Monto",
            "Resultado",
            "Detalle"
        ));
        $this->stdout(str_repeat("─", 80) . "\n");

        foreach ($resultados as $row) {
            $this->stdout(sprintf(
                " %-6s | %-25s | %-12s | $%-7s | %-12s | %s\n",
                $row['id'],
                substr($row['usuario'], 0, 25),
                $row['estatus'],
                $row['monto'],
                $row['resultado'],
                $row['detalle']
            ));
        }

        $this->stdout(str_repeat("─", 80) . "\n\n");

        // Resumen estadístico
        $this->stdout("📊 RESUMEN ESTADÍSTICO:\n");
        $this->stdout(str_repeat("═", 40) . "\n");

        $this->stdout(sprintf(" %-30s: %d\n", "Total contratos procesados", $contratosProcesados));
        $this->stdout(sprintf(" %-30s: %d\n", "Nuevas cuotas generadas", $cuotasGeneradas));
        $this->stdout(sprintf(" %-30s: %d\n", "Cuotas ya existentes", $cuotasExistentes));
        $this->stdout(sprintf(" %-30s: %d\n", "Contratos con pago previo", $contratosConPagoExistente));
        $this->stdout(sprintf(" %-30s: %d\n", "Errores encontrados", $errores));

        $porcentajeGenerado = $contratosProcesados > 0 ?
            round(($cuotasGeneradas / $contratosProcesados) * 100, 1) : 0;

        $this->stdout(sprintf(" %-30s: %.1f%%\n", "Tasa de generación", $porcentajeGenerado));

        $this->stdout("\n💰 IMPACTO FINANCIERO ESTIMADO:\n");
        $this->stdout(str_repeat("═", 40) . "\n");

        $ingresoEstimado = 0;
        foreach ($resultados as $row) {
            if ($row['resultado'] === '🆕 GENERADA') {
                $ingresoEstimado += (float)$row['monto'];
            }
        }

        $this->stdout(sprintf(" %-30s: $%.2f USD\n", "Ingreso potencial nuevo", $ingresoEstimado));

        // Mostrar los primeros 5 contratos con nuevas cuotas
        $nuevasCuotas = array_filter($resultados, function ($row) {
            return $row['resultado'] === '🆕 GENERADA';
        });

        if (!empty($nuevasCuotas)) {
            $this->stdout("\n🎯 CONTRATOS CON NUEVAS CUOTAS (primeros 5):\n");
            $this->stdout(str_repeat("─", 60) . "\n");

            $contador = 0;
            foreach ($nuevasCuotas as $row) {
                if ($contador < 5) {
                    $this->stdout(sprintf(
                        " • Contrato #%d: %s - $%.2f USD\n",
                        $row['id'],
                        $row['usuario'],
                        $row['monto']
                    ));
                    $contador++;
                }
            }

            if (count($nuevasCuotas) > 5) {
                $this->stdout(sprintf(" ... y %d contratos más\n", count($nuevasCuotas) - 5));
            }
        }

        // Mostrar contratos con errores
        $contratosConError = array_filter($resultados, function ($row) {
            return $row['resultado'] === '❌ ERROR';
        });

        if (!empty($contratosConError)) {
            $this->stdout("\n⚠️  CONTRATOS CON ERRORES:\n");
            $this->stdout(str_repeat("─", 60) . "\n");

            foreach ($contratosConError as $row) {
                $this->stdout(sprintf(" • Contrato #%d: %s\n", $row['id'], $row['usuario']));
            }
        }

        $this->stdout("\n" . str_repeat("═", 60) . "\n");

        if ($cuotasGeneradas > 0) {
            $this->stdout("✅ GENERACIÓN COMPLETADA CON ÉXITO\n");
            $this->stdout(sprintf(
                "   Se generaron %d nuevas cuotas para %d contratos\n",
                $cuotasGeneradas,
                count($nuevasCuotas)
            ));
        } elseif ($cuotasExistentes > 0) {
            $this->stdout("ℹ️  GENERACIÓN COMPLETADA - SIN CAMBIOS\n");
            $this->stdout("   Todas las cuotas ya estaban generadas o pagadas\n");
        } else {
            $this->stdout("⚠️  GENERACIÓN COMPLETADA - SIN RESULTADOS\n");
            $this->stdout("   No se generaron nuevas cuotas\n");
        }

        $this->stdout(str_repeat("═", 60) . "\n");

        // Sugerencias basadas en los resultados
        if ($cuotasExistentes > 0) {
            $this->stdout("\n💡 SUGERENCIAS:\n");
            if ($cuotasExistentes > 0) {
                $this->stdout(" • Verificar si las cuotas existentes están correctamente vinculadas\n");
            }
            if ($errores > 0) {
                $this->stdout(" • Revisar los contratos con errores para identificar problemas\n");
            }
            if ($contratosConPagoExistente > 0) {
                $this->stdout(" • Confirmar que los pagos están correctamente aplicados\n");
            }
        }

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

    /**
     * Repara la relación entre pagos y cuotas - Versión Web CORREGIDA
     * Compara los campos correctos: monto (cuotas) vs monto_pagado (pagos)
     */
    public function actionRepararRelacionPagos()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {
            $output = "Iniciando reparación CORREGIDA de relaciones pago-cuota...\n\n";

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
}
