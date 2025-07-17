<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Corporativo $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Corporativos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="corporativo-view">

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
            'nombre',
            'email:email',
            'telefono',
            'rif',
            'estado',
            'municipio',
            'parroquia',
            'direccion:ntext',
            'codigo_asesor',
            'lugar_registro',
            'fecha_registro_mercantil',
            'tomo_registro',
            'folio_registro',
            'domicilio_fiscal:ntext',
            'contacto_nombre',
            'contacto_cedula',
            'contacto_telefono',
            'contacto_cargo',
            'estatus',
            'created_at',
            'updated_at',
            'deleted_at',
        ],
    ]) ?>

</div>
