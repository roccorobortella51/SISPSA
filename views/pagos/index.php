<?php

use app\models\Pagos;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\switchinput\SwitchInput;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\PagosSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'GESTIÓN DE PAGOS';
$this->params['breadcrumbs'][] = $this->title;

// List of statuses for the 'estatus' filter
$estatusList = [
    'Conciliado' => 'Conciliado',
    'Por Conciliar' => 'Por Conciliar',
];

// List of payment methods for the filter
$metodoPagoList = [
    'Efectivo' => 'Efectivo',
    'Pago Móvil' => 'Pago Móvil',
    'Paypal' => 'Paypal',
    'Punto de Venta' => 'Punto de Venta',
    'Transferencia' => 'Transferencia',
    'Zelle' => 'Zelle',
];

$css = <<<CSS
/* ============================================================
   MICROSOFT FLUENT DESIGN SYSTEM - PROFESSIONAL STYLES
   ============================================================ */

/* Global Microsoft-inspired variables */
:root {
    --ms-blue: #0078d4;
    --ms-dark-blue: #106ebe;
    --ms-green: #107c10;
    --ms-red: #d13438;
    --ms-purple: #6b69d6;
    --ms-teal: #00b7c3;
    --ms-gold: #ffaa44;
    --ms-gray-100: #f3f2f1;
    --ms-gray-200: #e1dfdd;
    --ms-gray-300: #c8c6c4;
    --ms-gray-600: #605e5c;
    --ms-gray-800: #323130;
}

/* Page Header - Microsoft Style */
.pagos-index .page-header-modern {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
    border-radius: 0px;
    padding: 32px 28px;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.pagos-index .page-header-modern::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--ms-blue), var(--ms-teal), var(--ms-purple));
}

.pagos-index .page-header-modern::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: rgba(255, 255, 255, 0.1);
}

.pagos-index .page-title-modern {
    font-size: 28px;
    font-weight: 600;
    color: white;
    margin: 0;
    letter-spacing: -0.2px;
    display: flex;
    align-items: center;
    gap: 15px;
    position: relative;
    z-index: 1;
}

.pagos-index .page-title-modern i {
    font-size: 32px;
    background: linear-gradient(135deg, var(--ms-blue), #00b7c3);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.pagos-index .page-title-modern small {
    font-size: 14px;
    font-weight: 400;
    opacity: 0.8;
    margin-left: 10px;
}

.pagos-index .stats-badge-modern {
    background: rgba(255, 255, 255, 0.12);
    backdrop-filter: blur(10px);
    border-radius: 30px;
    padding: 8px 20px;
    display: flex;
    gap: 25px;
    position: relative;
    z-index: 1;
}

.pagos-index .stat-item {
    display: flex;
    align-items: center;
    gap: 10px;
    color: white;
}

.pagos-index .stat-number {
    font-size: 22px;
    font-weight: 700;
    line-height: 1;
}

.pagos-index .stat-label {
    font-size: 12px;
    opacity: 0.75;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Card Styles - Microsoft Fluent */
.pagos-index .ms-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    transition: box-shadow 0.2s ease;
}

.pagos-index .ms-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.pagos-index .ms-card-header {
    padding: 16px 20px;
    background: var(--ms-gray-100);
    border-bottom: 1px solid var(--ms-gray-200);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Section title — matches "Listado de Afiliados" from user-datos */
.pagos-index .ms-card-header .section-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #343a40;
    margin: 0;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 4px;
    line-height: 1.4;
}

.pagos-index .ms-card-header .section-title i {
    font-size: 1.1rem;
    color: #4f46e5;
}

/* Legend Container */
.pagos-index .legend-container {
    background: var(--ms-gray-100);
    border-radius: 8px;
    padding: 12px 20px;
    margin-top: 20px;
    border-left: 4px solid var(--ms-blue);
}

.pagos-index .legend-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--ms-gray-800);
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.pagos-index .legend-items {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.pagos-index .legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: var(--ms-gray-600);
}

/* ============================================================
   PROFESSIONAL COLOR SCHEME FOR PAYMENT TYPES
   ============================================================ */

/* CORPORATE MASTER BADGE - Purple Theme */
.corporate-type-badge {
    background: linear-gradient(135deg, #6610f2, #593196);
    color: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 8px 12px;
    border-radius: 6px;
    font-weight: 600;
    box-shadow: 0 2px 4px rgba(102, 16, 242, 0.2);
    transition: transform 0.2s ease;
}

.corporate-type-badge:hover {
    transform: translateY(-1px);
}

.corporate-type-badge i {
    font-size: 1.2em;
    margin-bottom: 4px;
}

.corporate-type-badge div {
    font-size: 0.85em;
    letter-spacing: 0.5px;
}

.corporate-type-badge small {
    font-size: 0.7em;
    opacity: 0.9;
    margin-top: 2px;
}

/* COMPANY COVERED BADGE - Teal/Green Theme */
.company-covered-badge {
    background: linear-gradient(135deg, #20c997, #12a87a);
    color: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 8px 12px;
    border-radius: 6px;
    font-weight: 600;
    box-shadow: 0 2px 4px rgba(32, 201, 151, 0.2);
    transition: transform 0.2s ease;
}

.company-covered-badge:hover {
    transform: translateY(-1px);
}

.company-covered-badge i {
    font-size: 1.2em;
    margin-bottom: 4px;
}

.company-covered-badge div {
    font-size: 0.85em;
    letter-spacing: 0.5px;
}

.company-covered-badge small {
    font-size: 0.7em;
    opacity: 0.9;
    margin-top: 2px;
}

/* AFFILIATE BADGE - Amber/Gold Theme */
.affiliate-type-badge {
    background: linear-gradient(135deg, #ffc107, #e0a800);
    color: #212529;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 8px 12px;
    border-radius: 6px;
    font-weight: 600;
    box-shadow: 0 2px 4px rgba(255, 193, 7, 0.2);
    transition: transform 0.2s ease;
}

.affiliate-type-badge:hover {
    transform: translateY(-1px);
}

.affiliate-type-badge i {
    font-size: 1.2em;
    margin-bottom: 4px;
}

.affiliate-type-badge div {
    font-size: 0.85em;
    letter-spacing: 0.5px;
}

.affiliate-type-badge small {
    font-size: 0.7em;
    opacity: 0.9;
    margin-top: 2px;
}

/* INDIVIDUAL BADGE - Ocean Blue Theme */
.individual-type-badge {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 8px 12px;
    border-radius: 6px;
    font-weight: 600;
    box-shadow: 0 2px 4px rgba(23, 162, 184, 0.2);
    transition: transform 0.2s ease;
}

.individual-type-badge:hover {
    transform: translateY(-1px);
}

.individual-type-badge i {
    font-size: 1.2em;
    margin-bottom: 4px;
}

.individual-type-badge div {
    font-size: 0.85em;
    letter-spacing: 0.5px;
}

.individual-type-badge small {
    font-size: 0.7em;
    opacity: 0.9;
    margin-top: 2px;
}

/* Payment Method Badge inside Tipo de Pago */
.payment-method-badge {
    display: inline-block;
    background: rgba(0, 0, 0, 0.05);
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.7em;
    margin-top: 6px;
    font-weight: normal;
}

/* ============================================================
   PAYER / HIERARCHY COLUMN STYLES
   ============================================================ */

/* Corporate name styling - Purple */
.corporate-name-pro {
    color: #6610f2;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 4px;
}

/* Individual name styling - Ocean Blue */
.individual-name-pro {
    color: #17a2b8;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 4px;
}

/* Corporate icon styling */
.corporate-icon {
    color: #6610f2;
    font-size: 0.9em;
}

.individual-icon {
    color: #17a2b8;
    font-size: 0.9em;
}

/* ============================================================
   IDENTIFICATION COLUMN STYLES
   ============================================================ */

/* Corporate RIF styling - Purple */
.corporate-rif {
    background-color: rgba(102, 16, 242, 0.1);
    color: #6610f2;
    font-weight: bold;
    padding: 6px 12px;
    border-radius: 4px;
    display: inline-block;
    font-size: 1.1em;
    border: 1px solid rgba(102, 16, 242, 0.2);
}

/* Company covered cedula - Teal/Green */
.company-covered-cedula {
    background-color: rgba(32, 201, 151, 0.1);
    color: #12a87a;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 4px;
    border: 1px solid rgba(32, 201, 151, 0.2);
    display: inline-block;
}

/* Affiliate cedula - Amber */
.affiliate-cedula {
    background-color: rgba(255, 193, 7, 0.1);
    color: #e0a800;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 4px;
    border: 1px solid rgba(255, 193, 7, 0.2);
    display: inline-block;
}

/* Individual cedula - Blue */
.individual-cedula {
    background-color: rgba(23, 162, 184, 0.1);
    color: #138496;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 4px;
    border: 1px solid rgba(23, 162, 184, 0.2);
    display: inline-block;
}

.corporate-id {
    text-align: center;
}

/* ============================================================
   HIERARCHY VISUAL CONNECTORS
   ============================================================ */

/* Jerarquía Visual */
.afiliado-indent {
    position: relative;
}

.afiliado-indent::before {
    content: "";
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: rgba(255, 193, 7, 0.3);
}

/* Pago Corporativo Principal */
.corporate-main-payment {
    padding: 8px;
}

.corporate-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 4px;
}

.corporate-main-cell {
    border-top: 2px solid #6610f2 !important;
    border-bottom: 2px solid #6610f2 !important;
}

.corporate-group-start {
    border-left: 4px solid #6610f2 !important;
}

.corporate-group-header-cell {
    background: linear-gradient(to right, rgba(102, 16, 242, 0.1), rgba(102, 16, 242, 0.05)) !important;
    border-bottom: 1px solid rgba(102, 16, 242, 0.2) !important;
}

.corporate-main-group {
    padding: 10px 5px;
}

.corporate-subtitle {
    margin-top: 5px;
    padding-left: 28px;
}

/* Pago de Afiliado */
.affiliate-payment {
    padding: 8px 8px 8px 30px;
    position: relative;
}

.affiliate-indent {
    display: flex;
    align-items: center;
}

.affiliate-details {
    margin-top: 4px;
    padding-left: 24px;
}

.affiliate-cell {
    border-left: 3px solid rgba(255, 193, 7, 0.5) !important;
}

.affiliate-in-group-cell {
    position: relative;
}

.affiliate-in-group-cell::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 15px;
    background: linear-gradient(to right, rgba(255, 193, 7, 0.1), transparent);
}

.affiliate-in-group-row {
    padding: 8px 0 8px 20px;
}

.group-connector {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 8px;
    padding: 4px 8px;
    background-color: rgba(0, 0, 0, 0.02);
    border-radius: 4px;
    font-size: 0.85em;
}

/* Separadores visuales entre grupos */
.corporate-group-header-cell + .affiliate-in-group-cell {
    border-top: none !important;
}

.affiliate-in-group-cell:last-of-type {
    border-bottom: 2px dashed #dee2e6 !important;
}

/* Para pagos individuales normales */
.individual-payment-single {
    padding: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.individual-single-cell {
    border-top: 1px solid #f8f9fa !important;
}

/* Payment connection styles */
.payment-connection {
    padding: 5px;
}

.connection-line {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 3px;
    padding-top: 3px;
    border-top: 1px dashed #dee2e6;
}

.parent-payment-ref {
    padding: 5px;
    background-color: rgba(102, 16, 242, 0.05);
    border-radius: 4px;
}

/* Background variations for tipo cells */
.corporate-tipo-cell {
    background-color: rgba(102, 16, 242, 0.05);
    border: 1px solid rgba(102, 16, 242, 0.1);
    border-radius: 4px;
}

.company-covered-tipo-cell {
    background-color: rgba(32, 201, 151, 0.05);
    border: 1px solid rgba(32, 201, 151, 0.1);
    border-radius: 4px;
}

.affiliate-tipo-cell {
    background-color: rgba(255, 193, 7, 0.05);
    border: 1px solid rgba(255, 193, 7, 0.1);
    border-radius: 4px;
}

.individual-tipo-cell {
    background-color: rgba(23, 162, 184, 0.05);
    border: 1px solid rgba(23, 162, 184, 0.1);
    border-radius: 4px;
}

/* Payer cell variations */
.corporate-payer-cell {
    background-color: rgba(102, 16, 242, 0.03);
    border-left: 2px solid rgba(102, 16, 242, 0.2);
}

.company-covered-payer-cell {
    background-color: rgba(32, 201, 151, 0.03);
    border-left: 2px solid rgba(32, 201, 151, 0.2);
}

.affiliate-payer-cell {
    background-color: rgba(255, 193, 7, 0.03);
    border-left: 2px solid rgba(255, 193, 7, 0.3);
}

.individual-payer-cell {
    background-color: rgba(23, 162, 184, 0.03);
    border-left: 2px solid rgba(23, 162, 184, 0.2);
}

/* ============================================================
   CONCILIATION STATE — ROW BACKGROUND COLOR CODING
   ------------------------------------------------------------
   Eye-catching, Microsoft Fluent-inspired state coloring.

   - PENDING  ("Por Conciliar") → warm amber wash + amber left
     accent. Warm colors pull attention (Teams, Planner use
     amber for "needs action").

   - DONE     ("Conciliado")    → near-white cool neutral +
     soft green left accent. Cool & calm, matches Outlook's
     read-state rows. Deliberately NOT green-background: when
     80% of rows are green the eye stops registering it, so we
     let the pending rows stand out instead.

   The left accent bar (4px) provides color-vision-deficiency
   redundancy: shape + color, not color alone.
   ============================================================ */

/* Base row — reset (Kartik's striped class would fight us otherwise) */
.pagos-index .kv-grid-table tbody tr {
    transition: background-color 0.25s ease, box-shadow 0.25s ease;
}

/* PENDING — "Por Conciliar" */
.pagos-index .kv-grid-table tbody tr[data-conciliado="0"] > td {
    background-color: #fff8e1 !important;   /* warm amber wash */
    border-top:    1px solid rgba(255, 170, 68, 0.18) !important;
    border-bottom: 1px solid rgba(255, 170, 68, 0.18) !important;
}

.pagos-index .kv-grid-table tbody tr[data-conciliado="0"] > td:first-child {
    border-left: 4px solid #ffaa44 !important;   /* amber accent */
    box-shadow: inset 6px 0 12px -8px rgba(255, 170, 68, 0.55);
}

.pagos-index .kv-grid-table tbody tr[data-conciliado="0"]:hover > td {
    background-color: #fff3cd !important;   /* one shade deeper on hover */
}

/* DONE — "Conciliado" */
.pagos-index .kv-grid-table tbody tr[data-conciliado="1"] > td {
    background-color: #fafbfc !important;   /* cool near-white */
    border-top:    1px solid rgba(16, 124, 16, 0.06) !important;
    border-bottom: 1px solid rgba(16, 124, 16, 0.06) !important;
}

.pagos-index .kv-grid-table tbody tr[data-conciliado="1"] > td:first-child {
    border-left: 4px solid rgba(16, 124, 16, 0.45) !important;   /* soft green accent */
    box-shadow: inset 6px 0 12px -8px rgba(16, 124, 16, 0.25);
}

.pagos-index .kv-grid-table tbody tr[data-conciliado="1"]:hover > td {
    background-color: #f1f4f6 !important;
}

/* Small pulsing dot next to the pending switch — reinforces state
   even before the user reads the toggle label. */
.pagos-index .kv-grid-table tbody tr[data-conciliado="0"] td:last-child {
    position: relative;
}

/* Reduced motion users — no pulse */
@media (prefers-reduced-motion: reduce) {
    .pagos-index .kv-grid-table tbody tr {
        transition: none !important;
    }
}

/* ============================================================
   ACTIONS COLUMN FIXES
   ============================================================ */
.kv-grid-table .kv-action-column {
    white-space: nowrap;
    min-width: 100px;
    width: 100px;
}

.kv-grid-table .kv-action-column a {
    display: inline-block;
    margin: 0 4px;
    padding: 6px 8px;
    border-radius: 4px;
    transition: all 0.2s ease;
    text-decoration: none;
}

.kv-grid-table .kv-action-column a:hover {
    background-color: rgba(0, 0, 0, 0.05);
    transform: translateY(-1px);
}

.kv-grid-table .kv-action-column a i {
    font-size: 18px;
}

/* Action buttons container */
.action-buttons {
    display: flex;
    justify-content: center;
    gap: 8px;
    align-items: center;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    transition: all 0.2s ease;
    text-decoration: none;
}

.action-btn:hover {
    transform: translateY(-2px);
    text-decoration: none;
}

.action-btn-view {
    color: #17a2b8;
    background-color: rgba(23, 162, 184, 0.1);
}

.action-btn-view:hover {
    color: #0f6674;
    background-color: rgba(23, 162, 184, 0.2);
}

.action-btn-update {
    color: #ffc107;
    background-color: rgba(255, 193, 7, 0.1);
}

.action-btn-update:hover {
    color: #e0a800;
    background-color: rgba(255, 193, 7, 0.2);
}

.action-btn-delete {
    color: #dc3545;
    background-color: rgba(220, 53, 69, 0.1);
}

.action-btn-delete:hover {
    color: #bd2130;
    background-color: rgba(220, 53, 69, 0.2);
}

/* ============================================================
   EYE-CATCHING DUAL SCROLLBAR (top + native bottom)
   Microsoft Fluent "Content Scroller" pattern
   ============================================================ */

.pagos-index .table-responsive-scroll {
    overflow-x: auto;
    overflow-y: visible;
    -webkit-overflow-scrolling: touch;
}

/* Wrapper that holds the hint label + arrows + native scroll strip */
.pagos-index .top-scrollbar-shell {
    display: none;                 /* toggled by JS when overflow exists */
    align-items: center;
    gap: 10px;
    margin: 0 12px 12px 12px;
    padding: 8px 12px;
    border-radius: 10px;
    background: linear-gradient(135deg, #f8f9ff 0%, #eef2ff 100%);
    border: 1px solid rgba(99, 102, 241, 0.15);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.9),
        0 1px 3px rgba(15, 23, 42, 0.04);
    position: relative;
    overflow: hidden;
    transition: box-shadow 0.25s ease, border-color 0.25s ease;
}

/* Animated gradient accent line at the top — matches page header */
.pagos-index .top-scrollbar-shell::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(
        90deg,
        var(--ms-blue) 0%,
        var(--ms-teal) 35%,
        var(--ms-purple) 70%,
        var(--ms-blue) 100%
    );
    background-size: 200% 100%;
    animation: scrollbarAccentSlide 6s linear infinite;
    opacity: 0.85;
}

@keyframes scrollbarAccentSlide {
    0%   { background-position:   0% 50%; }
    100% { background-position: 200% 50%; }
}

.pagos-index .top-scrollbar-shell:hover {
    border-color: rgba(99, 102, 241, 0.35);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.9),
        0 4px 14px rgba(99, 102, 241, 0.15);
}

/* Hint label on the left */
.pagos-index .top-scrollbar-hint {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    color: #4f46e5;
    white-space: nowrap;
    user-select: none;
    flex: 0 0 auto;
    padding-right: 6px;
    border-right: 1px solid rgba(99, 102, 241, 0.2);
}

.pagos-index .top-scrollbar-hint i {
    font-size: 12px;
    animation: scrollbarHintPulse 2.4s ease-in-out infinite;
}

@keyframes scrollbarHintPulse {
    0%, 100% { transform: translateX(0);   opacity: 1;    }
    50%      { transform: translateX(3px); opacity: 0.7;  }
}

/* Arrow buttons */
.pagos-index .top-scrollbar-arrow {
    flex: 0 0 auto;
    width: 28px;
    height: 28px;
    border: none;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.9);
    color: #4f46e5;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    padding: 0;
    box-shadow:
        inset 0 0 0 1px rgba(99, 102, 241, 0.15),
        0 1px 2px rgba(15, 23, 42, 0.05);
    transition: all 0.2s ease;
}

.pagos-index .top-scrollbar-arrow:hover {
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(79, 70, 229, 0.35);
}

.pagos-index .top-scrollbar-arrow:active {
    transform: translateY(0);
    box-shadow: 0 1px 2px rgba(79, 70, 229, 0.3);
}

.pagos-index .top-scrollbar-arrow i {
    font-size: 12px;
}

/* The actual native scroll strip */
.pagos-index .top-native-scrollbar {
    flex: 1 1 auto;
    height: 14px;
    overflow-x: scroll;
    overflow-y: hidden;
    cursor: pointer;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: #6366f1 #e0e7ff;
}

.pagos-index .top-native-scrollbar .top-native-dummy {
    display: inline-block;
    width: 100%;
    min-height: 1px;
    height: 1px;
}

/* WebKit — track */
.pagos-index .top-native-scrollbar::-webkit-scrollbar,
.pagos-index .table-responsive-scroll::-webkit-scrollbar {
    height: 12px;
}

.pagos-index .top-native-scrollbar::-webkit-scrollbar-track {
    background: linear-gradient(90deg, #e0e7ff 0%, #ede9fe 100%);
    border-radius: 6px;
    box-shadow: inset 0 1px 2px rgba(79, 70, 229, 0.08);
}

.pagos-index .table-responsive-scroll::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 6px;
}

/* WebKit — thumb: gradient + glow */
.pagos-index .top-native-scrollbar::-webkit-scrollbar-thumb {
    background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 50%, #06b6d4 100%);
    background-size: 200% 100%;
    border-radius: 6px;
    border: 2px solid transparent;
    background-clip: padding-box;
    transition: all 0.2s ease;
}

.pagos-index .top-native-scrollbar:hover::-webkit-scrollbar-thumb {
    background: linear-gradient(90deg, #4f46e5 0%, #7c3aed 50%, #0891b2 100%);
    background-size: 200% 100%;
    box-shadow: 0 0 8px rgba(99, 102, 241, 0.55);
}

.pagos-index .top-native-scrollbar::-webkit-scrollbar-thumb:hover {
    box-shadow: 0 0 12px rgba(99, 102, 241, 0.75);
}

/* Native bottom thumb — subtle indigo tint */
.pagos-index .table-responsive-scroll::-webkit-scrollbar-thumb {
    background: linear-gradient(90deg, #c7d2fe 0%, #ddd6fe 100%);
    border-radius: 6px;
    transition: background 0.2s ease;
}

.pagos-index .table-responsive-scroll::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(90deg, #a5b4fc 0%, #c4b5fd 100%);
}

/* Firefox native top scrollbar */
.pagos-index .top-native-scrollbar {
    scrollbar-color: #6366f1 #e0e7ff;
}

/* Firefox native bottom scrollbar */
.pagos-index .table-responsive-scroll {
    scrollbar-color: #c7d2fe #f1f1f1;
    scrollbar-width: thin;
}

/* Reduce motion — respect user preference */
@media (prefers-reduced-motion: reduce) {
    .pagos-index .top-scrollbar-shell::before,
    .pagos-index .top-scrollbar-hint i {
        animation: none !important;
    }
}

/* Responsive tweaks */
@media (max-width: 768px) {
    .pagos-index .top-scrollbar-shell {
        margin: 0 8px 10px 8px;
        padding: 6px 8px;
        gap: 6px;
    }
    .pagos-index .top-scrollbar-hint {
        font-size: 10px;
        letter-spacing: 0.4px;
        padding-right: 4px;
    }
    .pagos-index .top-scrollbar-arrow {
        width: 24px;
        height: 24px;
    }
}
CSS;

$this->registerCss($css);

// Get accurate totals from the entire database (not just current page)
$totalPayments = Pagos::find()->count();
$totalCorporate = Pagos::find()
    ->where(['tipo_pago' => 'corporativo'])
    ->andWhere(['is not', 'corporativo_id', null])
    ->andWhere(['user_id' => null])
    ->count();
$totalConciliated = Pagos::find()
    ->where(['estatus' => 'Conciliado'])
    ->orWhere(['estatus' => '1'])
    ->count();
$totalPending = Pagos::find()
    ->where(['estatus' => 'Por Conciliar'])
    ->orWhere(['estatus' => '0'])
    ->orWhere(['estatus' => 'Inactivo'])
    ->count();
?>

<div class="pagos-index">
    <!-- Microsoft Fluent Design Page Header -->
    <div class="page-header-modern">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <div class="page-title-modern">
                    <i class="fas fa-credit-card"></i>
                    <?= Html::encode($this->title) ?>
                    <small>Gestión y conciliación de pagos</small>
                </div>
                <?php if (!empty($this->params['breadcrumbs'])): ?>
                    <nav aria-label="breadcrumb" class="mt-3">
                        <ol class="breadcrumb bg-transparent p-0 mb-0" style="background: transparent !important;">
                            <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>" class="text-white-50"><i class="fas fa-home"></i> Inicio</a></li>
                            <li class="breadcrumb-item active text-white-50" aria-current="page"><?= Html::encode($this->title) ?></li>
                        </ol>
                    </nav>
                <?php endif; ?>
            </div>
            <div class="stats-badge-modern mt-3 mt-md-0">
                <div class="stat-item">
                    <i class="fas fa-chart-line fa-2x opacity-75"></i>
                    <div>
                        <div class="stat-number"><?= number_format($totalPayments) ?></div>
                        <div class="stat-label">Total Pagos</div>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-building fa-2x opacity-75"></i>
                    <div>
                        <div class="stat-number"><?= number_format($totalCorporate) ?></div>
                        <div class="stat-label">Corporativos</div>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                    <div>
                        <div class="stat-number"><?= number_format($totalConciliated) ?></div>
                        <div class="stat-label">Conciliados</div>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-clock fa-2x opacity-75"></i>
                    <div>
                        <div class="stat-number"><?= number_format($totalPending) ?></div>
                        <div class="stat-label">Pendientes</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="ms-card">
        <div class="ms-card-header">
            <h3 class="section-title">
                <i class="fas fa-table mr-3"></i> Listado de Pagos
                <span class="badge badge-secondary ml-2"><?= number_format($totalPayments) ?> registros totales</span>
            </h3>
            <div>
                <button class="btn btn-sm btn-outline-primary" onclick="window.location.reload();">
                    <i class="fas fa-sync-alt"></i> Refrescar
                </button>
            </div>
        </div>
        <div class="p-0">

            <!-- Eye-catching top scrollbar shell -->
            <div class="top-scrollbar-shell">
                <span class="top-scrollbar-hint">
                    <i class="fas fa-arrows-alt-h"></i> Deslizar
                </span>

                <button type="button"
                    class="top-scrollbar-arrow top-scrollbar-arrow-left"
                    aria-label="Desplazar a la izquierda"
                    title="Desplazar a la izquierda">
                    <i class="fas fa-chevron-left"></i>
                </button>

                <div class="top-native-scrollbar">
                    <div class="top-native-dummy"></div>
                </div>

                <button type="button"
                    class="top-scrollbar-arrow top-scrollbar-arrow-right"
                    aria-label="Desplazar a la derecha"
                    title="Desplazar a la derecha">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <!-- Grid wrapper -->
            <div class="table-responsive table-responsive-scroll" id="grid-scroll-container">

                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'responsiveWrap'   => false,
                    'resizableColumns' => false,
                    'persistResize'    => false,
                    'bordered' => true,
                    'striped' => true,
                    'condensed' => false,
                    'hover' => true,
                    'panel' => false,

                    /* ============================================================
                   CONCILIATION STATE — Row data attribute
                   ------------------------------------------------------------
                   Emits data-conciliado="1" on rows whose estatus is
                   Conciliado/1/Activo, and "0" otherwise. CSS then paints
                   the entire row. The JS switch handler also updates this
                   attribute in-place after a successful AJAX call, so the
                   background flips instantly without a page reload.
                   ============================================================ */
                    'rowOptions' => function ($model, $key, $index, $grid) {
                        $isConciliated =
                            $model->estatus == 'Conciliado' ||
                            $model->estatus == '1' ||
                            $model->estatus == 'Activo';

                        return [
                            'data-conciliado' => $isConciliated ? '1' : '0',
                        ];
                    },
                    /* ============================================================ */

                    'toolbar' => [
                        '{export}',
                        '{toggleData}',
                    ],
                    'exportConfig' => [
                        GridView::EXCEL => [],
                        GridView::PDF => [],
                    ],
                    'columns' => [
                        // Serial Number Column with hierarchy indicators
                        [
                            'class' => 'yii\grid\SerialColumn',
                            'contentOptions' => function ($model, $key, $index, $column) use ($dataProvider) {
                                $isCorporate = ($model->tipo_pago === 'corporativo' && $model->corporativo_id);
                                $isAffiliate = ($model->pago_corporativo_id);
                                $isCompanyCovered = ($model->corporativo_id && $model->user_id && $model->tipo_pago !== 'corporativo');

                                $options = ['style' => 'text-align: center;'];

                                if ($isCorporate) {
                                    $options['style'] .= ' font-weight: bold;';
                                    $options['class'] = 'corporate-group-start';
                                } elseif ($isCompanyCovered) {
                                    $options['style'] .= ' padding-left: 15px;';
                                } elseif ($isAffiliate) {
                                    $options['style'] .= ' padding-left: 30px;';
                                    $options['class'] = 'affiliate-in-group';
                                }

                                return $options;
                            },
                        ],

                        // Payer / Hierarchy Column with visual connections
                        [
                            'attribute' => 'nombreUsuario',
                            'value' => function ($model, $key, $index, $column) use ($dataProvider) {
                                $models = $dataProvider->getModels();
                                $isFirstInGroup = true;

                                // Determine if this is the first in its group
                                if ($index > 0 && $model->pago_corporativo_id) {
                                    $prevModel = $models[$index - 1];
                                    $isFirstInGroup = ($prevModel->id !== $model->pago_corporativo_id &&
                                        !($prevModel->pago_corporativo_id && $prevModel->pago_corporativo_id == $model->pago_corporativo_id));
                                }

                                // 1. CORPORATE MASTER PAYMENT
                                if ($model->tipo_pago === 'corporativo' && $model->corporativo_id) {
                                    $corpName = $model->corporativo ? $model->corporativo->nombre : 'Corporativo';
                                    $affiliateCount = count($model->pagosAfiliados);

                                    $html = '<div class="corporate-main-group">';
                                    $html .= '<div class="corporate-header">';
                                    $html .= '<i class="fas fa-building corporate-icon"></i>';
                                    $html .= '<strong>' . Html::encode($corpName) . '</strong>';
                                    if ($affiliateCount > 0) {
                                        $html .= '<span class="badge badge-light ml-2">' . $affiliateCount . ' empleado(s)</span>';
                                    }
                                    $html .= '</div>';
                                    $html .= '<div class="corporate-subtitle">';
                                    $html .= '<small class="text-muted">';
                                    $html .= '<i class="fas fa-chart-line"></i> Pago Corporativo Maestro';
                                    $html .= '</small>';
                                    $html .= '</div>';
                                    $html .= '</div>';

                                    return $html;
                                }

                                // 2. AFFILIATE PAYMENT (Linked to master corporate payment)
                                if ($model->pago_corporativo_id) {
                                    $parentPayment = $model->pagoCorporativo;
                                    $corpName = $parentPayment && $parentPayment->corporativo ?
                                        $parentPayment->corporativo->nombre : 'Corporativo';

                                    $html = '<div class="affiliate-in-group-row">';

                                    // Connector line for the first affiliate in group
                                    if ($isFirstInGroup) {
                                        $html .= '<div class="group-connector">';
                                        $html .= '<i class="fas fa-arrow-down text-muted mr-1"></i>';
                                        $html .= '<small class="text-muted">Afiliados de: ' . Html::encode($corpName) . '</small>';
                                        $html .= '</div>';
                                    }

                                    $html .= '<div class="affiliate-info">';
                                    $html .= '<div class="affiliate-indent">';
                                    $html .= '<i class="fas fa-user-friends affiliate-icon" style="color: #e0a800;"></i>';
                                    $html .= ($model->userDatos ? Html::encode($model->userDatos->nombres . ' ' . $model->userDatos->apellidos) : 'Afiliado');
                                    $html .= '</div>';
                                    $html .= '</div>';
                                    $html .= '</div>';

                                    return $html;
                                }

                                // 3. COMPANY COVERED PAYMENT (Company paid for specific employee)
                                if ($model->corporativo_id && $model->user_id && $model->tipo_pago !== 'corporativo') {
                                    $companyName = $model->corporativo ? $model->corporativo->nombre : 'Empresa';

                                    $html = '<div class="company-covered-payment">';
                                    $html .= '<div class="company-covered-header">';
                                    $html .= '<i class="fas fa-hand-holding-usd" style="color: #20c997;"></i>';
                                    $html .= '<div>';
                                    $html .= '<strong>' . Html::encode($model->userDatos->nombres . ' ' . $model->userDatos->apellidos) . '</strong>';
                                    $html .= '<div><small class="text-muted"><i class="fas fa-building"></i> Pagado por: ' . Html::encode($companyName) . '</small></div>';
                                    $html .= '</div>';
                                    $html .= '</div>';
                                    $html .= '</div>';

                                    return $html;
                                }

                                // 4. INDIVIDUAL PAYMENT (Self-paid)
                                return $model->userDatos ?
                                    '<div class="individual-payment-single">
                        <i class="fas fa-user individual-icon"></i>
                        <strong>' . Html::encode($model->userDatos->nombres . ' ' . $model->userDatos->apellidos) . '</strong>
                        <small class="text-muted ml-2">(Pago Personal)</small>
                    </div>' : 'N/A';
                            },
                            'label' => 'PAGADOR / JERARQUÍA',
                            'format' => 'raw',
                            'contentOptions' => function ($model) {
                                if ($model->tipo_pago === 'corporativo' && $model->corporativo_id) {
                                    return [
                                        'class' => 'corporate-group-header-cell',
                                        'style' => 'vertical-align: middle; border-top: 2px solid #6610f2 !important;'
                                    ];
                                }
                                if ($model->pago_corporativo_id) {
                                    return [
                                        'class' => 'affiliate-in-group-cell',
                                        'style' => 'vertical-align: middle;'
                                    ];
                                }
                                if ($model->corporativo_id && $model->user_id && $model->tipo_pago !== 'corporativo') {
                                    return [
                                        'class' => 'company-covered-payer-cell',
                                        'style' => 'vertical-align: middle; border-left: 2px solid #20c997 !important;'
                                    ];
                                }
                                return [
                                    'class' => 'individual-single-cell',
                                    'style' => 'vertical-align: middle;'
                                ];
                            }
                        ],

                        // Identification Column
                        [
                            'attribute' => 'cedulaUsuario',
                            'value' => function ($model) {
                                // Corporate Master Payment
                                if ($model->tipo_pago === 'corporativo' && $model->corporativo_id) {
                                    return $model->corporativo ?
                                        '<div class="corporate-id">
                            <span class="corporate-rif"><i class="fas fa-id-card"></i> ' . Html::encode($model->corporativo->rif) . '</span><br>
                            <small class="text-muted">RIF Corporativo</small>
                        </div>' : 'N/A';
                                }

                                // Affiliate Payment (under master)
                                if ($model->pago_corporativo_id) {
                                    if ($model->userDatos) {
                                        $cedula = $model->userDatos->cedula;
                                        $tipoCedula = $model->userDatos->tipo_cedula;
                                        $formatted = $tipoCedula && $cedula ? $tipoCedula . '-' . $cedula : ($cedula ?? 'N/A');
                                        return '<span class="affiliate-cedula"><i class="fas fa-id-card"></i> ' . Html::encode($formatted) . '</span>';
                                    }
                                }

                                // Company Covered Payment
                                if ($model->corporativo_id && $model->user_id && $model->tipo_pago !== 'corporativo') {
                                    if ($model->userDatos) {
                                        $cedula = $model->userDatos->cedula;
                                        $tipoCedula = $model->userDatos->tipo_cedula;
                                        $formatted = $tipoCedula && $cedula ? $tipoCedula . '-' . $cedula : ($cedula ?? 'N/A');
                                        return '<span class="company-covered-cedula"><i class="fas fa-id-card"></i> ' . Html::encode($formatted) . '</span>';
                                    }
                                }

                                // Individual Payment
                                if ($model->userDatos) {
                                    $cedula = $model->userDatos->cedula;
                                    $tipoCedula = $model->userDatos->tipo_cedula;
                                    $formatted = $tipoCedula && $cedula ? $tipoCedula . '-' . $cedula : ($cedula ?? 'N/A');
                                    return '<span class="individual-cedula"><i class="fas fa-id-card"></i> ' . Html::encode($formatted) . '</span>';
                                }

                                return '<span class="text-muted">N/A</span>';
                            },
                            'label' => 'IDENTIFICACIÓN',
                            'format' => 'raw',
                            'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                        ],

                        // Clinic Column
                        [
                            'label' => 'CLÍNICA',
                            'attribute' => 'clinica_nombre',
                            'value' => function ($model) {
                                // For individual payments and affiliates
                                if ($model->userDatos) {
                                    if ($model->userDatos->clinica) {
                                        return '<span class="badge badge-info" style="font-size: 0.9em; padding: 6px 12px;">
                            <i class="fas fa-hospital"></i> ' . Html::encode($model->userDatos->clinica->nombre) . '
                        </span>';
                                    }

                                    if ($model->userDatos->contratos) {
                                        foreach ($model->userDatos->contratos as $contrato) {
                                            if ($contrato->clinica) {
                                                return '<span class="badge badge-info" style="font-size: 0.9em; padding: 6px 12px;">
                                    <i class="fas fa-hospital"></i> ' . Html::encode($contrato->clinica->nombre) . '
                                </span>';
                                            }
                                        }
                                    }
                                }

                                // For corporate payments
                                if (($model->tipo_pago === 'corporativo' || ($model->corporativo_id && $model->user_id)) && $model->corporativo) {
                                    $clinicas = $model->corporativo->clinicas;
                                    if (!empty($clinicas)) {
                                        if (count($clinicas) === 1) {
                                            return '<span class="badge badge-info" style="font-size: 0.9em; padding: 6px 12px;">
                                <i class="fas fa-building"></i> ' . Html::encode($clinicas[0]->nombre) . '
                            </span>';
                                        }
                                        $clinicNames = [];
                                        foreach ($clinicas as $clinica) {
                                            $clinicNames[] = $clinica->nombre;
                                        }
                                        return '<span class="badge badge-info" style="font-size: 0.9em; padding: 6px 12px;" title="' . Html::encode(implode(', ', $clinicNames)) . '">
                            <i class="fas fa-building"></i> ' . Html::encode($clinicas[0]->nombre) . ' +' . (count($clinicas) - 1) . '
                        </span>';
                                    }
                                }

                                return '<span class="text-muted"><i class="fas fa-minus-circle"></i> No asignada</span>';
                            },
                            'format' => 'raw',
                            'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                            'headerOptions' => ['style' => 'text-align: center;'],
                            'filter' => \yii\helpers\ArrayHelper::map(
                                \app\models\RmClinica::find()->orderBy('nombre')->all(),
                                'nombre',
                                'nombre'
                            ),
                            'filterType' => GridView::FILTER_SELECT2,
                            'filterWidgetOptions' => [
                                'options' => ['placeholder' => 'Filtrar clínica...'],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ],
                        ],

                        // TIPO DE PAGO COLUMN
                        [
                            'label' => 'TIPO DE PAGO',
                            'attribute' => 'tipo_filter',
                            'value' => function ($model) {
                                $paymentMethod = $model->metodo_pago ?? 'No especificado';
                                $methodIcon = '';

                                switch (strtolower($paymentMethod)) {
                                    case 'efectivo':
                                        $methodIcon = '<i class="fas fa-money-bill-wave"></i> ';
                                        break;
                                    case 'pago móvil':
                                    case 'pago movil':
                                        $methodIcon = '<i class="fas fa-mobile-alt"></i> ';
                                        break;
                                    case 'paypal':
                                        $methodIcon = '<i class="fab fa-paypal"></i> ';
                                        break;
                                    case 'punto de venta':
                                        $methodIcon = '<i class="fas fa-credit-card"></i> ';
                                        break;
                                    case 'transferencia':
                                        $methodIcon = '<i class="fas fa-exchange-alt"></i> ';
                                        break;
                                    case 'zelle':
                                        $methodIcon = '<i class="fas fa-university"></i> ';
                                        break;
                                    default:
                                        $methodIcon = '<i class="fas fa-receipt"></i> ';
                                }

                                if ($model->tipo_pago === 'corporativo' && $model->corporativo_id && !$model->user_id) {
                                    return '<div class="corporate-type-badge" title="Pago realizado directamente por la corporación para cubrir múltiples empleados">
                                        <i class="fas fa-building"></i>
                                        <div>PAGO CORPORATIVO</div>
                                        <small>Maestro</small>
                                        <div class="payment-method-badge">
                                            ' . $methodIcon . Html::encode($paymentMethod) . '
                                        </div>
                                    </div>';
                                }

                                if ($model->corporativo_id && $model->user_id && $model->tipo_pago !== 'corporativo') {
                                    $companyName = $model->corporativo ? $model->corporativo->nombre : '';
                                    return '<div class="company-covered-badge" title="Pago cubierto por la empresa en nombre del empleado">
                                        <i class="fas fa-hand-holding-usd"></i>
                                        <div>CUBIERTO POR EMPRESA</div>
                                        <small>' . Html::encode($companyName) . '</small>
                                        <div class="payment-method-badge">
                                            ' . $methodIcon . Html::encode($paymentMethod) . '
                                        </div>
                                    </div>';
                                }

                                if ($model->pago_corporativo_id) {
                                    return '<div class="affiliate-type-badge" title="Pago vinculado a un pago corporativo maestro">
                                        <i class="fas fa-user-friends"></i>
                                        <div>AFILIADO</div>
                                        <small>Vinculado a Corporativo</small>
                                        <div class="payment-method-badge">
                                            ' . $methodIcon . Html::encode($paymentMethod) . '
                                        </div>
                                    </div>';
                                }

                                return '<div class="individual-type-badge" title="Pago realizado directamente por el usuario">
                                    <i class="fas fa-user"></i>
                                    <div>PAGO PERSONAL</div>
                                    <small>Directo</small>
                                    <div class="payment-method-badge">
                                        ' . $methodIcon . Html::encode($paymentMethod) . '
                                    </div>
                                </div>';
                            },
                            'format' => 'raw',
                            'contentOptions' => function ($model) {
                                if ($model->tipo_pago === 'corporativo' && $model->corporativo_id && !$model->user_id) {
                                    return ['style' => 'text-align: center; vertical-align: middle;', 'class' => 'corporate-tipo-cell'];
                                }
                                if ($model->corporativo_id && $model->user_id && $model->tipo_pago !== 'corporativo') {
                                    return ['style' => 'text-align: center; vertical-align: middle;', 'class' => 'company-covered-tipo-cell'];
                                }
                                if ($model->pago_corporativo_id) {
                                    return ['style' => 'text-align: center; vertical-align: middle;', 'class' => 'affiliate-tipo-cell'];
                                }
                                return ['style' => 'text-align: center; vertical-align: middle;', 'class' => 'individual-tipo-cell'];
                            },
                            'headerOptions' => ['style' => 'text-align: center;'],
                            'filter' => [
                                'corporativo' => '🏛️ PAGO CORPORATIVO (Maestro)',
                                'cubierto'    => '🏢 CUBIERTO POR EMPRESA',
                                'afiliado'    => '👥 AFILIADO',
                                'individual'  => '📝 PAGO PERSONAL',
                            ],
                            'filterType' => GridView::FILTER_SELECT2,
                            'filterWidgetOptions' => [
                                'options' => ['placeholder' => 'Filtrar tipo de pago...'],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ],
                        ],

                        // Reference Column with visual connection
                        [
                            'attribute' => 'numero_referencia_pago',
                            'header' => 'REFERENCIA /<br>CONEXIÓN',
                            'value' => function ($model) {
                                $ref = $model->numero_referencia_pago ?? 'N/A';

                                if ($model->pago_corporativo_id && $model->pagoCorporativo) {
                                    $parentRef = $model->pagoCorporativo->numero_referencia_pago ?? 'N/A';
                                    return '<div class="payment-connection">
                                        <div><small>' . Html::encode($ref) . '</small></div>
                                        <div class="connection-line">
                                            <i class="fas fa-arrow-up text-success"></i>
                                            <small class="text-muted">Vinculado a: ' . Html::encode($parentRef) . '</small>
                                        </div>
                                    </div>';
                                }

                                if ($model->tipo_pago === 'corporativo' && $model->corporativo_id && !$model->user_id) {
                                    $affiliateCount = count($model->pagosAfiliados);
                                    return '<div class="parent-payment-ref">
                                        <div><strong>' . Html::encode($ref) . '</strong></div>
                                        <div>
                                            <small class="text-success">
                                                <i class="fas fa-sitemap"></i> ' . $affiliateCount . ' empleado(s) vinculado(s)
                                            </small>
                                        </div>
                                    </div>';
                                }

                                return '<span class="text-muted">' . Html::encode($ref) . '</span>';
                            },
                            'format' => 'raw',
                            'contentOptions' => function ($model) {
                                return ['style' => 'text-align: center; vertical-align: middle;'];
                            },
                        ],

                        // Solvent Status Column
                        [
                            'label' => 'SOLVENTE',
                            'attribute' => 'estatus_solvente',
                            'value' => function ($model) {
                                $isSolvente = $model->userDatos ? $model->userDatos->estatus_solvente : 'No';
                                if (in_array(strtolower($isSolvente), ['si', 'sí', '1', 'true'])) {
                                    return '<span class="badge badge-success" style="padding: 6px 12px;"><i class="fas fa-check-circle"></i> SI</span>';
                                }
                                return '<span class="badge badge-danger" style="padding: 6px 12px;"><i class="fas fa-times-circle"></i> NO</span>';
                            },
                            'format' => 'raw',
                            'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                            'headerOptions' => ['style' => 'text-align: center;'],
                            'filter' => ['SI' => 'SI', 'NO' => 'NO'],
                        ],

                        // Payment Date Column
                        [
                            'attribute' => 'fecha_pago',
                            'format' => 'date',
                            'hAlign' => GridView::ALIGN_CENTER,
                            'contentOptions' => ['style' => 'white-space: nowrap;'],
                            'filterInputOptions' => [
                                'placeholder' => 'Ej: 10, 2024, 15/09',
                                'class' => 'form-control',
                            ],
                        ],

                        // Amount USD Column
                        [
                            'attribute' => 'monto_pagado',
                            'value' => function ($model) {
                                return '<strong>$ ' . number_format($model->monto_pagado, 2) . '</strong> <span class="text-muted">USD</span>';
                            },
                            'header' => 'MONTO<br>PAGADO USD',
                            'format' => 'raw',
                            'hAlign' => GridView::ALIGN_RIGHT,
                            'headerOptions' => [
                                'style' => 'width: 80px; text-align: right;',
                            ],
                            'contentOptions' => ['style' => 'white-space: nowrap;'],
                        ],

                        // Amount Bs Column
                        [
                            'attribute' => 'monto_usd',
                            'value' => function ($model) {
                                return '<strong>Bs ' . number_format($model->monto_usd, 2) . '</strong>';
                            },
                            'header' => 'MONTO<br>PAGADO BS',
                            'format' => 'raw',
                            'hAlign' => GridView::ALIGN_RIGHT,
                            'headerOptions' => [
                                'style' => 'width: 80px; text-align: right;',
                            ],
                            'contentOptions' => [
                                'style' => 'white-space: nowrap; font-weight: bold;',
                            ],
                        ],

                        // Conciliation Status Column with Switch
                        [
                            'attribute' => 'estatus',
                            'format' => 'raw',
                            'value' => function ($model) {
                                $isActive = ($model->estatus == 'Conciliado' || $model->estatus == '1' || $model->estatus == 'Activo');

                                return SwitchInput::widget([
                                    'name' => 'estatus_' . $model->id,
                                    'value' => $isActive,
                                    'pluginOptions' => [
                                        'size' => 'large',
                                        'onText' => 'Conciliado',
                                        'offText' => 'Por Conciliar',
                                        'onColor' => 'success',
                                        'offColor' => 'danger',
                                    ],
                                    'pluginEvents' => [
                                        'switchChange.bootstrapSwitch' => "function(event, state) {
                                        var currentRow = $(event.target).closest('tr');
                                        var solventeCell = currentRow.find('td').eq(6);
                                        
                                        $.ajax({
                                            url: '" . Url::to(['/pagos/updatestatus']) . "',
                                            type: 'POST',
                                            data: {
                                                id: " . $model->id . ",
                                                status: state ? 1 : 0,
                                                _csrf: '" . Yii::$app->request->getCsrfToken() . "'
                                            },
                                            success: function(response) {
                                                if (response.success) {
                                                    /* ---- Repaint the row background immediately ---- */
                                                    currentRow.attr('data-conciliado', state ? '1' : '0');
                                                    /* ------------------------------------------------ */

                                                    var newSolventeStatus = state ? '<span class=\"badge badge-success\"><i class=\"fas fa-check-circle\"></i> SI</span>' : '<span class=\"badge badge-danger\"><i class=\"fas fa-times-circle\"></i> NO</span>';
                                                    solventeCell.html(newSolventeStatus);
                                                } else {
                                                    $(event.target).bootstrapSwitch('state', !state, true);
                                                    alert('Error: ' + response.error);
                                                }
                                            },
                                            error: function(xhr) {
                                                $(event.target).bootstrapSwitch('state', !state, true);
                                                alert('Error del servidor: ' + xhr.responseText);
                                            }
                                        });
                                    }"
                                    ]
                                ]);
                            },
                            'label' => 'CONCILIACION',
                            'contentOptions' => ['style' => 'text-align: center; vertical-align: middle; width: 140px;'],
                            'headerOptions' => ['style' => 'text-align: center;'],
                            'filter' => $estatusList,
                            'filterType' => GridView::FILTER_SELECT2,
                            'filterWidgetOptions' => [
                                'options' => ['placeholder' => 'Filtrar estatus...'],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ],
                        ],

                        // Actions Column
                        [
                            'class' => ActionColumn::class,
                            'header' => 'ACCIONES',
                            'headerOptions' => [
                                'style' => 'width: 100px; text-align: center;',
                                'class' => 'kv-action-column'
                            ],
                            'contentOptions' => [
                                'style' => 'text-align: center; vertical-align: middle;',
                                'class' => 'kv-action-column'
                            ],
                            'buttons' => [
                                'view' => function ($url, $model, $key) {
                                    return Html::a('<i class="fas fa-eye"></i>', $url, [
                                        'title' => 'Ver Pago',
                                        'data-pjax' => '0',
                                        'class' => 'action-btn action-btn-view',
                                        'style' => 'margin: 0 4px;',
                                    ]);
                                },
                                'update' => function ($url, $model, $key) {
                                    return Html::a('<i class="fas fa-edit"></i>', $url, [
                                        'title' => 'Editar Pago',
                                        'data-pjax' => '0',
                                        'class' => 'action-btn action-btn-update',
                                        'style' => 'margin: 0 4px;',
                                    ]);
                                },
                                'delete' => function ($url, $model, $key) {
                                    return Html::a('<i class="fas fa-trash-alt"></i>', $url, [
                                        'title' => 'Eliminar Pago',
                                        'data-confirm' => '¿Está seguro de que desea eliminar este pago?',
                                        'data-method' => 'post',
                                        'data-pjax' => '0',
                                        'class' => 'action-btn action-btn-delete',
                                        'style' => 'margin: 0 4px;',
                                    ]);
                                },
                            ],
                            'urlCreator' => function ($action, Pagos $model, $key, $index, $column) {
                                return Url::toRoute([$action, 'id' => $model->id]);
                            },
                        ],
                    ],
                ]); ?>

            </div> <!-- /#grid-scroll-container -->

        </div>
        <div class="card-footer bg-white border-top">
            <div class="legend-container">
                <div class="legend-title">
                    <i class="fas fa-tags mr-2"></i> Leyenda de Tipos de Pago
                </div>
                <div class="legend-items">
                    <div class="legend-item">
                        <span class="badge" style="background:#6610f2; width:40px; height:24px;"></span>
                        <span><strong>PAGO CORPORATIVO</strong> - Pago maestro que cubre múltiples empleados</span>
                    </div>
                    <div class="legend-item">
                        <span class="badge" style="background:#20c997; width:40px; height:24px;"></span>
                        <span><strong>CUBIERTO POR EMPRESA</strong> - Empresa pagó por empleado específico</span>
                    </div>
                    <div class="legend-item">
                        <span class="badge" style="background:#ffc107; width:40px; height:24px;"></span>
                        <span><strong>AFILIADO</strong> - Vinculado a pago corporativo</span>
                    </div>
                    <div class="legend-item">
                        <span class="badge" style="background:#17a2b8; width:40px; height:24px;"></span>
                        <span><strong>PAGO PERSONAL</strong> - Usuario pagó directamente</span>
                    </div>

                    <div class="legend-item" style="border-left: 3px solid #ffaa44; padding-left: 10px; margin-left: 10px;">
                        <span class="badge" style="background:#fff8e1; border: 1px solid #ffaa44; width:40px; height:24px;"></span>
                        <span><strong>POR CONCILIAR</strong> - Fila con fondo ámbar (requiere atención)</span>
                    </div>
                    <div class="legend-item" style="border-left: 3px solid #107c10; padding-left: 10px;">
                        <span class="badge" style="background:#fafbfc; border: 1px solid rgba(16,124,16,0.45); width:40px; height:24px;"></span>
                        <span><strong>CONCILIADO</strong> - Fila neutra (procesado)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<'JS'
// ============================================================
// EYE-CATCHING DUAL SCROLLBAR SYSTEM — pagos/index.php
// ============================================================
$(document).ready(function () {
    var $gridWrapper = $('#grid-scroll-container');
    var $shell       = $('.top-scrollbar-shell');
    var $top         = $('.top-native-scrollbar');
    var $dummy       = $('.top-native-dummy');
    var $arrowLeft   = $('.top-scrollbar-arrow-left');
    var $arrowRight  = $('.top-scrollbar-arrow-right');

    var $activeScroller = $();
    var syncingFrom     = null;

    if (!$gridWrapper.length || !$shell.length || !$top.length) {
        return;
    }

    function getActiveGridScroller() {
        var $candidates = $gridWrapper.find('*').addBack();
        var best = null;
        var bestWidth = 0;

        $candidates.each(function () {
            var el = this;
            if (!el || !el.clientWidth) return;
            if (el.scrollWidth > el.clientWidth + 1 && el.scrollWidth > bestWidth) {
                best = el;
                bestWidth = el.scrollWidth;
            }
        });

        return best ? $(best) : $gridWrapper;
    }

    function syncBars(source) {
        var gridEl = $activeScroller.get(0);
        var topEl  = $top.get(0);
        if (!gridEl || !topEl) return;

        if (source === 'top') {
            if (syncingFrom === 'grid') return;
            syncingFrom = 'top';
            gridEl.scrollLeft = topEl.scrollLeft;
            syncingFrom = null;
            return;
        }

        if (source === 'grid') {
            if (syncingFrom === 'top') return;
            syncingFrom = 'grid';
            topEl.scrollLeft = gridEl.scrollLeft;
            syncingFrom = null;
        }
    }

    function getMaxScrollWidth() {
        var maxWidth = 0;

        $gridWrapper.find('table').each(function () {
            if (this.scrollWidth > maxWidth) maxWidth = this.scrollWidth;
        });

        if ($gridWrapper[0] && $gridWrapper[0].scrollWidth > maxWidth) {
            maxWidth = $gridWrapper[0].scrollWidth;
        }

        if ($activeScroller[0] && $activeScroller[0].scrollWidth > maxWidth) {
            maxWidth = $activeScroller[0].scrollWidth;
        }

        return maxWidth;
    }

    function refreshTopBar() {
        $activeScroller = getActiveGridScroller();

        var gridEl = $activeScroller.get(0);
        if (!gridEl || !$top.length || !$dummy.length) return;

        var scrollWidth   = getMaxScrollWidth();
        var viewportWidth = gridEl.clientWidth;
        var hasHorizontalScroll = scrollWidth > (viewportWidth + 1);

        if (hasHorizontalScroll) {
            $dummy.css('width', (scrollWidth + 20) + 'px');
            $shell.css('display', 'flex');

            setTimeout(function () {
                var maxScroll = scrollWidth - $top[0].clientWidth;
                if ($top[0].scrollLeft > maxScroll) {
                    $top[0].scrollLeft = maxScroll;
                }
            }, 20);

            syncBars('grid');
        } else {
            $shell.hide();
        }
    }

    $top.off('scroll.dual').on('scroll.dual', function () {
        syncBars('top');
    });

    function scrollByStep(direction) {
        var gridEl = $activeScroller.get(0);
        if (!gridEl) return;

        var step = Math.max(240, Math.floor(gridEl.clientWidth * 0.6));
        var target = gridEl.scrollLeft + (direction * step);

        var maxScroll = gridEl.scrollWidth - gridEl.clientWidth;
        if (target < 0) target = 0;
        if (target > maxScroll) target = maxScroll;

        if ('scrollBehavior' in document.documentElement.style) {
            gridEl.scrollTo({ left: target, behavior: 'smooth' });
        } else {
            gridEl.scrollLeft = target;
        }
    }

    $arrowLeft.off('click.dual').on('click.dual', function (e) {
        e.preventDefault();
        scrollByStep(-1);
    });

    $arrowRight.off('click.dual').on('click.dual', function (e) {
        e.preventDefault();
        scrollByStep(1);
    });

    function bindGridScroll() {
        $gridWrapper.find('*').off('scroll.dual-grid');
        $gridWrapper.off('scroll.dual-grid');

        $activeScroller = getActiveGridScroller();
        $activeScroller.off('scroll.dual-grid').on('scroll.dual-grid', function () {
            syncBars('grid');
        });
    }

    bindGridScroll();
    refreshTopBar();
    setTimeout(refreshTopBar, 120);
    setTimeout(refreshTopBar, 320);

    $(window).off('resize.dual').on('resize.dual', refreshTopBar);

    $(document).on('pjax:complete yiiGridViewUpdated ajaxComplete', function () {
        setTimeout(function () {
            bindGridScroll();
            refreshTopBar();
        }, 150);
    });
});
JS;

$this->registerJs($js, \yii\web\View::POS_READY);
?>