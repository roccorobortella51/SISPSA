<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\UserHelper; // Importar el UserHelper

/** @var yii\web\View $this */
/** @var app\models\Agente $model */

$this->title = 'DETALLES DE LA AGENCIA: ' . Html::encode($model->nom);
$this->params['breadcrumbs'][] = ['label' => 'AGENCIAS', 'url' => ['index']];
$this->params['breadcrumbs'][] = Html::encode($model->nom);
\yii\web\YiiAsset::register($this); // Esto registra los assets por defecto de Yii

// Function to format percentages (assuming they are stored as integers representing percentage points)
function formatPercentage($value) {
    return Yii::$app->formatter->asPercent((float)$value / 100);
}

// Function to format dates
function formatDateTime($value) {
    return $value ? Yii::$app->formatter->asDatetime($value) : 'N/A';
}

// Obtener los datos de contacto del propietario del agente usando el UserHelper
$ownerContactInfo = UserHelper::getAgenteOwnerContactInfo($model->id);

?>
<!DOCTYPE html> <!-- ¡AÑADIDO! -->
<html lang="es"> <!-- ¡AÑADIDO! -->
<head> <!-- ¡AÑADIDO! -->
    <meta charset="UTF-8"> <!-- ¡AÑADIDO! -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- ¡AÑADIDO! -->
    <title><?= Html::encode($this->title) ?></title> <!-- ¡AÑADIDO! -->
    <!-- Carga de Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script> <!-- ¡AÑADIDO! -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"> <!-- ¡AÑADIDO! -->
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- ¡AÑADIDO! -->
    <style> <!-- ¡AÑADIDO! -->
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
    </style> <!-- ¡AÑADIDO! -->
</head> <!-- ¡AÑADIDO! -->
<body> <!-- ¡AÑADIDO! -->

<div class="container mx-auto p-4 bg-gray-50 min-h-screen rounded-lg shadow-md">


    <!-- Encabezado y Botones de Acción -->
    <div class="flex flex-col justify-start items-start mb-6 p-4 bg-white rounded-lg shadow-sm">
        <h1 class="text-3xl font-bold text-gray-800 mb-4"><?= Html::encode($this->title) ?></h1>
        <div class="flex flex-wrap gap-3 w-full justify-start">
            <?= Html::a(
                '<i class="fas fa-edit mr-2"></i> Actualizar Agencia',
                ['update', 'id' => $model->id],
                ['class' => 'px-5 py-2 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 transition duration-300 ease-in-out flex items-center text-sm font-medium']
            ) ?>
            <?= Html::a(
                '<i class="fas fa-undo mr-2"></i> Volver a la Lista',
                ['index'],
                [
                    'class' => 'px-5 py-2 bg-gray-200 text-gray-800 rounded-lg shadow-md hover:bg-gray-300 transition duration-300 ease-in-out flex items-center text-sm font-medium',
                    'title' => 'Volver a la lista de agencias',
                ]
            ) ?>
            <?= Html::a(
                '<i class="fas fa-trash-alt mr-2"></i> Eliminar Agencia',
                ['delete', 'id' => $model->id],
                [
                    'class' => 'px-5 py-2 bg-red-600 text-white rounded-lg shadow-md hover:bg-red-700 transition duration-300 ease-in-out flex items-center text-sm font-medium',
                    'data' => [
                        'confirm' => '¿Estás seguro de que quieres eliminar este agente? Esta acción no se puede deshacer.',
                        'method' => 'post',
                    ],
                ]
            ) ?>
        </div>
    </div>

    <!-- Tarjeta de Información General de la Agencia -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border-t-4 border-blue-600">
        <h3 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center">
            <i class="fas fa-building text-blue-600 mr-3"></i> Información General de la Agencia
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
            <div>
                <p class="text-gray-700 mb-2"><strong>Nombre de la Agencia:</strong> <?= Html::encode($model->nom) ?></p>
                <p class="text-gray-700 mb-2"><strong>Nombre del Propietario:</strong> <?= Html::encode($model->propietario->username ?? 'N/A') ?></p>
                <p class="text-gray-700 mb-2"><strong>RIF:</strong> <?= Html::encode($ownerContactInfo['rif']) ?></p>
            </div>
            <div>
                <p class="text-gray-700 mb-2"><strong>Email:</strong> <?= Html::a(Html::encode($ownerContactInfo['email']), 'mailto:' . Html::encode($ownerContactInfo['email'])) ?></p>
                <p class="text-gray-700 mb-2"><strong>Teléfono:</strong> <?= Html::encode($ownerContactInfo['telefono']) ?></p>
            </div>
        </div>
        <p class="text-gray-700 mt-4 pt-4 border-t border-gray-100"><strong>Dirección:</strong> <?= nl2br(Html::encode($ownerContactInfo['direccion'])) ?></p>
    </div>

    <!-- Tarjeta de Porcentajes de Comisión -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border-t-4 border-purple-600">
        <h3 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center">
            <i class="fas fa-percent text-purple-600 mr-3"></i> Porcentajes de Comisión
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-gray-50 rounded-lg p-4 text-center shadow-sm">
                <h6 class="text-gray-600 text-sm font-medium">Porcentaje Venta</h6>
                <p class="text-xl font-bold text-blue-700 mt-1"><?= formatPercentage($model->por_venta) ?></p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 text-center shadow-sm">
                <h6 class="text-gray-600 text-sm font-medium">Porcentaje Asesoría</h6>
                <p class="text-xl font-bold text-blue-700 mt-1"><?= formatPercentage($model->por_asesor) ?></p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 text-center shadow-sm">
                <h6 class="text-gray-600 text-sm font-medium">Porcentaje Cobranza</h6>
                <p class="text-xl font-bold text-blue-700 mt-1"><?= formatPercentage($model->por_cobranza) ?></p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 text-center shadow-sm">
                <h6 class="text-gray-600 text-sm font-medium">Porcentaje Post Venta</h6>
                <p class="text-xl font-bold text-blue-700 mt-1"><?= formatPercentage($model->por_post_venta) ?></p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 text-center shadow-sm">
                <h6 class="text-gray-600 text-sm font-medium">Porcentaje Agente</h6>
                <p class="text-xl font-bold text-blue-700 mt-1"><?= formatPercentage($model->por_agente) ?></p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 text-center shadow-sm">
                <h6 class="text-gray-600 text-sm font-medium">Porcentaje Máximo</h6>
                <p class="text-xl font-bold text-blue-700 mt-1"><?= formatPercentage($model->por_max) ?></p>
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
                <p class="text-lg font-bold text-gray-800 mt-1"><?= formatDateTime($model->created_at) ?></p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 text-center shadow-sm">
                <h6 class="text-gray-600 text-sm font-medium">Última Actualización</h6>
                <p class="text-lg font-bold text-gray-800 mt-1"><?= formatDateTime($model->updated_at) ?></p>
            </div>
            <?php /*
            <div class="bg-gray-50 rounded-lg p-4 text-center shadow-sm">
                <h6 class="text-gray-600 text-sm font-medium">Fecha de Eliminación</h6>
                <p class="text-lg font-bold text-gray-800 mt-1"><?= formatDateTime($model->deleted_at) ?></p>
            </div>
            */ ?>
        </div>
    </div>

</div>

</body> <!-- ¡AÑADIDO! -->
</html> <!-- ¡AÑADIDO! -->