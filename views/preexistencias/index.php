<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap4\Modal;
use app\models\UserDatos;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PreexistenciasSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $user_id int|null */
/* @var $afiliado app\models\UserDatos|null */
/* @var $summary array */

// ============================================================
// GUARDS: Ensure variables are defined
// ============================================================
$user_id = $user_id ?? null;
$afiliado = $afiliado ?? null;
$searchModel = $searchModel ?? new \app\models\PreexistenciasSearch();
$dataProvider = $dataProvider ?? new \yii\data\ActiveDataProvider();
$summary = $summary ?? ['total' => 0, 'activos' => 0, 'tratamiento' => 0, 'remision' => 0];

// Determine title
if ($afiliado && is_object($afiliado)) {
    $this->title = 'Pre-existencias de ' . Html::encode($afiliado->nombres . ' ' . $afiliado->apellidos);
} else {
    $this->title = 'Pre-existencias Médicas';
}

$this->params['breadcrumbs'][] = ['label' => 'Afiliados', 'url' => ['/user-datos/index']];

if ($afiliado && is_object($afiliado)) {
    $this->params['breadcrumbs'][] = [
        'label' => Html::encode($afiliado->nombres . ' ' . $afiliado->apellidos),
        'url' => ['/user-datos/view', 'id' => $afiliado->id]
    ];
}
$this->params['breadcrumbs'][] = 'Pre-existencias';

// Register CSS for the page
$this->registerCss("
/* ============================================ */
/* PRE-EXISTENCIAS INDEX PAGE - IMPROVED */
/* ============================================ */

.preexistencias-container {
    max-width: 100%;
}

/* ===== HEADER ===== */
.preexistencias-header {
    background: linear-gradient(135deg, #6c2b8a 0%, #8e44ad 100%);
    padding: 24px 30px;
    border-radius: 12px 12px 0 0;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.preexistencias-header h1 {
    color: white;
    margin: 0;
    font-size: 1.6rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.preexistencias-header h1 .badge {
    background: rgba(255, 255, 255, 0.2) !important;
    color: white !important;
    font-size: 0.85rem;
    padding: 4px 12px;
    border-radius: 20px;
}

.preexistencias-header .header-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}

/* ===== BUTTONS ===== */
.btn-back {
    background: rgba(255, 255, 255, 0.15) !important;
    color: white !important;
    border: 1px solid rgba(255, 255, 255, 0.25) !important;
    padding: 10px 20px !important;
    border-radius: 8px !important;
    font-weight: 600 !important;
    transition: all 0.2s ease !important;
}

.btn-back:hover {
    background: rgba(255, 255, 255, 0.3) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-purple {
    background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%) !important;
    color: white !important;
    border: none !important;
    padding: 10px 24px !important;
    border-radius: 8px !important;
    font-weight: 600 !important;
    transition: all 0.2s ease !important;
    box-shadow: 0 2px 8px rgba(108, 43, 138, 0.3);
}

.btn-purple:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(108, 43, 138, 0.4);
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%) !important;
}

.btn-purple i {
    color: white !important;
}

/* ===== STATS CARDS ===== */
.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin: 20px 0 24px 0;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 18px 22px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border-left: 4px solid #6c2b8a;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 16px;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

.stat-card .stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

.stat-card .stat-icon.purple {
    background: #f4f0f8;
    color: #6c2b8a;
}
.stat-card .stat-icon.green {
    background: #e8f5e9;
    color: #2e7d32;
}
.stat-card .stat-icon.blue {
    background: #e3f2fd;
    color: #1565c0;
}
.stat-card .stat-icon.orange {
    background: #fff3e0;
    color: #e65100;
}

.stat-card .stat-number {
    font-size: 26px;
    font-weight: 700;
    color: #1a1a2e;
    line-height: 1.2;
}

.stat-card .stat-label {
    font-size: 13px;
    color: #6c757d;
    font-weight: 500;
}

/* ===== GRID WRAPPER ===== */
.preexistencias-grid-wrapper {
    background: white;
    border-radius: 0 0 12px 12px;
    padding: 20px 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

/* ===== TABLE STYLES ===== */
.preexistencias-grid-wrapper .grid-view {
    font-size: 14px;
}

.preexistencias-grid-wrapper table {
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
}

.preexistencias-grid-wrapper thead th {
    background: #f8f9fa;
    color: #1a1a2e;
    font-weight: 600;
    border-bottom: 2px solid #e9ecef;
    padding: 12px 16px;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.preexistencias-grid-wrapper tbody td {
    padding: 10px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f0f0f0;
}

.preexistencias-grid-wrapper tbody tr:hover {
    background: #faf5ff;
}

.preexistencias-grid-wrapper tbody tr:last-child td {
    border-bottom: none;
}

/* ===== BADGES ===== */
.preexistencias-grid-wrapper .badge {
    padding: 5px 14px;
    border-radius: 20px;
    font-weight: 500;
    font-size: 11px;
    text-transform: capitalize;
}

.badge-success {
    background: #e8f5e9;
    color: #2e7d32;
}

.badge-secondary {
    background: #f5f5f5;
    color: #616161;
}

.badge-warning {
    background: #fff3e0;
    color: #e65100;
}

.badge-info {
    background: #e3f2fd;
    color: #1565c0;
}

/* ===== ACTION BUTTONS - ULTRA COMPACT ===== */
.action-buttons {
    display: flex !important;
    gap: 3px !important;
    justify-content: center !important;
    align-items: center !important;
    flex-wrap: nowrap !important;
}

.action-buttons .btn-action {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 28px !important;
    height: 28px !important;
    min-width: 28px !important;
    min-height: 28px !important;
    padding: 0 !important;
    margin: 0 !important;
    border-radius: 6px !important;
    border: 1px solid transparent !important;
    font-size: 12px !important;
    line-height: 1 !important;
    text-decoration: none !important;
    transition: all 0.15s ease !important;
    cursor: pointer !important;
}

.action-buttons .btn-action i {
    font-size: 12px !important;
    margin: 0 !important;
}

.action-buttons .btn-action:hover {
    transform: scale(1.1) !important;
    text-decoration: none !important;
}

/* View button */
.action-buttons .btn-action.btn-view {
    background: rgba(23, 162, 184, 0.1) !important;
    color: #17a2b8 !important;
    border-color: rgba(23, 162, 184, 0.3) !important;
}

.action-buttons .btn-action.btn-view:hover {
    background: #17a2b8 !important;
    color: #ffffff !important;
    border-color: #17a2b8 !important;
}

/* Edit button */
.action-buttons .btn-action.btn-edit {
    background: rgba(0, 123, 255, 0.1) !important;
    color: #007bff !important;
    border-color: rgba(0, 123, 255, 0.3) !important;
}

.action-buttons .btn-action.btn-edit:hover {
    background: #007bff !important;
    color: #ffffff !important;
    border-color: #007bff !important;
}

/* Delete button */
.action-buttons .btn-action.btn-delete {
    background: rgba(220, 53, 69, 0.1) !important;
    color: #dc3545 !important;
    border-color: rgba(220, 53, 69, 0.3) !important;
}

.action-buttons .btn-action.btn-delete:hover {
    background: #dc3545 !important;
    color: #ffffff !important;
    border-color: #dc3545 !important;
}

/* Toggle status button */
.action-buttons .btn-action.btn-toggle {
    background: rgba(255, 193, 7, 0.1) !important;
    color: #ffc107 !important;
    border-color: rgba(255, 193, 7, 0.3) !important;
}

.action-buttons .btn-action.btn-toggle:hover {
    background: #ffc107 !important;
    color: #212529 !important;
    border-color: #ffc107 !important;
}

/* ===== FILTER FORM ===== */
.preexistencias-filters {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 16px 20px;
    margin-bottom: 20px;
    border: 1px solid #e9ecef;
}

.preexistencias-filters .form-group {
    margin-bottom: 0;
}

.preexistencias-filters .form-control {
    border-radius: 6px;
    border: 1px solid #dee2e6;
    padding: 8px 12px;
    font-size: 14px;
}

.preexistencias-filters .form-control:focus {
    border-color: #6c2b8a;
    box-shadow: 0 0 0 3px rgba(108, 43, 138, 0.1);
}

/* ===== PAGINATION ===== */
.pagination {
    margin-top: 16px;
}

.pagination .page-link {
    border-radius: 6px;
    margin: 0 2px;
    color: #6c2b8a;
    border: 1px solid #dee2e6;
}

.pagination .page-link:hover {
    background: #f4f0f8;
    border-color: #6c2b8a;
}

.pagination .active .page-link {
    background: #6c2b8a;
    border-color: #6c2b8a;
    color: white;
}

/* ===== SUMMARY ===== */
.summary {
    font-size: 13px !important;
    color: #6c757d !important;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 992px) {
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .preexistencias-header {
        flex-direction: column;
        align-items: flex-start;
        padding: 20px;
    }
    
    .preexistencias-header h1 {
        font-size: 1.2rem;
    }
    
    .preexistencias-header .header-actions {
        width: 100%;
    }
    
    .preexistencias-header .header-actions .btn {
        flex: 1;
        justify-content: center;
        font-size: 13px;
        padding: 8px 14px;
    }
    
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    
    .stat-card {
        padding: 14px 16px;
    }
    
    .stat-card .stat-number {
        font-size: 20px;
    }
    
    .preexistencias-grid-wrapper {
        padding: 12px;
        overflow-x: auto;
    }
    
    .action-buttons .btn-action {
        width: 24px !important;
        height: 24px !important;
        min-width: 24px !important;
        min-height: 24px !important;
        font-size: 10px !important;
    }
    
    .action-buttons .btn-action i {
        font-size: 10px !important;
    }
}

@media (max-width: 480px) {
    .stats-row {
        grid-template-columns: 1fr;
    }
}

/* ===== EMPTY STATE ===== */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state .empty-icon {
    font-size: 64px;
    color: #d4c5e0;
    margin-bottom: 16px;
}

.empty-state h4 {
    color: #1a1a2e;
    margin-bottom: 8px;
}

.empty-state p {
    color: #6c757d;
    max-width: 400px;
    margin: 0 auto 20px;
}
");
?>
<div class="preexistencias-container">

    <!-- Header -->
    <div class="preexistencias-header">
        <h1>
            <i class="fas fa-heartbeat"></i>
            <?= Html::encode($this->title) ?>
            <?php if ($afiliado && is_object($afiliado)): ?>
                <i class="fas fa-user-circle" style="opacity: 0.8;"></i>
                <?= Html::encode($afiliado->tipo_cedula . '-' . $afiliado->cedula) ?>
            <?php endif; ?>
            <span class="badge">
                <i class="fas fa-list"></i> <?= $dataProvider->getTotalCount() ?> registros
            </span>
        </h1>
        <div class="header-actions">
            <?php if ($afiliado && is_object($afiliado)): ?>
                <?= Html::a(
                    '<i class="fas fa-arrow-left"></i> Volver al Perfil',
                    ['/user-datos/view', 'id' => $afiliado->id],
                    ['class' => 'btn btn-back']
                ) ?>
            <?php else: ?>
                <?= Html::a(
                    '<i class="fas fa-arrow-left"></i> Volver',
                    ['/user-datos/index'],
                    ['class' => 'btn btn-back']
                ) ?>
            <?php endif; ?>
            <?php if ($user_id): ?>
                <?= Html::a(
                    '<i class="fas fa-plus-circle"></i> Nueva Pre-existencia',
                    ['create', 'user_id' => $user_id],
                    ['class' => 'btn btn-purple']
                ) ?>
            <?php else: ?>
                <?= Html::a(
                    '<i class="fas fa-plus-circle"></i> Nueva Pre-existencia',
                    ['create'],
                    ['class' => 'btn btn-purple']
                ) ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon purple">
                <i class="fas fa-notes-medical"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?= $summary['total'] ?? 0 ?></div>
                <div class="stat-label">Total Pre-existencias</div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color: #2e7d32;">
            <div class="stat-icon green">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?= $summary['activos'] ?? 0 ?></div>
                <div class="stat-label">Activas</div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color: #1565c0;">
            <div class="stat-icon blue">
                <i class="fas fa-heartbeat"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?= $summary['tratamiento'] ?? 0 ?></div>
                <div class="stat-label">En Tratamiento</div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color: #e65100;">
            <div class="stat-icon orange">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?= $summary['remision'] ?? 0 ?></div>
                <div class="stat-label">En Remisión</div>
            </div>
        </div>
    </div>

    <!-- Grid -->
    <div class="preexistencias-grid-wrapper">
        <?php Pjax::begin([
            'id' => 'preexistencias-pjax',
            'enablePushState' => true,
            'timeout' => 5000,
        ]); ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => "{summary}\n{items}\n{pager}",
            'summaryOptions' => ['class' => 'summary mb-3 text-muted'],
            'tableOptions' => ['class' => 'table table-hover'],
            'columns' => [
                [
                    'attribute' => 'id',
                    'label' => '#',
                    'contentOptions' => ['class' => 'text-center', 'style' => 'width: 50px;'],
                    'headerOptions' => ['class' => 'text-center'],
                ],
                [
                    'attribute' => 'nombre',
                    'label' => 'Pre-existencia',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return Html::a(
                            '<i class="fas fa-notes-medical" style="color: #6c2b8a; margin-right: 6px;"></i>' . Html::encode($model->nombre),
                            ['view', 'id' => $model->id],
                            [
                                'data-pjax' => '0',
                                'style' => 'font-weight: 600; color: #1a1a2e; text-decoration: none;',
                            ]
                        );
                    },
                ],
                [
                    'attribute' => 'afiliado_nombre',
                    'label' => 'Afiliado',
                    'format' => 'raw',
                    'value' => function ($model) {
                        if ($model->afiliado) {
                            return Html::a(
                                '<i class="fas fa-user-circle text-muted" style="margin-right: 4px;"></i> ' . Html::encode($model->afiliado->nombres . ' ' . $model->afiliado->apellidos . ' (' . $model->afiliado->tipo_cedula . '-' . $model->afiliado->cedula . ')'),
                                ['/user-datos/view', 'id' => $model->afiliado->id],
                                [
                                    'target' => '_blank',
                                    'data-pjax' => '0',
                                    'style' => 'color: #1a1a2e; text-decoration: none;',
                                    'title' => 'Ver perfil del afiliado',
                                ]
                            );
                        }
                        return '<span class="text-muted">N/A</span>';
                    },
                    'visible' => !$user_id,
                ],
                [
                    'attribute' => 'fecha_diagnostico',
                    'label' => 'Fecha Diagnóstico',
                    'format' => 'raw',
                    'contentOptions' => ['class' => 'text-center'],
                    'headerOptions' => ['class' => 'text-center'],
                    'filter' => false,
                    'value' => function ($model) {
                        if ($model->fecha_diagnostico) {
                            return '<span style="font-weight: 500;">' . Yii::$app->formatter->asDate($model->fecha_diagnostico, 'dd/MM/yyyy') . '</span>';
                        }
                        return '<span class="text-muted">—</span>';
                    },
                ],
                [
                    'attribute' => 'medico_tratante',
                    'label' => 'Médico',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->medico_tratante
                            ? '<i class="fas fa-user-md text-muted" style="margin-right: 4px;"></i> ' . Html::encode($model->medico_tratante)
                            : '<span class="text-muted">—</span>';
                    },
                ],
                [
                    'attribute' => 'estatus',
                    'label' => 'Estatus',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->getStatusBadge();
                    },
                    'filter' => \app\models\Preexistencias::getStatusOptions(),
                    'contentOptions' => ['class' => 'text-center'],
                    'headerOptions' => ['class' => 'text-center'],
                ],
                [
                    'attribute' => 'created_at',
                    'label' => 'Registro',
                    'format' => ['date', 'php:d/m/Y'],
                    'contentOptions' => ['class' => 'text-center', 'style' => 'font-size: 12px; color: #6c757d;'],
                    'headerOptions' => ['class' => 'text-center'],
                    'filter' => false,
                ],
                [

                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view} {toggle} {edit} {delete}',
                    'contentOptions' => ['class' => 'text-center', 'style' => 'width: 1%; white-space: nowrap; padding: 4px 8px;'],
                    'headerOptions' => ['class' => 'text-center', 'style' => 'width: 1%; white-space: nowrap;'],
                    'header' => 'ACCIONES',  // Use 'header' instead of 'label'
                    'buttons' => [
                        'view' => function ($url, $model) {
                            return Html::a(
                                '<i class="fas fa-eye"></i>',
                                ['view', 'id' => $model->id],
                                [
                                    'class' => 'btn-action btn-view',
                                    'title' => 'Ver detalles',
                                    'data-pjax' => '0',
                                ]
                            );
                        },
                        'edit' => function ($url, $model) {
                            return Html::a(
                                '<i class="fas fa-edit"></i>',
                                ['update', 'id' => $model->id],
                                [
                                    'class' => 'btn-action btn-edit',
                                    'title' => 'Editar',
                                    'data-pjax' => '0',
                                ]
                            );
                        },
                        'toggle' => function ($url, $model) {
                            $isActive = $model->estatus === \app\models\Preexistencias::ESTATUS_ACTIVO;
                            $icon = $isActive ? 'fa-pause' : 'fa-play';
                            $title = $isActive ? 'Desactivar' : 'Activar';
                            return Html::a(
                                '<i class="fas ' . $icon . '"></i>',
                                ['toggle-status', 'id' => $model->id],
                                [
                                    'class' => 'btn-action btn-toggle',
                                    'title' => $title,
                                    'data-method' => 'post',
                                    'data-pjax' => '0',
                                    'data-confirm' => '¿Está seguro de cambiar el estatus de esta pre-existencia?',
                                ]
                            );
                        },
                        'delete' => function ($url, $model) {
                            return Html::a(
                                '<i class="fas fa-trash"></i>',
                                ['delete', 'id' => $model->id],
                                [
                                    'class' => 'btn-action btn-delete',
                                    'title' => 'Eliminar',
                                    'data-method' => 'post',
                                    'data-pjax' => '0',
                                    'data-confirm' => '¿Está seguro de eliminar esta pre-existencia? Esta acción no se puede deshacer.',
                                ]
                            );
                        },
                    ],
                ],
            ],
        ]); ?>

        <?php Pjax::end(); ?>
    </div>

</div>

<?php
// Additional JS for tooltips and interactions
$this->registerJs("
    // Enable tooltips
    jQuery('[data-toggle=\"tooltip\"]').tooltip();
    
    // Handle Pjax events
    jQuery(document).on('pjax:success', function() {
        jQuery('[data-toggle=\"tooltip\"]').tooltip();
    });
");
?>