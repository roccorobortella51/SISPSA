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
$css = <<<CSS
/* Professional color scheme - Blue for Individuals, Purple for Corporations */
.corporate-badge-pro {
    background: linear-gradient(135deg, #6610f2, #593196);
    color: white;
    padding: 5px 12px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 0.85em;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    letter-spacing: 0.5px;
    box-shadow: 0 1px 3px rgba(102, 16, 242, 0.2);
}

/* Individual badge - ocean blue theme */
.individual-badge-pro {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
    padding: 5px 12px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 0.85em;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    letter-spacing: 0.5px;
    box-shadow: 0 1px 3px rgba(23, 162, 184, 0.2);
}

/* Corporate name styling - Purple */
.corporate-name-pro {
    color: #6610f2;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 4px;
}

/* Individual name styling - Ocean Blue */
.individual-name-pro {
    color: #17a2b8;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 4px;
}

/* Corporate RIF styling - Purple */
.corporate-rif {
    background-color: rgba(102, 16, 242, 0.1);
    color: #6610f2;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 3px;
    border: 1px solid rgba(102, 16, 242, 0.2);
    font-size: 0.9em;
    display: inline-block;
}

/* Individual cedula styling - Blue */
.individual-cedula {
    background-color: rgba(23, 162, 184, 0.1);
    color: #138496;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 3px;
    border: 1px solid rgba(23, 162, 184, 0.2);
    font-size: 0.9em;
    display: inline-block;
}

/* Corporate icon styling */
.corporate-icon {
    color: #6610f2;
    font-size: 0.9em;
}

.individual-icon {
    color: #17a2b8;
    font-size: 0.9em;
}

/* Add subtle background to tipo_pago cell */
.corporate-tipo-cell {
    background-color: rgba(102, 16, 242, 0.05);
    border: 1px solid rgba(102, 16, 242, 0.1);
    border-radius: 4px;
}

.individual-tipo-cell {
    background-color: rgba(23, 162, 184, 0.05);
    border: 1px solid rgba(23, 162, 184, 0.1);
    border-radius: 4px;
}

/* Style for corporate payer cells (optional subtle background) */
.corporate-payer-cell {
    background-color: rgba(102, 16, 242, 0.03);
    border-left: 2px solid rgba(102, 16, 242, 0.2);
}

.individual-payer-cell {
    background-color: rgba(23, 162, 184, 0.03);
    border-left: 2px solid rgba(23, 162, 184, 0.2);
}
/* Affiliate styling - Amber/Gold color */
.affiliate-badge-pro {
    background: linear-gradient(135deg, #ffc107, #e0a800);
    color: #212529;
    padding: 5px 12px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 0.85em;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    letter-spacing: 0.5px;
    box-shadow: 0 1px 3px rgba(255, 193, 7, 0.2);
}

.affiliate-payer-cell {
    background-color: rgba(255, 193, 7, 0.03);
    border-left: 2px solid rgba(255, 193, 7, 0.3);
}

.affiliate-cedula {
    background-color: rgba(255, 193, 7, 0.1);
    color: #e0a800;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 3px;
    border: 1px solid rgba(255, 193, 7, 0.2);
    font-size: 0.9em;
    display: inline-block;
}

.affiliate-tipo-cell {
    background-color: rgba(255, 193, 7, 0.05);
    border: 1px solid rgba(255, 193, 7, 0.1);
    border-radius: 4px;
}
CSS;

$this->registerCss($css);
?>

<div class="pagos-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,

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
                    // Si es un pago corporativo principal
                    if ($model->tipo_pago === 'corporativo' && $model->corporativo_id) {
                        if ($model->corporativo && $model->corporativo->nombre) {
                            return '<div class="corporate-name-pro">
                                <i class="fas fa-building corporate-icon"></i>
                                ' . $model->corporativo->nombre . ' (Corporación)
                            </div>';
                        }
                    }
                    // Si es un pago de afiliado (tiene pago_corporativo_id)
                    elseif ($model->pago_corporativo_id) {
                        if ($model->userDatos) {
                            return '<div class="individual-name-pro">
                                <i class="fas fa-user individual-icon"></i>
                                ' . $model->userDatos->nombres . ' ' . $model->userDatos->apellidos . ' (Afiliado)
                            </div>';
                        }
                    }
                    // Si es pago individual normal
                    else {
                        return $model->userDatos ?
                            '<div class="individual-name-pro">
                        <i class="fas fa-user individual-icon"></i>
                        ' . $model->userDatos->nombres . ' ' . $model->userDatos->apellidos . '
                    </div>' : 'N/A';
                    }

                    return 'N/A';
                },
                'label' => 'PAGADOR/AFILIADO',
                'format' => 'raw',
                'contentOptions' => function ($model) {
                    if ($model->tipo_pago === 'corporativo' && $model->corporativo_id) {
                        return ['class' => 'corporate-payer-cell', 'style' => 'vertical-align: middle;'];
                    } elseif ($model->pago_corporativo_id) {
                        return ['class' => 'affiliate-payer-cell', 'style' => 'vertical-align: middle;'];
                    } else {
                        return ['class' => 'individual-payer-cell', 'style' => 'vertical-align: middle;'];
                    }
                }
            ],

            [
                'attribute' => 'cedulaUsuario',
                'value' => function ($model) {
                    // Si es pago corporativo principal
                    if ($model->tipo_pago === 'corporativo' && $model->corporativo_id) {
                        if ($model->corporativo && $model->corporativo->rif) {
                            return '<span class="corporate-rif">' . $model->corporativo->rif . '</span>';
                        }
                    }
                    // Si es pago de afiliado
                    elseif ($model->pago_corporativo_id) {
                        if ($model->userDatos) {
                            $cedula = $model->userDatos->cedula;
                            $tipoCedula = $model->userDatos->tipo_cedula;
                            $formatted = $tipoCedula && $cedula ? $tipoCedula . '-' . $cedula : ($cedula ?? 'N/A');
                            return '<span class="affiliate-cedula">' . $formatted . '</span>';
                        }
                    }
                    // Si es pago individual normal
                    else {
                        if ($model->userDatos) {
                            $cedula = $model->userDatos->cedula;
                            $tipoCedula = $model->userDatos->tipo_cedula;
                            $formatted = $tipoCedula && $cedula ? $tipoCedula . '-' . $cedula : ($cedula ?? 'N/A');
                            return '<span class="individual-cedula">' . $formatted . '</span>';
                        }
                    }

                    return 'N/A';
                },
                'label' => 'IDENTIFICACIÓN',
                'format' => 'raw',
                'headerOptions' => ['style' => 'text-align: center;'],
                'contentOptions' => function ($model) {
                    if ($model->tipo_pago === 'corporativo' && $model->corporativo_id) {
                        return ['style' => 'text-align: center; vertical-align: middle; background-color: rgba(102, 16, 242, 0.03);'];
                    } elseif ($model->pago_corporativo_id) {
                        return ['style' => 'text-align: center; vertical-align: middle; background-color: rgba(255, 193, 7, 0.03);'];
                    } else {
                        return ['style' => 'text-align: center; vertical-align: middle; background-color: rgba(23, 162, 184, 0.03);'];
                    }
                },
            ],

            // Add a type column for clarity
            [
                'label' => 'TIPO',
                'value' => function ($model) {
                    // Pago corporativo principal
                    if ($model->tipo_pago === 'corporativo' && $model->corporativo_id) {
                        return '<span class="corporate-badge-pro">
                            <i class="fas fa-building"></i>
                            CORPORACIÓN
                        </span>';
                    }
                    // Pago de afiliado corporativo
                    elseif ($model->pago_corporativo_id) {
                        return '<span class="affiliate-badge-pro">
                            <i class="fas fa-users"></i>
                            AFILIADO
                        </span>';
                    }
                    // Pago individual normal
                    else {
                        return '<span class="individual-badge-pro">
                            <i class="fas fa-user"></i>
                            INDIVIDUAL
                        </span>';
                    }
                },
                'format' => 'raw',
                'contentOptions' => function ($model) {
                    $options = ['style' => 'text-align: center; vertical-align: middle;'];

                    if ($model->tipo_pago === 'corporativo' && $model->corporativo_id) {
                        $options['class'] = 'corporate-tipo-cell';
                    } elseif ($model->pago_corporativo_id) {
                        $options['class'] = 'affiliate-tipo-cell';
                    } else {
                        $options['class'] = 'individual-tipo-cell';
                    }

                    return $options;
                },
                'headerOptions' => ['style' => 'text-align: center;'],
                'filter' => [
                    'individual' => 'Individual',
                    'corporativo' => 'Corporación',
                    'afiliado' => 'Afiliado Corporativo'
                ],
                'filterType' => GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'options' => ['placeholder' => 'Filtrar tipo...'],
                    'pluginOptions' => ['allowClear' => true],
                ],
            ],
            [
                'label' => 'PAGO CORP.',
                'value' => function ($model) {
                    if ($model->pago_corporativo_id && $model->pagoCorporativo) {
                        return '<small>Ref: ' . ($model->pagoCorporativo->numero_referencia_pago ?? 'N/A') . '</small>';
                    } elseif ($model->tipo_pago === 'corporativo') {
                        return '<small class="text-success"><i class="fas fa-crown"></i> Pago Principal</small>';
                    }
                    return '-';
                },
                'format' => 'raw',
                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                'headerOptions' => ['style' => 'text-align: center;'],
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
    ]);

    ?>

</div>