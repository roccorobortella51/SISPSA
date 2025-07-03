<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\UserDatos $model */

$this->title = 'Actualizar datos del afiliado: Nombre completo: '.$model->nombres." ".$model->apellidos.', Cédula: ' . $model->cedula;
$this->params['breadcrumbs'][] = ['label' => 'AFILIADOS', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Actualizar';
?>
<div class="col-xl-12 col-md-12">
    <div class="ms-panel ms-panel-fh">
        <div class="ms-panel-header">
            <h1><?= Html::encode($this->title); ?></h1>
        </div>
        <div class="ms-panel-body">
            <?= $this->render('_form', [
                'model' => $model,
                'modelContrato' => $modelContrato,
            ]) ?>        
        </div>
    </div>
</div>
