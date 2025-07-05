<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\User $model */


$this->title = 'CREAR USUARIO';
$this->params['breadcrumbs'][] = ['label' => 'USUARIOS', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="col-xl-12 col-md-12">
    <div class="ms-panel ms-panel-fh">
        <div class="ms-panel-header">
            <h1><?= Html::encode($this->title); ?></h1>
        </div>
        <div class="ms-panel-body">
            <?= $this->render('_form', [
                'model' => $model,
                'model2' => $model2
            ]) ?>        
        </div>
    </div>
</div>

