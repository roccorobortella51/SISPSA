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

// Define admin roles for clinic search filter
$isAdmin = ($rol == 'superadmin' || $rol == 'DIRECTOR-COMERCIALIZACIÓN');

?>

<div class="main-container"> <!-- Contenedor principal de la vista -->
    <input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />
    
    <!-- Encabezado y Botones de Acción Principal -->
    <div class="header-section"> 
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="header-buttons-group">
            <?php if ($permisos) : ?>
                <?= Html::a(
                    '<i class="fas fa-file-excel mr-2"></i> CARGAR MASIVOS DE AFILIADOS', 
                    ['masivo'], 
                    ['class' => 'btn-base btn-blue'] // Usando clases de sipsa.css
                ) ?> 
                <?= Html::a(
                    '<i class="fas fa-plus mr-2"></i> CREAR NUEVO AFILIADO DEL SÍSTEMA', 
                    ['create'], 
                    ['class' => 'btn-base btn-blue'] // Usando clases de sipsa.css
                ) ?> 
            <?php endif; ?>
            <!-- Botón "Volver a Clínica" condicional -->
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

    <!-- Panel para la Gestión de Afiliados (GridView) -->
    <div class="ms-panel ms-panel-fh border-indigo"> <!-- Usando ms-panel y borde indigo -->
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
                                'options' => ['placeholder' => 'Seleccionar tipo'],
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
                                'options' => ['placeholder' => 'Seleccionar clínica'],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ]) : null,
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'contentOptions' => ['class' => 'text-center'],
                            'visible' => $isAdmin, // Only show for admin roles
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
                            'visible' => $isAdmin, // Only show for admin roles
                        ],
                        // REMOVED: Duplicate "Clínica" column that was next to "Asesor"
                        // This column was showing the clinic name again without filter

                        [
                            'attribute' => 'estatus_solvente',
                            'format' => 'Html',
                            'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                            'value' => function($model) {
                                 $isTrue = $model->estatus_solvente;
                                 return $isTrue == "Si" ? '<p class="status-badge active">Sí</p>' : '<p class="status-badge inactive">No</p>';
                            },
                            /** 'filter' => [0 => 'No', 1 => 'Sí'],**/
                            'filter' => ['Si' => 'Sí', 'No' => 'No']
                        ],
                        
                        // Columna de Acciones - Mantenida exactamente como se solicitó
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'header' => 'ACCIONES',
                            'template' => '<div class="d-flex justify-content-center gap-0">{view}{update}{siniestro}{pagos}</div>',
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
                                'siniestro' => function ($url, $model, $key) use ($permisos, $clinica, $rol) { // Pasar $permisos y $clinica
                                    if ($permisos == true || $rol == 'COORDINADOR-CLINICA') {
                                    $params = ['/sis-siniestro/index', 'user_id' => $model->id];
                                    if ($clinica && $clinica->id !== null) {
                                        $params['clinica_id'] = $clinica->id;
                                    }

                                    if($model->clinica_id){
                                    return Html::a(
                                        '<i class="fas fa-address-card ms-text-primary"></i>',
                                        Url::to($params), // Asegurar clinica_id condicionalmente
                                        [
                                            'title' => 'Siniestros',
                                            'class' => 'btn-action view'
                                        ]
                                    );}
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