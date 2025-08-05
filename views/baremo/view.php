<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\UserHelper;
/** @var yii\web\View $this */
/** @var app\models\Baremo $model */

$this->title = $model->nombre_servicio; // Usamos el nombre del servicio como título
$this->params['breadcrumbs'][] = ['label' => 'Baremos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$rol = UserHelper::getMyRol();

$permisos = false;

if ($rol == 'superadmin') 
{
    $permisos = true;
}


?>
<div class="baremo-view">
    <?php if($permisos == true){?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <p>
            <?= Html::a('Actualizar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Eliminar', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => '¿Estás seguro de que quieres eliminar este elemento?',
                    'method' => 'post',
                ],
            ]) ?>
        </p>
    </div>
    <?php } ?>


    <!-- Contenido de la vista en un diseño de tarjeta de Bootstrap -->
    <div class="card shadow-sm rounded">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Detalles del Servicio</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12 text-end">
                        <div class="float-right" style="margin-bottom:10px;">
                            <?= Html::a(
                                                '<i class="fas fa-undo"></i> Volver', 
                                                '#',
                                                [
                                                    'class' => 'btn btn-primary btn-lg', 
                                                    'onclick' => 'window.history.back(); return false;', 
                                                    'title' => 'Volver a la página anterior', 
                                                ]
                                            ) ?>  
                        </div>
                    </div>
                <!-- Columna para la información del servicio -->
                <div class="col-lg-6">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>Área:</strong>
                            <span class="float-end"><?= Html::encode($model->area ? $model->area->nombre : 'N/A') ?></span>
                        </li>
                        <li class="list-group-item">
                            <strong>Estatus:</strong>
                            <span class="float-end badge rounded-pill <?= $model->estatus == 1 ? 'bg-success' : 'bg-danger' ?>">
                                <?= $model->estatus == 1 ? 'Activo' : 'Inactivo' ?>
                            </span>
                        </li>
                        <li class="list-group-item">
                            <strong>Costo:</strong>
                            <span class="float-end"><?= $model->costo ?></span>
                        </li>
                        <li class="list-group-item">
                            <strong>Precio:</strong>
                            <span class="float-end"><?= $model->precio ?></span>
                        </li>
                    </ul>
                </div>
                <!-- Columna para las fechas -->
                <div class="col-lg-6">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>Fecha de Creación:</strong>
                            <span class="float-end"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></span>
                        </li>
                        <li class="list-group-item">
                            <strong>Última Actualización:</strong>
                            <span class="float-end"><?= Yii::$app->formatter->asDatetime($model->updated_at) ?></span>
                        </li>
                        <li class="list-group-item">
                            <strong>Eliminado en:</strong>
                            <span class="float-end"><?= $model->deleted_at ? Yii::$app->formatter->asDatetime($model->deleted_at) : 'N/A' ?></span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <h6 class="mt-4 border-bottom pb-2">Descripción del Servicio</h6>
            <p class="card-text text-muted"><?= Html::encode($model->descripcion) ?></p>

        </div>
    </div>
</div>