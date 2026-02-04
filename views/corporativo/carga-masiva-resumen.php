<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $resultados array */
/* @var $corporativo app\models\Corporativo */

$this->title = 'Resumen de Carga Masiva';
$this->params['breadcrumbs'][] = ['label' => 'Carga Masiva', 'url' => ['carga-masiva-afiliados']];
$this->params['breadcrumbs'][] = $this->title;

$errorCount = count($resultados['errors']);
$successCount = $resultados['successCount'];
$totalProcessed = $successCount + $errorCount;

?>
<div class="carga-masiva-resumen">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Detalles del Proceso</h3>
        </div>
        <div class="panel-body">
            <p><strong>Corporativo de Destino:</strong> <?= Html::encode($corporativo ? $corporativo->nombre : 'N/A') ?></p>
            <p><strong>Registros Totales Procesados:</strong> <?= $totalProcessed ?></p>
            <p><strong>Afiliados Cargados con Éxito:</strong> <span class="label label-success"><?= $successCount ?></span></p>
            <p><strong>Errores Encontrados:</strong> <span class="label label-danger"><?= $errorCount ?></span></p>
        </div>
    </div>

    <?php if ($errorCount > 0): ?>
        <div class="panel panel-warning">
            <div class="panel-heading">
                <h3 class="panel-title">Detalle de Errores</h3>
            </div>
            <div class="panel-body">
                <p>Se encontraron los siguientes problemas. Los registros con errores no fueron guardados.</p>
                <div class="list-group">
                    <?php foreach ($resultados['errors'] as $error): ?>
                        <div class="list-group-item list-group-item-danger">
                            <?= Html::encode($error) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="form-group">
        <?= Html::a('Realizar Otra Carga', ['carga-masiva-afiliados'], ['class' => 'btn btn-info']) ?>
    </div>

</div>