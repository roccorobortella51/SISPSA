<?php

use yii\helpers\Html;
use yii\helpers\Url;
// use yii\widgets\DetailView; // Ya no lo usaremos directamente para el layout principal
use app\models\Agente;
use app\models\User;

/** @var yii\web\View $this */
/** @var app\models\AgenteFuerza $model */

$this->title = 'DETALLES DE ASESOR VENDEDOR: ';
$this->params['breadcrumbs'][] = ['label' => 'AGENTES DE FUERZA', 'url' => ['index']]; // Añadido URL para breadcrumb
$this->params['breadcrumbs'][] = 'DETALLES';

\yii\web\YiiAsset::register($this);

// Preparar el nombre de la agencia
$agenciaNombre = 'N/A';
if ($model->agente) {
    $agenciaNombre = $model->agente->nom;
}

// Preparar el nombre de usuario (asumiendo que userDatos tiene nombres y apellidos)
$nombreCompletoUsuario = 'N/A';
$telefonoUsuario = 'N/A';
$emailUsuario = 'N/A';

if ($model->user && $model->user->userDatos) { // Acceder a userDatos a través de la relación user
    $nombreCompletoUsuario = $model->user->userDatos->nombres . ' ' . $model->user->userDatos->apellidos;
    $telefonoUsuario = $model->user->userDatos->telefono ?? 'N/A';
    $emailUsuario = $model->user->userDatos->email ?? 'N/A';
} elseif ($model->user) {
    $nombreCompletoUsuario = $model->user->username; // Fallback al username si no hay userDatos
}

// Función auxiliar para mostrar Sí/No con íconos y clases de Tailwind
function formatBooleanIcon($value) {
    if ($value) {
        return '<span class="text-green-600 mr-1"><i class="fas fa-check-circle"></i></span> Sí';
    } else {
        return '<span class="text-red-600 mr-1"><i class="fas fa-times-circle"></i></span> No';
    }
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
    

    <!-- Encabezado y Botones de Acción -->
    <div class="flex flex-col justify-start items-start mb-6 p-4 bg-white rounded-lg shadow-sm">
        <h1 class="text-3xl font-bold text-gray-800 mb-4"><?= Html::encode($this->title) ?></h1>
        <div class="flex flex-wrap gap-3 w-full justify-start">
            <?= Html::a(
                '<i class="fas fa-edit mr-2"></i> Actualizar Asesor',
                ['update', 'id' => $model->id],
                ['class' => 'px-5 py-2 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 transition duration-300 ease-in-out flex items-center text-sm font-medium']
            ) ?>
            <?= Html::a(
                '<i class="fas fa-arrow-left mr-2"></i> Volver a la Lista',
                ['agente-fuerza/index-by-agente', 'agente_id' => $model->agente_id],
                [
                    'class' => 'px-5 py-2 bg-gray-200 text-gray-800 rounded-lg shadow-md hover:bg-gray-300 transition duration-300 ease-in-out flex items-center text-sm font-medium',
                    'title' => 'Volver a la lista de agentes de fuerza',
                ]
            ) ?>
            <?= Html::a(
                '<i class="fas fa-trash-alt mr-2"></i> Eliminar Asesor',
                ['delete', 'id' => $model->id],
                [
                    'class' => 'px-5 py-2 bg-red-600 text-white rounded-lg shadow-md hover:bg-red-700 transition duration-300 ease-in-out flex items-center text-sm font-medium',
                    'data' => [
                        'confirm' => '¿Estás seguro de que quieres eliminar este agente de fuerza? Esta acción no se puede deshacer.',
                        'method' => 'post',
                    ],
                ]
            ) ?>
        </div>
    </div>

    <!-- Tarjeta de Información General del Asesor -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border-t-4 border-blue-600">
        <h3 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center">
            <i class="fas fa-user-tie text-blue-600 mr-3"></i> Información General del Asesor
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
            <div>
                <p class="text-gray-700 mb-2"><strong>Usuario:</strong> <?= Html::encode($nombreCompletoUsuario) ?></p>
                <p class="text-gray-700 mb-2"><strong>Teléfono:</strong> <?= Html::encode($telefonoUsuario) ?></p>
            </div>
            <div>
                <p class="text-gray-700 mb-2"><strong>Agencia Asociada:</strong> <?= Html::encode($agenciaNombre) ?></p>
                <p class="text-gray-700 mb-2"><strong>Correo Electrónico:</strong> <?= Html::a(Html::encode($emailUsuario), 'mailto:' . Html::encode($emailUsuario), ['class' => 'text-blue-500 hover:underline']) ?></p>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Porcentajes de Comisión -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border-t-4 border-purple-600">
        <h3 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center">
            <i class="fas fa-percent text-purple-600 mr-3"></i> Porcentajes de Comisión
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
            <div class="bg-gray-50 rounded-lg p-4 text-center shadow-sm">
                <h6 class="text-gray-600 text-sm font-medium">Venta</h6>
                <p class="text-xl font-bold text-blue-700 mt-1"><?= Yii::$app->formatter->asPercent($model->por_venta / 100) ?></p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 text-center shadow-sm">
                <h6 class="text-gray-600 text-sm font-medium">Asesoría</h6>
                <p class="text-xl font-bold text-blue-700 mt-1"><?= Yii::$app->formatter->asPercent($model->por_asesor / 100) ?></p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 text-center shadow-sm">
                <h6 class="text-gray-600 text-sm font-medium">Cobranza</h6>
                <p class="text-xl font-bold text-blue-700 mt-1"><?= Yii::$app->formatter->asPercent($model->por_cobranza / 100) ?></p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 text-center shadow-sm">
                <h6 class="text-gray-600 text-sm font-medium">Post Venta</h6>
                <p class="text-xl font-bold text-blue-700 mt-1"><?= Yii::$app->formatter->asPercent($model->por_post_venta / 100) ?></p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 text-center shadow-sm">
                <h6 class="text-gray-600 text-sm font-medium">Registro</h6>
                <p class="text-xl font-bold text-blue-700 mt-1"><?= Yii::$app->formatter->asPercent($model->por_registrar / 100) ?></p>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Permisos de Acceso -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border-t-4 border-green-600">
        <h3 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center">
            <i class="fas fa-lock text-green-600 mr-3"></i> Permisos de Acceso
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-gray-50 rounded-lg p-4 shadow-sm">
                <h6 class="text-lg font-medium text-gray-700 mb-3 pb-2 border-b border-gray-200">Venta y Asesoría</h6>
                <ul class="divide-y divide-gray-200">
                    <li class="py-2 flex justify-between items-center text-gray-700">
                        Puede Vender
                        <span><?= formatBooleanIcon($model->puede_vender) ?></span>
                    </li>
                    <li class="py-2 flex justify-between items-center text-gray-700">
                        Puede Asesorar
                        <span><?= formatBooleanIcon($model->puede_asesorar) ?></span>
                    </li>
                </ul>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 shadow-sm">
                <h6 class="text-lg font-medium text-gray-700 mb-3 pb-2 border-b border-gray-200">Gestión y Cobranza</h6>
                <ul class="divide-y divide-gray-200">
                    <li class="py-2 flex justify-between items-center text-gray-700">
                        Puede Cobrar
                        <span><?= formatBooleanIcon($model->puede_cobrar) ?></span>
                    </li>
                    <li class="py-2 flex justify-between items-center text-gray-700">
                        Puede Post Venta
                        <span><?= formatBooleanIcon($model->puede_post_venta) ?></span>
                    </li>
                    <li class="py-2 flex justify-between items-center text-gray-700">
                        Puede Registrar
                        <span><?= formatBooleanIcon($model->puede_registrar) ?></span>
                    </li>
                </ul>
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
                <h6 class="text-gray-600 text-sm font-medium">Fecha de Creación</h6>
                <p class="text-lg font-bold text-gray-800 mt-1"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 text-center shadow-sm">
                <h6 class="text-gray-600 text-sm font-medium">Última Actualización</h6>
                <p class="text-lg font-bold text-gray-800 mt-1"><?= Yii::$app->formatter->asDatetime($model->updated_at) ?></p>
            </div>
            <?php /*
            <div class="bg-gray-50 rounded-lg p-4 text-center shadow-sm">
                <h6 class="text-gray-600 text-sm font-medium">Fecha de Eliminación</h6>
                <p class="text-lg font-bold text-gray-800 mt-1"><?= $model->deleted_at ? Yii::$app->formatter->asDatetime($model->deleted_at) : 'N/A' ?></p>
            </div>
            */ ?>
        </div>
    </div>

</div>

</body>
</html>
