<?php

// Importaciones necesarias
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\grid\ActionColumn;

/**
 * @var yii\web\View $this
 * @var app\models\RmClinicaSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 */

// --- BREADCRUMBS ---

$this->params['breadcrumbs'][] = ['label' => 'CLINICAS', 'url' => ['index']];
// --- FIN  --- 


$this->title = 'Gestión de Clínicas'; // Este sigue siendo el título para la página y breadcrumbs

?>

<div class="rm-clinica-index">

    <div class="card shadow mb-4">
       <div class="card-header bg-primary text-white py-3 text-center">
            <h4 class="title"><?= Html::encode($this->title) ?></h4>
        </div>
    </div>
    <div class="d-flex justify-content-start mb-4" style="padding-left: 20px;">
        <?= Html::a('<i class="fas fa-plus"></i> CREAR NUEVA CLÍNICA', ['create'], ['class' => 'btn btn-success btn-lg']) ?>
    </div>
    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white py-3 text-center">
            <h4 class="m-0 font-weight-bold">LISTADO DE CLINÍCAS</h4>
        </div>
        <div class="card-body">

            <?= GridView::widget([
                'id' => 'clinica-grid',
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
                    // ID
                    [
                        'attribute' => 'id',
                        'options' => ['style' => 'width: 50px;'],
                        'headerOptions' => ['style' => 'color: white!important;'],
                        // MODIFICACIÓN: Añadir placeholder y centrado para el input de búsqueda
                        'filterInputOptions' => [
                            'placeholder' => 'Búsqueda',
                            'class' => 'form-control text-center', // Añadimos text-center de Bootstrap
                        ],
                    ],

                    // Nombre
                    [
                        'attribute' => 'nombre',
                        'format' => 'ntext',
                        'headerOptions' => ['style' => 'color: white!important;'],
                        'options' => ['style' => 'width: 250px;'],
                        // MODIFICACIÓN: Añadir placeholder y centrado para el input de búsqueda
                        'filterInputOptions' => [
                            'placeholder' => 'Búsqueda',
                            'class' => 'form-control text-center', // Añadimos text-center de Bootstrap
                        ],
                    ],

                    // Teléfono
                    [
                        'attribute' => 'telefono',
                        'options' => ['style' => 'width: 120px;'],
                        'headerOptions' => ['style' => 'color: white!important;'],
                        // MODIFICACIÓN: Añadir placeholder y centrado para el input de búsqueda
                        'filterInputOptions' => [
                            'placeholder' => 'Búsqueda',
                            'class' => 'form-control text-center', // Añadimos text-center de Bootstrap
                        ],
                    ],
                    // Correo
                    [
                        'attribute' => 'correo',
                        'options' => ['style' => 'width: 250px;'],
                        'headerOptions' => ['style' => 'color: white!important;'],
                        // MODIFICACIÓN: Añadir placeholder y centrado para el input de búsqueda
                        'filterInputOptions' => [
                            'placeholder' => 'Búsqueda',
                            'class' => 'form-control text-center', // Añadimos text-center de Bootstrap
                        ],
                    ],

                    // Columna de Acciones - Se mantiene sin cambios para no afectar lo ya logrado
                    [
                        'class' => ActionColumn::class,
                        'header' => 'ACCIONES',
                        'template' => '<div class="d-flex justify-content-center gap-0">{update}{delete}</div>',
                        'options' => ['style' => 'width:55px; min-width:55px;'],
                        'headerOptions' => ['style' => 'color: white!important;'],
                        'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                        'buttons' => [
                            'update' => function ($url, $model, $key) {
                                return Html::a(
                                    '<i class="fas fa-edit"></i>',
                                    Url::to(['update', 'id' => $model->id]),
                                    [
                                        'title' => 'Editar',
                                        'class' => 'btn btn-link btn-sm text-success',
                                        'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;'
                                    ]
                                );
                            },
                            'delete' => function ($url, $model, $key) {
                                return Html::a(
                                    '<i class="fas fa-trash-alt"></i>',
                                    Url::to(['delete', 'id' => $model->id]),
                                    [
                                        'title' => 'Eliminar',
                                        'data-confirm' => '¿Estás seguro de que quieres eliminar esta clínica?',
                                        'data-method' => 'post',
                                        'class' => 'btn btn-link btn-sm text-danger',
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