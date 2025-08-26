<?php
namespace app\components;

use Yii;
use yii\base\Component;
use app\models\SisSiniestro;
use app\models\Planes;

class SaldoHelper extends Component
{
    /**
     * Calcula el saldo disponible de un afiliado
     * 
     * @param int $afiliadoId ID del afiliado
     * @param int $planId ID del plan (opcional, si no se proporciona se obtiene del afiliado)
     * @return array Arreglo con los datos del cálculo
     */
    public static function calcularSaldoDisponible($afiliadoId, $planId = null)
    {
        // Obtener el plan si no se proporciona
        if ($planId === null) {
            $afiliado = \app\models\UserDatos::findOne($afiliadoId);
            $planId = $afiliado ? $afiliado->plan_id : null;
        }
        
        // Obtener información del plan
        $plan = Planes::findOne($planId);
        $precioPlan = $plan ? $plan->precio : 0;
        $coberturaPlan = $plan ? $plan->precio : 0;
        
        // Calcular la sumatoria de siniestros del afiliado
        $sumatoriaSiniestros = SisSiniestro::find()
            ->where(['iduser' => $afiliadoId])
            ->andWhere(['not', ['costo_total' => null]])
            ->sum('costo_total') ?? 0;
        
        // Calcular saldo disponible (usando cobertura si existe, sino precio del plan)
        $saldoDisponible = ($coberturaPlan > 0 ? $coberturaPlan : $precioPlan) - $sumatoriaSiniestros;
        
        // Calcular porcentaje consumido
        $porcentajeConsumido = 0;
        $baseCalculo = $coberturaPlan > 0 ? $coberturaPlan : $precioPlan;
        if ($baseCalculo > 0) {
            $porcentajeConsumido = round(($sumatoriaSiniestros / $baseCalculo) * 100);
        }
        
        return [
            'precio_plan' => $precioPlan,
            'cobertura_plan' => $coberturaPlan,
            'sumatoria_siniestros' => $sumatoriaSiniestros,
            'saldo_disponible' => $saldoDisponible,
            'porcentaje_consumido' => $porcentajeConsumido,
            'base_calculo' => $baseCalculo
        ];
    }
    
    /**
     * Obtiene el historial de siniestros de un afiliado
     * 
     * @param int $afiliadoId ID del afiliado
     * @return array Lista de siniestros
     */
    public static function obtenerHistorialSiniestros($afiliadoId)
    {
        return SisSiniestro::find()
            ->where(['iduser' => $afiliadoId])
            ->andWhere(['not', ['costo_total' => null]])
            ->orderBy(['fecha' => SORT_DESC])
            ->all();
    }
}
