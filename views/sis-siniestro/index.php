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
       CONSECUTIVE COUNTER BADGE
       ============================================ */
    .consecutive-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
    background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(248,249,250,0.9) 100%);
        color: #1e3c72;
        font-weight: 700;
        font-size: 0.95rem;
        border-radius: 12px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        border: 1px solid #e9ecef;
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
        border-radius: 12px;
        z-index: 0;
    }
    
    .consecutive-badge span {
        position: relative;
        z-index: 1;
    }
    
    .consecutive-badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(30, 60, 114, 0.15);
        border-color: #2a5298;
        background: linear-gradient(135deg, #ffffff 0%, #ffffff 100%);
    }
    
    /* ============================================
       SERVICES COLUMN STYLING
       ============================================ */
    .services-container {
        scrollbar-width: thin;
        scrollbar-color: #2a5298 #e9ecef;
        max-height: 250px;
        overflow-y: auto;
        padding-right: 8px;
    }
    
    .services-container::-webkit-scrollbar {
        width: 6px;
    }
    
    .services-container::-webkit-scrollbar-track {
        background: #e9ecef;
        border-radius: 3px;
    }
    
    .services-container::-webkit-scrollbar-thumb {
        background: #2a5298;
        border-radius: 3px;
    }
    
    .services-container::-webkit-scrollbar-thumb:hover {
        background: #1e3c72;
    }
    
    .service-item {
        transition: all 0.2s ease;
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 10px;
        margin-bottom: 10px;
    }
    
    .service-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    
    .service-item:hover {
        background-color: #f8f9fa;
        transform: translateX(2px);
        padding-left: 6px;
    }
    
    /* Larger text for service name */
    .service-name {
        font-weight: 700;
        color: #1e3c72;
        font-size: 1rem;
        line-height: 1.4;
    }
    
    /* Larger text for service details */
    .service-details {
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 4px;
    }
    
    .service-description {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 4px;
        font-style: italic;
    }
    
    /* ============================================
       STATUS BADGES STYLING
       ============================================ */
    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.3px;
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
       GRID VIEW HEADER STYLING
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
        font-weight: 600;
        font-size: 0.9rem;
        padding: 14px 12px !important;
        vertical-align: middle;
        border: none;
    }
    
    .grid-view-container td {
        vertical-align: middle;
        padding: 12px 10px !important;
        border-bottom: 1px solid #e9ecef;
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
       ACTION BUTTONS STYLING
       ============================================ */
    .action-buttons-cell {
        text-align: center !important;
        vertical-align: middle !important;
    }
    
    .action-buttons-container {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 12px;
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
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .btn-action i {
        font-size: 18px;
        display: inline-block;
    }
    
    .btn-action.view {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
    }
    
    .btn-action.view:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(23, 162, 184, 0.4);
        color: white;
        text-decoration: none;
        background: linear-gradient(135deg, #1fb0c8 0%, #1596aa 100%);
    }
    
    .btn-action.view:active {
        transform: translateY(0);
    }
    
    .btn-action.edit {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        color: #212529;
    }
    
    .btn-action.edit:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(255, 193, 7, 0.4);
        color: #212529;
        text-decoration: none;
        background: linear-gradient(135deg, #ffce3a 0%, #f0b800 100%);
    }
    
    .btn-action.edit:active {
        transform: translateY(0);
    }
    
    .btn-action.print {
        background: linear-gradient(135deg, #e65100 0%, #bf360c 100%);
        color: white;
    }
    
    .btn-action.print:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(230, 81, 0, 0.4);
        color: white;
        text-decoration: none;
        background: linear-gradient(135deg, #ff6d00 0%, #e65100 100%);
    }
    
    .btn-action.print:active {
        transform: translateY(0);
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
       PAGINATION STYLING
       ============================================ */
    .pagination {
        margin-top: 20px;
        justify-content: center;
    }
    
    .pagination > li > a,
    .pagination > li > span {
        border-radius: 8px;
        margin: 0 4px;
        color: #1e3c72;
        border: 1px solid #dee2e6;
        transition: all 0.2s ease;
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
       ADMISSION ANALYST COLUMN STYLING
       ============================================ */
    .admission-analyst-cell {
        font-size: 0.9rem;
        font-weight: 500;
        color: #495057;
    }
    
    .admission-analyst-cell i {
        color: #6c757d;
        margin-right: 6px;
    }
    
    /* ============================================
       FLASH MESSAGE STYLES
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
");
?>
<div class="row" style="margin:3px !important;">
    <!-- PROFESSIONAL FLASH MESSAGES DISPLAY -->
    <?php if (!empty($flashMessagesToDisplay)): ?>
        <div class="col-12">
            <?php foreach ($flashMessagesToDisplay as $type => $messages): ?>
                <?php foreach ((array)$messages as $message): ?>
                    <div class="alert alert-<?= $type ?> alert-elevated" role="alert" style="
                        border-radius: 10px;
                        border: 2px solid <?= $type === 'error' ? '#f5c6cb' : ($type === 'success' ? '#c3e6cb' : '#ffeaa7') ?>;
                        border-left: 6px solid <?= $type === 'error' ? '#dc3545' : ($type === 'success' ? '#28a745' : '#ffc107') ?>;
                        box-shadow: 0 6px 20px rgba(0,0,0,0.12);
                        margin: 25px auto;
                        max-width: 900px;
                        padding: 25px 30px;
                        position: relative;
                        font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
                    ">
                        <div class="d-flex align-items-start">
                            <div class="mr-4" style="font-size: 2.2rem; margin-top: 5px;">
                                <?php if ($type === 'error'): ?>
                                    <i class="fas fa-exclamation-circle text-danger"></i>
                                <?php elseif ($type === 'success'): ?>
                                    <i class="fas fa-check-circle text-success"></i>
                                <?php elseif ($type === 'warning'): ?>
                                    <i class="fas fa-exclamation-triangle text-warning"></i>
                                <?php else: ?>
                                    <i class="fas fa-info-circle text-info"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1" style="font-size: 1.05rem;">
                                <?php if ($type === 'error' && stripos($message, 'SUSPENDIDO') !== false): ?>
                                    <div class="contract-alert-header mb-3 p-3">
                                        <h4 class="alert-title mb-2" style="color: #721c24; font-weight: 700; font-size: 1.4rem; letter-spacing: 0.5px;">
                                            <i class="fas fa-ban mr-2"></i>¡ATENCIÓN IMPORTANTE!
                                        </h4>
                                        <div class="alert-subtitle text-muted" style="font-size: 1rem; font-weight: 500;">
                                            <i class="fas fa-calendar-times mr-2"></i> Restricción de Contrato - Acción Bloqueada
                                        </div>
                                    </div>

                                    <?php
                                    $cleanMessage = strip_tags($message);
                                    $lines = explode("\n", $cleanMessage);
                                    $formattedLines = [];

                                    foreach ($lines as $line) {
                                        $trimmedLine = trim($line);
                                        if (!empty($trimmedLine)) {
                                            $formattedLines[] = $trimmedLine;
                                        }
                                    }

                                    foreach ($formattedLines as $index => $formattedLine):
                                        if (strpos($formattedLine, '¡ATENCIÓN!') === 0):
                                            continue;
                                        elseif (strpos($formattedLine, 'No se puede crear una nueva atención para el afiliado') === 0):
                                            $afiliadoText = str_replace('No se puede crear una nueva atención para el afiliado ', '', $formattedLine);
                                    ?>
                                            <div class="mb-3 p-3" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 8px; border-left: 4px solid #6c757d;">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="fas fa-user-times mr-3" style="color: #dc3545; font-size: 1.3rem;"></i>
                                                    <h5 style="color: #495057; font-weight: 600; font-size: 1.2rem; margin: 0;">Restricción de Acceso</h5>
                                                </div>
                                                <p style="color: #495057; line-height: 1.6; font-size: 1.1rem; margin-left: 3rem;">
                                                    No se puede crear una nueva atención para el afiliado<br>
                                                    <strong style="color: #212529; font-size: 1.15rem;"><?= Html::encode($afiliadoText) ?></strong>
                                                </p>
                                            </div>
                                        <?php elseif (strpos($formattedLine, 'Motivo:') === 0):
                                            $motivoText = trim(str_replace('Motivo:', '', $formattedLine));
                                        ?>
                                            <div class="contract-suspended-box p-4 mb-3" style="background: linear-gradient(135deg, #fff5f5 0%, #ffeaea 100%); border-radius: 8px; border: 2px solid #ffcdd2;">
                                                <div class="d-flex align-items-center mb-3">
                                                    <i class="fas fa-file-contract mr-3" style="color: #dc3545; font-size: 1.4rem;"></i>
                                                    <h5 style="color: #dc3545; font-weight: 700; font-size: 1.25rem; margin: 0;">Estado del Contrato</h5>
                                                </div>
                                                <div class="ml-4 pl-1">
                                                    <span class="badge badge-danger px-4 py-3" style="font-size: 1.1rem; font-weight: 600; letter-spacing: 0.5px; background: linear-gradient(135deg, #ef5350 0%, #d32f2f 100%); box-shadow: 0 4px 12px rgba(211, 47, 47, 0.3); border-radius: 6px;">
                                                        <i class="fas fa-pause-circle mr-2"></i>
                                                        <?= Html::encode($motivoText) ?>
                                                    </span>
                                                    <p class="mt-3 mb-0" style="color: #721c24; font-size: 1rem; line-height: 1.5;">
                                                        <i class="fas fa-info-circle mr-2"></i>
                                                        El contrato se encuentra en estado de suspensión temporal
                                                    </p>
                                                </div>
                                            </div>
                                        <?php elseif (strpos($formattedLine, 'Período:') === 0):
                                            $periodoText = trim(str_replace('Período:', '', $formattedLine));
                                        ?>
                                            <div class="d-flex align-items-center mb-3 p-3" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 8px;">
                                                <i class="fas fa-calendar-alt mr-3" style="color: #6c757d; font-size: 1.4rem;"></i>
                                                <div>
                                                    <h6 style="color: #495057; font-weight: 600; font-size: 1.15rem; margin-bottom: 5px;">Vigencia de la Suspensión</h6>
                                                    <span style="color: #6c757d; font-size: 1.1rem; font-weight: 500;"><?= Html::encode($periodoText) ?></span>
                                                </div>
                                            </div>
                                        <?php elseif (strpos($formattedLine, 'Contacte') === 0): ?>
                                            <div class="alert-footer mt-4 pt-4" style="border-top: 2px solid #dee2e6; color: #495057; font-size: 1.05rem;">
                                                <div class="d-flex align-items-start">
                                                    <i class="fas fa-headset mr-3 mt-1" style="font-size: 1.4rem; color: #0c5460;"></i>
                                                    <div>
                                                        <h6 style="color: #0c5460; font-weight: 700; font-size: 1.2rem; margin-bottom: 8px;">
                                                            <i class="fas fa-exclamation-circle mr-2"></i>Acción Requerida
                                                        </h6>
                                                        <p style="color: #495057; line-height: 1.6; font-size: 1.1rem; margin: 0;">
                                                            <?= Html::encode($formattedLine) ?>
                                                        </p>
                                                        <div class="mt-3 pt-2" style="border-top: 1px dashed #adb5bd;">
                                                            <small style="color: #6c757d; font-size: 0.95rem;">
                                                                <i class="fas fa-lightbulb mr-2"></i>Para reactivar el servicio, regularice la situación contractual con el departamento administrativo.
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="mb-2 p-2" style="background-color: rgba(0,0,0,0.02); border-radius: 6px;">
                                                <p style="color: #495057; line-height: 1.6; font-size: 1.1rem; margin: 0;">
                                                    <i class="fas fa-circle mr-2" style="font-size: 0.6rem; color: #adb5bd;"></i>
                                                    <?= Html::encode($formattedLine) ?>
                                                </p>
                                            </div>
                                    <?php endif;
                                    endforeach;
                                else: ?>
                                    <?php $cleanMessage = strip_tags($message); ?>
                                    <div class="mb-3">
                                        <h4 class="alert-title mb-3" style="color: <?= $type === 'error' ? '#721c24' : ($type === 'success' ? '#155724' : '#856404') ?>; font-weight: 700; font-size: 1.4rem; letter-spacing: 0.3px;">
                                            <?php if ($type === 'error'): ?>
                                                <i class="fas fa-exclamation-circle mr-2"></i>Alerta Importante
                                            <?php elseif ($type === 'success'): ?>
                                                <i class="fas fa-check-circle mr-2"></i>Operación Exitosa
                                            <?php elseif ($type === 'warning'): ?>
                                                <i class="fas fa-exclamation-triangle mr-2"></i>Advertencia del Sistema
                                            <?php else: ?>
                                                <i class="fas fa-info-circle mr-2"></i>Notificación del Sistema
                                            <?php endif; ?>
                                        </h4>
                                        <div class="alert-message p-3" style="color: #495057; line-height: 1.7; font-size: 1.15rem; background-color: rgba(0,0,0,0.02); border-radius: 8px; border-left: 4px solid <?= $type === 'error' ? '#dc3545' : ($type === 'success' ? '#28a745' : '#ffc107') ?>;">
                                            <?= nl2br(Html::encode($cleanMessage)) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="close" onclick="this.parentElement.parentElement.style.display='none'"
                                style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: rgba(0,0,0,0.4); transition: all 0.2s; padding: 5px; border-radius: 4px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;"
                                onmouseover="this.style.color='rgba(0,0,0,0.8)'; this.style.backgroundColor='rgba(0,0,0,0.05)'"
                                onmouseout="this.style.color='rgba(0,0,0,0.4)'; this.style.backgroundColor='transparent'">
                                <span aria-hidden="true" style="font-size: 1.8rem;">&times;</span>
                            </button>
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
                    <h1 style="font-size: 1.8rem; font-weight: 700; letter-spacing: -0.3px;">
                        <i class="fas fa-calendar-alt me-2" style="color: #2a5298;"></i>
                        <?= $this->title ?>
                    </h1>
                </div>
                <div class="d-flex">
                    <?php
                    if ($permisos) {
                        echo Html::a(
                            '<i class="fas fa-plus-circle me-2"></i>' . $textoBoton,
                            ['create', 'user_id' => $user_id, 'es_cita' => $esCita],
                            ['class' => 'btn btn-create btn-lg mr-5', 'style' => 'font-size: 1rem; padding: 12px 24px; border-radius: 12px;']
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
                            'style' => 'font-size: 1rem; padding: 12px 24px; border-radius: 12px;'
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
                                'header' => '<i class="fas fa-hashtag me-1"></i> N°',
                                'headerOptions' => [
                                    'style' => 'width: 10px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white !important; font-weight: 600; text-align: center; width: 70px; border-radius: 12px 0 0 0;'
                                ],
                                'contentOptions' => [
                                    'style' => 'text-align: center; vertical-align: middle; padding: 8px 4px !important;'
                                ],
                                'options' => ['style' => 'width: 70px;'],
                                'value' => function ($model, $key, $index, $column) {
                                    $pagination = $column->grid->dataProvider->getPagination();
                                    if ($pagination) {
                                        $page = $pagination->getPage();
                                        $pageSize = $pagination->getPageSize();
                                        $sequentialNumber = $page * $pageSize + $index + 1;
                                    } else {
                                        $sequentialNumber = $index + 1;
                                    }

                                    return '<div class="consecutive-badge"><span>' . $sequentialNumber . '</span></div>';
                                },
                                'format' => 'raw',
                            ],
                            [
                                'attribute' => 'idclinica',
                                'value' => 'clinica.nombre',
                                'label' => 'Clínica',
                                'contentOptions' => ['style' => 'font-size: 0.95rem; font-weight: 500;'],
                            ],

                            // Columna para mostrar si es Cita o Atención
                            [
                                'label' => 'Tipo',
                                'attribute' => 'es_cita',
                                'format' => 'Html',
                                'contentOptions' => ['style' => 'text-align: center; padding: 10px !important;'],
                                'value' => function ($model) {
                                    return $model->es_cita == 1
                                        ? '<span class="status-badge cita"><i class="fas fa-calendar-check me-1"></i> Cita</span>'
                                        : '<span class="status-badge atencion"><i class="fas fa-heartbeat me-1" style="color: #ff6b6b;"></i> Atención</span>';
                                },
                                'filter' => [0 => 'Atención', 1 => 'Cita'],
                            ],
                            [
                                'attribute' => 'fecha_atencion',
                                'format' => 'Html',
                                'contentOptions' => ['style' => 'text-align: center; padding: 10px !important; font-size: 0.9rem;'],
                                'value' => function ($model) {
                                    return '<i class="fas fa-calendar-day me-1" style="color: #6c757d;"></i> ' . Yii::$app->formatter->asDate($model->fecha_atencion);
                                },
                            ],
                            [
                                'attribute' => 'hora_atencion',
                                'format' => 'Html',
                                'contentOptions' => ['style' => 'text-align: center; padding: 10px !important; font-size: 0.9rem;'],
                                'value' => function ($model) {
                                    return '<i class="fas fa-hourglass-half me-1" style="color: #6c757d;"></i> ' . Yii::$app->formatter->asTime($model->hora_atencion);
                                },
                            ],
                            [
                                'attribute' => 'baremos',
                                'format' => 'raw',
                                'contentOptions' => ['style' => 'max-width: 400px; white-space: normal; padding: 12px 8px;'],
                                'value' => function ($model) {
                                    $baremos = $model->baremos;
                                    if (empty($baremos)) {
                                        return '<span class="text-muted" style="font-size: 0.95rem; font-style: italic;">
                                                    <i class="fas fa-info-circle me-1"></i> Sin servicios
                                                </span>';
                                    }

                                    $items = [];
                                    foreach ($baremos as $baremo) {
                                        $serviceName = is_array($baremo) ? $baremo['nombre_servicio'] : $baremo->nombre_servicio;
                                        $serviceArea = is_array($baremo) ? ($baremo['area_nombre'] ?? '') : ($baremo->area->nombre ?? '');
                                        $servicePrice = is_array($baremo) ? ($baremo['precio'] ?? 0) : ($baremo->precio ?? 0);
                                        $serviceDesc = is_array($baremo) ? ($baremo['descripcion'] ?? '') : ($baremo->descripcion ?? '');

                                        if (!empty($serviceArea)) {
                                            $areaDisplay = '<i class="fas fa-tag me-1" style="font-size: 0.75rem;"></i>' . Html::encode($serviceArea);
                                        } else {
                                            $areaDisplay = '<span style="color: #856404;"><i class="fas fa-question-circle me-1" style="font-size: 0.75rem;"></i>Sin categoría</span>';
                                        }

                                        $descDisplay = '';
                                        if (!empty($serviceDesc)) {
                                            $descDisplay = '<div class="service-description">' .
                                                '<i class="fas fa-align-left me-1"></i>' . Html::encode($serviceDesc) .
                                                '</div>';
                                        }

                                        $items[] = Html::tag(
                                            'div',
                                            '<div class="d-flex align-items-start gap-2" style="margin-bottom: 10px;">' .
                                                '<div class="flex-shrink-0" style="width: 32px; text-align: center;">' .
                                                '<i class="fas fa-stethoscope" style="color: #2a5298; font-size: 18px;"></i>' .
                                                '</div>' .
                                                '<div class="flex-grow-1">' .
                                                '<div class="service-name">' . Html::encode($serviceName) . '</div>' .
                                                '<div class="service-details">' .
                                                $areaDisplay .
                                                '<span class="mx-2">•</span>' .
                                                '<i class="fas fa-dollar-sign me-1" style="font-size: 0.7rem;"></i><strong>$' . number_format($servicePrice, 2) . '</strong>' .
                                                '</div>' .
                                                $descDisplay .
                                                '</div>' .
                                                '</div>',
                                            ['class' => 'service-item']
                                        );
                                    }

                                    return '<div class="services-container">' . implode('', $items) . '</div>';
                                },
                                'label' => '<i class="fas fa-notes-medical me-2"></i> Servicios Médicos',
                                'encodeLabel' => false,
                                'headerOptions' => [
                                    'style' => 'background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white !important; font-weight: 600;'
                                ],
                                'filter' => false,
                            ],
                            [
                                'attribute' => 'nombre_doctor',
                                'label' => 'Doctor',
                                'format' => 'raw',
                                'headerOptions' => ['style' => 'width:15%; min-width: 150px;'],
                                'contentOptions' => ['style' => 'white-space: normal; word-wrap: break-word;'],
                                'value' => function ($model) {
                                    if (empty($model->nombre_doctor)) {
                                        return '<span class="text-muted"><i class="fas fa-user-md"></i> No asignado</span>';
                                    }
                                    return '<i class="fas fa-user-md text-info mr-2"></i> ' . Html::encode($model->nombre_doctor);
                                },
                            ],
                            // ADMISSION ANALYST COLUMN
                            [
                                'attribute' => 'admission_analyst',
                                'label' => '<i class="fas fa-user-check me-2"></i> Analista',
                                'encodeLabel' => false,
                                'format' => 'raw',
                                'contentOptions' => ['style' => 'text-align: left; vertical-align: middle; padding: 10px !important;', 'class' => 'admission-analyst-cell'],
                                'headerOptions' => [
                                    'style' => 'background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white !important; font-weight: 600; text-align: center;'
                                ],
                                'value' => function ($model) {
                                    if (empty($model->admission_analyst)) {
                                        return '<span class="text-muted" style="font-size: 0.85rem; font-style: italic;">
                                                    <i class="fas fa-user-slash me-1"></i> No asignado
                                                </span>';
                                    }
                                    return '<i class="fas fa-user-check me-2" style="color: #28a745;"></i> ' . Html::encode($model->admission_analyst);
                                },
                                'filterInputOptions' => [
                                    'class' => 'form-control',
                                    'placeholder' => 'Buscar analista...'
                                ],
                            ],

                            [
                                'attribute' => 'costo_total',
                                'format' => ['currency', 'USD'],
                                'headerOptions' => [
                                    'style' => 'background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white !important; font-weight: 600; text-align: right;'
                                ],
                                'contentOptions' => [
                                    'style' => 'text-align: right; font-size: 1.25rem; font-weight: 800; color: #28a745; padding: 10px !important;'
                                ],
                                'filter' => false
                            ],
                            [
                                'attribute' => 'atendido',
                                'format' => 'Html',
                                'contentOptions' => ['style' => 'text-align: center; padding: 10px !important;'],
                                'value' => function ($model) {
                                    $isTrue = $model->atendido;
                                    return $isTrue == 1
                                        ? '<span class="status-badge atendido"><i class="fas fa-check-circle me-1"></i> Sí</span>'
                                        : '<span class="status-badge no-atendido"><i class="fas fa-times-circle me-1"></i> No</span>';
                                },
                                'filter' => [0 => 'No', 1 => 'Sí'],
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => '<i class="fas fa-cog me-1"></i> ACCIONES',
                                'template' => '<div class="action-buttons-container">{view}{update}{print}</div>',
                                'options' => ['class' => 'action-buttons-cell'],
                                'headerOptions' => [
                                    'style' => 'background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white !important; font-weight: 600; text-align: center; width: 150px;'
                                ],
                                'contentOptions' => [
                                    'style' => 'text-align: center; vertical-align: middle; padding: 10px !important;'
                                ],
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fa fa-eye"></i>',
                                            Url::to(['view', 'id' => $model->id, 'user_id' => $model->iduser, 'es_cita' => $model->es_cita]),
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
                                                Url::to([
                                                    'update',
                                                    'id' => $model->id,
                                                    'user_id' => $model->iduser,
                                                    'es_cita' => (int)$model->es_cita
                                                ]),
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