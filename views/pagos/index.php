<?php

use app\models\Pagos;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\switchinput\SwitchInput;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\PagosSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'PAGOS';
$this->params['breadcrumbs'][] = $this->title;

// List of statuses for the 'estatus' filter
$estatusList = [
    'Conciliado' => 'Conciliado',
    'Por Conciliar' => 'Por Conciliar',
];

// List of payment methods for the filter
$metodoPagoList = [
    'Efectivo' => 'Efectivo',
    'Pago Móvil' => 'Pago Móvil',
    'Paypal' => 'Paypal',
    'Punto de Venta' => 'Punto de Venta',
    'Transferencia' => 'Transferencia',
    'Zelle' => 'Zelle',
    
];
?>
<div class="pagos-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        
        // AÑADIR ESTA LÍNEA para el resumen en español
        'summary' => '<span style="font-size: 1.7em; color: white;">Mostrando {begin}-{end} de {totalCount} elementos</span>',
        // --- KARTIK GRIDVIEW CONFIGURATION ---
        'pjax' => true, 
        'striped' => true,
        'condensed' => true,
        'responsive' => true,
        'hover' => true,
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => '<i class="fas fa-money-check-alt"></i> ' . $this->title,
            'before' => false, 
            'after' => false, 
        ],
        
        'toolbar' => [
            [
                'content' =>
                    Html::a('Crear Pagos', ['create'], ['class' => 'btn btn-success']) .
                    Html::a('<i class="fas fas-redo"></i>', ['index'], [
                        'class' => 'btn btn-outline-secondary',
                        'title' => Yii::t('kvgrid', 'Reset Grid'),
                        'data-pjax' => 0, 
                    ])
            ],
            '{export}',
            '{toggleData}',
        ],
        // --- END KARTIK GRIDVIEW CONFIGURATION ---

        'columns' => [
            // SerialColumn (#) - CONTENT CENTERED
            [
                'class' => 'yii\grid\SerialColumn',
                'contentOptions' => ['style' => 'text-align: center;'],
            ],
            
            // User Column (Afiliado)
            [
                'attribute' => 'nombreUsuario', 
                'value' => function ($model) {
                    return $model->userDatos ? $model->userDatos->nombres . ' ' . $model->userDatos->apellidos : 'N/A';
                },
                'label' => 'AFILIADO', 
            ],

            // Cedula Column 
            [
                'attribute' => 'cedulaUsuario', 
                'value' => function ($model) {
                    return $model->userDatos ? $model->userDatos->cedula : 'N/A';
                },
                'label' => 'CÉDULA', 
                'headerOptions' => ['style' => 'width: 10%; text-align: center;'],
                'contentOptions' => ['style' => 'width: 10%; text-align: center;'], 
                'filterOptions' => ['style' => 'width: 10%'], 
            ],

            // Solvente Column - CONTENT CENTERED
            [
                'label' => 'SOLVENTE', 
                'value' => function ($model) {
                    $isSolvente = $model->userDatos ? $model->userDatos->estatus_solvente : 'No';
                    if ($isSolvente == 'SI') {
                        return '<span class="badge badge-success">SI</span>';
                    }
                    return '<span class="badge badge-danger">No</span>';
                },
                'format' => 'raw',
                'contentOptions' => ['style' => 'text-align: center;'],
                'filter' => ['SI' => 'SI', 'No' => 'No'],
            ],
            
            // Payment Method Column - CONTENT CENTERED
            [
                'attribute' => 'metodo_pago', 
                'header' => 'MÉTODO<br>PAGO', 
                'format' => 'raw', 
                'headerOptions' => ['style' => 'width: 60px;'], 
                'contentOptions' => ['style' => 'width: 60px; text-align: center;'],
                'filter' => $metodoPagoList,
                'filterType' => GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'options' => ['placeholder' => 'MÉTODO'], 
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ],
            ],
            
            // Payment Reference Column - CONTENT CENTERED
            [
                'attribute' => 'numero_referencia_pago',
                'header' => 'REFERENCIA<br>PAGO', 
                'format' => 'raw', 
                'filter' => true, 
                'headerOptions' => [
                    'style' => 'width: 100px; text-align: center;',
                ],
                'contentOptions' => ['style' => 'text-align: center; white-space: normal;'],
            ],
            
            // Columna de Fecha de Pago
            [
                'attribute' => 'fecha_pago',
                'format' => 'date', 
                'hAlign' => GridView::ALIGN_CENTER, // Centra el valor
                'contentOptions' => ['style' => 'white-space: nowrap;'], // Fuerza a que no haya salto de línea
                'filterInputOptions' => [
                    'placeholder' => 'Ej: 10, 2024, 15/09', // <-- Placeholder informativo
                    'class' => 'form-control',
                ],
                /* NOTA: El filtro ya es flexible. El usuario puede buscar por:
                - Mes: '10' (Para pagos de Octubre)
                - Año: '2024'
                - Día y Mes: '15/09' 
                Esto es posible gracias al CAST(columna AS TEXT) en PagosSearch.php. */
            ],
            
            // Monto Pagado USD Column (Right aligned)
            [
                'attribute' => 'monto_pagado',
                'value' => function ($model) {
                    return $model->monto_pagado . ' USD';
                },
                'header' => 'MONTO<br>PAGADO USD', 
                'format' => 'raw', 
                'hAlign' => GridView::ALIGN_RIGHT,
                'headerOptions' => [
                    'style' => 'width: 80px; text-align: right;',
                ],
                'contentOptions' => ['style' => 'white-space: nowrap;'],
            ],
            
            // Monto Pagado Bs Column (Right aligned, single line)
            [
                'attribute' => 'monto_usd',
                'value' => function ($model) {
                    return $model->monto_usd . ' Bs';
                },
                'header' => 'MONTO<br>PAGADO BS', 
                'format' => 'raw', 
                'hAlign' => GridView::ALIGN_RIGHT,
                'headerOptions' => [
                    'style' => 'width: 80px; text-align: right;',
                ],
                'contentOptions' => [
                    'style' => 'white-space: nowrap; font-weight: bold;',
                ],
            ],
            
            // Conciliation Status Column - CONTENT CENTERED
            [
                'attribute' => 'estatus',
                'format' => 'raw',
                'value' => function ($model) {
                    $isActive = ($model->estatus == 'Conciliado' || $model->estatus == '1' || $model->estatus == 'Activo');
                    
                    return SwitchInput::widget([
                        'name' => 'estatus_' . $model->id,
                        'value' => $isActive,
                        'pluginOptions' => [
                            'size' => 'large',
                            'onText' => 'Conciliado',
                            'offText' => 'Por Conciliar',
                            'onColor' => 'success',
                            'offColor' => 'danger',
                        ],
                        'pluginEvents' => [
                            'switchChange.bootstrapSwitch' => "function(event, state) {
                                var currentRow = $(event.target).closest('tr');
                                var solventeCell = currentRow.find('td').eq(3); 

                                $.ajax({
                                    url: '" . Url::to(['/pagos/updatestatus']) . "',
                                    type: 'POST',
                                    data: {
                                        id: " . $model->id . ",
                                        status: state ? 1 : 0,
                                        _csrf: '" . Yii::$app->request->getCsrfToken() . "'
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            var newSolventeStatus = state ? '<span class=\"badge badge-success\">SI</span>' : '<span class=\"badge badge-danger\">No</span>';
                                            solventeCell.html(newSolventeStatus);
                                        } else {
                                            $(event.target).bootstrapSwitch('state', !state, true);
                                            alert('Error: ' + response.error);
                                        }
                                    },
                                    error: function(xhr) {
                                        $(event.target).bootstrapSwitch('state', !state, true);
                                        alert('Error del servidor: ' + xhr.responseText);
                                    }
                                });
                            }"
                        ]
                    ]);
                },
                'label' => 'CONCILIACION', 
                'contentOptions' => ['style' => 'text-align: center;'], // CONTENT CENTERED
                'filter' => $estatusList,
                'filterType' => GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'options' => ['placeholder' => 'Filtrar estatus...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ],
            ],
            
            
            [
                'class' => ActionColumn::class, 
                'header' => 'ACCIONES', 
                // --- KEY CHANGE: Add a custom template with spacing ---
                'template' => '{view}&nbsp;&nbsp;{update}&nbsp;&nbsp;&nbsp;&nbsp;{delete}',
                // -----------------------------------------------------
                'headerOptions' => ['style' => 'width: 120px; text-align: center;'], 
                'contentOptions' => ['style' => 'width: 120px; min-width: 120px; text-align: center;'], 
                'urlCreator' => function ($action, Pagos $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 },
            ],
        ],
    ]); ?>

</div>