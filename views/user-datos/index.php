<?php

use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\select2\Select2;
use app\components\UserHelper;
use app\models\RmClinica; // Importar el modelo de la clínica

/** @var yii\web\View $this */
/** @var app\models\UserSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string|null $clinica_id // Se asume que este parámetro puede venir de la URL */

// --- Detección y Carga de Clínica (para contexto) ---
$clinica = null;
$clinica_id_param = Yii::$app->request->get('clinica_id'); // Obtener clinica_id de la URL

if (!empty($clinica_id_param)) {
    $clinica = RmClinica::findOne((int)$clinica_id_param);
    if (!$clinica) {
        $clinica = (object)['id' => (int)$clinica_id_param, 'nombre' => 'Clínica Desconocida'];
    }
}

$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol == 'DIRECTOR-COMERCIALIZACIÓN' || $rol == 'Asesor' || $rol == 'Agente' || $rol == "ADMISIÓN" || $rol == "COORDINADOR-CLINICA"); // Lógica de permisos original

// --- BREADCRUMBS CONDICIONALES ---
if($permisos == true){
$this->params['breadcrumbs'][] = ['label' => 'CLÍNICAS', 'url' => ['/rm-clinica/index']]; // Siempre se muestra la raíz de clínicas
}
if ($clinica && $clinica->id !== null) {
    // Si estamos en el contexto de una clínica, añadirla a las migas de pan
    $this->params['breadcrumbs'][] = ['label' => Html::encode($clinica->nombre), 'url' => ['/rm-clinica/view', 'id' => $clinica->id]];
    $this->params['breadcrumbs'][] = 'AFILIADOS'; // Último elemento como texto
    $this->title = 'Gestión de Afiliados de ' . Html::encode($clinica->nombre); // Título específico
} else {
    // Si no estamos en el contexto de una clínica, miga de pan genérica
    $this->params['breadcrumbs'][] = 'AFILIADOS'; // Último elemento como texto
    $this->title = 'Gestión de Afiliados'; // Título genérico
}

?>

<div class="main-container"> 

<input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />
    
    

<div class="header-section"> 
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="header-buttons-group">
            
            <!-- Botón de Exportación Rápida con ESTILO ÍNDIGO MÁS FUERTE -->
            <?= Html::button(
                '<i class="fas fa-file-excel mr-2"></i> EXPORTAR A CSV (Rápido)', 
                [
                    'id' => 'export-csv-btn', 
                    // CLASES DE COLOR ACTUALIZADAS A ÍNDIGO SÓLIDO Y OSCURO
                    'class' => 'btn-base btn-blue'
                ]
            ) ?>

            <?php if ($permisos) : ?>
                <?= Html::a(
                    '<i class="fas fa-file-excel mr-2"></i> CARGAR MASIVOS DE AFILIADOS', 
                    ['masivo'], 
                    ['class' => 'btn-base btn-blue'] 
                ) ?> 
                <?= Html::a(
                    '<i class="fas fa-plus mr-2"></i> CREAR NUEVO AFILIADO DEL SÍSTEMA', 
                    ['create'], 
                    ['class' => 'btn-base btn-blue'] 
                ) ?> 
            <?php endif; ?>
            

<?php if ($clinica && $clinica->id !== null) : ?>
                <?= Html::a(
                    '<i class="fas fa-undo mr-2"></i> Volver a Clínica', 
                    ['/rm-clinica/view', 'id' => $clinica->id], 
                    [
                        'class' => 'btn-base btn-gray', 
                        'title' => 'Volver a los detalles de la clínica',
                    ]
                ) ?>
            <?php endif; ?>
        </div>
    </div>

    

<div class="ms-panel ms-panel-fh border-indigo"> 

<div class="ms-panel-header">
            <h3 class="section-title">
                <i class="fas fa-users mr-3 text-indigo-600"></i> Listado de Afiliados
            </h3>
        </div>
        <div class="ms-panel-body">
            <div class="table-responsive">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'resizableColumns' => false,
                    'bordered' => false,
                    'responsiveWrap' => false,
                    'persistResize' => false,
                    'filterModel' => $searchModel,
                    // *** Importante: Asignamos un ID a la tabla HTML para que JS la pueda referenciar ***
                    'tableOptions' => ['id' => 'affiliate-table', 'class' => 'min-w-full divide-y divide-gray-200'], 
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'attribute' => 'created_at',
                            'hAlign' => 'center',
                            'vAlign' => 'middle',
                            'label' => 'Fecha Afiliación',
                            'value' => function ($model, $key, $index, $widget) {
                                return !empty($model->created_at) ? Yii::$app->formatter->asDate($model->created_at, 'd/M/Y HH:mm:ss') : '';
                            },
                            'width' => '12%',
                            'filterType' => \kartik\grid\GridView::FILTER_DATE_RANGE,
                            'format' => 'raw',
                            'filterInputOptions' => ['placeholder' => 'Seleccione un rango de fechas', 'class' => 'form-control'],
                            'filterWidgetOptions' => [
                                'presetDropdown' => true,
                                'pluginOptions' => [
                                    'locale' => [
                                        'format' => 'DD/MM/YYYY',
                                        'separator' => ' a ',
                                    ],
                                    'placeholder' => 'Fecha de creación',
                                ],
                                'pluginEvents' => [
                                    "apply.daterangepicker" => "function() { $('.grid-view').yiiGridView('applyFilter') }",
                                ]
                            ],
                        ],
                        [
                            'attribute' => 'user_datos_type_id',
                            'label' => 'Tipo Afiliado',
                            'value' => function ($model) {
                                return $model->userDatosType ? $model->userDatosType->nombre : null;
                            },
                            'filter' => Select2::widget([
                                'model' => $searchModel,
                                'attribute' => 'user_datos_type_id',
                                'data' => app\models\UserDatosType::getList(), 
                                'options' => ['placeholder' => 'Seleccionar tipo'],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ]),
                            'contentOptions' => ['style' => 'width: 150px;'],
                        ],
                        [
                            'label' => 'Nombre Completo', 
                            'attribute' => 'nombres', 
                            'value' => function ($model) {
                                return $model->nombres . ' ' . $model->apellidos;
                            },
                            'format' => 'ntext',
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'filterInputOptions' => [
                                'placeholder' => 'Buscar por nombre',
                                'class' => 'form-control text-center',
                            ],
                        ],
                        [
                            'label' => 'Cédula de Identidad',
                            'attribute' => 'cedula',  
                            'value' => function ($model) {
                                return ($model->tipo_cedula ?? '') . ' ' . ($model->cedula ?? '');
                            },
                            'format' => 'ntext', 
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'options' => ['style' => 'width: 200px;'], 
                            'contentOptions' => ['class' => 'text-center'], 
                            'filterInputOptions' => [
                                'placeholder' => 'Buscar por cédula', 
                                'class' => 'form-control text-center',
                            ],
                        ],
                        'telefono',
                        [
                            'attribute' => 'email',
                            'label' => 'Correo Electrónico', 
                            'format' => 'email', 
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'options' => ['style' => 'width: 300px;'], 
                            'filterInputOptions' => [ 
                                'placeholder' => 'Buscar por correo',
                                'class' => 'form-control text-center',
                            ],
                        ],
                        [
                            'label' => 'Asesor',
                            'format' => 'ntext',
                            'value' => function ($model) {
                                
                                if ($model->asesor && $model->asesor->userDatos) {
                                    $ud = $model->asesor->userDatos;
                                    return trim(($ud->nombres ?? '') . ' ' . ($ud->apellidos ?? '')) ?: null;
                                }
                                return null;
                            },
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'visible' => in_array(\app\components\UserHelper::getMyRol(), ['superadmin','DIRECTOR-COMERCIALIZACIÓN']),
                        ],
                        [
                            'label' => 'Clínica',
                            'format' => 'ntext',
                            'value' => function ($model) {
                                // Muestra el nombre de la clínica a la que pertenece el afiliado
                                return $model->clinica ? $model->clinica->nombre : null;
                            },
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'visible' => in_array(\app\components\UserHelper::getMyRol(), ['superadmin','DIRECTOR-COMERCIALIZACIÓN']),
                        ],

                        [

                            'attribute' => 'contract_status',
                            'label' => 'Estado Contrato',
                            'format' => 'raw',
                            'value' => function($model) {
                                $contract = \app\models\Contratos::find()->where(['user_id' => $model->id])->orderBy(['id' => SORT_DESC])->one();
                                if (!$contract) {
                                    return Html::tag('span', 'Sin contrato', ['class' => 'badge bg-secondary']);
                                }

                                $status = trim((string)($contract->estatus ?? $contract->estado ?? $contract->status ?? 'Desconocido'));
                                $lower = mb_strtolower($status);

                                $statusClass = '';
                                $tooltipText = '';
                                $customStyle = '';
                                $displayText = ''; // New variable for display text

                                if ($lower === 'suspendido') {
                                    $statusClass = 'badge bg-danger';
                                    $tooltipText = 'Contrato suspendido por falta de pago';
                                    $displayText = 'Suspendido';
                                } elseif ($lower === 'creado') {
                                    // AQUA color for "Creado"
                                    $statusClass = 'badge';
                                    $customStyle = 'background-color: #00FFFF; color: #000;';
                                    $tooltipText = 'Contrato recién creado';
                                    $displayText = 'Creado';
                                } elseif (in_array($lower, ['activo','vigente','active','pagado'])) {
                                    $statusClass = 'badge bg-success';
                                    $tooltipText = 'Contrato activo y en buen estado';
                                    $displayText = 'Activo';
                                } elseif (in_array($lower, ['vencido','expirado','expired'])) {
                                    $statusClass = 'badge bg-danger';
                                    $tooltipText = 'Contrato vencido';
                                    $displayText = 'Vencido';
                                } elseif ($lower === 'esperar') {
                                    $statusClass = 'badge bg-warning';
                                    $tooltipText = is_callable([$contract, 'getTooltipEspera']) ? $contract->getTooltipEspera() : 'En período de penalidad';
                                    $displayText = 'Esperar Penalidad'; // Changed from 'Esperar' to 'Esperar Penalidad'
                                } else {
                                    $statusClass = 'badge bg-warning text-dark';
                                    $tooltipText = 'Estado: ' . ucfirst($status);
                                    $displayText = ucfirst($status);
                                }

                                $htmlOptions = [
                                    'class' => trim($statusClass . ' contract-status-tooltip'),
                                    'title' => $tooltipText,
                                    'data-bs-toggle' => 'tooltip',
                                    'data-bs-placement' => 'bottom',
                                    'style' => 'cursor: help;' . ($customStyle ? ' ' . $customStyle : '')
                                ];

                                return Html::tag('span', Html::encode($displayText), $htmlOptions);
                            },
                            'filter' => ['' => 'Todos','Si' => 'Sí', 'No' => 'No'], 
                            'contentOptions' => ['class' => 'text-center', 'style' => 'vertical-align: middle;'],
                            'headerOptions' => ['style' => 'background-color: #337ab7; color: white; font-size: 12px; font-weight: bold; text-align: center;'],
                            'header' => Html::tag('span', 'ESTATUS', ['style' => 'color:#fff;'])
                        ],
                        
                        // Columna de Acciones - CLASE 'exclude-csv' AGREGADA AQUÍ
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'header' => 'ACCIONES',
                            'template' => '<div class="d-flex justify-content-center gap-0">{view}{update}{siniestro}{cita}{pagos}</div>',
                            'options' => ['style' => 'width:55px; min-width:55px;'],
                            'headerOptions' => ['style' => 'color: white!important;', 'class' => 'exclude-csv'], 
                            'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;', 'class' => 'exclude-csv'],
                            'buttons' => [
                                'view' => function ($url, $model, $key) use ($clinica) { // Pasar $clinica
                                    $params = ['view', 'id' => $model->id];
                                    if ($clinica && $clinica->id !== null) {
                                        $params['clinica_id'] = $clinica->id;
                                    }
                                    return Html::a(
                                        '<i class="fa fa-eye"></i>',
                                        Url::to($params), // Asegurar clinica_id condicionalmente
                                        [
                                            'title' => 'Detalle de Usuario',
                                            'class' => 'btn-action view'
                                        ]
                                    );
                                },
                                'update' => function ($url, $model, $key) use ( $clinica, $rol) { // Pasar $permisos y $clinica
                                    if ($rol == 'superadmin' || $rol = 'DIRECTOR-COMERCIALIZACIÓN') {
                                        $params = ['update', 'id' => $model->id];
                                        if ($clinica && $clinica->id !== null) {
                                            $params['clinica_id'] = $clinica->id;
                                        }
                                        return Html::a(
                                            '<i class="fas fa-pencil-alt ms-text-primary"></i>',
                                            Url::to($params), // Asegurar clinica_id condicionalmente
                                            [
                                                'title' => 'Editar Usuario',
                                                'class' => 'btn-action view'
                                            ]
                                        );
                                    }else{
                                        return "";
                                    }
                                },
                                // Botón Siniestros (Modo Siniestro)
                                'siniestro' => function ($url, $model, $key) use ($permisos, $clinica, $rol) { 
                                    if ($permisos == true || $rol == 'COORDINADOR-CLINICA') {
                                        // Manda al index con modo=siniestro (o 0)
                                        $params = ['/sis-siniestro/index', 'user_id' => $model->id, 'modo' => 'siniestro']; // <-- CAMBIO CLAVE
                                        if ($clinica && $clinica->id !== null) {
                                            $params['clinica_id'] = $clinica->id;
                                        }

                                        if ($model->clinica_id) {
                                            return Html::a(
                                                '<i class="fas fa-address-card ms-text-primary"></i>',
                                                Url::to($params),
                                                ['title' => 'Ver Siniestros (Servicios sin plazo)', 'class' => 'btn-action view']
                                            );
                                        }
                                    }
                                },

                                // Nuevo Botón Citas (Modo Cita)
                                'cita' => function ($url, $model, $key) use ($permisos, $clinica, $rol) {
                                    if ($permisos == true || $rol == 'COORDINADOR-CLINICA') {
                                        // Manda al index con modo=cita (o 1)
                                        $params = ['/sis-siniestro/index', 'user_id' => $model->id, 'modo' => 'cita']; // <-- CAMBIO CLAVE
                                        if ($clinica && $clinica->id !== null) {
                                            $params['clinica_id'] = $clinica->id;
                                        }

                                        if ($model->clinica_id) {
                                            return Html::a(
                                                '<i class="fas fa-calendar-alt text-success"></i>', // Icono para cita
                                                Url::to($params),
                                                ['title' => 'Gestionar Citas (Servicios con plazo)', 'class' => 'btn-action view']
                                            );
                                        }
                                    }
                                },
                                'pagos' => function ($url, $model, $key) {
                                    // Si el tipo de afiliado (user_datos_type_id) NO es 2 (Corporativo), muestra el botón.
                                    // user_datos_type_id = 1 (Individual)
                                    if ($model->user_datos_type_id != 2) {
                                        $params = ['/contratos/index', 'user_id' => $model->id];
                                        return Html::a(
                                            '<i class="fas fa-file-invoice-dollar ms-text-primary"></i>',
                                            Url::to($params),
                                            [
                                                'title' => 'Pagos',
                                                'class' => 'btn-action view'
                                            ]
                                        );
                                    }
                                    // Si es tipo 2, la función no devuelve nada, por lo que el botón no se renderiza.
                                    return null; 

                                },
                                'delete' => function ($url, $model, $key) use ($permisos, $clinica) { // Pasar $permisos y $clinica
                                    if ($permisos) {
                                        $params = ['delete', 'id' => $model->id];
                                        if ($clinica && $clinica->id !== null) {
                                            $params['clinica_id'] = $clinica->id;
                                        }
                                        return Html::a(
                                            '<i class="far fa-trash-alt ms-text-danger"></i>',
                                            Url::to($params), // Asegurar clinica_id condicionalmente
                                            [
                                                'title' => 'Eliminar Usuario',
                                                'data-confirm' => '¿Estás seguro de que quieres eliminar esta clínica?',
                                                'data-method' => 'post',
                                                'class' => 'btn-action view'
                                            ]
                                        );
                                    }
                                },
                            ],
                        ],
                    ], // Fin de columns
                ]); ?>
            </div>
        </div>
    </div>
</div>

<?php 

// Código JavaScript de exportación a CSV (pre-minificado y ultra-estable para inyección PHP)
$js_code_stable = "function exportTableToCSV(tableID, filename) {const table = document.getElementById(tableID);if (!table) {console.error('Error: Tabla con ID ' + tableID + ' no encontrada.');return;}let csv = [];const rows = table.querySelectorAll('tr');for (let i = 0; i < rows.length; i++) {const row = rows[i];const cols = row.querySelectorAll('th:not(.exclude-csv), td:not(.exclude-csv)');let rowData = [];for (let j = 0; j < cols.length; j++) {let data = cols[j].innerText.trim();data = data.replace(new RegExp('\"', 'g'), '\"\"');if (data.includes(';') || data.includes('\"')) {data = '\"' + data + '\"';}rowData.push(data);}csv.push(rowData.join(';'));}const csvFile = csv.join('\\n');const BOM = '\\uFEFF';const blob = new Blob([BOM + csvFile], {type: 'text/csv;charset=utf-8;'});const link = document.createElement(\"a\");if (link.download !== undefined) {const url = URL.createObjectURL(blob);link.setAttribute(\"href\", url);link.setAttribute(\"download\", filename);link.style.visibility = 'hidden';document.body.appendChild(link);link.click();document.body.removeChild(link);}} $(document).ready(function() { $('#export-csv-btn').on('click', function() { exportTableToCSV('affiliate-table', 'Reporte_Afiliados.csv'); }); });";

$this->registerJs($js_code_stable, \yii\web\View::POS_END);
?>
<style>
/* Improved tooltip styling */
.custom-tooltip .tooltip-inner {
    background-color: #2d3748;
    color: #fff;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 13px;
    font-weight: 500;
    max-width: 280px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border: 1px solid #4a5568;
    line-height: 1.4;
    z-index: 9999; /* Ensure it's above everything */
}

/* Arrow styling for all directions */
.custom-tooltip.bs-tooltip-top .tooltip-arrow::before {
    border-top-color: #2d3748;
    bottom: 1px;
}

.custom-tooltip.bs-tooltip-bottom .tooltip-arrow::before {
    border-bottom-color: #2d3748;
    top: 1px;
}

.custom-tooltip.bs-tooltip-start .tooltip-arrow::before {
    border-left-color: #2d3748;
    right: 1px;
}

.custom-tooltip.bs-tooltip-end .tooltip-arrow::before {
    border-right-color: #2d3748;
    left: 1px;
}

/* Ensure tooltip has highest z-index */
.tooltip {
    z-index: 99999 !important;
}

/* Contract status badge styling */
.contract-status-tooltip {
    cursor: help;
    border-bottom: 1px dotted #666;
    transition: all 0.2s ease;
    position: relative;
}

.contract-status-tooltip:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

/* Specific styling for waiting status */
.badge.bg-warning.contract-status-tooltip {
    border: 1px solid #eab308;
    background: linear-gradient(135deg, #fef08a, #facc15) !important;
    color: #854d0e !important;
    font-weight: 600;
}

/* Specific styling for "Creado" status with aqua color */
.badge[style*="background-color: #00FFFF"] {
    border: 1px solid #00b7eb !important;
    background: linear-gradient(135deg, #00FFFF, #00b7eb) !important;
    color: #006d84 !important;
    font-weight: 600;
}

/* Ensure tooltips appear above all other content */
.tooltip.show {
    opacity: 1 !important;
}

/* Add a small animation for tooltip appearance */
.tooltip.fade {
    transition: opacity 0.15s ease-in-out;
}
</style>

<?php
// JavaScript for tooltip initialization with custom template
$js = <<<JS
// Improved tooltip initialization with better positioning
function initContractTooltips() {
    $('.contract-status-tooltip').tooltip({
        placement: 'bottom', // Show below to avoid cursor
        trigger: 'hover',
        delay: { "show": 100, "hide": 100 }, // Small delay to prevent flickering
        container: 'body',
        boundary: 'viewport',
        template: '<div class="tooltip custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
        offset: [0, 10] // 10px gap from element
    });
    
    // Custom positioning logic for better visibility
    $(document).on('show.bs.tooltip', function (e) {
        var \$tooltip = $(e.target);
        var tooltipId = \$tooltip.attr('aria-describedby');
        var \$tooltipElement = $('#' + tooltipId);
        
        // Ensure the tooltip has our custom class
        if (!\$tooltipElement.hasClass('custom-tooltip')) {
            \$tooltipElement.addClass('custom-tooltip');
        }
        
        // Additional positioning to ensure it's visible
        setTimeout(function() {
            if (\$tooltipElement.is(':visible')) {
                var tooltipRect = \$tooltipElement[0].getBoundingClientRect();
                var viewportHeight = window.innerHeight;
                
                // If tooltip is near bottom of viewport, adjust position
                if (tooltipRect.bottom > viewportHeight - 10) {
                    \$tooltipElement.css({
                        'top': (parseInt(\$tooltipElement.css('top')) - 20) + 'px'
                    });
                }
            }
        }, 50);
    });
}

$(document).ready(function() {
    initContractTooltips();
});

// Re-initialize after PJAX (for GridView filters, pagination, sorting)
$(document).on('pjax:success', function() {
    setTimeout(initContractTooltips, 100);
});

// Also re-initialize when the window is resized
$(window).on('resize', function() {
    setTimeout(initContractTooltips, 50);
});
JS;

$this->registerJs($js);
?>
