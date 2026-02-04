<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use app\components\UserHelper;
use yii\bootstrap4\Alert;

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
// ADD COORDINADOR-CLINICA TO PERMISSIONS
$permisos = ($rol == 'superadmin' || $rol == 'DIRECTOR-COMERCIALIZACION' || $rol == 'Asesor' || $rol == 'Agente' || $rol == "ADMISIÓN" || $rol == "CONTROL DE CITAS" || $rol == "Administrador-clinica" || $rol == "COORDINADOR-CLINICA");

// Definir variables basadas en el modo
$esCita = ($modo === 'cita') ? 1 : 0;
$tituloModo = ($modo === 'cita') ? 'Citas' : 'Atención';
$textoBoton = ($modo === 'cita') ? 'Crear Nueva Cita' : 'Crear Nueva Atención';

$this->params['breadcrumbs'][] = ['label' => 'AFILIADOS', 'url' => ['/user-datos/index-clinicas', 'clinica_id' => $afiliado->clinica_id]];
// Título ahora refleja el modo
$this->title = $tituloModo . ' para ' . Html::encode($afiliado->nombres . " " . $afiliado->apellidos . " " . $afiliado->tipo_cedula . "-" . $afiliado->cedula);

// ----------------------------------------------------------------------
// 2. DETECTAR SI HAY MENSAJE DE CONTRATO SUSPENDIDO
// ----------------------------------------------------------------------
$contratoSuspendido = false;
$allFlashMessages = Yii::$app->session->getAllFlashes(); // Store in variable
// Force session save to remove flash messages immediately
Yii::$app->session->close();
Yii::$app->session->open();
// Create a copy for display (to prevent duplicate display)
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
                                    <!-- Special styling for suspended contract messages -->
                                    <div class="contract-alert-header mb-3 p-3">
                                        <h4 class="alert-title mb-2" style="color: #721c24; font-weight: 700; font-size: 1.4rem; letter-spacing: 0.5px;">
                                            <i class="fas fa-ban mr-2"></i>¡ATENCIÓN IMPORTANTE!
                                        </h4>
                                        <div class="alert-subtitle text-muted" style="font-size: 1rem; font-weight: 500;">
                                            <i class="fas fa-calendar-times mr-2"></i> Restricción de Contrato - Acción Bloqueada
                                        </div>
                                    </div>

                                    <?php
                                    // Clean HTML tags from the message for parsing
                                    $cleanMessage = strip_tags($message);

                                    // Parse and format the suspended contract message nicely
                                    $lines = explode("\n", $cleanMessage);
                                    $formattedLines = [];

                                    foreach ($lines as $line) {
                                        $trimmedLine = trim($line);
                                        if (!empty($trimmedLine)) {
                                            $formattedLines[] = $trimmedLine;
                                        }
                                    }

                                    // Display formatted message
                                    foreach ($formattedLines as $index => $formattedLine):
                                        if (strpos($formattedLine, '¡ATENCIÓN!') === 0):
                                            // Skip the header as we already have our own
                                            continue;
                                        elseif (strpos($formattedLine, 'No se puede crear una nueva atención para el afiliado') === 0):
                                            // Extract afiliado name
                                            $afiliadoText = str_replace('No se puede crear una nueva atención para el afiliado ', '', $formattedLine);
                                    ?>
                                            <div class="mb-3 p-3" style="
                                                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                                                border-radius: 8px;
                                                border-left: 4px solid #6c757d;
                                            ">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="fas fa-user-times mr-3" style="color: #dc3545; font-size: 1.3rem;"></i>
                                                    <h5 style="color: #495057; font-weight: 600; font-size: 1.2rem; margin: 0;">
                                                        Restricción de Acceso
                                                    </h5>
                                                </div>
                                                <p style="color: #495057; line-height: 1.6; font-size: 1.1rem; margin-left: 3rem;">
                                                    No se puede crear una nueva atención para el afiliado<br>
                                                    <strong style="color: #212529; font-size: 1.15rem;"><?= Html::encode($afiliadoText) ?></strong>
                                                </p>
                                            </div>
                                        <?php elseif (strpos($formattedLine, 'Motivo:') === 0):
                                            $motivoText = trim(str_replace('Motivo:', '', $formattedLine));
                                        ?>
                                            <div class="contract-suspended-box p-4 mb-3" style="
                                                background: linear-gradient(135deg, #fff5f5 0%, #ffeaea 100%);
                                                border-radius: 8px;
                                                border: 2px solid #ffcdd2;
                                            ">
                                                <div class="d-flex align-items-center mb-3">
                                                    <i class="fas fa-file-contract mr-3" style="color: #dc3545; font-size: 1.4rem;"></i>
                                                    <h5 style="color: #dc3545; font-weight: 700; font-size: 1.25rem; margin: 0;">
                                                        Estado del Contrato
                                                    </h5>
                                                </div>
                                                <div class="ml-4 pl-1">
                                                    <span class="badge badge-danger px-4 py-3" style="
                                                        font-size: 1.1rem;
                                                        font-weight: 600;
                                                        letter-spacing: 0.5px;
                                                        background: linear-gradient(135deg, #ef5350 0%, #d32f2f 100%);
                                                        box-shadow: 0 4px 12px rgba(211, 47, 47, 0.3);
                                                        border-radius: 6px;
                                                    ">
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
                                            <div class="d-flex align-items-center mb-3 p-3" style="
                                                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                                                border-radius: 8px;
                                            ">
                                                <i class="fas fa-calendar-alt mr-3" style="color: #6c757d; font-size: 1.4rem;"></i>
                                                <div>
                                                    <h6 style="color: #495057; font-weight: 600; font-size: 1.15rem; margin-bottom: 5px;">
                                                        Vigencia de la Suspensión
                                                    </h6>
                                                    <span style="color: #6c757d; font-size: 1.1rem; font-weight: 500;">
                                                        <?= Html::encode($periodoText) ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php elseif (strpos($formattedLine, 'Contacte') === 0): ?>
                                            <div class="alert-footer mt-4 pt-4" style="
                                                border-top: 2px solid #dee2e6;
                                                color: #495057;
                                                font-size: 1.05rem;
                                            ">
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
                                    <!-- Regular flash messages - strip HTML tags first, then display -->
                                    <?php $cleanMessage = strip_tags($message); ?>
                                    <div class="mb-3">
                                        <h4 class="alert-title mb-3" style="
                                            color: <?= $type === 'error' ? '#721c24' : ($type === 'success' ? '#155724' : '#856404') ?>;
                                            font-weight: 700;
                                            font-size: 1.4rem;
                                            letter-spacing: 0.3px;
                                        ">
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
                                        <div class="alert-message p-3" style="
                                            color: #495057; 
                                            line-height: 1.7; 
                                            font-size: 1.15rem;
                                            background-color: rgba(0,0,0,0.02);
                                            border-radius: 8px;
                                            border-left: 4px solid <?= $type === 'error' ? '#dc3545' : ($type === 'success' ? '#28a745' : '#ffc107') ?>;
                                        ">
                                            <?= nl2br(Html::encode($cleanMessage)) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="close" onclick="this.parentElement.parentElement.style.display='none'"
                                style="
                                    position: absolute;
                                    top: 20px;
                                    right: 20px;
                                    background: none;
                                    border: none;
                                    font-size: 1.5rem;
                                    cursor: pointer;
                                    color: rgba(0,0,0,0.4);
                                    transition: all 0.2s;
                                    padding: 5px;
                                    border-radius: 4px;
                                    width: 40px;
                                    height: 40px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                "
                                onmouseover="this.style.color='rgba(0,0,0,0.8)'; this.style.backgroundColor='rgba(0,0,0,0.05)'"
                                onmouseout="this.style.color='rgba(0,0,0,0.4)'; this.style.backgroundColor='transparent'">
                                <span aria-hidden="true" style="font-size: 1.8rem;">&times;</span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>

        <style>
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

            .badge-danger {
                animation: pulse 1.5s infinite;
                transition: all 0.3s ease;
            }

            .badge-danger:hover {
                transform: scale(1.02);
                box-shadow: 0 6px 16px rgba(211, 47, 47, 0.4);
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

            .alert-footer {
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                padding: 20px;
                border-radius: 10px;
                border: 1px solid #dee2e6;
            }

            /* Typography enhancements */
            .alert-title {
                font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
                letter-spacing: 0.3px;
            }

            .alert-message {
                font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
                letter-spacing: 0.1px;
            }

            /* Add the original attention-alert and contract-alert styles */
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
        </style>
    <?php endif; ?>

    <input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />

    <div class="col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header d-flex justify-content-between align-items-center">
                <h1 style="font-size: 1.8rem; font-weight: 600; letter-spacing: 0.3px;"><?= $this->title ?></h1>
                <div class="d-flex gap-3"> <?php
                                            // BOTÓN DE CREACIÓN DINÁMICO
                                            if ($permisos) {
                                                echo Html::a(
                                                    '<i class="fas fa-plus mr-2"></i>' . $textoBoton,
                                                    // Enlace a actionCreate, pasando user_id y el valor binario es_cita (0 o 1)
                                                    ['create', 'user_id' => $user_id, 'es_cita' => $esCita],
                                                    ['class' => 'btn btn-outline-primary btn-lg', 'style' => 'font-size: 1.05rem; padding: 10px 20px;']
                                                );
                                            }
                                            ?>
                    <?= Html::a(
                        '<i class="' . $volverBtnIcon . ' mr-2"></i> Volver',
                        ['/user-datos/index-clinicas', 'clinica_id' => $afiliado->clinica_id],
                        [
                            'class' => 'btn btn-lg ' . $volverBtnIcon,
                            'title' => $volverBtnTitle,
                            'data' => ['pjax' => 0],
                            'style' => 'font-size: 1.05rem; padding: 10px 20px;'
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
                        'tableOptions' => [
                            'class' => 'table table-striped table-bordered table-hover '
                        ],
                        'options' => [
                            'class' => 'grid-view-container table-responsive',
                        ],
                        'columns' => [
                            [
                                'attribute' => 'id',
                                'value' => 'id',
                                'label' => 'ID',
                                'contentOptions' => ['style' => 'font-size: 1rem;'],
                            ],
                            [
                                'attribute' => 'idclinica',
                                'value' => 'clinica.nombre',
                                'label' => 'Clínica',
                                'contentOptions' => ['style' => 'font-size: 1rem;'],
                            ],
                            [
                                'attribute' => 'fecha',
                                'format' => 'Html',
                                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important; font-size: 1rem;'],
                                'value' => function ($model) {
                                    return Yii::$app->formatter->asDate($model->fecha);
                                },
                            ],
                            [
                                'attribute' => 'hora',
                                'format' => 'Html',
                                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important; font-size: 1rem;'],
                                'value' => function ($model) {
                                    return Yii::$app->formatter->asTime($model->hora);
                                },
                            ],
                            // Columna para mostrar si es Cita o Siniestro
                            [
                                'label' => 'Tipo',
                                'attribute' => 'es_cita',
                                'format' => 'Html',
                                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important; font-size: 1rem;'],
                                'value' => function ($model) {
                                    return $model->es_cita == 1 ? '<span class="status-badge active bg-success" style="font-size: 0.95rem; padding: 6px 12px;">Cita</span>' : '<span class="status-badge inactive bg-primary" style="font-size: 0.95rem; padding: 6px 12px;">Siniestro</span>';
                                },
                                'filter' => [0 => 'Siniestro', 1 => 'Cita'],
                            ],
                            [
                                'attribute' => 'baremos',
                                'format' => 'raw',
                                'contentOptions' => ['style' => 'max-width: 250px; white-space: normal; font-size: 1rem;'],
                                'value' => function ($model) {
                                    $baremos = $model->baremos;
                                    if (empty($baremos)) {
                                        return '<span class="text-muted" style="font-size: 1rem;">No hay baremos</span>';
                                    }

                                    $items = [];
                                    foreach ($baremos as $baremo) {
                                        if (is_array($baremo) && isset($baremo['nombre_servicio'])) {
                                            $items[] = Html::tag(
                                                'div',
                                                Html::encode($baremo['nombre_servicio']),
                                                ['class' => 'mb-1', 'style' => 'font-size: 1rem;']
                                            );
                                        } elseif (is_object($baremo) && property_exists($baremo, 'nombre_servicio')) {
                                            $items[] = Html::tag(
                                                'div',
                                                Html::encode($baremo->nombre_servicio),
                                                ['class' => 'mb-1', 'style' => 'font-size: 1rem;']
                                            );
                                        }
                                    }

                                    return !empty($items) ? implode('', $items) : '<span class="text-muted" style="font-size: 1rem;">No hay baremos</span>';
                                },
                                'label' => 'Baremos',
                            ],
                            [
                                'attribute' => 'fecha_atencion',
                                'format' => 'Html',
                                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important; font-size: 1rem;'],
                                'value' => function ($model) {
                                    return Yii::$app->formatter->asDate($model->fecha_atencion);
                                },
                            ],
                            [
                                'attribute' => 'hora_atencion',
                                'format' => 'Html',
                                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important; font-size: 1rem;'],
                                'value' => function ($model) {
                                    return Yii::$app->formatter->asTime($model->hora_atencion);
                                },
                            ],

                            [
                                'attribute' => 'costo_total',
                                'format' => ['currency', 'USD'],
                                'contentOptions' => ['style' => 'text-align: right; font-size: 1rem;'],
                                'filter' => false
                            ],

                            [
                                'attribute' => 'atendido',
                                'format' => 'Html',
                                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important; font-size: 1rem;'],
                                'value' => function ($model) {
                                    $isTrue = $model->atendido;
                                    return $isTrue == 1 ? '<p class="status-badge active" style="font-size: 1rem; padding: 6px 12px;">Sí</p>' : '<p class="status-badge inactive" style="font-size: 1rem; padding: 6px 12px;">No</p>';
                                },
                                'filter' => [0 => 'No', 1 => 'Sí'],
                            ],


                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => 'ACCIONES',
                                'template' => '<div class="d-flex justify-content-center gap-0">{view}{update}</div>',
                                'options' => ['class' => 'action-buttons'],
                                'headerOptions' => ['style' => 'color: white!important; font-size: 1.1rem;'],
                                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fa fa-eye"></i>',
                                            Url::to(['view', 'id' => $model->id, 'user_id' => $model->iduser]),
                                            [
                                                'title' => 'Detalle de la atención',
                                                'class' => 'btn-action view',
                                                'style' => 'font-size: 1.1rem;'
                                            ]
                                        );
                                    },
                                    'update' => function ($url, $model, $key) use ($permisos) {
                                        if ($permisos) {
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
                                                    'style' => 'font-size: 1.1rem;'
                                                ]
                                            );
                                        }
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