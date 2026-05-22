<?php

use kartik\file\FileInput;
?>

<!-- ===== DOCUMENTOS ADICIONALES SECTION ===== -->
<div class="card mb-4">
    <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
        <i class="fas fa-folder-open me-2"></i> Documentos Adicionales
        <span class="badge badge-light ml-2">Opcional</span>
    </div>
    <div class="card-body">
        <div class="alert alert-info py-2 px-3 mb-3" style="background-color: #e7f3ff; border-left: 4px solid #17a2b8;">
            <i class="fas fa-info-circle me-2"></i>
            Puede adjuntar hasta <strong>5 documentos adicionales</strong> (Certificados médicos, exámenes de laboratorio, estudios de imagen, autorizaciones, etc.)
        </div>

        <div id="docs-container-<?= uniqid() ?>">
            <div class="docs-list">
                <!-- Existing documents will be loaded here -->
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <button type="button" class="btn btn-outline-primary add-doc-btn" style="font-size: 1rem; padding: 0.5rem 1rem;">
                        <i class="fas fa-plus-circle me-2"></i> Agregar otro documento
                    </button>
                </div>
            </div>
        </div>
        <input type="hidden" class="doc-counter" value="0">
    </div>
</div>

<style>
    .doc-item {
        background-color: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 20px;
        transition: all 0.2s ease;
        border: 1px solid #e0e0e0;
    }

    .doc-item:hover {
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        border-color: #c0c0c0;
    }

    .doc-item .form-label {
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
        color: #2a5298;
        display: block;
    }

    .form-select,
    .form-control {
        border-radius: 6px;
        border: 1px solid #ced4da;
        font-size: 1rem !important;
        padding: 0.6rem 0.75rem !important;
        width: 100%;
    }

    .form-select:focus,
    .form-control:focus {
        border-color: #2a5298;
        box-shadow: 0 0 0 0.2rem rgba(42, 82, 152, 0.25);
    }

    .doc-item .file-input {
        margin-top: 8px;
    }

    .doc-item .file-input .btn {
        font-size: 0.95rem;
        padding: 0.5rem 1rem;
    }

    .doc-remove-btn {
        font-size: 1rem;
        padding: 0.6rem 0.75rem;
        white-space: nowrap;
        width: 100%;
    }

    .doc-remove-btn i {
        margin-right: 6px;
    }

    /* Align button with input fields */
    .button-wrapper {
        display: flex;
        align-items: flex-end;
        height: 100%;
    }

    .button-wrapper button {
        margin-bottom: 0;
    }

    /* Microsoft-style focus ring */
    .form-select:focus-visible,
    .form-control:focus-visible,
    .doc-remove-btn:focus-visible {
        outline: none;
        ring: 2px solid #2a5298;
    }

    @media (max-width: 768px) {
        .button-wrapper {
            margin-top: 10px;
        }
    }
</style>

<?php
// Register JavaScript for dynamic document addition
$this->registerJs(
    <<<'JS'
$(document).ready(function() {
    // Track initialized file inputs to prevent duplicates
    var initializedFileInputs = {};
    
    // Function to initialize FileInput on a visible element
    function initFileInput($element, instanceId) {
        if (!$element.length) return;
        
        // Generate unique ID if not provided
        var uniqueId = instanceId || 'fileinput-' + Date.now() + '-' + Math.random().toString(36).substr(2, 8);
        
        // Check if already initialized
        if ($element.data('fileinput')) {
            try {
                $element.fileinput('destroy');
            } catch(e) {
                console.log('Error destroying fileinput:', e);
            }
        }
        
        // Set unique ID for the element
        $element.attr('id', uniqueId);
        
        // Initialize FileInput
        $element.fileinput({
            theme: 'fa5',
            browseClass: 'btn btn-primary',
            removeClass: 'btn btn-secondary',
            removeIcon: '<i class="fas fa-trash"></i> ',
            showUpload: false,
            showCancel: false,
            showCaption: true,
            previewFileType: 'image',
            allowedFileExtensions: ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'],
            maxFileSize: 10240,
            dropZoneEnabled: false,
            showClose: false,
            browseLabel: 'Seleccionar archivo',
            removeLabel: 'Quitar',
            layoutTemplates: {
                main1: "{preview}\n{remove}\n{upload}\n{browse}\n{caption}",
                main2: "{preview}\n{remove}\n{upload}\n{browse}\n{caption}",
            }
        });
        
        // Mark as initialized
        initializedFileInputs[uniqueId] = true;
        
        return uniqueId;
    }
    
    // Function to update remove buttons visibility
    function updateRemoveButtons($container) {
        var $visibleItems = $container.find('.doc-item:visible');
        var $removeBtns = $container.find('.remove-doc');
        if ($visibleItems.length > 1) {
            $removeBtns.show();
        } else {
            $removeBtns.hide();
        }
    }
    
    // Function to create a new document item HTML
    function createDocItemHtml(index, tipoValue, descripcionValue) {
        var uniqueFileId = 'docfile-' + index + '-' + Date.now() + '-' + Math.random().toString(36).substr(2, 6);
        
        return '<div class="doc-item mb-3 p-4 border rounded bg-light" data-index="' + index + '">' +
            '<div class="row">' +
                '<div class="col-md-4">' +
                    '<label class="form-label"><i class="fas fa-tag me-2"></i> Tipo de Documento</label>' +
                    '<select class="form-select doc-tipo" name="OtrosDocumentos[' + index + '][tipo]" style="font-size: 1rem; padding: 0.6rem 0.75rem;">' +
                        '<option value="">Seleccione tipo...</option>' +
                        '<option value="Certificado Médico"' + (tipoValue === 'Certificado Médico' ? ' selected' : '') + '>Certificado Médico</option>' +
                        '<option value="Examen de Laboratorio"' + (tipoValue === 'Examen de Laboratorio' ? ' selected' : '') + '>Examen de Laboratorio</option>' +
                        '<option value="Estudio de Imagen"' + (tipoValue === 'Estudio de Imagen' ? ' selected' : '') + '>Estudio de Imagen</option>' +
                        '<option value="Autorización"' + (tipoValue === 'Autorización' ? ' selected' : '') + '>Autorización Médica</option>' +
                        '<option value="Referencia"' + (tipoValue === 'Referencia' ? ' selected' : '') + '>Referencia Médica</option>' +
                        '<option value="Historia Clínica"' + (tipoValue === 'Historia Clínica' ? ' selected' : '') + '>Historia Clínica</option>' +
                        '<option value="Nota de Evolución"' + (tipoValue === 'Nota de Evolución' ? ' selected' : '') + '>Nota de Evolución</option>' +
                        '<option value="Consentimiento Informado"' + (tipoValue === 'Consentimiento Informado' ? ' selected' : '') + '>Consentimiento Informado</option>' +
                        '<option value="Otro"' + (tipoValue === 'Otro' ? ' selected' : '') + '>Otro</option>' +
                    '</select>' +
                '</div>' +
                '<div class="col-md-5">' +
                    '<label class="form-label"><i class="fas fa-file-alt me-2"></i> Descripción (opcional)</label>' +
                    '<input type="text" class="form-control doc-descripcion" name="OtrosDocumentos[' + index + '][descripcion]" placeholder="Ej: Resultados de laboratorio, Radiografía de tórax, etc." value="' + (descripcionValue || '') + '" style="font-size: 1rem; padding: 0.6rem 0.75rem;">' +
                '</div>' +
                '<div class="col-md-3">' +
                    '<div class="button-wrapper">' +
                        '<button type="button" class="btn btn-danger remove-doc doc-remove-btn" style="display: none;">' +
                            '<i class="fas fa-trash-alt me-2"></i> Eliminar documento' +
                        '</button>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="row mt-4">' +
                '<div class="col-12">' +
                    '<label class="form-label"><i class="fas fa-paperclip me-2"></i> Archivo</label>' +
                    '<input type="file" class="doc-file" name="SisSiniestro[otrosDocumentosFile][' + index + ']" id="' + uniqueFileId + '">' +
                '</div>' +
            '</div>' +
        '</div>';
    }
    
    // Function to add a new document item
    function addDocumentItem($container, tipoValue, descripcionValue) {
        var $counter = $container.find('.doc-counter');
        var docCounter = parseInt($counter.val()) || 0;
        var maxDocs = 5;
        
        if (docCounter >= maxDocs) {
            Swal.fire({
                icon: 'warning',
                title: 'Límite alcanzado',
                text: 'Solo puede adjuntar un máximo de ' + maxDocs + ' documentos adicionales.',
                confirmButtonColor: '#2a5298'
            });
            return false;
        }
        
        docCounter++;
        $counter.val(docCounter);
        
        // Create new item HTML
        var newItemHtml = createDocItemHtml(docCounter, tipoValue || '', descripcionValue || '');
        var $newItem = $(newItemHtml);
        
        // Append to container
        $container.find('.docs-list').append($newItem);
        
        // Initialize FileInput on the new element
        var $fileInput = $newItem.find('.doc-file');
        initFileInput($fileInput);
        
        // Update remove buttons
        updateRemoveButtons($container);
        
        // Scroll to the new item smoothly
        $('html, body').animate({
            scrollTop: $newItem.offset().top - 100
        }, 300);
        
        return true;
    }
    
    // Handle add button click
    $(document).on('click', '.add-doc-btn', function() {
        var $container = $(this).closest('[id^="docs-container-"]');
        addDocumentItem($container, '', '');
    });
    
    // Handle remove button click
    $(document).on('click', '.remove-doc', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $item = $(this).closest('.doc-item');
        var $container = $item.closest('[id^="docs-container-"]');
        var $fileInput = $item.find('.doc-file');
        
        // Destroy FileInput instance
        if ($fileInput.data('fileinput')) {
            try {
                $fileInput.fileinput('destroy');
            } catch(e) {
                console.log('Error destroying fileinput:', e);
            }
        }
        
        // Remove the item with animation
        $item.fadeOut(200, function() {
            $(this).remove();
            
            // Re-index remaining items
            var newIndex = 1;
            $container.find('.doc-item:visible').each(function() {
                var $c = $(this);
                $c.attr('data-index', newIndex);
                
                // Update name attributes
                $c.find('.doc-tipo').attr('name', 'OtrosDocumentos[' + newIndex + '][tipo]');
                $c.find('.doc-descripcion').attr('name', 'OtrosDocumentos[' + newIndex + '][descripcion]');
                
                // Update file input
                var $file = $c.find('.doc-file');
                var newFileId = 'docfile-' + newIndex + '-' + Date.now() + '-' + Math.random().toString(36).substr(2, 6);
                $file.attr('name', 'SisSiniestro[otrosDocumentosFile][' + newIndex + ']');
                $file.attr('id', newFileId);
                
                // Reinitialize file input with new ID
                if ($file.data('fileinput')) {
                    try {
                        $file.fileinput('destroy');
                    } catch(e) {}
                }
                initFileInput($file, newFileId);
                
                newIndex++;
            });
            
            // Update counter
            var docCounter = $container.find('.doc-item:visible').length;
            $container.find('.doc-counter').val(docCounter);
            
            // Update remove buttons
            updateRemoveButtons($container);
        });
    });
    
    // Initialize existing documents from server-side
    function initializeExistingDocuments() {
        $('[id^="docs-container-"]').each(function() {
            var $container = $(this);
            var docCount = 0;
            
            // Look for existing file inputs that might have been rendered by PHP
            $container.find('.doc-file').each(function() {
                var $this = $(this);
                var $parent = $this.closest('.doc-item');
                
                if ($parent.length && $parent.css('display') !== 'none') {
                    // Get existing values
                    var tipoValue = $parent.find('.doc-tipo').val() || '';
                    var descripcionValue = $parent.find('.doc-descripcion').val() || '';
                    var currentIndex = $parent.attr('data-index') || docCount + 1;
                    
                    // Update index if needed
                    if (!$parent.attr('data-index') || $parent.attr('data-index') === '0') {
                        docCount++;
                        $parent.attr('data-index', docCount);
                        
                        // Update names
                        $parent.find('.doc-tipo').attr('name', 'OtrosDocumentos[' + docCount + '][tipo]');
                        $parent.find('.doc-descripcion').attr('name', 'OtrosDocumentos[' + docCount + '][descripcion]');
                        $this.attr('name', 'SisSiniestro[otrosDocumentosFile][' + docCount + ']');
                    } else {
                        docCount = parseInt($parent.attr('data-index'));
                    }
                    
                    // Initialize file input
                    var uniqueId = 'docfile-existing-' + docCount + '-' + Date.now();
                    initFileInput($this, uniqueId);
                }
            });
            
            // Update counter
            var existingCount = $container.find('.doc-item:visible').length;
            $container.find('.doc-counter').val(existingCount);
            
            // Update remove buttons visibility
            updateRemoveButtons($container);
        });
    }
    
    // Initialize remove buttons visibility on page load
    initializeExistingDocuments();
});
JS
);
?>