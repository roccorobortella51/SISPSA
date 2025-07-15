<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\User $model */

$this->title = 'DETALLES DE USUARIO: ';
$this->params['breadcrumbs'][] = ['label' => 'Usuarios', 'url' => ['index']];
$this->params['breadcrumbs'][] = Html::encode($model->username);

\yii\web\YiiAsset::register($this);

function formatBooleanIcon($value) {
    // Aquí el valor ACTIVO es 10 (lo que está en la base de datos para Activo)
    $isTrue = ($value == 1); // <--- ¡Asegúrate de que sea 10!
    return ($isTrue ? ('<span class="text-success me-1"><i class="fas fa-check-circle"></i></span> Activo') : ('<span class="text-danger me-1"><i class="fas fa-times-circle"></i></span> Inactivo'));
}

?>

<div class="user-view">

<div class="ms-panel-header mb-3"> 
    <h4><?= Html::encode($this->title) ?></h4>
</div>

<div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
    <div class="col">
        <?= Html::a(
            '<i class="fas fa-edit"></i> Actualizar',
            ['update', 'id' => $model->id],
            ['class' => 'btn btn-primary w-100'] // btn por defecto y ancho completo
        ) ?>
    </div>

    <div class="col">
        <?= Html::a(
            '<i class="fas fa-trash-alt"></i> Eliminar',
            ['delete', 'id' => $model->id],
            [
                'class' => 'btn btn-danger w-100', // btn por defecto y ancho completo
                'data' => [
                    'confirm' => '¿Estás seguro de que quieres eliminar este usuario? Esta acción no se puede deshacer.',
                    'method' => 'post',
                ],
            ]
        ) ?>
    </div>

    <div class="col">
        <?= Html::a(
            '<i class="fas fa-undo"></i> Volver',
            ['index'],
            ['class' => 'btn btn-secondary w-100'] // btn por defecto y ancho completo
        ) ?>
    </div>
</div>

    <div class="card shadow-sm">
        <div class="card-body">

            <div class="row row-cols-1 row-cols-md-3 g-3 mb-3">
                
                <div class="col-md-4">
                    <div class="card card-body bg-light text-center">
                        <h6 class="text-muted">Nombre de Usuario (Login)</h6>
                        <p class="h4 text-dark"><?= Html::encode($model->username) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-body bg-light text-center">
                        <h6 class="text-muted">Correo Electrónico</h6>
                        <p class="h4 text-dark"><?= Html::encode($model->email) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
        <div class="card card-body bg-light text-center">
            <h6 class="text-muted">Estado</h6>
            <p class="h4 text-dark"><?= formatBooleanIcon($model->status) ?></p>
        </div>
    </div>
            </div>


            <h5 class="mt-4 mb-3">Datos Personales del Afiliado</h5>
            <div class="row g-3 mb-3">
                <?php if ($model->userDatos): // Verificamos si existe la relación userDatos ?>
                    <div class="col-md-12">
                        <div class="card card-body bg-light text-center">
                            <h6 class="text-muted">Nombre Completo</h6>
                            <p class="h3 text-dark">
                                <?php
                                // Concatena nombres y apellidos de UserDatos
                                if (!empty($model->userDatos->nombres) || !empty($model->userDatos->apellidos)) {
                                    echo Html::encode($model->userDatos->nombres . ' ' . $model->userDatos->apellidos);
                                } else {
                                    echo 'No especificado';
                                }
                                ?>
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card card-body bg-light text-center">
                            <h6 class="text-muted">Fecha de Nacimiento</h6>
                            <p class="h5 text-dark">
                                <?= !empty($model->userDatos->fechanac) ? Yii::$app->formatter->asDate($model->userDatos->fechanac) : 'No especificada' ?>
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card card-body bg-light text-center">
                            <h6 class="text-muted">Teléfono</h6>
                            <p class="h5 text-dark">
                                <?= !empty($model->userDatos->telefono) ? Html::encode($model->userDatos->telefono) : 'No especificado' ?>
                            </p>
                        </div>
                    </div>

                    

                    <div class="col-md-12">
                        <div class="card card-body bg-light text-center">
                            <h6 class="text-muted">Dirección Completa</h6>
                            <p class="h5 text-dark">
                                <?php
                                $direccionParts = [];
                                if (!empty($model->userDatos->direccion)) $direccionParts[] = $model->userDatos->direccion;
                                if (!empty($model->userDatos->parroquia)) $direccionParts[] = $model->userDatos->parroquia;
                                if (!empty($model->userDatos->municipio)) $direccionParts[] = $model->userDatos->municipio;
                                if (!empty($model->userDatos->ciudad)) $direccionParts[] = $model->userDatos->ciudad; // Agregado ciudad
                                if (!empty($model->userDatos->estado)) $direccionParts[] = $model->userDatos->estado;

                                if (!empty($direccionParts)) {
                                    echo Html::encode(implode(', ', $direccionParts));
                                } else {
                                    echo 'No especificada';
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="col-md-12">
                        <div class="card card-body bg-light text-center">
                            <p class="h5 text-danger">Datos de afiliado no encontrados para este usuario.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            

            <h5 class="mt-4 mb-3">Fechas de Registro</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card card-body bg-light text-center">
                        <h6 class="text-muted">Fecha de Creación</h6>
                        <p class="h5 text-dark"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-body bg-light text-center">
                        <h6 class="text-muted">Última Actualización</h6>
                        <p class="h5 text-dark"><?= Yii::$app->formatter->asDatetime($model->updated_at) ?></p>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
