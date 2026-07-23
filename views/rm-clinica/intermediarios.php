<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\AgenteFuerza;

/* @var $this yii\web\View */
/* @var $clinic app\models\RmClinica */
/* @var $searchModel app\models\IntermediariosByClinicaSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $summary array */

// Check if variables are set, provide defaults if not
if (!isset($clinic)) {
    $clinic = null;
}
if (!isset($searchModel)) {
    $searchModel = new \app\models\IntermediariosByClinicaSearch();
}
if (!isset($dataProvider)) {
    $dataProvider = new \yii\data\ActiveDataProvider([
        'query' => \app\models\AgenteFuerza::find()->where('1=0'),
    ]);
}
if (!isset($summary)) {
    $summary = [
        'total_intermediarios' => 0,
        'total_agencias' => 0,
        'can_sell' => 0,
        'can_register' => 0,
        'can_asesorar' => 0,
        'can_cobrar' => 0,
        'avg_por_venta' => 0,
        'avg_por_asesor' => 0,
        'avg_por_cobranza' => 0,
        'avg_por_post_venta' => 0,
        'avg_por_registrar' => 0,
    ];
}

// Get clinic ID for the affiliate count filter
$clinicId = $clinic ? $clinic->id : null;

$this->title = 'Intermediarios' . ($clinic ? ' - ' . Html::encode($clinic->nombre) : '');
$this->params['breadcrumbs'][] = ['label' => 'Clínicas', 'url' => ['index']];
if ($clinic) {
    $this->params['breadcrumbs'][] = ['label' => Html::encode($clinic->nombre), 'url' => ['view', 'id' => $clinic->id]];
}
$this->params['breadcrumbs'][] = 'Intermediarios';

// Helper function to render permission badge
function renderPermissionBadge($value, $label)
{
    if ($value == 1) {
        return '<span class="badge badge-pill badge-success" title="' . $label . '" style="width: 36px; height: 36px; line-height: 36px; padding: 0; border-radius: 50%; font-size: 1.1rem;"><i class="fas fa-check" style="font-size: 14px;"></i></span>';
    }
    return '<span class="badge badge-pill badge-light border" title="' . $label . '" style="width: 36px; height: 36px; line-height: 36px; padding: 0; border-radius: 50%; color: #adb5bd; font-size: 1.1rem;"><i class="fas fa-times" style="font-size: 14px;"></i></span>';
}
?>

<div class="rm-clinica-intermediarios">
    <!-- Page Header with Action Buttons -->
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <div>
            <h4 class="text-primary mb-0" style="font-weight: 600; font-size: 2rem;">
                <i class="fas fa-handshake mr-2"></i> Intermediarios
            </h4>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
            <?php if ($clinic): ?>
                <?= Html::a(
                    '<i class="fas fa-arrow-left mr-1"></i> Volver',
                    ['view', 'id' => $clinic->id],
                    [
                        'class' => 'btn btn-outline-secondary btn-sm',
                        'style' => 'border-radius: 4px; font-weight: 500; font-size: 1.1rem; padding: 0.6rem 1.2rem;',
                    ]
                ) ?>
            <?php endif; ?>
            <?= Html::a(
                '<i class="fas fa-file-excel mr-1"></i> Excel',
                ['exportar-intermediarios-excel', 'id' => $clinicId, '?' => Yii::$app->request->getQueryString()],
                [
                    'class' => 'btn btn-success btn-sm',
                    'style' => 'border-radius: 4px; font-weight: 500; font-size: 1.1rem; padding: 0.6rem 1.2rem;',
                    'target' => '_blank',
                ]
            ) ?>
            <?= Html::a(
                '<i class="fas fa-file-pdf mr-1"></i> PDF',
                ['exportar-intermediarios-pdf', 'id' => $clinicId, '?' => Yii::$app->request->getQueryString()],
                [
                    'class' => 'btn btn-danger btn-sm',
                    'style' => 'border-radius: 4px; font-weight: 500; font-size: 1.1rem; padding: 0.6rem 1.2rem;',
                    'target' => '_blank',
                ]
            ) ?>
        </div>
    </div>

    <!-- 1. TOTAL NUMBER OF INTERMEDIARIOS - Prominent Display -->
    <div class="mb-4 pb-3" style="border-bottom: 1px solid #e9ecef;">
        <div class="d-flex align-items-center">
            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 72px; height: 72px; flex-shrink: 0;">
                <i class="fas fa-users text-white" style="font-size: 32px;"></i>
            </div>
            <div>
                <span class="text-muted small text-uppercase d-block" style="font-weight: 600; letter-spacing: 0.5px; font-size: 0.9rem;">Total Intermediarios</span>
                <span class="font-weight-bold" style="font-size: 3.2rem; line-height: 1.2; color: #2c3e50;"><?= number_format($summary['total_intermediarios']) ?></span>
                <span class="text-muted ml-2" style="font-size: 1.3rem;">
                    <i class="fas fa-building mr-1"></i> <?= number_format($summary['total_agencias']) ?> agencias asociadas
                </span>
            </div>
        </div>
    </div>

    <!-- 2. CLINIC NAME - Clear and Prominent -->
    <?php if ($clinic): ?>
        <div class="mb-4 p-4" style="background: linear-gradient(135deg, #f0f7ff 0%, #e8f4fd 100%); border-radius: 10px; border-left: 5px solid #007bff; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
            <div class="d-flex align-items-start flex-wrap">
                <div class="mr-4 mb-2 mb-md-0">
                    <span class="badge badge-primary px-3 py-2" style="font-size: 0.9rem; border-radius: 4px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="fas fa-hospital mr-1"></i> Clínica
                    </span>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center flex-wrap">
                        <span class="font-weight-bold" style="font-size: 2rem; color: #1a3a5c; line-height: 1.2;">
                            <?= Html::encode($clinic->nombre) ?>
                        </span>
                        <?php if ($clinic->codigo_clinica): ?>
                            <span class="badge border ml-3 px-3 py-2" style="font-size: 1rem; border-radius: 4px; font-weight: 500; background-color: #ffffff; color: #212529; border-color: #ced4da;">
                                <i class="fas fa-code mr-1 text-muted"></i> <?= Html::encode($clinic->codigo_clinica) ?>
                            </span>
                        <?php endif; ?>
                        <span class="badge ml-3 px-3 py-2" style="font-size: 1rem; border-radius: 4px; font-weight: 500; background-color: #ffffff; color: #212529; border: 1px solid #ced4da;">
                            <i class="fas fa-users mr-1 text-primary"></i> <?= number_format($summary['total_intermediarios']) ?> intermediarios
                        </span>
                    </div>
                    <?php if ($clinic->rif || $clinic->telefono || $clinic->correo): ?>
                        <div class="text-muted mt-2" style="font-size: 1.1rem;">
                            <?php if ($clinic->rif): ?>
                                <span class="mr-4"><i class="fas fa-id-card mr-1" style="width: 16px; color: #6c757d;"></i> <strong>RIF:</strong> <?= Html::encode($clinic->rif) ?></span>
                            <?php endif; ?>
                            <?php if ($clinic->telefono): ?>
                                <span class="mr-4"><i class="fas fa-phone mr-1" style="width: 16px; color: #6c757d;"></i> <strong>Teléfono:</strong> <?= Html::encode($clinic->telefono) ?></span>
                            <?php endif; ?>
                            <?php if ($clinic->correo): ?>
                                <span><i class="fas fa-envelope mr-1" style="width: 16px; color: #6c757d;"></i> <strong>Email:</strong> <?= Html::encode($clinic->correo) ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="mb-4 p-4" style="background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f5 100%); border-radius: 10px; border-left: 5px solid #6c757d; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
            <div class="d-flex align-items-center">
                <i class="fas fa-globe text-secondary mr-3" style="font-size: 2.5rem;"></i>
                <div>
                    <span class="font-weight-bold" style="font-size: 2rem; color: #2c3e50;">Todos los Intermediarios</span>
                    <span class="text-muted ml-3" style="font-size: 1.2rem;">- Listado completo del sistema</span>
                    <div class="text-muted mt-1" style="font-size: 1.1rem;">
                        <i class="fas fa-users mr-1"></i> <?= number_format($summary['total_intermediarios']) ?> intermediarios registrados
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Stats Summary - Compact -->
    <div class="d-flex flex-wrap align-items-center mb-3 pb-2" style="border-bottom: 1px solid #e9ecef;">
        <div class="mr-4 pr-3" style="border-right: 1px solid #e9ecef;">
            <span class="text-muted small text-uppercase d-block" style="font-weight: 600; letter-spacing: 0.3px; font-size: 0.8rem;">Con Venta</span>
            <span class="font-weight-bold" style="font-size: 1.4rem;"><?= number_format($summary['can_sell']) ?></span>
            <small class="text-muted ml-1" style="font-size: 1rem;"><?= $summary['total_intermediarios'] > 0 ? round(($summary['can_sell'] / $summary['total_intermediarios']) * 100) : 0 ?>%</small>
        </div>
        <div class="mr-4 pr-3" style="border-right: 1px solid #e9ecef;">
            <span class="text-muted small text-uppercase d-block" style="font-weight: 600; letter-spacing: 0.3px; font-size: 0.8rem;">Con Registro</span>
            <span class="font-weight-bold" style="font-size: 1.4rem;"><?= number_format($summary['can_register']) ?></span>
            <small class="text-muted ml-1" style="font-size: 1rem;"><?= $summary['total_intermediarios'] > 0 ? round(($summary['can_register'] / $summary['total_intermediarios']) * 100) : 0 ?>%</small>
        </div>
        <div class="mr-4 pr-3" style="border-right: 1px solid #e9ecef;">
            <span class="text-muted small text-uppercase d-block" style="font-weight: 600; letter-spacing: 0.3px; font-size: 0.8rem;">Comisión Prom.</span>
            <span class="font-weight-bold" style="font-size: 1.4rem;"><?= number_format($summary['avg_por_venta'], 1) ?>%</span>
            <small class="text-muted ml-1" style="font-size: 1rem;">Venta</small>
        </div>
        <div>
            <span class="text-muted small text-uppercase d-block" style="font-weight: 600; letter-spacing: 0.3px; font-size: 0.8rem;">Agencias</span>
            <span class="font-weight-bold" style="font-size: 1.4rem;"><?= number_format($summary['total_agencias']) ?></span>
            <small class="text-muted ml-1" style="font-size: 1rem;">Asociadas</small>
        </div>
    </div>

    <!-- 3. GRID - Results Table -->
    <div class="card border-0 shadow-sm" style="border-radius: 8px;">
        <div class="card-body p-0">
            <?php Pjax::begin(['id' => 'intermediarios-pjax', 'timeout' => 5000, 'enablePushState' => false]); ?>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => null,
                'summary' => '
                    <div class="d-flex justify-content-between align-items-center flex-wrap px-4 py-3" style="border-bottom: 1px solid #e9ecef;">
                        <div class="text-muted small" style="font-size: 1rem;">
                            <i class="fas fa-list mr-1"></i> 
                            Mostrando <strong>{begin}</strong> - <strong>{end}</strong> de <strong>{totalCount}</strong> intermediarios
                        </div>
                        <div>
                            <span class="badge border px-3 py-2" style="border-radius: 4px; font-weight: 500; background-color: #ffffff; color: #212529; border-color: #ced4da; font-size: 1.1rem; padding: 0.5rem 1.2rem;">
                                <i class="fas fa-users mr-1 text-primary"></i> Total: ' . number_format($summary['total_intermediarios']) . '
                            </span>
                        </div>
                    </div>
                ',
                'layout' => '{summary}<div class="table-responsive">{items}</div><div class="px-4 py-3" style="border-top: 1px solid #e9ecef;">{pager}</div>',
                'tableOptions' => [
                    'class' => 'table table-hover mb-0',
                    'style' => 'font-size: 1.3rem; background: white;'
                ],
                'rowOptions' => function ($model, $key, $index, $grid) {
                    return ['class' => $index % 2 == 0 ? 'bg-white' : 'bg-light'];
                },
                'columns' => [
                    [
                        'attribute' => 'id',
                        'label' => '#',
                        'headerOptions' => ['style' => 'width: 50px; font-weight: 600; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 0.3px; padding: 1rem 1.2rem; text-align: center;'],
                        'contentOptions' => ['class' => 'text-center font-weight-bold text-muted', 'style' => 'font-size: 1.3rem; padding: 1rem 1.2rem;'],
                    ],
                    [
                        'attribute' => 'nombre_completo',
                        'label' => 'Intermediario',
                        'headerOptions' => ['style' => 'font-weight: 600; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 0.3px; padding: 1rem 1.2rem;'],
                        'value' => function ($model) {
                            $userDatos = $model->userDatos;
                            if ($userDatos) {
                                return '<div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-primary text-white mr-2" style="width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 600; flex-shrink: 0;">
                                        ' . strtoupper(substr($userDatos->nombres, 0, 1) . substr($userDatos->apellidos, 0, 1)) . '
                                    </div>
                                    <div>
                                        <div class="font-weight-bold" style="font-size: 1.3rem;">' . Html::encode($userDatos->nombres . ' ' . $userDatos->apellidos) . '</div>
                                        <small class="text-muted" style="font-size: 1.1rem;">ID: ' . $model->id . '</small>
                                    </div>
                                </div>';
                            }
                            return 'N/A';
                        },
                        'format' => 'raw',
                        'headerOptions' => ['style' => 'min-width: 200px; font-weight: 600; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 0.3px;'],
                        'contentOptions' => ['style' => 'font-size: 1.3rem; padding: 1rem 1.2rem;'],
                    ],
                    [
                        'attribute' => 'cedula',
                        'label' => 'Cédula',
                        'headerOptions' => ['style' => 'font-weight: 600; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 0.3px; padding: 1rem 1.2rem;'],
                        'value' => function ($model) {
                            $userDatos = $model->userDatos;
                            if ($userDatos) {
                                return '<span class="text-nowrap" style="font-size: 1.3rem;">' . Html::encode($userDatos->tipo_cedula . '-' . $userDatos->cedula) . '</span>';
                            }
                            return 'N/A';
                        },
                        'format' => 'raw',
                        'headerOptions' => ['style' => 'min-width: 120px;'],
                        'contentOptions' => ['style' => 'font-size: 1.3rem; padding: 1rem 1.2rem;'],
                    ],
                    [
                        'attribute' => 'email',
                        'label' => 'Contacto',
                        'headerOptions' => ['style' => 'font-weight: 600; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 0.3px; padding: 1rem 1.2rem;'],
                        'value' => function ($model) {
                            $userDatos = $model->userDatos;
                            if ($userDatos) {
                                $html = '';
                                if ($userDatos->email) {
                                    $html .= '<div style="font-size: 1.2rem;"><i class="fas fa-envelope text-muted mr-1" style="width: 16px; font-size: 1.2rem;"></i> ' . Html::mailto(Html::encode($userDatos->email), $userDatos->email) . '</div>';
                                }
                                if ($userDatos->telefono) {
                                    $html .= '<div style="font-size: 1.2rem;"><i class="fas fa-phone text-muted mr-1" style="width: 16px; font-size: 1.2rem;"></i> ' . Html::encode($userDatos->telefono) . '</div>';
                                }
                                return $html;
                            }
                            return 'N/A';
                        },
                        'format' => 'raw',
                        'headerOptions' => ['style' => 'min-width: 180px;'],
                        'contentOptions' => ['style' => 'font-size: 1.3rem; padding: 1rem 1.2rem;'],
                    ],
                    [
                        'attribute' => 'agencia_nombre',
                        'label' => 'Agencia',
                        'headerOptions' => ['style' => 'font-weight: 600; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 0.3px; padding: 1rem 1.2rem;'],
                        'value' => function ($model) {
                            if ($model->agente) {
                                return '<span class="badge border px-3 py-2" style="border-radius: 4px; font-weight: 500; background-color: #ffffff; color: #212529; border-color: #ced4da; font-size: 1.1rem; padding: 0.5rem 1rem;">
                                    <i class="fas fa-building text-primary mr-1"></i> 
                                    ' . Html::encode($model->agente->nom) . '
                                </span>';
                            }
                            return '<span class="text-muted" style="font-size: 1.3rem;">N/A</span>';
                        },
                        'format' => 'raw',
                        'headerOptions' => ['style' => 'min-width: 150px;'],
                        'contentOptions' => ['style' => 'font-size: 1.3rem; padding: 1rem 1.2rem;'],
                    ],
                    [
                        'attribute' => 'codigo_sudeaseg',
                        'label' => 'SUDEASEG',
                        'headerOptions' => ['style' => 'font-weight: 600; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 0.3px; padding: 1rem 1.2rem;'],
                        'value' => function ($model) {
                            $code = $model->registro_corredor_actividad_aseguradora;
                            return $code ? '<span class="badge badge-info" style="border-radius: 4px; font-weight: 500; font-size: 1.1rem; padding: 0.4rem 0.8rem;">' . Html::encode($code) . '</span>' : '<span class="text-muted" style="font-size: 1.3rem;">N/A</span>';
                        },
                        'format' => 'raw',
                        'headerOptions' => ['style' => 'min-width: 100px;'],
                        'contentOptions' => ['style' => 'font-size: 1.3rem; padding: 1rem 1.2rem;'],
                    ],
                    [
                        'label' => 'Afiliados',
                        'headerOptions' => ['style' => 'width: 100px; font-weight: 600; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 0.3px; text-align: center; padding: 1rem 1.2rem;'],
                        'contentOptions' => ['class' => 'text-center', 'style' => 'font-size: 1.3rem; padding: 1rem 1.2rem;'],
                        'value' => function ($model) use ($clinicId) {
                            // Count affiliates ONLY for this clinic
                            $query = \app\models\UserDatos::find()
                                ->where(['asesor_id' => $model->id])
                                ->andWhere(['role' => 'afiliado'])
                                ->andWhere(['is', 'deleted_at', null]);

                            // Apply clinic filter if provided
                            if ($clinicId) {
                                $query->andWhere(['clinica_id' => $clinicId]);
                            }

                            $count = $query->count();

                            if ($count > 0) {
                                return Html::a(
                                    '<span class="badge badge-primary" style="border-radius: 4px; font-weight: 600; font-size: 1.2rem; padding: 0.5rem 1rem; min-width: 45px; transition: all 0.2s ease;">' . number_format($count) . '</span>',
                                    ['/user-datos/index-by-afiliado', 'asesor_id' => $model->id],
                                    [
                                        'target' => '_blank',
                                        'title' => 'Ver afiliados de ' . ($model->userDatos ? $model->userDatos->nombres . ' ' . $model->userDatos->apellidos : 'este intermediario') . ($clinicId ? ' en esta clínica' : ''),
                                        'style' => 'text-decoration: none;',
                                        'data-pjax' => '0',
                                    ]
                                );
                            }
                            return '<span class="badge badge-secondary" style="border-radius: 4px; font-weight: 600; font-size: 1.2rem; padding: 0.5rem 1rem; min-width: 45px;">0</span>';
                        },
                        'format' => 'raw',
                    ],
                    [
                        'label' => 'Comisiones',
                        'headerOptions' => ['style' => 'font-weight: 600; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 0.3px; min-width: 200px; padding: 1rem 1.2rem;'],
                        'value' => function ($model) {
                            return '<div class="d-flex flex-wrap" style="gap: 4px;">
                                <span class="badge badge-primary" style="border-radius: 4px; font-weight: 500; font-size: 1rem; padding: 0.4rem 0.8rem;" title="Venta">V: ' . number_format($model->por_venta, 1) . '%</span>
                                <span class="badge badge-secondary" style="border-radius: 4px; font-weight: 500; font-size: 1rem; padding: 0.4rem 0.8rem;" title="Asesoría">A: ' . number_format($model->por_asesor, 1) . '%</span>
                                <span class="badge badge-warning" style="border-radius: 4px; font-weight: 500; font-size: 1rem; padding: 0.4rem 0.8rem;" title="Cobranza">C: ' . number_format($model->por_cobranza, 1) . '%</span>
                                <span class="badge badge-success" style="border-radius: 4px; font-weight: 500; font-size: 1rem; padding: 0.4rem 0.8rem;" title="Post-Venta">P: ' . number_format($model->por_post_venta, 1) . '%</span>
                                <span class="badge badge-info" style="border-radius: 4px; font-weight: 500; font-size: 1rem; padding: 0.4rem 0.8rem;" title="Registrar">R: ' . number_format($model->por_registrar, 1) . '%</span>
                            </div>';
                        },
                        'format' => 'raw',
                        'contentOptions' => ['style' => 'font-size: 1.3rem; padding: 1rem 1.2rem;'],
                    ],
                    [
                        'attribute' => 'created_at',
                        'label' => 'Creado',
                        'headerOptions' => ['style' => 'width: 100px; font-weight: 600; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 0.3px; padding: 1rem 1.2rem;'],
                        'value' => function ($model) {
                            return '<span class="text-nowrap" title="' . $model->created_at . '" style="font-size: 1.2rem;">' . Yii::$app->formatter->asDate($model->created_at, 'php:d/m/Y') . '</span>';
                        },
                        'format' => 'raw',
                        'contentOptions' => ['class' => 'text-center', 'style' => 'font-size: 1.3rem; padding: 1rem 1.2rem;'],
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view}',
                        'header' => 'Acción',
                        'headerOptions' => ['style' => 'width: 50px; text-align: center; font-weight: 600; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 0.3px; padding: 1rem 1.2rem;'],
                        'contentOptions' => ['class' => 'text-center', 'style' => 'font-size: 1.3rem; padding: 1rem 1.2rem;'],
                        'buttons' => [
                            'view' => function ($url, $model) {
                                return Html::a(
                                    '<i class="fas fa-chevron-right"></i>',
                                    ['/agente-fuerza/view', 'id' => $model->id],
                                    [
                                        'class' => 'btn btn-sm btn-outline-primary',
                                        'title' => 'Ver Intermediario',
                                        'target' => '_blank',
                                        'style' => 'border-radius: 4px; width: 40px; height: 40px; padding: 0; line-height: 40px; font-size: 1.2rem;',
                                    ]
                                );
                            },
                        ],
                    ],
                ],
                'pager' => [
                    'class' => 'yii\bootstrap4\LinkPager',
                    'options' => ['class' => 'pagination justify-content-end flex-wrap mb-0'],
                    'linkOptions' => ['class' => 'page-link', 'style' => 'border-radius: 4px; margin: 0 2px; font-size: 1.1rem; padding: 0.6rem 1rem;'],
                    'linkContainerOptions' => ['class' => 'page-item'],
                    'disabledPageCssClass' => 'disabled',
                    'activePageCssClass' => 'active',
                ],
            ]); ?>

            <?php Pjax::end(); ?>
        </div>
    </div>

    <!-- 4. FILTERS - Search Form (Below the Grid) -->
    <?php if ($clinic): ?>
        <div class="mt-3 pt-2">
            <?= $this->render('_search_intermediarios', ['model' => $searchModel, 'clinic' => $clinic]); ?>
        </div>
    <?php endif; ?>
</div>

<?php
// CSS to improve the layout
$this->registerCss("
    .rm-clinica-intermediarios .gap-2 {
        gap: 0.5rem;
    }
    .rm-clinica-intermediarios .avatar-circle {
        flex-shrink: 0;
    }
    .rm-clinica-intermediarios .table {
        border-collapse: collapse;
    }
    .rm-clinica-intermediarios .table th {
        font-weight: 600;
        color: #495057;
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
    }
    .rm-clinica-intermediarios .table td {
        vertical-align: middle;
        border-top: 1px solid #e9ecef;
    }
    .rm-clinica-intermediarios .table-hover tbody tr:hover {
        background-color: #f0f7ff !important;
        transition: background-color 0.15s ease-in-out;
    }
    .rm-clinica-intermediarios .table-hover tbody tr {
        transition: background-color 0.15s ease-in-out;
    }
    .rm-clinica-intermediarios .badge {
        font-weight: 500;
        border-radius: 4px;
    }
    .rm-clinica-intermediarios .badge-pill {
        border-radius: 50rem;
    }
    .rm-clinica-intermediarios .btn-sm {
        border-radius: 4px;
        font-weight: 500;
    }
    .rm-clinica-intermediarios .pagination .page-link {
        color: #007bff;
        border-color: #dee2e6;
        border-radius: 4px;
        margin: 0 2px;
    }
    .rm-clinica-intermediarios .pagination .page-item.active .page-link {
        background-color: #007bff;
        border-color: #007bff;
        color: #fff;
    }
    .rm-clinica-intermediarios .pagination .page-item.disabled .page-link {
        color: #6c757d;
    }
    .rm-clinica-intermediarios .pagination .page-item:first-child .page-link {
        border-top-left-radius: 4px;
        border-bottom-left-radius: 4px;
    }
    .rm-clinica-intermediarios .pagination .page-item:last-child .page-link {
        border-top-right-radius: 4px;
        border-bottom-right-radius: 4px;
    }
    .rm-clinica-intermediarios .card {
        border-radius: 8px;
        border: none;
        overflow: hidden;
    }
    .rm-clinica-intermediarios .shadow-sm {
        box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04) !important;
    }
    .rm-clinica-intermediarios .border-left {
        border-left-width: 3px !important;
    }
    .rm-clinica-intermediarios .text-uppercase {
        letter-spacing: 0.3px;
    }
    .rm-clinica-intermediarios .bg-primary.rounded-circle {
        box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
    }
    @media (max-width: 768px) {
        .rm-clinica-intermediarios .table {
            font-size: 1rem !important;
        }
        .rm-clinica-intermediarios .table th,
        .rm-clinica-intermediarios .table td {
            padding: 0.75rem;
        }
        .rm-clinica-intermediarios .badge {
            font-size: 0.9rem !important;
            padding: 0.3rem 0.6rem !important;
        }
        .rm-clinica-intermediarios .d-flex.align-items-start {
            flex-direction: column;
        }
        .rm-clinica-intermediarios .d-flex.flex-wrap.align-items-center {
            gap: 0.5rem;
        }
        .rm-clinica-intermediarios .mr-4 {
            margin-right: 1rem !important;
        }
        .rm-clinica-intermediarios .pr-3 {
            padding-right: 0.75rem !important;
        }
        .rm-clinica-intermediarios .bg-primary.rounded-circle {
            width: 50px !important;
            height: 50px !important;
        }
        .rm-clinica-intermediarios .bg-primary.rounded-circle i {
            font-size: 22px !important;
        }
        .rm-clinica-intermediarios .font-weight-bold[style*=\"font-size: 3.2rem\"] {
            font-size: 2.2rem !important;
        }
        .rm-clinica-intermediarios .font-weight-bold[style*=\"font-size: 2rem\"] {
            font-size: 1.5rem !important;
        }
    }
    @media (max-width: 576px) {
        .rm-clinica-intermediarios .d-flex.flex-wrap.align-items-center {
            flex-direction: column;
            align-items: flex-start !important;
        }
        .rm-clinica-intermediarios .mr-4 {
            margin-right: 0 !important;
            margin-bottom: 0.5rem;
        }
        .rm-clinica-intermediarios .pr-3 {
            padding-right: 0 !important;
            border-right: none !important;
        }
        .rm-clinica-intermediarios .border-right {
            border-right: none !important;
        }
        .rm-clinica-intermediarios .font-weight-bold[style*=\"font-size: 3.2rem\"] {
            font-size: 1.8rem !important;
        }
        .rm-clinica-intermediarios .bg-primary.rounded-circle {
            width: 40px !important;
            height: 40px !important;
        }
        .rm-clinica-intermediarios .bg-primary.rounded-circle i {
            font-size: 18px !important;
        }
        .rm-clinica-intermediarios .avatar-circle {
            width: 34px !important;
            height: 34px !important;
            font-size: 13px !important;
        }
    }
");
?>