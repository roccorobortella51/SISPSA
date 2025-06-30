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
    <div class="col-md-12 text-end">
        <div class="float-right" style="margin-bottom:10px;">
            <?= Html::a('<i class="fas fa-plus"></i> CREAR NUEVA AGENCIA', ['create'], ['class' => 'btn btn-outline-primary btn-lg']) ?>
        </div>
    </div>
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header">
                <h1><?= Html::encode($this->title) ?></h1>
            </div>
            <div class="ms-panel-body">
                <div class="table-responsive">
                    <?= GridView::widget([
                        'id' => 'clinica-grid', // <--- ¡¡¡MODIFICACIÓN CLAVE AQUÍ!!! ANTES ERA 'agente-grid'
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'layout' => "{items}{pager}",

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

                            // Propietario (asumimos 'idusuariopropietario'. Si necesitas el nombre real,
                            // tu modelo 'Agente' necesitará una relación o un campo 'value' aquí)
                            [
                                'attribute' => 'idusuariopropietario', // **VERIFICA que este es el campo correcto**
                                'label' => 'Propietario', // Etiqueta visible en la cabecera
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar propietario ID',
                                    'class' => 'form-control form-control-lg text-center',
                                ],
                                /* Si 'idusuariopropietario' es una FK y quieres mostrar el nombre del usuario:
                                'value' => function ($model) {
                                    // Asegúrate de que 'getUsuarioPropietario()' sea el nombre de tu relación en el modelo Agente
                                    return $model->usuarioPropietario->nombre_completo ?? 'N/A'; // Suponiendo una relación y un campo 'nombre_completo' en el modelo de usuario
                                },
                                // Y si quieres filtrar por el nombre del propietario (requiere ajustes en AgenteSearch):
                                // 'filter' => \yii\helpers\ArrayHelper::map(\app\models\User::find()->all(), 'id', 'nombre_completo'),
                                */
                            ],

                            // Porcentaje (asumimos 'por_venta' como un ejemplo de porcentaje)
                            [
                                'attribute' => 'por_venta', // **VERIFICA que este es el campo correcto para porcentaje**
                                'label' => 'Porcentaje', // Etiqueta visible en la cabecera
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar %',
                                    'class' => 'form-control form-control-lg text-center',
                                ],
                            ],

                            // Fuerza de Venta (este atributo NO estaba en tu lista original de columnas de agente,
                            // **DEBES REEMPLAZAR 'fuerza_venta_atributo' CON EL NOMBRE REAL DE TU COLUMNA**)
                            [
                                'attribute' => 'fuerza_venta_atributo', // <--- ¡¡¡IMPORTANTE!!! CAMBIA ESTO POR EL CAMPO REAL DE TU BD
                                'label' => 'Fuerza de Venta', // Etiqueta visible en la cabecera
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar fuerza',
                                    'class' => 'form-control form-control-lg text-center',
                                ],
                                // Si es un campo calculado o una relación, puedes usar 'value':
                                /*
                                'value' => function($model) {
                                    // Lógica para obtener el valor de "fuerza de venta"
                                    return 'Valor Calculado';
                                }
                                */
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
                                            Url::to(['view', 'id' => $model->id]),
                                            [
                                                'title' => 'Ver Detalle del Agente',
                                                'class' => 'btn btn-link btn-sm text-info',
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
                        ], // Fin de columns
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
