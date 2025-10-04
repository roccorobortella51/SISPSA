<?php

namespace app\controllers;

use yii\web\Controller;
use yii\filters\AccessControl;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use app\components\FinancialHelper;
use app\components\SalesKpiHelper;

class FinancialsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [
                            'agency-earnings', 
                            'salesman-earnings', 
                            'debug-agencies',
                            'kpi-dashboard',
                            'agent-performance',
                            'pipeline-health'
                        ],
                        'roles' => ['admin', 'superadmin','GERENTE-COMERCIALIZACION'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Renders the agency earnings report page with data.
     * @return string
     */
    public function actionAgencyEarnings()
    {
        $earnings = FinancialHelper::getAgencyEarnings();
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => $earnings,
            'sort' => [
                'attributes' => ['id', 'nombre', 'total_ventas', 'comision_agencia', 'total_clients'],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('agency-earnings', [
            'dataProvider' => $dataProvider,
        ]);
    }

/**
 * Renders salesman earnings for a specific agency.
 * @param int $agencyId
 * @return string
 */
public function actionSalesmanEarnings($agencyId)
{
    $agency = (new Query())
        ->select('nom')
        ->from('agente')
        ->where(['id' => $agencyId])
        ->one();

    if (!$agency) {
        throw new \yii\web\NotFoundHttpException('Agencia no encontrada');
    }

    $earnings = FinancialHelper::getSalesmanEarnings($agencyId);
    
    // --- LOGIC: FETCH CLAIMS (SINIESTROS) COUNT PER SALESMAN VIA 3-WAY JOIN ---
    $siniestrosData = (new Query())
        ->select([
            'af.idusuario AS asesor_id', 
            'COUNT(s.id) AS total_siniestros'
        ])
        ->from(['s' => 'sis_siniestro']) 
        // 1. Join to user_datos using the affiliate ID
        ->innerJoin(['ud' => 'user_datos'], 's.iduser = ud.id')
        // 2. Join to agente_fuerza using the asesor_id (from user_datos) and the idusuario (from agente_fuerza)
        ->innerJoin(['af' => 'agente_fuerza'], 'ud.asesor_id = af.idusuario')
        ->where(['IS NOT', 'af.idusuario', null])
        ->groupBy('af.idusuario')
        ->all();
        
    // 2. Create a map for quick lookup: [asesor_id (User ID) => total_siniestros]
    $siniestrosMap = [];
    foreach ($siniestrosData as $row) {
        $siniestrosMap[$row['asesor_id']] = (int) $row['total_siniestros'];
    }

    // 3. Merge the siniestros count into the main earnings data AND prepare chart data
    $siniestroNames = [];
    $siniestroCounts = [];
    
    foreach ($earnings as $key => $salesman) {
        // 'id' in $earnings array is the salesman/asesor User ID
        $salesmanId = $salesman['id']; 
        $claimsCount = $siniestrosMap[$salesmanId] ?? 0;
        $earnings[$key]['total_siniestros'] = $claimsCount;
        
        // Collect data for the chart here, only for those with claims
        if (!empty($salesman['nombre']) && $claimsCount > 0) {
            $siniestroNames[] = $salesman['nombre'];
            $siniestroCounts[] = $claimsCount;
        }
    }
    // --- END LOGIC ---

    $dataProvider = new ArrayDataProvider([
        'allModels' => $earnings,
        'sort' => [
            'attributes' => ['id', 'nombre', 'total_ventas', 'comision_asesor', 'total_clients', 'quarterly_growth', 'total_siniestros'],
        ],
        'pagination' => [
            'pageSize' => 20,
        ],
    ]);

    return $this->render('salesman-earnings', [
        'dataProvider' => $dataProvider,
        'agencyName' => $agency['nom'],
        'agencyId' => $agencyId,
        // NEW DATA FOR CHART
        'siniestroNames' => $siniestroNames,
        'siniestroCounts' => $siniestroCounts,
    ]);
}

    /**
     * Sales KPI Dashboard - Main overview
     * @param int $agencyId
     * @return string
     */
    public function actionKpiDashboard($agencyId = null)
    {
        $performanceData = SalesKpiHelper::getAgentPerformanceScorecard($agencyId, 'month');
        $pipelineData = SalesKpiHelper::getSalesPipelineHealth($agencyId);

        return $this->render('kpi-dashboard', [
            'performanceData' => $performanceData,
            'pipelineData' => $pipelineData,
            'agencyId' => $agencyId,
        ]);
    }

    /**
     * Detailed Agent Performance Report
     * @param int $agencyId
     * @param string $timeframe
     * @return string
     */
    public function actionAgentPerformance($agencyId = null, $timeframe = 'year')
    {
           // Clear any previous output and buffers
    if (ob_get_length()) ob_clean();
    ob_start();
    
    try {
        $performanceData = SalesKpiHelper::getAgentPerformanceScorecard($agencyId, $timeframe);
        
        // Remove duplicates by agent_id (keep first occurrence)
        $uniqueAgents = [];
        foreach ($performanceData as $agent) {
            if (!isset($uniqueAgents[$agent['agent_id']])) {
                $uniqueAgents[$agent['agent_id']] = $agent;
            }
        }
        $performanceData = array_values($uniqueAgents);
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => $performanceData,
            'sort' => [
                'attributes' => ['agent_name', 'total_revenue', 'performance_score', 'total_clients'],
                'defaultOrder' => ['performance_score' => SORT_DESC],
            ],
            'pagination' => false,
        ]);

        $content = $this->render('agent-performance', [
            'dataProvider' => $dataProvider,
            'timeframe' => $timeframe,
            'agencyId' => $agencyId,
        ]);
        
        ob_end_clean();
        return $content;
        
    } catch (\Exception $e) {
        ob_end_clean();
        return "Error: " . $e->getMessage();
    }
}

    /**
     * Sales Pipeline Health Report
     * @param int $agencyId
     * @return string
     */
    public function actionPipelineHealth()
{
    $pipeline = FinancialHelper::getPipelineHealth();
    
    return $this->render('pipeline-health', [
        'pipeline' => $pipeline,
    ]);
}
    public function actionTestModal()
{
    return $this->render('test-modal');
}
    /**
     * Debug tool for agency relationships
     */
    public function actionDebugAgencies()
    {
        // Debug all payments with their agency relationships
        $allPayments = FinancialHelper::getAllPaymentsWithAgencies();
        
        echo "<h2>All Payments with Agency Relationships</h2>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr>
                <th>Payment ID</th>
                <th>User ID</th>
                <th>Amount</th>
                <th>Asesor ID</th>
                <th>Salesman ID</th>
                <th>Agency ID</th>
                <th>Agency Name</th>
                <th>Agency Commission</th>
                <th>Total Clients</th>
              </tr>";
        
        foreach ($allPayments as $payment) {
            echo "<tr>
                    <td>{$payment['payment_id']}</td>
                    <td>{$payment['user_id']}</td>
                    <td>\${$payment['monto_pagado']}</td>
                    <td>{$payment['asesor_id']}</td>
                    <td>{$payment['salesman_id']}</td>
                    <td>{$payment['agente_id']}</td>
                    <td>{$payment['agency_name']}</td>
                    <td>{$payment['agency_commission']}%</td>
                    <td>{$payment['total_clients']}</td>
                  </tr>";
        }
        echo "</table>";

        // Debug specific payment (user_id = 22 who paid $20)
        $payment22 = FinancialHelper::debugPaymentRelationships(22);
        echo "<h2>Payment for user_id = 22 (the $20 payment)</h2>";
        echo "<pre>";
        print_r($payment22);
        echo "</pre>";

        // Test the fixed agency earnings
        $earnings = FinancialHelper::getAgencyEarnings();
        echo "<h2>Agency Earnings Results</h2>";
        echo "<pre>";
        print_r($earnings);
        echo "</pre>";
        
        // Test salesman earnings for a specific agency
        if (!empty($payment22[0]['agente_id'])) {
            $salesmanEarnings = FinancialHelper::getSalesmanEarnings($payment22[0]['agente_id']);
            echo "<h2>Salesman Earnings for Agency ID: {$payment22[0]['agente_id']}</h2>";
            echo "<pre>";
            print_r($salesmanEarnings);
            echo "</pre>";
        }
    }
}
