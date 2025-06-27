<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\ContactosEmergencia $model */

$this->title = 'Update Contactos Emergencia: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Contactos Emergencias', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="contactos-emergencia-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
