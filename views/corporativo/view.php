<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Corporativo $model */

$this->title = 'DETALLES DEL AFILIADO CORPORATIVO: ' . Html::encode($model->nombre);
$this->params['breadcrumbs'][] = ['label' => 'Corporativos', 'url' => ['index']];
$this->params['breadcrumbs'][] = Html::encode($model->nombre);
\yii\web\YiiAsset::register($this);

// Función auxiliar para formatear fechas, manejando valores nulos
function formatUpdatedAt($value) {
    if (empty($value)) {
        return 'No se ha modificado';
    }
    return Yii::$app->formatter->asDatetime($value, 'medium');
}

?>

<div class="main-container"> 

    <!-- Encabezado y Botones de Acción -->
    <div class="ms-panel-header">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="button-group-spacing">
            <?= Html::a(
                '<i class="fas fa-edit"></i> Actualizar',
                ['update', 'id' => $model->id],
                ['class' => 'btn btn-primary']
            ) ?>

            <?= Html::a(
                '<i class="fas fa-file-contract"></i> Ver Contratos',
                ['contracts', 'id' => $model->id],
                ['class' => 'btn btn-info']
            ) ?>

            <?= Html::a(
                '<i class="fas fa-undo"></i> Volver',
                Url::to(['index']),
                [
                    'class' => 'btn btn-secondary',
                    'title' => 'Volver a la lista de corporativos',
                ]
            ) ?>
        </div>
    </div>

    <!-- Tarjeta de Información General del Corporativo -->
    <div class="ms-panel border-blue">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-info-circle text-blue-600"></i> Información General del Corporativo
            </h3>
            <div class="info-grid">
                <div>
                    <p><strong>Nombre:</strong> <?= Html::encode($model->nombre) ?></p>
                    <p><strong>Email:</strong> <?= Html::a(Html::encode($model->email), 'mailto:' . Html::encode($model->email), ['class' => 'text-blue-500']) ?></p>
                    <p><strong>Teléfono:</strong> <?= Html::encode($model->telefono) ?></p>
                </div>
                <div>
                    <p><strong>RIF:</strong> <?= Html::encode($model->rif) ?></p>
                    <p><strong>Estatus:</strong> <span class="status-badge <?= $model->estatus == 'Activo' ? 'active' : 'inactive' ?>"><?= Html::encode($model->estatus) ?></span></p>
                    <p><strong>Código Asesor:</strong> <?= Html::encode($model->codigo_asesor) ?></p>
                </div>
            </div>
            <div class="info-grid border-top">
                <div>
                    <p><strong>Fecha de Creación:</strong> <span class="font-medium"><?= Html::encode(Yii::$app->formatter->asDatetime($model->created_at, 'medium')) ?></span></p>
                </div>
                <div>
                    <p><strong>Última Actualización:</strong> <span class="font-medium"><?= Html::encode(formatUpdatedAt($model->updated_at)) ?></span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Ubicación Geográfica -->
    <div class="ms-panel border-indigo">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-map-marker-alt text-indigo-600"></i> Ubicación Geográfica
            </h3>
            <div class="info-grid">
                <div>
                    <p><strong>Estado:</strong> <?= Html::encode($model->rmEstado ? $model->rmEstado->nombre : $model->estado) ?></p>
                    <p><strong>Municipio:</strong> <?= Html::encode($model->rmMunicipio ? $model->rmMunicipio->nombre : $model->municipio) ?></p>
                </div>
                <div>
                    <p><strong>Parroquia:</strong> <?= Html::encode($model->rmParroquia ? $model->rmParroquia->nombre : $model->parroquia) ?></p>
                    <p><strong>Ciudad:</strong> <?= Html::encode($model->rmCiudad ? $model->rmCiudad->nombre : $model->ciudad) ?></p>
                </div>
            </div>
            <p class="border-top"><strong>Dirección:</strong> <?= nl2br(Html::encode($model->direccion)) ?></p>
        </div>
    </div>

    <!-- Tarjeta de Información Registral -->
    <div class="ms-panel border-gray">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-file-alt text-gray-600"></i> Información Registral
            </h3>
            <div class="info-grid">
                <div>
                    <p><strong>Lugar de Registro:</strong> <?= Html::encode($model->lugar_registro) ?></p>
                    <p><strong>Fecha Registro Mercantil:</strong> <span class="font-medium"><?= Html::encode(Yii::$app->formatter->asDate($model->fecha_registro_mercantil, 'long')) ?></span></p>
                </div>
                <div>
                    <p><strong>Tomo de Registro:</strong> <?= Html::encode($model->tomo_registro) ?></p>
                    <p><strong>Folio de Registro:</strong> <?= Html::encode($model->folio_registro) ?></p>
                </div>
            </div>
            <p class="border-top"><strong>Domicilio Fiscal:</strong> <?= nl2br(Html::encode($model->domicilio_fiscal)) ?></p>
        </div>
    </div>

    <!-- Tarjeta de Contacto Principal -->
    <div class="ms-panel border-yellow">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-user-circle text-yellow-600"></i> Contacto Principal
            </h3>
            <div class="info-grid">
                <div>
                    <p><strong>Nombre Contacto:</strong> <?= Html::encode($model->contacto_nombre) ?></p>
                    <p><strong>Cédula Contacto:</strong> <?= Html::encode($model->contacto_cedula) ?></p>
                </div>
                <div>
                    <p><strong>Teléfono Contacto:</strong> <?= Html::encode($model->contacto_telefono) ?></p>
                    <p><strong>Cargo Contacto:</strong> <?= Html::encode($model->contacto_cargo) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Datos Financieros y de Actividad -->
    <div class="ms-panel border-green-500">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-chart-line text-green-600"></i> Datos Financieros y de Actividad
            </h3>
            <div class="info-grid">
                <div>
                    <p><strong>Actividad Económica:</strong> <?= Html::encode($model->actividad_economica) ?></p>
                    <p><strong>Productos y Servicios:</strong> <?= Html::encode($model->productos_servicios) ?></p>
                </div>
                <div>
                    <p><strong>Utilidad Ejercicio Anterior:</strong> <?= Html::encode($model->utilidad_ejercicio_anterior) ?></p>
                    <p><strong>Patrimonio:</strong> <?= Html::encode($model->patrimonio) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Datos del Representante Legal -->
    <div class="ms-panel border-blue-500">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-user-tie text-blue-600"></i> Datos del Representante Legal
            </h3>
            <div class="info-grid">
                <div>
                    <p><strong>Nombre:</strong> <?= Html::encode($model->nombre_representante) ?></p>
                    <p><strong>Cédula:</strong> <?= Html::encode($model->cedula_representante) ?></p>
                    <p><strong>Nacionalidad:</strong> <?= Html::encode($model->nacionalidad_representante) ?></p>
                    <p><strong>Estado Civil:</strong> <?= Html::encode($model->estado_civil_representante) ?></p>
                </div>
                <div>
                    <p><strong>Lugar de Nacimiento:</strong> <?= Html::encode($model->lugar_nacimiento_representante) ?></p>
                    <p><strong>Fecha de Nacimiento:</strong> <?= Html::encode(Yii::$app->formatter->asDate($model->fecha_nacimiento_representante, 'long')) ?></p>
                    <p><strong>Sexo:</strong> <?= Html::encode($model->sexo_representante) ?></p>
                </div>
            </div>
            <div class="info-grid border-top">
                <div>
                    <p><strong>Profesión:</strong> <?= Html::encode($model->profesion_representante) ?></p>
                    <p><strong>Ocupación:</strong> <?= Html::encode($model->ocupacion_representante) ?></p>
                </div>
                <div>
                    <p><strong>Descripción de Actividad:</strong> <?= Html::encode($model->descripcion_actividad_representante) ?></p>
                </div>
            </div>
            <p class="border-top"><strong>Dirección:</strong> <?= nl2br(Html::encode($model->direccion_representante)) ?></p>
            <p><strong>Teléfono:</strong> <?= Html::encode($model->telefono_representante) ?></p>
        </div>
    </div>


    <!-- Secciones de Clínicas y Empleados Asociados -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Tarjeta de Clínicas Asociadas -->
        <div class="ms-panel border-green">
            <div class="ms-panel-body">
                <h3 class="section-title">
                    <i class="fas fa-hospital text-green-600"></i> Clínicas Asociadas
                </h3>
                <?php if (!empty($model->clinicas)): ?>
                    <ul class="divide-y">
                        <?php foreach ($model->clinicas as $clinica): ?>
                            <li class="py-3 flex justify-between items-center text-gray-700">
                                <span>
                                    <?= Html::a(Html::encode($clinica->nombre), ['rm-clinica/view', 'id' => $clinica->id], ['class' => 'text-blue-500 font-medium']) ?>
                                    <small class="text-gray-500 block sm:inline">(RIF: <?= Html::encode($clinica->rif) ?>)</small>
                                </span>
                                <i class="fas fa-arrow-right"></i>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-gray-500">No hay clínicas asociadas a este corporativo.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tarjeta de Empleados Asociados -->
        <div class="ms-panel border-gray">
            <div class="ms-panel-body">
                <h3 class="section-title">
                    <i class="fas fa-user-tie text-gray-600"></i> Empleados Asociados
                </h3>
                <?php if (!empty($model->users)): ?>
                    <ul class="divide-y">
                        <?php foreach ($model->users as $user): ?>
                            <li class="py-3 flex justify-between items-center text-gray-700">
                                <span>
                                    <?php
                                        $nombreCompleto = '';
                                        if ($user->id) {
                                            $nombreCompleto = $user->nombres . ' ' . $user->apellidos;
                                        } else {
                                            $nombreCompleto = $user->user->username; // Fallback
                                        }
                                    ?>
                                    <?= Html::a(Html::encode($nombreCompleto), ['user-datos/view', 'id' => $user->id], ['class' => 'text-blue-500 font-medium']) ?>
                                    <small class="text-gray-500 block sm:inline">(Usuario: <?= Html::encode($user->user->username) ?>)</small>
                                </span>
                                <i class="fas fa-arrow-right"></i>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-gray-500">No hay empleados asociados a este corporativo.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
