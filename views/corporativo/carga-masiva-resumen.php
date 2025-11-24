<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $resultados array */
/* @var $corporativo app\models\Corporativo */

$this->title = 'Resumen de Carga Masiva';
$this->params['breadcrumbs'][] = ['label' => 'Carga Masiva', 'url' => ['carga-masiva-afiliados']];
$this->params['breadcrumbs'][] = $this->title;

$successCount = $resultados['successCount'];
$errorCount = count($resultados['errors']);
$totalProcessed = $successCount + $errorCount;
?>

<div class="carga-masiva-resumen">
    <h1><?= Html::encode($this->title) ?></h1>
    <h2>Corporativo: <?= Html::encode($corporativo->nombre) ?></h2>

    <div class="row">
        <div class="col-md-4">
            <div class="panel panel-info">
                <div class="panel-heading">Total de Filas Procesadas</div>
                <div class="panel-body text-center">
                    <h2 class="text-info"><?= $totalProcessed ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-success">
                <div class="panel-heading">Registros Exitosos</div>
                <div class="panel-body text-center">
                    <h2 class="text-success"><?= $successCount ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-danger">
                <div class="panel-heading">Registros con Error</div>
                <div class="panel-body text-center">
                    <h2 class="text-danger"><?= $errorCount ?></h2>
                </div>
            </div>
        </div>
    </div>

    <?php if ($errorCount > 0): ?>
        <h3>Detalle de Errores (<?= $errorCount ?>)</h3>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($resultados['errors'] as $error): ?>
                    <li><?= Html::encode($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <p>
        <?= Html::a('Realizar otra Carga', ['carga-masiva-afiliados'], ['class' => 'btn btn-default']) ?>
    </p>
</div>