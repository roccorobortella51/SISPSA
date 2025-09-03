<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Pagos $model */

$this->title = 'Crear Nuevo Pago'; // Título más descriptivo
$this->params['breadcrumbs'][] = ['label' => 'Pagos', 'url' => ['contratos/index', 'user_id' => $model->user_id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container-fluid py-4">
    <div class="card shadow-lg mb-4 rounded-3">
        <div class="card-header bg-primary text-white text-center py-3 rounded-top-2">
            <h3 class="card-title mb-0 display-3" style="font-size: 1.8em;">
                <i class="fas fa-money-check-alt me-2"></i> <?= Html::encode($this->title) ?>
            </h3>
            <span style = "float: right; margin-top: -10px;">
                <?php
                    $ejecutarParams = ['pagos/ejecutar'];
                    $ejecutarParams['user_id'] = $model->user_id;
                ?>
                <?= Html::a('Calcular Cuotas', $ejecutarParams, [
                    'class' => 'btn btn-warning ms-2',
                    'style' => 'float: right;',
                ]) ?>
            </span>

        </div>
        <div class="card-body p-4">
            <!-- El botón "Volver" y "Guardar Pago" ahora se gestionan dentro del _form.php -->
            <?= $this->render('_form', [
                'model' => $model,
                'cuotas' => $cuotas,
                'modelCuotas' => $modelCuotas,
                'total' => $total,
            ]) ?>
        </div>
    </div>
</div>
