<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\UserHelper;
use app\models\RmClinica;
use app\models\AgenteFuerza;
use app\components\SaldoHelper;
use yii\bootstrap4\Modal;

/** @var yii\web\View $this */
/** @var app\models\UserDatos $model */
/** @var string $estado */
/** @var string $municipio */
/** @var string $parroquia */
/** @var string $ciudad */
/** @var int|null $clinica_id */

// ============================================
// HELPER FUNCTIONS
// ============================================

if (!function_exists('formatDateTime')) {
    function formatDateTime($value)
    {
        return $value ? Yii::$app->formatter->asDatetime($value) : 'N/A';
    }
}

if (!function_exists('getDefaultAvatar')) {
    /**
     * Returns the default avatar path based on sex.
     * Looks in @web/img/avatars/woman.png or @web/img/avatars/man.png
     *
     * @param string|null $sexo
     * @return string Relative URL to the default avatar
     */
    function getDefaultAvatar($sexo)
    {
        $sexo = strtolower(trim((string)$sexo));

        if (in_array($sexo, ['femenino', 'f', 'mujer'])) {
            return Yii::getAlias('@web/img/avatars/woman.png');
        }

        // Default to man for Masculino, M, or unknown
        return Yii::getAlias('@web/img/avatars/man.png');
    }
}

if (!function_exists('getDefaultIdImage')) {
    /**
     * Returns the default ID document image.
     * Looks in @web/img/avatars/cedula.jpg
     *
     * @return string Relative URL to the default ID image
     */
    function getDefaultIdImage()
    {
        return Yii::getAlias('@web/img/avatars/cedula.jpg');
    }
}

if (!function_exists('formatBooleanIcon')) {
    function formatBooleanIcon($value)
    {
        if ($value) {
            return '<span class="text-success" style="font-size: 0.82rem;"><i class="fas fa-check-circle"></i> Sí</span>';
        } else {
            return '<span class="text-danger" style="font-size: 0.82rem;"><i class="fas fa-times-circle"></i> No</span>';
        }
    }
}

// Register CSS for Microsoft-style design with enhanced profile header and image modal
$this->registerCss("
/* ============================================
   MICROSOFT-STYLE DESIGN SYSTEM
   WITH ENHANCED PROFILE HEADER
   ============================================ */
   
:root {
    --ms-blue: #0078D4;
    --ms-blue-dark: #106EBE;
    --ms-blue-light: #E5F1FB;
    --ms-purple: #6C2B8A;
    --ms-green: #107C10;
    --ms-red: #D13438;
    --ms-orange: #D83B01;
    --ms-teal: #008272;
    --ms-pink: #C239B3;
    --ms-gray-100: #bfd8ea;
    --ms-gray-200: #EDEBE9;
    --ms-gray-300: #E1DFDD;
    --ms-gray-400: #C8C6C4;
    --ms-gray-500: #A19F9D;
    --ms-gray-600: #797775;
    --ms-gray-700: #605E5C;
    --ms-gray-800: #3B3A39;
    --ms-gray-900: #201F1E;
    --ms-shadow: 0 1.6px 3.6px 0 rgba(0,0,0,0.132), 0 0.3px 0.9px 0 rgba(0,0,0,0.108);
    --ms-shadow-hover: 0 6.4px 14.4px 0 rgba(0,0,0,0.132), 0 1.2px 3.6px 0 rgba(0,0,0,0.108);
    --ms-radius: 3px;
    --ms-transition: all 0.2s cubic-bezier(0.1, 0.9, 0.2, 1);
}

/* ============================================
   BASE FONT SIZE - MINIMAL INCREASE
   ============================================ */
html, body, .main-container {
    font-size: 14.5px !important;
}

/* Microsoft Navigation Tabs - Colored Version */
.ms-nav-tabs {
    display: flex;
    gap: 0.35rem;
    padding: 0.5rem;
    background: var(--ms-gray-100);
    border-radius: var(--ms-radius);
    margin-bottom: 1.25rem;
    border: 1px solid var(--ms-gray-300);
    flex-wrap: wrap;
}

.ms-nav-item {
    flex: 1;
    min-width: 110px;
}

/* BASE STYLES - Applied to all tabs */
.ms-nav-btn {
    width: 100%;
    padding: 0.5rem 0.35rem;
    border: 2px solid transparent;
    border-radius: var(--ms-radius);
    font-weight: 600;
    font-size: 0.82rem !important;
    text-align: center;
    transition: var(--ms-transition);
    cursor: pointer;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    position: relative;
    background: transparent !important;
    color: var(--ms-gray-700);
}

.ms-nav-btn i {
    font-size: 0.95rem !important;
    transition: var(--ms-transition);
}

.ms-nav-btn:hover {
    text-decoration: none;
    transform: translateY(-1px);
}

/* ============================================
   COLORED TAB STYLES - DEFAULT STATE
   ============================================ */

/* 1. Datos Personales - Blue */
.ms-nav-btn.blue-tab {
    color: var(--ms-blue) !important;
    background: transparent !important;
    border-color: transparent !important;
}

.ms-nav-btn.blue-tab i {
    color: var(--ms-blue) !important;
}

.ms-nav-btn.blue-tab:hover {
    background: var(--ms-blue-light) !important;
    border-color: var(--ms-blue) !important;
    color: var(--ms-blue) !important;
}

.ms-nav-btn.blue-tab.active {
    background: var(--ms-blue) !important;
    color: #ffffff !important;
    border-color: var(--ms-blue-dark) !important;
    box-shadow: var(--ms-shadow) !important;
}

.ms-nav-btn.blue-tab.active i {
    color: #ffffff !important;
}

.ms-nav-btn.blue-tab.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 20%;
    right: 20%;
    height: 3px;
    background: #ffffff;
    border-radius: 2px;
}

/* 2. Contactos - Teal */
.ms-nav-btn.teal-tab {
    color: var(--ms-teal) !important;
    background: transparent !important;
    border-color: transparent !important;
}

.ms-nav-btn.teal-tab i {
    color: var(--ms-teal) !important;
}

.ms-nav-btn.teal-tab:hover {
    background: #E6F4F2 !important;
    border-color: var(--ms-teal) !important;
    color: var(--ms-teal) !important;
}

.ms-nav-btn.teal-tab.active {
    background: var(--ms-teal) !important;
    color: #ffffff !important;
    border-color: #006A5E !important;
    box-shadow: var(--ms-shadow) !important;
}

.ms-nav-btn.teal-tab.active i {
    color: #ffffff !important;
}

.ms-nav-btn.teal-tab.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 20%;
    right: 20%;
    height: 3px;
    background: #ffffff;
    border-radius: 2px;
}

/* 3. Declaración Salud - Green */
.ms-nav-btn.green-tab {
    color: var(--ms-green) !important;
    background: transparent !important;
    border-color: transparent !important;
}

.ms-nav-btn.green-tab i {
    color: var(--ms-green) !important;
}

.ms-nav-btn.green-tab:hover {
    background: #E8F5E8 !important;
    border-color: var(--ms-green) !important;
    color: var(--ms-green) !important;
}

.ms-nav-btn.green-tab.active {
    background: var(--ms-green) !important;
    color: #ffffff !important;
    border-color: #0A6A0A !important;
    box-shadow: var(--ms-shadow) !important;
}

.ms-nav-btn.green-tab.active i {
    color: #ffffff !important;
}

.ms-nav-btn.green-tab.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 20%;
    right: 20%;
    height: 3px;
    background: #ffffff;
    border-radius: 2px;
}

/* 4. Pre-existencias - Purple */
.ms-nav-btn.purple-tab {
    color: var(--ms-purple) !important;
    background: transparent !important;
    border-color: transparent !important;
}

.ms-nav-btn.purple-tab i {
    color: var(--ms-purple) !important;
}

.ms-nav-btn.purple-tab:hover {
    background: #F3E8F7 !important;
    border-color: var(--ms-purple) !important;
    color: var(--ms-purple) !important;
}

.ms-nav-btn.purple-tab.active {
    background: var(--ms-purple) !important;
    color: #ffffff !important;
    border-color: #5A2373 !important;
    box-shadow: var(--ms-shadow) !important;
}

.ms-nav-btn.purple-tab.active i {
    color: #ffffff !important;
}

.ms-nav-btn.purple-tab.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 20%;
    right: 20%;
    height: 3px;
    background: #ffffff;
    border-radius: 2px;
}

/* 5. Atenciones - Red */
.ms-nav-btn.red-tab {
    color: var(--ms-red) !important;
    background: transparent !important;
    border-color: transparent !important;
}

.ms-nav-btn.red-tab i {
    color: var(--ms-red) !important;
}

.ms-nav-btn.red-tab:hover {
    background: #FDE8E8 !important;
    border-color: var(--ms-red) !important;
    color: var(--ms-red) !important;
}

.ms-nav-btn.red-tab.active {
    background: var(--ms-red) !important;
    color: #ffffff !important;
    border-color: #B12A2E !important;
    box-shadow: var(--ms-shadow) !important;
}

.ms-nav-btn.red-tab.active i {
    color: #ffffff !important;
}

.ms-nav-btn.red-tab.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 20%;
    right: 20%;
    height: 3px;
    background: #ffffff;
    border-radius: 2px;
}

/* 6. Pagos - Orange */
.ms-nav-btn.orange-tab {
    color: var(--ms-orange) !important;
    background: transparent !important;
    border-color: transparent !important;
}

.ms-nav-btn.orange-tab i {
    color: var(--ms-orange) !important;
}

.ms-nav-btn.orange-tab:hover {
    background: #FEF0E6 !important;
    border-color: var(--ms-orange) !important;
    color: var(--ms-orange) !important;
}

.ms-nav-btn.orange-tab.active {
    background: var(--ms-orange) !important;
    color: #ffffff !important;
    border-color: #B32E00 !important;
    box-shadow: var(--ms-shadow) !important;
}

.ms-nav-btn.orange-tab.active i {
    color: #ffffff !important;
}

.ms-nav-btn.orange-tab.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 20%;
    right: 20%;
    height: 3px;
    background: #ffffff;
    border-radius: 2px;
}

/* Microsoft Card */
.ms-card {
    background: white;
    border: 1px solid var(--ms-gray-300);
    border-radius: var(--ms-radius);
    margin-bottom: 1.25rem;
    box-shadow: var(--ms-shadow);
    transition: var(--ms-transition);
}

.ms-card:hover {
    box-shadow: var(--ms-shadow-hover);
}

.ms-card-header {
    padding: 0.6rem 1rem;
    border-bottom: 1px solid var(--ms-gray-300);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--ms-gray-100);
    border-radius: var(--ms-radius) var(--ms-radius) 0 0;
}

.ms-card-header i {
    font-size: 1.05rem !important;
}

.ms-card-header h5 {
    margin: 0;
    font-weight: 600;
    font-size: 0.9rem !important;
    color: var(--ms-gray-900);
}

.ms-card-body {
    padding: 0.9rem;
}

/* ============================================
   ENHANCED PROFILE HEADER WITH PHOTOS
   ============================================ */
.ms-profile-header {
    background: linear-gradient(135deg, var(--ms-blue) 0%, #0a5c8e 100%);
    color: white;
    padding: 2rem 2.5rem;
    border-radius: var(--ms-radius);
    margin-bottom: 1.5rem;
    position: relative;
    overflow: hidden;
}

.ms-profile-header::before {
    content: '';
    position: absolute;
    top: -60%;
    right: -10%;
    width: 400px;
    height: 400px;
    background: rgba(255,255,255,0.04);
    border-radius: 50%;
}

.ms-profile-header::after {
    content: '';
    position: absolute;
    bottom: -40%;
    left: -5%;
    width: 300px;
    height: 300px;
    background: rgba(255,255,255,0.02);
    border-radius: 50%;
}

.ms-profile-header-content {
    position: relative;
    z-index: 1;
}

/* Profile Layout - Flexbox Grid */
.ms-profile-grid {
    display: flex;
    align-items: center;
    gap: 2.5rem;
    flex-wrap: wrap;
}

/* Left Column - Avatar */
.ms-profile-avatar-col {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    flex-shrink: 0;
}

.ms-profile-avatar-wrapper {
    position: relative;
    cursor: pointer;
}

.ms-profile-avatar {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    border: 4px solid rgba(255,255,255,0.9);
    object-fit: cover;
    box-shadow: 0 8px 32px rgba(0,0,0,0.25);
    background: rgba(255,255,255,0.1);
    transition: var(--ms-transition);
    display: block;
}

.ms-profile-avatar:hover {
    transform: scale(1.05);
    border-color: #ffffff;
    box-shadow: 0 12px 40px rgba(0,0,0,0.35);
}

.ms-profile-avatar-placeholder {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    border: 4px solid rgba(255,255,255,0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    background: rgba(255,255,255,0.1);
    color: rgba(255,255,255,0.6);
    box-shadow: 0 8px 32px rgba(0,0,0,0.25);
}

/* Click hint badge */
.ms-click-hint {
    position: absolute;
    bottom: -2px;
    right: -2px;
    background: rgba(0,0,0,0.7);
    color: white;
    font-size: 0.55rem;
    padding: 0.2rem 0.5rem;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255,255,255,0.2);
    white-space: nowrap;
    font-weight: 500;
    letter-spacing: 0.3px;
}

.ms-click-hint i {
    font-size: 0.5rem;
}

.ms-profile-avatar-label {
    font-size: 0.7rem;
    opacity: 0.85;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    font-weight: 600;
    color: rgba(255,255,255,0.9);
    text-align: center;
}

.ms-profile-avatar-label .badge-light {
    background: rgba(255, 255, 255, 0.25);
    color: #ffffff;
    font-weight: 500;
    padding: 0.15rem 0.4rem;
    border-radius: 8px;
    text-transform: none;
    letter-spacing: 0;
    margin-left: 0.25rem;
}

/* Middle Column - User Info */
.ms-profile-info-col {
    flex: 1;
    min-width: 250px;
}

.ms-profile-title {
    font-size: 1.6rem !important;
    font-weight: 700;
    margin-bottom: 0.4rem;
    letter-spacing: -0.3px;
}

.ms-profile-title i {
    font-size: 1.4rem;
    opacity: 0.7;
    margin-right: 0.5rem;
}

/* ID Card Section - Enhanced */
.ms-profile-id-section {
    display: inline-flex;
    align-items: center;
    gap: 1rem;
    margin-top: 0.3rem;
    padding: 0.5rem 1rem 0.5rem 0.8rem;
    background: rgba(255,255,255,0.1);
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.15);
    backdrop-filter: blur(8px);
    transition: var(--ms-transition);
}

.ms-profile-id-section:hover {
    background: rgba(255,255,255,0.15);
    border-color: rgba(255,255,255,0.25);
}

.ms-profile-id-photo-wrapper {
    position: relative;
    cursor: pointer;
    flex-shrink: 0;
}

.ms-profile-id-photo {
    width: 56px;
    height: 56px;
    border-radius: 4px;
    border: 2px solid rgba(255,255,255,0.7);
    object-fit: cover;
    background: rgba(255,255,255,0.1);
    transition: var(--ms-transition);
    display: block;
}

.ms-profile-id-photo:hover {
    transform: scale(1.05);
    border-color: #ffffff;
}

.ms-profile-id-photo-placeholder {
    width: 56px;
    height: 56px;
    border-radius: 4px;
    border: 2px solid rgba(255,255,255,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    background: rgba(255,255,255,0.1);
    color: rgba(255,255,255,0.6);
    flex-shrink: 0;
}

.ms-profile-id-info {
    display: flex;
    flex-direction: column;
    gap: 0.05rem;
}

.ms-profile-id-label {
    font-size: 0.6rem;
    opacity: 0.6;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    font-weight: 600;
    color: rgba(255,255,255,0.8);
}

.ms-profile-id-number {
    font-size: 1.1rem !important;
    font-weight: 600;
    letter-spacing: 0.5px;
    color: #ffffff;
}

/* Asesor Info */
.ms-profile-asesor-info {
    margin-top: 0.5rem;
    font-size: 0.85rem !important;
    opacity: 0.85;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.ms-profile-asesor-info i {
    opacity: 0.7;
}

/* Right Column - Status & Actions */
.ms-profile-actions-col {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.75rem;
    flex-shrink: 0;
}

/* Status Badge in Header */
.ms-profile-status {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.35rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem !important;
    font-weight: 600;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.15);
    color: white;
}

.ms-profile-status i {
    font-size: 0.5rem;
    color: #6bcb77;
}

/* Action Buttons in Header */
.ms-profile-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    justify-content: flex-end;
}

/* Microsoft Buttons with Colors */
.ms-btn {
    padding: 0.4rem 1rem;
    border: 1.5px solid transparent;
    border-radius: var(--ms-radius);
    font-weight: 600;
    font-size: 0.78rem !important;
    transition: var(--ms-transition);
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

.ms-btn:hover {
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

/* Button Colors */
.ms-btn-pdf {
    background: #d13438;
    color: white;
}

.ms-btn-pdf:hover {
    background: #b12a2e;
    color: white;
}

.ms-btn-edit {
    background: var(--ms-blue);
    color: white;
}

.ms-btn-edit:hover {
    background: var(--ms-blue-dark);
    color: white;
}

.ms-btn-payment {
    background: var(--ms-green);
    color: white;
}

.ms-btn-payment:hover {
    background: #0a6a0a;
    color: white;
}

.ms-btn-back {
    background: var(--ms-gray-500);
    color: white;
}

.ms-btn-back:hover {
    background: var(--ms-gray-600);
    color: white;
}

.ms-btn-outline {
    background: transparent;
    border-color: rgba(255,255,255,0.4);
    color: white;
}

.ms-btn-outline:hover {
    background: rgba(255,255,255,0.1);
    border-color: white;
    color: white;
}

/* Info Grid */
.ms-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.4rem 1.25rem;
}

.ms-info-item {
    display: flex;
    align-items: baseline;
    gap: 0.35rem;
    padding: 0.35rem 0;
    border-bottom: 1px solid var(--ms-gray-200);
}

.ms-info-item:last-child {
    border-bottom: none;
}

.ms-info-label {
    font-weight: 600;
    color: var(--ms-gray-700);
    font-size: 0.82rem !important;
    min-width: 120px;
}

.ms-info-value {
    color: var(--ms-gray-900);
    font-size: 0.82rem !important;
}

/* Balance Card */
.ms-balance-card {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    color: white;
    padding: 1.1rem;
    border-radius: var(--ms-radius);
    margin-bottom: 1.25rem;
    position: relative;
    overflow: hidden;
}

.ms-balance-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 200px;
    height: 200px;
    background: rgba(255,255,255,0.03);
    border-radius: 50%;
}

.ms-balance-amount {
    font-size: 1.6rem !important;
    font-weight: 700;
    margin: 0.3rem 0;
    color: #00d4ff;
}

.ms-balance-label {
    font-size: 0.82rem !important;
    opacity: 0.8;
    margin-bottom: 0.15rem;
}

/* ============================================
   PROGRESS SECTION WITH WHITE TEXT
   ============================================ */

.ms-progress-container {
    margin-top: 0.6rem;
}

.ms-progress {
    height: 5px;
    background: rgba(255,255,255,0.15);
    border-radius: 3px;
    overflow: hidden;
}

.ms-progress-bar {
    height: 100%;
    border-radius: 3px;
    transition: width 0.6s ease;
}

/* HIGH SPECIFICITY - Force white text */
.ms-balance-card .ms-progress-label,
.ms-progress-label,
div.ms-progress-label,
.ms-progress-label span,
.ms-progress-label .text-muted,
.ms-progress-label .small {
    color: #ffffff !important;
}

.ms-progress-label span {
    color: #ffffff !important;
}

.ms-balance-card .ms-progress-container .ms-progress-label {
    color: #ffffff !important;
}

.ms-balance-card .ms-progress-label .text-muted {
    color: rgba(255, 255, 255, 0.8) !important;
}

.ms-progress-label {
    display: flex;
    justify-content: space-between;
    font-size: 0.7rem !important;
    margin-top: 0.15rem;
    color: #ffffff !important;
    opacity: 1 !important;
}

/* Status Badge */
.ms-status-badge {
    display: inline-block;
    padding: 0.2rem 0.5rem;
    border-radius: 10px;
    font-size: 0.75rem !important;
    font-weight: 600;
}

.ms-status-active {
    background: #dff0d8;
    color: #3c763d;
}

.ms-status-inactive {
    background: #f2dede;
    color: #a94442;
}

/* Table Styles */
.table {
    font-size: 0.82rem !important;
}

.table th {
    font-size: 0.82rem !important;
    font-weight: 600;
}

.table td {
    font-size: 0.82rem !important;
    padding: 0.4rem;
}

/* ============================================
   DEFAULT IMAGE INDICATOR
   ============================================ */
.clickable-image[data-is-default=\"1\"] {
    opacity: 0.85;
    filter: grayscale(15%);
}

.clickable-image[data-is-default=\"1\"]:hover {
    opacity: 1;
    filter: grayscale(0%);
}

/* ============================================
   IMAGE MODAL / LIGHTBOX STYLES
   ============================================ */
.image-modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.92);
    z-index: 9999;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    animation: fadeIn 0.3s ease;
}

.image-modal-overlay.active {
    display: flex;
}

.image-modal-content {
    max-width: 90%;
    max-height: 90%;
    position: relative;
    animation: scaleIn 0.3s ease;
}

.image-modal-content img {
    max-width: 100%;
    max-height: 85vh;
    border-radius: 8px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.6);
    border: 3px solid rgba(255,255,255,0.15);
}

.image-modal-close {
    position: absolute;
    top: -40px;
    right: -40px;
    color: white;
    font-size: 2rem;
    cursor: pointer;
    transition: var(--ms-transition);
    background: rgba(255,255,255,0.1);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid rgba(255,255,255,0.3);
}

.image-modal-close:hover {
    background: rgba(255,255,255,0.2);
    transform: rotate(90deg);
}

.image-modal-caption {
    position: absolute;
    bottom: -40px;
    left: 0;
    right: 0;
    text-align: center;
    color: rgba(255,255,255,0.8);
    font-size: 0.9rem;
    font-weight: 500;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes scaleIn {
    from { transform: scale(0.8); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

/* Responsive */
@media (max-width: 992px) {
    .ms-profile-grid {
        gap: 1.5rem;
    }
}

@media (max-width: 768px) {
    .ms-info-grid {
        grid-template-columns: 1fr;
    }
    
    .ms-nav-tabs {
        flex-direction: column;
    }
    
    .ms-nav-item {
        min-width: 100%;
    }
    
    .ms-profile-header {
        padding: 1.25rem;
    }
    
    .ms-profile-grid {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 1rem;
    }
    
    .ms-profile-avatar-col {
        align-items: center;
    }
    
    .ms-profile-info-col {
        text-align: center;
        width: 100%;
    }
    
    .ms-profile-id-section {
        justify-content: center;
        flex-wrap: wrap;
        display: inline-flex;
    }
    
    .ms-profile-actions-col {
        align-items: center;
        width: 100%;
    }
    
    .ms-profile-actions {
        justify-content: center;
        width: 100%;
    }
    
    .ms-profile-actions .ms-btn {
        flex: 1;
        justify-content: center;
        min-width: 120px;
    }
    
    .image-modal-close {
        top: 10px;
        right: 10px;
    }
    
    .image-modal-caption {
        bottom: -35px;
    }
}

@media (max-width: 576px) {
    .ms-balance-amount {
        font-size: 1.3rem !important;
    }
    
    .ms-profile-avatar {
        width: 90px;
        height: 90px;
    }
    
    .ms-profile-avatar-placeholder {
        width: 90px;
        height: 90px;
        font-size: 3.2rem;
    }
    
    .ms-profile-id-photo {
        width: 48px;
        height: 48px;
    }
    
    .ms-profile-id-photo-placeholder {
        width: 48px;
        height: 48px;
        font-size: 1.3rem;
    }
    
    .ms-profile-title {
        font-size: 1.3rem !important;
    }
    
    .image-modal-content {
        max-width: 95%;
    }
    
    .ms-profile-actions .ms-btn {
        min-width: 100%;
    }
}
");

// Register JavaScript for image modal
$this->registerJs("
$(document).ready(function() {
    // Create modal overlay
    var modalHtml = `
        <div class=\"image-modal-overlay\" id=\"imageModal\">
            <div class=\"image-modal-content\">
                <img src=\"\" alt=\"Imagen ampliada\" id=\"modalImage\">
                <div class=\"image-modal-close\" id=\"modalClose\">
                    <i class=\"fas fa-times\"></i>
                </div>
                <div class=\"image-modal-caption\" id=\"modalCaption\"></div>
            </div>
        </div>
    `;
    $('body').append(modalHtml);

    // Open modal on image click
    $('.clickable-image').on('click', function(e) {
        e.preventDefault();
        var imgSrc = $(this).data('src') || $(this).attr('src');
        var imgAlt = $(this).data('caption') || $(this).attr('alt') || 'Imagen';
        $('#modalImage').attr('src', imgSrc);
        $('#modalImage').attr('alt', imgAlt);
        $('#modalCaption').text(imgAlt);
        $('#imageModal').addClass('active');
        $('body').css('overflow', 'hidden');
    });

    // Close modal on click outside, close button, or ESC key
    function closeModal() {
        $('#imageModal').removeClass('active');
        $('body').css('overflow', '');
    }

    $('#imageModal').on('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    $('#modalClose').on('click', function(e) {
        e.stopPropagation();
        closeModal();
    });

    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#imageModal').hasClass('active')) {
            closeModal();
        }
    });
});
");

// --- Detección y Carga de Clínica ---
$clinica = null;
$clinica_id_from_url = Yii::$app->request->get('clinica_id');

if (!empty($clinica_id_from_url)) {
    $clinica = RmClinica::findOne((int)$clinica_id_from_url);
    if (!$clinica) {
        $clinica = (object)['id' => (int)$clinica_id_from_url, 'nombre' => 'Clínica Desconocida'];
    }
}

$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol == 'DIRECTOR-COMERCIALIZACIÓN' || $rol == 'GERENTE-CLINICA' || $rol == 'Asesor' || $rol == 'COORDINADOR-CLINICA' || $rol == 'ATENCIÓN');

// --- Título y BREADCRUMBS ---
$titulo = 'PERFIL DEL AFILIADO: ' . $model->nombres . ' ' . $model->apellidos;
$asesor = AgenteFuerza::find()->where(['id' => $model->asesor_id])->one();

$titulo2 = "";
if ($asesor) {
    $titulo2 = 'ASESOR: ' . $asesor->id . ' - ' . $asesor->userDatos->nombres . " " . $asesor->userDatos->apellidos . " (" . $asesor->userDatos->user->username . ")";
}

$this->title = $titulo;

// Breadcrumbs
$this->params['breadcrumbs'][] = ['label' => 'CLÍNICAS', 'url' => ['/rm-clinica/index']];

if ($clinica && $clinica->id !== null) {
    $this->params['breadcrumbs'][] = ['label' => Html::encode($clinica->nombre), 'url' => ['/rm-clinica/view', 'id' => $clinica->id]];
    $this->params['breadcrumbs'][] = ['label' => 'AFILIADOS', 'url' => ['index-clinicas', 'clinica_id' => $clinica->id]];
} else {
    $this->params['breadcrumbs'][] = ['label' => 'AFILIADOS', 'url' => ['index']];
}
$this->params['breadcrumbs'][] = Html::encode($model->nombres . ' ' . $model->apellidos);

\yii\web\YiiAsset::register($this);

// --- CALCULAR SALDO ---
$datosSaldo = SaldoHelper::calcularSaldoDisponible($model->id);
$precioPlan = $datosSaldo['precio_plan'];
$coberturaPlan = $datosSaldo['cobertura_plan'];
$consumoActual = $datosSaldo['sumatoria_siniestros'];
$saldoDisponible = $datosSaldo['saldo_disponible'];
$porcentajeConsumido = $datosSaldo['porcentaje_consumido'];
$baseCalculo = $datosSaldo['base_calculo'];

$historialSiniestros = SaldoHelper::obtenerHistorialSiniestros($model->id);

$currentRoute = Yii::$app->controller->getRoute();
?>

<!-- Microsoft-style Navigation with Colored Tabs -->
<div class="ms-nav-tabs">
    <div class="ms-nav-item">
        <?= Html::a('<i class="fas fa-user"></i> Datos Personales', Url::to(['index']), [
            'class' => 'ms-nav-btn blue-tab' . ($currentRoute === 'user-datos/index' ? ' active' : ''),
        ]) ?>
    </div>
    <div class="ms-nav-item">
        <?= Html::a('<i class="fas fa-phone-alt"></i> Contactos', Url::to(['contactos-emergencia/index', 'user_id' => $model->id]), [
            'class' => 'ms-nav-btn teal-tab' . ($currentRoute === 'contactos-emergencia/index' ? ' active' : ''),
            'data-pjax' => '0'
        ]) ?>
    </div>
    <div class="ms-nav-item">
        <?= Html::a('<i class="fas fa-poll-h"></i> Declaración Salud', Url::to(['declaracion-de-salud/index', 'user_id' => $model->id]), [
            'class' => 'ms-nav-btn green-tab' . ($currentRoute === 'declaracion-salud/index' ? ' active' : ''),
            'data-pjax' => '0'
        ]) ?>
    </div>
    <div class="ms-nav-item">
        <?= Html::a('<i class="fas fa-notes-medical"></i> Pre-existencias', Url::to(['preexistencias/index', 'user_id' => $model->id]), [
            'class' => 'ms-nav-btn purple-tab' . ($currentRoute === 'preexistencias/index' ? ' active' : ''),
            'data-pjax' => '0'
        ]) ?>
    </div>
    <div class="ms-nav-item">
        <?= Html::a('<i class="fas fa-heartbeat"></i> Atenciones', Url::to(['sis-siniestro/index', 'user_id' => $model->id]), [
            'class' => 'ms-nav-btn red-tab' . ($currentRoute === 'sis-siniestro/index' ? ' active' : ''),
            'data-pjax' => '0'
        ]) ?>
    </div>
    <div class="ms-nav-item">
        <?= Html::a('<i class="fas fa-credit-card"></i> Pagos', Url::to(['contratos/index', 'user_id' => $model->id]), [
            'class' => 'ms-nav-btn orange-tab' . ($currentRoute === 'contratos/index' ? ' active' : ''),
            'data-pjax' => '0'
        ]) ?>
    </div>
</div>

<!-- Enhanced Profile Header with Photos -->
<div class="ms-profile-header">
    <div class="ms-profile-header-content">
        <div class="ms-profile-grid">
            <!-- Left Column: Avatar -->
            <div class="ms-profile-avatar-col">
                <div class="ms-profile-avatar-wrapper">
                    <?php
                    // ============================================
                    // Foto de Perfil with Default Fallback
                    // ============================================
                    $hasSelfie = !empty($model->selfie);
                    $selfieSrc = $hasSelfie ? $model->selfie : getDefaultAvatar($model->sexo);
                    $selfieCaption = $hasSelfie
                        ? 'Foto de Perfil - ' . Html::encode($model->nombres . ' ' . $model->apellidos)
                        : 'Foto de Perfil por defecto - ' . Html::encode($model->nombres . ' ' . $model->apellidos);
                    ?>
                    <?= Html::img($selfieSrc, [
                        'class' => 'ms-profile-avatar clickable-image',
                        'alt' => 'Foto de Perfil',
                        'title' => 'Click para ampliar',
                        'data-caption' => $selfieCaption,
                        'data-is-default' => $hasSelfie ? '0' : '1',
                    ]) ?>
                    <span class="ms-click-hint">
                        <i class="fas fa-search-plus"></i> Click
                    </span>
                </div>
                <span class="ms-profile-avatar-label">
                    <i class="fas fa-camera"></i> Foto de Perfil
                    <?php if (!$hasSelfie): ?>
                        <span class="badge badge-light">Por defecto</span>
                    <?php endif; ?>
                </span>
            </div>

            <!-- Middle Column: User Info -->
            <div class="ms-profile-info-col">
                <div class="ms-profile-title">
                    <i class="fas fa-user-circle"></i>
                    <?= Html::encode($model->nombres . ' ' . $model->apellidos) ?>
                </div>

                <!-- ID Card Section -->
                <div class="ms-profile-id-section">
                    <div class="ms-profile-id-photo-wrapper">
                        <?php
                        // ============================================
                        // Cédula de Identidad with Default Fallback
                        // ============================================
                        $hasIdImage = !empty($model->imagen_identificacion);
                        $idImageSrc = $hasIdImage ? $model->imagen_identificacion : getDefaultIdImage();
                        $idImageCaption = $hasIdImage
                            ? 'Documento de Identificación - ' . Html::encode($model->nombres . ' ' . $model->apellidos)
                            : 'Documento de Identificación por defecto - ' . Html::encode($model->nombres . ' ' . $model->apellidos);
                        ?>
                        <?= Html::img($idImageSrc, [
                            'class' => 'ms-profile-id-photo clickable-image',
                            'alt' => 'Identificación',
                            'title' => 'Click para ampliar',
                            'data-caption' => $idImageCaption,
                            'data-is-default' => $hasIdImage ? '0' : '1',
                        ]) ?>
                        <span class="ms-click-hint">
                            <i class="fas fa-search-plus"></i> Click
                        </span>
                    </div>

                    <div class="ms-profile-id-info">
                        <span class="ms-profile-id-label">
                            <i class="fas fa-id-card"></i>
                            <?php if ($model->tipo_cedula === 'P'): ?>
                                Número de Pasaporte
                            <?php elseif ($model->tipo_cedula === 'Menor Sin Cédula'): ?>
                                Cédula del Tutor
                            <?php elseif ($model->tipo_cedula === 'Afiliado Otras Clínicas'): ?>
                                Cédula (Otra Clínica)
                            <?php else: ?>
                                Cédula de Identidad
                            <?php endif; ?>
                        </span>

                        <span class="ms-profile-id-number">
                            <?php
                            if ($model->tipo_cedula === 'Menor Sin Cédula') {
                                echo 'Menor Sin Cédula';
                                if ($model->consecutivo_menor) {
                                    echo ' - ' . Html::encode($model->consecutivo_menor);
                                }
                            } else {
                                echo Html::encode(($model->tipo_cedula ? $model->tipo_cedula . '-' : '') . ($model->cedula ?? 'N/A'));
                                // Add a badge for Passport
                                if ($model->tipo_cedula === 'P'): ?>
                                    <span class="badge badge-info ml-2" style="font-size: 0.7rem; background-color: #17a2b8;">
                                        <i class="fas fa-passport"></i> Pasaporte
                                    </span>
                            <?php endif;
                            }
                            ?>
                        </span>
                    </div>
                </div>

                <!-- Asesor Info -->
                <?php if ($asesor): ?>
                    <div class="ms-profile-asesor-info">
                        <i class="fas fa-user-tie"></i>
                        Asesor: <?= Html::encode($asesor->userDatos->nombres . " " . $asesor->userDatos->apellidos) ?>
                        <span style="opacity:0.5; margin: 0 0.3rem;">•</span>
                        <i class="fas fa-building"></i>
                        <?= Html::encode($asesor->userDatos->user->username) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Column: Status & Actions -->
            <div class="ms-profile-actions-col">
                <span class="ms-profile-status">
                    <i class="fas fa-circle"></i>
                    <?= Html::encode($model->estatus ?? 'Activo') ?>
                </span>

                <?php if ($permisos): ?>
                    <div class="ms-profile-actions">
                        <?= Html::a(
                            '<i class="fas fa-file-pdf"></i> Contrato',
                            ['user-datos/generar-contratov', 'id' => $model->id],
                            ['class' => 'ms-btn ms-btn-pdf', 'target' => '_blank', 'data-pjax' => '0']
                        ) ?>
                        <?= Html::a(
                            '<i class="fas fa-edit"></i> Actualizar',
                            Url::to(array_merge(['update', 'id' => $model->id], ($clinica && $clinica->id !== null ? ['clinica_id' => $clinica->id] : []))),
                            ['class' => 'ms-btn ms-btn-edit']
                        ) ?>
                        <?= Html::a(
                            '<i class="fas fa-credit-card"></i> Registrar Pago',
                            ['contratos/index', 'user_id' => $model->id],
                            ['class' => 'ms-btn ms-btn-payment', 'data-pjax' => '0']
                        ) ?>
                        <?php if (!empty($clinica_id_from_url)) : ?>
                            <?= Html::a(
                                '<i class="fas fa-arrow-left"></i> Volver',
                                ['index-clinicas', 'clinica_id' => $clinica->id],
                                ['class' => 'ms-btn ms-btn-back']
                            ) ?>
                        <?php else: ?>
                            <?= Html::a(
                                '<i class="fas fa-arrow-left"></i> Volver',
                                ['index'],
                                ['class' => 'ms-btn ms-btn-back']
                            ) ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Balance Card -->
<div class="ms-balance-card">
    <div class="row align-items-center">
        <div class="col-md-4">
            <div class="ms-balance-label">
                <i class="fas fa-piggy-bank mr-2"></i> Saldo Disponible
            </div>
            <div class="ms-balance-amount">
                <?= Yii::$app->formatter->asCurrency($saldoDisponible, 'USD') ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="ms-balance-label">
                <i class="fas fa-chart-pie mr-2"></i> Consumido
            </div>
            <div class="ms-balance-amount" style="font-size: 1.2rem !important; color: <?= $porcentajeConsumido > 80 ? '#ff6b6b' : ($porcentajeConsumido > 50 ? '#ffd93d' : '#6bcb77') ?>;">
                <?= Yii::$app->formatter->asCurrency($consumoActual, 'USD') ?>
                <span style="font-size: 0.8rem; color: #ffffff; opacity: 0.9;">/ <?= Yii::$app->formatter->asCurrency($coberturaPlan, 'USD') ?></span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="ms-progress-container">
                <div class="ms-progress">
                    <div class="ms-progress-bar" style="width: <?= $porcentajeConsumido ?>%; background-color: <?= $porcentajeConsumido > 80 ? '#ff6b6b' : ($porcentajeConsumido > 50 ? '#ffd93d' : '#6bcb77') ?>;"></div>
                </div>
                <div class="ms-progress-label" style="color: #ffffff;">
                    <span><?= round($porcentajeConsumido) ?>% consumido</span>
                    <span><?= round(100 - $porcentajeConsumido) ?>% disponible</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Personal Information -->
<div class="ms-card">
    <div class="ms-card-header">
        <i class="fas fa-address-card" style="color: var(--ms-blue);"></i>
        <h5>Datos Personales</h5>
    </div>
    <div class="ms-card-body">
        <div class="ms-info-grid">
            <div class="ms-info-item">
                <span class="ms-info-label"><i class="fas fa-user mr-2"></i>Nombres:</span>
                <span class="ms-info-value"><?= Html::encode($model->nombres ?? 'N/A') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label"><i class="fas fa-user mr-2"></i>Apellidos:</span>
                <span class="ms-info-value"><?= Html::encode($model->apellidos ?? 'N/A') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label"><i class="fas fa-id-card mr-2"></i>
                    <?php if ($model->tipo_cedula === 'P'): ?>
                        Pasaporte:
                    <?php elseif ($model->tipo_cedula === 'Menor Sin Cédula'): ?>
                        Cédula del Tutor:
                    <?php elseif ($model->tipo_cedula === 'Afiliado Otras Clínicas'): ?>
                        Cédula (Otra Clínica):
                    <?php else: ?>
                        Cédula:
                    <?php endif; ?>
                </span>
                <span class="ms-info-value">
                    <?php
                    if ($model->tipo_cedula === 'Menor Sin Cédula') {
                        echo 'Menor Sin Cédula';
                        if ($model->consecutivo_menor) {
                            echo ' - ' . Html::encode($model->consecutivo_menor);
                        }
                    } else {
                        echo Html::encode(($model->tipo_cedula ? $model->tipo_cedula . '-' : '') . ($model->cedula ?? 'N/A'));
                    }
                    ?>
                </span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label"><i class="fas fa-calendar-alt mr-2"></i>Fecha Nacimiento:</span>
                <span class="ms-info-value">
                    <?php
                    if (!empty($model->fechanac)) {
                        try {
                            echo Html::encode(Yii::$app->formatter->asDate($model->fechanac, 'dd/MM/yyyy'));
                        } catch (\Exception $e) {
                            echo Html::encode($model->fechanac);
                        }
                    } else {
                        echo 'N/A';
                    }
                    ?>
                </span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label"><i class="fas fa-venus-mars mr-2"></i>Sexo:</span>
                <span class="ms-info-value"><?= Html::encode($model->sexo ?? 'N/A') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label"><i class="fas fa-envelope mr-2"></i>Email:</span>
                <span class="ms-info-value">
                    <?= !empty($model->email) ? Html::a(Html::encode($model->email), 'mailto:' . Html::encode($model->email)) : 'N/A' ?>
                </span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label"><i class="fas fa-phone mr-2"></i>Teléfono:</span>
                <span class="ms-info-value"><?= Html::encode($model->telefono ?? 'N/A') ?></span>
            </div>
        </div>
    </div>
</div>

<?php if ($model->tiene_contratante_diferente): ?>
    <!-- Contracting Party Information -->
    <div class="ms-card">
        <div class="ms-card-header">
            <i class="fas fa-user-tie" style="color: var(--ms-purple);"></i>
            <h5>Información del Contratante</h5>
        </div>
        <div class="ms-card-body">
            <div class="ms-info-grid">
                <div class="ms-info-item">
                    <span class="ms-info-label">Nombres:</span>
                    <span class="ms-info-value"><?= Html::encode($model->nombre_contratante ?? 'N/A') ?></span>
                </div>
                <div class="ms-info-item">
                    <span class="ms-info-label">Apellidos:</span>
                    <span class="ms-info-value"><?= Html::encode($model->apellido_contratante ?? 'N/A') ?></span>
                </div>
                <div class="ms-info-item">
                    <span class="ms-info-label">Cédula:</span>
                    <span class="ms-info-value"><?= Html::encode(($model->tipo_cedula_contratante ? $model->tipo_cedula_contratante . '-' : '') . ($model->cedula_contratante ?? 'N/A')) ?></span>
                </div>
                <div class="ms-info-item">
                    <span class="ms-info-label">Fecha Nacimiento:</span>
                    <span class="ms-info-value">
                        <?php
                        if (!empty($model->fecha_nacimiento_contratante)) {
                            try {
                                echo Html::encode(Yii::$app->formatter->asDate($model->fecha_nacimiento_contratante, 'dd/MM/yyyy'));
                            } catch (\Exception $e) {
                                echo Html::encode($model->fecha_nacimiento_contratante);
                            }
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </span>
                </div>
                <div class="ms-info-item">
                    <span class="ms-info-label">Email:</span>
                    <span class="ms-info-value">
                        <?= !empty($model->email_contratante) ? Html::a(Html::encode($model->email_contratante), 'mailto:' . Html::encode($model->email_contratante)) : 'N/A' ?>
                    </span>
                </div>
                <div class="ms-info-item">
                    <span class="ms-info-label">Sexo:</span>
                    <span class="ms-info-value"><?= Html::encode($model->sexo_contratante ?? 'N/A') ?></span>
                </div>
                <div class="ms-info-item">
                    <span class="ms-info-label">Nacionalidad:</span>
                    <span class="ms-info-value"><?= Html::encode($model->nacionalidad_contratante ?? 'N/A') ?></span>
                </div>
                <div class="ms-info-item">
                    <span class="ms-info-label">Estado Civil:</span>
                    <span class="ms-info-value"><?= Html::encode($model->estado_civil_contratante ?? 'N/A') ?></span>
                </div>
                <div class="ms-info-item">
                    <span class="ms-info-label">Ocupación:</span>
                    <span class="ms-info-value"><?= Html::encode($model->ocupacion_contratante ?? 'N/A') ?></span>
                </div>
                <div class="ms-info-item">
                    <span class="ms-info-label">Teléfono Celular:</span>
                    <span class="ms-info-value"><?= Html::encode($model->telefono_celular_contratante ?? 'N/A') ?></span>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Plan Details -->
<div class="ms-card">
    <div class="ms-card-header">
        <i class="fas fa-hand-holding-usd" style="color: var(--ms-purple);"></i>
        <h5>Detalles del Plan</h5>
    </div>
    <div class="ms-card-body">
        <div class="ms-info-grid">
            <div class="ms-info-item">
                <span class="ms-info-label">Plan:</span>
                <span class="ms-info-value"><?= Html::encode($model->plan->nombre ?? 'N/A') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label">Cuota Mensual:</span>
                <span class="ms-info-value"><?= Yii::$app->formatter->asCurrency($precioPlan, 'USD') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label">Cobertura:</span>
                <span class="ms-info-value"><?= Yii::$app->formatter->asCurrency($coberturaPlan, 'USD') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label">Saldo Disponible:</span>
                <span class="ms-info-value text-success font-weight-bold"><?= Yii::$app->formatter->asCurrency($saldoDisponible, 'USD') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label">Consumido:</span>
                <span class="ms-info-value text-danger font-weight-bold"><?= Yii::$app->formatter->asCurrency($consumoActual, 'USD') ?></span>
            </div>
        </div>

        <?php if (!empty($historialSiniestros)): ?>
            <hr>
            <h6 class="font-weight-bold text-center" style="font-size: 1.1rem !important;">Últimos Siniestros Registrados</h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th style="font-size: 0.82rem !important; color: white !important;">Fecha</th>
                            <th style="font-size: 0.82rem !important; color: white !important;">Descripción</th>
                            <th class="text-right" style="font-size: 0.82rem !important; color: white !important;">Costo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($historialSiniestros, 0, 3) as $siniestro): ?>
                            <tr>
                                <td style="font-size: 0.82rem !important;"><?= Yii::$app->formatter->asDate($siniestro->fecha) ?></td>
                                <td style="font-size: 0.82rem !important;"><?= Html::encode(mb_substr($siniestro->descripcion, 0, 30) . (mb_strlen($siniestro->descripcion) > 30 ? '...' : '')) ?></td>
                                <td class="text-right" style="font-size: 0.82rem !important;"><?= Yii::$app->formatter->asCurrency($siniestro->costo_total, 'USD') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (count($historialSiniestros) > 3): ?>
                    <p class="text-center text-muted small" style="font-size: 0.7rem !important;"><?= count($historialSiniestros) - 3 ?> siniestros más...</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Location -->
<div class="ms-card">
    <div class="ms-card-header">
        <i class="fas fa-map-marker-alt" style="color: #6610f2;"></i>
        <h5>Ubicación</h5>
    </div>
    <div class="ms-card-body">
        <div class="ms-info-grid">
            <div class="ms-info-item">
                <span class="ms-info-label">Estado:</span>
                <span class="ms-info-value"><?= Html::encode($estado ?? 'N/A') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label">Ciudad:</span>
                <span class="ms-info-value"><?= Html::encode($ciudad ?? 'N/A') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label">Municipio:</span>
                <span class="ms-info-value"><?= Html::encode($municipio ?? 'N/A') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label">Parroquia:</span>
                <span class="ms-info-value"><?= Html::encode($parroquia ?? 'N/A') ?></span>
            </div>
            <div class="ms-info-item" style="grid-column: 1 / -1;">
                <span class="ms-info-label">Dirección:</span>
                <span class="ms-info-value"><?= nl2br(Html::encode($model->direccion ?? 'N/A')) ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Additional Information -->
<div class="ms-card">
    <div class="ms-card-header">
        <i class="fas fa-info-circle" style="color: var(--ms-gray-600);"></i>
        <h5>Información Adicional</h5>
    </div>
    <div class="ms-card-body">
        <div class="ms-info-grid">
            <div class="ms-info-item">
                <span class="ms-info-label">Clínica:</span>
                <span class="ms-info-value"><?= Html::encode($model->clinica ? $model->clinica->nombre : 'No asignada') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label">Agencia:</span>
                <span class="ms-info-value"><?= Html::encode($model->asesor ? $model->asesor->agente->nom : 'Sin asignar') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label">Asesor:</span>
                <span class="ms-info-value"><?= Html::encode($model->asesor ? $model->asesor->userDatos->nombres . " " . $model->asesor->userDatos->apellidos : 'Sin asignar') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label">Tipo de Sangre:</span>
                <span class="ms-info-value"><?= Html::encode($model->tipo_sangre ?? 'N/A') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label">Plan:</span>
                <span class="ms-info-value"><?= Html::encode($model->plan ? $model->plan->nombre : 'No asignado') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label">Estatus:</span>
                <span class="ms-info-value">
                    <?php
                    $estatusText = $model->estatus ?? 'N/A';
                    $estatusClass = $estatusText === 'Activo' || $estatusText === 'Registrado' ? 'ms-status-active' : 'ms-status-inactive';
                    ?>
                    <span class="ms-status-badge <?= $estatusClass ?>"><?= Html::encode($estatusText) ?></span>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Sociodemographic Information -->
<div class="ms-card">
    <div class="ms-card-header">
        <i class="fas fa-file-alt" style="color: var(--ms-green);"></i>
        <h5>Información Sociodemográfica y Económica</h5>
    </div>
    <div class="ms-card-body">
        <div class="ms-info-grid">
            <div class="ms-info-item">
                <span class="ms-info-label">Nacionalidad:</span>
                <span class="ms-info-value"><?= Html::encode($model->nacionalidad ?? 'N/A') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label">Estado Civil:</span>
                <span class="ms-info-value"><?= Html::encode($model->estado_civil ?? 'N/A') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label">Lugar Nacimiento:</span>
                <span class="ms-info-value"><?= Html::encode($model->lugar_nacimiento ?? 'N/A') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label">Profesión:</span>
                <span class="ms-info-value"><?= Html::encode($model->profesion ?? 'N/A') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label">Ocupación:</span>
                <span class="ms-info-value"><?= Html::encode($model->ocupacion ?? 'N/A') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label">Actividad Económica:</span>
                <span class="ms-info-value"><?= Html::encode($model->actividad_economica ?? 'N/A') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label">Ramo Comercial:</span>
                <span class="ms-info-value"><?= Html::encode($model->ramo_comercial ?? 'N/A') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label">Descripción Actividad:</span>
                <span class="ms-info-value"><?= Html::encode($model->descripcion_actividad ?? 'N/A') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label">Ingreso Anual:</span>
                <span class="ms-info-value"><?= Html::encode($model->ingreso_anual ?? 'N/A') ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Contact Information -->
<?php if (!empty($model->telefono_residencia) || !empty($model->telefono_oficina) || !empty($model->direccion_residencia) || !empty($model->direccion_oficina)): ?>
    <div class="ms-card">
        <div class="ms-card-header">
            <i class="fas fa-building" style="color: #fd7e14;"></i>
            <h5>Información de Contacto y Domicilio Adicional</h5>
        </div>
        <div class="ms-card-body">
            <div class="ms-info-grid">
                <div class="ms-info-item" style="grid-column: 1 / -1;">
                    <span class="ms-info-label">Dirección de Cobro:</span>
                    <span class="ms-info-value"><?= nl2br(Html::encode($model->direccion_cobro ?? 'N/A')) ?></span>
                </div>
                <div class="ms-info-item">
                    <span class="ms-info-label">Teléfono Residencia:</span>
                    <span class="ms-info-value"><?= Html::encode($model->telefono_residencia ?? 'N/A') ?></span>
                </div>
                <div class="ms-info-item">
                    <span class="ms-info-label">Teléfono Celular:</span>
                    <span class="ms-info-value"><?= Html::encode($model->telefono_celular ?? 'N/A') ?></span>
                </div>
                <div class="ms-info-item" style="grid-column: 1 / -1;">
                    <span class="ms-info-label">Dirección Oficina:</span>
                    <span class="ms-info-value"><?= nl2br(Html::encode($model->direccion_oficina ?? 'N/A')) ?></span>
                </div>
                <div class="ms-info-item">
                    <span class="ms-info-label">Teléfono Oficina:</span>
                    <span class="ms-info-value"><?= Html::encode($model->telefono_oficina ?? 'N/A') ?></span>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Banking Information -->
<div class="ms-card">
    <div class="ms-card-header">
        <i class="fas fa-credit-card" style="color: var(--ms-red);"></i>
        <h5>Información Bancaria</h5>
    </div>
    <div class="ms-card-body">
        <div class="ms-info-grid">
            <div class="ms-info-item">
                <span class="ms-info-label">Titular:</span>
                <span class="ms-info-value"><?= Html::encode($model->nombre_titular ?? 'N/A') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label">Cédula Titular:</span>
                <span class="ms-info-value"><?= Html::encode($model->cedula_titular ?? 'N/A') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label">Número Cuenta:</span>
                <span class="ms-info-value"><?= Html::encode($model->numero_cuenta ?? 'N/A') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label">Banco:</span>
                <span class="ms-info-value"><?= Html::encode($model->banco ? $model->banco->nombre : 'N/A') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label">Tipo Cuenta:</span>
                <span class="ms-info-value"><?= Html::encode($model->tipo_cuenta ?? 'N/A') ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Policy Details -->
<div class="ms-card">
    <div class="ms-card-header">
        <i class="fas fa-user-friends" style="color: var(--ms-gray-600);"></i>
        <h5>Detalles de la Póliza</h5>
    </div>
    <div class="ms-card-body">
        <div class="ms-info-grid">
            <div class="ms-info-item">
                <span class="ms-info-label">Cubre Maternidad:</span>
                <span class="ms-info-value"><?= formatBooleanIcon($model->cobertura_maternidad) ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label">Deducible Maternidad:</span>
                <span class="ms-info-value"><?= Yii::$app->formatter->asCurrency($model->deducible_maternidad ?? 0, 'USD') ?></span>
            </div>
            <div class="ms-info-item">
                <span class="ms-info-label">Límite Maternidad:</span>
                <span class="ms-info-value"><?= Yii::$app->formatter->asCurrency($model->limite_cobertura_maternidad ?? 0, 'USD') ?></span>
            </div>
            <div class="ms-info-item" style="grid-column: 1 / -1;">
                <span class="ms-info-label">Grupo Familiar:</span>
                <span class="ms-info-value">
                    <pre class="p-2 bg-light rounded" style="white-space: pre-wrap; font-family: inherit; margin: 0; font-size: 0.82rem !important;"><?= Html::encode($model->grupo_familiar ?? 'N/A') ?></pre>
                </span>
            </div>
        </div>
    </div>
</div>