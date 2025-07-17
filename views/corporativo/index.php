<?php

use app\models\Corporativo;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView; // Usar Kartik GridView
use kartik\grid\ActionColumn; // También de Kartik para consistencia
use kartik\select2\Select2; // Para filtros de Select2
use app\models\RmClinica; // Para el filtro de clínicas asociadas
use app\models\User; // Para el filtro de empleados asociados
use app\models\UserDatos; // Para obtener nombres de empleados en filtros
use yii\helpers\ArrayHelper; // Para mapear datos para Select2

/** @var yii\web\View $this */
/** @var app\models\CorporativoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */


// --- BREADCRUMBS ---
$this->params['breadcrumbs'][] = ['label' => 'Corporativos', 'url' => ['index']];
// --- FIN  ---

$this->title = 'GESTION DE AFILIADOS CORPORATIVOS';
?>

<div class="row" style="margin:3px !important;">
    <input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">

            <div class="ms-panel-header d-flex justify-content-between align-items-center">
                <h1><?= Html::encode($this->title); ?></h1>

                <div>
                    <?= Html::a(
                        '<i class="fas fa-file-excel"></i> CARGAR MASIVOS DE CORPORATIVOS',
                        ['#'], // Ajusta esta ruta si tienes una funcionalidad de carga masiva
                        ['class' => 'btn btn-outline-primary btn-lg me-3']
                    ) ?>
                    <?= Html::a(
                        '<i class="fas fa-plus"></i> CREAR NUEVO CORPORATIVO',
                        ['create'],
                        ['class' => 'btn btn-outline-primary btn-lg']
                    ) ?>
                </div>
            </div>


            <div class="ms-panel-body">
                <div class="table-responsive">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'resizableColumns' => false,
                        'bordered' => false,
                        'responsiveWrap' => false,
                        'persistResize' => false,
                        'columns' => [
                            // COLUMNA SERIAL - Ajustada
                            [
                                'class' => 'kartik\grid\SerialColumn',
                                'headerOptions' => ['class' => 'tu-clase-de-cabecera-de-tema'], // <-- Añade la clase aquí
                            ],

                            'nombre',
                            [
                                'attribute' => 'rif',
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'contentOptions' => ['class' => 'text-center'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Buscar por RIF',
                                    'class' => 'form-control text-center',
                                ],
                            ],
                            'email:email',
                            'telefono',
                            [
                                'attribute' => 'estatus',
                                'label' => 'Estatus',
                                'filter' => Select2::widget([
                                    'model' => $searchModel,
                                    'attribute' => 'estatus',
                                    'data' => [
                                        'Activo' => 'Activo',
                                        'Inactivo' => 'Inactivo',
                                        'Pendiente' => 'Pendiente',
                                    ],
                                    'options' => ['placeholder' => 'Filtrar por estatus...'],
                                    'pluginOptions' => [
                                        'allowClear' => true
                                    ],
                                ]),
                                'contentOptions' => ['style' => 'width: 150px;'],
                            ],
                            // Columna para las clínicas asociadas (conteo y filtro)
                            [
                                'attribute' => 'clinicas_ids', // Usamos el atributo virtual para el filtro
                                'label' => 'Clínicas Asoc.',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $count = count($model->clinicas);
                                    return Html::a($count . ' Clínica(s)', ['view', 'id' => $model->id], ['title' => 'Ver clínicas asociadas']);
                                },
                                'filterType' => GridView::FILTER_SELECT2,
                                'filter' => ArrayHelper::map(RmClinica::find()->orderBy('nombre')->all(), 'id', 'nombre'),
                                'filterWidgetOptions' => [
                                    'pluginOptions' => ['allowClear' => true],
                                ],
                                'filterInputOptions' => ['placeholder' => 'Filtrar por clínicas...'],
                                'headerOptions' => ['style' => 'color: white!important; width:120px; text-align:center;'], // Asegura que el color y alineación del header sean consistentes
                                'contentOptions' => ['style' => 'text-align:center;'],
                            ],
                            // Columna para los empleados asociados (conteo y filtro)
                            [
                                'attribute' => 'users_ids', // Usamos el atributo virtual para el filtro
                                'label' => 'Empleados Asoc.',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $count = count($model->users);
                                    return Html::a($count . ' Empleado(s)', ['view', 'id' => $model->id], ['title' => 'Ver empleados asociados']);
                                },
                                'filterType' => GridView::FILTER_SELECT2,
                                'filter' => ArrayHelper::map(
                                    User::find()->joinWith('userDatos')->orderBy('user_datos.nombres')->all(),
                                    'id',
                                    function($user) {
                                        return ($user->userDatos) ? $user->userDatos->nombres . ' ' . $user->userDatos->apellidos : $user->username;
                                    }
                                ),
                                'filterWidgetOptions' => [
                                    'pluginOptions' => ['allowClear' => true],
                                ],
                                'filterInputOptions' => ['placeholder' => 'Filtrar por empleados...'],
                                'headerOptions' => ['style' => 'color: white!important; width:120px; text-align:center;'], // Asegura que el color y alineación del header sean consistentes
                                'contentOptions' => ['style' => 'text-align:center;'],
                            ],

                            // COLUMNA DE ACCIONES - Ajustada
                            [
                                'class' => ActionColumn::class, // Usar ActionColumn de Kartik
                                'header' => 'ACCIONES',
                                'template' => '<div class="d-flex justify-content-center gap-0">{view}{update}</div>', // Incluye delete
                                'options' => ['style' => 'width:100px; min-width:100px;'], // Ajusta el ancho total de la columna
                                'headerOptions' => ['style' => 'color: white!important; text-align: center;'], // Aplica estilo de texto y centra el encabezado
                                'contentOptions' => ['style' => 'text-align: center; padding: 10px !important;'], // Centra el contenido y ajusta el padding

                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fa fa-eye"></i>',
                                            Url::to(['view', 'id' => $model->id]),
                                            [
                                                'title' => 'Detalle de Corporativo',
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
                                                'title' => 'Editar Corporativo',
                                                'class' => 'btn btn-link btn-sm text-primary',
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