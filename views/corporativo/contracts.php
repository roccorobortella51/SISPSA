<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ContractHelper;
use app\models\Contratos;
use app\models\ContratoCorporativo;
use app\models\ContratoCorporativoAfiliado;
use app\models\UserDatos;
use yii\db\Query;

/** @var yii\web\View $this */
/** @var app\models\Corporativo $corporativo */
/** @var array $contractGroups */
/** @var bool $hasContractThisYear */
/** @var int $currentYear */
/** @var int $activeCount */
/** @var int $annulledCount */

// ── Compatibility shim: tolerate either $corporativo or $model ────────
if (!isset($corporativo) && isset($model)) {
    $corporativo = $model;
}
if (!isset($model) && isset($corporativo)) {
    $model = $corporativo;
}

$this->title = 'Contratos Corporativos — ' . $corporativo->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Corporativos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $corporativo->nombre, 'url' => ['view', 'id' => $corporativo->id]];
$this->params['breadcrumbs'][] = 'Contratos';

// ─────────────────────────────────────────────────────────────────────
// Per-plan affiliate counts (used for the plan-chip badges).
//
// These are SEPARATE from $activeCount / $annulledCount (which are
// corporate-wide and come from the controller, matching the KPI on
// corporate/index.php). The chip count is scoped to a single contract
// row's plan and only counts affiliates with a live contract.
// ─────────────────────────────────────────────────────────────────────
foreach ($contractGroups as &$group) {
    if (
        !empty($group['is_annulled'])
        || ContractHelper::getCorporateContractRemainingMeta($group['anchor'])['state'] === 'expired'
    ) {
        continue;
    }

    $planCounts = [];

    foreach ($group['rows'] as $contractRow) {
        $affiliateCount = (new Query())
            ->from(['cca' => ContratoCorporativoAfiliado::tableName()])
            ->innerJoin(['affiliate' => UserDatos::tableName()], 'affiliate.id = cca.user_datos_id')
            ->leftJoin(['individual_contract' => Contratos::tableName()], 'individual_contract.id = cca.contrato_individual_id')
            ->where(['cca.contrato_corporativo_id' => $contractRow->id])
            ->andWhere([
                'affiliate.afiliado_corporativo_id' => $corporativo->id,
                'affiliate.user_datos_type_id' => 2,
            ])
            ->andWhere([
                'or',
                ['individual_contract.id' => null],
                ['<>', 'individual_contract.estatus', Contratos::STATUS_ANULADO],
            ])
            ->count('DISTINCT affiliate.id');

        if ($contractRow->plan) {
            $planName = $contractRow->plan->nombre;
            $planCounts[$planName] = ($planCounts[$planName] ?? 0) + (int)$affiliateCount;
        }
    }

    $group['plan_counts'] = $planCounts;
}
unset($group);

// ── Smart "new contract" action — business rule lives in the helper ──
$newContractAction = ContractHelper::resolveNewContractAction($contractGroups, $currentYear);

/**
 * ─────────────────────────────────────────────────────────────────
 *  Enriched stats — computed once, consumed by the cards below.
 * ─────────────────────────────────────────────────────────────────
 */
$stats = [
    'total_contracts'    => 0,
    'active_contracts'   => 0,
    'expiring_soon'      => 0,
    'expired_contracts'  => 0,
    'annulled_contracts' => 0,
    'active_monthly'     => 0.0,
];

foreach ($contractGroups as $group) {
    $stats['total_contracts']++;

    if (!empty($group['is_annulled'])) {
        $stats['annulled_contracts']++;
        continue;
    }

    $meta = ContractHelper::getCorporateContractRemainingMeta($group['anchor']);

    if ($meta['state'] === 'expired') {
        $stats['expired_contracts']++;
        continue;
    }

    $stats['active_contracts']++;
    $stats['active_monthly'] += (float)($group['total_amount'] ?? 0);

    if (
        $meta['state'] === 'active'
        && $meta['days'] !== null
        && $meta['days'] <= ContractHelper::RENEWAL_WINDOW_DAYS
    ) {
        $stats['expiring_soon']++;
    }
}
?>

<style>
    /* ══════════════════════════════════════════════════════════════ */
    /* CORPORATE CONTRACTS PAGE                                       */
    /* ══════════════════════════════════════════════════════════════ */
    .corp-page {
        padding: 24px;
        max-width: 1500px;
        margin: 0 auto;
        font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
        color: #323130;
        font-size: 17px;
        line-height: 1.6;
    }

    /* ─── Header ──────────────────────────────────────────────── */
    .corp-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 24px;
        padding: 28px 30px;
        background: #fff;
        border: 1px solid #e8e6e3;
        border-radius: 14px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .corp-header__left {
        display: flex;
        align-items: center;
        gap: 22px;
        min-width: 0;
    }

    .corp-header__icon {
        width: 84px;
        height: 84px;
        border-radius: 18px;
        background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 38px;
        box-shadow: 0 6px 18px rgba(220, 38, 38, 0.3);
        flex-shrink: 0;
    }

    .corp-header__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 1.05rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.6px;
        color: #8a8886;
        margin-bottom: 8px;
    }

    .corp-header__title {
        font-size: 2.6rem;
        font-weight: 800;
        color: #1a237e;
        margin: 0 0 12px 0;
        line-height: 1.05;
        letter-spacing: -0.5px;
        word-break: break-word;
    }

    .corp-header__subtitle {
        display: flex;
        align-items: center;
        gap: 14px;
        font-size: 1.2rem;
        color: #605e5c;
        font-weight: 500;
        flex-wrap: wrap;
    }

    .corp-header__badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #f3f2f1;
        color: #605e5c;
        font-size: 1.1rem;
        font-weight: 600;
        padding: 6px 16px;
        border-radius: 12px;
    }

    .corp-header__badge i {
        color: #8a8886;
        font-size: 1.05rem;
    }

    .corp-header__actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
        flex-wrap: wrap;
    }

    /* ─── Buttons ─────────────────────────────────────────────── */
    .btn-corp {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 24px;
        border-radius: 10px;
        font-size: 1.2rem;
        font-weight: 700;
        text-decoration: none;
        border: 2px solid transparent;
        cursor: pointer;
        transition: all 0.15s ease;
        white-space: nowrap;
        line-height: 1.2;
    }

    .btn-corp i {
        font-size: 1.2rem;
    }

    .btn-corp--primary {
        background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        color: #fff;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
    }

    .btn-corp--primary:hover {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.5);
        text-decoration: none;
    }

    .btn-corp--renew {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #fff;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35);
    }

    .btn-corp--renew:hover {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(245, 158, 11, 0.5);
        text-decoration: none;
    }

    .btn-corp--ghost {
        background: #fff;
        color: #4338ca;
        border-color: #c7d2fe;
        box-shadow: 0 2px 6px rgba(79, 70, 229, 0.10);
    }

    .btn-corp--ghost:hover {
        background: #eef2ff;
        color: #312e81;
        border-color: #818cf8;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.25);
        text-decoration: none;
    }

    .btn-corp--disabled {
        background: #ecfdf5;
        color: #047857;
        border-color: #a7f3d0;
        cursor: not-allowed;
        box-shadow: none;
    }

    .btn-corp--disabled i {
        color: #10b981;
    }

    /* ─── Summary tiles ──────────────────────────────────────── */
    .corp-stats {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 18px;
        margin-bottom: 24px;
    }

    @media (max-width: 1200px) {
        .corp-stats {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 992px) {
        .corp-stats {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .corp-stats {
            grid-template-columns: 1fr;
        }
    }

    .stat-card {
        position: relative;
        background: #fff;
        border: 1px solid #e8e6e3;
        border-left: 5px solid #6366f1;
        border-radius: 14px;
        padding: 20px 22px 18px 22px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        display: flex;
        flex-direction: column;
        gap: 10px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
    }

    .stat-card--attention {
        border-left-color: #f59e0b;
        background: linear-gradient(180deg, #fff 0%, #fffbeb 100%);
        box-shadow: 0 4px 14px rgba(245, 158, 11, 0.12);
    }

    .stat-card--indigo {
        border-left-color: #4f46e5;
    }

    .stat-card--green {
        border-left-color: #059669;
    }

    .stat-card--blue {
        border-left-color: #2563eb;
    }

    .stat-card--amber {
        border-left-color: #d97706;
    }

    .stat-card--emerald {
        border-left-color: #10b981;
        background: linear-gradient(180deg, #fff 0%, #f0fdf4 100%);
    }

    .stat-card--rose {
        border-left-color: #e11d48;
        background: linear-gradient(180deg, #fff 0%, #fff1f2 100%);
    }

    .stat-card__header {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .stat-card__icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: #fff;
        flex-shrink: 0;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
    }

    .stat-card--indigo .stat-card__icon {
        background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
    }

    .stat-card--green .stat-card__icon {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .stat-card--blue .stat-card__icon {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    }

    .stat-card--amber .stat-card__icon {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .stat-card--emerald .stat-card__icon {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .stat-card--rose .stat-card__icon {
        background: linear-gradient(135deg, #f43f5e 0%, #be123c 100%);
    }

    .stat-card__title {
        font-size: 0.95rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.9px;
        color: #605e5c;
    }

    .stat-card__value {
        font-size: 2.6rem;
        font-weight: 800;
        color: #1a237e;
        line-height: 1;
        font-variant-numeric: tabular-nums;
        letter-spacing: -1px;
    }

    .stat-card--emerald .stat-card__value {
        color: #047857;
    }

    .stat-card--rose .stat-card__value {
        color: #b91c1c;
    }

    .stat-card__currency {
        font-size: 1.5rem;
        font-weight: 700;
        color: #8a8886;
        margin-right: 2px;
        vertical-align: 8px;
    }

    .stat-card__context {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        min-height: 26px;
    }

    .stat-card__hint {
        font-size: 0.95rem;
        font-weight: 500;
        color: #605e5c;
    }

    .stat-card__hint--muted {
        color: #8a8886;
        font-style: italic;
    }

    .stat-card__badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .stat-card__badge--muted {
        background: #f3f2f1;
        color: #605e5c;
        border: 1px solid #e8e6e3;
    }

    .stat-card__badge--amber {
        background: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    /* ─── Panels ──────────────────────────────────────────────── */
    .corp-panel {
        background: #fff;
        border: 1px solid #e8e6e3;
        border-radius: 14px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        margin-bottom: 24px;
        overflow: hidden;
        transition: box-shadow 0.15s ease;
    }

    .corp-panel:hover {
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.07);
    }

    .corp-panel__title {
        display: flex;
        align-items: center;
        gap: 14px;
        font-size: 1.4rem;
        font-weight: 700;
        color: #fff;
        margin: 0;
        padding: 18px 28px;
        background: linear-gradient(135deg, #1a237e 0%, #0d47a1 50%, #1565c0 100%);
        border-bottom: none;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
    }

    .corp-panel__title i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        font-size: 1.15rem;
        color: #fff;
        background: rgba(255, 255, 255, 0.18);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255, 255, 255, 0.25);
        flex-shrink: 0;
    }

    .corp-panel__title .badge {
        background: rgba(255, 255, 255, 0.22) !important;
        color: #fff !important;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.95rem;
        font-weight: 700;
        margin-left: 8px;
    }

    .corp-panel__body {
        padding: 28px 30px;
    }

    .corp-panel__body--roomy {
        padding: 34px 36px 38px 36px;
    }

    /* Contratos Colectivos — Crimson red panel with tinted body */
    .corp-panel--red {
        border-color: #fecaca;
        box-shadow: 0 4px 14px rgba(220, 38, 38, 0.08);
    }

    .corp-panel--red .corp-panel__title {
        background: linear-gradient(135deg, #991b1b 0%, #dc2626 50%, #ef4444 100%);
    }

    .corp-panel--red .corp-panel__body {
        background: linear-gradient(180deg,
                #fff5f5 0%,
                #fef2f2 40%,
                #fef2f2 100%);
    }

    /* ─── Contracts table ─────────────────────────────────────── */
    .corp-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 1.1rem;
    }

    .corp-table thead th {
        background: linear-gradient(135deg, #1a237e 0%, #0d47a1 50%, #1565c0 100%);
        color: #fff !important;
        padding: 20px 22px;
        text-align: left;
        font-weight: 700;
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        white-space: nowrap;
    }

    .corp-table thead th:first-child {
        border-top-left-radius: 10px;
    }

    .corp-table thead th:last-child {
        border-top-right-radius: 10px;
    }

    .corp-table tbody tr {
        border-bottom: 1px solid #f3f2f1;
        transition: background-color 0.12s ease;
    }

    .corp-table tbody tr:hover {
        background-color: #fafbfc;
    }

    .corp-table tbody tr:last-child {
        border-bottom: none;
    }

    .corp-table tbody tr.corp-table__row--annulled {
        opacity: 0.68;
    }

    .corp-table tbody td {
        padding: 22px 22px;
        vertical-align: middle;
        font-size: 1.1rem;
    }

    .corp-table__contract-no {
        font-family: 'Consolas', 'Monaco', monospace;
        font-weight: 700;
        color: #1a237e;
        font-size: 1.1rem;
        word-break: break-all;
    }

    .corp-table__plans {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .plan-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        color: #4338ca;
        font-size: 1rem;
        font-weight: 700;
        padding: 5px 14px;
        border-radius: 10px;
        border: 1px solid #c7d2fe;
        white-space: nowrap;
    }

    .plan-chip__count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 24px;
        padding: 0 8px;
        background: #4338ca;
        color: #fff;
        font-size: 0.9rem;
        font-weight: 700;
        border-radius: 8px;
        font-variant-numeric: tabular-nums;
    }

    .corp-table__numeric {
        text-align: center;
        font-weight: 700;
        color: #107c10;
        font-variant-numeric: tabular-nums;
    }

    .corp-table__money {
        font-weight: 700;
        color: #107c10;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    /* ── Affiliate count pills ── */
    .corp-table__count {
        display: inline-block;
        min-width: 54px;
        padding: 6px 14px;
        border-radius: 10px;
        font-weight: 800;
        font-size: 1.15rem;
        line-height: 1;
        text-align: center;
        font-variant-numeric: tabular-nums;
    }

    .corp-table__count--active {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }

    .corp-table__count--annulled {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    /* ─── Vigencia cell ───────────────────────────────────────── */
    .vigencia-cell {
        min-width: 260px;
    }

    .vigencia-cell__dates {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 1.1rem;
        color: #323130;
        font-weight: 600;
        white-space: nowrap;
    }

    .vigencia-cell__dates i {
        color: #8a8886;
        font-size: 1rem;
    }

    .vigencia-cell__sep {
        color: #c8c6c4;
        padding: 0 4px;
    }

    .vigencia-cell__progress {
        margin-top: 8px;
        max-width: 260px;
    }

    .vigencia-progress__track {
        position: relative;
        height: 6px;
        background: #edebe9;
        border-radius: 999px;
        overflow: hidden;
    }

    .vigencia-progress__fill {
        position: absolute;
        inset: 0 auto 0 0;
        height: 100%;
        border-radius: 999px;
        transition: width 0.4s ease;
    }

    .vigencia-progress--ok .vigencia-progress__fill {
        background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%);
    }

    .vigencia-progress--warn .vigencia-progress__fill {
        background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
    }

    .vigencia-progress--danger .vigencia-progress__fill {
        background: linear-gradient(90deg, #ef4444 0%, #b91c1c 100%);
    }

    .vigencia-progress--muted .vigencia-progress__fill {
        background: #c8c6c4;
    }

    .vigencia-progress__text {
        margin-top: 5px;
        font-size: 0.95rem;
        font-weight: 600;
        color: #605e5c;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .vigencia-progress--ok .vigencia-progress__text {
        color: #1e40af;
    }

    .vigencia-progress--warn .vigencia-progress__text {
        color: #b45309;
    }

    .vigencia-progress--danger .vigencia-progress__text {
        color: #b91c1c;
    }

    .vigencia-progress__sep {
        color: #c8c6c4;
    }

    .vigencia-progress__muted {
        color: #8a8886;
        font-weight: 500;
    }

    /* ─── Table wrapper ───────────────────────────────────────── */
    .corp-table-wrapper {
        margin: 0;
        padding: 4px 0 8px 0;
    }

    .corp-panel--red .corp-table {
        background: #fff;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        border-color: #fecaca;
    }

    .corp-table-wrapper .corp-table {
        border: 1px solid #f3f2f1;
        border-radius: 10px;
        overflow: hidden;
    }

    /* ─── Row actions ─────────────────────────────────────────── */
    .corp-table__actions {
        display: flex;
        gap: 10px;
        justify-content: center;
    }

    .row-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 10px;
        font-size: 1rem;
        font-weight: 700;
        text-decoration: none;
        border: 2px solid transparent;
        transition: all 0.15s ease;
        cursor: pointer;
        white-space: nowrap;
    }

    .row-action--pdf {
        background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
        color: #fff;
        box-shadow: 0 3px 10px rgba(239, 68, 68, 0.3);
    }

    .row-action--pdf:hover {
        background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(239, 68, 68, 0.45);
        text-decoration: none;
    }

    .row-action--danger {
        background: #fffbeb;
        color: #b45309;
        border-color: #fde68a;
    }

    .row-action--danger:hover {
        background: #fef3c7;
        color: #92400e;
        border-color: #fcd34d;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(245, 158, 11, 0.3);
        text-decoration: none;
    }

    /* ─── Empty state ─────────────────────────────────────────── */
    .corp-empty {
        text-align: center;
        padding: 72px 28px;
        background: #fff;
        border: 1px solid #e8e6e3;
        border-radius: 14px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        margin-bottom: 24px;
    }

    .corp-empty__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 130px;
        height: 130px;
        border-radius: 50%;
        background: #f3f2f1;
        color: #c8c6c4;
        font-size: 3.6rem;
        margin-bottom: 24px;
    }

    .corp-empty__title {
        font-size: 2rem;
        font-weight: 700;
        color: #323130;
        margin-bottom: 12px;
    }

    .corp-empty__text {
        font-size: 1.2rem;
        color: #605e5c;
        line-height: 1.6;
        margin-bottom: 32px;
    }

    .corp-empty__text strong {
        color: #1a237e;
    }

    /* ─── Responsive ─────────────────────────────────────────── */
    @media (max-width: 992px) {
        .corp-header__title {
            font-size: 2.2rem;
        }

        .corp-table {
            font-size: 1rem;
        }

        .corp-table thead th {
            padding: 12px 14px;
            font-size: 0.85rem;
        }

        .corp-table tbody td {
            padding: 14px;
            font-size: 1rem;
        }

        .stat-card__value {
            font-size: 2.1rem;
        }
    }

    @media (max-width: 576px) {
        .corp-page {
            padding: 14px;
        }

        .corp-header {
            padding: 20px;
        }

        .corp-header__icon {
            width: 64px;
            height: 64px;
            font-size: 28px;
        }

        .corp-header__title {
            font-size: 1.7rem;
        }

        .corp-header__subtitle {
            font-size: 1rem;
        }

        .corp-panel__title {
            font-size: 1.15rem;
            padding: 14px 20px;
            gap: 10px;
        }

        .corp-panel__title i {
            width: 32px;
            height: 32px;
            font-size: 1rem;
        }

        .corp-panel__body {
            padding: 20px 22px;
        }

        .corp-panel__body--roomy {
            padding: 22px 24px;
        }

        .btn-corp {
            padding: 12px 20px;
            font-size: 1.05rem;
        }

        .stat-card__value {
            font-size: 1.85rem;
        }

        .stat-card__title {
            font-size: 0.85rem;
        }

        .stat-card {
            padding: 16px 18px 14px 18px;
        }

        .vigencia-cell {
            min-width: auto;
        }

        .plan-chip {
            font-size: 0.9rem;
            padding: 4px 10px;
        }

        .row-action {
            padding: 8px 12px;
            font-size: 0.9rem;
        }
    }
</style>

<div class="corp-page">

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- PAGE HEADER                                                  -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div class="corp-header">
        <div class="corp-header__left">
            <div class="corp-header__icon">
                <i class="fas fa-file-contract" aria-hidden="true"></i>
            </div>
            <div>
                <div class="corp-header__eyebrow">Contratos Corporativos</div>
                <h1 class="corp-header__title"><?= Html::encode($corporativo->nombre) ?></h1>
                <div class="corp-header__subtitle">
                    <?php if (!empty($corporativo->rif)): ?>
                        <span class="corp-header__badge">
                            <i class="fas fa-id-card" aria-hidden="true"></i>
                            RIF <?= Html::encode($corporativo->rif) ?>
                        </span>
                    <?php endif; ?>
                    <span class="corp-header__badge">
                        <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                        Año actual <?= $currentYear ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="corp-header__actions">
            <?php if ($newContractAction['style'] === 'disabled'): ?>
                <button type="button"
                    class="btn-corp btn-corp--disabled"
                    disabled
                    title="<?= Html::encode($newContractAction['reason']) ?>">
                    <i class="fas fa-shield-alt" aria-hidden="true"></i>
                    <?= Html::encode($newContractAction['label']) ?>
                </button>
            <?php else: ?>
                <?= Html::a(
                    '<i class="fas ' . ($newContractAction['style'] === 'renew'
                        ? 'fa-sync-alt'
                        : 'fa-plus-circle') . '" aria-hidden="true"></i> '
                        . Html::encode($newContractAction['label']),
                    Url::to(['contract-selector', 'id' => $corporativo->id]),
                    [
                        'class'   => 'btn-corp btn-corp--' . $newContractAction['style'],
                        'onclick' => "openCorporateContractDrawer(event, this); return false;",
                        'title'   => $newContractAction['reason'] ?: null,
                    ]
                ) ?>
            <?php endif; ?>

            <?= Html::a(
                '<i class="fas fa-arrow-left" aria-hidden="true"></i> Volver',
                Url::to(['view', 'id' => $corporativo->id]),
                ['class' => 'btn-corp btn-corp--ghost']
            ) ?>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- QUICK STATS                                                  -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <?php if (!empty($contractGroups)): ?>
        <div class="corp-stats">

            <div class="stat-card stat-card--indigo">
                <div class="stat-card__header">
                    <div class="stat-card__icon">
                        <i class="fas fa-file-contract" aria-hidden="true"></i>
                    </div>
                    <div class="stat-card__title">Contratos</div>
                </div>
                <div class="stat-card__value"><?= $stats['total_contracts'] ?></div>
                <div class="stat-card__context">
                    <?php if ($stats['annulled_contracts'] > 0): ?>
                        <span class="stat-card__badge stat-card__badge--muted">
                            <?= $stats['annulled_contracts'] ?> anulado<?= $stats['annulled_contracts'] === 1 ? '' : 's' ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($stats['expired_contracts'] > 0): ?>
                        <span class="stat-card__badge stat-card__badge--amber">
                            <?= $stats['expired_contracts'] ?> vencido<?= $stats['expired_contracts'] === 1 ? '' : 's' ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($stats['annulled_contracts'] === 0 && $stats['expired_contracts'] === 0): ?>
                        <span class="stat-card__hint">Todos vigentes o programados</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="stat-card stat-card--green <?= $stats['expiring_soon'] > 0 ? 'stat-card--attention' : '' ?>">
                <div class="stat-card__header">
                    <div class="stat-card__icon">
                        <i class="fas fa-check-circle" aria-hidden="true"></i>
                    </div>
                    <div class="stat-card__title">Vigentes</div>
                </div>
                <div class="stat-card__value"><?= $stats['active_contracts'] ?></div>
                <div class="stat-card__context">
                    <?php if ($stats['expiring_soon'] > 0): ?>
                        <span class="stat-card__badge stat-card__badge--amber">
                            <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                            <?= $stats['expiring_soon'] ?> por vencer (&le;<?= ContractHelper::RENEWAL_WINDOW_DAYS ?> días)
                        </span>
                    <?php elseif ($stats['active_contracts'] > 0): ?>
                        <span class="stat-card__hint">Sin vencimientos próximos</span>
                    <?php else: ?>
                        <span class="stat-card__hint stat-card__hint--muted">Ningún contrato activo</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="stat-card stat-card--emerald">
                <div class="stat-card__header">
                    <div class="stat-card__icon">
                        <i class="fas fa-user-check" aria-hidden="true"></i>
                    </div>
                    <div class="stat-card__title">Afiliados Activos</div>
                </div>
                <div class="stat-card__value"><?= (int) $activeCount ?></div>
                <div class="stat-card__context">
                    <span class="stat-card__hint">con al menos un contrato vigente</span>
                </div>
            </div>

            <div class="stat-card stat-card--rose">
                <div class="stat-card__header">
                    <div class="stat-card__icon">
                        <i class="fas fa-user-slash" aria-hidden="true"></i>
                    </div>
                    <div class="stat-card__title">Afiliados Anulados</div>
                </div>
                <div class="stat-card__value"><?= (int) $annulledCount ?></div>
                <div class="stat-card__context">
                    <?php if ((int) $annulledCount === 0): ?>
                        <span class="stat-card__hint stat-card__hint--muted">ninguno</span>
                    <?php else: ?>
                        <span class="stat-card__hint">con solo contratos anulados</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="stat-card stat-card--amber">
                <div class="stat-card__header">
                    <div class="stat-card__icon">
                        <i class="fas fa-dollar-sign" aria-hidden="true"></i>
                    </div>
                    <div class="stat-card__title">Facturación</div>
                </div>
                <div class="stat-card__value">
                    <span class="stat-card__currency">$</span><?= number_format($stats['active_monthly'], 0, ',', '.') ?>
                </div>
                <div class="stat-card__context">
                    <span class="stat-card__hint">facturación mensual estimada</span>
                </div>
            </div>

        </div>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- CONTRATOS COLECTIVOS                                         -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <?php if (empty($contractGroups)): ?>

        <div class="corp-empty">
            <div class="corp-empty__icon">
                <i class="fas fa-file-signature" aria-hidden="true"></i>
            </div>
            <h2 class="corp-empty__title">Sin Contratos Registrados</h2>
            <p class="corp-empty__text">
                Este corporativo aún no tiene contratos colectivos.<br>
                Comience creando el primero para el año <strong><?= $currentYear ?></strong>.
            </p>
            <?= Html::a(
                '<i class="fas fa-plus-circle" aria-hidden="true"></i> Crear Primer Contrato',
                Url::to(['contract-selector', 'id' => $corporativo->id]),
                [
                    'class'   => 'btn-corp btn-corp--primary',
                    'onclick' => "openCorporateContractDrawer(event, this); return false;",
                ]
            ) ?>
        </div>

    <?php else: ?>

        <div class="corp-panel corp-panel--red">
            <h3 class="corp-panel__title">
                <i class="fas fa-file-contract" aria-hidden="true"></i>
                Contratos Corporativos
                <span class="badge"><?= $stats['total_contracts'] ?></span>
            </h3>

            <div class="corp-panel__body corp-panel__body--roomy">
                <div class="corp-table-wrapper table-responsive">
                    <table class="corp-table">
                        <thead>
                            <tr>
                                <th>N° Contrato</th>
                                <th>Planes</th>
                                <th style="text-align: center;">Activos</th>
                                <th style="text-align: center;">Anulados</th>
                                <th>Monto Total</th>
                                <th>Vigencia</th>
                                <th style="text-align: center;">Estatus</th>
                                <th style="text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contractGroups as $group): ?>
                                <?php
                                $anchor     = $group['anchor'];
                                $isAnnulled = $group['is_annulled'];
                                $planNames  = $group['plan_names'] ?? [];
                                $planCounts = $group['plan_counts'] ?? [];

                                $remainingMeta = ContractHelper::getCorporateContractRemainingMeta($anchor);

                                $renewalThresholdPct = $remainingMeta['total_days'] > 0
                                    ? (int) round((1 - (ContractHelper::RENEWAL_WINDOW_DAYS / $remainingMeta['total_days'])) * 100)
                                    : 100;

                                switch ($remainingMeta['state']) {
                                    case 'expired':
                                        $progressTone = 'danger';
                                        break;
                                    case 'upcoming':
                                        $progressTone = 'ok';
                                        break;
                                    case 'active':
                                        $progressTone = $remainingMeta['percent'] >= $renewalThresholdPct
                                            ? 'warn'
                                            : 'ok';
                                        break;
                                    default:
                                        $progressTone = 'muted';
                                }
                                ?>
                                <tr class="<?= $isAnnulled ? 'corp-table__row--annulled' : '' ?>">

                                    <td>
                                        <span class="corp-table__contract-no">
                                            <?= Html::encode($anchor->nrocontrato ?: 'N/A') ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?php if (!empty($planNames)): ?>
                                            <div class="corp-table__plans">
                                                <?php foreach ($planNames as $pName): ?>
                                                    <?php
                                                    $count = isset($planCounts[$pName])
                                                        ? (int)$planCounts[$pName]
                                                        : null;
                                                    ?>
                                                    <span class="plan-chip">
                                                        <?= Html::encode($pName) ?>
                                                        <?php if ($count !== null): ?>
                                                            <span class="plan-chip__count"><?= $count ?></span>
                                                        <?php endif; ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>

                                    <td style="text-align: center;">
                                        <span class="corp-table__count corp-table__count--active">
                                            <?= (int) $activeCount ?>
                                        </span>
                                    </td>

                                    <td style="text-align: center;">
                                        <span class="corp-table__count corp-table__count--annulled">
                                            <?= (int) $annulledCount ?>
                                        </span>
                                    </td>

                                    <td class="corp-table__money">
                                        $<?= number_format($group['total_amount'] ?? 0, 2, ',', '.') ?>
                                    </td>

                                    <td class="vigencia-cell">
                                        <div class="vigencia-cell__dates">
                                            <i class="far fa-calendar-alt" aria-hidden="true"></i>
                                            <?= $anchor->fecha_ini ? date('d/m/Y', strtotime($anchor->fecha_ini)) : 'N/A' ?>
                                            <span class="vigencia-cell__sep">→</span>
                                            <?= $anchor->fecha_ven ? date('d/m/Y', strtotime($anchor->fecha_ven)) : 'N/A' ?>
                                        </div>

                                        <?php if ($remainingMeta['state'] !== 'unknown'): ?>
                                            <div class="vigencia-cell__progress vigencia-progress--<?= $progressTone ?>">
                                                <div class="vigencia-progress__track"
                                                    role="progressbar"
                                                    aria-valuenow="<?= $remainingMeta['percent'] ?>"
                                                    aria-valuemin="0"
                                                    aria-valuemax="100"
                                                    aria-label="Progreso de vigencia del contrato">
                                                    <div class="vigencia-progress__fill"
                                                        style="width: <?= $remainingMeta['percent'] ?>%;"></div>
                                                </div>
                                                <div class="vigencia-progress__text">
                                                    <?= Html::encode($remainingMeta['label']) ?>
                                                    <?php if ($remainingMeta['total_days'] > 0): ?>
                                                        <span class="vigencia-progress__sep">•</span>
                                                        <span class="vigencia-progress__muted">
                                                            <?= $remainingMeta['percent'] ?>% transcurrido
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td style="text-align: center;">
                                        <?= ContractHelper::getCorporateContractStatusBadge($anchor) ?>
                                    </td>

                                    <td>
                                        <div class="corp-table__actions">
                                            <?= Html::a(
                                                '<i class="fas fa-file-pdf" aria-hidden="true"></i> PDF',
                                                [
                                                    'generar-contrato-pdf',
                                                    'id'  => $corporativo->id,
                                                    'cid' => $anchor->id,
                                                ],
                                                [
                                                    'class'     => 'row-action row-action--pdf',
                                                    'target'    => '_blank',
                                                    'data-pjax' => '0',
                                                    'title'     => 'Descargar PDF del contrato (todos los planes)',
                                                ]
                                            ) ?>

                                            <?php if (!$isAnnulled): ?>
                                                <?= Html::a(
                                                    '<i class="fas fa-ban" aria-hidden="true"></i> Anular',
                                                    ['anular-contrato-corporativo', 'id' => $anchor->id],
                                                    [
                                                        'class'   => 'row-action row-action--danger',
                                                        'title'   => 'Anular este contrato (todos sus planes)',
                                                        'onclick' => "return confirm('¿Está seguro que desea anular este contrato colectivo? Los contratos individuales de los afiliados también serán anulados.');",
                                                    ]
                                                ) ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php endif; ?>

</div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- DRAWER HOST                                                  -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div id="corporateContractDrawerHost"></div>

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