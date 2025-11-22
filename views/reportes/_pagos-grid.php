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
/** @var string $startDate */
/** @var string $endDate */

// Usar el formatter de Yii para mostrar los datos
?>

<div class="row">
    <div class="col-12 mb-4">
        <!-- Centered title section -->
        <div class="text-center mb-3">
            <h2><?= Html::encode($title) ?></h2>
            <p class="text-muted mb-0">
                Periodo: <strong><?= $startDate ?></strong> al <strong><?= $endDate ?></strong>
            </p>
        </div>
    </div>
    
    <div class="col-md-6 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="text-uppercase mb-0">Total Recaudado</h5>
                        <h1 class="display-4 font-weight-bold"><?= Yii::$app->formatter->asCurrency($summary['total_monto'], 'VES') ?></h1>
                    </div>
                    <i class="fas fa-money-bill-alt fa-4x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="text-uppercase mb-0">Total de Pagos</h5>
                        <h1 class="display-4 font-weight-bold"><?= Yii::$app->formatter->asInteger($summary['total_count']) ?></h1>
                    </div>
                    <i class="fas fa-receipt fa-4x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12">
        <!-- PDF Button aligned to right, just before GridView -->
        <div class="text-right  mb-3">
            <?= Html::a('<i class="fas fa-print"></i> Imprimir PDF', '#', [
                'id' => 'btn-print-pdf',
                'class' => 'btn btn-danger btn-sm',
                'target' => '_blank', // Abrir PDF en nueva pestaña
                'disabled' => true // Se habilitará y actualizará por JS de la vista principal
            ]) ?>
        </div>
        
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'options' => ['class' => 'grid-view table-responsive'],
            'columns' => [
                // Columna 1: ID del Pago (NUEVA)
                [
                    'attribute' => 'id',
                    'label' => 'ID Pago',
                    // Permitir filtrar y ordenar directamente por el ID
                ],
                // Columna 2: Nombres del Afiliado
                [
                    'attribute' => 'nombres',
                    'label' => 'Nombres',
                    'value' => 'userDatos.nombres',
                    'filterInputOptions' => ['placeholder' => 'Buscar nombres', 'class' => 'form-control'],
                ],
                // Columna 3: Apellidos del Afiliado
                [
                    'attribute' => 'apellidos',
                    'label' => 'Apellidos',
                    'value' => 'userDatos.apellidos',
                    'filterInputOptions' => ['placeholder' => 'Buscar apellidos', 'class' => 'form-control'],
                ],
                // Columna 4: Cédula de Identidad
                [
                    'attribute' => 'cedula',
                    'label' => 'Cédula',
                    'value' => 'userDatos.cedula',
                    'filterInputOptions' => ['placeholder' => 'Buscar cédula', 'class' => 'form-control'],
                ],
                // Columna 5: Monto Pagado (USD)
                [
                    'attribute' => 'monto_usd',
                    'label' => 'Monto (Bs.)',
                    'format' => ['currency', 'VES'], 
                    'contentOptions' => ['class' => 'text-right'],
                    'filter' => false,
                ],
                // Columna 6: Fecha de Pago
                [
                    'attribute' => 'fecha_pago',
                    'label' => 'Fecha de Pago',
                    'format' => 'date',
                    'filter' => false,
                ],
                // Columna Adicional: Método de Pago
                'metodo_pago',
            ],
        ]); ?>
    </div>
</div>