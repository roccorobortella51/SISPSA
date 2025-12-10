<?php
// app/views/reportes/index.php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\RmClinica;

/** @var yii\web\View $this */
$this->title = 'Reporte de Pagos de Afiliados';
$this->params['breadcrumbs'][] = $this->title;

// URLs
$ajaxUrl = Url::to(['get-pagos-detail']); 
$pdfUrl = Url::to(['generate-pdf']); 

// Obtener todas las clínicas activas
$clinicas = RmClinica::find()->where(['estatus' => 'Activo'])->orderBy('nombre')->all();
?>

<div class="reportes-pagos-index">
    <!-- Header -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="text-center">
                <h1 class="display-5 fw-bold text-primary mb-3">
                    <i class="fas fa-chart-line me-2"></i> <?= Html::encode($this->title) ?>
                </h1>
                <p class="lead text-muted">Sistema de reportes avanzado con filtros dinámicos y exportación a PDF</p>
            </div>
        </div>
    </div>

    <!-- Panel de Filtros - Diseño Profesional -->
    <div class="card border-0 shadow-lg mb-5">
        <div class="card-header bg-gradient-primary text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0 fw-bold">
                    <i class="fas fa-sliders-h me-2"></i> Panel de Filtros
                </h4>
                <span class="badge bg-light text-primary fs-6">Reporte Dinámico</span>
            </div>
        </div>
        
        <div class="card-body p-4">
            <!-- Fila 1: Filtros Principales -->
            <div class="row g-4 mb-4">
                <!-- Columna 1: Filtro de Estado -->
                <div class="col-lg-6">
                    <div class="filter-card">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-check-circle text-primary me-2 fs-5"></i>
                            <h5 class="mb-0 fw-bold">Estado del Pago</h5>
                        </div>
                        <select id="pago-status-selector" class="form-select form-select-lg border-primary">
                            <option value="Por Conciliar">⏳ Pendientes por Conciliar</option>
                            <option value="Conciliado">✅ Pagos Conciliados</option>
                            <option value="todos">📊 Todos los Estados</option>
                        </select>
                        <small class="text-muted mt-1 d-block">Seleccione el estado de los pagos a visualizar</small>
                    </div>
                </div>
                
                <!-- Columna 2: Filtro de Clínicas -->
                <div class="col-lg-6">
                    <div class="filter-card">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-hospital text-success me-2 fs-5"></i>
                            <h5 class="mb-0 fw-bold">Filtro por Clínica</h5>
                        </div>
                        <select id="clinica-filter" class="form-select select2-multiple" multiple="multiple">
                            <option value="todas" selected>🏥 Todas las Clínicas</option>
                            <?php foreach ($clinicas as $clinica): ?>
                                <option value="<?= $clinica->id ?>">
                                    <?= Html::encode($clinica->nombre) ?> 
                                    <span class="text-muted">(<?= Html::encode($clinica->rif) ?>)</span>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted mt-1 d-block">Seleccione una o múltiples clínicas</small>
                    </div>
                </div>
            </div>
            
            <!-- Separador Decorativo -->
            <div class="text-center my-4">
                <div class="d-flex align-items-center">
                    <hr class="flex-grow-1">
                    <span class="px-3 text-muted">
                        <i class="fas fa-calendar-alt me-1"></i> Configuración de Fechas
                    </span>
                    <hr class="flex-grow-1">
                </div>
            </div>
            
            <!-- Fila 2: Rango de Fechas -->
            <div class="row g-4 mb-4">
                <!-- Columna 1: Rangos Predefinidos -->
                <div class="col-lg-8">
                    <div class="filter-card">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-clock text-warning me-2 fs-5"></i>
                            <h5 class="mb-0 fw-bold">Rangos de Fecha Predefinidos</h5>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                            <?= Html::button('<i class="fas fa-sun me-2"></i> Hoy', [
                                'class' => 'btn btn-outline-primary btn-range px-4 py-2 fw-medium',
                                'data-range' => 'day'
                            ]) ?>
                            <?= Html::button('<i class="fas fa-calendar-week me-2"></i> Última Semana', [
                                'class' => 'btn btn-outline-info btn-range px-4 py-2 fw-medium',
                                'data-range' => 'week'
                            ]) ?>
                            <?= Html::button('<i class="fas fa-calendar-alt me-2"></i> Mes Actual', [
                                'class' => 'btn btn-outline-success btn-range px-4 py-2 fw-medium',
                                'data-range' => 'month'
                            ]) ?>
                            <?= Html::button('<i class="fas fa-calendar-minus me-2"></i> Mes Anterior', [
                                'class' => 'btn btn-outline-warning btn-range px-4 py-2 fw-medium',
                                'data-range' => 'last-month'
                            ]) ?>
                        </div>
                        <small class="text-muted mt-2 d-block">Seleccione un período rápido de consulta</small>
                    </div>
                </div>
                
                <!-- Columna 2: Fecha Específica -->
                <div class="col-lg-4">
                    <div class="filter-card">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-calendar-day text-danger me-2 fs-5"></i>
                            <h5 class="mb-0 fw-bold">Fecha Específica</h5>
                        </div>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-calendar text-primary"></i>
                            </span>
                            <input type="date" id="specific-date" class="form-control border-primary" 
                                   placeholder="YYYY-MM-DD">
                            <button class="btn btn-primary" type="button" id="btn-specific-date">
                                <i class="fas fa-search me-1"></i> Consultar
                            </button>
                        </div>
                        <small class="text-muted mt-2 d-block">Seleccione una fecha específica para el reporte</small>
                    </div>
                </div>
            </div>
            
            <!-- Botón de Acción Principal -->
            <div class="text-center mt-5 pt-3 border-top">
                <?= Html::button('<i class="fas fa-rocket me-2"></i> Generar Reporte', [
                    'id' => 'btn-aplicar-filtros',
                    'class' => 'btn btn-primary btn-lg px-5 py-3 fw-bold shadow-sm'
                ]) ?>
                <p class="text-muted mt-3 mb-0">
                    <i class="fas fa-info-circle me-1"></i> 
                    Los filtros se aplican automáticamente al cambiar cualquier opción
                </p>
            </div>
        </div>
        
        <!-- Footer del Card -->
        <div class="card-footer bg-light py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-database text-primary me-2"></i>
                        <small class="text-muted">Datos en tiempo real • Actualización automática</small>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">
                        <i class="fas fa-file-pdf me-1 text-danger"></i>
                        Exportable a PDF con un solo clic
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de Resultados -->
    <div class="row" id="report-results">
        <!-- Contenido dinámico cargado aquí -->
    </div>
</div>

<?php 
// Estilos CSS mejorados
$this->registerCss(<<<CSS
    .filter-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .filter-card:hover {
        border-color: #007bff;
        box-shadow: 0 5px 15px rgba(0,123,255,0.1);
    }
    
    .btn-range.active {
        background-color: #007bff !important;
        color: white !important;
        border-color: #007bff !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,123,255,0.3);
    }
    
    .select2-container--default .select2-selection--multiple {
        border: 2px solid #dee2e6;
        border-radius: 8px;
        min-height: 48px;
        padding: 4px;
    }
    
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #007bff;
        box-shadow: 0 0 0 0.25rem rgba(0,123,255,0.25);
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        border-radius: 20px;
        padding: 4px 12px;
        font-weight: 500;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: white;
        margin-right: 5px;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #ff6b6b;
    }
    
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }
    
    /* Mejoras para la tabla de resultados */
    .hospital-summary-card {
        border-left: 4px solid #007bff;
        transition: all 0.3s ease;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .hospital-summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .summary-totals {
        background: linear-gradient(135deg, #2c3e50 0%, #4a6491 100%);
        color: white;
        border-radius: 10px;
    }
    
    /* Animación de carga */
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    
    .loading-pulse {
        animation: pulse 1.5s infinite;
    }
CSS
);

// JavaScript reorganizado
$this->registerJs(<<<JS
    // =============================================
    // CONFIGURACIÓN INICIAL
    // =============================================
    const config = {
        ajaxUrl: '{$ajaxUrl}',
        pdfUrl: '{$pdfUrl}',
        selectors: {
            results: '#report-results',
            status: '#pago-status-selector',
            clinica: '#clinica-filter',
            dateInput: '#specific-date',
            aplicarBtn: '#btn-aplicar-filtros',
            specificDateBtn: '#btn-specific-date'
        }
    };
    
    // =============================================
    // INICIALIZACIÓN DE COMPONENTES
    // =============================================
    function initComponents() {
        // Inicializar Select2 para selección múltiple
        if (\$.fn.select2 && \$(config.selectors.clinica).length) {
            \$(config.selectors.clinica).select2({
                placeholder: "Seleccione clínicas...",
                width: '100%',
                allowClear: true,
                theme: 'bootstrap-5',
                closeOnSelect: false
            });
        }
        
        // Marcar botón inicial como activo
        \$('.btn-range[data-range="day"]').addClass('active');
    }
    
    // =============================================
    // FUNCIONES UTILITARIAS
    // =============================================
    function showLoading() {
        \$(config.selectors.results).html(`
            <div class="col-12">
                <div class="card border-0 shadow">
                    <div class="card-body text-center py-5">
                        <div class="loading-pulse">
                            <i class="fas fa-chart-bar fa-4x text-primary mb-4"></i>
                            <h4 class="text-primary">Generando Reporte</h4>
                            <p class="text-muted">Por favor espere mientras procesamos los datos...</p>
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `);
    }
    
    function showError(message) {
        \$(config.selectors.results).html(`
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Error:</strong> \${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        `);
    }
    
    function updatePDFButton(params, status, clinicasSeleccionadas) {
        const pdfBtn = \$('#btn-print-pdf');
        if (pdfBtn.length) {
            const pdfParams = {
                range: params.range || 'day',
                status: status,
                clinicas: clinicasSeleccionadas ? clinicasSeleccionadas.join(',') : 'todas',
                ...(params.specific_date && { specific_date: params.specific_date })
            };
            
            const pdfUrl = config.pdfUrl + '?' + new URLSearchParams(pdfParams).toString();
            pdfBtn.attr('href', pdfUrl).prop('disabled', false);
        }
    }
    
    // Function to show notifications
    function showNotification(type, title, message) {
        // Remove any existing notifications
        \$('.export-notification').remove();
        
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const iconClass = type === 'success' ? 'check-circle' : 'exclamation-triangle';
        
        const notification = 
            '<div class="export-notification alert ' + alertClass + ' alert-dismissible fade show position-fixed" ' +
            'style="top: 20px; right: 20px; z-index: 9999; min-width: 350px;" role="alert">' +
                '<div class="d-flex">' +
                    '<div class="me-3">' +
                        '<i class="fas fa-' + iconClass + ' fa-2x"></i>' +
                    '</div>' +
                    '<div>' +
                        '<h5 class="alert-heading mb-1">' + title + '</h5>' +
                        '<p class="mb-0">' + message + '</p>' +
                    '</div>' +
                '</div>' +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>';
        
        \$('body').append(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            \$('.export-notification').alert('close');
        }, 5000);
    }
    
    // Function to export to Excel
    function exportToExcel() {
        const status = \$(config.selectors.status).val();
        const clinicasSeleccionadas = \$(config.selectors.clinica).val();
        const activeRange = \$('.btn-range.active').data('range');
        const specificDate = \$(config.selectors.dateInput).val();
        const range = activeRange || 'day';
        
        // Build export URL
        let exportUrl = '{$ajaxUrl}'.replace('get-pagos-detail', 'export-excel') + '?';
        exportUrl += 'range=' + encodeURIComponent(range);
        exportUrl += '&status=' + encodeURIComponent(status);
        exportUrl += '&clinicas=' + (clinicasSeleccionadas ? clinicasSeleccionadas.join(',') : 'todas');
        
        if (specificDate) {
            exportUrl += '&specific_date=' + encodeURIComponent(specificDate);
        }
        
        // Show loading state on button
        const exportBtn = \$('#btn-export-excel');
        const originalHtml = exportBtn.html();
        exportBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> Generando...');
        exportBtn.prop('disabled', true);
        
        // Create hidden iframe for download
        const iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = exportUrl;
        
        iframe.onload = function() {
            // Reset button after download starts
            setTimeout(() => {
                exportBtn.html(originalHtml);
                exportBtn.prop('disabled', false);
                
                // Show success message
                showNotification('success', 'Excel generado', 'El archivo se está descargando. Si no inicia automáticamente, revise la carpeta de descargas.');
                
                // Remove iframe after download
                setTimeout(() => {
                    document.body.removeChild(iframe);
                }, 5000);
            }, 1000);
        };
        
        document.body.appendChild(iframe);
        
        // Fallback: Direct download after 2 seconds
        setTimeout(() => {
            if (exportBtn.prop('disabled')) {
                window.open(exportUrl, '_blank');
                exportBtn.html(originalHtml);
                exportBtn.prop('disabled', false);
            }
        }, 2000);
    }
    
    // =============================================
    // FUNCIÓN PRINCIPAL - CARGAR REPORTE
    // =============================================
    async function cargarReporte(params = {}) {
        try {
            // Mostrar estado de carga
            showLoading();
            
            // Preparar datos para la solicitud
            const requestData = {
                ...params,
                status: \$(config.selectors.status).val(),
                clinicas: \$(config.selectors.clinica).val() || []
            };
            
            // Realizar solicitud AJAX
            const response = await \$.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: requestData,
                dataType: 'json'
            });
            
            if (response.success) {
                // Mostrar resultados
                \$(config.selectors.results).html(response.html);
                
                // Actualizar botón PDF
                updatePDFButton(params, requestData.status, requestData.clinicas);
                
                // Inicializar tooltips
                \$('[data-bs-toggle="tooltip"]').tooltip();
                
                // Scroll suave a resultados
                \$('html, body').animate({
                    scrollTop: \$(config.selectors.results).offset().top - 100
                }, 500);
                
            } else {
                showError(response.message || 'Error al procesar el reporte');
            }
            
        } catch (error) {
            console.error('Error en carga de reporte:', error);
            
            let errorMessage = 'Error de conexión con el servidor';
            if (error.status === 403) {
                errorMessage = 'Acceso denegado. Verifique sus permisos.';
            } else if (error.status === 500) {
                try {
                    const errorData = JSON.parse(error.responseText);
                    errorMessage = errorData.message || 'Error interno del servidor';
                } catch (e) {
                    errorMessage = 'Error del servidor (500). Consulte los logs.';
                }
            }
            
            showError(errorMessage);
        }
    }
    
    // =============================================
    // MANEJADORES DE EVENTOS
    // =============================================
    function setupEventHandlers() {
        // 1. Botones de rango predefinido
        \$('.btn-range').on('click', function() {
            const range = \$(this).data('range');
            
            // Actualizar estado visual
            \$('.btn-range').removeClass('active');
            \$(this).addClass('active');
            
            // Limpiar fecha específica
            \$(config.selectors.dateInput).val('');
            
            // Cargar reporte
            cargarReporte({ range: range });
        });
        
        // 2. Botón de fecha específica
        \$(config.selectors.specificDateBtn).on('click', function() {
            const specificDate = \$(config.selectors.dateInput).val();
            
            if (!specificDate) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Fecha requerida',
                        text: 'Por favor seleccione una fecha específica',
                        confirmButtonColor: '#007bff'
                    });
                } else {
                    alert('Por favor seleccione una fecha específica');
                }
                return;
            }
            
            // Remover estado activo de botones de rango
            \$('.btn-range').removeClass('active');
            
            // Cargar reporte
            cargarReporte({ specific_date: specificDate });
        });
        
        // 3. Cambio en selector de estado
        \$(config.selectors.status).on('change', function() {
            const activeRange = \$('.btn-range.active').data('range');
            const range = activeRange || 'day';
            cargarReporte({ range: range });
        });
        
        // 4. Cambio en selector de clínicas
        \$(config.selectors.clinica).on('change', function() {
            const activeRange = \$('.btn-range.active').data('range');
            const range = activeRange || 'day';
            cargarReporte({ range: range });
        });
        
        // 5. Botón principal de aplicar filtros
        \$(config.selectors.aplicarBtn).on('click', function() {
            const activeRange = \$('.btn-range.active').data('range');
            const specificDate = \$(config.selectors.dateInput).val();
            const range = activeRange || 'day';
            
            const params = specificDate ? { specific_date: specificDate } : { range: range };
            cargarReporte(params);
        });
        
        // 6. Enter en campo de fecha
        \$(config.selectors.dateInput).on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                \$(config.selectors.specificDateBtn).click();
            }
        });
    }
    
    // =============================================
    // INICIALIZACIÓN DE LA PÁGINA
    // =============================================
    \$(document).ready(function() {
        // Inicializar componentes
        initComponents();
        
        // Configurar manejadores de eventos
        setupEventHandlers();
        
        // Cargar reporte inicial
        cargarReporte({ range: 'day' });
    });
JS
);
?>