<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\ContactosEmergencia $model */

$this->title = 'Create Contactos Emergencia';
$this->params['breadcrumbs'][] = ['label' => 'Contactos Emergencias', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="contactos-emergencia-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
