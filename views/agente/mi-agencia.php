<?php
/* @var $this yii\web\View */
/* @var $agencia app\models\Agente */
/* @var $agenciaId int */
/* @var $propietario app\models\UserDatos|null */
/* @var $propietarioNombre string */
/* @var $propietarioCedula string */
/* @var $propietarioEmail string */
/* @var $propietarioTelefono string */
/* @var $propietarioDireccion string */
/* @var $rif string */
/* @var $asesores array */
/* @var $totalAsesores int */
/* @var $totalComisionPromedio float */

use yii\helpers\Html;
use yii\helpers\Url;

// Set default values to prevent undefined variable errors
$agencia = $agencia ?? null;
$agenciaId = $agenciaId ?? 0;
$propietarioNombre = $propietarioNombre ?? 'N/A';
$propietarioCedula = $propietarioCedula ?? 'N/A';
$propietarioEmail = $propietarioEmail ?? 'N/A';
$propietarioTelefono = $propietarioTelefono ?? 'N/A';
$propietarioDireccion = $propietarioDireccion ?? 'N/A';
$rif = $rif ?? 'N/A';
$asesores = $asesores ?? [];
$totalAsesores = $totalAsesores ?? 0;
$totalComisionPromedio = $totalComisionPromedio ?? 0;

$this->title = 'Mi Agencia - ' . Html::encode($agencia->nom ?? 'Mi Agencia');
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['site/dashboard-agencia']];
$this->params['breadcrumbs'][] = 'Mi Agencia';
?>

<div class="mi-agencia-container">

    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="agencia-header">
                <div class="agencia-header-content">
                    <h1 class="agencia-header-title">
                        <i class="fas fa-building agencia-header-icon"></i>
                        Mi Agencia
                    </h1>
                    <p class="agencia-header-subtitle">
                        Información general y gestión de su agencia
                    </p>
                </div>
                <div class="agencia-header-actions">
                    <a href="<?= Url::to(['site/dashboard-agencia']) ?>" class="agencia-btn agencia-btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>Volver al Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Agency Information -->
        <div class="col-lg-6 mb-4">
            <!-- General Information Card -->
            <div class="agencia-card">
                <div class="agencia-card-header">
                    <div class="agencia-card-title">
                        <i class="fas fa-info-circle"></i>
                        <span>Información General de la Agencia</span>
                    </div>
                </div>
                <div class="agencia-card-body">
                    <div class="agencia-info-grid">
                        <div class="agencia-info-item">
                            <div class="agencia-info-label">
                                <i class="fas fa-tag"></i> Nombre de la Agencia
                            </div>
                            <div class="agencia-info-value"><?= Html::encode($agencia->nom ?? 'N/A') ?></div>
                        </div>
                        <div class="agencia-info-item">
                            <div class="agencia-info-label">
                                <i class="fas fa-barcode"></i> Código SUDEASEG
                            </div>
                            <div class="agencia-info-value">
                                <?php if (!empty($agencia->sudeaseg)): ?>
                                    <span class="agencia-badge agencia-badge-primary"><?= Html::encode($agencia->sudeaseg) ?></span>
                                <?php else: ?>
                                    <span class="agencia-text-muted">No registrado</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="agencia-info-item">
                            <div class="agencia-info-label">
                                <i class="fas fa-calendar-alt"></i> Fecha de Creación
                            </div>
                            <div class="agencia-info-value">
                                <?= Yii::$app->formatter->asDate($agencia->created_at ?? date('Y-m-d'), 'dd/MM/YYYY') ?>
                            </div>
                        </div>
                        <div class="agencia-info-item">
                            <div class="agencia-info-label">
                                <i class="fas fa-chart-line"></i> Total Asesores
                            </div>
                            <div class="agencia-info-value">
                                <span class="agencia-stat-badge"><?= $totalAsesores ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Commission Percentages Card -->
            <div class="agencia-card mt-4">
                <div class="agencia-card-header">
                    <div class="agencia-card-title">
                        <i class="fas fa-percent"></i>
                        <span>Porcentajes de Comisión de la Agencia</span>
                    </div>
                    <div class="agencia-card-subtitle">Porcentajes aplicados a la agencia</div>
                </div>
                <div class="agencia-card-body">
                    <div class="agencia-percentages-grid">
                        <div class="agencia-percentage-item">
                            <div class="agencia-percentage-label">Porcentaje Venta</div>
                            <div class="agencia-percentage-value"><?= $agencia->por_venta ?? 0 ?>%</div>
                            <div class="agencia-percentage-bar">
                                <div class="agencia-percentage-fill" style="width: <?= min(100, ($agencia->por_venta ?? 0)) ?>%;"></div>
                            </div>
                        </div>
                        <div class="agencia-percentage-item">
                            <div class="agencia-percentage-label">Porcentaje Asesoría</div>
                            <div class="agencia-percentage-value"><?= $agencia->por_asesor ?? 0 ?>%</div>
                            <div class="agencia-percentage-bar">
                                <div class="agencia-percentage-fill" style="width: <?= min(100, ($agencia->por_asesor ?? 0)) ?>%;"></div>
                            </div>
                        </div>
                        <div class="agencia-percentage-item">
                            <div class="agencia-percentage-label">Porcentaje Cobranza</div>
                            <div class="agencia-percentage-value"><?= $agencia->por_cobranza ?? 0 ?>%</div>
                            <div class="agencia-percentage-bar">
                                <div class="agencia-percentage-fill" style="width: <?= min(100, ($agencia->por_cobranza ?? 0)) ?>%;"></div>
                            </div>
                        </div>
                        <div class="agencia-percentage-item">
                            <div class="agencia-percentage-label">Porcentaje Post Venta</div>
                            <div class="agencia-percentage-value"><?= $agencia->por_post_venta ?? 0 ?>%</div>
                            <div class="agencia-percentage-bar">
                                <div class="agencia-percentage-fill" style="width: <?= min(100, ($agencia->por_post_venta ?? 0)) ?>%;"></div>
                            </div>
                        </div>
                        <div class="agencia-percentage-item">
                            <div class="agencia-percentage-label">Porcentaje Agente</div>
                            <div class="agencia-percentage-value"><?= $agencia->por_agente ?? 0 ?>%</div>
                            <div class="agencia-percentage-bar">
                                <div class="agencia-percentage-fill" style="width: <?= min(100, ($agencia->por_agente ?? 0)) ?>%;"></div>
                            </div>
                        </div>
                        <div class="agencia-percentage-item">
                            <div class="agencia-percentage-label">Porcentaje Máximo</div>
                            <div class="agencia-percentage-value"><?= $agencia->por_max ?? 100 ?>%</div>
                            <div class="agencia-percentage-bar">
                                <div class="agencia-percentage-fill" style="width: <?= min(100, ($agencia->por_max ?? 100)) ?>%; background: #00a1ab;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Owner Information -->
        <div class="col-lg-6 mb-4">
            <!-- Owner Information Card -->
            <div class="agencia-card">
                <div class="agencia-card-header">
                    <div class="agencia-card-title">
                        <i class="fas fa-user-tie"></i>
                        <span>Información del Propietario</span>
                    </div>
                </div>
                <div class="agencia-card-body">
                    <div class="agencia-owner-profile">
                        <div class="agencia-owner-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="agencia-owner-info">
                            <div class="agencia-owner-name"><?= Html::encode($propietarioNombre) ?></div>
                            <div class="agencia-owner-role">Propietario / Agente Principal</div>
                        </div>
                    </div>
                    <div class="agencia-info-grid mt-4">
                        <div class="agencia-info-item">
                            <div class="agencia-info-label">
                                <i class="fas fa-id-card"></i> Cédula
                            </div>
                            <div class="agencia-info-value"><?= Html::encode($propietarioCedula) ?></div>
                        </div>
                        <div class="agencia-info-item">
                            <div class="agencia-info-label">
                                <i class="fas fa-envelope"></i> Correo Electrónico
                            </div>
                            <div class="agencia-info-value"><?= Html::encode($propietarioEmail) ?></div>
                        </div>
                        <div class="agencia-info-item">
                            <div class="agencia-info-label">
                                <i class="fas fa-phone"></i> Teléfono
                            </div>
                            <div class="agencia-info-value"><?= Html::encode($propietarioTelefono) ?></div>
                        </div>
                        <div class="agencia-info-item">
                            <div class="agencia-info-label">
                                <i class="fas fa-file-invoice"></i> RIF
                            </div>
                            <div class="agencia-info-value"><?= Html::encode($rif) ?></div>
                        </div>
                        <div class="agencia-info-item agencia-info-full">
                            <div class="agencia-info-label">
                                <i class="fas fa-map-marker-alt"></i> Dirección
                            </div>
                            <div class="agencia-info-value"><?= Html::encode($propietarioDireccion) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Card -->
            <div class="agencia-card mt-4">
                <div class="agencia-card-header">
                    <div class="agencia-card-title">
                        <i class="fas fa-chart-simple"></i>
                        <span>Resumen Rápido</span>
                    </div>
                </div>
                <div class="agencia-card-body">
                    <div class="agencia-stats-row">
                        <div class="agencia-stat-item">
                            <div class="agencia-stat-icon bg-blue">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="agencia-stat-content">
                                <div class="agencia-stat-value"><?= $totalAsesores ?></div>
                                <div class="agencia-stat-label">Asesores Registrados</div>
                            </div>
                        </div>
                        <div class="agencia-stat-item">
                            <div class="agencia-stat-icon bg-teal">
                                <i class="fas fa-percent"></i>
                            </div>
                            <div class="agencia-stat-content">
                                <div class="agencia-stat-value"><?= $totalComisionPromedio ?>%</div>
                                <div class="agencia-stat-label">Comisión Promedio</div>
                            </div>
                        </div>
                        <div class="agencia-stat-item">
                            <div class="agencia-stat-icon bg-success">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="agencia-stat-content">
                                <div class="agencia-stat-value">
                                    <?php
                                    $totalPorcentajes = ($agencia->por_venta ?? 0) + ($agencia->por_asesor ?? 0) + ($agencia->por_cobranza ?? 0) + ($agencia->por_post_venta ?? 0);
                                    echo $totalPorcentajes . '%';
                                    ?>
                                </div>
                                <div class="agencia-stat-label">Total Comisiones</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Asesores Section -->
    <div class="row">
        <div class="col-12">
            <div class="agencia-card">
                <div class="agencia-card-header">
                    <div class="agencia-card-title">
                        <i class="fas fa-user-friends"></i>
                        <span>Asesores / Intermediarios</span>
                    </div>
                    <div class="agencia-card-subtitle">Lista de asesores asociados a su agencia</div>
                </div>
                <div class="agencia-card-body p-0">
                    <div class="agencia-table-wrapper">
                        <table class="agencia-table">
                            <thead>
                                <tr>
                                    <th>Asesor</th>
                                    <th>Documento</th>
                                    <th>Contacto</th>
                                    <th class="text-center">Venta</th>
                                    <th class="text-center">Asesoría</th>
                                    <th class="text-center">Cobranza</th>
                                    <th class="text-center">Post Venta</th>
                                    <th class="text-center">Registro</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($asesores)): ?>
                                    <?php foreach ($asesores as $asesor): ?>
                                        <tr>
                                            <td class="font-medium">
                                                <div class="agencia-asesor-cell">
                                                    <div class="agencia-asesor-avatar">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div>
                                                        <?= Html::encode($asesor['nombre_completo'] ?? 'N/A') ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= Html::encode($asesor['cedula'] ?? 'N/A') ?></td>
                                            <td>
                                                <div class="agencia-contact-cell">
                                                    <div><i class="fas fa-envelope"></i> <?= Html::encode($asesor['email'] ?? 'N/A') ?></div>
                                                    <div><i class="fas fa-phone"></i> <?= Html::encode($asesor['telefono'] ?? 'N/A') ?></div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="agencia-percent-badge"><?= $asesor['porcentajes']['por_venta'] ?? 0 ?>%</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="agencia-percent-badge"><?= $asesor['porcentajes']['por_asesor'] ?? 0 ?>%</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="agencia-percent-badge"><?= $asesor['porcentajes']['por_cobranza'] ?? 0 ?>%</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="agencia-percent-badge"><?= $asesor['porcentajes']['por_post_venta'] ?? 0 ?>%</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="agencia-percent-badge"><?= $asesor['porcentajes']['por_registrar'] ?? 0 ?>%</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="agencia-action-group">
                                                    <a href="<?= Url::to(['/agente-fuerza/update', 'id' => $asesor['id'] ?? 0]) ?>" class="agencia-action-icon" title="Editar comisiones">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="<?= Url::to(['/user-datos/index-by-afiliado', 'asesor_id' => $asesor['id'] ?? 0]) ?>" class="agencia-action-icon" title="Ver afiliados">
                                                        <i class="fas fa-users"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <div class="agencia-empty-state">
                                                <i class="fas fa-user-friends"></i>
                                                <p>No hay asesores registrados en esta agencia</p>
                                                <a href="<?= Url::to(['/user/create', 'role' => 'Asesor']) ?>" class="agencia-btn agencia-btn-primary">
                                                    <i class="fas fa-user-plus mr-2"></i>Registrar Asesor
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if (!empty($asesores)): ?>
                    <div class="agencia-card-footer">
                        <a href="<?= Url::to(['/agente-fuerza/index-by-agente', 'agente_id' => $agenciaId]) ?>" class="agencia-link">
                            <span>Gestionar todos los asesores y comisiones</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Action Buttons Footer -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="agencia-footer-actions">
                <a href="<?= Url::to(['site/dashboard-agencia']) ?>" class="agencia-btn agencia-btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>Volver al Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    /* ============================================
   MI AGENCIA - MICROSOFT FLUENT DESIGN
   ============================================ */

    :root {
        --agencia-primary: #0078d4;
        --agencia-primary-dark: #106ebe;
        --agencia-success: #107c10;
        --agencia-success-light: #e8f5e9;
        --agencia-warning: #ff8c00;
        --agencia-danger: #d13438;
        --agencia-teal: #00a1ab;
        --agencia-teal-light: #e6f7f8;
        --agencia-gray-dark: #323130;
        --agencia-gray: #605e5c;
        --agencia-gray-light: #f3f2f1;
        --agencia-border: #edebe9;
        --agencia-shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.04);
        --agencia-shadow-md: 0 4px 8px rgba(0, 0, 0, 0.08);
    }

    /* Header */
    .agencia-header {
        background: linear-gradient(135deg, #1a3a6e 0%, #2a5298 100%);
        border-radius: 16px;
        padding: 28px 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        color: white;
        box-shadow: var(--agencia-shadow-md);
    }

    .agencia-header-icon {
        font-size: 32px;
        margin-right: 12px;
        opacity: 0.9;
    }

    .agencia-header-title {
        font-size: 28px;
        font-weight: 600;
        margin: 0 0 8px 0;
        letter-spacing: -0.3px;
    }

    .agencia-header-subtitle {
        font-size: 14px;
        opacity: 0.85;
        margin: 0;
        color: white !important;
    }

    .agencia-header-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    /* Buttons */
    .agencia-btn {
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        border: none;
    }

    .agencia-btn-primary {
        background: white;
        color: #1a3a6e;
    }

    .agencia-btn-primary:hover {
        background: #f0f0f0;
        transform: translateY(-2px);
        text-decoration: none;
        color: #1a3a6e;
        box-shadow: var(--agencia-shadow-sm);
    }

    .agencia-btn-secondary {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        backdrop-filter: blur(4px);
    }

    .agencia-btn-secondary:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
        text-decoration: none;
        color: white;
    }

    /* Cards */
    .agencia-card {
        background: white;
        border-radius: 16px;
        box-shadow: var(--agencia-shadow-sm);
        border: 1px solid var(--agencia-border);
        overflow: hidden;
        transition: all 0.2s ease;
    }

    .agencia-card:hover {
        box-shadow: var(--agencia-shadow-md);
    }

    .agencia-card-header {
        padding: 20px 24px 12px 24px;
        border-bottom: 1px solid var(--agencia-border);
        background: var(--agencia-gray-light);
    }

    .agencia-card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 18px;
        font-weight: 600;
        color: var(--agencia-gray-dark);
        margin-bottom: 4px;
    }

    .agencia-card-title i {
        font-size: 20px;
        color: var(--agencia-primary);
    }

    .agencia-card-subtitle {
        font-size: 13px;
        color: var(--agencia-gray);
        margin-left: 30px;
    }

    .agencia-card-body {
        padding: 24px;
    }

    .agencia-card-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--agencia-border);
        background: white;
        text-align: right;
    }

    /* Info Grid */
    .agencia-info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .agencia-info-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .agencia-info-full {
        grid-column: span 2;
    }

    .agencia-info-label {
        font-size: 12px;
        font-weight: 500;
        color: var(--agencia-gray);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .agencia-info-label i {
        margin-right: 6px;
        font-size: 12px;
    }

    .agencia-info-value {
        font-size: 15px;
        font-weight: 500;
        color: var(--agencia-gray-dark);
        word-break: break-word;
    }

    /* Badges */
    .agencia-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .agencia-badge-primary {
        background: var(--agencia-primary);
        color: white;
    }

    .agencia-stat-badge {
        display: inline-block;
        background: var(--agencia-primary);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
    }

    .agencia-text-muted {
        color: var(--agencia-gray);
    }

    /* Percentages Grid */
    .agencia-percentages-grid {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .agencia-percentage-item {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .agencia-percentage-label {
        width: 160px;
        font-size: 13px;
        font-weight: 500;
        color: var(--agencia-gray);
    }

    .agencia-percentage-value {
        width: 60px;
        font-size: 16px;
        font-weight: 700;
        color: var(--agencia-primary);
        text-align: right;
    }

    .agencia-percentage-bar {
        flex: 1;
        height: 8px;
        background: var(--agencia-gray-light);
        border-radius: 4px;
        overflow: hidden;
    }

    .agencia-percentage-fill {
        height: 100%;
        background: var(--agencia-primary);
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    /* Owner Profile */
    .agencia-owner-profile {
        display: flex;
        align-items: center;
        gap: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--agencia-border);
    }

    .agencia-owner-avatar {
        width: 80px;
        height: 80px;
        background: var(--agencia-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 48px;
    }

    .agencia-owner-name {
        font-size: 20px;
        font-weight: 600;
        color: var(--agencia-gray-dark);
    }

    .agencia-owner-role {
        font-size: 13px;
        color: var(--agencia-gray);
        margin-top: 4px;
    }

    /* Stats Row */
    .agencia-stats-row {
        display: flex;
        gap: 24px;
        flex-wrap: wrap;
    }

    .agencia-stat-item {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 12px;
        background: var(--agencia-gray-light);
        border-radius: 12px;
    }

    .agencia-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: white;
    }

    .bg-blue {
        background: var(--agencia-primary);
    }

    .bg-teal {
        background: var(--agencia-teal);
    }

    .bg-success {
        background: var(--agencia-success);
    }

    .agencia-stat-content {
        flex: 1;
    }

    .agencia-stat-value {
        font-size: 22px;
        font-weight: 700;
        color: var(--agencia-gray-dark);
    }

    .agencia-stat-label {
        font-size: 12px;
        color: var(--agencia-gray);
    }

    /* Table */
    .agencia-table-wrapper {
        overflow-x: auto;
    }

    .agencia-table {
        width: 100%;
        border-collapse: collapse;
    }

    .agencia-table th {
        text-align: left;
        padding: 14px 16px;
        background: white;
        font-weight: 600;
        font-size: 12px;
        color: var(--agencia-gray);
        border-bottom: 1px solid var(--agencia-border);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .agencia-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--agencia-border);
        font-size: 13px;
        color: var(--agencia-gray-dark);
    }

    .agencia-table tbody tr:hover {
        background: var(--agencia-gray-light);
    }

    /* Asesor Cell */
    .agencia-asesor-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .agencia-asesor-avatar {
        width: 32px;
        height: 32px;
        background: var(--agencia-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 14px;
    }

    /* Contact Cell */
    .agencia-contact-cell {
        font-size: 12px;
        line-height: 1.6;
    }

    .agencia-contact-cell i {
        width: 16px;
        color: var(--agencia-gray);
        margin-right: 6px;
    }

    /* Percent Badge */
    .agencia-percent-badge {
        display: inline-block;
        padding: 4px 10px;
        background: var(--agencia-teal-light);
        color: var(--agencia-teal);
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    /* Action Group */
    .agencia-action-group {
        display: flex;
        gap: 8px;
        justify-content: center;
    }

    .agencia-action-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--agencia-gray);
        background: transparent;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .agencia-action-icon:hover {
        background: var(--agencia-gray-light);
        color: var(--agencia-primary);
        text-decoration: none;
    }

    /* Footer Actions */
    .agencia-footer-actions {
        display: flex;
        justify-content: flex-end;
        gap: 16px;
        padding: 20px 0;
    }

    /* Link */
    .agencia-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--agencia-primary);
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
    }

    .agencia-link:hover {
        text-decoration: underline;
    }

    /* Empty State */
    .agencia-empty-state {
        text-align: center;
        padding: 48px 24px;
    }

    .agencia-empty-state i {
        font-size: 48px;
        color: var(--agencia-border);
        margin-bottom: 16px;
    }

    .agencia-empty-state p {
        color: var(--agencia-gray);
        margin-bottom: 20px;
    }

    /* Utilities */
    .text-center {
        text-align: center;
    }

    .font-medium {
        font-weight: 500;
    }

    .mt-4 {
        margin-top: 24px;
    }

    .mb-4 {
        margin-bottom: 24px;
    }

    .p-0 {
        padding: 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .agencia-header {
            flex-direction: column;
            text-align: center;
        }

        .agencia-header-actions {
            width: 100%;
            justify-content: center;
        }

        .agencia-info-grid {
            grid-template-columns: 1fr;
        }

        .agencia-info-full {
            grid-column: span 1;
        }

        .agencia-percentage-item {
            flex-direction: column;
            align-items: flex-start;
        }

        .agencia-percentage-label {
            width: 100%;
        }

        .agencia-percentage-value {
            text-align: left;
        }

        .agencia-stats-row {
            flex-direction: column;
        }

        .agencia-footer-actions {
            flex-direction: column;
        }

        .agencia-footer-actions .agencia-btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>