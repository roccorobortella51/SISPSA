<?php

use yii\helpers\Html;
use yii\helpers\Url;

// ===== FIX: Define URLs at the top =====
$dailyCheckUrl = Url::to(['/cuota-web/daily-check']);
$graceReportUrl = Url::to(['/cuota-web/grace-report']);

/** @var yii\web\View $this */

$this->title = 'Verificación Diaria de Cuotas';
$this->params['breadcrumbs'][] = ['label' => 'Gestión de Cuotas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="daily-check">

    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="display-4 text-primary">
                <i class="fas fa-calendar-check mr-3"></i>
                <?= Html::encode($this->title) ?>
            </h1>
            <p class="lead text-muted">
                Ejecute manualmente el proceso automático de verificación de cuotas y contratos
            </p>
        </div>
        <div class="col-md-4 text-right">
            <?= Html::a(
                '<i class="fas fa-arrow-left mr-2"></i> Volver al Panel',
                ['index'],
                ['class' => 'btn btn-outline-secondary btn-lg px-4']
            ) ?>
        </div>
    </div>

    <!-- Info Cards Row -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-hourglass-half fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h6 class="text-primary font-weight-bold mb-1">Período de Gracia</h6>
                            <p class="small text-muted mb-0">7 días para pagar después del vencimiento</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                        </div>
                        <div>
                            <h6 class="text-warning font-weight-bold mb-1">Cuotas Vencidas</h6>
                            <p class="small text-muted mb-0">Después de 7 días sin pago</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-sync-alt fa-2x text-success"></i>
                        </div>
                        <div>
                            <h6 class="text-success font-weight-bold mb-1">Reactivación</h6>
                            <p class="small text-muted mb-0">Contratos sin cuotas vencidas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-danger shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-pause-circle fa-2x text-danger"></i>
                        </div>
                        <div>
                            <h6 class="text-danger font-weight-bold mb-1">Suspensión</h6>
                            <p class="small text-muted mb-0">Contratos con cuotas vencidas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Execution Card -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold">
                        <i class="fas fa-terminal mr-2"></i>
                        Consola de Verificación
                    </h5>
                    <div>
                        <span class="badge badge-light mr-2">
                            <i class="fas fa-clock mr-1"></i>
                            Cron: 00:05 diario
                        </span>
                        <span class="badge badge-light">
                            <i class="fas fa-calendar mr-1"></i>
                            Grace Period: 7 días
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Execution Controls -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Información:</strong> Esta herramienta ejecuta el mismo proceso que el cron job automático.
                                Los resultados se muestran exactamente como aparecerían en la consola.
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <?= Html::button(
                                '<i class="fas fa-play-circle mr-2"></i> Ejecutar Verificación',
                                [
                                    'id' => 'btn-execute-daily',
                                    'class' => 'btn btn-success btn-lg btn-block',
                                    'data-loading-text' => '<i class="fas fa-spinner fa-spin mr-2"></i> Ejecutando...'
                                ]
                            ) ?>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div id="progress-container" style="display: none;" class="mb-4">
                        <div class="progress" style="height: 20px;">
                            <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                role="progressbar" style="width: 0%;">
                                <span id="progress-text">0%</span>
                            </div>
                        </div>
                        <p class="text-center mt-2 small text-muted" id="progress-status">
                            Procesando...
                        </p>
                    </div>

                    <!-- Console Output -->
                    <div class="console-container">
                        <div class="console-header bg-dark text-white p-2 d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-terminal mr-2"></i>
                                <strong>Salida de Consola</strong>
                            </span>
                            <div>
                                <span id="output-timestamp" class="small mr-3 text-muted"></span>
                                <button id="btn-clear-output" class="btn btn-sm btn-outline-light">
                                    <i class="fas fa-eraser"></i>
                                </button>
                            </div>
                        </div>
                        <pre id="output" class="console-output bg-dark text-success p-4 mb-0">╔══════════════════════════════════════════════════════════╗
║    VERIFICACIÓN DIARIA DE CUOTAS                         ║
║    Listo para ejecutar. Presione el botón para comenzar. ║
╚══════════════════════════════════════════════════════════╝</pre>
                    </div>

                    <div class="mt-3 text-right small text-muted">
                        <span id="output-line-count">0 líneas</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Info -->
    <div class="row">
        <div class="col-md-12">
            <div class="card bg-light border-0">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center small">
                        <span>
                            <i class="fas fa-clock text-primary mr-1"></i>
                            <strong>Programación:</strong> Todos los días a las 00:05
                        </span>
                        <span>
                            <i class="fas fa-calendar-check text-success mr-1"></i>
                            <strong>Última ejecución automática:</strong> Hoy 00:05
                        </span>
                        <span>
                            <i class="fas fa-hourglass-half text-warning mr-1"></i>
                            <strong>Próxima:</strong> Mañana 00:05
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .border-left-primary {
        border-left: 4px solid #007bff !important;
    }

    .border-left-warning {
        border-left: 4px solid #ffc107 !important;
    }

    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }

    .border-left-danger {
        border-left: 4px solid #dc3545 !important;
    }

    .console-container {
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .console-header {
        background-color: #1e2a3a;
        border-bottom: 1px solid #2c3e50;
    }

    .console-output {
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
        line-height: 1.6;
        background-color: #1e2a3a !important;
        color: #00ff9d !important;
        white-space: pre-wrap;
        word-wrap: break-word;
        min-height: 400px;
        max-height: 600px;
        overflow-y: auto;
        scroll-behavior: smooth;
        border-bottom-left-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
        margin: 0;
    }

    .console-output::-webkit-scrollbar {
        width: 10px;
    }

    .console-output::-webkit-scrollbar-track {
        background: #2c3e50;
    }

    .console-output::-webkit-scrollbar-thumb {
        background: #4a5c6e;
        border-radius: 5px;
    }

    .console-output::-webkit-scrollbar-thumb:hover {
        background: #5d6f82;
    }

    .card {
        border-radius: 0.5rem;
        border: none;
        transition: all 0.2s ease;
    }

    .btn {
        border-radius: 0.25rem;
    }

    .btn-success {
        background: linear-gradient(135deg, #28a745, #218838);
        border: none;
    }

    .btn-success:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
    }

    @media (max-width: 768px) {
        .display-4 {
            font-size: 2.5rem;
        }

        .console-output {
            min-height: 300px;
        }
    }
</style>

<?php
// ===== FIX: Register JavaScript with properly passed variables =====
$script = <<<JS
    // URLs passed from PHP
    var dailyCheckUrl = "$dailyCheckUrl";
    var graceReportUrl = "$graceReportUrl";
    
    console.log('Daily Check URL:', dailyCheckUrl);
    console.log('Grace Report URL:', graceReportUrl);
    
    $(document).ready(function() {
        let output = $('#output');
        let btn = $('#btn-execute-daily');
        let progressContainer = $('#progress-container');
        let progressBar = $('#progress-bar');
        let progressText = $('#progress-text');
        let progressStatus = $('#progress-status');
        let outputTimestamp = $('#output-timestamp');
        let outputLineCount = $('#output-line-count');
        
        // Function to update line count
        function updateLineCount() {
            let text = output.text();
            let lines = text.split('\\n').length;
            outputLineCount.text(lines + ' líneas');
        }
        
        updateLineCount();
        
        // Clear output
        $('#btn-clear-output').click(function() {
            output.html('╔══════════════════════════════════════════════════════════╗\\n║    CONSOLA LIMPIADA - LISTO PARA NUEVA EJECUCIÓN   ║\\n╚══════════════════════════════════════════════════════════╝');
            updateLineCount();
            outputTimestamp.text('');
        });
        
        // Execute daily check
        btn.click(function() {
            console.log('Button clicked, calling URL:', dailyCheckUrl);
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Ejecutando...');
            
            progressContainer.show();
            progressBar.css('width', '10%');
            progressText.text('10%');
            progressStatus.text('Conectando...');
            
            let now = new Date();
            outputTimestamp.text(now.toLocaleString());
            
            output.html('╔══════════════════════════════════════════════════════════╗\\n║         EJECUTANDO VERIFICACIÓN DIARIA...          ║\\n╚══════════════════════════════════════════════════════════╝\\n');
            updateLineCount();
            
            $.ajax({
                url: dailyCheckUrl,
                type: 'POST',
                dataType: 'json',
                timeout: 60000,
                beforeSend: function() {
                    console.log('Sending AJAX request to:', dailyCheckUrl);
                },
                success: function(response) {
                    console.log('AJAX Success:', response);
                    
                    progressBar.css('width', '100%');
                    progressText.text('100%');
                    progressStatus.text('Completado');
                    
                    if (response.success) {
                        output.html(response.output);
                    } else {
                        output.html('❌ ERROR:\\n' + (response.output || 'Error desconocido'));
                    }
                    
                    updateLineCount();
                    output.scrollTop(output[0].scrollHeight);
                    
                    setTimeout(() => {
                        progressContainer.fadeOut(500);
                    }, 2000);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:');
                    console.error('URL:', dailyCheckUrl);
                    console.error('Status:', status);
                    console.error('Error:', error);
                    console.error('Response:', xhr.responseText);
                    
                    let errorMsg = '❌ ERROR DE CONEXIÓN:\\n\\n';
                    errorMsg += 'URL: ' + dailyCheckUrl + '\\n';
                    errorMsg += 'Status: ' + status + '\\n';
                    errorMsg += 'Error: ' + error + '\\n';
                    errorMsg += 'HTTP Status: ' + (xhr.status || 'unknown') + '\\n\\n';
                    
                    if (xhr.responseText) {
                        errorMsg += 'Respuesta del servidor:\\n' + xhr.responseText;
                    } else {
                        errorMsg += 'No se recibió respuesta del servidor.';
                    }
                    
                    output.html(errorMsg);
                    
                    progressBar.css('width', '100%').removeClass('bg-primary').addClass('bg-danger');
                    progressText.text('Error');
                    progressStatus.text('Error en la conexión');
                    updateLineCount();
                    
                    setTimeout(() => {
                        progressContainer.fadeOut(500);
                    }, 3000);
                },
                complete: function() {
                    console.log('AJAX complete');
                    setTimeout(() => {
                        btn.prop('disabled', false).html('<i class="fas fa-play-circle mr-2"></i> Ejecutar Verificación');
                        progressBar.removeClass('bg-danger').addClass('bg-primary');
                    }, 2000);
                }
            });
        });
    });
JS;

$this->registerJs($script, \yii\web\View::POS_END);
?>