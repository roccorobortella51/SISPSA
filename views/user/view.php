<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $userDatos app\models\UserDatos */

$this->title = $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-view container">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('<i class="fas fa-edit"></i> Actualizar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary btn-lg']) ?>
        <?= Html::a('<i class="fas fa-trash"></i> Eliminar', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger btn-lg',
            'data' => [
                'confirm' => '¿Está seguro que desea eliminar este usuario?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <h3>Información del Usuario</h3>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'username',
            'email',
            'status',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

    <h3>Datos del Usuario</h3>
    <?php if ($userDatos): ?>
        <?= DetailView::widget([
            'model' => $userDatos,
            'attributes' => [
                'nombres',
                'apellidos',
                'telefono',
                'email',
                'sexo',
                'fechanac',
                'direccion',
                'ciudad',
                'municipio',
                'parroquia',
            ],
        ]) ?>
    <?php else: ?>
        <p>No hay datos adicionales disponibles para este usuario.</p>
    <?php endif; ?>

</div>
