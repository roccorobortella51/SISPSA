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

// Usar el formatter de Yii para mostrar los datos
?>

<div class="row">
    <!-- Título del Reporte -->
    <div class="col-12 mb-4">
        <div class="text-center mb-3">
            <h2><?= Html::encode($title) ?></h2>
            <p class="text-muted mb-0">
                Periodo: <strong><?= $startDate ?></strong> al <strong><?= $endDate ?></strong>
            </p>
            <?php if (!empty($clinicasSeleccionadas) && !in_array('todas', $clinicasSeleccionadas)): ?>
                <p class="text-info">
                    <i class="fas fa-filter"></i> Filtrado por <?= count($clinicasSeleccionadas) ?> clínica(s) seleccionada(s)
                </p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Panel de Totales -->
    <div class="col-md-6 mb-3">
        <div class="card bg-gradient-success text-white shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="text-uppercase mb-0">Total Recaudado</h5>
                        <h1 class="display-4 font-weight-bold"><?= Yii::$app->formatter->asCurrency($summary['total_monto'], 'VES') ?></h1>
                        <p class="mb-0">
                            <i class="fas fa-hospital me-1"></i>
                            <?= number_format($summary['total_count']) ?> pagos
                        </p>
                    </div>
                    <i class="fas fa-money-bill-alt fa-4x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-3">
        <div class="card bg-gradient-info text-white shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="text-uppercase mb-0">Resumen por Estado</h5>
                        <div class="mt-3">
                            <span class="badge bg-success fs-5 me-3">
                                <i class="fas fa-check-circle me-1"></i>
                                <?= number_format($summary['conciliados'] ?? 0) ?> Conciliados
                            </span>
                            <span class="badge bg-warning fs-5">
                                <i class="fas fa-clock me-1"></i>
                                <?= number_format($summary['pendientes'] ?? 0) ?> Pendientes
                            </span>
                        </div>
                    </div>
                    <i class="fas fa-chart-pie fa-4x opacity-50"></i>
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
    
    <!-- GridView de Detalle -->
    <div class="col-12">
        <!-- Panel de Acciones -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i> Detalle de Pagos
                            <small class="text-muted">(<?= $dataProvider->getTotalCount() ?> registros)</small>
                        </h5>
                    </div>
                    <div class="col-md-6 text-end">
                        <?= Html::a('<i class="fas fa-print"></i> Imprimir PDF', '#', [
                            'id' => 'btn-print-pdf',
                            'class' => 'btn btn-danger btn-lg',
                            'target' => '_blank',
                            'disabled' => true
                        ]) ?>
                        
                        <?= Html::a('<i class="fas fa-file-excel"></i> Exportar Excel', '#', [
                        'id' => 'btn-export-excel',
                        'class' => 'btn btn-success btn-lg ms-2',
                        'disabled' => false,
                        'onclick' => 'exportToExcel()',
                        'href' => 'javascript:void(0);'
                    ]) ?>
                    </div>
                </div>
            </div>
        </div>
        
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'options' => ['class' => 'grid-view table-responsive'],
            'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
            'layout' => "{summary}\n{items}\n{pager}",
            'summary' => 'Mostrando <strong>{begin}-{end}</strong> de <strong>{totalCount}</strong> pagos',
            'columns' => [
                [
                    'attribute' => 'id',
                    'label' => 'ID Pago',
                    'headerOptions' => ['class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                ],
                [
                    'attribute' => 'nombres',
                    'label' => 'Afiliado',
                    'value' => function($model) {
                        return $model->userDatos ? 
                               $model->userDatos->nombres . ' ' . $model->userDatos->apellidos : 
                               'N/A';
                    },
                    'filterInputOptions' => ['placeholder' => 'Buscar afiliado', 'class' => 'form-control'],
                ],
                [
                    'attribute' => 'cedula',
                    'label' => 'Cédula',
                    'value' => 'userDatos.cedula',
                    'filterInputOptions' => ['placeholder' => 'Buscar cédula', 'class' => 'form-control'],
                    'headerOptions' => ['class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                ],
                [
                    'attribute' => 'monto_usd',
                    'label' => 'Monto (Bs.)',
                    'format' => ['currency', 'VES'], 
                    'contentOptions' => ['class' => 'text-right fw-bold'],
                    'filter' => false,
                    'headerOptions' => ['class' => 'text-center'],
                ],
                [
                    'attribute' => 'fecha_pago',
                    'label' => 'Fecha',
                    'format' => 'date',
                    'filter' => false,
                    'headerOptions' => ['class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                ],
                [
                    'attribute' => 'metodo_pago',
                    'label' => 'Método',
                    'headerOptions' => ['class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                ],
                [
                    'attribute' => 'estatus',
                    'label' => 'Estado',
                    'value' => function($model) {
                        $badge = $model->estatus === 'Conciliado' ? 'success' : 'warning';
                        return '<span class="badge bg-' . $badge . '">' . $model->estatus . '</span>';
                    },
                    'format' => 'raw',
                    'filter' => Html::dropDownList(
                        'PagosReporteSearch[estatus]',
                        $searchModel->estatus,
                        ['Conciliado' => 'Conciliado', 'Por Conciliar' => 'Por Conciliar'],
                        ['class' => 'form-control', 'prompt' => 'Todos']
                    ),
                    'headerOptions' => ['class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                ],
                // Nueva columna para clínica
                [
                    'label' => 'Clínica',
                    'value' => function($model) {
                        $contrato = $model->contratos ? $model->contratos[0] : null;
                        return $contrato && $contrato->clinica ? 
                               $contrato->clinica->nombre : 
                               'Sin Clínica';
                    },
                    'filter' => false,
                    'headerOptions' => ['class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                ],
            ],
        ]); ?>
    </div>
</div>