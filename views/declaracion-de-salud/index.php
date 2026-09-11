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
use app\models\UserDatos;
use app\models\DeclaracionDeSalud;
use app\models\DeclaracionDeSaludSearch;
use app\models\Preexistencias;

/**
 * @var yii\web\View $this
 * @var app\models\DeclaracionDeSaludSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\UserDatos $afiliado
 * @var app\models\DeclaracionDeSalud $model
 */

// --- BREADCRUMBS ---
$this->params['breadcrumbs'][] = ['label' => 'AFILIADO', 'url' => ['/user-datos/update', 'id' => $afiliado->id]];
$this->title = 'Gestión de Declaración de Salud del Afiliado';

$rol = UserHelper::getMyRol();

// FIX: Permitir a TODOS los roles EXCEPTO 'CONTROL DE CITAS'
$permisos = ($rol !== 'CONTROL DE CITAS');

// Get the health declaration for this affiliate (if exists)
$healthDeclaration = DeclaracionDeSalud::find()
    ->where(['user_id' => $afiliado->id, 'deleted_at' => null])
    ->orderBy(['created_at' => SORT_DESC])
    ->one();

$hasHealthDeclaration = $healthDeclaration !== null;
$yesAnswers = $hasHealthDeclaration ? $healthDeclaration->getYesAnswers() : [];
$hasYesAnswers = !empty($yesAnswers);

// FIX: Especificar la tabla en la condición deleted_at
$preexistenciasCount = Preexistencias::find()
    ->where(['user_id' => $afiliado->id])
    ->andWhere(['IS', 'preexistencias.deleted_at', null])
    ->count();
?>

<div class="row" style="margin:3px !important;">
    <?php if ($permisos) { ?>
        <div class="col-md-12 d-flex justify-content-between align-items-center" style="margin-bottom:10px; flex-wrap: wrap; gap: 10px;">
            <div class="d-flex gap-2">
                <?= Html::a('<i class="fas fa-undo"></i> Volver', ['/user-datos/update', 'id' => $afiliado->id], ['class' => 'btn btn-secondary btn-lg rounded-pill px-7 shadow-sm']) ?>
            </div>
            <div class="d-flex gap-2">
                <?php if (!$hasHealthDeclaration): ?>
                    <?= Html::a('<i class="fas fa-plus"></i> CREAR DECLARACIÓN DE SALUD', ['create', 'user_id' => $afiliado->id], ['class' => 'btn btn-primary btn-lg rounded-pill px-7 shadow-sm']) ?>
                <?php else: ?>
                    <?= Html::a('<i class="fas fa-edit"></i> EDITAR DECLARACIÓN', ['update', 'id' => $healthDeclaration->id], ['class' => 'btn btn-warning btn-lg rounded-pill px-7 shadow-sm']) ?>
                    <?= Html::a('<i class="fas fa-file-pdf"></i> PDF', ['generar-pdf', 'id' => $healthDeclaration->id], ['class' => 'btn btn-danger btn-lg rounded-pill px-7 shadow-sm', 'target' => '_blank']) ?>
                    <?= Html::a('<i class="fas fa-sync-alt"></i> SINCRONIZAR', ['sync', 'id' => $healthDeclaration->id], ['class' => 'btn btn-info btn-lg rounded-pill px-7 shadow-sm', 'data-method' => 'post']) ?>
                <?php endif; ?>
            </div>
        </div>
    <?php } else { ?>
        <!-- Mensaje informativo para CONTROL DE CITAS -->
        <div class="col-md-12">
            <div class="alert alert-info d-flex align-items-center rounded-lg shadow-sm" style="margin-bottom:10px;">
                <div class="mr-3">
                    <i class="fas fa-info-circle fa-2x" style="color: #0d6efd;"></i>
                </div>
                <div>
                    <h6 class="mb-0">
                        <i class="fas fa-eye mr-1"></i>
                        Solo visualización - No tienes permisos para crear o modificar declaraciones de salud
                    </h6>
                    <small class="text-muted">Puedes ver las declaraciones existentes, pero no crear o editar</small>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

<!-- Status Cards -->
<div class="row" style="margin:3px !important;">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="ms-card ms-card-gradient bg-gradient-primary text-white rounded-lg shadow-sm">
            <div class="ms-card-body d-flex align-items-center">
                <div class="ms-card-icon bg-white bg-opacity-25 rounded-circle p-3 mr-3">
                    <i class="fas fa-user-md fa-2x text-white"></i>
                </div>
                <div>
                    <h6 class="mb-0 text-white-50">Afiliado</h6>
                    <h5 class="mb-0 text-white font-weight-bold"><?= Html::encode($afiliado->nombres . ' ' . $afiliado->apellidos) ?></h5>
                    <small class="text-white-50"><?= Html::encode($afiliado->tipo_cedula . '-' . $afiliado->cedula) ?></small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="ms-card ms-card-gradient <?= $hasHealthDeclaration ? 'bg-gradient-success' : 'bg-gradient-secondary' ?> text-white rounded-lg shadow-sm">
            <div class="ms-card-body d-flex align-items-center">
                <div class="ms-card-icon bg-white bg-opacity-25 rounded-circle p-3 mr-3">
                    <i class="fas <?= $hasHealthDeclaration ? 'fa-check-circle' : 'fa-times-circle' ?> fa-2x text-white"></i>
                </div>
                <div>
                    <h6 class="mb-0 text-white-50">Declaración de Salud</h6>
                    <h5 class="mb-0 text-white font-weight-bold">
                        <?= $hasHealthDeclaration ? 'Registrada' : 'No Registrada' ?>
                    </h5>
                    <?php if ($hasHealthDeclaration): ?>
                        <small class="text-white-50"><?= Yii::$app->formatter->asDate($healthDeclaration->created_at, 'dd/MM/yyyy') ?></small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="ms-card ms-card-gradient <?= $hasYesAnswers ? 'bg-gradient-warning' : 'bg-gradient-success' ?> text-white rounded-lg shadow-sm">
            <div class="ms-card-body d-flex align-items-center">
                <div class="ms-card-icon bg-white bg-opacity-25 rounded-circle p-3 mr-3">
                    <i class="fas fa-exclamation-triangle fa-2x text-white"></i>
                </div>
                <div>
                    <h6 class="mb-0 text-white-50">Diagnósticos "Sí"</h6>
                    <h5 class="mb-0 text-white font-weight-bold"><?= count($yesAnswers) ?></h5>
                    <small class="text-white-50">de <?= count(DeclaracionDeSalud::QUESTIONS) ?> preguntas</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="ms-card ms-card-gradient <?= $preexistenciasCount > 0 ? 'bg-gradient-info' : 'bg-gradient-secondary' ?> text-white rounded-lg shadow-sm">
            <div class="ms-card-body d-flex align-items-center">
                <div class="ms-card-icon bg-white bg-opacity-25 rounded-circle p-3 mr-3">
                    <i class="fas fa-heartbeat fa-2x text-white"></i>
                </div>
                <div>
                    <h6 class="mb-0 text-white-50">Pre-existencias</h6>
                    <h5 class="mb-0 text-white font-weight-bold"><?= $preexistenciasCount ?></h5>
                    <small class="text-white-50">registros activos</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Health Declaration Details (if exists) -->
<?php if ($hasHealthDeclaration && $hasYesAnswers):
    $generatedPreexistencias = $healthDeclaration->getGeneratedPreexistencias();
?>
    <div class="row" style="margin:3px !important;">
        <div class="col-xl-12 col-md-12">
            <div class="ms-card ms-panel-fh shadow-sm">
                <div class="ms-card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-list text-primary mr-2"></i>
                            Diagnósticos Registrados
                        </h6>
                        <span class="badge badge-success">
                            <i class="fas fa-check-circle mr-1"></i>
                            <?= $preexistenciasCount ?> Pre-existencias activas
                        </span>
                    </div>
                </div>
                <div class="ms-card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 20%;">Condición (Descripción)</th>
                                    <th style="width: 25%;">Especificación (Nombre)</th>
                                    <th style="width: 20%;">Fecha Diagnóstico</th>
                                    <th style="width: 20%;">Pre-existencia</th>
                                    <th style="width: 10%;" class="text-center">Estatus</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $questionNumber = 1;
                                $fechaDiagnostico = Yii::$app->formatter->asDate($healthDeclaration->created_at, 'dd/MM/yyyy');
                                foreach ($yesAnswers as $key => $answer):
                                    $generated = $generatedPreexistencias[$key] ?? null;
                                ?>
                                    <tr>
                                        <td class="text-center font-weight-bold"><?= $questionNumber ?></td>
                                        <td><strong><?= Html::encode($answer['label']) ?></strong></td>
                                        <td><?= Html::encode($answer['especifica'] ?: 'No especificado') ?></td>
                                        <td><?= $fechaDiagnostico ?></td>
                                        <td>
                                            <?php if ($generated): ?>
                                                <small>
                                                    <strong>Nombre:</strong> <?= Html::encode($generated['nombre']) ?><br>
                                                    <strong>Descripción:</strong> <?= Html::encode($generated['descripcion']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($generated && $generated['exists']): ?>
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check-circle mr-1"></i> Registrado
                                                </span>
                                            <?php elseif ($generated): ?>
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-clock mr-1"></i> Pendiente
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">
                                                    <i class="fas fa-minus mr-1"></i> N/A
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php
                                    $questionNumber++;
                                endforeach;
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php elseif ($hasHealthDeclaration && !$hasYesAnswers): ?>
    <div class="row" style="margin:3px !important;">
        <div class="col-xl-12 col-md-12">
            <div class="alert alert-info d-flex align-items-start rounded-lg shadow-sm">
                <div class="mr-3">
                    <i class="fas fa-info-circle fa-2x" style="color: #0d6efd;"></i>
                </div>
                <div>
                    <h5 class="alert-heading">Declaración de Salud Registrada</h5>
                    <p class="mb-0">Todas las respuestas fueron "No". No hay condiciones diagnosticadas que registrar como pre-existencias.</p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Grid View -->
<div class="row" style="margin:3px !important;">
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh shadow-sm">
            <div class="ms-panel-header">
                <h6 class="mb-0">
                    <i class="fas fa-history text-primary mr-2"></i>
                    Historial de Declaraciones de Salud
                </h6>
                <small class="text-muted">Últimas declaraciones registradas para este afiliado</small>
            </div>
            <div class="ms-panel-body">
                <div class="table-responsive">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => null,
                        'layout' => "{items}{pager}",
                        'tableOptions' => ['class' => 'table table-hover table-striped'],
                        'columns' => [
                            [
                                'attribute' => 'created_at',
                                'label' => 'Fecha de Registro',
                                'format' => 'datetime',
                                'headerOptions' => ['style' => 'color: white!important; width: 15%;'],
                                'contentOptions' => ['style' => 'vertical-align: middle;'],
                            ],
                            [
                                'attribute' => 'ver_observacion',
                                'label' => 'Observación',
                                'format' => 'ntext',
                                'headerOptions' => ['style' => 'color: white!important; width: 30%;'],
                                'contentOptions' => ['style' => 'max-width: 500px; white-space: normal; word-wrap: break-word; vertical-align: middle;'],
                            ],
                            [
                                'attribute' => 'estatura',
                                'label' => 'Estatura',
                                'format' => 'ntext',
                                'headerOptions' => ['style' => 'color: white!important; width: 8%;'],
                                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                            ],
                            [
                                'attribute' => 'peso',
                                'label' => 'Peso',
                                'format' => 'ntext',
                                'headerOptions' => ['style' => 'color: white!important; width: 8%;'],
                                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                            ],
                            [
                                'label' => 'Edad',
                                'format' => 'ntext',
                                'value' => function ($model) {
                                    if ($model->user && $model->user->fechanac) {
                                        $fechaNacimiento = new \DateTime($model->user->fechanac);
                                        $hoy = new \DateTime();
                                        $edad = $hoy->diff($fechaNacimiento)->y;
                                        return $edad . ' años';
                                    }
                                    return 'N/A';
                                },
                                'headerOptions' => ['style' => 'color: white!important; width: 8%;'],
                                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                            ],
                            [
                                'label' => 'Diagnósticos "Sí"',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $yesCount = count($model->getYesAnswers());
                                    if ($yesCount > 0) {
                                        return '<span class="badge badge-warning">' . $yesCount . ' condiciones</span>';
                                    }
                                    return '<span class="badge badge-success">Ninguna</span>';
                                },
                                'headerOptions' => ['style' => 'color: white!important; width: 12%;'],
                                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => 'ACCIONES',
                                'template' => '<div class="d-flex justify-content-center gap-0">{view}{salud}{update}</div>',
                                'options' => ['style' => 'width:12%; min-width:120px;'],
                                'headerOptions' => ['style' => 'color: white!important; width: 12%;'],
                                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important; vertical-align: middle;'],
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fa fa-eye"></i>',
                                            Url::to(['view', 'id' => $model->id]),
                                            [
                                                'title' => 'Detalle de la Declaración de Salud',
                                                'class' => 'btn-action view btn btn-sm btn-outline-primary mr-1'
                                            ]
                                        );
                                    },
                                    'salud' => function ($url, $model, $key) {
                                        return Html::a(
                                            '<i class="fas fa-file-pdf ms-text-danger"></i>',
                                            Url::to(['generar-pdf', 'id' => $model->id]),
                                            [
                                                'title' => 'Declaración de salud',
                                                'class' => 'btn-action view btn btn-sm btn-outline-danger mr-1',
                                                'target' => '_blank'
                                            ]
                                        );
                                    },
                                    'update' => function ($url, $model, $key) use ($permisos) {
                                        if ($permisos) {
                                            return Html::a(
                                                '<i class="fas fa-pencil-alt ms-text-primary"></i>',
                                                Url::to(['update', 'id' => $model->id]),
                                                [
                                                    'title' => 'Editar',
                                                    'class' => 'btn-action view btn btn-sm btn-outline-warning mr-1'
                                                ]
                                            );
                                        }
                                        return '';
                                    },
                                ],
                            ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Microsoft Fluent Design inspired styles */
    .ms-card-gradient {
        border-radius: 12px;
        padding: 1rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .ms-card-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);
    }

    .bg-gradient-success {
        background: linear-gradient(135deg, #107c10 0%, #0d6e0d 100%);
    }

    .bg-gradient-warning {
        background: linear-gradient(135deg, #e97c00 0%, #d16c00 100%);
    }

    .bg-gradient-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }

    .bg-gradient-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    }

    .bg-white {
        background-color: #ffffff !important;
    }

    .bg-opacity-25 {
        opacity: 0.25;
    }

    .ms-card {
        background: white;
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: box-shadow 0.2s ease;
    }

    .ms-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }

    .ms-card-icon {
        width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.15) !important;
    }

    .rounded-circle {
        border-radius: 50% !important;
    }

    .ms-panel {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .ms-panel-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .ms-panel-body {
        padding: 1.5rem;
    }

    .ms-card-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e9ecef;
    }

    .table thead th {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%) !important;
        color: white !important;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none !important;
        padding: 12px 8px !important;
    }

    .table tbody td {
        border-bottom: 1px solid #f0f0f0;
        padding: 10px 8px !important;
        vertical-align: middle !important;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa !important;
    }

    .badge {
        font-weight: 500;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        letter-spacing: 0.3px;
    }

    .badge-success {
        background: #d4edda;
        color: #155724;
    }

    .badge-warning {
        background: #fff3cd;
        color: #856404;
    }

    .badge-info {
        background: #d1ecf1;
        color: #0c5460;
    }

    .badge-secondary {
        background: #e9ecef;
        color: #6c757d;
    }

    .btn-action {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .btn-action:hover {
        transform: scale(1.1);
    }

    .rounded-pill {
        border-radius: 50px !important;
    }

    .rounded-lg {
        border-radius: 12px !important;
    }

    .shadow-sm {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
    }

    .gap-2 {
        gap: 0.5rem !important;
    }

    .gap-3 {
        gap: 1rem !important;
    }

    .alert {
        border-radius: 12px;
        border: none;
    }

    .text-white-50 {
        color: rgba(255, 255, 255, 0.7) !important;
    }

    .font-weight-bold {
        font-weight: 700 !important;
    }

    .mr-1 {
        margin-right: 0.25rem !important;
    }

    .mr-2 {
        margin-right: 0.5rem !important;
    }

    .mr-3 {
        margin-right: 1rem !important;
    }

    .mb-0 {
        margin-bottom: 0 !important;
    }

    .mt-2 {
        margin-top: 0.5rem !important;
    }

    .p-3 {
        padding: 1rem !important;
    }

    .px-7 {
        padding-left: 3.5rem !important;
        padding-right: 3.5rem !important;
    }

    @media (max-width: 768px) {
        .ms-card-body {
            flex-direction: column;
            align-items: flex-start !important;
        }

        .ms-card-body .ms-card-icon {
            margin-bottom: 0.5rem;
        }

        .ms-panel-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .d-flex.justify-content-between {
            flex-direction: column;
            align-items: stretch !important;
        }

        .d-flex.justify-content-between .d-flex {
            justify-content: center !important;
            flex-wrap: wrap;
        }

        .px-7 {
            padding-left: 1.5rem !important;
            padding-right: 1.5rem !important;
        }
    }
</style>