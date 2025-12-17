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

        <!-- NEW: Cuotas Adelantadas Card -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-forward"></i> Cuotas Adelantadas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success btn-lg"
                            onclick="showAdelantadasModal()"
                            data-action-name="generar-adelantadas">
                            <i class="fas fa-calendar-plus"></i> Generar Cuotas Adelantadas
                        </button>

                        <small class="text-muted mt-2">
                            <i class="fas fa-info-circle"></i> Genere cuotas futuras para afiliados que pagan por adelantado.
                        </small>
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
                    <div class="card-header bg-secondary text-white">
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
                        <h6 class="card-title mb-0 text-dark">
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

        <!-- Modal for Advance Cuotas Generation - Bootstrap 4 Version -->
        <div class="modal fade" id="adelantadasModal" tabindex="-1" role="dialog" aria-labelledby="adelantadasModalLabel" aria-hidden="true" style="padding-top: 100px;">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="adelantadasModalLabel">
                            <i class="fas fa-forward"></i> Generar Cuotas Adelantadas
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" class="text-white">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="adelantadas-step-1">
                            <!-- Step 1: Search/Select User -->
                            <div class="form-group">
                                <label class="form-label">Buscar Afiliado</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="user-search"
                                        placeholder="ID, Nombre, Apellido o Cédula">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-primary" type="button" onclick="searchUser()">
                                            <i class="fas fa-search"></i> Buscar
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Ingrese al menos 3 caracteres para buscar</small>
                            </div>

                            <div id="user-results" class="mb-3" style="display: none;">
                                <h6>Resultados:</h6>
                                <div id="users-list" class="list-group" style="max-height: 200px; overflow-y: auto;"></div>
                            </div>

                            <div id="selected-user" class="mb-3" style="display: none;">
                                <div class="card">
                                    <div class="card-body">
                                        <h6>Afiliado Seleccionado:</h6>
                                        <p id="selected-user-info" class="mb-0"></p>
                                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="deselectUser()">
                                            <i class="fas fa-times"></i> Cambiar Afiliado
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div id="contracts-section" class="mb-3" style="display: none;">
                                <label class="form-label">Seleccionar Contrato</label>
                                <select class="form-control" id="contrato-select" onchange="loadContractInfo()">
                                    <option value="">Seleccione un contrato...</option>
                                </select>
                            </div>

                            <div id="contract-info" class="mb-3" style="display: none;">
                                <div class="card">
                                    <div class="card-body">
                                        <h6>Información del Contrato:</h6>
                                        <div id="contract-details"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="adelantadas-step-2" style="display: none;">
                            <!-- Step 2: Configure Generation -->
                            <div class="form-group">
                                <label class="form-label">Modo de Generación</label>
                                <div class="custom-control custom-radio">
                                    <input class="custom-control-input" type="radio" name="generation-mode" id="mode-cantidad" value="cantidad" checked onchange="toggleGenerationMode()">
                                    <label class="custom-control-label" for="mode-cantidad">
                                        Por cantidad de cuotas
                                    </label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input class="custom-control-input" type="radio" name="generation-mode" id="mode-meses" value="meses" onchange="toggleGenerationMode()">
                                    <label class="custom-control-label" for="mode-meses">
                                        Por meses específicos
                                    </label>
                                </div>
                            </div>

                            <div id="mode-cantidad-fields">
                                <div class="form-group">
                                    <label class="form-label">Número de Cuotas</label>
                                    <input type="number" class="form-control" id="num-cuotas" min="1" max="24" value="3">
                                    <small class="form-text text-muted">Máximo 24 cuotas (2 años)</small>
                                </div>
                            </div>

                            <div id="mode-meses-fields" style="display: none;">
                                <div class="form-group">
                                    <label class="form-label">Seleccionar Meses</label>
                                    <div id="months-container" style="max-height: 300px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 5px; padding: 10px; background: #f8f9fa;">
                                        <!-- Months will be populated by JavaScript -->
                                        <div class="text-center py-3 text-muted">
                                            <i class="fas fa-spinner fa-spin"></i> Cargando meses...
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Seleccione los meses específicos para generar cuotas</small>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Fecha de Inicio (Opcional)</label>
                                <input type="date" class="form-control" id="fecha-inicio">
                                <small class="form-text text-muted">Si se deja vacío, se usará la última cuota o fecha del contrato</small>
                            </div>

                            <button type="button" class="btn btn-outline-primary" onclick="previewCuotas()">
                                <i class="fas fa-eye"></i> Previsualizar Cuotas
                            </button>
                        </div>

                        <div id="adelantadas-step-3" style="display: none;">
                            <!-- Step 3: Preview and Confirm -->
                            <div id="preview-results" class="mb-3">
                                <h6>Previsualización de Cuotas:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Mes</th>
                                                <th>Fecha Vencimiento</th>
                                                <th>Monto (USD)</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody id="preview-table-body">
                                            <!-- Preview rows will be inserted here -->
                                        </tbody>
                                    </table>
                                </div>
                                <div class="alert alert-info">
                                    <strong>Total:</strong> <span id="preview-total">0.00</span> USD
                                </div>
                            </div>

                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Advertencia:</strong> Esta acción generará cuotas futuras. Asegúrese de que el afiliado haya realizado el pago por adelantado.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-outline-primary" id="prev-step-btn" onclick="prevStep()" style="display: none;">
                            <i class="fas fa-arrow-left"></i> Anterior
                        </button>
                        <button type="button" class="btn btn-primary" id="next-step-btn" onclick="nextStep()">
                            Siguiente <i class="fas fa-arrow-right"></i>
                        </button>
                        <button type="button" class="btn btn-success" id="generate-btn" onclick="generateAdelantadas()" style="display: none;">
                            <i class="fas fa-check"></i> Generar Cuotas
                        </button>
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
        // Debug: Check if modal exists on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('=== DEBUG: Checking modal existence ===');
            const modal = document.getElementById('adelantadasModal');
            console.log('Modal element:', modal);
            console.log('Modal exists:', !!modal);
            console.log('Modal HTML:', modal ? modal.outerHTML.substring(0, 200) + '...' : 'NOT FOUND');
            console.log('=== END DEBUG ===');
        });
        let isLoading = false;
        const csrfToken = '<?= Yii::$app->request->csrfToken ?>';

        // Variables for adelantadas modal
        let currentStep = 1;
        let selectedUserId = null;
        let selectedContractId = null;
        let previewData = null;

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
                    document.getElementById('result-area').scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });

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

        function showAdelantadasModal() {
            console.log('showAdelantadasModal - Simple approach');

            // Check if modal exists
            let modal = document.getElementById('adelantadasModal');

            if (!modal) {
                console.error('Modal not found in DOM! Creating it...');

                // Create modal from scratch
                modal = document.createElement('div');
                modal.id = 'adelantadasModal';
                modal.className = 'modal fade';
                modal.setAttribute('tabindex', '-1');
                modal.setAttribute('role', 'dialog');

                // Add basic modal structure
                modal.innerHTML = `
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-forward"></i> Generar Cuotas Adelantadas
                        </h5>
                        <button type="button" class="close text-white" onclick="hideAdelantadasModal()">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- We'll populate this dynamically -->
                        <div id="modal-content-placeholder">
                            <p>Loading modal content...</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="hideAdelantadasModal()">Cancelar</button>
                        <button type="button" class="btn btn-primary" onclick="nextStep()">Siguiente</button>
                    </div>
                </div>
            </div>
        `;

                document.body.appendChild(modal);
                console.log('Modal created and added to body');
            }

            // Show the modal
            showModalSimple(modal);
        }

        function showModalSimple(modalElement) {
            console.log('showModalSimple called');

            // Method 1: Use Bootstrap if available
            if (typeof $ !== 'undefined' && $.fn.modal) {
                console.log('Using Bootstrap modal');

                // Ensure modal is on top
                $(modalElement).css('z-index', '999999');

                // Show modal
                $(modalElement).modal('show');

                // Force modal dialog to be on top
                setTimeout(function() {
                    $(modalElement).find('.modal-dialog').css('z-index', '1000000');
                    $(modalElement).find('.modal-content').css('z-index', '1000001');

                    // Ensure backdrop is below
                    $('.modal-backdrop').css('z-index', '999998');
                }, 10);
            }
            // Method 2: Manual show (if needed)
            else {
                console.log('Using manual modal show');

                // Show modal
                modalElement.style.display = 'block';
                modalElement.classList.add('show');
                modalElement.style.zIndex = '999999';

                // Style the dialog
                const dialog = modalElement.querySelector('.modal-dialog');
                if (dialog) {
                    dialog.style.zIndex = '1000000';
                    dialog.style.position = 'relative';
                }

                // Style the content
                const content = modalElement.querySelector('.modal-content');
                if (content) {
                    content.style.zIndex = '1000001';
                    content.style.position = 'relative';
                }

                // Add backdrop (BELOW the modal)
                let backdrop = document.querySelector('.modal-backdrop');
                if (!backdrop) {
                    backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    backdrop.style.zIndex = '999998'; // IMPORTANT: Lower than modal
                    document.body.appendChild(backdrop);
                }

                // Add modal-open class
                document.body.classList.add('modal-open');

                // Add click handler to close when clicking backdrop
                backdrop.onclick = function() {
                    hideAdelantadasModal();
                };
            }

            // Load modal content
            loadModalContent();
        }

        function showAdelantadasModal() {
            console.log('showAdelantadasModal - Final working version');

            // Get the modal
            const modal = $('#adelantadasModal');

            if (modal.length === 0) {
                console.error('Modal not found!');
                alert('Error: Modal no encontrado. Recargue la página.');
                return;
            }

            // Ensure no blur/opacity filters are applied
            $('body').css({
                'filter': 'none',
                'opacity': '1',
                'backdrop-filter': 'none'
            });

            // Remove any existing conflicting modals
            $('.modal').not('#adelantadasModal').modal('hide');

            // Show the modal with Bootstrap
            modal.modal({
                backdrop: 'static',
                keyboard: false,
                show: true
            });

            // Reset modal content
            resetModal();

            console.log('Modal should be fully visible now');
        }

        function hideAdelantadasModal() {
            $('#adelantadasModal').modal('hide');
        }

        // Update resetModal to work with the visible modal
        function resetModal() {
            console.log('resetModal - Initializing modal content');

            currentStep = 1;
            selectedUserId = null;
            selectedContractId = null;
            previewData = null;

            // Show step 1, hide others
            $('#adelantadas-step-1').show();
            $('#adelantadas-step-2').hide();
            $('#adelantadas-step-3').hide();

            // Reset buttons
            $('#prev-step-btn').hide();
            $('#next-step-btn').show();
            $('#generate-btn').hide();

            // Clear fields
            $('#user-search').val('');
            $('#users-list').empty();
            $('#user-results').hide();
            $('#selected-user').hide();
            $('#contracts-section').hide();
            $('#contract-info').hide();

            $('#contrato-select').html('<option value="">Seleccione un contrato...</option>');
            $('#preview-table-body').empty();
            $('#preview-total').text('0.00');

            console.log('Modal content reset complete');
        }

        function loadModalContent() {
            console.log('Loading modal content...');

            // Get or create content container
            let contentContainer = document.querySelector('#adelantadasModal .modal-body');
            if (!contentContainer) {
                console.error('Modal body not found!');
                return;
            }

            // Add a HIGHLY VISIBLE test content
            contentContainer.innerHTML = `
        <div style="background: yellow; padding: 20px; border: 5px solid red;">
            <h1 style="color: red; font-size: 24px;">🎯 MODAL IS WORKING! 🎯</h1>
            <p style="font-size: 18px; font-weight: bold;">If you can see this YELLOW box with RED text, the modal is visible!</p>
            <p>Time: ${new Date().toLocaleTimeString()}</p>
            
            <div id="adelantadas-step-1">
                <h3>Step 1: Test Content</h3>
                <div class="form-group">
                    <label>Test Field</label>
                    <input type="text" class="form-control" value="Test value" style="border: 2px solid blue;">
                </div>
            </div>
        </div>
    `;

            console.log('Test content loaded - should be visible');

            // Remove blur effect from body if present
            document.body.style.filter = 'none';
            document.body.style.opacity = '1';
        }

        function resetModal() {
            currentStep = 1;
            selectedUserId = null;
            selectedContractId = null;
            previewData = null;

            // Reset UI
            document.getElementById('adelantadas-step-1').style.display = 'block';
            document.getElementById('adelantadas-step-2').style.display = 'none';
            document.getElementById('adelantadas-step-3').style.display = 'none';
            document.getElementById('prev-step-btn').style.display = 'none';
            document.getElementById('next-step-btn').style.display = 'block';
            document.getElementById('generate-btn').style.display = 'none';

            // Clear fields
            document.getElementById('user-search').value = '';
            document.getElementById('users-list').innerHTML = '';
            document.getElementById('user-results').style.display = 'none';
            document.getElementById('selected-user').style.display = 'none';
            document.getElementById('contracts-section').style.display = 'none';
            document.getElementById('contract-info').style.display = 'none';
            document.getElementById('contrato-select').innerHTML = '<option value="">Seleccione un contrato...</option>';
            document.getElementById('preview-table-body').innerHTML = '';
            document.getElementById('preview-total').textContent = '0.00';
        }

        function searchUser() {
            const searchTerm = document.getElementById('user-search').value.trim();

            console.log('Search term:', searchTerm);
            console.log('Search term type:', typeof searchTerm);

            if (searchTerm.length < 1) { // Changed from 3 to 1 for testing
                alert('Ingrese un término para buscar');
                return;
            }

            showGlobalLoading();

            const formData = new FormData();
            formData.append('_csrf', csrfToken);
            formData.append('search', searchTerm);

            console.log('Sending search request...');

            fetch('<?= Url::to(['cuota-web/search-user']) ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response OK:', response.ok);
                    return response.json();
                })
                .then(data => {
                    console.log('Search response:', data);
                    hideGlobalLoading();

                    if (data.success) {
                        const usersList = document.getElementById('users-list');
                        usersList.innerHTML = '';

                        console.log('Users found:', data.users.length);

                        if (data.users.length === 0) {
                            usersList.innerHTML = '<div class="list-group-item text-muted">No se encontraron afiliados</div>';
                        } else {
                            data.users.forEach(user => {
                                console.log('User:', user);
                                const item = document.createElement('button');
                                item.type = 'button';
                                item.className = 'list-group-item list-group-item-action';
                                item.innerHTML = `
                        <strong>${user.nombres || ''} ${user.apellidos || ''}</strong><br>
                        <small class="text-muted">ID: ${user.id} | Cédula: ${user.cedula || 'N/A'} | Email: ${user.email || 'N/A'}</small><br>
                        <small>Contratos activos: ${user.active_contracts || 0}</small>
                    `;
                                item.onclick = () => selectUser(user);
                                usersList.appendChild(item);
                            });
                        }

                        document.getElementById('user-results').style.display = 'block';
                    } else {
                        alert('Error buscando usuarios: ' + data.error);
                        console.error('Search error:', data.error);
                    }
                })
                .catch(error => {
                    hideGlobalLoading();
                    console.error('Search fetch error:', error);
                    alert('Error en la búsqueda: ' + error.message);
                });
        }

        function selectUser(user) {
            selectedUserId = user.id;

            document.getElementById('selected-user-info').innerHTML = `
        <strong>${user.nombres} ${user.apellidos}</strong><br>
        <small>Cédula: ${user.cedula || 'N/A'} | ID: ${user.id}</small>
    `;

            document.getElementById('user-results').style.display = 'none';
            document.getElementById('selected-user').style.display = 'block';

            // Load contracts for this user
            loadUserContracts();
        }

        function deselectUser() {
            selectedUserId = null;
            selectedContractId = null;
            document.getElementById('selected-user').style.display = 'none';
            document.getElementById('contracts-section').style.display = 'none';
            document.getElementById('contract-info').style.display = 'none';
            document.getElementById('user-results').style.display = 'block';
        }

        function loadUserContracts() {
            if (!selectedUserId) return;

            showGlobalLoading();

            const formData = new FormData();
            formData.append('_csrf', csrfToken);
            formData.append('user_id', selectedUserId);

            fetch('<?= Url::to(['cuota-web/get-user-contracts']) ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    hideGlobalLoading();

                    if (data.success) {
                        const select = document.getElementById('contrato-select');
                        select.innerHTML = '<option value="">Seleccione un contrato...</option>';

                        if (data.contratos.length === 0) {
                            select.innerHTML += '<option value="" disabled>No hay contratos activos</option>';
                        } else {
                            data.contratos.forEach(contrato => {
                                const lastCuotaInfo = contrato.last_cuota ?
                                    `(Última cuota: ${contrato.last_cuota.fecha} - ${contrato.last_cuota.estatus})` :
                                    '(Sin cuotas)';

                                const option = document.createElement('option');
                                option.value = contrato.id;
                                option.textContent = `Contrato ${contrato.nrocontrato} - ${contrato.estatus} ${lastCuotaInfo}`;
                                option.setAttribute('data-monto', contrato.monto);
                                select.appendChild(option);
                            });
                        }

                        document.getElementById('contracts-section').style.display = 'block';
                    } else {
                        alert('Error cargando contratos: ' + data.error);
                    }
                })
                .catch(error => {
                    hideGlobalLoading();
                    console.error('Load contracts error:', error);
                    alert('Error cargando contratos');
                });
        }

        function loadContractInfo() {
            const select = document.getElementById('contrato-select');
            selectedContractId = select.value;

            if (!selectedContractId) {
                document.getElementById('contract-info').style.display = 'none';
                return;
            }

            const selectedOption = select.options[select.selectedIndex];
            const monto = selectedOption.getAttribute('data-monto') || '0.00';

            document.getElementById('contract-details').innerHTML = `
        <strong>Contrato seleccionado:</strong> ${selectedOption.textContent}<br>
        <strong>Monto de cuota:</strong> ${parseFloat(monto).toFixed(2)} USD
    `;

            document.getElementById('contract-info').style.display = 'block';
        }

        function nextStep() {
            if (currentStep === 1) {
                // Validate step 1
                if (!selectedUserId || !selectedContractId) {
                    alert('Debe seleccionar un afiliado y un contrato');
                    return;
                }

                // Show step 2
                document.getElementById('adelantadas-step-1').style.display = 'none';
                document.getElementById('adelantadas-step-2').style.display = 'block';
                document.getElementById('prev-step-btn').style.display = 'inline-block';
                currentStep = 2;

            } else if (currentStep === 2) {
                // Validate step 2
                const modeCantidad = document.getElementById('mode-cantidad').checked;
                let valid = false;

                if (modeCantidad) {
                    const numCuotas = document.getElementById('num-cuotas').value;
                    valid = numCuotas && numCuotas > 0 && numCuotas <= 24;
                } else {
                    // Check if at least one month is selected
                    const selectedMonths = document.querySelectorAll('.month-checkbox:checked');
                    valid = selectedMonths.length > 0;
                }

                if (!valid) {
                    alert('Complete correctamente la configuración de cuotas');
                    return;
                }

                // Show step 3 (preview will be loaded separately)
                document.getElementById('adelantadas-step-2').style.display = 'none';
                document.getElementById('adelantadas-step-3').style.display = 'block';
                document.getElementById('next-step-btn').style.display = 'none';
                document.getElementById('generate-btn').style.display = 'inline-block';
                currentStep = 3;

                // Load preview
                previewCuotas();
            }
        }

        function prevStep() {
            if (currentStep === 2) {
                // Go back to step 1
                document.getElementById('adelantadas-step-2').style.display = 'none';
                document.getElementById('adelantadas-step-1').style.display = 'block';
                document.getElementById('prev-step-btn').style.display = 'none';
                currentStep = 1;
            } else if (currentStep === 3) {
                // Go back to step 2
                document.getElementById('adelantadas-step-3').style.display = 'none';
                document.getElementById('adelantadas-step-2').style.display = 'block';
                document.getElementById('next-step-btn').style.display = 'inline-block';
                document.getElementById('generate-btn').style.display = 'none';
                currentStep = 2;
            }
        }

        function toggleGenerationMode() {
            const modeCantidad = document.getElementById('mode-cantidad').checked;

            if (modeCantidad) {
                document.getElementById('mode-cantidad-fields').style.display = 'block';
                document.getElementById('mode-meses-fields').style.display = 'none';
            } else {
                document.getElementById('mode-cantidad-fields').style.display = 'none';
                document.getElementById('mode-meses-fields').style.display = 'block';

                // Populate months immediately when switching to this mode
                populateMonths();

                // Force a reflow to ensure proper rendering
                setTimeout(() => {
                    const container = document.getElementById('months-container');
                    if (container) {
                        container.style.display = 'block';
                    }
                }, 10);
            }
        }

        function populateMonths() {
            const container = document.getElementById('months-container');
            container.innerHTML = '';

            const today = new Date();
            // We start from month 0 (this month) or month 1 (next month) 
            // depending on your business logic. Here we start with NEXT month.

            let html = '';
            for (let i = 1; i <= 24; i++) {
                const date = new Date(today.getFullYear(), today.getMonth() + i, 1);
                const monthName = date.toLocaleString('es-ES', {
                    month: 'long',
                    year: 'numeric'
                });
                const monthValue = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}`;

                // Create a flat structure. No .row or .col classes!
                html += `
            <div class="month-selector-item">
                <input type="checkbox" 
                       class="month-checkbox" 
                       id="month-${i}" 
                       value="${monthValue}">
                <label for="month-${i}">
                    ${monthName.charAt(0).toUpperCase() + monthName.slice(1)}
                </label>
            </div>`;
            }

            container.innerHTML = html;
        }

        function previewCuotas() {
            if (!selectedContractId) {
                alert('No hay contrato seleccionado');
                return;
            }

            const modeCantidad = document.getElementById('mode-cantidad').checked;
            let numCuotas = 0;
            let meses = '';

            if (modeCantidad) {
                // MODE: Por cantidad de cuotas
                numCuotas = parseInt(document.getElementById('num-cuotas').value);
                if (isNaN(numCuotas) || numCuotas <= 0 || numCuotas > 24) {
                    alert('Ingrese un número válido de cuotas (1-24)');
                    return;
                }
            } else {
                // MODE: Por meses específicos
                const selectedMonths = Array.from(document.querySelectorAll('.month-checkbox:checked'))
                    .map(cb => cb.value);
                numCuotas = selectedMonths.length;
                meses = selectedMonths.join(',');

                if (numCuotas === 0) {
                    alert('Seleccione al menos un mes');
                    return;
                }
            }

            showGlobalLoading();

            const formData = new FormData();
            formData.append('_csrf', csrfToken);
            formData.append('contrato_id', selectedContractId);
            formData.append('num_cuotas', numCuotas);

            if (meses) {
                formData.append('meses', meses);
            }

            const fechaInicio = document.getElementById('fecha-inicio').value;
            if (fechaInicio) {
                formData.append('fecha_inicio', fechaInicio);
            }

            fetch('<?= Url::to(['cuota-web/preview-adelantadas']) ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    hideGlobalLoading();

                    if (data.success) {
                        previewData = data;
                        displayPreview(data);
                    } else {
                        alert('Error en previsualización: ' + (data.error || 'Desconocido'));
                    }
                })
                .catch(error => {
                    hideGlobalLoading();
                    console.error('Preview error:', error);
                    alert('Error en previsualización: ' + error.message);
                });
        }

        function displayPreview(data) {
            const tbody = document.getElementById('preview-table-body');
            tbody.innerHTML = '';

            let total = 0;

            data.preview.forEach((cuota, index) => {
                const row = document.createElement('tr');
                row.className = cuota.existe ? 'table-warning' : '';

                row.innerHTML = `
            <td>${cuota.numero}</td>
            <td>${cuota.mes}</td>
            <td>${cuota.fecha_vencimiento}</td>
            <td>${cuota.monto.toFixed(2)}</td>
            <td>
                ${cuota.existe ? 
                    '<span class="badge bg-warning">Ya existe</span>' : 
                    '<span class="badge bg-success">Nueva</span>'}
            </td>
        `;

                tbody.appendChild(row);
                total += cuota.monto;
            });

            document.getElementById('preview-total').textContent = total.toFixed(2);

            // Show warning if some cuotas already exist
            const existingCount = data.preview.filter(c => c.existe).length;
            if (existingCount > 0) {
                const warning = document.createElement('div');
                warning.className = 'alert alert-warning mt-3';
                warning.innerHTML = `
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Nota:</strong> ${existingCount} cuota(s) ya existen y no serán regeneradas.
        `;
                tbody.parentElement.parentElement.parentElement.appendChild(warning);
            }
        }

        function generateAdelantadas() {
            if (!previewData || !selectedContractId) {
                alert('No hay datos para generar');
                return;
            }

            if (!confirm('¿Está seguro de generar estas cuotas adelantadas?\n\nAsegúrese de que el afiliado haya realizado el pago correspondiente.')) {
                return;
            }

            showGlobalLoading();

            const modeCantidad = document.getElementById('mode-cantidad').checked;
            const formData = new FormData();
            formData.append('_csrf', csrfToken);
            formData.append('user_id', selectedUserId);
            formData.append('contrato_id', selectedContractId);
            formData.append('num_cuotas', document.getElementById('num-cuotas').value);
            formData.append('fecha_inicio', document.getElementById('fecha-inicio').value);

            if (!modeCantidad) {
                const selectedMonths = Array.from(document.querySelectorAll('.month-checkbox:checked'))
                    .map(cb => cb.value);
                formData.append('modo', 'meses');
                formData.append('meses', selectedMonths.join(','));
            }

            fetch('<?= Url::to(['cuota-web/generar-adelantadas']) ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    hideGlobalLoading();

                    if (data.success) {
                        // Show result in main results area
                        document.getElementById('result-content').innerHTML =
                            '<div class="text-success mb-2"><i class="fas fa-check-circle"></i> ' + data.message + '</div>' +
                            '<div class="border-top pt-2">' + data.output + '</div>';
                        document.getElementById('result-area').style.display = 'block';

                        // Close modal - Bootstrap 4 syntax
                        $('#adelantadasModal').modal('hide');

                        // Reset modal
                        resetModal();
                    } else {
                        alert('Error generando cuotas: ' + data.output);
                    }
                })
                .catch(error => {
                    hideGlobalLoading();
                    console.error('Generation error:', error);
                    alert('Error generando cuotas');
                });
        }

        // Existing functions for diagnostics
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

            // Initialize the modal
            $('#adelantadasModal').modal({
                backdrop: 'static',
                keyboard: false,
                show: false // Don't show on init
            });

            // Mostrar panel de diagnóstico en desarrollo
            if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                document.getElementById('diagnostic-panel').style.display = 'block';
            }

            // Agregar botón de diagnóstico al header
            const header = document.querySelector('.card-header:first-child');
            if (header) {
                const diagnosticBtn = document.createElement('button');
                diagnosticBtn.type = 'button';
                diagnosticBtn.className = 'btn btn-sm btn-outline-warning float-right';
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
    <style>
        /* FINAL WORKING MODAL CSS */
        #adelantadasModal {
            z-index: 1050 !important;
            display: none !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
            outline: 0 !important;
        }

        #adelantadasModal.show {
            display: block !important;
        }

        #adelantadasModal .modal-dialog {
            position: relative !important;
            width: auto !important;
            margin: 30px auto !important;
            max-width: 900px !important;
            z-index: 1051 !important;
            pointer-events: none !important;
        }

        #adelantadasModal .modal-content {
            position: relative !important;
            display: flex !important;
            flex-direction: column !important;
            width: 100% !important;
            pointer-events: auto !important;
            background-color: #fff !important;
            background-clip: padding-box !important;
            border: 1px solid rgba(0, 0, 0, .2) !important;
            border-radius: .3rem !important;
            outline: 0 !important;
            z-index: 1052 !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, .5) !important;
        }

        .modal-backdrop {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            background-color: #000 !important;
            z-index: 1049 !important;
            /* BELOW the modal */
        }

        .modal-backdrop.show {
            opacity: 0.5 !important;
        }

        /* Fix body when modal is open */
        body.modal-open {
            overflow: hidden !important;
            padding-right: 0 !important;
        }

        /* REMOVE ALL blur/opacity filters */
        * {
            filter: none !important;
            backdrop-filter: none !important;
            opacity: 1 !important;
        }

        body.modal-open>*:not(#adelantadasModal):not(.modal-backdrop) {
            filter: none !important;
            opacity: 1 !important;
        }

        /* Better spacing for month checkboxes */
        #months-container .month-item {
            background-color: #f8f9fa;
            border-radius: 5px;
            margin: 5px 0;
        }

        #months-container .form-check {
            min-height: 30px;
            display: flex;
            align-items: center;
        }

        #months-container .form-check-input {
            margin-right: 15px !important;
            flex-shrink: 0;
        }

        #months-container .form-check-label {
            flex-grow: 1;
        }

        /* NUCLEAR CSS SOLUTION FOR MONTH CHECKBOXES */
        #months-container {
            max-height: 400px;
            overflow-y: auto;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            background: white;
        }

        #months-container .month-option-container {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 12px 15px !important;
            margin: 8px 0 !important;
            border-left: 5px solid #007bff !important;
            transition: all 0.2s;
            min-height: 50px;
        }

        #months-container .month-option-container:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }

        #months-container .form-check {
            display: flex !important;
            align-items: center !important;
            margin: 0 !important;
            padding: 0 !important;
            min-height: auto !important;
        }

        #months-container .form-check-input {
            width: 22px !important;
            height: 22px !important;
            min-width: 22px !important;
            margin-right: 20px !important;
            margin-top: 0 !important;
            margin-left: 0 !important;
            flex-shrink: 0;
        }

        #months-container .form-check-label {
            font-size: 16px !important;
            font-weight: 500 !important;
            color: #333 !important;
            white-space: nowrap !important;
            overflow: visible !important;
            text-overflow: clip !important;
            padding-left: 0 !important;
            margin-left: 0 !important;
            flex-grow: 1;
            display: block !important;
        }

        /* Ensure no text truncation */
        #months-container * {
            white-space: nowrap !important;
            overflow: visible !important;
            text-overflow: clip !important;
        }

        /* Force full visibility */
        #months-container label {
            max-width: none !important;
            width: auto !important;
            display: inline-block !important;
        }

        /* Container should not cut off content */
        #mode-meses-fields {
            overflow: visible !important;
        }

        #months-container .row {
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        #months-container .col-12 {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        /* EMERGENCY OVERRIDE - Force everything visible */
        #adelantadasModal * {
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: clip !important;
            max-width: none !important;
            min-width: auto !important;
        }

        #months-container,
        #months-container *,
        #months-container label,
        #months-container span {
            white-space: nowrap !important;
            overflow: visible !important;
            text-overflow: clip !important;
            display: inline-block !important;
            width: auto !important;
            max-width: none !important;
        }

        /* Remove any text truncation */
        .text-truncate,
        .truncate,
        .ellipsis {
            white-space: nowrap !important;
            overflow: visible !important;
            text-overflow: clip !important;
        }

        /* EMERGENCY OVERRIDE - Force everything visible */
        #adelantadasModal * {
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: clip !important;
            max-width: none !important;
            min-width: auto !important;
        }

        #months-container,
        #months-container *,
        #months-container label,
        #months-container span {
            white-space: nowrap !important;
            overflow: visible !important;
            text-overflow: clip !important;
            display: inline-block !important;
            width: auto !important;
            max-width: none !important;
        }

        /* Remove any text truncation */
        .text-truncate,
        .truncate,
        .ellipsis {
            white-space: nowrap !important;
            overflow: visible !important;
            text-overflow: clip !important;
        }

        /* MONTHS SELECTION - MINIMAL FIXES */
        #months-container .list-group-item {
            border-top: 1px solid #dee2e6;
            border-bottom: 1px solid #dee2e6;
        }

        #months-container .list-group-item:first-child {
            border-top-left-radius: 0.375rem;
            border-top-right-radius: 0.375rem;
        }

        #months-container .list-group-item:last-child {
            border-bottom-left-radius: 0.375rem;
            border-bottom-right-radius: 0.375rem;
        }

        /* Remove any inherited weirdness */
        #months-container * {
            box-sizing: border-box;
        }

        /* Ensure checkboxes and labels are properly aligned */
        #months-container .form-check-input {
            margin-top: 0;
        }

        /* FINAL WORKING MODAL CSS */
        #adelantadasModal {
            z-index: 1050 !important;
        }

        .modal-backdrop {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            background-color: #000 !important;
            z-index: 1049 !important;
        }

        .modal-backdrop.show {
            opacity: 0.5 !important;
        }

        /* Fix body when modal is open */
        body.modal-open {
            overflow: hidden !important;
            padding-right: 0 !important;
        }

        /* Months selection styling */
        #months-container {
            background: white;
            border-radius: 5px;
            padding: 15px !important;
        }

        #months-container .form-check {
            display: flex;
            align-items: center;
            padding: 8px 5px;
            margin: 0;
            border-bottom: 1px solid #f0f0f0;
        }

        #months-container .form-check:last-child {
            border-bottom: none;
        }

        #months-container .form-check-input {
            width: 20px;
            height: 20px;
            min-width: 20px;
            margin-right: 12px;
            margin-top: 0;
        }

        #months-container .form-check-label {
            font-size: 15px;
            color: #333;
            white-space: normal !important;
            word-wrap: break-word;
            flex: 1;
        }

        /* Ensure no horizontal overflow */
        #mode-meses-fields {
            overflow: visible !important;
        }

        #months-container::-webkit-scrollbar {
            width: 6px;
        }

        #months-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        #months-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        #months-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* 1. Reset the parent container */
        #months-container {
            width: 100% !important;
            overflow-x: hidden !important;
            padding: 10px !important;
            display: block !important;
            /* Kill any flexbox from AdminLTE */
        }

        /* 2. Create the rigid 3-column grid */
        .months-grid-enforcer {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            /* EXACTLY 3 columns */
            grid-gap: 12px !important;
            width: 100% !important;
        }

        /* 3. Style individual items to prevent overflow */
        .month-selector-item {
            min-width: 0 !important;
            /* Critical for grid text wrapping */
            background: #fdfdfd;
            border: 1px solid #eee;
            padding: 8px;
            border-radius: 4px;
        }

        /* 4. Force Label Text Wrapping */
        #months-container .custom-control-label {
            display: inline-block !important;
            white-space: normal !important;
            /* Force wrap long months */
            word-break: break-word !important;
            line-height: 1.2 !important;
            font-size: 0.9rem !important;
            cursor: pointer;
            padding-left: 5px;
            width: 100%;
        }

        /* 5. Checkbox alignment */
        #months-container .custom-control-input {
            cursor: pointer;
        }

        /* 6. Responsive: Drop to 2 columns on small tablets, 1 on mobile */
        @media (max-width: 768px) {
            .months-grid-enforcer {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }

        @media (max-width: 480px) {
            .months-grid-enforcer {
                grid-template-columns: 1fr !important;
            }
        }

        /* FORCE THE GRID - Override everything else */
        #months-container {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            /* EXACTLY 3 columns */
            gap: 10px !important;
            width: 100% !important;
            max-width: 100% !important;
            padding: 15px !important;
            overflow-x: hidden !important;
            background: #fff;
        }

        /* Individual Item Container */
        .month-selector-item {
            display: flex !important;
            align-items: flex-start !important;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 8px !important;
            border-radius: 4px;
            min-width: 0 !important;
            /* Critical for grid text wrapping */
        }

        /* Checkbox Alignment */
        .month-selector-item input[type="checkbox"] {
            margin-top: 4px !important;
            margin-right: 10px !important;
            transform: scale(1.2);
            /* Make it slightly more clickable */
            cursor: pointer;
            flex-shrink: 0;
        }

        /* Label - Force Wrap */
        .month-selector-item label {
            margin-bottom: 0 !important;
            font-weight: normal !important;
            font-size: 0.9rem !important;
            line-height: 1.2 !important;
            white-space: normal !important;
            /* FORCES TEXT TO NEXT LINE */
            word-break: break-word !important;
            cursor: pointer;
            flex: 1;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            #months-container {
                grid-template-columns: repeat(2, 1fr) !important;
                /* 2 columns on tablets */
            }
        }

        @media (max-width: 480px) {
            #months-container {
                grid-template-columns: 1fr !important;
                /* 1 column on phones */
            }
        }
    </style>