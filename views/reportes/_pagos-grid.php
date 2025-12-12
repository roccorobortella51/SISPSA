<?php
// app/views/reportes/_pagos-grid.php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\data\ActiveDataProvider;

/** @var yii\web\View $this */
/** @var app\models\PagosReporteSearch $searchModel */
/** @var ActiveDataProvider $dataProvider */
/** @var string $title */
/** @var array $summary ['total_monto' => float, 'total_count' => int] */
/** @var array $summaryPorClinica Resumen por clínica */
/** @var array $clinicasSeleccionadas IDs de clínicas seleccionadas */
/** @var string $startDate */
/** @var string $endDate */

// Calcula total de páginas si existe paginación
$totalPages = $dataProvider->pagination ?
    ceil($dataProvider->getTotalCount() / $dataProvider->pagination->pageSize) : 1;
$currentPage = $dataProvider->pagination ? ($dataProvider->pagination->page + 1) : 1;
?>

<div class="row">
    <!-- Encabezado Principal Ajustado -->
    <div class="col-12 mb-4">
        <div class="ms-card border-0 shadow-lg ms-fade-in" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
            <div class="ms-card-body p-4">
                <div class="row align-items-center">
                    <div class="col-lg-8 mb-3 mb-lg-0">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center shadow"
                                    style="width: 70px; height: 70px; background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);">
                                    <i class="fas fa-chart-bar text-white" style="font-size: 2.2rem;"></i>
                                </div>
                            </div>
                            <div>
                                <h1 class="display-5 fw-bold mb-1 ms-primary" style="font-size: 2rem !important;">
                                    <?= Html::encode($title) ?>
                                </h1>
                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <div class="d-flex align-items-center">
                                        <i class="far fa-calendar-alt me-2 ms-primary" style="font-size: 1.3rem;"></i>
                                        <span class="ms-body-lg fw-bold" style="font-size: 1.3rem !important;">
                                            <?= $startDate ?>
                                            <i class="fas fa-arrow-right mx-2 ms-primary"></i>
                                            <?= $endDate ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($clinicasSeleccionadas) && !in_array('todas', $clinicasSeleccionadas)): ?>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-filter me-2 ms-warning" style="font-size: 1.3rem;"></i>
                                            <span class="ms-body-lg fw-bold" style="font-size: 1.3rem !important; color: #ff8c00;">
                                                <?= count($clinicasSeleccionadas) ?> clínica(s) filtradas
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="text-center text-lg-end">
                            <div class="d-inline-block px-3 py-2 rounded-3 shadow-sm"
                                style="background: white; border-left: 4px solid #107c10;">
                                <div class="ms-body-sm text-muted mb-1" style="font-size: 1.1rem !important;">
                                    <i class="fas fa-database me-2"></i>Registros Encontrados
                                </div>
                                <div class="display-6 fw-bold text-success" style="font-size: 2.2rem !important;">
                                    <?= number_format($dataProvider->getTotalCount()) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de Resumen Ajustadas -->
    <div class="col-12 mb-4">
        <div class="row g-3">
            <!-- Total Recaudado -->
            <div class="col-xl-4 col-lg-6">
                <div class="ms-summary-card ms-summary-card-success border-0 shadow-lg ms-fade-in h-100">
                    <div class="ms-card-body p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3 shadow"
                                style="width: 60px; height: 60px; background: linear-gradient(135deg, #107c10 0%, #0e6a0e 100%);">
                                <i class="fas fa-money-bill-wave text-white" style="font-size: 1.8rem;"></i>
                            </div>
                            <div>
                                <h5 class="ms-title-sm mb-1" style="font-size: 1.3rem !important; color: #605e5c;">
                                    <i class="fas fa-chart-line me-2"></i>TOTAL RECAUDADO
                                </h5>
                                <h2 class="ms-title-lg mb-0 text-success" style="font-size: 1.8rem !important;">
                                    <?= Yii::$app->formatter->asCurrency($summary['total_monto'], 'VES') ?>
                                </h2>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-receipt me-2" style="font-size: 1.3rem; color: #107c10;"></i>
                                <span class="ms-body-lg fw-semibold" style="font-size: 1.3rem !important;">
                                    <?= number_format($summary['total_count']) ?> pagos
                                </span>
                            </div>
                            <div class="ms-badge ms-badge-success shadow" style="font-size: 1.3rem !important; padding: 0.5rem 1rem;">
                                <i class="fas fa-chart-pie me-2"></i>Total
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Distribución por Estado -->
            <div class="col-xl-4 col-lg-6">
                <div class="ms-summary-card ms-summary-card-primary border-0 shadow-lg ms-fade-in h-100" style="animation-delay: 0.1s;">
                    <div class="ms-card-body p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3 shadow"
                                style="width: 60px; height: 60px; background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);">
                                <i class="fas fa-chart-pie text-white" style="font-size: 1.8rem;"></i>
                            </div>
                            <div>
                                <h5 class="ms-title-sm mb-1" style="font-size: 1.3rem !important; color: #605e5c;">
                                    <i class="fas fa-tasks me-2"></i>DISTRIBUCIÓN POR ESTADO
                                </h5>
                                <h3 class="ms-title-md mb-0 text-primary" style="font-size: 1.6rem !important;">
                                    <?= number_format($summary['total_count']) ?> transacciones
                                </h3>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="text-center p-2 rounded-3" style="background: #dff6dd; border: 1px solid #107c10;">
                                    <div class="display-6 fw-bold text-success mb-1" style="font-size: 1.6rem !important;">
                                        <?= number_format($summary['conciliados'] ?? 0) ?>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <div class="me-2" style="width: 12px; height: 12px; border-radius: 50%; background-color: #107c10;"></div>
                                        <span class="ms-body-lg fw-semibold" style="font-size: 1.2rem !important;">Conciliados</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center p-2 rounded-3" style="background: #fff4ce; border: 1px solid #ff8c00;">
                                    <div class="display-6 fw-bold text-warning mb-1" style="font-size: 1.6rem !important;">
                                        <?= number_format($summary['pendientes'] ?? 0) ?>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <div class="me-2" style="width: 12px; height: 12px; border-radius: 50%; background-color: #ff8c00;"></div>
                                        <span class="ms-body-lg fw-semibold" style="font-size: 1.2rem !important;">Pendientes</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Promedio por Transacción -->
            <div class="col-xl-4 col-lg-6">
                <div class="ms-summary-card ms-summary-card-warning border-0 shadow-lg ms-fade-in h-100" style="animation-delay: 0.2s;">
                    <div class="ms-card-body p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3 shadow"
                                style="width: 60px; height: 60px; background: linear-gradient(135deg, #ff8c00 0%, #e67e00 100%);">
                                <i class="fas fa-calculator text-white" style="font-size: 1.8rem;"></i>
                            </div>
                            <div>
                                <h5 class="ms-title-sm mb-1" style="font-size: 1.3rem !important; color: #605e5c;">
                                    <i class="fas fa-chart-bar me-2"></i>PROMEDIO POR TRANSACCIÓN
                                </h5>
                                <?php
                                $avgAmount = $summary['total_count'] > 0 ?
                                    $summary['total_monto'] / $summary['total_count'] : 0;
                                ?>
                                <h2 class="ms-title-lg mb-0 text-warning" style="font-size: 1.8rem !important;">
                                    <?= Yii::$app->formatter->asCurrency($avgAmount, 'VES') ?>
                                </h2>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exchange-alt me-2" style="font-size: 1.3rem; color: #ff8c00;"></i>
                                <span class="ms-body-lg fw-semibold" style="font-size: 1.3rem !important;">
                                    Valor promedio
                                </span>
                            </div>
                            <div class="ms-badge ms-badge-warning shadow" style="font-size: 1.3rem !important; padding: 0.5rem 1rem;">
                                <i class="fas fa-chart-line me-2"></i>Promedio
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen por Clínica (si existe) -->
    <?php if (!empty($summaryPorClinica)): ?>
        <?= $this->render('_pagos-resumen-clinicas', [
            'summaryPorClinica' => $summaryPorClinica,
            'summary' => $summary,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'title' => $title
        ]) ?>
    <?php endif; ?>

    <!-- Panel de Detalle de Pagos - Diseño Ajustado -->
    <div class="col-12">
        <!-- Encabezado del Panel de Detalle Ajustado -->
        <div class="ms-card border-0 shadow-lg mb-3 ms-fade-in" style="background: white;">
            <div class="ms-card-body p-3">
                <div class="row align-items-center">
                    <div class="col-lg-7 mb-3 mb-lg-0">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center shadow"
                                    style="width: 60px; height: 60px; background: linear-gradient(135deg, #2c3e50 0%, #4a6491 100%);">
                                    <i class="fas fa-list-alt text-white" style="font-size: 1.8rem;"></i>
                                </div>
                            </div>
                            <div>
                                <h2 class="display-6 fw-bold mb-1" style="font-size: 1.8rem !important;">
                                    <i class="fas fa-search-dollar me-2 ms-primary"></i>Detalle de Pagos
                                </h2>
                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-layer-group me-2 ms-primary" style="font-size: 1.3rem;"></i>
                                        <span class="ms-body-lg fw-semibold" style="font-size: 1.3rem !important;">
                                            <?= number_format($dataProvider->getCount()) ?> registros visibles
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="far fa-clock me-2 ms-primary" style="font-size: 1.3rem;"></i>
                                        <span class="ms-body-lg fw-semibold" style="font-size: 1.3rem !important;">
                                            Página <?= $currentPage ?> de <?= $totalPages ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="d-flex justify-content-lg-end gap-2 flex-wrap">
                            <a href="#" id="btn-print-pdf" class="ms-btn ms-btn-danger px-3 py-2 shadow"
                                style="font-size: 1.3rem !important;" target="_blank">
                                <i class="fas fa-file-pdf me-2" style="font-size: 1.3rem;"></i>Exportar PDF
                            </a>
                            <a href="#" id="btn-export-excel" class="ms-btn ms-btn-success px-3 py-2 shadow"
                                style="font-size: 1.3rem !important;" onclick="exportToExcel()">
                                <i class="fas fa-file-excel me-2" style="font-size: 1.3rem;"></i>Exportar Excel
                            </a>
                            <button class="ms-btn ms-btn-primary px-3 py-2 shadow"
                                style="font-size: 1.3rem !important;" onclick="window.print()">
                                <i class="fas fa-print me-2" style="font-size: 1.3rem;"></i>Imprimir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Detalle Ajustada -->
        <div class="ms-card border-0 shadow-lg p-0 ms-fade-in" style="animation-delay: 0.1s;">
            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                <table class="ms-table ms-table-striped mb-0" style="min-width: 1100px;">
                    <thead style="position: sticky; top: 0; z-index: 10;">
                        <tr style="background: linear-gradient(135deg, #2c3e50 0%, #4a6491 100%) !important;">
                            <th class="text-center py-3" style="width: 50px; border-right: 2px solid rgba(255,255,255,0.2);">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="text-white fw-bold" style="font-size: 1.3rem !important;">#</span>
                                </div>
                            </th>
                            <th class="text-center py-3" style="width: 100px; border-right: 2px solid rgba(255,255,255,0.2);">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fas fa-fingerprint me-2 text-white" style="font-size: 1.3rem;"></i>
                                    <span class="text-white fw-bold" style="font-size: 1.3rem !important;">ID Pago</span>
                                </div>
                            </th>
                            <th class="py-3 ps-3" style="min-width: 220px; border-right: 2px solid rgba(255,255,255,0.2);">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user me-2 text-white" style="font-size: 1.3rem;"></i>
                                    <span class="text-white fw-bold" style="font-size: 1.3rem !important;">Afiliado</span>
                                </div>
                            </th>
                            <th class="text-center py-3" style="width: 120px; border-right: 2px solid rgba(255,255,255,0.2);">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fas fa-id-card me-2 text-white" style="font-size: 1.3rem;"></i>
                                    <span class="text-white fw-bold" style="font-size: 1.3rem !important;">Cédula</span>
                                </div>
                            </th>
                            <th class="text-center py-3" style="width: 150px; border-right: 2px solid rgba(255,255,255,0.2);">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fas fa-money-bill-wave me-2 text-white" style="font-size: 1.3rem;"></i>
                                    <span class="text-white fw-bold" style="font-size: 1.3rem !important;">Monto (Bs.)</span>
                                </div>
                            </th>
                            <th class="text-center py-3" style="width: 140px; border-right: 2px solid rgba(255,255,255,0.2);">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="far fa-calendar-alt me-2 text-white" style="font-size: 1.3rem;"></i>
                                    <span class="text-white fw-bold" style="font-size: 1.3rem !important;">Fecha</span>
                                </div>
                            </th>
                            <th class="text-center py-3" style="width: 150px; border-right: 2px solid rgba(255,255,255,0.2);">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fas fa-credit-card me-2 text-white" style="font-size: 1.3rem;"></i>
                                    <span class="text-white fw-bold" style="font-size: 1.3rem !important;">Método</span>
                                </div>
                            </th>
                            <th class="text-center py-3" style="width: 150px; border-right: 2px solid rgba(255,255,255,0.2);">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fas fa-chart-line me-2 text-white" style="font-size: 1.3rem;"></i>
                                    <span class="text-white fw-bold" style="font-size: 1.3rem !important;">Estado</span>
                                </div>
                            </th>
                            <th class="text-center py-3 pe-3" style="min-width: 200px;">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fas fa-hospital me-2 text-white" style="font-size: 1.3rem;"></i>
                                    <span class="text-white fw-bold" style="font-size: 1.3rem !important;">Clínica</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $models = $dataProvider->getModels();
                        $totalMonto = 0;
                        $consecutivo = ($dataProvider->pagination ? ($dataProvider->pagination->page * $dataProvider->pagination->pageSize) : 0) + 1;

                        foreach ($models as $model):
                            $totalMonto += $model->monto_usd;
                        ?>
                            <tr class="ms-slide-in" style="animation-delay: <?= $consecutivo * 0.02 ?>s; border-bottom: 1px solid #f8f9fa;">
                                <!-- Número Consecutivo -->
                                <td class="text-center py-2" style="background-color: #f8fafc; border-right: 2px solid #e9ecef;">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <span class="ms-badge ms-badge-info shadow-sm"
                                            style="font-size: 1.3rem !important; padding: 0.5rem 0.75rem; min-width: 40px;">
                                            <?= $consecutivo++ ?>
                                        </span>
                                    </div>
                                </td>

                                <!-- ID Pago -->
                                <td class="text-center py-2" style="border-right: 2px solid #e9ecef;">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <span class="ms-badge ms-badge-info shadow-sm"
                                            style="font-size: 1.3rem !important; padding: 0.5rem 0.75rem;">
                                            #<?= $model->id ?>
                                        </span>
                                    </div>
                                </td>

                                <!-- Afiliado -->
                                <td class="py-2 ps-3" style="border-right: 2px solid #e9ecef;">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm"
                                            style="width: 45px; height: 45px; background: linear-gradient(135deg, #c7e0f4 0%, #a5d2f4 100%);">
                                            <i class="fas fa-user ms-primary" style="font-size: 1.4rem;"></i>
                                        </div>
                                        <div>
                                            <div class="ms-body-lg fw-bold mb-1" style="font-size: 1.3rem !important;">
                                                <?= $model->userDatos ?
                                                    Html::encode($model->userDatos->nombres . ' ' . $model->userDatos->apellidos) :
                                                    'N/A' ?>
                                            </div>
                                            <?php if ($model->userDatos && $model->userDatos->telefono): ?>
                                                <div class="ms-body text-muted" style="font-size: 1.2rem !important;">
                                                    <i class="fas fa-phone me-1"></i><?= $model->userDatos->telefono ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <!-- Cédula -->
                                <td class="text-center py-2" style="border-right: 2px solid #e9ecef;">
                                    <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                        <span class="ms-body-lg fw-bold" style="font-size: 1.3rem !important;">
                                            <?= $model->userDatos && $model->userDatos->cedula ?
                                                Html::encode($model->userDatos->cedula) : 'N/A' ?>
                                        </span>
                                    </div>
                                </td>

                                <!-- Monto -->
                                <td class="text-center py-2" style="border-right: 2px solid #e9ecef;">
                                    <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                        <span class="display-6 fw-bold text-success mb-1" style="font-size: 1.4rem !important;">
                                            <?= Yii::$app->formatter->asCurrency($model->monto_usd, 'VES') ?>
                                        </span>
                                        <small class="ms-body-sm text-muted" style="font-size: 1.1rem !important;">
                                            <?= Yii::$app->formatter->asCurrency($model->monto_usd, 'USD') ?>
                                        </small>
                                    </div>
                                </td>

                                <!-- Fecha -->
                                <td class="text-center py-2" style="border-right: 2px solid #e9ecef;">
                                    <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                        <span class="ms-body-lg fw-bold mb-1" style="font-size: 1.3rem !important;">
                                            <?= Yii::$app->formatter->asDate($model->fecha_pago, 'php:d/m/Y') ?>
                                        </span>
                                        <div class="d-flex align-items-center">
                                            <i class="far fa-clock me-1" style="font-size: 1.1rem; color: #605e5c;"></i>
                                            <span class="ms-body-sm text-muted" style="font-size: 1.1rem !important;">
                                                <?= Yii::$app->formatter->asDate($model->fecha_pago, 'php:l') ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Método de Pago -->
                                <td class="text-center py-2" style="border-right: 2px solid #e9ecef;">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <?php
                                        $icon = 'fa-wallet';
                                        $color = '#605e5c';
                                        $bgColor = '#f3f2f1';
                                        $text = $model->metodo_pago ?: 'N/A';

                                        if ($model->metodo_pago) {
                                            $method = strtolower($model->metodo_pago);
                                            if (strpos($method, 'transferencia') !== false) {
                                                $icon = 'fa-exchange-alt';
                                                $color = '#0078d4';
                                                $bgColor = '#c7e0f4';
                                            } elseif (strpos($method, 'efectivo') !== false) {
                                                $icon = 'fa-money-bill';
                                                $color = '#107c10';
                                                $bgColor = '#dff6dd';
                                            } elseif (strpos($method, 'tarjeta') !== false) {
                                                $icon = 'fa-credit-card';
                                                $color = '#d13438';
                                                $bgColor = '#fde7e9';
                                            }
                                        }
                                        ?>
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm"
                                            style="width: 40px; height: 40px; background-color: <?= $bgColor ?>;">
                                            <i class="fas <?= $icon ?>" style="font-size: 1.3rem; color: <?= $color ?>;"></i>
                                        </div>
                                        <span class="ms-body-lg fw-semibold" style="font-size: 1.3rem !important;"><?= $text ?></span>
                                    </div>
                                </td>

                                <!-- Estado -->
                                <td class="text-center py-2" style="border-right: 2px solid #e9ecef;">
                                    <?php if ($model->estatus === 'Conciliado'): ?>
                                        <div class="ms-badge ms-badge-success shadow-sm"
                                            style="font-size: 1.3rem !important; padding: 0.5rem 1rem;">
                                            <i class="fas fa-check-circle me-1"></i>
                                            <?= $model->estatus ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="ms-badge ms-badge-warning shadow-sm"
                                            style="font-size: 1.3rem !important; padding: 0.5rem 1rem;">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= $model->estatus ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Clínica -->
                                <td class="text-center py-2 pe-3">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm"
                                            style="width: 40px; height: 40px; background: linear-gradient(135deg, #e6f2fa 0%, #d4e8fa 100%);">
                                            <i class="fas fa-hospital ms-primary" style="font-size: 1.3rem;"></i>
                                        </div>
                                        <div class="text-start">
                                            <div class="ms-body-lg fw-bold mb-1" style="font-size: 1.3rem !important;">
                                                <?php
                                                $clinicaNombre = 'Sin Clínica';
                                                if ($model->contratos && count($model->contratos) > 0) {
                                                    foreach ($model->contratos as $contrato) {
                                                        if ($contrato->clinica) {
                                                            $clinicaNombre = Html::encode($contrato->clinica->nombre);
                                                            break;
                                                        }
                                                    }
                                                }
                                                echo $clinicaNombre;
                                                ?>
                                            </div>
                                            <?php if ($model->contratos && count($model->contratos) > 0): ?>
                                                <div class="ms-body text-muted" style="font-size: 1.1rem !important;">
                                                    <i class="fas fa-file-contract me-1"></i><?= count($model->contratos) ?> contrato(s)
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                    <!-- Total del Detalle Ajustado -->
                    <?php if (!empty($models)): ?>
                        <tfoot style="position: sticky; bottom: 0; z-index: 10;">
                            <tr style="background: linear-gradient(135deg, #2c3e50 0%, #4a6491 100%);">
                                <td colspan="4" class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-chart-bar me-3" style="font-size: 1.8rem; color: #ffffff;"></i>
                                        <div>
                                            <h3 class="mb-1 fw-bold" style="color: #ffffff; font-size: 1.6rem !important;">
                                                TOTAL DETALLE
                                            </h3>
                                            <p class="mb-0" style="color: rgba(255, 255, 255, 0.85); font-size: 1.3rem !important;">
                                                <?= count($models) ?> registros mostrados
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center py-3" colspan="2">
                                    <div class="d-flex flex-column align-items-center">
                                        <h2 class="mb-1 fw-bold" style="color: #ffffff; font-size: 1.8rem !important;">
                                            <?= Yii::$app->formatter->asCurrency($totalMonto, 'VES') ?>
                                        </h2>
                                        <p class="mb-0" style="color: rgba(255, 255, 255, 0.85); font-size: 1.3rem !important;">
                                            Monto Total
                                        </p>
                                    </div>
                                </td>
                                <td class="text-center py-3" colspan="3">
                                    <div class="d-flex justify-content-center align-items-center h-100">
                                        <div class="d-flex gap-4">
                                            <div class="text-center">
                                                <div class="d-flex align-items-center justify-content-center mb-1">
                                                    <div class="me-2" style="width: 12px; height: 12px; border-radius: 3px; background-color: #28a745;"></div>
                                                    <span style="color: #ffffff; font-size: 1.3rem !important;">Conciliados</span>
                                                </div>
                                                <div class="display-6 fw-bold" style="color: #ffffff; font-size: 1.4rem !important;">
                                                    <?= number_format($summary['conciliados'] ?? 0) ?>
                                                </div>
                                            </div>
                                            <div class="text-center">
                                                <div class="d-flex align-items-center justify-content-center mb-1">
                                                    <div class="me-2" style="width: 12px; height: 12px; border-radius: 3px; background-color: #ffc107;"></div>
                                                    <span style="color: #ffffff; font-size: 1.3rem !important;">Pendientes</span>
                                                </div>
                                                <div class="display-6 fw-bold" style="color: #ffffff; font-size: 1.4rem !important;">
                                                    <?= number_format($summary['pendientes'] ?? 0) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>

            <!-- Paginación y Controles Ajustados -->
            <div class="ms-card-footer py-3" style="background: #faf9f8;">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-3 mb-lg-0">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm"
                                style="width: 45px; height: 45px; background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);">
                                <i class="fas fa-info-circle text-white" style="font-size: 1.3rem;"></i>
                            </div>
                            <div>
                                <h4 class="ms-title-sm mb-1" style="font-size: 1.3rem !important;">
                                    Resumen de Datos
                                </h4>
                                <div class="d-flex flex-wrap gap-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-eye me-2 ms-primary" style="font-size: 1.1rem;"></i>
                                        <span class="ms-body-lg fw-semibold" style="font-size: 1.2rem !important;">
                                            <?= $dataProvider->getCount() ?> visibles
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-database me-2 ms-primary" style="font-size: 1.1rem;"></i>
                                        <span class="ms-body-lg fw-semibold" style="font-size: 1.2rem !important;">
                                            <?= $dataProvider->getTotalCount() ?> total
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <?= \yii\widgets\LinkPager::widget([
                            'pagination' => $dataProvider->pagination,
                            'options' => ['class' => 'pagination justify-content-end mb-0'],
                            'linkContainerOptions' => ['class' => 'page-item'],
                            'linkOptions' => [
                                'class' => 'page-link ms-focus shadow-sm',
                                'style' => 'font-size: 1.2rem !important; padding: 0.5rem 0.75rem;'
                            ],
                            'disabledListItemSubTagOptions' => [
                                'class' => 'page-link',
                                'style' => 'font-size: 1.2rem !important; padding: 0.5rem 0.75rem;'
                            ],
                            'maxButtonCount' => 5,
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estilos Adicionales Ajustados -->
<style>
    /* Scrollbar personalizado para la tabla - Ajustado */
    .table-responsive::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 5px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);
        border-radius: 5px;
        border: 2px solid #f1f1f1;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #106ebe 0%, #005a9e 100%);
    }

    /* Efecto hover para filas de la tabla - Ajustado */
    .ms-table tbody tr {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .ms-table tbody tr:hover {
        transform: translateX(3px);
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        background-color: rgba(0, 120, 212, 0.05) !important;
    }

    /* Ajustes responsivos - Ajustado */
    @media (max-width: 1200px) {
        .table-responsive {
            max-height: 400px;
        }
    }

    @media (max-width: 992px) {

        .display-5,
        .display-6 {
            font-size: calc(1.2rem + 0.5vw) !important;
        }

        .ms-body-lg,
        .ms-title-lg,
        .ms-title-md {
            font-size: calc(1rem + 0.3vw) !important;
        }
    }
</style>