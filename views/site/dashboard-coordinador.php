<?php

/**
 * @var yii\web\View $this
 * @var int $clinicaId
 * @var string $clinicaNombre
 * @var array $kpis
 * @var array $corporateKpis
 * @var array $corporateDetails
 * @var array|null $nearestExpiring
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->title = 'Dashboard - Coordinador Clínica';
$this->params['breadcrumbs'][] = $this->title;

// Shared Fluent KPI card styles (also used by user-datos/index.php)
$this->registerCssFile('@web/css/kpi-cards.css', [
    'depends' => [\yii\bootstrap4\BootstrapAsset::class],
]);

// Safe fallbacks so the page doesn't break if the controller hasn't
// been updated yet.
$corporateKpis = $corporateKpis ?? [
    'totalCorporativos'          => 0,
    'totalAfiliadosCorporativos' => 0,
    'activos'                    => 0,
    'registrados'                => 0,
    'suspendidos'                => 0,
    'anulados'                   => 0,
];

$corporateDetails = $corporateDetails ?? [];
$nearestExpiring  = $nearestExpiring  ?? null;

$hasCorporateData = ((int)$corporateKpis['totalCorporativos']) > 0;

// ── Derived metrics ──
$tasaActividad = $kpis['total'] > 0
    ? round(($kpis['contratosActivos'] / $kpis['total']) * 100, 1)
    : 0;

// ── Tipo de Afiliación breakdown (% of ACTIVE affiliates) ──
$activeTotal       = (int)($kpis['contratosActivos'] ?? 0);
$pctIndividual     = $activeTotal > 0
    ? round((($kpis['individuales'] ?? 0) / $activeTotal) * 100, 1)
    : 0;
$pctCorporate      = $activeTotal > 0
    ? round((($kpis['corporativos'] ?? 0) / $activeTotal) * 100, 1)
    : 0;
?>

<div class="dashboard-coordinador">
    <style>
        :root {
            --primary: #2c3e50;
            --primary-dark: #1a2632;
            --secondary: #3498db;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
            --info: #3498db;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --gray: #95a5a6;
            --white: #ffffff;
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
            --card-radius: 20px;
            --transition: all 0.3s ease;
            --section-header-gradient: linear-gradient(135deg, #5b21b6 0%, #7c3aed 50%, #a78bfa 100%);
        }

        .dashboard-coordinador {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            min-height: 100vh;
            padding: 2rem;
            font-size: 17px;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* ───────────────────────────────────────────────────────── */
        /* Header                                                    */
        /* ───────────────────────────────────────────────────────── */
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 24px;
            padding: 2.25rem 2.25rem;
            margin-bottom: 2rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .dashboard-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 100%;
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.1) 0%, rgba(52, 152, 219, 0.2) 100%);
            clip-path: polygon(25% 0%, 100% 0%, 100% 100%, 0% 100%);
        }

        .header-content {
            position: relative;
            z-index: 1;
        }

        .dashboard-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .dashboard-header h1 i {
            margin-right: 14px;
        }

        .dashboard-header p {
            font-size: 1.2rem;
            opacity: 1;
            margin-bottom: 0;
            color: white;
        }

        .clinic-badge,
        .date-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.6rem 1.15rem;
            border-radius: 12px;
            font-size: 1.05rem;
            display: inline-block;
            margin-top: 1rem;
            color: white;
        }

        .date-badge {
            margin-left: 1rem;
        }

        .clinic-badge i,
        .date-badge i {
            color: white;
        }

        /* ───────────────────────────────────────────────────────── */
        /* Unified Section Header                                    */
        /* ───────────────────────────────────────────────────────── */
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 1.35rem 1.75rem;
            background: var(--section-header-gradient);
            border-top-left-radius: var(--card-radius);
            border-top-right-radius: var(--card-radius);
            flex-wrap: wrap;
        }

        .section-header h3,
        .section-header h2 {
            color: #fff !important;
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-header h3 i,
        .section-header h2 i {
            font-size: 1.35rem;
            color: #fff !important;
        }

        .section-header__badge {
            background: rgba(255, 255, 255, 0.22) !important;
            color: #fff !important;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 4px 14px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            margin-left: 8px;
        }

        .section-header__link {
            color: #fff !important;
            text-decoration: none !important;
            font-weight: 600;
            font-size: 1.05rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.15);
            transition: var(--transition);
        }

        .section-header__link:hover {
            background: rgba(255, 255, 255, 0.28);
            transform: translateX(3px);
        }

        /* ───────────────────────────────────────────────────────── */
        /* Section Panel — the unified container                     */
        /* ───────────────────────────────────────────────────────── */
        .section-panel {
            background: var(--white);
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 2rem;
            transition: var(--transition);
        }

        .section-panel:hover {
            box-shadow: 0 20px 30px -12px rgba(0, 0, 0, 0.12);
        }

        .section-panel__body {
            padding: 1.75rem;
        }

        .section-panel__body--compact {
            padding: 1rem 1.25rem 1.25rem;
        }

        /* Fluent KPI row fits snugly inside a section panel */
        .section-panel__body .kpi-cards-row {
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .section-panel__body .kpi-card {
            min-height: 112px;
        }

        /* ───────────────────────────────────────────────────────── */
        /* Alert Banner                                              */
        /* ───────────────────────────────────────────────────────── */
        .alert-banner {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe8cc 100%);
            border-left: 4px solid var(--warning);
            border-radius: 16px;
            padding: 1.15rem 1.65rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1.15rem;
        }

        .alert-banner i {
            font-size: 1.75rem;
            color: var(--warning);
        }

        .alert-banner .alert-content {
            flex: 1;
        }

        .alert-banner .alert-title {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.25rem;
            font-size: 1.15rem;
        }

        .alert-banner .alert-message {
            font-size: 1.05rem;
            color: #5c6672;
        }

        /* ───────────────────────────────────────────────────────── */
        /* Expiring-Contracts Banner — Microsoft Fluent              */
        /* ───────────────────────────────────────────────────────── */
        .expiring-banner-wrap {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            margin-bottom: 2rem;
            animation: fadeInUp 0.5s ease forwards;
        }

        .expiring-banner {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            padding: 1.15rem 1.5rem;
            border-radius: 16px;
            background: linear-gradient(135deg, #fff4e6 0%, #ffe8cc 100%);
            border-left: 6px solid #f59e0b;
            box-shadow: 0 6px 22px -10px rgba(245, 158, 11, 0.35),
                0 2px 6px rgba(0, 0, 0, 0.04);
            text-decoration: none !important;
            color: inherit !important;
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
        }

        .expiring-banner--clickable {
            cursor: pointer;
        }

        .expiring-banner--clickable::after {
            content: '';
            position: absolute;
            top: 0;
            left: -60%;
            width: 60%;
            height: 100%;
            background: linear-gradient(120deg,
                    transparent 0%,
                    rgba(255, 255, 255, 0.45) 50%,
                    transparent 100%);
            transform: skewX(-20deg);
            transition: left 0.6s ease;
            pointer-events: none;
        }

        .expiring-banner--clickable:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 32px -12px rgba(245, 158, 11, 0.5),
                0 4px 12px rgba(0, 0, 0, 0.06);
            background: linear-gradient(135deg, #ffeacc 0%, #ffdcb0 100%);
        }

        .expiring-banner--clickable:hover::after {
            left: 120%;
        }

        .expiring-banner--clickable:hover .expiring-banner__cta {
            background: #f59e0b;
            color: #ffffff !important;
        }

        .expiring-banner--clickable:hover .expiring-banner__cta i {
            transform: translateX(3px);
        }

        /* ── Pulsing hourglass icon ── */
        .expiring-banner__icon {
            position: relative;
            width: 52px;
            height: 52px;
            min-width: 52px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.35rem;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.4);
            flex-shrink: 0;
        }

        .expiring-banner__pulse {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: rgba(245, 158, 11, 0.55);
            animation: expiringPulse 2s ease-out infinite;
            pointer-events: none;
        }

        @keyframes expiringPulse {
            0% {
                transform: scale(1);
                opacity: 0.65;
            }

            70% {
                transform: scale(1.7);
                opacity: 0;
            }

            100% {
                transform: scale(1.7);
                opacity: 0;
            }
        }

        /* ── Content ── */
        .expiring-banner__content {
            flex: 1;
            min-width: 0;
        }

        .expiring-banner__title {
            font-size: 1.15rem;
            font-weight: 600;
            color: #78350f;
            margin-bottom: 0.3rem;
            line-height: 1.35;
        }

        .expiring-banner__count {
            font-size: 1.75rem;
            font-weight: 800;
            color: #b45309;
            font-variant-numeric: tabular-nums;
            margin-right: 0.35rem;
            line-height: 1;
            display: inline-block;
            vertical-align: -3px;
        }

        .expiring-banner__nearest {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.45rem;
            font-size: 1rem;
            color: #78350f;
            line-height: 1.35;
        }

        .expiring-banner__nearest i {
            color: #b45309;
        }

        .expiring-banner__nearest strong {
            color: #431407;
            font-weight: 700;
        }

        /* ── Urgency badges ── */
        .expiring-banner__badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 11px;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.2px;
            white-space: nowrap;
            line-height: 1.3;
        }

        .expiring-banner__badge--critical {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .expiring-banner__badge--warning {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .expiring-banner__badge--notice {
            background: #e0f2fe;
            color: #075985;
            border: 1px solid #bae6fd;
        }

        /* ── CTA ── */
        .expiring-banner__cta {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.15rem;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.7);
            color: #92400e !important;
            font-weight: 700;
            font-size: 0.95rem;
            border: 1px solid rgba(245, 158, 11, 0.35);
            transition: all 0.2s ease;
            flex-shrink: 0;
            white-space: nowrap;
        }

        .expiring-banner__cta i {
            transition: transform 0.2s ease;
        }

        /* ── Expired banner ── */
        .expired-banner {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.4rem;
            border-radius: 14px;
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border-left: 5px solid #ef4444;
            color: #7f1d1d;
            font-size: 1rem;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.10);
        }

        .expired-banner i {
            font-size: 1.4rem;
            color: #dc2626;
            flex-shrink: 0;
        }

        .expired-banner strong {
            color: #b91c1c;
        }

        /* ───────────────────────────────────────────────────────── */
        /* Two-column breakdown cards                                */
        /* ───────────────────────────────────────────────────────── */
        .breakdown-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .breakdown-card {
            background: var(--white);
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: var(--transition);
        }

        .breakdown-card:hover {
            box-shadow: 0 20px 30px -12px rgba(0, 0, 0, 0.12);
        }

        .breakdown-card__body {
            padding: 1.65rem 1.75rem 1.75rem;
        }

        .list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.05rem 0;
            border-bottom: 1px solid var(--light);
        }

        .list-item:last-child {
            border-bottom: none;
        }

        .list-label {
            font-weight: 600;
            color: #334155;
            font-size: 1.15rem;
        }

        .list-value {
            font-weight: 800;
            color: #1e293b;
            font-variant-numeric: tabular-nums;
            font-size: 1.35rem;
        }

        .progress-bar-container {
            margin-top: 1.25rem;
            background: var(--light);
            border-radius: 10px;
            height: 10px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .progress-bar.success {
            background: var(--success);
        }

        .progress-bar.warning {
            background: var(--warning);
        }

        .progress-bar.danger {
            background: var(--danger);
        }

        .progress-bar.info {
            background: var(--info);
        }

        .breakdown-card .sub {
            font-size: 1rem;
            color: #94a3b8;
            font-weight: 500;
        }

        /* ───────────────────────────────────────────────────────── */
        /* Tipo de Afiliación breakdown — active-scoped              */
        /* ───────────────────────────────────────────────────────── */
        .tipo-breakdown__intro {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-left: 4px solid #3b82f6;
            border-radius: 10px;
            font-size: 0.98rem;
            color: #1e40af;
            line-height: 1.4;
        }

        .tipo-breakdown__intro i {
            font-size: 1.15rem;
            color: #2563eb;
            flex-shrink: 0;
        }

        .tipo-breakdown__intro strong {
            color: #1e3a8a;
        }

        .tipo-breakdown__row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.15rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 0.75rem;
            transition: var(--transition);
            border: 1px solid transparent;
        }

        .tipo-breakdown__row--individual {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-color: #bfdbfe;
        }

        .tipo-breakdown__row--corporate {
            background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
            border-color: #ddd6fe;
        }

        .tipo-breakdown__row:hover {
            transform: translateX(3px);
            box-shadow: 0 6px 18px -8px rgba(0, 0, 0, 0.12);
        }

        .tipo-breakdown__label {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            color: #1e293b;
            font-size: 1.05rem;
        }

        .tipo-breakdown__icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .tipo-breakdown__row--individual .tipo-breakdown__icon {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .tipo-breakdown__row--corporate .tipo-breakdown__icon {
            background: #ede9fe;
            color: #6d28d9;
        }

        .tipo-breakdown__count {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 2px;
        }

        .tipo-breakdown__value {
            font-weight: 800;
            font-variant-numeric: tabular-nums;
            font-size: 1.55rem;
            line-height: 1;
        }

        .tipo-breakdown__row--individual .tipo-breakdown__value {
            color: #1d4ed8;
        }

        .tipo-breakdown__row--corporate .tipo-breakdown__value {
            color: #6d28d9;
        }

        .tipo-breakdown__pct {
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
            font-variant-numeric: tabular-nums;
        }

        .tipo-breakdown__footer {
            margin-top: 1.15rem;
            padding-top: 1.15rem;
            border-top: 1px solid var(--light);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.95rem;
        }

        .tipo-breakdown__footer-label {
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.82rem;
        }

        .tipo-breakdown__footer-total {
            font-weight: 800;
            color: #1e293b;
            font-variant-numeric: tabular-nums;
            font-size: 1.15rem;
        }

        /* ───────────────────────────────────────────────────────── */
        /* Corporate Breakdown Card                                  */
        /* ───────────────────────────────────────────────────────── */
        .corp-breakdown-card {
            background: var(--white);
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: var(--transition);
            margin-bottom: 1.75rem;
        }

        .corp-breakdown-card:hover {
            box-shadow: 0 20px 30px -12px rgba(0, 0, 0, 0.12);
        }

        .corp-breakdown-card__body {
            padding: 1.65rem 1.75rem 1.75rem;
        }

        .breakdown-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 0.95rem 0;
            border-bottom: 1px solid var(--light);
        }

        .breakdown-row:last-child {
            border-bottom: none;
        }

        .breakdown-label {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            color: #334155;
            font-size: 1.15rem;
        }

        .breakdown-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .breakdown-dot--active {
            background: #10b981;
        }

        .breakdown-dot--registered {
            background: #f59e0b;
        }

        .breakdown-dot--suspended {
            background: #6b7280;
        }

        .breakdown-dot--annulled {
            background: #ef4444;
        }

        .breakdown-value {
            font-weight: 800;
            font-size: 1.45rem;
            font-variant-numeric: tabular-nums;
        }

        .breakdown-value--active {
            color: #047857;
        }

        .breakdown-value--registered {
            color: #b45309;
        }

        .breakdown-value--suspended {
            color: #4b5563;
        }

        .breakdown-value--annulled {
            color: #b91c1c;
        }

        /* ───────────────────────────────────────────────────────── */
        /* Corporate Detail Table                                    */
        /* ───────────────────────────────────────────────────────── */
        .corp-detail-panel {
            background: var(--white);
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 2rem;
            transition: var(--transition);
        }

        .corp-detail-panel:hover {
            box-shadow: 0 20px 30px -12px rgba(0, 0, 0, 0.12);
        }

        .corp-detail-panel__body {
            padding: 1.5rem 1.75rem 1.75rem;
            overflow-x: auto;
        }

        .corp-detail-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 1.15rem;
            background: #ffffff !important;
        }

        .corp-detail-table thead tr {
            background: #f1f5f9 !important;
        }

        .corp-detail-table thead th {
            background: #f1f5f9 !important;
            color: #1e293b !important;
            -webkit-text-fill-color: #1e293b !important;
            font-size: 0.92rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.7px !important;
            padding: 18px 20px !important;
            text-align: left !important;
            border-bottom: 2px solid #cbd5e1 !important;
            border-top: none !important;
            border-left: none !important;
            border-right: none !important;
            white-space: nowrap;
            text-shadow: none !important;
        }

        .corp-detail-table thead th.text-center {
            text-align: center !important;
        }

        .corp-detail-table tbody tr.corp-detail-row {
            background: #ffffff !important;
            transition: background 0.15s ease;
            border-bottom: 1px solid #f1f5f9;
        }

        .corp-detail-table tbody tr.corp-detail-row:last-child {
            border-bottom: none;
        }

        .corp-detail-table tbody tr.corp-detail-row:hover {
            background: #f8fafc !important;
        }

        .corp-detail-table tbody tr.corp-detail-row td {
            padding: 22px 20px;
            vertical-align: middle;
            background: transparent !important;
            color: #1e293b !important;
            border-top: none !important;
            border-bottom: none !important;
            font-size: 1.15rem;
        }

        .corp-detail-row td:first-child {
            position: relative;
        }

        .corp-detail-row td:first-child::before {
            content: '';
            position: absolute;
            left: 0;
            top: 12px;
            bottom: 12px;
            width: 5px;
            border-radius: 4px;
        }

        .corp-detail-row--good td:first-child::before {
            background: #10b981;
        }

        .corp-detail-row--warn td:first-child::before {
            background: #f59e0b;
        }

        .corp-detail-row--bad td:first-child::before {
            background: #ef4444;
        }

        .corp-detail-link {
            display: block;
            text-decoration: none !important;
            color: inherit;
            padding-left: 16px;
        }

        .corp-detail-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e293b !important;
            line-height: 1.25;
            transition: color 0.15s ease;
        }

        .corp-detail-link:hover .corp-detail-name {
            color: #5b21b6 !important;
        }

        .corp-detail-rif {
            font-size: 0.95rem;
            font-weight: 500;
            color: #94a3b8 !important;
            margin-top: 5px;
            font-variant-numeric: tabular-nums;
        }

        .corp-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 60px;
            padding: 9px 18px;
            border-radius: 10px;
            font-weight: 800;
            font-size: 1.25rem;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }

        .corp-pill--active {
            background: #d1fae5 !important;
            color: #047857 !important;
            border: 1px solid #a7f3d0;
        }

        .corp-pill--registered {
            background: #fef3c7 !important;
            color: #b45309 !important;
            border: 1px solid #fde68a;
        }

        .corp-pill--suspended {
            background: #f3f4f6 !important;
            color: #4b5563 !important;
            border: 1px solid #e5e7eb;
        }

        .corp-pill--annulled {
            background: #fee2e2 !important;
            color: #b91c1c !important;
            border: 1px solid #fecaca;
        }

        .corp-pill--empty {
            color: #cbd5e1 !important;
            font-weight: 600;
            background: transparent !important;
            border: none;
            font-size: 1.25rem;
        }

        .corp-total {
            font-size: 1.45rem;
            font-weight: 800;
            color: #1e293b !important;
            font-variant-numeric: tabular-nums;
        }

        .corp-pct-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 7px;
            min-width: 110px;
        }

        .corp-pct-bar {
            width: 100%;
            height: 9px;
            background: #f1f5f9;
            border-radius: 999px;
            overflow: hidden;
        }

        .corp-pct-fill {
            height: 100%;
            border-radius: 999px;
            transition: width 0.4s ease;
        }

        .corp-pct-fill--good {
            background: linear-gradient(90deg, #10b981 0%, #047857 100%);
        }

        .corp-pct-fill--warn {
            background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
        }

        .corp-pct-fill--bad {
            background: linear-gradient(90deg, #ef4444 0%, #b91c1c 100%);
        }

        .corp-pct-text {
            font-size: 1.05rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        .corp-pct-text--good {
            color: #047857 !important;
        }

        .corp-pct-text--warn {
            color: #b45309 !important;
        }

        .corp-pct-text--bad {
            color: #b91c1c !important;
        }

        /* ───────────────────────────────────────────────────────── */
        /* Quick Actions                                             */
        /* ───────────────────────────────────────────────────────── */
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.35rem;
        }

        .quick-action-card {
            background: #f8fafc;
            border: 1px solid #e8eaee;
            border-radius: 16px;
            padding: 1.45rem 1.55rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .quick-action-card:hover {
            background: #ffffff;
            transform: translateY(-4px);
            box-shadow: 0 16px 28px -12px rgba(0, 0, 0, 0.12);
        }

        .quick-action {
            display: flex;
            align-items: center;
            gap: 1.15rem;
        }

        .quick-action-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.55rem;
            flex-shrink: 0;
        }

        .quick-action-content {
            flex: 1;
        }

        .quick-action-title {
            font-weight: 700;
            font-size: 1.15rem;
            margin-bottom: 0.35rem;
            color: #1e293b;
        }

        .quick-action-desc {
            font-size: 0.95rem;
            color: #94a3b8;
            font-weight: 500;
        }

        /* ───────────────────────────────────────────────────────── */
        /* Responsive                                                */
        /* ───────────────────────────────────────────────────────── */
        @media (max-width: 768px) {
            .dashboard-coordinador {
                padding: 1rem;
                font-size: 16px;
            }

            .breakdown-grid {
                grid-template-columns: 1fr;
            }

            .quick-actions-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-header h1 {
                font-size: 1.9rem;
            }

            .section-header {
                padding: 1.15rem 1.35rem;
            }

            .section-header h3,
            .section-header h2 {
                font-size: 1.15rem;
            }

            .corp-detail-table {
                font-size: 1rem;
            }

            .corp-detail-table tbody tr.corp-detail-row td {
                padding: 16px 14px;
                font-size: 1rem;
            }

            .corp-detail-table thead th {
                padding: 14px 14px !important;
                font-size: 0.82rem !important;
            }

            .corp-detail-name {
                font-size: 1.15rem;
            }

            .corp-pill {
                min-width: 50px;
                padding: 7px 14px;
                font-size: 1.1rem;
            }

            .corp-total {
                font-size: 1.25rem;
            }

            .expiring-banner {
                flex-wrap: wrap;
                gap: 0.85rem;
                padding: 1rem 1.15rem;
            }

            .expiring-banner__content {
                flex: 1 1 100%;
                order: 2;
            }

            .expiring-banner__icon {
                order: 1;
            }

            .expiring-banner__cta {
                order: 3;
                flex: 1 1 100%;
                justify-content: center;
            }

            .expiring-banner__title {
                font-size: 1.05rem;
            }

            .expiring-banner__count {
                font-size: 1.5rem;
            }

            .expiring-banner__nearest {
                font-size: 0.95rem;
            }

            .expired-banner {
                font-size: 0.95rem;
                flex-wrap: wrap;
            }
        }

        /* ───────────────────────────────────────────────────────── */
        /* Animation                                                 */
        /* ───────────────────────────────────────────────────────── */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .section-panel,
        .breakdown-card,
        .alert-banner,
        .corp-breakdown-card,
        .corp-detail-panel {
            animation: fadeInUp 0.5s ease forwards;
        }

        /* Non-clickable KPI cards should not lift */
        .kpi-card-link:not([href]):hover {
            transform: none;
        }

        .kpi-card-link:not([href]):hover .kpi-card {
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06), 0 2px 8px rgba(15, 23, 42, 0.04);
        }

        /* Respect reduced motion */
        @media (prefers-reduced-motion: reduce) {

            .section-panel,
            .breakdown-card,
            .alert-banner,
            .corp-breakdown-card,
            .corp-detail-panel {
                animation: none !important;
            }

            .expiring-banner__pulse {
                animation: none;
            }

            .expiring-banner--clickable::after {
                display: none;
            }

            .expiring-banner--clickable:hover {
                transform: none;
            }
        }

        /* ═══════════════════════════════════════════════════════════ */
        /* Fluent Dark Tooltip — high-contrast, readable, consistent   */
        /* Fixes white-on-white / dark-on-dark contrast regressions    */
        /* introduced by global overrides (e.g. kpi-cards.css).        */
        /* ═══════════════════════════════════════════════════════════ */
        .tooltip.kpi-tooltip {
            opacity: 1 !important;
            z-index: 1080;
        }

        .tooltip.kpi-tooltip .tooltip-inner {
            background-color: #1f2937 !important;
            background-image: none !important;
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 0.92rem;
            line-height: 1.5;
            max-width: 320px;
            text-align: left;
            box-shadow: 0 8px 24px -8px rgba(0, 0, 0, 0.45),
                0 2px 6px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .tooltip.kpi-tooltip .tooltip-inner strong {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            font-weight: 700;
        }

        .tooltip.kpi-tooltip .tooltip-inner small,
        .tooltip.kpi-tooltip .tooltip-inner .text-muted {
            color: #cbd5e1 !important;
            -webkit-text-fill-color: #cbd5e1 !important;
            font-size: 0.85rem;
        }

        .tooltip.kpi-tooltip .tooltip-inner em {
            color: #fbbf24 !important;
            -webkit-text-fill-color: #fbbf24 !important;
            font-style: normal;
            font-weight: 600;
        }

        .tooltip.kpi-tooltip .arrow::before {
            border-top-color: #1f2937 !important;
            border-bottom-color: #1f2937 !important;
            border-left-color: #1f2937 !important;
            border-right-color: #1f2937 !important;
        }

        .tooltip.kpi-tooltip.bs-tooltip-top .arrow::before {
            border-top-color: #1f2937 !important;
        }

        .tooltip.kpi-tooltip.bs-tooltip-bottom .arrow::before {
            border-bottom-color: #1f2937 !important;
        }

        .tooltip.kpi-tooltip.bs-tooltip-left .arrow::before {
            border-left-color: #1f2937 !important;
        }

        .tooltip.kpi-tooltip.bs-tooltip-right .arrow::before {
            border-right-color: #1f2937 !important;
        }
    </style>

    <div class="dashboard-container">
        <!-- ═══════════════════════════════════════════════════════ -->
        <!-- Header                                                    -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <div class="dashboard-header">
            <div class="header-content">
                <h1>
                    <i class="fas fa-chart-line"></i>
                    Panel de Control
                </h1>
                <p>Bienvenido al panel de gestión de afiliados de su clínica</p>
                <div>
                    <span class="clinic-badge">
                        <i class="fas fa-hospital"></i> <?= Html::encode($clinicaNombre) ?>
                    </span>
                    <span class="date-badge">
                        <i class="far fa-calendar-alt"></i> <?= date('d/m/Y H:i') ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!-- Expiring / Expired contracts alert                        -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <?php if ($kpis['expired'] > 0 || $kpis['expiringSoon'] > 0): ?>
            <div class="expiring-banner-wrap">

                <?php if ($kpis['expiringSoon'] > 0): ?>
                    <?php
                    $expiringUrl = Url::to([
                        'site/contratos-por-vencer',
                        'days' => 30,
                    ]);

                    $nearestName = null;
                    $nearestDays = null;
                    $nearestId   = null;
                    if (!empty($nearestExpiring)) {
                        $nearestName = trim(($nearestExpiring['nombres'] ?? '') . ' ' . ($nearestExpiring['apellidos'] ?? ''));
                        $nearestDays = (int) ($nearestExpiring['dias_restantes'] ?? 0);
                        $nearestId   = (int) ($nearestExpiring['user_id'] ?? 0);
                    }
                    ?>

                    <a href="<?= $expiringUrl ?>"
                        class="expiring-banner expiring-banner--clickable"
                        data-toggle="tooltip" data-placement="top" data-html="true"
                        title="<strong>Contratos por Vencer</strong><br>Ver todos los contratos que vencen en los próximos <strong>30 días</strong>.<br><small class='text-muted'>Click para abrir el listado</small>">

                        <span class="expiring-banner__icon">
                            <span class="expiring-banner__pulse"></span>
                            <i class="fas fa-hourglass-half"></i>
                        </span>

                        <div class="expiring-banner__content">
                            <div class="expiring-banner__title">
                                <span class="expiring-banner__count"><?= number_format($kpis['expiringSoon']) ?></span>
                                contrato<?= $kpis['expiringSoon'] === 1 ? '' : 's' ?> por vencer en los próximos 30 días
                            </div>

                            <?php if ($nearestName): ?>
                                <div class="expiring-banner__nearest">
                                    <i class="fas fa-user-clock"></i>
                                    El más próximo:
                                    <strong><?= Html::encode($nearestName) ?></strong>
                                    <?php if ($nearestDays === 0): ?>
                                        <span class="expiring-banner__badge expiring-banner__badge--critical">
                                            <i class="fas fa-bolt"></i> Vence HOY
                                        </span>
                                    <?php elseif ($nearestDays <= 3): ?>
                                        <span class="expiring-banner__badge expiring-banner__badge--critical">
                                            <i class="fas fa-bolt"></i> en <?= $nearestDays ?> día<?= $nearestDays === 1 ? '' : 's' ?>
                                        </span>
                                    <?php elseif ($nearestDays <= 7): ?>
                                        <span class="expiring-banner__badge expiring-banner__badge--warning">
                                            <i class="fas fa-exclamation-circle"></i> en <?= $nearestDays ?> días
                                        </span>
                                    <?php else: ?>
                                        <span class="expiring-banner__badge expiring-banner__badge--notice">
                                            <i class="fas fa-clock"></i> en <?= $nearestDays ?> días
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <span class="expiring-banner__cta">
                            Ver todos
                            <i class="fas fa-arrow-right"></i>
                        </span>
                    </a>
                <?php endif; ?>

                <?php if ($kpis['expired'] > 0): ?>
                    <div class="expired-banner">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>
                            <strong><?= number_format($kpis['expired']) ?></strong>
                            contrato<?= $kpis['expired'] === 1 ? '' : 's' ?> vencido<?= $kpis['expired'] === 1 ? '' : 's' ?>
                            que requiere<?= $kpis['expired'] === 1 ? '' : 'n' ?> atención
                        </span>
                    </div>
                <?php endif; ?>

            </div>
        <?php endif; ?>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!-- 1. MAIN KPI CARDS — Microsoft Fluent                      -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <div class="section-panel">
            <div class="section-header">
                <h3><i class="fas fa-chart-pie"></i> Resumen General</h3>
                <span class="section-header__badge"><?= date('d/m/Y') ?></span>
            </div>
            <div class="section-panel__body">

                <div class="kpi-cards-row">

                    <!-- Card 1: Total -->
                    <a href="<?= Url::to(['user-datos/index-clinicas', 'clinica_id' => $clinicaId]) ?>"
                        class="kpi-card-link"
                        data-toggle="tooltip" data-placement="top" data-html="true"
                        title="<strong>Total de Afiliados</strong><br>Todos los afiliados de <strong><?= Html::encode($clinicaNombre) ?></strong>.<br><small class='text-muted'>Click para ver todos</small>">
                        <div class="kpi-card primary">
                            <span class="kpi-accent"></span>
                            <div class="kpi-card-header">
                                <span class="kpi-icon"><i class="fas fa-users"></i></span>
                                <span class="kpi-title">Total</span>
                                <span class="kpi-arrow"><i class="fas fa-chevron-right"></i></span>
                            </div>
                            <div class="kpi-card-body">
                                <span class="kpi-number"><?= number_format($kpis['total']) ?></span>
                                <span class="kpi-label">
                                    <i class="fas fa-chart-line mr-1"></i>
                                    <?= number_format($kpis['newThisMonth']) ?> nuevos este mes
                                </span>
                            </div>
                        </div>
                    </a>

                    <!-- Card 2: Activos -->
                    <a href="<?= Url::to(['user-datos/index-clinicas', 'clinica_id' => $clinicaId, 'UserDatosSearch[contrato_estatus]' => 'Activo']) ?>"
                        class="kpi-card-link"
                        data-toggle="tooltip" data-placement="top" data-html="true"
                        title="<strong>Afiliados Activos</strong><br>Afiliados con al menos un contrato en vigencia.<br><small class='text-muted'>Click para filtrar</small>">
                        <div class="kpi-card success">
                            <span class="kpi-accent"></span>
                            <div class="kpi-card-header">
                                <span class="kpi-icon"><i class="fas fa-user-check"></i></span>
                                <span class="kpi-title">Activos</span>
                                <span class="kpi-arrow"><i class="fas fa-chevron-right"></i></span>
                            </div>
                            <div class="kpi-card-body">
                                <span class="kpi-number">
                                    <?= number_format($kpis['contratosActivos']) ?>
                                    <?php if ($tasaActividad > 0): ?>
                                        <small class="kpi-percent"><?= $tasaActividad ?>%</small>
                                    <?php endif; ?>
                                </span>
                                <span class="kpi-label">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Con contrato vigente
                                </span>
                            </div>
                        </div>
                    </a>

                    <!-- Card 3: Suspendidos -->
                    <a href="<?= Url::to(['user-datos/index-clinicas', 'clinica_id' => $clinicaId, 'UserDatosSearch[contrato_estatus]' => 'Suspendido']) ?>"
                        class="kpi-card-link"
                        data-toggle="tooltip" data-placement="top" data-html="true"
                        title="<strong>Afiliados Suspendidos</strong><br>Afiliados con al menos un contrato suspendido.<br><small class='text-muted'>Click para filtrar</small>">
                        <div class="kpi-card danger">
                            <span class="kpi-accent"></span>
                            <div class="kpi-card-header">
                                <span class="kpi-icon"><i class="fas fa-user-clock"></i></span>
                                <span class="kpi-title">Suspendidos</span>
                                <span class="kpi-arrow"><i class="fas fa-chevron-right"></i></span>
                            </div>
                            <div class="kpi-card-body">
                                <span class="kpi-number"><?= number_format($kpis['contratosSuspendidos']) ?></span>
                                <span class="kpi-label">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Requieren atención
                                </span>
                            </div>
                        </div>
                    </a>

                    <!-- Card 4: Anulados -->
                    <a href="<?= Url::to(['user-datos/index-clinicas', 'clinica_id' => $clinicaId, 'UserDatosSearch[contrato_estatus]' => 'Anulado']) ?>"
                        class="kpi-card-link"
                        data-toggle="tooltip" data-placement="top" data-html="true"
                        title="<strong>Afiliados Anulados</strong><br>Afiliados sin ningún contrato vigente.<br><small class='text-muted'>Click para filtrar</small>">
                        <div class="kpi-card anulado">
                            <span class="kpi-accent"></span>
                            <div class="kpi-card-header">
                                <span class="kpi-icon"><i class="fas fa-user-slash"></i></span>
                                <span class="kpi-title">Anulados</span>
                                <span class="kpi-arrow"><i class="fas fa-chevron-right"></i></span>
                            </div>
                            <div class="kpi-card-body">
                                <span class="kpi-number"><?= number_format($kpis['contratosAnulados'] ?? 0) ?></span>
                                <span class="kpi-label">
                                    <i class="fas fa-user-times mr-1"></i>
                                    Sin contrato vigente
                                </span>
                            </div>
                        </div>
                    </a>

                </div>

            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!-- 2. TIPO DE AFILIACIÓN + ESTADO DE AFILIADOS              -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <div class="breakdown-grid">
            <!-- ─────────── Tipo de Afiliación (ACTIVE only) ─────────── -->
            <div class="breakdown-card">
                <div class="section-header">
                    <h3><i class="fas fa-user-tag"></i> Tipo de Afiliación</h3>
                </div>
                <div class="breakdown-card__body">

                    <div class="tipo-breakdown__intro">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            Solo cuenta <strong>afiliados activos</strong>
                            (<strong><?= number_format($activeTotal) ?></strong> en total).
                            Los afiliados suspendidos, vencidos o anulados no se incluyen.
                        </div>
                    </div>

                    <div class="tipo-breakdown__row tipo-breakdown__row--individual">
                        <span class="tipo-breakdown__label">
                            <span class="tipo-breakdown__icon"><i class="fas fa-user"></i></span>
                            Individual
                        </span>
                        <span class="tipo-breakdown__count">
                            <span class="tipo-breakdown__value"><?= number_format($kpis['individuales']) ?></span>
                            <span class="tipo-breakdown__pct"><?= $pctIndividual ?>%</span>
                        </span>
                    </div>

                    <div class="tipo-breakdown__row tipo-breakdown__row--corporate">
                        <span class="tipo-breakdown__label">
                            <span class="tipo-breakdown__icon"><i class="fas fa-building"></i></span>
                            Corporativo
                        </span>
                        <span class="tipo-breakdown__count">
                            <span class="tipo-breakdown__value"><?= number_format($kpis['corporativos']) ?></span>
                            <span class="tipo-breakdown__pct"><?= $pctCorporate ?>%</span>
                        </span>
                    </div>

                    <div class="progress-bar-container">
                        <div class="progress-bar info" style="width: <?= $pctIndividual ?>%"></div>
                    </div>

                    <div class="tipo-breakdown__footer">
                        <span class="tipo-breakdown__footer-label">
                            <i class="fas fa-check-circle" style="color: #10b981; margin-right: 6px;"></i>
                            Total activos
                        </span>
                        <span class="tipo-breakdown__footer-total">
                            <?= number_format($activeTotal) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- ─────────── Estado de Afiliados (bucket model) ─────────── -->
            <div class="breakdown-card">
                <div class="section-header">
                    <h3><i class="fas fa-users"></i> Estado de Afiliados</h3>
                </div>
                <div class="breakdown-card__body">
                    <div class="list-item">
                        <span class="list-label">Activos</span>
                        <span class="list-value" style="color: var(--success);">
                            <?= number_format($kpis['contratosActivos']) ?>
                        </span>
                    </div>

                    <div class="list-item">
                        <span class="list-label">Registrados</span>
                        <span class="list-value" style="color: #b45309;">
                            <?= number_format($kpis['contratosRegistrados'] ?? 0) ?>
                        </span>
                    </div>

                    <div class="list-item">
                        <span class="list-label">Suspendidos</span>
                        <span class="list-value" style="color: var(--danger);">
                            <?= number_format($kpis['contratosSuspendidos']) ?>
                        </span>
                    </div>

                    <div class="list-item">
                        <span class="list-label">Por vencer (30 días)</span>
                        <span class="list-value" style="color: var(--warning);">
                            <?= number_format($kpis['expiringSoon']) ?>
                        </span>
                    </div>

                    <div class="list-item">
                        <span class="list-label">Vencidos</span>
                        <span class="list-value" style="color: var(--danger);">
                            <?= number_format($kpis['expired']) ?>
                        </span>
                    </div>

                    <div class="list-item">
                        <span class="list-label">Anulados</span>
                        <span class="list-value" style="color: #be123c;">
                            <?= number_format($kpis['contratosAnulados'] ?? 0) ?>
                        </span>
                    </div>

                    <div class="progress-bar-container">
                        <div class="progress-bar success" style="width: <?= $tasaActividad ?>%"></div>
                    </div>
                    <div class="sub" style="margin-top: 0.6rem;">
                        <?= $tasaActividad ?>% de tasa de actividad
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!-- 3. CORPORATE SECTION                                      -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <?php if ($hasCorporateData): ?>

            <div class="section-panel">
                <div class="section-header">
                    <h3><i class="fas fa-building"></i> Corporativos</h3>
                    <?= Html::a(
                        'Ver todos <i class="fas fa-arrow-right"></i>',
                        ['/corporativo/index'],
                        ['class' => 'section-header__link']
                    ) ?>
                </div>
                <div class="section-panel__body">

                    <div class="kpi-cards-row">

                        <a href="<?= Url::to(['/corporativo/index']) ?>"
                            class="kpi-card-link"
                            data-toggle="tooltip" data-placement="top" data-html="true"
                            title="<strong>Corporativos</strong><br>Empresas con afiliados en su clínica.<br><small class='text-muted'>Click para ver todos</small>">
                            <div class="kpi-card violet">
                                <span class="kpi-accent"></span>
                                <div class="kpi-card-header">
                                    <span class="kpi-icon"><i class="fas fa-building"></i></span>
                                    <span class="kpi-title">Corporativos</span>
                                    <span class="kpi-arrow"><i class="fas fa-chevron-right"></i></span>
                                </div>
                                <div class="kpi-card-body">
                                    <span class="kpi-number"><?= number_format($corporateKpis['totalCorporativos']) ?></span>
                                    <span class="kpi-label">
                                        <i class="fas fa-briefcase mr-1"></i>
                                        Empresas activas
                                    </span>
                                </div>
                            </div>
                        </a>

                        <div class="kpi-card-link"
                            data-toggle="tooltip" data-placement="top" data-html="true"
                            title="<strong>Afiliados Corporativos</strong><br>Total de afiliados pertenecientes a corporativos.">
                            <div class="kpi-card amber">
                                <span class="kpi-accent"></span>
                                <div class="kpi-card-header">
                                    <span class="kpi-icon"><i class="fas fa-users"></i></span>
                                    <span class="kpi-title">Afiliados Corp.</span>
                                    <span class="kpi-arrow"><i class="fas fa-chevron-right"></i></span>
                                </div>
                                <div class="kpi-card-body">
                                    <span class="kpi-number"><?= number_format($corporateKpis['totalAfiliadosCorporativos']) ?></span>
                                    <span class="kpi-label">
                                        <i class="fas fa-id-badge mr-1"></i>
                                        En su clínica
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="kpi-card-link"
                            data-toggle="tooltip" data-placement="top" data-html="true"
                            title="<strong>Corporativos Activos</strong><br>Afiliados corporativos con contrato vigente.">
                            <div class="kpi-card emerald">
                                <span class="kpi-accent"></span>
                                <div class="kpi-card-header">
                                    <span class="kpi-icon"><i class="fas fa-user-check"></i></span>
                                    <span class="kpi-title">Corp. Activos</span>
                                    <span class="kpi-arrow"><i class="fas fa-chevron-right"></i></span>
                                </div>
                                <div class="kpi-card-body">
                                    <span class="kpi-number"><?= number_format($corporateKpis['activos']) ?></span>
                                    <span class="kpi-label">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Contrato vigente
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="kpi-card-link"
                            data-toggle="tooltip" data-placement="top" data-html="true"
                            title="<strong>Corporativos Anulados</strong><br>Afiliados corporativos sin contrato vigente.">
                            <div class="kpi-card rose">
                                <span class="kpi-accent"></span>
                                <div class="kpi-card-header">
                                    <span class="kpi-icon"><i class="fas fa-user-slash"></i></span>
                                    <span class="kpi-title">Corp. Anulados</span>
                                    <span class="kpi-arrow"><i class="fas fa-chevron-right"></i></span>
                                </div>
                                <div class="kpi-card-body">
                                    <span class="kpi-number"><?= number_format($corporateKpis['anulados']) ?></span>
                                    <span class="kpi-label">
                                        <i class="fas fa-user-times mr-1"></i>
                                        Sin contrato
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

            <div class="corp-breakdown-card">
                <div class="section-header">
                    <h3><i class="fas fa-list-ul"></i> Estado de Afiliados Corporativos</h3>
                </div>
                <div class="corp-breakdown-card__body">
                    <div class="breakdown-row">
                        <span class="breakdown-label">
                            <span class="breakdown-dot breakdown-dot--active"></span>
                            Activos
                        </span>
                        <span class="breakdown-value breakdown-value--active">
                            <?= number_format($corporateKpis['activos']) ?>
                        </span>
                    </div>

                    <div class="breakdown-row">
                        <span class="breakdown-label">
                            <span class="breakdown-dot breakdown-dot--registered"></span>
                            Registrados
                        </span>
                        <span class="breakdown-value breakdown-value--registered">
                            <?= number_format($corporateKpis['registrados']) ?>
                        </span>
                    </div>

                    <div class="breakdown-row">
                        <span class="breakdown-label">
                            <span class="breakdown-dot breakdown-dot--suspended"></span>
                            Suspendidos
                        </span>
                        <span class="breakdown-value breakdown-value--suspended">
                            <?= number_format($corporateKpis['suspendidos']) ?>
                        </span>
                    </div>

                    <div class="breakdown-row">
                        <span class="breakdown-label">
                            <span class="breakdown-dot breakdown-dot--annulled"></span>
                            Anulados
                        </span>
                        <span class="breakdown-value breakdown-value--annulled">
                            <?= number_format($corporateKpis['anulados']) ?>
                        </span>
                    </div>

                    <?php
                    $corpTotal = max((int)$corporateKpis['totalAfiliadosCorporativos'], 1);
                    $pctActivos = round(((int)$corporateKpis['activos'] / $corpTotal) * 100, 1);
                    ?>
                    <div class="progress-bar-container" style="margin-top: 1.25rem;">
                        <div class="progress-bar success" style="width: <?= $pctActivos ?>%"></div>
                    </div>
                    <div class="sub" style="margin-top: 0.6rem;">
                        <?= $pctActivos ?>% de los afiliados corporativos están activos
                    </div>
                </div>
            </div>

            <?php if (!empty($corporateDetails)): ?>
                <div class="corp-detail-panel">
                    <div class="section-header">
                        <h3>
                            <i class="fas fa-list-ul"></i>
                            Detalle por Corporativo
                            <span class="section-header__badge"><?= count($corporateDetails) ?></span>
                        </h3>
                        <?= Html::a(
                            'Ver todos <i class="fas fa-arrow-right"></i>',
                            ['/corporativo/index'],
                            ['class' => 'section-header__link']
                        ) ?>
                    </div>

                    <div class="corp-detail-panel__body table-responsive">
                        <table class="corp-detail-table">
                            <thead>
                                <tr>
                                    <th>Corporativo</th>
                                    <th class="text-center">Activos</th>
                                    <th class="text-center">Registrados</th>
                                    <th class="text-center">Suspendidos</th>
                                    <th class="text-center">Anulados</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">% Activo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($corporateDetails as $corp): ?>
                                    <tr class="corp-detail-row corp-detail-row--<?= $corp['tone'] ?>">
                                        <td>
                                            <?= Html::a(
                                                '<div class="corp-detail-name">' . Html::encode($corp['nombre']) . '</div>'
                                                    . '<div class="corp-detail-rif">RIF ' . Html::encode($corp['rif'] ?: 'N/A') . '</div>',
                                                ['/corporativo/view', 'id' => $corp['id']],
                                                [
                                                    'class' => 'corp-detail-link',
                                                    'title' => 'Ver detalle del corporativo',
                                                ]
                                            ) ?>
                                        </td>

                                        <td class="text-center">
                                            <?php if ($corp['activos'] > 0): ?>
                                                <span class="corp-pill corp-pill--active"><?= $corp['activos'] ?></span>
                                            <?php else: ?>
                                                <span class="corp-pill corp-pill--empty">—</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="text-center">
                                            <?php if ($corp['registrados'] > 0): ?>
                                                <span class="corp-pill corp-pill--registered"><?= $corp['registrados'] ?></span>
                                            <?php else: ?>
                                                <span class="corp-pill corp-pill--empty">—</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="text-center">
                                            <?php if ($corp['suspendidos'] > 0): ?>
                                                <span class="corp-pill corp-pill--suspended"><?= $corp['suspendidos'] ?></span>
                                            <?php else: ?>
                                                <span class="corp-pill corp-pill--empty">—</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="text-center">
                                            <?php if ($corp['anulados'] > 0): ?>
                                                <span class="corp-pill corp-pill--annulled"><?= $corp['anulados'] ?></span>
                                            <?php else: ?>
                                                <span class="corp-pill corp-pill--empty">—</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="text-center">
                                            <span class="corp-total"><?= number_format($corp['total']) ?></span>
                                        </td>

                                        <td class="text-center">
                                            <div class="corp-pct-wrap">
                                                <div class="corp-pct-bar">
                                                    <div class="corp-pct-fill corp-pct-fill--<?= $corp['tone'] ?>"
                                                        style="width: <?= $corp['pct_active'] ?>%"></div>
                                                </div>
                                                <span class="corp-pct-text corp-pct-text--<?= $corp['tone'] ?>">
                                                    <?= $corp['pct_active'] ?>%
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!-- 4. QUICK ACTIONS                                          -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <div class="section-panel">
            <div class="section-header">
                <h3><i class="fas fa-bolt"></i> Acciones Rápidas</h3>
            </div>
            <div class="section-panel__body">
                <div class="quick-actions-grid">
                    <div class="quick-action-card"
                        onclick="window.location.href='<?= Url::to(['user-datos/index-clinicas', 'clinica_id' => $clinicaId]) ?>'">
                        <div class="quick-action">
                            <div class="quick-action-icon" style="background: rgba(52,152,219,0.12); color: var(--info);">
                                <i class="fas fa-list"></i>
                            </div>
                            <div class="quick-action-content">
                                <div class="quick-action-title">Ver Todos los Afiliados</div>
                                <div class="quick-action-desc">Gestionar lista completa de afiliados de su clínica</div>
                            </div>
                        </div>
                    </div>

                    <div class="quick-action-card"
                        onclick="window.location.href='<?= Url::to(['user-datos/reporte-afiliados']) ?>'">
                        <div class="quick-action">
                            <div class="quick-action-icon" style="background: rgba(39,174,96,0.12); color: var(--success);">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="quick-action-content">
                                <div class="quick-action-title">Generar Reportes</div>
                                <div class="quick-action-desc">Exportar estadísticas y análisis detallados</div>
                            </div>
                        </div>
                    </div>

                    <div class="quick-action-card"
                        onclick="window.location.href='<?= Url::to(['user-datos/index']) ?>'">
                        <div class="quick-action">
                            <div class="quick-action-icon" style="background: rgba(243,156,18,0.12); color: var(--warning);">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="quick-action-content">
                                <div class="quick-action-title">Nuevo Afiliado</div>
                                <div class="quick-action-desc">Registrar un nuevo afiliado en el sistema</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
// Tooltip initialization — Microsoft Fluent dark tooltip, matching index.php
$js = <<<'JS'
$(document).ready(function () {
    function initFluentTooltips() {
        $('[data-toggle="tooltip"]').tooltip('dispose');
        try {
            $('[data-toggle="tooltip"]').tooltip({
                trigger: 'hover',
                delay: { show: 200, hide: 100 },
                container: 'body',
                boundary: 'window',
                html: true,
                template: '<div class="tooltip kpi-tooltip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
            });
        } catch (error) {
            console.error('Error initializing tooltips:', error);
        }
    }

    initFluentTooltips();

    // Re-init after any dynamic content swap (future-proofing)
    $(document).on('pjax:complete yiiGridViewUpdated', function () {
        setTimeout(initFluentTooltips, 100);
    });
});
JS;

$this->registerJs($js, View::POS_READY);
?>