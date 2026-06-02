<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use app\components\UserHelper;
use yii\bootstrap4\Alert;
use yii\web\View;

/**
 * @var yii\web\View $this
 * @var app\models\SisSiniestroSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\UserDatos $afiliado
 * @var int $user_id
 * @var string $modo 'siniestro' o 'cita'
 */

// ----------------------------------------------------------------------
// 1. LÓGICA DE MODO Y BOTONES
// ----------------------------------------------------------------------
$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol == 'DIRECTOR-COMERCIALIZACION' || $rol == 'Asesor' || $rol == 'Agente' || $rol == "ADMISIÓN" || $rol == "CONTROL DE CITAS" || $rol == "Administrador-clinica" || $rol == "COORDINADOR-CLINICA");

// Define si puede actualizar
$canUpdate = $permisos;

// Definir variables basadas en el modo
$esCita = ($modo === 'cita') ? 1 : 0;
$tituloModo = ($modo === 'cita') ? 'Citas' : 'Atenciones';
$textoBoton = ($modo === 'cita') ? 'Crear Nueva Cita' : 'Crear Nueva Atención';

$this->params['breadcrumbs'][] = ['label' => 'AFILIADOS', 'url' => ['/user-datos/index-clinicas', 'clinica_id' => $afiliado->clinica_id]];
$this->title = $tituloModo . ' para ' . Html::encode($afiliado->nombres . " " . $afiliado->apellidos . " " . $afiliado->tipo_cedula . "-" . $afiliado->cedula);

// ----------------------------------------------------------------------
// 2. DETECTAR SI HAY MENSAJE DE CONTRATO SUSPENDIDO
// ----------------------------------------------------------------------
$contratoSuspendido = false;
$allFlashMessages = Yii::$app->session->getAllFlashes();
Yii::$app->session->close();
Yii::$app->session->open();
$flashMessagesToDisplay = $allFlashMessages;

// Check for suspended contract message
foreach ($allFlashMessages as $type => $messages) {
    foreach ((array)$messages as $message) {
        if (stripos($message, 'SUSPENDIDO') !== false) {
            $contratoSuspendido = true;
        }
    }
}

// Definir clase y color del botón "Volver" basado en el estado del contrato
$volverBtnClass = $contratoSuspendido ? 'btn-warning' : 'btn-outline-secondary';
$volverBtnIcon = $contratoSuspendido ? 'fas fa-exclamation-triangle' : 'fas fa-undo';
$volverBtnTitle = $contratoSuspendido ? 'Volver (Contrato Suspendido)' : 'Volver a la lista de afiliados';
// ----------------------------------------------------------------------

// Register CSS for professional styling with Bootstrap 4 compatibility
$this->registerCss("
    /* ============================================
       HEADER SECTION STYLING
       ============================================ */
    .ms-panel-header {
        padding: 20px 25px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 3px solid #2a5298;
    }
    
    .ms-panel-header h1 {
        color: #1e3c72;
        margin: 0;
        font-size: 1.8rem;
        font-weight: 700;
        letter-spacing: -0.3px;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.05);
    }
    
    /* ============================================
       BUTTON STYLING
       ============================================ */
    .btn-create {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border: none;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
    }
    
    .btn-create:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(40, 167, 69, 0.4);
        color: white;
        background: linear-gradient(135deg, #34ce57 0%, #2ee0a5 100%);
    }
    
    .btn-back {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        color: white;
        border: none;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
    }
    
    .btn-back:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(108, 117, 125, 0.4);
        color: white;
        background: linear-gradient(135deg, #7e888f 0%, #6b757d 100%);
    }
    
    .btn-back-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        color: #212529;
        border: none;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
    }
    
    .btn-back-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(255, 193, 7, 0.4);
        color: #212529;
    }
    
    /* ============================================
       CONSECUTIVE COUNTER BADGE - BIGGER
       ============================================ */
    .consecutive-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(248,249,250,0.9) 100%);
        color: #1e3c72;
        font-weight: 800;
        font-size: 2rem;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border: 2px solid #e9ecef;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .consecutive-badge::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(30, 60, 114, 0.05) 0%, rgba(42, 82, 152, 0.02) 100%);
        border-radius: 16px;
        z-index: 0;
    }
    
    .consecutive-badge span {
        position: relative;
        z-index: 1;
    }
    
    .consecutive-badge:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(30, 60, 114, 0.2);
        border-color: #2a5298;
        background: linear-gradient(135deg, #ffffff 0%, #ffffff 100%);
    }
    
    /* ============================================
       SERVICES COLUMN STYLING - BIGGER
       ============================================ */
    .services-container {
        scrollbar-width: thin;
        scrollbar-color: #2a5298 #e9ecef;
        max-height: 500px;
        overflow-y: auto;
        padding-right: 12px;
    }
    
    .services-container::-webkit-scrollbar {
        width: 8px;
    }
    
    .services-container::-webkit-scrollbar-track {
        background: #e9ecef;
        border-radius: 4px;
    }
    
    .services-container::-webkit-scrollbar-thumb {
        background: #2a5298;
        border-radius: 4px;
    }
    
    .services-container::-webkit-scrollbar-thumb:hover {
        background: #1e3c72;
    }
    
    .service-item {
        transition: all 0.2s ease;
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 20px;
        margin-bottom: 20px;
    }
    
    .service-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    
    .service-item:hover {
        background-color: #f8f9fa;
        transform: translateX(4px);
        padding-left: 12px;
    }
    
    .service-name {
        font-weight: 700;
        color: #1e3c72;
        font-size: 1.4rem;
        line-height: 1.4;
    }
    
    .service-details {
        font-size: 1.1rem;
        color: #6c757d;
        margin-top: 8px;
    }
    
    .service-description {
        font-size: 1rem;
        color: #6c757d;
        margin-top: 8px;
        font-style: italic;
    }
    
    /* ============================================
       STATUS BADGES STYLING - BIGGER
       ============================================ */
    .status-badge {
        display: inline-block;
        padding: 12px 28px;
        border-radius: 30px;
        font-size: 1.2rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        transition: all 0.2s ease;
    }
    
    .status-badge.cita {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        color: #212529;
    }
    
    .status-badge.atencion {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
    }
    
    .status-badge.atendido {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }
    
    .status-badge.no-atendido {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        color: white;
    }
    
    /* ============================================
       GRID VIEW HEADER STYLING - BIGGER
       ============================================ */
    .grid-view-container table {
        margin-bottom: 0;
        border-radius: 12px;
        overflow: hidden;
        width: 100%;
    }
    
    .grid-view-container th {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white !important;
        font-weight: 700;
        font-size: 1.3rem;
        padding: 20px 16px !important;
        vertical-align: middle;
        border: none;
    }
    
    .grid-view-container td {
        vertical-align: middle;
        padding: 18px 12px !important;
        font-size: 1.2rem;
        border-bottom: 2px solid #e9ecef;
        background: transparent !important;
    }
    
    /* ============================================
       MINIMAL GRID ZEBRA STRIPING
       ============================================ */
    .grid-view-container table.kv-grid-table.custom-zebra > tbody > tr > td {
        background: transparent !important;
        background-color: transparent !important;
    }
    .grid-view-container table.kv-grid-table.custom-zebra > tbody > tr:nth-child(odd) > td {
        background-color: #ffffff !important;
        background-image: none !important;
    }
    .grid-view-container table.kv-grid-table.custom-zebra > tbody > tr:nth-child(even) > td {
        background-color: #f0f4f8 !important;
        background-image: none !important;
    }
    .grid-view-container table.kv-grid-table.custom-zebra > tbody > tr:hover > td {
        background-color: #e3f2fd !important;
        background-image: none !important;
    }
    .grid-view-container .consecutive-badge,
    .grid-view-container .consecutive-badge span,
    .grid-view-container .consecutive-badge * {
        background: transparent !important;
        background-color: transparent !important;
        box-shadow: none !important;
    }
    
    /* ============================================
       ACTION BUTTONS STYLING - SIMPLIFIED
       ============================================ */
    .action-buttons-cell {
        text-align: center !important;
        vertical-align: middle !important;
    }
    
    .action-buttons-container {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin: 0;
        padding: 0;
    }
    
    .btn-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        transition: all 0.25s ease;
        text-decoration: none;
        cursor: pointer;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .btn-action i {
        font-size: 18px;
        display: inline-block;
    }
    
    .btn-action:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.2);
    }
    
    .btn-action:active {
        transform: translateY(0);
    }
    
    /* View Button - Blue */
    .btn-action.view {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
    }
    
    .btn-action.view:hover {
        background: linear-gradient(135deg, #0069d9 0%, #004999 100%);
        box-shadow: 0 6px 12px rgba(0, 123, 255, 0.3);
    }
    
    /* Edit Button - Amber */
    .btn-action.edit {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        color: #212529;
    }
    
    .btn-action.edit:hover {
        background: linear-gradient(135deg, #e0a800 0%, #c69500 100%);
        box-shadow: 0 6px 12px rgba(255, 193, 7, 0.3);
    }
    
    /* Print Button - Orange */
    .btn-action.print {
        background: linear-gradient(135deg, #fd7e14 0%, #dc6a0a 100%);
        color: white;
    }
    
    .btn-action.print:hover {
        background: linear-gradient(135deg, #dc6a0a 0%, #b85a00 100%);
        box-shadow: 0 6px 12px rgba(253, 126, 20, 0.3);
    }
    
    /* Cancel Button - Red */
    .btn-action.cancel {
        background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);
        color: white;
    }
    
    .btn-action.cancel:hover {
        background: linear-gradient(135deg, #c82333 0%, #a71d2a 100%);
        box-shadow: 0 6px 12px rgba(220, 53, 69, 0.3);
    }
    
    /* Attend Button - Green */
    .btn-action.attend {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        color: white;
    }
    
    .btn-action.attend:hover {
        background: linear-gradient(135deg, #218838 0%, #19692c 100%);
        box-shadow: 0 6px 12px rgba(40, 167, 69, 0.3);
    }
    
    /* ============================================
       PANEL STYLING
       ============================================ */
    .ms-panel {
        border-radius: 16px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        overflow: hidden;
        background: white;
    }
    
    .ms-panel-body {
        padding: 25px;
    }
    
    /* ============================================
       PAGINATION STYLING - BIGGER
       ============================================ */
    .pagination {
        margin-top: 30px;
        justify-content: center;
    }
    
    .pagination > li > a,
    .pagination > li > span {
        border-radius: 12px;
        margin: 0 6px;
        color: #1e3c72;
        border: 1px solid #dee2e6;
        transition: all 0.2s ease;
        font-size: 1.2rem;
        padding: 14px 22px;
    }
    
    .pagination > li.active > a,
    .pagination > li.active > span {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        border-color: #2a5298;
        color: white;
    }
    
    .pagination > li > a:hover {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        transform: translateY(-2px);
    }
    
    /* ============================================
       ADMISSION ANALYST COLUMN STYLING - BIGGER
       ============================================ */
    .admission-analyst-cell {
        font-size: 1.2rem;
        font-weight: 500;
        color: #495057;
    }
    
    .admission-analyst-cell i {
        color: #6c757d;
        margin-right: 8px;
        font-size: 1.2rem;
    }
    
    /* ============================================
       COSTO TOTAL LARGER TEXT - BIGGER
       ============================================ */
    .costo-total-value {
        font-size: 1.6rem;
        font-weight: 800;
        color: #28a745;
    }
    
    /* ============================================
       DOCTOR COLUMN - BIGGER
       ============================================ */
    .doctor-cell {
        font-size: 1.2rem;
        font-weight: 500;
    }
    
    /* ============================================
       CLINICA COLUMN - BIGGER
       ============================================ */
    .clinica-cell {
        font-size: 1.2rem;
        font-weight: 500;
    }
    
    /* ============================================
       FLASH MESSAGE STYLES - BOOTSTRAP 4 COMPATIBLE
       ============================================ */
    .contract-alert-header {
        background: linear-gradient(135deg, #fff5f5 0%, #ffeaea 100%);
        padding: 18px 20px;
        border-radius: 10px;
        border-left: 5px solid #dc3545;
        margin-bottom: 20px;
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.1);
    }

    .alert-elevated {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .alert-elevated:hover {
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }

    .contract-suspended-box {
        transition: all 0.3s ease;
    }

    .contract-suspended-box:hover {
        box-shadow: 0 6px 20px rgba(211, 47, 47, 0.2);
        transform: translateY(-1px);
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 4px 12px rgba(211, 47, 47, 0.3);
        }
        50% {
            box-shadow: 0 4px 18px rgba(211, 47, 47, 0.5);
        }
        100% {
            box-shadow: 0 4px 12px rgba(211, 47, 47, 0.3);
        }
    }

    .attention-alert {
        animation: attention-pulse 1.2s infinite alternate ease-in-out;
        color: #d32f2f !important;
        font-weight: 800 !important;
        font-size: 1.4em !important;
        display: inline-block;
        padding: 8px 16px;
        margin: 0 5px;
        border-radius: 6px;
        background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
        box-shadow: 0 4px 15px rgba(211, 47, 47, 0.3);
        border: 3px solid #ff8a80;
        position: relative;
        overflow: hidden;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .contract-alert {
        animation: contract-warning 1s infinite alternate cubic-bezier(0.4, 0, 0.2, 1);
        color: #ffffff !important;
        font-weight: 700 !important;
        font-size: 1.3em !important;
        display: inline-block;
        padding: 8px 18px;
        margin: 0 5px;
        border-radius: 6px;
        background: linear-gradient(135deg, #ef5350 0%, #d32f2f 100%);
        box-shadow: 0 6px 20px rgba(211, 47, 47, 0.5), inset 0 2px 0 rgba(255, 255, 255, 0.4);
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        position: relative;
        overflow: hidden;
        text-transform: uppercase;
        letter-spacing: 1.2px;
    }

    @keyframes attention-pulse {
        0% {
            transform: scale(1) translateY(0);
            box-shadow: 0 4px 15px rgba(211, 47, 47, 0.3);
            border-color: #ff8a80;
        }
        100% {
            transform: scale(1.05) translateY(-3px);
            box-shadow: 0 8px 25px rgba(211, 47, 47, 0.5);
            border-color: #ff5252;
        }
    }

    @keyframes contract-warning {
        0% {
            transform: scale(1) translateY(0);
            box-shadow: 0 6px 20px rgba(211, 47, 47, 0.5), inset 0 2px 0 rgba(255, 255, 255, 0.4);
            background: linear-gradient(135deg, #ef5350 0%, #d32f2f 100%);
        }
        100% {
            transform: scale(1.05) translateY(-2px);
            box-shadow: 0 10px 30px rgba(211, 47, 47, 0.7), inset 0 2px 0 rgba(255, 255, 255, 0.5);
            background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
        }
    }
    
    /* Additional larger text for all cells */
    .grid-view-container td .text-muted {
        font-size: 1.1rem !important;
    }
    
    .grid-view-container td .badge {
        font-size: 1rem !important;
        padding: 8px 16px !important;
    }

    /* ============================================
       BOOTSTRAP 4 COMPATIBLE FLASH MESSAGES
       ============================================ */
    .alert {
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        padding: 16px 20px;
        position: relative;
    }

    .alert-success {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        border-left: 5px solid #28a745;
        color: #155724;
    }

    .alert-danger {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        border-left: 5px solid #dc3545;
        color: #721c24;
    }

    .alert-warning {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border-left: 5px solid #ffc107;
        color: #856404;
    }

    .alert-info {
        background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
        border-left: 5px solid #17a2b8;
        color: #0c5460;
    }

    .alert .close {
        opacity: 0.6;
        transition: opacity 0.2s;
        position: absolute;
        top: 15px;
        right: 20px;
    }

    .alert .close:hover {
        opacity: 1;
    }

    /* Auto-hide animation */
    .alert-success, .alert-danger, .alert-warning, .alert-info {
        position: relative;
        animation: slideInRight 0.3s ease-out;
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Progress bar for auto-hide */
    .alert-success::after,
    .alert-danger::after,
    .alert-warning::after,
    .alert-info::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        animation: progressBar 5s linear forwards;
        border-radius: 0 0 12px 12px;
    }

    .alert-success::after {
        background: #28a745;
    }

    .alert-danger::after {
        background: #dc3545;
    }

    .alert-warning::after {
        background: #ffc107;
    }

    .alert-info::after {
        background: #17a2b8;
    }

    @keyframes progressBar {
        from {
            width: 100%;
        }
        to {
            width: 0%;
        }
    }
");
?>
<div class="row" style="margin:3px !important;">
    <!-- PROFESSIONAL FLASH MESSAGES DISPLAY -->
    <?php if (!empty($flashMessagesToDisplay)): ?>
        <div class="col-12">
            <?php foreach ($flashMessagesToDisplay as $type => $messages): ?>
                <?php foreach ((array)$messages as $message): ?>
                    <div class="alert alert-<?= $type ?> alert-dismissible" role="alert" style="margin-bottom: 20px; font-size: 1.3rem; padding: 20px 25px;">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="font-size: 1.8rem; opacity: 0.7;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <div style="font-size: 1.3rem; line-height: 1.5;">
                            <?= $message ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />

    <div class="col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header d-flex justify-content-between align-items-center">
                <div>
                    <?php
                    // Determine icon based on mode
                    $isCitaMode = ($modo === 'cita');
                    $iconClass = $isCitaMode ? 'fa-calendar-alt' : 'fa-heartbeat';
                    $iconColor = $isCitaMode ? '#28a745' : '#dc3545';
                    ?>
                    <h1 style="font-size: 2.2rem; font-weight: 700; letter-spacing: -0.3px; margin: 0; display: flex; align-items: center;">
                        <i class="fas <?= $iconClass ?>" style="color: <?= $iconColor ?>; font-size: 5rem; margin-right: 16px; line-height: 1;"></i>
                        <span style="line-height: 1.4;"><?= $this->title ?></span>
                    </h1>
                </div>
                <div class="d-flex">
                    <?php
                    if ($permisos) {
                        echo Html::a(
                            '<i class="fas fa-plus-circle me-2"></i>' . $textoBoton,
                            ['create', 'user_id' => $user_id, 'es_cita' => $esCita],
                            ['class' => 'btn btn-create btn-lg mr-5', 'style' => 'font-size: 1.2rem; padding: 14px 28px; border-radius: 12px;']
                        );
                    }
                    ?>
                    <?= Html::a(
                        '<i class="' . $volverBtnIcon . ' me-2"></i> Volver',
                        ['/user-datos/index-clinicas', 'clinica_id' => $afiliado->clinica_id],
                        [
                            'class' => 'btn btn-lg ' . ($contratoSuspendido ? 'btn-back-warning' : 'btn-back'),
                            'title' => $volverBtnTitle,
                            'data' => ['pjax' => 0],
                            'style' => 'font-size: 1.2rem; padding: 14px 28px; border-radius: 12px;'
                        ]
                    ) ?>
                </div>
            </div>
            <div class="ms-panel-body">
                <div class="table-responsive">

                    <?= GridView::widget([
                        'id' => 'clinica-grid',
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'layout' => "{items}{pager}",
                        'resizableColumns' => false,
                        'bordered' => false,
                        'responsiveWrap' => false,
                        'persistResize' => false,
                        'striped' => false,
                        'hover' => false,
                        'tableOptions' => [
                            'class' => 'table kv-grid-table custom-zebra',
                        ],
                        'options' => [
                            'class' => 'grid-view-container',
                        ],
                        'columns' => [
                            // Professional consecutive counter instead of ID
                            [
                                'class' => 'yii\grid\DataColumn',
                                'header' => '<i class="fas fa-hashtag me-1" style="font-size: 1rem;"></i> N°',
                                'encodeLabel' => false,
                                'headerOptions' => [
                                    'style' => 'background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white !important; font-weight: 600; text-align: center; width: 60px; border-radius: 12px 0 0 0;'
                                ],
                                'contentOptions' => [
                                    'style' => 'text-align: center; vertical-align: middle; padding: 12px 4px !important;'
                                ],
                                'options' => ['style' => 'width: 60px;'],
                                'value' => function ($model, $key, $index, $column) {
                                    $pagination = $column->grid->dataProvider->getPagination();
                                    if ($pagination) {
                                        $page = $pagination->getPage();
                                        $pageSize = $pagination->getPageSize();
                                        $sequentialNumber = $page * $pageSize + $index + 1;
                                    } else {
                                        $sequentialNumber = $index + 1;
                                    }
                                    return '<div class="consecutive-badge" style="width: 45px; height: 45px; font-size: 1.3rem;">' . $sequentialNumber . '</div>';
                                },
                                'format' => 'raw',
                            ],

                            // Combined Fecha / Hora Column
                            [
                                'attribute' => 'fecha_atencion',
                                'label' => ($modo === 'cita') ? '<i class="fas fa-calendar-alt me-1" style="font-size: 1.3rem;"></i> Fecha/Hora' : '<i class="fas fa-calendar-alt me-1" style="font-size: 1rem;"></i> Fecha / Hora Atención',
                                'encodeLabel' => false,
                                'format' => 'Html',
                                'headerOptions' => ['style' => 'background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white !important; text-align: center;'],
                                'contentOptions' => ['style' => 'text-align: center; padding: 18px 12px;'],
                                'value' => function ($model) {
                                    $date = Yii::$app->formatter->asDate($model->fecha_atencion);
                                    $time = $model->hora_atencion ? substr($model->hora_atencion, 0, 5) : 'N/A';

                                    return '<div style="display: flex; flex-direction: column; align-items: center; gap: 6px;">
                                        <div style="font-size: 1.2rem; font-weight: 600;">' . $date . '</div>
                                        <div style="font-size: 1rem; color: #6c757d;">
                                            <i class="fas fa-clock" style="font-size: 0.9rem;"></i> ' . $time . '
                                        </div>
                                    </div>';
                                },
                            ],
                            // Servicios Médicos
                            [
                                'attribute' => 'baremos',
                                'format' => 'raw',
                                'label' => '<i class="fas fa-notes-medical me-2" style="font-size: 1rem;"></i> Servicios Médicos',
                                'encodeLabel' => false,
                                'contentOptions' => ['style' => 'max-width: 200px; white-space: normal; padding: 18px 12px;'],
                                'headerOptions' => [
                                    'style' => 'background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white !important; font-weight: 600;'
                                ],
                                'filter' => false,
                                'value' => function ($model) {
                                    $baremos = $model->baremos;
                                    if (empty($baremos)) {
                                        return '<span class="text-muted" style="font-size: 1.2rem; font-style: italic;">Sin servicios</span>';
                                    }
                                    $items = [];
                                    foreach ($baremos as $baremo) {
                                        $serviceName = is_array($baremo) ? $baremo['nombre_servicio'] : $baremo->nombre_servicio;
                                        $serviceArea = is_array($baremo) ? ($baremo['area_nombre'] ?? '') : ($baremo->area->nombre ?? '');
                                        $servicePrice = is_array($baremo) ? ($baremo['precio'] ?? 0) : ($baremo->precio ?? 0);
                                        $serviceDesc = is_array($baremo) ? ($baremo['descripcion'] ?? '') : ($baremo->descripcion ?? '');

                                        if (!empty($serviceArea)) {
                                            $areaDisplay = Html::encode($serviceArea);
                                        } else {
                                            $areaDisplay = '<span style="color: #856404;">Sin categoría</span>';
                                        }

                                        $descDisplay = '';
                                        if (!empty($serviceDesc)) {
                                            $descDisplay = '<div class="service-description">' . Html::encode($serviceDesc) . '</div>';
                                        }

                                        $items[] = Html::tag(
                                            'div',
                                            '<div class="d-flex align-items-start gap-2" style="margin-bottom: 15px;">' .
                                                '<div class="flex-shrink-0" style="width: 45px; text-align: center;">' .
                                                '<i class="fas fa-stethoscope" style="color: #2a5298; font-size: 28px;"></i>' .
                                                '</div>' .
                                                '<div class="flex-grow-1">' .
                                                '<div class="service-name" style="font-size: 1.3rem;">' . Html::encode($serviceName) . '</div>' .
                                                '<div class="service-details" style="font-size: 1.1rem;">' .
                                                $areaDisplay .
                                                '<span class="mx-2">•</span>' .
                                                '<strong>$' . number_format($servicePrice, 2) . '</strong>' .
                                                '</div>' .
                                                $descDisplay .
                                                '</div>' .
                                                '</div>',
                                            ['class' => 'service-item']
                                        );
                                    }
                                    return '<div class="services-container">' . implode('', $items) . '</div>';
                                },
                            ],
                            // Doctor Column
                            [
                                'attribute' => 'nombre_doctor',
                                'label' => '<i class="fas fa-user-md me-1" style="font-size: 1rem;"></i> Doctor',
                                'encodeLabel' => false,
                                'format' => 'raw',
                                'headerOptions' => ['style' => 'background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white !important; width:15%; min-width: 120px;'],
                                'contentOptions' => ['style' => 'text-align: center; white-space: normal; word-wrap: break-word; font-size: 1.2rem; padding: 18px 12px;'],
                                'value' => function ($model) {
                                    if (empty($model->nombre_doctor)) {
                                        return '<span class="text-muted" style="font-size: 1.1rem;">No asignado</span>';
                                    }
                                    return Html::encode($model->nombre_doctor);
                                },
                            ],
                            // Analista Column
                            [
                                'attribute' => 'admission_analyst',
                                'label' => ($modo === 'cita') ? '<i class="fas fa-user-check me-1" style="font-size: 1rem;"></i> Analista' : '<i class="fas fa-user-check me-1" style="font-size: 1.3rem;"></i> Analista Admisión',
                                'encodeLabel' => false,
                                'format' => 'raw',
                                'headerOptions' => [
                                    'style' => 'background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white !important; font-weight: 600; text-align: center; width:15%; min-width: 120px;'
                                ],
                                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle; padding: 18px 12px; font-size: 1.2rem;', 'class' => 'admission-analyst-cell'],
                                'value' => function ($model) {
                                    if (empty($model->admission_analyst)) {
                                        return '<span class="text-muted" style="font-size: 1.1rem; font-style: italic;">No asignado</span>';
                                    }
                                    return Html::encode($model->admission_analyst);
                                },
                                'filterInputOptions' => [
                                    'class' => 'form-control',
                                    'placeholder' => 'Buscar analista...',
                                    'style' => 'font-size: 1.1rem; padding: 10px;'
                                ],
                            ],
                            // Costo Total
                            [
                                'attribute' => 'costo_total',
                                'label' => '<i class="fas fa-dollar-sign me-1" style="font-size: 1rem;"></i> Costo Total',
                                'encodeLabel' => false,
                                'format' => ['currency', 'USD'],
                                'headerOptions' => [
                                    'style' => 'background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white !important; font-weight: 600; text-align: right; width: 80px;'
                                ],
                                'contentOptions' => [
                                    'style' => 'text-align: right; font-size: 1.6rem; font-weight: 800; color: #28a745; padding: 18px 4px;'
                                ],
                                'filter' => false
                            ],
                            // Appointment Status - Simplified
                            [
                                'attribute' => 'appointment_status',
                                'label' => '<i class="fas fa-info-circle me-1" style="font-size: 1rem;"></i> Estado',
                                'encodeLabel' => false,
                                'format' => 'raw',
                                'headerOptions' => ['style' => 'background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white !important;'],
                                'contentOptions' => ['style' => 'text-align: center;padding: 18px 12px;'],
                                'value' => function ($model) {
                                    if ($model->es_cita == 1 && $model->appointment_status) {
                                        $badges = [
                                            'scheduled' => '<span class="badge badge-warning" style="font-size: 1rem; padding: 10px 18px;"><i class="fas fa-calendar"></i> Agendada</span>',
                                            'completed' => '<span class="badge badge-success" style="font-size: 1rem; padding: 10px 18px;"><i class="fas fa-check-double"></i> Completada</span>',
                                            'cancelled' => '<span class="badge badge-danger" style="font-size: 1rem; padding: 10px 18px;"><i class="fas fa-ban"></i> Cancelada</span>',
                                        ];
                                        return $badges[$model->appointment_status] ?? '<span class="badge badge-warning">' . $model->appointment_status . '</span>';
                                    }
                                    return '<span class="badge badge-secondary" style="font-size: 1rem; padding: 10px 18px;">Atención</span>';
                                },
                                'filter' => [
                                    'scheduled' => 'Agendada',
                                    'completed' => 'Completada',
                                    'cancelled' => 'Cancelada',
                                ],
                            ],

                            // Action Buttons - Simplified
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => '<i class="fas fa-cog me-1" style="font-size: 1.3rem;"></i> ACCIONES',
                                'template' => '<div class="action-buttons-container">{view}{update}{print}{cancel}{attend}</div>',
                                'options' => ['class' => 'action-buttons-cell'],
                                'headerOptions' => [
                                    'style' => 'background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white !important; font-weight: 600; text-align: center; width: 280px;'
                                ],
                                'contentOptions' => [
                                    'style' => 'text-align: center; vertical-align: middle; padding: 18px 12px;'
                                ],
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fa fa-eye"></i>',
                                            ['view', 'id' => $model->id, 'user_id' => $model->iduser, 'es_cita' => $model->es_cita],
                                            [
                                                'title' => 'Ver detalle',
                                                'class' => 'btn-action view',
                                            ]
                                        );
                                    },
                                    'update' => function ($url, $model, $key) use ($canUpdate) {
                                        if ($canUpdate) {
                                            return Html::a(
                                                '<i class="fas fa-pencil-alt"></i>',
                                                [
                                                    'update',
                                                    'id' => $model->id,
                                                    'user_id' => $model->iduser,
                                                    'es_cita' => (int)$model->es_cita
                                                ],
                                                [
                                                    'title' => 'Editar',
                                                    'class' => 'btn-action edit',
                                                ]
                                            );
                                        }
                                        return '';
                                    },
                                    'print' => function ($url, $model, $key) {
                                        $esCita = $model->es_cita;
                                        $termino = $esCita == 1 ? 'Cita' : 'Atención';
                                        $printUrl = Url::to(['print', 'id' => $model->id]);
                                        return Html::a(
                                            '<i class="fas fa-print"></i>',
                                            '#',
                                            [
                                                'title' => 'Imprimir ' . $termino,
                                                'class' => 'btn-action print',
                                                'onclick' => "window.open('{$printUrl}', '_blank', 'width=900,height=700,scrollbars=yes,resizable=yes,toolbar=yes,menubar=yes'); return false;",
                                            ]
                                        );
                                    },
                                    'cancel' => function ($url, $model) {
                                        if ($model->es_cita == 1 && $model->appointment_status == 'scheduled') {
                                            return Html::a(
                                                '<i class="fas fa-calendar-times"></i>',
                                                ['cancel-appointment', 'id' => $model->id],
                                                [
                                                    'title' => 'Cancelar Cita',
                                                    'class' => 'btn-action cancel',
                                                ]
                                            );
                                        }
                                        return '';
                                    },
                                    'attend' => function ($url, $model) {
                                        if ($model->es_cita == 1 && $model->appointment_status == 'scheduled') {
                                            $attendUrl = Url::to(['attend', 'id' => $model->id]);
                                            return Html::a(
                                                '<i class="fas fa-check-double"></i>',
                                                $attendUrl,
                                                [
                                                    'title' => 'Marcar como Atendida',
                                                    'class' => 'btn-action attend',
                                                    'onclick' => 'return confirm("¿Confirmar que el paciente asistió y recibió el servicio?");',
                                                ]
                                            );
                                        }
                                        return '';
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