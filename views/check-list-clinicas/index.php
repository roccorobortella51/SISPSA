<?php

// Importaciones necesarias
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use kartik\widgets\SwitchInput;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\CheckListClinicasSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'LISTA DE VERIFICACIÓN DE CLÍNICAS';
$this->params['breadcrumbs'][] = $this->title;

// Helper para opciones de filtro booleano
$booleanFilterOptions = [
    '1' => 'Sí',
    '0' => 'No',
    null => '(Todos)', // Permite filtrar por registros nulos o sin especificar
];

// Helper para formato de columna booleana
$booleanColumnFormat = function ($data, $row) {
    if ($data === true || $data === 1) {
        return '<span class="badge badge-success">Sí</span>';
    } else if ($data === false || $data === 0) {
        return '<span class="badge badge-danger">No</span>';
    }
    return ''; // Para valores nulos o no definidos
};

/**
 * @var yii\web\View $this
 * @var app\models\RmClinicaSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 */

// --- BREADCRUMBS ---

$this->params['breadcrumbs'][] = ['label' => 'CLINICA', 'url' => ['rm-clinica/update?id='. $clinica->id]];
// --- FIN  --- 


$this->title = 'Verificación de Clínicas'; // Este sigue siendo el título para la página y breadcrumbs

?>

<div class=row style="margin:3px !important;">
<input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />
    <div class="col-md-12 text-end">
        <div class="float-right" style="margin-bottom:10px;">
            <?= Html::a('<i class="fas fa-plus"></i> CREAR NUEVA VERIFICACIÓN', ['create', 'clinica_id' => $clinica->id], ['class' => 'btn btn-outline-primary btn-lg']) ?> 
             <?= Html::a('<i class="fas fa-undo"></i> Volver', ['/rm-clinica/update', 'id' => $clinica->id], ['class' => 'btn btn-info btn-lg']) ?>
        </div>
    </div>
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header">
                <h1><?= $this->title = 'Verificación de Clínicas'." ".$clinica->nombre; ?></h1>
            </div>

            <?php Pjax::begin(); ?>
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
                    'resizableColumns' => false,
                    'bordered' => false,
                    'responsiveWrap' => false,
                    'persistResize' => false,
                    'columns' => [
                    'clinica.nombre',
                    'clinica.rif',
                    [
                                    'class' => 'yii\grid\ActionColumn',
                                    'header' => 'ACCIONES',
                                    'template' => '<div class="d-flex justify-content-center gap-0">{view}{update}</div>',
                                    'options' => ['style' => 'width:55px; min-width:55px;'],
                                    'headerOptions' => ['style' => 'color: white!important;'],
                                    'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                                    'buttons' => [
                                        'view' => function ($url, $model, $key) {
                                            return Html::a(
                                                '<i class="fa fa-eye"></i>',
                                                Url::to(['view', 'id' => $model->id]),
                                                [
                                                    'title' => 'Detalle de la Verificación',
                                                    'class' => 'btn btn-link btn-sm text-success',
                                                    'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;'
                                                ]
                                            );
                                        },
                                        'update' => function ($url, $model, $key) {
                                            return Html::a(
                                                '<i class="fas fa-pencil-alt ms-text-primary"></i>',
                                                Url::to(['update', 'id' => $model->id]),
                                                [
                                                    'title' => 'Editar',
                                                    'class' => 'btn btn-link btn-sm text-success',
                                                    'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;'
                                                ]
                                            );
                                        },
                                   
                                        
                                    ],
                                ],

                      

                    [
                    'attribute' => 'planes',
                    'headerOptions' => ['style' => 'width: 100px;'],
                    'format' => 'html',
                    'contentOptions' => ['style' => 'width: 100px; text-align: center; padding: 10px !important;'], // Agregado padding
                    'filter' => $booleanFilterOptions,
                    'value' => function ($model) {
                        // Verifica explícitamente el valor booleano
                            if ($model->planes === true || $model->planes === 1 || $model->planes === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                // Cualquier otro valor (false, 0, null, 'f') se considera "No"
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'programa_de_servicio',
                        'headerOptions' => ['style' => 'width: 120px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 120px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->programa_de_servicio === true || $model->programa_de_servicio === 1 || $model->programa_de_servicio === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'equipamiento',
                        'headerOptions' => ['style' => 'width: 100px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 100px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->equipamiento === true || $model->equipamiento === 1 || $model->equipamiento === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'servicios_de_tecnologia',
                        'headerOptions' => ['style' => 'width: 120px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 120px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->servicios_de_tecnologia === true || $model->servicios_de_tecnologia === 1 || $model->servicios_de_tecnologia === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'soepsa_rm_009013_reglamento_soepsa_mes',
                        'headerOptions' => ['style' => 'width: 150px;'], // Ajustado el ancho
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 150px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->soepsa_rm_009013_reglamento_soepsa_mes === true || $model->soepsa_rm_009013_reglamento_soepsa_mes === 1 || $model->soepsa_rm_009013_reglamento_soepsa_mes === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'visita_clinica_registro_escrito',
                        'headerOptions' => ['style' => 'width: 150px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 150px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->visita_clinica_registro_escrito === true || $model->visita_clinica_registro_escrito === 1 || $model->visita_clinica_registro_escrito === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'otro_paso1',
                        'headerOptions' => ['style' => 'width: 100px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 100px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->otro_paso1 === true || $model->otro_paso1 === 1 || $model->otro_paso1 === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],

                    // Paso 2
                    [
                        'attribute' => 'ubicacion_de_la_clinica_facil_acceso_usuario',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->ubicacion_de_la_clinica_facil_acceso_usuario === true || $model->ubicacion_de_la_clinica_facil_acceso_usuario === 1 || $model->ubicacion_de_la_clinica_facil_acceso_usuario === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'instalaciones_adecuacion_aire_atencion_medica',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->instalaciones_adecuacion_aire_atencion_medica === true || $model->instalaciones_adecuacion_aire_atencion_medica === 1 || $model->instalaciones_adecuacion_aire_atencion_medica === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'instalaciones_optimas_equipos_nuevos',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->instalaciones_optimas_equipos_nuevos === true || $model->instalaciones_optimas_equipos_nuevos === 1 || $model->instalaciones_optimas_equipos_nuevos === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'generador_energia_emergencia',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->generador_energia_emergencia === true || $model->generador_energia_emergencia === 1 || $model->generador_energia_emergencia === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'capacidad_atencion_afiliados_emergencia_hosp',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->capacidad_atencion_afiliados_emergencia_hosp === true || $model->capacidad_atencion_afiliados_emergencia_hosp === 1 || $model->capacidad_atencion_afiliados_emergencia_hosp === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'especialistas_diferentes_especialidades',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->especialistas_diferentes_especialidades === true || $model->especialistas_diferentes_especialidades === 1 || $model->especialistas_diferentes_especialidades === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'disponibilidad_ambulancia',
                        'headerOptions' => ['style' => 'width: 150px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 150px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->disponibilidad_ambulancia === true || $model->disponibilidad_ambulancia === 1 || $model->disponibilidad_ambulancia === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'personal_medico_registrado_licencia',
                        'headerOptions' => ['style' => 'width: 150px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 150px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->personal_medico_registrado_licencia === true || $model->personal_medico_registrado_licencia === 1 || $model->personal_medico_registrado_licencia === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'personal_enfermeria_licencia',
                        'headerOptions' => ['style' => 'width: 150px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 150px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->personal_enfermeria_licencia === true || $model->personal_enfermeria_licencia === 1 || $model->personal_enfermeria_licencia === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'complemento_horarios_atencion',
                        'headerOptions' => ['style' => 'width: 150px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 150px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->complemento_horarios_atencion === true || $model->complemento_horarios_atencion === 1 || $model->complemento_horarios_atencion === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'servicio_farmacia',
                        'headerOptions' => ['style' => 'width: 120px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 120px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->servicio_farmacia === true || $model->servicio_farmacia === 1 || $model->servicio_farmacia === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],

                    // Paso 3
                    [
                        'attribute' => 'acuerdo_servicios_medicos_emergencia_urgencia',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->acuerdo_servicios_medicos_emergencia_urgencia === true || $model->acuerdo_servicios_medicos_emergencia_urgencia === 1 || $model->acuerdo_servicios_medicos_emergencia_urgencia === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'formatos_servicios',
                        'headerOptions' => ['style' => 'width: 120px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 120px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->formatos_servicios === true || $model->formatos_servicios === 1 || $model->formatos_servicios === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'certificacion_cumplimientos_normativas_sanitarias',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->certificacion_cumplimientos_normativas_sanitarias === true || $model->certificacion_cumplimientos_normativas_sanitarias === 1 || $model->certificacion_cumplimientos_normativas_sanitarias === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'certificacion_cumplimiento_normativas_riesgos',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->certificacion_cumplimiento_normativas_riesgos === true || $model->certificacion_cumplimiento_normativas_riesgos === 1 || $model->certificacion_cumplimiento_normativas_riesgos === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'certificado_control_calidad',
                        'headerOptions' => ['style' => 'width: 150px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 150px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->certificado_control_calidad === true || $model->certificado_control_calidad === 1 || $model->certificado_control_calidad === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'documentos_superintendencia_seguros',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->documentos_superintendencia_seguros === true || $model->documentos_superintendencia_seguros === 1 || $model->documentos_superintendencia_seguros === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'certificado_registro_salud_minsalud_ci_nii',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->certificado_registro_salud_minsalud_ci_nii === true || $model->certificado_registro_salud_minsalud_ci_nii === 1 || $model->certificado_registro_salud_minsalud_ci_nii === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'documentos_legales_firma_poderes_autorizar',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->documentos_legales_firma_poderes_autorizar === true || $model->documentos_legales_firma_poderes_autorizar === 1 || $model->documentos_legales_firma_poderes_autorizar === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'firma_permisos',
                        'headerOptions' => ['style' => 'width: 120px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 120px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->firma_permisos === true || $model->firma_permisos === 1 || $model->firma_permisos === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],

                    // Paso 4
                    [
                        'attribute' => 'contratos',
                        'headerOptions' => ['style' => 'width: 100px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 100px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->contratos === true || $model->contratos === 1 || $model->contratos === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'marco_legal_operar',
                        'headerOptions' => ['style' => 'width: 120px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 120px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->marco_legal_operar === true || $model->marco_legal_operar === 1 || $model->marco_legal_operar === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'documentacion_responsables_firmantes_contrato',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->documentacion_responsables_firmantes_contrato === true || $model->documentacion_responsables_firmantes_contrato === 1 || $model->documentacion_responsables_firmantes_contrato === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],

                    // Paso 5
                    [
                        'attribute' => 'personal_soepsa',
                        'headerOptions' => ['style' => 'width: 120px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 120px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->personal_soepsa === true || $model->personal_soepsa === 1 || $model->personal_soepsa === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'asignar_personal_adecuado_soepsa',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->asignar_personal_adecuado_soepsa === true || $model->asignar_personal_adecuado_soepsa === 1 || $model->asignar_personal_adecuado_soepsa === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'capacitacion_personal_pdv_servicio_local',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->capacitacion_personal_pdv_servicio_local === true || $model->capacitacion_personal_pdv_servicio_local === 1 || $model->capacitacion_personal_pdv_servicio_local === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'verif_sistema_pdv_local_clinica',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->verif_sistema_pdv_local_clinica === true || $model->verif_sistema_pdv_local_clinica === 1 || $model->verif_sistema_pdv_local_clinica === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'otro_paso5',
                        'headerOptions' => ['style' => 'width: 100px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 100px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->otro_paso5 === true || $model->otro_paso5 === 1 || $model->otro_paso5 === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],

                    // Paso 6
                    [
                        'attribute' => 'descripcion_servicios_soepsa',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->descripcion_servicios_soepsa === true || $model->descripcion_servicios_soepsa === 1 || $model->descripcion_servicios_soepsa === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'explicacion_beneficios_afiliados_soepsa',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->explicacion_beneficios_afiliados_soepsa === true || $model->explicacion_beneficios_afiliados_soepsa === 1 || $model->explicacion_beneficios_afiliados_soepsa === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'espacio_presentacion_ilum_sonido_internet',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->espacio_presentacion_ilum_sonido_internet === true || $model->espacio_presentacion_ilum_sonido_internet === 1 || $model->espacio_presentacion_ilum_sonido_internet === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'material_apoyo_presentacion_triptico_laminas',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->material_apoyo_presentacion_triptico_laminas === true || $model->material_apoyo_presentacion_triptico_laminas === 1 || $model->material_apoyo_presentacion_triptico_laminas === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'plan_contingencia_fallas_procedencia',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->plan_contingencia_fallas_procedencia === true || $model->plan_contingencia_fallas_procedencia === 1 || $model->plan_contingencia_fallas_procedencia === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    /*[
                        'attribute' => 'comunicacion_fluida_gerencias_apoyo',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->comunicacion_fluida_gerencias_apoyo === true || $model->comunicacion_fluida_gerencias_apuesta === 1 || $model->comunicacion_fluida_gerencias_apoyo === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],*/

                    // Paso 7
                    [
                        'attribute' => 'tiempo_ejecucion_actividades_acumuladas',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->tiempo_ejecucion_actividades_acumuladas === true || $model->tiempo_ejecucion_actividades_acumuladas === 1 || $model->tiempo_ejecucion_actividades_acumuladas === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'plazos_ejecucion_actividades_super',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->plazos_ejecucion_actividades_super === true || $model->plazos_ejecucion_actividades_super === 1 || $model->plazos_ejecucion_actividades_super === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'verificar_servicios_cuentan_clinica',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->verificar_servicios_cuentan_clinica === true || $model->verificar_servicios_cuentan_clinica === 1 || $model->verificar_servicios_cuentan_clinica === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],

                    // Paso 8 - Equipo de Implementación
                    [
                        'attribute' => 'equipo_implementacion_coordinador_soepsa',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->equipo_implementacion_coordinador_soepsa === true || $model->equipo_implementacion_coordinador_soepsa === 1 || $model->equipo_implementacion_coordinador_soepsa === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'equipo_implementacion_gerencia',
                        'headerOptions' => ['style' => 'width: 120px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 120px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->equipo_implementacion_gerencia === true || $model->equipo_implementacion_gerencia === 1 || $model->equipo_implementacion_gerencia === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'equipo_implementacion_directivos_operaciones',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->equipo_implementacion_directivos_operaciones === true || $model->equipo_implementacion_directivos_operaciones === 1 || $model->equipo_implementacion_directivos_operaciones === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'equipo_implementacion_directivo_marketing_ventas',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->equipo_implementacion_directivo_marketing_ventas === true || $model->equipo_implementacion_directivo_marketing_ventas === 1 || $model->equipo_implementacion_directivo_marketing_ventas === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'equipo_implementacion_directivo_finanzas',
                        'headerOptions' => ['style' => 'width: 150px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 150px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->equipo_implementacion_directivo_finanzas === true || $model->equipo_implementacion_directivo_finanzas === 1 || $model->equipo_implementacion_directivo_finanzas === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'equipo_implementacion_contabilidad',
                        'headerOptions' => ['style' => 'width: 150px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 150px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->equipo_implementacion_contabilidad === true || $model->equipo_implementacion_contabilidad === 1 || $model->equipo_implementacion_contabilidad === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'equipo_implementacion_coordinador_ventas',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->equipo_implementacion_coordinador_ventas === true || $model->equipo_implementacion_coordinador_ventas === 1 || $model->equipo_implementacion_coordinador_ventas === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'equipo_implementacion_soporte',
                        'headerOptions' => ['style' => 'width: 120px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 120px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->equipo_implementacion_soporte === true || $model->equipo_implementacion_soporte === 1 || $model->equipo_implementacion_soporte === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'equipo_implementacion_cursos_capacitacion',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->equipo_implementacion_cursos_capacitacion === true || $model->equipo_implementacion_cursos_capacitacion === 1 || $model->equipo_implementacion_cursos_capacitacion === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'equipo_implementacion_material_apoyo_sistema',
                        'headerOptions' => ['style' => 'width: 180px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 180px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->equipo_implementacion_material_apoyo_sistema === true || $model->equipo_implementacion_material_apoyo_sistema === 1 || $model->equipo_implementacion_material_apoyo_sistema === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],

                    // Paso 9
                    [
                        'attribute' => 'sistema_soepsa',
                        'headerOptions' => ['style' => 'width: 120px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 120px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->sistema_soepsa === true || $model->sistema_soepsa === 1 || $model->sistema_soepsa === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'instalacion_personal_medico',
                        'headerOptions' => ['style' => 'width: 150px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 150px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->instalacion_personal_medico === true || $model->instalacion_personal_medico === 1 || $model->instalacion_personal_medico === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],
                    [
                        'attribute' => 'verificacion_area_emergencia',
                        'headerOptions' => ['style' => 'width: 150px;'],
                        'format' => 'html',
                        'contentOptions' => ['style' => 'width: 150px; text-align: center; padding: 10px !important;'],
                        'filter' => $booleanFilterOptions,
                        'value' => function ($model) {
                            if ($model->verificacion_area_emergencia === true || $model->verificacion_area_emergencia === 1 || $model->verificacion_area_emergencia === 't') {
                                return '<span class="badge badge-success">Sí</span>';
                            } else {
                                return '<span class="badge badge-danger">No</span>';
                            }
                        },
                    ],


                        // Action Column
             
                    ],
                ]); ?>
            </div>

    <?php Pjax::end(); ?>

</div>
</div>
</div>
               </div>
            </div>
            <div class="clearfix"></div>
        </div>
 











