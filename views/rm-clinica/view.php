<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\UserHelper; // Importar el UserHelper

/** @var yii\web\View $this */
/** @var app\models\RmClinica $model */
/** @var array $estadosList */
/** @var array $municipiosList */
/** @var array $parroquiaList */
/** @var array $ciudadesList */
/** @var array $listaEstatus */

// Asegúrate de que estas variables siempre tengan un valor para evitar errores
$estadosList = $estadosList ?? [];
$municipiosList = $municipiosList ?? [];
$parroquiaList = $parroquiaList ?? [];
$ciudadesList = $ciudadesList ?? [];
$listaEstatus = $listaEstatus ?? [];

$this->title = 'DETALLES DE LA CLÍNICA: ' . Html::encode($model->nombre);
$this->params['breadcrumbs'][] = ['label' => 'CLÍNICAS', 'url' => ['index']];
$this->params['breadcrumbs'][] = Html::encode($model->nombre);

\yii\web\YiiAsset::register($this); // Esto registra los assets por defecto de Yii

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
   

    <!-- Encabezado y Botones de Acción Principal -->
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
                        'confirm' => '¿Está seguro de que desea eliminar esta clínica?',
                        'method' => 'post',
                    ],
                ]
            ) ?>
            <?= Html::a(
                '<i class="fas fa-undo mr-2"></i> Volver',
                '#',
                [
                    'class' => 'px-5 py-2 bg-gray-200 text-gray-800 rounded-lg shadow-md hover:bg-gray-300 transition duration-300 ease-in-out flex items-center text-sm font-medium',
                    'onclick' => 'window.history.back(); return false;',
                    'title' => 'Volver a la página anterior',
                ]
            ) ?>
        </div>
    </div>

    <!-- Sección de Botones de Navegación Específicos de Clínica -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="col-span-1">
            <?= Html::a(
                '<i class="fas fa-file-invoice-dollar mr-2"></i> Baremo',
                ['baremo/index', 'clinica_id' => $model->id],
                ['class' => 'px-5 py-3 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 transition duration-300 ease-in-out flex items-center justify-center text-base font-medium w-full']
            ) ?>
        </div>
        <div class="col-span-1">
            <?= Html::a(
                '<i class="fas fa-clipboard-list mr-2"></i> Planes',
                ['planes/index', 'clinica_id' => $model->id],
                ['class' => 'px-5 py-3 bg-indigo-500 text-white rounded-lg shadow-md hover:bg-indigo-600 transition duration-300 ease-in-out flex items-center justify-center text-base font-medium w-full']
            ) ?>
        </div>
        <div class="col-span-1">
            <?= Html::a(
                '<i class="fas fa-users mr-2"></i> Afiliados',
                ['user-datos/index-clinicas', 'clinica_id' => $model->id],
                ['class' => 'px-5 py-3 bg-teal-500 text-white rounded-lg shadow-md hover:bg-teal-600 transition duration-300 ease-in-out flex items-center justify-center text-base font-medium w-full']
            ) ?>
        </div>
        <div class="col-span-1">
            <?= Html::a(
                '<i class="fas fa-tasks mr-2"></i> Check List',
                ['check-list-clinicas/index', 'clinica_id' => $model->id],
                ['class' => 'px-5 py-3 bg-cyan-500 text-white rounded-lg shadow-md hover:bg-cyan-600 transition duration-300 ease-in-out flex items-center justify-center text-base font-medium w-full']
            ) ?>
        </div>
    </div>

    <!-- Tarjeta de Información General de la Clínica -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border-t-4 border-blue-600">
        <h3 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center">
            <i class="fas fa-hospital-alt text-blue-600 mr-3"></i> Información General de la Clínica
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
            <div>
                <p class="text-gray-700 mb-2"><strong>Nombre:</strong> <?= Html::encode($model->nombre) ?></p>
                <p class="text-gray-700 mb-2"><strong>RIF:</strong> <?= Html::encode($model->rif) ?></p>
            </div>
            <div>
                <p class="text-gray-700 mb-2"><strong>Teléfono:</strong> <?= Html::encode($model->telefono) ?></p>
                <p class="text-gray-700 mb-2"><strong>Correo Electrónico:</strong> <?= Html::a(Html::encode($model->correo), 'mailto:' . Html::encode($model->correo), ['class' => 'text-blue-500 hover:underline']) ?></p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8 mt-4 pt-4 border-t border-gray-100">
            <div>
                <p class="text-gray-700 mb-2"><strong>Código de Clínica:</strong> <?= Html::encode($model->codigo_clinica) ?></p>
            </div>
            <div>
                <p class="text-gray-700 mb-2"><strong>Estatus:</strong> <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $model->estatus == 'Activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>"><?= Html::encode($listaEstatus[$model->estatus] ?? $model->estatus) ?></span></p>
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
                <p class="text-gray-700 mb-2"><strong>Estado:</strong> <?= Html::encode($estadosList[$model->estado] ?? 'N/A') ?></p>
                <p class="text-gray-700 mb-2"><strong>Municipio:</strong> <?= Html::encode($municipiosList[(string)$model->municipio] ?? 'N/A') ?></p>
            </div>
            <div>
                <p class="text-gray-700 mb-2"><strong>Parroquia:</strong> <?= Html::encode($parroquiaList[$model->parroquia] ?? 'N/A') ?></p>
                <p class="text-gray-700 mb-2"><strong>Ciudad:</strong> <?= Html::encode($ciudadesList[$model->ciudad] ?? 'N/A') ?></p>
            </div>
        </div>
        <p class="text-gray-700 mt-4 pt-4 border-t border-gray-100"><strong>Dirección:</strong> <?= nl2br(Html::encode($model->direccion)) ?></p>
    </div>

    <!-- Tarjeta de Información de Contacto y Redes Sociales -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border-t-4 border-yellow-600">
        <h3 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center">
            <i class="fas fa-globe text-yellow-600 mr-3"></i> Web y Redes Sociales
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
            <div>
                <p class="text-gray-700 mb-2"><strong>Página Web:</strong>
                    <?php if (!empty($model->webpage)): ?>
                        <?= Html::a(Html::encode($model->webpage), $model->webpage, ['target' => '_blank', 'class' => 'text-blue-500 hover:underline']) ?>
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </p>
            </div>
            <div>
                <p class="text-gray-700 mb-2"><strong>Instagram:</strong>
                    <?php if (!empty($model->rs_instagram)): ?>
                        <?= Html::a(Html::encode($model->rs_instagram), 'https://instagram.com/' . ltrim($model->rs_instagram, '@'), ['target' => '_blank', 'class' => 'text-blue-500 hover:underline']) ?>
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Fechas de Gestión -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border-t-4 border-gray-600">
        <h3 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center">
            <i class="fas fa-calendar-alt text-gray-600 mr-3"></i> Fechas de Gestión
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded-lg p-4 text-center shadow-sm">
                <p class="text-gray-600 text-sm font-medium">Fecha de Creación</p>
                <p class="text-lg font-bold text-gray-800 mt-1"><?= Html::encode(Yii::$app->formatter->asDatetime($model->created_at, 'medium')) ?></p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 text-center shadow-sm">
                <p class="text-gray-600 text-sm font-medium">Última Actualización</p>
                <p class="text-lg font-bold text-gray-800 mt-1"><?= Html::encode($model->updated_at ? Yii::$app->formatter->asDatetime($model->updated_at, 'medium') : 'No se ha modificado') ?></p>
            </div>
        </div>
    </div>

</div>

</body>
</html>
