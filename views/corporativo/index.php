<?php

use app\models\Corporativo;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use kartik\grid\ActionColumn;
use kartik\select2\Select2;
use app\models\RmClinica;
use app\models\User;
use app\models\UserDatos;
use app\models\Contratos;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\CorporativoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $activeCounts     Map: corporate_id => count of affiliates whose best status is Activo */
/** @var array $registeredCounts Map: corporate_id => count of affiliates whose best status is Registrado */
/** @var array $suspendedCounts  Map: corporate_id => count of affiliates whose best status is Suspendido */
/** @var array $annulledCounts   Map: corporate_id => count of affiliates whose only contracts are annulled */
/** @var array $stats            Role-scoped KPI stats array (built in the controller) */
/** @var bool  $isClinicUser     Whether the current user is bound to a clinic */

$this->params['breadcrumbs'][] = ['label' => 'Corporativos', 'url' => ['index']];
$this->title = 'GESTIÓN DE AFILIADOS CORPORATIVOS';

// ── Safe fallbacks if the controller didn't pass the maps ──
$activeCounts     = $activeCounts     ?? [];
$registeredCounts = $registeredCounts ?? [];
$suspendedCounts  = $suspendedCounts  ?? [];
$annulledCounts   = $annulledCounts   ?? [];
$stats            = $stats            ?? [];
$isClinicUser     = $isClinicUser     ?? false;

// ── Header stats — already role-scoped by the controller ──
$totalCorp = $dataProvider->getTotalCount();

// Resolve the clinic name once, only when needed.
$myClinicaName = $isClinicUser
    ? \app\components\UserHelper::getMyClinicaName()
    : '';
?>

<div class="row" style="margin: 3px !important;">
    <input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">

            <!-- ============================================================ -->
            <!-- HEADER — title bar + KPI strip                                -->
            <!-- ============================================================ -->
            <div class="corp-head">

                <div class="corp-head__bar">
                    <div class="corp-head__title-wrap">
                        <span class="corp-head__glyph" aria-hidden="true">
                            <i class="fas fa-building"></i>
                        </span>
                        <div class="corp-head__titles">
                            <h1 class="corp-head__title"><?= Html::encode($this->title) ?></h1>
                            <p class="corp-head__subtitle">
                                <span class="corp-head__count"><?= number_format($totalCorp) ?></span>
                                registro<?= $totalCorp === 1 ? '' : 's' ?>
                                <span class="corp-head__dot">·</span>
                                Gestiona corporativos, planes y afiliados

                                <?php if ($isClinicUser && $myClinicaName !== ''): ?>
                                    <span class="corp-head__scope-badge"
                                        title="Vista limitada a su clínica">
                                        <i class="fas fa-clinic-medical"></i>
                                        <?= Html::encode($myClinicaName) ?>
                                    </span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <?= Html::a(
                        '<span class="corp-cta__badge"><i class="fas fa-plus"></i></span>'
                            . '<span class="corp-cta__label">Crear nuevo corporativo</span>',
                        ['create'],
                        ['class' => 'corp-cta']
                    ) ?>
                </div>

                <div class="corp-stats">
                    <?php foreach ($stats as $s): ?>
                        <?php if (empty($s['show'])): continue;
                        endif; ?>
                        <div class="corp-stat corp-stat--<?= $s['tone'] ?>">
                            <div class="corp-stat__icon" aria-hidden="true">
                                <i class="fas <?= $s['icon'] ?>"></i>
                            </div>
                            <div class="corp-stat__body">
                                <div class="corp-stat__value">
                                    <?= number_format($s['value']) ?>
                                </div>
                                <div class="corp-stat__label"><?= Html::encode($s['label']) ?></div>
                                <div class="corp-stat__helper"><?= Html::encode($s['helper']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- BODY                                                          -->
            <!-- ============================================================ -->
            <div class="ms-panel-body">
                <div class="table-responsive">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'resizableColumns' => false,
                        'bordered' => false,
                        'responsiveWrap' => false,
                        'persistResize' => false,
                        'columns' => [

                            // ── No. ─────────────────────────────────────────
                            [
                                'attribute' => 'rowNumber',
                                'label' => 'No.',
                                'options' => ['style' => 'width: 55px; text-align: center;'],
                                'headerOptions' => [
                                    'class' => 'gridview-header-custom',
                                    'style' => 'width: 55px; min-width: 55px;',
                                ],
                                'contentOptions' => ['class' => 'text-center', 'style' => 'width: 55px;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'No.',
                                    'class' => 'form-control text-center',
                                ],
                                'value' => function ($model, $key, $index, $column) use ($dataProvider) {
                                    $pagination = $dataProvider->getPagination();
                                    return ($pagination->page * $pagination->pageSize) + $index + 1;
                                },
                                'width' => '55px',
                            ],

                            // ── Nombre ──────────────────────────────────────
                            [
                                'attribute' => 'nombre',
                                'label'     => 'Nombre',
                                'header'    => '<i class="fas fa-building" style="margin-right:6px; color:#8ab4f8;"></i> Nombre',
                                'headerOptions' => ['style' => 'color: white !important;'],
                                'contentOptions' => [
                                    'class' => 'corp-name-cell',
                                ],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar por nombre',
                                    'class' => 'form-control text-center',
                                ],
                            ],

                            // ── Teléfono ────────────────────────────────────
                            [
                                'attribute' => 'telefono',
                                'label'     => 'Teléfono',
                                'header'    => '<i class="fas fa-phone" style="margin-right:6px; color:#8ab4f8;"></i> Teléfono',
                                'headerOptions' => [
                                    'style' => 'color: white !important; width: 130px; min-width: 130px;',
                                ],
                                'contentOptions' => [
                                    'class' => 'text-center corp-phone-cell',
                                    'style' => 'width: 130px; min-width: 130px;',
                                ],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar teléfono',
                                    'class' => 'form-control text-center',
                                ],
                            ],

                            // ── Estatus ─────────────────────────────────────
                            [
                                'attribute' => 'estatus',
                                'label'     => 'Estatus',
                                'header'    => '<i class="fas fa-circle" style="margin-right:6px; color:#a5d6a7;"></i> Estatus',
                                'headerOptions' => [
                                    'style' => 'color: white !important; width: 120px; min-width: 120px;',
                                ],
                                'contentOptions' => [
                                    'style' => 'width: 120px; min-width: 120px; text-align: center; vertical-align: middle;',
                                ],
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $estatus = $model->estatus ?: 'Inactivo';

                                    $map = [
                                        'Activo'    => ['badge-success', 'Activo'],
                                        'Inactivo'  => ['badge-danger',  'Inactivo'],
                                        'Pendiente' => ['badge-warning', 'Pendiente'],
                                    ];

                                    [$cls, $label] = $map[$estatus]
                                        ?? ['badge-secondary', $estatus];

                                    return '<span class="badge ' . $cls . '" style="
                                        font-size: 0.9rem;
                                        padding: 7px 14px;
                                        border-radius: 20px;
                                        font-weight: 700;
                                        letter-spacing: 0.3px;
                                    ">'
                                        . Html::encode($label) .
                                        '</span>';
                                },
                                'filter' => Select2::widget([
                                    'model' => $searchModel,
                                    'attribute' => 'estatus',
                                    'data' => [
                                        'Activo'    => 'Activo',
                                        'Inactivo'  => 'Inactivo',
                                        'Pendiente' => 'Pendiente',
                                    ],
                                    'options' => ['placeholder' => 'Estatus...'],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                    ],
                                ]),
                            ],

                            // ── Clínicas Asoc. ──────────────────────────────
                            [
                                'attribute' => 'clinicas_ids',
                                'label'     => 'Clínicas Asoc.',
                                'header'    => '<i class="fas fa-hospital" style="margin-right:6px; color:#67e8f9;"></i> Clínicas Asoc.',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $count = count($model->clinicas);

                                    return Html::a(
                                        '<div class="count-tile count-tile--clinics">'
                                            . '<div class="count-tile__number">' . (int)$count . '</div>'
                                            . '<div class="count-tile__label">'
                                            . 'Clínica' . ($count === 1 ? '' : 's')
                                            . '</div>'
                                            . '</div>',
                                        ['view', 'id' => $model->id],
                                        [
                                            'title' => 'Ver clínicas asociadas',
                                            'class' => 'count-tile-link',
                                        ]
                                    );
                                },
                                'filterType' => GridView::FILTER_SELECT2,
                                'filter' => ArrayHelper::map(
                                    RmClinica::find()->orderBy('nombre')->all(),
                                    'id',
                                    'nombre'
                                ),
                                'filterWidgetOptions' => [
                                    'pluginOptions' => ['allowClear' => true],
                                ],
                                'filterInputOptions' => ['placeholder' => 'Filtrar clínicas...'],
                                'headerOptions' => [
                                    'style' => 'color: white !important; width: 120px; min-width: 120px; text-align: center;',
                                ],
                                'contentOptions' => [
                                    'style' => 'width: 120px; min-width: 120px; text-align: center; vertical-align: middle;',
                                ],
                            ],

                            // ── Afiliados — count-tile per bucket, mirrors Clínicas Asoc. ──
                            [
                                'attribute'     => 'active_affiliates_count',
                                'label'         => 'Afiliados',
                                'header'        => '<i class="fas fa-users" style="margin-right:6px; color:#a5b4fc;"></i> Afiliados',
                                'format'        => 'raw',
                                'value'  => function ($model) use (
                                    $activeCounts,
                                    $registeredCounts,
                                    $suspendedCounts,
                                    $annulledCounts
                                ) {
                                    $corpId = (int) $model->id;

                                    $active     = (int) ($activeCounts[$corpId]     ?? 0);
                                    $registered = (int) ($registeredCounts[$corpId] ?? 0);
                                    $suspended  = (int) ($suspendedCounts[$corpId]  ?? 0);
                                    $annulled   = (int) ($annulledCounts[$corpId]   ?? 0);

                                    $url = Url::to(['view', 'id' => $corpId]);

                                    // Build the tile list — ONLY include buckets with count > 0.
                                    $tiles = [];

                                    if ($active > 0) {
                                        $tiles[] = Html::a(
                                            '<div class="count-tile count-tile--affiliates-active">'
                                                . '<div class="count-tile__number">' . $active . '</div>'
                                                . '<div class="count-tile__label">'
                                                . 'Activo' . ($active === 1 ? '' : 's')
                                                . '</div>'
                                                . '</div>',
                                            $url,
                                            [
                                                'class' => 'count-tile-link',
                                                'title' => 'Afiliados con contrato Activo',
                                            ]
                                        );
                                    }

                                    if ($registered > 0) {
                                        $tiles[] = Html::a(
                                            '<div class="count-tile count-tile--affiliates-registered">'
                                                . '<div class="count-tile__number">' . $registered . '</div>'
                                                . '<div class="count-tile__label">'
                                                . 'Registrado' . ($registered === 1 ? '' : 's')
                                                . '</div>'
                                                . '</div>',
                                            $url,
                                            [
                                                'class' => 'count-tile-link',
                                                'title' => 'Afiliados esperando primer pago',
                                            ]
                                        );
                                    }

                                    if ($suspended > 0) {
                                        $tiles[] = Html::a(
                                            '<div class="count-tile count-tile--affiliates-suspended">'
                                                . '<div class="count-tile__number">' . $suspended . '</div>'
                                                . '<div class="count-tile__label">'
                                                . 'Suspendido' . ($suspended === 1 ? '' : 's')
                                                . '</div>'
                                                . '</div>',
                                            $url,
                                            [
                                                'class' => 'count-tile-link',
                                                'title' => 'Afiliados suspendidos por cuotas vencidas',
                                            ]
                                        );
                                    }

                                    if ($annulled > 0) {
                                        $tiles[] = Html::a(
                                            '<div class="count-tile count-tile--affiliates-annulled">'
                                                . '<div class="count-tile__number">' . $annulled . '</div>'
                                                . '<div class="count-tile__label">'
                                                . 'Anulado' . ($annulled === 1 ? '' : 's')
                                                . '</div>'
                                                . '</div>',
                                            $url,
                                            [
                                                'class' => 'count-tile-link',
                                                'title' => 'Afiliados con contratos anulados o egresados',
                                            ]
                                        );
                                    }

                                    // Edge case: all four are zero.
                                    if (empty($tiles)) {
                                        return '<div class="affiliate-empty">'
                                            . '<i class="fas fa-user-slash" style="opacity: 0.35;"></i>'
                                            . '<span>Sin afiliados</span>'
                                            . '</div>';
                                    }

                                    return '<div class="count-tile-strip">'
                                        . implode('', $tiles)
                                        . '</div>';
                                },
                                'headerOptions' => [
                                    'style' => 'color: white !important; width: 220px; min-width: 220px; text-align: center;',
                                ],
                                'contentOptions' => [
                                    'style' => 'width: 220px; min-width: 220px; vertical-align: middle; padding: 10px 8px !important; text-align: center;',
                                ],
                            ],

                            // ── Acciones ────────────────────────────────────
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => '<i class="fas fa-cogs" style="margin-right:6px;"></i> ACCIONES',
                                'template' => '<div class="d-flex align-items-center justify-content-center actions-wrapper">{view}{update}{pay}{corporate-contract}{upload-affiliates}</div>',
                                'options' => ['style' => 'width: 300px; min-width: 300px;'],
                                'headerOptions' => ['style' => 'color: white !important; text-align: center;'],
                                'contentOptions' => ['style' => 'text-align: center; padding: 10px !important; vertical-align: middle !important;'],
                                'buttons' => [

                                    'view' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fa fa-eye"></i>',
                                            Url::to(['view', 'id' => $model->id]),
                                            [
                                                'title' => 'Detalle de Corporativo',
                                                'class' => 'btn-action view'
                                            ]
                                        );
                                    },

                                    'update' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fas fa-pencil-alt"></i>',
                                            Url::to(['update', 'id' => $model->id]),
                                            [
                                                'title' => 'Editar Corporativo',
                                                'class' => 'btn-action edit'
                                            ]
                                        );
                                    },

                                    'pay' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fas fa-credit-card"></i>',
                                            Url::to(['deuda', 'id' => $model->id]),
                                            [
                                                'title' => 'Pagar Corporativo',
                                                'class' => 'btn-action corporate-payment'
                                            ]
                                        );
                                    },

                                    'corporate-contract' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fas fa-file-contract"></i>',
                                            Url::to(['contracts', 'id' => $model->id]),
                                            [
                                                'title'     => 'Ver Contratos Corporativos',
                                                'class'     => 'btn-action corporate-contract',
                                                'data-pjax' => '0',
                                            ]
                                        );
                                    },

                                    'upload-affiliates' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fas fa-file-upload"></i>',
                                            Url::to(['carga-masiva-afiliados', 'corporativo_id' => $model->id]),
                                            [
                                                'title' => 'Carga Masiva de Afiliados',
                                                'class' => 'btn-action upload-affiliates',
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
    <div class="clearfix"></div>
</div>

<!-- ============================================================ -->
<!-- STYLES                                                        -->
<!-- ============================================================ -->
<style>
    /* ═══════════════════════════════════════════════════════════ */
    /* HEADER — title bar + KPI strip                              */
    /* ═══════════════════════════════════════════════════════════ */
    .corp-head {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 22px;
        padding: 26px 30px 24px !important;
        background: linear-gradient(180deg, #ffffff 0%, #f7f9fb 100%);
        border-bottom: 1px solid #e6eaee;
    }

    .corp-head::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #0078d4 0%, #106ebe 40%, #1cc88a 100%);
        opacity: 0.9;
    }

    .corp-head__bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        flex-wrap: wrap;
    }

    .corp-head__title-wrap {
        display: flex;
        align-items: center;
        gap: 18px;
        min-width: 0;
    }

    .corp-head__glyph {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 56px;
        height: 56px;
        border-radius: 14px;
        background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);
        color: #ffffff;
        font-size: 1.4rem;
        box-shadow:
            0 10px 22px rgba(26, 37, 47, 0.28),
            inset 0 1px 0 rgba(255, 255, 255, 0.15);
        flex-shrink: 0;
    }

    .corp-head__titles {
        min-width: 0;
    }

    .corp-head__title {
        font-size: clamp(1.2rem, 1.9vw, 1.65rem);
        font-weight: 800;
        color: #1a237e;
        letter-spacing: -0.4px;
        line-height: 1.15;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .corp-head__subtitle {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.9rem;
        color: #605e5c;
        margin: 6px 0 0;
        line-height: 1.35;
        font-weight: 400;
        flex-wrap: wrap;
    }

    .corp-head__count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        padding: 1px 8px;
        border-radius: 6px;
        background: #e6f2fc;
        color: #0078d4;
        font-size: 0.85rem;
        font-weight: 800;
        font-variant-numeric: tabular-nums;
    }

    .corp-head__dot {
        color: #c8c6c4;
        font-weight: 700;
        margin: 0 2px;
    }

    /* ── Clinic-scope badge (only shown for clinic-bound roles) ── */
    .corp-head__scope-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 10px;
        border-radius: 999px;
        background: linear-gradient(135deg, #e6f2fc 0%, #d4e8f7 100%);
        color: #0078d4;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.3px;
        border: 1px solid rgba(0, 120, 212, 0.20);
        white-space: nowrap;
        max-width: 240px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .corp-head__scope-badge i {
        font-size: 0.72rem;
        flex-shrink: 0;
    }

    /* ── CTA button ──────────────────────────────────────────── */
    .corp-cta,
    .corp-cta:link,
    .corp-cta:visited,
    .corp-cta:hover,
    .corp-cta:focus,
    .corp-cta:active {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        text-decoration: none !important;
        text-shadow: none !important;
    }

    .corp-cta {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 10px 20px 10px 10px;
        border-radius: 999px;
        background: linear-gradient(135deg, #0078d4 0%, #106ebe 60%, #0b4f8a 100%);
        font-weight: 700;
        font-size: 0.95rem;
        letter-spacing: 0.2px;
        box-shadow:
            0 8px 18px rgba(16, 110, 190, 0.28),
            0 1px 3px rgba(16, 110, 190, 0.20),
            inset 0 1px 0 rgba(255, 255, 255, 0.25);
        position: relative;
        overflow: hidden;
        transition: transform 0.25s cubic-bezier(.2, .7, .2, 1),
            box-shadow 0.25s ease;
        isolation: isolate;
        flex-shrink: 0;
    }

    .corp-cta .corp-cta__label,
    .corp-cta .corp-cta__badge,
    .corp-cta .corp-cta__badge i {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    .corp-cta::after {
        content: "";
        position: absolute;
        top: 0;
        left: -120%;
        width: 55%;
        height: 100%;
        background: linear-gradient(120deg,
                transparent 0%,
                rgba(255, 255, 255, 0.35) 45%,
                rgba(255, 255, 255, 0.55) 50%,
                rgba(255, 255, 255, 0.35) 55%,
                transparent 100%);
        transform: skewX(-20deg);
        transition: left 0.6s cubic-bezier(.2, .7, .2, 1);
        pointer-events: none;
    }

    .corp-cta:hover::after {
        left: 130%;
    }

    .corp-cta:hover,
    .corp-cta:focus {
        color: #ffffff !important;
        transform: translateY(-2px);
        box-shadow:
            0 14px 26px rgba(16, 110, 190, 0.38),
            0 3px 8px rgba(16, 110, 190, 0.22),
            inset 0 1px 0 rgba(255, 255, 255, 0.30);
        text-decoration: none !important;
        outline: none;
    }

    .corp-cta:active {
        transform: translateY(0);
        box-shadow:
            0 4px 12px rgba(16, 110, 190, 0.30),
            inset 0 2px 4px rgba(0, 0, 0, 0.15);
    }

    .corp-cta__badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.20);
        color: #ffffff;
        font-size: 0.85rem;
        flex-shrink: 0;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.35);
        transition: transform 0.35s cubic-bezier(.2, .7, .2, 1),
            background 0.25s ease;
    }

    .corp-cta:hover .corp-cta__badge {
        transform: rotate(90deg);
        background: rgba(255, 255, 255, 0.30);
    }

    .corp-cta__label {
        white-space: nowrap;
    }

    /* ═══════════════════════════════════════════════════════════ */
    /* KPI STRIP                                                   */
    /* ═══════════════════════════════════════════════════════════ */
    .corp-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 14px;
    }

    .corp-stat {
        position: relative;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 16px 18px 15px;
        background: #ffffff;
        border: 1px solid #e6eaee;
        border-radius: 12px;
        transition: transform 0.15s ease,
            box-shadow 0.15s ease,
            border-color 0.15s ease;
        overflow: hidden;
    }

    .corp-stat::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        width: 4px;
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
    }

    .corp-stat:hover {
        transform: translateY(-2px);
        border-color: #d5dce3;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
    }

    .corp-stat__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 11px;
        font-size: 1.05rem;
        flex-shrink: 0;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
    }

    .corp-stat__body {
        display: flex;
        flex-direction: column;
        min-width: 0;
        flex: 1;
    }

    .corp-stat__value {
        font-size: 1.65rem;
        font-weight: 800;
        line-height: 1;
        letter-spacing: -0.6px;
        font-variant-numeric: tabular-nums;
        color: #1a237e;
    }

    .corp-stat__label {
        font-size: 0.78rem;
        font-weight: 700;
        color: #323130;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-top: 6px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .corp-stat__helper {
        font-size: 0.72rem;
        font-weight: 500;
        color: #8a8886;
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .corp-stat--primary::before {
        background: linear-gradient(180deg, #2f9bff 0%, #0078d4 100%);
    }

    .corp-stat--primary .corp-stat__icon {
        background: #e6f2fc;
        color: #0078d4;
    }

    .corp-stat--success::before {
        background: linear-gradient(180deg, #34d399 0%, #059669 100%);
    }

    .corp-stat--success .corp-stat__icon {
        background: #ecfdf5;
        color: #059669;
    }

    .corp-stat--indigo::before {
        background: linear-gradient(180deg, #818cf8 0%, #4f46e5 100%);
    }

    .corp-stat--indigo .corp-stat__icon {
        background: #eef2ff;
        color: #4f46e5;
    }

    .corp-stat--amber::before {
        background: linear-gradient(180deg, #fbbf24 0%, #d97706 100%);
    }

    .corp-stat--amber .corp-stat__icon {
        background: #fef3c7;
        color: #b45309;
    }

    .corp-stat--amber .corp-stat__value {
        color: #b45309;
    }

    .corp-stat--slate::before {
        background: linear-gradient(180deg, #9ca3af 0%, #4b5563 100%);
    }

    .corp-stat--slate .corp-stat__icon {
        background: #f3f4f6;
        color: #4b5563;
    }

    .corp-stat--slate .corp-stat__value {
        color: #4b5563;
    }

    .corp-stat--danger::before {
        background: linear-gradient(180deg, #fb7185 0%, #be123c 100%);
    }

    .corp-stat--danger .corp-stat__icon {
        background: #ffe4e6;
        color: #be123c;
    }

    .corp-stat--teal::before {
        background: linear-gradient(180deg, #22d3ee 0%, #0891b2 100%);
    }

    .corp-stat--teal .corp-stat__icon {
        background: #ecfeff;
        color: #0891b2;
    }

    @media (max-width: 992px) {
        .corp-head {
            padding: 22px 22px 20px !important;
            gap: 18px;
        }

        .corp-stat {
            padding: 14px 16px 13px;
            gap: 12px;
        }

        .corp-stat__value {
            font-size: 1.45rem;
        }
    }

    @media (max-width: 576px) {
        .corp-head {
            padding: 18px 16px 16px !important;
            gap: 16px;
        }

        .corp-head__bar {
            gap: 14px;
        }

        .corp-head__glyph {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            font-size: 1.1rem;
        }

        .corp-head__title {
            white-space: normal;
            font-size: 1.05rem;
        }

        .corp-head__subtitle {
            font-size: 0.82rem;
        }

        .corp-head__scope-badge {
            max-width: 100%;
            font-size: 0.72rem;
        }

        .corp-cta {
            padding: 9px 16px 9px 9px;
            font-size: 0.88rem;
            width: 100%;
            justify-content: center;
        }

        .corp-cta__badge {
            width: 28px;
            height: 28px;
            font-size: 0.78rem;
        }

        .corp-stats {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .corp-stat {
            padding: 12px 14px;
        }

        .corp-stat__value {
            font-size: 1.35rem;
        }

        .corp-stat__label {
            font-size: 0.72rem;
        }

        .corp-stat__helper {
            font-size: 0.68rem;
        }
    }

    /* ═══════════════════════════════════════════════════════════ */
    /* GRID — name & secondary cells                               */
    /* ═══════════════════════════════════════════════════════════ */
    .grid-view tbody td.corp-name-cell {
        font-size: 1.5rem !important;
        font-weight: 800 !important;
        color: #1a237e !important;
        letter-spacing: 0.2px;
        vertical-align: middle;
        line-height: 1.25;
    }

    .grid-view tbody td.corp-phone-cell {
        font-size: 1rem !important;
        font-weight: 700 !important;
        color: #1a237e !important;
        letter-spacing: 0.2px;
        vertical-align: middle;
        font-variant-numeric: tabular-nums;
    }

    /* ═══════════════════════════════════════════════════════════ */
    /* GRID HEADER                                                 */
    /* ═══════════════════════════════════════════════════════════ */
    .grid-view thead th,
    .table thead th,
    .kv-grid-table thead th {
        background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%) !important;
        color: #ffffff !important;
        font-size: 0.95rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        padding: 14px 12px !important;
        border-color: rgba(255, 255, 255, 0.15) !important;
        vertical-align: middle !important;
    }

    .grid-view thead th a,
    .table thead th a {
        color: #ffffff !important;
        text-decoration: none !important;
    }

    .grid-view thead th a:hover {
        color: #ffd700 !important;
    }

    /* Sort indicator — make it visible on the dark header */
    .grid-view thead th a::after,
    .grid-view thead th .sort-indicator {
        color: #ffd700 !important;
    }

    /* ═══════════════════════════════════════════════════════════ */
    /* GRID BODY                                                   */
    /* ═══════════════════════════════════════════════════════════ */
    .grid-view tbody td {
        font-size: 1rem !important;
        padding: 14px 10px !important;
        vertical-align: middle !important;
    }

    .grid-view .form-control {
        font-size: 0.95rem !important;
        height: auto !important;
        min-height: 38px !important;
    }

    /* ═══════════════════════════════════════════════════════════ */
    /* COUNT TILES — shared by Clínicas Asoc. and Afiliados        */
    /* ═══════════════════════════════════════════════════════════ */
    .count-tile-link {
        display: inline-block;
        text-decoration: none !important;
        transition: transform 0.15s ease;
    }

    .count-tile-link:hover {
        transform: translateY(-2px);
    }

    .count-tile {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1px;
        padding: 6px 10px 5px;
        border-radius: 10px;
        min-width: 62px;
        transition: box-shadow 0.15s ease, transform 0.15s ease;
        position: relative;
        overflow: hidden;
    }

    .count-tile::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        opacity: 0.9;
    }

    .count-tile__number {
        font-size: 1.35rem;
        font-weight: 800;
        line-height: 1;
        letter-spacing: -0.5px;
        font-variant-numeric: tabular-nums;
        text-shadow: 0 1px 0 rgba(255, 255, 255, 0.6);
    }

    .count-tile__label {
        font-size: 0.6rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        opacity: 0.85;
        margin-top: 2px;
        white-space: nowrap;
    }

    /* ── Clinics tile ──────────────────────────────────────────── */
    .count-tile--clinics {
        background: linear-gradient(135deg, #ecfeff 0%, #cffafe 100%);
        border: 2px solid #67e8f9;
        box-shadow: 0 2px 6px rgba(8, 145, 178, 0.15);
    }

    .count-tile--clinics::before {
        background: linear-gradient(90deg, #06b6d4 0%, #0891b2 100%);
    }

    .count-tile--clinics .count-tile__number {
        color: #0e7490;
    }

    .count-tile--clinics .count-tile__label {
        color: #155e75;
    }

    .count-tile-link:hover .count-tile--clinics {
        box-shadow: 0 8px 20px rgba(8, 145, 178, 0.35);
        border-color: #22d3ee;
    }

    /* ── Affiliate tiles (one per bucket) ──────────────────────── */
    .count-tile--affiliates-active {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        border: 2px solid #6ee7b7;
        box-shadow: 0 2px 6px rgba(5, 150, 105, 0.15);
    }

    .count-tile--affiliates-active::before {
        background: linear-gradient(90deg, #10b981 0%, #059669 100%);
    }

    .count-tile--affiliates-active .count-tile__number {
        color: #047857;
    }

    .count-tile--affiliates-active .count-tile__label {
        color: #065f46;
    }

    .count-tile-link:hover .count-tile--affiliates-active {
        box-shadow: 0 8px 20px rgba(5, 150, 105, 0.35);
        border-color: #34d399;
    }

    .count-tile--affiliates-registered {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        border: 2px solid #fcd34d;
        box-shadow: 0 2px 6px rgba(217, 119, 6, 0.15);
    }

    .count-tile--affiliates-registered::before {
        background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
    }

    .count-tile--affiliates-registered .count-tile__number {
        color: #b45309;
    }

    .count-tile--affiliates-registered .count-tile__label {
        color: #92400e;
    }

    .count-tile-link:hover .count-tile--affiliates-registered {
        box-shadow: 0 8px 20px rgba(217, 119, 6, 0.35);
        border-color: #fbbf24;
    }

    .count-tile--affiliates-suspended {
        background: linear-gradient(135deg, #f9fafb 0%, #e5e7eb 100%);
        border: 2px solid #9ca3af;
        box-shadow: 0 2px 6px rgba(75, 85, 99, 0.15);
    }

    .count-tile--affiliates-suspended::before {
        background: linear-gradient(90deg, #6b7280 0%, #4b5563 100%);
    }

    .count-tile--affiliates-suspended .count-tile__number {
        color: #4b5563;
    }

    .count-tile--affiliates-suspended .count-tile__label {
        color: #374151;
    }

    .count-tile-link:hover .count-tile--affiliates-suspended {
        box-shadow: 0 8px 20px rgba(75, 85, 99, 0.30);
        border-color: #6b7280;
    }

    .count-tile--affiliates-annulled {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        border: 2px solid #fca5a5;
        box-shadow: 0 2px 6px rgba(185, 28, 28, 0.15);
    }

    .count-tile--affiliates-annulled::before {
        background: linear-gradient(90deg, #ef4444 0%, #b91c1c 100%);
    }

    .count-tile--affiliates-annulled .count-tile__number {
        color: #b91c1c;
    }

    .count-tile--affiliates-annulled .count-tile__label {
        color: #7f1d1d;
    }

    .count-tile-link:hover .count-tile--affiliates-annulled {
        box-shadow: 0 8px 20px rgba(185, 28, 28, 0.35);
        border-color: #f87171;
    }

    /* ── Strip wrapper — holds multiple affiliate tiles per row ── */
    .count-tile-strip {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        justify-content: center;
        align-items: flex-start;
        width: 100%;
    }

    /* Each tile sizes to its content instead of stretching to 50%. */
    .count-tile-strip .count-tile-link {
        flex: 0 0 auto;
        display: inline-block;
    }

    /* Let the inner tile shrink-to-fit its number + label. */
    .count-tile-strip .count-tile {
        min-width: 62px;
        width: auto;
    }

    /* ── Empty state (no affiliates at all) ───────────────────── */
    .affiliate-empty {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 14px 10px;
        border-radius: 10px;
        background: #fafbfc;
        border: 1px dashed #d1d5db;
        color: #9ca3af;
        font-size: 0.82rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        font-style: italic;
    }

    .affiliate-empty i {
        font-size: 0.95rem;
    }

    @media (max-width: 768px) {
        .count-tile {
            min-width: 56px;
            padding: 5px 8px 4px;
        }

        .count-tile__number {
            font-size: 1.15rem;
        }

        .count-tile__label {
            font-size: 0.55rem;
        }
    }

    /* ═══════════════════════════════════════════════════════════ */
    /* ACTION BUTTONS                                              */
    /* ═══════════════════════════════════════════════════════════ */
    .grid-view td .actions-wrapper {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 6px !important;
        height: 100%;
        min-height: 42px;
        line-height: 1;
        flex-wrap: nowrap;
    }

    .btn-action {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 42px !important;
        height: 42px !important;
        padding: 0 !important;
        border-radius: 10px !important;
        font-size: 1.15rem !important;
        line-height: 1 !important;
        flex: 0 0 auto;
        vertical-align: middle;
        transition: all 0.2s ease !important;
        text-decoration: none !important;
        border: 2px solid transparent !important;
    }

    .btn-action i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1 !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 1em;
        height: 1em;
        vertical-align: middle;
    }

    .btn-action.view {
        color: #007bff !important;
        background-color: rgba(0, 123, 255, 0.10) !important;
        border-color: rgba(0, 123, 255, 0.25) !important;
    }

    .btn-action.view:hover {
        color: #ffffff !important;
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
        border-color: #0056b3 !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 14px rgba(0, 123, 255, 0.4) !important;
    }

    .btn-action.edit {
        color: #f59e0b !important;
        background-color: rgba(245, 158, 11, 0.12) !important;
        border-color: rgba(245, 158, 11, 0.30) !important;
    }

    .btn-action.edit:hover {
        color: #ffffff !important;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
        border-color: #d97706 !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 14px rgba(245, 158, 11, 0.4) !important;
    }

    .btn-action.corporate-payment {
        color: #10b981 !important;
        background-color: rgba(16, 185, 129, 0.12) !important;
        border-color: rgba(16, 185, 129, 0.30) !important;
    }

    .btn-action.corporate-payment:hover {
        color: #ffffff !important;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        border-color: #059669 !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 14px rgba(16, 185, 129, 0.4) !important;
    }

    .btn-action.corporate-contract {
        color: #dc3545 !important;
        background-color: rgba(220, 53, 69, 0.12) !important;
        border-color: rgba(220, 53, 69, 0.30) !important;
    }

    .btn-action.corporate-contract:hover {
        color: #ffffff !important;
        background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%) !important;
        border-color: #a71d2a !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 14px rgba(220, 53, 69, 0.4) !important;
    }

    .btn-action.upload-affiliates {
        color: #8b5cf6 !important;
        background-color: rgba(139, 92, 246, 0.12) !important;
        border-color: rgba(139, 92, 246, 0.30) !important;
    }

    .btn-action.upload-affiliates:hover {
        color: #ffffff !important;
        background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%) !important;
        border-color: #6d28d9 !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 14px rgba(139, 92, 246, 0.4) !important;
    }

    /* ═══════════════════════════════════════════════════════════ */
    /* BADGES                                                      */
    /* ═══════════════════════════════════════════════════════════ */
    .grid-view .badge {
        font-size: 0.9rem !important;
        padding: 7px 14px !important;
        border-radius: 20px !important;
        font-weight: 700 !important;
        letter-spacing: 0.3px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .grid-view .badge:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    .grid-view .badge.badge-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
        color: #ffffff !important;
    }

    .grid-view .badge.badge-info {
        background: linear-gradient(135deg, #17a2b8 0%, #0d6e7c 100%) !important;
        color: #ffffff !important;
    }

    .grid-view .badge.badge-success {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
        color: #ffffff !important;
    }

    .grid-view .badge.badge-danger {
        background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%) !important;
        color: #ffffff !important;
    }

    .grid-view .badge.badge-warning {
        background: linear-gradient(135deg, #ffc107 0%, #d39e00 100%) !important;
        color: #212529 !important;
    }

    .grid-view .badge.badge-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #494f54 100%) !important;
        color: #ffffff !important;
    }

    /* ═══════════════════════════════════════════════════════════ */
    /* SELECT2 FILTER ROW                                          */
    /* ═══════════════════════════════════════════════════════════ */
    .grid-view thead .select2-container {
        width: 100% !important;
    }

    .grid-view thead .select2-selection--single {
        height: 38px !important;
        border-radius: 8px !important;
        border: 1px solid rgba(255, 255, 255, 0.35) !important;
        background: #ffffff !important;
        padding: 0 4px !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .grid-view thead .select2-selection--single:hover {
        border-color: rgba(255, 255, 255, 0.6) !important;
    }

    .grid-view thead .select2-container--focus .select2-selection--single,
    .grid-view thead .select2-container--open .select2-selection--single {
        border-color: #4db8ff !important;
        box-shadow: 0 0 0 3px rgba(77, 184, 255, 0.35) !important;
    }

    .grid-view thead .select2-selection__rendered {
        line-height: 36px !important;
        font-size: 0.9rem !important;
        padding-left: 10px !important;
        padding-right: 26px !important;
        color: #323130 !important;
    }

    .grid-view thead .select2-selection__placeholder {
        color: #8a8886 !important;
    }

    .grid-view thead .select2-selection__arrow {
        height: 36px !important;
        right: 8px !important;
    }

    .grid-view thead .select2-selection__arrow b {
        border-color: #323130 transparent transparent transparent !important;
        border-width: 5px 4px 0 4px !important;
    }

    .grid-view thead .select2-container--open .select2-selection__arrow b {
        border-color: transparent transparent #323130 transparent !important;
        border-width: 0 4px 5px 4px !important;
    }

    .grid-view thead .select2-selection__clear {
        color: #605e5c !important;
        font-weight: 700 !important;
        margin-right: 22px !important;
    }

    .grid-view thead .select2-selection__clear:hover {
        color: #a4262c !important;
    }

    .select2-container--krajee .select2-search--dropdown .select2-search__field,
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #c8c6c4 !important;
        border-radius: 6px !important;
        padding: 6px 10px !important;
        font-size: 0.9rem !important;
        color: #323130 !important;
        background: #ffffff !important;
        outline: none !important;
    }

    .select2-container--krajee .select2-search--dropdown .select2-search__field:focus,
    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
        border-color: #0078d4 !important;
        box-shadow: 0 0 0 2px rgba(0, 120, 212, 0.18) !important;
    }

    .select2-container--krajee .select2-dropdown,
    .select2-container--default .select2-dropdown {
        border: 1px solid #e1dfdd !important;
        border-radius: 8px !important;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12) !important;
        background: #ffffff !important;
        overflow: hidden;
    }

    .select2-container--krajee .select2-results__option,
    .select2-container--default .select2-results__option {
        color: #323130 !important;
        font-size: 0.9rem !important;
        padding: 8px 12px !important;
        background: #ffffff !important;
        transition: background 0.1s ease;
    }

    .select2-container--krajee .select2-results__option--highlighted,
    .select2-container--default .select2-results__option--highlighted,
    .select2-container--krajee .select2-results__option[aria-selected="true"],
    .select2-container--default .select2-results__option[aria-selected="true"] {
        background: #e6f2fc !important;
        color: #0078d4 !important;
    }

    .select2-container--krajee .select2-results__message,
    .select2-container--default .select2-results__message {
        color: #8a8886 !important;
        font-size: 0.9rem !important;
        padding: 8px 12px !important;
    }

    /* ═══════════════════════════════════════════════════════════ */
    /* PAGINATION                                                  */
    /* ═══════════════════════════════════════════════════════════ */
    .grid-view .pagination .page-link {
        font-size: 1rem !important;
        padding: 10px 16px !important;
        border-radius: 8px !important;
        margin: 0 3px !important;
        color: #2c3e50 !important;
        border: 2px solid #e1e8ed !important;
        font-weight: 600 !important;
    }

    .grid-view .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
        border-color: #0056b3 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.35) !important;
    }

    .grid-view .pagination .page-link:hover {
        background: #f1f5f9 !important;
        border-color: #007bff !important;
        color: #007bff !important;
        transform: translateY(-1px);
    }

    /* ═══════════════════════════════════════════════════════════ */
    /* SUMMARY / FOOTER                                            */
    /* ═══════════════════════════════════════════════════════════ */
    .grid-view .kv-panel-after,
    .grid-view .panel-footer {
        font-size: 1rem !important;
        padding: 15px !important;
    }
</style>

<!-- ============================================================ -->
<!-- DRAWER HOST                                                   -->
<!-- ============================================================ -->
<div id="corporateContractDrawerHost"></div>

<!-- ============================================================ -->
<!-- JAVASCRIPT — drawer logic                                     -->
<!-- ============================================================ -->
<script>
    (function() {
        'use strict';

        window.openCorporateContractDrawer = function(event, el) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            var url = el ? el.getAttribute('href') : null;
            if (!url) return false;

            var existing = document.getElementById('corporateContractDrawer');
            if (existing) {
                existing.classList.remove('closing');
                return false;
            }

            var host = document.getElementById('corporateContractDrawerHost');
            if (!host) return false;

            host.innerHTML = '<div style="padding:30px;text-align:center;color:#666;font-size:1.2rem;">' +
                '<i class="fas fa-spinner fa-spin"></i> Cargando…</div>';

            fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                .then(function(r) {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.text();
                })
                .then(function(html) {
                    host.innerHTML = html;
                    host.querySelectorAll('script').forEach(function(oldScript) {
                        var s = document.createElement('script');
                        if (oldScript.src) {
                            s.src = oldScript.src;
                        } else {
                            s.textContent = oldScript.textContent;
                        }
                        oldScript.parentNode.replaceChild(s, oldScript);
                    });
                    var drawer = document.getElementById('corporateContractDrawer');
                    if (drawer) {
                        drawer.classList.add('closing');
                        requestAnimationFrame(function() {
                            requestAnimationFrame(function() {
                                drawer.classList.remove('closing');
                            });
                        });
                    }
                })
                .catch(function(err) {
                    host.innerHTML = '<div style="padding:20px;color:#d13438;font-size:1.1rem;">' +
                        'Error: ' + err.message + '</div>';
                });

            return false;
        };

        window.closeCorporateContractDrawer = function(event) {
            if (event && event.target !== event.currentTarget) return;
            var drawer = document.getElementById('corporateContractDrawer');
            if (!drawer) return;
            drawer.classList.add('closing');
            setTimeout(function() {
                var host = document.getElementById('corporateContractDrawerHost');
                if (host) host.innerHTML = '';
            }, 320);
        };

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.closeCorporateContractDrawer();
            }
        });
    })();
</script>