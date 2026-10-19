<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ContractHelper;

/** @var yii\web\View $this */
/** @var app\models\Corporativo $corporativo */
/** @var array $planBreakdown */
/** @var int $totalEmpleados */
/** @var int $sinPlanCount */
/** @var float $grandTotal */
/** @var string $suggestedIni */
/** @var string $suggestedVen */
/** @var bool $hasCurrentYear */
/** @var \app\models\ContratoCorporativo|null $previousContract */
/** @var array|null $previousGroup */
/** @var app\models\UserDatos[] $carriedAffiliates */
/** @var array $carriedByPlan */
/** @var int $carriedTotal */
/** @var int $contractCount */

$formAction = Url::to(['create-corporate-contract', 'id' => $corporativo->id]);

$startDateValue = !empty($previousContract) ? $suggestedIni : '';
?>

<style>
    /* ══════════════════════════════════════════════════════════════ */
    /* CORPORATE CONTRACT DRAWER                                      */
    /* ══════════════════════════════════════════════════════════════ */

    /* ─── Overlay ─────────────────────────────────────────────── */
    .corp-drawer-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(3px);
        z-index: 1040;
        opacity: 0;
        transition: opacity 0.28s ease;
    }

    .corp-drawer-overlay:not(.closing) {
        opacity: 1;
    }

    /* ─── Drawer shell ───────────────────────────────────────── */
    .corp-drawer {
        position: fixed;
        top: 0;
        right: 0;
        width: 100%;
        max-width: 640px;
        height: 100vh;
        background: #fff;
        box-shadow: -12px 0 40px rgba(15, 23, 42, 0.18);
        z-index: 1045;
        display: flex;
        flex-direction: column;
        transform: translateX(0);
        transition: transform 0.28s ease;
    }

    .corp-drawer.closing {
        transform: translateX(105%);
    }

    /* ─── Header ─────────────────────────────────────────────── */
    .corp-drawer__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 22px 28px;
        background: linear-gradient(135deg, #991b1b 0%, #dc2626 50%, #ef4444 100%);
        color: #fff;
        border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        flex-shrink: 0;
    }

    .corp-drawer__header-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.18);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255, 255, 255, 0.25);
        font-size: 1.25rem;
        margin-right: 14px;
        flex-shrink: 0;
    }

    .corp-drawer__title-group {
        display: flex;
        align-items: center;
        flex: 1;
        min-width: 0;
    }

    .corp-drawer__title {
        font-size: 1.35rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.2;
        color: #fff;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
    }

    .corp-drawer__subtitle {
        font-size: 0.95rem;
        opacity: 0.9;
        margin-top: 2px;
    }

    .corp-drawer__close {
        background: transparent;
        border: none;
        color: #fff;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        font-size: 1.5rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background 0.15s ease;
        flex-shrink: 0;
    }

    .corp-drawer__close:hover {
        background: rgba(255, 255, 255, 0.18);
    }

    /* ─── Body ───────────────────────────────────────────────── */
    .corp-drawer__body {
        flex: 1;
        overflow-y: auto;
        padding: 26px 28px 32px;
        font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
        color: #323130;
        font-size: 16px;
        line-height: 1.55;
    }

    /* ─── Drawer info banners ────────────────────────────────── */
    .selector-banner {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 16px 18px;
        border-radius: 12px;
        margin-bottom: 20px;
        font-size: 1rem;
        line-height: 1.55;
        border: 1px solid;
    }

    .selector-banner--info {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: #1e3a8a;
    }

    .selector-banner--info .selector-banner__icon {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    }

    .selector-banner--first {
        background: #ecfdf5;
        border-color: #a7f3d0;
        color: #065f46;
    }

    .selector-banner--first .selector-banner__icon {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .selector-banner--neutral {
        background: #f3f2f1;
        border-color: #e8e6e3;
        color: #605e5c;
    }

    .selector-banner--neutral .selector-banner__icon {
        background: linear-gradient(135deg, #9ca3af 0%, #4b5563 100%);
    }

    .selector-banner__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        color: #fff;
        font-size: 1.1rem;
        flex-shrink: 0;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
    }

    .selector-banner__content {
        flex: 1;
        min-width: 0;
    }

    .selector-banner__title {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .selector-banner__body {
        font-size: 1rem;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        margin-bottom: 8px;
    }

    .selector-banner__sep {
        color: #93c5fd;
        font-weight: 700;
    }

    .selector-banner__note {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        font-size: 0.95rem;
        padding-top: 8px;
        border-top: 1px dashed rgba(30, 58, 138, 0.2);
        margin-top: 4px;
    }

    .selector-banner__note i {
        color: #2563eb;
    }

    .selector-banner__hint {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.9rem;
        font-style: italic;
        color: #2563eb;
        margin-top: 6px;
    }

    .selector-banner__hint i {
        font-size: 0.85rem;
    }

    .selector-banner__plan-chips {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-left: 4px;
    }

    .selector-banner__chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        background: #fff;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 700;
        color: #1e40af;
        white-space: nowrap;
    }

    .selector-banner__chip-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 18px;
        padding: 0 6px;
        background: #2563eb;
        color: #fff;
        font-size: 0.75rem;
        font-weight: 700;
        border-radius: 6px;
        font-variant-numeric: tabular-nums;
    }

    /* ─── Section titles inside the drawer ───────────────────── */
    .selector-section {
        margin-bottom: 22px;
    }

    .selector-section__title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.05rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.9px;
        color: #605e5c;
        margin: 0 0 12px 0;
    }

    .selector-section__title i {
        color: #8a8886;
        font-size: 1rem;
    }

    /* ─── Plan breakdown table ───────────────────────────────── */
    .selector-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border: 1px solid #f3f2f1;
        border-radius: 10px;
        overflow: hidden;
        font-size: 1rem;
    }

    .selector-table thead th {
        background: linear-gradient(135deg, #1a237e 0%, #0d47a1 50%, #1565c0 100%);
        color: #fff !important;
        padding: 12px 16px;
        text-align: left;
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        white-space: nowrap;
    }

    .selector-table tbody td {
        padding: 14px 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f3f2f1;
        font-size: 1rem;
    }

    .selector-table tbody tr:last-child td {
        border-bottom: none;
    }

    .selector-table tbody tr:hover {
        background: #fafbfc;
    }

    .selector-table__plan-name {
        font-weight: 700;
        color: #1a237e;
    }

    .selector-table__clinic {
        font-size: 0.9rem;
        color: #8a8886;
        margin-top: 2px;
    }

    .selector-table__num {
        text-align: right;
        font-variant-numeric: tabular-nums;
        font-weight: 600;
        color: #323130;
    }

    .selector-table__subtotal {
        color: #107c10;
        font-weight: 700;
    }

    .selector-table tfoot td {
        padding: 14px 16px;
        background: #faf9f8;
        border-top: 2px solid #e8e6e3;
        font-weight: 700;
    }

    .selector-table tfoot .total-label {
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #605e5c;
    }

    .selector-table tfoot .total-value {
        text-align: right;
        font-size: 1.35rem;
        font-weight: 800;
        color: #107c10;
        font-variant-numeric: tabular-nums;
    }

    /* ─── Warning chip for employees without plan ────────────── */
    .selector-warning {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        background: #fef3c7;
        border: 1px solid #fde68a;
        border-radius: 10px;
        color: #b45309;
        font-size: 0.95rem;
        font-weight: 600;
        margin-top: 12px;
    }

    .selector-warning i {
        font-size: 1.1rem;
    }

    /* ═══════════════════════════════════════════════════════════ */
    /* DATES SECTION                                               */
    /* ═══════════════════════════════════════════════════════════ */
    .selector-date-grid {
        display: grid;
        grid-template-columns: 1.1fr 1fr;
        gap: 18px;
        align-items: stretch;
    }

    @media (max-width: 560px) {
        .selector-date-grid {
            grid-template-columns: 1fr;
        }
    }

    .selector-date-field {
        display: flex;
        flex-direction: column;
    }

    .selector-date-field label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 1rem;
        font-weight: 700;
        color: #1a237e;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.7px;
    }

    /* Hint chips next to labels */
    .selector-date-field__hint {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-left: 6px;
        padding: 2px 8px;
        border-radius: 8px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: #eef2ff;
        color: #4338ca;
        border: 1px solid #c7d2fe;
        vertical-align: middle;
        white-space: nowrap;
    }

    .selector-date-field__hint--required {
        background: #fef2f2;
        color: #b91c1c;
        border-color: #fecaca;
        animation: requiredPulse 2s ease-in-out infinite;
    }

    @keyframes requiredPulse {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(220, 38, 38, 0);
        }

        50% {
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.15);
        }
    }

    .selector-date-field__hint--auto {
        background: #ecfdf5;
        color: #047857;
        border-color: #a7f3d0;
    }

    /* ═══════════════════════════════════════════════════════════ */
    /* START DATE — icon on the RIGHT                              */
    /* ═══════════════════════════════════════════════════════════ */
    .selector-start-date-wrap {
        position: relative;
        display: flex;
        align-items: center;
        padding: 4px 6px 4px 16px;
        /* left padding for text, right for icon */
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 2px solid #93c5fd;
        border-radius: 14px;
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.08);
        cursor: pointer;
    }

    .selector-start-date-wrap:hover {
        border-color: #60a5fa;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.15);
    }

    .selector-start-date-wrap:focus-within {
        border-color: #2563eb;
        background: #fff;
        box-shadow: 0 0 0 5px rgba(37, 99, 235, 0.18);
        transform: translateY(-1px);
    }

    .selector-start-date-wrap.is-empty {
        border-color: #fbbf24;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        animation: startDateAttention 2.4s ease-in-out infinite;
    }

    .selector-start-date-wrap.is-empty:focus-within {
        border-color: #2563eb;
        background: #fff;
        animation: none;
    }

    @keyframes startDateAttention {

        0%,
        100% {
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.10);
            border-color: #fbbf24;
        }

        50% {
            box-shadow: 0 4px 18px rgba(245, 158, 11, 0.35);
            border-color: #f59e0b;
        }
    }

    /* The native input */
    .selector-start-date-input {
        flex: 1;
        min-width: 0;
        padding: 14px 4px 14px 0;
        border: none;
        background: transparent;
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e3a8a;
        font-family: inherit;
        font-variant-numeric: tabular-nums;
        letter-spacing: 0.3px;
        cursor: pointer;
    }

    .selector-start-date-input:focus {
        outline: none;
    }

    /* Hide the native calendar indicator */
    .selector-start-date-input::-webkit-calendar-picker-indicator {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        background: transparent;
        color: transparent;
    }

    /* Icon tile — RIGHT side of the input */
    .selector-start-date-wrap__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: #fff;
        font-size: 1.2rem;
        flex-shrink: 0;
        box-shadow: 0 3px 8px rgba(37, 99, 235, 0.35);
        margin-left: 12px;
        /* space between input and icon */
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
    }

    .selector-start-date-wrap__icon:hover {
        transform: translateY(-1px);
        box-shadow: 0 5px 12px rgba(37, 99, 235, 0.45);
    }

    .selector-start-date-wrap__icon:active {
        transform: translateY(0);
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.4);
    }

    .selector-start-date-wrap.is-empty .selector-start-date-wrap__icon {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        box-shadow: 0 3px 8px rgba(245, 158, 11, 0.4);
    }

    .selector-start-date-wrap.is-empty .selector-start-date-wrap__icon:hover {
        box-shadow: 0 5px 12px rgba(245, 158, 11, 0.5);
    }

    /* Helper text below the input */
    .selector-start-date-help {
        margin-top: 8px;
        font-size: 0.9rem;
        color: #605e5c;
        display: flex;
        align-items: center;
        gap: 6px;
        font-style: italic;
    }

    .selector-start-date-help i {
        color: #2563eb;
    }

    .selector-start-date-help--empty {
        color: #b45309;
        font-style: normal;
        font-weight: 600;
    }

    .selector-start-date-help--empty i {
        color: #d97706;
    }

    /* ═══════════════════════════════════════════════════════════ */
    /* DERIVED EXPIRY DATE CARD — icon on the RIGHT                */
    /* ═══════════════════════════════════════════════════════════ */
    .selector-derived-date {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 18px;
        border-radius: 14px;
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        border: 2px dashed #6ee7b7;
        transition: background 0.3s ease, transform 0.15s ease, box-shadow 0.3s ease;
        position: relative;
        overflow: hidden;
        min-height: 74px;
        flex: 1;
    }

    .selector-derived-date.is-placeholder {
        background: linear-gradient(135deg, #f3f2f1 0%, #e8e6e3 100%);
        border-color: #c8c6c4;
        border-style: dashed;
    }

    .selector-derived-date.is-placeholder .selector-derived-date__icon {
        background: linear-gradient(135deg, #9ca3af 0%, #4b5563 100%);
        box-shadow: 0 3px 8px rgba(107, 114, 128, 0.25);
    }

    .selector-derived-date.is-placeholder .selector-derived-date__label,
    .selector-derived-date.is-placeholder .selector-derived-date__value,
    .selector-derived-date.is-placeholder .selector-derived-date__note {
        color: #8a8886;
    }

    .selector-derived-date.is-updated {
        animation: derivedPulse 0.6s ease-out;
    }

    @keyframes derivedPulse {
        0% {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        }

        40% {
            background: linear-gradient(135deg, #a7f3d0 0%, #6ee7b7 100%);
            transform: scale(1.015);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.25);
        }

        100% {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            transform: scale(1);
        }
    }

    /* Body of the derived card — flex-grow so the icon sits on the right */
    .selector-derived-date__body {
        flex: 1;
        min-width: 0;
    }

    /* Icon tile of the derived card — RIGHT side */
    .selector-derived-date__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: linear-gradient(135deg, #10b981 0%, #047857 100%);
        color: #fff;
        font-size: 1.15rem;
        flex-shrink: 0;
        box-shadow: 0 3px 8px rgba(16, 185, 129, 0.35);
    }

    .selector-derived-date__label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #047857;
        margin-bottom: 2px;
    }

    .selector-derived-date__value {
        font-size: 1.4rem;
        font-weight: 800;
        color: #065f46;
        line-height: 1.1;
        font-variant-numeric: tabular-nums;
        letter-spacing: -0.3px;
    }

    .selector-derived-date__note {
        font-size: 0.8rem;
        color: #047857;
        opacity: 0.8;
        margin-top: 4px;
        font-style: italic;
    }

    /* ─── Footer / actions ──────────────────────────────────── */
    .corp-drawer__footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding: 18px 28px;
        background: #faf9f8;
        border-top: 1px solid #f3f2f1;
        flex-shrink: 0;
        flex-wrap: wrap;
    }

    .drawer-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 13px 22px;
        border-radius: 10px;
        font-size: 1.05rem;
        font-weight: 700;
        text-decoration: none;
        border: 2px solid transparent;
        cursor: pointer;
        transition: all 0.15s ease;
        white-space: nowrap;
        line-height: 1.2;
    }

    .drawer-btn--primary {
        background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
        color: #fff;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35);
    }

    .drawer-btn--primary:hover {
        background: linear-gradient(135deg, #b91c1c 0%, #7f1d1d 100%);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(220, 38, 38, 0.5);
        text-decoration: none;
    }

    .drawer-btn--primary:disabled {
        background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%);
        cursor: not-allowed;
        box-shadow: none;
        opacity: 0.85;
    }

    .drawer-btn--ghost {
        background: #fff;
        color: #4338ca;
        border-color: #c7d2fe;
    }

    .drawer-btn--ghost:hover {
        background: #eef2ff;
        color: #312e81;
        border-color: #818cf8;
        transform: translateY(-2px);
        text-decoration: none;
    }

    /* ─── Responsive ────────────────────────────────────────── */
    @media (max-width: 576px) {
        .corp-drawer__header {
            padding: 18px 20px;
        }

        .corp-drawer__body {
            padding: 20px 20px 24px;
        }

        .corp-drawer__footer {
            padding: 16px 20px;
        }

        .corp-drawer__title {
            font-size: 1.15rem;
        }

        .selector-banner {
            padding: 12px 14px;
            gap: 10px;
        }

        .selector-banner__icon {
            width: 34px;
            height: 34px;
            font-size: 1rem;
        }

        .selector-banner__title {
            font-size: 1rem;
        }

        .selector-banner__body,
        .selector-banner__note {
            font-size: 0.9rem;
        }

        .selector-start-date-wrap {
            padding: 3px 5px 3px 12px;
        }

        .selector-start-date-wrap__icon {
            width: 38px;
            height: 38px;
            font-size: 1rem;
            margin-left: 8px;
        }

        .selector-start-date-input {
            font-size: 1.05rem;
            padding: 12px 4px 12px 0;
        }

        .selector-derived-date {
            padding: 12px 14px;
            gap: 10px;
            min-height: 66px;
        }

        .selector-derived-date__icon {
            width: 34px;
            height: 34px;
            font-size: 1rem;
        }

        .selector-derived-date__value {
            font-size: 1.1rem;
        }

        .drawer-btn {
            padding: 11px 18px;
            font-size: 1rem;
        }
    }
</style>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- DRAWER                                                       -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div id="corporateContractDrawer"
    class="corp-drawer closing"
    role="dialog"
    aria-modal="true"
    aria-labelledby="corp-drawer-title"
    onclick="event.stopPropagation();">

    <!-- ─── Header ──────────────────────────────────────────── -->
    <div class="corp-drawer__header">
        <div class="corp-drawer__title-group">
            <div class="corp-drawer__header-icon">
                <i class="fas fa-file-contract" aria-hidden="true"></i>
            </div>
            <div>
                <h2 class="corp-drawer__title" id="corp-drawer-title">
                    Nuevo Contrato Colectivo
                </h2>
                <div class="corp-drawer__subtitle">
                    <?= Html::encode($corporativo->nombre) ?>
                    <?php if (!empty($corporativo->rif)): ?>
                        · RIF <?= Html::encode($corporativo->rif) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <button type="button"
            class="corp-drawer__close"
            aria-label="Cerrar"
            onclick="closeCorporateContractDrawer(event);">
            <i class="fas fa-times" aria-hidden="true"></i>
        </button>
    </div>

    <!-- ─── Form ────────────────────────────────────────────── -->
    <form method="post"
        action="<?= $formAction ?>"
        id="corporate-contract-form"
        style="display:flex;flex-direction:column;flex:1;overflow:hidden;">

        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

        <!-- ─── Body ────────────────────────────────────────── -->
        <div class="corp-drawer__body">

            <!-- ══════════════════════════════════════════════════ -->
            <!-- BANNER — Three variants                            -->
            <!-- ══════════════════════════════════════════════════ -->

            <?php if (!empty($previousContract)): ?>
                <div class="selector-banner selector-banner--info">
                    <div class="selector-banner__icon">
                        <i class="fas fa-history" aria-hidden="true"></i>
                    </div>
                    <div class="selector-banner__content">
                        <div class="selector-banner__title">
                            Contrato anterior detectado
                        </div>
                        <div class="selector-banner__body">
                            <strong><?= Html::encode($previousContract->nrocontrato) ?></strong>
                            <span class="selector-banner__sep">·</span>
                            Vencido el
                            <strong>
                                <?= $previousContract->fecha_ven
                                    ? date('d/m/Y', strtotime($previousContract->fecha_ven))
                                    : 'N/A' ?>
                            </strong>
                            <span class="selector-banner__sep">·</span>
                            Estatus:
                            <?= ContractHelper::getCorporateContractStatusBadge($previousContract) ?>
                        </div>
                        <div class="selector-banner__note">
                            <i class="fas fa-users" aria-hidden="true"></i>
                            El nuevo contrato <strong>reutilizará los
                                <?= (int)$carriedTotal ?> afiliados</strong>
                            del contrato anterior.
                            <?php if (!empty($carriedByPlan) && count($carriedByPlan) > 1): ?>
                                <span class="selector-banner__plan-chips">
                                    <?php foreach ($carriedByPlan as $planId => $count): ?>
                                        <?php
                                        $planName = 'Plan #' . $planId;
                                        foreach ($planBreakdown as $pb) {
                                            if ((int)$pb['plan']->id === (int)$planId) {
                                                $planName = $pb['plan']->nombre;
                                                break;
                                            }
                                        }
                                        ?>
                                        <span class="selector-banner__chip">
                                            <?= Html::encode($planName) ?>
                                            <span class="selector-banner__chip-count"><?= (int)$count ?></span>
                                        </span>
                                    <?php endforeach; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="selector-banner__hint">
                            <i class="fas fa-calendar-check" aria-hidden="true"></i>
                            La fecha de inicio se ha pre-cargado con el día siguiente al vencimiento
                            para evitar solapamiento.
                        </div>
                    </div>
                </div>

            <?php elseif (empty($previousContract) && empty($hasCurrentYear) && ($contractCount ?? 0) === 0): ?>
                <div class="selector-banner selector-banner--first">
                    <div class="selector-banner__icon">
                        <i class="fas fa-plus-circle" aria-hidden="true"></i>
                    </div>
                    <div class="selector-banner__content">
                        <div class="selector-banner__title">
                            Primer contrato para este corporativo
                        </div>
                        <div class="selector-banner__body">
                            Este corporativo aún no tiene contratos registrados. Se creará
                            uno nuevo con los
                            <strong><?= (int)$totalEmpleados ?> afiliados</strong>
                            actualmente vinculados.
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="selector-banner selector-banner--neutral">
                    <div class="selector-banner__icon">
                        <i class="fas fa-info-circle" aria-hidden="true"></i>
                    </div>
                    <div class="selector-banner__content">
                        <div class="selector-banner__title">
                            Nuevo contrato
                        </div>
                        <div class="selector-banner__body">
                            Este corporativo <strong>no tiene contratos vigentes</strong>
                            (los anteriores fueron anulados). Se creará uno nuevo con los
                            <strong><?= (int)$totalEmpleados ?> afiliados</strong>
                            actualmente vinculados.
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ══════════════════════════════════════════════════ -->
            <!-- DATES                                              -->
            <!-- ══════════════════════════════════════════════════ -->
            <div class="selector-section">
                <h3 class="selector-section__title">
                    <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                    Vigencia del contrato
                </h3>

                <div class="selector-date-grid">

                    <!-- ─── START DATE — icon on the RIGHT ─── -->
                    <div class="selector-date-field">
                        <label for="fecha_ini">
                            <i class="fas fa-play-circle" style="color: #2563eb;" aria-hidden="true"></i>
                            Fecha de Inicio
                            <span class="selector-date-field__hint selector-date-field__hint--required">
                                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                                requerida
                            </span>
                        </label>

                        <div class="selector-start-date-wrap <?= $startDateValue === '' ? 'is-empty' : '' ?>"
                            id="start-date-wrap">
                            <input type="date"
                                id="fecha_ini"
                                name="fecha_ini"
                                class="selector-start-date-input"
                                value="<?= Html::encode($startDateValue) ?>"
                                required
                                autocomplete="off">
                            <div class="selector-start-date-wrap__icon"
                                id="start-date-icon"
                                role="button"
                                tabindex="0"
                                title="Haga clic para seleccionar una fecha"
                                aria-label="Abrir selector de fecha">
                                <i class="fas fa-calendar-day" aria-hidden="true"></i>
                            </div>
                        </div>

                        <div class="selector-start-date-help <?= $startDateValue === '' ? 'selector-start-date-help--empty' : '' ?>"
                            id="start-date-help">
                            <?php if ($startDateValue === ''): ?>
                                <i class="fas fa-hand-point-up" aria-hidden="true"></i>
                                Seleccione la fecha en que comenzará la cobertura.
                            <?php else: ?>
                                <i class="fas fa-info-circle" aria-hidden="true"></i>
                                La fecha de vencimiento se calcula automáticamente (+1 año).
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- ─── END DATE — icon on the RIGHT ─── -->
                    <div class="selector-date-field">
                        <label>
                            <i class="fas fa-stop-circle" style="color: #059669;" aria-hidden="true"></i>
                            Fecha de Vencimiento
                            <span class="selector-date-field__hint selector-date-field__hint--auto">
                                <i class="fas fa-magic" aria-hidden="true"></i>
                                automática
                            </span>
                        </label>

                        <div class="selector-derived-date <?= $startDateValue === '' ? 'is-placeholder' : '' ?>"
                            id="derived-date-display">
                            <div class="selector-derived-date__body">
                                <div class="selector-derived-date__label">
                                    Vence el
                                </div>
                                <div class="selector-derived-date__value" id="derived-date-value">
                                    <?php if ($startDateValue === ''): ?>
                                        — — —
                                    <?php else: ?>
                                        <?= date('d M Y', strtotime($suggestedVen)) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="selector-derived-date__note" id="derived-date-note">
                                    <?php if ($startDateValue === ''): ?>
                                        Seleccione la fecha de inicio
                                    <?php else: ?>
                                        Exactamente un año después de la fecha de inicio
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="selector-derived-date__icon">
                                <i class="fas fa-calendar-check" aria-hidden="true"></i>
                            </div>
                        </div>

                        <!-- Hidden input carries the value to the server -->
                        <input type="hidden"
                            id="fecha_ven"
                            name="fecha_ven"
                            value="<?= Html::encode($suggestedVen) ?>">
                    </div>

                </div>
            </div>

            <!-- ══════════════════════════════════════════════════ -->
            <!-- PLAN BREAKDOWN                                     -->
            <!-- ══════════════════════════════════════════════════ -->
            <div class="selector-section">
                <h3 class="selector-section__title">
                    <i class="fas fa-layer-group" aria-hidden="true"></i>
                    Planes incluidos en el contrato
                </h3>

                <?php if (empty($planBreakdown)): ?>
                    <div class="selector-warning">
                        <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                        Ningún afiliado de este corporativo tiene un plan asignado.
                        No se puede generar el contrato.
                    </div>
                <?php else: ?>
                    <table class="selector-table">
                        <thead>
                            <tr>
                                <th>Plan</th>
                                <th style="text-align:right;">Afiliados</th>
                                <th style="text-align:right;">Precio</th>
                                <th style="text-align:right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($planBreakdown as $row): ?>
                                <tr>
                                    <td>
                                        <div class="selector-table__plan-name">
                                            <?= Html::encode($row['plan']->nombre) ?>
                                        </div>
                                        <?php if ($row['clinica']): ?>
                                            <div class="selector-table__clinic">
                                                <i class="fas fa-hospital" aria-hidden="true"></i>
                                                <?= Html::encode($row['clinica']->nombre) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="selector-table__num"><?= (int)$row['count'] ?></td>
                                    <td class="selector-table__num">
                                        $<?= number_format((float)$row['plan']->precio, 2, ',', '.') ?>
                                    </td>
                                    <td class="selector-table__num selector-table__subtotal">
                                        $<?= number_format((float)$row['subtotal'], 2, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="total-label">
                                    Total mensual estimado
                                </td>
                                <td class="total-value">
                                    $<?= number_format($grandTotal, 2, ',', '.') ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    <?php if ($sinPlanCount > 0): ?>
                        <div class="selector-warning">
                            <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                            <?= (int)$sinPlanCount ?>
                            afiliado<?= $sinPlanCount === 1 ? '' : 's' ?>
                            no tiene<?= $sinPlanCount === 1 ? '' : 'n' ?>
                            plan asignado y será<?= $sinPlanCount === 1 ? '' : 'n' ?>
                            excluido<?= $sinPlanCount === 1 ? '' : 's' ?>
                            del contrato.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

        </div>

        <!-- ─── Footer / actions ───────────────────────────── -->
        <div class="corp-drawer__footer">
            <button type="button"
                class="drawer-btn drawer-btn--ghost"
                onclick="closeCorporateContractDrawer(event);">
                <i class="fas fa-times" aria-hidden="true"></i>
                Cancelar
            </button>
            <button type="submit"
                class="drawer-btn drawer-btn--primary"
                id="corp-drawer-submit">
                <i class="fas fa-file-contract" aria-hidden="true"></i>
                Crear Contrato
            </button>
        </div>

    </form>
</div>

<script>
    (function() {
        'use strict';

        var iniInput = document.getElementById('fecha_ini');
        var iniIcon = document.getElementById('start-date-icon');
        var venHidden = document.getElementById('fecha_ven');
        var venDisplay = document.getElementById('derived-date-value');
        var venNote = document.getElementById('derived-date-note');
        var venCard = document.getElementById('derived-date-display');
        var startWrap = document.getElementById('start-date-wrap');
        var startHelp = document.getElementById('start-date-help');
        var form = document.getElementById('corporate-contract-form');

        // ── Spanish month abbreviations ─────────────────────────────
        var MONTHS = [
            'ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN',
            'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'
        ];

        function formatPretty(dateObj) {
            var d = String(dateObj.getDate()).padStart(2, '0');
            var m = MONTHS[dateObj.getMonth()];
            var y = dateObj.getFullYear();
            return d + ' ' + m + ' ' + y;
        }

        function computeExpiry(iniStr) {
            if (!iniStr) return null;

            var parts = iniStr.split('-');
            if (parts.length !== 3) return null;

            var y = parseInt(parts[0], 10);
            var m = parseInt(parts[1], 10);
            var d = parseInt(parts[2], 10);

            // start + 1 year - 1 day (leap-year safe)
            var expiry = new Date(y + 1, m - 1, d);
            expiry.setDate(expiry.getDate() - 1);

            var yy = expiry.getFullYear();
            var mm = String(expiry.getMonth() + 1).padStart(2, '0');
            var dd = String(expiry.getDate()).padStart(2, '0');

            return {
                iso: yy + '-' + mm + '-' + dd,
                pretty: formatPretty(expiry)
            };
        }

        function refresh() {
            var val = iniInput.value;

            if (!val) {
                if (startWrap) startWrap.classList.add('is-empty');

                if (venDisplay) venDisplay.textContent = '— — —';
                if (venNote) {
                    venNote.textContent = 'Seleccione la fecha de inicio';
                }
                if (venCard) {
                    venCard.classList.add('is-placeholder');
                    venCard.classList.remove('is-updated');
                }
                if (startHelp) {
                    startHelp.classList.add('selector-start-date-help--empty');
                    startHelp.innerHTML =
                        '<i class="fas fa-hand-point-up" aria-hidden="true"></i> ' +
                        'Seleccione la fecha en que comenzará la cobertura.';
                }
                venHidden.value = '';
                return;
            }

            if (startWrap) startWrap.classList.remove('is-empty');
            if (venCard) venCard.classList.remove('is-placeholder');

            var result = computeExpiry(val);
            if (!result) return;

            var changed = venHidden.value !== result.iso;

            venHidden.value = result.iso;
            venDisplay.textContent = result.pretty;

            if (venNote) {
                venNote.textContent = 'Exactamente un año después de la fecha de inicio';
            }
            if (startHelp) {
                startHelp.classList.remove('selector-start-date-help--empty');
                startHelp.innerHTML =
                    '<i class="fas fa-info-circle" aria-hidden="true"></i> ' +
                    'La fecha de vencimiento se calcula automáticamente (+1 año).';
            }

            if (changed && venCard) {
                venCard.classList.remove('is-updated');
                void venCard.offsetWidth;
                venCard.classList.add('is-updated');
                setTimeout(function() {
                    venCard.classList.remove('is-updated');
                }, 700);
            }
        }

        if (iniInput) {
            refresh();
            iniInput.addEventListener('input', refresh);
            iniInput.addEventListener('change', refresh);
        }

        function openPicker() {
            if (!iniInput) return;

            if (typeof iniInput.showPicker === 'function') {
                try {
                    iniInput.showPicker();
                    return;
                } catch (err) {
                    // fall through
                }
            }

            iniInput.focus();
            iniInput.click();
        }

        if (iniIcon) {
            iniIcon.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                openPicker();
            });
            iniIcon.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    openPicker();
                }
            });
        }

        if (form) {
            var submitted = false;
            form.addEventListener('submit', function(e) {
                if (submitted) {
                    e.preventDefault();
                    return;
                }

                if (!iniInput || !iniInput.value) {
                    e.preventDefault();
                    alert('Debe indicar la fecha de inicio del contrato.');
                    if (iniInput) iniInput.focus();
                    return;
                }

                submitted = true;
                var btn = document.getElementById('corp-drawer-submit');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando…';
                }
            });
        }
    })();
</script>