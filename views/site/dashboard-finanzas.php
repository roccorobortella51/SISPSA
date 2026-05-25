<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $clinica \app\models\RmClinica */
/* @var $kpis array */
/* @var $revenueByPlan array */
/* @var $monthlyRevenue array */
/* @var $contractStatus array */
/* @var $recentContracts array */

$this->title = 'Dashboard Financiero';
$this->params['breadcrumbs'][] = $this->title;

// Ensure $kpis is defined
if (!isset($kpis) || !is_array($kpis)) {
    $kpis = [
        'total_revenue' => 0,
        'mrr' => 0,
        'total_active_affiliates' => 0,
        'avg_contract_value' => 0,
        'expiring_contracts' => 0,
        'expired_contracts' => 0,
        'attention_needed' => 0,
        'total_contracts' => 0,
    ];
}

// Ensure $contractStatus is defined
if (!isset($contractStatus) || !is_array($contractStatus)) {
    $contractStatus = [
        'activos' => 0,
        'creados' => 0,
        'suspendidos' => 0,
        'anulados' => 0,
        'registrados' => 0,
        'vencidos' => 0,
    ];
}

// Ensure $recentContracts is defined
if (!isset($recentContracts) || !is_array($recentContracts)) {
    $recentContracts = [];
}

// Ensure $revenueByPlan is defined
if (!isset($revenueByPlan) || !is_array($revenueByPlan)) {
    $revenueByPlan = [];
}

// Ensure $monthlyRevenue is defined
if (!isset($monthlyRevenue) || !is_array($monthlyRevenue) || empty($monthlyRevenue)) {
    $months = [];
    for ($i = 11; $i >= 0; $i--) {
        $dt = new \DateTime();
        $dt->modify("-{$i} months");
        $months[] = [
            'month' => $dt->format('M Y'),
            'revenue' => 0,
        ];
    }
    $monthlyRevenue = $months;
}

// Format currency function
function formatMoney($amount)
{
    return '$ ' . number_format($amount, 2, ',', '.');
}
?>

<div class="finanzas-dashboard">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-0 text-dark font-weight-bold">
                    <i class="fas fa-chart-line mr-2 text-primary"></i><?= Html::encode($this->title) ?>
                </h1>
                <p class="text-secondary mt-2 mb-0">Panel de control financiero con indicadores clave y reportes de gestión</p>
            </div>
            <div class="btn-group">
                <?= Html::a('<i class="fas fa-download mr-1"></i> Exportar Reportes', '#', [
                    'class' => 'btn btn-sm btn-outline-success font-weight-medium',
                    'onclick' => 'window.print(); return false;'
                ]) ?>
                <?= Html::a('<i class="fas fa-calendar-alt mr-1"></i> ' . date('d/m/Y'), '#', [
                    'class' => 'btn btn-sm btn-outline-secondary disabled font-weight-medium',
                ]) ?>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- SECTION 1: KPI CARDS - Key Performance Indicators (Compact) -->
        <!-- ============================================ -->
        <div class="mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary rounded p-2 mr-2">
                        <i class="fas fa-chart-simple text-white"></i>
                    </div>
                    <h5 class="mb-0 text-dark font-weight-bold">Indicadores Clave de Rendimiento</h5>
                    <span class="badge badge-primary ml-2">KPI's</span>
                </div>
                <i class="fas fa-info-circle text-secondary" data-toggle="tooltip" data-placement="left" title="Métricas fundamentales para evaluar el desempeño financiero"></i>
            </div>

            <div class="row">
                <!-- Total Revenue Card -->
                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="card border-left-primary shadow h-100"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="Suma total de todos los contratos activos">
                        <div class="card-body py-2">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Ingresos Totales
                                    </div>
                                    <div class="h6 mb-0 font-weight-bold text-gray-800">
                                        <?= formatMoney($kpis['total_revenue']) ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MRR Card -->
                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="card border-left-success shadow h-100"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="Ingresos Mensuales Recurrentes. Flujo de caja mensual garantizado">
                        <div class="card-body py-2">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        MRR
                                    </div>
                                    <div class="h6 mb-0 font-weight-bold text-gray-800">
                                        <?= formatMoney($kpis['mrr']) ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-week fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Affiliates Card -->
                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="card border-left-info shadow h-100"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="Afiliados con al menos un contrato activo">
                        <div class="card-body py-2">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Afiliados Activos
                                    </div>
                                    <div class="h6 mb-0 font-weight-bold text-gray-800">
                                        <?= number_format($kpis['total_active_affiliates']) ?>
                                    </div>
                                    <div class="small text-muted">
                                        <i class="fas fa-file-contract"></i> <?= number_format($kpis['total_contracts'] ?? 0) ?> contratos
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Avg Contract Value Card -->
                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="card border-left-warning shadow h-100"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="Valor promedio de los contratos activos">
                        <div class="card-body py-2">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Promedio x Contrato
                                    </div>
                                    <div class="h6 mb-0 font-weight-bold text-gray-800">
                                        <?= formatMoney($kpis['avg_contract_value']) ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expiring Contracts Card -->
                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="card border-left-danger shadow h-100"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="Contratos activos que vencen en los próximos 30 días">
                        <div class="card-body py-2">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                        Por Vencer
                                    </div>
                                    <div class="h6 mb-0 font-weight-bold text-gray-800">
                                        <?= number_format($kpis['expiring_contracts']) ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expired + Attention Card -->
                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="card border-left-dark shadow h-100"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="Contratos vencidos que requieren atención inmediata">
                        <div class="card-body py-2">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                        Vencidos
                                    </div>
                                    <div class="h6 mb-0 font-weight-bold text-gray-800">
                                        <?= number_format($kpis['expired_contracts']) ?>
                                    </div>
                                    <div class="small text-muted">
                                        <i class="fas fa-bell"></i> <?= number_format($kpis['attention_needed']) ?> requieren atención
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- SECTION 2: CONTRACT STATUS TABLE -->
        <!-- ============================================ -->
        <div class="mb-4">
            <div class="d-flex align-items-center mb-3">
                <div class="bg-success rounded p-2 mr-2">
                    <i class="fas fa-chart-pie text-white"></i>
                </div>
                <h5 class="mb-0 text-dark font-weight-bold">Distribución de Contratos por Estado</h5>
                <span class="badge badge-success ml-2">Resumen</span>
                <i class="fas fa-info-circle text-secondary ml-2" data-toggle="tooltip" data-placement="right" title="Desglose de todos los contratos según su estado actual. Los porcentajes se calculan sobre el total de contratos."></i>
            </div>
            <div class="card shadow">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.35rem 0.35rem 0 0;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-chart-pie mr-2 text-white fa-lg"></i>
                        <div>
                            <h6 class="m-0 font-weight-bold text-white">Distribución de Contratos por Estado</h6>
                            <small class="text-white-50">Análisis detallado del estado actual de todos los contratos</small>
                        </div>
                        <i class="fas fa-info-circle text-white-50 ml-2" data-toggle="tooltip" data-placement="right" title="Los porcentajes se calculan sobre el total de contratos registrados"></i>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-dark font-weight-bold">Estado</th>
                                    <th class="text-dark font-weight-bold text-right">Cantidad</th>
                                    <th class="text-dark font-weight-bold text-right">Porcentaje</th>
                                    <th class="text-dark font-weight-bold">Visualización</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $totalContracts = array_sum($contractStatus);
                                $statusColors = [
                                    'activos' => ['color' => '#28a745', 'bg' => 'bg-success', 'text' => 'text-success', 'tooltip' => 'Contratos vigentes con cobertura activa'],
                                    'registrados' => ['color' => '#17a2b8', 'bg' => 'bg-info', 'text' => 'text-info', 'tooltip' => 'Contratos registrados que aún no han iniciado'],
                                    'suspendidos' => ['color' => '#ffc107', 'bg' => 'bg-warning', 'text' => 'text-warning', 'tooltip' => 'Contratos temporalmente suspendidos por mora o incumplimiento'],
                                    'creados' => ['color' => '#6c757d', 'bg' => 'bg-secondary', 'text' => 'text-secondary', 'tooltip' => 'Contratos creados manualmente pendientes de activación'],
                                    'vencidos' => ['color' => '#dc3545', 'bg' => 'bg-danger', 'text' => 'text-danger', 'tooltip' => 'Contratos que excedieron su fecha de vencimiento'],
                                    'anulados' => ['color' => '#343a40', 'bg' => 'bg-dark', 'text' => 'text-dark', 'tooltip' => 'Contratos anulados o cancelados'],
                                ];
                                $displayNames = [
                                    'activos' => 'Activos',
                                    'registrados' => 'Registrados',
                                    'suspendidos' => 'Suspendidos',
                                    'creados' => 'Creados Manual',
                                    'vencidos' => 'Vencidos',
                                    'anulados' => 'Anulados'
                                ];

                                foreach ($contractStatus as $status => $count):
                                    $percentage = $totalContracts > 0 ? round(($count / $totalContracts) * 100, 1) : 0;
                                    $colorInfo = $statusColors[$status] ?? ['color' => '#6c757d', 'bg' => 'bg-secondary', 'text' => 'text-secondary', 'tooltip' => 'Estado del contrato'];
                                ?>
                                    <tr>
                                        <td data-toggle="tooltip" data-placement="left" title="<?= $colorInfo['tooltip'] ?>">
                                            <span class="badge <?= $colorInfo['bg'] ?> px-3 py-2 text-white" style="font-size: 0.85rem;">
                                                <?= $displayNames[$status] ?? ucfirst($status) ?>
                                            </span>
                                        </td>
                                        <td class="text-right font-weight-bold text-dark"><?= number_format($count) ?></td>
                                        <td class="text-right">
                                            <span class="font-weight-bold text-dark"><?= $percentage ?>%</span>
                                        </td>
                                        <td style="width: 40%;">
                                            <div class="progress" style="height: 12px;" data-toggle="tooltip" data-placement="top" title="<?= $percentage ?>% del total">
                                                <div class="progress-bar <?= $colorInfo['bg'] ?>"
                                                    role="progressbar"
                                                    style="width: <?= $percentage ?>%;"
                                                    aria-valuenow="<?= $percentage ?>"
                                                    aria-valuemin="0"
                                                    aria-valuemax="100">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if ($totalContracts == 0): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-secondary py-4">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            No hay contratos registrados
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="bg-light">
                                <tr class="font-weight-bold">
                                    <td class="text-dark" data-toggle="tooltip" data-placement="left" title="Suma total de todos los contratos">Total</td>
                                    <td class="text-right text-dark"><?= number_format($totalContracts) ?></td>
                                    <td class="text-right text-dark">100%</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- SECTION 3: CHARTS SECTION -->
        <!-- ============================================ -->
        <div class="mb-4">
            <div class="d-flex align-items-center mb-3">
                <div class="bg-info rounded p-2 mr-2">
                    <i class="fas fa-chart-line text-white"></i>
                </div>
                <h5 class="mb-0 text-dark font-weight-bold">Análisis Gráfico</h5>
                <span class="badge badge-info ml-2">Visualización</span>
                <i class="fas fa-info-circle text-secondary ml-2" data-toggle="tooltip" data-placement="right" title="Gráficos interactivos. Pase el mouse sobre los elementos para ver detalles."></i>
            </div>
            <div class="row">
                <!-- Monthly Revenue Chart -->
                <div class="col-xl-7 col-lg-7 mb-4">
                    <div class="card shadow h-100">
                        <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.35rem 0.35rem 0 0;">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-chart-line mr-2 text-white fa-lg"></i>
                                <div>
                                    <h6 class="m-0 font-weight-bold text-white">Evolución de Ingresos Mensuales</h6>
                                    <small class="text-white-50">Últimos 12 meses</small>
                                </div>
                                <i class="fas fa-info-circle text-white-50 ml-2" data-toggle="tooltip" data-placement="right" title="Muestra la tendencia de ingresos mes a mes basado en contratos activos"></i>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-area" style="position: relative; height: 320px;">
                                <canvas id="monthlyRevenueChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenue by Plan Chart -->
                <div class="col-xl-5 col-lg-5 mb-4">
                    <div class="card shadow h-100">
                        <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.35rem 0.35rem 0 0;">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-chart-pie mr-2 text-white fa-lg"></i>
                                <div>
                                    <h6 class="m-0 font-weight-bold text-white">Distribución de Ingresos por Plan</h6>
                                    <small class="text-white-50">Distribución por categoría de plan</small>
                                </div>
                                <i class="fas fa-info-circle text-white-50 ml-2" data-toggle="tooltip" data-placement="right" title="Participación porcentual de cada plan en el ingreso total"></i>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-pie" style="position: relative; height: 250px;">
                                <canvas id="revenueByPlanChart"></canvas>
                            </div>
                            <div class="mt-4 text-center">
                                <?php if (!empty($revenueByPlan)): ?>
                                    <div class="d-flex flex-wrap justify-content-center">
                                        <?php foreach ($revenueByPlan as $index => $plan): ?>
                                            <span class="mr-3 mb-2" data-toggle="tooltip" data-placement="top" title="<?= Html::encode($plan['plan_name']) ?>: <?= formatMoney($plan['total_revenue']) ?> (<?= number_format($plan['contract_count'] ?? 0) ?> contratos)">
                                                <i class="fas fa-circle" style="color: <?= $plan['color'] ?>"></i>
                                                <span class="text-dark font-weight-medium"><?= Html::encode($plan['plan_name']) ?></span>
                                                <small class="text-secondary ml-1">(<?= formatMoney($plan['total_revenue']) ?>)</small>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-secondary">No hay datos de ingresos por plan</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- SECTION 4: RECENT CONTRACTS -->
        <!-- ============================================ -->
        <div class="mb-4">
            <div class="d-flex align-items-center mb-3">
                <div class="bg-warning rounded p-2 mr-2">
                    <i class="fas fa-file-contract text-white"></i>
                </div>
                <h5 class="mb-0 text-dark font-weight-bold">Contratos Recientes</h5>
                <span class="badge badge-warning ml-2">Últimos 10</span>
                <i class="fas fa-info-circle text-secondary ml-2" data-toggle="tooltip" data-placement="right" title="Listado de los 10 contratos más recientes creados en el sistema"></i>
            </div>
            <div class="card shadow">
                <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between border-bottom" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.35rem 0.35rem 0 0;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-list mr-2 text-white fa-lg"></i>
                        <h6 class="m-0 font-weight-bold text-white">Listado de Contratos Recientes</h6>
                    </div>
                    <?= Html::a('<i class="fas fa-eye mr-1"></i> Ver Todos los Contratos', ['/contratos/index'], [
                        'class' => 'btn btn-sm btn-light shadow-sm',
                        'style' => 'background-color: #ffffff; color: #4e73df; border: none; font-weight: 600;',
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'left',
                        'title' => 'Ir al listado completo de contratos para gestionar'
                    ]) ?>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-dark font-weight-bold">ID Contrato</th>
                                    <th class="text-dark font-weight-bold">Afiliado</th>
                                    <th class="text-dark font-weight-bold">Plan</th>
                                    <th class="text-dark font-weight-bold text-right">Monto</th>
                                    <th class="text-dark font-weight-bold">Estado</th>
                                    <th class="text-dark font-weight-bold">Fecha Inicio</th>
                                    <th class="text-dark font-weight-bold">Fecha Vencimiento</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentContracts as $contract): ?>
                                    <tr>
                                        <td class="align-middle">
                                            <?= Html::a($contract->id, ['/contratos/view', 'id' => $contract->id], [
                                                'class' => 'text-primary font-weight-bold',
                                                'data-toggle' => 'tooltip',
                                                'data-placement' => 'top',
                                                'title' => 'Ver detalle del contrato #' . $contract->id
                                            ]) ?>
                                        </td>
                                        <td class="align-middle text-dark">
                                            <?php
                                            $userDatos = $contract->user;
                                            echo $userDatos ? Html::encode($userDatos->nombres . ' ' . $userDatos->apellidos) : 'N/A';
                                            ?>
                                        </td>
                                        <td class="align-middle text-dark"><?= $contract->plan ? Html::encode($contract->plan->nombre) : 'N/A' ?></td>
                                        <td class="text-right align-middle font-weight-bold text-dark"><?= formatMoney($contract->monto ?? 0) ?></td>
                                        <td class="align-middle">
                                            <?= $contract->getStatusBadge() ?>
                                        </td>
                                        <td class="align-middle text-dark"><?= $contract->fecha_ini ? Yii::$app->formatter->asDate($contract->fecha_ini) : 'N/A' ?></td>
                                        <td class="align-middle">
                                            <span class="text-dark"><?= $contract->fecha_ven ? Yii::$app->formatter->asDate($contract->fecha_ven) : 'N/A' ?></span>
                                            <?php if ($contract->fecha_ven && strtotime($contract->fecha_ven) < time()): ?>
                                                <i class="fas fa-exclamation-circle text-danger ml-1" data-toggle="tooltip" data-placement="top" title="Contrato vencido"></i>
                                            <?php elseif ($contract->fecha_ven && strtotime($contract->fecha_ven) < strtotime('+30 days')): ?>
                                                <i class="fas fa-clock text-warning ml-1" data-toggle="tooltip" data-placement="top" title="Por vencer pronto"></i>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recentContracts)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-secondary py-4">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            No hay contratos registrados
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- SECTION 5: QUICK ACTIONS -->
        <!-- ============================================ -->
        <div class="mb-4">
            <div class="d-flex align-items-center mb-3">
                <div class="bg-secondary rounded p-2 mr-2">
                    <i class="fas fa-bolt text-white"></i>
                </div>
                <h5 class="mb-0 text-dark font-weight-bold">Acciones Rápidas</h5>
                <span class="badge badge-secondary ml-2">Accesos directos</span>
                <i class="fas fa-info-circle text-secondary ml-2" data-toggle="tooltip" data-placement="right" title="Accesos directos a reportes y funcionalidades comunes"></i>
            </div>
            <div class="card shadow">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.35rem 0.35rem 0 0;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-bolt mr-2 text-white fa-lg"></i>
                        <div>
                            <h6 class="m-0 font-weight-bold text-white">Acciones Rápidas</h6>
                            <small class="text-white-50">Acceso directo a reportes y módulos financieros</small>
                        </div>
                        <i class="fas fa-info-circle text-white-50 ml-2" data-toggle="tooltip" data-placement="right" title="Haga clic en cualquier botón para acceder al módulo correspondiente"></i>
                    </div>
                </div>
                <div class="card-body">
                    <div class="btn-group flex-wrap" role="group">
                        <?= Html::a('<i class="fas fa-file-invoice-dollar mr-1"></i> Cartera Vigente', ['/cartera-vigente/index'], [
                            'class' => 'btn mb-2 mr-2 font-weight-medium',
                            'style' => 'background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); border: none; color: white; padding: 0.375rem 0.75rem; border-radius: 0.25rem; font-size: 0.875rem; transition: transform 0.2s, box-shadow 0.2s;',
                            'data-toggle' => 'tooltip',
                            'data-placement' => 'top',
                            'title' => 'Visualizar cartera de contratos vigentes'
                        ]) ?>

                        <?= Html::a('<i class="fas fa-chart-line mr-1"></i> Dashboard Afiliados', ['/user-datos/reporte-afiliados-dashboard'], [
                            'class' => 'btn mb-2 mr-2 font-weight-medium',
                            'style' => 'background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%); border: none; color: white; padding: 0.375rem 0.75rem; border-radius: 0.25rem; font-size: 0.875rem; transition: transform 0.2s, box-shadow 0.2s;',
                            'data-toggle' => 'tooltip',
                            'data-placement' => 'top',
                            'title' => 'Ver dashboard de afiliados por plan'
                        ]) ?>

                        <?= Html::a('<i class="fas fa-percent mr-1"></i> Comisiones', ['/reportes/comisiones'], [
                            'class' => 'btn mb-2 mr-2 font-weight-medium',
                            'style' => 'background: linear-gradient(135deg, #f6c23e 0%, #d3851a 100%); border: none; color: white; padding: 0.375rem 0.75rem; border-radius: 0.25rem; font-size: 0.875rem; transition: transform 0.2s, box-shadow 0.2s;',
                            'data-toggle' => 'tooltip',
                            'data-placement' => 'top',
                            'title' => 'Generar reporte de comisiones'
                        ]) ?>

                        <?= Html::a('<i class="fas fa-hospital-user mr-1"></i> Afiliados por Clínica', ['/user-datos/reporte-afiliados'], [
                            'class' => 'btn mb-2 mr-2 font-weight-medium',
                            'style' => 'background: linear-gradient(135deg, #36b9cc 0%, #258391 100%); border: none; color: white; padding: 0.375rem 0.75rem; border-radius: 0.25rem; font-size: 0.875rem; transition: transform 0.2s, box-shadow 0.2s;',
                            'data-toggle' => 'tooltip',
                            'data-placement' => 'top',
                            'title' => 'Ver reporte de afiliados por clínica'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
<?php
$monthlyData = json_encode(array_column($monthlyRevenue, 'revenue'));
$monthlyLabels = json_encode(array_column($monthlyRevenue, 'month'));
$planData = json_encode(array_column($revenueByPlan, 'total_revenue'));
$planLabels = json_encode(array_column($revenueByPlan, 'plan_name'));
$planColors = json_encode(array_column($revenueByPlan, 'color'));
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Bootstrap tooltips
        if (typeof $ !== 'undefined' && $.fn.tooltip) {
            $('[data-toggle="tooltip"]').tooltip();
        }

        // Monthly Revenue Chart
        const monthlyCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
        const monthlyLabels = <?= $monthlyLabels ?>;
        const monthlyData = <?= $monthlyData ?>;

        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Ingresos ($)',
                    data: monthlyData,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#4e73df',
                    pointBorderColor: '#fff',
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 12,
                                weight: 'bold'
                            },
                            color: '#333',
                            boxWidth: 12
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Ingresos: $ ' + context.raw.toLocaleString('es-VE');
                            }
                        },
                        backgroundColor: '#2c3e50',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        titleFont: {
                            weight: 'bold'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#e9ecef'
                        },
                        ticks: {
                            callback: function(value) {
                                return '$ ' + value.toLocaleString('es-VE');
                            },
                            color: '#555',
                            font: {
                                weight: '500'
                            }
                        },
                        title: {
                            display: true,
                            text: 'Monto en Dólares ($)',
                            color: '#333',
                            font: {
                                weight: 'bold',
                                size: 12
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#555',
                            font: {
                                weight: '500'
                            }
                        },
                        title: {
                            display: true,
                            text: 'Meses',
                            color: '#333',
                            font: {
                                weight: 'bold',
                                size: 12
                            }
                        }
                    }
                }
            }
        });

        // Revenue by Plan Pie Chart with specific colors
        const planCtx = document.getElementById('revenueByPlanChart').getContext('2d');
        const planLabels = <?= json_encode(array_column($revenueByPlan, 'plan_name')) ?>;
        const planData = <?= json_encode(array_column($revenueByPlan, 'total_revenue')) ?>;
        const planColors = <?= json_encode(array_column($revenueByPlan, 'color')) ?>;

        if (planLabels.length > 0 && planData.length > 0 && planData.some(v => v > 0)) {
            new Chart(planCtx, {
                type: 'doughnut',
                data: {
                    labels: planLabels,
                    datasets: [{
                        data: planData,
                        backgroundColor: planColors,
                        hoverBackgroundColor: planColors.map(color => color + 'cc'),
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 11,
                                    weight: '500'
                                },
                                color: '#333',
                                boxWidth: 12,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.raw / total) * 100).toFixed(1);
                                    return context.label + ': $ ' + context.raw.toLocaleString('es-VE') + ' (' + percentage + '%)';
                                }
                            },
                            backgroundColor: '#2c3e50',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            titleFont: {
                                weight: 'bold'
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        } else {
            planCtx.canvas.width = planCtx.canvas.width || 300;
            planCtx.canvas.height = planCtx.canvas.height || 300;
            planCtx.font = 'bold 14px Arial';
            planCtx.fillStyle = '#666';
            planCtx.textAlign = 'center';
            planCtx.fillText('No hay datos de ingresos por plan disponibles', planCtx.canvas.width / 2, planCtx.canvas.height / 2);
        }
    });
</script>

<style>
    .finanzas-dashboard .card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .finanzas-dashboard .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    .finanzas-dashboard .card-header {
        background-color: #ffffff !important;
    }

    .table-responsive::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    .btn-group .btn {
        margin-right: 0.5rem;
    }

    .progress {
        background-color: #e9ecef;
        border-radius: 10px;
    }

    .badge {
        font-size: 0.8rem;
        padding: 0.4rem 0.75rem;
        border-radius: 20px;
    }

    .table th {
        border-top: none;
        font-weight: 600;
    }

    /* Tooltip custom styling */
    .tooltip-inner {
        max-width: 280px;
        font-size: 0.8rem;
        background-color: #2c3e50;
        color: #ffffff;
    }

    .tooltip.show {
        opacity: 0.95;
    }

    /* Ensure all text has proper contrast */
    .text-dark {
        color: #1a1a2e !important;
    }

    .text-secondary {
        color: #6c757d !important;
    }

    .font-weight-bold {
        font-weight: 700 !important;
    }

    .font-weight-medium {
        font-weight: 600 !important;
    }

    /* Card value text */
    .card .h5 {
        font-size: 1.5rem;
        color: #1a1a2e;
    }

    /* Table cell text */
    .table td {
        color: #2c3e50;
    }

    /* Badge text */
    .badge.text-white {
        color: #ffffff !important;
    }
</style>