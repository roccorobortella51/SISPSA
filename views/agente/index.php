<?php

// Importaciones necesarias
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView; // Asegúrate de tener kartik/yii2-grid instalado
use yii\grid\ActionColumn;
use app\models\Agente; // Asegúrate de que tu modelo Agente esté correctamente importado

/**
 * @var yii\web\View $this
 * @var app\models\AgenteSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 */

// --- BREADCRUMBS ---
$this->params['breadcrumbs'][] = ['label' => 'AGENCIAS', 'url' => ['index']];
// --- FIN ---

$this->title = 'GESTIÓN DE AGENCIAS'; // Título para la página y breadcrumbs

?>
<div class="row" style="margin:3px !important;">
   
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">


        <div class="ms-panel-header d-flex justify-content-between align-items-center mb-3">
            <h1 class="m-0"><?= Html::encode($this->title) ?></h1>

            <div>
                <?= Html::a('<i class="fas fa-plus"></i> CREAR NUEVA AGENCIA', ['create'], ['class' => 'btn btn-outline-primary btn-lg']) ?>
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
                            'class' => 'table table-striped table-bordered table-hover table-sm'
                        ],
                        'options' => [
                            'class' => 'grid-view-container table-responsive',
                        ],

                        'columns' => [
                            // Nombre (asumimos 'nom' como el atributo para el nombre del agente)
                            [
                                'attribute' => 'nom', // **VERIFICA que 'nom' es el campo correcto para el nombre**
                                'label' => 'Nombre', // Etiqueta visible en la cabecera
                                'format' => 'ntext',
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'options' => ['style' => 'width: 250px;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar nombre',
                                    'class' => 'form-control form-control-lg text-center',
                                ],
                            ],

                            // [
                            //     'attribute' => 'rif', 
                            //     'label' => 'RIF',     // 
                            //     'headerOptions' => ['style' => 'color: white!important;'], 
                            //     'filterInputOptions' => [
                            //         'placeholder' => 'Buscar RIF',
                            //         'class' => 'form-control form-control-lg text-center',
                            //     ],
                            // ],

                            // Propietario (asumimos 'idusuariopropietario'. Si necesitas el nombre real,
                            // tu modelo 'Agente' necesitará una relación o un campo 'value' aquí)
                            [
                                'attribute' => 'propietario.username', // CORRECTO, si 'username' es donde está el nombre en el modelo User
                                'label' => 'Propietario',
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar propietario',
                                    'class' => 'form-control form-control-lg text-center',
                                ],
                                // Opcional: para manejar el caso de que no haya propietario y no mostrar error, puedes usar 'value':
                                'value' => function($model) {
                                    return $model->propietario ? $model->propietario->username : 'No asignado';
                                }
                            ],
                            
                            [
                                'attribute' => 'propietarioEmail', // Atributo virtual del AgenteSearch
                                'label' => 'Correo del Propietario', // Etiqueta para el encabezado
                                'value' => function ($model) {
                                    // $model aquí es una instancia de Agente.
                                    // Accedemos a su relación 'propietario' y luego a su 'email'.
                                    return $model->propietario ? $model->propietario->email : 'N/A';
                                },
                            ],
                            // Columna para la cédula del propietario
                            [
                                'attribute' => 'propietarioCedula', // Atributo virtual del AgenteSearch
                                'label' => 'Cédula del Propietario', // Etiqueta para el encabezado
                                'value' => function ($model) {
                                    // $model aquí es una instancia de Agente.
                                    // Accedemos a su relación 'propietario', y luego a los 'userDatos' del propietario.
                                    return ($model->propietario && $model->propietario->userDatos) ? $model->propietario->userDatos->cedula : 'N/A';
                                },
                            ],
               
                            
                            [
                                'attribute' => 'agenteFuerzaCount', 
                                'label' => 'Fuerza de Venta', 
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar fuerza', 
                                    'class' => 'form-control form-control-lg text-center',
                                ],
                                'format' => 'raw', 
                                'value' => function($model) {
                                    
                                    
                                    $count = $model->agenteFuerzaCount; // O $model->getAgenteFuerzaCount() si prefieres la forma explícita
                            
                                    return Html::a(
                                        $count, // El texto del enlace será el número de asesores
                                        ['agente-fuerza/index-by-agente', 'agente_id' => $model->id], // URL a la vista de los asesores de esta agencia
                                        ['title' => 'Ver asesores de esta agencia', 'data-pjax' => '0'] // data-pjax="0" para que no recargue el Pjax del GridView si lo usas
                                    );
                            
                                    // Si solo quieres mostrar el número sin enlace, usa:
                                    // return $count;
                                },
                                'contentOptions' => ['class' => 'text-center'],
                            ],

                           // Columna de Acciones (Ver, Editar, Eliminar)
                      [
                                'class' => ActionColumn::class,
                                'header' => 'ACCIONES',
                                'template' => '<div class="d-flex justify-content-center gap-2">{view}{update}</div>', // Ajustado a gap-2 para un pequeño espacio
                                'options' => ['style' => 'width:80px; min-width:80px;'], // Mantener el ancho de la columna
                                'headerOptions' => ['class' => 'text-white'], // Usando clase CSS en lugar de estilo inline
                                'contentOptions' => ['class' => 'text-center'], // Usando clase CSS en lugar de estilo inline
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fa fa-eye text-primary"></i>', // Icono de ojo, usando text-primary de sipsa.css
                                            Url::to(['view', 'id' => $model->id]),
                                            [
                                                'title' => 'Ver Detalle',
                                                'class' => 'btn btn-sm', // Usando clases de Bootstrap/sipsa.css, sin estilos inline
                                            ]
                                        );
                                    },
                                    'update' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fas fa-pencil-alt text-success"></i>', // Usando text-success de sipsa.css
                                            Url::to(['update', 'id' => $model->id]),
                                            [
                                                'title' => 'Editar',
                                                'class' => 'btn btn-sm', // Usando clases de Bootstrap/sipsa.css, sin estilos inline
                                            ]
                                        );
                                    },
                                    // Si tenías un botón 'delete' y quieres que vuelva, asegúrate de añadirlo aquí con las clases correctas.
                                    // Por ejemplo:
                                    // 'delete' => function ($url, $model, $key) {
                                    //     return Html::a(
                                    //         '<i class="fas fa-trash-alt text-danger"></i>',
                                    //         Url::to(['delete', 'id' => $model->id]),
                                    //         [
                                    //             'title' => 'Eliminar',
                                    //             'class' => 'btn btn-link btn-sm',
                                    //             'data' => [
                                    //                 'confirm' => '¿Está seguro de que desea eliminar este elemento?',
                                    //                 'method' => 'post',
                                    //             ],
                                    //         ]
                                    //     );
                                    // },
                                ],
                            ],
                        ], // Fin de columns
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
