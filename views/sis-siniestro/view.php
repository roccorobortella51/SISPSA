<?php

use yii\helpers\Html;

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

?>

<div class="main-container">

    <div class="header-section">
        <h1><?= Html::encode($this->title) ?></h1>

        <div class="header-buttons-group">
            <?= Html::a(
                '<i class="fas fa-edit mr-2"></i> Actualizar',
                ['update', 'id' => $model->id, 'es_cita' => $esCita],
                ['class' => 'btn-base btn-blue']
            ) ?>
            <?php Html::a(
                '<i class="fas fa-trash-alt mr-2"></i> Eliminar',
                ['delete', 'id' => $model->id],
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
                    'modo' => $esCita == 1 ? 'cita' : 'siniestro'  // Send 'cita' or 'siniestro' string
                ],
                [
                    'class' => 'btn-base btn-gray',
                    'title' => 'Volver a la lista de ' . $terminoLower . 's de ' . Html::encode($afiliadoName),
                    'data' => [
                        'pjax' => 0,
                    ],
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
                        <h5 class="text-muted">¿Fue atendido?</h5>
                        <p class="h5 text-dark"><?= formatBooleanIcon($model->atendido) ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h5 class="text-muted">Afiliado</h5>
                        <p class="h5 text-dark"><?= is_object($afiliado) ? Html::encode($afiliado->nombres . " " . $afiliado->apellidos . " (" . $afiliado->tipo_cedula . "-" . $afiliado->cedula . ")") : Html::encode($afiliado) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="ms-panel">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-file-alt text-blue-600 mr-3"></i> Descripción de la <?= $termino ?>
            </h3>
            <div class="info-card-body">
                <h5><strong>Descripción:</strong> <?= nl2br(Html::encode($model->descripcion)) ?></h5>
            </div>
            <div class="alert alert-success" align="center">
                <h2><strong>Total:</strong> <?= nl2br(Html::encode($model->costo_total)) ?> USD</h2>
            </div>
        </div>
    </div>

    <div class="ms-panel">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-clock text-blue-600 mr-3"></i> Fechas de Registro
            </h3>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h5 class="text-muted">Fecha de Creación</h5>
                        <p class="h5 text-dark"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h5 class="text-muted">Última Actualización</h5>
                        <p class="h5 text-dark"><?= Yii::$app->formatter->asDatetime($model->updated_at) ?></p>
                    </div>
                </div>
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
                                // Mostrar vista previa de imágenes
                                // Añadir timestamp para evitar caché
                                $timestamp = time();
                                $imageUrl = $model->imagen_recipe . '?v=' . $timestamp;
                                echo Html::a(
                                    Html::img($imageUrl, [
                                        'class' => 'img-fluid border rounded',
                                        'style' => 'max-height: 250px; max-width: 100%;',
                                        'loading' => 'lazy' // Carga perezosa para imágenes
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
                                // Mostrar vista previa de PDF usando PDF.js
                                $timestamp = time();
                                $pdfUrl = $model->imagen_recipe . '?v=' . $timestamp . '#toolbar=0&view=FitH';
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
                                echo '<div class="text-muted small">Tamaño del archivo: ' . $this->context->getFileSize($model->imagen_recipe) . '</div>';
                                echo '</div>';
                            }

                            // Botón de descarga con URL sin parámetro de caché
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

                            // Botón para abrir en nueva pestaña
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
                                // Mostrar vista previa de imágenes
                                $imageUrl = $model->imagen_informe . '?v=' . $timestamp;
                                echo Html::a(
                                    Html::img($imageUrl, [
                                        'class' => 'img-fluid border rounded',
                                        'style' => 'max-height: 250px; max-width: 100%;',
                                        'loading' => 'lazy' // Carga perezosa para imágenes
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
                                // Mostrar vista previa de PDF usando PDF.js
                                $pdfUrl = $model->imagen_informe . '?v=' . $timestamp . '#toolbar=0&view=FitH';
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
                                echo '<div class="text-muted small">Tamaño del archivo: ' . $this->context->getFileSize($model->imagen_informe) . '</div>';
                                echo '</div>';
                            }

                            // Botón de descarga
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

                            // Botón para abrir en nueva pestaña
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

</div>