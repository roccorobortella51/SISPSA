<?php

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 * @var app\models\SisSiniestro $model
 * @var app\models\UserDatos $afiliado
 * @var array $baremos
 * @var int $es_cita
 */

// Get es_cita parameter from the model or URL
$esCita = isset($model->es_cita) ? (int)$model->es_cita : (int)Yii::$app->request->get('es_cita', 0);
$termino = $esCita === 1 ? 'Cita' : 'Atención';
$terminoLower = strtolower($termino);

// Determine correct plural label
$pluralLabel = ($termino === 'Cita') ? 'Citas' : 'Atenciones';

$afiliadoName = is_object($afiliado) ? ($afiliado->nombres . " " . $afiliado->apellidos . " " . $afiliado->tipo_cedula . "-" . $afiliado->cedula) : 'Afiliado';
$this->title = 'Detalles de la ' . $termino . ': ' . Html::encode($afiliadoName);
$this->params['breadcrumbs'][] = ['label' => $pluralLabel, 'url' => ['index', 'user_id' => $model->iduser, 'modo' => $esCita == 1 ? 'cita' : 'siniestro']];
$this->params['breadcrumbs'][] = $this->title;

\yii\web\YiiAsset::register($this);

function formatBooleanIcon($value)
{
    $isTrue = (bool)$value;
    return $isTrue ? '<span class="status-badge active">Sí</span>' : '<span class="status-badge inactive">No</span>';
}

// Register CSS for white text in table headers
$this->registerCss("
    .table-bordered thead th {
        color: white !important;
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;
        text-align: center !important;
        vertical-align: middle !important;
        font-weight: 600 !important;
    }
    
    .table-bordered thead th i {
        color: white !important;
    }
    
    .table-bordered tbody td {
        vertical-align: middle !important;
        text-align: center !important;
    }
");

?>

<div class="main-container">

    <div class="header-section">
        <h1><?= Html::encode($this->title) ?></h1>

        <div class="header-buttons-group">
            <?= Html::a(
                '<i class="fas fa-edit mr-2"></i> Actualizar',
                ['update', 'id' => $model->id, 'user_id' => $model->iduser, 'es_cita' => $esCita],
                ['class' => 'btn-base btn-blue']
            ) ?>
            <?= Html::a(
                '<i class="fas fa-trash-alt mr-2"></i> Eliminar',
                ['delete', 'id' => $model->id, 'user_id' => $model->iduser, 'es_cita' => $esCita],
                [
                    'class' => 'btn-base btn-red',
                    'data' => [
                        'confirm' => '¿Está seguro de que desea eliminar esta ' . $terminoLower . '? Esta acción no se puede deshacer.',
                        'method' => 'post',
                    ],
                ]
            ) ?>
            <?= Html::a(
                '<i class="fas fa-undo mr-2"></i> Volver',
                [
                    'index',
                    'user_id' => $model->iduser,
                    'clinica_id' => $model->idclinica,
                    'modo' => $esCita == 1 ? 'cita' : 'siniestro'
                ],
                [
                    'class' => 'btn-base btn-gray',
                    'title' => 'Volver a la lista de ' . $terminoLower . 's de ' . Html::encode($afiliadoName),
                    'data' => ['pjax' => 0],
                ]
            ) ?>
        </div>
    </div>

    <div class="ms-panel">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-info-circle text-blue-600 mr-3"></i> Información General de la <?= $termino ?>
            </h3>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h5 class="text-muted">Clínica Asociada</h5>
                        <p class="h4 text-dark"><?= Html::encode($model->clinica->nombre) ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h5 class="text-muted">Servicios Médicos</h5>
                        <?php
                        if (!empty($baremos) && is_array($baremos)) {
                            $nombresBaremos = [];
                            foreach ($baremos as $baremo) {
                                $nombresBaremos[] = Html::encode($baremo->nombre_servicio);
                            }
                            echo '<p class="h5 text-dark">' . implode(', ', $nombresBaremos) . '</p>';
                        } else {
                            echo '<p class="text-muted">No se han seleccionado servicios médicos</p>';
                        }
                        ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h5 class="text-muted">Fecha del Evento de Salud</h5>
                        <p class="h5 text-dark"><?= Yii::$app->formatter->asDate($model->fecha) ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h5 class="text-muted">Hora del Evento de Salud</h5>
                        <p class="h5 text-dark"><?= Yii::$app->formatter->asTime($model->hora) ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h5 class="text-muted">Fecha de la <?= $termino ?></h5>
                        <p class="h5 text-dark"><?= Yii::$app->formatter->asDate($model->fecha_atencion) ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h5 class="text-muted">Hora de la <?= $termino ?></h5>
                        <p class="h5 text-dark"><?= Yii::$app->formatter->asTime($model->hora_atencion) ?></p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h5 class="text-muted">Afiliado</h5>
                        <p class="h5 text-dark"><?= is_object($afiliado) ? Html::encode($afiliado->nombres . " " . $afiliado->apellidos . " (" . $afiliado->tipo_cedula . "-" . $afiliado->cedula . ")") : Html::encode($afiliado) ?></p>
                    </div>
                </div>
                <!-- ===== DOCTOR NAME ===== -->
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h5 class="text-muted">
                            <i class="fas fa-user-md text-info mr-2"></i> Nombre del Doctor
                        </h5>
                        <?php if (!empty($model->nombre_doctor)): ?>
                            <p class="h5 text-dark">
                                <i class="fas fa-stethoscope mr-2 text-info"></i>
                                <?= Html::encode($model->nombre_doctor) ?>
                            </p>
                        <?php else: ?>
                            <p class="text-muted">
                                <i class="fas fa-user-slash mr-1"></i> No asignado
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- ===== ADMISSION ANALYST ===== -->
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h5 class="text-muted">
                            <i class="fas fa-user-check text-success mr-2"></i> Analista de Admisión
                        </h5>
                        <?php if (!empty($model->admission_analyst)): ?>
                            <p class="h5 text-dark">
                                <i class="fas fa-user-tie mr-2 text-success"></i>
                                <?= Html::encode($model->admission_analyst) ?>
                            </p>
                        <?php else: ?>
                            <p class="text-muted">
                                <i class="fas fa-user-slash mr-1"></i> No asignado
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== SECTION: DETALLE DE SERVICIOS MÉDICOS CON DESCRIPCIÓN ===== -->
    <div class="ms-panel">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-stethoscope text-blue-600 mr-3"></i> Detalle de Servicios Médicos
            </h3>

            <?php if (!empty($baremos) && is_array($baremos)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th width="30%"><i class="fas fa-notes-medical me-1"></i> Servicio</th>
                                <th width="25%"><i class="fas fa-tag me-1"></i> Área</th>
                                <th width="35%"><i class="fas fa-align-left me-1"></i> Descripción</th>
                                <th width="10%"><i class="fas fa-dollar-sign me-1"></i> Precio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $totalServicios = 0;
                            foreach ($baremos as $baremo):
                                $totalServicios += $baremo->precio;
                            ?>
                                <tr>
                                    <td>
                                        <i class="fas fa-stethoscope text-primary mr-2"></i>
                                        <strong><?= Html::encode($baremo->nombre_servicio) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            <i class="fas fa-building mr-1"></i>
                                            <?= Html::encode($baremo->area->nombre ?? 'Sin área') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($baremo->descripcion)): ?>
                                            <i class="fas fa-file-alt text-muted mr-1"></i>
                                            <?= Html::encode($baremo->descripcion) ?>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <i class="fas fa-minus-circle"></i> Sin descripción
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong class="text-success">
                                            $<?= number_format($baremo->precio, 2) ?>
                                        </strong>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-active">
                                <td colspan="3" class="text-right">
                                    <strong>Total de Servicios:</strong>
                                </td>
                                <td class="text-center">
                                    <strong class="text-success h5">
                                        $<?= number_format($totalServicios, 2) ?>
                                    </strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                    <p class="mb-0">No se han seleccionado servicios médicos para esta <?= $terminoLower ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- ===== END DETALLE DE SERVICIOS MÉDICOS ===== -->

    <div class="ms-panel">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-file-alt text-blue-600 mr-3"></i> Descripción de la <?= $termino ?>
            </h3>
            <div class="info-card-body">
                <h5><strong>Descripción:</strong> <?= nl2br(Html::encode($model->descripcion)) ?></h5>
            </div>
            <div class="alert alert-success" align="center">
                <h2><strong>Total:</strong> <?= number_format($model->costo_total, 2) ?> USD</h2>
            </div>
        </div>
    </div>

    <div class="ms-panel">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-images text-blue-600 mr-3"></i> Documentos de la <?= $termino ?>
            </h3>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h5 class="text-muted">Récipe Médico</h5>
                        <?php
                        if ($model->imagen_recipe) {
                            $extension = strtolower(pathinfo($model->imagen_recipe, PATHINFO_EXTENSION));
                            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
                            $isPdf = $extension === 'pdf';

                            if ($isImage) {
                                $timestamp = time();
                                $imageUrl = $model->imagen_recipe . '?v=' . $timestamp;
                                echo Html::a(
                                    Html::img($imageUrl, [
                                        'class' => 'img-fluid border rounded',
                                        'style' => 'max-height: 250px; max-width: 100%;',
                                        'loading' => 'lazy'
                                    ]),
                                    $model->imagen_recipe . '?v=' . $timestamp,
                                    [
                                        'target' => '_blank',
                                        'title' => 'Ver Recibo/Factura',
                                        'class' => 'd-block mb-2',
                                        'data-pjax' => '0'
                                    ]
                                );
                            } elseif ($isPdf) {
                                $timestamp = time();
                                echo '<div class="pdf-preview-container mb-2">';
                                echo Html::a(
                                    '<i class="fas fa-file-pdf fa-5x text-danger d-block mb-2"></i>',
                                    $model->imagen_recipe . '?v=' . $timestamp,
                                    [
                                        'target' => '_blank',
                                        'title' => 'Ver PDF',
                                        'class' => 'd-block',
                                        'data-pjax' => '0'
                                    ]
                                );
                                echo '</div>';
                            }

                            echo Html::a(
                                '<i class="fas fa-download me-1"></i> Descargar Archivo',
                                $model->imagen_recipe . '?download=true',
                                [
                                    'class' => 'btn btn-sm btn-primary mt-2',
                                    'target' => '_blank',
                                    'download' => 'recibo_' . $model->id . '.' . $extension,
                                    'data-pjax' => '0'
                                ]
                            );

                            echo ' ';
                            echo Html::a(
                                '<i class="fas fa-external-link-alt me-1"></i> Abrir',
                                $model->imagen_recipe . '?v=' . time(),
                                [
                                    'class' => 'btn btn-sm btn-outline-secondary mt-2',
                                    'target' => '_blank',
                                    'data-pjax' => '0'
                                ]
                            );
                        } else {
                            echo '<p class="text-muted">No se ha subido ningún récipe médico.</p>';
                        }
                        ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h5 class="text-muted">Informe Médico</h5>
                        <?php
                        if ($model->imagen_informe) {
                            $extension = strtolower(pathinfo($model->imagen_informe, PATHINFO_EXTENSION));
                            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
                            $isPdf = $extension === 'pdf';
                            $timestamp = time();

                            if ($isImage) {
                                $imageUrl = $model->imagen_informe . '?v=' . $timestamp;
                                echo Html::a(
                                    Html::img($imageUrl, [
                                        'class' => 'img-fluid border rounded',
                                        'style' => 'max-height: 250px; max-width: 100%;',
                                        'loading' => 'lazy'
                                    ]),
                                    $model->imagen_informe . '?v=' . $timestamp,
                                    [
                                        'target' => '_blank',
                                        'title' => 'Ver Informe Médico',
                                        'class' => 'd-block mb-2',
                                        'data-pjax' => '0'
                                    ]
                                );
                            } elseif ($isPdf) {
                                echo '<div class="pdf-preview-container mb-2">';
                                echo Html::a(
                                    '<i class="fas fa-file-pdf fa-5x text-danger d-block mb-2"></i>',
                                    $model->imagen_informe . '?v=' . $timestamp,
                                    [
                                        'target' => '_blank',
                                        'title' => 'Ver PDF',
                                        'class' => 'd-block',
                                        'data-pjax' => '0'
                                    ]
                                );
                                echo '</div>';
                            }

                            echo Html::a(
                                '<i class="fas fa-download me-1"></i> Descargar Archivo',
                                $model->imagen_informe . '?download=true',
                                [
                                    'class' => 'btn btn-sm btn-primary mt-2',
                                    'target' => '_blank',
                                    'download' => 'informe_medico_' . $model->id . '.' . $extension,
                                    'data-pjax' => '0'
                                ]
                            );

                            echo ' ';
                            echo Html::a(
                                '<i class="fas fa-external-link-alt me-1"></i> Abrir',
                                $model->imagen_informe . '?v=' . $timestamp,
                                [
                                    'class' => 'btn btn-sm btn-outline-secondary mt-2',
                                    'target' => '_blank',
                                    'data-pjax' => '0'
                                ]
                            );
                        } else {
                            echo '<p class="text-muted">No se ha subido ningún informe médico.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== SECTION: DOCUMENTOS ADICIONALES ===== -->
    <?php
    // Decode additional documents from JSON
    $documentosAdicionales = [];
    if (!empty($model->otros_documentos)) {
        $documentosAdicionales = json_decode($model->otros_documentos, true);
        if (!is_array($documentosAdicionales)) {
            $documentosAdicionales = [];
        }
    }

    if (!empty($documentosAdicionales)):
    ?>
        <div class="ms-panel">
            <div class="ms-panel-body">
                <h3 class="section-title">
                    <i class="fas fa-folder-open text-blue-600 mr-3"></i> Documentos Adicionales
                    <span class="badge badge-secondary ml-2"><?= count($documentosAdicionales) ?> archivo(s)</span>
                </h3>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th width="20%"><i class="fas fa-tag me-1"></i> Tipo de Documento</th>
                                <th width="35%"><i class="fas fa-align-left me-1"></i> Descripción</th>
                                <th width="25%"><i class="fas fa-file me-1"></i> Archivo</th>
                                <th width="20%"><i class="fas fa-calendar-alt me-1"></i> Fecha de Subida</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documentosAdicionales as $index => $doc): ?>
                                <tr>
                                    <td>
                                        <?php
                                        $tipoIconMap = [
                                            'Certificado Médico' => 'fa-certificate text-primary',
                                            'Examen de Laboratorio' => 'fa-flask text-success',
                                            'Estudio de Imagen' => 'fa-x-ray text-info',
                                            'Autorización' => 'fa-file-signature text-warning',
                                            'Referencia' => 'fa-share-square text-secondary',
                                            'Historia Clínica' => 'fa-notes-medical text-danger',
                                            'Nota de Evolución' => 'fa-chart-line text-dark',
                                            'Consentimiento Informado' => 'fa-file-contract text-muted',
                                            'Otro' => 'fa-file-alt text-secondary',
                                        ];
                                        $tipo = $doc['tipo'] ?? 'Otro';
                                        $iconClass = $tipoIconMap[$tipo] ?? 'fa-file-alt text-secondary';
                                        ?>
                                        <i class="fas <?= $iconClass ?> me-2 fa-lg"></i>
                                        <strong><?= Html::encode($tipo) ?></strong>
                                    </td>
                                    <td>
                                        <?= !empty($doc['descripcion']) ? Html::encode($doc['descripcion']) : '<span class="text-muted font-italic">Sin descripción</span>' ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php
                                            $fileUrl = $doc['url'];
                                            $extension = strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION));
                                            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
                                            $isPdf = $extension === 'pdf';
                                            ?>

                                            <?php if ($isImage): ?>
                                                <?= Html::a(
                                                    '<i class="fas fa-eye me-1"></i> Ver',
                                                    $fileUrl . '?v=' . time(),
                                                    [
                                                        'class' => 'btn btn-sm btn-outline-primary',
                                                        'target' => '_blank',
                                                        'data-pjax' => '0'
                                                    ]
                                                ) ?>
                                            <?php elseif ($isPdf): ?>
                                                <?= Html::a(
                                                    '<i class="fas fa-file-pdf me-1"></i> Ver PDF',
                                                    $fileUrl . '?v=' . time(),
                                                    [
                                                        'class' => 'btn btn-sm btn-outline-danger',
                                                        'target' => '_blank',
                                                        'data-pjax' => '0'
                                                    ]
                                                ) ?>
                                            <?php else: ?>
                                                <?= Html::a(
                                                    '<i class="fas fa-download me-1"></i> Descargar',
                                                    $fileUrl . '?download=true',
                                                    [
                                                        'class' => 'btn btn-sm btn-outline-secondary',
                                                        'target' => '_blank',
                                                        'download' => 'documento_' . ($index + 1) . '.' . $extension,
                                                        'data-pjax' => '0'
                                                    ]
                                                ) ?>
                                            <?php endif; ?>

                                            <?= Html::a(
                                                '<i class="fas fa-download"></i>',
                                                $fileUrl . '?download=true',
                                                [
                                                    'class' => 'btn btn-sm btn-outline-secondary',
                                                    'target' => '_blank',
                                                    'title' => 'Descargar archivo',
                                                    'download' => 'documento_' . ($index + 1) . '.' . $extension,
                                                    'data-pjax' => '0'
                                                ]
                                            ) ?>
                                        </div>

                                        <?php if (!empty($doc['nombre_archivo'])): ?>
                                            <div class="small text-muted mt-1">
                                                <i class="fas fa-file me-1"></i> <?= Html::encode($doc['nombre_archivo']) ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($doc['tamano'])): ?>
                                            <div class="small text-muted">
                                                <i class="fas fa-weight-hanging me-1"></i>
                                                <?php
                                                $size = $doc['tamano'];
                                                if ($size < 1024) {
                                                    echo $size . ' B';
                                                } elseif ($size < 1048576) {
                                                    echo round($size / 1024, 2) . ' KB';
                                                } else {
                                                    echo round($size / 1048576, 2) . ' MB';
                                                }
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <i class="fas fa-clock me-1 text-muted"></i>
                                        <?= isset($doc['fecha_subida']) ? Yii::$app->formatter->asDatetime($doc['fecha_subida']) : Yii::$app->formatter->asDatetime($model->created_at) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <!-- ===== END DOCUMENTOS ADICIONALES ===== -->

</div>