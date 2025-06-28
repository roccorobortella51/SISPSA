<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\RmClinica $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Rm Clinicas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="rm-clinica-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'created_at',
            'rif:ntext',
            'nombre:ntext',
            'estado:ntext',
            'direccion:ntext',
            'telefono:ntext',
            'correo:ntext',
            'estatus:ntext',
            'webpage:ntext',
            'rs_instagram:ntext',
            'QRCode:ntext',
            'codigo_clinica:ntext',
            'deleted_at',
            'updated_at',
            'private_key',
        ],
    ]) ?>

</div>
