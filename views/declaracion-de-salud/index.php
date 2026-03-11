<?php

// Importaciones necesarias
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use yii\widgets\ActiveForm;
use app\components\UserHelper;
use kartik\widgets\SwitchInput;
use kartik\select2\Select2;
use app\models\UserDatos; // Add this import for type hinting
use app\models\DeclaracionDeSalud; // Add this import for type hinting
use app\models\DeclaracionDeSaludSearch; // Add this import for type hinting

/**
 * @var yii\web\View $this
 * @var app\models\DeclaracionDeSaludSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\UserDatos $afiliado
 * @var app\models\DeclaracionDeSalud $model
 */

// --- BREADCRUMBS ---

$this->params['breadcrumbs'][] = ['label' => 'AFILIADO', 'url' => ['/user-datos/update', 'id' => $afiliado->id]];
// --- FIN  --- 


$this->title = 'Gestión de Declaración de Salud del Afiliado'; // Este sigue siendo el título para la página y breadcrumbs

$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol == 'DIRECTOR-COMERCIALIZACIÓN' || $rol == 'GERENTE-VENTAS' || $rol = 'Administrador-clinica');
?>

<div class=row style="margin:3px !important;">

    <?php if ($permisos) { ?>
        <!-- Nuevo contenedor para los botones "Volver" y "Crear Declaración de Salud" -->
        <div class="col-md-12 d-flex justify-content-center gap-3" style="margin-bottom:10px;">
            <?= Html::a('<i class="fas fa-undo"></i> Volver', ['/user-datos/update', 'id' => $afiliado->id], ['class' => 'btn btn-secondary btn-lg rounded-pill px-7 shadow-sm']) ?>
            <?= Html::a('<i class="fas fa-plus"></i> CREAR DECLARACIÓN DE SALUD', ['create', 'user_id' => $afiliado->id], ['class' => 'btn btn-outline-primary btn-lg']) ?>
        </div>
    <?php } ?>


</div>
<div class="col-xl-12 col-md-12">
    <div class="ms-panel ms-panel-fh">
        <div class="ms-panel-header">
            <div class="ms-panel-header">
                <h1>Gestión de Declaración de Salud del Afiliado: <?= $this->title = 'Nombre completo: ' . $afiliado->nombres . " " . $afiliado->apellidos . ', Cédula: ' . $afiliado->cedula; ?></h1>
            </div>

        </div>
        <div class="ms-panel-body">
            <div class="table-responsive">

                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => null, // Removed the filterModel to hide search fields
                    'layout' => "{items}{pager}",
                    'columns' => [
                        // SerialColumn eliminado - ya no aparece la columna #

                        //'id',
                        //'created_at',
                        //'p1_sino:ntext',
                        //'p1_especifica:ntext',
                        //'p2_sino:ntext',
                        //'p2_especifica:ntext',
                        //'p3_sino:ntext',
                        //'p3_especifica:ntext',
                        //'p4_sino:ntext',
                        //'p4_especifica:ntext',
                        //'p5_sino:ntext',
                        //'p5_especifica:ntext',
                        //'p6_sino:ntext',
                        //'p6_especifica:ntext',
                        //'p7_sino:ntext',
                        //'p7_especifica:ntext',
                        //'p8_sino:ntext',
                        //'p8_especifica:ntext',
                        //'p9_sino:ntext',
                        //'p9_especifica:ntext',
                        //'p10_sino:ntext',
                        //'p10_especifica:ntext',
                        //'p11_sino:ntext',
                        //'p11_especifica:ntext',
                        //'p12_sino:ntext',
                        //'p12_especifica:ntext',
                        //'p13_sino:ntext',
                        //'p13_especifica:ntext',
                        //'p14_sino:ntext',
                        //'p14_especifica:ntext',
                        //'p15_sino:ntext',
                        //'p15_especifica:ntext',
                        //'p16_sino:ntext',
                        //'p16_especifica:ntext',
                        //'deleted_at',
                        //'updated_at',
                        //'ver_usuario_id',

                        // Campo de Observación - AHORA MUCHO MÁS ANCHO
                        [
                            'attribute' => 'ver_observacion',
                            'label' => 'Ver Observación',
                            'format' => 'ntext',
                            'headerOptions' => ['style' => 'color: white!important; width: 50%;'],
                            'contentOptions' => ['style' => 'max-width: 500px; white-space: normal; word-wrap: break-word;'],
                        ],

                        // Campos reducidos - Estatura
                        [
                            'attribute' => 'estatura',
                            'label' => 'Estatura',
                            'format' => 'ntext',
                            'headerOptions' => ['style' => 'color: white!important; width: 5%;'],
                            'contentOptions' => ['style' => 'text-align: center;'],
                        ],

                        // Campos reducidos - Peso
                        [
                            'attribute' => 'peso',
                            'label' => 'Peso',
                            'format' => 'ntext',
                            'headerOptions' => ['style' => 'color: white!important; width: 5%;'],
                            'contentOptions' => ['style' => 'text-align: center;'],
                        ],

                        // NUEVA COLUMNA: EDAD CALCULADA DESDE fechanac - TAMAÑO REDUCIDO
                        [
                            'attribute' => 'edad',
                            'label' => 'Edad',
                            'format' => 'ntext',
                            'value' => function ($model) {
                                /** @var \app\models\DeclaracionDeSalud $model */
                                // Verificar si existe el afiliado y tiene fecha de nacimiento
                                if ($model->user && $model->user->fechanac) {
                                    $fechaNacimiento = new \DateTime($model->user->fechanac);
                                    $hoy = new \DateTime();
                                    $edad = $hoy->diff($fechaNacimiento)->y;
                                    return $edad . ' años';
                                }
                                // Si no hay fecha de nacimiento, retornar un mensaje o vacío
                                return 'N/A';
                            },
                            'headerOptions' => ['style' => 'color: white!important; width: 5%;'],
                            'contentOptions' => ['style' => 'text-align: center;'],
                        ],

                        [
                            'class' => 'yii\grid\ActionColumn',
                            'header' => 'ACCIONES',
                            'template' => '<div class="d-flex justify-content-center gap-0">{view}{salud}{update}</div>',
                            'options' => ['style' => 'width:12%; min-width:120px;'],
                            'headerOptions' => ['style' => 'color: white!important; width: 12%;'],
                            'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                            'buttons' => [
                                'view' => function ($url, $model, $key) {
                                    /** @var \app\models\DeclaracionDeSalud $model */
                                    return Html::a(
                                        '<i class="fa fa-eye"></i>',
                                        Url::to(['view', 'id' => $model->id]),
                                        [
                                            'title' => 'Detalle de la Declaración de Salud',
                                            'class' => 'btn-action view'
                                        ]
                                    );
                                },
                                'salud' => function ($url, $model, $key) {
                                    /** @var \app\models\DeclaracionDeSalud $model */
                                    return Html::a(
                                        '<i class="fas fa-file-pdf ms-text-danger"></i>',
                                        Url::to(['generar-pdf', 'id' => $model->id]),
                                        [
                                            'title' => 'Declaración de salud',
                                            'class' => 'btn-action view',
                                            'target' => '_blank'
                                        ]
                                    );
                                },
                                'update' => function ($url, $model, $key) use ($permisos) {
                                    /** @var \app\models\DeclaracionDeSalud $model */
                                    if ($permisos) {
                                        return Html::a(
                                            '<i class="fas fa-pencil-alt ms-text-primary"></i>',
                                            Url::to(['update', 'id' => $model->id]),
                                            [
                                                'title' => 'Editar',
                                                'class' => 'btn-action view'
                                            ]
                                        );
                                    }
                                    return '';
                                },
                                /*'delete' => function ($url, $model, $key) {
                                            return Html::a(
                                                '<i class="far fa-trash-alt ms-text-danger"></i>',
                                                Url::to(['delete', 'id' => $model->id]),
                                                [
                                                    'title' => 'Eliminar',
                                                    'data-confirm' => '¿Estás seguro de que quieres eliminar esta clínica?',
                                                    'data-method' => 'post',
                                                    'class' => 'btn-action view'
                                                ]
                                            );
                                        },*/

                            ],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
</div>
<div class="clearfix"></div>
</div>