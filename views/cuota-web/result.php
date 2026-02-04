<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $success bool */
/* @var $title string */
/* @var $output string */
/* @var $rawOutput string */

$this->title = $title;
?>
<div class="cuota-web-result">
    <div class="card">
        <div class="card-header">
            <h1 class="card-title"><?= Html::encode($title) ?></h1>
        </div>
        <div class="card-body">
            <div class="alert alert-<?= $success ? 'success' : 'danger' ?>">
                <h4 class="alert-heading"><?= $success ? '✅ Éxito' : '❌ Error' ?></h4>
                <p class="mb-0">El proceso ha <?= $success ? 'completado exitosamente' : 'encontrado errores' ?>.</p>
            </div>
            
            <div class="output-container">
                <h4>Salida del Proceso:</h4>
                <div class="output-content" style="background: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #dee2e6; font-family: 'Courier New', monospace; font-size: 14px; white-space: pre-wrap;">
                    <?= $output ?>
                </div>
            </div>
            
            <div class="actions mt-4">
                <div class="row">
                    <div class="col-md-6">
                        <?= Html::a('🔄 Generar Cuotas', ['generar'], ['class' => 'btn btn-primary btn-block mb-2']) ?>
                        <?= Html::a('📅 Generar Mensual', ['generar-mensual'], ['class' => 'btn btn-secondary btn-block mb-2']) ?>
                        <?= Html::a('✅ Verificar Diario', ['verificar-diario'], ['class' => 'btn btn-info btn-block mb-2']) ?>
                    </div>
                    <div class="col-md-6">
                        <?= Html::a('⏰ Verificar Vencidas', ['verificar-vencidas'], ['class' => 'btn btn-danger btn-block mb-2']) ?>
                        <?= Html::a('📊 Resumen Próximos', ['resumen-proximos-vencer'], ['class' => 'btn btn-warning btn-block mb-2']) ?>
                        <?= Html::a('📋 Resumen Atrasadas', ['resumen-atrasadas'], ['class' => 'btn btn-warning btn-block mb-2']) ?>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <?= Html::a('📑 Verificar Contratos Vencidos', ['verificar-contratos-vencidos'], ['class' => 'btn btn-dark btn-block mb-2']) ?>
                    </div>
                    <div class="col-md-6">
                        <?= Html::a('⏳ Verificar Espera', ['verificar-espera'], ['class' => 'btn btn-light btn-block mb-2']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>