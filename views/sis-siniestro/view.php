<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\AppointmentNotificationService;
use app\models\SisSiniestroAttachment;

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

// Register CSS for white text in table headers and notification buttons
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

/* ===== NOTIFICATION BUTTON STYLES ===== */
.btn-notification {
    display: inline-flex;
    align-items: center;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
    text-decoration: none;
    gap: 8px;
}

.btn-notification:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    text-decoration: none;
}

.btn-notification-success {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: #ffffff;
}

.btn-notification-success:hover {
    color: #ffffff;
    background: linear-gradient(135deg, #218838, #1ba87a);
}

.btn-notification-info {
    background: linear-gradient(135deg, #1e3c72, #2a5298);
    color: #ffffff;
}

.btn-notification-info:hover {
    color: #ffffff;
    background: linear-gradient(135deg, #162b54, #1e3c72);
}

.whatsapp-link-btn {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    background: #25D366;
    color: #ffffff;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    font-size: 13px;
    transition: all 0.2s ease;
    gap: 6px;
    border: none;
    cursor: pointer;
}

.whatsapp-link-btn:hover {
    color: #ffffff;
    background: #1da851;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
}

.header-buttons-group {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}

/* ===== ATTACHMENT CARD STYLES ===== */
.attachment-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 16px;
    margin-top: 16px;
}

.attachment-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 16px;
    text-align: center;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.attachment-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    border-color: #0078d4;
}

.attachment-icon {
    font-size: 48px;
    margin-bottom: 8px;
    display: block;
}

.attachment-filename {
    font-weight: 500;
    font-size: 13px;
    color: #1a1a1a;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    margin-bottom: 4px;
}

.attachment-meta {
    font-size: 11px;
    color: #6c757d;
}

.attachment-actions {
    margin-top: 12px;
    display: flex;
    gap: 6px;
    justify-content: center;
    flex-wrap: wrap;
}

.attachment-badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
}

.attachment-badge-receta {
    background: #fff3cd;
    color: #856404;
}

.attachment-badge-informe {
    background: #cce5ff;
    color: #004085;
}

.attachment-badge-autorizacion {
    background: #d1ecf1;
    color: #0c5460;
}

.attachment-badge-resultado {
    background: #d4edda;
    color: #155724;
}

.attachment-badge-otro {
    background: #e2e3e5;
    color: #383d41;
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

            <?php if ($esCita == 1): ?>
                <?= Html::a(
                    '<i class="fas fa-envelope mr-2"></i> Reenviar Notificación',
                    ['resend-notification', 'id' => $model->id],
                    [
                        'class' => 'btn-notification btn-notification-info',
                        'title' => 'Reenviar notificación por correo electrónico al afiliado',
                        'data-confirm' => '¿Desea reenviar la notificación de la cita por correo electrónico?',
                        'data-method' => 'post',
                    ]
                ) ?>

                <?php
                // ============================================
                // GENERATE WHATSAPP LINK FOR STAFF
                // ============================================
                if ($afiliado && !empty($afiliado->telefono)) {
                    // Build the message
                    $baremosList = [];
                    $totalCost = 0;
                    if (!empty($baremos)) {
                        foreach ($baremos as $baremo) {
                            $baremosList[] = $baremo->nombre_servicio ?? 'Servicio';
                            $totalCost += (float)($baremo->precio ?? 0);
                        }
                    }

                    $appointmentDate = isset($model->fecha_atencion)
                        ? Yii::$app->formatter->asDate($model->fecha_atencion, 'dd/MM/yyyy')
                        : 'No especificada';

                    $appointmentTime = isset($model->hora_atencion)
                        ? date('H:i', strtotime($model->hora_atencion))
                        : 'No especificada';

                    $doctorName = $model->nombre_doctor ?? 'No especificado';
                    $analystName = $model->admission_analyst ?? 'No especificado';
                    $clinicaName = $model->clinica ? $model->clinica->nombre : 'No especificada';

                    $message = "🏥 SISPSA - Confirmación de Cita Médica\n\n";
                    $message .= "👤 Paciente: {$afiliado->nombres} {$afiliado->apellidos}\n";
                    $message .= "📌 Cédula: {$afiliado->tipo_cedula}-{$afiliado->cedula}\n";
                    $message .= "🏛️ Clínica: {$clinicaName}\n";
                    $message .= "📅 Fecha: {$appointmentDate}\n";
                    $message .= "🕐 Hora: {$appointmentTime}\n";
                    $message .= "👨‍⚕️ Médico: {$doctorName}\n";
                    $message .= "💼 Analista: {$analystName}\n\n";

                    if (!empty($baremosList)) {
                        $message .= "📋 Servicios:\n";
                        foreach ($baremos as $baremo) {
                            $message .= "   • {$baremo->nombre_servicio} - \$" . number_format((float)($baremo->precio ?? 0), 2) . "\n";
                        }
                        $message .= "\n💰 Total: \$" . number_format($totalCost, 2) . "\n";
                    }

                    $message .= "\n📝 Por favor, llegar con 15 minutos de anticipación.";

                    $whatsappLink = AppointmentNotificationService::generateWhatsAppLink($afiliado->telefono, $message);
                ?>
                    <?php if ($whatsappLink): ?>
                        <a href="<?= $whatsappLink ?>" target="_blank" class="whatsapp-link-btn" title="Abrir WhatsApp con mensaje pre-cargado">
                            <i class="fab fa-whatsapp fa-lg"></i>
                            WhatsApp
                        </a>
                    <?php endif; ?>
                <?php } ?>
            <?php endif; ?>

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

    <!-- ===== INFO GENERAL ===== -->
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

    <!-- ===== DESCRIPCIÓN ===== -->
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

    <!-- ===== DOCUMENTOS ===== -->
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

    <!-- ============================================================ -->
    <!-- ===== SECTION: DOCUMENTOS ADICIONALES (FROM ATTACHMENTS TABLE) ===== -->
    <!-- ============================================================ -->
    <?php
    // Get attachments from the new table
    $attachments = $model->getActiveAttachments();
    $attachmentCount = count($attachments);
    ?>

    <div class="ms-panel">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-folder-open text-blue-600 mr-3"></i> Documentos Adicionales
                <?php if ($attachmentCount > 0): ?>
                    <span class="badge badge-secondary ml-2"><?= $attachmentCount ?> archivo(s)</span>
                <?php endif; ?>
            </h3>

            <?php if ($attachmentCount > 0): ?>
                <!-- Grid View for Attachments -->
                <div class="attachment-grid">
                    <?php foreach ($attachments as $index => $attachment): ?>
                        <div class="attachment-card">
                            <!-- Icon -->
                            <span class="attachment-icon">
                                <i class="fas <?= $attachment->getFileIcon() ?>"></i>
                            </span>

                            <!-- Document Type Badge -->
                            <div class="mb-2">
                                <?php
                                $badgeClass = 'attachment-badge-otro';
                                $docType = $attachment->document_type ?? 'Otro';
                                if ($docType === 'Receta Médica') $badgeClass = 'attachment-badge-receta';
                                elseif ($docType === 'Informe Médico') $badgeClass = 'attachment-badge-informe';
                                elseif ($docType === 'Autorización') $badgeClass = 'attachment-badge-autorizacion';
                                elseif ($docType === 'Resultado de Examen') $badgeClass = 'attachment-badge-resultado';
                                ?>
                                <span class="attachment-badge <?= $badgeClass ?>">
                                    <?= Html::encode($docType) ?>
                                </span>
                            </div>

                            <!-- Filename -->
                            <div class="attachment-filename" title="<?= Html::encode($attachment->original_filename) ?>">
                                <?= Html::encode($attachment->original_filename) ?>
                            </div>

                            <!-- Meta info -->
                            <div class="attachment-meta">
                                <?= $attachment->getFormattedSize() ?>
                                <br>
                                <small><?= Yii::$app->formatter->asDate($attachment->created_at, 'dd/MM/yyyy HH:mm') ?></small>
                            </div>

                            <!-- Description if available -->
                            <?php if (!empty($attachment->description)): ?>
                                <div class="attachment-meta mt-1" style="font-size: 10px; color: #6c757d; max-height: 32px; overflow: hidden;">
                                    <i class="fas fa-quote-left mr-1"></i>
                                    <?= Html::encode($attachment->description) ?>
                                </div>
                            <?php endif; ?>

                            <!-- Actions -->
                            <div class="attachment-actions">
                                <a href="<?= $attachment->getFileUrl() ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Ver documento">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= $attachment->getFileUrl() ?>" download class="btn btn-sm btn-outline-success" title="Descargar">
                                    <i class="fas fa-download"></i>
                                </a>
                                <?= Html::a(
                                    '<i class="fas fa-trash-alt"></i>',
                                    ['/sis-siniestro/delete-attachment', 'id' => $attachment->id],
                                    [
                                        'class' => 'btn btn-sm btn-outline-danger',
                                        'title' => 'Eliminar documento',
                                        'data-method' => 'post',
                                        'data-confirm' => '¿Está seguro de eliminar este documento? Esta acción no se puede deshacer.',
                                    ]
                                ) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Table View for Attachments (Alternative - shows more details) -->
                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="25%"><i class="fas fa-file me-1"></i> Archivo</th>
                                <th width="15%"><i class="fas fa-tag me-1"></i> Tipo</th>
                                <th width="25%"><i class="fas fa-align-left me-1"></i> Descripción</th>
                                <th width="10%"><i class="fas fa-weight-hanging me-1"></i> Tamaño</th>
                                <th width="10%"><i class="fas fa-calendar-alt me-1"></i> Fecha</th>
                                <th width="10%"><i class="fas fa-cog me-1"></i> Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attachments as $index => $attachment): ?>
                                <tr>
                                    <td class="text-center"><?= $index + 1 ?></td>
                                    <td>
                                        <i class="fas <?= $attachment->getFileIcon() ?> me-2"></i>
                                        <?= Html::encode($attachment->original_filename) ?>
                                    </td>
                                    <td>
                                        <?php
                                        $badgeClass = 'badge-secondary';
                                        $docType = $attachment->document_type ?? 'Otro';
                                        if ($docType === 'Receta Médica') $badgeClass = 'badge-warning';
                                        elseif ($docType === 'Informe Médico') $badgeClass = 'badge-info';
                                        elseif ($docType === 'Autorización') $badgeClass = 'badge-primary';
                                        elseif ($docType === 'Resultado de Examen') $badgeClass = 'badge-success';
                                        ?>
                                        <span class="badge <?= $badgeClass ?>">
                                            <?= Html::encode($docType) ?>
                                        </span>
                                    </td>
                                    <td><?= Html::encode($attachment->description ?? 'Sin descripción') ?></td>
                                    <td><?= $attachment->getFormattedSize() ?></td>
                                    <td><?= Yii::$app->formatter->asDate($attachment->created_at, 'dd/MM/yyyy HH:mm') ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= $attachment->getFileUrl() ?>" target="_blank" class="btn btn-outline-primary" title="Ver documento">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= $attachment->getFileUrl() ?>" download class="btn btn-outline-success" title="Descargar">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <?= Html::a(
                                                '<i class="fas fa-trash-alt"></i>',
                                                ['/sis-siniestro/delete-attachment', 'id' => $attachment->id],
                                                [
                                                    'class' => 'btn btn-outline-danger',
                                                    'title' => 'Eliminar documento',
                                                    'data-method' => 'post',
                                                    'data-confirm' => '¿Está seguro de eliminar este documento? Esta acción no se puede deshacer.',
                                                ]
                                            ) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                    <p class="mb-0">No hay documentos adicionales adjuntos a esta <?= $terminoLower ?></p>
                    <?php if (Yii::$app->user->can('updateSisSiniestro')): ?>
                        <p class="mb-0 mt-2">
                            <?= Html::a(
                                '<i class="fas fa-plus mr-1"></i> Agregar documentos',
                                ['update', 'id' => $model->id, 'user_id' => $model->iduser, 'es_cita' => $esCita],
                                ['class' => 'btn btn-sm btn-outline-primary']
                            ) ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- ============================================================ -->
    <!-- ===== END DOCUMENTOS ADICIONALES ===== -->
    <!-- ============================================================ -->

</div>