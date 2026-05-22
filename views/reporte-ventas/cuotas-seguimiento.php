<?php
// views/reporteventas/cuotas-seguimiento.php - PURPLE FOR POR VENCER

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\web\View;
use yii\data\ArrayDataProvider;

/**
 * @var View $this
 * @var ArrayDataProvider $dataProvider
 * @var array $stats
 * @var string $filtro
 * @var int|null $clinicaId
 * @var string|null $search
 * @var \app\models\RmClinica[] $clinicas
 */

$this->title = 'Seguimiento de Cobranza - Equipo de Ventas';
$this->params['breadcrumbs'][] = $this->title;

// Helper function to render tooltip content
function renderCuotaTooltip($cuotas, $title, $type, $total)
{
    if (empty($cuotas)) {
        return '<div style="padding: 15px; text-align: center; color: #999;">No hay cuotas en esta categoría</div>';
    }

    $iconMap = [
        'critical' => '🔴',
        'grace' => '🟠',
        'urgent' => '🟣',
        'warning' => '📆',
        'normal' => '📋',
    ];
    $icon = isset($iconMap[$type]) ? $iconMap[$type] : '📋';

    $html = '<div style="min-width: 280px; padding: 8px;">';
    $html .= '<div style="font-weight: bold; border-bottom: 2px solid #dee2e6; padding-bottom: 8px; margin-bottom: 10px;">';
    $html .= $icon . ' ' . htmlspecialchars($title) . '</div>';
    $html .= '<div style="max-height: 300px; overflow-y: auto;">';
    $html .= '<table style="width: 100%; font-size: 12px; border-collapse: collapse;">';
    $html .= '<thead><tr style="background: #f8f9fa;">';
    $html .= '<th style="padding: 6px; text-align: center;">N°</th>';
    $html .= '<th style="padding: 6px; text-align: left;">Mes</th>';
    $html .= '<th style="padding: 6px; text-align: right;">Monto</th>';
    $html .= '<th style="padding: 6px; text-align: center;">Días</th>';
    $html .= '<tr></thead><tbody>';

    foreach ($cuotas as $cuota) {
        $numero = isset($cuota['numero']) ? $cuota['numero'] : '?';
        $mesAnio = isset($cuota['mes_anio']) ? htmlspecialchars($cuota['mes_anio']) : '?';
        $monto = isset($cuota['monto']) ? $cuota['monto'] : 0;
        $diasVencidos = isset($cuota['dias_vencidos']) ? $cuota['dias_vencidos'] : null;
        $diasRestantes = isset($cuota['dias_restantes']) ? $cuota['dias_restantes'] : null;

        if ($diasVencidos !== null) {
            $diasText = '-' . floor($diasVencidos) . ' días';
            $color = '#dc3545';
        } elseif ($diasRestantes !== null) {
            $diasText = floor($diasRestantes) . ' días';
            $color = '#6f42c1';  // Purple for urgent
        } else {
            $diasText = 'N/A';
            $color = '#6c757d';
        }

        $html .= '<tr style="border-bottom: 1px solid #eee;">';
        $html .= '<td style="padding: 5px; text-align: center; font-weight: bold;">' . $numero . '</td>';
        $html .= '<td style="padding: 5px; text-align: left;">' . $mesAnio . '</td>';
        $html .= '<td style="padding: 5px; text-align: right; color: #dc3545;">$' . number_format($monto, 2) . '</td>';
        $html .= '<td style="padding: 5px; text-align: center; color: ' . $color . ';">' . $diasText . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody><tr></div>';
    $html .= '<div style="border-top: 1px solid #dee2e6; margin-top: 8px; padding-top: 8px; text-align: right; font-weight: bold;">';
    $html .= 'Total: $' . number_format($total, 2) . '</div>';

    if ($type == 'critical') {
        $html .= '<div style="margin-top: 8px; padding: 6px; background: #f8d7da; color: #dc3545; font-size: 11px; border-radius: 4px;">';
        $html .= '⚠️ Contactar inmediatamente para regularizar pagos.</div>';
    } elseif ($type == 'grace') {
        $html .= '<div style="margin-top: 8px; padding: 6px; background: #fff3e0; color: #fd7e14; font-size: 11px; border-radius: 4px;">';
        $html .= '⏰ Última oportunidad antes de suspensión.</div>';
    } elseif ($type == 'urgent') {
        $html .= '<div style="margin-top: 8px; padding: 6px; background: #e9d8fd; color: #6f42c1; font-size: 11px; border-radius: 4px;">';
        $html .= '🚨 Enviar recordatorio urgente vía WhatsApp.</div>';
    }

    $html .= '</div>';
    return $html;
}

$css = "
.stat-card {
    border-radius: 8px;
    padding: 10px 8px;
    margin-bottom: 20px;
    text-align: center;
    cursor: help;
    transition: all 0.2s;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.stat-card i {
    font-size: 20px;
}
.stat-critical { background: linear-gradient(135deg, #dc3545, #c82333); color: white; }
.stat-grace { background: linear-gradient(135deg, #fd7e14, #e06a10); color: white; }
.stat-urgent { background: linear-gradient(135deg, #6f42c1, #5538a8); color: white; }
.stat-total { background: linear-gradient(135deg, #28a745, #218838); color: white; }
.stat-number { font-size: 24px; font-weight: bold; margin: 5px 0; line-height: 1.2; }
.stat-label { font-size: 11px; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px; }
.stat-tooltip { font-size: 9px; margin-top: 4px; opacity: 0.8; }

.filter-bar {
    background: #f8f9fa;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}
.filter-form {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 15px;
}
.filter-group {
    display: flex;
    flex-direction: column;
}
.filter-group label {
    font-size: 12px;
    margin-bottom: 4px;
    color: #6c757d;
    font-weight: 500;
}
.filter-group select, .filter-group input {
    min-width: 180px;
}
.filter-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.table-cuota-critical { background-color: #f8d7da !important; border-left: 4px solid #dc3545; }
.table-cuota-grace { background-color: #fff3e0 !important; border-left: 4px solid #fd7e14; }
.table-cuota-urgent { background-color: #e9d8fd !important; border-left: 4px solid #6f42c1; }

.center-cell {
    text-align: center !important;
    vertical-align: middle !important;
}

.affiliate-cell {
    text-align: center !important;
    vertical-align: middle !important;
    min-width: 200px;
}

.contact-cell {
    min-width: 240px;
    padding: 12px 8px !important;
}
.contact-buttons {
    display: flex;
    gap: 6px;
    justify-content: center;
    margin-bottom: 8px;
}
.contact-btn {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}
.contact-btn:hover {
    transform: scale(1.05);
    opacity: 0.9;
}
.contact-btn i {
    font-size: 14px;
    margin: 0;
}
.btn-phone { background: #25D366; color: white; }
.btn-whatsapp { background: #25D366; color: white; }
.btn-email { background: #007bff; color: white; }
.btn-contract { background: #17a2b8; color: white; }

.contact-info-text {
    font-size: 12px;
    line-height: 1.5;
    text-align: center;
}
.contact-info-text .phone {
    color: #28a745;
    font-weight: 600;
    font-size: 13px;
    display: block;
    background: #e8f5e9;
    padding: 3px 8px;
    border-radius: 15px;
    margin: 2px 0;
}
.contact-info-text .email {
    color: #007bff;
    font-weight: 600;
    font-size: 12px;
    display: block;
    word-break: break-all;
    background: #e3f2fd;
    padding: 3px 8px;
    border-radius: 15px;
    margin: 2px 0;
}

.amount-total {
    font-size: 18px;
    font-weight: bold;
    color: #dc3545;
}
.badge-with-tooltip {
    cursor: pointer;
}
.badge-with-tooltip:hover {
    transform: scale(1.02);
    filter: brightness(0.95);
}
.table-responsive-scroll {
    overflow-x: auto;
    display: block;
    width: 100%;
}
.tooltip-inner {
    max-width: 400px !important;
    text-align: left !important;
    padding: 0 !important;
    background: white !important;
    color: #333 !important;
    border: 1px solid #dee2e6;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
";
$this->registerCss($css);
?>

<div class="cuotas-seguimiento">
    <div class="row mb-3">
        <div class="col-md-8">
            <h1><i class="fas fa-credit-card"></i> <?= Html::encode($this->title) ?></h1>
            <p class="text-muted">
                <i class="fas fa-chart-line"></i>
                Gestión de cuotas vencidas y próximas a vencer
            </p>
        </div>
        <div class="col-md-4 text-right">
            <?= Html::a(
                '<i class="fas fa-download"></i> Exportar CSV',
                Url::to(['reporte-ventas/exportar-csv', 'filtro' => $filtro, 'clinica_id' => $clinicaId, 'search' => $search]),
                ['class' => 'btn btn-success btn-sm']
            ) ?>
        </div>
    </div>

    <!-- STAT CARDS -->
    <div class="row">
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="stat-card stat-critical" data-toggle="tooltip" data-placement="top" title="Afiliados con cuotas vencidas hace MÁS de 7 días">
                <i class="fas fa-exclamation-triangle"></i>
                <div class="stat-number"><?= isset($stats['critical_count']) ? $stats['critical_count'] : 0 ?></div>
                <div class="stat-label">Críticas</div>
                <div class="stat-tooltip">&gt;7 días</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="stat-card stat-grace" data-toggle="tooltip" data-placement="top" title="Afiliados con cuotas en período de gracia (0-7 días vencidas)">
                <i class="fas fa-clock"></i>
                <div class="stat-number"><?= isset($stats['grace_count']) ? $stats['grace_count'] : 0 ?></div>
                <div class="stat-label">En Gracia</div>
                <div class="stat-tooltip">0-7 días</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="stat-card stat-urgent" data-toggle="tooltip" data-placement="top" title="Afiliados con cuotas por vencer en 3 días o menos">
                <i class="fas fa-bell"></i>
                <div class="stat-number"><?= isset($stats['urgent_count']) ? $stats['urgent_count'] : 0 ?></div>
                <div class="stat-label">Por Vencer</div>
                <div class="stat-tooltip">≤3 días</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="stat-card stat-total" data-toggle="tooltip" data-placement="top" title="Total de afiliados con adeudos">
                <i class="fas fa-users"></i>
                <div class="stat-number"><?= isset($stats['total_afiliados']) ? $stats['total_afiliados'] : 0 ?></div>
                <div class="stat-label">Afiliados con Adeudos</div>
                <div class="stat-tooltip">Total</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="stat-card stat-total" data-toggle="tooltip" data-placement="top" title="Monto total de cuotas VENCIDAS (no incluye pendientes)">
                <i class="fas fa-dollar-sign"></i>
                <div class="stat-number">$<?= isset($stats['total_adeudado']) ? number_format($stats['total_adeudado'], 0) : 0 ?></div>
                <div class="stat-label">Total Vencido</div>
                <div class="stat-tooltip">Solo cuotas vencidas</div>
            </div>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="filter-bar">
        <?php $form = ActiveForm::begin([
            'method' => 'get',
            'action' => Url::to(['reporte-ventas/cuotas-seguimiento']),
            'options' => ['class' => 'filter-form', 'id' => 'filter-form']
        ]); ?>

        <div class="filter-group">
            <label>Filtro Rápido</label>
            <?= Html::dropDownList('filtro', $filtro, [
                'todas' => '📋 Todos los afiliados con adeudos',
                'vencidas' => '🔴 Con cuotas vencidas (Críticas + En Gracia)',
                'en_gracia' => '🟠 En Gracia (0-7 días vencidas)',
                'por_vencer' => '🟣 Por vencer (3 días)',
            ], ['class' => 'form-control form-control-sm', 'onchange' => 'this.form.submit()']) ?>
        </div>

        <?php if (!empty($clinicas)): ?>
            <div class="filter-group">
                <label>Clínica</label>
                <?= Html::dropDownList(
                    'clinica_id',
                    $clinicaId,
                    \yii\helpers\ArrayHelper::map($clinicas, 'id', 'nombre'),
                    ['prompt' => 'Todas las clínicas', 'class' => 'form-control form-control-sm', 'onchange' => 'this.form.submit()']
                ) ?>
            </div>
        <?php endif; ?>

        <div class="filter-group">
            <label>Buscar</label>
            <?= Html::textInput('search', $search, [
                'class' => 'form-control form-control-sm',
                'placeholder' => 'Nombre, cédula o contrato...',
                'style' => 'min-width: 200px;'
            ]) ?>
        </div>

        <div class="filter-actions">
            <?= Html::submitButton('<i class="fas fa-search"></i> Buscar', ['class' => 'btn btn-primary btn-sm']) ?>
            <?php if (!empty($search) || $filtro != 'todas' || (!empty($clinicaId) && $clinicaId != '')): ?>
                <?= Html::a('Limpiar', Url::to(['reporte-ventas/cuotas-seguimiento']), ['class' => 'btn btn-secondary btn-sm']) ?>
            <?php endif; ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

    <div class="card">
        <div class="card-header">
            <strong><i class="fas fa-list"></i> Afiliados con Adeudos</strong>
            <span class="badge badge-info float-right">Total: <?= $dataProvider->totalCount ?> afiliados</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive-scroll">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'tableOptions' => ['class' => 'table table-hover mb-0', 'style' => 'min-width: 1000px;'],
                    'rowOptions' => function ($model) {
                        $class = '';
                        if (isset($model['priority'])) {
                            if ($model['priority'] == 'critical') $class = 'table-cuota-critical';
                            elseif ($model['priority'] == 'grace') $class = 'table-cuota-grace';
                            elseif ($model['priority'] == 'urgent') $class = 'table-cuota-urgent';
                        }
                        return ['class' => $class];
                    },
                    'columns' => [
                        [
                            'class' => 'yii\grid\SerialColumn',
                            'header' => '#',
                            'headerOptions' => ['style' => 'width: 50px;', 'class' => 'center-cell'],
                            'contentOptions' => ['class' => 'center-cell'],
                        ],
                        [
                            'attribute' => 'user_nombres',
                            'label' => 'Afiliado',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 220px;', 'class' => 'center-cell'],
                            'contentOptions' => ['class' => 'affiliate-cell'],
                            'value' => function ($model) {
                                $nombre = isset($model['user_nombres']) ? Html::encode($model['user_nombres'] . ' ' . $model['user_apellidos']) : 'N/A';
                                $cedula = (isset($model['tipo_cedula']) ? $model['tipo_cedula'] : 'V') . '-' . (isset($model['cedula']) ? $model['cedula'] : 'N/A');
                                $userId = isset($model['user_id']) ? $model['user_id'] : 0;
                                return Html::a($nombre, ['/user-datos/view', 'id' => $userId], [
                                    'target' => '_blank',
                                    'data-pjax' => 0,
                                    'class' => 'font-weight-bold'
                                ]) . '<br><small class="text-muted">' . $cedula . '</small>';
                            },
                        ],
                        [
                            'attribute' => 'clinica_nombre',
                            'label' => 'Clínica / Plan',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 160px;', 'class' => 'center-cell'],
                            'contentOptions' => ['class' => 'center-cell'],
                            'value' => function ($model) {
                                $clinica = isset($model['clinica_nombre']) ? Html::encode($model['clinica_nombre']) : 'N/A';
                                $plan = isset($model['plan_nombre']) ? Html::encode($model['plan_nombre']) : 'N/A';
                                return '<strong>' . $clinica . '</strong><br><small class="text-muted">' . $plan . '</small>';
                            },
                        ],
                        [
                            'label' => 'Cuotas Afectadas',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 140px;', 'class' => 'center-cell'],
                            'contentOptions' => ['class' => 'center-cell'],
                            'value' => function ($model) {
                                $badges = [];

                                // Critical badge (RED)
                                if (isset($model['criticas_count']) && $model['criticas_count'] > 0 && !empty($model['criticas_list'])) {
                                    $tooltipContent = renderCuotaTooltip(
                                        $model['criticas_list'],
                                        '🔴 CUOTAS CRÍTICAS (>7 días vencidas)',
                                        'critical',
                                        array_sum(array_column($model['criticas_list'], 'monto'))
                                    );
                                    $badges[] = '<span class="badge badge-danger badge-with-tooltip" 
                                        data-toggle="tooltip" 
                                        data-html="true" 
                                        data-placement="top"
                                        title="' . htmlspecialchars($tooltipContent, ENT_QUOTES) . '"
                                        style="cursor: pointer; display: inline-block; margin: 2px;">
                                        <i class="fas fa-skull-crosswalk"></i> ' . $model['criticas_count'] . ' Críticas
                                    </span>';
                                }

                                // Grace badge (ORANGE)
                                if (isset($model['gracia_count']) && $model['gracia_count'] > 0 && !empty($model['gracia_list'])) {
                                    $tooltipContent = renderCuotaTooltip(
                                        $model['gracia_list'],
                                        '🟠 CUOTAS EN GRACIA (0-7 días vencidas)',
                                        'grace',
                                        array_sum(array_column($model['gracia_list'], 'monto'))
                                    );
                                    $badges[] = '<span class="badge badge-warning badge-with-tooltip" 
                                        style="background-color: #fd7e14 !important; color: white; cursor: pointer; display: inline-block; margin: 2px;"
                                        data-toggle="tooltip" 
                                        data-html="true" 
                                        data-placement="top"
                                        title="' . htmlspecialchars($tooltipContent, ENT_QUOTES) . '">
                                        <i class="fas fa-hourglass-half"></i> ' . $model['gracia_count'] . ' En Gracia
                                    </span>';
                                }

                                // Urgent badge (PURPLE) - Por Vencer
                                if (isset($model['por_vencer_3d_count']) && $model['por_vencer_3d_count'] > 0 && !empty($model['por_vencer_3d_list'])) {
                                    $tooltipContent = renderCuotaTooltip(
                                        $model['por_vencer_3d_list'],
                                        '🟣 CUOTAS POR VENCER (≤3 días)',
                                        'urgent',
                                        array_sum(array_column($model['por_vencer_3d_list'], 'monto'))
                                    );
                                    $badges[] = '<span class="badge badge-with-tooltip" 
                                        style="background-color: #6f42c1 !important; color: white; cursor: pointer; display: inline-block; margin: 2px;"
                                        data-toggle="tooltip" 
                                        data-html="true" 
                                        data-placement="top"
                                        title="' . htmlspecialchars($tooltipContent, ENT_QUOTES) . '">
                                        <i class="fas fa-bell"></i> ' . $model['por_vencer_3d_count'] . ' ≤3d
                                    </span>';
                                }

                                // Warning badge (4-7 days) - if you have this
                                if (isset($model['por_vencer_7d_count']) && $model['por_vencer_7d_count'] > 0 && !empty($model['por_vencer_7d_list'])) {
                                    $tooltipContent = renderCuotaTooltip(
                                        $model['por_vencer_7d_list'],
                                        '📆 CUOTAS POR VENCER (4-7 días)',
                                        'warning',
                                        array_sum(array_column($model['por_vencer_7d_list'], 'monto'))
                                    );
                                    $badges[] = '<span class="badge badge-secondary badge-with-tooltip" 
                                        data-toggle="tooltip" 
                                        data-html="true" 
                                        data-placement="top"
                                        title="' . htmlspecialchars($tooltipContent, ENT_QUOTES) . '"
                                        style="cursor: pointer; display: inline-block; margin: 2px;">
                                        <i class="fas fa-calendar-week"></i> ' . $model['por_vencer_7d_count'] . ' 4-7d
                                    </span>';
                                }

                                return !empty($badges) ? implode(' ', $badges) : '<span class="text-muted">Sin cuotas afectadas</span>';
                            },
                        ],
                        [
                            'attribute' => 'total_adeudado',
                            'label' => 'Total Vencido (USD)',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 130px;', 'class' => 'center-cell'],
                            'contentOptions' => ['class' => 'center-cell'],
                            'value' => function ($model) {
                                $monto = isset($model['total_adeudado']) ? $model['total_adeudado'] : 0;
                                return '<span class="amount-total">$' . number_format($monto, 2) . '</span>';
                            },
                        ],
                        [
                            'label' => 'Contacto',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 220px;', 'class' => 'center-cell'],
                            'contentOptions' => ['class' => 'contact-cell text-center'],
                            'value' => function ($model) {
                                $buttons = '<div class="contact-buttons">';

                                if (!empty($model['telefono']) && $model['telefono'] != 'N/A') {
                                    $buttons .= Html::a('<i class="fas fa-phone"></i>', 'tel:' . $model['telefono'], [
                                        'class' => 'contact-btn btn-phone',
                                        'data-toggle' => 'tooltip',
                                        'title' => 'Llamar'
                                    ]);
                                    $buttons .= Html::a('<i class="fab fa-whatsapp"></i>', 'https://wa.me/' . preg_replace('/[^0-9]/', '', $model['telefono']), [
                                        'class' => 'contact-btn btn-whatsapp',
                                        'data-toggle' => 'tooltip',
                                        'title' => 'WhatsApp',
                                        'target' => '_blank'
                                    ]);
                                }

                                if (!empty($model['email']) && $model['email'] != 'N/A') {
                                    $buttons .= Html::a('<i class="fas fa-envelope"></i>', 'mailto:' . $model['email'], [
                                        'class' => 'contact-btn btn-email',
                                        'data-toggle' => 'tooltip',
                                        'title' => 'Enviar Email'
                                    ]);
                                }

                                $buttons .= Html::a('<i class="fas fa-file-contract"></i>', ['/contratos/view', 'id' => isset($model['contrato_id']) ? $model['contrato_id'] : 0], [
                                    'class' => 'contact-btn btn-contract',
                                    'data-toggle' => 'tooltip',
                                    'title' => 'Ver Contrato',
                                    'target' => '_blank'
                                ]);

                                $buttons .= '</div>';

                                $contactInfo = '<div class="contact-info-text">';
                                if (!empty($model['telefono']) && $model['telefono'] != 'N/A') {
                                    $contactInfo .= '<span class="phone"><i class="fas fa-phone-alt"></i> ' . Html::encode($model['telefono']) . '</span>';
                                }
                                if (!empty($model['email']) && $model['email'] != 'N/A') {
                                    $contactInfo .= '<span class="email"><i class="fas fa-envelope"></i> ' . Html::encode($model['email']) . '</span>';
                                }
                                $contactInfo .= '</div>';

                                return $buttons . $contactInfo;
                            },
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="alert alert-info" style="font-size: 12px; padding: 10px;">
                <i class="fas fa-info-circle"></i>
                <strong>Guía rápida:</strong>
                Pase el mouse sobre las etiquetas 🔴 Críticas, 🟠 En Gracia, 🟣 Por Vencer para ver detalles de cada cuota.
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<JS
$(function() {
    $('[data-toggle="tooltip"]').tooltip({
        html: true,
        trigger: 'hover',
        delay: { show: 300, hide: 100 },
        container: 'body'
    });
});
JS;
$this->registerJs($js, View::POS_READY);
?>