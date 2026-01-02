<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use app\components\UserHelper;


/**
 * @var yii\web\View $this
 * @var app\models\SisSiniestroSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\UserDatos $afiliado
 * @var int $user_id
 * @var string $modo 'siniestro' o 'cita' <-- ASUMIMOS QUE ESTO SE PASA DESDE EL CONTROLADOR
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
$flashMessages = Yii::$app->session->getAllFlashes();
foreach ($flashMessages as $type => $messages) {
    foreach ((array)$messages as $message) {
        if (stripos($message, 'SUSPENDIDO') !== false) {
            $contratoSuspendido = true;
            break 2; // Salir de ambos bucles
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
    <!-- ULTRA NUCLEAR SOLUTION -->
    <?php $flashMessages = Yii::$app->session->getAllFlashes(); ?>
    <?php if (!empty($flashMessages)): ?>
        <div style="
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        width: 100vw !important;
        position: relative !important;
        left: 50% !important;
        right: 50% !important;
        margin-left: -50vw !important;
        margin-right: -50vw !important;
        padding: 0 !important;
        margin-bottom: 20px !important;
    ">
            <div style="
            width: 100% !important;
            max-width: 800px !important;
            margin: 0 auto !important;
            padding: 0 15px !important;
        ">
                <?php foreach ($flashMessages as $type => $messages): ?>
                    <?php foreach ((array)$messages as $message): ?>
                        <div style="
                        display: block !important;
                        width: 100% !important;
                        margin: 0 auto !important;
                        padding: 20px !important;
                        border-radius: 5px !important;
                        border-left: 4px solid <?= $type === 'error' ? '#dc3545' : ($type === 'success' ? '#28a745' : '#ffc107') ?> !important;
                        background-color: <?= $type === 'error' ? '#f8d7da' : ($type === 'success' ? '#d4edda' : ($type === 'warning' ? '#fff3cd' : '#d1ecf1')) ?> !important;
                        color: <?= $type === 'error' ? '#721c24' : ($type === 'success' ? '#155724' : ($type === 'warning' ? '#856404' : '#0c5460')) ?> !important;
                        text-align: center !important;
                        position: relative !important;
                    ">
                            <h5 style="
                            text-align: center !important;
                            margin: 0 auto 15px auto !important;
                            color: <?= $type === 'error' ? '#721c24' : ($type === 'success' ? '#155724' : ($type === 'warning' ? '#856404' : '#0c5460')) ?> !important;
                            display: block !important;
                            width: 100% !important;
                        ">
                                <i class="fas fa-ban mr-2"></i>¡ATENCIÓN!
                            </h5>

                            <?php
                            $lines = explode("\n", $message);
                            foreach ($lines as $index => $line):
                                $trimmedLine = trim($line);
                                if (!empty($trimmedLine)):
                                    if (strpos($trimmedLine, 'Motivo:') === 0): ?>
                                        <div style="text-align: center !important; margin-bottom: 10px !important;">
                                            <strong style="color: #dc3545 !important;">Motivo:</strong>
                                            <span style="margin-left: 8px !important;"><?= Html::encode(substr($trimmedLine, 7)) ?></span>
                                        </div>
                                    <?php elseif (strpos($trimmedLine, 'Período:') === 0): ?>
                                        <div style="text-align: center !important; margin-bottom: 10px !important;">
                                            <strong>Período:</strong>
                                            <span style="margin-left: 8px !important;"><?= Html::encode(substr($trimmedLine, 8)) ?></span>
                                        </div>
                                    <?php elseif (strpos($trimmedLine, 'Contacte') === 0): ?>
                                        <div style="text-align: center !important; margin-top: 15px !important; padding-top: 15px !important; border-top: 1px solid rgba(0,0,0,0.1) !important;">
                                            <p style="margin: 0 !important; font-style: italic !important;">
                                                <i class="fas fa-headset mr-2"></i>
                                                <?= Html::encode($trimmedLine) ?>
                                            </p>
                                        </div>
                                    <?php else: ?>
                                        <p style="
                                        text-align: center !important;
                                        margin: 0 auto <?= ($index === count($lines) - 1 && strpos($trimmedLine, 'Contacte') === false) ? '0' : '10px' ?> auto !important;
                                        max-width: 100% !important;
                                        display: block !important;
                                    ">
                                            <?= Html::encode($trimmedLine) ?>
                                        </p>
                            <?php endif;
                                endif;
                            endforeach; ?>

                            <button type="button" onclick="this.parentElement.style.display='none'"
                                style="
                                    position: absolute !important;
                                    top: 10px !important;
                                    right: 15px !important;
                                    background: none !important;
                                    border: none !important;
                                    font-size: 1.5rem !important;
                                    cursor: pointer !important;
                                    color: #000 !important;
                                    opacity: 0.5 !important;
                                ">
                                &times;
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- ENHANCED PROFESSIONAL ALERT STYLES -->
        <style>
            .attention-alert {
                animation: attention-pulse 1.2s infinite alternate ease-in-out;
                color: #d32f2f !important;
                font-weight: 700 !important;
                font-size: 1.3em !important;
                display: inline-block;
                padding: 4px 12px;
                margin: 0 3px;
                border-radius: 4px;
                background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
                box-shadow: 0 3px 12px rgba(211, 47, 47, 0.3);
                border: 2px solid #ff8a80;
                position: relative;
                overflow: hidden;
                letter-spacing: 0.5px;
                text-transform: uppercase;
            }

            .attention-alert::before {
                content: '⚠';
                margin-right: 6px;
                font-size: 1em;
                animation: icon-pulse 1s infinite alternate;
            }

            .attention-alert::after {
                content: '';
                position: absolute;
                top: -50%;
                left: -50%;
                width: 200%;
                height: 200%;
                background: linear-gradient(to right,
                        transparent 20%,
                        rgba(255, 255, 255, 0.4) 50%,
                        transparent 80%);
                transform: rotate(30deg);
                animation: light-sweep 2.5s infinite linear;
            }

            .contract-alert {
                animation: contract-warning 1s infinite alternate cubic-bezier(0.4, 0, 0.2, 1);
                color: #ffffff !important;
                font-weight: 600 !important;
                font-size: 1.2em !important;
                display: inline-block;
                padding: 5px 14px;
                margin: 0 4px;
                border-radius: 4px;
                background: linear-gradient(135deg, #ef5350 0%, #d32f2f 100%);
                box-shadow:
                    0 4px 15px rgba(211, 47, 47, 0.4),
                    inset 0 1px 0 rgba(255, 255, 255, 0.3);
                text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
                position: relative;
                overflow: hidden;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            .contract-alert::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(90deg,
                        transparent 30%,
                        rgba(255, 255, 255, 0.2) 50%,
                        transparent 70%);
                animation: shimmer 2s infinite;
            }

            .contract-alert::after {
                content: '';
                position: absolute;
                top: -2px;
                left: -2px;
                right: -2px;
                bottom: -2px;
                border-radius: 6px;
                background: linear-gradient(45deg, #ff5252, #ff8a80, #ff5252);
                z-index: -1;
                animation: border-glow 1.5s infinite alternate;
                opacity: 0.7;
            }

            @keyframes attention-pulse {
                0% {
                    transform: scale(1) translateY(0);
                    box-shadow: 0 3px 12px rgba(211, 47, 47, 0.3);
                    border-color: #ff8a80;
                }

                100% {
                    transform: scale(1.05) translateY(-2px);
                    box-shadow: 0 6px 20px rgba(211, 47, 47, 0.5);
                    border-color: #ff5252;
                }
            }

            @keyframes icon-pulse {
                0% {
                    transform: scale(1);
                    opacity: 0.8;
                }

                100% {
                    transform: scale(1.2);
                    opacity: 1;
                }
            }

            @keyframes light-sweep {
                0% {
                    transform: translateX(-100%) translateY(-100%) rotate(30deg);
                }

                100% {
                    transform: translateX(100%) translateY(100%) rotate(30deg);
                }
            }

            @keyframes contract-warning {
                0% {
                    transform: scale(1) translateY(0);
                    box-shadow:
                        0 4px 15px rgba(211, 47, 47, 0.4),
                        inset 0 1px 0 rgba(255, 255, 255, 0.3);
                    background: linear-gradient(135deg, #ef5350 0%, #d32f2f 100%);
                }

                100% {
                    transform: scale(1.03) translateY(-2px);
                    box-shadow:
                        0 8px 25px rgba(211, 47, 47, 0.6),
                        inset 0 1px 0 rgba(255, 255, 255, 0.4);
                    background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
                }
            }

            @keyframes shimmer {
                0% {
                    transform: translateX(-100%);
                }

                100% {
                    transform: translateX(100%);
                }
            }

            @keyframes border-glow {
                0% {
                    opacity: 0.5;
                    filter: blur(4px);
                }

                100% {
                    opacity: 0.8;
                    filter: blur(6px);
                }
            }

            /* Alert container enhancements */
            .alert-danger {
                border: 1px solid rgba(211, 47, 47, 0.2);
                border-left: 4px solid #d32f2f;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            }

            .alert-danger:hover {
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
                transition: box-shadow 0.3s ease;
            }
        </style>
    <?php endif; ?>

    <input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />

    <div class="col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header d-flex justify-content-between align-items-center">
                <h1><?= $this->title ?></h1>
                <div class="d-flex gap-3"> <?php
                                            // BOTÓN DE CREACIÓN DINÁMICO
                                            if ($permisos) {
                                                echo Html::a(
                                                    '<i class="fas fa-plus"></i> ' . $textoBoton,
                                                    // Enlace a actionCreate, pasando user_id y el valor binario es_cita (0 o 1)
                                                    ['create', 'user_id' => $user_id, 'es_cita' => $esCita],
                                                    ['class' => 'btn btn-outline-primary btn-lg']
                                                );
                                            }
                                            ?>
                    <?= Html::a(
                        '<i class="' . $volverBtnIcon . ' mr-2"></i> Volver',
                        ['/user-datos/index-clinicas', 'clinica_id' => $afiliado->clinica_id],
                        [
                            'class' => 'btn btn-lg ' . $volverBtnClass,
                            'title' => $volverBtnTitle,
                            'data' => ['pjax' => 0],
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
                            ],
                            [
                                'attribute' => 'idclinica',
                                'value' => 'clinica.nombre',
                                'label' => 'Clínica',
                            ],
                            [
                                'attribute' => 'fecha',
                                'format' => 'Html',
                                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                                'value' => function ($model) {
                                    return Yii::$app->formatter->asDate($model->fecha);
                                },
                            ],
                            [
                                'attribute' => 'hora',
                                'format' => 'Html',
                                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                                'value' => function ($model) {
                                    return Yii::$app->formatter->asTime($model->hora);
                                },
                            ],
                            // Columna para mostrar si es Cita o Siniestro
                            [
                                'label' => 'Tipo',
                                'attribute' => 'es_cita',
                                'format' => 'Html',
                                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                                'value' => function ($model) {
                                    return $model->es_cita == 1 ? '<span class="status-badge active bg-success">Cita</span>' : '<span class="status-badge inactive bg-primary">Siniestro</span>';
                                },
                                'filter' => [0 => 'Siniestro', 1 => 'Cita'],
                            ],
                            [
                                'attribute' => 'baremos',
                                'format' => 'raw',
                                'contentOptions' => ['style' => 'max-width: 250px; white-space: normal;'],
                                'value' => function ($model) {
                                    $baremos = $model->baremos;
                                    if (empty($baremos)) {
                                        return '<span class="text-muted">No hay baremos</span>';
                                    }

                                    $items = [];
                                    foreach ($baremos as $baremo) {
                                        if (is_array($baremo) && isset($baremo['nombre_servicio'])) {
                                            $items[] = Html::tag(
                                                'div',
                                                Html::encode($baremo['nombre_servicio']),
                                                ['class' => 'mb-1']
                                            );
                                        } elseif (is_object($baremo) && property_exists($baremo, 'nombre_servicio')) {
                                            $items[] = Html::tag(
                                                'div',
                                                Html::encode($baremo->nombre_servicio),
                                                ['class' => 'mb-1']
                                            );
                                        }
                                    }

                                    return !empty($items) ? implode('', $items) : '<span class="text-muted">No hay baremos</span>';
                                },
                                'label' => 'Baremos',
                            ],
                            [
                                'attribute' => 'fecha_atencion',
                                'format' => 'Html',
                                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                                'value' => function ($model) {
                                    return Yii::$app->formatter->asDate($model->fecha_atencion);
                                },
                            ],
                            [
                                'attribute' => 'hora_atencion',
                                'format' => 'Html',
                                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                                'value' => function ($model) {
                                    return Yii::$app->formatter->asTime($model->hora_atencion);
                                },
                            ],

                            [
                                'attribute' => 'costo_total',
                                'format' => ['currency', 'USD'],
                                'contentOptions' => ['style' => 'text-align: right;'],
                                'filter' => false
                            ],

                            [
                                'attribute' => 'atendido',
                                'format' => 'Html',
                                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                                'value' => function ($model) {
                                    $isTrue = $model->atendido;
                                    return $isTrue == 1 ? '<p class="status-badge active">Sí</p>' : '<p class="status-badge inactive">No</p>';
                                },
                                'filter' => [0 => 'No', 1 => 'Sí'],
                            ],


                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => 'ACCIONES',
                                'template' => '<div class="d-flex justify-content-center gap-0">{view}{update}</div>',
                                'options' => ['class' => 'action-buttons'],
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fa fa-eye"></i>',
                                            Url::to(['view', 'id' => $model->id, 'user_id' => $model->iduser]),
                                            [
                                                'title' => 'Detalle de la atención',
                                                'class' => 'btn-action view'
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
                                                    'class' => 'btn-action edit'
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