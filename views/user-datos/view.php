<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\UserDatos $model */
/** @var string $estado Nombre del estado resuelto para mostrar */
/** @var string $municipio Nombre del municipio resuelto para mostrar */
/** @var string $parroquia Nombre de la parroquia resuelta para mostrar */
/** @var string $ciudad Nombre de la ciudad resuelta para mostrar */

$this->title = 'PERFIL DEL AFILIADO: ' . Html::encode($model->nombres . ' ' . $model->apellidos);
$this->params['breadcrumbs'][] = ['label' => 'Afiliados', 'url' => ['index']];
$this->params['breadcrumbs'][] = Html::encode($model->nombres . ' ' . $model->apellidos);
\yii\web\YiiAsset::register($this);
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
        /* Estilos específicos para la foto de perfil */
        .profile-img-container {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin: 0 auto 1rem;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #e2e8f0; /* gray-200 */
        }
        .profile-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 p-4 bg-white rounded-lg shadow-sm">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0"><?= Html::encode("Perfil del Afiliado #{$model->id}") ?></h1>
        <div class="flex flex-wrap gap-3">
            <?= Html::a(
                '<i class="fas fa-file-pdf mr-2"></i> Contrato',
                ['user-datos/generar-contratov', 'id' => $model->id],
                [
                    'class' => 'px-5 py-2 bg-red-600 text-white rounded-lg shadow-md hover:bg-red-700 transition duration-300 ease-in-out flex items-center text-sm font-medium',
                    'target' => '_blank',
                    'data-pjax' => '0'
                ]
            ) ?>
            <?= Html::a(
                '<i class="fas fa-edit mr-2"></i> Actualizar',
                ['update', 'id' => $model->id],
                ['class' => 'px-5 py-2 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 transition duration-300 ease-in-out flex items-center text-sm font-medium']
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

    <!-- Sección de Foto de Perfil y Datos Personales -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border-t-4 border-blue-600 text-center">
        <div class="profile-img-container">
            <?php if ($model->selfie): ?>
                <?= Html::img(Yii::$app->request->baseUrl . '/' . $model->selfie, [
                    'alt' => 'Foto de Perfil',
                    'class' => 'profile-img'
                ]) ?>
            <?php else: ?>
                <i class="fas fa-user-circle text-gray-400" style="font-size: 80px;"></i>
            <?php endif; ?>
        </div>
        <p class="text-lg font-semibold text-gray-800 mb-4"><?= Html::encode($model->nombres . ' ' . $model->apellidos) ?></p>

        <h3 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center justify-center">
            <i class="fas fa-address-card text-blue-600 mr-3"></i> Datos Personales
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8 text-left">
            <div>
                <p class="text-gray-700 mb-2"><strong>Nombres:</strong> <?= Html::encode($model->nombres) ?></p>
                <p class="text-gray-700 mb-2"><strong>Cédula de Identidad:</strong> <?= Html::encode($model->cedulaFormatted) ?></p>
                <p class="text-gray-700 mb-2"><strong>Sexo:</strong> <?= Html::encode($model->sexo) ?></p>
                <p class="text-gray-700 mb-2"><strong>Correo Electrónico:</strong> <?= Html::a(Html::encode($model->email), 'mailto:' . Html::encode($model->email), ['class' => 'text-blue-500 hover:underline']) ?></p>
            </div>
            <div>
                <p class="text-gray-700 mb-2"><strong>Apellidos:</strong> <?= Html::encode($model->apellidos) ?></p>
                <p class="text-gray-700 mb-2"><strong>Fecha de Nacimiento:</strong> <span class="font-medium"><?= Html::encode(Yii::$app->formatter->asDate($model->fechanac, 'd-m-Y')) ?></span></p>
                <p class="text-gray-700 mb-2"><strong>Teléfono:</strong> <?= Html::encode($model->telefono) ?></p>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Ubicación -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border-t-4 border-indigo-600">
        <h3 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center">
            <i class="fas fa-map-marker-alt text-indigo-600 mr-3"></i> Ubicación
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
            <div>
                <p class="text-gray-700 mb-2"><strong>Estado:</strong> <?= Html::encode($estado ?? 'N/A') ?></p>
                <p class="text-gray-700 mb-2"><strong>Ciudad:</strong> <?= Html::encode($ciudad ?? 'N/A') ?></p>
            </div>
            <div>
                <p class="text-gray-700 mb-2"><strong>Municipio:</strong> <?= Html::encode($municipio ?? 'N/A') ?></p>
                <p class="text-gray-700 mb-2"><strong>Parroquia:</strong> <?= Html::encode($parroquia ?? 'N/A') ?></p>
            </div>
        </div>
        <p class="text-gray-700 mt-4 pt-4 border-t border-gray-100"><strong>Dirección:</strong> <?= nl2br(Html::encode($model->direccion)) ?></p>
    </div>

    <!-- Tarjeta de Información Adicional -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border-t-4 border-gray-600">
        <h3 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center">
            <i class="fas fa-info-circle text-gray-600 mr-3"></i> Información Adicional
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
            <div>
                <p class="text-gray-700 mb-2"><strong>Clínica:</strong> <?= Html::encode($model->clinica ? $model->clinica->nombre : 'No asignada') ?></p>
                <p class="text-gray-700 mb-2"><strong>Asesor:</strong> <?= Html::encode($model->asesor ? $model->asesor->nombre : 'Sin asignar') ?></p>
                <p class="text-gray-700 mb-2"><strong>Tipo de Sangre:</strong> <?= Html::encode($model->tipo_sangre) ?></p>
            </div>
            <div>
                <p class="text-gray-700 mb-2"><strong>Plan:</strong> <?= Html::encode($model->plan ? $model->plan->nombre : 'No asignado') ?></p>
                <p class="text-gray-700 mb-2"><strong>Estatus:</strong> <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $model->estatus == 'Activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>"><?= Html::encode($model->estatus) ?></span></p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
