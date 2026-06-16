<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use app\models\Agente;
use app\models\AgenteFuerza;
use app\models\User;
use app\components\UserHelper;

/**
 * @var yii\web\View $this
 * @var app\models\AgenteSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 */

// --- BREADCRUMBS ---
$this->params['breadcrumbs'][] = ['label' => 'AGENCIAS', 'url' => ['index']];
// --- FIN ---

$this->title = 'GESTIÓN DE AGENCIAS';

$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol == 'GERENTE-COMERCIALIZACION');

// Force ascending order by ID
$dataProvider->sort->defaultOrder = ['id' => SORT_ASC];
$dataProvider->setSort([
    'defaultOrder' => ['id' => SORT_ASC],
    'attributes' => [
        'id' => [
            'asc' => ['t.id' => SORT_ASC],
            'desc' => ['t.id' => SORT_DESC],
            'label' => 'ID',
        ],
        'nom' => [
            'asc' => ['t.nom' => SORT_ASC],
            'desc' => ['t.nom' => SORT_DESC],
            'label' => 'AGENCIAS',
        ],
        'sudeaseg' => [
            'asc' => ['t.sudeaseg' => SORT_ASC],
            'desc' => ['t.sudeaseg' => SORT_DESC],
            'label' => 'Código SUDEASEG',
        ],
        'propietarioEmail' => [
            'asc' => ['user.email' => SORT_ASC],
            'desc' => ['user.email' => SORT_DESC],
            'label' => 'Correo del Propietario',
        ],
        'propietarioCedula' => [
            'asc' => ['userDatos.cedula' => SORT_ASC],
            'desc' => ['userDatos.cedula' => SORT_DESC],
            'label' => 'Cédula del Propietario',
        ],
        'propietarioNombreCompleto' => [
            'asc' => ['userDatos.nombres' => SORT_ASC, 'userDatos.apellidos' => SORT_ASC],
            'desc' => ['userDatos.nombres' => SORT_DESC, 'userDatos.apellidos' => SORT_DESC],
            'label' => 'Propietario',
        ],
    ],
]);

// Register custom CSS for professional Microsoft-style layout
$this->registerCss("
    /* Microsoft Fluent Design System - Professional Styling */
    :root {
        --ms-blue: #0078d4;
        --ms-dark-blue: #106ebe;
        --ms-green: #107c10;
        --ms-red: #d13438;
        --ms-gray-100: #f3f2f1;
        --ms-gray-200: #e1dfdd;
        --ms-gray-300: #c8c6c4;
        --ms-gray-400: #a19f9d;
        --ms-gray-500: #797775;
        --ms-gray-600: #484644;
        --ms-gray-700: #252423;
        --ms-purple: #5c2e91;
        --ms-teal: #008272;
    }
    
    /* Main Panel */
    .ms-panel {
        border-radius: 8px;
        box-shadow: 0 1.6px 3.6px 0 rgba(0, 0, 0, 0.132), 0 0.3px 0.9px 0 rgba(0, 0, 0, 0.108);
        background: #fff;
        overflow: hidden;
    }
    
    /* Panel Header - Clean Centered Layout */
    .ms-panel-header {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        padding: 1rem 2rem;
    }
    
    .header-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
    }
    
    .header-center {
        display: flex;
        align-items: center;
        gap: 5.5rem;
    }
    
    .header-title {
        display: flex;
        align-items: center;
        gap: 1.75rem;
    }
    
    .header-title h1 {
        font-size: 1.35rem;
        font-weight: 600;
        letter-spacing: -0.2px;
        margin: 0;
        color: #ffffff !important;
        font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
    }
    
    .header-title i {
        font-size: 1.35rem;
        color: #ffffff;
    }
    
    .record-count {
        background: rgba(255, 255, 255, 0.95);
        padding: 6px 16px;
        border-radius: 30px;
        font-size: 1.1rem;
        color: #1e3c72 !important;
        font-weight: 600;
        backdrop-filter: blur(10px);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }
    
    .record-count i {
        font-size: 0.85rem;
        color: #1e3c72 !important;
    }
    
    .record-count span {
        color: #1e3c72 !important;
        font-weight: 600;
    }
    
    .header-left {
        visibility: hidden;
    }
    
    /* Create Button - Microsoft Style */
    .btn-create {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        border: none;
        border-radius: 6px;
        padding: 8px 20px;
        font-weight: 600;
        font-size: 1.1rem;
        letter-spacing: 0.5px;
        transition: all 0.2s ease;
        color: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        white-space: nowrap;
    }
    
    .btn-create:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 124, 16, 0.3);
        background: linear-gradient(135deg, #d35400 0%, #e67e22 100%);
        color: white;
        text-decoration: none;
    }
    
    .btn-create i {
        font-size: 0.9rem;
    }
    
    /* Table Styling */
    .table {
        font-size: 0.85rem;
        margin-bottom: 0;
        font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
    }
    
    .table thead th {
        background: #2c3e50;
        color: #ffffff !important;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.7rem;
        border-bottom: 2px solid #1a252f;
        vertical-align: middle;
        padding: 14px 12px;
        position: sticky;
        top: 0;
        z-index: 10;
        white-space: nowrap;
    }
    
    .table tbody td {
        vertical-align: middle;
        padding: 12px 12px;
        border-bottom: 1px solid #e9ecef;
        transition: background-color 0.2s ease;
    }
    
    .table tbody tr:hover td {
        background-color: #f8f9fa;
    }
    
    /* Scrollable table container */
    .scrollable-table-container {
        max-height: 70vh;
        overflow-y: auto;
        overflow-x: auto;
        border-radius: 0;
        scrollbar-width: thin;
    }
    
    .scrollable-table-container::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    
    .scrollable-table-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .scrollable-table-container::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }
    
    .scrollable-table-container::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    
    /* SUDEASEG Code Badge - White Text, Larger */
    .sudeaseg-badge {
        background: linear-gradient(135deg, #5c2e91 0%, #7b4b9e 100%);
        color: #ffffff !important;
        font-size: 1.1rem !important;
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 6px;
        display: inline-block;
        font-family: 'Consolas', 'Courier New', monospace;
        letter-spacing: 0.5px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }
    
    /* Sales Force Badge - White Text, Larger */
    .force-badge {
        background: linear-gradient(135deg, #008272 0%, #00a393 100%);
        color: #ffffff !important;
        font-size: 1.1rem !important;
        font-weight: 700;
        padding: 6px 14px;
        border-radius: 20px;
        display: inline-block;
        min-width: 50px;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease;
    }
    
    .force-badge:hover {
        transform: scale(1.05);
    }
    
    .force-badge-zero {
        background: #797775;
        color: #ffffff !important;
        font-size: 0.9rem !important;
        font-weight: 700;
        padding: 6px 14px;
        border-radius: 20px;
        display: inline-block;
        min-width: 50px;
        text-align: center;
    }
    
    /* Cédula field - Increased font size */
    .cedula-value {
        font-size: 1.1rem !important;
        font-weight: 500;
        color: #2c3e50;
        font-family: 'Consolas', 'Courier New', monospace;
        letter-spacing: 0.5px;
    }
    
    /* Filter inputs */
    .form-control-sm {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
        border-radius: 4px;
        border: 1px solid #ced4da;
        transition: all 0.15s ease-in-out;
        font-family: 'Segoe UI', sans-serif;
    }
    
    .form-control-sm:focus {
        border-color: #0078d4;
        box-shadow: 0 0 0 0.2rem rgba(0, 120, 212, 0.25);
    }
    
    /* Action Buttons - Compact Professional Styling */
    .btn-action {
        transition: all 0.2s ease;
        border-radius: 4px;
        padding: 4px 10px;
        font-size: 1.1rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        text-decoration: none;
        cursor: pointer;
        border: none;
    }
    
    .btn-view {
        background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);
        color: white;
        box-shadow: 0 1px 2px rgba(0, 120, 212, 0.2);
    }
    
    .btn-view:hover {
        background: linear-gradient(135deg, #106ebe 0%, #005a9e 100%);
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 120, 212, 0.3);
        color: white;
        text-decoration: none;
    }
    
    .btn-view i {
        font-size: 0.7rem;
    }
    
    .btn-edit {
        background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
        color: #252423;
        box-shadow: 0 1px 2px rgba(255, 193, 7, 0.2);
    }
    
    .btn-edit:hover {
        background: linear-gradient(135deg, #ffb300 0%, #ffa000 100%);
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(255, 193, 7, 0.3);
        color: #252423;
        text-decoration: none;
    }
    
    .btn-edit i {
        font-size: 0.7rem;
    }
    
    /* Cell text alignment */
    .text-center {
        text-align: center !important;
    }
    
    .align-middle {
        vertical-align: middle !important;
    }
    
    /* Empty value styling */
    .empty-value {
        color: #a19f9d;
        font-style: italic;
        font-size: 0.8rem;
    }
    
    /* Panel body padding */
    .ms-panel-body {
        padding: 0;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .ms-panel-header {
            padding: 1rem;
        }
        
        .header-container {
            flex-direction: column;
            gap: 1rem;
        }
        
        .header-center {
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .header-left {
            display: none;
        }
        
        .btn-create {
            width: 100%;
            justify-content: center;
        }
    }
");
?>

<div class="row" style="margin: 0 !important;">
    <div class="col-xl-12 col-md-12" style="padding: 0 !important;">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header">
                <div class="header-container">
                    <!-- Left spacer for balance -->
                    <div class="header-left">
                        <div style="width: 160px;"></div>
                    </div>

                    <!-- Centered content -->
                    <div class="header-center">
                        <div class="header-title">
                            <i class="fas fa-building"></i>
                            <h1><?= Html::encode($this->title) ?></h1>
                        </div>
                        <div class="record-count">
                            <i class="fas fa-chart-line"></i>
                            <span>Total: <?= number_format($dataProvider->getTotalCount()) ?> agencias</span>
                        </div>
                    </div>

                    <!-- Right side button -->
                    <div class="header-right">
                        <?php if ($permisos) { ?>
                            <?= Html::a('<i class="fas fa-plus-circle"></i> CREAR NUEVA AGENCIA', ['create'], [
                                'class' => 'btn-create',
                            ]) ?>
                        <?php } else { ?>
                            <div style="width: 160px;"></div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="ms-panel-body">
                <div class="scrollable-table-container">
                    <?= GridView::widget([
                        'id' => 'clinica-grid',
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'layout' => "{items}",
                        'resizableColumns' => false,
                        'bordered' => false,
                        'responsiveWrap' => false,
                        'persistResize' => false,
                        'tableOptions' => [
                            'class' => 'table table-hover',
                            'style' => 'margin-bottom: 0;'
                        ],
                        'options' => [
                            'class' => 'grid-view-container',
                        ],
                        'columns' => [
                            // ID Column
                            [
                                'attribute' => 'id',
                                'label' => 'ID',
                                'headerOptions' => ['style' => 'width: 70px;'],
                                'contentOptions' => ['class' => 'text-center align-middle', 'style' => 'font-weight: 600;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar ID',
                                    'class' => 'form-control form-control-sm text-center',
                                ],
                                'enableSorting' => true,
                            ],

                            // Agency Name
                            [
                                'attribute' => 'nom',
                                'label' => 'AGENCIA',
                                'format' => 'ntext',
                                'headerOptions' => ['style' => 'min-width: 220px;'],
                                'contentOptions' => ['class' => 'align-middle', 'style' => 'font-weight: 500;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar agencia',
                                    'class' => 'form-control form-control-sm',
                                ],
                            ],

                            // SUDEASEG Code
                            [
                                'attribute' => 'sudeaseg',
                                'label' => 'CÓDIGO SUDEASEG',
                                'format' => 'raw',
                                'headerOptions' => ['style' => 'width: 150px;'],
                                'contentOptions' => ['class' => 'text-center align-middle'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar código',
                                    'class' => 'form-control form-control-sm text-center',
                                ],
                                'value' => function ($model) {
                                    if (!empty($model->sudeaseg)) {
                                        return Html::tag('span', Html::encode($model->sudeaseg), [
                                            'class' => 'sudeaseg-badge',
                                        ]);
                                    }
                                    return Html::tag('span', '—', ['class' => 'empty-value']);
                                },
                            ],

                            // Owner Name
                            [
                                'attribute' => 'propietarioNombreCompleto',
                                'label' => 'PROPIETARIO',
                                'headerOptions' => ['style' => 'min-width: 200px;'],
                                'contentOptions' => ['class' => 'align-middle'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar propietario',
                                    'class' => 'form-control form-control-sm',
                                ],
                                'value' => function ($model) {
                                    if ($model->propietario) {
                                        return Html::encode($model->propietario->nombres . ' ' . $model->propietario->apellidos);
                                    }
                                    return Html::tag('span', 'No asignado', ['class' => 'empty-value']);
                                },
                            ],

                            // Owner Email
                            [
                                'attribute' => 'propietarioEmail',
                                'label' => 'CORREO',
                                'contentOptions' => ['class' => 'align-middle'],
                                'headerOptions' => ['style' => 'min-width: 200px;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar correo',
                                    'class' => 'form-control form-control-sm',
                                ],
                                'value' => function ($model) {
                                    if ($model->propietario && $model->propietario->email) {
                                        return Html::mailto(Html::encode($model->propietario->email), $model->propietario->email, [
                                            'class' => 'text-primary',
                                            'style' => 'text-decoration: none; font-weight: 500;'
                                        ]);
                                    }
                                    return Html::tag('span', 'N/A', ['class' => 'empty-value']);
                                },
                                'format' => 'raw',
                            ],

                            // Owner ID Card
                            [
                                'attribute' => 'propietarioCedula',
                                'label' => 'CÉDULA',
                                'contentOptions' => ['class' => 'text-center align-middle'],
                                'headerOptions' => ['style' => 'width: 130px;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar cédula',
                                    'class' => 'form-control form-control-sm text-center',
                                ],
                                'value' => function ($model) {
                                    if ($model->propietario && $model->propietario->cedula) {
                                        return Html::tag('span', Html::encode($model->propietario->cedula), [
                                            'class' => 'cedula-value'
                                        ]);
                                    }
                                    return Html::tag('span', 'N/A', ['class' => 'empty-value']);
                                },
                                'format' => 'raw',
                            ],

                            // Sales Force Count
                            [
                                'attribute' => 'agenteFuerzaCount',
                                'label' => 'FUERZA VENTA',
                                'headerOptions' => ['style' => 'width: 130px; text-align: center;'],
                                'contentOptions' => ['class' => 'text-center align-middle'],
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $count = User::find()
                                        ->joinWith('userDatos')
                                        ->leftJoin('auth_assignment', '"user"."id" = CAST("auth_assignment"."user_id" AS INTEGER)')
                                        ->leftJoin('agente_fuerza', '"agente_fuerza"."idusuario" = "user_datos"."id"')
                                        ->where(['auth_assignment.item_name' => "Asesor"])
                                        ->andWhere(['agente_id' => $model->id])
                                        ->andWhere(['is not', 'agente_fuerza.idusuario', null])
                                        ->count();

                                    if ($count > 0) {
                                        return Html::a(
                                            '<span class="force-badge">' . number_format($count) . '</span>',
                                            ['agente-fuerza/index-by-agente', 'agente_id' => $model->id],
                                            [
                                                'title' => 'Ver asesores de esta agencia',
                                                'data-pjax' => '0',
                                                'class' => 'text-decoration-none'
                                            ]
                                        );
                                    }
                                    return '<span class="force-badge-zero">0</span>';
                                },
                            ],

                            // Action Buttons - Compact
                            [
                                'class' => ActionColumn::class,
                                'header' => 'ACCIONES',
                                'template' => '{view} {update}',
                                'options' => ['class' => 'action-buttons'],
                                'headerOptions' => ['style' => 'width: 130px; text-align: center;'],
                                'contentOptions' => ['class' => 'text-center align-middle'],
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fas fa-eye"></i> Ver',
                                            $url,
                                            [
                                                'title' => 'Ver Detalle',
                                                'class' => 'btn-action btn-view mb-3',
                                                'data-toggle' => 'tooltip'
                                            ]
                                        );
                                    },
                                    'update' => function ($url, $model, $key) use ($permisos) {
                                        if ($permisos) {
                                            return Html::a(
                                                '<i class="fas fa-edit"></i> Editar',
                                                $url,
                                                [
                                                    'title' => 'Editar',
                                                    'class' => 'btn-action btn-edit',
                                                    'data-toggle' => 'tooltip'
                                                ]
                                            );
                                        }
                                        return '';
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
// Register tooltip initialization
$this->registerJs("
    $(function () {
        $('[data-toggle=\"tooltip\"]').tooltip();
    });
");
?>