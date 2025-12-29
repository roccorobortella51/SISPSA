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

<div class="col-12 mb-4">
    <div class="ms-card border-0 shadow-lg ms-fade-in"
        style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">

        <div class="col-12 resumen-section mb-4">

            <div class="row">
                <!-- Resumen por Clínica (si existe) -->
                <?php if (!empty($summaryPorClinica)): ?>
                    <?= $this->render('_comisiones-resumen', [
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
                        <div class="ms-card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-lg-7 mb-3 mb-lg-0">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center shadow"
                                                style="width: 70px; height: 70px; background: linear-gradient(135deg, #2c3e50 0%, #4a6491 100%);">
                                                <i class="fas fa-list-alt text-white" style="font-size: 2rem;"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h2 class="display-6 fw-bold mb-2" style="font-size: 2rem !important;">
                                                <i class="fas fa-search-dollar me-2 ms-primary"></i>Detalle de Pagos
                                            </h2>
                                            <div class="d-flex flex-wrap align-items-center gap-4">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-layer-group me-2 ms-primary" style="font-size: 1.4rem;"></i>
                                                    <span class="ms-body-lg fw-semibold" style="font-size: 1.4rem !important;">
                                                        <?= number_format($dataProvider->getCount()) ?> registros visibles
                                                    </span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <i class="far fa-clock me-2 ms-primary" style="font-size: 1.4rem;"></i>
                                                    <span class="ms-body-lg fw-semibold" style="font-size: 1.4rem !important;">
                                                        Página <?= $currentPage ?> de <?= $totalPages ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="d-flex justify-content-lg-end gap-3 flex-wrap">
                                        <!-- Change the button to a button element or use onclick event -->
                                        <button type="button" id="btn-print-pdf" class="ms-btn ms-btn-danger px-4 py-3 shadow"
                                            style="font-size: 1.4rem !important; min-width: 180px;">
                                            <i class="fas fa-file-pdf me-2" style="font-size: 1.4rem;"></i>Exportar PDF
                                        </button>
                                        <button type="button" id="btn-export-excel" class="ms-btn ms-btn-success px-4 py-3 shadow"
                                            style="font-size: 1.4rem !important; min-width: 180px;">
                                            <i class="fas fa-file-excel me-2" style="font-size: 1.4rem;"></i>Exportar Excel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Detalle Ajustada -->
                    <div class="ms-card border-0 shadow-lg p-0 ms-fade-in" style="animation-delay: 0.1s;">
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="ms-table ms-table-striped mb-0" style="min-width: 1900px;">
                                <thead style="position: sticky; top: 0; z-index: 10;">
                                    <tr style="background: linear-gradient(135deg, #2c3e50 0%, #4a6491 100%) !important;">
                                        <!-- # Column -->
                                        <th class="text-center py-4" style="width: 70px; border-right: 2px solid rgba(255,255,255,0.2);">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <span class="text-white fw-bold" style="font-size: 1.4rem !important;">#</span>
                                            </div>
                                        </th>

                                        <!-- Afiliado Column -->
                                        <th class="py-4 ps-4" style="min-width: 250px; border-right: 2px solid rgba(255,255,255,0.2);">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user me-3 text-white" style="font-size: 1.4rem;"></i>
                                                <span class="text-white fw-bold" style="font-size: 1.4rem !important;">Afiliado</span>
                                            </div>
                                        </th>

                                        <!-- Cédula Column -->
                                        <th class="text-center py-4" style="width: 140px; border-right: 2px solid rgba(255,255,255,0.2);">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <i class="fas fa-id-card me-3 text-white" style="font-size: 1.4rem;"></i>
                                                <span class="text-white fw-bold" style="font-size: 1.4rem !important;">Cédula</span>
                                            </div>
                                        </th>

                                        <!-- Sección de Montos -->
                                        <th colspan="3" class="text-center py-4" style="background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%); border-right: 2px solid rgba(255,255,255,0.2);">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-money-bill-wave me-3 text-white" style="font-size: 1.4rem;"></i>
                                                    <span class="text-white fw-bold" style="font-size: 1.5rem !important;">MONTOS</span>
                                                </div>
                                                <div class="d-flex w-100 mt-2">
                                                    <div class="col-4 text-center">
                                                        <span class="text-white fw-semibold" style="font-size: 1.3rem !important;">USD</span>
                                                    </div>
                                                    <div class="col-4 text-center">
                                                        <span class="text-white fw-semibold" style="font-size: 1.3rem !important;">TASA</span>
                                                    </div>
                                                    <div class="col-4 text-center">
                                                        <span class="text-white fw-semibold" style="font-size: 1.3rem !important;">Bs.</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Sección de Comisiones -->
                                        <th colspan="4" class="text-center py-4" style="background: linear-gradient(135deg, #8b0000 0%, #a52a2a 100%); border-right: 2px solid rgba(255,255,255,0.2);">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-hand-holding-usd me-3 text-white" style="font-size: 1.4rem;"></i>
                                                    <span class="text-white fw-bold" style="font-size: 1.5rem !important;">COMISIONES</span>
                                                </div>
                                                <div class="d-flex w-100 mt-2">
                                                    <div class="col-6 text-center">
                                                        <span class="text-white fw-semibold" style="font-size: 1.3rem !important;">ASESOR (10%)</span>
                                                    </div>
                                                    <div class="col-6 text-center">
                                                        <span class="text-white fw-semibold" style="font-size: 1.3rem !important;">AGENCIA (4%)</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Sección de Pagos Clínica -->
                                        <th colspan="2" class="text-center py-4" style="background: linear-gradient(135deg, #006400 0%, #228B22 100%); border-right: 2px solid rgba(255,255,255,0.2);">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-hospital me-3 text-white" style="font-size: 1.4rem;"></i>
                                                    <span class="text-white fw-bold" style="font-size: 1.5rem !important;">PAGOS CLÍNICA</span>
                                                </div>
                                                <div class="w-100 mt-2 text-center">
                                                    <span class="text-white fw-semibold" style="font-size: 1.3rem !important;">(70%)</span>
                                                </div>
                                            </div>
                                        </th>

                                        <!-- Fecha Column -->
                                        <th class="text-center py-4" style="width: 160px; border-right: 2px solid rgba(255,255,255,0.2);">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <i class="far fa-calendar-alt me-3 text-white" style="font-size: 1.4rem;"></i>
                                                <span class="text-white fw-bold" style="font-size: 1.4rem !important;">Fecha</span>
                                            </div>
                                        </th>

                                        <!-- Método Column -->
                                        <th class="text-center py-4" style="width: 170px; border-right: 2px solid rgba(255,255,255,0.2);">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <i class="fas fa-credit-card me-3 text-white" style="font-size: 1.4rem;"></i>
                                                <span class="text-white fw-bold" style="font-size: 1.4rem !important;">Método</span>
                                            </div>
                                        </th>

                                        <!-- Clínica Column -->
                                        <th class="text-center py-4 pe-4" style="min-width: 220px;">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <i class="fas fa-hospital me-3 text-white" style="font-size: 1.4rem;"></i>
                                                <span class="text-white fw-bold" style="font-size: 1.4rem !important;">Clínica</span>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $models = $dataProvider->getModels();
                                    $totalMontoUsd = 0;
                                    $totalMontoPagado = 0;
                                    $totalComisionAsesorBs = 0;
                                    $totalComisionAsesorUsd = 0;
                                    $totalComisionAgenciaBs = 0;
                                    $totalComisionAgenciaUsd = 0;
                                    $totalClinicaBs = 0;
                                    $totalClinicaUsd = 0;
                                    $consecutivo = ($dataProvider->pagination ? ($dataProvider->pagination->page * $dataProvider->pagination->pageSize) : 0) + 1;

                                    foreach ($models as $model):
                                        // Cálculos de comisiones
                                        $montoUsd = $model->monto_usd; // Monto en USD
                                        $montoPagado = $model->monto_pagado; // Monto pagado en bolívares

                                        // Cálculo de la tasa del día: Monto_usd / Monto_pagado
                                        $tasaDia = 0;
                                        if ($montoPagado > 0 && $montoUsd > 0) {
                                            $tasaDia = $montoUsd / $montoPagado;
                                        }

                                        // Cálculo de comisiones en Bs.
                                        $comisionAsesorBs = $montoUsd * 0.10; // 10% del monto pagado
                                        $comisionAgenciaBs = $montoUsd * 0.04; // 4% del monto pagado

                                        // Cálculo de comisiones en USD (comisionBs / tasaDia)
                                        $comisionAsesorUsd = $tasaDia > 0 ? $comisionAsesorBs / $tasaDia : 0;
                                        $comisionAgenciaUsd = $tasaDia > 0 ? $comisionAgenciaBs / $tasaDia : 0;

                                        // Cálculo de pagos a clínica (70%)
                                        $pagoClinicaBs = $montoUsd * 0.70; // 70% del monto en Bs.
                                        $pagoClinicaUsd = $montoPagado * 0.70; // 70% del monto en USD

                                        // Sumar a totales
                                        $totalMontoUsd += $montoUsd;
                                        $totalMontoPagado += $montoPagado;
                                        $totalComisionAsesorBs += $comisionAsesorBs;
                                        $totalComisionAsesorUsd += $comisionAsesorUsd;
                                        $totalComisionAgenciaBs += $comisionAgenciaBs;
                                        $totalComisionAgenciaUsd += $comisionAgenciaUsd;
                                        $totalClinicaBs += $pagoClinicaBs;
                                        $totalClinicaUsd += $pagoClinicaUsd;
                                    ?>
                                        <tr class="ms-slide-in" style="animation-delay: <?= $consecutivo * 0.02 ?>s; border-bottom: 1px solid #f8f9fa;">
                                            <!-- Número Consecutivo -->
                                            <td class="text-center py-3" style="background-color: #f8fafc; border-right: 2px solid #e9ecef;">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <span class="ms-badge ms-badge-info shadow-sm"
                                                        style="font-size: 1.4rem !important; padding: 0.6rem 0.9rem; min-width: 50px; background-color: #142e48ff;">
                                                        <?= $consecutivo++ ?>
                                                    </span>
                                                </div>
                                            </td>

                                            <!-- Afiliado -->
                                            <td class="py-3 ps-4" style="border-right: 2px solid #e9ecef;">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-4 shadow-sm"
                                                        style="width: 50px; height: 50px; background: linear-gradient(135deg, #c7e0f4 0%, #a5d2f4 100%);">
                                                        <i class="fas fa-user ms-primary" style="font-size: 1.5rem;"></i>
                                                    </div>
                                                    <div>
                                                        <div class="ms-body-lg fw-bold mb-2" style="font-size: 1.4rem !important;">
                                                            <?= $model->userDatos ?
                                                                Html::encode($model->userDatos->nombres . ' ' . $model->userDatos->apellidos) :
                                                                'N/A' ?>
                                                        </div>
                                                        <?php if ($model->userDatos && $model->userDatos->telefono): ?>
                                                            <div class="ms-body text-muted" style="font-size: 1.3rem !important;">
                                                                <i class="fas fa-phone me-1"></i><?= $model->userDatos->telefono ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Cédula -->
                                            <td class="text-center py-3" style="border-right: 2px solid #e9ecef;">
                                                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                                    <span class="ms-body-lg fw-bold" style="font-size: 1.4rem !important;">
                                                        <?= $model->userDatos && $model->userDatos->cedula ?
                                                            Html::encode($model->userDatos->cedula) : 'N/A' ?>
                                                    </span>
                                                </div>
                                            </td>

                                            <!-- Montos Section -->
                                            <!-- Monto (USD) -->
                                            <td class="text-center py-3" style="border-right: 1px solid #e9ecef; background-color: #f0f8ff;">
                                                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                                    <span class="display-6 fw-bold text-primary mb-2" style="font-size: 1.5rem !important;">
                                                        <?= Yii::$app->formatter->asCurrency($montoPagado, 'USD') ?>
                                                    </span>
                                                </div>
                                            </td>

                                            <!-- Tasa del Día (USD/Bs.) -->
                                            <td class="text-center py-3" style="border-right: 1px solid #e9ecef; background-color: #f0f8ff;">
                                                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                                    <?php if ($tasaDia > 0): ?>
                                                        <span class="display-6 fw-bold text-info mb-2" style="font-size: 1.5rem !important;">
                                                            <?= Yii::$app->formatter->asDecimal($tasaDia, 2) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="display-6 fw-bold text-muted mb-2" style="font-size: 1.5rem !important;">
                                                            N/A
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>

                                            <!-- Monto Pagado (Bs.) -->
                                            <td class="text-center py-3" style="border-right: 2px solid #e9ecef; background-color: #f0f8ff;">
                                                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                                    <span class="display-6 fw-bold text-success mb-2" style="font-size: 1.5rem !important;">
                                                        <?= Yii::$app->formatter->asCurrency($montoUsd, 'Bs.') ?>
                                                    </span>
                                                </div>
                                            </td>

                                            <!-- COMISIONES ASESOR -->
                                            <td class="text-center py-3" style="border-right: 1px solid #e9ecef; background-color: #fff9e6;">
                                                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                                    <span class="fw-bold text-warning mb-2" style="font-size: 1.4rem !important;">
                                                        <?= Yii::$app->formatter->asCurrency($comisionAsesorBs, 'Bs.') ?>
                                                    </span>
                                                </div>
                                            </td>

                                            <td class="text-center py-3" style="border-right: 2px solid #e9ecef; background-color: #fff9e6;">
                                                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                                    <span class="fw-bold text-primary mb-2" style="font-size: 1.4rem !important;">
                                                        <?= Yii::$app->formatter->asCurrency($comisionAsesorUsd, 'USD') ?>
                                                    </span>
                                                </div>
                                            </td>

                                            <!-- COMISIONES AGENCIA -->
                                            <td class="text-center py-3" style="border-right: 1px solid #e9ecef; background-color: #ffe6e6;">
                                                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                                    <span class="fw-bold text-danger mb-2" style="font-size: 1.4rem !important;">
                                                        <?= Yii::$app->formatter->asCurrency($comisionAgenciaBs, 'Bs.') ?>
                                                    </span>
                                                </div>
                                            </td>

                                            <td class="text-center py-3" style="border-right: 2px solid #e9ecef; background-color: #ffe6e6;">
                                                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                                    <span class="fw-bold text-primary mb-2" style="font-size: 1.4rem !important;">
                                                        <?= Yii::$app->formatter->asCurrency($comisionAgenciaUsd, 'USD') ?>
                                                    </span>
                                                </div>
                                            </td>

                                            <!-- PAGOS CLÍNICA (70%) -->
                                            <td class="text-center py-3" style="border-right: 1px solid #e9ecef; background-color: #e6ffe6;">
                                                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                                    <span class="fw-bold text-success mb-2" style="font-size: 1.4rem !important;">
                                                        <?= Yii::$app->formatter->asCurrency($pagoClinicaBs, 'Bs.') ?>
                                                    </span>
                                                </div>
                                            </td>

                                            <td class="text-center py-3" style="border-right: 2px solid #e9ecef; background-color: #e6ffe6;">
                                                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                                    <span class="fw-bold text-success mb-2" style="font-size: 1.4rem !important;">
                                                        <?= Yii::$app->formatter->asCurrency($pagoClinicaUsd, 'USD') ?>
                                                    </span>
                                                </div>
                                            </td>

                                            <!-- Fecha -->
                                            <td class="text-center py-3" style="border-right: 2px solid #e9ecef;">
                                                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                                                    <span class="ms-body-lg fw-bold mb-2" style="font-size: 1.4rem !important;">
                                                        <?= Yii::$app->formatter->asDate($model->fecha_pago, 'php:d/m/Y') ?>
                                                    </span>
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <i class="far fa-clock me-1" style="font-size: 1.2rem; color: #605e5c;"></i>
                                                        <span class="ms-body-sm text-muted" style="font-size: 1.2rem !important;">
                                                            <?= Yii::$app->formatter->asDate($model->fecha_pago, 'php:l') ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Método de Pago -->
                                            <td class="text-center py-3" style="border-right: 2px solid #e9ecef;">
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
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm"
                                                        style="width: 45px; height: 45px; background-color: <?= $bgColor ?>;">
                                                        <i class="fas <?= $icon ?>" style="font-size: 1.4rem; color: <?= $color ?>;"></i>
                                                    </div>
                                                    <span class="ms-body-lg fw-semibold" style="font-size: 1.4rem !important;"><?= $text ?></span>
                                                </div>
                                            </td>

                                            <!-- Clínica -->
                                            <td class="text-center py-3 pe-4">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm"
                                                        style="width: 45px; height: 45px; background: linear-gradient(135deg, #e6f2fa 0%, #d4e8fa 100%);">
                                                        <i class="fas fa-hospital ms-primary" style="font-size: 1.4rem;"></i>
                                                    </div>
                                                    <div class="text-start">
                                                        <div class="ms-body-lg fw-bold mb-2" style="font-size: 1.4rem !important;">
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
                                                            <div class="ms-body text-muted" style="font-size: 1.2rem !important;">
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
                                            <!-- TOTAL DETALLE Header -->
                                            <td colspan="3" class="ps-5 py-4">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-chart-bar me-4" style="font-size: 2rem; color: #ffffff;"></i>
                                                    <div>
                                                        <h3 class="mb-2 fw-bold" style="color: #ffffff; font-size: 1.8rem !important;">
                                                            TOTAL DETALLE
                                                        </h3>
                                                        <p class="mb-0" style="color: rgba(255, 255, 255, 0.85); font-size: 1.4rem !important;">
                                                            <?= count($models) ?> registros mostrados
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Montos Section Totals -->
                                            <!-- Total USD -->
                                            <td class="text-center py-4" style="background-color: rgba(0, 120, 212, 0.3);">
                                                <div class="d-flex flex-column align-items-center">
                                                    <h3 class="mb-2 fw-bold" style="color: #ffffff; font-size: 1.8rem !important;">
                                                        <?= number_format($totalMontoPagado, 2) ?>
                                                    </h3>
                                                    <p class="mb-0" style="color: rgba(255, 255, 255, 0.85); font-size: 1.2rem !important;">
                                                        Total USD
                                                    </p>
                                                </div>
                                            </td>

                                            <!-- Tasa Promedio Label -->
                                            <td class="text-center py-4" style="background-color: rgba(0, 120, 212, 0.3);">
                                                <div class="d-flex flex-column align-items-center">
                                                    <p class="mb-0" style="color: rgba(255, 255, 255, 0.85); font-size: 1.2rem !important;">
                                                        Tasa Promedio
                                                    </p>
                                                </div>
                                            </td>

                                            <!-- Total Bs. -->
                                            <td class="text-center py-4" style="background-color: rgba(0, 120, 212, 0.3); border-right: 2px solid rgba(255,255,255,0.2);">
                                                <div class="d-flex flex-column align-items-center">
                                                    <h3 class="mb-2 fw-bold" style="color: #ffffff; font-size: 1.8rem !important;">
                                                        <?= number_format($totalMontoUsd, 2) ?>
                                                    </h3>
                                                    <p class="mb-0" style="color: rgba(255, 255, 255, 0.85); font-size: 1.2rem !important;">
                                                        Total Bs.
                                                    </p>
                                                </div>
                                            </td>

                                            <!-- Comisiones Asesor Totals -->
                                            <td class="text-center py-4" style="background-color: rgba(255, 249, 230, 0.3);">
                                                <div class="d-flex flex-column align-items-center">
                                                    <h3 class="mb-2 fw-bold" style="color: #ffffff; font-size: 1.8rem !important;">
                                                        <?= number_format($totalComisionAsesorBs, 2) ?>
                                                    </h3>
                                                    <p class="mb-0" style="color: rgba(255, 255, 255, 0.85); font-size: 1.2rem !important;">
                                                        Asesor Bs.
                                                    </p>
                                                </div>
                                            </td>

                                            <td class="text-center py-4" style="background-color: rgba(255, 249, 230, 0.3);">
                                                <div class="d-flex flex-column align-items-center">
                                                    <h3 class="mb-2 fw-bold" style="color: #ffffff; font-size: 1.8rem !important;">
                                                        <?= number_format($totalComisionAsesorUsd, 2) ?>
                                                    </h3>
                                                    <p class="mb-0" style="color: rgba(255, 255, 255, 0.85); font-size: 1.2rem !important;">
                                                        Asesor USD
                                                    </p>
                                                </div>
                                            </td>

                                            <!-- Comisiones Agencia Totals -->
                                            <td class="text-center py-4" style="background-color: rgba(255, 230, 230, 0.3);">
                                                <div class="d-flex flex-column align-items-center">
                                                    <h3 class="mb-2 fw-bold" style="color: #ffffff; font-size: 1.8rem !important;">
                                                        <?= number_format($totalComisionAgenciaBs, 2) ?>
                                                    </h3>
                                                    <p class="mb-0" style="color: rgba(255, 255, 255, 0.85); font-size: 1.2rem !important;">
                                                        Agencia Bs.
                                                    </p>
                                                </div>
                                            </td>

                                            <td class="text-center py-4" style="background-color: rgba(255, 230, 230, 0.3); border-right: 2px solid rgba(255,255,255,0.2);">
                                                <div class="d-flex flex-column align-items-center">
                                                    <h3 class="mb-2 fw-bold" style="color: #ffffff; font-size: 1.8rem !important;">
                                                        <?= number_format($totalComisionAgenciaUsd, 2) ?>
                                                    </h3>
                                                    <p class="mb-0" style="color: rgba(255, 255, 255, 0.85); font-size: 1.2rem !important;">
                                                        Agencia USD
                                                    </p>
                                                </div>
                                            </td>

                                            <!-- Pagos Clínica Totals -->
                                            <td class="text-center py-4" style="background-color: rgba(230, 255, 230, 0.3);">
                                                <div class="d-flex flex-column align-items-center">
                                                    <h3 class="mb-2 fw-bold" style="color: #ffffff; font-size: 1.8rem !important;">
                                                        <?= number_format($totalClinicaBs, 2) ?>
                                                    </h3>
                                                    <p class="mb-0" style="color: rgba(255, 255, 255, 0.85); font-size: 1.2rem !important;">
                                                        Clínica Bs.
                                                    </p>
                                                </div>
                                            </td>

                                            <td class="text-center py-4" style="background-color: rgba(230, 255, 230, 0.3); border-right: 2px solid rgba(255,255,255,0.2);">
                                                <div class="d-flex flex-column align-items-center">
                                                    <h3 class="mb-2 fw-bold" style="color: #ffffff; font-size: 1.8rem !important;">
                                                        <?= number_format($totalClinicaUsd, 2) ?>
                                                    </h3>
                                                    <p class="mb-0" style="color: rgba(255, 255, 255, 0.85); font-size: 1.2rem !important;">
                                                        Clínica USD
                                                    </p>
                                                </div>
                                            </td>

                                            <!-- Fecha, Método, Clínica - Empty columns -->
                                            <td class="text-center py-4" style="background-color: rgba(255, 255, 255, 0.1);">
                                                <!-- Empty for Fecha column -->
                                            </td>

                                            <td class="text-center py-4" style="background-color: rgba(255, 255, 255, 0.1);">
                                                <!-- Empty for Método column -->
                                            </td>

                                            <td class="text-center py-4 pe-5" style="background-color: rgba(255, 255, 255, 0.1);">
                                                <!-- Empty for Clínica column -->
                                            </td>
                                        </tr>
                                    </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>

                        <!-- Paginación y Controles Ajustados -->
                        <div class="ms-card-footer py-4" style="background: #faf9f8;">
                            <div class="row align-items-center">
                                <div class="col-lg-6 mb-3 mb-lg-0">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-4 shadow-sm"
                                            style="width: 50px; height: 50px; background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);">
                                            <i class="fas fa-info-circle text-white" style="font-size: 1.4rem;"></i>
                                        </div>
                                        <div>
                                            <h4 class="ms-title-sm mb-2" style="font-size: 1.4rem !important;">
                                                Resumen de Datos
                                            </h4>
                                            <div class="d-flex flex-wrap gap-4">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-eye me-2 ms-primary" style="font-size: 1.2rem;"></i>
                                                    <span class="ms-body-lg fw-semibold" style="font-size: 1.3rem !important;">
                                                        <?= $dataProvider->getCount() ?> visibles
                                                    </span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-database me-2 ms-primary" style="font-size: 1.2rem;"></i>
                                                    <span class="ms-body-lg fw-semibold" style="font-size: 1.3rem !important;">
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
                                            'style' => 'font-size: 1.3rem !important; padding: 0.6rem 0.9rem;'
                                        ],
                                        'disabledListItemSubTagOptions' => [
                                            'class' => 'page-link',
                                            'style' => 'font-size: 1.3rem !important; padding: 0.6rem 0.9rem;'
                                        ],
                                        'maxButtonCount' => 5,
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Tarjetas de Resumen Ajustadas - Comisiones y Pagos Clínica -->
                <div class="col-12 mb-4 mt-4">
                    <div class="row g-4">
                        <!-- Total Comisión Asesor -->
                        <div class="col-xl-4 col-lg-6">
                            <div class="ms-summary-card ms-summary-card-warning border-0 shadow-lg ms-fade-in h-100">
                                <div class="ms-card-body p-4">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-4 shadow"
                                            style="width: 70px; height: 70px; background: linear-gradient(135deg, #ff8c00 0%, #e67e00 100%);">
                                            <i class="fas fa-user-tie text-white" style="font-size: 2rem;"></i>
                                        </div>
                                        <div>
                                            <h5 class="ms-title-sm mb-2" style="font-size: 1.4rem !important; color: #605e5c;">
                                                <i class="fas fa-hand-holding-usd me-2"></i>COMISIÓN ASESOR
                                            </h5>
                                            <h2 class="ms-title-lg mb-0 text-warning" style="font-size: 2rem !important;">
                                                <?= Yii::$app->formatter->asCurrency($totalComisionAsesorBs, 'VES') ?>
                                            </h2>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-dollar-sign me-3" style="font-size: 1.5rem; color: #ff8c00;"></i>
                                            <span class="ms-body-lg fw-semibold" style="font-size: 1.5rem !important;">
                                                <?= Yii::$app->formatter->asCurrency($totalComisionAsesorUsd, 'USD') ?>
                                            </span>
                                        </div>
                                        <div class="ms-badge ms-badge-warning shadow" style="font-size: 1.4rem !important; padding: 0.6rem 1.2rem;">
                                            <i class="fas fa-percentage me-2"></i>10%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Comisión Agencia -->
                        <div class="col-xl-4 col-lg-6">
                            <div class="ms-summary-card ms-summary-card-danger border-0 shadow-lg ms-fade-in h-100" style="animation-delay: 0.1s;">
                                <div class="ms-card-body p-4">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-4 shadow"
                                            style="width: 70px; height: 70px; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                                            <i class="fas fa-building text-white" style="font-size: 2rem;"></i>
                                        </div>
                                        <div>
                                            <h5 class="ms-title-sm mb-2" style="font-size: 1.4rem !important; color: #605e5c;">
                                                <i class="fas fa-hand-holding-usd me-2"></i>COMISIÓN AGENCIA
                                            </h5>
                                            <h2 class="ms-title-lg mb-0 text-danger" style="font-size: 2rem !important;">
                                                <?= Yii::$app->formatter->asCurrency($totalComisionAgenciaBs, 'VES') ?>
                                            </h2>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-dollar-sign me-3" style="font-size: 1.5rem; color: #dc3545;"></i>
                                            <span class="ms-body-lg fw-semibold" style="font-size: 1.5rem !important;">
                                                <?= Yii::$app->formatter->asCurrency($totalComisionAgenciaUsd, 'USD') ?>
                                            </span>
                                        </div>
                                        <div class="ms-badge ms-badge-danger shadow" style="font-size: 1.4rem !important; padding: 0.6rem 1.2rem;">
                                            <i class="fas fa-percentage me-2"></i>4%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Pagos Clínica -->
                        <div class="col-xl-4 col-lg-6">
                            <div class="ms-summary-card ms-summary-card-success border-0 shadow-lg ms-fade-in h-100" style="animation-delay: 0.2s;">
                                <div class="ms-card-body p-4">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-4 shadow"
                                            style="width: 70px; height: 70px; background: linear-gradient(135deg, #107c10 0%, #0e6a0e 100%);">
                                            <i class="fas fa-hospital text-white" style="font-size: 2rem;"></i>
                                        </div>
                                        <div>
                                            <h5 class="ms-title-sm mb-2" style="font-size: 1.4rem !important; color: #605e5c;">
                                                <i class="fas fa-hand-holding-usd me-2"></i>PAGOS CLÍNICA
                                            </h5>
                                            <h2 class="ms-title-lg mb-0 text-success" style="font-size: 2rem !important;">
                                                <?= Yii::$app->formatter->asCurrency($totalClinicaBs, 'VES') ?>
                                            </h2>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-dollar-sign me-3" style="font-size: 1.5rem; color: #107c10;"></i>
                                            <span class="ms-body-lg fw-semibold" style="font-size: 1.5rem !important;">
                                                <?= Yii::$app->formatter->asCurrency($totalClinicaUsd, 'USD') ?>
                                            </span>
                                        </div>
                                        <div class="ms-badge ms-badge-success shadow" style="font-size: 1.4rem !important; padding: 0.6rem 1.2rem;">
                                            <i class="fas fa-percentage me-2"></i>70%
                                        </div>
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
                        width: 12px;
                        height: 12px;
                    }

                    .table-responsive::-webkit-scrollbar-track {
                        background: #f1f1f1;
                        border-radius: 6px;
                    }

                    .table-responsive::-webkit-scrollbar-thumb {
                        background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);
                        border-radius: 6px;
                        border: 3px solid #f1f1f1;
                    }

                    .table-responsive::-webkit-scrollbar-thumb:hover {
                        background: linear-gradient(135deg, #106ebe 0%, #005a9e 100%);
                    }

                    /* Efecto hover para filas de la tabla - Ajustado */
                    .ms-table tbody tr {
                        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
                    }

                    .ms-table tbody tr:hover {
                        transform: translateX(5px);
                        box-shadow: 0 5px 10px rgba(0, 0, 0, 0.15);
                        background-color: rgba(0, 120, 212, 0.05) !important;
                    }

                    /* Estilos para las celdas de comisiones */
                    .ms-table tbody tr td[style*="background-color: #fff9e6;"]:hover {
                        background-color: #fff0cc !important;
                    }

                    .ms-table tbody tr td[style*="background-color: #ffe6e6;"]:hover {
                        background-color: #ffcccc !important;
                    }

                    .ms-table tbody tr td[style*="background-color: #e6ffe6;"]:hover {
                        background-color: #ccffcc !important;
                    }

                    .ms-table tbody tr td[style*="background-color: #f0f8ff;"]:hover {
                        background-color: #e0f0ff !important;
                    }

                    /* Estilos para las tarjetas de resumen */
                    .ms-summary-card {
                        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                        border-radius: 12px;
                    }

                    .ms-summary-card:hover {
                        transform: translateY(-8px);
                        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2) !important;
                    }

                    .ms-summary-card-warning {
                        border-left: 6px solid #ff8c00;
                    }

                    .ms-summary-card-danger {
                        border-left: 6px solid #dc3545;
                    }

                    .ms-summary-card-success {
                        border-left: 6px solid #107c10;
                    }

                    /* Ajustes responsivos - Ajustado */
                    @media (max-width: 1200px) {
                        .table-responsive {
                            max-height: 450px;
                        }
                    }

                    @media (max-width: 992px) {

                        .display-5,
                        .display-6 {
                            font-size: calc(1.3rem + 0.5vw) !important;
                        }

                        .ms-body-lg,
                        .ms-title-lg,
                        .ms-title-md {
                            font-size: calc(1.1rem + 0.3vw) !important;
                        }

                        .ms-btn {
                            min-width: 160px !important;
                        }
                    }
                </style>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        // Handle PDF export for comisiones report
        $('#btn-print-pdf').click(function(e) {
            e.preventDefault();

            // Get current filter parameters from the form
            var range = $('[name="range"]').val();
            var specific_date = $('[name="specific_date"]').val();
            var status = $('[name="status"]').val(); // This might not exist

            // Try to get status from #status-filter if [name="status"] doesn't exist
            if (!status || status === 'undefined') {
                status = $('#status-filter').val() || 'todos';
            }

            var clinicas = [];

            // Check if clinicas checkboxes exist and get values
            if ($('[name="clinicas[]"]').length > 0) {
                $('[name="clinicas[]"]:checked').each(function() {
                    clinicas.push($(this).val());
                });
            }

            // If no clinicas selected, set default
            if (clinicas.length === 0) {
                clinicas = ['todas']; // Default to "todas"
            }

            // Use 'last-month' as default if range is undefined or empty
            // Since your data is from November 2025
            if (!range || range === 'undefined' || range === 'day') {
                range = 'last-month'; // Changed to 'last-month' since data is from previous month
            }

            // Use 'todos' as default if status is undefined or empty
            if (!status || status === 'undefined') {
                status = 'todos';
            }

            console.log("PDF Params:", {
                range: range,
                status: status,
                clinicas: clinicas,
                specific_date: specific_date
            });

            // Build URL for comisiones PDF
            var url = '<?= Yii::$app->urlManager->createUrl(['reportes/generate-comisiones-pdf-tcpdf']) ?>';
            url += '?range=' + encodeURIComponent(range);
            url += '&status=' + encodeURIComponent(status);

            if (specific_date && specific_date !== 'Invalid date') {
                url += '&specific_date=' + encodeURIComponent(specific_date);
            }

            if (clinicas.length > 0) {
                url += '&clinicas=' + encodeURIComponent(clinicas.join(','));
            }

            // Add custom range if applicable
            var customRangeToggle = $('#custom-range-toggle');
            if (customRangeToggle.length > 0 && customRangeToggle.is(':checked')) {
                var dateFrom = $('[name="date_from"]').val();
                var dateTo = $('[name="date_to"]').val();
                if (dateFrom && dateTo) {
                    url += '&custom_range=true';
                    url += '&date_from=' + encodeURIComponent(dateFrom);
                    url += '&date_to=' + encodeURIComponent(dateTo);
                }
            }

            console.log("PDF URL:", url);

            // Open PDF in new tab
            window.open(url, '_blank');
        });

        // Handle Excel export for comisiones report
        $('#btn-export-excel').click(function(e) {
            e.preventDefault();

            // Same logic as above for Excel
            var range = $('[name="range"]').val();
            var specific_date = $('[name="specific_date"]').val();
            var status = $('[name="status"]').val();
            var clinicas = [];

            if ($('[name="clinicas[]"]').length > 0) {
                $('[name="clinicas[]"]:checked').each(function() {
                    clinicas.push($(this).val());
                });
            }

            if (clinicas.length === 0) {
                clinicas = ['todas'];
            }

            // Build URL for comisiones Excel
            var url = '<?= Yii::$app->urlManager->createUrl(['reportes/export-comisiones-excel']) ?>';
            url += '?range=' + (range || 'day');
            url += '&status=' + (status || 'todos');

            if (specific_date && specific_date !== 'Invalid date') {
                url += '&specific_date=' + specific_date;
            }

            if (clinicas.length > 0) {
                url += '&clinicas=' + clinicas.join(',');
            }

            // Add custom range if applicable
            var customRangeToggle = $('#custom-range-toggle');
            if (customRangeToggle.length > 0 && customRangeToggle.is(':checked')) {
                var dateFrom = $('[name="date_from"]').val();
                var dateTo = $('[name="date_to"]').val();
                if (dateFrom && dateTo) {
                    url += '&custom_range=true';
                    url += '&date_from=' + dateFrom;
                    url += '&date_to=' + dateTo;
                }
            }

            // Download Excel
            window.location.href = url;
        });
    });
</script>