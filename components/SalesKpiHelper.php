<?php

namespace app\components;

use yii\db\Query;
use yii\db\Expression;

class SalesKpiHelper
{
    /**
     * Agent Performance Scorecard for Sales Managers
     */
    public static function getAgentPerformanceScorecard($agencyId = null, $timeframe = 'month')
    {
        $query = (new Query())
        ->select([
            'af.idusuario as agent_id',
            new Expression("CONCAT(ud.nombres, ' ', ud.apellidos) AS agent_name"),
            'a.nom as agency_name',
            'COUNT(DISTINCT ud_affiliate.id) as total_clients',
            'COUNT(DISTINCT p.id) as total_policies',
            'COALESCE(SUM(p.monto_pagado), 0) as total_revenue',
            'COALESCE(AVG(p.monto_pagado), 0) as avg_policy_value',
            'COUNT(DISTINCT CASE WHEN p.fecha_pago >= CURRENT_DATE - INTERVAL \'30 days\' THEN p.id END) as recent_policies'
        ])
        ->from('agente_fuerza af')
        ->innerJoin('user_datos ud', 'af.idusuario = ud.id')
        ->innerJoin('agente a', 'af.agente_id = a.id')
        ->leftJoin('user_datos ud_affiliate', 'af.idusuario = ud_affiliate.asesor_id')
        ->leftJoin('pagos p', 'ud_affiliate.id = p.user_id')
        ->groupBy('af.idusuario, ud.nombres, ud.apellidos, a.nom, a.id');

        if ($agencyId) {
            $query->where(['af.agente_id' => $agencyId]);
        }

        // Apply timeframe filter
        switch ($timeframe) {
            case 'week':
                $query->andWhere(['>=', 'p.fecha_pago', new Expression('CURRENT_DATE - INTERVAL \'7 days\'')]);
                break;
            case 'month':
                $query->andWhere(['>=', 'p.fecha_pago', new Expression('CURRENT_DATE - INTERVAL \'30 days\'')]);
                break;
            case 'quarter':
                $query->andWhere(['>=', 'p.fecha_pago', new Expression('CURRENT_DATE - INTERVAL \'90 days\'')]);
                break;
        }

        $agents = $query->all();

        // Calculate performance metrics
        foreach ($agents as &$agent) {
            $agent['performance_score'] = self::calculatePerformanceScore($agent);
            $agent['revenue_per_client'] = $agent['total_clients'] > 0 ? 
                $agent['total_revenue'] / $agent['total_clients'] : 0;
            $agent['policies_per_client'] = $agent['total_clients'] > 0 ? 
                $agent['total_policies'] / $agent['total_clients'] : 0;
            
            // Format numbers for better display
            $agent['total_revenue_formatted'] = '$' . number_format($agent['total_revenue'], 2);
            $agent['avg_policy_value_formatted'] = '$' . number_format($agent['avg_policy_value'], 2);
            $agent['revenue_per_client_formatted'] = '$' . number_format($agent['revenue_per_client'], 2);
        }

        return $agents;
    }

    /**
     * Calculate overall performance score (0-100)
     */
    private static function calculatePerformanceScore($agentData)
    {
        $score = 0;
        
        // ADJUSTED: More realistic thresholds for payments system
        // Revenue weight: 40% - $500 for full score instead of $10,000
        $revenueScore = min(($agentData['total_revenue'] / 500) * 100, 100) * 0.4;
        
        // Client count weight: 30% - 5 clients for full score instead of 50
        $clientScore = min(($agentData['total_clients'] / 5) * 100, 100) * 0.3;
        
        // Payment activity weight: 30% - 3 recent payments for full score instead of 10
        $activityScore = min(($agentData['recent_policies'] / 3) * 100, 100) * 0.3;
        
        return round($revenueScore + $clientScore + $activityScore, 1);
    }

    /**
     * Sales Pipeline Health Dashboard
     */
    public static function getSalesPipelineHealth($agencyId = null)
    {
        $pipeline = [
            'new_acquisitions' => (new Query())
                ->from('pagos p')
                ->innerJoin('user_datos ud', 'p.user_id = ud.id')
                ->innerJoin('agente_fuerza af', 'ud.asesor_id = af.idusuario')
                ->where(['>=', 'p.fecha_pago', new Expression('CURRENT_DATE - INTERVAL \'30 days\'')])
                ->andWhere(['NOT EXISTS', (new Query())
                    ->select(new Expression('1')) 
                    ->from('pagos p2')
                    ->where('p2.user_id = p.user_id')
                    ->andWhere(['<', 'p2.fecha_pago', new Expression('p.fecha_pago - INTERVAL \'30 days\'')])
                ])
                ->andFilterWhere(['af.agente_id' => $agencyId]) // FIXED: Added quotes
                ->count(),
            
            'renewals' => (new Query())
                ->from('pagos p')
                ->innerJoin('user_datos ud', 'p.user_id = ud.id')
                ->innerJoin('agente_fuerza af', 'ud.asesor_id = af.idusuario')
                ->where(['>=', 'p.fecha_pago', new Expression('CURRENT_DATE - INTERVAL \'30 days\'')])
                ->andWhere(['EXISTS', (new Query())
                    ->select(new Expression('1')) 
                    ->from('pagos p2')
                    ->where('p2.user_id = p.user_id')
                    ->andWhere(['<', 'p2.fecha_pago', new Expression('p.fecha_pago - INTERVAL \'30 days\'')])
                ])
                ->andFilterWhere(['af.agente_id' => $agencyId]) // FIXED: Added quotes
                ->count(),
            
            'total_active_clients' => (new Query())
                ->from('user_datos ud')
                ->innerJoin('agente_fuerza af', 'ud.asesor_id = af.idusuario')
                ->where(['EXISTS', (new Query())
                    ->select(new Expression('1')) 
                    ->from('pagos p')
                    ->where('p.user_id = ud.id')
                    ->andWhere(['>=', 'p.fecha_pago', new Expression('CURRENT_DATE - INTERVAL \'90 days\'')])
                ])
                ->andFilterWhere(['af.agente_id' => $agencyId]) // FIXED: Added quotes
                ->count(),
            
            'revenue_trend' => self::calculateRevenueTrend($agencyId)
        ];

        $pipeline['renewal_rate'] = $pipeline['total_active_clients'] > 0 ? 
            round(($pipeline['renewals'] / $pipeline['total_active_clients']) * 100, 1) : 0;

        // Format for display
        $pipeline['new_acquisitions_formatted'] = number_format($pipeline['new_acquisitions']);
        $pipeline['renewals_formatted'] = number_format($pipeline['renewals']);
        $pipeline['total_active_clients_formatted'] = number_format($pipeline['total_active_clients']);
        $pipeline['renewal_rate_formatted'] = $pipeline['renewal_rate'] . '%';
        $pipeline['revenue_trend_formatted'] = $pipeline['revenue_trend'] . '%';
        $pipeline['revenue_trend_icon'] = $pipeline['revenue_trend'] >= 0 ? '📈' : '📉';

        return $pipeline;
    }

    /**
     * Calculate revenue trend (last 30 days vs previous 30 days)
     */
    private static function calculateRevenueTrend($agencyId = null)
    {
        $currentPeriod = (new Query())
            ->from('pagos p')
            ->innerJoin('user_datos ud', 'p.user_id = ud.id')
            ->innerJoin('agente_fuerza af', 'ud.asesor_id = af.idusuario')
            ->where(['>=', 'p.fecha_pago', new Expression('CURRENT_DATE - INTERVAL \'30 days\'')])
            ->andFilterWhere(['af.agente_id' => $agencyId]) // FIXED: Added quotes
            ->sum('p.monto_pagado') ?? 0;

        $previousPeriod = (new Query())
            ->from('pagos p')
            ->innerJoin('user_datos ud', 'p.user_id = ud.id')
            ->innerJoin('agente_fuerza af', 'ud.asesor_id = af.idusuario')
            ->where(['>=', 'p.fecha_pago', new Expression('CURRENT_DATE - INTERVAL \'60 days\'')])
            ->andWhere(['<', 'p.fecha_pago', new Expression('CURRENT_DATE - INTERVAL \'30 days\'')])
            ->andFilterWhere(['af.agente_id' => $agencyId]) // FIXED: Added quotes
            ->sum('p.monto_pagado') ?? 0;

        if ($previousPeriod == 0) return $currentPeriod > 0 ? 100 : 0;

        return round((($currentPeriod - $previousPeriod) / $previousPeriod) * 100, 1);
    }

    /**
     * Get KPI summary cards data for dashboard
     */
    public static function getKPISummaryCards($agencyId = null, $timeframe = 'month')
    {
        $agents = self::getAgentPerformanceScorecard($agencyId, $timeframe);
        $pipeline = self::getSalesPipelineHealth($agencyId);

        $totalRevenue = array_sum(array_column($agents, 'total_revenue'));
        $totalClients = array_sum(array_column($agents, 'total_clients'));
        $totalPolicies = array_sum(array_column($agents, 'total_policies'));
        $avgPerformance = count($agents) > 0 ? 
            array_sum(array_column($agents, 'performance_score')) / count($agents) : 0;

        return [
            'total_revenue' => [
                'value' => $totalRevenue,
                'formatted' => '$' . number_format($totalRevenue, 2),
                'label' => 'Ingresos Totales',
                'icon' => 'fa-money',
                'color' => '#28a745'
            ],
            'total_clients' => [
                'value' => $totalClients,
                'formatted' => number_format($totalClients),
                'label' => 'Total Clientes',
                'icon' => 'fa-users',
                'color' => '#4267B2'
            ],
            'total_policies' => [
                'value' => $totalPolicies,
                'formatted' => number_format($totalPolicies),
                'label' => 'Total Pagos',
                'icon' => 'fa-file-text',
                'color' => '#F65058'
            ],
            'avg_performance' => [
                'value' => $avgPerformance,
                'formatted' => round($avgPerformance, 1) . '/100',
                'label' => 'Desempeño Promedio',
                'icon' => 'fa-chart-line',
                'color' => '#F9A11A'
            ],
            'pipeline' => $pipeline
        ];
    }
}