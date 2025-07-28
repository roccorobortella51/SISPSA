<?php

// Importaciones necesarias
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use kartik\widgets\SwitchInput;
use yii\widgets\Pjax;
use yii\bootstrap4\Progress; 
use app\models\CheckListClinicas;


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

// Define un array con los nombres de todas tus columnas booleanas
$booleanColumns = [
    'planes',
    'programa_de_servicio',
    'equipamiento',
    'servicios_de_tecnologia',
    'soepsa_rm_009013_reglamento_soepsa_mes',
    'visita_clinica_registro_escrito',
    'otro_paso1',
    'ubicacion_de_la_clinica_facil_acceso_usuario',
    'instalaciones_adecuacion_aire_atencion_medica',
    'instalaciones_optimas_equipos_nuevos',
    'generador_energia_emergencia',
    'capacidad_atencion_afiliados_emergencia_hosp',
    'especialistas_diferentes_especialidades',
    'disponibilidad_ambulancia',
    'personal_medico_registrado_licencia',
    'personal_enfermeria_licencia',
    'complemento_horarios_atencion',
    'servicio_farmacia',
    'acuerdo_servicios_medicos_emergencia_urgencia',
    'formatos_servicios',
    'certificacion_cumplimientos_normativas_sanitarias',
    'certificacion_cumplimiento_normativas_riesgos',
    'certificado_control_calidad',
    'documentos_superintendencia_seguros',
    'certificado_registro_salud_minsalud_ci_nii',
    'documentos_legales_firma_poderes_autorizar',
    'firma_permisos',
    'contratos',
    'marco_legal_operar',
    'documentacion_responsables_firmantes_contrato',
    'personal_soepsa',
    'asignar_personal_adecuado_soepsa',
    'capacitacion_personal_pdv_servicio_local',
    'verif_sistema_pdv_local_clinica',
    'otro_paso5',
    'descripcion_servicios_soepsa',
    'explicacion_beneficios_afiliados_soepsa',
    'espacio_presentacion_ilum_sonido_internet',
    'material_apoyo_presentacion_triptico_laminas',
    'plan_contingencia_fallas_procedencia',
    'comunicacion_fluida_gerencias_apoyo',
    'tiempo_ejecucion_actividades_acumuladas',
    'plazos_ejecucion_actividades_super',
    'verificar_servicios_cuentan_clinica',
    'equipo_implementacion_coordinador_soepsa',
    'equipo_implementacion_gerencia',
    'equipo_implementacion_directivos_operaciones',
    'equipo_implementacion_directivo_marketing_ventas',
    'equipo_implementacion_directivo_finanzas',
    'equipo_implementacion_contabilidad',
    'equipo_implementacion_coordinador_ventas',
    'equipo_implementacion_soporte',
    'equipo_implementacion_cursos_capacitacion',
    'equipo_implementacion_material_apoyo_sistema',
    'sistema_soepsa',
    'instalacion_personal_medico',
    'verificacion_area_emergencia',
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

$maxId = CheckListClinicas::find()->max('id');
$latestCreatedAt = CheckListClinicas::find()->max('created_at');


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
        
    </div>
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
           

        <div class="ms-panel-header d-flex justify-content-between align-items-center">
             
            <h1><?= $this->title = 'Verificación de Clínicas'." ".$clinica->nombre; ?></h1>
                
                <div class="d-flex align-items-center gap-2">

                    <?php
                    $check = CheckListClinicas::find()->where(['clinica_id' => $clinica->id])->one();

                    if($check == "" || $check == null){
                       echo  Html::a(
                            '<i class="fas fa-plus"></i> CREAR NUEVA VERIFICACIÓN', 
                            ['create', 'clinica_id' => $clinica->id], 
                            ['class' => 'btn btn-outline-primary btn-lg']
                        ); 
                    }
                    ?> 
                   <?= Html::a(
                        '<i class="fas fa-undo"></i> Volver', 
                        '#',
                        [
                            // CAMBIO AQUÍ: Añadimos 'me-3' (Bootstrap 5) o 'mr-3' (Bootstrap 4)
                            'class' => 'btn btn-primary btn-lg me-3', 
                            'onclick' => 'window.history.back(); return false;', 
                            'title' => 'Volver a la página anterior', 
                        ]
                    ) ?> 
                </div>
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
                    [
                        'attribute' => 'created_at',
                        'format' => ['datetime', 'php:d-m-Y H:i:s'], // O 'datetime' para el formato por defecto de la app
                        'label' => 'Fecha de Creación', // Opcional: Cambiar el título de la columna
                    ],
                    [
                        'attribute' => 'updated_at',
                        'format' => ['datetime', 'php:d-m-Y H:i:s'], // O 'datetime'
                        'label' => 'Última Actualización', // Opcional: Cambiar el título de la columna
                    ],
                    'clinica.nombre',
                    'clinica.rif',
                    [
                        'label' => '% Seleccionados',
                        'format' => 'raw',
                        'value' => function ($model) use ($booleanColumns) {
                            $totalColumns = count($booleanColumns);
                            if ($totalColumns === 0) {
                                $percentage = 0;
                                $barColorClass = 'bg-secondary';
                            } else {
                                $trueCount = 0;
                                foreach ($booleanColumns as $column) {
                                    if ($model->$column === true || $model->$column == 1) {
                                        $trueCount++;
                                    }
                                }
                                $percentage = round(($trueCount / $totalColumns) * 100);
                                $barColorClass = 'bg-success';
                            }

                            return Progress::widget([
                                'percent' => $percentage,
                                // Establecer el color de la barra (verde)
                                'barOptions' => ['class' => $barColorClass],
                                'options' => [
                                    'style' => 'width: 100px; height: 20px; background-color: #e9ecef;', // Fondo gris claro
                                ],
                                // Envolver el label en un <span> con estilo de color azul
                                'label' => Html::tag('span', $percentage . '%', ['style' => 'color: black;']),
                            ]);
                        },
                        'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                        'headerOptions' => ['style' => 'text-align: center;'],
                    ],
                    /*[
                        'label' => '% No Seleccionados',
                        'format' => 'raw',
                        'value' => function ($model) use ($booleanColumns) {
                            $totalColumns = count($booleanColumns);
                            if ($totalColumns === 0) {
                                $percentage = 0;
                                $barColorClass = 'bg-secondary';
                            } else {
                                $falseCount = 0;
                                foreach ($booleanColumns as $column) {
                                    if ($model->$column === false || $model->$column == 0) {
                                        $falseCount++;
                                    }
                                }
                                $percentage = round(($falseCount / $totalColumns) * 100);
                                $barColorClass = 'bg-danger';
                            }

                            return Progress::widget([
                                'percent' => $percentage,
                                // Establecer el color de la barra (rojo)
                                'barOptions' => ['class' => $barColorClass],
                                'options' => [
                                    'style' => 'width: 100px; height: 20px; background-color: #e9ecef;', // Fondo gris claro
                                ],
                                // Envolver el label en un <span> con estilo de color azul
                                'label' => Html::tag('span', $percentage . '%', ['style' => 'color: black;']),
                            ]);
                        },
                        'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                        'headerOptions' => ['style' => 'text-align: center;'],
                    ],*/
                    [
                                    'class' => 'yii\grid\ActionColumn',
                                    'header' => 'ACCIONES',
                                    'template' => '<div class="d-flex justify-content-center gap-0">{update}{view}</div>',
                                    'options' => ['style' => 'width:55px; min-width:55px;'],
                                    'headerOptions' => ['style' => 'color: white!important;'],
                                    'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                                    'buttons' => [

                                    'update' => function ($url, $model, $key) use ($maxId, $latestCreatedAt) { // ¡Aquí está el cambio clave!
                                        // Solo muestra el botón si el ID del modelo actual es el ID máximo
                                        if ($model->id == $maxId || $latestCreatedAt == $model->created_at) {
                                            return Html::a(
                                                '<i class="fas fa-sync-alt ms-text-primary"></i>', // Ícono de sincronización
                                                Url::to(['update', 'id' => $model->id]),
                                                [
                                                    'title' => 'Renovar Check List de la Clínica', // Tooltip
                                                    'class' => 'btn btn-link btn-sm text-info',
                                                    'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;'
                                                ]
                                            );
                                        }
                                        return ''; // Si no es el ID mayor, no muestra nada
                                    },

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
                                    ],
                                ],

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
 











