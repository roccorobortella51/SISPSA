$(document).ready(function() {
    let documentCounter = parseInt($('#documento-counter').val()) || 0;
    const maxDocuments = 5;
    
    function updateRemoveButtons() {
        const visibleItems = $('.documento-item:visible').length;
        $('.remove-documento').each(function() {
            if (visibleItems > 1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }
    
    function initializeFileInput(fileInputElement) {
        var $element = $(fileInputElement);
        
        if (!$element.length || $element.closest('.documento-item[data-index="0"]').length) {
            return;
        }
        
        if ($element.data('fileinput')) {
            $element.fileinput('destroy');
        }
        
        $element.val('');
        
        $element.fileinput({
            theme: 'fa5',
            browseClass: 'btn btn-outline-secondary btn-sm',
            removeClass: 'btn btn-secondary btn-sm',
            removeIcon: '<i class="fas fa-trash"></i>',
            showUpload: false,
            showCancel: false,
            showCaption: true,
            allowedFileExtensions: ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'],
            maxFileSize: 10240,
            dropZoneEnabled: false,
            showClose: false,
            browseLabel: '<i class="fas fa-folder-open"></i> Seleccionar archivo',
            removeLabel: '<i class="fas fa-trash"></i> Quitar',
            fileActionSettings: {
                showZoom: false,
                showDrag: false,
            },
            layoutTemplates: {
                main1: '{preview}\n{remove}\n{browse}\n{caption}',
                main2: '{preview}\n{remove}\n{browse}\n{caption}'
            }
        });
    }
    
    function addDocumentItem() {
        if (documentCounter >= maxDocuments) {
            alert('Solo puede adjuntar un máximo de ' + maxDocuments + ' documentos adicionales.');
            return;
        }
        
        documentCounter++;
        $('#documento-counter').val(documentCounter);
        
        var $template = $('.documento-item[data-index="0"]').clone();
        $template.removeAttr('style');
        $template.attr('data-index', documentCounter);
        $template.css('display', 'block');
        
        $template.find('.documento-tipo').attr('name', 'OtrosDocumentos[' + documentCounter + '][tipo]');
        $template.find('.documento-descripcion').attr('name', 'OtrosDocumentos[' + documentCounter + '][descripcion]');
        
        var $fileInput = $template.find('.otros-documento-file');
        $fileInput.attr('name', 'SisSiniestro[otrosDocumentosFile][' + documentCounter + ']');
        $fileInput.attr('id', 'otrosdocumentosfile-' + documentCounter);
        
        $template.find('.documento-tipo').val('');
        $template.find('.documento-descripcion').val('');
        
        if ($fileInput.data('fileinput')) {
            $fileInput.fileinput('destroy');
        }
        $fileInput.val('');
        
        $('.otros-documentos-list').append($template);
        
        setTimeout(function() {
            initializeFileInput($fileInput);
        }, 100);
        
        updateRemoveButtons();
    }
    
    $('#add-documento-btn').off('click').on('click', function(e) {
        e.preventDefault();
        addDocumentItem();
    });
    
    $(document).on('click', '.remove-documento', function(e) {
        e.preventDefault();
        var $item = $(this).closest('.documento-item');
        var $fileInput = $item.find('.otros-documento-file');
        
        if ($fileInput.data('fileinput')) {
            $fileInput.fileinput('destroy');
        }
        
        $item.remove();
        
        var newIndex = 1;
        $('.documento-item:visible').each(function() {
            var $current = $(this);
            $current.attr('data-index', newIndex);
            $current.find('.documento-tipo').attr('name', 'OtrosDocumentos[' + newIndex + '][tipo]');
            $current.find('.documento-descripcion').attr('name', 'OtrosDocumentos[' + newIndex + '][descripcion]');
            $current.find('.otros-documento-file').attr('name', 'SisSiniestro[otrosDocumentosFile][' + newIndex + ']');
            $current.find('.otros-documento-file').attr('id', 'otrosdocumentosfile-' + newIndex);
            newIndex++;
        });
        
        documentCounter = $('.documento-item:visible').length;
        $('#documento-counter').val(documentCounter);
        updateRemoveButtons();
    });
    
    $('.otros-documento-file').each(function() {
        var $this = $(this);
        if ($this.closest('.documento-item[data-index="0"]').length === 0 && !$this.data('fileinput')) {
            setTimeout(function() {
                initializeFileInput($this);
            }, 100);
        }
    });
    
    updateRemoveButtons();
});