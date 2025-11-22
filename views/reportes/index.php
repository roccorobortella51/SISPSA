<?php
// app/views/reportes/index.php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
$this->title = 'Reporte de Pagos de Afiliados';
$this->params['breadcrumbs'][] = $this->title;

// La URL apunta a la acción del controlador que devuelve el GridView en HTML
$ajaxUrl = Url::to(['get-pagos-detail']); 
// La URL apunta a la acción que generará el PDF
$pdfUrl = Url::to(['generate-pdf']); 
?>

<div class="reportes-pagos-index">
    <div class="row">
        <div class="col-12">
            <h1 class="text-center mb-4">
                <i class="fas fa-chart-line text-success"></i> <?= Html::encode($this->title) ?>
            </h1>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header bg-dark">
            <h4 class="mb-0 fw-bold text-white fs-4">Selección de Periodo y Estado</h4>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-5">
                    <label for="pago-status-selector" class="fw-bold">Estado de Pago:</label>
                    <select id="pago-status-selector" class="form-control">
                        <option value="Por Conciliar">Pendientes por Conciliar</option>
                        <option value="Conciliado">Pagos Conciliados</option>
                    </select>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-8">
                    <?= Html::button('<i class="fas fa-sun"></i> Hoy', ['class' => 'btn btn-primary btn-range me-2', 'data-range' => 'day']) ?>
                    <?= Html::button('<i class="fas fa-calendar-week"></i> Última Semana', ['class' => 'btn btn-info btn-range me-2', 'data-range' => 'week']) ?>
                    <?= Html::button('<i class="fas fa-calendar-alt"></i> Mes Actual', ['class' => 'btn btn-success btn-range me-2', 'data-range' => 'month']) ?>
                </div>
                <div class="col-md-4 text-end">
                    <div class="input-group">
                        <input type="date" id="specific-date" class="form-control" placeholder="Seleccionar fecha">
                        <button class="btn btn-secondary" type="button" id="btn-specific-date">
                            <i class="fas fa-calendar-check"></i> Consultar Fecha
                        </button>
                    </div>
                </div>
            </div>
            </div>
    </div>

    <div class="row" id="report-results">
        </div>
</div>

<?php 
$this->registerJs(<<<JS
    // Cache selectors
    const \$results = $('#report-results');
    // Note: \$pdfBtn is dynamically loaded in the partial view. We should target the one in _pagos-grid.php.
    // For simplicity, we'll assume the PDF button ID is 'btn-print-pdf' as used in other report implementations.
    const \$pdfBtn = $('#btn-print-pdf'); // This will be set after the AJAX call success
    
    // NEW: Status selector variable
    const \$statusSelector = $('#pago-status-selector');
    const ajaxUrl = '{$ajaxUrl}';
    const pdfUrl = '{$pdfUrl}';

    /**
     * Función principal para cargar y mostrar los datos del reporte.
     * @param {object} params Parámetros (range, specific_date)
     */
    function fetchAndDisplayData(params) {
        // 1. Obtener el estado seleccionado
        const status = \$statusSelector.val(); // <--- NEW LINE
        
        // 2. Agregar el estado a los parámetros para el AJAX
        const data = { ...params, status: status }; // <--- MODIFIED LINE

        // Mostrar spinner de carga
        \$results.html('<div class=\"col-12 text-center my-5\"><i class=\"fas fa-spinner fa-spin fa-3x\"></i><p class=\"mt-3\">Cargando reporte...</p></div>');
        
        // 3. Petición AJAX
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    \$results.html(response.html);
                    
                    // Update PDF button after content is loaded
                    const \$pdfBtnAfterLoad = $('#btn-print-pdf');
                    let pdfFullUrl = pdfUrl + '?' + new URLSearchParams(data).toString();
                    \$pdfBtnAfterLoad.attr('href', pdfFullUrl);
                    \$pdfBtnAfterLoad.prop('disabled', false); 
                } else {
                    \$results.html('<div class=\"col-12\"><div class=\"alert alert-danger\">Error al cargar el reporte: ' + response.message + '</div></div>');
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: ", status, error, xhr.responseText);
                
                let errorMsg = 'Error de Conexión.';
                if (xhr.status === 403) {
                    errorMsg = 'Acceso denegado (403). Verifique su sesión.';
                } else if (xhr.status === 500) {
                    errorMsg = 'Error del servidor (500). Revise los logs.';
                }
                
                \$results.html('<div class=\"col-12\"><div class=\"alert alert-danger\">Error: ' + errorMsg + '</div></div>');
            }
        });
    }

    // --- Event Handlers ---
    
    // 1. Botones de Rango Predefinido
    $('.btn-range').on('click', function() {
        const range = $(this).data('range');
        fetchAndDisplayData({ range: range });
        $('#specific-date').val(''); // Limpiar fecha específica
    });

    // 2. Botón de Fecha Específica
    $('#btn-specific-date').on('click', function() {
        const specificDate = $('#specific-date').val();
        if (specificDate) {
            fetchAndDisplayData({ specific_date: specificDate });
        } else {
            alert('Por favor, seleccione una fecha.');
        }
    });
    
    // 3. Selector de Estado (NUEVO)
    \$statusSelector.on('change', function() {
        // Al cambiar el estado, se re-consulta con el rango actual (si existe)
        // Por defecto, se usa el rango 'day' si no hay otro rango activo
        // Forzamos el reporte del día actual al cambiar el selector
        fetchAndDisplayData({ range: 'day' });
        // Visually activate the 'Hoy' button
        $('.btn-range[data-range=\"day\"]').addClass('active').siblings('.btn-range').removeClass('active');
        $('#specific-date').val('');
    });

    // 4. Trigger the initial 'Today' report when the page loads
    fetchAndDisplayData({ range: 'day' });
    $('.btn-range[data-range=\"day\"]').addClass('active');

JS
);
?>