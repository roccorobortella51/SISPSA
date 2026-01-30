<?php

use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\select2\Select2;
use app\components\UserHelper;
use app\models\RmClinica; // Importar el modelo de la clínica
use app\models\Contratos;
use app\components\ContractHelper;
use yii\web\View; // <--- AÑADIDO: Importación para el registro de scripts en el DOM

// Helper function for contract status classes
function getContractStatusClass($status)
{
    $status = strtolower($status);
    $classes = [
        'registrado' => 'badge badge-primary',
        'activo' => 'badge badge-success',
        'anulado' => 'badge badge-danger',
        'vencido' => 'badge badge-warning',
        'pendiente' => 'badge badge-info',
        'suspendido' => 'badge badge-secondary',
    ];

    return $classes[$status] ?? 'badge badge-light';
}

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
if ($permisos == true) {
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

// Define admin roles for clinic search filter
$isAdmin = ($rol == 'superadmin' || $rol == 'DIRECTOR-COMERCIALIZACIÓN');

?>

<div class="main-container"> <input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />

    <div class="ms-panel-header d-flex justify-content-between align-items-center p-4">
        <h1 class="font-weight-bold" style="font-size: 2.5rem; margin: 0; line-height: 1;">
            <?= Html::encode($this->title); ?>
        </h1>

        <div class="d-flex flex-wrap gap-4 align-items-center justify-content-end user-datos-btn-group">
            <?php if ($permisos) : ?>
                <?= Html::a(
                    '<i class="fas fa-plus fa-lg me-3"></i> <span class="btn-label">CREAR NUEVO AFILIADO DEL SÍSTEMA</span>',
                    ['create'],
                    [
                        'class' => 'btn btn-success btn-lg fw-bold shadow user-datos-btn',
                        'style' => 'font-size:1.35rem; padding: 20px 32px; border-radius: 1.5rem; letter-spacing:0.5px; display:flex; align-items:center; gap:18px; min-width:340px;'
                    ]
                ) ?>
                <?= Html::a(
                    '<i class="fas fa-file-excel fa-lg me-3"></i> <span class="btn-label">CARGAR MASIVOS DE AFILIADOS</span>',
                    ['masivo'],
                    [
                        'class' => 'btn btn-primary btn-lg fw-bold shadow user-datos-btn',
                        'style' => 'font-size:1.35rem; padding: 20px 32px; border-radius: 1.5rem; letter-spacing:0.5px; display:flex; align-items:center; gap:18px; min-width:340px;'
                    ]
                ) ?>
            <?php endif; ?>
            <?php if ($clinica && $clinica->id !== null) : ?>
                <?= Html::a(
                    '<i class="fas fa-undo fa-lg me-3"></i> <span class="btn-label">Volver a Clínica</span>',
                    ['/rm-clinica/view', 'id' => $clinica->id],
                    [
                        'class' => 'btn btn-secondary btn-lg fw-bold shadow user-datos-btn',
                        'style' => 'font-size:1.35rem; padding: 20px 32px; border-radius: 1.5rem; letter-spacing:0.5px; display:flex; align-items:center; gap:18px; min-width:280px;',
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
                                'options' => ['placeholder' => 'Filtrar'],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ]),
                            'contentOptions' => ['style' => 'width: 150px;'],
                        ],
                        // Clínica search filter - ONLY for admin roles
                        [
                            'attribute' => 'clinica_id',
                            'label' => 'Clínica',
                            'value' => function ($model) {
                                return $model->clinica ? $model->clinica->nombre : 'No asignada';
                            },
                            'filter' => $isAdmin ? Select2::widget([
                                'model' => $searchModel,
                                'attribute' => 'clinica_id',
                                'data' => \yii\helpers\ArrayHelper::map(
                                    \app\models\RmClinica::find()->orderBy('nombre')->all(),
                                    'id',
                                    'nombre'
                                ),
                                'options' => ['placeholder' => 'Filtrar por Clínica'],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ]) : null,
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'contentOptions' => ['class' => 'text-center'],
                            'visible' => $isAdmin, // Only show for admin roles
                        ],
                        // CORRECTED COLUMN: Corporativo Name
                        [
                            'attribute' => 'afiliado_corporativo_id',
                            'label' => 'Corporativo',
                            'value' => function ($model) {
                                // Only show corporativo name for corporativo affiliates (type_id = 2)
                                if ($model->user_datos_type_id == 2 && $model->corporativo) {
                                    return $model->corporativo->nombre;
                                }
                                return null; // Or empty string for individual affiliates
                            },
                            'filter' => \kartik\select2\Select2::widget([
                                'model' => $searchModel,
                                'attribute' => 'afiliado_corporativo_id',
                                'data' => \yii\helpers\ArrayHelper::map(
                                    \app\models\Corporativo::find()->orderBy('nombre')->all(),
                                    'id',
                                    'nombre'
                                ),
                                'options' => [
                                    'placeholder' => 'Seleccionar corporativo',
                                    'class' => 'form-control'
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ]),
                            'contentOptions' => function ($model) {
                                // Style differently for corporativo affiliates
                                if ($model->user_datos_type_id == 2) {
                                    return ['class' => 'corporativo-affiliate'];
                                }
                                return [];
                            },
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
                                // Inicializar variables
                                $tipoCedula = $model->tipo_cedula ?? '';
                                $numeroCedula = $model->cedula ?? '';
                                $idAfiliado = $model->id ?? '';

                                // DEBUG
                                echo "<script>console.log('ID: $idAfiliado | Tipo: \"$tipoCedula\" | Cédula: $numeroCedula | Consecutivo: " . ($model->consecutivo_menor ?? 'NULL') . "');</script>";

                                // Obtener consecutivo
                                $consecutivo = '';
                                if (isset($model->consecutivo_menor) && $model->consecutivo_menor !== null && $model->consecutivo_menor !== '') {
                                    $consecutivo = str_pad((int)$model->consecutivo_menor, 2, '0', STR_PAD_LEFT);
                                }

                                // ======================= CASO ESPECIAL: "Menor Sin Cédula" =======================
                                if ($tipoCedula === 'Menor Sin Cédula') {


                                    if (!empty($consecutivo)) {
                                        return '
                                <div class="cedula-grande-container menor-sin-cedula">
                                    <div class="cedula-grande-text">
                                    SIN CÉDULA-' . $numeroCedula . '-<span class="consecutivo-grande">' . $consecutivo . '</span>
                                    </div>
                                </div>';
                                    } else {
                                        return '
                                <div class="cedula-grande-container menor-sin-cedula">
                                    <div class="cedula-grande-text">
                                    SIN CÉDULA-' . $numeroCedula . '
                                    </div>
                                </div>';
                                    }
                                }

                                // ======================= CASO 2: CÉDULA NORMAL (V, E, J, P, N, G, M) =======================
                                if (!empty($numeroCedula) && !empty($tipoCedula) && $tipoCedula !== 'Menor Sin Cédula') {
                                    // Determinar si es menor de edad
                                    $esMenorEdad = false;
                                    $edad = null;
                                    if (!empty($model->fechanac)) {
                                        try {
                                            $fechaNac = new DateTime($model->fechanac);
                                            $hoy = new DateTime();
                                            $edad = $fechaNac->diff($hoy)->y;
                                            $esMenorEdad = ($edad < 18);
                                        } catch (\Exception $e) {
                                        }
                                    }

                                    return '
                                <div class="cedula-grande-container cedula-normal">
                                    <div class="cedula-grande-text">
                                        ' . $tipoCedula . '-' . $numeroCedula . '
                                    </div>
                                    ' . ($esMenorEdad ? '<div class="edad-etiqueta">MENOR (' . $edad . ' años)</div>' : '') . '
                                </div>';
                                }

                                // ======================= CASO 3: TIPO SIN NÚMERO =======================
                                if (!empty($tipoCedula) && empty($numeroCedula) && $tipoCedula !== 'Menor Sin Cédula') {
                                    return '
                                <div class="cedula-grande-container cedula-pendiente">
                                    <div class="cedula-grande-text">
                                        ' . $tipoCedula . '
                                    </div>
                                    <div class="estado-etiqueta">PENDIENTE DE NÚMERO</div>
                                </div>';
                                }

                                // ======================= CASO 4: SIN INFORMACIÓN =======================
                                return '
                            <div class="cedula-grande-container sin-cedula">
                                <div class="cedula-grande-text">
                                    SIN REGISTRO
                                </div>
                            </div>';
                            },
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'options' => ['style' => 'width: 280px; min-width: 280px;'],
                            'contentOptions' => function ($model) {
                                $tipo = $model->tipo_cedula ?? '';

                                if ($tipo === 'Menor Sin Cédula') {
                                    return ['class' => 'text-center bg-menor-sin-cedula'];
                                }

                                if (!empty($model->cedula)) {
                                    return ['class' => 'text-center bg-cedula-normal'];
                                }

                                return ['class' => 'text-center'];
                            },
                            'filterInputOptions' => [
                                'placeholder' => 'Buscar por cédula o tipo',
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
                            'visible' => $isAdmin, // Only show for admin roles
                        ],

                        // CORRECTED COLUMN: Contract Status with Detailed Tooltips
                        // CORRECTED COLUMN: Contract Status with Detailed Tooltips and Filter
                        [
                            'label' => 'Estatus Contrato',
                            'format' => 'raw',
                            'value' => function ($model) {
                                return ContractHelper::generateContractStatusBadge($model);
                            },
                            'attribute' => 'contrato_estatus', // Add this line for filtering
                            'filter' => \kartik\select2\Select2::widget([
                                'model' => $searchModel,
                                'attribute' => 'contrato_estatus',
                                'data' => array_merge(
                                    Contratos::getStatusOptions(),
                                    ['sin_contrato' => 'Sin Contrato']
                                ),
                                'options' => [
                                    'placeholder' => 'Filtrar por estatus',
                                    'class' => 'form-control'
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ]),
                            'contentOptions' => function ($model) {
                                return ContractHelper::getContractStatusCellClasses($model);
                            },
                            'headerOptions' => [
                                'style' => 'color: white!important;',
                                'title' => 'Estado actual del contrato del afiliado. Pase el cursor sobre cada estado para más detalles.',
                                'data-toggle' => 'tooltip',
                                'data-placement' => 'top'
                            ],
                            'filterInputOptions' => [
                                'class' => 'form-control',
                                'prompt' => 'Todos'
                            ],
                        ],

                        [
                            'class' => 'yii\grid\ActionColumn',
                            'header' => 'ACCIONES',
                            // MODIFICADO: Se usa {atencion} en lugar de {siniestro} y {cita}
                            'template' => '<div class="d-flex justify-content-center gap-0">{view}{update}{atencion}{pagos}</div>',
                            'options' => ['style' => 'width:55px; min-width:55px;'],
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
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
                                'update' => function ($url, $model, $key) use ($clinica, $rol) { // Pasar $permisos y $clinica
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
                                    } else {
                                        return "";
                                    }
                                },
                                // BOTÓN DE ATENCION
                                'atencion' => function ($url, $model, $key) use ($permisos, $clinica, $rol) {
                                    if (($permisos == true || $rol == 'COORDINADOR-CLINICA' || $rol == 'CONTROL DE CITAS') && $model->clinica_id) {

                                        // URLs para pasar a la función JS
                                        $urlSiniestro = Url::to(['/sis-siniestro/index', 'user_id' => $model->id, 'modo' => 'siniestro', 'clinica_id' => $clinica ? $clinica->id : null]);
                                        $urlCita = Url::to(['/sis-siniestro/index', 'user_id' => $model->id, 'modo' => 'cita', 'clinica_id' => $clinica ? $clinica->id : null]);

                                        // Botón que usa DATA ATTRIBUTES para las URLs
                                        return Html::a(
                                            // --- CÓDIGO DEL ÍCONO MODIFICADO (Font Awesome Heart) ---
                                            '<i class="fas fa-heartbeat" style="color: red;"></i>',
                                            // --------------------------------------------------------
                                            '#', // URL vacía, la acción será manejada por JS
                                            [
                                                'title' => 'Gestionar Atención',
                                                'class' => 'btn-action view atencion-btn',
                                                'data' => [
                                                    'url-siniestro' => $urlSiniestro,
                                                    'url-cita' => $urlCita,
                                                ],
                                            ]
                                        );
                                    }
                                    return "";
                                },
                                'pagos' => function ($url, $model, $key) {
                                    // Show for both Individual (1) AND Corporativo (2)
                                    if ($model->user_datos_type_id == 1 || $model->user_datos_type_id == 2) {
                                        $params = ['/contratos/index', 'user_id' => $model->id];
                                        return Html::a(
                                            '<i class="fas fa-file-invoice-dollar ms-text-success"></i>',
                                            Url::to($params),
                                            [
                                                'title' => 'Pagos',
                                                'class' => 'btn-action view'
                                            ]
                                        );
                                    }
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
$js = <<<'JS'
// 1. EVENTO PARA ABRIR EL MODAL (keep this as-is)
$(document).on('click', '.atencion-btn', function(e) {
    e.preventDefault(); 
    
    var urlSiniestro = $(this).data('url-siniestro');
    var urlCita = $(this).data('url-cita');

    var contentHtml = 
        '<p class="text-xl mt-4 mb-5 font-weight-bold">¿Desea registrar una Atención o programar una Cita?</p>' +
        '<div class="d-flex justify-content-center w-100 mt-5">' +
            '<a href="' + urlSiniestro + '" class="btn-base btn-danger btn-lg mx-4 px-5 py-4" style="color: white; text-decoration: none; font-size: 1.5rem; min-width: 250px;">' +
                '<i class="fas fa-hand-holding-medical me-3"></i> ATENCION' +
            '</a>' +
            '<a href="' + urlCita + '" class="btn-base btn-success btn-lg mx-4 px-5 py-4" style="color: white; text-decoration: none; font-size: 1.5rem; min-width: 250px;">' +
                '<i class="fas fa-calendar-check me-3"></i> CITA' +
            '</a>' +
        '</div>';

    var footerHtml = 
        '<button type="button" class="btn-base btn-gray btn-lg mt-4 btn-cerrar-swal">' +
            'CERRAR' +
        '</button>';

    Swal.fire({
        title: 'Selecciona una Opción',
        icon: 'question',
        showCloseButton: true,
        width: '50%',       
        padding: '2em',     
        showConfirmButton: false, 
        showDenyButton: false,     
        showCancelButton: false,    
        buttonsStyling: false, 
        html: contentHtml,
        footer: footerHtml 
    });
});

// 2. EVENTO GLOBAL PARA CERRAR EL MODAL (keep this as-is)
$(document).on('click', '.btn-cerrar-swal', function(e) {
    e.preventDefault();
    Swal.close();
});

// 3. FIX 1: ROBUST TOOLTIP MANAGEMENT SYSTEM
$(document).ready(function() {
    
    // Function to initialize all tooltips
    function initAllTooltips() {
        // Dispose all existing tooltips first
        $('[data-toggle="tooltip"]').tooltip('dispose');
        
        // Find all elements that need tooltips
        var tooltipElements = $('[data-toggle="tooltip"]');
        console.log('Found ' + tooltipElements.length + ' elements requiring tooltips');
        
        // Initialize with error handling
        try {
            tooltipElements.tooltip({
                trigger: 'hover',
                delay: { "show": 100, "hide": 100 },
                container: 'body',
                boundary: 'window',
                html: true // Important for HTML tooltips
            });
            console.log('✓ Successfully initialized ' + tooltipElements.length + ' tooltips');
        } catch (error) {
            console.error('✗ Error initializing tooltips:', error);
        }
        
        // Debug: Log first few tooltips
        tooltipElements.slice(0, 5).each(function(index) {
            var title = $(this).attr('title');
            console.log('Tooltip ' + (index + 1) + ': ' + (title ? 'Has title (' + title.length + ' chars)' : 'NO TITLE'));
        });
    }
    
    // Initialize on page load
    console.log('Page loaded - initializing tooltips');
    initAllTooltips();
    
    // Re-initialize on PJAX complete (for GridView pagination/filtering)
    $(document).on('pjax:complete', function(event, xhr, options) {
        console.log('PJAX complete - reinitializing tooltips');
        setTimeout(initAllTooltips, 100); // Small delay to ensure DOM is ready
    });
    
    // Also re-initialize when GridView updates (alternative event)
    $(document).on('yiiGridViewUpdated', function(event) {
        console.log('GridView updated - reinitializing tooltips');
        setTimeout(initAllTooltips, 100);
    });
    
    // Re-initialize on window resize (sometimes helps)
    $(window).on('resize', function() {
        // Only reinit if tooltips seem broken
        var brokenTooltips = $('[data-toggle="tooltip"]').filter(function() {
            return $(this).data('bs.tooltip') === undefined;
        });
        if (brokenTooltips.length > 0) {
            console.log('Found ' + brokenTooltips.length + ' broken tooltips after resize');
            initAllTooltips();
        }
    });
    
    // Add click-to-copy for contract numbers in tooltips
    $(document).on('click', '.contract-tooltip strong', function(e) {
        e.stopPropagation();
        
        var text = $(this).text();
        if (text.includes('Contrato #') || text.includes('Número:')) {
            // Extract contract number
            var contractNum = text.replace('Contrato #', '').replace('Número: ', '').trim();
            
            // Copy to clipboard
            navigator.clipboard.writeText(contractNum).then(function() {
                // Show copied notification
                var badgeElement = $(e.target).closest('[data-toggle="tooltip"]');
                var originalText = badgeElement.text();
                var originalTitle = badgeElement.attr('title');
                
                badgeElement.text('✓ Copiado!');
                badgeElement.attr('title', 'Número de contrato copiado: ' + contractNum);
                badgeElement.tooltip('update').tooltip('show');
                
                setTimeout(function() {
                    badgeElement.text(originalText);
                    badgeElement.attr('title', originalTitle);
                    badgeElement.tooltip('update');
                }, 1500);
            });
        }
    });
    
    // Highlight important statuses (optional visual enhancement)
    function highlightImportantStatuses() {
        $('.badge-danger, .badge-warning, .badge-secondary').each(function() {
            if (!$(this).hasClass('highlighted')) {
                $(this).addClass('highlighted');
                $(this).css({
                    'animation': 'pulse 2s infinite',
                    'border': '2px solid rgba(255,255,255,0.3)'
                });
            }
        });
    }
    
    // Initial highlight
    highlightImportantStatuses();
    
    // Re-highlight after grid updates
    $(document).on('pjax:complete', function() {
        setTimeout(highlightImportantStatuses, 150);
    });
    
    $(document).on('yiiGridViewUpdated', function() {
        setTimeout(highlightImportantStatuses, 150);
    });
    
    // Force tooltip check every 2 seconds (fallback for missed events)
    setInterval(function() {
        var elementsWithoutTooltips = $('[data-toggle="tooltip"]').filter(function() {
            return $(this).data('bs.tooltip') === undefined;
        });
        
        if (elementsWithoutTooltips.length > 0) {
            console.log('Fallback: Found ' + elementsWithoutTooltips.length + ' elements without initialized tooltips');
            initAllTooltips();
        }
    }, 2000);
});

// 4. Add status explanation modal on badge click (optional - keep if you want this feature)
$(document).on('click', '[data-toggle="tooltip"].badge', function(e) {
    if (e.ctrlKey || e.metaKey) {
        e.preventDefault();
        var title = $(this).attr('title');
        
        // Remove HTML tags for modal display
        var plainText = title.replace(/<[^>]*>/g, '');
        
        // Show in console for debugging
        console.log('Contract Status Details:', plainText);
    }
});
JS;

$this->registerJs($js, View::POS_READY);
?>
<style>
    /* ESTILOS PARA CÉDULAS - BOOTSTRAP 4 */
    /* Contenedor principal para todas las cédulas */
    .cedula-grande-container {
        text-align: center;
        padding: 10px 5px;
        margin: 0;
    }

    /* Texto principal de cédula - FUENTE GRANDE PARA TODOS */
    .cedula-grande-text {
        font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        font-size: 1.2rem !important;
        /* 20% más grande que el tamaño base */
        font-weight: 600;
        line-height: 1.3;
        margin-bottom: 4px;
        letter-spacing: 0.3px;
    }

    /* Especial: Menor sin cédula - aún más grande */
    .menor-sin-cedula .cedula-grande-text {
        font-size: 1.3rem !important;
        /* 30% más grande */
        font-weight: 700;
        color: #e67e22;
        font-family: 'Courier New', Consolas, monospace;
        background-color: rgba(255, 152, 0, 0.1);
        border: 2px solid rgba(255, 152, 0, 0.2);
        border-radius: 8px;
        padding: 10px 15px;
        display: inline-block;
    }

    /* Consecutivo dentro de menor sin cédula - MUY GRANDE */
    .menor-sin-cedula .consecutivo-grande {
        font-size: 1.4rem !important;
        font-weight: 900;
        color: #e74c3c;
        background-color: rgba(231, 76, 60, 0.15);
        padding: 0 6px;
        border-radius: 5px;
        margin: 0 3px;
    }

    /* Menor con cédula */
    .menor-con-cedula .cedula-grande-text {
        color: #f39c12;
        background-color: rgba(243, 156, 18, 0.1);
        border: 2px solid rgba(243, 156, 18, 0.2);
        border-radius: 6px;
        padding: 8px 12px;
        display: inline-block;
    }

    /* Cédula normal (V, E, J, P, N, G) */
    .cedula-normal .cedula-grande-text {
        color: #2c3e50;
        background-color: rgba(52, 152, 219, 0.1);
        border: 2px solid rgba(52, 152, 219, 0.2);
        border-radius: 6px;
        padding: 8px 12px;
        display: inline-block;
    }

    /* Cédula pendiente */
    .cedula-pendiente .cedula-grande-text {
        color: #7f8c8d;
        background-color: rgba(127, 140, 141, 0.1);
        border: 2px dashed rgba(127, 140, 141, 0.3);
        border-radius: 6px;
        padding: 8px 12px;
        display: inline-block;
    }

    /* Sin cédula */
    .sin-cedula .cedula-grande-text {
        color: #e74c3c;
        background-color: rgba(231, 76, 60, 0.1);
        border: 2px solid rgba(231, 76, 60, 0.2);
        border-radius: 6px;
        padding: 8px 12px;
        display: inline-block;
    }

    /* Etiquetas de edad y estado */
    .edad-etiqueta,
    .estado-etiqueta {
        font-size: 0.85rem;
        color: #7f8c8d;
        background-color: rgba(127, 140, 141, 0.1);
        padding: 4px 10px;
        border-radius: 12px;
        display: inline-block;
        margin-top: 5px;
        font-style: italic;
    }

    /* Fondos para celdas de la tabla */
    .bg-menor-sin-cedula {
        background-color: rgba(255, 193, 7, 0.08) !important;
    }

    .bg-menor-con-cedula {
        background-color: rgba(243, 156, 18, 0.05) !important;
    }

    .bg-cedula-normal {
        background-color: rgba(52, 152, 219, 0.03) !important;
    }

    /* Efectos hover */
    tbody tr:hover .bg-menor-sin-cedula {
        background-color: rgba(255, 193, 7, 0.12) !important;
    }

    tbody tr:hover .bg-menor-con-cedula {
        background-color: rgba(243, 156, 18, 0.08) !important;
    }

    tbody tr:hover .bg-cedula-normal {
        background-color: rgba(52, 152, 219, 0.06) !important;
    }

    /* Asegurar que los textos sean visibles */
    .cedula-grande-text {
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
    }

    /* Responsive para pantallas más pequeñas */
    @media (max-width: 768px) {
        .cedula-grande-text {
            font-size: 1.1rem !important;
        }

        .menor-sin-cedula .cedula-grande-text {
            font-size: 1.2rem !important;
            padding: 8px 10px;
        }

        .menor-sin-cedula .consecutivo-grande {
            font-size: 1.3rem !important;
        }
    }

    /* Para impresión */
    @media print {
        .cedula-grande-text {
            font-size: 11pt !important;
            color: #000 !important;
            background-color: transparent !important;
            border: 1px solid #ccc !important;
        }
    }

    /* ESTILOS PARA BADGES DE ESTATUS DE CONTRATO */
    .badge {
        display: inline-block;
        padding: 0.35em 0.65em;
        font-size: 0.85em;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.375rem;
        transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out,
            border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .badge-primary {
        color: #fff;
        background-color: #007bff;
    }

    .badge-success {
        color: #fff;
        background-color: #28a745;
    }

    .badge-danger {
        color: #fff;
        background-color: #dc3545;
    }

    .badge-warning {
        color: #212529;
        background-color: #ffc107;
    }

    .badge-info {
        color: #fff;
        background-color: #17a2b8;
    }

    .badge-secondary {
        color: #fff;
        background-color: #6c757d;
    }

    .badge-light {
        color: #212529;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
    }

    /* Estilos para badges en hover */
    .badge:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Animación sutil para cambios de estado */
    @keyframes statusChange {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    .badge.badge-success,
    .badge.badge-primary {
        animation: statusChange 0.5s ease-in-out;
    }

    /* Tooltip para badges */
    .badge[title]:hover:after {
        content: attr(title);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background-color: #333;
        color: #fff;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 1000;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .badge {
            font-size: 0.75em;
            padding: 0.25em 0.5em;
        }
    }

    /* Enhanced Contract Status Column */
    .contract-status-cell {
        font-weight: 600;
    }

    .contract-status-cell.activo {
        background-color: rgba(40, 167, 69, 0.05) !important;
    }

    .contract-status-cell.vencido,
    .contract-status-cell.suspendido,
    .contract-status-cell.anulado {
        background-color: rgba(220, 53, 69, 0.05) !important;
    }

    .contract-status-cell.registrado {
        background-color: rgba(0, 123, 255, 0.05) !important;
    }

    .contract-status-cell.pendiente {
        background-color: rgba(23, 162, 184, 0.05) !important;
    }

    /* Highlight active contracts */
    tbody tr:hover .contract-status-cell.activo {
        background-color: rgba(40, 167, 69, 0.1) !important;
    }

    /* Professional badge styling */
    .badge {
        min-width: 80px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border: 1px solid transparent;
    }

    .badge-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border-color: #1e7e34;
    }

    .badge-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        border-color: #bd2130;
    }

    .badge-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        border-color: #d39e00;
    }

    .badge-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border-color: #0062cc;
    }

    .badge-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        border-color: #117a8b;
    }

    .badge-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
        border-color: #4e555b;
    }

    /* Status indicators */
    .status-indicator {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 6px;
        vertical-align: middle;
    }

    .status-active {
        background-color: #28a745;
    }

    .status-inactive {
        background-color: #dc3545;
    }

    .status-pending {
        background-color: #ffc107;
    }

    /* Enhanced Tooltip Styling */
    .contract-tooltip {
        max-width: 300px;
        text-align: left;
        font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }

    .tooltip {
        font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }

    .tooltip-inner {
        max-width: 350px;
        padding: 12px;
        background-color: #fff;
        color: #333;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        text-align: left;
        font-size: 13px;
        line-height: 1.5;
    }

    .tooltip.bs-tooltip-top .arrow::before {
        border-top-color: #ddd;
    }

    .tooltip.bs-tooltip-bottom .arrow::before {
        border-bottom-color: #ddd;
    }

    .tooltip.bs-tooltip-left .arrow::before {
        border-left-color: #ddd;
    }

    .tooltip.bs-tooltip-right .arrow::before {
        border-right-color: #ddd;
    }

    /* Badge with tooltip enhancement */
    .badge[data-toggle="tooltip"] {
        transition: all 0.2s ease;
        position: relative;
    }

    .badge[data-toggle="tooltip"]:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    /* Status-specific hover effects */
    .badge-success[data-toggle="tooltip"]:hover {
        background: linear-gradient(135deg, #28a745 0%, #218838 100%) !important;
    }

    .badge-danger[data-toggle="tooltip"]:hover {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
    }

    .badge-warning[data-toggle="tooltip"]:hover {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%) !important;
    }

    .badge-primary[data-toggle="tooltip"]:hover {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
    }

    .badge-info[data-toggle="tooltip"]:hover {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
    }

    .badge-secondary[data-toggle="tooltip"]:hover {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%) !important;
    }

    /* Animation for status changes */
    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    .badge[data-toggle="tooltip"]:hover {
        animation: pulse 0.6s ease-in-out;
    }

    /* Status icons in badges */
    .badge .status-icon {
        font-size: 0.9em;
        margin-right: 4px;
        vertical-align: middle;
    }

    /* Special styling for "Sin Contrato" */
    .badge-light[data-toggle="tooltip"] {
        border: 1px dashed #ccc;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        color: #6c757d;
    }

    .badge-light[data-toggle="tooltip"]:hover {
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        border-color: #adb5bd;
    }

    /* Status indicator dots */
    .status-indicator-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 6px;
        vertical-align: middle;
        animation: pulse-dot 2s infinite;
    }

    @keyframes pulse-dot {
        0% {
            transform: scale(1);
            opacity: 1;
        }

        50% {
            transform: scale(1.2);
            opacity: 0.8;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .status-dot-active {
        background-color: #28a745;
    }

    .status-dot-inactive {
        background-color: #dc3545;
    }

    .status-dot-pending {
        background-color: #ffc107;
    }

    .status-dot-warning {
        background-color: #fd7e14;
    }

    .status-dot-info {
        background-color: #17a2b8;
    }

    /* Quick action buttons in tooltip (optional) */
    .tooltip-quick-actions {
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px dashed #dee2e6;
    }

    .tooltip-quick-actions button {
        font-size: 11px;
        padding: 2px 6px;
        margin: 2px;
        border-radius: 3px;
    }

    /* Responsive adjustments for tooltips */
    @media (max-width: 768px) {
        .tooltip-inner {
            max-width: 280px;
            font-size: 12px;
            padding: 8px;
        }

        .badge {
            font-size: 0.7em;
            padding: 0.2em 0.4em;
            min-width: 70px;
        }
    }
</style>