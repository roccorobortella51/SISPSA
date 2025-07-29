<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Corporativo $model */

$this->title = 'CREAR AFILIADO CORPORATIVO';
$this->params['breadcrumbs'][] = ['label' => 'AFILIADOS CORPORATIVOS', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="col-xl-12 col-md-12">
    <div class="ms-panel ms-panel-fh">

        <div class="ms-panel-header d-flex justify-content-between align-items-center">
            <h1><?= Html::encode($this->title); ?></h1>
    
               
        </div>


        <div class="ms-panel-body">
        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>          
        </div>
    </div>
</div>
