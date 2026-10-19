<?php

/**
 * @var yii\web\View $this
 * @var string $clinicaNombre
 * @var array $contratos
 * @var int $days
 * @var string $fromDate
 * @var string $toDate
 */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Contratos por Vencer';
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['site/dashboard-coordinador']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/kpi-cards.css', [
    'depends' => [\yii\bootstrap4\BootstrapAsset::class],
]);
?>

<div class="contratos-por-vencer">
    <style>
        :root {
            --cpv-primary: #2c3e50;
            --cpv-primary-dark: #1a2632;
            --cpv-warning: #f59e0b;
            --cpv-danger: #ef4444;
            --cpv-success: #10b981;
            --cpv-info: #3b82f6;
            --cpv-light: #f1f5f9;
        }

        .contratos-por-vencer {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            min-height: 100vh;
            padding: 2rem;
        }

        .cpv-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* ════════════════════════════════════════════════════════════ */
        /* HEADER — every text node re-asserts color:white !important   */
        /* -webkit-text-fill-color is added as a belt-and-suspenders    */
        /* fix so no inherited color can ever win, on any browser.      */
        /* ════════════════════════════════════════════════════════════ */
        .contratos-por-vencer .cpv-header {
            background: linear-gradient(135deg, var(--cpv-primary) 0%, var(--cpv-primary-dark) 100%) !important;
            border-radius: 24px;
            padding: 2rem 2.25rem;
            margin-bottom: 1.75rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px -8px rgba(15, 23, 42, 0.25);
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }

        .contratos-por-vencer .cpv-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 320px;
            height: 100%;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.12) 0%, rgba(245, 158, 11, 0.28) 100%);
            clip-path: polygon(25% 0%, 100% 0%, 100% 100%, 0% 100%);
            pointer-events: none;
        }

        .contratos-por-vencer .cpv-header__content {
            position: relative;
            z-index: 1;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }

        .contratos-por-vencer .cpv-header h1 {
            font-size: 2.1rem;
            font-weight: 700;
            line-height: 1.2;
            margin: 0 0 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 14px;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
        }

        .contratos-por-vencer .cpv-header h1 i {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            opacity: 0.95;
        }

        .contratos-por-vencer .cpv-header p {
            font-size: 1.1rem;
            line-height: 1.45;
            margin: 0;
            color: rgba(255, 255, 255, 0.92) !important;
            -webkit-text-fill-color: rgba(255, 255, 255, 0.92) !important;
        }

        .contratos-por-vencer .cpv-header p strong {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            font-weight: 700;
        }

        .contratos-por-vencer .cpv-header__badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
            margin-top: 1rem;
        }

        .contratos-por-vencer .cpv-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0.55rem 1.1rem;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.25);
            font-size: 1rem;
            font-weight: 500;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }

        .contratos-por-vencer .cpv-badge i {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }

        /* ── Summary strip ── */
        .cpv-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .cpv-stat {
            background: #fff;
            border-radius: 16px;
            padding: 1.35rem 1.5rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06), 0 2px 8px rgba(15, 23, 42, 0.04);
            border-left: 4px solid var(--cpv-warning);
            transition: all 0.2s ease;
        }

        .cpv-stat:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -8px rgba(0, 0, 0, 0.12);
        }

        .cpv-stat--critical {
            border-left-color: var(--cpv-danger);
        }

        .cpv-stat--warning {
            border-left-color: var(--cpv-warning);
        }

        .cpv-stat--notice {
            border-left-color: var(--cpv-info);
        }

        .cpv-stat__label {
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 0.35rem;
        }

        .cpv-stat__value {
            font-size: 2rem;
            font-weight: 800;
            color: #1e293b;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }

        .cpv-stat__hint {
            font-size: 0.9rem;
            color: #94a3b8;
            margin-top: 0.4rem;
        }

        /* ── Panel ── */
        .cpv-panel {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
            overflow: hidden;
        }

        .cpv-panel__header {
            padding: 1.35rem 1.75rem;
            background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 50%, #a78bfa 100%);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }

        .cpv-panel__header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }

        .cpv-panel__header h3 i {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }

        .cpv-panel__header h3>span {
            background: rgba(255, 255, 255, 0.22);
            padding: 4px 14px;
            border-radius: 12px;
            font-size: 1rem;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }

        .cpv-panel__body {
            padding: 0;
            overflow-x: auto;
        }

        /* ── Table ── */
        .cpv-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 1.05rem;
            margin: 0;
        }

        .cpv-table thead tr {
            background: #f1f5f9 !important;
        }

        .cpv-table thead th {
            background: #f1f5f9 !important;
            color: #1e293b !important;
            font-size: 0.85rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.7px !important;
            padding: 16px 20px !important;
            text-align: left !important;
            border-bottom: 2px solid #cbd5e1 !important;
            white-space: nowrap;
        }

        .cpv-table tbody tr {
            background: #fff;
            transition: background 0.15s ease;
            border-bottom: 1px solid #f1f5f9;
        }

        .cpv-table tbody tr:hover {
            background: #f8fafc;
        }

        .cpv-table tbody td {
            padding: 18px 20px;
            vertical-align: middle;
            color: #1e293b;
        }

        .cpv-row--critical td:first-child {
            box-shadow: inset 4px 0 0 var(--cpv-danger);
        }

        .cpv-row--warning td:first-child {
            box-shadow: inset 4px 0 0 var(--cpv-warning);
        }

        .cpv-row--notice td:first-child {
            box-shadow: inset 4px 0 0 var(--cpv-info);
        }

        .cpv-name {
            font-weight: 700;
            font-size: 1.1rem;
            color: #1e293b;
        }

        .cpv-sub {
            font-size: 0.88rem;
            color: #94a3b8;
            margin-top: 3px;
        }

        .cpv-days {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 10px;
            font-weight: 800;
            font-size: 1.05rem;
            font-variant-numeric: tabular-nums;
        }

        .cpv-days--critical {
            background: #fee2e2;
            color: #b91c1c;
        }

        .cpv-days--warning {
            background: #fef3c7;
            color: #b45309;
        }

        .cpv-days--notice {
            background: #e0f2fe;
            color: #075985;
        }

        .cpv-empty {
            text-align: center;
            padding: 4rem 2rem;
            color: #94a3b8;
        }

        .cpv-empty i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.4;
            display: block;
        }

        .cpv-empty h4 {
            color: #475569;
            font-size: 1.4rem;
            margin-bottom: 0.5rem;
        }

        /* ── Back button ── */
        .cpv-back {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 0.85rem 1.5rem;
            margin-top: 1.75rem;
            background: #fff;
            color: #334155 !important;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none !important;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
        }

        .cpv-back:hover {
            background: #f8fafc;
            transform: translateX(-3px);
            color: #1e293b !important;
        }

        .cpv-back i {
            color: inherit !important;
        }

        @media (max-width: 768px) {
            .contratos-por-vencer {
                padding: 1rem;
            }

            .contratos-por-vencer .cpv-header {
                padding: 1.5rem;
            }

            .contratos-por-vencer .cpv-header h1 {
                font-size: 1.6rem;
            }

            .cpv-table tbody td {
                padding: 14px 12px;
                font-size: 0.95rem;
            }

            .cpv-table thead th {
                padding: 12px 12px !important;
                font-size: 0.75rem !important;
            }
        }
    </style>

    <div class="cpv-container">

        <!-- Header -->
        <div class="cpv-header">
            <div class="cpv-header__content">
                <h1>
                    <i class="fas fa-hourglass-half"></i>
                    Contratos por Vencer
                </h1>
                <p>Contratos que vencen en los próximos <strong><?= (int)$days ?> días</strong></p>
                <div class="cpv-header__badges">
                    <span class="cpv-badge">
                        <i class="fas fa-hospital"></i>
                        <?= Html::encode($clinicaNombre) ?>
                    </span>
                    <span class="cpv-badge">
                        <i class="far fa-calendar-alt"></i>
                        <?= Yii::$app->formatter->asDate($fromDate, 'dd/MM/yyyy') ?>
                        &rarr;
                        <?= Yii::$app->formatter->asDate($toDate, 'dd/MM/yyyy') ?>
                    </span>
                </div>
            </div>
        </div>

        <?php
        $critical = 0;
        $warning = 0;
        $notice = 0;
        foreach ($contratos as $c) {
            if ($c['urgency'] === 'critical') $critical++;
            elseif ($c['urgency'] === 'warning') $warning++;
            else $notice++;
        }
        ?>

        <!-- Summary strip -->
        <div class="cpv-summary">
            <div class="cpv-stat">
                <div class="cpv-stat__label">Total</div>
                <div class="cpv-stat__value"><?= number_format(count($contratos)) ?></div>
                <div class="cpv-stat__hint">Contratos por vencer</div>
            </div>
            <div class="cpv-stat cpv-stat--critical">
                <div class="cpv-stat__label"><i class="fas fa-bolt"></i> 0–3 días</div>
                <div class="cpv-stat__value"><?= number_format($critical) ?></div>
                <div class="cpv-stat__hint">Urgencia crítica</div>
            </div>
            <div class="cpv-stat cpv-stat--warning">
                <div class="cpv-stat__label"><i class="fas fa-exclamation-circle"></i> 4–7 días</div>
                <div class="cpv-stat__value"><?= number_format($warning) ?></div>
                <div class="cpv-stat__hint">Requieren seguimiento</div>
            </div>
            <div class="cpv-stat cpv-stat--notice">
                <div class="cpv-stat__label"><i class="fas fa-clock"></i> 8–<?= (int)$days ?> días</div>
                <div class="cpv-stat__value"><?= number_format($notice) ?></div>
                <div class="cpv-stat__hint">Aún con margen</div>
            </div>
        </div>

        <!-- Panel -->
        <div class="cpv-panel">
            <div class="cpv-panel__header">
                <h3>
                    <i class="fas fa-list-ul"></i>
                    Listado de Contratos
                    <span style="background: rgba(255,255,255,0.22); padding: 4px 14px; border-radius: 12px; font-size: 1rem;">
                        <?= number_format(count($contratos)) ?>
                    </span>
                </h3>
            </div>

            <div class="cpv-panel__body">
                <?php if (empty($contratos)): ?>
                    <div class="cpv-empty">
                        <i class="far fa-check-circle"></i>
                        <h4>¡Todo en orden!</h4>
                        <p>No hay contratos que venzan en los próximos <?= (int)$days ?> días.</p>
                    </div>
                <?php else: ?>
                    <table class="cpv-table table">
                        <thead>
                            <tr>
                                <th>Afiliado</th>
                                <th>Plan</th>
                                <th>Contrato</th>
                                <th>Fecha Vencimiento</th>
                                <th>Días Restantes</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contratos as $c): ?>
                                <tr class="cpv-row--<?= $c['urgency'] ?>">
                                    <td>
                                        <div class="cpv-name">
                                            <?= Html::encode($c['nombres'] . ' ' . $c['apellidos']) ?>
                                        </div>
                                        <div class="cpv-sub">
                                            <i class="fas fa-id-card"></i>
                                            <?= Html::encode($c['tipo_cedula'] . '-' . $c['cedula']) ?>
                                            <?php if (!empty($c['telefono'])): ?>
                                                &nbsp;·&nbsp;
                                                <i class="fas fa-phone"></i>
                                                <?= Html::encode($c['telefono']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?= Html::encode($c['plan_nombre'] ?: '—') ?></td>
                                    <td>
                                        <code style="background:#f1f5f9; padding:3px 10px; border-radius:6px; color:#475569; font-size:0.9rem;">
                                            <?= Html::encode($c['nrocontrato'] ?: '—') ?>
                                        </code>
                                    </td>
                                    <td>
                                        <?= Yii::$app->formatter->asDate($c['fecha_ven'], 'dd/MM/yyyy') ?>
                                    </td>
                                    <td>
                                        <?php if ($c['dias_restantes'] === 0): ?>
                                            <span class="cpv-days cpv-days--critical">
                                                <i class="fas fa-bolt"></i> HOY
                                            </span>
                                        <?php else: ?>
                                            <span class="cpv-days cpv-days--<?= $c['urgency'] ?>">
                                                <?= $c['dias_restantes'] ?>
                                                día<?= $c['dias_restantes'] === 1 ? '' : 's' ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?= Html::a(
                                            '<i class="fas fa-eye"></i> Ver Afiliado',
                                            ['user-datos/view', 'id' => $c['user_id']],
                                            ['class' => 'btn btn-sm btn-outline-primary', 'style' => 'border-radius: 8px; font-weight: 600;']
                                        ) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <?= Html::a(
            '<i class="fas fa-arrow-left"></i> Volver al Dashboard',
            ['site/dashboard-coordinador'],
            ['class' => 'cpv-back']
        ) ?>

    </div>
</div>