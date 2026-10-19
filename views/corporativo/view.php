<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ContractHelper;
use app\models\ContratoCorporativo;
use app\models\Contratos;

/** @var yii\web\View $this */
/** @var app\models\Corporativo $model */

$this->title = $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Corporativos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->nombre;

\yii\web\YiiAsset::register($this);

// Auxiliary formatter for nullable dates
$formatUpdatedAt = static function ($value) {
    return empty($value)
        ? 'No se ha modificado'
        : Yii::$app->formatter->asDatetime($value, 'medium');
};

// ── Load contract groups ONCE for the whole view ────────────────────
$corporateContractsFlat  = ContractHelper::findCorporateContracts($model->id);
$corporateContractsGroup = ContractHelper::groupCorporateContractsByNumber($corporateContractsFlat);
$contractCount           = count($corporateContractsGroup);

// ─────────────────────────────────────────────────────────────────────
// ACTIVE / ANNULLED AFFILIATE COUNTS — SAME LOGIC AS corporate/index.php
// ─────────────────────────────────────────────────────────────────────
// This mirrors CorporativoController::actionIndex() exactly, scoped to
// the current corporate. `user_datos` is the source of truth; the
// junction table (contratos_corporativos_afiliados) is NOT consulted
// for the counts.
//
//   ACTIVO  = has at least one contract whose estatus <> 'Anulado'
//   ANULADO = has only Anulado contracts (or no contracts at all)
// ─────────────────────────────────────────────────────────────────────
$corpId = (int) $model->id;

$activeRow = (new \yii\db\Query())
    ->select([
        'cnt' => 'COUNT(DISTINCT ud.id)',
    ])
    ->from(['ud' => 'user_datos'])
    ->innerJoin(['c' => 'contratos'], 'c.user_id = ud.id')
    ->where(['ud.afiliado_corporativo_id' => $corpId])
    ->andWhere(['ud.user_datos_type_id' => 2])
    ->andWhere(['<>', 'c.estatus', Contratos::STATUS_ANULADO])
    ->one(\Yii::$app->db);

$annulledRow = (new \yii\db\Query())
    ->select([
        'cnt' => 'COUNT(DISTINCT ud.id)',
    ])
    ->from(['ud' => 'user_datos'])
    ->where(['ud.afiliado_corporativo_id' => $corpId])
    ->andWhere(['ud.user_datos_type_id' => 2])
    ->andWhere([
        'NOT EXISTS',
        (new \yii\db\Query())
            ->from(['c' => 'contratos'])
            ->where('c.user_id = ud.id')
            ->andWhere(['<>', 'c.estatus', Contratos::STATUS_ANULADO])
    ])
    ->one(\Yii::$app->db);

$activeCount   = (int) ($activeRow['cnt']   ?? 0);
$annulledCount = (int) ($annulledRow['cnt'] ?? 0);

// ─────────────────────────────────────────────────────────────────────
// Lists of affiliates for the side panels
// ─────────────────────────────────────────────────────────────────────
// An affiliate is ACTIVE if they have at least one contract whose
// estatus <> 'Anulado'. Otherwise they count as ANNULLED.
//
// Both lists are sorted by `nombres`, then `apellidos`.
// ─────────────────────────────────────────────────────────────────────

$allAffiliates = $model->users;

$activeAffiliates   = [];
$annulledAffiliates = [];

if (!empty($allAffiliates)) {
    $affiliateIds = array_map(fn($u) => (int)$u->id, $allAffiliates);

    $hasLiveContract = (new \yii\db\Query())
        ->select(['user_id'])
        ->from('contratos')
        ->where(['user_id' => $affiliateIds])
        ->andWhere(['<>', 'estatus', Contratos::STATUS_ANULADO])
        ->column(\Yii::$app->db);

    $hasLiveContract = array_flip(array_map('intval', $hasLiveContract));

    foreach ($allAffiliates as $user) {
        if (isset($hasLiveContract[(int)$user->id])) {
            $activeAffiliates[] = $user;
        } else {
            $annulledAffiliates[] = $user;
        }
    }
}

usort($activeAffiliates, static function ($a, $b) {
    $cmp = strcasecmp((string)($a->nombres ?? ''), (string)($b->nombres ?? ''));
    if ($cmp !== 0) {
        return $cmp;
    }
    return strcasecmp((string)($a->apellidos ?? ''), (string)($b->apellidos ?? ''));
});

usort($annulledAffiliates, static function ($a, $b) {
    $cmp = strcasecmp((string)($a->nombres ?? ''), (string)($b->nombres ?? ''));
    if ($cmp !== 0) {
        return $cmp;
    }
    return strcasecmp((string)($a->apellidos ?? ''), (string)($b->apellidos ?? ''));
});
?>

<style>
    /* ══════════════════════════════════════════════════════════════ */
    /* CORPORATE DETAIL PAGE                                          */
    /* Mirrors the visual language of contracts.php                   */
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
        background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 38px;
        box-shadow: 0 6px 18px rgba(79, 70, 229, 0.3);
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
        font-size: 2.9rem;
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

    .corp-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 1.05rem;
        font-weight: 700;
        padding: 7px 16px;
        border-radius: 12px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
    }

    .corp-status-pill--active {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }

    .corp-status-pill--inactive {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
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

    .btn-corp--info {
        background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
        color: #fff;
        box-shadow: 0 4px 12px rgba(8, 145, 178, 0.35);
    }

    .btn-corp--info:hover {
        background: linear-gradient(135deg, #0e7490 0%, #155e75 100%);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(8, 145, 178, 0.5);
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

    /* ── Per-section header colors ───────────────────────────── */
    .corp-panel--blue .corp-panel__title {
        background: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #3b82f6 100%);
    }

    .corp-panel--indigo .corp-panel__title {
        background: linear-gradient(135deg, #3730a3 0%, #4f46e5 50%, #6366f1 100%);
    }

    .corp-panel--gray .corp-panel__title {
        background: linear-gradient(135deg, #374151 0%, #6b7280 50%, #9ca3af 100%);
    }

    .corp-panel--yellow .corp-panel__title {
        background: linear-gradient(135deg, #b45309 0%, #d97706 50%, #f59e0b 100%);
    }

    .corp-panel--green .corp-panel__title {
        background: linear-gradient(135deg, #047857 0%, #059669 50%, #10b981 100%);
    }

    .corp-panel--red .corp-panel__title {
        background: linear-gradient(135deg, #991b1b 0%, #dc2626 50%, #ef4444 100%);
    }

    .corp-panel--teal .corp-panel__title {
        background: linear-gradient(135deg, #0f766e 0%, #0d9488 50%, #14b8a6 100%);
    }

    .corp-panel--violet .corp-panel__title {
        background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 50%, #a78bfa 100%);
    }

    .corp-panel--cyan .corp-panel__title {
        background: linear-gradient(135deg, #0e7490 0%, #0891b2 50%, #22d3ee 100%);
    }

    /* ─── Info grid ───────────────────────────────────────────── */
    .corp-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px 44px;
        align-items: start;
    }

    @media (max-width: 768px) {
        .corp-info-grid {
            grid-template-columns: 1fr;
            gap: 14px;
        }
    }

    .corp-info-row {
        margin: 0;
        font-size: 1.15rem;
        color: #323130;
        line-height: 1.55;
    }

    .corp-info-row strong {
        display: inline-block;
        min-width: 200px;
        color: #605e5c;
        font-weight: 600;
        margin-right: 10px;
    }

    .corp-info-row--full {
        grid-column: 1 / -1;
    }

    .corp-info-divider {
        grid-column: 1 / -1;
        height: 1px;
        background: #f3f2f1;
        margin: 10px 0;
        border: none;
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
    }

    .corp-table__plans {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .plan-chip {
        display: inline-flex;
        align-items: center;
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        color: #4338ca;
        font-size: 1rem;
        font-weight: 700;
        padding: 5px 14px;
        border-radius: 10px;
        border: 1px solid #c7d2fe;
        white-space: nowrap;
    }

    /* ── Table wrapper ── */
    .corp-table-wrapper {
        margin: 0;
        padding: 4px 0 8px 0;
    }

    .corp-table-wrapper .corp-table {
        border: 1px solid #f3f2f1;
        border-radius: 10px;
        overflow: hidden;
    }

    .corp-table__actions {
        display: flex;
        gap: 10px;
        justify-content: center;
    }

    /* ── Affiliate count cells ── */
    .corp-table__count {
        text-align: center;
        font-weight: 800;
        font-size: 1.25rem;
        line-height: 1;
        padding: 6px 14px;
        border-radius: 10px;
        display: inline-block;
        min-width: 54px;
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

    /* ─── Two-column panels grid ──────────────────────────────── */
    .corp-two-col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 24px;
    }

    @media (max-width: 992px) {
        .corp-two-col {
            grid-template-columns: 1fr;
        }
    }

    /* ─── Search + list ──────────────────────────────────────── */
    .corp-search {
        position: relative;
        margin-bottom: 18px;
    }

    .corp-search input {
        width: 100%;
        padding: 14px 18px 14px 46px;
        border: 1px solid #e8e6e3;
        border-radius: 10px;
        font-size: 1.1rem;
        color: #323130;
        background: #faf9f8;
        transition: all 0.15s ease;
    }

    .corp-search input:focus {
        outline: none;
        border-color: #4f46e5;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
    }

    .corp-search::before {
        content: '\f002';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #8a8886;
        font-size: 1.05rem;
        pointer-events: none;
    }

    .corp-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .corp-list__item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 18px;
        border-bottom: 1px solid #f3f2f1;
        transition: background-color 0.12s ease;
        border-radius: 8px;
    }

    .corp-list__item:hover {
        background: #fafbfc;
    }

    .corp-list__item:last-child {
        border-bottom: none;
    }

    .corp-list__name {
        color: #1e40af;
        font-weight: 600;
        text-decoration: none;
        font-size: 1.15rem;
    }

    .corp-list__name:hover {
        color: #1e3a8a;
        text-decoration: underline;
    }

    .corp-list__meta {
        color: #8a8886;
        font-size: 1rem;
        margin-left: 8px;
    }

    .corp-list__arrow {
        color: #c8c6c4;
        font-size: 1rem;
    }

    .corp-show-more {
        text-align: center;
        margin-top: 14px;
    }

    .corp-show-more button {
        background: transparent;
        border: none;
        color: #2563eb;
        font-weight: 700;
        font-size: 1.05rem;
        padding: 10px 18px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .corp-show-more button:hover {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .corp-empty-text {
        color: #8a8886;
        font-style: italic;
        text-align: center;
        padding: 28px;
        font-size: 1.1rem;
    }

    /* ─── Responsive ─────────────────────────────────────────── */
    @media (max-width: 992px) {
        .corp-header__title {
            font-size: 2.2rem;
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

        .corp-info-row strong {
            min-width: 140px;
        }

        .corp-info-row {
            font-size: 1.05rem;
        }

        .btn-corp {
            padding: 12px 20px;
            font-size: 1.05rem;
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

        .corp-list__name {
            font-size: 1.05rem;
        }

        .vigencia-cell {
            min-width: auto;
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
                <i class="fas fa-building" aria-hidden="true"></i>
            </div>
            <div>
                <div class="corp-header__eyebrow">Detalles del Corporativo</div>
                <h1 class="corp-header__title"><?= Html::encode($model->nombre) ?></h1>
                <div class="corp-header__subtitle">
                    <?php if (!empty($model->rif)): ?>
                        <span class="corp-header__badge">
                            <i class="fas fa-id-card" aria-hidden="true"></i>
                            RIF <?= Html::encode($model->rif) ?>
                        </span>
                    <?php endif; ?>
                    <span class="corp-status-pill corp-status-pill--<?= $model->estatus === 'Activo' ? 'active' : 'inactive' ?>">
                        <i class="fas fa-circle" style="font-size: 8px;" aria-hidden="true"></i>
                        <?= Html::encode($model->estatus) ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="corp-header__actions">
            <?= Html::a(
                '<i class="fas fa-edit" aria-hidden="true"></i> Actualizar',
                ['update', 'id' => $model->id],
                ['class' => 'btn-corp btn-corp--primary']
            ) ?>
            <?= Html::a(
                '<i class="fas fa-arrow-left" aria-hidden="true"></i> Volver',
                Url::to(['index']),
                [
                    'class' => 'btn-corp btn-corp--ghost',
                    'title' => 'Volver a la lista de corporativos',
                ]
            ) ?>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- INFORMACIÓN GENERAL                                          -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div class="corp-panel corp-panel--blue">
        <h3 class="corp-panel__title">
            <i class="fas fa-info-circle" aria-hidden="true"></i>
            Información General
        </h3>
        <div class="corp-panel__body">
            <div class="corp-info-grid">
                <p class="corp-info-row">
                    <strong>Nombre:</strong>
                    <?= Html::encode($model->nombre) ?>
                </p>
                <p class="corp-info-row">
                    <strong>RIF:</strong>
                    <?= Html::encode($model->rif) ?>
                </p>
                <p class="corp-info-row">
                    <strong>Email:</strong>
                    <?= Html::a(
                        Html::encode($model->email),
                        'mailto:' . Html::encode($model->email),
                        ['style' => 'color: #2563eb; text-decoration: none;']
                    ) ?>
                </p>
                <p class="corp-info-row">
                    <strong>Teléfono:</strong>
                    <?= Html::encode($model->telefono) ?>
                </p>
                <p class="corp-info-row">
                    <strong>Código Asesor:</strong>
                    <?= Html::encode($model->codigo_asesor) ?>
                </p>
                <p class="corp-info-row">
                    <strong>Estatus:</strong>
                    <span class="corp-status-pill corp-status-pill--<?= $model->estatus === 'Activo' ? 'active' : 'inactive' ?>">
                        <?= Html::encode($model->estatus) ?>
                    </span>
                </p>

                <hr class="corp-info-divider">

                <p class="corp-info-row">
                    <strong>Fecha de Creación:</strong>
                    <?= Html::encode(Yii::$app->formatter->asDatetime($model->created_at, 'medium')) ?>
                </p>
                <p class="corp-info-row">
                    <strong>Última Actualización:</strong>
                    <?= Html::encode($formatUpdatedAt($model->updated_at)) ?>
                </p>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- UBICACIÓN + REGISTRAL (2 columns)                            -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div class="corp-two-col">
        <div class="corp-panel corp-panel--indigo" style="margin-bottom: 0;">
            <h3 class="corp-panel__title">
                <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                Ubicación Geográfica
            </h3>
            <div class="corp-panel__body">
                <div class="corp-info-grid" style="grid-template-columns: 1fr;">
                    <p class="corp-info-row">
                        <strong>Estado:</strong>
                        <?= Html::encode($model->rmEstado ? $model->rmEstado->nombre : $model->estado) ?>
                    </p>
                    <p class="corp-info-row">
                        <strong>Municipio:</strong>
                        <?= Html::encode($model->rmMunicipio ? $model->rmMunicipio->nombre : $model->municipio) ?>
                    </p>
                    <p class="corp-info-row">
                        <strong>Parroquia:</strong>
                        <?= Html::encode($model->rmParroquia ? $model->rmParroquia->nombre : $model->parroquia) ?>
                    </p>
                    <p class="corp-info-row">
                        <strong>Ciudad:</strong>
                        <?= Html::encode($model->rmCiudad ? $model->rmCiudad->nombre : $model->ciudad) ?>
                    </p>
                    <p class="corp-info-row">
                        <strong>Dirección:</strong><br>
                        <span style="display: block; margin-top: 4px; color: #605e5c;">
                            <?= nl2br(Html::encode($model->direccion)) ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <div class="corp-panel corp-panel--gray" style="margin-bottom: 0;">
            <h3 class="corp-panel__title">
                <i class="fas fa-file-alt" aria-hidden="true"></i>
                Información Registral
            </h3>
            <div class="corp-panel__body">
                <div class="corp-info-grid" style="grid-template-columns: 1fr;">
                    <p class="corp-info-row">
                        <strong>Lugar de Registro:</strong>
                        <?= Html::encode($model->lugar_registro) ?>
                    </p>
                    <p class="corp-info-row">
                        <strong>Fecha Registro Mercantil:</strong>
                        <?= Html::encode(Yii::$app->formatter->asDate($model->fecha_registro_mercantil, 'long')) ?>
                    </p>
                    <p class="corp-info-row">
                        <strong>Tomo de Registro:</strong>
                        <?= Html::encode($model->tomo_registro) ?>
                    </p>
                    <p class="corp-info-row">
                        <strong>Folio de Registro:</strong>
                        <?= Html::encode($model->folio_registro) ?>
                    </p>
                    <p class="corp-info-row">
                        <strong>Domicilio Fiscal:</strong><br>
                        <span style="display: block; margin-top: 4px; color: #605e5c;">
                            <?= nl2br(Html::encode($model->domicilio_fiscal)) ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- CONTACTO + FINANCIEROS (2 columns)                           -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div class="corp-two-col">
        <div class="corp-panel corp-panel--yellow" style="margin-bottom: 0;">
            <h3 class="corp-panel__title">
                <i class="fas fa-user-circle" aria-hidden="true"></i>
                Contacto Principal
            </h3>
            <div class="corp-panel__body">
                <div class="corp-info-grid" style="grid-template-columns: 1fr;">
                    <p class="corp-info-row">
                        <strong>Nombre:</strong>
                        <?= Html::encode($model->contacto_nombre) ?>
                    </p>
                    <p class="corp-info-row">
                        <strong>Cédula:</strong>
                        <?= Html::encode($model->contacto_cedula) ?>
                    </p>
                    <p class="corp-info-row">
                        <strong>Teléfono:</strong>
                        <?= Html::encode($model->contacto_telefono) ?>
                    </p>
                    <p class="corp-info-row">
                        <strong>Cargo:</strong>
                        <?= Html::encode($model->contacto_cargo) ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="corp-panel corp-panel--green" style="margin-bottom: 0;">
            <h3 class="corp-panel__title">
                <i class="fas fa-chart-line" aria-hidden="true"></i>
                Datos Financieros y de Actividad
            </h3>
            <div class="corp-panel__body">
                <div class="corp-info-grid" style="grid-template-columns: 1fr;">
                    <p class="corp-info-row">
                        <strong>Actividad Económica:</strong>
                        <?= Html::encode($model->actividad_economica) ?>
                    </p>
                    <p class="corp-info-row">
                        <strong>Productos y Servicios:</strong>
                        <?= Html::encode($model->productos_servicios) ?>
                    </p>
                    <p class="corp-info-row">
                        <strong>Utilidad Ejercicio Anterior:</strong>
                        <?= Html::encode($model->utilidad_ejercicio_anterior) ?>
                    </p>
                    <p class="corp-info-row">
                        <strong>Patrimonio:</strong>
                        <?= Html::encode($model->patrimonio) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- REPRESENTANTE LEGAL                                          -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div class="corp-panel corp-panel--blue">
        <h3 class="corp-panel__title">
            <i class="fas fa-user-tie" aria-hidden="true"></i>
            Datos del Representante Legal
        </h3>
        <div class="corp-panel__body">
            <div class="corp-info-grid">
                <p class="corp-info-row">
                    <strong>Nombre:</strong>
                    <?= Html::encode($model->nombre_representante) ?>
                </p>
                <p class="corp-info-row">
                    <strong>Cédula:</strong>
                    <?= Html::encode($model->cedula_representante) ?>
                </p>
                <p class="corp-info-row">
                    <strong>Nacionalidad:</strong>
                    <?= Html::encode($model->nacionalidad_representante) ?>
                </p>
                <p class="corp-info-row">
                    <strong>Estado Civil:</strong>
                    <?= Html::encode($model->estado_civil_representante) ?>
                </p>
                <p class="corp-info-row">
                    <strong>Lugar de Nacimiento:</strong>
                    <?= Html::encode($model->lugar_nacimiento_representante) ?>
                </p>
                <p class="corp-info-row">
                    <strong>Fecha de Nacimiento:</strong>
                    <?= Html::encode(Yii::$app->formatter->asDate($model->fecha_nacimiento_representante, 'long')) ?>
                </p>
                <p class="corp-info-row">
                    <strong>Sexo:</strong>
                    <?= Html::encode($model->sexo_representante) ?>
                </p>
                <p class="corp-info-row">
                    <strong>Profesión:</strong>
                    <?= Html::encode($model->profesion_representante) ?>
                </p>
                <p class="corp-info-row">
                    <strong>Ocupación:</strong>
                    <?= Html::encode($model->ocupacion_representante) ?>
                </p>
                <p class="corp-info-row">
                    <strong>Descripción de Actividad:</strong>
                    <?= Html::encode($model->descripcion_actividad_representante) ?>
                </p>

                <hr class="corp-info-divider">

                <p class="corp-info-row corp-info-row--full">
                    <strong>Dirección:</strong><br>
                    <span style="display: block; margin-top: 4px; color: #605e5c;">
                        <?= nl2br(Html::encode($model->direccion_representante)) ?>
                    </span>
                </p>
                <p class="corp-info-row corp-info-row--full">
                    <strong>Teléfono:</strong>
                    <?= Html::encode($model->telefono_representante) ?>
                </p>
            </div>
        </div>
    </div>
    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- CONTRATOS COLECTIVOS                                         -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <?php if (!empty($corporateContractsGroup)): ?>
        <div class="corp-panel corp-panel--red">
            <h3 class="corp-panel__title">
                <i class="fas fa-file-contract" aria-hidden="true"></i>
                Contratos Colectivos
                <span class="badge"><?= $contractCount ?></span>
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
                            <?php foreach ($corporateContractsGroup as $nrocontrato => $rows): ?>
                                <?php
                                $anchor    = $rows[0];
                                $planNames = [];

                                $contractIds = array_map(fn($r) => (int)$r->id, $rows);

                                // ─────────────────────────────────────────────────
                                // Monto Total — sum of `precio_individual` from the
                                // junction table, restricted to affiliates that
                                // have at least one non-Anulado contract.
                                //
                                // Active / Anulled COUNTS themselves come from
                                // $activeCount / $annulledCount, computed above
                                // with the same logic as corporate/index.php.
                                // ─────────────────────────────────────────────────
                                $totalAmount = (float) (new \yii\db\Query())
                                    ->from(['cca' => 'contratos_corporativos_afiliados'])
                                    ->innerJoin(['ud' => 'user_datos'], 'ud.id = cca.user_datos_id')
                                    ->where(['cca.contrato_corporativo_id' => $contractIds])
                                    ->andWhere([
                                        'EXISTS',
                                        (new \yii\db\Query())
                                            ->from(['c' => 'contratos'])
                                            ->where('c.user_id = ud.id')
                                            ->andWhere(['<>', 'c.estatus', Contratos::STATUS_ANULADO])
                                    ])
                                    ->sum('cca.precio_individual');

                                if ($totalAmount === null) {
                                    $totalAmount = 0.0;
                                }

                                // ── Vigencia metadata + progress tone ───────────
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

                                foreach ($rows as $r) {
                                    if ($r->plan) {
                                        $planNames[] = $r->plan->nombre;
                                    }
                                }
                                ?>
                                <tr>
                                    <td>
                                        <span class="corp-table__contract-no">
                                            <?= Html::encode($nrocontrato) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($planNames)): ?>
                                            <div class="corp-table__plans">
                                                <?php foreach ($planNames as $planName): ?>
                                                    <span class="plan-chip"><?= Html::encode($planName) ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="corp-table__count corp-table__count--active">
                                            <?= (int)$activeCount ?>
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="corp-table__count corp-table__count--annulled">
                                            <?= (int)$annulledCount ?>
                                        </span>
                                    </td>
                                    <td style="font-weight: 700; color: #107c10;">
                                        $<?= number_format($totalAmount, 2, ',', '.') ?>
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
                                                '<i class="fas fa-file-pdf" aria-hidden="true"></i> Ver PDF',
                                                [
                                                    'generar-contrato-pdf',
                                                    'id'  => $model->id,
                                                    'cid' => $anchor->id,
                                                ],
                                                [
                                                    'class'     => 'row-action row-action--pdf',
                                                    'target'    => '_blank',
                                                    'data-pjax' => '0',
                                                    'title'     => 'Descargar PDF del contrato (todos los planes)',
                                                ]
                                            ) ?>
                                            <?php if ($anchor->estatus !== ContratoCorporativo::STATUS_ANULADO): ?>
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
    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- CLÍNICAS + AFILIADOS (ACTIVOS / ANULADOS)                    -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div class="corp-two-col">
        <!-- Clínicas Asociadas -->
        <div class="corp-panel corp-panel--green" style="margin-bottom: 0;">
            <h3 class="corp-panel__title">
                <i class="fas fa-hospital" aria-hidden="true"></i>
                Clínicas Asociadas
                <span class="badge"><?= count($model->clinicas) ?></span>
            </h3>
            <div class="corp-panel__body">
                <?php if (!empty($model->clinicas)): ?>
                    <div class="corp-search">
                        <input id="typeahead-clinicas" type="text"
                            placeholder="Buscar clínica por nombre o RIF...">
                    </div>
                    <ul class="corp-list" id="clinicas-list">
                        <?php $clinicaIndex = 0;
                        foreach ($model->clinicas as $clinica): ?>
                            <li class="corp-list__item clinica-item<?= $clinicaIndex >= 3 ? ' d-none' : '' ?>"
                                data-nombre="<?= strtolower(Html::encode($clinica->nombre)) ?>"
                                data-rif="<?= strtolower(Html::encode($clinica->rif)) ?>">
                                <span>
                                    <?= Html::a(
                                        Html::encode($clinica->nombre),
                                        ['rm-clinica/view', 'id' => $clinica->id],
                                        ['class' => 'corp-list__name']
                                    ) ?>
                                    <span class="corp-list__meta">(RIF: <?= Html::encode($clinica->rif) ?>)</span>
                                </span>
                                <i class="fas fa-arrow-right corp-list__arrow" aria-hidden="true"></i>
                            </li>
                        <?php $clinicaIndex++;
                        endforeach; ?>
                    </ul>
                    <?php if (count($model->clinicas) > 3): ?>
                        <div class="corp-show-more">
                            <button id="ver-mas-clinicas" type="button">
                                Ver más <i class="fas fa-chevron-down ms-1" aria-hidden="true"></i>
                            </button>
                            <button id="ver-menos-clinicas" type="button" style="display:none;">
                                Ver menos <i class="fas fa-chevron-up ms-1" aria-hidden="true"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="corp-empty-text">No hay clínicas asociadas a este corporativo.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Afiliados Activos -->
        <div class="corp-panel corp-panel--gray" style="margin-bottom: 0;">
            <h3 class="corp-panel__title">
                <i class="fas fa-user-check" aria-hidden="true"></i>
                Afiliados Activos
                <span class="badge"><?= (int)$activeCount ?></span>
            </h3>
            <div class="corp-panel__body">
                <?php if (!empty($activeAffiliates)): ?>
                    <div class="corp-search">
                        <input id="typeahead-active" type="text"
                            placeholder="Buscar afiliado activo por nombre o usuario...">
                    </div>
                    <ul class="corp-list" id="active-list">
                        <?php $activeIndex = 0;
                        foreach ($activeAffiliates as $user): ?>
                            <?php
                            $nombreCompleto = trim(($user->nombres ?? '') . ' ' . ($user->apellidos ?? ''));
                            if ($nombreCompleto === '') {
                                $nombreCompleto = $user->user->username ?? $user->userLogin->username ?? 'Sin nombre';
                            }
                            $usuarioLogin = $user->user->username ?? $user->userLogin->username ?? 'N/A';
                            ?>
                            <li class="corp-list__item active-item<?= $activeIndex >= 3 ? ' d-none' : '' ?>"
                                data-nombre="<?= strtolower(Html::encode($nombreCompleto)) ?>"
                                data-usuario="<?= strtolower(Html::encode($usuarioLogin)) ?>">
                                <span>
                                    <?= Html::a(
                                        Html::encode($nombreCompleto),
                                        ['user-datos/view', 'id' => $user->id],
                                        ['class' => 'corp-list__name']
                                    ) ?>
                                    <span class="corp-list__meta">(Usuario: <?= Html::encode($usuarioLogin) ?>)</span>
                                </span>
                                <i class="fas fa-arrow-right corp-list__arrow" aria-hidden="true"></i>
                            </li>
                        <?php $activeIndex++;
                        endforeach; ?>
                    </ul>
                    <?php if (count($activeAffiliates) > 3): ?>
                        <div class="corp-show-more">
                            <button id="ver-mas-active" type="button">
                                Ver más <i class="fas fa-chevron-down ms-1" aria-hidden="true"></i>
                            </button>
                            <button id="ver-menos-active" type="button" style="display:none;">
                                Ver menos <i class="fas fa-chevron-up ms-1" aria-hidden="true"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="corp-empty-text">No hay afiliados con contrato activo en este corporativo.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Afiliados Anulados — full width below, so it has breathing room -->
    <div class="corp-panel corp-panel--red" style="margin-bottom: 0;">
        <h3 class="corp-panel__title">
            <i class="fas fa-user-slash" aria-hidden="true"></i>
            Afiliados Anulados
            <span class="badge"><?= (int)$annulledCount ?></span>
        </h3>
        <div class="corp-panel__body">
            <?php if (!empty($annulledAffiliates)): ?>
                <div class="corp-search">
                    <input id="typeahead-annulled" type="text"
                        placeholder="Buscar afiliado anulado por nombre o usuario...">
                </div>
                <ul class="corp-list" id="annulled-list">
                    <?php $annulledIndex = 0;
                    foreach ($annulledAffiliates as $user): ?>
                        <?php
                        $nombreCompleto = trim(($user->nombres ?? '') . ' ' . ($user->apellidos ?? ''));
                        if ($nombreCompleto === '') {
                            $nombreCompleto = $user->user->username ?? $user->userLogin->username ?? 'Sin nombre';
                        }
                        $usuarioLogin = $user->user->username ?? $user->userLogin->username ?? 'N/A';
                        ?>
                        <li class="corp-list__item annulled-item<?= $annulledIndex >= 3 ? ' d-none' : '' ?>"
                            data-nombre="<?= strtolower(Html::encode($nombreCompleto)) ?>"
                            data-usuario="<?= strtolower(Html::encode($usuarioLogin)) ?>">
                            <span>
                                <?= Html::a(
                                    Html::encode($nombreCompleto),
                                    ['user-datos/view', 'id' => $user->id],
                                    ['class' => 'corp-list__name']
                                ) ?>
                                <span class="corp-list__meta">(Usuario: <?= Html::encode($usuarioLogin) ?>)</span>
                            </span>
                            <i class="fas fa-arrow-right corp-list__arrow" aria-hidden="true"></i>
                        </li>
                    <?php $annulledIndex++;
                    endforeach; ?>
                </ul>
                <?php if (count($annulledAffiliates) > 3): ?>
                    <div class="corp-show-more">
                        <button id="ver-mas-annulled" type="button">
                            Ver más <i class="fas fa-chevron-down ms-1" aria-hidden="true"></i>
                        </button>
                        <button id="ver-menos-annulled" type="button" style="display:none;">
                            Ver menos <i class="fas fa-chevron-up ms-1" aria-hidden="true"></i>
                        </button>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p class="corp-empty-text">No hay afiliados con contratos anulados en este corporativo.</p>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php
// ── Build autocomplete sources ───────────────────────────────────────
$buildAutocomplete = function (array $users) {
    $arr = [];
    foreach ($users as $user) {
        $nombreCompleto = trim(($user->nombres ?? '') . ' ' . ($user->apellidos ?? ''));
        if ($nombreCompleto === '') {
            $nombreCompleto = $user->user->username ?? $user->userLogin->username ?? 'Sin nombre';
        }
        $usuarioLogin = $user->user->username ?? $user->userLogin->username ?? 'N/A';
        $arr[] = [
            'label' => $nombreCompleto . ' (' . $usuarioLogin . ')',
            'value' => $nombreCompleto,
            'id'    => $user->id,
        ];
    }
    return $arr;
};

$activeJson   = json_encode($buildAutocomplete($activeAffiliates));
$annulledJson = json_encode($buildAutocomplete($annulledAffiliates));

$clinicasArray = [];
foreach ($model->clinicas as $clinica) {
    $clinicasArray[] = [
        'label' => ($clinica->nombre ?? '') . ' (RIF: ' . ($clinica->rif ?? '') . ')',
        'value' => $clinica->nombre ?? '',
        'id'    => $clinica->id,
    ];
}
$clinicasJson = json_encode($clinicasArray);

$empleadoViewUrl = json_encode(Url::to(['/user-datos/view', 'id' => '__id__']));

$this->registerJsFile('@web/js/jquery-ui/jquery-ui.min.js', [
    'depends' => [\yii\web\YiiAsset::class],
]);

$this->registerJs(<<<JS
$(function() {
    var empleadoViewUrl = {$empleadoViewUrl};

    function bindAffiliatePanel(cfg) {
        var inputSel   = cfg.inputSel;
        var itemSel    = cfg.itemSel;
        var moreBtn    = cfg.moreBtn;
        var lessBtn    = cfg.lessBtn;
        var source     = cfg.source;

        if ($.fn.autocomplete) {
            $(inputSel).autocomplete({
                minLength: 1,
                source: source,
                select: function(event, ui) {
                    window.location.href = empleadoViewUrl.replace('__id__', encodeURIComponent(ui.item.id));
                }
            });
        }
        $(inputSel).on('input', function() {
            var val = $(this).val().toLowerCase();
            var hasQuery = val.length > 0;
            $(itemSel).each(function(index) {
                var nombre  = $(this).data('nombre')  || '';
                var usuario = $(this).data('usuario') || '';
                var matches = !hasQuery || nombre.indexOf(val) !== -1 || usuario.indexOf(val) !== -1;
                $(this).toggle(matches);
                if (!hasQuery && index >= 3) {
                    $(this).addClass('d-none');
                } else {
                    $(this).removeClass('d-none');
                }
            });
            $(moreBtn).toggle(!hasQuery && $(itemSel).length > 3);
            $(lessBtn).hide();
        });

        $(moreBtn).on('click', function() {
            $(itemSel + '.d-none').removeClass('d-none');
            $(this).hide();
            $(lessBtn).show();
        });
        $(lessBtn).on('click', function() {
            $(itemSel).each(function(index) {
                if (index >= 3) $(this).addClass('d-none');
            });
            $(this).hide();
            $(moreBtn).show();
        });
    }

    bindAffiliatePanel({
        inputSel: '#typeahead-active',
        itemSel:  '#active-list .active-item',
        moreBtn:  '#ver-mas-active',
        lessBtn:  '#ver-menos-active',
        source:   {$activeJson}
    });

    bindAffiliatePanel({
        inputSel: '#typeahead-annulled',
        itemSel:  '#annulled-list .annulled-item',
        moreBtn:  '#ver-mas-annulled',
        lessBtn:  '#ver-menos-annulled',
        source:   {$annulledJson}
    });

    var clinicas = {$clinicasJson};
    if ($.fn.autocomplete) {
        $('#typeahead-clinicas').autocomplete({
            minLength: 1,
            source: clinicas,
            select: function(event, ui) {
                window.location.href = '/rm-clinica/view?id=' + ui.item.id;
            }
        });
    }
    $('#typeahead-clinicas').on('input', function() {
        var val = $(this).val().toLowerCase();
        $('.clinica-item').each(function() {
            var nombre = $(this).data('nombre') || '';
            var rif    = $(this).data('rif')    || '';
            if (nombre.indexOf(val) !== -1 || rif.indexOf(val) !== -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    $('#ver-mas-clinicas').on('click', function() {
        $('#clinicas-list .clinica-item.d-none').removeClass('d-none');
        $(this).hide();
        $('#ver-menos-clinicas').show();
    });
    $('#ver-menos-clinicas').on('click', function() {
        $('#clinicas-list .clinica-item').each(function(index) {
            if (index >= 3) $(this).addClass('d-none');
        });
        $(this).hide();
        $('#ver-mas-clinicas').show();
        $('html, body').animate({ scrollTop: $('#clinicas-list').offset().top - 100 }, 300);
    });
});
JS);
?>