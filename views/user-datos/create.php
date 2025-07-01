<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\UserDatos $model */

$this->title = 'Create User Datos';
$this->params['breadcrumbs'][] = ['label' => 'User Datos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-datos-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
