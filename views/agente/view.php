<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\UserHelper;

/** @var yii\web\View $this */
/** @var app\models\Agente $model */

$this->title = 'DETALLES DE LA AGENCIA: ' . Html::encode($model->nom);
$this->params['breadcrumbs'][] = ['label' => 'AGENCIAS', 'url' => ['index']];
$this->params['breadcrumbs'][] = Html::encode($model->nom);
\yii\web\YiiAsset::register($this);

function formatPercentage($value)
{
    // show percentages with 2 decimals (e.g. 12.34%)
    return Yii::$app->formatter->asPercent((float)$value / 100, 2);
}

function formatDateTime($value)
{
    return $value ? Yii::$app->formatter->asDatetime($value) : 'N/A';
}

$ownerContactInfo = UserHelper::getAgenteOwnerContactInfo($model->id);
$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol == 'GERENTE-COMERCIALIZACION');

// --- DATOS DE EJEMPLO PARA LA GRÁFICA ---
$gananciasPorMes = [
    'Enero' => 12500,
    'Febrero' => 14300,
    'Marzo' => 13800,
    'Abril' => 16500,
    'Mayo' => 17200,
    'Junio' => 15300,
];

// Preparamos los datos y las etiquetas para el JavaScript
$meses = array_keys($gananciasPorMes);
$datos = array_values($gananciasPorMes);

// Codificamos los datos para que JavaScript pueda usarlos fácilmente
$mesesJs = json_encode($meses);
$datosJs = json_encode($datos);

?>

<style>
    /* Microsoft Fluent Design System - Professional Styling for View */
    .main-container {
        padding: 0;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Header Section - Same size as index view */
    .header-section {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        border-radius: 8px 8px 0 0;
        margin-bottom: 2rem;
        overflow: hidden;
    }

    /* Title Row - Same padding as index header */
    .title-row {
        padding: 1rem 2rem;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .title-row h1 {
        font-size: 1.9rem !important;
        font-weight: 800;
        color: #ffffff !important;
        margin: 0;
        font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .title-row h1 i {
        margin-right: 12px;
        font-size: 1.35rem;
    }

    /* Buttons Row */
    .buttons-row {
        padding: 0.75rem 2rem 1rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        width: 100%;
    }

    .left-buttons {
        display: flex;
        gap: 12px;
    }

    .right-buttons {
        display: flex;
        gap: 12px;
    }

    /* Button Styles - Matching index page */
    .btn-base {
        padding: 8px 20px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }

    .btn-base:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .btn-primary-custom {
        background: linear-gradient(135deg, #00bcd4 0%, #0097a7 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(0, 188, 212, 0.2);
        font-size: 1rem;
    }

    .btn-primary-custom:hover {
        background: linear-gradient(135deg, #0097a7 0%, #00838f 100%);
        box-shadow: 0 4px 12px rgba(0, 188, 212, 0.3);
        color: white;
    }

    .btn-blue {
        background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(255, 152, 0, 0.2);
        font-size: 1rem;

    }

    .btn-blue:hover {
        background: linear-gradient(135deg, #f57c00 0%, #ef6c00 100%);
        box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
        color: white;
    }

    .btn-gray {
        background: linear-gradient(135deg, #78909c 0%, #607d8b 100%);
        color: white !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        font-size: 1rem;

    }

    .btn-gray:hover {
        background: linear-gradient(135deg, #607d8b 0%, #546e7a 100%);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        color: white !important;
    }

    /* Panel Styles */
    .ms-panel {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1.6px 3.6px 0 rgba(0, 0, 0, 0.132), 0 0.3px 0.9px 0 rgba(0, 0, 0, 0.108);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .ms-panel-body {
        padding: 2rem;
    }

    /* Section Title - Larger */
    .section-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1e3c72;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e9ecef;
        display: flex;
        align-items: center;
    }

    .section-title i {
        font-size: 1.6rem;
        margin-right: 12px;
    }

    /* ============================================ */
    /* SUBTLE ELEGANT PERCENTAGE CARDS - SINGLE ROW */
    /* ============================================ */
    .percentage-cards-wrapper {
        overflow-x: auto;
        margin: 0 -0.5rem;
        padding: 0 0.5rem;
    }

    .percentage-cards-row {
        display: flex;
        flex-direction: row;
        gap: 1.25rem;
        justify-content: space-between;
        min-width: min-content;
    }

    .percentage-card {
        flex: 1;
        min-width: 140px;
        background: #ffffff;
        border-radius: 12px;
        padding: 1.25rem 0.75rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: default;
        position: relative;
        border: 1px solid #eef2f6;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    }

    .percentage-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        border-radius: 12px 12px 0 0;
    }

    .percentage-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        border-color: transparent;
    }

    /* Card icon */
    .card-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 0.75rem auto;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.3rem;
        transition: all 0.3s ease;
    }

    .percentage-card:hover .card-icon {
        transform: scale(1.05);
    }

    /* Card title */
    .card-title {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #8e9eae;
        margin-bottom: 0.5rem;
    }

    /* Card percentage value - LARGER & BOLDER */
    .card-value {
        font-size: 2.2rem !important;
        font-weight: 800 !important;
        margin: 0;
        line-height: 1.2;
        letter-spacing: -0.5px;
    }

    /* Subtle individual card themes */
    .card-venta::before {
        background: #3b82f6;
    }

    .card-venta .card-icon {
        background: #eff6ff;
        color: #3b82f6;
    }

    .card-venta .card-value {
        color: #3b82f6;
    }

    .card-asesoria::before {
        background: #10b981;
    }

    .card-asesoria .card-icon {
        background: #ecfdf5;
        color: #10b981;
    }

    .card-asesoria .card-value {
        color: #10b981;
    }

    .card-cobranza::before {
        background: #f59e0b;
    }

    .card-cobranza .card-icon {
        background: #fffbeb;
        color: #f59e0b;
    }

    .card-cobranza .card-value {
        color: #f59e0b;
    }

    .card-postventa::before {
        background: #ef4444;
    }

    .card-postventa .card-icon {
        background: #fef2f2;
        color: #ef4444;
    }

    .card-postventa .card-value {
        color: #ef4444;
    }

    .card-agente::before {
        background: #8b5cf6;
    }

    .card-agente .card-icon {
        background: #f5f3ff;
        color: #8b5cf6;
    }

    .card-agente .card-value {
        color: #8b5cf6;
    }

    .card-maximo::before {
        background: #06b6d4;
    }

    .card-maximo .card-icon {
        background: #ecfeff;
        color: #06b6d4;
    }

    .card-maximo .card-value {
        color: #06b6d4;
    }

    /* Info Cards */
    .info-card-body {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        transition: all 0.2s ease;
        height: 100%;
    }

    .info-card-body:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        background: #ffffff;
    }

    .info-card-body h4 {
        font-size: 1rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        margin-bottom: 1rem;
    }

    .info-card-body .h4 {
        font-size: 2rem !important;
        font-weight: 600;
        color: #0078d4;
        margin: 0;
    }

    .info-card-body .h5 {
        font-size: 1.3rem !important;
        font-weight: 500;
        margin: 0;
    }

    /* Información General Section - Larger Fonts */
    .info-general .text-gray-700 {
        color: #495057;
        font-size: 1.3rem !important;
        margin-bottom: 1rem !important;
    }

    .info-general .field-value {
        font-size: 1.3rem !important;
        font-weight: 500;
        color: #2c3e50;
        margin-left: 8px;
        display: inline-block;
    }

    .info-general strong {
        font-size: 1.3rem !important;
        font-weight: 600;
        color: #1e3c72;
    }

    /* Text Styles - Enlarged */
    .text-gray-700 {
        color: #495057;
    }

    .text-lg-18 {
        font-size: 1.2rem !important;
        line-height: 1.5;
    }

    .field-value {
        font-size: 1.2rem !important;
        font-weight: 500;
        color: #2c3e50;
        margin-left: 8px;
        display: inline-block;
    }

    .field-value-date {
        font-size: 1.3rem !important;
        font-weight: 500;
        color: #0078d4;
    }

    /* SUDEASEG Badge - Larger */
    .sudeaseg-badge-large {
        background: linear-gradient(135deg, #5c2e91 0%, #7b4b9e 100%);
        color: #ffffff !important;
        font-size: 1.3rem !important;
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 8px;
        display: inline-block;
        font-family: 'Consolas', 'Courier New', monospace;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Border Top */
    .border-top {
        border-top: 1px solid #e9ecef !important;
    }

    /* Chart Container */
    .chart-container {
        height: 350px;
        position: relative;
    }

    /* Row Gaps */
    .row.g-3 {
        margin-right: -0.75rem;
        margin-left: -0.75rem;
    }

    .row.g-3>[class^="col-"] {
        padding-right: 0.75rem;
        padding-left: 0.75rem;
        margin-bottom: 1rem;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .percentage-cards-row {
            gap: 1rem;
        }

        .percentage-card {
            min-width: 120px;
        }

        .card-value {
            font-size: 1.8rem !important;
        }

        .card-icon {
            width: 40px;
            height: 40px;
            font-size: 1.1rem;
        }
    }

    @media (max-width: 768px) {
        .title-row {
            padding: 0.75rem 1.5rem;
        }

        .title-row h1 {
            font-size: 1.1rem !important;
        }

        .title-row h1 i {
            font-size: 1.1rem;
        }

        .buttons-row {
            flex-direction: column;
            padding: 0.5rem 1.5rem 0.75rem 1.5rem;
        }

        .left-buttons {
            flex-direction: column;
            width: 100%;
        }

        .btn-base {
            justify-content: center;
            width: 100%;
        }

        .section-title {
            font-size: 1.2rem;
        }

        .section-title i {
            font-size: 1.3rem;
        }

        .percentage-cards-wrapper {
            margin: 0 -1rem;
            padding: 0 1rem;
        }

        .percentage-cards-row {
            gap: 0.75rem;
        }

        .percentage-card {
            min-width: 100px;
            padding: 0.75rem 0.5rem;
        }

        .card-value {
            font-size: 1.4rem !important;
            font-weight: 800 !important;
        }

        .card-title {
            font-size: 0.6rem;
        }

        .card-icon {
            width: 32px;
            height: 32px;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .sudeaseg-badge-large {
            font-size: 1rem !important;
            padding: 6px 12px;
        }

        .field-value {
            font-size: 1rem !important;
        }

        .text-lg-18 {
            font-size: 1rem !important;
        }

        .info-general .text-gray-700,
        .info-general .field-value,
        .info-general strong {
            font-size: 1rem !important;
        }
    }

    /* Color Utilities */
    .text-blue-600 {
        color: #0078d4;
    }

    .text-purple-600 {
        color: #5c2e91;
    }

    .text-green-600 {
        color: #107c10;
    }

    .text-info {
        color: #0078d4 !important;
    }

    .text-dark {
        color: #2c3e50 !important;
    }

    .text-muted {
        color: #6c757d !important;
    }

    .bg-light {
        background-color: #f8f9fa !important;
    }

    .mt-4 {
        margin-top: 1.5rem;
    }

    .pt-4 {
        padding-top: 1.5rem;
    }

    .mb-2 {
        margin-bottom: 0.5rem;
    }

    .mb-3 {
        margin-bottom: 1rem;
    }

    .mr-2 {
        margin-right: 0.5rem;
    }

    .mr-3 {
        margin-right: 1rem;
    }

    .ml-3 {
        margin-left: 1rem;
    }

    .text-decoration-none {
        text-decoration: none;
    }

    .text-primary {
        color: #0078d4 !important;
    }
</style>

<div class="main-container agente-view">
    <div class="header-section">
        <!-- First Row: Title only - Centered -->
        <div class="title-row" style="text-align: center; width: 100%;">
            <h1 style="display: inline-block; margin: 0 auto;">
                <i class="fas fa-building"></i> <?= Html::encode($this->title) ?>
            </h1>
        </div>

        <!-- Second Row: Buttons - Left and Right alignment -->
        <div class="buttons-row">
            <div class="left-buttons">
                <?php if ($permisos) { ?>
                    <?= Html::a('<i class="fas fa-edit"></i> ACTUALIZAR', ['update', 'id' => $model->id], ['class' => 'btn-base btn-blue']) ?>
                <?php } ?>
                <?= Html::a('<i class="fas fa-undo"></i> VOLVER', ['index'], ['class' => 'btn-base btn-gray']) ?>
            </div>
            <div class="right-buttons" style="margin-left: auto;">
                <a href="<?= Url::to(['agente-fuerza/index-by-agente', 'agente_id' => $model->id]) ?>" class="btn-base btn-primary-custom">
                    <i class="fas fa-users"></i> FUERZA DE VENTAS
                </a>
            </div>
        </div>
    </div>

    <!-- Información General de la Agencia - with SUDEASEG -->
    <div class="ms-panel info-general">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-building text-blue-600"></i> Información General de la Agencia
            </h3>
            <div class="row">
                <div class="col-md-6">
                    <p class="text-gray-700 mb-3">
                        <strong>Nombre de la Agencia:</strong>
                        <span class="field-value"><?= Html::encode($model->nom) ?></span>
                    </p>
                    <p class="text-gray-700 mb-3">
                        <strong>Código SUDEASEG:</strong>
                        <span class="sudeaseg-badge-large ml-3">
                            <?= !empty($model->sudeaseg) ? Html::encode($model->sudeaseg) : '—' ?>
                        </span>
                    </p>
                    <p class="text-gray-700 mb-3">
                        <strong>Nombre del Propietario:</strong>
                        <span class="field-value"><?= Html::encode(($model->propietario->nombres ?? 'N/A') . ' ' . ($model->propietario->apellidos ?? '')) ?></span>
                    </p>
                    <p class="text-gray-700 mb-3">
                        <strong>RIF:</strong>
                        <span class="field-value"><?= Html::encode($ownerContactInfo['rif']) ?></span>
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="text-gray-700 mb-3">
                        <strong>Email:</strong>
                        <span class="field-value"><?= Html::a(Html::encode($ownerContactInfo['email']), 'mailto:' . Html::encode($ownerContactInfo['email']), ['class' => 'text-primary text-decoration-none']) ?></span>
                    </p>
                    <p class="text-gray-700 mb-3">
                        <strong>Teléfono:</strong>
                        <span class="field-value"><?= Html::encode($ownerContactInfo['telefono']) ?></span>
                    </p>
                    <p class="text-gray-700 mb-3">
                        <strong>Cédula:</strong>
                        <span class="field-value"><?= Html::encode($model->propietario->cedula ?? 'N/A') ?></span>
                    </p>
                </div>
            </div>
            <p class="text-gray-700 mt-4 pt-4 border-top">
                <strong>Dirección:</strong>
                <span class="field-value"><?= nl2br(Html::encode($ownerContactInfo['direccion'])) ?></span>
            </p>
        </div>
    </div>

    <!-- Porcentajes de Comisión - SUBTLE CARDS IN SINGLE ROW -->
    <div class="ms-panel">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-percent text-purple-600"></i> Porcentajes de Comisión
            </h3>
            <div class="percentage-cards-wrapper">
                <div class="percentage-cards-row">
                    <!-- Venta Card -->
                    <div class="percentage-card card-venta">
                        <div class="card-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="card-title">Venta</div>
                        <div class="card-value"><?= formatPercentage($model->por_venta) ?></div>
                    </div>

                    <!-- Asesoría Card -->
                    <div class="percentage-card card-asesoria">
                        <div class="card-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div class="card-title">Asesoría</div>
                        <div class="card-value"><?= formatPercentage($model->por_asesor) ?></div>
                    </div>

                    <!-- Cobranza Card -->
                    <div class="percentage-card card-cobranza">
                        <div class="card-icon">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div class="card-title">Cobranza</div>
                        <div class="card-value"><?= formatPercentage($model->por_cobranza) ?></div>
                    </div>

                    <!-- Post Venta Card -->
                    <div class="percentage-card card-postventa">
                        <div class="card-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div class="card-title">Post Venta</div>
                        <div class="card-value"><?= formatPercentage($model->por_post_venta) ?></div>
                    </div>

                    <!-- Agente Card -->
                    <div class="percentage-card card-agente">
                        <div class="card-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="card-title">Agente</div>
                        <div class="card-value"><?= formatPercentage($model->por_agente) ?></div>
                    </div>

                    <!-- Porcentaje Máximo Card -->
                    <div class="percentage-card card-maximo">
                        <div class="card-icon">
                            <i class="fas fa-chart-simple"></i>
                        </div>
                        <div class="card-title">Porcentaje Máximo</div>
                        <div class="card-value"><?= formatPercentage($model->por_max) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ganancias de los Últimos 6 Meses -->
    <div class="ms-panel">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-chart-line text-green-600"></i> Ganancias de los Últimos 6 Meses
            </h3>
            <div class="chart-container">
                <canvas id="gananciasChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Fechas de Gestión -->
    <div class="ms-panel">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-calendar-alt"></i> Fechas de Gestión
            </h3>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h4 class="text-muted">Fecha de Creación</h4>
                        <p class="h5 text-dark">
                            <span class="field-value-date"><?= formatDateTime($model->created_at) ?></span>
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h4 class="text-muted">Última Actualización</h4>
                        <p class="h5 text-dark">
                            <span class="field-value-date"><?= formatDateTime($model->updated_at) ?></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const meses = <?= $mesesJs ?>;
        const datos = <?= $datosJs ?>;

        const ctx = document.getElementById('gananciasChart').getContext('2d');
        const gananciasChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: meses,
                datasets: [{
                    label: 'Ganancias',
                    data: datos,
                    backgroundColor: 'rgba(0, 120, 212, 0.1)',
                    borderColor: '#0078d4',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#0078d4',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        ticks: {
                            callback: function(val, index) {
                                const monto = datos[index].toLocaleString('en-US', {
                                    style: 'currency',
                                    currency: 'USD',
                                    minimumFractionDigits: 2
                                });
                                return [meses[index], monto];
                            },
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            },
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            color: '#e9ecef'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Ganancia: $' + context.raw.toLocaleString();
                            }
                        },
                        backgroundColor: '#1e3c72',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        bodyFont: {
                            size: 13
                        },
                        titleFont: {
                            size: 13,
                            weight: 'bold'
                        }
                    }
                }
            }
        });
    });
</script>