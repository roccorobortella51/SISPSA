<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Agente $model */

$this->title = 'CREAR NUEVA AGENCIA'; // Aseguramos que el título esté establecido aquí
$this->params['breadcrumbs'][] = ['label' => 'AGENCIAS', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="col-xl-12 col-md-12">
    <div class="ms-panel ms-panel-fh">
        <div class="ms-panel-header">
            <h4><?= Html::encode($this->title); ?></h4>
        </div>
        <div class="ms-panel-body">
            <div class="agente-create">
                <?= $this->render('_form', [
                    'model' => $model,
                ]) ?>
            </div>
        </div>
    </div>
</div>