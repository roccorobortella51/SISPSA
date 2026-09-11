<?php

/**
 * @var yii\web\View $this
 * @var app\models\SisSiniestro $model
 * @var yii\widgets\ActiveForm $form
 * @var app\models\SisSiniestroAttachment[] $attachments
 */

use yii\helpers\Html;
use app\models\SisSiniestroAttachment;

// Get attachments from the model
$attachments = $model->getActiveAttachments();
$documentTypeOptions = SisSiniestroAttachment::getDocumentTypeOptions();
?>

<div class="card mb-4">
    <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <i class="fas fa-paperclip me-2"></i> Documentos Adicionales
                <?php if (!empty($attachments)): ?>
                    <span class="badge badge-white ms-2">
                        <?= count($attachments) ?> documento(s)
                    </span>
                <?php endif; ?>
            </div>
            <span class="badge badge-white">
                <i class="fas fa-info-circle me-1"></i> Opcional
            </span>
        </div>
    </div>
    <div class="card-body">
        <!-- Existing Documents -->
        <?php if (!empty($attachments)): ?>
            <div class="mb-4">
                <label class="form-label font-weight-bold">
                    <i class="fas fa-folder-open text-primary me-1"></i> Documentos Adjuntos
                </label>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 20%;">Archivo</th>
                                <th style="width: 15%;">Tipo</th>
                                <th style="width: 35%;">Descripción</th>
                                <th style="width: 10%;">Tamaño</th>
                                <th style="width: 15%;">Acciones</th>
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
                                        <span class="badge badge-info">
                                            <?= Html::encode($attachment->document_type ?? 'Otro') ?>
                                        </span>
                                    </td>
                                    <td><?= Html::encode($attachment->description ?? 'Sin descripción') ?></td>
                                    <td><?= $attachment->getFormattedSize() ?></td>
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
            </div>
        <?php endif; ?>

        <!-- ============================================================ -->
        <!-- ADD NEW DOCUMENTS - BULLETPROOF LAYOUT -->
        <!-- ============================================================ -->
        <div class="documentos-adicionales-container">
            <div class="d-flex align-items-center mb-3">
                <i class="fas fa-plus-circle text-success me-2"></i>
                <span class="font-weight-bold">Agregar Nuevos Documentos</span>
            </div>

            <!-- Table container for attachments -->
            <div id="documentos-adicionales-wrapper">
                <!-- Template row - hidden -->
                <div class="document-row template-row" style="display: none !important;">
                    <div class="attachment-row-inner">
                        <!-- Tipo -->
                        <div class="attachment-field attachment-field-tipo">
                            <label class="attachment-label">TIPO DE DOCUMENTO</label>
                            <?= Html::dropDownList('SisSiniestroAttachment[__INDEX__][document_type]', '', $documentTypeOptions, [
                                'class' => 'form-control form-control-sm attachment-input',
                                'data-index' => '__INDEX__',
                            ]) ?>
                        </div>
                        <!-- Descripción -->
                        <div class="attachment-field attachment-field-descripcion">
                            <label class="attachment-label">DESCRIPCIÓN</label>
                            <?= Html::textInput('SisSiniestroAttachment[__INDEX__][description]', '', [
                                'class' => 'form-control form-control-sm attachment-input',
                                'placeholder' => 'Breve descripción',
                                'data-index' => '__INDEX__',
                                'maxlength' => 500,
                            ]) ?>
                        </div>
                        <!-- Archivo -->
                        <div class="attachment-field attachment-field-archivo">
                            <label class="attachment-label">ARCHIVO <span class="text-danger">*</span></label>
                            <div class="custom-file-wrapper">
                                <?= Html::fileInput('SisSiniestroAttachment[__INDEX__][uploadFile]', null, [
                                    'class' => 'attachment-file-input',
                                    'accept' => 'image/*,application/pdf,.doc,.docx',
                                    'data-index' => '__INDEX__',
                                    'id' => 'attachment-file-__INDEX__',
                                ]) ?>
                                <label class="custom-file-label" for="attachment-file-__INDEX__">
                                    <i class="fas fa-upload me-1"></i> <span class="file-name-text">Seleccionar archivo...</span>
                                </label>
                            </div>
                            <small class="attachment-hint">JPG, PNG, PDF, DOC, DOCX (máx. 10MB)</small>
                        </div>
                        <!-- Eliminar -->
                        <div class="attachment-field attachment-field-eliminar">
                            <label class="attachment-label" style="visibility: hidden;">ELIMINAR</label>
                            <button type="button" class="btn btn-sm btn-danger remove-document-row w-100">
                                <i class="fas fa-times me-1"></i> Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add button -->
            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-document-row">
                <i class="fas fa-plus me-1"></i> Agregar Documento
            </button>
        </div>
        <!-- ============================================================ -->
        <!-- END ADD NEW DOCUMENTS -->
        <!-- ============================================================ -->
    </div>
</div>

<!-- ============================================================ -->
<!-- JAVASCRIPT FOR DOCUMENT MANAGEMENT -->
<!-- ============================================================ -->
<script type="text/javascript">
    (function() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAttachmentHandler);
        } else {
            initAttachmentHandler();
        }

        function initAttachmentHandler() {
            var wrapper = document.getElementById('documentos-adicionales-wrapper');
            if (!wrapper) return;

            var template = wrapper.querySelector('.template-row');
            if (!template) return;

            var addButton = document.getElementById('add-document-row');
            if (!addButton) return;

            var rowCounter = 0;

            function addDocumentRow() {
                // Clone the template
                var newRow = template.cloneNode(true);
                newRow.classList.remove('template-row');
                newRow.style.display = 'block';

                // Replace all __INDEX__ with the current counter
                var html = newRow.innerHTML;
                html = html.replace(/__INDEX__/g, rowCounter);
                newRow.innerHTML = html;

                // Update file input ID
                var fileInput = newRow.querySelector('.attachment-file-input');
                if (fileInput) {
                    var uniqueId = 'attachment-file-' + Date.now() + '-' + rowCounter;
                    fileInput.id = uniqueId;
                    var label = newRow.querySelector('.custom-file-label');
                    if (label) {
                        label.setAttribute('for', uniqueId);
                    }
                }

                // Remove button handler
                var removeBtn = newRow.querySelector('.remove-document-row');
                if (removeBtn) {
                    removeBtn.addEventListener('click', function() {
                        newRow.remove();
                    });
                }

                // File input change handler
                var fileInput2 = newRow.querySelector('.attachment-file-input');
                if (fileInput2) {
                    fileInput2.addEventListener('change', function() {
                        var fileName = this.value.split('\\').pop();
                        var label = this.parentElement.querySelector('.custom-file-label');
                        var textSpan = label ? label.querySelector('.file-name-text') : null;
                        if (fileName) {
                            if (textSpan) {
                                textSpan.textContent = fileName;
                            }
                            label.classList.add('selected');
                        } else {
                            if (textSpan) {
                                textSpan.textContent = 'Seleccionar archivo...';
                            }
                            label.classList.remove('selected');
                        }
                    });
                }

                // Append the new row
                wrapper.appendChild(newRow);
                rowCounter++;
            }

            addButton.addEventListener('click', function(e) {
                e.preventDefault();
                addDocumentRow();
            });

            // Delegate file input changes for existing rows
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('attachment-file-input')) {
                    var fileName = e.target.value.split('\\').pop();
                    var label = e.target.parentElement.querySelector('.custom-file-label');
                    var textSpan = label ? label.querySelector('.file-name-text') : null;
                    if (fileName) {
                        if (textSpan) {
                            textSpan.textContent = fileName;
                        }
                        label.classList.add('selected');
                    } else {
                        if (textSpan) {
                            textSpan.textContent = 'Seleccionar archivo...';
                        }
                        label.classList.remove('selected');
                    }
                }
            });
        }
    })();
</script>

<!-- ============================================================ -->
<!-- CSS STYLES - BULLETPROOF -->
<!-- ============================================================ -->
<?php
$css = <<<CSS
/* ============================================================
   ATTACHMENT FORM - BULLETPROOF LAYOUT
   ============================================================ */

.document-row {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 10px 16px 8px 16px;
    margin-bottom: 8px;
    border: 1px solid #e9ecef;
    display: block;
    width: 100%;
}

.document-row:hover {
    background: #f1f3f5;
}

/* --- Flexbox Row (Forces horizontal layout) --- */
.attachment-row-inner {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    align-items: flex-end;
    gap: 10px;
    width: 100%;
}

/* --- Each Field --- */
.attachment-field {
    flex: 0 0 auto;
}

.attachment-field-tipo {
    width: 18%;
    min-width: 150px;
}

.attachment-field-descripcion {
    width: 22%;
    min-width: 160px;
}

.attachment-field-archivo {
    width: 38%;
    min-width: 200px;
    flex: 1 1 auto;
}

.attachment-field-eliminar {
    width: 12%;
    min-width: 90px;
}

/* --- Labels --- */
.attachment-label {
    display: block;
    margin-bottom: 3px;
    font-size: 10px;
    font-weight: 600;
    color: #6c757d;
    letter-spacing: 0.3px;
    text-transform: uppercase;
}

/* --- Inputs --- */
.attachment-input {
    font-size: 13px;
    padding: 0.25rem 0.5rem;
    height: calc(1.5em + 0.5rem + 2px);
    width: 100%;
}

/* --- Custom File Input --- */
.custom-file-wrapper {
    position: relative;
    width: 100%;
}

.attachment-file-input {
    position: absolute;
    opacity: 0;
    width: 0.1px;
    height: 0.1px;
    z-index: -1;
}

.custom-file-label {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    padding: 0.25rem 0.5rem;
    height: calc(1.5em + 0.5rem + 2px);
    line-height: 1.5;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    background-color: #fff;
    cursor: pointer;
    font-size: 13px;
    color: #495057;
    position: relative;
    padding-right: 75px;
}

.custom-file-label::after {
    content: "Examinar";
    background-color: #e9ecef;
    border-left: 1px solid #ced4da;
    border-radius: 0 0.25rem 0.25rem 0;
    height: 100%;
    line-height: 1.5;
    padding: 0.25rem 0.5rem;
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    font-size: 12px;
    color: #495057;
    font-weight: 500;
}

.custom-file-label.selected {
    background: #e8f0fe;
    border-color: #0078d4;
}

.custom-file-label .file-name-text {
    display: inline-block;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.attachment-hint {
    font-size: 9px;
    color: #6c757d;
    display: block;
    margin-top: 2px;
    white-space: nowrap;
}

/* --- Remove Button --- */
.remove-document-row {
    font-size: 12px;
    padding: 0.25rem 0.5rem;
    width: 100%;
}

/* ============================================================
   TABLE STYLES - WHITE HEADERS + LARGER FONT
   ============================================================ */

/* --- Force white text in table headers --- */
.table-bordered thead th {
    color: #ffffff !important;
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;
    text-align: center !important;
    vertical-align: middle !important;
    font-weight: 600 !important;
    white-space: nowrap !important;
    font-size: 14px !important;
    padding: 12px 10px !important;
}

.table-bordered thead th i {
    color: #ffffff !important;
}

.table-bordered thead th .badge {
    color: #ffffff !important;
}

/* --- Force larger font in table body --- */
.table-bordered tbody td {
    font-size: 14px !important;
    padding: 10px 10px !important;
    vertical-align: middle !important;
    color: #1a1a1a !important;
}

/* --- Larger font for badges inside table --- */
.table-bordered tbody td .badge {
    font-size: 13px !important;
    padding: 4px 10px !important;
}

/* --- Larger font for buttons inside table --- */
.table-bordered tbody td .btn {
    font-size: 13px !important;
    padding: 4px 10px !important;
}

.table-bordered tbody td .btn i {
    font-size: 14px !important;
}

/* --- Larger font for file name in table --- */
.table-bordered tbody td i.fas {
    font-size: 16px !important;
}

/* --- Also ensure the table headers in the attachments section are white --- */
.table-sm thead th {
    color: #ffffff !important;
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;
    font-size: 14px !important;
    padding: 12px 10px !important;
}

.table-sm tbody td {
    font-size: 14px !important;
    padding: 10px 10px !important;
}

/* --- Responsive --- */
@media (max-width: 992px) {
    .attachment-field-tipo { width: 16%; min-width: 120px; }
    .attachment-field-descripcion { width: 20%; min-width: 130px; }
    .attachment-field-archivo { width: 40%; min-width: 160px; }
    .attachment-field-eliminar { width: 14%; min-width: 80px; }
    .custom-file-label { font-size: 12px; padding-right: 65px; }
    .custom-file-label::after { font-size: 10px; padding: 0.25rem 0.4rem; }
}

@media (max-width: 768px) {
    .document-row {
        padding: 12px 12px 8px 12px;
    }
    .attachment-row-inner {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 6px;
    }
    .attachment-field {
        width: 100% !important;
        min-width: 100% !important;
        flex: 1 1 100% !important;
    }
    .attachment-field-eliminar {
        width: 100% !important;
        min-width: 100% !important;
    }
    .remove-document-row {
        width: auto !important;
        min-width: 120px;
    }
    .attachment-hint {
        white-space: normal;
    }
    .table-bordered tbody td {
        font-size: 13px !important;
        padding: 8px 6px !important;
    }
    .table-bordered thead th {
        font-size: 12px !important;
        padding: 8px 6px !important;
    }
}

@media (max-width: 576px) {
    .custom-file-label {
        font-size: 11px;
        padding-right: 55px;
    }
    .custom-file-label::after {
        font-size: 9px;
        padding: 0.25rem 0.3rem;
    }
    .table-bordered tbody td {
        font-size: 12px !important;
        padding: 6px 4px !important;
    }
    .table-bordered thead th {
        font-size: 11px !important;
        padding: 6px 4px !important;
    }
}

/* --- Print --- */
@media print {
    .document-row {
        break-inside: avoid;
        background: #f8f9fa !important;
    }
    .table-bordered thead th {
        background: #333 !important;
        color: #fff !important;
    }
}
CSS;


$this->registerCss($css);
?>