<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\CheckListClinicas */

$this->title = 'CREAR LISTA DE VERIFICACIÓN DE CLÍNICAS';
$this->params['breadcrumbs'][] = ['label' => 'CREAR LISTA DE VERIFICACIÓN DE CLÍNICAS', 'url' => ['index', 'clinica_id' => $clinica->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= Yii::$app->session->getFlash('success') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= Yii::$app->session->getFlash('error') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
<div class="col-xl-12 col-md-12">
    <div class="ms-panel ms-panel-fh">
        <div class="ms-panel-header">
            <h1><?= $this->title = 'Verificación de la Clínica '.$clinica->nombre; ?></h1>
        </div>
        <div class="ms-panel-body">
            <?= $this->render('_form', [
                'model' => $model,
                'clinica' => $clinica
            ]) ?>        
        </div>
    </div>
</div>
