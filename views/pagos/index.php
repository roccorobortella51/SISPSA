<?php

use app\models\Pagos;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\switchinput\SwitchInput;
use yii\helpers\ArrayHelper; // Necesario para DropdownList en el filtro

/** @var yii\web\View $this */
/** @var app\models\PagosSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'PAGOS';
$this->params['breadcrumbs'][] = $this->title;

// Lista de estados para el filtro de la columna 'estatus'
$estatusList = [
    'Conciliado' => 'Conciliado',
    'Por Conciliar' => 'Por Conciliar', // Asumiendo que 'Por Conciliar' se mapea a 'Pendiente' o similar en la DB
];
?>
<div class="pagos-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        
        // --- CONFIGURACIÓN DE KARTIK GRIDVIEW ---
        'pjax' => true, // Habilitar Pjax para recargas rápidas
        'striped' => true,
        'condensed' => true,
        'responsive' => true,
        'hover' => true,
        'panel' => [
            'type' => GridView::TYPE_PRIMARY,
            'heading' => '<i class="fas fa-money-check-alt"></i> ' . $this->title,
            'before' => false, // No mostrar nada antes de la grilla
            'after' => false, // No mostrar nada después de la grilla
        ],
        
        // Barra de herramientas: Añadir botón de creación y exportación
        'toolbar' => [
            [
                'content' =>
                    Html::a('Crear Pagos', ['create'], ['class' => 'btn btn-success']) .
                    Html::a('<i class="fas fa-redo"></i>', ['index'], [
                        'class' => 'btn btn-outline-secondary',
                        'title' => Yii::t('kvgrid', 'Reset Grid'),
                        'data-pjax' => 0, 
                    ])
            ],
            '{export}',
            '{toggleData}',
        ],
        // --- FIN CONFIGURACIÓN DE KARTIK GRIDVIEW ---

        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            
            // Columna de Usuario (Permite búsqueda por nombre en PagosSearch)
            [
                'attribute' => 'nombreUsuario', 
                'value' => function ($model) {
                    return $model->userDatos ? $model->userDatos->nombres . ' ' . $model->userDatos->apellidos : 'N/A';
                },
                'label' => 'Usuario',
                // 'group' => true, // COMENTADO para mostrar todas las filas individualmente.
            ],

            [
                'attribute' => 'cedulaUsuario', // Usamos el nuevo atributo virtual
                'value' => function ($model) {
                    // Accedemos a la cédula a través de la relación userDatos
                    return $model->userDatos ? $model->userDatos->cedula : 'N/A';
                },
                'label' => 'Cédula/DNI',
                'filterOptions' => ['style' => 'width: 10%'], 
            ],

            // Columna Solvente (Muestra estatus de usuario relacionado)
            [
                'label' => 'Solvente',
                'value' => function ($model) {
                    // Usar badge para mejor visualización
                    $isSolvente = $model->userDatos ? $model->userDatos->estatus_solvente : 'No';
                    if ($isSolvente == 'SI') {
                        return '<span class="badge badge-success">SI</span>';
                    }
                    return '<span class="badge badge-danger">No</span>';
                },
                'format' => 'raw',
                'filter' => ['SI' => 'SI', 'No' => 'No'],
            ],
            
            'numero_referencia_pago:ntext',
            
            // Fecha de Pago (Permite búsqueda por texto)
            [
                'attribute' => 'fecha_pago',
                'format' => 'date', 
            ],
            
            // Monto Pagado USD
            [
                'attribute' => 'monto_pagado',
                'value' => function ($model) {
                    return $model->monto_pagado . ' USD';
                },
                'label' => 'Monto Pagado USD',
                'hAlign' => GridView::ALIGN_RIGHT,
            ],
            
            // Monto Pagado Bs (Permite búsqueda por texto)
            [
                'attribute' => 'monto_usd',
                'value' => function ($model) {
                    return $model->monto_usd . ' Bs';
                },
                'label' => 'Monto Pagado Bs',
                'hAlign' => GridView::ALIGN_RIGHT,
            ],
            
            // Columna de Conciliación con SwitchInput y Filtro Dropdown
            [
                'attribute' => 'estatus',
                'format' => 'raw',
                'value' => function ($model) {
                    // Convertir el valor de texto a booleano
                    $isActive = ($model->estatus == 'Conciliado' || $model->estatus == '1' || $model->estatus == 'Activo');
                    
                    return SwitchInput::widget([
                        'name' => 'estatus_' . $model->id,
                        'value' => $isActive,
                        'pluginOptions' => [
                            'size' => 'large',
                            'onText' => 'Conciliado',
                            'offText' => 'Por Conciliar',
                            'onColor' => 'success',
                            'offColor' => 'danger',
                        ],
                        // Se mantiene la lógica AJAX del usuario
                        'pluginEvents' => [
                            'switchChange.bootstrapSwitch' => "function(event, state) {
                                var currentRow = $(event.target).closest('tr');
                                // Buscar el valor actual de Solvente (Índice 2, después de Serial y Usuario)
                                var solventeCell = currentRow.find('td').eq(2); 

                                $.ajax({
                                    url: '" . Url::to(['/pagos/updatestatus']) . "',
                                    type: 'POST',
                                    data: {
                                        id: " . $model->id . ",
                                        status: state ? 1 : 0,
                                        _csrf: '" . Yii::$app->request->getCsrfToken() . "'
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            // Actualizar la columna de solvente en tiempo real
                                            var newSolventeStatus = state ? '<span class=\"badge badge-success\">SI</span>' : '<span class=\"badge badge-danger\">No</span>';
                                            solventeCell.html(newSolventeStatus);
                                        } else {
                                            // Revertir el cambio si falla
                                            $(event.target).bootstrapSwitch('state', !state, true);
                                            alert('Error: ' + response.error);
                                        }
                                    },
                                    error: function(xhr) {
                                        $(event.target).bootstrapSwitch('state', !state, true);
                                        alert('Error del servidor: ' + xhr.responseText);
                                    }
                                });
                            }"
                        ]
                    ]);
                },
                'label' => 'Conciliacion',
                // Añadir filtro de dropdown
                'filter' => $estatusList,
                'filterType' => GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'options' => ['placeholder' => 'Filtrar estatus...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ],
            ],
            
            // Columna de Acciones (CORREGIDO: Eliminamos hAlign/vAlign, mantenemos ancho 120px)
            [
                'class' => ActionColumn::class, // Usar ::class es la práctica moderna
                // Establecemos un ancho mayor para la columna (120px) y centrado CSS
                'headerOptions' => ['style' => 'width: 120px; text-align: center;'], 
                'contentOptions' => ['style' => 'width: 120px; min-width: 120px; text-align: center;'], 
                'urlCreator' => function ($action, Pagos $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 },
            ],
        ],
    ]); ?>

</div>
