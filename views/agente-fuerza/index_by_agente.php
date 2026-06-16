<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\web\JqueryAsset;
use app\models\AgenteFuerza;
use app\models\Agente;
use app\components\UserHelper;


/** @var yii\web\View $this */
/** @var app\models\search\AgenteFuerzaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var int $id_agente */
/** @var app\models\Agente $agente */

$this->title = 'FUERZA DE VENTA';
$this->params['breadcrumbs'][] = ['label' => 'AGENCIAS', 'url' => ['agente/index']];
$this->params['breadcrumbs'][] = ['label' => $agente->nom, 'url' => ['agente/update', 'id' => $agente->id]];
$this->params['breadcrumbs'][] = $this->title;

$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol == 'DIRECTOR-COMERCIALIZACIÓN');

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
    
    /* Main Container */
    .main-container {
        padding: 0;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    /* Header Section */
    .header-section {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        border-radius: 8px 8px 0 0;
        margin-bottom: 2rem;
        overflow: hidden;
    }
    
    /* Title Row */
    .title-row {
        padding: 1rem 2rem;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .title-row h1 {
        font-size: 1.35rem !important;
        font-weight: 600;
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
    }
    
    .left-buttons {
        display: flex;
        gap: 12px;
    }
    
    .right-buttons {
        display: flex;
        gap: 12px;
    }
    
    /* Button Styles */
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
        background: linear-gradient(135deg, #107c10 0%, #0b5e0b 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .btn-primary-custom:hover {
        background: linear-gradient(135deg, #0b5e0b 0%, #0a4d0a 100%);
        box-shadow: 0 4px 12px rgba(16, 124, 16, 0.3);
        color: white;
    }
    
    .btn-blue {
        background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(0, 120, 212, 0.2);
    }
    
    .btn-blue:hover {
        background: linear-gradient(135deg, #106ebe 0%, #005a9e 100%);
        box-shadow: 0 4px 12px rgba(0, 120, 212, 0.3);
        color: white;
    }
    
    .btn-gray {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        color: white !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .btn-gray:hover {
        background: linear-gradient(135deg, #5a6268 0%, #4e555b 100%);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        color: white !important;
    }
    
    .btn-info-custom {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .btn-info-custom:hover {
        background: linear-gradient(135deg, #138496 0%, #0f6674 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3);
        color: white;
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
        padding: 0;
    }
    
    /* Table Styling */
    .table {
        font-size: 1.6rem;
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
    
    /* SUDEASEG Badge */
    .sudeaseg-badge {
        background: linear-gradient(135deg, #5c2e91 0%, #7b4b9e 100%);
        color: #ffffff !important;
        font-size: 0.85rem !important;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 6px;
        display: inline-block;
        font-family: 'Consolas', 'Courier New', monospace;
        letter-spacing: 0.5px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }
    
    /* Action Buttons - Compact */
    .btn-action {
        transition: all 0.2s ease;
        border-radius: 4px;
        padding: 4px 10px;
        font-size: 0.7rem;
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
    
    .empty-value {
        color: #a19f9d;
        font-style: italic;
        font-size: 0.8rem;
    }
    
    /* Responsive */
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
        
        .right-buttons {
            flex-direction: column;
            width: 100%;
        }
        
        .btn-base {
            justify-content: center;
            width: 100%;
        }
    }
");
?>

<div class="main-container">
    <div class="header-section">
        <div class="title-row">
            <h1>
                <i class="fas fa-users"></i> <?= Html::encode($this->title) . ": " . Html::encode($agente->nom) ?>
            </h1>
        </div>

        <div class="buttons-row">
            <div class="left-buttons">
                <a href="<?= Url::to(['agente/index']) ?>" class="btn-base btn-info-custom">
                    <i class="fas fa-building"></i> AGENCIAS
                </a>
                <?php if ($permisos) { ?>
                    <a href="<?= Url::to(['agente/update', 'id' => $agente->id]) ?>" class="btn-base btn-gray">
                        <i class="fas fa-undo"></i> VOLVER
                    </a>
                <?php } ?>
            </div>
            <div class="right-buttons">
                <?php if ($permisos) { ?>
                    <a href="<?= Url::to(['agente-fuerza/create', 'agente_id' => $agente->id]) ?>" class="btn-base btn-primary-custom">
                        <i class="fas fa-plus"></i> ASIGNAR INTERMEDIARIO
                    </a>
                    <a href="#" class="btn-base btn-blue disabled">
                        <i class="fas fa-upload"></i> CARGA MASIVA
                    </a>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="ms-panel">
        <div class="ms-panel-body">
            <div class="scrollable-table-container">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'layout' => "{items}<div class='pagination-wrapper p-3'>{pager}</div>",
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
                    'pager' => [
                        'options' => ['class' => 'pagination justify-content-center'],
                        'linkOptions' => ['class' => 'page-link'],
                        'disabledPageCssClass' => 'disabled',
                        'activePageCssClass' => 'active',
                        'prevPageCssClass' => 'page-item',
                        'nextPageCssClass' => 'page-item',
                        'prevPageLabel' => '<i class="fas fa-chevron-left"></i>',
                        'nextPageLabel' => '<i class="fas fa-chevron-right"></i>',
                        'maxButtonCount' => 5,
                    ],
                    'columns' => [
                        [
                            'attribute' => 'id',
                            'label' => 'N° INTERMEDIARIO',
                            'contentOptions' => ['class' => 'text-center align-middle', 'style' => 'font-weight: 600;'],
                            'headerOptions' => ['style' => 'width: 130px;'],
                            'value' => function ($model) {
                                return $model->id;
                            },
                            'filterInputOptions' => [
                                'placeholder' => 'Buscar N°',
                                'class' => 'form-control form-control-sm text-center',
                            ],
                        ],
                        // NEW COLUMN: Código SUDEASEG (from AgenteFuerza table)
                        [
                            'attribute' => 'registro_corredor_actividad_aseguradora',
                            'label' => 'CÓDIGO SUDEASEG',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 140px;'],
                            'contentOptions' => ['class' => 'text-center align-middle'],
                            'value' => function ($model) {
                                if (!empty($model->registro_corredor_actividad_aseguradora)) {
                                    return Html::tag('span', Html::encode($model->registro_corredor_actividad_aseguradora), [
                                        'class' => 'sudeaseg-badge',
                                    ]);
                                }
                                return Html::tag('span', '—', ['class' => 'empty-value']);
                            },
                            'filterInputOptions' => [
                                'placeholder' => 'Buscar código',
                                'class' => 'form-control form-control-sm text-center',
                            ],
                        ],
                        [
                            'attribute' => 'agenteFuerzaUserNombres',
                            'label' => 'NOMBRE',
                            'contentOptions' => ['class' => 'align-middle'],
                            'headerOptions' => ['style' => 'min-width: 200px;'],
                            'value' => function ($model) {
                                if ($model->userDatos) {
                                    return Html::encode($model->userDatos->nombres . ' ' . $model->userDatos->apellidos);
                                }
                                return '<span class="empty-value">N/A</span>';
                            },
                            'format' => 'raw',
                            'filterInputOptions' => [
                                'placeholder' => 'Buscar nombre',
                                'class' => 'form-control form-control-sm',
                            ],
                        ],
                        [
                            'label' => 'CÉDULA',
                            'contentOptions' => ['class' => 'text-center align-middle'],
                            'headerOptions' => ['style' => 'width: 130px;'],
                            'value' => function ($model) {
                                if ($model->userDatos && $model->userDatos->cedula) {
                                    return Html::encode($model->userDatos->cedula);
                                }
                                return '<span class="empty-value">No disponible</span>';
                            },
                            'attribute' => 'agenteFuerzaUserCedula',
                            'format' => 'raw',
                            'filterInputOptions' => [
                                'placeholder' => 'Buscar cédula',
                                'class' => 'form-control form-control-sm text-center',
                            ],
                        ],
                        [
                            'label' => 'CORREO',
                            'contentOptions' => ['class' => 'align-middle'],
                            'headerOptions' => ['style' => 'min-width: 200px;'],
                            'value' => function ($model) {
                                if ($model->userDatos && $model->userDatos->email) {
                                    return Html::mailto(Html::encode($model->userDatos->email), $model->userDatos->email, [
                                        'class' => 'text-primary text-decoration-none',
                                        'style' => 'font-weight: 500;'
                                    ]);
                                }
                                return '<span class="empty-value">No disponible</span>';
                            },
                            'attribute' => 'agenteFuerzaUserEmail',
                            'format' => 'raw',
                            'filterInputOptions' => [
                                'placeholder' => 'Buscar correo',
                                'class' => 'form-control form-control-sm',
                            ],
                        ],
                        [
                            'label' => 'TELÉFONO',
                            'contentOptions' => ['class' => 'text-center align-middle'],
                            'headerOptions' => ['style' => 'width: 140px;'],
                            'value' => function ($model) {
                                if ($model->userDatos && $model->userDatos->telefono) {
                                    return Html::encode($model->userDatos->telefono);
                                }
                                return '<span class="empty-value">No disponible</span>';
                            },
                            'attribute' => 'agenteFuerzaUserTelefono',
                            'format' => 'raw',
                            'filterInputOptions' => [
                                'placeholder' => 'Buscar teléfono',
                                'class' => 'form-control form-control-sm text-center',
                            ],
                        ],

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
                                            'class' => 'btn-action btn-view',
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

<?php
// Register tooltip initialization
$this->registerJs("
    $(function () {
        $('[data-toggle=\"tooltip\"]').tooltip();
    });
");
?>