<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\RmClinica $model */

$this->title = 'CREAR CLÍNICA'; // Título principal de la página
$this->params['breadcrumbs'][] = ['label' => 'CREAR CLÍNICAS', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="col-xl-12 col-md-12">
    <div class="ms-panel ms-panel-fh">
        <div class="ms-panel-header d-flex justify-content-between align-items-center mb-3">
            <h1 class="m-0"><?= Html::encode($this->title); ?></h1>

            <div>
                <?= Html::a('<i class="fas fa-undo"></i> Volver', ['index'], ['class' => 'btn btn-primary btn-lg']) ?>
            </div>
        </div>
        <div class="ms-panel-body">
            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>        
        </div>
    </div>
</div>