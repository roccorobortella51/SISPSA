<?php

use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\select2\Select2;
use app\components\UserHelper;
use app\models\RmClinica;
use app\models\Contratos;
use app\components\ContractHelper;
use yii\web\View;

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
/** @var string|null $clinica_id */

// --- Detección y Carga de Clínica (para contexto) ---
$clinica = null;
$clinica_id_param = Yii::$app->request->get('clinica_id');

if (!empty($clinica_id_param)) {
    $clinica = RmClinica::findOne((int)$clinica_id_param);
    if (!$clinica) {
        $clinica = (object)['id' => (int)$clinica_id_param, 'nombre' => 'Clínica Desconocida'];
    }
}

$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol == 'DIRECTOR-COMERCIALIZACIÓN' || $rol == 'Asesor' || $rol == 'Agente' || $rol == "ADMISIÓN" || $rol == "COORDINADOR-CLINICA");

// --- BREADCRUMBS CONDICIONALES ---
if ($permisos == true) {
    $this->params['breadcrumbs'][] = ['label' => 'CLÍNICAS', 'url' => ['/rm-clinica/index']];
}
if ($clinica && $clinica->id !== null) {
    $this->params['breadcrumbs'][] = ['label' => Html::encode($clinica->nombre), 'url' => ['/rm-clinica/view', 'id' => $clinica->id]];
    $this->params['breadcrumbs'][] = 'AFILIADOS';
    $this->title = 'Gestión de Afiliados de ' . Html::encode($clinica->nombre);
} else {
    $this->params['breadcrumbs'][] = 'AFILIADOS';
    $this->title = 'Gestión de Afiliados';
}

// Define admin roles for clinic search filter
$isAdmin = ($rol == 'superadmin' || $rol == 'DIRECTOR-COMERCIALIZACIÓN');
$rolesSinFiltroClinica = ['GERENTE-CLINICA', 'Administrador-clinica', 'CONTROL DE CITAS', 'ADMISIÓN', 'ATENCIÓN', 'COORDINADOR-CLINICA'];
$mostrarFiltroClinica = $isAdmin && !in_array($rol, $rolesSinFiltroClinica);

// Define roles that can access Atención Médica
$rolesAtencionMedica = ['superadmin', 'DIRECTOR-COMERCIALIZACIÓN', 'COORDINADOR-CLINICA', 'CONTROL DE CITAS', 'GERENTE-CLINICA', 'ADMISIÓN', 'ATENCIÓN'];

?>

<div class="main-container"> <input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />

    <div class="header-section">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="header-buttons-group">
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

            <div class="top-native-scrollbar" style="display: none;">
                <div class="top-native-dummy"></div>
            </div>

            <div class="table-responsive table-responsive-scroll" id="grid-scroll-container">
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
                            'label' => 'Fecha Alta',
                            'value' => function ($model, $key, $index, $widget) {
                                return !empty($model->created_at) ? Yii::$app->formatter->asDate($model->created_at, 'd/M/Y') : '';
                            },
                            'width' => '10%',
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
                            'hAlign' => 'center',
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
                        [
                            'attribute' => 'clinica_id',
                            'label' => 'Clínica',
                            'value' => function ($model) {
                                return $model->clinica ? $model->clinica->nombre : 'No asignada';
                            },
                            'filter' => $mostrarFiltroClinica ? Select2::widget([
                                'model' => $searchModel,
                                'attribute' => 'clinica_id',
                                'data' => \yii\helpers\ArrayHelper::map(
                                    \app\models\RmClinica::find()->orderBy('nombre')->all(),
                                    'id',
                                    'nombre'
                                ),
                                'options' => ['placeholder' => 'Filtrar Clínica'],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ]) : false,
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'contentOptions' => ['class' => 'text-center'],
                        ],
                        [
                            'attribute' => 'afiliado_corporativo_id',
                            'label' => 'Corporativo',
                            'value' => function ($model) {
                                if ($model->user_datos_type_id == 2 && $model->corporativo) {
                                    $name = Html::encode($model->corporativo->nombre);

                                    // Predefined color palettes
                                    $colorPalettes = [
                                        ['#dbeafe', '#93c5fd', '#3b82f6', '#1e40af', '#2563eb'],
                                        ['#d1fae5', '#6ee7b7', '#10b981', '#065f46', '#059669'],
                                        ['#ede9fe', '#c4b5fd', '#8b5cf6', '#5b21b6', '#7c3aed'],
                                        ['#fef3c7', '#fcd34d', '#f59e0b', '#92400e', '#d97706'],
                                        ['#fce7f3', '#f9a8d4', '#ec4899', '#9d174d', '#db2777'],
                                        ['#ccfbf1', '#5eead4', '#14b8a6', '#115e59', '#0d9488'],
                                        ['#fecaca', '#f87171', '#ef4444', '#991b1b', '#dc2626'],
                                        ['#e0e7ff', '#818cf8', '#4f46e5', '#312e81', '#4338ca'],
                                        ['#cffafe', '#67e8f9', '#06b6d4', '#164e63', '#0891b2'],
                                        ['#fce4ec', '#f48fb1', '#e91e63', '#880e4f', '#c2185b'],
                                        ['#e8f5e9', '#81c784', '#4caf50', '#1b5e20', '#388e3c'],
                                        ['#fff3e0', '#ffb74d', '#ff9800', '#e65100', '#f57c00'],
                                        ['#f3e5f5', '#ce93d8', '#9c27b0', '#4a148c', '#7b1fa2'],
                                        ['#e0f7fa', '#80deea', '#00bcd4', '#006064', '#00838f'],
                                        ['#fff8e1', '#ffd54f', '#ffc107', '#f57f17', '#f9a825'],
                                        ['#efebe9', '#a1887f', '#795548', '#3e2723', '#5d4037'],
                                    ];

                                    $hash = abs(crc32($name));
                                    $colorIndex = $hash % count($colorPalettes);
                                    $palette = $colorPalettes[$colorIndex];
                                    list($bg1, $bg2, $border, $text, $icon) = $palette;

                                    $style = sprintf(
                                        'background: linear-gradient(135deg, %s 0%%, %s 100%%); border-color: %s; color: %s;',
                                        $bg1,
                                        $bg2,
                                        $border,
                                        $text
                                    );
                                    $iconStyle = sprintf('color: %s;', $icon);

                                    return sprintf(
                                        '<span class="corporativo-badge" style="%s"><i class="fas fa-building mr-2" style="%s"></i> %s</span>',
                                        $style,
                                        $iconStyle,
                                        $name
                                    );
                                }
                                return '<span class="no-corporativo-badge"><i class="fas fa-user mr-2"></i> No aplica</span>';
                            },
                            'filter' => \kartik\select2\Select2::widget([
                                'model' => $searchModel,
                                'attribute' => 'afiliado_corporativo_id',
                                'data' => \yii\helpers\ArrayHelper::map(
                                    \app\models\Corporativo::find()
                                        ->where(['estatus' => 'Activo'])
                                        ->orderBy('nombre')
                                        ->all(),
                                    'id',
                                    'nombre'
                                ),
                                'options' => [
                                    'placeholder' => 'Filtrar corporativo',
                                    'class' => 'form-control'
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ]),
                            'contentOptions' => function ($model) {
                                if ($model->user_datos_type_id == 2 && $model->corporativo) {
                                    return ['class' => 'text-center', 'style' => 'vertical-align: middle; padding: 8px 5px;'];
                                }
                                return ['class' => 'text-center', 'style' => 'vertical-align: middle; padding: 8px 5px;'];
                            },
                            'headerOptions' => ['style' => 'color: white!important; width: 150px; min-width: 150px;'],
                            'format' => 'raw',
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
                                'placeholder' => 'Buscar nombre',
                                'class' => 'form-control text-center',
                            ],
                        ],
                        [
                            'label' => 'Cédula Identidad',
                            'attribute' => 'cedula',
                            'value' => function ($model) {
                                $tipoCedula = $model->tipo_cedula ?? '';
                                $numeroCedula = $model->cedula ?? '';
                                $consecutivo = '';

                                if (isset($model->consecutivo_menor) && $model->consecutivo_menor !== null && $model->consecutivo_menor !== '') {
                                    $consecutivo = str_pad((int)$model->consecutivo_menor, 2, '0', STR_PAD_LEFT);
                                }

                                if ($tipoCedula === 'Menor Sin Cédula') {
                                    if (!empty($consecutivo)) {
                                        return '<div class="cedula-grande-container menor-sin-cedula"><div class="cedula-grande-text">SIN CÉDULA-' . $numeroCedula . '-<span class="consecutivo-grande">' . $consecutivo . '</span></div></div>';
                                    } else {
                                        return '<div class="cedula-grande-container menor-sin-cedula"><div class="cedula-grande-text">SIN CÉDULA-' . $numeroCedula . '</div></div>';
                                    }
                                }

                                if (!empty($numeroCedula) && !empty($tipoCedula) && $tipoCedula !== 'Menor Sin Cédula') {
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
                                    return '<div class="cedula-grande-container cedula-normal"><div class="cedula-grande-text">' . $tipoCedula . '-' . $numeroCedula . '</div>' . ($esMenorEdad ? '<div class="edad-etiqueta">MENOR (' . $edad . ' años)</div>' : '') . '</div>';
                                }

                                if (!empty($tipoCedula) && empty($numeroCedula) && $tipoCedula !== 'Menor Sin Cédula') {
                                    return '<div class="cedula-grande-container cedula-pendiente"><div class="cedula-grande-text">' . $tipoCedula . '</div><div class="estado-etiqueta">PENDIENTE DE NÚMERO</div></div>';
                                }

                                return '<div class="cedula-grande-container sin-cedula"><div class="cedula-grande-text">SIN REGISTRO</div></div>';
                            },
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'options' => ['style' => 'width: 120px; min-width:120px;'],
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
                                'placeholder' => 'Buscar cédula',
                                'class' => 'form-control text-center',
                            ],
                        ],
                        'telefono',
                        [
                            'attribute' => 'email',
                            'label' => 'Correo Electrónico',
                            'format' => 'email',
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'options' => ['style' => 'width: 200px;'],
                            'filterInputOptions' => [
                                'placeholder' => 'Buscar por correo',
                                'class' => 'form-control text-center',
                            ],
                        ],
                        [
                            'label' => 'Asesor',
                            'hAlign' => 'center',
                            'format' => 'ntext',
                            'value' => function ($model) {
                                if ($model->asesor && $model->asesor->userDatos) {
                                    $ud = $model->asesor->userDatos;
                                    return trim(($ud->nombres ?? '') . ' ' . ($ud->apellidos ?? '')) ?: null;
                                }
                                return null;
                            },
                            'headerOptions' => ['style' => 'color: white!important;'],
                        ],
                        [
                            'label' => 'Estatus Contrato',
                            'format' => 'raw',
                            'value' => function ($model) {
                                return ContractHelper::generateContractStatusBadge($model);
                            },
                            'attribute' => 'contrato_estatus',
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
                            'template' => '<div class="d-flex justify-content-center gap-0">{view}{update}{atencion}{pagos}</div>',
                            'options' => ['style' => 'width:55px; min-width:55px;'],
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                            'buttons' => [
                                'view' => function ($url, $model, $key) use ($clinica) {
                                    $params = ['view', 'id' => $model->id];
                                    if ($clinica && $clinica->id !== null) {
                                        $params['clinica_id'] = $clinica->id;
                                    }
                                    return Html::a(
                                        '<i class="fa fa-eye"></i>',
                                        Url::to($params),
                                        [
                                            'title' => 'Detalle de Usuario',
                                            'class' => 'btn-action view'
                                        ]
                                    );
                                },
                                'update' => function ($url, $model, $key) use ($clinica, $rol) {
                                    if ($rol == 'superadmin' || $rol = 'DIRECTOR-COMERCIALIZACIÓN') {
                                        $params = ['update', 'id' => $model->id];
                                        if ($clinica && $clinica->id !== null) {
                                            $params['clinica_id'] = $clinica->id;
                                        }
                                        return Html::a(
                                            '<i class="fas fa-pencil-alt ms-text-primary"></i>',
                                            Url::to($params),
                                            [
                                                'title' => 'Editar Usuario',
                                                'class' => 'btn-action view'
                                            ]
                                        );
                                    } else {
                                        return "";
                                    }
                                },
                                'atencion' => function ($url, $model, $key) use ($rolesAtencionMedica, $clinica, $rol) {
                                    if (in_array($rol, $rolesAtencionMedica) && $model->clinica_id) {
                                        $urlSiniestro = Url::to(['/sis-siniestro/index', 'user_id' => $model->id, 'modo' => 'siniestro', 'clinica_id' => $clinica ? $clinica->id : null]);
                                        $urlCita = Url::to(['/sis-siniestro/index', 'user_id' => $model->id, 'modo' => 'cita', 'clinica_id' => $clinica ? $clinica->id : null]);

                                        return Html::a(
                                            '<i class="fas fa-heartbeat" style="color: red;"></i>',
                                            '#',
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
                                'delete' => function ($url, $model, $key) use ($permisos, $clinica) {
                                    if ($permisos) {
                                        $params = ['delete', 'id' => $model->id];
                                        if ($clinica && $clinica->id !== null) {
                                            $params['clinica_id'] = $clinica->id;
                                        }
                                        return Html::a(
                                            '<i class="far fa-trash-alt ms-text-danger"></i>',
                                            Url::to($params),
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
                    ],
                ]); ?>
            </div>
        </div>

    </div>
</div>

<?php
$js = <<<'JS'
// 1. EVENTO PARA ABRIR EL MODAL
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

// 2. EVENTO GLOBAL PARA CERRAR EL MODAL
$(document).on('click', '.btn-cerrar-swal', function(e) {
    e.preventDefault();
    Swal.close();
});

// 3. ROBUST TOOLTIP MANAGEMENT SYSTEM
$(document).ready(function() {
    function initAllTooltips() {
        $('[data-toggle="tooltip"]').tooltip('dispose');
        try {
            $('[data-toggle="tooltip"]').tooltip({
                trigger: 'hover',
                delay: { "show": 100, "hide": 100 },
                container: 'body',
                boundary: 'window',
                html: true
            });
        } catch (error) {
            console.error('Error initializing tooltips:', error);
        }
    }
    
    initAllTooltips();
    $(document).on('pjax:complete yiiGridViewUpdated', function() {
        setTimeout(initAllTooltips, 100);
    });
    
    $(document).on('click', '.contract-tooltip strong', function(e) {
        e.stopPropagation();
        var text = $(this).text();
        if (text.includes('Contrato #') || text.includes('Número:')) {
            var contractNum = text.replace('Contrato #', '').replace('Número: ', '').trim();
            navigator.clipboard.writeText(contractNum).then(function() {
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
    
    highlightImportantStatuses();
    $(document).on('pjax:complete yiiGridViewUpdated', function() {
        setTimeout(highlightImportantStatuses, 150);
    });
});

// 4. DUAL SCROLLBAR SYSTEM
$(document).ready(function() {
    var $gridWrapper = $('#grid-scroll-container');
    var $top = $('.top-native-scrollbar');
    var $dummy = $('.top-native-dummy');
    var $activeScroller = $();
    var syncingFrom = null;

    function getActiveGridScroller() {
        var $candidates = $gridWrapper.find('*').addBack();
        var best = null;
        var bestWidth = 0;
        $candidates.each(function() {
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
        var topEl = $top.get(0);
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
        $gridWrapper.find('table').each(function() {
            var width = this.scrollWidth;
            if (width > maxWidth) maxWidth = width;
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
        var scrollWidth = getMaxScrollWidth();
        var viewportWidth = gridEl.clientWidth;
        var hasHorizontalScroll = scrollWidth > (viewportWidth + 1);
        if (hasHorizontalScroll) {
            $dummy.css('width', (scrollWidth + 20) + 'px');
            $top.show();
            setTimeout(function() {
                var maxScroll = scrollWidth - $top[0].clientWidth;
                if ($top[0].scrollLeft > maxScroll) {
                    $top[0].scrollLeft = maxScroll;
                }
            }, 20);
            syncBars('grid');
        } else {
            $top.hide();
        }
    }

    $top.off('scroll.dual').on('scroll.dual', function() {
        syncBars('top');
    });

    function bindGridScroll() {
        $gridWrapper.find('*').off('scroll.dual-grid');
        $gridWrapper.off('scroll.dual-grid');
        $activeScroller = getActiveGridScroller();
        $activeScroller.off('scroll.dual-grid').on('scroll.dual-grid', function() {
            syncBars('grid');
        });
    }

    bindGridScroll();
    refreshTopBar();
    setTimeout(refreshTopBar, 120);
    setTimeout(refreshTopBar, 320);

    $(window).off('resize.dual').on('resize.dual', refreshTopBar);
    $(document).on('pjax:complete yiiGridViewUpdated ajaxComplete', function() {
        setTimeout(function() {
            bindGridScroll();
            refreshTopBar();
        }, 150);
    });
});
JS;

$this->registerJs($js, View::POS_READY);
?>

<style>
    /* ============================================
       CÉDULA DE IDENTIDAD STYLES
       ============================================ */
    .cedula-grande-container {
        text-align: center;
        padding: 10px 5px;
        margin: 0;
    }

    .cedula-grande-text {
        font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        font-size: 1.2rem !important;
        font-weight: 600;
        line-height: 1.3;
        margin-bottom: 4px;
        letter-spacing: 0.3px;
    }

    .menor-sin-cedula .cedula-grande-text {
        font-size: 1.3rem !important;
        font-weight: 700;
        color: #e67e22;
        font-family: 'Courier New', Consolas, monospace;
        background-color: rgba(255, 152, 0, 0.1);
        border: 2px solid rgba(255, 152, 0, 0.2);
        border-radius: 8px;
        padding: 10px 15px;
        display: inline-block;
    }

    .menor-sin-cedula .consecutivo-grande {
        font-size: 1.4rem !important;
        font-weight: 900;
        color: #e74c3c;
        background-color: rgba(231, 76, 60, 0.15);
        padding: 0 6px;
        border-radius: 5px;
        margin: 0 3px;
    }

    .cedula-normal .cedula-grande-text {
        color: #2c3e50;
        background-color: rgba(52, 152, 219, 0.1);
        border: 2px solid rgba(52, 152, 219, 0.2);
        border-radius: 6px;
        padding: 8px 12px;
        display: inline-block;
    }

    .cedula-pendiente .cedula-grande-text {
        color: #7f8c8d;
        background-color: rgba(127, 140, 141, 0.1);
        border: 2px dashed rgba(127, 140, 141, 0.3);
        border-radius: 6px;
        padding: 8px 12px;
        display: inline-block;
    }

    .sin-cedula .cedula-grande-text {
        color: #e74c3c;
        background-color: rgba(231, 76, 60, 0.1);
        border: 2px solid rgba(231, 76, 60, 0.2);
        border-radius: 6px;
        padding: 8px 12px;
        display: inline-block;
    }

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

    .bg-menor-sin-cedula {
        background-color: rgba(255, 193, 7, 0.08) !important;
    }

    .bg-cedula-normal {
        background-color: rgba(52, 152, 219, 0.03) !important;
    }

    tbody tr:hover .bg-menor-sin-cedula {
        background-color: rgba(255, 193, 7, 0.12) !important;
    }

    tbody tr:hover .bg-cedula-normal {
        background-color: rgba(52, 152, 219, 0.06) !important;
    }

    .cedula-grande-text {
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
    }

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

    @media print {
        .cedula-grande-text {
            font-size: 11pt !important;
            color: #000 !important;
            background-color: transparent !important;
            border: 1px solid #ccc !important;
        }
    }

    /* ============================================
       CONTRACT STATUS BADGE STYLES
       ============================================ */
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
        transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        min-width: 80px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border: 1px solid transparent;
    }

    .badge-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: #fff;
        border-color: #0062cc;
    }

    .badge-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: #fff;
        border-color: #1e7e34;
    }

    .badge-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: #fff;
        border-color: #bd2130;
    }

    .badge-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        color: #212529;
        border-color: #d39e00;
    }

    .badge-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: #fff;
        border-color: #117a8b;
    }

    .badge-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
        color: #fff;
        border-color: #4e555b;
    }

    .badge-light {
        color: #212529;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
    }

    .badge:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .contract-status-cell {
        font-weight: 600;
        text-align: center !important;
        vertical-align: middle !important;
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

    tbody tr:hover .contract-status-cell.activo {
        background-color: rgba(40, 167, 69, 0.1) !important;
    }

    /* ============================================
       CORPORATIVO BADGE STYLES
       ============================================ */
    .corporativo-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.6rem 1.2rem;
        font-size: 1rem;
        font-weight: 600;
        border: 2px solid;
        border-radius: 25px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        letter-spacing: 0.4px;
        max-width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        min-height: 40px;
        background: linear-gradient(135deg, #dbeafe 0%, #93c5fd 100%);
        border-color: #3b82f6;
        color: #1e40af;
    }

    .corporativo-badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .corporativo-badge i {
        font-size: 1.1rem;
        margin-right: 10px;
    }

    .no-corporativo-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.6rem 1.2rem;
        font-size: 0.95rem;
        font-weight: 500;
        color: #6c757d;
        background: #f1f2f6;
        border: 2px dashed #ced4da;
        border-radius: 25px;
        transition: all 0.3s ease;
        letter-spacing: 0.3px;
        min-height: 40px;
    }

    .no-corporativo-badge i {
        font-size: 1rem;
        margin-right: 10px;
        color: #adb5bd;
    }

    .no-corporativo-badge:hover {
        background: #e9ecef;
        border-color: #adb5bd;
        transform: translateY(-1px);
    }

    .corporativo-badge {
        animation: fadeInBadge 0.5s ease-in-out;
    }

    @keyframes fadeInBadge {
        0% {
            opacity: 0;
            transform: scale(0.9);
        }

        100% {
            opacity: 1;
            transform: scale(1);
        }
    }

    /* ============================================
       DUAL SCROLLBAR SYSTEM
       ============================================ */
    .table-responsive-scroll {
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
    }

    .top-native-scrollbar {
        width: 100%;
        height: 12px;
        overflow-x: scroll;
        overflow-y: hidden;
        margin-bottom: 10px;
        display: none;
        cursor: pointer;
        box-sizing: border-box;
        -webkit-overflow-scrolling: touch;
    }

    .top-native-scrollbar .top-native-dummy {
        display: inline-block;
        width: 100%;
        min-height: 1px;
        height: 1px;
    }

    .top-native-scrollbar::-webkit-scrollbar,
    .table-responsive-scroll::-webkit-scrollbar {
        height: 12px;
    }

    .top-native-scrollbar::-webkit-scrollbar-track,
    .table-responsive-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 6px;
    }

    .top-native-scrollbar::-webkit-scrollbar-thumb,
    .table-responsive-scroll::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 6px;
    }

    .top-native-scrollbar::-webkit-scrollbar-thumb:hover,
    .table-responsive-scroll::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Ensure proper centering */
    .grid-view td:has(.badge) {
        text-align: center !important;
        vertical-align: middle !important;
    }

    .badge {
        display: inline-block !important;
        text-align: center !important;
    }

    .grid-view td {
        vertical-align: middle !important;
        padding: 8px 6px !important;
    }

    .grid-view th {
        vertical-align: middle !important;
    }

    /* Responsive */
    @media (max-width: 768px) {

        .corporativo-badge,
        .no-corporativo-badge {
            font-size: 0.85rem;
            padding: 0.4rem 0.9rem;
            min-height: 34px;
            white-space: normal;
            word-break: break-word;
        }

        .corporativo-badge i,
        .no-corporativo-badge i {
            font-size: 0.9rem;
            margin-right: 6px;
        }

        .badge {
            font-size: 0.75em;
            padding: 0.25em 0.5em;
        }
    }

    @media (max-width: 576px) {

        .corporativo-badge,
        .no-corporativo-badge {
            font-size: 0.75rem;
            padding: 0.3rem 0.7rem;
            min-height: 30px;
            border-radius: 20px;
        }

        .corporativo-badge i,
        .no-corporativo-badge i {
            font-size: 0.8rem;
            margin-right: 4px;
        }
    }
</style>