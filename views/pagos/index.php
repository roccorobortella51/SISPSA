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
/* Jerarquía Visual */
.afiliado-indent {
    position: relative;
}

.afiliado-indent::before {
    content: "";
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: rgba(255, 193, 7, 0.3);
}

/* Pago Corporativo Principal */
.corporate-main-payment {
    padding: 8px;
}

.corporate-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 4px;
}

.corporate-main-cell {
    border-top: 2px solid #6610f2 !important;
    border-bottom: 2px solid #6610f2 !important;
}

/* Pago de Afiliado */
.affiliate-payment {
    padding: 8px 8px 8px 30px;
    position: relative;
}

.affiliate-indent {
    display: flex;
    align-items: center;
}

.affiliate-details {
    margin-top: 4px;
    padding-left: 24px;
}

.affiliate-cell {
    border-left: 3px solid rgba(255, 193, 7, 0.5) !important;
}

/* Badges de Tipo Mejorados */
.corporate-type-badge, .affiliate-type-badge, .individual-type-badge {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 8px;
    border-radius: 6px;
}

.corporate-type-badge {
    background: linear-gradient(135deg, rgba(102, 16, 242, 0.1), rgba(102, 16, 242, 0.05));
    color: #6610f2;
    border: 1px solid rgba(102, 16, 242, 0.2);
}

.affiliate-type-badge {
    background: linear-gradient(135deg, rgba(255, 193, 7, 0.1), rgba(255, 193, 7, 0.05));
    color: #e0a800;
    border: 1px solid rgba(255, 193, 7, 0.2);
}

.individual-type-badge {
    background: linear-gradient(135deg, rgba(23, 162, 184, 0.1), rgba(23, 162, 184, 0.05));
    color: #138496;
    border: 1px solid rgba(23, 162, 184, 0.2);
}

/* Conexiones visuales */
.payment-connection {
    padding: 5px;
}

.connection-line {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 3px;
    padding-top: 3px;
    border-top: 1px dashed #dee2e6;
}

.parent-payment-ref {
    padding: 5px;
    background-color: rgba(102, 16, 242, 0.05);
    border-radius: 4px;
}

/* Estilos de identificación */
.corporate-id {
    text-align: center;
}

.corporate-rif {
    background-color: rgba(102, 16, 242, 0.1);
    color: #6610f2;
    font-weight: bold;
    padding: 6px 12px;
    border-radius: 4px;
    display: inline-block;
    font-size: 1.1em;
}

.affiliate-cedula {
    background-color: rgba(255, 193, 7, 0.1);
    color: #e0a800;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 4px;
    border: 1px solid rgba(255, 193, 7, 0.2);
    display: inline-block;
}

.individual-cedula {
    background-color: rgba(23, 162, 184, 0.1);
    color: #138496;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 4px;
    border: 1px solid rgba(23, 162, 184, 0.2);
    display: inline-block;
}
/* Estilos para grupos ordenados */
.corporate-group-start {
    border-left: 4px solid #6610f2 !important;
}

.corporate-group-header-cell {
    background: linear-gradient(to right, rgba(102, 16, 242, 0.1), rgba(102, 16, 242, 0.05)) !important;
    border-bottom: 1px solid rgba(102, 16, 242, 0.2) !important;
}

.corporate-main-group {
    padding: 10px 5px;
}

.corporate-subtitle {
    margin-top: 5px;
    padding-left: 28px;
}

/* Grupo de afiliados */
.affiliate-in-group-cell {
    position: relative;
}

.affiliate-in-group-cell::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 15px;
    background: linear-gradient(to right, rgba(255, 193, 7, 0.1), transparent);
}

.group-connector {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 8px;
    padding: 4px 8px;
    background-color: rgba(0, 0, 0, 0.02);
    border-radius: 4px;
    font-size: 0.85em;
}

.affiliate-in-group-row {
    padding: 8px 0 8px 20px;
}

.affiliate-indent {
    display: flex;
    align-items: center;
    gap: 8px;
    padding-left: 5px;
}

/* Separadores visuales entre grupos */
.corporate-group-header-cell + .affiliate-in-group-cell {
    border-top: none !important;
}

.affiliate-in-group-cell:last-of-type {
    border-bottom: 2px dashed #dee2e6 !important;
}

/* Para pagos individuales normales */
.individual-payment-single {
    padding: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.individual-single-cell {
    border-top: 1px solid #f8f9fa !important;
}
CSS;

$this->registerCss($css);
?>

<div class="pagos-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,

        'columns' => [
            // Columna # con indicadores de jerarquía
            [
                'class' => 'yii\grid\SerialColumn',
                'contentOptions' => function ($model, $key, $index, $column) use ($dataProvider) {
                    $isCorporate = ($model->tipo_pago === 'corporativo' && $model->corporativo_id);
                    $isAffiliate = ($model->pago_corporativo_id);

                    $options = ['style' => 'text-align: center;'];

                    if ($isCorporate) {
                        $options['style'] .= ' font-weight: bold; background-color: rgba(102, 16, 242, 0.1);';
                        $options['class'] = 'corporate-group-start';
                    } elseif ($isAffiliate) {
                        $options['style'] .= ' padding-left: 30px; background-color: rgba(255, 193, 7, 0.05);';
                        $options['class'] = 'affiliate-in-group';
                    }

                    return $options;
                },
            ],

            // Columna de Pagador con conexión visual
            [
                'attribute' => 'nombreUsuario',
                'value' => function ($model, $key, $index, $column) use ($dataProvider) {
                    $models = $dataProvider->getModels();
                    $isFirstInGroup = true;
                    $groupLabel = '';

                    // Determinar si es el primero en su grupo
                    if ($index > 0 && $model->pago_corporativo_id) {
                        $prevModel = $models[$index - 1];
                        $isFirstInGroup = ($prevModel->id !== $model->pago_corporativo_id &&
                            !($prevModel->pago_corporativo_id && $prevModel->pago_corporativo_id == $model->pago_corporativo_id));
                    }

                    // 1. PAGO CORPORATIVO PRINCIPAL
                    if ($model->tipo_pago === 'corporativo' && $model->corporativo_id) {
                        $corpName = $model->corporativo ? $model->corporativo->nombre : 'Corporativo';
                        $affiliateCount = count($model->pagosAfiliados);

                        $html = '<div class="corporate-main-group">';
                        $html .= '<div class="corporate-header">';
                        $html .= '<i class="fas fa-building corporate-icon"></i>';
                        $html .= '<strong>' . $corpName . '</strong>';
                        if ($affiliateCount > 0) {
                            $html .= '<span class="badge badge-light ml-2">' . $affiliateCount . ' afiliado(s)</span>';
                        }
                        $html .= '</div>';
                        $html .= '<div class="corporate-subtitle">';
                        $html .= '<small class="text-muted">';
                        $html .= '<i class="fas fa-crown"></i> Pago Corporativo Principal';
                        $html .= '</small>';
                        $html .= '</div>';
                        $html .= '</div>';

                        return $html;
                    }

                    // 2. PAGO DE AFILIADO
                    if ($model->pago_corporativo_id) {
                        $parentPayment = $model->pagoCorporativo;
                        $corpName = $parentPayment && $parentPayment->corporativo ?
                            $parentPayment->corporativo->nombre : 'Corporativo';

                        $html = '<div class="affiliate-in-group-row">';

                        // Línea conectora para el primer afiliado del grupo
                        if ($isFirstInGroup) {
                            $html .= '<div class="group-connector">';
                            $html .= '<i class="fas fa-arrow-down text-muted mr-1"></i>';
                            $html .= '<small class="text-muted">Afiliados de: ' . $corpName . '</small>';
                            $html .= '</div>';
                        }

                        $html .= '<div class="affiliate-info">';
                        $html .= '<div class="affiliate-indent">';
                        $html .= '<i class="fas fa-user individual-icon"></i>';
                        $html .= ($model->userDatos ? $model->userDatos->nombres . ' ' . $model->userDatos->apellidos : 'Afiliado');
                        $html .= '</div>';
                        $html .= '</div>';
                        $html .= '</div>';

                        return $html;
                    }

                    // 3. PAGO INDIVIDUAL NORMAL
                    return $model->userDatos ?
                        '<div class="individual-payment-single">
                    <i class="fas fa-user individual-icon"></i>
                    ' . $model->userDatos->nombres . ' ' . $model->userDatos->apellidos . '
                </div>' : 'N/A';
                },
                'label' => 'PAGADOR / JERARQUÍA',
                'format' => 'raw',
                'contentOptions' => function ($model) {
                    if ($model->tipo_pago === 'corporativo' && $model->corporativo_id) {
                        return [
                            'class' => 'corporate-group-header-cell',
                            'style' => 'vertical-align: middle; border-top: 2px solid #6610f2 !important;'
                        ];
                    }
                    if ($model->pago_corporativo_id) {
                        return [
                            'class' => 'affiliate-in-group-cell',
                            'style' => 'vertical-align: middle;'
                        ];
                    }
                    return [
                        'class' => 'individual-single-cell',
                        'style' => 'vertical-align: middle;'
                    ];
                }
            ],

            // Columna de IDENTIFICACIÓN
            [
                'attribute' => 'cedulaUsuario',
                'value' => function ($model) {
                    // Pago corporativo principal
                    if ($model->tipo_pago === 'corporativo' && $model->corporativo_id) {
                        return $model->corporativo ?
                            '<div class="corporate-id">
                        <span class="corporate-rif">' . $model->corporativo->rif . '</span><br>
                        <small class="text-muted">RIF Corporativo</small>
                    </div>' : 'N/A';
                    }

                    // Pago de afiliado
                    if ($model->pago_corporativo_id) {
                        if ($model->userDatos) {
                            $cedula = $model->userDatos->cedula;
                            $tipoCedula = $model->userDatos->tipo_cedula;
                            $formatted = $tipoCedula && $cedula ? $tipoCedula . '-' . $cedula : ($cedula ?? 'N/A');
                            return '<span class="affiliate-cedula">' . $formatted . '</span>';
                        }
                    }

                    // Pago individual
                    if ($model->userDatos) {
                        $cedula = $model->userDatos->cedula;
                        $tipoCedula = $model->userDatos->tipo_cedula;
                        $formatted = $tipoCedula && $cedula ? $tipoCedula . '-' . $cedula : ($cedula ?? 'N/A');
                        return '<span class="individual-cedula">' . $formatted . '</span>';
                    }

                    return 'N/A';
                },
                'label' => 'IDENTIFICACIÓN',
                'format' => 'raw',
                'contentOptions' => function ($model) {
                    return ['style' => 'text-align: center; vertical-align: middle;'];
                },
            ],

            // Columna TIPO con iconos descriptivos
            [
                'label' => 'TIPO',
                'value' => function ($model) {
                    if ($model->tipo_pago === 'corporativo' && $model->corporativo_id) {
                        return '<div class="corporate-type-badge">
                            <i class="fas fa-crown"></i>
                            <div>CORPORACIÓN</div>
                            <small>Principal</small>
                        </div>';
                    }
                    if ($model->pago_corporativo_id) {
                        return '<div class="affiliate-type-badge">
                            <i class="fas fa-user-friends"></i>
                            <div>AFILIADO</div>
                            <small>Corporativo</small>
                        </div>';
                    }
                    return '<div class="individual-type-badge">
                        <i class="fas fa-user"></i>
                        <div>INDIVIDUAL</div>
                        <small>Independiente</small>
                    </div>';
                },
                'format' => 'raw',
                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                'headerOptions' => ['style' => 'text-align: center;'],
            ],

            // Columna REFERENCIA con conexión visual
            [
                'attribute' => 'numero_referencia_pago',
                'header' => 'REFERENCIA<br>CONEXIÓN',
                'value' => function ($model) {
                    $ref = $model->numero_referencia_pago ?? 'N/A';

                    // Si es afiliado, mostrar conexión al pago padre
                    if ($model->pago_corporativo_id && $model->pagoCorporativo) {
                        $parentRef = $model->pagoCorporativo->numero_referencia_pago ?? 'N/A';
                        return '<div class="payment-connection">
                            <div><small>' . $ref . '</small></div>
                            <div class="connection-line">
                                <i class="fas fa-arrow-up text-success"></i>
                                <small class="text-muted">Vinculado a: ' . $parentRef . '</small>
                            </div>
                        </div>';
                    }

                    // Si es pago corporativo, mostrar que tiene afiliados
                    if ($model->tipo_pago === 'corporativo' && $model->corporativo_id) {
                        $affiliateCount = count($model->pagosAfiliados);
                        return '<div class="parent-payment-ref">
                            <div><strong>' . $ref . '</strong></div>
                            <div>
                                <small class="text-success">
                                    <i class="fas fa-sitemap"></i> Con ' . $affiliateCount . ' afiliado(s)
                                </small>
                            </div>
                        </div>';
                    }

                    return $ref;
                },
                'format' => 'raw',
                'contentOptions' => function ($model) {
                    return ['style' => 'text-align: center; vertical-align: middle;'];
                },
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