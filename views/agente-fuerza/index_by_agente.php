<?php

use yii\helpers\Html;
use kartik\grid\GridView; // Usaremos Kartick GridView para consistencia si lo tienes instalado
use yii\helpers\Url;
use yii\grid\ActionColumn; // Para la columna de acciones
use yii\web\JqueryAsset; // Asegúrate de tener este 'use' si tu JS de paneles lo requiere
use app\models\AgenteFuerza; // Tu modelo AgenteFuerza
use app\models\Agente; // Tu modelo Agente



/** @var yii\web\View $this */
/** @var app\models\search\AgenteFuerzaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var int $id_agente */
/** @var app\models\Agente $agente */ // ¡Este es el objeto Agente que necesitas!

$this->title = 'FUERZA DE VENTA'; //PARA AGENTE: ' . $agente->nom;
$this->params['breadcrumbs'][] = ['label' => 'AGENCIAS', 'url' => ['agente/index']];
$this->params['breadcrumbs'][] = ['label' => $agente->nom, 'url' => ['agente/update', 'id' => $agente->id]];
$this->params['breadcrumbs'][] = $this->title;


?>

<!-- <div class="row" style="margin:3px !important;">
    <div class="col-md-12 text-end">
        <div class="float-right" style="margin-bottom:10px;">
            ('<i class="fas fa-plus"></i> CREAR MIEMBRO DE FUERZA DE VENTA', ['agente-fuerza/create', 'agente_id' => $id_agente], ['class' => 'btn btn-outline-primary btn-lg'])
            ('<i class="fas fa-undo"></i> Volver', ['agente/update', 'id' => $agente->id], ['class' => 'btn btn-info btn-lg'])
        </div>
    </div> -->

    <?php


if (!$agente->isNewRecord) { ?>
    <div class="col-xl-12 col-md-12 mb-3">
        <div class="row row-cols-1 row-cols-md-4 g-3">
            <div class="col">
                <?= Html::a(
                    '<i class="fas fa-building"></i> AGENCIAS',
                    ['agente/index'],
                    ['class' => 'btn btn-secondary btn-lg w-100']
                ) ?>
            </div>

            <div class="col">
                <?= Html::a(
                    '<i class="fas fa-undo"></i> VOLVER PARA AGENCIA PRINCIPAL',
                    ['agente/update', 'id' => $agente->id],
                    ['class' => 'btn btn-info btn-lg w-100']
                ) ?>
            </div>

            <div class="col">
                <?= Html::a(
                    '<i class="fas fa-plus"></i> CREAR UN MIEMBRO DE FUERZA DE VENTA',
                    ['agente-fuerza/create', 'agente_id' => $agente->id], // Asegúrate que $agente->id esté disponible
                    ['class' => 'btn btn-outline-primary btn-lg w-100'] // Usa btn-outline-primary para un estilo diferente
                ) ?>
            </div>

            <div class="col">
                <?= Html::a(
                    '<i class="fas fa-plus"></i> CARGA MASIVA(EN CONSTRUCCION)',
                    '#',//['agente-fuerza/create', 'agente_id' => $agente->id], // Asegúrate que $agente->id esté disponible
                    ['class' => 'btn btn-primary btn-lg w-100'] // Usa btn-outline-primary para un estilo diferente
                ) ?>
            </div>
        </div>
    </div>
<?php } ?>
    
    
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header">
                <h1><?= Html::encode($this->title) ?></h1> </div>
                <div class="ms-panel-body">
                    <div class="table-responsive">
                        <?= GridView::widget([
                            'dataProvider' => $dataProvider,
                            'filterModel' => $searchModel,
                            'layout' => "{items}{pager}", // Puedes mantener o ajustar según necesites {summary}{items}{pager}
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
                            // ['class' => 'kartik\grid\SerialColumn'], // Usamos SerialColumn de Kartik para consistencia
                            
                            // Asegúrate de que estos atributos existan en tu modelo AgenteFuerza
                            // y sean relevantes para mostrar en la tabla
                           
                            
                            [
                                'attribute' => 'usuario.nombre_completo',
                                'label' => 'Nombre',
                                'value' => function($model) {
                                    
                                    if($model->user){
                                        
                                        //return $model->user->username;
                                        return $model->user->userDatos->nombres;
                                    }
                                    
                                },
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar nombre',
                                    'class' => 'form-control form-control-lg text-center',
                                ],
                                'options' => ['style' => 'width: 30%;'], // <--- para ancho de la celda
                            ],
                            
                            
                            [
                                'label' => 'Correo Electrónico',
                                'value' => function($model) {
                                    if ($model->user && $model->user->userDatos) {
                                        return $model->user->userDatos->email;
                                    }
                                    return 'No disponible';
                                },
                                // Asegúrate de que este 'attribute' sea correcto para el SearchModel
                                'attribute' => 'user.userDatos.email',
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar correo',
                                    'class' => 'form-control form-control-lg text-center',
                                ],
                            ],

                            [
                                'label' => 'Teléfono', // Título de la columna
                                'value' => function($model) {
                                    // Asumiendo que la columna en user_datos es 'telefono' o 'telf'
                                    // Ajusta 'telefono' al nombre real de tu columna
                                    return $model->user?->userDatos?->telefono ?? 'No disponible';
                                },
                                // Asegúrate de que este 'attribute' sea correcto para el SearchModel
                                'attribute' => 'user.userDatos.telefono', // Cambia 'telefono' si el nombre de tu columna es diferente
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar teléfono',
                                    'class' => 'form-control form-control-lg text-center',
                                ],
                            ],
                           // Columna de Acciones (Ver, Editar, Eliminar)
                            [
                                'class' => ActionColumn::class,
                                'header' => 'ACCIONES',
                                'template' => '<div class="d-flex justify-content-center gap-0">{view}{update}</div>',
                                'options' => ['style' => 'width:80px; min-width:80px;'],
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'contentOptions' => ['style' => 'text-align: center; padding: 10px !important;'],
                                'buttons' => [
                                    'view' => function ($url, $model, $key) { 
                                        return Html::a(
                                            '<i class="fa fa-eye"></i>', 
                                            Url::to(['agente-fuerza/view', 'id' => $model->id]), // URL a la acción 'view' de tu controlador AgenteFuerza
                                            [
                                                'title' => 'Ver Detalles',
                                                'class' => 'btn btn-link btn-sm text-info', // Estilo de botón para ver (azul)
                                                'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;'
                                            ]
                                        );
                                    },
                                    'update' => function ($url, $model, $key) use ($id_agente, $agente) {
                                        return Html::a(
                                            '<i class="fas fa-pencil-alt ms-text-primary"></i>',
                                            Url::to(['agente-fuerza/update', 'id' => $model->id]),
                                            [
                                                'title' => 'Editar',
                                                'class' => 'btn btn-link btn-sm text-success',
                                                'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;'
                                            ]
                                        );
                                    },
                                    // 'delete' => function ($url, $model, $key) {
                                    //     return Html::a(
                                    //         '<i class="fas fa-trash-alt"></i>',
                                    //         Url::to(['agente-fuerza/delete', 'id' => $model->id]),
                                    //         [
                                    //             'title' => 'Eliminar',
                                    //             'class' => 'btn btn-link btn-sm text-danger',
                                    //             'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;',
                                    //             'data-confirm' => '¿Estás seguro de que quieres eliminar este miembro de la fuerza de venta?',
                                    //             'data-method' => 'post'
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

