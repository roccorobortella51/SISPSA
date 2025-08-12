<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Contratos */

$this->title = 'Crear Nuevo Contrato'; // Título de la página
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
            <div class="row">
                <div class="col-md-12">
                    <?= $this->render('_form', [
                        'model' => $model
                    ]) ?>
                </div>
            </div>
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>