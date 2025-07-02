<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\DeclaracionDeSalud $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Declaracion De Saluds', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="declaracion-de-salud-view">

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
            'p1_sino:ntext',
            'p1_especifica:ntext',
            'p2_sino:ntext',
            'p2_especifica:ntext',
            'p3_sino:ntext',
            'p3_especifica:ntext',
            'p4_sino:ntext',
            'p4_especifica:ntext',
            'p5_sino:ntext',
            'p5_especifica:ntext',
            'p6_sino:ntext',
            'p6_especifica:ntext',
            'p7_sino:ntext',
            'p7_especifica:ntext',
            'p8_sino:ntext',
            'p8_especifica:ntext',
            'p9_sino:ntext',
            'p9_especifica:ntext',
            'p10_sino:ntext',
            'p10_especifica:ntext',
            'p11_sino:ntext',
            'p11_especifica:ntext',
            'p12_sino:ntext',
            'p12_especifica:ntext',
            'p13_sino:ntext',
            'p13_especifica:ntext',
            'p14_sino:ntext',
            'p14_especifica:ntext',
            'p15_sino:ntext',
            'p15_especifica:ntext',
            'p16_sino:ntext',
            'p16_especifica:ntext',
            'deleted_at',
            'updated_at',
            'ver_usuario_id',
            'ver_observacion:ntext',
            'ver_si_no:ntext',
            'ver_fecha',
            'url_video_declaracion:ntext',
            'estatus:ntext',
            'user_id',
            'estatura:ntext',
            'peso:ntext',
        ],
    ]) ?>

</div>
