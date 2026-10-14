<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Contratos */

$isRenewal = !empty($isRenewal);
$this->title = $isRenewal ? 'Renovar Contrato' : 'Crear Nuevo Contrato';
$this->params['breadcrumbs'][] = ['label' => 'Contratos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container-fluid py-4">
    <div class="card shadow-lg mb-4 rounded-3">
        <div class="card-header bg-primary text-white text-center py-3 rounded-top-3">
            <h3 class="card-title mb-0 display-3" style="font-size: 1.8em;"> <!-- Título para el formulario de creación -->
                <i class="fas fa-plus-circle me-2"></i> <?= Html::encode($this->title) ?>
            </h3>
        </div>
        <div class="card-body p-4">
            <?php if ($isRenewal && isset($sourceContract)): ?>
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle mr-2" aria-hidden="true"></i>
                    Se renovará el contrato #<?= Html::encode($sourceContract->id) ?> con sus condiciones actuales. La nueva cobertura comenzará el día siguiente a su vencimiento.
                </div>
            <?php endif; ?>
            <div class="row">
                <div class="col-md-12">
                    <?= $this->render('_form', [
                        'model' => $model,
                        'isRenewal' => $isRenewal,
                    ]) ?>
                </div>
            </div>
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>