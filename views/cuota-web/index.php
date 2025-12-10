<?php
// views/cuota-web/index.php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap5\Alert;

/** @var yii\web\View $this */
$this->title = 'Gestión de Cuotas - Panel de Control';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="cuota-web-index">
    <div class="row">
        <div class="col-12">
            <h1 class="text-center mb-4">
                <i class="fas fa-cogs text-primary"></i> Panel de Gestión de Cuotas
            </h1>
            <p class="text-center text-muted mb-5">
                Ejecute las funciones de gestión de cuotas manualmente cuando lo necesite
            </p>
        </div>
    </div>

    <div id="global-loading" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>

    <div id="result-area" class="mb-4" style="display: none;">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-terminal"></i> Resultado de la Ejecución
                    <button type="button" class="btn btn-sm btn-outline-secondary float-end" onclick="clearResults()">
                        <i class="fas fa-times"></i> Limpiar
                    </button>
                </h5>
            </div>
            <div class="card-body">
                <div id="result-content" style="max-height: 400px; overflow-y: auto; background-color: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; white-space: pre-wrap; font-size: 0.9em;"></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-play-circle"></i> Operaciones Principales
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success btn-lg" 
                            onclick="executeAction(this)" 
                            data-action-url="<?= Url::to(['cuota-web/generar']) ?>"
                            data-action-name="generar">
                            <i class="fas fa-plus-circle"></i> Generar Todas las Cuotas
                        </button>
                        
                        <button type="button" class="btn btn-info btn-lg" 
                            onclick="executeAction(this)" 
                            data-action-url="<?= Url::to(['cuota-web/generar-mensual']) ?>"
                            data-action-name="generar-mensual">
                            <i class="fas fa-calendar-plus"></i> Generar Cuotas Mensuales
                        </button>
                        
                        <button type="button" class="btn btn-warning btn-lg" 
                            onclick="executeAction(this)" 
                            data-action-url="<?= Url::to(['cuota-web/generar-atrasadas']) ?>"
                            data-action-name="generar-atrasadas">
                            <i class="fas fa-clock"></i> Generar Cuotas Atrasadas
                        </button>
                        
                        <button type="button" class="btn btn-danger btn-lg" 
                            onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/verificar-vencidas']) ?>"
                            data-action-name="verificar-vencidas">
                            <i class="fas fa-exclamation-triangle"></i> Verificar y Suspender Vencidas
                        </button>

                        <!-- NEW: Duplicate Management Buttons -->
                        <button type="button" class="btn btn-outline-warning btn-lg" 
                            onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/verificar-duplicados']) ?>"
                            data-action-name="verificar-duplicados">
                            <i class="fas fa-search"></i> Verificar Cuotas Duplicadas
                        </button>
                        
                        <button type="button" class="btn btn-outline-danger btn-lg" 
                            onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/eliminar-duplicados']) ?>"
                            data-action-name="eliminar-duplicados">
                            <i class="fas fa-trash-alt"></i> Eliminar Cuotas Duplicadas
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> Verificaciones y Reportes
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary btn-lg" 
                            onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/verificar-diario']) ?>"
                            data-action-name="verificar-diario">
                            <i class="fas fa-calendar-day"></i> Verificación Diaria Completa
                        </button>
                        
                        <button type="button" class="btn btn-outline-success btn-lg" 
                            onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/resumen-proximos-vencer']) ?>"
                            data-action-name="resumen-proximos-vencer">
                            <i class="fas fa-hourglass-half"></i> Resumen Próximos a Vencer
                        </button>
                        
                        <button type="button" class="btn btn-outline-warning btn-lg" 
                            onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/resumen-atrasadas']) ?>"
                            data-action-name="resumen-atrasadas">
                            <i class="fas fa-list-alt"></i> Resumen Cuotas Atrasadas
                        </button>
                        
                        <button type="button" class="btn btn-outline-info btn-lg" 
                            onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/verificar-todo']) ?>"
                            data-action-name="verificar-todo">
                            <i class="fas fa-check-double"></i> Verificar Todo el Sistema
                        </button>

                        <button type="button" class="btn btn-outline-danger btn-lg" 
                            onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/verificar-contratos-vencidos']) ?>"
                            data-action-name="verificar-contratos-vencidos">
                            <i class="fas fa-file-contract"></i> Verificar Contratos Vencidos
                        </button>

                        <button type="button" class="btn btn-outline-secondary btn-lg" 
                            onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/verificar-espera']) ?>"
                            data-action-name="verificar-espera">
                            <i class="fas fa-pause-circle"></i> Verificar Contratos en Espera
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0 text-white">
                        <i class="fas fa-tools"></i> Mantenimiento
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <!-- Botones existentes -->
                        <button type="button" class="btn btn-outline-secondary btn-lg" 
                            onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/actualizar-montos']) ?>"
                            data-action-name="actualizar-montos">
                            <i class="fas fa-dollar-sign"></i> Actualizar Montos de Cuotas
                        </button>
                        
                        <!-- NUEVOS BOTONES AÑADIDOS -->
                        <button type="button" class="btn btn-outline-info btn-lg" 
                            onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/diagnostico-inconsistencias']) ?>"
                            data-action-name="diagnostico-inconsistencias">
                            <i class="fas fa-stethoscope"></i> Diagnóstico de Inconsistencias
                        </button>
                        
                        <button type="button" class="btn btn-outline-success btn-lg" 
                            onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/generar-cuotas-faltantes']) ?>"
                            data-action-name="generar-cuotas-faltantes">
                            <i class="fas fa-plus-circle"></i> Generar Cuotas Faltantes
                        </button>
                        <!-- FIN NUEVOS BOTONES -->
                        
                        <button type="button" class="btn btn-outline-info btn-lg" 
                            onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/reparar-relacion-pagos']) ?>"
                            data-action-name="reparar-relacion-pagos">
                            <i class="fas fa-link"></i> Reparar Relación Pagos-Cuotas
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-lg" 
                            onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/eliminar-incorrectas']) ?>" 
                            data-action-name="eliminar-incorrectas">
                            <i class="fas fa-trash-alt"></i> Eliminar Cuotas Incorrectas (149,150,147)
                        </button>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Use estas herramientas para mantenimiento del sistema.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt"></i> Acciones Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-dark w-100" 
                                onclick="executeAction(this)"
                                data-action-url="<?= Url::to(['cuota-web/generar']) ?>"
                                data-action-name="generar-refresh">
                                <i class="fas fa-sync"></i> Refresh
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-dark w-100" 
                                onclick="executeAction(this)"
                                data-action-url="<?= Url::to(['cuota-web/verificar-diario']) ?>"
                                data-action-name="verificar-diario-quick">
                                <i class="fas fa-check"></i> Daily Check
                            </button>
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        <small class="text-muted">
                            Última ejecución: <span id="last-execution">Nunca</span>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0 text-white">
                        <i class="fas fa-info-circle"></i> Información del Sistema
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <h4 class="text-primary" id="total-contratos">-</h4>
                                <small class="text-muted">Contratos Activos</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <h4 class="text-success" id="cuotas-pendientes">-</h4>
                                <small class="text-muted">Cuotas Pendientes</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <h4 class="text-warning" id="cuotas-vencidas">-</h4>
                                <small class="text-muted">Cuotas Vencidas</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <h4 class="text-danger" id="contratos-suspendidos">-</h4>
                                <small class="text-muted">Contratos Suspendidos</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de diagnóstico (oculto por defecto) -->
    <div class="row mt-4" id="diagnostic-panel" style="display: none;">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-stethoscope"></i> Diagnóstico del Sistema
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-outline-info btn-sm mb-2" onclick="testCommandExecution()">
                                <i class="fas fa-vial"></i> Probar Ejecución de Comandos
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm mb-2" onclick="showSystemInfo()">
                                <i class="fas fa-info-circle"></i> Mostrar Info del Sistema
                            </button>
                        </div>
                        <div class="col-md-6">
                            <div id="diagnostic-result" style="font-family: monospace; font-size: 0.8em; background: #f8f9fa; padding: 10px; border-radius: 5px; max-height: 200px; overflow-y: auto;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let isLoading = false;
const csrfToken = '<?= Yii::$app->request->csrfToken ?>';

function showGlobalLoading() {
    document.getElementById('global-loading').style.display = 'flex';
}

function hideGlobalLoading() {
    document.getElementById('global-loading').style.display = 'none';
}

function executeAction(button) {
    const action = button.getAttribute('data-action-name');
    const url = button.getAttribute('data-action-url');

    console.log('Executing action:', action);
    console.log('Final URL:', url);
    
    if (isLoading) {
        console.log('Already loading, skipping...');
        return;
    }

    if (!url) {
        document.getElementById('result-content').innerHTML = 
           '<div class="text-danger"><i class="fas fa-times-circle"></i> Error de configuración: El botón no tiene una URL válida (data-action-url).</div>';
       document.getElementById('result-area').style.display = 'block';
       return;
    }
    
    isLoading = true;
    const originalText = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ejecutando...';
    button.disabled = true;
    showGlobalLoading();
    
    // Show result area
    document.getElementById('result-area').style.display = 'block';
    document.getElementById('result-content').innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Ejecutando comando...</div>';
    
    const formData = new FormData();
    formData.append('_csrf', csrfToken);
    
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            // Error de enrutamiento (404) o servidor (500)
            throw new Error(`Error HTTP: ${response.status} ${response.statusText}. URL: ${url}`);
        }
        return response.json();
    })
    .then(response => {
        console.log('Success! Response received:', response);
        const timestamp = new Date().toLocaleString();
        document.getElementById('last-execution').textContent = timestamp;
        
        // Enhanced response handling
        let resultHtml = '';
        if (response.success) {
            resultHtml = '<div class="text-success mb-2"><i class="fas fa-check-circle"></i> ' + response.message + '</div>';
            if (response.returnCode !== undefined) {
                resultHtml += '<div class="text-muted small mb-2">Código de retorno: ' + response.returnCode + '</div>';
            }
            resultHtml += '<div class="border-top pt-2">' + (response.output || 'Sin salida') + '</div>';
        } else {
            resultHtml = '<div class="text-danger mb-2"><i class="fas fa-exclamation-circle"></i> ' + response.message + '</div>';
            if (response.returnCode !== undefined) {
                resultHtml += '<div class="text-muted small mb-2">Código de error: ' + response.returnCode + '</div>';
            }
            resultHtml += '<div class="border-top pt-2 text-danger">' + (response.output || 'Sin detalles del error') + '</div>';
        }
        
        document.getElementById('result-content').innerHTML = resultHtml;
        
        // Scroll to results
        document.getElementById('result-area').scrollIntoView({ behavior: 'smooth', block: 'start' });
        
        // Update system info after successful execution
        if (response.success) {
            setTimeout(updateSystemInfo, 1000);
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        
        // Enhanced error display
        document.getElementById('result-content').innerHTML = 
            '<div class="text-danger">' +
            '<i class="fas fa-times-circle"></i> Error de Red/Ruta: No se pudo conectar.<br>' +
            '<strong>Mensaje:</strong> ' + error.message + '<br>' +
            '<strong>URL:</strong> ' + url + '<br>' +
            '<small class="text-muted">Verifique que el controlador exista y tenga permisos.</small>' +
            '</div>';
    })
    .finally(() => {
        isLoading = false;
        hideGlobalLoading();
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function clearResults() {
    document.getElementById('result-area').style.display = 'none';
    document.getElementById('result-content').innerHTML = '';
}

function updateSystemInfo() {
    console.log('System info would be updated here');
    // Esta función puede ser implementada para actualizar las estadísticas del sistema
    // después de ejecutar comandos importantes
}

function testCommandExecution() {
    const diagnosticResult = document.getElementById('diagnostic-result');
    diagnosticResult.innerHTML = '<div class="text-info"><i class="fas fa-spinner fa-spin"></i> Probando ejecución de comandos...</div>';
    
    fetch('<?= Url::to(['cuota-web/test-command']) ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Test Command Result:', data);
        if (data.success) {
            diagnosticResult.innerHTML = 
                '<div class="text-success"><i class="fas fa-check-circle"></i> Comandos funcionando correctamente</div>' +
                '<div class="mt-2"><strong>Salida:</strong><br>' + data.output + '</div>';
        } else {
            diagnosticResult.innerHTML = 
                '<div class="text-danger"><i class="fas fa-exclamation-circle"></i> Error en ejecución de comandos</div>' +
                '<div class="mt-2"><strong>Error:</strong><br>' + data.output + '</div>' +
                '<div class="mt-1"><strong>Código:</strong> ' + data.returnCode + '</div>';
        }
    })
    .catch(error => {
        console.error('Test command error:', error);
        diagnosticResult.innerHTML = 
            '<div class="text-danger"><i class="fas fa-times-circle"></i> Error en prueba de diagnóstico</div>' +
            '<div class="mt-2"><strong>Error:</strong><br>' + error.message + '</div>';
    });
}

function showSystemInfo() {
    const diagnosticResult = document.getElementById('diagnostic-result');
    diagnosticResult.innerHTML = 
        '<div class="text-info">Información del sistema:</div>' +
        '<div><strong>User Agent:</strong> ' + navigator.userAgent + '</div>' +
        '<div><strong>Online:</strong> ' + navigator.onLine + '</div>' +
        '<div><strong>Timestamp:</strong> ' + new Date().toISOString() + '</div>';
}

function toggleDiagnosticPanel() {
    const panel = document.getElementById('diagnostic-panel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('Cuota Web Panel loaded successfully');
    
    // Mostrar panel de diagnóstico en desarrollo
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        document.getElementById('diagnostic-panel').style.display = 'block';
    }
    
    // Agregar botón de diagnóstico al header
    const header = document.querySelector('.card-header:first-child');
    if (header) {
        const diagnosticBtn = document.createElement('button');
        diagnosticBtn.type = 'button';
        diagnosticBtn.className = 'btn btn-sm btn-outline-warning float-end';
        diagnosticBtn.innerHTML = '<i class="fas fa-stethoscope"></i>';
        diagnosticBtn.title = 'Mostrar/Ocultar Diagnóstico';
        diagnosticBtn.onclick = toggleDiagnosticPanel;
        header.querySelector('.card-title').appendChild(diagnosticBtn);
    }
    
    updateSystemInfo();
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+Shift+D para diagnóstico
    if (e.ctrlKey && e.shiftKey && e.key === 'D') {
        e.preventDefault();
        toggleDiagnosticPanel();
    }
    // Escape para limpiar resultados
    if (e.key === 'Escape') {
        clearResults();
    }
});
</script>