<?php

// Importaciones necesarias
use yii\helpers\Html;
use yii\helpers\Url;
// use yii\widgets\Pjax; // Pjax sigue eliminado
use kartik\grid\GridView;
use yii\grid\SerialColumn; // La columna serial estándar
use yii\grid\ActionColumn; // La columna de acciones estándar
// Las importaciones de Kartik como DateRangePicker ya no son necesarias aquí

// Si usas 'Urls' para encriptar IDs en los botones de acción, asegúrate de que esté importada
// use app\helpers\Urls; // Descomenta si 'Urls' es una clase auxiliar con un namespace

/**
 * @var yii\web\View $this
 * @var app\models\RmClinicaSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 */

$this->title = 'Gestión de Clínicas'; // Título para la página
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="rm-clinica-index">

    <p>
        <?= Html::a('<i class="fas fa-plus"></i> Crear Nueva Clínica', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'id' => 'clinica-grid', // ID único para tu GridView
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel, // Necesario para los filtros de las columnas
        // Opciones de la tabla HTML para celdas más pequeñas
        'tableOptions' => [
            'class' => 'table table-striped table-bordered table-hover table-sm w-100'
            // Ya no necesitas 'thead' => ['class' => 'letrablanca'] aquí si el thead ya tiene esa clase.
            // Opcionalmente, puedes añadir una clase al thead si no la tiene automáticamente:
            // 'class' => 'table table-striped table-bordered table-hover table-sm w-100',
            // 'headerRowOptions' => ['class' => 'letrablanca'], // Esto aplica la clase a la fila <thead><tr>
        ],
    
        'options' => [
            'class' => 'grid-view-container',
            'style' => 'overflow-x: auto;',
        ],
        
        // Contenedor para el scroll horizontal si la tabla es muy ancha
        // La clase 'table-responsive' de Bootstrap también podría usarse en un div externo:
        // <div class="table-responsive"> ... GridView ... </div>
               // Definición de Columnas
        'columns' => [
            // Columna Serial (numeración de filas)
           // Columna Serial (numeración de filas)
[
    'class' => SerialColumn::class,
    'options' => ['style' => 'width: 30px;'], // Esto es para el contenedor <th>/<td>
    'headerOptions' => ['style' => 'color: white!important;'], // Si quieres la cabecera blanca (aunque suele estar vacía)
    //'contentOptions' => ['style' => 'color: white!important;'], // Esto hace que los NÚMEROS (1, 2, 3...) sean blancos
],

            // ID
            [
                'attribute' => 'id',
                'options' => ['style' => 'width: 60px;'], // Ancho con opciones HTML
                'headerOptions' => ['style' => 'color: white!important;'],
            ],
            // Created At (Fecha de Creación)
            [
                'attribute' => 'created_at',
                'label' => 'Fecha Creación',
                'format' => 'datetime', // Formato de fecha y hora
                'options' => ['style' => 'width: 150px;'], // Ancho con opciones HTML
                // El filtro DateRangePicker no es parte del GridView estándar de Yii2.
                // Si necesitas un filtro de fecha, sería un campo de texto simple por defecto.
            ],
            // RIF
            [
                'attribute' => 'rif',
                'contentOptions' => ['style' => 'width: 100px;text-align: center;']
            ], // Ancho con opciones HTML
            // Nombre
            'nombre:ntext',
            // Estado
            'estado:ntext',
            // Dirección
            'direccion:ntext',
            // Teléfono
            'telefono', 
            // Correo
            'correo',
            // Estatus
            // Si necesitas un filtro desplegable para 'estatus' sin Kartik Select2, puedes usar:
            /*
            [
                'attribute' => 'estatus',
                'value' => function($model) {
                    // Tu lógica de transformación de estatus si es necesario
                    return $model->estatus; 
                },
                'filter' => ['Activo' => 'Activo', 'Inactivo' => 'Inactivo'], // Tus opciones de filtro
            ],
            */
            'estatus', 
            
            // Webpage
            'webpage:url', 
            // Instagram
            'rs_instagram',

            // Columna de Acciones (Ver, Editar, Eliminar)
            [
                'class' => ActionColumn::class, // Usamos ActionColumn estándar de Yii2
                'header' => 'ACCIONES',
                'template' => '{update} {view} {delete}', // Mostrar todos los botones
                'options' => ['style' => 'width:120px;'], // Ancho fijo para la columna de botones
                'buttons' => [
                    'update' => function ($url, $model, $key) {
                        // Asumo que 'id' es la clave primaria en tu URL de actualización
                        return Html::a(
                            '<i class="fas fa-edit"></i>',
                            Url::to(['update', 'id' => $model->id]), 
                            ['title' => 'Editar', 'class' => 'btn btn-primary btn-sm']
                        );
                    },
                    'view' => function ($url, $model, $key) {
                        // Asumo que 'id' es la clave primaria en tu URL de vista
                        return Html::a(
                            '<i class="fas fa-eye"></i>',
                            Url::to(['view', 'id' => $model->id]), 
                            ['title' => 'Ver Detalles', 'class' => 'btn btn-info btn-sm']
                        );
                    },
                    'delete' => function ($url, $model, $key) {
                        // Asumo que 'id' es la clave primaria en tu URL de eliminación
                        return Html::a(
                            '<i class="fas fa-trash-alt"></i>',
                            Url::to(['delete', 'id' => $model->id]), 
                            [
                                'title' => 'Eliminar',
                                'data-confirm' => '¿Estás seguro de que quieres eliminar esta clínica?',
                                'data-method' => 'post', 
                                'class' => 'btn btn-danger btn-sm'
                            ]
                        );
                    },
                ],
            ],
        ], // Fin de columns
    ]); ?>

</div>