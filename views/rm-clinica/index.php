<?php

// Importaciones necesarias
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use kartik\widgets\SwitchInput;
use app\components\UserHelper;

/**
 * @var yii\web\View $this
 * @var app\models\RmClinicaSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 */

// --- BREADCRUMBS ---
$this->params['breadcrumbs'][] = ['label' => 'CLÍNICAS', 'url' => ['index']];
// --- FIN  --- 

$this->title = 'GESTIÓN DE CLÍNICAS';

// Permisos basados en el rol del usuario
$rol = UserHelper::getMyRol();
$permisosCrear = ($rol == 'superadmin' || $rol == 'DIRECTOR-COMERCIALIZACIÓN' || $rol == 'Administrador-clinica');
$permisosSiniestros = ($rol == 'superadmin' || $rol == 'DIRECTOR-COMERCIALIZACIÓN');
?>

<div class="row" style="margin:3px !important;">
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">

            <div class="ms-panel-header d-flex justify-content-between align-items-center mb-3">
                <h1 class="m-0"><?= Html::encode($this->title) ?></h1>

                <?php if ($permisosCrear): ?>
                    <div>
                        <?= Html::a('<i class="fas fa-plus"></i> CREAR NUEVA CLÍNICA', ['create'], ['class' => 'btn btn-primary']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="ms-panel-body">
                <div class="table-responsive">
                    <?= GridView::widget([
                        'id' => 'clinica-grid',
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'layout' => "{items}{pager}",
                        'resizableColumns' => false,
                        'bordered' => false,
                        'responsiveWrap' => false,
                        'persistResize' => false,
                        'tableOptions' => [
                            'class' => 'table table-striped table-bordered table-hover'
                        ],
                        'options' => [
                            'class' => 'grid-view-container table-responsive',
                        ],
                        'columns' => [
                            // Replace the ID column with this:
                            [
                                'attribute' => 'codigo_clinica',
                                'label' => 'CÓDIGO',
                                'headerOptions' => ['style' => 'color: white!important; width: 110px;'],
                                'contentOptions' => ['class' => 'text-center'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar código',
                                    'class' => 'form-control form-control-lg text-center',
                                ],
                                'value' => function ($model) {
                                    return Html::tag('span', Html::encode($model->codigo_clinica), [
                                        'class' => 'badge',
                                        'style' => 'background: linear-gradient(135deg, #0078D4 0%, #005a9e 100%); color: white; font-weight: 600; font-size: 0.85rem; padding: 6px 14px; border-radius: 20px; letter-spacing: 0.5px; display: inline-block;'
                                    ]);
                                },
                                'format' => 'raw',
                            ],
                            // Nombre
                            [
                                'attribute' => 'nombre',
                                'label' => 'CLÍNICA',
                                'format' => 'ntext',
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'options' => ['style' => 'width: 250px;'],
                                'contentOptions' => ['class' => 'text-center'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar nombre',
                                    'class' => 'form-control form-control-lg text-center',
                                ],
                            ],
                            // RIF
                            [
                                'attribute' => 'rif',
                                'label' => 'RIF',
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'options' => ['style' => 'width: 130px;'],
                                'contentOptions' => ['class' => 'text-center'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar RIF',
                                    'class' => 'form-control form-control-lg text-center',
                                ],
                            ],
                            // Teléfono
                            [
                                'attribute' => 'telefono',
                                'label' => 'TELÉFONO',
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'options' => ['style' => 'width: 140px;'],
                                'contentOptions' => ['class' => 'text-center'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar teléfono',
                                    'class' => 'form-control form-control-lg text-center',
                                ],
                            ],
                            // Correo
                            [
                                'attribute' => 'correo',
                                'label' => 'CORREO',
                                'format' => 'email',
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'options' => ['style' => 'width: 250px;'],
                                'contentOptions' => ['class' => 'text-center text-truncate', 'style' => 'max-width: 220px;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar correo',
                                    'class' => 'form-control form-control-lg text-center',
                                ],
                            ],
                            // Estado - Con colores diferentes según el estado
                            [
                                'attribute' => 'estado',
                                'label' => 'ESTADO',
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'options' => ['style' => 'width: 180px;'],
                                'contentOptions' => ['class' => 'text-center'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar estado',
                                    'class' => 'form-control form-control-lg text-center',
                                ],
                                'value' => function ($model) {
                                    // Cache de estados para evitar múltiples consultas
                                    static $estados = [];

                                    $estadoValue = $model->estado;

                                    // Si es numérico, buscar en cache o en BD
                                    if (is_numeric($estadoValue)) {
                                        if (!isset($estados[$estadoValue])) {
                                            $estados[$estadoValue] = \app\models\RmEstado::find()
                                                ->select('nombre')
                                                ->where(['id' => (int)$estadoValue])
                                                ->scalar();
                                        }
                                        $nombreEstado = $estados[$estadoValue] ?: $estadoValue;
                                    } else {
                                        $nombreEstado = $estadoValue;
                                    }

                                    // Colores diferentes según la región (ejemplo)
                                    $colorStyles = [
                                        'Distrito Capital' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                                        'Miranda' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                                        'Aragua' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                                        'Carabobo' => 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
                                        'Zulia' => 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
                                        'Bolívar' => 'linear-gradient(135deg, #30cfd0 0%, #330867 100%)',
                                    ];

                                    $gradient = $colorStyles[$nombreEstado] ?? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';

                                    return Html::tag('span', Html::encode($nombreEstado), [
                                        'class' => 'badge',
                                        'style' => "background: {$gradient}; color: white; font-weight: 600; font-size: 0.95rem; padding: 8px 18px; border-radius: 30px; letter-spacing: 0.5px; box-shadow: 0 3px 6px rgba(0,0,0,0.15); display: inline-block; min-width: 100px;"
                                    ]);
                                },
                                'format' => 'raw',
                                'filter' => \kartik\select2\Select2::widget([
                                    'model' => $searchModel,
                                    'attribute' => 'estado',
                                    'data' => \yii\helpers\ArrayHelper::map(
                                        \app\models\RmEstado::find()->orderBy('nombre')->all(),
                                        'nombre',
                                        'nombre'
                                    ),
                                    'options' => [
                                        'placeholder' => 'Filtrar por Estado',
                                        'class' => 'form-control'
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'width' => '100%',
                                    ],
                                ]),
                            ],
                            // Estatus - Switch
                            [
                                'label' => 'ESTATUS',
                                'attribute' => 'estatus',
                                'format' => 'raw',
                                'headerOptions' => ['class' => 'text-center', 'style' => 'color: white!important; width: 110px;'],
                                'contentOptions' => ['class' => 'text-center'],
                                'value' => function ($model) {
                                    $isActive = ($model->estatus === 'Activo' || $model->estatus === 1 || $model->estatus === true);
                                    return SwitchInput::widget([
                                        'name' => 'status_' . $model->id,
                                        'value' => $isActive,
                                        'pluginEvents' => [
                                            'switchChange.bootstrapSwitch' => "function(e){ updatestatus('{$model->id}'); }"
                                        ],
                                        'pluginOptions' => [
                                            'onText' => 'Activo',
                                            'offText' => 'Inactivo',
                                            'onColor' => 'success',
                                            'offColor' => 'secondary',
                                            'state' => $isActive,
                                            'size' => 'small',
                                        ],
                                        'options' => [
                                            'id' => 'status-switch-' . $model->id,
                                            'title' => $isActive ? 'Click para desactivar' : 'Click para activar'
                                        ],
                                    ]);
                                },
                                'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                                'filter' => ['Activo' => 'Activo', 'Inactivo' => 'Inactivo'],
                                'filterWidgetOptions' => [
                                    'pluginOptions' => ['allowClear' => true, 'width' => '100%', 'minimumResultsForSearch' => -1],
                                ],
                                'filterInputOptions' => ['placeholder' => 'Todos', 'class' => 'form-control form-control-lg'],
                            ],
                            // Columna de Acciones - Mismos iconos que Afiliados
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => 'ACCIONES',
                                'template' => '<div class="d-flex justify-content-center gap-0">{view}{update}{siniestros}{indicator}</div>',
                                'options' => ['style' => 'width:100px; min-width:100px;'],
                                'headerOptions' => ['style' => 'color: white!important; text-align: center;'],
                                'contentOptions' => ['style' => 'text-align: center; padding: 8px !important;'],
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fa fa-eye"></i>',
                                            Url::to(['view', 'id' => $model->id]),
                                            [
                                                'title' => 'Detalle de la Clínica',
                                                'class' => 'btn-action view'
                                            ]
                                        );
                                    },
                                    'update' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fas fa-pencil-alt ms-text-primary"></i>',
                                            Url::to(['update', 'id' => $model->id]),
                                            [
                                                'title' => 'Editar',
                                                'class' => 'btn-action view'
                                            ]
                                        );
                                    },
                                    'siniestros' => function ($url, $model, $key) use ($permisosSiniestros) {
                                        if ($permisosSiniestros) {
                                            return Html::a(
                                                '<i class="fas fa-heartbeat" style="color: red;"></i>',
                                                Url::to(['/sis-siniestro/por-clinica', 'clinica_id' => $model->id]),
                                                [
                                                    'title' => 'Siniestros de esta Clínica',
                                                    'class' => 'btn-action view'
                                                ]
                                            );
                                        }
                                        return '';
                                    },
                                    'indicator' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fa fa-chart-line"></i>',
                                            Url::to(['indicator', 'id' => $model->id]),
                                            [
                                                'title' => 'Indicadores de la Clínica',
                                                'class' => 'btn-action indicator'
                                            ]
                                        );
                                    },
                                ],
                            ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// JavaScript para actualización de estatus
$js = <<<'JS'
// Función para actualizar estado vía AJAX
window.updatestatus = function(id) {
    var isChecked = $('#status-switch-' + id).bootstrapSwitch('state');
    var newStatus = isChecked ? 'Activo' : 'Inactivo';
    
    $.ajax({
        url: '/rm-clinica/update-status',
        type: 'POST',
        data: {
            id: id,
            estatus: newStatus,
            _csrf: $('#csrf-token').val()
        },
        success: function(response) {
            if (response && response.success) {
                // Feedback visual - resaltado verde
                var row = $('#status-switch-' + id).closest('tr');
                row.css('background-color', '#d4edda');
                setTimeout(function() {
                    row.css('background-color', '');
                }, 500);
            } else {
                // Revertir en caso de error
                $('#status-switch-' + id).bootstrapSwitch('state', !isChecked);
                var row = $('#status-switch-' + id).closest('tr');
                row.css('background-color', '#f8d7da');
                setTimeout(function() {
                    row.css('background-color', '');
                }, 500);
            }
        },
        error: function() {
            $('#status-switch-' + id).bootstrapSwitch('state', !isChecked);
            var row = $('#status-switch-' + id).closest('tr');
            row.css('background-color', '#f8d7da');
            setTimeout(function() {
                row.css('background-color', '');
            }, 500);
        }
    });
};
JS;

$this->registerJs($js, \yii\web\View::POS_READY);

// Registrar CSS consistente
$this->registerCss("
    /* Estilos para tabla */
    .table th {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white !important;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 8px;
        border-bottom: none;
    }
    
    .table td {
        padding: 10px 8px;
        vertical-align: middle;
        border-bottom: 1px solid #ecf0f1;
        font-size: 0.85rem;
    }
    
    .table tbody tr:hover {
        background-color: #f5f7fd !important;
        transition: background-color 0.2s ease;
    }
    
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: #fafbfc;
    }
    
    /* Botones de acción - Mismos estilos que Afiliados */
    .btn-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: #f8f9fa;
        color: #6c757d;
        transition: all 0.2s ease;
        text-decoration: none;
        margin: 0 2px;
    }
    
    .btn-action:hover {
        transform: translateY(-2px);
        text-decoration: none;
    }
    
    .btn-action.view:hover {
        background-color: #0078D4;
        color: white;
    }
    
    .btn-action.indicator:hover {
        background-color: #28a745;
        color: white;
    }
    
    /* Formularios de filtro */
    .form-control.form-control-lg {
        font-size: 0.85rem;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        border: 1px solid #d1d5db;
        height: auto;
    }
    
    .form-control.form-control-lg:focus {
        border-color: #0078D4;
        box-shadow: 0 0 0 3px rgba(0, 120, 212, 0.1);
    }
    
    /* Badge para estado - Ahora con tamaño normal */
    .badge {
        font-size: 0.85rem;
        font-weight: 500;
        padding: 6px 12px;
        display: inline-block;
    }
    
    /* Paginación */
    .pagination {
        margin-top: 15px;
        margin-bottom: 0;
        justify-content: flex-end;
    }
    
    .pagination .page-link {
        color: #0078D4;
        border-radius: 6px;
        margin: 0 2px;
        padding: 6px 12px;
        font-size: 0.8rem;
    }
    
    .pagination .active .page-link {
        background-color: #0078D4;
        border-color: #0078D4;
    }
    
    /* Text truncate para correo */
    .text-truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* D-flex para acciones */
    .d-flex {
        display: flex !important;
    }
    
    .justify-content-center {
        justify-content: center !important;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .btn-action {
            width: 28px;
            height: 28px;
            font-size: 0.7rem;
        }
        
        .table th, .table td {
            padding: 6px 4px;
            font-size: 0.75rem;
        }
        
        .form-control.form-control-lg {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
        
        .badge {
            font-size: 0.7rem;
            padding: 4px 8px;
        }
    }
        /* Style for the código badge */
        .table .badge-code {
            background: linear-gradient(135deg, #0078D4 0%, #005a9e 100%);
            color: white;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 6px 14px;
            border-radius: 20px;
            letter-spacing: 0.5px;
            display: inline-block;
        }
");
?>