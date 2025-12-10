<?php

namespace app\components;

use yii\db\Query;
use yii\db\Expression;

class FinancialHelper
{
    /**
     * Calculates pipeline health metrics
     * @return array
     */
    public static function getPipelineHealth()
    {
        $currentDate = date('Y-m-d');
        $startDate90 = date('Y-m-d', strtotime('-90 days'));
        $startDate30 = date('Y-m-d', strtotime('-30 days'));
        $startDate60_90 = date('Y-m-d', strtotime('-60 days'));
        $endDate60_90 = date('Y-m-d', strtotime('-31 days'));

        // 1. Total Active Clients (last 90 days)
        $totalActiveClients = (new Query())
            ->select(['COUNT(DISTINCT user_id)'])
            ->from('pagos')
            ->where(['>=', 'fecha_pago', $startDate90])
            ->andWhere(['<=', 'fecha_pago', $currentDate]) // FIXED: fecha_pago
            ->andWhere(['estatus' => 'Aprobado'])
            ->andWhere(['>', 'monto_pagado', 0])
            ->scalar();

        // 2. New Acquisitions (last 30 days) - SIMPLIFIED VERSION
        $newAcquisitions = (new Query())
            ->select(['COUNT(DISTINCT user_id)'])
            ->from('pagos')
            ->where(['>=', 'fecha_pago', $startDate30])
            ->andWhere(['<=', 'fecha_pago', $currentDate]) // FIXED: fecha_pago
            ->andWhere(['estatus' => 'Aprobado'])
            ->andWhere(['>', 'monto_pagado', 0])
            ->scalar();

        // 3. Renewals (last 30 days - clients who also had payments in previous 60 days)
        $renewals = (new Query())
            ->select(['COUNT(DISTINCT p1.user_id)'])
            ->from(['p1' => 'pagos'])
            ->where(['>=', 'p1.fecha_pago', $startDate30])
            ->andWhere(['<=', 'p1.fecha_pago', $currentDate]) // FIXED: fecha_pago
            ->andWhere(['p1.estatus' => 'Aprobado'])
            ->andWhere(['>', 'p1.monto_pagado', 0])
            ->andWhere(['EXISTS', (new Query())
                ->select([new Expression('1')])
                ->from('pagos p2')
                ->where('p2.user_id = p1.user_id')
                ->andWhere(['>=', 'p2.fecha_pago', $startDate60_90])
                ->andWhere(['<', 'p2.fecha_pago', $startDate30]) // FIXED: fecha_pago
                ->andWhere(['p2.estatus' => 'Aprobado'])
                ->andWhere(['>', 'p2.monto_pagado', 0])
            ])
            ->scalar();

        // 4. Renewal Rate Calculation
        $totalRecentTransactions = $newAcquisitions + $renewals;
        $renewalRate = $totalRecentTransactions > 0 ? round(($renewals / $totalRecentTransactions) * 100, 1) : 0;

        // 5. Revenue calculations
        $currentPeriodRevenue = (new Query())
            ->select(['COALESCE(SUM(monto_pagado), 0)'])
            ->from('pagos')
            ->where(['>=', 'fecha_pago', $startDate30])
            ->andWhere(['<=', 'fecha_pago', $currentDate]) // FIXED: fecha_pago
            ->andWhere(['estatus' => 'Aprobado'])
            ->andWhere(['>', 'monto_pagado', 0])
            ->scalar();

        $previousPeriodRevenue = (new Query())
            ->select(['COALESCE(SUM(monto_pagado), 0)'])
            ->from('pagos')
            ->where(['>=', 'fecha_pago', $startDate60_90])
            ->andWhere(['<=', 'fecha_pago', $endDate60_90]) // FIXED: fecha_pago
            ->andWhere(['estatus' => 'Aprobado'])
            ->andWhere(['>', 'monto_pagado', 0])
            ->scalar();

        // Calculate revenue trend percentage
        if ($previousPeriodRevenue > 0) {
            $revenueTrend = round((($currentPeriodRevenue - $previousPeriodRevenue) / $previousPeriodRevenue) * 100, 1);
        } else {
            $revenueTrend = $currentPeriodRevenue > 0 ? 100 : 0;
        }

        // 6. Additional metrics for deeper insights
        $avgRevenuePerClient = $totalActiveClients > 0 ? round($currentPeriodRevenue / $totalActiveClients, 2) : 0;

        // Client activity distribution - FIXED ALL fecha_pagado TYPOS
        $highActivityClients = (new Query())
            ->select(['COUNT(DISTINCT user_id)'])
            ->from('pagos')
            ->where(['>=', 'fecha_pago', $startDate90])
            ->andWhere(['<=', 'fecha_pago', $currentDate]) // FIXED: fecha_pago
            ->andWhere(['estatus' => 'Aprobado'])
            ->andWhere(['>', 'monto_pagado', 0])
            ->groupBy('user_id')
            ->having(['>=', 'COUNT(*)', 3])
            ->count();

        $mediumActivityClients = (new Query())
            ->select(['COUNT(DISTINCT user_id)'])
            ->from('pagos')
            ->where(['>=', 'fecha_pago', $startDate90])
            ->andWhere(['<=', 'fecha_pago', $currentDate]) // FIXED: fecha_pago
            ->andWhere(['estatus' => 'Aprobado'])
            ->andWhere(['>', 'monto_pagado', 0])
            ->groupBy('user_id')
            ->having(['COUNT(*)' => 2])
            ->count();

        $lowActivityClients = (new Query())
            ->select(['COUNT(DISTINCT user_id)'])
            ->from('pagos')
            ->where(['>=', 'fecha_pago', $startDate90])
            ->andWhere(['<=', 'fecha_pago', $currentDate]) // FIXED: fecha_pago
            ->andWhere(['estatus' => 'Aprobado'])
            ->andWhere(['>', 'monto_pagado', 0])
            ->groupBy('user_id')
            ->having(['COUNT(*)' => 1])
            ->count();

        return [
            'total_active_clients' => (int)$totalActiveClients,
            'new_acquisitions' => (int)$newAcquisitions,
            'renewals' => (int)$renewals,
            'renewal_rate' => $renewalRate,
            'revenue_trend' => $revenueTrend,
            'current_period_revenue' => round($currentPeriodRevenue, 2),
            'previous_period_revenue' => round($previousPeriodRevenue, 2),
            'avg_revenue_per_client' => $avgRevenuePerClient,
            'client_activity_distribution' => [
                'high' => $highActivityClients,
                'medium' => $mediumActivityClients,
                'low' => $lowActivityClients,
            ],
            'date_ranges' => [
                'current_period' => $startDate30 . ' to ' . $currentDate,
                'previous_period' => $startDate60_90 . ' to ' . $endDate60_90,
                'active_clients_period' => $startDate90 . ' to ' . $currentDate,
            ]
        ];
    }

    /**
     * Calculates and returns the earnings for all agencies.
     * @return array
     */
    public static function getAgencyEarnings()
    {
        $agencies = (new Query())
            ->select(['id', 'nom', 'por_agente'])
            ->from('agente')
            ->all();

        $earnings = [];

        foreach ($agencies as $agency) {
            // Get total payments for agency
            $totalPayments = (new Query())
                ->select(['COALESCE(SUM(p.monto_pagado), 0)'])
                ->from('agente_fuerza af')
                ->innerJoin('user_datos ud', 'af.id = ud.asesor_id') 
                ->innerJoin('pagos p', 'ud.id = p.user_id') 
                ->where(['af.agente_id' => $agency['id']])
                ->scalar();

            // Get total clients for agency
            $totalClients = (new Query())
                ->select(['COUNT(DISTINCT ud.id)'])
                ->from('agente_fuerza af')
                ->innerJoin('user_datos ud', 'af.id = ud.asesor_id')
                ->where(['af.agente_id' => $agency['id']])
                ->scalar();

            $commissionPercentage = $agency['por_agente'] / 100;
            $agencyCommission = $totalPayments * $commissionPercentage;

            $earnings[] = [
                'id' => $agency['id'],
                'nombre' => $agency['nom'],
                'total_ventas' => (float)$totalPayments,
                'porcentaje_comision' => round($commissionPercentage, 4),
                'porcentaje_comision_display' => round($agency['por_agente'], 2),
                'comision_agencia' => round($agencyCommission, 2),
                'total_clients' => (int)$totalClients,
            ];
        }

        return $earnings;
    }

    /**
     * Calculates and returns the earnings for salesmen within a given agency.
     * @param int $agencyId
     * @return array
     */
    public static function getSalesmanEarnings($agencyId)
    {
        $agency = (new Query())
            ->select(['por_venta'])
            ->from('agente')
            ->where(['id' => $agencyId])
            ->one();

        if (!$agency) {
            return [];
        }

        $salesmen = (new Query())
            ->select([
                'af.id',
                'af.idusuario',
                new Expression("COALESCE(CONCAT(ud.nombres, ' ', ud.apellidos), 'Nombre no encontrado') AS nombre")
            ])
            ->from('agente_fuerza af')
            ->leftJoin('user_datos ud', 'af.idusuario = ud.id') 
            ->where(['af.agente_id' => $agencyId])
            ->all();

        $earnings = [];

        foreach ($salesmen as $salesman) {
            // Get total sales for salesman
            $totalSales = (new Query())
                ->select(['COALESCE(SUM(p.monto_pagado), 0)'])
                ->from('user_datos ud')
                ->innerJoin('pagos p', 'ud.id = p.user_id')
                ->where(['ud.asesor_id' => $salesman['id']])
                ->scalar();
            
            // Get total clients for salesman
            $totalClients = (new Query())
                ->select(['COUNT(DISTINCT ud.id)'])
                ->from('user_datos ud')
                ->where(['ud.asesor_id' => $salesman['id']])
                ->scalar();

            $commissionPercentage = $agency['por_venta'] / 100;
            $salesmanCommission = $totalSales * $commissionPercentage;
            
            $commissionEfficiency = $salesmanCommission > 0 ? $totalSales / $salesmanCommission : 0;
            $quarterlyData = self::getSalesmanQuarterlySales($salesman['id']);

            $earnings[] = [
                'id' => $salesman['idusuario'],
                'nombre' => $salesman['nombre'],
                'total_ventas' => (float)$totalSales,
                'porcentaje_comision' => round($commissionPercentage, 4),
                'porcentaje_comision_display' => round($agency['por_venta'], 2),
                'comision_asesor' => round($salesmanCommission, 2),
                'total_clients' => (int)$totalClients,
                'commission_efficiency' => round($commissionEfficiency, 2),
                'current_quarter_sales' => $quarterlyData['current_sales'],
                'previous_quarter_sales' => $quarterlyData['previous_sales'],
                'quarterly_growth' => round($quarterlyData['growth'], 2),
                'current_quarter' => $quarterlyData['current_quarter'],
                'previous_quarter' => $quarterlyData['previous_quarter'],
            ];
        }

        return $earnings;
    }

    /**
     * Debug method to get all payments with agency relationships
     * @return array
     */
    public static function getAllPaymentsWithAgencies()
    {
        return (new Query())
            ->select([
                'p.id as payment_id',
                'p.user_id',
                'p.monto_pagado',
                'ud.asesor_id',
                'af.id as salesman_id',
                'af.agente_id',
                'a.nom as agency_name',
                'a.por_agente as agency_commission',
                new Expression("COUNT(DISTINCT ud.id) as total_clients")
            ])
            ->from('pagos p')
            ->innerJoin('user_datos ud', 'p.user_id = ud.id')
            ->innerJoin('agente_fuerza af', 'ud.asesor_id = af.id')
            ->innerJoin('agente a', 'af.agente_id = a.id')
            ->groupBy(['p.id', 'p.user_id', 'p.monto_pagado', 'ud.asesor_id', 'af.id', 'af.agente_id', 'a.nom', 'a.por_agente'])
            ->all();
    }

    /**
     * Debug method to get payment relationships for a specific user
     * @param int $userId
     * @return array
     */
    public static function debugPaymentRelationships($userId)
    {
        return (new Query())
            ->select([
                'p.id as payment_id',
                'p.user_id',
                'p.monto_pagado',
                'ud.asesor_id',
                'af.id as salesman_id',
                'af.agente_id',
                'a.nom as agency_name',
                'a.por_agente as agency_commission',
                new Expression("COUNT(DISTINCT ud.id) as total_clients")
            ])
            ->from('pagos p')
            ->innerJoin('user_datos ud', 'p.user_id = ud.id')
            ->innerJoin('agente_fuerza af', 'ud.asesor_id = af.id')
            ->innerJoin('agente a', 'af.agente_id = a.id')
            ->where(['p.user_id' => $userId])
            ->groupBy(['p.id', 'p.user_id', 'p.monto_pagado', 'ud.asesor_id', 'af.id', 'af.agente_id', 'a.nom', 'a.por_agente'])
            ->all();
    }

    /**
     * Gets quarterly sales data for salesmen (PostgreSQL compatible)
     * @param int $salesmanId
     * @param string $currentQuarter Optional: specific quarter to analyze
     * @return array
     */
    public static function getSalesmanQuarterlySales($salesmanId, $currentQuarter = null)
    {
        if (!$currentQuarter) {
            $currentYear = date('Y');
            $currentQuarterNum = ceil(date('n') / 3);
            $currentQuarter = $currentYear . '-Q' . $currentQuarterNum;
        } else {
            list($currentYear, $currentQuarterNum) = explode('-Q', $currentQuarter);
            $currentQuarterNum = (int)$currentQuarterNum;
        }
        
        // Calculate previous quarter
        $prevQuarterNum = $currentQuarterNum - 1;
        $prevYear = $currentYear;
        
        if ($prevQuarterNum === 0) {
            $prevQuarterNum = 4;
            $prevYear = $currentYear - 1;
        }
        
        $prevQuarter = $prevYear . '-Q' . $prevQuarterNum;
        
        // PostgreSQL date functions
        // Get current quarter sales
        $currentQuarterSales = (new Query())
            ->select(['COALESCE(SUM(p.monto_pagado), 0)'])
            ->from('user_datos ud')
            ->innerJoin('pagos p', 'ud.id = p.user_id')
            ->where(['ud.asesor_id' => $salesmanId])
            ->andWhere([
                'EXTRACT(YEAR FROM p.fecha_pago)' => $currentYear,
                'EXTRACT(QUARTER FROM p.fecha_pago)' => $currentQuarterNum
            ])
            ->scalar();

        // Get previous quarter sales
        $previousQuarterSales = (new Query())
            ->select(['COALESCE(SUM(p.monto_pagado), 0)'])
            ->from('user_datos ud')
            ->innerJoin('pagos p', 'ud.id = p.user_id')
            ->where(['ud.asesor_id' => $salesmanId])
            ->andWhere([
                'EXTRACT(YEAR FROM p.fecha_pago)' => $prevYear,
                'EXTRACT(QUARTER FROM p.fecha_pago)' => $prevQuarterNum
            ])
            ->scalar();

        return [
            'current_quarter' => $currentQuarter,
            'current_sales' => (float)$currentQuarterSales,
            'previous_quarter' => $prevQuarter,
            'previous_sales' => (float)$previousQuarterSales,
            'growth' => $previousQuarterSales > 0 ? 
                (($currentQuarterSales - $previousQuarterSales) / $previousQuarterSales) * 100 : 
                ($currentQuarterSales > 0 ? 100 : 0)
        ];
    }

    /**
     * Debug method to check pipeline calculations
     */
    public static function debugPipelineCalculations()
    {
        $currentDate = date('Y-m-d');
        $startDate90 = date('Y-m-d', strtotime('-90 days'));
        $startDate30 = date('Y-m-d', strtotime('-30 days'));
        $startDate60_90 = date('Y-m-d', strtotime('-60 days'));
        $endDate60_90 = date('Y-m-d', strtotime('-31 days'));
        
        echo "<h3>Debug: Payment Counts by Date Range</h3>";
        echo "<p>Current Date: $currentDate</p>";
        echo "<p>Last 90 Days: $startDate90 to $currentDate</p>";
        echo "<p>Last 30 Days: $startDate30 to $currentDate</p>";
        echo "<p>Previous Period: $startDate60_90 to $endDate60_90</p>";
        
        // Check payments in last 90 days
        $payments90 = (new Query())
            ->select(['COUNT(*) as count', 'SUM(monto_pagado) as total'])
            ->from('pagos')
            ->where(['>=', 'fecha_pago', $startDate90])
            ->andWhere(['<=', 'fecha_pago', $currentDate])
            ->andWhere(['estatus' => 'Aprobado'])
            ->andWhere(['>', 'monto_pagado', 0])
            ->one();
            
        echo "<p>Last 90 Days: " . $payments90['count'] . " payments, Total: $" . $payments90['total'] . "</p>";
        
        // Check payments in last 30 days
        $payments30 = (new Query())
            ->select(['COUNT(*) as count', 'SUM(monto_pagado) as total'])
            ->from('pagos')
            ->where(['>=', 'fecha_pago', $startDate30])
            ->andWhere(['<=', 'fecha_pago', $currentDate])
            ->andWhere(['estatus' => 'Aprobado'])
            ->andWhere(['>', 'monto_pagado', 0])
            ->one();
            
        echo "<p>Last 30 Days: " . $payments30['count'] . " payments, Total: $" . $payments30['total'] . "</p>";
        
        // Check payments in previous period
        $paymentsPrev = (new Query())
            ->select(['COUNT(*) as count', 'SUM(monto_pagado) as total'])
            ->from('pagos')
            ->where(['>=', 'fecha_pago', $startDate60_90])
            ->andWhere(['<=', 'fecha_pago', $endDate60_90])
            ->andWhere(['estatus' => 'Aprobado'])
            ->andWhere(['>', 'monto_pagado', 0])
            ->one();
            
        echo "<p>Previous Period: " . $paymentsPrev['count'] . " payments, Total: $" . $paymentsPrev['total'] . "</p>";
        
        // Show sample payments from last 30 days
        $samplePayments = (new Query())
            ->select(['user_id', 'fecha_pago', 'monto_pagado', 'estatus'])
            ->from('pagos')
            ->where(['>=', 'fecha_pago', $startDate30])
            ->andWhere(['<=', 'fecha_pago', $currentDate])
            ->andWhere(['estatus' => 'Aprobado'])
            ->andWhere(['>', 'monto_pagado', 0])
            ->limit(10)
            ->all();
            
        echo "<h4>Sample Recent Payments:</h4>";
        echo "<pre>";
        print_r($samplePayments);
        echo "</pre>";
    }
}