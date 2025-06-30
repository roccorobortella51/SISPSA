<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\AgenteFuerza $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Agente Fuerzas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="agente-fuerza-view">

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
            'idusuario',
            'agente_id',
            'por_venta',
            'por_asesor',
            'por_cobranza',
            'por_post_venta',
            'puede_vender',
            'puede_asesorar',
            'puede_cobrar',
            'puede_post_venta',
            'created_at',
            'updated_at',
            'deleted_at',
            'puede_registrar',
            'por_registrar',
        ],
    ]) ?>

</div>
