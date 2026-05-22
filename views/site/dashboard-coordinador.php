<?php

/**
 * @var yii\web\View $this
 * @var int $clinicaId
 * @var string $clinicaNombre
 * @var array $kpis
 */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Dashboard - Coordinador Clínica';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="dashboard-coordinador">
    <style>
        :root {
            --primary: #2c3e50;
            --primary-dark: #1a2632;
            --secondary: #3498db;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
            --info: #3498db;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --gray: #95a5a6;
            --white: #ffffff;
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
            --transition: all 0.3s ease;
        }

        .dashboard-coordinador {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            min-height: 100vh;
            padding: 2rem;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header Section */
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 24px;
            padding: 2rem;
            margin-bottom: 2rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .dashboard-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 100%;
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.1) 0%, rgba(52, 152, 219, 0.2) 100%);
            clip-path: polygon(25% 0%, 100% 0%, 100% 100%, 0% 100%);
        }

        .header-content {
            position: relative;
            z-index: 1;
        }

        .dashboard-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .dashboard-header h1 i {
            margin-right: 12px;
        }

        .dashboard-header p {
            font-size: 1rem;
            opacity: 1;
            margin-bottom: 0;
            color: white;
        }

        .clinic-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 12px;
            font-size: 0.875rem;
            display: inline-block;
            margin-top: 1rem;
            color: white;
        }

        .clinic-badge i {
            color: white;
        }

        .date-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 12px;
            font-size: 0.875rem;
            display: inline-block;
            margin-left: 1rem;
            margin-top: 1rem;
            color: white;
        }

        .date-badge i {
            color: white;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 30px -12px rgba(0, 0, 0, 0.15);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary);
        }

        .stat-card.success::before {
            background: var(--success);
        }

        .stat-card.warning::before {
            background: var(--warning);
        }

        .stat-card.danger::before {
            background: var(--danger);
        }

        .stat-card.info::before {
            background: var(--info);
        }

        .stat-card.primary::before {
            background: var(--primary);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-title {
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--gray);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            background: rgba(44, 62, 80, 0.1);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--primary);
        }

        .stat-card.success .stat-icon {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success);
        }

        .stat-card.warning .stat-icon {
            background: rgba(243, 156, 18, 0.1);
            color: var(--warning);
        }

        .stat-card.danger .stat-icon {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger);
        }

        .stat-card.info .stat-icon {
            background: rgba(52, 152, 219, 0.1);
            color: var(--info);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--dark);
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-change {
            font-size: 0.75rem;
            color: var(--gray);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .stat-change.positive {
            color: var(--success);
        }

        .stat-change.negative {
            color: var(--danger);
        }

        /* Section Title */
        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            margin-top: 1rem;
        }

        .section-title h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
        }

        .section-title a {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .section-title a:hover {
            color: var(--primary);
        }

        /* Cards Grid */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-card {
            background: var(--white);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
        }

        .info-card:hover {
            transform: translateY(-4px);
        }

        .info-card h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray);
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-card h3 i {
            margin-right: 8px;
        }

        .info-card .value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .info-card .sub {
            font-size: 0.75rem;
            color: var(--gray);
        }

        .progress-bar-container {
            margin-top: 1rem;
            background: var(--light);
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .progress-bar.success {
            background: var(--success);
        }

        .progress-bar.warning {
            background: var(--warning);
        }

        .progress-bar.danger {
            background: var(--danger);
        }

        .progress-bar.info {
            background: var(--info);
        }

        .list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--light);
        }

        .list-item:last-child {
            border-bottom: none;
        }

        .list-label {
            font-weight: 500;
            color: var(--dark);
        }

        .list-value {
            font-weight: 700;
            color: var(--primary);
        }

        /* Alert Banner */
        .alert-banner {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe8cc 100%);
            border-left: 4px solid var(--warning);
            border-radius: 16px;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .alert-banner i {
            font-size: 1.5rem;
            color: var(--warning);
        }

        .alert-banner .alert-content {
            flex: 1;
        }

        .alert-banner .alert-title {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .alert-banner .alert-message {
            font-size: 0.875rem;
            color: var(--gray);
        }

        /* Quick Action Buttons */
        .quick-action {
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: var(--transition);
        }

        .quick-action:hover {
            transform: translateX(5px);
        }

        .quick-action-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .quick-action-content {
            flex: 1;
        }

        .quick-action-title {
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }

        .quick-action-desc {
            font-size: 0.75rem;
            color: var(--gray);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-coordinador {
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .cards-grid {
                grid-template-columns: 1fr;
            }

            .stat-value {
                font-size: 2rem;
            }

            .dashboard-header h1 {
                font-size: 1.5rem;
            }
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-card,
        .info-card,
        .alert-banner {
            animation: fadeInUp 0.5s ease forwards;
        }

        .stat-card:nth-child(1) {
            animation-delay: 0s;
        }

        .stat-card:nth-child(2) {
            animation-delay: 0.05s;
        }

        .stat-card:nth-child(3) {
            animation-delay: 0.1s;
        }

        .stat-card:nth-child(4) {
            animation-delay: 0.15s;
        }
    </style>

    <div class="dashboard-container">
        <!-- Header -->
        <div class="dashboard-header">
            <div class="header-content">
                <h1>
                    <i class="fas fa-chart-line"></i>
                    Panel de Control
                </h1>
                <p>Bienvenido al panel de gestión de afiliados de su clínica</p>
                <div>
                    <span class="clinic-badge">
                        <i class="fas fa-hospital"></i> <?= Html::encode($clinicaNombre) ?>
                    </span>
                    <span class="date-badge">
                        <i class="far fa-calendar-alt"></i> <?= date('d/m/Y H:i') ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Alert for expired/expiring contracts -->
        <?php if ($kpis['expired'] > 0 || $kpis['expiringSoon'] > 0): ?>
            <div class="alert-banner">
                <i class="fas fa-exclamation-triangle"></i>
                <div class="alert-content">
                    <div class="alert-title">Atención</div>
                    <div class="alert-message">
                        <?php if ($kpis['expired'] > 0): ?>
                            <strong><?= number_format($kpis['expired']) ?></strong> contrato(s) vencido(s) |
                        <?php endif; ?>
                        <?php if ($kpis['expiringSoon'] > 0): ?>
                            <strong><?= number_format($kpis['expiringSoon']) ?></strong> contrato(s) por vencer en los próximos 30 días
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Main Stats Grid - Now showing Contract stats -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-header">
                    <span class="stat-title">Total Afiliados</span>
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-value"><?= number_format($kpis['total']) ?></div>
                <div class="stat-change">
                    <i class="fas fa-chart-line"></i>
                    <span><?= number_format($kpis['newThisMonth']) ?> nuevos este mes</span>
                </div>
            </div>

            <div class="stat-card success">
                <div class="stat-header">
                    <span class="stat-title">Contratos Activos</span>
                    <div class="stat-icon">
                        <i class="fas fa-file-contract"></i>
                    </div>
                </div>
                <div class="stat-value"><?= number_format($kpis['contratosActivos']) ?></div>
                <div class="stat-change positive">
                    <i class="fas fa-check-circle"></i>
                    <span>Contratos vigentes</span>
                </div>
            </div>

            <!-- FIXED: Suspendidos case - Now using standardized 'Suspendido' with capital S -->
            <div class="stat-card danger">
                <div class="stat-header">
                    <span class="stat-title">Contratos Suspendidos</span>
                    <div class="stat-icon">
                        <i class="fas fa-pause-circle"></i>
                    </div>
                </div>
                <div class="stat-value"><?= number_format($kpis['contratosSuspendidos']) ?></div>
                <div class="stat-change negative">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Requieren atención</span>
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-header">
                    <span class="stat-title">Tasa de Actividad</span>
                    <div class="stat-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                </div>
                <div class="stat-value"><?= round(($kpis['contratosActivos'] / max($kpis['total'], 1)) * 100, 1) ?>%</div>
                <div class="stat-change">
                    <i class="fas fa-percent"></i>
                    <span>Contratos activos vs total</span>
                </div>
            </div>
        </div>

        <!-- Secondary Stats - Only Tipo de Afiliación and Estado de Contratos -->
        <div class="cards-grid">
            <div class="info-card">
                <h3><i class="fas fa-building"></i> Tipo de Afiliación</h3>
                <div class="list-item">
                    <span class="list-label">Individuales</span>
                    <span class="list-value"><?= number_format($kpis['individuales']) ?></span>
                </div>
                <div class="list-item">
                    <span class="list-label">Corporativos</span>
                    <span class="list-value"><?= number_format($kpis['corporativos']) ?></span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar info" style="width: <?= round(($kpis['individuales'] / max($kpis['total'], 1)) * 100, 2) ?>%"></div>
                </div>
                <div class="sub" style="margin-top: 0.5rem;">
                    <?= round(($kpis['individuales'] / max($kpis['total'], 1)) * 100, 1) ?>% son afiliados individuales
                </div>
            </div>

            <div class="info-card">
                <h3><i class="fas fa-file-contract"></i> Estado de Contratos</h3>
                <div class="list-item">
                    <span class="list-label">Activos</span>
                    <span class="list-value" style="color: var(--success);"><?= number_format($kpis['contratosActivos']) ?></span>
                </div>
                <div class="list-item">
                    <span class="list-label">Suspendidos</span>
                    <span class="list-value" style="color: var(--danger);"><?= number_format($kpis['contratosSuspendidos']) ?></span>
                </div>
                <div class="list-item">
                    <span class="list-label">Por vencer (30 días)</span>
                    <span class="list-value" style="color: var(--warning);"><?= number_format($kpis['expiringSoon']) ?></span>
                </div>
                <div class="list-item">
                    <span class="list-label">Vencidos</span>
                    <span class="list-value" style="color: var(--danger);"><?= number_format($kpis['expired']) ?></span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="section-title">
            <h2><i class="fas fa-bolt"></i> Acciones Rápidas</h2>
        </div>

        <div class="cards-grid">
            <div class="info-card" style="cursor: pointer;" onclick="window.location.href='<?= Url::to(['user-datos/index-clinicas', 'clinica_id' => $clinicaId]) ?>'">
                <div class="quick-action">
                    <div class="quick-action-icon" style="background: rgba(52,152,219,0.1); color: var(--info);">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="quick-action-content">
                        <div class="quick-action-title">Ver Todos los Afiliados</div>
                        <div class="quick-action-desc">Gestionar lista completa de afiliados de su clínica</div>
                    </div>
                </div>
            </div>

            <div class="info-card" style="cursor: pointer;" onclick="window.location.href='<?= Url::to(['user-datos/reporte-afiliados']) ?>'">
                <div class="quick-action">
                    <div class="quick-action-icon" style="background: rgba(39,174,96,0.1); color: var(--success);">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="quick-action-content">
                        <div class="quick-action-title">Generar Reportes</div>
                        <div class="quick-action-desc">Exportar estadísticas y análisis detallados</div>
                    </div>
                </div>
            </div>

            <div class="info-card" style="cursor: pointer;" onclick="window.location.href='<?= Url::to(['user-datos/index']) ?>'">
                <div class="quick-action">
                    <div class="quick-action-icon" style="background: rgba(243,156,18,0.1); color: var(--warning);">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="quick-action-content">
                        <div class="quick-action-title">Nuevo Afiliado</div>
                        <div class="quick-action-desc">Registrar un nuevo afiliado en el sistema</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add click handlers for stat cards - FIXED: Using standardized 'Suspendido' with capital S
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('click', () => {
                const title = card.querySelector('.stat-title')?.innerText;
                if (title === 'Total Afiliados') {
                    window.location.href = '<?= Url::to(['user-datos/index-clinicas', 'clinica_id' => $clinicaId]) ?>';
                } else if (title === 'Contratos Activos') {
                    window.location.href = '<?= Url::to(['user-datos/index-clinicas', 'clinica_id' => $clinicaId, 'UserDatosSearch[estatus_contrato]' => 'Activo']) ?>';
                } else if (title === 'Contratos Suspendidos') {
                    // FIXED: Changed from lowercase 'suspendido' to 'Suspendido' (capital S)
                    window.location.href = '<?= Url::to(['user-datos/index-clinicas', 'clinica_id' => $clinicaId, 'UserDatosSearch[estatus_contrato]' => 'Suspendido']) ?>';
                }
            });
        });
    </script>
</div>