<?php

/**
 * @var yii\web\View $this
 * @var string $asesorName
 * @var string $clinicaNombre
 * @var array $clinicaStats
 * @var array $asesorStats
 * @var array $monthlyData
 * @var array $planesData
 * @var app\models\UserDatos[] $recientes
 */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Panel de Asesor - SISPSA';
$this->params['breadcrumbs'][] = $this->title;

// Register Chart.js CDN
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', ['position' => \yii\web\View::POS_END]);
?>

<style>
    /* Microsoft Fluent Design + SISPSA Brand Colors */
    :root {
        --sispsa-blue: #2c6496;
        --sispsa-blue-light: #e8f0f8;
        --sispsa-green: #008b6b;
        --sispsa-green-light: #e6f4f0;
        --sispsa-dark: #1a3a4f;
        --gray-50: #f8f9fc;
        --gray-100: #f3f6f9;
        --gray-200: #eaecf4;
        --gray-300: #dddfeb;
        --gray-400: #d1d3e0;
        --gray-500: #b7b9cc;
        --gray-600: #858796;
        --gray-700: #6e707e;
        --gray-800: #5a5c69;
        --gray-900: #3a3b45;
        --success: #27ae60;
        --danger: #e74a3b;
        --warning: #f39c12;
        --info: #36b9cc;
        --shadow-sm: 0 0.125rem 0.25rem 0 rgba(0, 0, 0, 0.075);
        --shadow-md: 0 0.5rem 1rem 0 rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 1rem 2rem 0 rgba(0, 0, 0, 0.15);
        --border-radius: 0.5rem;
        --transition: all 0.2s ease;
    }

    .dashboard-asesor {
        background-color: var(--gray-50);
        min-height: 100vh;
        padding: 1.5rem;
        font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Roboto', system-ui, sans-serif;
    }

    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Welcome Header */
    .welcome-header {
        margin-bottom: 2rem;
    }

    .welcome-header h1 {
        font-size: 1.75rem;
        font-weight: 600;
        color: var(--sispsa-blue);
        margin: 0 0 0.25rem 0;
    }

    .welcome-header p {
        color: var(--gray-600);
        margin: 0;
        font-size: 1rem;
    }

    .clinic-badge {
        display: inline-block;
        background: var(--sispsa-blue-light);
        color: var(--sispsa-blue);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        margin-top: 0.5rem;
    }

    /* Section Title */
    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--sispsa-blue);
        margin: 1.5rem 0 1rem 0;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--gray-200);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .section-title i {
        color: var(--sispsa-green);
    }

    /* KPI Cards Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.25rem;
        margin-bottom: 1rem;
    }

    .kpi-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.25rem 1.5rem;
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
        border: 1px solid var(--gray-200);
        position: relative;
        overflow: hidden;
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--sispsa-blue);
    }

    .kpi-card:nth-child(1)::before {
        background: var(--sispsa-blue);
    }

    .kpi-card:nth-child(2)::before {
        background: var(--sispsa-green);
    }

    .kpi-card:nth-child(3)::before {
        background: var(--warning);
    }

    .kpi-card:nth-child(4)::before {
        background: var(--danger);
    }

    .kpi-card:nth-child(5)::before {
        background: var(--info);
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .kpi-title {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        color: var(--gray-600);
        margin-bottom: 0.5rem;
    }

    .kpi-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 0.25rem;
        line-height: 1.2;
    }

    .kpi-trend {
        font-size: 0.7rem;
        color: var(--gray-500);
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .trend-up {
        color: var(--success);
    }

    .trend-down {
        color: var(--danger);
    }

    /* Tabs Navigation */
    .tabs-navigation {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--gray-200);
        margin-bottom: 1.5rem;
        margin-top: 1rem;
    }

    .tabs-nav {
        display: flex;
        border-bottom: 1px solid var(--gray-200);
        padding: 0 1rem;
        gap: 0.5rem;
    }

    .tab-btn {
        padding: 0.875rem 1.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--gray-600);
        background: transparent;
        border: none;
        cursor: pointer;
        transition: var(--transition);
        position: relative;
        border-radius: 0;
    }

    .tab-btn:hover {
        color: var(--sispsa-blue);
    }

    .tab-btn.active {
        color: var(--sispsa-blue);
        font-weight: 600;
    }

    .tab-btn.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--sispsa-green);
    }

    /* Tab Content */
    .tab-content {
        display: none;
        padding: 1.5rem;
        animation: fadeIn 0.3s ease;
    }

    .tab-content.active {
        display: block;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Chart Container */
    .chart-container {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        border: 1px solid var(--gray-200);
    }

    .chart-container h3 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--gray-800);
        margin: 0 0 1rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .chart-container h3 i {
        color: var(--sispsa-green);
    }

    .chart-wrapper {
        height: 280px;
        position: relative;
    }

    /* Recent Affiliates Table */
    .recent-table-container {
        background: white;
        border-radius: var(--border-radius);
        border: 1px solid var(--gray-200);
        overflow: hidden;
    }

    .recent-table-container h3 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--gray-800);
        margin: 0;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .recent-table {
        width: 100%;
        border-collapse: collapse;
    }

    .recent-table th {
        text-align: left;
        padding: 0.75rem 1.25rem;
        background-color: var(--gray-50);
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--gray-600);
        border-bottom: 1px solid var(--gray-200);
    }

    .recent-table td {
        padding: 0.75rem 1.25rem;
        font-size: 0.875rem;
        color: var(--gray-700);
        border-bottom: 1px solid var(--gray-200);
    }

    .recent-table tr:last-child td {
        border-bottom: none;
    }

    .recent-table tr:hover {
        background-color: var(--gray-50);
    }

    .badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        font-size: 0.7rem;
        font-weight: 600;
        border-radius: 12px;
        text-transform: capitalize;
    }

    .badge-active {
        background: #e6f4f0;
        color: var(--sispsa-green);
    }

    .badge-pending {
        background: #fff3e0;
        color: var(--warning);
    }

    .badge-expired {
        background: #fee2e2;
        color: var(--danger);
    }

    /* Plans Section */
    .plans-section {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .plan-category {
        background: white;
        border-radius: var(--border-radius);
        border: 1px solid var(--gray-200);
        overflow: hidden;
    }

    .plan-category-header {
        background: linear-gradient(135deg, var(--sispsa-blue) 0%, var(--sispsa-dark) 100%);
        padding: 1rem 1.5rem;
        color: white;
    }

    .plan-category-header h3 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
    }

    .plan-category-header p {
        margin: 0.25rem 0 0 0;
        font-size: 0.8rem;
        opacity: 0.9;
    }

    .plan-table {
        width: 100%;
        border-collapse: collapse;
    }

    .plan-table th {
        text-align: left;
        padding: 0.875rem 1rem;
        background-color: var(--gray-50);
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--gray-600);
        border-bottom: 1px solid var(--gray-200);
    }

    .plan-table td {
        padding: 0.875rem 1rem;
        font-size: 0.875rem;
        color: var(--gray-700);
        border-bottom: 1px solid var(--gray-200);
    }

    .plan-table tr:last-child td {
        border-bottom: none;
    }

    .plan-price {
        font-weight: 700;
        color: var(--sispsa-green);
        font-size: 1rem;
    }

    .plan-coverage {
        font-family: monospace;
        font-size: 0.8rem;
    }

    .benefits-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1rem;
        padding: 1.5rem;
    }

    .benefit-item {
        display: flex;
        gap: 0.75rem;
        padding: 0.75rem;
        background: var(--gray-50);
        border-radius: 0.5rem;
    }

    .benefit-icon {
        width: 36px;
        height: 36px;
        background: var(--sispsa-blue-light);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--sispsa-blue);
        font-size: 1rem;
    }

    .benefit-content {
        flex: 1;
    }

    .benefit-title {
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--gray-800);
        margin-bottom: 0.25rem;
    }

    .benefit-desc {
        font-size: 0.75rem;
        color: var(--gray-600);
        line-height: 1.4;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: var(--gray-500);
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-asesor {
            padding: 1rem;
        }

        .kpi-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .tabs-nav {
            flex-direction: column;
            padding: 0;
        }

        .tab-btn {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        .tab-btn.active::after {
            display: none;
        }

        .plan-table,
        .recent-table {
            display: block;
            overflow-x: auto;
        }

        .benefits-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="dashboard-asesor">
    <div class="dashboard-container">
        <!-- Welcome Header -->
        <div class="welcome-header">
            <h1>
                <i class="fas fa-chart-line" style="color: var(--sispsa-green); margin-right: 10px;"></i>
                Panel de Asesor
            </h1>
            <p>Bienvenido, <?= Html::encode($asesorName) ?></p>
            <span class="clinic-badge">
                <i class="fas fa-hospital"></i> Clínica: <?= Html::encode($clinicaNombre) ?>
            </span>
        </div>

        <!-- Tabs Navigation -->
        <div class="tabs-navigation">
            <div class="tabs-nav">
                <button class="tab-btn active" data-tab="clinic">
                    <i class="fas fa-hospital"></i> Estadísticas de la Clínica
                </button>
                <button class="tab-btn" data-tab="personal">
                    <i class="fas fa-user-check"></i> Mis Afiliados
                </button>
                <button class="tab-btn" data-tab="plans">
                    <i class="fas fa-file-alt"></i> Planes SISPSA
                </button>
            </div>

            <!-- Tab 1: Clinic Statistics -->
            <div id="tab-clinic" class="tab-content active">
                <div class="section-title">
                    <i class="fas fa-chart-pie"></i>
                    <span>Resumen de la Clínica: <?= Html::encode($clinicaNombre) ?></span>
                </div>

                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-title">Total Afiliados</div>
                        <div class="kpi-value"><?= number_format($clinicaStats['total_afiliados']) ?></div>
                        <div class="kpi-trend">
                            <i class="fas fa-user-plus"></i>
                            <span><?= number_format($clinicaStats['nuevos_este_mes']) ?> nuevos este mes</span>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-title">Contratos Activos</div>
                        <div class="kpi-value"><?= number_format($clinicaStats['activos']) ?></div>
                        <div class="kpi-trend trend-up">
                            <i class="fas fa-check-circle"></i>
                            <span>Afiliados activos</span>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-title">Contratos Suspendidos</div>
                        <div class="kpi-value"><?= number_format($clinicaStats['suspendidos']) ?></div>
                        <div class="kpi-trend trend-down">
                            <i class="fas fa-pause-circle"></i>
                            <span>Requieren atención</span>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-title">Contratos Vencidos</div>
                        <div class="kpi-value"><?= number_format($clinicaStats['con_contratos_vencidos']) ?></div>
                        <div class="kpi-trend trend-down">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Requieren renovación</span>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-title">Tasa de Actividad</div>
                        <div class="kpi-value"><?= $clinicaStats['tasa_actividad'] ?>%</div>
                        <div class="kpi-trend">
                            <i class="fas fa-chart-pie"></i>
                            <span>Afiliados activos vs total</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Personal Statistics (Asesor's own affiliates) -->
            <div id="tab-personal" class="tab-content">
                <div class="section-title">
                    <i class="fas fa-user-check"></i>
                    <span>Mis Afiliados</span>
                </div>

                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-title">Mis Afiliados</div>
                        <div class="kpi-value"><?= number_format($asesorStats['total_afiliados']) ?></div>
                        <div class="kpi-trend">
                            <i class="fas fa-user-plus"></i>
                            <span><?= number_format($asesorStats['nuevos_este_mes']) ?> nuevos este mes</span>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-title">Mis Activos</div>
                        <div class="kpi-value"><?= number_format($asesorStats['activos']) ?></div>
                        <div class="kpi-trend trend-up">
                            <i class="fas fa-check-circle"></i>
                            <span>Con contratos activos</span>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-title">Mis Suspendidos</div>
                        <div class="kpi-value"><?= number_format($asesorStats['suspendidos']) ?></div>
                        <div class="kpi-trend trend-down">
                            <i class="fas fa-pause-circle"></i>
                            <span>Contratos suspendidos</span>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-title">Mis Vencidos</div>
                        <div class="kpi-value"><?= number_format($asesorStats['con_contratos_vencidos']) ?></div>
                        <div class="kpi-trend trend-down">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Requieren renovación</span>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-title">Mi Tasa de Actividad</div>
                        <div class="kpi-value"><?= $asesorStats['tasa_actividad'] ?>%</div>
                        <div class="kpi-trend">
                            <i class="fas fa-chart-pie"></i>
                            <span>Mis activos vs mis totales</span>
                        </div>
                    </div>
                </div>

                <!-- Monthly Performance Chart -->
                <div class="chart-container">
                    <h3>
                        <i class="fas fa-chart-line"></i>
                        Nuevos Afiliados por Mes (Mis afiliados)
                    </h3>
                    <div class="chart-wrapper">
                        <canvas id="affiliatesChart"></canvas>
                    </div>
                </div>

                <!-- Recent Affiliates -->
                <div class="recent-table-container">
                    <h3>
                        <i class="fas fa-clock"></i>
                        Mis Afiliados Recientes
                    </h3>
                    <?php if (!empty($recientes)): ?>
                        <table class="recent-table">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Cédula</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Fecha Registro</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recientes as $afiliado): ?>
                                    <tr>
                                        <td><?= Html::encode($afiliado->nombres . ' ' . $afiliado->apellidos) ?></td>
                                        <td><?= Html::encode($afiliado->tipo_cedula . '-' . $afiliado->cedula) ?></td>
                                        <td><?= Html::encode($afiliado->email) ?></td>
                                        <td><?= Html::encode($afiliado->telefono) ?></td>
                                        <td><?= Yii::$app->formatter->asDate($afiliado->created_at, 'dd/MM/yyyy') ?></td>
                                        <td>
                                            <?php
                                            $badgeClass = 'badge-pending';
                                            $statusText = 'Pendiente';
                                            if ($afiliado->estatus === 'Registrado') {
                                                $badgeClass = 'badge-active';
                                                $statusText = 'Activo';
                                            } elseif ($afiliado->estatus === 'Suspendido') {
                                                $badgeClass = 'badge-expired';
                                                $statusText = 'Suspendido';
                                            }
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= $statusText ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-user-friends"></i>
                            <p>Aún no tiene afiliados registrados.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab 3: Plans -->
            <div id="tab-plans" class="tab-content">
                <div class="plans-section">
                    <!-- Individual Plans -->
                    <div class="plan-category">
                        <div class="plan-category-header">
                            <h3><i class="fas fa-user"></i> Planes Individuales</h3>
                            <p>Para edades de 0 a 59 años</p>
                        </div>
                        <table class="plan-table">
                            <thead>
                                <tr>
                                    <th>Plan</th>
                                    <th>Precio Mensual</th>
                                    <th>Cobertura</th>
                                    <th>Rango de Edad</th>
                                    <th>Anexo Maternidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($planesData['individual'] as $plan): ?>
                                    <tr>
                                        <td><strong><?= Html::encode($plan['name']) ?></strong></td>
                                        <td class="plan-price">$<?= number_format($plan['price'], 2) ?></td>
                                        <td class="plan-coverage">$<?= number_format($plan['coverage'], 0) ?></td>
                                        <td><?= Html::encode($plan['age_range']) ?></td>
                                        <td><?= Html::encode($plan['maternity']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Senior Plans -->
                    <div class="plan-category">
                        <div class="plan-category-header">
                            <h3><i class="fas fa-user-plus"></i> Planes Senior</h3>
                            <p>Para edades de 60 a 80 años</p>
                        </div>
                        <table class="plan-table">
                            <thead>
                                <tr>
                                    <th>Plan</th>
                                    <th>Precio Mensual</th>
                                    <th>Cobertura</th>
                                    <th>Rango de Edad</th>
                                    <th>Anexo Maternidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($planesData['senior'] as $plan): ?>
                                    <tr>
                                        <td><strong><?= Html::encode($plan['name']) ?></strong></td>
                                        <td class="plan-price">$<?= number_format($plan['price'], 2) ?></td>
                                        <td class="plan-coverage">$<?= number_format($plan['coverage'], 0) ?></td>
                                        <td><?= Html::encode($plan['age_range']) ?></td>
                                        <td><?= Html::encode($plan['maternity']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Benefits Summary -->
                    <div class="plan-category">
                        <div class="plan-category-header">
                            <h3><i class="fas fa-star-of-life"></i> Beneficios Incluidos</h3>
                            <p>Cobertura médica integral para tu tranquilidad</p>
                        </div>
                        <div class="benefits-grid">
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="fas fa-ambulance"></i>
                                </div>
                                <div class="benefit-content">
                                    <div class="benefit-title">Emergencias</div>
                                    <div class="benefit-desc"><?= Html::encode($planesData['benefits_summary']['emergency']) ?></div>
                                </div>
                            </div>
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="fas fa-stethoscope"></i>
                                </div>
                                <div class="benefit-content">
                                    <div class="benefit-title">Consultas Básicas</div>
                                    <div class="benefit-desc"><?= Html::encode($planesData['benefits_summary']['consultas_basicas']) ?></div>
                                </div>
                            </div>
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="fas fa-microscope"></i>
                                </div>
                                <div class="benefit-content">
                                    <div class="benefit-title">Exámenes y Diagnósticos</div>
                                    <div class="benefit-desc"><?= Html::encode($planesData['benefits_summary']['examenes']) ?></div>
                                </div>
                            </div>
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="fas fa-user-md"></i>
                                </div>
                                <div class="benefit-content">
                                    <div class="benefit-title">Consultas Especializadas</div>
                                    <div class="benefit-desc"><?= Html::encode($planesData['benefits_summary']['consultas_especializadas']) ?></div>
                                </div>
                            </div>
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="fas fa-syringe"></i>
                                </div>
                                <div class="benefit-content">
                                    <div class="benefit-title">Cirugías Electivas</div>
                                    <div class="benefit-desc"><?= Html::encode($planesData['benefits_summary']['cirugias_electivas']) ?></div>
                                </div>
                            </div>
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="fas fa-baby-carriage"></i>
                                </div>
                                <div class="benefit-content">
                                    <div class="benefit-title">Maternidad</div>
                                    <div class="benefit-desc"><?= Html::encode($planesData['benefits_summary']['maternidad']) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Note -->
                    <div style="background: var(--gray-50); border-radius: 0.5rem; padding: 1rem; border-left: 3px solid var(--sispsa-green);">
                        <p style="margin: 0; font-size: 0.75rem; color: var(--gray-600);">
                            <i class="fas fa-info-circle" style="color: var(--sispsa-green);"></i>
                            <strong>Nota:</strong> Todos los planes incluyen atención inmediata con solo presentar la cédula de identidad en la clínica contratada.
                            Los plazos de espera aplican para procedimientos electivos y consultas especializadas según el plan seleccionado.
                            Para más información, visite <a href="https://sispsa.com.ve/planes-de-medicina-prepagada/" target="_blank" style="color: var(--sispsa-blue);">sispsa.com.ve</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize Chart when DOM is visible
    document.addEventListener('DOMContentLoaded', function() {
        const monthlyData = <?= json_encode($monthlyData) ?>;

        // Only create chart if there's data and canvas exists
        if (monthlyData && monthlyData.length > 0) {
            const canvas = document.getElementById('affiliatesChart');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: monthlyData.map(item => item.month),
                        datasets: [{
                            label: 'Nuevos Afiliados',
                            data: monthlyData.map(item => item.count),
                            backgroundColor: 'rgba(0, 139, 107, 0.1)',
                            borderColor: '#008b6b',
                            borderWidth: 2,
                            pointBackgroundColor: '#2c6496',
                            pointBorderColor: '#fff',
                            pointRadius: 4,
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
                                        size: 11,
                                        family: "'Segoe UI', sans-serif"
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: '#2c6496',
                                titleFont: {
                                    size: 12
                                },
                                bodyFont: {
                                    size: 11
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#eaecf4'
                                },
                                ticks: {
                                    stepSize: 1,
                                    font: {
                                        size: 10
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 10
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }
    });

    // Tab switching functionality
    document.querySelectorAll('.tab-btn').forEach(button => {
        button.addEventListener('click', () => {
            const tabId = button.getAttribute('data-tab');

            // Remove active class from all buttons and tabs
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));

            // Add active class to current button and tab
            button.classList.add('active');
            const targetTab = document.getElementById(`tab-${tabId}`);
            if (targetTab) {
                targetTab.classList.add('active');
            }
        });
    });
</script>