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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::encode($this->title) ?></title>
    <!-- Carga de Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5; /* Un gris claro para el fondo */
            margin: 0;
            padding: 20px; /* Espaciado general */
            box-sizing: border-box;
        }
        /* Estilos para el breadcrumb de Yii */
        .breadcrumb {
            background-color: transparent;
            padding: 0;
            margin-bottom: 1rem;
            list-style: none;
            display: flex;
            flex-wrap: wrap;
        }
        .breadcrumb-item + .breadcrumb-item::before {
            content: "/";
            padding-right: 0.5rem;
            padding-left: 0.5rem;
            color: #6b7280; /* gray-500 */
        }
        .breadcrumb-item a {
            color: #2563eb; /* blue-600 */
            text-decoration: none;
        }
        .breadcrumb-item.active {
            color: #4b5563; /* gray-600 */
        }
    </style>
</head>
<body>

<div class="container mx-auto p-4 bg-gray-50 min-h-screen rounded-lg shadow-md">
    <!-- Breadcrumbs -->
    <nav class="mb-6" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <?php foreach ($this->params['breadcrumbs'] as $i => $breadcrumb): ?>
                <li class="breadcrumb-item <?= $i === count($this->params['breadcrumbs']) - 1 ? 'active' : '' ?>">
                    <?php if (is_array($breadcrumb)): ?>
                        <?= Html::a(Html::encode($breadcrumb['label']), $breadcrumb['url']) ?>
                    <?php else: ?>
                        <?= Html::encode($breadcrumb) ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>

    <!-- Encabezado y Botones de Acción -->
    <div class="flex flex-col justify-start items-start mb-6 p-4 bg-white rounded-lg shadow-sm">
        <h1 class="text-3xl font-bold text-gray-800 mb-4"><?= Html::encode($this->title) ?></h1>
        <div class="flex flex-wrap gap-3 w-full justify-start">
            <?= Html::a(
                '<i class="fas fa-edit mr-2"></i> Actualizar',
                ['update', 'id' => $model->id],
                ['class' => 'px-5 py-2 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 transition duration-300 ease-in-out flex items-center text-sm font-medium']
            ) ?>
            <?= Html::a(
                '<i class="fas fa-trash-alt mr-2"></i> Eliminar',
                ['delete', 'id' => $model->id],
                [
                    'class' => 'px-5 py-2 bg-red-600 text-white rounded-lg shadow-md hover:bg-red-700 transition duration-300 ease-in-out flex items-center text-sm font-medium',
                    'data' => [
                        'confirm' => '¿Está seguro de que desea eliminar este corporativo?',
                        'method' => 'post',
                    ],
                ]
            ) ?>
            <?= Html::a(
                '<i class="fas fa-undo mr-2"></i> Volver',
                Url::to(['index']),
                [
                    'class' => 'px-5 py-2 bg-gray-200 text-gray-800 rounded-lg shadow-md hover:bg-gray-300 transition duration-300 ease-in-out flex items-center text-sm font-medium',
                    'title' => 'Volver a la lista de corporativos',
                ]
            ) ?>
        </div>
    </div>

    <!-- Tarjeta de Información General del Corporativo -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border-t-4 border-blue-600">
        <h3 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center">
            <i class="fas fa-info-circle text-blue-600 mr-3"></i> Información General del Corporativo
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
            <div>
                <p class="text-gray-700 mb-2"><strong>Nombre:</strong> <?= Html::encode($model->nombre) ?></p>
                <p class="text-gray-700 mb-2"><strong>Email:</strong> <?= Html::a(Html::encode($model->email), 'mailto:' . Html::encode($model->email), ['class' => 'text-blue-500 hover:underline']) ?></p>
                <p class="text-gray-700 mb-2"><strong>Teléfono:</strong> <?= Html::encode($model->telefono) ?></p>
            </div>
            <div>
                <p class="text-gray-700 mb-2"><strong>RIF:</strong> <?= Html::encode($model->rif) ?></p>
                <p class="text-gray-700 mb-2"><strong>Estatus:</strong> <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $model->estatus == 'Activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>"><?= Html::encode($model->estatus) ?></span></p>
                <p class="text-gray-700 mb-2"><strong>Código Asesor:</strong> <?= Html::encode($model->codigo_asesor) ?></p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8 mt-4 pt-4 border-t border-gray-100">
            <div>
                <p class="text-gray-700 mb-2"><strong>Fecha de Creación:</strong> <span class="font-medium"><?= Html::encode(Yii::$app->formatter->asDatetime($model->created_at, 'medium')) ?></span></p>
            </div>
            <div>
                <p class="text-gray-700 mb-2"><strong>Última Actualización:</strong> <span class="font-medium"><?= Html::encode(formatUpdatedAt($model->updated_at)) ?></span></p>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Ubicación Geográfica -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border-t-4 border-indigo-600">
        <h3 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center">
            <i class="fas fa-map-marker-alt text-indigo-600 mr-3"></i> Ubicación Geográfica
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
            <div>
                <p class="text-gray-700 mb-2"><strong>Estado:</strong> <?= Html::encode($model->rmEstado ? $model->rmEstado->nombre : $model->estado) ?></p>
                <p class="text-gray-700 mb-2"><strong>Municipio:</strong> <?= Html::encode($model->rmMunicipio ? $model->rmMunicipio->nombre : $model->municipio) ?></p>
            </div>
            <div>
                <p class="text-gray-700 mb-2"><strong>Parroquia:</strong> <?= Html::encode($model->rmParroquia ? $model->rmParroquia->nombre : $model->parroquia) ?></p>
                <p class="text-gray-700 mb-2"><strong>Ciudad:</strong> <?= Html::encode($model->rmCiudad ? $model->rmCiudad->nombre : $model->ciudad) ?></p>
            </div>
        </div>
        <p class="text-gray-700 mt-4 pt-4 border-t border-gray-100"><strong>Dirección:</strong> <?= nl2br(Html::encode($model->direccion)) ?></p>
    </div>

    <!-- Tarjeta de Información Registral -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border-t-4 border-gray-600">
        <h3 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center">
            <i class="fas fa-file-alt text-gray-600 mr-3"></i> Información Registral
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
            <div>
                <p class="text-gray-700 mb-2"><strong>Lugar de Registro:</strong> <?= Html::encode($model->lugar_registro) ?></p>
                <p class="text-gray-700 mb-2"><strong>Fecha Registro Mercantil:</strong> <span class="font-medium"><?= Html::encode(Yii::$app->formatter->asDate($model->fecha_registro_mercantil, 'long')) ?></span></p>
            </div>
            <div>
                <p class="text-gray-700 mb-2"><strong>Tomo de Registro:</strong> <?= Html::encode($model->tomo_registro) ?></p>
                <p class="text-gray-700 mb-2"><strong>Folio de Registro:</strong> <?= Html::encode($model->folio_registro) ?></p>
            </div>
        </div>
        <p class="text-gray-700 mt-4 pt-4 border-t border-gray-100"><strong>Domicilio Fiscal:</strong> <?= nl2br(Html::encode($model->domicilio_fiscal)) ?></p>
    </div>

    <!-- Tarjeta de Contacto Principal -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border-t-4 border-yellow-600">
        <h3 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center">
            <i class="fas fa-user-circle text-yellow-600 mr-3"></i> Contacto Principal
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
            <div>
                <p class="text-gray-700 mb-2"><strong>Nombre Contacto:</strong> <?= Html::encode($model->contacto_nombre) ?></p>
                <p class="text-gray-700 mb-2"><strong>Cédula Contacto:</strong> <?= Html::encode($model->contacto_cedula) ?></p>
            </div>
            <div>
                <p class="text-gray-700 mb-2"><strong>Teléfono Contacto:</strong> <?= Html::encode($model->contacto_telefono) ?></p>
                <p class="text-gray-700 mb-2"><strong>Cargo Contacto:</strong> <?= Html::encode($model->contacto_cargo) ?></p>
            </div>
        </div>
    </div>

    <!-- Secciones de Clínicas y Empleados Asociados -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Tarjeta de Clínicas Asociadas -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-green-600">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center">
                <i class="fas fa-hospital text-green-600 mr-3"></i> Clínicas Asociadas
            </h3>
            <?php if (!empty($model->clinicas)): ?>
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($model->clinicas as $clinica): ?>
                        <li class="py-3 flex justify-between items-center">
                            <span>
                                <?= Html::a(Html::encode($clinica->nombre), ['rm-clinica/view', 'id' => $clinica->id], ['class' => 'text-blue-500 hover:underline font-medium']) ?>
                                <small class="text-gray-500 block sm:inline">(RIF: <?= Html::encode($clinica->rif) ?>)</small>
                            </span>
                            <i class="fas fa-arrow-right text-gray-400"></i>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-gray-500">No hay clínicas asociadas a este corporativo.</p>
            <?php endif; ?>
        </div>

        <!-- Tarjeta de Empleados Asociados -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-gray-800">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center">
                <i class="fas fa-user-tie text-gray-800 mr-3"></i> Empleados Asociados
            </h3>
            <?php if (!empty($model->users)): ?>
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($model->users as $user): ?>
                        <li class="py-3 flex justify-between items-center">
                            <span>
                                <?php
                                    $nombreCompleto = '';
                                    if ($user->userDatos) {
                                        $nombreCompleto = $user->userDatos->nombres . ' ' . $user->userDatos->apellidos;
                                    } else {
                                        $nombreCompleto = $user->username; // Fallback
                                    }
                                ?>
                                <?= Html::a(Html::encode($nombreCompleto), ['user/view', 'id' => $user->id], ['class' => 'text-blue-500 hover:underline font-medium']) ?>
                                <small class="text-gray-500 block sm:inline">(Usuario: <?= Html::encode($user->username) ?>)</small>
                            </span>
                            <i class="fas fa-arrow-right text-gray-400"></i>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-gray-500">No hay empleados asociados a este corporativo.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
