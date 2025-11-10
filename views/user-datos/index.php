
<?php

use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\UserSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */


// --- BREADCRUMBS ---

$this->params['breadcrumbs'][] = ['label' => 'Afiliados', 'url' => ['index']];
// --- FIN  --- 


$this->title = 'Gestión de Afiliados'; // Este sigue siendo el título para la página y breadcrumbs
?>
<div class=row style="margin:3px !important;">
<input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />
    <div class="col-md-12 text-end">
        
    </div>
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">




        <div class="ms-panel-header d-flex justify-content-between align-items-center">
    <h1><?= $this->title = 'Gestión de Afiliados'; ?></h1>

    <div> 
        <?= Html::a(
            '<i class="fas fa-file-excel"></i> CARGAR MASIVOS DE AFILIADOS', 
            ['#'], 
            // CAMBIO AQUÍ: Añadimos 'me-3' (Bootstrap 5) o 'mr-3' (Bootstrap 4)
            ['class' => 'btn btn-outline-primary btn-lg me-3']
        ) ?> 
        <?= Html::a(
            '<i class="fas fa-plus"></i> CREAR NUEVO AFILIADO DEL SÍSTEMA', 
            ['create'], 
            // Este es el último botón, no necesita margen a la derecha
            ['class' => 'btn btn-outline-primary btn-lg'] 
        ) ?> 
    </div>
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

                            //'id',
                            //'created_at',
                            //'user_id',
                             [
                                'attribute' => 'created_at',
                                'hAlign' => 'center',
                                'vAlign' => 'middle',
                                'value' => function ($model, $key, $index, $widget) {
                                    return $model->created_at;
                                },
                                'width' => '12%',
                                'filterType' => \kartik\grid\GridView::FILTER_DATE_RANGE,
                                'format' => 'date',
                                'filterInputOptions' => ['placeholder' => 'Seleccione un rango de fechas', 'class' => 'form-control'],
                                'filterWidgetOptions' => [
                                    'presetDropdown' => true,
                                    'pluginOptions' => [
                                        'locale' => ['format' => 'YYYY/MM/DD'],
                                        'separator' => ' A ',
                                        'placeholder' => 'Fecha de creación',
                                        'placeholder' => "Filter",
                                    ],
                                    'pluginEvents' => [
                                        "apply.daterangepicker" => "function() { $('.grid-view').yiiGridView('applyFilter') }",
                                    ]
                                ],
                            ],

                            [
                                'label' => 'Nombre Completo', 
                                'attribute' => 'nombres', 
                                'value' => function ($model) {
                                    
                                    return $model->nombres . ' ' . $model->apellidos;
                                },
                                'format' => 'ntext',
                                'headerOptions' => ['style' => 'color: white!important;'],
                                // Opcional: Si quieres que el filtro de búsqueda busque tanto en nombres como en apellidos
                                // necesitarías ajustar tu UserDatosSearch, pero por ahora, el filtro predeterminado
                                // seguirá buscando solo en 'nombres'.
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
                            /*[
                                'label' => 'Fecha de Nacimiento',
                                'attribute' => 'fechanac', 
                                'format' => 'date', // Mantiene el formato de fecha de Yii (ej. 10 de julio de 2025)
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'options' => ['style' => 'width: 150px;'], 
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar por fecha',
                                    'class' => 'form-control text-center', 
                                ],
                            ],*/

                            [
                                'attribute' => 'fechanac',
                                'label' => 'Fecha de Nacimiento',
                                'hAlign' => 'center',
                                'vAlign' => 'middle',
                                'value' => function ($model, $key, $index, $widget) {
                                    return $model->created_at;
                                },
                                'width' => '12%',
                                'filterType' => \kartik\grid\GridView::FILTER_DATE_RANGE,
                                'format' => 'date',
                                'filterInputOptions' => ['placeholder' => 'Seleccione un rango de fechas', 'class' => 'form-control'],
                                'filterWidgetOptions' => [
                                    'presetDropdown' => true,
                                    'pluginOptions' => [
                                        'locale' => ['format' => 'YYYY/MM/DD'],
                                        'separator' => ' A ',
                                        'placeholder' => 'Fecha de Nacimiento',
                                        'placeholder' => "Filter",
                                    ],
                                    'pluginEvents' => [
                                        "apply.daterangepicker" => "function() { $('.grid-view').yiiGridView('applyFilter') }",
                                    ]
                                ],
                            ],


                            /*[
                                'label' => 'clinica', 
                                'attribute' => 'clinica_id', 
                                'value' => function ($model) {
                                    $clinica = '';
                                    $plan = '';

                                    if($model->clinica){
                                        $clinica = 'Clinica: '.  $model->clinica->nombre;
                                    }

                                    if($model->plan){
                                        $plan = 'Plan: ' .$model->plan->nombre;
                                    }
                                        return $clinica . '<br> ' . $plan;
                                    
                                    
                                    
                                },
                                'format' => 'html',
                                'headerOptions' => ['style' => 'color: white!important;'],
                                // Opcional: Si quieres que el filtro de búsqueda busque tanto en nombres como en apellidos
                                // necesitarías ajustar tu UserDatosSearch, pero por ahora, el filtro predeterminado
                                // seguirá buscando solo en 'nombres'.
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar por nombre',
                                    'class' => 'form-control text-center',
                                ],
                            ],*/

                            [
                                'attribute' => 'clinica_id',
                                'vAlign' => 'middle',
                                'label' => 'Clínicas',
                                'value' => function ($model) {
                                    $clinica = '';
                                    $plan = '';

                                    if($model->clinica){
                                        $clinica = 'Clinica: '.  $model->clinica->nombre;
                                    }

                                    if($model->plan){
                                        $plan = 'Plan: ' .$model->plan->nombre;
                                    }
                                        return $clinica . '<br> ' . $plan;
                                    
                                    
                                    
                                },
                                'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                                 'filter' => \yii\helpers\ArrayHelper::map(\app\models\RmClinica::find()->orderBy('nombre')->asArray()->all(), 'id', 'nombre'),
                                'filterWidgetOptions' => [
                                    'pluginOptions' => ['allowClear' => true],
                                ],
                                'filterInputOptions' => ['placeholder' => 'Clinicas'],
                                'format' => 'raw',
                            ],

                            
                           
                            //'selfie:ntext',
                            //'telefono:ntext',
                            //'estado:ntext',
                            //'role:ntext',
                            //'estatus:ntext',
                            //'imagen_identificacion:ntext',
                            //'qr:ntext',
                            //'paso',
                            //'video:ntext',
                            //'ciudad:ntext',
                            //'municipio:ntext',
                            //'parroquia:ntext',
                            //'direccion:ntext',
                            //'codigoValidacion:ntext',
                            //'clinica_id',
                            //'plan_id',
                            //'apellidos:ntext',
                            
                            //'contrato_id',
                            //'asesor_id',
                            //'deleted_at',
                            //'updated_at',
                            //'ver_cedula:ntext',
                            //'ver_foto:ntext',
                            //'session_id',
                            //'cedula',
                            //'tipo_cedula:ntext',
                            //'tipo_sangre:ntext',
                            //'estatus_solvente:ntext',
                            //'user_login_id',
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
                                                        'title' => 'Detalle de Usuario',
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
                                                        'title' => 'Editar Usuario',
                                                        'class' => 'btn btn-link btn-sm text-success',
                                                        'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;'
                                                    ]
                                                );
                                            },
                                            'delete' => function ($url, $model, $key) {
                                                return Html::a(
                                                    '<i class="far fa-trash-alt ms-text-danger"></i>',
                                                    Url::to(['delete', 'id' => $model->id]),
                                                    [
                                                        'title' => 'Eliminar Usuario',
                                                        'data-confirm' => '¿Estás seguro de que quieres eliminar esta clínica?',
                                                        'data-method' => 'post',
                                                        'class' => 'btn btn-link btn-sm text-danger',
                                                        'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;'
                                                    ]
                                                );
                                            },
                                            
                                        ],
                            ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</div>
