<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\UserDatos */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'User Datos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
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
                            'user_id',
                            'nombres:ntext',
                            'fechanac',
                            'sexo:ntext',
                            'selfie:ntext',
                            'telefono:ntext',
                            'estado:ntext',
                            'role:ntext',
                            'estatus:ntext',
                            'imagen_identificacion:ntext',
                            'qr:ntext',
                            'paso',
                            'video:ntext',
                            'ciudad:ntext',
                            'municipio:ntext',
                            'parroquia:ntext',
                            'direccion:ntext',
                            'codigoValidacion:ntext',
                            'clinica_id',
                            'plan_id',
                            'apellidos:ntext',
                            'email:email',
                            'contrato_id',
                            'asesor_id',
                            'deleted_at',
                            'updated_at',
                            'ver_cedula:ntext',
                            'ver_foto:ntext',
                            'session_id',
                            'cedula',
                            'tipo_cedula:ntext',
                            'tipo_sangre:ntext',
                            'estatus_solvente:ntext',
                        ],
                    ]) ?>
                </div>
                <!--.col-md-12-->
            </div>
            <!--.row-->
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>